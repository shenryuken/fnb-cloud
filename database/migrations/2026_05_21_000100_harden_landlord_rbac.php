<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissionData = [
            ['name' => 'View Landlord Dashboard', 'slug' => 'landlord.dashboard.view'],
            ['name' => 'Manage Tenants', 'slug' => 'landlord.tenants.manage'],
            ['name' => 'View Audit Logs', 'slug' => 'landlord.audit.view'],
            ['name' => 'View System Health', 'slug' => 'landlord.system-health.view'],
        ];

        foreach ($permissionData as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        $superadmin = Role::withoutGlobalScopes()->firstOrCreate(
            ['slug' => 'superadmin', 'tenant_id' => null],
            ['name' => 'Super Admin', 'tenant_id' => null],
        );

        $admin = Role::withoutGlobalScopes()->firstOrCreate(
            ['slug' => 'admin', 'tenant_id' => null],
            ['name' => 'Admin', 'tenant_id' => null],
        );

        $staff = Role::withoutGlobalScopes()->firstOrCreate(
            ['slug' => 'staff', 'tenant_id' => null],
            ['name' => 'Staff', 'tenant_id' => null],
        );

        $allPermissionIds = Permission::query()->pluck('id')->all();
        $superadmin->permissions()->sync($allPermissionIds);

        $adminPermSlugs = [
            'landlord.dashboard.view',
            'landlord.tenants.manage',
            'landlord.audit.view',
            'landlord.system-health.view',
        ];
        $adminPermIds = Permission::query()->whereIn('slug', $adminPermSlugs)->pluck('id')->all();
        $admin->permissions()->sync($adminPermIds);

        $staffPermSlugs = [
            'landlord.dashboard.view',
        ];
        $staffPermIds = Permission::query()->whereIn('slug', $staffPermSlugs)->pluck('id')->all();
        $staff->permissions()->sync($staffPermIds);

        User::withoutGlobalScopes()
            ->whereNull('tenant_id')
            ->with('roles:id')
            ->get()
            ->each(function (User $user) use ($admin) {
                if ($user->roles->count() > 0) {
                    return;
                }

                $user->roles()->attach($admin->id);
            });
    }

    public function down(): void
    {
        // No-op: keep permissions/roles stable once introduced.
    }
};
