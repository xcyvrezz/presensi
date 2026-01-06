<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Semester;
use App\Models\AttendanceSetting;
use App\Models\AcademicCalendar;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    protected GeofencingService $geofencingService;

    public function __construct(GeofencingService $geofencingService)
    {
        $this->geofencingService = $geofencingService;
    }

    /**
     * Process check-in
     *
     * @param array $data
     * @return array
     */
    public function checkIn(array $data): array
    {
        try {
            // Log the attempt for debugging
            Log::info('Check-in attempt', [
                'card_uid' => $data['card_uid'] ?? 'N/A',
                'method' => $data['method'] ?? 'N/A',
                'time' => now()->format('Y-m-d H:i:s')
            ]);

            // Test database connection first
            try {
                DB::connection()->getPdo();
                Log::info('✅ Database connection OK');
            } catch (\Exception $e) {
                Log::error('❌ Database connection failed: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => '❌ ERROR DATABASE: Koneksi database gagal. Periksa konfigurasi database.'
                ];
            }

            DB::beginTransaction();

            // Validate student with detailed error
            $student = $this->validateStudent($data);
            if (!$student) {
                $cardUid = $data['card_uid'] ?? $data['student_id'] ?? 'unknown';
                Log::warning("❌ Student not found: Card UID = {$cardUid}");

                // Check if card exists but inactive
                if (isset($data['card_uid'])) {
                    $inactiveStudent = Student::where('card_uid', $data['card_uid'])->first();
                    if ($inactiveStudent) {
                        return [
                            'success' => false,
                            'message' => "❌ KARTU TIDAK AKTIF: {$inactiveStudent->full_name} ({$inactiveStudent->nis}). Hubungi admin."
                        ];
                    }
                }

                return [
                    'success' => false,
                    'message' => "❌ KARTU TIDAK TERDAFTAR: UID {$cardUid}. Silakan registrasi kartu terlebih dahulu."
                ];
            }

            Log::info("✅ Student validated: {$student->full_name} ({$student->nis})");

            // Check if already checked in today
            $today = Carbon::today();
            try {
                $existing = Attendance::where('student_id', $student->id)
                    ->whereDate('date', $today)
                    ->first();

                if ($existing && $existing->check_in_time) {
                    Log::info("⚠️ Already checked in: {$student->full_name} at {$existing->check_in_time->format('H:i')}");
                    return [
                        'success' => false,
                        'type' => 'info',
                        'message' => "⚠️ SUDAH CHECK-IN: Anda sudah absen masuk pada {$existing->check_in_time->format('H:i')}",
                        'data' => [
                            'student' => [
                                'id' => $student->id,
                                'name' => $student->full_name,
                                'nis' => $student->nis,
                                'class' => $student->class->name ?? '-',
                                'photo' => $student->photo,
                            ],
                            'attendance' => [
                                'status' => $existing->status,
                                'check_in_time' => $existing->check_in_time->format('H:i'),
                                'check_out_time' => $existing->check_out_time ? $existing->check_out_time->format('H:i') : null,
                            ]
                        ]
                    ];
                }
            } catch (\Exception $e) {
                Log::error('❌ Error checking existing attendance: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => '❌ ERROR DATABASE: Gagal memeriksa data absensi. ' . $e->getMessage()
                ];
            }

            // Validate time window with detailed error
            try {
                $timeValidation = $this->validateCheckInTime();
                if (!$timeValidation['valid']) {
                    Log::warning("⏰ Time validation failed: {$timeValidation['message']}");
                    return [
                        'success' => false,
                        'type' => 'info',
                        'message' => $timeValidation['message'],
                        'data' => [
                            'student' => [
                                'id' => $student->id,
                                'name' => $student->full_name,
                                'nis' => $student->nis,
                                'class' => $student->class->name ?? '-',
                                'photo' => $student->photo,
                            ]
                        ]
                    ];
                }
                Log::info("✅ Time window valid");
            } catch (\Exception $e) {
                Log::error('❌ Error validating time: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => '❌ ERROR VALIDASI WAKTU: ' . $e->getMessage()
                ];
            }

            // Validate geofencing (for mobile NFC)
            $geofenceValid = true;
            $locationId = null;
            $distance = null;

            if (isset($data['method']) && $data['method'] === 'nfc_mobile') {
                $geofenceResult = $this->geofencingService->validateLocation(
                    $data['latitude'],
                    $data['longitude'],
                    'check_in'
                );

                if (!$geofenceResult['valid']) {
                    Log::warning("📍 Geofence validation failed: {$geofenceResult['message']}");
                    return ['success' => false, 'message' => "📍 {$geofenceResult['message']}"];
                }

                // Validate GPS accuracy
                $accuracyResult = $this->geofencingService->validateAccuracy($data['accuracy'] ?? 999);
                if (!$accuracyResult['valid']) {
                    Log::warning("📍 GPS accuracy failed: {$accuracyResult['message']}");
                    return ['success' => false, 'message' => "📍 {$accuracyResult['message']}"];
                }

                $locationId = $geofenceResult['location_id'];
                $distance = $geofenceResult['distance'];
            }

            // Get active semester with detailed error
            try {
                $semester = Semester::active()->first();
                if (!$semester) {
                    Log::error('❌ No active semester found');
                    return [
                        'success' => false,
                        'message' => '❌ SEMESTER TIDAK AKTIF: Tidak ada semester yang aktif. Hubungi admin untuk mengaktifkan semester.'
                    ];
                }
                Log::info("✅ Active semester: {$semester->name}");
            } catch (\Exception $e) {
                Log::error('❌ Error getting semester: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => '❌ ERROR DATABASE SEMESTER: ' . $e->getMessage()
                ];
            }

            // Calculate status and late minutes
            $checkInTime = now();
            try {
                $lateMinutes = $this->calculateLateMinutes($checkInTime);
                $status = $lateMinutes > 0 ? 'terlambat' : 'hadir';
                Log::info("✅ Status calculated: {$status}, Late: {$lateMinutes} minutes");
            } catch (\Exception $e) {
                Log::error('❌ Error calculating late minutes: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => '❌ ERROR KALKULASI: Gagal menghitung keterlambatan. ' . $e->getMessage()
                ];
            }

            // Create or update attendance
            try {
                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'date' => $today,
                    ],
                    [
                        'class_id' => $student->class_id,
                        'semester_id' => $semester->id,
                        'check_in_time' => $checkInTime->format('H:i:s'),
                        'check_in_method' => $data['method'] ?? 'rfid_physical',
                        'check_in_location_id' => $locationId,
                        'check_in_latitude' => $data['latitude'] ?? null,
                        'check_in_longitude' => $data['longitude'] ?? null,
                        'check_in_distance' => $distance,
                        'check_in_photo' => $data['photo'] ?? null,
                        'status' => $status,
                        'late_minutes' => $lateMinutes,
                    ]
                );
                Log::info("✅ Attendance saved: ID {$attendance->id}");
            } catch (\Exception $e) {
                Log::error('❌ Error saving attendance: ' . $e->getMessage());
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => '❌ ERROR SAVE DATABASE: Gagal menyimpan absensi. ' . $e->getMessage()
                ];
            }

            DB::commit();
            Log::info("✅✅✅ Check-in SUCCESS for {$student->full_name}");

            return [
                'success' => true,
                'message' => $status === 'hadir'
                    ? '✅ Check-in berhasil! Selamat datang.'
                    : "✅ Check-in berhasil. Anda terlambat {$lateMinutes} menit.",
                'data' => [
                    'student' => [
                        'id' => $student->id,
                        'card_uid' => $student->card_uid,
                        'name' => $student->full_name,
                        'nis' => $student->nis,
                    ],
                    'attendance_id' => $attendance->id,
                    'check_in_time' => $checkInTime->format('H:i:s'),
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'location' => isset($geofenceResult) ? ($geofenceResult['location_name'] ?? 'Physical Reader') : 'Physical Reader',
                ],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌❌❌ CRITICAL Check-in error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => '❌ KESALAHAN SISTEM: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')'
            ];
        }
    }

    /**
     * Process check-out
     *
     * @param array $data
     * @return array
     */
    public function checkOut(array $data): array
    {
        try {
            // Log the attempt for debugging
            Log::info('Check-out attempt', [
                'card_uid' => $data['card_uid'] ?? 'N/A',
                'method' => $data['method'] ?? 'N/A',
                'time' => now()->format('Y-m-d H:i:s')
            ]);

            // Test database connection first
            try {
                DB::connection()->getPdo();
                Log::info('✅ Database connection OK');
            } catch (\Exception $e) {
                Log::error('❌ Database connection failed: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => '❌ ERROR DATABASE: Koneksi database gagal. Periksa konfigurasi database.'
                ];
            }

            DB::beginTransaction();

            // Validate student with detailed error
            $student = $this->validateStudent($data);
            if (!$student) {
                $cardUid = $data['card_uid'] ?? $data['student_id'] ?? 'unknown';
                Log::warning("❌ Student not found for check-out: Card UID = {$cardUid}");
                return [
                    'success' => false,
                    'message' => "❌ KARTU TIDAK TERDAFTAR: UID {$cardUid}"
                ];
            }

            Log::info("✅ Student validated: {$student->full_name} ({$student->nis})");

            // Validate check-out time window
            try {
                $timeValidation = $this->validateCheckOutTime();
                if (!$timeValidation['valid']) {
                    Log::warning("⏰ Check-out time validation failed: {$timeValidation['message']}");
                    return [
                        'success' => false,
                        'type' => 'info',
                        'message' => $timeValidation['message'],
                        'data' => [
                            'student' => [
                                'id' => $student->id,
                                'name' => $student->full_name,
                                'nis' => $student->nis,
                                'class' => $student->class->name ?? '-',
                                'photo' => $student->photo,
                            ]
                        ]
                    ];
                }
                Log::info("✅ Check-out time window valid");
            } catch (\Exception $e) {
                Log::error('❌ Error validating check-out time: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => '❌ ERROR VALIDASI WAKTU: ' . $e->getMessage()
                ];
            }

            // Check if checked in today
            $today = Carbon::today();
            try {
                $attendance = Attendance::where('student_id', $student->id)
                    ->whereDate('date', $today)
                    ->first();

                if (!$attendance || !$attendance->check_in_time) {
                    Log::warning("⚠️ No check-in record for {$student->full_name}");
                    return [
                        'success' => false,
                        'message' => '⚠️ BELUM CHECK-IN: Anda belum melakukan check-in hari ini.'
                    ];
                }

                if ($attendance->check_out_time) {
                    Log::info("⚠️ Already checked out: {$student->full_name} at {$attendance->check_out_time->format('H:i')}");
                    return [
                        'success' => false,
                        'message' => "⚠️ SUDAH CHECK-OUT: Anda sudah absen pulang pada {$attendance->check_out_time->format('H:i')}",
                    ];
                }
            } catch (\Exception $e) {
                Log::error('❌ Error checking attendance record: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => '❌ ERROR DATABASE: Gagal memeriksa data absensi. ' . $e->getMessage()
                ];
            }

            // Validate geofencing (for mobile NFC)
            $locationId = null;
            $distance = null;

            if (isset($data['method']) && $data['method'] === 'nfc_mobile') {
                $geofenceResult = $this->geofencingService->validateLocation(
                    $data['latitude'],
                    $data['longitude'],
                    'check_out'
                );

                if (!$geofenceResult['valid']) {
                    Log::warning("📍 Geofence validation failed: {$geofenceResult['message']}");
                    return ['success' => false, 'message' => "📍 {$geofenceResult['message']}"];
                }

                $accuracyResult = $this->geofencingService->validateAccuracy($data['accuracy'] ?? 999);
                if (!$accuracyResult['valid']) {
                    Log::warning("📍 GPS accuracy failed: {$accuracyResult['message']}");
                    return ['success' => false, 'message' => "📍 {$accuracyResult['message']}"];
                }

                $locationId = $geofenceResult['location_id'];
                $distance = $geofenceResult['distance'];
            }

            // Calculate early leave
            $checkOutTime = now();
            try {
                $earlyLeaveMinutes = $this->calculateEarlyLeaveMinutes($checkOutTime);
                Log::info("✅ Early leave calculated: {$earlyLeaveMinutes} minutes");
            } catch (\Exception $e) {
                Log::error('❌ Error calculating early leave: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => '❌ ERROR KALKULASI: Gagal menghitung waktu pulang cepat. ' . $e->getMessage()
                ];
            }

            // Update status if leaving early
            if ($earlyLeaveMinutes > 0) {
                $attendance->status = $attendance->status === 'terlambat' ? 'terlambat' : 'pulang_cepat';
            }

            // Update attendance
            try {
                $attendance->update([
                    'check_out_time' => $checkOutTime->format('H:i:s'),
                    'check_out_method' => $data['method'] ?? 'rfid_physical',
                    'check_out_location_id' => $locationId,
                    'check_out_latitude' => $data['latitude'] ?? null,
                    'check_out_longitude' => $data['longitude'] ?? null,
                    'check_out_distance' => $distance,
                    'check_out_photo' => $data['photo'] ?? null,
                    'early_leave_minutes' => $earlyLeaveMinutes,
                    'percentage' => $this->calculatePercentage($attendance),
                ]);
                Log::info("✅ Attendance updated: ID {$attendance->id}");
            } catch (\Exception $e) {
                Log::error('❌ Error updating attendance: ' . $e->getMessage());
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => '❌ ERROR SAVE DATABASE: Gagal menyimpan check-out. ' . $e->getMessage()
                ];
            }

            DB::commit();

            // Calculate total hours
            try {
                // Ensure date is string format
                $dateStr = $attendance->date instanceof \Carbon\Carbon
                    ? $attendance->date->format('Y-m-d')
                    : $attendance->date;

                $checkInDateTime = Carbon::parse($dateStr . ' ' . $attendance->check_in_time);
                $totalHours = $checkInDateTime->diffInHours($checkOutTime);
            } catch (\Exception $e) {
                Log::warning('Error calculating total hours: ' . $e->getMessage());
                $totalHours = 0; // Fallback
            }

            Log::info("✅✅✅ Check-out SUCCESS for {$student->full_name}");

            return [
                'success' => true,
                'message' => $earlyLeaveMinutes > 0
                    ? "✅ Check-out berhasil. Anda pulang {$earlyLeaveMinutes} menit lebih awal."
                    : '✅ Check-out berhasil. Terima kasih.',
                'data' => [
                    'student' => [
                        'id' => $student->id,
                        'card_uid' => $student->card_uid,
                        'name' => $student->full_name,
                        'nis' => $student->nis,
                    ],
                    'attendance_id' => $attendance->id,
                    'check_out_time' => $checkOutTime->format('H:i:s'),
                    'total_hours' => $totalHours,
                    'early_leave_minutes' => $earlyLeaveMinutes,
                    'status' => $attendance->status,
                    'percentage' => $attendance->percentage,
                ],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌❌❌ CRITICAL Check-out error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => '❌ KESALAHAN SISTEM: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')'
            ];
        }
    }

    /**
     * Validate student from card UID or student ID
     */
    protected function validateStudent(array $data): ?Student
    {
        if (isset($data['card_uid'])) {
            return Student::where('card_uid', $data['card_uid'])
                ->where('is_active', true)
                ->first();
        }

        if (isset($data['student_id'])) {
            return Student::where('id', $data['student_id'])
                ->where('is_active', true)
                ->first();
        }

        return null;
    }

    /**
     * Validate check-in time window
     */
    protected function validateCheckInTime(): array
    {
        try {
            Log::info('Validating check-in time window...');

            // Check if today is a holiday from academic calendar
            $todayEvent = $this->getTodayEvent();
            if ($todayEvent && $todayEvent->is_holiday) {
                return [
                    'valid' => false,
                    'message' => "⛔ HARI LIBUR: {$todayEvent->title}. Tidak ada absensi hari ini."
                ];
            }

            $now = now();
            Log::info("Current time: {$now->format('H:i:s')}");

            // Get attendance settings with validation
            try {
                // Use custom time from event if available
                if ($todayEvent && $todayEvent->use_custom_times) {
                    $checkInStart = $todayEvent->custom_check_in_start ?? AttendanceSetting::getValue('check_in_start', '05:00:00');
                    $checkInEnd = $todayEvent->custom_check_in_end ?? AttendanceSetting::getValue('check_in_end', '07:00:00');
                } else {
                    $checkInStart = AttendanceSetting::getValue('check_in_start', '05:00:00');
                    $checkInEnd = AttendanceSetting::getValue('check_in_end', '07:00:00');
                }

                // Ensure we have string values, not Carbon objects
                if ($checkInStart instanceof \Carbon\Carbon) {
                    $checkInStart = $checkInStart->format('H:i:s');
                }
                if ($checkInEnd instanceof \Carbon\Carbon) {
                    $checkInEnd = $checkInEnd->format('H:i:s');
                }

                Log::info("Check-in window: {$checkInStart} - {$checkInEnd}");

                if (!$checkInStart || !$checkInEnd) {
                    Log::error('❌ Attendance settings not configured');
                    return [
                        'valid' => false,
                        'message' => '❌ PENGATURAN BELUM DIATUR: Waktu absensi belum dikonfigurasi. Hubungi admin.'
                    ];
                }
            } catch (\Exception $e) {
                Log::error('❌ Error getting attendance settings: ' . $e->getMessage());
                return [
                    'valid' => false,
                    'message' => '❌ ERROR PENGATURAN: Gagal mengambil pengaturan absensi. ' . $e->getMessage()
                ];
            }

            // Parse time with validation
            try {
                $start = Carbon::parse($checkInStart);
                $end = Carbon::parse($checkInEnd); // No grace period - strict end time
            } catch (\Exception $e) {
                Log::error('❌ Error parsing time settings: ' . $e->getMessage());
                return [
                    'valid' => false,
                    'message' => '❌ FORMAT WAKTU SALAH: Pengaturan waktu tidak valid. Hubungi admin.'
                ];
            }

            // Validate time window - strict check
            if ($now->lt($start)) {
                $message = "⏰ CHECK-IN BELUM DIBUKA: Waktu check-in dimulai pukul {$checkInStart}. Sekarang: {$now->format('H:i')}";
                Log::warning($message);
                return ['valid' => false, 'message' => $message];
            }

            if ($now->gt($end)) {
                $message = "⏰ CHECK-IN SUDAH DITUTUP: Batas waktu check-in berakhir pukul {$checkInEnd}. Sekarang: {$now->format('H:i')}. Hubungi wali kelas untuk absensi manual.";
                Log::warning($message);
                return ['valid' => false, 'message' => $message];
            }

            Log::info('✅ Time window valid');
            return ['valid' => true, 'message' => 'Time window valid'];

        } catch (\Exception $e) {
            // If error, fallback to default settings
            Log::error('❌ CRITICAL error in validateCheckInTime: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            try {
                $now = now();
                $checkInStart = '05:00:00'; // Hard-coded fallback
                $checkInEnd = '07:00:00';   // Hard-coded fallback

                Log::warning('Using hard-coded fallback times');

                $start = Carbon::parse($checkInStart);
                $end = Carbon::parse($checkInEnd); // No grace period for fallback too

                if ($now->lt($start)) {
                    return ['valid' => false, 'message' => "⏰ Check-in belum dibuka. Mulai: {$checkInStart}"];
                }

                if ($now->gt($end)) {
                    return ['valid' => false, 'message' => "⏰ Waktu check-in sudah ditutup (berakhir pukul {$checkInEnd}). Hubungi wali kelas."];
                }

                return ['valid' => true, 'message' => 'Time window valid (fallback)'];
            } catch (\Exception $fallbackError) {
                Log::error('❌ Even fallback failed: ' . $fallbackError->getMessage());
                return [
                    'valid' => false,
                    'message' => '❌ KESALAHAN KRITIS: Tidak dapat memvalidasi waktu. ' . $e->getMessage()
                ];
            }
        }
    }

    /**
     * Validate check-out time window
     */
    protected function validateCheckOutTime(): array
    {
        try {
            Log::info('Validating check-out time window...');

            // Check if today is a holiday from academic calendar
            $todayEvent = $this->getTodayEvent();
            if ($todayEvent && $todayEvent->is_holiday) {
                return [
                    'valid' => false,
                    'message' => "⛔ HARI LIBUR: {$todayEvent->title}. Tidak ada absensi hari ini."
                ];
            }

            $now = now();
            Log::info("Current time: {$now->format('H:i:s')}");

            // Get attendance settings with validation
            try {
                // Use custom time from event if available
                if ($todayEvent && $todayEvent->use_custom_times) {
                    $checkOutStart = $todayEvent->custom_check_out_start ?? AttendanceSetting::getValue('check_out_start', '14:00:00');
                    $checkOutEnd = $todayEvent->custom_check_out_end ?? AttendanceSetting::getValue('check_out_end', '15:00:00');
                } else {
                    $checkOutStart = AttendanceSetting::getValue('check_out_start', '14:00:00');
                    $checkOutEnd = AttendanceSetting::getValue('check_out_end', '15:00:00');
                }

                // Ensure we have string values, not Carbon objects
                if ($checkOutStart instanceof \Carbon\Carbon) {
                    $checkOutStart = $checkOutStart->format('H:i:s');
                }
                if ($checkOutEnd instanceof \Carbon\Carbon) {
                    $checkOutEnd = $checkOutEnd->format('H:i:s');
                }

                Log::info("Check-out window: {$checkOutStart} - {$checkOutEnd}");

                if (!$checkOutStart || !$checkOutEnd) {
                    Log::error('❌ Attendance settings not configured');
                    return [
                        'valid' => false,
                        'message' => '❌ PENGATURAN BELUM DIATUR: Waktu check-out belum dikonfigurasi. Hubungi admin.'
                    ];
                }
            } catch (\Exception $e) {
                Log::error('❌ Error getting attendance settings: ' . $e->getMessage());
                return [
                    'valid' => false,
                    'message' => '❌ ERROR PENGATURAN: Gagal mengambil pengaturan absensi. ' . $e->getMessage()
                ];
            }

            // Parse time with validation
            try {
                $start = Carbon::parse($checkOutStart);
                $end = Carbon::parse($checkOutEnd); // No grace period - strict end time
            } catch (\Exception $e) {
                Log::error('❌ Error parsing time settings: ' . $e->getMessage());
                return [
                    'valid' => false,
                    'message' => '❌ FORMAT WAKTU SALAH: Pengaturan waktu tidak valid. Hubungi admin.'
                ];
            }

            // Validate time window - strict check
            if ($now->lt($start)) {
                $message = "⏰ CHECK-OUT BELUM DIBUKA: Waktu check-out dimulai pukul {$checkOutStart}. Sekarang: {$now->format('H:i')}";
                Log::warning($message);
                return ['valid' => false, 'message' => $message];
            }

            if ($now->gt($end)) {
                $message = "⏰ CHECK-OUT SUDAH DITUTUP: Batas waktu check-out berakhir pukul {$checkOutEnd}. Sekarang: {$now->format('H:i')}. Hubungi wali kelas untuk absensi manual.";
                Log::warning($message);
                return ['valid' => false, 'message' => $message];
            }

            Log::info('✅ Time window valid');
            return ['valid' => true, 'message' => 'Time window valid'];

        } catch (\Exception $e) {
            // If error, fallback to default settings
            Log::error('❌ CRITICAL error in validateCheckOutTime: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            try {
                $now = now();
                $checkOutStart = '14:00:00'; // Hard-coded fallback
                $checkOutEnd = '15:00:00';   // Hard-coded fallback

                Log::warning('Using hard-coded fallback times');

                $start = Carbon::parse($checkOutStart);
                $end = Carbon::parse($checkOutEnd); // No grace period for fallback too

                if ($now->lt($start)) {
                    return ['valid' => false, 'message' => "⏰ Check-out belum dibuka. Mulai: {$checkOutStart}"];
                }

                if ($now->gt($end)) {
                    return ['valid' => false, 'message' => "⏰ Waktu check-out sudah ditutup (berakhir pukul {$checkOutEnd}). Hubungi wali kelas."];
                }

                return ['valid' => true, 'message' => 'Time window valid (fallback)'];
            } catch (\Exception $fallbackError) {
                Log::error('❌ Even fallback failed: ' . $fallbackError->getMessage());
                return [
                    'valid' => false,
                    'message' => '❌ KESALAHAN KRITIS: Tidak dapat memvalidasi waktu. ' . $e->getMessage()
                ];
            }
        }
    }

    /**
     * Calculate late minutes
     */
    protected function calculateLateMinutes(Carbon $checkInTime): int
    {
        try {
            Log::info('=== CALCULATE LATE MINUTES START ===');
            Log::info('Check-in time: ' . $checkInTime->format('Y-m-d H:i:s'));

            // Use custom time from event if available
            $todayEvent = $this->getTodayEvent();
            if ($todayEvent && $todayEvent->use_custom_times && $todayEvent->custom_check_in_normal) {
                $lateThreshold = $todayEvent->custom_check_in_normal;
                Log::info('Using custom late threshold from event: ' . $lateThreshold);
            } else {
                $lateThreshold = AttendanceSetting::getValue('late_threshold', '07:15:00');
                Log::info('Using late threshold from settings: ' . $lateThreshold);
            }

            // Ensure string format
            if ($lateThreshold instanceof \Carbon\Carbon) {
                $lateThreshold = $lateThreshold->format('H:i:s');
            }

            // PERBAIKAN: Parse threshold dengan tanggal yang sama dengan check-in time
            $thresholdString = $checkInTime->format('Y-m-d') . ' ' . $lateThreshold;
            Log::info('Threshold string: ' . $thresholdString);

            $threshold = Carbon::createFromFormat('Y-m-d H:i:s', $thresholdString);
            Log::info('Threshold parsed: ' . $threshold->format('Y-m-d H:i:s'));

            if ($checkInTime->gt($threshold)) {
                $lateMinutes = $threshold->diffInMinutes($checkInTime);
                Log::info('CHECK-IN TERLAMBAT! Late minutes: ' . $lateMinutes);
                Log::info('=== CALCULATE LATE MINUTES END (TERLAMBAT) ===');
                return $lateMinutes;
            }

            Log::info('CHECK-IN TEPAT WAKTU! Late minutes: 0');
            Log::info('=== CALCULATE LATE MINUTES END (TEPAT WAKTU) ===');
            return 0;
        } catch (\Exception $e) {
            // Fallback to default
            Log::error('❌ Error in calculateLateMinutes: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            try {
                $lateThreshold = '07:15:00'; // Hard-coded fallback
                $threshold = Carbon::createFromFormat('Y-m-d H:i:s', $checkInTime->format('Y-m-d') . ' ' . $lateThreshold);

                Log::warning('Using fallback late threshold: ' . $lateThreshold);

                if ($checkInTime->gt($threshold)) {
                    $lateMinutes = $threshold->diffInMinutes($checkInTime);
                    Log::warning('FALLBACK: Check-in terlambat! Late minutes: ' . $lateMinutes);
                    return $lateMinutes;
                }

                Log::warning('FALLBACK: Check-in tepat waktu! Late minutes: 0');
                return 0;
            } catch (\Exception $fallbackError) {
                Log::error('❌ Even fallback failed in calculateLateMinutes: ' . $fallbackError->getMessage());
                return 0;
            }
        }
    }

    /**
     * Calculate early leave minutes
     */
    protected function calculateEarlyLeaveMinutes(Carbon $checkOutTime): int
    {
        try {
            // Use custom time from event if available
            $todayEvent = $this->getTodayEvent();
            if ($todayEvent && $todayEvent->use_custom_times && $todayEvent->custom_check_out_normal) {
                $checkOutNormal = $todayEvent->custom_check_out_normal;
            } else {
                // Use check_out_normal setting (waktu normal pulang) instead of check_out_start
                $checkOutNormal = AttendanceSetting::getValue('check_out_normal', '15:30:00');
            }

            // Ensure string format
            if ($checkOutNormal instanceof \Carbon\Carbon) {
                $checkOutNormal = $checkOutNormal->format('H:i:s');
            }

            // Parse threshold dengan tanggal yang sama dengan check-out time
            $threshold = Carbon::createFromFormat('Y-m-d H:i:s', $checkOutTime->format('Y-m-d') . ' ' . $checkOutNormal);

            // If check-out before normal time, calculate early leave minutes
            if ($checkOutTime->lt($threshold)) {
                return $threshold->diffInMinutes($checkOutTime);
            }

            return 0;
        } catch (\Exception $e) {
            // Fallback to default
            Log::warning('Error in calculateEarlyLeaveMinutes, using default: ' . $e->getMessage());

            try {
                $checkOutNormal = '15:30:00'; // Hard-coded fallback for normal check-out time
                $threshold = Carbon::createFromFormat('Y-m-d H:i:s', $checkOutTime->format('Y-m-d') . ' ' . $checkOutNormal);

                if ($checkOutTime->lt($threshold)) {
                    return $threshold->diffInMinutes($checkOutTime);
                }

                return 0;
            } catch (\Exception $fallbackError) {
                Log::error('Even fallback failed in calculateEarlyLeaveMinutes: ' . $fallbackError->getMessage());
                return 0;
            }
        }
    }

    /**
     * Get today's academic calendar event (if any)
     */
    protected function getTodayEvent(): ?AcademicCalendar
    {
        try {
            $today = Carbon::today();

            return AcademicCalendar::where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->first();
        } catch (\Exception $e) {
            // Log error but don't break the attendance process
            Log::warning('Failed to get today event from academic calendar: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get today's event info for display
     */
    public function getTodayEventInfo(): ?array
    {
        try {
            $event = $this->getTodayEvent();

            if (!$event) {
                return null;
            }

            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'type' => $event->type,
                'type_label' => $event->type_label,
                'is_holiday' => $event->is_holiday,
                'use_custom_times' => $event->use_custom_times,
                'color' => $event->color,
                'custom_times' => $event->use_custom_times ? [
                    'check_in_start' => $event->custom_check_in_start,
                    'check_in_end' => $event->custom_check_in_end,
                    'check_in_normal' => $event->custom_check_in_normal,
                    'check_out_start' => $event->custom_check_out_start,
                    'check_out_end' => $event->custom_check_out_end,
                    'check_out_normal' => $event->custom_check_out_normal,
                ] : null,
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get today event info: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process card tap (centralized method for all tapping)
     * Handles UID normalization and determines check-in or check-out
     *
     * @param string $cardUid
     * @param string $method (rfid_physical, nfc_mobile, etc)
     * @return array
     */
    public function processTap(string $cardUid, string $method = 'rfid'): array
    {
        try {
            Log::info('Processing card tap', [
                'original_uid' => $cardUid,
                'method' => $method,
            ]);

            // Normalize card UID to multiple possible formats
            $possibleFormats = $this->normalizeCardUid($cardUid);

            Log::info('Card UID formats to try:', [
                'original' => $cardUid,
                'formats' => $possibleFormats
            ]);

            // Find student by trying all possible formats
            $student = Student::where('is_active', true)
                ->where(function($query) use ($possibleFormats) {
                    foreach ($possibleFormats as $format) {
                        $query->orWhere('card_uid', $format);
                    }
                })
                ->with(['class.department'])
                ->first();

            if (!$student) {
                Log::warning('Card not found in database', [
                    'tried_formats' => $possibleFormats
                ]);
                return [
                    'success' => false,
                    'message' => '❌ KARTU TIDAK TERDAFTAR: Silakan registrasi kartu terlebih dahulu atau hubungi admin.',
                    'type' => 'error'
                ];
            }

            Log::info('Student found', [
                'student_id' => $student->id,
                'name' => $student->full_name,
                'matched_uid' => $student->card_uid
            ]);

            // Check if already checked in today
            $today = Carbon::today();
            $attendance = Attendance::where('student_id', $student->id)
                ->whereDate('date', $today)
                ->first();

            // Determine action: check-in or check-out
            if (!$attendance || !$attendance->check_in_time) {
                // Perform check-in
                return $this->checkIn([
                    'card_uid' => $student->card_uid,
                    'method' => $method,
                ]);
            } elseif (!$attendance->check_out_time) {
                // Perform check-out
                return $this->checkOut([
                    'card_uid' => $student->card_uid,
                    'method' => $method,
                ]);
            } else {
                // Already completed
                return [
                    'success' => false,
                    'message' => '⚠️ SUDAH LENGKAP: Anda sudah check-in dan check-out hari ini.',
                    'type' => 'info',
                    'data' => [
                        'student' => [
                            'name' => $student->full_name,
                            'nis' => $student->nis,
                            'class' => $student->class->name ?? '-',
                        ],
                        'attendance' => [
                            'check_in_time' => $attendance->check_in_time->format('H:i'),
                            'check_out_time' => $attendance->check_out_time->format('H:i'),
                            'status' => $attendance->status,
                        ]
                    ]
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error processing card tap: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '❌ KESALAHAN SISTEM: ' . $e->getMessage(),
                'type' => 'error'
            ];
        }
    }

    /**
     * Normalize card UID to multiple possible formats
     * Support: Decimal, Hex, Doubled digits, with/without separators
     *
     * @param string $cardUid
     * @return array
     */
    protected function normalizeCardUid(string $cardUid): array
    {
        $formats = [];

        // Original format (as-is, uppercase)
        $formats[] = strtoupper(trim($cardUid));

        // Remove all separators (colons, dashes, spaces)
        $cleanUid = preg_replace('/[:\-\s]/', '', $cardUid);
        $formats[] = strtoupper($cleanUid);

        // Check if it's decimal number (all digits)
        if (ctype_digit($cleanUid)) {

            // Check for "doubled digits" pattern (e.g., 33115511 -> 3151)
            // This happens when USB readers send each digit twice
            if (strlen($cleanUid) % 2 === 0 && strlen($cleanUid) >= 10) {
                $doubled = true;
                $chars = str_split($cleanUid);

                // Check if every pair is the same digit
                for ($i = 0; $i < strlen($cleanUid); $i += 2) {
                    if ($chars[$i] !== $chars[$i + 1]) {
                        $doubled = false;
                        break;
                    }
                }

                // If doubled, un-double it
                if ($doubled) {
                    $undoubled = '';
                    for ($i = 0; $i < strlen($cleanUid); $i += 2) {
                        $undoubled .= $chars[$i];
                    }
                    $formats[] = $undoubled;
                    Log::info('Detected doubled-digit UID', [
                        'original' => $cleanUid,
                        'undoubled' => $undoubled
                    ]);
                }
            }

            // Try hex conversion (only for reasonable size numbers)
            if (strlen($cleanUid) <= 15) {
                try {
                    $hexUid = strtoupper(dechex((int)$cleanUid));
                    $formats[] = $hexUid;

                    // Add hex with colons (every 2 chars)
                    if (strlen($hexUid) >= 4) {
                        $formats[] = implode(':', str_split($hexUid, 2));
                    }

                    // Add hex with dashes
                    if (strlen($hexUid) >= 4) {
                        $formats[] = implode('-', str_split($hexUid, 2));
                    }
                } catch (\Exception $e) {
                    // Skip if number too large
                }
            }
        } else {
            // Already hex, add variations
            $formats[] = strtoupper($cleanUid);

            // Add with colons
            if (strlen($cleanUid) >= 4) {
                $formats[] = strtoupper(implode(':', str_split($cleanUid, 2)));
            }

            // Add with dashes
            if (strlen($cleanUid) >= 4) {
                $formats[] = strtoupper(implode('-', str_split($cleanUid, 2)));
            }
        }

        return array_unique($formats);
    }

    /**
     * Calculate attendance percentage (weighted)
     */
    protected function calculatePercentage(Attendance $attendance): float
    {
        // Weighted percentage based on status
        $percentages = [
            'hadir' => 100,
            'terlambat' => 75,
            'izin' => 50,
            'sakit' => 50,
            'dispensasi' => 75,
            'pulang_cepat' => 75,
            'alpha' => 0,
            'bolos' => 0,
        ];

        return $percentages[$attendance->status] ?? 0;
    }
}
