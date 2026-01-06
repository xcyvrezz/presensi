<div>
    @if(!$class)
        <!-- No Class Assigned -->
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-slate-200 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-slate-900">Tidak Ada Kelas</h3>
                    <p class="mt-1 text-sm text-slate-600">
                        Anda belum ditugaskan sebagai wali kelas untuk kelas manapun. Silakan hubungi administrator untuk penugasan kelas.
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Dashboard Wali Kelas</h1>
            <p class="text-sm text-slate-600 mt-2">Selamat datang, {{ auth()->user()->name }}</p>
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

        <!-- Class Info Card -->
        <div class="bg-blue-600 border border-blue-700 rounded-xl p-6 mb-8 text-white shadow-lg shadow-blue-600/20">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">{{ $class->name }}</h2>
                    <p class="text-blue-100 mt-1">{{ $class->department->name }} ({{ $class->department->code }})</p>
                    <p class="text-blue-100 mt-1">Tingkat {{ $class->grade }} • Tahun Ajaran {{ $class->academic_year }}</p>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold">{{ $totalStudents }}</div>
                    <div class="text-blue-100">Total Siswa</div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        @if(!$isHoliday)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
            <!-- Present Today -->
            <div class="group bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Hadir Hari Ini</p>
                        <p class="text-4xl font-bold text-slate-900 mb-2">{{ $presentToday }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Late Today -->
            <div class="group bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Terlambat</p>
                        <p class="text-4xl font-bold text-slate-900 mb-2">{{ $lateToday }}</p>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Absent Today -->
            <div class="group bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Tidak Hadir</p>
                        <p class="text-4xl font-bold text-slate-900 mb-2">{{ $absentToday }}</p>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Attendance Percentage -->
            <div class="group bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Persentase Hadir</p>
                        <p class="text-4xl font-bold text-slate-900 mb-2">{{ $attendancePercentage }}%</p>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <a href="{{ route('wali-kelas.students') }}" class="flex items-center gap-3 p-4 bg-white border border-slate-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                <div class="w-12 h-12 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors">
                    <svg class="w-6 h-6 text-slate-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-slate-900 group-hover:text-blue-600">Daftar Siswa</p>
                    <p class="text-xs text-slate-500">Lihat semua siswa</p>
                </div>
                <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <a href="{{ route('wali-kelas.attendance') }}" class="flex items-center gap-3 p-4 bg-white border border-slate-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                <div class="w-12 h-12 bg-slate-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors">
                    <svg class="w-6 h-6 text-slate-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-slate-900 group-hover:text-blue-600">Rekap Absensi</p>
                    <p class="text-xs text-slate-500">Lihat rekap data</p>
                </div>
                <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <div class="flex items-center gap-3 p-4 bg-white border border-slate-200 rounded-xl">
                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-xs text-slate-500 font-semibold">Tanggal</p>
                    <p class="text-sm font-bold text-slate-900">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-900">Absensi Terbaru Hari Ini</h3>
            </div>
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($attendance->student->photo)
                                            <img src="{{ Storage::url($attendance->student->photo) }}" alt="{{ $attendance->student->full_name }}" class="w-8 h-8 rounded-full object-cover mr-3">
                                        @else
                                            <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-slate-600 text-xs font-semibold">{{ substr($attendance->student->full_name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div class="text-sm font-medium text-slate-900">{{ $attendance->student->full_name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ $attendance->student->nis }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                    {{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                    {{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($attendance->status === 'hadir')
                                        <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-600 text-white">Hadir</span>
                                    @elseif($attendance->status === 'terlambat')
                                        <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-700 border border-slate-200">Terlambat</span>
                                    @elseif($attendance->status === 'izin')
                                        <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-700 border border-slate-200">Izin</span>
                                    @elseif($attendance->status === 'sakit')
                                        <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-700 border border-slate-200">Sakit</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-700 border border-slate-200">Alpha</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="mt-2 text-sm">Belum ada absensi hari ini</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
