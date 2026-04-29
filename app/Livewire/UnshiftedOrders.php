<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;

#[Title('Unshifted Orders')]
class UnshiftedOrders extends Component
{
    public string $searchQuery = '';
    public ?int $selectedShiftId = null;
    public array $selectedOrderIds = [];
    public string $filterStatus = 'all'; // all, pending, completed, paid
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    public function mount(): void
    {
        abort_if(!Auth::user()->hasPermission('orders.manage'), 403);
    }

    #[Computed]
    public function unshiftedOrders()
    {
        $query = Order::query()
            ->whereNull('shift_id')
            ->with(['customer', 'user', 'table'])
            ->where('tenant_id', Auth::user()->tenant_id);

        // Filter by search
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('id', $this->searchQuery)
                    ->orWhere('table_number', 'like', '%' . $this->searchQuery . '%')
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', '%' . $this->searchQuery . '%'));
            });
        }

        // Filter by status
        if ($this->filterStatus !== 'all') {
            match ($this->filterStatus) {
                'pending' => $query->whereIn('status', ['pending', 'processing']),
                'completed' => $query->where('status', 'completed'),
                'paid' => $query->where('payment_status', 'paid'),
                default => null,
            };
        }

        // Sort
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(25);
    }

    #[Computed]
    public function availableShifts()
    {
        return Shift::query()
            ->where('tenant_id', Auth::user()->tenant_id)
            ->orderByDesc('opened_at')
            ->limit(10)
            ->get();
    }

    public function selectAll(): void
    {
        $this->selectedOrderIds = $this->unshiftedOrders->pluck('id')->toArray();
    }

    public function deselectAll(): void
    {
        $this->selectedOrderIds = [];
    }

    public function toggleOrder(int $orderId): void
    {
        if (in_array($orderId, $this->selectedOrderIds)) {
            $this->selectedOrderIds = array_filter(
                $this->selectedOrderIds,
                fn ($id) => $id !== $orderId
            );
        } else {
            $this->selectedOrderIds[] = $orderId;
        }
    }

    public function reassignToShift(): void
    {
        if (empty($this->selectedOrderIds) || !$this->selectedShiftId) {
            $this->dispatch('notify', message: 'Please select orders and a shift.', type: 'error');
            return;
        }

        $shift = Shift::findOrFail($this->selectedShiftId);

        $count = Order::whereIn('id', $this->selectedOrderIds)
            ->update([
                'shift_id' => $shift->id,
                'shift_reassigned_at' => now(),
            ]);

        $this->selectedOrderIds = [];
        $this->selectedShiftId = null;
        unset($this->unshiftedOrders);

        $this->dispatch('notify', message: "Reassigned $count orders to shift #{$shift->id}", type: 'success');
    }

    public function addNote(int $orderId): void
    {
        $this->dispatch('openNoteModal', orderId: $orderId);
    }

    public function render()
    {
        return view('livewire.unshifted-orders');
    }
}
