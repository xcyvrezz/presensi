# PROGRESSIVE WEB APP (PWA) SPECIFICATION
## Sistem Absensi MIFARE - SMK Negeri 10 Pandeglang

**Versi:** 2.0 - PWA Edition
**Tanggal:** 13 Desember 2025
**Architecture:** Laravel 11 + Livewire 3 + PWA

---

## 1. OVERVIEW

### 1.1 Perubahan Arsitektur

**Sebelumnya:** Flutter Native App (Cross-platform)
**Sekarang:** Progressive Web App (PWA) dengan Laravel Livewire

**Alasan Perubahan:**
✅ Single codebase (Laravel full-stack)
✅ No app store approval needed
✅ Instant updates (no download required)
✅ Works on all platforms (Android, iOS, Desktop)
✅ Easier maintenance (one team, one stack)
✅ Physical RFID readers already available

### 1.2 PWA Capabilities

| Feature | PWA Support | Implementation |
|---------|-------------|----------------|
| **Offline Mode** | ✅ Yes | Service Worker + IndexedDB |
| **Install to Homescreen** | ✅ Yes | Web App Manifest |
| **Push Notifications** | ✅ Yes | Web Push API |
| **NFC Reading** | ⚠️ Android Only | Web NFC API (Chrome 89+) |
| **GPS Location** | ✅ Yes | HTML5 Geolocation API |
| **Camera Access** | ✅ Yes | MediaDevices API (for evidence upload) |
| **Background Sync** | ✅ Yes | Background Sync API |

### 1.3 Platform Support

| Platform | Browser | NFC Support | GPS Support | PWA Install |
|----------|---------|-------------|-------------|-------------|
| **Android 8.0+** | Chrome 89+ | ✅ Web NFC | ✅ HTML5 Geo | ✅ Install |
| **Android 8.0+** | Edge Mobile | ✅ Web NFC | ✅ HTML5 Geo | ✅ Install |
| **iOS 13+** | Safari | ❌ Not Supported | ✅ HTML5 Geo | ✅ Install |
| **Desktop** | Chrome/Edge | ✅ (with reader) | ⚠️ Limited | ✅ Install |

**iOS Workaround:** Use physical RFID reader only (already available)

---

## 2. TECHNOLOGY STACK

### 2.1 Backend

```
Framework: Laravel 11
PHP: 8.2+
Database: MySQL 8.0+
Authentication: Laravel Sanctum (SPA mode)
Real-time: Laravel Echo + Pusher (optional)
Queue: Laravel Queue (for background jobs)
Cache: Redis (optional but recommended)
```

### 2.2 Frontend

```
Framework: Laravel Livewire 3
CSS: Tailwind CSS 3
Icons: Heroicons / Font Awesome
UI Components: Alpine.js (bundled with Livewire)
Charts: ApexCharts.js / Chart.js
Animations: CSS Animations + Alpine transitions
```

### 2.3 PWA Components

```
Service Worker: Workbox 7 (via Vite PWA plugin)
Manifest: Web App Manifest JSON
Storage: IndexedDB (via localForage.js)
Notifications: Web Push API
Build Tool: Vite 5 (Laravel default)
```

### 2.4 Hardware Integration

```
RFID Reader: MIFARE 13.56 MHz (USB/Serial)
NFC (Mobile): Web NFC API (Android only)
GPS: HTML5 Geolocation API
Camera: MediaDevices API (getUserMedia)
```

---

## 3. APPLICATION ARCHITECTURE

### 3.1 Folder Structure

```
absensi-mifare/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── API/                    # API untuk RFID reader
│   │   │   └── Web/                    # Web controllers
│   │   ├── Livewire/                   # Livewire components ⭐
│   │   │   ├── Auth/
│   │   │   │   ├── Login.php
│   │   │   │   └── Logout.php
│   │   │   ├── Student/
│   │   │   │   ├── Dashboard.php       # Student dashboard
│   │   │   │   ├── CheckIn.php         # NFC check-in component
│   │   │   │   ├── CheckOut.php        # NFC check-out component
│   │   │   │   ├── History.php         # Attendance history
│   │   │   │   └── Profile.php
│   │   │   ├── WaliKelas/
│   │   │   │   ├── Dashboard.php
│   │   │   │   ├── ManualAttendance.php
│   │   │   │   ├── ApprovalList.php
│   │   │   │   └── ClassReport.php
│   │   │   ├── Admin/
│   │   │   │   ├── Dashboard.php
│   │   │   │   ├── Students.php
│   │   │   │   ├── Locations.php       # Geofencing management
│   │   │   │   ├── Violations.php
│   │   │   │   └── Reports.php
│   │   │   └── Shared/
│   │   │       ├── LocationPicker.php   # Map component
│   │   │       └── Notifications.php
│   │   └── Middleware/
│   │       ├── CheckRole.php
│   │       ├── CheckPermission.php
│   │       └── CheckManualInputScope.php
│   ├── Models/                         # Eloquent models (19 tables)
│   ├── Services/                       # Business logic
│   │   ├── AttendanceService.php
│   │   ├── GeofencingService.php
│   │   ├── ManualAttendanceService.php
│   │   ├── ViolationService.php
│   │   └── AnomalyDetectionService.php
│   └── Console/
│       └── Commands/                   # Cron jobs
│           ├── DetectAlphaCommand.php
│           ├── DetectNoCheckoutCommand.php
│           └── SendNotificationsCommand.php
├── database/
│   ├── migrations/                     # 19 migrations
│   └── seeders/
├── public/
│   ├── sw.js                          # Service Worker ⭐
│   ├── manifest.json                  # Web App Manifest ⭐
│   └── icons/                         # PWA icons
│       ├── icon-72x72.png
│       ├── icon-96x96.png
│       ├── icon-128x128.png
│       ├── icon-144x144.png
│       ├── icon-152x152.png
│       ├── icon-192x192.png
│       ├── icon-384x384.png
│       └── icon-512x512.png
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php          # Main layout
│   │   │   └── guest.blade.php        # Login layout
│   │   ├── livewire/                  # Livewire blade views
│   │   │   ├── student/
│   │   │   │   ├── dashboard.blade.php
│   │   │   │   ├── check-in.blade.php
│   │   │   │   └── history.blade.php
│   │   │   └── ...
│   │   └── components/                # Blade components
│   ├── js/
│   │   ├── app.js
│   │   ├── nfc.js                     # Web NFC handler ⭐
│   │   ├── geolocation.js             # GPS handler ⭐
│   │   └── sw-registration.js         # Service Worker registration ⭐
│   └── css/
│       └── app.css                    # Tailwind CSS
└── routes/
    ├── web.php                        # Livewire routes
    └── api.php                        # API for RFID reader
```

### 3.2 Livewire Component Example

**Student Check-In Component:**

```php
// app/Http/Livewire/Student/CheckIn.php
<?php

namespace App\Http\Livewire\Student;

use Livewire\Component;
use App\Services\AttendanceService;
use App\Services\GeofencingService;

class CheckIn extends Component
{
    public $cardUid;
    public $latitude;
    public $longitude;
    public $accuracy;
    public $nearestLocation;
    public $distance;
    public $isGeofenceValid = false;
    public $isProcessing = false;
    public $message = '';
    public $messageType = ''; // success, error, warning

    protected $attendanceService;
    protected $geofencingService;

    public function boot(
        AttendanceService $attendanceService,
        GeofencingService $geofencingService
    ) {
        $this->attendanceService = $attendanceService;
        $this->geofencingService = $geofencingService;
    }

    // Called from JavaScript after NFC read
    public function processNfcTap($cardUid, $gpsData)
    {
        $this->isProcessing = true;
        $this->cardUid = $cardUid;
        $this->latitude = $gpsData['latitude'];
        $this->longitude = $gpsData['longitude'];
        $this->accuracy = $gpsData['accuracy'];

        // Validate GPS accuracy
        if ($this->accuracy > 50) {
            $this->message = 'GPS akurasi terlalu rendah (' . $this->accuracy . 'm). Pastikan Anda di area terbuka.';
            $this->messageType = 'warning';
            $this->isProcessing = false;
            return;
        }

        // Validate geofencing
        $geofenceResult = $this->geofencingService->validateLocation(
            $this->latitude,
            $this->longitude
        );

        if (!$geofenceResult['valid']) {
            $this->message = 'Anda berada di luar area sekolah (' . round($geofenceResult['distance']) . 'm dari lokasi terdekat)';
            $this->messageType = 'error';
            $this->isProcessing = false;
            return;
        }

        $this->isGeofenceValid = true;
        $this->nearestLocation = $geofenceResult['location_name'];
        $this->distance = $geofenceResult['distance'];

        // Process check-in
        try {
            $result = $this->attendanceService->checkIn([
                'card_uid' => $this->cardUid,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'location_id' => $geofenceResult['location_id'],
                'distance' => $this->distance,
                'method' => 'nfc_mobile',
                'device_info' => request()->userAgent(),
            ]);

            $this->message = 'Absen datang berhasil! Status: ' . $result['status_text'];
            $this->messageType = 'success';

            // Play success sound and confetti animation
            $this->dispatch('check-in-success');

        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            $this->messageType = 'error';
        }

        $this->isProcessing = false;
    }

    public function render()
    {
        return view('livewire.student.check-in');
    }
}
```

**Blade View:**

```blade
<!-- resources/views/livewire/student/check-in.blade.php -->
<div class="max-w-2xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Absen Datang</h2>
            <p class="text-gray-600 mt-2">Tap kartu MIFARE atau gunakan NFC smartphone</p>
        </div>

        <!-- Permission Status -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="flex items-center space-x-2">
                <span id="nfc-status-icon">⏳</span>
                <span class="text-sm text-gray-600">NFC Status</span>
            </div>
            <div class="flex items-center space-x-2">
                <span id="gps-status-icon">⏳</span>
                <span class="text-sm text-gray-600">GPS Status</span>
            </div>
        </div>

        <!-- NFC Scanner -->
        <div class="text-center mb-6">
            <button
                id="start-nfc-btn"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors"
                @if($isProcessing) disabled @endif
            >
                @if($isProcessing)
                    <span class="inline-block animate-spin mr-2">⏳</span>
                    Memproses...
                @else
                    📱 Tap Kartu Sekarang
                @endif
            </button>

            <!-- NFC Animation -->
            <div id="nfc-animation" class="hidden mt-6">
                <div class="animate-pulse">
                    <svg class="mx-auto h-32 w-32 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z"/>
                        <path d="M10 5a5 5 0 100 10 5 5 0 000-10zm0 8a3 3 0 110-6 3 3 0 010 6z"/>
                    </svg>
                </div>
                <p class="text-gray-600 mt-4">Dekatkan kartu MIFARE...</p>
            </div>
        </div>

        <!-- Message Display -->
        @if($message)
            <div class="mb-6 p-4 rounded-lg
                @if($messageType === 'success') bg-green-100 text-green-800
                @elseif($messageType === 'error') bg-red-100 text-red-800
                @else bg-yellow-100 text-yellow-800
                @endif
            ">
                {{ $message }}
                @if($isGeofenceValid)
                    <div class="mt-2 text-sm">
                        📍 Lokasi: {{ $nearestLocation }} ({{ round($distance, 1) }}m)
                    </div>
                @endif
            </div>
        @endif

        <!-- Instructions -->
        <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
            <h3 class="font-semibold mb-2">Cara Absen:</h3>
            <ol class="list-decimal list-inside space-y-1">
                <li>Pastikan NFC dan GPS aktif</li>
                <li>Pastikan Anda berada di area sekolah (radius 15m dari titik lokasi)</li>
                <li>Tap tombol "Tap Kartu Sekarang"</li>
                <li>Dekatkan kartu MIFARE ke smartphone (bagian belakang)</li>
                <li>Tunggu hingga muncul notifikasi sukses</li>
            </ol>
        </div>
    </div>
</div>

<!-- Confetti Animation -->
<canvas id="confetti-canvas" class="fixed inset-0 pointer-events-none z-50" style="display: none;"></canvas>

@push('scripts')
<script>
    // Web NFC Implementation
    document.addEventListener('DOMContentLoaded', function() {
        const startNfcBtn = document.getElementById('start-nfc-btn');
        const nfcAnimation = document.getElementById('nfc-animation');
        const nfcStatusIcon = document.getElementById('nfc-status-icon');
        const gpsStatusIcon = document.getElementById('gps-status-icon');

        // Check NFC availability
        if ('NDEFReader' in window) {
            nfcStatusIcon.textContent = '✅';
        } else {
            nfcStatusIcon.textContent = '❌';
            startNfcBtn.disabled = true;
            startNfcBtn.textContent = 'NFC Tidak Didukung';
            return;
        }

        // Check GPS permission and get location
        function getLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    gpsStatusIcon.textContent = '❌';
                    reject('GPS tidak didukung');
                    return;
                }

                gpsStatusIcon.textContent = '⏳';
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        gpsStatusIcon.textContent = '✅';
                        resolve({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: position.coords.accuracy
                        });
                    },
                    (error) => {
                        gpsStatusIcon.textContent = '❌';
                        reject('GPS error: ' + error.message);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }

        // NFC Scan
        startNfcBtn.addEventListener('click', async function() {
            try {
                // Get GPS location first
                nfcAnimation.classList.remove('hidden');
                startNfcBtn.disabled = true;

                const gpsData = await getLocation();

                // Start NFC reading
                const ndef = new NDEFReader();
                await ndef.scan();

                ndef.addEventListener('reading', ({ message, serialNumber }) => {
                    const cardUid = serialNumber; // UID kartu

                    // Send to Livewire component
                    @this.call('processNfcTap', cardUid, gpsData);

                    nfcAnimation.classList.add('hidden');
                });

            } catch (error) {
                alert('Error: ' + error);
                nfcAnimation.classList.add('hidden');
                startNfcBtn.disabled = false;
            }
        });

        // Listen for success event
        Livewire.on('check-in-success', () => {
            // Play success sound
            const audio = new Audio('/sounds/success.mp3');
            audio.play();

            // Show confetti
            showConfetti();

            // Redirect after 3 seconds
            setTimeout(() => {
                window.location.href = '/student/dashboard';
            }, 3000);
        });
    });

    // Confetti animation function
    function showConfetti() {
        const canvas = document.getElementById('confetti-canvas');
        canvas.style.display = 'block';
        // ... confetti animation code ...
    }
</script>
@endpush
```

---

## 4. PWA CONFIGURATION

### 4.1 Web App Manifest

**File:** `public/manifest.json`

```json
{
  "name": "Absensi MIFARE - SMK Negeri 10 Pandeglang",
  "short_name": "Absensi MIFARE",
  "description": "Sistem absensi berbasis NFC dan GPS untuk SMK Negeri 10 Pandeglang",
  "start_url": "/",
  "scope": "/",
  "display": "standalone",
  "orientation": "portrait",
  "background_color": "#ffffff",
  "theme_color": "#1e40af",
  "icons": [
    {
      "src": "/icons/icon-72x72.png",
      "sizes": "72x72",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/icons/icon-96x96.png",
      "sizes": "96x96",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/icons/icon-128x128.png",
      "sizes": "128x128",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/icons/icon-144x144.png",
      "sizes": "144x144",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/icons/icon-152x152.png",
      "sizes": "152x152",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/icons/icon-384x384.png",
      "sizes": "384x384",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any maskable"
    }
  ],
  "categories": ["education", "productivity"],
  "screenshots": [
    {
      "src": "/screenshots/dashboard.png",
      "sizes": "540x720",
      "type": "image/png"
    },
    {
      "src": "/screenshots/check-in.png",
      "sizes": "540x720",
      "type": "image/png"
    }
  ],
  "prefer_related_applications": false
}
```

### 4.2 Service Worker (Workbox)

**Installation:**

```bash
npm install -D vite-plugin-pwa workbox-window
```

**Configuration:** `vite.config.js`

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: ['favicon.ico', 'apple-touch-icon.png', 'masked-icon.svg'],
            manifest: {
                // ... manifest content from above
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,jpg,jpeg,webp}'],
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/api\.*/i,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 60 * 60 * 24 // 1 day
                            },
                            cacheableResponse: {
                                statuses: [0, 200]
                            }
                        }
                    },
                    {
                        urlPattern: /\.(?:png|jpg|jpeg|svg|gif|webp)$/,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'image-cache',
                            expiration: {
                                maxEntries: 100,
                                maxAgeSeconds: 60 * 60 * 24 * 30 // 30 days
                            }
                        }
                    }
                ]
            }
        })
    ],
});
```

### 4.3 Offline Support with IndexedDB

**Library:** `localForage.js`

```bash
npm install localforage
```

**Usage:**

```javascript
// resources/js/offline.js
import localforage from 'localforage';

// Initialize IndexedDB
localforage.config({
    driver: localforage.INDEXEDDB,
    name: 'absensi-mifare',
    version: 1.0,
    storeName: 'attendance_cache'
});

// Save attendance data for offline
export async function cacheAttendanceData(data) {
    await localforage.setItem('dashboard_data', data);
    await localforage.setItem('last_sync', new Date().toISOString());
}

// Retrieve cached data
export async function getCachedData() {
    const data = await localforage.getItem('dashboard_data');
    const lastSync = await localforage.getItem('last_sync');
    return { data, lastSync };
}

// Check online status
export function isOnline() {
    return navigator.onLine;
}

// Sync when back online
window.addEventListener('online', async () => {
    console.log('Back online, syncing data...');
    // Implement sync logic
});
```

---

## 5. WEB NFC API IMPLEMENTATION

### 5.1 NFC Detection and Reading

```javascript
// resources/js/nfc.js

class NFCHandler {
    constructor() {
        this.ndef = null;
        this.isScanning = false;
    }

    // Check if NFC is supported
    async isSupported() {
        if ('NDEFReader' in window) {
            return true;
        }
        return false;
    }

    // Request NFC permission and start scanning
    async startScan(onSuccess, onError) {
        if (!await this.isSupported()) {
            onError('NFC tidak didukung di browser ini');
            return;
        }

        try {
            this.ndef = new NDEFReader();
            await this.ndef.scan();
            this.isScanning = true;

            this.ndef.addEventListener('reading', ({ message, serialNumber }) => {
                const cardUid = serialNumber;

                // Parse NDEF message (optional, for additional data)
                const records = message.records.map(record => ({
                    recordType: record.recordType,
                    data: new TextDecoder().decode(record.data)
                }));

                onSuccess({
                    cardUid: cardUid,
                    records: records,
                    timestamp: new Date().toISOString()
                });

                this.stopScan();
            });

            this.ndef.addEventListener('readingerror', (error) => {
                onError('Error membaca NFC: ' + error);
                this.stopScan();
            });

        } catch (error) {
            onError('Gagal memulai NFC: ' + error.message);
        }
    }

    // Stop scanning
    stopScan() {
        this.isScanning = false;
        // NDEFReader doesn't have explicit stop, just remove listeners
    }

    // Write to NFC tag (untuk admin - assign card UID)
    async writeTag(data) {
        try {
            const ndef = new NDEFReader();
            await ndef.write({
                records: [
                    {
                        recordType: "text",
                        data: data
                    }
                ]
            });
            return true;
        } catch (error) {
            console.error('Error writing NFC:', error);
            return false;
        }
    }
}

export default NFCHandler;
```

### 5.2 Integration with Livewire

```javascript
// resources/js/app.js
import NFCHandler from './nfc';
import { getGPSLocation } from './geolocation';

const nfcHandler = new NFCHandler();

// Global function accessible from Livewire components
window.startNFCScan = async function(livewireComponent) {
    // Check support
    if (!await nfcHandler.isSupported()) {
        alert('NFC tidak didukung di browser/perangkat Anda. Gunakan Chrome di Android 8.0+');
        return;
    }

    // Get GPS location first
    try {
        const gpsData = await getGPSLocation();

        // Start NFC scan
        nfcHandler.startScan(
            // On Success
            (nfcData) => {
                // Call Livewire component method
                livewireComponent.call('processNfcTap', nfcData.cardUid, gpsData);
            },
            // On Error
            (error) => {
                livewireComponent.call('handleNfcError', error);
            }
        );
    } catch (gpsError) {
        alert('GPS Error: ' + gpsError);
    }
};
```

---

## 6. GEOLOCATION API IMPLEMENTATION

### 6.1 GPS Handler

```javascript
// resources/js/geolocation.js

export function getGPSLocation(options = {}) {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject('Geolocation tidak didukung');
            return;
        }

        const defaultOptions = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        };

        const finalOptions = { ...defaultOptions, ...options };

        navigator.geolocation.getCurrentPosition(
            (position) => {
                resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    altitude: position.coords.altitude,
                    heading: position.coords.heading,
                    speed: position.coords.speed,
                    timestamp: position.timestamp
                });
            },
            (error) => {
                let errorMessage = '';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Izin lokasi ditolak. Aktifkan di pengaturan browser.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Lokasi tidak tersedia. Pastikan GPS aktif.';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'Timeout mendapatkan lokasi. Coba lagi.';
                        break;
                    default:
                        errorMessage = 'Error GPS: ' + error.message;
                }
                reject(errorMessage);
            },
            finalOptions
        );
    });
}

// Watch position (untuk tracking real-time)
export function watchGPSLocation(onSuccess, onError, options = {}) {
    if (!navigator.geolocation) {
        onError('Geolocation tidak didukung');
        return null;
    }

    const defaultOptions = {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 1000
    };

    const finalOptions = { ...defaultOptions, ...options };

    const watchId = navigator.geolocation.watchPosition(
        (position) => {
            onSuccess({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy
            });
        },
        (error) => {
            onError('GPS Error: ' + error.message);
        },
        finalOptions
    );

    return watchId;
}

// Stop watching
export function stopWatchingGPS(watchId) {
    if (watchId) {
        navigator.geolocation.clearWatch(watchId);
    }
}

// Calculate distance between two GPS points (Haversine formula)
export function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Earth radius in meters
    const φ1 = lat1 * Math.PI / 180;
    const φ2 = lat2 * Math.PI / 180;
    const Δφ = (lat2 - lat1) * Math.PI / 180;
    const Δλ = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ / 2) * Math.sin(Δλ / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    const distance = R * c; // Distance in meters
    return distance;
}
```

---

## 7. USER INTERFACE (UI/UX)

### 7.1 Responsive Design with Tailwind CSS

**Mobile-First Approach:**

```blade
<!-- Mobile optimized layout -->
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Content adapts to screen size -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Cards -->
    </div>
</div>
```

### 7.2 Components Library

**Reusable Blade Components:**

```blade
<!-- resources/views/components/button.blade.php -->
@props([
    'variant' => 'primary', // primary, secondary, danger, success
    'size' => 'md', // sm, md, lg
    'type' => 'button'
])

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "
        inline-flex items-center justify-center font-medium rounded-lg
        transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2
        " . match($variant) {
            'primary' => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
            'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-800 focus:ring-gray-500',
            'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
            'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
            default => 'bg-gray-100 hover:bg-gray-200 text-gray-800'
        } . ' ' . match($size) {
            'sm' => 'px-3 py-1.5 text-sm',
            'md' => 'px-4 py-2 text-base',
            'lg' => 'px-6 py-3 text-lg',
            default => 'px-4 py-2'
        }
    "]) }}
>
    {{ $slot }}
</button>
```

**Usage:**

```blade
<x-button variant="primary" size="lg">
    Absen Sekarang
</x-button>
```

### 7.3 Loading States

```blade
<!-- Livewire loading indicator -->
<div wire:loading class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Memproses...</p>
    </div>
</div>
```

---

## 8. INSTALLATION FLOW

### 8.1 Add to Homescreen Prompt

```javascript
// resources/js/pwa-install.js

let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent default browser install prompt
    e.preventDefault();
    deferredPrompt = e;

    // Show custom install button
    const installButton = document.getElementById('install-app-btn');
    if (installButton) {
        installButton.style.display = 'block';
    }
});

async function installApp() {
    if (!deferredPrompt) {
        return;
    }

    // Show the install prompt
    deferredPrompt.prompt();

    // Wait for user response
    const { outcome } = await deferredPrompt.userChoice;

    if (outcome === 'accepted') {
        console.log('User accepted the install prompt');
    } else {
        console.log('User dismissed the install prompt');
    }

    // Clear the deferredPrompt
    deferredPrompt = null;

    // Hide install button
    document.getElementById('install-app-btn').style.display = 'none';
}

// Detect if app is installed
window.addEventListener('appinstalled', () => {
    console.log('PWA was installed');
    // Hide install button
    document.getElementById('install-app-btn').style.display = 'none';
});

// Export for global use
window.installApp = installApp;
```

### 8.2 Install UI Component

```blade
<!-- Show on first visit only -->
<div id="install-banner" class="fixed bottom-0 inset-x-0 pb-2 sm:pb-5 z-50">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
        <div class="p-2 rounded-lg bg-blue-600 shadow-lg sm:p-3">
            <div class="flex items-center justify-between flex-wrap">
                <div class="w-0 flex-1 flex items-center">
                    <span class="flex p-2 rounded-lg bg-blue-800">
                        📱
                    </span>
                    <p class="ml-3 font-medium text-white truncate">
                        <span class="md:hidden">
                            Install aplikasi untuk akses lebih cepat!
                        </span>
                        <span class="hidden md:inline">
                            Install Absensi MIFARE ke homescreen untuk pengalaman lebih baik!
                        </span>
                    </p>
                </div>
                <div class="order-3 mt-2 flex-shrink-0 w-full sm:order-2 sm:mt-0 sm:w-auto">
                    <button
                        id="install-app-btn"
                        onclick="installApp()"
                        class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-blue-600 bg-white hover:bg-blue-50"
                    >
                        Install Sekarang
                    </button>
                </div>
                <div class="order-2 flex-shrink-0 sm:order-3 sm:ml-2">
                    <button
                        onclick="document.getElementById('install-banner').style.display='none'"
                        class="flex p-2 rounded-md hover:bg-blue-500 focus:outline-none"
                    >
                        <span class="sr-only">Dismiss</span>
                        ✕
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 9. PERFORMANCE OPTIMIZATION

### 9.1 Lazy Loading

```blade
<!-- Lazy load images -->
<img src="placeholder.jpg" data-src="actual-image.jpg" loading="lazy" alt="Student Photo">

<!-- Lazy load Livewire components -->
@livewire('student.history', lazy: true)
```

### 9.2 Code Splitting

```javascript
// vite.config.js
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['alpinejs', 'axios'],
                    'nfc': ['./resources/js/nfc.js'],
                    'maps': ['./resources/js/geolocation.js']
                }
            }
        }
    }
});
```

### 9.3 Caching Strategy

```javascript
// Service Worker caching
workbox.routing.registerRoute(
    ({ request }) => request.destination === 'document',
    new workbox.strategies.NetworkFirst({
        cacheName: 'html-cache',
        plugins: [
            new workbox.expiration.ExpirationPlugin({
                maxEntries: 10,
                maxAgeSeconds: 60 * 60 // 1 hour
            })
        ]
    })
);
```

---

## 10. TESTING

### 10.1 PWA Testing Checklist

- [ ] Manifest.json valid (use Lighthouse)
- [ ] Service Worker registered and working
- [ ] Offline mode functional (cache dashboard data)
- [ ] Install to homescreen works (Android & iOS)
- [ ] Icons display correctly (all sizes)
- [ ] Theme color applied
- [ ] NFC reading works (Android Chrome)
- [ ] GPS permission requested and functional
- [ ] Push notifications work
- [ ] Responsive on all screen sizes (320px - 2560px)
- [ ] Performance score > 90 (Lighthouse)
- [ ] Accessibility score > 90 (Lighthouse)
- [ ] Best Practices score > 90 (Lighthouse)
- [ ] SEO score > 90 (Lighthouse)

### 10.2 Browser Testing

| Browser | Version | NFC | GPS | PWA Install | Status |
|---------|---------|-----|-----|-------------|--------|
| Chrome Android | 89+ | ✅ | ✅ | ✅ | **Primary** |
| Edge Mobile | Latest | ✅ | ✅ | ✅ | Supported |
| Safari iOS | 13+ | ❌ | ✅ | ✅ | Limited (no NFC) |
| Chrome Desktop | Latest | ⚠️ | ⚠️ | ✅ | Admin only |
| Firefox Android | Latest | ❌ | ✅ | ✅ | GPS only |

### 10.3 Device Testing

**Minimum:**
- Samsung Galaxy A series (Android 10+)
- Xiaomi Redmi Note series (Android 10+)
- Oppo/Vivo mid-range (Android 10+)
- iPhone 11+ (iOS 13+) - GPS only

**Test Scenarios:**
1. Fresh install (first-time user)
2. Offline mode
3. Poor network (3G/Edge)
4. Low battery mode
5. GPS in indoor vs outdoor
6. NFC with different card orientations
7. Multiple rapid taps
8. Session timeout

---

## 11. DEPLOYMENT

### 11.1 Production Checklist

- [ ] HTTPS enabled (required for PWA & NFC)
- [ ] SSL certificate valid
- [ ] Service Worker deployed
- [ ] Manifest.json accessible
- [ ] All icons generated (72px - 512px)
- [ ] Environment variables configured
- [ ] Database optimized (indexes, queries)
- [ ] Caching configured (Redis recommended)
- [ ] Queue workers running (for cron jobs)
- [ ] Error logging enabled (Sentry/Bugsnag)
- [ ] Analytics configured (Google Analytics / Plausible)
- [ ] Backup automation configured
- [ ] Monitoring set up (uptime, performance)

### 11.2 HTTPS Requirement

**PWA & Web NFC require HTTPS:**

```bash
# Using Let's Encrypt (Free)
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d absensi.smkn10pandeglang.sch.id
```

### 11.3 Build Command

```bash
# Production build
npm run build

# Laravel optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 12. COMPARISON: PWA vs Flutter Native

| Feature | PWA (Choosen) | Flutter Native |
|---------|---------------|----------------|
| **Development Cost** | Lower (single codebase) | Higher (separate team) |
| **Maintenance** | Easier (web stack) | More complex (native) |
| **Distribution** | Instant (no app store) | App Store approval needed |
| **Updates** | Instant (refresh) | User must download |
| **NFC Support** | Android only (Web NFC) | Both Android & iOS |
| **Offline Mode** | Service Worker | Built-in |
| **Performance** | Good (Livewire) | Better (native) |
| **Installation** | Optional | Required |
| **Size** | ~5MB (cached) | ~20-50MB APK/IPA |
| **Team Required** | 1 full-stack dev | 2 devs (backend + mobile) |
| **Time to Market** | Faster | Slower |

**Decision:** PWA chosen because:
✅ Lower cost
✅ Faster development
✅ No app store approval
✅ Instant updates
✅ **Physical RFID readers already available** (iOS workaround not critical)

---

**Document Control:**
- **Author:** PWA Architect
- **Version:** 2.0 PWA Edition
- **Status:** Ready for Implementation
- **Next:** Update Project Roadmap for PWA development

