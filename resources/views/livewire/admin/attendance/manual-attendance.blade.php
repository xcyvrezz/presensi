<div>
    <!-- Page Header -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Input Absensi Manual</h1>
                <p class="text-sm sm:text-base text-slate-600 mt-1">Input manual untuk siswa yang Hadir, Terlambat, Izin, Sakit, atau Dispensasi</p>
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

    <!-- Status Message -->
    @if($statusMessage)
        <div class="mb-6 p-4 rounded-xl border-l-4
            {{ $statusType === 'success' ? 'bg-green-50 border-green-500' : '' }}
            {{ $statusType === 'error' ? 'bg-red-50 border-red-500' : '' }}">
            <p class="font-medium
                {{ $statusType === 'success' ? 'text-green-700' : '' }}
                {{ $statusType === 'error' ? 'text-red-700' : '' }}">
                {{ $statusMessage }}
            </p>
        </div>
    @endif

    <!-- Info Card -->
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-xl">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-blue-900 font-semibold mb-1">Panduan Input Manual</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• <strong>Hadir</strong>: Siswa hadir tepat waktu (Nilai: 100%)</li>
                    <li>• <strong>Terlambat</strong>: Siswa hadir terlambat (Nilai: 75%)</li>
                    <li>• <strong>Izin</strong>: Untuk urusan pribadi/keluarga (Nilai: 50%)</li>
                    <li>• <strong>Sakit</strong>: Siswa tidak hadir karena sakit (Nilai: 50%)</li>
                    <li>• <strong>Dispensasi</strong>: Izin resmi sekolah untuk lomba, tugas, dll (Nilai: 75%)</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form wire:submit.prevent="submitManualAttendance">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Department Select -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Jurusan <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="selectedDepartment"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->code }} - {{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Class Select -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Kelas <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="selectedClass"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            @if(!$selectedDepartment) disabled @endif>
                        <option value="">{{ $selectedDepartment ? '-- Pilih Kelas --' : '-- Pilih Jurusan Dulu --' }}</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Student Select with Search -->
                @if($inputMode === 'single')
                <div class="md:col-span-2" x-data="{ open: @entangle('showStudentDropdown') }">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Cari Siswa (NIS / Nama) <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text"
                                   wire:model.live.debounce.300ms="studentSearch"
                                   @focus="open = true"
                                   placeholder="Ketik NIS atau nama siswa (min 2 karakter)..."
                                   autocomplete="off"
                                   class="w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">

                            <!-- Search Icon -->
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            <!-- Clear Button -->
                            @if($studentSearch)
                                <button type="button"
                                        wire:click="clearStudentSearch"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg class="w-5 h-5 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif
                        </div>

                        <!-- Search Results Dropdown -->
                        @if($showStudentDropdown && count($studentSearchResults) > 0)
                            <div class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-64 overflow-y-auto">
                                <div class="p-2">
                                    <p class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        {{ count($studentSearchResults) }} siswa ditemukan
                                    </p>
                                    @foreach($studentSearchResults as $student)
                                        <button type="button"
                                                wire:click="selectStudent({{ $student->id }})"
                                                class="w-full text-left px-3 py-2.5 hover:bg-blue-50 rounded-lg transition-colors group">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <span class="text-sm font-bold text-blue-600">{{ substr($student->full_name, 0, 1) }}</span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $student->full_name }}</p>
                                                    <p class="text-xs text-slate-500">
                                                        NIS: {{ $student->nis }} • {{ $student->class->name }} ({{ $student->class->department->code }})
                                                    </p>
                                                </div>
                                                <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 opacity-0 group-hover:opacity-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @elseif($showStudentDropdown && $studentSearch && strlen($studentSearch) >= 2)
                            <div class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg p-4">
                                <div class="text-center text-sm text-slate-500">
                                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="font-medium">Siswa tidak ditemukan</p>
                                    <p class="text-xs mt-1">Coba kata kunci lain atau pilih kelas terlebih dahulu</p>
                                </div>
                            </div>
                        @endif

                        <!-- Selected Student Display -->
                        @if($selectedStudentData)
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-sm font-bold text-white">{{ substr($selectedStudentData->full_name, 0, 1) }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-slate-900">{{ $selectedStudentData->full_name }}</p>
                                        <p class="text-xs text-slate-600">
                                            NIS: {{ $selectedStudentData->nis }} • {{ $selectedStudentData->class->name }} ({{ $selectedStudentData->class->department->code }})
                                        </p>
                                    </div>
                                    <button type="button"
                                            wire:click="clearStudentSearch"
                                            class="text-slate-400 hover:text-slate-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    @error('selectedStudent')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <p class="mt-2 text-xs text-slate-500">
                        💡 Tip: Ketik minimal 2 karakter untuk mencari. Bisa cari berdasarkan NIS atau nama siswa.
                    </p>
                </div>
                @else
                <!-- Multiple Student Selection -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Pilih Siswa <span class="text-red-500">*</span>
                    </label>

                    @if(count($filteredStudents) > 0)
                        <!-- Select All Checkbox -->
                        <div class="mb-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox"
                                       wire:model.live="selectAll"
                                       wire:click="toggleSelectAll"
                                       class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                                <span class="ml-3 text-sm font-semibold text-slate-700 group-hover:text-blue-600">
                                    Pilih Semua ({{ count($filteredStudents) }} siswa)
                                </span>
                            </label>
                        </div>

                        <!-- Student List with Checkboxes -->
                        <div class="max-h-96 overflow-y-auto border border-slate-200 rounded-xl p-3 space-y-2">
                            @foreach($filteredStudents as $student)
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
                    @else
                        <div class="p-8 text-center border-2 border-dashed border-slate-200 rounded-xl">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <p class="text-sm font-medium text-slate-500">Pilih kelas terlebih dahulu</p>
                        </div>
                    @endif

                    @error('selectedStudents')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Date Select -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           wire:model="selectedDate"
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    @error('selectedDate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Select -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Status Absensi <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="selectedStatus"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="hadir">Hadir (100%)</option>
                        <option value="terlambat">Terlambat (75%)</option>
                        <option value="izin">Izin (50%)</option>
                        <option value="sakit">Sakit (50%)</option>
                        <option value="dispensasi">Dispensasi (75%)</option>
                    </select>
                    @error('selectedStatus')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Time Inputs (only shown for Hadir/Terlambat) -->
                @if(in_array($selectedStatus, ['hadir', 'terlambat']))
                <div class="md:col-span-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Check-in Time -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Waktu Check-In <span class="text-red-500">*</span>
                            </label>
                            <input type="time"
                                   wire:model="checkInTime"
                                   class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            @error('checkInTime')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Check-out Time -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Waktu Check-Out <span class="text-slate-400">(Opsional)</span>
                            </label>
                            <input type="time"
                                   wire:model="checkOutTime"
                                   class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            @error('checkOutTime')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">
                        💡 Sistem akan otomatis menghitung keterlambatan berdasarkan waktu check-in
                    </p>
                </div>
                @endif

                <!-- Reason Textarea (only shown for Izin/Sakit/Dispensasi) -->
                @if(!in_array($selectedStatus, ['hadir', 'terlambat']))
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Alasan/Keterangan <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="reason"
                              rows="4"
                              placeholder="Masukkan alasan atau keterangan lengkap..."
                              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"></textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-slate-500">Contoh: Sakit demam, ada keperluan keluarga, mengikuti lomba robotik, dll.</p>
                </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-end">
                <button type="button"
                        wire:click="resetForm"
                        class="px-6 py-2.5 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 transition-all font-medium">
                    Reset Form
                </button>
                <button type="submit"
                        :disabled="isProcessing"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all font-medium shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="submitManualAttendance">
                        @if($inputMode === 'single')
                            Simpan Absensi Manual
                        @else
                            Simpan untuk {{ count($selectedStudents) }} Siswa
                        @endif
                    </span>
                    <span wire:loading wire:target="submitManualAttendance">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Manual Attendance (Optional) -->
    <div class="mt-6 bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Absensi Manual Hari Ini</h3>
        <div class="text-sm text-slate-500">
            <p>Menampilkan riwayat absensi manual yang telah diinput hari ini...</p>
            <p class="mt-2 text-xs">(Fitur ini akan ditambahkan)</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-clear success/error messages after 5 seconds
    window.addEventListener('schedule-message-clear', () => {
        setTimeout(() => {
            @this.set('statusMessage', '');
            @this.set('statusType', '');
        }, 5000);
    });
</script>
@endpush
