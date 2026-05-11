<x-layouts::app :title="__('System Dashboard')">
    <div class="flex flex-col gap-6 p-4 md:p-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <flux:heading size="xl" level="1">System Dashboard</flux:heading>
                <flux:subheading class="text-zinc-400">Global overview across all tenants — {{ now()->format('l, F j, Y') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-2">
                <flux:badge color="purple">Global Overview</flux:badge>
                <flux:button :href="route('landlord.tenants.index')" wire:navigate variant="primary" icon="users">
                    Manage Tenants
                </flux:button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <flux:card class="p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/5 rounded-full -mr-8 -mt-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text size="sm" class="text-zinc-400 font-semibold">Total Tenants</flux:text>
                        <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                            <flux:icon.building-storefront class="w-5 h-5 text-blue-500" />
                        </div>
                    </div>
                    <flux:heading size="xl" class="tabular-nums">{{ number_format((int) ($stats['total_tenants'] ?? 0)) }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-green-500/5 rounded-full -mr-8 -mt-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text size="sm" class="text-zinc-400 font-semibold">Active Tenants</flux:text>
                        <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center">
                            <flux:icon.check-circle class="w-5 h-5 text-green-500" />
                        </div>
                    </div>
                    <flux:heading size="xl" class="tabular-nums">{{ number_format((int) ($stats['active_tenants'] ?? 0)) }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-violet-500/5 rounded-full -mr-8 -mt-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text size="sm" class="text-zinc-400 font-semibold">Tenant Users</flux:text>
                        <div class="w-10 h-10 rounded-lg bg-violet-500/10 flex items-center justify-center">
                            <flux:icon.users class="w-5 h-5 text-violet-500" />
                        </div>
                    </div>
                    <flux:heading size="xl" class="tabular-nums">{{ number_format((int) ($stats['total_users'] ?? 0)) }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/5 rounded-full -mr-8 -mt-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text size="sm" class="text-zinc-400 font-semibold">Global Orders</flux:text>
                        <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center">
                            <flux:icon.clipboard-document-list class="w-5 h-5 text-amber-500" />
                        </div>
                    </div>
                    <flux:heading size="xl" class="tabular-nums">{{ number_format((int) ($stats['total_orders'] ?? 0)) }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full -mr-8 -mt-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text size="sm" class="text-zinc-400 font-semibold">Global Revenue</flux:text>
                        <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                            <flux:icon.banknotes class="w-5 h-5 text-emerald-500" />
                        </div>
                    </div>
                    <flux:heading size="xl" class="tabular-nums">${{ number_format((float) ($stats['total_revenue'] ?? 0), 2) }}</flux:heading>
                </div>
            </flux:card>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <flux:card class="p-0 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <flux:heading size="lg">Recent Tenants</flux:heading>
                        <flux:text size="sm" class="text-zinc-400">Latest onboarded restaurants</flux:text>
                    </div>
                    <flux:button :href="route('landlord.tenants.index')" wire:navigate variant="ghost" size="sm">
                        View All
                    </flux:button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Tenant</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Slug</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Status</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($recentTenants as $tenant)
                                @php
                                    $status = $tenant->status ?? ($tenant->is_active ? 'active' : 'suspended');
                                    $badgeColor = match ($status) {
                                        'trial' => 'amber',
                                        'suspended' => 'red',
                                        default => 'green',
                                    };
                                    $statusLabel = match ($status) {
                                        'trial' => 'Trial',
                                        'suspended' => 'Suspended',
                                        default => 'Active',
                                    };
                                @endphp
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-black text-zinc-500">
                                                {{ strtoupper(substr($tenant->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-zinc-800 dark:text-zinc-100 truncate">{{ $tenant->name }}</div>
                                                <div class="text-xs text-zinc-500">#{{ $tenant->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-zinc-500">{{ $tenant->slug }}</td>
                                    <td class="py-3 px-4">
                                        <flux:badge :color="$badgeColor">{{ $statusLabel }}</flux:badge>
                                    </td>
                                    <td class="py-3 px-4 text-right text-zinc-500">{{ $tenant->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-10 px-6 text-center text-zinc-500">No tenants found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </flux:card>

            <flux:card class="p-0 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <flux:heading size="lg">Quick Actions</flux:heading>
                        <flux:text size="sm" class="text-zinc-400">Landlord tools</flux:text>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 gap-3">
                    <a href="{{ route('landlord.tenants.index') }}" wire:navigate class="flex items-center gap-3 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 hover:bg-zinc-100/60 dark:hover:bg-zinc-800/50 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500">
                            <flux:icon.users class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-zinc-800 dark:text-zinc-100">Manage Tenants</div>
                            <div class="text-sm text-zinc-500">Create, edit, suspend, or resume restaurants</div>
                        </div>
                        <flux:icon.chevron-right class="w-5 h-5 text-zinc-400 group-hover:translate-x-1 transition-transform" />
                    </a>

                    <div class="flex items-center gap-3 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 opacity-60 cursor-not-allowed">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                            <flux:icon.chart-bar class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-zinc-800 dark:text-zinc-100">System Reports</div>
                            <div class="text-sm text-zinc-500">Analytics across all tenants</div>
                        </div>
                        <flux:badge color="zinc">Soon</flux:badge>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts::app>
