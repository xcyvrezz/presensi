<div>
    <!-- Welcome Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Selamat Datang, {{ $student->full_name }}!</h1>
                <p class="text-sm text-slate-600 mt-2">{{ $student->class->name }} - {{ $student->class->department->name }}</p>
            </div>
            <button wire:click="refreshData" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl flex items-center gap-2 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Holiday Banner -->
    @if($isHoliday && $holidayInfo)
        <div class="mb-8 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border-2 border-amber-300 rounded-2xl p-6 shadow-lg">
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
                        <h3 class="text-2xl font-bold text-amber-900">🎉 Hari Libur</h3>
                        <span class="px-3 py-1 bg-amber-200 text-amber-900 text-xs font-bold rounded-full uppercase">{{ $holidayInfo->type === 'national' ? 'Nasional' : 'Sekolah' }}</span>
                    </div>
                    <p class="text-lg font-semibold text-amber-800 mb-2">{{ $holidayInfo->title }}</p>
                    @if($holidayInfo->description)
                        <p class="text-sm text-amber-700">{{ $holidayInfo->description }}</p>
                    @endif
                    <div class="mt-4 p-3 bg-white bg-opacity-50 rounded-lg">
                        <p class="text-sm text-amber-800 font-medium">Selamat beristirahat! Gunakan waktu libur dengan baik untuk bersantai dan mengisi energi.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <!-- Today's Attendance Status -->
    @if(!$isHoliday)
        <div class="mb-8">
            <h2 class="text-lg font-bold text-slate-900 mb-4">Absensi Hari Ini</h2>
            <div class="bg-white border border-slate-200 rounded-xl p-6">
            @if($todayAttendance)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- Check-In Status -->
                    <div class="p-5 rounded-xl bg-blue-50 border border-blue-200">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">Check-In</p>
                                <p class="text-2xl font-bold text-blue-900">
                                    {{ $todayAttendance->check_in_time ? $todayAttendance->check_in_time->format('H:i') : '-' }}
                                </p>
                                @if($todayAttendance->check_in_time && $todayAttendance->late_minutes > 0)
                                    <p class="text-xs text-slate-600 mt-1">Terlambat {{ $todayAttendance->late_minutes }} menit</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Check-Out Status -->
                    <div class="p-5 rounded-xl {{ $todayAttendance->check_out_time ? 'bg-blue-50 border border-blue-200' : 'bg-slate-50 border border-slate-200' }}">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 {{ $todayAttendance->check_out_time ? 'bg-blue-600' : 'bg-slate-200' }} rounded-xl flex items-center justify-center">
                                    <svg class="h-6 w-6 {{ $todayAttendance->check_out_time ? 'text-white' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-semibold {{ $todayAttendance->check_out_time ? 'text-blue-600' : 'text-slate-500' }} uppercase tracking-wider mb-1">Check-Out</p>
                                @if($todayAttendance->check_out_time)
                                    <p class="text-2xl font-bold text-blue-900">{{ $todayAttendance->check_out_time->format('H:i') }}</p>
                                    @if($todayAttendance->early_leave_minutes > 0)
                                        <p class="text-xs text-slate-600 mt-1">Pulang cepat {{ $todayAttendance->early_leave_minutes }} menit</p>
                                    @endif
                                @else
                                    <p class="text-lg text-slate-400 font-semibold">Belum Check-Out</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Status & Percentage -->
                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-slate-200 rounded-xl flex items-center justify-center">
                                    <svg class="h-6 w-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</p>
                                <p class="text-lg font-bold text-slate-900 capitalize">{{ str_replace('_', ' ', $todayAttendance->status) }}</p>
                                <p class="text-sm text-slate-600 mt-1">{{ $todayAttendance->percentage }}% Persentase</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between pt-5 border-t border-slate-200">
                    <div class="text-sm text-slate-600">
                        <span class="font-semibold">Metode:</span>
                        @if($todayAttendance->method === 'nfc')
                            📱 NFC Mobile
                        @elseif($todayAttendance->method === 'rfid')
                            💳 RFID Card
                        @else
                            {{ ucfirst($todayAttendance->method) }}
                        @endif
                    </div>
                    @if($canCheckOut)
                        <a href="{{ route('student.nfc') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                            Check-Out Sekarang
                        </a>
                    @endif
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Belum Melakukan Absensi</h3>
                    <p class="mt-2 text-sm text-slate-500">Anda belum melakukan check-in hari ini</p>
                    @if($canCheckIn)
                        <div class="mt-6">
                            <a href="{{ route('student.nfc') }}" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                Check-In Sekarang
                            </a>
                        </div>
                    @else
                        <p class="mt-4 text-xs text-slate-500">Waktu check-in: 05:00 - 07:00</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Monthly Statistics -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900">Statistik Kehadiran</h2>

            <!-- Period Filter Dropdown -->
            <div class="relative inline-block" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 text-sm font-medium text-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $periodLabel }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-lg shadow-lg z-10">
                    <div class="py-1">
                        <button wire:click="changePeriod('semester')" @click="open = false"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 {{ $periodFilter === 'semester' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700' }}">
                            <div class="flex items-center justify-between">
                                <span>Semester Aktif</span>
                                @if($periodFilter === 'semester')
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                        </button>
                        <button wire:click="changePeriod('month')" @click="open = false"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 {{ $periodFilter === 'month' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700' }}">
                            <div class="flex items-center justify-between">
                                <span>Bulan Ini</span>
                                @if($periodFilter === 'month')
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-9 gap-4">
            <!-- Hadir -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-green-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Hadir</p>
                        <p class="text-xl font-bold text-slate-900">{{ $totalPresent }}</p>
                    </div>
                </div>
            </div>

            <!-- Terlambat -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-yellow-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Terlambat</p>
                        <p class="text-xl font-bold text-slate-900">{{ $totalLate }}</p>
                    </div>
                </div>
            </div>

            <!-- Izin -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Izin</p>
                        <p class="text-xl font-bold text-slate-900">{{ $totalPermit }}</p>
                    </div>
                </div>
            </div>

            <!-- Sakit -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-purple-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Sakit</p>
                        <p class="text-xl font-bold text-slate-900">{{ $totalSick }}</p>
                    </div>
                </div>
            </div>

            <!-- Dispensasi -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-cyan-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-cyan-50 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Dispensasi</p>
                        <p class="text-xl font-bold text-slate-900">{{ $totalDispensasi }}</p>
                    </div>
                </div>
            </div>

            <!-- Alpha -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-slate-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Alpha</p>
                        <p class="text-xl font-bold text-slate-900">{{ $totalAbsent }}</p>
                    </div>
                </div>
            </div>

            <!-- Bolos -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-red-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Bolos</p>
                        <p class="text-xl font-bold text-slate-900">{{ $totalBolos }}</p>
                    </div>
                </div>
            </div>

            <!-- Pulang Cepat -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-orange-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Pulang Cepat</p>
                        <p class="text-xl font-bold text-slate-900">{{ $totalPulangCepat }}</p>
                    </div>
                </div>
            </div>

            <!-- Lupa Checkout -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-amber-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Lupa Checkout</p>
                        <p class="text-xl font-bold text-slate-900">{{ $totalLupaCheckout }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Percentage -->
        <div class="mt-5 bg-white border border-slate-200 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <span class="text-sm font-semibold text-slate-700">Tingkat Kehadiran</span>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $totalPresent + $totalLate + $totalDispensasi }} dari {{ $workingDays }} hari efektif</p>
                </div>
                <span class="text-2xl font-bold text-blue-600">{{ $attendancePercentage }}%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-3">
                <div class="h-3 rounded-full {{ $attendancePercentage >= 80 ? 'bg-green-600' : ($attendancePercentage >= 60 ? 'bg-yellow-500' : 'bg-red-500') }} transition-all duration-1000"
                     style="width: {{ $attendancePercentage }}%"></div>
            </div>
            <p class="text-xs text-slate-500 mt-2">
                @if($attendancePercentage >= 80)
                    <span class="text-green-600 font-semibold">✓ Sangat Baik!</span> Pertahankan kehadiran Anda.
                @elseif($attendancePercentage >= 60)
                    <span class="text-yellow-600 font-semibold">⚠ Cukup Baik.</span> Tingkatkan lagi kehadiran Anda.
                @else
                    <span class="text-red-600 font-semibold">⚠ Perlu Ditingkatkan!</span> Tingkatkan kehadiran Anda segera.
                @endif
            </p>
        </div>
    </div>

    <!-- Recent Attendance History -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900">Riwayat Absensi Terbaru</h2>
            <a href="{{ route('student.attendance.history') }}" class="text-sm text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1 group">
                Lihat Semua
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Check-In</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Check-Out</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($recentAttendances as $attendance)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 font-medium">
                                    {{ $attendance->check_in_time ? $attendance->check_in_time->format('d/m/Y') : \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                    {{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                    {{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($attendance->status === 'hadir')
                                        <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-lg bg-blue-600 text-white">Hadir</span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-blue-600">{{ $attendance->percentage }}%</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="mt-2 text-sm">Belum ada riwayat absensi</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- NFC Check-In/Out -->
        <a href="{{ route('student.nfc') }}" class="bg-blue-600 hover:bg-blue-700 border border-blue-700 rounded-xl p-6 text-white transition-all shadow-lg shadow-blue-600/20">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-bold">Absensi NFC</h3>
                    <p class="text-sm text-blue-100 mt-0.5">Tap kartu untuk absensi</p>
                </div>
            </div>
        </a>

        <!-- Absence Request -->
        <a href="{{ route('student.absence.request') }}" class="flex items-center gap-4 p-6 bg-white border border-slate-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors">
                    <svg class="h-6 w-6 text-slate-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Ajukan Izin</h3>
                <p class="text-sm text-slate-500 mt-0.5">Izin sakit atau keperluan</p>
            </div>
        </a>

        <!-- View Statistics -->
        <a href="{{ route('student.statistics') }}" class="flex items-center gap-4 p-6 bg-white border border-slate-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors">
                    <svg class="h-6 w-6 text-slate-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Statistik Lengkap</h3>
                <p class="text-sm text-slate-500 mt-0.5">Lihat grafik kehadiran</p>
            </div>
        </a>
    </div>
</div>
