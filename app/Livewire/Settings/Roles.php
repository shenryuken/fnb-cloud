<?php

namespace App\Livewire\Settings;

use App\Models\Role;
use App\Models\Permission;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('User Roles & Permissions')]
class Roles extends Component
{
    public $showModal = false;
    public $editingRoleId = null;
    
    // Form fields
    public $name = '';
    public $selectedPermissions = [];

    public function getRolesProperty()
    {
        return Role::with('permissions')->get();
    }

    public function getPermissionsProperty()
    {
        return Permission::all();
    }

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
            'name' => 'required|string|max:255',
            'selectedPermissions' => 'array'
        ]);

        $slug = \Illuminate\Support\Str::slug($this->name);

        if ($this->editingRoleId) {
            $role = Role::findOrFail($this->editingRoleId);
            $role->update([
                'name' => $this->name,
                'slug' => $slug
            ]);
        } else {
            $role = Role::create([
                'name' => $this->name,
                'slug' => $slug,
                'tenant_id' => auth()->user()->tenant_id
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

    public function render()
    {
        return view('livewire.settings.roles', [
            'roles' => $this->roles,
            'permissions' => $this->permissions
        ]);
    }
}
