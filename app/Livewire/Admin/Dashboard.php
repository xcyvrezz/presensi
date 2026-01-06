<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Student;
use App\Models\Classes;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\AbsenceRequest;
use App\Models\Semester;
use App\Models\AcademicCalendar;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.admin')]
#[Title('Admin Dashboard')]
class Dashboard extends Component
{
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

        $systemStats = $this->getSystemStatistics();
        $todayStats = $this->getTodayStatistics();
        $userStats = $this->getUserStatistics();
        $recentUsers = $this->getRecentUsers();
        $recentStudents = $this->getRecentStudents();
        $recentAttendances = $this->getRecentAttendances();
        $pendingRequests = $this->getPendingRequests();
        $weeklyTrend = $this->getWeeklyAttendanceTrend();
        $systemHealth = $this->getSystemHealth();

        return view('livewire.admin.dashboard', [
            'isHoliday' => $isHoliday,
            'holidayInfo' => $holidayInfo,
            'systemStats' => $systemStats,
            'todayStats' => $todayStats,
            'userStats' => $userStats,
            'recentUsers' => $recentUsers,
            'recentStudents' => $recentStudents,
            'recentAttendances' => $recentAttendances,
            'pendingRequests' => $pendingRequests,
            'weeklyTrend' => $weeklyTrend,
            'systemHealth' => $systemHealth,
        ]);
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

        // Ambil semua attendance hari ini
        $todayAttendances = Attendance::whereDate('date', $today)->get();

        // KONSISTEN: Present = hanya hadir + terlambat (yang benar-benar datang ke sekolah)
        $totalPresent = $todayAttendances->whereIn('status', ['hadir', 'terlambat'])->count();

        // Hitung per status
        $totalHadir = $todayAttendances->where('status', 'hadir')->count();
        $totalTerlambat = $todayAttendances->where('status', 'terlambat')->count();
        $totalIzin = $todayAttendances->where('status', 'izin')->count();
        $totalSakit = $todayAttendances->where('status', 'sakit')->count();
        $totalDispensasi = $todayAttendances->where('status', 'dispensasi')->count();
        $totalBolos = $todayAttendances->where('status', 'bolos')->count();

        // LOGIC BARU: Alpha = record yang sudah di-generate dengan status 'alpha'
        $totalAlpha = $todayAttendances->where('status', 'alpha')->count();

        // Belum absen = siswa yang belum punya record sama sekali
        $totalBelumAbsen = $activeStudents - $todayAttendances->count();

        return [
            'total_present' => $totalPresent,
            'total_hadir' => $totalHadir,
            'total_terlambat' => $totalTerlambat,
            'total_izin' => $totalIzin,
            'total_sakit' => $totalSakit,
            'total_alpha' => $totalAlpha, // Record dengan status alpha
            'total_bolos' => $totalBolos,
            'total_dispensasi' => $totalDispensasi,
            'total_absent' => $totalBelumAbsen, // Belum absen sama sekali
            'attendance_percentage' => $activeStudents > 0
                ? round(($totalPresent / $activeStudents) * 100, 1)
                : 0,
        ];
    }

    private function getUserStatistics()
    {
        $users = User::with('role')->get();

        return [
            'total' => $users->count(),
            'admins' => $users->filter(fn($u) => $u->role && $u->role->name === 'admin')->count(),
            'kepala_sekolah' => $users->filter(fn($u) => $u->role && $u->role->name === 'kepala_sekolah')->count(),
            'wali_kelas' => $users->filter(fn($u) => $u->role && $u->role->name === 'wali_kelas')->count(),
            'siswa' => $users->filter(fn($u) => $u->role && $u->role->name === 'siswa')->count(),
            'active' => $users->where('is_active', true)->count(),
            'inactive' => $users->where('is_active', false)->count(),
        ];
    }

    private function getRecentUsers()
    {
        return User::with('role')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function getRecentStudents()
    {
        return Student::with(['class.department', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function getRecentAttendances()
    {
        return Attendance::with(['student.class'])
            ->orderBy('check_in_time', 'desc')
            ->limit(10)
            ->get();
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

            // Present = hadir + terlambat
            $present = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();

            // Alpha = siswa yang tidak punya record
            $absent = $activeStudents - $attendances->count();

            $percentage = $activeStudents > 0 ? round(($present / $activeStudents) * 100, 1) : 0;

            $weeklyData[] = [
                'day' => $date->format('D'),
                'date' => $date->format('d'),
                'full_date' => $date->format('Y-m-d'),
                'hadir' => $attendances->where('status', 'hadir')->count(),
                'terlambat' => $attendances->where('status', 'terlambat')->count(),
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'alpha' => $absent,
                'dispensasi' => $attendances->where('status', 'dispensasi')->count(),
                'total_present' => $present,
                'percentage' => $percentage,
                'is_today' => $date->isToday(),
            ];
        }

        return $weeklyData;
    }

    private function getSystemHealth()
    {
        // Calculate various health metrics
        $totalStudents = Student::where('is_active', true)->count();
        $studentsWithNFC = Student::where('is_active', true)->where('nfc_enabled', true)->count();
        $studentsWithUser = Student::whereNotNull('user_id')->count();

        $classesWithWaliKelas = Classes::whereNotNull('wali_kelas_id')->count();
        $totalClasses = Classes::count();

        // Calculate data completeness
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

        // Overall system health (average of all metrics)
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
}
