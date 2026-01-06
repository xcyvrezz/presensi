<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Semester;
use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceRulesService
{
    /**
     * Business Rules untuk Status Absensi
     *
     * 1. HADIR: Check-in tepat waktu (sebelum late_threshold)
     * 2. TERLAMBAT: Check-in setelah late_threshold
     * 3. ALPHA: Tidak ada record absensi sama sekali di akhir hari
     * 4. IZIN: Input manual dengan approval (sakit keluarga, urusan pribadi, dll)
     * 5. SAKIT: Input manual dengan surat sakit
     * 6. DISPENSASI: Izin resmi sekolah (lomba, tugas sekolah, dll)
     * 7. BOLOS: Tidak hadir tanpa keterangan (sama seperti alpha tapi dengan flag khusus)
     * 8. PULANG_CEPAT: Check-out sebelum check_out_start
     * 9. LUPA_CHECK_OUT: Ada check-in tapi tidak ada check-out sampai batas waktu
     */

    /**
     * Auto-mark siswa yang tidak absen sebagai ALPHA
     * Dijalankan setiap hari jam 23:59 via cronjob
     */
    public function autoMarkAlpha(Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $results = [
            'total_students' => 0,
            'marked_alpha' => 0,
            'already_present' => 0,
            'has_permission' => 0,
        ];

        try {
            DB::beginTransaction();

            // Get active semester
            $semester = Semester::active()->first();
            if (!$semester) {
                Log::warning('No active semester for auto-mark alpha');
                return $results;
            }

            // Get all active students
            $students = Student::where('is_active', true)->get();
            $results['total_students'] = $students->count();

            foreach ($students as $student) {
                // Check if already has attendance record
                $attendance = Attendance::where('student_id', $student->id)
                    ->whereDate('date', $date)
                    ->first();

                if ($attendance) {
                    // Already has record (hadir, terlambat, izin, sakit, dispensasi, dll)
                    if (in_array($attendance->status, ['izin', 'sakit', 'dispensasi'])) {
                        $results['has_permission']++;
                    } else {
                        $results['already_present']++;
                    }
                    continue;
                }

                // Create ALPHA record
                Attendance::create([
                    'student_id' => $student->id,
                    'class_id' => $student->class_id,
                    'semester_id' => $semester->id,
                    'date' => $date,
                    'status' => 'alpha',
                    'late_minutes' => 0,
                    'early_leave_minutes' => 0,
                    'percentage' => 0,
                    'notes' => 'Auto-marked as alpha - no attendance record',
                ]);

                $results['marked_alpha']++;
            }

            DB::commit();

            Log::info('Auto-mark alpha completed', $results);
            return $results;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto-mark alpha failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Auto-mark siswa yang lupa check-out
     * Dijalankan setiap hari jam 20:00 via cronjob
     */
    public function autoMarkForgotCheckOut(Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $results = [
            'total_checked' => 0,
            'marked_forgot' => 0,
            'already_complete' => 0,
        ];

        try {
            DB::beginTransaction();

            // Get all attendances today that has check-in but no check-out
            $attendances = Attendance::whereDate('date', $date)
                ->whereNotNull('check_in_time')
                ->whereNull('check_out_time')
                ->whereIn('status', ['hadir', 'terlambat']) // Only for present students
                ->get();

            $results['total_checked'] = $attendances->count();

            $checkOutEndTime = AttendanceSetting::getValue('check_out_end', '17:00:00');
            $currentTime = now();

            // Only mark if already past check-out end time
            if ($currentTime->format('H:i:s') < $checkOutEndTime) {
                Log::info('Too early to mark forgot check-out');
                DB::commit();
                return $results;
            }

            foreach ($attendances as $attendance) {
                // Add note about forgot check-out
                $attendance->update([
                    'notes' => ($attendance->notes ? $attendance->notes . '. ' : '') . 'Lupa check-out - auto-marked at ' . $currentTime->format('H:i'),
                ]);

                $results['marked_forgot']++;
            }

            DB::commit();

            Log::info('Auto-mark forgot check-out completed', $results);
            return $results;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto-mark forgot check-out failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create manual attendance (izin, sakit, dispensasi)
     *
     * @param array $data [student_id, date, status, reason, proof_file, approved_by]
     * @return array
     */
    public function createManualAttendance(array $data): array
    {
        try {
            DB::beginTransaction();

            // Validate student
            $student = Student::find($data['student_id']);
            if (!$student) {
                return ['success' => false, 'message' => 'Siswa tidak ditemukan'];
            }

            // Get active semester
            $semester = Semester::active()->first();
            if (!$semester) {
                return ['success' => false, 'message' => 'Tidak ada semester aktif'];
            }

            $date = Carbon::parse($data['date']);

            // Check if date is a holiday
            if (\App\Models\AcademicCalendar::isHoliday($date)) {
                return [
                    'success' => false,
                    'message' => '⛔ HARI LIBUR: Tidak dapat menginput absensi manual pada hari libur. Silakan pilih tanggal lain.'
                ];
            }

            // Check if already has attendance
            $existing = Attendance::where('student_id', $student->id)
                ->whereDate('date', $date)
                ->first();

            if ($existing && !in_array($existing->status, ['alpha'])) {
                return [
                    'success' => false,
                    'message' => "Siswa sudah memiliki record absensi dengan status: {$existing->status}"
                ];
            }

            // Determine percentage based on status
            $percentage = match($data['status']) {
                'izin' => 50,
                'sakit' => 50,
                'dispensasi' => 75,
                default => 0,
            };

            // Create or update attendance
            $attendance = Attendance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'date' => $date,
                ],
                [
                    'class_id' => $student->class_id,
                    'semester_id' => $semester->id,
                    'status' => $data['status'],
                    'late_minutes' => 0,
                    'early_leave_minutes' => 0,
                    'percentage' => $percentage,
                    'notes' => $data['reason'] ?? null,
                    'approved_by' => $data['approved_by'] ?? null,
                    'approved_at' => now(),
                    'approval_status' => 'approved',
                    'check_in_method' => 'manual',
                ]
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Berhasil input {$data['status']} untuk {$student->full_name}",
                'data' => $attendance
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create manual attendance failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mark student as BOLOS (ditching/skipping)
     * Biasanya digunakan oleh wali kelas jika ada informasi siswa bolos
     *
     * Updated: Mengizinkan mark bolos untuk siswa yang sudah hadir/terlambat
     * Alasan: Siswa bisa saja absen pagi tapi bolos siang hari
     */
    public function markAsBolos(int $studentId, Carbon $date, string $reason = null, int $markedBy = null): array
    {
        try {
            DB::beginTransaction();

            $student = Student::find($studentId);
            if (!$student) {
                return ['success' => false, 'message' => 'Siswa tidak ditemukan'];
            }

            $semester = Semester::active()->first();
            if (!$semester) {
                return ['success' => false, 'message' => 'Tidak ada semester aktif'];
            }

            // Check if date is a holiday
            if (\App\Models\AcademicCalendar::isHoliday($date)) {
                return [
                    'success' => false,
                    'message' => '⛔ HARI LIBUR: Tidak dapat mark bolos pada hari libur. Silakan pilih tanggal lain.'
                ];
            }

            // Check existing attendance
            $existing = Attendance::where('student_id', $studentId)
                ->whereDate('date', $date)
                ->first();

            // Prevent overwriting status dengan persetujuan resmi (izin, sakit, dispensasi)
            if ($existing && in_array($existing->status, ['izin', 'sakit', 'dispensasi'])) {
                return [
                    'success' => false,
                    'message' => "Tidak bisa mark bolos. Siswa sudah memiliki {$existing->status} dengan persetujuan resmi."
                ];
            }

            // Jika sudah ada record hadir/terlambat, simpan informasi lama di notes
            $additionalNotes = '';
            if ($existing && in_array($existing->status, ['hadir', 'terlambat'])) {
                $oldStatus = $existing->status === 'hadir' ? 'HADIR' : 'TERLAMBAT';
                $checkInTime = $existing->check_in_time ? Carbon::parse($existing->check_in_time)->format('H:i') : '-';
                $additionalNotes = " [Status lama: {$oldStatus} pada {$checkInTime}, diubah menjadi BOLOS karena: {$reason}]";
            }

            // Create or update as BOLOS
            $attendance = Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'date' => $date,
                ],
                [
                    'class_id' => $student->class_id,
                    'semester_id' => $semester->id,
                    'status' => 'bolos',
                    'late_minutes' => 0,
                    'early_leave_minutes' => 0,
                    'percentage' => 0,
                    'notes' => ($reason ?? 'Ditandai bolos oleh wali kelas') . $additionalNotes,
                    'approved_by' => $markedBy,
                    'approved_at' => now(),
                    // Reset check-in/out times karena statusnya berubah ke bolos
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'check_in_method' => 'manual',
                ]
            );

            DB::commit();

            $message = "Berhasil mark {$student->full_name} sebagai BOLOS";
            if ($additionalNotes) {
                $message .= " (status sebelumnya telah ditimpa)";
            }

            return [
                'success' => true,
                'message' => $message,
                'data' => $attendance
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark as bolos failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get attendance statistics summary
     */
    public function getAttendanceStats(Carbon $startDate, Carbon $endDate, int $studentId = null): array
    {
        $query = Attendance::whereBetween('date', [$startDate, $endDate]);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $attendances = $query->get();

        return [
            'total' => $attendances->count(),
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'dispensasi' => $attendances->where('status', 'dispensasi')->count(),
            'bolos' => $attendances->where('status', 'bolos')->count(),
            'pulang_cepat' => $attendances->where('status', 'pulang_cepat')->count(),
            'lupa_checkout' => $attendances->whereNotNull('check_in_time')
                ->whereNull('check_out_time')
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count(),
            'attendance_rate' => $attendances->count() > 0
                ? round(($attendances->whereIn('status', ['hadir', 'terlambat', 'dispensasi'])->count() / $attendances->count()) * 100, 2)
                : 0,
        ];
    }

    /**
     * Check if date is a holiday from academic calendar
     */
    public function isHoliday(Carbon $date): bool
    {
        // Check academic calendar (akan diintegrasikan nanti)
        return false;
    }
}
