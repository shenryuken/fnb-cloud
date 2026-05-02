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

        // ── 2. Landlord roles (tenant_id = null) ────────────────────────────
        // These are for system administrators (users without a tenant)
        $landlordRoles = [
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
                'permissions' => ['pos.access', 'orders.manage', 'kds.access'],
            ],
        ];

        foreach ($landlordRoles as $roleData) {
            $role = Role::withoutGlobalScopes()->firstOrCreate(
                ['slug' => $roleData['slug'], 'tenant_id' => null],
                ['name' => $roleData['name'], 'tenant_id' => null]
            );

            $role->permissions()->sync(
                collect($roleData['permissions'])->map(fn ($slug) => $permissions[$slug]->id)->toArray()
            );
        }

        // ── 3. Restaurant default roles (tenant_id = null) ────────────────────
        // These are shared defaults for all restaurants/tenants
        $restaurantRoles = [
            [
                'name'        => 'Owner',
                'slug'        => 'owner',
                'permissions' => array_keys($permissions), // all
            ],
            [
                'name'        => 'Manager',
                'slug'        => 'manager',
                'permissions' => array_keys($permissions), // all (same as owner)
            ],
            [
                'name'        => 'Kitchen Staff',
                'slug'        => 'kitchen-staff',
                'permissions' => ['kds.access', 'orders.manage'],
            ],
            [
                'name'        => 'Waiter',
                'slug'        => 'waiter',
                'permissions' => ['pos.access', 'orders.manage'],
            ],
            [
                'name'        => 'Cashier',
                'slug'        => 'cashier',
                'permissions' => ['pos.access', 'orders.manage', 'customers.manage', 'vouchers.manage'],
            ],
        ];

        foreach ($restaurantRoles as $roleData) {
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
