<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Customers')]
#[Lazy]
class Customers extends Component
{
    use WithPagination;

    public string $name = '';
    public string $email = '';
    public string $mobile = '';
    public int $points_balance = 0;

    public ?Customer $editing = null;
    public bool $isCreating = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'mobile' => 'nullable|string|max:50',
        'points_balance' => 'required|integer|min:0',
    ];

    public function create(): void
    {
        $this->reset(['name', 'email', 'mobile', 'points_balance', 'editing']);
        $this->points_balance = 0;
        $this->isCreating = true;
    }

    public function edit(Customer $customer): void
    {
        $this->editing = $customer;
        $this->name = $customer->name;
        $this->email = (string) ($customer->email ?? '');
        $this->mobile = (string) ($customer->mobile ?? '');
        $this->points_balance = (int) ($customer->points_balance ?? 0);
        $this->isCreating = false;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['name'] = trim((string) $validated['name']);
        $validated['email'] = filled($validated['email'] ?? null) ? strtolower(trim((string) $validated['email'])) : null;
        $validated['mobile'] = filled($validated['mobile'] ?? null) ? $this->normalizeMobile((string) $validated['mobile']) : null;

        if (!filled($validated['email']) && !filled($validated['mobile'])) {
            $this->addError('email', 'Email or mobile is required.');
            $this->addError('mobile', 'Email or mobile is required.');
            return;
        }

        $tenantId = auth()->user()->tenant_id;

        if (filled($validated['email'])) {
            $q = Customer::where('tenant_id', $tenantId)->where('email', $validated['email']);
            if ($this->editing) {
                $q->where('id', '!=', $this->editing->id);
            }
            if ($q->exists()) {
                $this->addError('email', 'Email already exists.');
                return;
            }
        }

        if (filled($validated['mobile'])) {
            $q = Customer::where('tenant_id', $tenantId)->where('mobile', $validated['mobile']);
            if ($this->editing) {
                $q->where('id', '!=', $this->editing->id);
            }
            if ($q->exists()) {
                $this->addError('mobile', 'Mobile already exists.');
                return;
            }
        }

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            Customer::create($validated);
        }

        $this->reset(['name', 'email', 'mobile', 'points_balance', 'editing', 'isCreating']);
        $this->dispatch('notify', message: 'Customer saved.', type: 'success');
    }

    private function normalizeMobile(string $mobile): string
    {
        $mobile = trim($mobile);
        $mobile = str_replace([' ', '-', '(', ')'], '', $mobile);
        return $mobile;
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
        $this->dispatch('notify', message: 'Customer deleted.', type: 'success');
    }

    public function render()
    {
        return view('livewire.customers', [
            'customers' => Customer::orderByDesc('id')->paginate(10),
        ])->layout('layouts.app');
    }
}
