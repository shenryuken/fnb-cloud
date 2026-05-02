<?php

namespace App\Livewire\Settings;

use App\Models\User;
use App\Models\Role;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

#[Title('Team Members')]
class Users extends Component
{
    public string $search = '';

    // Modal state
    public bool $showModal = false;
    public ?int $editingUserId = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $selectedRoles = [];

    // Reset password modal
    public bool $showResetPasswordModal = false;
    public ?int $resetPasswordUserId = null;
    public string $newPassword = '';
    public string $newPassword_confirmation = '';
    
    // Manager PIN modal
    public bool $showPinModal = false;
    public ?int $pinUserId = null;
    public string $newPin = '';
    public string $newPin_confirmation = '';

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email' . ($this->editingUserId ? ',' . $this->editingUserId : ''),
            'selectedRoles' => 'array',
            'selectedRoles.*' => 'exists:roles,id',
        ];

        // Password required only when creating new user
        if (!$this->editingUserId) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        return $rules;
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when($this->search, fn($q) => $q->where(fn($q2) => 
                $q2->where('name', 'like', "%{$this->search}%")
                   ->orWhere('email', 'like', "%{$this->search}%")
            ))
            ->with('roles')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function roles()
    {
        return Role::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();
    }

    public function create(): void
    {
        $this->reset(['editingUserId', 'name', 'email', 'password', 'password_confirmation', 'selectedRoles']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $user = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = $user->roles->pluck('id')->toArray();

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'tenant_id' => auth()->user()->tenant_id,
        ];

        if ($this->editingUserId) {
            $user = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($this->editingUserId);
            $user->update($data);
            $message = 'Team member updated successfully.';
        } else {
            $data['password'] = Hash::make($this->password);
            $user = User::create($data);
            $message = 'Team member created successfully.';
        }

        // Sync roles
        $user->roles()->sync($this->selectedRoles);

        $this->showModal = false;
        $this->reset(['editingUserId', 'name', 'email', 'password', 'password_confirmation', 'selectedRoles']);
        unset($this->users);

        session()->flash('status', $message);
    }

    public function delete(int $id): void
    {
        $user = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        $user->roles()->detach();
        $user->delete();

        unset($this->users);
        session()->flash('status', 'Team member deleted successfully.');
    }

    public function openResetPassword(int $id): void
    {
        $this->resetPasswordUserId = $id;
        $this->newPassword = '';
        $this->newPassword_confirmation = '';
        $this->showResetPasswordModal = true;
    }

    public function resetPassword(): void
    {
        $this->validate([
            'newPassword' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($this->resetPasswordUserId);
        $user->update(['password' => Hash::make($this->newPassword)]);

        $this->showResetPasswordModal = false;
        $this->reset(['resetPasswordUserId', 'newPassword', 'newPassword_confirmation']);

        session()->flash('status', 'Password reset successfully.');
    }
    
    public function openSetPin(int $id): void
    {
        $user = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        
        // Only allow setting PIN for users with manager/admin roles
        if (!$user->hasRole('admin') && !$user->hasRole('manager') && !$user->hasRole('super-admin')) {
            session()->flash('error', 'Only managers and admins can have a PIN.');
            return;
        }
        
        $this->pinUserId = $id;
        $this->newPin = '';
        $this->newPin_confirmation = '';
        $this->showPinModal = true;
    }
    
    public function setPin(): void
    {
        $this->validate([
            'newPin' => 'required|numeric|digits:4|confirmed',
        ], [
            'newPin.required' => 'PIN is required.',
            'newPin.numeric' => 'PIN must contain only numbers.',
            'newPin.digits' => 'PIN must be exactly 4 digits.',
            'newPin.confirmed' => 'PINs do not match.',
        ]);

        $user = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($this->pinUserId);
        $user->update(['pin' => $this->newPin]);

        $this->showPinModal = false;
        $this->reset(['pinUserId', 'newPin', 'newPin_confirmation']);

        session()->flash('status', "Manager PIN set successfully for {$user->name}.");
    }

    public function render()
    {
        return view('livewire.settings.users');
    }
}
