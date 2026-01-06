<div>
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Manajemen Pengguna</h1>
                <p class="text-sm text-slate-600 mt-1">Kelola semua pengguna sistem</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-300 shadow-sm hover:shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah Pengguna
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-600 p-4 rounded-xl">
            <p class="text-sm font-medium text-blue-900">{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 bg-slate-50 border-l-4 border-slate-600 p-4 rounded-xl">
            <p class="text-sm font-medium text-slate-900">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 mb-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Cari</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nama atau email..." class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>

            <!-- Role Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Role</label>
                <select wire:model.live="roleFilter" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Login Terakhir</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ $user->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-600">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 text-xs font-medium rounded-lg bg-slate-100 text-slate-700">
                                    {{ $user->role->display_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="toggleStatus({{ $user->id }})"
                                        wire:confirm="Apakah Anda yakin ingin mengubah status user ini?"
                                        class="inline-flex px-3 py-1 text-xs font-medium rounded-lg transition-all duration-300 {{ $user->is_active ? 'bg-blue-50 text-blue-700 hover:bg-blue-100' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-700 mr-4 transition-colors">Edit</a>
                                <button wire:click="deleteUser({{ $user->id }})"
                                        wire:confirm="Apakah Anda yakin ingin menghapus user ini?"
                                        class="text-slate-600 hover:text-slate-900 transition-colors">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="mt-2">Tidak ada data pengguna</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $users->links() }}
        </div>
    </div>
</div>
