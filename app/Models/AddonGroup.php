<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AddonGroup extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'min_select',
        'max_select',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_select' => 'integer',
        'max_select' => 'integer',
    ];

    /**
     * Get the individual addons in this group.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProductAddon::class);
    }

    /**
     * Get the products that use this addon group.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'addon_group_product');
    }
}
