<?php

namespace App\Livewire\Menu;

use App\Models\ProductAddon;
use App\Models\AddonGroup;
use Livewire\Attributes\Title;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Add-ons')]
#[Lazy]
class Addons extends Component
{
    use WithPagination;

    // Group Fields
    public string $group_name = '';
    public string $group_description = '';
    public int $min_select = 0;
    public int $max_select = 1;
    public bool $group_is_active = true;

    // Item Fields
    public string $name = '';
    public string $description = '';
    public float $price = 0;
    public bool $is_active = true;
    public ?int $addon_group_id = null;

    public ?AddonGroup $editingGroup = null;
    public ?ProductAddon $editingItem = null;
    
    public bool $isCreatingGroup = false;
    public bool $isCreatingItem = false;

    public function createGroup(): void
    {
        $this->reset(['group_name', 'group_description', 'min_select', 'max_select', 'group_is_active', 'editingGroup', 'isCreatingGroup', 'isCreatingItem']);
        $this->isCreatingGroup = true;
    }

    public function editGroup(AddonGroup $group): void
    {
        $this->editingGroup = $group;
        $this->group_name = $group->name;
        $this->group_description = $group->description ?? '';
        $this->min_select = $group->min_select;
        $this->max_select = $group->max_select;
        $this->group_is_active = $group->is_active;
        $this->isCreatingGroup = false;
        $this->isCreatingItem = false;
    }

    public function saveGroup(): void
    {
        $validated = $this->validate([
            'group_name' => 'required|string|max:255',
            'group_description' => 'nullable|string',
            'min_select' => 'required|integer|min:0',
            'max_select' => 'required|integer|min:1',
            'group_is_active' => 'boolean',
        ]);

        if ($this->editingGroup) {
            $this->editingGroup->update([
                'name' => $validated['group_name'],
                'description' => $validated['group_description'],
                'min_select' => $validated['min_select'],
                'max_select' => $validated['max_select'],
                'is_active' => $validated['group_is_active'],
            ]);
        } else {
            AddonGroup::create([
                'name' => $validated['group_name'],
                'description' => $validated['group_description'],
                'min_select' => $validated['min_select'],
                'max_select' => $validated['max_select'],
                'is_active' => $validated['group_is_active'],
            ]);
        }

        $this->reset(['group_name', 'group_description', 'min_select', 'max_select', 'group_is_active', 'editingGroup', 'isCreatingGroup']);
    }

    public function createItem(?int $groupId = null): void
    {
        $this->reset(['name', 'description', 'price', 'is_active', 'addon_group_id', 'editingItem', 'isCreatingItem', 'isCreatingGroup']);
        $this->addon_group_id = $groupId;
        $this->isCreatingItem = true;
    }

    public function editItem(ProductAddon $item): void
    {
        $this->editingItem = $item;
        $this->name = $item->name;
        $this->description = $item->description ?? '';
        $this->price = $item->price;
        $this->is_active = $item->is_active;
        $this->addon_group_id = $item->addon_group_id;
        $this->isCreatingItem = false;
        $this->isCreatingGroup = false;
    }

    public function saveItem(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'addon_group_id' => 'nullable|exists:addon_groups,id',
        ]);

        if ($this->editingItem) {
            $this->editingItem->update($validated);
        } else {
            ProductAddon::create($validated);
        }

        $this->reset(['name', 'description', 'price', 'is_active', 'addon_group_id', 'editingItem', 'isCreatingItem']);
    }

    public function deleteGroup(AddonGroup $group): void
    {
        $group->delete();
    }

    public function deleteItem(ProductAddon $item): void
    {
        $item->delete();
    }

    public function render()
    {
        return view('livewire.menu.addons', [
            'groups' => AddonGroup::with('items')->latest()->paginate(10),
            'standaloneItems' => ProductAddon::whereNull('addon_group_id')->get(),
        ]);
    }
}
