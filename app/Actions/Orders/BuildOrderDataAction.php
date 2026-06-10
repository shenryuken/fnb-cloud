<?php

namespace App\Actions\Orders;

use App\DTOs\CartItemData;
use App\DTOs\CreateOrderData;
use App\Exceptions\OrderException;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\Tenant;
use App\Services\OrderPricingService;

/**
 * Builds an authoritative CreateOrderData from raw (untrusted) client input.
 *
 * Used by the API and offline-sync endpoints. Prices are ALWAYS resolved from
 * the database — never trusted from the client — then run through the shared
 * OrderPricingService so the totals match the POS exactly.
 */
class BuildOrderDataAction
{
    public function __construct(
        private OrderPricingService $pricing,
    ) {}

    /**
     * @param  array<string, mixed>  $input  Validated request payload.
     */
    public function build(array $input, ?int $userId, string $source = 'api'): CreateOrderData
    {
        $items = [];
        $subTotal = 0.0;

        foreach ($input['items'] as $row) {
            $product = Product::query()->findOrFail($row['product_id']);

            $unitPrice = (float) $product->price;

            $variantId = $row['variant_id'] ?? null;
            if ($variantId) {
                $variant = $product->variants()->findOrFail($variantId);
                // Variant price is treated as the absolute unit price when set.
                $unitPrice = (float) $variant->price;
            }

            $quantity = (int) $row['quantity'];
            $addonIds = array_map('intval', $row['addon_ids'] ?? []);

            $addonTotal = 0.0;
            if (!empty($addonIds)) {
                $addons = ProductAddon::query()->whereIn('id', $addonIds)->get();
                if ($addons->count() !== count(array_unique($addonIds))) {
                    throw OrderException::make('One or more add-ons are invalid.');
                }
                $addonTotal = (float) $addons->sum('price');
            }

            $lineSubtotal = round(($unitPrice + $addonTotal) * $quantity, 2);
            $subTotal += $lineSubtotal;

            $items[] = new CartItemData(
                productId: (int) $product->id,
                variantId: $variantId ? (int) $variantId : null,
                quantity: $quantity,
                unitPrice: round($unitPrice, 2),
                subtotal: $lineSubtotal,
                notes: $row['notes'] ?? null,
                addonIds: $addonIds,
                setItems: $row['set_items'] ?? [],
            );
        }

        $subTotal = round($subTotal, 2);

        $taxes = $this->resolveTaxes();
        $discountType = $input['discount_type'] ?? 'percent';
        $discountValue = (float) ($input['discount_value'] ?? 0);

        $breakdown = $this->pricing->calculate(
            subTotal: $subTotal,
            discountType: $discountType,
            discountValue: $discountValue,
            taxes: $taxes,
            voucherCode: $input['voucher_code'] ?? null,
            appliedPoints: (int) ($input['points_redeemed'] ?? 0),
        );

        $orderType = $input['order_type'] ?? 'dine_in';
        $paymentStatus = $input['payment_status'] ?? 'paid';

        return new CreateOrderData(
            items: $items,
            orderType: $orderType,
            customerId: $input['customer_id'] ?? null,
            tableId: $input['table_id'] ?? null,
            tableNumber: $input['table_number'] ?? null,
            notes: $input['notes'] ?? null,
            shiftId: $input['shift_id'] ?? null,
            userId: $userId,
            subtotalAmount: $breakdown['subtotal'],
            totalAmount: $breakdown['total'],
            discountType: $discountType,
            discountValue: $discountValue,
            discountAmount: $breakdown['discount_amount'],
            manualDiscountAmount: $breakdown['manual_discount'],
            taxRate: $breakdown['tax_rate'],
            taxAmount: $breakdown['tax_amount'],
            voucherCode: $input['voucher_code'] ?? null,
            pointsRedeemed: (int) ($input['points_redeemed'] ?? 0),
            paymentMethod: $input['payment_method'] ?? 'cash',
            amountReceived: (float) ($input['amount_received'] ?? $breakdown['total']),
            changeAmount: max(0, round((float) ($input['amount_received'] ?? $breakdown['total']) - $breakdown['total'], 2)),
            paymentStatus: $paymentStatus,
            status: $paymentStatus === 'paid' ? 'completed' : 'pending',
            clientUuid: $input['client_uuid'] ?? null,
            source: $source,
        );
    }

    /**
     * Resolve the active tax lines for the current tenant.
     * Mirrors Pos::loadTaxes() so API totals match the POS exactly.
     *
     * @return array<int, array{name: string, rate: float}>
     */
    private function resolveTaxes(): array
    {
        $tenantId = app()->bound('tenant_id') ? app('tenant_id') : null;
        if (!$tenantId) {
            return [];
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return [];
        }

        return $tenant->taxes()
            ->where('is_enabled', true)
            ->orderBy('name')
            ->get(['name', 'rate'])
            ->map(fn ($t) => [
                'name' => (string) ($t->name ?? 'Tax'),
                'rate' => (float) ($t->rate ?? 0),
            ])
            ->all();
    }
}
