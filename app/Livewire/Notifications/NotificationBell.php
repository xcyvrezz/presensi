<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use App\Services\NotificationService;
use Livewire\Component;

class NotificationBell extends Component
{
    public $showDropdown = false;
    public $unreadCount = 0;

    protected $listeners = ['notificationCreated' => 'refreshNotifications'];

    public function mount()
    {
        $this->refreshNotifications();
    }

    public function refreshNotifications()
    {
        $this->unreadCount = NotificationService::getUnreadCount(auth()->user());
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);

        if ($notification && $notification->user_id === auth()->id()) {
            $notification->markAsRead();
            $this->refreshNotifications();

            // Redirect to action URL if exists
            if ($notification->action_url) {
                return redirect($notification->action_url);
            }
        }
    }

    public function markAllAsRead()
    {
        NotificationService::markAllAsRead(auth()->user());
        $this->refreshNotifications();
        session()->flash('success', 'Semua notifikasi ditandai sebagai dibaca');
    }

    public function deleteNotification($notificationId)
    {
        $notification = Notification::find($notificationId);

        if ($notification && $notification->user_id === auth()->id()) {
            $notification->delete();
            $this->refreshNotifications();
        }
    }

    public function render()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.notifications.notification-bell', [
            'notifications' => $notifications,
        ]);
    }
}
