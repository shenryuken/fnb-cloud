<div class="flex flex-col gap-6 p-4 md:p-8">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Sales Report</flux:heading>
            <flux:subheading>Track revenue, tax, discounts, and top-selling items</flux:subheading>
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

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <flux:card class="p-6">
            <flux:text size="sm" class="font-black uppercase tracking-widest text-zinc-400">Orders</flux:text>
            <flux:heading size="xl" class="mt-2 tabular-nums">{{ number_format($this->summary['orders_count'] ?? 0) }}</flux:heading>
        </flux:card>

        <flux:card class="p-6">
            <flux:text size="sm" class="font-black uppercase tracking-widest text-zinc-400">Gross Sales</flux:text>
            <flux:heading size="xl" class="mt-2 tabular-nums">${{ number_format($this->summary['gross_sales'] ?? 0, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="p-6">
            <flux:text size="sm" class="font-black uppercase tracking-widest text-zinc-400">Discounts</flux:text>
            <flux:heading size="xl" class="mt-2 tabular-nums text-red-500">-${{ number_format($this->summary['discounts'] ?? 0, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="p-6">
            <flux:text size="sm" class="font-black uppercase tracking-widest text-zinc-400">Tax</flux:text>
            <flux:heading size="xl" class="mt-2 tabular-nums text-emerald-600">${{ number_format($this->summary['taxes'] ?? 0, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="p-6 ring-1 ring-blue-500/30">
            <flux:text size="sm" class="font-black uppercase tracking-widest text-zinc-400">Net Sales</flux:text>
            <flux:heading size="xl" class="mt-2 tabular-nums text-blue-600">${{ number_format($this->summary['net_sales'] ?? 0, 2) }}</flux:heading>
        </flux:card>
    </div>

    {{-- Revenue Chart --}}
    @if(!empty($this->daily))
    @php
        $chartData = collect($this->daily)->map(fn($r) => array_merge($r, [
            'label' => \Carbon\Carbon::parse($r['day'])->format('M d'),
        ]))->values()->toArray();
    @endphp
    <flux:card class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="lg">Revenue Trend</flux:heading>
                <flux:text size="sm" class="text-zinc-400">Net sales per day</flux:text>
            </div>
        </div>

        <flux:chart :value="$chartData" class="h-56">
            <flux:chart.svg>
                <flux:chart.bar field="net_sales" class="text-blue-500 dark:text-blue-400" radius="2" width="60%" />
                <flux:chart.axis axis="x" field="label" tick-count="7">
                    <flux:chart.axis.tick />
                    <flux:chart.axis.line />
                </flux:chart.axis>
                <flux:chart.axis axis="y" tick-count="5" tick-prefix="$">
                    <flux:chart.axis.grid />
                    <flux:chart.axis.tick />
                </flux:chart.axis>
                <flux:chart.cursor />
            </flux:chart.svg>
            <flux:chart.tooltip>
                <flux:chart.tooltip.heading field="label" />
                <flux:chart.tooltip.value field="net_sales" label="Net Sales" prefix="$" />
                <flux:chart.tooltip.value field="orders_count" label="Orders" />
            </flux:chart.tooltip>
        </flux:chart>
    </flux:card>
    @endif

    {{-- Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Daily Summary --}}
        <div class="lg:col-span-2">
            <flux:card class="overflow-hidden p-0">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <flux:heading size="lg">Daily Summary</flux:heading>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Date</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">Orders</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Gross</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Discount</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Tax</th>
                                <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Net</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($this->daily as $row)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3 px-4 font-semibold">{{ \Carbon\Carbon::parse($row['day'])->format('d M Y') }}</td>
                                    <td class="py-3 px-4 text-center tabular-nums">{{ $row['orders_count'] }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums">${{ number_format($row['gross_sales'], 2) }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums text-red-500">-${{ number_format($row['discounts'], 2) }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums text-emerald-600">${{ number_format($row['taxes'], 2) }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums font-black text-blue-600">${{ number_format($row['net_sales'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-zinc-400 italic">No sales found in this date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </flux:card>
        </div>

        <div class="flex flex-col gap-6">
            {{-- Payment Methods --}}
            <flux:card class="overflow-hidden p-0">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <flux:heading>Payment Methods</flux:heading>
                </div>
                <div class="p-4 space-y-2">
                    @forelse($this->paymentBreakdown as $row)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <div>
                                <flux:text class="font-black uppercase tracking-widest text-xs">{{ strtoupper($row['payment_method']) }}</flux:text>
                                <flux:text size="sm" class="text-zinc-400">{{ $row['orders_count'] }} orders</flux:text>
                            </div>
                            <flux:text class="font-black text-blue-600 tabular-nums">${{ number_format($row['net_sales'], 2) }}</flux:text>
                        </div>
                    @empty
                        <flux:text class="text-zinc-400 italic text-sm p-2">No payment data.</flux:text>
                    @endforelse
                </div>
            </flux:card>

            {{-- Top Products --}}
            <flux:card class="overflow-hidden p-0">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <flux:heading>Top Products</flux:heading>
                </div>
                <div class="p-4 space-y-2">
                    @forelse($this->topProducts as $row)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <div class="min-w-0">
                                <flux:text class="font-semibold truncate">{{ $row['product_name'] }}</flux:text>
                                <flux:text size="sm" class="font-black uppercase tracking-widest text-zinc-400">{{ $row['quantity_sold'] }} sold</flux:text>
                            </div>
                            <flux:text class="font-black text-blue-600 tabular-nums shrink-0 ml-2">${{ number_format($row['gross_sales'], 2) }}</flux:text>
                        </div>
                    @empty
                        <flux:text class="text-zinc-400 italic text-sm p-2">No product data.</flux:text>
                    @endforelse
                </div>
            </flux:card>
        </div>
    </div>
</div>
