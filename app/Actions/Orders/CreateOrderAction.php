<?php

namespace App\Actions\Orders;

use App\DTOs\CartItemData;
use App\DTOs\CreateOrderData;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductAddon;
use App\Models\RestaurantTable;
use App\Services\InventoryService;
use App\Services\LoyaltyService;
use App\Services\VoucherService;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for creating an order.
 *
 * Extracted from Pos::checkout() / placeOrderPayLater() so the POS (Livewire)
 * and the API share identical persistence + validation semantics. Runs the
 * whole operation in one DB transaction with row locks on vouchers, customers,
 * and usage counts to stay safe under concurrency.
 *
 * Returns the created Order with `issuedVoucherCodes` attached as a transient
 * attribute for the caller (receipt display).
 */
class CreateOrderAction
{
    public function __construct(
        private VoucherService $vouchers,
        private LoyaltyService $loyalty,
        private InventoryService $inventory,
    ) {}

    public function execute(CreateOrderData $data): Order
    {
        // Idempotency: if this client_uuid was already synced, return the existing order.
        if (filled($data->clientUuid)) {
            $existing = Order::query()->where('client_uuid', $data->clientUuid)->first();
            if ($existing) {
                $existing->setAttribute('issuedVoucherCodes', []);

                return $existing;
            }
        }

        $issuedCodes = [];

        $order = DB::transaction(function () use ($data, &$issuedCodes) {
            $voucherCode = filled($data->voucherCode) ? strtoupper(trim($data->voucherCode)) : null;
            $points = max(0, $data->pointsRedeemed);

            $customer = null;
            if ($data->customerId) {
                $customer = Customer::where('id', $data->customerId)->lockForUpdate()->first();
            }

            // Resolve + validate voucher (throws OrderException on failure).
            $resolved = $this->vouchers->validateForCheckout(
                voucherCode: $voucherCode,
                customer: $customer,
                points: $points,
                manualDiscountAmount: $data->manualDiscountAmount,
            );
            $voucher = $resolved['voucher'];
            $customerVoucher = $resolved['customerVoucher'];

            // Validate point redemption.
            $this->loyalty->assertCanRedeem($customer, $points);

            // Apply loyalty balance change (earn/redeem) and capture earned points.
            $earnedPoints = 0;
            if ($customer) {
                $earnedPoints = $this->loyalty->applyBalanceChange(
                    customer: $customer,
                    pointsRedeemed: $points,
                    subTotal: $data->subtotalAmount,
                    discountAmount: $data->discountAmount,
                    earnRate: $data->pointsEarnRate,
                    promoMultiplier: $data->pointsPromoMultiplier,
                );
            }

            $order = Order::create([
                'client_uuid' => $data->clientUuid,
                'source' => $data->source,
                'shift_id' => $data->shiftId,
                'user_id' => $data->userId,
                'customer_id' => $data->customerId,
                'table_id' => $data->orderType === 'dine_in' ? $data->tableId : null,
                'table_number' => $data->orderType === 'dine_in' ? $data->tableNumber : null,
                'order_type' => $data->orderType,
                'notes' => $data->notes,
                'status' => $data->status,
                'total_amount' => $data->totalAmount,
                'subtotal_amount' => $data->subtotalAmount,
                'discount_type' => $data->discountType,
                'discount_value' => $data->discountValue,
                'discount_amount' => $data->discountAmount,
                'voucher_id' => $voucher?->id,
                'voucher_code' => $voucherCode,
                'points_redeemed' => $points,
                'points_earned' => $earnedPoints,
                'tax_rate' => $data->taxRate,
                'tax_amount' => $data->taxAmount,
                'payment_method' => $data->isSplitPayment
                    ? implode('+', array_unique(array_column($data->paymentSplits ?? [], 'method')))
                    : $data->paymentMethod,
                'payment_splits' => $data->isSplitPayment ? $data->paymentSplits : null,
                'payment_status' => $data->paymentStatus,
                'amount_paid' => $this->resolveAmountPaid($data),
                'change_amount' => $data->isSplitPayment ? 0 : $data->changeAmount,
            ]);

            if ($voucher) {
                $this->vouchers->consume($voucher, $customerVoucher, $order);
            }

            $this->persistItems($order, $data->items);

            // Deduct stock for tracked items (no-op for untracked products/variants).
            $this->inventory->deductForOrder($order);

            if ($customer) {
                $issuedCodes = $this->vouchers->issueOnSpend($customer, $order);
            }

            return $order;
        });

        // Post-commit side effects (mirror Pos::checkout()).
        if ($data->tableId && $data->orderType === 'dine_in') {
            RestaurantTable::find($data->tableId)?->markDirty();
        }

        if ($data->shiftId) {
            $order->shift?->recalculateSales();
        }

        $order->setAttribute('issuedVoucherCodes', $issuedCodes);

        return $order;
    }

    /**
     * Persist line items, their add-ons, and set components.
     *
     * @param  array<int, CartItemData>  $items
     */
    private function persistItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $orderItem = $order->items()->create([
                'product_id' => $item->productId,
                'variant_id' => $item->variantId,
                'quantity' => $item->quantity,
                'unit_price' => $item->unitPrice,
                'subtotal' => $item->subtotal,
                'notes' => $item->notes,
            ]);

            foreach ($item->addonIds as $addonId) {
                $addon = ProductAddon::find($addonId);
                if (!$addon) {
                    continue;
                }
                $orderItem->addons()->create([
                    'addon_id' => $addon->id,
                    'name' => $addon->name,
                    'price' => $addon->price,
                ]);
            }

            foreach ($item->setItems as $component) {
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

    private function resolveAmountPaid(CreateOrderData $data): float
    {
        if ($data->paymentStatus !== 'paid') {
            return 0;
        }

        if ($data->isSplitPayment) {
            return round(collect($data->paymentSplits ?? [])->sum('amount'), 2);
        }

        return $data->amountReceived;
    }
}
