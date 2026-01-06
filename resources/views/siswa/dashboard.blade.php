<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <title>Dashboard Siswa - Absensi MIFARE</title>

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/build/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icon-192x192.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Dashboard Siswa</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-500">Siswa</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <!-- Welcome Message -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Selamat Datang, {{ auth()->user()->name }}!</h2>
                <p class="text-gray-600 mt-1">Jangan lupa melakukan absensi hari ini.</p>
            </div>

            @php
                $today = \Carbon\Carbon::today();
                $attendance = \App\Models\Attendance::where('student_id', auth()->user()->student->id ?? null)
                    ->whereDate('date', $today)
                    ->first();
                $hasCheckedIn = $attendance && $attendance->check_in_time;
                $hasCheckedOut = $attendance && $attendance->check_out_time;
            @endphp

            <!-- Today's Attendance Status -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Absensi Hari Ini</h3>

                @if(!$hasCheckedIn)
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">Anda belum melakukan check-in hari ini.</p>
                            </div>
                        </div>
                    </div>
                @elseif(!$hasCheckedOut)
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-700">Anda sudah check-in.</p>
                                <p class="text-xs text-blue-600 mt-1">Jangan lupa check-out nanti.</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-700">Absensi hari ini sudah lengkap!</p>
                                <p class="text-xs text-green-600 mt-1">Terima kasih telah tertib dalam absensi.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Attendance Details (if exists) -->
                @if($attendance)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-xs text-gray-500 mb-1">Check-In</div>
                            <div class="text-lg font-semibold text-gray-900">
                                {{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-' }}
                            </div>
                            @if($attendance->late_minutes > 0)
                                <div class="text-xs text-red-600 mt-1">Terlambat {{ $attendance->late_minutes }} menit</div>
                            @endif
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-xs text-gray-500 mb-1">Check-Out</div>
                            <div class="text-lg font-semibold text-gray-900">
                                {{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-' }}
                            </div>
                            @if($attendance->early_leave_minutes > 0)
                                <div class="text-xs text-orange-600 mt-1">Pulang cepat {{ $attendance->early_leave_minutes }} menit</div>
                            @endif
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-xs text-gray-500 mb-1">Status</div>
                            <div class="text-lg font-semibold text-gray-900">
                                {{ ucfirst($attendance->status) }}
                            </div>
                            @if($attendance->percentage)
                                <div class="text-xs text-green-600 mt-1">{{ $attendance->percentage }}%</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Check-In Card -->
                <a href="{{ route('siswa.check-in') }}" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden {{ $hasCheckedIn ? 'opacity-60 pointer-events-none' : '' }}">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">Check-In</h4>
                                <p class="text-sm text-gray-500">Absensi masuk sekolah</p>
                            </div>
                        </div>
                        @if($hasCheckedIn)
                            <div class="mt-4 text-sm text-green-600 font-medium">
                                ✓ Sudah check-in hari ini
                            </div>
                        @else
                            <div class="mt-4 inline-flex items-center text-sm font-medium text-blue-600">
                                Tap untuk check-in
                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </a>

                <!-- Check-Out Card -->
                <a href="{{ route('siswa.check-out') }}" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden {{ (!$hasCheckedIn || $hasCheckedOut) ? 'opacity-60 pointer-events-none' : '' }}">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">Check-Out</h4>
                                <p class="text-sm text-gray-500">Absensi pulang sekolah</p>
                            </div>
                        </div>
                        @if($hasCheckedOut)
                            <div class="mt-4 text-sm text-green-600 font-medium">
                                ✓ Sudah check-out hari ini
                            </div>
                        @elseif(!$hasCheckedIn)
                            <div class="mt-4 text-sm text-gray-500">
                                Check-in dulu sebelum check-out
                            </div>
                        @else
                            <div class="mt-4 inline-flex items-center text-sm font-medium text-orange-600">
                                Tap untuk check-out
                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </a>
            </div>

            <!-- Weekly Stats -->
            @php
                $weekStart = \Carbon\Carbon::now()->startOfWeek();
                $weekEnd = \Carbon\Carbon::now()->endOfWeek();
                $weekAttendance = \App\Models\Attendance::where('student_id', auth()->user()->student->id ?? null)
                    ->whereBetween('date', [$weekStart, $weekEnd])
                    ->get();
                $presentCount = $weekAttendance->whereIn('status', ['hadir'])->count();
                $lateCount = $weekAttendance->where('status', 'terlambat')->count();
            @endphp

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Minggu Ini</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ $weekAttendance->count() }}</div>
                        <div class="text-sm text-gray-500 mt-1">Total Absensi</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600">{{ $presentCount }}</div>
                        <div class="text-sm text-gray-500 mt-1">Hadir Tepat Waktu</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-yellow-600">{{ $lateCount }}</div>
                        <div class="text-sm text-gray-500 mt-1">Terlambat</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600">
                            {{ $weekAttendance->avg('percentage') ? round($weekAttendance->avg('percentage')) : 0 }}%
                        </div>
                        <div class="text-sm text-gray-500 mt-1">Rata-rata</div>
                    </div>
                </div>
            </div>

            <!-- System Info -->
            <div class="mt-6 bg-blue-50 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-blue-900">Informasi Sistem</h4>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>✓ Sistem absensi NFC mobile sudah aktif</p>
                            <p class="mt-1">✓ Pastikan GPS dan NFC aktif saat melakukan absensi</p>
                            <p class="mt-1">✓ Absensi menggunakan geofencing (radius 15m dari sekolah)</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
