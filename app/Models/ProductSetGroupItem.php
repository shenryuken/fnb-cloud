<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSetGroupItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'set_group_id',
        'product_id',
        'extra_price',
        'sort_order',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductSetGroup::class, 'set_group_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

