<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Inventory')]
#[Lazy]
class Inventory extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'all'; // all | tracked | low | out

    // Adjustment modal state
    public bool $showAdjustModal = false;
    public ?int $adjustProductId = null;
    public ?int $adjustVariantId = null;
    public string $adjustLabel = '';
    public string $adjustMode = 'restock'; // restock | set
    public int $adjustQuantity = 0;
    public int $adjustCurrent = 0;
    public string $adjustReason = '';

    // Enable-tracking inline state
    public function toggleTracking(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $product->update(['track_stock' => !$product->track_stock]);
        $this->dispatch('notify', message: $product->track_stock ? 'Stock tracking enabled.' : 'Stock tracking disabled.', type: 'success');
    }

    public function updateThreshold(int $productId, int $threshold): void
    {
        $product = Product::findOrFail($productId);
        $product->update(['low_stock_threshold' => max(0, $threshold)]);
    }

    public function openAdjust(int $productId, ?int $variantId = null): void
    {
        $product = Product::findOrFail($productId);
        $variant = $variantId ? ProductVariant::findOrFail($variantId) : null;

        $this->adjustProductId = $product->id;
        $this->adjustVariantId = $variant?->id;
        $this->adjustLabel = $variant ? "{$product->name} — {$variant->name}" : $product->name;
        $this->adjustCurrent = (int) ($variant ? $variant->stock_quantity : $product->stock_quantity);
        $this->adjustMode = 'restock';
        $this->adjustQuantity = 0;
        $this->adjustReason = '';
        $this->showAdjustModal = true;
    }

    public function saveAdjust(InventoryService $inventory): void
    {
        $this->validate([
            'adjustQuantity' => 'required|integer',
            'adjustReason' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($this->adjustProductId);
        $variant = $this->adjustVariantId ? ProductVariant::findOrFail($this->adjustVariantId) : null;

        // Ensure tracking is on so the movement is recorded.
        if ($variant && !$variant->track_stock) {
            $variant->update(['track_stock' => true]);
        } elseif (!$variant && !$product->track_stock) {
            $product->update(['track_stock' => true]);
        }

        if ($this->adjustMode === 'set') {
            $inventory->setQuantity($product, $variant, (int) $this->adjustQuantity, Auth::id(), $this->adjustReason ?: 'Stock take');
        } else {
            $inventory->restock($product, $variant, (int) $this->adjustQuantity, Auth::id(), $this->adjustReason ?: 'Restock');
        }

        $this->showAdjustModal = false;
        $this->dispatch('notify', message: 'Stock updated.', type: 'success');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::query()
            ->with(['variants' => fn ($q) => $q->orderBy('name')])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filter === 'tracked', fn ($q) => $q->where('track_stock', true))
            ->when($this->filter === 'low', fn ($q) => $q->where('track_stock', true)->whereColumn('stock_quantity', '<=', 'low_stock_threshold'))
            ->when($this->filter === 'out', fn ($q) => $q->where('track_stock', true)->where('stock_quantity', '<=', 0))
            ->orderBy('name');

        $products = $query->paginate(20);

        $trackedCount = Product::where('track_stock', true)->count();
        $lowCount = Product::where('track_stock', true)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->where('stock_quantity', '>', 0)->count();
        $outCount = Product::where('track_stock', true)->where('stock_quantity', '<=', 0)->count();

        $recentMovements = StockMovement::with(['product:id,name', 'variant:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        return view('livewire.inventory', [
            'products' => $products,
            'trackedCount' => $trackedCount,
            'lowCount' => $lowCount,
            'outCount' => $outCount,
            'recentMovements' => $recentMovements,
        ]);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="p-6 space-y-4">
            <div class="h-8 bg-neutral-200 dark:bg-neutral-700 rounded w-1/4 animate-pulse"></div>
            <div class="h-64 bg-neutral-100 dark:bg-neutral-800 rounded animate-pulse"></div>
        </div>
        HTML;
    }
}
