<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin - ALL permissions (72 permissions)
        $adminRole = Role::where('name', 'admin')->first();
        $allPermissions = Permission::all();
        $adminRole->permissions()->attach($allPermissions->pluck('id'));

        // Kepala Sekolah - 17 permissions (view-oriented, analytics, reports, approvals)
        $kepalaSekolahRole = Role::where('name', 'kepala_sekolah')->first();
        $kepalaSekolahPermissions = Permission::whereIn('name', [
            // Dashboard & Analytics
            'dashboard.view_principal',
            'dashboard.analytics',
            'dashboard.realtime',

            // View permissions
            'users.view',
            'students.view',
            'students.view_detail',
            'classes.view',
            'classes.view_students',
            'classes.view_statistics',
            'attendance.view_all',
            'attendance.view_violations',
            'attendance.view_anomalies',

            // Approval permissions
            'attendance.manual_approve',
            'attendance.manual_reject',

            // Reports - all report permissions
            'reports.view_all',
            'reports.generate_daily',
            'reports.generate_monthly',
            'reports.generate_semester',
            'reports.export_pdf',
            'reports.export_excel',
        ])->get();
        $kepalaSekolahRole->permissions()->attach($kepalaSekolahPermissions->pluck('id'));

        // Wali Kelas - 24 permissions (manage class, manual attendance, approvals)
        $waliKelasRole = Role::where('name', 'wali_kelas')->first();
        $waliKelasPermissions = Permission::whereIn('name', [
            // Dashboard
            'dashboard.view_teacher',

            // Students in their class
            'students.view',
            'students.view_detail',
            'students.edit',
            'students.assign_card',
            'students.enable_nfc',

            // Class management
            'classes.view',
            'classes.view_students',
            'classes.view_statistics',

            // Attendance management for their class
            'attendance.view_class',
            'attendance.manual_input',
            'attendance.manual_approve',
            'attendance.manual_reject',
            'attendance.edit',
            'attendance.export',
            'attendance.view_violations',
            'attendance.manage_violations',
            'attendance.view_anomalies',

            // Reports for their class
            'reports.view_class',
            'reports.generate_daily',
            'reports.generate_monthly',
            'reports.export_pdf',
            'reports.export_excel',
        ])->get();
        $waliKelasRole->permissions()->attach($waliKelasPermissions->pluck('id'));

        // Siswa - 5 permissions (view own data, check-in/out)
        $siswaRole = Role::where('name', 'siswa')->first();
        $siswaPermissions = Permission::whereIn('name', [
            // Dashboard
            'dashboard.view_student',

            // Own attendance
            'attendance.view_own',
            'attendance.checkin',
            'attendance.checkout',

            // Own reports
            'reports.view_own',
        ])->get();
        $siswaRole->permissions()->attach($siswaPermissions->pluck('id'));

        $this->command->info('✓ Role-Permission mappings created successfully');
        $this->command->info('  - Admin: ' . $allPermissions->count() . ' permissions');
        $this->command->info('  - Kepala Sekolah: ' . $kepalaSekolahPermissions->count() . ' permissions');
        $this->command->info('  - Wali Kelas: ' . $waliKelasPermissions->count() . ' permissions');
        $this->command->info('  - Siswa: ' . $siswaPermissions->count() . ' permissions');
    }
}
