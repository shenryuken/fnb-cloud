<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

#[Title('Kitchen Display System')]
#[Lazy]
class Kds extends Component
{
    public bool $isBusy = false;

    public function mount()
    {
        $this->isBusy = (bool) Auth::user()->tenant->is_busy;
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="p-6 bg-neutral-900 min-h-screen text-white flex items-center justify-center">
            <div class="flex flex-col items-center gap-4">
                <div class="w-16 h-16 border-4 border-orange-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm font-black text-neutral-500 uppercase tracking-widest">Warming up the kitchen...</p>
            </div>
        </div>
        HTML;
    }

    public function toggleBusy()
    {
        $tenant = Auth::user()->tenant;
        $tenant->update(['is_busy' => !$tenant->is_busy]);
        $this->isBusy = (bool) $tenant->is_busy;
        
        $this->dispatch('busy-status-updated', isBusy: $this->isBusy);
    }

    public function rendering($view, $data)
    {
        $this->isBusy = (bool) Auth::user()->tenant->is_busy;
    }

    #[Computed]
    public function orders()
    {
        return Order::whereIn('kds_status', ['pending', 'preparing', 'ready'])
            ->with(['items.product', 'items.variant', 'items.addons', 'items.components'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function updateStatus(int $orderId, string $status, ?int $prepTime = null): void
    {
        DB::transaction(function () use ($orderId, $status, $prepTime) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($orderId);

            $data = ['kds_status' => $status];

            if ($status === 'preparing') {
                $data['preparing_at'] = now();
                // Only reset items that are NOT already served (preserve served items)
                $order->items()->where('kds_is_served', false)->update([
                    'kds_is_ready' => false,
                    'kds_ready_at' => null,
                ]);
            }

            if ($prepTime) {
                $data['prep_time_minutes'] = $prepTime;
            }

            if ($status === 'ready') {
                $order->items()->where('kds_is_ready', false)->update([
                    'kds_is_ready' => true,
                    'kds_ready_at' => now(),
                ]);
            }

            if ($status === 'served') {
                $order->items()->where('kds_is_ready', false)->update([
                    'kds_is_ready' => true,
                    'kds_ready_at' => now(),
                ]);
                $order->items()->where('kds_is_served', false)->update([
                    'kds_is_served' => true,
                    'kds_served_at' => now(),
                ]);
            }

            $order->update($data);

            if ($status === 'served' && $order->status === 'processing') {
                $order->update(['status' => 'completed']);
            }
        });
    }

    public function toggleItemReady(int $orderItemId): void
    {
        DB::transaction(function () use ($orderItemId) {
            $item = OrderItem::with('order')->lockForUpdate()->findOrFail($orderItemId);
            $order = Order::with('items')->lockForUpdate()->findOrFail($item->order_id);

            if (!in_array($order->kds_status, ['preparing', 'ready'], true)) {
                return;
            }

            $next = !$item->kds_is_ready;
            $item->update([
                'kds_is_ready' => $next,
                'kds_ready_at' => $next ? now() : null,
                'kds_is_served' => $next ? (bool) $item->kds_is_served : false,
                'kds_served_at' => $next ? $item->kds_served_at : null,
            ]);

            $order->refresh();
            $total = $order->items->count();
            $readyCount = $order->items->where('kds_is_ready', true)->count();
            $servedCount = $order->items->where('kds_is_served', true)->count();

            if ($total > 0 && $readyCount === $total) {
                $order->update(['kds_status' => 'ready']);
            } else {
                $order->update(['kds_status' => 'preparing']);
            }

            if ($total > 0 && $servedCount === $total) {
                $order->update(['kds_status' => 'served']);
                if ($order->status === 'processing') {
                    $order->update(['status' => 'completed']);
                }
            }
        });
    }

    public function toggleItemServed(int $orderItemId): void
    {
        DB::transaction(function () use ($orderItemId) {
            $item = OrderItem::with('order')->lockForUpdate()->findOrFail($orderItemId);
            $order = Order::with('items')->lockForUpdate()->findOrFail($item->order_id);

            if (!in_array($order->kds_status, ['preparing', 'ready'], true)) {
                return;
            }

            $servedNext = !$item->kds_is_served;
            $payload = [
                'kds_is_served' => $servedNext,
                'kds_served_at' => $servedNext ? now() : null,
            ];

            if ($servedNext && !$item->kds_is_ready) {
                $payload['kds_is_ready'] = true;
                $payload['kds_ready_at'] = now();
            }

            $item->update($payload);

            $order->refresh();
            $total = $order->items->count();
            $readyCount = $order->items->where('kds_is_ready', true)->count();
            $servedCount = $order->items->where('kds_is_served', true)->count();

            if ($total > 0 && $servedCount === $total) {
                $order->update(['kds_status' => 'served']);
                if ($order->status === 'processing') {
                    $order->update(['status' => 'completed']);
                }
                return;
            }

            if ($total > 0 && $readyCount === $total) {
                $order->update(['kds_status' => 'ready']);
            } else {
                $order->update(['kds_status' => 'preparing']);
            }
        });
    }

    public function render()
    {
        return view('livewire.kds', [
            'orders' => $this->orders
        ]);
    }
}
