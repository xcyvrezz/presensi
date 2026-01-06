<?php

namespace App\Livewire\Components;

use App\Models\Notification;
use Livewire\Component;

class NotificationBell extends Component
{
    public $unreadCount = 0;
    public $notifications = [];
    public $showDropdown = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $this->unreadCount = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        if ($this->showDropdown) {
            $this->loadNotifications();
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification && $notification->user_id === auth()->id()) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.components.notification-bell');
    }
}
