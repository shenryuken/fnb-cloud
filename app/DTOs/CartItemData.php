<?php

namespace App\DTOs;

/**
 * Represents a single line item in an order request.
 *
 * Framework-agnostic value object built from either the POS cart array
 * or an API request payload, so the same CreateOrderAction can consume both.
 */
class CartItemData
{
    /**
     * @param  array<int>  $addonIds
     * @param  array<int, array{product_id?: int|null, group_name?: string|null, name?: string, extra_price?: float}>  $setItems
     */
    public function __construct(
        public int $productId,
        public ?int $variantId,
        public int $quantity,
        public float $unitPrice,
        public float $subtotal,
        public ?string $notes = null,
        public array $addonIds = [],
        public array $setItems = [],
    ) {}

    /**
     * Build from the POS cart item array shape.
     *
     * @param  array<string, mixed>  $item
     */
    public static function fromCartArray(array $item): self
    {
        return new self(
            productId: (int) $item['product_id'],
            variantId: isset($item['variant_id']) ? ($item['variant_id'] !== null ? (int) $item['variant_id'] : null) : null,
            quantity: (int) ($item['quantity'] ?? 1),
            unitPrice: (float) ($item['unit_price'] ?? 0),
            subtotal: (float) ($item['subtotal'] ?? 0),
            notes: $item['notes'] ?? null,
            addonIds: array_map('intval', $item['addon_ids'] ?? []),
            setItems: $item['set_items'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'variant_id' => $this->variantId,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'subtotal' => $this->subtotal,
            'notes' => $this->notes,
            'addon_ids' => $this->addonIds,
            'set_items' => $this->setItems,
        ];
    }
}
