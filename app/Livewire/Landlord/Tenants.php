<?php

namespace App\Livewire\Landlord;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manage Tenants')]
class Tenants extends Component
{
    use WithPagination;

    public $search = '';
    public ?int $editTenantId = null;
    public bool $createTenant = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'editTenantId' => ['except' => null, 'as' => 'edit'],
        'createTenant' => ['except' => false, 'as' => 'create'],
    ];
    
    // Create/Edit Tenant state
    public $showModal = false;
    public $editingTenantId = null;
    public $name = '';
    public $slug = '';
    public $domain = '';
    public $address = '';
    public $phone = '';
    public $logo_url = '';
    public $receipt_email = '';
    public $receipt_header = '';
    public $receipt_footer = '';
    public $is_active = true;
    public $status = 'active';
    public $plan = 'standard';
    public $trial_ends_at = null;
    public $suspended_reason = '';
    
    // Admin user state (for new tenants)
    public $admin_name = '';
    public $admin_email = '';
    public $admin_password = 'password';

    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|unique:tenants,slug',
        'domain' => 'nullable|string|unique:tenants,domain',
        'address' => 'nullable|string',
        'phone' => 'nullable|string',
        'is_active' => 'boolean',
        'status' => 'required|in:active,trial,suspended',
        'plan' => 'required|string|max:50',
        'trial_ends_at' => 'nullable|date',
        'suspended_reason' => 'nullable|string|max:255',
    ];

    public function mount(): void
    {
        if ($this->editTenantId) {
            $this->edit($this->editTenantId);
            return;
        }

        if ($this->createTenant) {
            $this->create();
        }
    }

    public function updatedName($value)
    {
        if (!$this->editingTenantId) {
            $this->slug = Str::slug($value);
        }
    }

    public function create()
    {
        $this->editTenantId = null;
        $this->createTenant = true;
        $this->reset([
            'editingTenantId',
            'name',
            'slug',
            'domain',
            'address',
            'phone',
            'is_active',
            'status',
            'plan',
            'trial_ends_at',
            'suspended_reason',
            'admin_name',
            'admin_email',
            'admin_password',
        ]);
        $this->is_active = true;
        $this->status = 'active';
        $this->plan = 'standard';
        $this->showModal = true;
    }

    public function edit(int $tenantId)
    {
        try {
            $tenant = Tenant::find($tenantId);

            if (!$tenant) {
                $this->dispatch('notify', message: 'Tenant not found.', type: 'error');
                return;
            }

            $this->editTenantId = $tenant->id;
            $this->editingTenantId = $tenant->id;
            $this->name = $tenant->name;
            $this->slug = $tenant->slug;
            $this->domain = $tenant->domain;
            $this->address = $tenant->address;
            $this->phone = $tenant->phone;
            $this->logo_url = $tenant->logo_url;
            $this->receipt_email = $tenant->receipt_email;
            $this->receipt_header = $tenant->receipt_header;
            $this->receipt_footer = $tenant->receipt_footer;
            $this->is_active = $tenant->is_active;
            $this->status = $tenant->status ?? ($tenant->is_active ? 'active' : 'suspended');
            $this->plan = $tenant->plan ?? 'standard';
            $this->trial_ends_at = optional($tenant->trial_ends_at)->format('Y-m-d\TH:i');
            $this->suspended_reason = $tenant->suspended_reason ?? '';
            $this->showModal = true;
        } catch (\Throwable $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingTenantId = null;
        $this->editTenantId = null;
        $this->createTenant = false;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editingTenantId) {
            $rules['slug'] = 'required|string|unique:tenants,slug,' . $this->editingTenantId;
            $rules['domain'] = 'nullable|string|unique:tenants,domain,' . $this->editingTenantId;
        } else {
            $rules['admin_name'] = 'required|string|max:255';
            $rules['admin_email'] = 'required|email|unique:users,email';
        }

        $this->validate($rules);

        $status = $this->status ?: 'active';
        $isActive = $status !== 'suspended';
        $trialEndsAt = $this->trial_ends_at ?: null;
        $suspendedAt = $status === 'suspended' ? now() : null;
        $suspendedReason = $status === 'suspended'
            ? ($this->suspended_reason ?: 'Suspended by landlord')
            : null;

        if ($this->editingTenantId) {
            $tenant = Tenant::findOrFail($this->editingTenantId);
            $before = $tenant->only([
                'name',
                'slug',
                'domain',
                'address',
                'phone',
                'logo_url',
                'receipt_email',
                'receipt_header',
                'receipt_footer',
                'is_active',
                'status',
                'plan',
                'trial_ends_at',
                'suspended_at',
                'suspended_reason',
            ]);
            $tenant->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'domain' => $this->domain,
                'address' => $this->address,
                'phone' => $this->phone,
                'logo_url' => $this->logo_url,
                'receipt_email' => $this->receipt_email,
                'receipt_header' => $this->receipt_header,
                'receipt_footer' => $this->receipt_footer,
                'is_active' => $isActive,
                'status' => $status,
                'plan' => $this->plan,
                'trial_ends_at' => $trialEndsAt,
                'suspended_at' => $suspendedAt,
                'suspended_reason' => $suspendedReason,
            ]);

            AuditLog::create([
                'tenant_id' => null,
                'actor_user_id' => auth()->id(),
                'action' => 'landlord.tenant.updated',
                'subject_type' => Tenant::class,
                'subject_id' => $tenant->id,
                'meta' => [
                    'before' => $before,
                    'after' => $tenant->only(array_keys($before)),
                ],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } else {
            $tenant = Tenant::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'domain' => $this->domain,
                'address' => $this->address,
                'phone' => $this->phone,
                'logo_url' => $this->logo_url,
                'receipt_email' => $this->receipt_email,
                'receipt_header' => $this->receipt_header,
                'receipt_footer' => $this->receipt_footer,
                'is_active' => $isActive,
                'status' => $status,
                'plan' => $this->plan,
                'trial_ends_at' => $trialEndsAt,
                'suspended_at' => $suspendedAt,
                'suspended_reason' => $suspendedReason,
            ]);

            // Create the first admin user for this tenant
            User::create([
                'tenant_id' => $tenant->id,
                'name' => $this->admin_name,
                'email' => $this->admin_email,
                'password' => Hash::make($this->admin_password),
                'api_token' => Str::random(80),
            ]);

            AuditLog::create([
                'tenant_id' => null,
                'actor_user_id' => auth()->id(),
                'action' => 'landlord.tenant.created',
                'subject_type' => Tenant::class,
                'subject_id' => $tenant->id,
                'meta' => [
                    'tenant' => $tenant->only([
                        'id',
                        'name',
                        'slug',
                        'domain',
                        'is_active',
                        'status',
                        'plan',
                        'trial_ends_at',
                        'suspended_at',
                        'suspended_reason',
                    ]),
                    'admin_email' => $this->admin_email,
                ],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        $this->showModal = false;
        $this->dispatch('notify', message: 'Tenant saved successfully.', type: 'success');
    }

    public function toggleStatus(int $tenantId)
    {
        try {
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                $this->dispatch('notify', message: 'Tenant not found.', type: 'error');
                return;
            }

            $before = $tenant->only(['is_active', 'status', 'suspended_at', 'suspended_reason']);

            if (($tenant->status ?? 'active') === 'suspended' || !$tenant->is_active) {
                $tenant->update([
                    'is_active' => true,
                    'status' => 'active',
                    'suspended_at' => null,
                    'suspended_reason' => null,
                ]);
            } else {
                $tenant->update([
                    'is_active' => false,
                    'status' => 'suspended',
                    'suspended_at' => now(),
                    'suspended_reason' => 'Suspended by landlord',
                ]);
            }

            AuditLog::create([
                'tenant_id' => null,
                'actor_user_id' => auth()->id(),
                'action' => 'landlord.tenant.toggled',
                'subject_type' => Tenant::class,
                'subject_id' => $tenant->id,
                'meta' => [
                    'before' => $before,
                    'after' => $tenant->only(array_keys($before)),
                ],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $tenants = Tenant::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('slug', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.landlord.tenants', [
            'tenants' => $tenants
        ]);
    }
}
