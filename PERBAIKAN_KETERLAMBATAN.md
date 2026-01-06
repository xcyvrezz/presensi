# PANDUAN PERBAIKAN SISTEM KETERLAMBATAN

## ✅ Yang Sudah Diperbaiki

### 1. **Validasi Waktu Check-out di Tapping Station**
- ✅ Menambahkan validasi waktu check-out
- ✅ Check-out sekarang harus dalam rentang waktu yang ditentukan (14:00 - 17:00 + 2 jam grace period)
- ✅ Mendukung custom time dari academic calendar

### 2. **Batas Waktu Terlambat (Late Threshold)**
- ✅ Seeder diupdate: `late_threshold` dari `07:00:00` menjadi `07:15:00`
- ✅ Fallback value diupdate di `AttendanceService.php`
- ✅ Fallback value diupdate di `TappingStation.php`

### 3. **Notifikasi Terlambat di Tapping Station**
- ✅ **Warna berbeda**: Notifikasi terlambat sekarang berwarna AMBER (kuning-orange) yang mencolok
- ✅ **Suara berbeda**: Saat terlambat, sistem akan membunyikan "warning beep" (500Hz) bukan "success beep"
- ✅ **Label jelas**: Menampilkan "TERLAMBAT!" dengan font bold di bagian atas pesan
- ✅ **Icon khusus**: Menggunakan icon jam/clock untuk menandakan keterlambatan

### 4. **Statistik Dashboard**
- ✅ Admin Dashboard: Sudah benar (memisahkan hadir dan terlambat)
- ✅ Wali Kelas Dashboard: Sudah benar (memisahkan hadir dan terlambat)
- ✅ Student Dashboard: Sudah benar (memisahkan hadir dan terlambat)
- ✅ Kepala Sekolah Dashboard: Sudah benar (memisahkan hadir dan terlambat)
- ✅ Tapping Station: Sudah benar (statistik "Tepat Waktu" hanya menghitung status 'hadir')

---

## ⚠️ PENTING: Yang Harus Anda Lakukan

### **UPDATE DATABASE**

Database Anda masih memiliki nilai lama `late_threshold = 07:00:00`. Anda HARUS update nilai ini ke `07:15:00`.

#### **Cara 1: Via phpMyAdmin (Paling Mudah)**

1. Buka phpMyAdmin
2. Pilih database `absensi_mifare`
3. Klik tab "SQL"
4. Copy-paste SQL berikut:

```sql
UPDATE attendance_settings
SET value = '07:15:00',
    default_value = '07:15:00',
    updated_at = NOW()
WHERE `key` = 'late_threshold';
```

5. Klik "Go" atau "Jalankan"
6. Verifikasi dengan query:

```sql
SELECT `key`, `value`, `default_value`, `updated_at`
FROM attendance_settings
WHERE `key` = 'late_threshold';
```

#### **Cara 2: Via Command Line**

```bash
# Masuk ke direktori project
cd D:\xampp\htdocs\absensi-mifare

# Jalankan SQL script
mysql -u root absensi_mifare < update_late_threshold.sql
```

#### **Cara 3: Via Halaman Pengaturan (Jika Sudah Ada)**

Jika sistem Anda sudah punya halaman pengaturan absensi di menu Admin:
1. Login sebagai Admin
2. Masuk ke menu Pengaturan > Absensi
3. Cari "Batas Waktu Terlambat"
4. Ubah dari `07:00:00` ke `07:15:00`
5. Klik Simpan

---

## 🧪 Testing Setelah Update Database

### **Test 1: Check-in Tepat Waktu**
1. Tap kartu sebelum jam **07:15**
2. **Hasil yang diharapkan:**
   - ✅ Status: **"hadir"** (bukan "terlambat")
   - ✅ Notifikasi: Biru dengan pesan "Check-in berhasil! Selamat datang"
   - ✅ Suara: Success beep (tinggi)
   - ✅ Late minutes: 0
   - ✅ Statistik "Tepat Waktu" bertambah 1

### **Test 2: Check-in Terlambat Sedikit**
1. Tap kartu pada jam **07:20**
2. **Hasil yang diharapkan:**
   - ✅ Status: **"terlambat"**
   - ✅ Notifikasi: **AMBER (kuning-orange)** dengan tulisan besar **"TERLAMBAT!"**
   - ✅ Pesan: "Check-in berhasil. Anda terlambat 5 menit"
   - ✅ Suara: **Warning beep** (sedang, 500Hz)
   - ✅ Late minutes: 5
   - ✅ Badge amber di bawah jam showing "Terlambat 5 menit"
   - ✅ Statistik "Terlambat" bertambah 1 (BUKAN "Tepat Waktu")

### **Test 3: Check-in Terlambat Banyak**
1. Tap kartu pada jam **09:00**
2. **Hasil yang diharapkan:**
   - ✅ Status: **"terlambat"**
   - ✅ Notifikasi: **AMBER** dengan **"TERLAMBAT!"**
   - ✅ Pesan: "Check-in berhasil. Anda terlambat 105 menit" (09:00 - 07:15 = 1 jam 45 menit = 105 menit)
   - ✅ Suara: Warning beep
   - ✅ Late minutes: 105
   - ✅ Statistik "Terlambat" bertambah 1

### **Test 4: Check-out Normal**
1. Tap kartu untuk check-out antara jam **14:00 - 17:00**
2. **Hasil yang diharapkan:**
   - ✅ Check-out berhasil
   - ✅ Notifikasi hijau "Check-out berhasil"
   - ✅ Menampilkan jam masuk dan keluar

### **Test 5: Check-out Terlalu Awal**
1. Tap kartu untuk check-out sebelum jam **14:00** (misalnya jam 13:00)
2. **Hasil yang diharapkan:**
   - ❌ Check-out ditolak
   - ⚠️ Pesan: "CHECK-OUT BELUM DIBUKA: Waktu check-out dimulai pukul 14:00:00"

### **Test 6: Check-out Terlalu Malam**
1. Tap kartu untuk check-out setelah jam **19:00** (17:00 + 2 jam grace period)
2. **Hasil yang diharapkan:**
   - ❌ Check-out ditolak
   - ⚠️ Pesan: "CHECK-OUT SUDAH DITUTUP: ... Hubungi wali kelas"

---

## 📊 Statistik Dashboard

Semua dashboard sudah benar memisahkan antara:
- **Tepat Waktu / On Time** = hanya status `hadir`
- **Terlambat / Late** = status `terlambat`
- **Total Hadir / Present** = `hadir` + `terlambat` (yang benar-benar datang ke sekolah)

### Di Tapping Station:
```
Statistik Hari Ini:
├── Total Hadir: XX     (hadir + terlambat)
├── Tepat Waktu: XX     (hanya hadir)
└── Terlambat: XX       (hanya terlambat)
```

---

## 🎨 Tampilan Notifikasi Terlambat Baru

### Sebelum (Perbaikan):
- Warna: Biru (sama dengan tepat waktu)
- Suara: Success beep
- Tidak ada indikator jelas bahwa ini terlambat

### Sesudah (Sekarang):
```
┌─────────────────────────────────────────────┐
│  [🕐]  TERLAMBAT!                           │
│        Check-in berhasil. Anda terlambat    │
│        XX menit.                            │
└─────────────────────────────────────────────┘
     WARNA: AMBER (Kuning-Orange Terang)
     SUARA: Warning Beep (500Hz, medium pitch)
```

---

## 📁 File yang Dimodifikasi

1. **app/Services/AttendanceService.php**
   - ✅ Menambahkan method `validateCheckOutTime()`
   - ✅ Update fallback `late_threshold` dari '07:00:00' ke '07:15:00'
   - ✅ Memanggil validasi check-out di method `checkOut()`

2. **app/Livewire/Public/TappingStation.php**
   - ✅ Update default `late_threshold` dari '07:00:00' ke '07:15:00'
   - ✅ Menambahkan logic untuk play warning sound saat terlambat

3. **resources/views/livewire/public/tapping-station.blade.php**
   - ✅ Menambahkan conditional styling untuk notifikasi terlambat (warna amber)
   - ✅ Menampilkan label "TERLAMBAT!" yang jelas
   - ✅ Icon clock khusus untuk keterlambatan

4. **database/seeders/AttendanceSettingSeeder.php**
   - ✅ Update default value `late_threshold` dari '07:00:00' ke '07:15:00'

5. **update_late_threshold.sql**
   - ✅ SQL script untuk update database yang sudah ada

---

## ❓ FAQ

### Q: Kenapa saat tap jam 09:00 masih tercatat "Tepat Waktu"?
**A:** Karena database Anda masih punya nilai lama `late_threshold = 07:00:00`. Jalankan SQL update di atas untuk mengubahnya ke `07:15:00`.

### Q: Apakah perlu re-seed database?
**A:** TIDAK PERLU. Cukup jalankan SQL UPDATE di atas. Re-seed akan menghapus semua data absensi yang sudah ada.

### Q: Bagaimana kalau mau ubah batas terlambat ke jam lain (misal 07:30)?
**A:** Ubah di database via SQL atau menu Pengaturan:
```sql
UPDATE attendance_settings SET value = '07:30:00' WHERE `key` = 'late_threshold';
```

### Q: Apakah dashboard sudah otomatis update?
**A:** YA! Setelah update database, semua dashboard akan otomatis menampilkan statistik yang benar.

---

## 🔧 Troubleshooting

### Problem: Setelah update database, masih tercatat tepat waktu
**Solusi:**
1. Clear cache aplikasi: `php artisan cache:clear`
2. Clear config cache: `php artisan config:clear`
3. Restart Livewire
4. Refresh halaman tapping station (Ctrl+F5)

### Problem: Notifikasi tidak berwarna amber
**Solusi:**
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+F5)
3. Cek apakah Tailwind CSS sudah build ulang

### Problem: Warning sound tidak berbunyi
**Solusi:**
1. Pastikan browser mengizinkan autoplay audio
2. Refresh halaman tapping station
3. Test dengan tap sekali lagi

---

## ✨ Fitur Tambahan yang Sudah Ada

1. **Grace Period Check-in**: 4 jam setelah `check_in_end`
2. **Grace Period Check-out**: 2 jam setelah `check_out_end`
3. **Custom Time per Event**: Mendukung waktu khusus dari Academic Calendar
4. **Holiday Detection**: Otomatis block absensi saat libur
5. **Multiple Format Card UID**: Support berbagai format RFID/NFC
6. **Detailed Logging**: Semua proses check-in/out ter-log untuk debugging

---

**Dibuat:** 2026-01-06
**Versi:** 1.0
