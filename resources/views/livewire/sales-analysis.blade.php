<div class="flex flex-col gap-6 p-4 md:p-8">
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Sales Analysis</flux:heading>
            <flux:subheading>Understand product performance and peak hours</flux:subheading>
            <flux:text size="sm" class="text-zinc-400 mt-1">Business day: {{ $businessDayStartTime }} &rarr; {{ $businessDayEndTime }}</flux:text>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <flux:card class="p-3">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <flux:text size="sm" class="font-black text-zinc-400 uppercase tracking-widest">From</flux:text>
                        <flux:input type="date" wire:model.live="fromDate" size="sm" />
                    </div>
                    <flux:separator vertical class="h-6" />
                    <div class="flex items-center gap-2">
                        <flux:text size="sm" class="font-black text-zinc-400 uppercase tracking-widest">To</flux:text>
                        <flux:input type="date" wire:model.live="toDate" size="sm" />
                    </div>
                </div>
            </flux:card>

            <div class="flex gap-2">
                <flux:button size="sm" wire:click="setRange('today')" variant="ghost">Today</flux:button>
                <flux:button size="sm" wire:click="setRange('7d')" variant="ghost">7D</flux:button>
                <flux:button size="sm" wire:click="setRange('month')" variant="ghost">Month</flux:button>
            </div>
        </div>
    </div>

    <flux:card class="p-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
            <div>
                <flux:heading size="lg">Highlights</flux:heading>
                <flux:text size="sm" class="text-zinc-400">Busiest hour and top sales hour</flux:text>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(($this->peakHours['by_orders'] ?? null))
                    <flux:badge color="zinc">Busiest Hour: {{ $this->peakHours['by_orders']['label'] }} ({{ $this->peakHours['by_orders']['orders_count'] }} orders)</flux:badge>
                @endif
                @if(($this->peakHours['by_sales'] ?? null))
                    <flux:badge color="blue">Top Sales Hour: {{ $this->peakHours['by_sales']['label'] }} (${{ number_format($this->peakHours['by_sales']['net_sales'], 2) }})</flux:badge>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                    <div class="px-5 py-3 border-b border-zinc-100 dark:border-zinc-800">
                        <flux:text class="text-xs font-black text-zinc-400 uppercase tracking-widest">Orders By Hour</flux:text>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                    <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Hour</th>
                                    <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">Orders</th>
                                    <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Net Sales</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($this->hourlyOrders as $row)
                                    @php
                                        $isPeakOrders = (int) ($this->peakHours['by_orders']['hour'] ?? -1) === (int) $row['hour'];
                                        $isPeakSales = (int) ($this->peakHours['by_sales']['hour'] ?? -1) === (int) $row['hour'];
                                        $label = \Carbon\Carbon::createFromTime((int) $row['hour'], 0)->format('g A');
                                    @endphp
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors {{ $isPeakOrders || $isPeakSales ? 'bg-zinc-50 dark:bg-zinc-800/30' : '' }}">
                                        <td class="py-3 px-4 font-semibold">
                                            {{ $label }}
                                            @if($isPeakOrders)
                                                <span class="ml-2 text-[10px] font-black uppercase tracking-widest text-zinc-400">busiest</span>
                                            @endif
                                            @if($isPeakSales)
                                                <span class="ml-2 text-[10px] font-black uppercase tracking-widest text-blue-500">top sales</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center tabular-nums">{{ (int) $row['orders_count'] }}</td>
                                        <td class="py-3 px-4 text-right tabular-nums">${{ number_format((float) $row['net_sales'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                    <div class="px-5 py-3 border-b border-zinc-100 dark:border-zinc-800">
                        <flux:text class="text-xs font-black text-zinc-400 uppercase tracking-widest">Weekly Summary</flux:text>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                    <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Week</th>
                                    <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">Orders</th>
                                    <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Net</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @forelse($this->weekly as $row)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                        <td class="py-3 px-4 font-semibold">
                                            {{ \Carbon\Carbon::parse($row['week_start'])->format('d M') }}
                                        </td>
                                        <td class="py-3 px-4 text-center tabular-nums">{{ (int) $row['orders_count'] }}</td>
                                        <td class="py-3 px-4 text-right tabular-nums font-black text-blue-600">${{ number_format((float) $row['net_sales'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-10 text-center text-zinc-400 italic">No data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                <div class="px-5 py-3 border-b border-zinc-100 dark:border-zinc-800">
                    <flux:text class="text-xs font-black text-zinc-400 uppercase tracking-widest">Top Products Per Day</flux:text>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Date</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Top Products</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($this->dailyTopProducts as $row)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3 px-4 font-semibold">{{ \Carbon\Carbon::parse($row['day'])->format('d M Y') }}</td>
                                    <td class="py-3 px-4">
                                        <div class="space-y-1">
                                            @foreach(($row['top'] ?? []) as $p)
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="truncate">{{ $p['product_name'] }}</span>
                                                    <span class="font-mono tabular-nums text-zinc-500 shrink-0">{{ (int) $p['quantity_sold'] }} • ${{ number_format((float) $p['gross_sales'], 2) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="py-10 text-center text-zinc-400 italic">No product sales found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                <div class="px-5 py-3 border-b border-zinc-100 dark:border-zinc-800">
                    <flux:text class="text-xs font-black text-zinc-400 uppercase tracking-widest">Product Breakdown</flux:text>
                </div>
                <div class="p-5 space-y-4">
                    <flux:select wire:model.live="analysisProductId" placeholder="Select product">
                        @foreach($this->analysisProducts as $p)
                            <flux:select.option value="{{ $p['id'] }}">{{ $p['name'] }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    @if($analysisProductId)
                        <div class="grid grid-cols-1 gap-4">
                            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                                <div class="px-4 py-2 border-b border-zinc-100 dark:border-zinc-800">
                                    <flux:text class="text-xs font-black text-zinc-400 uppercase tracking-widest">Hourly</flux:text>
                                </div>
                                <div class="max-h-56 overflow-y-auto">
                                    <table class="w-full text-sm text-left">
                                        <thead>
                                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                                <th class="py-2 px-3 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Hour</th>
                                                <th class="py-2 px-3 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">Qty</th>
                                                <th class="py-2 px-3 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Sales</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                            @foreach($this->productHourly as $row)
                                                <tr>
                                                    <td class="py-2 px-3 font-semibold">{{ \Carbon\Carbon::createFromTime((int) $row['hour'], 0)->format('g A') }}</td>
                                                    <td class="py-2 px-3 text-center tabular-nums">{{ (int) $row['quantity_sold'] }}</td>
                                                    <td class="py-2 px-3 text-right tabular-nums">${{ number_format((float) $row['gross_sales'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                                <div class="px-4 py-2 border-b border-zinc-100 dark:border-zinc-800">
                                    <flux:text class="text-xs font-black text-zinc-400 uppercase tracking-widest">Daily</flux:text>
                                </div>
                                <div class="max-h-56 overflow-y-auto">
                                    <table class="w-full text-sm text-left">
                                        <thead>
                                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                                <th class="py-2 px-3 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Date</th>
                                                <th class="py-2 px-3 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">Qty</th>
                                                <th class="py-2 px-3 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Sales</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                            @forelse($this->productDaily as $row)
                                                <tr>
                                                    <td class="py-2 px-3 font-semibold">{{ \Carbon\Carbon::parse($row['day'])->format('d M') }}</td>
                                                    <td class="py-2 px-3 text-center tabular-nums">{{ (int) $row['quantity_sold'] }}</td>
                                                    <td class="py-2 px-3 text-right tabular-nums">${{ number_format((float) $row['gross_sales'], 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="py-8 text-center text-zinc-400 italic">No data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                                <div class="px-4 py-2 border-b border-zinc-100 dark:border-zinc-800">
                                    <flux:text class="text-xs font-black text-zinc-400 uppercase tracking-widest">Weekly</flux:text>
                                </div>
                                <div class="max-h-56 overflow-y-auto">
                                    <table class="w-full text-sm text-left">
                                        <thead>
                                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                                <th class="py-2 px-3 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Week</th>
                                                <th class="py-2 px-3 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">Qty</th>
                                                <th class="py-2 px-3 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Sales</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                            @forelse($this->productWeekly as $row)
                                                <tr>
                                                    <td class="py-2 px-3 font-semibold">{{ \Carbon\Carbon::parse($row['week_start'])->format('d M') }}</td>
                                                    <td class="py-2 px-3 text-center tabular-nums">{{ (int) $row['quantity_sold'] }}</td>
                                                    <td class="py-2 px-3 text-right tabular-nums">${{ number_format((float) $row['gross_sales'], 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="py-8 text-center text-zinc-400 italic">No data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <flux:text size="sm" class="text-zinc-400">No products found.</flux:text>
                    @endif
                </div>
            </div>
        </div>
    </flux:card>
</div>

