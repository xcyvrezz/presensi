<div>
    <div class="max-w-2xl mx-auto text-center py-12">
        <svg class="mx-auto h-24 w-24 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <h2 class="mt-6 text-3xl font-bold text-gray-900">Profil Saya</h2>
        <p class="mt-4 text-lg text-gray-600">
            Fitur manajemen profil sedang dalam pengembangan.
        </p>
        <p class="mt-2 text-sm text-gray-500">
            Halaman ini akan memungkinkan Anda melihat dan mengedit informasi profil, mengganti password, dan upload foto.
        </p>
        <div class="mt-8 bg-gray-50 rounded-lg p-6 text-left">
            <h3 class="font-semibold text-gray-900 mb-4">Informasi Sementara:</h3>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Nama:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $student->full_name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">NIS:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $student->nis }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Kelas:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $student->class->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Jurusan:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $student->class->department->name }}</dd>
                </div>
            </dl>
        </div>
        <div class="mt-8">
            <a href="{{ route('student.dashboard') }}"
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
