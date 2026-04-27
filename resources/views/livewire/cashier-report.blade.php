<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Cashier Performance Report</flux:heading>
            <flux:text size="sm" class="text-zinc-400">Track sales, shifts, and cash variance by cashier</flux:text>
        </div>
    </div>

    {{-- Filters --}}
    <flux:card class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            {{-- Date Range --}}
            <div class="md:col-span-2">
                <flux:heading size="sm" level="3">Date Range</flux:heading>
                <div class="flex gap-4 mt-2">
                    <flux:input
                        type="date"
                        :value="$this->fromDate"
                        wire:change="$set('fromDate', $event->target->value)"
                        placeholder="From"
                    />
                    <flux:input
                        type="date"
                        :value="$this->toDate"
                        wire:change="$set('toDate', $event->target->value)"
                        placeholder="To"
                    />
                    <flux:input
                        type="date"
                        :value="date('Y-m-d', $this->toDate)"
                        wire:change="$set('toDate', strtotime($event->target->value) * 1)"
                        class="flex-1"
                    />
                </div>
            </div>

            {{-- Cashier Filter --}}
            <div>
                <flux:heading size="sm" level="3">Filter by Cashier</flux:heading>
                <flux:select wire:model.live="selectedUserId" class="mt-2">
                    <option value="">All Cashiers</option>
                    @foreach($this->users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Quick Filters --}}
            <div class="flex gap-2">
                <flux:button
                    size="sm"
                    wire:click="$set('fromDate', now()->startOfMonth()->timestamp)"
                    wire:click="$set('toDate', now()->endOfMonth()->timestamp)"
                    class="w-full"
                >
                    This Month
                </flux:button>
            </div>
        </div>
    </flux:card>

    {{-- Stats Cards --}}
    @if($this->cashierStats->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $totalSalesSum = $this->cashierStats->sum('totalSales');
            $totalOrdersSum = $this->cashierStats->sum('totalOrders');
            $totalVariance = $this->cashierStats->sum('totalCashVariance');
            $shiftsSum = $this->cashierStats->sum('shiftsCount');
        @endphp

        <flux:card class="p-4">
            <div class="text-sm text-zinc-400 mb-1">Total Sales</div>
            <div class="text-2xl font-bold">${{ number_format($totalSalesSum, 2) }}</div>
        </flux:card>

        <flux:card class="p-4">
            <div class="text-sm text-zinc-400 mb-1">Total Orders</div>
            <div class="text-2xl font-bold">{{ $totalOrdersSum }}</div>
        </flux:card>

        <flux:card class="p-4">
            <div class="text-sm text-zinc-400 mb-1">Total Shifts</div>
            <div class="text-2xl font-bold">{{ $shiftsSum }}</div>
        </flux:card>

        <flux:card class="p-4">
            <div class="text-sm text-zinc-400 mb-1">Cash Variance</div>
            <div class="text-2xl font-bold {{ $totalVariance >= 0 ? 'text-green-400' : 'text-red-400' }}">
                ${{ number_format($totalVariance, 2) }}
            </div>
        </flux:card>
    </div>
    @endif

    {{-- Cashier Performance Table --}}
    @if($this->cashierStats->count() > 0)
    <flux:card class="p-6">
        <flux:heading size="lg" class="mb-4">Performance by Cashier</flux:heading>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-700">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">Cashier</th>
                        <th class="text-right py-3 px-4 font-semibold">Shifts</th>
                        <th class="text-right py-3 px-4 font-semibold">Total Sales</th>
                        <th class="text-right py-3 px-4 font-semibold">Orders</th>
                        <th class="text-right py-3 px-4 font-semibold">Avg Order</th>
                        <th class="text-right py-3 px-4 font-semibold">Avg Duration</th>
                        <th class="text-right py-3 px-4 font-semibold">Cash Variance</th>
                        <th class="text-right py-3 px-4 font-semibold">Variance %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-700">
                    @foreach($this->cashierStats as $stat)
                    <tr class="hover:bg-zinc-800/50 transition">
                        <td class="py-3 px-4">
                            <div class="font-medium">{{ $stat['user']->name }}</div>
                        </td>
                        <td class="text-right py-3 px-4">{{ $stat['shiftsCount'] }}</td>
                        <td class="text-right py-3 px-4 font-semibold">${{ number_format($stat['totalSales'], 2) }}</td>
                        <td class="text-right py-3 px-4">{{ $stat['totalOrders'] }}</td>
                        <td class="text-right py-3 px-4">${{ number_format($stat['avgOrderValue'], 2) }}</td>
                        <td class="text-right py-3 px-4">{{ intdiv($stat['avgShiftDuration'], 60) }}h {{ $stat['avgShiftDuration'] % 60 }}m</td>
                        <td class="text-right py-3 px-4">
                            <span class="{{ $stat['totalCashVariance'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                ${{ number_format($stat['totalCashVariance'], 2) }}
                            </span>
                        </td>
                        <td class="text-right py-3 px-4">
                            <span class="{{ $stat['variancePercentage'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ number_format($stat['variancePercentage'], 2) }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </flux:card>
    @else
    <flux:card class="p-6 text-center">
        <flux:heading size="lg" class="text-zinc-400">No shifts found</flux:heading>
        <flux:text size="sm" class="text-zinc-500 mt-2">Try adjusting your date range or filter selection</flux:text>
    </flux:card>
    @endif

    {{-- Detailed Shifts for Selected Cashier --}}
    @if($this->selectedUserId && $this->shifts->count() > 0)
    <flux:card class="p-6">
        <flux:heading size="lg" class="mb-4">
            Shifts - {{ $this->users->find($this->selectedUserId)?->name }}
        </flux:heading>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-700">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold">Date</th>
                        <th class="text-right py-3 px-4 font-semibold">Time</th>
                        <th class="text-right py-3 px-4 font-semibold">Orders</th>
                        <th class="text-right py-3 px-4 font-semibold">Sales</th>
                        <th class="text-right py-3 px-4 font-semibold">Opening Cash</th>
                        <th class="text-right py-3 px-4 font-semibold">Expected Cash</th>
                        <th class="text-right py-3 px-4 font-semibold">Actual Cash</th>
                        <th class="text-right py-3 px-4 font-semibold">Variance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-700">
                    @foreach($this->shifts as $shift)
                    <tr class="hover:bg-zinc-800/50 transition">
                        <td class="py-3 px-4">{{ $shift->opened_at->format('M d, Y') }}</td>
                        <td class="text-right py-3 px-4">
                            {{ $shift->opened_at->format('H:i') }}
                            @if($shift->closed_at)
                                - {{ $shift->closed_at->format('H:i') }}
                            @else
                                <span class="text-yellow-400 text-xs">(open)</span>
                            @endif
                        </td>
                        <td class="text-right py-3 px-4">{{ $shift->total_orders }}</td>
                        <td class="text-right py-3 px-4">${{ number_format($shift->total_sales, 2) }}</td>
                        <td class="text-right py-3 px-4">${{ number_format($shift->opening_cash_amount, 2) }}</td>
                        <td class="text-right py-3 px-4">${{ number_format($shift->expected_cash_amount, 2) }}</td>
                        <td class="text-right py-3 px-4">
                            @if($shift->closed_at)
                                ${{ number_format($shift->actual_cash_amount, 2) }}
                            @else
                                <span class="text-zinc-500">-</span>
                            @endif
                        </td>
                        <td class="text-right py-3 px-4">
                            @if($shift->closed_at)
                                @php $variance = $shift->actual_cash_amount - $shift->expected_cash_amount @endphp
                                <span class="{{ $variance >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    ${{ number_format($variance, 2) }}
                                </span>
                            @else
                                <span class="text-zinc-500">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </flux:card>
    @endif
</div>
