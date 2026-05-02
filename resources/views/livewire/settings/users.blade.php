<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout>
        <x-slot:slot>
<div class="flex flex-col gap-6 w-full">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <flux:heading size="xl" level="2">Team Members</flux:heading>
            <flux:subheading>
                Manage your restaurant staff and assign their roles.
            </flux:subheading>
        </div>
        <flux:button wire:click="create" variant="primary" icon="plus">
            Add Member
        </flux:button>
    </div>

    @if(session('status'))
        <flux:callout variant="success" icon="check-circle">
            {{ session('status') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout variant="danger" icon="exclamation-triangle">
            {{ session('error') }}
        </flux:callout>
    @endif

    {{-- Search --}}
    <div class="relative max-w-sm">
        <flux:icon.magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400" />
        <flux:input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search by name or email..." 
            class="pl-9"
        />
    </div>

    {{-- Users Table --}}
    @if($this->users->count())
        <flux:card class="p-0 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-zinc-400 uppercase tracking-widest">Member</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-zinc-400 uppercase tracking-widest hidden sm:table-cell">Roles</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-zinc-400 uppercase tracking-widest hidden md:table-cell">Joined</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-zinc-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($this->users as $user)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center font-bold text-pink-600 shrink-0">
                                        {{ $user->initials() }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <flux:text class="font-semibold">{{ $user->name }}</flux:text>
                                            @if($user->id === auth()->id())
                                                <flux:badge color="emerald" size="sm">You</flux:badge>
                                            @endif
                                        </div>
                                        <flux:text size="xs" class="text-zinc-400">{{ $user->email }}</flux:text>
                                        {{-- Mobile: Show roles inline --}}
                                        <div class="flex flex-wrap gap-1 mt-1 sm:hidden">
                                            @forelse($user->roles as $role)
                                                <flux:badge color="zinc" size="sm">{{ $role->name }}</flux:badge>
                                            @empty
                                                <flux:text size="xs" class="text-zinc-400 italic">No role</flux:text>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 hidden sm:table-cell">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($user->roles as $role)
                                        <flux:badge color="zinc" size="sm">{{ $role->name }}</flux:badge>
                                    @empty
                                        <flux:text size="xs" class="text-zinc-400 italic">No role assigned</flux:text>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-4 hidden md:table-cell">
                                <flux:text size="sm" class="text-zinc-500">{{ $user->created_at->format('M d, Y') }}</flux:text>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $user->id }})" title="Edit" />
                                    <flux:button size="sm" variant="ghost" icon="key" wire:click="openResetPassword({{ $user->id }})" title="Reset Password" />
                                    @if($user->hasRole('admin') || $user->hasRole('manager') || $user->hasRole('super-admin'))
                                        <flux:button size="sm" variant="ghost" icon="shield-check" wire:click="openSetPin({{ $user->id }})" title="Set Manager PIN" class="text-blue-400 hover:text-blue-600" />
                                    @endif
                                    @if($user->id !== auth()->id())
                                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $user->id }})" wire:confirm="Remove '{{ $user->name }}' from your team?" class="text-red-400 hover:text-red-600" title="Delete" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </flux:card>
    @else
        <div class="flex flex-col items-center justify-center py-16 text-center gap-3">
            <div class="w-14 h-14 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                <flux:icon.users class="w-7 h-7 text-zinc-400" />
            </div>
            <flux:heading size="lg">No team members yet</flux:heading>
            <flux:subheading>Add staff members and assign roles to control their access.</flux:subheading>
            <flux:button wire:click="create" variant="primary" icon="plus" class="mt-2">
                Add Your First Member
            </flux:button>
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <flux:card class="w-full max-w-lg overflow-hidden p-0">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-pink-500 flex items-center justify-center shrink-0">
                            <flux:icon.user-plus class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ $editingUserId ? 'Edit Member' : 'Add Team Member' }}</flux:heading>
                            <flux:subheading>{{ $editingUserId ? 'Update their details and roles.' : 'Create credentials and assign roles.' }}</flux:subheading>
                        </div>
                    </div>
                    <flux:button wire:click="$set('showModal', false)" variant="ghost" icon="x-mark" />
                </div>

                {{-- Modal Body --}}
                <form wire:submit="save">
                    <div class="p-5 flex flex-col gap-5 max-h-[60vh] overflow-y-auto">
                        <flux:field>
                            <flux:label>Full Name</flux:label>
                            <flux:input wire:model="name" placeholder="e.g. John Doe" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Email Address</flux:label>
                            <flux:input type="email" wire:model="email" placeholder="e.g. john@restaurant.com" />
                            <flux:error name="email" />
                        </flux:field>

                        @if(!$editingUserId)
                            <div class="grid sm:grid-cols-2 gap-4">
                                <flux:field>
                                    <flux:label>Password</flux:label>
                                    <flux:input type="password" wire:model="password" placeholder="Min 8 characters" />
                                    <flux:error name="password" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Confirm Password</flux:label>
                                    <flux:input type="password" wire:model="password_confirmation" placeholder="Repeat password" />
                                </flux:field>
                            </div>
                        @endif

                        <div class="flex flex-col gap-3">
                            <flux:label>Assign Roles</flux:label>
                            @if($this->roles->count())
                                <div class="grid sm:grid-cols-2 gap-2">
                                    @foreach($this->roles as $role)
                                        <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all
                                            {{ in_array($role->id, $selectedRoles) ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/10' : 'border-zinc-100 dark:border-zinc-800 hover:border-zinc-200 dark:hover:border-zinc-700' }}">
                                            <input type="checkbox" wire:model="selectedRoles" value="{{ $role->id }}"
                                                class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-pink-500 focus:ring-pink-500">
                                            <flux:text class="font-semibold text-sm">{{ $role->name }}</flux:text>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <flux:callout variant="warning" icon="exclamation-triangle">
                                    <p>No roles available. <a href="{{ route('manage.settings.roles') }}" wire:navigate class="underline font-semibold">Create roles first</a> to assign access levels.</p>
                                </flux:callout>
                            @endif
                            <flux:error name="selectedRoles" />
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex gap-3 p-5 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button type="button" wire:click="$set('showModal', false)" variant="ghost">Cancel</flux:button>
                        <flux:button type="submit" variant="primary" class="flex-1" icon="check-circle">
                            {{ $editingUserId ? 'Update Member' : 'Create Member' }}
                        </flux:button>
                    </div>
                </form>
            </flux:card>
        </div>
    @endif

    {{-- Reset Password Modal --}}
    @if($showResetPasswordModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <flux:card class="w-full max-w-md overflow-hidden p-0">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-500 flex items-center justify-center shrink-0">
                            <flux:icon.key class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <flux:heading size="lg">Reset Password</flux:heading>
                            <flux:subheading>Set a new password for this user.</flux:subheading>
                        </div>
                    </div>
                    <flux:button wire:click="$set('showResetPasswordModal', false)" variant="ghost" icon="x-mark" />
                </div>

                {{-- Modal Body --}}
                <form wire:submit="resetPassword">
                    <div class="p-5 flex flex-col gap-4">
                        <flux:field>
                            <flux:label>New Password</flux:label>
                            <flux:input type="password" wire:model="newPassword" placeholder="Min 8 characters" />
                            <flux:error name="newPassword" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Confirm New Password</flux:label>
                            <flux:input type="password" wire:model="newPassword_confirmation" placeholder="Repeat password" />
                        </flux:field>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex gap-3 p-5 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button type="button" wire:click="$set('showResetPasswordModal', false)" variant="ghost">Cancel</flux:button>
                        <flux:button type="submit" variant="primary" class="flex-1" icon="check-circle">
                            Reset Password
                        </flux:button>
                    </div>
                </form>
            </flux:card>
        </div>
    @endif
    
    {{-- Manager PIN Modal --}}
    @if($showPinModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <flux:card class="w-full max-w-md overflow-hidden p-0">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center shrink-0">
                            <flux:icon.shield-check class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <flux:heading size="lg">Set Manager PIN</flux:heading>
                            <flux:subheading>4-digit PIN for authorizing voids.</flux:subheading>
                        </div>
                    </div>
                    <flux:button wire:click="$set('showPinModal', false)" variant="ghost" icon="x-mark" />
                </div>

                {{-- Modal Body --}}
                <form wire:submit="setPin">
                    <div class="p-5 flex flex-col gap-4">
                        <flux:callout variant="info" icon="information-circle">
                            This PIN is used for authorizing void operations and clearing tables with unpaid orders.
                        </flux:callout>
                        
                        <flux:field>
                            <flux:label>4-Digit PIN</flux:label>
                            <flux:input type="password" inputmode="numeric" wire:model="newPin" placeholder="e.g. 1234" maxlength="4" />
                            <flux:error name="newPin" />
                            <flux:text size="xs" class="text-zinc-400 mt-1">Must be exactly 4 numbers</flux:text>
                        </flux:field>

                        <flux:field>
                            <flux:label>Confirm PIN</flux:label>
                            <flux:input type="password" inputmode="numeric" wire:model="newPin_confirmation" placeholder="Repeat PIN" maxlength="4" />
                        </flux:field>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex gap-3 p-5 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button type="button" wire:click="$set('showPinModal', false)" variant="ghost">Cancel</flux:button>
                        <flux:button type="submit" variant="primary" class="flex-1" icon="check-circle">
                            Set PIN
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
