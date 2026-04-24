<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px] shrink-0">
        <flux:navlist aria-label="{{ __('Settings') }}">
            <flux:navlist.group :heading="__('Account')">
                <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
                <flux:navlist.item :href="route('security.edit')" wire:navigate>{{ __('Security') }}</flux:navlist.item>
                <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Restaurant')" class="mt-4">
                <flux:navlist.item :href="route('manage.settings.receipt')" wire:navigate>{{ __('Receipt Settings') }}</flux:navlist.item>
                <flux:navlist.item :href="route('manage.settings.quick_notes')" wire:navigate>{{ __('Quick Notes') }}</flux:navlist.item>
                <flux:navlist.item :href="route('manage.settings.roles')" wire:navigate>{{ __('Roles & Access') }}</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6 min-w-0">
        @if($heading ?? false)
            <flux:heading>{{ $heading }}</flux:heading>
            <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
            <div class="mt-5 w-full max-w-lg">
                {{ $slot }}
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
</div>
