# MOBILE APPLICATION SPECIFICATION
## Sistem Absensi MIFARE - SMK Negeri 10 Pandeglang

**Versi:** 1.0
**Tanggal:** 13 Desember 2025
**Platform:** Android & iOS

---

## 1. OVERVIEW

### 1.1 Tujuan Mobile App
Mobile application ini memungkinkan siswa SMK Negeri 10 Pandeglang untuk melakukan absensi menggunakan smartphone mereka dengan teknologi NFC (Near Field Communication) dan validasi lokasi GPS (Geofencing).

### 1.2 Target Platform
- **Android:** 8.0 Oreo (API 26) ke atas
- **iOS:** 13.0 ke atas
- **Development Stack (Recommended):**
  - **Option 1:** Progressive Web App (PWA) - Cost-effective, single codebase
  - **Option 2:** Flutter - Cross-platform native
  - **Option 3:** React Native - Cross-platform with JavaScript

### 1.3 Pengguna
- **Primary:** Siswa (1500+ users)
- **Secondary:** Wali Kelas dan Admin (via responsive web)

---

## 2. FUNCTIONAL REQUIREMENTS

### 2.1 Authentication & Authorization

#### F-AUTH-001: Login Siswa
- **Input:**
  - NIS atau Email
  - Password
- **Process:**
  - POST request ke `/api/auth/login`
  - Server validasi kredensial
  - Server return JWT token + user data
  - App simpan token di secure storage
- **Output:**
  - Navigate to Dashboard
  - Display user info (name, class, photo)
- **Error Handling:**
  - Invalid credentials: "NIS/Password salah"
  - Account inactive: "Akun Anda tidak aktif, hubungi admin"
  - Network error: "Tidak dapat terhubung ke server"

#### F-AUTH-002: Auto-Login (Remember Me)
- Simpan token di secure storage (Keychain/Keystore)
- Auto-refresh token before expiry
- Logout otomatis jika token invalid

#### F-AUTH-003: Logout
- Clear stored token
- Clear cached data
- Navigate to Login screen

---

### 2.2 Permission Management

#### F-PERM-001: Request NFC Permission
- Check device NFC capability
- Request NFC enable jika disabled
- Show tutorial cara enable NFC

#### F-PERM-002: Request Location Permission
- Request "Allow all the time" atau "While using the app"
- Explain why GPS diperlukan (geofencing validation)
- Fallback: tidak bisa absen jika permission denied

#### F-PERM-003: Permission Status Indicator
- Visual indicator (icon/color) untuk:
  - ✅ NFC Enabled
  - ❌ NFC Disabled
  - ✅ GPS Enabled & Accurate
  - ⚠️ GPS Enabled tapi akurasi rendah
  - ❌ GPS Disabled

---

### 2.3 Dashboard

#### F-DASH-001: Home Screen
- Display informasi siswa:
  - Nama, NIS, Kelas, Jurusan
  - Foto profil
- Display status hari ini:
  - Sudah absen datang? Jam berapa? (Tepat waktu/Terlambat)
  - Sudah absen pulang? Jam berapa? (Normal/Pulang cepat)
  - Belum absen? Reminder to tap
- Quick Action Buttons:
  - **"ABSEN DATANG"** (jika belum check-in)
  - **"ABSEN PULANG"** (jika sudah check-in tapi belum check-out)
  - **"LIHAT RIWAYAT"**

#### F-DASH-002: Statistics Card
- Total kehadiran bulan ini
- Total keterlambatan bulan ini
- Persentase kehadiran semester ini
- Badge/Achievement (optional)

#### F-DASH-003: Calendar View
- Mini calendar showing:
  - ✅ Hari hadir
  - ❌ Hari alpha
  - 🏥 Hari sakit
  - 📝 Hari izin
  - 📅 Hari libur
- Tap tanggal untuk detail

---

### 2.4 Absensi (Core Feature)

#### F-ABS-001: Absen Datang via NFC + GPS
**Pre-conditions:**
- User sudah login
- Belum absen datang hari ini
- Waktu antara 06:00 - 08:30
- NFC enabled
- GPS enabled

**Flow:**
1. User tap button "ABSEN DATANG"
2. App cek pre-conditions:
   - Jika waktu invalid: "Absensi datang hanya dapat dilakukan pukul 06:00-08:30"
   - Jika sudah absen: "Anda sudah absen datang hari ini"
   - Jika NFC disabled: Navigate ke Settings
   - Jika GPS disabled: Request permission
3. App request current GPS location
4. App show loading indicator: "Mendapatkan lokasi..."
5. Saat GPS fix (accuracy ≤ 20m):
   - Display map dengan marker current location
   - Display jarak ke lokasi absensi terdekat
   - Show NFC reader screen: "Tap kartu Anda"
6. User tap kartu MIFARE
7. App baca Card UID
8. App kirim data ke server:
   ```json
   POST /api/attendance/check-in
   {
     "card_uid": "04A1B2C3D4E5F6",
     "latitude": -6.123456,
     "longitude": 106.123456,
     "accuracy": 12.5,
     "device_info": {
       "model": "Samsung Galaxy A52",
       "os": "Android 12",
       "nfc_type": "HCE"
     }
   }
   ```
9. Server response:
   - **Success (200):**
     ```json
     {
       "success": true,
       "message": "Absensi berhasil",
       "data": {
         "time": "07:25:00",
         "status": "tepat_waktu",
         "location_name": "Gerbang Utama",
         "distance": 8.5
       }
     }
     ```
     App display success dialog dengan confetti animation
   - **Error (422):**
     ```json
     {
       "success": false,
       "message": "Anda berada di luar area absensi",
       "data": {
         "nearest_location": "Gerbang Utama",
         "distance": 45.2
       }
     }
     ```
     App display error dengan petunjuk
10. App refresh dashboard

#### F-ABS-002: Absen Pulang via NFC + GPS
- Similar dengan F-ABS-001
- Waktu valid: 15:00 - 18:00
- Pre-condition tambahan: Sudah absen datang

#### F-ABS-003: Fallback: Tanpa Kartu (Card UID dari Device)
- Jika siswa lupa bawa kartu fisik
- Gunakan virtual card UID yang di-assign ke device
- Admin assign device_id ke student di web admin

---

### 2.5 History & Reports

#### F-HIST-001: Riwayat Absensi
- List view dengan infinite scroll / pagination
- Filter:
  - Bulan ini
  - Bulan lalu
  - Custom range (date picker)
- Card per hari menampilkan:
  - Tanggal
  - Status: Hadir / Alpha / Izin / Sakit
  - Jam datang & pulang (jika hadir)
  - Metode: Kartu fisik / Mobile
  - Lokasi absensi
  - Terlambat berapa menit (jika ada)
- Tap card untuk detail lengkap

#### F-HIST-002: Detail Absensi
- Full information:
  - Tanggal dan hari
  - Check-in time, status, late minutes
  - Check-out time, status, early minutes
  - Metode absensi
  - Lokasi absensi + map marker
  - Jarak dari titik lokasi
  - Device info (jika via mobile)

#### F-HIST-003: Monthly Summary
- Ringkasan per bulan:
  - Total hari efektif
  - Total hadir
  - Total alpha, izin, sakit
  - Total keterlambatan (menit & jam)
  - Persentase kehadiran
- Visual: Progress bar / donut chart

---

### 2.6 Profile & Settings

#### F-PROF-001: View Profile
- Display:
  - Foto profil
  - Nama lengkap
  - NIS, NISN
  - Kelas, Jurusan
  - Nomor WhatsApp
  - Email

#### F-PROF-002: Edit Profile
- Editable fields:
  - Foto profil (upload from gallery/camera)
  - Nomor WhatsApp
  - Email
- Save changes via API

#### F-PROF-003: Change Password
- Input old password
- Input new password (min 8 char, konfirmasi)
- Validate & update via API

#### F-PROF-004: App Settings
- Enable/Disable notifikasi
- Theme: Light / Dark / System
- Bahasa: Indonesia / English

---

## 3. NON-FUNCTIONAL REQUIREMENTS

### 3.1 Performance
- App launch time: < 2 detik
- NFC tap response: < 1 detik
- GPS fix time: < 5 detik (normal conditions)
- API call response: < 2 detik
- Smooth scrolling: 60 FPS minimum

### 3.2 Offline Capability
- Cache last fetched data (dashboard, history)
- Show cached data saat offline
- Display "Offline Mode" banner
- Queue absensi requests (jika feasible)
- Sync saat online kembali

### 3.3 Security
- HTTPS only untuk semua API calls
- JWT token dengan expiry
- Secure storage untuk token (Keychain/Keystore)
- Certificate pinning (optional, recommended)
- Detect rooted/jailbroken device (warning)
- GPS spoofing detection

### 3.4 Usability
- Material Design (Android) / Human Interface Guidelines (iOS)
- Intuitive navigation
- Clear error messages dengan actionable steps
- Loading indicators untuk semua async operations
- Haptic feedback untuk NFC tap
- Accessibility: Support screen readers

### 3.5 Reliability
- Graceful error handling
- Retry mechanism untuk network errors
- Crash reporting (Firebase Crashlytics)
- Analytics tracking (Firebase Analytics)

---

## 4. USER INTERFACE DESIGN

### 4.1 Screen Flow

```
Splash Screen
    ↓
Login Screen
    ↓
Dashboard (Home)
    ├─→ Absen Datang → NFC Scanner → Success/Error
    ├─→ Absen Pulang → NFC Scanner → Success/Error
    ├─→ Riwayat → List View → Detail View
    ├─→ Profile → Edit Profile
    └─→ Settings
```

### 4.2 Color Scheme (Recommended)
- **Primary:** #2196F3 (Blue) - SMK theme
- **Secondary:** #4CAF50 (Green) - Success
- **Error:** #F44336 (Red)
- **Warning:** #FF9800 (Orange)
- **Background:** #FFFFFF / #121212 (Light/Dark)

### 4.3 Key UI Components

#### Login Screen
```
┌─────────────────────────────────┐
│        [Logo Sekolah]           │
│   SMK NEGERI 10 PANDEGLANG      │
│                                 │
│   ┌───────────────────────┐     │
│   │ NIS/Email             │     │
│   └───────────────────────┘     │
│   ┌───────────────────────┐     │
│   │ Password    [👁]      │     │
│   └───────────────────────┘     │
│   ☐ Ingat Saya                  │
│                                 │
│   ┌───────────────────────┐     │
│   │     MASUK             │     │
│   └───────────────────────┘     │
│                                 │
│   Lupa Password?                │
└─────────────────────────────────┘
```

#### Dashboard Screen
```
┌─────────────────────────────────┐
│ ☰  Dashboard          [👤]      │
├─────────────────────────────────┤
│ Halo, Ahmad Rizki               │
│ X PPLG 1                        │
│                                 │
│ ┌─────────────────────────────┐ │
│ │ Status Hari Ini             │ │
│ │ Rabu, 13 Desember 2025      │ │
│ │                             │ │
│ │ ✅ Datang: 07:25 (Tepat waktu)│ │
│ │ ⏱️ Pulang: Belum             │ │
│ └─────────────────────────────┘ │
│                                 │
│ ┌───────────────────────────┐   │
│ │  📍 ABSEN PULANG          │   │
│ │                           │   │
│ │  [Tap untuk scan NFC]     │   │
│ └───────────────────────────┘   │
│                                 │
│ Statistik Bulan Ini:            │
│ • Hadir: 18 hari                │
│ • Terlambat: 2x                 │
│ • Kehadiran: 95%                │
│                                 │
│ ┌───────────────────────────┐   │
│ │  LIHAT RIWAYAT            │   │
│ └───────────────────────────┘   │
└─────────────────────────────────┘
Bottom Nav: [🏠 Home] [📊 Riwayat] [👤 Profile]
```

#### NFC Scanner Screen
```
┌─────────────────────────────────┐
│ ←  Absen Datang                 │
├─────────────────────────────────┤
│                                 │
│   Status GPS:                   │
│   ✅ Aktif (Akurasi: 12m)       │
│                                 │
│   Lokasi Terdekat:              │
│   📍 Gerbang Utama (8.5m)       │
│                                 │
│   ┌─────────────────────────┐   │
│   │                         │   │
│   │    [Map Preview]        │   │
│   │    with marker          │   │
│   │                         │   │
│   └─────────────────────────┘   │
│                                 │
│   ┌─────────────────────────┐   │
│   │                         │   │
│   │     📱                  │   │
│   │                         │   │
│   │  Tempelkan kartu        │   │
│   │  MIFARE Anda            │   │
│   │                         │   │
│   │  [NFC wave animation]   │   │
│   │                         │   │
│   └─────────────────────────┘   │
│                                 │
│   [ BATAL ]                     │
└─────────────────────────────────┘
```

#### Success Dialog
```
┌─────────────────────────────────┐
│                                 │
│        ✅ [Confetti]            │
│                                 │
│    Absensi Berhasil!            │
│                                 │
│  Datang: 07:25 WIB              │
│  Status: Tepat Waktu            │
│  Lokasi: Gerbang Utama          │
│  Jarak: 8.5 meter               │
│                                 │
│   ┌───────────────────────┐     │
│   │       OK              │     │
│   └───────────────────────┘     │
└─────────────────────────────────┘
```

---

## 5. TECHNICAL IMPLEMENTATION

### 5.1 Technology Stack (Recommendation)

#### Option 1: Progressive Web App (PWA)
**Pros:**
- Single codebase untuk Android & iOS
- Mudah deploy & update
- Cost-effective
- Access NFC via Web NFC API (Chrome Android)

**Cons:**
- NFC support limited (Android Chrome only, iOS not support Web NFC)
- GPS accuracy might be less than native
- Offline capability terbatas

**Stack:**
- Frontend: Vue.js / React
- PWA Framework: Workbox
- UI: Vuetify / Material-UI
- NFC: Web NFC API
- GPS: Geolocation API
- Storage: IndexedDB / LocalStorage

**Verdict:** ❌ Not recommended karena iOS tidak support Web NFC

#### Option 2: Flutter (Cross-platform Native)
**Pros:**
- Single Dart codebase
- Native performance
- Full access ke NFC & GPS hardware
- Hot reload untuk development
- Material Design built-in

**Cons:**
- Learning curve untuk Dart
- App size lebih besar (~20-30 MB)

**Stack:**
- Framework: Flutter 3.x
- Language: Dart
- NFC: `flutter_nfc_kit` atau `nfc_manager` package
- GPS: `geolocator` package
- HTTP: `dio` package
- State Management: Riverpod / Bloc
- Storage: `flutter_secure_storage` + `hive`
- Maps: `google_maps_flutter`

**Verdict:** ✅ **RECOMMENDED** - Best balance of performance & development efficiency

#### Option 3: React Native
**Pros:**
- JavaScript/TypeScript (familiar untuk web dev)
- Large ecosystem
- Hot reload

**Cons:**
- NFC support kurang mature
- Bridge overhead untuk native modules

**Stack:**
- Framework: React Native 0.72+
- Language: TypeScript
- NFC: `react-native-nfc-manager`
- GPS: `react-native-geolocation-service`
- HTTP: `axios`
- State: Redux Toolkit / Zustand
- Maps: `react-native-maps`

**Verdict:** ⚠️ Alternative option, tapi Flutter lebih recommended untuk NFC use case

---

### 5.2 NFC Implementation (Flutter Example)

```dart
import 'package:nfc_manager/nfc_manager.dart';

Future<String?> readMifareCard() async {
  try {
    // Check NFC availability
    bool isAvailable = await NfcManager.instance.isAvailable();
    if (!isAvailable) {
      throw Exception('NFC tidak tersedia di perangkat ini');
    }

    // Start NFC session
    String? cardUid;
    await NfcManager.instance.startSession(
      onDiscovered: (NfcTag tag) async {
        // Get tag identifier
        var identifier = tag.data['nfca']?['identifier'] ??
                        tag.data['nfcb']?['identifier'];

        if (identifier != null) {
          // Convert bytes to hex string
          cardUid = identifier.map((byte) =>
            byte.toRadixString(16).padLeft(2, '0')).join('');

          // Stop session
          await NfcManager.instance.stopSession();
        }
      },
    );

    return cardUid;
  } catch (e) {
    throw Exception('Error membaca kartu: $e');
  }
}
```

### 5.3 GPS / Geofencing Implementation (Flutter Example)

```dart
import 'package:geolocator/geolocator.dart';
import 'dart:math';

class GeofencingService {
  // Haversine formula to calculate distance between two GPS coordinates
  double calculateDistance(double lat1, double lon1, double lat2, double lon2) {
    const double earthRadius = 6371000; // meters

    double dLat = _toRadians(lat2 - lat1);
    double dLon = _toRadians(lon2 - lon1);

    double a = sin(dLat / 2) * sin(dLat / 2) +
               cos(_toRadians(lat1)) * cos(_toRadians(lat2)) *
               sin(dLon / 2) * sin(dLon / 2);

    double c = 2 * atan2(sqrt(a), sqrt(1 - a));

    return earthRadius * c; // distance in meters
  }

  double _toRadians(double degree) {
    return degree * pi / 180;
  }

  Future<Position> getCurrentLocation() async {
    // Check permissions
    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw Exception('GPS tidak aktif');
    }

    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        throw Exception('Permission GPS ditolak');
      }
    }

    // Get current position with high accuracy
    Position position = await Geolocator.getCurrentPosition(
      desiredAccuracy: LocationAccuracy.high,
      timeLimit: Duration(seconds: 10),
    );

    return position;
  }

  // Validate if user is within allowed geofence
  Future<Map<String, dynamic>> validateGeofence(
    double userLat,
    double userLon,
    List<AttendanceLocation> locations
  ) async {
    double minDistance = double.infinity;
    AttendanceLocation? nearestLocation;

    for (var location in locations) {
      double distance = calculateDistance(
        userLat, userLon,
        location.latitude, location.longitude
      );

      if (distance < minDistance) {
        minDistance = distance;
        nearestLocation = location;
      }
    }

    bool isValid = minDistance <= (nearestLocation?.radius ?? 15);

    return {
      'valid': isValid,
      'nearest_location': nearestLocation,
      'distance': minDistance,
    };
  }
}
```

### 5.4 API Integration (Flutter Example)

```dart
import 'package:dio/dio.dart';

class AttendanceService {
  final Dio _dio;

  AttendanceService(this._dio);

  Future<AttendanceResponse> checkIn({
    required String cardUid,
    required double latitude,
    required double longitude,
    required double accuracy,
  }) async {
    try {
      final response = await _dio.post('/api/attendance/check-in', data: {
        'card_uid': cardUid,
        'latitude': latitude,
        'longitude': longitude,
        'accuracy': accuracy,
        'device_info': {
          'model': await _getDeviceModel(),
          'os': await _getOS(),
          'nfc_type': 'HCE',
        },
      });

      return AttendanceResponse.fromJson(response.data);
    } on DioError catch (e) {
      if (e.response?.statusCode == 422) {
        throw ValidationException(e.response?.data['message']);
      } else if (e.type == DioErrorType.connectTimeout) {
        throw NetworkException('Koneksi timeout, coba lagi');
      } else {
        throw Exception('Gagal melakukan absensi');
      }
    }
  }
}
```

---

## 6. TESTING STRATEGY

### 6.1 Unit Testing
- Test business logic functions
- Test data models & parsers
- Test API service methods
- Coverage target: 80%+

### 6.2 Widget Testing (Flutter)
- Test UI components
- Test user interactions
- Test navigation flows

### 6.3 Integration Testing
- Test full attendance flow (end-to-end)
- Test with mock NFC tags
- Test with mock GPS coordinates

### 6.4 Device Testing
- Test on multiple Android devices (Samsung, Xiaomi, Oppo)
- Test on iOS devices (iPhone 11+)
- Test NFC reading reliability
- Test GPS accuracy in different conditions

### 6.5 Beta Testing
- Internal testing dengan 20-30 siswa
- Gather feedback
- Fix bugs before full rollout

---

## 7. DEPLOYMENT

### 7.1 Android (Google Play Store)
- Package name: `com.smkn10pandeglang.absensi`
- Minimum SDK: 26 (Android 8.0)
- Target SDK: 34 (Android 14)
- App signing: Google Play App Signing
- Release track: Internal → Closed Beta → Open Beta → Production

### 7.2 iOS (App Store)
- Bundle ID: `com.smkn10pandeglang.absensi`
- Minimum iOS: 13.0
- TestFlight untuk beta testing
- App Store review guidelines compliance

### 7.3 CI/CD
- Use GitHub Actions / GitLab CI
- Automated build untuk setiap commit
- Automated testing
- Automated deployment ke beta tracks

---

## 8. MAINTENANCE & UPDATES

### 8.1 Update Strategy
- **Bug fixes:** Immediate patch release
- **Feature updates:** Monthly release cycle
- **Major version:** Semester-based

### 8.2 Monitoring
- Crash reporting: Firebase Crashlytics
- Analytics: Firebase Analytics
- Performance monitoring: Firebase Performance
- User feedback: In-app feedback form

### 8.3 Support
- FAQ di dalam app
- WhatsApp support group (optional)
- Admin contact via app

---

## 9. COST ESTIMATION

### 9.1 Development Cost (Estimation)
- **Flutter Development:** 2-3 bulan @ 1-2 developers
- **UI/UX Design:** 2 minggu @ 1 designer
- **Testing & QA:** 2 minggu
- **Total:** ~3-4 bulan development time

### 9.2 Infrastructure Cost (Yearly)
- **Google Play Developer:** $25 (one-time)
- **Apple Developer:** $99/year
- **Firebase (free tier):** $0 (sufficient untuk 1500 users)
- **Google Maps API:** ~$50-100/month (depending on usage)
- **Total:** ~$700-1300/year

---

## 10. SUCCESS METRICS

### 10.1 KPIs
- **Adoption Rate:** 80% siswa menggunakan mobile app dalam 1 bulan
- **Daily Active Users:** 70%+ dari total siswa
- **Crash-free Rate:** 99%+
- **Average Session Duration:** 2-3 menit
- **NFC Success Rate:** 95%+ (successful tap on first try)

### 10.2 User Satisfaction
- In-app rating: Target 4.5+ stars
- User feedback survey: 80%+ satisfied
- Support ticket volume: < 5% dari total users

---

**Document Control:**
- **Author:** Mobile App Architect
- **Reviewer:** -
- **Approved by:** -

