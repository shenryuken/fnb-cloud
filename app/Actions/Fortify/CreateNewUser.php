<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        // Create a new tenant for this user
        $tenant = Tenant::create([
            'name' => $input['name'],
            'slug' => Str::slug($input['name']) . '-' . Str::random(6),
            'is_active' => true,
        ]);

        // Create the user and assign to the tenant
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'tenant_id' => $tenant->id,
        ]);

        // Get or create the "Owner" role for this tenant
        $ownerRole = Role::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'owner'],
            ['name' => 'Owner']
        );

        // Assign all permissions to the Owner role if not already assigned
        if ($ownerRole->permissions()->count() === 0) {
            $ownerRole->permissions()->sync($this->getAllPermissionIds($tenant->id));
        }

        // Attach the Owner role to the user
        $user->roles()->attach($ownerRole);

        return $user;
    }

    /**
     * Get all permission IDs for a tenant
     */
    private function getAllPermissionIds($tenantId): array
    {
        return \DB::table('permissions')
            ->whereNull('tenant_id')
            ->orWhere('tenant_id', $tenantId)
            ->pluck('id')
            ->toArray();
    }
}
