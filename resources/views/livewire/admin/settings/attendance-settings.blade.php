<div x-data="{ activeTab: @entangle('activeTab') }">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Pengaturan Sistem Absensi</h1>
                <p class="text-gray-600 mt-1">Konfigurasi sistem absensi, geofencing, dan notifikasi</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="resetToDefaults"
                        wire:confirm="Yakin ingin mereset semua pengaturan ke default?"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset Default
                </button>
                <button wire:click="save"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span wire:loading.remove>Simpan Perubahan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
                <button @click="$wire.switchTab('time_windows')"
                        :class="activeTab === 'time_windows' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                    ⏰ Waktu Absensi
                </button>
                <button @click="$wire.switchTab('geofencing')"
                        :class="activeTab === 'geofencing' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                    📍 Geofencing
                </button>
                <button @click="$wire.switchTab('violations')"
                        :class="activeTab === 'violations' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                    ⚠️ Pelanggaran
                </button>
                <button @click="$wire.switchTab('manual_attendance')"
                        :class="activeTab === 'manual_attendance' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                    ✍️ Absensi Manual
                </button>
                <button @click="$wire.switchTab('notifications')"
                        :class="activeTab === 'notifications' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                    🔔 Notifikasi
                </button>
            </nav>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        @foreach($groupedSettings as $group => $groupSettings)
            <div x-show="activeTab === '{{ $group }}'" style="display: none;">
                <div class="space-y-6">
                    @foreach($groupSettings as $setting)
                        <div class="border-b border-gray-200 pb-6 last:border-0">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-900">
                                        {{ $setting->label }}
                                    </label>
                                    @if($setting->description)
                                        <p class="text-sm text-gray-500 mt-1">{{ $setting->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-2">
                                @if($setting->value_type === 'boolean')
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               wire:model="settings.{{ $group }}.{{ $setting->key }}"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Aktifkan</span>
                                    </label>

                                @elseif($setting->value_type === 'time')
                                    <input type="time"
                                           wire:model="settings.{{ $group }}.{{ $setting->key }}"
                                           class="w-48 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                                @elseif($setting->value_type === 'integer')
                                    <input type="number"
                                           wire:model="settings.{{ $group }}.{{ $setting->key }}"
                                           class="w-48 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           min="0">

                                @else
                                    <input type="text"
                                           wire:model="settings.{{ $group }}.{{ $setting->key }}"
                                           class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @endif
                            </div>

                            @if($setting->default_value)
                                <p class="text-xs text-gray-400 mt-1">
                                    Default: {{ $setting->default_value }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
