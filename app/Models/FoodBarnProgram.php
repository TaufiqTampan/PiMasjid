<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodBarnProgram extends Model
{
    protected $fillable = [
        'title',
        'description',
        'target_amount',
        'collected_amount',
        'distributed_amount',
        'status',
        'image_url',
    ];

    protected $appends = ['formatted_progress'];

    /**
     * Get the donations for this program.
     */
    public function donations(): HasMany
    {
        return $this->hasMany(FoodBarnDonation::class);
    }

    /**
     * Get the aid requests for this program.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(FoodBarnRequest::class);
    }

    /**
     * Format image_url accessor.
     */
    public function getImageUrlAttribute($value)
    {
        if (! $value) {
            return 'https://images.unsplash.com/photo-1593113598332-cd288d649433?q=80&w=800&auto=format&fit=crop';
        }

        return str_starts_with($value, 'http')
            ? $value
            : asset('storage/'.$value);
    }

    /**
     * Get formatted progress text.
     */
    public function getFormattedProgressAttribute(): string
    {
        if ($this->target_amount <= 0) {
            return '0%';
        }
        $pct = ($this->collected_amount / $this->target_amount) * 100;

        return round(min($pct, 100)).'%';
    }
}
