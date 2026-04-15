<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
    ];

    public function getUsedCountAttribute(): int
    {
        return (int) $this->usage_count;
    }
}
