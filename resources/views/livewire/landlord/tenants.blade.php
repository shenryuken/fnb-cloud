<div class="flex flex-col gap-6 p-4 md:p-8 bg-neutral-50 dark:bg-neutral-950 min-h-full font-sans">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Manage Tenants</h2>
            <p class="text-neutral-500 font-medium">Global administration of restaurant restaurant accounts</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-xl shadow-blue-500/20 hover:bg-blue-500 hover:shadow-blue-500/40 transition-all transform active:scale-95">
            <flux:icon.plus class="w-5 h-5" />
            Add New Tenant
        </button>
    </div>

    <!-- Search -->
    <div class="relative max-w-md group">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by restaurant name or slug..." class="w-full pl-12 pr-4 py-3 bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm font-medium">
        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
            <flux:icon.magnifying-glass class="w-5 h-5" />
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white dark:bg-neutral-900 rounded-3xl border border-neutral-200 dark:border-neutral-800 shadow-xl overflow-hidden">
        <div class="overflow-x-auto scrollbar-hide">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-neutral-50/50 dark:bg-neutral-800/50 border-b border-neutral-100 dark:border-neutral-800">
                        <th class="px-8 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Restaurant</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest">Digital Presence</th>
                        <th class="px-6 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-8 py-5 text-xs font-black text-neutral-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center border border-neutral-200 dark:border-neutral-700 font-black text-blue-600">
                                        {{ substr($tenant->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-black text-neutral-800 dark:text-neutral-100 tracking-tight">{{ $tenant->name }}</div>
                                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-tighter">ID: #{{ $tenant->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-bold text-neutral-700 dark:text-neutral-300">/{{ $tenant->slug }}</span>
                                    @if($tenant->domain)
                                        <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest">{{ $tenant->domain }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <button wire:click="toggleStatus({{ $tenant->id }})" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider transition-all
                                    {{ $tenant->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-200' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 hover:bg-red-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $tenant->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <button wire:click="edit({{ $tenant->id }})" class="p-2.5 rounded-xl bg-neutral-50 hover:bg-blue-50 dark:bg-neutral-800 dark:hover:bg-blue-900/20 text-neutral-400 hover:text-blue-600 transition-all border border-neutral-100 dark:border-neutral-700 hover:border-blue-100 dark:hover:border-blue-900/50">
                                    <flux:icon.pencil-square class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center gap-4 max-w-xs mx-auto">
                                    <div class="w-20 h-20 rounded-full bg-neutral-50 dark:bg-neutral-900 flex items-center justify-center">
                                        <flux:icon.building-storefront class="w-10 h-10 text-neutral-200 dark:text-neutral-800" />
                                    </div>
                                    <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">No tenants found</h3>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tenants->hasPages())
            <div class="px-8 py-6 bg-neutral-50/50 dark:bg-neutral-800/50 border-t border-neutral-100 dark:border-neutral-800">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Styling Improvement -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xl animate-in fade-in duration-300">
            <div class="bg-white dark:bg-neutral-900 rounded-[3rem] shadow-2xl w-full max-w-2xl overflow-hidden animate-in zoom-in-95 duration-300 border border-neutral-200 dark:border-neutral-800">
                <div class="p-10 border-b border-neutral-100 dark:border-neutral-800 flex items-center justify-between bg-neutral-50/50 dark:bg-neutral-950/50">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center shadow-xl shadow-blue-500/20">
                            <flux:icon.building-storefront class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">
                                {{ $editingTenantId ? 'Update Identity' : 'New Onboarding' }}
                            </h3>
                            <p class="text-neutral-500 font-medium text-sm">Configure restaurant profile and access</p>
                        </div>
                    </div>
                    <button wire:click="$set('showModal', false)" class="p-3 rounded-full hover:bg-neutral-200 dark:hover:bg-neutral-800 transition-colors">
                        <flux:icon.x-mark class="w-6 h-6 text-neutral-400" />
                    </button>
                </div>

                <form wire:submit="save">
                    <div class="p-10 space-y-10 max-h-[65vh] overflow-y-auto scrollbar-hide">
                        <div class="space-y-6">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Restaurant Identity</h4>
                            <div class="grid gap-6">
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                        <flux:icon.pencil-square class="w-5 h-5" />
                                    </div>
                                    <input type="text" wire:model.blur="name" 
                                        class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-lg" 
                                        placeholder="Official Restaurant Name">
                                </div>
                                @error('name') <span class="text-xs text-red-500 font-bold ml-4">{{ $message }}</span> @enderror
                                
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                            <flux:icon.globe-alt class="w-5 h-5" />
                                        </div>
                                        <input type="text" wire:model="slug" 
                                            class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                            placeholder="URL Slug">
                                    </div>
                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                            <flux:icon.link class="w-5 h-5" />
                                        </div>
                                        <input type="text" wire:model="domain" 
                                            class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                            placeholder="Custom Domain">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em]">Contact Details</h4>
                            <div class="grid gap-6">
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                        <flux:icon.map-pin class="w-5 h-5" />
                                    </div>
                                    <input type="text" wire:model="address" 
                                        class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                        placeholder="Business Address">
                                </div>
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                        <flux:icon.phone class="w-5 h-5" />
                                    </div>
                                    <input type="text" wire:model="phone" 
                                        class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                        placeholder="Primary Phone Number">
                                </div>
                            </div>
                        </div>

                        @if(!$editingTenantId)
                            <div class="pt-10 border-t border-neutral-100 dark:border-neutral-800 space-y-6">
                                <h4 class="text-xs font-black text-blue-600 uppercase tracking-[0.2em]">Initial Admin Credentials</h4>
                                <div class="space-y-6">
                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                            <flux:icon.user class="w-5 h-5" />
                                        </div>
                                        <input type="text" wire:model="admin_name" 
                                            class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                            placeholder="Admin Full Name">
                                    </div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="relative group">
                                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                                <flux:icon.envelope class="w-5 h-5" />
                                            </div>
                                            <input type="email" wire:model="admin_email" 
                                                class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                                placeholder="Admin Email">
                                        </div>
                                        <div class="relative group">
                                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                                <flux:icon.key class="w-5 h-5" />
                                            </div>
                                            <input type="text" wire:model="admin_password" 
                                                class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                                                placeholder="Secure Password">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="p-10 border-t border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex gap-4">
                        <button type="button" wire:click="$set('showModal', false)" class="flex-1 py-5 rounded-[2rem] font-black text-neutral-500 uppercase tracking-widest text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-all">Dismiss</button>
                        <button type="submit" class="flex-[2] py-5 rounded-[2rem] bg-blue-600 hover:bg-blue-500 text-white font-black shadow-2xl shadow-blue-500/20 transition-all transform active:scale-95 uppercase tracking-widest text-xs flex items-center justify-center gap-2 group">
                            <flux:icon.check-circle class="w-5 h-5 group-hover:scale-110 transition-transform" />
                            {{ $editingTenantId ? 'Save Restaurant Profile' : 'Confirm & Launch Tenant' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>