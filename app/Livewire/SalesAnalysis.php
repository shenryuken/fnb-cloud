<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Analysis')]
#[Lazy]
class SalesAnalysis extends Component
{
    public string $fromDate;
    public string $toDate;
    public string $businessDayStartTime = '00:00';
    public string $businessDayEndTime = '23:59';
    public ?int $analysisProductId = null;

    public function mount(): void
    {
        $tenant = Auth::user()->tenant;
        $this->businessDayStartTime = $tenant->business_day_start_time ? substr((string) $tenant->business_day_start_time, 0, 5) : '00:00';
        $this->businessDayEndTime = $tenant->business_day_end_time ? substr((string) $tenant->business_day_end_time, 0, 5) : '23:59';

        $this->toDate = now()->toDateString();
        $this->fromDate = now()->startOfMonth()->toDateString();
        $this->analysisProductId = Product::query()->orderBy('name')->value('id');
    }

    public function setRange(string $range): void
    {
        $today = now()->toDateString();

        if ($range === 'today') {
            $this->fromDate = $today;
            $this->toDate = $today;
            return;
        }

        if ($range === 'yesterday') {
            $yesterday = now()->subDay()->toDateString();
            $this->fromDate = $yesterday;
            $this->toDate = $yesterday;
            return;
        }

        if ($range === '7d') {
            $this->fromDate = now()->subDays(6)->toDateString();
            $this->toDate = $today;
            return;
        }

        if ($range === '30d') {
            $this->fromDate = now()->subDays(29)->toDateString();
            $this->toDate = $today;
            return;
        }

        if ($range === 'month') {
            $this->fromDate = now()->startOfMonth()->toDateString();
            $this->toDate = $today;
            return;
        }
    }

    private function rangeStart(): Carbon
    {
        return Carbon::parse($this->fromDate . ' ' . $this->businessDayStartTime)->startOfMinute();
    }

    private function rangeEnd(): Carbon
    {
        $end = Carbon::parse($this->toDate . ' ' . $this->businessDayEndTime)->endOfMinute();

        if ($this->businessDayEndTime <= $this->businessDayStartTime) {
            $end = $end->addDay();
        }

        return $end;
    }

    private function businessDayStartOffsetMinutes(): int
    {
        [$h, $m] = array_map('intval', explode(':', $this->businessDayStartTime));
        return ($h * 60) + $m;
    }

    private function ordersBaseQuery()
    {
        return Order::query()
            ->whereBetween('created_at', [$this->rangeStart(), $this->rangeEnd()])
            ->where('status', 'completed');
    }

    #[Computed]
    public function summary(): array
    {
        $row = (clone $this->ordersBaseQuery())
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as net_sales')
            ->first();

        $ordersCount = (int) ($row->orders_count ?? 0);
        $netSales = (float) ($row->net_sales ?? 0);

        return [
            'orders_count' => $ordersCount,
            'net_sales' => $netSales,
            'avg_order_value' => $ordersCount > 0 ? ($netSales / $ordersCount) : 0.0,
        ];
    }

    #[Computed]
    public function hourlyOrders(): array
    {
        $rows = (clone $this->ordersBaseQuery())
            ->selectRaw('HOUR(created_at) as hour')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as net_sales')
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour', 'asc')
            ->get()
            ->keyBy(fn ($r) => (int) $r->hour);

        $out = [];
        for ($h = 0; $h < 24; $h++) {
            $r = $rows->get($h);
            $out[] = [
                'hour' => $h,
                'orders_count' => (int) ($r->orders_count ?? 0),
                'net_sales' => (float) ($r->net_sales ?? 0),
            ];
        }

        return $out;
    }

    #[Computed]
    public function peakHours(): array
    {
        $byOrders = collect($this->hourlyOrders)->sortByDesc('orders_count')->first();
        $bySales = collect($this->hourlyOrders)->sortByDesc('net_sales')->first();

        $format = function (?array $row): ?array {
            if (!$row) {
                return null;
            }

            $h = (int) ($row['hour'] ?? 0);
            $start = Carbon::createFromTime($h, 0)->format('g:i A');
            $end = Carbon::createFromTime($h, 59)->format('g:i A');

            return [
                'hour' => $h,
                'label' => $start . ' - ' . $end,
                'orders_count' => (int) ($row['orders_count'] ?? 0),
                'net_sales' => (float) ($row['net_sales'] ?? 0),
            ];
        };

        return [
            'by_orders' => $format($byOrders),
            'by_sales' => $format($bySales),
        ];
    }

    #[Computed]
    public function weekly(): array
    {
        $rows = (clone $this->ordersBaseQuery())
            ->selectRaw('YEARWEEK(created_at, 3) as year_week')
            ->selectRaw("STR_TO_DATE(CONCAT(YEARWEEK(created_at, 3), ' Monday'), '%X%V %W') as week_start")
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as net_sales')
            ->groupBy('year_week', 'week_start')
            ->orderBy('week_start', 'asc')
            ->get();

        return $rows
            ->map(fn ($r) => [
                'year_week' => (int) $r->year_week,
                'week_start' => (string) $r->week_start,
                'orders_count' => (int) $r->orders_count,
                'net_sales' => (float) $r->net_sales,
            ])
            ->all();
    }

    #[Computed]
    public function dailyTopProducts(): array
    {
        $offsetMinutes = $this->businessDayStartOffsetMinutes();
        $start = $this->rangeStart();
        $end = $this->rangeEnd();
        $dayExpr = "DATE(DATE_SUB(o.created_at, INTERVAL {$offsetMinutes} MINUTE))";

        $rows = OrderItem::query()
            ->join('orders as o', 'o.id', '=', 'order_items.order_id')
            ->whereBetween('o.created_at', [$start, $end])
            ->where('o.status', 'completed')
            ->selectRaw($dayExpr . ' as day')
            ->addSelect('order_items.product_id')
            ->selectRaw('SUM(order_items.quantity) as quantity_sold')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as gross_sales')
            ->groupBy(DB::raw($dayExpr), 'order_items.product_id')
            ->orderBy(DB::raw($dayExpr), 'asc')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $productsById = Product::whereIn('id', $rows->pluck('product_id')->unique()->all())
            ->get(['id', 'name'])
            ->keyBy('id');

        return $rows
            ->groupBy(fn ($r) => (string) $r->day)
            ->map(function ($dayRows, $day) use ($productsById) {
                $top = $dayRows
                    ->sortByDesc(fn ($r) => (float) $r->gross_sales)
                    ->take(3)
                    ->values()
                    ->map(fn ($r) => [
                        'product_name' => (string) ($productsById[$r->product_id]->name ?? 'Unknown'),
                        'quantity_sold' => (int) $r->quantity_sold,
                        'gross_sales' => (float) $r->gross_sales,
                    ])
                    ->all();

                return [
                    'day' => (string) $day,
                    'top' => $top,
                ];
            })
            ->values()
            ->all();
    }

    #[Computed]
    public function analysisProducts(): array
    {
        return Product::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($p) => ['id' => (int) $p->id, 'name' => (string) $p->name])
            ->all();
    }

    #[Computed]
    public function productHourly(): array
    {
        if (!$this->analysisProductId) {
            return [];
        }

        $start = $this->rangeStart();
        $end = $this->rangeEnd();

        $rows = OrderItem::query()
            ->join('orders as o', 'o.id', '=', 'order_items.order_id')
            ->whereBetween('o.created_at', [$start, $end])
            ->where('o.status', 'completed')
            ->where('order_items.product_id', $this->analysisProductId)
            ->selectRaw('HOUR(o.created_at) as hour')
            ->selectRaw('SUM(order_items.quantity) as quantity_sold')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as gross_sales')
            ->groupBy(DB::raw('HOUR(o.created_at)'))
            ->orderBy('hour', 'asc')
            ->get()
            ->keyBy(fn ($r) => (int) $r->hour);

        $out = [];
        for ($h = 0; $h < 24; $h++) {
            $r = $rows->get($h);
            $out[] = [
                'hour' => $h,
                'quantity_sold' => (int) ($r->quantity_sold ?? 0),
                'gross_sales' => (float) ($r->gross_sales ?? 0),
            ];
        }

        return $out;
    }

    #[Computed]
    public function productDaily(): array
    {
        if (!$this->analysisProductId) {
            return [];
        }

        $offsetMinutes = $this->businessDayStartOffsetMinutes();
        $start = $this->rangeStart();
        $end = $this->rangeEnd();
        $dayExpr = "DATE(DATE_SUB(o.created_at, INTERVAL {$offsetMinutes} MINUTE))";

        return OrderItem::query()
            ->join('orders as o', 'o.id', '=', 'order_items.order_id')
            ->whereBetween('o.created_at', [$start, $end])
            ->where('o.status', 'completed')
            ->where('order_items.product_id', $this->analysisProductId)
            ->selectRaw($dayExpr . ' as day')
            ->selectRaw('SUM(order_items.quantity) as quantity_sold')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as gross_sales')
            ->groupBy(DB::raw($dayExpr))
            ->orderBy(DB::raw($dayExpr), 'asc')
            ->get()
            ->map(fn ($r) => [
                'day' => (string) $r->day,
                'quantity_sold' => (int) $r->quantity_sold,
                'gross_sales' => (float) $r->gross_sales,
            ])
            ->all();
    }

    #[Computed]
    public function productWeekly(): array
    {
        if (!$this->analysisProductId) {
            return [];
        }

        $start = $this->rangeStart();
        $end = $this->rangeEnd();

        return OrderItem::query()
            ->join('orders as o', 'o.id', '=', 'order_items.order_id')
            ->whereBetween('o.created_at', [$start, $end])
            ->where('o.status', 'completed')
            ->where('order_items.product_id', $this->analysisProductId)
            ->selectRaw('YEARWEEK(o.created_at, 3) as year_week')
            ->selectRaw("STR_TO_DATE(CONCAT(YEARWEEK(o.created_at, 3), ' Monday'), '%X%V %W') as week_start")
            ->selectRaw('SUM(order_items.quantity) as quantity_sold')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as gross_sales')
            ->groupBy('year_week', 'week_start')
            ->orderBy('week_start', 'asc')
            ->get()
            ->map(fn ($r) => [
                'year_week' => (int) $r->year_week,
                'week_start' => (string) $r->week_start,
                'quantity_sold' => (int) $r->quantity_sold,
                'gross_sales' => (float) $r->gross_sales,
            ])
            ->all();
    }

    public function render()
    {
        return view('livewire.sales-analysis');
    }
}
