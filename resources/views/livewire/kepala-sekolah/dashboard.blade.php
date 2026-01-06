<div>
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Dashboard Eksekutif</h1>
        <p class="text-sm text-slate-600 mt-2">Ringkasan statistik kehadiran sekolah</p>
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

    <!-- Overall Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Total Students -->
        <div class="group bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-md transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Total Siswa</p>
                    <p class="text-4xl font-bold text-slate-900 mb-2">{{ $overallStats['total_students'] }}</p>
                    <p class="text-sm text-slate-600">Siswa aktif</p>
                </div>
                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Classes -->
        <div class="group bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-md transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Total Kelas</p>
                    <p class="text-4xl font-bold text-slate-900 mb-2">{{ $overallStats['total_classes'] }}</p>
                    <p class="text-sm text-slate-600">{{ $overallStats['total_departments'] }} jurusan</p>
                </div>
                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Today's Present -->
        <div class="group bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-md transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Hadir Hari Ini</p>
                    @if($isHoliday)
                        <p class="text-4xl font-bold text-amber-600 mb-2">LIBUR</p>
                        <p class="text-sm text-slate-600">Hari libur - tidak ada absensi</p>
                    @else
                        <p class="text-4xl font-bold text-slate-900 mb-2">{{ $overallStats['today_present'] }}</p>
                        <p class="text-sm text-slate-600">{{ $overallStats['attendance_percentage'] }}% kehadiran</p>
                    @endif
                </div>
                <div class="w-12 h-12 {{ $isHoliday ? 'bg-amber-50' : 'bg-blue-50' }} rounded-xl flex items-center justify-center group-hover:{{ $isHoliday ? 'bg-amber-100' : 'bg-blue-100' }} transition-colors">
                    @if($isHoliday)
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </div>
            </div>
        </div>

        <!-- Today's Absent -->
        <div class="group bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-md transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Tidak Hadir</p>
                    @if($isHoliday)
                        <p class="text-4xl font-bold text-slate-400 mb-2">-</p>
                        <p class="text-sm text-slate-600">-</p>
                    @else
                        <p class="text-4xl font-bold text-slate-900 mb-2">{{ $overallStats['today_absent'] }}</p>
                        <p class="text-sm text-slate-600">Belum absen hari ini</p>
                    @endif
                </div>
                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    @if(!$isHoliday)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Department Comparison -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Perbandingan Kehadiran per Jurusan</h3>

            @php
                $maxStudents = $departmentStats->max('total_students');
                $maxStudents = $maxStudents > 0 ? $maxStudents : 1;
            @endphp

            <div class="space-y-4">
                @foreach($departmentStats as $dept)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-blue-600"></div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $dept['name'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $dept['total_students'] }} siswa</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-blue-600">{{ $dept['percentage'] }}%</p>
                                <p class="text-xs text-slate-500">{{ $dept['present'] }}/{{ $dept['total_students'] }}</p>
                            </div>
                        </div>
                        <div class="h-4 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full transition-all duration-1000"
                                 style="width: {{ ($dept['total_students'] / $maxStudents) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Top 5 Classes -->
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Top 5 Kelas Hari Ini</h3>

            <div class="space-y-3">
                @foreach($topClasses as $index => $class)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 {{ $index === 0 ? 'bg-blue-600' : 'bg-slate-200' }} rounded-full flex items-center justify-center {{ $index === 0 ? 'text-white' : 'text-slate-600' }} font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $class['class_name'] }}</p>
                            <p class="text-xs text-slate-500">{{ $class['department'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-blue-600">{{ $class['percentage'] }}%</p>
                            <p class="text-xs text-slate-500">{{ $class['present'] }}/{{ $class['total_students'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Monthly Trend -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 mb-8">
        <h3 class="text-lg font-bold text-slate-900 mb-6">Tren Kehadiran Bulanan {{ now()->year }}</h3>

        @php
            $maxPercentage = max(array_column($monthlyTrend, 'percentage'));
            $maxPercentage = $maxPercentage > 0 ? $maxPercentage : 100;
        @endphp

        <div class="grid grid-cols-12 gap-2 items-end h-64">
            @foreach($monthlyTrend as $month)
                <div class="flex flex-col items-center">
                    <div class="relative w-full group cursor-pointer">
                        <div class="w-full bg-blue-600 rounded-t-xl hover:bg-blue-700 transition-all duration-300 flex items-end justify-center pb-2"
                             style="height: {{ ($month['percentage'] / $maxPercentage) * 240 }}px; min-height: 20px;">
                            <span class="text-xs text-white font-bold opacity-0 group-hover:opacity-100 transition-opacity">{{ $month['percentage'] }}%</span>
                        </div>
                        <!-- Tooltip -->
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-slate-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10 shadow-xl">
                            <p class="font-bold border-b border-slate-700 pb-1 mb-1">{{ $month['month'] }}</p>
                            <p>Hadir: {{ $month['present'] }}</p>
                            <p>Terlambat: {{ $month['late'] }}</p>
                            <p>Alpha: {{ $month['absent'] }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 mt-2 font-medium">{{ $month['month'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Today's Attendance by Hour -->
    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-6">Kehadiran Hari Ini per Jam</h3>

        @php
            $maxCount = max(array_column($todayAttendance, 'count'));
            $maxCount = $maxCount > 0 ? $maxCount : 1;
        @endphp

        <div class="space-y-2">
            @foreach($todayAttendance as $hour)
                <div class="flex items-center gap-3">
                    <div class="w-16 text-xs font-semibold text-slate-600">{{ $hour['hour'] }}</div>
                    <div class="flex-1 h-8 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full flex items-center justify-end pr-3 transition-all duration-1000"
                             style="width: {{ ($hour['count'] / $maxCount) * 100 }}%">
                            @if($hour['count'] > 0)
                                <span class="text-xs text-white font-bold">{{ $hour['count'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
