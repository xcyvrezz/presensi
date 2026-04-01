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

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function isHoliday($date): bool
    {
        $date = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        return static::where('is_holiday', true)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
    }

    public static function getHolidayDates($startDate, $endDate, ?int $departmentId = null, ?int $classId = null): array
    {
        return static::getDatesByFilter($startDate, $endDate, true, $departmentId, $classId);
    }

    public static function getExcludedDates($startDate, $endDate, ?int $departmentId = null, ?int $classId = null): array
    {
        return static::getDatesByFilter($startDate, $endDate, null, $departmentId, $classId);
    }

    protected static function getDatesByFilter($startDate, $endDate, ?bool $onlyHoliday = null, ?int $departmentId = null, ?int $classId = null): array
    {
        $startDate = is_string($startDate) ? \Carbon\Carbon::parse($startDate) : $startDate;
        $endDate = is_string($endDate) ? \Carbon\Carbon::parse($endDate) : $endDate;

        $query = static::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                    $subQuery->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });

        if (!is_null($onlyHoliday)) {
            $query->where('is_holiday', $onlyHoliday);
        }

        $events = $query->get()->filter(function ($event) use ($departmentId, $classId) {
            $affectedDepartments = collect($event->affected_departments ?? [])->map(fn ($id) => (int) $id)->filter()->values();
            $affectedClasses = collect($event->affected_classes ?? [])->map(fn ($id) => (int) $id)->filter()->values();

            if ($affectedDepartments->isEmpty() && $affectedClasses->isEmpty()) {
                return true;
            }

            if (!is_null($classId) && $affectedClasses->contains((int) $classId)) {
                return true;
            }

            if (!is_null($departmentId) && $affectedDepartments->contains((int) $departmentId)) {
                return true;
            }

            return false;
        });

        $dates = [];

        foreach ($events as $event) {
            $current = \Carbon\Carbon::parse($event->start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($event->end_date)->startOfDay();

            if ($current->lt($startDate)) {
                $current = $startDate->copy()->startOfDay();
            }

            if ($end->gt($endDate)) {
                $end = $endDate->copy()->startOfDay();
            }

            while ($current->lte($end)) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }
        }

        return array_values(array_unique($dates));
    }

    public function getDurationDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function scopeHolidays($query)
    {
        return $query->where('is_holiday', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString())
            ->orderBy('start_date');
    }

    public function hasCustomTimes(): bool
    {
        return $this->use_custom_times &&
               ($this->custom_check_in_start || $this->custom_check_out_start);
    }

    public function getEffectiveCheckInStart(): string
    {
        if ($this->use_custom_times && $this->custom_check_in_start) {
            return $this->custom_check_in_start;
        }
        return AttendanceSetting::getValue('check_in_start', '06:00:00');
    }

    public function getEffectiveCheckInEnd(): string
    {
        if ($this->use_custom_times && $this->custom_check_in_end) {
            return $this->custom_check_in_end;
        }
        return AttendanceSetting::getValue('check_in_end', '08:30:00');
    }

    public function getEffectiveCheckInNormal(): string
    {
        if ($this->use_custom_times && $this->custom_check_in_normal) {
            return $this->custom_check_in_normal;
        }
        return AttendanceSetting::getValue('check_in_normal', '07:30:00');
    }

    public function getEffectiveCheckOutStart(): string
    {
        if ($this->use_custom_times && $this->custom_check_out_start) {
            return $this->custom_check_out_start;
        }
        return AttendanceSetting::getValue('check_out_start', '15:00:00');
    }

    public function getEffectiveCheckOutEnd(): string
    {
        if ($this->use_custom_times && $this->custom_check_out_end) {
            return $this->custom_check_out_end;
        }
        return AttendanceSetting::getValue('check_out_end', '18:00:00');
    }

    public function getEffectiveCheckOutNormal(): string
    {
        if ($this->use_custom_times && $this->custom_check_out_normal) {
            return $this->custom_check_out_normal;
        }
        return AttendanceSetting::getValue('check_out_normal', '15:30:00');
    }

    public function affectsStudent($studentId): bool
    {
        if (!$this->affected_departments && !$this->affected_classes) {
            return true;
        }

        $student = \App\Models\Student::find($studentId);
        if (!$student) {
            return false;
        }

        if ($this->affected_departments && count($this->affected_departments) > 0) {
            $departmentId = $student->class->department_id ?? null;
            if (!in_array($departmentId, $this->affected_departments)) {
                return false;
            }
        }

        if ($this->affected_classes && count($this->affected_classes) > 0) {
            if (!in_array($student->class_id, $this->affected_classes)) {
                return false;
            }
        }

        return true;
    }

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

