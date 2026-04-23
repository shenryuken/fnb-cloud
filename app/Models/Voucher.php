<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'type',
        'value',
        'is_active',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_count',
        'per_customer_limit',
        'first_time_only',
        'can_combine_with_manual_discount',
        'can_combine_with_points',
        'free_product_id',
        'free_quantity',
        'issue_on_min_spend',
        'issue_expires_in_days',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'per_customer_limit' => 'integer',
        'first_time_only' => 'boolean',
        'can_combine_with_manual_discount' => 'boolean',
        'can_combine_with_points' => 'boolean',
        'free_product_id' => 'integer',
        'free_quantity' => 'integer',
        'issue_on_min_spend' => 'decimal:2',
        'issue_expires_in_days' => 'integer',
    ];

    public function getUsedCountAttribute(): int
    {
        return (int) $this->usage_count;
    }

    public function freeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'free_product_id');
    }

    public function customerVouchers(): HasMany
    {
        return $this->hasMany(CustomerVoucher::class);
    }
}
