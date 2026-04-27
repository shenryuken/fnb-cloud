<?php

namespace App\Livewire;

use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CashierReport extends Component
{
    public ?string $fromDate = null;
    public ?string $toDate = null;
    public ?int $selectedUserId = null;

    public function mount()
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->endOfMonth()->format('Y-m-d');
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
        // Remove the explicit tenant_id filter - BelongsToTenant trait handles it automatically
        $fromDateTime = Carbon::createFromFormat('Y-m-d', $this->fromDate)->startOfDay();
        $toDateTime = Carbon::createFromFormat('Y-m-d', $this->toDate)->endOfDay();
        
        $query = Shift::whereBetween('opened_at', [$fromDateTime, $toDateTime]);

        if ($this->selectedUserId) {
            $query->where('user_id', $this->selectedUserId);
        }

        return $query->with('user', 'closedBy')
            ->orderBy('opened_at', 'desc')
            ->get();
    }

    #[Computed]
    public function cashierStats()
    {
        $stats = [];

        // Group shifts by user_id
        foreach ($this->shifts->groupBy('user_id') as $userId => $userShifts) {
            if ($userShifts->isEmpty()) {
                continue;
            }

            $user = $userShifts->first()->user;
            if (!$user) {
                continue;
            }

            $totalSales = $userShifts->sum('total_sales');
            $totalOrders = $userShifts->sum('order_count');
            $totalCashVariance = $userShifts->sum('difference');
            $shiftsCount = $userShifts->count();

            $totalDuration = $userShifts->reduce(function ($carry, $shift) {
                $duration = 0;
                if ($shift->opened_at && $shift->closed_at) {
                    $duration = $shift->closed_at->diffInMinutes($shift->opened_at);
                }
                return $carry + $duration;
            }, 0);

            $avgDuration = $shiftsCount > 0 ? intdiv($totalDuration, $shiftsCount) : 0;

            $stats[$userId] = [
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
