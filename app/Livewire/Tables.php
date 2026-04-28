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

    public function mount()
    {
        // Default view
    }

    #[Computed]
    public function tables()
    {
        $query = RestaurantTable::where('is_active', true)
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
    }

    // Status changes
    public function setTableStatus(int $tableId, string $status)
    {
        $table = RestaurantTable::findOrFail($tableId);
        
        match($status) {
            'available' => $table->release(),
            'dirty' => $table->markDirty(),
            'occupied' => $table->occupy(),
            default => $table->update(['status' => $status]),
        };
        
        $this->dispatch('notify', message: 'Table status updated', type: 'success');
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
        return RestaurantTable::with(['currentOrder.items.product', 'mergedTables'])->find($this->detailsTableId);
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

    public function render()
    {
        return view('livewire.tables');
    }
}
