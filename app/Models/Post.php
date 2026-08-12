<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'image_path',
        'cloudinary_public_id',
        'author_id',
        'published_at',
        'is_published',
        'views',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    protected $appends = ['image_url', 'author_name'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        if (! $this->image_path) {
            return 'https://placehold.co/600x400/e2e8f0/1e293b?text=No+Image';
        }

        return str_starts_with($this->image_path, 'http')
            ? $this->image_path
            : asset('storage/'.$this->image_path);
    }

    public function getAuthorNameAttribute()
    {
        return $this->author->name ?? 'Admin';
    }

    // Mutators
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = Str::slug($value).'-'.uniqid();
        // Simple slug generation. For production, unique checking is better, but this suffices for now.
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true)->where('published_at', '<=', now());
    }
}
