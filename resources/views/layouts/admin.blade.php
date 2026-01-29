<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <title>{{ $title ?? 'Dashboard Admin - Absensi MIFARE' }}</title>

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/build/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icon-192x192.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased">
    <!-- Offline Detector -->
    @livewire('components.offline-detector')

    <div class="min-h-screen" x-data="{ 
        sidebarOpen: false,
        manajemenOpen: true,
        absensiOpen: true
    }">
        <!-- Top Navigation -->
        <nav class="bg-white border-b border-slate-200 fixed w-full z-30 shadow-sm">
            <div class="px-6">
                <div class="flex justify-between h-16">
                    <!-- Left: Logo & Menu Toggle -->
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-800">Absensi MIFARE</div>
                                <div class="text-xs text-blue-600">Administrator</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: User Menu -->
                    <div class="flex items-center gap-6">
                        <!-- Notification Bell -->
                        @livewire('notifications.notification-bell')

                        <div class="hidden sm:flex items-center gap-3">
                            <div class="text-right">
                                <div class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-slate-500">{{ auth()->user()->role->display_name }}</div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 font-medium transition-colors">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex pt-16">
            <!-- Mobile Sidebar Overlay -->
            <div x-show="sidebarOpen"
                 @click="sidebarOpen = false"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 lg:hidden z-20"
                 style="display: none;"></div>

            <!-- Sidebar -->
            <aside
                class="fixed lg:sticky top-16 left-0 z-20 w-64 h-[calc(100vh-4rem)] bg-white border-r border-slate-200 overflow-y-auto
                       lg:translate-x-0 -translate-x-full transition-transform duration-300 ease-in-out"
                :class="{'translate-x-0': sidebarOpen}"
            >
                <nav class="px-3 py-6 space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Manajemen Section with Dropdown -->
                    <div class="pt-4">
                        <button @click="manajemenOpen = !manajemenOpen"
                                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-slate-600 hover:text-blue-600 uppercase tracking-wider transition-colors">
                            <span>Manajemen</span>
                            <svg class="h-4 w-4 transition-transform duration-200" :class="{'rotate-180': manajemenOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="manajemenOpen" 
                             x-collapse
                             class="mt-1 space-y-1">
                            <a href="{{ route('admin.users.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 pl-6 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span>Pengguna</span>
                            </a>
                            <a href="{{ route('admin.students.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 pl-6 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.students.*') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                                </svg>
                                <span>Siswa</span>
                            </a>
                            <a href="{{ route('admin.classes.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 pl-6 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.classes.*') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>Kelas</span>
                            </a>
                            <a href="{{ route('admin.departments.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 pl-6 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.departments.*') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>Jurusan</span>
                            </a>
                        </div>
                    </div>

                    <!-- Absensi Section with Dropdown -->
                    <div class="pt-4">
                        <button @click="absensiOpen = !absensiOpen"
                                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-slate-600 hover:text-blue-600 uppercase tracking-wider transition-colors">
                            <span>Absensi</span>
                            <svg class="h-4 w-4 transition-transform duration-200" :class="{'rotate-180': absensiOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="absensiOpen" 
                             x-collapse
                             class="mt-1 space-y-1">
                            <a href="{{ route('admin.attendance.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 pl-6 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.attendance.index') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                <span>Data Absensi</span>
                            </a>
                            <a href="{{ route('admin.attendance.manual') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 pl-6 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.attendance.manual') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>Input Manual</span>
                            </a>
                            <a href="{{ route('admin.attendance.mark-bolos') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 pl-6 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.attendance.mark-bolos') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Mark Bolos</span>
                            </a>
                            <a href="{{ route('admin.analytics') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 pl-6 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.analytics') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span>Analitik & Laporan</span>
                            </a>
                        </div>
                    </div>

                    <!-- Pengaturan Section -->
                    <div class="pt-4 pb-2">
                        <h3 class="px-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Pengaturan</h3>
                    </div>
                    <a href="{{ route('admin.settings.calendar') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.settings.calendar') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Kalender Akademik</span>
                    </a>
                    <a href="{{ route('admin.settings.semester') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.settings.semester') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Semester</span>
                    </a>
                    <a href="{{ route('admin.settings.attendance') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.settings.attendance') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <span>Pengaturan Absensi</span>
                    </a>

                    <!-- Sistem Section -->
                    <div class="pt-4 pb-2">
                        <h3 class="px-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Sistem</h3>
                    </div>
                    <a href="{{ route('admin.system.logs') }}"
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.system.logs') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Activity Logs</span>
                    </a>

                    <!-- Logout Button -->
                    <div class="pt-4 pb-2 border-t border-slate-200 mt-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 text-red-600 hover:bg-red-50">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 w-full overflow-x-hidden bg-slate-50">
                <div class="p-4 sm:p-6 max-w-7xl mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>