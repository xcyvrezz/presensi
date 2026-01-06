# ROLE & PERMISSION MATRIX
## Sistem Absensi MIFARE - SMK Negeri 10 Pandeglang

**Versi:** 1.0
**Tanggal:** 13 Desember 2025

---

## 1. ROLE DEFINITIONS

| Role ID | Role Name | Display Name | Deskripsi | User Count (Est.) |
|---------|-----------|--------------|-----------|-------------------|
| 1 | admin | Administrator | Mengelola seluruh sistem, data master, dan konfigurasi | 2-5 |
| 2 | kepala_sekolah | Kepala Sekolah | Mengawasi dan melihat statistik kehadiran seluruh sekolah | 1 |
| 3 | wali_kelas | Wali Kelas | Mengelola kehadiran siswa di kelas yang diampu | 30-50 |
| 4 | siswa | Siswa | Melakukan absensi dan melihat data pribadi | 1500+ |

---

## 2. PERMISSION GROUPS & LIST

### 2.1 Permission Group: DASHBOARD
| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-DASH-001 | dashboard.view_admin | Lihat Dashboard Admin | Akses dashboard admin dengan statistik real-time |
| P-DASH-002 | dashboard.view_kepala_sekolah | Lihat Dashboard Kepala Sekolah | Akses dashboard dengan statistik sekolah |
| P-DASH-003 | dashboard.view_wali_kelas | Lihat Dashboard Wali Kelas | Akses dashboard dengan data kelas |
| P-DASH-004 | dashboard.view_siswa | Lihat Dashboard Siswa | Akses dashboard pribadi siswa |

### 2.2 Permission Group: USERS (Manajemen Pengguna)
| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-USER-001 | users.view | Lihat Daftar Pengguna | Melihat list semua users (admin, guru, kepala sekolah) |
| P-USER-002 | users.create | Tambah Pengguna | Membuat user baru |
| P-USER-003 | users.edit | Edit Pengguna | Mengubah data user |
| P-USER-004 | users.delete | Hapus Pengguna | Menghapus user (soft delete) |
| P-USER-005 | users.assign_role | Assign Role | Memberikan/mengubah role user |

### 2.3 Permission Group: STUDENTS (Manajemen Siswa)
| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-STD-001 | students.view_all | Lihat Semua Siswa | Melihat semua siswa di sekolah |
| P-STD-002 | students.view_own_class | Lihat Siswa Kelas Sendiri | Wali kelas lihat siswa di kelasnya |
| P-STD-003 | students.create | Tambah Siswa | Menambah siswa baru |
| P-STD-004 | students.edit | Edit Siswa | Mengubah data siswa |
| P-STD-005 | students.delete | Hapus Siswa | Menghapus siswa (soft delete) |
| P-STD-006 | students.import | Import Siswa | Import bulk siswa via Excel |
| P-STD-007 | students.export | Export Siswa | Export data siswa ke Excel/PDF |
| P-STD-008 | students.assign_card | Assign Kartu MIFARE | Assign card UID ke siswa |

### 2.4 Permission Group: CLASSES (Manajemen Kelas)
| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-CLS-001 | classes.view | Lihat Daftar Kelas | Melihat semua kelas |
| P-CLS-002 | classes.create | Tambah Kelas | Membuat kelas baru |
| P-CLS-003 | classes.edit | Edit Kelas | Mengubah data kelas |
| P-CLS-004 | classes.delete | Hapus Kelas | Menghapus kelas |
| P-CLS-005 | classes.assign_homeroom | Assign Wali Kelas | Menentukan wali kelas |

### 2.5 Permission Group: DEPARTMENTS (Manajemen Jurusan)
| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-DEPT-001 | departments.view | Lihat Jurusan | Melihat daftar jurusan |
| P-DEPT-002 | departments.edit | Edit Jurusan | Mengubah data jurusan |

### 2.6 Permission Group: ATTENDANCES (Manajemen Absensi)
| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-ATT-001 | attendances.check_in | Absen Datang | Melakukan absensi datang (siswa via tap) |
| P-ATT-002 | attendances.check_out | Absen Pulang | Melakukan absensi pulang (siswa via tap) |
| P-ATT-003 | attendances.view_all | Lihat Semua Absensi | Melihat absensi seluruh sekolah |
| P-ATT-004 | attendances.view_own_class | Lihat Absensi Kelas Sendiri | Wali kelas lihat absensi kelasnya |
| P-ATT-005 | attendances.view_own | Lihat Absensi Pribadi | Siswa lihat absensi sendiri |
| P-ATT-006 | attendances.manual_input | Input Manual | Input izin/sakit/dispensasi |
| P-ATT-007 | attendances.edit | Edit Absensi | Koreksi data absensi (admin) |
| P-ATT-008 | attendances.delete | Hapus Absensi | Hapus record absensi (admin) |
| P-ATT-009 | attendances.export | Export Absensi | Export data absensi |

### 2.7 Permission Group: ATTENDANCE_LOCATIONS (Manajemen Lokasi Absensi) **[NEW]**
| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-LOC-001 | locations.view | Lihat Lokasi Absensi | Melihat daftar lokasi geofencing |
| P-LOC-002 | locations.create | Tambah Lokasi | Menambah titik lokasi absensi baru |
| P-LOC-003 | locations.edit | Edit Lokasi | Mengubah koordinat/radius lokasi |
| P-LOC-004 | locations.delete | Hapus Lokasi | Menghapus lokasi |
| P-LOC-005 | locations.test | Test Lokasi | Test validasi geofencing |

### 2.8 Permission Group: REPORTS (Laporan)
| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-RPT-001 | reports.view_all | Lihat Semua Laporan | Akses semua jenis laporan |
| P-RPT-002 | reports.view_own_class | Lihat Laporan Kelas | Laporan kelas yang diampu |
| P-RPT-003 | reports.view_own | Lihat Laporan Pribadi | Laporan kehadiran pribadi |
| P-RPT-004 | reports.export_pdf | Export PDF | Export laporan ke PDF |
| P-RPT-005 | reports.export_excel | Export Excel | Export laporan ke Excel |
| P-RPT-006 | reports.statistics | Lihat Statistik | Akses analisis & statistik |
| P-RPT-007 | reports.top_classes | Lihat Top 10 Kelas | Akses ranking kelas |

### 2.9 Permission Group: CALENDAR (Kalender Akademik)
| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-CAL-001 | calendar.view | Lihat Kalender | Melihat kalender akademik |
| P-CAL-002 | calendar.create | Tambah Event | Menambah libur/acara |
| P-CAL-003 | calendar.edit | Edit Event | Mengubah event |
| P-CAL-004 | calendar.delete | Hapus Event | Menghapus event |

### 2.10 Permission Group: SEMESTERS (Manajemen Semester)
| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-SEM-001 | semesters.view | Lihat Semester | Melihat daftar semester |
| P-SEM-002 | semesters.create | Tambah Semester | Membuat semester baru |
| P-SEM-003 | semesters.edit | Edit Semester | Mengubah data semester |
| P-SEM-004 | semesters.close | Tutup Semester | Menutup semester (archive) |
| P-SEM-005 | semesters.activate | Aktifkan Semester | Set semester aktif |

### 2.11 Permission Group: SETTINGS (Pengaturan Sistem)
| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-SET-001 | settings.view | Lihat Pengaturan | Melihat konfigurasi sistem |
| P-SET-002 | settings.edit | Edit Pengaturan | Mengubah konfigurasi (jam absensi, dll) |
| P-SET-003 | settings.notification | Kelola Notifikasi | Setting notifikasi WhatsApp |

### 2.12 Permission Group: AUDIT (Audit Trail)
| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-AUD-001 | audit.view | Lihat Audit Log | Melihat history perubahan data |
| P-AUD-002 | audit.export | Export Audit Log | Export audit trail |

---

## 3. ROLE-PERMISSION MAPPING

### 3.1 Role: ADMIN (Administrator)

**Full Access** - Administrator memiliki akses ke semua permission:

| Group | Permissions | Notes |
|-------|-------------|-------|
| Dashboard | P-DASH-001 | Dashboard admin dengan full statistics |
| Users | P-USER-001 s/d P-USER-005 | Full CRUD users |
| Students | P-STD-001, P-STD-003 s/d P-STD-008 | Full CRUD siswa + import/export |
| Classes | P-CLS-001 s/d P-CLS-005 | Full CRUD kelas |
| Departments | P-DEPT-001, P-DEPT-002 | Manage jurusan |
| Attendances | P-ATT-003, P-ATT-006 s/d P-ATT-009 | View all + manual input + edit/delete |
| Locations | P-LOC-001 s/d P-LOC-005 | Full CRUD lokasi geofencing |
| Reports | P-RPT-001, P-RPT-004 s/d P-RPT-007 | All reports + export + statistics |
| Calendar | P-CAL-001 s/d P-CAL-004 | Full CRUD calendar |
| Semesters | P-SEM-001 s/d P-SEM-005 | Full manage semester |
| Settings | P-SET-001 s/d P-SET-003 | Full system settings |
| Audit | P-AUD-001, P-AUD-002 | View & export audit logs |

**Total Permissions:** 48

---

### 3.2 Role: KEPALA SEKOLAH (Kepala Sekolah)

**Read-Only Access** - Fokus pada monitoring dan statistik:

| Group | Permissions | Notes |
|-------|-------------|-------|
| Dashboard | P-DASH-002 | Dashboard kepala sekolah |
| Students | P-STD-001, P-STD-007 | View all siswa + export |
| Classes | P-CLS-001 | View kelas |
| Departments | P-DEPT-001 | View jurusan |
| Attendances | P-ATT-003, P-ATT-009 | View all absensi + export |
| Locations | P-LOC-001 | View lokasi absensi |
| Reports | P-RPT-001, P-RPT-004 s/d P-RPT-007 | All reports, statistics, top 10 |
| Calendar | P-CAL-001 | View calendar |
| Semesters | P-SEM-001 | View semester |

**Total Permissions:** 13

**Business Logic:**
- Kepala sekolah hanya dapat melihat, tidak dapat mengubah data
- Fokus pada dashboard, laporan, dan analisis
- Dapat export laporan untuk presentasi/rapat

---

### 3.3 Role: WALI KELAS (Wali Kelas)

**Limited CRUD** - Akses terbatas pada kelas yang diampu:

| Group | Permissions | Notes |
|-------|-------------|-------|
| Dashboard | P-DASH-003 | Dashboard wali kelas |
| Students | P-STD-002, P-STD-007 | View siswa di kelas sendiri + export |
| Classes | P-CLS-001 | View kelas (readonly) |
| Departments | P-DEPT-001 | View jurusan |
| Attendances | P-ATT-004, P-ATT-006, P-ATT-009 | View kelas sendiri + manual input (izin/sakit) + export |
| Locations | P-LOC-001 | View lokasi |
| Reports | P-RPT-002, P-RPT-004, P-RPT-005 | Laporan kelas sendiri + export |
| Calendar | P-CAL-001 | View calendar |
| Semesters | P-SEM-001 | View semester aktif |

**Total Permissions:** 11

**Business Logic:**
- Wali kelas hanya dapat akses data siswa di kelasnya
- Dapat input manual attendance (izin, sakit, dispensasi) untuk siswa kelasnya
- Dapat export laporan kelasnya
- Backend harus validasi: user hanya bisa akses kelas yang di-assign ke dia (via classes.homeroom_teacher_id)

**Middleware Check Example:**
```php
// Laravel middleware untuk validasi wali kelas
if ($user->role->name === 'wali_kelas') {
    $allowedClassIds = $user->homeroomClasses()->pluck('id')->toArray();
    if (!in_array($requestedClassId, $allowedClassIds)) {
        abort(403, 'Anda tidak memiliki akses ke kelas ini');
    }
}
```

---

### 3.4 Role: SISWA (Siswa)

**Self-Service** - Akses sangat terbatas, hanya data pribadi:

| Group | Permissions | Notes |
|-------|-------------|-------|
| Dashboard | P-DASH-004 | Dashboard pribadi |
| Attendances | P-ATT-001, P-ATT-002, P-ATT-005 | Tap absen datang/pulang + view own |
| Reports | P-RPT-003 | View laporan pribadi |
| Calendar | P-CAL-001 | View calendar (untuk tahu libur) |

**Total Permissions:** 5

**Business Logic:**
- Siswa hanya dapat tap untuk absen (via kartu atau mobile)
- Siswa hanya dapat lihat data absensi pribadi
- Backend harus validasi: student_id sesuai dengan user yang login
- Mobile app sudah enforce ini di frontend, tapi backend tetap harus validate

**Middleware Check Example:**
```php
// Laravel middleware untuk validasi siswa
if ($user->role->name === 'siswa') {
    $student = $user->student;  // relation
    if ($requestedStudentId != $student->id) {
        abort(403, 'Anda hanya dapat akses data pribadi');
    }
}
```

---

## 4. PERMISSION MATRIX TABLE

### 4.1 Complete Matrix

| Permission | Admin | Kepala Sekolah | Wali Kelas | Siswa |
|------------|:-----:|:--------------:|:----------:|:-----:|
| **DASHBOARD** |
| dashboard.view_admin | ✅ | ❌ | ❌ | ❌ |
| dashboard.view_kepala_sekolah | ❌ | ✅ | ❌ | ❌ |
| dashboard.view_wali_kelas | ❌ | ❌ | ✅ | ❌ |
| dashboard.view_siswa | ❌ | ❌ | ❌ | ✅ |
| **USERS** |
| users.view | ✅ | ❌ | ❌ | ❌ |
| users.create | ✅ | ❌ | ❌ | ❌ |
| users.edit | ✅ | ❌ | ❌ | ❌ |
| users.delete | ✅ | ❌ | ❌ | ❌ |
| users.assign_role | ✅ | ❌ | ❌ | ❌ |
| **STUDENTS** |
| students.view_all | ✅ | ✅ | ❌ | ❌ |
| students.view_own_class | ❌ | ❌ | ✅ | ❌ |
| students.create | ✅ | ❌ | ❌ | ❌ |
| students.edit | ✅ | ❌ | ❌ | ❌ |
| students.delete | ✅ | ❌ | ❌ | ❌ |
| students.import | ✅ | ❌ | ❌ | ❌ |
| students.export | ✅ | ✅ | ✅ | ❌ |
| students.assign_card | ✅ | ❌ | ❌ | ❌ |
| **CLASSES** |
| classes.view | ✅ | ✅ | ✅ | ❌ |
| classes.create | ✅ | ❌ | ❌ | ❌ |
| classes.edit | ✅ | ❌ | ❌ | ❌ |
| classes.delete | ✅ | ❌ | ❌ | ❌ |
| classes.assign_homeroom | ✅ | ❌ | ❌ | ❌ |
| **DEPARTMENTS** |
| departments.view | ✅ | ✅ | ✅ | ❌ |
| departments.edit | ✅ | ❌ | ❌ | ❌ |
| **ATTENDANCES** |
| attendances.check_in | ❌ | ❌ | ❌ | ✅ |
| attendances.check_out | ❌ | ❌ | ❌ | ✅ |
| attendances.view_all | ✅ | ✅ | ❌ | ❌ |
| attendances.view_own_class | ❌ | ❌ | ✅ | ❌ |
| attendances.view_own | ❌ | ❌ | ❌ | ✅ |
| attendances.manual_input | ✅ | ❌ | ✅ | ❌ |
| attendances.edit | ✅ | ❌ | ❌ | ❌ |
| attendances.delete | ✅ | ❌ | ❌ | ❌ |
| attendances.export | ✅ | ✅ | ✅ | ❌ |
| **LOCATIONS** (NEW) |
| locations.view | ✅ | ✅ | ✅ | ❌ |
| locations.create | ✅ | ❌ | ❌ | ❌ |
| locations.edit | ✅ | ❌ | ❌ | ❌ |
| locations.delete | ✅ | ❌ | ❌ | ❌ |
| locations.test | ✅ | ❌ | ❌ | ❌ |
| **REPORTS** |
| reports.view_all | ✅ | ✅ | ❌ | ❌ |
| reports.view_own_class | ❌ | ❌ | ✅ | ❌ |
| reports.view_own | ❌ | ❌ | ❌ | ✅ |
| reports.export_pdf | ✅ | ✅ | ✅ | ❌ |
| reports.export_excel | ✅ | ✅ | ✅ | ❌ |
| reports.statistics | ✅ | ✅ | ❌ | ❌ |
| reports.top_classes | ✅ | ✅ | ❌ | ❌ |
| **CALENDAR** |
| calendar.view | ✅ | ✅ | ✅ | ✅ |
| calendar.create | ✅ | ❌ | ❌ | ❌ |
| calendar.edit | ✅ | ❌ | ❌ | ❌ |
| calendar.delete | ✅ | ❌ | ❌ | ❌ |
| **SEMESTERS** |
| semesters.view | ✅ | ✅ | ✅ | ❌ |
| semesters.create | ✅ | ❌ | ❌ | ❌ |
| semesters.edit | ✅ | ❌ | ❌ | ❌ |
| semesters.close | ✅ | ❌ | ❌ | ❌ |
| semesters.activate | ✅ | ❌ | ❌ | ❌ |
| **SETTINGS** |
| settings.view | ✅ | ❌ | ❌ | ❌ |
| settings.edit | ✅ | ❌ | ❌ | ❌ |
| settings.notification | ✅ | ❌ | ❌ | ❌ |
| **AUDIT** |
| audit.view | ✅ | ❌ | ❌ | ❌ |
| audit.export | ✅ | ❌ | ❌ | ❌ |
| **TOTAL** | **48** | **13** | **11** | **5** |

---

## 5. IMPLEMENTATION GUIDE (Laravel)

### 5.1 Database Seeder

```php
// database/seeders/RoleSeeder.php
public function run()
{
    $roles = [
        ['name' => 'admin', 'display_name' => 'Administrator'],
        ['name' => 'kepala_sekolah', 'display_name' => 'Kepala Sekolah'],
        ['name' => 'wali_kelas', 'display_name' => 'Wali Kelas'],
        ['name' => 'siswa', 'display_name' => 'Siswa'],
    ];

    foreach ($roles as $role) {
        Role::create($role);
    }
}

// database/seeders/PermissionSeeder.php
public function run()
{
    $permissions = [
        // Dashboard
        ['name' => 'dashboard.view_admin', 'display_name' => 'Lihat Dashboard Admin', 'group' => 'dashboard'],
        ['name' => 'dashboard.view_kepala_sekolah', 'display_name' => 'Lihat Dashboard Kepala Sekolah', 'group' => 'dashboard'],
        // ... dst (semua 60+ permissions)
    ];

    foreach ($permissions as $perm) {
        Permission::create($perm);
    }
}
```

### 5.2 Middleware Check

```php
// app/Http/Middleware/CheckPermission.php
public function handle($request, Closure $next, $permission)
{
    if (!auth()->user()->can($permission)) {
        abort(403, 'Anda tidak memiliki akses ke fitur ini');
    }

    return $next($request);
}

// Route usage:
Route::get('/admin/users', [UserController::class, 'index'])
    ->middleware('permission:users.view');
```

### 5.3 Blade Directive

```php
// app/Providers/AuthServiceProvider.php
Blade::if('role', function ($role) {
    return auth()->user()->role->name === $role;
});

// Usage in blade:
@role('admin')
    <a href="/admin/users">Kelola Pengguna</a>
@endrole
```

---

## 6. SECURITY CONSIDERATIONS

### 6.1 Permission Escalation Prevention
- NEVER allow users to change their own role
- Admin cannot delete themselves
- Role changes require super admin approval (optional)

### 6.2 Data Isolation
- Wali kelas: Enforce `class_id` filter di query
- Siswa: Enforce `student_id` filter di query
- Use Laravel Policy untuk centralized authorization logic

### 6.3 Audit Logging
- Log semua permission changes
- Log role assignments
- Track who gave permission to whom

---

**Document Control:**
- **Author:** Security Architect
- **Reviewer:** -
- **Approved by:** -

