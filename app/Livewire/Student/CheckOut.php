<?php

namespace App\Livewire\Student;

use App\Services\AttendanceService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Check-Out - Absensi MIFARE')]
class CheckOut extends Component
{
    public ?string $message = null;
    public bool $isSuccess = false;
    public ?array $attendanceData = null;
    public bool $processing = false;

    // NFC & GPS Data
    public ?string $cardUid = null;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?float $accuracy = null;

    protected AttendanceService $attendanceService;

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Process check-out (called from JavaScript after NFC scan + GPS capture)
     */
    public function processCheckOut()
    {
        $this->processing = true;
        $this->reset(['message', 'isSuccess', 'attendanceData']);

        try {
            // Validate required data
            $this->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'accuracy' => 'nullable|numeric',
            ], [
                'latitude.required' => 'Data lokasi GPS tidak valid. Pastikan GPS aktif.',
                'longitude.required' => 'Data lokasi GPS tidak valid. Pastikan GPS aktif.',
            ]);

            // Prepare data for attendance service
            $data = [
                'student_id' => auth()->user()->student->id ?? null,
                'card_uid' => $this->cardUid, // From NFC scan
                'method' => 'nfc_mobile',
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'accuracy' => $this->accuracy ?? 999,
            ];

            // Call attendance service
            $result = $this->attendanceService->checkOut($data);

            $this->isSuccess = $result['success'];
            $this->message = $result['message'];

            if ($result['success']) {
                $this->attendanceData = $result['data'];

                // Dispatch success event to JavaScript
                $this->dispatch('checkOutSuccess', data: $result['data']);
            } else {
                $this->dispatch('checkOutFailed', message: $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isSuccess = false;
            $this->message = $e->validator->errors()->first();
            $this->dispatch('checkOutFailed', message: $this->message);
        } catch (\Exception $e) {
            $this->isSuccess = false;
            $this->message = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            $this->dispatch('checkOutFailed', message: $this->message);
        } finally {
            $this->processing = false;
        }
    }

    /**
     * Set GPS coordinates (called from JavaScript)
     */
    public function setGpsData($latitude, $longitude, $accuracy = null)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->accuracy = $accuracy;
    }

    /**
     * Set NFC card UID (called from JavaScript)
     */
    public function setCardUid($uid)
    {
        $this->cardUid = $uid;
    }

    /**
     * Reset form
     */
    public function resetForm()
    {
        $this->reset();
    }

    public function render()
    {
        return view('livewire.student.check-out');
    }
}
