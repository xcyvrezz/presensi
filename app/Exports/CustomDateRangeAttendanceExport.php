<?php

namespace App\Exports;

use App\Models\AcademicCalendar;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Semester;
use App\Models\Student;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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

        $customStartDate = Carbon::parse($startDate);
        $customEndDate = Carbon::parse($endDate);
        $overlappingSemesters = Semester::findOverlapping($customStartDate, $customEndDate);

        if ($overlappingSemesters->isNotEmpty()) {
            $semester = $overlappingSemesters->first();
            [$this->actualStartDate, $this->actualEndDate] = $semester->getIntersectionDates($customStartDate, $customEndDate);
        } else {
            $this->actualStartDate = $customStartDate;
            $this->actualEndDate = $customEndDate;
        }

        $this->holidayDates = AcademicCalendar::getHolidayDates(
            $this->actualStartDate->format('Y-m-d'),
            $this->actualEndDate->format('Y-m-d'),
            $this->class->department_id ?? null,
            $this->classId
        );

        $this->effectiveSchoolDays = $this->calculateEffectiveSchoolDays($this->actualStartDate, $this->actualEndDate);
    }

    protected function calculateEffectiveSchoolDays($startDate, $endDate): int
    {
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $effectiveDays = 0;

        while ($current->lte($end)) {
            $dateString = $current->format('Y-m-d');
            if (!$current->isWeekend() && !in_array($dateString, $this->holidayDates, true)) {
                $effectiveDays++;
            }
            $current->addDay();
        }

        return $effectiveDays;
    }

    public function collection()
    {
        $students = Student::where('class_id', $this->classId)->active()->orderBy('full_name')->get();
        $data = [];

        foreach ($students as $student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->whereBetween('date', [$this->actualStartDate->format('Y-m-d'), $this->actualEndDate->format('Y-m-d')])
                ->get();

            $hadirCount = $attendances->where('status', 'hadir')->count();
            $terlambatCount = $attendances->where('status', 'terlambat')->count();
            $dispensasiCount = $attendances->where('status', 'dispensasi')->count();
            $tidakCheckoutCount = $attendances->filter(fn ($attendance) => $attendance->check_in_time && !$attendance->check_out_time && in_array($attendance->status, ['hadir', 'terlambat'], true))->count();
            $totalKehadiran = $hadirCount + $terlambatCount + $dispensasiCount;
            $percentage = $this->effectiveSchoolDays > 0 ? round(($totalKehadiran / $this->effectiveSchoolDays) * 100, 2) : 0;

            $data[] = [
                'student' => $student,
                'hadir' => $hadirCount,
                'terlambat' => $terlambatCount,
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'dispensasi' => $dispensasiCount,
                'bolos' => $attendances->where('status', 'bolos')->count(),
                'alpha' => $attendances->where('status', 'alpha')->count(),
                'tidak_checkout' => $tidakCheckoutCount,
                'total_kehadiran' => $totalKehadiran,
                'percentage' => $percentage,
            ];
        }

        return collect($data);
    }

    public function map($row): array
    {
        $this->rowNumber++;
        return [$this->rowNumber, $row['student']->nis, $row['student']->full_name, $this->class->name ?? '', $this->class->department->name ?? '', $row['hadir'], $row['terlambat'], $row['izin'], $row['sakit'], $row['dispensasi'], $row['bolos'], $row['alpha'], $row['tidak_checkout'], $row['total_kehadiran'], $this->effectiveSchoolDays, $row['percentage'] . '%'];
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
            ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Jurusan', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Dispensasi', 'Bolos', 'Alpha', 'Tidak Checkout', 'Total Hadir', 'Hari Efektif', 'Persentase'],
        ];
    }

    public function title(): string
    {
        return 'Rekap Absensi Custom';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:P1'); $sheet->mergeCells('A2:P2'); $sheet->mergeCells('A3:P3');
        $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F59E0B']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]);
        $sheet->getStyle('A2')->applyFromArray(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F59E0B']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]);
        $sheet->getStyle('A3')->applyFromArray(['font' => ['bold' => true, 'size' => 10], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('A5:P5')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]]);
        $sheet->getColumnDimension('A')->setWidth(5); $sheet->getColumnDimension('B')->setWidth(12); $sheet->getColumnDimension('C')->setWidth(25); $sheet->getColumnDimension('D')->setWidth(15); $sheet->getColumnDimension('E')->setWidth(20); $sheet->getColumnDimension('F')->setWidth(8); $sheet->getColumnDimension('G')->setWidth(10); $sheet->getColumnDimension('H')->setWidth(8); $sheet->getColumnDimension('I')->setWidth(8); $sheet->getColumnDimension('J')->setWidth(10); $sheet->getColumnDimension('K')->setWidth(8); $sheet->getColumnDimension('L')->setWidth(8); $sheet->getColumnDimension('M')->setWidth(13); $sheet->getColumnDimension('N')->setWidth(12); $sheet->getColumnDimension('O')->setWidth(12); $sheet->getColumnDimension('P')->setWidth(12);
        $sheet->getRowDimension(1)->setRowHeight(30); $sheet->getRowDimension(2)->setRowHeight(25); $sheet->getRowDimension(3)->setRowHeight(20); $sheet->getRowDimension(5)->setRowHeight(25);
        return [];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $highestRow = $sheet->getHighestRow();
            for ($row = 6; $row <= $highestRow; $row++) {
                $sheet->getStyle("A{$row}:P{$row}")->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]]);
                if ($row % 2 == 0) {
                    $sheet->getStyle("A{$row}:P{$row}")->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFBEB']]]);
                }
                $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$row}:P{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $percentageCell = "P{$row}";
                $percentageValue = (float) str_replace('%', '', $sheet->getCell($percentageCell)->getValue());
                $color = $percentageValue >= 90 ? '15803D' : ($percentageValue >= 75 ? 'D97706' : 'DC2626');
                $sheet->getStyle($percentageCell)->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => $color]]]);
            }
        }];
    }
}

