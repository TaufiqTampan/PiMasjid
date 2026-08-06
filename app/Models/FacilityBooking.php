<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityBooking extends Model
{
    protected $fillable = [
        'facility_id',
        'booking_code',
        'borrower_name',
        'borrower_phone',
        'borrower_address',
        'event_name',
        'event_description',
        'start_time',
        'end_time',
        'status',
        'admin_notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the facility for this booking.
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Get the user who approved the booking.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope pending bookings.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope approved bookings.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    /**
     * Get formatted duration.
     */
    public function getDurationAttribute(): string
    {
        if (!$this->start_time || !$this->end_time) return '-';
        $diff = $this->start_time->diff($this->end_time);
        $hours = $diff->h + ($diff->days * 24);
        $minutes = $diff->i;
        if ($minutes > 0) {
            return "{$hours} jam {$minutes} menit";
        }
        return "{$hours} jam";
    }

    /**
     * Generate unique booking code.
     */
    public static function generateBookingCode(): string
    {
        do {
            $code = 'BK-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (static::where('booking_code', $code)->exists());

        return $code;
    }
}
