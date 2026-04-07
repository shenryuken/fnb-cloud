<div x-data="{}" class="flex flex-col lg:flex-row min-h-[calc(100vh-4rem)] lg:h-[calc(100vh-4rem)] gap-4 lg:gap-6 p-4 overflow-y-auto lg:overflow-hidden bg-neutral-50 dark:bg-neutral-950 font-sans" wire:poll.15s>
    <!-- Left Side: Product Selection -->
    <div class="w-full lg:flex-1 flex flex-col gap-4 lg:overflow-hidden">
        <!-- Top Bar: Search and Categories -->
        <div class="bg-white dark:bg-neutral-900 p-4 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-800 space-y-4">
            <div class="relative group">
                <input type="text" wire:model.live.debounce.300ms="search" 
                    placeholder="Search menu items..." 
                    class="w-full pl-12 pr-4 py-3 bg-neutral-50 dark:bg-neutral-800 border-none rounded-xl focus:ring-2 focus:ring-blue-500 transition-all">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                    <flux:icon.magnifying-glass class="w-5 h-5" />
                </div>
            </div>
            
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                <button wire:click="$set('selectedCategoryId', null)" 
                    class="px-5 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition-all
                    {{ is_null($selectedCategoryId) ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 scale-105' : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400 hover:bg-neutral-200' }}">
                    All Menu
                </button>
                @foreach($this->categories as $category)
                    <button wire:click="$set('selectedCategoryId', {{ $category->id }})" 
                        class="px-5 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition-all
                        {{ $selectedCategoryId === $category->id ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 scale-105' : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400 hover:bg-neutral-200' }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Product Grid -->
        <div class="lg:flex-1 lg:overflow-y-auto grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4 pb-4 scrollbar-hide">
            @foreach($this->products as $product)
                <div wire:click="quickAddProduct({{ $product->id }})" 
                    class="group flex flex-col bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden cursor-pointer hover:border-blue-500 hover:shadow-xl hover:shadow-blue-500/5 transition-all duration-300 transform hover:-translate-y-1 relative">
                    <div class="aspect-square bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center overflow-hidden relative">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @elseif($product->tile_color)
                            <div class="w-full h-full flex flex-col items-center justify-center gap-1 text-center px-3" style="background-color: {{ $product->tile_color }};">
                                <span class="text-white text-lg sm:text-xl font-black tracking-tight leading-tight">{{ $product->name }}</span>
                                <span class="text-white/90 text-lg font-black tracking-tighter">${{ number_format($product->price, 2) }}</span>
                            </div>
                        @else
                            <flux:icon.package class="w-12 h-12 text-neutral-300 dark:text-neutral-700" />
                        @endif
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors"></div>

                        <div class="absolute top-3 left-3 flex flex-col gap-2 pointer-events-none">
                            @if(isset($this->autoBadges[$product->id]))
                                <span class="px-2.5 py-1 rounded-xl text-[9px] font-black uppercase tracking-widest shadow-lg
                                    {{ $this->autoBadges[$product->id] === 'Top Sale' ? 'bg-red-600 text-white' : 'bg-orange-600 text-white' }}">
                                    {{ $this->autoBadges[$product->id] }}
                                </span>
                            @endif

                            @if($product->badge_text)
                                <span class="px-2.5 py-1 rounded-xl text-[9px] font-black uppercase tracking-widest shadow-lg bg-blue-600 text-white">
                                    {{ $product->badge_text }}
                                </span>
                            @endif
                        </div>

                        @if(($product->product_type ?? 'ala_carte') === 'set' || ($product->variants?->count() ?? 0) > 1 || ($product->addons?->count() ?? 0) > 0 || ($product->addonGroups?->count() ?? 0) > 0)
                            <button type="button" wire:click.stop="selectProduct({{ $product->id }})"
                                class="absolute bottom-3 left-3 right-3 py-2 rounded-xl bg-neutral-900/80 text-white text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-blue-600 transition-all">
                                Customize
                            </button>
                        @endif
                    </div>
                    @if(!$product->tile_color || $product->image_url)
                        <div class="p-4">
                            <h3 class="font-bold text-neutral-800 dark:text-neutral-100 truncate group-hover:text-blue-600 transition-colors">
                                {{ $product->name }}
                            </h3>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs font-medium text-neutral-400 uppercase tracking-wider">{{ $product->category->name }}</span>
                                <span class="text-sm font-black text-blue-600 dark:text-blue-400">${{ number_format($product->price, 2) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Right Side: Current Cart -->
    <div class="hidden lg:flex w-[420px] flex-col bg-white dark:bg-neutral-900 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-2xl overflow-hidden relative">
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
        
        <div class="p-4 sm:p-5 lg:p-6 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/30 dark:bg-neutral-900/50 {{ $isKitchenBusy ? 'mt-12' : '' }} transition-all duration-500">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="$set('showCartMobile', false)" class="lg:hidden w-10 h-10 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-500 hover:text-neutral-900 dark:text-neutral-300 transition-all border border-neutral-200 dark:border-neutral-700">
                        <flux:icon.x-mark class="w-5 h-5" />
                    </button>
                    <h3 class="text-xl sm:text-2xl font-black text-neutral-800 dark:text-neutral-100 flex items-center gap-3 tracking-tight">
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
                    @if(count($cart) > 0)
                        <button type="button" wire:click="clearCart" class="mt-2 flex items-center gap-2 px-2.5 py-1 rounded-xl bg-red-50 dark:bg-red-900/10 text-red-600 dark:text-red-400 text-[9px] font-black uppercase tracking-widest border border-red-200/60 dark:border-red-500/20 hover:bg-red-500 hover:text-white transition-all">
                            <flux:icon.trash class="w-3.5 h-3.5" />
                            Clear
                        </button>
                    @endif
                </div>
            </div>

            <!-- Order Type Selection -->
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
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                            <flux:icon.hashtag class="w-4 h-4" />
                        </div>
                        <input type="text" wire:model.live="tableNumber" placeholder="Table Number" 
                            class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                </div>
            @endif
        </div>

        <div class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-2 sm:space-y-3 scrollbar-hide">
            @forelse($cart as $index => $item)
                @php
                    $eachPrice = (float) ($item['unit_price'] ?? 0) + (float) ($item['addons_total'] ?? 0) + (float) ($item['set_total'] ?? 0);
                @endphp
                <div class="group p-2.5 sm:p-3 bg-white dark:bg-neutral-800/40 rounded-2xl border border-neutral-100 dark:border-neutral-800 hover:border-blue-500/30 hover:shadow-xl hover:shadow-blue-500/5 transition-all duration-300 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-blue-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                    <div class="flex items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h4 class="font-black text-neutral-800 dark:text-neutral-100 leading-tight tracking-tight text-[13px] sm:text-sm truncate">
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
                                    @if(!empty($item['set_summary']))
                                        <div class="mt-1 text-[10px] font-bold text-neutral-400 truncate">
                                            Set: {{ $item['set_summary'] }}
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
                                    <div class="mt-1 font-black text-[15px] sm:text-base text-neutral-900 dark:text-neutral-100 tracking-tighter tabular-nums">${{ number_format($item['subtotal'], 2) }}</div>
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

        <div class="p-3 sm:p-4 bg-neutral-50 dark:bg-neutral-800/50 border-t border-neutral-100 dark:border-neutral-800 space-y-3">
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

                @if(filled($appliedVoucherCode))
                    <div class="flex justify-between text-neutral-500 font-black tracking-tight text-[10px]">
                        <span>Voucher</span>
                        <span class="font-black text-neutral-400 uppercase tracking-widest">{{ $appliedVoucherCode }}</span>
                    </div>
                @elseif(($appliedPoints ?? 0) > 0)
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

            <button wire:click="startPayment" 
                @disabled(empty($cart))
                class="w-full py-3 rounded-[1.75rem] bg-blue-600 hover:bg-blue-500 disabled:bg-neutral-200 disabled:dark:bg-neutral-800 disabled:text-neutral-400 text-white font-black text-base shadow-2xl shadow-blue-500/30 hover:shadow-blue-500/50 transition-all transform active:scale-95 flex items-center justify-center gap-3 group">
                <flux:icon.credit-card class="w-5 h-5 group-hover:rotate-12 transition-transform" />
                CHECKOUT
            </button>

            @if(count($cart) > 0)
                <button type="button" wire:click="clearCart" class="w-full py-2 rounded-[1.75rem] bg-neutral-900/5 dark:bg-white/5 text-neutral-500 dark:text-neutral-300 font-black text-xs uppercase tracking-widest border border-neutral-200 dark:border-neutral-700 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all">
                    Clear Cart
                </button>
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
                                    <div class="relative group">
                                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                            <flux:icon.hashtag class="w-4 h-4" />
                                        </div>
                                        <input type="text" wire:model.live="tableNumber" placeholder="Table Number"
                                            class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all">
                                    </div>
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

                            @if(filled($appliedVoucherCode))
                                <div class="flex justify-between text-neutral-500 font-black tracking-tight text-[10px]">
                                    <span>Voucher</span>
                                    <span class="font-black text-neutral-400 uppercase tracking-widest">{{ $appliedVoucherCode }}</span>
                                </div>
                            @elseif(($appliedPoints ?? 0) > 0)
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

                        <button wire:click="startPayment"
                            @disabled(empty($cart))
                            class="w-full py-3 rounded-[1.75rem] bg-blue-600 hover:bg-blue-500 disabled:bg-neutral-200 disabled:dark:bg-neutral-800 disabled:text-neutral-400 text-white font-black text-base shadow-2xl shadow-blue-500/30 hover:shadow-blue-500/50 transition-all transform active:scale-95 flex items-center justify-center gap-3 group">
                            <flux:icon.credit-card class="w-5 h-5 group-hover:rotate-12 transition-transform" />
                            CHECKOUT
                        </button>

                        @if(count($cart) > 0)
                            <button type="button" wire:click="clearCart" class="w-full py-2 rounded-[1.75rem] bg-neutral-900/5 dark:bg-white/5 text-neutral-500 dark:text-neutral-300 font-black text-xs uppercase tracking-widest border border-neutral-200 dark:border-neutral-700 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all">
                                Clear Cart
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </template>
    @endif

    @if(!$isPaying && !$selectingProduct && !$lastOrder && !$showCartMobile)
        <template x-teleport="body">
            <button type="button" wire:click="$set('showCartMobile', true)" class="lg:hidden fixed bottom-6 right-6 z-[9998] w-14 h-14 rounded-full bg-blue-600 text-white shadow-2xl shadow-blue-500/30 flex items-center justify-center">
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
        <div class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xl animate-in fade-in duration-200">
            <div class="bg-white dark:bg-neutral-900 rounded-[2.5rem] shadow-2xl w-full max-w-xl overflow-hidden border border-neutral-200 dark:border-neutral-800">
                <div class="p-6 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                            <flux:icon.tag class="w-5 h-5 text-white" />
                        </div>
                        <div class="flex flex-col leading-none">
                            <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Promotions</span>
                            <span class="text-lg font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Discount / Voucher / Points</span>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDiscountModal" class="w-10 h-10 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-500 hover:text-neutral-900 dark:text-neutral-300 transition-all border border-neutral-200 dark:border-neutral-700">
                        <flux:icon.x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" wire:click="setDiscountTab('discount')" class="py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all {{ $discountTab === 'discount' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white/60 dark:bg-neutral-900/40 text-neutral-500 border-neutral-200 dark:border-neutral-800 hover:border-blue-500/40' }}">
                            Discount
                        </button>
                        <button type="button" wire:click="setDiscountTab('voucher')" class="py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all {{ $discountTab === 'voucher' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white/60 dark:bg-neutral-900/40 text-neutral-500 border-neutral-200 dark:border-neutral-800 hover:border-blue-500/40' }}">
                            Voucher
                        </button>
                        <button type="button" wire:click="setDiscountTab('points')" class="py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all {{ $discountTab === 'points' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white/60 dark:bg-neutral-900/40 text-neutral-500 border-neutral-200 dark:border-neutral-800 hover:border-blue-500/40' }}">
                            Points
                        </button>
                        <button type="button" wire:click="setDiscountTab('customer')" class="py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all {{ $discountTab === 'customer' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white/60 dark:bg-neutral-900/40 text-neutral-500 border-neutral-200 dark:border-neutral-800 hover:border-blue-500/40' }}">
                            Customer
                        </button>
                    </div>

                    @if($discountTab === 'discount')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-neutral-500 uppercase tracking-widest">Manual Discount</span>
                                <button type="button" wire:click="clearPromotion" class="text-[10px] font-black uppercase tracking-widest text-neutral-400 hover:text-red-500 transition-colors">
                                    Clear
                                </button>
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 rounded-xl bg-neutral-50 dark:bg-neutral-800/60 border border-neutral-200 dark:border-neutral-800 px-2 py-1">
                                    <div class="flex p-0.5 bg-neutral-100 dark:bg-neutral-800 rounded-lg">
                                        <button type="button" wire:click="$set('discountType', 'percent')" class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest transition-all {{ $discountType === 'percent' ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm' : 'text-neutral-400' }}">%</button>
                                        <button type="button" wire:click="$set('discountType', 'fixed')" class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest transition-all {{ $discountType === 'fixed' ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm' : 'text-neutral-400' }}">$</button>
                                    </div>
                                    <input type="number" step="0.01" wire:model.live="discountValue" class="w-24 bg-transparent border-none focus:ring-0 text-lg font-black text-neutral-700 dark:text-neutral-200 text-right tabular-nums p-0" placeholder="0">
                                </div>
                                <button type="button" wire:click="applyManualDiscount" class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-black shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest text-[10px]">
                                    Apply
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($discountTab === 'voucher')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-neutral-500 uppercase tracking-widest">Voucher Code</span>
                                <button type="button" wire:click="clearPromotion" class="text-[10px] font-black uppercase tracking-widest text-neutral-400 hover:text-red-500 transition-colors">
                                    Clear
                                </button>
                            </div>

                            <div class="flex items-center gap-3">
                                <input type="text" wire:model.live="voucherCode" class="flex-1 rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-black focus:ring-4 focus:ring-blue-500/10 transition-all uppercase" placeholder="ENTER CODE">
                                <button type="button" wire:click="applyVoucher" class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-black shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest text-[10px]">
                                    Apply
                                </button>
                            </div>

                            @if(filled($appliedVoucherCode))
                                <div class="flex items-center justify-between p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200/60 dark:border-emerald-800/30">
                                    <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Applied</span>
                                    <span class="font-black text-emerald-700 dark:text-emerald-300 uppercase tracking-widest">{{ $appliedVoucherCode }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($discountTab === 'points')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-neutral-500 uppercase tracking-widest">Redeem Points</span>
                                <button type="button" wire:click="clearPromotion" class="text-[10px] font-black uppercase tracking-widest text-neutral-400 hover:text-red-500 transition-colors">
                                    Clear
                                </button>
                            </div>

                            @if($this->customer)
                                <div class="flex items-center justify-between p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/60 border border-neutral-200 dark:border-neutral-800">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Customer</span>
                                        <span class="font-black text-neutral-800 dark:text-neutral-100">{{ $this->customer->name }}</span>
                                        <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-1">Points: {{ (int) $this->customer->points_balance }}</span>
                                    </div>
                                    <button type="button" wire:click="clearCustomer" class="px-3 py-2 rounded-xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 text-[10px] font-black text-neutral-500 uppercase tracking-widest hover:border-red-500/50 hover:text-red-500 transition-all">
                                        Clear
                                    </button>
                                </div>

                                <div class="flex items-center gap-3">
                                    <input type="number" wire:model.live="pointsToRedeem" class="flex-1 rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-black focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="Points ({{ (int) $pointsRedeemPoints }} = RM {{ number_format((float) $pointsRedeemAmount, 2) }})">
                                    <button type="button" wire:click="applyPoints" class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-black shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest text-[10px]">
                                        Redeem
                                    </button>
                                </div>

                                <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest px-1">
                                    @if((int) $pointsMinRedeem > 0)
                                        Min {{ (int) $pointsMinRedeem }} pts •
                                    @endif
                                    {{ (int) $pointsRedeemPoints }} pts = RM {{ number_format((float) $pointsRedeemAmount, 2) }}
                                </div>

                                @if(($appliedPoints ?? 0) > 0)
                                    <div class="flex items-center justify-between p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200/60 dark:border-emerald-800/30">
                                        <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Applied Points</span>
                                        <span class="font-black text-emerald-700 dark:text-emerald-300 tabular-nums">{{ (int) $appliedPoints }}</span>
                                    </div>
                                @endif
                            @else
                                <div class="p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/60 border border-neutral-200 dark:border-neutral-800 text-sm text-neutral-500 font-medium">
                                    Select or register a customer before redeeming points.
                                </div>
                                <button type="button" wire:click="setDiscountTab('customer')" class="w-full py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-black shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest text-[10px]">
                                    Go to Customer
                                </button>
                            @endif
                        </div>
                    @endif

                    @if($discountTab === 'customer')
                        <div class="space-y-6">
                            @if($this->customer)
                                <div class="flex items-center justify-between p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200/60 dark:border-emerald-800/30">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Selected Customer</span>
                                        <span class="font-black text-neutral-800 dark:text-neutral-100">{{ $this->customer->name }}</span>
                                        <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-1">Points: {{ (int) $this->customer->points_balance }}</span>
                                    </div>
                                    <button type="button" wire:click="clearCustomer" class="px-3 py-2 rounded-xl bg-white dark:bg-neutral-900 border border-emerald-200/80 dark:border-emerald-800/40 text-[10px] font-black text-emerald-700 dark:text-emerald-300 uppercase tracking-widest hover:border-red-500/50 hover:text-red-500 transition-all">
                                        Clear
                                    </button>
                                </div>
                            @endif

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest ml-2">Find Customer</label>
                                <input type="text" wire:model.live.debounce.200ms="customerSearch" class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-bold focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="Search name / email / mobile">
                                <div class="mt-2 rounded-2xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
                                    @forelse($this->customerSearchResults as $row)
                                        @php $isSelected = (int) $customerId === (int) $row['id']; @endphp
                                        <button type="button" wire:click="selectCustomer({{ (int) $row['id'] }})" class="w-full px-4 py-3 transition-all flex items-center justify-between border-l-4
                                            {{ $isSelected ? 'bg-blue-50 dark:bg-blue-900/10 border-blue-600' : 'bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 border-transparent' }}">
                                            <div class="flex flex-col text-left">
                                                <span class="font-black text-neutral-800 dark:text-neutral-100">{{ $row['name'] }}</span>
                                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">
                                                    {{ $row['email'] ?: ($row['mobile'] ?: '—') }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                @if($isSelected)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-600 text-white text-[9px] font-black uppercase tracking-widest">
                                                        <flux:icon.check class="w-3 h-3" />
                                                        Selected
                                                    </span>
                                                @endif
                                                <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest">
                                                    {{ (int) ($row['points_balance'] ?? 0) }} pts
                                                </span>
                                            </div>
                                        </button>
                                    @empty
                                        <div class="px-4 py-4 text-sm text-neutral-400 font-medium italic">
                                            No customers found.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black text-neutral-500 uppercase tracking-widest">Register Customer</span>
                                </div>

                                <div class="grid grid-cols-1 gap-3">
                                    <input type="text" wire:model="newCustomerName" class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-black focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="Name">
                                    @error('newCustomerName') <span class="text-red-500 text-xs font-bold ml-2">{{ $message }}</span> @enderror
                                    <input type="email" wire:model="newCustomerEmail" class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-bold focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="Email (optional)">
                                    @error('newCustomerEmail') <span class="text-red-500 text-xs font-bold ml-2">{{ $message }}</span> @enderror
                                    <input type="text" wire:model="newCustomerMobile" class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-bold focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="Mobile (optional)">
                                    @error('newCustomerMobile') <span class="text-red-500 text-xs font-bold ml-2">{{ $message }}</span> @enderror
                                </div>

                                <button type="button" wire:click="registerCustomer" class="w-full py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black shadow-lg shadow-emerald-500/20 transition-all uppercase tracking-widest text-[10px]">
                                    Register
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-neutral-500 uppercase tracking-widest">Current Discount</span>
                            <span class="text-xs font-black text-red-500 tabular-nums">- ${{ number_format($discountAmount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Payment Modal -->
    @if($isPaying)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xl animate-in fade-in duration-300">
            <div class="bg-white dark:bg-neutral-900 rounded-[3rem] shadow-2xl w-full max-w-2xl overflow-hidden animate-in zoom-in-95 duration-300 border border-neutral-200 dark:border-neutral-800 flex flex-col">

                <!-- Header -->
                <div class="bg-blue-600 dark:bg-blue-700 p-8 text-white flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                            <flux:icon.credit-card class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-black tracking-tight">Checkout</h3>
                            <p class="text-blue-100 text-[10px] font-medium opacity-80 uppercase tracking-widest">
                                {{ $isSplitPayment ? 'Split Payment' : 'Select Payment Method' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="block text-[10px] font-bold opacity-60 uppercase tracking-widest">Amount Due</span>
                        <span class="text-4xl font-black tracking-tighter">${{ number_format($totalAmount, 2) }}</span>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-8 space-y-6 overflow-y-auto max-h-[70vh] scrollbar-hide">

                    {{-- Order Summary --}}
                    <div class="rounded-2xl border border-neutral-100 dark:border-neutral-800 bg-neutral-50/60 dark:bg-neutral-950/40 p-5 space-y-2">
                        <div class="flex justify-between text-neutral-500 font-bold tracking-tight">
                            <span>Subtotal</span>
                            <span class="font-black">${{ number_format($subTotalAmount, 2) }}</span>
                        </div>
                        @if($discountAmount > 0)
                            <div class="flex justify-between text-neutral-500 font-bold tracking-tight">
                                <span>Discount</span>
                                <span class="font-black text-red-500">- ${{ number_format($discountAmount, 2) }}</span>
                            </div>
                        @endif
                        @if($taxAmount > 0)
                            @if(count($taxBreakdown) > 1)
                                @foreach($taxBreakdown as $row)
                                    <div class="flex justify-between text-neutral-500 font-bold tracking-tight">
                                        <span>{{ $row['name'] }} ({{ rtrim(rtrim(number_format((float) ($row['rate'] ?? 0), 2), '0'), '.') }}%)</span>
                                        <span class="font-black text-emerald-600">${{ number_format((float) ($row['amount'] ?? 0), 2) }}</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="flex justify-between text-neutral-500 font-bold tracking-tight">
                                    <span>Tax ({{ $taxLabel }})</span>
                                    <span class="font-black text-emerald-600">${{ number_format($taxAmount, 2) }}</span>
                                </div>
                            @endif
                        @endif
                        <div class="flex justify-between items-center pt-2 border-t border-neutral-200 dark:border-neutral-800">
                            <span class="text-sm font-black text-neutral-900 dark:text-neutral-100 uppercase tracking-widest">Total</span>
                            <span class="text-xl font-black text-blue-600 tracking-tighter">${{ number_format($totalAmount, 2) }}</span>
                        </div>
                    </div>

                    {{-- Split Payment Toggle --}}
                    <div class="flex items-center justify-between px-1">
                        <span class="text-xs font-black text-neutral-500 uppercase tracking-widest">Split Payment</span>
                        <button type="button"
                            wire:click="{{ $isSplitPayment ? 'disableSplitPayment' : 'enableSplitPayment' }}"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none
                                {{ $isSplitPayment ? 'bg-blue-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                                {{ $isSplitPayment ? 'translate-x-6' : 'translate-x-1' }}">
                            </span>
                        </button>
                    </div>

                    {{-- ── SINGLE PAYMENT MODE ── --}}
                    @if(!$isSplitPayment)
                        {{-- Method selector --}}
                        <div class="grid grid-cols-2 gap-4">
                            <label class="group relative flex items-center gap-4 p-5 rounded-[1.5rem] border-2 cursor-pointer transition-all duration-300
                                {{ $paymentMethod === 'cash' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-900/10 shadow-lg shadow-blue-500/5' : 'border-neutral-100 dark:border-neutral-800 hover:border-blue-200' }}">
                                <input type="radio" wire:model.live="paymentMethod" value="cash" class="sr-only">
                                <div class="w-10 h-10 rounded-xl bg-white dark:bg-neutral-800 flex items-center justify-center shadow-sm">
                                    <flux:icon.banknotes class="w-5 h-5 {{ $paymentMethod === 'cash' ? 'text-blue-600' : 'text-neutral-400' }}" />
                                </div>
                                <span class="font-black text-sm {{ $paymentMethod === 'cash' ? 'text-blue-600' : 'text-neutral-500' }}">CASH</span>
                            </label>
                            <label class="group relative flex items-center gap-4 p-5 rounded-[1.5rem] border-2 cursor-pointer transition-all duration-300
                                {{ $paymentMethod === 'card' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-900/10 shadow-lg shadow-blue-500/5' : 'border-neutral-100 dark:border-neutral-800 hover:border-blue-200' }}">
                                <input type="radio" wire:model.live="paymentMethod" value="card" class="sr-only">
                                <div class="w-10 h-10 rounded-xl bg-white dark:bg-neutral-800 flex items-center justify-center shadow-sm">
                                    <flux:icon.credit-card class="w-5 h-5 {{ $paymentMethod === 'card' ? 'text-blue-600' : 'text-neutral-400' }}" />
                                </div>
                                <span class="font-black text-sm {{ $paymentMethod === 'card' ? 'text-blue-600' : 'text-neutral-500' }}">CARD</span>
                            </label>
                        </div>

                        @if($paymentMethod === 'cash')
                            <div class="space-y-6 animate-in slide-in-from-top-4 duration-300">
                                <div class="grid grid-cols-4 gap-2">
                                    <button type="button" wire:click="setExactAmount"
                                        class="py-3 text-[10px] font-black rounded-xl bg-blue-600 text-white hover:bg-blue-500 transition-all uppercase tracking-widest">
                                        Exact
                                    </button>
                                    @foreach([5, 10, 20, 50, 100] as $amount)
                                        <button type="button" wire:click="addQuickAmount({{ $amount }})"
                                            class="py-3 text-[10px] font-black rounded-xl bg-neutral-50 dark:bg-neutral-800 border border-neutral-100 dark:border-neutral-700 hover:border-blue-500 hover:text-blue-600 transition-all">
                                            +${{ $amount }}
                                        </button>
                                    @endforeach
                                </div>
                                <div class="flex items-center gap-6">
                                    <div class="flex-1 relative group">
                                        <div class="absolute left-6 top-1/2 -translate-y-1/2 text-2xl font-black text-neutral-300 group-focus-within:text-blue-500 transition-colors">$</div>
                                        <input type="number" step="0.01" wire:model.live="amountReceived"
                                            class="w-full pl-12 pr-6 py-6 text-4xl font-black rounded-2xl border-none bg-neutral-50 dark:bg-neutral-800 focus:ring-4 focus:ring-blue-500/10 transition-all tracking-tighter text-neutral-800 dark:text-neutral-100 shadow-inner text-right"
                                            onfocus="const n=parseFloat(this.value); if(Number.isFinite(n)) this.value=n.toFixed(2)"
                                            onblur="const n=parseFloat(this.value); this.value=Number.isFinite(n)?n.toFixed(2):'0.00'">
                                    </div>
                                    <div class="flex-1 flex justify-between items-center p-6 rounded-2xl bg-emerald-50 dark:bg-emerald-900/10 border-2 border-emerald-100 dark:border-emerald-800/30">
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Change</span>
                                            <span class="text-3xl font-black text-emerald-600 tracking-tighter">${{ number_format($changeAmount, 2) }}</span>
                                        </div>
                                        <flux:icon.banknotes class="w-8 h-8 text-emerald-500/30" />
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- ── SPLIT PAYMENT MODE ── --}}
                    @if($isSplitPayment)
                        <div class="space-y-4 animate-in slide-in-from-top-4 duration-300">

                            {{-- Remaining balance indicator --}}
                            <div class="flex items-center justify-between p-4 rounded-2xl
                                {{ $splitRemaining <= 0.001 ? 'bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800/30' : 'bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/30' }}">
                                <span class="text-[10px] font-black uppercase tracking-widest
                                    {{ $splitRemaining <= 0.001 ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $splitRemaining <= 0.001 ? 'Fully Covered' : 'Remaining' }}
                                </span>
                                <span class="text-2xl font-black tracking-tighter
                                    {{ $splitRemaining <= 0.001 ? 'text-emerald-600' : 'text-amber-600' }}">
                                    ${{ number_format($splitRemaining, 2) }}
                                </span>
                            </div>

                            {{-- Existing splits --}}
                            @if(!empty($paymentSplits))
                                <div class="space-y-2">
                                    @foreach($paymentSplits as $i => $split)
                                        <div class="flex items-center gap-3 p-3 rounded-xl bg-neutral-50 dark:bg-neutral-800 border border-neutral-100 dark:border-neutral-700">
                                            <div class="w-8 h-8 rounded-lg bg-white dark:bg-neutral-700 flex items-center justify-center shadow-sm shrink-0">
                                                @if($split['method'] === 'cash')
                                                    <flux:icon.banknotes class="w-4 h-4 text-blue-600" />
                                                @elseif($split['method'] === 'card')
                                                    <flux:icon.credit-card class="w-4 h-4 text-blue-600" />
                                                @else
                                                    <flux:icon.device-phone-mobile class="w-4 h-4 text-blue-600" />
                                                @endif
                                            </div>
                                            <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest w-16 shrink-0">{{ $split['method'] }}</span>
                                            <span class="flex-1 text-right font-black text-neutral-800 dark:text-neutral-100 tabular-nums">${{ number_format($split['amount'], 2) }}</span>
                                            <button type="button" wire:click="removeSplit({{ $i }})"
                                                class="w-7 h-7 rounded-lg bg-red-50 dark:bg-red-900/10 flex items-center justify-center text-red-400 hover:bg-red-500 hover:text-white transition-all shrink-0">
                                                <flux:icon.x-mark class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Add split row --}}
                            @if($splitRemaining > 0.001)
                                <div class="flex gap-3 items-end">
                                    {{-- Method selector --}}
                                    <div class="flex gap-2">
                                        @foreach(['cash' => 'banknotes', 'card' => 'credit-card', 'ewallet' => 'device-phone-mobile'] as $method => $icon)
                                            <button type="button" wire:click="$set('splitMethod', '{{ $method }}')"
                                                class="w-12 h-12 rounded-xl border-2 flex items-center justify-center transition-all
                                                    {{ $splitMethod === $method ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/10' : 'border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 hover:border-blue-300' }}">
                                                <flux:icon.{{ $icon }} class="w-5 h-5 {{ $splitMethod === $method ? 'text-blue-600' : 'text-neutral-400' }}" />
                                            </button>
                                        @endforeach
                                    </div>

                                    {{-- Amount input --}}
                                    <div class="flex-1 relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-neutral-300">$</span>
                                        <input type="number" step="0.01" min="0.01" wire:model.live="splitAmount"
                                            class="w-full pl-8 pr-4 py-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-sm font-black text-right text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all"
                                            onfocus="const n=parseFloat(this.value); if(Number.isFinite(n)&&n>0) this.value=n.toFixed(2)"
                                            onblur="const n=parseFloat(this.value); this.value=Number.isFinite(n)?n.toFixed(2):'0.00'">
                                    </div>

                                    {{-- Exact remaining button --}}
                                    <button type="button" wire:click="setSplitExact"
                                        class="px-3 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest bg-neutral-100 dark:bg-neutral-800 text-neutral-500 hover:bg-blue-50 hover:text-blue-600 border border-neutral-200 dark:border-neutral-700 transition-all whitespace-nowrap">
                                        Full
                                    </button>

                                    {{-- Add button --}}
                                    <button type="button" wire:click="addSplit"
                                        class="px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-1.5 shrink-0">
                                        <flux:icon.plus class="w-4 h-4" />
                                        Add
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Notes --}}
                    <div class="relative group">
                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                            <flux:icon.pencil-square class="w-5 h-5" />
                        </div>
                        <input type="text" wire:model="orderNotes" placeholder="Add a quick note..."
                            class="w-full rounded-xl border-none bg-neutral-50 dark:bg-neutral-800 p-4 pl-12 font-medium focus:ring-4 focus:ring-blue-500/10 transition-all text-sm">
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-8 border-t border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex gap-4">
                    <button wire:click="$set('isPaying', false)" class="flex-1 py-4 rounded-2xl font-black text-neutral-400 hover:text-neutral-800 transition-colors uppercase tracking-widest text-[10px]">Back</button>
                    <button wire:click="checkout"
                        @php
                            $checkoutDisabled = $isSplitPayment
                                ? (empty($paymentSplits) || $splitRemaining > 0.01)
                                : (!$isSplitPayment && $paymentMethod === 'cash' && $amountReceived < $totalAmount);
                        @endphp
                        {{ $checkoutDisabled ? 'disabled' : '' }}
                        class="flex-[2] py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-base shadow-xl shadow-emerald-500/20 disabled:opacity-30 transition-all transform active:scale-95 uppercase tracking-widest flex items-center justify-center gap-2 group">
                        <flux:icon.check-circle class="w-5 h-5 group-hover:scale-110 transition-transform" />
                        Process Payment
                    </button>
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
                        <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto">
                            <flux:icon.check-circle class="w-12 h-12 text-green-600" />
                        </div>
                        
                        <div class="space-y-2">
                            <h3 class="text-2xl font-black text-neutral-800 dark:text-neutral-100">Order Successful!</h3>
                            <p class="text-neutral-500">Order #{{ $lastOrder->id }} has been placed and paid.</p>
                        </div>

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
                                <span class="text-neutral-500 font-black uppercase tracking-widest text-[10px]">Total</span>
                                <span class="font-bold text-blue-600">${{ number_format($lastOrder->total_amount, 2) }}</span>
                            </div>
                            @if(!empty($lastOrder->payment_splits))
                                @foreach($lastOrder->payment_splits as $split)
                                    <div class="flex justify-between">
                                        <span class="text-neutral-500 font-black uppercase tracking-widest text-[10px]">{{ $split['method'] }}</span>
                                        <span class="font-bold">${{ number_format($split['amount'], 2) }}</span>
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
                                        <span class="font-bold">${{ number_format($lastOrder->change_amount, 2) }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="flex flex-col gap-3">
                            <button type="button" 
                                onclick="window.open('{{ route('pos.receipt', $lastOrder) }}', '_blank', 'width=400,height=600')"
                                class="w-full py-4 rounded-xl border-2 border-blue-600 text-blue-600 hover:bg-blue-50 font-bold transition-all flex items-center justify-center gap-2">
                                <flux:icon.printer class="w-5 h-5" />
                                PRINT RECEIPT
                            </button>
                            
                            <button wire:click="newOrder" class="w-full py-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold shadow-lg transition-all">
                                NEW ORDER
                            </button>
                        </div>
                    </div>

                    <div class="hidden lg:flex flex-col border-l border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">Receipt Preview</span>
                            <button type="button"
                                onclick="window.open('{{ route('pos.receipt', $lastOrder) }}', '_blank', 'width=400,height=600')"
                                class="px-3 py-1.5 rounded-xl bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[10px] font-black text-neutral-600 dark:text-neutral-200 uppercase tracking-widest hover:border-blue-500 hover:text-blue-600 transition-all">
                                Open
                            </button>
                        </div>
                        <iframe
                            src="{{ route('pos.receipt', $lastOrder) }}?preview=1"
                            class="w-full flex-1 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Customization Modal -->
    @if($selectingProduct)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xl animate-in fade-in duration-300">
            <div class="bg-white dark:bg-neutral-900 rounded-[3rem] shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-300 border border-neutral-200 dark:border-neutral-800">
                <!-- Modal Header -->
                <div class="p-10 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50">
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 rounded-[2rem] bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center overflow-hidden border-2 border-white dark:border-neutral-700 shadow-xl">
                            @if($selectingProduct->image_url)
                                <img src="{{ $selectingProduct->image_url }}" class="w-full h-full object-cover">
                            @else
                                <flux:icon.package class="w-10 h-10 text-neutral-300 dark:text-neutral-600" />
                            @endif
                        </div>
                        <div class="flex-1">
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
                            <div class="flex items-center justify-between">
                                <h3 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">{{ $selectingProduct->name }}</h3>
                                <span class="text-2xl font-black text-blue-600 tracking-tighter">${{ number_format($displayPrice, 2) }}</span>
                            </div>
                            <p class="text-neutral-500 font-medium mt-1">{{ $selectingProduct->description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-10 space-y-10 scrollbar-hide">
                    <!-- Variants (Sizes) -->
                    @if($selectingProduct->variants->count() > 0)
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Select Variation</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" wire:click="$set('selectedVariantId', null)"
                                    class="group relative flex items-center justify-between p-6 rounded-[2rem] border-2 cursor-pointer transition-all duration-300
                                    {{ is_null($selectedVariantId) ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 shadow-xl shadow-blue-500/10' : 'border-neutral-100 dark:border-neutral-800 hover:border-neutral-200 dark:hover:border-neutral-700' }}">
                                    <div class="flex flex-col text-left">
                                        <span class="font-black text-lg {{ is_null($selectedVariantId) ? 'text-blue-600' : 'text-neutral-800 dark:text-neutral-100' }}">Regular</span>
                                        <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-1">Base: ${{ number_format($selectingProduct->price, 2) }}</span>
                                    </div>
                                    <span class="text-xl font-black {{ is_null($selectedVariantId) ? 'text-blue-600' : 'text-neutral-400' }} tracking-tighter">${{ number_format($selectingProduct->price, 2) }}</span>
                                    @if(is_null($selectedVariantId))
                                        <div class="absolute top-4 right-4 w-2 h-2 rounded-full bg-blue-600"></div>
                                    @endif
                                </button>
                                @foreach($selectingProduct->variants as $variant)
                                    <label class="group relative flex items-center justify-between p-6 rounded-[2rem] border-2 cursor-pointer transition-all duration-300
                                        {{ $selectedVariantId == $variant->id ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 shadow-xl shadow-blue-500/10' : 'border-neutral-100 dark:border-neutral-800 hover:border-neutral-200 dark:hover:border-neutral-700' }}">
                                        <input type="radio" wire:model.live="selectedVariantId" value="{{ $variant->id }}" class="sr-only">
                                        <div class="flex flex-col">
                                            <span class="font-black text-lg {{ $selectedVariantId == $variant->id ? 'text-blue-600' : 'text-neutral-800 dark:text-neutral-100' }}">
                                                {{ $variant->name }}
                                            </span>
                                            <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-1">Base: ${{ number_format($variant->price, 2) }}</span>
                                        </div>
                                        <span class="text-xl font-black {{ $selectedVariantId == $variant->id ? 'text-blue-600' : 'text-neutral-400' }} tracking-tighter">${{ number_format($variant->price, 2) }}</span>
                                        @if($selectedVariantId == $variant->id)
                                            <div class="absolute top-4 right-4 w-2 h-2 rounded-full bg-blue-600"></div>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Set Builder -->
                    @if(($selectingProduct->product_type ?? 'ala_carte') === 'set' && ($selectingProduct->setGroups?->count() ?? 0) > 0)
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Set Items</h4>

                            @foreach($selectingProduct->setGroups as $group)
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">{{ $group->name }}</h4>
                                        <div class="px-3 py-1 rounded-full bg-neutral-100 dark:bg-neutral-800 text-[10px] font-black text-neutral-500 uppercase tracking-widest">
                                            Min: {{ (int) $group->min_select }} • Max: {{ (int) $group->max_select }}
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-3">
                                        @foreach($group->items as $row)
                                            @php
                                                $choice = $row->product;
                                                $extra = (float) ($row->extra_price ?? 0);
                                                $rawSelected = $selectedSetItems[$group->id] ?? [];
                                                $selectedList = collect(is_array($rawSelected) ? $rawSelected : [$rawSelected])->map(fn ($v) => (int) $v)->all();
                                                $isSelected = in_array((int) $choice->id, $selectedList, true);
                                            @endphp
                                            <label class="group flex items-center justify-between p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300
                                                {{ $isSelected ? 'border-blue-600/30 bg-blue-50/50 dark:bg-blue-900/10' : 'border-neutral-50 dark:border-neutral-800 hover:border-neutral-100 dark:hover:border-neutral-700' }}">
                                                <div class="flex items-center gap-4">
                                                    @if((int) $group->max_select === 1)
                                                        <input type="radio" wire:model.live="selectedSetItems.{{ $group->id }}" value="{{ $choice->id }}"
                                                            class="w-6 h-6 rounded-full border-2 border-neutral-200 dark:border-neutral-700 text-blue-600 focus:ring-4 focus:ring-blue-500/10 transition-all">
                                                    @else
                                                        <input type="checkbox" wire:model.live="selectedSetItems.{{ $group->id }}" value="{{ $choice->id }}"
                                                            class="w-6 h-6 rounded-lg border-2 border-neutral-200 dark:border-neutral-700 text-blue-600 focus:ring-4 focus:ring-blue-500/10 transition-all">
                                                    @endif
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-neutral-700 dark:text-neutral-200">{{ $choice->name }}</span>
                                                        <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-1">${{ number_format((float) $choice->price, 2) }}</span>
                                                    </div>
                                                </div>
                                                @if($extra > 0)
                                                    <span class="font-black text-blue-600 dark:text-blue-400 tracking-tighter">+${{ number_format($extra, 2) }}</span>
                                                @else
                                                    <span class="font-black text-neutral-300 dark:text-neutral-700 tracking-tighter">$0.00</span>
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
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">{{ $group->name }}</h4>
                                <div class="px-3 py-1 rounded-full bg-neutral-100 dark:bg-neutral-800 text-[10px] font-black text-neutral-500 uppercase tracking-widest">
                                    Min: {{ $group->min_select }} • Max: {{ $group->max_select }}
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-3">
                                @foreach($group->items as $addon)
                                    <label class="group flex items-center justify-between p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300
                                        {{ in_array($addon->id, $selectedAddonIds) ? 'border-blue-600/30 bg-blue-50/50 dark:bg-blue-900/10' : 'border-neutral-50 dark:border-neutral-800 hover:border-neutral-100 dark:hover:border-neutral-700' }}">
                                        <div class="flex items-center gap-4">
                                            <div class="relative flex items-center justify-center">
                                                <input type="checkbox" wire:model.live="selectedAddonIds" value="{{ $addon->id }}" 
                                                    class="w-6 h-6 rounded-lg border-2 border-neutral-200 dark:border-neutral-700 text-blue-600 focus:ring-4 focus:ring-blue-500/10 transition-all">
                                            </div>
                                            <span class="font-bold text-neutral-700 dark:text-neutral-200">{{ $addon->name }}</span>
                                        </div>
                                        <span class="font-black text-blue-600 dark:text-blue-400 tracking-tighter">+${{ number_format($addon->price, 2) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <!-- Standalone Addons -->
                    @if($selectingProduct->addons->whereNull('addon_group_id')->count() > 0)
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Extras</h4>
                            <div class="grid grid-cols-1 gap-3">
                                @foreach($selectingProduct->addons->whereNull('addon_group_id') as $addon)
                                    <label class="group flex items-center justify-between p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300
                                        {{ in_array($addon->id, $selectedAddonIds) ? 'border-blue-600/30 bg-blue-50/50 dark:bg-blue-900/10' : 'border-neutral-50 dark:border-neutral-800 hover:border-neutral-100 dark:hover:border-neutral-700' }}">
                                        <div class="flex items-center gap-4">
                                            <input type="checkbox" wire:model.live="selectedAddonIds" value="{{ $addon->id }}" 
                                                class="w-6 h-6 rounded-lg border-2 border-neutral-200 dark:border-neutral-700 text-blue-600 focus:ring-4 focus:ring-blue-500/10 transition-all">
                                            <span class="font-bold text-neutral-700 dark:text-neutral-200">{{ $addon->name }}</span>
                                        </div>
                                        <span class="font-black text-blue-600 dark:text-blue-400 tracking-tighter">+${{ number_format($addon->price, 2) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Quantity and Notes -->
                    <div class="grid grid-cols-2 gap-8 pt-6">
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Quantity</h4>
                            <div class="flex items-center gap-6 bg-neutral-50 dark:bg-neutral-800 p-2 rounded-[2rem] w-fit border border-neutral-100 dark:border-neutral-700">
                                <button type="button" wire:click="$set('quantity', {{ max(1, $quantity - 1) }})" 
                                    class="w-14 h-14 rounded-[1.5rem] bg-white dark:bg-neutral-900 shadow-sm flex items-center justify-center font-black text-xl hover:bg-red-50 hover:text-red-500 transition-all duration-200">
                                    <flux:icon.minus class="w-5 h-5" />
                                </button>
                                <span class="text-3xl font-black w-12 text-center tracking-tighter">{{ $quantity }}</span>
                                <button type="button" wire:click="$set('quantity', {{ $quantity + 1 }})" 
                                    class="w-14 h-14 rounded-[1.5rem] bg-white dark:bg-neutral-900 shadow-sm flex items-center justify-center font-black text-xl hover:bg-blue-50 hover:text-blue-600 transition-all duration-200">
                                    <flux:icon.plus class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Special Instructions</h4>
                            <div class="relative group">
                                <div class="absolute left-5 top-5 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                    <flux:icon.pencil-square class="w-5 h-5" />
                                </div>
                                <input type="text" wire:model.live="notes" placeholder="No spicy, extra ice..." 
                                    class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            </div>
                            @if(count($this->quickNotes) > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($this->quickNotes as $label)
                                        <button type="button" wire:click="applyQuickNote(@js($label))"
                                            class="px-3 py-1.5 rounded-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-[10px] font-black uppercase tracking-widest text-neutral-500 hover:border-blue-500/40 hover:text-blue-600 transition-all">
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-10 border-t border-neutral-100 dark:border-neutral-800 flex gap-4 bg-neutral-50/50 dark:bg-neutral-950/50">
                    <button type="button" wire:click="cancelSelection" class="flex-1 py-5 rounded-[2rem] font-black text-neutral-500 uppercase tracking-widest text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-all">Cancel</button>
                    <button type="button" wire:click="addToCart" class="flex-[2] py-5 rounded-[2rem] bg-blue-600 hover:bg-blue-500 text-white font-black shadow-2xl shadow-blue-500/20 transition-all transform active:scale-95 uppercase tracking-widest text-xs flex items-center justify-center gap-2 group">
                        <flux:icon.plus-circle class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" />
                        Add to Order
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
