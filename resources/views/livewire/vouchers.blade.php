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
    <flux:table :paginate="$vouchers">
        <flux:table.columns>
            <flux:table.column class="py-3 px-4">Code</flux:table.column>
            <flux:table.column class="py-3 px-4">Type</flux:table.column>
            <flux:table.column class="py-3 px-4">Value</flux:table.column>
            <flux:table.column class="py-3 px-4">Usage</flux:table.column>
            <flux:table.column class="py-3 px-4">Status</flux:table.column>
            <flux:table.column class="py-3 px-4 text-right">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($vouchers as $voucher)
                <flux:table.row>
                    <flux:table.cell class="py-3 px-4">
                        <flux:text class="font-black uppercase tracking-wider">{{ $voucher->code }}</flux:text>
                        @if($voucher->name)
                            <flux:text size="sm" class="text-zinc-400">{{ $voucher->name }}</flux:text>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="py-3 px-4">
                        <flux:badge color="zinc" size="sm">{{ $voucher->type === 'fixed' ? 'Fixed' : 'Percent' }}</flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="py-3 px-4">
                        <flux:text class="font-black text-blue-600">
                            @if($voucher->type === 'fixed')
                                ${{ number_format((float) $voucher->value, 2) }}
                            @else
                                {{ rtrim(rtrim(number_format((float) $voucher->value, 2), '0'), '.') }}%
                            @endif
                        </flux:text>
                    </flux:table.cell>

                    <flux:table.cell class="py-3 px-4">
                        <flux:text size="sm" class="tabular-nums">
                            {{ (int) $voucher->usage_count }}@if($voucher->usage_limit) / {{ (int) $voucher->usage_limit }}@endif
                        </flux:text>
                        @if($voucher->usage_limit)
                            <div class="w-20 h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full mt-1 overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full" style="width: {{ min(100, ($voucher->usage_count / $voucher->usage_limit) * 100) }}%"></div>
                            </div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="py-3 px-4">
                        <flux:badge :color="$voucher->is_active ? 'green' : 'red'" size="sm">
                            {{ $voucher->is_active ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="py-3 px-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $voucher->id }})" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $voucher->id }})" wire:confirm="Delete this voucher?" class="text-red-500 hover:text-red-600" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="py-24 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <flux:icon.tag class="w-10 h-10 text-zinc-300 dark:text-zinc-700" />
                            <flux:heading>No vouchers yet</flux:heading>
                            <flux:subheading>Create your first voucher to start offering discounts.</flux:subheading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

</div>
