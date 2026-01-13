<div class="h-screen bg-gradient-to-br from-blue-50 via-white to-slate-50 p-3 flex items-center justify-center overflow-hidden" x-data="tappingStation()" x-init="init()">
    <div class="w-full max-w-6xl mx-auto flex flex-col" style="max-height: 100vh;">

        <!-- Header Section -->
        <div class="text-center mb-3">
            <div class="flex items-center justify-center gap-2">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-left">
                    <h1 class="text-xl font-bold text-slate-800">{{ config('app.name') }}</h1>
                    <p class="text-xs text-slate-500">Digital Attendance System</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 flex-1 items-start">

            <!-- Left Sidebar - Stats (Order 2 on mobile, Order 1 on desktop) -->
            <div class="lg:col-span-1 space-y-2 order-2 lg:order-1">
                <!-- Live Clock Card -->
                <div class="bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-2.5 text-white">
                        <div class="text-xl font-bold tracking-tight" x-text="currentTime">{{ $currentTime }}</div>
                        <div class="text-xs opacity-90">{{ $currentDate }}</div>
                    </div>
                </div>

                <!-- Today's Statistics -->
                <div class="bg-white rounded-xl shadow-lg border border-slate-100 p-2.5">
                    <h3 class="text-xs font-semibold text-slate-800 mb-2 uppercase">Statistik Hari Ini</h3>
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between p-2 bg-gradient-to-r from-slate-50 to-transparent rounded-lg">
                            <div>
                                <div class="text-lg font-bold text-slate-800">{{ number_format($todayStats['total_present']) }}</div>
                                <div class="text-xs text-slate-500">Total Hadir</div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-2 bg-gradient-to-r from-blue-50 to-transparent rounded-lg">
                            <div>
                                <div class="text-lg font-bold text-blue-600">{{ number_format($todayStats['on_time']) }}</div>
                                <div class="text-xs text-blue-600">Tepat Waktu</div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-2 bg-gradient-to-r from-amber-50 to-transparent rounded-lg">
                            <div>
                                <div class="text-lg font-bold text-amber-600">{{ number_format($todayStats['late']) }}</div>
                                <div class="text-xs text-amber-600">Terlambat</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedule Info -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-700 rounded-xl shadow-lg p-2.5 text-white">
                    <h3 class="text-xs font-semibold mb-2 uppercase">Jadwal Absensi</h3>
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <div class="text-xs opacity-80">Check-In</div>
                                <div class="text-sm font-semibold">{{ $checkInStart }} - {{ $checkInEnd }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <div class="text-xs opacity-80">Check-Out</div>
                                <div class="text-sm font-semibold">{{ $checkOutStart }} - {{ $checkOutEnd }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area (Order 1 on mobile, Order 2 on desktop) -->
            <div class="lg:col-span-2 order-1 lg:order-2">
                <div class="bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden flex flex-col" style="min-height: 400px;">

                    @if(!$studentData && !$message)
                        <!-- Waiting State -->
                        <div class="flex-1 flex flex-col items-center justify-center p-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center shadow-xl mb-4">
                                <svg class="w-10 h-10 text-blue-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                                </svg>
                            </div>

                            <h2 class="text-2xl font-bold text-slate-800 mb-2">Tap Kartu RFID Anda</h2>
                            <p class="text-slate-500 text-center max-w-md mb-4 text-sm">Tempelkan kartu MIFARE pada reader</p>

                            <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 rounded-full">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <span class="text-sm font-medium text-blue-700">Menunggu kartu...</span>
                            </div>
                        </div>
                    @elseif($message && !$studentData)
                        <!-- Error State -->
                        <div class="flex-1 flex flex-col items-center justify-center p-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-red-100 to-red-200 rounded-2xl flex items-center justify-center shadow-xl mb-4">
                                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                </svg>
                            </div>

                            <h2 class="text-2xl font-bold text-red-600 mb-2">
                                @if(str_contains($message, 'TIDAK TERDAFTAR'))
                                    Kartu Tidak Terdaftar
                                @else
                                    Gagal
                                @endif
                            </h2>
                            <p class="text-slate-600 text-center max-w-md">{{ $message }}</p>
                        </div>
                    @else
                        <!-- Student Display -->
                        <div class="flex-1 flex flex-col items-center justify-center p-6">

                            <!-- Photo -->
                            <div class="relative mb-3">
                                @if($studentData['photo'])
                                    <img src="{{ $studentData['photo'] }}" alt="{{ $studentData['name'] }}" class="w-24 h-24 rounded-full border-4 border-white shadow-xl object-cover">
                                @else
                                    <div class="w-24 h-24 rounded-full border-4 border-white shadow-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                        <span class="text-4xl font-bold text-white">{{ substr($studentData['name'], 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Student Info -->
                            <h2 class="text-2xl font-bold text-slate-800 mb-2">{{ $studentData['name'] }}</h2>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="px-3 py-1 bg-slate-100 rounded-lg text-sm font-semibold text-slate-700">{{ $studentData['nis'] }}</span>
                                <span class="text-slate-400">•</span>
                                <span class="px-3 py-1 bg-blue-50 rounded-lg text-sm font-semibold text-blue-700">{{ $studentData['class'] }}</span>
                            </div>

                            <!-- Status Message -->
                            @if($message)
                                <div class="w-full max-w-md mb-3">
                                    @if($messageType === 'success')
                                        @if(isset($attendanceStatus['status']) && $attendanceStatus['status'] === 'terlambat')
                                            <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-xl p-3 text-white shadow-lg">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="text-xs mb-1">TERLAMBAT!</div>
                                                        <div class="font-bold text-base">{{ $message }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-3 text-white shadow-lg">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="text-xs mb-1">Berhasil</div>
                                                        <div class="font-semibold text-base">{{ $message }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @elseif($messageType === 'error')
                                        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl p-3 text-white shadow-lg">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-xs mb-1">Error</div>
                                                    <div class="font-semibold text-base">{{ $message }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="bg-slate-100 border border-slate-200 rounded-xl p-3">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 bg-slate-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-xs text-slate-500 mb-1">Informasi</div>
                                                    <div class="font-semibold text-slate-800 text-base">{{ $message }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Attendance Details -->
                            @if($attendanceStatus)
                                <div class="w-full max-w-lg">
                                    @if($attendanceStatus['action'] === 'check-in')
                                        <div class="bg-gradient-to-br from-slate-50 to-white border-2 border-slate-200 rounded-xl p-4 text-center">
                                            <div class="inline-flex items-center gap-2 mb-3 px-3 py-1 bg-blue-50 rounded-full">
                                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                                <span class="text-xs font-semibold text-blue-700 uppercase">Check-In</span>
                                            </div>
                                            <div class="text-5xl font-bold text-slate-800 mb-3">{{ $attendanceStatus['time'] }}</div>
                                            <div class="inline-flex items-center gap-2 px-6 py-2 rounded-xl shadow-lg {{ $attendanceStatus['status'] === 'hadir' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white' : 'bg-gradient-to-r from-amber-500 to-amber-600 text-white' }}">
                                                <span class="font-bold text-base uppercase">{{ $attendanceStatus['status'] }}</span>
                                            </div>
                                            @if($attendanceStatus['late_minutes'] > 0)
                                                <div class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-amber-50 rounded-lg border border-amber-200">
                                                    <span class="font-semibold text-amber-700 text-sm">Terlambat {{ $attendanceStatus['late_minutes'] }} menit</span>
                                                </div>
                                            @endif
                                        </div>

                                    @elseif($attendanceStatus['action'] === 'check-out')
                                        <div class="bg-gradient-to-br from-slate-50 to-white border-2 border-slate-200 rounded-xl p-4">
                                            <div class="text-center mb-4">
                                                <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 bg-green-50 rounded-full">
                                                    <span class="text-xs font-semibold text-green-700 uppercase">Check-Out Berhasil</span>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="text-center p-3 bg-white rounded-lg border-2 border-slate-200">
                                                    <div class="text-xs text-slate-500 uppercase mb-2 font-semibold">Masuk</div>
                                                    <div class="text-2xl font-bold text-slate-800">{{ $attendanceStatus['check_in_time'] }}</div>
                                                </div>
                                                <div class="text-center p-3 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border-2 border-blue-300">
                                                    <div class="text-xs text-blue-700 uppercase mb-2 font-semibold">Keluar</div>
                                                    <div class="text-2xl font-bold text-blue-600">{{ $attendanceStatus['check_out_time'] }}</div>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($attendanceStatus['action'] === 'waiting')
                                        <div class="bg-gradient-to-br from-slate-50 to-white border-2 border-slate-200 rounded-xl p-4 text-center">
                                            <div class="inline-flex items-center gap-2 mb-3 px-3 py-1 bg-slate-100 rounded-full">
                                                <span class="text-xs font-semibold text-slate-600 uppercase">Menunggu</span>
                                            </div>
                                            <div class="text-4xl font-bold text-slate-800 mb-2">{{ $attendanceStatus['check_in_time'] }}</div>
                                            <div class="text-slate-500 text-sm">Belum waktunya check-out</div>
                                        </div>

                                    @elseif($attendanceStatus['action'] === 'completed')
                                        <div class="bg-gradient-to-br from-green-50 to-white border-2 border-green-200 rounded-xl p-4">
                                            <div class="text-center mb-4">
                                                <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 bg-green-100 rounded-full">
                                                    <span class="text-xs font-semibold text-green-700 uppercase">Absensi Lengkap</span>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4 mb-3">
                                                <div class="text-center p-3 bg-white rounded-lg border border-slate-200">
                                                    <div class="text-xs text-slate-500 uppercase mb-1 font-semibold">Masuk</div>
                                                    <div class="text-2xl font-bold text-slate-800">{{ $attendanceStatus['check_in_time'] }}</div>
                                                </div>
                                                <div class="text-center p-3 bg-white rounded-lg border border-slate-200">
                                                    <div class="text-xs text-slate-500 uppercase mb-1 font-semibold">Keluar</div>
                                                    <div class="text-2xl font-bold text-slate-800">{{ $attendanceStatus['check_out_time'] }}</div>
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <span class="inline-block px-5 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg font-semibold text-sm uppercase">{{ $attendanceStatus['status'] }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Footer -->
                    <div class="border-t border-slate-100 px-4 py-2 bg-slate-50">
                        <div class="flex items-center justify-end">
                            <a href="{{ route('login') }}" class="flex items-center gap-2 text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                                <span>Admin Panel</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function tappingStation() {
    return {
        currentTime: '{{ $currentTime }}',
        cardBuffer: '',
        nfcSupported: false,
        nfcReader: null,

        init() {
            setInterval(() => { this.updateClock(); }, 1000);
            this.setupUSBReader();
            this.setupNFC();
            setInterval(() => { @this.call('updateTime'); }, 60000);

            // Listen for clear event
            window.addEventListener('schedule-clear', () => {
                setTimeout(() => {
                    @this.call('clearDisplay');
                }, 5000);
            });

            // Listen for play sound event
            window.addEventListener('play-sound', (event) => {
                this.playSound(event.detail.sound);
            });
        },

        playSound(type) {
            // Simple audio feedback using Web Audio API
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            if (type === 'success') {
                // Success sound: High pitch beep
                oscillator.frequency.value = 800;
                gainNode.gain.value = 0.3;
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.2);
            } else if (type === 'error') {
                // Error sound: Low pitch beep
                oscillator.frequency.value = 200;
                gainNode.gain.value = 0.3;
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            } else if (type === 'warning') {
                // Warning sound: Medium pitch beep
                oscillator.frequency.value = 500;
                gainNode.gain.value = 0.3;
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            }
        },

        updateClock() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
        },

        setupUSBReader() {
            document.addEventListener('keypress', (e) => {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                if (e.key === 'Enter') {
                    if (this.cardBuffer.length > 0) {
                        this.processCardUID(this.cardBuffer);
                        this.cardBuffer = '';
                    }
                } else {
                    this.cardBuffer += e.key;
                    clearTimeout(this.bufferTimeout);
                    this.bufferTimeout = setTimeout(() => {
                        if (this.cardBuffer.length > 5) {
                            this.processCardUID(this.cardBuffer);
                        }
                        this.cardBuffer = '';
                    }, 2000);
                }
            });
        },

        async setupNFC() {
            if ('NDEFReader' in window) {
                this.nfcSupported = true;
                try {
                    this.nfcReader = new NDEFReader();
                    await this.nfcReader.scan();
                    this.nfcReader.addEventListener('reading', ({ serialNumber }) => {
                        this.processCardUID(serialNumber);
                    });
                } catch (error) {}
            }
        },

        processCardUID(cardUid) {
            if (cardUid === this.lastCardUid) return;
            this.lastCardUid = cardUid;
            @this.call('processCard', cardUid);

            // Auto clear setelah 5 detik untuk siap tap berikutnya
            setTimeout(() => {
                this.lastCardUid = null;
                @this.call('clearDisplay');
            }, 5000);
        }
    }
}
</script>
@endpush
