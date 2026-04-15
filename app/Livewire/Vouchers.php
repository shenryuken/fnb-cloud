<?php

namespace App\Livewire;

use App\Models\Voucher;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

#[Title('Vouchers')]
#[Lazy]
class Vouchers extends Component
{
    use WithPagination;

    public string $code = '';
    public string $name = '';
    public string $type = 'percent';
    public float $value = 0;
    public bool $is_active = true;
    public ?string $starts_at = null;
    public ?string $ends_at = null;
    public ?int $usage_limit = null;

    public ?Voucher $editing = null;
    public bool $isCreating = false;

    protected $rules = [
        'code' => 'required|string|max:50',
        'name' => 'nullable|string|max:255',
        'type' => 'required|in:percent,fixed',
        'value' => 'required|numeric|min:0',
        'is_active' => 'boolean',
        'starts_at' => 'nullable|date',
        'ends_at' => 'nullable|date',
        'usage_limit' => 'nullable|integer|min:1',
    ];

    public function create(): void
    {
        $this->reset(['code', 'name', 'type', 'value', 'is_active', 'starts_at', 'ends_at', 'usage_limit', 'editing']);
        $this->type = 'percent';
        $this->is_active = true;
        $this->isCreating = true;
    }

    public function edit(Voucher $voucher): void
    {
        $this->editing = $voucher;
        $this->code = $voucher->code;
        $this->name = (string) ($voucher->name ?? '');
        $this->type = $voucher->type;
        $this->value = (float) $voucher->value;
        $this->is_active = (bool) $voucher->is_active;
        $this->starts_at = $voucher->starts_at?->format('Y-m-d\TH:i');
        $this->ends_at = $voucher->ends_at?->format('Y-m-d\TH:i');
        $this->usage_limit = $voucher->usage_limit;
        $this->isCreating = false;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['code'] = strtoupper(trim((string) $validated['code']));
        $validated['name'] = filled($validated['name'] ?? null) ? trim((string) $validated['name']) : null;
        $validated['usage_limit'] = filled($validated['usage_limit'] ?? null) ? (int) $validated['usage_limit'] : null;
        $validated['starts_at'] = filled($validated['starts_at'] ?? null) ? $validated['starts_at'] : null;
        $validated['ends_at'] = filled($validated['ends_at'] ?? null) ? $validated['ends_at'] : null;

        $tenantId = Auth::user()->tenant_id;

        $existing = Voucher::where('tenant_id', $tenantId)->where('code', $validated['code']);
        if ($this->editing) {
            $existing->where('id', '!=', $this->editing->id);
        }
        if ($existing->exists()) {
            $this->addError('code', 'Code already exists.');
            return;
        }

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            Voucher::create($validated);
        }

        $this->reset(['code', 'name', 'type', 'value', 'is_active', 'starts_at', 'ends_at', 'usage_limit', 'editing', 'isCreating']);
        $this->dispatch('notify', message: 'Voucher saved.', type: 'success');
    }

    public function delete(Voucher $voucher): void
    {
        $voucher->delete();
        $this->dispatch('notify', message: 'Voucher deleted.', type: 'success');
    }

    public function render()
    {
        return view('livewire.vouchers', [
            'vouchers' => Voucher::query()
                ->select(['id', 'code', 'name', 'type', 'value', 'is_active', 'starts_at', 'ends_at', 'usage_limit', 'usage_count', 'created_at'])
                ->orderByDesc('id')
                ->paginate(10),
        ]);
    }
}
