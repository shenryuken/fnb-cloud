<div class="flex flex-col gap-6 p-4 md:p-8 bg-neutral-50 dark:bg-neutral-950 min-h-full font-sans">
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Sales Report</h2>
            <p class="text-neutral-500 font-medium">Track revenue, tax, discounts, and top-selling items</p>
            <p class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-2">Business day: {{ $businessDayStartTime }} → {{ $businessDayEndTime }}</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex items-center gap-2 bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 px-4 py-3 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">From</span>
                    <input type="date" wire:model.live="fromDate" class="bg-transparent border-none focus:ring-0 text-sm font-black text-neutral-700 dark:text-neutral-200">
                </div>
                <div class="w-px h-6 bg-neutral-200 dark:bg-neutral-800"></div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">To</span>
                    <input type="date" wire:model.live="toDate" class="bg-transparent border-none focus:ring-0 text-sm font-black text-neutral-700 dark:text-neutral-200">
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="setRange('today')" class="px-4 py-3 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-[10px] font-black uppercase tracking-widest text-neutral-600 dark:text-neutral-300 hover:border-blue-500 hover:text-blue-600 transition-all">
                    Today
                </button>
                <button type="button" wire:click="setRange('7d')" class="px-4 py-3 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-[10px] font-black uppercase tracking-widest text-neutral-600 dark:text-neutral-300 hover:border-blue-500 hover:text-blue-600 transition-all">
                    7D
                </button>
                <button type="button" wire:click="setRange('month')" class="px-4 py-3 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-[10px] font-black uppercase tracking-widest text-neutral-600 dark:text-neutral-300 hover:border-blue-500 hover:text-blue-600 transition-all">
                    Month
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-neutral-900 rounded-[2rem] border border-neutral-200 dark:border-neutral-800 p-6 shadow-sm">
            <p class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Orders</p>
            <p class="text-3xl font-black text-neutral-900 dark:text-neutral-100 tracking-tighter mt-2">{{ number_format($this->summary['orders_count'] ?? 0) }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-[2rem] border border-neutral-200 dark:border-neutral-800 p-6 shadow-sm">
            <p class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Gross Sales</p>
            <p class="text-3xl font-black text-neutral-900 dark:text-neutral-100 tracking-tighter mt-2">${{ number_format($this->summary['gross_sales'] ?? 0, 2) }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-[2rem] border border-neutral-200 dark:border-neutral-800 p-6 shadow-sm">
            <p class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Discounts</p>
            <p class="text-3xl font-black text-red-500 tracking-tighter mt-2">- ${{ number_format($this->summary['discounts'] ?? 0, 2) }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-[2rem] border border-neutral-200 dark:border-neutral-800 p-6 shadow-sm">
            <p class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Tax</p>
            <p class="text-3xl font-black text-emerald-500 tracking-tighter mt-2">${{ number_format($this->summary['taxes'] ?? 0, 2) }}</p>
        </div>

        <div class="bg-gradient-to-br from-white to-blue-50/40 dark:from-neutral-900 dark:to-blue-900/10 rounded-[2rem] border border-neutral-200 dark:border-neutral-800 p-6 shadow-sm">
            <p class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Net Sales</p>
            <p class="text-3xl font-black text-blue-600 tracking-tighter mt-2">${{ number_format($this->summary['net_sales'] ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-neutral-900 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl overflow-hidden">
            <div class="px-8 py-6 border-b border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                <h4 class="text-xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Daily Summary</h4>
            </div>
            <div class="overflow-x-auto scrollbar-hide">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/50 dark:bg-neutral-800/50 border-b border-neutral-100 dark:border-neutral-800">
                            <th class="px-8 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest">Date</th>
                            <th class="px-6 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest text-center">Orders</th>
                            <th class="px-6 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest text-right">Gross</th>
                            <th class="px-6 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest text-right">Discount</th>
                            <th class="px-6 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest text-right">Tax</th>
                            <th class="px-8 py-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest text-right">Net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-50 dark:divide-neutral-800">
                        @forelse($this->daily as $row)
                            <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="px-8 py-5 font-black text-neutral-700 dark:text-neutral-200 text-sm">{{ \Carbon\Carbon::parse($row['day'])->format('d M Y') }}</td>
                                <td class="px-6 py-5 text-center font-black text-neutral-400 text-sm">{{ $row['orders_count'] }}</td>
                                <td class="px-6 py-5 text-right font-black text-neutral-900 dark:text-neutral-100 text-sm tabular-nums">${{ number_format($row['gross_sales'], 2) }}</td>
                                <td class="px-6 py-5 text-right font-black text-red-500 text-sm tabular-nums">- ${{ number_format($row['discounts'], 2) }}</td>
                                <td class="px-6 py-5 text-right font-black text-emerald-600 text-sm tabular-nums">${{ number_format($row['taxes'], 2) }}</td>
                                <td class="px-8 py-5 text-right font-black text-blue-600 text-sm tabular-nums">${{ number_format($row['net_sales'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-sm text-neutral-400 font-medium italic">No sales found in this date range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col gap-6">
            <div class="bg-white dark:bg-neutral-900 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl overflow-hidden">
                <div class="px-8 py-6 border-b border-neutral-100 dark:border-neutral-800">
                    <h4 class="text-lg font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Payment Methods</h4>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($this->paymentBreakdown as $row)
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">{{ strtoupper($row['payment_method']) }}</span>
                                <span class="text-xs font-black text-neutral-600 dark:text-neutral-300">{{ $row['orders_count'] }} orders</span>
                            </div>
                            <span class="text-sm font-black text-blue-600 tabular-nums">${{ number_format($row['net_sales'], 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-400 font-medium italic">No payment data.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl overflow-hidden">
                <div class="px-8 py-6 border-b border-neutral-100 dark:border-neutral-800">
                    <h4 class="text-lg font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Top Products</h4>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($this->topProducts as $row)
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800">
                            <div class="min-w-0">
                                <p class="text-sm font-black text-neutral-800 dark:text-neutral-100 truncate">{{ $row['product_name'] }}</p>
                                <p class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">{{ $row['quantity_sold'] }} sold</p>
                            </div>
                            <span class="text-sm font-black text-blue-600 tabular-nums">${{ number_format($row['gross_sales'], 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-400 font-medium italic">No product data.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
