<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout>
        <x-slot:slot>
<div class="flex flex-col gap-6 w-full max-w-2xl">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Quick Notes</flux:heading>
            <flux:subheading>Pre-set labels that staff can tap to add notes to orders.</flux:subheading>
        </div>
        <div class="flex gap-3">
            <flux:button type="button" wire:click="addQuickNote" variant="ghost" icon="plus">Add Note</flux:button>
            <flux:button type="button" wire:click="save" variant="primary" icon="check-circle">Save</flux:button>
        </div>
    </div>

    <flux:card class="flex flex-col gap-3">
        <div class="flex items-center justify-between mb-2">
            <flux:heading size="sm" class="text-zinc-400 uppercase tracking-widest text-xs font-black">Labels</flux:heading>
            <flux:badge color="zinc" size="sm">{{ count($quick_notes) }} items</flux:badge>
        </div>

        @forelse($quick_notes as $i => $note)
            <div class="grid grid-cols-12 gap-3 items-center p-3 rounded-xl border border-zinc-100 dark:border-zinc-800">
                <div class="col-span-1">
                    <flux:input type="number" wire:model.live="quick_notes.{{ $i }}.sort_order" size="sm" class="text-center tabular-nums" />
                </div>
                <div class="col-span-8">
                    <flux:input wire:model.live="quick_notes.{{ $i }}.text" placeholder="e.g. No spicy" size="sm" />
                    <flux:error name="quick_notes.{{ $i }}.text" />
                </div>
                <div class="col-span-2 flex items-center justify-center gap-2">
                    <flux:checkbox wire:model.live="quick_notes.{{ $i }}.is_active" />
                    <flux:text size="xs" class="text-zinc-500 uppercase tracking-widest">On</flux:text>
                </div>
                <div class="col-span-1 flex items-center justify-end">
                    <flux:button type="button" wire:click="removeQuickNote({{ $i }})" size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-600" />
                </div>
            </div>
        @empty
            <div class="py-10 text-center">
                <flux:text class="text-zinc-400 italic">No quick notes yet. Click "Add Note" to get started.</flux:text>
            </div>
        @endforelse
    </flux:card>

</div>
        </x-slot:slot>
    </x-settings.layout>
</section>
