<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'nullable|integer|exists:students,id',
            'class_id' => 'nullable|integer|exists:classes,id',
            'status' => 'nullable|string',
            'method' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Attendance::with(['student.class.department', 'checkInLocation', 'checkOutLocation'])
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc');

        if (!empty($validated['student_id'])) {
            $query->where('student_id', $validated['student_id']);
        }

        if (!empty($validated['class_id'])) {
            $query->where('class_id', $validated['class_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['method'])) {
            $query->where('check_in_method', Attendance::normalizeMethod($validated['method']));
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('date', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('date', '<=', $validated['date_to']);
        }

        $attendances = $query->paginate($validated['per_page'] ?? 20);

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil diambil.',
            'data' => $attendances,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Process check-in from physical RFID reader
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
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
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
                'timestamp' => now()->toIso8601String(),
            ], $result['success'] ? 200 : 400);
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
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
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
                'timestamp' => now()->toIso8601String(),
            ], $result['success'] ? 200 : 400);
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
            $student = Student::where('card_uid', $request->card_uid)
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
                    'name' => $student->full_name,
                    'nis' => $student->nis,
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
            $student = Student::where('card_uid', $request->card_uid)
                ->where('is_active', true)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kartu tidak terdaftar.',
                    'timestamp' => now()->toIso8601String(),
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
                    'nis' => $student->nis,
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

    public function statistics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'nullable|integer|exists:students,id',
            'class_id' => 'nullable|integer|exists:classes,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $dateFrom = Carbon::parse($validated['date_from'] ?? now()->toDateString())->startOfDay();
        $dateTo = Carbon::parse($validated['date_to'] ?? now()->toDateString())->endOfDay();

        $query = Attendance::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()]);

        if (!empty($validated['student_id'])) {
            $query->where('student_id', $validated['student_id']);
        }

        if (!empty($validated['class_id'])) {
            $query->where('class_id', $validated['class_id']);
        }

        $attendances = $query->get();
        $presentCount = $attendances->whereIn('status', ['hadir', 'terlambat', 'dispensasi'])->count();
        $forgotCheckoutCount = $attendances->filter(function ($attendance) {
            return $attendance->check_in_time
                && !$attendance->check_out_time
                && in_array($attendance->status, ['hadir', 'terlambat'], true);
        })->count();

        return response()->json([
            'success' => true,
            'message' => 'Statistik absensi berhasil diambil.',
            'data' => [
                'period' => [
                    'date_from' => $dateFrom->toDateString(),
                    'date_to' => $dateTo->toDateString(),
                ],
                'total_records' => $attendances->count(),
                'present' => $presentCount,
                'hadir' => $attendances->where('status', 'hadir')->count(),
                'terlambat' => $attendances->where('status', 'terlambat')->count(),
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'dispensasi' => $attendances->where('status', 'dispensasi')->count(),
                'alpha' => $attendances->where('status', 'alpha')->count(),
                'bolos' => $attendances->where('status', 'bolos')->count(),
                'pulang_cepat' => $attendances->where('status', 'pulang_cepat')->count(),
                'lupa_checkout' => $forgotCheckoutCount,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
