<?php

namespace App\Livewire;

use App\Models\Shift;
use App\Models\CashMovement;
use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Title('Shift Management')]
class Shifts extends Component
{
    use WithPagination;

    // Open shift modal
    public bool $showOpenModal = false;
    public float $openingCash = 0;
    public string $openingNotes = '';

    // Close shift modal
    public bool $showCloseModal = false;
    public ?float $actualCash = null;
    public string $closingNotes = '';

    // Cash movement modal
    public bool $showMovementModal = false;
    public string $movementType = 'cash_in';
    public float $movementAmount = 0;
    public string $movementReason = '';
    public string $movementNotes = '';

    // View shift modal
    public bool $showViewModal = false;
    public ?int $viewingShiftId = null;

    // Filter
    public string $dateFilter = '';
    public ?int $cashierFilter = null;

    protected $rules = [
        'openingCash' => 'required|numeric|min:0',
        'openingNotes' => 'nullable|string|max:500',
        'actualCash' => 'required|numeric|min:0',
        'closingNotes' => 'nullable|string|max:500',
        'movementType' => 'required|in:cash_in,cash_out,adjustment',
        'movementAmount' => 'required|numeric|min:0.01',
        'movementReason' => 'required|string|max:100',
        'movementNotes' => 'nullable|string|max:500',
    ];

    #[Computed]
    public function currentShift(): ?Shift
    {
        return Shift::currentOpen();
    }

    #[Computed]
    public function cashiers()
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->whereHas('roles', fn($q) => $q->whereIn('slug', ['cashier', 'waiter', 'kitchen-staff', 'owner']))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function shifts()
    {
        $query = Shift::with(['user', 'closedBy'])
            ->orderByDesc('opened_at');

        if ($this->dateFilter) {
            $query->whereDate('opened_at', $this->dateFilter);
        }

        if ($this->cashierFilter) {
            $query->where('user_id', $this->cashierFilter);
        }

        return $query->paginate(10);
    }

    #[Computed]
    public function viewingShift(): ?Shift
    {
        if (!$this->viewingShiftId) return null;
        return Shift::with(['user', 'closedBy', 'cashMovements.user', 'orders'])
            ->find($this->viewingShiftId);
    }

    public function openShiftModal(): void
    {
        $this->reset(['openingCash', 'openingNotes']);
        $this->showOpenModal = true;
    }

    public function openShift(): void
    {
        $this->validate([
            'openingCash' => 'required|numeric|min:0',
            'openingNotes' => 'nullable|string|max:500',
        ]);

        // Check if there's already an open shift
        if ($this->currentShift) {
            $this->dispatch('notify', [
                'message' => 'A shift is already open. Close it first.',
                'type' => 'error',
            ]);
            return;
        }

        Shift::create([
            'tenant_id' => Auth::user()->tenant_id,
            'user_id' => Auth::id(),
            'opened_at' => now(),
            'opening_cash' => $this->openingCash,
            'expected_cash' => $this->openingCash,
            'opening_notes' => $this->openingNotes ?: null,
            'status' => 'open',
        ]);

        $this->showOpenModal = false;
        $this->dispatch('notify', 'Shift opened successfully');
        unset($this->currentShift);
    }

    public function closeShiftModal(): void
    {
        if (!$this->currentShift) return;
        
        // Recalculate before showing modal
        $this->currentShift->recalculateSales();
        unset($this->currentShift);
        
        $this->reset(['actualCash', 'closingNotes']);
        $this->actualCash = $this->currentShift?->expected_cash;
        $this->showCloseModal = true;
    }

    public function closeShift(): void
    {
        $this->validate([
            'actualCash' => 'required|numeric|min:0',
            'closingNotes' => 'nullable|string|max:500',
        ]);

        $shift = $this->currentShift;
        if (!$shift) return;

        // Final recalculation
        $shift->recalculateSales();

        $shift->update([
            'closed_at' => now(),
            'closed_by_user_id' => Auth::id(),
            'actual_cash' => $this->actualCash,
            'difference' => $this->actualCash - $shift->expected_cash,
            'closing_notes' => $this->closingNotes ?: null,
            'status' => 'closed',
        ]);

        $this->showCloseModal = false;
        $this->dispatch('notify', 'Shift closed successfully');
        unset($this->currentShift);
    }

    public function openMovementModal(): void
    {
        if (!$this->currentShift) {
            $this->dispatch('notify', [
                'message' => 'No open shift. Open a shift first.',
                'type' => 'error',
            ]);
            return;
        }

        $this->reset(['movementType', 'movementAmount', 'movementReason', 'movementNotes']);
        $this->movementType = 'cash_in';
        $this->movementAmount = 0;
        $this->showMovementModal = true;
    }

    public function saveCashMovement(): void
    {
        $this->validate([
            'movementType' => 'required|in:cash_in,cash_out,adjustment',
            'movementAmount' => 'required|numeric|min:0.01',
            'movementReason' => 'required|string|max:100',
            'movementNotes' => 'nullable|string|max:500',
        ]);

        $shift = $this->currentShift;
        if (!$shift) return;

        // For adjustment, amount can be negative
        $amount = $this->movementAmount;
        if ($this->movementType === 'adjustment' && $amount < 0) {
            $amount = abs($amount);
        }

        CashMovement::create([
            'tenant_id' => Auth::user()->tenant_id,
            'shift_id' => $shift->id,
            'user_id' => Auth::id(),
            'type' => $this->movementType,
            'amount' => $amount,
            'reason' => $this->movementReason,
            'notes' => $this->movementNotes ?: null,
        ]);

        // Update expected cash
        $shift->expected_cash = $shift->calculateExpectedCash();
        $shift->save();

        $this->showMovementModal = false;
        $this->dispatch('notify', 'Cash movement recorded');
        unset($this->currentShift);
    }

    public function viewShift(int $id): void
    {
        $this->viewingShiftId = $id;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewingShiftId = null;
    }

    public function render()
    {
        return view('livewire.shifts');
    }
}
