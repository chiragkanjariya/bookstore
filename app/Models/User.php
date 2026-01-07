<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'state_id',
        'district_id',
        'taluka_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Check if the user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Get the user's role name
     */
    public function getRoleName(): string
    {
        return ucfirst($this->role);
    }

    /**
     * Get the state that the user belongs to
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the district that the user belongs to
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the taluka that the user belongs to
     */
    public function taluka()
    {
        return $this->belongsTo(Taluka::class);
    }

    /**
     * Get the user's cart items.
     */
    public function cartItems()
    {
        return $this->hasMany(\App\Models\CartItem::class);
    }

    /**
     * Get the user's wishlist items.
     */
    public function wishlists()
    {
        return $this->hasMany(\App\Models\Wishlist::class);
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    /**
     * Get the total number of items in cart.
     */
    public function getCartCountAttribute(): int
    {
        return $this->cartItems()->sum('quantity');
    }

    /**
     * Get the total cart value.
     */
    public function getCartTotalAttribute(): float
    {
        return $this->cartItems()->with('book')->get()->sum('subtotal');
    }

    /**
     * Get the wishlist count.
     */
    public function getWishlistCountAttribute(): int
    {
        return $this->wishlists()->count();
    }

    /**
     * Check if a book is in the user's cart.
     */
    public function hasInCart(\App\Models\Book $book): bool
    {
        return $this->cartItems()->where('book_id', $book->id)->exists();
    }

    /**
     * Check if a book is in the user's wishlist.
     */
    public function hasInWishlist(\App\Models\Book $book): bool
    {
        return $this->wishlists()->where('book_id', $book->id)->exists();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $emailService = new \App\Services\EmailService();
        $emailService->sendPasswordResetEmail($this, $token);
    }
}
