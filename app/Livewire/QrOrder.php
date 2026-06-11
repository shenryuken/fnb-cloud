<?php

namespace App\Livewire;

use App\Actions\Orders\BuildOrderDataAction;
use App\Actions\Orders\CreateOrderAction;
use App\Exceptions\OrderException;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Public, unauthenticated QR self-ordering page.
 *
 * Reached by scanning a table's QR code (/order/{token}). The table token
 * resolves both the table and its tenant; we bind the tenant_id into the
 * container so every tenant-scoped query (products, categories, taxes) is
 * automatically restricted to the correct venue.
 *
 * Orders are placed through the same BuildOrderDataAction + CreateOrderAction
 * pipeline used by the POS and API, so pricing is server-authoritative and the
 * order lands in KDS/Orders exactly like a staff-entered one (source = 'qr').
 */
#[Layout('layouts.guest')]
#[Title('Order')]
class QrOrder extends Component
{
    public string $token = '';

    public ?int $tableId = null;
    public ?int $tenantId = null;

    public string $search = '';
    public ?int $selectedCategoryId = null;

    /** @var array<int, array{product_id:int, name:string, variant_id:?int, variant_name:?string, addon_ids:array<int>, addons:array, quantity:int, unit_price:float, line_total:float, notes:string}> */
    public array $cart = [];

    public string $guestName = '';
    public string $orderNotes = '';

    // Product detail modal
    public ?int $selectingProductId = null;
    public ?int $selectedVariantId = null;
    public array $selectedAddonIds = [];
    public int $quantity = 1;
    public string $itemNotes = '';

    public bool $showCart = false;
    public bool $placing = false;

    // Confirmation state
    public ?int $placedOrderId = null;
    public ?string $placedOrderNumber = null;

    public function mount(string $token): void
    {
        $this->token = $token;

        // Resolve the table globally (no tenant bound yet), then bind its tenant.
        $table = RestaurantTable::withoutGlobalScopes()
            ->where('qr_token', $token)
            ->first();

        if (!$table) {
            abort(404);
        }

        $tenant = Tenant::find($table->tenant_id);
        if (!$tenant || !$tenant->is_active || ($tenant->status ?? 'active') === 'suspended') {
            abort(404);
        }

        if (!$tenant->qr_ordering_enabled) {
            abort(403, 'Online ordering is currently unavailable for this table.');
        }

        // Bind tenant for the remainder of the request so TenantScope applies.
        app()->instance('tenant_id', $tenant->id);

        $this->tableId = $table->id;
        $this->tenantId = $tenant->id;
    }

    /**
     * Re-bind the tenant on every Livewire request (mount only runs once).
     */
    public function hydrate(): void
    {
        if ($this->tenantId) {
            app()->instance('tenant_id', $this->tenantId);
        }
    }

    #[Computed]
    public function tenant(): Tenant
    {
        return Tenant::findOrFail($this->tenantId);
    }

    #[Computed]
    public function table(): RestaurantTable
    {
        return RestaurantTable::findOrFail($this->tableId);
    }

    public function currency(): string
    {
        return trim((string) ($this->tenant->currency_symbol ?? 'RM'));
    }

    public function money(float $amount): string
    {
        return $this->currency() . ' ' . number_format($amount, 2);
    }

    #[Computed]
    public function categories()
    {
        return Category::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function products()
    {
        $query = Product::query()
            ->where('is_active', true)
            ->where('is_available', true)
            ->with(['variants', 'addons']);

        if ($this->selectedCategoryId) {
            $childIds = Category::where('parent_id', $this->selectedCategoryId)->pluck('id')->all();
            $query->whereIn('category_id', array_merge([$this->selectedCategoryId], $childIds));
        }

        if (trim($this->search) !== '') {
            $query->where('name', 'like', '%' . trim($this->search) . '%');
        }

        return $query->orderBy('sort_order')->orderBy('name')->get();
    }

    #[Computed]
    public function selectingProduct(): ?Product
    {
        if (!$this->selectingProductId) {
            return null;
        }

        return Product::with(['variants', 'addons'])->find($this->selectingProductId);
    }

    public function openProduct(int $productId): void
    {
        $product = Product::with(['variants', 'addons'])->find($productId);
        if (!$product) {
            return;
        }

        $this->selectingProductId = $productId;
        $this->selectedVariantId = $product->variants->first()?->id;
        $this->selectedAddonIds = [];
        $this->quantity = 1;
        $this->itemNotes = '';
    }

    public function closeProduct(): void
    {
        $this->reset(['selectingProductId', 'selectedVariantId', 'selectedAddonIds', 'quantity', 'itemNotes']);
    }

    public function addToCart(): void
    {
        $product = $this->selectingProduct;
        if (!$product) {
            return;
        }

        $unitPrice = (float) $product->price;
        $variantName = null;
        if ($this->selectedVariantId) {
            $variant = $product->variants->firstWhere('id', $this->selectedVariantId);
            if ($variant) {
                $unitPrice = (float) $variant->price;
                $variantName = $variant->name;
            }
        }

        $addons = $product->addons->whereIn('id', $this->selectedAddonIds);
        $addonTotal = (float) $addons->sum('price');

        $qty = max(1, (int) $this->quantity);
        $lineTotal = round(($unitPrice + $addonTotal) * $qty, 2);

        $this->cart[] = [
            'product_id' => (int) $product->id,
            'name' => $product->name,
            'variant_id' => $this->selectedVariantId ? (int) $this->selectedVariantId : null,
            'variant_name' => $variantName,
            'addon_ids' => array_values(array_map('intval', $this->selectedAddonIds)),
            'addons' => $addons->map(fn ($a) => ['name' => $a->name, 'price' => (float) $a->price])->values()->all(),
            'quantity' => $qty,
            'unit_price' => round($unitPrice, 2),
            'line_total' => $lineTotal,
            'notes' => trim($this->itemNotes),
        ];

        $this->closeProduct();
        $this->dispatch('item-added');
    }

    public function removeFromCart(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);

        if (empty($this->cart)) {
            $this->showCart = false;
        }
    }

    public function incrementQty(int $index): void
    {
        if (!isset($this->cart[$index])) {
            return;
        }
        $this->cart[$index]['quantity']++;
        $this->recalcLine($index);
    }

    public function decrementQty(int $index): void
    {
        if (!isset($this->cart[$index])) {
            return;
        }
        if ($this->cart[$index]['quantity'] <= 1) {
            $this->removeFromCart($index);
            return;
        }
        $this->cart[$index]['quantity']--;
        $this->recalcLine($index);
    }

    private function recalcLine(int $index): void
    {
        $row = $this->cart[$index];
        $addonTotal = collect($row['addons'])->sum('price');
        $this->cart[$index]['line_total'] = round(((float) $row['unit_price'] + (float) $addonTotal) * (int) $row['quantity'], 2);
    }

    #[Computed]
    public function cartCount(): int
    {
        return collect($this->cart)->sum('quantity');
    }

    #[Computed]
    public function cartSubtotal(): float
    {
        return round(collect($this->cart)->sum('line_total'), 2);
    }

    public function placeOrder(BuildOrderDataAction $build, CreateOrderAction $create): void
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', type: 'error', message: 'Your cart is empty.');
            return;
        }

        if ($this->tenant->qr_ordering_requires_name && trim($this->guestName) === '') {
            $this->dispatch('notify', type: 'error', message: 'Please enter your name.');
            return;
        }

        $this->placing = true;

        try {
            $input = [
                'order_type' => 'dine_in',
                'table_id' => $this->tableId,
                'table_number' => $this->table->name,
                'payment_status' => 'unpaid',
                'notes' => $this->buildOrderNotes(),
                'items' => collect($this->cart)->map(fn ($row) => [
                    'product_id' => $row['product_id'],
                    'variant_id' => $row['variant_id'],
                    'quantity' => $row['quantity'],
                    'addon_ids' => $row['addon_ids'],
                    'notes' => $row['notes'] ?: null,
                ])->all(),
            ];

            // userId is null — this is a guest-placed order.
            $data = $build->build($input, null, 'qr');
            $order = $create->execute($data);

            $this->placedOrderId = $order->id;
            $this->placedOrderNumber = '#' . $order->id;
            $this->reset(['cart', 'showCart', 'orderNotes']);
        } catch (OrderException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Something went wrong placing your order. Please ask a staff member.');
        } finally {
            $this->placing = false;
        }
    }

    private function buildOrderNotes(): ?string
    {
        $parts = [];
        if (trim($this->guestName) !== '') {
            $parts[] = 'Guest: ' . trim($this->guestName);
        }
        if (trim($this->orderNotes) !== '') {
            $parts[] = trim($this->orderNotes);
        }

        return empty($parts) ? null : implode(' — ', $parts);
    }

    public function startNewOrder(): void
    {
        $this->reset(['placedOrderId', 'placedOrderNumber', 'guestName', 'orderNotes', 'cart']);
    }

    public function render()
    {
        return view('livewire.qr-order');
    }
}
