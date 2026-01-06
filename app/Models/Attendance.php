<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'class_id',
        'semester_id',
        'date',
        // Check-in
        'check_in_time',
        'check_in_method',
        'check_in_location_id',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_distance',
        'check_in_photo',
        // Check-out
        'check_out_time',
        'check_out_method',
        'check_out_location_id',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_distance',
        'check_out_photo',
        // Status
        'status',
        'late_minutes',
        'early_leave_minutes',
        'percentage',
        'notes',
        // Approval
        'approved_by',
        'approved_at',
        'approval_status',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
        'check_in_distance' => 'integer',
        'check_out_distance' => 'integer',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
        'percentage' => 'decimal:2',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the student this attendance belongs to
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the class this attendance belongs to
     */
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Get the semester this attendance belongs to
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get check-in location
     */
    public function checkInLocation()
    {
        return $this->belongsTo(AttendanceLocation::class, 'check_in_location_id');
    }

    /**
     * Get check-out location
     */
    public function checkOutLocation()
    {
        return $this->belongsTo(AttendanceLocation::class, 'check_out_location_id');
    }

    /**
     * Get approver (user who approved manual attendance)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get manual attendance records
     */
    public function manualAttendances()
    {
        return $this->hasMany(ManualAttendance::class);
    }

    /**
     * Get violations related to this attendance
     */
    public function violations()
    {
        return $this->hasMany(AttendanceViolation::class);
    }

    /**
     * Get anomalies related to this attendance
     */
    public function anomalies()
    {
        return $this->hasMany(AttendanceAnomaly::class);
    }

    /**
     * Get approvals related to this attendance
     */
    public function approvals()
    {
        return $this->hasMany(AttendanceApproval::class);
    }

    /**
     * Check if attendance is complete (has both check-in and check-out)
     */
    public function isComplete(): bool
    {
        return !is_null($this->check_in_time) && !is_null($this->check_out_time);
    }

    /**
     * Check if student is late
     */
    public function isLate(): bool
    {
        return $this->late_minutes > 0;
    }

    /**
     * Check if student left early
     */
    public function isEarlyLeave(): bool
    {
        return $this->early_leave_minutes > 0;
    }

    /**
     * Scope: filter by student
     */
    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope: filter by class
     */
    public function scopeByClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope: filter by semester
     */
    public function scopeBySemester($query, int $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    /**
     * Scope: filter by date
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope: filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope: filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: filter by method
     */
    public function scopeByCheckInMethod($query, string $method)
    {
        return $query->where('check_in_method', $method);
    }

    /**
     * Scope: pending approval
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope: today's attendance
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }
}
