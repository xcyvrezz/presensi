<div>
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="{{ route('admin.users.index') }}" class="mr-4 text-slate-600 hover:text-slate-900 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Tambah Pengguna Baru</h1>
                <p class="text-sm text-slate-600 mt-1">Buat akun pengguna baru</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        <form wire:submit="save">
            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                        Nama Lengkap <span class="text-blue-600">*</span>
                    </label>
                    <input type="text" id="name" wire:model="name" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('name') border-blue-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                        Email <span class="text-blue-600">*</span>
                    </label>
                    <input type="email" id="email" wire:model="email" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('email') border-blue-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                            Password <span class="text-blue-600">*</span>
                        </label>
                        <input type="password" id="password" wire:model="password" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('password') border-blue-500 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-2">
                            Konfirmasi Password <span class="text-blue-600">*</span>
                        </label>
                        <input type="password" id="password_confirmation" wire:model="password_confirmation" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>

                <!-- Role -->
                <div>
                    <label for="role_id" class="block text-sm font-medium text-slate-700 mb-2">
                        Role <span class="text-blue-600">*</span>
                    </label>
                    <select id="role_id" wire:model="role_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('role_id') border-blue-500 @enderror">
                        <option value="0">Pilih Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-slate-700">Aktif</span>
                    </label>
                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-8 flex justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition-all font-medium">
                    Batal
                </a>
                <button type="submit" wire:loading.attr="disabled" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-300 disabled:opacity-50 shadow-sm hover:shadow-md">
                    <span wire:loading.remove>Simpan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
