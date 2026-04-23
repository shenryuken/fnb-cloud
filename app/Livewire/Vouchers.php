<?php

namespace App\Livewire;

use App\Models\Voucher;
use App\Models\Product;
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
    public ?int $per_customer_limit = null;
    public bool $first_time_only = false;
    public bool $can_combine_with_manual_discount = false;
    public bool $can_combine_with_points = false;
    public ?int $free_product_id = null;
    public int $free_quantity = 1;
    public ?float $issue_on_min_spend = null;
    public ?int $issue_expires_in_days = null;

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
        'per_customer_limit' => 'nullable|integer|min:1',
        'first_time_only' => 'boolean',
        'can_combine_with_manual_discount' => 'boolean',
        'can_combine_with_points' => 'boolean',
        'free_product_id' => 'nullable|integer|exists:products,id',
        'free_quantity' => 'required|integer|min:1',
        'issue_on_min_spend' => 'nullable|numeric|min:0.01',
        'issue_expires_in_days' => 'nullable|integer|min:1|max:3650',
    ];

    public function create(): void
    {
        $this->reset(['code', 'name', 'type', 'value', 'is_active', 'starts_at', 'ends_at', 'usage_limit', 'per_customer_limit', 'first_time_only', 'can_combine_with_manual_discount', 'can_combine_with_points', 'free_product_id', 'free_quantity', 'issue_on_min_spend', 'issue_expires_in_days', 'editing']);
        $this->type = 'percent';
        $this->is_active = true;
        $this->free_quantity = 1;
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
        $this->per_customer_limit = $voucher->per_customer_limit;
        $this->first_time_only = (bool) ($voucher->first_time_only ?? false);
        $this->can_combine_with_manual_discount = (bool) ($voucher->can_combine_with_manual_discount ?? false);
        $this->can_combine_with_points = (bool) ($voucher->can_combine_with_points ?? false);
        $this->free_product_id = $voucher->free_product_id;
        $this->free_quantity = max(1, (int) ($voucher->free_quantity ?? 1));
        $this->issue_on_min_spend = $voucher->issue_on_min_spend !== null ? (float) $voucher->issue_on_min_spend : null;
        $this->issue_expires_in_days = $voucher->issue_expires_in_days;
        $this->isCreating = false;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['code'] = strtoupper(trim((string) $validated['code']));
        $validated['name'] = filled($validated['name'] ?? null) ? trim((string) $validated['name']) : null;
        $validated['usage_limit'] = filled($validated['usage_limit'] ?? null) ? (int) $validated['usage_limit'] : null;
        $validated['per_customer_limit'] = filled($validated['per_customer_limit'] ?? null) ? (int) $validated['per_customer_limit'] : null;
        $validated['free_product_id'] = filled($validated['free_product_id'] ?? null) ? (int) $validated['free_product_id'] : null;
        $validated['free_quantity'] = max(1, (int) ($validated['free_quantity'] ?? 1));
        $validated['issue_on_min_spend'] = filled($validated['issue_on_min_spend'] ?? null) ? round((float) $validated['issue_on_min_spend'], 2) : null;
        $validated['issue_expires_in_days'] = filled($validated['issue_expires_in_days'] ?? null) ? (int) $validated['issue_expires_in_days'] : null;
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

        $this->reset(['code', 'name', 'type', 'value', 'is_active', 'starts_at', 'ends_at', 'usage_limit', 'per_customer_limit', 'first_time_only', 'can_combine_with_manual_discount', 'can_combine_with_points', 'free_product_id', 'free_quantity', 'issue_on_min_spend', 'issue_expires_in_days', 'editing', 'isCreating']);
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
            'products' => Product::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
