<div class="flex flex-col gap-6 p-4 md:p-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Menu Categories</flux:heading>
            <flux:subheading>Organise your menu by category.</flux:subheading>
        </div>
        <flux:button wire:click="create" variant="primary" icon="plus">
            New Category
        </flux:button>
    </div>

    {{-- Create / Edit Form --}}
    @if($isCreating || $editing)
        <flux:card>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center shrink-0">
                        <flux:icon.layers class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ $editing ? 'Update Category' : 'New Category' }}</flux:heading>
                        <flux:subheading>Fill in the category details below.</flux:subheading>
                    </div>
                </div>
                <flux:button wire:click="$set('isCreating', false); $set('editing', null)" variant="ghost" icon="x-mark" />
            </div>

            <form wire:submit.prevent="save">
                <div class="grid md:grid-cols-2 gap-5 mb-5">
                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="name" placeholder="e.g. Main Course" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Sort Order</flux:label>
                        <flux:input type="number" wire:model="sort_order" placeholder="0" />
                        <flux:error name="sort_order" />
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label>Description <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                        <flux:input wire:model="description" placeholder="Brief category description..." />
                        <flux:error name="description" />
                    </flux:field>
                </div>

                <div class="mb-5">
                    <flux:checkbox wire:model="is_active" label="Active on menu" />
                </div>

                <flux:separator class="mb-5" />

                <div class="flex justify-end gap-3">
                    <flux:button type="button" wire:click="$set('isCreating', false); $set('editing', null)" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    {{-- Table --}}
    <flux:card class="p-0 overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="py-3 px-4">Order</flux:table.column>
                <flux:table.column class="py-3 px-4">Category</flux:table.column>
                <flux:table.column class="py-3 px-4">Description</flux:table.column>
                <flux:table.column class="py-3 px-4 text-center">Status</flux:table.column>
                <flux:table.column class="py-3 px-4 text-right">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($categories as $category)
                    <flux:table.row>
                        <flux:table.cell class="py-3 px-4">
                            <flux:badge color="zinc" size="sm">{{ $category->sort_order }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="py-3 px-4">
                            <flux:text class="font-semibold">{{ $category->name }}</flux:text>
                            <flux:text size="sm" class="text-zinc-400">ID: #{{ $category->id }}</flux:text>
                        </flux:table.cell>

                        <flux:table.cell class="py-3 px-4">
                            <flux:text size="sm" class="text-zinc-500">{{ $category->description ?? '—' }}</flux:text>
                        </flux:table.cell>

                        <flux:table.cell class="py-3 px-4 text-center">
                            <flux:badge :color="$category->is_active ? 'green' : 'zinc'" size="sm">
                                {{ $category->is_active ? 'Active' : 'Hidden' }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $category->id }})" />
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $category->id }})" wire:confirm="Permanently delete this category?" class="text-red-500 hover:text-red-600" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-24 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon.layers class="w-10 h-10 text-zinc-300 dark:text-zinc-700" />
                                <flux:heading>No categories yet</flux:heading>
                                <flux:subheading>Create your first category to organise your menu.</flux:subheading>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

</div>
