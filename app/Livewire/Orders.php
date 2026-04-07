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
        return view('livewire.orders', [
            'orders' => Order::with(['items.product', 'user', 'customer'])
                ->latest()
                ->paginate(10),
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
