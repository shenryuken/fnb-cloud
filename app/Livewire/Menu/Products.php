<?php

namespace App\Livewire\Menu;

use App\Models\Product;
use App\Models\Category;
use Livewire\Attributes\Title;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

#[Title('Products')]
#[Lazy]
class Products extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $product_type = 'ala_carte';
    public string $name = '';
    public string $description = '';
    public float $price = 0;
    public ?int $category_id = null;
    public string $image_url = '';
    public $image;
    public string $badge_text = '';
    public string $tile_color = '';
    public bool $use_tile_color = false;
    public array $set_groups = [];
    public int $sort_order = 0;
    public bool $is_active = true;
    public array $addons = [];

    // Variants and Addons for the form
    public array $variants = [];
    public array $selectedGroups = []; // IDs of addon groups assigned to this product
    public array $selectedStandaloneAddons = []; // IDs of standalone addons assigned to this product

    public ?Product $editing = null;
    public bool $isCreating = false;

    // Search and filters
    public string $search = '';
    public string $categoryFilter = '';
    public string $statusFilter = '';
    
    // Add-ons tab state
    public string $activeAddonTab = 'addon-groups';

    protected $rules = [
        'product_type' => 'required|in:ala_carte,set',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'image_url' => 'nullable|string',
        'image' => 'nullable|image|max:2048',
        'badge_text' => 'nullable|string|max:30',
        'tile_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        'sort_order' => 'required|integer|min:0',
        'is_active' => 'boolean',
        'set_groups' => 'array',
        'variants.*.name' => 'required|string|max:255',
        'variants.*.receipt_label' => 'nullable|string|max:10',
        'variants.*.price' => 'required|numeric|min:0',
        'selectedGroups' => 'nullable|array',
        'selectedGroups.*' => 'exists:addon_groups,id',
        'selectedStandaloneAddons' => 'nullable|array',
        'selectedStandaloneAddons.*' => 'exists:product_addons,id',
    ];

    /**
     * Start creating a new product.
     */
    public function create(): void
    {
        $this->reset(['product_type', 'name', 'description', 'price', 'category_id', 'image_url', 'image', 'badge_text', 'tile_color', 'use_tile_color', 'set_groups', 'sort_order', 'is_active', 'editing', 'variants', 'selectedGroups', 'selectedStandaloneAddons']);
        $this->product_type = 'ala_carte';
        $this->isCreating = true;
    }

    /**
     * Start editing a product.
     */
    public function edit(Product $product): void
    {
        $this->editing = $product;
        $this->product_type = (string) ($product->product_type ?? 'ala_carte');
        $this->name = $product->name;
        $this->description = $product->description ?? '';
        $this->price = $product->price;
        $this->category_id = $product->category_id;
        $this->image_url = $product->image_url ?? '';
        $this->image = null;
        $this->badge_text = (string) ($product->badge_text ?? '');
        $this->tile_color = (string) ($product->tile_color ?? '');
        $this->use_tile_color = filled($this->tile_color);
        $this->sort_order = $product->sort_order;
        $this->is_active = $product->is_active;
        $this->variants = $product->variants()->get()->toArray();
        $this->selectedGroups = $product->addonGroups()->pluck('addon_groups.id')->toArray();
        $this->selectedStandaloneAddons = $product->addons()->whereNull('addon_group_id')->pluck('product_addons.id')->toArray();
        $this->set_groups = $product->setGroups()
            ->with(['items.product'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($g) => [
                'name' => $g->name,
                'min_select' => (int) $g->min_select,
                'max_select' => (int) $g->max_select,
                'sort_order' => (int) $g->sort_order,
                'items' => $g->items
                    ->sortBy('sort_order')
                    ->values()
                    ->map(fn ($i) => [
                        'product_id' => $i->product_id,
                        'extra_price' => (float) $i->extra_price,
                        'sort_order' => (int) $i->sort_order,
                    ])
                    ->all(),
            ])
            ->all();
        $this->isCreating = true; // Open the modal for editing
    }

    /**
     * Add a new variant row to the form.
     */
    public function addVariant(): void
    {
        $this->variants[] = ['name' => '', 'receipt_label' => '', 'price' => 0];
    }

    /**
     * Remove a variant row from the form.
     */
    public function removeVariant(int $index): void
    {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
    }

    public function addSetGroup(): void
    {
        $this->set_groups[] = [
            'name' => '',
            'min_select' => 0,
            'max_select' => 1,
            'sort_order' => count($this->set_groups) + 1,
            'items' => [],
        ];
    }

    public function removeSetGroup(int $index): void
    {
        unset($this->set_groups[$index]);
        $this->set_groups = array_values($this->set_groups);
    }

    public function addSetGroupItem(int $groupIndex): void
    {
        if (!isset($this->set_groups[$groupIndex])) {
            return;
        }
        $items = $this->set_groups[$groupIndex]['items'] ?? [];
        $items[] = [
            'product_id' => null,
            'extra_price' => 0,
            'sort_order' => count($items) + 1,
        ];
        $this->set_groups[$groupIndex]['items'] = $items;
    }

    public function removeSetGroupItem(int $groupIndex, int $itemIndex): void
    {
        if (!isset($this->set_groups[$groupIndex]['items'][$itemIndex])) {
            return;
        }
        unset($this->set_groups[$groupIndex]['items'][$itemIndex]);
        $this->set_groups[$groupIndex]['items'] = array_values($this->set_groups[$groupIndex]['items']);
    }

    /**
     * Add a new addon row to the form.
     */
    public function addAddon(): void
    {
        $this->addons[] = ['name' => '', 'price' => 0];
    }

    /**
     * Remove an addon row from the form.
     */
    public function removeAddon(int $index): void
    {
        unset($this->addons[$index]);
        $this->addons = array_values($this->addons);
    }

    /**
     * Save the product.
     */
    public function save(): void
    {
        $validated = $this->validate();
        $validated['badge_text'] = filled($validated['badge_text'] ?? null) ? trim((string) $validated['badge_text']) : null;
        if (!$this->use_tile_color) {
            $validated['tile_color'] = null;
        } else {
            $validated['tile_color'] = filled($validated['tile_color'] ?? null) ? strtoupper(trim((string) $validated['tile_color'])) : null;
        }

        if ($this->image) {
            $path = $this->image->store('products', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        if (($validated['product_type'] ?? 'ala_carte') === 'set') {
            $this->validate([
                'set_groups' => 'required|array|min:1',
                'set_groups.*.name' => 'required|string|max:80',
                'set_groups.*.min_select' => 'required|integer|min:0|max:99',
                'set_groups.*.max_select' => 'required|integer|min:1|max:99',
                'set_groups.*.sort_order' => 'required|integer|min:0|max:1000000',
                'set_groups.*.items' => 'required|array|min:1',
                'set_groups.*.items.*.product_id' => 'required|integer|exists:products,id',
                'set_groups.*.items.*.extra_price' => 'nullable|numeric|min:0|max:100000',
                'set_groups.*.items.*.sort_order' => 'required|integer|min:0|max:1000000',
            ]);

            foreach ($this->set_groups as $g) {
                if ((int) ($g['max_select'] ?? 0) < (int) ($g['min_select'] ?? 0)) {
                    $this->addError('set_groups', 'Max select must be greater than or equal to min select.');
                    return;
                }
            }
        } else {
            $this->set_groups = [];
        }

        DB::transaction(function () use ($validated) {
            if ($this->editing) {
                $this->editing->update($validated);
                $product = $this->editing;
            } else {
                $product = Product::create($validated);
            }

            // Sync variants
            $product->variants()->delete();
            if (!empty($this->variants)) {
                $product->variants()->createMany($this->variants);
            }

            // Sync addon groups (Many-to-Many)
            $product->addonGroups()->sync($this->selectedGroups);

            // Sync standalone addons (Many-to-Many)
            $product->addons()->sync($this->selectedStandaloneAddons);

            $product->setGroups()->delete();
            if (($validated['product_type'] ?? 'ala_carte') === 'set') {
                foreach ($this->set_groups as $group) {
                    $g = $product->setGroups()->create([
                        'name' => trim((string) ($group['name'] ?? '')),
                        'min_select' => (int) ($group['min_select'] ?? 0),
                        'max_select' => (int) ($group['max_select'] ?? 1),
                        'sort_order' => (int) ($group['sort_order'] ?? 0),
                    ]);

                    $items = $group['items'] ?? [];
                    foreach ($items as $item) {
                        $g->items()->create([
                            'product_id' => (int) $item['product_id'],
                            'extra_price' => round(max(0, (float) ($item['extra_price'] ?? 0)), 2),
                            'sort_order' => (int) ($item['sort_order'] ?? 0),
                        ]);
                    }
                }
            }
        });

        $this->reset(['product_type', 'name', 'description', 'price', 'category_id', 'image_url', 'image', 'badge_text', 'tile_color', 'use_tile_color', 'set_groups', 'sort_order', 'is_active', 'editing', 'isCreating', 'variants', 'selectedGroups', 'selectedStandaloneAddons']);
        $this->dispatch('product-saved');
    }

    public function duplicateWithVariants(Product $product): void
    {
        $this->duplicateProduct($product, true);
    }

    public function duplicateWithoutVariants(Product $product): void
    {
        $this->duplicateProduct($product, false);
    }

    private function duplicateProduct(Product $product, bool $withVariants): void
    {
        $product->load(['variants', 'addonGroups', 'addons', 'setGroups.items']);

        $new = DB::transaction(function () use ($product, $withVariants) {
            $baseName = (string) $product->name;
            $copyPrefix = $baseName . ' (Copy';
            $existingCount = Product::where('name', 'like', $copyPrefix . '%')->count();

            $name = $existingCount > 0
                ? $baseName . ' (Copy ' . ($existingCount + 1) . ')'
                : $baseName . ' (Copy)';

            $newProduct = Product::create([
                'category_id' => $product->category_id,
                'product_type' => (string) ($product->product_type ?? 'ala_carte'),
                'name' => $name,
                'price' => (float) $product->price,
                'description' => $product->description,
                'image_url' => $product->image_url,
                'badge_text' => $product->badge_text,
                'tile_color' => $product->tile_color,
                'sort_order' => (int) $product->sort_order + 1,
                'is_active' => false,
            ]);

            $newProduct->addonGroups()->sync($product->addonGroups->pluck('id')->all());
            $newProduct->addons()->sync($product->addons->pluck('id')->all());

            if (($product->product_type ?? 'ala_carte') === 'set') {
                foreach ($product->setGroups->sortBy('sort_order') as $group) {
                    $g = $newProduct->setGroups()->create([
                        'name' => $group->name,
                        'min_select' => (int) $group->min_select,
                        'max_select' => (int) $group->max_select,
                        'sort_order' => (int) $group->sort_order,
                    ]);

                    foreach ($group->items->sortBy('sort_order') as $item) {
                        $g->items()->create([
                            'product_id' => (int) $item->product_id,
                            'extra_price' => (float) $item->extra_price,
                            'sort_order' => (int) $item->sort_order,
                        ]);
                    }
                }
            }

            if ($withVariants) {
                $variantRows = $product->variants->map(fn ($v) => [
                    'name' => $v->name,
                    'receipt_label' => $v->receipt_label,
                    'price' => $v->price,
                    'is_active' => (bool) $v->is_active,
                ])->all();

                if (!empty($variantRows)) {
                    $newProduct->variants()->createMany($variantRows);
                }
            }

            return $newProduct;
        });

        $this->edit($new->fresh());
    }

    /**
     * Delete a product.
     */
    public function delete(Product $product): void
    {
        $product->delete();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $query = Product::with(['category']);

        // Apply search filter
        if (filled($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Apply category filter
        if (filled($this->categoryFilter)) {
            $query->where('category_id', $this->categoryFilter);
        }

        // Apply status filter
        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        return view('livewire.menu.products', [
            'products' => $query->orderBy('sort_order')->paginate(10),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'allProducts' => Product::orderBy('name')->get(['id', 'name', 'price']),
            'addonGroups' => \App\Models\AddonGroup::all(),
            'standaloneAddons' => \App\Models\ProductAddon::whereNull('addon_group_id')->get(),
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
