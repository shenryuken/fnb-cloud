<x-layouts::app :title="__('Dashboard')">
    <div class="flex flex-col gap-8 p-4 md:p-8 bg-neutral-50 dark:bg-neutral-950 min-h-full font-sans">
        @if(isset($tenant) && $tenant)
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">
                        Welcome, {{ auth()->user()->name }}
                    </h2>
                    <p class="text-neutral-500 font-medium">Here's what's happening at <span class="text-blue-600 font-bold">{{ $tenant->name }}</span> today</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-4 py-2 bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm text-xs font-black uppercase tracking-widest text-neutral-400">
                        {{ $tenant->slug }}
                    </span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Categories Count -->
            <div class="group bg-white dark:bg-neutral-900 p-8 rounded-[2rem] border border-neutral-200 dark:border-neutral-800 shadow-xl shadow-neutral-200/50 dark:shadow-none transition-all hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600">
                        <flux:icon.layers class="w-6 h-6" />
                    </div>
                    <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Menu</span>
                </div>
                <p class="text-sm font-bold text-neutral-500 dark:text-neutral-400">Categories</p>
                <h3 class="text-4xl font-black text-neutral-900 dark:text-neutral-100 tracking-tighter mt-1">{{ $categoryCount ?? 0 }}</h3>
            </div>

            <!-- Products Count -->
            <div class="group bg-white dark:bg-neutral-900 p-8 rounded-[2rem] border border-neutral-200 dark:border-neutral-800 shadow-xl shadow-neutral-200/50 dark:shadow-none transition-all hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600">
                        <flux:icon.package class="w-6 h-6" />
                    </div>
                    <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Inventory</span>
                </div>
                <p class="text-sm font-bold text-neutral-500 dark:text-neutral-400">Products</p>
                <h3 class="text-4xl font-black text-neutral-900 dark:text-neutral-100 tracking-tighter mt-1">{{ $productCount ?? 0 }}</h3>
            </div>

            <!-- Orders Count -->
            <div class="group bg-white dark:bg-neutral-900 p-8 rounded-[2rem] border border-neutral-200 dark:border-neutral-800 shadow-xl shadow-neutral-200/50 dark:shadow-none transition-all hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600">
                        <flux:icon.shopping-cart class="w-6 h-6" />
                    </div>
                    <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Sales</span>
                </div>
                <p class="text-sm font-bold text-neutral-500 dark:text-neutral-400">Total Orders</p>
                <h3 class="text-4xl font-black text-neutral-900 dark:text-neutral-100 tracking-tighter mt-1">{{ $orderCount ?? 0 }}</h3>
            </div>

            <!-- Total Revenue -->
            <div class="group bg-white dark:bg-neutral-900 p-8 rounded-[2rem] border border-neutral-200 dark:border-neutral-800 shadow-xl shadow-neutral-200/50 dark:shadow-none transition-all hover:-translate-y-1 bg-gradient-to-br from-white to-blue-50/30 dark:from-neutral-900 dark:to-blue-900/5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600">
                        <flux:icon.banknotes class="w-6 h-6" />
                    </div>
                    <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Revenue</span>
                </div>
                <p class="text-sm font-bold text-neutral-500 dark:text-neutral-400">Total Revenue</p>
                <h3 class="text-4xl font-black text-blue-600 tracking-tighter mt-1">${{ number_format($totalRevenue ?? 0, 2) }}</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Orders Table -->
            <div class="lg:col-span-2 bg-white dark:bg-neutral-900 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl overflow-hidden">
                <div class="px-8 py-6 border-b border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                    <h4 class="text-xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Recent Orders</h4>
                    <a href="{{ route('manage.orders.index') }}" class="text-xs font-black text-blue-600 uppercase tracking-widest hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-neutral-50/50 dark:bg-neutral-800/50 border-b border-neutral-100 dark:border-neutral-800">
                                <th class="px-8 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest">ID</th>
                                <th class="px-6 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest">Table</th>
                                <th class="px-6 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-50 dark:divide-neutral-800">
                            @forelse($recentOrders ?? [] as $order)
                                <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors group">
                                    <td class="px-8 py-5 font-bold text-neutral-400 text-sm">#{{ $order->id }}</td>
                                    <td class="px-6 py-5">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider
                                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 font-bold text-neutral-700 dark:text-neutral-300 text-sm">{{ $order->table_number ?? 'Walk-in' }}</td>
                                    <td class="px-6 py-5 text-right font-black text-neutral-900 dark:text-neutral-100 text-sm">${{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center text-sm text-neutral-400 font-medium italic">No transactions yet today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions / System Info -->
            <div class="bg-neutral-900 dark:bg-blue-600 rounded-[2.5rem] p-8 text-white shadow-2xl flex flex-col justify-between overflow-hidden relative group">
                <div class="absolute top-0 right-0 -mt-8 -mr-8 w-48 h-48 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700"></div>
                <div class="relative z-10">
                    <h4 class="text-2xl font-black tracking-tight mb-2">Ready to Serve?</h4>
                    <p class="text-white/60 font-medium text-sm leading-relaxed mb-8">Process orders quickly with our blazing fast Point of Sale system.</p>
                    
                    <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-3 px-8 py-4 bg-white text-neutral-900 rounded-2xl font-black text-sm shadow-xl hover:scale-105 transition-all transform active:scale-95">
                        <flux:icon.rocket class="w-5 h-5 text-blue-600" />
                        LAUNCH POS
                    </a>
                </div>

                <div class="relative z-10 pt-12 mt-12 border-t border-white/10">
                    <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-[0.2em] text-white/40">
                        <span>Platform Version</span>
                        <span>v1.0.4</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
