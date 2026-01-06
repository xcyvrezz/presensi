<div>
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Statistik Absensi</h1>
                <p class="text-gray-600 mt-1">{{ $student->full_name }} - {{ $student->class->name }}</p>
            </div>

            <!-- Period Filter Dropdown -->
            <div class="relative inline-block" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 px-4 py-2.5 bg-white border-2 border-gray-200 rounded-lg hover:bg-gray-50 text-sm font-medium text-gray-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>
                        @if($selectedPeriod === 'semester')
                            Semester Aktif
                        @elseif($selectedPeriod === 'year')
                            Tahun Ini
                        @else
                            Semua Data
                        @endif
                    </span>
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
                     class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                    <div class="py-1">
                        <button wire:click="$set('selectedPeriod', 'semester')" @click="open = false"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 {{ $selectedPeriod === 'semester' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700' }}">
                            <div class="flex items-center justify-between">
                                <span>Semester Aktif</span>
                                @if($selectedPeriod === 'semester')
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                        </button>
                        <button wire:click="$set('selectedPeriod', 'year')" @click="open = false"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 {{ $selectedPeriod === 'year' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700' }}">
                            <div class="flex items-center justify-between">
                                <span>Tahun Ini ({{ now()->year }})</span>
                                @if($selectedPeriod === 'year')
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                        </button>
                        <button wire:click="$set('selectedPeriod', 'all')" @click="open = false"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 {{ $selectedPeriod === 'all' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700' }}">
                            <div class="flex items-center justify-between">
                                <span>Semua Data</span>
                                @if($selectedPeriod === 'all')
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
    </div>

    <!-- Overall Percentage Card -->
    <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-700 rounded-2xl p-8 mb-6 shadow-2xl">
        <div class="flex items-center justify-between">
            <div class="text-white">
                <p class="text-sm font-medium opacity-90 uppercase tracking-wide">Tingkat Kehadiran</p>
                <h2 class="text-5xl font-bold mt-2">{{ $overallStats['percentage'] }}%</h2>
                <p class="mt-3 text-blue-100">{{ $overallStats['presentDays'] }} dari {{ $overallStats['totalWorkingDays'] }} hari efektif</p>
                @if($overallStats['startDate'] && $overallStats['endDate'])
                    <p class="mt-1 text-xs text-blue-200 opacity-75">
                        Periode: {{ \Carbon\Carbon::parse($overallStats['startDate'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($overallStats['endDate'])->format('d M Y') }}
                    </p>
                @endif
            </div>
            <div class="relative">
                <!-- Circular Progress -->
                <svg class="transform -rotate-90" width="140" height="140">
                    <circle cx="70" cy="70" r="60" stroke="rgba(255,255,255,0.2)" stroke-width="12" fill="none"/>
                    <circle cx="70" cy="70" r="60"
                            stroke="white"
                            stroke-width="12"
                            fill="none"
                            stroke-dasharray="{{ 2 * 3.14159 * 60 }}"
                            stroke-dashoffset="{{ 2 * 3.14159 * 60 * (1 - $overallStats['percentage'] / 100) }}"
                            stroke-linecap="round"
                            style="transition: stroke-dashoffset 1s ease;"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    @if($overallStats['percentage'] >= 80)
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($overallStats['percentage'] >= 60)
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    @else
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="mt-4 pt-4 border-t border-white/20">
            @if($overallStats['percentage'] >= 80)
                <div class="flex items-center gap-2 text-green-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-semibold">Kehadiran Sangat Baik! Pertahankan terus.</span>
                </div>
            @elseif($overallStats['percentage'] >= 60)
                <div class="flex items-center gap-2 text-yellow-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-semibold">Kehadiran Cukup Baik. Tingkatkan lagi!</span>
                </div>
            @else
                <div class="flex items-center gap-2 text-red-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-semibold">Perlu Ditingkatkan! Tingkatkan kehadiran segera.</span>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Status Distribution (Donut Chart) -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Distribusi Status</h3>

            @if($overallStats['total'] > 0)
                <div class="flex items-center justify-center mb-6">
                    <!-- CSS Donut Chart -->
                    <div class="relative w-48 h-48">
                        @php
                            $offset = 0;
                            $colors = ['#10b981', '#f59e0b', '#3b82f6', '#a855f7', '#ef4444', '#6b7280'];
                        @endphp

                        <svg width="192" height="192" viewBox="0 0 192 192">
                            @foreach($statusDistribution as $index => $data)
                                @if($data['count'] > 0)
                                    @php
                                        $circumference = 2 * 3.14159 * 70;
                                        $strokeLength = ($data['percentage'] / 100) * $circumference;
                                        $strokeOffset = -$offset;
                                        $offset += $strokeLength;
                                    @endphp
                                    <circle
                                        cx="96"
                                        cy="96"
                                        r="70"
                                        fill="none"
                                        stroke="{{ $data['color'] }}"
                                        stroke-width="40"
                                        stroke-dasharray="{{ $strokeLength }} {{ $circumference }}"
                                        stroke-dashoffset="{{ $strokeOffset }}"
                                        transform="rotate(-90 96 96)"
                                    />
                                @endif
                            @endforeach
                            <!-- Center white circle -->
                            <circle cx="96" cy="96" r="50" fill="white"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center flex-col">
                            <p class="text-3xl font-bold text-gray-800">{{ $overallStats['total'] }}</p>
                            <p class="text-xs text-gray-500 uppercase">Total</p>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="grid grid-cols-2 gap-3">
                    @foreach($statusDistribution as $data)
                        @if($data['count'] > 0)
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $data['color'] }}"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-700 truncate">{{ $data['status'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $data['count'] }} ({{ $data['percentage'] }}%)</p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Tidak ada data</p>
                </div>
            @endif
        </div>

        <!-- Status Progress Bars -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Rincian Status</h3>

            <div class="space-y-4">
                <!-- Hadir -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700">Hadir</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $overallStats['hadir'] }}</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-green-400 to-green-600 rounded-full transition-all duration-1000"
                             style="width: {{ $overallStats['total'] > 0 ? ($overallStats['hadir'] / $overallStats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <!-- Terlambat -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700">Terlambat</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $overallStats['terlambat'] }}</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full transition-all duration-1000"
                             style="width: {{ $overallStats['total'] > 0 ? ($overallStats['terlambat'] / $overallStats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <!-- Izin -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700">Izin</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $overallStats['izin'] }}</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-400 to-blue-600 rounded-full transition-all duration-1000"
                             style="width: {{ $overallStats['total'] > 0 ? ($overallStats['izin'] / $overallStats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <!-- Sakit -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700">Sakit</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $overallStats['sakit'] }}</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-purple-400 to-purple-600 rounded-full transition-all duration-1000"
                             style="width: {{ $overallStats['total'] > 0 ? ($overallStats['sakit'] / $overallStats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <!-- Alpha -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-slate-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700">Alpha</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $overallStats['alpha'] }}</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-slate-400 to-slate-600 rounded-full transition-all duration-1000"
                             style="width: {{ $overallStats['total'] > 0 ? ($overallStats['alpha'] / $overallStats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <!-- Dispensasi -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-cyan-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700">Dispensasi</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $overallStats['dispensasi'] }}</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-cyan-400 to-cyan-600 rounded-full transition-all duration-1000"
                             style="width: {{ $overallStats['total'] > 0 ? ($overallStats['dispensasi'] / $overallStats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <!-- Bolos -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-red-600 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700">Bolos</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $overallStats['bolos'] }}</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-red-500 to-red-700 rounded-full transition-all duration-1000"
                             style="width: {{ $overallStats['total'] > 0 ? ($overallStats['bolos'] / $overallStats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <!-- Pulang Cepat -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700">Pulang Cepat</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $overallStats['pulang_cepat'] }}</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-orange-400 to-orange-600 rounded-full transition-all duration-1000"
                             style="width: {{ $overallStats['total'] > 0 ? ($overallStats['pulang_cepat'] / $overallStats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <!-- Lupa Checkout -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-amber-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-700">Lupa Checkout</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $overallStats['lupa_checkout'] }}</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-amber-400 to-amber-600 rounded-full transition-all duration-1000"
                             style="width: {{ $overallStats['total'] > 0 ? ($overallStats['lupa_checkout'] / $overallStats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trend Bar Chart -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-6">Tren Kehadiran Bulanan ({{ now()->year }})</h3>

        @php
            $maxTotal = max(array_column($monthlyData, 'total'));
            $maxTotal = $maxTotal > 0 ? $maxTotal : 1;
        @endphp

        <div class="space-y-3">
            @foreach($monthlyData as $data)
                <div class="flex items-center gap-3">
                    <div class="w-12 text-xs font-medium text-gray-600">{{ $data['month'] }}</div>
                    <div class="flex-1">
                        <div class="flex items-center gap-1 h-8">
                            <!-- Hadir -->
                            @if($data['hadir'] > 0)
                                <div class="h-full bg-green-500 rounded-l hover:bg-green-600 transition-all cursor-pointer group relative"
                                     style="width: {{ ($data['hadir'] / $maxTotal) * 100 }}%"
                                     title="Hadir: {{ $data['hadir'] }}">
                                    <span class="absolute inset-0 flex items-center justify-center text-xs text-white font-bold opacity-0 group-hover:opacity-100">{{ $data['hadir'] }}</span>
                                </div>
                            @endif
                            <!-- Terlambat -->
                            @if($data['terlambat'] > 0)
                                <div class="h-full bg-yellow-500 {{ $data['hadir'] == 0 ? 'rounded-l' : '' }} hover:bg-yellow-600 transition-all cursor-pointer group relative"
                                     style="width: {{ ($data['terlambat'] / $maxTotal) * 100 }}%"
                                     title="Terlambat: {{ $data['terlambat'] }}">
                                    <span class="absolute inset-0 flex items-center justify-center text-xs text-white font-bold opacity-0 group-hover:opacity-100">{{ $data['terlambat'] }}</span>
                                </div>
                            @endif
                            <!-- Alpha -->
                            @if($data['alpha'] > 0)
                                <div class="h-full bg-red-500 rounded-r hover:bg-red-600 transition-all cursor-pointer group relative"
                                     style="width: {{ ($data['alpha'] / $maxTotal) * 100 }}%"
                                     title="Alpha: {{ $data['alpha'] }}">
                                    <span class="absolute inset-0 flex items-center justify-center text-xs text-white font-bold opacity-0 group-hover:opacity-100">{{ $data['alpha'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="w-12 text-right text-sm font-bold text-gray-700">{{ $data['total'] }}</div>
                </div>
            @endforeach
        </div>

        <!-- Legend -->
        <div class="flex items-center justify-center gap-6 mt-6 pt-6 border-t border-gray-200">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-500 rounded"></div>
                <span class="text-xs font-medium text-gray-600">Hadir</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                <span class="text-xs font-medium text-gray-600">Terlambat</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-500 rounded"></div>
                <span class="text-xs font-medium text-gray-600">Alpha</span>
            </div>
        </div>
    </div>
</div>
