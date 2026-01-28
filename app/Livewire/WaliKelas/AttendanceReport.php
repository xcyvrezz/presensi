<?php

namespace App\Livewire\WaliKelas;

use App\Models\Classes;
use App\Models\Attendance;
use App\Exports\WaliKelasAttendanceExport;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('layouts.wali-kelas')]
#[Title('Absensi Kelas')]
class AttendanceReport extends Component
{
    use WithPagination;

    public $class;
    public $dateFrom;
    public $dateTo;
    public $statusFilter = '';
    public $search = '';

    // Monthly export filters
    public $exportMonth;
    public $exportYear;

    // Export mode and filters
    public $exportMode = 'monthly'; // monthly, semester, or custom
    public $exportSemester;
    public $exportStartDate;
    public $exportEndDate;

    // Statistics
    public $totalPresent = 0;
    public $totalLate = 0;
    public $totalAbsent = 0;
    public $totalPermit = 0;
    public $totalSick = 0;
    public $totalDispensasi = 0;
    public $totalBolos = 0;
    public $totalPulangCepat = 0;
    public $totalLupaCheckout = 0;

    public function mount()
    {
        // Get class yang diampu oleh wali kelas ini
        $this->class = Classes::where('wali_kelas_id', auth()->id())
            ->with(['department'])
            ->first();

        // Set default date range (last 7 days)
        $this->dateTo = Carbon::today()->format('Y-m-d');
        $this->dateFrom = Carbon::today()->subDays(6)->format('Y-m-d');

        // Set default month and year for export
        $this->exportMonth = Carbon::now()->format('m');
        $this->exportYear = Carbon::now()->format('Y');

        // Set default semester (active semester)
        $activeSemester = \App\Models\Semester::active()->first();
        $this->exportSemester = $activeSemester ? $activeSemester->id : null;

        // Set default dates for custom export (current month)
        $this->exportStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->exportEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->calculateStatistics();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
        $this->calculateStatistics();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
        $this->calculateStatistics();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function calculateStatistics()
    {
        if (!$this->class) {
            return;
        }

        $query = Attendance::whereHas('student', function ($q) {
            $q->where('class_id', $this->class->id);
        });

        if ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        if ($this->search) {
            $query->whereHas('student', function ($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('nis', 'like', '%' . $this->search . '%');
            });
        }

        $attendances = $query->get();

        // Calculate all status types
        $this->totalPresent = $attendances->where('status', 'hadir')->count();
        $this->totalLate = $attendances->where('status', 'terlambat')->count();
        $this->totalAbsent = $attendances->where('status', 'alpha')->count();
        $this->totalPermit = $attendances->where('status', 'izin')->count();
        $this->totalSick = $attendances->where('status', 'sakit')->count();
        $this->totalDispensasi = $attendances->where('status', 'dispensasi')->count();
        $this->totalBolos = $attendances->where('status', 'bolos')->count();
        $this->totalPulangCepat = $attendances->where('status', 'pulang_cepat')->count();

        // Calculate lupa checkout (has check-in but no check-out)
        $this->totalLupaCheckout = $attendances->filter(function($attendance) {
            return $attendance->check_in_time
                && !$attendance->check_out_time
                && in_array($attendance->status, ['hadir', 'terlambat']);
        })->count();
    }

    public function exportExcel()
    {
        if (!$this->class) {
            session()->flash('error', 'Anda tidak mengampu kelas manapun.');
            return;
        }

        $fileName = 'Absensi_' . str_replace(' ', '_', $this->class->name) . '_' . ($this->dateFrom ?? 'All') . '_to_' . ($this->dateTo ?? 'All') . '_' . now()->format('YmdHis') . '.xlsx';

        return Excel::download(
            new WaliKelasAttendanceExport(
                $this->class->id,
                $this->dateFrom,
                $this->dateTo,
                $this->statusFilter,
                $this->class->name
            ),
            $fileName
        );
    }

    public function exportPdf()
    {
        if (!$this->class) {
            session()->flash('error', 'Anda tidak mengampu kelas manapun.');
            return;
        }

        $query = Attendance::whereHas('student', function ($q) {
            $q->where('class_id', $this->class->id);

            if ($this->search) {
                $q->where(function ($query) {
                    $query->where('full_name', 'like', '%' . $this->search . '%')
                          ->orWhere('nis', 'like', '%' . $this->search . '%');
                });
            }
        })
        ->with(['student', 'location']);

        if ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.wali-kelas-attendance-pdf', [
            'attendances' => $attendances,
            'class' => $this->class,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'totalPresent' => $this->totalPresent,
            'totalLate' => $this->totalLate,
            'totalAbsent' => $this->totalAbsent,
            'totalPermit' => $this->totalPermit,
            'totalSick' => $this->totalSick,
            'totalDispensasi' => $this->totalDispensasi,
            'totalBolos' => $this->totalBolos,
            'totalPulangCepat' => $this->totalPulangCepat,
            'totalLupaCheckout' => $this->totalLupaCheckout,
        ])->setPaper('a4', 'landscape');

        $fileName = 'Absensi_' . str_replace(' ', '_', $this->class->name) . '_' . ($this->dateFrom ?? 'All') . '_to_' . ($this->dateTo ?? 'All') . '_' . now()->format('YmdHis') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function exportMonthlyExcel()
    {
        if (!$this->class) {
            session()->flash('error', 'Anda tidak mengampu kelas manapun.');
            return;
        }

        $monthName = Carbon::createFromDate($this->exportYear, $this->exportMonth, 1)
            ->locale('id')
            ->translatedFormat('F');

        $fileName = 'Rekap_Absensi_' . str_replace(' ', '_', $this->class->name) . '_' . $monthName . '_' . $this->exportYear . '.xlsx';

        return Excel::download(
            new \App\Exports\MonthlyAttendanceExport(
                $this->class->id,
                $this->exportMonth,
                $this->exportYear
            ),
            $fileName
        );
    }

    public function exportMonthlyPdf()
    {
        if (!$this->class) {
            session()->flash('error', 'Anda tidak mengampu kelas manapun.');
            return;
        }

        // Get all students in this class
        $students = \App\Models\Student::where('class_id', $this->class->id)
            ->active()
            ->orderBy('full_name')
            ->get();

        // Calculate attendance summary for each student
        $attendanceData = [];
        foreach ($students as $student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->whereYear('date', $this->exportYear)
                ->whereMonth('date', $this->exportMonth)
                ->get();

            $attendanceData[] = [
                'student' => $student,
                'hadir' => $attendances->where('status', 'hadir')->count(),
                'terlambat' => $attendances->where('status', 'terlambat')->count(),
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'bolos' => $attendances->where('status', 'bolos')->count(),
                'alpha' => $attendances->where('status', 'alpha')->count(),
            ];
        }

        $monthName = Carbon::createFromDate($this->exportYear, $this->exportMonth, 1)
            ->locale('id')
            ->translatedFormat('F');

        $pdf = Pdf::loadView('exports.monthly-attendance-pdf', [
            'attendanceData' => $attendanceData,
            'class' => $this->class,
            'month' => $monthName,
            'year' => $this->exportYear,
        ])->setPaper('a4', 'landscape');

        $fileName = 'Rekap_Absensi_' . str_replace(' ', '_', $this->class->name) . '_' . $monthName . '_' . $this->exportYear . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function exportSemesterExcel()
    {
        if (!$this->class) {
            session()->flash('error', 'Anda tidak mengampu kelas manapun.');
            return;
        }

        // Validate that semester is selected
        if (empty($this->exportSemester)) {
            session()->flash('error', 'Silakan pilih semester terlebih dahulu untuk export absensi semester.');
            return;
        }

        $semester = \App\Models\Semester::find($this->exportSemester);
        if (!$semester) {
            session()->flash('error', 'Semester tidak ditemukan.');
            return;
        }

        // Sanitize filename
        $className = preg_replace('/[\/\\\\\s]+/', '_', $this->class->name);
        $semesterName = preg_replace('/[\/\\\\\s]+/', '_', $semester->name);
        $fileName = 'Rekap_Absensi_Semester_' . $className . '_' . $semesterName . '.xlsx';

        return Excel::download(
            new \App\Exports\SemesterAttendanceExport(
                $this->class->id,
                $this->exportSemester
            ),
            $fileName
        );
    }

    public function exportSemesterPdf()
    {
        if (!$this->class) {
            session()->flash('error', 'Anda tidak mengampu kelas manapun.');
            return;
        }

        // Validate that semester is selected
        if (empty($this->exportSemester)) {
            session()->flash('error', 'Silakan pilih semester terlebih dahulu.');
            return;
        }

        $semester = \App\Models\Semester::find($this->exportSemester);
        if (!$semester) {
            session()->flash('error', 'Semester tidak ditemukan.');
            return;
        }

        // Get all students in this class
        $students = \App\Models\Student::where('class_id', $this->class->id)
            ->active()
            ->orderBy('full_name')
            ->get();

        // Get holiday dates in this semester
        $holidayDates = \App\Models\AcademicCalendar::getHolidayDates(
            $semester->start_date,
            $semester->end_date
        );

        // Calculate effective school days
        $effectiveSchoolDays = $this->calculateEffectiveSchoolDays($semester->start_date, $semester->end_date, $holidayDates);

        // Calculate attendance summary for each student
        $attendanceData = [];
        foreach ($students as $student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->where('semester_id', $this->exportSemester)
                ->whereBetween('date', [$semester->start_date, $semester->end_date])
                ->get();

            $hadirCount = $attendances->where('status', 'hadir')->count();
            $terlambatCount = $attendances->where('status', 'terlambat')->count();
            $dispensasiCount = $attendances->where('status', 'dispensasi')->count();
            $totalKehadiran = $hadirCount + $terlambatCount + $dispensasiCount;
            $percentage = $effectiveSchoolDays > 0
                ? round(($totalKehadiran / $effectiveSchoolDays) * 100, 2)
                : 0;

            $attendanceData[] = [
                'student' => $student,
                'hadir' => $hadirCount,
                'terlambat' => $terlambatCount,
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'dispensasi' => $dispensasiCount,
                'bolos' => $attendances->where('status', 'bolos')->count(),
                'alpha' => $attendances->where('status', 'alpha')->count(),
                'total_kehadiran' => $totalKehadiran,
                'percentage' => $percentage,
            ];
        }

        $pdf = Pdf::loadView('exports.semester-attendance-pdf', [
            'attendanceData' => $attendanceData,
            'class' => $this->class,
            'semester' => $semester,
            'effectiveSchoolDays' => $effectiveSchoolDays,
        ])->setPaper('a4', 'landscape');

        // Sanitize filename
        $className = preg_replace('/[\/\\\\\s]+/', '_', $this->class->name);
        $semesterName = preg_replace('/[\/\\\\\s]+/', '_', $semester->name);
        $fileName = 'Rekap_Absensi_Semester_' . $className . '_' . $semesterName . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function exportCustomExcel()
    {
        if (!$this->class) {
            session()->flash('error', 'Anda tidak mengampu kelas manapun.');
            return;
        }

        // Validate that dates are selected
        if (empty($this->exportStartDate) || empty($this->exportEndDate)) {
            session()->flash('error', 'Silakan pilih tanggal mulai dan tanggal akhir untuk export absensi periode kustom.');
            return;
        }

        // Validate date range
        $startDate = Carbon::parse($this->exportStartDate);
        $endDate = Carbon::parse($this->exportEndDate);

        if ($startDate->gt($endDate)) {
            session()->flash('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
            return;
        }

        // Validate max 365 days
        $daysDiff = $startDate->diffInDays($endDate);
        if ($daysDiff > 365) {
            session()->flash('error', 'Periode maksimal adalah 365 hari (1 tahun).');
            return;
        }

        // Sanitize filename
        $className = preg_replace('/[\/\\\\\s]+/', '_', $this->class->name);
        $startDateStr = $startDate->format('Ymd');
        $endDateStr = $endDate->format('Ymd');
        $fileName = 'Rekap_Absensi_Custom_' . $className . '_' . $startDateStr . '_to_' . $endDateStr . '.xlsx';

        return Excel::download(
            new \App\Exports\CustomDateRangeAttendanceExport(
                $this->class->id,
                $this->exportStartDate,
                $this->exportEndDate
            ),
            $fileName
        );
    }

    public function exportCustomPdf()
    {
        if (!$this->class) {
            session()->flash('error', 'Anda tidak mengampu kelas manapun.');
            return;
        }

        // Validate that dates are selected
        if (empty($this->exportStartDate) || empty($this->exportEndDate)) {
            session()->flash('error', 'Silakan pilih tanggal mulai dan tanggal akhir untuk export absensi periode kustom.');
            return;
        }

        // Validate date range
        $startDate = Carbon::parse($this->exportStartDate);
        $endDate = Carbon::parse($this->exportEndDate);

        if ($startDate->gt($endDate)) {
            session()->flash('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
            return;
        }

        // Validate max 365 days
        $daysDiff = $startDate->diffInDays($endDate);
        if ($daysDiff > 365) {
            session()->flash('error', 'Periode maksimal adalah 365 hari (1 tahun).');
            return;
        }

        // Get all students in this class
        $students = \App\Models\Student::where('class_id', $this->class->id)
            ->active()
            ->orderBy('full_name')
            ->get();

        // Get holiday dates in this period
        $holidayDates = \App\Models\AcademicCalendar::getHolidayDates(
            $startDate,
            $endDate
        );

        // Calculate effective school days
        $effectiveSchoolDays = $this->calculateEffectiveSchoolDays($startDate, $endDate, $holidayDates);

        // Calculate attendance summary for each student
        $attendanceData = [];
        foreach ($students as $student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $hadirCount = $attendances->where('status', 'hadir')->count();
            $terlambatCount = $attendances->where('status', 'terlambat')->count();
            $dispensasiCount = $attendances->where('status', 'dispensasi')->count();
            $totalKehadiran = $hadirCount + $terlambatCount + $dispensasiCount;
            $percentage = $effectiveSchoolDays > 0
                ? round(($totalKehadiran / $effectiveSchoolDays) * 100, 2)
                : 0;

            $attendanceData[] = [
                'student' => $student,
                'hadir' => $hadirCount,
                'terlambat' => $terlambatCount,
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'dispensasi' => $dispensasiCount,
                'bolos' => $attendances->where('status', 'bolos')->count(),
                'alpha' => $attendances->where('status', 'alpha')->count(),
                'total_kehadiran' => $totalKehadiran,
                'percentage' => $percentage,
            ];
        }

        $pdf = Pdf::loadView('exports.custom-attendance-pdf', [
            'attendanceData' => $attendanceData,
            'class' => $this->class,
            'startDate' => $startDate->locale('id')->translatedFormat('d F Y'),
            'endDate' => $endDate->locale('id')->translatedFormat('d F Y'),
            'effectiveSchoolDays' => $effectiveSchoolDays,
        ])->setPaper('a4', 'landscape');

        // Sanitize filename
        $className = preg_replace('/[\/\\\\\s]+/', '_', $this->class->name);
        $startDateStr = $startDate->format('Ymd');
        $endDateStr = $endDate->format('Ymd');
        $fileName = 'Rekap_Absensi_Custom_' . $className . '_' . $startDateStr . '_to_' . $endDateStr . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    /**
     * Calculate effective school days (exclude Saturday, Sunday, and holidays)
     */
    protected function calculateEffectiveSchoolDays($startDate, $endDate, $holidayDates): int
    {
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $effectiveDays = 0;

        while ($current->lte($end)) {
            $dateString = $current->format('Y-m-d');

            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($current->dayOfWeek !== Carbon::SATURDAY && $current->dayOfWeek !== Carbon::SUNDAY) {
                // Skip holidays
                if (!in_array($dateString, $holidayDates)) {
                    $effectiveDays++;
                }
            }

            $current->addDay();
        }

        return $effectiveDays;
    }

    public function render()
    {
        $attendances = collect();

        if ($this->class) {
            $attendances = Attendance::whereHas('student', function ($q) {
                $q->where('class_id', $this->class->id);

                if ($this->search) {
                    $q->where(function ($query) {
                        $query->where('full_name', 'like', '%' . $this->search . '%')
                              ->orWhere('nis', 'like', '%' . $this->search . '%');
                    });
                }
            })
                ->with(['student'])
                ->when($this->dateFrom, function ($query) {
                    $query->whereDate('date', '>=', $this->dateFrom);
                })
                ->when($this->dateTo, function ($query) {
                    $query->whereDate('date', '<=', $this->dateTo);
                })
                ->when($this->statusFilter !== '', function ($query) {
                    $query->where('status', $this->statusFilter);
                })
                ->orderBy('date', 'desc')
                ->orderBy('check_in_time', 'desc')
                ->paginate(15);
        }

        // Get semesters for export dropdown
        $semesters = \App\Models\Semester::orderBy('academic_year', 'desc')
            ->orderBy('name', 'desc')
            ->get();

        return view('livewire.wali-kelas.attendance-report', [
            'attendances' => $attendances,
            'semesters' => $semesters,
        ]);
    }
}
