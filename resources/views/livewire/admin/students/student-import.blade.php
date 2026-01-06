<div>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.students.index') }}" class="text-slate-600 hover:text-slate-900 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Import Data Siswa</h1>
                    <p class="text-sm text-slate-600 mt-2">Import data siswa dari file Excel</p>
                </div>
            </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Instructions & Template -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-slate-200 rounded-xl p-6 hover:shadow-md transition-all sticky top-6">
                <h3 class="text-lg font-bold text-slate-900 mb-5">Panduan Import</h3>

                <div class="space-y-4">
                    <!-- Step 1: Download Template -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center flex-shrink-0 font-bold text-sm">
                                1
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-slate-900 mb-1">Download Template</h4>
                                <p class="text-sm text-slate-600 mb-3">Template Excel dengan format dan contoh data</p>
                            </div>
                        </div>
                        <button wire:click="downloadTemplate" class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2 shadow-sm hover:shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download Template
                        </button>
                    </div>

                    <!-- Step 2: Fill Data -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-8 h-8 bg-slate-600 text-white rounded-lg flex items-center justify-center flex-shrink-0 font-bold text-sm">
                                2
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-slate-900 mb-2">Isi Data Siswa</h4>
                            </div>
                        </div>
                        <div class="ml-11 space-y-2 text-sm">
                            <div class="p-2 bg-white rounded-lg border border-slate-200">
                                <p class="text-xs font-semibold text-red-700 uppercase mb-1">Kolom Wajib:</p>
                                <p class="text-slate-700">nis, full_name, gender, birth_place, birth_date, parent_name, parent_phone, class_name, email</p>
                            </div>
                            <div class="p-2 bg-white rounded-lg border border-slate-200">
                                <p class="text-xs font-semibold text-slate-600 uppercase mb-1">Kolom Opsional:</p>
                                <p class="text-slate-600">nisn, nickname, address, phone, password, card_uid</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Upload -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-slate-600 text-white rounded-lg flex items-center justify-center flex-shrink-0 font-bold text-sm">
                                3
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-slate-900 mb-1">Upload File Excel</h4>
                                <p class="text-sm text-slate-600">Upload file yang sudah diisi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="mt-6 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <h4 class="font-semibold text-slate-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Catatan Penting
                    </h4>
                    <ul class="space-y-1 text-sm text-slate-600">
                        <li>• NIS, Email, NISN, Card UID harus unik</li>
                        <li>• Nama kelas harus sudah terdaftar</li>
                        <li>• Format tanggal: YYYY-MM-DD</li>
                        <li>• Gender: L atau P</li>
                        <li>• Format: .xlsx atau .xls (max 10MB)</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right: Upload Form & Results -->
        <div class="lg:col-span-2">
            @if(!$hasImported)
                <!-- Upload Form -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 hover:shadow-md transition-all">
                    <h3 class="text-lg font-bold text-slate-900 mb-5">Upload File Excel</h3>

                    <form wire:submit="import">
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-3">
                                Pilih File Excel (.xlsx atau .xls)
                            </label>
                            <input
                                type="file"
                                wire:model="file"
                                accept=".xlsx,.xls"
                                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('file') border-red-500 @enderror">
                            @error('file')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror

                            @if ($file)
                                <div class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">{{ $file->getClientOriginalName() }}</p>
                                                <p class="text-xs text-slate-600">{{ number_format($file->getSize() / 1024, 2) }} KB</p>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="$set('file', null)" class="text-slate-400 hover:text-red-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex gap-3">
                            <button type="submit"
                                    class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2 shadow-sm hover:shadow-md"
                                    wire:loading.attr="disabled"
                                    wire:target="import">
                                <span wire:loading.remove wire:target="import" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    Mulai Import
                                </span>
                                <span wire:loading wire:target="import" class="flex items-center gap-2">
                                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Memproses...
                                </span>
                            </button>
                            <a href="{{ route('admin.students.index') }}" class="px-6 py-3 border-2 border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                                Batal
                            </a>
                        </div>
                    </form>

                    <!-- Loading Indicator -->
                    <div wire:loading wire:target="import" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                        <div class="flex items-center gap-3">
                            <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <div>
                                <p class="text-blue-900 font-semibold">Sedang memproses import data...</p>
                                <p class="text-sm text-blue-700 mt-1">Mohon tunggu, proses ini mungkin memakan waktu</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Import Results -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 hover:shadow-md transition-all">
                    <h3 class="text-lg font-bold text-slate-900 mb-5">Hasil Import</h3>

                    <!-- Summary Cards -->
                    <div class="grid grid-cols-2 gap-5 mb-6">
                        <div class="group bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-md transition-all">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Berhasil Diimport</p>
                                    <p class="text-4xl font-bold text-slate-900 mb-2">{{ number_format($importResults['success']) }}</p>
                                    <p class="text-sm text-slate-600">Data siswa berhasil</p>
                                </div>
                                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="group bg-white border border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-md transition-all">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Gagal Diimport</p>
                                    <p class="text-4xl font-bold text-slate-900 mb-2">{{ number_format($importResults['failed']) }}</p>
                                    <p class="text-sm text-slate-600">Data dengan error</p>
                                </div>
                                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Errors List -->
                    @if(count($importResults['errors']) > 0)
                        <div class="mb-6 p-5 bg-slate-50 border border-slate-200 rounded-xl">
                            <h4 class="font-semibold text-slate-900 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Detail Error ({{ count($importResults['errors']) }})
                            </h4>
                            <div class="bg-white border border-slate-200 rounded-lg p-4 max-h-96 overflow-y-auto">
                                <ul class="space-y-2 text-sm text-slate-700">
                                    @foreach($importResults['errors'] as $error)
                                        <li class="flex items-start gap-2 p-2 hover:bg-slate-50 rounded transition-colors">
                                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>{{ $error }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button wire:click="resetImport" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors shadow-sm hover:shadow-md">
                            Import Lagi
                        </button>
                        <a href="{{ route('admin.students.index') }}" class="px-6 py-3 bg-slate-600 hover:bg-slate-700 text-white font-semibold rounded-xl transition-colors shadow-sm hover:shadow-md">
                            Lihat Daftar Siswa
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
