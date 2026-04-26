<x-layouts::app :title="__('Dashboard')">
    <div class="flex flex-col gap-6 p-4 md:p-8">

        {{-- Header with greeting --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" level="1">
                    @php
                        $hour = now()->hour;
                        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
                    @endphp
                    {{ $greeting }}, {{ auth()->user()->name }}
                </flux:heading>
                <flux:subheading class="text-zinc-400">
                    Here&apos;s what&apos;s happening with your store today — {{ now()->format('l, F j, Y') }}
                </flux:subheading>
            </div>
            <div class="flex items-center gap-2">
                <flux:button :href="route('kds.index')" wire:navigate variant="ghost" icon="fire">
                    Kitchen
                </flux:button>
                <flux:button :href="route('pos.index')" wire:navigate variant="primary" icon="shopping-cart">
                    Open POS
                </flux:button>
            </div>
        </div>

        {{-- Live Kitchen Status Bar --}}
        @if(($pendingKds ?? 0) > 0 || ($readyOrders ?? 0) > 0)
        <div class="flex items-center gap-3 p-4 rounded-xl bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-red-500/10 border border-amber-500/20">
            <div class="w-10 h-10 rounded-lg bg-amber-500 flex items-center justify-center shrink-0">
                <flux:icon.fire class="w-5 h-5 text-white animate-pulse" />
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-zinc-100">Kitchen Activity</p>
                <p class="text-xs text-zinc-400">
                    @if(($preparingOrders ?? 0) > 0)
                        <span class="text-orange-400">{{ $preparingOrders }} preparing</span>
                    @endif
                    @if(($preparingOrders ?? 0) > 0 && ($readyOrders ?? 0) > 0) · @endif
                    @if(($readyOrders ?? 0) > 0)
                        <span class="text-green-400">{{ $readyOrders }} ready to serve</span>
                    @endif
                    @if((($preparingOrders ?? 0) > 0 || ($readyOrders ?? 0) > 0) && (($pendingKds ?? 0) - ($preparingOrders ?? 0) - ($readyOrders ?? 0)) > 0) · @endif
                    @php $pending = ($pendingKds ?? 0) - ($preparingOrders ?? 0); @endphp
                    @if($pending > 0)
                        <span class="text-amber-400">{{ $pending }} pending</span>
                    @endif
                </p>
            </div>
            <flux:button :href="route('kds.index')" wire:navigate size="sm" variant="filled" class="bg-amber-500 hover:bg-amber-600">
                View KDS
            </flux:button>
        </div>
        @endif

        {{-- Today's Performance Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Today's Sales --}}
            <flux:card class="p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-pink-500/5 rounded-full -mr-8 -mt-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text size="sm" class="text-zinc-400 font-semibold">Today&apos;s Sales</flux:text>
                        <div class="w-10 h-10 rounded-lg bg-pink-500/10 flex items-center justify-center">
                            <flux:icon.banknotes class="w-5 h-5 text-pink-500" />
                        </div>
                    </div>
                    <flux:heading size="xl" class="mb-1 tabular-nums">
                        RM {{ number_format($todaySales ?? 0, 2) }}
                    </flux:heading>
                    <div class="flex items-center gap-2">
                        @if(($salesChange ?? 0) != 0)
                            <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded {{ ($salesChange ?? 0) >= 0 ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">
                                <flux:icon :name="($salesChange ?? 0) >= 0 ? 'arrow-trending-up' : 'arrow-trending-down'" class="w-3 h-3" />
                                {{ abs(round($salesChange ?? 0, 1)) }}%
                            </span>
                        @endif
                        <flux:text size="xs" class="text-zinc-500">vs yesterday</flux:text>
                    </div>
                </div>
            </flux:card>

            {{-- Today's Orders --}}
            <flux:card class="p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/5 rounded-full -mr-8 -mt-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text size="sm" class="text-zinc-400 font-semibold">Orders Today</flux:text>
                        <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                            <flux:icon.shopping-bag class="w-5 h-5 text-blue-500" />
                        </div>
                    </div>
                    <flux:heading size="xl" class="mb-1 tabular-nums">
                        {{ $todayOrders ?? 0 }}
                    </flux:heading>
                    <div class="flex items-center gap-2">
                        @if(($ordersChange ?? 0) != 0)
                            <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded {{ ($ordersChange ?? 0) >= 0 ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">
                                <flux:icon :name="($ordersChange ?? 0) >= 0 ? 'arrow-trending-up' : 'arrow-trending-down'" class="w-3 h-3" />
                                {{ abs(round($ordersChange ?? 0, 1)) }}%
                            </span>
                        @endif
                        <flux:text size="xs" class="text-zinc-500">vs yesterday</flux:text>
                    </div>
                </div>
            </flux:card>

            {{-- Average Order Value --}}
            <flux:card class="p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full -mr-8 -mt-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text size="sm" class="text-zinc-400 font-semibold">Avg Order Value</flux:text>
                        <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                            <flux:icon.calculator class="w-5 h-5 text-emerald-500" />
                        </div>
                    </div>
                    <flux:heading size="xl" class="mb-1 tabular-nums">
                        RM {{ number_format($todayAvgOrder ?? 0, 2) }}
                    </flux:heading>
                    <flux:text size="xs" class="text-zinc-500">per transaction</flux:text>
                </div>
            </flux:card>

            {{-- This Week --}}
            <flux:card class="p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-violet-500/5 rounded-full -mr-8 -mt-8"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text size="sm" class="text-zinc-400 font-semibold">This Week</flux:text>
                        <div class="w-10 h-10 rounded-lg bg-violet-500/10 flex items-center justify-center">
                            <flux:icon.chart-bar class="w-5 h-5 text-violet-500" />
                        </div>
                    </div>
                    <flux:heading size="xl" class="mb-1 tabular-nums">
                        RM {{ number_format($weekSales ?? 0, 2) }}
                    </flux:heading>
                    <div class="flex items-center gap-2">
                        @if(($weekChange ?? 0) != 0)
                            <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded {{ ($weekChange ?? 0) >= 0 ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">
                                <flux:icon :name="($weekChange ?? 0) >= 0 ? 'arrow-trending-up' : 'arrow-trending-down'" class="w-3 h-3" />
                                {{ abs(round($weekChange ?? 0, 1)) }}%
                            </span>
                        @endif
                        <flux:text size="xs" class="text-zinc-500">vs last week</flux:text>
                    </div>
                </div>
            </flux:card>
        </div>

        {{-- Quick Actions Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
            <a href="{{ route('pos.index') }}" wire:navigate class="group flex flex-col items-center gap-2 p-4 rounded-xl bg-zinc-800/50 hover:bg-pink-500/10 border border-zinc-700/50 hover:border-pink-500/30 transition-all">
                <div class="w-12 h-12 rounded-xl bg-pink-500/10 group-hover:bg-pink-500/20 flex items-center justify-center transition-colors">
                    <flux:icon.shopping-cart class="w-6 h-6 text-pink-500" />
                </div>
                <span class="text-xs font-semibold text-zinc-300 group-hover:text-pink-400 transition-colors">New Order</span>
            </a>
            <a href="{{ route('kds.index') }}" wire:navigate class="group flex flex-col items-center gap-2 p-4 rounded-xl bg-zinc-800/50 hover:bg-orange-500/10 border border-zinc-700/50 hover:border-orange-500/30 transition-all">
                <div class="w-12 h-12 rounded-xl bg-orange-500/10 group-hover:bg-orange-500/20 flex items-center justify-center transition-colors">
                    <flux:icon.fire class="w-6 h-6 text-orange-500" />
                </div>
                <span class="text-xs font-semibold text-zinc-300 group-hover:text-orange-400 transition-colors">Kitchen</span>
            </a>
            <a href="{{ route('manage.orders.index') }}" wire:navigate class="group flex flex-col items-center gap-2 p-4 rounded-xl bg-zinc-800/50 hover:bg-blue-500/10 border border-zinc-700/50 hover:border-blue-500/30 transition-all">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 group-hover:bg-blue-500/20 flex items-center justify-center transition-colors">
                    <flux:icon.clipboard-document-list class="w-6 h-6 text-blue-500" />
                </div>
                <span class="text-xs font-semibold text-zinc-300 group-hover:text-blue-400 transition-colors">Orders</span>
            </a>
            <a href="{{ route('reports.sales') }}" wire:navigate class="group flex flex-col items-center gap-2 p-4 rounded-xl bg-zinc-800/50 hover:bg-emerald-500/10 border border-zinc-700/50 hover:border-emerald-500/30 transition-all">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 group-hover:bg-emerald-500/20 flex items-center justify-center transition-colors">
                    <flux:icon.chart-pie class="w-6 h-6 text-emerald-500" />
                </div>
                <span class="text-xs font-semibold text-zinc-300 group-hover:text-emerald-400 transition-colors">Reports</span>
            </a>
            <a href="{{ route('manage.products.index') }}" wire:navigate class="group flex flex-col items-center gap-2 p-4 rounded-xl bg-zinc-800/50 hover:bg-violet-500/10 border border-zinc-700/50 hover:border-violet-500/30 transition-all">
                <div class="w-12 h-12 rounded-xl bg-violet-500/10 group-hover:bg-violet-500/20 flex items-center justify-center transition-colors">
                    <flux:icon.cube class="w-6 h-6 text-violet-500" />
                </div>
                <span class="text-xs font-semibold text-zinc-300 group-hover:text-violet-400 transition-colors">Menu</span>
            </a>
            <a href="{{ route('manage.customers.index') }}" wire:navigate class="group flex flex-col items-center gap-2 p-4 rounded-xl bg-zinc-800/50 hover:bg-cyan-500/10 border border-zinc-700/50 hover:border-cyan-500/30 transition-all">
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 group-hover:bg-cyan-500/20 flex items-center justify-center transition-colors">
                    <flux:icon.users class="w-6 h-6 text-cyan-500" />
                </div>
                <span class="text-xs font-semibold text-zinc-300 group-hover:text-cyan-400 transition-colors">Customers</span>
            </a>
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
                                Daily
                            </button>
                            <button 
                                @click="period = 'monthly'; $dispatch('chart-period', 'monthly')" 
                                :class="period === 'monthly' ? 'bg-pink-500 text-white' : 'text-zinc-400 hover:text-white'"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors">
                                Weekly
                            </button>
                            <button 
                                @click="period = 'yearly'; $dispatch('chart-period', 'yearly')" 
                                :class="period === 'yearly' ? 'bg-pink-500 text-white' : 'text-zinc-400 hover:text-white'"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors">
                                Monthly
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
                                monthly: '52 weeks of ' + new Date().getFullYear(),
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
                                        
                                        {{-- Data points (hidden for large datasets) --}}
                                        <template x-if="currentData.length <= 14">
                                            <g>
                                                <template x-for="(item, index) in currentData" :key="index">
                                                    <circle :cx="getX(index)" :cy="getY(item.value)" r="5" fill="rgb(236, 72, 153)" stroke="white" stroke-width="2" />
                                                </template>
                                            </g>
                                        </template>
                                    </svg>
                                </div>
                                
                                {{-- X-axis labels (show every 4th for large datasets) --}}
                                <div class="flex justify-between mt-3 px-2 overflow-hidden">
                                    <template x-for="(item, index) in currentData" :key="'label-' + index">
                                        <div class="text-xs text-zinc-500 text-center"
                                             x-show="currentData.length <= 14 || index % 4 === 0"
                                             :class="item.isCurrent ? 'text-pink-400 font-semibold' : ''"
                                             x-text="item.label">
                                        </div>
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

            {{-- Store Stats Summary --}}
            <div class="space-y-4">
                {{-- Month Summary --}}
                <flux:card class="p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center">
                            <flux:icon.calendar class="w-5 h-5 text-green-500" />
                        </div>
                        <div>
                            <flux:text size="sm" class="text-zinc-400">This Month</flux:text>
                            <flux:heading size="lg" class="tabular-nums">RM {{ number_format($monthSales ?? 0, 2) }}</flux:heading>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-400">{{ $monthOrders ?? 0 }} orders</span>
                        <span class="text-zinc-500">{{ now()->format('F Y') }}</span>
                    </div>
                </flux:card>

                {{-- Store Stats --}}
                <flux:card class="p-5">
                    <flux:heading size="sm" class="mb-4 text-zinc-400">Store Overview</flux:heading>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                                    <flux:icon.squares-2x2 class="w-4 h-4 text-blue-500" />
                                </div>
                                <span class="text-sm text-zinc-300">Categories</span>
                            </div>
                            <span class="font-bold text-zinc-100">{{ $categoryCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-violet-500/10 flex items-center justify-center">
                                    <flux:icon.cube class="w-4 h-4 text-violet-500" />
                                </div>
                                <span class="text-sm text-zinc-300">Products</span>
                            </div>
                            <span class="font-bold text-zinc-100">{{ $productCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                                    <flux:icon.shopping-cart class="w-4 h-4 text-amber-500" />
                                </div>
                                <span class="text-sm text-zinc-300">Total Orders</span>
                            </div>
                            <span class="font-bold text-zinc-100">{{ $orderCount ?? 0 }}</span>
                        </div>
                        <flux:separator />
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                                    <flux:icon.currency-dollar class="w-4 h-4 text-emerald-500" />
                                </div>
                                <span class="text-sm text-zinc-300">Lifetime Revenue</span>
                            </div>
                            <span class="font-bold text-emerald-400">RM {{ number_format($totalRevenue ?? 0, 2) }}</span>
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
                            <div class="flex items-center justify-between px-6 py-4 hover:bg-zinc-800/30 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-zinc-700 flex items-center justify-center text-sm font-bold text-zinc-300">
                                        #{{ $order->id }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-zinc-100">
                                            {{ $order->customer?->name ?? 'Walk-in Customer' }}
                                        </div>
                                        <div class="text-sm text-zinc-400">
                                            {{ $order->items->count() }} items · {{ $order->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-zinc-100">RM {{ number_format($order->total_amount, 2) }}</div>
                                    <div class="text-xs">
                                        @if($order->status === 'completed')
                                            <span class="text-green-400">Completed</span>
                                        @elseif($order->status === 'pending')
                                            <span class="text-amber-400">Pending</span>
                                        @else
                                            <span class="text-zinc-400">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-full bg-zinc-800 flex items-center justify-center">
                                        <flux:icon.shopping-cart class="w-8 h-8 text-zinc-600" />
                                    </div>
                                    <div>
                                        <flux:text class="text-zinc-300 font-semibold">No transactions yet</flux:text>
                                        <flux:text size="sm" class="text-zinc-500">Start selling by opening the POS</flux:text>
                                    </div>
                                    <flux:button :href="route('pos.index')" wire:navigate variant="primary" size="sm" class="mt-2">
                                        Open POS
                                    </flux:button>
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
                            <flux:heading size="lg">Top Products</flux:heading>
                            <flux:text size="sm" class="text-zinc-400">Best sellers this month</flux:text>
                        </div>
                        <flux:icon.trophy class="w-5 h-5 text-yellow-500" />
                    </div>
                    <div class="divide-y divide-zinc-800">
                        @forelse($topProducts ?? [] as $i => $row)
                            <div class="flex items-center gap-4 px-6 py-4 hover:bg-zinc-800/30 transition-colors">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0
                                    {{ $i === 0 ? 'bg-yellow-500 text-yellow-900' : ($i === 1 ? 'bg-zinc-400 text-zinc-900' : ($i === 2 ? 'bg-amber-700 text-amber-100' : 'bg-zinc-700 text-zinc-300')) }}">
                                    {{ $i + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm text-zinc-100 truncate">{{ $row->product?->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-zinc-400">{{ (int) $row->total_qty }} sold</p>
                                </div>
                                <span class="font-bold text-sm text-zinc-100">RM {{ number_format($row->total_revenue, 2) }}</span>
                            </div>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <div class="w-12 h-12 rounded-full bg-zinc-800 flex items-center justify-center mx-auto mb-3">
                                    <flux:icon.chart-bar class="w-6 h-6 text-zinc-600" />
                                </div>
                                <flux:text size="sm" class="text-zinc-400">No sales data yet</flux:text>
                            </div>
                        @endforelse
                    </div>
                </flux:card>
            </div>
        </div>

    </div>
</x-layouts::app>
