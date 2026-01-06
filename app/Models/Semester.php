<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semester extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'academic_year',
        'semester',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'semester' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all academic calendars for this semester
     */
    public function academicCalendars()
    {
        return $this->hasMany(AcademicCalendar::class);
    }

    /**
     * Get all attendances in this semester
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get all violations in this semester
     */
    public function violations()
    {
        return $this->hasMany(AttendanceViolation::class);
    }

    /**
     * Get attendance reports for this semester
     */
    public function attendanceReports()
    {
        return $this->hasMany(AttendanceReport::class);
    }

    /**
     * Check if date is within semester period
     */
    public function isDateInSemester($date): bool
    {
        $date = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        return $date->between($this->start_date, $this->end_date);
    }

    /**
     * Get total days in semester
     */
    public function getTotalDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Scope: only active semester
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: current semester (based on today's date)
     */
    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }
}
