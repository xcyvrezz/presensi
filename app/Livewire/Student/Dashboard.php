<?php

namespace App\Livewire\Student;

use App\Models\AcademicCalendar;
use App\Models\Attendance;
use App\Models\Semester;
use App\Models\Student;
use App\Services\AttendanceSummaryService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.student')]
#[Title('Dashboard Siswa')]
class Dashboard extends Component
{
    public $student;
    public $todayAttendance;
    public $monthlyStats = [];
    public $recentAttendances = [];
    public $canCheckIn = false;
    public $canCheckOut = false;
    public $isHoliday = false;
    public $holidayInfo = null;

    public $periodFilter = 'semester';
    public $activeSemester;
    public $periodLabel = '';
    public $workingDays = 0;
    public $periodStartDate;
    public $periodEndDate;

    public $totalPresent = 0;
    public $totalLate = 0;
    public $totalPermit = 0;
    public $totalSick = 0;
    public $totalAbsent = 0;
    public $totalDispensasi = 0;
    public $totalBolos = 0;
    public $totalPulangCepat = 0;
    public $totalLupaCheckout = 0;
    public $attendancePercentage = 0;
    public $summaryStats = [];

    public function mount(AttendanceSummaryService $summaryService)
    {
        $this->student = Student::where('user_id', auth()->id())
            ->with(['class.department'])
            ->first();

        if (!$this->student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        $this->isHoliday = AcademicCalendar::isHoliday(Carbon::today());

        if ($this->isHoliday) {
            $today = Carbon::today()->format('Y-m-d');
            $this->holidayInfo = AcademicCalendar::where('is_holiday', true)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
        }

        $this->loadTodayAttendance();
        $this->loadMonthlyStatistics($summaryService);
        $this->loadRecentAttendances();
    }

    public function loadTodayAttendance()
    {
        $today = Carbon::today();
        $this->todayAttendance = Attendance::where('student_id', $this->student->id)
            ->whereDate('date', $today)
            ->first();

        $now = Carbon::now();
        $checkInStart = Carbon::parse('05:00');
        $checkInEnd = Carbon::parse('07:00');
        $checkOutStart = Carbon::parse('14:00');

        if (!$this->todayAttendance) {
            $this->canCheckIn = $now->between($checkInStart, $checkInEnd);
            $this->canCheckOut = false;
        } else {
            $this->canCheckIn = false;
            $this->canCheckOut = !$this->todayAttendance->check_out_time && $now->greaterThanOrEqualTo($checkOutStart);
        }
    }

    public function loadMonthlyStatistics(?AttendanceSummaryService $summaryService = null)
    {
        $summaryService ??= app(AttendanceSummaryService::class);
        $this->activeSemester = Semester::where('is_active', true)->first();

        if ($this->periodFilter === 'semester' && $this->activeSemester) {
            $period = $summaryService->getActivePeriod($this->activeSemester);
            $startDate = $period['start_date'];
            $endDate = $period['end_date'];
            $this->periodLabel = $this->activeSemester->name . ' (s/d Hari Ini)';
        } else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::today();
            $this->periodLabel = 'Bulan ' . Carbon::now()->translatedFormat('F Y');
        }

        $this->periodStartDate = $startDate->format('Y-m-d');
        $this->periodEndDate = $endDate->format('Y-m-d');

        $attendances = Attendance::where('student_id', $this->student->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $this->totalPresent = $attendances->where('status', 'hadir')->count();
        $this->totalLate = $attendances->where('status', 'terlambat')->count();
        $this->totalPermit = $attendances->where('status', 'izin')->count();
        $this->totalSick = $attendances->where('status', 'sakit')->count();
        $this->totalDispensasi = $attendances->where('status', 'dispensasi')->count();
        $this->totalBolos = $attendances->where('status', 'bolos')->count();
        $this->totalPulangCepat = $attendances->where('status', 'pulang_cepat')->count();
        $this->totalLupaCheckout = $attendances->filter(fn ($attendance) => $attendance->check_in_time && !$attendance->check_out_time && in_array($attendance->status, ['hadir', 'terlambat']))->count();
        $this->totalAbsent = $attendances->where('status', 'alpha')->count();

        $this->summaryStats = $summaryService->getStudentSummary(
            $this->student->id,
            $startDate,
            $endDate,
            $this->student->class->department_id ?? null,
            $this->student->class_id
        );

        $this->workingDays = $this->summaryStats['effective_days'];
        $this->attendancePercentage = $this->summaryStats['attendance_rate'];

        $this->monthlyStats = $attendances->groupBy(fn ($attendance) => Carbon::parse($attendance->date)->format('Y-m-d'))
            ->map(fn ($dayAttendances) => $dayAttendances->first())
            ->toArray();
    }

    public function loadRecentAttendances()
    {
        $this->recentAttendances = Attendance::where('student_id', $this->student->id)
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->limit(10)
            ->get();
    }

    public function changePeriod($period)
    {
        $this->periodFilter = $period;
        $this->loadMonthlyStatistics();
    }

    public function refreshData()
    {
        $this->loadTodayAttendance();
        $this->loadMonthlyStatistics();
        $this->loadRecentAttendances();

        session()->flash('success', 'Data berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.student.dashboard');
    }
}
