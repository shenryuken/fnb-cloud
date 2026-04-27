<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Report')]
#[Lazy]
class SalesReport extends Component
{
    public string $fromDate;
    public string $toDate;
    public string $businessDayStartTime = '00:00';
    public string $businessDayEndTime = '23:59';

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;
        $this->businessDayStartTime = $tenant->business_day_start_time ? substr((string) $tenant->business_day_start_time, 0, 5) : '00:00';
        $this->businessDayEndTime = $tenant->business_day_end_time ? substr((string) $tenant->business_day_end_time, 0, 5) : '23:59';

        $this->toDate = now()->toDateString();
        $this->fromDate = now()->startOfMonth()->toDateString();
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
            ->selectRaw('COALESCE(SUM(subtotal_amount), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discounts')
            ->selectRaw('COALESCE(SUM(tax_amount), 0) as taxes')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as net_sales')
            ->first();

        return [
            'orders_count' => (int) ($row->orders_count ?? 0),
            'gross_sales' => (float) ($row->gross_sales ?? 0),
            'discounts' => (float) ($row->discounts ?? 0),
            'taxes' => (float) ($row->taxes ?? 0),
            'net_sales' => (float) ($row->net_sales ?? 0),
        ];
    }

    #[Computed]
    public function daily(): array
    {
        $offsetMinutes = $this->businessDayStartOffsetMinutes();

        return (clone $this->ordersBaseQuery())
            ->selectRaw("DATE(DATE_SUB(created_at, INTERVAL {$offsetMinutes} MINUTE)) as day")
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(subtotal_amount), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discounts')
            ->selectRaw('COALESCE(SUM(tax_amount), 0) as taxes')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as net_sales')
            ->groupBy(DB::raw("DATE(DATE_SUB(created_at, INTERVAL {$offsetMinutes} MINUTE))"))
            ->orderBy('day', 'asc')
            ->get()
            ->map(fn ($r) => [
                'day' => (string) $r->day,
                'orders_count' => (int) $r->orders_count,
                'gross_sales' => (float) $r->gross_sales,
                'discounts' => (float) $r->discounts,
                'taxes' => (float) $r->taxes,
                'net_sales' => (float) $r->net_sales,
            ])
            ->all();
    }

    #[Computed]
    public function chartData(): array
    {
        $data = collect($this->daily)->map(fn($r) => [
            'date' => Carbon::parse($r['day'])->format('M d'),
            'revenue' => round($r['net_sales'], 2),
        ])->values()->toArray();
        
        return count($data) > 0 ? $data : [];
    }

    #[Computed]
    public function paymentBreakdown(): array
    {
        return (clone $this->ordersBaseQuery())
            ->whereNotNull('payment_method')
            ->select('payment_method')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as net_sales')
            ->groupBy('payment_method')
            ->orderBy('net_sales', 'desc')
            ->get()
            ->map(fn ($r) => [
                'payment_method' => (string) $r->payment_method,
                'orders_count' => (int) $r->orders_count,
                'net_sales' => (float) $r->net_sales,
            ])
            ->all();
    }

    #[Computed]
    public function orderTypeBreakdown(): array
    {
        return (clone $this->ordersBaseQuery())
            ->selectRaw("COALESCE(order_type, 'unknown') as order_type")
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as net_sales')
            ->groupBy('order_type')
            ->orderBy('net_sales', 'desc')
            ->get()
            ->map(fn ($r) => [
                'order_type' => (string) $r->order_type,
                'orders_count' => (int) $r->orders_count,
                'net_sales' => (float) $r->net_sales,
            ])
            ->all();
    }

    #[Computed]
    public function topProducts(): array
    {
        $orderIdsQuery = (clone $this->ordersBaseQuery())->select('id');

        $rows = OrderItem::query()
            ->whereIn('order_id', $orderIdsQuery)
            ->select('product_id')
            ->selectRaw('SUM(quantity) as quantity_sold')
            ->selectRaw('COALESCE(SUM(subtotal), 0) as gross_sales')
            ->groupBy('product_id')
            ->orderBy('gross_sales', 'desc')
            ->limit(10)
            ->get();

        $productsById = Product::whereIn('id', $rows->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        return $rows
            ->map(fn ($r) => [
                'product_name' => (string) ($productsById[$r->product_id]->name ?? 'Unknown'),
                'quantity_sold' => (int) $r->quantity_sold,
                'gross_sales' => (float) $r->gross_sales,
            ])
            ->all();
    }

    public function render()
    {
        return view('livewire.sales-report');
    }
}
