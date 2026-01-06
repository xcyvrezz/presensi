<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'head_teacher',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all classes in this department
     */
    public function classes()
    {
        return $this->hasMany(Classes::class);
    }

    /**
     * Get all students in this department (through classes)
     */
    public function students()
    {
        return $this->hasManyThrough(
            Student::class,        // Final model
            Classes::class,        // Intermediate model
            'department_id',       // Foreign key on classes table
            'class_id',           // Foreign key on students table
            'id',                 // Local key on departments table
            'id'                  // Local key on classes table
        );
    }

    /**
     * Get attendance reports for this department
     */
    public function attendanceReports()
    {
        return $this->hasMany(AttendanceReport::class);
    }

    /**
     * Scope: only active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
