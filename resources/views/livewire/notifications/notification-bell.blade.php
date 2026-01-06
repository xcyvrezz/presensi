<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    <!-- Notification Bell Button -->
    <button @click="open = !open" class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        <!-- Unread Badge -->
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 flex h-5 w-5 items-center justify-center">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-5 w-5 bg-red-500 text-white text-xs font-bold items-center justify-center">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            </span>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
         style="display: none;">

        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">Notifikasi</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">
                    Tandai Semua Dibaca
                </button>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div wire:key="notification-{{ $notification->id }}"
                     class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors {{ !$notification->is_read ? 'bg-blue-50' : '' }}">
                    <div class="flex items-start gap-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-1">
                            <div class="h-10 w-10 rounded-full {{ $notification->badge_color }} flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $notification->icon }}"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900">{{ $notification->title }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-1">
                                    @if(!$notification->is_read)
                                        <button wire:click="markAsRead({{ $notification->id }})"
                                                class="p-1 text-blue-600 hover:bg-blue-100 rounded transition-colors"
                                                title="Tandai sebagai dibaca">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <button wire:click="deleteNotification({{ $notification->id }})"
                                            class="p-1 text-red-600 hover:bg-red-100 rounded transition-colors"
                                            title="Hapus">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Action Button -->
                            @if($notification->action_url && !$notification->is_read)
                                <a href="{{ $notification->action_url }}"
                                   wire:click="markAsRead({{ $notification->id }})"
                                   class="inline-block mt-2 text-xs text-blue-600 hover:text-blue-800 font-semibold">
                                    Lihat Detail →
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <svg class="h-12 w-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-gray-500 font-medium">Tidak ada notifikasi</p>
                    <p class="text-sm text-gray-400 mt-1">Anda akan menerima notifikasi di sini</p>
                </div>
            @endforelse
        </div>

        <!-- Footer - View All -->
        @if($notifications->count() > 0)
            <div class="p-3 border-t border-gray-200 text-center">
                <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-semibold">
                    Lihat Semua Notifikasi
                </a>
            </div>
        @endif
    </div>
</div>
