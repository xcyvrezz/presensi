# USB RFID Reader - Quick Guide

## 📱 Cara Menggunakan USB RFID Reader

### 1. Setup Hardware
- Colokkan USB RFID Reader ke komputer/laptop server
- Pastikan driver terinstall (biasanya auto-detect sebagai USB Keyboard HID)
- Test reader dengan membuka Notepad dan tap kartu → akan muncul Card UID

### 2. Cara Pakai di Admin Panel

1. **Login** sebagai Admin
2. Buka menu **Data Absensi**
3. Klik tombol **"Start Reader"** (biru)
4. Input field akan muncul dan **auto-focus**
5. **Tap kartu** ke USB reader
6. Card UID otomatis diketik ke input field
7. Tekan **Enter** (atau reader auto-enter)
8. Sistem langsung proses absensi
9. Lihat message konfirmasi (hijau/merah/kuning)
10. Input field **auto-clear** dan siap untuk kartu berikutnya

### 3. Status Messages

| Icon | Status | Arti |
|------|--------|------|
| ✅ | Hijau | Check-in/out berhasil |
| ❌ | Merah | Kartu tidak terdaftar atau error |
| ⚠️ | Kuning | Sudah absen lengkap hari ini |
| 💡 | Biru | Reader aktif, siap scan |

### 4. Fitur Auto:
- ✅ **Auto-focus** → Input selalu focus, tidak perlu klik
- ✅ **Auto-process** → Langsung proses saat Enter ditekan
- ✅ **Auto-clear** → Input otomatis kosong setelah scan
- ✅ **Auto-detect** → Check-in atau check-out otomatis
- ✅ **Auto-refresh** → Statistics update real-time

### 5. Troubleshooting

**Reader tidak mengetik:**
- Cek apakah USB reader ter-connect
- Test di Notepad, apakah mengetik Card UID
- Restart browser

**Card UID tidak dikenali:**
```sql
-- Cek card UID di database
SELECT nis, full_name, card_uid FROM students WHERE card_uid IS NOT NULL;

-- Update card UID siswa
UPDATE students SET card_uid = 'ABC123456789' WHERE nis = '2024001';
```

**Input tidak focus:**
- Klik manual pada input field
- Atau reload page

**Enter tidak otomatis:**
- Beberapa reader perlu config untuk auto-enter
- Atau tekan Enter manual setelah scan

### 6. Format Card UID

Reader biasanya output format:
- **HEX:** `0123456789AB` (8-20 characters)
- **Decimal:** Convert dulu ke HEX
- **Case:** Auto convert ke uppercase

Contoh valid Card UID:
```
0A1B2C3D
04E5F6G7H8I9
ABCDEF123456
```

### 7. Mode Reader USB

Pastikan reader mode:
- ✅ **Keyboard HID Mode** (paling umum)
- ✅ **Auto-enter** di akhir (recommended)
- ❌ Bukan mode serial/RS232

### 8. Testing Manual

```bash
# Login admin → Data Absensi → Start Reader

# Di input field, ketik manual:
ABC123456789

# Tekan Enter

# Akan proses kalau card UID terdaftar
```

### 9. Multiple Cards

Untuk scan banyak kartu berturut-turut:
1. Tap kartu 1 → tunggu message hijau
2. Input auto-clear (1.5 detik)
3. Tap kartu 2 → tunggu message
4. Dan seterusnya...

**Interval:** ~2 detik per kartu

### 10. Security

- Reader hanya aktif saat "Start Reader" diklik
- Input hanya menerima saat reader active
- Card UID auto-validate di server
- Tidak perlu authentication API untuk kemudahan

---

## 🔧 Config USB Reader (Advanced)

Jika reader punya software config:
- **Output mode:** Keyboard emulation
- **Suffix:** Enter (CR atau CR+LF)
- **Prefix:** None
- **Format:** HEX uppercase
- **Beep:** On (optional)

---

## ✅ Ready to Use!

**Workflow:**
```
[Colok USB Reader]
    ↓
[Login Admin]
    ↓
[Data Absensi]
    ↓
[Start Reader]
    ↓
[Tap Card]
    ↓
[Auto Process]
    ↓
[Done! ✅]
```

Selamat menggunakan! 🎉
