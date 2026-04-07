<div class="flex flex-col gap-6 p-4 md:p-8 bg-neutral-50 dark:bg-neutral-950 min-h-full font-sans">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Quick Notes</h2>
            <p class="text-neutral-500 font-medium">Create reusable labels for item special instructions in POS.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" wire:click="addQuickNote" class="inline-flex items-center gap-2 rounded-2xl bg-white dark:bg-neutral-900 px-5 py-3 text-[10px] font-black uppercase tracking-widest text-neutral-600 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-800 hover:border-blue-500/40 hover:text-blue-600 transition-all">
                <flux:icon.plus class="w-4 h-4" />
                Add Label
            </button>
            <button type="button" wire:click="save" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-[10px] font-black uppercase tracking-widest text-white shadow-xl shadow-blue-500/20 hover:bg-blue-500 hover:shadow-blue-500/40 transition-all">
                Save
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-neutral-900 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl overflow-hidden">
        <div class="p-6 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex items-center justify-between">
            <h3 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Labels</h3>
            <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">{{ count($quick_notes) }} items</span>
        </div>

        <div class="p-6 space-y-3">
            @forelse($quick_notes as $i => $row)
                <div class="grid grid-cols-12 gap-3 items-center p-4 rounded-2xl border border-neutral-100 dark:border-neutral-800 bg-white/60 dark:bg-neutral-900/40">
                    <div class="col-span-1">
                        <input type="number" wire:model.live="quick_notes.{{ $i }}.sort_order"
                            class="w-full rounded-xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-3 font-black text-center tabular-nums focus:ring-4 focus:ring-blue-500/10">
                    </div>
                    <div class="col-span-8">
                        <input type="text" wire:model.live="quick_notes.{{ $i }}.text" placeholder="e.g. No spicy"
                            class="w-full rounded-xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-3 font-black focus:ring-4 focus:ring-blue-500/10">
                        @error("quick_notes.$i.text") <div class="text-red-500 text-xs font-bold mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-span-2 flex items-center justify-center">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" wire:model.live="quick_notes.{{ $i }}.is_active" class="rounded border-neutral-300 dark:border-neutral-700">
                            <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">On</span>
                        </label>
                    </div>
                    <div class="col-span-1 flex items-center justify-end">
                        <button type="button" wire:click="removeQuickNote({{ $i }})" class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/10 text-red-600 hover:bg-red-500 hover:text-white transition-all">
                            <flux:icon.trash class="w-4 h-4 mx-auto" />
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-sm text-neutral-400 font-medium italic">
                    No quick notes yet. Click “Add Label”.
                </div>
            @endforelse
        </div>
    </div>
</div>

