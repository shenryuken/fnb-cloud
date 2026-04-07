<x-layouts::app :title="__('Dashboard')">
    <div class="flex flex-col gap-8 p-4 md:p-8">

        @if(isset($tenant) && $tenant)
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <flux:heading size="xl" level="2">Welcome, {{ auth()->user()->name }}</flux:heading>
                    <flux:subheading>
                        Here&apos;s what&apos;s happening at <strong class="text-blue-600">{{ $tenant->name }}</strong> today
                    </flux:subheading>
                </div>
                <flux:badge color="zinc" size="lg">{{ $tenant->slug }}</flux:badge>
            </div>
        @endif

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <flux:card class="p-6 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600">
                        <flux:icon.layers class="w-5 h-5" />
                    </div>
                    <flux:badge color="zinc" size="sm">Menu</flux:badge>
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500">Categories</flux:text>
                    <flux:heading size="xl" class="tracking-tight mt-0.5">{{ $categoryCount ?? 0 }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-6 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center text-violet-600">
                        <flux:icon.package class="w-5 h-5" />
                    </div>
                    <flux:badge color="zinc" size="sm">Inventory</flux:badge>
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500">Products</flux:text>
                    <flux:heading size="xl" class="tracking-tight mt-0.5">{{ $productCount ?? 0 }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-6 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600">
                        <flux:icon.shopping-cart class="w-5 h-5" />
                    </div>
                    <flux:badge color="zinc" size="sm">Sales</flux:badge>
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500">Total Orders</flux:text>
                    <flux:heading size="xl" class="tracking-tight mt-0.5">{{ $orderCount ?? 0 }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-6 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600">
                        <flux:icon.banknotes class="w-5 h-5" />
                    </div>
                    <flux:badge color="zinc" size="sm">Revenue</flux:badge>
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500">Total Revenue</flux:text>
                    <flux:heading size="xl" class="text-blue-600 tracking-tight mt-0.5">${{ number_format($totalRevenue ?? 0, 2) }}</flux:heading>
                </div>
            </flux:card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Recent Orders --}}
            <div class="lg:col-span-2">
                <flux:card>
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg">Recent Orders</flux:heading>
                        <flux:button :href="route('manage.orders.index')" wire:navigate variant="ghost" size="sm" icon-trailing="arrow-right">
                            View All
                        </flux:button>
                    </div>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>ID</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Table</flux:table.column>
                            <flux:table.column class="text-right">Total</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse($recentOrders ?? [] as $order)
                                <flux:table.row>
                                    <flux:table.cell>
                                        <flux:text class="font-semibold text-zinc-500">#{{ $order->id }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge :color="$order->status === 'completed' ? 'green' : 'yellow'" size="sm">
                                            {{ $order->status }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text size="sm">{{ $order->table_number ?? 'Walk-in' }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right">
                                        <flux:text class="font-black">${{ number_format($order->total_amount, 2) }}</flux:text>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="py-10 text-center">
                                        <flux:text class="text-zinc-400 italic">No transactions yet today.</flux:text>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </flux:card>
            </div>

            {{-- Launch POS Card --}}
            <flux:card class="bg-zinc-900 dark:bg-blue-700 text-white flex flex-col justify-between overflow-hidden relative">
                <div>
                    <flux:heading size="xl" class="text-white mb-2">Ready to Serve?</flux:heading>
                    <flux:text class="text-white/60 leading-relaxed mb-8">
                        Process orders quickly with our blazing fast Point of Sale system.
                    </flux:text>
                    <flux:button :href="route('pos.index')" wire:navigate variant="filled" class="bg-white text-zinc-900 hover:bg-zinc-100 font-black" icon="rocket">
                        Launch POS
                    </flux:button>
                </div>

                <flux:separator class="border-white/10 mt-10" />
                <div class="flex items-center justify-between mt-4">
                    <flux:text size="sm" class="text-white/40 uppercase tracking-widest text-[10px] font-black">Platform Version</flux:text>
                    <flux:text size="sm" class="text-white/40 font-black">v1.0.4</flux:text>
                </div>
            </flux:card>
        </div>

    </div>
</x-layouts::app>
