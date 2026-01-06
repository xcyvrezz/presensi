<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'code' => 'PPLG',
                'name' => 'Pengembangan Perangkat Lunak dan Gim',
                'description' => 'Jurusan yang mempelajari pemrograman, pengembangan aplikasi, web development, mobile development, dan game development.',
                'head_teacher' => null, // Will be assigned later
                'phone' => null,
                'is_active' => true,
            ],
            [
                'code' => 'AKL',
                'name' => 'Akuntansi dan Keuangan Lembaga',
                'description' => 'Jurusan yang mempelajari akuntansi, pembukuan, perpajakan, dan manajemen keuangan.',
                'head_teacher' => null,
                'phone' => null,
                'is_active' => true,
            ],
            [
                'code' => 'TO',
                'name' => 'Teknik Otomotif',
                'description' => 'Jurusan yang mempelajari teknologi kendaraan bermotor, mesin otomotif, dan sistem kelistrikan kendaraan.',
                'head_teacher' => null,
                'phone' => null,
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        $this->command->info('✓ 3 departments created successfully (PPLG, AKL, TO)');
    }
}
