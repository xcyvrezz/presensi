<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classes extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'wali_kelas_id',
        'name',
        'grade',
        'academic_year',
        'capacity',
        'current_students',
        'description',
        'is_active',
    ];

    protected $casts = [
        'grade' => 'integer',
        'capacity' => 'integer',
        'current_students' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the department this class belongs to
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the wali kelas (homeroom teacher) for this class
     */
    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    /**
     * Get all students in this class
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * Get all attendances for this class
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    /**
     * Get attendance reports for this class
     */
    public function attendanceReports()
    {
        return $this->hasMany(AttendanceReport::class);
    }

    /**
     * Check if class is full
     */
    public function isFull(): bool
    {
        return $this->current_students >= $this->capacity;
    }

    /**
     * Get available slots
     */
    public function getAvailableSlotsAttribute(): int
    {
        return max(0, $this->capacity - $this->current_students);
    }

    /**
     * Scope: filter by grade
     */
    public function scopeGrade($query, int $grade)
    {
        return $query->where('grade', $grade);
    }

    /**
     * Scope: filter by academic year
     */
    public function scopeAcademicYear($query, string $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    /**
     * Scope: only active classes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
