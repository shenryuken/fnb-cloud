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
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between px-6 py-4 border-b border-zinc-700 gap-3">
                        <div>
                            <flux:heading size="lg">Sales Trend</flux:heading>
                            <flux:text size="sm" class="text-zinc-400" x-text="periodDescription">Last 7 days revenue</flux:text>
                        </div>
                        <div class="flex gap-1 bg-zinc-800 p-1 rounded-lg" x-data="{ period: 'weekly' }">
                            <button 
                                @click="period = 'weekly'; $dispatch('chart-period', 'weekly')" 
                                :class="period === 'weekly' ? 'bg-pink-500 text-white' : 'text-zinc-400 hover:text-white'"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors">
                                Weekly
                            </button>
                            <button 
                                @click="period = 'monthly'; $dispatch('chart-period', 'monthly')" 
                                :class="period === 'monthly' ? 'bg-pink-500 text-white' : 'text-zinc-400 hover:text-white'"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors">
                                Monthly
                            </button>
                            <button 
                                @click="period = 'yearly'; $dispatch('chart-period', 'yearly')" 
                                :class="period === 'yearly' ? 'bg-pink-500 text-white' : 'text-zinc-400 hover:text-white'"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors">
                                Yearly
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6" 
                         x-data="{
                            period: 'weekly',
                            periodDescription: 'Last 7 days revenue',
                            chartData: {
                                weekly: @js($weeklyChartData ?? []),
                                monthly: @js($monthlyChartData ?? []),
                                yearly: @js($yearlyChartData ?? [])
                            },
                            descriptions: {
                                weekly: 'Last 7 days revenue',
                                monthly: 'Last 4 weeks revenue',
                                yearly: 'Last 12 months revenue'
                            },
                            get currentData() { return this.chartData[this.period] || []; },
                            get maxValue() { 
                                const max = Math.max(...this.currentData.map(d => d.value));
                                return max > 0 ? max : 100;
                            },
                            getY(value) { return 240 - ((value / this.maxValue) * 220); },
                            getX(index) { 
                                const count = this.currentData.length;
                                return count > 1 ? (index / (count - 1)) * 680 + 10 : 350;
                            },
                            get polylinePoints() {
                                return this.currentData.map((d, i) => `${this.getX(i)},${this.getY(d.value)}`).join(' ');
                            },
                            get polygonPoints() {
                                if (this.currentData.length === 0) return '';
                                const first = this.getX(0);
                                const last = this.getX(this.currentData.length - 1);
                                return `${first},240 ${this.polylinePoints} ${last},240`;
                            },
                            formatValue(val) {
                                return 'RM ' + val.toLocaleString('en-MY', { minimumFractionDigits: 2 });
                            }
                         }"
                         @chart-period.window="period = $event.detail; periodDescription = descriptions[$event.detail]">
                        
                        {{-- Y-axis labels --}}
                        <div class="flex gap-4">
                            <div class="flex flex-col justify-between h-60 text-right pr-2 text-xs text-zinc-500 w-16 shrink-0">
                                <span x-text="formatValue(maxValue)"></span>
                                <span x-text="formatValue(maxValue * 0.75)"></span>
                                <span x-text="formatValue(maxValue * 0.5)"></span>
                                <span x-text="formatValue(maxValue * 0.25)"></span>
                                <span>RM 0</span>
                            </div>
                            
                            <div class="flex-1">
                                <div class="h-60">
                                    <svg class="w-full h-full" viewBox="0 0 700 250" preserveAspectRatio="none">
                                        {{-- Grid lines --}}
                                        <line x1="10" y1="20" x2="690" y2="20" stroke="currentColor" class="text-zinc-800" stroke-width="1" />
                                        <line x1="10" y1="75" x2="690" y2="75" stroke="currentColor" class="text-zinc-800" stroke-width="1" />
                                        <line x1="10" y1="130" x2="690" y2="130" stroke="currentColor" class="text-zinc-800" stroke-width="1" />
                                        <line x1="10" y1="185" x2="690" y2="185" stroke="currentColor" class="text-zinc-800" stroke-width="1" />
                                        <line x1="10" y1="240" x2="690" y2="240" stroke="currentColor" class="text-zinc-800" stroke-width="1" />
                                        
                                        {{-- Gradient definition --}}
                                        <defs>
                                            <linearGradient id="salesGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                                <stop offset="0%" style="stop-color:rgb(236, 72, 153);stop-opacity:0.3" />
                                                <stop offset="100%" style="stop-color:rgb(236, 72, 153);stop-opacity:0" />
                                            </linearGradient>
                                        </defs>
                                        
                                        {{-- Area fill --}}
                                        <polygon :points="polygonPoints" fill="url(#salesGradient)" />
                                        
                                        {{-- Line --}}
                                        <polyline :points="polylinePoints" fill="none" stroke="rgb(236, 72, 153)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                        
                                        {{-- Data points --}}
                                        <template x-for="(item, index) in currentData" :key="index">
                                            <circle :cx="getX(index)" :cy="getY(item.value)" r="5" fill="rgb(236, 72, 153)" stroke="white" stroke-width="2" />
                                        </template>
                                    </svg>
                                </div>
                                
                                {{-- X-axis labels --}}
                                <div class="flex justify-between mt-3 px-2">
                                    <template x-for="(item, index) in currentData" :key="'label-' + index">
                                        <div class="text-xs text-zinc-500 text-center" x-text="item.label"></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Summary --}}
                        <div class="mt-4 pt-4 border-t border-zinc-800 flex flex-wrap gap-4 text-sm">
                            <div>
                                <span class="text-zinc-400">Total: </span>
                                <span class="font-semibold text-white" x-text="formatValue(currentData.reduce((sum, d) => sum + d.value, 0))"></span>
                            </div>
                            <div>
                                <span class="text-zinc-400">Average: </span>
                                <span class="font-semibold text-white" x-text="formatValue(currentData.length > 0 ? currentData.reduce((sum, d) => sum + d.value, 0) / currentData.length : 0)"></span>
                            </div>
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
