<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout>
        <x-slot:slot>
<div class="flex flex-col gap-6 w-full">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Receipt Settings</flux:heading>
            <flux:subheading>Define your brand identity for customer transactions.</flux:subheading>
        </div>
        <flux:badge color="blue" icon="printer">Printer Config</flux:badge>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- Form Section --}}
        <div class="lg:col-span-7 flex flex-col gap-6">
            <form wire:submit="save" class="flex flex-col gap-6">

                {{-- Branding & Identity --}}
                <flux:card class="flex flex-col gap-5">
                    <flux:heading size="sm" class="text-zinc-400 uppercase tracking-widest text-xs font-black">Branding &amp; Identity</flux:heading>

                    <div class="flex flex-col md:flex-row gap-6 items-start">
                        {{-- Logo Upload --}}
                        <div class="relative group shrink-0">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" class="w-28 h-28 object-contain rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white shadow-sm" alt="Logo preview">
                            @elseif ($logo_url)
                                <img src="{{ $logo_url }}" class="w-28 h-28 object-contain rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white shadow-sm" alt="Current logo">
                            @else
                                <div class="w-28 h-28 bg-zinc-50 dark:bg-zinc-800 flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-700 text-zinc-400 group-hover:border-blue-400 transition-all cursor-pointer">
                                    <flux:icon.image class="w-8 h-8 mb-1 opacity-30" />
                                    <flux:text size="xs" class="font-bold uppercase tracking-widest text-center">Upload</flux:text>
                                </div>
                            @endif
                            <input type="file" wire:model="logo" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                        </div>

                        <div class="flex-1 flex flex-col gap-4 w-full">
                            <flux:field>
                                <flux:label>Restaurant Name</flux:label>
                                <flux:input wire:model="name" placeholder="e.g. Pizza Palace" icon="building-storefront" />
                                <flux:error name="name" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Address</flux:label>
                                <flux:input wire:model="address" placeholder="Business Address" icon="map-pin" />
                            </flux:field>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Phone</flux:label>
                            <flux:input wire:model="phone" placeholder="+1 234 567 890" icon="phone" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Public Email <flux:badge size="sm" color="zinc">Optional</flux:badge></flux:label>
                            <flux:input type="email" wire:model="receipt_email" placeholder="hello@restaurant.com" icon="envelope" />
                        </flux:field>
                    </div>
                </flux:card>

                {{-- Receipt Messaging --}}
                <flux:card class="flex flex-col gap-5">
                    <flux:heading size="sm" class="text-zinc-400 uppercase tracking-widest text-xs font-black">Receipt Messaging</flux:heading>

                    <flux:field>
                        <flux:label>Header Message</flux:label>
                        <flux:textarea wire:model="receipt_header" rows="2" placeholder="e.g. Welcome to Pizza Palace!" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Footer Message</flux:label>
                        <flux:textarea wire:model="receipt_footer" rows="2" placeholder="e.g. Thank you for dining with us!" />
                    </flux:field>
                </flux:card>

                {{-- Printer Size --}}
                <flux:card class="flex flex-col gap-5">
                    <flux:heading size="sm" class="text-zinc-400 uppercase tracking-widest text-xs font-black">Printer Hardware</flux:heading>

                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex flex-col items-center justify-center gap-1 p-5 rounded-xl border-2 cursor-pointer transition-all
                            {{ $receipt_size === '58mm' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/10' : 'border-zinc-100 dark:border-zinc-800 hover:border-zinc-200 dark:hover:border-zinc-700' }}">
                            <input type="radio" wire:model.live="receipt_size" value="58mm" class="sr-only">
                            <flux:text class="font-black text-lg {{ $receipt_size === '58mm' ? 'text-blue-600' : '' }}">58mm</flux:text>
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-widest">Standard Small</flux:text>
                            @if($receipt_size === '58mm')
                                <div class="absolute top-2 right-2 w-2 h-2 rounded-full bg-blue-600"></div>
                            @endif
                        </label>
                        <label class="relative flex flex-col items-center justify-center gap-1 p-5 rounded-xl border-2 cursor-pointer transition-all
                            {{ $receipt_size === '80mm' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/10' : 'border-zinc-100 dark:border-zinc-800 hover:border-zinc-200 dark:hover:border-zinc-700' }}">
                            <input type="radio" wire:model.live="receipt_size" value="80mm" class="sr-only">
                            <flux:text class="font-black text-lg {{ $receipt_size === '80mm' ? 'text-blue-600' : '' }}">80mm</flux:text>
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-widest">Premium Wide</flux:text>
                            @if($receipt_size === '80mm')
                                <div class="absolute top-2 right-2 w-2 h-2 rounded-full bg-blue-600"></div>
                            @endif
                        </label>
                    </div>
                </flux:card>

                {{-- Taxes --}}
                <flux:card class="flex flex-col gap-5">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm" class="text-zinc-400 uppercase tracking-widest text-xs font-black">Taxes</flux:heading>
                        <flux:button type="button" wire:click="addTax" size="sm" variant="ghost" icon="plus">Add Tax</flux:button>
                    </div>

                    <div class="flex flex-col gap-3">
                        @forelse($taxes as $i => $tax)
                            <div class="grid grid-cols-12 gap-3 items-center p-3 rounded-xl border border-zinc-100 dark:border-zinc-800">
                                <div class="col-span-5">
                                    <flux:input wire:model.live="taxes.{{ $i }}.name" placeholder="Tax name (e.g., GST)" size="sm" />
                                </div>
                                <div class="col-span-2">
                                    <flux:input wire:model.live="taxes.{{ $i }}.code" placeholder="Code" size="sm" />
                                </div>
                                <div class="col-span-3">
                                    <flux:input type="number" step="0.01" wire:model.live="taxes.{{ $i }}.rate" placeholder="%" size="sm" />
                                </div>
                                <div class="col-span-1 flex items-center justify-center">
                                    <flux:checkbox wire:model.live="taxes.{{ $i }}.is_enabled" />
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <flux:button type="button" wire:click="removeTax({{ $i }})" size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-600" />
                                </div>
                            </div>
                        @empty
                            <flux:text class="text-zinc-400 italic text-sm">No taxes configured. Click "Add Tax".</flux:text>
                        @endforelse
                    </div>
                </flux:card>

                {{-- Business Day --}}
                <flux:card class="flex flex-col gap-5">
                    <flux:heading size="sm" class="text-zinc-400 uppercase tracking-widest text-xs font-black">Business Day</flux:heading>

                    <div class="grid md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Start Time (sales day)</flux:label>
                            <flux:input type="time" wire:model.live="business_day_start_time" />
                        </flux:field>
                        <flux:field>
                            <flux:label>End Time (can cross midnight)</flux:label>
                            <flux:input type="time" wire:model.live="business_day_end_time" />
                        </flux:field>
                    </div>
                </flux:card>

                {{-- Business Hours --}}
                <flux:card class="flex flex-col gap-5">
                    <flux:heading size="sm" class="text-zinc-400 uppercase tracking-widest text-xs font-black">Business Hours</flux:heading>

                    @php $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']; @endphp

                    <div class="flex flex-col gap-3">
                        @foreach($business_hours as $i => $row)
                            <div class="p-4 rounded-xl border border-zinc-100 dark:border-zinc-800">
                                <div class="flex items-center justify-between gap-4 mb-3">
                                    <div class="flex items-center gap-3">
                                        <flux:badge color="zinc" size="sm">{{ $dayNames[$row['day_of_week']] ?? $row['day_of_week'] }}</flux:badge>
                                        <flux:text size="sm" class="font-semibold">Day {{ $row['day_of_week'] }}</flux:text>
                                    </div>
                                    <flux:checkbox wire:model.live="business_hours.{{ $i }}.is_closed" label="Closed" />
                                </div>

                                <div class="grid md:grid-cols-2 gap-4">
                                    <flux:field>
                                        <flux:label>Open</flux:label>
                                        <flux:input type="time" wire:model.live="business_hours.{{ $i }}.open_time" :disabled="$row['is_closed']" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Close</flux:label>
                                        <flux:input type="time" wire:model.live="business_hours.{{ $i }}.close_time" :disabled="$row['is_closed']" />
                                    </flux:field>
                                </div>

                                @if(!$row['is_closed'] && !empty($row['open_time']) && !empty($row['close_time']) && $row['close_time'] <= $row['open_time'])
                                    <div class="mt-3 flex items-start gap-2 px-3 py-2 rounded-lg bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400">
                                        <flux:icon.triangle-alert class="w-4 h-4 mt-0.5 shrink-0" />
                                        <flux:text size="sm" class="font-semibold">Operating across midnight — close time is treated as next day.</flux:text>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </flux:card>

                <flux:button type="submit" variant="primary" class="w-full" icon="check-circle">
                    Save Receipt Config
                </flux:button>
            </form>
        </div>

        {{-- Receipt Preview --}}
        <div class="lg:col-span-5">
            <div class="sticky top-10 flex flex-col items-center gap-4">
                <div class="w-full flex items-center justify-between">
                    <flux:heading size="sm" class="text-zinc-400 uppercase tracking-widest text-xs font-black">Real-time Preview</flux:heading>
                    <flux:badge color="green" size="sm" class="animate-pulse">Live</flux:badge>
                </div>

                <div class="bg-zinc-100 dark:bg-zinc-900 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-800 flex flex-col items-center w-full overflow-hidden">
                    <div class="bg-white dark:bg-zinc-950 shadow-xl font-mono text-[11px] text-zinc-800 dark:text-zinc-200 p-6 relative transition-all duration-500"
                         style="width: {{ $receipt_size === '58mm' ? '58mm' : '80mm' }}; min-height: 130mm;">

                        <div class="text-center space-y-2 mb-4">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" class="w-16 h-16 mx-auto object-contain grayscale" alt="Logo">
                            @elseif ($logo_url)
                                <img src="{{ $logo_url }}" class="w-16 h-16 mx-auto object-contain grayscale" alt="Logo">
                            @endif
                            <p class="text-base font-black uppercase tracking-tighter">{{ $name }}</p>
                            <div class="space-y-0.5 opacity-70 text-[10px]">
                                <p>{{ $address ?? '123 Restaurant St, Food City' }}</p>
                                <p>Tel: {{ $phone ?? '+1 234 567 890' }}</p>
                                @if($receipt_email)<p class="break-all">{{ $receipt_email }}</p>@endif
                            </div>
                        </div>

                        <div class="border-b-2 border-dashed border-zinc-200 dark:border-zinc-700 my-3"></div>

                        @if($receipt_header)
                            <p class="text-center italic mb-4 opacity-80">{{ $receipt_header }}</p>
                        @endif

                        <div class="space-y-1 mb-3">
                            <div class="flex justify-between font-black border-b border-zinc-100 dark:border-zinc-800 pb-1">
                                <span>ITEM</span><span>TOTAL</span>
                            </div>
                            <div class="flex justify-between"><span>Truffle Burger</span><span>$12.50</span></div>
                            <div class="flex justify-between"><span>Large Fries x2</span><span>$8.00</span></div>
                        </div>

                        <div class="border-b-2 border-dashed border-zinc-200 dark:border-zinc-700 my-3"></div>

                        <div class="space-y-1">
                            <div class="flex justify-between opacity-70"><span>SUBTOTAL</span><span>$20.50</span></div>
                            <div class="flex justify-between opacity-70">
                                <span>TAX ({{ number_format((float)($tax_rate ?? 0), 2) }}%)</span>
                                <span>${{ number_format(20.50 * ((float)($tax_rate ?? 0) / 100), 2) }}</span>
                            </div>
                            <div class="flex justify-between font-black pt-1 border-t border-zinc-100 dark:border-zinc-800">
                                <span>TOTAL</span>
                                <span>${{ number_format(20.50 + (20.50 * ((float)($tax_rate ?? 0) / 100)), 2) }}</span>
                            </div>
                        </div>

                        <div class="border-b-2 border-dashed border-zinc-200 dark:border-zinc-700 my-3"></div>

                        <div class="text-center space-y-2 mt-4">
                            <p class="font-black text-[10px] uppercase">{{ $receipt_footer ?? 'THANK YOU FOR YOUR VISIT!' }}</p>
                            <div class="text-[9px] opacity-40 space-y-0.5">
                                <p>{{ now()->format('M d, Y H:i') }}</p>
                                <p>Powered by F&amp;B Cloud</p>
                            </div>
                        </div>
                    </div>
                </div>

                <flux:text size="sm" class="text-zinc-400 text-center leading-relaxed max-w-xs">
                    Approximate preview only. Actual layout may vary by printer model.
                </flux:text>
            </div>
        </div>

    </div>
</div>
        </x-slot:slot>
    </x-settings.layout>
</section>
