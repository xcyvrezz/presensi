<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceApiController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService)
    {
    }

    /**
     * Health Check
     */
    public function health(): JsonResponse
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
    public function verify(Request $request): JsonResponse
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
    public function status(Request $request): JsonResponse
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
            ->whereDate('date', $today)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Status absensi hari ini.',
            'data' => [
                'student_id' => $student->id,
                'name' => $student->full_name,
                'has_checked_in' => $attendance && $attendance->check_in_time ? true : false,
                'has_checked_out' => $attendance && $attendance->check_out_time ? true : false,
                'check_in_time' => $attendance && $attendance->check_in_time ? $attendance->check_in_time->format('H:i:s') : null,
                'check_out_time' => $attendance && $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : null,
                'status' => $attendance ? $attendance->status : 'belum_absen',
                'late_minutes' => $attendance ? $attendance->late_minutes : 0,
            ],
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Check-In
     */
    public function checkIn(Request $request): JsonResponse
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

        $result = $this->attendanceService->checkIn([
            'card_uid' => $request->card_uid,
            'method' => 'rfid_physical',
            'reader_id' => $request->reader_id,
            'location_id' => $request->location_id,
        ]);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
            'timestamp' => Carbon::now()->toIso8601String(),
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Check-Out
     */
    public function checkOut(Request $request): JsonResponse
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

        $result = $this->attendanceService->checkOut([
            'card_uid' => $request->card_uid,
            'method' => 'rfid_physical',
            'reader_id' => $request->reader_id,
            'location_id' => $request->location_id,
        ]);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
            'timestamp' => Carbon::now()->toIso8601String(),
        ], $result['success'] ? 200 : 400);
    }
}
