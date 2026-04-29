<div x-data="{}" class="flex flex-col lg:flex-row min-h-[calc(100vh-4rem)] lg:h-[calc(100vh-4rem)] gap-4 p-4 overflow-y-auto lg:overflow-hidden bg-zinc-50 dark:bg-zinc-950" wire:poll.15s>
    {{-- No Shift Warning --}}
    @if(!$this->currentShift)
        <div class="fixed top-0 left-0 right-0 z-50 bg-amber-500 text-white px-4 py-2 text-center text-sm font-semibold flex items-center justify-center gap-3">
            <flux:icon.exclamation-triangle class="w-5 h-5" />
            <span>No active shift. Orders will not be linked to a shift.</span>
            <a href="{{ route('manage.shifts.index') }}" wire:navigate class="underline hover:no-underline">Open Shift</a>
        </div>
    @endif
    
    {{-- Adding to Existing Order Banner --}}
    @if($existingOrder)
        <div class="fixed top-0 left-0 right-0 z-50 bg-blue-600 text-white px-4 py-2 text-center text-sm font-semibold flex items-center justify-center gap-3 {{ !$this->currentShift ? 'top-10' : '' }}">
            <flux:icon.plus-circle class="w-5 h-5" />
            <span>Adding items to Order #{{ $existingOrder->id }} ({{ ucfirst($existingOrder->kds_status) }})</span>
            <span class="text-blue-200">|</span>
            <span>Table: {{ $tableNumber }}</span>
        </div>
    @endif

    <!-- Left Side: Product Selection -->
    <div class="w-full lg:flex-1 flex flex-col gap-4 lg:overflow-hidden {{ !$this->currentShift ? 'pt-10' : '' }} {{ $existingOrder ? 'pt-10' : '' }}">
        <!-- Top Bar: Search and Categories -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 space-y-3">
            {{-- Shift status indicator --}}
            @if($this->currentShift)
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 text-green-600">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        <span class="font-semibold">Shift Active</span>
                        <span class="text-zinc-400">{{ $this->currentShift->opened_at->format('g:i A') }}</span>
                    </div>
                    <a href="{{ route('manage.shifts.index') }}" wire:navigate class="text-zinc-400 hover:text-pink-500 transition-colors">
                        <flux:icon.banknotes class="w-4 h-4" />
                    </a>
                </div>
            @endif
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" 
                    placeholder="Search menu items..." 
                    class="w-full pl-11 pr-4 py-2.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all placeholder:text-zinc-400">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400">
                    <flux:icon.magnifying-glass class="w-4 h-4" />
                </div>
            </div>
            
            <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide -mx-1 px-1">
                <button wire:click="$set('selectedCategoryId', null)" 
                    class="px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-all
                    {{ is_null($selectedCategoryId) ? 'bg-pink-500 text-white' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                    All Menu
                </button>
                @foreach($this->categories as $category)
                    <button wire:click="$set('selectedCategoryId', {{ $category->id }})" 
                        class="px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-all
                        {{ $selectedCategoryId === $category->id ? 'bg-pink-500 text-white' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Product Grid -->
        <div class="lg:flex-1 lg:overflow-y-auto grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 pb-4 scrollbar-hide">
            @foreach($this->products as $product)
                <div wire:click="quickAddProduct({{ $product->id }})" 
                    class="group flex flex-col bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden cursor-pointer hover:border-pink-500/50 hover:shadow-lg transition-all duration-200 relative">
                    <div class="{{ $product->tile_color ? 'flex-1' : 'aspect-[4/3]' }} bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center overflow-hidden relative">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @elseif($product->tile_color)
                            <div class="w-full h-full flex flex-col items-center justify-center gap-1 text-center px-3" style="background-color: {{ $product->tile_color }};">
                                <span class="text-white text-sm sm:text-base font-semibold leading-tight line-clamp-2">{{ $product->name }}</span>
                                <span class="text-white/90 text-base font-bold">RM {{ number_format($product->price, 2) }}</span>
                            </div>
                        @else
                            <flux:icon.package class="w-10 h-10 text-zinc-300 dark:text-zinc-600" />
                        @endif

                        {{-- Badges --}}
                        <div class="absolute top-2 left-2 flex flex-col gap-1.5 pointer-events-none">
                            @if(isset($this->autoBadges[$product->id]))
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $this->autoBadges[$product->id] === 'Top Sale' ? 'bg-red-500 text-white' : 'bg-orange-500 text-white' }}">
                                    {{ $this->autoBadges[$product->id] }}
                                </span>
                            @endif

                            @if($product->badge_text)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-500 text-white">
                                    {{ $product->badge_text }}
                                </span>
                            @endif
                        </div>

                        {{-- Customize button --}}
                        @if(($product->product_type ?? 'ala_carte') === 'set' || ($product->variants?->count() ?? 0) > 1 || ($product->addons?->count() ?? 0) > 0 || ($product->addonGroups?->count() ?? 0) > 0)
                            <button type="button" wire:click.stop="selectProduct({{ $product->id }})"
                                class="absolute bottom-2 left-2 right-2 py-2 rounded-lg bg-zinc-900/90 text-white text-xs font-semibold hover:bg-pink-500 transition-colors">
                                Customize
                            </button>
                        @endif
                    </div>
                    @if(!$product->tile_color || $product->image_url)
                        <div class="p-3">
                            <h3 class="font-semibold text-zinc-800 dark:text-zinc-100 truncate text-sm">
                                {{ $product->name }}
                            </h3>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-zinc-400">{{ $product->category->name }}</span>
                                <span class="text-sm font-bold text-pink-500">RM {{ number_format($product->price, 2) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Right Side: Current Cart -->
    <div class="hidden lg:flex w-[380px] flex-col bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden relative">
        @if($isKitchenBusy)
            <div class="absolute inset-x-0 top-0 z-50 bg-red-600/90 backdrop-blur-md px-4 sm:px-6 py-2 flex items-center justify-between border-b border-red-500 shadow-xl shadow-red-600/20 animate-in slide-in-from-top duration-500">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center">
                        <flux:icon.fire class="w-5 h-5 text-white animate-pulse" />
                    </div>
                    <div class="flex flex-col leading-none">
                        <span class="text-[10px] font-black text-white/70 uppercase tracking-[0.2em]">Kitchen Alert</span>
                        <span class="text-xs font-black text-white uppercase tracking-tight">KITCHEN IS CURRENTLY BUSY</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-white/10 rounded-lg border border-white/20">
                    <div class="w-2 h-2 rounded-full bg-white animate-ping"></div>
                    <span class="text-[10px] font-black text-white uppercase tracking-widest">Delay expected</span>
                </div>
            </div>
        @endif
        
        <div class="p-4 border-b border-zinc-100 dark:border-zinc-800 {{ $isKitchenBusy ? 'mt-12' : '' }} transition-all duration-500">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-pink-500 flex items-center justify-center">
                        <flux:icon.shopping-bag class="w-4 h-4 text-white" />
                    </div>
                    <h3 class="text-lg font-bold text-zinc-800 dark:text-zinc-100">Current Order</h3>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="px-2.5 py-1 bg-pink-50 dark:bg-pink-900/20 text-pink-600 dark:text-pink-400 text-xs font-semibold rounded-lg">
                        {{ count($cart) }} Items
                    </span>
                    <button type="button" wire:click="openUnpaidOrdersModal" class="px-2 py-1 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-xs font-semibold hover:bg-amber-500 hover:text-white transition-all" title="View unpaid orders">
                        <flux:icon.clock class="w-3.5 h-3.5" />
                    </button>
                    <button type="button" wire:click="openHeldOrders" @disabled(count($this->heldOrders) === 0) class="px-2.5 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-500 text-xs font-semibold hover:bg-pink-500 hover:text-white disabled:opacity-40 transition-all">
                        Held ({{ count($this->heldOrders) }})
                    </button>
                </div>
            </div>

            <!-- Order Type Selection -->
            <div class="flex p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                <button wire:click="$set('orderType', 'dine_in')" 
                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-md text-xs font-semibold transition-all
                    {{ $orderType === 'dine_in' ? 'bg-white dark:bg-zinc-700 text-pink-600 shadow-sm' : 'text-zinc-400 hover:text-zinc-600' }}">
                    <flux:icon.building-storefront class="w-4 h-4" />
                    DINE IN
                </button>
                <button wire:click="$set('orderType', 'takeaway')" 
                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-md text-xs font-semibold transition-all
                    {{ $orderType === 'takeaway' ? 'bg-white dark:bg-zinc-700 text-pink-600 shadow-sm' : 'text-zinc-400 hover:text-zinc-600' }}">
                    <flux:icon.shopping-bag class="w-4 h-4" />
                    TAKE AWAY
                </button>
            </div>

            @if($orderType === 'dine_in')
                <div class="mt-3">
                    @if($this->availableTables->count() > 0)
                        <select wire:model.live="tableId"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all">
                            <option value="">Select Table</option>
                            @foreach($this->availableTables as $table)
                                <option value="{{ $table->id }}">
                                    {{ $table->name }} 
                                    ({{ $table->total_capacity }} seats)
                                    @if($table->status === 'occupied') - Occupied @endif
                                    @if($table->status === 'reserved') - Reserved @endif
                                    @if($table->status === 'dirty') - Needs Cleaning @endif
                                </option>
                            @endforeach
                        </select>
                        @if($this->selectedTable)
                            <div class="mt-2 px-3 py-2 rounded-lg text-xs
                                @if($this->selectedTable->status === 'available') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                @elseif($this->selectedTable->status === 'occupied') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                @elseif($this->selectedTable->status === 'reserved') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                @else bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400
                                @endif
                            ">
                                <span class="font-semibold">{{ $this->selectedTable->name }}</span>
                                - {{ $this->selectedTable->getStatusLabel() }}
                                @if($this->selectedTable->turn_time_formatted)
                                    ({{ $this->selectedTable->turn_time_formatted }})
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                                <flux:icon.hashtag class="w-4 h-4" />
                            </div>
                            <input type="text" wire:model.live="tableNumber" placeholder="Table Number" 
                                class="w-full pl-9 pr-4 py-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all">
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-2 scrollbar-hide">
            @forelse($cart as $index => $item)
                @php
                    $eachPrice = (float) ($item['unit_price'] ?? 0) + (float) ($item['addons_total'] ?? 0) + (float) ($item['set_total'] ?? 0);
                    $isExisting = !empty($item['existing']);
                @endphp
                <div class="p-3 rounded-lg border transition-all {{ $isExisting ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 opacity-70' : 'bg-zinc-50 dark:bg-zinc-800/50 border-zinc-100 dark:border-zinc-800 hover:border-pink-500/30' }}">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="min-w-0 flex-1">
                            <h4 class="font-semibold text-sm truncate {{ $isExisting ? 'text-blue-700 dark:text-blue-300' : 'text-zinc-800 dark:text-zinc-100' }}">
                                @if($isExisting)
                                    <flux:icon.lock-closed class="w-3 h-3 inline mr-1" />
                                @endif
                                {{ $item['product_name'] }}
                                @if(!empty($item['variant_name']))
                                    <span class="text-zinc-400">({{ $item['variant_name'] }})</span>
                                @endif
                            </h4>
                            @if(!empty($item['addon_names']))
                                <p class="text-xs text-zinc-400 truncate mt-0.5">+ {{ implode(', ', $item['addon_names']) }}</p>
                            @endif
                            @if(!empty($item['set_summary']))
                                <p class="text-xs text-zinc-400 truncate mt-0.5">Set: {{ $item['set_summary'] }}</p>
                            @endif
                            @if(!empty($item['notes']))
                                <p class="text-xs text-zinc-400 truncate mt-0.5 italic">{{ $item['notes'] }}</p>
                            @endif
                        </div>
                        @if(!$isExisting)
                            <button type="button" wire:click="removeFromCart({{ $index }})" class="w-6 h-6 flex items-center justify-center rounded-md text-zinc-400 hover:bg-red-500 hover:text-white transition-all">
                                <flux:icon.x-mark class="w-4 h-4" />
                            </button>
                        @else
                            <span class="text-xs text-blue-500 font-medium">In Kitchen</span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between">
                        @if(!$isExisting)
                            <div class="flex items-center bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <button wire:click="updateQuantity({{ $index }}, -1)" class="w-8 h-8 flex items-center justify-center text-zinc-400 hover:text-red-500 transition-colors">
                                    <flux:icon.minus class="w-3.5 h-3.5" />
                                </button>
                                <span class="w-8 text-center font-semibold text-sm text-zinc-800 dark:text-zinc-100 tabular-nums">{{ $item['quantity'] }}</span>
                                <button wire:click="updateQuantity({{ $index }}, 1)" class="w-8 h-8 flex items-center justify-center text-pink-500 hover:text-pink-600 transition-colors">
                                    <flux:icon.plus class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        @else
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-semibold">
                                Qty: {{ $item['quantity'] }}
                            </span>
                        @endif

                        <div class="text-right">
                            <div class="text-xs text-zinc-400 tabular-nums">RM {{ number_format($eachPrice, 2) }} ea</div>
                            <div class="font-bold text-sm text-zinc-900 dark:text-zinc-100 tabular-nums">RM {{ number_format($item['subtotal'], 2) }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center text-center py-12">
                    <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mb-4">
                        <flux:icon.shopping-cart class="w-8 h-8 text-zinc-300 dark:text-zinc-600" />
                    </div>
                    <h4 class="font-semibold text-zinc-800 dark:text-zinc-100 text-lg">Your cart is empty</h4>
                    <p class="text-sm text-zinc-400 mt-1">Add items from the menu to start an order</p>
                </div>
            @endforelse
        </div>

        <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-zinc-800 space-y-2">
            <div class="space-y-1.5">
                <div class="flex justify-between text-zinc-500 text-sm">
                    <span>Subtotal</span>
                    <span class="font-semibold">RM {{ number_format($subTotalAmount, 2) }}</span>
                </div>

                <button type="button" wire:click="openDiscountModal" class="w-full flex items-center justify-between text-zinc-500 text-sm rounded-lg hover:bg-white dark:hover:bg-zinc-900 px-2 py-1.5 -mx-2 transition-all">
                    <span>Discount / Voucher / Points</span>
                    <span class="{{ $discountAmount > 0 ? 'text-red-500 font-semibold' : 'text-pink-500' }}">
                        {{ $discountAmount > 0 ? '- RM ' . number_format($discountAmount, 2) : 'Add' }}
                    </span>
                </button>

                @if(($manualDiscountAmount ?? 0) > 0)
                    <div class="flex justify-between text-zinc-400 text-xs">
                        <span>Manual Discount</span>
                        <span class="tabular-nums">- RM {{ number_format((float) $manualDiscountAmount, 2) }}</span>
                    </div>
                @endif
                @if(filled($appliedVoucherCode))
                    <div class="flex justify-between text-zinc-400 text-xs">
                        <span>Voucher</span>
                        <span class="uppercase">{{ $appliedVoucherCode }}</span>
                    </div>
                @endif
                @if(($appliedPoints ?? 0) > 0)
                    <div class="flex justify-between text-zinc-400 text-xs">
                        <span>Points Redeemed</span>
                        <span class="tabular-nums">{{ (int) $appliedPoints }}</span>
                    </div>
                @endif
                @if($this->customer)
                    <div class="flex justify-between text-zinc-400 text-xs">
                        <span>Customer</span>
                        <span>{{ $this->customer->name }}</span>
                    </div>
                @endif
                @if(count($taxBreakdown) > 1)
                    @foreach($taxBreakdown as $row)
                        <div class="flex justify-between text-zinc-500 text-sm">
                            <span>{{ $row['name'] }} ({{ rtrim(rtrim(number_format((float) ($row['rate'] ?? 0), 2), '0'), '.') }}%)</span>
                            <span class="text-green-600 tabular-nums">RM {{ number_format((float) ($row['amount'] ?? 0), 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between text-zinc-500 text-sm">
                        <span>Tax Total</span>
                        <span class="text-green-600 tabular-nums">RM {{ number_format($taxAmount, 2) }}</span>
                    </div>
                @else
                    <div class="flex justify-between text-zinc-500 text-sm">
                        <span>Tax ({{ $taxLabel }})</span>
                        <span class="text-green-600 tabular-nums">RM {{ number_format($taxAmount, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between items-baseline pt-3 mt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 uppercase">Total</span>
                    <span class="text-xl font-bold text-pink-500 tabular-nums">RM {{ number_format($totalAmount, 2) }}</span>
                </div>
            </div>

            {{-- Payment Options: Pay Now vs Pay Later --}}
            <div class="flex gap-2">
                <button wire:click="startPayment" 
                    @disabled(empty($cart))
                    class="flex-1 py-3 rounded-lg bg-pink-500 hover:bg-pink-600 disabled:bg-zinc-200 disabled:dark:bg-zinc-800 disabled:text-zinc-400 text-white font-semibold transition-all flex items-center justify-center gap-2">
                    <flux:icon.credit-card class="w-4 h-4" />
                    <span class="text-sm">PAY NOW</span>
                </button>
                @if($orderType === 'dine_in')
                    <button wire:click="placeOrderPayLater" 
                        @disabled(empty($cart) || !$tableId)
                        title="{{ !$tableId ? 'Select a table first' : 'Send order to kitchen, pay later' }}"
                        class="flex-1 py-3 rounded-lg bg-amber-500 hover:bg-amber-600 disabled:bg-zinc-200 disabled:dark:bg-zinc-800 disabled:text-zinc-400 text-white font-semibold transition-all flex items-center justify-center gap-2">
                        <flux:icon.fire class="w-4 h-4" />
                        <span class="text-sm">KITCHEN</span>
                    </button>
                @endif
            </div>

            @if(count($cart) > 0)
                <div class="flex gap-2">
                    <button type="button" wire:click="holdOrder" class="flex-1 py-2 rounded-lg bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-xs font-semibold border border-zinc-200 dark:border-zinc-700 hover:border-pink-500 hover:text-pink-500 transition-all">
                        Hold Order
                    </button>
                    <button type="button" wire:click="clearCart" class="flex-1 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-500 text-xs font-semibold border border-zinc-200 dark:border-zinc-700 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all">
                        Clear Cart
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if($showCartMobile)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[9999] lg:hidden">
                <button type="button" wire:click="$set('showCartMobile', false)" class="absolute inset-0 bg-black/60 backdrop-blur-sm" aria-label="Close cart"></button>

                <div class="absolute inset-0 flex flex-col bg-white dark:bg-neutral-900">
                    @if($isKitchenBusy)
                        <div class="bg-red-600/90 backdrop-blur-md px-4 py-2 flex items-center justify-between border-b border-red-500 shadow-xl shadow-red-600/20">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center">
                                    <flux:icon.fire class="w-5 h-5 text-white animate-pulse" />
                                </div>
                                <div class="flex flex-col leading-none">
                                    <span class="text-[10px] font-black text-white/70 uppercase tracking-[0.2em]">Kitchen Alert</span>
                                    <span class="text-xs font-black text-white uppercase tracking-tight">KITCHEN IS CURRENTLY BUSY</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-1 bg-white/10 rounded-lg border border-white/20">
                                <div class="w-2 h-2 rounded-full bg-white animate-ping"></div>
                                <span class="text-[10px] font-black text-white uppercase tracking-widest">Delay expected</span>
                            </div>
                        </div>
                    @endif

                    <div class="p-3 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/30 dark:bg-neutral-900/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <button type="button" wire:click="$set('showCartMobile', false)" class="w-10 h-10 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-500 hover:text-neutral-900 dark:text-neutral-300 transition-all border border-neutral-200 dark:border-neutral-700">
                                    <flux:icon.x-mark class="w-5 h-5" />
                                </button>
                                <h3 class="text-xl font-black text-neutral-800 dark:text-neutral-100 flex items-center gap-3 tracking-tight">
                                    <div class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                                        <flux:icon.shopping-bag class="w-5 h-5 text-white" />
                                    </div>
                                    Current Order
                                </h3>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="px-4 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-black rounded-2xl tracking-widest uppercase">
                                    {{ count($cart) }} Items
                                </span>
                                <div class="flex items-center gap-2 mt-2">
                                    <button type="button" wire:click="openUnpaidOrdersModal" class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 text-[10px] font-black uppercase tracking-widest border border-amber-200/60 dark:border-amber-500/20 hover:bg-amber-500 hover:text-white transition-all">
                                        <flux:icon.clock class="w-4 h-4" />
                                        Unpaid
                                    </button>
                                    <button type="button" wire:click="openHeldOrders" @disabled(count($this->heldOrders) === 0) class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-neutral-900/5 dark:bg-white/5 text-neutral-500 dark:text-neutral-300 text-[10px] font-black uppercase tracking-widest border border-neutral-200 dark:border-neutral-700 hover:bg-blue-600 hover:text-white hover:border-blue-600 disabled:opacity-40 disabled:hover:bg-neutral-900/5 disabled:hover:text-neutral-500 transition-all">
                                        <flux:icon.pause class="w-4 h-4" />
                                        Held ({{ count($this->heldOrders) }})
                                    </button>
                                </div>
                                @if(count($cart) > 0)
                                    <button type="button" wire:click="clearCart" class="mt-2 flex items-center gap-2 px-3 py-1.5 rounded-xl bg-red-50 dark:bg-red-900/10 text-red-600 dark:text-red-400 text-[10px] font-black uppercase tracking-widest border border-red-200/60 dark:border-red-500/20 hover:bg-red-500 hover:text-white transition-all">
                                        <flux:icon.trash class="w-4 h-4" />
                                        Clear
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="flex p-1 bg-neutral-100 dark:bg-neutral-800 rounded-2xl">
                                <button wire:click="$set('orderType', 'dine_in')"
                                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl text-xs font-black transition-all
                                    {{ $orderType === 'dine_in' ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm' : 'text-neutral-400 hover:text-neutral-600' }}">
                                    <flux:icon.building-storefront class="w-4 h-4" />
                                    DINE IN
                                </button>
                                <button wire:click="$set('orderType', 'takeaway')"
                                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl text-xs font-black transition-all
                                    {{ $orderType === 'takeaway' ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm' : 'text-neutral-400 hover:text-neutral-600' }}">
                                    <flux:icon.shopping-bag class="w-4 h-4" />
                                    TAKE AWAY
                                </button>
                            </div>

                            @if($orderType === 'dine_in')
                                <div class="mt-3 animate-in fade-in slide-in-from-top-2 duration-300">
                                    @if($this->availableTables->count() > 0)
                                        <select wire:model.live="tableId"
                                            class="w-full px-3 py-2.5 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all">
                                            <option value="">Select Table</option>
                                            @foreach($this->availableTables as $table)
                                                <option value="{{ $table->id }}">
                                                    {{ $table->name }} ({{ $table->total_capacity }})
                                                    @if($table->status !== 'available') - {{ $table->getStatusLabel() }} @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="relative group">
                                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                                <flux:icon.hashtag class="w-4 h-4" />
                                            </div>
                                            <input type="text" wire:model.live="tableNumber" placeholder="Table Number"
                                                class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all">
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-3 space-y-2 scrollbar-hide">
                        @forelse($cart as $index => $item)
                            @php
                                $eachPrice = (float) ($item['unit_price'] ?? 0) + (float) ($item['addons_total'] ?? 0);
                            @endphp
                            <div class="group p-2.5 bg-white dark:bg-neutral-800/40 rounded-2xl border border-neutral-100 dark:border-neutral-800 hover:border-blue-500/30 hover:shadow-xl hover:shadow-blue-500/5 transition-all duration-300 relative overflow-hidden">
                                <div class="absolute top-0 left-0 w-1 h-full bg-blue-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                                <div class="flex items-start gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <h4 class="font-black text-neutral-800 dark:text-neutral-100 leading-tight tracking-tight text-[13px] truncate">
                                                    {{ $item['product_name'] }}
                                                    @if(!empty($item['variant_name']))
                                                        <span class="text-neutral-400 font-black">({{ $item['variant_name'] }})</span>
                                                    @endif
                                                </h4>
                                                @if(!empty($item['addon_names']))
                                                    <div class="mt-1 text-[10px] font-bold text-neutral-400 truncate">
                                                        Extras: {{ implode(', ', $item['addon_names']) }}
                                                    </div>
                                                @endif
                                                @if(!empty($item['notes']))
                                                    <div class="mt-1 text-[10px] font-bold text-neutral-400 truncate">
                                                        Note: {{ $item['notes'] }}
                                                    </div>
                                                @endif
                                            </div>
                                            <button type="button" wire:click="removeFromCart({{ $index }})" class="w-7 h-7 flex items-center justify-center rounded-xl bg-red-50 dark:bg-red-900/10 text-red-500 hover:bg-red-500 hover:text-white transition-all">
                                                <flux:icon.x-mark class="w-4 h-4" />
                                            </button>
                                        </div>

                                        <div class="mt-2.5 flex items-center justify-between gap-3">
                                            <div class="flex items-center bg-neutral-50 dark:bg-neutral-900 rounded-2xl border border-neutral-100 dark:border-neutral-800 p-1 shadow-inner">
                                                <button wire:click="updateQuantity({{ $index }}, -1)" class="w-7 h-7 flex items-center justify-center hover:bg-white dark:hover:bg-neutral-800 hover:shadow-md rounded-xl transition-all duration-200 text-neutral-400 hover:text-red-500">
                                                    <flux:icon.minus class="w-4 h-4" />
                                                </button>
                                                <span class="w-10 text-center font-black text-sm tracking-tighter text-neutral-800 dark:text-neutral-100 tabular-nums">{{ $item['quantity'] }}</span>
                                                <button wire:click="updateQuantity({{ $index }}, 1)" class="w-7 h-7 flex items-center justify-center hover:bg-white dark:hover:bg-neutral-800 hover:shadow-md rounded-xl transition-all duration-200 text-blue-600">
                                                    <flux:icon.plus class="w-4 h-4" />
                                                </button>
                                            </div>

                                            <div class="text-right leading-none">
                                                <div class="text-[9px] font-black text-neutral-400 uppercase tracking-widest tabular-nums">${{ number_format($eachPrice, 2) }} each</div>
                                                <div class="mt-1 font-black text-[15px] text-neutral-900 dark:text-neutral-100 tracking-tighter tabular-nums">${{ number_format($item['subtotal'], 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="h-full flex flex-col items-center justify-center text-neutral-400 p-12 text-center">
                                <div class="relative mb-6">
                                    <div class="w-24 h-24 bg-neutral-50 dark:bg-neutral-800 rounded-[2rem] flex items-center justify-center animate-pulse">
                                        <flux:icon.shopping-cart class="w-12 h-12 opacity-10" />
                                    </div>
                                    <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-white dark:bg-neutral-900 rounded-2xl shadow-xl flex items-center justify-center">
                                        <flux:icon.plus class="w-5 h-5 text-blue-600" />
                                    </div>
                                </div>
                                <h4 class="font-black text-neutral-800 dark:text-neutral-100 text-xl tracking-tight">Your cart is empty</h4>
                                <p class="text-sm mt-2 font-medium text-neutral-500">Add delicious items from the menu to start an order</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="p-3 bg-neutral-50 dark:bg-neutral-800/50 border-t border-neutral-100 dark:border-neutral-800 space-y-3">
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-neutral-500 font-black tracking-tight text-sm">
                                <span>Subtotal</span>
                                <span class="font-black">${{ number_format($subTotalAmount, 2) }}</span>
                            </div>

                            <button type="button" wire:click="openDiscountModal" class="w-full flex items-center justify-between gap-3 text-neutral-500 font-black tracking-tight text-sm rounded-xl hover:bg-white/60 dark:hover:bg-neutral-900/40 px-2 py-2 transition-all">
                                <span>Discount / Voucher / Points</span>
                                <span class="{{ $discountAmount > 0 ? 'text-red-500' : 'text-blue-600 dark:text-blue-400' }}">
                                    {{ $discountAmount > 0 ? '- $' . number_format($discountAmount, 2) : 'Add' }}
                                </span>
                            </button>

                            @if(($manualDiscountAmount ?? 0) > 0)
                                <div class="flex justify-between text-neutral-500 font-black tracking-tight text-[10px]">
                                    <span>Manual Discount</span>
                                    <span class="font-black text-neutral-400 tabular-nums">- ${{ number_format((float) $manualDiscountAmount, 2) }}</span>
                                </div>
                            @endif
                            @if(filled($appliedVoucherCode))
                                <div class="flex justify-between text-neutral-500 font-black tracking-tight text-[10px]">
                                    <span>Voucher</span>
                                    <span class="font-black text-neutral-400 uppercase tracking-widest">{{ $appliedVoucherCode }}</span>
                                </div>
                            @endif
                            @if(($appliedPoints ?? 0) > 0)
                                <div class="flex justify-between text-neutral-500 font-black tracking-tight text-[10px]">
                                    <span>Points Redeemed</span>
                                    <span class="font-black text-neutral-400 tabular-nums">{{ (int) $appliedPoints }}</span>
                                </div>
                            @endif
                            @if($this->customer)
                                <div class="flex justify-between text-neutral-500 font-black tracking-tight text-[10px]">
                                    <span>Customer</span>
                                    <span class="font-black text-neutral-400">{{ $this->customer->name }}</span>
                                </div>
                            @endif
                            @if(count($taxBreakdown) > 1)
                                @foreach($taxBreakdown as $row)
                                    <div class="flex justify-between text-neutral-500 font-black tracking-tight text-sm">
                                        <span>{{ $row['name'] }} ({{ rtrim(rtrim(number_format((float) ($row['rate'] ?? 0), 2), '0'), '.') }}%)</span>
                                        <span class="font-black text-emerald-600 tabular-nums">${{ number_format((float) ($row['amount'] ?? 0), 2) }}</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between text-neutral-500 font-black tracking-tight text-sm">
                                    <span>Tax Total</span>
                                    <span class="font-black text-emerald-600 tabular-nums">${{ number_format($taxAmount, 2) }}</span>
                                </div>
                            @else
                                <div class="flex justify-between text-neutral-500 font-black tracking-tight text-sm">
                                    <span>Tax ({{ $taxLabel }})</span>
                                    <span class="font-black text-emerald-600 tabular-nums">${{ number_format($taxAmount, 2) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between items-baseline pt-2 border-t border-neutral-200 dark:border-neutral-800">
                                <span class="text-sm font-black text-neutral-900 dark:text-neutral-100 tracking-widest uppercase">Total</span>
                                <span class="text-2xl font-black text-blue-600 tracking-tighter shadow-blue-500/10 tabular-nums">${{ number_format($totalAmount, 2) }}</span>
                            </div>
                        </div>

                        {{-- Payment Options: Pay Now vs Pay Later --}}
                        <div class="flex gap-2">
                            <button wire:click="startPayment"
                                @disabled(empty($cart))
                                class="flex-1 py-3 rounded-[1.75rem] bg-blue-600 hover:bg-blue-500 disabled:bg-neutral-200 disabled:dark:bg-neutral-800 disabled:text-neutral-400 text-white font-black text-sm shadow-2xl shadow-blue-500/30 hover:shadow-blue-500/50 transition-all transform active:scale-95 flex items-center justify-center gap-2 group">
                                <flux:icon.credit-card class="w-4 h-4 group-hover:rotate-12 transition-transform" />
                                PAY NOW
                            </button>
                            @if($orderType === 'dine_in')
                                <button wire:click="placeOrderPayLater"
                                    @disabled(empty($cart) || !$tableId)
                                    title="{{ !$tableId ? 'Select a table first' : 'Send order to kitchen, pay later' }}"
                                    class="flex-1 py-3 rounded-[1.75rem] bg-amber-500 hover:bg-amber-400 disabled:bg-neutral-200 disabled:dark:bg-neutral-800 disabled:text-neutral-400 text-white font-black text-sm shadow-2xl shadow-amber-500/30 hover:shadow-amber-500/50 transition-all transform active:scale-95 flex items-center justify-center gap-2 group">
                                    <flux:icon.fire class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                    KITCHEN
                                </button>
                            @endif
                        </div>

                        @if(count($cart) > 0)
                            <button type="button" wire:click="holdOrder" class="w-full py-2 rounded-[1.75rem] bg-white dark:bg-neutral-900 text-neutral-600 dark:text-neutral-200 font-black text-xs uppercase tracking-widest border border-neutral-200 dark:border-neutral-700 hover:border-blue-500/40 hover:text-blue-600 transition-all">
                                Hold Order
                            </button>
                            <button type="button" wire:click="clearCart" class="w-full py-2 rounded-[1.75rem] bg-neutral-900/5 dark:bg-white/5 text-neutral-500 dark:text-neutral-300 font-black text-xs uppercase tracking-widest border border-neutral-200 dark:border-neutral-700 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all">
                                Clear Cart
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </template>
    @endif

    @if($showHeldOrdersModal)
        <div class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xl animate-in fade-in duration-200">
            <div class="bg-white dark:bg-neutral-900 rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden border border-neutral-200 dark:border-neutral-800">
                <div class="p-6 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                            <flux:icon.pause class="w-5 h-5 text-white" />
                        </div>
                        <div class="flex flex-col leading-none">
                            <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">POS</span>
                            <span class="text-lg font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Held Orders</span>
                        </div>
                    </div>
                    <button type="button" wire:click="closeHeldOrders" class="w-10 h-10 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-500 hover:text-neutral-900 dark:text-neutral-300 transition-all border border-neutral-200 dark:border-neutral-700">
                        <flux:icon.x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($this->heldOrders as $h)
                            <div class="p-4 rounded-2xl border border-neutral-100 dark:border-neutral-800 bg-white/60 dark:bg-neutral-900/40 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="font-black text-neutral-800 dark:text-neutral-100 truncate">{{ $h['label'] }}</div>
                                    <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-1">
                                        {{ (int) ($h['items'] ?? 0) }} items • ${{ number_format((float) ($h['total'] ?? 0), 2) }} • {{ $h['created_at'] }}
                                    </div>
                                    @if(filled($h['customer_name'] ?? ''))
                                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-1">Customer: {{ $h['customer_name'] }}</div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" wire:click="recallHeldOrder({{ (int) $h['id'] }})" class="px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-black shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest text-[10px]">
                                        Recall
                                    </button>
                                    <button type="button" wire:click="deleteHeldOrder({{ (int) $h['id'] }})" class="w-10 h-10 rounded-2xl bg-red-50 dark:bg-red-900/10 text-red-600 hover:bg-red-500 hover:text-white transition-all">
                                        <flux:icon.trash class="w-4 h-4 mx-auto" />
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="py-16 text-center text-sm text-neutral-400 font-medium italic">
                                No held orders.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Unpaid Orders Modal --}}
    @if($showUnpaidOrdersModal)
        <div class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xl animate-in fade-in duration-200">
            <div class="bg-white dark:bg-neutral-900 rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden border border-neutral-200 dark:border-neutral-800">
                <div class="p-6 border-b border-neutral-100 dark:border-neutral-800 bg-amber-50/50 dark:bg-amber-950/20 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500 flex items-center justify-center shadow-lg shadow-amber-500/20">
                            <flux:icon.clock class="w-5 h-5 text-white" />
                        </div>
                        <div class="flex flex-col leading-none">
                            <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Payment Pending</span>
                            <span class="text-lg font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Unpaid Orders</span>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('showUnpaidOrdersModal', false)" class="w-10 h-10 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-500 hover:text-neutral-900 dark:text-neutral-300 transition-all border border-neutral-200 dark:border-neutral-700">
                        <flux:icon.x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-6 max-h-[60vh] overflow-y-auto">
                    <div class="space-y-3">
                        @forelse($this->unpaidOrders as $order)
                            <div class="p-4 rounded-2xl border border-amber-200/50 dark:border-amber-800/30 bg-amber-50/30 dark:bg-amber-900/10 flex items-center justify-between gap-4 hover:border-amber-400 transition-all">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-black text-neutral-800 dark:text-neutral-100">Order #{{ $order['id'] }}</span>
                                        @if($order['table_number'])
                                            <span class="px-2 py-0.5 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-bold">
                                                {{ $order['table_number'] }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-xs font-bold">
                                                Takeaway
                                            </span>
                                        @endif
                                        @if($order['kds_status'] === 'ready')
                                            <span class="px-2 py-0.5 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-xs font-bold">
                                                Ready
                                            </span>
                                        @elseif($order['kds_status'] === 'preparing')
                                            <span class="px-2 py-0.5 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 text-xs font-bold">
                                                Preparing
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-1">
                                        {{ $order['items_count'] }} items - {{ $order['created_at'] }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="text-right">
                                        <div class="text-xs text-neutral-400">Amount Due</div>
                                        <div class="text-lg font-black text-amber-600">RM {{ number_format($order['total_amount'], 2) }}</div>
                                    </div>
                                    <button type="button" 
                                        wire:click="selectUnpaidOrder({{ $order['id'] }})" 
                                        class="px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-white font-black shadow-lg shadow-amber-500/20 transition-all uppercase tracking-widest text-[10px]">
                                        Collect
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="py-16 text-center">
                                <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
                                    <flux:icon.check-circle class="w-8 h-8 text-green-500" />
                                </div>
                                <div class="text-sm text-neutral-500 font-medium">All orders have been paid!</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(!$isPaying && !$selectingProduct && !$lastOrder && !$showCartMobile)
        <template x-teleport="body">
            <button type="button" wire:click="$set('showCartMobile', true)" class="lg:hidden fixed bottom-6 right-6 z-[9998] w-14 h-14 rounded-full bg-pink-500 text-white shadow-lg flex items-center justify-center">
                <div class="relative w-full h-full flex items-center justify-center">
                    <flux:icon.shopping-cart class="w-6 h-6" />
                    @if(count($cart) > 0)
                        <span class="absolute -top-2 -right-2 min-w-6 h-6 px-2 rounded-full bg-red-600 text-white text-[10px] font-black flex items-center justify-center ring-2 ring-white dark:ring-neutral-950">
                            {{ count($cart) }}
                        </span>
                    @endif
                </div>
            </button>
        </template>
    @endif

    @if($showDiscountModal)
        <div class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-zinc-200 dark:border-zinc-800">
                <div class="p-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-pink-500 flex items-center justify-center">
                            <flux:icon.tag class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zinc-800 dark:text-zinc-100">Promotions</h3>
                            <p class="text-xs text-zinc-400">Apply discount, voucher or points</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDiscountModal" class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-zinc-600 transition-colors">
                        <flux:icon.x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-4 space-y-4">
                    <div class="flex p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                        <button type="button" wire:click="setDiscountTab('discount')" class="flex-1 py-2 rounded-md text-xs font-semibold transition-all {{ $discountTab === 'discount' ? 'bg-white dark:bg-zinc-700 text-pink-600 shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}">
                            Discount
                        </button>
                        <button type="button" wire:click="setDiscountTab('voucher')" class="flex-1 py-2 rounded-md text-xs font-semibold transition-all {{ $discountTab === 'voucher' ? 'bg-white dark:bg-zinc-700 text-pink-600 shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}">
                            Voucher
                        </button>
                        <button type="button" wire:click="setDiscountTab('points')" class="flex-1 py-2 rounded-md text-xs font-semibold transition-all {{ $discountTab === 'points' ? 'bg-white dark:bg-zinc-700 text-pink-600 shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}">
                            Points
                        </button>
                        <button type="button" wire:click="setDiscountTab('customer')" class="flex-1 py-2 rounded-md text-xs font-semibold transition-all {{ $discountTab === 'customer' ? 'bg-white dark:bg-zinc-700 text-pink-600 shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}">
                            Customer
                        </button>
                    </div>

                    @if($discountTab === 'discount')
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Manual Discount</span>
                                <button type="button" wire:click="clearPromotion" class="text-xs text-zinc-400 hover:text-red-500 transition-colors">
                                    Clear
                                </button>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-2 flex-1 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-3 py-2">
                                    <div class="flex p-0.5 bg-zinc-100 dark:bg-zinc-700 rounded-md">
                                        <button type="button" wire:click="$set('discountInputType', 'percent')" class="px-2.5 py-1 rounded text-xs font-semibold transition-all {{ $discountInputType === 'percent' ? 'bg-white dark:bg-zinc-600 text-pink-600 shadow-sm' : 'text-zinc-400' }}">%</button>
                                        <button type="button" wire:click="$set('discountInputType', 'fixed')" class="px-2.5 py-1 rounded text-xs font-semibold transition-all {{ $discountInputType === 'fixed' ? 'bg-white dark:bg-zinc-600 text-pink-600 shadow-sm' : 'text-zinc-400' }}">RM</button>
                                    </div>
                                    <input type="number" step="0.01" wire:model.live="discountInputValue" class="flex-1 bg-transparent border-none focus:ring-0 text-lg font-bold text-zinc-700 dark:text-zinc-200 text-right tabular-nums p-0" placeholder="0">
                                </div>
                                <button type="button" wire:click="applyManualDiscount"
                                    @disabled(filled($appliedVoucherCode) && !(bool) ($appliedVoucherMeta['can_combine_with_manual_discount'] ?? false))
                                    class="px-4 py-2.5 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold transition-all text-sm disabled:opacity-40">
                                    Apply
                                </button>
                            </div>

                            @if(filled($appliedVoucherCode) && !(bool) ($appliedVoucherMeta['can_combine_with_manual_discount'] ?? false))
                                <p class="text-xs text-zinc-400">Manual discount disabled by voucher rules.</p>
                            @endif
                        </div>
                    @endif

                    @if($discountTab === 'voucher')
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Voucher Code</span>
                                <button type="button" wire:click="clearPromotion" class="text-xs text-zinc-400 hover:text-red-500 transition-colors">
                                    Clear
                                </button>
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="text" wire:model.live="voucherCode" class="flex-1 rounded-lg border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 px-3 py-2.5 font-semibold focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all uppercase text-sm" placeholder="ENTER CODE">
                                <button type="button" wire:click="applyVoucher" class="px-4 py-2.5 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold transition-all text-sm">
                                    Apply
                                </button>
                            </div>

                            @if(filled($appliedVoucherCode))
                                <div class="flex items-center justify-between p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/30">
                                    <span class="text-sm font-medium text-green-600">Applied</span>
                                    <span class="font-bold text-green-700 dark:text-green-400 uppercase">{{ $appliedVoucherCode }}</span>
                                </div>

                                <div class="flex flex-wrap gap-1.5">
                                    @if(!(bool) ($appliedVoucherMeta['can_combine_with_manual_discount'] ?? false))
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-zinc-100 dark:bg-zinc-800 text-xs text-zinc-500">No Manual Discount</span>
                                    @endif
                                    @if(!(bool) ($appliedVoucherMeta['can_combine_with_points'] ?? false))
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-zinc-100 dark:bg-zinc-800 text-xs text-zinc-500">No Points</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($discountTab === 'points')
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Redeem Points</span>
                                <button type="button" wire:click="clearPromotion" class="text-xs text-zinc-400 hover:text-red-500 transition-colors">
                                    Clear
                                </button>
                            </div>

                            @if($this->customer)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                    <div>
                                        <span class="text-xs text-zinc-400 block">Customer</span>
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $this->customer->name }}</span>
                                        <span class="text-xs text-zinc-400 block mt-0.5">Points: {{ (int) $this->customer->points_balance }}</span>
                                    </div>
                                    <button type="button" wire:click="clearCustomer" class="px-2 py-1 rounded-md bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-xs text-zinc-500 hover:border-red-300 hover:text-red-500 transition-all">
                                        Clear
                                    </button>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="number" wire:model.live="pointsToRedeem"
                                        @disabled(filled($appliedVoucherCode) && !(bool) ($appliedVoucherMeta['can_combine_with_points'] ?? false))
                                        class="flex-1 rounded-lg border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 px-3 py-2.5 font-semibold focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all disabled:opacity-50 text-sm" placeholder="Points ({{ (int) $pointsRedeemPoints }} = RM {{ number_format((float) $pointsRedeemAmount, 2) }})">
                                    <button type="button" wire:click="applyPoints"
                                        @disabled(filled($appliedVoucherCode) && !(bool) ($appliedVoucherMeta['can_combine_with_points'] ?? false))
                                        class="px-4 py-2.5 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold transition-all text-sm disabled:opacity-40">
                                        Redeem
                                    </button>
                                </div>

                                @if(filled($appliedVoucherCode) && !(bool) ($appliedVoucherMeta['can_combine_with_points'] ?? false))
                                    <p class="text-xs text-zinc-400">Points redemption disabled by voucher rules.</p>
                                @endif

                                <p class="text-xs text-zinc-400">
                                    @if((int) $pointsMinRedeem > 0)
                                        Min {{ (int) $pointsMinRedeem }} pts |
                                    @endif
                                    {{ (int) $pointsRedeemPoints }} pts = RM {{ number_format((float) $pointsRedeemAmount, 2) }}
                                </p>

                                @if(($appliedPoints ?? 0) > 0)
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/30">
                                        <span class="text-sm font-medium text-green-600">Applied Points</span>
                                        <span class="font-bold text-green-700 dark:text-green-400 tabular-nums">{{ (int) $appliedPoints }}</span>
                                    </div>
                                @endif
                            @else
                                <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-500">
                                    Select or register a customer before redeeming points.
                                </div>
                                <button type="button" wire:click="setDiscountTab('customer')" class="w-full py-2.5 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold transition-all text-sm">
                                    Go to Customer
                                </button>
                            @endif
                        </div>
                    @endif

                    @if($discountTab === 'customer')
                        <div class="space-y-4">
                            @if($this->customer)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/30">
                                    <div>
                                        <span class="text-xs text-green-600 block">Selected Customer</span>
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $this->customer->name }}</span>
                                        <span class="text-xs text-zinc-400 block mt-0.5">Points: {{ (int) $this->customer->points_balance }}</span>
                                    </div>
                                    <button type="button" wire:click="clearCustomer" class="px-2 py-1 rounded-md bg-white dark:bg-zinc-900 border border-green-200 dark:border-green-800/40 text-xs text-green-700 dark:text-green-400 hover:border-red-300 hover:text-red-500 transition-all">
                                        Clear
                                    </button>
                                </div>
                            @endif

                            <div class="space-y-2">
                                <label class="text-xs font-medium text-zinc-500 block">Find Customer</label>
                                <input type="text" wire:model.live.debounce.200ms="customerSearch" class="w-full rounded-lg border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 px-3 py-2.5 font-medium focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all text-sm" placeholder="Search name / email / mobile">
                                <div class="mt-1 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden max-h-40 overflow-y-auto">
                                    @forelse($this->customerSearchResults as $row)
                                        @php $isSelected = (int) $customerId === (int) $row['id']; @endphp
                                        <button type="button" wire:click="selectCustomer({{ (int) $row['id'] }})" class="w-full px-3 py-2 transition-all flex items-center justify-between border-l-2
                                            {{ $isSelected ? 'bg-pink-50 dark:bg-pink-900/20 border-pink-500' : 'bg-white dark:bg-zinc-900 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 border-transparent' }}">
                                            <div class="text-left">
                                                <span class="font-semibold text-zinc-800 dark:text-zinc-100 text-sm">{{ $row['name'] }}</span>
                                                <span class="text-xs text-zinc-400 block">
                                                    {{ $row['email'] ?: ($row['mobile'] ?: '—') }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                @if($isSelected)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-pink-500 text-white text-xs font-medium">
                                                        <flux:icon.check class="w-3 h-3" />
                                                        Selected
                                                    </span>
                                                @endif
                                                <span class="text-xs font-medium text-pink-600 dark:text-pink-400">
                                                    {{ (int) ($row['points_balance'] ?? 0) }} pts
                                                </span>
                                            </div>
                                        </button>
                                    @empty
                                        <div class="px-3 py-3 text-sm text-zinc-400 italic">
                                            No customers found.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 space-y-3">
                                <span class="text-xs font-medium text-zinc-500">Register New Customer</span>

                                    <input type="email" wire:model="newCustomerEmail" class="w-full rounded-lg border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 px-3 py-2.5 font-medium focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all text-sm" placeholder="Email (optional)">
                                    @error('newCustomerEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    <input type="text" wire:model="newCustomerMobile" class="w-full rounded-lg border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 px-3 py-2.5 font-medium focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all text-sm" placeholder="Mobile (optional)">
                                    @error('newCustomerMobile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <button type="button" wire:click="registerCustomer" class="w-full py-2.5 rounded-lg bg-green-500 hover:bg-green-600 text-white font-semibold transition-all text-sm">
                                    Register Customer
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Footer with current discount --}}
                <div class="p-4 border-t border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950/50">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-500">Current Discount</span>
                        <span class="font-bold text-red-500 tabular-nums">- RM {{ number_format($discountAmount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Payment Modal -->
    @if($isPaying)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in zoom-in-95 duration-200 border border-zinc-200 dark:border-zinc-800 flex flex-col max-h-[90vh]">

                <!-- Header -->
                <div class="bg-gradient-to-r {{ $selectedUnpaidOrder ? 'from-amber-500 to-amber-600' : 'from-pink-500 to-pink-600' }} p-5 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                <flux:icon.credit-card class="w-5 h-5 text-white" />
                            </div>
                            <div>
                                @if($selectedUnpaidOrder)
                                    <h3 class="text-lg font-bold">Collect Payment</h3>
                                    <p class="text-amber-100 text-xs">
                                        Order #{{ $selectedUnpaidOrder->id }} - {{ $selectedUnpaidOrder->table_number ?? 'Takeaway' }}
                                    </p>
                                @else
                                    <h3 class="text-lg font-bold">Checkout</h3>
                                    <p class="text-pink-100 text-xs">
                                        {{ $isSplitPayment ? 'Split Payment' : 'Select payment method' }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block text-xs {{ $selectedUnpaidOrder ? 'text-amber-200' : 'text-pink-200' }}">Amount Due</span>
                            <span class="text-2xl font-bold">RM {{ number_format($selectedUnpaidOrder ? $selectedUnpaidOrder->total_amount : $totalAmount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-4 space-y-4 overflow-y-auto flex-1 scrollbar-hide">

                    {{-- Order Summary --}}
                    <div class="rounded-lg border border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50 p-4 space-y-2">
                        @if($selectedUnpaidOrder)
                            {{-- Show items for unpaid order --}}
                            <div class="text-xs text-zinc-400 font-semibold uppercase tracking-wider mb-2">Order Items</div>
                            <div class="max-h-32 overflow-y-auto space-y-1.5">
                                @foreach($selectedUnpaidOrder->items as $item)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-zinc-600 dark:text-zinc-300">{{ $item->quantity }}x {{ $item->product->name }}</span>
                                        <span class="font-semibold">RM {{ number_format($item->subtotal, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-between text-zinc-500 text-sm pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                <span>Subtotal</span>
                                <span class="font-semibold">RM {{ number_format($selectedUnpaidOrder->subtotal_amount, 2) }}</span>
                            </div>
                            @if($selectedUnpaidOrder->discount_amount > 0)
                                <div class="flex justify-between text-zinc-500 text-sm">
                                    <span>Discount</span>
                                    <span class="font-semibold text-red-500">- RM {{ number_format($selectedUnpaidOrder->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            @if($selectedUnpaidOrder->tax_amount > 0)
                                <div class="flex justify-between text-zinc-500 text-sm">
                                    <span>Tax</span>
                                    <span class="font-semibold text-green-600">RM {{ number_format($selectedUnpaidOrder->tax_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center pt-2 mt-1 border-t border-zinc-200 dark:border-zinc-700">
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100">Total</span>
                                <span class="text-lg font-bold text-amber-500">RM {{ number_format($selectedUnpaidOrder->total_amount, 2) }}</span>
                            </div>
                        @else
                            <div class="flex justify-between text-zinc-500 text-sm">
                                <span>Subtotal</span>
                                <span class="font-semibold">RM {{ number_format($subTotalAmount, 2) }}</span>
                            </div>
                            
                            {{-- Discount/Voucher row with edit button --}}
                            <button type="button" wire:click="openDiscountModal" class="w-full flex justify-between text-sm hover:bg-white dark:hover:bg-zinc-800 rounded px-1 py-0.5 -mx-1 transition-colors">
                                <span class="text-zinc-500">Discount / Voucher</span>
                                @if($discountAmount > 0)
                                    <span class="font-semibold text-red-500">- RM {{ number_format($discountAmount, 2) }}</span>
                                @else
                                    <span class="text-pink-500 text-sm">+ Add</span>
                                @endif
                            </button>
                            
                            @if($taxAmount > 0)
                                @if(count($taxBreakdown) > 1)
                                    @foreach($taxBreakdown as $row)
                                        <div class="flex justify-between text-zinc-500 text-sm">
                                            <span>{{ $row['name'] }} ({{ rtrim(rtrim(number_format((float) ($row['rate'] ?? 0), 2), '0'), '.') }}%)</span>
                                            <span class="font-semibold text-green-600">RM {{ number_format((float) ($row['amount'] ?? 0), 2) }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex justify-between text-zinc-500 text-sm">
                                        <span>Tax ({{ $taxLabel }})</span>
                                        <span class="font-semibold text-green-600">RM {{ number_format($taxAmount, 2) }}</span>
                                    </div>
                                @endif
                            @endif
                            <div class="flex justify-between items-center pt-2 mt-1 border-t border-zinc-200 dark:border-zinc-700">
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100">Total</span>
                                <span class="text-lg font-bold text-pink-500">RM {{ number_format($totalAmount, 2) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Split Payment Toggle --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-500">Split Payment</span>
                        <button type="button"
                            wire:click="{{ $isSplitPayment ? 'disableSplitPayment' : 'enableSplitPayment' }}"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none
                                {{ $isSplitPayment ? 'bg-pink-500' : 'bg-zinc-200 dark:bg-zinc-700' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                                {{ $isSplitPayment ? 'translate-x-6' : 'translate-x-1' }}">
                            </span>
                        </button>
                    </div>

                    {{-- ── SINGLE PAYMENT MODE ── --}}
                    @if(!$isSplitPayment)
                        {{-- Method selector --}}
                        <div class="grid grid-cols-2 gap-2">
                            <label class="relative flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all
                                {{ $paymentMethod === 'cash' ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' }}">
                                <input type="radio" wire:model.live="paymentMethod" value="cash" class="sr-only">
                                <div class="w-9 h-9 rounded-lg bg-white dark:bg-zinc-800 flex items-center justify-center border border-zinc-100 dark:border-zinc-700">
                                    <flux:icon.banknotes class="w-5 h-5 {{ $paymentMethod === 'cash' ? 'text-pink-500' : 'text-zinc-400' }}" />
                                </div>
                                <span class="font-semibold text-sm {{ $paymentMethod === 'cash' ? 'text-pink-600' : 'text-zinc-500' }}">Cash</span>
                            </label>
                            <label class="relative flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all
                                {{ $paymentMethod === 'card' ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' }}">
                                <input type="radio" wire:model.live="paymentMethod" value="card" class="sr-only">
                                <div class="w-9 h-9 rounded-lg bg-white dark:bg-zinc-800 flex items-center justify-center border border-zinc-100 dark:border-zinc-700">
                                    <flux:icon.credit-card class="w-5 h-5 {{ $paymentMethod === 'card' ? 'text-pink-500' : 'text-zinc-400' }}" />
                                </div>
                                <span class="font-semibold text-sm {{ $paymentMethod === 'card' ? 'text-pink-600' : 'text-zinc-500' }}">Card</span>
                            </label>
                        </div>

                        @if($paymentMethod === 'cash')
                            <div class="space-y-3 animate-in slide-in-from-top-2 duration-200">
                                {{-- Quick amount buttons --}}
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" wire:click="setExactAmount"
                                        class="px-4 py-2 text-xs font-semibold rounded-lg bg-pink-500 text-white hover:bg-pink-600 transition-all">
                                        Exact
                                    </button>
                                    @foreach([5, 10, 20, 50, 100] as $amount)
                                        <button type="button" wire:click="addQuickAmount({{ $amount }})"
                                            class="px-4 py-2 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-pink-500 hover:text-pink-500 transition-all">
                                            +RM{{ $amount }}
                                        </button>
                                    @endforeach
                                </div>
                                
                                {{-- Amount input and change display --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-lg font-semibold text-zinc-400">RM</span>
                                        <input type="number" step="0.01" min="0" wire:model.live="amountReceived"
                                            class="w-full pl-12 pr-10 py-3 text-2xl font-bold rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all text-zinc-800 dark:text-zinc-100 text-right"
                                            placeholder="0.00"
                                            onfocus="const n=parseFloat(this.value); if(Number.isFinite(n)&&n>0) this.value=n.toFixed(2); else this.value=''"
                                            onblur="const n=parseFloat(this.value); this.value=Number.isFinite(n)&&n>0?n.toFixed(2):'0.00'">
                                        <button type="button" wire:click="clearAmountReceived"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 rounded flex items-center justify-center text-zinc-400 hover:text-red-500 transition-colors"
                                            title="Clear amount">
                                            <flux:icon.x-mark class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/30">
                                        <div>
                                            <span class="text-xs text-green-600 block">Change</span>
                                            <span class="text-xl font-bold text-green-600">RM {{ number_format($changeAmount, 2) }}</span>
                                        </div>
                                        <flux:icon.banknotes class="w-6 h-6 text-green-400" />
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- ── SPLIT PAYMENT MODE ── --}}
                    @if($isSplitPayment)
                        <div class="space-y-3 animate-in slide-in-from-top-2 duration-200">

                            {{-- Remaining balance indicator --}}
                            <div class="flex items-center justify-between p-3 rounded-lg
                                {{ $splitRemaining <= 0.001 ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/30' : 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/30' }}">
                                <span class="text-sm font-medium {{ $splitRemaining <= 0.001 ? 'text-green-600' : 'text-amber-600' }}">
                                    {{ $splitRemaining <= 0.001 ? 'Fully Covered' : 'Remaining' }}
                                </span>
                                <span class="text-lg font-bold {{ $splitRemaining <= 0.001 ? 'text-green-600' : 'text-amber-600' }}">
                                    RM {{ number_format($splitRemaining, 2) }}
                                </span>
                            </div>

                            {{-- Existing splits --}}
                            @if(!empty($paymentSplits))
                                <div class="space-y-1.5">
                                    @foreach($paymentSplits as $i => $split)
                                        <div class="flex items-center gap-2 p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700">
                                            <div class="w-7 h-7 rounded-md bg-white dark:bg-zinc-700 flex items-center justify-center shrink-0">
                                                @if($split['method'] === 'cash')
                                                    <flux:icon.banknotes class="w-4 h-4 text-pink-500" />
                                                @elseif($split['method'] === 'card')
                                                    <flux:icon.credit-card class="w-4 h-4 text-pink-500" />
                                                @else
                                                    <flux:icon.device-phone-mobile class="w-4 h-4 text-pink-500" />
                                                @endif
                                            </div>
                                            <span class="text-xs font-medium text-zinc-500 capitalize w-14 shrink-0">{{ $split['method'] }}</span>
                                            <span class="flex-1 text-right font-semibold text-zinc-800 dark:text-zinc-100 tabular-nums">RM {{ number_format($split['amount'], 2) }}</span>
                                            <button type="button" wire:click="removeSplit({{ $i }})"
                                                class="w-6 h-6 rounded flex items-center justify-center text-zinc-400 hover:text-red-500 transition-colors shrink-0">
                                                <flux:icon.x-mark class="w-4 h-4" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Add split row --}}
                            @if($splitRemaining > 0.001)
                                <div class="flex flex-wrap gap-2 items-end">
                                    {{-- Method selector --}}
                                    <div class="flex gap-1.5">
                                        <button type="button" wire:click="$set('splitMethod', 'cash')"
                                            class="flex flex-col items-center justify-center gap-0.5 w-12 h-12 rounded-lg border transition-all
                                                {{ $splitMethod === 'cash' ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 hover:border-zinc-300' }}">
                                            <flux:icon.banknotes class="w-4 h-4 {{ $splitMethod === 'cash' ? 'text-pink-500' : 'text-zinc-400' }}" />
                                            <span class="text-[9px] font-medium {{ $splitMethod === 'cash' ? 'text-pink-600' : 'text-zinc-400' }}">Cash</span>
                                        </button>
                                        <button type="button" wire:click="$set('splitMethod', 'card')"
                                            class="flex flex-col items-center justify-center gap-0.5 w-12 h-12 rounded-lg border transition-all
                                                {{ $splitMethod === 'card' ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 hover:border-zinc-300' }}">
                                            <flux:icon.credit-card class="w-4 h-4 {{ $splitMethod === 'card' ? 'text-pink-500' : 'text-zinc-400' }}" />
                                            <span class="text-[9px] font-medium {{ $splitMethod === 'card' ? 'text-pink-600' : 'text-zinc-400' }}">Card</span>
                                        </button>
                                        <button type="button" wire:click="$set('splitMethod', 'ewallet')"
                                            class="flex flex-col items-center justify-center gap-0.5 w-12 h-12 rounded-lg border transition-all
                                                {{ $splitMethod === 'ewallet' ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 hover:border-zinc-300' }}">
                                            <flux:icon.device-phone-mobile class="w-4 h-4 {{ $splitMethod === 'ewallet' ? 'text-pink-500' : 'text-zinc-400' }}" />
                                            <span class="text-[9px] font-medium {{ $splitMethod === 'ewallet' ? 'text-pink-600' : 'text-zinc-400' }}">eWallet</span>
                                        </button>
                                    </div>

                                    {{-- Amount input --}}
                                    <div class="flex-1 relative min-w-[120px]">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-zinc-400">RM</span>
                                        <input type="number" step="0.01" min="0.01" wire:model.live="splitAmount"
                                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm font-semibold text-right text-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all"
                                            onfocus="const n=parseFloat(this.value); if(Number.isFinite(n)&&n>0) this.value=n.toFixed(2)"
                                            onblur="const n=parseFloat(this.value); this.value=Number.isFinite(n)?n.toFixed(2):'0.00'">
                                    </div>

                                    {{-- Exact remaining button --}}
                                    <button type="button" wire:click="setSplitExact"
                                        class="px-3 py-2.5 rounded-lg text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-500 hover:bg-pink-50 hover:text-pink-600 border border-zinc-200 dark:border-zinc-700 transition-all">
                                        Full
                                    </button>

                                    {{-- Add button --}}
                                    <button type="button" wire:click="addSplit"
                                        class="px-4 py-2.5 rounded-lg bg-pink-500 hover:bg-pink-600 text-white text-xs font-semibold transition-all flex items-center gap-1.5 shrink-0">
                                        <flux:icon.plus class="w-4 h-4" />
                                        Add
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Notes --}}
                    <div>
                        <input type="text" wire:model="orderNotes" placeholder="Add a note (optional)..."
                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2.5 text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all">
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 border-t border-zinc-100 dark:border-zinc-800 flex gap-3">
                    @if($selectedUnpaidOrder)
                        <button wire:click="cancelCollectPayment" class="px-6 py-2.5 rounded-lg font-medium text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all">
                            Cancel
                        </button>
                        <button wire:click="collectPayment"
                            @php
                                $orderTotal = (float) $selectedUnpaidOrder->total_amount;
                                $checkoutDisabled = $isSplitPayment
                                    ? (empty($paymentSplits) || $splitRemaining > 0.01)
                                    : ($paymentMethod === 'cash' && $amountReceived < $orderTotal);
                            @endphp
                            {{ $checkoutDisabled ? 'disabled' : '' }}
                            class="flex-1 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold disabled:opacity-40 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                            <flux:icon.check-circle class="w-5 h-5" />
                            Collect Payment
                        </button>
                    @else
                        <button wire:click="$set('isPaying', false)" class="px-6 py-2.5 rounded-lg font-medium text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all">
                            Back
                        </button>
                        <button wire:click="checkout"
                            @php
                                $checkoutDisabled = $isSplitPayment
                                    ? (empty($paymentSplits) || $splitRemaining > 0.01)
                                    : (!$isSplitPayment && $paymentMethod === 'cash' && $amountReceived < $totalAmount);
                            @endphp
                            {{ $checkoutDisabled ? 'disabled' : '' }}
                            class="flex-1 py-2.5 rounded-lg bg-green-500 hover:bg-green-600 text-white font-semibold disabled:opacity-40 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                            <flux:icon.check-circle class="w-5 h-5" />
                            Process Payment
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Order Success Modal -->
    @if($lastOrder)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md">
            <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl w-full max-w-sm lg:max-w-4xl overflow-hidden animate-in zoom-in duration-300">
                <div class="grid lg:grid-cols-2">
                    <div class="p-8 text-center space-y-6">
                        {{-- Different icon/color for paid vs unpaid --}}
                        @if($lastOrder->payment_status === 'paid')
                            <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto">
                                <flux:icon.check-circle class="w-12 h-12 text-green-600" />
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-2xl font-black text-neutral-800 dark:text-neutral-100">Order Complete!</h3>
                                <p class="text-neutral-500">Order #{{ $lastOrder->id }} has been placed and paid.</p>
                            </div>
                        @else
                            <div class="w-20 h-20 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mx-auto">
                                <flux:icon.fire class="w-12 h-12 text-amber-600" />
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-2xl font-black text-neutral-800 dark:text-neutral-100">Sent to Kitchen!</h3>
                                <p class="text-neutral-500">Order #{{ $lastOrder->id }} is being prepared. Payment pending.</p>
                            </div>
                        @endif

                        <div class="p-4 rounded-xl bg-neutral-50 dark:bg-neutral-900 text-sm space-y-2">
                            <div class="flex justify-between">
                                <span class="text-neutral-500 font-black uppercase tracking-widest text-[10px]">Type</span>
                                <span class="font-bold uppercase flex items-center gap-1">
                                    @if($lastOrder->order_type === 'dine_in')
                                        <flux:icon.building-storefront class="w-3 h-3 text-blue-500" />
                                        Dine In ({{ $lastOrder->table_number }})
                                    @else
                                        <flux:icon.shopping-bag class="w-3 h-3 text-orange-500" />
                                        Takeaway
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500 font-black uppercase tracking-widest text-[10px]">Status</span>
                                @if($lastOrder->payment_status === 'paid')
                                    <span class="font-bold text-green-600 uppercase">Paid</span>
                                @else
                                    <span class="font-bold text-amber-600 uppercase">Unpaid</span>
                                @endif
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500 font-black uppercase tracking-widest text-[10px]">Total</span>
                                <span class="font-bold text-blue-600">RM {{ number_format($lastOrder->total_amount, 2) }}</span>
                            </div>
                            @if($lastOrder->payment_status === 'paid')
                                @if(!empty($lastOrder->payment_splits))
                                    @foreach($lastOrder->payment_splits as $split)
                                        <div class="flex justify-between">
                                            <span class="text-neutral-500 font-black uppercase tracking-widest text-[10px]">{{ $split['method'] }}</span>
                                            <span class="font-bold">RM {{ number_format($split['amount'], 2) }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex justify-between">
                                        <span class="text-neutral-500 font-black uppercase tracking-widest text-[10px]">Method</span>
                                        <span class="font-bold uppercase">{{ $lastOrder->payment_method }}</span>
                                    </div>
                                    @if($lastOrder->change_amount > 0)
                                        <div class="flex justify-between text-green-600">
                                            <span>Change</span>
                                            <span class="font-bold">RM {{ number_format($lastOrder->change_amount, 2) }}</span>
                                        </div>
                                    @endif
                                @endif
                            @endif
                        </div>

                        @if(!empty($issuedVoucherCodes))
                            <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/10 border border-purple-200/60 dark:border-purple-800/30 text-sm space-y-2">
                                <div class="text-[10px] font-black text-purple-600 uppercase tracking-widest">Issued Voucher</div>
                                @foreach($issuedVoucherCodes as $row)
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="font-black text-purple-800 dark:text-purple-200 uppercase tracking-widest">{{ $row['code'] ?? '' }}</span>
                                        <span class="text-[10px] font-black text-purple-500 uppercase tracking-widest">
                                            @if(!empty($row['expires_at']))
                                                Exp {{ $row['expires_at'] }}
                                            @else
                                                No Expiry
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex flex-col gap-3">
                            @if($lastOrder->payment_status === 'paid')
                                <button type="button" 
                                    onclick="window.open('{{ route('pos.receipt', $lastOrder) }}', '_blank', 'width=400,height=600')"
                                    class="w-full py-4 rounded-xl border-2 border-blue-600 text-blue-600 hover:bg-blue-50 font-bold transition-all flex items-center justify-center gap-2">
                                    <flux:icon.printer class="w-5 h-5" />
                                    PRINT RECEIPT
                                </button>
                            @else
                                <button type="button" 
                                    onclick="window.open('{{ route('pos.bill', $lastOrder) }}', '_blank', 'width=400,height=600')"
                                    class="w-full py-4 rounded-xl border-2 border-amber-600 text-amber-600 hover:bg-amber-50 font-bold transition-all flex items-center justify-center gap-2">
                                    <flux:icon.document-text class="w-5 h-5" />
                                    PRINT BILL
                                </button>
                            @endif
                            
                            <button wire:click="newOrder" class="w-full py-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold shadow-lg transition-all">
                                NEW ORDER
                            </button>
                        </div>
                    </div>

                    <div class="hidden lg:flex flex-col border-l border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">
                                {{ $lastOrder->payment_status === 'paid' ? 'Receipt Preview' : 'Bill Preview' }}
                            </span>
                            <button type="button"
                                onclick="window.open('{{ $lastOrder->payment_status === 'paid' ? route('pos.receipt', $lastOrder) : route('pos.bill', $lastOrder) }}', '_blank', 'width=400,height=600')"
                                class="px-3 py-1.5 rounded-xl bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[10px] font-black text-neutral-600 dark:text-neutral-200 uppercase tracking-widest hover:border-blue-500 hover:text-blue-600 transition-all">
                                Open
                            </button>
                        </div>
                        <iframe
                            src="{{ $lastOrder->payment_status === 'paid' ? route('pos.receipt', $lastOrder) : route('pos.bill', $lastOrder) }}?preview=1"
                            class="w-full flex-1 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Customization Modal -->
    @if($selectingProduct)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-200 border border-zinc-200 dark:border-zinc-800">
                <!-- Modal Header -->
                <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center overflow-hidden shrink-0">
                            @if($selectingProduct->image_url)
                                <img src="{{ $selectingProduct->image_url }}" class="w-full h-full object-cover">
                            @elseif($selectingProduct->tile_color)
                                <div class="w-full h-full" style="background-color: {{ $selectingProduct->tile_color }};"></div>
                            @else
                                <flux:icon.package class="w-7 h-7 text-zinc-300 dark:text-zinc-600" />
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            @php
                                $selectedVariant = $selectingProduct->variants->firstWhere('id', $selectedVariantId);
                                $selectedIds = collect($selectedAddonIds ?? [])->map(fn ($v) => (int) $v)->all();
                                $groupAddons = $selectingProduct->addonGroups->flatMap(fn ($g) => $g->items);
                                $allAddons = $selectingProduct->addons->whereNull('addon_group_id')->concat($groupAddons);
                                $addonsTotal = (float) $allAddons->whereIn('id', $selectedIds)->sum('price');

                                $setTotal = 0.0;
                                if (($selectingProduct->product_type ?? 'ala_carte') === 'set') {
                                    foreach ($selectingProduct->setGroups as $g) {
                                        $raw = $selectedSetItems[$g->id] ?? [];
                                        $ids = is_array($raw) ? $raw : [$raw];
                                        $ids = array_values(array_filter(array_map(fn ($v) => (int) $v, $ids)));
                                        foreach ($ids as $pid) {
                                            $row = $g->items->firstWhere('product_id', $pid);
                                            if ($row) {
                                                $setTotal += (float) ($row->extra_price ?? 0);
                                            }
                                        }
                                    }
                                }

                                $displayPrice = (float) ($selectedVariant?->price ?? $selectingProduct->price) + $addonsTotal + round(max(0, $setTotal), 2);
                            @endphp
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-xl font-bold text-zinc-800 dark:text-zinc-100 truncate">{{ $selectingProduct->name }}</h3>
                                <span class="text-xl font-bold text-pink-500 shrink-0">RM {{ number_format($displayPrice, 2) }}</span>
                            </div>
                            @if($selectingProduct->description)
                                <p class="text-zinc-400 text-sm mt-0.5 truncate">{{ $selectingProduct->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-5 space-y-5 scrollbar-hide">
                    <!-- Variants (Sizes) -->
                    @if($selectingProduct->variants->count() > 0)
                        <div class="space-y-3">
                            <h4 class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Select Variation</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" wire:click="$set('selectedVariantId', null)"
                                    class="relative flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all
                                    {{ is_null($selectedVariantId) ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600' }}">
                                    <div class="flex flex-col text-left">
                                        <span class="font-semibold text-sm {{ is_null($selectedVariantId) ? 'text-pink-600' : 'text-zinc-800 dark:text-zinc-100' }}">Regular</span>
                                        <span class="text-xs text-zinc-400 mt-0.5">Base price</span>
                                    </div>
                                    <span class="text-sm font-bold {{ is_null($selectedVariantId) ? 'text-pink-600' : 'text-zinc-500' }}">RM {{ number_format($selectingProduct->price, 2) }}</span>
                                    @if(is_null($selectedVariantId))
                                        <div class="absolute top-2 right-2 w-2 h-2 rounded-full bg-pink-500"></div>
                                    @endif
                                </button>
                                @foreach($selectingProduct->variants as $variant)
                                    <label class="relative flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all
                                        {{ $selectedVariantId == $variant->id ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600' }}">
                                        <input type="radio" wire:model.live="selectedVariantId" value="{{ $variant->id }}" class="sr-only">
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-sm {{ $selectedVariantId == $variant->id ? 'text-pink-600' : 'text-zinc-800 dark:text-zinc-100' }}">
                                                {{ $variant->name }}
                                            </span>
                                            <span class="text-xs text-zinc-400 mt-0.5">+RM {{ number_format($variant->price - $selectingProduct->price, 2) }}</span>
                                        </div>
                                        <span class="text-sm font-bold {{ $selectedVariantId == $variant->id ? 'text-pink-600' : 'text-zinc-500' }}">RM {{ number_format($variant->price, 2) }}</span>
                                        @if($selectedVariantId == $variant->id)
                                            <div class="absolute top-2 right-2 w-2 h-2 rounded-full bg-pink-500"></div>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Set Builder -->
                    @if(($selectingProduct->product_type ?? 'ala_carte') === 'set' && ($selectingProduct->setGroups?->count() ?? 0) > 0)
                        <div class="space-y-4">
                            @foreach($selectingProduct->setGroups as $group)
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">{{ $group->name }}</h4>
                                        <span class="text-xs text-zinc-400">
                                            Select {{ (int) $group->min_select }}{{ (int) $group->max_select > (int) $group->min_select ? '-' . (int) $group->max_select : '' }}
                                        </span>
                                    </div>

                                    <div class="space-y-1.5">
                                        @foreach($group->items as $row)
                                            @php
                                                $choice = $row->product;
                                                $extra = (float) ($row->extra_price ?? 0);
                                                $rawSelected = $selectedSetItems[$group->id] ?? [];
                                                $selectedList = collect(is_array($rawSelected) ? $rawSelected : [$rawSelected])->map(fn ($v) => (int) $v)->all();
                                                $isSelected = in_array((int) $choice->id, $selectedList, true);
                                            @endphp
                                            <label class="flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all
                                                {{ $isSelected ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' }}">
                                                <div class="flex items-center gap-3">
                                                    @if((int) $group->max_select === 1)
                                                        <input type="radio" wire:model.live="selectedSetItems.{{ $group->id }}" value="{{ $choice->id }}"
                                                            class="w-4 h-4 border-zinc-300 text-pink-500 focus:ring-pink-500/20">
                                                    @else
                                                        <input type="checkbox" wire:model.live="selectedSetItems.{{ $group->id }}" value="{{ $choice->id }}"
                                                            class="w-4 h-4 rounded border-zinc-300 text-pink-500 focus:ring-pink-500/20">
                                                    @endif
                                                    <span class="font-medium text-sm text-zinc-700 dark:text-zinc-200">{{ $choice->name }}</span>
                                                </div>
                                                @if($extra > 0)
                                                    <span class="text-sm font-semibold text-pink-500">+RM {{ number_format($extra, 2) }}</span>
                                                @else
                                                    <span class="text-sm text-zinc-400">Included</span>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Addon Groups -->
                    @foreach($selectingProduct->addonGroups as $group)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">{{ $group->name }}</h4>
                                <span class="text-xs text-zinc-400">
                                    Select {{ $group->min_select }}{{ $group->max_select > $group->min_select ? '-' . $group->max_select : '' }}
                                </span>
                            </div>
                            <div class="space-y-1.5">
                                @foreach($group->items as $addon)
                                    <label class="flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all
                                        {{ in_array($addon->id, $selectedAddonIds) ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' }}">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" wire:model.live="selectedAddonIds" value="{{ $addon->id }}" 
                                                class="w-4 h-4 rounded border-zinc-300 text-pink-500 focus:ring-pink-500/20">
                                            <span class="font-medium text-sm text-zinc-700 dark:text-zinc-200">{{ $addon->name }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-pink-500">+RM {{ number_format($addon->price, 2) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <!-- Standalone Addons -->
                    @if($selectingProduct->addons->whereNull('addon_group_id')->count() > 0)
                        <div class="space-y-2">
                            <h4 class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Extras</h4>
                            <div class="space-y-1.5">
                                @foreach($selectingProduct->addons->whereNull('addon_group_id') as $addon)
                                    <label class="flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all
                                        {{ in_array($addon->id, $selectedAddonIds) ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' }}">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" wire:model.live="selectedAddonIds" value="{{ $addon->id }}" 
                                                class="w-4 h-4 rounded border-zinc-300 text-pink-500 focus:ring-pink-500/20">
                                            <span class="font-medium text-sm text-zinc-700 dark:text-zinc-200">{{ $addon->name }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-pink-500">+RM {{ number_format($addon->price, 2) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Quantity and Notes -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="space-y-2">
                            <h4 class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Quantity</h4>
                            <div class="flex items-center bg-zinc-100 dark:bg-zinc-800 rounded-lg w-fit">
                                <button type="button" wire:click="$set('quantity', {{ max(1, $quantity - 1) }})" 
                                    class="w-10 h-10 flex items-center justify-center text-zinc-500 hover:text-red-500 transition-colors">
                                    <flux:icon.minus class="w-4 h-4" />
                                </button>
                                <span class="text-xl font-bold w-10 text-center text-zinc-800 dark:text-zinc-100">{{ $quantity }}</span>
                                <button type="button" wire:click="$set('quantity', {{ $quantity + 1 }})" 
                                    class="w-10 h-10 flex items-center justify-center text-pink-500 hover:text-pink-600 transition-colors">
                                    <flux:icon.plus class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Special Instructions</h4>
                            <input type="text" wire:model.live="notes" placeholder="No spicy, extra ice..." 
                                class="w-full rounded-lg border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 px-3 py-2 text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all">
                            @if(count($this->quickNotes) > 0)
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach($this->quickNotes as $label)
                                        <button type="button" wire:click="applyQuickNote(@js($label))"
                                            class="px-2.5 py-1 rounded-md bg-zinc-100 dark:bg-zinc-800 text-xs font-medium text-zinc-500 hover:bg-pink-100 hover:text-pink-600 dark:hover:bg-pink-900/30 transition-all">
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-zinc-100 dark:border-zinc-800 flex gap-3">
                    <button type="button" wire:click="cancelSelection" class="px-6 py-2.5 rounded-lg font-medium text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all">
                        Cancel
                    </button>
                    <button type="button" wire:click="addToCart" class="flex-1 py-2.5 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold transition-all flex items-center justify-center gap-2">
                        <flux:icon.plus-circle class="w-5 h-5" />
                        Add to Order
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
