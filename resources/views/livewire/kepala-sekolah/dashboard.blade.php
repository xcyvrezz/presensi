<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Dashboard Eksekutif</h1>
        <p class="text-sm text-slate-600 mt-2">Ringkasan kehadiran sekolah dari awal semester/periode aktif sampai hari ini</p>
    </div>

    @if($isHoliday && $holidayInfo)
        <div class="mb-8 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border-2 border-amber-300 rounded-2xl p-6 shadow-lg">
            <h3 class="text-2xl font-bold text-amber-900">Hari Libur</h3>
            <p class="mt-2 text-lg font-semibold text-amber-800">{{ $holidayInfo->title }}</p>
            @if($holidayInfo->description)<p class="mt-2 text-sm text-amber-700">{{ $holidayInfo->description }}</p>@endif
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        <div class="bg-white border border-slate-200 rounded-xl p-6"><p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Siswa Aktif</p><p class="text-4xl font-bold text-slate-900 mt-3">{{ $overallStats['total_students'] }}</p></div>
        <div class="bg-white border border-slate-200 rounded-xl p-6"><p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Hari Efektif Periode</p><p class="text-4xl font-bold text-slate-900 mt-3">{{ $overallStats['period_effective_days'] }}</p><p class="text-sm text-slate-500 mt-2">{{ $period['label'] }}</p></div>
        <div class="bg-white border border-slate-200 rounded-xl p-6"><p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kehadiran Periode</p><p class="text-4xl font-bold text-blue-600 mt-3">{{ $overallStats['period_attendance_percentage'] }}%</p><p class="text-sm text-slate-500 mt-2">{{ $overallStats['period_present'] }} / {{ $overallStats['period_expected'] }}</p></div>
        <div class="bg-white border border-slate-200 rounded-xl p-6"><p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Hadir Hari Ini</p><p class="text-4xl font-bold text-slate-900 mt-3">{{ $isHoliday ? 'LIBUR' : $overallStats['today_present'] }}</p><p class="text-sm text-slate-500 mt-2">{{ $isHoliday ? 'Tidak ada absensi' : $overallStats['attendance_percentage'].'% hari ini' }}</p></div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <div class="xl:col-span-2 bg-white border border-slate-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-900">Perbandingan Kehadiran per Jurusan</h3>
                <span class="text-xs text-slate-500">Periode aktif</span>
            </div>
            <div class="space-y-4">
                @foreach($departmentStats as $dept)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $dept['name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $dept['total_students'] }} siswa • {{ $dept['present'] }}/{{ $dept['expected_records'] }}</p>
                            </div>
                            <p class="text-lg font-bold text-blue-600">{{ $dept['percentage'] }}%</p>
                        </div>
                        <div class="h-3 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-blue-600" style="width: {{ $dept['percentage'] }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Top 5 Kelas Periode Aktif</h3>
            <div class="space-y-3">
                @foreach($topClasses as $index => $class)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="w-9 h-9 rounded-full {{ $index === 0 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700' }} flex items-center justify-center font-bold">{{ $index + 1 }}</div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-900">{{ $class['class_name'] }}</p>
                            <p class="text-xs text-slate-500">{{ $class['department'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-blue-600">{{ $class['percentage'] }}%</p>
                            <p class="text-xs text-slate-500">{{ $class['present'] }}/{{ $class['expected_records'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6 mb-8">
        <h3 class="text-lg font-bold text-slate-900 mb-6">Tren Kehadiran Bulanan</h3>
        @php $maxPercentage = max(array_column($monthlyTrend, 'percentage')); $maxPercentage = $maxPercentage > 0 ? $maxPercentage : 100; @endphp
        <div class="grid gap-2 items-end h-64" style="grid-template-columns: repeat({{ max(count($monthlyTrend), 1) }}, minmax(0, 1fr));">
            @foreach($monthlyTrend as $month)
                <div class="flex flex-col items-center">
                    <div class="w-full bg-blue-600 rounded-t-xl flex items-end justify-center pb-2" style="height: {{ ($month['percentage'] / $maxPercentage) * 220 }}px; min-height: 20px;">
                        <span class="text-xs text-white font-bold">{{ $month['percentage'] }}%</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-2 font-medium">{{ $month['month'] }}</p>
                    <p class="text-[11px] text-slate-400">{{ $month['effective_days'] }} hari</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-6">Kehadiran Hari Ini per Jam</h3>
        @php $maxCount = max(array_column($todayAttendance, 'count')); $maxCount = $maxCount > 0 ? $maxCount : 1; @endphp
        <div class="space-y-2">
            @foreach($todayAttendance as $hour)
                <div class="flex items-center gap-3">
                    <div class="w-16 text-xs font-semibold text-slate-600">{{ $hour['hour'] }}</div>
                    <div class="flex-1 h-8 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-blue-600 rounded-full flex items-center justify-end pr-3" style="width: {{ ($hour['count'] / $maxCount) * 100 }}%">@if($hour['count'] > 0)<span class="text-xs text-white font-bold">{{ $hour['count'] }}</span>@endif</div></div>
                </div>
            @endforeach
        </div>
    </div>
</div>

