<div>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Riwayat Absensi</h1>
        <p class="text-slate-600 mt-1">{{ $student->full_name }} - {{ $student->class->name }}</p>
    </div>

    <!-- Monthly Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-10 gap-3 mb-6">
        <!-- Total -->
        <div class="bg-white rounded-lg p-4 border-2 border-slate-200 hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Total</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $monthlyStats['total'] }}</p>
                </div>
                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Hadir -->
        <div class="bg-white rounded-lg p-4 border-2 border-green-200 hover:border-green-300 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Hadir</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">{{ $monthlyStats['hadir'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="bg-white rounded-lg p-4 border-2 border-amber-200 hover:border-amber-300 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Terlambat</p>
                    <p class="text-2xl font-bold text-amber-700 mt-1">{{ $monthlyStats['terlambat'] }}</p>
                </div>
                <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Izin -->
        <div class="bg-white rounded-lg p-4 border-2 border-blue-200 hover:border-blue-300 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Izin</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1">{{ $monthlyStats['izin'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Sakit -->
        <div class="bg-white rounded-lg p-4 border-2 border-purple-200 hover:border-purple-300 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Sakit</p>
                    <p class="text-2xl font-bold text-purple-700 mt-1">{{ $monthlyStats['sakit'] }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Alpha -->
        <div class="bg-white rounded-lg p-4 border-2 border-slate-200 hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Alpha</p>
                    <p class="text-2xl font-bold text-slate-700 mt-1">{{ $monthlyStats['alpha'] }}</p>
                </div>
                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Dispensasi -->
        <div class="bg-white rounded-lg p-4 border-2 border-cyan-200 hover:border-cyan-300 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Dispensasi</p>
                    <p class="text-2xl font-bold text-cyan-700 mt-1">{{ $monthlyStats['dispensasi'] }}</p>
                </div>
                <div class="w-10 h-10 bg-cyan-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Bolos -->
        <div class="bg-white rounded-lg p-4 border-2 border-red-200 hover:border-red-300 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Bolos</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ $monthlyStats['bolos'] }}</p>
                </div>
                <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pulang Cepat -->
        <div class="bg-white rounded-lg p-4 border-2 border-orange-200 hover:border-orange-300 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Pulang Cepat</p>
                    <p class="text-2xl font-bold text-orange-700 mt-1">{{ $monthlyStats['pulang_cepat'] }}</p>
                </div>
                <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Lupa Checkout -->
        <div class="bg-white rounded-lg p-4 border-2 border-slate-200 hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Lupa Checkout</p>
                    <p class="text-2xl font-bold text-slate-700 mt-1">{{ $monthlyStats['lupa_checkout'] }}</p>
                </div>
                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6 border border-slate-200">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Month Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Bulan</label>
                <select wire:model.live="selectedMonth" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                    <option value="4">April</option>
                    <option value="5">Mei</option>
                    <option value="6">Juni</option>
                    <option value="7">Juli</option>
                    <option value="8">Agustus</option>
                    <option value="9">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
            </div>

            <!-- Year Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Tahun</label>
                <select wire:model.live="selectedYear" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
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
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Hari</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Check In</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Check Out</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">
                                    {{ $attendance->check_in_time ? $attendance->check_in_time->format('d/m/Y') : \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-600">
                                    {{ $attendance->check_in_time ? $attendance->check_in_time->isoFormat('dddd') : \Carbon\Carbon::parse($attendance->date)->isoFormat('dddd') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($attendance->check_in_time)
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-slate-900">{{ $attendance->check_in_time->format('H:i') }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($attendance->check_out_time)
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-slate-900">{{ $attendance->check_out_time->format('H:i') }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-md
                                    @if($attendance->status === 'hadir') bg-green-50 text-green-700 border border-green-200
                                    @elseif($attendance->status === 'terlambat') bg-amber-50 text-amber-700 border border-amber-200
                                    @elseif($attendance->status === 'izin') bg-blue-50 text-blue-700 border border-blue-200
                                    @elseif($attendance->status === 'sakit') bg-purple-50 text-purple-700 border border-purple-200
                                    @elseif($attendance->status === 'dispensasi') bg-cyan-50 text-cyan-700 border border-cyan-200
                                    @elseif($attendance->status === 'alpha') bg-slate-50 text-slate-700 border border-slate-200
                                    @elseif($attendance->status === 'bolos') bg-red-50 text-red-700 border border-red-200
                                    @elseif($attendance->status === 'pulang_cepat') bg-orange-50 text-orange-700 border border-orange-200
                                    @else bg-slate-50 text-slate-700 border border-slate-200
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-md
                                    {{ $attendance->method === 'nfc' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-50 text-slate-700 border border-slate-200' }}">
                                    {{ $attendance->method === 'nfc' ? 'NFC' : 'Manual' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($attendance->notes)
                                    <p class="text-xs text-slate-600 max-w-xs truncate" title="{{ $attendance->notes }}">{{ $attendance->notes }}</p>
                                @else
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-slate-500 text-sm font-medium">Tidak ada data absensi</p>
                                    <p class="text-slate-400 text-xs mt-1">untuk bulan dan tahun yang dipilih</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($attendances->hasPages())
            <div class="bg-slate-50 px-4 py-3 border-t border-slate-200">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>