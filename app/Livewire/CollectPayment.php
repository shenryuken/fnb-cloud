<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\CustomerVoucher;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\Tenant;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CollectPayment extends Component
{
    public bool $showModal = false;
    public ?Order $order = null;
    public array $orders = [];
    public bool $isMultiOrder = false;
    public string $currency = 'RM';

    // Single payment
    public float $amountReceived = 0;
    public float $changeAmount = 0;
    public string $paymentMethod = 'cash';
    public string $orderNotes = '';
    public float $subTotalAmount = 0;
    public float $taxRate = 0;
    public float $taxAmount = 0;
    public array $taxBreakdown = [];
    public float $effectiveTotal = 0;

    // Split payment
    public bool $isSplitPayment = false;
    public array $paymentSplits = [];
    public string $splitMethod = 'cash';
    public float $splitAmount = 0;
    public float $splitRemaining = 0;

    // Discount and loyalty points
    public bool $showDiscountModal = false;
    public string $discountTab = 'discount';
    public string $discountType = 'percent';
    public float $discountValue = 0;
    public float $discountAmount = 0;
    public float $manualDiscountAmount = 0;
    public string $discountInputType = 'percent';
    public float $discountInputValue = 0;
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
    public ?int $customerId = null;
    public string $customerSearch = '';
    public string $newCustomerName = '';
    public string $newCustomerEmail = '';
    public string $newCustomerMobile = '';

    // Listen for events
    protected $listeners = [
        'open-collect-payment' => 'open',
        'open-collect-payment-multiple' => 'openMultiple',
    ];

    public function open(int $orderId): void
    {
        $order = Order::with(['items.product', 'customer'])
            ->where('payment_status', 'unpaid')
            ->find($orderId);

        if (!$order) {
            $this->dispatch('notify', message: 'Order not found or already paid.', type: 'error');
            return;
        }

        // Load tenant settings for loyalty points and currency
        $tenantId = app('tenant_id');
        $tenant = Tenant::find($tenantId);
        $this->currency = $tenant->currency_symbol ?? 'RM';
        $this->pointsEarnRate = (float) ($tenant->points_earn_rate ?? 1);
        $this->pointsRedeemPoints = (int) ($tenant->points_redeem_points ?? 100);
        $this->pointsRedeemAmount = (float) ($tenant->points_redeem_amount ?? 1);
        $this->pointsMinRedeem = (int) ($tenant->points_min_redeem ?? 0);
        $this->pointsPromoIsEnabled = (bool) ($tenant->points_promo_is_enabled ?? false);
        $this->pointsPromoMultiplier = (float) ($tenant->points_promo_multiplier ?? 1);
        $this->pointsPromoStartsAt = $tenant->points_promo_starts_at;
        $this->pointsPromoEndsAt = $tenant->points_promo_ends_at;

        $this->order = $order;
        $this->orders = [];
        $this->isMultiOrder = false;
        $this->subTotalAmount = (float) $order->subtotal_amount;
        $this->taxRate = (float) $order->tax_rate;
        $this->taxAmount = (float) $order->tax_amount;
        $this->discountType = $order->discount_type ?? 'percent';
        $this->discountValue = (float) $order->discount_value;
        $this->manualDiscountAmount = (float) $order->discount_amount;
        $this->discountAmount = (float) $order->discount_amount;
        if ($order->voucher_code) {
            $this->appliedVoucherCode = $order->voucher_code;
            $this->appliedVoucherId = $order->voucher_id;
            $this->voucherDiscountType = $order->voucher?->discount_type ?? 'percent';
            $this->voucherDiscountValue = (float) ($order->voucher?->discount_value ?? 0);
        }
        $this->appliedPoints = (int) $order->points_redeemed;
        $this->customerId = $order->customer_id;
        $this->effectiveTotal = (float) $order->total_amount;
        $this->amountReceived = (float) $order->total_amount;
        $this->changeAmount = 0;
        $this->paymentMethod = 'cash';
        $this->orderNotes = $order->notes ?? '';
        $this->isSplitPayment = false;
        $this->paymentSplits = [];
        $this->splitRemaining = (float) $order->total_amount;
        $this->recalculateTotals();
        $this->showModal = true;
    }

    public function openMultiple(array $orderIds): void
    {
        $orders = Order::with(['items.product'])
            ->whereIn('id', $orderIds)
            ->where('payment_status', 'unpaid')
            ->get();

        if ($orders->isEmpty()) {
            $this->dispatch('notify', message: 'No unpaid orders found.', type: 'error');
            return;
        }

        $this->orders = $orders->toArray();
        $this->order = null;
        $this->isMultiOrder = true;
        $this->effectiveTotal = $orders->sum('total_amount');
        $this->amountReceived = $orders->sum('total_amount');
        $this->changeAmount = 0;
        $this->paymentMethod = 'cash';
        $this->orderNotes = '';
        $this->isSplitPayment = false;
        $this->paymentSplits = [];
        $this->showModal = true;
    }

    private function recalculateTotals(): void
    {
        if ($this->isMultiOrder) {
            $this->effectiveTotal = collect($this->orders)->sum('total_amount');
            $this->updatedAmountReceived();
            return;
        }
        $subTotal = round(max(0, (float) $this->subTotalAmount), 2);

        $manualDiscount = 0.0;
        if ($this->discountType === 'fixed') {
            $manualDiscount = round(min($subTotal, max(0, (float) $this->discountValue)), 2);
        } else {
            $rate = min(100, max(0, (float) $this->discountValue));
            $manualDiscount = round($subTotal * ($rate / 100), 2);
        }
        $this->manualDiscountAmount = $manualDiscount;

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
        $this->voucherDiscountAmount = $voucherDiscount;

        $remainingAfterVoucher = round(max(0, $remainingAfterManual - $voucherDiscount), 2);

        $pointsDiscount = 0.0;
        if ((int) $this->appliedPoints > 0) {
            $valuePerPoint = (float) $this->pointsRedeemAmount / max(1, (int) $this->pointsRedeemPoints);
            $amount = round(((int) $this->appliedPoints) * max(0, $valuePerPoint), 2);
            if ($amount > 0) {
                $pointsDiscount = round(min($remainingAfterVoucher, $amount), 2);
            }
        }
        $this->pointsDiscountAmount = $pointsDiscount;

        $totalDiscount = round(min($subTotal, $manualDiscount + $voucherDiscount + $pointsDiscount), 2);
        $this->discountAmount = $totalDiscount;

        $taxable = round(max(0, $subTotal - $totalDiscount), 2);
        $taxSum = round($taxable * ($this->taxRate / 100), 2);
        $this->taxAmount = $taxSum;
        $this->effectiveTotal = round($taxable + $taxSum, 2);
        $this->updatedAmountReceived();
    }

    public function close(): void
    {
        $this->showModal = false;
        $this->order = null;
        $this->orders = [];
        $this->isMultiOrder = false;
        $this->amountReceived = 0;
        $this->changeAmount = 0;
        $this->paymentMethod = 'cash';
        $this->orderNotes = '';
        $this->isSplitPayment = false;
        $this->paymentSplits = [];
    }

    public function updatedDiscountValue(): void
    {
        $this->discountValue = round(max(0, (float) $this->discountValue), 2);
        $this->recalculateTotals();
    }

    public function updatedVoucherDiscountValue(): void
    {
        $this->recalculateTotals();
    }

    public function updatedAppliedPoints(): void
    {
        $this->recalculateTotals();
    }

    public function updatedAmountReceived(): void
    {
        $total = $this->isMultiOrder 
            ? collect($this->orders)->sum('total_amount') 
            : $this->effectiveTotal;
        $this->changeAmount = max(0, $this->amountReceived - $total);
    }

    public function toggleSplitPayment(): void
    {
        $this->isSplitPayment = !$this->isSplitPayment;
        if ($this->isSplitPayment) {
            $total = $this->isMultiOrder 
                ? collect($this->orders)->sum('total_amount') 
                : ($this->order?->total_amount ?? 0);
            $this->splitRemaining = (float) $total;
            $this->paymentSplits = [];
        }
    }

    public function addSplit(): void
    {
        if ($this->splitAmount <= 0) return;
        $split = min($this->splitAmount, $this->splitRemaining);
        $this->paymentSplits[] = [
            'method' => $this->splitMethod,
            'amount' => $split,
        ];
        $this->splitRemaining -= $split;
        $this->splitAmount = 0;
    }

    public function removeSplit(int $index): void
    {
        if (!isset($this->paymentSplits[$index])) return;
        $split = $this->paymentSplits[$index];
        $this->splitRemaining += $split['amount'];
        array_splice($this->paymentSplits, $index, 1);
    }

    public function setSplitExact(): void
    {
        $this->splitAmount = $this->splitRemaining;
    }

    public function setExact(): void
    {
        $total = $this->isMultiOrder 
            ? collect($this->orders)->sum('total_amount') 
            : ($this->order?->total_amount ?? 0);
        $this->amountReceived = (float) $total;
    }

    public function openDiscountModal(): void
    {
        $this->showDiscountModal = true;
    }

    public function closeDiscountModal(): void
    {
        $this->showDiscountModal = false;
        $this->discountInputType = 'percent';
        $this->discountInputValue = 0;
    }

    public function setDiscountType(string $type): void
    {
        $this->discountType = $type;
    }

    public function applyManualDiscount(): void
    {
        if ($this->isMultiOrder) {
            $this->dispatch('notify', message: 'Manual discount not available for combined payments.', type: 'error');
            return;
        }
        if ($this->discountInputValue <= 0) {
            $this->dispatch('notify', message: 'Enter a discount value greater than 0.', type: 'error');
            return;
        }
        $this->discountType = $this->discountInputType;
        $this->discountValue = $this->discountInputValue;
        $this->recalculateTotals();
        $this->closeDiscountModal();
    }

    public function clearDiscount(): void
    {
        $this->discountType = 'percent';
        $this->discountValue = 0;
        $this->manualDiscountAmount = 0;
        $this->discountAmount = 0;
    }

    public function applyVoucher(): void
    {
        if ($this->isMultiOrder) {
            $this->dispatch('notify', message: 'Vouchers not available for combined payments.', type: 'error');
            return;
        }

        $code = strtoupper(trim((string) $this->voucherCode));
        if (!filled($code)) {
            $this->dispatch('notify', message: 'Enter voucher code.', type: 'error');
            return;
        }

        $resolved = $this->resolveVoucherForCode($code);
        $voucher = $resolved['voucher'] ?? null;
        $customerVoucher = $resolved['customerVoucher'] ?? null;

        if (!$voucher) {
            $this->dispatch('notify', message: 'Voucher code is not valid.', type: 'error');
            return;
        }

        $customer = $this->order?->customer;
        if ((bool) $voucher->can_combine_with_manual_discount === false && (float) $this->manualDiscountAmount > 0) {
            $this->dispatch('notify', message: 'This voucher cannot be combined with manual discount.', type: 'error');
            return;
        }

        if ((bool) $voucher->can_combine_with_points === false && (int) $this->appliedPoints > 0) {
            $this->dispatch('notify', message: 'This voucher cannot be combined with points.', type: 'error');
            return;
        }

        if ($customerVoucher) {
            if (!$customer) {
                $this->dispatch('notify', message: 'This voucher requires a customer.', type: 'error');
                return;
            }
            if ((int) $customerVoucher->customer_id !== (int) $customer->id) {
                $this->dispatch('notify', message: 'This voucher is not for this customer.', type: 'error');
                return;
            }
            if ($customerVoucher->used_order_id !== null) {
                $this->dispatch('notify', message: 'Voucher already used.', type: 'error');
                return;
            }
            if ($customerVoucher->expires_at && now()->gt($customerVoucher->expires_at)) {
                $this->dispatch('notify', message: 'Voucher expired.', type: 'error');
                return;
            }
        } else {
            if ((bool) $voucher->first_time_only || $voucher->per_customer_limit !== null) {
                if (!$customer) {
                    $this->dispatch('notify', message: 'Select a customer to use this voucher.', type: 'error');
                    return;
                }
            }
            if ((bool) $voucher->first_time_only) {
                $hasOrders = Order::where('customer_id', $customer->id)
                    ->where('payment_status', 'paid')
                    ->exists();
                if ($hasOrders) {
                    $this->dispatch('notify', message: 'Voucher only for first-time customers.', type: 'error');
                    return;
                }
            }
            if ($voucher->per_customer_limit !== null) {
                $used = Order::where('customer_id', $customer->id)
                    ->where('voucher_id', $voucher->id)
                    ->where('payment_status', 'paid')
                    ->count();
                if ($used >= (int) $voucher->per_customer_limit) {
                    $this->dispatch('notify', message: 'Voucher already used by this customer.', type: 'error');
                    return;
                }
            }
        }

        $this->appliedVoucherCode = $code;
        $this->appliedVoucherId = $voucher->id;
        $this->appliedVoucherMeta = [
            'name' => $voucher->name,
            'discount_type' => $voucher->discount_type,
            'discount_value' => (float) $voucher->discount_value,
        ];
        $this->voucherDiscountType = $voucher->discount_type;
        $this->voucherDiscountValue = (float) $voucher->discount_value;
        $this->voucherCode = '';
        $this->closeDiscountModal();
    }

    public function clearVoucher(): void
    {
        $this->appliedVoucherCode = '';
        $this->appliedVoucherId = null;
        $this->appliedVoucherMeta = [];
        $this->voucherDiscountType = 'percent';
        $this->voucherDiscountValue = 0;
        $this->voucherDiscountAmount = 0;
    }

    public function applyPoints(): void
    {
        if ($this->isMultiOrder) {
            $this->dispatch('notify', message: 'Points not available for combined payments.', type: 'error');
            return;
        }
        if (!$this->order || !$this->order->customer) {
            $this->dispatch('notify', message: 'No customer selected for this order.', type: 'error');
            return;
        }
        $customer = $this->order->customer;
        if ($this->pointsToRedeem <= 0) {
            $this->dispatch('notify', message: 'Enter points to redeem.', type: 'error');
            return;
        }
        if ($this->pointsMinRedeem > 0 && $this->pointsToRedeem < $this->pointsMinRedeem) {
            $this->dispatch('notify', message: 'Minimum points to redeem is ' . $this->pointsMinRedeem . '.', type: 'error');
            return;
        }
        if ($this->pointsToRedeem > (int) $customer->points_balance) {
            $this->dispatch('notify', message: 'Not enough points.', type: 'error');
            return;
        }
        $this->appliedPoints = $this->pointsToRedeem;
        $this->closeDiscountModal();
    }

    public function clearPoints(): void
    {
        $this->appliedPoints = 0;
        $this->pointsDiscountAmount = 0;
        $this->pointsToRedeem = 0;
    }

    #[\Livewire\Attributes\Computed]
    public function customer(): ?Customer
    {
        if (!$this->customerId) {
            return null;
        }

        return Customer::find($this->customerId);
    }

    #[\Livewire\Attributes\Computed]
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

    public function selectCustomer(int $customerId): void
    {
        $this->customerId = $customerId;
        $this->customerSearch = '';
        $this->recalculateTotals();
    }

    public function clearCustomer(): void
    {
        $this->customerId = null;
        $this->clearPoints();
        $this->customerSearch = '';
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

        $customer = Customer::create([
            'tenant_id' => app('tenant_id'),
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
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

    public function setDiscountTab(string $tab): void
    {
        $this->discountTab = $tab;
    }

    private function normalizeMobile(string $mobile): string
    {
        $mobile = trim($mobile);
        $mobile = str_replace([' ', '-', '(', ')'], '', $mobile);
        return $mobile;
    }

    private function resolveVoucherForCode(string $code): array
    {
        $voucher = null;
        $customerVoucher = null;
        $customerVoucher = CustomerVoucher::where('code', $code)->first();
        if ($customerVoucher) {
            $voucher = Voucher::find($customerVoucher->voucher_id);
        } else {
            $voucher = Voucher::where('code', $code)->first();
        }
        return ['voucher' => $voucher, 'customerVoucher' => $customerVoucher];
    }

    private function currentPointsPromoMultiplier(): float
    {
        if (!$this->pointsPromoIsEnabled) {
            return 1;
        }
        if ($this->pointsPromoStartsAt && now()->lt($this->pointsPromoStartsAt)) {
            return 1;
        }
        if ($this->pointsPromoEndsAt && now()->gt($this->pointsPromoEndsAt)) {
            return 1;
        }
        return max(1, (float) $this->pointsPromoMultiplier);
    }

    public function applyQuickDiscount(string $type, float $value): void
    {
        if ($this->isMultiOrder) {
            $this->dispatch('notify', message: 'Quick discounts not available for combined payments.', type: 'error');
            return;
        }
        $this->discountType = $type;
        $this->discountValue = $value;
        $this->closeDiscountModal();
    }

    public function collect(): void
    {
        if (!$this->order && empty($this->orders)) {
            $this->dispatch('notify', message: 'No order selected.', type: 'error');
            return;
        }

        // Validate split payment
        if ($this->isSplitPayment) {
            if (empty($this->paymentSplits)) {
                $this->dispatch('notify', message: 'Add at least one payment split.', type: 'error');
                return;
            }
            if ($this->splitRemaining > 0.01) {
                $this->dispatch('notify', message: 'Split payments do not cover the full amount.', type: 'error');
                return;
            }
        }

        if ($this->isMultiOrder) {
            $this->collectMultiple();
        } else {
            $this->collectSingle();
        }
    }

    private function collectSingle(): void
    {
        $order = $this->order;
        $customer = $this->customer;
        $voucher = null;
        $customerVoucher = null;
        $earnedPoints = 0;

        DB::transaction(function () use ($order, $customer, &$voucher, &$customerVoucher, &$earnedPoints) {
            // Handle voucher
            if ($this->appliedVoucherCode) {
                $resolved = $this->resolveVoucherForCode($this->appliedVoucherCode);
                $voucher = $resolved['voucher'];
                $customerVoucher = $resolved['customerVoucher'];

                if ($voucher) {
                    Voucher::where('id', $voucher->id)->increment('usage_count');
                    if ($customerVoucher) {
                        $customerVoucher->update([
                            'used_order_id' => $order->id,
                            'used_at' => now(),
                        ]);
                    }
                }
            }

            // Handle customer points
            if ($customer) {
                $subTotal = round(max(0, (float) $this->subTotalAmount), 2);
                $discount = round(max(0, (float) $this->discountAmount), 2);
                $earnBase = round(max(0, $subTotal - $discount), 2);
                $multiplier = $this->currentPointsPromoMultiplier();
                $earnedPoints = (int) floor($earnBase * (float) $this->pointsEarnRate * $multiplier);
                $earnedPoints = max(0, $earnedPoints);

                $redeemed = max(0, (int) $this->appliedPoints);
                $newBalance = (int) $customer->points_balance - $redeemed + $earnedPoints;
                $customer->update(['points_balance' => max(0, $newBalance)]);
            }

            // Update order
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
                'discount_type' => $this->discountType,
                'discount_value' => $this->discountValue,
                'discount_amount' => $this->discountAmount,
                'voucher_id' => $this->appliedVoucherId,
                'voucher_code' => $this->appliedVoucherCode,
                'points_redeemed' => $this->appliedPoints,
                'points_earned' => $earnedPoints,
                'tax_rate' => $this->taxRate,
                'tax_amount' => $this->taxAmount,
                'total_amount' => $this->effectiveTotal,
                'customer_id' => $this->customerId,
                'notes' => $this->orderNotes,
            ]);

            // Update table status to dirty after payment
            if ($order->table_id) {
                $table = RestaurantTable::find($order->table_id);
                if ($table) {
                    $table->markDirty();
                }
            }
        });

        $this->dispatch('notify', message: 'Payment collected for Order #' . $order->id, type: 'success');
        $this->dispatch('payment-collected', orderId: $order->id);
        $this->close();
    }

    private function collectMultiple(): void
    {
        $orderIds = collect($this->orders)->pluck('id')->toArray();
        $orders = Order::whereIn('id', $orderIds)->where('payment_status', 'unpaid')->get();
        $combinedTotal = $orders->sum('total_amount');
        $tableId = $orders->first()->table_id;

        DB::transaction(function () use ($orders, $tableId) {
            foreach ($orders as $order) {
                $order->update([
                    'payment_method' => $this->isSplitPayment
                    ? implode('+', array_unique(array_column($this->paymentSplits, 'method')))
                    : $this->paymentMethod,
                    'payment_splits' => $this->isSplitPayment ? $this->paymentSplits : null,
                    'payment_status' => 'paid',
                    'status' => 'completed',
                    'amount_paid' => $order->total_amount,
                    'change_amount' => 0,
                ]);
            }

            // Update table status to dirty after payment
            if ($tableId) {
                $table = RestaurantTable::find($tableId);
                if ($table) {
                    $remainingUnpaid = Order::where('table_id', $tableId)
                        ->where('payment_status', 'unpaid')
                        ->count();
                    if ($remainingUnpaid === 0) {
                        $table->markDirty();
                    }
                }
            }
        });

        $this->dispatch('notify', message: 'Payment collected for ' . $orders->count() . ' orders!', type: 'success');
        $this->dispatch('payment-collected', orderIds: $orderIds);
        $this->close();
    }

    public function render()
    {
        return view('livewire.collect-payment');
    }
}
