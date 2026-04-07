<div class="flex flex-col gap-6 p-4 md:p-8 bg-neutral-50 dark:bg-neutral-950 min-h-full font-sans">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Order History</h2>
            <p class="text-neutral-500 font-medium">Manage and track all restaurant transactions</p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="px-4 py-2 bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm flex items-center gap-3">
                <span class="text-xs font-bold text-neutral-400 uppercase tracking-widest">{{ $hasActiveFilters ? 'Filtered' : 'Total Orders' }}</span>
                <span class="text-xl font-black text-blue-600">{{ $orders->total() }}</span>
            </div>
        </div>
    </div>

    {{-- Filters Bar --}}
    <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-4">
        <div class="flex flex-col lg:flex-row gap-3">
            {{-- Search --}}
            <div class="relative flex-1 min-w-0">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <flux:icon.magnifying-glass class="w-4 h-4 text-neutral-400" />
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by order ID, customer, table, voucher..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-sm font-medium text-neutral-800 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                />
            </div>

            {{-- Status Filter --}}
            <select
                wire:model.live="statusFilter"
                class="appearance-none px-4 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-sm font-bold text-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all min-w-[140px]">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>

            {{-- Order Type Filter --}}
            <select
                wire:model.live="orderTypeFilter"
                class="appearance-none px-4 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-sm font-bold text-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all min-w-[140px]">
                <option value="">All Types</option>
                <option value="dine_in">Dine-in</option>
                <option value="takeaway">Takeaway</option>
            </select>

            {{-- Date Range --}}
            <div class="flex items-center gap-2">
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    class="px-3 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-sm font-medium text-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                    title="Date from"
                />
                <span class="text-neutral-400 text-sm font-bold shrink-0">to</span>
                <input
                    type="date"
                    wire:model.live="dateTo"
                    class="px-3 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-sm font-medium text-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                    title="Date to"
                />
            </div>

            {{-- Clear Filters --}}
            @if($hasActiveFilters)
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/10 text-red-600 dark:text-red-400 text-sm font-bold hover:bg-red-100 dark:hover:bg-red-900/20 transition-all shrink-0">
                    <flux:icon.x-mark class="w-4 h-4" />
                    Clear
                </button>
            @endif
        </div>

        {{-- Active filter chips --}}
        @if($hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-neutral-100 dark:border-neutral-800">
                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Active Filters:</span>
                @if($search !== '')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/40 text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                        <flux:icon.magnifying-glass class="w-3 h-3" /> "{{ $search }}"
                    </span>
                @endif
                @if($statusFilter !== '')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider">
                        Status: {{ $statusFilter }}
                    </span>
                @endif
                @if($orderTypeFilter !== '')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800/40 text-[10px] font-black text-purple-600 dark:text-purple-400 uppercase tracking-wider">
                        Type: {{ str_replace('_', '-', $orderTypeFilter) }}
                    </span>
                @endif
                @if($dateFrom !== '' || $dateTo !== '')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/40 text-[10px] font-black text-green-600 dark:text-green-400 uppercase tracking-wider">
                        <flux:icon.calendar class="w-3 h-3" />
                        {{ $dateFrom ?: '...' }} &rarr; {{ $dateTo ?: '...' }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-neutral-900 rounded-3xl border border-neutral-200 dark:border-neutral-800 shadow-xl shadow-neutral-200/50 dark:shadow-none overflow-hidden">
        <div class="overflow-x-auto scrollbar-hide">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-neutral-50/50 dark:bg-neutral-800/50 border-b border-neutral-100 dark:border-neutral-800">
                        <th class="px-8 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Order</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Customer / Table</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Items Summary</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Amount</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Time</th>
                        <th class="px-8 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    @forelse($orders as $order)
                        <tr wire:click="openOrder({{ $order->id }})" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors group cursor-pointer">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 font-black text-xs">
                                        #{{ $order->id }}
                                    </div>
                                    <div class="text-xs font-bold text-neutral-400">ID</div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider
                                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 
                                       ($order->status === 'cancelled' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 
                                       'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $order->status === 'completed' ? 'bg-green-500' : ($order->status === 'cancelled' ? 'bg-red-500' : 'bg-amber-500') }}"></span>
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col">
                                    <span class="font-bold text-neutral-800 dark:text-neutral-200">{{ $order->customer?->name ?: ($order->table_number ? 'Table ' . $order->table_number : 'Walk-in') }}</span>
                                    @if($order->customer)
                                        <span class="text-[10px] text-neutral-400 font-medium">{{ $order->customer->email ?: ($order->customer->mobile ?: 'Customer') }}</span>
                                    @endif
                                    <span class="text-[10px] text-neutral-400 font-medium">Server: {{ $order->user?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-wrap gap-2 max-w-[200px]">
                                    @foreach($order->items->take(5) as $item)
                                        <div class="group/item relative flex flex-col items-center">
                                            <div class="h-10 w-10 rounded-xl ring-2 ring-white dark:ring-neutral-900 bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-[10px] font-black text-neutral-500 shadow-sm transition-all group-hover/item:scale-110 group-hover/item:bg-blue-600 group-hover/item:text-white" title="{{ $item->product?->name }}">
                                                @if($item->product?->image_url)
                                                    <img src="{{ $item->product->image_url }}" class="h-full w-full object-cover rounded-xl" alt="">
                                                @else
                                                    {{ substr($item->product?->name ?? '?', 0, 1) }}
                                                @endif
                                                <div class="absolute -top-2 -right-2 h-5 w-5 rounded-full bg-blue-600 text-white text-[8px] flex items-center justify-center ring-2 ring-white dark:ring-neutral-900 font-black">
                                                    {{ $item->quantity }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($order->items->count() > 5)
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-neutral-100 dark:bg-neutral-800 text-[10px] font-black text-neutral-400 ring-2 ring-white dark:ring-neutral-900 shadow-sm border border-neutral-200 dark:border-neutral-700">
                                            +{{ $order->items->count() - 5 }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col">
                                    <span class="font-black text-neutral-900 dark:text-neutral-100">${{ number_format($order->total_amount, 2) }}</span>
                                    <span class="text-[10px] text-neutral-400 uppercase tracking-tighter">{{ $order->payment_method }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-neutral-700 dark:text-neutral-300">{{ $order->created_at->format('M d, H:i') }}</span>
                                    <span class="text-[10px] text-neutral-400 font-medium">{{ $order->created_at->diffForHumans() }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" 
                                        onclick="event.stopPropagation(); window.open('{{ route('pos.receipt', $order) }}', '_blank', 'width=400,height=600')"
                                        class="p-2.5 rounded-xl bg-neutral-50 hover:bg-blue-50 dark:bg-neutral-800 dark:hover:bg-blue-900/20 text-neutral-400 hover:text-blue-600 transition-all border border-neutral-100 dark:border-neutral-700 hover:border-blue-100 dark:hover:border-blue-900/50"
                                        title="Print Receipt">
                                        <flux:icon.printer class="w-4 h-4" />
                                    </button>
                                    
                                    <div class="relative group/select">
                                        <select wire:change="updateStatus({{ $order->id }}, $event.target.value)" 
                                            onclick="event.stopPropagation()"
                                            class="appearance-none text-[10px] font-black uppercase tracking-wider pl-3 pr-8 py-2 rounded-xl border-neutral-200 dark:bg-neutral-800 dark:border-neutral-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all cursor-pointer">
                                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        <flux:icon.chevron-down class="absolute right-3 top-1/2 -translate-y-1/2 w-3 h-3 text-neutral-400 pointer-events-none" />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center gap-4 max-w-xs mx-auto">
                                    <div class="w-20 h-20 rounded-full bg-neutral-50 dark:bg-neutral-900 flex items-center justify-center">
                                        @if($hasActiveFilters)
                                            <flux:icon.funnel class="w-10 h-10 text-neutral-200 dark:text-neutral-800" />
                                        @else
                                            <flux:icon.clipboard-list class="w-10 h-10 text-neutral-200 dark:text-neutral-800" />
                                        @endif
                                    </div>
                                    <div>
                                        @if($hasActiveFilters)
                                            <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">No orders match your filters</h3>
                                            <p class="text-sm text-neutral-500">Try adjusting your search or filter criteria.</p>
                                            <button type="button" wire:click="clearFilters" class="mt-3 text-xs font-black text-blue-600 hover:underline uppercase tracking-widest">
                                                Clear all filters
                                            </button>
                                        @else
                                            <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">No orders found</h3>
                                            <p class="text-sm text-neutral-500">Transactions will appear here once they are processed in the POS.</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($orders->hasPages())
            <div class="px-8 py-6 bg-neutral-50/50 dark:bg-neutral-800/50 border-t border-neutral-100 dark:border-neutral-800">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    @if($showOrderModal && $viewingOrder)
        <div class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xl">
            <div class="bg-white dark:bg-neutral-900 rounded-[2.5rem] shadow-2xl w-full max-w-3xl overflow-hidden border border-neutral-200 dark:border-neutral-800">
                <div class="p-6 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                            <flux:icon.receipt-percent class="w-6 h-6 text-white" />
                        </div>
                        <div class="flex flex-col leading-none">
                            <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Order #{{ $viewingOrder->id }}</span>
                            <span class="text-lg font-black text-neutral-800 dark:text-neutral-100 tracking-tight">{{ $viewingOrder->created_at->format('M d, H:i') }}</span>
                        </div>
                    </div>
                    <button type="button" wire:click="closeOrder" class="w-10 h-10 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-500 hover:text-neutral-900 dark:text-neutral-300 transition-all border border-neutral-200 dark:border-neutral-700">
                        <flux:icon.x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-6 grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <div class="lg:col-span-3 space-y-4">
                        <div class="rounded-2xl border border-neutral-100 dark:border-neutral-800 bg-white/60 dark:bg-neutral-900/40 p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Customer</span>
                                <span class="text-xs font-black text-neutral-800 dark:text-neutral-100">
                                    {{ $viewingOrder->customer?->name ?: ($viewingOrder->table_number ? 'Table '.$viewingOrder->table_number : 'Walk-in') }}
                                </span>
                            </div>
                            @if($viewingOrder->customer)
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Contact</span>
                                    <span class="text-xs font-bold text-neutral-600 dark:text-neutral-300">
                                        {{ $viewingOrder->customer->email ?: ($viewingOrder->customer->mobile ?: '—') }}
                                    </span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Server</span>
                                <span class="text-xs font-bold text-neutral-600 dark:text-neutral-300">{{ $viewingOrder->user?->name ?? 'System' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Status</span>
                                <span class="text-[10px] font-black uppercase tracking-widest {{ $viewingOrder->status === 'completed' ? 'text-emerald-600' : ($viewingOrder->status === 'cancelled' ? 'text-red-500' : 'text-amber-600') }}">
                                    {{ $viewingOrder->status }}
                                </span>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-neutral-100 dark:border-neutral-800 bg-white/60 dark:bg-neutral-900/40 p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Items</span>
                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">{{ $viewingOrder->items->count() }} lines</span>
                            </div>
                            <div class="space-y-3">
                                @foreach($viewingOrder->items as $item)
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="font-black text-neutral-800 dark:text-neutral-100 text-sm tracking-tight">
                                                {{ (int) $item->quantity }}x {{ $item->product?->name }}
                                                @if($item->variant)
                                                    <span class="text-neutral-400 font-black text-xs">({{ $item->variant->receipt_label ?: $item->variant->name }})</span>
                                                @endif
                                            </div>
                                            @if($item->addons->count() > 0)
                                                <div class="text-[10px] font-bold text-neutral-500 mt-1">
                                                    + {{ $item->addons->pluck('name')->implode(', ') }}
                                                </div>
                                            @endif
                                            @if($item->components->count() > 0)
                                                <div class="text-[10px] font-bold text-neutral-500 mt-1">
                                                    Set: {{ $item->components->pluck('name')->implode(', ') }}
                                                </div>
                                            @endif
                                            @if($item->notes)
                                                <div class="mt-2 inline-flex items-center px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200/60 dark:border-amber-800/30 text-[10px] font-black text-amber-700 dark:text-amber-300">
                                                    {{ $item->notes }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="font-black text-neutral-900 dark:text-neutral-100 tabular-nums text-sm">
                                            ${{ number_format((float) $item->subtotal, 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 space-y-4">
                        <div class="rounded-2xl border border-neutral-100 dark:border-neutral-800 bg-white/60 dark:bg-neutral-900/40 p-5 space-y-2">
                            <div class="flex justify-between text-neutral-500 font-black tracking-tight text-sm">
                                <span>Subtotal</span>
                                <span class="font-black tabular-nums">${{ number_format((float) $viewingOrder->subtotal_amount, 2) }}</span>
                            </div>
                            @if((float) ($viewingOrder->discount_amount ?? 0) > 0)
                                <div class="flex justify-between text-neutral-500 font-black tracking-tight text-sm">
                                    <span>Discount</span>
                                    <span class="font-black text-red-500 tabular-nums">- ${{ number_format((float) $viewingOrder->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            @if((float) ($viewingOrder->tax_amount ?? 0) > 0)
                                <div class="flex justify-between text-neutral-500 font-black tracking-tight text-sm">
                                    <span>Tax</span>
                                    <span class="font-black text-emerald-600 tabular-nums">${{ number_format((float) $viewingOrder->tax_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="pt-2 border-t border-neutral-200 dark:border-neutral-800 flex justify-between items-baseline">
                                <span class="text-sm font-black text-neutral-900 dark:text-neutral-100 tracking-widest uppercase">Total</span>
                                <span class="text-2xl font-black text-blue-600 tracking-tighter tabular-nums">${{ number_format((float) $viewingOrder->total_amount, 2) }}</span>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-neutral-100 dark:border-neutral-800 bg-white/60 dark:bg-neutral-900/40 p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Payment</span>
                                <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">{{ $viewingOrder->payment_method }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Paid</span>
                                <span class="text-sm font-black text-neutral-800 dark:text-neutral-100 tabular-nums">${{ number_format((float) ($viewingOrder->amount_paid ?? 0), 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Change</span>
                                <span class="text-sm font-black text-neutral-800 dark:text-neutral-100 tabular-nums">${{ number_format((float) ($viewingOrder->change_amount ?? 0), 2) }}</span>
                            </div>
                        </div>

                        @if($viewingOrder->voucher_code || ((int) ($viewingOrder->points_redeemed ?? 0) > 0))
                            <div class="rounded-2xl border border-neutral-100 dark:border-neutral-800 bg-white/60 dark:bg-neutral-900/40 p-5 space-y-2">
                                <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Loyalty / Voucher</div>
                                @if($viewingOrder->voucher_code)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-neutral-600 dark:text-neutral-300">Voucher</span>
                                        <span class="text-xs font-black text-neutral-800 dark:text-neutral-100 uppercase tracking-widest">{{ $viewingOrder->voucher_code }}</span>
                                    </div>
                                @endif
                                @if((int) ($viewingOrder->points_redeemed ?? 0) > 0)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-neutral-600 dark:text-neutral-300">Points Redeemed</span>
                                        <span class="text-xs font-black text-neutral-800 dark:text-neutral-100 tabular-nums">{{ (int) $viewingOrder->points_redeemed }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <button type="button" onclick="window.open('{{ route('pos.receipt', $viewingOrder) }}?preview=1', '_blank', 'width=420,height=700')" class="w-full py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-black shadow-xl shadow-blue-500/20 transition-all uppercase tracking-widest text-[10px]">
                            View Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
