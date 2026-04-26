<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DefaultRolesSeeder extends Seeder
{
    /**
     * Seed default permissions and default roles for every tenant.
     * Also seeds landlord-level roles (no tenant_id).
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

        // ── 2. Landlord roles (tenant_id = null) ─────────────────────────────
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
                'permissions' => [
                    'pos.access', 'orders.manage', 'kds.access',
                ],
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

        // ── 3. Tenant roles – seeded for every tenant ────────────────────────
        $tenantRoles = [
            [
                'name'        => 'Admin',
                'slug'        => 'admin',
                'permissions' => array_keys($permissions), // full access
            ],
            [
                'name'        => 'Kitchen Staff',
                'slug'        => 'kitchen-staff',
                'permissions' => [
                    'kds.access', 'orders.manage',
                ],
            ],
            [
                'name'        => 'Waiter',
                'slug'        => 'waiter',
                'permissions' => [
                    'pos.access', 'orders.manage',
                ],
            ],
            [
                'name'        => 'Cashier',
                'slug'        => 'cashier',
                'permissions' => [
                    'pos.access', 'orders.manage', 'customers.manage', 'vouchers.manage',
                ],
            ],
        ];

        foreach (Tenant::all() as $tenant) {
            app()->instance('tenant_id', $tenant->id);

            foreach ($tenantRoles as $roleData) {
                $role = Role::firstOrCreate(
                    ['slug' => $roleData['slug'], 'tenant_id' => $tenant->id],
                    ['name' => $roleData['name'], 'tenant_id' => $tenant->id]
                );

                $role->permissions()->sync(
                    collect($roleData['permissions'])->map(fn ($slug) => $permissions[$slug]->id)->toArray()
                );
            }
        }
    }
}
