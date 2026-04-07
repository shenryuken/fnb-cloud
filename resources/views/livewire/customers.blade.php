<div class="flex flex-col gap-6 p-4 md:p-8 bg-neutral-50 dark:bg-neutral-950 min-h-full font-sans">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Customers</h2>
            <p class="text-neutral-500 font-medium">Register customers (name + email or mobile).</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-xl shadow-blue-500/20 hover:bg-blue-500 hover:shadow-blue-500/40 transition-all transform active:scale-95">
            <flux:icon.plus class="w-5 h-5" />
            Add Customer
        </button>
    </div>

    @if($isCreating || $editing)
        <div class="bg-white dark:bg-neutral-900 rounded-[3rem] border border-neutral-200 dark:border-neutral-800 shadow-2xl overflow-hidden">
            <div class="p-8 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center shadow-xl shadow-blue-500/20">
                        <flux:icon.users class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">
                            {{ $editing ? 'Update Customer' : 'New Customer' }}
                        </h3>
                        <p class="text-neutral-500 font-medium text-sm">Email or mobile is required.</p>
                    </div>
                </div>
                <button type="button" wire:click="$set('isCreating', false); $set('editing', null)" class="p-3 rounded-full hover:bg-neutral-200 dark:hover:bg-neutral-800 transition-colors">
                    <flux:icon.x-mark class="w-6 h-6 text-neutral-400" />
                </button>
            </div>

            <form wire:submit.prevent="save" class="p-8 space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest ml-2">Name</label>
                        <input type="text" wire:model="name" class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-black focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="Customer Name">
                        @error('name') <span class="text-red-500 text-xs font-bold ml-2">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest ml-2">Points</label>
                        <input type="number" wire:model="points_balance" class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-black focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="0">
                        @error('points_balance') <span class="text-red-500 text-xs font-bold ml-2">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest ml-2">Email (Optional)</label>
                        <input type="email" wire:model="email" class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-bold focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="customer@email.com">
                        @error('email') <span class="text-red-500 text-xs font-bold ml-2">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-neutral-400 uppercase tracking-widest ml-2">Mobile (Optional)</label>
                        <input type="text" wire:model="mobile" class="w-full rounded-2xl border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-4 font-bold focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="+60123456789">
                        @error('mobile') <span class="text-red-500 text-xs font-bold ml-2">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                    <button type="button" wire:click="$set('isCreating', false); $set('editing', null)" class="px-6 py-3 rounded-2xl font-black text-neutral-500 hover:text-neutral-800 transition-colors uppercase tracking-widest text-[10px]">Cancel</button>
                    <button type="submit" class="px-8 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-black shadow-xl shadow-blue-500/20 transition-all uppercase tracking-widest text-[10px]">
                        Save
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white dark:bg-neutral-900 rounded-3xl border border-neutral-200 dark:border-neutral-800 shadow-xl overflow-hidden">
        <div class="overflow-x-auto scrollbar-hide">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-neutral-50/50 dark:bg-neutral-800/50 border-b border-neutral-100 dark:border-neutral-800">
                        <th class="px-8 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Customer</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Email</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Mobile</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Points</th>
                        <th class="px-8 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="font-black text-neutral-800 dark:text-neutral-100 tracking-tight">{{ $customer->name }}</div>
                                <div class="text-[10px] font-black text-neutral-400 uppercase tracking-tighter mt-1">ID: #{{ $customer->id }}</div>
                            </td>
                            <td class="px-6 py-6 text-xs font-bold text-neutral-600 dark:text-neutral-300">
                                {{ $customer->email ?? '-' }}
                            </td>
                            <td class="px-6 py-6 text-xs font-bold text-neutral-600 dark:text-neutral-300">
                                {{ $customer->mobile ?? '-' }}
                            </td>
                            <td class="px-6 py-6 text-xs font-black text-blue-600 dark:text-blue-400 tabular-nums">
                                {{ (int) $customer->points_balance }}
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" wire:click="edit({{ $customer->id }})" class="p-2.5 rounded-xl bg-neutral-50 hover:bg-blue-50 dark:bg-neutral-800 dark:hover:bg-blue-900/20 text-neutral-400 hover:text-blue-600 transition-all border border-neutral-100 dark:border-neutral-700 hover:border-blue-100 dark:hover:border-blue-900/50">
                                        <flux:icon.pencil-square class="w-4 h-4" />
                                    </button>
                                    <button type="button" wire:click="delete({{ $customer->id }})" wire:confirm="Delete this customer?" class="p-2.5 rounded-xl bg-neutral-50 hover:bg-red-50 dark:bg-neutral-800 dark:hover:bg-red-900/20 text-neutral-400 hover:text-red-600 transition-all border border-neutral-100 dark:border-neutral-700 hover:border-red-100 dark:hover:border-red-900/50">
                                        <flux:icon.trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-24 text-center text-sm text-neutral-400 font-medium italic">No customers registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="px-8 py-6 bg-neutral-50/50 dark:bg-neutral-800/50 border-t border-neutral-100 dark:border-neutral-800">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
