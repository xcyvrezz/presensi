<?php

namespace App\Livewire\WaliKelas;

use App\Models\Classes;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Semester;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Layout('layouts.wali-kelas')]
#[Title('Absensi Manual')]
class ManualAttendance extends Component
{
    public $class;
    public $students;
    public $selectedDate;
    public $selectedStudent = '';
    public $status = 'hadir';
    public $checkInTime = '';
    public $checkOutTime = '';
    public $notes = '';

    // Multiple Student Selection
    public $inputMode = 'single'; // single or multiple
    public $selectedStudents = [];
    public $selectAll = false;

    protected $rules = [
        'selectedDate' => 'required|date',
        'selectedStudent' => 'required|exists:students,id',
        'status' => 'required|in:hadir,terlambat,izin,sakit,alpha,dispensasi',
        'checkInTime' => 'required|date_format:H:i',
        'checkOutTime' => 'nullable|date_format:H:i|after:checkInTime',
        'notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'selectedDate.required' => 'Tanggal harus dipilih.',
        'selectedStudent.required' => 'Siswa harus dipilih.',
        'status.required' => 'Status harus dipilih.',
        'checkInTime.required' => 'Waktu check-in harus diisi.',
        'checkInTime.date_format' => 'Format waktu check-in tidak valid.',
        'checkOutTime.date_format' => 'Format waktu check-out tidak valid.',
        'checkOutTime.after' => 'Waktu check-out harus setelah check-in.',
    ];

    public function mount()
    {
        // Get class yang diampu
        $this->class = Classes::where('wali_kelas_id', auth()->id())
            ->with(['department'])
            ->first();

        if (!$this->class) {
            session()->flash('error', 'Anda tidak ditugaskan sebagai wali kelas.');
            return;
        }

        // Get all students in this class
        $this->students = Student::where('class_id', $this->class->id)
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        // Set default values
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->checkInTime = '07:00';
    }

    public function switchMode($mode)
    {
        $this->inputMode = $mode;
        $this->selectedStudents = [];
        $this->selectAll = false;

        if ($mode === 'single') {
            $this->selectedStudent = '';
        }
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedStudents = $this->students->pluck('id')->toArray();
        } else {
            $this->selectedStudents = [];
        }
    }

    public function updatedSelectedStudents()
    {
        $this->selectAll = count($this->selectedStudents) === count($this->students);
    }

    public function submit()
    {
        if ($this->inputMode === 'single') {
            $this->submitSingle();
        } else {
            $this->submitMultiple();
        }
    }

    private function submitSingle()
    {
        $this->validate();

        // Check if attendance already exists
        $existingAttendance = Attendance::where('student_id', $this->selectedStudent)
            ->whereDate('date', $this->selectedDate)
            ->first();

        if ($existingAttendance) {
            session()->flash('error', 'Absensi untuk siswa ini pada tanggal tersebut sudah ada.');
            return;
        }

        // Get student
        $student = Student::find($this->selectedStudent);

        // Get active semester
        $semester = Semester::where('is_active', true)->first();

        // Calculate late minutes and percentage
        $lateMinutes = 0;
        $percentage = 100;

        if ($this->status === 'terlambat') {
            $checkInTime = Carbon::parse($this->selectedDate . ' ' . $this->checkInTime);
            $lateThreshold = Carbon::parse($this->selectedDate . ' 07:00');
            $lateMinutes = $checkInTime->diffInMinutes($lateThreshold);
            $percentage = 75;
        } elseif (in_array($this->status, ['izin', 'sakit'])) {
            $percentage = 50;
        } elseif (in_array($this->status, ['alpha', 'dispensasi'])) {
            $percentage = 0;
        }

        // Create attendance record
        $attendance = Attendance::create([
            'student_id' => $this->selectedStudent,
            'class_id' => $this->class->id,
            'semester_id' => $semester ? $semester->id : null,
            'date' => $this->selectedDate,
            'check_in_time' => $this->checkInTime . ':00',
            'check_out_time' => $this->checkOutTime ? $this->checkOutTime . ':00' : null,
            'status' => $this->status,
            'late_minutes' => $lateMinutes,
            'percentage' => $percentage,
            'check_in_method' => 'manual',
            'check_out_method' => $this->checkOutTime ? 'manual' : null,
            'notes' => $this->notes ? 'Manual Entry: ' . $this->notes : 'Manual Entry by ' . auth()->user()->name,
        ]);

        session()->flash('success', 'Absensi manual untuk ' . $student->full_name . ' berhasil disimpan.');

        // Reset form
        $this->reset(['selectedStudent', 'status', 'checkInTime', 'checkOutTime', 'notes']);
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->checkInTime = '07:00';
        $this->status = 'hadir';
    }

    private function submitMultiple()
    {
        $this->validate([
            'selectedStudents' => 'required|array|min:1',
            'selectedDate' => 'required|date',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha,dispensasi',
            'checkInTime' => 'required|date_format:H:i',
            'checkOutTime' => 'nullable|date_format:H:i|after:checkInTime',
            'notes' => 'nullable|string|max:500',
        ], [
            'selectedStudents.required' => 'Pilih minimal satu siswa',
            'selectedStudents.min' => 'Pilih minimal satu siswa',
        ]);

        // Get active semester
        $semester = Semester::where('is_active', true)->first();

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($this->selectedStudents as $studentId) {
            // Check if attendance already exists
            $existingAttendance = Attendance::where('student_id', $studentId)
                ->whereDate('date', $this->selectedDate)
                ->first();

            if ($existingAttendance) {
                $errorCount++;
                $student = Student::find($studentId);
                $errors[] = $student->full_name . ': Sudah ada absensi';
                continue;
            }

            // Calculate late minutes and percentage
            $lateMinutes = 0;
            $percentage = 100;

            if ($this->status === 'terlambat') {
                $checkInTime = Carbon::parse($this->selectedDate . ' ' . $this->checkInTime);
                $lateThreshold = Carbon::parse($this->selectedDate . ' 07:00');
                $lateMinutes = $checkInTime->diffInMinutes($lateThreshold);
                $percentage = 75;
            } elseif (in_array($this->status, ['izin', 'sakit'])) {
                $percentage = 50;
            } elseif (in_array($this->status, ['alpha', 'dispensasi'])) {
                $percentage = 0;
            }

            try {
                // Create attendance record
                Attendance::create([
                    'student_id' => $studentId,
                    'class_id' => $this->class->id,
                    'semester_id' => $semester ? $semester->id : null,
                    'date' => $this->selectedDate,
                    'check_in_time' => $this->checkInTime . ':00',
                    'check_out_time' => $this->checkOutTime ? $this->checkOutTime . ':00' : null,
                    'status' => $this->status,
                    'late_minutes' => $lateMinutes,
                    'percentage' => $percentage,
                    'check_in_method' => 'manual',
                    'check_out_method' => $this->checkOutTime ? 'manual' : null,
                    'notes' => $this->notes ? 'Manual Entry: ' . $this->notes : 'Manual Entry by ' . auth()->user()->name,
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $student = Student::find($studentId);
                $errors[] = $student->full_name . ': ' . $e->getMessage();
            }
        }

        if ($successCount > 0 && $errorCount === 0) {
            session()->flash('success', "Berhasil menyimpan absensi untuk {$successCount} siswa.");
        } elseif ($successCount > 0 && $errorCount > 0) {
            session()->flash('error', "Berhasil: {$successCount} siswa, Gagal: {$errorCount} siswa. " . implode('; ', array_slice($errors, 0, 3)));
        } else {
            session()->flash('error', 'Semua gagal: ' . implode('; ', array_slice($errors, 0, 3)));
        }

        // Reset form
        $this->reset(['selectedStudents', 'status', 'checkInTime', 'checkOutTime', 'notes']);
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->checkInTime = '07:00';
        $this->status = 'hadir';
        $this->selectAll = false;
    }

    public function render()
    {
        // Get recent attendances for this class (manual or not)
        $recentEntries = collect();
        if ($this->class) {
            $recentEntries = Attendance::whereHas('student', function ($q) {
                $q->where('class_id', $this->class->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->with(['student'])
            ->get();
        }

        return view('livewire.wali-kelas.manual-attendance', [
            'recentEntries' => $recentEntries,
        ]);
    }
}
