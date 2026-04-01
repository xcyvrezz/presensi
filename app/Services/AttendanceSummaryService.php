<?php

namespace App\Services;

use App\Models\AcademicCalendar;
use App\Models\Attendance;
use App\Models\Semester;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class AttendanceSummaryService
{
    public const PRESENT_STATUSES = [
        'hadir',
        'terlambat',
        'dispensasi',
        'pulang_cepat',
        'izin_terlambat',
        'izin_pulang_cepat',
    ];

    public function getActivePeriod(?Semester $semester = null): array
    {
        $semester = $semester ?: Semester::active()->first();
        $today = Carbon::today();

        if ($semester) {
            $startDate = Carbon::parse($semester->start_date)->startOfDay();
            $endDate = Carbon::parse($semester->end_date)->startOfDay()->min($today);
        } else {
            $startDate = $today->copy()->startOfMonth();
            $endDate = $today->copy();
        }

        if ($startDate->gt($endDate)) {
            $startDate = $endDate->copy();
        }

        return [
            'semester' => $semester,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'label' => $semester
                ? $semester->name . ' (' . $startDate->translatedFormat('d M Y') . ' - ' . $endDate->translatedFormat('d M Y') . ')'
                : 'Periode berjalan',
        ];
    }

    public function getEffectiveDates(Carbon $startDate, Carbon $endDate, ?int $departmentId = null, ?int $classId = null): Collection
    {
        if ($startDate->gt($endDate)) {
            return collect();
        }

        $excludedDates = AcademicCalendar::getHolidayDates($startDate, $endDate, $departmentId, $classId);
        $excludedLookup = array_flip($excludedDates);

        return collect(CarbonPeriod::create($startDate, $endDate))
            ->filter(function (Carbon $date) use ($excludedLookup) {
                return !$date->isWeekend() && !isset($excludedLookup[$date->format('Y-m-d')]);
            })
            ->values();
    }

    public function getStudentSummary(int $studentId, Carbon $startDate, Carbon $endDate, ?int $departmentId = null, ?int $classId = null): array
    {
        $effectiveDates = $this->getEffectiveDates($startDate, $endDate, $departmentId, $classId);
        $attendances = Attendance::where('student_id', $studentId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        return $this->buildSummary($attendances, 1, $effectiveDates->count(), $effectiveDates);
    }

    public function getAggregateSummary(Builder|Relation $studentQuery, Carbon $startDate, Carbon $endDate, ?int $departmentId = null, ?int $classId = null): array
    {
        $students = (clone $studentQuery)->select('id')->get();
        $studentIds = $students->pluck('id');
        $studentCount = $studentIds->count();
        $effectiveDates = $this->getEffectiveDates($startDate, $endDate, $departmentId, $classId);
        $effectiveDays = $effectiveDates->count();

        $attendances = $studentCount > 0
            ? Attendance::whereIn('student_id', $studentIds)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get()
            : collect();

        return $this->buildSummary($attendances, $studentCount, $effectiveDays, $effectiveDates);
    }

    private function buildSummary(Collection $attendances, int $studentCount, int $effectiveDays, Collection $effectiveDates): array
    {
        $expectedRecords = $studentCount * $effectiveDays;
        $presentCount = $attendances->whereIn('status', self::PRESENT_STATUSES)->count();
        $alphaCount = $attendances->where('status', 'alpha')->count();
        $bolosCount = $attendances->where('status', 'bolos')->count();
        $attendanceRate = $expectedRecords > 0 ? round(($presentCount / $expectedRecords) * 100, 1) : 0;

        return [
            'effective_days' => $effectiveDays,
            'effective_dates' => $effectiveDates,
            'expected_records' => $expectedRecords,
            'present_count' => $presentCount,
            'hadir_count' => $attendances->where('status', 'hadir')->count(),
            'late_count' => $attendances->where('status', 'terlambat')->count(),
            'dispensation_count' => $attendances->where('status', 'dispensasi')->count(),
            'permit_count' => $attendances->where('status', 'izin')->count(),
            'sick_count' => $attendances->where('status', 'sakit')->count(),
            'alpha_count' => $alphaCount,
            'bolos_count' => $bolosCount,
            'recorded_count' => $attendances->count(),
            'missing_records' => max($expectedRecords - $attendances->count(), 0),
            'attendance_rate' => $attendanceRate,
            'student_count' => $studentCount,
        ];
    }
}
