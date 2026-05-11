<?php

namespace App\Livewire\Landlord;

use App\Models\AuditLog;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Audit Logs')]
#[Lazy]
class AuditLogs extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $action = null;
    public ?string $date_from = null;
    public ?string $date_to = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAction(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'action', 'date_from', 'date_to']);
        $this->resetPage();
    }

    public function getLogsProperty()
    {
        $query = AuditLog::query()
            ->with([
                'actor:id,name,email',
                'tenant:id,name,slug',
            ])
            ->when($this->action, function ($q) {
                $q->where('action', $this->action);
            })
            ->when($this->date_from, function ($q) {
                $q->where('created_at', '>=', $this->date_from . ' 00:00:00');
            })
            ->when($this->date_to, function ($q) {
                $q->where('created_at', '<=', $this->date_to . ' 23:59:59');
            })
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('action', 'like', $term)
                        ->orWhere('ip', 'like', $term)
                        ->orWhere('subject_type', 'like', $term)
                        ->orWhereRaw('CAST(subject_id AS CHAR) like ?', [$term])
                        ->orWhereHas('actor', function ($a) use ($term) {
                            $a->where('name', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        })
                        ->orWhereHas('tenant', function ($t) use ($term) {
                            $t->where('name', 'like', $term)
                                ->orWhere('slug', 'like', $term);
                        });
                });
            })
            ->latest();

        return $query->paginate(25);
    }

    public function getActionOptionsProperty(): array
    {
        return AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->limit(50)
            ->pluck('action')
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.landlord.audit-logs', [
            'logs' => $this->logs,
            'actionOptions' => $this->actionOptions,
        ]);
    }
}

