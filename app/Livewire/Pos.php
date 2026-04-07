<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ProductAddon;
use App\Models\Customer;
use App\Models\Voucher;
use Livewire\Attributes\Title;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
    public string $orderType = 'dine_in'; // dine_in, takeaway
    public string $orderNotes = '';

    // Payment state
    public bool $isPaying = false;
    public string $paymentMethod = 'cash';
    public float $amountReceived = 0;
    public float $changeAmount = 0;
    public ?Order $lastOrder = null;
    public bool $isKitchenBusy = false;
    public bool $showCartMobile = false;
    public bool $showDiscountModal = false;
    public string $discountTab = 'discount';
    public ?int $customerId = null;
    public string $customerSearch = '';
    public string $newCustomerName = '';
    public string $newCustomerEmail = '';
    public string $newCustomerMobile = '';
    public string $voucherCode = '';
    public string $appliedVoucherCode = '';
    public int $pointsToRedeem = 0;
    public int $appliedPoints = 0;
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

    public function mount(): void
    {
        $tenant = Auth::user()->tenant;
        $this->isKitchenBusy = (bool) $tenant->is_busy;
        $this->loadTaxes();
        $this->loadLoyaltySettings();
        $this->recalculateTotals();
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
        $this->voucherCode = '';
        $this->appliedVoucherCode = '';
        $this->pointsToRedeem = 0;
        $this->appliedPoints = 0;
        $this->customerId = null;
        $this->taxBreakdown = [];
        $this->taxAmount = 0;
        $this->totalAmount = 0;
        $this->orderNotes = '';
        $this->isPaying = false;
        $this->amountReceived = 0;
        $this->changeAmount = 0;

        $this->recalculateTotals();
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

        $discount = 0.0;
        if ($this->discountType === 'fixed') {
            $discount = round(min($subTotal, max(0, (float) $this->discountValue)), 2);
        } else {
            $rate = min(100, max(0, (float) $this->discountValue));
            $discount = round($subTotal * ($rate / 100), 2);
        }

        $taxable = round(max(0, $subTotal - $discount), 2);
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

        $this->discountAmount = $discount;
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
        $this->amountReceived = (float) $this->totalAmount;
        $this->calculateChange();
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

    /**
     * Place the order and process payment.
     */
    public function checkout(): void
    {
        if (empty($this->cart)) return;

        $this->lastOrder = DB::transaction(function () {
            $voucherCode = filled($this->appliedVoucherCode) ? strtoupper(trim($this->appliedVoucherCode)) : null;
            $points = max(0, (int) $this->appliedPoints);

            if ($voucherCode) {
                $voucher = Voucher::where('code', $voucherCode)->lockForUpdate()->first();
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
                'user_id' => Auth::id(),
                'customer_id' => $this->customerId,
                'table_number' => $this->orderType === 'dine_in' ? $this->tableNumber : null,
                'order_type' => $this->orderType,
                'notes' => $this->orderNotes,
                'status' => 'completed', // POS orders are usually completed immediately
                'total_amount' => $this->totalAmount,
                'subtotal_amount' => $this->subTotalAmount,
                'discount_type' => $this->discountType,
                'discount_value' => $this->discountValue,
                'discount_amount' => $this->discountAmount,
                'voucher_code' => $voucherCode,
                'points_redeemed' => $points,
                'tax_rate' => $this->taxRate,
                'tax_amount' => $this->taxAmount,
                'payment_method' => $this->paymentMethod,
                'payment_status' => 'paid',
                'amount_paid' => $this->amountReceived,
                'change_amount' => $this->changeAmount,
            ]);

            if ($voucherCode) {
                Voucher::where('code', $voucherCode)->lockForUpdate()->increment('usage_count');
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

            return $order;
        });

        if (!$this->lastOrder) {
            return;
        }

        $this->reset(['cart', 'subTotalAmount', 'totalAmount', 'discountType', 'discountValue', 'discountAmount', 'voucherCode', 'appliedVoucherCode', 'pointsToRedeem', 'appliedPoints', 'customerId', 'customerSearch', 'newCustomerName', 'newCustomerEmail', 'newCustomerMobile', 'showDiscountModal', 'discountTab', 'taxBreakdown', 'taxAmount', 'tableNumber', 'orderNotes', 'isPaying', 'showCartMobile']);
        $this->reset(['amountReceived', 'changeAmount', 'paymentMethod']);
        // We keep lastOrder to show receipt/success screen if needed
        $this->dispatch('order-placed');
    }

    /**
     * Reset POS for new order.
     */
    public function newOrder(): void
    {
        $this->reset(['cart', 'subTotalAmount', 'totalAmount', 'discountType', 'discountValue', 'discountAmount', 'voucherCode', 'appliedVoucherCode', 'pointsToRedeem', 'appliedPoints', 'customerId', 'customerSearch', 'newCustomerName', 'newCustomerEmail', 'newCustomerMobile', 'showDiscountModal', 'discountTab', 'taxBreakdown', 'taxAmount', 'tableNumber', 'orderType', 'orderNotes', 'isPaying', 'lastOrder', 'amountReceived', 'changeAmount', 'paymentMethod', 'showCartMobile']);
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
        $this->discountAmount = 0;
        $this->voucherCode = '';
        $this->appliedVoucherCode = '';
        $this->pointsToRedeem = 0;
        $this->appliedPoints = 0;
        $this->recalculateTotals();
    }

    public function applyManualDiscount(): void
    {
        $this->appliedVoucherCode = '';
        $this->voucherCode = '';
        $this->appliedPoints = 0;
        $this->pointsToRedeem = 0;
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

        $voucher = Voucher::where('code', $code)->first();
        if (
            !$voucher
            || !(bool) $voucher->is_active
            || ($voucher->starts_at && now()->lt($voucher->starts_at))
            || ($voucher->ends_at && now()->gt($voucher->ends_at))
            || ($voucher->usage_limit !== null && (int) $voucher->usage_count >= (int) $voucher->usage_limit)
        ) {
            $this->dispatch('notify', message: 'Voucher code is not valid.', type: 'error');
            return;
        }

        if ($voucher->type === 'fixed') {
            $this->discountType = 'fixed';
            $this->discountValue = round(max(0, (float) $voucher->value), 2);
        } else {
            $this->discountType = 'percent';
            $this->discountValue = round(min(100, max(0, (float) $voucher->value)), 2);
        }

        $this->appliedVoucherCode = $code;
        $this->appliedPoints = 0;
        $this->pointsToRedeem = 0;
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

        $customer = Customer::find($this->customerId);
        if (!$customer || (int) $customer->points_balance < $points) {
            $this->dispatch('notify', message: 'Not enough points.', type: 'error');
            return;
        }

        $this->discountType = 'fixed';
        $valuePerPoint = (float) $this->pointsRedeemAmount / max(1, (int) $this->pointsRedeemPoints);
        $amount = round($points * max(0, $valuePerPoint), 2);
        if ($amount <= 0) {
            $this->dispatch('notify', message: 'Points redemption value is not configured.', type: 'error');
            return;
        }

        $this->discountValue = $amount;
        $this->appliedPoints = $points;
        $this->appliedVoucherCode = '';
        $this->voucherCode = '';
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
        if ($this->discountType === 'fixed' && $this->discountValue > 0 && $this->appliedVoucherCode === '') {
            $this->clearPromotion();
        }
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
