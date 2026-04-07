<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Attributes\Title;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithPagination;

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

    protected $queryString = [
        'search'          => ['except' => ''],
        'statusFilter'    => ['except' => '', 'as' => 'status'],
        'orderTypeFilter' => ['except' => '', 'as' => 'type'],
        'dateFrom'        => ['except' => '', 'as' => 'from'],
        'dateTo'          => ['except' => '', 'as' => 'to'],
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
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search          = '';
        $this->statusFilter    = '';
        $this->orderTypeFilter = '';
        $this->dateFrom        = '';
        $this->dateTo          = '';
        $this->resetPage();
    }

    public function updateStatus(Order $order, string $status): void
    {
        if (in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
            $order->update(['status' => $status]);
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
    }

    public function closeOrder(): void
    {
        $this->showOrderModal = false;
        $this->viewingOrder = null;
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
