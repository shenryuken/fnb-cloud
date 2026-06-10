<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an order-creation payload for the API.
 *
 * Note: prices are NOT accepted from the client — they're resolved
 * server-side by BuildOrderDataAction. We only validate identifiers,
 * quantities, and order metadata here.
 */
class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_uuid' => 'nullable|uuid',
            'order_type' => 'nullable|in:dine_in,takeaway',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'table_id' => 'nullable|integer|exists:restaurant_tables,id',
            'table_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
            'shift_id' => 'nullable|integer|exists:shifts,id',

            'discount_type' => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'voucher_code' => 'nullable|string|max:100',
            'points_redeemed' => 'nullable|integer|min:0',

            'payment_method' => 'nullable|string|max:50',
            'payment_status' => 'nullable|in:paid,unpaid',
            'amount_received' => 'nullable|numeric|min:0',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:500',
            'items.*.addon_ids' => 'nullable|array',
            'items.*.addon_ids.*' => 'integer|exists:product_addons,id',
            'items.*.set_items' => 'nullable|array',
        ];
    }
}
