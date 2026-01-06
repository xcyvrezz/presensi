<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
        'method',
        'url',
        'properties',
        'request_data',
        'category',
        'severity',
        'session_id',
    ];

    protected $casts = [
        'properties' => 'array',
        'request_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the activity
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject model (polymorphic-like)
     */
    public function subject()
    {
        if (!$this->subject_type || !$this->subject_id) {
            return null;
        }

        $modelClass = "App\\Models\\" . $this->subject_type;

        if (!class_exists($modelClass)) {
            return null;
        }

        return $modelClass::find($this->subject_id);
    }

    /**
     * Scope: filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: filter by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: filter by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: filter by severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope: recent activities
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: critical activities
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope: authentication activities
     */
    public function scopeAuthentication($query)
    {
        return $query->where('category', 'authentication');
    }

    /**
     * Get icon based on action
     */
    public function getIconAttribute(): string
    {
        return match($this->action) {
            'login' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
            'logout' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
            'create' => 'M12 4v16m8-8H4',
            'update' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'delete' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
            'export' => 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'view' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
            default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }

    /**
     * Get color based on severity
     */
    public function getColorClassAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'text-red-600 bg-red-50',
            'warning' => 'text-orange-600 bg-orange-50',
            'info' => 'text-blue-600 bg-blue-50',
            default => 'text-gray-600 bg-gray-50',
        };
    }

    /**
     * Get badge color based on severity
     */
    public function getBadgeColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'bg-red-500',
            'warning' => 'bg-orange-500',
            'info' => 'bg-blue-500',
            default => 'bg-gray-500',
        };
    }
}
