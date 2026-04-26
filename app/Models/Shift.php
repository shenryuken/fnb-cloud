<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'closed_by_user_id',
        'opened_at',
        'closed_at',
        'opening_cash',
        'expected_cash',
        'actual_cash',
        'difference',
        'total_sales',
        'cash_sales',
        'card_sales',
        'ewallet_sales',
        'qris_sales',
        'order_count',
        'refunds_total',
        'refunds_count',
        'status',
        'opening_notes',
        'closing_notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'ewallet_sales' => 'decimal:2',
        'qris_sales' => 'decimal:2',
        'refunds_total' => 'decimal:2',
    ];

    /**
     * Get the user who opened the shift.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who closed the shift.
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    /**
     * Get the orders for this shift.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the cash movements for this shift.
     */
    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    /**
     * Check if shift is open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Get duration of the shift.
     */
    public function getDurationAttribute(): ?string
    {
        $end = $this->closed_at ?? now();
        $diff = $this->opened_at->diff($end);
        
        if ($diff->h > 0) {
            return $diff->format('%hh %im');
        }
        return $diff->format('%im');
    }

    /**
     * Calculate expected cash based on opening + cash sales + movements.
     */
    public function calculateExpectedCash(): float
    {
        $cashIn = $this->cashMovements()->where('type', 'cash_in')->sum('amount');
        $cashOut = $this->cashMovements()->where('type', 'cash_out')->sum('amount');
        $adjustments = $this->cashMovements()->where('type', 'adjustment')->sum('amount');
        
        return (float) $this->opening_cash 
            + (float) $this->cash_sales 
            + (float) $cashIn 
            - (float) $cashOut 
            + (float) $adjustments;
    }

    /**
     * Recalculate sales totals from orders.
     */
    public function recalculateSales(): void
    {
        $orders = $this->orders()->where('status', 'completed')->get();
        
        $this->order_count = $orders->count();
        $this->total_sales = $orders->sum('total_amount');
        
        // Calculate by payment method
        $this->cash_sales = $orders->where('payment_method', 'cash')->sum('total_amount');
        $this->card_sales = $orders->where('payment_method', 'card')->sum('total_amount');
        $this->ewallet_sales = $orders->where('payment_method', 'ewallet')->sum('total_amount');
        $this->qris_sales = $orders->where('payment_method', 'qris')->sum('total_amount');
        
        // Handle split payments
        foreach ($orders->whereNotNull('payment_splits') as $order) {
            if (is_array($order->payment_splits)) {
                foreach ($order->payment_splits as $split) {
                    $method = $split['method'] ?? 'cash';
                    $amount = (float) ($split['amount'] ?? 0);
                    
                    match ($method) {
                        'cash' => $this->cash_sales += $amount,
                        'card' => $this->card_sales += $amount,
                        'ewallet' => $this->ewallet_sales += $amount,
                        'qris' => $this->qris_sales += $amount,
                        default => null,
                    };
                }
            }
        }
        
        // Calculate refunds
        $refundedOrders = $this->orders()->where('status', 'cancelled')->get();
        $this->refunds_count = $refundedOrders->count();
        $this->refunds_total = $refundedOrders->sum('total_amount');
        
        $this->expected_cash = $this->calculateExpectedCash();
        $this->save();
    }

    /**
     * Get the current open shift for a tenant.
     */
    public static function currentOpen(): ?self
    {
        return static::where('status', 'open')->latest('opened_at')->first();
    }
}
