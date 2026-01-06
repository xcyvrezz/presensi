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
                    <h1 class="text-2xl font-bold text-slate-900">Persetujuan Izin Siswa</h1>
                    <p class="text-slate-600 mt-1">{{ $class->name }} - {{ $class->department->name }}</p>
                </div>
                <div class="flex gap-3">
                    @if($statusFilter === 'pending')
                        <button wire:click="bulkApprove"
                                wire:confirm="Yakin ingin menyetujui semua pengajuan izin yang pending?"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all duration-300 shadow-sm hover:shadow-md font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Setujui Semua
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-600 rounded-xl">
                <p class="text-sm font-medium text-blue-900">{{ session('success') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-600 rounded-xl">
                <p class="text-sm font-medium text-red-900">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                    <select wire:model.live="statusFilter"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
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

        <!-- Requests Table -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Siswa</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Tanggal Izin</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Alasan</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Diajukan</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @forelse($requests as $request)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-slate-900">{{ $request->student->full_name }}</div>
                                            <div class="text-sm text-slate-500">{{ $request->student->nis }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900">{{ $request->absence_date->format('d/m/Y') }}</div>
                                    <div class="text-xs text-slate-500">{{ $request->absence_date->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs font-medium rounded-lg
                                        {{ $request->type === 'sakit' ? 'bg-slate-100 text-slate-700' : 'bg-blue-50 text-blue-700' }}">
                                        {{ $request->type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-900">{{ Str::limit($request->reason, 50) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs font-medium rounded-lg
                                        @if($request->status === 'pending') bg-slate-100 text-slate-700
                                        @elseif($request->status === 'approved') bg-blue-50 text-blue-700
                                        @else bg-slate-100 text-slate-700
                                        @endif">
                                        {{ $request->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    @if($request->status === 'pending')
                                        <button wire:click="openApprovalModal({{ $request->id }})"
                                                class="text-blue-600 hover:text-blue-900 font-medium">
                                            Review
                                        </button>
                                    @else
                                        <button wire:click="openApprovalModal({{ $request->id }})"
                                                class="text-blue-600 hover:text-blue-900 font-medium">
                                            Detail
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="mt-2">Tidak ada pengajuan izin</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($requests->hasPages())
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>

        <!-- Approval/Rejection Modal -->
        @if($showApprovalModal && $selectedRequest)
            <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: true }">
                <!-- Background overlay with blur effect -->
                <div class="fixed inset-0 bg-white/70 backdrop-blur-sm transition-all duration-200"
                     wire:click="closeApprovalModal"></div>

                <!-- Modal panel -->
                <div class="flex items-center justify-center min-h-screen px-4 py-8">
                    <div class="relative bg-white rounded-2xl text-left shadow-2xl max-w-2xl w-full border border-gray-200"
                         style="animation: slideUp 0.3s ease-out;">
                        <style>
                            @keyframes slideUp {
                                from {
                                    opacity: 0;
                                    transform: translateY(20px);
                                }
                                to {
                                    opacity: 1;
                                    transform: translateY(0);
                                }
                            }
                        </style>
                        <!-- Modal Header -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 pt-6 pb-5 border-b border-gray-200">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">Detail Pengajuan Izin</h3>
                                        <p class="text-sm text-gray-600 mt-0.5">Review dan setujui pengajuan siswa</p>
                                    </div>
                                </div>
                                <button wire:click="closeApprovalModal" type="button"
                                        class="text-gray-400 hover:text-gray-600 hover:bg-white rounded-full p-2 transition-all">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Modal Body -->
                        <div class="bg-white px-6 py-6 space-y-5 max-h-[500px] overflow-y-auto">
                            <!-- Student Info Card -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-700 mb-1.5 uppercase tracking-wide">Nama Siswa</label>
                                        <p class="text-base text-gray-900 font-semibold">{{ $selectedRequest->student->full_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-700 mb-1.5 uppercase tracking-wide">NIS</label>
                                        <p class="text-base text-gray-900 font-medium">{{ $selectedRequest->student->nis }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Request Details -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="flex items-center text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">
                                        <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Tanggal Izin
                                    </label>
                                    <p class="text-base text-gray-900 font-medium">{{ $selectedRequest->absence_date->format('d F Y') }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $selectedRequest->absence_date->diffForHumans() }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="flex items-center text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">
                                        <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        Jenis
                                    </label>
                                    <span class="inline-flex px-3 py-1.5 text-sm font-bold rounded-lg
                                        {{ $selectedRequest->type === 'sakit' ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                        {{ $selectedRequest->type_label }}
                                    </span>
                                </div>
                            </div>

                            <!-- Reason -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="flex items-center text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                                    </svg>
                                    Alasan
                                </label>
                                <p class="text-sm text-gray-900 leading-relaxed">{{ $selectedRequest->reason }}</p>
                            </div>

                            <!-- Document -->
                            @if($selectedRequest->document_path)
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                    <label class="flex items-center text-xs font-semibold text-amber-800 mb-2 uppercase tracking-wide">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Dokumen Pendukung
                                    </label>
                                    <a href="{{ asset('storage/' . $selectedRequest->document_path) }}"
                                       target="_blank"
                                       class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg transition-all shadow-sm hover:shadow-md">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Lihat Dokumen
                                    </a>
                                </div>
                            @endif

                            <!-- Status -->
                            <div>
                                <label class="flex items-center text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Status Pengajuan
                                </label>
                                <span class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-lg
                                    @if($selectedRequest->status === 'pending') bg-yellow-100 text-yellow-800 border-2 border-yellow-300
                                    @elseif($selectedRequest->status === 'approved') bg-green-100 text-green-800 border-2 border-green-300
                                    @else bg-red-100 text-red-800 border-2 border-red-300
                                    @endif">
                                    @if($selectedRequest->status === 'pending')
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @elseif($selectedRequest->status === 'approved')
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                    {{ $selectedRequest->status_label }}
                                </span>
                            </div>

                            @if($selectedRequest->status === 'approved' && $selectedRequest->approvedBy)
                                <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl p-5">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-semibold text-green-700 mb-1 uppercase tracking-wide">Disetujui oleh</label>
                                            <p class="text-base text-green-900 font-bold">{{ $selectedRequest->approvedBy->name }}</p>
                                            <p class="text-sm text-green-700 mt-1">{{ $selectedRequest->approved_at->format('d F Y, H:i') }} WIB</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($selectedRequest->status === 'rejected')
                                <div class="bg-gradient-to-br from-red-50 to-rose-50 border-2 border-red-300 rounded-xl p-5">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-semibold text-red-700 mb-1 uppercase tracking-wide">Alasan Penolakan</label>
                                            <p class="text-sm text-red-900 leading-relaxed">{{ $selectedRequest->rejection_reason }}</p>
                                            @if($selectedRequest->approvedBy)
                                                <div class="mt-3 pt-3 border-t border-red-200">
                                                    <p class="text-xs text-red-700">
                                                        <span class="font-semibold">Ditolak oleh:</span> {{ $selectedRequest->approvedBy->name }}
                                                    </p>
                                                    <p class="text-xs text-red-600 mt-0.5">{{ $selectedRequest->approved_at->format('d F Y, H:i') }} WIB</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($selectedRequest->status === 'pending')
                                <div class="bg-gradient-to-br from-orange-50 to-red-50 border-2 border-orange-200 rounded-xl p-5">
                                    <label class="flex items-center text-xs font-semibold text-orange-800 mb-3 uppercase tracking-wide">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        Alasan Penolakan (Wajib diisi jika ingin menolak)
                                    </label>
                                    <textarea wire:model="rejectionReason"
                                              rows="4"
                                              placeholder="Contoh: Surat keterangan dokter tidak valid atau tidak melampirkan bukti yang memadai..."
                                              class="w-full px-4 py-3 border-2 border-orange-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"></textarea>
                                    @error('rejectionReason')
                                        <div class="mt-2 flex items-center gap-2 text-red-700 bg-red-50 px-3 py-2 rounded-lg border border-red-200">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <p class="text-sm font-medium">{{ $message }}</p>
                                        </div>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <!-- Modal Footer -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-5 border-t border-gray-200">
                            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                                @if($selectedRequest->status === 'pending')
                                    <button wire:click="reject"
                                            wire:confirm="Yakin ingin menolak pengajuan izin ini?"
                                            class="group w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                                        <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Tolak Pengajuan
                                    </button>
                                    <button wire:click="approve({{ $selectedRequest->id }})"
                                            wire:confirm="Yakin ingin menyetujui pengajuan izin ini?"
                                            class="group w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                                        <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Setujui Pengajuan
                                    </button>
                                @endif
                                <button wire:click="closeApprovalModal"
                                        class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl shadow hover:shadow-md transition-all duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
