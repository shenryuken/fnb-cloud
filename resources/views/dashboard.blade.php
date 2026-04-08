<x-layouts::app :title="__('Dashboard')">
    <div class="flex flex-col gap-6 p-4 md:p-8">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">Dashboard</flux:heading>
                <flux:subheading class="text-zinc-400">
                    Welcome back. Here&apos;s what&apos;s happening with your store today.
                </flux:subheading>
            </div>
            <flux:button :href="route('pos.index')" wire:navigate variant="filled" icon="shopping-cart">
                Open POS
            </flux:button>
        </div>

        {{-- Sales Stats Row --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Today --}}
            <flux:card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text size="sm" class="text-zinc-400 font-semibold">Today&apos;s Sales</flux:text>
                    <div class="w-10 h-10 rounded-lg bg-pink-500/10 flex items-center justify-center">
                        <flux:icon.receipt-percent class="w-5 h-5 text-pink-500" />
                    </div>
                </div>
                <flux:heading size="xl" class="mb-1">
                    RM {{ number_format($todaySales ?? 0, 2) }}
                </flux:heading>
                <flux:text size="xs" class="text-zinc-500">{{ $todayOrders ?? 0 }} transactions</flux:text>
            </flux:card>

            {{-- This Week --}}
            <flux:card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text size="sm" class="text-zinc-400 font-semibold">This Week</flux:text>
                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <flux:icon.chart-bar class="w-5 h-5 text-blue-500" />
                    </div>
                </div>
                <flux:heading size="xl" class="mb-1">
                    RM {{ number_format($weekSales ?? 0, 2) }}
                </flux:heading>
                <flux:text size="xs" class="text-zinc-500">Week to date</flux:text>
            </flux:card>

            {{-- This Month --}}
            <flux:card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text size="sm" class="text-zinc-400 font-semibold">This Month</flux:text>
                    <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center">
                        <flux:icon.banknotes class="w-5 h-5 text-green-500" />
                    </div>
                </div>
                <flux:heading size="xl" class="mb-1">
                    RM {{ number_format($monthSales ?? 0, 2) }}
                </flux:heading>
                <flux:text size="xs" class="text-zinc-500">{{ now()->format('F Y') }}</flux:text>
            </flux:card>

            {{-- Quick Actions Card --}}
            <flux:card class="p-5 bg-pink-500 text-white">
                <flux:text size="sm" class="font-semibold mb-2">Quick Actions</flux:text>
                <flux:heading size="lg" class="text-white mb-4">Ready to sell?</flux:heading>
                <flux:button :href="route('pos.index')" wire:navigate variant="filled" class="bg-white text-pink-500 hover:bg-pink-50 w-full">
                    Open POS
                </flux:button>
            </flux:card>
        </div>

        {{-- Inventory & Order Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Categories --}}
            <flux:card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <flux:icon.squares-2x2 class="w-5 h-5 text-blue-500" />
                    </div>
                    <flux:badge color="blue" size="sm">Menu</flux:badge>
                </div>
                <flux:text size="sm" class="text-zinc-400 mb-1">Categories</flux:text>
                <flux:heading size="xl">{{ $categoryCount ?? 0 }}</flux:heading>
            </flux:card>

            {{-- Products --}}
            <flux:card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-violet-500/10 flex items-center justify-center">
                        <flux:icon.cube class="w-5 h-5 text-violet-500" />
                    </div>
                    <flux:badge color="violet" size="sm">Inventory</flux:badge>
                </div>
                <flux:text size="sm" class="text-zinc-400 mb-1">Products</flux:text>
                <flux:heading size="xl">{{ $productCount ?? 0 }}</flux:heading>
            </flux:card>

            {{-- Total Orders --}}
            <flux:card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center">
                        <flux:icon.shopping-cart class="w-5 h-5 text-amber-500" />
                    </div>
                    <flux:badge color="amber" size="sm">Sales</flux:badge>
                </div>
                <flux:text size="sm" class="text-zinc-400 mb-1">Total Orders</flux:text>
                <flux:heading size="xl">{{ $orderCount ?? 0 }}</flux:heading>
            </flux:card>

            {{-- Total Revenue --}}
            <flux:card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                        <flux:icon.currency-dollar class="w-5 h-5 text-emerald-500" />
                    </div>
                    <flux:badge color="emerald" size="sm">Revenue</flux:badge>
                </div>
                <flux:text size="sm" class="text-zinc-400 mb-1">Total Revenue</flux:text>
                <flux:heading size="xl">RM {{ number_format($totalRevenue ?? 0, 2) }}</flux:heading>
            </flux:card>
        </div>

        {{-- Sales Trend Chart --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <flux:card class="p-0 overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-700">
                        <div>
                            <flux:heading size="lg">Sales Trend</flux:heading>
                            <flux:text size="sm" class="text-zinc-400">Last 7 days revenue</flux:text>
                        </div>
                        <flux:icon.chart-bar class="w-5 h-5 text-zinc-400" />
                    </div>
                    <div class="p-6">
                        <div class="h-64 flex items-end justify-between gap-2">
                            @php
                                $maxValue = 35;
                                $chartData = [
                                    ['date' => 'Apr 02', 'value' => 0],
                                    ['date' => 'Apr 03', 'value' => 0],
                                    ['date' => 'Apr 04', 'value' => 0],
                                    ['date' => 'Apr 05', 'value' => 0],
                                    ['date' => 'Apr 06', 'value' => 5],
                                    ['date' => 'Apr 07', 'value' => 30],
                                    ['date' => 'Apr 08', 'value' => 10],
                                ];
                            @endphp
                            
                            <svg class="w-full h-full" viewBox="0 0 700 256" preserveAspectRatio="none">
                                <!-- Grid lines -->
                                @for($i = 0; $i <= 4; $i++)
                                    <line x1="0" y1="{{ $i * 64 }}" x2="700" y2="{{ $i * 64 }}" stroke="currentColor" class="text-zinc-800" stroke-width="1" />
                                @endfor
                                
                                <!-- Area fill -->
                                <defs>
                                    <linearGradient id="salesGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:rgb(236, 72, 153);stop-opacity:0.3" />
                                        <stop offset="100%" style="stop-color:rgb(236, 72, 153);stop-opacity:0" />
                                    </linearGradient>
                                </defs>
                                
                                @php
                                    $points = collect($chartData)->map(function($item, $index) use ($maxValue) {
                                        $x = ($index / 6) * 700;
                                        $y = 256 - (($item['value'] / 35) * 256);
                                        return "$x,$y";
                                    })->join(' ');
                                    $areaPoints = "0,256 " . $points . " 700,256";
                                @endphp
                                
                                <polyline points="{{ $points }}" fill="none" stroke="rgb(236, 72, 153)" stroke-width="3" />
                                <polygon points="{{ $areaPoints }}" fill="url(#salesGradient)" />
                                
                                <!-- Data points -->
                                @foreach($chartData as $index => $item)
                                    @php
                                        $x = ($index / 6) * 700;
                                        $y = 256 - (($item['value'] / 35) * 256);
                                    @endphp
                                    <circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="rgb(236, 72, 153)" stroke="white" stroke-width="2" />
                                @endforeach
                            </svg>
                        </div>
                        
                        <!-- X-axis labels -->
                        <div class="flex justify-between mt-4">
                            @foreach($chartData as $item)
                                <div class="text-xs text-zinc-500">{{ $item['date'] }}</div>
                            @endforeach
                        </div>
                    </div>
                </flux:card>
            </div>

            {{-- Low Stock Alerts --}}
            <div>
                <flux:card class="p-0 overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-700">
                        <div>
                            <flux:heading size="lg">Low Stock Alerts</flux:heading>
                            <flux:text size="sm" class="text-zinc-400">Products running low</flux:text>
                        </div>
                        <flux:icon.exclamation-triangle class="w-5 h-5 text-yellow-500" />
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mb-4">
                                <flux:icon.check-circle class="w-8 h-8 text-green-500" />
                            </div>
                            <flux:text class="text-zinc-400 text-center">All products are well stocked</flux:text>
                        </div>
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- Recent Sales & Top Products --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Recent Sales --}}
            <div class="lg:col-span-2">
                <flux:card class="p-0 overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-700">
                        <div>
                            <flux:heading size="lg">Recent Sales</flux:heading>
                            <flux:text size="sm" class="text-zinc-400">Latest transactions</flux:text>
                        </div>
                        <flux:button :href="route('manage.orders.index')" wire:navigate variant="ghost" size="sm">
                            View All
                        </flux:button>
                    </div>
                    <div class="divide-y divide-zinc-800">
                        @forelse($recentOrders ?? [] as $order)
                            <div class="flex items-center justify-between px-6 py-4 hover:bg-zinc-800/30">
                                <div class="flex-1">
                                    <div class="font-semibold">{{ $order->id }}</div>
                                    <div class="text-sm text-zinc-400">
                                        {{ $order->items->count() }} items · {{ $order->created_at->diffForHumans() }}
                                    </div>
                                    <div class="text-xs text-zinc-500 mt-0.5">Cash</div>
                                </div>
                                <div class="font-bold text-right">RM {{ number_format($order->total_amount, 2) }}</div>
                            </div>
                        @empty
                            <div class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <flux:icon.shopping-cart class="w-12 h-12 text-zinc-600" />
                                    <flux:text class="text-zinc-400">No transactions yet</flux:text>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </flux:card>
            </div>

            {{-- Top Selling Products --}}
            <div>
                <flux:card class="p-0 overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-700">
                        <div>
                            <flux:heading size="lg">Top Selling Products</flux:heading>
                            <flux:text size="sm" class="text-zinc-400">Best performers this month</flux:text>
                        </div>
                        <flux:icon.star class="w-5 h-5 text-yellow-500" />
                    </div>
                    <div class="divide-y divide-zinc-800">
                        @forelse($topProducts ?? [] as $i => $row)
                            <div class="flex items-center gap-4 px-6 py-4 hover:bg-zinc-800/30">
                                <div class="w-8 h-8 rounded-full bg-pink-500 flex items-center justify-center text-sm font-bold text-white shrink-0">
                                    {{ $i + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm truncate">{{ $row->product?->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-zinc-400">{{ (int) $row->total_qty }} sold</p>
                                </div>
                                <span class="font-bold text-sm">RM {{ number_format($row->total_revenue, 2) }}</span>
                            </div>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <flux:icon.chart-bar class="w-12 h-12 text-zinc-600 mx-auto mb-2" />
                                <flux:text size="sm" class="text-zinc-400">No sales data yet</flux:text>
                            </div>
                        @endforelse
                    </div>
                </flux:card>
            </div>
        </div>

    </div>
</x-layouts::app>
