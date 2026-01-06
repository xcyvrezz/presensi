<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2563eb">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Absensi MIFARE' }}</title>

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/build/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icon-192x192.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Left: Logo & App Name -->
                    <div class="flex items-center">
                        <a href="{{ route(auth()->user()->isStudent() ? 'siswa.dashboard' : 'admin.dashboard') }}" class="flex items-center">
                            <div class="h-10 w-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">Absensi MIFARE</div>
                                <div class="text-xs text-gray-500">SMK Negeri 10 Pandeglang</div>
                            </div>
                        </a>
                    </div>

                    <!-- Right: User Menu -->
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-500">{{ auth()->user()->role->display_name }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
