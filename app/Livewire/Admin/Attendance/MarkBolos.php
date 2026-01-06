<?php

namespace App\Livewire\Admin\Attendance;

use App\Models\Student;
use App\Models\Classes;
use App\Models\Department;
use App\Services\AttendanceRulesService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Mark Siswa Bolos')]
class MarkBolos extends Component
{
    public $students = [];
    public $departments = [];
    public $classes = [];

    // Form fields
    public $selectedDepartment = '';
    public $selectedClass = '';
    public $selectedStudent = '';
    public $selectedDate;
    public $reason = '';

    // Search & Filter
    public $studentSearch = '';
    public $studentSearchResults = [];
    public $showStudentDropdown = false;
    public $selectedStudentData = null;

    // UI state
    public $statusMessage = '';
    public $statusType = '';
    public $isProcessing = false;

    protected $attendanceRules;

    public function boot(AttendanceRulesService $attendanceRules)
    {
        $this->attendanceRules = $attendanceRules;
    }

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->departments = Department::orderBy('code')->get();
    }

    public function updatedSelectedDepartment($value)
    {
        if ($value) {
            $this->classes = Classes::where('department_id', $value)
                ->orderBy('name')
                ->get();
            $this->selectedClass = '';
            $this->students = [];
            $this->selectedStudent = '';
        } else {
            $this->classes = [];
            $this->students = [];
            $this->selectedClass = '';
            $this->selectedStudent = '';
        }
    }

    public function updatedSelectedClass($value)
    {
        if ($value) {
            $this->students = Student::where('class_id', $value)
                ->where('is_active', true)
                ->orderBy('full_name')
                ->get();
            $this->selectedStudent = '';
            $this->studentSearch = '';
            $this->studentSearchResults = [];
            $this->selectedStudentData = null;
        } else {
            $this->students = [];
            $this->selectedStudent = '';
            $this->studentSearch = '';
            $this->studentSearchResults = [];
            $this->selectedStudentData = null;
        }
    }

    public function updatedStudentSearch($value)
    {
        if ($value && strlen($value) >= 2) {
            $query = Student::where('is_active', true);

            // If class is selected, filter by class
            if ($this->selectedClass) {
                $query->where('class_id', $this->selectedClass);
            }

            // Search by NIS or name
            $this->studentSearchResults = $query->where(function($q) use ($value) {
                $q->where('nis', 'like', '%' . $value . '%')
                  ->orWhere('full_name', 'like', '%' . $value . '%');
            })
            ->with(['class.department'])
            ->limit(20)
            ->get();

            $this->showStudentDropdown = true;
        } else {
            $this->studentSearchResults = [];
            $this->showStudentDropdown = false;
        }
    }

    public function selectStudent($studentId)
    {
        $student = Student::with(['class.department'])->find($studentId);

        if ($student) {
            $this->selectedStudent = $student->id;
            $this->selectedStudentData = $student;
            $this->studentSearch = $student->nis . ' - ' . $student->full_name;
            $this->showStudentDropdown = false;
            $this->studentSearchResults = [];

            // Auto-select department and class if not selected
            if (!$this->selectedDepartment) {
                $this->selectedDepartment = $student->class->department_id;
                $this->updatedSelectedDepartment($this->selectedDepartment);
            }
            if (!$this->selectedClass) {
                $this->selectedClass = $student->class_id;
            }
        }
    }

    public function clearStudentSearch()
    {
        $this->selectedStudent = '';
        $this->selectedStudentData = null;
        $this->studentSearch = '';
        $this->studentSearchResults = [];
        $this->showStudentDropdown = false;
    }

    public function submitMarkBolos()
    {
        $this->validate([
            'selectedStudent' => 'required',
            'selectedDate' => 'required|date',
            'reason' => 'required|min:5',
        ], [
            'selectedStudent.required' => 'Pilih siswa terlebih dahulu',
            'selectedDate.required' => 'Tanggal harus diisi',
            'reason.required' => 'Alasan/keterangan harus diisi',
            'reason.min' => 'Alasan minimal 5 karakter',
        ]);

        $this->isProcessing = true;

        try {
            $result = $this->attendanceRules->markAsBolos(
                $this->selectedStudent,
                Carbon::parse($this->selectedDate),
                $this->reason,
                auth()->id()
            );

            if ($result['success']) {
                $this->statusMessage = $result['message'];
                $this->statusType = 'success';
                $this->resetForm();
            } else {
                $this->statusMessage = $result['message'];
                $this->statusType = 'error';
            }

        } catch (\Exception $e) {
            $this->statusMessage = '❌ Terjadi kesalahan: ' . $e->getMessage();
            $this->statusType = 'error';
        }

        $this->isProcessing = false;

        // Auto-clear message after 5 seconds
        $this->dispatch('schedule-message-clear');
    }

    public function resetForm()
    {
        $this->selectedStudent = '';
        $this->selectedStudentData = null;
        $this->studentSearch = '';
        $this->studentSearchResults = [];
        $this->showStudentDropdown = false;
        $this->reason = '';
        $this->selectedDate = Carbon::today()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.admin.attendance.mark-bolos');
    }
}
