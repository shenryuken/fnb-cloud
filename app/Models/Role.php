<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'slug'];

    /**
     * Scope to get roles for the current tenant including global roles
     */
    public function scopeForTenant(Builder $query, $tenantId = null): Builder
    {
        $tenantId = $tenantId ?? auth()?->user()?->tenant_id;
        
        return $query->where(function ($q) use ($tenantId) {
            $q->whereNull('tenant_id') // Global roles
              ->orWhere('tenant_id', $tenantId); // Tenant-specific roles
        });
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
