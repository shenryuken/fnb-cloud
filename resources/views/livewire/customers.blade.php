<div class="flex flex-col gap-6 p-4 md:p-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Customers</flux:heading>
            <flux:subheading>Register customers (name + email or mobile).</flux:subheading>
        </div>
        <flux:badge color="blue">
            {{ ($hasActiveFilters ?? false) ? 'Filtered:' : 'Total:' }} {{ $customers->total() }} customers
        </flux:badge>
        <flux:button wire:click="create" variant="primary" icon="plus">
            Add Customer
        </flux:button>
    </div>

    {{-- Filters --}}
    <flux:card class="p-4">
        <div class="flex flex-col lg:flex-row gap-3">
            <div class="flex-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by name, email, mobile..."
                    icon="magnifying-glass"
                    clearable
                />
            </div>

            <flux:select wire:model.live="contactFilter" placeholder="All Contacts" class="min-w-[170px]">
                <flux:select.option value="">All Contacts</flux:select.option>
                <flux:select.option value="has_email">Has Email</flux:select.option>
                <flux:select.option value="has_mobile">Has Mobile</flux:select.option>
                <flux:select.option value="missing">Missing Contact</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="ordersFilter" placeholder="All Orders" class="min-w-[160px]">
                <flux:select.option value="">All Orders</flux:select.option>
                <flux:select.option value="with_orders">Has Orders</flux:select.option>
                <flux:select.option value="no_orders">No Orders</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="sort" placeholder="Sort" class="min-w-[160px]">
                <flux:select.option value="newest">Newest</flux:select.option>
                <flux:select.option value="name">Name (A-Z)</flux:select.option>
                <flux:select.option value="points_desc">Points (High)</flux:select.option>
                <flux:select.option value="last_visit">Last Visit</flux:select.option>
            </flux:select>

            @if(($hasActiveFilters ?? false))
                <flux:button wire:click="clearFilters" variant="ghost" icon="x-mark" class="text-red-500 hover:text-red-600">
                    Clear
                </flux:button>
            @endif
        </div>

        @if(($hasActiveFilters ?? false))
            <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                <flux:text size="sm" class="font-bold text-zinc-400">Active Filters:</flux:text>
                @if($search !== '')
                    <flux:badge color="blue" size="sm" icon="magnifying-glass">"{{ $search }}"</flux:badge>
                @endif
                @if($contactFilter !== '')
                    <flux:badge color="purple" size="sm">Contact: {{ str_replace('_', ' ', $contactFilter) }}</flux:badge>
                @endif
                @if($ordersFilter !== '')
                    <flux:badge color="yellow" size="sm">Orders: {{ str_replace('_', ' ', $ordersFilter) }}</flux:badge>
                @endif
                @if($sort !== 'newest')
                    <flux:badge color="zinc" size="sm">Sort: {{ str_replace('_', ' ', $sort) }}</flux:badge>
                @endif
            </div>
        @endif
    </flux:card>

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
                                    <flux:button size="sm" variant="ghost" icon="clock" wire:click="viewHistory({{ $customer->id }})" />
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

    @if($showHistoryModal && $historyCustomer)
        <div class="fixed inset-0 z-[10000] flex">
            <div class="flex-1 bg-black/50" wire:click="closeHistory"></div>
            <div class="w-full max-w-xl bg-white dark:bg-zinc-900 border-l border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-y-auto">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center shrink-0">
                        <flux:icon.users class="w-6 h-6 text-white" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <flux:heading size="lg" class="truncate">{{ $historyCustomer->name }}</flux:heading>
                        <flux:subheading class="truncate">{{ $historyCustomer->email ?: ($historyCustomer->mobile ?: '—') }}</flux:subheading>
                    </div>
                    <flux:button variant="ghost" icon="x-mark" wire:click="closeHistory" />
                </div>

                <div class="p-6 space-y-6">
                    <flux:card class="p-4 space-y-3">
                        <div class="flex justify-between">
                            <flux:text size="sm" class="text-zinc-500">Total Orders</flux:text>
                            <flux:text size="sm" class="font-semibold tabular-nums">{{ (int) ($historyStats['total_orders'] ?? 0) }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text size="sm" class="text-zinc-500">Total Spend</flux:text>
                            <flux:text size="sm" class="font-semibold tabular-nums">${{ number_format((float) ($historyStats['total_spend'] ?? 0), 2) }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text size="sm" class="text-zinc-500">Points Earned</flux:text>
                            <flux:text size="sm" class="font-semibold tabular-nums">{{ (int) ($historyStats['points_earned'] ?? 0) }} pts</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text size="sm" class="text-zinc-500">Points Used</flux:text>
                            <flux:text size="sm" class="font-semibold tabular-nums">{{ (int) ($historyStats['points_used'] ?? 0) }} pts</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text size="sm" class="text-zinc-500">Last Visit</flux:text>
                            <flux:text size="sm" class="font-semibold">
                                {{ $historyStats['last_visit'] ? \Illuminate\Support\Carbon::parse($historyStats['last_visit'])->format('M d, Y H:i') : '—' }}
                            </flux:text>
                        </div>
                    </flux:card>

                    <flux:card class="p-0 overflow-hidden">
                        <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                            <flux:text class="text-xs font-semibold text-zinc-500 uppercase tracking-widest">Recent Orders</flux:text>
                            <flux:text size="xs" class="text-zinc-400">Last 20</flux:text>
                        </div>
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($historyOrders as $o)
                                @php
                                    $statusColor = match($o['status']) {
                                        'completed' => 'green',
                                        'cancelled' => 'red',
                                        'processing' => 'blue',
                                        default => 'yellow',
                                    };
                                @endphp
                                <div class="p-4 flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <flux:badge size="sm" color="zinc">#{{ $o['id'] }}</flux:badge>
                                            <flux:badge size="sm" :color="$statusColor">{{ $o['status'] }}</flux:badge>
                                            @if(!empty($o['voucher_code']))
                                                <flux:badge size="sm" color="purple">{{ $o['voucher_code'] }}</flux:badge>
                                            @endif
                                            @if(((int) ($o['points_earned'] ?? 0)) > 0)
                                                <flux:badge size="sm" color="green">+{{ (int) $o['points_earned'] }} pts</flux:badge>
                                            @endif
                                            @if(((int) ($o['points_redeemed'] ?? 0)) > 0)
                                                <flux:badge size="sm" color="blue">-{{ (int) $o['points_redeemed'] }} pts</flux:badge>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-sm font-semibold text-zinc-800 dark:text-zinc-200 truncate">
                                            {{ $o['created_at'] }}
                                        </div>
                                        <div class="text-xs text-zinc-400">
                                            {{ strtoupper($o['payment_method'] ?: '—') }} • {{ (int) $o['items_count'] }} items
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-mono font-semibold tabular-nums">${{ number_format((float) $o['total_amount'], 2) }}</div>
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="printer"
                                            onclick="window.open('{{ route('pos.receipt', $o['id']) }}', '_blank', 'width=420,height=700'); event.stopPropagation();"
                                        />
                                    </div>
                                </div>
                            @empty
                                <div class="p-10 text-center">
                                    <flux:icon.receipt-percent class="w-10 h-10 text-zinc-300 dark:text-zinc-700 mx-auto" />
                                    <flux:heading class="mt-3">No orders yet</flux:heading>
                                    <flux:subheading>This customer has no recorded orders.</flux:subheading>
                                </div>
                            @endforelse
                        </div>
                    </flux:card>
                </div>
            </div>
        </div>
    @endif

</div>
