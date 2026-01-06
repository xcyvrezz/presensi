<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicCalendar extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'semester_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'type',
        'is_holiday',
        'color',
        'custom_check_in_start',
        'custom_check_in_end',
        'custom_check_in_normal',
        'custom_check_out_start',
        'custom_check_out_end',
        'custom_check_out_normal',
        'use_custom_times',
        'affected_departments',
        'affected_classes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_holiday' => 'boolean',
        'use_custom_times' => 'boolean',
        'affected_departments' => 'array',
        'affected_classes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the semester this calendar belongs to
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the user who created this calendar event
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if given date is a holiday
     */
    public static function isHoliday($date): bool
    {
        $date = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        return static::where('is_holiday', true)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
    }

    /**
     * Get all holiday dates in a date range
     */
    public static function getHolidayDates($startDate, $endDate): array
    {
        $startDate = is_string($startDate) ? \Carbon\Carbon::parse($startDate) : $startDate;
        $endDate = is_string($endDate) ? \Carbon\Carbon::parse($endDate) : $endDate;

        $holidays = static::where('is_holiday', true)
            ->where(function($query) use ($startDate, $endDate) {
                // Holiday overlaps with our date range
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($subQ) use ($startDate, $endDate) {
                          $subQ->where('start_date', '<=', $startDate)
                               ->where('end_date', '>=', $endDate);
                      });
                });
            })
            ->get();

        $holidayDates = [];

        foreach ($holidays as $holiday) {
            $current = \Carbon\Carbon::parse($holiday->start_date);
            $end = \Carbon\Carbon::parse($holiday->end_date);

            // Make sure we only include dates within our range
            if ($current->lt($startDate)) {
                $current = $startDate->copy();
            }
            if ($end->gt($endDate)) {
                $end = $endDate->copy();
            }

            while ($current->lte($end)) {
                $holidayDates[] = $current->format('Y-m-d');
                $current->addDay();
            }
        }

        return array_unique($holidayDates);
    }

    /**
     * Get duration in days
     */
    public function getDurationDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Scope: only holidays
     */
    public function scopeHolidays($query)
    {
        return $query->where('is_holiday', true);
    }

    /**
     * Scope: filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString())
            ->orderBy('start_date');
    }

    /**
     * Check if this event has custom attendance times
     */
    public function hasCustomTimes(): bool
    {
        return $this->use_custom_times &&
               ($this->custom_check_in_start || $this->custom_check_out_start);
    }

    /**
     * Get effective check-in start time (custom or default)
     */
    public function getEffectiveCheckInStart(): string
    {
        if ($this->use_custom_times && $this->custom_check_in_start) {
            return $this->custom_check_in_start;
        }
        return AttendanceSetting::getValue('check_in_start', '06:00:00');
    }

    /**
     * Get effective check-in end time
     */
    public function getEffectiveCheckInEnd(): string
    {
        if ($this->use_custom_times && $this->custom_check_in_end) {
            return $this->custom_check_in_end;
        }
        return AttendanceSetting::getValue('check_in_end', '08:30:00');
    }

    /**
     * Get effective check-in normal time (untuk hitung terlambat)
     */
    public function getEffectiveCheckInNormal(): string
    {
        if ($this->use_custom_times && $this->custom_check_in_normal) {
            return $this->custom_check_in_normal;
        }
        return AttendanceSetting::getValue('check_in_normal', '07:30:00');
    }

    /**
     * Get effective check-out start time
     */
    public function getEffectiveCheckOutStart(): string
    {
        if ($this->use_custom_times && $this->custom_check_out_start) {
            return $this->custom_check_out_start;
        }
        return AttendanceSetting::getValue('check_out_start', '15:00:00');
    }

    /**
     * Get effective check-out end time
     */
    public function getEffectiveCheckOutEnd(): string
    {
        if ($this->use_custom_times && $this->custom_check_out_end) {
            return $this->custom_check_out_end;
        }
        return AttendanceSetting::getValue('check_out_end', '18:00:00');
    }

    /**
     * Get effective check-out normal time (untuk hitung pulang cepat)
     */
    public function getEffectiveCheckOutNormal(): string
    {
        if ($this->use_custom_times && $this->custom_check_out_normal) {
            return $this->custom_check_out_normal;
        }
        return AttendanceSetting::getValue('check_out_normal', '15:30:00');
    }

    /**
     * Check if event affects specific student (by department or class)
     */
    public function affectsStudent($studentId): bool
    {
        // If no filters, affects all students
        if (!$this->affected_departments && !$this->affected_classes) {
            return true;
        }

        $student = \App\Models\Student::find($studentId);
        if (!$student) {
            return false;
        }

        // Check department filter
        if ($this->affected_departments && count($this->affected_departments) > 0) {
            $departmentId = $student->class->department_id ?? null;
            if (!in_array($departmentId, $this->affected_departments)) {
                return false;
            }
        }

        // Check class filter
        if ($this->affected_classes && count($this->affected_classes) > 0) {
            if (!in_array($student->class_id, $this->affected_classes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get type label in Indonesian
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'holiday' => 'Hari Libur',
            'event' => 'Acara/Kegiatan',
            'exam' => 'Ujian',
            'other' => 'Lainnya',
            default => ucfirst($this->type),
        };
    }
}
