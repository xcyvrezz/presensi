<?php

namespace App\Livewire\Student;

use App\Models\Attendance;
use App\Models\Semester;
use App\Models\Student;
use App\Services\AttendanceSummaryService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.student')]
#[Title('Statistik Absensi')]
class Statistics extends Component
{
    public $student;
    public $selectedPeriod = 'semester';

    public function mount()
    {
        $this->student = Student::where('user_id', auth()->id())
            ->with(['class.department'])
            ->first();

        if (!$this->student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }
    }

    public function render(AttendanceSummaryService $summaryService)
    {
        $overallStats = $this->getOverallStatistics($summaryService);
        $monthlyData = $this->getMonthlyData();
        $statusDistribution = $this->getStatusDistribution();

        return view('livewire.student.statistics', compact('overallStats', 'monthlyData', 'statusDistribution'));
    }

    private function resolvePeriodDates(): array
    {
        $startDate = null;
        $endDate = Carbon::today();
        $semesterId = null;

        if ($this->selectedPeriod === 'semester') {
            $activeSemester = Semester::where('is_active', true)->first();
            if ($activeSemester) {
                $semesterId = $activeSemester->id;
                $startDate = Carbon::parse($activeSemester->start_date);
                $semesterEnd = Carbon::parse($activeSemester->end_date);
                $endDate = $semesterEnd->lt($endDate) ? $semesterEnd : $endDate;
            }
        } elseif ($this->selectedPeriod === 'year') {
            $startDate = Carbon::now()->startOfYear();
        }

        return [$startDate, $endDate, $semesterId];
    }

    private function getOverallStatistics(AttendanceSummaryService $summaryService)
    {
        [$startDate, $endDate, $semesterId] = $this->resolvePeriodDates();

        $query = Attendance::where('student_id', $this->student->id);
        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        } elseif ($this->selectedPeriod === 'year') {
            $query->whereYear('date', Carbon::now()->year);
        }

        $attendances = $query->get();
        $summary = $startDate
            ? $summaryService->getStudentSummary($this->student->id, $startDate, $endDate, $this->student->class->department_id ?? null, $this->student->class_id)
            : null;

        $totalWorkingDays = $summary['effective_days'] ?? $attendances->count();
        $presentDays = $summary['present_count'] ?? $attendances->whereIn('status', ['hadir', 'terlambat', 'dispensasi'])->count();
        $percentage = $totalWorkingDays > 0 ? round(($presentDays / $totalWorkingDays) * 100, 1) : 0;

        $lupaCheckout = $attendances->filter(fn ($attendance) => $attendance->check_in_time && !$attendance->check_out_time && in_array($attendance->status, ['hadir', 'terlambat']))->count();

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
        [$startDate, $endDate, $semesterId] = $this->resolvePeriodDates();
        $query = Attendance::where('student_id', $this->student->id);

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        } elseif ($this->selectedPeriod === 'year') {
            $query->whereYear('date', Carbon::now()->year);
        }

        $attendances = $query->get();
        $total = $attendances->count();

        if ($total === 0) {
            return collect(['Hadir' => '#10b981', 'Terlambat' => '#f59e0b', 'Izin' => '#3b82f6', 'Sakit' => '#a855f7', 'Dispensasi' => '#06b6d4', 'Alpha' => '#64748b', 'Bolos' => '#ef4444', 'Pulang Cepat' => '#f97316', 'Lupa Checkout' => '#f59e0b'])
                ->map(fn ($color, $status) => ['status' => $status, 'count' => 0, 'percentage' => 0, 'color' => $color])
                ->values()
                ->all();
        }

        $lupaCheckout = $attendances->filter(fn ($attendance) => $attendance->check_in_time && !$attendance->check_out_time && in_array($attendance->status, ['hadir', 'terlambat']))->count();

        return [
            ['status' => 'Hadir', 'count' => $attendances->where('status', 'hadir')->count(), 'percentage' => round(($attendances->where('status', 'hadir')->count() / $total) * 100, 1), 'color' => '#10b981'],
            ['status' => 'Terlambat', 'count' => $attendances->where('status', 'terlambat')->count(), 'percentage' => round(($attendances->where('status', 'terlambat')->count() / $total) * 100, 1), 'color' => '#f59e0b'],
            ['status' => 'Izin', 'count' => $attendances->where('status', 'izin')->count(), 'percentage' => round(($attendances->where('status', 'izin')->count() / $total) * 100, 1), 'color' => '#3b82f6'],
            ['status' => 'Sakit', 'count' => $attendances->where('status', 'sakit')->count(), 'percentage' => round(($attendances->where('status', 'sakit')->count() / $total) * 100, 1), 'color' => '#a855f7'],
            ['status' => 'Dispensasi', 'count' => $attendances->where('status', 'dispensasi')->count(), 'percentage' => round(($attendances->where('status', 'dispensasi')->count() / $total) * 100, 1), 'color' => '#06b6d4'],
            ['status' => 'Alpha', 'count' => $attendances->where('status', 'alpha')->count(), 'percentage' => round(($attendances->where('status', 'alpha')->count() / $total) * 100, 1), 'color' => '#64748b'],
            ['status' => 'Bolos', 'count' => $attendances->where('status', 'bolos')->count(), 'percentage' => round(($attendances->where('status', 'bolos')->count() / $total) * 100, 1), 'color' => '#ef4444'],
            ['status' => 'Pulang Cepat', 'count' => $attendances->where('status', 'pulang_cepat')->count(), 'percentage' => round(($attendances->where('status', 'pulang_cepat')->count() / $total) * 100, 1), 'color' => '#f97316'],
            ['status' => 'Lupa Checkout', 'count' => $lupaCheckout, 'percentage' => round(($lupaCheckout / $total) * 100, 1), 'color' => '#f59e0b'],
        ];
    }
}
