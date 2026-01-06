<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'key',
        'group',
        'label',
        'description',
        'value_type',
        'value',
        'default_value',
        'validation_rules',
        'is_editable',
        'display_order',
        'last_modified_by',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'is_editable' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who last modified this setting
     */
    public function lastModifier()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    /**
     * Get value with proper type casting
     */
    public function getTypedValue()
    {
        return match($this->value_type) {
            'integer' => (int) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            'time' => $this->value, // Return as string (HH:mm:ss format)
            default => $this->value,
        };
    }

    /**
     * Get setting by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->getTypedValue();
    }

    /**
     * Set setting value
     */
    public static function setValue(string $key, $value, ?int $userId = null): bool
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        $setting->value = is_array($value) ? json_encode($value) : $value;
        $setting->last_modified_by = $userId;

        return $setting->save();
    }

    /**
     * Scope: filter by group
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope: only editable settings
     */
    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    /**
     * Scope: order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('key');
    }
}
