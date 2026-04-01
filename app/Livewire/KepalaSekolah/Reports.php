<?php

namespace App\Livewire\KepalaSekolah;

use App\Exports\AttendanceReportExport;
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

#[Layout('layouts.kepala-sekolah')]
#[Title('Laporan Kehadiran')]
class Reports extends Component
{
    public $reportType = 'semester';
    public $selectedMonth;
    public $selectedYear;
    public $selectedSemesterId;
    public $startDate;
    public $endDate;
    public $selectedDepartment = 'all';
    public $selectedClass = 'all';

    public function mount()
    {
        $activeSemester = Semester::where('is_active', true)->first();

        if ($activeSemester) {
            $this->selectedSemesterId = $activeSemester->id;
            $this->startDate = Carbon::parse($activeSemester->start_date)->format('Y-m-d');
            $this->endDate = Carbon::parse($activeSemester->end_date)->format('Y-m-d');
            $this->selectedYear = Carbon::parse($activeSemester->start_date)->year;
        } else {
            $this->selectedMonth = Carbon::now()->month;
            $this->selectedYear = Carbon::now()->year;
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
            $this->reportType = 'monthly';
        }
    }

    public function updatedReportType()
    {
        if ($this->reportType === 'monthly') {
            $this->startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth()->format('Y-m-d');
            $this->endDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->endOfMonth()->format('Y-m-d');
        } elseif ($this->reportType === 'semester') {
            $semester = $this->selectedSemesterId ? Semester::find($this->selectedSemesterId) : (Semester::where('is_active', true)->first() ?? Semester::latest()->first());
            if ($semester) {
                $this->selectedSemesterId = $semester->id;
                $this->startDate = Carbon::parse($semester->start_date)->format('Y-m-d');
                $this->endDate = Carbon::parse($semester->end_date)->format('Y-m-d');
            }
        }
    }

    public function updatedSelectedSemesterId()
    {
        if ($this->reportType === 'semester' && $this->selectedSemesterId) {
            $semester = Semester::find($this->selectedSemesterId);
            if ($semester) {
                $this->startDate = Carbon::parse($semester->start_date)->format('Y-m-d');
                $this->endDate = Carbon::parse($semester->end_date)->format('Y-m-d');
            }
        }
    }

    public function updatedSelectedMonth()
    {
        if ($this->reportType === 'monthly') {
            $this->startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth()->format('Y-m-d');
            $this->endDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->endOfMonth()->format('Y-m-d');
        }
    }

    public function updatedSelectedYear()
    {
        $this->updatedReportType();
    }

    public function exportPdf()
    {
        $data = $this->getReportData();

        $pdf = Pdf::loadView('exports.attendance-report-pdf', [
            'data' => $data,
            'reportType' => $this->reportType,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'generatedAt' => Carbon::now(),
        ]);

        $filename = 'Laporan_Kehadiran_' . Carbon::parse($this->startDate)->format('Y-m-d') . '_to_' . Carbon::parse($this->endDate)->format('Y-m-d') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, $filename);
    }

    public function exportExcel()
    {
        $filename = 'Laporan_Kehadiran_' . Carbon::parse($this->startDate)->format('Y-m-d') . '_to_' . Carbon::parse($this->endDate)->format('Y-m-d') . '.xlsx';

        return Excel::download(new AttendanceReportExport($this->startDate, $this->endDate, $this->selectedDepartment, $this->selectedClass), $filename);
    }

    private function getReportData()
    {
        $summaryService = app(AttendanceSummaryService::class);
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $studentsQuery = Student::where('is_active', true);
        if ($this->selectedDepartment !== 'all') {
            $studentsQuery->whereHas('class', fn ($q) => $q->where('department_id', $this->selectedDepartment));
        }
        if ($this->selectedClass !== 'all') {
            $studentsQuery->where('class_id', $this->selectedClass);
        }

        $students = $studentsQuery->with(['class.department'])->get();
        $studentIds = $students->pluck('id');

        $attendances = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereIn('student_id', $studentIds)
            ->get();

        $departmentId = $this->selectedDepartment !== 'all' ? (int) $this->selectedDepartment : null;
        $classId = $this->selectedClass !== 'all' ? (int) $this->selectedClass : null;
        $workingDays = $summaryService->getEffectiveDates($startDate, $endDate, $departmentId, $classId)->count();
        $totalStudents = $students->count();
        $expectedAttendances = $totalStudents * $workingDays;
        $totalPresent = $attendances->whereIn('status', ['hadir', 'terlambat', 'dispensasi'])->count();

        $statistics = [
            'total_students' => $totalStudents,
            'working_days' => $workingDays,
            'expected_attendances' => $expectedAttendances,
            'total_present' => $totalPresent,
            'total_hadir' => $attendances->where('status', 'hadir')->count(),
            'total_terlambat' => $attendances->where('status', 'terlambat')->count(),
            'total_izin' => $attendances->where('status', 'izin')->count(),
            'total_sakit' => $attendances->where('status', 'sakit')->count(),
            'total_alpha' => $attendances->where('status', 'alpha')->count(),
            'total_dispensasi' => $attendances->where('status', 'dispensasi')->count(),
            'attendance_percentage' => $expectedAttendances > 0 ? round(($totalPresent / $expectedAttendances) * 100, 2) : 0,
        ];

        $departmentStats = [];
        foreach (Department::all() as $dept) {
            $deptStudents = $students->filter(fn ($student) => $student->class && $student->class->department_id == $dept->id);
            if ($deptStudents->isEmpty()) continue;

            $deptAttendances = $attendances->whereIn('student_id', $deptStudents->pluck('id'));
            $deptWorkingDays = $summaryService->getEffectiveDates($startDate, $endDate, $dept->id)->count();
            $deptExpected = $deptStudents->count() * $deptWorkingDays;
            $deptPresent = $deptAttendances->whereIn('status', ['hadir', 'terlambat', 'dispensasi'])->count();

            $departmentStats[] = [
                'name' => $dept->name,
                'code' => $dept->code,
                'total_students' => $deptStudents->count(),
                'expected' => $deptExpected,
                'present' => $deptPresent,
                'percentage' => $deptExpected > 0 ? round(($deptPresent / $deptExpected) * 100, 2) : 0,
                'hadir' => $deptAttendances->where('status', 'hadir')->count(),
                'terlambat' => $deptAttendances->where('status', 'terlambat')->count(),
                'izin' => $deptAttendances->where('status', 'izin')->count(),
                'sakit' => $deptAttendances->where('status', 'sakit')->count(),
                'alpha' => $deptAttendances->where('status', 'alpha')->count(),
            ];
        }

        $classStats = [];
        foreach (Classes::all() as $class) {
            $classStudents = $students->where('class_id', $class->id);
            if ($classStudents->isEmpty()) continue;

            $classAttendances = $attendances->whereIn('student_id', $classStudents->pluck('id'));
            $classWorkingDays = $summaryService->getEffectiveDates($startDate, $endDate, $class->department_id, $class->id)->count();
            $classExpected = $classStudents->count() * $classWorkingDays;
            $classPresent = $classAttendances->whereIn('status', ['hadir', 'terlambat', 'dispensasi'])->count();

            $classStats[] = [
                'name' => $class->name,
                'department' => $class->department->name ?? '-',
                'total_students' => $classStudents->count(),
                'expected' => $classExpected,
                'present' => $classPresent,
                'percentage' => $classExpected > 0 ? round(($classPresent / $classExpected) * 100, 2) : 0,
                'hadir' => $classAttendances->where('status', 'hadir')->count(),
                'terlambat' => $classAttendances->where('status', 'terlambat')->count(),
                'izin' => $classAttendances->where('status', 'izin')->count(),
                'sakit' => $classAttendances->where('status', 'sakit')->count(),
                'alpha' => $classAttendances->where('status', 'alpha')->count(),
            ];
        }

        return [
            'statistics' => $statistics,
            'department_stats' => collect($departmentStats)->sortByDesc('percentage')->values(),
            'class_stats' => collect($classStats)->sortByDesc('percentage')->values(),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    public function render()
    {
        $data = $this->getReportData();
        $departments = Department::all();
        $classes = Classes::with('department')->get();
        $semesters = Semester::orderBy('start_date', 'desc')->get();

        return view('livewire.kepala-sekolah.reports', [
            'reportData' => $data,
            'departments' => $departments,
            'classes' => $classes,
            'semesters' => $semesters,
        ]);
    }
}
