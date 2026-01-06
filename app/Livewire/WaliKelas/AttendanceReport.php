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

        return view('livewire.wali-kelas.attendance-report', [
            'attendances' => $attendances,
        ]);
    }
}
