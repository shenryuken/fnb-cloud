<div class="flex flex-col gap-6 p-4 md:p-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Access Control</flux:heading>
            <flux:subheading>Manage user roles and their platform permissions.</flux:subheading>
        </div>
        <flux:button wire:click="create" variant="primary" icon="plus">
            Create New Role
        </flux:button>
    </div>

    {{-- Role Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
            <flux:card class="p-0 overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center shrink-0">
                            <flux:icon.user-group class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <flux:text class="font-semibold">{{ $role->name }}</flux:text>
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-widest">{{ $role->slug }}</flux:text>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $role->id }})" />
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $role->id }})" wire:confirm="Are you sure you want to delete this role?" class="text-red-500 hover:text-red-600" />
                    </div>
                </div>

                <div class="p-5">
                    <flux:text size="xs" class="text-zinc-400 uppercase tracking-widest font-black mb-3">Active Permissions</flux:text>
                    <div class="flex flex-wrap gap-2">
                        @forelse($role->permissions as $permission)
                            <flux:badge color="zinc" size="sm">{{ $permission->name }}</flux:badge>
                        @empty
                            <flux:text size="sm" class="text-zinc-400 italic">No permissions assigned yet.</flux:text>
                        @endforelse
                    </div>
                </div>
            </flux:card>
        @endforeach
    </div>

    {{-- Role Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <flux:card class="w-full max-w-2xl overflow-hidden p-0">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between p-6 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center shrink-0">
                            <flux:icon.shield-check class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ $editingRoleId ? 'Update Role' : 'New Role' }}</flux:heading>
                            <flux:subheading>Configure permissions for this role.</flux:subheading>
                        </div>
                    </div>
                    <flux:button wire:click="$set('showModal', false)" variant="ghost" icon="x-mark" />
                </div>

                {{-- Modal Body --}}
                <form wire:submit="save">
                    <div class="p-6 flex flex-col gap-6 max-h-[60vh] overflow-y-auto">
                        <flux:field>
                            <flux:label>Role Name</flux:label>
                            <flux:input wire:model="name" placeholder="e.g. Head Chef" />
                            <flux:error name="name" />
                        </flux:field>

                        <div class="flex flex-col gap-3">
                            <flux:heading size="sm" class="text-zinc-400 uppercase tracking-widest text-xs font-black">Module Permissions</flux:heading>
                            <div class="grid sm:grid-cols-2 gap-3">
                                @foreach($permissions as $permission)
                                    <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all
                                        {{ in_array($permission->id, $selectedPermissions) ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/10' : 'border-zinc-100 dark:border-zinc-800 hover:border-zinc-200 dark:hover:border-zinc-700' }}">
                                        <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->id }}"
                                            class="w-5 h-5 rounded border-2 border-zinc-200 dark:border-zinc-700 text-blue-600">
                                        <div>
                                            <flux:text class="font-semibold text-sm">{{ $permission->name }}</flux:text>
                                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-widest">{{ $permission->slug }}</flux:text>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex gap-3 p-6 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button type="button" wire:click="$set('showModal', false)" variant="ghost" class="flex-1">Dismiss</flux:button>
                        <flux:button type="submit" variant="primary" class="flex-[2]" icon="check-circle">
                            {{ $editingRoleId ? 'Update Role' : 'Create Role' }}
                        </flux:button>
                    </div>
                </form>
            </flux:card>
        </div>
    @endif

</div>
