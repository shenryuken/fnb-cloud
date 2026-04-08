<div class="p-6 space-y-6">
    {{-- Header --}}
    <flux:header>
        <flux:heading size="xl">Menu Products</flux:heading>
        <flux:subheading>Create and manage your restaurant's digital menu</flux:subheading>

        <flux:separator />

        <flux:button wire:click="create" icon="plus" variant="primary">Add New Product</flux:button>
    </flux:header>

    {{-- Product Form Modal --}}
    <flux:modal name="product-form" wire:model="isCreating" class="w-full max-w-5xl space-y-6">
        <div class="flex items-center gap-3">
            <flux:icon.package class="w-8 h-8 text-blue-600" />
            <div>
                <flux:heading size="lg">{{ $editing ? 'Update Item' : 'New Menu Item' }}</flux:heading>
                <flux:subheading>Configure your product's profile, variants and addons</flux:subheading>
            </div>
        </div>

        <flux:separator />

        <form wire:submit.prevent="save" class="space-y-8">
            <div class="grid md:grid-cols-2 gap-6">
                {{-- Left Column: Basic Information --}}
                <div class="space-y-6">
                    <flux:heading size="md" class="text-zinc-400">Basic Information</flux:heading>

                    <flux:input wire:model="name" label="Product Name" placeholder="e.g. Truffle Beef Burger" icon="pencil-square" />
                    @error('name') <flux:error>{{ $message }}</flux:error> @enderror

                    <flux:fieldset>
                        <flux:legend>Product Type</flux:legend>
                        <flux:radio.group wire:model.live="product_type">
                            <flux:radio value="ala_carte" label="Ala Carte" />
                            <flux:radio value="set" label="Set" />
                        </flux:radio.group>
                        @error('product_type') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:fieldset>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:select wire:model="category_id" label="Category" placeholder="Select category" icon="tag">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input wire:model="price" type="number" step="0.01" label="Base Price" placeholder="0.00" icon="currency-dollar" />
                    </div>

                    <flux:textarea wire:model="description" label="Description" rows="4" placeholder="Describe the flavors, ingredients, etc." />

                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Image (Optional)</flux:label>
                            <flux:input type="file" wire:model="image" accept="image/*" />
                            @error('image') <flux:error>{{ $message }}</flux:error> @enderror
                            
                            @if($image)
                                <div class="mt-2 w-16 h-16 rounded-lg border overflow-hidden">
                                    <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                                </div>
                            @elseif($image_url)
                                <div class="mt-2 w-16 h-16 rounded-lg border overflow-hidden">
                                    <img src="{{ $image_url }}" class="w-full h-full object-cover">
                                </div>
                            @endif
                        </flux:field>

                        <flux:field>
                            <flux:label>Tile Color</flux:label>
                            <flux:checkbox wire:model.live="use_tile_color" label="Use color tile" />
                            @if($use_tile_color)
                                <flux:input type="color" wire:model.live="tile_color" class="mt-2" />
                                <flux:description>{{ $tile_color ?: 'Not set' }}</flux:description>
                                @error('tile_color') <flux:error>{{ $message }}</flux:error> @enderror
                            @else
                                <flux:description>Used only when no image is set</flux:description>
                            @endif
                        </flux:field>
                    </div>

                    <flux:input wire:model="badge_text" label="Badge (Optional)" placeholder="e.g. Promotion, Season Promo" icon="sparkles" />
                    @error('badge_text') <flux:error>{{ $message }}</flux:error> @enderror
                </div>

                {{-- Right Column: Variants & Settings --}}
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <flux:heading size="md" class="text-zinc-300">Sizing & Variations</flux:heading>
                        <flux:button wire:click="addVariant" size="sm" icon="plus" variant="primary">Add Size</flux:button>
                    </div>

                    <div class="space-y-3">
                        @foreach($variants as $index => $variant)
                            <flux:card class="p-4 bg-zinc-800/50 border-zinc-700">
                                <div class="flex items-start gap-3">
                                    <div class="flex-1 grid grid-cols-3 gap-3">
                                        <flux:input wire:model="variants.{{ $index }}.name" label="Size Name" placeholder="e.g. Regular, Large" />
                                        <flux:input wire:model="variants.{{ $index }}.receipt_label" label="Receipt Label" placeholder="e.g. L, XL" />
                                        <flux:input wire:model="variants.{{ $index }}.price" type="number" step="0.01" label="Price" placeholder="0.00" icon="currency-dollar" />
                                    </div>
                                    <flux:button wire:click="removeVariant({{ $index }})" icon="trash" variant="danger" size="sm" class="mt-6" />
                                </div>
                            </flux:card>
                        @endforeach
                        @if(empty($variants))
                            <div class="flex flex-col items-center justify-center py-6 text-center border-2 border-dashed border-zinc-700 rounded-xl">
                                <flux:icon.arrows-right-left class="w-8 h-8 text-zinc-500 mb-2" />
                                <flux:text size="sm" class="text-zinc-500">No size variations yet</flux:text>
                                <flux:text size="xs" class="text-zinc-600">Click "Add Size" to create options</flux:text>
                            </div>
                        @endif
                    </div>

                    {{-- Active Status & Display Priority --}}
                    <flux:separator />
                    
                    <div class="space-y-4">
                        <flux:heading size="md" class="text-zinc-300">Settings</flux:heading>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Active Status</flux:label>
                                <flux:switch wire:model.live="is_active" />
                                <flux:description>
                                    @if($is_active)
                                        <span class="text-green-400 font-semibold">Visible in POS</span>
                                    @else
                                        <span class="text-red-400 font-semibold">Hidden from POS</span>
                                    @endif
                                </flux:description>
                            </flux:field>

                            <flux:input wire:model="sort_order" type="number" label="Display Order" placeholder="1" />
                        </div>
                        @error('is_active') <flux:error>{{ $message }}</flux:error> @enderror
                        @error('sort_order') <flux:error>{{ $message }}</flux:error> @enderror
                    </div>

                    @if($product_type === 'set')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <flux:heading size="md" class="text-zinc-400">Set Builder</flux:heading>
                                <flux:button wire:click="addSetGroup" size="sm" icon="plus" variant="ghost">Add Group</flux:button>
                            </div>

                            @error('set_groups') <flux:error>{{ $message }}</flux:error> @enderror

                            <div class="space-y-4">
                                @foreach($set_groups as $gIndex => $group)
                                    <flux:card class="space-y-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <flux:input wire:model.live="set_groups.{{ $gIndex }}.name" placeholder="Group Name (e.g. Choose Drink)" class="flex-1" />
                                            <flux:button wire:click="removeSetGroup({{ $gIndex }})" icon="trash" variant="danger" square />
                                        </div>
                                        @error("set_groups.$gIndex.name") <flux:error>{{ $message }}</flux:error> @enderror

                                        <div class="grid grid-cols-3 gap-3">
                                            <flux:input wire:model.live="set_groups.{{ $gIndex }}.min_select" type="number" label="Min" />
                                            <flux:input wire:model.live="set_groups.{{ $gIndex }}.max_select" type="number" label="Max" />
                                            <flux:input wire:model.live="set_groups.{{ $gIndex }}.sort_order" type="number" label="Order" />
                                        </div>

                                        <div class="flex items-center justify-between pt-2">
                                            <flux:heading size="sm" class="text-zinc-300">Choices</flux:heading>
                                            <flux:button wire:click="addSetGroupItem({{ $gIndex }})" size="sm" icon="plus" variant="primary">Add Item</flux:button>
                                        </div>

                                        <div class="space-y-3">
                                            @foreach(($group['items'] ?? []) as $iIndex => $item)
                                                <flux:card class="p-4 bg-zinc-800/50 border-zinc-700">
                                                    <div class="flex items-start gap-3">
                                                        <div class="flex-1 space-y-3">
                                                            <div>
                                                                <flux:label class="text-xs text-zinc-400 mb-1">Product</flux:label>
                                                                <flux:select wire:model.live="set_groups.{{ $gIndex }}.items.{{ $iIndex }}.product_id" placeholder="Select product">
                                                                    @foreach($allProducts as $p)
                                                                        <option value="{{ $p->id }}">{{ $p->name }} - ${{ number_format((float) $p->price, 2) }}</option>
                                                                    @endforeach
                                                                </flux:select>
                                                                @error("set_groups.$gIndex.items.$iIndex.product_id") <flux:error class="text-xs mt-1">{{ $message }}</flux:error> @enderror
                                                            </div>
                                                            
                                                            <div class="grid grid-cols-2 gap-3">
                                                                <flux:input wire:model.live="set_groups.{{ $gIndex }}.items.{{ $iIndex }}.extra_price" type="number" step="0.01" label="Extra Price" placeholder="0.00" icon="currency-dollar" />
                                                                <flux:input wire:model.live="set_groups.{{ $gIndex }}.items.{{ $iIndex }}.sort_order" type="number" label="Display Order" placeholder="1" />
                                                            </div>
                                                        </div>

                                                        <flux:button wire:click="removeSetGroupItem({{ $gIndex }}, {{ $iIndex }})" icon="trash" variant="danger" size="sm" class="mt-6" />
                                                    </div>
                                                </flux:card>
                                            @endforeach

                                            @if(empty($group['items'] ?? []))
                                                <div class="flex flex-col items-center justify-center py-6 text-center border-2 border-dashed border-zinc-700 rounded-xl">
                                                    <flux:icon.shopping-bag class="w-8 h-8 text-zinc-500 mb-2" />
                                                    <flux:text size="sm" class="text-zinc-500">No items added yet</flux:text>
                                                    <flux:text size="xs" class="text-zinc-600">Click "Add Item" to get started</flux:text>
                                                </div>
                                            @endif
                                        </div>
                                    </flux:card>
                                @endforeach

                                @if(empty($set_groups))
                                    <flux:card class="flex flex-col items-center justify-center py-8 text-center">
                                        <flux:icon.squares-plus class="w-10 h-10 text-zinc-300 mb-2" />
                                        <flux:text size="sm" class="text-zinc-400">No set groups configured</flux:text>
                                    </flux:card>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Add-ons Section --}}
            <flux:separator />

            <div class="flex items-center justify-between mb-4">
                <flux:heading size="md" class="text-zinc-300">Product Add-ons</flux:heading>
                <flux:link href="{{ route('manage.addons.index') }}" variant="ghost" size="sm" icon="cog-6-tooth">Configure Groups</flux:link>
            </div>

            <flux:tab.group>
                <flux:tabs wire:model="activeAddonTab">
                    <flux:tab name="addon-groups">Available Add-on Groups</flux:tab>
                    <flux:tab name="standalone">Standalone Extras</flux:tab>
                </flux:tabs>

                <flux:tab.panel name="addon-groups">
                    <div class="space-y-2 mt-4">
                        @forelse(\App\Models\AddonGroup::all() as $group)
                            <flux:card class="p-3 hover:bg-zinc-800/30 transition-colors cursor-pointer" wire:key="addon-group-{{ $group->id }}">
                                <flux:checkbox wire:model.live="selectedGroups" value="{{ $group->id }}">
                                    <div class="flex items-center justify-between flex-1">
                                        <div>
                                            <div class="font-semibold text-sm">{{ $group->name }}</div>
                                            <flux:text size="xs" class="text-zinc-400">{{ $group->items->count() }} item(s) in this group</flux:text>
                                        </div>
                                        <flux:badge size="sm" color="zinc">{{ $group->items->count() }}</flux:badge>
                                    </div>
                                </flux:checkbox>
                            </flux:card>
                        @empty
                            <div class="flex flex-col items-center justify-center py-8 text-center border-2 border-dashed border-zinc-700 rounded-xl">
                                <flux:icon.squares-2x2 class="w-8 h-8 text-zinc-500 mb-2" />
                                <flux:text size="xs" class="text-zinc-500">No add-on groups available</flux:text>
                                <flux:text size="xs" class="text-zinc-600">Configure groups to get started</flux:text>
                            </div>
                        @endforelse
                    </div>
                </flux:tab.panel>

                <flux:tab.panel name="standalone">
                    <div class="space-y-2 mt-4">
                        @forelse(\App\Models\ProductAddon::whereNull('addon_group_id')->get() as $addon)
                            <flux:card class="p-3 hover:bg-zinc-800/30 transition-colors cursor-pointer" wire:key="standalone-addon-{{ $addon->id }}">
                                <flux:checkbox wire:model.live="selectedStandaloneAddons" value="{{ $addon->id }}">
                                    <div class="flex items-center justify-between flex-1">
                                        <div>
                                            <div class="font-semibold text-sm">{{ $addon->name }}</div>
                                            @if($addon->description)
                                                <flux:text size="xs" class="text-zinc-400">{{ $addon->description }}</flux:text>
                                            @endif
                                        </div>
                                        <flux:badge size="sm" color="blue">+${{ number_format($addon->price, 2) }}</flux:badge>
                                    </div>
                                </flux:checkbox>
                            </flux:card>
                        @empty
                            <div class="flex flex-col items-center justify-center py-8 text-center border-2 border-dashed border-zinc-700 rounded-xl">
                                <flux:icon.plus-circle class="w-8 h-8 text-zinc-500 mb-2" />
                                <flux:text size="sm" class="text-zinc-500">No standalone extras available</flux:text>
                                <flux:text size="xs" class="text-zinc-600">Add individual extras from settings</flux:text>
                            </div>
                        @endforelse
                    </div>
                </flux:tab.panel>
            </flux:tab.group>

            <flux:separator />

            <div class="flex justify-end gap-3">
                <flux:button wire:click="$set('isCreating', false); $set('editing', null)" variant="ghost">Discard</flux:button>
                <flux:button type="submit" variant="primary" icon="check-circle">
                    {{ $editing ? 'Update Menu Item' : 'Launch Product' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Search & Filters --}}
    <flux:card class="p-4">
        <div class="flex items-center gap-3">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search products by name..." icon="magnifying-glass" />
            </div>
            <flux:select wire:model.live="categoryFilter" placeholder="All Categories" class="w-48">
                <option value="">All Categories</option>
                @foreach(\App\Models\Category::orderBy('name')->get() as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="statusFilter" placeholder="All Status" class="w-40">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </flux:select>
        </div>
    </flux:card>

    {{-- Products Table --}}
    <flux:table :paginate="$products">
        <flux:table.columns>
            <flux:table.column>Product</flux:table.column>
            <flux:table.column class="text-center w-24">Order</flux:table.column>
            <flux:table.column>Category</flux:table.column>
            <flux:table.column class="text-right w-32">Price</flux:table.column>
            <flux:table.column class="text-center w-28">Status</flux:table.column>
            <flux:table.column class="text-right w-32">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($products as $product)
                <flux:table.row :key="$product->id" class="hover:bg-zinc-800/30 transition-colors">
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-xl overflow-hidden border-2 border-zinc-700 flex items-center justify-center shrink-0 shadow-sm">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" class="w-full h-full object-cover" alt="{{ $product->name }}">
                                @elseif($product->tile_color)
                                    <div class="w-full h-full flex items-center justify-center" style="background-color: {{ $product->tile_color }};">
                                        <span class="text-white font-black text-lg">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                                    </div>
                                @else
                                    <div class="w-full h-full bg-zinc-800 flex items-center justify-center">
                                        <flux:icon.cube class="w-7 h-7 text-zinc-400" />
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-sm truncate">{{ $product->name }}</div>
                                @if($product->description)
                                    <flux:text size="xs" class="text-zinc-400 truncate">{{ Str::limit($product->description, 40) }}</flux:text>
                                @endif
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="text-center">
                        <flux:badge size="sm" color="zinc" class="font-mono">{{ $product->sort_order }}</flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" color="blue" inset="top bottom">
                            {{ $product->category->name ?? 'Uncategorized' }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell variant="strong" class="text-right">
                        <span class="font-mono text-base">${{ number_format($product->price, 2) }}</span>
                    </flux:table.cell>

                    <flux:table.cell class="text-center">
                        @if($product->is_active)
                            <flux:badge size="sm" color="green" inset="top bottom">
                                <flux:icon.check-circle class="w-3.5 h-3.5" />
                                Active
                            </flux:badge>
                        @else
                            <flux:badge size="sm" color="red" inset="top bottom">
                                <flux:icon.x-circle class="w-3.5 h-3.5" />
                                Inactive
                            </flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="text-right">
                        <flux:dropdown position="left" align="top">
                            <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" square />
                            
                            <flux:menu>
                                <flux:menu.item icon="clipboard-document" wire:click="duplicateWithVariants({{ $product->id }})">
                                    Copy + Variants
                                </flux:menu.item>
                                <flux:menu.item icon="document-duplicate" wire:click="duplicateWithoutVariants({{ $product->id }})">
                                    Copy
                                </flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="pencil" wire:click="edit({{ $product->id }})">
                                    Edit
                                </flux:menu.item>
                                <flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $product->id }})" wire:confirm="Are you sure you want to delete this product?">
                                    Delete
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-12 text-zinc-400">
                        <flux:icon.inbox class="w-16 h-16 mx-auto mb-3 text-zinc-300" />
                        <div>No products found. Create your first menu item!</div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if($products->hasPages())
        <div class="flex justify-center">
            {{ $products->links() }}
        </div>
    @endif
</div>
