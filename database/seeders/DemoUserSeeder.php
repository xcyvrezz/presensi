<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $kepalaSekolahRole = Role::where('name', 'kepala_sekolah')->first();
        $waliKelasRole = Role::where('name', 'wali_kelas')->first();
        $siswaRole = Role::where('name', 'siswa')->first();

        // Demo users
        $users = [
            // 1 Admin
            [
                'role_id' => $adminRole->id,
                'name' => 'Admin SMK Negeri 10',
                'email' => 'admin@smkn10pdg.sch.id',
                'password' => Hash::make('admin123'),
                'phone' => '081234567890',
                'photo' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],

            // 1 Kepala Sekolah
            [
                'role_id' => $kepalaSekolahRole->id,
                'name' => 'Dr. Suherman, M.Pd.',
                'email' => 'kepala.sekolah@smkn10pdg.sch.id',
                'password' => Hash::make('kepsek123'),
                'phone' => '081234567891',
                'photo' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],

            // 3 Wali Kelas
            [
                'role_id' => $waliKelasRole->id,
                'name' => 'Budi Santoso, S.Kom.',
                'email' => 'budi.santoso@smkn10pdg.sch.id',
                'password' => Hash::make('walikelas123'),
                'phone' => '081234567892',
                'photo' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'role_id' => $waliKelasRole->id,
                'name' => 'Siti Aminah, S.Pd.',
                'email' => 'siti.aminah@smkn10pdg.sch.id',
                'password' => Hash::make('walikelas123'),
                'phone' => '081234567893',
                'photo' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'role_id' => $waliKelasRole->id,
                'name' => 'Ahmad Fauzi, S.T.',
                'email' => 'ahmad.fauzi@smkn10pdg.sch.id',
                'password' => Hash::make('walikelas123'),
                'phone' => '081234567894',
                'photo' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],

            // 5 Siswa demo
            [
                'role_id' => $siswaRole->id,
                'name' => 'Andi Wijaya',
                'email' => 'andi.wijaya@student.smkn10pdg.sch.id',
                'password' => Hash::make('siswa123'),
                'phone' => '081234567895',
                'photo' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'role_id' => $siswaRole->id,
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@student.smkn10pdg.sch.id',
                'password' => Hash::make('siswa123'),
                'phone' => '081234567896',
                'photo' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'role_id' => $siswaRole->id,
                'name' => 'Reza Pratama',
                'email' => 'reza.pratama@student.smkn10pdg.sch.id',
                'password' => Hash::make('siswa123'),
                'phone' => '081234567897',
                'photo' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'role_id' => $siswaRole->id,
                'name' => 'Fitria Rahmawati',
                'email' => 'fitria.rahmawati@student.smkn10pdg.sch.id',
                'password' => Hash::make('siswa123'),
                'phone' => '081234567898',
                'photo' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'role_id' => $siswaRole->id,
                'name' => 'Doni Saputra',
                'email' => 'doni.saputra@student.smkn10pdg.sch.id',
                'password' => Hash::make('siswa123'),
                'phone' => '081234567899',
                'photo' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('✓ ' . count($users) . ' demo users created successfully');
        $this->command->info('  - 1 Admin (admin@smkn10pdg.sch.id / admin123)');
        $this->command->info('  - 1 Kepala Sekolah (kepala.sekolah@smkn10pdg.sch.id / kepsek123)');
        $this->command->info('  - 3 Wali Kelas (password: walikelas123)');
        $this->command->info('  - 5 Siswa (password: siswa123)');
    }
}
