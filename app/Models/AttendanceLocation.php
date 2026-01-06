<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'radius',
        'type',
        'is_check_in_enabled',
        'is_check_out_enabled',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius' => 'integer',
        'is_check_in_enabled' => 'boolean',
        'is_check_out_enabled' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get check-in attendances at this location
     */
    public function checkInAttendances()
    {
        return $this->hasMany(Attendance::class, 'check_in_location_id');
    }

    /**
     * Get check-out attendances at this location
     */
    public function checkOutAttendances()
    {
        return $this->hasMany(Attendance::class, 'check_out_location_id');
    }

    /**
     * Calculate distance from given coordinates using Haversine formula
     * Returns distance in meters
     */
    public function calculateDistance(float $lat, float $lng): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if coordinates are within geofencing radius
     */
    public function isWithinRadius(float $lat, float $lng): bool
    {
        return $this->calculateDistance($lat, $lng) <= $this->radius;
    }

    /**
     * Scope: only active locations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: check-in enabled
     */
    public function scopeCheckInEnabled($query)
    {
        return $query->where('is_check_in_enabled', true);
    }

    /**
     * Scope: check-out enabled
     */
    public function scopeCheckOutEnabled($query)
    {
        return $query->where('is_check_out_enabled', true);
    }

    /**
     * Scope: filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
