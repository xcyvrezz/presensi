<div>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Activity Logs & Audit Trail</h1>
        <p class="text-gray-600 mt-1">Monitoring dan pelacakan semua aktivitas sistem</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="text-sm font-semibold text-gray-600">Total Aktivitas</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_logs']) }}</div>
            <div class="text-xs text-gray-500 mt-1">Dalam periode yang dipilih</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <div class="text-sm font-semibold text-gray-600">Event Kritis</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['critical_logs']) }}</div>
            <div class="text-xs text-gray-500 mt-1">Memerlukan perhatian</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="text-sm font-semibold text-gray-600">Pengguna Aktif</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['unique_users']) }}</div>
            <div class="text-xs text-gray-500 mt-1">User unik yang beraktivitas</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="text-sm font-semibold text-gray-600">Login Attempts</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['login_attempts']) }}</div>
            <div class="text-xs text-gray-500 mt-1">Total percobaan login</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pencarian</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari deskripsi, IP, URL..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Date Range -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Periode</label>
                <select wire:model.live="dateRange" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="24hours">24 Jam Terakhir</option>
                    <option value="7days">7 Hari Terakhir</option>
                    <option value="30days">30 Hari Terakhir</option>
                    <option value="90days">90 Hari Terakhir</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            <!-- User Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">User</label>
                <select wire:model.live="selectedUser" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Aksi</label>
                <select wire:model.live="selectedAction" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}">{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                <select wire:model.live="selectedCategory" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ ucfirst(str_replace('_', ' ', $category)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Custom Date Range -->
        @if($dateRange === 'custom')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" wire:model="customStartDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Selesai</label>
                    <input type="date" wire:model="customEndDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="flex items-end gap-2">
                    <button wire:click="applyCustomDate" class="flex-1 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        Terapkan
                    </button>
                    <button wire:click="clearFilters" class="px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                        Reset
                    </button>
                </div>
            </div>
        @else
            <div class="mt-4 text-right">
                <button wire:click="clearFilters" class="px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                    Reset Filter
                </button>
            </div>
        @endif
    </div>

    <!-- Activity Logs Table -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Aktivitas</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Severity</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $log->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $log->user->name ?? 'System' }}</div>
                                <div class="text-xs text-gray-500">{{ $log->user->role->display_name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="h-8 w-8 rounded-full {{ $log->color_class }} flex items-center justify-center">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $log->icon }}"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800 mr-2">
                                                {{ $log->action }}
                                            </span>
                                            {{ $log->description }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $log->category }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $log->ip_address }}</div>
                                <div class="text-xs text-gray-500">{{ $log->method }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full
                                    {{ $log->severity === 'critical' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $log->severity === 'warning' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $log->severity === 'info' ? 'bg-blue-100 text-blue-800' : '' }}">
                                    {{ ucfirst($log->severity) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button wire:click="viewDetails({{ $log->id }})" class="px-3 py-1.5 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-lg text-xs font-semibold transition-colors">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-gray-500 font-medium">Tidak ada log aktivitas</p>
                                <p class="text-sm text-gray-400 mt-1">Coba ubah filter atau periode waktu</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal && $selectedLog)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" wire:click="closeDetailModal"></div>

            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full border border-gray-200">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between p-6 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-gray-900">Detail Activity Log</h3>
                        <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm font-semibold text-gray-600">Waktu</div>
                                <div class="text-base text-gray-900">{{ $selectedLog->created_at->format('d F Y, H:i:s') }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-600">User</div>
                                <div class="text-base text-gray-900">{{ $selectedLog->user->name ?? 'System' }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-600">Aksi</div>
                                <div class="text-base text-gray-900">{{ $selectedLog->action }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-600">Kategori</div>
                                <div class="text-base text-gray-900">{{ $selectedLog->category }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-600">IP Address</div>
                                <div class="text-base text-gray-900">{{ $selectedLog->ip_address }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-600">Method</div>
                                <div class="text-base text-gray-900">{{ $selectedLog->method }}</div>
                            </div>
                        </div>

                        <div>
                            <div class="text-sm font-semibold text-gray-600 mb-2">Deskripsi</div>
                            <div class="text-base text-gray-900 p-3 bg-gray-50 rounded-lg">{{ $selectedLog->description }}</div>
                        </div>

                        @if($selectedLog->url)
                            <div>
                                <div class="text-sm font-semibold text-gray-600 mb-2">URL</div>
                                <div class="text-sm text-gray-900 p-3 bg-gray-50 rounded-lg break-all">{{ $selectedLog->url }}</div>
                            </div>
                        @endif

                        @if($selectedLog->user_agent)
                            <div>
                                <div class="text-sm font-semibold text-gray-600 mb-2">User Agent</div>
                                <div class="text-sm text-gray-900 p-3 bg-gray-50 rounded-lg break-all">{{ $selectedLog->user_agent }}</div>
                            </div>
                        @endif

                        @if($selectedLog->properties)
                            <div>
                                <div class="text-sm font-semibold text-gray-600 mb-2">Properties</div>
                                <pre class="text-xs text-gray-900 p-3 bg-gray-50 rounded-lg overflow-auto max-h-64">{{ json_encode($selectedLog->properties, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
                        <button wire:click="closeDetailModal" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
