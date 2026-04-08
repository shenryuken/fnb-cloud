<x-layouts::app :title="__('Dashboard')">
    <div class="flex flex-col gap-8 p-4 md:p-8">

        {{-- Header --}}
        @if(isset($tenant) && $tenant)
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <flux:heading size="xl" level="2" class="bg-gradient-to-r from-zinc-900 to-zinc-700 dark:from-white dark:to-zinc-300 bg-clip-text text-transparent">
                        Welcome, {{ auth()->user()->name }}
                    </flux:heading>
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
            <flux:card class="p-5 flex flex-col gap-3 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 border-l-4 border-blue-500 dark:border-blue-400 bg-gradient-to-br from-white to-blue-50/30 dark:from-zinc-900 dark:to-blue-950/10">
                <div class="flex items-center justify-between">
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400 font-bold uppercase tracking-widest text-xs">Today&apos;s Sales</flux:text>
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white shadow-md">
                        <flux:icon.calendar-days class="w-5 h-5" />
                    </div>
                </div>
                <flux:heading size="xl" class="font-black tracking-tight text-2xl">
                    ${{ number_format($todaySales ?? 0, 2) }}
                </flux:heading>
                <div class="flex items-center gap-2">
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $todayOrders ?? 0 }} {{ Str::plural('transaction', $todayOrders ?? 0) }}</flux:text>
                    @if(($todayOrders ?? 0) > 0)
                        <div class="flex items-center gap-0.5 text-green-600 text-xs font-bold">
                            <flux:icon.arrow-trending-up class="w-3 h-3" />
                        </div>
                    @endif
                </div>
            </flux:card>

            {{-- This Week --}}
            <flux:card class="p-5 flex flex-col gap-3 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 border-l-4 border-amber-500 dark:border-amber-400 bg-gradient-to-br from-white to-amber-50/30 dark:from-zinc-900 dark:to-amber-950/10">
                <div class="flex items-center justify-between">
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400 font-bold uppercase tracking-widest text-xs">This Week</flux:text>
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center text-white shadow-md">
                        <flux:icon.chart-bar class="w-5 h-5" />
                    </div>
                </div>
                <flux:heading size="xl" class="font-black tracking-tight text-2xl">
                    ${{ number_format($weekSales ?? 0, 2) }}
                </flux:heading>
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $weekOrders ?? 0 }} {{ Str::plural('order', $weekOrders ?? 0) }} · Week to date</flux:text>
            </flux:card>

            {{-- This Month --}}
            <flux:card class="p-5 flex flex-col gap-3 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 border-l-4 border-emerald-500 dark:border-emerald-400 bg-gradient-to-br from-white to-emerald-50/30 dark:from-zinc-900 dark:to-emerald-950/10">
                <div class="flex items-center justify-between">
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400 font-bold uppercase tracking-widest text-xs">This Month</flux:text>
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white shadow-md">
                        <flux:icon.banknotes class="w-5 h-5" />
                    </div>
                </div>
                <flux:heading size="xl" class="font-black tracking-tight text-2xl">
                    ${{ number_format($monthSales ?? 0, 2) }}
                </flux:heading>
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $monthOrders ?? 0 }} {{ Str::plural('order', $monthOrders ?? 0) }} · {{ now()->format('F Y') }}</flux:text>
            </flux:card>
        </div>

        {{-- KPI Count Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <flux:card class="p-5 flex flex-col gap-3 hover:shadow-md hover:scale-[1.02] transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/10 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                        <flux:icon.layers class="w-6 h-6" />
                    </div>
                    <flux:badge color="blue" size="sm" class="font-bold">Menu</flux:badge>
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500 font-medium">Categories</flux:text>
                    <flux:heading size="xl" class="tracking-tight mt-1 text-2xl font-black">{{ $categoryCount ?? 0 }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-5 flex flex-col gap-3 hover:shadow-md hover:scale-[1.02] transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-violet-100 to-violet-50 dark:from-violet-900/30 dark:to-violet-900/10 flex items-center justify-center text-violet-600 group-hover:scale-110 transition-transform">
                        <flux:icon.cube class="w-6 h-6" />
                    </div>
                    <flux:badge color="violet" size="sm" class="font-bold">Inventory</flux:badge>
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500 font-medium">Products</flux:text>
                    <flux:heading size="xl" class="tracking-tight mt-1 text-2xl font-black">{{ $productCount ?? 0 }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-5 flex flex-col gap-3 hover:shadow-md hover:scale-[1.02] transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-100 to-amber-50 dark:from-amber-900/30 dark:to-amber-900/10 flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform">
                        <flux:icon.shopping-cart class="w-6 h-6" />
                    </div>
                    <flux:badge color="amber" size="sm" class="font-bold">Sales</flux:badge>
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500 font-medium">Total Orders</flux:text>
                    <flux:heading size="xl" class="tracking-tight mt-1 text-2xl font-black">{{ $orderCount ?? 0 }}</flux:heading>
                </div>
            </flux:card>

            <flux:card class="p-5 flex flex-col gap-3 hover:shadow-md hover:scale-[1.02] transition-all duration-200 cursor-pointer group bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/20 dark:to-zinc-900 border-2 border-emerald-200 dark:border-emerald-900/50">
                <div class="flex items-center justify-between">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white shadow-md group-hover:scale-110 transition-transform">
                        <flux:icon.currency-dollar class="w-6 h-6" />
                    </div>
                    <flux:badge color="emerald" size="sm" class="font-bold">Revenue</flux:badge>
                </div>
                <div>
                    <flux:text size="sm" class="text-emerald-700 dark:text-emerald-400 font-medium">Total Revenue</flux:text>
                    <flux:heading size="xl" class="text-emerald-600 dark:text-emerald-400 tracking-tight mt-1 text-2xl font-black">${{ number_format($totalRevenue ?? 0, 2) }}</flux:heading>
                </div>
            </flux:card>
        </div>

        {{-- Middle Row: Recent Orders + Top Selling --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Recent Orders --}}
            <div class="lg:col-span-2">
                <flux:card class="p-0 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-zinc-50 to-white dark:from-zinc-800/50 dark:to-zinc-900 border-b-2 border-zinc-100 dark:border-zinc-800">
                        <div>
                            <flux:heading size="lg" class="font-black">Recent Orders</flux:heading>
                            <flux:text size="sm" class="text-zinc-500">Latest transactions</flux:text>
                        </div>
                        <flux:button :href="route('manage.orders.index')" wire:navigate variant="ghost" size="sm" icon-trailing="arrow-right" class="font-bold">
                            View All
                        </flux:button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="bg-zinc-50 dark:bg-zinc-900/50">
                                    <th class="py-4 px-6 text-xs font-black text-zinc-600 dark:text-zinc-400 uppercase tracking-widest">Order</th>
                                    <th class="py-4 px-6 text-xs font-black text-zinc-600 dark:text-zinc-400 uppercase tracking-widest">Status</th>
                                    <th class="py-4 px-6 text-xs font-black text-zinc-600 dark:text-zinc-400 uppercase tracking-widest">Customer / Table</th>
                                    <th class="py-4 px-6 text-xs font-black text-zinc-600 dark:text-zinc-400 uppercase tracking-widest text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @forelse($recentOrders ?? [] as $order)
                                    <tr class="hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-transparent dark:hover:from-blue-950/20 dark:hover:to-transparent transition-all duration-200 cursor-pointer group">
                                        <td class="py-4 px-6">
                                            <span class="font-bold text-zinc-700 dark:text-zinc-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">#{{ $order->id }}</span>
                                        </td>
                                        <td class="py-4 px-6">
                                            @php
                                                $color = match($order->status) {
                                                    'completed' => 'green',
                                                    'cancelled' => 'red',
                                                    'processing' => 'blue',
                                                    default => 'yellow',
                                                };
                                            @endphp
                                            <flux:badge :color="$color" size="sm" class="font-bold">{{ $order->status }}</flux:badge>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="font-semibold text-zinc-700 dark:text-zinc-300">
                                                {{ $order->customer?->name ?: ($order->table_number ? 'Table ' . $order->table_number : 'Walk-in') }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <span class="font-black text-zinc-900 dark:text-white">${{ number_format($order->total_amount, 2) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-16 text-center">
                                            <div class="flex flex-col items-center gap-3">
                                                <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                                                    <flux:icon.shopping-cart class="w-8 h-8 text-zinc-300 dark:text-zinc-600" />
                                                </div>
                                                <flux:text class="text-zinc-400 italic">No transactions yet today.</flux:text>
                                            </div>
                                        </td>
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
                <flux:card class="p-0 overflow-hidden flex-1 hover:shadow-lg transition-shadow duration-300">
                    <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-amber-50 to-white dark:from-amber-950/10 dark:to-zinc-900 border-b-2 border-amber-100 dark:border-amber-900/30">
                        <div>
                            <flux:heading size="lg" class="font-black">Top Selling</flux:heading>
                            <flux:text size="sm" class="text-zinc-500">Best performers this month</flux:text>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center text-white shadow-md">
                            <flux:icon.fire class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($topProducts ?? [] as $i => $row)
                            <div class="flex items-center gap-3 px-6 py-4 hover:bg-gradient-to-r hover:from-amber-50/50 hover:to-transparent dark:hover:from-amber-950/10 dark:hover:to-transparent transition-all duration-200 group cursor-pointer">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center text-xs font-black text-white shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                                    {{ $i + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm truncate text-zinc-800 dark:text-zinc-200">{{ $row->product?->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">{{ (int) $row->total_qty }} sold</p>
                                </div>
                                <span class="font-black text-sm text-amber-600 dark:text-amber-400">${{ number_format($row->total_revenue, 2) }}</span>
                            </div>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-full bg-amber-100 dark:bg-amber-900/20 flex items-center justify-center">
                                        <flux:icon.chart-bar class="w-7 h-7 text-amber-300 dark:text-amber-700" />
                                    </div>
                                    <flux:text size="sm" class="text-zinc-400 italic">No sales this month yet.</flux:text>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </flux:card>

                {{-- Launch POS Card --}}
                <flux:card class="bg-gradient-to-br from-blue-600 via-blue-600 to-blue-700 text-white flex flex-col justify-between overflow-hidden relative p-6 hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 cursor-pointer group">
                    {{-- Background decoration --}}
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2 group-hover:scale-150 transition-transform duration-500"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                <flux:icon.rocket-launch class="w-5 h-5 text-white" />
                            </div>
                            <flux:badge color="white" size="sm" class="font-bold">Quick Access</flux:badge>
                        </div>
                        <flux:heading size="xl" class="text-white mb-2 font-black">Ready to Serve?</flux:heading>
                        <flux:text class="text-blue-100 leading-relaxed mb-6 text-sm">
                            Process orders quickly with our blazing fast Point of Sale system.
                        </flux:text>
                        <flux:button :href="route('pos.index')" wire:navigate variant="filled" class="bg-white text-blue-700 hover:bg-blue-50 font-black shadow-lg hover:shadow-xl transition-all" icon="bolt">
                            Launch POS
                        </flux:button>
                    </div>
                    <flux:separator class="border-white/20 mt-8 relative z-10" />
                    <div class="flex items-center justify-between mt-4 relative z-10">
                        <flux:text size="sm" class="text-blue-200/60 uppercase tracking-widest text-[10px] font-black">Platform Version</flux:text>
                        <flux:text size="sm" class="text-blue-100 font-black">v1.0.4</flux:text>
                    </div>
                </flux:card>

            </div>
        </div>

    </div>
</x-layouts::app>
