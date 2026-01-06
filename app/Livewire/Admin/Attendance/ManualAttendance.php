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
#[Title('Input Absensi Manual')]
class ManualAttendance extends Component
{
    public $students = [];
    public $departments = [];
    public $classes = [];

    // Form fields
    public $selectedDepartment = '';
    public $selectedClass = '';
    public $selectedStudent = '';
    public $selectedDate;
    public $selectedStatus = 'izin';
    public $reason = '';
    public $proofFile = null;

    // Search & Filter
    public $studentSearch = '';
    public $studentSearchResults = [];
    public $showStudentDropdown = false;
    public $selectedStudentData = null;

    // Multiple Student Selection
    public $inputMode = 'single'; // single or multiple
    public $selectedStudents = [];
    public $filteredStudents = [];
    public $selectAll = false;

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

            // For multiple mode, also update filtered students
            $this->filteredStudents = $this->students;

            $this->selectedStudent = '';
            $this->studentSearch = '';
            $this->studentSearchResults = [];
            $this->selectedStudentData = null;
            $this->selectedStudents = [];
            $this->selectAll = false;
        } else {
            $this->students = [];
            $this->filteredStudents = [];
            $this->selectedStudent = '';
            $this->studentSearch = '';
            $this->studentSearchResults = [];
            $this->selectedStudentData = null;
            $this->selectedStudents = [];
            $this->selectAll = false;
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

    public function switchMode($mode)
    {
        $this->inputMode = $mode;
        $this->selectedStudents = [];
        $this->selectAll = false;

        if ($mode === 'single') {
            $this->selectedStudent = '';
            $this->selectedStudentData = null;
        }
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedStudents = $this->filteredStudents->pluck('id')->toArray();
        } else {
            $this->selectedStudents = [];
        }
    }

    public function updatedSelectedStudents()
    {
        $this->selectAll = count($this->selectedStudents) === count($this->filteredStudents);
    }

    public function submitManualAttendance()
    {
        if ($this->inputMode === 'single') {
            $this->submitSingle();
        } else {
            $this->submitMultiple();
        }
    }

    private function submitSingle()
    {
        $this->validate([
            'selectedStudent' => 'required',
            'selectedDate' => 'required|date',
            'selectedStatus' => 'required|in:izin,sakit,dispensasi',
            'reason' => 'required|min:5',
        ], [
            'selectedStudent.required' => 'Pilih siswa terlebih dahulu',
            'selectedDate.required' => 'Tanggal harus diisi',
            'selectedStatus.required' => 'Pilih status absensi',
            'reason.required' => 'Alasan/keterangan harus diisi',
            'reason.min' => 'Alasan minimal 5 karakter',
        ]);

        $this->isProcessing = true;

        try {
            $result = $this->attendanceRules->createManualAttendance([
                'student_id' => $this->selectedStudent,
                'date' => $this->selectedDate,
                'status' => $this->selectedStatus,
                'reason' => $this->reason,
                'approved_by' => auth()->id(),
            ]);

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
        $this->dispatch('schedule-message-clear');
    }

    private function submitMultiple()
    {
        $this->validate([
            'selectedStudents' => 'required|array|min:1',
            'selectedDate' => 'required|date',
            'selectedStatus' => 'required|in:izin,sakit,dispensasi',
            'reason' => 'required|min:5',
        ], [
            'selectedStudents.required' => 'Pilih minimal satu siswa',
            'selectedStudents.min' => 'Pilih minimal satu siswa',
            'selectedDate.required' => 'Tanggal harus diisi',
            'selectedStatus.required' => 'Pilih status absensi',
            'reason.required' => 'Alasan/keterangan harus diisi',
            'reason.min' => 'Alasan minimal 5 karakter',
        ]);

        $this->isProcessing = true;

        try {
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($this->selectedStudents as $studentId) {
                $result = $this->attendanceRules->createManualAttendance([
                    'student_id' => $studentId,
                    'date' => $this->selectedDate,
                    'status' => $this->selectedStatus,
                    'reason' => $this->reason,
                    'approved_by' => auth()->id(),
                ]);

                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $student = Student::find($studentId);
                    $errors[] = $student->full_name . ': ' . $result['message'];
                }
            }

            if ($successCount > 0 && $errorCount === 0) {
                $this->statusMessage = "✅ Berhasil menyimpan absensi untuk {$successCount} siswa";
                $this->statusType = 'success';
                $this->resetForm();
            } elseif ($successCount > 0 && $errorCount > 0) {
                $this->statusMessage = "⚠️ Berhasil: {$successCount} siswa, Gagal: {$errorCount} siswa. " . implode('; ', $errors);
                $this->statusType = 'error';
            } else {
                $this->statusMessage = "❌ Semua gagal: " . implode('; ', $errors);
                $this->statusType = 'error';
            }

        } catch (\Exception $e) {
            $this->statusMessage = '❌ Terjadi kesalahan: ' . $e->getMessage();
            $this->statusType = 'error';
        }

        $this->isProcessing = false;
        $this->dispatch('schedule-message-clear');
    }

    public function resetForm()
    {
        $this->selectedStudent = '';
        $this->selectedStudentData = null;
        $this->studentSearch = '';
        $this->studentSearchResults = [];
        $this->showStudentDropdown = false;
        $this->selectedStudents = [];
        $this->selectAll = false;
        $this->reason = '';
        $this->selectedDate = Carbon::today()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.admin.attendance.manual-attendance');
    }
}
