<?php

namespace App\Livewire\Student;

use App\Models\Student;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Layout('layouts.student')]
#[Title('NFC Absensi')]
class NfcCheckInOut extends Component
{
    public $student;
    public $todayAttendance;
    public $canCheckIn = false;
    public $canCheckOut = false;
    public $isProcessing = false;
    public $nfcSupported = false;
    public $statusMessage = '';
    public $statusType = ''; // success, error, warning, info

    protected $attendanceService;

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount()
    {
        $this->student = Student::where('user_id', auth()->id())
            ->with(['class.department'])
            ->first();

        if (!$this->student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        if (!$this->student->card_uid) {
            $this->statusMessage = '⚠️ Kartu NFC Anda belum terdaftar. Hubungi admin untuk registrasi kartu.';
            $this->statusType = 'warning';
        }

        $this->loadTodayAttendance();
    }

    public function loadTodayAttendance()
    {
        $this->todayAttendance = Attendance::where('student_id', $this->student->id)
            ->whereDate('date', Carbon::today())
            ->first();

        // Get today's event info for custom times
        try {
            $eventInfo = $this->attendanceService->getTodayEventInfo();

            if ($eventInfo && $eventInfo['is_holiday']) {
                $this->canCheckIn = false;
                $this->canCheckOut = false;
                $this->statusMessage = "🌙 Hari Libur: {$eventInfo['title']}. Tidak ada absensi.";
                $this->statusType = 'info';
                return;
            }
        } catch (\Exception $e) {
            // Ignore error, use default behavior
        }

        // Determine if student can check in or check out
        if (!$this->todayAttendance || !$this->todayAttendance->check_in_time) {
            $this->canCheckIn = true;
            $this->canCheckOut = false;
        } elseif (!$this->todayAttendance->check_out_time) {
            $this->canCheckIn = false;
            $this->canCheckOut = true;
        } else {
            $this->canCheckIn = false;
            $this->canCheckOut = false;
            $this->statusMessage = '✅ Anda sudah absen lengkap hari ini';
            $this->statusType = 'success';
        }
    }

    public function processNfcCheckIn($cardUid, $latitude, $longitude, $accuracy)
    {
        if (!$this->canCheckIn || $this->isProcessing) {
            $this->dispatch('nfc-processing-done');
            return;
        }

        $this->isProcessing = true;
        $this->dispatch('nfc-processing-start');

        try {
            $result = $this->attendanceService->checkIn([
                'student_id' => $this->student->id,
                'method' => 'nfc_mobile',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $accuracy,
            ]);

            if ($result['success']) {
                $this->statusMessage = $result['message'];
                $this->statusType = 'success';
                $this->loadTodayAttendance();
            } else {
                $this->statusMessage = $result['message'];
                $this->statusType = 'error';
            }
        } catch (\Exception $e) {
            $this->statusMessage = '❌ Terjadi kesalahan: ' . $e->getMessage();
            $this->statusType = 'error';
            \Log::error('NFC Check-in error: ' . $e->getMessage());
        }

        $this->isProcessing = false;
        $this->dispatch('nfc-processing-done');
    }

    public function processNfcCheckOut($cardUid, $latitude, $longitude, $accuracy)
    {
        if (!$this->canCheckOut || $this->isProcessing || !$this->todayAttendance) {
            $this->dispatch('nfc-processing-done');
            return;
        }

        $this->isProcessing = true;
        $this->dispatch('nfc-processing-start');

        try {
            $result = $this->attendanceService->checkOut([
                'student_id' => $this->student->id,
                'method' => 'nfc_mobile',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $accuracy,
            ]);

            if ($result['success']) {
                $this->statusMessage = $result['message'];
                $this->statusType = 'success';
                $this->loadTodayAttendance();
            } else {
                $this->statusMessage = $result['message'];
                $this->statusType = 'error';
            }
        } catch (\Exception $e) {
            $this->statusMessage = '❌ Terjadi kesalahan: ' . $e->getMessage();
            $this->statusType = 'error';
            \Log::error('NFC Check-out error: ' . $e->getMessage());
        }

        $this->isProcessing = false;
        $this->dispatch('nfc-processing-done');
    }

    public function render()
    {
        return view('livewire.student.nfc-check-in-out');
    }
}
