<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'author',
        'description',
        'price',
        'shipping_price',
        'stock',
        'language',
        'cover_image',
        'status',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'shipping_price' => 'decimal:2',
        'stock' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($book) {
            if (empty($book->slug)) {
                $book->slug = Str::slug($book->title);
            }
        });

        static::updating(function ($book) {
            if ($book->isDirty('title')) {
                $book->slug = Str::slug($book->title);
            }
            
            // Auto set status to out_of_stock if stock is 0
            if ($book->stock <= 0 && $book->status === 'active') {
                $book->status = 'out_of_stock';
            }
        });
    }

    /**
     * Get the category that owns the book.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope a query to only include active books.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include books in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to search books by title or author.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
        });
    }

    /**
     * Get the status badge for display.
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>',
            'inactive' => '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>',
            'out_of_stock' => '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Out of Stock</span>',
        ];

        return $badges[$this->status] ?? $badges['inactive'];
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return '₹' . number_format($this->price, 2);
    }

    /**
     * Get the formatted shipping price.
     */
    public function getFormattedShippingPriceAttribute()
    {
        return $this->shipping_price > 0 ? '₹' . number_format($this->shipping_price, 2) : 'Free';
    }

    /**
     * Get the total price including shipping.
     */
    public function getTotalPriceAttribute()
    {
        return $this->price + $this->shipping_price;
    }

    /**
     * Get the formatted total price.
     */
    public function getFormattedTotalPriceAttribute()
    {
        return '₹' . number_format($this->total_price, 2);
    }

    /**
     * Check if the book is available.
     */
    public function getIsAvailableAttribute()
    {
        return $this->status === 'active' && $this->stock > 0;
    }

    /**
     * Get the stock status for display.
     */
    public function getStockStatusAttribute()
    {
        if ($this->stock <= 0) {
            return 'Out of Stock';
        } elseif ($this->stock <= 5) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    /**
     * Get the stock status color.
     */
    public function getStockStatusColorAttribute()
    {
        if ($this->stock <= 0) {
            return 'text-red-600';
        } elseif ($this->stock <= 5) {
            return 'text-yellow-600';
        } else {
            return 'text-green-600';
        }
    }

    /**
     * Get the cover image URL or placeholder.
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image && file_exists(public_path('storage/' . $this->cover_image))) {
            return asset('storage/' . $this->cover_image);
        }
        
        return 'https://via.placeholder.com/300x400/4F46E5/FFFFFF?text=' . urlencode($this->title);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}