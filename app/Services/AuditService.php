<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log an activity
     */
    public static function log(
        string $action,
        string $description,
        string $category = 'system',
        string $severity = 'info',
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?array $properties = null,
        ?array $requestData = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'method' => Request::method(),
            'url' => Request::fullUrl(),
            'properties' => $properties,
            'request_data' => $requestData ? self::filterSensitiveData($requestData) : null,
            'category' => $category,
            'severity' => $severity,
            'session_id' => session()->getId(),
        ]);
    }

    /**
     * Log authentication events
     */
    public static function logLogin($user, bool $success = true)
    {
        return self::log(
            action: 'login',
            description: $success
                ? "User {$user->name} berhasil login"
                : "Login gagal untuk user {$user->email}",
            category: 'authentication',
            severity: $success ? 'info' : 'warning',
            properties: [
                'user_email' => $user->email,
                'user_role' => $user->role->name ?? 'unknown',
                'success' => $success,
            ]
        );
    }

    public static function logLogout($user)
    {
        return self::log(
            action: 'logout',
            description: "User {$user->name} logout",
            category: 'authentication',
            severity: 'info',
            properties: [
                'user_email' => $user->email,
                'session_duration' => session()->get('login_time')
                    ? now()->diffInMinutes(session()->get('login_time'))
                    : null,
            ]
        );
    }

    /**
     * Log CRUD operations
     */
    public static function logCreate($model, string $description = null)
    {
        $modelName = class_basename($model);

        return self::log(
            action: 'create',
            description: $description ?? "Membuat {$modelName} baru: {$model->name ?? $model->id}",
            category: self::getCategoryFromModel($modelName),
            severity: 'info',
            subjectType: $modelName,
            subjectId: $model->id,
            properties: [
                'model' => $modelName,
                'attributes' => $model->getAttributes(),
            ]
        );
    }

    public static function logUpdate($model, array $changes, string $description = null)
    {
        $modelName = class_basename($model);

        return self::log(
            action: 'update',
            description: $description ?? "Mengupdate {$modelName}: {$model->name ?? $model->id}",
            category: self::getCategoryFromModel($modelName),
            severity: 'info',
            subjectType: $modelName,
            subjectId: $model->id,
            properties: [
                'model' => $modelName,
                'old' => $changes['old'] ?? [],
                'new' => $changes['new'] ?? [],
            ]
        );
    }

    public static function logDelete($model, string $description = null)
    {
        $modelName = class_basename($model);

        return self::log(
            action: 'delete',
            description: $description ?? "Menghapus {$modelName}: {$model->name ?? $model->id}",
            category: self::getCategoryFromModel($modelName),
            severity: 'warning',
            subjectType: $modelName,
            subjectId: $model->id,
            properties: [
                'model' => $modelName,
                'deleted_data' => $model->getAttributes(),
            ]
        );
    }

    /**
     * Log approval actions
     */
    public static function logApproval($request, bool $approved, ?string $reason = null)
    {
        return self::log(
            action: $approved ? 'approve' : 'reject',
            description: $approved
                ? "Menyetujui pengajuan izin dari {$request->student->full_name}"
                : "Menolak pengajuan izin dari {$request->student->full_name}",
            category: 'approval',
            severity: 'info',
            subjectType: 'AbsenceRequest',
            subjectId: $request->id,
            properties: [
                'student_name' => $request->student->full_name,
                'absence_date' => $request->absence_date->format('Y-m-d'),
                'type' => $request->type,
                'approved' => $approved,
                'reason' => $reason,
            ]
        );
    }

    /**
     * Log export actions
     */
    public static function logExport(string $type, ?array $filters = null)
    {
        return self::log(
            action: 'export',
            description: "Export data {$type}",
            category: 'report',
            severity: 'info',
            properties: [
                'export_type' => $type,
                'filters' => $filters,
                'format' => 'excel',
            ]
        );
    }

    /**
     * Log critical security events
     */
    public static function logSecurityEvent(string $event, string $description, ?array $details = null)
    {
        return self::log(
            action: $event,
            description: $description,
            category: 'system',
            severity: 'critical',
            properties: $details
        );
    }

    /**
     * Filter sensitive data from request
     */
    private static function filterSensitiveData(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key', 'secret'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[FILTERED]';
            }
        }

        return $data;
    }

    /**
     * Get category from model name
     */
    private static function getCategoryFromModel(string $modelName): string
    {
        return match($modelName) {
            'User' => 'user_management',
            'Student' => 'student_management',
            'Attendance', 'AbsenceRequest' => 'attendance',
            'Semester', 'AttendanceSettings' => 'settings',
            default => 'system',
        };
    }

    /**
     * Clean old logs (keep last 90 days)
     */
    public static function cleanupOldLogs(int $days = 90)
    {
        $threshold = now()->subDays($days);

        ActivityLog::where('created_at', '<', $threshold)
            ->where('severity', '!=', 'critical') // Keep critical logs longer
            ->delete();
    }
}
