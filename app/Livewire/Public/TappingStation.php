<?php

namespace App\Livewire\Public;

use App\Services\AttendanceService;
use App\Models\Attendance;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class TappingStation extends Component
{
    protected AttendanceService $attendanceService;

    // Current state
    public $currentTime;
    public $currentDate;
    public $isProcessing = false;

    // Student info
    public $studentData = null;
    public $attendanceStatus = null;

    // Message state
    public $message = '';
    public $messageType = ''; // success, error, info

    // Settings (from event or default)
    public $checkInStart;
    public $checkInEnd;
    public $checkOutStart;
    public $checkOutEnd;
    public $lateThreshold;

    // Today's event info
    public $todayEvent = null;

    // Stats
    public $todayStats = [
        'total_present' => 0,
        'on_time' => 0,
        'late' => 0,
    ];

    protected $listeners = ['cardDetected'];

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount()
    {
        $this->loadTodayEvent();
        $this->loadTodayStats();
        $this->updateTime();
    }

    public function loadTodayEvent()
    {
        try {
            $eventInfo = $this->attendanceService->getTodayEventInfo();
            $this->todayEvent = $eventInfo;

            // Load settings from event or default
            if ($eventInfo && $eventInfo['use_custom_times']) {
                $this->checkInStart = $eventInfo['custom_times']['check_in_start'] ?? '06:00:00';
                $this->checkInEnd = $eventInfo['custom_times']['check_in_end'] ?? '08:00:00';
                $this->checkOutStart = $eventInfo['custom_times']['check_out_start'] ?? '14:00:00';
                $this->checkOutEnd = $eventInfo['custom_times']['check_out_end'] ?? '17:00:00';
                $this->lateThreshold = $eventInfo['custom_times']['check_in_normal'] ?? '07:00:00';
            } else {
                $this->checkInStart = \App\Models\AttendanceSetting::getValue('check_in_start', '06:00:00');
                $this->checkInEnd = \App\Models\AttendanceSetting::getValue('check_in_end', '08:00:00');
                $this->checkOutStart = \App\Models\AttendanceSetting::getValue('check_out_start', '14:00:00');
                $this->checkOutEnd = \App\Models\AttendanceSetting::getValue('check_out_end', '17:00:00');
                $this->lateThreshold = \App\Models\AttendanceSetting::getValue('late_threshold', '07:15:00');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to load today event in TappingStation: ' . $e->getMessage());
            $this->todayEvent = null;
        }
    }

    public function loadTodayStats()
    {
        $today = Carbon::today();
        $this->todayStats['total_present'] = Attendance::whereDate('date', $today)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->count();

        $this->todayStats['on_time'] = Attendance::whereDate('date', $today)
            ->where('status', 'hadir')
            ->count();

        $this->todayStats['late'] = Attendance::whereDate('date', $today)
            ->where('status', 'terlambat')
            ->count();
    }

    public function updateTime()
    {
        $this->currentTime = Carbon::now()->format('H:i:s');
        $this->currentDate = Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY');
    }

    /**
     * Process card tap - now uses centralized AttendanceService
     */
    public function processCard($cardUid)
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;
        $this->reset(['studentData', 'attendanceStatus', 'message', 'messageType']);

        try {
            // Use centralized tap processing from AttendanceService
            $result = $this->attendanceService->processTap($cardUid, 'rfid_physical');

            if ($result['success']) {
                // Extract data from successful response
                $data = $result['data'];
                $studentInfo = $data['student'];

                // Get full student info with relations
                $student = \App\Models\Student::where('id', $studentInfo['id'])
                    ->with('class.department')
                    ->first();

                if ($student) {
                    $this->studentData = [
                        'name' => $student->full_name,
                        'nis' => $student->nis,
                        'class' => $student->class->name ?? '-',
                        'department' => $student->class->department->code ?? '-',
                        'photo' => $student->photo ? asset('storage/' . $student->photo) : null,
                    ];

                    // Set attendance status based on action
                    if (isset($data['check_in_time'])) {
                        // Check-in
                        $this->attendanceStatus = [
                            'action' => 'check-in',
                            'status' => $data['status'],
                            'time' => $data['check_in_time'],
                            'late_minutes' => $data['late_minutes'] ?? 0,
                            'percentage' => 100,
                        ];
                    } elseif (isset($data['check_out_time'])) {
                        // Check-out
                        $today = Carbon::today();
                        $attendance = Attendance::where('student_id', $student->id)
                            ->whereDate('date', $today)
                            ->first();

                        $this->attendanceStatus = [
                            'action' => 'check-out',
                            'status' => $data['status'],
                            'check_in_time' => $attendance ? $attendance->check_in_time->format('H:i') : '-',
                            'check_out_time' => $data['check_out_time'],
                        ];
                    }
                }

                $this->message = $result['message'];
                $this->messageType = 'success';

                // Play different sound for late check-in
                if (isset($data['status']) && $data['status'] === 'terlambat') {
                    $this->dispatch('play-sound', sound: 'warning');
                } else {
                    $this->dispatch('play-sound', sound: 'success');
                }

            } else {
                // Handle error/info responses
                $this->message = $result['message'];
                $this->messageType = $result['type'] ?? 'error';

                // Try to get student data even on error if available
                if (isset($result['data']['student'])) {
                    $studentData = $result['data']['student'];

                    // Get full student info
                    $student = \App\Models\Student::where('id', $studentData['id'] ?? null)
                        ->with('class.department')
                        ->first();

                    if ($student) {
                        $this->studentData = [
                            'name' => $student->full_name,
                            'nis' => $student->nis,
                            'class' => $student->class->name ?? '-',
                            'department' => $student->class->department->code ?? '-',
                            'photo' => $student->photo ? asset('storage/' . $student->photo) : null,
                        ];
                    } else {
                        $this->studentData = [
                            'name' => $studentData['name'],
                            'nis' => $studentData['nis'],
                            'class' => $studentData['class'],
                            'department' => '-',
                            'photo' => null,
                        ];
                    }

                    if (isset($result['data']['attendance'])) {
                        $attendanceData = $result['data']['attendance'];
                        $this->attendanceStatus = [
                            'action' => 'completed',
                            'status' => $attendanceData['status'],
                            'check_in_time' => $attendanceData['check_in_time'],
                            'check_out_time' => $attendanceData['check_out_time'],
                        ];
                    }
                }

                $soundType = $this->messageType === 'info' ? 'info' : 'error';
                $this->dispatch('play-sound', sound: $soundType);
            }

            // Reload stats
            $this->loadTodayStats();

        } catch (\Exception $e) {
            Log::error('Tapping station error: ' . $e->getMessage());
            $this->message = '❌ Terjadi kesalahan sistem';
            $this->messageType = 'error';
            $this->dispatch('play-sound', sound: 'error');
        } finally {
            $this->isProcessing = false;
            $this->clearAfterDelay();
        }
    }

    public function clearAfterDelay()
    {
        $this->dispatch('schedule-clear');
    }

    public function clearDisplay()
    {
        $this->reset(['studentData', 'attendanceStatus', 'message', 'messageType']);
    }

    public function render()
    {
        return view('livewire.public.tapping-station')
            ->layout('layouts.tapping');
    }
}
