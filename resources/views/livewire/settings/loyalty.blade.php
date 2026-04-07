<div class="flex flex-col gap-6 p-4 md:p-8 max-w-2xl">

    {{-- Header --}}
    <div>
        <flux:heading size="xl" level="2">Loyalty Program</flux:heading>
        <flux:subheading>Configure points earning and redemption rules.</flux:subheading>
    </div>

    <form wire:submit="save" class="flex flex-col gap-6">

        {{-- Points Config --}}
        <flux:card class="flex flex-col gap-6">
            <flux:heading size="sm" class="text-zinc-400 uppercase tracking-widest text-xs font-black">Loyalty Points</flux:heading>

            <div class="grid md:grid-cols-3 gap-6">
                {{-- Earn Rate --}}
                <div class="flex flex-col gap-3">
                    <flux:heading size="sm">Earn Rate</flux:heading>
                    <div class="grid grid-cols-2 gap-3">
                        <flux:field>
                            <flux:label>Points</flux:label>
                            <flux:input type="number" wire:model="earn_points" placeholder="1" class="text-right tabular-nums" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Per RM</flux:label>
                            <flux:input type="number" step="0.01" wire:model="earn_per_rm" placeholder="1.00" class="text-right tabular-nums" />
                        </flux:field>
                    </div>
                    <flux:text size="xs" class="text-zinc-400">
                        {{ $earn_points ?? 1 }} pt per RM {{ number_format((float)($earn_per_rm ?? 1), 2) }} spent
                    </flux:text>
                </div>

                {{-- Redeem Value --}}
                <div class="flex flex-col gap-3">
                    <flux:heading size="sm">Redeem Value</flux:heading>
                    <div class="grid grid-cols-2 gap-3">
                        <flux:field>
                            <flux:label>Points</flux:label>
                            <flux:input type="number" wire:model="redeem_points" placeholder="100" class="text-right tabular-nums" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Equals RM</flux:label>
                            <flux:input type="number" step="0.01" wire:model="redeem_rm" placeholder="1.00" class="text-right tabular-nums" />
                        </flux:field>
                    </div>
                    <flux:text size="xs" class="text-zinc-400">
                        {{ $redeem_points ?? 100 }} pts = RM {{ number_format((float)($redeem_rm ?? 1), 2) }}
                    </flux:text>
                </div>

                {{-- Minimum Redemption --}}
                <div class="flex flex-col gap-3">
                    <flux:heading size="sm">Min. Redemption</flux:heading>
                    <flux:field>
                        <flux:label>Minimum Points</flux:label>
                        <flux:input type="number" wire:model="min_redeem_points" placeholder="0" class="text-right tabular-nums" />
                        <flux:description>0 = no minimum</flux:description>
                    </flux:field>
                </div>
            </div>
        </flux:card>

        {{-- Points Promotion --}}
        <flux:card class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <flux:heading size="sm" class="text-zinc-400 uppercase tracking-widest text-xs font-black">Points Promotion</flux:heading>
                <flux:switch wire:model.live="promo_enabled" />
            </div>

            @if($promo_enabled)
                <div class="grid md:grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>Multiplier</flux:label>
                        <flux:input type="number" step="0.1" wire:model="promo_multiplier" placeholder="2" />
                        <flux:description>2 = double, 3 = triple</flux:description>
                    </flux:field>

                    <flux:field>
                        <flux:label>Starts At</flux:label>
                        <flux:input type="datetime-local" wire:model="promo_starts_at" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Ends At</flux:label>
                        <flux:input type="datetime-local" wire:model="promo_ends_at" />
                    </flux:field>
                </div>
            @endif
        </flux:card>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" icon="check-circle">Save Loyalty Config</flux:button>
        </div>
    </form>
</div>
