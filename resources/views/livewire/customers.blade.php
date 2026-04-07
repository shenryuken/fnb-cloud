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
    <flux:table :paginate="$customers">
        <flux:table.columns>
            <flux:table.column class="py-3 px-4">Customer</flux:table.column>
            <flux:table.column class="py-3 px-4">Email</flux:table.column>
            <flux:table.column class="py-3 px-4">Mobile</flux:table.column>
            <flux:table.column class="py-3 px-4">Points</flux:table.column>
            <flux:table.column class="py-3 px-4 text-right">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($customers as $customer)
                <flux:table.row>
                    <flux:table.cell class="py-3 px-4">
                        <flux:text class="font-semibold">{{ $customer->name }}</flux:text>
                        <flux:text size="sm" class="text-zinc-400">ID: #{{ $customer->id }}</flux:text>
                    </flux:table.cell>

                    <flux:table.cell class="py-3 px-4">
                        <flux:text size="sm">{{ $customer->email ?? '—' }}</flux:text>
                    </flux:table.cell>

                    <flux:table.cell class="py-3 px-4">
                        <flux:text size="sm">{{ $customer->mobile ?? '—' }}</flux:text>
                    </flux:table.cell>

                    <flux:table.cell class="py-3 px-4">
                        <flux:badge color="blue">{{ (int) $customer->points_balance }} pts</flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="py-3 px-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $customer->id }})" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $customer->id }})" wire:confirm="Delete this customer?" class="text-red-500 hover:text-red-600" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="py-24 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <flux:icon.users class="w-10 h-10 text-zinc-300 dark:text-zinc-700" />
                            <flux:heading>No customers yet</flux:heading>
                            <flux:subheading>Add your first customer to get started.</flux:subheading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

</div>
