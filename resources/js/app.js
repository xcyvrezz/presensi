import './bootstrap';
// Alpine.js is bundled with Livewire 3, no need to import separately
// import Alpine from 'alpinejs';
// Temporary comment - PWA features disabled for PHP 8.0 compatibility
// import localforage from 'localforage';
// import { registerSW } from 'virtual:pwa-register';

// Alpine.js will be initialized by Livewire
// window.Alpine = Alpine;
// Alpine.start();

// Initialize LocalForage for offline storage (DISABLED - uncomment when needed)
/*
window.localforage = localforage;
localforage.config({
    driver: localforage.INDEXEDDB,
    name: 'absensi-mifare',
    version: 1.0,
    storeName: 'attendance_data',
    description: 'Offline storage for attendance data'
});
*/

// Register Service Worker for PWA (DISABLED - uncomment when needed)
/*
if ('serviceWorker' in navigator) {
    const updateSW = registerSW({
        onNeedRefresh() {
            if (confirm('Update tersedia! Reload untuk mendapatkan versi terbaru?')) {
                updateSW(true);
            }
        },
        onOfflineReady() {
            console.log('App siap digunakan offline');
        },
    });
}
*/

// NFC Detection (for mobile devices)
// Note: NFC Web API only works on Android Chrome
if ('NDEFReader' in window) {
    console.log('✅ NFC Web API tersedia (Mobile)');
    window.nfcAvailable = true;
} else {
    // This is normal for desktop browsers - RFID reader uses USB/keyboard input instead
    console.log('ℹ️ NFC Web API tidak tersedia (Desktop) - Gunakan USB RFID Reader');
    window.nfcAvailable = false;
}

// Geolocation Detection
if ('geolocation' in navigator) {
    console.log('✅ Geolocation API tersedia');
    window.geolocationAvailable = true;
} else {
    console.log('❌ Geolocation API tidak tersedia');
    window.geolocationAvailable = false;
}

