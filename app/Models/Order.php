<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Order extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'user_id',
        'table_number',
        'order_type', // dine_in, takeaway
        'total_amount',
        'subtotal_amount',
        'discount_type',
        'discount_value',
        'discount_amount',
        'voucher_code',
        'points_redeemed',
        'points_earned',
        'tax_rate',
        'tax_amount',
        'status', // pending, processing, completed, cancelled
        'kds_status', // pending, preparing, ready, served
        'preparing_at',
        'prep_time_minutes',
        'payment_method',
        'payment_splits',
        'payment_status',
        'amount_paid',
        'change_amount',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'points_redeemed' => 'integer',
        'points_earned' => 'integer',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'payment_splits' => 'array',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'preparing_at' => 'datetime',
        'prep_time_minutes' => 'integer',
    ];

    /**
     * Get the remaining time in minutes.
     */
    protected function remainingMinutes(): Attribute
    {
        return Attribute::make(
            get: function () {
                $prepTime = (int) $this->prep_time_minutes;
                if ($prepTime <= 0) {
                    return null;
                }

                // Use preparing_at if available, fallback to updated_at if status is preparing
                $startTime = $this->preparing_at;
                if (!$startTime && $this->kds_status === 'preparing') {
                    $startTime = $this->updated_at;
                }

                if (!$startTime) {
                    return null;
                }

                // Carbon difference calculation
                $elapsed = $startTime->diffInMinutes(now());
                $remaining = $prepTime - $elapsed;

                return (int) max(0, $remaining);
            }
        );
    }

    /**
     * Check if the order is overdue.
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: function () {
                $prepTime = (int) $this->prep_time_minutes;
                if ($prepTime <= 0) {
                    return false;
                }

                $startTime = $this->preparing_at;
                if (!$startTime && $this->kds_status === 'preparing') {
                    $startTime = $this->updated_at;
                }

                if (!$startTime) {
                    return false;
                }

                return $startTime->addMinutes($prepTime)->isPast();
            }
        );
    }

    /**
     * Get the items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the user who placed the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
