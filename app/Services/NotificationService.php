<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a new notification
     */
    public static function create(
        User|int $user,
        string $title,
        string $message,
        string $type = 'other',
        string $priority = 'normal',
        ?array $data = null,
        ?string $actionUrl = null,
        ?string $relatedType = null,
        ?int $relatedId = null
    ): Notification {
        $userId = $user instanceof User ? $user->id : $user;

        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => $priority,
            'data' => $data,
            'action_url' => $actionUrl,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'sent_at' => now(),
        ]);
    }

    /**
     * Notify when absence request is submitted
     */
    public static function absenceRequestSubmitted($absenceRequest)
    {
        // Notify wali kelas
        $waliKelas = $absenceRequest->student->class->waliKelas;

        if ($waliKelas) {
            self::create(
                user: $waliKelas,
                title: 'Pengajuan Izin Baru',
                message: "{$absenceRequest->student->full_name} mengajukan {$absenceRequest->type_label} untuk tanggal {$absenceRequest->absence_date->format('d/m/Y')}",
                type: 'approval_request',
                priority: 'normal',
                actionUrl: route('wali-kelas.absence-requests'),
                relatedType: 'AbsenceRequest',
                relatedId: $absenceRequest->id
            );
        }
    }

    /**
     * Notify when absence request is approved
     */
    public static function absenceRequestApproved($absenceRequest, $approvedBy)
    {
        // Notify student
        self::create(
            user: $absenceRequest->student->user,
            title: 'Izin Disetujui',
            message: "Pengajuan {$absenceRequest->type_label} Anda untuk tanggal {$absenceRequest->absence_date->format('d/m/Y')} telah disetujui oleh {$approvedBy->name}",
            type: 'approval_result',
            priority: 'normal',
            actionUrl: route('student.absence.request'),
            relatedType: 'AbsenceRequest',
            relatedId: $absenceRequest->id
        );
    }

    /**
     * Notify when absence request is rejected
     */
    public static function absenceRequestRejected($absenceRequest, $approvedBy, $reason)
    {
        // Notify student
        self::create(
            user: $absenceRequest->student->user,
            title: 'Izin Ditolak',
            message: "Pengajuan {$absenceRequest->type_label} Anda untuk tanggal {$absenceRequest->absence_date->format('d/m/Y')} ditolak. Alasan: {$reason}",
            type: 'approval_result',
            priority: 'high',
            actionUrl: route('student.absence.request'),
            relatedType: 'AbsenceRequest',
            relatedId: $absenceRequest->id
        );
    }

    /**
     * Notify about late attendance
     */
    public static function lateAttendance($attendance)
    {
        self::create(
            user: $attendance->student->user,
            title: 'Terlambat Masuk',
            message: "Anda terlambat {$attendance->late_minutes} menit pada {$attendance->check_in_time->format('d/m/Y H:i')}. Harap datang tepat waktu.",
            type: 'violation_warning',
            priority: 'normal',
            actionUrl: route('student.attendance.history'),
            relatedType: 'Attendance',
            relatedId: $attendance->id
        );
    }

    /**
     * Notify about alpha (unexcused absence)
     */
    public static function alphaWarning($student, $date)
    {
        self::create(
            user: $student->user,
            title: 'Peringatan Alpha',
            message: "Anda tidak hadir tanpa keterangan pada {$date->format('d/m/Y')}. Segera hubungi wali kelas Anda.",
            type: 'violation_warning',
            priority: 'high',
            actionUrl: route('student.absence.request')
        );

        // Also notify wali kelas
        $waliKelas = $student->class->waliKelas;
        if ($waliKelas) {
            self::create(
                user: $waliKelas,
                title: 'Siswa Alpha',
                message: "{$student->full_name} tidak hadir tanpa keterangan pada {$date->format('d/m/Y')}",
                type: 'violation_warning',
                priority: 'normal',
                actionUrl: route('wali-kelas.students')
            );
        }
    }

    /**
     * Attendance reminder for students who haven't checked in
     */
    public static function attendanceReminder($student)
    {
        self::create(
            user: $student->user,
            title: 'Reminder Absensi',
            message: 'Anda belum melakukan absensi hari ini. Segera lakukan check-in!',
            type: 'attendance_reminder',
            priority: 'normal',
            actionUrl: route('student.nfc')
        );
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead(User $user)
    {
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Delete old read notifications (older than 30 days)
     */
    public static function cleanupOldNotifications()
    {
        $threshold = now()->subDays(30);

        Notification::where('is_read', true)
            ->where('read_at', '<', $threshold)
            ->delete();
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }
}
