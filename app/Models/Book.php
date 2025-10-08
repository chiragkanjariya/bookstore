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
        'isbn',
        'description',
        'price',
        'shipping_price',
        'height',
        'width',
        'depth',
        'weight',
        'stock',
        'language',
        'cover_image',
        'status',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'shipping_price' => 'decimal:2',
        'height' => 'decimal:2',
        'width' => 'decimal:2',
        'depth' => 'decimal:2',
        'weight' => 'decimal:2',
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
            
            // Auto set status to out_of_stock if stock is out_of_stock
            if ($book->stock === 'out_of_stock' && $book->status === 'active') {
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
     * Get the images for the book.
     */
    public function images()
    {
        return $this->hasMany(BookImage::class)->ordered();
    }

    /**
     * Get the primary image for the book.
     */
    public function primaryImage()
    {
        return $this->hasOne(BookImage::class)->primary();
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
     * Scope a query to search books by title, author, or ISBN.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
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
     * Get available stock options.
     */
    public static function getStockOptions()
    {
        return [
            'in_stock' => 'In Stock',
            'limited_stock' => 'Limited Stock',
            'out_of_stock' => 'Out of Stock',
        ];
    }

    /**
     * Get the stock status for display.
     */
    public function getStockStatusAttribute()
    {
        $options = self::getStockOptions();
        return $options[$this->stock] ?? 'Unknown';
    }

    /**
     * Get the stock status color.
     */
    public function getStockStatusColorAttribute()
    {
        $colors = [
            'in_stock' => 'text-green-600',
            'limited_stock' => 'text-yellow-600',
            'out_of_stock' => 'text-red-600',
        ];
        
        return $colors[$this->stock] ?? 'text-gray-600';
    }

    /**
     * Check if the book is available for purchase.
     */
    public function getIsAvailableAttribute()
    {
        return $this->status === 'active' && in_array($this->stock, ['in_stock', 'limited_stock']);
    }

    /**
     * Get the cover image URL or placeholder.
     */
    public function getCoverImageUrlAttribute()
    {
        // First check if there's a primary image
        $primaryImage = $this->primaryImage;
        if ($primaryImage) {
            return $primaryImage->image_url;
        }

        // Then check if there are any images
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->image_url;
        }

        // Fall back to the old cover_image field
        if ($this->cover_image && file_exists(public_path('storage/' . $this->cover_image))) {
            return asset('storage/' . $this->cover_image);
        }
        
        return 'https://via.placeholder.com/300x400/4F46E5/FFFFFF?text=' . urlencode($this->title);
    }

    /**
     * Get all image URLs for the book.
     */
    public function getAllImageUrlsAttribute()
    {
        $imageUrls = $this->images->pluck('image_url')->toArray();
        
        // If no images exist, fall back to cover_image
        if (empty($imageUrls) && $this->cover_image && file_exists(public_path('storage/' . $this->cover_image))) {
            $imageUrls[] = asset('storage/' . $this->cover_image);
        }
        
        // If still no images, return placeholder
        if (empty($imageUrls)) {
            $imageUrls[] = 'https://via.placeholder.com/300x400/4F46E5/FFFFFF?text=' . urlencode($this->title);
        }
        
        return $imageUrls;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}