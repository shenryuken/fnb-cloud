<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Unshifted Orders</h1>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Orders created without an active shift. Reassign them to a shift for accurate reporting.</p>
                </div>
                <div class="flex items-center gap-2">
                    <flux:badge size="lg" :color="count($this->unshiftedOrders) > 0 ? 'warning' : 'success'">
                        {{ count($this->unshiftedOrders) }} Total
                    </flux:badge>
                </div>
            </div>
        </div>

        <!-- Filters & Actions -->
        <div class="mb-6 space-y-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
            <div class="grid gap-4 sm:grid-cols-4">
                <!-- Search -->
                <div>
                    <flux:input 
                        wire:model.live="searchQuery" 
                        type="text" 
                        placeholder="Search by Order ID, Table..." 
                        icon="magnifying-glass"
                        clearable
                    />
                </div>

                <!-- Filter Status -->
                <div>
                    <flux:select wire:model.live="filterStatus">
                        <option value="all">All Status</option>
                        <option value="pending">Pending/Processing</option>
                        <option value="completed">Completed</option>
                        <option value="paid">Paid</option>
                    </flux:select>
                </div>

                <!-- Sort -->
                <div>
                    <flux:select wire:model.live="sortBy">
                        <option value="created_at">Newest First</option>
                        <option value="total_amount">Highest Amount</option>
                        <option value="status">Status</option>
                    </flux:select>
                </div>

                <!-- Shift Selection -->
                <div>
                    <flux:select wire:model="selectedShiftId">
                        <option value="">Select Shift to Assign</option>
                        @foreach($this->availableShifts as $shift)
                            <option value="{{ $shift->id }}">
                                Shift #{{ $shift->id }} - {{ $shift->started_at?->format('M d, H:i') }}
                            </option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            <!-- Bulk Actions -->
            @if(count($this->unshiftedOrders) > 0)
                <div class="flex items-center justify-between border-t border-zinc-200 dark:border-zinc-800 pt-4">
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input 
                                type="checkbox"
                                wire:click="{{ count($selectedOrderIds) === count($this->unshiftedOrders) ? 'deselectAll' : 'selectAll' }}"
                                @checked(count($selectedOrderIds) > 0 && count($selectedOrderIds) === count($this->unshiftedOrders))
                                class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-pink-600 focus:ring-pink-500 dark:bg-zinc-800"
                            />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Select all on page</span>
                        </label>
                        @if(!empty($selectedOrderIds))
                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                {{ count($selectedOrderIds) }} selected
                            </span>
                            <button wire:click="deselectAll" class="text-xs text-pink-600 hover:text-pink-700 font-medium">
                                Clear
                            </button>
                        @endif
                    </div>

                    @if(!empty($selectedOrderIds) && $selectedShiftId)
                        <button 
                            wire:click="reassignToShift"
                            class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium text-sm transition-all flex items-center gap-2">
                            <flux:icon.arrow-path class="w-4 h-4" />
                            Reassign {{ count($selectedOrderIds) }} Orders
                        </button>
                    @endif
                </div>
            @endif
        </div>

        <!-- Orders Table -->
        @if(count($this->unshiftedOrders) > 0)
            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                <table class="w-full">
                    <thead class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input 
                                    type="checkbox"
                                    wire:click="{{ count($selectedOrderIds) === count($this->unshiftedOrders) ? 'deselectAll' : 'selectAll' }}"
                                    @checked(count($selectedOrderIds) > 0 && count($selectedOrderIds) === count($this->unshiftedOrders))
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-pink-600 focus:ring-pink-500 dark:bg-zinc-800"
                                />
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Order ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Table / Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Created</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach($this->unshiftedOrders as $order)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="px-4 py-3">
                                    <input 
                                        type="checkbox"
                                        wire:click="toggleOrder({{ $order->id }})"
                                        @checked(in_array($order->id, $selectedOrderIds))
                                        class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-pink-600 focus:ring-pink-500 dark:bg-zinc-800"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100">#{{ $order->id }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($order->order_type === 'dine_in')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-semibold">
                                            <flux:icon.building-storefront class="w-3 h-3" />
                                            {{ $order->table_number }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 text-xs font-semibold">
                                            <flux:icon.shopping-bag class="w-3 h-3" />
                                            Takeaway
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $order->customer?->name ?? 'Walk-in' }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-zinc-100">
                                    RM {{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <flux:badge 
                                            :color="$order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'info')"
                                            size="sm"
                                        >
                                            {{ ucfirst($order->status) }}
                                        </flux:badge>
                                        @if($order->payment_status === 'paid')
                                            <flux:badge color="success" size="sm">Paid</flux:badge>
                                        @else
                                            <flux:badge color="warning" size="sm">Unpaid</flux:badge>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $order->created_at?->format('M d, H:i') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="#" wire:click.prevent="addNote({{ $order->id }})" class="text-zinc-400 hover:text-pink-600 transition-colors" title="Add note">
                                            <flux:icon.pencil class="w-4 h-4" />
                                        </a>
                                    <a href="{{ route('orders.show', $order) }}" target="_blank" class="text-zinc-400 hover:text-blue-600 transition-colors" title="View order">
                                        <flux:icon.arrow-up-right class="w-4 h-4" />
                                    </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $this->unshiftedOrders->links() }}
            </div>
        @else
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-12 text-center">
                <div class="mx-auto mb-4 w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <flux:icon.check-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
                </div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-1">All Caught Up!</h3>
                <p class="text-zinc-500 dark:text-zinc-400">No unshifted orders found. All orders are properly assigned to shifts.</p>
            </div>
        @endif
    </div>
</div>
