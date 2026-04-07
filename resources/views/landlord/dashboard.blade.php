<x-layouts::app :title="__('System Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-neutral-800 dark:text-neutral-100">
                System Administrator Dashboard
            </h2>
            <span class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-sm font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-100">
                Global Overview
            </span>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-5">
            <!-- Total Tenants -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Total Tenants</p>
                <h3 class="mt-1 text-3xl font-bold text-neutral-900 dark:text-neutral-100">{{ $stats['total_tenants'] }}</h3>
            </div>

            <!-- Active Tenants -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Active Tenants</p>
                <h3 class="mt-1 text-3xl font-bold text-neutral-900 dark:text-neutral-100">{{ $stats['active_tenants'] }}</h3>
            </div>

            <!-- Total Users -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Tenant Users</p>
                <h3 class="mt-1 text-3xl font-bold text-neutral-900 dark:text-neutral-100">{{ $stats['total_users'] }}</h3>
            </div>

            <!-- Total Orders -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Global Orders</p>
                <h3 class="mt-1 text-3xl font-bold text-neutral-900 dark:text-neutral-100">{{ $stats['total_orders'] }}</h3>
            </div>

            <!-- Global Revenue -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Global Revenue</p>
                <h3 class="mt-1 text-3xl font-bold text-neutral-900 dark:text-neutral-100">${{ number_format($stats['total_revenue'], 2) }}</h3>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Recent Tenants -->
            <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                    <h4 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">Recent Tenants</h4>
                    <a href="{{ route('landlord.tenants.index') }}" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-neutral-50 dark:bg-neutral-900">
                                <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Tenant Name</th>
                                <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Slug</th>
                                <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Status</th>
                                <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @forelse($recentTenants as $tenant)
                                <tr>
                                    <td class="px-6 py-4 font-medium">{{ $tenant->name }}</td>
                                    <td class="px-6 py-4 text-neutral-500">{{ $tenant->slug }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                                            {{ $tenant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-neutral-500">{{ $tenant->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-neutral-500">No tenants found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                <h4 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 mb-4">Quick Actions</h4>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('landlord.tenants.index') }}" class="flex items-center gap-3 p-4 rounded-xl bg-neutral-50 dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                            <flux:icon.users class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-neutral-800 dark:text-neutral-100">Manage Tenants</p>
                            <p class="text-sm text-neutral-500">Create, edit, or disable restaurant tenants</p>
                        </div>
                        <flux:icon.chevron-right class="w-5 h-5 text-neutral-400 group-hover:translate-x-1 transition-transform" />
                    </a>
                    
                    <div class="flex items-center gap-3 p-4 rounded-xl bg-neutral-50 dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-700 opacity-50 cursor-not-allowed">
                        <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600">
                            <flux:icon.chart-bar class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-neutral-800 dark:text-neutral-100">System Reports</p>
                            <p class="text-sm text-neutral-500">View detailed analytics across all tenants</p>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-widest bg-neutral-200 dark:bg-neutral-700 px-2 py-1 rounded">Soon</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>