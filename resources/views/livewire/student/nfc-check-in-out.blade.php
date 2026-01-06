<div x-data="nfcReader()" x-init="init()">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📱 NFC Absensi Mobile</h1>
        <p class="text-gray-600 mt-1">{{ $student->full_name }} - {{ $student->nis }}</p>
        <p class="text-sm text-gray-500 mt-1">Kelas: {{ $student->class->name }} ({{ $student->class->department->code }})</p>
    </div>

    <!-- NFC Support Check -->
    <div x-show="!nfcSupported" class="mb-6 p-6 bg-red-50 border-l-4 border-red-500 rounded-xl">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-red-500 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-red-800 font-semibold mb-2">NFC Tidak Didukung</h3>
                <p class="text-red-700 text-sm">Browser Anda tidak mendukung NFC Web API. Fitur ini hanya tersedia di Android Chrome.</p>
                <p class="text-red-600 text-xs mt-2">💡 Gunakan Chrome di HP Android untuk menggunakan fitur NFC.</p>
            </div>
        </div>
    </div>

    <!-- Status Message -->
    @if($statusMessage)
        <div class="mb-6 p-4 rounded-xl border-l-4
            {{ $statusType === 'success' ? 'bg-green-50 border-green-500' : '' }}
            {{ $statusType === 'error' ? 'bg-red-50 border-red-500' : '' }}
            {{ $statusType === 'warning' ? 'bg-yellow-50 border-yellow-500' : '' }}
            {{ $statusType === 'info' ? 'bg-blue-50 border-blue-500' : '' }}">
            <p class="font-medium
                {{ $statusType === 'success' ? 'text-green-700' : '' }}
                {{ $statusType === 'error' ? 'text-red-700' : '' }}
                {{ $statusType === 'warning' ? 'text-yellow-700' : '' }}
                {{ $statusType === 'info' ? 'text-blue-700' : '' }}">
                {{ $statusMessage }}
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- NFC Reader Interface -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl shadow-2xl p-8 text-white">
            <h3 class="text-xl font-bold mb-6 text-center">📱 Tap Kartu NFC Anda</h3>

            <!-- NFC Animation -->
            <div class="relative mb-8 flex items-center justify-center h-64">
                <!-- Animated Rings -->
                <div x-show="isScanning" class="absolute inset-0 flex items-center justify-center">
                    <div class="absolute w-32 h-32 border-4 border-white rounded-full opacity-20 animate-ping"></div>
                    <div class="absolute w-48 h-48 border-4 border-white rounded-full opacity-20 animate-ping" style="animation-delay: 0.2s;"></div>
                    <div class="absolute w-64 h-64 border-4 border-white rounded-full opacity-20 animate-ping" style="animation-delay: 0.4s;"></div>
                </div>

                <!-- NFC Icon -->
                <div class="relative z-10">
                    <svg class="w-32 h-32" :class="{ 'animate-pulse': isScanning }" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                    </svg>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
                @if($canCheckIn)
                    <button @click="startNfcScan('check-in')"
                            :disabled="isScanning || !nfcSupported"
                            class="w-full py-4 bg-white text-blue-700 rounded-xl font-bold text-lg shadow-lg hover:bg-blue-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        <span x-text="isScanning ? 'Tap Kartu Sekarang...' : 'Check-In dengan NFC'"></span>
                    </button>
                @elseif($canCheckOut)
                    <button @click="startNfcScan('check-out')"
                            :disabled="isScanning || !nfcSupported"
                            class="w-full py-4 bg-white text-blue-700 rounded-xl font-bold text-lg shadow-lg hover:bg-blue-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span x-text="isScanning ? 'Tap Kartu Sekarang...' : 'Check-Out dengan NFC'"></span>
                    </button>
                @else
                    <div class="w-full py-4 bg-white/20 text-white rounded-xl font-bold text-center">
                        Absensi Tidak Tersedia
                    </div>
                @endif

                <button @click="cancelScan()" x-show="isScanning"
                        class="w-full py-3 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition-all">
                    Batal
                </button>
            </div>

            <!-- Status Text -->
            <div class="mt-6 text-center">
                <p x-show="isScanning" class="text-white/90 text-sm animate-pulse">
                    ⚡ Dekatkan kartu NFC ke belakang HP Anda...
                </p>
                <p x-show="!isScanning && nfcSupported" class="text-white/70 text-xs">
                    Pastikan NFC di HP Anda sudah aktif
                </p>
            </div>
        </div>

        <!-- Today's Attendance Info -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">📊 Absensi Hari Ini</h3>

            @if($todayAttendance)
                <!-- Check-in Info -->
                <div class="mb-6 p-4 bg-green-50 rounded-xl border border-green-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-green-700">Check-In</span>
                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                            {{ $todayAttendance->status === 'hadir' ? 'TEPAT WAKTU' : 'TERLAMBAT' }}
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $todayAttendance->check_in_time ? substr($todayAttendance->check_in_time, 0, 5) : '-' }}
                    </p>
                    @if($todayAttendance->late_minutes > 0)
                        <p class="text-xs text-red-600 mt-1">Terlambat {{ $todayAttendance->late_minutes }} menit</p>
                    @endif
                </div>

                <!-- Check-out Info -->
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-blue-700">Check-Out</span>
                        @if($todayAttendance->check_out_time)
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">SELESAI</span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full">BELUM</span>
                        @endif
                    </div>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $todayAttendance->check_out_time ? substr($todayAttendance->check_out_time, 0, 5) : '-' }}
                    </p>
                    @if($todayAttendance->check_out_time && $todayAttendance->early_leave_minutes > 0)
                        <p class="text-xs text-yellow-600 mt-1">Pulang {{ $todayAttendance->early_leave_minutes }} menit lebih awal</p>
                    @endif
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500">Belum ada absensi hari ini</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Instructions -->
    <div class="mt-8 bg-blue-50 rounded-xl p-6 border border-blue-200">
        <h3 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Cara Menggunakan NFC
        </h3>
        <ul class="space-y-2 text-sm text-blue-800">
            <li class="flex items-start gap-2">
                <span class="font-bold">1.</span>
                <span>Pastikan NFC di HP Android Anda sudah <strong>aktif</strong> (Settings → Connection → NFC)</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="font-bold">2.</span>
                <span>Buka halaman ini menggunakan <strong>Chrome Browser</strong></span>
            </li>
            <li class="flex items-start gap-2">
                <span class="font-bold">3.</span>
                <span>Klik tombol <strong>"Check-In dengan NFC"</strong> atau <strong>"Check-Out dengan NFC"</strong></span>
            </li>
            <li class="flex items-start gap-2">
                <span class="font-bold">4.</span>
                <span>Dekatkan <strong>kartu MIFARE</strong> ke belakang HP Anda</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="font-bold">5.</span>
                <span>Tunggu hingga proses absensi selesai ✅</span>
            </li>
        </ul>
    </div>
</div>

@push('scripts')
<script>
function nfcReader() {
    return {
        nfcSupported: false,
        isScanning: false,
        scanType: null, // 'check-in' or 'check-out'
        abortController: null,

        init() {
            // Check NFC support
            if ('NDEFReader' in window) {
                this.nfcSupported = true;
                console.log('✅ NFC Web API supported');
            } else {
                this.nfcSupported = false;
                console.log('❌ NFC Web API not supported');
            }
        },

        async startNfcScan(type) {
            if (!this.nfcSupported) {
                alert('❌ Browser Anda tidak mendukung NFC. Gunakan Chrome di Android.');
                return;
            }

            this.scanType = type;
            this.isScanning = true;
            this.abortController = new AbortController();

            try {
                const ndef = new NDEFReader();

                // Request permission
                await ndef.scan({ signal: this.abortController.signal });

                console.log('🔵 NFC scan started. Waiting for tag...');

                // Listen for NFC tag
                ndef.addEventListener('reading', async ({ message, serialNumber }) => {
                    console.log('📱 NFC Tag detected:', serialNumber);

                    // Get location
                    const location = await this.getLocation();

                    if (!location) {
                        @this.set('statusMessage', '❌ Gagal mendapatkan lokasi. Aktifkan GPS Anda.');
                        @this.set('statusType', 'error');
                        this.cancelScan();
                        return;
                    }

                    // Process attendance
                    if (type === 'check-in') {
                        @this.call('processNfcCheckIn', serialNumber, location.latitude, location.longitude, location.accuracy);
                    } else {
                        @this.call('processNfcCheckOut', serialNumber, location.latitude, location.longitude, location.accuracy);
                    }

                    this.isScanning = false;
                }, { once: true });

                ndef.addEventListener('readingerror', () => {
                    console.log('❌ Cannot read data from the NFC tag.');
                    @this.set('statusMessage', '❌ Gagal membaca kartu NFC. Coba lagi.');
                    @this.set('statusType', 'error');
                    this.isScanning = false;
                });

            } catch (error) {
                console.error('NFC Error:', error);

                if (error.name === 'NotAllowedError') {
                    alert('❌ Izin NFC ditolak. Berikan izin pada browser untuk menggunakan NFC.');
                } else if (error.name === 'NotSupportedError') {
                    alert('❌ NFC tidak didukung di perangkat ini.');
                } else {
                    @this.set('statusMessage', '❌ Error: ' + error.message);
                    @this.set('statusType', 'error');
                }

                this.isScanning = false;
            }
        },

        cancelScan() {
            if (this.abortController) {
                this.abortController.abort();
                this.abortController = null;
            }
            this.isScanning = false;
            console.log('🛑 NFC scan cancelled');
        },

        async getLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    console.error('❌ Geolocation not supported');
                    resolve(null);
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        resolve({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: position.coords.accuracy
                        });
                    },
                    (error) => {
                        console.error('❌ Geolocation error:', error);
                        resolve(null);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            });
        }
    }
}
</script>
@endpush
