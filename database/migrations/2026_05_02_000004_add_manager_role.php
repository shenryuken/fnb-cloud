<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Role;
use App\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add Manager role for tenants if it doesn't exist
        $role = Role::withoutGlobalScopes()->firstOrCreate(
            ['slug' => 'manager', 'tenant_id' => null],
            ['name' => 'Manager', 'tenant_id' => null]
        );

        // Give Manager all permissions (same as Owner)
        $permissions = Permission::all()->pluck('id')->toArray();
        $role->permissions()->sync($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::withoutGlobalScopes()
            ->where('slug', 'manager')
            ->whereNull('tenant_id')
            ->delete();
    }
};
