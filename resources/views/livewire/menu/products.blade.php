<div class="flex flex-col gap-6 p-4 md:p-8 bg-neutral-50 dark:bg-neutral-950 min-h-full font-sans">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Menu Products</h2>
            <p class="text-neutral-500 font-medium">Create and manage your restaurant's digital menu</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-xl shadow-blue-500/20 hover:bg-blue-500 hover:shadow-blue-500/40 transition-all transform active:scale-95">
            <flux:icon.plus class="w-5 h-5" />
            Add New Product
        </button>
    </div>

    @if($isCreating || $editing)
        <div class="bg-white dark:bg-neutral-900 rounded-[3rem] border border-neutral-200 dark:border-neutral-800 shadow-2xl overflow-hidden animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="p-10 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center shadow-xl shadow-blue-500/20">
                        <flux:icon.package class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <h3 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">
                            {{ $editing ? 'Update Item' : 'New Menu Item' }}
                        </h3>
                        <p class="text-neutral-500 font-medium text-sm">Configure your product's profile, variants and addons</p>
                    </div>
                </div>
                <button wire:click="$set('isCreating', false); $set('editing', null)" class="p-3 rounded-full hover:bg-neutral-200 dark:hover:bg-neutral-800 transition-colors">
                    <flux:icon.x-mark class="w-6 h-6 text-neutral-400" />
                </button>
            </div>
            
            <form wire:submit.prevent="save" class="p-10 space-y-12">
                <div class="grid gap-12 md:grid-cols-2">
                    <div class="space-y-10">
                        <div class="space-y-6">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Basic Information</h4>
                            <div class="space-y-6">
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                        <flux:icon.pencil-square class="w-5 h-5" />
                                    </div>
                                    <input type="text" wire:model="name" 
                                        class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-lg" 
                                        placeholder="Item Name (e.g. Truffle Beef Burger)">
                                </div>
                                @error('name') <span class="text-red-500 text-xs font-bold ml-4">{{ $message }}</span> @enderror

                                <div class="p-5 rounded-[1.5rem] border border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/40">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Product Type</span>
                                            <span class="text-sm font-black text-neutral-700 dark:text-neutral-200">Ala Carte or Set</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click="$set('product_type', 'ala_carte')" class="px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest border transition-all
                                                {{ $product_type === 'ala_carte' ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/20' : 'bg-white/60 dark:bg-neutral-900/40 text-neutral-500 border-neutral-200 dark:border-neutral-800 hover:border-blue-500/40' }}">
                                                Ala Carte
                                            </button>
                                            <button type="button" wire:click="$set('product_type', 'set')" class="px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest border transition-all
                                                {{ $product_type === 'set' ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/20' : 'bg-white/60 dark:bg-neutral-900/40 text-neutral-500 border-neutral-200 dark:border-neutral-800 hover:border-blue-500/40' }}">
                                                Set
                                            </button>
                                        </div>
                                    </div>
                                    @error('product_type') <div class="text-red-500 text-xs font-bold mt-2">{{ $message }}</div> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                            <flux:icon.tag class="w-5 h-5" />
                                        </div>
                                        <select wire:model="category_id" 
                                            class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all appearance-none">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors font-black text-xl">$</div>
                                        <input type="number" step="0.01" wire:model="price" 
                                            class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-lg" 
                                            placeholder="Base Price">
                                    </div>
                                </div>

                                <div class="relative group">
                                    <div class="absolute left-5 top-5 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                        <flux:icon.document-text class="w-5 h-5" />
                                    </div>
                                    <textarea wire:model="description" rows="4" 
                                        class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                        placeholder="Describe the flavors, ingredients, etc."></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest ml-2">Image</div>
                                        <div class="p-5 rounded-[1.5rem] border-2 border-dashed border-neutral-200 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/40 space-y-4">
                                            <input type="file" wire:model="image" accept="image/*" class="block w-full text-sm font-bold text-neutral-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-blue-600 file:text-white hover:file:bg-blue-500">
                                            @error('image') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror

                                            <div class="flex items-center gap-4">
                                                <div class="w-16 h-16 rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 overflow-hidden flex items-center justify-center">
                                                    @if($image)
                                                        <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                                                    @elseif($image_url)
                                                        <img src="{{ $image_url }}" class="w-full h-full object-cover">
                                                    @else
                                                        <flux:icon.photo class="w-7 h-7 text-neutral-300 dark:text-neutral-700" />
                                                    @endif
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Optional</div>
                                                    <div class="text-xs font-bold text-neutral-600 dark:text-neutral-300">Upload image, otherwise POS can use color.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest ml-2">Color</div>
                                        <div class="p-5 rounded-[1.5rem] border border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/40 space-y-4">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Use Color Tile</span>
                                                <label class="inline-flex items-center gap-2">
                                                    <input type="checkbox" wire:model.live="use_tile_color" class="rounded border-neutral-300 dark:border-neutral-700">
                                                    <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">On</span>
                                                </label>
                                            </div>

                                            @if($use_tile_color)
                                                <div class="flex items-center gap-4">
                                                    <input type="color" wire:model.live="tile_color" class="w-16 h-16 rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-transparent p-1">
                                                    <div class="flex-1">
                                                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Tile Color</div>
                                                        <div class="text-xs font-bold text-neutral-600 dark:text-neutral-300">{{ $tile_color ?: 'Not set' }}</div>
                                                    </div>
                                                </div>
                                                @error('tile_color') <span class="text-red-500 text-xs font-bold ml-1">{{ $message }}</span> @enderror
                                            @else
                                                <div class="text-xs font-bold text-neutral-500">Disabled</div>
                                            @endif

                                            <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Used only when no image is set.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                        <flux:icon.sparkles class="w-5 h-5" />
                                    </div>
                                    <input type="text" wire:model="badge_text"
                                        class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-sm"
                                        placeholder="Badge (optional) e.g. Promotion, Season Promo">
                                </div>
                                @error('badge_text') <span class="text-red-500 text-xs font-bold ml-4">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="space-y-10">
                        <div class="p-8 bg-neutral-50 dark:bg-neutral-950/50 rounded-[2.5rem] border-2 border-dashed border-neutral-200 dark:border-neutral-800 space-y-8">
                            <div class="flex items-center justify-between px-2">
                                <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Sizing & Variations</h4>
                                <button type="button" wire:click="addVariant" class="text-[10px] font-black text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 px-3 py-1.5 rounded-full transition-all flex items-center gap-1 uppercase tracking-widest border border-blue-100 dark:border-blue-900/50">
                                    <flux:icon.plus class="w-3 h-3" />
                                    Add Size
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                @foreach($variants as $index => $variant)
                                    <div class="flex gap-4 items-center group/item animate-in slide-in-from-right-4 duration-300">
                                        <div class="flex-1 relative group/input">
                                            <input type="text" wire:model="variants.{{ $index }}.name" placeholder="Size (e.g. Jumbo)" 
                                                class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-4 text-sm font-black focus:ring-4 focus:ring-blue-500/10 transition-all">
                                        </div>
                                        <div class="relative w-24 group/input">
                                            <input type="text" wire:model="variants.{{ $index }}.receipt_label" placeholder="Label (D)" 
                                                class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-4 text-sm font-black focus:ring-4 focus:ring-blue-500/10 transition-all text-center uppercase">
                                        </div>
                                        <div class="relative w-36 group/input">
                                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-300 font-black text-xs">$</div>
                                            <input type="number" step="0.01" wire:model="variants.{{ $index }}.price" placeholder="Price" 
                                                class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-4 pl-8 text-sm font-black focus:ring-4 focus:ring-blue-500/10 transition-all">
                                        </div>
                                        <button type="button" wire:click="removeVariant({{ $index }})" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-red-50 dark:bg-red-900/10 text-red-500 hover:bg-red-500 hover:text-white transition-all duration-200">
                                            <flux:icon.trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endforeach
                                @if(empty($variants))
                                    <div class="flex flex-col items-center justify-center py-10 text-neutral-300 dark:text-neutral-700">
                                        <flux:icon.arrows-right-left class="w-10 h-10 mb-2 opacity-20" />
                                        <p class="text-[10px] font-black uppercase tracking-[0.2em]">No variations configured</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($product_type === 'set')
                            <div class="p-8 bg-neutral-50 dark:bg-neutral-950/50 rounded-[2.5rem] border-2 border-dashed border-neutral-200 dark:border-neutral-800 space-y-6">
                                <div class="flex items-center justify-between px-2">
                                    <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Set Builder</h4>
                                    <button type="button" wire:click="addSetGroup" class="text-[10px] font-black text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 px-3 py-1.5 rounded-full transition-all flex items-center gap-1 uppercase tracking-widest border border-blue-100 dark:border-blue-900/50">
                                        <flux:icon.plus class="w-3 h-3" />
                                        Add Group
                                    </button>
                                </div>

                                @error('set_groups') <div class="text-red-500 text-xs font-bold px-2">{{ $message }}</div> @enderror

                                <div class="space-y-6">
                                    @foreach($set_groups as $gIndex => $group)
                                        <div class="p-6 rounded-[1.75rem] bg-white dark:bg-neutral-900/40 border border-neutral-100 dark:border-neutral-800 space-y-4">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="flex-1 space-y-2">
                                                    <div class="text-[9px] font-black text-neutral-400 uppercase tracking-widest px-1">Group Name</div>
                                                    <input type="text" wire:model.live="set_groups.{{ $gIndex }}.name" placeholder="e.g. Choose Drink"
                                                        class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-4 text-sm font-black focus:ring-4 focus:ring-blue-500/10 transition-all">
                                                    @error("set_groups.$gIndex.name") <div class="text-red-500 text-xs font-bold px-1">{{ $message }}</div> @enderror
                                                </div>
                                                <button type="button" wire:click="removeSetGroup({{ $gIndex }})" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-red-50 dark:bg-red-900/10 text-red-500 hover:bg-red-500 hover:text-white transition-all duration-200">
                                                    <flux:icon.trash class="w-4 h-4" />
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-3 gap-3">
                                                <div class="space-y-1">
                                                    <div class="text-[9px] font-black text-neutral-400 uppercase tracking-widest px-1">Min</div>
                                                    <input type="number" wire:model.live="set_groups.{{ $gIndex }}.min_select"
                                                        class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-3 font-black text-center tabular-nums focus:ring-4 focus:ring-blue-500/10">
                                                </div>
                                                <div class="space-y-1">
                                                    <div class="text-[9px] font-black text-neutral-400 uppercase tracking-widest px-1">Max</div>
                                                    <input type="number" wire:model.live="set_groups.{{ $gIndex }}.max_select"
                                                        class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-3 font-black text-center tabular-nums focus:ring-4 focus:ring-blue-500/10">
                                                </div>
                                                <div class="space-y-1">
                                                    <div class="text-[9px] font-black text-neutral-400 uppercase tracking-widest px-1">Order</div>
                                                    <input type="number" wire:model.live="set_groups.{{ $gIndex }}.sort_order"
                                                        class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-3 font-black text-center tabular-nums focus:ring-4 focus:ring-blue-500/10">
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between pt-2">
                                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Choices</span>
                                                <button type="button" wire:click="addSetGroupItem({{ $gIndex }})" class="text-[10px] font-black text-blue-600 hover:underline uppercase tracking-widest">
                                                    + Add Item
                                                </button>
                                            </div>

                                            <div class="space-y-3">
                                                @foreach(($group['items'] ?? []) as $iIndex => $item)
                                                    <div class="grid grid-cols-12 gap-3 items-center">
                                                        <div class="col-span-7">
                                                            <select wire:model.live="set_groups.{{ $gIndex }}.items.{{ $iIndex }}.product_id"
                                                                class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-3 font-black focus:ring-4 focus:ring-blue-500/10 transition-all">
                                                                <option value="">Select product</option>
                                                                @foreach($allProducts as $p)
                                                                    <option value="{{ $p->id }}">{{ $p->name }} (${{ number_format((float) $p->price, 2) }})</option>
                                                                @endforeach
                                                            </select>
                                                            @error("set_groups.$gIndex.items.$iIndex.product_id") <div class="text-red-500 text-xs font-bold px-1 mt-1">{{ $message }}</div> @enderror
                                                        </div>
                                                        <div class="col-span-3">
                                                            <div class="relative">
                                                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-300 font-black text-xs">$</div>
                                                                <input type="number" step="0.01" wire:model.live="set_groups.{{ $gIndex }}.items.{{ $iIndex }}.extra_price"
                                                                    class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-3 pl-7 font-black text-right tabular-nums focus:ring-4 focus:ring-blue-500/10 transition-all"
                                                                    placeholder="0.00">
                                                            </div>
                                                        </div>
                                                        <div class="col-span-1">
                                                            <input type="number" wire:model.live="set_groups.{{ $gIndex }}.items.{{ $iIndex }}.sort_order"
                                                                class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-3 font-black text-center tabular-nums focus:ring-4 focus:ring-blue-500/10">
                                                        </div>
                                                        <div class="col-span-1 flex justify-end">
                                                            <button type="button" wire:click="removeSetGroupItem({{ $gIndex }}, {{ $iIndex }})" class="w-10 h-10 rounded-2xl bg-red-50 dark:bg-red-900/10 text-red-500 hover:bg-red-500 hover:text-white transition-all">
                                                                <flux:icon.x-mark class="w-4 h-4 mx-auto" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach

                                    @if(empty($set_groups))
                                        <div class="flex flex-col items-center justify-center py-10 text-neutral-300 dark:text-neutral-700">
                                            <flux:icon.squares-plus class="w-10 h-10 mb-2 opacity-20" />
                                            <p class="text-[10px] font-black uppercase tracking-[0.2em]">No set groups configured</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center justify-between p-8 bg-neutral-50 dark:bg-neutral-950/50 rounded-[2rem] border border-neutral-100 dark:border-neutral-800">
                            <div class="flex items-center gap-4">
                                <label class="relative inline-flex items-center cursor-pointer group">
                                    <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                    <div class="w-14 h-8 bg-neutral-200 peer-focus:outline-none rounded-full peer dark:bg-neutral-800 peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600 shadow-inner"></div>
                                    <span class="ml-4 text-sm font-black text-neutral-700 dark:text-neutral-300 uppercase tracking-widest">Active Status</span>
                                </label>
                            </div>
                            
                            <div class="flex items-center gap-4">
                                <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Display Priority</label>
                                <input type="number" wire:model="sort_order" 
                                    class="w-20 text-center rounded-2xl border-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 p-3 font-black focus:ring-4 focus:ring-blue-500/10 transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customizations -->
                <div class="grid md:grid-cols-2 gap-12 pt-12 border-t border-neutral-100 dark:border-neutral-800">
                    <div class="space-y-6">
                        <div class="flex items-center justify-between px-2">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Available Add-on Groups</h4>
                            <a href="{{ route('manage.addons.index') }}" class="text-[10px] font-black text-blue-600 hover:underline uppercase tracking-widest">Configure Groups</a>
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            @foreach(\App\Models\AddonGroup::all() as $group)
                                <label class="group relative flex items-center gap-5 p-6 rounded-[1.5rem] border-2 cursor-pointer transition-all duration-300
                                    {{ in_array($group->id, $selectedGroups) ? 'border-blue-600/30 bg-blue-50/50 dark:bg-blue-900/10 shadow-lg' : 'border-neutral-50 dark:border-neutral-800 hover:border-neutral-100 dark:hover:border-neutral-700' }}">
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" wire:model="selectedGroups" value="{{ $group->id }}" 
                                            class="w-6 h-6 rounded-lg border-2 border-neutral-200 dark:border-neutral-700 text-blue-600 focus:ring-4 focus:ring-blue-500/10 transition-all">
                                    </div>
                                    <div class="flex-1">
                                        <span class="block font-black text-neutral-800 dark:text-neutral-200">{{ $group->name }}</span>
                                        <span class="text-[10px] text-neutral-400 uppercase font-black tracking-widest">{{ $group->items->count() }} OPTIONS</span>
                                    </div>
                                    @if(in_array($group->id, $selectedGroups))
                                        <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2">Standalone Extras</h4>
                        <div class="grid grid-cols-1 gap-3">
                            @foreach(\App\Models\ProductAddon::whereNull('addon_group_id')->get() as $addon)
                                <label class="group relative flex items-center gap-5 p-6 rounded-[1.5rem] border-2 cursor-pointer transition-all duration-300
                                    {{ in_array($addon->id, $selectedStandaloneAddons) ? 'border-blue-600/30 bg-blue-50/50 dark:bg-blue-900/10 shadow-lg' : 'border-neutral-50 dark:border-neutral-800 hover:border-neutral-100 dark:hover:border-neutral-700' }}">
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" wire:model="selectedStandaloneAddons" value="{{ $addon->id }}" 
                                            class="w-6 h-6 rounded-lg border-2 border-neutral-200 dark:border-neutral-700 text-blue-600 focus:ring-4 focus:ring-blue-500/10 transition-all">
                                    </div>
                                    <div class="flex-1">
                                        <span class="block font-black text-neutral-800 dark:text-neutral-200">{{ $addon->name }}</span>
                                        <span class="text-[10px] text-blue-600 font-black tracking-widest uppercase">+${{ number_format($addon->price, 2) }}</span>
                                    </div>
                                    @if(in_array($addon->id, $selectedStandaloneAddons))
                                        <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-10 border-t border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 -mx-10 -mb-10 p-10">
                    <button type="button" wire:click="$set('isCreating', false); $set('editing', null)" class="px-8 py-5 rounded-[2rem] font-black text-neutral-500 hover:text-neutral-800 transition-colors uppercase tracking-widest text-xs">Discard</button>
                    <button type="submit" class="px-12 py-5 rounded-[2rem] bg-blue-600 hover:bg-blue-500 text-white font-black shadow-2xl shadow-blue-500/30 hover:shadow-blue-500/50 transition-all transform active:scale-95 uppercase tracking-widest text-xs flex items-center justify-center gap-2 group">
                        <flux:icon.check-circle class="w-5 h-5 group-hover:scale-110 transition-transform" />
                        {{ $editing ? 'Update Menu Item' : 'Launch Product' }}
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
                        <th class="px-8 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Product</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest text-center">Order</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Category</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Price</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    @forelse($products as $product)
                        <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center overflow-hidden border border-neutral-200 dark:border-neutral-700">
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}" class="w-full h-full object-cover">
                                        @elseif($product->tile_color)
                                            <div class="w-full h-full flex items-center justify-center" style="background-color: {{ $product->tile_color }};">
                                                <span class="text-white font-black text-sm">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                                            </div>
                                        @else
                                            <flux:icon.package class="w-6 h-6 text-neutral-300 dark:text-neutral-600" />
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-black text-neutral-800 dark:text-neutral-100 tracking-tight">{{ $product->name }}</div>
                                        @if($product->badge_text)
                                            <div class="mt-1 inline-flex items-center px-2.5 py-1 rounded-full bg-blue-600 text-white text-[9px] font-black uppercase tracking-widest">
                                                {{ $product->badge_text }}
                                            </div>
                                        @endif
                                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-tighter">ID: #{{ $product->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="text-xs font-black text-neutral-400 bg-neutral-50 dark:bg-neutral-800 px-2.5 py-1 rounded-lg border border-neutral-100 dark:border-neutral-700">
                                    {{ $product->sort_order }}
                                </span>
                            </td>
                            <td class="px-6 py-6">
                                <span class="text-xs font-bold text-neutral-600 dark:text-neutral-400">{{ $product->category->name }}</span>
                            </td>
                            <td class="px-6 py-6 font-black text-blue-600 dark:text-blue-400">
                                ${{ number_format($product->price, 2) }}
                            </td>
                            <td class="px-6 py-6">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider
                                    {{ $product->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $product->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" wire:click="duplicateWithVariants({{ $product->id }})" class="px-3 py-2 rounded-xl bg-neutral-50 hover:bg-amber-50 dark:bg-neutral-800 dark:hover:bg-amber-900/20 text-neutral-500 hover:text-amber-600 transition-all border border-neutral-100 dark:border-neutral-700 hover:border-amber-100 dark:hover:border-amber-900/50 text-[10px] font-black uppercase tracking-widest">
                                        Copy + Var
                                    </button>
                                    <button type="button" wire:click="duplicateWithoutVariants({{ $product->id }})" class="px-3 py-2 rounded-xl bg-neutral-50 hover:bg-amber-50 dark:bg-neutral-800 dark:hover:bg-amber-900/20 text-neutral-500 hover:text-amber-600 transition-all border border-neutral-100 dark:border-neutral-700 hover:border-amber-100 dark:hover:border-amber-900/50 text-[10px] font-black uppercase tracking-widest">
                                        Copy
                                    </button>
                                    <button wire:click="edit({{ $product->id }})" class="p-2.5 rounded-xl bg-neutral-50 hover:bg-blue-50 dark:bg-neutral-800 dark:hover:bg-blue-900/20 text-neutral-400 hover:text-blue-600 transition-all border border-neutral-100 dark:border-neutral-700 hover:border-blue-100 dark:hover:border-blue-900/50">
                                        <flux:icon.pencil-square class="w-4 h-4" />
                                    </button>
                                    <button wire:click="delete({{ $product->id }})" wire:confirm="Permanently delete this product?" class="p-2.5 rounded-xl bg-neutral-50 hover:bg-red-50 dark:bg-neutral-800 dark:hover:bg-red-900/20 text-neutral-400 hover:text-red-600 transition-all border border-neutral-100 dark:border-neutral-700 hover:border-red-100 dark:hover:border-red-900/50">
                                        <flux:icon.trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center gap-4 max-w-xs mx-auto">
                                    <div class="w-20 h-20 rounded-full bg-neutral-50 dark:bg-neutral-900 flex items-center justify-center">
                                        <flux:icon.package class="w-10 h-10 text-neutral-200 dark:text-neutral-800" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">No products added</h3>
                                        <p class="text-sm text-neutral-500">Start by creating your first menu item above.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
            <div class="px-8 py-6 bg-neutral-50/50 dark:bg-neutral-800/50 border-t border-neutral-100 dark:border-neutral-800">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
