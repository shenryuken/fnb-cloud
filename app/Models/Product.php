<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'product_type',
        'name',
        'price',
        'description',
        'image_url',
        'badge_text',
        'tile_color',
        'is_active',
        'is_available',
        'track_stock',
        'stock_quantity',
        'low_stock_threshold',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'track_stock' => 'boolean',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Whether this product (without a tracked variant) is currently low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->track_stock
            && $this->stock_quantity <= $this->low_stock_threshold;
    }

    /**
     * Whether this product is out of stock at the product level.
     */
    public function isOutOfStock(): bool
    {
        return $this->track_stock && $this->stock_quantity <= 0;
    }

    /**
     * Get the stock movements for the product.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function setGroups(): HasMany
    {
        return $this->hasMany(ProductSetGroup::class);
    }

    /**
     * Get the addon groups for the product.
     */
    public function addonGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AddonGroup::class, 'addon_group_product');
    }

    /**
     * Get the individual addons for the product (Legacy or Direct).
     */
    public function addons(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ProductAddon::class, 'addon_product');
    }
}
