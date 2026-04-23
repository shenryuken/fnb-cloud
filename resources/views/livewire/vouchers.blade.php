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

                    <form wire:submit.prevent="save" class="space-y-5">

                        {{-- Code & Name --}}
                        <div class="grid sm:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Code</flux:label>
                                <flux:input wire:model="code" placeholder="WELCOME10" class="uppercase font-mono tracking-widest" />
                                <flux:error name="code" />
                            </flux:field>
                            <flux:field>
                                <flux:label>
                                    Name
                                    <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge>
                                </flux:label>
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
                                <flux:label>
                                    Usage Limit
                                    <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge>
                                </flux:label>
                                <flux:input type="number" min="1" wire:model="usage_limit" placeholder="Unlimited" />
                                <flux:error name="usage_limit" />
                            </flux:field>
                        </div>

                        {{-- Date Range --}}
                        <div class="grid sm:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>
                                    Starts At
                                    <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge>
                                </flux:label>
                                <flux:input type="datetime-local" wire:model="starts_at" />
                                <flux:error name="starts_at" />
                            </flux:field>
                            <flux:field>
                                <flux:label>
                                    Ends At
                                    <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge>
                                </flux:label>
                                <flux:input type="datetime-local" wire:model="ends_at" />
                                <flux:error name="ends_at" />
                            </flux:field>
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
                                <flux:button type="button" wire:click="$set('isCreating', false); $set('editing', null)" variant="ghost">
                                    Cancel
                                </flux:button>
                                <flux:button type="submit" variant="primary">
                                    {{ $editing ? 'Update' : 'Save' }}
                                </flux:button>
                            </div>
                        </div>

                    </form>
                </flux:card>
            </div>

<<<<<<< Updated upstream
            {{-- Right Sidebar --}}
            <div class="flex flex-col gap-4">

                {{-- Guide --}}
                <flux:card class="p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <flux:icon.information-circle class="w-4 h-4 text-zinc-400" />
                        <flux:text size="xs" class="text-zinc-400 uppercase font-semibold tracking-widest">Guide</flux:text>
=======
            <form wire:submit.prevent="save">
                @php
                    $previewCode = strtoupper(trim((string) ($code ?? '')));
                    $previewName = trim((string) ($name ?? ''));
                    $previewType = (string) ($type ?? 'percent');
                    $previewValue = (float) ($value ?? 0);
                    $previewUsageLimit = filled($usage_limit ?? null) ? (int) $usage_limit : null;
                    $previewPerCustomer = filled($per_customer_limit ?? null) ? (int) $per_customer_limit : null;
                    $previewFirstTimeOnly = (bool) ($first_time_only ?? false);
                    $previewCombineDiscount = (bool) ($can_combine_with_manual_discount ?? false);
                    $previewCombinePoints = (bool) ($can_combine_with_points ?? false);
                    $previewFreeProductId = filled($free_product_id ?? null) ? (int) $free_product_id : null;
                    $previewFreeQty = max(1, (int) ($free_quantity ?? 1));
                    $previewIssueMinSpend = filled($issue_on_min_spend ?? null) ? (float) $issue_on_min_spend : null;
                    $previewIssueExpiryDays = filled($issue_expires_in_days ?? null) ? (int) $issue_expires_in_days : null;
                    $previewStartsAt = (string) ($starts_at ?? '');
                    $previewEndsAt = (string) ($ends_at ?? '');
                    $freeProductName = '';
                    if ($previewFreeProductId) {
                        $match = collect($products ?? [])->firstWhere('id', $previewFreeProductId);
                        $freeProductName = (string) ($match?->name ?? '');
                    }
                    $requiresCustomer = $previewFirstTimeOnly || ($previewPerCustomer !== null && $previewPerCustomer > 0);
                    $requiresCustomerForIssuing = $previewIssueMinSpend !== null && $previewIssueMinSpend > 0;
                @endphp

                <div class="grid lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <div class="grid md:grid-cols-2 gap-5 mb-5">
                            <flux:field>
                                <flux:label>Code</flux:label>
                                <flux:input wire:model="code" placeholder="WELCOME10" class="uppercase" />
                                <flux:error name="code" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Name <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                                <flux:input wire:model="name" placeholder="Season Promo" />
                                <flux:error name="name" />
                            </flux:field>
                        </div>

                        <div class="grid md:grid-cols-3 gap-5 mb-5">
                            <flux:field>
                                <flux:label>Type</flux:label>
                                <flux:select wire:model="type">
                                    <flux:select.option value="percent">Percent (%)</flux:select.option>
                                    <flux:select.option value="fixed">Fixed ($)</flux:select.option>
                                </flux:select>
                                <flux:error name="type" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Value</flux:label>
                                <flux:input type="number" step="0.01" wire:model="value" placeholder="10" />
                                <flux:error name="value" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Usage Limit <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                                <flux:input type="number" wire:model="usage_limit" placeholder="100" />
                                <flux:error name="usage_limit" />
                            </flux:field>
                        </div>

                        <div class="grid md:grid-cols-2 gap-5 mb-5">
                            <flux:field>
                                <flux:label>Starts At <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                                <flux:input type="datetime-local" wire:model="starts_at" />
                                <flux:error name="starts_at" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Ends At <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                                <flux:input type="datetime-local" wire:model="ends_at" />
                                <flux:error name="ends_at" />
                            </flux:field>
                        </div>

                        <div class="grid md:grid-cols-3 gap-5 mb-5">
                            <flux:field>
                                <flux:label>Per Customer Limit <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                                <flux:input type="number" wire:model="per_customer_limit" placeholder="1" />
                                <flux:error name="per_customer_limit" />
                            </flux:field>

                            <flux:field class="md:col-span-2">
                                <flux:label>Rules</flux:label>
                                <div class="flex flex-wrap gap-5 pt-2">
                                    <flux:checkbox wire:model="first_time_only" label="First-time customer only" />
                                    <flux:checkbox wire:model="can_combine_with_manual_discount" label="Can combine with manual discount" />
                                    <flux:checkbox wire:model="can_combine_with_points" label="Can combine with points" />
                                </div>
                                <div class="mt-2 text-[10px] font-black text-zinc-400 uppercase tracking-widest">
                                    If a checkbox is OFF, POS will block using this voucher together with that promotion.
                                </div>
                                <flux:error name="first_time_only" />
                                <flux:error name="can_combine_with_manual_discount" />
                                <flux:error name="can_combine_with_points" />
                            </flux:field>
                        </div>

                        <div class="grid md:grid-cols-3 gap-5 mb-5">
                            <flux:field class="md:col-span-2">
                                <flux:label>Free Item <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                                <flux:select wire:model="free_product_id">
                                    <flux:select.option value="">— None —</flux:select.option>
                                    @foreach($products as $p)
                                        <flux:select.option value="{{ $p->id }}">{{ $p->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="free_product_id" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Qty</flux:label>
                                <flux:input type="number" wire:model="free_quantity" placeholder="1" />
                                <flux:error name="free_quantity" />
                            </flux:field>
                        </div>

                        <div class="grid md:grid-cols-2 gap-5 mb-5">
                            <flux:field>
                                <flux:label>Auto-Issue After Purchase (Min Spend) <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                                <flux:input type="number" step="0.01" wire:model="issue_on_min_spend" placeholder="5.00" />
                                <flux:error name="issue_on_min_spend" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Issued Voucher Expiry (Days) <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                                <flux:input type="number" wire:model="issue_expires_in_days" placeholder="30" />
                                <flux:error name="issue_expires_in_days" />
                            </flux:field>
                        </div>

                        <flux:separator class="mb-5" />

                        <div class="flex items-center justify-between">
                            <flux:checkbox wire:model="is_active" label="Active" />
                            <div class="flex gap-3">
                                <flux:button type="button" wire:click="$set('isCreating', false); $set('editing', null)" variant="ghost">Cancel</flux:button>
                                <flux:button type="submit" variant="primary">Save</flux:button>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="lg:sticky lg:top-6 space-y-4">
                            <flux:card class="p-4 space-y-3">
                                <div class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Guide</div>

                                <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">No promotion stacking voucher</div>
                                <div class="text-xs text-zinc-500">
                                    Turn OFF both “Can combine with manual discount” and “Can combine with points”.
                                </div>

                                <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">WELCOME voucher (once per customer)</div>
                                <div class="text-xs text-zinc-500">
                                    Set “Per Customer Limit” to 1. Optionally enable “First-time customer only”.
                                </div>

                                <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Free item next purchase</div>
                                <div class="text-xs text-zinc-500">
                                    Choose “Free Item”, set “Auto-Issue After Purchase (Min Spend)”, and select customer in POS checkout.
                                </div>
                            </flux:card>

                            <flux:card class="p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Live Details</div>
                                    <flux:badge :color="(bool) ($is_active ?? true) ? 'green' : 'red'" size="sm">
                                        {{ (bool) ($is_active ?? true) ? 'Active' : 'Inactive' }}
                                    </flux:badge>
                                </div>

                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-zinc-500 font-semibold">Code</span>
                                        <span class="font-black uppercase tracking-widest text-zinc-800 dark:text-zinc-100">{{ $previewCode !== '' ? $previewCode : '—' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-zinc-500 font-semibold">Name</span>
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-100 text-right">{{ $previewName !== '' ? $previewName : '—' }}</span>
                                    </div>

                                    <div class="border-t border-zinc-100 dark:border-zinc-800 pt-2 space-y-2">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-zinc-500 font-semibold">Benefit</span>
                                            <span class="font-semibold text-zinc-800 dark:text-zinc-100 text-right">
                                                @if($previewFreeProductId)
                                                    Free: {{ $freeProductName !== '' ? $freeProductName : ('#' . $previewFreeProductId) }} × {{ $previewFreeQty }}
                                                @else
                                                    @if($previewType === 'fixed')
                                                        ${{ number_format(max(0, $previewValue), 2) }} off
                                                    @else
                                                        {{ rtrim(rtrim(number_format(min(100, max(0, $previewValue)), 2), '0'), '.') }}% off
                                                    @endif
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    <div class="border-t border-zinc-100 dark:border-zinc-800 pt-2 space-y-2">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-zinc-500 font-semibold">Validity</span>
                                            <span class="font-semibold text-zinc-800 dark:text-zinc-100 text-right">
                                                @if($previewStartsAt === '' && $previewEndsAt === '')
                                                    Always
                                                @else
                                                    {{ $previewStartsAt !== '' ? $previewStartsAt : '—' }} → {{ $previewEndsAt !== '' ? $previewEndsAt : '—' }}
                                                @endif
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-zinc-500 font-semibold">Usage</span>
                                            <span class="font-semibold text-zinc-800 dark:text-zinc-100 text-right">
                                                @if($previewUsageLimit === null)
                                                    Unlimited
                                                @else
                                                    {{ $previewUsageLimit }} total uses
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    <div class="border-t border-zinc-100 dark:border-zinc-800 pt-2 space-y-2">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-zinc-500 font-semibold">Customer rules</span>
                                            <span class="font-semibold text-zinc-800 dark:text-zinc-100 text-right">
                                                @if($previewFirstTimeOnly)
                                                    First-time only
                                                @elseif($previewPerCustomer !== null)
                                                    Limit {{ $previewPerCustomer }}/customer
                                                @else
                                                    None
                                                @endif
                                            </span>
                                        </div>

                                        <div class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">
                                            @if($requiresCustomer)
                                                Customer required to redeem in POS.
                                            @else
                                                Customer optional to redeem in POS.
                                            @endif
                                            @if($requiresCustomerForIssuing)
                                                Auto-issue requires selecting customer at checkout.
                                            @endif
                                        </div>
                                    </div>

                                    <div class="border-t border-zinc-100 dark:border-zinc-800 pt-2 space-y-2">
                                        <div class="text-zinc-500 font-semibold">Promotion stacking</div>
                                        <div class="flex flex-wrap gap-2">
                                            <flux:badge :color="$previewCombineDiscount ? 'green' : 'zinc'" size="sm">
                                                {{ $previewCombineDiscount ? 'Manual discount allowed' : 'No manual discount' }}
                                            </flux:badge>
                                            <flux:badge :color="$previewCombinePoints ? 'green' : 'zinc'" size="sm">
                                                {{ $previewCombinePoints ? 'Points allowed' : 'No points' }}
                                            </flux:badge>
                                        </div>
                                    </div>

                                    <div class="border-t border-zinc-100 dark:border-zinc-800 pt-2 space-y-2">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-zinc-500 font-semibold">Auto-issue</span>
                                            <span class="font-semibold text-zinc-800 dark:text-zinc-100 text-right">
                                                @if($previewIssueMinSpend !== null && $previewIssueMinSpend > 0)
                                                    Min spend ${{ number_format($previewIssueMinSpend, 2) }}
                                                @else
                                                    Off
                                                @endif
                                            </span>
                                        </div>
                                        @if($previewIssueMinSpend !== null && $previewIssueMinSpend > 0)
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-zinc-500 font-semibold">Issued expiry</span>
                                                <span class="font-semibold text-zinc-800 dark:text-zinc-100 text-right">
                                                    @if($previewIssueExpiryDays !== null && $previewIssueExpiryDays > 0)
                                                        {{ $previewIssueExpiryDays }} day(s)
                                                    @else
                                                        No expiry
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </flux:card>
                        </div>
>>>>>>> Stashed changes
                    </div>
                    <div class="space-y-4">
                        <div>
                            <flux:text size="sm" class="font-semibold text-white mb-1">Basic Discount Voucher</flux:text>
                            <flux:text size="xs" class="text-zinc-400">Set a code, choose Percent or Fixed type, enter the discount value, and set Active to make it available in POS.</flux:text>
                        </div>
                        <flux:separator />
                        <div>
                            <flux:text size="sm" class="font-semibold text-white mb-1">Limited Usage Voucher</flux:text>
                            <flux:text size="xs" class="text-zinc-400">Set a Usage Limit to cap how many times this voucher can be redeemed across all customers.</flux:text>
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
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <flux:text size="sm" class="text-zinc-400">Code</flux:text>
                                <flux:text size="sm" class="font-mono font-semibold">{{ $editing->code }}</flux:text>
                            </div>
                            <flux:separator />
                            <div class="flex items-center justify-between">
                                <flux:text size="sm" class="text-zinc-400">Name</flux:text>
                                <flux:text size="sm">{{ $editing->name ?? '—' }}</flux:text>
                            </div>
                            <flux:separator />
                            <div class="flex items-center justify-between">
                                <flux:text size="sm" class="text-zinc-400">Benefit</flux:text>
                                <flux:text size="sm" class="font-semibold text-pink-400">
                                    @if($editing->type === 'fixed')
                                        RM {{ number_format((float) $editing->value, 2) }} off
                                    @else
                                        {{ rtrim(rtrim(number_format((float) $editing->value, 2), '0'), '.') }}% off
                                    @endif
                                </flux:text>
                            </div>
                            <flux:separator />
                            <div class="flex items-center justify-between">
                                <flux:text size="sm" class="text-zinc-400">Validity</flux:text>
                                <flux:text size="sm">
                                    @if($editing->starts_at || $editing->ends_at)
                                        {{ $editing->starts_at?->format('d M Y') ?? '∞' }} – {{ $editing->ends_at?->format('d M Y') ?? '∞' }}
                                    @else
                                        Always
                                    @endif
                                </flux:text>
                            </div>
                            <flux:separator />
                            <div class="flex items-center justify-between">
                                <flux:text size="sm" class="text-zinc-400">Usage</flux:text>
                                <flux:text size="sm">
                                    {{ (int) $editing->usage_count }}{{ $editing->usage_limit ? ' / ' . $editing->usage_limit : '' }}
                                    @if(!$editing->usage_limit)
                                        <span class="text-zinc-500 text-xs ml-1">Unlimited</span>
                                    @endif
                                </flux:text>
                            </div>
                        </div>
                    </flux:card>
                @endif

            </div>
        </div>
    @endif

    {{-- Vouchers Table --}}
    <flux:card class="p-0 overflow-hidden">

        {{-- Table Header --}}
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
                            {{ (int) $voucher->used_count }}{{ $voucher->usage_limit ? ' / ' . $voucher->usage_limit : ' / ∞' }} used
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
                                <div class="text-sm tabular-nums">
                                    {{ (int) $voucher->used_count }}@if($voucher->usage_limit) / {{ (int) $voucher->usage_limit }}@endif
                                </div>
                                @if($voucher->usage_limit)
                                    <div class="w-20 h-1 bg-zinc-700 rounded-full mt-1.5 overflow-hidden">
                                        <div class="h-full bg-pink-500 rounded-full" style="width: {{ min(100, ($voucher->used_count / $voucher->usage_limit) * 100) }}%"></div>
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
