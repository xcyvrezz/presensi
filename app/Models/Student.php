<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'class_id',
        'nis',
        'nisn',
        'card_uid',
        'full_name',
        'nickname',
        'gender',
        'birth_date',
        'birth_place',
        'address',
        'phone',
        'parent_phone',
        'parent_name',
        'photo',
        'nfc_enabled',
        'home_latitude',
        'home_longitude',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'nfc_enabled' => 'boolean',
        'home_latitude' => 'decimal:8',
        'home_longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user account for this student
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the class this student belongs to
     */
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Get all attendances for this student
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get all manual attendances for this student
     */
    public function manualAttendances()
    {
        return $this->hasMany(ManualAttendance::class);
    }

    /**
     * Get all violations for this student
     */
    public function violations()
    {
        return $this->hasMany(AttendanceViolation::class);
    }

    /**
     * Get all anomalies for this student
     */
    public function anomalies()
    {
        return $this->hasMany(AttendanceAnomaly::class);
    }

    /**
     * Get attendance reports for this student
     */
    public function attendanceReports()
    {
        return $this->hasMany(AttendanceReport::class);
    }

    /**
     * Get full name with NIS
     */
    public function getFullNameWithNisAttribute(): string
    {
        return "{$this->full_name} ({$this->nis})";
    }

    /**
     * Get age
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    /**
     * Scope: filter by class
     */
    public function scopeByClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope: filter by gender
     */
    public function scopeByGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope: only active students
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: NFC enabled students
     */
    public function scopeNfcEnabled($query)
    {
        return $query->where('nfc_enabled', true);
    }
}
