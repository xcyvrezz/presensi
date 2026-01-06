<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class WaliKelasAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize, WithTitle
{
    protected $classId;
    protected $dateFrom;
    protected $dateTo;
    protected $statusFilter;
    protected $className;

    public function __construct($classId, $dateFrom = null, $dateTo = null, $statusFilter = '', $className = '')
    {
        $this->classId = $classId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->statusFilter = $statusFilter;
        $this->className = $className;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Attendance::with(['student', 'location'])
            ->where('class_id', $this->classId);

        // Apply date filters
        if ($this->dateFrom) {
            $query->whereDate('check_in_time', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('check_in_time', '<=', $this->dateTo);
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
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
            'E' => 12,
            'F' => 12,
            'G' => 15,
            'H' => 18,
            'I' => 20,
            'J' => 12,
            'K' => 12,
            'L' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'], // Green color for Wali Kelas
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
        $sheet->getStyle('A2:L' . $highestRow)->applyFromArray([
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
        $sheet->getStyle('E2:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G2:G' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H2:J' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K2:K' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return substr('Absensi ' . $this->className, 0, 31); // Excel sheet name limit is 31 characters
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
