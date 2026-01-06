<div x-data="{
    online: navigator.onLine,
    showBanner: false,
    init() {
        window.addEventListener('online', () => {
            this.online = true;
            this.showBanner = true;
            setTimeout(() => { this.showBanner = false; }, 3000);
        });
        window.addEventListener('offline', () => {
            this.online = false;
            this.showBanner = true;
        });
    }
}">
    <!-- Offline Banner -->
    <div x-show="showBanner && !online"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-full"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-full"
         class="fixed top-0 left-0 right-0 z-50 bg-red-600 text-white px-4 py-3 shadow-lg"
         style="display: none;">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"/>
                </svg>
                <div>
                    <p class="font-semibold">Tidak Ada Koneksi Internet</p>
                    <p class="text-sm text-red-100">Beberapa fitur mungkin tidak tersedia</p>
                </div>
            </div>
            <button @click="showBanner = false" class="text-white hover:text-red-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Online Banner -->
    <div x-show="showBanner && online"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-full"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-full"
         class="fixed top-0 left-0 right-0 z-50 bg-green-600 text-white px-4 py-3 shadow-lg"
         style="display: none;">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-semibold">Kembali Online</p>
                    <p class="text-sm text-green-100">Koneksi internet telah pulih</p>
                </div>
            </div>
            <button @click="showBanner = false" class="text-white hover:text-green-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Offline Indicator (bottom right) -->
    <div x-show="!online"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-full"
         x-transition:enter-end="opacity-100 transform translate-x-0"
         class="fixed bottom-4 right-4 z-40 bg-gray-800 text-white px-4 py-2 rounded-full shadow-lg flex items-center gap-2"
         style="display: none;">
        <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
        <span class="text-sm font-medium">Offline</span>
    </div>
</div>
