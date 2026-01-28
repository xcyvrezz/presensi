<div>
    @if(!$class)
        <!-- No Class Assigned -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-yellow-800">Anda Belum Ditugaskan Sebagai Wali Kelas</h3>
                    <p class="mt-2 text-sm text-yellow-700">
                        Silakan hubungi administrator untuk ditugaskan sebagai wali kelas.
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Rekap Absensi Kelas</h1>
                    <p class="text-slate-600 mt-1">{{ $class->name }} - {{ $class->department->name }}</p>
                </div>
                <div class="flex gap-3">
                    <button wire:click="{{ $exportMode === 'monthly' ? 'exportMonthlyExcel' : ($exportMode === 'semester' ? 'exportSemesterExcel' : 'exportCustomExcel') }}"
                            class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all duration-300 shadow-sm hover:shadow-md font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span wire:loading.remove wire:target="exportMonthlyExcel,exportSemesterExcel,exportCustomExcel">Export Excel</span>
                        <span wire:loading wire:target="exportMonthlyExcel,exportSemesterExcel,exportCustomExcel">Exporting...</span>
                    </button>
                    <button wire:click="{{ $exportMode === 'monthly' ? 'exportMonthlyPdf' : ($exportMode === 'semester' ? 'exportSemesterPdf' : 'exportCustomPdf') }}"
                            class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all duration-300 shadow-sm hover:shadow-md font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span wire:loading.remove wire:target="exportMonthlyPdf,exportSemesterPdf,exportCustomPdf">Export PDF</span>
                        <span wire:loading wire:target="exportMonthlyPdf,exportSemesterPdf,exportCustomPdf">Exporting...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Unified Export Rekap Card -->
        <div class="mb-6 bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Export Rekap Absensi</h3>
                        <p class="text-sm text-slate-600">Pilih periode, lalu gunakan tombol Export di kanan atas</p>
                    </div>
                </div>

                <!-- Mode Toggle -->
                <div class="inline-flex items-center bg-slate-100 rounded-xl p-1">
                    <button wire:click="$set('exportMode', 'monthly')"
                            class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $exportMode === 'monthly' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Bulanan
                    </button>
                    <button wire:click="$set('exportMode', 'semester')"
                            class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $exportMode === 'semester' ? 'bg-white text-purple-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Semester
                    </button>
                    <button wire:click="$set('exportMode', 'custom')"
                            class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $exportMode === 'custom' ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Kustom
                    </button>
                </div>
            </div>

            @if (session()->has('error'))
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-600 rounded-lg">
                    <p class="text-sm font-medium text-red-900">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Export Form -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($exportMode === 'monthly')
                    <!-- Bulan -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Bulan</label>
                        <select wire:model="exportMonth"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="01">Januari</option>
                            <option value="02">Februari</option>
                            <option value="03">Maret</option>
                            <option value="04">April</option>
                            <option value="05">Mei</option>
                            <option value="06">Juni</option>
                            <option value="07">Juli</option>
                            <option value="08">Agustus</option>
                            <option value="09">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                    </div>

                    <!-- Tahun -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tahun</label>
                        <select wire:model="exportYear"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            @for($year = date('Y'); $year >= 2020; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                @elseif($exportMode === 'semester')
                    <!-- Semester -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <span class="flex items-center gap-1">
                                Semester
                                <span class="text-red-500">*</span>
                            </span>
                        </label>
                        <select wire:model="exportSemester"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                            <option value="">-- Pilih Semester --</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}">
                                    {{ $sem->name }} ({{ $sem->academic_year }})
                                    @if($sem->is_active) ★ @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <!-- Custom Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <span class="flex items-center gap-1">
                                Tanggal Mulai
                                <span class="text-red-500">*</span>
                            </span>
                        </label>
                        <input type="date" wire:model="exportStartDate"
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <span class="flex items-center gap-1">
                                Tanggal Akhir
                                <span class="text-red-500">*</span>
                            </span>
                        </label>
                        <input type="date" wire:model="exportEndDate"
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                    </div>
                @endif
            </div>

            <!-- Info Box -->
            <div class="mt-4 p-4 {{ $exportMode === 'monthly' ? 'bg-blue-50 border-blue-200' : ($exportMode === 'semester' ? 'bg-purple-50 border-purple-200' : 'bg-amber-50 border-amber-200') }} border rounded-xl">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 {{ $exportMode === 'monthly' ? 'text-blue-600' : ($exportMode === 'semester' ? 'text-purple-600' : 'text-amber-600') }} flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        @if($exportMode === 'monthly')
                            <p class="text-sm text-blue-900 font-semibold mb-1">
                                Cara Export Rekap Bulanan:
                            </p>
                            <ol class="text-xs text-blue-800 space-y-1 list-decimal list-inside">
                                <li>Pilih bulan dan tahun</li>
                                <li>Klik tombol <strong>Export Excel</strong> atau <strong>Export PDF</strong> di kanan atas halaman</li>
                                <li>Rekap akan menghitung hari efektif dengan mengecualikan hari libur</li>
                            </ol>
                        @elseif($exportMode === 'semester')
                            <p class="text-sm text-purple-900 font-semibold mb-1">
                                Cara Export Rekap Semester:
                            </p>
                            <ol class="text-xs text-purple-800 space-y-1 list-decimal list-inside">
                                <li>Pilih semester</li>
                                <li>Klik tombol <strong>Export Excel</strong> atau <strong>Export PDF</strong> di kanan atas halaman</li>
                                <li>Rekap menghitung hari efektif sekolah (exclude Sabtu, Minggu, dan hari libur)</li>
                                <li>Persentase kehadiran = (Total Hadir / Hari Efektif) × 100%</li>
                            </ol>
                        @else
                            <p class="text-sm text-amber-900 font-semibold mb-1">
                                Cara Export Rekap Periode Kustom:
                            </p>
                            <ol class="text-xs text-amber-800 space-y-1 list-decimal list-inside">
                                <li>Pilih tanggal mulai dan tanggal akhir (maksimal 1 tahun)</li>
                                <li>Klik tombol <strong>Export Excel</strong> atau <strong>Export PDF</strong> di kanan atas halaman</li>
                                <li>Rekap menghitung hari efektif dalam periode (exclude Sabtu, Minggu, dan hari libur)</li>
                                <li>Persentase kehadiran = (Total Hadir / Hari Efektif) × 100%</li>
                            </ol>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-9 gap-4 mb-8">
            <!-- Hadir -->
            <div class="bg-white border border-green-200 rounded-lg p-4 hover:border-green-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Hadir</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalPresent }}</p>
                </div>
            </div>

            <!-- Terlambat -->
            <div class="bg-white border border-yellow-200 rounded-lg p-4 hover:border-yellow-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Terlambat</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalLate }}</p>
                </div>
            </div>

            <!-- Izin -->
            <div class="bg-white border border-blue-200 rounded-lg p-4 hover:border-blue-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Izin</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalPermit }}</p>
                </div>
            </div>

            <!-- Sakit -->
            <div class="bg-white border border-purple-200 rounded-lg p-4 hover:border-purple-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Sakit</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalSick }}</p>
                </div>
            </div>

            <!-- Dispensasi -->
            <div class="bg-white border border-cyan-200 rounded-lg p-4 hover:border-cyan-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="h-6 w-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Dispensasi</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalDispensasi }}</p>
                </div>
            </div>

            <!-- Alpha -->
            <div class="bg-white border border-slate-200 rounded-lg p-4 hover:border-slate-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="h-6 w-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Alpha</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalAbsent }}</p>
                </div>
            </div>

            <!-- Bolos -->
            <div class="bg-white border border-red-200 rounded-lg p-4 hover:border-red-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Bolos</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalBolos }}</p>
                </div>
            </div>

            <!-- Pulang Cepat -->
            <div class="bg-white border border-orange-200 rounded-lg p-4 hover:border-orange-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Pulang Cepat</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalPulangCepat }}</p>
                </div>
            </div>

            <!-- Lupa Checkout -->
            <div class="bg-white border border-amber-200 rounded-lg p-4 hover:border-amber-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Lupa Checkout</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalLupaCheckout }}</p>
                </div>
            </div>
        </div>

        <!-- Filters & Export -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Filter & Export Data Absensi</h3>
                    <p class="text-sm text-slate-600">Filter data dan export rekap bulanan</p>
                </div>
            </div>

            @if (session()->has('error'))
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-600 rounded-lg">
                    <p class="text-sm font-medium text-red-900">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Filter Section -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-slate-700 mb-3 uppercase tracking-wider">Filter Tampilan Data</h4>
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

                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Cari Siswa</label>
                        <input type="text" wire:model.live.debounce.300ms="search"
                               placeholder="Nama atau NIS..."
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
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
                                    @if($attendance->check_in_method === 'nfc' || $attendance->check_in_method === 'nfc_mobile')
                                        <span class="text-blue-600">📱 NFC</span>
                                    @elseif($attendance->check_in_method === 'rfid')
                                        <span class="text-blue-600">💳 RFID</span>
                                    @elseif($attendance->check_in_method === 'manual')
                                        <span class="text-slate-600">✍️ Manual</span>
                                    @else
                                        <span class="text-slate-600">{{ $attendance->check_in_method ?? '-' }}</span>
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
                                <td colspan="8" class="px-6 py-12 text-center text-slate-500">
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
    @endif
</div>
