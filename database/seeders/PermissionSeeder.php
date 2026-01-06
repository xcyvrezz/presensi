<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Users Management (10 permissions)
            ['name' => 'users.view', 'display_name' => 'Lihat Users', 'group' => 'users', 'description' => 'Melihat daftar users'],
            ['name' => 'users.create', 'display_name' => 'Tambah User', 'group' => 'users', 'description' => 'Membuat user baru'],
            ['name' => 'users.edit', 'display_name' => 'Edit User', 'group' => 'users', 'description' => 'Mengubah data user'],
            ['name' => 'users.delete', 'display_name' => 'Hapus User', 'group' => 'users', 'description' => 'Menghapus user'],
            ['name' => 'users.activate', 'display_name' => 'Aktifkan/Nonaktifkan User', 'group' => 'users', 'description' => 'Mengaktifkan atau menonaktifkan user'],
            ['name' => 'users.reset_password', 'display_name' => 'Reset Password User', 'group' => 'users', 'description' => 'Reset password user'],
            ['name' => 'users.assign_role', 'display_name' => 'Assign Role', 'group' => 'users', 'description' => 'Mengubah role user'],
            ['name' => 'users.view_logs', 'display_name' => 'Lihat User Logs', 'group' => 'users', 'description' => 'Melihat activity log user'],
            ['name' => 'users.import', 'display_name' => 'Import Users', 'group' => 'users', 'description' => 'Import users dari Excel'],
            ['name' => 'users.export', 'display_name' => 'Export Users', 'group' => 'users', 'description' => 'Export users ke Excel'],

            // Students Management (12 permissions)
            ['name' => 'students.view', 'display_name' => 'Lihat Siswa', 'group' => 'students', 'description' => 'Melihat daftar siswa'],
            ['name' => 'students.view_detail', 'display_name' => 'Lihat Detail Siswa', 'group' => 'students', 'description' => 'Melihat detail lengkap siswa'],
            ['name' => 'students.create', 'display_name' => 'Tambah Siswa', 'group' => 'students', 'description' => 'Membuat data siswa baru'],
            ['name' => 'students.edit', 'display_name' => 'Edit Siswa', 'group' => 'students', 'description' => 'Mengubah data siswa'],
            ['name' => 'students.delete', 'display_name' => 'Hapus Siswa', 'group' => 'students', 'description' => 'Menghapus data siswa'],
            ['name' => 'students.activate', 'display_name' => 'Aktifkan/Nonaktifkan Siswa', 'group' => 'students', 'description' => 'Status aktif siswa'],
            ['name' => 'students.assign_card', 'display_name' => 'Assign Kartu RFID', 'group' => 'students', 'description' => 'Mendaftarkan UID kartu RFID'],
            ['name' => 'students.enable_nfc', 'display_name' => 'Enable NFC Mobile', 'group' => 'students', 'description' => 'Mengaktifkan NFC mobile'],
            ['name' => 'students.move_class', 'display_name' => 'Pindah Kelas', 'group' => 'students', 'description' => 'Memindahkan siswa ke kelas lain'],
            ['name' => 'students.import', 'display_name' => 'Import Siswa', 'group' => 'students', 'description' => 'Import siswa dari Excel'],
            ['name' => 'students.export', 'display_name' => 'Export Siswa', 'group' => 'students', 'description' => 'Export siswa ke Excel'],
            ['name' => 'students.bulk_operations', 'display_name' => 'Operasi Bulk', 'group' => 'students', 'description' => 'Operasi bulk pada siswa'],

            // Classes Management (8 permissions)
            ['name' => 'classes.view', 'display_name' => 'Lihat Kelas', 'group' => 'classes', 'description' => 'Melihat daftar kelas'],
            ['name' => 'classes.create', 'display_name' => 'Tambah Kelas', 'group' => 'classes', 'description' => 'Membuat kelas baru'],
            ['name' => 'classes.edit', 'display_name' => 'Edit Kelas', 'group' => 'classes', 'description' => 'Mengubah data kelas'],
            ['name' => 'classes.delete', 'display_name' => 'Hapus Kelas', 'group' => 'classes', 'description' => 'Menghapus kelas'],
            ['name' => 'classes.assign_wali', 'display_name' => 'Assign Wali Kelas', 'group' => 'classes', 'description' => 'Menentukan wali kelas'],
            ['name' => 'classes.view_students', 'display_name' => 'Lihat Siswa di Kelas', 'group' => 'classes', 'description' => 'Melihat siswa dalam kelas'],
            ['name' => 'classes.manage_students', 'display_name' => 'Kelola Siswa di Kelas', 'group' => 'classes', 'description' => 'Menambah/menghapus siswa'],
            ['name' => 'classes.view_statistics', 'display_name' => 'Lihat Statistik Kelas', 'group' => 'classes', 'description' => 'Melihat statistik kelas'],

            // Attendance Management (15 permissions)
            ['name' => 'attendance.view_all', 'display_name' => 'Lihat Semua Absensi', 'group' => 'attendance', 'description' => 'Melihat absensi semua siswa'],
            ['name' => 'attendance.view_class', 'display_name' => 'Lihat Absensi Kelas', 'group' => 'attendance', 'description' => 'Melihat absensi siswa di kelas tertentu'],
            ['name' => 'attendance.view_own', 'display_name' => 'Lihat Absensi Sendiri', 'group' => 'attendance', 'description' => 'Siswa lihat absensi sendiri'],
            ['name' => 'attendance.checkin', 'display_name' => 'Check-in', 'group' => 'attendance', 'description' => 'Melakukan check-in'],
            ['name' => 'attendance.checkout', 'display_name' => 'Check-out', 'group' => 'attendance', 'description' => 'Melakukan check-out'],
            ['name' => 'attendance.manual_input', 'display_name' => 'Input Manual', 'group' => 'attendance', 'description' => 'Input absensi manual'],
            ['name' => 'attendance.manual_approve', 'display_name' => 'Approve Manual Attendance', 'group' => 'attendance', 'description' => 'Menyetujui absensi manual'],
            ['name' => 'attendance.manual_reject', 'display_name' => 'Reject Manual Attendance', 'group' => 'attendance', 'description' => 'Menolak absensi manual'],
            ['name' => 'attendance.edit', 'display_name' => 'Edit Absensi', 'group' => 'attendance', 'description' => 'Mengubah data absensi'],
            ['name' => 'attendance.delete', 'display_name' => 'Hapus Absensi', 'group' => 'attendance', 'description' => 'Menghapus data absensi'],
            ['name' => 'attendance.export', 'display_name' => 'Export Absensi', 'group' => 'attendance', 'description' => 'Export absensi ke Excel'],
            ['name' => 'attendance.view_violations', 'display_name' => 'Lihat Pelanggaran', 'group' => 'attendance', 'description' => 'Melihat pelanggaran absensi'],
            ['name' => 'attendance.manage_violations', 'display_name' => 'Kelola Pelanggaran', 'group' => 'attendance', 'description' => 'Mengelola pelanggaran absensi'],
            ['name' => 'attendance.view_anomalies', 'display_name' => 'Lihat Anomali', 'group' => 'attendance', 'description' => 'Melihat anomali absensi'],
            ['name' => 'attendance.resolve_anomalies', 'display_name' => 'Review Anomali', 'group' => 'attendance', 'description' => 'Mereview dan resolve anomali'],

            // Reports (10 permissions)
            ['name' => 'reports.view_all', 'display_name' => 'Lihat Semua Laporan', 'group' => 'reports', 'description' => 'Melihat semua laporan'],
            ['name' => 'reports.view_class', 'display_name' => 'Lihat Laporan Kelas', 'group' => 'reports', 'description' => 'Melihat laporan kelas tertentu'],
            ['name' => 'reports.view_own', 'display_name' => 'Lihat Laporan Pribadi', 'group' => 'reports', 'description' => 'Siswa lihat laporan sendiri'],
            ['name' => 'reports.generate_daily', 'display_name' => 'Generate Laporan Harian', 'group' => 'reports', 'description' => 'Membuat laporan harian'],
            ['name' => 'reports.generate_monthly', 'display_name' => 'Generate Laporan Bulanan', 'group' => 'reports', 'description' => 'Membuat laporan bulanan'],
            ['name' => 'reports.generate_semester', 'display_name' => 'Generate Laporan Semester', 'group' => 'reports', 'description' => 'Membuat laporan semester'],
            ['name' => 'reports.export_pdf', 'display_name' => 'Export PDF', 'group' => 'reports', 'description' => 'Export laporan ke PDF'],
            ['name' => 'reports.export_excel', 'display_name' => 'Export Excel', 'group' => 'reports', 'description' => 'Export laporan ke Excel'],
            ['name' => 'reports.schedule', 'display_name' => 'Jadwal Laporan Otomatis', 'group' => 'reports', 'description' => 'Membuat jadwal laporan otomatis'],
            ['name' => 'reports.delete', 'display_name' => 'Hapus Laporan', 'group' => 'reports', 'description' => 'Menghapus laporan'],

            // Dashboard & Analytics (6 permissions)
            ['name' => 'dashboard.view_admin', 'display_name' => 'Dashboard Admin', 'group' => 'dashboard', 'description' => 'Akses dashboard admin'],
            ['name' => 'dashboard.view_principal', 'display_name' => 'Dashboard Kepala Sekolah', 'group' => 'dashboard', 'description' => 'Dashboard kepala sekolah'],
            ['name' => 'dashboard.view_teacher', 'display_name' => 'Dashboard Wali Kelas', 'group' => 'dashboard', 'description' => 'Dashboard wali kelas'],
            ['name' => 'dashboard.view_student', 'display_name' => 'Dashboard Siswa', 'group' => 'dashboard', 'description' => 'Dashboard siswa'],
            ['name' => 'dashboard.analytics', 'display_name' => 'Analytics', 'group' => 'dashboard', 'description' => 'Akses fitur analytics'],
            ['name' => 'dashboard.realtime', 'display_name' => 'Real-time Monitoring', 'group' => 'dashboard', 'description' => 'Monitoring real-time'],

            // Settings (11 permissions)
            ['name' => 'settings.view', 'display_name' => 'Lihat Pengaturan', 'group' => 'settings', 'description' => 'Melihat pengaturan sistem'],
            ['name' => 'settings.edit_attendance', 'display_name' => 'Edit Pengaturan Absensi', 'group' => 'settings', 'description' => 'Mengubah pengaturan absensi'],
            ['name' => 'settings.edit_time', 'display_name' => 'Edit Jam Operasional', 'group' => 'settings', 'description' => 'Mengubah jam operasional'],
            ['name' => 'settings.edit_geofencing', 'display_name' => 'Edit Geofencing', 'group' => 'settings', 'description' => 'Mengubah setting geofencing'],
            ['name' => 'settings.manage_locations', 'display_name' => 'Kelola Lokasi', 'group' => 'settings', 'description' => 'Mengelola lokasi absensi'],
            ['name' => 'settings.manage_semesters', 'display_name' => 'Kelola Semester', 'group' => 'settings', 'description' => 'Mengelola data semester'],
            ['name' => 'settings.manage_calendar', 'display_name' => 'Kelola Kalender Akademik', 'group' => 'settings', 'description' => 'Mengelola kalender akademik'],
            ['name' => 'settings.manage_departments', 'display_name' => 'Kelola Jurusan', 'group' => 'settings', 'description' => 'Mengelola data jurusan'],
            ['name' => 'settings.backup', 'display_name' => 'Backup Database', 'group' => 'settings', 'description' => 'Melakukan backup database'],
            ['name' => 'settings.restore', 'display_name' => 'Restore Database', 'group' => 'settings', 'description' => 'Restore database dari backup'],
            ['name' => 'settings.system', 'display_name' => 'Pengaturan Sistem', 'group' => 'settings', 'description' => 'Pengaturan sistem lanjutan'],
        ];

        foreach ($permissions as $permission) {
            Permission::create(array_merge($permission, ['is_active' => true]));
        }

        $this->command->info('✓ ' . count($permissions) . ' permissions created successfully');
    }
}
