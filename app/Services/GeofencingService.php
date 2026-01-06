<?php

namespace App\Services;

use App\Models\AttendanceLocation;
use Illuminate\Support\Facades\Log;

class GeofencingService
{
    /**
     * Validate if coordinates are within allowed geofencing radius
     *
     * @param float $latitude User's latitude
     * @param float $longitude User's longitude
     * @return array ['valid' => bool, 'location_id' => int|null, 'distance' => float|null, 'message' => string]
     */
    public function validateLocation(float $latitude, float $longitude, string $type = 'check_in'): array
    {
        // Get active locations
        $locations = AttendanceLocation::active()
            ->when($type === 'check_in', fn($q) => $q->checkInEnabled())
            ->when($type === 'check_out', fn($q) => $q->checkOutEnabled())
            ->get();

        if ($locations->isEmpty()) {
            return [
                'valid' => false,
                'location_id' => null,
                'distance' => null,
                'message' => 'Tidak ada lokasi absensi yang aktif.',
            ];
        }

        // Find nearest valid location
        $nearestLocation = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($locations as $location) {
            $distance = $this->calculateDistance(
                $latitude,
                $longitude,
                $location->latitude,
                $location->longitude
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearestLocation = $location;
            }
        }

        // Check if within radius
        if ($minDistance <= $nearestLocation->radius) {
            return [
                'valid' => true,
                'location_id' => $nearestLocation->id,
                'location_name' => $nearestLocation->name,
                'distance' => round($minDistance, 2),
                'message' => "Lokasi valid: {$nearestLocation->name} ({$minDistance}m)",
            ];
        }

        return [
            'valid' => false,
            'location_id' => null,
            'distance' => round($minDistance, 2),
            'message' => "Anda berada di luar area sekolah. Jarak terdekat: {$minDistance}m dari {$nearestLocation->name} (radius: {$nearestLocation->radius}m)",
        ];
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     *
     * @param float $lat1 Latitude 1
     * @param float $lon1 Longitude 1
     * @param float $lat2 Latitude 2
     * @param float $lon2 Longitude 2
     * @return float Distance in meters
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Validate GPS accuracy
     *
     * @param float $accuracy Accuracy in meters
     * @return array
     */
    public function validateAccuracy(float $accuracy): array
    {
        $threshold = config('attendance.gps_accuracy_threshold', 50);

        if ($accuracy > $threshold) {
            return [
                'valid' => false,
                'message' => "Akurasi GPS terlalu rendah ({$accuracy}m). Minimal: {$threshold}m. Silakan pindah ke area dengan sinyal GPS lebih baik.",
            ];
        }

        return [
            'valid' => true,
            'message' => "Akurasi GPS baik ({$accuracy}m)",
        ];
    }

    /**
     * Get all active geofencing locations
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveLocations()
    {
        return AttendanceLocation::active()->get();
    }

    /**
     * Get nearest location from coordinates
     *
     * @param float $latitude
     * @param float $longitude
     * @return array|null
     */
    public function getNearestLocation(float $latitude, float $longitude): ?array
    {
        $locations = $this->getActiveLocations();

        if ($locations->isEmpty()) {
            return null;
        }

        $nearestLocation = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($locations as $location) {
            $distance = $this->calculateDistance(
                $latitude,
                $longitude,
                $location->latitude,
                $location->longitude
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearestLocation = $location;
            }
        }

        return [
            'location' => $nearestLocation,
            'distance' => round($minDistance, 2),
            'within_radius' => $minDistance <= $nearestLocation->radius,
        ];
    }
}
