# DATABASE DESIGN & ERD
## Sistem Absensi MIFARE - SMK Negeri 10 Pandeglang

**Versi:** 1.1
**Tanggal:** 13 Desember 2025
**Status:** Updated with Mobile NFC & Geofencing

---

## 1. DAFTAR TABEL

| No | Nama Tabel | Deskripsi | Estimasi Records |
|----|------------|-----------|------------------|
| 1 | users | Data pengguna sistem (Admin, Kepala Sekolah, Wali Kelas, Siswa) | 2000 |
| 2 | roles | Master role | 4 |
| 3 | permissions | Master permission | 50 |
| 4 | role_permission | Mapping role ke permission (many-to-many) | 200 |
| 5 | departments | Jurusan (PPLG, AKL, TO) | 3 |
| 6 | classes | Kelas (X PPLG 1, XI AKL 2, dll) | 30-50 |
| 7 | students | Data siswa | 1500 |
| 8 | semesters | Semester akademik | 10 |
| 9 | academic_calendars | Kalender akademik (libur, acara) | 100/tahun |
| 10 | **attendance_locations** | **[NEW]** Titik lokasi absensi (geofencing) | 5-10 |
| 11 | attendances | Transaksi absensi (datang & pulang) - UPDATED | 500k/tahun |
| 12 | manual_attendances | Kehadiran manual (izin, sakit, dispensasi) | 50k/tahun |
| 13 | attendance_settings | Konfigurasi jam operasional absensi | 10 |
| 14 | notifications | Log notifikasi | 100k/tahun |
| 15 | audit_logs | Audit trail perubahan data | 200k/tahun |

---

## 2. DETAIL STRUKTUR TABEL

### 2.1 Table: `users`
**Deskripsi:** Menyimpan semua pengguna sistem kecuali siswa

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID pengguna |
| role_id | BIGINT UNSIGNED | - | NO | - | INDEX | FK ke roles |
| name | VARCHAR | 255 | NO | - | - | Nama lengkap |
| email | VARCHAR | 255 | NO | - | UNIQUE | Email (untuk login) |
| username | VARCHAR | 100 | NO | - | UNIQUE | Username (untuk login) |
| password | VARCHAR | 255 | NO | - | - | Password (hashed bcrypt) |
| phone | VARCHAR | 20 | YES | NULL | - | Nomor telepon/WA |
| photo | VARCHAR | 255 | YES | NULL | - | Path foto profil |
| is_active | BOOLEAN | - | NO | TRUE | - | Status aktif |
| last_login_at | TIMESTAMP | - | YES | NULL | - | Waktu login terakhir |
| remember_token | VARCHAR | 100 | YES | NULL | - | Token remember me |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (email)
- UNIQUE KEY (username)
- INDEX (role_id)

**Relations:**
- belongsTo: roles

---

### 2.2 Table: `roles`
**Deskripsi:** Master role pengguna

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID role |
| name | VARCHAR | 50 | NO | - | UNIQUE | Nama role (admin, kepala_sekolah, wali_kelas, siswa) |
| display_name | VARCHAR | 100 | NO | - | - | Nama tampilan (Admin, Kepala Sekolah, dll) |
| description | TEXT | - | YES | NULL | - | Deskripsi role |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (name)

**Data Master:**
```sql
INSERT INTO roles (name, display_name, description) VALUES
('admin', 'Administrator', 'Pengelola sistem dan data master'),
('kepala_sekolah', 'Kepala Sekolah', 'Melihat statistik dan dashboard'),
('wali_kelas', 'Wali Kelas', 'Mengelola kehadiran siswa di kelasnya'),
('siswa', 'Siswa', 'Melihat kehadiran pribadi');
```

---

### 2.3 Table: `permissions`
**Deskripsi:** Master permission/hak akses

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID permission |
| name | VARCHAR | 100 | NO | - | UNIQUE | Nama permission (contoh: students.create) |
| display_name | VARCHAR | 150 | NO | - | - | Nama tampilan |
| group | VARCHAR | 50 | NO | - | INDEX | Grup (students, attendances, reports, dll) |
| description | TEXT | - | YES | NULL | - | Deskripsi |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (name)
- INDEX (group)

**Permission Groups:**
- `students`: Manajemen siswa
- `users`: Manajemen pengguna
- `classes`: Manajemen kelas
- `attendances`: Manajemen absensi
- `reports`: Akses laporan
- `calendar`: Manajemen kalender
- `dashboard`: Akses dashboard
- `settings`: Pengaturan sistem

---

### 2.4 Table: `role_permission`
**Deskripsi:** Mapping role ke permission (many-to-many)

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID |
| role_id | BIGINT UNSIGNED | - | NO | - | INDEX | FK ke roles |
| permission_id | BIGINT UNSIGNED | - | NO | - | INDEX | FK ke permissions |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (role_id, permission_id)
- INDEX (role_id)
- INDEX (permission_id)

**Relations:**
- belongsTo: roles
- belongsTo: permissions

---

### 2.5 Table: `departments`
**Deskripsi:** Master jurusan/program keahlian

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID jurusan |
| code | VARCHAR | 10 | NO | - | UNIQUE | Kode (PPLG, AKL, TO) |
| name | VARCHAR | 255 | NO | - | - | Nama lengkap |
| description | TEXT | - | YES | NULL | - | Deskripsi |
| is_active | BOOLEAN | - | NO | TRUE | - | Status aktif |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)

**Data Master:**
```sql
INSERT INTO departments (code, name, description) VALUES
('PPLG', 'Pengembangan Perangkat Lunak dan Gim', 'Program keahlian di bidang software development'),
('AKL', 'Akuntansi dan Keuangan Lembaga', 'Program keahlian di bidang akuntansi'),
('TO', 'Teknik Otomotif', 'Program keahlian di bidang otomotif');
```

---

### 2.6 Table: `classes`
**Deskripsi:** Data kelas

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID kelas |
| department_id | BIGINT UNSIGNED | - | NO | - | INDEX | FK ke departments |
| homeroom_teacher_id | BIGINT UNSIGNED | - | YES | NULL | INDEX | FK ke users (wali kelas) |
| name | VARCHAR | 100 | NO | - | - | Nama kelas (X PPLG 1) |
| grade_level | ENUM | - | NO | - | INDEX | Tingkat (X, XI, XII) |
| academic_year | VARCHAR | 20 | NO | - | INDEX | Tahun ajaran (2025/2026) |
| capacity | INT UNSIGNED | - | NO | 36 | - | Kapasitas maksimal |
| is_active | BOOLEAN | - | NO | TRUE | - | Status aktif |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (department_id)
- INDEX (homeroom_teacher_id)
- INDEX (grade_level)
- INDEX (academic_year)

**Relations:**
- belongsTo: departments
- belongsTo: users (as homeroom_teacher)
- hasMany: students

**Enum Values:**
- grade_level: ('X', 'XI', 'XII')

---

### 2.7 Table: `students`
**Deskripsi:** Data siswa (extended dari users)

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID siswa |
| class_id | BIGINT UNSIGNED | - | NO | - | INDEX | FK ke classes |
| nis | VARCHAR | 20 | NO | - | UNIQUE | Nomor Induk Siswa |
| nisn | VARCHAR | 20 | NO | - | UNIQUE | Nomor Induk Siswa Nasional |
| name | VARCHAR | 255 | NO | - | INDEX | Nama lengkap |
| card_uid | VARCHAR | 50 | YES | NULL | UNIQUE | UID kartu MIFARE |
| phone | VARCHAR | 20 | YES | NULL | - | Nomor telepon/WA siswa |
| parent_phone | VARCHAR | 20 | YES | NULL | - | Nomor telepon/WA orang tua |
| email | VARCHAR | 255 | YES | NULL | UNIQUE | Email siswa |
| gender | ENUM | - | NO | - | - | Jenis kelamin (L, P) |
| birth_date | DATE | - | YES | NULL | - | Tanggal lahir |
| address | TEXT | - | YES | NULL | - | Alamat |
| photo | VARCHAR | 255 | YES | NULL | - | Path foto |
| is_active | BOOLEAN | - | NO | TRUE | INDEX | Status aktif |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (nis)
- UNIQUE KEY (nisn)
- UNIQUE KEY (card_uid)
- UNIQUE KEY (email)
- INDEX (class_id)
- INDEX (name)
- INDEX (is_active)

**Relations:**
- belongsTo: classes
- hasMany: attendances
- hasMany: manual_attendances

**Enum Values:**
- gender: ('L', 'P')

---

### 2.8 Table: `semesters`
**Deskripsi:** Semester akademik

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID semester |
| name | VARCHAR | 100 | NO | - | UNIQUE | Nama (Ganjil 2025/2026) |
| type | ENUM | - | NO | - | - | Jenis (Ganjil, Genap) |
| academic_year | VARCHAR | 20 | NO | - | INDEX | Tahun ajaran (2025/2026) |
| start_date | DATE | - | NO | - | - | Tanggal mulai |
| end_date | DATE | - | NO | - | - | Tanggal selesai |
| is_active | BOOLEAN | - | NO | FALSE | INDEX | Status aktif (hanya 1 aktif) |
| is_closed | BOOLEAN | - | NO | FALSE | - | Sudah ditutup (archived) |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (name)
- INDEX (academic_year)
- INDEX (is_active)

**Relations:**
- hasMany: attendances
- hasMany: manual_attendances

**Enum Values:**
- type: ('Ganjil', 'Genap')

**Business Rules:**
- Hanya 1 semester yang bisa is_active = TRUE
- Semester yang is_closed = TRUE tidak bisa diedit

---

### 2.9 Table: `academic_calendars`
**Deskripsi:** Kalender akademik (libur, acara)

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID kalender |
| title | VARCHAR | 255 | NO | - | - | Judul acara/libur |
| type | ENUM | - | NO | - | INDEX | Jenis |
| start_date | DATE | - | NO | - | INDEX | Tanggal mulai |
| end_date | DATE | - | NO | - | INDEX | Tanggal selesai |
| description | TEXT | - | YES | NULL | - | Deskripsi |
| is_holiday | BOOLEAN | - | NO | TRUE | INDEX | Apakah libur |
| color | VARCHAR | 7 | YES | '#FF0000' | - | Warna di kalender (hex) |
| created_by | BIGINT UNSIGNED | - | YES | NULL | - | FK ke users |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (type)
- INDEX (start_date, end_date)
- INDEX (is_holiday)

**Relations:**
- belongsTo: users (as creator)

**Enum Values:**
- type: ('libur_nasional', 'libur_semester', 'ujian', 'acara_sekolah', 'lainnya')

---

### 2.10 Table: `attendance_locations`
**Deskripsi:** **[NEW]** Titik lokasi yang diperbolehkan untuk absensi (geofencing)

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID lokasi |
| name | VARCHAR | 100 | NO | - | - | Nama lokasi (Gerbang Utama, dll) |
| latitude | DECIMAL | 10,8 | NO | - | INDEX | Latitude GPS |
| longitude | DECIMAL | 11,8 | NO | - | INDEX | Longitude GPS |
| radius | INT UNSIGNED | - | NO | 15 | - | Radius validasi (meter) |
| description | TEXT | - | YES | NULL | - | Deskripsi lokasi |
| is_active | BOOLEAN | - | NO | TRUE | INDEX | Status aktif |
| created_by | BIGINT UNSIGNED | - | YES | NULL | - | FK ke users (admin) |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (latitude, longitude)
- INDEX (is_active)

**Relations:**
- belongsTo: users (as creator)

**Business Rules:**
- Radius default: 15 meter
- Radius minimum: 5 meter
- Radius maksimum: 50 meter
- Precision GPS: 8 digit decimal untuk latitude, 8 digit untuk longitude
- Contoh data:
  ```sql
  INSERT INTO attendance_locations (name, latitude, longitude, radius, description) VALUES
  ('Gerbang Utama', -6.2345678, 106.1234567, 15, 'Gerbang utama SMK Negeri 10 Pandeglang'),
  ('Ruang Guru', -6.2345678, 106.1234567, 10, 'Area ruang guru'),
  ('Lapangan Upacara', -6.2345678, 106.1234567, 20, 'Lapangan untuk upacara');
  ```

**Validation:**
- Tidak boleh ada 2 lokasi dengan koordinat yang sama persis
- Nama lokasi harus unique

---

### 2.11 Table: `attendances`
**Deskripsi:** Transaksi absensi tapping (datang & pulang) - **UPDATED untuk Mobile NFC**

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID absensi |
| student_id | BIGINT UNSIGNED | - | NO | - | INDEX | FK ke students |
| semester_id | BIGINT UNSIGNED | - | NO | - | INDEX | FK ke semesters |
| date | DATE | - | NO | - | INDEX | Tanggal absensi |
| check_in_time | TIME | - | YES | NULL | - | Jam tap datang |
| check_in_status | ENUM | - | YES | NULL | - | Status datang |
| check_in_late_minutes | INT UNSIGNED | - | YES | 0 | - | Menit terlambat |
| **check_in_method** | **ENUM** | - | **YES** | **NULL** | **INDEX** | **[NEW] Metode absensi datang** |
| **check_in_latitude** | **DECIMAL** | **10,8** | **YES** | **NULL** | - | **[NEW] GPS latitude saat datang** |
| **check_in_longitude** | **DECIMAL** | **11,8** | **YES** | **NULL** | - | **[NEW] GPS longitude saat datang** |
| **check_in_location_id** | **BIGINT UNSIGNED** | - | **YES** | **NULL** | **INDEX** | **[NEW] FK ke attendance_locations** |
| **check_in_distance** | **DECIMAL** | **6,2** | **YES** | **NULL** | - | **[NEW] Jarak dari lokasi (meter)** |
| **check_in_device** | **VARCHAR** | **255** | **YES** | **NULL** | - | **[NEW] Info device (mobile)** |
| check_out_time | TIME | - | YES | NULL | - | Jam tap pulang |
| check_out_status | ENUM | - | YES | NULL | - | Status pulang |
| check_out_early_minutes | INT UNSIGNED | - | YES | 0 | - | Menit pulang cepat |
| **check_out_method** | **ENUM** | - | **YES** | **NULL** | **INDEX** | **[NEW] Metode absensi pulang** |
| **check_out_latitude** | **DECIMAL** | **10,8** | **YES** | **NULL** | - | **[NEW] GPS latitude saat pulang** |
| **check_out_longitude** | **DECIMAL** | **11,8** | **YES** | **NULL** | - | **[NEW] GPS longitude saat pulang** |
| **check_out_location_id** | **BIGINT UNSIGNED** | - | **YES** | **NULL** | **INDEX** | **[NEW] FK ke attendance_locations** |
| **check_out_distance** | **DECIMAL** | **6,2** | **YES** | **NULL** | - | **[NEW] Jarak dari lokasi (meter)** |
| **check_out_device** | **VARCHAR** | **255** | **YES** | **NULL** | - | **[NEW] Info device (mobile)** |
| is_complete | BOOLEAN | - | NO | FALSE | INDEX | Sudah tap datang & pulang |
| notes | TEXT | - | YES | NULL | - | Catatan |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (student_id)
- INDEX (semester_id)
- INDEX (date)
- INDEX (is_complete)
- **INDEX (check_in_method)** -- **[NEW]** untuk analisis metode absensi
- **INDEX (check_out_method)** -- **[NEW]**
- **INDEX (check_in_location_id)** -- **[NEW]**
- **INDEX (check_out_location_id)** -- **[NEW]**
- UNIQUE KEY (student_id, date) -- 1 siswa 1 record per hari

**Relations:**
- belongsTo: students
- belongsTo: semesters
- **belongsTo: attendance_locations (as check_in_location)** -- **[NEW]**
- **belongsTo: attendance_locations (as check_out_location)** -- **[NEW]**

**Enum Values:**
- check_in_status: ('tepat_waktu', 'terlambat')
- check_out_status: ('normal', 'pulang_cepat')
- **check_in_method: ('card', 'mobile')** -- **[NEW]**
- **check_out_method: ('card', 'mobile')** -- **[NEW]**

**Field Details:**
- **check_in_device / check_out_device:** Format JSON string
  ```json
  {
    "model": "Samsung Galaxy S21",
    "os": "Android 12",
    "browser": "Chrome 96",
    "nfc_type": "HCE" // Host Card Emulation
  }
  ```
- **check_in_distance / check_out_distance:** Dalam satuan meter dengan 2 desimal
- **Latitude:** -90 sampai 90, precision 8 desimal (~1.1 mm)
- **Longitude:** -180 sampai 180, precision 8 desimal

**Business Logic:**
- check_in_late_minutes: Diisi jika check_in_time > 07:30
- check_out_early_minutes: Diisi jika check_out_time < 15:30
- is_complete: TRUE jika sudah tap datang DAN pulang

---

### 2.12 Table: `manual_attendances`
**Deskripsi:** Kehadiran manual (izin, sakit, dispensasi) oleh wali kelas

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID |
| student_id | BIGINT UNSIGNED | - | NO | - | INDEX | FK ke students |
| semester_id | BIGINT UNSIGNED | - | NO | - | INDEX | FK ke semesters |
| date | DATE | - | NO | - | INDEX | Tanggal |
| type | ENUM | - | NO | - | INDEX | Jenis (izin, sakit, dispensasi, alpha) |
| reason | TEXT | - | YES | NULL | - | Alasan/keterangan |
| attachment | VARCHAR | 255 | YES | NULL | - | Path file lampiran (surat) |
| created_by | BIGINT UNSIGNED | - | NO | - | INDEX | FK ke users (wali kelas) |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (student_id)
- INDEX (semester_id)
- INDEX (date)
- INDEX (type)
- INDEX (created_by)
- UNIQUE KEY (student_id, date) -- Tidak boleh duplikat

**Relations:**
- belongsTo: students
- belongsTo: semesters
- belongsTo: users (as creator)

**Enum Values:**
- type: ('izin', 'sakit', 'dispensasi', 'alpha')

**Business Rules:**
- 'alpha' biasanya diisi otomatis sistem untuk siswa yang tidak tap dan tidak ada keterangan
- Tidak boleh bentrok dengan attendance (tapping)

---

### 2.13 Table: `attendance_settings`
**Deskripsi:** Konfigurasi jam operasional dan setting absensi

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID |
| key | VARCHAR | 100 | NO | - | UNIQUE | Key setting |
| value | VARCHAR | 255 | NO | - | - | Value |
| type | ENUM | - | NO | 'string' | - | Tipe data |
| group | VARCHAR | 50 | NO | - | INDEX | Grup setting |
| description | TEXT | - | YES | NULL | - | Deskripsi |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (key)
- INDEX (group)

**Enum Values:**
- type: ('string', 'integer', 'time', 'boolean')

**Data Master:**
```sql
INSERT INTO attendance_settings (key, value, type, group, description) VALUES
('check_in_start', '06:00:00', 'time', 'schedule', 'Jam mulai bisa tap datang'),
('check_in_end', '08:30:00', 'time', 'schedule', 'Jam terakhir bisa tap datang'),
('check_in_normal', '07:30:00', 'time', 'schedule', 'Jam normal masuk sekolah'),
('check_out_start', '15:00:00', 'time', 'schedule', 'Jam mulai bisa tap pulang'),
('check_out_end', '18:00:00', 'time', 'schedule', 'Jam terakhir bisa tap pulang'),
('check_out_normal', '15:30:00', 'time', 'schedule', 'Jam normal pulang sekolah'),
('whatsapp_notification_enabled', 'true', 'boolean', 'notification', 'Aktifkan notifikasi WA'),
('whatsapp_check_in_time', '08:31:00', 'time', 'notification', 'Jam cek siswa belum tap datang'),
('whatsapp_check_out_time', '18:01:00', 'time', 'notification', 'Jam cek siswa belum tap pulang');
```

---

### 2.14 Table: `notifications`
**Deskripsi:** Log notifikasi yang dikirim

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID |
| student_id | BIGINT UNSIGNED | - | YES | NULL | INDEX | FK ke students (jika untuk siswa) |
| user_id | BIGINT UNSIGNED | - | YES | NULL | INDEX | FK ke users (jika untuk user) |
| type | ENUM | - | NO | - | INDEX | Jenis notifikasi |
| channel | ENUM | - | NO | - | - | Channel (whatsapp, email, in_app) |
| recipient | VARCHAR | 100 | NO | - | - | Penerima (nomor WA/email) |
| subject | VARCHAR | 255 | YES | NULL | - | Subject (untuk email) |
| message | TEXT | - | NO | - | - | Isi pesan |
| status | ENUM | - | NO | 'pending' | INDEX | Status pengiriman |
| sent_at | TIMESTAMP | - | YES | NULL | - | Waktu terkirim |
| failed_reason | TEXT | - | YES | NULL | - | Alasan gagal |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | NULL | - | Waktu diupdate |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (student_id)
- INDEX (user_id)
- INDEX (type)
- INDEX (status)

**Relations:**
- belongsTo: students
- belongsTo: users

**Enum Values:**
- type: ('forgot_check_in', 'forgot_check_out', 'late_arrival', 'monthly_recap')
- channel: ('whatsapp', 'email', 'in_app')
- status: ('pending', 'sent', 'failed')

---

### 2.15 Table: `audit_logs`
**Deskripsi:** Audit trail untuk tracking perubahan data kritis

| Column | Type | Length | Null | Default | Index | Description |
|--------|------|--------|------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PRIMARY | ID |
| user_id | BIGINT UNSIGNED | - | YES | NULL | INDEX | FK ke users (yang melakukan) |
| action | VARCHAR | 50 | NO | - | INDEX | Aksi (create, update, delete) |
| auditable_type | VARCHAR | 100 | NO | - | INDEX | Model yang diubah |
| auditable_id | BIGINT UNSIGNED | - | NO | - | INDEX | ID record yang diubah |
| old_values | JSON | - | YES | NULL | - | Nilai lama |
| new_values | JSON | - | YES | NULL | - | Nilai baru |
| ip_address | VARCHAR | 45 | YES | NULL | - | IP address |
| user_agent | TEXT | - | YES | NULL | - | User agent |
| created_at | TIMESTAMP | - | YES | NULL | - | Waktu kejadian |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (action)
- INDEX (auditable_type, auditable_id)

**Relations:**
- belongsTo: users

**Auditable Models:**
- Student
- User
- Class
- Attendance
- ManualAttendance
- Semester
- AcademicCalendar

---

## 3. ENTITY RELATIONSHIP DIAGRAM (ERD)

```
┌─────────────────┐         ┌──────────────────┐
│     roles       │1       *│      users       │
│─────────────────│─────────│──────────────────│
│ PK: id          │         │ PK: id           │
│ name            │         │ FK: role_id      │
│ display_name    │         │ name             │
└─────────────────┘         │ email            │
        │                   │ username         │
        │                   │ password         │
        │*                  └──────────────────┘
        │                           │1
        │                           │ (homeroom_teacher)
        │                           │
┌─────────────────┐         ┌──────────────────┐         ┌──────────────────┐
│  permissions    │*       *│ role_permission  │         │    classes       │
│─────────────────│─────────│──────────────────│         │──────────────────│
│ PK: id          │         │ PK: id           │        *│ PK: id           │
│ name            │         │ FK: role_id      │─────────│ FK: department_id│
│ display_name    │         │ FK: permission_id│         │ FK: homeroom_t.. │
│ group           │         └──────────────────┘         │ name             │
└─────────────────┘                                      │ grade_level      │
                                                          │ academic_year    │
                                                          └──────────────────┘
┌─────────────────┐                                              │1
│  departments    │                                              │
│─────────────────│1                                             │
│ PK: id          │──────────────────────────────────────────────┘
│ code            │
│ name            │
└─────────────────┘

                    ┌──────────────────┐
                    │    students      │
                   *│──────────────────│
            ┌───────│ PK: id           │
            │       │ FK: class_id     │
            │       │ nis              │
            │       │ nisn             │
            │       │ name             │
            │       │ card_uid         │
            │       │ phone            │
            │       │ parent_phone     │
            │       └──────────────────┘
            │               │1
            │               │
            │               │*
            │       ┌──────────────────┐         ┌──────────────────┐
            │       │  attendances     │*       1│   semesters      │
            │       │──────────────────│─────────│──────────────────│
            │       │ PK: id           │         │ PK: id           │
            │      *│ FK: student_id   │         │ name             │
            └───────│ FK: semester_id  │         │ type             │
                    │ date             │         │ academic_year    │
                    │ check_in_time    │         │ start_date       │
                    │ check_in_status  │         │ end_date         │
                    │ check_out_time   │         │ is_active        │
                    │ check_out_status │         │ is_closed        │
                    │ is_complete      │         └──────────────────┘
                    └──────────────────┘                 │1
                                                         │
                                                         │*
            ┌──────────────────────────┐         ┌──────────────────┐
            │ manual_attendances       │*       1│                  │
            │──────────────────────────│─────────┘
            │ PK: id                   │
            │ FK: student_id           │
            │ FK: semester_id          │
            │ FK: created_by (users)   │
            │ date                     │
            │ type                     │         ┌──────────────────┐
            │ reason                   │         │ academic_cal...  │
            │ attachment               │         │──────────────────│
            └──────────────────────────┘         │ PK: id           │
                                                 │ FK: created_by   │
                                                 │ title            │
┌──────────────────────────┐                    │ type             │
│   notifications          │                    │ start_date       │
│──────────────────────────│                    │ end_date         │
│ PK: id                   │                    │ is_holiday       │
│ FK: student_id           │                    └──────────────────┘
│ FK: user_id              │
│ type                     │
│ channel                  │         ┌──────────────────────────┐
│ recipient                │         │  attendance_settings     │
│ message                  │         │──────────────────────────│
│ status                   │         │ PK: id                   │
│ sent_at                  │         │ key (UNIQUE)             │
└──────────────────────────┘         │ value                    │
                                     │ type                     │
                                     │ group                    │
┌──────────────────────────┐         └──────────────────────────┘
│      audit_logs          │
│──────────────────────────│
│ PK: id                   │
│ FK: user_id              │
│ action                   │
│ auditable_type           │
│ auditable_id             │
│ old_values (JSON)        │
│ new_values (JSON)        │
│ ip_address               │
│ created_at               │
└──────────────────────────┘
```

---

## 4. DATA FLOW & BUSINESS LOGIC

### 4.1 Flow Absensi Datang (Check-In)

```
1. Siswa tap kartu MIFARE di reader
2. Reader kirim Card UID ke sistem
3. Sistem validasi:
   ├─ Cek card_uid ada di table students?
   │  ├─ Tidak → Reject: "Kartu tidak terdaftar"
   │  └─ Ya → Lanjut
   ├─ Cek student.is_active = TRUE?
   │  ├─ Tidak → Reject: "Siswa tidak aktif"
   │  └─ Ya → Lanjut
   ├─ Cek tanggal hari ini di academic_calendars?
   │  ├─ Libur → Reject: "Hari ini libur"
   │  └─ Bukan libur → Lanjut
   ├─ Cek waktu sekarang antara check_in_start dan check_in_end?
   │  ├─ Tidak → Reject: "Di luar jam absensi"
   │  └─ Ya → Lanjut
   ├─ Cek sudah ada attendance untuk student ini hari ini?
   │  ├─ Sudah ada DAN check_in_time sudah diisi → Reject: "Sudah absen datang"
   │  └─ Belum ada ATAU check_in_time masih NULL → Lanjut
   └─ Simpan/Update attendance:
      ├─ student_id = {student_id}
      ├─ semester_id = {semester aktif}
      ├─ date = {hari ini}
      ├─ check_in_time = {waktu sekarang}
      ├─ check_in_status = (waktu > 07:30 ? 'terlambat' : 'tepat_waktu')
      └─ check_in_late_minutes = (waktu > 07:30 ? selisih menit : 0)
4. Tampilkan konfirmasi di reader: "Selamat datang, {Nama Siswa}"
```

### 4.2 Flow Absensi Pulang (Check-Out)

```
Similar dengan check-in, dengan perbedaan:
- Cek sudah check_in hari ini? (harus sudah tap datang)
- Validasi waktu: check_out_start sampai check_out_end
- Update field: check_out_time, check_out_status, check_out_early_minutes
- Set is_complete = TRUE
```

### 4.3 Flow Deteksi Alpha (Cron Job)

```
Setiap hari jam 23:55:
1. Ambil semua siswa yang is_active = TRUE
2. Loop setiap siswa:
   ├─ Cek hari ini libur?
   │  └─ Ya → Skip siswa ini
   ├─ Cek ada attendance hari ini?
   │  └─ Ya → Skip siswa ini
   ├─ Cek ada manual_attendance hari ini?
   │  └─ Ya → Skip siswa ini
   └─ Insert manual_attendance:
      ├─ type = 'alpha'
      ├─ reason = 'Tidak hadir tanpa keterangan'
      └─ created_by = NULL (sistem)
```

### 4.4 Flow Notifikasi WhatsApp

```
Cron Job 1: Jam 08:31 (siswa belum tap datang)
1. Ambil semua siswa yang is_active = TRUE
2. Cek hari ini bukan libur
3. Loop setiap siswa:
   ├─ Cek attendance.check_in_time untuk hari ini
   │  ├─ Sudah ada → Skip
   │  └─ NULL → Lanjut
   ├─ Cek manual_attendance untuk hari ini
   │  ├─ Sudah ada → Skip
   │  └─ Tidak ada → Lanjut
   └─ Kirim WA + Insert ke notifications:
      ├─ student_id
      ├─ type = 'forgot_check_in'
      ├─ channel = 'whatsapp'
      ├─ recipient = student.phone
      └─ message = template

Cron Job 2: Jam 18:01 (siswa belum tap pulang)
Similar, tapi cek check_out_time dan type = 'forgot_check_out'
```

### 4.5 Flow Absensi via Mobile NFC dengan Geofencing (NEW)

```
1. Siswa buka mobile app dan tap "Absen Datang/Pulang"
2. App request permission GPS (jika belum)
3. App mendapatkan lokasi GPS siswa (latitude, longitude, accuracy)
4. App activate NFC reader
5. Siswa tap kartu MIFARE atau virtual card
6. App kirim data ke server API:
   {
     "card_uid": "ABC123",
     "latitude": -6.2345678,
     "longitude": 106.1234567,
     "accuracy": 15,
     "device_info": {...}
   }
7. Server validasi:
   ├─ Cek card_uid terdaftar? → Sama dengan flow kartu fisik
   ├─ Cek GPS accuracy ≤ 20 meter?
   │  ├─ Tidak → Return warning: "Sinyal GPS lemah"
   │  └─ Ya → Lanjut
   ├─ Hitung jarak ke semua attendance_locations (Haversine Formula):
   │  └─ Formula: a = sin²(Δφ/2) + cos φ1 ⋅ cos φ2 ⋅ sin²(Δλ/2)
   │               c = 2 ⋅ atan2( √a, √(1−a) )
   │               d = R ⋅ c  (R = 6371 km)
   ├─ Cari lokasi terdekat dengan jarak ≤ radius
   │  ├─ Tidak ada → Reject: "Anda di luar area absensi (jarak terdekat: X meter)"
   │  └─ Ada → Lanjut
   └─ Simpan attendance dengan data tambahan:
      ├─ check_in_method = 'mobile'
      ├─ check_in_latitude = {lat}
      ├─ check_in_longitude = {long}
      ├─ check_in_location_id = {lokasi terdekat}
      ├─ check_in_distance = {jarak dalam meter}
      └─ check_in_device = {device info JSON}
8. Return success response ke app
9. App tampilkan konfirmasi: "Absensi berhasil di {Nama Lokasi} ({jarak} meter)"
```

**GPS Spoofing Prevention:**
- Cek GPS accuracy (jika > 50 meter: suspicious)
- Cek mock location flag (Android Developer Options)
- Simpan device fingerprint untuk detect anomaly
- Log semua attempt untuk audit trail


---

## 5. INDEXING STRATEGY

### 5.1 Performance Critical Indexes
Tabel yang sering di-query dengan volume tinggi:

**attendance_locations:** **[NEW]**
- Composite index: (latitude, longitude) - untuk geofencing calculation
- Index: (is_active) - untuk filter lokasi aktif
- SPATIAL index (jika menggunakan MySQL 8.0+) untuk optimasi distance calculation

**attendances:**
- Composite index: (student_id, date) - untuk cek duplikasi
- Composite index: (semester_id, date) - untuk laporan semester
- Index: (date) - untuk filter tanggal
- Index: (is_complete) - untuk cek siswa belum pulang
- **Index: (check_in_method, check_out_method)** - **[NEW]** untuk analisis metode absensi
- **Index: (check_in_location_id, check_out_location_id)** - **[NEW]** untuk laporan per lokasi

**manual_attendances:**
- Composite index: (student_id, date)
- Index: (type) - untuk filter jenis
- Composite index: (semester_id, date)

**students:**
- Index: (card_uid) - untuk tap lookup (CRITICAL)
- Index: (class_id) - untuk query per kelas
- Index: (is_active) - untuk filter aktif

### 5.2 Query Optimization Tips
```sql
-- Optimized: Gunakan covering index
SELECT id, name, card_uid FROM students WHERE card_uid = 'ABC123';

-- Optimized: Gunakan BETWEEN untuk range tanggal
SELECT * FROM attendances
WHERE date BETWEEN '2025-01-01' AND '2025-01-31';

-- Avoid: SELECT *  (pilih kolom yang diperlukan saja)
-- Avoid: OR dalam WHERE (gunakan UNION jika perlu)
-- Avoid: Function di WHERE (WHERE MONTH(date) = 1) → gunakan BETWEEN
```

---

## 6. DATA RETENTION & ARCHIVING

### 6.1 Retention Policy
| Tabel | Retention | Archive Strategy |
|-------|-----------|------------------|
| attendances | 5 tahun | Pindah ke attendances_archive setelah 5 tahun |
| manual_attendances | 5 tahun | Pindah ke manual_attendances_archive |
| audit_logs | 2 tahun | Delete after 2 tahun |
| notifications | 6 bulan | Delete after 6 bulan |
| students (inactive) | Permanent | Soft delete (is_active = FALSE) |

### 6.2 Backup Strategy
- **Daily:** Full backup database (retention 30 hari)
- **Weekly:** Export data penting ke cloud storage
- **Semester:** Backup khusus saat tutup semester (permanent)

---

## 7. DATABASE MIGRATIONS (Laravel)

### 7.1 Migration Order
```
1. create_roles_table
2. create_users_table
3. create_permissions_table
4. create_role_permission_table
5. create_departments_table
6. create_classes_table
7. create_students_table
8. create_semesters_table
9. create_academic_calendars_table
10. create_attendance_locations_table          ← [NEW]
11. create_attendances_table (with mobile fields)  ← [UPDATED]
12. create_manual_attendances_table
13. create_attendance_settings_table
14. create_notifications_table
15. create_audit_logs_table
```

### 7.2 Seeders
```
1. RoleSeeder
2. PermissionSeeder
3. RolePermissionSeeder
4. DepartmentSeeder
5. AttendanceSettingSeeder
6. AttendanceLocationSeeder  ← [NEW] Seed default locations
7. DemoUserSeeder (untuk testing)
```

---

## 8. DATABASE PERFORMANCE ESTIMATES

### 8.1 Storage Estimates (per tahun)
- **attendance_locations:** ~10 lokasi × 300 bytes = ~3 KB (negligible) **[NEW]**
- **attendances:** ~1500 siswa × 200 hari × 350 bytes = ~105 MB/tahun **[UPDATED: +GPS data]**
- manual_attendances: ~50k records × 300 bytes = ~15 MB/tahun
- notifications: ~100k records × 500 bytes = ~50 MB/tahun
- audit_logs: ~200k records × 1KB = ~200 MB/tahun

**Total estimate:** ~370 MB/tahun **[UPDATED]**
**5 tahun:** ~1.85 GB (masih sangat manageable)

**Notes:**
- Attendance record size meningkat dari 200 → 350 bytes karena tambahan GPS coordinates, device info, dll.
- GPS coordinates disimpan sebagai DECIMAL bukan POINT untuk compatibility

### 8.2 Query Performance Targets
- Tap lookup (by card_uid): < 50ms
- **Geofencing validation (Haversine calculation):** < 100ms **[NEW]**
- **Mobile API response (full attendance flow):** < 1 second **[NEW]**
- Dashboard queries: < 500ms
- Report generation (1 bulan): < 2 detik
- Bulk operations (import 100 siswa): < 5 detik

---

**Document Control:**
- **Author:** Database Architect
- **Reviewer:** -
- **Approved by:** -

