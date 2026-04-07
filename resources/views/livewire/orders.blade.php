<div class="flex flex-col gap-6 p-4 md:p-8">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Order History</flux:heading>
            <flux:subheading>Manage and track all restaurant transactions</flux:subheading>
        </div>
        <flux:badge color="blue">
            {{ $hasActiveFilters ? 'Filtered:' : 'Total:' }} {{ $orders->total() }} orders
        </flux:badge>
    </div>

    {{-- Filters --}}
    <flux:card class="p-4">
        <div class="flex flex-col lg:flex-row gap-3">
            <div class="flex-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by order ID, customer, table, voucher..."
                    icon="magnifying-glass"
                    clearable
                />
            </div>

            <flux:select wire:model.live="statusFilter" placeholder="All Statuses" class="min-w-[150px]">
                <flux:select.option value="">All Statuses</flux:select.option>
                <flux:select.option value="pending">Pending</flux:select.option>
                <flux:select.option value="processing">Processing</flux:select.option>
                <flux:select.option value="completed">Completed</flux:select.option>
                <flux:select.option value="cancelled">Cancelled</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="orderTypeFilter" placeholder="All Types" class="min-w-[140px]">
                <flux:select.option value="">All Types</flux:select.option>
                <flux:select.option value="dine_in">Dine-in</flux:select.option>
                <flux:select.option value="takeaway">Takeaway</flux:select.option>
            </flux:select>

            <div class="flex items-center gap-2">
                <flux:input type="date" wire:model.live="dateFrom" title="From" />
                <flux:text class="shrink-0 text-sm">to</flux:text>
                <flux:input type="date" wire:model.live="dateTo" title="To" />
            </div>

            @if($hasActiveFilters)
                <flux:button wire:click="clearFilters" variant="ghost" icon="x-mark" class="text-red-500 hover:text-red-600">
                    Clear
                </flux:button>
            @endif
        </div>

        @if($hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                <flux:text size="sm" class="font-bold text-zinc-400">Active Filters:</flux:text>
                @if($search !== '')
                    <flux:badge color="blue" size="sm" icon="magnifying-glass">"{{ $search }}"</flux:badge>
                @endif
                @if($statusFilter !== '')
                    <flux:badge color="yellow" size="sm">Status: {{ $statusFilter }}</flux:badge>
                @endif
                @if($orderTypeFilter !== '')
                    <flux:badge color="purple" size="sm">Type: {{ str_replace('_', '-', $orderTypeFilter) }}</flux:badge>
                @endif
                @if($dateFrom !== '' || $dateTo !== '')
                    <flux:badge color="green" size="sm" icon="calendar">{{ $dateFrom ?: '...' }} &rarr; {{ $dateTo ?: '...' }}</flux:badge>
                @endif
            </div>
        @endif
    </flux:card>

    {{-- Table --}}
    <flux:card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Order</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Status</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Customer / Table</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Items</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Amount</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Time</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($orders as $order)
                        <tr wire:click="openOrder({{ $order->id }})" class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="py-3 px-4">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 font-black text-xs shrink-0">
                                    #{{ $order->id }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $statusColor = match($order->status) {
                                        'completed' => 'green',
                                        'cancelled' => 'red',
                                        'processing' => 'blue',
                                        default => 'yellow',
                                    };
                                @endphp
                                <flux:badge :color="$statusColor" size="sm">{{ $order->status }}</flux:badge>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold">
                                    {{ $order->customer?->name ?: ($order->table_number ? 'Table ' . $order->table_number : 'Walk-in') }}
                                </span>
                                @if($order->customer)
                                    <div class="text-xs text-zinc-400">{{ $order->customer->email ?: $order->customer->mobile }}</div>
                                @endif
                                <div class="text-xs text-zinc-400">Server: {{ $order->user?->name ?? 'System' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-wrap gap-1.5 max-w-[180px]">
                                    @foreach($order->items->take(5) as $item)
                                        <div class="relative h-9 w-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-[10px] font-black text-zinc-500 overflow-visible" title="{{ $item->product?->name }}">
                                            @if($item->product?->image_url)
                                                <img src="{{ $item->product->image_url }}" class="h-full w-full object-cover rounded-xl" alt="">
                                            @else
                                                {{ substr($item->product?->name ?? '?', 0, 1) }}
                                            @endif
                                            <span class="absolute -top-1.5 -right-1.5 h-4 w-4 rounded-full bg-blue-600 text-white text-[8px] flex items-center justify-center font-black">{{ $item->quantity }}</span>
                                        </div>
                                    @endforeach
                                    @if($order->items->count() > 5)
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800 text-[10px] font-black text-zinc-400">
                                            +{{ $order->items->count() - 5 }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-black">${{ number_format($order->total_amount, 2) }}</span>
                                <div class="text-xs text-zinc-400 uppercase">{{ $order->payment_method }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold">{{ $order->created_at->format('M d, H:i') }}</span>
                                <div class="text-xs text-zinc-400">{{ $order->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="printer"
                                        onclick="window.open('{{ route('pos.receipt', $order) }}', '_blank', 'width=400,height=600')"
                                        title="Print Receipt"
                                    />
                                    @php $orderId = $order->id; @endphp
                                    <select
                                        onchange="@this.call('updateStatus', {{ $orderId }}, this.value)"
                                        class="text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2 py-1.5 font-semibold min-w-[110px] focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                    >
                                        <option value="pending"    @selected($order->status === 'pending')>Pending</option>
                                        <option value="processing" @selected($order->status === 'processing')>Processing</option>
                                        <option value="completed"  @selected($order->status === 'completed')>Completed</option>
                                        <option value="cancelled"  @selected($order->status === 'cancelled')>Cancelled</option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-24 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    @if($hasActiveFilters)
                                        <flux:icon.funnel class="w-10 h-10 text-zinc-300 dark:text-zinc-700" />
                                        <flux:heading>No orders match your filters</flux:heading>
                                        <flux:subheading>Try adjusting your search or filter criteria.</flux:subheading>
                                        <flux:button wire:click="clearFilters" variant="ghost" size="sm">Clear all filters</flux:button>
                                    @else
                                        <flux:icon.clipboard-list class="w-10 h-10 text-zinc-300 dark:text-zinc-700" />
                                        <flux:heading>No orders found</flux:heading>
                                        <flux:subheading>Transactions will appear here once they are processed in the POS.</flux:subheading>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-zinc-100 dark:border-zinc-800">
            {{ $orders->links() }}
        </div>
    </flux:card>

    {{-- Order Detail Modal --}}
    <flux:modal name="order-detail" wire:model="showOrderModal" class="max-w-3xl w-full">
        @if($viewingOrder)
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg shrink-0">
                    <flux:icon.receipt-percent class="w-6 h-6 text-white" />
                </div>
                <div class="flex-1">
                    <flux:heading size="lg">Order #{{ $viewingOrder->id }}</flux:heading>
                    <flux:subheading>{{ $viewingOrder->created_at->format('M d, Y H:i') }}</flux:subheading>
                </div>
                @php
                    $statusColor = match($viewingOrder->status) {
                        'completed' => 'green',
                        'cancelled' => 'red',
                        'processing' => 'blue',
                        default => 'yellow',
                    };
                @endphp
                <flux:badge :color="$statusColor">{{ $viewingOrder->status }}</flux:badge>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                {{-- Left: Customer + Items --}}
                <div class="lg:col-span-3 space-y-4">
                    <flux:card class="p-4 space-y-2">
                        <flux:text class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Order Info</flux:text>
                        <div class="flex justify-between">
                            <flux:text size="sm" class="text-zinc-500">Customer</flux:text>
                            <flux:text size="sm" class="font-semibold">{{ $viewingOrder->customer?->name ?: ($viewingOrder->table_number ? 'Table '.$viewingOrder->table_number : 'Walk-in') }}</flux:text>
                        </div>
                        @if($viewingOrder->customer)
                            <div class="flex justify-between">
                                <flux:text size="sm" class="text-zinc-500">Contact</flux:text>
                                <flux:text size="sm">{{ $viewingOrder->customer->email ?: ($viewingOrder->customer->mobile ?: '—') }}</flux:text>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <flux:text size="sm" class="text-zinc-500">Server</flux:text>
                            <flux:text size="sm">{{ $viewingOrder->user?->name ?? 'System' }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text size="sm" class="text-zinc-500">Type</flux:text>
                            <flux:badge size="sm" color="zinc">{{ str_replace('_', '-', $viewingOrder->order_type ?? 'dine-in') }}</flux:badge>
                        </div>
                    </flux:card>

                    <flux:card class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <flux:text class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Items</flux:text>
                            <flux:badge size="sm" color="zinc">{{ $viewingOrder->items->count() }} lines</flux:badge>
                        </div>
                        <div class="space-y-3 divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($viewingOrder->items as $item)
                                <div class="flex items-start justify-between gap-4 pt-3 first:pt-0">
                                    <div class="flex-1">
                                        <flux:text class="font-semibold">
                                            {{ (int) $item->quantity }}x {{ $item->product?->name }}
                                            @if($item->variant)
                                                <flux:text as="span" size="sm" class="text-zinc-400">({{ $item->variant->receipt_label ?: $item->variant->name }})</flux:text>
                                            @endif
                                        </flux:text>
                                        @if($item->addons->count() > 0)
                                            <flux:text size="sm" class="text-zinc-500 mt-0.5">+ {{ $item->addons->pluck('name')->implode(', ') }}</flux:text>
                                        @endif
                                        @if($item->components->count() > 0)
                                            <flux:text size="sm" class="text-zinc-500 mt-0.5">Set: {{ $item->components->pluck('name')->implode(', ') }}</flux:text>
                                        @endif
                                        @if($item->notes)
                                            <flux:badge size="sm" color="yellow" class="mt-1">{{ $item->notes }}</flux:badge>
                                        @endif
                                    </div>
                                    <flux:text class="font-black tabular-nums shrink-0">${{ number_format((float) $item->subtotal, 2) }}</flux:text>
                                </div>
                            @endforeach
                        </div>
                    </flux:card>
                </div>

                {{-- Right: Totals + Payment --}}
                <div class="lg:col-span-2 space-y-4">
                    <flux:card class="p-4 space-y-2">
                        <flux:text class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Summary</flux:text>
                        <div class="flex justify-between">
                            <flux:text size="sm" class="text-zinc-500">Subtotal</flux:text>
                            <flux:text size="sm" class="font-semibold tabular-nums">${{ number_format((float) $viewingOrder->subtotal_amount, 2) }}</flux:text>
                        </div>
                        @if((float) ($viewingOrder->discount_amount ?? 0) > 0)
                            <div class="flex justify-between">
                                <flux:text size="sm" class="text-zinc-500">Discount</flux:text>
                                <flux:text size="sm" class="font-semibold text-red-500 tabular-nums">- ${{ number_format((float) $viewingOrder->discount_amount, 2) }}</flux:text>
                            </div>
                        @endif
                        @if((float) ($viewingOrder->tax_amount ?? 0) > 0)
                            <div class="flex justify-between">
                                <flux:text size="sm" class="text-zinc-500">Tax</flux:text>
                                <flux:text size="sm" class="font-semibold text-emerald-600 tabular-nums">${{ number_format((float) $viewingOrder->tax_amount, 2) }}</flux:text>
                            </div>
                        @endif
                        <flux:separator />
                        <div class="flex justify-between items-baseline">
                            <flux:text class="font-black uppercase tracking-widest text-sm">Total</flux:text>
                            <flux:heading size="xl" class="text-blue-600 tabular-nums">${{ number_format((float) $viewingOrder->total_amount, 2) }}</flux:heading>
                        </div>
                    </flux:card>

                    <flux:card class="p-4 space-y-2">
                        <flux:text class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Payment</flux:text>
                        @if(!empty($viewingOrder->payment_splits))
                            @foreach($viewingOrder->payment_splits as $split)
                                <div class="flex justify-between">
                                    <flux:text size="sm" class="text-zinc-500 uppercase">{{ $split['method'] }}</flux:text>
                                    <flux:text size="sm" class="font-semibold tabular-nums">${{ number_format($split['amount'], 2) }}</flux:text>
                                </div>
                            @endforeach
                            <flux:badge size="sm" color="blue">Split Payment</flux:badge>
                        @else
                            <div class="flex justify-between">
                                <flux:text size="sm" class="text-zinc-500">Method</flux:text>
                                <flux:text size="sm" class="font-semibold uppercase">{{ $viewingOrder->payment_method }}</flux:text>
                            </div>
                            <div class="flex justify-between">
                                <flux:text size="sm" class="text-zinc-500">Paid</flux:text>
                                <flux:text size="sm" class="font-semibold tabular-nums">${{ number_format((float) ($viewingOrder->amount_paid ?? 0), 2) }}</flux:text>
                            </div>
                            @if((float) ($viewingOrder->change_amount ?? 0) > 0)
                                <div class="flex justify-between">
                                    <flux:text size="sm" class="text-zinc-500">Change</flux:text>
                                    <flux:text size="sm" class="font-semibold text-emerald-600 tabular-nums">${{ number_format((float) $viewingOrder->change_amount, 2) }}</flux:text>
                                </div>
                            @endif
                        @endif
                    </flux:card>

                    @if($viewingOrder->voucher_code || ((int) ($viewingOrder->points_redeemed ?? 0) > 0))
                        <flux:card class="p-4 space-y-2">
                            <flux:text class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Loyalty / Voucher</flux:text>
                            @if($viewingOrder->voucher_code)
                                <div class="flex justify-between">
                                    <flux:text size="sm" class="text-zinc-500">Voucher</flux:text>
                                    <flux:badge size="sm" color="purple">{{ $viewingOrder->voucher_code }}</flux:badge>
                                </div>
                            @endif
                            @if((int) ($viewingOrder->points_redeemed ?? 0) > 0)
                                <div class="flex justify-between">
                                    <flux:text size="sm" class="text-zinc-500">Points Redeemed</flux:text>
                                    <flux:text size="sm" class="font-semibold tabular-nums">{{ (int) $viewingOrder->points_redeemed }}</flux:text>
                                </div>
                            @endif
                        </flux:card>
                    @endif

                    <flux:button
                        onclick="window.open('{{ route('pos.receipt', $viewingOrder) }}?preview=1', '_blank', 'width=420,height=700')"
                        variant="primary"
                        icon="printer"
                        class="w-full"
                    >
                        View Receipt
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

</div>
