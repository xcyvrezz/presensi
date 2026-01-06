<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

#[Layout('layouts.admin')]
#[Title('Edit Pengguna')]
class UserEdit extends Component
{
    public User $user;
    public string $name = '';
    public string $email = '';
    public ?string $password = null;
    public ?string $password_confirmation = null;
    public int $role_id = 0;
    public bool $is_active = true;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
        $this->is_active = $user->is_active;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        'password.min' => 'Password minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
        'role_id.required' => 'Role wajib dipilih.',
    ];

    public function save()
    {
        $validated = $this->validate();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'is_active' => $validated['is_active'],
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $this->user->update($data);

        session()->flash('success', 'User berhasil diperbarui.');

        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        $roles = Role::all();

        return view('livewire.admin.users.user-edit', [
            'roles' => $roles,
        ]);
    }
}
