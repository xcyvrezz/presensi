<div>
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="{{ route('admin.students.index') }}" class="mr-4 text-slate-600 hover:text-slate-900 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Tambah Siswa</h1>
                <p class="text-sm text-slate-600 mt-1">Tambahkan siswa baru ke sistem</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form wire:submit="save">
        <div class="space-y-6">
            <!-- Account Information -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Informasi Akun</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Nama User <span class="text-blue-600">*</span></label>
                        <input type="text" wire:model="name" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('name') border-blue-500 @enderror">
                        @error('name') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email <span class="text-blue-600">*</span></label>
                        <input type="email" wire:model="email" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('email') border-blue-500 @enderror">
                        @error('email') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Password <span class="text-blue-600">*</span></label>
                        <input type="password" wire:model="password" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('password') border-blue-500 @enderror">
                        @error('password') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Konfirmasi Password <span class="text-blue-600">*</span></label>
                        <input type="password" wire:model="password_confirmation" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Informasi Dasar</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">NIS <span class="text-blue-600">*</span></label>
                        <input type="text" wire:model="nis" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('nis') border-blue-500 @enderror">
                        @error('nis') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">NISN</label>
                        <input type="text" wire:model="nisn" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('nisn') border-blue-500 @enderror">
                        @error('nisn') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Card UID (MIFARE)</label>
                        <input type="text" wire:model="card_uid" placeholder="Kosongkan jika belum ada" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('card_uid') border-blue-500 @enderror">
                        @error('card_uid') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Kelas <span class="text-blue-600">*</span></label>
                        <select wire:model="class_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('class_id') border-blue-500 @enderror">
                            <option value="">Pilih Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }} - {{ $class->department->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap <span class="text-blue-600">*</span></label>
                        <input type="text" wire:model="full_name" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('full_name') border-blue-500 @enderror">
                        @error('full_name') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Nama Panggilan</label>
                        <input type="text" wire:model="nickname" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Jenis Kelamin <span class="text-blue-600">*</span></label>
                        <select wire:model="gender" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('gender') border-blue-500 @enderror">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                        @error('gender') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tempat Lahir <span class="text-blue-600">*</span></label>
                        <input type="text" wire:model="birth_place" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('birth_place') border-blue-500 @enderror">
                        @error('birth_place') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Lahir <span class="text-blue-600">*</span></label>
                        <input type="date" wire:model="birth_date" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('birth_date') border-blue-500 @enderror">
                        @error('birth_date') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Telepon Siswa</label>
                        <input type="text" wire:model="phone" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Alamat <span class="text-blue-600">*</span></label>
                        <textarea wire:model="address" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('address') border-blue-500 @enderror"></textarea>
                        @error('address') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Parent Information -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Informasi Orang Tua</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Nama Orang Tua <span class="text-blue-600">*</span></label>
                        <input type="text" wire:model="parent_name" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('parent_name') border-blue-500 @enderror">
                        @error('parent_name') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Telepon Orang Tua <span class="text-blue-600">*</span></label>
                        <input type="text" wire:model="parent_phone" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('parent_phone') border-blue-500 @enderror">
                        @error('parent_phone') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Photo & Settings -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Foto & Pengaturan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Foto Siswa</label>
                        <input type="file" wire:model="photo" accept="image/*" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('photo') border-blue-500 @enderror">
                        @error('photo') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-slate-500">Maksimal 2MB. Format: JPG, PNG</p>

                        @if ($photo)
                            <div class="mt-3">
                                <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="w-32 h-32 object-cover rounded-xl border-2 border-slate-200">
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" wire:model="nfc_enabled" id="nfc_enabled" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                            <label for="nfc_enabled" class="ml-2 block text-sm text-slate-700">Aktifkan NFC untuk siswa ini</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" wire:model="is_active" id="is_active" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                            <label for="is_active" class="ml-2 block text-sm text-slate-700">Status Aktif</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.students.index') }}" class="px-6 py-2.5 border border-slate-200 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-all">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-300 shadow-sm hover:shadow-md" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </div>
    </form>
</div>
