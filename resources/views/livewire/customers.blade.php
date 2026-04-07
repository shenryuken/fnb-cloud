<div class="flex flex-col gap-6 p-4 md:p-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Customers</flux:heading>
            <flux:subheading>Register customers (name + email or mobile).</flux:subheading>
        </div>
        <flux:button wire:click="create" variant="primary" icon="plus">
            Add Customer
        </flux:button>
    </div>

    {{-- Create / Edit Form --}}
    @if($isCreating || $editing)
        <flux:card>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center shrink-0">
                        <flux:icon.users class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ $editing ? 'Update Customer' : 'New Customer' }}</flux:heading>
                        <flux:subheading>Email or mobile is required.</flux:subheading>
                    </div>
                </div>
                <flux:button wire:click="$set('isCreating', false); $set('editing', null)" variant="ghost" icon="x-mark" />
            </div>

            <form wire:submit.prevent="save">
                <div class="grid md:grid-cols-2 gap-5 mb-5">
                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="name" placeholder="Customer Name" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Points Balance</flux:label>
                        <flux:input type="number" wire:model="points_balance" placeholder="0" />
                        <flux:error name="points_balance" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Email <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                        <flux:input type="email" wire:model="email" placeholder="customer@email.com" />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Mobile <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                        <flux:input wire:model="mobile" placeholder="+60123456789" />
                        <flux:error name="mobile" />
                    </flux:field>
                </div>

                <flux:separator class="mb-5" />

                <div class="flex justify-end gap-3">
                    <flux:button type="button" wire:click="$set('isCreating', false); $set('editing', null)" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save</flux:button>
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
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Customer</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Email</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Mobile</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Points</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-semibold">{{ $customer->name }}</span>
                                <div class="text-xs text-zinc-400">ID: #{{ $customer->id }}</div>
                            </td>
                            <td class="py-3 px-4 text-sm text-zinc-600 dark:text-zinc-400">{{ $customer->email ?? '—' }}</td>
                            <td class="py-3 px-4 text-sm text-zinc-600 dark:text-zinc-400">{{ $customer->mobile ?? '—' }}</td>
                            <td class="py-3 px-4">
                                <flux:badge color="blue">{{ (int) $customer->points_balance }} pts</flux:badge>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $customer->id }})" />
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $customer->id }})" wire:confirm="Delete this customer?" class="text-red-500 hover:text-red-600" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-24 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <flux:icon.users class="w-10 h-10 text-zinc-300 dark:text-zinc-700" />
                                    <flux:heading>No customers yet</flux:heading>
                                    <flux:subheading>Add your first customer to get started.</flux:subheading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-zinc-100 dark:border-zinc-800">
            {{ $customers->links() }}
        </div>
    </flux:card>

</div>
