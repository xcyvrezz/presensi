<?php

namespace App\Livewire\Student;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\Semester;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Layout('layouts.student')]
#[Title('Statistik Absensi')]
class Statistics extends Component
{
    public $student;
    public $selectedPeriod = 'semester'; // semester, year, all

    public function mount()
    {
        $this->student = Student::where('user_id', auth()->id())
            ->with(['class.department'])
            ->first();

        if (!$this->student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }
    }

    public function render()
    {
        $overallStats = $this->getOverallStatistics();
        $monthlyData = $this->getMonthlyData();
        $statusDistribution = $this->getStatusDistribution();

        return view('livewire.student.statistics', [
            'overallStats' => $overallStats,
            'monthlyData' => $monthlyData,
            'statusDistribution' => $statusDistribution,
        ]);
    }

    private function getOverallStatistics()
    {
        $query = Attendance::where('student_id', $this->student->id);

        // Get date range based on period filter
        $startDate = null;
        $endDate = Carbon::today();

        if ($this->selectedPeriod === 'semester') {
            $activeSemester = Semester::where('is_active', true)->first();
            if ($activeSemester) {
                $query->where('semester_id', $activeSemester->id);
                $startDate = Carbon::parse($activeSemester->start_date);
                // If semester end date is before today, use end date, otherwise use today
                $semesterEnd = Carbon::parse($activeSemester->end_date);
                $endDate = $semesterEnd->lt($endDate) ? $semesterEnd : $endDate;
            }
        } elseif ($this->selectedPeriod === 'year') {
            $query->whereYear('date', Carbon::now()->year);
            $startDate = Carbon::now()->startOfYear();
        }

        $attendances = $query->get();

        // Calculate total working days (Monday-Friday only, excluding holidays)
        $totalWorkingDays = 0;
        if ($startDate) {
            // Get all holiday dates in this range
            $holidayDates = \App\Models\AcademicCalendar::getHolidayDates($startDate, $endDate);

            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                // Count weekdays only (Monday-Friday) and exclude holidays
                if ($current->isWeekday() && !in_array($current->format('Y-m-d'), $holidayDates)) {
                    $totalWorkingDays++;
                }
                $current->addDay();
            }
        } else {
            $totalWorkingDays = $attendances->count();
        }

        $totalDays = $attendances->count();

        // UPDATED: Include Dispensasi in positive attendance (hadir + terlambat + dispensasi)
        $presentDays = $attendances->whereIn('status', ['hadir', 'terlambat', 'dispensasi'])->count();

        // Calculate percentage based on total working days that have passed
        $percentage = $totalWorkingDays > 0 ? round(($presentDays / $totalWorkingDays) * 100, 1) : 0;

        // Calculate lupa checkout
        $lupaCheckout = $attendances->filter(function($attendance) {
            return $attendance->check_in_time
                && !$attendance->check_out_time
                && in_array($attendance->status, ['hadir', 'terlambat']);
        })->count();

        return [
            'total' => $totalDays,
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
            'dispensasi' => $attendances->where('status', 'dispensasi')->count(),
            'bolos' => $attendances->where('status', 'bolos')->count(),
            'pulang_cepat' => $attendances->where('status', 'pulang_cepat')->count(),
            'lupa_checkout' => $lupaCheckout,
            'percentage' => $percentage,
            'presentDays' => $presentDays,
            'totalWorkingDays' => $totalWorkingDays,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    private function getMonthlyData()
    {
        $year = Carbon::now()->year;
        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            $attendances = Attendance::where('student_id', $this->student->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();

            $monthlyData[] = [
                'month' => Carbon::create($year, $month, 1)->format('M'),
                'total' => $attendances->count(),
                'hadir' => $attendances->where('status', 'hadir')->count(),
                'terlambat' => $attendances->where('status', 'terlambat')->count(),
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'dispensasi' => $attendances->where('status', 'dispensasi')->count(),
                'alpha' => $attendances->where('status', 'alpha')->count(),
                'bolos' => $attendances->where('status', 'bolos')->count(),
                'pulang_cepat' => $attendances->where('status', 'pulang_cepat')->count(),
            ];
        }

        return $monthlyData;
    }

    private function getStatusDistribution()
    {
        $query = Attendance::where('student_id', $this->student->id);

        if ($this->selectedPeriod === 'semester') {
            $activeSemester = Semester::where('is_active', true)->first();
            if ($activeSemester) {
                $query->where('semester_id', $activeSemester->id);
            }
        } elseif ($this->selectedPeriod === 'year') {
            $query->whereYear('date', Carbon::now()->year);
        }

        $attendances = $query->get();
        $total = $attendances->count();

        if ($total === 0) {
            return [
                ['status' => 'Hadir', 'count' => 0, 'percentage' => 0, 'color' => '#10b981'],
                ['status' => 'Terlambat', 'count' => 0, 'percentage' => 0, 'color' => '#f59e0b'],
                ['status' => 'Izin', 'count' => 0, 'percentage' => 0, 'color' => '#3b82f6'],
                ['status' => 'Sakit', 'count' => 0, 'percentage' => 0, 'color' => '#a855f7'],
                ['status' => 'Dispensasi', 'count' => 0, 'percentage' => 0, 'color' => '#06b6d4'],
                ['status' => 'Alpha', 'count' => 0, 'percentage' => 0, 'color' => '#64748b'],
                ['status' => 'Bolos', 'count' => 0, 'percentage' => 0, 'color' => '#ef4444'],
                ['status' => 'Pulang Cepat', 'count' => 0, 'percentage' => 0, 'color' => '#f97316'],
                ['status' => 'Lupa Checkout', 'count' => 0, 'percentage' => 0, 'color' => '#f59e0b'],
            ];
        }

        // Calculate lupa checkout
        $lupaCheckout = $attendances->filter(function($attendance) {
            return $attendance->check_in_time
                && !$attendance->check_out_time
                && in_array($attendance->status, ['hadir', 'terlambat']);
        })->count();

        return [
            [
                'status' => 'Hadir',
                'count' => $attendances->where('status', 'hadir')->count(),
                'percentage' => round(($attendances->where('status', 'hadir')->count() / $total) * 100, 1),
                'color' => '#10b981' // green
            ],
            [
                'status' => 'Terlambat',
                'count' => $attendances->where('status', 'terlambat')->count(),
                'percentage' => round(($attendances->where('status', 'terlambat')->count() / $total) * 100, 1),
                'color' => '#f59e0b' // yellow
            ],
            [
                'status' => 'Izin',
                'count' => $attendances->where('status', 'izin')->count(),
                'percentage' => round(($attendances->where('status', 'izin')->count() / $total) * 100, 1),
                'color' => '#3b82f6' // blue
            ],
            [
                'status' => 'Sakit',
                'count' => $attendances->where('status', 'sakit')->count(),
                'percentage' => round(($attendances->where('status', 'sakit')->count() / $total) * 100, 1),
                'color' => '#a855f7' // purple
            ],
            [
                'status' => 'Dispensasi',
                'count' => $attendances->where('status', 'dispensasi')->count(),
                'percentage' => round(($attendances->where('status', 'dispensasi')->count() / $total) * 100, 1),
                'color' => '#06b6d4' // cyan
            ],
            [
                'status' => 'Alpha',
                'count' => $attendances->where('status', 'alpha')->count(),
                'percentage' => round(($attendances->where('status', 'alpha')->count() / $total) * 100, 1),
                'color' => '#64748b' // slate
            ],
            [
                'status' => 'Bolos',
                'count' => $attendances->where('status', 'bolos')->count(),
                'percentage' => round(($attendances->where('status', 'bolos')->count() / $total) * 100, 1),
                'color' => '#ef4444' // red
            ],
            [
                'status' => 'Pulang Cepat',
                'count' => $attendances->where('status', 'pulang_cepat')->count(),
                'percentage' => round(($attendances->where('status', 'pulang_cepat')->count() / $total) * 100, 1),
                'color' => '#f97316' // orange
            ],
            [
                'status' => 'Lupa Checkout',
                'count' => $lupaCheckout,
                'percentage' => round(($lupaCheckout / $total) * 100, 1),
                'color' => '#f59e0b' // amber
            ],
        ];
    }
}
