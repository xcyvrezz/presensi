<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $departmentId;
    protected $classId;

    public function __construct($startDate, $endDate, $departmentId = 'all', $classId = 'all')
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->departmentId = $departmentId;
        $this->classId = $classId;
    }

    public function collection()
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);

        // Base query for students
        $studentsQuery = Student::where('is_active', true);

        if ($this->departmentId !== 'all') {
            $studentsQuery->whereHas('class', function ($q) {
                $q->where('department_id', $this->departmentId);
            });
        }

        if ($this->classId !== 'all') {
            $studentsQuery->where('class_id', $this->classId);
        }

        $students = $studentsQuery->with(['class.department'])->get();

        $data = [];
        $no = 1;

        foreach ($students as $student) {
            // PERBAIKAN: gunakan field 'date' bukan 'check_in_time'
            $attendances = Attendance::where('student_id', $student->id)
                ->whereBetween('date', [
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                ])
                ->get();

            $hadir = $attendances->where('status', 'hadir')->count();
            $terlambat = $attendances->where('status', 'terlambat')->count();
            $izin = $attendances->where('status', 'izin')->count();
            $sakit = $attendances->where('status', 'sakit')->count();
            $alpha = $attendances->where('status', 'alpha')->count();
            $dispensasi = $attendances->where('status', 'dispensasi')->count();

            $workingDays = $this->getWorkingDaysBetween($startDate, $endDate);
            $totalPresent = $hadir + $terlambat;
            $percentage = $workingDays > 0 ? round(($totalPresent / $workingDays) * 100, 2) : 0;

            $data[] = [
                'no' => $no++,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'nama' => $student->full_name,
                'kelas' => $student->class->name ?? '-',
                'jurusan' => $student->class->department->name ?? '-',
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpha' => $alpha,
                'dispensasi' => $dispensasi,
                'total_kehadiran' => $totalPresent,
                'hari_kerja' => $workingDays,
                'persentase' => $percentage . '%',
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'NISN',
            'Nama Lengkap',
            'Kelas',
            'Jurusan',
            'Hadir',
            'Terlambat',
            'Izin',
            'Sakit',
            'Alpha',
            'Dispensasi',
            'Total Kehadiran',
            'Hari Kerja',
            'Persentase',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 12,
            'C' => 15,
            'D' => 30,
            'E' => 15,
            'F' => 20,
            'G' => 10,
            'H' => 12,
            'I' => 10,
            'J' => 10,
            'K' => 10,
            'L' => 12,
            'M' => 18,
            'N' => 12,
            'O' => 12,
        ];
    }

    public function title(): string
    {
        return 'Laporan Kehadiran';
    }

    private function getWorkingDaysBetween($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workingDays = 0;

        while ($start <= $end) {
            if ($start->isWeekday()) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }
}
