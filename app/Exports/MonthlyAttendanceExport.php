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
        $this->monthName = Carbon::createFromDate($year, $month, 1)->locale('id')->translatedFormat('F');

        $monthStartDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEndDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        $overlappingSemesters = Semester::findOverlapping($monthStartDate, $monthEndDate);

        if ($overlappingSemesters->isNotEmpty()) {
            $semester = $overlappingSemesters->first();
            [$this->actualStartDate, $this->actualEndDate] = $semester->getIntersectionDates($monthStartDate, $monthEndDate);
        } else {
            $this->actualStartDate = $monthStartDate;
            $this->actualEndDate = $monthEndDate;
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
            $isWeekend = $current->isWeekend();
            $isHoliday = in_array($dateString, $this->holidayDates, true);

            if (!$isWeekend && !$isHoliday) {
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
        return [
            ['Rekap Absensi Bulanan ' . $this->monthName . ' ' . $this->year],
            ['Kelas: ' . ($this->class->name ?? '') . ' - ' . ($this->class->department->name ?? '') . ' | Hari Efektif: ' . $this->effectiveSchoolDays . ' hari'],
            [''],
            ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Jurusan', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Dispensasi', 'Bolos', 'Alpha', 'Tidak Checkout', 'Total Hadir', 'Hari Efektif', 'Persentase'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(6); $sheet->getColumnDimension('B')->setWidth(13); $sheet->getColumnDimension('C')->setWidth(30); $sheet->getColumnDimension('D')->setWidth(14); $sheet->getColumnDimension('E')->setWidth(14); $sheet->getColumnDimension('F')->setWidth(10); $sheet->getColumnDimension('G')->setWidth(11); $sheet->getColumnDimension('H')->setWidth(10); $sheet->getColumnDimension('I')->setWidth(10); $sheet->getColumnDimension('J')->setWidth(11); $sheet->getColumnDimension('K')->setWidth(10); $sheet->getColumnDimension('L')->setWidth(10); $sheet->getColumnDimension('M')->setWidth(13); $sheet->getColumnDimension('N')->setWidth(11); $sheet->getColumnDimension('O')->setWidth(11); $sheet->getColumnDimension('P')->setWidth(11);
        $sheet->mergeCells('A1:P1'); $sheet->mergeCells('A2:P2');
        $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]);
        $sheet->getStyle('A2')->applyFromArray(['font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]);
        $sheet->getStyle('A4:P4')->applyFromArray(['font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1E293B']]]]);
        $sheet->getRowDimension(1)->setRowHeight(35); $sheet->getRowDimension(2)->setRowHeight(25); $sheet->getRowDimension(4)->setRowHeight(30);
        return [];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $lastRow = $sheet->getHighestRow();
            $sheet->getStyle('A4:P' . $lastRow)->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]]]);
            $sheet->getStyle('A5:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B5:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D5:P' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C5:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            for ($row = 5; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':P' . $row)->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']]]);
                }
                $sheet->getRowDimension($row)->setRowHeight(22);
            }
            $sheet->getStyle('A5:P' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('F5:P' . $lastRow)->applyFromArray(['font' => ['bold' => true, 'size' => 10]]);
            for ($row = 5; $row <= $lastRow; $row++) {
                $sheet->getStyle('F' . $row)->applyFromArray(['font' => ['color' => ['rgb' => '15803D']]]);
                $sheet->getStyle('G' . $row)->applyFromArray(['font' => ['color' => ['rgb' => 'CA8A04']]]);
                $sheet->getStyle('H' . $row)->applyFromArray(['font' => ['color' => ['rgb' => '1D4ED8']]]);
                $sheet->getStyle('I' . $row)->applyFromArray(['font' => ['color' => ['rgb' => '7C3AED']]]);
                $sheet->getStyle('J' . $row)->applyFromArray(['font' => ['color' => ['rgb' => '0891B2']]]);
                $sheet->getStyle('K' . $row)->applyFromArray(['font' => ['color' => ['rgb' => 'DC2626']]]);
                $sheet->getStyle('L' . $row)->applyFromArray(['font' => ['color' => ['rgb' => '475569']]]);
                $sheet->getStyle('M' . $row)->applyFromArray(['font' => ['color' => ['rgb' => 'EA580C']]]);
                $sheet->getStyle('N' . $row)->applyFromArray(['font' => ['color' => ['rgb' => '047857'], 'bold' => true]]);
                $percentValue = (float) str_replace('%', '', $sheet->getCell('P' . $row)->getValue());
                $color = $percentValue >= 90 ? '047857' : ($percentValue >= 75 ? 'CA8A04' : 'DC2626');
                $sheet->getStyle('P' . $row)->applyFromArray(['font' => ['color' => ['rgb' => $color], 'bold' => true]]);
            }
        }];
    }

    public function title(): string
    {
        return 'Rekap Absensi';
    }
}
