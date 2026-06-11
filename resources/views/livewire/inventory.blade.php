<div class="flex flex-col gap-6 p-4 md:p-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Inventory</flux:heading>
            <flux:subheading>Track stock levels and manage replenishment.</flux:subheading>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <flux:card>
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-blue-600 flex items-center justify-center shrink-0">
                    <flux:icon.cube class="w-5 h-5 text-white" />
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-400">Tracked Items</flux:text>
                    <flux:heading size="lg">{{ $trackedCount }}</flux:heading>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-amber-500 flex items-center justify-center shrink-0">
                    <flux:icon.exclamation-triangle class="w-5 h-5 text-white" />
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-400">Low Stock</flux:text>
                    <flux:heading size="lg">{{ $lowCount }}</flux:heading>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-red-500 flex items-center justify-center shrink-0">
                    <flux:icon.x-circle class="w-5 h-5 text-white" />
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-400">Out of Stock</flux:text>
                    <flux:heading size="lg">{{ $outCount }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search products..." icon="magnifying-glass" class="max-w-xs" />
        <flux:radio.group wire:model.live="filter" variant="segmented" size="sm">
            <flux:radio value="all" label="All" />
            <flux:radio value="tracked" label="Tracked" />
            <flux:radio value="low" label="Low" />
            <flux:radio value="out" label="Out" />
        </flux:radio.group>
    </div>

    {{-- Table --}}
    <flux:card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Product</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">Tracking</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">On Hand</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">Status</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($products as $product)
                        @php
                            $hasVariants = $product->variants->isNotEmpty();
                            $low = $product->isLowStock();
                            $out = $product->isOutOfStock();
                        @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="py-3 px-4">
                                <flux:text class="font-semibold">{{ $product->name }}</flux:text>
                                <flux:text size="sm" class="text-zinc-400">ID: #{{ $product->id }}{{ $hasVariants ? ' · '.$product->variants->count().' variants' : '' }}</flux:text>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <flux:switch wire:click="toggleTracking({{ $product->id }})" :checked="$product->track_stock" />
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if(!$product->track_stock)
                                    <flux:text class="text-zinc-400">—</flux:text>
                                @elseif($hasVariants)
                                    <flux:text size="sm" class="text-zinc-400">By variant</flux:text>
                                @else
                                    <flux:text class="font-semibold tabular-nums {{ $out ? 'text-red-500' : ($low ? 'text-amber-500' : '') }}">{{ $product->stock_quantity }}</flux:text>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if(!$product->track_stock)
                                    <flux:badge color="zinc" size="sm">Untracked</flux:badge>
                                @elseif($hasVariants)
                                    <flux:badge color="blue" size="sm">Variant-based</flux:badge>
                                @elseif($out)
                                    <flux:badge color="red" size="sm">Out of stock</flux:badge>
                                @elseif($low)
                                    <flux:badge color="amber" size="sm">Low stock</flux:badge>
                                @else
                                    <flux:badge color="green" size="sm">In stock</flux:badge>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if(!$hasVariants)
                                    <flux:button size="sm" variant="ghost" icon="plus-circle" wire:click="openAdjust({{ $product->id }})">Adjust</flux:button>
                                @else
                                    <flux:text size="sm" class="text-zinc-400">See variants below</flux:text>
                                @endif
                            </td>
                        </tr>
                        @if($hasVariants && $product->track_stock)
                            @foreach($product->variants as $variant)
                                @php
                                    $vLow = $variant->isLowStock();
                                    $vOut = $variant->isOutOfStock();
                                @endphp
                                <tr class="bg-zinc-50/50 dark:bg-zinc-800/20">
                                    <td class="py-2 px-4 pl-10">
                                        <div class="flex items-center gap-2">
                                            <flux:icon.chevron-right class="w-3.5 h-3.5 text-zinc-400" />
                                            <flux:text size="sm">{{ $variant->name }}</flux:text>
                                        </div>
                                    </td>
                                    <td class="py-2 px-4 text-center">
                                        <flux:badge size="sm" :color="$variant->track_stock ? 'green' : 'zinc'">{{ $variant->track_stock ? 'On' : 'Off' }}</flux:badge>
                                    </td>
                                    <td class="py-2 px-4 text-center">
                                        <flux:text size="sm" class="font-semibold tabular-nums {{ $vOut ? 'text-red-500' : ($vLow ? 'text-amber-500' : '') }}">{{ $variant->stock_quantity }}</flux:text>
                                    </td>
                                    <td class="py-2 px-4 text-center">
                                        @if($vOut)
                                            <flux:badge color="red" size="sm">Out</flux:badge>
                                        @elseif($vLow)
                                            <flux:badge color="amber" size="sm">Low</flux:badge>
                                        @else
                                            <flux:badge color="green" size="sm">OK</flux:badge>
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 text-right">
                                        <flux:button size="sm" variant="ghost" icon="plus-circle" wire:click="openAdjust({{ $product->id }}, {{ $variant->id }})">Adjust</flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="py-24 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <flux:icon.cube class="w-10 h-10 text-zinc-300 dark:text-zinc-700" />
                                    <flux:heading>No products found</flux:heading>
                                    <flux:subheading>Adjust your search or filters.</flux:subheading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
                {{ $products->links() }}
            </div>
        @endif
    </flux:card>

    {{-- Recent movements --}}
    @if($recentMovements->isNotEmpty())
        <flux:card>
            <flux:heading size="lg" class="mb-4">Recent Stock Movements</flux:heading>
            <div class="flex flex-col divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($recentMovements as $m)
                    <div class="flex items-center justify-between py-2.5">
                        <div class="flex items-center gap-3">
                            @php
                                $color = match($m->type) {
                                    'sale' => 'red',
                                    'restock' => 'green',
                                    'void_return' => 'blue',
                                    default => 'zinc',
                                };
                            @endphp
                            <flux:badge size="sm" :color="$color">{{ str_replace('_', ' ', ucfirst($m->type)) }}</flux:badge>
                            <div>
                                <flux:text size="sm" class="font-medium">
                                    {{ $m->product?->name }}{{ $m->variant ? ' — '.$m->variant->name : '' }}
                                </flux:text>
                                @if($m->reason)
                                    <flux:text size="sm" class="text-zinc-400">{{ $m->reason }}</flux:text>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <flux:text class="font-semibold tabular-nums {{ $m->quantity_change < 0 ? 'text-red-500' : 'text-green-600' }}">
                                {{ $m->quantity_change > 0 ? '+' : '' }}{{ $m->quantity_change }}
                            </flux:text>
                            <flux:text size="sm" class="text-zinc-400">→ {{ $m->balance_after }}</flux:text>
                        </div>
                    </div>
                @endforeach
            </div>
        </flux:card>
    @endif

    {{-- Adjust modal --}}
    <flux:modal wire:model.self="showAdjustModal" class="md:w-96">
        <div class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Adjust Stock</flux:heading>
                <flux:subheading>{{ $adjustLabel }}</flux:subheading>
            </div>

            <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-zinc-800/40 px-4 py-3">
                <flux:text size="sm" class="text-zinc-500">Current on hand</flux:text>
                <flux:heading>{{ $adjustCurrent }}</flux:heading>
            </div>

            <flux:radio.group wire:model.live="adjustMode" variant="segmented">
                <flux:radio value="restock" label="Add / Restock" />
                <flux:radio value="set" label="Set Exact" />
            </flux:radio.group>

            <flux:field>
                <flux:label>{{ $adjustMode === 'set' ? 'New quantity' : 'Quantity to add' }}</flux:label>
                <flux:input type="number" wire:model="adjustQuantity" />
                <flux:error name="adjustQuantity" />
            </flux:field>

            <flux:field>
                <flux:label>Reason <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                <flux:input wire:model="adjustReason" placeholder="e.g. Weekly delivery" />
                <flux:error name="adjustReason" />
            </flux:field>

            <div class="flex justify-end gap-3">
                <flux:button type="button" variant="ghost" wire:click="$set('showAdjustModal', false)">Cancel</flux:button>
                <flux:button type="button" variant="primary" wire:click="saveAdjust">Save</flux:button>
            </div>
        </div>
    </flux:modal>

</div>
