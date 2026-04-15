<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Order;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

#[Title('Customers')]
#[Lazy]
class Customers extends Component
{
    use WithPagination;

    public string $name = '';
    public string $email = '';
    public string $mobile = '';
    public int $points_balance = 0;

    public ?Customer $editing = null;
    public bool $isCreating = false;

    public string $search = '';
    public string $contactFilter = '';
    public string $ordersFilter = '';
    public string $sort = 'newest';

    public bool $showHistoryModal = false;
    public ?Customer $historyCustomer = null;
    public array $historyStats = [];
    public array $historyOrders = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'contactFilter' => ['except' => '', 'as' => 'contact'],
        'ordersFilter' => ['except' => '', 'as' => 'orders'],
        'sort' => ['except' => 'newest'],
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'mobile' => 'nullable|string|max:50',
        'points_balance' => 'required|integer|min:0',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingContactFilter(): void
    {
        $this->resetPage();
    }

    public function updatingOrdersFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['name', 'email', 'mobile', 'points_balance', 'editing']);
        $this->points_balance = 0;
        $this->isCreating = true;
    }

    public function edit(Customer $customer): void
    {
        $this->editing = $customer;
        $this->name = $customer->name;
        $this->email = (string) ($customer->email ?? '');
        $this->mobile = (string) ($customer->mobile ?? '');
        $this->points_balance = (int) ($customer->points_balance ?? 0);
        $this->isCreating = false;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['name'] = trim((string) $validated['name']);
        $validated['email'] = filled($validated['email'] ?? null) ? strtolower(trim((string) $validated['email'])) : null;
        $validated['mobile'] = filled($validated['mobile'] ?? null) ? $this->normalizeMobile((string) $validated['mobile']) : null;

        if (!filled($validated['email']) && !filled($validated['mobile'])) {
            $this->addError('email', 'Email or mobile is required.');
            $this->addError('mobile', 'Email or mobile is required.');
            return;
        }

        $tenantId = Auth::user()->tenant_id;

        if (filled($validated['email'])) {
            $q = Customer::where('tenant_id', $tenantId)->where('email', $validated['email']);
            if ($this->editing) {
                $q->where('id', '!=', $this->editing->id);
            }
            if ($q->exists()) {
                $this->addError('email', 'Email already exists.');
                return;
            }
        }

        if (filled($validated['mobile'])) {
            $q = Customer::where('tenant_id', $tenantId)->where('mobile', $validated['mobile']);
            if ($this->editing) {
                $q->where('id', '!=', $this->editing->id);
            }
            if ($q->exists()) {
                $this->addError('mobile', 'Mobile already exists.');
                return;
            }
        }

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            Customer::create($validated);
        }

        $this->reset(['name', 'email', 'mobile', 'points_balance', 'editing', 'isCreating']);
        $this->dispatch('notify', message: 'Customer saved.', type: 'success');
    }

    private function normalizeMobile(string $mobile): string
    {
        $mobile = trim($mobile);
        $mobile = str_replace([' ', '-', '(', ')'], '', $mobile);
        return $mobile;
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
        $this->dispatch('notify', message: 'Customer deleted.', type: 'success');
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'contactFilter', 'ordersFilter', 'sort']);
        $this->sort = 'newest';
        $this->resetPage();
    }

    public function viewHistory(Customer $customer): void
    {
        $ordersQuery = $customer->orders()->latest();

        $totalOrders = (clone $ordersQuery)->count();
        $totalSpend = (float) (clone $ordersQuery)
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        $totalPointsEarned = (int) (clone $ordersQuery)
            ->where('payment_status', 'paid')
            ->sum('points_earned');
        $totalPointsUsed = (int) (clone $ordersQuery)
            ->where('payment_status', 'paid')
            ->sum('points_redeemed');
        $lastVisit = (clone $ordersQuery)->max('created_at');

        $this->historyCustomer = $customer;
        $this->historyStats = [
            'total_orders' => (int) $totalOrders,
            'total_spend' => (float) $totalSpend,
            'points_earned' => $totalPointsEarned,
            'points_used' => $totalPointsUsed,
            'last_visit' => $lastVisit,
        ];

        $this->historyOrders = (clone $ordersQuery)
            ->withCount('items')
            ->limit(20)
            ->get(['id', 'created_at', 'total_amount', 'status', 'payment_method', 'voucher_code', 'points_redeemed', 'points_earned'])
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'created_at' => $o->created_at?->format('M d, Y H:i'),
                'status' => (string) $o->status,
                'payment_method' => (string) $o->payment_method,
                'total_amount' => (float) $o->total_amount,
                'items_count' => (int) $o->items_count,
                'voucher_code' => (string) ($o->voucher_code ?? ''),
                'points_redeemed' => (int) ($o->points_redeemed ?? 0),
                'points_earned' => (int) ($o->points_earned ?? 0),
            ])
            ->all();

        $this->showHistoryModal = true;
    }

    public function closeHistory(): void
    {
        $this->showHistoryModal = false;
        $this->historyCustomer = null;
        $this->historyStats = [];
        $this->historyOrders = [];
    }

    public function render()
    {
        $query = Customer::query()->withCount('orders');

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('mobile', 'like', $term);
            });
        }

        if ($this->contactFilter === 'has_email') {
            $query->whereNotNull('email')->where('email', '!=', '');
        } elseif ($this->contactFilter === 'has_mobile') {
            $query->whereNotNull('mobile')->where('mobile', '!=', '');
        } elseif ($this->contactFilter === 'missing') {
            $query->where(function ($q) {
                $q->whereNull('email')->orWhere('email', '');
            })->where(function ($q) {
                $q->whereNull('mobile')->orWhere('mobile', '');
            });
        }

        if ($this->ordersFilter === 'with_orders') {
            $query->whereHas('orders');
        } elseif ($this->ordersFilter === 'no_orders') {
            $query->whereDoesntHave('orders');
        }

        if ($this->sort === 'name') {
            $query->orderBy('name');
        } elseif ($this->sort === 'points_desc') {
            $query->orderByDesc('points_balance')->orderBy('name');
        } elseif ($this->sort === 'last_visit') {
            $query->withMax('orders', 'created_at')
                ->orderByDesc('orders_max_created_at')
                ->orderByDesc('id');
        } else {
            $query->orderByDesc('id');
        }

        $hasActiveFilters = $this->search !== ''
            || $this->contactFilter !== ''
            || $this->ordersFilter !== ''
            || $this->sort !== 'newest';

        return view('livewire.customers', [
            'customers' => $query->paginate(10),
            'hasActiveFilters' => $hasActiveFilters,
        ]);
    }
}
