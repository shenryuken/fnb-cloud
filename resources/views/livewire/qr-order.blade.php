<div class="mx-auto flex min-h-screen w-full max-w-lg flex-col bg-neutral-50">

    @if ($this->placedOrderId)
        {{-- ============ ORDER CONFIRMATION ============ --}}
        <div class="flex flex-1 flex-col items-center justify-center px-6 py-16 text-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                <svg class="h-10 w-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </div>
            <h1 class="mt-6 text-2xl font-bold text-neutral-900">Order sent!</h1>
            <p class="mt-2 text-pretty text-sm text-neutral-500">
                Your order <span class="font-semibold text-neutral-700">{{ $this->placedOrderNumber }}</span>
                has been sent to the kitchen for <span class="font-semibold text-neutral-700">{{ $this->table->name }}</span>.
            </p>
            <p class="mt-1 text-sm text-neutral-500">A staff member will bring it to your table shortly.</p>

            <button
                type="button"
                wire:click="startNewOrder"
                class="mt-8 w-full rounded-xl bg-neutral-900 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-neutral-800"
            >
                Order more
            </button>
        </div>
    @else
        {{-- ============ HEADER ============ --}}
        <header class="sticky top-0 z-30 border-b border-neutral-200 bg-white/95 px-4 py-3 backdrop-blur">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="truncate text-lg font-bold text-neutral-900">{{ $this->tenant->name }}</h1>
                    <p class="text-xs font-medium text-neutral-500">
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5" /></svg>
                            {{ $this->table->name }}
                        </span>
                    </p>
                </div>
                <span class="shrink-0 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Self-order</span>
            </div>

            {{-- Search --}}
            <div class="mt-3">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search the menu..."
                        class="w-full rounded-xl border border-neutral-200 bg-neutral-50 py-2.5 pl-9 pr-3 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100"
                    />
                </div>
            </div>

            {{-- Category chips --}}
            <div class="mt-3 -mx-4 flex gap-2 overflow-x-auto px-4 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <button
                    type="button"
                    wire:click="$set('selectedCategoryId', null)"
                    @class([
                        'shrink-0 rounded-full px-4 py-1.5 text-sm font-semibold transition',
                        'bg-neutral-900 text-white' => $selectedCategoryId === null,
                        'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' => $selectedCategoryId !== null,
                    ])
                >
                    All
                </button>
                @foreach ($this->categories as $category)
                    <button
                        type="button"
                        wire:click="$set('selectedCategoryId', {{ $category->id }})"
                        wire:key="cat-{{ $category->id }}"
                        @class([
                            'shrink-0 rounded-full px-4 py-1.5 text-sm font-semibold transition',
                            'bg-neutral-900 text-white' => $selectedCategoryId === $category->id,
                            'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' => $selectedCategoryId !== $category->id,
                        ])
                    >
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </header>

        {{-- ============ MENU LIST ============ --}}
        <main class="flex-1 px-4 py-4 {{ count($cart) > 0 ? 'pb-28' : 'pb-8' }}">
            @forelse ($this->products as $product)
                <button
                    type="button"
                    wire:click="openProduct({{ $product->id }})"
                    wire:key="prod-{{ $product->id }}"
                    class="mb-3 flex w-full items-center gap-3 rounded-2xl border border-neutral-200 bg-white p-3 text-left transition active:scale-[0.99]"
                >
                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-neutral-100">
                        <img
                            src="{{ $product->image_url ?: '/placeholder.svg?height=80&width=80&query=food' }}"
                            alt="{{ $product->name }}"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate font-semibold text-neutral-900">{{ $product->name }}</h3>
                        @if ($product->description)
                            <p class="mt-0.5 line-clamp-2 text-xs text-neutral-500">{{ $product->description }}</p>
                        @endif
                        <p class="mt-1.5 text-sm font-bold text-amber-600">{{ $this->money((float) $product->price) }}</p>
                    </div>
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-neutral-900 text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </div>
                </button>
            @empty
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <p class="text-sm font-medium text-neutral-500">No items found.</p>
                    @if (trim($search) !== '')
                        <button type="button" wire:click="$set('search', '')" class="mt-2 text-sm font-semibold text-amber-600">Clear search</button>
                    @endif
                </div>
            @endforelse
        </main>

        {{-- ============ STICKY CART BAR ============ --}}
        @if (count($cart) > 0)
            <div class="fixed inset-x-0 bottom-0 z-30 mx-auto w-full max-w-lg px-4 pb-4">
                <button
                    type="button"
                    wire:click="$set('showCart', true)"
                    class="flex w-full items-center justify-between rounded-2xl bg-neutral-900 px-5 py-4 text-white shadow-lg transition active:scale-[0.99]"
                >
                    <span class="flex items-center gap-2">
                        <span class="flex h-6 min-w-6 items-center justify-center rounded-full bg-amber-500 px-1.5 text-xs font-bold">{{ $this->cartCount }}</span>
                        <span class="text-sm font-semibold">View cart</span>
                    </span>
                    <span class="text-sm font-bold tabular-nums">{{ $this->money($this->cartSubtotal) }}</span>
                </button>
            </div>
        @endif

        {{-- ============ PRODUCT DETAIL MODAL ============ --}}
        @if ($this->selectingProduct)
            <div class="fixed inset-0 z-50 flex items-end justify-center" wire:key="product-modal">
                <div class="absolute inset-0 bg-neutral-900/40" wire:click="closeProduct"></div>
                <div class="relative z-10 flex max-h-[90vh] w-full max-w-lg flex-col rounded-t-3xl bg-white">
                    <div class="overflow-y-auto px-5 pt-5">
                        <div class="mb-4 h-44 w-full overflow-hidden rounded-2xl bg-neutral-100">
                            <img src="{{ $this->selectingProduct->image_url ?: '/placeholder.svg?height=176&width=480&query=food' }}" alt="{{ $this->selectingProduct->name }}" class="h-full w-full object-cover" />
                        </div>

                        <h2 class="text-xl font-bold text-neutral-900">{{ $this->selectingProduct->name }}</h2>
                        @if ($this->selectingProduct->description)
                            <p class="mt-1 text-sm text-neutral-500">{{ $this->selectingProduct->description }}</p>
                        @endif

                        {{-- Variants --}}
                        @if ($this->selectingProduct->variants->count() > 0)
                            <div class="mt-5">
                                <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-neutral-400">Choose an option</h3>
                                <div class="space-y-2">
                                    @foreach ($this->selectingProduct->variants as $variant)
                                        <label wire:key="var-{{ $variant->id }}" @class([
                                            'flex cursor-pointer items-center justify-between rounded-xl border p-3 transition',
                                            'border-amber-400 bg-amber-50' => $selectedVariantId === $variant->id,
                                            'border-neutral-200' => $selectedVariantId !== $variant->id,
                                        ])>
                                            <span class="flex items-center gap-2.5">
                                                <input type="radio" wire:model.live="selectedVariantId" value="{{ $variant->id }}" class="h-4 w-4 text-amber-500 focus:ring-amber-400" />
                                                <span class="text-sm font-medium text-neutral-800">{{ $variant->name }}</span>
                                            </span>
                                            <span class="text-sm font-semibold text-neutral-600">{{ $this->money((float) $variant->price) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Add-ons --}}
                        @if ($this->selectingProduct->addons->count() > 0)
                            <div class="mt-5">
                                <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-neutral-400">Add-ons</h3>
                                <div class="space-y-2">
                                    @foreach ($this->selectingProduct->addons as $addon)
                                        <label wire:key="addon-{{ $addon->id }}" @class([
                                            'flex cursor-pointer items-center justify-between rounded-xl border p-3 transition',
                                            'border-amber-400 bg-amber-50' => in_array($addon->id, $selectedAddonIds),
                                            'border-neutral-200' => !in_array($addon->id, $selectedAddonIds),
                                        ])>
                                            <span class="flex items-center gap-2.5">
                                                <input type="checkbox" wire:model.live="selectedAddonIds" value="{{ $addon->id }}" class="h-4 w-4 rounded text-amber-500 focus:ring-amber-400" />
                                                <span class="text-sm font-medium text-neutral-800">{{ $addon->name }}</span>
                                            </span>
                                            <span class="text-sm font-semibold text-neutral-600">+ {{ $this->money((float) $addon->price) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Notes --}}
                        <div class="mt-5">
                            <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-neutral-400">Special requests</h3>
                            <textarea
                                wire:model="itemNotes"
                                rows="2"
                                placeholder="e.g. no onions, extra spicy"
                                class="w-full rounded-xl border border-neutral-200 bg-neutral-50 p-3 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100"
                            ></textarea>
                        </div>
                    </div>

                    {{-- Footer: qty + add --}}
                    <div class="flex items-center gap-3 border-t border-neutral-100 p-4">
                        <div class="flex items-center gap-3 rounded-xl border border-neutral-200 px-2 py-1.5">
                            <button type="button" wire:click="$set('quantity', {{ max(1, $quantity - 1) }})" class="flex h-7 w-7 items-center justify-center rounded-lg text-neutral-600 hover:bg-neutral-100">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                            </button>
                            <span class="w-5 text-center text-sm font-bold tabular-nums">{{ $quantity }}</span>
                            <button type="button" wire:click="$set('quantity', {{ $quantity + 1 }})" class="flex h-7 w-7 items-center justify-center rounded-lg text-neutral-600 hover:bg-neutral-100">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            </button>
                        </div>
                        <button type="button" wire:click="addToCart" class="flex-1 rounded-xl bg-neutral-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-neutral-800">
                            Add to cart
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============ CART SHEET ============ --}}
        @if ($showCart)
            <div class="fixed inset-0 z-50 flex items-end justify-center" wire:key="cart-sheet">
                <div class="absolute inset-0 bg-neutral-900/40" wire:click="$set('showCart', false)"></div>
                <div class="relative z-10 flex max-h-[90vh] w-full max-w-lg flex-col rounded-t-3xl bg-white">
                    <div class="flex items-center justify-between border-b border-neutral-100 px-5 py-4">
                        <h2 class="text-lg font-bold text-neutral-900">Your order</h2>
                        <button type="button" wire:click="$set('showCart', false)" class="flex h-8 w-8 items-center justify-center rounded-full text-neutral-400 hover:bg-neutral-100">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="overflow-y-auto px-5 py-4">
                        @foreach ($cart as $index => $row)
                            <div wire:key="cart-{{ $index }}" class="mb-4 flex gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-neutral-900">{{ $row['name'] }}</p>
                                    @if ($row['variant_name'])
                                        <p class="text-xs text-neutral-500">{{ $row['variant_name'] }}</p>
                                    @endif
                                    @if (!empty($row['addons']))
                                        <p class="text-xs text-neutral-500">+ {{ collect($row['addons'])->pluck('name')->join(', ') }}</p>
                                    @endif
                                    @if (!empty($row['notes']))
                                        <p class="mt-0.5 text-xs italic text-neutral-400">"{{ $row['notes'] }}"</p>
                                    @endif
                                    <div class="mt-2 flex items-center gap-3">
                                        <div class="flex items-center gap-2 rounded-lg border border-neutral-200 px-1.5 py-1">
                                            <button type="button" wire:click="decrementQty({{ $index }})" class="flex h-6 w-6 items-center justify-center rounded text-neutral-600 hover:bg-neutral-100">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                                            </button>
                                            <span class="w-4 text-center text-sm font-bold tabular-nums">{{ $row['quantity'] }}</span>
                                            <button type="button" wire:click="incrementQty({{ $index }})" class="flex h-6 w-6 items-center justify-center rounded text-neutral-600 hover:bg-neutral-100">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold tabular-nums text-neutral-900">{{ $this->money((float) $row['line_total']) }}</p>
                                    <button type="button" wire:click="removeFromCart({{ $index }})" class="mt-1 text-xs font-medium text-red-500 hover:text-red-600">Remove</button>
                                </div>
                            </div>
                        @endforeach

                        {{-- Guest details --}}
                        <div class="mt-2 space-y-3 border-t border-neutral-100 pt-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-neutral-400">
                                    Your name @if ($this->tenant->qr_ordering_requires_name)<span class="text-red-500">*</span>@endif
                                </label>
                                <input
                                    type="text"
                                    wire:model="guestName"
                                    placeholder="Name for this order"
                                    class="w-full rounded-xl border border-neutral-200 bg-neutral-50 p-3 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100"
                                />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-neutral-400">Order notes</label>
                                <textarea
                                    wire:model="orderNotes"
                                    rows="2"
                                    placeholder="Any notes for the whole order?"
                                    class="w-full rounded-xl border border-neutral-200 bg-neutral-50 p-3 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Footer: total + place --}}
                    <div class="border-t border-neutral-100 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-sm font-medium text-neutral-500">Subtotal</span>
                            <span class="text-lg font-bold tabular-nums text-neutral-900">{{ $this->money($this->cartSubtotal) }}</span>
                        </div>
                        <p class="mb-3 text-center text-xs text-neutral-400">Taxes are calculated and shown on your final bill. Pay at the counter or with your server.</p>
                        <button
                            type="button"
                            wire:click="placeOrder"
                            wire:loading.attr="disabled"
                            wire:target="placeOrder"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-amber-600 disabled:opacity-60"
                        >
                            <svg wire:loading wire:target="placeOrder" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span wire:loading.remove wire:target="placeOrder">Send order to kitchen</span>
                            <span wire:loading wire:target="placeOrder">Sending...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
