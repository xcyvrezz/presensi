<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-slate-50 p-6 flex items-center justify-center" x-data="tappingStation()" x-init="init()">
    <div class="w-full max-w-5xl">

        <!-- Header Section -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-3 mb-4">
                <div class="relative">
                    <div class="absolute inset-0 bg-blue-500 rounded-2xl blur-xl opacity-20"></div>
                    <div class="relative w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-left">
                    <h1 class="text-2xl font-bold text-slate-800">{{ config('app.name') }}</h1>
                    <p class="text-sm text-slate-500">Digital Attendance System</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Sidebar - Stats -->
            <div class="lg:col-span-1 space-y-4">
                <!-- Live Clock Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white">
                        <div class="text-xs uppercase tracking-wider mb-2 opacity-80">Waktu Real-time</div>
                        <div class="text-4xl font-bold tracking-tight mb-1" x-text="currentTime">{{ $currentTime }}</div>
                        <div class="text-sm opacity-90">{{ $currentDate }}</div>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-slate-50 to-white">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <div class="relative flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                                </div>
                                <span class="text-slate-600 font-medium">System Online</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Statistics -->
                <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-6">
                    <h3 class="text-sm font-semibold text-slate-800 mb-4 uppercase tracking-wider">Statistik Hari Ini</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-slate-50 to-transparent rounded-xl border border-slate-100">
                            <div>
                                <div class="text-2xl font-bold text-slate-800">{{ number_format($todayStats['total_present']) }}</div>
                                <div class="text-xs text-slate-500 mt-1">Total Hadir</div>
                            </div>
                            <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-transparent rounded-xl border border-blue-100">
                            <div>
                                <div class="text-2xl font-bold text-blue-600">{{ number_format($todayStats['on_time']) }}</div>
                                <div class="text-xs text-blue-600 mt-1">Tepat Waktu</div>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-amber-50 to-transparent rounded-xl border border-amber-100">
                            <div>
                                <div class="text-2xl font-bold text-amber-600">{{ number_format($todayStats['late']) }}</div>
                                <div class="text-xs text-amber-600 mt-1">Terlambat</div>
                            </div>
                            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedule Info -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-700 rounded-2xl shadow-lg p-6 text-white">
                    <h3 class="text-xs font-semibold mb-4 uppercase tracking-wider opacity-80">Jadwal Absensi</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-xs opacity-80">Check-In</div>
                                <div class="text-sm font-semibold">{{ $checkInStart }} - {{ $checkInEnd }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-xs opacity-80">Check-Out</div>
                                <div class="text-sm font-semibold">{{ $checkOutStart }} - {{ $checkOutEnd }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden min-h-[600px] flex flex-col">
                    
                    @if(!$studentData && !$message)
                        <!-- Waiting State -->
                        <div class="flex-1 flex flex-col items-center justify-center p-12">
                            <div class="relative mb-8">
                                <div class="absolute inset-0 bg-blue-500 rounded-full blur-3xl opacity-20 animate-pulse"></div>
                                <div class="relative w-32 h-32 bg-gradient-to-br from-blue-100 to-blue-200 rounded-3xl flex items-center justify-center shadow-2xl">
                                    <svg class="w-16 h-16 text-blue-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                                    </svg>
                                </div>
                            </div>

                            <h2 class="text-3xl font-bold text-slate-800 mb-3">Tap Kartu RFID Anda</h2>
                            <p class="text-slate-500 text-center max-w-md mb-8">Tempelkan kartu MIFARE pada reader untuk melakukan check-in atau check-out</p>

                            <div class="inline-flex items-center gap-3 px-6 py-3 bg-blue-50 border border-blue-200 rounded-full">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <span class="text-sm font-medium text-blue-700">Menunggu kartu...</span>
                            </div>
                        </div>
                    @elseif($message && !$studentData)
                        <!-- Error State (Card Not Registered or System Error) -->
                        <div class="flex-1 flex flex-col items-center justify-center p-12">
                            <div class="relative mb-8">
                                <div class="absolute inset-0 bg-red-500 rounded-full blur-3xl opacity-20"></div>
                                <div class="relative w-32 h-32 bg-gradient-to-br from-red-100 to-red-200 rounded-3xl flex items-center justify-center shadow-2xl">
                                    <svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                    </svg>
                                </div>
                            </div>

                            <h2 class="text-3xl font-bold text-red-600 mb-3">
                                @if(str_contains($message, 'TIDAK TERDAFTAR'))
                                    Kartu Tidak Terdaftar
                                @else
                                    Gagal
                                @endif
                            </h2>
                            <p class="text-slate-600 text-center max-w-md mb-8">{{ $message }}</p>

                            @if(str_contains($message, 'TIDAK TERDAFTAR'))
                                <div class="inline-flex items-center gap-3 px-6 py-3 bg-red-50 border-2 border-red-200 rounded-xl">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-red-700">Silakan hubungi admin untuk registrasi kartu</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Student Display -->
                        <div class="flex-1 flex flex-col items-center justify-center p-12">
                            
                            <!-- Photo -->
                            <div class="relative mb-6">
                                <div class="absolute -inset-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full blur-2xl opacity-30"></div>
                                @if($studentData['photo'])
                                    <img src="{{ $studentData['photo'] }}" alt="{{ $studentData['name'] }}" class="relative w-36 h-36 rounded-full border-4 border-white shadow-2xl object-cover">
                                @else
                                    <div class="relative w-36 h-36 rounded-full border-4 border-white shadow-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                        <span class="text-6xl font-bold text-white">{{ substr($studentData['name'], 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Student Info -->
                            <h2 class="text-3xl font-bold text-slate-800 mb-2">{{ $studentData['name'] }}</h2>
                            <div class="flex items-center gap-3 mb-8">
                                <span class="px-4 py-1.5 bg-slate-100 rounded-lg text-sm font-semibold text-slate-700">{{ $studentData['nis'] }}</span>
                                <span class="text-slate-400">•</span>
                                <span class="px-4 py-1.5 bg-blue-50 rounded-lg text-sm font-semibold text-blue-700">{{ $studentData['class'] }}</span>
                            </div>

                            <!-- Status Message -->
                            @if($message)
                                <div class="w-full max-w-md mb-8">
                                    @if($messageType === 'success')
                                        {{-- Check if late --}}
                                        @if(isset($attendanceStatus['status']) && $attendanceStatus['status'] === 'terlambat')
                                            <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-lg shadow-amber-200">
                                                <div class="flex items-start gap-4">
                                                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="text-sm opacity-90 mb-1">TERLAMBAT!</div>
                                                        <div class="font-bold text-xl">{{ $message }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg shadow-blue-200">
                                                <div class="flex items-start gap-4">
                                                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="text-sm opacity-90 mb-1">Berhasil</div>
                                                        <div class="font-semibold text-lg">{{ $message }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @elseif($messageType === 'error')
                                        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-2xl p-6 text-white shadow-lg shadow-red-200">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-sm opacity-90 mb-1">Error</div>
                                                    <div class="font-semibold text-lg">{{ $message }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="bg-slate-100 border border-slate-200 rounded-2xl p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 bg-slate-200 rounded-xl flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-sm text-slate-500 mb-1">Informasi</div>
                                                    <div class="font-semibold text-slate-800">{{ $message }}</div>
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
                                        <div class="bg-gradient-to-br from-slate-50 to-white border-2 border-slate-200 rounded-2xl p-8 text-center">
                                            <div class="inline-flex items-center gap-2 mb-6 px-4 py-2 bg-blue-50 rounded-full">
                                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                                <span class="text-xs font-semibold text-blue-700 uppercase tracking-wider">Check-In</span>
                                            </div>
                                            <div class="text-7xl font-bold text-slate-800 mb-6">{{ $attendanceStatus['time'] }}</div>
                                            <div class="inline-flex items-center gap-3 px-8 py-4 rounded-2xl shadow-lg {{ $attendanceStatus['status'] === 'hadir' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white' : 'bg-gradient-to-r from-amber-500 to-amber-600 text-white' }}">
                                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="font-bold text-lg uppercase">{{ $attendanceStatus['status'] }}</span>
                                            </div>
                                            @if($attendanceStatus['late_minutes'] > 0)
                                                <div class="mt-6 inline-flex items-center gap-2 px-5 py-3 bg-amber-50 rounded-xl border border-amber-200">
                                                    <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="font-semibold text-amber-700">Terlambat {{ $attendanceStatus['late_minutes'] }} menit</span>
                                                </div>
                                            @endif
                                        </div>

                                    @elseif($attendanceStatus['action'] === 'check-out')
                                        <div class="bg-gradient-to-br from-slate-50 to-white border-2 border-slate-200 rounded-2xl p-8">
                                            <div class="text-center mb-8">
                                                <div class="inline-flex items-center gap-2 mb-4 px-4 py-2 bg-green-50 rounded-full">
                                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="text-xs font-semibold text-green-700 uppercase tracking-wider">Check-Out Berhasil</span>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-6">
                                                <div class="text-center p-6 bg-white rounded-xl border-2 border-slate-200">
                                                    <div class="text-xs text-slate-500 uppercase tracking-wider mb-3 font-semibold">Masuk</div>
                                                    <div class="text-4xl font-bold text-slate-800">{{ $attendanceStatus['check_in_time'] }}</div>
                                                </div>
                                                <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border-2 border-blue-300">
                                                    <div class="text-xs text-blue-700 uppercase tracking-wider mb-3 font-semibold">Keluar</div>
                                                    <div class="text-4xl font-bold text-blue-600">{{ $attendanceStatus['check_out_time'] }}</div>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($attendanceStatus['action'] === 'waiting')
                                        <div class="bg-gradient-to-br from-slate-50 to-white border-2 border-slate-200 rounded-2xl p-8 text-center">
                                            <div class="inline-flex items-center gap-2 mb-6 px-4 py-2 bg-slate-100 rounded-full">
                                                <div class="w-2 h-2 bg-slate-400 rounded-full"></div>
                                                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Menunggu</span>
                                            </div>
                                            <div class="text-5xl font-bold text-slate-800 mb-3">{{ $attendanceStatus['check_in_time'] }}</div>
                                            <div class="text-slate-500">Belum waktunya check-out</div>
                                        </div>

                                    @elseif($attendanceStatus['action'] === 'completed')
                                        <div class="bg-gradient-to-br from-green-50 to-white border-2 border-green-200 rounded-2xl p-8">
                                            <div class="text-center mb-8">
                                                <div class="inline-flex items-center gap-2 mb-4 px-4 py-2 bg-green-100 rounded-full">
                                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="text-sm font-semibold text-green-700 uppercase tracking-wider">Absensi Lengkap</span>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-6 mb-6">
                                                <div class="text-center p-5 bg-white rounded-xl border border-slate-200">
                                                    <div class="text-xs text-slate-500 uppercase mb-2 font-semibold">Masuk</div>
                                                    <div class="text-3xl font-bold text-slate-800">{{ $attendanceStatus['check_in_time'] }}</div>
                                                </div>
                                                <div class="text-center p-5 bg-white rounded-xl border border-slate-200">
                                                    <div class="text-xs text-slate-500 uppercase mb-2 font-semibold">Keluar</div>
                                                    <div class="text-3xl font-bold text-slate-800">{{ $attendanceStatus['check_out_time'] }}</div>
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <span class="inline-block px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl font-semibold text-sm uppercase tracking-wider shadow-lg">{{ $attendanceStatus['status'] }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Footer -->
                    <div class="border-t border-slate-100 px-8 py-4 bg-slate-50">
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-slate-500">
                                Powered by RFID Technology
                            </div>
                            <a href="{{ route('login') }}" class="flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors group">
                                <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
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