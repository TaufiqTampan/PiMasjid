<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'speaker',
        'date',
        'time',
        'location',
        'image_path',
        'cloudinary_public_id',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_url'];

    // Accessors
    public function getImageUrlAttribute()
    {
        if (! $this->image_path) {
            return 'https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=600';
        }

        return str_starts_with($this->image_path, 'http')
            ? $this->image_path
            : asset('storage/'.$this->image_path);
    }
}
