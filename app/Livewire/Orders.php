<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\Shift;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;

#[Title('Orders')]
#[Lazy]
class Orders extends Component
{
    use WithPagination;

    public bool $showOrderModal = false;
    public ?Order $viewingOrder = null;

    public string $search = '';
    public string $statusFilter = '';
    public string $orderTypeFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $datePreset = '';

    public bool $showResetModal = false;
    public string $resetPreset = 'today';
    public string $resetFrom = '';
    public string $resetTo = '';
    public bool $resetWithBackup = true;
    public string $resetPassword = '';
    public string $resetConfirm = '';

    protected $queryString = [
        'search'          => ['except' => ''],
        'statusFilter'    => ['except' => '', 'as' => 'status'],
        'orderTypeFilter' => ['except' => '', 'as' => 'type'],
        'dateFrom'        => ['except' => '', 'as' => 'from'],
        'dateTo'          => ['except' => '', 'as' => 'to'],
        'datePreset'      => ['except' => '', 'as' => 'range'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingOrderTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->datePreset = '';
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->datePreset = '';
        $this->resetPage();
    }

    public function setDatePreset(string $preset): void
    {
        $preset = trim($preset);
        $now = Carbon::now();

        if ($preset === 'today') {
            $this->dateFrom = $now->toDateString();
            $this->dateTo = $now->toDateString();
            $this->datePreset = 'today';
        } elseif ($preset === 'week') {
            $this->dateFrom = $now->copy()->startOfWeek()->toDateString();
            $this->dateTo = $now->copy()->endOfWeek()->toDateString();
            $this->datePreset = 'week';
        } elseif ($preset === 'month') {
            $this->dateFrom = $now->copy()->startOfMonth()->toDateString();
            $this->dateTo = $now->copy()->endOfMonth()->toDateString();
            $this->datePreset = 'month';
        } else {
            $this->dateFrom = '';
            $this->dateTo = '';
            $this->datePreset = '';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search          = '';
        $this->statusFilter    = '';
        $this->orderTypeFilter = '';
        $this->dateFrom        = '';
        $this->dateTo          = '';
        $this->datePreset      = '';
        $this->resetPage();
    }

    public function updateStatus(Order $order, string $status): void
    {
        if (in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
            $order->update(['status' => $status]);
            
            // Clean up table when order is cancelled or completed
            if (in_array($status, ['cancelled', 'completed']) && $order->table_id) {
                $table = \App\Models\RestaurantTable::find($order->table_id);
                if ($table && $table->current_order_id === $order->id) {
                    $table->markDirty();
                }
            }
            
            $this->dispatch('order-updated');
        }
    }
    
    public function updateKdsStatus(Order $order, string $kdsStatus): void
    {
        if (in_array($kdsStatus, ['pending', 'preparing', 'ready', 'served'])) {
            $order->update(['kds_status' => $kdsStatus]);
            
            // Auto-complete order when served and paid
            if ($kdsStatus === 'served' && $order->payment_status === 'paid' && $order->status === 'processing') {
                $order->update(['status' => 'completed']);
            }
            
            $this->dispatch('order-updated');
        }
    }

    public function openOrder(Order $order): void
    {
        $this->viewingOrder = $order->load([
            'items.product',
            'items.variant',
            'items.addons',
            'items.components',
            'customer',
            'user',
        ]);
        $this->showOrderModal = true;
        $this->dispatch('modal:open', name: 'order-detail');
    }

    public function closeOrder(): void
    {
        $this->showOrderModal = false;
        $this->viewingOrder = null;
        $this->dispatch('modal:close', name: 'order-detail');
    }

    #[Computed]
    public function canResetOrders(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if (is_null($user->tenant_id)) {
            return true;
        }

        if (!$user->hasPermission('orders.reset')) {
            return false;
        }

        return $user->roles()->where('slug', 'owner')->exists();
    }

    public function openResetModal(): void
    {
        if (!$this->canResetOrders) {
            abort(403);
        }

        $this->resetPassword = '';
        $this->resetConfirm = '';
        $this->resetWithBackup = true;

        if (filled($this->dateFrom) || filled($this->dateTo)) {
            $this->resetPreset = 'filtered';
            $this->resetFrom = filled($this->dateFrom) ? $this->dateFrom : Carbon::now()->toDateString();
            $this->resetTo = filled($this->dateTo) ? $this->dateTo : Carbon::now()->toDateString();
        } else {
            $this->setResetPreset('today');
        }

        $this->showResetModal = true;
        $this->dispatch('modal:open', name: 'orders-reset');
    }

    public function closeResetModal(): void
    {
        $this->showResetModal = false;
        $this->reset(['resetPreset', 'resetFrom', 'resetTo', 'resetWithBackup', 'resetPassword', 'resetConfirm']);
        $this->resetPreset = 'today';
        $this->dispatch('modal:close', name: 'orders-reset');
    }

    public function setResetPreset(string $preset): void
    {
        $preset = trim($preset);
        $now = Carbon::now();

        if ($preset === 'today') {
            $this->resetPreset = 'today';
            $this->resetFrom = $now->toDateString();
            $this->resetTo = $now->toDateString();
            return;
        }

        if ($preset === 'week') {
            $this->resetPreset = 'week';
            $this->resetFrom = $now->copy()->startOfWeek()->toDateString();
            $this->resetTo = $now->copy()->endOfWeek()->toDateString();
            return;
        }

        if ($preset === 'month') {
            $this->resetPreset = 'month';
            $this->resetFrom = $now->copy()->startOfMonth()->toDateString();
            $this->resetTo = $now->copy()->endOfMonth()->toDateString();
            return;
        }

        if ($preset === 'filtered') {
            $this->resetPreset = 'filtered';
            $this->resetFrom = filled($this->dateFrom) ? $this->dateFrom : $now->toDateString();
            $this->resetTo = filled($this->dateTo) ? $this->dateTo : $now->toDateString();
            return;
        }

        $this->resetPreset = 'custom';
        if (!filled($this->resetFrom)) {
            $this->resetFrom = $now->toDateString();
        }
        if (!filled($this->resetTo)) {
            $this->resetTo = $now->toDateString();
        }
    }

    private function resolveResetRange(): array
    {
        $from = Carbon::parse($this->resetFrom)->startOfDay();
        $to = Carbon::parse($this->resetTo)->endOfDay();

        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    #[Computed]
    public function resetOrdersCount(): int
    {
        if (!$this->showResetModal || !$this->canResetOrders) {
            return 0;
        }

        if (!filled($this->resetFrom) || !filled($this->resetTo)) {
            return 0;
        }

        try {
            [$from, $to] = $this->resolveResetRange();
        } catch (\Throwable) {
            return 0;
        }

        return Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    private function createOrdersBackup(Carbon $from, Carbon $to): array
    {
        $tenant = auth()->user()?->tenant;
        $tenantId = (int) (auth()->user()?->tenant_id ?? 0);
        $tenantSlug = $tenant?->slug ?: ('tenant_' . $tenantId);

        $dir = 'backups/orders/' . $tenantSlug;
        Storage::disk('local')->makeDirectory($dir);

        $fileName = 'orders_backup_' . $tenantSlug . '_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '_' . now()->format('Ymd_His') . '.jsonl';
        $relativePath = $dir . '/' . $fileName;
        $fullPath = storage_path('app/' . $relativePath);

        $handle = fopen($fullPath, 'wb');

        $meta = [
            'type' => 'orders_backup',
            'tenant_id' => $tenantId,
            'range' => [
                'from' => $from->toDateTimeString(),
                'to' => $to->toDateTimeString(),
            ],
            'created_at' => now()->toDateTimeString(),
        ];

        fwrite($handle, json_encode($meta, JSON_UNESCAPED_UNICODE) . "\n");

        Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->with([
                'items',
                'items.product',
                'items.variant',
                'items.addons',
                'items.components',
                'customer',
                'user',
                'shift',
                'voucher',
                'table',
            ])
            ->orderBy('id')
            ->chunkById(100, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    fwrite($handle, json_encode($order->toArray(), JSON_UNESCAPED_UNICODE) . "\n");
                }
            });

        fclose($handle);

        return [$relativePath, $fileName];
    }

    public function confirmResetOrders()
    {
        if (!$this->canResetOrders) {
            abort(403);
        }

        $this->validate([
            'resetFrom' => 'required|date',
            'resetTo' => 'required|date',
            'resetPassword' => 'required|string|min:1',
            'resetConfirm' => 'required|string',
            'resetWithBackup' => 'boolean',
        ]);

        if (!Hash::check((string) $this->resetPassword, (string) auth()->user()->password)) {
            $this->addError('resetPassword', 'Incorrect password.');
            return;
        }

        if (trim((string) $this->resetConfirm) !== 'DELETE') {
            $this->addError('resetConfirm', 'Type DELETE to confirm.');
            return;
        }

        [$from, $to] = $this->resolveResetRange();

        $backupRelativePath = null;
        $backupFileName = null;
        if ($this->resetWithBackup) {
            [$backupRelativePath, $backupFileName] = $this->createOrdersBackup($from, $to);
        }

        $shiftIds = [];
        $customerIds = [];
        $voucherIds = [];
        $orderIds = [];

        Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->select(['id', 'shift_id', 'customer_id', 'voucher_id'])
            ->orderBy('id')
            ->chunkById(500, function ($orders) use (&$shiftIds, &$customerIds, &$voucherIds, &$orderIds) {
                foreach ($orders as $o) {
                    $orderIds[] = (int) $o->id;
                    if ($o->shift_id) {
                        $shiftIds[] = (int) $o->shift_id;
                    }
                    if ($o->customer_id) {
                        $customerIds[] = (int) $o->customer_id;
                    }
                    if ($o->voucher_id) {
                        $voucherIds[] = (int) $o->voucher_id;
                    }
                }
            });

        $shiftIds = array_values(array_unique($shiftIds));
        $customerIds = array_values(array_unique($customerIds));
        $voucherIds = array_values(array_unique($voucherIds));
        $orderIds = array_values(array_unique($orderIds));

        $affectedTables = [];
        if (!empty($orderIds)) {
            $affectedTables = RestaurantTable::query()
                ->whereIn('current_order_id', $orderIds)
                ->get();
        }

        $deletedCount = 0;
        DB::transaction(function () use ($from, $to, &$deletedCount) {
            $deletedCount = (int) Order::query()
                ->whereBetween('created_at', [$from, $to])
                ->count();

            Order::query()
                ->whereBetween('created_at', [$from, $to])
                ->delete();
        });

        foreach ($affectedTables as $table) {
            $table->markDirty();
        }

        if (!empty($shiftIds)) {
            Shift::query()
                ->whereIn('id', $shiftIds)
                ->get()
                ->each(fn (Shift $s) => $s->recalculateSales());
        }

        if (!empty($customerIds)) {
            $balances = Order::query()
                ->whereIn('customer_id', $customerIds)
                ->where('payment_status', 'paid')
                ->where('status', 'completed')
                ->selectRaw('customer_id, COALESCE(SUM(points_earned - points_redeemed), 0) as balance')
                ->groupBy('customer_id')
                ->pluck('balance', 'customer_id')
                ->toArray();

            Customer::query()
                ->whereIn('id', $customerIds)
                ->get()
                ->each(function (Customer $c) use ($balances) {
                    $balance = (int) ($balances[$c->id] ?? 0);
                    $c->update(['points_balance' => max(0, $balance)]);
                });
        }

        if (!empty($voucherIds)) {
            $counts = Order::query()
                ->whereIn('voucher_id', $voucherIds)
                ->where('payment_status', 'paid')
                ->selectRaw('voucher_id, COUNT(*) as usage_count')
                ->groupBy('voucher_id')
                ->pluck('usage_count', 'voucher_id')
                ->toArray();

            Voucher::query()
                ->whereIn('id', $voucherIds)
                ->get()
                ->each(function (Voucher $v) use ($counts) {
                    $usage = (int) ($counts[$v->id] ?? 0);
                    $v->update(['usage_count' => max(0, $usage)]);
                });
        }

        AuditLog::create([
            'tenant_id' => auth()->user()?->tenant_id,
            'actor_user_id' => auth()->id(),
            'action' => 'orders.reset',
            'subject_type' => 'orders',
            'subject_id' => null,
            'meta' => [
                'from' => $from->toDateTimeString(),
                'to' => $to->toDateTimeString(),
                'deleted_orders' => $deletedCount,
                'backup_file' => $backupFileName,
                'backup_path' => $backupRelativePath,
            ],
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->closeResetModal();
        $this->resetPage();
        $this->dispatch('notify', message: 'Orders reset completed. Deleted ' . $deletedCount . ' order(s).', type: 'warning');

        if ($backupRelativePath && $backupFileName) {
            return response()->download(storage_path('app/' . $backupRelativePath), $backupFileName, [
                'Content-Type' => 'application/x-ndjson',
            ]);
        }
    }

    public function render()
    {
        $query = Order::with(['items.product', 'user', 'customer'])
            ->latest();

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                  ->orWhere('table_number', 'like', $term)
                  ->orWhere('voucher_code', 'like', $term)
                  ->orWhereHas('customer', fn ($c) =>
                      $c->where('name', 'like', $term)
                        ->orWhere('mobile', 'like', $term)
                        ->orWhere('email', 'like', $term)
                  );
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->orderTypeFilter !== '') {
            $query->where('order_type', $this->orderTypeFilter);
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $hasActiveFilters = $this->search !== ''
            || $this->statusFilter !== ''
            || $this->orderTypeFilter !== ''
            || $this->dateFrom !== ''
            || $this->dateTo !== '';

        return view('livewire.orders', [
            'orders'           => $query->paginate(10),
            'hasActiveFilters' => $hasActiveFilters,
        ]);
    }

    public function placeholder()
    {        return <<<'HTML'
        <div class="p-6 space-y-4">
            <div class="h-8 bg-neutral-200 dark:bg-neutral-700 rounded w-1/4 animate-pulse"></div>
            <div class="h-64 bg-neutral-100 dark:bg-neutral-800 rounded animate-pulse"></div>
        </div>
        HTML;
    }
}
