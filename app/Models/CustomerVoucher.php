<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerVoucher extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'voucher_id',
        'customer_id',
        'code',
        'issued_from_order_id',
        'used_order_id',
        'issued_at',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function issuedFromOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'issued_from_order_id');
    }

    public function usedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'used_order_id');
    }
}

