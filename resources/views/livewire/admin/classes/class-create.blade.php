<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center mb-4">
            <a href="{{ route('admin.classes.index') }}" class="mr-4 text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Kelas</h1>
                <p class="text-sm text-gray-600 mt-1">Buat kelas baru dan assign wali kelas</p>
            </div>
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

    <!-- Form -->
    <form wire:submit.prevent="save">
        <div class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kelas</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="Contoh: X RPL 1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Format: [Tingkat] [Jurusan] [Nomor]</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan <span class="text-red-500">*</span></label>
                        <select wire:model="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('department_id') border-red-500 @enderror">
                            <option value="">Pilih Jurusan</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }} ({{ $department->code }})</option>
                            @endforeach
                        </select>
                        @error('department_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat Kelas <span class="text-red-500">*</span></label>
                        <select wire:model="grade" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('grade') border-red-500 @enderror">
                            <option value="">Pilih Tingkat</option>
                            <option value="10">Kelas 10</option>
                            <option value="11">Kelas 11</option>
                            <option value="12">Kelas 12</option>
                        </select>
                        @error('grade') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="academic_year" placeholder="Contoh: 2024/2025" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('academic_year') border-red-500 @enderror">
                        @error('academic_year') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Siswa <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="capacity" min="1" max="50" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('capacity') border-red-500 @enderror">
                        @error('capacity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Maksimal 50 siswa per kelas</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Wali Kelas</label>
                        <select wire:model="wali_kelas_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('wali_kelas_id') border-red-500 @enderror">
                            <option value="">Belum ada wali kelas</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        @error('wali_kelas_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Opsional, bisa diisi nanti</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea wire:model="description" rows="3" placeholder="Keterangan tambahan..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" wire:model="is_active" id="is_active" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">Kelas Aktif</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.classes.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </div>
    </form>
</div>
