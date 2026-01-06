<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'priority',
        'data',
        'related_type',
        'related_id',
        'is_read',
        'read_at',
        'sent_push',
        'sent_email',
        'sent_at',
        'action_url',
        'action_taken',
        'action_taken_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'sent_push' => 'boolean',
        'sent_email' => 'boolean',
        'sent_at' => 'datetime',
        'action_taken' => 'boolean',
        'action_taken_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user this notification belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model (polymorphic)
     */
    public function related()
    {
        if (!$this->related_type || !$this->related_id) {
            return null;
        }

        $modelClass = "App\\Models\\" . $this->related_type;

        if (!class_exists($modelClass)) {
            return null;
        }

        return $modelClass::find($this->related_id);
    }

    /**
     * Mark as read
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }

        $this->is_read = true;
        $this->read_at = now();

        return $this->save();
    }

    /**
     * Mark action as taken
     */
    public function markActionTaken(): bool
    {
        if ($this->action_taken) {
            return true;
        }

        $this->action_taken = true;
        $this->action_taken_at = now();

        return $this->save();
    }

    /**
     * Scope: unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope: filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: filter by priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: urgent notifications
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Scope: filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: recent notifications
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: action pending
     */
    public function scopeActionPending($query)
    {
        return $query->where('action_taken', false)
            ->whereNotNull('action_url');
    }

    /**
     * Get icon SVG path based on notification type
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'attendance_reminder' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'violation_warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            'approval_request' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'approval_result' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'report_ready' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'system_alert' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            default => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        };
    }

    /**
     * Get badge color based on priority
     */
    public function getBadgeColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'bg-red-500',
            'high' => 'bg-orange-500',
            'normal' => 'bg-blue-500',
            'low' => 'bg-gray-500',
            default => 'bg-blue-500',
        };
    }

    /**
     * Get text color based on priority
     */
    public function getTextColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'text-red-600',
            'high' => 'text-orange-600',
            'normal' => 'text-blue-600',
            'low' => 'text-gray-600',
            default => 'text-blue-600',
        };
    }
}
