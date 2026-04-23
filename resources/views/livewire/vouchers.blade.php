<div class="flex flex-col gap-6 p-4 md:p-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Vouchers</flux:heading>
            <flux:subheading class="text-zinc-400">Create and manage voucher codes for discounts.</flux:subheading>
        </div>
        <flux:button wire:click="create" variant="primary" icon="plus">
            Add Voucher
        </flux:button>
    </div>

    {{-- Create / Edit Form --}}
    @if($isCreating || $editing)
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Main Form --}}
            <div class="xl:col-span-2">
                <flux:card class="p-6">

                    {{-- Form Header --}}
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-pink-500/10 flex items-center justify-center shrink-0">
                                <flux:icon.tag class="w-5 h-5 text-pink-500" />
                            </div>
                            <div>
                                <flux:heading size="lg">{{ $editing ? 'Update Voucher' : 'New Voucher' }}</flux:heading>
                                <flux:text size="sm" class="text-zinc-400">Codes are tenant-specific.</flux:text>
                            </div>
                        </div>
                        <flux:button wire:click="$set('isCreating', false); $set('editing', null)" variant="ghost" icon="x-mark" size="sm" />
                    </div>

                    <form wire:submit.prevent="save" class="space-y-6">

                        {{-- Code & Name --}}
                        <div class="grid sm:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Code</flux:label>
                                <flux:input wire:model="code" placeholder="WELCOME10" class="uppercase font-mono tracking-widest" />
                                <flux:error name="code" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Name <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                                <flux:input wire:model="name" placeholder="e.g. Season Promo" />
                                <flux:error name="name" />
                            </flux:field>
                        </div>

                        {{-- Type, Value, Usage Limit --}}
                        <div class="grid sm:grid-cols-3 gap-4">
                            <flux:field>
                                <flux:label>Type</flux:label>
                                <flux:select wire:model.live="type">
                                    <flux:select.option value="percent">Percent (%)</flux:select.option>
                                    <flux:select.option value="fixed">Fixed (RM)</flux:select.option>
                                </flux:select>
                                <flux:error name="type" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Value</flux:label>
                                <flux:input type="number" step="0.01" min="0" wire:model="value" placeholder="{{ $type === 'percent' ? '10' : '5.00' }}" />
                                <flux:error name="value" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Usage Limit <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                                <flux:input type="number" min="1" wire:model="usage_limit" placeholder="Unlimited" />
                                <flux:error name="usage_limit" />
                            </flux:field>
                        </div>

                        {{-- Date Range --}}
                        <div class="grid sm:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Starts At <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                                <flux:input type="datetime-local" wire:model="starts_at" />
                                <flux:error name="starts_at" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Ends At <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                                <flux:input type="datetime-local" wire:model="ends_at" />
                                <flux:error name="ends_at" />
                            </flux:field>
                        </div>

                        <flux:separator />

                        {{-- Rules --}}
                        <div class="space-y-4">
                            <flux:heading size="sm" class="text-zinc-300">Rules</flux:heading>

                            <flux:field>
                                <flux:label>Per Customer Limit <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                                <flux:input type="number" min="1" wire:model="per_customer_limit" placeholder="Unlimited" class="max-w-xs" />
                                <flux:description>Max times a single customer can redeem this voucher.</flux:description>
                                <flux:error name="per_customer_limit" />
                            </flux:field>

                            <div class="flex flex-col gap-3">
                                <div class="flex items-start gap-3 p-3 bg-zinc-800/50 rounded-lg border border-zinc-700">
                                    <flux:checkbox wire:model="first_time_only" class="mt-0.5" />
                                    <div>
                                        <flux:text size="sm" class="font-medium">First-time customer only</flux:text>
                                        <flux:text size="xs" class="text-zinc-400">Only customers with no prior orders can redeem.</flux:text>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 bg-zinc-800/50 rounded-lg border border-zinc-700">
                                    <flux:checkbox wire:model="can_combine_with_manual_discount" class="mt-0.5" />
                                    <div>
                                        <flux:text size="sm" class="font-medium">Can combine with manual discount</flux:text>
                                        <flux:text size="xs" class="text-zinc-400">Allow stacking with POS manual discounts.</flux:text>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 bg-zinc-800/50 rounded-lg border border-zinc-700">
                                    <flux:checkbox wire:model="can_combine_with_points" class="mt-0.5" />
                                    <div>
                                        <flux:text size="sm" class="font-medium">Can combine with points</flux:text>
                                        <flux:text size="xs" class="text-zinc-400">Allow stacking with loyalty point redemptions.</flux:text>
                                    </div>
                                </div>
                            </div>

                            <flux:text size="xs" class="text-zinc-500 uppercase tracking-widest font-semibold">
                                If a rule is OFF, POS will block using this voucher together with that promotion.
                            </flux:text>
                        </div>

                        <flux:separator />

                        {{-- Free Item --}}
                        <div class="space-y-3">
                            <flux:heading size="sm" class="text-zinc-300">Free Item <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:heading>
                            <div class="grid sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-2">
                                    <flux:field>
                                        <flux:label>Product</flux:label>
                                        <flux:select wire:model="free_product_id">
                                            <flux:select.option value="">— None —</flux:select.option>
                                            @foreach($products as $product)
                                                <flux:select.option value="{{ $product->id }}">{{ $product->name }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        <flux:error name="free_product_id" />
                                    </flux:field>
                                </div>
                                <flux:field>
                                    <flux:label>Qty</flux:label>
                                    <flux:input type="number" min="1" wire:model="free_quantity" />
                                    <flux:error name="free_quantity" />
                                </flux:field>
                            </div>
                        </div>

                        <flux:separator />

                        {{-- Auto-Issue --}}
                        <div class="space-y-3">
                            <flux:heading size="sm" class="text-zinc-300">Auto-Issue <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:heading>
                            <div class="grid sm:grid-cols-2 gap-4">
                                <flux:field>
                                    <flux:label>Auto-Issue After Purchase (Min Spend)</flux:label>
                                    <flux:input type="number" step="0.01" min="0" wire:model="issue_on_min_spend" placeholder="5.00" />
                                    <flux:description>Issue this voucher after a qualifying purchase.</flux:description>
                                    <flux:error name="issue_on_min_spend" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Issued Voucher Expiry (Days)</flux:label>
                                    <flux:input type="number" min="1" wire:model="issue_expires_in_days" placeholder="30" />
                                    <flux:description>How long the issued voucher stays valid.</flux:description>
                                    <flux:error name="issue_expires_in_days" />
                                </flux:field>
                            </div>
                        </div>

                        <flux:separator />

                        {{-- Footer Actions --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <flux:field>
                                <div class="flex items-center gap-3">
                                    <flux:switch wire:model.live="is_active" />
                                    <div>
                                        <flux:label class="mb-0">Active</flux:label>
                                        <flux:text size="xs" class="{{ $is_active ? 'text-green-400' : 'text-red-400' }}">
                                            {{ $is_active ? 'Visible in POS' : 'Hidden from POS' }}
                                        </flux:text>
                                    </div>
                                </div>
                            </flux:field>
                            <div class="flex gap-3">
                                <flux:button type="button" wire:click="$set('isCreating', false); $set('editing', null)" variant="ghost">Cancel</flux:button>
                                <flux:button type="submit" variant="primary">{{ $editing ? 'Update' : 'Save' }}</flux:button>
                            </div>
                        </div>

                    </form>
                </flux:card>
            </div>

            {{-- Right Sidebar --}}
            <div class="flex flex-col gap-4">

                {{-- Guide --}}
                <flux:card class="p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:icon.information-circle class="w-4 h-4 text-zinc-400" />
                        <flux:text size="xs" class="text-zinc-400 uppercase font-semibold tracking-widest">Guide</flux:text>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <flux:text size="sm" class="font-semibold text-white mb-1">No promotion stacking voucher</flux:text>
                            <flux:text size="xs" class="text-zinc-400">Turn OFF both "Can combine with manual discount" and "Can combine with points".</flux:text>
                        </div>
                        <flux:separator />
                        <div>
                            <flux:text size="sm" class="font-semibold text-white mb-1">WELCOME voucher (once per customer)</flux:text>
                            <flux:text size="xs" class="text-zinc-400">Set "Per Customer Limit" to 1. Optionally enable "First-time customer only".</flux:text>
                        </div>
                        <flux:separator />
                        <div>
                            <flux:text size="sm" class="font-semibold text-white mb-1">Free item next purchase</flux:text>
                            <flux:text size="xs" class="text-zinc-400">Choose "Free Item", set "Auto-Issue After Purchase (Min Spend)", and select customer in POS checkout.</flux:text>
                        </div>
                        <flux:separator />
                        <div>
                            <flux:text size="sm" class="font-semibold text-white mb-1">Basic Discount Voucher</flux:text>
                            <flux:text size="xs" class="text-zinc-400">Set a code, choose Percent or Fixed type, enter the discount value, and set Active to make it available in POS.</flux:text>
                        </div>
                        <flux:separator />
                        <div>
                            <flux:text size="sm" class="font-semibold text-white mb-1">Time-Limited Voucher</flux:text>
                            <flux:text size="xs" class="text-zinc-400">Use Starts At and Ends At to define a validity window. POS will automatically block usage outside this range.</flux:text>
                        </div>
                    </div>
                </flux:card>

                {{-- Live Details --}}
                @if($editing)
                    <flux:card class="p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <flux:icon.eye class="w-4 h-4 text-zinc-400" />
                                <flux:text size="xs" class="text-zinc-400 uppercase font-semibold tracking-widest">Live Details</flux:text>
                            </div>
                            <flux:badge :color="$editing->is_active ? 'green' : 'red'" size="sm">
                                {{ $editing->is_active ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </div>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-400">Code</span>
                                <span class="font-mono font-semibold">{{ $editing->code }}</span>
                            </div>
                            <flux:separator />
                            <div class="flex justify-between">
                                <span class="text-zinc-400">Name</span>
                                <span>{{ $editing->name ?? '—' }}</span>
                            </div>
                            <flux:separator />
                            <div class="flex justify-between">
                                <span class="text-zinc-400">Benefit</span>
                                <span class="font-semibold text-pink-400">
                                    @if($editing->type === 'fixed')
                                        RM {{ number_format((float) $editing->value, 2) }} off
                                    @else
                                        {{ rtrim(rtrim(number_format((float) $editing->value, 2), '0'), '.') }}% off
                                    @endif
                                </span>
                            </div>
                            <flux:separator />
                            <div class="flex justify-between">
                                <span class="text-zinc-400">Validity</span>
                                <span>
                                    @if($editing->starts_at || $editing->ends_at)
                                        {{ $editing->starts_at?->format('d M Y') ?? '∞' }} – {{ $editing->ends_at?->format('d M Y') ?? '∞' }}
                                    @else
                                        Always
                                    @endif
                                </span>
                            </div>
                            <flux:separator />
                            <div class="flex justify-between">
                                <span class="text-zinc-400">Usage</span>
                                <span>
                                    {{ (int) $editing->usage_count }}{{ $editing->usage_limit ? ' / ' . $editing->usage_limit : '' }}
                                    @if(!$editing->usage_limit)<span class="text-zinc-500 text-xs ml-1">Unlimited</span>@endif
                                </span>
                            </div>
                            <flux:separator />
                            <div class="flex justify-between">
                                <span class="text-zinc-400">Customer rules</span>
                                <span>{{ $editing->per_customer_limit ? 'Max ' . $editing->per_customer_limit . '/customer' : 'None' }}</span>
                            </div>
                            <flux:separator />
                            <p class="text-xs text-zinc-500 uppercase tracking-widest font-semibold">Customer optional to redeem in POS.</p>
                            <div>
                                <span class="text-zinc-400 text-sm">Promotion stacking</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <flux:badge size="sm" :color="$editing->can_combine_with_manual_discount ? 'green' : 'red'">
                                    {{ $editing->can_combine_with_manual_discount ? 'Manual discount OK' : 'No manual discount' }}
                                </flux:badge>
                                <flux:badge size="sm" :color="$editing->can_combine_with_points ? 'green' : 'red'">
                                    {{ $editing->can_combine_with_points ? 'Points OK' : 'No points' }}
                                </flux:badge>
                            </div>
                            <flux:separator />
                            <div class="flex justify-between">
                                <span class="text-zinc-400">Auto-Issue</span>
                                <span>{{ $editing->issue_on_min_spend ? 'RM ' . number_format((float) $editing->issue_on_min_spend, 2) . ' min spend' : 'Off' }}</span>
                            </div>
                        </div>
                    </flux:card>
                @endif

            </div>
        </div>
    @endif

    {{-- Vouchers Table --}}
    <flux:card class="p-0 overflow-hidden">

        <div class="px-6 py-4 border-b border-zinc-700">
            <flux:heading size="md">All Vouchers</flux:heading>
            <flux:text size="sm" class="text-zinc-400">{{ $vouchers->total() }} voucher(s) found</flux:text>
        </div>

        {{-- Mobile Cards --}}
        <div class="divide-y divide-zinc-800 sm:hidden">
            @forelse($vouchers as $voucher)
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="font-mono font-bold uppercase tracking-wider">{{ $voucher->code }}</div>
                            @if($voucher->name)
                                <div class="text-xs text-zinc-400 mt-0.5">{{ $voucher->name }}</div>
                            @endif
                        </div>
                        <flux:badge :color="$voucher->is_active ? 'green' : 'red'" size="sm">
                            {{ $voucher->is_active ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:badge color="zinc" size="sm">{{ $voucher->type === 'fixed' ? 'Fixed' : 'Percent' }}</flux:badge>
                        <span class="font-bold text-pink-400 text-sm">
                            @if($voucher->type === 'fixed')
                                RM {{ number_format((float) $voucher->value, 2) }}
                            @else
                                {{ rtrim(rtrim(number_format((float) $voucher->value, 2), '0'), '.') }}%
                            @endif
                        </span>
                        <span class="text-xs text-zinc-400 ml-auto">
                            {{ (int) $voucher->usage_count }}{{ $voucher->usage_limit ? ' / ' . $voucher->usage_limit : ' / ∞' }} used
                        </span>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $voucher->id }})" />
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $voucher->id }})" wire:confirm="Delete this voucher?" class="text-red-500" />
                    </div>
                </div>
            @empty
                <div class="py-16 text-center">
                    <flux:icon.tag class="w-10 h-10 text-zinc-600 mx-auto mb-3" />
                    <flux:heading size="sm">No vouchers yet</flux:heading>
                    <flux:text size="sm" class="text-zinc-400 mt-1">Create your first voucher to start offering discounts.</flux:text>
                </div>
            @endforelse
        </div>

        {{-- Desktop Table --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-zinc-700">
                        <th class="py-3 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Code</th>
                        <th class="py-3 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Type</th>
                        <th class="py-3 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Value</th>
                        <th class="py-3 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Usage</th>
                        <th class="py-3 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Status</th>
                        <th class="py-3 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @forelse($vouchers as $voucher)
                        <tr class="hover:bg-zinc-800/30 transition-colors">
                            <td class="py-4 px-5">
                                <span class="font-mono font-bold uppercase tracking-wider">{{ $voucher->code }}</span>
                                @if($voucher->name)
                                    <div class="text-xs text-zinc-400 mt-0.5">{{ $voucher->name }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-5">
                                <flux:badge color="zinc" size="sm">{{ $voucher->type === 'fixed' ? 'Fixed' : 'Percent' }}</flux:badge>
                            </td>
                            <td class="py-4 px-5">
                                <span class="font-bold text-pink-400">
                                    @if($voucher->type === 'fixed')
                                        RM {{ number_format((float) $voucher->value, 2) }}
                                    @else
                                        {{ rtrim(rtrim(number_format((float) $voucher->value, 2), '0'), '.') }}%
                                    @endif
                                </span>
                            </td>
                            <td class="py-4 px-5">
                                <div class="tabular-nums">
                                    {{ (int) $voucher->usage_count }}@if($voucher->usage_limit) / {{ (int) $voucher->usage_limit }}@endif
                                </div>
                                @if($voucher->usage_limit)
                                    <div class="w-20 h-1 bg-zinc-700 rounded-full mt-1.5 overflow-hidden">
                                        <div class="h-full bg-pink-500 rounded-full" style="width: {{ min(100, ($voucher->usage_count / $voucher->usage_limit) * 100) }}%"></div>
                                    </div>
                                @else
                                    <div class="text-xs text-zinc-500 mt-0.5">Unlimited</div>
                                @endif
                            </td>
                            <td class="py-4 px-5">
                                <flux:badge :color="$voucher->is_active ? 'green' : 'red'" size="sm">
                                    {{ $voucher->is_active ? 'Active' : 'Inactive' }}
                                </flux:badge>
                            </td>
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $voucher->id }})" />
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $voucher->id }})" wire:confirm="Delete this voucher?" class="text-red-500" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <flux:icon.tag class="w-10 h-10 text-zinc-600" />
                                    <flux:heading size="sm">No vouchers yet</flux:heading>
                                    <flux:text size="sm" class="text-zinc-400">Create your first voucher to start offering discounts.</flux:text>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vouchers->hasPages())
            <div class="px-5 py-3 border-t border-zinc-700">
                {{ $vouchers->links() }}
            </div>
        @endif

    </flux:card>

</div>
