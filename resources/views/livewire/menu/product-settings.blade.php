<div class="flex flex-col gap-6 p-4 md:p-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Product Settings</flux:heading>
            <flux:subheading>Bulk tools to manage products faster</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button :href="route('manage.products.index')" wire:navigate variant="ghost" icon="arrow-left">
                Back to Products
            </flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <flux:card class="p-0 overflow-hidden lg:col-span-1">
            <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <flux:heading size="lg">Sort Order</flux:heading>
                <flux:text size="sm" class="text-zinc-400">Apply a sorting rule to update sort_order</flux:text>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <flux:label>Mode</flux:label>
                    <flux:select wire:model.live="sortMode">
                        <flux:select.option value="sort_order">Normalize by Sort Order</flux:select.option>
                        <flux:select.option value="name_asc">Sort by Name (A-Z)</flux:select.option>
                        <flux:select.option value="name_desc">Sort by Name (Z-A)</flux:select.option>
                    </flux:select>
                </div>
                <div>
                    <flux:label>Category</flux:label>
                    <flux:select wire:model.live="sortCategoryId">
                        <flux:select.option value="">All Categories</flux:select.option>
                        @foreach($categoryOptions as $opt)
                            <flux:select.option value="{{ $opt['id'] }}">{{ $opt['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="pt-2">
                    <flux:button wire:click="applySortOrder" variant="primary" icon="arrows-up-down" class="w-full">
                        Apply Sorting
                    </flux:button>
                    <flux:text size="sm" class="text-zinc-500 mt-2">
                        Manual drag-and-drop sorting still stays on the Products page.
                    </flux:text>
                </div>
            </div>
        </flux:card>

        <flux:card class="p-0 overflow-hidden lg:col-span-2">
            <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <flux:heading size="lg">Bulk Management</flux:heading>
                <flux:text size="sm" class="text-zinc-400">Select products then apply actions</flux:text>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                    <div class="flex-1">
                        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search products..." icon="magnifying-glass" />
                    </div>
                    <flux:select wire:model.live="categoryFilter" placeholder="All Categories" class="w-full lg:w-56">
                        <flux:select.option value="">All Categories</flux:select.option>
                        @foreach($categoryOptions as $opt)
                            <flux:select.option value="{{ $opt['id'] }}">{{ $opt['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="statusFilter" placeholder="All Status" class="w-full lg:w-44">
                        <flux:select.option value="">All Status</flux:select.option>
                        <flux:select.option value="active">Active</flux:select.option>
                        <flux:select.option value="inactive">Inactive</flux:select.option>
                    </flux:select>
                </div>

                @php
                    $pageIds = collect($products->items())->pluck('id')->all();
                    $allOnPageSelected = !empty($pageIds) && empty(array_diff($pageIds, array_map('intval', $selectedProductIds)));
                @endphp

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:click="toggleSelectAllOnPage({{ json_encode($pageIds) }})"
                                @checked($allOnPageSelected)
                                class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-pink-600 focus:ring-pink-500 dark:bg-zinc-800"
                            />
                            <span class="text-sm text-zinc-500">Select all on page</span>
                        </label>
                        @if(count($selectedProductIds) > 0)
                            <flux:badge color="zinc">{{ count($selectedProductIds) }} selected</flux:badge>
                            <flux:button size="sm" wire:click="clearSelection" variant="ghost" icon="x-mark">
                                Clear
                            </flux:button>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="color" wire:model.live="bulkColor" class="h-9 w-12 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent" />
                        <flux:input wire:model.live="bulkColor" placeholder="#3B82F6" class="w-32" />
                        <flux:button size="sm" wire:click="applyBulkColor" variant="primary" icon="paint-brush">
                            Set Color
                        </flux:button>
                        <flux:button size="sm" wire:click="clearBulkColor" variant="ghost" icon="trash">
                            Clear Color
                        </flux:button>
                    </div>
                </div>

                <flux:separator />

                <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <table class="w-full text-sm text-left">
                        <thead class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900">
                            <tr>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest w-10"></th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Product</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Category</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">Order</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Color</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @forelse($products as $product)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3 px-4">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleSelectedProduct({{ $product->id }})"
                                            @checked(in_array((int) $product->id, array_map('intval', $selectedProductIds), true))
                                            class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-pink-600 focus:ring-pink-500 dark:bg-zinc-800"
                                        />
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 flex items-center justify-center shrink-0">
                                                @if($product->image_url)
                                                    <img src="{{ $product->image_url }}" class="w-full h-full object-cover" alt="{{ $product->name }}">
                                                @elseif($product->tile_color)
                                                    <div class="w-full h-full" style="background-color: {{ $product->tile_color }};"></div>
                                                @else
                                                    <div class="w-full h-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                                                        <flux:icon.cube class="w-4 h-4 text-zinc-400" />
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-zinc-800 dark:text-zinc-100 truncate">{{ $product->name }}</div>
                                                <div class="text-xs text-zinc-500 truncate">#{{ $product->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <flux:badge size="sm" color="blue">{{ $product->category->name ?? 'Uncategorized' }}</flux:badge>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <flux:badge size="sm" color="zinc">{{ $product->sort_order }}</flux:badge>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="inline-flex items-center gap-2 justify-end">
                                            @if($product->tile_color)
                                                <span class="inline-block w-4 h-4 rounded-full border border-zinc-200 dark:border-zinc-700" style="background-color: {{ $product->tile_color }};"></span>
                                                <span class="text-xs text-zinc-500 font-mono">{{ $product->tile_color }}</span>
                                            @else
                                                <span class="text-xs text-zinc-500">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 px-6 text-center text-zinc-500">No products found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                    <div class="pt-3">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </flux:card>
    </div>
</div>
