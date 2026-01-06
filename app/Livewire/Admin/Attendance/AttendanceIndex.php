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

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'departmentFilter' => ['except' => ''],
        'classFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'methodFilter' => ['except' => ''],
    ];

    public function mount()
    {
        // Set default date range (today)
        $this->dateTo = Carbon::today()->format('Y-m-d');
        $this->dateFrom = Carbon::today()->format('Y-m-d');

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

        return view('livewire.admin.attendance.attendance-index', [
            'attendances' => $attendances,
            'departments' => $departments,
            'classes' => $classes,
        ]);
    }
}
