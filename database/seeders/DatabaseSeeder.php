<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        $this->command->newLine();

        $this->call([
            // Step 1: Roles & Permissions (RBAC system)
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,

            // Step 2: Master Data
            DepartmentSeeder::class,
            AttendanceSettingSeeder::class,
            AttendanceLocationSeeder::class,

            // Step 3: Demo Users
            DemoUserSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->warn('📝 Next steps:');
        $this->command->warn('   1. Update GPS coordinates in attendance_locations table');
        $this->command->warn('   2. Create semester and academic calendar data');
        $this->command->warn('   3. Create classes and assign wali kelas');
        $this->command->warn('   4. Import students from Excel or create manually');
    }
}

