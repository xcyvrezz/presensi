<div>
    <!-- Page Header -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Data Absensi</h1>
                <p class="text-sm sm:text-base text-slate-600 mt-1">Rekap lengkap absensi seluruh siswa</p>
            </div>
            <div class="flex flex-wrap gap-2 sm:gap-3">
                <button wire:click="exportExcel"
                        class="bg-slate-600 hover:bg-slate-700 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl flex items-center gap-2 transition-all duration-300 shadow-sm hover:shadow-md font-medium text-sm">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span wire:loading.remove wire:target="exportExcel" class="hidden sm:inline">Export Excel</span>
                    <span wire:loading.remove wire:target="exportExcel" class="sm:hidden">Excel</span>
                    <span wire:loading wire:target="exportExcel">...</span>
                </button>
                <button wire:click="exportPdf"
                        class="bg-slate-700 hover:bg-slate-800 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl flex items-center gap-2 transition-all duration-300 shadow-sm hover:shadow-md font-medium text-sm">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span wire:loading.remove wire:target="exportPdf" class="hidden sm:inline">Export PDF</span>
                    <span wire:loading.remove wire:target="exportPdf" class="sm:hidden">PDF</span>
                    <span wire:loading wire:target="exportPdf">...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-600 rounded-xl">
            <p class="text-sm font-medium text-blue-900">{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-600 rounded-xl">
            <p class="text-sm font-medium text-blue-900">{{ session('info') }}</p>
        </div>
    @endif

    {{-- Today's Event Alert --}}
    @if($todayEvent)
        <div class="mb-6 rounded-xl shadow-sm border overflow-hidden"
             style="border-color: {{ $todayEvent['color'] }}; border-width: 2px;">
            <div class="p-4 sm:p-5"
                 style="background: linear-gradient(135deg, {{ $todayEvent['color'] }}15 0%, {{ $todayEvent['color'] }}05 100%);">
                <div class="flex flex-col sm:flex-row items-start gap-3 sm:gap-4">
                    {{-- Icon --}}
                    <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center"
                         style="background-color: {{ $todayEvent['color'] }}20;">
                        @if($todayEvent['is_holiday'])
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" style="color: {{ $todayEvent['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        @elseif($todayEvent['type'] === 'exam')
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" style="color: {{ $todayEvent['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" style="color: {{ $todayEvent['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0 w-full">
                        {{-- Title & Badge --}}
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">{{ $todayEvent['title'] }}</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium w-fit"
                                  style="background-color: {{ $todayEvent['color'] }}20; color: {{ $todayEvent['color'] }};">
                                {{ $todayEvent['type_label'] }}
                            </span>
                        </div>

                        {{-- Description --}}
                        @if($todayEvent['description'])
                            <p class="text-xs sm:text-sm text-slate-600 mb-3">{{ $todayEvent['description'] }}</p>
                        @endif

                        {{-- Holiday Notice --}}
                        @if($todayEvent['is_holiday'])
                            <div class="inline-flex items-center gap-2 px-3 py-2 bg-red-50 border border-red-200 rounded-lg">
                                <svg class="w-4 h-4 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span class="text-xs sm:text-sm font-semibold text-red-700">HARI LIBUR - Tidak ada absensi</span>
                            </div>
                        @endif

                        {{-- Custom Times Info --}}
                        @if($todayEvent['use_custom_times'] && $todayEvent['custom_times'])
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                                {{-- Jam Masuk --}}
                                <div class="bg-white/60 backdrop-blur-sm rounded-lg p-3 border border-slate-200">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-4 h-4 flex-shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                        <p class="text-xs font-medium text-slate-500">Jam Masuk</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm sm:text-base font-bold text-slate-900">
                                            {{ \Carbon\Carbon::parse($todayEvent['custom_times']['check_in_start'])->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($todayEvent['custom_times']['check_in_end'])->format('H:i') }}
                                        </p>
                                        @if($todayEvent['custom_times']['check_in_normal'])
                                            <p class="text-xs text-slate-500">
                                                Terlambat jika > {{ \Carbon\Carbon::parse($todayEvent['custom_times']['check_in_normal'])->format('H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Jam Pulang --}}
                                <div class="bg-white/60 backdrop-blur-sm rounded-lg p-3 border border-slate-200">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-4 h-4 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                                        </svg>
                                        <p class="text-xs font-medium text-slate-500">Jam Pulang</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm sm:text-base font-bold text-slate-900">
                                            {{ \Carbon\Carbon::parse($todayEvent['custom_times']['check_out_start'])->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($todayEvent['custom_times']['check_out_end'])->format('H:i') }}
                                        </p>
                                        @if($todayEvent['custom_times']['check_out_normal'])
                                            <p class="text-xs text-slate-500">
                                                Normal pulang: {{ \Carbon\Carbon::parse($todayEvent['custom_times']['check_out_normal'])->format('H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- RFID Reader Card -->
    <div class="mb-6 bg-gradient-to-r from-blue-600 to-blue-700 border border-blue-700 rounded-xl shadow-lg p-4 sm:p-6 text-white">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex-1 w-full">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base sm:text-lg font-bold">USB RFID Card Reader</h3>
                        <p class="text-xs sm:text-sm opacity-90">Tap kartu MIFARE untuk absensi otomatis</p>
                    </div>
                </div>

                <!-- Hidden Input for USB Reader -->
                @if($rfidReaderActive)
                    <div class="mt-4 p-3 sm:p-4 bg-white/10 rounded-lg border border-white/20">
                        <label class="block text-xs sm:text-sm font-medium mb-2">Scan Area (Auto-focus)</label>
                        <input
                            type="text"
                            id="rfidInput"
                            wire:model.live="lastCardUid"
                            wire:keydown.enter="processRfidCard"
                            placeholder="Tap kartu di sini..."
                            autocomplete="off"
                            x-data
                            x-init="$el.focus(); $el.select();"
                            class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-white/20 border border-white/30 rounded-lg text-white placeholder-white/60 focus:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 font-mono text-sm sm:text-lg"
                            autofocus>
                        <p class="text-xs mt-2 opacity-75">💡 Reader USB akan otomatis mengetik Card UID di sini</p>
                    </div>
                @endif

                @if($rfidMessage)
                    <div class="mt-4 p-2.5 sm:p-3 rounded-lg {{ $rfidMessageType === 'success' ? 'bg-green-500/20 border border-green-400/30' : ($rfidMessageType === 'error' ? 'bg-red-500/20 border border-red-400/30' : ($rfidMessageType === 'warning' ? 'bg-yellow-500/20 border border-yellow-400/30' : 'bg-blue-500/20 border border-blue-400/30')) }}">
                        <div class="flex items-center gap-2">
                            @if($rfidMessageType === 'success')
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @elseif($rfidMessageType === 'error')
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @elseif($rfidMessageType === 'warning')
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @endif
                            <p class="text-xs sm:text-sm font-medium flex-1">{{ $rfidMessage }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="w-full sm:w-auto">
                <button wire:click="toggleRfidReader"
                        class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl font-semibold transition-all duration-300 shadow-md hover:shadow-lg text-sm sm:text-base {{ $rfidReaderActive ? 'bg-red-500 hover:bg-red-600' : 'bg-white text-blue-600 hover:bg-blue-50' }}">
                    <span class="flex items-center justify-center gap-2">
                        @if($rfidReaderActive)
                            <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                            Stop Reader
                        @else
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Start Reader
                        @endif
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4 mb-6 sm:mb-8">
        <!-- Hadir -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Hadir</p>
                    <p class="text-xl font-bold text-slate-900">{{ $totalPresent }}</p>
                </div>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Terlambat</p>
                    <p class="text-xl font-bold text-slate-900">{{ $totalLate }}</p>
                </div>
            </div>
        </div>

        <!-- Izin -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Izin</p>
                    <p class="text-xl font-bold text-slate-900">{{ $totalPermit }}</p>
                </div>
            </div>
        </div>

        <!-- Sakit -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sakit</p>
                    <p class="text-xl font-bold text-slate-900">{{ $totalSick }}</p>
                </div>
            </div>
        </div>

        <!-- Dispensasi -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-cyan-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Dispensasi</p>
                    <p class="text-xl font-bold text-slate-900">{{ $totalDispensasi }}</p>
                </div>
            </div>
        </div>

        <!-- Alpha -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Alpha</p>
                    <p class="text-xl font-bold text-slate-900">{{ $totalAbsent }}</p>
                </div>
            </div>
        </div>

        <!-- Bolos -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Bolos</p>
                    <p class="text-xl font-bold text-slate-900">{{ $totalBolos }}</p>
                </div>
            </div>
        </div>

        <!-- Pulang Cepat -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pulang Cepat</p>
                    <p class="text-xl font-bold text-slate-900">{{ $totalPulangCepat }}</p>
                </div>
            </div>
        </div>

        <!-- Lupa Checkout -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Lupa Checkout</p>
                    <p class="text-xl font-bold text-slate-900">{{ $totalLupaCheckout }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Dari Tanggal</label>
                <input type="date" wire:model.live="dateFrom"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Sampai Tanggal</label>
                <input type="date" wire:model.live="dateTo"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>

            <!-- Department Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Jurusan</label>
                <select wire:model.live="departmentFilter"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Semua Jurusan</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->code }} - {{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Class Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Kelas</label>
                <select wire:model.live="classFilter"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                        @if(!$departmentFilter) disabled @endif>
                    <option value="">{{ $departmentFilter ? 'Semua Kelas' : 'Pilih Jurusan Dulu' }}</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                <select wire:model.live="statusFilter"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Semua Status</option>
                    <option value="hadir">Hadir</option>
                    <option value="terlambat">Terlambat</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="dispensasi">Dispensasi</option>
                    <option value="alpha">Alpha</option>
                    <option value="bolos">Bolos</option>
                    <option value="pulang_cepat">Pulang Cepat</option>
                </select>
            </div>

            <!-- Method Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Metode</label>
                <select wire:model.live="methodFilter"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Semua Metode</option>
                    <option value="nfc_mobile">📱 NFC Mobile</option>
                    <option value="rfid_physical">💳 RFID Card</option>
                    <option value="manual">✍️ Manual</option>
                    <option value="system">🤖 System</option>
                </select>
            </div>

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Cari Siswa</label>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Nama atau NIS..."
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>

            <!-- Reset Button -->
            <div class="flex items-end">
                <button wire:click="resetFilters"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 transition-all font-medium">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">NIS</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Nama Siswa</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Check In</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Check Out</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">%</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                {{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-900">
                                {{ $attendance->student->nis }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                {{ $attendance->student->full_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                <div>{{ $attendance->student->class->name }}</div>
                                <div class="text-xs text-slate-500">{{ $attendance->student->class->department->code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                {{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                {{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs font-medium rounded-lg {{ $attendance->status === 'hadir' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                @if($attendance->check_in_method === 'nfc_mobile')
                                    <span class="text-blue-600">📱 NFC Mobile</span>
                                @elseif($attendance->check_in_method === 'rfid_physical')
                                    <span class="text-blue-600">💳 RFID Card</span>
                                @elseif($attendance->check_in_method === 'manual')
                                    <span class="text-slate-600">✍️ Manual</span>
                                @elseif($attendance->check_in_method === 'system')
                                    <span class="text-slate-600">🤖 System</span>
                                @else
                                    <span class="text-slate-600">{{ $attendance->check_in_method }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium {{ $attendance->percentage >= 75 ? 'text-blue-600' : 'text-slate-600' }}">
                                    {{ $attendance->percentage }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-500">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2">Tidak ada data absensi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($attendances->hasPages())
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    let messageClearTimeout = null;

    // Auto-focus to RFID input when reader is active
    document.addEventListener('DOMContentLoaded', () => {
        focusRfidInput();
    });

    // Listen for Livewire updates (when component re-renders)
    Livewire.hook('morph.updated', () => {
        focusRfidInput();
    });

    // Listen for Livewire commit (after any property update)
    Livewire.hook('commit', ({ component, commit, respond }) => {
        setTimeout(() => {
            focusRfidInput();
        }, 100);
    });

    // Listen for reset card UID event from Livewire
    window.addEventListener('reset-card-uid', () => {
        // Quick reset - ready for next card immediately
        setTimeout(() => {
            const input = document.getElementById('rfidInput');
            if (input) {
                input.value = '';
                input.focus();
                input.select();
                console.log('🔄 Ready for next card...');
            }
        }, 500); // Reduced from 1500ms to 500ms for faster readiness
    });

    // Listen for schedule message clear event
    window.addEventListener('schedule-message-clear', () => {
        // Clear any existing timeout
        if (messageClearTimeout) {
            clearTimeout(messageClearTimeout);
        }

        // Schedule message to be cleared after 3 seconds
        messageClearTimeout = setTimeout(() => {
            @this.set('rfidMessage', '');
            @this.set('rfidMessageType', '');
            console.log('✨ Message cleared - Ready for next scan');
        }, 3000);
    });

    function focusRfidInput() {
        setTimeout(() => {
            const input = document.getElementById('rfidInput');
            if (input) {
                input.focus();
                input.select();
                console.log('🔵 USB RFID Reader ready - Input focused');
            }
        }, 150);
    }

    // Keep focus on input field when reader is active
    setInterval(() => {
        const input = document.getElementById('rfidInput');
        if (input && document.activeElement !== input) {
            input.focus();
        }
    }, 1000);

    // Prevent accidental navigation away
    document.addEventListener('keydown', (e) => {
        const input = document.getElementById('rfidInput');
        if (input && document.activeElement !== input) {
            // Auto-focus if user types anywhere
            if (e.key.length === 1 || e.key === 'Enter') {
                input.focus();
            }
        }
    });

    // Auto-hide success/warning messages after 3 seconds
    Livewire.on('attendance-processed', () => {
        setTimeout(() => {
            // Clear the input immediately for next scan
            const input = document.getElementById('rfidInput');
            if (input) {
                input.value = '';
                input.focus();
                input.select();
            }
        }, 100);
    });
</script>
@endpush
