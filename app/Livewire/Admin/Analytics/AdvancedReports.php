<?php

namespace App\Livewire\Admin\Analytics;

use App\Exports\AttendanceExport;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Department;
use App\Models\Semester;
use App\Models\Student;
use App\Services\AttendanceSummaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.admin')]
#[Title('Analitik & Laporan Lanjutan')]
class AdvancedReports extends Component
{
    public $selectedDepartment = '';
    public $selectedClass = '';
    public $selectedStudent = '';
    public $selectedSemester = '';
    public $statistics = [];
    public $topDiligentStudents = [];
    public $topAlphaStudents = [];
    public $topLateStudents = [];
    public $bestClasses = [];
    public $attentionClasses = [];
    public $studentDetail = null;
    public $weeklyTrend = [];
    public $statusDistribution = [];

    public function mount()
    {
        $activeSemester = Semester::where('is_active', true)->first();
        if ($activeSemester) {
            $this->selectedSemester = $activeSemester->id;
        }
        $this->loadAnalytics();
    }

    public function updatedSelectedSemester() { $this->loadAnalytics(); }
    public function updatedSelectedDepartment() { $this->selectedClass = ''; $this->selectedStudent = ''; $this->loadAnalytics(); }
    public function updatedSelectedClass() { $this->selectedStudent = ''; $this->loadAnalytics(); }
    public function updatedSelectedStudent() { $this->loadAnalytics(); }

    public function loadAnalytics()
    {
        $semester = Semester::find($this->selectedSemester);
        if (!$semester) return;

        $startDate = Carbon::parse($semester->start_date);
        $endDate = Carbon::parse($semester->end_date);

        $this->loadGeneralStatistics($startDate, $endDate);
        $this->loadStudentRankings($startDate, $endDate);
        $this->loadClassRankings($startDate, $endDate);
        $this->loadWeeklyTrend($startDate, $endDate);
        $this->loadStatusDistribution($startDate, $endDate);
        $this->studentDetail = $this->selectedStudent ? $this->buildStudentDetail($startDate, $endDate) : null;
    }

    private function scopeStudentQuery()
    {
        return Student::where('is_active', true)
            ->when($this->selectedClass, fn ($q) => $q->where('class_id', $this->selectedClass))
            ->when($this->selectedDepartment && !$this->selectedClass, fn ($q) => $q->whereHas('class', fn ($sq) => $sq->where('department_id', $this->selectedDepartment)));
    }

    private function scopedWorkingDays($startDate, $endDate, ?int $departmentId = null, ?int $classId = null): int
    {
        return app(AttendanceSummaryService::class)->getEffectiveDates($startDate, $endDate, $departmentId, $classId)->count();
    }

    private function loadGeneralStatistics($startDate, $endDate)
    {
        $query = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        if ($this->selectedClass) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $this->selectedClass));
        } elseif ($this->selectedDepartment) {
            $query->whereHas('student.class', fn ($q) => $q->where('department_id', $this->selectedDepartment));
        }

        $attendances = $query->get();
        $totalStudents = $this->scopeStudentQuery()->count();
        $workingDays = $this->scopedWorkingDays($startDate, $endDate, $this->selectedDepartment ?: null, $this->selectedClass ?: null);
        $expectedRecords = $totalStudents * $workingDays;

        $this->statistics = [
            'total_students' => $totalStudents,
            'working_days' => $workingDays,
            'total_records' => $attendances->count(),
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
            'dispensasi' => $attendances->where('status', 'dispensasi')->count(),
            'bolos' => $attendances->where('status', 'bolos')->count(),
            'avg_late_minutes' => round($attendances->where('status', 'terlambat')->avg('late_minutes') ?? 0, 1),
            'attendance_rate' => $expectedRecords > 0 ? round((($attendances->whereIn('status', ['hadir', 'terlambat', 'dispensasi'])->count()) / $expectedRecords) * 100, 1) : 0,
        ];
    }

    private function loadWeeklyTrend($startDate, $endDate)
    {
        $weeks = [];
        $currentWeekStart = Carbon::now()->startOfWeek();
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = $currentWeekStart->copy()->subWeeks($i)->max($startDate);
            $weekEnd = $weekStart->copy()->endOfWeek()->min($endDate);
            $query = Attendance::whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')]);
            if ($this->selectedClass) {
                $query->whereHas('student', fn ($q) => $q->where('class_id', $this->selectedClass));
            } elseif ($this->selectedDepartment) {
                $query->whereHas('student.class', fn ($q) => $q->where('department_id', $this->selectedDepartment));
            }
            $attendances = $query->get();
            $weeks[] = ['label' => 'Minggu ' . $weekStart->format('d/m'), 'hadir' => $attendances->where('status', 'hadir')->count(), 'terlambat' => $attendances->where('status', 'terlambat')->count(), 'alpha' => $attendances->where('status', 'alpha')->count()];
        }
        $this->weeklyTrend = $weeks;
    }

    private function loadStatusDistribution($startDate, $endDate)
    {
        $query = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        if ($this->selectedClass) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $this->selectedClass));
        } elseif ($this->selectedDepartment) {
            $query->whereHas('student.class', fn ($q) => $q->where('department_id', $this->selectedDepartment));
        }
        $attendances = $query->get();

        $this->statusDistribution = [
            ['status' => 'Hadir', 'count' => $attendances->where('status', 'hadir')->count(), 'color' => 'green'],
            ['status' => 'Terlambat', 'count' => $attendances->where('status', 'terlambat')->count(), 'color' => 'yellow'],
            ['status' => 'Izin', 'count' => $attendances->where('status', 'izin')->count(), 'color' => 'blue'],
            ['status' => 'Sakit', 'count' => $attendances->where('status', 'sakit')->count(), 'color' => 'purple'],
            ['status' => 'Alpha', 'count' => $attendances->where('status', 'alpha')->count(), 'color' => 'gray'],
            ['status' => 'Dispensasi', 'count' => $attendances->where('status', 'dispensasi')->count(), 'color' => 'cyan'],
            ['status' => 'Bolos', 'count' => $attendances->where('status', 'bolos')->count(), 'color' => 'red'],
        ];
    }

    private function loadStudentRankings($startDate, $endDate)
    {
        $query = $this->scopeStudentQuery();
        $workingDays = $this->scopedWorkingDays($startDate, $endDate, $this->selectedDepartment ?: null, $this->selectedClass ?: null);

        $this->topDiligentStudents = (clone $query)->with('class')->withCount([
            'attendances as hadir_count' => fn ($q) => $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->where('status', 'hadir'),
            'attendances as total_attendance' => fn ($q) => $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->whereIn('status', ['hadir', 'terlambat', 'dispensasi'])
        ])->get()->map(function ($student) use ($workingDays) {
            $student->attendance_rate = $workingDays > 0 ? round(($student->total_attendance / $workingDays) * 100, 1) : 0;
            return $student;
        })->sortByDesc(fn ($student) => ($student->attendance_rate * 100) + $student->hadir_count)->take(10)->values();

        $this->topAlphaStudents = (clone $query)->with('class')->withCount(['attendances as alpha_count' => fn ($q) => $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->where('status', 'alpha')])->having('alpha_count', '>', 0)->orderBy('alpha_count', 'desc')->limit(10)->get();
        $this->topLateStudents = (clone $query)->with('class')->withCount(['attendances as late_count' => fn ($q) => $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->where('status', 'terlambat')])->having('late_count', '>', 0)->orderBy('late_count', 'desc')->limit(10)->get();
    }

    private function loadClassRankings($startDate, $endDate)
    {
        if ($this->selectedClass) { $this->bestClasses = []; $this->attentionClasses = []; return; }

        $classes = Classes::query()->when($this->selectedDepartment, fn ($q) => $q->where('department_id', $this->selectedDepartment))->with('department')->get();
        $classRankings = [];

        foreach ($classes as $class) {
            $classStudents = Student::where('class_id', $class->id)->where('is_active', true)->count();
            if ($classStudents === 0) continue;
            $classAttendances = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->whereHas('student', fn ($q) => $q->where('class_id', $class->id))->get();
            $workingDays = $this->scopedWorkingDays($startDate, $endDate, $class->department_id, $class->id);
            $expected = $classStudents * $workingDays;
            if ($expected <= 0) continue;
            $hadirCount = $classAttendances->whereIn('status', ['hadir', 'terlambat', 'dispensasi'])->count();
            $classRankings[] = ['class' => $class, 'rate' => round(($hadirCount / $expected) * 100, 1), 'total_students' => $classStudents, 'alpha_count' => $classAttendances->where('status', 'alpha')->count(), 'late_count' => $classAttendances->where('status', 'terlambat')->count()];
        }

        usort($classRankings, fn ($a, $b) => $b['rate'] <=> $a['rate']);
        $this->bestClasses = array_slice($classRankings, 0, 5);
        usort($classRankings, fn ($a, $b) => $a['rate'] <=> $b['rate']);
        $this->attentionClasses = array_slice($classRankings, 0, 5);
    }

    private function buildStudentDetail($startDate, $endDate)
    {
        $student = Student::with('class.department')->find($this->selectedStudent);
        if (!$student) return null;

        $attendances = Attendance::where('student_id', $student->id)->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->orderBy('date', 'desc')->get();
        $workingDays = $this->scopedWorkingDays($startDate, $endDate, $student->class->department_id ?? null, $student->class_id);
        $hadirCount = $attendances->where('status', 'hadir')->count();
        $terlambatCount = $attendances->where('status', 'terlambat')->count();
        $totalKehadiran = $hadirCount + $terlambatCount + $attendances->where('status', 'dispensasi')->count();

        return [
            'student' => $student,
            'working_days' => $workingDays,
            'hadir' => $hadirCount,
            'terlambat' => $terlambatCount,
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
            'dispensasi' => $attendances->where('status', 'dispensasi')->count(),
            'bolos' => $attendances->where('status', 'bolos')->count(),
            'total_kehadiran' => $totalKehadiran,
            'attendance_rate' => $workingDays > 0 ? round(($totalKehadiran / $workingDays) * 100, 1) : 0,
            'avg_late_minutes' => round($attendances->where('status', 'terlambat')->avg('late_minutes') ?? 0, 1),
            'recent_attendances' => $attendances->take(10),
        ];
    }

    public function exportExcel()
    {
        $semester = Semester::find($this->selectedSemester);
        if (!$semester) { session()->flash('error', 'Semester tidak ditemukan.'); return; }
        $fileName = 'Laporan_Analitik_' . $semester->name . '_' . now()->format('YmdHis') . '.xlsx';
        return Excel::download(new AttendanceExport($semester->start_date->format('Y-m-d'), $semester->end_date->format('Y-m-d'), $this->selectedDepartment, $this->selectedClass, $this->selectedStudent, '', ''), $fileName);
    }

    public function exportPdf()
    {
        $semester = Semester::find($this->selectedSemester);
        if (!$semester) { session()->flash('error', 'Semester tidak ditemukan.'); return; }
        $this->loadAnalytics();
        $pdf = Pdf::loadView('exports.analytics-pdf', ['semester' => $semester, 'selectedDepartment' => $this->selectedDepartment, 'selectedClass' => $this->selectedClass, 'selectedStudent' => $this->selectedStudent, 'statistics' => $this->statistics, 'topDiligentStudents' => $this->topDiligentStudents, 'topAlphaStudents' => $this->topAlphaStudents, 'topLateStudents' => $this->topLateStudents, 'bestClasses' => $this->bestClasses, 'attentionClasses' => $this->attentionClasses, 'studentDetail' => $this->studentDetail, 'weeklyTrend' => $this->weeklyTrend, 'statusDistribution' => $this->statusDistribution])->setPaper('a4', 'landscape');
        $fileName = 'Laporan_Analitik_' . $semester->name . '_' . now()->format('YmdHis') . '.pdf';
        return response()->streamDownload(function () use ($pdf) { echo $pdf->output(); }, $fileName);
    }

    public function render()
    {
        $departments = Department::orderBy('name')->get();
        $classes = Classes::when($this->selectedDepartment, fn ($q) => $q->where('department_id', $this->selectedDepartment))->orderBy('name')->get();
        $students = Student::where('is_active', true)->when($this->selectedClass, fn ($q) => $q->where('class_id', $this->selectedClass))->orderBy('full_name')->get();
        $semesters = Semester::orderBy('academic_year', 'desc')->orderBy('name', 'desc')->get();
        return view('livewire.admin.analytics.advanced-reports', compact('departments', 'classes', 'students', 'semesters'));
    }
}
