<?php

namespace App\Livewire\Student;

use App\Models\Student;
use App\Models\Attendance;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Layout('layouts.student')]
#[Title('Riwayat Absensi')]
class AttendanceHistory extends Component
{
    use WithPagination;

    public $student;
    public $selectedMonth;
    public $selectedYear;
    public $statusFilter = '';

    public function mount()
    {
        $this->student = Student::where('user_id', auth()->id())
            ->with(['class.department'])
            ->first();

        if (!$this->student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        // Set default to current month and year
        $this->selectedMonth = Carbon::now()->month;
        $this->selectedYear = Carbon::now()->year;
    }

    public function updatingSelectedMonth()
    {
        $this->resetPage();
    }

    public function updatingSelectedYear()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Get attendance records
        $attendances = Attendance::where('student_id', $this->student->id)
            ->whereYear('date', $this->selectedYear)
            ->whereMonth('date', $this->selectedMonth)
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(15);

        // Calculate monthly statistics
        $monthlyStats = $this->getMonthlyStatistics();

        // Get years for dropdown (from first attendance to current year)
        $firstAttendance = Attendance::where('student_id', $this->student->id)
            ->orderBy('date', 'asc')
            ->first();

        $startYear = $firstAttendance ? Carbon::parse($firstAttendance->date)->year : Carbon::now()->year;
        $years = range(Carbon::now()->year, $startYear);

        return view('livewire.student.attendance-history', [
            'attendances' => $attendances,
            'monthlyStats' => $monthlyStats,
            'years' => $years,
        ]);
    }

    private function getMonthlyStatistics()
    {
        $attendances = Attendance::where('student_id', $this->student->id)
            ->whereYear('date', $this->selectedYear)
            ->whereMonth('date', $this->selectedMonth)
            ->get();

        // Calculate lupa checkout
        $lupaCheckout = $attendances->filter(function($attendance) {
            return $attendance->check_in_time
                && !$attendance->check_out_time
                && in_array($attendance->status, ['hadir', 'terlambat']);
        })->count();

        return [
            'total' => $attendances->count(),
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
            'dispensasi' => $attendances->where('status', 'dispensasi')->count(),
            'bolos' => $attendances->where('status', 'bolos')->count(),
            'pulang_cepat' => $attendances->where('status', 'pulang_cepat')->count(),
            'lupa_checkout' => $lupaCheckout,
        ];
    }
}
