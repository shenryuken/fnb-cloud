<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ProductAddon;
use App\Models\Customer;
use App\Models\Voucher;
use App\Models\CustomerVoucher;
use App\Models\HeldOrder;
use App\Models\Shift;
use App\Models\RestaurantTable;
use Livewire\Attributes\Title;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Title('POS')]
#[Lazy]
class Pos extends Component
{
    public $search = '';
    public $selectedCategoryId = null;
    
    // Cart management
    public array $cart = [];
    public float $subTotalAmount = 0;
    public float $totalAmount = 0;
    public string $discountType = 'percent';
    public float $discountValue = 0;
    public float $discountAmount = 0;
    public float $manualDiscountAmount = 0;
    public string $discountInputType = 'percent';
    public float $discountInputValue = 0;
    public float $taxRate = 0;
    public array $taxes = [];
    public array $taxBreakdown = [];
    public float $taxAmount = 0;
    public string $taxLabel = '0%';
    
    // Item selection modal state
    public ?Product $selectingProduct = null;
    public ?int $selectedVariantId = null;
    public array $selectedAddonIds = [];
    public array $selectedSetItems = [];
    public int $quantity = 1;
    public string $notes = '';

    // Order details
    public string $tableNumber = '';
    
    #[Url(as: 'table')]
    public ?int $tableId = null;
    
    #[Url(as: 'pay')]
    public ?int $payOrderId = null; // auto-open payment modal for this order
    
    #[Url(as: 'addto')]
    public ?int $addToOrderId = null; // load existing order for adding more items
    
    public ?Order $existingOrder = null; // the order we're adding items to
    
    public string $orderType = 'dine_in'; // dine_in, takeaway
    public string $orderNotes = '';

    // Payment state
    public bool $isPaying = false;
    public bool $isPayLater = false; // true = send to kitchen without payment (pay later)
    public string $paymentMethod = 'cash';   // kept for single-method fast path
    public float $amountReceived = 0;
    public float $changeAmount = 0;
    public ?Order $lastOrder = null;
    public bool $showBillPreview = false; // for printing bill before payment

    // Split payment state
    public bool $isSplitPayment = false;
    public array $paymentSplits = [];        // [['method' => 'cash', 'amount' => 50.00], ...]
    public string $splitMethod = 'cash';
    public float $splitAmount = 0;
    public float $splitRemaining = 0;
    public bool $isKitchenBusy = false;
    public bool $showCartMobile = false;
    public bool $showDiscountModal = false;
    public bool $showHeldOrdersModal = false;
    public bool $showUnpaidOrdersModal = false;
    public ?Order $selectedUnpaidOrder = null; // for collecting payment on existing order
    public string $discountTab = 'discount';
    public ?int $customerId = null;
    public string $customerSearch = '';
    public string $newCustomerName = '';
    public string $newCustomerEmail = '';
    public string $newCustomerMobile = '';
    public string $voucherCode = '';
    public string $appliedVoucherCode = '';
    public ?int $appliedVoucherId = null;
    public array $appliedVoucherMeta = [];
    public string $voucherDiscountType = 'percent';
    public float $voucherDiscountValue = 0;
    public float $voucherDiscountAmount = 0;
    public int $pointsToRedeem = 0;
    public int $appliedPoints = 0;
    public float $pointsDiscountAmount = 0;
    public float $pointsEarnRate = 1;
    public float $pointsRedeemValuePer100 = 1;
    public int $pointsMinRedeem = 0;
    public float $pointsEarnPoints = 1;
    public float $pointsEarnAmount = 1;
    public int $pointsRedeemPoints = 100;
    public float $pointsRedeemAmount = 1;
    public bool $pointsPromoIsEnabled = false;
    public float $pointsPromoMultiplier = 1;
    public ?string $pointsPromoStartsAt = null;
    public ?string $pointsPromoEndsAt = null;
    public array $issuedVoucherCodes = [];

    #[Computed]
    public function categories()
    {
        return Category::where('is_active', true)->orderBy('sort_order')->get();
    }

    #[Computed]
    public function products()
    {
        $productsQuery = Product::where('is_active', true)
            ->with(['category', 'variants', 'addons', 'addonGroups.items']);

        if ($this->selectedCategoryId) {
            $productsQuery->where('category_id', $this->selectedCategoryId);
        }

        if ($this->search) {
            $productsQuery->where('name', 'like', '%' . $this->search . '%');
        }

        return $productsQuery->orderBy('sort_order')->get();
    }

    #[Computed]
    public function autoBadges(): array
    {
        $tenantId = Auth::user()?->tenant_id;
        if (!$tenantId) {
            return [];
        }

        return Cache::remember("pos:auto-badges:{$tenantId}", 300, function () use ($tenantId) {
            $since = now()->subDays(7);

            $rows = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.tenant_id', $tenantId)
                ->where('orders.status', 'completed')
                ->where('orders.payment_status', 'paid')
                ->where('orders.created_at', '>=', $since)
                ->groupBy('order_items.product_id')
                ->orderByDesc(DB::raw('SUM(order_items.quantity)'))
                ->limit(5)
                ->pluck('order_items.product_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $badges = [];
            foreach ($rows as $i => $productId) {
                $badges[$productId] = $i === 0 ? 'Top Sale' : 'Hot';
            }

            return $badges;
        });
    }

    #[Computed]
    public function customer(): ?Customer
    {
        if (!$this->customerId) {
            return null;
        }

        return Customer::find($this->customerId);
    }

    #[Computed]
    public function customerSearchResults(): array
    {
        $q = trim((string) $this->customerSearch);
        $normalizedMobile = $this->normalizeMobile($q);

        if (mb_strlen($q) < 2) {
            return Customer::query()
                ->orderByDesc('id')
                ->limit(8)
                ->get(['id', 'name', 'email', 'mobile', 'points_balance'])
                ->toArray();
        }

        return Customer::query()
            ->where(function ($query) use ($q, $normalizedMobile) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%')
                    ->orWhere('mobile', 'like', '%' . $normalizedMobile . '%');
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'email', 'mobile', 'points_balance'])
            ->toArray();
    }

    #[Computed]
    public function quickNotes(): array
    {
        $tenant = Auth::user()->tenant;

        return $tenant->quickNotes()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(20)
            ->pluck('text')
            ->map(fn ($t) => (string) $t)
            ->all();
    }

    #[Computed]
    public function currentShift(): ?Shift
    {
        return Shift::currentOpen();
    }

    #[Computed]
    public function availableTables()
    {
        return RestaurantTable::where('is_active', true)
            ->whereNull('merged_into_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function selectedTable(): ?RestaurantTable
    {
        if (!$this->tableId) return null;
        return RestaurantTable::find($this->tableId);
    }

    #[Computed]
    public function heldOrders(): array
    {
        $tenantId = Auth::user()?->tenant_id;
        if (!$tenantId) {
            return [];
        }

        return HeldOrder::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('id')
            ->limit(15)
            ->get(['id', 'label', 'payload', 'created_at'])
            ->map(function (HeldOrder $h) {
                $payload = is_array($h->payload) ? $h->payload : [];
                $cart = is_array($payload['cart'] ?? null) ? $payload['cart'] : [];
                $itemsCount = 0;
                foreach ($cart as $row) {
                    $itemsCount += (int) ($row['quantity'] ?? 0);
                }

                return [
                    'id' => $h->id,
                    'label' => (string) ($h->label ?? 'Held Order'),
                    'created_at' => $h->created_at?->format('M d, H:i'),
                    'items' => $itemsCount,
                    'total' => (float) ($payload['totalAmount'] ?? 0),
                    'customer_name' => (string) ($payload['customer_name'] ?? ''),
                ];
            })
            ->all();
    }

    #[Computed]
    public function unpaidOrders(): array
    {
        return Order::query()
            ->where('payment_status', 'unpaid')
            ->whereIn('status', ['pending', 'processing'])
            ->with(['items', 'table'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'table_number' => $o->table_number,
                'table_id' => $o->table_id,
                'order_type' => $o->order_type,
                'total_amount' => (float) $o->total_amount,
                'items_count' => $o->items->sum('quantity'),
                'created_at' => $o->created_at?->format('M d, H:i'),
                'kds_status' => $o->kds_status,
            ])
            ->all();
    }

    public function mount(): void
    {
        $tenant = Auth::user()->tenant;
        $this->isKitchenBusy = (bool) $tenant->is_busy;
        $this->loadTaxes();
        $this->loadLoyaltySettings();
        $this->recalculateTotals();
        $this->discountInputType = $this->discountType;
        $this->discountInputValue = $this->discountValue;

        // Handle table assignment from URL query parameter (?table=1)
        // The #[Url] attribute automatically populates $this->tableId from the query string
        if ($this->tableId) {
            $restaurantTable = RestaurantTable::find($this->tableId);
            if ($restaurantTable && $restaurantTable->is_active) {
                $this->tableNumber = $restaurantTable->name;
                $this->orderType = 'dine_in';
            } else {
                // Invalid table ID, reset it
                $this->tableId = null;
            }
        }
        
        // Handle auto-opening payment collection for an unpaid order (?pay=123)
        if ($this->payOrderId) {
            $order = Order::where('id', $this->payOrderId)
                ->where('payment_status', 'unpaid')
                ->first();
            
            if ($order) {
                $this->selectUnpaidOrder($this->payOrderId);
            }
            
            // Clear the URL parameter
            $this->payOrderId = null;
        }
        
        // Handle adding items to an existing order (?addto=123)
        if ($this->addToOrderId) {
            $order = Order::with('items.product')->find($this->addToOrderId);
            
            if ($order && in_array($order->kds_status, ['preparing', 'ready', 'served'])) {
                $this->existingOrder = $order;
                $this->orderType = $order->order_type ?? 'dine_in';
                $this->orderNotes = $order->notes ?? '';
                $this->customerId = $order->customer_id;
                
                // Load existing items into cart so staff can see what's already ordered
                foreach ($order->items as $item) {
                    $this->cart[] = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name ?? $item->product?->name ?? 'Unknown',
                        'variant_id' => $item->variant_id,
                        'variant_name' => $item->variant?->name,
                        'addon_ids' => [],
                        'addons' => [],
                        'set_items' => [],
                        'quantity' => $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'addons_total' => 0,
                        'set_total' => 0,
                        'subtotal' => (float) $item->subtotal,
                        'notes' => $item->notes ?? '',
                        'existing' => true, // Mark as existing item (cannot be removed)
                    ];
                }
                
                $this->calculateTotal();
            }
            
            // Clear the URL parameter
            $this->addToOrderId = null;
        }
    }

    /**
     * Called when tableId property is updated (from URL or select dropdown).
     */
    public function updatedTableId(?int $value): void
    {
        if ($value) {
            $table = RestaurantTable::find($value);
            if ($table && $table->is_active) {
                $this->tableNumber = $table->name;
                $this->orderType = 'dine_in';
            } else {
                $this->tableId = null;
                $this->tableNumber = '';
            }
        } else {
            $this->tableNumber = '';
        }
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="flex h-[calc(100vh-4rem)] items-center justify-center bg-neutral-50 dark:bg-neutral-950">
            <div class="flex flex-col items-center gap-4">
                <div class="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm font-black text-neutral-400 uppercase tracking-widest">Loading POS...</p>
            </div>
        </div>
        HTML;
    }

    public function rendering($view, $data)
    {
        $tenant = Auth::user()->tenant;
        $this->isKitchenBusy = (bool) $tenant->is_busy;

        $this->loadTaxes();
        $this->loadLoyaltySettings();
    }

    private function loadLoyaltySettings(): void
    {
        $tenant = Auth::user()->tenant;

        $earnPoints = (float) ($tenant->points_earn_points ?? ($tenant->points_earn_rate ?? 1));
        $earnAmount = (float) ($tenant->points_earn_amount ?? 1);
        $earnAmount = $earnAmount > 0 ? $earnAmount : 1;
        $earnRate = round(max(0, $earnPoints / $earnAmount), 4);

        $redeemPoints = (int) ($tenant->points_redeem_points ?? 100);
        $redeemPoints = $redeemPoints > 0 ? $redeemPoints : 100;
        $redeemAmount = (float) ($tenant->points_redeem_amount ?? ($tenant->points_redeem_value_per_100 ?? 1));
        $redeemAmount = $redeemAmount >= 0 ? $redeemAmount : 0;
        $valuePer100 = round(max(0, $redeemAmount * (100 / $redeemPoints)), 2);
        $minRedeem = max(0, (int) ($tenant->points_min_redeem ?? 0));
        $promoIsEnabled = (bool) ($tenant->points_promo_is_enabled ?? false);
        $promoMultiplier = round(max(0, (float) ($tenant->points_promo_multiplier ?? 1)), 2);
        $promoStartsAt = $tenant->points_promo_starts_at?->format('Y-m-d\TH:i');
        $promoEndsAt = $tenant->points_promo_ends_at?->format('Y-m-d\TH:i');

        if (
            round((float) $this->pointsEarnRate, 4) === $earnRate
            && round((float) $this->pointsRedeemValuePer100, 2) === $valuePer100
            && (int) $this->pointsMinRedeem === $minRedeem
            && round((float) $this->pointsEarnPoints, 4) === round($earnPoints, 4)
            && round((float) $this->pointsEarnAmount, 2) === round($earnAmount, 2)
            && (int) $this->pointsRedeemPoints === $redeemPoints
            && round((float) $this->pointsRedeemAmount, 2) === round($redeemAmount, 2)
            && (bool) $this->pointsPromoIsEnabled === $promoIsEnabled
            && round((float) $this->pointsPromoMultiplier, 2) === $promoMultiplier
            && (string) ($this->pointsPromoStartsAt ?? '') === (string) ($promoStartsAt ?? '')
            && (string) ($this->pointsPromoEndsAt ?? '') === (string) ($promoEndsAt ?? '')
        ) {
            return;
        }

        $this->pointsEarnRate = $earnRate;
        $this->pointsRedeemValuePer100 = $valuePer100;
        $this->pointsMinRedeem = $minRedeem;
        $this->pointsEarnPoints = round(max(0, $earnPoints), 4);
        $this->pointsEarnAmount = round(max(0.01, $earnAmount), 2);
        $this->pointsRedeemPoints = $redeemPoints;
        $this->pointsRedeemAmount = round(max(0, $redeemAmount), 2);
        $this->pointsPromoIsEnabled = $promoIsEnabled;
        $this->pointsPromoMultiplier = $promoMultiplier;
        $this->pointsPromoStartsAt = $promoStartsAt;
        $this->pointsPromoEndsAt = $promoEndsAt;
    }

    private function currentPointsPromoMultiplier(): float
    {
        if (!$this->pointsPromoIsEnabled) {
            return 1.0;
        }

        $multiplier = max(0, (float) $this->pointsPromoMultiplier);
        if ($multiplier <= 0) {
            return 1.0;
        }

        $now = now();
        $startsAt = filled($this->pointsPromoStartsAt) ? \Illuminate\Support\Carbon::parse($this->pointsPromoStartsAt) : null;
        $endsAt = filled($this->pointsPromoEndsAt) ? \Illuminate\Support\Carbon::parse($this->pointsPromoEndsAt) : null;

        if ($startsAt && $now->lt($startsAt)) {
            return 1.0;
        }
        if ($endsAt && $now->gt($endsAt)) {
            return 1.0;
        }

        return $multiplier;
    }

    private function loadTaxes(): void
    {
        $tenant = Auth::user()->tenant;
        $rows = $tenant->taxes()->where('is_enabled', true)->orderBy('name')->get(['name', 'rate'])->toArray();
        $sum = 0.0;
        $parts = [];
        foreach ($rows as $t) {
            $rate = (float) ($t['rate'] ?? 0);
            $sum += $rate;
            $parts[] = ($t['name'] ?? 'Tax') . ' ' . rtrim(rtrim(number_format($rate, 2), '0'), '.') . '%';
        }
        $sum = round($sum, 2);
        $label = $parts ? implode(' + ', $parts) : '0%';

        if ($this->taxes === $rows && round((float) $this->taxRate, 2) === $sum && $this->taxLabel === $label) {
            return;
        }

        $this->taxes = $rows;
        $this->taxRate = $sum;
        $this->taxLabel = $label;
        $this->recalculateTotals();
    }

    /**
     * Start selecting a product (opens modal).
     */
    public function selectProduct(Product $product): void
    {
        $this->selectingProduct = $product->load(['variants', 'addonGroups.items', 'addons', 'setGroups.items.product']);
        $this->selectedVariantId = null;
        $this->selectedAddonIds = [];
        $this->selectedSetItems = [];
        $this->quantity = 1;
        $this->notes = '';
    }

    public function quickAddProduct(Product $product): void
    {
        if (($product->product_type ?? 'ala_carte') === 'set') {
            $this->selectProduct($product);
            return;
        }

        $variantId = null;
        $unitPrice = (float) $product->price;

        foreach ($this->cart as $index => $cartItem) {
            if (
                (int) $cartItem['product_id'] === (int) $product->id
                && (int) ($cartItem['variant_id'] ?? 0) === (int) ($variantId ?? 0)
                && empty($cartItem['addon_ids'])
                && ($cartItem['notes'] ?? '') === ''
            ) {
                $this->cart[$index]['quantity'] = (int) $this->cart[$index]['quantity'] + 1;
                $this->cart[$index]['subtotal'] = round($unitPrice * (int) $this->cart[$index]['quantity'], 2);
                $this->calculateTotal();
                return;
            }
        }

        $this->cart[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'variant_id' => $variantId,
            'variant_name' => null,
            'addon_ids' => [],
            'addon_names' => [],
            'set_items' => [],
            'set_total' => 0,
            'set_summary' => '',
            'quantity' => 1,
            'unit_price' => $unitPrice,
            'addons_total' => 0,
            'subtotal' => round($unitPrice, 2),
            'notes' => '',
        ];

        $this->calculateTotal();
    }

    /**
     * Close product selection modal.
     */
    public function cancelSelection(): void
    {
        $this->selectingProduct = null;
        $this->reset(['selectedVariantId', 'selectedAddonIds', 'selectedSetItems', 'quantity', 'notes']);
    }

    /**
     * Add the selected product with its variants/addons to the cart.
     */
    public function addToCart(): void
    {
        if (!$this->selectingProduct) return;

        $variant = null;
        if ($this->selectedVariantId) {
            $variant = ProductVariant::find($this->selectedVariantId);
        }

        $addons = ProductAddon::whereIn('id', $this->selectedAddonIds)->get();

        $setItems = [];
        $setTotal = 0.0;
        $setSummary = '';
        if (($this->selectingProduct->product_type ?? 'ala_carte') === 'set') {
            $groups = $this->selectingProduct->setGroups ?? collect();

            foreach ($groups as $group) {
                $raw = $this->selectedSetItems[$group->id] ?? [];
                $selectedIds = is_array($raw) ? $raw : [$raw];
                $selectedIds = array_values(array_filter(array_map(fn ($v) => (int) $v, $selectedIds)));

                $count = count($selectedIds);
                $min = (int) ($group->min_select ?? 0);
                $max = (int) ($group->max_select ?? 1);

                if ($count < $min) {
                    $this->dispatch('notify', message: "Select at least {$min} item(s) for {$group->name}.", type: 'error');
                    return;
                }
                if ($count > $max) {
                    $this->dispatch('notify', message: "Select at most {$max} item(s) for {$group->name}.", type: 'error');
                    return;
                }

                foreach ($selectedIds as $pid) {
                    $row = $group->items->firstWhere('product_id', $pid);
                    if (!$row) {
                        continue;
                    }
                    $name = $row->product?->name ?? ('#' . $pid);
                    $extra = (float) ($row->extra_price ?? 0);
                    $setItems[] = [
                        'product_id' => $pid,
                        'group_name' => (string) ($group->name ?? ''),
                        'name' => $name,
                        'extra_price' => round(max(0, $extra), 2),
                    ];
                    $setTotal += $extra;
                }
            }

            $setTotal = round(max(0, $setTotal), 2);
            $setSummary = implode(', ', array_map(fn ($r) => (string) ($r['name'] ?? ''), $setItems));
        }
        
        $itemPrice = $this->selectingProduct->price;
        if ($variant) {
            $itemPrice = $variant->price;
        }
        
        $addonsPrice = $addons->sum('price');
        $subtotal = ($itemPrice + $addonsPrice + $setTotal) * $this->quantity;

        $this->cart[] = [
            'product_id' => $this->selectingProduct->id,
            'product_name' => $this->selectingProduct->name,
            'variant_id' => $this->selectedVariantId,
            'variant_name' => $variant?->name,
            'addon_ids' => $this->selectedAddonIds,
            'addon_names' => $addons->pluck('name')->toArray(),
            'set_items' => $setItems,
            'set_total' => $setTotal,
            'set_summary' => $setSummary,
            'quantity' => $this->quantity,
            'unit_price' => $itemPrice,
            'addons_total' => $addonsPrice,
            'subtotal' => $subtotal,
            'notes' => $this->notes,
        ];

        $this->calculateTotal();
        $this->cancelSelection();
    }

    public function applyQuickNote(string $text): void
    {
        $text = trim($text);
        if (!filled($text)) {
            return;
        }

        $current = trim((string) $this->notes);
        if ($current === '') {
            $this->notes = $text;
            return;
        }

        if (str_contains(mb_strtolower($current), mb_strtolower($text))) {
            return;
        }

        $separator = str_ends_with($current, ',') ? ' ' : ', ';
        $this->notes = $current . $separator . $text;
    }

    /**
     * Remove an item from the cart.
     */
    public function removeFromCart(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotal();
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->subTotalAmount = 0;
        $this->discountType = 'percent';
        $this->discountValue = 0;
        $this->discountAmount = 0;
        $this->manualDiscountAmount = 0;
        $this->discountInputType = 'percent';
        $this->discountInputValue = 0;
        $this->voucherCode = '';
        $this->appliedVoucherCode = '';
        $this->appliedVoucherId = null;
        $this->appliedVoucherMeta = [];
        $this->voucherDiscountType = 'percent';
        $this->voucherDiscountValue = 0;
        $this->voucherDiscountAmount = 0;
        $this->pointsToRedeem = 0;
        $this->appliedPoints = 0;
        $this->pointsDiscountAmount = 0;
        $this->customerId = null;
        $this->taxBreakdown = [];
        $this->taxAmount = 0;
        $this->totalAmount = 0;
        $this->orderNotes = '';
        $this->isPaying = false;
        $this->isSplitPayment = false;
        $this->paymentSplits = [];
        $this->splitMethod = 'cash';
        $this->splitAmount = 0;
        $this->splitRemaining = 0;
        $this->amountReceived = 0;
        $this->changeAmount = 0;
        $this->tableId = null;
        $this->tableNumber = '';

        $this->recalculateTotals();
    }

    public function openHeldOrders(): void
    {
        $this->showHeldOrdersModal = true;
    }

    public function closeHeldOrders(): void
    {
        $this->showHeldOrdersModal = false;
    }

    public function holdOrder(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Cart is empty.', type: 'error');
            return;
        }

        $customerName = $this->customer?->name ?? null;
        $label = $customerName
            ?: ($this->orderType === 'dine_in' && filled($this->tableNumber) ? 'Table ' . trim($this->tableNumber) : 'Walk-in');

        HeldOrder::create([
            'user_id' => Auth::id(),
            'label' => $label,
            'payload' => [
                'cart' => $this->cart,
                'orderType' => $this->orderType,
                'tableNumber' => $this->tableNumber,
                'orderNotes' => $this->orderNotes,
                'discountType' => $this->discountType,
                'discountValue' => $this->discountValue,
                'discountAmount' => $this->discountAmount,
                'manualDiscountAmount' => $this->manualDiscountAmount,
                'appliedVoucherCode' => $this->appliedVoucherCode,
                'appliedVoucherId' => $this->appliedVoucherId,
                'appliedVoucherMeta' => $this->appliedVoucherMeta,
                'voucherDiscountType' => $this->voucherDiscountType,
                'voucherDiscountValue' => $this->voucherDiscountValue,
                'appliedPoints' => $this->appliedPoints,
                'pointsDiscountAmount' => $this->pointsDiscountAmount,
                'customerId' => $this->customerId,
                'customer_name' => $customerName,
                'subTotalAmount' => $this->subTotalAmount,
                'taxAmount' => $this->taxAmount,
                'totalAmount' => $this->totalAmount,
            ],
        ]);

        $this->clearCart();
        $this->tableNumber = '';
        $this->orderType = 'dine_in';
        $this->dispatch('notify', message: 'Order held.', type: 'success');
    }

    public function recallHeldOrder(int $heldOrderId): void
    {
        $tenantId = Auth::user()?->tenant_id;
        if (!$tenantId) {
            return;
        }

        $held = HeldOrder::where('tenant_id', $tenantId)->find($heldOrderId);
        if (!$held) {
            $this->dispatch('notify', message: 'Held order not found.', type: 'error');
            return;
        }

        $payload = is_array($held->payload) ? $held->payload : [];

        $this->cart = is_array($payload['cart'] ?? null) ? $payload['cart'] : [];
        $this->orderType = (string) ($payload['orderType'] ?? 'dine_in');
        $this->tableNumber = (string) ($payload['tableNumber'] ?? '');
        $this->orderNotes = (string) ($payload['orderNotes'] ?? '');
        $this->discountType = (string) ($payload['discountType'] ?? 'percent');
        $this->discountValue = (float) ($payload['discountValue'] ?? 0);
        $this->discountAmount = (float) ($payload['discountAmount'] ?? 0);
        $this->manualDiscountAmount = (float) ($payload['manualDiscountAmount'] ?? 0);
        $this->discountInputType = $this->discountType;
        $this->discountInputValue = $this->discountValue;
        $this->appliedVoucherCode = (string) ($payload['appliedVoucherCode'] ?? '');
        $this->appliedVoucherId = filled($payload['appliedVoucherId'] ?? null) ? (int) $payload['appliedVoucherId'] : null;
        $this->appliedVoucherMeta = is_array($payload['appliedVoucherMeta'] ?? null) ? (array) $payload['appliedVoucherMeta'] : [];
        $this->voucherDiscountType = (string) ($payload['voucherDiscountType'] ?? 'percent');
        $this->voucherDiscountValue = (float) ($payload['voucherDiscountValue'] ?? 0);
        $this->voucherCode = '';
        $this->appliedPoints = (int) ($payload['appliedPoints'] ?? 0);
        $this->pointsDiscountAmount = (float) ($payload['pointsDiscountAmount'] ?? 0);
        $this->pointsToRedeem = 0;
        $this->customerId = filled($payload['customerId'] ?? null) ? (int) $payload['customerId'] : null;

        $held->delete();
        $this->recalculateTotals();
        $this->showHeldOrdersModal = false;
        $this->dispatch('notify', message: 'Held order recalled.', type: 'success');
    }

    public function deleteHeldOrder(int $heldOrderId): void
    {
        $tenantId = Auth::user()?->tenant_id;
        if (!$tenantId) {
            return;
        }

        HeldOrder::where('tenant_id', $tenantId)->where('id', $heldOrderId)->delete();
        $this->dispatch('notify', message: 'Held order deleted.', type: 'success');
    }

    /**
     * Update the quantity of an item in the cart.
     */
    public function updateQuantity(int $index, int $delta): void
    {
        if (!isset($this->cart[$index])) return;

        $this->cart[$index]['quantity'] += $delta;

        if ($this->cart[$index]['quantity'] <= 0) {
            $this->removeFromCart($index);
            return;
        }

        $setTotal = (float) ($this->cart[$index]['set_total'] ?? 0);
        $this->cart[$index]['subtotal'] = ($this->cart[$index]['unit_price'] + $this->cart[$index]['addons_total'] + $setTotal) * $this->cart[$index]['quantity'];
        $this->calculateTotal();
    }

    /**
     * Calculate the total amount of the cart.
     */
    private function calculateTotal(): void
    {
        $this->subTotalAmount = (float) collect($this->cart)->sum('subtotal');
        $this->recalculateTotals();
    }

    public function updatedDiscountType(): void
    {
        $this->recalculateTotals();
    }

    public function updatedDiscountValue(): void
    {
        $this->discountValue = round(max(0, (float) $this->discountValue), 2);
        $this->recalculateTotals();
    }

    public function updatedTaxRate(): void
    {
        $this->taxRate = round(max(0, (float) $this->taxRate), 2);
        $this->recalculateTotals();
    }

    private function recalculateTotals(): void
    {
        $subTotal = round(max(0, (float) $this->subTotalAmount), 2);

        $manualDiscount = 0.0;
        if ($this->discountType === 'fixed') {
            $manualDiscount = round(min($subTotal, max(0, (float) $this->discountValue)), 2);
        } else {
            $rate = min(100, max(0, (float) $this->discountValue));
            $manualDiscount = round($subTotal * ($rate / 100), 2);
        }

        $remainingAfterManual = round(max(0, $subTotal - $manualDiscount), 2);

        $voucherDiscount = 0.0;
        if (filled($this->appliedVoucherCode) && (float) $this->voucherDiscountValue > 0) {
            if ($this->voucherDiscountType === 'fixed') {
                $voucherDiscount = round(min($remainingAfterManual, max(0, (float) $this->voucherDiscountValue)), 2);
            } else {
                $rate = min(100, max(0, (float) $this->voucherDiscountValue));
                $voucherDiscount = round($remainingAfterManual * ($rate / 100), 2);
            }
        }

        $remainingAfterVoucher = round(max(0, $remainingAfterManual - $voucherDiscount), 2);

        $pointsDiscount = 0.0;
        if ((int) $this->appliedPoints > 0) {
            $valuePerPoint = (float) $this->pointsRedeemAmount / max(1, (int) $this->pointsRedeemPoints);
            $amount = round(((int) $this->appliedPoints) * max(0, $valuePerPoint), 2);
            if ($amount > 0) {
                $pointsDiscount = round(min($remainingAfterVoucher, $amount), 2);
            }
        }

        $totalDiscount = round(min($subTotal, $manualDiscount + $voucherDiscount + $pointsDiscount), 2);
        $taxable = round(max(0, $subTotal - $totalDiscount), 2);
        $taxRateSum = 0.0;
        foreach ($this->taxes as $t) {
            $taxRateSum += (float) ($t['rate'] ?? 0);
        }
        $this->taxRate = round($taxRateSum, 2);

        $breakdown = [];
        $taxSum = 0.0;
        foreach ($this->taxes as $t) {
            $rate = (float) ($t['rate'] ?? 0);
            if ($rate <= 0) {
                continue;
            }

            $name = trim((string) ($t['name'] ?? 'Tax'));
            $amount = round($taxable * ($rate / 100), 2);
            $breakdown[] = [
                'name' => $name !== '' ? $name : 'Tax',
                'rate' => round($rate, 2),
                'amount' => $amount,
            ];
            $taxSum += $amount;
        }

        $taxSum = round($taxSum, 2);

        $this->manualDiscountAmount = $manualDiscount;
        $this->voucherDiscountAmount = $voucherDiscount;
        $this->pointsDiscountAmount = $pointsDiscount;
        $this->discountAmount = $totalDiscount;
        $this->taxBreakdown = $breakdown;
        $this->taxAmount = $taxSum;
        $this->totalAmount = round($taxable + $taxSum, 2);

        $this->updatedAmountReceived();
    }

    /**
     * Show payment screen.
     */
    public function startPayment(): void
    {
        if (empty($this->cart)) return;
        $this->showCartMobile = false;
        $this->isPaying = true;
        $this->isSplitPayment = false;
        $this->paymentSplits = [];
        $this->splitMethod = 'cash';
        $this->splitAmount = 0;
        $this->splitRemaining = 0;
        $this->amountReceived = 0.00;
        $this->changeAmount = 0.00;
    }

    public function clearAmountReceived(): void
    {
        $this->amountReceived = 0.00;
        $this->changeAmount = 0.00;
    }

    public function addQuickAmount(float $amount): void
    {
        $this->amountReceived = round((float)$this->amountReceived + $amount, 2);
        $this->calculateChange();
    }

    public function setExactAmount(): void
    {
        $this->amountReceived = round((float)$this->totalAmount, 2);
        $this->calculateChange();
    }

    public function updatedAmountReceived(): void
    {
        $value = (float) $this->amountReceived;
        if (!is_finite($value) || is_nan($value)) {
            $value = 0.0;
        }
        $this->amountReceived = round($value, 2);
        $this->calculateChange();
    }

    private function calculateChange(): void
    {
        $paid = (float) $this->amountReceived;
        if (!is_finite($paid) || is_nan($paid)) {
            $paid = 0.0;
        }

        $this->changeAmount = round(max(0, $paid - (float) $this->totalAmount), 2);
    }

    /**
     * Reset cash received.
     */
    public function resetAmountReceived(): void
    {
        $this->amountReceived = 0;
        $this->calculateChange();
    }

    // ─── Split Payment Methods ─────────────────────────────────────────────────

    public function enableSplitPayment(): void
    {
        $this->isSplitPayment = true;
        $this->paymentSplits = [];
        $this->splitMethod = 'cash';
        $this->splitAmount = round((float) $this->totalAmount, 2);
        $this->recalculateSplitRemaining();
    }

    public function disableSplitPayment(): void
    {
        $this->isSplitPayment = false;
        $this->paymentSplits = [];
        $this->splitMethod = 'cash';
        $this->splitAmount = 0;
        $this->splitRemaining = 0;
        $this->amountReceived = (float) $this->totalAmount;
        $this->calculateChange();
    }

    public function addSplit(): void
    {
        $amount = round(max(0, (float) $this->splitAmount), 2);

        if ($amount <= 0) {
            $this->dispatch('notify', message: 'Enter an amount greater than 0.', type: 'error');
            return;
        }

        $remaining = round((float) $this->splitRemaining, 2);
        if ($amount > $remaining + 0.001) {
            $amount = $remaining;
        }

        if ($amount <= 0) {
            $this->dispatch('notify', message: 'No remaining balance to allocate.', type: 'error');
            return;
        }

        $this->paymentSplits[] = [
            'method' => $this->splitMethod,
            'amount' => $amount,
        ];

        $this->recalculateSplitRemaining();
        $this->splitAmount = round(max(0, $this->splitRemaining), 2);
    }

    public function removeSplit(int $index): void
    {
        unset($this->paymentSplits[$index]);
        $this->paymentSplits = array_values($this->paymentSplits);
        $this->recalculateSplitRemaining();
        $this->splitAmount = round(max(0, $this->splitRemaining), 2);
    }

    public function setSplitExact(): void
    {
        $this->splitAmount = round(max(0, (float) $this->splitRemaining), 2);
    }

    private function recalculateSplitRemaining(): void
    {
        $allocated = collect($this->paymentSplits)->sum('amount');
        $this->splitRemaining = round(max(0, (float) $this->totalAmount - (float) $allocated), 2);
    }

    private function splitIsPaid(): bool
    {
        if (!$this->isSplitPayment) {
            return false;
        }
        return $this->splitRemaining <= 0.001;
    }

    // ─── End Split Payment Methods ─────────────────────────────────────────────

    /**
     * Place the order and process payment.
     */
    public function checkout(): void
    {
        if (empty($this->cart)) return;

        // Validate split payment before entering transaction
        if ($this->isSplitPayment) {
            if (empty($this->paymentSplits)) {
                $this->dispatch('notify', message: 'Add at least one payment split.', type: 'error');
                return;
            }
            if ($this->splitRemaining > 0.01) {
                $this->dispatch('notify', message: 'Split payments do not cover the full amount. Remaining: $' . number_format($this->splitRemaining, 2), type: 'error');
                return;
            }
        }

        $issuedCodes = [];

        $this->lastOrder = DB::transaction(function () use (&$issuedCodes) {
            $voucherCode = filled($this->appliedVoucherCode) ? strtoupper(trim($this->appliedVoucherCode)) : null;
            $points = max(0, (int) $this->appliedPoints);
            $earnedPoints = 0;
            $voucher = null;
            $customerVoucher = null;

            if ($voucherCode) {
                $customerVoucher = CustomerVoucher::query()->where('code', $voucherCode)->lockForUpdate()->first();
                if ($customerVoucher) {
                    $voucher = Voucher::query()->where('id', $customerVoucher->voucher_id)->lockForUpdate()->first();
                } else {
                    $voucher = Voucher::query()->where('code', $voucherCode)->lockForUpdate()->first();
                }

                if (
                    !$voucher
                    || !(bool) $voucher->is_active
                    || ($voucher->starts_at && now()->lt($voucher->starts_at))
                    || ($voucher->ends_at && now()->gt($voucher->ends_at))
                    || ($voucher->usage_limit !== null && (int) $voucher->usage_count >= (int) $voucher->usage_limit)
                ) {
                    $this->dispatch('notify', message: 'Voucher code is not valid.', type: 'error');
                    return null;
                }
            }

            $customer = null;
            if ($this->customerId) {
                $customer = Customer::where('id', $this->customerId)->lockForUpdate()->first();
            }

            if ($voucher && $customerVoucher) {
                if (!$customer) {
                    $this->dispatch('notify', message: 'Select a customer to use this voucher.', type: 'error');
                    return null;
                }
                if ((int) $customerVoucher->customer_id !== (int) $customer->id) {
                    $this->dispatch('notify', message: 'This voucher is not assigned to this customer.', type: 'error');
                    return null;
                }
                if ($customerVoucher->used_at !== null || $customerVoucher->used_order_id !== null) {
                    $this->dispatch('notify', message: 'This voucher has already been used.', type: 'error');
                    return null;
                }
                if ($customerVoucher->expires_at && now()->gt($customerVoucher->expires_at)) {
                    $this->dispatch('notify', message: 'This voucher has expired.', type: 'error');
                    return null;
                }
            }

            if ($voucher && ($voucher->per_customer_limit !== null || (bool) $voucher->first_time_only)) {
                if (!$customer) {
                    $this->dispatch('notify', message: 'Select a customer to use this voucher.', type: 'error');
                    return null;
                }
            }

            if ($voucher && !(bool) $voucher->can_combine_with_points && $points > 0) {
                $this->dispatch('notify', message: 'This voucher cannot be combined with points.', type: 'error');
                return null;
            }
            if ($voucher && !(bool) $voucher->can_combine_with_manual_discount && (float) $this->manualDiscountAmount > 0) {
                $this->dispatch('notify', message: 'This voucher cannot be combined with manual discount.', type: 'error');
                return null;
            }

            if ($voucher && $customer && (bool) $voucher->first_time_only) {
                $hasOrders = Order::query()
                    ->where('customer_id', $customer->id)
                    ->where('payment_status', 'paid')
                    ->exists();
                if ($hasOrders) {
                    $this->dispatch('notify', message: 'This voucher is only for first-time customers.', type: 'error');
                    return null;
                }
            }

            if ($voucher && $customer && $voucher->per_customer_limit !== null) {
                $used = Order::query()
                    ->where('customer_id', $customer->id)
                    ->where('voucher_id', $voucher->id)
                    ->where('payment_status', 'paid')
                    ->lockForUpdate()
                    ->count();
                if ($used >= (int) $voucher->per_customer_limit) {
                    $this->dispatch('notify', message: 'This voucher has already been used by this customer.', type: 'error');
                    return null;
                }
            }

            if ($points > 0) {
                if (!$customer) {
                    $this->dispatch('notify', message: 'Select a customer to redeem points.', type: 'error');
                    return null;
                }

                if ((int) $customer->points_balance < $points) {
                    $this->dispatch('notify', message: 'Customer does not have enough points.', type: 'error');
                    return null;
                }
            }

            if ($customer && $this->pointsEarnRate > 0) {
                $subTotal = round(max(0, (float) $this->subTotalAmount), 2);
                $discount = round(max(0, (float) $this->discountAmount), 2);
                $earnBase = round(max(0, $subTotal - $discount), 2);
                $multiplier = $this->currentPointsPromoMultiplier();
                $earnedPoints = (int) floor($earnBase * (float) $this->pointsEarnRate * $multiplier);
                $earnedPoints = max(0, $earnedPoints);

                $newBalance = (int) $customer->points_balance - $points + $earnedPoints;
                $customer->update(['points_balance' => max(0, $newBalance)]);
            } elseif ($customer && $points > 0) {
                $customer->update(['points_balance' => max(0, (int) $customer->points_balance - $points)]);
            }

            $order = Order::create([
                'shift_id' => $this->currentShift?->id,
                'user_id' => Auth::id(),
                'customer_id' => $this->customerId,
                'table_id' => $this->orderType === 'dine_in' ? $this->tableId : null,
                'table_number' => $this->orderType === 'dine_in' ? $this->tableNumber : null,
                'order_type' => $this->orderType,
                'notes' => $this->orderNotes,
                'status' => 'completed', // POS orders are usually completed immediately
                'total_amount' => $this->totalAmount,
                'subtotal_amount' => $this->subTotalAmount,
                'discount_type' => $this->discountType,
                'discount_value' => $this->discountValue,
                'discount_amount' => $this->discountAmount,
                'voucher_id' => $voucher?->id,
                'voucher_code' => $voucherCode,
                'points_redeemed' => $points,
                'points_earned' => $earnedPoints,
                'tax_rate' => $this->taxRate,
                'tax_amount' => $this->taxAmount,
                'payment_method' => $this->isSplitPayment
                    ? implode('+', array_unique(array_column($this->paymentSplits, 'method')))
                    : $this->paymentMethod,
                'payment_splits' => $this->isSplitPayment ? $this->paymentSplits : null,
                'payment_status' => 'paid',
                'amount_paid' => $this->isSplitPayment
                    ? round(collect($this->paymentSplits)->sum('amount'), 2)
                    : $this->amountReceived,
                'change_amount' => $this->isSplitPayment ? 0 : $this->changeAmount,
            ]);

            if ($voucherCode) {
                Voucher::where('id', $voucher->id)->lockForUpdate()->increment('usage_count');
                if ($customerVoucher) {
                    $customerVoucher->update([
                        'used_order_id' => $order->id,
                        'used_at' => now(),
                    ]);
                }
            }

            foreach ($this->cart as $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'notes' => $item['notes'],
                ]);

                if (!empty($item['addon_ids'])) {
                    foreach ($item['addon_ids'] as $addonId) {
                        $addon = ProductAddon::find($addonId);
                        $orderItem->addons()->create([
                            'addon_id' => $addon->id,
                            'name' => $addon->name,
                            'price' => $addon->price,
                        ]);
                    }
                }

                if (!empty($item['set_items'])) {
                    foreach ($item['set_items'] as $component) {
                        $orderItem->components()->create([
                            'product_id' => $component['product_id'] ?? null,
                            'group_name' => $component['group_name'] ?? null,
                            'name' => $component['name'] ?? '',
                            'quantity' => 1,
                            'extra_price' => $component['extra_price'] ?? 0,
                        ]);
                    }
                }
            }

            if ($customer) {
                $issuable = Voucher::query()
                    ->whereNotNull('issue_on_min_spend')
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                    })
                    ->get();

                foreach ($issuable as $v) {
                    $minSpend = (float) ($v->issue_on_min_spend ?? 0);
                    if ($minSpend <= 0) {
                        continue;
                    }
                    if ((float) $order->total_amount < $minSpend) {
                        continue;
                    }

                    $expiresAt = null;
                    if ($v->issue_expires_in_days !== null) {
                        $expiresAt = now()->addDays((int) $v->issue_expires_in_days);
                    }
                    if ($v->ends_at && (!$expiresAt || $v->ends_at->lt($expiresAt))) {
                        $expiresAt = $v->ends_at;
                    }

                    $prefix = strtoupper(trim((string) ($v->code ?? 'VOUCHER')));
                    $generated = null;
                    for ($i = 0; $i < 5; $i++) {
                        $candidate = $prefix . '-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
                        if (!CustomerVoucher::where('code', $candidate)->exists()) {
                            $generated = $candidate;
                            break;
                        }
                    }
                    if (!$generated) {
                        continue;
                    }

                    CustomerVoucher::create([
                        'voucher_id' => $v->id,
                        'customer_id' => $customer->id,
                        'code' => $generated,
                        'issued_from_order_id' => $order->id,
                        'issued_at' => now(),
                        'expires_at' => $expiresAt,
                    ]);

                    $issuedCodes[] = [
                        'code' => $generated,
                        'expires_at' => $expiresAt?->toDateString(),
                    ];
                }
            }

            return $order;
        });

        if (!$this->lastOrder) {
            return;
        }

        // Update table status to dirty after order completion
        if ($this->tableId && $this->orderType === 'dine_in') {
            $table = RestaurantTable::find($this->tableId);
            if ($table) {
                $table->markDirty();
            }
        }

        // Update shift sales totals
        if ($shift = $this->currentShift) {
            $shift->recalculateSales();
            unset($this->currentShift);
        }

        $this->issuedVoucherCodes = $issuedCodes;

        $this->reset(['cart', 'subTotalAmount', 'totalAmount', 'discountType', 'discountValue', 'discountAmount', 'manualDiscountAmount', 'voucherCode', 'appliedVoucherCode', 'appliedVoucherId', 'appliedVoucherMeta', 'voucherDiscountType', 'voucherDiscountValue', 'voucherDiscountAmount', 'pointsToRedeem', 'appliedPoints', 'pointsDiscountAmount', 'customerId', 'customerSearch', 'newCustomerName', 'newCustomerEmail', 'newCustomerMobile', 'showDiscountModal', 'discountTab', 'taxBreakdown', 'taxAmount', 'tableNumber', 'tableId', 'orderNotes', 'isPaying', 'showCartMobile']);
        $this->reset(['amountReceived', 'changeAmount', 'paymentMethod', 'isSplitPayment', 'paymentSplits', 'splitMethod', 'splitAmount', 'splitRemaining']);
        // We keep lastOrder to show receipt/success screen if needed
        $this->dispatch('order-placed');
    }

    /**
     * Place order and send to kitchen without payment (Pay Later).
     * Customer eats first, pays when requesting the bill.
     * Also handles adding items to an existing order.
     */
    public function placeOrderPayLater(): void
    {
        // Filter out existing items - only process new items
        $newItems = collect($this->cart)->filter(fn($item) => empty($item['existing']))->values()->all();
        
        if (empty($newItems)) {
            $this->dispatch('notify', message: 'No new items to add.', type: 'error');
            return;
        }

        // For dine-in pay-later orders, a table must be selected
        if ($this->orderType === 'dine_in' && !$this->tableId) {
            $this->dispatch('notify', message: 'Please select a table for dine-in orders.', type: 'error');
            return;
        }

        $this->lastOrder = DB::transaction(function () use ($newItems) {
            // Check if we're adding to an existing order
            if ($this->existingOrder) {
                return $this->addItemsToExistingOrder($newItems);
            }
            
            $order = Order::create([
                'shift_id' => $this->currentShift?->id,
                'user_id' => Auth::id(),
                'customer_id' => $this->customerId,
                'table_id' => $this->orderType === 'dine_in' ? $this->tableId : null,
                'table_number' => $this->orderType === 'dine_in' ? $this->tableNumber : null,
                'order_type' => $this->orderType,
                'notes' => $this->orderNotes,
                'status' => 'pending', // Order is pending until paid
                'kds_status' => 'pending', // Send to kitchen
                'total_amount' => $this->totalAmount,
                'subtotal_amount' => $this->subTotalAmount,
                'discount_type' => $this->discountType,
                'discount_value' => $this->discountValue,
                'discount_amount' => $this->discountAmount,
                'voucher_id' => null, // Don't apply voucher until payment
                'voucher_code' => null,
                'points_redeemed' => 0, // Don't redeem points until payment
                'points_earned' => 0,
                'tax_rate' => $this->taxRate,
                'tax_amount' => $this->taxAmount,
                'payment_method' => null,
                'payment_splits' => null,
                'payment_status' => 'unpaid', // Not paid yet
                'amount_paid' => 0,
                'change_amount' => 0,
            ]);

            foreach ($newItems as $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'notes' => $item['notes'],
                ]);

                if (!empty($item['addon_ids'])) {
                    foreach ($item['addon_ids'] as $addonId) {
                        $addon = ProductAddon::find($addonId);
                        $orderItem->addons()->create([
                            'addon_id' => $addon->id,
                            'name' => $addon->name,
                            'price' => $addon->price,
                        ]);
                    }
                }

                if (!empty($item['set_items'])) {
                    foreach ($item['set_items'] as $component) {
                        $orderItem->components()->create([
                            'product_id' => $component['product_id'] ?? null,
                            'group_name' => $component['group_name'] ?? null,
                            'name' => $component['name'] ?? '',
                            'quantity' => 1,
                            'extra_price' => $component['extra_price'] ?? 0,
                        ]);
                    }
                }
            }

            // Update table to track the current order
            if ($this->tableId && $this->orderType === 'dine_in') {
                $table = RestaurantTable::find($this->tableId);
                if ($table) {
                    $table->update(['current_order_id' => $order->id]);
                    if ($table->status === 'available' || $table->status === 'reserved') {
                        $table->occupy();
                    }
                }
            }

            return $order;
        });

        if (!$this->lastOrder) {
            return;
        }

        $this->isPayLater = true;
        
        $message = $this->existingOrder 
            ? 'Added items to Order #' . $this->lastOrder->id . '. Sent to kitchen.'
            : 'Order #' . $this->lastOrder->id . ' sent to kitchen. Payment pending.';
        
        $this->dispatch('notify', message: $message, type: 'success');

        $this->reset(['cart', 'subTotalAmount', 'totalAmount', 'discountType', 'discountValue', 'discountAmount', 'manualDiscountAmount', 'voucherCode', 'appliedVoucherCode', 'appliedVoucherId', 'appliedVoucherMeta', 'voucherDiscountType', 'voucherDiscountValue', 'voucherDiscountAmount', 'pointsToRedeem', 'appliedPoints', 'pointsDiscountAmount', 'customerId', 'customerSearch', 'newCustomerName', 'newCustomerEmail', 'newCustomerMobile', 'showDiscountModal', 'discountTab', 'taxBreakdown', 'taxAmount', 'orderNotes', 'isPaying', 'showCartMobile', 'existingOrder']);
        $this->reset(['amountReceived', 'changeAmount', 'paymentMethod', 'isSplitPayment', 'paymentSplits', 'splitMethod', 'splitAmount', 'splitRemaining']);
        // Keep tableId and tableNumber for potential follow-up orders at the same table
        $this->dispatch('order-placed');
    }
    
    /**
     * Add items to an existing order (when customer orders more after initial order sent to kitchen).
     */
    protected function addItemsToExistingOrder(array $newItems): Order
    {
        $order = $this->existingOrder;
        
        // Calculate new totals including existing items
        $existingSubtotal = (float) $order->subtotal_amount;
        $newSubtotal = collect($newItems)->sum('subtotal');
        $combinedSubtotal = $existingSubtotal + $newSubtotal;
        
        // Recalculate tax on the combined total
        $combinedTaxAmount = $combinedSubtotal * ($this->taxRate / 100);
        $combinedTotal = $combinedSubtotal + $combinedTaxAmount - (float) $order->discount_amount;
        
        // Add new items to the existing order
        foreach ($newItems as $item) {
            $orderItem = $order->items()->create([
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
                'notes' => $item['notes'],
            ]);

            if (!empty($item['addon_ids'])) {
                foreach ($item['addon_ids'] as $addonId) {
                    $addon = ProductAddon::find($addonId);
                    $orderItem->addons()->create([
                        'addon_id' => $addon->id,
                        'name' => $addon->name,
                        'price' => $addon->price,
                    ]);
                }
            }

            if (!empty($item['set_items'])) {
                foreach ($item['set_items'] as $component) {
                    $orderItem->components()->create([
                        'product_id' => $component['product_id'] ?? null,
                        'group_name' => $component['group_name'] ?? null,
                        'name' => $component['name'] ?? '',
                        'quantity' => 1,
                        'extra_price' => $component['extra_price'] ?? 0,
                    ]);
                }
            }
        }
        
        // Update order totals
        $order->update([
            'subtotal_amount' => $combinedSubtotal,
            'tax_amount' => $combinedTaxAmount,
            'total_amount' => $combinedTotal,
            'kds_status' => 'pending', // Reset KDS status so kitchen sees new items
        ]);
        
        return $order;
    }

    /**
     * Reset POS for new order.
     */
    public function newOrder(): void
    {
        $this->issuedVoucherCodes = [];
        $this->isPayLater = false;
        $this->showBillPreview = false;
        $this->selectedUnpaidOrder = null;
        $this->showUnpaidOrdersModal = false;
        $this->reset(['cart', 'subTotalAmount', 'totalAmount', 'discountType', 'discountValue', 'discountAmount', 'manualDiscountAmount', 'voucherCode', 'appliedVoucherCode', 'appliedVoucherId', 'appliedVoucherMeta', 'voucherDiscountType', 'voucherDiscountValue', 'voucherDiscountAmount', 'pointsToRedeem', 'appliedPoints', 'pointsDiscountAmount', 'customerId', 'customerSearch', 'newCustomerName', 'newCustomerEmail', 'newCustomerMobile', 'showDiscountModal', 'discountTab', 'taxBreakdown', 'taxAmount', 'tableNumber', 'tableId', 'orderType', 'orderNotes', 'isPaying', 'lastOrder', 'amountReceived', 'changeAmount', 'paymentMethod', 'isSplitPayment', 'paymentSplits', 'splitMethod', 'splitAmount', 'splitRemaining', 'showCartMobile']);
    }

    /**
     * Open modal to show unpaid orders.
     */
    public function openUnpaidOrdersModal(): void
    {
        unset($this->unpaidOrders);
        $this->showUnpaidOrdersModal = true;
    }

    /**
     * Select an unpaid order for payment collection.
     */
    public function selectUnpaidOrder(int $orderId): void
    {
        $order = Order::with(['items.product', 'items.variant', 'items.addons', 'table'])
            ->where('payment_status', 'unpaid')
            ->find($orderId);

        if (!$order) {
            $this->dispatch('notify', message: 'Order not found or already paid.', type: 'error');
            return;
        }

        $this->selectedUnpaidOrder = $order;
        $this->totalAmount = (float) $order->total_amount;
        $this->amountReceived = 0;
        $this->changeAmount = 0;
        $this->paymentMethod = 'cash';
        $this->isSplitPayment = false;
        $this->paymentSplits = [];
        $this->showUnpaidOrdersModal = false;
        $this->isPaying = true;
    }

    /**
     * Collect payment for an existing unpaid order.
     */
    public function collectPayment(): void
    {
        if (!$this->selectedUnpaidOrder) {
            $this->dispatch('notify', message: 'No order selected.', type: 'error');
            return;
        }

        // Validate split payment before entering transaction
        if ($this->isSplitPayment) {
            if (empty($this->paymentSplits)) {
                $this->dispatch('notify', message: 'Add at least one payment split.', type: 'error');
                return;
            }
            if ($this->splitRemaining > 0.01) {
                $this->dispatch('notify', message: 'Split payments do not cover the full amount. Remaining: RM ' . number_format($this->splitRemaining, 2), type: 'error');
                return;
            }
        }

        $order = $this->selectedUnpaidOrder;

        DB::transaction(function () use ($order) {
            $order->update([
                'payment_method' => $this->isSplitPayment
                    ? implode('+', array_unique(array_column($this->paymentSplits, 'method')))
                    : $this->paymentMethod,
                'payment_splits' => $this->isSplitPayment ? $this->paymentSplits : null,
                'payment_status' => 'paid',
                'status' => 'completed',
                'amount_paid' => $this->isSplitPayment
                    ? round(collect($this->paymentSplits)->sum('amount'), 2)
                    : $this->amountReceived,
                'change_amount' => $this->isSplitPayment ? 0 : $this->changeAmount,
            ]);

            // Update table status to dirty after payment
            if ($order->table_id) {
                $table = RestaurantTable::find($order->table_id);
                if ($table) {
                    $table->markDirty();
                    $table->update(['current_order_id' => null]);
                }
            }

            // Update shift sales totals
            if ($shift = $this->currentShift) {
                $shift->recalculateSales();
                unset($this->currentShift);
            }
        });

        $this->lastOrder = $order->fresh();
        $this->isPayLater = false;
        $this->selectedUnpaidOrder = null;
        $this->isPaying = false;

        $this->dispatch('notify', message: 'Payment collected for Order #' . $order->id, type: 'success');
        $this->reset(['amountReceived', 'changeAmount', 'paymentMethod', 'isSplitPayment', 'paymentSplits', 'splitMethod', 'splitAmount', 'splitRemaining']);
    }

    /**
     * Cancel collecting payment for an unpaid order.
     */
    public function cancelCollectPayment(): void
    {
        $this->selectedUnpaidOrder = null;
        $this->isPaying = false;
        $this->reset(['amountReceived', 'changeAmount', 'paymentMethod', 'isSplitPayment', 'paymentSplits', 'splitMethod', 'splitAmount', 'splitRemaining']);
    }

    /**
     * Select a table for the current order.
     */
    public function selectTable(?int $tableId): void
    {
        if (!$tableId) {
            $this->tableId = null;
            $this->tableNumber = '';
            return;
        }

        $table = RestaurantTable::find($tableId);
        if (!$table || !$table->is_active) {
            $this->dispatch('notify', message: 'Table not found or inactive.', type: 'error');
            return;
        }

        $this->tableId = $table->id;
        $this->tableNumber = $table->name;
        $this->orderType = 'dine_in';

        // Mark table as occupied if it was available
        if ($table->status === 'available' || $table->status === 'reserved') {
            $table->occupy();
        }
    }

    private function clearAppliedVoucher(): void
    {
        $code = filled($this->appliedVoucherCode) ? (string) $this->appliedVoucherCode : null;
        if ($code) {
            $this->cart = array_values(array_filter($this->cart, function ($row) use ($code) {
                if (!is_array($row)) {
                    return true;
                }
                if (!(bool) ($row['is_voucher_reward'] ?? false)) {
                    return true;
                }
                return strtoupper((string) ($row['voucher_code'] ?? '')) !== strtoupper($code);
            }));
        }

        $this->appliedVoucherCode = '';
        $this->appliedVoucherId = null;
        $this->appliedVoucherMeta = [];
        $this->voucherDiscountType = 'percent';
        $this->voucherDiscountValue = 0;
        $this->voucherDiscountAmount = 0;
    }

    private function addVoucherRewardToCart(int $productId, int $quantity, string $voucherCode, int $voucherId): void
    {
        $product = Product::find($productId);
        if (!$product || !(bool) $product->is_active) {
            $this->dispatch('notify', message: 'Voucher reward product is not available.', type: 'error');
            return;
        }

        $quantity = max(1, $quantity);

        foreach ($this->cart as $index => $cartItem) {
            if (
                (int) ($cartItem['product_id'] ?? 0) === (int) $product->id
                && (bool) ($cartItem['is_voucher_reward'] ?? false)
                && strtoupper((string) ($cartItem['voucher_code'] ?? '')) === strtoupper($voucherCode)
            ) {
                $this->cart[$index]['quantity'] = $quantity;
                $this->cart[$index]['unit_price'] = 0;
                $this->cart[$index]['addons_total'] = 0;
                $this->cart[$index]['subtotal'] = 0;
                $this->calculateTotal();
                return;
            }
        }

        $this->cart[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'variant_id' => null,
            'variant_name' => null,
            'addon_ids' => [],
            'addon_names' => [],
            'set_items' => [],
            'set_total' => 0,
            'set_summary' => '',
            'quantity' => $quantity,
            'unit_price' => 0,
            'addons_total' => 0,
            'subtotal' => 0,
            'notes' => '',
            'is_voucher_reward' => true,
            'voucher_code' => strtoupper($voucherCode),
            'voucher_id' => $voucherId,
        ];

        $this->calculateTotal();
    }

    private function resolveVoucherForCode(string $code): array
    {
        $code = strtoupper(trim($code));
        if (!filled($code)) {
            return ['voucher' => null, 'customerVoucher' => null, 'error' => 'Voucher code is not valid.'];
        }

        $customerVoucher = CustomerVoucher::query()->where('code', $code)->first();
        $voucher = null;

        if ($customerVoucher) {
            if ($customerVoucher->used_at !== null || $customerVoucher->used_order_id !== null) {
                return ['voucher' => null, 'customerVoucher' => null, 'error' => 'Voucher has already been used.'];
            }
            if ($customerVoucher->expires_at && now()->gt($customerVoucher->expires_at)) {
                return ['voucher' => null, 'customerVoucher' => null, 'error' => 'Voucher has expired.'];
            }
            $voucher = Voucher::where('id', $customerVoucher->voucher_id)->first();
        } else {
            $voucher = Voucher::where('code', $code)->first();
        }

        if (
            !$voucher
            || !(bool) $voucher->is_active
            || ($voucher->starts_at && now()->lt($voucher->starts_at))
            || ($voucher->ends_at && now()->gt($voucher->ends_at))
            || ($voucher->usage_limit !== null && (int) $voucher->usage_count >= (int) $voucher->usage_limit)
        ) {
            return ['voucher' => null, 'customerVoucher' => null, 'error' => 'Voucher code is not valid.'];
        }

        return ['voucher' => $voucher, 'customerVoucher' => $customerVoucher];
    }

    public function openDiscountModal(): void
    {
        $this->showDiscountModal = true;
    }

    public function closeDiscountModal(): void
    {
        $this->showDiscountModal = false;
    }

    public function setDiscountTab(string $tab): void
    {
        $allowed = ['discount', 'voucher', 'points', 'customer'];
        $this->discountTab = in_array($tab, $allowed, true) ? $tab : 'discount';
    }

    public function clearPromotion(): void
    {
        $this->discountType = 'percent';
        $this->discountValue = 0;
        $this->discountInputType = 'percent';
        $this->discountInputValue = 0;
        $this->voucherCode = '';
        $this->clearAppliedVoucher();
        $this->pointsToRedeem = 0;
        $this->appliedPoints = 0;
        $this->recalculateTotals();
    }

    public function applyManualDiscount(): void
    {
        $type = $this->discountInputType === 'fixed' ? 'fixed' : 'percent';
        $value = round(max(0, (float) $this->discountInputValue), 2);

        $subTotal = round(max(0, (float) $this->subTotalAmount), 2);
        $manualDiscount = 0.0;
        if ($type === 'fixed') {
            $manualDiscount = round(min($subTotal, $value), 2);
        } else {
            $rate = min(100, $value);
            $manualDiscount = round($subTotal * ($rate / 100), 2);
        }

        if ($this->appliedVoucherId && !(bool) ($this->appliedVoucherMeta['can_combine_with_manual_discount'] ?? false) && $manualDiscount > 0) {
            $this->dispatch('notify', message: 'This voucher cannot be combined with manual discount.', type: 'error');
            return;
        }

        $this->discountType = $type;
        $this->discountValue = $value;
        $this->recalculateTotals();
        $this->closeDiscountModal();
    }

    public function applyVoucher(): void
    {
        $code = strtoupper(trim((string) $this->voucherCode));
        if (!filled($code)) {
            $this->dispatch('notify', message: 'Enter voucher code.', type: 'error');
            return;
        }

        $resolved = $this->resolveVoucherForCode($code);
        $voucher = $resolved['voucher'] ?? null;
        $customerVoucher = $resolved['customerVoucher'] ?? null;
        if (!$voucher) {
            $this->dispatch('notify', message: (string) ($resolved['error'] ?? 'Voucher code is not valid.'), type: 'error');
            return;
        }

        $requiresCustomer = $customerVoucher !== null
            || $voucher->per_customer_limit !== null
            || (bool) $voucher->first_time_only;

        if ($requiresCustomer && !$this->customerId) {
            $this->dispatch('notify', message: 'Select a customer to use this voucher.', type: 'error');
            return;
        }

        if ($customerVoucher && $this->customerId && (int) $customerVoucher->customer_id !== (int) $this->customerId) {
            $this->dispatch('notify', message: 'This voucher is not assigned to this customer.', type: 'error');
            return;
        }

        if (!(bool) $voucher->can_combine_with_points && (int) $this->appliedPoints > 0) {
            $this->dispatch('notify', message: 'This voucher cannot be combined with points.', type: 'error');
            return;
        }

        if (!(bool) $voucher->can_combine_with_manual_discount && (float) $this->manualDiscountAmount > 0) {
            $this->dispatch('notify', message: 'This voucher cannot be combined with manual discount.', type: 'error');
            return;
        }

        if ($this->customerId && (bool) $voucher->first_time_only) {
            $hasOrders = Order::query()
                ->where('customer_id', $this->customerId)
                ->where('payment_status', 'paid')
                ->exists();
            if ($hasOrders) {
                $this->dispatch('notify', message: 'This voucher is only for first-time customers.', type: 'error');
                return;
            }
        }

        if ($this->customerId && $voucher->per_customer_limit !== null) {
            $used = Order::query()
                ->where('customer_id', $this->customerId)
                ->where('voucher_id', $voucher->id)
                ->where('payment_status', 'paid')
                ->count();
            if ($used >= (int) $voucher->per_customer_limit) {
                $this->dispatch('notify', message: 'This voucher has already been used by this customer.', type: 'error');
                return;
            }
        }

        $this->clearAppliedVoucher();
        $this->appliedVoucherCode = $code;
        $this->appliedVoucherId = (int) $voucher->id;
        $this->appliedVoucherMeta = [
            'can_combine_with_points' => (bool) $voucher->can_combine_with_points,
            'can_combine_with_manual_discount' => (bool) $voucher->can_combine_with_manual_discount,
            'requires_customer' => $requiresCustomer,
            'free_product_id' => $voucher->free_product_id ? (int) $voucher->free_product_id : null,
            'free_quantity' => max(1, (int) ($voucher->free_quantity ?? 1)),
            'is_customer_specific' => $customerVoucher !== null,
        ];

        if ($voucher->free_product_id) {
            $this->voucherDiscountType = 'percent';
            $this->voucherDiscountValue = 0;
            $this->addVoucherRewardToCart(
                (int) $voucher->free_product_id,
                max(1, (int) ($voucher->free_quantity ?? 1)),
                $code,
                (int) $voucher->id
            );
        } else {
            $this->voucherDiscountType = $voucher->type === 'fixed' ? 'fixed' : 'percent';
            $this->voucherDiscountValue = $voucher->type === 'fixed'
                ? round(max(0, (float) $voucher->value), 2)
                : round(min(100, max(0, (float) $voucher->value)), 2);
        }

        $this->voucherCode = '';
        $this->recalculateTotals();
        $this->closeDiscountModal();
    }

    public function applyPoints(): void
    {
        $points = max(0, (int) $this->pointsToRedeem);
        if ($points <= 0) {
            $this->dispatch('notify', message: 'Enter points to redeem.', type: 'error');
            return;
        }
        if ($this->pointsMinRedeem > 0 && $points < $this->pointsMinRedeem) {
            $this->dispatch('notify', message: 'Minimum redemption is ' . (int) $this->pointsMinRedeem . ' points.', type: 'error');
            return;
        }
        if (!$this->customerId) {
            $this->dispatch('notify', message: 'Select a customer first.', type: 'error');
            return;
        }
        if ($this->appliedVoucherId && !(bool) ($this->appliedVoucherMeta['can_combine_with_points'] ?? false)) {
            $this->dispatch('notify', message: 'This voucher cannot be combined with points.', type: 'error');
            return;
        }

        $customer = Customer::find($this->customerId);
        if (!$customer || (int) $customer->points_balance < $points) {
            $this->dispatch('notify', message: 'Not enough points.', type: 'error');
            return;
        }

        $valuePerPoint = (float) $this->pointsRedeemAmount / max(1, (int) $this->pointsRedeemPoints);
        $amount = round($points * max(0, $valuePerPoint), 2);
        if ($amount <= 0) {
            $this->dispatch('notify', message: 'Points redemption value is not configured.', type: 'error');
            return;
        }

        $this->appliedPoints = $points;
        $this->recalculateTotals();
        $this->closeDiscountModal();
    }

    public function selectCustomer(int $customerId): void
    {
        $this->customerId = $customerId;
        $this->customerSearch = '';
        $this->pointsToRedeem = 0;
        $this->clearValidation();
    }

    public function clearCustomer(): void
    {
        $this->customerId = null;
        $this->customerSearch = '';
        $this->pointsToRedeem = 0;
        $this->appliedPoints = 0;
        if ((bool) ($this->appliedVoucherMeta['requires_customer'] ?? false)) {
            $this->clearAppliedVoucher();
        }
        $this->recalculateTotals();
    }

    public function registerCustomer(): void
    {
        $name = trim($this->newCustomerName);
        $email = filled($this->newCustomerEmail) ? strtolower(trim($this->newCustomerEmail)) : null;
        $mobile = filled($this->newCustomerMobile) ? $this->normalizeMobile($this->newCustomerMobile) : null;

        if (!filled($name)) {
            $this->addError('newCustomerName', 'Name is required.');
            return;
        }
        if (!filled($email) && !filled($mobile)) {
            $this->addError('newCustomerEmail', 'Email or mobile is required.');
            $this->addError('newCustomerMobile', 'Email or mobile is required.');
            return;
        }

        if ($email && Customer::where('email', $email)->exists()) {
            $this->addError('newCustomerEmail', 'Email already exists.');
            return;
        }
        if ($mobile && Customer::where('mobile', $mobile)->exists()) {
            $this->addError('newCustomerMobile', 'Mobile already exists.');
            return;
        }

        $customer = Customer::create([
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'points_balance' => 0,
        ]);

        $this->customerId = $customer->id;
        $this->customerSearch = '';
        $this->newCustomerName = '';
        $this->newCustomerEmail = '';
        $this->newCustomerMobile = '';
        $this->clearValidation();
        $this->dispatch('notify', message: 'Customer registered.', type: 'success');
        $this->setDiscountTab('points');
    }

    private function normalizeMobile(string $mobile): string
    {
        $mobile = trim($mobile);
        $mobile = str_replace([' ', '-', '(', ')'], '', $mobile);
        return $mobile;
    }

    public function render()
    {
        return view('livewire.pos');
    }
}
