@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div>
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Manajemen Siswa</h1>
            <p class="text-sm text-slate-600 mt-1">Kelola semua data siswa</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.students.import') }}" class="inline-flex items-center px-4 py-2.5 bg-slate-600 hover:bg-slate-700 text-white font-medium rounded-xl transition-all duration-300 shadow-sm hover:shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Import
            </a>
            <a href="{{ route('admin.students.create') }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-300 shadow-sm hover:shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah Siswa
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-600 rounded-xl">
            <p class="text-sm font-medium text-blue-900">{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-slate-50 border-l-4 border-slate-600 rounded-xl">
            <p class="text-sm font-medium text-slate-900">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-2">Cari</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="NIS, NISN, Nama, Card UID..." class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>

            <!-- Class Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Kelas</label>
                <select wire:model.live="classFilter" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Department Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Jurusan</label>
                <select wire:model.live="departmentFilter" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Semua Jurusan</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Gender Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Jenis Kelamin</label>
                <select wire:model.live="genderFilter" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Semua</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
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

    <!-- Students Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Foto</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">NIS/NISN</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Card UID</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">NFC</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($student->photo)
                                    <img src="{{ Storage::url($student->photo) }}" alt="{{ $student->full_name }}" class="w-10 h-10 rounded-full object-cover border-2 border-slate-200">
                                @else
                                    <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center border-2 border-slate-200">
                                        <span class="text-slate-600 font-semibold text-sm">{{ substr($student->full_name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ $student->nis }}</div>
                                <div class="text-xs text-slate-500">{{ $student->nisn ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $student->full_name }}</div>
                                <div class="text-xs text-slate-500">{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">{{ $student->class->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $student->class->department->code ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-mono text-slate-700">
                                    {{ $student->card_uid ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="toggleNfc({{ $student->id }})" wire:confirm="Yakin ingin mengubah status NFC siswa ini?" class="inline-flex px-3 py-1 text-xs font-medium rounded-lg transition-all duration-300 {{ $student->nfc_enabled ? 'bg-blue-50 text-blue-700 hover:bg-blue-100' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                    {{ $student->nfc_enabled ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="toggleStatus({{ $student->id }})" wire:confirm="Yakin ingin mengubah status siswa ini?" class="inline-flex px-3 py-1 text-xs font-medium rounded-lg transition-all duration-300 {{ $student->is_active ? 'bg-blue-50 text-blue-700 hover:bg-blue-100' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                    {{ $student->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.students.edit', $student) }}" class="text-blue-600 hover:text-blue-700 transition-colors">Edit</a>
                                    <button wire:click="deleteStudent({{ $student->id }})" wire:confirm="Yakin ingin menghapus siswa ini? Data yang sudah dihapus tidak dapat dikembalikan." class="text-slate-600 hover:text-slate-900 transition-colors">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                                <p class="mt-2">Tidak ada data siswa</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $students->links() }}
        </div>
    </div>
</div>
