<?php

namespace App\Livewire\Settings;

use App\Models\QuickNote;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Quick Notes')]
class QuickNotes extends Component
{
    public array $quick_notes = [];

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;

        $this->quick_notes = $tenant->quickNotes()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'text' => $n->text,
                'sort_order' => (int) $n->sort_order,
                'is_active' => (bool) $n->is_active,
            ])
            ->values()
            ->all();
    }

    public function addQuickNote(): void
    {
        $nextSort = 0;
        foreach ($this->quick_notes as $row) {
            $nextSort = max($nextSort, (int) ($row['sort_order'] ?? 0));
        }

        $this->quick_notes[] = [
            'text' => '',
            'sort_order' => $nextSort + 1,
            'is_active' => true,
        ];
    }

    public function removeQuickNote(int $index): void
    {
        unset($this->quick_notes[$index]);
        $this->quick_notes = array_values($this->quick_notes);
    }

    public function save(): void
    {
        $tenant = auth()->user()->tenant;

        $this->validate([
            'quick_notes' => 'array',
            'quick_notes.*.text' => 'required|string|max:80',
            'quick_notes.*.sort_order' => 'required|integer|min:0|max:1000000',
            'quick_notes.*.is_active' => 'required|boolean',
        ]);

        $keptIds = [];
        foreach ($this->quick_notes as $row) {
            $text = trim((string) ($row['text'] ?? ''));
            if (!filled($text)) {
                continue;
            }

            $payload = [
                'tenant_id' => $tenant->id,
                'text' => $text,
                'sort_order' => (int) ($row['sort_order'] ?? 0),
                'is_active' => (bool) ($row['is_active'] ?? true),
            ];

            if (!empty($row['id'])) {
                $note = $tenant->quickNotes()->find($row['id']);
                if ($note) {
                    $note->update($payload);
                    $keptIds[] = $note->id;
                    continue;
                }
            }

            $note = $tenant->quickNotes()->create($payload);
            $keptIds[] = $note->id;
        }

        $tenant->quickNotes()->whereNotIn('id', $keptIds)->delete();

        $this->mount();
        $this->dispatch('notify', message: 'Quick notes updated successfully.', type: 'success');
    }

    public function render()
    {
        return view('livewire.settings.quick-notes')->layout('layouts.app');
    }
}

