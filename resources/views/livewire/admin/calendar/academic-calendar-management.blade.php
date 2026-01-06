<div>
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Kalender Akademik</h1>
                <p class="text-sm text-slate-600 mt-1">Kelola hari libur, ujian, dan agenda khusus lainnya</p>
            </div>
            <button wire:click="openCreateModal" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah Event
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-slate-200 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Bulan</label>
                <input type="month" wire:model.live="filterMonth" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tipe</label>
                <select wire:model.live="filterType" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Tipe</option>
                    <option value="holiday">Hari Libur</option>
                    <option value="exam">Ujian</option>
                    <option value="event">Acara/Kegiatan</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Semester</label>
                <select wire:model.live="filterSemester" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Semester</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button wire:click="$set('filterType', ''); $set('filterSemester', ''); $set('filterMonth', '{{ now()->format('Y-m') }}');" class="w-full px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- Calendar List --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Judul</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Jam Khusus</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Semester</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($calendars as $calendar)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-10 w-1.5 rounded-full" style="background-color: {{ $calendar->color }}"></div>
                                    <div class="text-sm">
                                        <div class="font-medium text-slate-800">
                                            {{ $calendar->start_date->format('d M Y') }}
                                        </div>
                                        @if($calendar->start_date->format('Y-m-d') !== $calendar->end_date->format('Y-m-d'))
                                            <div class="text-xs text-slate-500">
                                                s/d {{ $calendar->end_date->format('d M Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">{{ $calendar->title }}</div>
                                @if($calendar->description)
                                    <div class="text-xs text-slate-500 mt-0.5">{{ Str::limit($calendar->description, 60) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $calendar->type === 'holiday' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $calendar->type === 'exam' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $calendar->type === 'event' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $calendar->type === 'other' ? 'bg-slate-100 text-slate-700' : '' }}
                                ">
                                    @if($calendar->is_holiday)
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                        </svg>
                                    @endif
                                    {{ $calendar->type_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($calendar->use_custom_times)
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-blue-700 font-medium">Ya</span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($calendar->semester)
                                    <span class="text-sm text-slate-700">{{ $calendar->semester->name }}</span>
                                @else
                                    <span class="text-xs text-slate-400">Semua</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEditModal({{ $calendar->id }})" class="p-1.5 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="delete({{ $calendar->id }})" wire:confirm="Yakin ingin menghapus event ini?" class="p-1.5 text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                <svg class="h-12 w-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Tidak ada event kalender
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-4 py-3 border-t border-slate-200">
            {{ $calendars->links() }}
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 bg-slate-900/50 z-50 flex items-start justify-center pt-10 pb-10 overflow-y-auto" wire:click.self="closeModal">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4" @click.stop>
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">
                        {{ $editMode ? 'Edit Event Kalender' : 'Tambah Event Kalender' }}
                    </h2>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="px-6 py-4 max-h-[calc(100vh-16rem)] overflow-y-auto">
                    <form wire:submit.prevent="save">
                        {{-- Basic Info --}}
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Judul Event <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="title" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-300 @enderror">
                                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                                    <textarea wire:model="description" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="start_date" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('start_date') border-red-300 @enderror">
                                    @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="end_date" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-300 @enderror">
                                    @error('end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tipe <span class="text-red-500">*</span></label>
                                    <select wire:model.live="type" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="event">Acara/Kegiatan</option>
                                        <option value="holiday">Hari Libur</option>
                                        <option value="exam">Ujian (UTS/UAS/UKK)</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Semester</label>
                                    <select wire:model="semester_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Semua Semester</option>
                                        @foreach($semesters as $sem)
                                            <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Warna <span class="text-red-500">*</span></label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" wire:model="color" class="h-10 w-20 border border-slate-300 rounded-lg">
                                        <input type="text" wire:model="color" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="#3B82F6">
                                    </div>
                                </div>

                                <div>
                                    <label class="flex items-center gap-2 cursor-pointer mt-7">
                                        <input type="checkbox" wire:model="is_holiday" class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-2 focus:ring-blue-500">
                                        <span class="text-sm font-medium text-slate-700">Hari Libur (tidak ada absensi)</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Custom Times Section --}}
                            <div class="pt-4 border-t border-slate-200">
                                <label class="flex items-center gap-2 cursor-pointer mb-4">
                                    <input type="checkbox" wire:model.live="use_custom_times" class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-2 focus:ring-blue-500">
                                    <span class="text-sm font-semibold text-slate-700">Gunakan Jam Absensi Khusus</span>
                                    <span class="text-xs text-slate-500">(untuk UTS, UAS, UKK, dll)</span>
                                </label>

                                @if($use_custom_times)
                                    <div class="bg-blue-50 rounded-lg p-4 space-y-4">
                                        <p class="text-sm text-blue-700 mb-3">Kosongkan jika ingin menggunakan jam default dari pengaturan</p>

                                        <div class="grid grid-cols-3 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 mb-1">Mulai Check-in</label>
                                                <input type="time" wire:model="custom_check_in_start" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 mb-1">Batas Check-in</label>
                                                <input type="time" wire:model="custom_check_in_end" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 mb-1">Normal (Telat)</label>
                                                <input type="time" wire:model="custom_check_in_normal" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-3 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 mb-1">Mulai Check-out</label>
                                                <input type="time" wire:model="custom_check_out_start" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 mb-1">Batas Check-out</label>
                                                <input type="time" wire:model="custom_check_out_end" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 mb-1">Normal (Pulang Cepat)</label>
                                                <input type="time" wire:model="custom_check_out_normal" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            </div>
                                        </div>

                                        @error('custom_check_in_end') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        @error('custom_check_out_end') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                            </div>

                            {{-- Affected Scope --}}
                            <div class="pt-4 border-t border-slate-200">
                                <p class="text-sm font-semibold text-slate-700 mb-3">Berlaku Untuk (Opsional)</p>
                                <p class="text-xs text-slate-500 mb-3">Kosongkan jika berlaku untuk semua jurusan/kelas</p>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Jurusan</label>
                                        <select wire:model="affected_departments" multiple class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" size="4">
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-slate-500 mt-1">Tahan Ctrl/Cmd untuk pilih banyak</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                                        <select wire:model="affected_classes" multiple class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" size="4">
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-slate-500 mt-1">Tahan Ctrl/Cmd untuk pilih banyak</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button wire:click="closeModal" type="button" class="px-4 py-2 text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button wire:click="save" type="button" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        {{ $editMode ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
