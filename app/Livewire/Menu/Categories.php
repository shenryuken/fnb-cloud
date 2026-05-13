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

    public ?int $parent_id = null;
    public string $name = '';
    public string $description = '';
    public int $sort_order = 0;
    public bool $is_active = true;

    public ?Category $editing = null;
    public bool $isCreating = false;

    protected $rules = [
        'parent_id' => 'nullable|exists:categories,id',
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
        $this->reset(['parent_id', 'name', 'description', 'sort_order', 'is_active', 'editing']);
        $this->isCreating = true;
    }

    /**
     * Start editing a category.
     */
    public function edit(Category $category): void
    {
        $this->editing = $category;
        $this->parent_id = $category->parent_id;
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

        $parentId = $validated['parent_id'] ?? null;
        if ($this->editing && $parentId === (int) $this->editing->id) {
            $this->addError('parent_id', 'Parent category cannot be itself.');
            return;
        }

        if ($parentId) {
            $parent = Category::find($parentId);
            if (!$parent) {
                $this->addError('parent_id', 'Invalid parent category.');
                return;
            }
            if ($parent->parent_id) {
                $this->addError('parent_id', 'Only 2 levels supported. Pick a top-level parent.');
                return;
            }
        }

        if ($this->editing && $parentId) {
            $hasChildren = Category::where('parent_id', $this->editing->id)->exists();
            if ($hasChildren) {
                $this->addError('parent_id', 'This category has children. Move or delete children first.');
                return;
            }
        }

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            Category::create($validated);
        }

        $this->reset(['parent_id', 'name', 'description', 'sort_order', 'is_active', 'editing', 'isCreating']);
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
        $all = Category::orderBy('sort_order')->orderBy('name')->get();

        $parents = $all->whereNull('parent_id')->values();
        $childrenByParent = $all->whereNotNull('parent_id')->groupBy('parent_id');

        $flat = collect();
        foreach ($parents as $p) {
            $flat->push($p);
            foreach (($childrenByParent[$p->id] ?? collect())->sortBy('sort_order')->values() as $c) {
                $flat->push($c);
            }
        }

        return view('livewire.menu.categories', [
            'categories' => $flat,
            'parentOptions' => $parents,
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
