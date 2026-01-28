<?php

namespace App\Livewire\Admin\Attendance;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Classes;
use App\Exports\AttendanceExport;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.admin')]
#[Title('Data Absensi')]
class AttendanceIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom;
    public $dateTo;
    public $departmentFilter = '';
    public $classFilter = '';
    public $statusFilter = '';
    public $methodFilter = '';

    // Export filters (separate from table filters)
    public $exportMode = 'monthly'; // monthly, semester, or custom
    public $exportClassFilter = '';
    public $exportMonth;
    public $exportYear;
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

    // RFID Reader
    public $rfidReaderActive = false;
    public $lastCardUid = '';
    public $rfidMessage = '';
    public $rfidMessageType = '';

    // Today's Event
    public $todayEvent = null;

    // Edit Modal State
    public $showEditModal = false;
    public $editingAttendanceId;

    // Form Fields
    public $editDate;
    public $editStudentName;
    public $editStudentNis;
    public $editStudentClass;
    public $editCheckInTime;
    public $editCheckOutTime;
    public $editStatus;
    public $editNotes;

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'departmentFilter' => ['except' => ''],
        'classFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'methodFilter' => ['except' => ''],
    ];

    protected function rules()
    {
        return [
            'editCheckInTime' => 'required|date_format:H:i',
            'editCheckOutTime' => 'nullable|date_format:H:i|after:editCheckInTime',
            'editStatus' => 'required|in:hadir,terlambat,izin,sakit,dispensasi,alpha,bolos,pulang_cepat,lupa_check_out',
            'editNotes' => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'editCheckInTime.required' => 'Waktu check-in harus diisi',
        'editCheckInTime.date_format' => 'Format waktu tidak valid (HH:MM)',
        'editCheckOutTime.date_format' => 'Format waktu tidak valid (HH:MM)',
        'editCheckOutTime.after' => 'Check-out harus setelah check-in',
        'editStatus.required' => 'Status kehadiran harus dipilih',
        'editStatus.in' => 'Status tidak valid',
    ];

    public function mount()
    {
        // Set default date range (today)
        $this->dateTo = Carbon::today()->format('Y-m-d');
        $this->dateFrom = Carbon::today()->format('Y-m-d');

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

        // Load today's event from academic calendar
        try {
            $attendanceService = app(\App\Services\AttendanceService::class);
            $this->todayEvent = $attendanceService->getTodayEventInfo();
        } catch (\Exception $e) {
            // If error loading event, just skip it (don't break the page)
            \Log::warning('Failed to load today event in AttendanceIndex: ' . $e->getMessage());
            $this->todayEvent = null;
        }
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
        $this->classFilter = ''; // Reset class filter when department changes
    }

    public function updatingClassFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingMethodFilter()
    {
        $this->resetPage();
    }

    public function calculateStatistics()
    {
        $query = Attendance::query();

        if ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        // Apply filters for accurate statistics
        if ($this->departmentFilter) {
            $query->whereHas('student.class', function ($q) {
                $q->where('department_id', $this->departmentFilter);
            });
        }

        if ($this->classFilter) {
            $query->where('class_id', $this->classFilter);
        }

        if ($this->search) {
            $query->whereHas('student', function ($q) {
                $q->where('nis', 'like', '%' . $this->search . '%')
                  ->orWhere('full_name', 'like', '%' . $this->search . '%');
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

    public function resetFilters()
    {
        $this->reset([
            'search',
            'dateFrom',
            'dateTo',
            'departmentFilter',
            'classFilter',
            'statusFilter',
            'methodFilter',
        ]);

        $this->dateTo = Carbon::today()->format('Y-m-d');
        $this->dateFrom = Carbon::today()->format('Y-m-d');
        $this->calculateStatistics();
    }

    public function exportExcel()
    {
        // If export class filter is set, export by mode (monthly/semester/custom)
        if ($this->exportClassFilter) {
            if ($this->exportMode === 'semester') {
                return $this->exportSemesterExcel();
            } elseif ($this->exportMode === 'custom') {
                return $this->exportCustomExcel();
            } else {
                return $this->exportMonthlyExcel();
            }
        }

        // Otherwise, export current filtered view
        $fileName = 'Absensi_' . ($this->dateFrom ?? 'All') . '_to_' . ($this->dateTo ?? 'All') . '_' . now()->format('YmdHis') . '.xlsx';

        return Excel::download(
            new AttendanceExport(
                $this->dateFrom,
                $this->dateTo,
                $this->departmentFilter,
                $this->classFilter,
                $this->statusFilter,
                $this->methodFilter,
                $this->search
            ),
            $fileName
        );
    }

    public function exportPdf()
    {
        // If export class filter is set, export by mode (monthly/semester/custom)
        if ($this->exportClassFilter) {
            if ($this->exportMode === 'semester') {
                return $this->exportSemesterPdf();
            } elseif ($this->exportMode === 'custom') {
                return $this->exportCustomPdf();
            } else {
                return $this->exportMonthlyPdf();
            }
        }

        // Otherwise, export current filtered view
        $query = Attendance::query()
            ->with(['student.class.department', 'location']);

        // Apply all filters
        if ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        if ($this->departmentFilter) {
            $query->whereHas('student.class', function ($q) {
                $q->where('department_id', $this->departmentFilter);
            });
        }

        if ($this->classFilter) {
            $query->where('class_id', $this->classFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->methodFilter) {
            $query->where('check_in_method', $this->methodFilter);
        }

        if ($this->search) {
            $query->whereHas('student', function ($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('nis', 'like', '%' . $this->search . '%');
            });
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.attendance-pdf', [
            'attendances' => $attendances,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'totalPresent' => $this->totalPresent,
            'totalLate' => $this->totalLate,
            'totalAbsent' => $this->totalAbsent,
            'totalPermit' => $this->totalPermit,
            'totalSick' => $this->totalSick,
        ])->setPaper('a4', 'landscape');

        $fileName = 'Absensi_' . ($this->dateFrom ?? 'All') . '_to_' . ($this->dateTo ?? 'All') . '_' . now()->format('YmdHis') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function toggleRfidReader()
    {
        $this->rfidReaderActive = !$this->rfidReaderActive;

        if ($this->rfidReaderActive) {
            $this->rfidMessage = 'RFID Reader aktif. Tap kartu untuk absensi...';
            $this->rfidMessageType = 'info';
            $this->dispatch('rfid-reader-toggled', state: true);
        } else {
            $this->rfidMessage = '';
            $this->rfidMessageType = '';
            $this->dispatch('rfid-reader-toggled', state: false);
        }
    }

    public function processRfidCard()
    {
        if (!$this->rfidReaderActive) {
            return;
        }

        $cardUid = trim(strtoupper($this->lastCardUid));

        // Validate card UID
        if (empty($cardUid) || strlen($cardUid) < 8) {
            return;
        }

        try {
            $student = \App\Models\Student::where('card_uid', $cardUid)->first();

            if (!$student) {
                $this->rfidMessage = "❌ Kartu tidak terdaftar (UID: {$cardUid})";
                $this->rfidMessageType = 'error';
                $this->dispatch('reset-card-uid');
                $this->scheduleMessageClear();
                return;
            }

            // Check if already checked in today
            $today = \Carbon\Carbon::today();
            $existingAttendance = \App\Models\Attendance::where('student_id', $student->id)
                ->whereDate('date', $today)
                ->first();

            $attendanceService = app(\App\Services\AttendanceService::class);

            if ($existingAttendance && !$existingAttendance->check_out_time) {
                // Process check-out
                $result = $attendanceService->checkOut([
                    'card_uid' => $cardUid,
                    'method' => 'rfid_physical',
                ]);

                if ($result['success']) {
                    $this->rfidMessage = "✅ Check-out berhasil: {$student->full_name} ({$student->nis})";
                    $this->rfidMessageType = 'success';
                    $this->scheduleMessageClear();
                } else {
                    $this->rfidMessage = "❌ " . $result['message'];
                    $this->rfidMessageType = 'error';
                    $this->scheduleMessageClear();
                }
            } elseif ($existingAttendance && $existingAttendance->check_out_time) {
                $this->rfidMessage = "⚠️ Sudah absen lengkap hari ini: {$student->full_name}";
                $this->rfidMessageType = 'warning';
                $this->scheduleMessageClear();
            } else {
                // Process check-in
                $result = $attendanceService->checkIn([
                    'card_uid' => $cardUid,
                    'method' => 'rfid_physical',
                ]);

                if ($result['success']) {
                    $status = $result['data']['status'] ?? 'hadir';
                    $statusText = $status === 'hadir' ? 'Tepat Waktu ⏰' : 'Terlambat ⏱️';

                    $this->rfidMessage = "✅ Check-in berhasil: {$student->full_name} - {$statusText}";
                    $this->rfidMessageType = 'success';
                    $this->scheduleMessageClear();
                } else {
                    $this->rfidMessage = "❌ " . $result['message'];
                    $this->rfidMessageType = 'error';
                    $this->scheduleMessageClear();
                }
            }

            // Refresh statistics and data
            $this->calculateStatistics();

            // Reset card UID for next scan
            $this->dispatch('reset-card-uid');

        } catch (\Exception $e) {
            $this->rfidMessage = "❌ Error: " . $e->getMessage();
            $this->rfidMessageType = 'error';
            $this->dispatch('reset-card-uid');
            $this->scheduleMessageClear();
        }
    }

    /**
     * Schedule message to be cleared after 3 seconds
     */
    protected function scheduleMessageClear()
    {
        $this->dispatch('schedule-message-clear');
    }

    public function exportMonthlyExcel()
    {
        // Validate that class is selected
        if (empty($this->exportClassFilter)) {
            session()->flash('error', 'Silakan pilih kelas terlebih dahulu untuk export absensi bulanan.');
            return;
        }

        $class = Classes::find($this->exportClassFilter);
        if (!$class) {
            session()->flash('error', 'Kelas tidak ditemukan.');
            return;
        }

        $monthName = Carbon::createFromDate($this->exportYear, $this->exportMonth, 1)
            ->locale('id')
            ->translatedFormat('F');

        $fileName = 'Rekap_Absensi_' . str_replace(' ', '_', $class->name) . '_' . $monthName . '_' . $this->exportYear . '.xlsx';

        return Excel::download(
            new \App\Exports\MonthlyAttendanceExport(
                $this->exportClassFilter,
                $this->exportMonth,
                $this->exportYear
            ),
            $fileName
        );
    }

    public function exportMonthlyPdf()
    {
        // Validate that class is selected
        if (empty($this->exportClassFilter)) {
            session()->flash('error', 'Silakan pilih kelas terlebih dahulu untuk export absensi bulanan.');
            return;
        }

        $class = Classes::with('department')->find($this->exportClassFilter);
        if (!$class) {
            session()->flash('error', 'Kelas tidak ditemukan.');
            return;
        }

        // Calculate date range for the month
        $startDate = Carbon::createFromDate($this->exportYear, $this->exportMonth, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->exportYear, $this->exportMonth, 1)->endOfMonth();

        // Get holiday dates in this month
        $holidayDates = \App\Models\AcademicCalendar::getHolidayDates(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        // Calculate effective school days
        $effectiveSchoolDays = $this->calculateEffectiveSchoolDays($startDate->format('Y-m-d'), $endDate->format('Y-m-d'), $holidayDates);

        // Get all students in this class
        $students = \App\Models\Student::where('class_id', $this->exportClassFilter)
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

            $hadirCount = $attendances->where('status', 'hadir')->count();
            $terlambatCount = $attendances->where('status', 'terlambat')->count();
            $dispensasiCount = $attendances->where('status', 'dispensasi')->count();

            // Total kehadiran (hadir + terlambat + dispensasi)
            $totalKehadiran = $hadirCount + $terlambatCount + $dispensasiCount;

            // Percentage calculation
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

        $monthName = Carbon::createFromDate($this->exportYear, $this->exportMonth, 1)
            ->locale('id')
            ->translatedFormat('F');

        $pdf = Pdf::loadView('exports.monthly-attendance-pdf', [
            'attendanceData' => $attendanceData,
            'class' => $class,
            'month' => $monthName,
            'year' => $this->exportYear,
            'effectiveSchoolDays' => $effectiveSchoolDays,
        ])->setPaper('a4', 'landscape');

        $fileName = 'Rekap_Absensi_' . str_replace(' ', '_', $class->name) . '_' . $monthName . '_' . $this->exportYear . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function exportSemesterExcel()
    {
        // Validate that class is selected
        if (empty($this->exportClassFilter)) {
            session()->flash('error', 'Silakan pilih kelas terlebih dahulu untuk export absensi semester.');
            return;
        }

        // Validate that semester is selected
        if (empty($this->exportSemester)) {
            session()->flash('error', 'Silakan pilih semester terlebih dahulu.');
            return;
        }

        $class = Classes::find($this->exportClassFilter);
        if (!$class) {
            session()->flash('error', 'Kelas tidak ditemukan.');
            return;
        }

        $semester = \App\Models\Semester::find($this->exportSemester);
        if (!$semester) {
            session()->flash('error', 'Semester tidak ditemukan.');
            return;
        }

        // Sanitize filename by replacing invalid characters
        $className = preg_replace('/[\/\\\\\s]+/', '_', $class->name);
        $semesterName = preg_replace('/[\/\\\\\s]+/', '_', $semester->name);
        $fileName = 'Rekap_Absensi_Semester_' . $className . '_' . $semesterName . '.xlsx';

        return Excel::download(
            new \App\Exports\SemesterAttendanceExport(
                $this->exportClassFilter,
                $this->exportSemester
            ),
            $fileName
        );
    }

    public function exportSemesterPdf()
    {
        // Validate that class is selected
        if (empty($this->exportClassFilter)) {
            session()->flash('error', 'Silakan pilih kelas terlebih dahulu untuk export absensi semester.');
            return;
        }

        // Validate that semester is selected
        if (empty($this->exportSemester)) {
            session()->flash('error', 'Silakan pilih semester terlebih dahulu.');
            return;
        }

        $class = Classes::with('department')->find($this->exportClassFilter);
        if (!$class) {
            session()->flash('error', 'Kelas tidak ditemukan.');
            return;
        }

        $semester = \App\Models\Semester::find($this->exportSemester);
        if (!$semester) {
            session()->flash('error', 'Semester tidak ditemukan.');
            return;
        }

        // Get all students in this class
        $students = \App\Models\Student::where('class_id', $this->exportClassFilter)
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
            'class' => $class,
            'semester' => $semester,
            'effectiveSchoolDays' => $effectiveSchoolDays,
        ])->setPaper('a4', 'landscape');

        // Sanitize filename by replacing invalid characters
        $className = preg_replace('/[\/\\\\\s]+/', '_', $class->name);
        $semesterName = preg_replace('/[\/\\\\\s]+/', '_', $semester->name);
        $fileName = 'Rekap_Absensi_Semester_' . $className . '_' . $semesterName . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function exportCustomExcel()
    {
        // Validate that class is selected
        if (empty($this->exportClassFilter)) {
            session()->flash('error', 'Silakan pilih kelas terlebih dahulu untuk export absensi periode kustom.');
            return;
        }

        // Validate that dates are selected
        if (empty($this->exportStartDate) || empty($this->exportEndDate)) {
            session()->flash('error', 'Silakan pilih tanggal mulai dan tanggal akhir untuk export absensi periode kustom.');
            return;
        }

        $class = Classes::find($this->exportClassFilter);
        if (!$class) {
            session()->flash('error', 'Kelas tidak ditemukan.');
            return;
        }

        // Validate date range
        $startDate = Carbon::parse($this->exportStartDate);
        $endDate = Carbon::parse($this->exportEndDate);

        if ($endDate->lt($startDate)) {
            session()->flash('error', 'Tanggal akhir harus sama atau setelah tanggal mulai.');
            return;
        }

        // Check maximum date range (365 days)
        $daysDifference = $startDate->diffInDays($endDate);
        if ($daysDifference > 365) {
            session()->flash('error', 'Rentang tanggal maksimal adalah 1 tahun (365 hari). Rentang Anda: ' . $daysDifference . ' hari.');
            return;
        }

        // Sanitize filename
        $className = preg_replace('/[\/\\\\\s]+/', '_', $class->name);
        $startDateStr = $startDate->format('Ymd');
        $endDateStr = $endDate->format('Ymd');
        $fileName = 'Rekap_Absensi_Custom_' . $className . '_' . $startDateStr . '_to_' . $endDateStr . '.xlsx';

        return Excel::download(
            new \App\Exports\CustomDateRangeAttendanceExport(
                $this->exportClassFilter,
                $this->exportStartDate,
                $this->exportEndDate
            ),
            $fileName
        );
    }

    public function exportCustomPdf()
    {
        // Validate that class is selected
        if (empty($this->exportClassFilter)) {
            session()->flash('error', 'Silakan pilih kelas terlebih dahulu untuk export absensi periode kustom.');
            return;
        }

        // Validate that dates are selected
        if (empty($this->exportStartDate) || empty($this->exportEndDate)) {
            session()->flash('error', 'Silakan pilih tanggal mulai dan tanggal akhir untuk export absensi periode kustom.');
            return;
        }

        $class = Classes::with('department')->find($this->exportClassFilter);
        if (!$class) {
            session()->flash('error', 'Kelas tidak ditemukan.');
            return;
        }

        // Validate date range
        $startDate = Carbon::parse($this->exportStartDate);
        $endDate = Carbon::parse($this->exportEndDate);

        if ($endDate->lt($startDate)) {
            session()->flash('error', 'Tanggal akhir harus sama atau setelah tanggal mulai.');
            return;
        }

        // Check maximum date range (365 days)
        $daysDifference = $startDate->diffInDays($endDate);
        if ($daysDifference > 365) {
            session()->flash('error', 'Rentang tanggal maksimal adalah 1 tahun (365 hari). Rentang Anda: ' . $daysDifference . ' hari.');
            return;
        }

        // Get holiday dates in this date range
        $holidayDates = AcademicCalendar::getHolidayDates(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        // Calculate effective school days
        $effectiveSchoolDays = $this->calculateEffectiveSchoolDays(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            $holidayDates
        );

        // Get all students in this class
        $students = \App\Models\Student::where('class_id', $this->exportClassFilter)
            ->active()
            ->orderBy('full_name')
            ->get();

        // Calculate attendance data for each student
        $attendanceData = [];
        foreach ($students as $student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get();

            $hadirCount = $attendances->where('status', 'hadir')->count();
            $terlambatCount = $attendances->where('status', 'terlambat')->count();
            $dispensasiCount = $attendances->where('status', 'dispensasi')->count();

            // Total kehadiran (hadir + terlambat + dispensasi)
            $totalKehadiran = $hadirCount + $terlambatCount + $dispensasiCount;

            // Percentage calculation
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
            'class' => $class,
            'startDate' => $startDate->locale('id')->translatedFormat('d F Y'),
            'endDate' => $endDate->locale('id')->translatedFormat('d F Y'),
            'effectiveSchoolDays' => $effectiveSchoolDays,
        ])->setPaper('a4', 'landscape');

        // Sanitize filename
        $className = preg_replace('/[\/\\\\\s]+/', '_', $class->name);
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

            // Exclude Saturday (6) and Sunday (0)
            $isWeekend = $current->dayOfWeek == 0 || $current->dayOfWeek == 6;

            // Exclude holidays
            $isHoliday = in_array($dateString, $holidayDates);

            if (!$isWeekend && !$isHoliday) {
                $effectiveDays++;
            }

            $current->addDay();
        }

        return $effectiveDays;
    }

    /**
     * Calculate late minutes based on check-in time
     */
    protected function calculateLateMinutes(Carbon $checkInDateTime): int
    {
        try {
            // Get late threshold from settings
            $lateThreshold = \App\Models\AttendanceSetting::getValue('late_threshold', '07:15:00');

            // Ensure string format
            if ($lateThreshold instanceof Carbon) {
                $lateThreshold = $lateThreshold->format('H:i:s');
            }

            // Parse threshold with same date as check-in
            $thresholdString = $checkInDateTime->format('Y-m-d') . ' ' . $lateThreshold;
            $threshold = Carbon::createFromFormat('Y-m-d H:i:s', $thresholdString);

            // If check-in after threshold, calculate late minutes
            if ($checkInDateTime->gt($threshold)) {
                return $threshold->diffInMinutes($checkInDateTime);
            }

            return 0;
        } catch (\Exception $e) {
            \Log::error('Error calculating late minutes: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate early leave minutes based on check-out time
     */
    protected function calculateEarlyLeaveMinutes(Carbon $checkOutDateTime): int
    {
        try {
            // Get normal check-out time from settings
            $checkOutNormal = \App\Models\AttendanceSetting::getValue('check_out_normal', '15:30:00');

            // Ensure string format
            if ($checkOutNormal instanceof Carbon) {
                $checkOutNormal = $checkOutNormal->format('H:i:s');
            }

            // Parse threshold with same date as check-out
            $thresholdString = $checkOutDateTime->format('Y-m-d') . ' ' . $checkOutNormal;
            $threshold = Carbon::createFromFormat('Y-m-d H:i:s', $thresholdString);

            // If check-out before normal time, calculate early leave minutes
            if ($checkOutDateTime->lt($threshold)) {
                return $threshold->diffInMinutes($checkOutDateTime);
            }

            return 0;
        } catch (\Exception $e) {
            \Log::error('Error calculating early leave minutes: ' . $e->getMessage());
            return 0;
        }
    }

    public function openEditModal($attendanceId)
    {
        try {
            $attendance = Attendance::with(['student', 'class.department'])->findOrFail($attendanceId);

            // Populate form
            $this->editingAttendanceId = $attendance->id;
            $this->editDate = $attendance->date->format('d/m/Y');
            $this->editStudentName = $attendance->student->full_name;
            $this->editStudentNis = $attendance->student->nis;
            $this->editStudentClass = $attendance->class->name . ' - ' . $attendance->class->department->name;
            $this->editCheckInTime = $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('H:i') : '';
            $this->editCheckOutTime = $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('H:i') : '';
            $this->editStatus = $attendance->status;
            $this->editNotes = $attendance->notes;

            $this->showEditModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuka data: ' . $e->getMessage());
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->reset([
            'editingAttendanceId', 'editDate', 'editStudentName',
            'editStudentNis', 'editStudentClass', 'editCheckInTime',
            'editCheckOutTime', 'editStatus', 'editNotes'
        ]);
        $this->resetValidation();
    }

    public function updateAttendance()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $attendance = Attendance::findOrFail($this->editingAttendanceId);

            // Capture old values for audit
            $oldValues = $attendance->only([
                'check_in_time', 'check_out_time', 'status',
                'late_minutes', 'early_leave_minutes', 'percentage', 'notes'
            ]);

            // Prepare new data
            $statusesWithoutTimes = ['izin', 'sakit', 'alpha', 'bolos'];

            if (in_array($this->editStatus, $statusesWithoutTimes)) {
                // These statuses don't require times
                $checkInTime = null;
                $checkOutTime = null;
                $lateMinutes = 0;
                $earlyLeaveMinutes = 0;
            } else {
                // Format times to HH:MM:SS
                $checkInTime = $this->editCheckInTime ? $this->editCheckInTime . ':00' : null;
                $checkOutTime = $this->editCheckOutTime ? $this->editCheckOutTime . ':00' : null;

                // Recalculate late/early minutes using local helper methods
                $checkInDateTime = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $this->editCheckInTime);
                $lateMinutes = $this->calculateLateMinutes($checkInDateTime);

                if ($checkOutTime) {
                    $checkOutDateTime = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $this->editCheckOutTime);
                    $earlyLeaveMinutes = $this->calculateEarlyLeaveMinutes($checkOutDateTime);
                } else {
                    $earlyLeaveMinutes = 0;
                }
            }

            // Calculate percentage
            $percentageMap = [
                'hadir' => 100, 'terlambat' => 75, 'izin' => 50,
                'sakit' => 50, 'dispensasi' => 75, 'pulang_cepat' => 75,
                'alpha' => 0, 'bolos' => 0, 'lupa_check_out' => 75,
            ];
            $percentage = $percentageMap[$this->editStatus] ?? 0;

            // Update attendance
            $attendance->update([
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'status' => $this->editStatus,
                'late_minutes' => $lateMinutes,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'percentage' => $percentage,
                'notes' => $this->editNotes,
            ]);

            // Create audit log
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'user_type' => 'admin',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'auditable_type' => 'Attendance',
                'auditable_id' => $attendance->id,
                'event' => 'updated',
                'old_values' => $oldValues,
                'new_values' => $attendance->fresh()->only([
                    'check_in_time', 'check_out_time', 'status',
                    'late_minutes', 'early_leave_minutes', 'percentage', 'notes'
                ]),
                'url' => request()->fullUrl(),
                'tags' => ['attendance_edit', 'admin_action'],
                'notes' => 'Data absensi diubah oleh admin: ' . auth()->user()->name,
            ]);

            DB::commit();

            session()->flash('success', 'Data absensi berhasil diperbarui');
            $this->closeEditModal();
            $this->calculateStatistics();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal memperbarui data: ' . $e->getMessage());
            \Log::error('Error updating attendance: ' . $e->getMessage());
        }
    }

    public function deleteAttendance($attendanceId)
    {
        try {
            DB::beginTransaction();

            $attendance = Attendance::with('student')->findOrFail($attendanceId);

            // Capture data for audit before deleting
            $deletedData = [
                'student_name' => $attendance->student->full_name,
                'student_nis' => $attendance->student->nis,
                'date' => $attendance->date->format('Y-m-d'),
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time,
                'status' => $attendance->status,
                'percentage' => $attendance->percentage,
            ];

            // Create audit log before deletion
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'user_type' => 'admin',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'auditable_type' => 'Attendance',
                'auditable_id' => $attendance->id,
                'event' => 'deleted',
                'old_values' => $deletedData,
                'new_values' => [],
                'url' => request()->fullUrl(),
                'tags' => ['attendance_delete', 'admin_action'],
                'notes' => 'Data absensi dihapus oleh admin: ' . auth()->user()->name,
            ]);

            // Soft delete
            $attendance->delete();

            DB::commit();

            session()->flash('success', 'Data absensi berhasil dihapus');
            $this->calculateStatistics();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menghapus data: ' . $e->getMessage());
            \Log::error('Error deleting attendance: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $attendances = Attendance::query()
            ->with(['student.class.department'])
            ->when($this->search, function ($query) {
                $query->whereHas('student', function ($q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('nis', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('date', '<=', $this->dateTo);
            })
            ->when($this->departmentFilter, function ($query) {
                $query->whereHas('student.class', function ($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->when($this->classFilter, function ($query) {
                $query->whereHas('student', function ($q) {
                    $q->where('class_id', $this->classFilter);
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->methodFilter !== '', function ($query) {
                $query->where('check_in_method', $this->methodFilter);
            })
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(20);

        $departments = Department::active()->orderBy('code')->get();

        $classes = collect();
        if ($this->departmentFilter) {
            $classes = Classes::where('department_id', $this->departmentFilter)
                ->active()
                ->orderBy('name')
                ->get();
        }

        // Get all classes for export dropdown (not filtered by department)
        $allClasses = Classes::active()->orderBy('name')->get();

        // Get all semesters for dropdown
        $semesters = \App\Models\Semester::orderBy('academic_year', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        return view('livewire.admin.attendance.attendance-index', [
            'attendances' => $attendances,
            'departments' => $departments,
            'classes' => $classes,
            'allClasses' => $allClasses,
            'semesters' => $semesters,
        ]);
    }
}
