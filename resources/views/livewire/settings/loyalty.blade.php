<div class="flex flex-col gap-6 p-4 md:p-8 bg-neutral-50 dark:bg-neutral-950 min-h-full font-sans">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Loyalty Program</h2>
            <p class="text-neutral-500 font-medium">Configure how customers earn and redeem points.</p>
        </div>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="bg-white dark:bg-neutral-900 p-8 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl space-y-6">
            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2">Loyalty Points</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest px-2">Earn Rate</label>
                    <div class="rounded-2xl border border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/40 p-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <div class="text-[9px] font-black text-neutral-400 uppercase tracking-widest px-1">Points</div>
                                <input type="number" step="0.0001" wire:model.live="points_earn_points"
                                    class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-right tabular-nums">
                                @error('points_earn_points') <span class="text-red-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <div class="text-[9px] font-black text-neutral-400 uppercase tracking-widest px-1">Per RM</div>
                                <input type="number" step="0.01" wire:model.live="points_earn_amount"
                                    class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-right tabular-nums">
                                @error('points_earn_amount') <span class="text-red-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest px-1">
                            Example: 1 point per RM 2.00 OR 2 points per RM 1.00
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest px-2">Redeem Value</label>
                    <div class="rounded-2xl border border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/40 p-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <div class="text-[9px] font-black text-neutral-400 uppercase tracking-widest px-1">Points</div>
                                <input type="number" step="1" wire:model.live="points_redeem_points"
                                    class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-right tabular-nums">
                                @error('points_redeem_points') <span class="text-red-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <div class="text-[9px] font-black text-neutral-400 uppercase tracking-widest px-1">Equals RM</div>
                                <input type="number" step="0.01" wire:model.live="points_redeem_amount"
                                    class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-right tabular-nums">
                                @error('points_redeem_amount') <span class="text-red-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest px-1">
                            Example: 250 points = RM 5.00
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest px-2">Minimum Redemption</label>
                    <div class="relative">
                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 font-black text-xs">Min pts</div>
                        <input type="number" step="1" wire:model.live="points_min_redeem"
                            class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 pl-20 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-right tabular-nums">
                    </div>
                    @error('points_min_redeem') <span class="text-red-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                    <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest px-2">0 = no minimum</div>
                </div>
            </div>

            <div class="pt-2 border-t border-neutral-100 dark:border-neutral-800"></div>

            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <div class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Points Promotion</div>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" wire:model.live="points_promo_is_enabled" class="rounded border-neutral-300 dark:border-neutral-700">
                        <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">Enabled</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest px-2">Multiplier</label>
                        <div class="relative">
                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 font-black text-xs">x</div>
                            <input type="number" step="0.01" wire:model.live="points_promo_multiplier"
                                class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 pl-12 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-right tabular-nums">
                        </div>
                        @error('points_promo_multiplier') <span class="text-red-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest px-2">Example: 2 = double, 3 = triple</div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest px-2">Starts At</label>
                        <input type="datetime-local" wire:model.live="points_promo_starts_at"
                            class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                        @error('points_promo_starts_at') <span class="text-red-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest px-2">Ends At</label>
                        <input type="datetime-local" wire:model.live="points_promo_ends_at"
                            class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                        @error('points_promo_ends_at') <span class="text-red-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-neutral-100 dark:border-neutral-800 flex justify-end">
                <button type="submit" class="px-8 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-black shadow-xl shadow-blue-500/20 transition-all uppercase tracking-widest text-[10px]">
                    Save
                </button>
            </div>
        </div>
    </form>
</div>
