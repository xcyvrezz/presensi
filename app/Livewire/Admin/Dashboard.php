<?php

namespace App\Livewire\Admin;

use App\Models\AbsenceRequest;
use App\Models\AcademicCalendar;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use App\Services\AttendanceSummaryService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Admin Dashboard')]
class Dashboard extends Component
{
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
        $periodSummary = $summaryService->getAggregateSummary(
            Student::active(),
            $period['start_date'],
            $period['end_date']
        );

        $systemStats = $this->getSystemStatistics();
        $todayStats = $this->getTodayStatistics();
        $userStats = $this->getUserStatistics();
        $recentUsers = $this->getRecentUsers();
        $recentStudents = $this->getRecentStudents();
        $recentAttendances = $this->getRecentAttendances();
        $pendingRequests = $this->getPendingRequests();
        $weeklyTrend = $this->getWeeklyAttendanceTrend();
        $systemHealth = $this->getSystemHealth();
        $topClasses = $this->getTopClassesByPeriod($summaryService, $period);

        return view('livewire.admin.dashboard', compact(
            'isHoliday',
            'holidayInfo',
            'systemStats',
            'todayStats',
            'userStats',
            'recentUsers',
            'recentStudents',
            'recentAttendances',
            'pendingRequests',
            'weeklyTrend',
            'systemHealth',
            'period',
            'periodSummary',
            'topClasses'
        ));
    }

    private function getSystemStatistics()
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_students' => Student::count(),
            'active_students' => Student::where('is_active', true)->count(),
            'total_classes' => Classes::count(),
            'total_departments' => Department::count(),
            'total_attendance_records' => Attendance::count(),
            'total_attendance_today' => Attendance::whereDate('date', Carbon::today())->count(),
        ];
    }

    private function getTodayStatistics()
    {
        $today = Carbon::today();
        $activeStudents = Student::where('is_active', true)->count();
        $todayAttendances = Attendance::whereDate('date', $today)->get();
        $totalPresent = $todayAttendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalAlpha = $todayAttendances->where('status', 'alpha')->count();
        $totalBelumAbsen = max($activeStudents - $todayAttendances->count(), 0);

        return [
            'total_present' => $totalPresent,
            'total_hadir' => $todayAttendances->where('status', 'hadir')->count(),
            'total_terlambat' => $todayAttendances->where('status', 'terlambat')->count(),
            'total_izin' => $todayAttendances->where('status', 'izin')->count(),
            'total_sakit' => $todayAttendances->where('status', 'sakit')->count(),
            'total_alpha' => $totalAlpha,
            'total_bolos' => $todayAttendances->where('status', 'bolos')->count(),
            'total_dispensasi' => $todayAttendances->where('status', 'dispensasi')->count(),
            'total_absent' => $totalBelumAbsen,
            'attendance_percentage' => $activeStudents > 0 ? round(($totalPresent / $activeStudents) * 100, 1) : 0,
        ];
    }

    private function getUserStatistics()
    {
        $users = User::with('role')->get();

        return [
            'total' => $users->count(),
            'admins' => $users->filter(fn ($u) => $u->role && $u->role->name === 'admin')->count(),
            'kepala_sekolah' => $users->filter(fn ($u) => $u->role && $u->role->name === 'kepala_sekolah')->count(),
            'wali_kelas' => $users->filter(fn ($u) => $u->role && $u->role->name === 'wali_kelas')->count(),
            'siswa' => $users->filter(fn ($u) => $u->role && $u->role->name === 'siswa')->count(),
            'active' => $users->where('is_active', true)->count(),
            'inactive' => $users->where('is_active', false)->count(),
        ];
    }

    private function getRecentUsers()
    {
        return User::with('role')->orderBy('created_at', 'desc')->limit(5)->get();
    }

    private function getRecentStudents()
    {
        return Student::with(['class.department', 'user'])->orderBy('created_at', 'desc')->limit(5)->get();
    }

    private function getRecentAttendances()
    {
        return Attendance::with(['student.class'])->orderBy('check_in_time', 'desc')->limit(10)->get();
    }

    private function getPendingRequests()
    {
        return AbsenceRequest::with(['student.class', 'student.user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function getWeeklyAttendanceTrend()
    {
        $weeklyData = [];
        $startOfWeek = Carbon::now()->startOfWeek();
        $activeStudents = Student::where('is_active', true)->count();

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $attendances = Attendance::whereDate('date', $date)->get();
            $present = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();
            $alpha = $attendances->where('status', 'alpha')->count();
            $notRecorded = max($activeStudents - $attendances->count(), 0);

            $weeklyData[] = [
                'day' => $date->format('D'),
                'date' => $date->format('d'),
                'full_date' => $date->format('Y-m-d'),
                'hadir' => $attendances->where('status', 'hadir')->count(),
                'terlambat' => $attendances->where('status', 'terlambat')->count(),
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'alpha' => $alpha,
                'belum_absen' => $notRecorded,
                'dispensasi' => $attendances->where('status', 'dispensasi')->count(),
                'total_present' => $present,
                'percentage' => $activeStudents > 0 ? round(($present / $activeStudents) * 100, 1) : 0,
                'is_today' => $date->isToday(),
            ];
        }

        return $weeklyData;
    }

    private function getSystemHealth()
    {
        $totalStudents = Student::where('is_active', true)->count();
        $studentsWithNFC = Student::where('is_active', true)->where('nfc_enabled', true)->count();
        $studentsWithUser = Student::whereNotNull('user_id')->count();
        $classesWithWaliKelas = Classes::whereNotNull('wali_kelas_id')->count();
        $totalClasses = Classes::count();
        $studentsComplete = Student::where('is_active', true)
            ->whereNotNull('nis')
            ->whereNotNull('nisn')
            ->whereNotNull('full_name')
            ->whereNotNull('class_id')
            ->count();

        $nfcPercentage = $totalStudents > 0 ? round(($studentsWithNFC / $totalStudents) * 100, 1) : 0;
        $userAccountPercentage = $totalStudents > 0 ? round(($studentsWithUser / $totalStudents) * 100, 1) : 0;
        $waliKelasPercentage = $totalClasses > 0 ? round(($classesWithWaliKelas / $totalClasses) * 100, 1) : 0;
        $dataCompletenessPercentage = $totalStudents > 0 ? round(($studentsComplete / $totalStudents) * 100, 1) : 0;
        $overallHealth = round(($nfcPercentage + $userAccountPercentage + $waliKelasPercentage + $dataCompletenessPercentage) / 4, 1);

        return [
            'overall' => $overallHealth,
            'nfc_enabled' => $nfcPercentage,
            'user_accounts' => $userAccountPercentage,
            'wali_kelas_assigned' => $waliKelasPercentage,
            'data_completeness' => $dataCompletenessPercentage,
            'status' => $overallHealth >= 90 ? 'excellent' : ($overallHealth >= 75 ? 'good' : ($overallHealth >= 50 ? 'fair' : 'poor')),
        ];
    }

    private function getTopClassesByPeriod(AttendanceSummaryService $summaryService, array $period)
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

                $summary = $summaryService->getAggregateSummary(
                    $studentQuery,
                    $period['start_date'],
                    $period['end_date'],
                    $class->department_id,
                    $class->id
                );

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
}
