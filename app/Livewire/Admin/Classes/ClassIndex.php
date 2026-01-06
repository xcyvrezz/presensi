<?php

namespace App\Livewire\Admin\Classes;

use App\Models\Classes;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Manajemen Kelas')]
class ClassIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $departmentFilter = '';
    public $gradeFilter = '';
    public $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'departmentFilter' => ['except' => ''],
        'gradeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatingGradeFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function toggleStatus($classId)
    {
        $class = Classes::findOrFail($classId);
        $class->update(['is_active' => !$class->is_active]);

        session()->flash('success', 'Status kelas berhasil diubah.');
    }

    public function deleteClass($classId)
    {
        $class = Classes::findOrFail($classId);

        // Check if class has students
        if ($class->students()->exists()) {
            session()->flash('error', 'Tidak dapat menghapus kelas yang masih memiliki siswa.');
            return;
        }

        $class->delete();

        session()->flash('success', 'Kelas berhasil dihapus.');
    }

    public function render()
    {
        $classes = Classes::query()
            ->with(['department', 'waliKelas'])
            ->withCount('students')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->departmentFilter, function ($query) {
                $query->where('department_id', $this->departmentFilter);
            })
            ->when($this->gradeFilter, function ($query) {
                $query->where('grade', $this->gradeFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->orderBy('name')
            ->paginate(15);

        $departments = Department::orderBy('name')->get();

        return view('livewire.admin.classes.class-index', [
            'classes' => $classes,
            'departments' => $departments,
        ]);
    }
}
