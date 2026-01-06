<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    // No soft deletes for audit logs - they should never be deleted

    protected $fillable = [
        'user_id',
        'user_type',
        'ip_address',
        'user_agent',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'url',
        'tags',
        'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'tags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model (polymorphic)
     */
    public function auditable()
    {
        if (!$this->auditable_type || !$this->auditable_id) {
            return null;
        }

        $modelClass = "App\\Models\\" . $this->auditable_type;

        if (!class_exists($modelClass)) {
            return null;
        }

        return $modelClass::withTrashed()->find($this->auditable_id);
    }

    /**
     * Get changed attributes
     */
    public function getChangedAttributesAttribute(): array
    {
        if ($this->event === 'created') {
            return array_keys($this->new_values ?? []);
        }

        if ($this->event === 'deleted') {
            return array_keys($this->old_values ?? []);
        }

        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        return array_keys(array_merge($old, $new));
    }

    /**
     * Scope: filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: filter by auditable type
     */
    public function scopeByAuditableType($query, string $type)
    {
        return $query->where('auditable_type', $type);
    }

    /**
     * Scope: filter by event
     */
    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope: filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: filter by tag
     */
    public function scopeByTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope: recent logs
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
