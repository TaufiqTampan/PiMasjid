<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodBarnRequest extends Model
{
    protected $fillable = [
        'food_barn_program_id',
        'name',
        'phone',
        'address',
        'family_members',
        'reason',
        'status',
    ];

    /**
     * Get the program this request belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(FoodBarnProgram::class, 'food_barn_program_id');
    }
}
