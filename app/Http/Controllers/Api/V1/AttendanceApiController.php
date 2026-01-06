<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceApiController extends Controller
{
    /**
     * Health Check
     */
    public function health()
    {
        return response()->json([
            'success' => true,
            'message' => 'API is running',
            'version' => '1.0.0',
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Verify Card UID
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 422);
        }

        $student = Student::where('card_uid', $request->card_uid)
            ->where('is_active', true)
            ->with(['class'])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu tidak terdaftar atau siswa tidak aktif.',
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kartu valid.',
            'data' => [
                'student_id' => $student->id,
                'name' => $student->full_name,
                'nis' => $student->nis,
                'class' => $student->class->name ?? '-',
                'photo' => $student->photo ? url('storage/' . $student->photo) : null,
            ],
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Get Today's Attendance Status
     */
    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 422);
        }

        $student = Student::where('card_uid', $request->card_uid)
            ->where('is_active', true)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan atau tidak aktif.',
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 404);
        }

        $today = Carbon::today();
        $attendance = Attendance::where('student_id', $student->id)
            ->whereDate('check_in_time', $today)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Status absensi hari ini.',
            'data' => [
                'student_id' => $student->id,
                'name' => $student->full_name,
                'has_checked_in' => $attendance ? true : false,
                'has_checked_out' => $attendance && $attendance->check_out_time ? true : false,
                'check_in_time' => $attendance && $attendance->check_in_time ? $attendance->check_in_time->format('H:i:s') : null,
                'check_out_time' => $attendance && $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : null,
                'status' => $attendance ? $attendance->status : null,
                'late_minutes' => $attendance ? $attendance->late_minutes : 0,
            ],
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Check-In
     */
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_uid' => 'required|string',
            'reader_id' => 'nullable|string',
            'location_id' => 'nullable|integer|exists:attendance_locations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 422);
        }

        // Find student
        $student = Student::where('card_uid', $request->card_uid)
            ->where('is_active', true)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan atau tidak aktif.',
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 400);
        }

        // Check if already checked in today
        $today = Carbon::today();
        $existingAttendance = Attendance::where('student_id', $student->id)
            ->whereDate('check_in_time', $today)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan check-in hari ini pada ' . $existingAttendance->check_in_time->format('H:i'),
                'data' => null,
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 400);
        }

        // Get time windows from settings
        $checkInStart = AttendanceSetting::getValue('check_in_start', '05:00');
        $checkInEnd = AttendanceSetting::getValue('check_in_end', '07:00');
        $lateThreshold = AttendanceSetting::getValue('late_threshold', '07:00');

        $now = Carbon::now();
        $checkInStartTime = Carbon::parse($checkInStart);
        $checkInEndTime = Carbon::parse($checkInEnd);
        $lateThresholdTime = Carbon::parse($lateThreshold);

        // Check if within check-in window
        if ($now->lt($checkInStartTime) || $now->gt($checkInEndTime)) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu check-in sudah ditutup. Hubungi wali kelas untuk absensi manual.',
                'data' => null,
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 400);
        }

        // Calculate status and late minutes
        $status = 'hadir';
        $lateMinutes = 0;
        $percentage = 100;

        if ($now->gt($lateThresholdTime)) {
            $status = 'terlambat';
            $lateMinutes = $now->diffInMinutes($lateThresholdTime);
            $percentage = 75;
        }

        // Get active semester
        $semester = Semester::where('is_active', true)->first();

        // Create attendance record
        $attendance = Attendance::create([
            'student_id' => $student->id,
            'class_id' => $student->class_id,
            'semester_id' => $semester ? $semester->id : null,
            'location_id' => $request->location_id,
            'check_in_time' => $now,
            'check_in_latitude' => null,
            'check_in_longitude' => null,
            'check_in_accuracy' => null,
            'check_in_device' => $request->reader_id ?? 'Physical Reader',
            'status' => $status,
            'late_minutes' => $lateMinutes,
            'percentage' => $percentage,
            'method' => 'rfid',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil! Selamat datang.',
            'data' => [
                'attendance_id' => $attendance->id,
                'check_in_time' => $attendance->check_in_time->format('H:i:s'),
                'status' => $attendance->status,
                'late_minutes' => $attendance->late_minutes,
                'location' => 'Physical Reader',
            ],
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Check-Out
     */
    public function checkOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_uid' => 'required|string',
            'reader_id' => 'nullable|string',
            'location_id' => 'nullable|integer|exists:attendance_locations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 422);
        }

        // Find student
        $student = Student::where('card_uid', $request->card_uid)
            ->where('is_active', true)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan atau tidak aktif.',
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 400);
        }

        // Find today's attendance
        $today = Carbon::today();
        $attendance = Attendance::where('student_id', $student->id)
            ->whereDate('check_in_time', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan check-in hari ini.',
                'data' => null,
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 400);
        }

        if ($attendance->check_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan check-out pada ' . $attendance->check_out_time->format('H:i'),
                'data' => null,
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 400);
        }

        // Get check-out time window
        $checkOutStart = AttendanceSetting::getValue('check_out_start', '14:00');
        $checkOutEnd = AttendanceSetting::getValue('check_out_end', '17:00');

        $now = Carbon::now();
        $checkOutStartTime = Carbon::parse($checkOutStart);
        $checkOutEndTime = Carbon::parse($checkOutEnd);

        // Check if within check-out window (optional, bisa di-disable)
        // if ($now->lt($checkOutStartTime) || $now->gt($checkOutEndTime)) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Waktu check-out belum dibuka atau sudah ditutup.',
        //         'data' => null,
        //         'timestamp' => Carbon::now()->toIso8601String(),
        //     ], 400);
        // }

        // Calculate early leave
        $earlyLeaveMinutes = 0;
        if ($now->lt($checkOutStartTime)) {
            $earlyLeaveMinutes = $now->diffInMinutes($checkOutStartTime);
            $attendance->status = 'pulang_cepat';
            $attendance->percentage = 75;
        }

        // Calculate total hours
        $totalHours = $attendance->check_in_time->diffInHours($now, false);

        // Update attendance
        $attendance->check_out_time = $now;
        $attendance->check_out_latitude = null;
        $attendance->check_out_longitude = null;
        $attendance->check_out_accuracy = null;
        $attendance->check_out_device = $request->reader_id ?? 'Physical Reader';
        $attendance->early_leave_minutes = $earlyLeaveMinutes;
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil. Terima kasih.',
            'data' => [
                'attendance_id' => $attendance->id,
                'check_out_time' => $attendance->check_out_time->format('H:i:s'),
                'total_hours' => round($totalHours, 1),
                'early_leave_minutes' => $attendance->early_leave_minutes,
                'status' => $attendance->status,
                'percentage' => $attendance->percentage,
            ],
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }
}
