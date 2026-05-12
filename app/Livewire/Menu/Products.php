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
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    public bool $is_available = true;
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

    public array $rowIsActive = [];
    public array $rowIsAvailable = [];

    public array $selectedProductIds = [];
    public bool $manualSort = false;
    
    // Add-ons tab state
    public string $activeAddonTab = 'addon-groups';

    // Import state
    public bool $showImportModal = false;
    public $importFile = null;
    public array $importErrors = [];
    public array $importPreview = [];
    public int $importSuccessCount = 0;
    public bool $importDone = false;

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
        'is_available' => 'boolean',
        'set_groups' => 'array',
        'variants.*.name' => 'required|string|max:255',
        'variants.*.receipt_label' => 'nullable|string|max:10',
        'variants.*.price' => 'required|numeric|min:0',
        'selectedGroups' => 'nullable|array',
        'selectedGroups.*' => 'exists:addon_groups,id',
        'selectedStandaloneAddons' => 'nullable|array',
        'selectedStandaloneAddons.*' => 'exists:product_addons,id',
        'importFile' => 'nullable|file|mimes:csv,txt|max:2048',
    ];

    /**
     * Start creating a new product.
     */
    public function create(): void
    {
        $this->reset(['product_type', 'name', 'description', 'price', 'category_id', 'image_url', 'image', 'badge_text', 'tile_color', 'use_tile_color', 'set_groups', 'sort_order', 'is_active', 'is_available', 'editing', 'variants', 'selectedGroups', 'selectedStandaloneAddons']);
        $this->product_type = 'ala_carte';
        $this->is_available = true;
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
        $this->is_available = (bool) ($product->is_available ?? true);
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

    public function updatedRowIsActive($value, $key): void
    {
        $id = is_string($key) && str_contains($key, '.') ? last(explode('.', $key)) : $key;
        Product::whereKey($id)->update(['is_active' => (bool) $value]);
    }

    public function updatedRowIsAvailable($value, $key): void
    {
        $id = is_string($key) && str_contains($key, '.') ? last(explode('.', $key)) : $key;
        Product::whereKey($id)->update(['is_available' => (bool) $value]);
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

        $savedProductId = null;
        DB::transaction(function () use ($validated, &$savedProductId) {
            if ($this->editing) {
                $this->editing->update($validated);
                $product = $this->editing;
            } else {
                $product = Product::create($validated);
            }
            $savedProductId = (int) $product->id;

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

        if ($savedProductId) {
            $this->rowIsActive[(string) $savedProductId] = (bool) ($validated['is_active'] ?? false);
            $this->rowIsAvailable[(string) $savedProductId] = (bool) ($validated['is_available'] ?? true);
        }

        $this->reset(['product_type', 'name', 'description', 'price', 'category_id', 'image_url', 'image', 'badge_text', 'tile_color', 'use_tile_color', 'set_groups', 'sort_order', 'is_active', 'is_available', 'editing', 'isCreating', 'variants', 'selectedGroups', 'selectedStandaloneAddons']);
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

    public function bulkCopyWithVariants(): void
    {
        $this->bulkCopy(true);
    }

    public function bulkCopyWithoutVariants(): void
    {
        $this->bulkCopy(false);
    }

    private function bulkCopy(bool $withVariants): void
    {
        $ids = array_values(array_unique(array_map('intval', $this->selectedProductIds)));
        if (empty($ids)) {
            return;
        }

        $products = Product::whereIn('id', $ids)->get();
        if ($products->isEmpty()) {
            $this->selectedProductIds = [];
            return;
        }

        $created = 0;
        foreach ($products as $product) {
            $this->duplicateProductToNew($product, $withVariants);
            $created++;
        }

        $this->selectedProductIds = [];
        $this->dispatch('notify', message: "Copied {$created} product(s).", type: 'success');
    }

    public function bulkDelete(): void
    {
        $ids = array_values(array_unique(array_map('intval', $this->selectedProductIds)));
        if (empty($ids)) {
            return;
        }

        $products = Product::whereIn('id', $ids)->get();
        if ($products->isEmpty()) {
            $this->selectedProductIds = [];
            return;
        }

        foreach ($products as $p) {
            $p->delete();
        }
        $count = $products->count();
        $this->selectedProductIds = [];
        $this->dispatch('notify', message: "Deleted {$count} product(s).", type: 'success');
    }

    public function clearSelection(): void
    {
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

    public function toggleManualSort(): void
    {
        $this->manualSort = !$this->manualSort;
        $this->resetPage();
        $this->selectedProductIds = [];
    }

    public function saveManualSort(array $orderedIds): void
    {
        if (!$this->manualSort) {
            return;
        }

        $orderedIds = array_values(array_unique(array_map('intval', $orderedIds)));
        if (empty($orderedIds)) {
            return;
        }

        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                Product::where('id', $id)->update(['sort_order' => $index]);
            }
        });

        $this->dispatch('notify', message: 'Order updated.', type: 'success');
    }

    private function duplicateProduct(Product $product, bool $withVariants): void
    {
        $new = $this->duplicateProductToNew($product, $withVariants);

        $this->edit($new->fresh());
    }

    private function duplicateProductToNew(Product $product, bool $withVariants): Product
    {
        $product->load(['variants', 'addonGroups', 'addons', 'setGroups.items']);

        return DB::transaction(function () use ($product, $withVariants) {
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
                'is_available' => (bool) ($product->is_available ?? true),
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
    }

    /**
     * Delete a product.
     */
    public function delete(Product $product): void
    {
        $product->delete();
    }

    /**
     * Open the import modal and reset state.
     */
    public function openImportModal(): void
    {
        $this->reset(['importFile', 'importErrors', 'importPreview', 'importSuccessCount', 'importDone']);
        $this->showImportModal = true;
    }

    /**
     * Close the import modal.
     */
    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset(['importFile', 'importErrors', 'importPreview', 'importSuccessCount', 'importDone']);
    }

    /**
     * Preview the uploaded CSV before committing.
     */
    public function previewImport(): void
    {
        $this->validateOnly('importFile', ['importFile' => 'required|file|mimes:csv,txt|max:2048']);

        $this->importErrors = [];
        $this->importPreview = [];

        $path = $this->importFile->getRealPath();
        $handle = fopen($path, 'r');

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            $this->importErrors[] = 'Could not read the CSV file. Please ensure it is a valid CSV.';
            fclose($handle);
            return;
        }

        // Normalize headers (lowercase, trim)
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        $required = ['name', 'category', 'price'];
        $missing = array_diff($required, $headers);
        if (!empty($missing)) {
            $this->importErrors[] = 'Missing required columns: ' . implode(', ', $missing);
            fclose($handle);
            return;
        }

        $row = 2;
        while (($data = fgetcsv($handle)) !== false) {
            if (empty(array_filter($data))) {
                $row++;
                continue; // skip blank rows
            }

            $record = array_combine($headers, array_pad($data, count($headers), ''));

            $name     = trim($record['name'] ?? '');
            $category = trim($record['category'] ?? '');
            $price    = trim($record['price'] ?? '');
            $desc     = trim($record['description'] ?? '');
            $badge    = trim($record['badge'] ?? '');
            $status   = strtolower(trim($record['status'] ?? 'active'));
            $order    = trim($record['sort_order'] ?? '0');
            $variantsRaw = trim($record['variants'] ?? '');

            $rowErrors = [];

            if (empty($name)) {
                $rowErrors[] = 'Name is required.';
            }
            if (empty($category)) {
                $rowErrors[] = 'Category is required.';
            }
            if (!is_numeric($price) || (float) $price < 0) {
                $rowErrors[] = 'Price must be a valid non-negative number.';
            }

            $parsedVariants = $this->parseVariantsString($variantsRaw);
            foreach ($parsedVariants['errors'] as $e) {
                $rowErrors[] = $e;
            }

            $this->importPreview[] = [
                'row'         => $row,
                'name'        => $name,
                'category'    => $category,
                'price'       => $price,
                'description' => $desc,
                'badge'       => $badge,
                'status'      => in_array($status, ['active', '1', 'yes', 'true']) ? 'active' : 'inactive',
                'sort_order'  => is_numeric($order) ? (int) $order : 0,
                'variants_raw' => $variantsRaw,
                'variants' => $parsedVariants['rows'],
                'variants_count' => count($parsedVariants['rows']),
                'errors'      => $rowErrors,
            ];

            $row++;
        }

        fclose($handle);

        if (empty($this->importPreview)) {
            $this->importErrors[] = 'No data rows found in the CSV.';
        }
    }

    /**
     * Commit the import: create or update products from the preview.
     */
    public function commitImport(): void
    {
        if (empty($this->importPreview)) {
            return;
        }

        // Only import rows without errors
        $valid = array_filter($this->importPreview, fn($r) => empty($r['errors']));

        if (empty($valid)) {
            $this->importErrors[] = 'No valid rows to import. Please fix the errors in your CSV.';
            return;
        }

        // Build a category name->id map (create if not exists)
        $categoryMap = Category::pluck('id', 'name')->toArray();

        $count = 0;

        DB::transaction(function () use ($valid, &$categoryMap, &$count) {
            foreach ($valid as $record) {
                $catName = $record['category'];

                if (!isset($categoryMap[$catName])) {
                    $cat = Category::create([
                        'name'      => $catName,
                        'is_active' => true,
                        'sort_order' => 0,
                    ]);
                    $categoryMap[$catName] = $cat->id;
                }

                $product = Product::create([
                    'name'        => $record['name'],
                    'category_id' => $categoryMap[$catName],
                    'price'       => (float) $record['price'],
                    'description' => $record['description'] ?: null,
                    'badge_text'  => $record['badge'] ?: null,
                    'is_active'   => $record['status'] === 'active',
                    'sort_order'  => $record['sort_order'],
                    'product_type' => 'ala_carte',
                    'is_available' => true,
                ]);

                $variantRows = is_array($record['variants'] ?? null) ? $record['variants'] : [];
                if (!empty($variantRows)) {
                    $product->variants()->createMany($variantRows);
                }

                $count++;
            }
        });

        $this->importSuccessCount = $count;
        $this->importDone = true;
        $this->importPreview = [];
        $this->importFile = null;
    }

    /**
     * Stream a CSV template file for download.
     */
    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'name',
                'category',
                'price',
                'description',
                'badge',
                'status',
                'sort_order',
            ]);

            // Example rows
            fputcsv($handle, ['Burger Ayam', 'Burgers', '8.50', 'Crispy fried chicken burger', 'HOT', 'active', '1']);
            fputcsv($handle, ['Ayam Goreng Set', 'Set Meals', '12.00', 'Comes with rice and drink', '', 'active', '2']);
            fputcsv($handle, ['Teh Tarik', 'Beverages', '3.00', 'Freshly pulled milk tea', '', 'active', '3']);

            fclose($handle);
        }, 'menu_import_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function downloadTemplateWithVariants(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'name',
                'category',
                'price',
                'description',
                'badge',
                'status',
                'sort_order',
                'variants',
            ]);

            fputcsv($handle, [
                'Teh Tarik',
                'Beverages',
                '3.00',
                'Freshly pulled milk tea',
                '',
                'active',
                '1',
                'Hot|HOT|3.00;Ice|AIS|3.50',
            ]);

            fputcsv($handle, [
                'Kopi O',
                'Beverages',
                '2.00',
                '',
                '',
                'active',
                '2',
                'Hot|HOT|2.00;Ice|AIS|2.50',
            ]);

            fputcsv($handle, [
                'Burger Ayam',
                'Burgers',
                '8.50',
                'Crispy fried chicken burger',
                'HOT',
                'active',
                '3',
                '',
            ]);

            fclose($handle);
        }, 'menu_import_template_with_variants.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportMenuBackup(): StreamedResponse
    {
        $timestamp = now()->format('Ymd_His');

        return response()->streamDownload(function () {
            $clean = fn ($value) => trim(str_replace(['|', ';'], ' ', (string) $value));

            $products = Product::query()
                ->with(['category', 'variants'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'name',
                'category',
                'price',
                'description',
                'badge',
                'status',
                'sort_order',
                'variants',
            ]);

            foreach ($products as $p) {
                $variantParts = [];
                foreach (($p->variants ?? collect())->where('is_active', true)->sortBy('id') as $v) {
                    $variantName = $clean($v->name);
                    $label = $clean($v->receipt_label ?? '');
                    $variantPrice = (string) $v->price;

                    $variantParts[] = $label !== ''
                        ? ($variantName . '|' . $label . '|' . $variantPrice)
                        : ($variantName . '|' . $variantPrice);
                }

                fputcsv($handle, [
                    $clean($p->name),
                    $clean($p->category?->name ?? ''),
                    (string) $p->price,
                    (string) ($p->description ?? ''),
                    (string) ($p->badge_text ?? ''),
                    $p->is_active ? 'active' : 'inactive',
                    (int) $p->sort_order,
                    implode(';', $variantParts),
                ]);
            }

            fclose($handle);
        }, 'menu_products_export_' . $timestamp . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function parseVariantsString(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return ['rows' => [], 'errors' => []];
        }

        $rows = [];
        $errors = [];

        $parts = array_map('trim', explode(';', $raw));
        foreach ($parts as $index => $part) {
            if ($part === '') {
                continue;
            }

            $cells = array_map('trim', explode('|', $part));
            $name = (string) ($cells[0] ?? '');
            $receiptLabel = '';
            $price = '';
            if (count($cells) >= 3) {
                $receiptLabel = (string) ($cells[1] ?? '');
                $price = (string) ($cells[2] ?? '');
            } else {
                $price = (string) ($cells[1] ?? '');
            }

            if ($name === '') {
                $errors[] = 'Variants: missing name at position ' . ($index + 1) . '.';
                continue;
            }

            if ($price === '' || !is_numeric($price) || (float) $price < 0) {
                $errors[] = 'Variants: invalid price for "' . $name . '".';
                continue;
            }

            $rows[] = [
                'name' => $name,
                'receipt_label' => $receiptLabel !== '' ? $receiptLabel : null,
                'price' => (float) $price,
                'is_active' => true,
            ];
        }

        return ['rows' => $rows, 'errors' => $errors];
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
            $catId = (int) $this->categoryFilter;
            $cat = Category::find($catId);
            if ($cat && !$cat->parent_id) {
                $childIds = Category::where('parent_id', $cat->id)->pluck('id')->all();
                $query->whereIn('category_id', array_values(array_unique(array_merge([$catId], array_map('intval', $childIds)))));
            } else {
                $query->where('category_id', $catId);
            }
        }

        // Apply status filter
        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if ($this->manualSort) {
            $products = $query->orderBy('sort_order')->orderBy('id')->get();
        } else {
            $products = $query->orderBy('sort_order')->paginate(10);
        }

        $items = $products instanceof \Illuminate\Pagination\LengthAwarePaginator ? $products->items() : $products;
        foreach ($items as $p) {
            $id = (string) $p->id;
            $currentActive = (bool) $p->is_active;
            $currentAvailable = (bool) ($p->is_available ?? true);

            if (!array_key_exists($id, $this->rowIsActive) || (bool) $this->rowIsActive[$id] !== $currentActive) {
                $this->rowIsActive[$id] = $currentActive;
            }
            if (!array_key_exists($id, $this->rowIsAvailable) || (bool) $this->rowIsAvailable[$id] !== $currentAvailable) {
                $this->rowIsAvailable[$id] = $currentAvailable;
            }
        }

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

        return view('livewire.menu.products', [
            'products' => $products,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'categoryOptions' => $categoryOptions,
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
