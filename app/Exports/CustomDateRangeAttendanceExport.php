<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Student;
use App\Models\AcademicCalendar;
use App\Models\Semester;
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

class CustomDateRangeAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $classId;
    protected $startDate;
    protected $endDate;
    protected $class;
    protected $effectiveSchoolDays;
    protected $holidayDates;
    protected $rowNumber = 0;
    protected $actualStartDate;
    protected $actualEndDate;

    public function __construct($classId, $startDate, $endDate)
    {
        $this->classId = $classId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->class = Classes::with('department')->find($classId);

        // Parse the input dates
        $customStartDate = Carbon::parse($startDate);
        $customEndDate = Carbon::parse($endDate);

        // Find overlapping semesters
        $overlappingSemesters = Semester::findOverlapping($customStartDate, $customEndDate);

        // If there are overlapping semesters, use the intersection with semester boundaries
        if ($overlappingSemesters->isNotEmpty()) {
            // Use the first overlapping semester
            $semester = $overlappingSemesters->first();
            [$this->actualStartDate, $this->actualEndDate] = $semester->getIntersectionDates($customStartDate, $customEndDate);
        } else {
            // No semester found, use the full custom range
            $this->actualStartDate = $customStartDate;
            $this->actualEndDate = $customEndDate;
        }

        // Get holiday dates in the actual date range
        $this->holidayDates = AcademicCalendar::getHolidayDates(
            $this->actualStartDate->format('Y-m-d'),
            $this->actualEndDate->format('Y-m-d')
        );

        // Calculate effective school days (exclude weekends and holidays)
        $this->effectiveSchoolDays = $this->calculateEffectiveSchoolDays(
            $this->actualStartDate,
            $this->actualEndDate
        );
    }

    /**
     * Calculate effective school days (exclude Saturday, Sunday, and holidays)
     */
    protected function calculateEffectiveSchoolDays($startDate, $endDate): int
    {
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $effectiveDays = 0;

        while ($current->lte($end)) {
            $dateString = $current->format('Y-m-d');

            // Exclude Saturday (6) and Sunday (0)
            $isWeekend = $current->dayOfWeek == 0 || $current->dayOfWeek == 6;

            // Exclude holidays
            $isHoliday = in_array($dateString, $this->holidayDates);

            if (!$isWeekend && !$isHoliday) {
                $effectiveDays++;
            }

            $current->addDay();
        }

        return $effectiveDays;
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
                ->whereBetween('date', [
                    $this->actualStartDate->format('Y-m-d'),
                    $this->actualEndDate->format('Y-m-d')
                ])
                ->get();

            $hadirCount = $attendances->where('status', 'hadir')->count();
            $terlambatCount = $attendances->where('status', 'terlambat')->count();
            $dispensasiCount = $attendances->where('status', 'dispensasi')->count();
            $tidakCheckoutCount = $attendances->where('status', 'tidak checkout')->count();

            // Total kehadiran (hadir + terlambat + dispensasi)
            $totalKehadiran = $hadirCount + $terlambatCount + $dispensasiCount;

            // Percentage calculation
            $percentage = $this->effectiveSchoolDays > 0
                ? round(($totalKehadiran / $this->effectiveSchoolDays) * 100, 2)
                : 0;

            $data[] = [
                'student' => $student,
                'hadir' => $hadirCount,
                'terlambat' => $terlambatCount,
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'dispensasi' => $dispensasiCount,
                'bolos' => $attendances->where('status', 'bolos')->count(),
                'alpha' => $attendances->where('status', 'alpha')->count(),
                'tidak checkout' => $tidakCheckoutCount,
                'total_kehadiran' => $totalKehadiran,
                'percentage' => $percentage,
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
            $row['dispensasi'],
            $row['bolos'],
            $row['alpha'],
            $row['tidak checkout'],
            $row['total_kehadiran'],
            $this->effectiveSchoolDays,
            $row['percentage'] . '%',
        ];
    }

    public function headings(): array
    {
        $startDateFormatted = Carbon::parse($this->startDate)->locale('id')->translatedFormat('d F Y');
        $endDateFormatted = Carbon::parse($this->endDate)->locale('id')->translatedFormat('d F Y');

        return [
            ['REKAP DATA ABSENSI PERIODE KUSTOM'],
            ['Periode: ' . $startDateFormatted . ' s/d ' . $endDateFormatted],
            ['Kelas: ' . ($this->class->name ?? '') . ' | Jurusan: ' . ($this->class->department->name ?? '') . ' | Hari Efektif: ' . $this->effectiveSchoolDays . ' hari'],
            [],
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
                'Dispensasi',
                'Bolos',
                'Alpha',
                'Tidak Checkout',
                'Total Hadir',
                'Hari Efektif',
                'Persentase',
            ],
        ];
    }

    public function title(): string
    {
        return 'Rekap Absensi Custom';
    }

    public function styles(Worksheet $sheet)
    {
        // Title row styling
        $sheet->mergeCells('A1:P1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F59E0B'], // Amber color for custom
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Period row styling
        $sheet->mergeCells('A2:P2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F59E0B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Info row styling
        $sheet->mergeCells('A3:P3');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF3C7'], // Light amber
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Header row styling (row 5)
        $sheet->getStyle('A5:P5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D97706'], // Darker amber
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

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(8);
        $sheet->getColumnDimension('I')->setWidth(8);
        $sheet->getColumnDimension('J')->setWidth(10);
        $sheet->getColumnDimension('K')->setWidth(8);
        $sheet->getColumnDimension('L')->setWidth(8);
        $sheet->getColumnDimension('M')->setWidth(13);
        $sheet->getColumnDimension('N')->setWidth(12);
        $sheet->getColumnDimension('O')->setWidth(12);
        $sheet->getColumnDimension('P')->setWidth(12);

        // Set row heights
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(5)->setRowHeight(25);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Apply borders and alternating colors to data rows
                for ($row = 6; $row <= $highestRow; $row++) {
                    // Borders
                    $sheet->getStyle("A{$row}:P{$row}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC'],
                            ],
                        ],
                    ]);

                    // Alternating row colors
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A{$row}:P{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFFBEB'], // Very light amber
                            ],
                        ]);
                    }

                    // Center align numeric columns
                    $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$row}:P{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Conditional formatting for percentage
                    $percentageCell = "P{$row}";
                    $percentageValue = (float) str_replace('%', '', $sheet->getCell($percentageCell)->getValue());

                    if ($percentageValue >= 90) {
                        $sheet->getStyle($percentageCell)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => '15803D']], // Green
                        ]);
                    } elseif ($percentageValue >= 75) {
                        $sheet->getStyle($percentageCell)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'D97706']], // Amber
                        ]);
                    } else {
                        $sheet->getStyle($percentageCell)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'DC2626']], // Red
                        ]);
                    }
                }
            },
        ];
    }
}
