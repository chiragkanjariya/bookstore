<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Get the user that owns the cart item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book associated with the cart item.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the total price for this cart item.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->quantity * $this->price;
    }

    /**
     * Get the subtotal including shipping for this cart item.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->total_price + ($this->book->shipping_price * $this->quantity);
    }

    /**
     * Check if the requested quantity is available in stock.
     */
    public function isAvailable(): bool
    {
        return $this->book->stock >= $this->quantity;
    }

    /**
     * Get the maximum quantity available for this item.
     */
    public function getMaxQuantityAttribute(): int
    {
        return min($this->book->stock, 10); // Limit to 10 per item
    }
}