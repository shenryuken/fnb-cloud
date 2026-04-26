<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout>
        <x-slot:slot>
<div class="flex flex-col gap-6 w-full">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <flux:heading size="xl" level="2">Access Control</flux:heading>
            <flux:subheading>
                Manage {{ $isLandlord ? 'landlord' : 'restaurant' }} roles and their permissions.
            </flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:click="seedDefaults" variant="ghost" icon="sparkles" wire:confirm="This will create or update the default roles for your {{ $isLandlord ? 'platform' : 'restaurant' }}. Continue?">
                Seed Defaults
            </flux:button>
            <flux:button wire:click="create" variant="primary" icon="plus">
                New Role
            </flux:button>
        </div>
    </div>

    @if(session('status'))
        <flux:callout variant="success" icon="check-circle">
            {{ session('status') }}
        </flux:callout>
    @endif

    {{-- Default role reference table --}}
    <flux:card class="p-0 overflow-hidden">
        <div class="px-5 py-3 border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
            <flux:text size="sm" class="font-semibold text-zinc-500 uppercase tracking-widest text-xs">
                {{ $isLandlord ? 'Landlord Default Roles' : 'Restaurant Default Roles' }}
            </flux:text>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-100 dark:border-zinc-800">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-zinc-400 uppercase tracking-widest w-40">Role</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-zinc-400 uppercase tracking-widest">Permissions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($defaults as $def)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                        <td class="px-5 py-3">
                            <flux:text class="font-semibold">{{ $def['name'] }}</flux:text>
                            <flux:text size="xs" class="text-zinc-400 font-mono">{{ $def['slug'] }}</flux:text>
                        </td>
                        <td class="px-5 py-3">
                            @if($def['all'] ?? false)
                                <flux:badge color="amber" size="sm">All Permissions</flux:badge>
                            @else
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($def['permissions'] as $slug)
                                        <flux:badge color="zinc" size="sm">{{ $slug }}</flux:badge>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </flux:card>

    {{-- Existing Role Cards --}}
    @if($roles->count())
        <div>
            <flux:heading size="sm" class="mb-3 text-zinc-500 uppercase tracking-widest text-xs font-semibold">Active Roles</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($roles as $role)
                    @php
                        $isDefault = collect($defaults)->contains('slug', $role->slug);
                        $iconMap = [
                            'superadmin'    => 'shield-check',
                            'admin'         => 'cog-6-tooth',
                            'kitchen-staff' => 'fire',
                            'waiter'        => 'clipboard-document-list',
                            'cashier'       => 'banknotes',
                            'staff'         => 'user',
                        ];
                        $icon = $iconMap[$role->slug] ?? 'user-group';
                    @endphp
                    <flux:card class="p-0 overflow-hidden">
                        <div class="flex items-center justify-between p-4 border-b border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-pink-50 dark:bg-pink-900/20 flex items-center justify-center shrink-0">
                                    <flux:icon :icon="$icon" class="w-5 h-5 text-pink-500" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <flux:text class="font-semibold text-sm">{{ $role->name }}</flux:text>
                                        @if($isDefault)
                                            <flux:badge color="zinc" size="sm">Default</flux:badge>
                                        @endif
                                    </div>
                                    <flux:text size="xs" class="text-zinc-400 font-mono">{{ $role->slug }}</flux:text>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $role->id }})" />
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $role->id }})" wire:confirm="Delete the '{{ $role->name }}' role?" class="text-red-400 hover:text-red-600" />
                            </div>
                        </div>
                        <div class="p-4">
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-widest font-semibold mb-2">Permissions</flux:text>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($role->permissions as $permission)
                                    <flux:badge color="zinc" size="sm">{{ $permission->name }}</flux:badge>
                                @empty
                                    <flux:text size="sm" class="text-zinc-400 italic">No permissions assigned.</flux:text>
                                @endforelse
                            </div>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-16 text-center gap-3">
            <div class="w-14 h-14 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                <flux:icon.shield-check class="w-7 h-7 text-zinc-400" />
            </div>
            <flux:heading size="lg">No roles yet</flux:heading>
            <flux:subheading>Click "Seed Defaults" to add the recommended roles, or create a custom one.</flux:subheading>
        </div>
    @endif

    {{-- Role Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <flux:card class="w-full max-w-2xl overflow-hidden p-0">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-pink-500 flex items-center justify-center shrink-0">
                            <flux:icon.shield-check class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ $editingRoleId ? 'Edit Role' : 'New Role' }}</flux:heading>
                            <flux:subheading>Configure permissions for this role.</flux:subheading>
                        </div>
                    </div>
                    <flux:button wire:click="$set('showModal', false)" variant="ghost" icon="x-mark" />
                </div>

                {{-- Modal Body --}}
                <form wire:submit="save">
                    <div class="p-5 flex flex-col gap-5 max-h-[60vh] overflow-y-auto">
                        <flux:field>
                            <flux:label>Role Name</flux:label>
                            <flux:input wire:model="name" placeholder="e.g. Head Chef" />
                            <flux:error name="name" />
                        </flux:field>

                        <div class="flex flex-col gap-3">
                            <flux:text size="xs" class="text-zinc-400 uppercase tracking-widest font-semibold">Module Permissions</flux:text>
                            <div class="grid sm:grid-cols-2 gap-2">
                                @foreach($permissions as $permission)
                                    <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all
                                        {{ in_array($permission->id, $selectedPermissions) ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/10' : 'border-zinc-100 dark:border-zinc-800 hover:border-zinc-200 dark:hover:border-zinc-700' }}">
                                        <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->id }}"
                                            class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-pink-500 focus:ring-pink-500">
                                        <div>
                                            <flux:text class="font-semibold text-sm">{{ $permission->name }}</flux:text>
                                            <flux:text size="xs" class="text-zinc-400 font-mono">{{ $permission->slug }}</flux:text>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex gap-3 p-5 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button type="button" wire:click="$set('showModal', false)" variant="ghost">Cancel</flux:button>
                        <flux:button type="submit" variant="primary" class="flex-1" icon="check-circle">
                            {{ $editingRoleId ? 'Update Role' : 'Create Role' }}
                        </flux:button>
                    </div>
                </form>
            </flux:card>
        </div>
    @endif

</div>
        </x-slot:slot>
    </x-settings.layout>
</section>
