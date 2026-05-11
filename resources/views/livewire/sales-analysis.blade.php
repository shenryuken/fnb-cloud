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

    @php
        $maxHourlyOrders = max(array_map(fn ($r) => (int) ($r['orders_count'] ?? 0), $this->hourlyOrders ?: [[]]));
        $maxHourlySales = max(array_map(fn ($r) => (float) ($r['net_sales'] ?? 0), $this->hourlyOrders ?: [[]]));
        $maxWeeklySales = max(array_map(fn ($r) => (float) ($r['net_sales'] ?? 0), $this->weekly ?: [[]]));
        $maxWeeklyOrders = max(array_map(fn ($r) => (int) ($r['orders_count'] ?? 0), $this->weekly ?: [[]]));
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <flux:card class="p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-pink-500/5 rounded-full -mr-8 -mt-8"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-3">
                    <flux:text size="sm" class="text-zinc-400 font-semibold">Net Sales</flux:text>
                    <div class="w-10 h-10 rounded-lg bg-pink-500/10 flex items-center justify-center">
                        <flux:icon.banknotes class="w-5 h-5 text-pink-500" />
                    </div>
                </div>
                <flux:heading size="xl" class="mb-1 tabular-nums">${{ number_format((float) ($this->summary['net_sales'] ?? 0), 2) }}</flux:heading>
                <flux:text size="sm" class="text-zinc-400">Selected range</flux:text>
            </div>
        </flux:card>

        <flux:card class="p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/5 rounded-full -mr-8 -mt-8"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-3">
                    <flux:text size="sm" class="text-zinc-400 font-semibold">Orders</flux:text>
                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <flux:icon.clipboard-document-list class="w-5 h-5 text-blue-500" />
                    </div>
                </div>
                <flux:heading size="xl" class="mb-1 tabular-nums">{{ number_format((int) ($this->summary['orders_count'] ?? 0)) }}</flux:heading>
                <flux:text size="sm" class="text-zinc-400">Avg: ${{ number_format((float) ($this->summary['avg_order_value'] ?? 0), 2) }}</flux:text>
            </div>
        </flux:card>

        <flux:card class="p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/5 rounded-full -mr-8 -mt-8"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-3">
                    <flux:text size="sm" class="text-zinc-400 font-semibold">Busiest Hour</flux:text>
                    <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center">
                        <flux:icon.clock class="w-5 h-5 text-amber-500" />
                    </div>
                </div>
                <flux:heading size="lg" class="mb-1 truncate">{{ $this->peakHours['by_orders']['label'] ?? '—' }}</flux:heading>
                <flux:text size="sm" class="text-zinc-400">{{ (int) ($this->peakHours['by_orders']['orders_count'] ?? 0) }} orders</flux:text>
            </div>
        </flux:card>

        <flux:card class="p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full -mr-8 -mt-8"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-3">
                    <flux:text size="sm" class="text-zinc-400 font-semibold">Top Sales Hour</flux:text>
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                        <flux:icon.currency-dollar class="w-5 h-5 text-emerald-500" />
                    </div>
                </div>
                <flux:heading size="lg" class="mb-1 truncate">{{ $this->peakHours['by_sales']['label'] ?? '—' }}</flux:heading>
                <flux:text size="sm" class="text-zinc-400">${{ number_format((float) ($this->peakHours['by_sales']['net_sales'] ?? 0), 2) }}</flux:text>
            </div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <flux:card class="p-0 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <flux:heading size="lg">Orders By Hour</flux:heading>
                        <flux:text size="sm" class="text-zinc-400">Spot the busiest time slots</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:badge color="amber">Peak Orders</flux:badge>
                        <flux:badge color="blue">Peak Sales</flux:badge>
                    </div>
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
                                    $ordersPct = $maxHourlyOrders > 0 ? (int) round(((int) $row['orders_count'] / $maxHourlyOrders) * 100) : 0;
                                    $salesPct = $maxHourlySales > 0 ? (int) round(((float) $row['net_sales'] / $maxHourlySales) * 100) : 0;
                                @endphp
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors {{ $isPeakOrders || $isPeakSales ? 'bg-zinc-50 dark:bg-zinc-800/30' : '' }}">
                                    <td class="py-3 px-4 font-semibold">
                                        {{ $label }}
                                        @if($isPeakOrders)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">peak orders</span>
                                        @endif
                                        @if($isPeakSales)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">peak sales</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center tabular-nums">
                                        <div class="font-semibold">{{ (int) $row['orders_count'] }}</div>
                                        <div class="mt-1 h-1.5 w-full rounded-full bg-zinc-200/70 dark:bg-zinc-700/60 overflow-hidden">
                                            <div class="h-full rounded-full {{ $isPeakOrders ? 'bg-amber-500' : 'bg-zinc-400 dark:bg-zinc-500' }}" style="width: {{ $ordersPct }}%"></div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right tabular-nums">
                                        <div class="font-semibold">${{ number_format((float) $row['net_sales'], 2) }}</div>
                                        <div class="mt-1 h-1.5 w-full rounded-full bg-zinc-200/70 dark:bg-zinc-700/60 overflow-hidden">
                                            <div class="h-full rounded-full {{ $isPeakSales ? 'bg-blue-500' : 'bg-blue-300/80 dark:bg-blue-400/60' }}" style="width: {{ $salesPct }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </flux:card>
        </div>

        <div class="flex flex-col gap-6">
            <flux:card class="p-0 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <flux:heading size="lg">Weekly Summary</flux:heading>
                        <flux:text size="sm" class="text-zinc-400">Compare weeks at a glance</flux:text>
                    </div>
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
                                @php
                                    $ordersPct = $maxWeeklyOrders > 0 ? (int) round(((int) $row['orders_count'] / $maxWeeklyOrders) * 100) : 0;
                                    $salesPct = $maxWeeklySales > 0 ? (int) round(((float) $row['net_sales'] / $maxWeeklySales) * 100) : 0;
                                    $isTopWeekSales = $maxWeeklySales > 0 && (float) $row['net_sales'] === $maxWeeklySales;
                                @endphp
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3 px-4 font-semibold">{{ \Carbon\Carbon::parse($row['week_start'])->format('d M') }}</td>
                                    <td class="py-3 px-4 text-center tabular-nums">
                                        <div class="font-semibold">{{ (int) $row['orders_count'] }}</div>
                                        <div class="mt-1 h-1.5 w-full rounded-full bg-zinc-200/70 dark:bg-zinc-700/60 overflow-hidden">
                                            <div class="h-full rounded-full bg-emerald-400/90 dark:bg-emerald-500/70" style="width: {{ $ordersPct }}%"></div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right tabular-nums">
                                        <div class="font-black {{ $isTopWeekSales ? 'text-blue-700 dark:text-blue-300' : 'text-blue-600' }}">${{ number_format((float) $row['net_sales'], 2) }}</div>
                                        <div class="mt-1 h-1.5 w-full rounded-full bg-zinc-200/70 dark:bg-zinc-700/60 overflow-hidden">
                                            <div class="h-full rounded-full {{ $isTopWeekSales ? 'bg-blue-600' : 'bg-blue-300/80 dark:bg-blue-400/60' }}" style="width: {{ $salesPct }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-10 text-center text-zinc-400 italic">No data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </flux:card>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <flux:card class="p-0 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                    <flux:heading size="lg">Top Products Per Day</flux:heading>
                    <flux:text size="sm" class="text-zinc-400">Daily best sellers</flux:text>
                </div>
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
                                        @foreach(($row['top'] ?? []) as $idx => $p)
                                            @php
                                                $rank = (int) $idx + 1;
                                                $rankClass = $rank === 1
                                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'
                                                    : ($rank === 2
                                                        ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200'
                                                        : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300');
                                            @endphp
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $rankClass }}">#{{ $rank }}</span>
                                                    <span class="truncate">{{ $p['product_name'] }}</span>
                                                </div>
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
        </flux:card>

        <flux:card class="p-0 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                    <flux:heading size="lg">Product Breakdown</flux:heading>
                    <flux:text size="sm" class="text-zinc-400">Hourly, daily, and weekly for a single product</flux:text>
                </div>
            </div>
            <div class="p-6 space-y-4">
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
                                            @php
                                                $qty = (int) $row['quantity_sold'];
                                                $isHot = $qty > 0 && $maxHourlyOrders > 0 && $qty >= (int) ceil($maxHourlyOrders * 0.6);
                                            @endphp
                                            <tr>
                                                <td class="py-2 px-3 font-semibold">{{ \Carbon\Carbon::createFromTime((int) $row['hour'], 0)->format('g A') }}</td>
                                                <td class="py-2 px-3 text-center tabular-nums">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $isHot ? 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                                        {{ (int) $row['quantity_sold'] }}
                                                    </span>
                                                </td>
                                                <td class="py-2 px-3 text-right tabular-nums font-semibold text-blue-600">${{ number_format((float) $row['gross_sales'], 2) }}</td>
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
        </flux:card>
    </div>
</div>
