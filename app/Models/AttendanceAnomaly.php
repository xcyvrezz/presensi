<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceAnomaly extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attendance_id',
        'student_id',
        'date',
        'anomaly_type',
        'severity',
        'description',
        'data',
        'detection_method',
        'detected_at',
        'is_reviewed',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'resolution',
    ];

    protected $casts = [
        'date' => 'date',
        'data' => 'array',
        'detected_at' => 'datetime',
        'is_reviewed' => 'boolean',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the attendance record
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the student
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the reviewer
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if reviewed
     */
    public function isReviewed(): bool
    {
        return $this->is_reviewed;
    }

    /**
     * Scope: filter by student
     */
    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope: filter by anomaly type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('anomaly_type', $type);
    }

    /**
     * Scope: filter by severity
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope: unreviewed anomalies
     */
    public function scopeUnreviewed($query)
    {
        return $query->where('is_reviewed', false);
    }

    /**
     * Scope: reviewed anomalies
     */
    public function scopeReviewed($query)
    {
        return $query->where('is_reviewed', true);
    }

    /**
     * Scope: critical severity
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope: filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope: filter by detection method
     */
    public function scopeByDetectionMethod($query, string $method)
    {
        return $query->where('detection_method', $method);
    }
}
