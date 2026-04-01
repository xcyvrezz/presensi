<?php

namespace App\Livewire\WaliKelas;

use App\Models\AcademicCalendar;
use App\Models\Attendance;
use App\Models\Classes;
use App\Services\AttendanceSummaryService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.wali-kelas')]
#[Title('Dashboard Wali Kelas')]
class Dashboard extends Component
{
    public $class;
    public $totalStudents = 0;
    public $presentToday = 0;
    public $absentToday = 0;
    public $lateToday = 0;
    public $attendancePercentage = 0;
    public $isHoliday = false;
    public $holidayInfo = null;
    public $summaryStats = [];
    public $activePeriodLabel = '-';
    public $periodStartDate;
    public $periodEndDate;

    public function mount(AttendanceSummaryService $summaryService)
    {
        $this->isHoliday = AcademicCalendar::isHoliday(Carbon::today());

        if ($this->isHoliday) {
            $today = Carbon::today()->format('Y-m-d');
            $this->holidayInfo = AcademicCalendar::where('is_holiday', true)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
        }

        $this->class = Classes::where('wali_kelas_id', auth()->id())
            ->with(['department', 'students'])
            ->first();

        if (!$this->class) {
            return;
        }

        $studentQuery = $this->class->students()->where('is_active', true);
        $this->totalStudents = (clone $studentQuery)->count();
        $activeStudentIds = (clone $studentQuery)->pluck('id');

        $todayAttendances = Attendance::whereIn('student_id', $activeStudentIds)
            ->whereDate('date', Carbon::today())
            ->get();

        $this->lateToday = $todayAttendances->where('status', 'terlambat')->count();
        $this->presentToday = $todayAttendances->where('status', 'hadir')->count() + $this->lateToday;
        $this->absentToday = max($this->totalStudents - $todayAttendances->count(), 0);

        $period = $summaryService->getActivePeriod();
        $this->activePeriodLabel = $period['label'];
        $this->periodStartDate = $period['start_date']->format('Y-m-d');
        $this->periodEndDate = $period['end_date']->format('Y-m-d');

        $this->summaryStats = $summaryService->getAggregateSummary(
            $studentQuery,
            $period['start_date'],
            $period['end_date'],
            $this->class->department_id,
            $this->class->id
        );

        $this->attendancePercentage = $this->summaryStats['attendance_rate'];
    }

    public function render()
    {
        $recentAttendances = [];

        if ($this->class) {
            $recentAttendances = Attendance::whereHas('student', function ($query) {
                $query->where('class_id', $this->class->id);
            })
                ->with(['student'])
                ->whereDate('date', Carbon::today())
                ->orderBy('check_in_time', 'desc')
                ->limit(10)
                ->get();
        }

        return view('livewire.wali-kelas.dashboard', [
            'recentAttendances' => $recentAttendances,
        ]);
    }
}
