<div class="flex flex-col gap-6 p-4 md:p-8 bg-neutral-50 dark:bg-neutral-950 min-h-full font-sans">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Menu Categories</h2>
            <p class="text-neutral-500 font-medium">Organize your menu items into logical sections</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-xl shadow-blue-500/20 hover:bg-blue-500 hover:shadow-blue-500/40 transition-all transform active:scale-95">
            <flux:icon.plus class="w-5 h-5" />
            Add New Category
        </button>
    </div>

    @if($isCreating || $editing)
        <div class="bg-white dark:bg-neutral-900 rounded-[3rem] border border-neutral-200 dark:border-neutral-800 shadow-2xl overflow-hidden animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="p-10 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center shadow-xl shadow-blue-500/20">
                        <flux:icon.layers class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <h3 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">
                            {{ $editing ? 'Update Category' : 'New Category' }}
                        </h3>
                        <p class="text-neutral-500 font-medium text-sm">Define a new group for your menu items</p>
                    </div>
                </div>
                <button wire:click="$set('isCreating', false); $set('editing', null)" class="p-3 rounded-full hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-400 transition-colors">
                    <flux:icon.x-mark class="w-6 h-6 text-neutral-400" />
                </button>
            </div>
            
            <form wire:submit.prevent="save" class="p-10 space-y-12">
                <div class="grid gap-12 md:grid-cols-2">
                    <div class="space-y-10">
                        <div class="space-y-6">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Basic Details</h4>
                            <div class="space-y-6">
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                        <flux:icon.pencil-square class="w-5 h-5" />
                                    </div>
                                    <input type="text" wire:model="name" 
                                        class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-lg" 
                                        placeholder="Category Name (e.g. Main Course)">
                                </div>
                                @error('name') <span class="text-red-500 text-xs font-bold ml-4">{{ $message }}</span> @enderror

                                <div class="relative group">
                                    <div class="absolute left-5 top-5 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                        <flux:icon.document-text class="w-5 h-5" />
                                    </div>
                                    <textarea wire:model="description" rows="4" 
                                        class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                        placeholder="Brief description for this category..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-10">
                        <div class="p-8 bg-neutral-50 dark:bg-neutral-950/50 rounded-[2.5rem] border border-neutral-100 dark:border-neutral-800 space-y-8">
                            <div class="space-y-6">
                                <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2">Display Configuration</h4>
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest ml-4">Sort Priority</label>
                                    <div class="relative group">
                                        <input type="number" wire:model="sort_order" 
                                            class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-6 font-black text-4xl text-center focus:ring-4 focus:ring-blue-500/10 transition-all shadow-inner">
                                        <div class="absolute inset-y-0 left-6 flex items-center pointer-events-none opacity-20">
                                            <flux:icon.bars-arrow-down class="w-8 h-8" />
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-neutral-400 text-center font-bold tracking-widest uppercase">Lower numbers appear first in POS</p>
                                </div>

                                <div class="flex items-center justify-center pt-8 border-t border-neutral-100 dark:border-neutral-800">
                                    <label class="relative inline-flex items-center cursor-pointer group">
                                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                        <div class="w-14 h-8 bg-neutral-200 peer-focus:outline-none rounded-full peer dark:bg-neutral-800 peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600 shadow-inner"></div>
                                        <span class="ml-4 text-sm font-black text-neutral-700 dark:text-neutral-300 uppercase tracking-widest">Active on Menu</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-10 border-t border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 -mx-10 -mb-10 p-10">
                    <button type="button" wire:click="$set('isCreating', false); $set('editing', null)" class="px-8 py-5 rounded-[2rem] font-black text-neutral-500 hover:text-neutral-800 transition-colors uppercase tracking-widest text-xs">Discard</button>
                    <button type="submit" class="px-12 py-5 rounded-[2rem] bg-blue-600 hover:bg-blue-500 text-white font-black shadow-2xl shadow-blue-500/30 hover:shadow-blue-500/50 transition-all transform active:scale-95 uppercase tracking-widest text-xs flex items-center justify-center gap-2 group">
                        <flux:icon.check-circle class="w-5 h-5 group-hover:scale-110 transition-transform" />
                        {{ $editing ? 'Update Category' : 'Launch Category' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white dark:bg-neutral-900 rounded-3xl border border-neutral-200 dark:border-neutral-800 shadow-xl overflow-hidden">
        <div class="overflow-x-auto scrollbar-hide">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-neutral-50/50 dark:bg-neutral-800/50 border-b border-neutral-100 dark:border-neutral-800">
                        <th class="px-8 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Order</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Category</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Description</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-8 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    @forelse($categories as $category)
                        <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors group">
                            <td class="px-8 py-6">
                                <span class="text-xs font-black text-neutral-400 bg-neutral-50 dark:bg-neutral-800 px-3 py-1.5 rounded-xl border border-neutral-100 dark:border-neutral-700">
                                    {{ $category->sort_order }}
                                </span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="font-black text-neutral-800 dark:text-neutral-100 tracking-tight text-lg">{{ $category->name }}</div>
                                <div class="text-[10px] font-black text-neutral-400 uppercase tracking-tighter">ID: #{{ $category->id }}</div>
                            </td>
                            <td class="px-6 py-6">
                                <p class="text-sm text-neutral-500 font-medium max-w-xs truncate">{{ $category->description ?: 'No description provided.' }}</p>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider
                                    {{ $category->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $category->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="edit({{ $category->id }})" class="p-2.5 rounded-xl bg-neutral-50 hover:bg-blue-50 dark:bg-neutral-800 dark:hover:bg-blue-900/20 text-neutral-400 hover:text-blue-600 transition-all border border-neutral-100 dark:border-neutral-700 hover:border-blue-100 dark:hover:border-blue-900/50">
                                        <flux:icon.pencil-square class="w-4 h-4" />
                                    </button>
                                    <button wire:click="delete({{ $category->id }})" wire:confirm="Permanently delete this category?" class="p-2.5 rounded-xl bg-neutral-50 hover:bg-red-50 dark:bg-neutral-800 dark:hover:bg-red-900/20 text-neutral-400 hover:text-red-600 transition-all border border-neutral-100 dark:border-neutral-700 hover:border-red-100 dark:hover:border-red-900/50">
                                        <flux:icon.trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center gap-4 max-w-xs mx-auto">
                                    <div class="w-20 h-20 rounded-full bg-neutral-50 dark:bg-neutral-900 flex items-center justify-center">
                                        <flux:icon.layers class="w-10 h-10 text-neutral-200 dark:text-neutral-800" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">No categories yet</h3>
                                        <p class="text-sm text-neutral-500">Categories help you group products for faster navigation in the POS.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($categories->hasPages())
            <div class="px-8 py-6 bg-neutral-50/50 dark:bg-neutral-800/50 border-t border-neutral-100 dark:border-neutral-800">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
