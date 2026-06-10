<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for all stock mutations.
 *
 * Every change to on-hand quantity flows through here so that a matching
 * StockMovement audit row is always written, and concurrent writes are
 * serialised with row locks. Used by the POS/API order flow (deduction),
 * the inventory management UI (restock/adjust), and order voids (returns).
 */
class InventoryService
{
    /**
     * Deduct stock for every tracked line item in an order.
     *
     * Safe to call for all orders — items whose product/variant does not track
     * stock are skipped. Stock is allowed to go negative (oversell) rather than
     * blocking a sale, but each movement is recorded for reconciliation.
     */
    public function deductForOrder(Order $order): void
    {
        $items = $order->items()->get(['id', 'product_id', 'variant_id', 'quantity']);

        foreach ($items as $item) {
            $this->applyChange(
                productId: $item->product_id,
                variantId: $item->variant_id,
                delta: -1 * (int) $item->quantity,
                type: StockMovement::TYPE_SALE,
                order: $order,
                userId: $order->user_id,
            );
        }
    }

    /**
     * Return previously deducted stock when an order is voided/refunded.
     */
    public function returnForOrder(Order $order, ?string $reason = null): void
    {
        $items = $order->items()->get(['id', 'product_id', 'variant_id', 'quantity']);

        foreach ($items as $item) {
            $this->applyChange(
                productId: $item->product_id,
                variantId: $item->variant_id,
                delta: (int) $item->quantity,
                type: StockMovement::TYPE_VOID_RETURN,
                order: $order,
                userId: $order->user_id,
                reason: $reason,
            );
        }
    }

    /**
     * Set an absolute on-hand quantity (manual stock-take). Records the delta.
     */
    public function setQuantity(Product $product, ?ProductVariant $variant, int $quantity, ?int $userId = null, ?string $reason = null): void
    {
        $current = $variant ? (int) $variant->stock_quantity : (int) $product->stock_quantity;
        $delta = $quantity - $current;

        if ($delta === 0) {
            return;
        }

        $this->applyChange(
            productId: $product->id,
            variantId: $variant?->id,
            delta: $delta,
            type: StockMovement::TYPE_ADJUSTMENT,
            userId: $userId,
            reason: $reason,
        );
    }

    /**
     * Add stock (restock/receive delivery). Records a positive movement.
     */
    public function restock(Product $product, ?ProductVariant $variant, int $quantity, ?int $userId = null, ?string $reason = null): void
    {
        if ($quantity <= 0) {
            return;
        }

        $this->applyChange(
            productId: $product->id,
            variantId: $variant?->id,
            delta: $quantity,
            type: StockMovement::TYPE_RESTOCK,
            userId: $userId,
            reason: $reason,
        );
    }

    /**
     * Core primitive: apply a signed delta to a product/variant's stock with a
     * row lock, only when stock tracking is enabled, and log a movement.
     */
    private function applyChange(
        int $productId,
        ?int $variantId,
        int $delta,
        string $type,
        ?Order $order = null,
        ?int $userId = null,
        ?string $reason = null,
    ): void {
        DB::transaction(function () use ($productId, $variantId, $delta, $type, $order, $userId, $reason) {
            if ($variantId) {
                $variant = ProductVariant::whereKey($variantId)->lockForUpdate()->first();
                if (!$variant || !$variant->track_stock) {
                    return;
                }
                $balance = (int) $variant->stock_quantity + $delta;
                $variant->update(['stock_quantity' => $balance]);
            } else {
                $product = Product::whereKey($productId)->lockForUpdate()->first();
                if (!$product || !$product->track_stock) {
                    return;
                }
                $balance = (int) $product->stock_quantity + $delta;
                $product->update(['stock_quantity' => $balance]);
            }

            StockMovement::create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'type' => $type,
                'quantity_change' => $delta,
                'balance_after' => $balance,
                'order_id' => $order?->id,
                'user_id' => $userId,
                'reason' => $reason,
            ]);
        });
    }
}
