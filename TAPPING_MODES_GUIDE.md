# Tapping Station - Input Modes Guide

## Overview

Tapping Station mendukung **3 mode input** untuk membaca kartu MIFARE:

1. **🔌 USB RFID Reader** (Keyboard Emulation Mode)
2. **📱 NFC HP** (Web NFC API)
3. **📡 API Polling** (External Reader/Simulator)

Semua mode berjalan **simultan** - sistem akan otomatis detect dari mana kartu di-tap.

---

## Mode 1: USB RFID Reader 🔌

### Cara Kerja
USB reader bertindak sebagai **keyboard** - ketika scan kartu, reader akan "mengetik" Card UID secara otomatis.

### Setup
1. **Colokkan USB Reader** ke komputer
2. **Buka Tapping Station** di browser
3. **Scan kartu** - UID akan otomatis terdeteksi
4. Tidak perlu konfigurasi tambahan!

### Format Card UID yang Didukung
Reader bisa kirim dalam berbagai format:
- `04ABCDEF123480` (hex tanpa separator)
- `04:AB:CD:EF:12:34:80` (dengan colon)
- `04-AB-CD-EF-12-34-80` (dengan dash)

Sistem akan otomatis normalize format.

### Troubleshooting

**❌ Card tidak terdeteksi:**
- Pastikan reader tercolok dan ada lampu indikator
- Test reader di Notepad - scan kartu, apakah muncul UID?
- Jika muncul di Notepad, berarti reader OK
- Refresh tapping station (Ctrl+Shift+R)

**❌ UID salah/terpotong:**
- Reader mungkin kirim terlalu cepat
- System akan buffer input selama 2 detik
- Pastikan reader kirim Enter di akhir

**Console Logs:**
```javascript
⌨️ Setting up USB Reader (Keyboard Mode)...
✅ USB Reader ready
⌨️ USB Reader detected card: 04ABCDEF123480
📤 Processing card via Livewire: 04ABCDEF123480
```

---

## Mode 2: NFC HP 📱

### Cara Kerja
Menggunakan **Web NFC API** (Chrome Android) untuk baca kartu MIFARE langsung dari HP.

### Requirements
- **HP Android** dengan NFC
- **Chrome Browser** versi 89+
- **HTTPS** (atau localhost untuk development)

### Setup

1. **Buka HP Android**, aktifkan NFC di Settings

2. **Buka Chrome**, akses tapping station:
   ```
   http://[IP-KOMPUTER]/absensi-mifare/public
   ```

3. **Izinkan NFC** - Chrome akan minta permission

4. **Tap kartu ke belakang HP** (dekat kamera biasanya)

### Permission Request

Saat pertama kali, browser akan minta izin:
```
[Tapping Station] wants to use NFC

[Block] [Allow]
```

Klik **Allow**.

### Test NFC HP

Untuk cek apakah HP support NFC:

1. Buka Chrome di HP
2. Akses: `chrome://flags/#enable-experimental-web-platform-features`
3. Set ke **Enabled**
4. Restart Chrome
5. Buka tapping station

**Console akan show:**
```javascript
📱 NFC Web API tersedia!
✅ NFC Reader started (HP mode)
```

### Troubleshooting

**❌ NFC Web API tidak tersedia:**
- Pastikan pakai **Chrome Android** (bukan Firefox/Samsung Internet)
- Pastikan **NFC aktif** di Settings HP
- Test di: `chrome://flags` → enable experimental features

**❌ Permission denied:**
- Chrome akan block jika bukan HTTPS
- Development: gunakan `http://localhost` atau `http://127.0.0.1`
- Production: wajib HTTPS

**❌ Kartu tidak terbaca:**
- Tempel kartu lebih lama (1-2 detik)
- Cari lokasi antenna NFC HP (biasanya dekat kamera belakang)
- Lepas casing HP jika terlalu tebal

**Console Logs:**
```javascript
📱 NFC Web API tersedia!
✅ NFC Reader started (HP mode)
📱 NFC HP detected card: 04:ab:cd:ef:12:34:80
📤 Processing card via Livewire: 04:ab:cd:ef:12:34:80
```

---

## Mode 3: API Polling 📡

### Cara Kerja
Frontend polling API setiap 500ms untuk cek kartu baru yang dilaporkan via API endpoint.

### Use Cases
- **External RFID Reader** dengan software sendiri
- **Network-based Reader** yang kirim HTTP request
- **Testing** dengan simulator
- **Custom Integration** dari sistem lain

### API Endpoints

#### 1. Report Card (POST)
External reader/simulator kirim card UID ke sistem:

```bash
POST /api/rfid/report-card
Content-Type: application/json

{
  "card_uid": "04:AB:CD:EF:12:34:80"
}
```

Response:
```json
{
  "success": true,
  "card_uid": "04:AB:CD:EF:12:34:80",
  "message": "Card reported successfully"
}
```

#### 2. Get Last Card (GET)
Frontend polling endpoint ini untuk ambil kartu terakhir:

```bash
GET /api/rfid/last-card
```

Response (ada kartu):
```json
{
  "success": true,
  "card_uid": "04:AB:CD:EF:12:34:80"
}
```

Response (tidak ada kartu):
```json
{
  "success": false,
  "card_uid": null,
  "message": "No card detected"
}
```

#### 3. Clear Card (POST)
Clear card dari cache:

```bash
POST /api/rfid/clear-card
```

### Cache Mechanism

Card UID disimpan di cache dengan **3 detik TTL**:
- Reader POST card → Cache
- Frontend GET card dari cache setiap 500ms
- Setelah 3 detik, cache auto-expire

### Using RFID Simulator

Untuk testing tanpa hardware:

1. **Buka Simulator:**
   ```
   http://localhost/absensi-mifare/public/rfid-simulator.html
   ```

2. **Get Sample Card UID:**
   ```
   http://localhost/absensi-mifare/public/get-cards.php
   ```

3. **Paste UID** ke simulator

4. **Click "Tap Card"**

5. **Buka Tapping Station** - card akan terdeteksi

### Custom Integration Example

**Python Script:**
```python
import serial
import requests

# Read from serial port
ser = serial.Serial('COM3', 9600)

while True:
    if ser.in_waiting > 0:
        card_uid = ser.readline().decode('utf-8').strip()

        # Send to API
        response = requests.post(
            'http://localhost/absensi-mifare/public/api/rfid/report-card',
            json={'card_uid': card_uid}
        )

        print(f"Card {card_uid}: {response.json()}")
```

**Node.js Script:**
```javascript
const SerialPort = require('serialport');
const axios = require('axios');

const port = new SerialPort('COM3', { baudRate: 9600 });

port.on('data', async (data) => {
  const cardUid = data.toString().trim();

  const response = await axios.post(
    'http://localhost/absensi-mifare/public/api/rfid/report-card',
    { card_uid: cardUid }
  );

  console.log(`Card ${cardUid}:`, response.data);
});
```

### Console Logs

```javascript
📡 Polling URL: http://localhost/absensi-mifare/public/api/rfid/last-card
🔄 Starting polling...
✅ Polling started (every 500ms)
📊 Polling count: 10
🔍 Polling... http://localhost/absensi-mifare/public/api/rfid/last-card
📥 Poll response: {success: false, card_uid: null}
🎴 Card detected: 04:AB:CD:EF:12:34:80
📤 Processing card via Livewire: 04:AB:CD:EF:12:34:80
```

---

## Multi-Mode Operation

### Semua Mode Jalan Bersamaan

Tapping station **mendengarkan semua 3 mode simultan**:

```
┌─────────────────────────────────────┐
│      TAPPING STATION (Browser)      │
├─────────────────────────────────────┤
│                                     │
│  [USB Reader] ─────┐                │
│                     │                │
│  [NFC HP] ──────────┼─→ Process Card│
│                     │                │
│  [API Polling] ─────┘                │
│                                     │
└─────────────────────────────────────┘
```

### Deduplication

Sistem otomatis prevent double-processing:
- Jika card UID sama dengan yang terakhir, skip
- Last card UID di-clear setelah 3 detik
- Bisa scan card yang sama lagi setelah 3 detik

### Console Output

Ketika tapping station ready, console akan show:
```
✅ tappingStation() function loaded
🔄 Tapping Station Initialized
📡 Polling URL: http://localhost/absensi-mifare/public/api/rfid/last-card
🔄 Starting polling...
⌨️ Setting up USB Reader (Keyboard Mode)...
✅ USB Reader ready
ℹ️ NFC Web API tidak tersedia (gunakan Chrome Android)
✅ Polling started (every 500ms)
✅ All input modes initialized
```

---

## Testing All Modes

### Test 1: USB Reader

1. Open tapping station di desktop
2. Scan kartu dengan USB reader
3. Console should show:
   ```
   ⌨️ USB Reader detected card: 04ABCDEF123480
   📤 Processing card via Livewire
   ```

### Test 2: NFC HP

1. Open tapping station di Chrome Android
2. Tap kartu ke belakang HP
3. Console should show:
   ```
   📱 NFC HP detected card: 04:ab:cd:ef:12:34:80
   📤 Processing card via Livewire
   ```

### Test 3: API Polling

1. Open tapping station
2. Open simulator in another tab
3. Click "Tap Card"
4. Console should show:
   ```
   🎴 Card detected: 04:AB:CD:EF:12:34:80
   📤 Processing card via Livewire
   ```

---

## Production Deployment

### Desktop Kiosk (USB Reader)
- Install di PC dengan monitor touchscreen
- Colokkan USB RFID reader
- Browser full-screen mode (F11)
- Auto-start on boot

### Mobile Kiosk (NFC HP)
- Mount HP Android di wall holder
- Install Chrome, pin tapping station
- Kiosk mode app (fully-kiosk-browser)
- Students tap card dengan HP mereka atau HP kiosk

### Hybrid Setup
- Desktop dengan USB reader di pintu masuk
- Mobile app dengan NFC untuk staff roaming
- API polling untuk integrasi dengan sistem lain

---

**Created:** 2025-12-26
**Version:** 1.0
**Status:** Production Ready
