<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all users with this role
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all permissions for this role
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    /**
     * Check if role has specific permission
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()
            ->where('name', $permissionName)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if role has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()
            ->whereIn('name', $permissions)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Scope: only active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
