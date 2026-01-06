<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Process check-in from physical RFID reader
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkIn(Request $request): JsonResponse
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'card_uid' => 'required|string',
            'reader_id' => 'nullable|string',
            'location_id' => 'nullable|integer|exists:attendance_locations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Prepare data for service
            $data = [
                'card_uid' => $request->card_uid,
                'method' => 'rfid_physical',
                'reader_id' => $request->reader_id,
                'location_id' => $request->location_id,
            ];

            // Process check-in
            $result = $this->attendanceService->checkIn($data);

            $statusCode = $result['success'] ? 200 : 400;

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
                'timestamp' => now()->toIso8601String(),
            ], $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Process check-out from physical RFID reader
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkOut(Request $request): JsonResponse
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'card_uid' => 'required|string',
            'reader_id' => 'nullable|string',
            'location_id' => 'nullable|integer|exists:attendance_locations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Prepare data for service
            $data = [
                'card_uid' => $request->card_uid,
                'method' => 'rfid_physical',
                'reader_id' => $request->reader_id,
                'location_id' => $request->location_id,
            ];

            // Process check-out
            $result = $this->attendanceService->checkOut($data);

            $statusCode = $result['success'] ? 200 : 400;

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
                'timestamp' => now()->toIso8601String(),
            ], $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Get student info by card UID (for verification before check-in)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyCard(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'card_uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Card UID diperlukan.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $student = \App\Models\Student::where('card_uid', $request->card_uid)
                ->where('is_active', true)
                ->with(['class', 'user'])
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kartu tidak terdaftar atau siswa tidak aktif.',
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kartu valid.',
                'data' => [
                    'student_id' => $student->id,
                    'name' => $student->user->name ?? $student->nisn,
                    'nisn' => $student->nisn,
                    'class' => $student->class->name ?? null,
                    'photo' => $student->photo_url ?? null,
                ],
                'timestamp' => now()->toIso8601String(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Get attendance status for today (for RFID reader display)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTodayStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'card_uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Card UID diperlukan.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $student = \App\Models\Student::where('card_uid', $request->card_uid)
                ->where('is_active', true)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kartu tidak terdaftar.',
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            $today = \Carbon\Carbon::today();
            $attendance = \App\Models\Attendance::where('student_id', $student->id)
                ->whereDate('date', $today)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Status absensi hari ini.',
                'data' => [
                    'student_id' => $student->id,
                    'name' => $student->user->name ?? $student->nisn,
                    'has_checked_in' => $attendance && $attendance->check_in_time ? true : false,
                    'has_checked_out' => $attendance && $attendance->check_out_time ? true : false,
                    'check_in_time' => $attendance && $attendance->check_in_time ? $attendance->check_in_time->format('H:i:s') : null,
                    'check_out_time' => $attendance && $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : null,
                    'status' => $attendance->status ?? 'belum_absen',
                    'late_minutes' => $attendance->late_minutes ?? 0,
                ],
                'timestamp' => now()->toIso8601String(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }
}
