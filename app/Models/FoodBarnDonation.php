<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodBarnDonation extends Model
{
    protected $fillable = [
        'food_barn_program_id',
        'donor_name',
        'donor_phone',
        'donation_type',
        'amount',
        'items',
        'status',
        'proof_url',
    ];

    /**
     * Get the program this donation belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(FoodBarnProgram::class, 'food_barn_program_id');
    }

    /**
     * Format proof_url accessor.
     */
    public function getProofUrlAttribute($value)
    {
        if (! $value) {
            return null;
        }

        return str_starts_with($value, 'http')
            ? $value
            : asset('storage/'.$value);
    }
}
