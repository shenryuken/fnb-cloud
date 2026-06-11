<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Consistent API representation of an Order.
 *
 * Keeps the API contract stable and decoupled from the DB column layout,
 * and is shared by the V1 order endpoints and the sync endpoints.
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_uuid' => $this->client_uuid,
            'source' => $this->source,
            'order_type' => $this->order_type,
            'status' => $this->status,
            'kds_status' => $this->kds_status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'table_id' => $this->table_id,
            'table_number' => $this->table_number,
            'customer_id' => $this->customer_id,
            'subtotal_amount' => (float) $this->subtotal_amount,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount' => (float) $this->tax_amount,
            'total_amount' => (float) $this->total_amount,
            'amount_paid' => (float) $this->amount_paid,
            'change_amount' => (float) $this->change_amount,
            'points_earned' => (int) $this->points_earned,
            'points_redeemed' => (int) $this->points_redeemed,
            'voucher_code' => $this->voucher_code,
            'notes' => $this->notes,
            'issued_vouchers' => $this->getAttribute('issuedVoucherCodes') ?? [],
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->relationLoaded('product') ? $item->product?->name : null,
                'variant_id' => $item->variant_id,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
                'notes' => $item->notes,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
