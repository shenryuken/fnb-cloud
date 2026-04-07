<x-layouts::app :title="__('Dashboard')">
    <div class="flex flex-col gap-8 p-4 md:p-8">

        {{-- Header --}}
        @if(isset($tenant) && $tenant)
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <flux:heading size="xl" level="2">Welcome, {{ auth()->user()->name }}</flux:heading>
                    <flux:subheading>
                        Here&apos;s what&apos;s happening at <strong class="text-blue-600">{{ $tenant->name }}</strong> today
                    </flux:subheading>
                </div>
                <flux:badge color="zinc">{{ $tenant->slug }}</flux:badge>
            </div>
        @endif

        {{-- Time-based Revenue Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Today --}}
            <flux:card class="p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <flux:text size="sm" class="text-zinc-500 font-semibold uppercase tracking-widest text-xs">Today&apos;s Sales</flux:text>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600">
                        <flux:icon.calendar-days class="w-4 h-4" />
                    </div>
                </div>
                <flux:heading size="xl" class="font-black tracking-tight">
                    ${{ number_format($todaySales ?? 0, 2) }}
                </flux:heading>
                <flux:text size="sm" class="text-zinc-400">{{ $todayOrders ?? 0 }} {{ Str::plural('transaction', $todayOrders ?? 0) }}</flux:text>
            </flux:card>

            {{-- This Week --}}
            <flux:card class="p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <flux:text size="sm" class="text-zinc-500 font-semibold uppercase tracking-widest text-xs">This Week</flux:text>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600">
                        <flux:icon.chart-bar class="w-4 h-4" />
                    </div>
                </div>
                <flux:heading size="xl" class="font-black tracking-tight">
                    ${{ number_format($weekSales ?? 0, 2) }}
                </flux:heading>
                <flux:text size="sm" class="text-zinc-400">{{ $weekOrders ?? 0 }} {{ Str::plural('order', $weekOrders ?? 0) }} · Week to date</flux:text>
            </flux:card>

            {{-- This Month --}}
            <flux:card class="p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <flux:text size="sm" class="text-zinc-500 font-semibold uppercase tracking-widest text-xs">This Month</flux:text>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600">
                        <flux:icon.banknotes class="w-4 h-4" />
                    </div>
                </div>
                <flux:heading size="xl" class="font-black tracking-tight">
                    ${{ number_format($monthSales ?? 0, 2) }}
                </flux:heading>
                <flux:text size="sm" class="text-zinc-400">{{ $monthOrders ?? 0 }} {{ Str::plural('order', $monthOrders ?? 0) }} · {{ now()->format('F Y') }}</flux:text>
            </flux:card>
        </div>

        {{-- KPI Count Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <flux:card class="p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600">
                        <flux:icon.layers class="w-5 h-5" />
                    </div>
                    <flux:badge color="zinc" size="sm">Menu</flux:badge>
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500">Categories</flux:text>
                    <flux:heading size="xl" class="tracking-tight mt-0.5">{{ $categoryCount ?? 0 }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center text-violet-600">
                        <flux:icon.package class="w-5 h-5" />
                    </div>
                    <flux:badge color="zinc" size="sm">Inventory</flux:badge>
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500">Products</flux:text>
                    <flux:heading size="xl" class="tracking-tight mt-0.5">{{ $productCount ?? 0 }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600">
                        <flux:icon.shopping-cart class="w-5 h-5" />
                    </div>
                    <flux:badge color="zinc" size="sm">Sales</flux:badge>
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500">Total Orders</flux:text>
                    <flux:heading size="xl" class="tracking-tight mt-0.5">{{ $orderCount ?? 0 }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600">
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

        {{-- Middle Row: Recent Orders + Top Selling --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Recent Orders --}}
            <div class="lg:col-span-2">
                <flux:card class="p-0 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                        <div>
                            <flux:heading size="lg">Recent Orders</flux:heading>
                            <flux:text size="sm" class="text-zinc-400">Latest transactions</flux:text>
                        </div>
                        <flux:button :href="route('manage.orders.index')" wire:navigate variant="ghost" size="sm" icon-trailing="arrow-right">
                            View All
                        </flux:button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                    <th class="py-3 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Order</th>
                                    <th class="py-3 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Status</th>
                                    <th class="py-3 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Customer / Table</th>
                                    <th class="py-3 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @forelse($recentOrders ?? [] as $order)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                        <td class="py-3 px-5">
                                            <span class="font-semibold text-zinc-500">#{{ $order->id }}</span>
                                        </td>
                                        <td class="py-3 px-5">
                                            @php
                                                $color = match($order->status) {
                                                    'completed' => 'green',
                                                    'cancelled' => 'red',
                                                    'processing' => 'blue',
                                                    default => 'yellow',
                                                };
                                            @endphp
                                            <flux:badge :color="$color" size="sm">{{ $order->status }}</flux:badge>
                                        </td>
                                        <td class="py-3 px-5">
                                            <span class="font-semibold">
                                                {{ $order->customer?->name ?: ($order->table_number ? 'Table ' . $order->table_number : 'Walk-in') }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-5 text-right">
                                            <span class="font-black">${{ number_format($order->total_amount, 2) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center text-zinc-400 italic text-sm">No transactions yet today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </flux:card>
            </div>

            {{-- Right Column: Top Products + POS Launch --}}
            <div class="flex flex-col gap-6">

                {{-- Top Selling Products --}}
                <flux:card class="p-0 overflow-hidden flex-1">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                        <div>
                            <flux:heading size="lg">Top Selling</flux:heading>
                            <flux:text size="sm" class="text-zinc-400">Best performers this month</flux:text>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-500">
                            <flux:icon.chart-bar class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($topProducts ?? [] as $i => $row)
                            <div class="flex items-center gap-3 px-5 py-3">
                                <div class="w-7 h-7 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xs font-black text-zinc-500 shrink-0">
                                    {{ $i + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm truncate">{{ $row->product?->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-zinc-400">{{ (int) $row->total_qty }} sold</p>
                                </div>
                                <span class="font-black text-sm text-blue-600">${{ number_format($row->total_revenue, 2) }}</span>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center">
                                <flux:icon.chart-bar class="w-8 h-8 text-zinc-300 dark:text-zinc-700 mx-auto mb-2" />
                                <flux:text size="sm" class="text-zinc-400 italic">No sales this month yet.</flux:text>
                            </div>
                        @endforelse
                    </div>
                </flux:card>

                {{-- Launch POS Card --}}
                <flux:card class="bg-blue-600 text-white flex flex-col justify-between overflow-hidden relative p-6">
                    <div>
                        <flux:heading size="xl" class="text-white mb-2">Ready to Serve?</flux:heading>
                        <flux:text class="text-white/70 leading-relaxed mb-6">
                            Process orders quickly with our blazing fast Point of Sale system.
                        </flux:text>
                        <flux:button :href="route('pos.index')" wire:navigate variant="filled" class="bg-white text-blue-700 hover:bg-blue-50 font-black" icon="rocket">
                            Launch POS
                        </flux:button>
                    </div>
                    <flux:separator class="border-white/20 mt-8" />
                    <div class="flex items-center justify-between mt-4">
                        <flux:text size="sm" class="text-white/40 uppercase tracking-widest text-[10px] font-black">Platform Version</flux:text>
                        <flux:text size="sm" class="text-white/50 font-black">v1.0.4</flux:text>
                    </div>
                </flux:card>

            </div>
        </div>

    </div>
</x-layouts::app>
