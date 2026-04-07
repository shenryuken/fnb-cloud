<?php

namespace App\Livewire\Landlord;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manage Tenants')]
#[Lazy]
class Tenants extends Component
{
    use WithPagination;

    public $search = '';
    
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
    ];

    public function updatedName($value)
    {
        if (!$this->editingTenantId) {
            $this->slug = Str::slug($value);
        }
    }

    public function create()
    {
        $this->reset(['editingTenantId', 'name', 'slug', 'domain', 'address', 'phone', 'is_active', 'admin_name', 'admin_email', 'admin_password']);
        $this->showModal = true;
    }

    public function edit(Tenant $tenant)
    {
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
        $this->showModal = true;
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

        if ($this->editingTenantId) {
            $tenant = Tenant::find($this->editingTenantId);
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
                'is_active' => $this->is_active,
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
                'is_active' => $this->is_active,
            ]);

            // Create the first admin user for this tenant
            User::create([
                'tenant_id' => $tenant->id,
                'name' => $this->admin_name,
                'email' => $this->admin_email,
                'password' => Hash::make($this->admin_password),
                'api_token' => Str::random(80),
            ]);
        }

        $this->showModal = false;
        $this->dispatch('notify', message: 'Tenant saved successfully.', type: 'success');
    }

    public function toggleStatus(Tenant $tenant)
    {
        $tenant->update(['is_active' => !$tenant->is_active]);
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
