<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'facility_type',
        'capacity',
        'description',
        'terms',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    /**
     * Get all bookings for this facility.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(FacilityBooking::class);
    }

    /**
     * Get pending bookings.
     */
    public function pendingBookings(): HasMany
    {
        return $this->hasMany(FacilityBooking::class)->where('status', 'pending');
    }

    /**
     * Scope active facilities.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the image URL accessor.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) return null;
        return str_starts_with($this->image_path, 'http')
            ? $this->image_path
            : asset('storage/' . $this->image_path);
    }

    /**
     * Get human-readable facility type label.
     */
    public function getFacilityTypeLabelAttribute(): string
    {
        return match ($this->facility_type) {
            'room' => 'Ruangan',
            'equipment' => 'Peralatan',
            default => 'Fasilitas',
        };
    }

    /**
     * Check if facility is available for a given period.
     */
    public function isAvailable(string $startTime, string $endTime, ?int $excludeBookingId = null): bool
    {
        $query = $this->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($inner) use ($startTime, $endTime) {
                    $inner->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->count() === 0;
    }
}
