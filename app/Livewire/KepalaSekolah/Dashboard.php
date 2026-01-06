<?php

namespace App\Livewire\KepalaSekolah;

use App\Models\Student;
use App\Models\Classes;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Semester;
use App\Models\AcademicCalendar;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.kepala-sekolah')]
#[Title('Dashboard Kepala Sekolah')]
class Dashboard extends Component
{
    public $selectedPeriod = 'today'; // today, week, month, year

    public function render()
    {
        // Check if today is a holiday
        $isHoliday = AcademicCalendar::isHoliday(Carbon::today());
        $holidayInfo = null;

        if ($isHoliday) {
            $today = Carbon::today()->format('Y-m-d');

            $holidayInfo = AcademicCalendar::where('is_holiday', true)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
        }

        $overallStats = $this->getOverallStatistics();
        $departmentStats = $this->getDepartmentStatistics();
        $monthlyTrend = $this->getMonthlyTrend();
        $topClasses = $this->getTopClasses();
        $todayAttendance = $this->getTodayAttendanceByHour();

        return view('livewire.kepala-sekolah.dashboard', [
            'isHoliday' => $isHoliday,
            'holidayInfo' => $holidayInfo,
            'overallStats' => $overallStats,
            'departmentStats' => $departmentStats,
            'monthlyTrend' => $monthlyTrend,
            'topClasses' => $topClasses,
            'todayAttendance' => $todayAttendance,
        ]);
    }

    private function getOverallStatistics()
    {
        $totalStudents = Student::where('is_active', true)->count();
        $totalClasses = Classes::count();
        $totalDepartments = Department::count();
        $totalTeachers = User::waliKelas()->count();

        // Today's attendance - KONSISTEN
        $today = Carbon::today();
        $todayAttendances = Attendance::whereDate('date', $today)->get();

        // Present = hanya yang benar-benar hadir (hadir + terlambat)
        $totalPresent = $todayAttendances->whereIn('status', ['hadir', 'terlambat'])->count();

        // LOGIC BARU: Alpha = record yang sudah di-generate dengan status 'alpha'
        $totalAlpha = $todayAttendances->where('status', 'alpha')->count();

        // Belum absen = siswa yang belum punya record sama sekali
        $totalBelumAbsen = $totalStudents - $todayAttendances->count();

        $attendancePercentage = $totalStudents > 0 ? round(($totalPresent / $totalStudents) * 100, 1) : 0;

        return [
            'total_students' => $totalStudents,
            'total_classes' => $totalClasses,
            'total_departments' => $totalDepartments,
            'total_teachers' => $totalTeachers,
            'today_present' => $totalPresent,
            'today_absent' => $totalBelumAbsen, // Belum absen
            'today_late' => $todayAttendances->where('status', 'terlambat')->count(),
            'today_sick' => $todayAttendances->where('status', 'sakit')->count(),
            'today_permission' => $todayAttendances->where('status', 'izin')->count(),
            'today_alpha' => $totalAlpha, // Record dengan status alpha
            'today_bolos' => $todayAttendances->where('status', 'bolos')->count(),
            'today_dispensation' => $todayAttendances->where('status', 'dispensasi')->count(),
            'attendance_percentage' => $attendancePercentage,
        ];
    }

    private function getDepartmentStatistics()
    {
        $departments = Department::withCount(['students' => function ($q) {
            $q->where('students.is_active', true);
        }])->get();

        $stats = [];
        $today = Carbon::today();

        foreach ($departments as $dept) {
            $todayAttendances = Attendance::whereDate('date', $today)
                ->whereHas('student.class', function ($q) use ($dept) {
                    $q->where('department_id', $dept->id);
                })->get();

            $present = $todayAttendances->whereIn('status', ['hadir', 'terlambat'])->count();

            // Belum absen = siswa jurusan ini yang tidak punya record
            $absent = $dept->students_count - $todayAttendances->count();

            $percentage = $dept->students_count > 0 ? round(($present / $dept->students_count) * 100, 1) : 0;

            $stats[] = [
                'name' => $dept->name,
                'code' => $dept->code,
                'total_students' => $dept->students_count,
                'present' => $present,
                'absent' => $absent,
                'percentage' => $percentage,
            ];
        }

        return collect($stats)->sortByDesc('percentage')->values();
    }

    private function getMonthlyTrend()
    {
        // Get active semester for period calculation
        $activeSemester = Semester::where('is_active', true)->first();

        if (!$activeSemester) {
            // Fallback to current year if no active semester
            $year = Carbon::now()->year;
            $startMonth = 1;
            $endMonth = 12;
        } else {
            // Use semester period
            $startDate = Carbon::parse($activeSemester->start_date);
            $endDate = Carbon::parse($activeSemester->end_date);
            $year = $startDate->year;
            $startMonth = $startDate->month;
            $endMonth = $endDate->month;

            // Handle if semester crosses year boundary
            if ($endDate->year > $startDate->year) {
                $endMonth = 12; // Show until end of start year
            }
        }

        $monthlyData = [];
        $totalStudents = Student::where('is_active', true)->count();

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
            $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

            $attendances = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])->get();

            $workingDays = $this->getWorkingDaysInMonth($year, $month);
            $expectedAttendances = $workingDays * $totalStudents;

            $actualPresent = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();
            $percentage = $expectedAttendances > 0 ? round(($actualPresent / $expectedAttendances) * 100, 1) : 0;

            $monthlyData[] = [
                'month' => Carbon::create($year, $month, 1)->format('M'),
                'present' => $attendances->where('status', 'hadir')->count(),
                'late' => $attendances->where('status', 'terlambat')->count(),
                'absent' => ($workingDays * $totalStudents) - $attendances->count(), // Alpha
                'percentage' => $percentage,
            ];
        }

        return $monthlyData;
    }

    private function getTopClasses()
    {
        $classes = Classes::with('department')->get();
        $classStats = [];
        $today = Carbon::today();

        foreach ($classes as $class) {
            $students = Student::where('class_id', $class->id)
                ->where('is_active', true)
                ->get();

            if ($students->count() === 0) continue;

            $attendances = Attendance::whereDate('date', $today)
                ->whereIn('student_id', $students->pluck('id'))
                ->get();

            $present = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();
            $percentage = round(($present / $students->count()) * 100, 1);

            $classStats[] = [
                'class_name' => $class->name,
                'department' => $class->department->name,
                'total_students' => $students->count(),
                'present' => $present,
                'percentage' => $percentage,
            ];
        }

        return collect($classStats)->sortByDesc('percentage')->take(5)->values();
    }

    private function getTodayAttendanceByHour()
    {
        $today = Carbon::today();
        $attendances = Attendance::whereDate('date', $today)
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

    private function getWorkingDaysInMonth($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $workingDays = 0;

        // Get all holiday dates in this range
        $holidayDates = \App\Models\AcademicCalendar::getHolidayDates($startDate, $endDate);

        while ($startDate <= $endDate) {
            // Count weekdays only (Monday-Friday) and exclude holidays
            if ($startDate->isWeekday() && !in_array($startDate->format('Y-m-d'), $holidayDates)) {
                $workingDays++;
            }
            $startDate->addDay();
        }

        return $workingDays;
    }
}
