<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DefaultRolesSeeder extends Seeder
{
    /**
     * Seed default permissions and global default roles.
     * Tenant-specific roles are created on-demand when needed.
     */
    public function run(): void
    {
        // ── 1. Ensure all permissions exist ──────────────────────────────────
        $permissionData = [
            ['name' => 'Access POS',        'slug' => 'pos.access'],
            ['name' => 'Manage Orders',     'slug' => 'orders.manage'],
            ['name' => 'Access KDS',        'slug' => 'kds.access'],
            ['name' => 'Manage Menu',       'slug' => 'menu.manage'],
            ['name' => 'View Reports',      'slug' => 'reports.view'],
            ['name' => 'Manage Settings',   'slug' => 'settings.manage'],
            ['name' => 'Manage Roles',      'slug' => 'roles.manage'],
            ['name' => 'Manage Customers',  'slug' => 'customers.manage'],
            ['name' => 'Manage Vouchers',   'slug' => 'vouchers.manage'],
        ];

        $permissions = [];
        foreach ($permissionData as $p) {
            $permissions[$p['slug']] = Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // ── 2. Global default roles (tenant_id = null) ────────────────────────
        // These are shared across all tenants
        $globalRoles = [
            [
                'name'        => 'Super Admin',
                'slug'        => 'superadmin',
                'permissions' => array_keys($permissions), // all
            ],
            [
                'name'        => 'Admin',
                'slug'        => 'admin',
                'permissions' => [
                    'pos.access', 'orders.manage', 'kds.access',
                    'menu.manage', 'reports.view', 'settings.manage',
                    'customers.manage', 'vouchers.manage',
                ],
            ],
            [
                'name'        => 'Staff',
                'slug'        => 'staff',
                'permissions' => [
                    'pos.access', 'orders.manage', 'kds.access',
                ],
            ],
        ];

        foreach ($globalRoles as $roleData) {
            $role = Role::withoutGlobalScopes()->firstOrCreate(
                ['slug' => $roleData['slug'], 'tenant_id' => null],
                ['name' => $roleData['name'], 'tenant_id' => null]
            );

            $role->permissions()->sync(
                collect($roleData['permissions'])->map(fn ($slug) => $permissions[$slug]->id)->toArray()
            );
        }
    }
}
