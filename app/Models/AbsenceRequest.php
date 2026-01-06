<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbsenceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'approved_by',
        'absence_date',
        'type',
        'reason',
        'document_path',
        'status',
        'rejection_reason',
        'approved_at',
    ];

    protected $casts = [
        'absence_date' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the student that made this absence request
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user (wali kelas/admin) who approved/rejected this request
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: only pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: only approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: only rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope: requests for a specific date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('absence_date', [$from, $to]);
    }

    /**
     * Check if this request is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if this request is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if this request is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Get the type label in Indonesian
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            default => $this->type,
        };
    }

    /**
     * Get the status label in Indonesian
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => $this->status,
        };
    }

    /**
     * Get the status color for UI
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }
}
