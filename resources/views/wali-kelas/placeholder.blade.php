<x-wali-kelas-layout :title="$title">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-yellow-800">{{ $title }}</h3>
                <p class="mt-2 text-sm text-yellow-700">
                    Fitur ini sedang dalam pengembangan.
                </p>
            </div>
        </div>
    </div>
</x-wali-kelas-layout>
