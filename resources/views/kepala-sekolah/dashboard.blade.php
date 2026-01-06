<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kepala Sekolah - Absensi MIFARE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Dashboard Kepala Sekolah</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Selamat Datang, {{ auth()->user()->name }}!</h2>
                    <p class="text-gray-600 mb-6">Anda login sebagai <span class="font-semibold text-blue-600">Kepala Sekolah</span></p>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <div class="text-blue-600 text-sm font-medium">Total Users</div>
                            <div class="text-3xl font-bold text-blue-900 mt-2">{{ \App\Models\User::count() }}</div>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg">
                            <div class="text-green-600 text-sm font-medium">Total Siswa</div>
                            <div class="text-3xl font-bold text-green-900 mt-2">{{ \App\Models\Student::count() }}</div>
                        </div>
                        <div class="bg-purple-50 p-6 rounded-lg">
                            <div class="text-purple-600 text-sm font-medium">Total Kelas</div>
                            <div class="text-3xl font-bold text-purple-900 mt-2">{{ \App\Models\Classes::count() }}</div>
                        </div>
                        <div class="bg-orange-50 p-6 rounded-lg">
                            <div class="text-orange-600 text-sm font-medium">Jurusan</div>
                            <div class="text-3xl font-bold text-orange-900 mt-2">{{ \App\Models\Department::count() }}</div>
                        </div>
                    </div>

                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
                        <p class="font-bold">✓ Authentication berhasil!</p>
                        <p class="text-sm">Login system dengan role-based access control sudah berfungsi.</p>
                        <p class="text-sm mt-2">Next: Develop dashboard features & check-in/out components.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
