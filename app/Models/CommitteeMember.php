<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    protected $fillable = [
        'name',
        'position',
        'division',
        'photo_path',
        'cloudinary_public_id',
        'order',
        'is_active',
    ];

    protected $appends = ['photo_url'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getPhotoUrlAttribute()
    {
        if (! $this->photo_path) {
            return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&size=200&background=059669&color=fff';
        }

        if (str_starts_with($this->photo_path, 'http')) {
            return $this->photo_path;
        }

        return asset('storage/'.$this->photo_path);
    }
}
