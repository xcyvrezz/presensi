<?php

namespace App\Livewire\Admin\Analytics;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Department;
use App\Models\Semester;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AttendanceExport;

#[Layout('layouts.admin')]
#[Title('Analitik & Laporan Lanjutan')]
class AdvancedReports extends Component
{
    public $reportType = 'overview'; // overview, trends, comparison, detailed, timeanalysis, insights
    public $dateRange = '30days';
    public $customStartDate;
    public $customEndDate;
    public $selectedDepartment = '';
    public $selectedClass = '';
    public $selectedSemester = '';

    public $chartData = [];
    public $statistics = [];
    public $insights = [];

    public function mount()
    {
        $this->customStartDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->customEndDate = Carbon::now()->format('Y-m-d');

        // Load initial data
        $this->loadAnalytics();
    }

    public function updatedDateRange()
    {
        $this->setDateRangeFromPreset();
        $this->loadAnalytics();
    }

    public function updatedReportType()
    {
        $this->loadAnalytics();
    }

    public function updatedSelectedDepartment()
    {
        $this->selectedClass = '';
        $this->loadAnalytics();
    }

    public function updatedSelectedClass()
    {
        $this->loadAnalytics();
    }

    public function applyCustomDate()
    {
        $this->dateRange = 'custom';
        $this->loadAnalytics();
    }

    private function setDateRangeFromPreset()
    {
        switch ($this->dateRange) {
            case '7days':
                $this->customStartDate = Carbon::now()->subDays(7)->format('Y-m-d');
                $this->customEndDate = Carbon::now()->format('Y-m-d');
                break;
            case '30days':
                $this->customStartDate = Carbon::now()->subDays(30)->format('Y-m-d');
                $this->customEndDate = Carbon::now()->format('Y-m-d');
                break;
            case '3months':
                $this->customStartDate = Carbon::now()->subMonths(3)->format('Y-m-d');
                $this->customEndDate = Carbon::now()->format('Y-m-d');
                break;
            case '6months':
                $this->customStartDate = Carbon::now()->subMonths(6)->format('Y-m-d');
                $this->customEndDate = Carbon::now()->format('Y-m-d');
                break;
            case 'semester':
                $activeSemester = Semester::where('is_active', true)->first();
                if ($activeSemester) {
                    $this->customStartDate = $activeSemester->start_date->format('Y-m-d');
                    $this->customEndDate = $activeSemester->end_date->format('Y-m-d');
                }
                break;
        }
    }

    public function loadAnalytics()
    {
        $startDate = Carbon::parse($this->customStartDate);
        $endDate = Carbon::parse($this->customEndDate);

        switch ($this->reportType) {
            case 'overview':
                $this->loadOverviewData($startDate, $endDate);
                break;
            case 'trends':
                $this->loadTrendsData($startDate, $endDate);
                break;
            case 'comparison':
                $this->loadComparisonData($startDate, $endDate);
                break;
            case 'detailed':
                $this->loadDetailedData($startDate, $endDate);
                break;
            case 'timeanalysis':
                $this->loadTimeAnalysisData($startDate, $endDate);
                break;
            case 'insights':
                $this->loadInsightsData($startDate, $endDate);
                break;
        }
    }

    private function loadOverviewData($startDate, $endDate)
    {
        $query = Attendance::whereBetween('date', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ]);

        if ($this->selectedClass) {
            $query->where('class_id', $this->selectedClass);
        } elseif ($this->selectedDepartment) {
            $query->whereHas('student.class', function ($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
        }

        $attendances = $query->get();
        $totalStudents = Student::where('is_active', true)
            ->when($this->selectedClass, fn($q) => $q->where('class_id', $this->selectedClass))
            ->when($this->selectedDepartment && !$this->selectedClass, function ($q) {
                $q->whereHas('class', fn($sq) => $sq->where('department_id', $this->selectedDepartment));
            })
            ->count();

        $workingDays = $this->getWorkingDays($startDate, $endDate);

        // Calculate completion rate
        $totalRecords = $attendances->count();
        $expectedRecords = $totalStudents * $workingDays;
        $completionRate = $expectedRecords > 0 ? round(($totalRecords / $expectedRecords) * 100, 1) : 0;

        // Calculate positive attendance (hadir, terlambat, dispensasi)
        $positiveAttendance = $attendances->whereIn('status', ['hadir', 'terlambat', 'dispensasi'])->count();

        // Calculate problem cases (alpha, bolos)
        $problemCases = $attendances->whereIn('status', ['alpha', 'bolos'])->count();

        $this->statistics = [
            'total_records' => $totalRecords,
            'total_students' => $totalStudents,
            'working_days' => $workingDays,
            'expected_records' => $expectedRecords,
            'completion_rate' => $completionRate,
            'positive_attendance' => $positiveAttendance,
            'problem_cases' => $problemCases,
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
            'dispensasi' => $attendances->where('status', 'dispensasi')->count(),
            'bolos' => $attendances->where('status', 'bolos')->count(),
            'pulang_cepat' => $attendances->where('status', 'pulang_cepat')->count(),
            'lupa_checkout' => $attendances->filter(function($a) {
                return $a->check_in_time && !$a->check_out_time && in_array($a->status, ['hadir', 'terlambat']);
            })->count(),
            'avg_late_minutes' => round($attendances->where('status', 'terlambat')->avg('late_minutes') ?? 0, 1),
            'max_late_minutes' => $attendances->where('status', 'terlambat')->max('late_minutes') ?? 0,
            'avg_percentage' => round($attendances->avg('percentage') ?? 0, 1),
            'attendance_rate' => $expectedRecords > 0
                ? round(($positiveAttendance / $expectedRecords) * 100, 1)
                : 0,
        ];

        // Pie chart data for status distribution
        $this->chartData['statusDistribution'] = [
            'labels' => ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', 'Dispensasi', 'Bolos', 'Pulang Cepat'],
            'data' => [
                $this->statistics['hadir'],
                $this->statistics['terlambat'],
                $this->statistics['izin'],
                $this->statistics['sakit'],
                $this->statistics['alpha'],
                $this->statistics['dispensasi'],
                $this->statistics['bolos'],
                $this->statistics['pulang_cepat'],
            ],
            'colors' => [
                '#10b981', // hadir - green
                '#f59e0b', // terlambat - yellow
                '#3b82f6', // izin - blue
                '#a855f7', // sakit - purple
                '#6b7280', // alpha - gray
                '#06b6d4', // dispensasi - cyan
                '#ef4444', // bolos - red
                '#f97316', // pulang_cepat - orange
            ],
        ];
    }

    private function loadTrendsData($startDate, $endDate)
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $dates = [];
        $hadirData = [];
        $terlambatData = [];
        $izinData = [];
        $sakitData = [];

        foreach ($period as $date) {
            if ($date->isWeekday()) {
                $dates[] = $date->format('d/m');

                $dayAttendances = Attendance::whereDate('date', $date)
                    ->when($this->selectedClass, fn($q) => $q->where('class_id', $this->selectedClass))
                    ->when($this->selectedDepartment && !$this->selectedClass, function ($q) {
                        $q->whereHas('student.class', fn($sq) => $sq->where('department_id', $this->selectedDepartment));
                    })
                    ->get();

                $hadirData[] = $dayAttendances->where('status', 'hadir')->count();
                $terlambatData[] = $dayAttendances->where('status', 'terlambat')->count();
                $izinData[] = $dayAttendances->where('status', 'izin')->count();
                $sakitData[] = $dayAttendances->where('status', 'sakit')->count();
            }
        }

        $this->chartData['trends'] = [
            'labels' => $dates,
            'datasets' => [
                ['label' => 'Hadir', 'data' => $hadirData],
                ['label' => 'Terlambat', 'data' => $terlambatData],
                ['label' => 'Izin', 'data' => $izinData],
                ['label' => 'Sakit', 'data' => $sakitData],
            ],
        ];
    }

    private function loadComparisonData($startDate, $endDate)
    {
        $departments = Department::with('classes')->get();
        $labels = [];
        $hadirData = [];
        $terlambatData = [];
        $attendanceRates = [];

        foreach ($departments as $dept) {
            $labels[] = $dept->code;

            $deptAttendances = Attendance::whereBetween('date', [
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                ])
                ->whereHas('student.class', fn($q) => $q->where('department_id', $dept->id))
                ->get();

            $hadirData[] = $deptAttendances->where('status', 'hadir')->count();
            $terlambatData[] = $deptAttendances->where('status', 'terlambat')->count();

            $deptStudents = Student::whereHas('class', fn($q) => $q->where('department_id', $dept->id))
                ->where('is_active', true)
                ->count();

            $workingDays = $this->getWorkingDays($startDate, $endDate);
            $expected = $deptStudents * $workingDays;

            $attendanceRates[] = $expected > 0
                ? round(($deptAttendances->whereIn('status', ['hadir', 'terlambat'])->count() / $expected) * 100, 1)
                : 0;
        }

        $this->chartData['comparison'] = [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Hadir', 'data' => $hadirData],
                ['label' => 'Terlambat', 'data' => $terlambatData],
            ],
        ];

        $this->chartData['attendanceRates'] = [
            'labels' => $labels,
            'data' => $attendanceRates,
        ];
    }

    private function loadDetailedData($startDate, $endDate)
    {
        $query = Student::where('is_active', true);

        if ($this->selectedClass) {
            $query->where('class_id', $this->selectedClass);
        } elseif ($this->selectedDepartment) {
            $query->whereHas('class', fn($q) => $q->where('department_id', $this->selectedDepartment));
        }

        // Top 10 most punctual students (highest attendance count)
        $topStudents = (clone $query)
            ->withCount(['attendances as hadir_count' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->where('status', 'hadir');
            }])
            ->orderBy('hadir_count', 'desc')
            ->limit(10)
            ->get();

        // Top 10 most late students
        $lateStudents = (clone $query)
            ->withCount(['attendances as late_count' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->where('status', 'terlambat');
            }])
            ->having('late_count', '>', 0)
            ->orderBy('late_count', 'desc')
            ->limit(10)
            ->get();

        // Students with most absences (alpha + bolos)
        $absentStudents = (clone $query)
            ->withCount(['attendances as absent_count' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->whereIn('status', ['alpha', 'bolos']);
            }])
            ->having('absent_count', '>', 0)
            ->orderBy('absent_count', 'desc')
            ->limit(10)
            ->get();

        // Students with perfect attendance (100% attendance rate)
        $perfectStudents = (clone $query)
            ->withCount(['attendances as total_attendance' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }])
            ->withCount(['attendances as hadir_only' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->where('status', 'hadir');
            }])
            ->get()
            ->filter(function($student) {
                return $student->total_attendance > 0 && $student->total_attendance == $student->hadir_only;
            })
            ->take(10);

        $this->statistics['top_students'] = $topStudents;
        $this->statistics['late_students'] = $lateStudents;
        $this->statistics['absent_students'] = $absentStudents;
        $this->statistics['perfect_students'] = $perfectStudents;
    }

    private function loadTimeAnalysisData($startDate, $endDate)
    {
        $query = Attendance::whereBetween('date', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ]);

        if ($this->selectedClass) {
            $query->where('class_id', $this->selectedClass);
        } elseif ($this->selectedDepartment) {
            $query->whereHas('student.class', function ($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
        }

        $attendances = $query->get();

        // Analyze check-in time patterns (group by hour)
        $checkInHours = [];
        $lateArrivalHours = [];

        foreach ($attendances as $attendance) {
            if ($attendance->check_in_time) {
                $hour = Carbon::parse($attendance->check_in_time)->format('H:00');

                if (!isset($checkInHours[$hour])) {
                    $checkInHours[$hour] = 0;
                }
                $checkInHours[$hour]++;

                if ($attendance->status === 'terlambat') {
                    if (!isset($lateArrivalHours[$hour])) {
                        $lateArrivalHours[$hour] = 0;
                    }
                    $lateArrivalHours[$hour]++;
                }
            }
        }

        // Sort by hour
        ksort($checkInHours);
        ksort($lateArrivalHours);

        // Day of week analysis
        $dayOfWeekData = [
            'Monday' => ['hadir' => 0, 'terlambat' => 0, 'absent' => 0],
            'Tuesday' => ['hadir' => 0, 'terlambat' => 0, 'absent' => 0],
            'Wednesday' => ['hadir' => 0, 'terlambat' => 0, 'absent' => 0],
            'Thursday' => ['hadir' => 0, 'terlambat' => 0, 'absent' => 0],
            'Friday' => ['hadir' => 0, 'terlambat' => 0, 'absent' => 0],
        ];

        foreach ($attendances as $attendance) {
            $dayName = Carbon::parse($attendance->date)->format('l');

            if (isset($dayOfWeekData[$dayName])) {
                if ($attendance->status === 'hadir') {
                    $dayOfWeekData[$dayName]['hadir']++;
                } elseif ($attendance->status === 'terlambat') {
                    $dayOfWeekData[$dayName]['terlambat']++;
                } elseif (in_array($attendance->status, ['alpha', 'bolos'])) {
                    $dayOfWeekData[$dayName]['absent']++;
                }
            }
        }

        // Late duration distribution
        $lateDurations = [
            '1-15 menit' => 0,
            '16-30 menit' => 0,
            '31-60 menit' => 0,
            '> 60 menit' => 0,
        ];

        foreach ($attendances->where('status', 'terlambat') as $attendance) {
            $minutes = $attendance->late_minutes ?? 0;

            if ($minutes <= 15) {
                $lateDurations['1-15 menit']++;
            } elseif ($minutes <= 30) {
                $lateDurations['16-30 menit']++;
            } elseif ($minutes <= 60) {
                $lateDurations['31-60 menit']++;
            } else {
                $lateDurations['> 60 menit']++;
            }
        }

        $this->chartData['checkInPattern'] = [
            'labels' => array_keys($checkInHours),
            'data' => array_values($checkInHours),
        ];

        $this->chartData['lateArrivalPattern'] = [
            'labels' => array_keys($lateArrivalHours),
            'data' => array_values($lateArrivalHours),
        ];

        $this->chartData['dayOfWeek'] = $dayOfWeekData;
        $this->chartData['lateDurations'] = $lateDurations;
    }

    private function loadInsightsData($startDate, $endDate)
    {
        $query = Attendance::whereBetween('date', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ]);

        if ($this->selectedClass) {
            $query->where('class_id', $this->selectedClass);
        } elseif ($this->selectedDepartment) {
            $query->whereHas('student.class', function ($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
        }

        $attendances = $query->get();
        $workingDays = $this->getWorkingDays($startDate, $endDate);

        // Calculate insights
        $this->insights = [];

        // 1. Attendance Trend (comparing first half vs second half)
        $midDate = $startDate->copy()->addDays((int)(($endDate->diffInDays($startDate)) / 2));

        $firstHalf = $attendances->where('date', '<=', $midDate->format('Y-m-d'));
        $secondHalf = $attendances->where('date', '>', $midDate->format('Y-m-d'));

        $firstHalfRate = $firstHalf->count() > 0
            ? round(($firstHalf->whereIn('status', ['hadir', 'terlambat'])->count() / $firstHalf->count()) * 100, 1)
            : 0;
        $secondHalfRate = $secondHalf->count() > 0
            ? round(($secondHalf->whereIn('status', ['hadir', 'terlambat'])->count() / $secondHalf->count()) * 100, 1)
            : 0;

        $trendChange = $secondHalfRate - $firstHalfRate;

        $this->insights['trend'] = [
            'direction' => $trendChange > 0 ? 'up' : ($trendChange < 0 ? 'down' : 'stable'),
            'change' => abs($trendChange),
            'first_half' => $firstHalfRate,
            'second_half' => $secondHalfRate,
        ];

        // 2. Most problematic day
        $dayProblems = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            if ($date->isWeekday()) {
                $dayAttendances = $attendances->where('date', $date->format('Y-m-d'));
                $problemCount = $dayAttendances->whereIn('status', ['alpha', 'bolos', 'terlambat'])->count();

                if ($problemCount > 0) {
                    $dayProblems[] = [
                        'date' => $date->format('Y-m-d'),
                        'day_name' => $date->format('l'),
                        'problem_count' => $problemCount,
                    ];
                }
            }
        }

        usort($dayProblems, function($a, $b) {
            return $b['problem_count'] - $a['problem_count'];
        });

        $this->insights['worst_days'] = array_slice($dayProblems, 0, 5);

        // 3. Classes that need attention (if not filtered by class)
        if (!$this->selectedClass) {
            $classesQuery = Classes::query();

            if ($this->selectedDepartment) {
                $classesQuery->where('department_id', $this->selectedDepartment);
            }

            $classes = $classesQuery->get();
            $classAttendanceRates = [];

            foreach ($classes as $class) {
                $classAttendances = Attendance::whereBetween('date', [
                        $startDate->format('Y-m-d'),
                        $endDate->format('Y-m-d')
                    ])
                    ->where('class_id', $class->id)
                    ->get();

                $classStudents = Student::where('class_id', $class->id)
                    ->where('is_active', true)
                    ->count();

                $expected = $classStudents * $workingDays;

                if ($expected > 0) {
                    $rate = round(($classAttendances->whereIn('status', ['hadir', 'terlambat'])->count() / $expected) * 100, 1);

                    $classAttendanceRates[] = [
                        'class_name' => $class->name,
                        'rate' => $rate,
                        'problem_count' => $classAttendances->whereIn('status', ['alpha', 'bolos'])->count(),
                    ];
                }
            }

            usort($classAttendanceRates, function($a, $b) {
                return $a['rate'] - $b['rate'];
            });

            $this->insights['attention_classes'] = array_slice($classAttendanceRates, 0, 5);
        }

        // 4. Peak late hours
        $peakLateHours = [];
        foreach ($attendances->where('status', 'terlambat') as $attendance) {
            if ($attendance->check_in_time) {
                $hour = Carbon::parse($attendance->check_in_time)->format('H:00');
                if (!isset($peakLateHours[$hour])) {
                    $peakLateHours[$hour] = 0;
                }
                $peakLateHours[$hour]++;
            }
        }

        arsort($peakLateHours);
        $this->insights['peak_late_hours'] = array_slice($peakLateHours, 0, 3, true);
    }

    private function getWorkingDays($startDate, $endDate)
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $workingDays = 0;

        // Get all holiday dates in this range
        $holidayDates = \App\Models\AcademicCalendar::getHolidayDates($startDate, $endDate);

        foreach ($period as $date) {
            // Count weekdays only and exclude holidays
            if ($date->isWeekday() && !in_array($date->format('Y-m-d'), $holidayDates)) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    public function exportExcel()
    {
        $fileName = 'Laporan_Analitik_' . $this->reportType . '_' . now()->format('YmdHis') . '.xlsx';

        return Excel::download(
            new AttendanceExport(
                $this->customStartDate,
                $this->customEndDate,
                $this->selectedDepartment,
                $this->selectedClass,
                '',
                '',
                ''
            ),
            $fileName
        );
    }

    public function exportPdf()
    {
        $startDate = Carbon::parse($this->customStartDate);
        $endDate = Carbon::parse($this->customEndDate);

        // Reload data to ensure we have fresh data
        $this->loadAnalytics();

        $pdf = Pdf::loadView('exports.analytics-pdf', [
            'reportType' => $this->reportType,
            'dateFrom' => $this->customStartDate,
            'dateTo' => $this->customEndDate,
            'statistics' => $this->statistics,
            'chartData' => $this->chartData,
            'insights' => $this->insights,
        ])->setPaper('a4', 'landscape');

        $fileName = 'Laporan_Analitik_' . $this->reportType . '_' . now()->format('YmdHis') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function render()
    {
        $departments = Department::orderBy('name')->get();
        $classes = Classes::when($this->selectedDepartment, function ($q) {
                return $q->where('department_id', $this->selectedDepartment);
            })
            ->orderBy('name')
            ->get();

        return view('livewire.admin.analytics.advanced-reports', [
            'departments' => $departments,
            'classes' => $classes,
        ]);
    }
}
