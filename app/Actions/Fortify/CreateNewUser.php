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

        // Attach the global "Owner" role to the user
        // Owner is a global default role with all permissions
        $ownerRole = Role::withoutGlobalScopes()
            ->where('slug', 'owner')
            ->where('tenant_id', null)
            ->firstOrFail();

        $user->roles()->attach($ownerRole);

        return $user;
    }
}
