<?php

namespace App\Livewire\WaliKelas;

use App\Models\Classes;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Semester;
use App\Models\AcademicCalendar;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

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

    public function mount()
    {
        // Check if today is a holiday
        $this->isHoliday = AcademicCalendar::isHoliday(Carbon::today());

        if ($this->isHoliday) {
            $today = Carbon::today()->format('Y-m-d');

            $this->holidayInfo = AcademicCalendar::where('is_holiday', true)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
        }

        // Get class yang diampu oleh wali kelas ini
        $this->class = Classes::where('wali_kelas_id', auth()->id())
            ->with(['department', 'students'])
            ->first();

        if (!$this->class) {
            return;
        }

        // Hitung statistik
        $this->totalStudents = $this->class->students()->where('is_active', true)->count();

        // Statistik hari ini - KONSISTEN
        $today = Carbon::today();
        $activeStudentIds = $this->class->students()->where('is_active', true)->pluck('id');

        $attendances = Attendance::whereIn('student_id', $activeStudentIds)
            ->whereDate('date', $today)
            ->get();

        // Present = hadir + terlambat (yang benar-benar datang)
        $hadirCount = $attendances->where('status', 'hadir')->count();
        $this->lateToday = $attendances->where('status', 'terlambat')->count();
        $this->presentToday = $hadirCount + $this->lateToday;

        // LOGIC BARU: Alpha = record yang sudah di-generate dengan status 'alpha'
        $alphaCount = $attendances->where('status', 'alpha')->count();

        // Belum absen = siswa yang belum punya record sama sekali
        $this->absentToday = $this->totalStudents - $attendances->count();

        if ($this->totalStudents > 0) {
            $this->attendancePercentage = round(($this->presentToday / $this->totalStudents) * 100, 1);
        }
    }

    public function render()
    {
        $recentAttendances = [];

        if ($this->class) {
            // Ambil absensi terbaru hari ini
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
