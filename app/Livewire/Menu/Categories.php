<?php

namespace App\Livewire\Menu;

use App\Models\Category;
use Livewire\Attributes\Title;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Categories')]
#[Lazy]
class Categories extends Component
{
    use WithPagination;

    public string $name = '';
    public string $description = '';
    public int $sort_order = 0;
    public bool $is_active = true;

    public ?Category $editing = null;
    public bool $isCreating = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'sort_order' => 'required|integer|min:0',
        'is_active' => 'boolean',
    ];

    /**
     * Start creating a new category.
     */
    public function create(): void
    {
        $this->reset(['name', 'description', 'sort_order', 'is_active', 'editing']);
        $this->isCreating = true;
    }

    /**
     * Start editing a category.
     */
    public function edit(Category $category): void
    {
        $this->editing = $category;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->sort_order = $category->sort_order;
        $this->is_active = $category->is_active;
        $this->isCreating = false;
    }

    /**
     * Save the category.
     */
    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            Category::create($validated);
        }

        $this->reset(['name', 'description', 'sort_order', 'is_active', 'editing', 'isCreating']);
        $this->dispatch('category-saved');
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): void
    {
        $category->delete();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.menu.categories', [
            'categories' => Category::orderBy('sort_order')->paginate(10),
        ]);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="p-6 space-y-4">
            <div class="h-8 bg-neutral-200 dark:bg-neutral-700 rounded w-1/4 animate-pulse"></div>
            <div class="h-64 bg-neutral-100 dark:bg-neutral-800 rounded animate-pulse"></div>
        </div>
        HTML;
    }
}
