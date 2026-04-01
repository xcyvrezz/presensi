<div>
    @if(!$class)
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-6">
            <h3 class="text-lg font-bold text-slate-900">Tidak Ada Kelas</h3>
            <p class="mt-2 text-sm text-slate-600">Anda belum ditugaskan sebagai wali kelas.</p>
        </div>
    @else
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Dashboard Wali Kelas</h1>
            <p class="text-sm text-slate-600 mt-2">Selamat datang, {{ auth()->user()->name }}</p>
        </div>

        @if($isHoliday && $holidayInfo)
            <div class="mb-8 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border-2 border-amber-300 rounded-2xl p-6 shadow-lg">
                <h3 class="text-2xl font-bold text-amber-900">Hari Libur</h3>
                <p class="mt-2 text-lg font-semibold text-amber-800">{{ $holidayInfo->title }}</p>
                @if($holidayInfo->description)<p class="mt-2 text-sm text-amber-700">{{ $holidayInfo->description }}</p>@endif
            </div>
        @endif

        <div class="bg-blue-600 border border-blue-700 rounded-xl p-6 mb-8 text-white shadow-lg shadow-blue-600/20">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold">{{ $class->name }}</h2>
                    <p class="text-blue-100 mt-1">{{ $class->department->name }} ({{ $class->department->code }})</p>
                    <p class="text-blue-100 mt-1">Tingkat {{ $class->grade }} • Tahun Ajaran {{ $class->academic_year }}</p>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold">{{ $totalStudents }}</div>
                    <div class="text-blue-100">Total Siswa Aktif</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
            <div class="xl:col-span-2 bg-white border border-slate-200 rounded-xl p-6">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <p class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Ringkasan periode aktif</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $attendancePercentage }}%</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ $activePeriodLabel }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-500">Hari efektif</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $summaryStats['effective_days'] ?? 0 }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="rounded-xl bg-blue-50 border border-blue-100 p-4"><p class="text-xs text-slate-500">Kehadiran efektif</p><p class="text-2xl font-bold text-slate-900">{{ $summaryStats['present_count'] ?? 0 }}</p></div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4"><p class="text-xs text-slate-500">Ekspektasi catatan</p><p class="text-2xl font-bold text-slate-900">{{ $summaryStats['expected_records'] ?? 0 }}</p></div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4"><p class="text-xs text-slate-500">Alpha + Bolos</p><p class="text-2xl font-bold text-slate-900">{{ ($summaryStats['alpha_count'] ?? 0) + ($summaryStats['bolos_count'] ?? 0) }}</p></div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4"><p class="text-xs text-slate-500">Belum tercatat</p><p class="text-2xl font-bold text-slate-900">{{ $summaryStats['missing_records'] ?? 0 }}</p></div>
                </div>
                <div class="mt-5 w-full bg-slate-100 rounded-full h-3"><div class="h-3 rounded-full bg-blue-600" style="width: {{ $attendancePercentage }}%"></div></div>
                <p class="text-xs text-slate-500 mt-2">Persentase kelas dihitung dari awal semester aktif sampai hari ini, hanya Senin-Jumat, dan mengecualikan hari libur kalender yang berlaku untuk kelas ini.</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h3 class="text-base font-bold text-slate-900 mb-4">Snapshot Hari Ini</h3>
                <div class="space-y-4">
                    <div class="flex justify-between"><span class="text-slate-600">Hadir hari ini</span><strong>{{ $presentToday }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-600">Terlambat</span><strong>{{ $lateToday }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-600">Belum/tdk hadir</span><strong>{{ $absentToday }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-600">Izin</span><strong>{{ $summaryStats['permit_count'] ?? 0 }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-600">Sakit</span><strong>{{ $summaryStats['sick_count'] ?? 0 }}</strong></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <a href="{{ route('wali-kelas.students') }}" class="flex items-center gap-3 p-4 bg-white border border-slate-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                <div class="w-12 h-12 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors"></div>
                <div class="flex-1"><p class="text-sm font-semibold text-slate-900 group-hover:text-blue-600">Daftar Siswa</p><p class="text-xs text-slate-500">Lihat semua siswa</p></div>
            </a>
            <a href="{{ route('wali-kelas.attendance') }}" class="flex items-center gap-3 p-4 bg-white border border-slate-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                <div class="w-12 h-12 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors"></div>
                <div class="flex-1"><p class="text-sm font-semibold text-slate-900 group-hover:text-blue-600">Rekap Absensi</p><p class="text-xs text-slate-500">Lihat rekap data</p></div>
            </a>
            <div class="flex items-center gap-3 p-4 bg-white border border-slate-200 rounded-xl"><div class="flex-1"><p class="text-xs text-slate-500 font-semibold">Tanggal</p><p class="text-sm font-bold text-slate-900">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p></div></div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200"><h3 class="text-lg font-bold text-slate-900">Absensi Terbaru Hari Ini</h3></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">NIS</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Check In</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Check Out</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($recentAttendances as $attendance)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-slate-900">{{ $attendance->student->full_name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $attendance->student->nis }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700 capitalize">{{ str_replace('_', ' ', $attendance->status) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">Belum ada absensi hari ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

