<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\BelongsToTenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'pin', 'tenant_id', 'api_token'])]
#[Hidden(['password', 'pin', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token', 'api_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, BelongsToTenant, HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        // Bypass tenant scope to include global roles (tenant_id = null)
        return $this->belongsToMany(Role::class)->withoutGlobalScopes();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->tenant_id === null && $this->hasRole('superadmin')) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('slug', $permission))
            ->exists();
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()
            ->where('slug', $role)
            ->exists();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
