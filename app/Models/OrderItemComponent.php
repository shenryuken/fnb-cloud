<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemComponent extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'order_item_id',
        'product_id',
        'group_name',
        'name',
        'quantity',
        'extra_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'extra_price' => 'decimal:2',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

