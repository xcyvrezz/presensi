<?php

namespace Database\Seeders;

use App\Models\AttendanceLocation;
use Illuminate\Database\Seeder;

class AttendanceLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Gerbang Utama SMK Negeri 10 Pandeglang',
                'description' => 'Lokasi check-in/out utama di gerbang sekolah',
                'latitude' => -6.3019816, // Koordinat SMK Negeri 10 Pandeglang (contoh, perlu disesuaikan)
                'longitude' => 105.9632744,
                'radius' => 15,
                'type' => 'gate',
                'is_check_in_enabled' => true,
                'is_check_out_enabled' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Gedung A (Lab PPLG)',
                'description' => 'Laboratorium Pengembangan Perangkat Lunak dan Gim',
                'latitude' => -6.3020000, // Sesuaikan dengan koordinat aktual
                'longitude' => 105.9633000,
                'radius' => 15,
                'type' => 'lab',
                'is_check_in_enabled' => true,
                'is_check_out_enabled' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Gedung B (Lab AKL)',
                'description' => 'Laboratorium Akuntansi dan Keuangan Lembaga',
                'latitude' => -6.3019500,
                'longitude' => 105.9633500,
                'radius' => 15,
                'type' => 'lab',
                'is_check_in_enabled' => true,
                'is_check_out_enabled' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Workshop TO',
                'description' => 'Workshop Teknik Otomotif',
                'latitude' => -6.3020500,
                'longitude' => 105.9632000,
                'radius' => 15,
                'type' => 'lab',
                'is_check_in_enabled' => true,
                'is_check_out_enabled' => true,
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            AttendanceLocation::create($location);
        }

        $this->command->info('✓ ' . count($locations) . ' attendance locations created successfully');
        $this->command->warn('⚠ Note: GPS coordinates are example values. Please update with actual coordinates.');
    }
}
