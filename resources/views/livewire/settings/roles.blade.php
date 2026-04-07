<div class="flex flex-col gap-8 p-4 md:p-10 max-w-6xl mx-auto font-sans">
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <h2 class="text-4xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">Access Control</h2>
            <p class="text-neutral-500 font-medium">Manage user roles and their platform permissions</p>
        </div>
        <button wire:click="create" class="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-[1.5rem] shadow-xl shadow-blue-500/20 transition-all transform active:scale-95 group">
            <flux:icon.plus class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" />
            <span class="text-sm font-black uppercase tracking-widest">Create New Role</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
            <div class="bg-white dark:bg-neutral-900 rounded-[2.5rem] border border-neutral-200 dark:border-neutral-800 shadow-xl overflow-hidden group">
                <div class="p-8 border-b border-neutral-100 dark:border-neutral-800 flex items-center justify-between bg-neutral-50/30 dark:bg-neutral-900/50">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                            <flux:icon.user-group class="w-6 h-6 text-blue-600" />
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">{{ $role->name }}</h3>
                            <p class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">{{ $role->slug }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="edit({{ $role->id }})" class="p-2 rounded-xl hover:bg-white dark:hover:bg-neutral-800 text-neutral-400 hover:text-blue-600 transition-all border border-transparent hover:border-blue-100 dark:hover:border-blue-900/50 shadow-sm">
                            <flux:icon.pencil-square class="w-4 h-4" />
                        </button>
                        <button wire:click="delete({{ $role->id }})" wire:confirm="Are you sure you want to delete this role?" class="p-2 rounded-xl hover:bg-white dark:hover:bg-neutral-800 text-neutral-400 hover:text-red-600 transition-all border border-transparent hover:border-red-100 dark:hover:border-red-900/50 shadow-sm">
                            <flux:icon.trash class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                <div class="p-8 space-y-4">
                    <h4 class="text-[10px] font-black text-neutral-400 uppercase tracking-[0.2em]">Active Permissions</h4>
                    <div class="flex flex-wrap gap-2">
                        @forelse($role->permissions as $permission)
                            <span class="px-3 py-1.5 rounded-xl bg-neutral-50 dark:bg-neutral-800 border border-neutral-100 dark:border-neutral-700 text-[10px] font-black text-neutral-600 dark:text-neutral-400 uppercase tracking-widest">
                                {{ $permission->name }}
                            </span>
                        @empty
                            <p class="text-xs font-medium text-neutral-400 italic px-2">No permissions assigned yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Role Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xl animate-in fade-in duration-300">
            <div class="bg-white dark:bg-neutral-900 rounded-[3rem] shadow-2xl w-full max-w-2xl overflow-hidden animate-in zoom-in-95 duration-300 border border-neutral-200 dark:border-neutral-800">
                <div class="p-10 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center shadow-xl shadow-blue-500/20">
                            <flux:icon.shield-check class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-3xl font-black text-neutral-800 dark:text-neutral-100 tracking-tight">
                                {{ $editingRoleId ? 'Update Role' : 'New Role' }}
                            </h3>
                            <p class="text-neutral-500 font-medium text-sm">Configure permissions for this role</p>
                        </div>
                    </div>
                    <button wire:click="$set('showModal', false)" class="p-3 rounded-full hover:bg-neutral-200 dark:hover:bg-neutral-800 transition-colors">
                        <flux:icon.x-mark class="w-6 h-6 text-neutral-400" />
                    </button>
                </div>

                <form wire:submit="save">
                    <div class="p-10 space-y-10 max-h-[60vh] overflow-y-auto scrollbar-hide">
                        <div class="space-y-6">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2">Identity</h4>
                            <div class="relative group">
                                <div class="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-blue-500 transition-colors">
                                    <flux:icon.pencil-square class="w-5 h-5" />
                                </div>
                                <input type="text" wire:model="name" 
                                    class="w-full rounded-[1.5rem] border-neutral-100 dark:bg-neutral-800 dark:border-neutral-800 p-5 pl-14 font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-lg" 
                                    placeholder="Role Name (e.g. Head Chef)">
                            </div>
                            @error('name') <span class="text-red-500 text-xs font-bold ml-4">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-6">
                            <h4 class="text-xs font-black text-neutral-400 uppercase tracking-[0.2em] px-2">Module Permissions</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($permissions as $permission)
                                    <label class="group flex items-center gap-4 p-5 rounded-[1.5rem] border-2 cursor-pointer transition-all duration-300
                                        {{ in_array($permission->id, $selectedPermissions) ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 shadow-lg' : 'border-neutral-50 dark:border-neutral-800 hover:border-neutral-100 dark:hover:border-neutral-700' }}">
                                        <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->id }}" class="w-6 h-6 rounded-lg border-2 border-neutral-200 dark:border-neutral-700 text-blue-600 focus:ring-4 focus:ring-blue-500/10 transition-all">
                                        <div class="flex-1">
                                            <span class="block font-black text-sm text-neutral-800 dark:text-neutral-200 tracking-tight">{{ $permission->name }}</span>
                                            <span class="text-[9px] text-neutral-400 uppercase font-black tracking-widest">{{ $permission->slug }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="p-10 border-t border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-950/50 flex gap-4">
                        <button type="button" wire:click="$set('showModal', false)" class="flex-1 py-5 rounded-[2rem] font-black text-neutral-500 uppercase tracking-widest text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-all">Dismiss</button>
                        <button type="submit" class="flex-[2] py-5 rounded-[2rem] bg-blue-600 hover:bg-blue-500 text-white font-black shadow-2xl shadow-blue-500/20 transition-all transform active:scale-95 uppercase tracking-widest text-xs flex items-center justify-center gap-2 group">
                            <flux:icon.check-circle class="w-5 h-5 group-hover:scale-110 transition-transform" />
                            {{ $editingRoleId ? 'Update Role Access' : 'Create Access Role' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
