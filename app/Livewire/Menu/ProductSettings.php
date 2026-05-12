<?php

namespace App\Livewire\Menu;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Product Settings')]
#[Lazy]
class ProductSettings extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $statusFilter = '';

    public array $selectedProductIds = [];

    public string $bulkColor = '#3b82f6';

    public string $sortMode = 'sort_order';
    public string $sortCategoryId = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selectedProductIds = [];
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
        $this->selectedProductIds = [];
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        $this->selectedProductIds = [];
    }

    public function toggleSelectedProduct(int $productId): void
    {
        $productId = (int) $productId;

        if (in_array($productId, $this->selectedProductIds, true)) {
            $this->selectedProductIds = array_values(array_filter(
                $this->selectedProductIds,
                fn ($id) => (int) $id !== $productId
            ));
            return;
        }

        $this->selectedProductIds[] = $productId;
        $this->selectedProductIds = array_values(array_unique(array_map('intval', $this->selectedProductIds)));
    }

    public function toggleSelectAllOnPage(array $pageIds): void
    {
        $pageIds = array_values(array_unique(array_map('intval', $pageIds)));
        if (empty($pageIds)) {
            return;
        }

        $selected = array_values(array_unique(array_map('intval', $this->selectedProductIds)));
        $missing = array_diff($pageIds, $selected);

        if (empty($missing)) {
            $this->selectedProductIds = array_values(array_diff($selected, $pageIds));
            return;
        }

        $this->selectedProductIds = array_values(array_unique(array_merge($selected, $pageIds)));
    }

    public function clearSelection(): void
    {
        $this->selectedProductIds = [];
    }

    public function applyBulkColor(): void
    {
        $ids = array_values(array_unique(array_map('intval', $this->selectedProductIds)));
        if (empty($ids)) {
            return;
        }

        $color = strtoupper(trim($this->bulkColor));
        if (!preg_match('/^#[0-9A-F]{6}$/', $color)) {
            $this->dispatch('notify', message: 'Invalid color. Use format like #3B82F6.', type: 'error');
            return;
        }

        $count = Product::whereIn('id', $ids)->update(['tile_color' => $color]);
        $this->dispatch('notify', message: "Updated color for {$count} product(s).", type: 'success');
    }

    public function clearBulkColor(): void
    {
        $ids = array_values(array_unique(array_map('intval', $this->selectedProductIds)));
        if (empty($ids)) {
            return;
        }

        $count = Product::whereIn('id', $ids)->update(['tile_color' => null]);
        $this->dispatch('notify', message: "Cleared color for {$count} product(s).", type: 'success');
    }

    public function applySortOrder(): void
    {
        $mode = $this->sortMode;
        if (!in_array($mode, ['sort_order', 'name_asc', 'name_desc'], true)) {
            return;
        }

        $query = Product::query();
        if (filled($this->sortCategoryId)) {
            $catId = (int) $this->sortCategoryId;
            $cat = Category::find($catId);
            if ($cat && !$cat->parent_id) {
                $childIds = Category::where('parent_id', $cat->id)->pluck('id')->all();
                $query->whereIn('category_id', array_values(array_unique(array_merge([$catId], array_map('intval', $childIds)))));
            } else {
                $query->where('category_id', $catId);
            }
        }

        if ($mode === 'name_asc') {
            $query->orderBy('name')->orderBy('id');
        } elseif ($mode === 'name_desc') {
            $query->orderByDesc('name')->orderBy('id');
        } else {
            $query->orderBy('sort_order')->orderBy('id');
        }

        $ids = $query->pluck('id')->all();
        if (empty($ids)) {
            return;
        }

        DB::transaction(function () use ($ids) {
            foreach ($ids as $index => $id) {
                Product::where('id', (int) $id)->update(['sort_order' => $index]);
            }
        });

        $this->dispatch('notify', message: 'Sort order updated.', type: 'success');
    }

    public function render()
    {
        $query = Product::with(['category']);

        if (filled($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if (filled($this->categoryFilter)) {
            $catId = (int) $this->categoryFilter;
            $cat = Category::find($catId);
            if ($cat && !$cat->parent_id) {
                $childIds = Category::where('parent_id', $cat->id)->pluck('id')->all();
                $query->whereIn('category_id', array_values(array_unique(array_merge([$catId], array_map('intval', $childIds)))));
            } else {
                $query->where('category_id', $catId);
            }
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $products = $query->orderBy('sort_order')->paginate(20);

        $allCategories = Category::orderBy('sort_order')->orderBy('name')->get();
        $parents = $allCategories->whereNull('parent_id')->values();
        $childrenByParent = $allCategories->whereNotNull('parent_id')->groupBy('parent_id');
        $categoryOptions = [];
        foreach ($parents as $p) {
            $categoryOptions[] = ['id' => (int) $p->id, 'label' => (string) $p->name];
            foreach (($childrenByParent[$p->id] ?? collect())->sortBy('sort_order')->values() as $c) {
                $categoryOptions[] = ['id' => (int) $c->id, 'label' => (string) ($p->name . ' / ' . $c->name)];
            }
        }

        return view('livewire.menu.product-settings', [
            'products' => $products,
            'categoryOptions' => $categoryOptions,
        ]);
    }
}
