<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            // Only set tenant_id if it wasn't explicitly set (including explicit null)
            // Check if tenant_id was explicitly provided in the model's attributes
            if (!array_key_exists('tenant_id', $model->getAttributes()) && app()->bound('tenant_id')) {
                $model->tenant_id = app('tenant_id');
            }
        });
    }

    /**
     * Get the tenant that owns the model.
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
