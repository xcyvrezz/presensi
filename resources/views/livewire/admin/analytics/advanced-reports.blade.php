<div>
    <!-- Page Header -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Analitik & Laporan Lanjutan</h1>
                <p class="text-sm text-slate-600 mt-1">Analisis kehadiran siswa dan peringkat kelas</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="exportExcel" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-600 hover:bg-slate-700 text-white rounded-xl flex items-center gap-2 transition-all shadow-sm hover:shadow-md font-medium text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span wire:loading.remove wire:target="exportExcel" class="hidden sm:inline">Export Excel</span>
                    <span wire:loading.remove wire:target="exportExcel" class="sm:hidden">Excel</span>
                    <span wire:loading wire:target="exportExcel">...</span>
                </button>
                <button wire:click="exportPdf" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-700 hover:bg-slate-800 text-white rounded-xl flex items-center gap-2 transition-all shadow-sm hover:shadow-md font-medium text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span wire:loading.remove wire:target="exportPdf" class="hidden sm:inline">Export PDF</span>
                    <span wire:loading.remove wire:target="exportPdf" class="sm:hidden">PDF</span>
                    <span wire:loading wire:target="exportPdf">...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Filter Data</h3>
                <p class="text-sm text-slate-600">Pilih periode dan filter untuk melihat analitik</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Semester -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Semester</label>
                <select wire:model.live="selectedSemester" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}">
                            {{ $sem->name }} ({{ $sem->academic_year }})
                            @if($sem->is_active) ★ @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Department Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Jurusan</label>
                <select wire:model.live="selectedDepartment" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">Semua Jurusan</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->code }} - {{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Class Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Kelas</label>
                <select wire:model.live="selectedClass" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" @if(!$selectedDepartment) disabled @endif>
                    <option value="">{{ $selectedDepartment ? 'Semua Kelas' : 'Pilih Jurusan Dulu' }}</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Student Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Siswa (Opsional)</label>
                <select wire:model.live="selectedStudent" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" @if(!$selectedClass) disabled @endif>
                    <option value="">{{ $selectedClass ? 'Semua Siswa' : 'Pilih Kelas Dulu' }}</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->nis }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- General Statistics -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Statistik Umum</h3>
                <p class="text-sm text-slate-600">Ringkasan data kehadiran periode ini</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <!-- Total Students -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p class="text-xs font-semibold text-blue-700 uppercase">Total Siswa</p>
                </div>
                <p class="text-2xl font-bold text-blue-900">{{ $statistics['total_students'] ?? 0 }}</p>
            </div>

            <!-- Working Days -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-xs font-semibold text-purple-700 uppercase">Hari Efektif</p>
                </div>
                <p class="text-2xl font-bold text-purple-900">{{ $statistics['working_days'] ?? 0 }}</p>
            </div>

            <!-- Hadir -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs font-semibold text-green-700 uppercase">Hadir</p>
                </div>
                <p class="text-2xl font-bold text-green-900">{{ $statistics['hadir'] ?? 0 }}</p>
            </div>

            <!-- Terlambat -->
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-4 border border-yellow-200">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs font-semibold text-yellow-700 uppercase">Terlambat</p>
                </div>
                <p class="text-2xl font-bold text-yellow-900">{{ $statistics['terlambat'] ?? 0 }}</p>
            </div>

            <!-- Alpha -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs font-semibold text-red-700 uppercase">Alpha</p>
                </div>
                <p class="text-2xl font-bold text-red-900">{{ $statistics['alpha'] ?? 0 }}</p>
            </div>

            <!-- Attendance Rate -->
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-4 border border-indigo-200">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <p class="text-xs font-semibold text-indigo-700 uppercase">Tingkat Hadir</p>
                </div>
                <p class="text-2xl font-bold text-indigo-900">{{ $statistics['attendance_rate'] ?? 0 }}%</p>
            </div>
        </div>
    </div>

    <!-- Student Rankings -->
    @if(!$selectedStudent)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Top Diligent Students -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Siswa Paling Rajin</h3>
                    <p class="text-xs text-slate-600">Top 10 kehadiran terbaik</p>
                </div>
            </div>
            <div class="space-y-2">
                @forelse($topDiligentStudents as $index => $student)
                <div class="flex items-center gap-3 p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex-shrink-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $student->full_name }}</p>
                        <p class="text-xs text-slate-600">{{ $student->class->name ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-green-700">{{ $student->attendance_rate }}%</p>
                        <p class="text-xs text-slate-600">{{ $student->hadir_count }} hadir</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-500 text-center py-4">Tidak ada data</p>
                @endforelse
            </div>
        </div>

        <!-- Top Alpha Students -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Siswa Banyak Alpha</h3>
                    <p class="text-xs text-slate-600">Top 10 alpha terbanyak</p>
                </div>
            </div>
            <div class="space-y-2">
                @forelse($topAlphaStudents as $index => $student)
                <div class="flex items-center gap-3 p-3 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex-shrink-0 w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $student->full_name }}</p>
                        <p class="text-xs text-slate-600">{{ $student->class->name ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-red-700">{{ $student->alpha_count }}x</p>
                        <p class="text-xs text-slate-600">alpha</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-500 text-center py-4">Tidak ada data</p>
                @endforelse
            </div>
        </div>

        <!-- Top Late Students -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Siswa Sering Terlambat</h3>
                    <p class="text-xs text-slate-600">Top 10 terlambat terbanyak</p>
                </div>
            </div>
            <div class="space-y-2">
                @forelse($topLateStudents as $index => $student)
                <div class="flex items-center gap-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                    <div class="flex-shrink-0 w-8 h-8 bg-yellow-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $student->full_name }}</p>
                        <p class="text-xs text-slate-600">{{ $student->class->name ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-yellow-700">{{ $student->late_count }}x</p>
                        <p class="text-xs text-slate-600">terlambat</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-500 text-center py-4">Tidak ada data</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    <!-- Class Rankings -->
    @if(!$selectedClass && !$selectedStudent)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Best Classes -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Kelas Terbaik</h3>
                    <p class="text-xs text-slate-600">Top 5 kehadiran tertinggi</p>
                </div>
            </div>
            <div class="space-y-2">
                @forelse($bestClasses as $index => $classData)
                <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $classData['class']->name }}</p>
                        <p class="text-xs text-slate-600">{{ $classData['total_students'] }} siswa</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-blue-700">{{ $classData['rate'] }}%</p>
                        <p class="text-xs text-slate-600">kehadiran</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-500 text-center py-4">Tidak ada data</p>
                @endforelse
            </div>
        </div>

        <!-- Classes Need Attention -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Kelas Perlu Perhatian</h3>
                    <p class="text-xs text-slate-600">Top 5 kehadiran terendah</p>
                </div>
            </div>
            <div class="space-y-2">
                @forelse($attentionClasses as $index => $classData)
                <div class="flex items-center gap-3 p-3 bg-orange-50 rounded-lg border border-orange-200">
                    <div class="flex-shrink-0 w-8 h-8 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $classData['class']->name }}</p>
                        <p class="text-xs text-slate-600">{{ $classData['alpha_count'] }} alpha, {{ $classData['late_count'] }} terlambat</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-orange-700">{{ $classData['rate'] }}%</p>
                        <p class="text-xs text-slate-600">kehadiran</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-500 text-center py-4">Tidak ada data</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    <!-- Individual Student Detail -->
    @if($selectedStudent && $studentDetail)
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Detail Kehadiran Siswa</h3>
                <p class="text-sm text-slate-600">{{ $studentDetail['student']->full_name }} - {{ $studentDetail['student']->nis }}</p>
            </div>
        </div>

        <!-- Student Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                <p class="text-xs font-semibold text-slate-600 uppercase mb-1">Kelas</p>
                <p class="text-base font-bold text-slate-900">{{ $studentDetail['student']->class->name ?? '-' }}</p>
            </div>
            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                <p class="text-xs font-semibold text-slate-600 uppercase mb-1">Jurusan</p>
                <p class="text-base font-bold text-slate-900">{{ $studentDetail['student']->class->department->name ?? '-' }}</p>
            </div>
            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                <p class="text-xs font-semibold text-slate-600 uppercase mb-1">Hari Efektif</p>
                <p class="text-base font-bold text-slate-900">{{ $studentDetail['working_days'] }} hari</p>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">
            <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                <p class="text-xs font-semibold text-green-700 uppercase mb-1">Hadir</p>
                <p class="text-xl font-bold text-green-900">{{ $studentDetail['hadir'] }}</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
                <p class="text-xs font-semibold text-yellow-700 uppercase mb-1">Terlambat</p>
                <p class="text-xl font-bold text-yellow-900">{{ $studentDetail['terlambat'] }}</p>
            </div>
            <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                <p class="text-xs font-semibold text-blue-700 uppercase mb-1">Izin</p>
                <p class="text-xl font-bold text-blue-900">{{ $studentDetail['izin'] }}</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-3 border border-purple-200">
                <p class="text-xs font-semibold text-purple-700 uppercase mb-1">Sakit</p>
                <p class="text-xl font-bold text-purple-900">{{ $studentDetail['sakit'] }}</p>
            </div>
            <div class="bg-cyan-50 rounded-lg p-3 border border-cyan-200">
                <p class="text-xs font-semibold text-cyan-700 uppercase mb-1">Dispensasi</p>
                <p class="text-xl font-bold text-cyan-900">{{ $studentDetail['dispensasi'] }}</p>
            </div>
            <div class="bg-red-50 rounded-lg p-3 border border-red-200">
                <p class="text-xs font-semibold text-red-700 uppercase mb-1">Bolos</p>
                <p class="text-xl font-bold text-red-900">{{ $studentDetail['bolos'] }}</p>
            </div>
            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                <p class="text-xs font-semibold text-slate-700 uppercase mb-1">Alpha</p>
                <p class="text-xl font-bold text-slate-900">{{ $studentDetail['alpha'] }}</p>
            </div>
            <div class="bg-indigo-50 rounded-lg p-3 border border-indigo-200">
                <p class="text-xs font-semibold text-indigo-700 uppercase mb-1">% Hadir</p>
                <p class="text-xl font-bold text-indigo-900">{{ $studentDetail['attendance_rate'] }}%</p>
            </div>
        </div>

        <!-- Recent Attendances -->
        <div>
            <h4 class="text-sm font-bold text-slate-900 mb-3">10 Absensi Terakhir</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-700 uppercase">Tanggal</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-700 uppercase">Check In</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-700 uppercase">Check Out</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-700 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @forelse($studentDetail['recent_attendances'] as $attendance)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2 text-sm text-slate-900">{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm text-slate-900">{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}</td>
                            <td class="px-4 py-2 text-sm text-slate-900">{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-lg
                                    @if($attendance->status === 'hadir') bg-green-100 text-green-700
                                    @elseif($attendance->status === 'terlambat') bg-yellow-100 text-yellow-700
                                    @elseif($attendance->status === 'alpha') bg-slate-100 text-slate-700
                                    @else bg-blue-100 text-blue-700
                                    @endif">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Tidak ada data absensi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
