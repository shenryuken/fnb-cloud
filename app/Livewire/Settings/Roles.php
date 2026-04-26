<?php

namespace App\Livewire\Settings;

use App\Models\Permission;
use App\Models\Role;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('User Roles & Permissions')]
class Roles extends Component
{
    public bool $showModal = false;
    public ?int $editingRoleId = null;

    // Form fields
    public string $name = '';
    public array $selectedPermissions = [];

    // ── Default role definitions ──────────────────────────────────────────

    /** Global default roles shared by all tenants. */
    private array $globalDefaults = [
        [
            'name'        => 'Super Admin',
            'slug'        => 'superadmin',
            'permissions' => [], // all
            'all'         => true,
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

    /** Custom roles available on the tenant level. */
    private array $tenantCustomRoles = [
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

    // ── Computed properties ───────────────────────────────────────────────

    public function getRolesProperty()
    {
        // For landlord: show only global roles
        // For tenants: show both global and tenant-specific roles
        if (auth()->user()->tenant_id === null) {
            return Role::withoutGlobalScopes()->where('tenant_id', null)->with('permissions')->get();
        }
        
        return Role::forTenant(auth()->user()->tenant_id)->with('permissions')->get();
    }

    public function getPermissionsProperty()
    {
        return Permission::orderBy('name')->get();
    }

    public function getIsLandlordProperty(): bool
    {
        return is_null(auth()->user()->tenant_id);
    }

    public function getDefaultDefinitionsProperty(): array
    {
        return $this->isLandlord ? $this->globalDefaults : $this->tenantCustomRoles;
    }

    // ── Actions ───────────────────────────────────────────────────────────

    public function create(): void
    {
        $this->reset(['name', 'selectedPermissions', 'editingRoleId']);
        $this->showModal = true;
    }

    public function edit(int $roleId): void
    {
        $role = Role::findOrFail($roleId);
        $this->editingRoleId = $roleId;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'               => 'required|string|max:255',
            'selectedPermissions' => 'array',
        ]);

        $slug = \Illuminate\Support\Str::slug($this->name);

        if ($this->editingRoleId) {
            $role = Role::findOrFail($this->editingRoleId);
            $role->update(['name' => $this->name, 'slug' => $slug]);
        } else {
            $role = Role::create([
                'name'      => $this->name,
                'slug'      => $slug,
                'tenant_id' => auth()->user()->tenant_id,
            ]);
        }

        $role->permissions()->sync($this->selectedPermissions);

        $this->showModal = false;
        $this->reset(['name', 'selectedPermissions', 'editingRoleId']);
    }

    public function delete(int $roleId): void
    {
        Role::findOrFail($roleId)->delete();
    }

    /**
     * Seed the default custom roles for this tenant.
     */
    public function seedDefaults(): void
    {
        $allPermissions = Permission::all()->keyBy('slug');
        $tenantId       = auth()->user()->tenant_id;
        $defaults       = $this->tenantCustomRoles;

        foreach ($defaults as $def) {
            $role = Role::firstOrCreate(
                ['slug' => $def['slug'], 'tenant_id' => $tenantId],
                ['name' => $def['name'], 'tenant_id' => $tenantId]
            );

            $permissionIds = collect($def['permissions'] ?? [])
                ->filter(fn ($s) => isset($allPermissions[$s]))
                ->map(fn ($s) => $allPermissions[$s]->id)
                ->toArray();

            $role->permissions()->sync($permissionIds);
        }

        session()->flash('status', 'Default custom roles seeded successfully.');
    }

    // ── Render ────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.settings.roles', [
            'roles'       => $this->roles,
            'permissions' => $this->permissions,
            'isLandlord'  => $this->isLandlord,
            'defaults'    => $this->defaultDefinitions,
        ]);
    }
}
