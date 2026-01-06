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
#[Title('Tambah Pengguna')]
class UserCreate extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public int $role_id = 0;
    public bool $is_active = true;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
        'role_id.required' => 'Role wajib dipilih.',
    ];

    public function save()
    {
        $validated = $this->validate();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'is_active' => $validated['is_active'],
        ]);

        session()->flash('success', 'User berhasil ditambahkan.');

        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        $roles = Role::all();

        return view('livewire.admin.users.user-create', [
            'roles' => $roles,
        ]);
    }
}
