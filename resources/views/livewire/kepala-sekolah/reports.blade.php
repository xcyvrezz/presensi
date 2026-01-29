<div>
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Laporan Kehadiran</h1>
        <p class="text-slate-600 mt-1">Generate dan export laporan kehadiran siswa</p>
    </div>

    <!-- Filter Section -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Filter Laporan</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Report Type -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Jenis Laporan</label>
                <select wire:model.live="reportType" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="monthly">Bulanan</option>
                    <option value="semester">Semesteran</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            @if($reportType === 'monthly')
                <!-- Month -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Bulan</label>
                    <select wire:model.live="selectedMonth" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}">{{ \Carbon\Carbon::create()->month($i)->isoFormat('MMMM') }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Year -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tahun</label>
                    <select wire:model.live="selectedYear" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        @for($year = date('Y'); $year >= date('Y') - 3; $year--)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>
            @elseif($reportType === 'semester')
                <!-- Semester -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Semester</label>
                    <select wire:model.live="selectedSemesterId" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}">
                                {{ $semester->name }}
                                ({{ \Carbon\Carbon::parse($semester->start_date)->isoFormat('D MMM Y') }} - {{ \Carbon\Carbon::parse($semester->end_date)->isoFormat('D MMM Y') }})
                                @if($semester->is_active) - Aktif @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(count($semesters) == 0)
                    <div class="col-span-2">
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                            <strong>Perhatian:</strong> Belum ada data semester. Silakan tambahkan semester terlebih dahulu di menu pengaturan.
                        </div>
                    </div>
                @endif
            @else
                <!-- Custom Date Range -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Mulai</label>
                    <input type="date" wire:model.live="startDate" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Akhir</label>
                    <input type="date" wire:model.live="endDate" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
            @endif

            <!-- Department Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Jurusan</label>
                <select wire:model.live="selectedDepartment" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="all">Semua Jurusan</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Class Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Kelas</label>
                <select wire:model.live="selectedClass" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="all">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }} - {{ $class->department->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="flex items-center gap-3 mt-6 pt-6 border-t border-slate-200">
            <button wire:click="exportPdf" class="flex items-center gap-2 px-6 py-2.5 bg-slate-700 hover:bg-slate-800 text-white font-medium rounded-xl transition-all duration-300 shadow-sm hover:shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </button>

            <button wire:click="exportExcel" class="flex items-center gap-2 px-6 py-2.5 bg-slate-600 hover:bg-slate-700 text-white font-medium rounded-xl transition-all duration-300 shadow-sm hover:shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>

            <div class="ml-auto text-sm text-slate-600">
                <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMM Y') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMM Y') }}
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Total Students -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Total Siswa</p>
                    <p class="text-4xl font-bold text-slate-900 mb-1">{{ $reportData['statistics']['total_students'] }}</p>
                    <p class="text-sm text-slate-600">Siswa aktif</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Working Days -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Hari Kerja</p>
                    <p class="text-4xl font-bold text-slate-900 mb-1">{{ $reportData['statistics']['working_days'] }}</p>
                    <p class="text-sm text-slate-600">Hari efektif</p>
                </div>
                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Attendance Percentage -->
        <div class="bg-blue-600 border border-blue-700 rounded-xl shadow-sm p-6 hover:shadow-md transition-all text-white">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wider mb-2 opacity-90">Persentase Hadir</p>
                    <p class="text-4xl font-bold mb-1">{{ number_format($reportData['statistics']['attendance_percentage'], 1) }}%</p>
                    <p class="text-sm opacity-80">{{ $reportData['statistics']['total_present'] }}/{{ $reportData['statistics']['expected_attendances'] }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Alpha -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Total Alpha</p>
                    <p class="text-4xl font-bold text-slate-900 mb-1">{{ $reportData['statistics']['total_alpha'] }}</p>
                    <p class="text-sm text-slate-600">Tanpa keterangan</p>
                </div>
                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Status Breakdown -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Breakdown Status Kehadiran</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-4 bg-blue-50 border border-blue-100 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-slate-700">Hadir</span>
                    </div>
                    <span class="text-2xl font-bold text-blue-600">{{ $reportData['statistics']['total_hadir'] }}</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-500 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-slate-700">Terlambat</span>
                    </div>
                    <span class="text-2xl font-bold text-slate-600">{{ $reportData['statistics']['total_terlambat'] }}</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-500 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-slate-700">Izin</span>
                    </div>
                    <span class="text-2xl font-bold text-slate-600">{{ $reportData['statistics']['total_izin'] }}</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-500 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-slate-700">Sakit</span>
                    </div>
                    <span class="text-2xl font-bold text-slate-600">{{ $reportData['statistics']['total_sakit'] }}</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-500 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-slate-700">Dispensasi</span>
                    </div>
                    <span class="text-2xl font-bold text-slate-600">{{ $reportData['statistics']['total_dispensasi'] }}</span>
                </div>
            </div>
        </div>

        <!-- Top 5 Departments -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Top 5 Jurusan</h3>
            <div class="space-y-3">
                @foreach($reportData['department_stats']->take(5) as $index => $dept)
                    <div class="flex items-center gap-3 p-4 rounded-xl border {{ $index === 0 ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-slate-200' }}">
                        <div class="flex-shrink-0">
                            @if($index === 0)
                                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                            @else
                                <div class="w-10 h-10 bg-slate-200 rounded-xl flex items-center justify-center text-slate-600 font-bold">{{ $index + 1 }}</div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $dept['name'] }}</p>
                            <p class="text-xs text-slate-500">{{ $dept['total_students'] }} siswa</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-blue-600">{{ $dept['percentage'] }}%</p>
                            <p class="text-xs text-slate-500">{{ $dept['present'] }}/{{ $dept['expected'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Department Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Detail Per Jurusan</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Jurusan</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Siswa</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Hadir</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Terlambat</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Izin</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Sakit</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Alpha</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Persentase</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @foreach($reportData['department_stats'] as $dept)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-900">{{ $dept['name'] }}</div>
                                <div class="text-xs text-slate-500">{{ $dept['code'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-900">{{ $dept['total_students'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-blue-600 font-semibold">{{ $dept['hadir'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600 font-semibold">{{ $dept['terlambat'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600">{{ $dept['izin'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600">{{ $dept['sakit'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600 font-semibold">{{ $dept['alpha'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-3 py-1 text-sm font-bold rounded-lg
                                    {{ $dept['percentage'] >= 90 ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $dept['percentage'] }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Class Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Detail Per Kelas</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Siswa</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Hadir</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Terlambat</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Izin</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Sakit</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Alpha</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Persentase</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @foreach($reportData['class_stats'] as $class)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-900">{{ $class['name'] }}</div>
                                <div class="text-xs text-slate-500">{{ $class['department'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-900">{{ $class['total_students'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-blue-600 font-semibold">{{ $class['hadir'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600 font-semibold">{{ $class['terlambat'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600">{{ $class['izin'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600">{{ $class['sakit'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600 font-semibold">{{ $class['alpha'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-3 py-1 text-sm font-bold rounded-lg
                                    {{ $class['percentage'] >= 90 ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $class['percentage'] }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
