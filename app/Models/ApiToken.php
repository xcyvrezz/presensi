<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'token',
        'abilities',
        'last_used_at',
        'usage_count',
        'last_ip_address',
        'expires_at',
        'is_active',
        'rate_limit',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
    ];

    /**
     * Get the user that owns the token
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new token
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Check if token is valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if token has ability
     */
    public function hasAbility(string $ability): bool
    {
        if (!$this->abilities) {
            return false;
        }

        return in_array('*', $this->abilities) || in_array($ability, $this->abilities);
    }

    /**
     * Record token usage
     */
    public function recordUsage(?string $ipAddress = null)
    {
        $this->increment('usage_count');
        $this->update([
            'last_used_at' => now(),
            'last_ip_address' => $ipAddress,
        ]);
    }

    /**
     * Revoke token
     */
    public function revoke()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Scope: active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: expired tokens
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }
}
