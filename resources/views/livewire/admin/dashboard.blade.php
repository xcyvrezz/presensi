<div class="min-h-screen bg-slate-50">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Dashboard Admin</h1>
                <p class="text-sm text-slate-600 mt-2">Monitoring sistem dan statistik keseluruhan</p>
            </div>
            <div class="hidden lg:block">
                <div class="bg-white border border-slate-200 rounded-xl px-6 py-3 shadow-sm">
                    <p class="text-sm font-semibold text-slate-700">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</p>
                    <p class="text-xs text-slate-500 mt-1 text-center" x-data x-text="new Date().toLocaleTimeString('id-ID')" x-init="setInterval(() => { $el.textContent = new Date().toLocaleTimeString('id-ID') }, 1000)">00:00:00</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Holiday Banner -->
    @if($isHoliday && $holidayInfo)
        <div class="mb-6 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border-2 border-amber-300 rounded-2xl p-6 shadow-lg">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center shadow-md">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <h3 class="text-2xl font-bold text-amber-900">Hari Libur</h3>
                        <span class="px-3 py-1 bg-amber-200 text-amber-900 text-xs font-bold rounded-full uppercase">{{ $holidayInfo->type === 'national' ? 'Nasional' : 'Sekolah' }}</span>
                    </div>
                    <p class="text-lg font-semibold text-amber-800 mb-2">{{ $holidayInfo->title }}</p>
                    @if($holidayInfo->description)
                        <p class="text-sm text-amber-700">{{ $holidayInfo->description }}</p>
                    @endif
                    <div class="mt-4 flex items-center gap-2 text-sm text-amber-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">Statistik absensi hari ini tidak ditampilkan karena libur</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- System Overview Cards -->
    <div class="mb-6">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Ringkasan Sistem</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Users Card -->
            <div class="group bg-white border border-slate-200 rounded-xl p-5 hover:border-blue-300 hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Total Users</p>
                        <p class="text-3xl font-bold text-slate-900 mb-1">{{ $systemStats['total_users'] }}</p>
                        <p class="text-xs text-slate-600">{{ $systemStats['active_users'] }} user aktif</p>
                    </div>
                    <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Students Card -->
            <div class="group bg-white border border-slate-200 rounded-xl p-5 hover:border-blue-300 hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Total Siswa</p>
                        <p class="text-3xl font-bold text-slate-900 mb-1">{{ $systemStats['total_students'] }}</p>
                        <p class="text-xs text-slate-600">{{ $systemStats['active_students'] }} siswa aktif</p>
                    </div>
                    <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Classes Card -->
            <div class="group bg-white border border-slate-200 rounded-xl p-5 hover:border-blue-300 hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Total Kelas</p>
                        <p class="text-3xl font-bold text-slate-900 mb-1">{{ $systemStats['total_classes'] }}</p>
                        <p class="text-xs text-slate-600">{{ $systemStats['total_departments'] }} jurusan</p>
                    </div>
                    <div class="w-11 h-11 bg-slate-100 rounded-xl flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Today's Attendance Card -->
            <div class="group bg-white border-2 {{ $isHoliday ? 'border-amber-300' : 'border-blue-500' }} rounded-xl p-5 hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Kehadiran Hari Ini</p>
                        @if($isHoliday)
                            <p class="text-3xl font-bold text-amber-600 mb-1">LIBUR</p>
                            <p class="text-xs text-slate-600">Tidak ada absensi</p>
                        @else
                            <p class="text-3xl font-bold text-blue-600 mb-1">{{ $todayStats['attendance_percentage'] }}%</p>
                            <p class="text-xs text-slate-600">{{ $todayStats['total_present'] }} dari {{ $systemStats['active_students'] }} siswa</p>
                        @endif
                    </div>
                    <div class="w-11 h-11 {{ $isHoliday ? 'bg-amber-50' : 'bg-blue-50' }} rounded-xl flex items-center justify-center group-hover:{{ $isHoliday ? 'bg-amber-100' : 'bg-blue-100' }} transition-colors">
                        @if($isHoliday)
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Attendance Statistics -->
    @if(!$isHoliday)
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">Statistik Absensi Hari Ini</h2>
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-7 gap-4">
            <!-- Hadir -->
            <div class="text-center p-5 bg-slate-50 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-slate-900 mb-1">{{ $todayStats['total_hadir'] }}</p>
                <p class="text-xs text-slate-600 font-semibold uppercase tracking-wide">Hadir</p>
            </div>

            <!-- Terlambat -->
            <div class="text-center p-5 bg-slate-50 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-slate-900 mb-1">{{ $todayStats['total_terlambat'] }}</p>
                <p class="text-xs text-slate-600 font-semibold uppercase tracking-wide">Terlambat</p>
            </div>

            <!-- Izin -->
            <div class="text-center p-5 bg-slate-50 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all">
                <div class="w-12 h-12 bg-slate-200 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-slate-900 mb-1">{{ $todayStats['total_izin'] }}</p>
                <p class="text-xs text-slate-600 font-semibold uppercase tracking-wide">Izin</p>
            </div>

            <!-- Sakit -->
            <div class="text-center p-5 bg-slate-50 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all">
                <div class="w-12 h-12 bg-slate-200 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-slate-900 mb-1">{{ $todayStats['total_sakit'] }}</p>
                <p class="text-xs text-slate-600 font-semibold uppercase tracking-wide">Sakit</p>
            </div>

            <!-- Dispensasi -->
            <div class="text-center p-5 bg-slate-50 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all">
                <div class="w-12 h-12 bg-slate-200 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-slate-900 mb-1">{{ $todayStats['total_dispensasi'] }}</p>
                <p class="text-xs text-slate-600 font-semibold uppercase tracking-wide">Dispensasi</p>
            </div>

            <!-- Alpha -->
            <div class="text-center p-5 bg-slate-50 rounded-xl border border-slate-200 hover:border-red-300 hover:bg-red-50 transition-all">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-slate-900 mb-1">{{ $todayStats['total_alpha'] }}</p>
                <p class="text-xs text-slate-600 font-semibold uppercase tracking-wide">Alpha</p>
            </div>

            <!-- Belum Absen -->
            <div class="text-center p-5 bg-slate-50 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-100 transition-all">
                <div class="w-12 h-12 bg-slate-200 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-slate-900 mb-1">{{ $todayStats['total_absent'] }}</p>
                <p class="text-xs text-slate-600 font-semibold uppercase tracking-wide">Belum Absen</p>
            </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Actions & System Info -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <!-- Quick Actions Panel -->
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-5">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.users.create') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50 transition-all group">
                    <div class="w-10 h-10 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-slate-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <p class="flex-1 text-sm font-semibold text-slate-900 group-hover:text-blue-600">Tambah User</p>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.students.create') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50 transition-all group">
                    <div class="w-10 h-10 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-slate-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <p class="flex-1 text-sm font-semibold text-slate-900 group-hover:text-blue-600">Tambah Siswa</p>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.students.import') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50 transition-all group">
                    <div class="w-10 h-10 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-slate-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <p class="flex-1 text-sm font-semibold text-slate-900 group-hover:text-blue-600">Import Data Siswa</p>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <!-- Generate Alpha - Highlighted -->
                <a href="{{ route('admin.attendance.generate-alpha') }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-slate-300 bg-slate-50 hover:border-slate-400 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 bg-slate-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <p class="flex-1 text-sm font-bold text-slate-900 group-hover:text-slate-700">Generate Alpha</p>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.attendance.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50 transition-all group">
                    <div class="w-10 h-10 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-slate-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <p class="flex-1 text-sm font-semibold text-slate-900 group-hover:text-blue-600">Data Absensi</p>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.attendance.manual') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50 transition-all group">
                    <div class="w-10 h-10 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-slate-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <p class="flex-1 text-sm font-semibold text-slate-900 group-hover:text-blue-600">Input Manual</p>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.attendance.mark-bolos') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50 transition-all group">
                    <div class="w-10 h-10 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-slate-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <p class="flex-1 text-sm font-semibold text-slate-900 group-hover:text-blue-600">Mark Bolos</p>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- System Health -->
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-5">System Health</h3>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-slate-700">Overall Status</span>
                    <span class="text-3xl font-bold text-slate-900">{{ $systemHealth['overall'] }}%</span>
                </div>
                <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 rounded-full transition-all duration-1000" style="width: {{ $systemHealth['overall'] }}%"></div>
                </div>
                <p class="text-xs font-medium text-slate-500 mt-2 capitalize">{{ $systemHealth['status'] }}</p>
            </div>

            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-slate-600">NFC Enabled</span>
                        <span class="text-sm font-bold text-slate-900">{{ $systemHealth['nfc_enabled'] }}%</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full transition-all" style="width: {{ $systemHealth['nfc_enabled'] }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-slate-600">User Accounts</span>
                        <span class="text-sm font-bold text-slate-900">{{ $systemHealth['user_accounts'] }}%</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full transition-all" style="width: {{ $systemHealth['user_accounts'] }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-slate-600">Wali Kelas Assigned</span>
                        <span class="text-sm font-bold text-slate-900">{{ $systemHealth['wali_kelas_assigned'] }}%</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full transition-all" style="width: {{ $systemHealth['wali_kelas_assigned'] }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-slate-600">Data Completeness</span>
                        <span class="text-sm font-bold text-slate-900">{{ $systemHealth['data_completeness'] }}%</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full transition-all" style="width: {{ $systemHealth['data_completeness'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Distribution -->
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-5">User Distribution</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Admin</span>
                    </div>
                    <span class="text-2xl font-bold text-slate-900">{{ $userStats['admins'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-200 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Kepala Sekolah</span>
                    </div>
                    <span class="text-2xl font-bold text-slate-900">{{ $userStats['kepala_sekolah'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-200 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Wali Kelas</span>
                    </div>
                    <span class="text-2xl font-bold text-slate-900">{{ $userStats['wali_kelas'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-200 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Siswa</span>
                    </div>
                    <span class="text-2xl font-bold text-slate-900">{{ $userStats['siswa'] }}</span>
                </div>

                <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">Status Akun</span>
                    <div class="flex gap-2">
                        <span class="px-2.5 py-1 text-xs font-bold bg-blue-600 text-white rounded-lg">{{ $userStats['active'] }} Aktif</span>
                        <span class="px-2.5 py-1 text-xs font-bold bg-slate-100 text-slate-600 rounded-lg border border-slate-200">{{ $userStats['inactive'] }} Nonaktif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Attendance Trend -->
    <div class="mb-6">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Tren Kehadiran Minggu Ini</h2>
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-sm text-slate-500">Grafik persentase kehadiran 7 hari terakhir</p>
                </div>
            </div>
            <div class="grid grid-cols-7 gap-3 items-end h-72">
            @foreach($weeklyTrend as $day)
                <div class="flex flex-col items-center">
                    <div class="relative w-full group cursor-pointer">
                        <div class="w-full rounded-t-xl transition-all duration-300 flex items-end justify-center pb-2 {{ $day['is_today'] ? 'bg-blue-600' : 'bg-slate-400 hover:bg-slate-500' }}"
                             style="height: {{ min($day['percentage'] * 2.6, 260) }}px; min-height: 30px;">
                            <span class="text-sm text-white font-bold opacity-90 group-hover:opacity-100 transition-opacity">{{ $day['percentage'] }}%</span>
                        </div>
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-3 px-4 py-3 bg-slate-900 text-white text-xs rounded-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10 shadow-xl">
                            <p class="font-bold text-sm mb-2 border-b border-slate-700 pb-2">{{ $day['day'] }}, {{ $day['date'] }}</p>
                            <div class="space-y-1">
                                <p class="flex items-center justify-between gap-4">
                                    <span class="text-slate-300">Hadir:</span>
                                    <span class="font-semibold text-white">{{ $day['hadir'] }}</span>
                                </p>
                                <p class="flex items-center justify-between gap-4">
                                    <span class="text-slate-300">Terlambat:</span>
                                    <span class="font-semibold text-white">{{ $day['terlambat'] }}</span>
                                </p>
                                <p class="flex items-center justify-between gap-4">
                                    <span class="text-slate-300">Alpha:</span>
                                    <span class="font-semibold text-white">{{ $day['alpha'] }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm font-bold mt-3 {{ $day['is_today'] ? 'text-blue-600' : 'text-slate-600' }}">{{ $day['day'] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $day['date'] }}</p>
                </div>
            @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mb-6">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Aktivitas Terbaru</h2>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-slate-900">Recent Users</h3>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-semibold group">
                    Lihat Semua
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div class="space-y-3">
                @forelse($recentUsers as $user)
                    <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                        <div class="w-12 h-12 bg-slate-200 rounded-xl flex items-center justify-center text-slate-700 font-bold text-lg group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 truncate group-hover:text-blue-600">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $user->role->display_name ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-400 font-medium mb-1">{{ $user->created_at->diffForHumans() }}</p>
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-lg {{ $user->is_active ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500">Belum ada user terdaftar</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Students -->
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-slate-900">Recent Students</h3>
                <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-semibold group">
                    Lihat Semua
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div class="space-y-3">
                @forelse($recentStudents as $student)
                    <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                        <div class="w-12 h-12 bg-slate-200 rounded-xl flex items-center justify-center text-slate-700 font-bold text-lg group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                            {{ substr($student->full_name, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 truncate group-hover:text-blue-600">{{ $student->full_name }}</p>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $student->class->name ?? '-' }} • {{ $student->nis }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-400 font-medium">{{ $student->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500">Belum ada siswa terdaftar</p>
                    </div>
                @endforelse
            </div>
        </div>
        </div>
    </div>
</div>
