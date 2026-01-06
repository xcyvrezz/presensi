<div>
    @if(!$class)
        <!-- No Class Assigned -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-yellow-800">Anda Belum Ditugaskan Sebagai Wali Kelas</h3>
                    <p class="mt-2 text-sm text-yellow-700">
                        Silakan hubungi administrator untuk ditugaskan sebagai wali kelas.
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Input Absensi Manual</h1>
                    <p class="text-slate-600 mt-1">{{ $class->name }} - {{ $class->department->name }}</p>
                </div>

                <!-- Mode Switcher -->
                <div class="inline-flex items-center bg-slate-100 rounded-xl p-1">
                    <button wire:click="switchMode('single')"
                            class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $inputMode === 'single' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Input Tunggal
                    </button>
                    <button wire:click="switchMode('multiple')"
                            class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $inputMode === 'multiple' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Input Multiple
                    </button>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-600 rounded-xl">
                <p class="text-sm font-medium text-blue-900">{{ session('success') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-600 rounded-xl">
                <p class="text-sm font-medium text-red-900">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Form Input -->
            <div>
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Form Absensi Manual</h2>

                    <form wire:submit.prevent="submit">
                        <!-- Tanggal -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal</label>
                            <input type="date" wire:model="selectedDate"
                                   class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            @error('selectedDate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Pilih Siswa -->
                        @if($inputMode === 'single')
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Siswa</label>
                            <select wire:model="selectedStudent"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->nis }} - {{ $student->full_name }}</option>
                                @endforeach
                            </select>
                            @error('selectedStudent')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @else
                        <!-- Multiple Student Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-3">
                                Pilih Siswa <span class="text-red-500">*</span>
                            </label>

                            <!-- Select All Checkbox -->
                            <div class="mb-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="checkbox"
                                           wire:model.live="selectAll"
                                           wire:click="toggleSelectAll"
                                           class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                                    <span class="ml-3 text-sm font-semibold text-slate-700 group-hover:text-blue-600">
                                        Pilih Semua ({{ count($students) }} siswa)
                                    </span>
                                </label>
                            </div>

                            <!-- Student List with Checkboxes -->
                            <div class="max-h-64 overflow-y-auto border border-slate-200 rounded-xl p-3 space-y-2">
                                @foreach($students as $student)
                                    <label class="flex items-center p-3 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer group border border-transparent hover:border-blue-200">
                                        <input type="checkbox"
                                               wire:model.live="selectedStudents"
                                               value="{{ $student->id }}"
                                               class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-blue-200">
                                                    <span class="text-xs font-bold text-blue-600">{{ substr($student->full_name, 0, 1) }}</span>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm font-semibold text-slate-900">{{ $student->full_name }}</p>
                                                    <p class="text-xs text-slate-500">NIS: {{ $student->nis }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <!-- Selected Count -->
                            @if(count($selectedStudents) > 0)
                                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p class="text-sm font-semibold text-blue-900">
                                        {{ count($selectedStudents) }} siswa dipilih
                                    </p>
                                </div>
                            @endif

                            @error('selectedStudents')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                            <select wire:model="status"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option value="hadir">Hadir</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpha">Alpha</option>
                                <option value="dispensasi">Dispensasi</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Waktu Check-In -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Waktu Check-In</label>
                            <input type="time" wire:model="checkInTime"
                                   class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            @error('checkInTime')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Waktu Check-Out (Opsional) -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Waktu Check-Out (Opsional)</label>
                            <input type="time" wire:model="checkOutTime"
                                   class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            @error('checkOutTime')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Catatan -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Catatan (Opsional)</label>
                            <textarea wire:model="notes"
                                      rows="3"
                                      placeholder="Tambahkan catatan jika diperlukan..."
                                      class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"></textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-300 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="submit">
                                @if($inputMode === 'single')
                                    Simpan Absensi
                                @else
                                    Simpan untuk {{ count($selectedStudents) }} Siswa
                                @endif
                            </span>
                            <span wire:loading wire:target="submit">Menyimpan...</span>
                        </button>
                    </form>
                </div>

                <!-- Info Box -->
                <div class="mt-6 bg-blue-50 border-l-4 border-blue-600 p-4 rounded-xl">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Catatan:</strong> Gunakan fitur ini untuk mencatat absensi siswa yang tidak membawa kartu, lupa tap, atau ada keperluan khusus lainnya.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Entries -->
            <div>
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Entry Manual Terbaru</h2>

                    <div class="space-y-3">
                        @forelse($recentEntries as $entry)
                            <div class="border border-slate-200 rounded-xl p-4 hover:shadow-md transition-all">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-medium text-slate-900">{{ $entry->student->full_name }}</h3>
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg
                                                @if($entry->status === 'hadir') bg-blue-50 text-blue-700
                                                @elseif($entry->status === 'terlambat') bg-slate-100 text-slate-700
                                                @elseif($entry->status === 'izin') bg-slate-100 text-slate-700
                                                @elseif($entry->status === 'sakit') bg-slate-100 text-slate-700
                                                @else bg-slate-100 text-slate-700
                                                @endif">
                                                {{ ucfirst($entry->status) }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-slate-600 space-y-1">
                                            <p><strong>NIS:</strong> {{ $entry->student->nis }}</p>
                                            <p><strong>Tanggal:</strong> {{ $entry->date ? \Carbon\Carbon::parse($entry->date)->format('d/m/Y') : '-' }}</p>
                                            <p><strong>Check-In:</strong> {{ $entry->check_in_time ? \Carbon\Carbon::parse($entry->check_in_time)->format('H:i') : '-' }}</p>
                                            @if($entry->check_out_time)
                                                <p><strong>Check-Out:</strong> {{ \Carbon\Carbon::parse($entry->check_out_time)->format('H:i') }}</p>
                                            @endif
                                            @if($entry->notes)
                                                <p class="mt-2 text-xs text-slate-500 italic">{{ $entry->notes }}</p>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-400 mt-2">
                                            Diinput: {{ $entry->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-slate-500">Belum ada entry manual</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
