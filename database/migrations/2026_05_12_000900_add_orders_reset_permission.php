<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate(
            ['slug' => 'orders.reset'],
            ['name' => 'Reset Orders', 'slug' => 'orders.reset']
        );

        $ownerRole = Role::withoutGlobalScopes()
            ->whereNull('tenant_id')
            ->where('slug', 'owner')
            ->first();

        if ($ownerRole) {
            $ownerRole->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $managerRole = Role::withoutGlobalScopes()
            ->whereNull('tenant_id')
            ->where('slug', 'manager')
            ->first();

        if ($managerRole) {
            $managerRole->permissions()->detach([$permission->id]);
        }
    }

    public function down(): void
    {
        $permission = Permission::where('slug', 'orders.reset')->first();
        if (!$permission) {
            return;
        }

        $ownerRole = Role::withoutGlobalScopes()
            ->whereNull('tenant_id')
            ->where('slug', 'owner')
            ->first();

        if ($ownerRole) {
            $ownerRole->permissions()->detach([$permission->id]);
        }
    }
};

