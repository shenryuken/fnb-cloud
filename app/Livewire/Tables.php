<?php

namespace App\Livewire;

use App\Models\RestaurantTable;
use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class Tables extends Component
{
    // View mode: 'floor' or 'list'
    public string $viewMode = 'floor';
    
    // Filter
    public string $filterFloor = '';
    public string $filterStatus = '';
    
    // Table form
    public bool $showTableModal = false;
    public ?int $editingTableId = null;
    public string $createMode = 'single'; // 'single' or 'bulk'
    public string $tableName = '';
    public string $tableCode = '';
    public int $tableCapacity = 4;
    public string $tableShape = 'square';
    public string $tableFloor = '';
    public int $tablePositionX = 0;
    public int $tablePositionY = 0;
    public int $tableWidth = 1;
    public int $tableHeight = 1;
    public bool $tableIsActive = true;

    // Bulk creation
    public string $bulkRange = '';
    public int $bulkCapacity = 4;
    public string $bulkShape = 'square';
    public string $bulkFloor = '';
    public bool $bulkIsActive = true;
    public array $bulkPreview = [];
    
    // Reservation modal
    public bool $showReservationModal = false;
    public ?int $reservingTableId = null;
    public string $reservationName = '';
    public string $reservationPhone = '';
    public string $reservationNotes = '';
    
    // Merge modal
    public bool $showMergeModal = false;
    public ?int $primaryTableId = null;
    public array $selectedMergeTables = [];
    
    // Table details modal
    public bool $showDetailsModal = false;
    public ?int $detailsTableId = null;
    
    // Order details modal
    public bool $showOrderModal = false;
    public ?Order $viewingOrder = null;
    
    // Void/Clear table modal (for tables with unpaid orders)
    public bool $showVoidModal = false;
    public ?int $voidTableId = null;
    public string $voidReason = '';
    public string $voidNotes = '';
    public string $managerPin = '';
    public array $voidReasons = [
        'customer_left' => 'Customer Left Without Paying',
        'order_error' => 'Order Entry Error',
        'food_quality' => 'Food Quality Issue / Comp',
        'manager_comp' => 'Manager Complimentary',
        'test_order' => 'Test Order',
        'duplicate' => 'Duplicate Order',
        'other' => 'Other (specify in notes)',
    ];

    public function mount()
    {
        // Default view
    }

    #[Computed]
    public function tables()
    {
        $query = RestaurantTable::with(['currentOrder', 'activeOrders'])
            ->where('is_active', true)
            ->whereNull('merged_into_id');
        
        if ($this->filterFloor) {
            $query->where('floor', $this->filterFloor);
        }
        
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }
        
        return $query->orderBy('sort_order')->orderBy('name')->get();
    }

    #[Computed]
    public function floors()
    {
        return RestaurantTable::where('is_active', true)
            ->whereNotNull('floor')
            ->distinct()
            ->pluck('floor')
            ->filter()
            ->values();
    }

    #[Computed]
    public function tableStats()
    {
        $tables = RestaurantTable::where('is_active', true)
            ->whereNull('merged_into_id')
            ->get();
        
        return [
            'total' => $tables->count(),
            'available' => $tables->where('status', 'available')->count(),
            'occupied' => $tables->where('status', 'occupied')->count(),
            'reserved' => $tables->where('status', 'reserved')->count(),
            'dirty' => $tables->where('status', 'dirty')->count(),
        ];
    }

    #[Computed]
    public function allTables()
    {
        return RestaurantTable::orderBy('sort_order')->orderBy('name')->get();
    }

    /**
     * Parse a bulk range string into an array of table names.
     * Supports: "1-10" → Table 1..10, "T1-T10" → T1..T10, "ML1-ML5" → ML1..ML5
     */
    public function parseBulkRange(string $range): array
    {
        $range = trim($range);
        if (!$range) return [];

        // Match pattern: optional prefix + number - optional same prefix + number
        // e.g. "1-10", "T1-T10", "ML1-ML5", "A01-A20"
        if (!preg_match('/^([a-zA-Z]*)(\d+)\s*-\s*([a-zA-Z]*)(\d+)$/', $range, $m)) {
            return [];
        }

        [, $prefixStart, $numStart, $prefixEnd, $numEnd] = $m;

        // Prefixes must match (or end prefix empty meaning same as start)
        $prefix = $prefixStart;
        if ($prefixEnd && $prefixEnd !== $prefixStart) {
            return []; // mismatched prefixes
        }

        $start = (int) $numStart;
        $end   = (int) $numEnd;

        if ($start > $end || ($end - $start) > 200) return []; // safety cap at 200

        $tables = [];
        $padLen = strlen($numStart) > 1 && $numStart[0] === '0' ? strlen($numStart) : 0;

        for ($i = $start; $i <= $end; $i++) {
            $num = $padLen ? str_pad($i, $padLen, '0', STR_PAD_LEFT) : (string) $i;
            $tables[] = $prefix . $num;
        }

        return $tables;
    }

    public function updatedBulkRange(): void
    {
        $this->bulkPreview = $this->parseBulkRange($this->bulkRange);
    }

    // Table CRUD
    public function create()
    {
        $this->resetTableForm();
        $this->showTableModal = true;
    }

    public function edit(int $id)
    {
        $table = RestaurantTable::findOrFail($id);
        $this->editingTableId = $table->id;
        $this->tableName = $table->name;
        $this->tableCode = $table->code ?? '';
        $this->tableCapacity = $table->capacity;
        $this->tableShape = $table->shape;
        $this->tableFloor = $table->floor ?? '';
        $this->tablePositionX = $table->position_x;
        $this->tablePositionY = $table->position_y;
        $this->tableWidth = $table->width;
        $this->tableHeight = $table->height;
        $this->tableIsActive = $table->is_active;
        $this->showTableModal = true;
    }

    public function saveTable()
    {
        if ($this->createMode === 'bulk' && !$this->editingTableId) {
            return $this->saveBulkTables();
        }

        $this->validate([
            'tableName' => 'required|string|max:255',
            'tableCapacity' => 'required|integer|min:1|max:50',
            'tableShape' => 'required|in:square,rectangle,circle,oval',
        ]);

        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->tableName,
            'code' => $this->tableCode ?: null,
            'capacity' => $this->tableCapacity,
            'shape' => $this->tableShape,
            'floor' => $this->tableFloor ?: null,
            'position_x' => $this->tablePositionX,
            'position_y' => $this->tablePositionY,
            'width' => $this->tableWidth,
            'height' => $this->tableHeight,
            'is_active' => $this->tableIsActive,
        ];

        if ($this->editingTableId) {
            RestaurantTable::findOrFail($this->editingTableId)->update($data);
            $this->dispatch('notify', message: 'Table updated successfully', type: 'success');
        } else {
            RestaurantTable::create($data);
            $this->dispatch('notify', message: 'Table created successfully', type: 'success');
        }

        $this->showTableModal = false;
        $this->resetTableForm();
    }

    public function saveBulkTables()
    {
        $this->validate([
            'bulkRange'    => 'required|string',
            'bulkCapacity' => 'required|integer|min:1|max:50',
            'bulkShape'    => 'required|in:square,rectangle,circle,oval',
        ]);

        $names = $this->parseBulkRange($this->bulkRange);

        if (empty($names)) {
            $this->addError('bulkRange', 'Invalid range format. Use e.g. T1-T10 or 1-20 or ML1-ML5.');
            return;
        }

        $tenantId = auth()->user()->tenant_id;
        $created  = 0;

        foreach ($names as $name) {
            // Skip if a table with this name already exists for the tenant
            if (RestaurantTable::where('tenant_id', $tenantId)->where('name', $name)->exists()) {
                continue;
            }

            RestaurantTable::create([
                'tenant_id'  => $tenantId,
                'name'       => $name,
                'code'       => $name,
                'capacity'   => $this->bulkCapacity,
                'shape'      => $this->bulkShape,
                'floor'      => $this->bulkFloor ?: null,
                'is_active'  => $this->bulkIsActive,
                'position_x' => 0,
                'position_y' => 0,
                'width'      => 1,
                'height'     => 1,
            ]);

            $created++;
        }

        $skipped = count($names) - $created;
        $msg = "Created {$created} table(s) successfully.";
        if ($skipped > 0) {
            $msg .= " {$skipped} skipped (already exist).";
        }

        $this->dispatch('notify', message: $msg, type: 'success');
        $this->showTableModal = false;
        $this->resetTableForm();
    }

    public function deleteTable(int $id)
    {
        $table = RestaurantTable::findOrFail($id);
        
        if ($table->status === 'occupied') {
            $this->dispatch('notify', message: 'Cannot delete an occupied table', type: 'error');
            return;
        }
        
        $table->delete();
        $this->dispatch('notify', message: 'Table deleted successfully', type: 'success');
    }

    private function resetTableForm()
    {
        $this->editingTableId = null;
        $this->createMode = 'single';
        $this->tableName = '';
        $this->tableCode = '';
        $this->tableCapacity = 4;
        $this->tableShape = 'square';
        $this->tableFloor = '';
        $this->tablePositionX = 0;
        $this->tablePositionY = 0;
        $this->tableWidth = 1;
        $this->tableHeight = 1;
        $this->tableIsActive = true;
        $this->bulkRange = '';
        $this->bulkCapacity = 4;
        $this->bulkShape = 'square';
        $this->bulkFloor = '';
        $this->bulkIsActive = true;
        $this->bulkPreview = [];
    }

    // Status changes
    public function setTableStatus(int $tableId, string $status)
    {
        $table = RestaurantTable::with('activeOrders')->findOrFail($tableId);
        
        // If clearing table (marking dirty) and there are unpaid orders, show void modal
        if ($status === 'dirty') {
            $unpaidOrders = $table->activeOrders->where('payment_status', 'unpaid');
            
            if ($unpaidOrders->isNotEmpty()) {
                // Cannot clear table with unpaid orders - show void confirmation modal
                $this->voidTableId = $tableId;
                $this->voidReason = '';
                $this->voidNotes = '';
                $this->managerPin = '';
                $this->showVoidModal = true;
                return;
            }
        }
        
        match($status) {
            'available' => $table->release(),
            'dirty' => $table->markDirty(),
            'occupied' => $table->occupy(),
            default => $table->update(['status' => $status]),
        };
        
        $this->dispatch('notify', message: 'Table status updated', type: 'success');
    }
    
    /**
     * Void all unpaid orders and clear the table.
     * Requires manager PIN and void reason.
     */
    public function confirmVoidAndClearTable()
    {
        $this->validate([
            'voidReason' => 'required|string',
            'voidNotes' => 'nullable|string|max:500',
            'managerPin' => 'required|string|min:4',
        ], [
            'voidReason.required' => 'Please select a reason for voiding.',
            'managerPin.required' => 'Manager PIN is required to void orders.',
            'managerPin.min' => 'PIN must be at least 4 characters.',
        ]);
        
        // Verify manager PIN (check against users with manager/admin/owner role)
        // Use slug field and bypass tenant scope for global roles
        $manager = \App\Models\User::where('pin', $this->managerPin)
            ->whereHas('roles', fn($q) => $q->withoutGlobalScopes()
                ->whereIn('slug', ['admin', 'owner', 'superadmin', 'manager']))
            ->first();
        
        if (!$manager) {
            $this->addError('managerPin', 'Invalid manager PIN.');
            return;
        }
        
        $table = RestaurantTable::with('activeOrders')->findOrFail($this->voidTableId);
        $unpaidOrders = $table->activeOrders->where('payment_status', 'unpaid');
        
        if ($unpaidOrders->isEmpty()) {
            $this->dispatch('notify', message: 'No unpaid orders to void.', type: 'info');
            $this->closeVoidModal();
            return;
        }
        
        $voidedCount = 0;
        $voidedTotal = 0;
        
        DB::transaction(function () use ($unpaidOrders, $manager, &$voidedCount, &$voidedTotal) {
            foreach ($unpaidOrders as $order) {
                $voidedTotal += $order->total_amount;
                
                $order->update([
                    'status' => 'voided',
                    'payment_status' => 'voided',
                    'kds_status' => 'cancelled',
                    'voided_at' => now(),
                    'voided_by' => $manager->id,
                    'void_reason' => $this->voidReasons[$this->voidReason] ?? $this->voidReason,
                    'void_notes' => $this->voidNotes ?: null,
                ]);
                
                $voidedCount++;
            }
        });
        
        // Now clear the table
        $table->markDirty();
        
        // Close modals
        $this->closeVoidModal();
        $this->showDetailsModal = false;
        
        $this->dispatch('notify', 
            message: "Voided {$voidedCount} order(s) totaling RM " . number_format($voidedTotal, 2) . ". Table cleared.", 
            type: 'warning'
        );
    }
    
    public function closeVoidModal()
    {
        $this->showVoidModal = false;
        $this->voidTableId = null;
        $this->voidReason = '';
        $this->voidNotes = '';
        $this->managerPin = '';
        $this->resetValidation();
    }

    // Reservations
    public function openReservationModal(int $tableId)
    {
        $this->reservingTableId = $tableId;
        $this->reservationName = '';
        $this->reservationPhone = '';
        $this->reservationNotes = '';
        $this->showReservationModal = true;
    }

    public function saveReservation()
    {
        $this->validate([
            'reservationName' => 'required|string|max:255',
        ]);

        $table = RestaurantTable::findOrFail($this->reservingTableId);
        $table->reserve($this->reservationName, $this->reservationPhone, $this->reservationNotes);
        
        $this->showReservationModal = false;
        $this->dispatch('notify', message: 'Reservation saved', type: 'success');
    }

    public function cancelReservation(int $tableId)
    {
        $table = RestaurantTable::findOrFail($tableId);
        $table->release();
        $this->dispatch('notify', message: 'Reservation cancelled', type: 'success');
    }

    // Merge tables
    public function openMergeModal(int $tableId)
    {
        $this->primaryTableId = $tableId;
        $this->selectedMergeTables = [];
        $this->showMergeModal = true;
    }

    public function mergeTables()
    {
        if (empty($this->selectedMergeTables)) {
            $this->dispatch('notify', message: 'Please select tables to merge', type: 'error');
            return;
        }

        $table = RestaurantTable::findOrFail($this->primaryTableId);
        $table->mergeTables($this->selectedMergeTables);
        
        $this->showMergeModal = false;
        $this->dispatch('notify', message: 'Tables merged successfully', type: 'success');
    }

    public function splitTables(int $tableId)
    {
        $table = RestaurantTable::findOrFail($tableId);
        $table->splitTables();
        $this->dispatch('notify', message: 'Tables split successfully', type: 'success');
    }

    // Table details
    public function viewDetails(int $tableId)
    {
        $this->detailsTableId = $tableId;
        $this->showDetailsModal = true;
    }

    #[Computed]
    public function detailsTable()
    {
        if (!$this->detailsTableId) return null;
        return RestaurantTable::with(['currentOrder.items.product', 'activeOrders', 'mergedTables'])->find($this->detailsTableId);
    }

    // Quick actions from floor plan
    public function quickSeatTable(int $tableId)
    {
        $table = RestaurantTable::findOrFail($tableId);
        
        if ($table->status !== 'available' && $table->status !== 'reserved') {
            $this->dispatch('notify', message: 'Table is not available', type: 'error');
            return;
        }
        
        $table->occupy();
        $this->dispatch('notify', message: 'Table seated', type: 'success');
    }

    public function goToPOS(int $tableId)
    {
        $table = RestaurantTable::findOrFail($tableId);
        return redirect()->route('pos.index', ['table' => $tableId]);
    }

    /**
     * Open order details modal for a specific order.
     */
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

    /**
     * Close the order details modal.
     */
    public function closeOrder(): void
    {
        $this->showOrderModal = false;
        $this->viewingOrder = null;
        $this->dispatch('modal:close', name: 'order-detail');
    }

    /**
     * Redirect to POS to add more items to an existing order.
     * Used when order is preparing/ready/served and customer wants more.
     */
    public function addToExistingOrder(int $tableId)
    {
        $table = RestaurantTable::with('currentOrder')->findOrFail($tableId);
        
        if (!$table->currentOrder) {
            $this->dispatch('notify', message: 'No active order for this table', type: 'error');
            return;
        }
        
        // Redirect to POS with addto parameter to load the existing order for adding items
        return redirect()->route('pos.index', [
            'table' => $tableId,
            'addto' => $table->currentOrder->id,
        ]);
    }

    /**
     * Redirect to POS to add more items to a specific order by ID.
     */
    public function addToExistingOrderById(int $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        if ($order->payment_status !== 'unpaid') {
            $this->dispatch('notify', message: 'Cannot add to a paid order', type: 'error');
            return;
        }
        
        return redirect()->route('pos.index', [
            'table' => $order->table_id,
            'addto' => $order->id,
            'type'  => $order->order_type, // preserve order type (dine_in or takeaway)
        ]);
    }

    /**
     * Redirect to POS to pay all unpaid orders for a table at once.
     */
    public function payAllOrders(int $tableId)
    {
        $table = RestaurantTable::with('activeOrders')->findOrFail($tableId);
        $unpaidOrderIds = $table->activeOrders->where('payment_status', 'unpaid')->pluck('id')->toArray();
        
        if (empty($unpaidOrderIds)) {
            $this->dispatch('notify', message: 'No unpaid orders', type: 'error');
            return;
        }
        
        // Redirect to POS with payall parameter containing comma-separated order IDs
        return redirect()->route('pos.index', [
            'table'  => $tableId,
            'payall' => implode(',', $unpaidOrderIds),
        ]);
    }

    /**
     * Redirect to POS to create a NEW takeaway order for this table.
     * Creates a separate order (not combined with dine-in) to avoid KDS workflow conflicts.
     * Each order has independent KDS status. Payment can be combined at checkout.
     */
    public function createTakeawayOrder(int $tableId)
    {
        // Always create a new separate order for takeaway
        // This avoids resetting dine-in order KDS status when adding takeaway
        return redirect()->route('pos.index', [
            'table' => $tableId,
            'type'  => 'takeaway',
        ]);
    }

    /**
     * Redirect to POS to collect payment for table's unpaid orders.
     * If multiple unpaid orders exist, combines them into one payment.
     */
    public function collectTablePayment(int $tableId)
    {
        $table = RestaurantTable::with('activeOrders')->findOrFail($tableId);
        
        $unpaidOrders = $table->activeOrders->where('payment_status', 'unpaid');
        
        if ($unpaidOrders->isEmpty()) {
            $this->dispatch('notify', message: 'No unpaid orders for this table', type: 'error');
            return;
        }
        
        // If multiple unpaid orders, use payall to combine them
        if ($unpaidOrders->count() > 1) {
            $unpaidOrderIds = $unpaidOrders->pluck('id')->toArray();
            return redirect()->route('pos.index', [
                'table'  => $tableId,
                'payall' => implode(',', $unpaidOrderIds),
            ]);
        }
        
        // Single unpaid order - pay just that one
        return redirect()->route('pos.index', [
            'table' => $tableId,
            'pay' => $unpaidOrders->first()->id,
        ]);
    }

    /**
     * Redirect to POS to collect payment for a specific order.
     */
    public function collectPaymentForOrder(int $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        if ($order->payment_status !== 'unpaid') {
            $this->dispatch('notify', message: 'Order is already paid', type: 'error');
            return;
        }
        
        // Redirect to POS with pay parameter to auto-open payment modal
        return redirect()->route('pos.index', [
            'table' => $order->table_id,
            'pay' => $order->id,
        ]);
    }

    public function render()
    {
        return view('livewire.tables');
    }
}
