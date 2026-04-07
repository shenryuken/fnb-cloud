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
    @php
        $chartRows   = collect($this->daily);
        $maxNet      = (float) ($chartRows->max('net_sales') ?: 1);
        $chartJson   = $chartRows->map(fn($r) => [
            'day'          => $r['day'],
            'net_sales'    => (float) $r['net_sales'],
            'orders_count' => (int)   $r['orders_count'],
        ])->values()->toJson();
    @endphp

    @if($chartRows->isNotEmpty())
    <flux:card class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="lg">Revenue Trend</flux:heading>
                <flux:text size="sm" class="text-zinc-400">Net sales per day</flux:text>
            </div>
        </div>

        <div
            x-data="{
                rows: {{ $chartJson }},
                max: {{ $maxNet }},
                tooltip: null,
                tooltipX: 0,
                tooltipY: 0,
                chartW: 800,
                chartH: 200,
                padL: 56, padR: 16, padT: 12, padB: 32,
                init() {
                    this.$nextTick(() => {
                        this.chartW = this.$el.offsetWidth || 800;
                    });
                    window.addEventListener('resize', () => {
                        this.chartW = this.$el.offsetWidth || 800;
                    });
                },
                innerW() { return Math.max(1, this.chartW - this.padL - this.padR); },
                innerH() { return this.chartH - this.padT - this.padB; },
                barW() {
                    const n = this.rows.length;
                    if (n === 0) return 0;
                    const slot = this.innerW() / n;
                    return Math.max(4, Math.min(slot * 0.55, 40));
                },
                barX(i) {
                    const n = this.rows.length;
                    const slot = this.innerW() / n;
                    return this.padL + slot * i + slot / 2 - this.barW() / 2;
                },
                barH(val) { return Math.max(2, (val / this.max) * this.innerH()); },
                barY(val) { return this.padT + this.innerH() - this.barH(val); },
                yTicks() {
                    const steps = 5;
                    return Array.from({length: steps + 1}, (_, i) => ({
                        val: this.max * i / steps,
                        y:   this.padT + this.innerH() - (this.innerH() * i / steps),
                    }));
                },
                xTicks() {
                    const n = this.rows.length;
                    if (n === 0) return [];
                    const step = n <= 14 ? 1 : Math.ceil(n / 7);
                    return this.rows
                        .map((r, i) => ({ i, r }))
                        .filter(({ i }) => i % step === 0)
                        .map(({ i, r }) => ({
                            x: this.padL + (this.innerW() / n) * i + (this.innerW() / n) / 2,
                            label: new Date(r.day + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
                        }));
                },
                showTip(e, row) {
                    const rect = this.$el.getBoundingClientRect();
                    this.tooltipX = e.clientX - rect.left;
                    this.tooltipY = e.clientY - rect.top - 10;
                    this.tooltip = row;
                },
                fmtCurrency(v) {
                    return '$' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                fmtDate(d) {
                    return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
                },
            }"
            class="relative select-none"
            :style="`height: ${chartH}px`"
        >
            <svg :width="chartW" :height="chartH" class="overflow-visible w-full">
                <!-- Y-axis gridlines & labels -->
                <template x-for="tick in yTicks()" :key="tick.y">
                    <g>
                        <line :x1="padL" :x2="chartW - padR" :y1="tick.y" :y2="tick.y"
                              stroke="currentColor" stroke-width="1" stroke-dasharray="4,4"
                              class="text-zinc-200 dark:text-zinc-700" />
                        <text :x="padL - 8" :y="tick.y + 4" text-anchor="end" font-size="11"
                              class="fill-zinc-400" x-text="'$' + tick.val.toLocaleString('en-US', { maximumFractionDigits: 0 })"></text>
                    </g>
                </template>

                <!-- Bars -->
                <template x-for="(row, i) in rows" :key="i">
                    <rect
                        :x="barX(i)"
                        :y="barY(row.net_sales)"
                        :width="barW()"
                        :height="barH(row.net_sales)"
                        rx="3"
                        class="fill-blue-500 hover:fill-blue-400 transition-colors cursor-pointer"
                        @mouseenter="showTip($event, row)"
                        @mousemove="showTip($event, row)"
                        @mouseleave="tooltip = null"
                    />
                </template>

                <!-- X-axis labels -->
                <template x-for="tick in xTicks()" :key="tick.x">
                    <text :x="tick.x" :y="chartH - 6" text-anchor="middle" font-size="11"
                          class="fill-zinc-400" x-text="tick.label"></text>
                </template>
            </svg>

            <!-- Tooltip -->
            <div
                x-show="tooltip !== null"
                x-cloak
                :style="`left: ${tooltipX + 12}px; top: ${tooltipY - 60}px`"
                class="absolute pointer-events-none z-20 bg-zinc-900 dark:bg-zinc-800 text-white rounded-xl px-3 py-2 shadow-xl text-xs whitespace-nowrap border border-zinc-700"
            >
                <div class="font-bold mb-1" x-text="tooltip ? fmtDate(tooltip.day) : ''"></div>
                <div class="flex items-center justify-between gap-4">
                    <span class="text-zinc-400">Net Sales</span>
                    <span class="font-mono font-bold" x-text="tooltip ? fmtCurrency(tooltip.net_sales) : ''"></span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="text-zinc-400">Orders</span>
                    <span class="font-mono font-bold" x-text="tooltip ? tooltip.orders_count : ''"></span>
                </div>
            </div>
        </div>
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
