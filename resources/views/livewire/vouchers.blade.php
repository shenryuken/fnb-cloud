<div class="flex flex-col gap-6 p-4 md:p-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Vouchers</flux:heading>
            <flux:subheading>Create and manage voucher codes for discounts.</flux:subheading>
        </div>
        <flux:button wire:click="create" variant="primary" icon="plus">
            Add Voucher
        </flux:button>
    </div>

    {{-- Create / Edit Form --}}
    @if($isCreating || $editing)
        <flux:card>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center shrink-0">
                        <flux:icon.tag class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ $editing ? 'Update Voucher' : 'New Voucher' }}</flux:heading>
                        <flux:subheading>Codes are tenant-specific.</flux:subheading>
                    </div>
                </div>
                <flux:button wire:click="$set('isCreating', false); $set('editing', null)" variant="ghost" icon="x-mark" />
            </div>

            <form wire:submit.prevent="save">
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

                <flux:separator class="mb-5" />

                <div class="flex items-center justify-between">
                    <flux:checkbox wire:model="is_active" label="Active" />
                    <div class="flex gap-3">
                        <flux:button type="button" wire:click="$set('isCreating', false); $set('editing', null)" variant="ghost">Cancel</flux:button>
                        <flux:button type="submit" variant="primary">Save</flux:button>
                    </div>
                </div>
            </form>
        </flux:card>
    @endif

    {{-- Table --}}
    <flux:card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Code</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Type</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Value</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Usage</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Status</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($vouchers as $voucher)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-black uppercase tracking-wider">{{ $voucher->code }}</span>
                                @if($voucher->name)
                                    <div class="text-xs text-zinc-400">{{ $voucher->name }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <flux:badge color="zinc" size="sm">{{ $voucher->type === 'fixed' ? 'Fixed' : 'Percent' }}</flux:badge>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-black text-blue-600">
                                    @if($voucher->type === 'fixed')
                                        ${{ number_format((float) $voucher->value, 2) }}
                                    @else
                                        {{ rtrim(rtrim(number_format((float) $voucher->value, 2), '0'), '.') }}%
                                    @endif
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm tabular-nums">{{ (int) $voucher->used_count }}@if($voucher->usage_limit) / {{ (int) $voucher->usage_limit }}@endif</span>
                                @if($voucher->usage_limit)
                                    <div class="w-20 h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ min(100, ($voucher->used_count / $voucher->usage_limit) * 100) }}%"></div>
                                    </div>
                                @else
                                    <div class="text-[10px] font-black text-zinc-400 uppercase tracking-widest mt-1">Unlimited</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <flux:badge :color="$voucher->is_active ? 'green' : 'red'" size="sm">
                                    {{ $voucher->is_active ? 'Active' : 'Inactive' }}
                                </flux:badge>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $voucher->id }})" />
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $voucher->id }})" wire:confirm="Delete this voucher?" class="text-red-500 hover:text-red-600" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-24 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <flux:icon.tag class="w-10 h-10 text-zinc-300 dark:text-zinc-700" />
                                    <flux:heading>No vouchers yet</flux:heading>
                                    <flux:subheading>Create your first voucher to start offering discounts.</flux:subheading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-zinc-100 dark:border-zinc-800">
            {{ $vouchers->links() }}
        </div>
    </flux:card>

</div>
