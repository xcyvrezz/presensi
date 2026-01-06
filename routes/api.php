<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\RfidReaderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes (for RFID reader hardware)
Route::prefix('v1')->group(function () {

    // Health check for RFID reader
    Route::get('/health', [AttendanceApiController::class, 'health']);

    // Attendance routes for physical RFID reader
    Route::prefix('attendance')->group(function () {
        // Verify card before check-in/out
        Route::post('/verify', [AttendanceApiController::class, 'verify']);

        // Get today's attendance status
        Route::post('/status', [AttendanceApiController::class, 'status']);

        // Check-in endpoint
        Route::post('/check-in', [AttendanceApiController::class, 'checkIn']);

        // Check-out endpoint
        Route::post('/check-out', [AttendanceApiController::class, 'checkOut']);
    });
});

// RFID Reader routes (for admin polling and hardware reporting)
Route::prefix('rfid')->group(function () {
    // Get last detected card (polled by admin interface)
    Route::get('/last-card', [RfidReaderController::class, 'getLastCard']);

    // Report card detection from physical RFID reader hardware
    Route::post('/report-card', [RfidReaderController::class, 'reportCard']);

    // Clear last card cache
    Route::post('/clear-card', [RfidReaderController::class, 'clearLastCard']);
});

// Protected API routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// ========== New API v2 Routes ==========

// API Documentation
Route::prefix('v2')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'name' => 'Absensi MIFARE API v2',
            'version' => '2.0.0',
            'description' => 'RESTful API for attendance management system',
            'endpoints' => [
                'auth' => [
                    'POST /api/v2/auth/token' => 'Generate API token',
                    'POST /api/v2/auth/revoke' => 'Revoke API token',
                    'GET /api/v2/auth/tokens' => 'List user tokens',
                    'GET /api/v2/auth/verify' => 'Verify token',
                ],
                'students' => [
                    'GET /api/v2/students' => 'Get all students',
                    'GET /api/v2/students/{id}' => 'Get student by ID',
                    'POST /api/v2/students/nfc' => 'Get student by NFC UID',
                ],
                'attendance' => [
                    'GET /api/v2/attendance' => 'Get attendance records',
                    'POST /api/v2/attendance/check-in' => 'Record check-in',
                    'POST /api/v2/attendance/check-out' => 'Record check-out',
                    'GET /api/v2/attendance/statistics' => 'Get attendance statistics',
                ],
            ],
        ]);
    });

    // Authentication endpoints (no auth required)
    Route::prefix('auth')->group(function () {
        Route::post('/token', [AuthController::class, 'generateToken']);
    });

    // Protected API endpoints
    Route::middleware('api.auth')->group(function () {
        // Auth management
        Route::prefix('auth')->group(function () {
            Route::post('/revoke', [AuthController::class, 'revokeToken']);
            Route::get('/tokens', [AuthController::class, 'listTokens']);
            Route::get('/verify', [AuthController::class, 'verifyToken']);
        });

        // Students
        Route::prefix('students')->group(function () {
            Route::get('/', [StudentController::class, 'index']);
            Route::get('/{id}', [StudentController::class, 'show']);
            Route::post('/nfc', [StudentController::class, 'getByNfc']);
        });

        // Attendance
        Route::prefix('attendance')->group(function () {
            Route::get('/', [AttendanceController::class, 'index']);
            Route::post('/check-in', [AttendanceController::class, 'checkIn']);
            Route::post('/check-out', [AttendanceController::class, 'checkOut']);
            Route::get('/statistics', [AttendanceController::class, 'statistics']);
        });
    });
});
