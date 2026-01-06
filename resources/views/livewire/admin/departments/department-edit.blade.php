<div>
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Jurusan</h1>
                <p class="text-gray-600 mt-1">Perbarui informasi jurusan {{ $department->code }}</p>
            </div>
            <a href="{{ route('admin.departments.index') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Validation Errors Summary -->
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500">
            <h3 class="text-sm font-medium text-red-800 mb-2">Terdapat kesalahan:</h3>
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Department Stats -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg">
        <div class="flex items-center gap-4">
            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-blue-800">Statistik Jurusan</p>
                <p class="text-sm text-blue-700">
                    Jumlah Kelas: <span class="font-semibold">{{ $department->classes()->count() }}</span> |
                    Jumlah Siswa: <span class="font-semibold">{{ $department->students()->count() }}</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form wire:submit.prevent="save">
        <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
            <!-- Informasi Dasar -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Informasi Dasar</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Kode Jurusan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kode Jurusan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="code"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase @error('code') border-red-500 @enderror"
                               placeholder="Contoh: PPLG"
                               maxlength="10">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Maksimal 10 karakter, akan otomatis uppercase</p>
                    </div>

                    <!-- Nama Jurusan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Jurusan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                               placeholder="Contoh: Pengembangan Perangkat Lunak dan Gim"
                               maxlength="100">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kepala Jurusan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kepala Jurusan</label>
                        <input type="text" wire:model="head_teacher"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('head_teacher') border-red-500 @enderror"
                               placeholder="Nama kepala jurusan">
                        @error('head_teacher')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Telepon -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                        <input type="text" wire:model="phone"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                               placeholder="08xxxxxxxxxx"
                               maxlength="20">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea wire:model="description"
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                                  placeholder="Deskripsi singkat tentang jurusan ini..."></textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="is_active"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Jurusan Aktif</span>
                        </label>
                        <p class="ml-6 text-xs text-gray-500">Centang jika jurusan ini masih aktif menerima siswa</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t">
                <a href="{{ route('admin.departments.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span wire:loading.remove>Perbarui</span>
                    <span wire:loading>Memperbarui...</span>
                </button>
            </div>
        </div>
    </form>
</div>
