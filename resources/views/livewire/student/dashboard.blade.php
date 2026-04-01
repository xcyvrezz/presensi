<div>
    <div class="mb-8 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Selamat Datang, {{ $student->full_name }}!</h1>
            <p class="mt-2 text-sm text-slate-600">{{ $student->class->name }} - {{ $student->class->department->name }}</p>
        </div>
        <button wire:click="refreshData" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl flex items-center gap-2 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Refresh
        </button>
    </div>

    @if($isHoliday && $holidayInfo)
        <div class="mb-8 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border-2 border-amber-300 rounded-2xl p-6 shadow-lg">
            <h3 class="text-2xl font-bold text-amber-900">Hari Libur</h3>
            <p class="mt-2 text-lg font-semibold text-amber-800">{{ $holidayInfo->title }}</p>
            @if($holidayInfo->description)<p class="mt-2 text-sm text-amber-700">{{ $holidayInfo->description }}</p>@endif
        </div>
    @endif

    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl">{{ session('success') }}</div>
    @endif

    @if(!$isHoliday)
        <div class="mb-8 bg-white border border-slate-200 rounded-xl p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">Absensi Hari Ini</h2>
            @if($todayAttendance)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="p-5 rounded-xl bg-blue-50 border border-blue-200">
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">Check-In</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $todayAttendance->check_in_time ? $todayAttendance->check_in_time->format('H:i') : '-' }}</p>
                        @if($todayAttendance->check_in_time && $todayAttendance->late_minutes > 0)<p class="text-xs text-slate-600 mt-1">Terlambat {{ $todayAttendance->late_minutes }} menit</p>@endif
                    </div>
                    <div class="p-5 rounded-xl {{ $todayAttendance->check_out_time ? 'bg-blue-50 border border-blue-200' : 'bg-slate-50 border border-slate-200' }}">
                        <p class="text-xs font-semibold {{ $todayAttendance->check_out_time ? 'text-blue-600' : 'text-slate-500' }} uppercase tracking-wider mb-1">Check-Out</p>
                        <p class="text-2xl font-bold {{ $todayAttendance->check_out_time ? 'text-blue-900' : 'text-slate-400' }}">{{ $todayAttendance->check_out_time ? $todayAttendance->check_out_time->format('H:i') : 'Belum Check-Out' }}</p>
                        @if($todayAttendance->check_out_time && $todayAttendance->early_leave_minutes > 0)<p class="text-xs text-slate-600 mt-1">Pulang cepat {{ $todayAttendance->early_leave_minutes }} menit</p>@endif
                    </div>
                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-200">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Status Hari Ini</p>
                        <p class="text-lg font-bold text-slate-900 capitalize">{{ str_replace('_', ' ', $todayAttendance->status) }}</p>
                        <p class="text-sm text-slate-600 mt-1">{{ $todayAttendance->percentage }}% persentase</p>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <h3 class="text-lg font-bold text-slate-900">Belum Melakukan Absensi</h3>
                    <p class="mt-2 text-sm text-slate-500">Anda belum melakukan check-in hari ini.</p>
                </div>
            @endif
        </div>
    @endif

    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900">Statistik Kehadiran</h2>
            <div class="relative inline-block" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 text-sm font-medium text-slate-700 transition-colors">
                    <span>{{ $periodLabel }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-lg shadow-lg z-10">
                    <div class="py-1">
                        <button wire:click="changePeriod('semester')" @click="open = false" class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 {{ $periodFilter === 'semester' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700' }}">Semester Aktif</button>
                        <button wire:click="changePeriod('month')" @click="open = false" class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 {{ $periodFilter === 'month' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700' }}">Bulan Ini</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5 items-stretch">
            <div class="bg-white border border-slate-200 rounded-xl p-6 h-full">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <p class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Ringkasan periode aktif</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $attendancePercentage }}%</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ \Carbon\Carbon::parse($periodStartDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($periodEndDate)->translatedFormat('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-500">Hari efektif</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $workingDays }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="rounded-xl bg-blue-50 border border-blue-100 p-4">
                        <p class="text-xs text-slate-500">Hadir efektif</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $summaryStats['present_count'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                        <p class="text-xs text-slate-500">Izin + Sakit</p>
                        <p class="text-2xl font-bold text-slate-900">{{ ($summaryStats['permit_count'] ?? 0) + ($summaryStats['sick_count'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                        <p class="text-xs text-slate-500">Alpha + Bolos</p>
                        <p class="text-2xl font-bold text-slate-900">{{ ($summaryStats['alpha_count'] ?? 0) + ($summaryStats['bolos_count'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                        <p class="text-xs text-slate-500">Belum tercatat</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $summaryStats['missing_records'] ?? 0 }}</p>
                    </div>
                </div>
                <div class="mt-5">
                    <div class="w-full bg-slate-100 rounded-full h-3">
                        <div class="h-3 rounded-full {{ $attendancePercentage >= 80 ? 'bg-green-600' : ($attendancePercentage >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $attendancePercentage }}%"></div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Persentase dihitung dari awal semester/periode aktif sampai hari ini, hanya Senin-Jumat, dan mengecualikan hari libur pada kalender.</p>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-6 h-full">
                <h3 class="text-base font-bold text-slate-900 mb-4">Komposisi Status</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span>Hadir</span><strong>{{ $totalPresent }}</strong></div>
                    <div class="flex justify-between"><span>Terlambat</span><strong>{{ $totalLate }}</strong></div>
                    <div class="flex justify-between"><span>Dispensasi</span><strong>{{ $totalDispensasi }}</strong></div>
                    <div class="flex justify-between"><span>Izin</span><strong>{{ $totalPermit }}</strong></div>
                    <div class="flex justify-between"><span>Sakit</span><strong>{{ $totalSick }}</strong></div>
                    <div class="flex justify-between"><span>Alpha</span><strong>{{ $totalAbsent }}</strong></div>
                    <div class="flex justify-between"><span>Bolos</span><strong>{{ $totalBolos }}</strong></div>
                    <div class="flex justify-between"><span>Pulang cepat</span><strong>{{ $totalPulangCepat }}</strong></div>
                    <div class="flex justify-between"><span>Lupa checkout</span><strong>{{ $totalLupaCheckout }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-8 bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900">Riwayat Absensi Terbaru</h2>
            <a href="{{ route('student.attendance.history') }}" class="text-sm text-blue-600 hover:text-blue-700 font-semibold">Lihat Semua</a>
        </div>
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
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm text-slate-700">{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700 capitalize">{{ str_replace('_', ' ', $attendance->status) }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-blue-600">{{ $attendance->percentage }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">Belum ada riwayat absensi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

