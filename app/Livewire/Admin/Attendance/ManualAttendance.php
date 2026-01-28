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

    // For Hadir/Terlambat status
    public $checkInTime;
    public $checkOutTime;

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
        $this->checkInTime = '07:00'; // Default check-in time
        $this->checkOutTime = '15:00'; // Default check-out time
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
        // Base validation
        $rules = [
            'selectedStudent' => 'required',
            'selectedDate' => 'required|date',
            'selectedStatus' => 'required|in:izin,sakit,dispensasi,hadir,terlambat',
        ];

        $messages = [
            'selectedStudent.required' => 'Pilih siswa terlebih dahulu',
            'selectedDate.required' => 'Tanggal harus diisi',
            'selectedStatus.required' => 'Pilih status absensi',
        ];

        // Conditional validation based on status
        if (in_array($this->selectedStatus, ['hadir', 'terlambat'])) {
            // For hadir/terlambat, require check-in time
            $rules['checkInTime'] = 'required|date_format:H:i';
            $rules['checkOutTime'] = 'nullable|date_format:H:i|after:checkInTime';
            $messages['checkInTime.required'] = 'Waktu check-in harus diisi untuk status Hadir/Terlambat';
            $messages['checkInTime.date_format'] = 'Format waktu check-in tidak valid (HH:MM)';
            $messages['checkOutTime.date_format'] = 'Format waktu check-out tidak valid (HH:MM)';
            $messages['checkOutTime.after'] = 'Waktu check-out harus setelah check-in';
        } else {
            // For izin/sakit/dispensasi, require reason
            $rules['reason'] = 'required|min:5';
            $messages['reason.required'] = 'Alasan/keterangan harus diisi';
            $messages['reason.min'] = 'Alasan minimal 5 karakter';
        }

        $this->validate($rules, $messages);

        $this->isProcessing = true;

        try {
            // For hadir/terlambat status, create attendance directly
            if (in_array($this->selectedStatus, ['hadir', 'terlambat'])) {
                $result = $this->createHadirAttendance();
            } else {
                // For izin/sakit/dispensasi, use existing method
                $result = $this->attendanceRules->createManualAttendance([
                    'student_id' => $this->selectedStudent,
                    'date' => $this->selectedDate,
                    'status' => $this->selectedStatus,
                    'reason' => $this->reason,
                    'approved_by' => auth()->id(),
                ]);
            }

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

    private function createHadirAttendance()
    {
        try {
            \DB::beginTransaction();

            $student = Student::with('class')->findOrFail($this->selectedStudent);
            $activeSemester = \App\Models\Semester::where('is_active', true)->first();

            if (!$activeSemester) {
                return [
                    'success' => false,
                    'message' => '❌ Tidak ada semester aktif'
                ];
            }

            // Check if already has attendance for this date
            $existing = \App\Models\Attendance::where('student_id', $this->selectedStudent)
                ->whereDate('date', $this->selectedDate)
                ->first();

            if ($existing) {
                return [
                    'success' => false,
                    'message' => '❌ Siswa sudah memiliki data absensi untuk tanggal ini'
                ];
            }

            // Parse times
            $checkInDateTime = Carbon::parse($this->selectedDate . ' ' . $this->checkInTime);
            $checkOutDateTime = $this->checkOutTime ? Carbon::parse($this->selectedDate . ' ' . $this->checkOutTime) : null;

            // Calculate late minutes
            $lateThreshold = \App\Models\AttendanceSetting::getValue('late_threshold', '07:15:00');
            $thresholdTime = Carbon::parse($this->selectedDate . ' ' . $lateThreshold);
            $lateMinutes = $checkInDateTime->gt($thresholdTime) ? $thresholdTime->diffInMinutes($checkInDateTime) : 0;

            // Calculate early leave minutes
            $earlyLeaveMinutes = 0;
            if ($checkOutDateTime) {
                $normalCheckOut = \App\Models\AttendanceSetting::getValue('check_out_normal', '15:30:00');
                $normalTime = Carbon::parse($this->selectedDate . ' ' . $normalCheckOut);
                $earlyLeaveMinutes = $checkOutDateTime->lt($normalTime) ? $normalTime->diffInMinutes($checkOutDateTime) : 0;
            }

            // Determine final status and percentage
            if ($this->selectedStatus === 'terlambat' || $lateMinutes > 0) {
                $finalStatus = 'terlambat';
                $percentage = 75;
            } else {
                $finalStatus = 'hadir';
                $percentage = 100;
            }

            // Create attendance record
            $attendance = \App\Models\Attendance::create([
                'student_id' => $this->selectedStudent,
                'class_id' => $student->class_id,
                'semester_id' => $activeSemester->id,
                'date' => $this->selectedDate,
                'check_in_time' => $this->checkInTime . ':00',
                'check_out_time' => $this->checkOutTime ? $this->checkOutTime . ':00' : null,
                'status' => $finalStatus,
                'check_in_method' => 'manual',
                'check_out_method' => $this->checkOutTime ? 'manual' : null,
                'late_minutes' => $lateMinutes,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'percentage' => $percentage,
                'approved_by' => auth()->id(),
                'notes' => 'Input manual oleh admin: ' . auth()->user()->name,
            ]);

            // Create audit log
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'user_type' => 'admin',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'auditable_type' => 'Attendance',
                'auditable_id' => $attendance->id,
                'event' => 'created',
                'old_values' => [],
                'new_values' => $attendance->toArray(),
                'url' => request()->fullUrl(),
                'tags' => ['manual_attendance', 'admin_input'],
                'notes' => 'Absensi manual (hadir/terlambat) dibuat oleh admin: ' . auth()->user()->name,
            ]);

            \DB::commit();

            return [
                'success' => true,
                'message' => "✅ Berhasil: {$student->full_name} - Status: " . ucfirst($finalStatus) . " ({$percentage}%)"
            ];

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error creating hadir attendance: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '❌ Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    private function submitMultiple()
    {
        // Base validation
        $rules = [
            'selectedStudents' => 'required|array|min:1',
            'selectedDate' => 'required|date',
            'selectedStatus' => 'required|in:izin,sakit,dispensasi,hadir,terlambat',
        ];

        $messages = [
            'selectedStudents.required' => 'Pilih minimal satu siswa',
            'selectedStudents.min' => 'Pilih minimal satu siswa',
            'selectedDate.required' => 'Tanggal harus diisi',
            'selectedStatus.required' => 'Pilih status absensi',
        ];

        // Conditional validation
        if (in_array($this->selectedStatus, ['hadir', 'terlambat'])) {
            $rules['checkInTime'] = 'required|date_format:H:i';
            $rules['checkOutTime'] = 'nullable|date_format:H:i|after:checkInTime';
            $messages['checkInTime.required'] = 'Waktu check-in harus diisi untuk status Hadir/Terlambat';
        } else {
            $rules['reason'] = 'required|min:5';
            $messages['reason.required'] = 'Alasan/keterangan harus diisi';
            $messages['reason.min'] = 'Alasan minimal 5 karakter';
        }

        $this->validate($rules, $messages);

        $this->isProcessing = true;

        try {
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($this->selectedStudents as $studentId) {
                // Temporarily set selectedStudent for createHadirAttendance
                $tempStudent = $this->selectedStudent;
                $this->selectedStudent = $studentId;

                if (in_array($this->selectedStatus, ['hadir', 'terlambat'])) {
                    $result = $this->createHadirAttendance();
                } else {
                    $result = $this->attendanceRules->createManualAttendance([
                        'student_id' => $studentId,
                        'date' => $this->selectedDate,
                        'status' => $this->selectedStatus,
                        'reason' => $this->reason,
                        'approved_by' => auth()->id(),
                    ]);
                }

                // Restore original selectedStudent
                $this->selectedStudent = $tempStudent;

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
        $this->checkInTime = '07:00';
        $this->checkOutTime = '15:00';
    }

    public function render()
    {
        return view('livewire.admin.attendance.manual-attendance');
    }
}
