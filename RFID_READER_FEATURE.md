# RFID Card Reader Feature - Admin Attendance

## 📋 Overview

Fitur ini memungkinkan admin untuk melakukan absensi siswa dengan cara **tap kartu MIFARE** langsung dari halaman admin. Sistem akan secara otomatis mendeteksi kartu yang di-tap dan memproses absensi (check-in atau check-out).

## ✨ Fitur

- ✅ **Auto-detect** kartu MIFARE yang di-tap
- ✅ **Real-time processing** - Langsung proses absensi
- ✅ **Smart detection** - Otomatis check-in atau check-out
- ✅ **Visual feedback** - Status dan pesan real-time
- ✅ **Prevent duplicate** - Mencegah pemrosesan ganda

## 🔧 Cara Kerja

### 1. Arsitektur System

```
[Physical RFID Reader]
        ↓ (HTTP POST)
[API: /api/rfid/report-card]
        ↓ (Cache)
[Laravel Cache Store]
        ↓ (Polling GET)
[Admin Interface JS]
        ↓ (Livewire)
[Process Attendance]
```

### 2. Flow Process

1. **Physical RFID Reader** mendeteksi kartu dan mengirim UID ke API `/api/rfid/report-card`
2. API menyimpan UID ke **Cache** selama 3 detik
3. **Admin Interface** melakukan polling ke `/api/rfid/last-card` setiap 500ms
4. Jika ada card baru, **Livewire** memproses absensi
5. Sistem menentukan **check-in** atau **check-out** otomatis

## 🚀 Cara Menggunakan

### Untuk Admin:

1. Login sebagai **Admin**
2. Buka menu **Data Absensi**
3. Klik tombol **"Start Reader"** pada card RFID Reader (biru)
4. Status berubah menjadi **"RFID Reader aktif..."**
5. **Tap kartu MIFARE** ke reader
6. Sistem otomatis proses absensi
7. Lihat pesan konfirmasi (hijau = sukses, merah = error, kuning = warning)
8. Klik **"Stop Reader"** untuk menghentikan

### Dengan Simulator (Testing):

```bash
# Install Python requests (jika belum)
pip install requests

# Run simulator
python rfid_simulator.py

# Masukkan Card UID test
Enter Card UID: 0123456789AB
```

## 📡 API Endpoints

### 1. Report Card (dari hardware)
```http
POST /api/rfid/report-card
Content-Type: application/json

{
  "card_uid": "0123456789AB"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Card UID received",
  "card_uid": "0123456789AB"
}
```

### 2. Get Last Card (polling dari admin)
```http
GET /api/rfid/last-card
```

**Response (ada card):**
```json
{
  "success": true,
  "card_uid": "0123456789AB",
  "timestamp": 1703500000
}
```

**Response (no card):**
```json
{
  "success": true,
  "card_uid": null,
  "timestamp": null
}
```

### 3. Clear Card Cache
```http
POST /api/rfid/clear-card
```

**Response:**
```json
{
  "success": true,
  "message": "Cache cleared"
}
```

## 🔌 Integrasi dengan Hardware RFID Reader

### Arduino/ESP32 Example:

```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <MFRC522.h>

#define RST_PIN 22
#define SS_PIN 21

MFRC522 mfrc522(SS_PIN, RST_PIN);
const char* apiUrl = "http://192.168.1.100/absensi-mifare/public/api/rfid/report-card";

void setup() {
  Serial.begin(115200);
  SPI.begin();
  mfrc522.PCD_Init();
  WiFi.begin("SSID", "PASSWORD");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
}

void loop() {
  if (!mfrc522.PICC_IsNewCardPresent() || !mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  String cardUID = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    cardUID += String(mfrc522.uid.uidByte[i], HEX);
  }
  cardUID.toUpperCase();

  // Send to API
  HTTPClient http;
  http.begin(apiUrl);
  http.addHeader("Content-Type", "application/json");

  String payload = "{\"card_uid\":\"" + cardUID + "\"}";
  int httpCode = http.POST(payload);

  if (httpCode == 200) {
    Serial.println("✅ Card sent: " + cardUID);
  }

  http.end();
  delay(2000); // Prevent duplicate
}
```

### Raspberry Pi Example (Python):

```python
import requests
from pirc522 import RFID

API_URL = "http://192.168.1.100/absensi-mifare/public/api/rfid/report-card"
rdr = RFID()

while True:
    rdr.wait_for_tag()
    (error, tag_type) = rdr.request()

    if not error:
        (error, uid) = rdr.anticoll()
        if not error:
            card_uid = ''.join([hex(x)[2:].upper() for x in uid])

            # Send to API
            response = requests.post(API_URL, json={'card_uid': card_uid})
            if response.status_code == 200:
                print(f"✅ Card sent: {card_uid}")
```

## 🧪 Testing

### 1. Test dengan Simulator:
```bash
python rfid_simulator.py
```

### 2. Test Manual API:
```bash
# Send card UID
curl -X POST http://localhost/absensi-mifare/public/api/rfid/report-card \
  -H "Content-Type: application/json" \
  -d '{"card_uid":"0123456789AB"}'

# Get last card
curl http://localhost/absensi-mifare/public/api/rfid/last-card
```

### 3. Lihat Console Browser:
- Buka DevTools (F12)
- Lihat Console
- Akan ada log: `🔵 RFID Reader started` saat start
- Dan: `💳 Card detected: xxxxx` saat card terdeteksi

## 📊 Status Messages

| Type | Color | Meaning |
|------|-------|---------|
| **Success** | 🟢 Green | Check-in/out berhasil |
| **Error** | 🔴 Red | Kartu tidak terdaftar atau error |
| **Warning** | 🟡 Yellow | Sudah absen hari ini |
| **Info** | 🔵 Blue | Reader aktif, menunggu kartu |

## 🔒 Security Notes

- API endpoint **tidak memerlukan authentication** untuk kemudahan hardware
- Validasi card UID dilakukan di server
- Cache timeout 3 detik untuk keamanan
- Duplikasi dicegah dengan tracking last processed card

## ⚡ Performance

- **Polling interval**: 500ms (2x per detik)
- **Cache TTL**: 3 detik
- **Response time**: < 100ms untuk deteksi card
- **Auto-clear**: Card cache otomatis clear setelah 3 detik

## 🐛 Troubleshooting

### Reader tidak mendeteksi kartu
1. Cek browser console untuk error
2. Pastikan tombol "Start Reader" sudah diklik
3. Test API manual dengan curl

### Kartu terdeteksi tapi tidak diproses
1. Cek apakah card UID terdaftar di database
2. Cek tabel `students` kolom `card_uid`
3. Lihat message error di interface

### Duplikasi absensi
- Sistem sudah mencegah duplikasi
- Last card UID di-reset setelah 2 detik

## 📝 Developer Notes

### File yang Dimodifikasi:
- `app/Livewire/Admin/Attendance/AttendanceIndex.php` - Added RFID methods
- `resources/views/livewire/admin/attendance/attendance-index.blade.php` - Added UI & JS
- `app/Http/Controllers/Api/RfidReaderController.php` - NEW: API controller
- `routes/api.php` - Added RFID routes

### Database Dependencies:
- `students.card_uid` - Must be populated with valid card UIDs
- `attendances` - Records created automatically

### Cache Keys:
- `rfid_last_card_uid` - Last detected card UID
- `rfid_last_card_timestamp` - Detection timestamp

---

**Created:** December 2025
**Version:** 1.0.0
**Author:** Laravel Absensi MIFARE Team
