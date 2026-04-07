<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'address',
        'phone',
        'logo_url',
        'receipt_email',
        'receipt_header',
        'receipt_footer',
        'receipt_size',
        'tax_rate',
        'points_earn_rate',
        'points_redeem_value_per_100',
        'points_min_redeem',
        'points_earn_points',
        'points_earn_amount',
        'points_redeem_points',
        'points_redeem_amount',
        'points_promo_is_enabled',
        'points_promo_multiplier',
        'points_promo_starts_at',
        'points_promo_ends_at',
        'business_day_start_time',
        'business_day_end_time',
        'is_active',
        'is_busy',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_busy' => 'boolean',
        'tax_rate' => 'decimal:2',
        'points_earn_rate' => 'decimal:4',
        'points_redeem_value_per_100' => 'decimal:2',
        'points_min_redeem' => 'integer',
        'points_earn_points' => 'decimal:4',
        'points_earn_amount' => 'decimal:2',
        'points_redeem_points' => 'integer',
        'points_redeem_amount' => 'decimal:2',
        'points_promo_is_enabled' => 'boolean',
        'points_promo_multiplier' => 'decimal:2',
        'points_promo_starts_at' => 'datetime',
        'points_promo_ends_at' => 'datetime',
    ];

    /**
     * Get the users for the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(TenantBusinessHour::class);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(TenantTax::class);
    }

    public function quickNotes(): HasMany
    {
        return $this->hasMany(QuickNote::class);
    }
}
