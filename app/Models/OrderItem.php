<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price',
        'variant_price',
        'subtotal',
        'notes',
        'kds_is_ready',
        'kds_ready_at',
        'kds_is_served',
        'kds_served_at',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'variant_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'kds_is_ready' => 'boolean',
        'kds_ready_at' => 'datetime',
        'kds_is_served' => 'boolean',
        'kds_served_at' => 'datetime',
    ];

    /**
     * Get the order that owns the item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product for the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant for the item.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the addons for the order item.
     */
    public function addons(): HasMany
    {
        return $this->hasMany(OrderItemAddon::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(OrderItemComponent::class);
    }
}
