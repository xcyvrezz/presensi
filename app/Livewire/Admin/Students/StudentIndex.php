<?php

namespace App\Livewire\Admin\Students;

use App\Models\Student;
use App\Models\Classes;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Manajemen Siswa')]
class StudentIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $classFilter = '';
    public $departmentFilter = '';
    public $statusFilter = '';
    public $genderFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'classFilter' => ['except' => ''],
        'departmentFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'genderFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingClassFilter()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingGenderFilter()
    {
        $this->resetPage();
    }

    public function toggleStatus($studentId)
    {
        $student = Student::findOrFail($studentId);
        $student->update(['is_active' => !$student->is_active]);

        session()->flash('success', 'Status siswa berhasil diubah.');
    }

    public function toggleNfc($studentId)
    {
        $student = Student::findOrFail($studentId);
        $student->update(['nfc_enabled' => !$student->nfc_enabled]);

        session()->flash('success', 'Status NFC siswa berhasil diubah.');
    }

    public function deleteStudent($studentId)
    {
        $student = Student::findOrFail($studentId);

        // Check if student has attendance records
        if ($student->attendances()->exists()) {
            session()->flash('error', 'Tidak dapat menghapus siswa yang sudah memiliki riwayat absensi.');
            return;
        }

        // Delete associated user if exists
        if ($student->user_id) {
            $student->user->delete();
        }

        $student->delete();

        session()->flash('success', 'Siswa berhasil dihapus.');
    }

    public function render()
    {
        $students = Student::query()
            ->with(['class.department', 'user'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('nis', 'like', '%' . $this->search . '%')
                      ->orWhere('nisn', 'like', '%' . $this->search . '%')
                      ->orWhere('card_uid', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->classFilter, function ($query) {
                $query->where('class_id', $this->classFilter);
            })
            ->when($this->departmentFilter, function ($query) {
                $query->whereHas('class', function ($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->when($this->genderFilter, function ($query) {
                $query->where('gender', $this->genderFilter);
            })
            ->orderBy('full_name')
            ->paginate(15);

        $classes = Classes::with('department')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('livewire.admin.students.student-index', [
            'students' => $students,
            'classes' => $classes,
            'departments' => $departments,
        ]);
    }
}
