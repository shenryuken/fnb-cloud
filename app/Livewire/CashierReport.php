<?php

namespace App\Livewire;

use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CashierReport extends Component
{
    public ?int $fromDate = null;
    public ?int $toDate = null;
    public ?int $selectedUserId = null;

    public function mount()
    {
        $this->fromDate = now()->startOfMonth()->timestamp;
        $this->toDate = now()->endOfMonth()->timestamp;
    }

    #[Computed]
    public function users()
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->whereHas('roles', fn($q) => $q->whereIn('slug', ['cashier', 'waiter', 'kitchen-staff', 'owner']))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function shifts()
    {
        $query = Shift::where('tenant_id', auth()->user()->tenant_id)
            ->whereBetween('opened_at', [
                Carbon::createFromTimestamp($this->fromDate),
                Carbon::createFromTimestamp($this->toDate),
            ]);

        if ($this->selectedUserId) {
            $query->where('opened_by_user_id', $this->selectedUserId);
        }

        return $query->with('openedByUser', 'closedByUser')
            ->orderBy('opened_at', 'desc')
            ->get();
    }

    #[Computed]
    public function cashierStats()
    {
        $stats = [];

        foreach ($this->users as $user) {
            $userShifts = $this->shifts->where('opened_by_user_id', $user->id);

            if ($userShifts->isEmpty()) {
                continue;
            }

            $totalSales = $userShifts->sum('total_sales');
            $totalOrders = $userShifts->sum('total_orders');
            $totalCashVariance = $userShifts->sum(fn($shift) => $shift->actual_cash_amount - $shift->expected_cash_amount);
            $shiftsCount = $userShifts->count();

            $totalDuration = $userShifts->reduce(function ($carry, $shift) {
                $duration = 0;
                if ($shift->opened_at && $shift->closed_at) {
                    $duration = $shift->closed_at->diffInMinutes($shift->opened_at);
                }
                return $carry + $duration;
            }, 0);

            $avgDuration = $shiftsCount > 0 ? intdiv($totalDuration, $shiftsCount) : 0;

            $stats[$user->id] = [
                'user' => $user,
                'shiftsCount' => $shiftsCount,
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
                'avgOrderValue' => $totalOrders > 0 ? $totalSales / $totalOrders : 0,
                'avgShiftDuration' => $avgDuration,
                'totalCashVariance' => $totalCashVariance,
                'avgCashVariance' => $shiftsCount > 0 ? $totalCashVariance / $shiftsCount : 0,
                'variancePercentage' => $totalSales > 0 ? ($totalCashVariance / $totalSales) * 100 : 0,
            ];
        }

        return collect($stats)->sortByDesc('totalSales')->values();
    }

    public function render()
    {
        return view('livewire.cashier-report', [
            'stats' => $this->cashierStats,
            'users' => $this->users,
            'shifts' => $this->shifts,
        ]);
    }
}
