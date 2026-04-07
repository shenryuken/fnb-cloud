<div class="flex flex-col gap-8 p-4 md:p-10 max-w-6xl mx-auto font-sans">
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <h2 class="text-4xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Receipt Settings</h2>
            <p class="text-neutral-500 font-medium">Define your brand identity for customer transactions</p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-800">
            <flux:icon.printer class="w-5 h-5 text-blue-600" />
            <span class="text-xs font-black text-blue-600 uppercase tracking-widest">Printer Config</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Form Section -->
        <div class="lg:col-span-7 space-y-8">
            <form wire:submit="save" class="space-y-8">
                <!-- Branding Card -->
                <div class="bg-white dark:bg-neutral-900 p-8 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl space-y-8">
                    <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2">Branding & Identity</h4>
                    
                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        <div class="relative group">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" class="w-32 h-32 object-contain rounded-[2rem] border-2 border-white dark:border-neutral-700 shadow-2xl bg-white">
                            @elseif ($logo_url)
                                <img src="{{ $logo_url }}" class="w-32 h-32 object-contain rounded-[2rem] border-2 border-white dark:border-neutral-700 shadow-2xl bg-white">
                            @else
                                <div class="w-32 h-32 bg-neutral-50 dark:bg-neutral-800 flex flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-neutral-200 dark:border-neutral-700 text-neutral-400 group-hover:border-blue-400 group-hover:text-blue-500 transition-all cursor-pointer">
                                    <flux:icon.image class="w-10 h-10 mb-2 opacity-20" />
                                    <span class="text-[10px] font-black uppercase tracking-widest">Upload Logo</span>
                                </div>
                            @endif
                            <input type="file" wire:model="logo" class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>

                        <div class="flex-1 space-y-6 w-full">
                            <div class="relative group">
                                <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                    <flux:icon.building-storefront class="w-5 h-5" />
                                </div>
                                <input type="text" wire:model="name" 
                                    class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                    placeholder="Restaurant Name">
                            </div>
                            @error('name') <span class="text-xs text-red-500 font-bold ml-4">{{ $message }}</span> @enderror

                            <div class="relative group">
                                <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                    <flux:icon.map-pin class="w-5 h-5" />
                                </div>
                                <input type="text" wire:model="address" 
                                    class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 pl-14 font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                    placeholder="Business Address">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative group">
                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                <flux:icon.phone class="w-5 h-5" />
                            </div>
                            <input type="text" wire:model="phone" 
                                class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 pl-14 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                placeholder="Phone Number">
                        </div>
                        <div class="relative group">
                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                <flux:icon.envelope class="w-5 h-5" />
                            </div>
                            <input type="email" wire:model="receipt_email" 
                                class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 pl-14 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                placeholder="Public Email">
                        </div>
                    </div>
                </div>

                <!-- Messaging Card -->
                <div class="bg-white dark:bg-neutral-900 p-8 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl space-y-6">
                    <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2">Receipt Messaging</h4>
                    
                    <div class="space-y-6">
                        <div class="relative group">
                            <div class="absolute left-5 top-5 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                <flux:icon.chat-bubble-bottom-center-text class="w-5 h-5" />
                            </div>
                            <textarea wire:model="receipt_header" rows="2" 
                                class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                placeholder="Header Message (e.g. Welcome to Pizza Palace!)"></textarea>
                        </div>
                        <div class="relative group">
                            <div class="absolute left-5 top-5 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                <flux:icon.heart class="w-5 h-5" />
                            </div>
                            <textarea wire:model="receipt_footer" rows="2" 
                                class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                placeholder="Footer Message (e.g. Thank you for dining with us!)"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Hardware Card -->
                <div class="bg-white dark:bg-neutral-900 p-8 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl space-y-6">
                    <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2">Printer Hardware</h4>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <label class="group relative flex items-center justify-center gap-3 p-6 rounded-[2rem] border-2 cursor-pointer transition-all duration-300
                            {{ $receipt_size === '58mm' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 shadow-xl shadow-blue-500/10' : 'border-neutral-100 dark:border-neutral-800 hover:border-neutral-200 dark:hover:border-neutral-700' }}">
                            <input type="radio" wire:model.live="receipt_size" value="58mm" class="sr-only">
                            <div class="flex flex-col items-center">
                                <span class="font-black text-lg {{ $receipt_size === '58mm' ? 'text-blue-600' : 'text-neutral-500 group-hover:text-neutral-700' }}">58mm</span>
                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Standard Small</span>
                            </div>
                            @if($receipt_size === '58mm')
                                <div class="absolute top-3 right-3 w-2 h-2 rounded-full bg-blue-600"></div>
                            @endif
                        </label>
                        <label class="group relative flex items-center justify-center gap-3 p-6 rounded-[2rem] border-2 cursor-pointer transition-all duration-300
                            {{ $receipt_size === '80mm' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 shadow-xl shadow-blue-500/10' : 'border-neutral-100 dark:border-neutral-800 hover:border-neutral-200 dark:hover:border-neutral-700' }}">
                            <input type="radio" wire:model.live="receipt_size" value="80mm" class="sr-only">
                            <div class="flex flex-col items-center">
                                <span class="font-black text-lg {{ $receipt_size === '80mm' ? 'text-blue-600' : 'text-neutral-500 group-hover:text-neutral-700' }}">80mm</span>
                                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Premium Wide</span>
                            </div>
                            @if($receipt_size === '80mm')
                                <div class="absolute top-3 right-3 w-2 h-2 rounded-full bg-blue-600"></div>
                            @endif
                        </label>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 p-8 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl space-y-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2">Taxes</h4>
                        <button type="button" wire:click="addTax" class="px-3 py-1.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[10px] font-black uppercase tracking-widest hover:border-blue-500 hover:text-blue-600 transition-all">Add Tax</button>
                    </div>
                    <div class="space-y-3">
                        @forelse($taxes as $i => $tax)
                            <div class="grid grid-cols-12 gap-3 items-center p-3 rounded-2xl border border-neutral-100 dark:border-neutral-800 bg-white/60 dark:bg-neutral-900/40">
                                <div class="col-span-5">
                                    <input type="text" wire:model.live="taxes.{{ $i }}.name" placeholder="Tax name (e.g., GST)" class="w-full rounded-xl border-neutral-200 dark:border-neutral-700 dark:bg-neutral-800 p-3 font-bold">
                                </div>
                                <div class="col-span-2">
                                    <input type="text" wire:model.live="taxes.{{ $i }}.code" placeholder="Code" class="w-full rounded-xl border-neutral-200 dark:border-neutral-700 dark:bg-neutral-800 p-3 font-bold">
                                </div>
                                <div class="col-span-3 relative">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 font-black text-sm">%</div>
                                    <input type="number" step="0.01" wire:model.live="taxes.{{ $i }}.rate" class="w-full rounded-xl border-neutral-200 dark:border-neutral-700 dark:bg-neutral-800 p-3 pl-7 font-bold text-right">
                                </div>
                                <div class="col-span-1 flex items-center justify-center">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-neutral-400 flex items-center gap-2">
                                        <input type="checkbox" wire:model.live="taxes.{{ $i }}.is_enabled" class="rounded border-neutral-300 dark:border-neutral-700">
                                        On
                                    </label>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <button type="button" wire:click="removeTax({{ $i }})" class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-900/10 text-red-600 hover:bg-red-500 hover:text-white transition-all">
                                        <flux:icon.trash class="w-4 h-4 mx-auto" />
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-neutral-400 font-medium italic px-2">No taxes configured. Click “Add Tax”.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 p-8 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl space-y-6">
                    <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2">Business Day</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative group">
                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                <flux:icon.play class="w-5 h-5" />
                            </div>
                            <input type="time" wire:model.live="business_day_start_time"
                                class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            <div class="mt-2 text-[10px] font-black text-neutral-400 uppercase tracking-widest px-2">Start time (sales day)</div>
                        </div>

                        <div class="relative group">
                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                <flux:icon.stop class="w-5 h-5" />
                            </div>
                            <input type="time" wire:model.live="business_day_end_time"
                                class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            <div class="mt-2 text-[10px] font-black text-neutral-400 uppercase tracking-widest px-2">End time (can cross midnight)</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 p-8 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl space-y-6">
                    <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2">Business Hours</h4>
                    @php
                        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    @endphp
                    <div class="space-y-3">
                        @foreach($business_hours as $i => $row)
                            <div class="p-4 rounded-2xl border border-neutral-100 dark:border-neutral-800 bg-white/60 dark:bg-neutral-900/40">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center border border-neutral-200 dark:border-neutral-700">
                                            <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">{{ $dayNames[$row['day_of_week']] ?? $row['day_of_week'] }}</span>
                                        </div>
                                        <div class="text-sm font-black text-neutral-700 dark:text-neutral-200">Day {{ $row['day_of_week'] }}</div>
                                    </div>

                                    <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-neutral-400">
                                        <input type="checkbox" wire:model.live="business_hours.{{ $i }}.is_closed" class="rounded border-neutral-300 dark:border-neutral-700">
                                        Closed
                                    </label>
                                </div>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                            <flux:icon.play class="w-5 h-5" />
                                        </div>
                                        <input type="time" wire:model.live="business_hours.{{ $i }}.open_time" @disabled($row['is_closed'])
                                            class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all disabled:opacity-50">
                                        <div class="mt-2 text-[10px] font-black text-neutral-400 uppercase tracking-widest px-2">Open</div>
                                    </div>

                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                            <flux:icon.stop class="w-5 h-5" />
                                        </div>
                                        <input type="time" wire:model.live="business_hours.{{ $i }}.close_time" @disabled($row['is_closed'])
                                            class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all disabled:opacity-50">
                                        <div class="mt-2 text-[10px] font-black text-neutral-400 uppercase tracking-widest px-2">Close</div>
                                    </div>
                                </div>

                                @if(!$row['is_closed'] && !empty($row['open_time']) && !empty($row['close_time']) && $row['close_time'] <= $row['open_time'])
                                    <div class="mt-4 px-4 py-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600">
                                        <div class="flex items-start gap-3">
                                            <flux:icon.triangle-alert class="w-5 h-5 mt-0.5" />
                                            <div>
                                                <div class="text-[10px] font-black uppercase tracking-widest">Operating Across Midnight</div>
                                                <div class="text-xs font-black mt-1">Close time is earlier than open time, so closing is treated as next day.</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-6 rounded-[2rem] bg-blue-600 hover:bg-blue-500 text-white font-black text-xl shadow-2xl shadow-blue-500/30 hover:shadow-blue-500/50 transition-all transform active:scale-95 flex items-center justify-center gap-3 group">
                        <flux:icon.check-circle class="w-6 h-6 group-hover:scale-110 transition-transform" />
                        SAVE RECEIPT CONFIG
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview Section -->
        <div class="lg:col-span-5 relative">
            <div class="sticky top-10 flex flex-col items-center">
                <div class="w-full mb-6 flex items-center justify-between px-2">
                    <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Real-time Preview</h4>
                    <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 text-[10px] font-black rounded-full uppercase tracking-widest animate-pulse">Live</span>
                </div>
                
                <div class="bg-neutral-100 dark:bg-neutral-950 p-10 rounded-[3rem] border-4 border-white dark:border-neutral-800 shadow-2xl flex flex-col items-center transition-all duration-500 overflow-hidden group/receipt">
                    <div class="bg-white dark:bg-neutral-900 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] p-8 transition-all duration-500 font-mono text-[11px] text-neutral-800 dark:text-neutral-200 relative group-hover/receipt:scale-[1.02]"
                         style="width: {{ $receipt_size === '58mm' ? '58mm' : '80mm' }}; min-height: 140mm;">
                        
                        <!-- Receipt Content -->
                        <div class="text-center space-y-3">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" class="w-20 h-20 mx-auto object-contain mb-2 grayscale">
                            @elseif ($logo_url)
                                <img src="{{ $logo_url }}" class="w-20 h-20 mx-auto object-contain mb-2 grayscale">
                            @endif
                            <h3 class="text-xl font-black uppercase tracking-tighter leading-none">{{ $name }}</h3>
                            <div class="space-y-0.5 opacity-70">
                                <p class="leading-tight">{{ $address ?? '123 Restaurant St, Food City' }}</p>
                                <p>Tel: {{ $phone ?? '+1 234 567 890' }}</p>
                                @if($receipt_email)
                                    <p class="break-all">{{ $receipt_email }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="border-b-2 border-dashed border-neutral-200 dark:border-neutral-700 my-6"></div>

                        @if($receipt_header)
                            <p class="text-center italic mb-6 leading-tight opacity-80 px-2">{{ $receipt_header }}</p>
                        @endif

                        <div class="space-y-2">
                            <div class="flex justify-between font-black border-b border-neutral-100 dark:border-neutral-800 pb-1 mb-2">
                                <span>ITEM</span>
                                <div class="flex gap-4">
                                    <span>QTY</span>
                                    <span>TOTAL</span>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <span class="truncate">Truffle Beef Burger</span>
                                <div class="flex gap-8">
                                    <span>1</span>
                                    <span>$12.50</span>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <span class="truncate">Large French Fries</span>
                                <div class="flex gap-8">
                                    <span>2</span>
                                    <span>$8.00</span>
                                </div>
                            </div>
                        </div>

                        <div class="border-b-2 border-dashed border-neutral-200 dark:border-neutral-700 my-4"></div>

                        <div class="flex justify-between text-[9px] font-black uppercase tracking-widest opacity-60">
                            <span>Order Type</span>
                            <span class="text-blue-600">Dine-in (Table 5)</span>
                        </div>

                        <div class="border-b-2 border-dashed border-neutral-200 dark:border-neutral-700 my-4"></div>

                        <div class="space-y-1.5">
                            <div class="flex justify-between opacity-70">
                                <span>SUBTOTAL</span>
                                <span>$20.50</span>
                            </div>
                            <div class="flex justify-between opacity-70">
                                <span>TAX ({{ number_format((float) ($tax_rate ?? 0), 2) }}%)</span>
                                <span>${{ number_format(20.50 * ((float) ($tax_rate ?? 0) / 100), 2) }}</span>
                            </div>
                            <div class="flex justify-between text-base font-black pt-2 border-t border-neutral-100 dark:border-neutral-800 mt-2">
                                <span>TOTAL</span>
                                <span>${{ number_format(20.50 + (20.50 * ((float) ($tax_rate ?? 0) / 100)), 2) }}</span>
                            </div>
                        </div>

                        <div class="border-b-2 border-dashed border-neutral-200 dark:border-neutral-700 my-6"></div>

                        <div class="text-center space-y-3 mt-6">
                            <p class="font-black text-sm uppercase">{{ $receipt_footer ?? 'THANK YOU FOR YOUR VISIT!' }}</p>
                            <div class="text-[9px] opacity-40 uppercase tracking-widest space-y-1">
                                <p>{{ now()->format('M d, Y H:i:s') }}</p>
                                <p>Transaction #8829-102</p>
                                <p class="pt-4">Powered by F&B Cloud</p>
                            </div>
                        </div>

                        <!-- Thermal Paper Edge Effect -->
                        <div class="absolute bottom-0 left-0 w-full h-4 bg-gradient-to-t from-neutral-50/50 dark:from-neutral-950/50 to-transparent"></div>
                    </div>
                </div>
                
                <p class="mt-6 text-xs font-medium text-neutral-400 max-w-[250px] text-center leading-relaxed">
                    This is an approximate visualization. Actual print layout may vary slightly depending on your thermal printer model.
                </p>
            </div>
        </div>
    </div>
</div>
