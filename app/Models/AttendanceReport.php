<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'generated_by',
        'report_name',
        'report_type',
        'filters',
        'start_date',
        'end_date',
        'student_id',
        'class_id',
        'department_id',
        'semester_id',
        'format',
        'file_path',
        'file_size',
        'statistics',
        'status',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'file_size' => 'integer',
        'statistics' => 'array',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who generated this report
     */
    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get the student (for student-specific reports)
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the class (for class-specific reports)
     */
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Get the department (for department-specific reports)
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the semester
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Check if report is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if report is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if report failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) return '-';

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $unit = 0;

        while ($bytes >= 1024 && $unit < count($units) - 1) {
            $bytes /= 1024;
            $unit++;
        }

        return round($bytes, 2) . ' ' . $units[$unit];
    }

    /**
     * Scope: filter by report type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope: filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: completed reports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: filter by generator
     */
    public function scopeByGenerator($query, int $userId)
    {
        return $query->where('generated_by', $userId);
    }

    /**
     * Scope: filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }
}
