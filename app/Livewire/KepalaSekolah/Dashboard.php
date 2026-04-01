<?php

namespace App\Livewire\KepalaSekolah;

use App\Models\AcademicCalendar;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Department;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use App\Services\AttendanceSummaryService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.kepala-sekolah')]
#[Title('Dashboard Kepala Sekolah')]
class Dashboard extends Component
{
    public $selectedPeriod = 'today';

    public function render(AttendanceSummaryService $summaryService)
    {
        $isHoliday = AcademicCalendar::isHoliday(Carbon::today());
        $holidayInfo = null;

        if ($isHoliday) {
            $today = Carbon::today()->format('Y-m-d');
            $holidayInfo = AcademicCalendar::where('is_holiday', true)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
        }

        $period = $summaryService->getActivePeriod();
        $overallStats = $this->getOverallStatistics($summaryService, $period);
        $departmentStats = $this->getDepartmentStatistics($summaryService, $period);
        $monthlyTrend = $this->getMonthlyTrend($summaryService, $period['semester']);
        $topClasses = $this->getTopClasses($summaryService, $period);
        $todayAttendance = $this->getTodayAttendanceByHour();

        return view('livewire.kepala-sekolah.dashboard', compact(
            'isHoliday',
            'holidayInfo',
            'overallStats',
            'departmentStats',
            'monthlyTrend',
            'topClasses',
            'todayAttendance',
            'period'
        ));
    }

    private function getOverallStatistics(AttendanceSummaryService $summaryService, array $period)
    {
        $totalStudents = Student::where('is_active', true)->count();
        $periodSummary = $summaryService->getAggregateSummary(Student::active(), $period['start_date'], $period['end_date']);
        $todayAttendances = Attendance::whereDate('date', Carbon::today())->get();
        $todayPresent = $todayAttendances->whereIn('status', ['hadir', 'terlambat'])->count();

        return [
            'total_students' => $totalStudents,
            'total_classes' => Classes::count(),
            'total_departments' => Department::count(),
            'total_teachers' => User::waliKelas()->count(),
            'today_present' => $todayPresent,
            'today_absent' => max($totalStudents - $todayAttendances->count(), 0),
            'attendance_percentage' => $totalStudents > 0 ? round(($todayPresent / $totalStudents) * 100, 1) : 0,
            'period_attendance_percentage' => $periodSummary['attendance_rate'],
            'period_effective_days' => $periodSummary['effective_days'],
            'period_present' => $periodSummary['present_count'],
            'period_expected' => $periodSummary['expected_records'],
            'period_missing' => $periodSummary['missing_records'],
            'period_alpha' => $periodSummary['alpha_count'],
            'period_bolos' => $periodSummary['bolos_count'],
        ];
    }

    private function getDepartmentStatistics(AttendanceSummaryService $summaryService, array $period)
    {
        return Department::withCount(['students' => fn ($q) => $q->where('students.is_active', true)])
            ->get()
            ->map(function ($dept) use ($summaryService, $period) {
                $studentQuery = Student::active()->whereHas('class', fn ($q) => $q->where('department_id', $dept->id));
                $summary = $summaryService->getAggregateSummary($studentQuery, $period['start_date'], $period['end_date'], $dept->id);

                return [
                    'name' => $dept->name,
                    'code' => $dept->code,
                    'total_students' => $dept->students_count,
                    'present' => $summary['present_count'],
                    'expected_records' => $summary['expected_records'],
                    'absent' => $summary['missing_records'] + $summary['alpha_count'] + $summary['bolos_count'],
                    'percentage' => $summary['attendance_rate'],
                ];
            })
            ->sortByDesc('percentage')
            ->values();
    }

    private function getMonthlyTrend(AttendanceSummaryService $summaryService, ?Semester $semester)
    {
        $today = Carbon::today();
        $semesterStart = $semester ? Carbon::parse($semester->start_date)->startOfMonth() : $today->copy()->startOfYear();
        $semesterEnd = $semester ? Carbon::parse($semester->end_date)->min($today) : $today;
        $months = [];
        $cursor = $semesterStart->copy();

        while ($cursor->lte($semesterEnd)) {
            $startOfMonth = $cursor->copy()->startOfMonth()->max($semester ? Carbon::parse($semester->start_date) : $cursor->copy()->startOfMonth());
            $endOfMonth = $cursor->copy()->endOfMonth()->min($semesterEnd);
            $summary = $summaryService->getAggregateSummary(Student::active(), $startOfMonth, $endOfMonth);

            $months[] = [
                'month' => $cursor->translatedFormat('M'),
                'present' => $summary['hadir_count'],
                'late' => $summary['late_count'],
                'absent' => $summary['alpha_count'] + $summary['bolos_count'] + $summary['missing_records'],
                'percentage' => $summary['attendance_rate'],
                'effective_days' => $summary['effective_days'],
            ];

            $cursor->addMonth()->startOfMonth();
        }

        return $months;
    }

    private function getTopClasses(AttendanceSummaryService $summaryService, array $period)
    {
        return Classes::with('department')
            ->active()
            ->get()
            ->map(function ($class) use ($summaryService, $period) {
                $studentQuery = $class->students()->where('is_active', true);
                $studentCount = (clone $studentQuery)->count();

                if ($studentCount === 0) {
                    return null;
                }

                $summary = $summaryService->getAggregateSummary($studentQuery, $period['start_date'], $period['end_date'], $class->department_id, $class->id);

                return [
                    'class_name' => $class->name,
                    'department' => $class->department->name ?? '-',
                    'total_students' => $studentCount,
                    'present' => $summary['present_count'],
                    'expected_records' => $summary['expected_records'],
                    'percentage' => $summary['attendance_rate'],
                ];
            })
            ->filter()
            ->sortByDesc('percentage')
            ->take(5)
            ->values();
    }

    private function getTodayAttendanceByHour()
    {
        $attendances = Attendance::whereDate('date', Carbon::today())
            ->selectRaw('HOUR(check_in_time) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $hourlyData = [];
        for ($hour = 6; $hour <= 17; $hour++) {
            $count = $attendances->where('hour', $hour)->first();
            $hourlyData[] = [
                'hour' => sprintf('%02d:00', $hour),
                'count' => $count ? $count->count : 0,
            ];
        }

        return $hourlyData;
    }
}
