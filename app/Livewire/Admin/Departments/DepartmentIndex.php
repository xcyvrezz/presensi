<?php

namespace App\Livewire\Admin\Departments;

use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Manajemen Jurusan')]
class DepartmentIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function toggleStatus($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $department->update(['is_active' => !$department->is_active]);

        session()->flash('success', 'Status jurusan berhasil diubah.');
    }

    public function deleteDepartment($departmentId)
    {
        $department = Department::findOrFail($departmentId);

        // Check if department has classes
        if ($department->classes()->exists()) {
            session()->flash('error', 'Tidak dapat menghapus jurusan yang masih memiliki kelas.');
            return;
        }

        $department->delete();
        session()->flash('success', 'Jurusan berhasil dihapus.');
    }

    public function render()
    {
        $departments = Department::query()
            ->withCount(['classes', 'students'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('head_teacher', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter == '1');
            })
            ->orderBy('code')
            ->paginate(15);

        return view('livewire.admin.departments.department-index', [
            'departments' => $departments,
        ]);
    }
}
