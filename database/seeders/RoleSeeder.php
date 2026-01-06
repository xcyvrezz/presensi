<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Akses penuh ke seluruh sistem. Dapat mengelola users, master data, absensi, laporan, dan pengaturan.',
                'is_active' => true,
            ],
            [
                'name' => 'kepala_sekolah',
                'display_name' => 'Kepala Sekolah',
                'description' => 'Dapat melihat dashboard analitik, laporan komprehensif, approval absensi manual, dan monitoring seluruh sistem.',
                'is_active' => true,
            ],
            [
                'name' => 'wali_kelas',
                'display_name' => 'Wali Kelas',
                'description' => 'Dapat mengelola absensi siswa di kelasnya, input manual attendance, melihat laporan kelas, dan approval absensi.',
                'is_active' => true,
            ],
            [
                'name' => 'siswa',
                'display_name' => 'Siswa',
                'description' => 'Dapat melakukan check-in/check-out via NFC, melihat riwayat absensi pribadi, dan mengajukan izin/dispensasi.',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        $this->command->info('✓ 4 roles created successfully');
    }
}
