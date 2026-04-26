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

    /** Landlord roles - for system administrators only */
    private array $landlordRoles = [
        'superadmin',
        'admin',
        'staff',
    ];

    /** Restaurant roles - for tenant users */
    private array $restaurantRoles = [
        'owner',
        'kitchen-staff',
        'waiter',
        'cashier',
    ];

    // ── Computed properties ───────────────────────────────────────────────

    public function getRolesProperty()
    {
        // For landlord: show only landlord roles (superadmin, admin, staff)
        if (auth()->user()->tenant_id === null) {
            return Role::withoutGlobalScopes()
                ->where('tenant_id', null)
                ->whereIn('slug', $this->landlordRoles)
                ->with('permissions')
                ->get();
        }
        
        // For tenants: show restaurant default roles + any custom tenant roles
        return Role::withoutGlobalScopes()
            ->where(function ($q) {
                $q->where(function ($sub) {
                    // Global restaurant roles
                    $sub->whereNull('tenant_id')
                        ->whereIn('slug', $this->restaurantRoles);
                })->orWhere(function ($sub) {
                    // Custom roles for this tenant
                    $sub->where('tenant_id', auth()->user()->tenant_id);
                });
            })
            ->with('permissions')
            ->get();
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
        // Get the appropriate default roles based on user type
        $slugs = $this->isLandlord ? $this->landlordRoles : $this->restaurantRoles;
        
        return Role::withoutGlobalScopes()
            ->where('tenant_id', null)
            ->whereIn('slug', $slugs)
            ->orderByRaw("FIELD(slug, '" . implode("','", $slugs) . "')")
            ->with('permissions')
            ->get()
            ->map(fn($role) => [
                'name' => $role->name,
                'slug' => $role->slug,
                'permissions' => $role->permissions->pluck('slug')->toArray(),
                'all' => $role->permissions->count() === Permission::count(),
            ])
            ->toArray();
    }

    /**
     * Check if a role is a global default (read-only)
     */
    public function isDefaultRole(Role $role): bool
    {
        // Global default roles (tenant_id = null) are read-only
        return $role->tenant_id === null;
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
        
        // Prevent editing default roles
        if ($this->isDefaultRole($role)) {
            session()->flash('error', 'Cannot edit default roles.');
            return;
        }
        
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
        $role = Role::findOrFail($roleId);
        
        // Prevent deleting default roles
        if ($this->isDefaultRole($role)) {
            session()->flash('error', 'Cannot delete default roles.');
            return;
        }
        
        $role->delete();
    }

    /**
     * Create a new custom role (only for tenants, not for default roles)
     */
    public function seedDefaults(): void
    {
        session()->flash('info', 'Default roles are automatically available. Create custom roles specific to your tenant here.');
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
