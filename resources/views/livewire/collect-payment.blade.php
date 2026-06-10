<div>
@if($showModal)
<div class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/70 backdrop-blur-md animate-in fade-in duration-200" x-data x-on:keydown.escape.window="$wire.close()">
    <div class="bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-zinc-700">
        @if($order || count($orders))
            @php
                $isMultiOrder = $this->isMultiOrder;
                $total = $this->effectiveTotal;
            @endphp
            {{-- Header --}}
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-5 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                            <flux:icon.credit-card class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            @if($isMultiOrder)
                                <h3 class="text-lg font-bold">Combined Payment</h3>
                                <p class="text-amber-100 text-xs">
                                    {{ count($orders) }} orders combined
                                </p>
                            @else
                                <h3 class="text-lg font-bold">Collect Payment</h3>
                                <p class="text-amber-100 text-xs">
                                    Order #{{ $order->id }} - {{ $order->table_number ?? 'Takeaway' }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="block text-xs text-amber-200">Amount Due</span>
                        <span class="text-2xl font-bold">{{ $this->currency }} {{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-4 space-y-4 overflow-y-auto flex-1 scrollbar-hide">
            {{-- Order Summary --}}
            <div class="rounded-lg bg-zinc-800/50 p-4 space-y-3">
                @if($isMultiOrder)
                    {{-- Show combined orders --}}
                    <div class="text-xs text-zinc-400 font-semibold uppercase tracking-wider mb-2">Combined Orders</div>
                    <div class="max-h-32 overflow-y-auto space-y-1.5">
                        @foreach($orders as $orderData)
                            <div class="p-2 rounded-lg border {{ ($orderData['order_type'] ?? 'dine_in') === 'takeaway' ? 'border-orange-200/30 bg-orange-900/20' : 'border-zinc-700 bg-zinc-800' }}">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-semibold text-zinc-200">
                                        Order #{{ $orderData['id'] }}
                                        @if(($orderData['order_type'] ?? 'dine_in') === 'takeaway')
                                            <span class="text-xs px-1.5 py-0.5 rounded bg-orange-200/20 text-orange-300">Takeaway</span>
                                        @endif
                                    </span>
                                    <span class="font-bold text-zinc-100">{{ $this->currency }} {{ number_format($orderData['total_amount'], 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between items-center pt-2 mt-1 border-t border-zinc-700">
                        <span class="font-semibold text-zinc-100">Combined Total</span>
                        <span class="text-lg font-bold text-green-500">{{ $this->currency }} {{ number_format($total, 2) }}</span>
                    </div>
                @else
                    {{-- Show items for unpaid order --}}
                    <div class="text-xs text-zinc-400 font-semibold uppercase tracking-wider mb-2">Order Items</div>
                    <div class="max-h-32 overflow-y-auto space-y-1.5">
                        @foreach($order->items as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-200">{{ $item->quantity }}x {{ $item->product->name }}</span>
                                <span class="font-semibold text-zinc-100">{{ $this->currency }} {{ number_format((float) $item->subtotal, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between text-zinc-400 text-sm pt-2 border-t border-zinc-700">
                        <span>Subtotal</span>
                        <span class="font-semibold">{{ $this->currency }} {{ number_format((float) $order->subtotal_amount, 2) }}</span>
                    </div>

                    {{-- Discount/Voucher row with edit button --}}
                    <button type="button" wire:click="openDiscountModal" class="w-full flex justify-between text-sm hover:bg-zinc-700/50 rounded px-1 py-0.5 -mx-1 transition-colors">
                        <span class="text-zinc-400">Discount / Voucher</span>
                        @if($this->discountAmount > 0)
                            <span class="font-semibold text-red-500">- {{ $this->currency }} {{ number_format($this->discountAmount, 2) }}</span>
                        @else
                            <span class="text-pink-500 text-sm">+ Add</span>
                        @endif
                    </button>

                    @if($this->taxAmount > 0)
                        <div class="flex justify-between text-zinc-400 text-sm">
                            <span>Tax ({{ number_format((float) $this->taxRate, 2) }}%)</span>
                            <span class="font-semibold text-green-500">{{ $this->currency }} {{ number_format($this->taxAmount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center pt-2 mt-1 border-t border-zinc-700">
                        <span class="font-semibold text-zinc-100 text-lg">Total</span>
                        <span class="text-xl font-bold text-amber-500">{{ $this->currency }} {{ number_format($total, 2) }}</span>
                    </div>
                @endif
            </div>

            {{-- Split Payment Toggle --}}
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-400">Split Payment</span>
                <button type="button"
                    wire:click="toggleSplitPayment"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none
                        {{ $isSplitPayment ? 'bg-pink-500' : 'bg-zinc-700' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                        {{ $isSplitPayment ? 'translate-x-6' : 'translate-x-1' }}">
                    </span>
                </button>
            </div>

            {{-- Payment Method Selection --}}
            @if(!$isSplitPayment)
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['cash' => 'Cash', 'card' => 'Card', 'ewallet' => 'eWallet'] as $method => $label)
                        <label class="relative flex flex-col items-center justify-center gap-1 p-3 rounded-xl border cursor-pointer transition-all {{ $this->paymentMethod === $method ? 'border-pink-500 bg-pink-950/30' : 'border-zinc-700 hover:border-zinc-600 bg-zinc-800/50' }}">
                            <input type="radio" wire:model.live="paymentMethod" value="{{ $method }}" class="sr-only">
                            <div class="w-9 h-9 rounded-lg bg-zinc-800 flex items-center justify-center border border-zinc-700">
                                @if($method === 'cash')
                                    <flux:icon.banknotes class="w-5 h-5 {{ $this->paymentMethod === $method ? 'text-pink-500' : 'text-zinc-500' }}" />
                                @elseif($method === 'card')
                                    <flux:icon.credit-card class="w-5 h-5 {{ $this->paymentMethod === $method ? 'text-pink-500' : 'text-zinc-500' }}" />
                                @else
                                    <flux:icon.device-phone-mobile class="w-5 h-5 {{ $this->paymentMethod === $method ? 'text-pink-500' : 'text-zinc-500' }}" />
                                @endif
                            </div>
                            <span class="font-semibold text-sm {{ $this->paymentMethod === $method ? 'text-pink-500' : 'text-zinc-400' }}">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                {{-- Amount Received & Change (for Cash only) --}}
                @if($this->paymentMethod === 'cash')
                    <div class="space-y-3 animate-in slide-in-from-top-2 duration-200">
                        {{-- Quick amount buttons --}}
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="setExact" class="px-4 py-2 text-xs font-semibold rounded-xl bg-pink-500 text-white hover:bg-pink-600 transition-all">Exact</button>
                            @foreach([5, 10, 20, 50, 100] as $amount)
                                <button type="button" wire:click="$set('amountReceived', {{ $amount }})" class="px-4 py-2 text-xs font-semibold rounded-xl bg-zinc-800 border border-zinc-700 text-zinc-300 hover:border-pink-500 hover:text-pink-500 transition-all">+ {{ $this->currency }}{{ $amount }}</button>
                            @endforeach
                        </div>

                        {{-- Amount input and change display --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xl font-semibold text-zinc-400">{{ $this->currency }}</span>
                                <input type="number" step="0.01" min="0" wire:model.live="amountReceived" class="w-full pl-11 pr-10 py-4 text-3xl font-bold rounded-xl border border-zinc-700 bg-zinc-800 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all text-zinc-100 text-right" placeholder="0.00" onfocus="const n=parseFloat(this.value); if(Number.isFinite(n)&&n>0) this.value=n.toFixed(2); else this.value=''" onblur="const n=parseFloat(this.value); this.value=Number.isFinite(n)&&n>0?n.toFixed(2):'0.00'">
                                <button type="button" wire:click="$set('amountReceived', 0)" class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 rounded flex items-center justify-center text-zinc-400 hover:text-red-500 transition-colors" title="Clear amount">
                                    <flux:icon.x-mark class="w-4 h-4" />
                                </button>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-green-900/30 border border-green-800/50">
                                <div>
                                    <span class="text-xs text-green-400 block">Change</span>
                                    <span class="text-xl font-bold text-green-500">{{ $this->currency }} {{ number_format($this->changeAmount, 2) }}</span>
                                </div>
                                <flux:icon.banknotes class="w-6 h-6 text-green-400" />
                            </div>
                        </div>
                    </div>
                @endif
            @else
                {{-- Split Payment Mode --}}
                <div class="space-y-3 animate-in slide-in-from-top-2 duration-200">
                    {{-- Remaining balance indicator --}}
                    <div class="flex items-center justify-between p-3 rounded-xl {{ $this->splitRemaining <= 0.001 ? 'bg-green-900/30 border border-green-800/50' : 'bg-amber-900/30 border border-amber-800/50' }}">
                        <span class="text-sm font-medium {{ $this->splitRemaining <= 0.001 ? 'text-green-400' : 'text-amber-400' }}">{{ $this->splitRemaining <= 0.001 ? 'Fully Covered' : 'Remaining' }}</span>
                        <span class="text-lg font-bold {{ $this->splitRemaining <= 0.001 ? 'text-green-500' : 'text-amber-500' }}">{{ $this->currency }} {{ number_format($this->splitRemaining, 2) }}</span>
                    </div>

                    {{-- Existing splits --}}
                    @if(!empty($this->paymentSplits))
                        <div class="space-y-1.5">
                            @foreach($this->paymentSplits as $index => $split)
                                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-zinc-800/50 border border-zinc-700">
                                    <div class="w-7 h-7 rounded-md bg-zinc-700 flex items-center justify-center shrink-0">
                                        @if($split['method'] === 'cash')
                                            <flux:icon.banknotes class="w-4 h-4 text-pink-500" />
                                        @elseif($split['method'] === 'card')
                                            <flux:icon.credit-card class="w-4 h-4 text-pink-500" />
                                        @else
                                            <flux:icon.device-phone-mobile class="w-4 h-4 text-pink-500" />
                                        @endif
                                    </div>
                                    <span class="text-xs font-medium text-zinc-400 capitalize w-14 shrink-0">{{ $split['method'] }}</span>
                                    <span class="flex-1 text-right font-semibold text-zinc-100 tabular-nums">{{ $this->currency }} {{ number_format($split['amount'], 2) }}</span>
                                    <button type="button" wire:click="removeSplit({{ $index }})" class="w-6 h-6 rounded flex items-center justify-center text-zinc-400 hover:text-red-500 transition-colors shrink-0">
                                        <flux:icon.x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Add split row --}}
                    @if($this->splitRemaining > 0.001)
                        <div class="flex flex-wrap gap-2 items-end">
                            {{-- Method selector --}}
                            <div class="flex gap-1.5">
                                @foreach(['cash' => 'Cash', 'card' => 'Card', 'ewallet' => 'eWallet'] as $method => $label)
                                    <button type="button" wire:click="$set('splitMethod', '{{ $method }}')" class="flex flex-col items-center justify-center gap-0.5 w-12 h-12 rounded-xl border transition-all {{ $this->splitMethod === $method ? 'border-pink-500 bg-pink-950/30' : 'border-zinc-700 bg-zinc-800/50 hover:border-zinc-600' }}">
                                        @if($method === 'cash')
                                            <flux:icon.banknotes class="w-4 h-4 {{ $this->splitMethod === $method ? 'text-pink-500' : 'text-zinc-400' }}" />
                                        @elseif($method === 'card')
                                            <flux:icon.credit-card class="w-4 h-4 {{ $this->splitMethod === $method ? 'text-pink-500' : 'text-zinc-400' }}" />
                                        @else
                                            <flux:icon.device-phone-mobile class="w-4 h-4 {{ $this->splitMethod === $method ? 'text-pink-500' : 'text-zinc-400' }}" />
                                        @endif
                                        <span class="text-[10px] font-medium {{ $this->splitMethod === $method ? 'text-pink-500' : 'text-zinc-400' }}">{{ $label }}</span>
                                    </button>
                                @endforeach
                            </div>

                            {{-- Amount input --}}
                            <div class="flex-1 relative min-w-[120px]">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-zinc-400">{{ $this->currency }}</span>
                                <input type="number" step="0.01" min="0.01" wire:model.live="splitAmount" class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-zinc-700 bg-zinc-800 text-sm font-semibold text-right text-zinc-100 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all" placeholder="0.00" onfocus="const n=parseFloat(this.value); if(Number.isFinite(n)&&n>0) this.value=n.toFixed(2)" onblur="const n=parseFloat(this.value); this.value=Number.isFinite(n)?n.toFixed(2):'0.00'">
                            </div>

                            {{-- Exact remaining button --}}
                            <button type="button" wire:click="setSplitExact" class="px-3 py-2.5 rounded-xl text-xs font-semibold bg-zinc-800 text-zinc-400 hover:bg-pink-950/30 hover:text-pink-500 border border-zinc-700 transition-all">Full</button>

                            {{-- Add button --}}
                            <button type="button" wire:click="addSplit" class="px-4 py-2.5 rounded-xl bg-pink-500 hover:bg-pink-600 text-white text-xs font-semibold transition-all flex items-center gap-1.5 shrink-0">
                                <flux:icon.plus class="w-4 h-4" />
                                Add
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Note Input (Optional) --}}
            <div class="space-y-1">
                <textarea wire:model.live="orderNotes" rows="1" class="w-full rounded-xl border border-zinc-700 bg-zinc-800/50 px-3 py-3 text-sm text-zinc-200 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all resize-none" placeholder="Add a note (optional)..."></textarea>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="button" wire:click="close" class="flex-1 px-4 py-3 text-sm font-semibold rounded-xl text-zinc-400 hover:text-white transition-all">Cancel</button>
                <button type="button" wire:click="collect" class="flex-1 px-4 py-3 text-sm font-semibold rounded-xl bg-amber-500 hover:bg-amber-600 text-white transition-all flex items-center justify-center gap-2">
                    <flux:icon.check class="w-4 h-4" />
                    {{ $isMultiOrder ? 'Pay All' : 'Collect Payment' }}
                </button>
            </div>
            </div>
        @endif
    </div>
</div>
@endif

{{-- Discount Modal --}}
@if($showDiscountModal)
<div class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/70 backdrop-blur-md animate-in fade-in duration-200">
    <div class="bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-zinc-700">
        <div class="p-4 border-b border-zinc-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-pink-500 flex items-center justify-center">
                    <flux:icon.tag class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-zinc-100">Promotions</h3>
                    <p class="text-xs text-zinc-400">Apply discount, voucher or points</p>
                </div>
            </div>
            <button type="button" wire:click="closeDiscountModal" class="w-8 h-8 rounded-lg bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-zinc-200 transition-colors">
                <flux:icon.x-mark class="w-5 h-5" />
            </button>
        </div>

        <div class="p-4 space-y-4">
            <div class="flex p-1 bg-zinc-800 rounded-lg">
                <button type="button" wire:click="$set('discountTab', 'discount')" class="flex-1 py-2 rounded-md text-xs font-semibold transition-all {{ $discountTab === 'discount' ? 'bg-zinc-700 text-pink-500 shadow-sm' : 'text-zinc-500 hover:text-zinc-300' }}">
                    Discount
                </button>
                <button type="button" wire:click="$set('discountTab', 'voucher')" class="flex-1 py-2 rounded-md text-xs font-semibold transition-all {{ $discountTab === 'voucher' ? 'bg-zinc-700 text-pink-500 shadow-sm' : 'text-zinc-500 hover:text-zinc-300' }}">
                    Voucher
                </button>
                <button type="button" wire:click="$set('discountTab', 'customer')" class="flex-1 py-2 rounded-md text-xs font-semibold transition-all {{ $discountTab === 'customer' ? 'bg-zinc-700 text-pink-500 shadow-sm' : 'text-zinc-500 hover:text-zinc-300' }}">
                    Customer
                </button>
                <button type="button" wire:click="$set('discountTab', 'points')" class="flex-1 py-2 rounded-md text-xs font-semibold transition-all {{ $discountTab === 'points' ? 'bg-zinc-700 text-pink-500 shadow-sm' : 'text-zinc-500 hover:text-zinc-300' }}">
                    Points
                </button>
            </div>

            @if($discountTab === 'discount')
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-zinc-400">Manual Discount</span>
                        <button type="button" wire:click="clearDiscount" class="text-xs text-zinc-400 hover:text-red-500 transition-colors">
                            Clear
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-2 flex-1 rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2">
                            <div class="flex p-0.5 bg-zinc-700 rounded-md">
                                <button type="button" wire:click="$set('discountInputType', 'percent')" class="px-2.5 py-1 rounded text-xs font-semibold transition-all {{ $discountInputType === 'percent' ? 'bg-zinc-600 text-pink-500 shadow-sm' : 'text-zinc-400' }}">%</button>
                                <button type="button" wire:click="$set('discountInputType', 'fixed')" class="px-2.5 py-1 rounded text-xs font-semibold transition-all {{ $discountInputType === 'fixed' ? 'bg-zinc-600 text-pink-500 shadow-sm' : 'text-zinc-400' }}">{{ $this->currency }}</button>
                            </div>
                            <input type="number" step="0.01" wire:model.live="discountInputValue" class="flex-1 bg-transparent border-none focus:ring-0 text-lg font-bold text-zinc-100 text-right tabular-nums p-0" placeholder="0">
                        </div>
                        <button type="button" wire:click="applyManualDiscount"
                            class="px-4 py-2.5 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold transition-all text-sm">
                            Apply
                        </button>
                    </div>
                </div>
            @endif

            @if($discountTab === 'voucher')
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-zinc-400">Voucher Code</span>
                        <button type="button" wire:click="clearVoucher" class="text-xs text-zinc-400 hover:text-red-500 transition-colors">
                            Clear
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="text" wire:model.live="voucherCode" class="flex-1 rounded-lg border-zinc-700 bg-zinc-800 px-3 py-2.5 font-semibold focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all uppercase text-sm text-zinc-100" placeholder="ENTER CODE">
                        <button type="button" wire:click="applyVoucher" class="px-4 py-2.5 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold transition-all text-sm">
                            Apply
                        </button>
                    </div>

                    @if(filled($this->appliedVoucherCode))
                        <div class="flex items-center justify-between p-3 rounded-lg bg-green-900/30 border border-green-800/50">
                            <span class="text-sm font-medium text-green-400">Applied</span>
                            <span class="font-bold text-green-500 uppercase">{{ $this->appliedVoucherCode }}</span>
                        </div>
                    @endif
                </div>
            @endif

            @if($discountTab === 'customer')
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-zinc-400">Select Customer</span>
                        @if($this->customer)
                            <button type="button" wire:click="clearCustomer" class="text-xs text-zinc-400 hover:text-red-500 transition-colors">
                                Clear
                            </button>
                        @endif
                    </div>

                    @if($this->customer)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-green-900/30 border border-green-800/50">
                            <div>
                                <span class="text-xs text-green-400 block">Selected</span>
                                <span class="font-semibold text-green-500">{{ $this->customer->name }}</span>
                                <span class="text-xs text-green-400 block mt-0.5">Points: {{ (int) $this->customer->points_balance }}</span>
                            </div>
                        </div>
                    @else
                        <div class="space-y-2">
                            <div>
                                <span class="text-xs text-zinc-400 block mb-1">Find Customer</span>
                                <input type="text" wire:model.live="customerSearch" class="w-full rounded-lg border-zinc-700 bg-zinc-800 px-3 py-2.5 font-semibold focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all text-sm text-zinc-100" placeholder="Search name / email / mobile">
                            </div>

                            @if(!empty($this->customerSearchResults))
                                <div class="space-y-1.5 max-h-40 overflow-y-auto">
                                    @foreach($this->customerSearchResults as $customer)
                                        <button type="button" wire:click="selectCustomer({{ $customer['id'] }})" class="w-full flex items-center justify-between p-2.5 rounded-lg bg-zinc-800 border border-zinc-700 hover:border-pink-500 hover:bg-pink-950/30 transition-all">
                                            <div>
                                                <span class="text-sm font-semibold text-zinc-100">{{ $customer['name'] }}</span>
                                                @if(filled($customer['mobile']))
                                                    <span class="text-xs text-zinc-400 block">{{ $customer['mobile'] }}</span>
                                                @endif
                                            </div>
                                            <span class="text-xs text-pink-500 font-semibold">{{ (int) $customer['points_balance'] }} pts</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="pt-2 border-t border-zinc-700 mt-2">
                                <div class="text-xs text-zinc-400 mb-1">Register New Customer</div>
                                <div class="space-y-2">
                                    <input type="text" wire:model.live="newCustomerName" class="w-full rounded-lg border-zinc-700 bg-zinc-800 px-3 py-2 font-semibold focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all text-sm text-zinc-100" placeholder="Customer Name *">
                                    <input type="email" wire:model.live="newCustomerEmail" class="w-full rounded-lg border-zinc-700 bg-zinc-800 px-3 py-2 font-semibold focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all text-sm text-zinc-100" placeholder="Email (optional)">
                                    <input type="text" wire:model.live="newCustomerMobile" class="w-full rounded-lg border-zinc-700 bg-zinc-800 px-3 py-2 font-semibold focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all text-sm text-zinc-100" placeholder="Mobile (optional)">
                                    <button type="button" wire:click="registerCustomer" class="w-full px-4 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold transition-all text-sm">
                                        Register Customer
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if($discountTab === 'points')
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-zinc-400">Redeem Points</span>
                        <button type="button" wire:click="clearPoints" class="text-xs text-zinc-400 hover:text-red-500 transition-colors">
                            Clear
                        </button>
                    </div>

                    @if($this->customer)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-800 border border-zinc-700">
                            <div>
                                <span class="text-xs text-zinc-400 block">Customer</span>
                                <span class="font-semibold text-zinc-100">{{ $this->customer->name }}</span>
                                <span class="text-xs text-zinc-400 block mt-0.5">Points: {{ (int) $this->customer->points_balance }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.live="pointsToRedeem"
                                class="flex-1 rounded-lg border-zinc-700 bg-zinc-800 px-3 py-2.5 font-semibold focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all text-sm text-zinc-100" placeholder="Points ({{ (int) $this->pointsRedeemPoints }} = {{ $this->currency }}{{ number_format((float) $this->pointsRedeemAmount, 2) }})">
                            <button type="button" wire:click="applyPoints"
                                class="px-4 py-2.5 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold transition-all text-sm">
                                Redeem
                            </button>
                        </div>

                        <p class="text-xs text-zinc-400">
                            {{ (int) $this->pointsRedeemPoints }} pts = {{ $this->currency }}{{ number_format((float) $this->pointsRedeemAmount, 2) }}
                        </p>

                        @if(($this->appliedPoints ?? 0) > 0)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-green-900/30 border border-green-800/50">
                                <span class="text-sm font-medium text-green-400">Applied Points</span>
                                <span class="font-bold text-green-500 tabular-nums">{{ (int) $this->appliedPoints }}</span>
                            </div>
                        @endif
                    @else
                        <div class="p-3 rounded-lg bg-zinc-800 border border-zinc-700 text-sm text-zinc-400">
                            Select a customer first to use points.
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Footer with current discount --}}
        @if($this->discountAmount > 0)
        <div class="p-4 border-t border-zinc-700 bg-zinc-800/50">
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-400">Current Discount</span>
                <span class="font-bold text-red-500 tabular-nums">- {{ $this->currency }}{{ number_format($this->discountAmount, 2) }}</span>
            </div>
        </div>
        @endif
    </div>
</div>
@endif
</div>
