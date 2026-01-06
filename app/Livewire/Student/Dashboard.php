<?php

namespace App\Livewire\Student;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Semester;
use App\Models\AcademicCalendar;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

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

    // Period filter
    public $periodFilter = 'semester'; // semester, month, custom
    public $activeSemester;
    public $periodLabel = '';
    public $workingDays = 0;

    // Monthly statistics
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

    public function mount()
    {
        // Get authenticated student
        $this->student = Student::where('user_id', auth()->id())
            ->with(['class.department'])
            ->first();

        if (!$this->student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        // Check if today is a holiday
        $this->isHoliday = AcademicCalendar::isHoliday(Carbon::today());

        if ($this->isHoliday) {
            $today = Carbon::today()->format('Y-m-d');

            $this->holidayInfo = AcademicCalendar::where('is_holiday', true)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
        }

        $this->loadTodayAttendance();
        $this->loadMonthlyStatistics();
        $this->loadRecentAttendances();
    }

    public function loadTodayAttendance()
    {
        $today = Carbon::today();
        $this->todayAttendance = Attendance::where('student_id', $this->student->id)
            ->whereDate('date', $today)
            ->first();

        // Check if can check-in or check-out
        $now = Carbon::now();
        $checkInStart = Carbon::parse('05:00');
        $checkInEnd = Carbon::parse('07:00');
        $checkOutStart = Carbon::parse('14:00');
        $checkOutEnd = Carbon::parse('17:00');

        if (!$this->todayAttendance) {
            // Can check-in if within time window
            $this->canCheckIn = $now->between($checkInStart, $checkInEnd);
            $this->canCheckOut = false;
        } else {
            // Can check-out if checked in and within time window
            $this->canCheckIn = false;
            $this->canCheckOut = !$this->todayAttendance->check_out_time && $now->greaterThanOrEqualTo($checkOutStart);
        }
    }

    public function loadMonthlyStatistics()
    {
        // Get active semester for period calculation
        $this->activeSemester = Semester::where('is_active', true)->first();

        if ($this->periodFilter === 'semester' && $this->activeSemester) {
            // Use semester period (from semester start to today)
            $startDate = Carbon::parse($this->activeSemester->start_date);
            $endDate = Carbon::today(); // Always until today
            $this->periodLabel = $this->activeSemester->name . ' (s/d Hari Ini)';
        } elseif ($this->periodFilter === 'month') {
            // Use current month only
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::today();
            $this->periodLabel = 'Bulan ' . Carbon::now()->format('F Y');
        } else {
            // Fallback to current month if no active semester
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::today();
            $this->periodLabel = 'Bulan ' . Carbon::now()->format('F Y');
        }

        // Get all attendances in period
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

        // Calculate lupa checkout
        $this->totalLupaCheckout = $attendances->filter(function($attendance) {
            return $attendance->check_in_time
                && !$attendance->check_out_time
                && in_array($attendance->status, ['hadir', 'terlambat']);
        })->count();

        // LOGIC BARU: Alpha = records with status 'alpha'
        $this->totalAbsent = $attendances->where('status', 'alpha')->count();

        // Calculate working days in period (excluding weekends and holidays)
        $this->workingDays = $this->getWorkingDaysBetween($startDate, $endDate);

        // Calculate attendance percentage for the period
        // Present = hadir + terlambat + dispensasi (positive attendance)
        $presentDays = $this->totalPresent + $this->totalLate + $this->totalDispensasi;
        $this->attendancePercentage = $this->workingDays > 0
            ? round(($presentDays / $this->workingDays) * 100, 1)
            : 0;

        // Store for calendar view
        $this->monthlyStats = $attendances->groupBy(function ($attendance) {
            return Carbon::parse($attendance->date)->format('Y-m-d');
        })->map(function ($dayAttendances) {
            return $dayAttendances->first();
        })->toArray();
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

    /**
     * Get working days (weekdays) between two dates, excluding holidays
     */
    private function getWorkingDaysBetween($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workingDays = 0;

        // Only count until today if end date is in the future
        $today = Carbon::today();
        if ($end->greaterThan($today)) {
            $end = $today;
        }

        // Get all holiday dates in this range
        $holidayDates = \App\Models\AcademicCalendar::getHolidayDates($start, $end);

        while ($start <= $end) {
            // Count weekdays only (Monday-Friday) and exclude holidays
            if ($start->isWeekday() && !in_array($start->format('Y-m-d'), $holidayDates)) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }

    public function render()
    {
        return view('livewire.student.dashboard');
    }
}
