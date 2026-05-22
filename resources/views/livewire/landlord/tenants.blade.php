<div class="flex flex-col gap-6 p-4 md:p-8">
    <flux:modal name="tenant-form" wire:model="showModal" class="w-full max-w-4xl space-y-6">
        <div class="flex items-center gap-3">
            <flux:icon.building-storefront class="w-8 h-8 text-blue-600" />
            <div>
                <flux:heading size="lg">{{ $editingTenantId ? 'Update Tenant' : 'New Tenant' }}</flux:heading>
                <flux:subheading>Configure restaurant profile and access.</flux:subheading>
            </div>
        </div>

        <flux:separator />

        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:field class="md:col-span-2">
                    <flux:label>Restaurant Name</flux:label>
                    <flux:input wire:model.blur="name" placeholder="e.g. My Restaurant" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Slug</flux:label>
                    <flux:input wire:model="slug" placeholder="e.g. my-restaurant" />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label>Domain <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                    <flux:input wire:model="domain" placeholder="e.g. restaurant.com" />
                    <flux:error name="domain" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Address <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                    <flux:input wire:model="address" placeholder="Business address" />
                    <flux:error name="address" />
                </flux:field>

                <flux:field>
                    <flux:label>Phone <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                    <flux:input wire:model="phone" placeholder="Primary phone number" />
                    <flux:error name="phone" />
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="status">
                        <flux:select.option value="active">Active</flux:select.option>
                        <flux:select.option value="trial">Trial</flux:select.option>
                        <flux:select.option value="suspended">Suspended</flux:select.option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>

                <flux:field>
                    <flux:label>Plan</flux:label>
                    <flux:input wire:model="plan" placeholder="e.g. standard" />
                    <flux:error name="plan" />
                </flux:field>

                <flux:field>
                    <flux:label>Trial Ends At <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                    <flux:input type="datetime-local" wire:model="trial_ends_at" />
                    <flux:error name="trial_ends_at" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Suspended Reason <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                    <flux:input wire:model="suspended_reason" placeholder="Shown when suspended" />
                    <flux:error name="suspended_reason" />
                </flux:field>

                @if(!$editingTenantId)
                    <flux:separator class="md:col-span-2" />

                    <flux:field class="md:col-span-2">
                        <flux:label>Initial Admin Name</flux:label>
                        <flux:input wire:model="admin_name" placeholder="Admin full name" />
                        <flux:error name="admin_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Initial Admin Email</flux:label>
                        <flux:input type="email" wire:model="admin_email" placeholder="admin@example.com" />
                        <flux:error name="admin_email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Initial Admin Password</flux:label>
                        <flux:input type="text" wire:model="admin_password" />
                    </flux:field>
                @endif
            </div>

            <flux:separator />

            <div class="flex justify-end gap-3">
                <flux:button type="button" variant="ghost" wire:click="closeModal">Cancel</flux:button>
                <flux:button type="submit" variant="primary">{{ $editingTenantId ? 'Save' : 'Create Tenant' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Manage Tenants</flux:heading>
            <flux:subheading>Global administration of restaurant accounts.</flux:subheading>
        </div>
        <flux:button :href="route('landlord.tenants.index', ['create' => 1])" variant="primary" icon="plus">
            New Tenant
        </flux:button>
    </div>

    <flux:card class="p-4">
        <div class="flex flex-col lg:flex-row gap-3">
            <div class="flex-1 min-w-[280px]">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by restaurant name or slug..."
                    icon="magnifying-glass"
                    clearable
                    class="w-full"
                />
            </div>
        </div>
    </flux:card>

    <flux:card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Tenant</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Slug / Domain</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Status</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($tenants as $tenant)
                        @php
                            $status = $tenant->status ?? ($tenant->is_active ? 'active' : 'suspended');
                            $statusColor = match ($status) {
                                'trial' => 'amber',
                                'suspended' => 'red',
                                default => 'green',
                            };
                        @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-black text-zinc-500 shrink-0">
                                        {{ strtoupper(substr($tenant->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-zinc-800 dark:text-zinc-100 truncate">{{ $tenant->name }}</div>
                                        <div class="text-xs text-zinc-500">#{{ $tenant->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-zinc-700 dark:text-zinc-200 font-semibold">/{{ $tenant->slug }}</div>
                                @if($tenant->domain)
                                    <div class="text-xs text-zinc-500">{{ $tenant->domain }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <flux:badge :color="$statusColor" size="sm">{{ ucfirst($status) }}</flux:badge>
                                    <flux:button size="xs" variant="ghost" wire:click="toggleStatus({{ $tenant->id }})">
                                        Toggle
                                    </flux:button>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil-square"
                                        :href="route('landlord.tenants.index', ['edit' => $tenant->id])"
                                    >
                                        Edit
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-16 px-6 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <flux:icon.building-storefront class="w-10 h-10 text-zinc-300 dark:text-zinc-700" />
                                    <flux:heading>No tenants found</flux:heading>
                                    <flux:subheading>Create your first tenant to get started.</flux:subheading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div class="px-6 py-4 border-t border-zinc-100 dark:border-zinc-800">
                {{ $tenants->links() }}
            </div>
        @endif
    </flux:card>
</div>
