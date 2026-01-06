<div>
    <!-- Page Header -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Analitik & Laporan Lanjutan</h1>
            <div class="flex gap-2">
                <button wire:click="exportExcel" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-600 hover:bg-slate-700 text-white rounded-xl flex items-center gap-2 transition-all shadow-sm hover:shadow-md font-medium text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span wire:loading.remove wire:target="exportExcel" class="hidden sm:inline">Export Excel</span>
                    <span wire:loading.remove wire:target="exportExcel" class="sm:hidden">Excel</span>
                    <span wire:loading wire:target="exportExcel">...</span>
                </button>
                <button wire:click="exportPdf" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-700 hover:bg-slate-800 text-white rounded-xl flex items-center gap-2 transition-all shadow-sm hover:shadow-md font-medium text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span wire:loading.remove wire:target="exportPdf" class="hidden sm:inline">Export PDF</span>
                    <span wire:loading.remove wire:target="exportPdf" class="sm:hidden">PDF</span>
                    <span wire:loading wire:target="exportPdf">...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Report Type -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Jenis Laporan</label>
                <select wire:model.live="reportType" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="overview">Ringkasan Umum</option>
                    <option value="trends">Tren Kehadiran</option>
                    <option value="comparison">Perbandingan Jurusan</option>
                    <option value="detailed">Detail & Ranking</option>
                    <option value="timeanalysis">Analisis Waktu</option>
                    <option value="insights">Insights & Rekomendasi</option>
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Periode</label>
                <select wire:model.live="dateRange" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="7days">7 Hari Terakhir</option>
                    <option value="30days">30 Hari Terakhir</option>
                    <option value="3months">3 Bulan Terakhir</option>
                    <option value="6months">6 Bulan Terakhir</option>
                    <option value="semester">Semester Aktif</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            <!-- Department Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Jurusan</label>
                <select wire:model.live="selectedDepartment" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Semua Jurusan</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Class Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Kelas</label>
                <select wire:model.live="selectedClass" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Custom Date Range -->
        @if($dateRange === 'custom')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Mulai</label>
                    <input type="date" wire:model="customStartDate" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Selesai</label>
                    <input type="date" wire:model="customEndDate" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                <div class="flex items-end">
                    <button wire:click="applyCustomDate" class="w-full px-4 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-all duration-300 shadow-sm hover:shadow-md">
                        Terapkan
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Overview Report -->
    @if($reportType === 'overview')
        <!-- Summary Cards Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5 mb-6">
            <!-- Completion Rate -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tingkat Kelengkapan</p>
                        <p class="text-3xl sm:text-4xl font-bold text-slate-900 mb-1">{{ $statistics['completion_rate'] ?? 0 }}%</p>
                        <p class="text-xs sm:text-sm text-slate-600">{{ number_format($statistics['total_records'] ?? 0) }} dari {{ number_format($statistics['expected_records'] ?? 0) }} rekaman</p>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Attendance Rate -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tingkat Kehadiran</p>
                        <p class="text-3xl sm:text-4xl font-bold text-slate-900 mb-1">{{ $statistics['attendance_rate'] ?? 0 }}%</p>
                        <p class="text-xs sm:text-sm text-slate-600">Rata-rata Nilai: {{ $statistics['avg_percentage'] ?? 0 }}%</p>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Problem Cases -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Kasus Bermasalah</p>
                        <p class="text-3xl sm:text-4xl font-bold text-slate-900 mb-1">{{ number_format($statistics['problem_cases'] ?? 0) }}</p>
                        <p class="text-xs sm:text-sm text-slate-600">Alpha: {{ $statistics['alpha'] ?? 0 }}, Bolos: {{ $statistics['bolos'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Statistics Row -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 sm:p-6 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Total Hadir</p>
                        <p class="text-2xl sm:text-3xl font-bold text-slate-900 mb-1">{{ number_format($statistics['hadir'] ?? 0) }}</p>
                        <p class="text-xs text-slate-600">Dari {{ number_format($statistics['total_records'] ?? 0) }} total</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 sm:p-6 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Total Terlambat</p>
                        <p class="text-2xl sm:text-3xl font-bold text-slate-900 mb-1">{{ number_format($statistics['terlambat'] ?? 0) }}</p>
                        <p class="text-xs text-slate-600">Rata-rata: {{ $statistics['avg_late_minutes'] ?? 0 }} menit</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-yellow-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 sm:p-6 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Izin & Sakit</p>
                        <p class="text-2xl sm:text-3xl font-bold text-slate-900 mb-1">{{ number_format(($statistics['izin'] ?? 0) + ($statistics['sakit'] ?? 0)) }}</p>
                        <p class="text-xs text-slate-600">Izin: {{ $statistics['izin'] ?? 0 }}, Sakit: {{ $statistics['sakit'] ?? 0 }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 sm:p-6 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Total Alpha</p>
                        <p class="text-2xl sm:text-3xl font-bold text-slate-900 mb-1">{{ number_format($statistics['alpha'] ?? 0) }}</p>
                        <p class="text-xs text-slate-600">Tidak hadir tanpa izin</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-slate-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Statistics Row -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:shadow-md transition-all">
                <div class="text-center">
                    <div class="w-10 h-10 bg-cyan-50 rounded-lg flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ number_format($statistics['dispensasi'] ?? 0) }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Dispensasi</p>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:shadow-md transition-all">
                <div class="text-center">
                    <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ number_format($statistics['alpha'] ?? 0) }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Alpha</p>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:shadow-md transition-all">
                <div class="text-center">
                    <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ number_format($statistics['bolos'] ?? 0) }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Bolos</p>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:shadow-md transition-all">
                <div class="text-center">
                    <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ number_format($statistics['pulang_cepat'] ?? 0) }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Pulang Cepat</p>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-4 hover:shadow-md transition-all">
                <div class="text-center">
                    <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ number_format($statistics['lupa_checkout'] ?? 0) }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Lupa Checkout</p>
                </div>
            </div>
        </div>

        <!-- Status Distribution Chart -->
        @if(isset($chartData['statusDistribution']))
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Distribusi Status Kehadiran</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
                    @foreach($chartData['statusDistribution']['labels'] as $index => $label)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-900">{{ number_format($chartData['statusDistribution']['data'][$index]) }}</div>
                            <div class="text-xs font-semibold text-slate-600 mt-1">{{ $label }}</div>
                            <div class="mt-2 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full transition-all duration-300"
                                     style="width: {{ ($statistics['total_records'] ?? 0) > 0 ? round(($chartData['statusDistribution']['data'][$index] / $statistics['total_records']) * 100, 1) : 0 }}%; background-color: {{ $chartData['statusDistribution']['colors'][$index] ?? '#6b7280' }}"></div>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">
                                {{ ($statistics['total_records'] ?? 0) > 0 ? round(($chartData['statusDistribution']['data'][$index] / $statistics['total_records']) * 100, 1) : 0 }}%
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <!-- Trends Report -->
    @if($reportType === 'trends' && isset($chartData['trends']))
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-6">Tren Kehadiran Harian</h3>

            <div class="overflow-x-auto">
                <div class="min-w-full">
                    <!-- Simple Line Chart Visualization -->
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($chartData['trends']['datasets'] as $dataset)
                            <div class="border-b border-slate-200 pb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold text-slate-700">{{ $dataset['label'] }}</span>
                                    <span class="text-xs text-slate-500">Total: {{ array_sum($dataset['data']) }}</span>
                                </div>
                                <div class="flex items-end gap-1 h-32">
                                    @foreach($dataset['data'] as $value)
                                        @php
                                            $maxValue = max($dataset['data']) ?: 1;
                                            $height = ($value / $maxValue) * 100;
                                        @endphp
                                        <div class="flex-1 bg-blue-600 rounded-t hover:bg-blue-700 transition-all cursor-pointer"
                                             style="height: {{ $height }}%"
                                             title="{{ $value }}">
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex gap-1 mt-1">
                                    @foreach($chartData['trends']['labels'] as $label)
                                        <div class="flex-1 text-xs text-slate-500 text-center">{{ $label }}</div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Comparison Report -->
    @if($reportType === 'comparison' && isset($chartData['comparison']))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Department Comparison -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Perbandingan Antar Jurusan</h3>
                <div class="space-y-4">
                    @foreach($chartData['comparison']['labels'] as $index => $label)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-semibold text-slate-700">{{ $label }}</span>
                                <span class="text-slate-600">
                                    Hadir: {{ $chartData['comparison']['datasets'][0]['data'][$index] }} |
                                    Terlambat: {{ $chartData['comparison']['datasets'][1]['data'][$index] }}
                                </span>
                            </div>
                            <div class="flex gap-1 h-8">
                                @php
                                    $total = $chartData['comparison']['datasets'][0]['data'][$index] + $chartData['comparison']['datasets'][1]['data'][$index];
                                    $hadirPercent = $total > 0 ? ($chartData['comparison']['datasets'][0]['data'][$index] / $total) * 100 : 0;
                                    $terlambatPercent = $total > 0 ? ($chartData['comparison']['datasets'][1]['data'][$index] / $total) * 100 : 0;
                                @endphp
                                <div class="bg-blue-600 rounded" style="width: {{ $hadirPercent }}%" title="Hadir: {{ $hadirPercent }}%"></div>
                                <div class="bg-slate-500 rounded" style="width: {{ $terlambatPercent }}%" title="Terlambat: {{ $terlambatPercent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Attendance Rates -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Tingkat Kehadiran per Jurusan</h3>
                <div class="space-y-4">
                    @foreach($chartData['attendanceRates']['labels'] as $index => $label)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-semibold text-slate-700">{{ $label }}</span>
                                <span class="font-bold text-slate-900">{{ $chartData['attendanceRates']['data'][$index] }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-3">
                                <div class="h-full rounded-full {{ $chartData['attendanceRates']['data'][$index] >= 90 ? 'bg-blue-600' : 'bg-slate-500' }}"
                                     style="width: {{ $chartData['attendanceRates']['data'][$index] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Detailed Report -->
    @if($reportType === 'detailed' && isset($statistics['top_students']))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Top Punctual Students -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Top 10 Siswa Paling Rajin</h3>
                <div class="space-y-3">
                    @foreach($statistics['top_students'] as $index => $student)
                        <div class="flex items-center gap-3 p-3 {{ $index === 0 ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-slate-200' }} border rounded-xl hover:shadow-sm transition-all">
                            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl {{ $index === 0 ? 'bg-blue-600' : 'bg-slate-600' }} text-white font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-slate-900">{{ $student->full_name }}</div>
                                <div class="text-xs text-slate-500">{{ $student->class->name ?? '-' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold {{ $index === 0 ? 'text-blue-600' : 'text-slate-700' }}">{{ $student->hadir_count }}</div>
                                <div class="text-xs text-slate-500">hari hadir</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Most Late Students -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Top 10 Siswa Sering Terlambat</h3>
                <div class="space-y-3">
                    @foreach($statistics['late_students'] as $index => $student)
                        <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl hover:shadow-sm transition-all">
                            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-slate-600 text-white font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-slate-900">{{ $student->full_name }}</div>
                                <div class="text-xs text-slate-500">{{ $student->class->name ?? '-' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-slate-700">{{ $student->late_count }}</div>
                                <div class="text-xs text-slate-500">kali terlambat</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Perfect Attendance Students -->
            @if(isset($statistics['perfect_students']) && count($statistics['perfect_students']) > 0)
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Siswa dengan Kehadiran Sempurna</h3>
                    <div class="space-y-3">
                        @foreach($statistics['perfect_students'] as $index => $student)
                            <div class="flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-xl hover:shadow-sm transition-all">
                                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-green-600 text-white font-bold text-sm">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900">{{ $student->full_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $student->class->name ?? '-' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-green-600">100%</div>
                                    <div class="text-xs text-slate-500">{{ $student->total_attendance }} hari</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Most Absent Students -->
            @if(isset($statistics['absent_students']) && count($statistics['absent_students']) > 0)
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Siswa Sering Tidak Hadir</h3>
                    <div class="space-y-3">
                        @foreach($statistics['absent_students'] as $index => $student)
                            <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl hover:shadow-sm transition-all">
                                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-slate-600 text-white font-bold text-sm">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900">{{ $student->full_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $student->class->name ?? '-' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-slate-700">{{ $student->absent_count }}</div>
                                    <div class="text-xs text-slate-500">kali alpha/bolos</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Time Analysis Report -->
    @if($reportType === 'timeanalysis' && isset($chartData['checkInPattern']))
        <div class="space-y-6">
            <!-- Check-in Pattern -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Pola Waktu Check-in</h3>
                <div class="overflow-x-auto">
                    <div class="flex items-end gap-2 h-64">
                        @php
                            $maxCheckIn = !empty($chartData['checkInPattern']['data']) ? max($chartData['checkInPattern']['data']) : 1;
                        @endphp
                        @foreach($chartData['checkInPattern']['labels'] as $index => $label)
                            @php
                                $value = $chartData['checkInPattern']['data'][$index] ?? 0;
                                $height = $maxCheckIn > 0 ? ($value / $maxCheckIn) * 100 : 0;
                            @endphp
                            <div class="flex-1 flex flex-col items-center justify-end">
                                <span class="text-xs font-medium text-slate-700 mb-1">{{ $value }}</span>
                                <div class="w-full bg-blue-600 rounded-t hover:bg-blue-700 transition-all cursor-pointer"
                                     style="height: {{ $height }}%"
                                     title="{{ $label }}: {{ $value }} siswa">
                                </div>
                                <span class="text-xs text-slate-500 mt-1">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Late Arrival Pattern -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Pola Keterlambatan per Jam</h3>
                    <div class="space-y-3">
                        @if(!empty($chartData['lateArrivalPattern']['labels']))
                            @foreach($chartData['lateArrivalPattern']['labels'] as $index => $label)
                                @php
                                    $value = $chartData['lateArrivalPattern']['data'][$index] ?? 0;
                                    $maxLate = !empty($chartData['lateArrivalPattern']['data']) ? max($chartData['lateArrivalPattern']['data']) : 1;
                                    $percentage = $maxLate > 0 ? ($value / $maxLate) * 100 : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-semibold text-slate-700">{{ $label }}</span>
                                        <span class="text-slate-600">{{ $value }} siswa</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-3">
                                        <div class="h-full rounded-full bg-yellow-500" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-slate-500 text-center py-4">Tidak ada data keterlambatan</p>
                        @endif
                    </div>
                </div>

                <!-- Late Duration Distribution -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Distribusi Durasi Keterlambatan</h3>
                    <div class="space-y-4">
                        @foreach($chartData['lateDurations'] as $duration => $count)
                            @php
                                $total = array_sum($chartData['lateDurations']);
                                $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                            @endphp
                            <div class="flex items-center gap-3">
                                <div class="w-16 text-right">
                                    <span class="text-xl font-bold text-slate-900">{{ $count }}</span>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-slate-700">{{ $duration }}</div>
                                    <div class="mt-1 w-full bg-slate-100 rounded-full h-2">
                                        <div class="h-full rounded-full bg-orange-500" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                                <div class="w-12 text-right">
                                    <span class="text-xs font-medium text-slate-600">{{ $percentage }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Day of Week Analysis -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Analisis per Hari dalam Seminggu</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    @foreach($chartData['dayOfWeek'] as $day => $data)
                        @php
                            $total = $data['hadir'] + $data['terlambat'] + $data['absent'];
                            $hadirPercent = $total > 0 ? round(($data['hadir'] / $total) * 100, 1) : 0;
                            $terlambatPercent = $total > 0 ? round(($data['terlambat'] / $total) * 100, 1) : 0;
                            $absentPercent = $total > 0 ? round(($data['absent'] / $total) * 100, 1) : 0;
                        @endphp
                        <div class="border border-slate-200 rounded-xl p-4 hover:shadow-md transition-all bg-slate-50">
                            <div class="text-center">
                                <div class="font-bold text-slate-900 text-base mb-2">{{ $day }}</div>
                                <div class="text-2xl font-bold text-slate-700 mb-3">{{ $total }}</div>

                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-slate-600">Hadir:</span>
                                        <span class="font-semibold text-green-600">{{ $data['hadir'] }} ({{ $hadirPercent }}%)</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-slate-600">Terlambat:</span>
                                        <span class="font-semibold text-yellow-600">{{ $data['terlambat'] }} ({{ $terlambatPercent }}%)</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-slate-600">Tidak Hadir:</span>
                                        <span class="font-semibold text-slate-600">{{ $data['absent'] }} ({{ $absentPercent }}%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Insights & Recommendations Report -->
    @if($reportType === 'insights' && !empty($insights))
        <div class="space-y-6">
            <!-- Attendance Trend Card -->
            @if(isset($insights['trend']))
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-slate-900 mb-2 flex items-center gap-2">
                                @if($insights['trend']['direction'] === 'up')
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    <span class="text-green-600">Tren Kehadiran Meningkat</span>
                                @elseif($insights['trend']['direction'] === 'down')
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                    </svg>
                                    <span class="text-red-600">Tren Kehadiran Menurun</span>
                                @else
                                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                                    </svg>
                                    <span class="text-slate-600">Tren Kehadiran Stabil</span>
                                @endif
                            </h3>
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                                    <div class="text-xs font-medium text-slate-500 mb-1">Paruh Pertama</div>
                                    <div class="text-2xl font-bold text-slate-900">{{ $insights['trend']['first_half'] }}%</div>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                                    <div class="text-xs font-medium text-slate-500 mb-1">Paruh Kedua</div>
                                    <div class="text-2xl font-bold text-slate-900">{{ $insights['trend']['second_half'] }}%</div>
                                </div>
                            </div>
                        </div>
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-100 rounded-full flex items-center justify-center ml-4 flex-shrink-0">
                            <div class="text-3xl sm:text-4xl font-bold text-slate-600">
                                {{ $insights['trend']['direction'] === 'up' ? '↗' : ($insights['trend']['direction'] === 'down' ? '↘' : '→') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Worst Days -->
                @if(isset($insights['worst_days']) && count($insights['worst_days']) > 0)
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Hari-hari Terburuk</h3>
                        <div class="space-y-3">
                            @foreach($insights['worst_days'] as $index => $day)
                                <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl hover:shadow-sm transition-all">
                                    <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-slate-600 text-white font-bold text-sm">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold text-slate-900">{{ \Carbon\Carbon::parse($day['date'])->format('d F Y') }}</div>
                                        <div class="text-xs text-slate-500">{{ $day['day_name'] }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-slate-900">{{ $day['problem_count'] }}</div>
                                        <div class="text-xs text-slate-500">masalah</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Peak Late Hours -->
                @if(isset($insights['peak_late_hours']) && count($insights['peak_late_hours']) > 0)
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Jam Puncak Keterlambatan</h3>
                        <div class="space-y-4">
                            @foreach($insights['peak_late_hours'] as $hour => $count)
                                @php
                                    $maxPeak = max($insights['peak_late_hours']);
                                    $percentage = $maxPeak > 0 ? ($count / $maxPeak) * 100 : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between text-sm mb-2">
                                        <span class="font-bold text-slate-900">{{ $hour }}</span>
                                        <span class="text-slate-600 font-semibold">{{ $count }} siswa terlambat</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-3">
                                        <div class="h-full rounded-full bg-yellow-500" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Classes Need Attention -->
            @if(isset($insights['attention_classes']) && count($insights['attention_classes']) > 0)
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Kelas yang Membutuhkan Perhatian</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($insights['attention_classes'] as $index => $class)
                            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50 hover:shadow-md transition-all">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <div class="font-bold text-slate-900 text-lg">{{ $class['class_name'] }}</div>
                                        <div class="text-xs text-slate-500 mt-1">Peringkat #{{ $index + 1 }} terendah</div>
                                    </div>
                                    <div class="w-10 h-10 bg-slate-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600">Tingkat Kehadiran:</span>
                                        <span class="font-bold text-slate-900">{{ $class['rate'] }}%</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600">Kasus Bermasalah:</span>
                                        <span class="font-bold text-slate-900">{{ $class['problem_count'] }}</span>
                                    </div>
                                </div>
                                <div class="mt-3 w-full bg-slate-200 rounded-full h-2">
                                    <div class="h-full rounded-full {{ $class['rate'] >= 75 ? 'bg-blue-600' : 'bg-slate-600' }}" style="width: {{ $class['rate'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
