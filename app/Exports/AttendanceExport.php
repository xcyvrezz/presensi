<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $dateFrom;
    protected $dateTo;
    protected $departmentFilter;
    protected $classFilter;
    protected $statusFilter;
    protected $methodFilter;
    protected $search;

    public function __construct($dateFrom = null, $dateTo = null, $departmentFilter = '', $classFilter = '', $statusFilter = '', $methodFilter = '', $search = '')
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->departmentFilter = $departmentFilter;
        $this->classFilter = $classFilter;
        $this->statusFilter = $statusFilter;
        $this->methodFilter = $methodFilter;
        $this->search = $search;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Attendance::with(['student.class.department', 'location']);

        // Apply date filters
        if ($this->dateFrom) {
            $query->whereDate('check_in_time', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('check_in_time', '<=', $this->dateTo);
        }

        // Apply department filter
        if ($this->departmentFilter) {
            $query->whereHas('student.class', function ($q) {
                $q->where('department_id', $this->departmentFilter);
            });
        }

        // Apply class filter
        if ($this->classFilter) {
            $query->where('class_id', $this->classFilter);
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Apply method filter
        if ($this->methodFilter) {
            $query->where('method', $this->methodFilter);
        }

        // Apply search
        if ($this->search) {
            $query->whereHas('student', function ($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('nis', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('check_in_time', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jurusan',
            'Check-In',
            'Check-Out',
            'Status',
            'Terlambat (Menit)',
            'Pulang Cepat (Menit)',
            'Persentase',
            'Metode',
            'Lokasi',
        ];
    }

    /**
     * @var Attendance $attendance
     */
    public function map($attendance): array
    {
        static $row = 0;
        $row++;

        return [
            $row,
            $attendance->check_in_time ? $attendance->check_in_time->format('d/m/Y') : '-',
            $attendance->student->nis ?? '-',
            $attendance->student->full_name ?? '-',
            $attendance->student->class->name ?? '-',
            $attendance->student->class->department->name ?? '-',
            $attendance->check_in_time ? $attendance->check_in_time->format('H:i:s') : '-',
            $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '-',
            $this->getStatusLabel($attendance->status),
            $attendance->late_minutes ?? 0,
            $attendance->early_leave_minutes ?? 0,
            $attendance->percentage . '%',
            $this->getMethodLabel($attendance->method),
            $attendance->location->name ?? 'Physical Reader',
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 12,
            'C' => 12,
            'D' => 25,
            'E' => 15,
            'F' => 20,
            'G' => 12,
            'H' => 12,
            'I' => 15,
            'J' => 18,
            'K' => 20,
            'L' => 12,
            'M' => 12,
            'N' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563eb'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Get the highest row
        $highestRow = $sheet->getHighestRow();

        // Data styling
        $sheet->getStyle('A2:N' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Center align for specific columns
        $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G2:H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I2:I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J2:L' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('M2:M' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    private function getStatusLabel($status)
    {
        return match($status) {
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Alpha',
            'pulang_cepat' => 'Pulang Cepat',
            default => $status,
        };
    }

    private function getMethodLabel($method)
    {
        return match($method) {
            'nfc' => 'NFC',
            'rfid' => 'RFID',
            'manual' => 'Manual',
            'qr' => 'QR Code',
            default => $method,
        };
    }
}
