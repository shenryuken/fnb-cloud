<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\Shift;
use App\Models\Voucher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

#[Title('Orders')]
#[Lazy]
class Orders extends Component
{
    use WithPagination;
    use WithFileUploads;

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

    public bool $showRestoreModal = false;
    public ?UploadedFile $restoreFile = null;
    public string $restorePassword = '';
    public string $restoreConfirm = '';

    public array $selectedOrderIds = [];

    public bool $showDeleteModal = false;
    public array $deleteOrderIds = [];
    public bool $deleteWithBackup = true;
    public string $deletePassword = '';
    public string $deleteConfirm = '';

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
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatingOrderTypeFilter(): void
    {
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->clearSelection();
        $this->datePreset = '';
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->clearSelection();
        $this->datePreset = '';
        $this->resetPage();
    }

    public function setDatePreset(string $preset): void
    {
        $this->clearSelection();
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
        $this->clearSelection();
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

    private function createOrdersBackupForIds(array $orderIds): array
    {
        $orderIds = array_values(array_unique(array_map('intval', $orderIds)));
        sort($orderIds);

        $tenant = auth()->user()?->tenant;
        $tenantId = (int) (auth()->user()?->tenant_id ?? 0);
        $tenantSlug = $tenant?->slug ?: ('tenant_' . $tenantId);

        $dir = 'backups/orders/' . $tenantSlug;
        Storage::disk('local')->makeDirectory($dir);

        $fileName = 'orders_backup_selection_' . $tenantSlug . '_' . count($orderIds) . '_' . now()->format('Ymd_His') . '.jsonl';
        $relativePath = $dir . '/' . $fileName;
        $fullPath = storage_path('app/' . $relativePath);

        $handle = fopen($fullPath, 'wb');

        $meta = [
            'type' => 'orders_backup',
            'tenant_id' => $tenantId,
            'selection' => [
                'count' => count($orderIds),
                'first_id' => $orderIds[0] ?? null,
                'last_id' => $orderIds[count($orderIds) - 1] ?? null,
            ],
            'created_at' => now()->toDateTimeString(),
        ];

        fwrite($handle, json_encode($meta, JSON_UNESCAPED_UNICODE) . "\n");

        if (!empty($orderIds)) {
            Order::query()
                ->whereIn('id', $orderIds)
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
        }

        fclose($handle);

        return [$relativePath, $fileName];
    }

    private function clearSelection(): void
    {
        $this->selectedOrderIds = [];
    }

    private function getCurrentPageOrderIds(int $perPage = 10): array
    {
        return $this->ordersIdQuery()
            ->paginate($perPage)
            ->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function selectAllOnPage(): void
    {
        if (!$this->canResetOrders) {
            abort(403);
        }

        $this->selectedOrderIds = $this->getCurrentPageOrderIds();
    }

    public function clearSelectedOrders(): void
    {
        $this->selectedOrderIds = [];
    }

    #[Computed]
    public function selectedOrdersCount(): int
    {
        return count($this->selectedOrderIds);
    }

    public function openDeleteOrders(?int $orderId = null): void
    {
        if (!$this->canResetOrders) {
            abort(403);
        }

        $ids = [];
        if ($orderId) {
            $ids = [$orderId];
        } else {
            $ids = array_values(array_unique(array_map('intval', $this->selectedOrderIds)));
        }

        if (empty($ids)) {
            $this->dispatch('notify', message: 'Select at least one order first.', type: 'warning');
            return;
        }

        $this->deleteOrderIds = $ids;
        $this->deleteWithBackup = true;
        $this->deletePassword = '';
        $this->deleteConfirm = '';

        $this->showDeleteModal = true;
        $this->dispatch('modal:open', name: 'orders-delete');
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->reset(['deleteOrderIds', 'deleteWithBackup', 'deletePassword', 'deleteConfirm']);
        $this->dispatch('modal:close', name: 'orders-delete');
    }

    #[Computed]
    public function deleteOrdersCount(): int
    {
        if (!$this->showDeleteModal || !$this->canResetOrders) {
            return 0;
        }

        $ids = array_values(array_unique(array_map('intval', $this->deleteOrderIds)));
        if (empty($ids)) {
            return 0;
        }

        return (int) Order::query()->whereIn('id', $ids)->count();
    }

    public function confirmDeleteOrders()
    {
        if (!$this->canResetOrders) {
            abort(403);
        }

        $this->validate([
            'deleteOrderIds' => 'required|array|min:1',
            'deletePassword' => 'required|string|min:1',
            'deleteConfirm' => 'required|string',
            'deleteWithBackup' => 'boolean',
        ]);

        if (!Hash::check((string) $this->deletePassword, (string) auth()->user()->password)) {
            $this->addError('deletePassword', 'Incorrect password.');
            return;
        }

        if (trim((string) $this->deleteConfirm) !== 'DELETE') {
            $this->addError('deleteConfirm', 'Type DELETE to confirm.');
            return;
        }

        $orderIds = array_values(array_unique(array_map('intval', $this->deleteOrderIds)));
        if (empty($orderIds)) {
            $this->dispatch('notify', message: 'No orders selected for delete.', type: 'warning');
            return;
        }

        $backupRelativePath = null;
        $backupFileName = null;
        if ($this->deleteWithBackup) {
            [$backupRelativePath, $backupFileName] = $this->createOrdersBackupForIds($orderIds);
        }

        $shiftIds = [];
        $customerIds = [];
        $voucherIds = [];

        Order::query()
            ->whereIn('id', $orderIds)
            ->select(['id', 'shift_id', 'customer_id', 'voucher_id'])
            ->orderBy('id')
            ->chunkById(500, function ($orders) use (&$shiftIds, &$customerIds, &$voucherIds) {
                foreach ($orders as $o) {
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

        $affectedTables = RestaurantTable::query()
            ->whereIn('current_order_id', $orderIds)
            ->get();

        $deletedCount = 0;
        DB::transaction(function () use ($orderIds, &$deletedCount) {
            $deletedCount = (int) Order::query()->whereIn('id', $orderIds)->count();
            Order::query()->whereIn('id', $orderIds)->delete();
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
            'action' => 'orders.delete',
            'subject_type' => 'orders',
            'subject_id' => null,
            'meta' => [
                'deleted_orders' => $deletedCount,
                'order_ids' => array_slice($orderIds, 0, 500),
                'backup_file' => $backupFileName,
                'backup_path' => $backupRelativePath,
            ],
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->closeDeleteModal();
        $this->clearSelection();
        $this->resetPage();
        $this->dispatch('notify', message: 'Deleted ' . $deletedCount . ' order(s).', type: 'warning');

        if ($backupRelativePath && $backupFileName) {
            return response()->download(storage_path('app/' . $backupRelativePath), $backupFileName, [
                'Content-Type' => 'application/x-ndjson',
            ]);
        }
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

    public function openRestoreModal(): void
    {
        if (!$this->canResetOrders) {
            abort(403);
        }

        $this->restoreFile = null;
        $this->restorePassword = '';
        $this->restoreConfirm = '';

        $this->showRestoreModal = true;
        $this->dispatch('modal:open', name: 'orders-restore');
    }

    public function closeRestoreModal(): void
    {
        $this->showRestoreModal = false;
        $this->reset(['restoreFile', 'restorePassword', 'restoreConfirm']);
        $this->dispatch('modal:close', name: 'orders-restore');
    }

    private function safeForeignId(?int $id, string $table): ?int
    {
        if (!$id) {
            return null;
        }

        $exists = DB::table($table)->where('id', $id)->exists();
        return $exists ? $id : null;
    }

    public function confirmRestoreOrders(): void
    {
        if (!$this->canResetOrders) {
            abort(403);
        }

        $this->validate([
            'restoreFile' => 'required|file|max:51200',
            'restorePassword' => 'required|string|min:1',
            'restoreConfirm' => 'required|string',
        ]);

        if (!Hash::check((string) $this->restorePassword, (string) auth()->user()->password)) {
            $this->addError('restorePassword', 'Incorrect password.');
            return;
        }

        if (trim((string) $this->restoreConfirm) !== 'RESTORE') {
            $this->addError('restoreConfirm', 'Type RESTORE to confirm.');
            return;
        }

        $path = $this->restoreFile?->getRealPath();
        if (!$path || !is_file($path)) {
            $this->addError('restoreFile', 'Upload failed. Please try again.');
            return;
        }

        $file = new \SplFileObject($path, 'rb');
        $file->setFlags(\SplFileObject::DROP_NEW_LINE);

        $firstLine = $file->fgets();
        $meta = json_decode((string) $firstLine, true);
        if (!is_array($meta) || ($meta['type'] ?? null) !== 'orders_backup') {
            $this->addError('restoreFile', 'Invalid backup file.');
            return;
        }

        $tenantId = (int) (auth()->user()?->tenant_id ?? 0);
        if ((int) ($meta['tenant_id'] ?? 0) !== $tenantId) {
            $this->addError('restoreFile', 'This backup belongs to a different tenant.');
            return;
        }

        $createdOrders = 0;
        $shiftIds = [];
        $customerIds = [];
        $voucherIds = [];

        DB::transaction(function () use ($file, &$createdOrders, &$shiftIds, &$customerIds, &$voucherIds) {
            $orderFillable = (new Order())->getFillable();
            $itemFillable = (new \App\Models\OrderItem())->getFillable();
            $addonFillable = (new \App\Models\OrderItemAddon())->getFillable();
            $componentFillable = (new \App\Models\OrderItemComponent())->getFillable();

            while (!$file->eof()) {
                $line = trim((string) $file->fgets());
                if ($line === '') {
                    continue;
                }

                $data = json_decode($line, true);
                if (!is_array($data)) {
                    continue;
                }

                $orderAttrs = array_intersect_key($data, array_flip($orderFillable));
                unset($orderAttrs['tenant_id'], $orderAttrs['id']);

                $orderAttrs['shift_id'] = $this->safeForeignId(isset($orderAttrs['shift_id']) ? (int) $orderAttrs['shift_id'] : null, 'shifts');
                $orderAttrs['customer_id'] = $this->safeForeignId(isset($orderAttrs['customer_id']) ? (int) $orderAttrs['customer_id'] : null, 'customers');
                $orderAttrs['user_id'] = $this->safeForeignId(isset($orderAttrs['user_id']) ? (int) $orderAttrs['user_id'] : null, 'users');
                $orderAttrs['voucher_id'] = $this->safeForeignId(isset($orderAttrs['voucher_id']) ? (int) $orderAttrs['voucher_id'] : null, 'vouchers');
                $orderAttrs['table_id'] = $this->safeForeignId(isset($orderAttrs['table_id']) ? (int) $orderAttrs['table_id'] : null, 'restaurant_tables');

                $order = new Order();
                $order->fill($orderAttrs);
                if (!empty($data['created_at'])) {
                    $order->created_at = Carbon::parse((string) $data['created_at']);
                }
                if (!empty($data['updated_at'])) {
                    $order->updated_at = Carbon::parse((string) $data['updated_at']);
                }
                $order->save();

                $createdOrders++;

                if ($order->shift_id) {
                    $shiftIds[] = (int) $order->shift_id;
                }
                if ($order->customer_id) {
                    $customerIds[] = (int) $order->customer_id;
                }
                if ($order->voucher_id) {
                    $voucherIds[] = (int) $order->voucher_id;
                }

                $items = $data['items'] ?? [];
                if (!is_array($items)) {
                    $items = [];
                }

                foreach ($items as $itemData) {
                    if (!is_array($itemData)) {
                        continue;
                    }

                    $itemAttrs = array_intersect_key($itemData, array_flip($itemFillable));
                    unset($itemAttrs['tenant_id'], $itemAttrs['id']);
                    $itemAttrs['order_id'] = $order->id;

                    $productId = isset($itemAttrs['product_id']) ? (int) $itemAttrs['product_id'] : null;
                    if (!$productId || !DB::table('products')->where('id', $productId)->exists()) {
                        throw new \RuntimeException('Missing product for restored order item.');
                    }

                    if (!empty($itemAttrs['variant_id'])) {
                        $itemAttrs['variant_id'] = $this->safeForeignId((int) $itemAttrs['variant_id'], 'product_variants');
                    }

                    $orderItem = new \App\Models\OrderItem();
                    $orderItem->fill($itemAttrs);
                    if (!empty($itemData['created_at'])) {
                        $orderItem->created_at = Carbon::parse((string) $itemData['created_at']);
                    }
                    if (!empty($itemData['updated_at'])) {
                        $orderItem->updated_at = Carbon::parse((string) $itemData['updated_at']);
                    }
                    $orderItem->save();

                    $addons = $itemData['addons'] ?? [];
                    if (is_array($addons)) {
                        foreach ($addons as $addonData) {
                            if (!is_array($addonData)) {
                                continue;
                            }

                            $addonAttrs = array_intersect_key($addonData, array_flip($addonFillable));
                            unset($addonAttrs['tenant_id'], $addonAttrs['id']);
                            $addonAttrs['order_item_id'] = $orderItem->id;

                            $addonId = isset($addonAttrs['addon_id']) ? (int) $addonAttrs['addon_id'] : null;
                            if (!$addonId || !DB::table('product_addons')->where('id', $addonId)->exists()) {
                                throw new \RuntimeException('Missing addon for restored order item.');
                            }

                            $orderItemAddon = new \App\Models\OrderItemAddon();
                            $orderItemAddon->fill($addonAttrs);
                            if (!empty($addonData['created_at'])) {
                                $orderItemAddon->created_at = Carbon::parse((string) $addonData['created_at']);
                            }
                            if (!empty($addonData['updated_at'])) {
                                $orderItemAddon->updated_at = Carbon::parse((string) $addonData['updated_at']);
                            }
                            $orderItemAddon->save();
                        }
                    }

                    $components = $itemData['components'] ?? [];
                    if (is_array($components)) {
                        foreach ($components as $componentData) {
                            if (!is_array($componentData)) {
                                continue;
                            }

                            $componentAttrs = array_intersect_key($componentData, array_flip($componentFillable));
                            unset($componentAttrs['tenant_id'], $componentAttrs['id']);
                            $componentAttrs['order_item_id'] = $orderItem->id;

                            if (!empty($componentAttrs['product_id'])) {
                                $componentAttrs['product_id'] = $this->safeForeignId((int) $componentAttrs['product_id'], 'products');
                            }

                            $orderItemComponent = new \App\Models\OrderItemComponent();
                            $orderItemComponent->fill($componentAttrs);
                            if (!empty($componentData['created_at'])) {
                                $orderItemComponent->created_at = Carbon::parse((string) $componentData['created_at']);
                            }
                            if (!empty($componentData['updated_at'])) {
                                $orderItemComponent->updated_at = Carbon::parse((string) $componentData['updated_at']);
                            }
                            $orderItemComponent->save();
                        }
                    }
                }
            }
        });

        $shiftIds = array_values(array_unique($shiftIds));
        $customerIds = array_values(array_unique($customerIds));
        $voucherIds = array_values(array_unique($voucherIds));

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
            'action' => 'orders.restore',
            'subject_type' => 'orders',
            'subject_id' => null,
            'meta' => [
                'restored_orders' => (int) $createdOrders,
                'file_name' => $this->restoreFile?->getClientOriginalName(),
            ],
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->closeRestoreModal();
        $this->resetPage();
        $this->dispatch('notify', message: 'Restore completed. Restored ' . (int) $createdOrders . ' order(s).', type: 'success');
    }

    private function applyOrderFilters(Builder $query): Builder
    {
        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('table_number', 'like', $term)
                    ->orWhere('voucher_code', 'like', $term)
                    ->orWhereHas('customer', fn ($c) => $c
                        ->where('name', 'like', $term)
                        ->orWhere('mobile', 'like', $term)
                        ->orWhere('email', 'like', $term));
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

        return $query;
    }

    private function ordersListQuery(): Builder
    {
        return $this->applyOrderFilters(
            Order::query()
                ->with(['items.product', 'user', 'customer'])
                ->latest()
        );
    }

    private function ordersIdQuery(): Builder
    {
        return $this->applyOrderFilters(
            Order::query()
                ->select(['id'])
                ->latest()
        );
    }

    public function render()
    {
        $hasActiveFilters = $this->search !== ''
            || $this->statusFilter !== ''
            || $this->orderTypeFilter !== ''
            || $this->dateFrom !== ''
            || $this->dateTo !== '';

        return view('livewire.orders', [
            'orders'           => $this->ordersListQuery()->paginate(10),
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
