<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'image_path',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the book that owns the image.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path && file_exists(public_path('storage/' . $this->image_path))) {
            return asset('storage/' . $this->image_path);
        }
        
        return 'https://via.placeholder.com/300x400/4F46E5/FFFFFF?text=No+Image';
    }

    /**
     * Scope to get images ordered by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Scope to get primary image.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
