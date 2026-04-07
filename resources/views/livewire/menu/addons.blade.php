<div class="flex flex-col gap-6 p-4 md:p-8 bg-neutral-50 dark:bg-neutral-950 min-h-full font-sans">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Add-ons & Groups</h2>
            <p class="text-neutral-500 font-medium">Create customizable options and modifiers for your menu</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="createGroup" class="inline-flex items-center gap-2 rounded-2xl bg-neutral-900 dark:bg-white text-white dark:text-black px-6 py-3 text-sm font-black shadow-xl transition-all transform active:scale-95">
                <flux:icon.plus class="w-5 h-5" />
                New Group
            </button>
            <button wire:click="createItem" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-xl shadow-blue-500/20 hover:bg-blue-500 hover:shadow-blue-500/40 transition-all transform active:scale-95">
                <flux:icon.plus class="w-5 h-5" />
                Standalone Add-on
            </button>
        </div>
    </div>

    @if($isCreatingGroup || $editingGroup)
        <div class="bg-white dark:bg-neutral-900 rounded-[3rem] border border-neutral-200 dark:border-neutral-800 shadow-2xl overflow-hidden animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="p-10 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center shadow-xl shadow-blue-500/20">
                        <flux:icon.rectangle-group class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <h3 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">{{ $editingGroup ? 'Update Group' : 'New Add-on Group' }}</h3>
                        <p class="text-neutral-500 font-medium text-sm">Define selection rules and naming for a set of options</p>
                    </div>
                </div>
                <button wire:click="$set('isCreatingGroup', false); $set('editingGroup', null)" class="p-3 rounded-full hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-400 transition-colors">
                    <flux:icon.x-mark class="w-6 h-6 text-neutral-400" />
                </button>
            </div>
            <form wire:submit.prevent="saveGroup" class="p-10 space-y-12">
                <div class="grid gap-12 md:grid-cols-2">
                    <div class="space-y-10">
                        <div class="space-y-6">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Group Configuration</h4>
                            <div class="space-y-6">
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                        <flux:icon.pencil-square class="w-5 h-5" />
                                    </div>
                                    <input type="text" wire:model="group_name" placeholder="Group Name (e.g. Select your toppings)" 
                                        class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-lg">
                                </div>
                                @error('group_name') <span class="text-red-500 text-xs font-bold ml-4">{{ $message }}</span> @enderror

                                <div class="relative group">
                                    <div class="absolute left-5 top-5 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                        <flux:icon.document-text class="w-5 h-5" />
                                    </div>
                                    <input type="text" wire:model="group_description" placeholder="Brief group description..." 
                                        class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-10">
                        <div class="p-8 bg-neutral-50 dark:bg-neutral-950/50 rounded-[2.5rem] border border-neutral-100 dark:border-neutral-800 space-y-8">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2 text-center">Selection Constraints</h4>
                            <div class="grid grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest ml-4">Minimum Options</label>
                                    <input type="number" wire:model="min_select" 
                                        class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-6 font-black text-4xl text-center focus:ring-4 focus:ring-blue-500/10 transition-all shadow-inner">
                                </div>
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest ml-4">Maximum Options</label>
                                    <input type="number" wire:model="max_select" 
                                        class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-6 font-black text-4xl text-center focus:ring-4 focus:ring-blue-500/10 transition-all shadow-inner">
                                </div>
                            </div>
                            <p class="text-[10px] text-neutral-400 text-center font-bold tracking-widest uppercase">Set 0 for optional, 1+ for required</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-10 border-t border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 -mx-10 -mb-10 p-10">
                    <button type="button" wire:click="$set('isCreatingGroup', false); $set('editingGroup', null)" class="px-8 py-5 rounded-[2rem] font-black text-neutral-500 hover:text-neutral-800 transition-colors uppercase tracking-widest text-xs">Discard</button>
                    <button type="submit" class="px-12 py-5 rounded-[2rem] bg-blue-600 hover:bg-blue-500 text-white font-black shadow-2xl shadow-blue-500/30 hover:shadow-blue-500/50 transition-all transform active:scale-95 uppercase tracking-widest text-xs flex items-center justify-center gap-2 group">
                        <flux:icon.check-circle class="w-5 h-5 group-hover:scale-110 transition-transform" />
                        {{ $editingGroup ? 'Update Group Config' : 'Launch Add-on Group' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if($isCreatingItem || $editingItem)
        <div class="bg-white dark:bg-neutral-900 rounded-[3rem] border border-neutral-200 dark:border-neutral-800 shadow-2xl overflow-hidden animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="p-10 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-600 flex items-center justify-center shadow-xl shadow-emerald-500/20">
                        <flux:icon.plus-circle class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <h3 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">{{ $editingItem ? 'Update Add-on' : 'New Add-on Option' }}</h3>
                        <p class="text-neutral-500 font-medium text-sm">Create an individual choice or extra for your products</p>
                    </div>
                </div>
                <button wire:click="$set('isCreatingItem', false); $set('editingItem', null)" class="p-3 rounded-full hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-400 transition-colors">
                    <flux:icon.x-mark class="w-6 h-6 text-neutral-400" />
                </button>
            </div>
            <form wire:submit.prevent="saveItem" class="p-10 space-y-12">
                <div class="grid gap-12 md:grid-cols-2">
                    <div class="space-y-10">
                        <div class="space-y-6">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Choice Details</h4>
                            <div class="space-y-6">
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                        <flux:icon.tag class="w-5 h-5" />
                                    </div>
                                    <input type="text" wire:model="name" placeholder="Add-on Name (e.g. Extra Cheese)" 
                                        class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-lg">
                                </div>
                                @error('name') <span class="text-red-500 text-xs font-bold ml-4">{{ $message }}</span> @enderror

                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors font-black text-xl">$</div>
                                    <input type="number" step="0.01" wire:model="price" 
                                        class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-lg" 
                                        placeholder="Option Price">
                                </div>
                                @error('price') <span class="text-red-500 text-xs font-bold ml-4">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="space-y-10">
                        <div class="p-8 bg-neutral-50 dark:bg-neutral-950/50 rounded-[2.5rem] border border-neutral-100 dark:border-neutral-800 space-y-8">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2 text-center">Group Assignment</h4>
                            <div class="space-y-6">
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                        <flux:icon.rectangle-group class="w-5 h-5" />
                                    </div>
                                    <select wire:model="addon_group_id" 
                                        class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all appearance-none text-neutral-700 dark:text-neutral-200">
                                        <option value="">Standalone (Global Extra)</option>
                                        @foreach(\App\Models\AddonGroup::all() as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <p class="text-[10px] text-neutral-400 text-center font-bold tracking-widest uppercase">Assigned items only appear with their group</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-10 border-t border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 -mx-10 -mb-10 p-10">
                    <button type="button" wire:click="$set('isCreatingItem', false); $set('editingItem', null)" class="px-8 py-5 rounded-[2rem] font-black text-neutral-500 hover:text-neutral-800 transition-colors uppercase tracking-widest text-xs">Discard</button>
                    <button type="submit" class="px-12 py-5 rounded-[2rem] bg-emerald-600 hover:bg-emerald-500 text-white font-black shadow-2xl shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all transform active:scale-95 uppercase tracking-widest text-xs flex items-center justify-center gap-2 group">
                        <flux:icon.check-circle class="w-5 h-5 group-hover:scale-110 transition-transform" />
                        {{ $editingItem ? 'Update Option' : 'Launch Add-on' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="space-y-6">
            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-widest ml-4">Modifier Groups</h4>
            @foreach($groups as $group)
                <div class="bg-white dark:bg-neutral-900 rounded-3xl border border-neutral-200 dark:border-neutral-800 shadow-xl overflow-hidden group/card hover:border-blue-500/50 transition-colors">
                    <div class="bg-neutral-50/50 dark:bg-neutral-800/50 px-8 py-6 flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800">
                        <div>
                            <h3 class="text-xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">{{ $group->name }}</h3>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 px-2 py-0.5 rounded-lg">Min: {{ $group->min_select }}</span>
                                <span class="text-[10px] font-black uppercase tracking-widest bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 px-2 py-0.5 rounded-lg">Max: {{ $group->max_select }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 opacity-0 group-hover/card:opacity-100 transition-opacity">
                            <button wire:click="createItem({{ $group->id }})" class="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-100 transition-colors" title="Add Option">
                                <flux:icon.plus class="w-4 h-4" />
                            </button>
                            <button wire:click="editGroup({{ $group->id }})" class="p-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-600 hover:bg-neutral-200 transition-colors" title="Edit Group">
                                <flux:icon.pencil-square class="w-4 h-4" />
                            </button>
                            <button wire:click="deleteGroup({{ $group->id }})" wire:confirm="Delete group and all its items?" class="p-2 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 transition-colors" title="Delete Group">
                                <flux:icon.trash class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <table class="w-full text-left border-collapse">
                            <tbody class="divide-y divide-neutral-50 dark:divide-neutral-800">
                                @forelse($group->items as $item)
                                    <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors group/row">
                                        <td class="px-4 py-3 font-bold text-neutral-700 dark:text-neutral-300 text-sm">{{ $item->name }}</td>
                                        <td class="px-4 py-3 text-sm font-black text-blue-600 dark:text-blue-400">${{ number_format($item->price, 2) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover/row:opacity-100 transition-opacity">
                                                <button wire:click="editItem({{ $item->id }})" class="p-1.5 text-neutral-400 hover:text-blue-600"><flux:icon.pencil-square class="w-3.5 h-3.5" /></button>
                                                <button wire:click="deleteItem({{ $item->id }})" class="p-1.5 text-neutral-400 hover:text-red-600"><flux:icon.trash class="w-3.5 h-3.5" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-8 py-8 text-center text-xs font-bold text-neutral-400 italic">No options in this group.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
            <div class="px-4">{{ $groups->links() }}</div>
        </div>

        <div class="space-y-6">
            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-widest ml-4">Standalone Add-ons</h4>
            <div class="bg-white dark:bg-neutral-900 rounded-3xl border border-neutral-200 dark:border-neutral-800 shadow-xl overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-neutral-50/50 dark:bg-neutral-800/50 border-b border-neutral-100 dark:border-neutral-800">
                        <tr>
                            <th class="px-8 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest">Name</th>
                            <th class="px-6 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest">Price</th>
                            <th class="px-8 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                        @forelse($standaloneItems as $item)
                            <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors group">
                                <td class="px-8 py-4 font-bold text-neutral-700 dark:text-neutral-300">{{ $item->name }}</td>
                                <td class="px-6 py-4 font-black text-blue-600 dark:text-blue-400">${{ number_format($item->price, 2) }}</td>
                                <td class="px-8 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="editItem({{ $item->id }})" class="p-2 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 text-neutral-400 hover:text-blue-600 transition-all">
                                            <flux:icon.pencil-square class="w-4 h-4" />
                                        </button>
                                        <button wire:click="deleteItem({{ $item->id }})" class="p-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-neutral-400 hover:text-red-600 transition-all">
                                            <flux:icon.trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-8 py-12 text-center text-sm text-neutral-400 font-medium">No standalone add-ons yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
