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

class MonthlyAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $classId;
    protected $month;
    protected $year;
    protected $class;
    protected $monthName;
    protected $effectiveSchoolDays;
    protected $holidayDates;
    protected $rowNumber = 0;
    protected $actualStartDate;
    protected $actualEndDate;

    public function __construct($classId, $month, $year)
    {
        $this->classId = $classId;
        $this->month = $month;
        $this->year = $year;
        $this->class = Classes::with('department')->find($classId);
        $this->monthName = Carbon::createFromDate($year, $month, 1)
            ->locale('id')
            ->translatedFormat('F');

        // Calculate date range for the month
        $monthStartDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEndDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Find overlapping semesters
        $overlappingSemesters = Semester::findOverlapping($monthStartDate, $monthEndDate);

        // If there are overlapping semesters, use the intersection with semester boundaries
        if ($overlappingSemesters->isNotEmpty()) {
            // Use the first overlapping semester
            $semester = $overlappingSemesters->first();
            [$this->actualStartDate, $this->actualEndDate] = $semester->getIntersectionDates($monthStartDate, $monthEndDate);
        } else {
            // No semester found, use the full month range
            $this->actualStartDate = $monthStartDate;
            $this->actualEndDate = $monthEndDate;
        }

        // Get holiday dates in the actual date range
        $this->holidayDates = AcademicCalendar::getHolidayDates(
            $this->actualStartDate->format('Y-m-d'),
            $this->actualEndDate->format('Y-m-d')
        );

        // Calculate effective school days (exclude weekends and holidays)
        $this->effectiveSchoolDays = $this->calculateEffectiveSchoolDays($this->actualStartDate, $this->actualEndDate);
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
            $row['total_kehadiran'],
            $this->effectiveSchoolDays,
            $row['percentage'] . '%',
        ];
    }

    public function headings(): array
    {
        return [
            ['Rekap Absensi Bulanan ' . $this->monthName . ' ' . $this->year],
            ['Kelas: ' . ($this->class->name ?? '') . ' - ' . ($this->class->department->name ?? '') . ' | Hari Efektif: ' . $this->effectiveSchoolDays . ' hari'],
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
                'Dispensasi',
                'Bolos',
                'Alpha',
                'Total Hadir',
                'Hari Efektif',
                'Persentase',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(13);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(11);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(11);
        $sheet->getColumnDimension('K')->setWidth(10);
        $sheet->getColumnDimension('L')->setWidth(10);
        $sheet->getColumnDimension('M')->setWidth(11);
        $sheet->getColumnDimension('N')->setWidth(11);
        $sheet->getColumnDimension('O')->setWidth(11);

        // Style for title (row 1)
        $sheet->mergeCells('A1:O1');
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
        $sheet->mergeCells('A2:O2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
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
        $sheet->getRowDimension(2)->setRowHeight(25);

        // Style for header row (row 4)
        $sheet->getStyle('A4:O4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
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
                $sheet->getStyle('A4:O' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '94A3B8'],
                        ],
                    ],
                ]);

                // Center align for specific columns
                $sheet->getStyle('A5:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B5:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D5:O' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Left align for name column
                $sheet->getStyle('C5:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Add alternating row colors for data rows (starting from row 5)
                for ($row = 5; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F1F5F9'], // Slate 100
                            ],
                        ]);
                    }
                }

                // Add vertical alignment to all data cells
                $sheet->getStyle('A5:O' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Set row height for data rows
                for ($row = 5; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(22);
                }

                // Bold the numbers in attendance columns for better readability
                $sheet->getStyle('F5:O' . $lastRow)->applyFromArray([
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
                    // Dispensasi - Cyan
                    $sheet->getStyle('J' . $row)->applyFromArray([
                        'font' => ['color' => ['rgb' => '0891B2']], // Cyan 600
                    ]);
                    // Bolos - Red
                    $sheet->getStyle('K' . $row)->applyFromArray([
                        'font' => ['color' => ['rgb' => 'DC2626']], // Red 600
                    ]);
                    // Alpha - Dark Gray
                    $sheet->getStyle('L' . $row)->applyFromArray([
                        'font' => ['color' => ['rgb' => '475569']], // Slate 600
                    ]);
                    // Total Kehadiran - Bold Green
                    $sheet->getStyle('M' . $row)->applyFromArray([
                        'font' => ['color' => ['rgb' => '047857'], 'bold' => true], // Green 700
                    ]);

                    // Percentage - Bold with conditional formatting
                    $cellValue = $sheet->getCell('O' . $row)->getValue();
                    $percentValue = floatval(str_replace('%', '', $cellValue));

                    if ($percentValue >= 90) {
                        // Green for >= 90%
                        $sheet->getStyle('O' . $row)->applyFromArray([
                            'font' => ['color' => ['rgb' => '047857'], 'bold' => true],
                        ]);
                    } elseif ($percentValue >= 75) {
                        // Yellow for 75-89%
                        $sheet->getStyle('O' . $row)->applyFromArray([
                            'font' => ['color' => ['rgb' => 'CA8A04'], 'bold' => true],
                        ]);
                    } else {
                        // Red for < 75%
                        $sheet->getStyle('O' . $row)->applyFromArray([
                            'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true],
                        ]);
                    }
                }
            },
        ];
    }

    public function title(): string
    {
        return 'Rekap Absensi';
    }
}
