<div class="flex flex-col gap-6 p-4 md:p-8">
    {{-- Header --}}
    <flux:header class="flex-wrap gap-3">
        <flux:heading size="xl" class="flex items-center gap-2">
            <flux:icon.banknotes class="w-7 h-7 text-pink-500" />
            Shift Management
        </flux:heading>
        <flux:text class="text-zinc-400">Manage cash drawer and shifts</flux:text>

        <flux:spacer />

        @if($this->currentShift)
            <flux:button wire:click="openMovementModal" icon="arrows-right-left" variant="ghost">
                Cash In/Out
            </flux:button>
            <flux:button wire:click="closeShiftModal" icon="lock-closed" variant="danger">
                Close Shift
            </flux:button>
        @else
            <flux:button wire:click="openShiftModal" icon="lock-open" variant="primary">
                Open Shift
            </flux:button>
        @endif
    </flux:header>

    {{-- Current Shift Status --}}
    @if($this->currentShift)
        @php $shift = $this->currentShift; @endphp
        <div class="rounded-2xl border border-green-200 dark:border-green-900/50 bg-green-50 dark:bg-green-900/20 p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-green-500 flex items-center justify-center">
                        <flux:icon.clock class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider">Active Shift</p>
                        <p class="text-lg font-bold text-zinc-800 dark:text-zinc-100">
                            Opened by {{ $shift->user->name }}
                        </p>
                        <p class="text-sm text-zinc-500">
                            {{ $shift->opened_at->format('M d, Y g:i A') }} &bull; {{ $shift->duration }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-6">
                    <div class="text-center">
                        <p class="text-xs text-zinc-400 uppercase tracking-wider">Opening Cash</p>
                        <p class="text-xl font-bold text-zinc-800 dark:text-zinc-100">RM {{ number_format($shift->opening_cash, 2) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-zinc-400 uppercase tracking-wider">Expected Cash</p>
                        <p class="text-xl font-bold text-green-600">RM {{ number_format($shift->expected_cash, 2) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-zinc-400 uppercase tracking-wider">Orders</p>
                        <p class="text-xl font-bold text-zinc-800 dark:text-zinc-100">{{ $shift->order_count }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-zinc-400 uppercase tracking-wider">Total Sales</p>
                        <p class="text-xl font-bold text-pink-500">RM {{ number_format($shift->total_sales, 2) }}</p>
                    </div>
                </div>
            </div>

            {{-- Cash Movements for Current Shift --}}
            @if($shift->cashMovements->count() > 0)
                <div class="mt-4 pt-4 border-t border-green-200 dark:border-green-900/50">
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Recent Cash Movements</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($shift->cashMovements->take(5) as $movement)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium
                                {{ $movement->type === 'cash_in' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : '' }}
                                {{ $movement->type === 'cash_out' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : '' }}
                                {{ $movement->type === 'adjustment' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : '' }}
                            ">
                                @if($movement->type === 'cash_in')
                                    <flux:icon.arrow-down class="w-3 h-3" />
                                @elseif($movement->type === 'cash_out')
                                    <flux:icon.arrow-up class="w-3 h-3" />
                                @else
                                    <flux:icon.arrows-right-left class="w-3 h-3" />
                                @endif
                                {{ $movement->typeLabel }}: RM {{ number_format($movement->amount, 2) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="rounded-2xl border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500 flex items-center justify-center shrink-0">
                <flux:icon.exclamation-triangle class="w-6 h-6 text-white" />
            </div>
            <div>
                <p class="font-bold text-zinc-800 dark:text-zinc-100">No Active Shift</p>
                <p class="text-sm text-zinc-500">Open a shift to start taking orders and track cash.</p>
            </div>
        </div>
    @endif

    {{-- Shift History --}}
    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="p-4 border-b border-zinc-100 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-base font-bold text-zinc-800 dark:text-zinc-100">Shift History</h3>
            <input type="date" wire:model.live="dateFilter"
                class="px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-sm text-zinc-700 dark:text-zinc-300"
            />
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Opened By</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Time</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Duration</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Opening</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Sales</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Expected</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Actual</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Diff</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($this->shifts as $shift)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-zinc-800 dark:text-zinc-100">{{ $shift->user->name }}</p>
                                @if($shift->closedBy && $shift->closedBy->id !== $shift->user->id)
                                    <p class="text-xs text-zinc-400">Closed by {{ $shift->closedBy->name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-500">
                                <p>{{ $shift->opened_at->format('M d, g:i A') }}</p>
                                @if($shift->closed_at)
                                    <p class="text-xs text-zinc-400">to {{ $shift->closed_at->format('g:i A') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-zinc-600 dark:text-zinc-400">{{ $shift->duration }}</td>
                            <td class="px-4 py-3 text-right font-mono text-zinc-600 dark:text-zinc-400">
                                RM {{ number_format($shift->opening_cash, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-zinc-800 dark:text-zinc-200 font-semibold">
                                RM {{ number_format($shift->total_sales, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-zinc-600 dark:text-zinc-400">
                                RM {{ number_format($shift->expected_cash, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-zinc-600 dark:text-zinc-400">
                                @if($shift->actual_cash !== null)
                                    RM {{ number_format($shift->actual_cash, 2) }}
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                @if($shift->difference !== null)
                                    <span class="{{ $shift->difference >= 0 ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                        {{ $shift->difference >= 0 ? '+' : '' }}RM {{ number_format($shift->difference, 2) }}
                                    </span>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($shift->status === 'open')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                        Open
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-500">
                                        Closed
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="viewShift({{ $shift->id }})" class="p-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-400 hover:text-zinc-600 transition-colors">
                                    <flux:icon.eye class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center">
                                <flux:icon.banknotes class="w-12 h-12 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" />
                                <p class="text-zinc-500">No shifts found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->shifts->hasPages())
            <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
                {{ $this->shifts->links() }}
            </div>
        @endif
    </div>

    {{-- Open Shift Modal --}}
    @if($showOpenModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-md border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-green-500 flex items-center justify-center">
                            <flux:icon.lock-open class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Open Shift</h3>
                            <p class="text-xs text-zinc-400">Start a new shift and set opening cash</p>
                        </div>
                    </div>
                    <button wire:click="$set('showOpenModal', false)" class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-zinc-600 transition-colors">
                        <flux:icon.x-mark class="w-4 h-4" />
                    </button>
                </div>

                <form wire:submit="openShift" class="p-5 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-zinc-600 dark:text-zinc-400 block mb-1.5">Opening Cash (RM)</label>
                        <input type="number" step="0.01" min="0" wire:model="openingCash"
                            class="w-full px-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 text-lg font-mono focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all"
                            placeholder="0.00"
                        />
                        @error('openingCash') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-600 dark:text-zinc-400 block mb-1.5">Notes (Optional)</label>
                        <textarea wire:model="openingNotes" rows="2"
                            class="w-full px-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all resize-none"
                            placeholder="Any notes for this shift..."
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showOpenModal', false)" class="px-4 py-2 rounded-lg text-sm font-medium text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-green-500 hover:bg-green-600 text-white font-semibold text-sm transition-all flex items-center gap-2">
                            <flux:icon.play class="w-4 h-4" />
                            Start Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Close Shift Modal --}}
    @if($showCloseModal && $this->currentShift)
        @php $shift = $this->currentShift; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-lg border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-red-500 flex items-center justify-center">
                            <flux:icon.lock-closed class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Close Shift</h3>
                            <p class="text-xs text-zinc-400">Count your drawer and close the shift</p>
                        </div>
                    </div>
                    <button wire:click="$set('showCloseModal', false)" class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-zinc-600 transition-colors">
                        <flux:icon.x-mark class="w-4 h-4" />
                    </button>
                </div>

                <form wire:submit="closeShift" class="p-5 space-y-5">
                    {{-- Shift Summary --}}
                    <div class="rounded-xl bg-zinc-50 dark:bg-zinc-800 p-4 space-y-3">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Shift Summary</p>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Opening Cash</span>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300">RM {{ number_format($shift->opening_cash, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Orders</span>
                                <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $shift->order_count }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Cash Sales</span>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300">RM {{ number_format($shift->cash_sales, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Card Sales</span>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300">RM {{ number_format($shift->card_sales, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">E-Wallet Sales</span>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300">RM {{ number_format($shift->ewallet_sales, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Total Sales</span>
                                <span class="font-mono font-bold text-pink-500">RM {{ number_format($shift->total_sales, 2) }}</span>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-zinc-200 dark:border-zinc-700 flex justify-between">
                            <span class="font-semibold text-zinc-700 dark:text-zinc-300">Expected Cash in Drawer</span>
                            <span class="font-mono font-bold text-lg text-green-600">RM {{ number_format($shift->expected_cash, 2) }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-600 dark:text-zinc-400 block mb-1.5">Actual Cash Counted (RM)</label>
                        <input type="number" step="0.01" min="0" wire:model="actualCash"
                            class="w-full px-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 text-lg font-mono focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all"
                            placeholder="0.00"
                        />
                        @error('actualCash') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                        @if($actualCash !== null && is_numeric($actualCash))
                            @php $diff = $actualCash - $shift->expected_cash; @endphp
                            <p class="mt-2 text-sm font-semibold {{ $diff >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Difference: {{ $diff >= 0 ? '+' : '' }}RM {{ number_format($diff, 2) }}
                                @if(abs($diff) > 0.01)
                                    @if($diff > 0)
                                        (Overage)
                                    @else
                                        (Shortage)
                                    @endif
                                @endif
                            </p>
                        @endif
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-600 dark:text-zinc-400 block mb-1.5">Closing Notes (Optional)</label>
                        <textarea wire:model="closingNotes" rows="2"
                            class="w-full px-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all resize-none"
                            placeholder="Any notes about the shift close..."
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showCloseModal', false)" class="px-4 py-2 rounded-lg text-sm font-medium text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold text-sm transition-all flex items-center gap-2">
                            <flux:icon.lock-closed class="w-4 h-4" />
                            Close Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Cash Movement Modal --}}
    @if($showMovementModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-md border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-pink-500 flex items-center justify-center">
                            <flux:icon.arrows-right-left class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Cash In/Out</h3>
                            <p class="text-xs text-zinc-400">Record a cash movement</p>
                        </div>
                    </div>
                    <button wire:click="$set('showMovementModal', false)" class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-zinc-600 transition-colors">
                        <flux:icon.x-mark class="w-4 h-4" />
                    </button>
                </div>

                <form wire:submit="saveCashMovement" class="p-5 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-zinc-600 dark:text-zinc-400 block mb-2">Type</label>
                        <div class="flex gap-2">
                            <button type="button" wire:click="$set('movementType', 'cash_in')"
                                class="flex-1 px-4 py-2.5 rounded-xl border-2 text-sm font-semibold transition-all flex items-center justify-center gap-2
                                    {{ $movementType === 'cash_in' ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-600' : 'border-zinc-200 dark:border-zinc-700 text-zinc-500 hover:border-zinc-300' }}
                                "
                            >
                                <flux:icon.arrow-down class="w-4 h-4" />
                                Cash In
                            </button>
                            <button type="button" wire:click="$set('movementType', 'cash_out')"
                                class="flex-1 px-4 py-2.5 rounded-xl border-2 text-sm font-semibold transition-all flex items-center justify-center gap-2
                                    {{ $movementType === 'cash_out' ? 'border-red-500 bg-red-50 dark:bg-red-900/20 text-red-600' : 'border-zinc-200 dark:border-zinc-700 text-zinc-500 hover:border-zinc-300' }}
                                "
                            >
                                <flux:icon.arrow-up class="w-4 h-4" />
                                Cash Out
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-600 dark:text-zinc-400 block mb-1.5">Amount (RM)</label>
                        <input type="number" step="0.01" min="0.01" wire:model="movementAmount"
                            class="w-full px-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 text-lg font-mono focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all"
                            placeholder="0.00"
                        />
                        @error('movementAmount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-600 dark:text-zinc-400 block mb-1.5">Reason</label>
                        <input type="text" wire:model="movementReason"
                            class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all"
                            placeholder="e.g., Change for bills, Petty cash withdrawal"
                        />
                        @error('movementReason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-600 dark:text-zinc-400 block mb-1.5">Notes (Optional)</label>
                        <textarea wire:model="movementNotes" rows="2"
                            class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all resize-none"
                            placeholder="Additional details..."
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showMovementModal', false)" class="px-4 py-2 rounded-lg text-sm font-medium text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold text-sm transition-all">
                            Record Movement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- View Shift Modal --}}
    @if($showViewModal && $this->viewingShift)
        @php $shift = $this->viewingShift; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-2xl border border-zinc-200 dark:border-zinc-800 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-800 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg {{ $shift->status === 'open' ? 'bg-green-500' : 'bg-zinc-500' }} flex items-center justify-center">
                            <flux:icon.banknotes class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Shift Details</h3>
                            <p class="text-xs text-zinc-400">{{ $shift->opened_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>
                    <button wire:click="closeViewModal" class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-zinc-600 transition-colors">
                        <flux:icon.x-mark class="w-4 h-4" />
                    </button>
                </div>

                <div class="p-5 space-y-5 overflow-y-auto flex-1">
                    {{-- Shift Info --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-xl bg-zinc-50 dark:bg-zinc-800 p-4">
                            <p class="text-xs text-zinc-400 uppercase tracking-wider mb-1">Opened By</p>
                            <p class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $shift->user->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $shift->opened_at->format('g:i A') }}</p>
                        </div>
                        <div class="rounded-xl bg-zinc-50 dark:bg-zinc-800 p-4">
                            <p class="text-xs text-zinc-400 uppercase tracking-wider mb-1">Closed By</p>
                            @if($shift->closedBy)
                                <p class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $shift->closedBy->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $shift->closed_at?->format('g:i A') }}</p>
                            @else
                                <p class="text-zinc-500">Still open</p>
                            @endif
                        </div>
                    </div>

                    {{-- Sales Summary --}}
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-3">Sales Summary</p>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">{{ $shift->order_count }}</p>
                                <p class="text-xs text-zinc-400">Orders</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-pink-500">RM {{ number_format($shift->total_sales, 2) }}</p>
                                <p class="text-xs text-zinc-400">Total Sales</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-red-500">RM {{ number_format($shift->refunds_total, 2) }}</p>
                                <p class="text-xs text-zinc-400">Refunds ({{ $shift->refunds_count }})</p>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700 grid grid-cols-4 gap-3 text-sm">
                            <div class="text-center">
                                <p class="font-mono font-semibold text-zinc-700 dark:text-zinc-300">RM {{ number_format($shift->cash_sales, 2) }}</p>
                                <p class="text-xs text-zinc-400">Cash</p>
                            </div>
                            <div class="text-center">
                                <p class="font-mono font-semibold text-zinc-700 dark:text-zinc-300">RM {{ number_format($shift->card_sales, 2) }}</p>
                                <p class="text-xs text-zinc-400">Card</p>
                            </div>
                            <div class="text-center">
                                <p class="font-mono font-semibold text-zinc-700 dark:text-zinc-300">RM {{ number_format($shift->ewallet_sales, 2) }}</p>
                                <p class="text-xs text-zinc-400">E-Wallet</p>
                            </div>
                            <div class="text-center">
                                <p class="font-mono font-semibold text-zinc-700 dark:text-zinc-300">RM {{ number_format($shift->qris_sales, 2) }}</p>
                                <p class="text-xs text-zinc-400">QRIS</p>
                            </div>
                        </div>
                    </div>

                    {{-- Cash Reconciliation --}}
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-3">Cash Reconciliation</p>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Opening Cash</span>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300">RM {{ number_format($shift->opening_cash, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">+ Cash Sales</span>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300">RM {{ number_format($shift->cash_sales, 2) }}</span>
                            </div>
                            @php
                                $cashIn = $shift->cashMovements->where('type', 'cash_in')->sum('amount');
                                $cashOut = $shift->cashMovements->where('type', 'cash_out')->sum('amount');
                            @endphp
                            @if($cashIn > 0)
                                <div class="flex justify-between text-green-600">
                                    <span>+ Cash In</span>
                                    <span class="font-mono">RM {{ number_format($cashIn, 2) }}</span>
                                </div>
                            @endif
                            @if($cashOut > 0)
                                <div class="flex justify-between text-red-600">
                                    <span>- Cash Out</span>
                                    <span class="font-mono">RM {{ number_format($cashOut, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between pt-2 border-t border-zinc-200 dark:border-zinc-700 font-semibold">
                                <span class="text-zinc-700 dark:text-zinc-300">Expected Cash</span>
                                <span class="font-mono text-green-600">RM {{ number_format($shift->expected_cash, 2) }}</span>
                            </div>
                            @if($shift->actual_cash !== null)
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">Actual Cash</span>
                                    <span class="font-mono text-zinc-700 dark:text-zinc-300">RM {{ number_format($shift->actual_cash, 2) }}</span>
                                </div>
                                <div class="flex justify-between font-semibold {{ $shift->difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    <span>Difference</span>
                                    <span class="font-mono">{{ $shift->difference >= 0 ? '+' : '' }}RM {{ number_format($shift->difference, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Cash Movements --}}
                    @if($shift->cashMovements->count() > 0)
                        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-3">Cash Movements</p>
                            <div class="space-y-2">
                                @foreach($shift->cashMovements as $movement)
                                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center
                                                {{ $movement->type === 'cash_in' ? 'bg-green-100 dark:bg-green-900/30 text-green-600' : '' }}
                                                {{ $movement->type === 'cash_out' ? 'bg-red-100 dark:bg-red-900/30 text-red-600' : '' }}
                                                {{ $movement->type === 'adjustment' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600' : '' }}
                                            ">
                                                @if($movement->type === 'cash_in')
                                                    <flux:icon.arrow-down class="w-4 h-4" />
                                                @elseif($movement->type === 'cash_out')
                                                    <flux:icon.arrow-up class="w-4 h-4" />
                                                @else
                                                    <flux:icon.arrows-right-left class="w-4 h-4" />
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $movement->reason }}</p>
                                                <p class="text-xs text-zinc-400">{{ $movement->user->name }} &bull; {{ $movement->created_at->format('g:i A') }}</p>
                                            </div>
                                        </div>
                                        <span class="font-mono font-semibold {{ $movement->type === 'cash_in' ? 'text-green-600' : ($movement->type === 'cash_out' ? 'text-red-600' : 'text-amber-600') }}">
                                            {{ $movement->type === 'cash_out' ? '-' : '+' }}RM {{ number_format($movement->amount, 2) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    @if($shift->opening_notes || $shift->closing_notes)
                        <div class="rounded-xl bg-zinc-50 dark:bg-zinc-800 p-4 space-y-2">
                            @if($shift->opening_notes)
                                <div>
                                    <p class="text-xs text-zinc-400 uppercase tracking-wider">Opening Notes</p>
                                    <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $shift->opening_notes }}</p>
                                </div>
                            @endif
                            @if($shift->closing_notes)
                                <div>
                                    <p class="text-xs text-zinc-400 uppercase tracking-wider">Closing Notes</p>
                                    <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $shift->closing_notes }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="p-4 border-t border-zinc-100 dark:border-zinc-800 shrink-0">
                    <button wire:click="closeViewModal" class="w-full px-4 py-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-semibold text-sm hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-all">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
