<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceApproval extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'manual_attendance_id',
        'attendance_id',
        'approval_type',
        'request_data',
        'request_reason',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'status',
        'approval_notes',
        'approval_level',
        'requires_multi_approval',
    ];

    protected $casts = [
        'request_data' => 'array',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'approval_level' => 'integer',
        'requires_multi_approval' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the manual attendance record
     */
    public function manualAttendance()
    {
        return $this->belongsTo(ManualAttendance::class);
    }

    /**
     * Get the attendance record
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the user who requested approval
     */
    public function requestor()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved/rejected
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Scope: pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: rejected
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope: filter by approval type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('approval_type', $type);
    }

    /**
     * Scope: filter by requestor
     */
    public function scopeByRequestor($query, int $userId)
    {
        return $query->where('requested_by', $userId);
    }

    /**
     * Scope: filter by approver
     */
    public function scopeByApprover($query, int $userId)
    {
        return $query->where('approved_by', $userId);
    }

    /**
     * Scope: requiring multi-level approval
     */
    public function scopeMultiLevel($query)
    {
        return $query->where('requires_multi_approval', true);
    }
}
