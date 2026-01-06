<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Check-Out</h2>
        <p class="text-sm text-gray-600 mt-1">Tap kartu NFC Anda untuk melakukan absensi pulang</p>
    </div>

    <!-- Status Message -->
    @if($message)
        <div class="mb-6 p-4 rounded-lg {{ $isSuccess ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500' }}">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($isSuccess)
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium {{ $isSuccess ? 'text-green-800' : 'text-red-800' }}">
                        {{ $message }}
                    </p>
                    @if($isSuccess && $attendanceData)
                        <div class="mt-2 text-sm {{ $isSuccess ? 'text-green-700' : 'text-red-700' }}">
                            <p>Waktu: {{ $attendanceData['check_out_time'] }}</p>
                            <p>Total Jam: {{ $attendanceData['total_hours'] }} jam</p>
                            <p>Status: <span class="font-semibold">{{ ucfirst($attendanceData['status']) }}</span></p>
                            @if($attendanceData['early_leave_minutes'] > 0)
                                <p>Pulang Cepat: {{ $attendanceData['early_leave_minutes'] }} menit</p>
                            @endif
                            <p>Persentase: {{ $attendanceData['percentage'] }}%</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- NFC Scan Card -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="text-center">
            <!-- NFC Icon Animation -->
            <div class="mx-auto mb-6 relative" id="nfc-icon-container">
                <div class="w-32 h-32 mx-auto bg-orange-50 rounded-full flex items-center justify-center" id="nfc-icon">
                    <svg class="w-16 h-16 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <!-- Pulse animation rings -->
                <div class="absolute inset-0 flex items-center justify-center" id="pulse-rings" style="display: none;">
                    <div class="animate-ping absolute inline-flex h-32 w-32 rounded-full bg-orange-400 opacity-75"></div>
                    <div class="animate-ping absolute inline-flex h-24 w-24 rounded-full bg-orange-400 opacity-50" style="animation-delay: 0.2s;"></div>
                </div>
            </div>

            <!-- Scan Button -->
            <button
                type="button"
                id="scan-button"
                wire:loading.attr="disabled"
                wire:target="processCheckOut"
                class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-4 px-6 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="processCheckOut">
                    <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Scan Kartu NFC
                </span>
                <span wire:loading wire:target="processCheckOut" class="inline-flex items-center">
                    <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                </span>
            </button>

            <p class="mt-4 text-sm text-gray-500">
                Pastikan GPS dan NFC sudah aktif
            </p>
        </div>

        <!-- GPS Status -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center" id="gps-status">
                    <div class="w-3 h-3 rounded-full bg-gray-300 mr-2" id="gps-indicator"></div>
                    <span class="text-gray-600" id="gps-text">Mendeteksi GPS...</span>
                </div>
                <div class="flex items-center" id="nfc-status">
                    <div class="w-3 h-3 rounded-full {{ window.nfcAvailable ? 'bg-green-500' : 'bg-red-500' }} mr-2"></div>
                    <span class="text-gray-600">NFC: {{ window.nfcAvailable ? 'Tersedia' : 'Tidak Tersedia' }}</span>
                </div>
            </div>
            @if($latitude && $longitude)
                <div class="mt-2 text-xs text-gray-500">
                    Lokasi: {{ number_format($latitude, 6) }}, {{ number_format($longitude, 6) }}
                    @if($accuracy)
                        (Akurasi: {{ round($accuracy) }}m)
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Instructions -->
    <div class="bg-orange-50 rounded-lg p-4">
        <h3 class="font-semibold text-orange-900 mb-2">Cara Check-Out:</h3>
        <ol class="text-sm text-orange-800 space-y-1 list-decimal list-inside">
            <li>Pastikan GPS dan NFC aktif di smartphone Anda</li>
            <li>Klik tombol "Scan Kartu NFC"</li>
            <li>Tempelkan kartu MIFARE Anda ke bagian belakang smartphone</li>
            <li>Tunggu hingga proses selesai</li>
        </ol>
    </div>
</div>

@script
<script>
    let nfcReader = null;

    // Initialize GPS tracking
    function initGPS() {
        if (!navigator.geolocation) {
            updateGPSStatus(false, 'GPS tidak tersedia');
            return;
        }

        // Watch position for continuous tracking
        navigator.geolocation.watchPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                // Update Livewire component
                $wire.setGpsData(lat, lon, accuracy);

                // Update UI
                updateGPSStatus(true, 'GPS aktif', accuracy);
            },
            (error) => {
                console.error('GPS Error:', error);
                updateGPSStatus(false, 'GPS error: ' + error.message);
            },
            {
                enableHighAccuracy: true,
                maximumAge: 10000,
                timeout: 5000
            }
        );
    }

    function updateGPSStatus(active, text, accuracy = null) {
        const indicator = document.getElementById('gps-indicator');
        const textEl = document.getElementById('gps-text');

        if (indicator && textEl) {
            indicator.className = `w-3 h-3 rounded-full mr-2 ${active ? 'bg-green-500' : 'bg-red-500'}`;
            textEl.textContent = text + (accuracy ? ` (${Math.round(accuracy)}m)` : '');
        }
    }

    // NFC Scan Handler
    async function scanNFC() {
        if (!('NDEFReader' in window)) {
            alert('NFC tidak didukung di browser ini. Gunakan Chrome di Android.');
            return;
        }

        try {
            // Show scanning animation
            showScanningAnimation();

            nfcReader = new NDEFReader();
            await nfcReader.scan();

            console.log('NFC scan started...');

            nfcReader.addEventListener('reading', ({ message, serialNumber }) => {
                console.log('NFC card detected:', serialNumber);

                // Set card UID in Livewire component
                $wire.setCardUid(serialNumber);

                // Stop scanning animation
                hideScanningAnimation();

                // Process check-out
                $wire.processCheckOut();

                // Vibrate on success
                if (navigator.vibrate) {
                    navigator.vibrate(200);
                }
            });

            nfcReader.addEventListener('readingerror', () => {
                console.error('NFC read error');
                hideScanningAnimation();
                alert('Gagal membaca kartu NFC. Silakan coba lagi.');
            });

        } catch (error) {
            console.error('NFC Error:', error);
            hideScanningAnimation();

            if (error.name === 'NotAllowedError') {
                alert('Izin NFC ditolak. Aktifkan NFC di pengaturan browser.');
            } else {
                alert('Error NFC: ' + error.message);
            }
        }
    }

    function showScanningAnimation() {
        document.getElementById('pulse-rings').style.display = 'flex';
        document.getElementById('nfc-icon').classList.add('scale-110');
    }

    function hideScanningAnimation() {
        document.getElementById('pulse-rings').style.display = 'none';
        document.getElementById('nfc-icon').classList.remove('scale-110');
    }

    // Event Listeners
    document.getElementById('scan-button')?.addEventListener('click', scanNFC);

    // Initialize GPS on page load
    initGPS();

    // Listen for Livewire events
    $wire.on('checkOutSuccess', (event) => {
        console.log('Check-out successful:', event.data);

        // Play success sound (optional)
        const audio = new Audio('/sounds/success.mp3');
        audio.play().catch(() => {});

        // Vibrate
        if (navigator.vibrate) {
            navigator.vibrate([200, 100, 200]);
        }

        // Reset after 5 seconds
        setTimeout(() => {
            $wire.resetForm();
        }, 5000);
    });

    $wire.on('checkOutFailed', (event) => {
        console.log('Check-out failed:', event.message);

        // Vibrate error pattern
        if (navigator.vibrate) {
            navigator.vibrate([100, 50, 100, 50, 100]);
        }
    });
</script>
@endscript
