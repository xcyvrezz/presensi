# SYSTEM REQUIREMENTS SPECIFICATION (SRS)
## Sistem Absensi MIFARE - SMK Negeri 10 Pandeglang

**Versi:** 1.1
**Tanggal:** 13 Desember 2025
**Status:** Draft - Updated with Mobile NFC & Geofencing

---

## 1. PENDAHULUAN

### 1.1 Tujuan Dokumen
Dokumen ini menjelaskan spesifikasi kebutuhan sistem absensi berbasis teknologi NFC MIFARE 13.56 MHz untuk SMK Negeri 10 Pandeglang yang dikembangkan menggunakan framework Laravel dengan dukungan mobile application.

### 1.2 Ruang Lingkup
Sistem ini mencakup:
- Pencatatan kehadiran siswa menggunakan kartu MIFARE fisik
- **[NEW]** Pencatatan kehadiran menggunakan smartphone NFC (Android/iOS)
- **[NEW]** Validasi lokasi absensi dengan geofencing (radius 15 meter)
- Manajemen data kehadiran oleh berbagai role pengguna
- Pelaporan dan analisis kehadiran
- Notifikasi otomatis untuk siswa yang lupa absen
- Pengelolaan kalender akademik dan lokasi absensi

### 1.3 Profil Sekolah
- **Nama:** SMK Negeri 10 Pandeglang
- **Program Keahlian:**
  - PPLG (Pengembangan Perangkat Lunak dan Gim)
  - AKL (Akuntansi dan Keuangan Lembaga)
  - TO (Teknik Otomotif)
- **Sistem Pembelajaran:** Full Day School (5 hari kerja)
- **Jam Operasional:**
  - Masuk: 07:30 WIB
  - Pulang: 15:30 WIB

---

## 2. DESKRIPSI UMUM SISTEM

### 2.1 Perspektif Produk
Sistem absensi MIFARE adalah aplikasi hybrid yang terdiri dari:
1. **Web Application (Laravel):** Backend API dan dashboard untuk admin, kepala sekolah, dan wali kelas
2. **Mobile Application (Progressive Web App/Native):** Untuk absensi siswa via NFC smartphone dengan validasi geolocation
3. **RFID Reader Integration:** Untuk absensi menggunakan kartu MIFARE fisik di lokasi tertentu

Sistem ini memberikan fleksibilitas maksimal dengan dua metode absensi:
- **Kartu MIFARE Fisik:** Tap di reader yang terpasang di sekolah
- **Smartphone NFC:** Tap menggunakan HP pribadi dengan validasi lokasi (geofencing 15m)

### 2.2 Fungsi Produk
1. Pencatatan absensi otomatis via tapping kartu MIFARE atau smartphone NFC
2. **[NEW]** Validasi lokasi absensi dengan teknologi GPS/Geofencing
3. **[NEW]** Manajemen titik lokasi absensi oleh admin
4. Manajemen kehadiran manual (izin, sakit, dispensasi)
5. Pelaporan kehadiran multi-level
6. Analisis statistik kehadiran
7. Notifikasi otomatis via WhatsApp
8. Manajemen kalender akademik

### 2.3 Karakteristik Pengguna

| Role | Deskripsi | Jumlah Estimasi | Tingkat Teknis |
|------|-----------|-----------------|----------------|
| **Siswa** | Pengguna yang melakukan absensi dan melihat data pribadi | 900-1500 | Rendah |
| **Wali Kelas** | Guru yang mengelola kehadiran kelas | 30-50 | Sedang |
| **Admin** | Pengelola sistem dan data master | 2-5 | Tinggi |
| **Kepala Sekolah** | Pengawas yang melihat dashboard dan statistik | 1 | Sedang |

---

## 3. KEBUTUHAN FUNGSIONAL

### 3.1 Modul Autentikasi & Otorisasi

#### FR-AUTH-001: Login Multi-Role
- **Prioritas:** Tinggi
- **Deskripsi:** Sistem harus mendukung login untuk 4 role berbeda (Admin, Kepala Sekolah, Wali Kelas, Siswa)
- **Input:** Email/Username, Password
- **Output:** Dashboard sesuai role
- **Validasi:**
  - Kredensial harus valid
  - Password minimal 8 karakter
  - Implementasi rate limiting (5 percobaan per 15 menit)

#### FR-AUTH-002: Role-Based Access Control (RBAC)
- **Prioritas:** Tinggi
- **Deskripsi:** Setiap role memiliki akses terbatas sesuai kewenangannya
- **Detail:** Lihat bagian 4 (Role & Permission Matrix)

---

### 3.2 Modul Absensi (Tapping)

#### FR-ABS-001: Pencatatan Absensi Datang
- **Prioritas:** Kritis
- **Deskripsi:** Sistem mencatat kehadiran siswa saat tap kartu MIFARE untuk kedatangan
- **Input:**
  - Card UID (MIFARE)
  - Timestamp
- **Output:** Konfirmasi absensi tersimpan
- **Aturan Bisnis:**
  - Jam operasional tap datang: 06:00 - 08:30
  - Jam normal masuk: 07:30
  - Jika tap > 07:30: status "Hadir Terlambat" + hitung durasi keterlambatan
  - Jika tap ≤ 07:30: status "Hadir Tepat Waktu"
  - Jika tap < 06:00 atau > 08:30: tolak dengan pesan error
  - Satu siswa hanya bisa tap datang 1x per hari

#### FR-ABS-002: Pencatatan Absensi Pulang
- **Prioritas:** Kritis
- **Deskripsi:** Sistem mencatat kepulangan siswa saat tap kartu MIFARE
- **Input:**
  - Card UID (MIFARE)
  - Timestamp
- **Output:** Konfirmasi absensi pulang tersimpan
- **Aturan Bisnis:**
  - Jam operasional tap pulang: 15:00 - 18:00
  - Jam normal pulang: 15:30
  - Jika tap < 15:30: status "Pulang Cepat" + hitung durasi pulang cepat
  - Jika tap ≥ 15:30: status "Pulang Normal"
  - Jika tap < 15:00 atau > 18:00: tolak dengan pesan error
  - Harus sudah tap datang sebelumnya
  - Satu siswa hanya bisa tap pulang 1x per hari

#### FR-ABS-003: Validasi Hari Libur/Acara
- **Prioritas:** Tinggi
- **Deskripsi:** Sistem tidak menerima absensi pada hari libur atau acara khusus
- **Aturan Bisnis:**
  - Cek kalender akademik sebelum menerima tap
  - Jika hari libur: tolak dengan pesan "Hari ini libur"
  - Jika ada acara khusus: sesuaikan dengan jenis acara

#### FR-ABS-004: Pencatatan Absensi via Smartphone NFC (Datang)
- **Prioritas:** Kritis
- **Deskripsi:** Siswa dapat melakukan absensi datang menggunakan smartphone NFC dengan validasi lokasi
- **Input:**
  - Card UID (dari virtual card atau card emulation)
  - Timestamp
  - GPS Coordinate (latitude, longitude)
  - Device info (untuk tracking)
- **Output:** Konfirmasi absensi tersimpan
- **Aturan Bisnis:**
  - Semua validasi sama dengan FR-ABS-001
  - **[TAMBAHAN]** Validasi jarak dari titik lokasi absensi terdekat
  - Jarak harus ≤ 15 meter dari titik lokasi yang ditentukan admin
  - Jika > 15 meter: tolak dengan pesan "Anda berada di luar area absensi"
  - Jika GPS tidak aktif: tolak dengan pesan "Aktifkan GPS untuk absensi"
  - Jika smartphone tidak support NFC: tolak dengan pesan "Perangkat tidak support NFC"
  - Simpan koordinat lokasi saat absensi untuk audit trail
- **Platform Support:**
  - Android: NFC + GPS required
  - iOS: NFC + GPS required (iOS 13+)

#### FR-ABS-005: Pencatatan Absensi via Smartphone NFC (Pulang)
- **Prioritas:** Kritis
- **Deskripsi:** Siswa dapat melakukan absensi pulang menggunakan smartphone NFC dengan validasi lokasi
- **Input:** Sama dengan FR-ABS-004
- **Output:** Konfirmasi absensi pulang tersimpan
- **Aturan Bisnis:**
  - Semua validasi sama dengan FR-ABS-002
  - Validasi geofencing sama dengan FR-ABS-004
  - Harus sudah tap datang sebelumnya (via kartu atau HP)

#### FR-ABS-006: Deteksi Metode Absensi
- **Prioritas:** Sedang
- **Deskripsi:** Sistem mencatat metode absensi yang digunakan (kartu fisik vs smartphone)
- **Data yang disimpan:**
  - Method: 'card' atau 'mobile'
  - Device info (jika mobile): model, OS, browser
  - Location (jika mobile): latitude, longitude, accuracy
- **Tujuan:** Untuk analisis dan audit trail

---

### 3.2.1 Modul Manajemen Lokasi Absensi (Geofencing)

#### FR-GEO-001: Manajemen Titik Lokasi Absensi
- **Prioritas:** Tinggi
- **Deskripsi:** Admin dapat mengelola titik-titik lokasi yang diperbolehkan untuk absensi
- **Operasi:** CRUD
- **Data:**
  - Nama lokasi (contoh: "Gerbang Utama", "Ruang Guru", "Lapangan")
  - Koordinat GPS (latitude, longitude)
  - Radius validasi (default: 15 meter, bisa disesuaikan 5-50 meter)
  - Status aktif/non-aktif
  - Deskripsi
- **Fitur Tambahan:**
  - Pin lokasi di map (Google Maps/OpenStreetMap)
  - Preview radius di map (lingkaran)
  - Test lokasi: cek apakah koordinat tertentu dalam radius

#### FR-GEO-002: Validasi Jarak Geofencing
- **Prioritas:** Kritis
- **Deskripsi:** Sistem memvalidasi apakah lokasi siswa berada dalam radius yang diizinkan
- **Algoritma:** Haversine Formula untuk menghitung jarak antara 2 koordinat GPS
- **Proses:**
  1. Ambil koordinat siswa saat tap
  2. Ambil semua lokasi absensi yang aktif
  3. Hitung jarak ke setiap lokasi
  4. Cari lokasi terdekat
  5. Jika jarak terdekat ≤ radius: VALID
  6. Jika semua jarak > radius: INVALID
- **Toleransi:**
  - GPS accuracy: accept jika accuracy ≤ 20 meter
  - Jika accuracy > 20 meter: peringatan "Sinyal GPS lemah"

#### FR-GEO-003: Log Lokasi Absensi
- **Prioritas:** Sedang
- **Deskripsi:** Sistem menyimpan log lokasi setiap kali absensi via mobile
- **Data Log:**
  - Student ID
  - Attendance ID
  - GPS coordinate (lat, long)
  - GPS accuracy
  - Lokasi terdekat yang terdeteksi
  - Jarak ke lokasi terdekat
  - Timestamp
- **Tujuan:** Audit trail dan deteksi kecurangan (GPS spoofing)

---

### 3.3 Modul Manajemen Kehadiran Manual

#### FR-MAN-001: Input Izin oleh Wali Kelas
- **Prioritas:** Tinggi
- **Deskripsi:** Wali kelas dapat menginput siswa yang izin
- **Input:**
  - Siswa
  - Tanggal
  - Jenis: Izin
  - Keterangan (opsional)
  - Dokumen pendukung (opsional)
- **Output:** Data kehadiran tersimpan dengan status "Izin"
- **Validasi:**
  - Hanya untuk siswa di kelas yang diampu
  - Tidak boleh bentrok dengan data absensi tapping

#### FR-MAN-002: Input Sakit oleh Wali Kelas
- **Prioritas:** Tinggi
- **Deskripsi:** Wali kelas dapat menginput siswa yang sakit
- **Input:** Sama dengan FR-MAN-001, jenis: Sakit
- **Output:** Data kehadiran tersimpan dengan status "Sakit"

#### FR-MAN-003: Input Dispensasi oleh Wali Kelas
- **Prioritas:** Sedang
- **Deskripsi:** Wali kelas dapat menginput siswa yang dispensasi
- **Input:** Sama dengan FR-MAN-001, jenis: Dispensasi
- **Output:** Data kehadiran tersimpan dengan status "Dispensasi"
- **Catatan:** Dispensasi dihitung sebagai hadir tetapi dengan catatan khusus

---

### 3.4 Modul Pelaporan

#### FR-REP-001: Rekap Kehadiran Siswa (Wali Kelas)
- **Prioritas:** Tinggi
- **Deskripsi:** Wali kelas dapat melihat rekap kehadiran siswa di kelasnya
- **Filter:**
  - Per siswa
  - Per periode (harian, mingguan, bulanan, semester)
  - Per status (Hadir, Alpha, Izin, Sakit, Dispensasi)
- **Output:**
  - Tabel kehadiran
  - Total: Hadir, Alpha, Izin, Sakit, Dispensasi
  - Persentase kehadiran
  - Total keterlambatan (menit & jam)
  - Total pulang cepat (menit & jam)
  - Export: PDF, Excel

#### FR-REP-002: Statistik Kehadiran (Kepala Sekolah)
- **Prioritas:** Tinggi
- **Deskripsi:** Kepala sekolah dapat melihat statistik kehadiran seluruh sekolah
- **Tampilan:**
  - Dashboard dengan grafik
  - Tingkat kehadiran per jurusan (PPLG, AKL, TO)
  - Tingkat kehadiran per kelas
  - Tren kehadiran (grafik garis)
  - Top 10 kelas dengan kehadiran terbaik
- **Filter:**
  - Per periode
  - Per jurusan
  - Per semester
- **Output:**
  - Dashboard interaktif
  - Export: PDF

#### FR-REP-003: Rekap Pribadi (Siswa)
- **Prioritas:** Sedang
- **Deskripsi:** Siswa dapat melihat rekap kehadiran pribadinya
- **Tampilan:**
  - Kalender kehadiran
  - Total kehadiran bulan ini
  - Total keterlambatan
  - History absensi
- **Output:** Halaman web responsif

#### FR-REP-004: Laporan Periode
- **Prioritas:** Tinggi
- **Deskripsi:** Generate laporan kehadiran untuk periode tertentu
- **Input:**
  - Tanggal mulai - selesai
  - Kelas/Jurusan (opsional)
- **Output:**
  - Jumlah Hadir, Alpha, Izin, Sakit per siswa
  - Jumlah keterlambatan (total menit & jam)
  - Jumlah pulang cepat (total menit & jam)
  - Persentase kehadiran
  - Export: PDF, Excel

---

### 3.5 Modul Notifikasi

#### FR-NOT-001: Notifikasi WhatsApp untuk Siswa Tidak Absen
- **Prioritas:** Sedang
- **Deskripsi:** Sistem otomatis mengirim pesan WhatsApp kepada siswa yang lupa absen
- **Trigger:**
  - Setiap hari pukul 08:31 (setelah jam tap datang berakhir)
  - Setiap hari pukul 18:01 (setelah jam tap pulang berakhir)
- **Kondisi:** Siswa belum tap datang/pulang dan bukan hari libur
- **Isi Pesan:**
  ```
  Halo [Nama Siswa],
  Anda belum melakukan absensi [datang/pulang] hari ini ([Tanggal]).
  Silakan hubungi wali kelas Anda.

  SMK Negeri 10 Pandeglang
  ```
- **Teknologi:** WhatsApp Business API / Fonnte / Wablas

#### FR-NOT-002: Notifikasi In-App
- **Prioritas:** Rendah
- **Deskripsi:** Notifikasi dalam aplikasi untuk berbagai event
- **Event:**
  - Siswa berhasil absen
  - Wali kelas ada siswa terlambat
  - Admin ada data baru

---

### 3.6 Modul Manajemen Data Master (Admin)

#### FR-ADM-001: Manajemen Data Siswa
- **Prioritas:** Kritis
- **Operasi:** CRUD (Create, Read, Update, Delete)
- **Data:**
  - NIS, NISN
  - Nama lengkap
  - Kelas, Jurusan
  - Card UID (MIFARE)
  - Nomor WhatsApp
  - Foto
  - Status aktif/non-aktif
- **Fitur Tambahan:**
  - Import Excel (bulk)
  - Export Excel
  - Registrasi kartu MIFARE (assign card ke siswa)

#### FR-ADM-002: Manajemen Data Kelas
- **Prioritas:** Tinggi
- **Operasi:** CRUD
- **Data:**
  - Nama kelas (contoh: X PPLG 1, XI AKL 2)
  - Jurusan (PPLG/AKL/TO)
  - Tingkat (X, XI, XII)
  - Wali kelas
  - Tahun ajaran
  - Kapasitas

#### FR-ADM-003: Manajemen Pengguna
- **Prioritas:** Tinggi
- **Operasi:** CRUD
- **Data:**
  - Nama, Email, Username
  - Password (hashed)
  - Role
  - Nomor WhatsApp
  - Status aktif/non-aktif

#### FR-ADM-004: Manajemen Kalender Akademik
- **Prioritas:** Tinggi
- **Operasi:** CRUD
- **Data:**
  - Tanggal mulai - selesai
  - Jenis: Libur Nasional, Libur Semester, Acara Sekolah, dll.
  - Nama acara/libur
  - Deskripsi
- **Aturan:**
  - Sistem otomatis menandai hari Sabtu-Minggu sebagai libur
  - Admin bisa override untuk acara khusus di weekend

#### FR-ADM-005: Manajemen Semester
- **Prioritas:** Tinggi
- **Operasi:** CRUD
- **Data:**
  - Nama semester (contoh: Ganjil 2025/2026)
  - Tanggal mulai - selesai
  - Status aktif (hanya 1 semester aktif)
- **Fitur:**
  - Tutup semester: proses rekap dan mulai semester baru
  - Saat semester ditutup, data tidak bisa diedit lagi (archived)

---

### 3.7 Modul Analisis & Dashboard

#### FR-ANA-001: Top 10 Kelas Kehadiran Terbaik
- **Prioritas:** Sedang
- **Deskripsi:** Menampilkan 10 kelas dengan persentase kehadiran tertinggi
- **Perhitungan:**
  ```
  Persentase = (Total Hadir + Dispensasi) / (Total Hari Efektif × Jumlah Siswa) × 100%
  ```
- **Filter:** Per semester, per bulan
- **Tampilan:** Tabel ranking dengan badge

#### FR-ANA-002: Analisis Keterlambatan
- **Prioritas:** Sedang
- **Deskripsi:** Analisis pola keterlambatan siswa
- **Output:**
  - Siswa dengan keterlambatan terbanyak
  - Rata-rata durasi keterlambatan per kelas
  - Hari dengan keterlambatan terbanyak
  - Grafik tren keterlambatan

#### FR-ANA-003: Analisis Ketidakhadiran
- **Prioritas:** Sedang
- **Deskripsi:** Analisis pola ketidakhadiran (Alpha)
- **Output:**
  - Siswa dengan alpha terbanyak (early warning system)
  - Kelas dengan alpha tertinggi
  - Tren alpha per bulan

#### FR-ANA-004: Dashboard Real-time
- **Prioritas:** Sedang
- **Deskripsi:** Dashboard real-time untuk monitoring kehadiran hari ini
- **Tampilan:**
  - Total siswa hadir hari ini
  - Total siswa terlambat
  - Total siswa belum absen
  - Live feed: absensi terbaru
  - Grafik kehadiran per jam

---

## 4. KEBUTUHAN NON-FUNGSIONAL

### 4.1 Performance
- **NFR-PERF-001:** Sistem harus merespons tap kartu dalam < 2 detik
- **NFR-PERF-002:** Dashboard harus load dalam < 3 detik
- **NFR-PERF-003:** Export laporan (100 siswa) harus selesai dalam < 10 detik
- **NFR-PERF-004:** Sistem harus dapat menangani 500 tap/menit (peak hour)
- **NFR-PERF-005:** **[NEW]** API response untuk validasi geofencing < 1 detik
- **NFR-PERF-006:** **[NEW]** Mobile app harus merespons tap NFC < 3 detik (termasuk validasi lokasi)

### 4.2 Security
- **NFR-SEC-001:** Semua password harus di-hash menggunakan bcrypt (cost: 12)
- **NFR-SEC-002:** Implementasi CSRF protection untuk semua form
- **NFR-SEC-003:** Sanitasi input untuk mencegah SQL Injection & XSS
- **NFR-SEC-004:** Session timeout setelah 30 menit idle
- **NFR-SEC-005:** HTTPS mandatory untuk production
- **NFR-SEC-006:** Audit trail untuk semua perubahan data kritis
- **NFR-SEC-007:** **[NEW]** API authentication menggunakan JWT/Laravel Sanctum untuk mobile app
- **NFR-SEC-008:** **[NEW]** Validasi GPS spoofing detection (cek GPS accuracy, mock location detection)
- **NFR-SEC-009:** **[NEW]** Rate limiting untuk API absensi (max 5 request per menit per user)
- **NFR-SEC-010:** **[NEW]** Enkripsi data lokasi (GPS coordinate) di database
- **NFR-SEC-011:** **[NEW]** Device fingerprinting untuk deteksi multiple devices per user

### 4.3 Usability
- **NFR-USA-001:** Interface harus responsif (mobile-friendly)
- **NFR-USA-002:** Support browser: Chrome, Firefox, Edge (versi terbaru)
- **NFR-USA-003:** Menggunakan Bahasa Indonesia yang baku
- **NFR-USA-004:** Pesan error harus jelas dan actionable
- **NFR-USA-005:** **[NEW]** Mobile app harus support Android 8.0+ dan iOS 13+
- **NFR-USA-006:** **[NEW]** Offline capability: app harus bisa dibuka tanpa internet (cache data)
- **NFR-USA-007:** **[NEW]** Visual feedback untuk status GPS, NFC, dan koneksi
- **NFR-USA-008:** **[NEW]** Tutorial/onboarding untuk penggunaan pertama kali

### 4.4 Reliability
- **NFR-REL-001:** System uptime minimum 99% (downtime < 7.2 jam/bulan)
- **NFR-REL-002:** Backup database otomatis setiap hari (retention 30 hari)
- **NFR-REL-003:** Data absensi tidak boleh hilang (critical data)

### 4.5 Scalability
- **NFR-SCA-001:** Sistem harus dapat menangani hingga 2000 siswa
- **NFR-SCA-002:** Database harus dapat menyimpan data hingga 5 tahun

### 4.6 Maintainability
- **NFR-MAI-001:** Kode harus mengikuti PSR-12 coding standard
- **NFR-MAI-002:** Code coverage testing minimal 70%
- **NFR-MAI-003:** Dokumentasi kode (PHPDoc) untuk semua fungsi public

---

## 5. CONSTRAINT

### 5.1 Technical Constraints
- Framework: Laravel (versi 10 atau 11)
- PHP: >= 8.1
- Database: MySQL >= 8.0 atau MariaDB >= 10.6
- RFID Reader: MIFARE 13.56 MHz compatible
- **[NEW]** Mobile Platform:
  - Android: 8.0+ with NFC capability
  - iOS: 13+ with NFC capability
  - Progressive Web App (PWA) atau Native App (Flutter/React Native)
- **[NEW]** Map Service: Google Maps API atau OpenStreetMap (Leaflet.js)
- **[NEW]** Geolocation: HTML5 Geolocation API atau native GPS

### 5.2 Business Constraints
- Sistem harus selesai dan live sebelum tahun ajaran baru
- Budget terbatas (prefer open-source solutions)
- Training pengguna maksimal 2 hari

### 5.3 Regulatory Constraints
- Comply dengan UU Perlindungan Data Pribadi
- Data siswa tidak boleh dibagikan ke pihak ketiga tanpa izin

---

## 6. ASUMSI DAN KETERGANTUNGAN

### 6.1 Asumsi
1. Setiap siswa memiliki 1 kartu MIFARE yang unique ATAU smartphone yang support NFC
2. RFID reader sudah terpasang dan terhubung ke server (untuk metode kartu)
3. Koneksi internet tersedia untuk WhatsApp API dan validasi geofencing
4. Server/hosting tersedia dengan spesifikasi memadai
5. **[NEW]** Minimal 80% siswa memiliki smartphone dengan NFC (Android 8.0+ atau iOS 13+)
6. **[NEW]** Siswa memberikan izin akses GPS dan NFC ke aplikasi
7. **[NEW]** Koordinat GPS sekolah sudah ditentukan dengan akurat oleh admin

### 6.2 Ketergantungan
1. WhatsApp Business API atau third-party gateway (Fonnte/Wablas)
2. Middleware/service untuk komunikasi RFID reader ke Laravel (untuk metode kartu)
3. Server dengan OS yang support RFID reader driver (untuk metode kartu)
4. **[NEW]** Google Maps API atau OpenStreetMap untuk map visualization
5. **[NEW]** HTTPS/SSL certificate untuk secure communication antara mobile app dan server
6. **[NEW]** Push notification service (FCM untuk Android, APNs untuk iOS) - opsional

---

## 7. ACCEPTANCE CRITERIA

Sistem dianggap selesai dan acceptable jika:
1. ✅ Semua functional requirements (FR) dengan prioritas Kritis dan Tinggi sudah terimplementasi
2. ✅ Semua non-functional requirements (NFR) terpenuhi
3. ✅ User Acceptance Testing (UAT) oleh semua role berhasil
4. ✅ Load testing berhasil (500 tap/menit)
5. ✅ Dokumentasi lengkap (teknis & user manual)
6. ✅ Training untuk admin dan wali kelas selesai

---

## 8. REFERENSI
- Laravel Documentation: https://laravel.com/docs
- MIFARE Classic 1K Datasheet
- PSR-12 Coding Standard
- UU Perlindungan Data Pribadi Indonesia

---

**Document Control:**
- **Author:** System Analyst
- **Reviewer:** -
- **Approved by:** -
- **Next Review:** -

