<div class="p-6 bg-neutral-900 min-h-screen text-white font-sans" wire:poll.10s>
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-orange-600 flex items-center justify-center shadow-2xl shadow-orange-500/20">
                <flux:icon.fire class="w-8 h-8 text-white" />
            </div>
            <div>
                <h1 class="text-3xl font-black tracking-tight">KITCHEN DISPLAY</h1>
                <p class="text-neutral-500 text-xs font-black uppercase tracking-[0.2em]">Live Order Stream</p>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <button 
                wire:click="toggleBusy" 
                class="flex items-center gap-3 px-6 py-3 rounded-2xl border-2 transition-all duration-300 group
                {{ $isBusy 
                    ? 'bg-red-500/10 border-red-500 text-red-500 shadow-lg shadow-red-500/20' 
                    : 'bg-emerald-500/10 border-emerald-500 text-emerald-500 hover:bg-emerald-500/20 shadow-lg shadow-emerald-500/10' 
                }}"
            >
                <div class="relative flex items-center justify-center">
                    <div class="w-3 h-3 rounded-full {{ $isBusy ? 'bg-red-500 animate-ping' : 'bg-emerald-500 animate-pulse' }}"></div>
                    <div class="absolute w-1.5 h-1.5 rounded-full {{ $isBusy ? 'bg-red-600' : 'bg-emerald-600' }}"></div>
                </div>
                <div class="flex flex-col items-start leading-none">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-70">Kitchen Status</span>
                    <span class="text-sm font-black uppercase tracking-tight">{{ $isBusy ? 'BUSY / DELAY' : 'NORMAL / FAST' }}</span>
                </div>
                <flux:icon.chevron-right class="w-4 h-4 opacity-50 group-hover:translate-x-1 transition-transform" />
            </button>

            <div class="flex items-center gap-2 px-4 py-2 bg-neutral-800 rounded-xl border border-neutral-700">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-[10px] font-black uppercase tracking-widest text-neutral-400">Live Connection</span>
            </div>
            <div class="text-right">
                <p class="text-xs font-black text-neutral-500 uppercase tracking-widest">{{ now()->format('l, d M Y') }}</p>
                <p class="text-xl font-black tabular-nums">{{ now()->format('H:i:s') }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
        @forelse($orders as $order)
            <div class="bg-neutral-800 rounded-[2rem] border-2 flex flex-col overflow-hidden transition-all duration-300
                {{ $order->is_overdue ? 'border-red-600 shadow-2xl shadow-red-600/20 animate-pulse' : ($order->kds_status === 'preparing' ? 'border-orange-500 shadow-2xl shadow-orange-500/10' : ($order->kds_status === 'ready' ? 'border-emerald-500 shadow-2xl shadow-emerald-500/10' : 'border-neutral-700')) }}">
                
                <!-- Order Header -->
                <div class="p-5 flex items-center justify-between border-b border-neutral-700/50 {{ $order->is_overdue ? 'bg-red-600/20' : 'bg-neutral-900/30' }}">
                    <div>
                        <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">Order #{{ $order->id }}</span>
                        <h3 class="text-lg font-black tracking-tight mt-1 flex items-center gap-2">
                            @if($order->order_type === 'dine_in')
                                <flux:icon.building-storefront class="w-4 h-4 text-blue-500" />
                                <span>Table {{ $order->table_number ?? '?' }}</span>
                            @else
                                <flux:icon.shopping-bag class="w-4 h-4 text-orange-500" />
                                <span>Takeaway</span>
                            @endif
                        </h3>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest block">{{ $order->created_at->diffForHumans() }}</span>
                        <div class="flex flex-col items-end gap-1 mt-1">
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest inline-block
                                {{ $order->is_overdue ? 'bg-red-600 text-white' : ($order->kds_status === 'preparing' ? 'bg-orange-500/20 text-orange-500' : ($order->kds_status === 'ready' ? 'bg-emerald-500/20 text-emerald-500' : 'bg-neutral-700 text-neutral-400')) }}">
                                {{ $order->is_overdue ? 'Delayed' : $order->kds_status }}
                            </span>
                            @if($order->kds_status === 'preparing')
                                <div class="flex flex-col items-end gap-1">
                                    <span class="flex items-center gap-1 text-[9px] font-black text-blue-400 uppercase tracking-widest bg-blue-500/10 px-2 py-0.5 rounded-lg border border-blue-500/20">
                                        <flux:icon.clock class="w-3 h-3" />
                                        {{ $order->prep_time_minutes ?? 0 }}m Target
                                    </span>

                                    <span class="flex items-center gap-1 text-[9px] font-black text-emerald-400 uppercase tracking-widest bg-emerald-500/10 px-2 py-0.5 rounded-lg border border-emerald-500/20">
                                        <flux:icon.check-circle class="w-3 h-3" />
                                        {{ $order->items->where('kds_is_ready', true)->count() }}/{{ $order->items->count() }} Ready
                                    </span>
                                    
                                    @if($order->is_overdue)
                                        <span class="flex items-center gap-1 text-[10px] font-black text-white uppercase tracking-widest bg-red-600 px-2 py-1 rounded-lg border border-red-500 animate-bounce">
                                            OVERDUE!
                                        </span>
                                    @elseif(!is_null($order->remaining_minutes))
                                        <span class="flex items-center gap-1 text-[10px] font-black {{ $order->remaining_minutes <= 5 ? 'text-red-500 animate-pulse' : 'text-emerald-500' }} uppercase tracking-widest bg-neutral-900 px-2 py-1 rounded-lg border {{ $order->remaining_minutes <= 5 ? 'border-red-500/50' : 'border-emerald-500/50' }}">
                                            {{ $order->remaining_minutes }}M Left
                                        </span>
                                    @endif
                                </div>
                            @endif
                            @if($order->kds_status === 'ready')
                                <span class="flex items-center gap-1 text-[9px] font-black text-blue-400 uppercase tracking-widest bg-blue-500/10 px-2 py-0.5 rounded-lg border border-blue-500/20 mt-1">
                                    <flux:icon.truck class="w-3 h-3" />
                                    {{ $order->items->where('kds_is_served', true)->count() }}/{{ $order->items->count() }} Served
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="flex-1 p-5 space-y-4 overflow-y-auto max-h-[300px] scrollbar-hide">
                    @foreach($order->items as $item)
                        <div class="flex gap-4 items-start">
                            <div class="w-10 h-10 rounded-xl bg-neutral-900 flex items-center justify-center font-black text-lg text-neutral-400 border border-neutral-700">
                                {{ $item->quantity }}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black text-sm tracking-tight text-neutral-100 uppercase">{{ $item->product?->name }}</h4>
                                @if($item->variant)
                                    <span class="text-[9px] font-black text-orange-500 uppercase tracking-widest">{{ $item->variant->name }}</span>
                                @endif
                                @if($item->addons->count() > 0)
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($item->addons as $addon)
                                            <span class="text-[9px] font-bold text-neutral-500 bg-neutral-900 px-2 py-0.5 rounded-lg border border-neutral-700">+ {{ $addon->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($item->components->count() > 0)
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($item->components as $component)
                                            <span class="text-[9px] font-bold text-neutral-500 bg-neutral-900 px-2 py-0.5 rounded-lg border border-neutral-700">Set: {{ $component->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($item->notes)
                                    <div class="mt-2 p-2 rounded-lg bg-orange-500/10 border border-orange-500/20 text-[10px] text-orange-400 font-bold italic">
                                        Note: {{ $item->notes }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-col gap-2 items-end">
                                @if(in_array($order->kds_status, ['preparing', 'ready'], true))
                                    <button type="button" wire:click="toggleItemReady({{ $item->id }})"
                                        class="w-10 h-10 rounded-xl border font-black text-[9px] uppercase tracking-widest transition-all flex items-center justify-center
                                        {{ $item->kds_is_ready ? 'bg-emerald-600 border-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'bg-neutral-900 border-neutral-700 text-neutral-400 hover:border-emerald-500/50 hover:text-emerald-400' }}">
                                        <flux:icon.check-circle class="w-4 h-4" />
                                    </button>

                                    <button type="button" wire:click="toggleItemServed({{ $item->id }})"
                                        class="w-10 h-10 rounded-xl border font-black text-[9px] uppercase tracking-widest transition-all flex items-center justify-center
                                        {{ $item->kds_is_served ? 'bg-blue-600 border-blue-500 text-white shadow-lg shadow-blue-500/20' : 'bg-neutral-900 border-neutral-700 text-neutral-400 hover:border-blue-500/50 hover:text-blue-400' }}">
                                        <flux:icon.truck class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($order->notes)
                    <div class="px-5 py-3 bg-red-500/10 border-t border-red-500/20">
                        <p class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-1">Kitchen Note</p>
                        <p class="text-xs font-medium text-red-200">{{ $order->notes }}</p>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="p-3 bg-neutral-900/50 border-t border-neutral-700/50 flex flex-col gap-3">
                    @if($order->kds_status === 'pending')
                        <div class="grid grid-cols-2 gap-2">
                            @foreach([10, 15, 20, 30] as $time)
                                <button wire:click="updateStatus({{ $order->id }}, 'preparing', {{ $time }})" 
                                    class="py-3 rounded-xl bg-orange-600/20 hover:bg-orange-600 text-orange-500 hover:text-white border border-orange-500/30 font-black text-[10px] uppercase tracking-widest transition-all">
                                    {{ $time }} MIN
                                </button>
                            @endforeach
                        </div>
                        <button wire:click="updateStatus({{ $order->id }}, 'preparing')" 
                            class="w-full py-4 rounded-2xl bg-orange-600 hover:bg-orange-500 text-white font-black text-xs uppercase tracking-widest transition-all transform active:scale-95 flex items-center justify-center gap-2 shadow-lg shadow-orange-500/20">
                            <flux:icon.fire class="w-4 h-4" />
                            Start Cooking
                        </button>
                    @elseif($order->kds_status === 'preparing')
                        <div class="grid grid-cols-4 gap-2 mb-1">
                            @foreach([10, 15, 20, 30] as $time)
                                <button wire:click="updateStatus({{ $order->id }}, 'preparing', {{ $time }})" 
                                    class="py-2 rounded-lg bg-neutral-800 hover:bg-orange-600 text-neutral-400 hover:text-white border border-neutral-700 font-black text-[8px] uppercase tracking-tighter transition-all">
                                    {{ $time }}M
                                </button>
                            @endforeach
                        </div>
                        <button wire:click="updateStatus({{ $order->id }}, 'ready')" 
                            class="w-full py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-widest transition-all transform active:scale-95 flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20">
                            <flux:icon.check-circle class="w-4 h-4" />
                            Mark Ready
                        </button>
                    @elseif($order->kds_status === 'ready')
                        <button wire:click="updateStatus({{ $order->id }}, 'served')" 
                            class="w-full py-4 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-black text-xs uppercase tracking-widest transition-all transform active:scale-95 flex items-center justify-center gap-2 shadow-lg shadow-blue-500/20">
                            <flux:icon.truck class="w-4 h-4" />
                            Serve Order
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-40 flex flex-col items-center justify-center text-neutral-600">
                <div class="w-32 h-32 rounded-[3rem] bg-neutral-800 flex items-center justify-center mb-6">
                    <flux:icon.clipboard-document-check class="w-16 h-16 opacity-20" />
                </div>
                <h3 class="text-2xl font-black tracking-tight text-neutral-500">KITCHEN IS CLEAR</h3>
                <p class="text-sm font-medium mt-2">Waiting for new incoming orders...</p>
            </div>
        @endforelse
    </div>
</div>
