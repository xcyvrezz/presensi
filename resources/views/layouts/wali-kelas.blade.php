<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <title>{{ $title ?? 'Dashboard Wali Kelas - Absensi MIFARE' }}</title>

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/build/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icon-192x192.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script>
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => console.log('SW registered:', registration))
                    .catch(error => console.log('SW registration failed:', error));
            });
        }
    </script>

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

    <div class="min-h-screen" x-data="{ sidebarOpen: false }">
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
                                <div class="text-xs text-blue-600">Wali Kelas</div>
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
                    <a href="{{ route('wali-kelas.dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('wali-kelas.dashboard') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Kelas Section -->
                    <div class="pt-4 pb-2">
                        <h3 class="px-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Kelas Saya</h3>
                    </div>
                    <a href="{{ route('wali-kelas.students') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('wali-kelas.students*') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>Daftar Siswa</span>
                    </a>
                    <a href="{{ route('wali-kelas.manual-attendance') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('wali-kelas.manual-attendance') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Absensi Manual</span>
                    </a>

                    <!-- Absensi Section -->
                    <div class="pt-4 pb-2">
                        <h3 class="px-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Absensi</h3>
                    </div>
                    <a href="{{ route('wali-kelas.attendance') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('wali-kelas.attendance') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span>Data Absensi</span>
                    </a>
                    <a href="{{ route('wali-kelas.absence-requests') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('wali-kelas.absence-requests') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Pengajuan Izin</span>
                    </a>
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