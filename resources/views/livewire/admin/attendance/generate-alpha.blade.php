<div>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Generate Alpha - Siswa Tidak Absen</h1>
                <p class="text-slate-600 mt-2">Generate otomatis record alpha untuk siswa yang tidak melakukan absensi</p>
            </div>
            <a href="{{ route('admin.attendance.index') }}" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2.5 rounded-xl flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Working Days -->
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-slate-600">Total Hari Kerja</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalWorkingDays }}</p>
                </div>
            </div>
        </div>

        <!-- Processed Days -->
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-slate-600">Hari Terproses</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalProcessedDays }}</p>
                </div>
            </div>
        </div>

        <!-- Total Alpha Generated -->
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-slate-600">Total Alpha</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalAlphaGenerated }}</p>
                </div>
            </div>
        </div>

        <!-- Last Generated -->
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-slate-600">Terakhir Generate</p>
                    <p class="text-sm font-bold text-slate-900">{{ $lastGeneratedDate ?? '-' }}</p>
                    @if($lastGeneratedBy)
                        <p class="text-xs text-slate-500">oleh {{ $lastGeneratedBy }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Generate Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Generate Per Tanggal -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-6">Generate Alpha Per Tanggal</h2>

                <div class="space-y-6">
                    <!-- Date Selector -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Tanggal</label>
                        <input type="date"
                               wire:model="selectedDate"
                               max="{{ Carbon\Carbon::yesterday()->format('Y-m-d') }}"
                               class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        @error('selectedDate')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-slate-500">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Pilih tanggal hari kerja (Senin-Jumat) yang bukan hari libur. Maksimal kemarin.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button wire:click="preview"
                                wire:loading.attr="disabled"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors flex items-center justify-center gap-2 disabled:opacity-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="preview">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" wire:loading wire:target="preview">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="preview">Preview</span>
                            <span wire:loading wire:target="preview">Loading...</span>
                        </button>

                        @if($showPreview)
                            <button wire:click="generate"
                                    wire:loading.attr="disabled"
                                    wire:confirm="Apakah Anda yakin ingin generate {{ $previewData['students_without_attendance'] ?? 0 }} record alpha untuk tanggal {{ $previewData['date'] ?? '' }}? Pastikan data sudah benar!"
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors flex items-center justify-center gap-2 disabled:opacity-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="generate">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" wire:loading wire:target="generate">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="generate">Generate Alpha</span>
                                <span wire:loading wire:target="generate">Processing...</span>
                            </button>
                        @endif
                    </div>

                    <!-- Preview Data -->
                    @if($showPreview && !empty($previewData))
                        <div class="mt-6 p-5 bg-blue-50 border border-blue-200 rounded-xl">
                            <h3 class="text-lg font-bold text-blue-900 mb-4">Preview Data</h3>

                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div class="bg-white p-3 rounded-lg">
                                    <p class="text-xs text-slate-600">Total Siswa Aktif</p>
                                    <p class="text-xl font-bold text-slate-900">{{ $previewData['total_active_students'] }}</p>
                                </div>
                                <div class="bg-white p-3 rounded-lg">
                                    <p class="text-xs text-slate-600">Sudah Absen</p>
                                    <p class="text-xl font-bold text-green-600">{{ $previewData['students_with_attendance'] }}</p>
                                </div>
                                <div class="bg-white p-3 rounded-lg">
                                    <p class="text-xs text-slate-600">Tidak Absen (Alpha)</p>
                                    <p class="text-xl font-bold text-red-600">{{ $previewData['students_without_attendance'] }}</p>
                                </div>
                            </div>

                            @if($previewData['students_without_attendance'] > 0)
                                <div class="bg-white p-4 rounded-lg">
                                    <p class="text-sm font-semibold text-slate-700 mb-3">Siswa yang akan di-generate alpha ({{ $previewData['total_to_show'] }} dari {{ $previewData['students_without_attendance'] }}):</p>
                                    <ul class="space-y-2 text-sm">
                                        @foreach($previewData['students_list'] as $student)
                                            <li class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                                <span class="font-medium">{{ $student->full_name }}</span>
                                                <span class="text-slate-600">{{ $student->nis }} - {{ $student->class->name }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                    @if($previewData['students_without_attendance'] > 10)
                                        <p class="mt-3 text-xs text-slate-500 italic">+ {{ $previewData['students_without_attendance'] - 10 }} siswa lainnya...</p>
                                    @endif
                                </div>
                            @else
                                <div class="bg-white p-4 rounded-lg text-center">
                                    <svg class="w-12 h-12 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-green-600 font-semibold">Semua siswa sudah melakukan absensi!</p>
                                    <p class="text-sm text-slate-600 mt-1">Tidak ada yang perlu di-generate.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cancel/Undo Generation -->
            <div class="bg-white border border-red-200 rounded-xl p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Batalkan Generation Alpha</h2>
                        <p class="text-sm text-slate-600 mt-1">Hapus record alpha yang di-generate otomatis</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
                        <p class="text-sm text-red-800">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <strong>Peringatan:</strong> Fitur ini hanya akan menghapus record alpha yang di-generate otomatis oleh sistem. Record alpha yang diinput manual tidak akan terhapus.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Tanggal untuk Dibatalkan</label>
                        <input type="date"
                               wire:model="selectedDate"
                               max="{{ Carbon\Carbon::yesterday()->format('Y-m-d') }}"
                               class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all">
                        <p class="mt-2 text-xs text-slate-500">Pilih tanggal yang ingin dibatalkan generate alpha-nya</p>
                    </div>

                    <button wire:click="cancelGeneration('{{ $selectedDate }}')"
                            wire:loading.attr="disabled"
                            wire:confirm="Apakah Anda yakin ingin membatalkan generation alpha untuk tanggal {{ Carbon\Carbon::parse($selectedDate ?? date('Y-m-d'))->format('d/m/Y') }}?\n\nSemua record alpha yang di-generate otomatis untuk tanggal tersebut akan dihapus."
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors flex items-center justify-center gap-2 disabled:opacity-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="cancelGeneration">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" wire:loading wire:target="cancelGeneration">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="cancelGeneration">Batalkan Generation</span>
                        <span wire:loading wire:target="cancelGeneration">Processing...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="lg:col-span-1">
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-6 shadow-lg text-white mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold">Bulk Generate</h3>
                </div>

                <p class="text-sm text-white/90 mb-4">
                    Generate alpha untuk semua hari kerja di semester aktif yang belum diproses (dari awal semester hingga kemarin).
                </p>

                <button wire:click="generateBulk"
                        wire:loading.attr="disabled"
                        wire:confirm="PERINGATAN!\n\nAnda akan generate alpha untuk SEMUA hari kerja yang belum diproses di semester aktif. Proses ini mungkin memakan waktu beberapa menit.\n\nApakah Anda yakin?"
                        class="w-full bg-white text-amber-600 px-4 py-3 rounded-xl font-bold hover:bg-amber-50 transition-colors flex items-center justify-center gap-2 disabled:opacity-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="generateBulk">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" wire:loading wire:target="generateBulk">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="generateBulk">Bulk Generate</span>
                    <span wire:loading wire:target="generateBulk">Processing...</span>
                </button>

                <p class="text-xs text-white/70 mt-3">
                    ⚠️ Gunakan dengan hati-hati! Proses ini tidak bisa di-undo untuk semua tanggal sekaligus.
                </p>
            </div>

            <!-- Help Card -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Cara Penggunaan
                </h3>

                <ol class="space-y-3 text-sm text-slate-600">
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                        <span>Pilih tanggal yang ingin di-generate alpha</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                        <span>Klik "Preview" untuk melihat siswa yang tidak absen</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                        <span>Periksa data preview dengan teliti</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                        <span>Klik "Generate Alpha" untuk memproses</span>
                    </li>
                </ol>

                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-xs text-amber-800">
                        <strong>💡 Tips:</strong> Lakukan generate setiap hari atau gunakan Bulk Generate untuk memproses semua hari sekaligus.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
