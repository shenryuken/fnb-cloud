<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemAddon extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'order_item_id',
        'addon_id',
        'name',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the order item that owns the addon.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the addon that this order item addon refers to.
     */
    public function addon(): BelongsTo
    {
        return $this->belongsTo(ProductAddon::class, 'addon_id');
    }
}
