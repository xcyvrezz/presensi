<div>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pengajuan Izin</h1>
        <p class="text-gray-600 mt-1">Ajukan izin sakit atau keperluan lainnya</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Info Box -->
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Wali Kelas:</strong> {{ $student->class->waliKelas->name ?? '-' }}<br>
                    <strong>Informasi:</strong> Pengajuan izin akan ditinjau oleh wali kelas Anda. Pastikan alasan yang Anda berikan jelas dan valid.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form Pengajuan Izin -->
        <div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Form Pengajuan Baru</h2>

                <form wire:submit.prevent="submit">
                    <!-- Tanggal Izin -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Izin</label>
                        <input type="date" wire:model="absenceDate"
                               min="{{ now()->format('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('absenceDate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jenis Izin -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Izin</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer {{ $type === 'izin' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                                <input type="radio" wire:model="type" value="izin" class="mr-3">
                                <div>
                                    <div class="font-medium">Izin</div>
                                    <div class="text-xs text-gray-500">Keperluan pribadi/keluarga</div>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer {{ $type === 'sakit' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                                <input type="radio" wire:model="type" value="sakit" class="mr-3">
                                <div>
                                    <div class="font-medium">Sakit</div>
                                    <div class="text-xs text-gray-500">Kondisi kesehatan</div>
                                </div>
                            </label>
                        </div>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alasan -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan</label>
                        <textarea wire:model="reason"
                                  rows="4"
                                  placeholder="Jelaskan alasan izin Anda secara detail..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        <p class="mt-1 text-xs text-gray-500">{{ strlen($reason) }}/500 karakter</p>
                        @error('reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dokumen Pendukung -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Dokumen Pendukung (Opsional)
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition">
                            <div class="space-y-1 text-center">
                                @if ($document)
                                    <svg class="mx-auto h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm text-gray-600">{{ $document->getClientOriginalName() }}</p>
                                    <button type="button" wire:click="$set('document', null)" class="text-sm text-red-600 hover:text-red-800">
                                        Hapus File
                                    </button>
                                @else
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                            <span>Upload file</span>
                                            <input type="file" wire:model="document" class="sr-only" accept=".pdf,.jpg,.jpeg,.png">
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PDF, JPG, PNG max 2MB</p>
                                @endif
                            </div>
                        </div>
                        @error('document')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">
                            Untuk izin sakit, lampirkan surat keterangan dokter. Untuk izin lainnya, lampirkan dokumen pendukung jika ada.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submit">Kirim Pengajuan</span>
                        <span wire:loading wire:target="submit">Mengirim...</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Daftar Pengajuan -->
        <div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Pengajuan</h2>

                <div class="space-y-4">
                    @forelse($requests as $request)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($request->status === 'approved') bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $request->status_label }}
                                        </span>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $request->type_label }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $request->absence_date->format('d F Y') }}
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ Str::limit($request->reason, 80) }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-2">
                                        Diajukan: {{ $request->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div class="flex flex-col gap-2 ml-4">
                                    <button wire:click="viewRequest({{ $request->id }})"
                                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                        Detail
                                    </button>
                                    @if($request->status === 'pending')
                                        <button wire:click="cancelRequest({{ $request->id }})"
                                                wire:confirm="Yakin ingin membatalkan pengajuan ini?"
                                                class="text-xs text-red-600 hover:text-red-800 font-medium">
                                            Batalkan
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Belum ada pengajuan izin</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($requests->hasPages())
                    <div class="mt-4">
                        {{ $requests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal && $selectedRequest)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDetailModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-start justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Detail Pengajuan Izin</h3>
                            <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Status</label>
                                <p class="mt-1">
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                                        @if($selectedRequest->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($selectedRequest->status === 'approved') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ $selectedRequest->status_label }}
                                    </span>
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Tanggal Izin</label>
                                <p class="mt-1 text-gray-900">{{ $selectedRequest->absence_date->format('d F Y') }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Jenis</label>
                                <p class="mt-1 text-gray-900">{{ $selectedRequest->type_label }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Alasan</label>
                                <p class="mt-1 text-gray-900">{{ $selectedRequest->reason }}</p>
                            </div>

                            @if($selectedRequest->document_path)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Dokumen Pendukung</label>
                                    <a href="{{ Storage::url($selectedRequest->document_path) }}"
                                       target="_blank"
                                       class="mt-1 inline-flex items-center text-blue-600 hover:text-blue-800">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Lihat Dokumen
                                    </a>
                                </div>
                            @endif

                            @if($selectedRequest->status === 'approved')
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Disetujui oleh</label>
                                    <p class="mt-1 text-gray-900">{{ $selectedRequest->approvedBy->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $selectedRequest->approved_at?->format('d/m/Y H:i') }}</p>
                                </div>
                            @endif

                            @if($selectedRequest->status === 'rejected' && $selectedRequest->rejection_reason)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Alasan Penolakan</label>
                                    <p class="mt-1 text-red-700">{{ $selectedRequest->rejection_reason }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="closeDetailModal"
                                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:ml-3 sm:w-auto sm:text-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
