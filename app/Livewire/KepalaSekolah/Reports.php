<?php

namespace App\Livewire\KepalaSekolah;

use App\Models\Student;
use App\Models\Classes;
use App\Models\Department;
use App\Models\Attendance;
use App\Exports\AttendanceReportExport;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.kepala-sekolah')]
#[Title('Laporan Kehadiran')]
class Reports extends Component
{
    public $reportType = 'monthly'; // monthly, semester, custom
    public $selectedMonth;
    public $selectedYear;
    public $selectedSemester = 1;
    public $startDate;
    public $endDate;
    public $selectedDepartment = 'all';
    public $selectedClass = 'all';

    public function mount()
    {
        $this->selectedMonth = Carbon::now()->month;
        $this->selectedYear = Carbon::now()->year;
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedReportType()
    {
        if ($this->reportType === 'monthly') {
            $this->startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth()->format('Y-m-d');
            $this->endDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->endOfMonth()->format('Y-m-d');
        } elseif ($this->reportType === 'semester') {
            if ($this->selectedSemester == 1) {
                $this->startDate = Carbon::create($this->selectedYear, 7, 1)->format('Y-m-d');
                $this->endDate = Carbon::create($this->selectedYear, 12, 31)->format('Y-m-d');
            } else {
                $this->startDate = Carbon::create($this->selectedYear, 1, 1)->format('Y-m-d');
                $this->endDate = Carbon::create($this->selectedYear, 6, 30)->format('Y-m-d');
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

    public function updatedSelectedSemester()
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

        return Excel::download(
            new AttendanceReportExport($this->startDate, $this->endDate, $this->selectedDepartment, $this->selectedClass),
            $filename
        );
    }

    private function getReportData()
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        // Base query for students
        $studentsQuery = Student::where('is_active', true);

        if ($this->selectedDepartment !== 'all') {
            $studentsQuery->whereHas('class', function ($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
        }

        if ($this->selectedClass !== 'all') {
            $studentsQuery->where('class_id', $this->selectedClass);
        }

        $students = $studentsQuery->with(['class.department'])->get();

        // Get attendances for the period - PERBAIKAN: gunakan date field atau whereDate
        $attendances = Attendance::whereBetween('date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ])
            ->whereIn('student_id', $students->pluck('id'))
            ->get();

        // Calculate statistics
        $totalStudents = $students->count();
        $workingDays = $this->getWorkingDaysBetween($startDate, $endDate);
        $expectedAttendances = $totalStudents * $workingDays;

        $statistics = [
            'total_students' => $totalStudents,
            'working_days' => $workingDays,
            'expected_attendances' => $expectedAttendances,
            'total_present' => $attendances->whereIn('status', ['hadir', 'terlambat'])->count(),
            'total_hadir' => $attendances->where('status', 'hadir')->count(),
            'total_terlambat' => $attendances->where('status', 'terlambat')->count(),
            'total_izin' => $attendances->where('status', 'izin')->count(),
            'total_sakit' => $attendances->where('status', 'sakit')->count(),
            'total_alpha' => $attendances->where('status', 'alpha')->count(),
            'total_dispensasi' => $attendances->where('status', 'dispensasi')->count(),
        ];

        $statistics['attendance_percentage'] = $expectedAttendances > 0
            ? round(($statistics['total_present'] / $expectedAttendances) * 100, 2)
            : 0;

        // Department breakdown
        $departmentStats = [];
        $departments = Department::all();

        foreach ($departments as $dept) {
            $deptStudents = $students->filter(function ($student) use ($dept) {
                return $student->class && $student->class->department_id == $dept->id;
            });

            if ($deptStudents->count() == 0) continue;

            $deptAttendances = $attendances->whereIn('student_id', $deptStudents->pluck('id'));
            $deptExpected = $deptStudents->count() * $workingDays;
            $deptPresent = $deptAttendances->whereIn('status', ['hadir', 'terlambat'])->count();

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

        // Class breakdown
        $classStats = [];
        $classes = Classes::all();

        foreach ($classes as $class) {
            $classStudents = $students->where('class_id', $class->id);

            if ($classStudents->count() == 0) continue;

            $classAttendances = $attendances->whereIn('student_id', $classStudents->pluck('id'));
            $classExpected = $classStudents->count() * $workingDays;
            $classPresent = $classAttendances->whereIn('status', ['hadir', 'terlambat'])->count();

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

    private function getWorkingDaysBetween($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workingDays = 0;

        // Get all holiday dates in this range
        $holidayDates = \App\Models\AcademicCalendar::getHolidayDates($start, $end);

        while ($start <= $end) {
            // Count weekdays only and exclude holidays
            if ($start->isWeekday() && !in_array($start->format('Y-m-d'), $holidayDates)) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }

    public function render()
    {
        $data = $this->getReportData();
        $departments = Department::all();
        $classes = Classes::with('department')->get();

        return view('livewire.kepala-sekolah.reports', [
            'reportData' => $data,
            'departments' => $departments,
            'classes' => $classes,
        ]);
    }
}
