<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookStateShippingPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'state_id',
        'shipping_price',
    ];

    protected $casts = [
        'shipping_price' => 'decimal:2',
    ];

    /**
     * Get the book associated with this state shipping price.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the state associated with this shipping price.
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
