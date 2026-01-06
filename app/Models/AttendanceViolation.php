<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceViolation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'attendance_id',
        'semester_id',
        'violation_date',
        'type',
        'points',
        'description',
        'evidence',
        'sanction_level',
        'sanction_notes',
        'sanctioned_by',
        'sanctioned_at',
        'is_resolved',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'violation_date' => 'date',
        'points' => 'integer',
        'evidence' => 'array',
        'sanctioned_at' => 'datetime',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the student this violation belongs to
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the related attendance
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the semester
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the user who gave sanction
     */
    public function sanctioner()
    {
        return $this->belongsTo(User::class, 'sanctioned_by');
    }

    /**
     * Scope: filter by student
     */
    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope: filter by semester
     */
    public function scopeBySemester($query, int $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    /**
     * Scope: filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: filter by sanction level
     */
    public function scopeBySanctionLevel($query, string $level)
    {
        return $query->where('sanction_level', $level);
    }

    /**
     * Scope: unresolved violations
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope: resolved violations
     */
    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    /**
     * Scope: filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('violation_date', [$startDate, $endDate]);
    }
}
