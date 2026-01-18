<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class MonthlyAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $classId;
    protected $month;
    protected $year;
    protected $class;
    protected $monthName;
    protected $rowNumber = 0;

    public function __construct($classId, $month, $year)
    {
        $this->classId = $classId;
        $this->month = $month;
        $this->year = $year;
        $this->class = Classes::with('department')->find($classId);
        $this->monthName = Carbon::createFromDate($year, $month, 1)
            ->locale('id')
            ->translatedFormat('F');
    }

    public function collection()
    {
        // Get all active students in this class
        $students = Student::where('class_id', $this->classId)
            ->active()
            ->orderBy('full_name')
            ->get();

        // For each student, calculate their attendance summary
        $data = [];
        foreach ($students as $student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->whereYear('date', $this->year)
                ->whereMonth('date', $this->month)
                ->get();

            $data[] = [
                'student' => $student,
                'hadir' => $attendances->where('status', 'hadir')->count(),
                'terlambat' => $attendances->where('status', 'terlambat')->count(),
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'bolos' => $attendances->where('status', 'bolos')->count(),
                'alpha' => $attendances->where('status', 'alpha')->count(),
            ];
        }

        return collect($data);
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row['student']->nis,
            $row['student']->full_name,
            $this->class->name ?? '',
            $this->class->department->name ?? '',
            $row['hadir'],
            $row['terlambat'],
            $row['izin'],
            $row['sakit'],
            $row['bolos'],
            $row['alpha'],
        ];
    }

    public function headings(): array
    {
        return [
            ['Rekap data absensi bulan ' . $this->monthName . ' Tahun ' . $this->year],
            ['Kelas ' . ($this->class->name ?? '')],
            [''],
            [
                'No',
                'NIS',
                'Nama Siswa',
                'Kelas',
                'Jurusan',
                'Hadir',
                'Terlambat',
                'Izin',
                'Sakit',
                'Bolos',
                'Alpha',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(13);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(10);
        $sheet->getColumnDimension('K')->setWidth(10);

        // Style for title (row 1)
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'], // Blue 800
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Style for subtitle (row 2)
        $sheet->mergeCells('A2:K2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3B82F6'], // Blue 500
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(28);

        // Style for header row (row 4)
        $sheet->getStyle('A4:K4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Indigo 600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '1E293B'],
                ],
            ],
        ]);

        // Set row height for header
        $sheet->getRowDimension(4)->setRowHeight(30);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Apply borders to all data rows
                $sheet->getStyle('A4:K' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '94A3B8'],
                        ],
                    ],
                ]);

                // Center align for specific columns (No, NIS, Kelas, Jurusan, Keterangan columns)
                $sheet->getStyle('A5:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B5:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D5:K' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Left align for name column
                $sheet->getStyle('C5:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Add alternating row colors for data rows (starting from row 5)
                for ($row = 5; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F1F5F9'], // Slate 100
                            ],
                        ]);
                    }
                }

                // Add vertical alignment to all data cells
                $sheet->getStyle('A5:K' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Set row height for data rows
                for ($row = 5; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(22);
                }

                // Bold the numbers in attendance columns for better readability
                $sheet->getStyle('F5:K' . $lastRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                    ],
                ]);

                // Add color coding for attendance numbers
                for ($row = 5; $row <= $lastRow; $row++) {
                    // Hadir - Green
                    $sheet->getStyle('F' . $row)->applyFromArray([
                        'font' => ['color' => ['rgb' => '15803D']], // Green 700
                    ]);
                    // Terlambat - Yellow
                    $sheet->getStyle('G' . $row)->applyFromArray([
                        'font' => ['color' => ['rgb' => 'CA8A04']], // Yellow 700
                    ]);
                    // Izin - Blue
                    $sheet->getStyle('H' . $row)->applyFromArray([
                        'font' => ['color' => ['rgb' => '1D4ED8']], // Blue 700
                    ]);
                    // Sakit - Purple
                    $sheet->getStyle('I' . $row)->applyFromArray([
                        'font' => ['color' => ['rgb' => '7C3AED']], // Purple 600
                    ]);
                    // Bolos - Red
                    $sheet->getStyle('J' . $row)->applyFromArray([
                        'font' => ['color' => ['rgb' => 'DC2626']], // Red 600
                    ]);
                    // Alpha - Dark Gray
                    $sheet->getStyle('K' . $row)->applyFromArray([
                        'font' => ['color' => ['rgb' => '475569']], // Slate 600
                    ]);
                }
            },
        ];
    }

    public function title(): string
    {
        return 'Rekap Absensi';
    }
}
