<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Manajemen Pengguna')]
class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public string $statusFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            return;
        }

        $user->update(['is_active' => !$user->is_active]);

        session()->flash('success', $user->is_active ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.');
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            return;
        }

        // Check if user has attendance records
        if ($user->student && $user->student->attendances()->count() > 0) {
            session()->flash('error', 'User tidak dapat dihapus karena memiliki data absensi.');
            return;
        }

        $user->delete();
        session()->flash('success', 'User berhasil dihapus.');
    }

    public function render()
    {
        $users = User::query()
            ->with('role')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->where('role_id', $this->roleFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === '1');
            })
            ->latest()
            ->paginate(15);

        $roles = Role::all();

        return view('livewire.admin.users.user-index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
