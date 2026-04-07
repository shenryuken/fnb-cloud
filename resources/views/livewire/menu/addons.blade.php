<div class="flex flex-col gap-6 p-4 md:p-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Add-ons &amp; Groups</flux:heading>
            <flux:subheading>Create customisable options and modifiers for your menu.</flux:subheading>
        </div>
        <div class="flex gap-3">
            <flux:button wire:click="createGroup" variant="ghost" icon="plus">
                New Group
            </flux:button>
            <flux:button wire:click="createItem" variant="primary" icon="plus">
                Standalone Add-on
            </flux:button>
        </div>
    </div>

    {{-- Group Form --}}
    @if($isCreatingGroup || $editingGroup)
        <flux:card>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center shrink-0">
                        <flux:icon.rectangle-group class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ $editingGroup ? 'Update Group' : 'New Add-on Group' }}</flux:heading>
                        <flux:subheading>Define selection rules and naming.</flux:subheading>
                    </div>
                </div>
                <flux:button wire:click="$set('isCreatingGroup', false); $set('editingGroup', null)" variant="ghost" icon="x-mark" />
            </div>

            <form wire:submit.prevent="saveGroup">
                <div class="grid md:grid-cols-2 gap-5 mb-5">
                    <flux:field>
                        <flux:label>Group Name</flux:label>
                        <flux:input wire:model="group_name" placeholder="e.g. Select your toppings" />
                        <flux:error name="group_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Description <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                        <flux:input wire:model="group_description" placeholder="Brief group description..." />
                    </flux:field>

                    <flux:field>
                        <flux:label>Minimum Options</flux:label>
                        <flux:input type="number" wire:model="min_select" placeholder="0" />
                        <flux:description>Set 0 for optional</flux:description>
                        <flux:error name="min_select" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Maximum Options</flux:label>
                        <flux:input type="number" wire:model="max_select" placeholder="1" />
                        <flux:description>Set 0 for unlimited</flux:description>
                        <flux:error name="max_select" />
                    </flux:field>
                </div>

                <flux:separator class="mb-5" />

                <div class="flex justify-end gap-3">
                    <flux:button type="button" wire:click="$set('isCreatingGroup', false); $set('editingGroup', null)" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">{{ $editingGroup ? 'Update Group' : 'Create Group' }}</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    {{-- Item Form --}}
    @if($isCreatingItem || $editingItem)
        <flux:card>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center shrink-0">
                        <flux:icon.plus-circle class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ $editingItem ? 'Update Add-on' : 'New Add-on Option' }}</flux:heading>
                        <flux:subheading>Create an individual choice or extra for your products.</flux:subheading>
                    </div>
                </div>
                <flux:button wire:click="$set('isCreatingItem', false); $set('editingItem', null)" variant="ghost" icon="x-mark" />
            </div>

            <form wire:submit.prevent="saveItem">
                <div class="grid md:grid-cols-2 gap-5 mb-5">
                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="name" placeholder="e.g. Extra Cheese" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Price</flux:label>
                        <flux:input type="number" step="0.01" wire:model="price" placeholder="0.00" />
                        <flux:error name="price" />
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label>Group <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                        <flux:select wire:model="addon_group_id">
                            <flux:select.option value="">Standalone (Global Extra)</flux:select.option>
                            @foreach(\App\Models\AddonGroup::all() as $g)
                                <flux:select.option value="{{ $g->id }}">{{ $g->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:description>Assigned items only appear with their group.</flux:description>
                    </flux:field>
                </div>

                <flux:separator class="mb-5" />

                <div class="flex justify-end gap-3">
                    <flux:button type="button" wire:click="$set('isCreatingItem', false); $set('editingItem', null)" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">{{ $editingItem ? 'Update Add-on' : 'Create Add-on' }}</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    {{-- Two Column Layout: Groups + Standalone --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        {{-- Modifier Groups --}}
        <div class="flex flex-col gap-4">
            <flux:heading size="sm" class="text-zinc-500 uppercase tracking-widest text-xs font-black">Modifier Groups</flux:heading>

            @forelse($groups as $group)
                <flux:card class="p-0 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                        <div>
                            <flux:text class="font-semibold">{{ $group->name }}</flux:text>
                            <div class="flex items-center gap-2 mt-1">
                                <flux:badge color="blue" size="sm">Min: {{ $group->min_select }}</flux:badge>
                                <flux:badge color="zinc" size="sm">Max: {{ $group->max_select }}</flux:badge>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:button size="sm" variant="ghost" icon="plus" wire:click="createItem({{ $group->id }})" />
                            <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="editGroup({{ $group->id }})" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteGroup({{ $group->id }})" wire:confirm="Delete group and all its items?" class="text-red-500 hover:text-red-600" />
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @forelse($group->items as $item)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="font-semibold text-sm">{{ $item->name }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="font-black text-sm text-blue-600">${{ number_format($item->price, 2) }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <flux:button size="xs" variant="ghost" icon="pencil-square" wire:click="editItem({{ $item->id }})" />
                                                <flux:button size="xs" variant="ghost" icon="trash" wire:click="deleteItem({{ $item->id }})" class="text-red-500 hover:text-red-600" />
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-sm text-zinc-400 italic">No options in this group.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </flux:card>
            @empty
                <flux:card class="py-16 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <flux:icon.rectangle-group class="w-10 h-10 text-zinc-300 dark:text-zinc-700" />
                        <flux:heading>No groups yet</flux:heading>
                        <flux:subheading>Create your first add-on group.</flux:subheading>
                    </div>
                </flux:card>
            @endforelse

            <div>{{ $groups->links() }}</div>
        </div>

        {{-- Standalone Add-ons --}}
        <div class="flex flex-col gap-4">
            <flux:heading size="sm" class="text-zinc-500 uppercase tracking-widest text-xs font-black">Standalone Add-ons</flux:heading>

                <flux:card class="p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Name</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Price</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($standaloneItems as $item)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="font-semibold">{{ $item->name }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-black text-blue-600">${{ number_format($item->price, 2) }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="editItem({{ $item->id }})" />
                                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteItem({{ $item->id }})" class="text-red-500 hover:text-red-600" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <flux:icon.tag class="w-10 h-10 text-zinc-300 dark:text-zinc-700" />
                                            <flux:heading>No standalone add-ons</flux:heading>
                                            <flux:subheading>Add global extras here.</flux:subheading>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </flux:card>
        </div>

    </div>

</div>
