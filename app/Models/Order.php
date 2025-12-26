<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'payment_status',
        'payment_method',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'shiprocket_order_id',
        'shiprocket_shipment_id',
        'tracking_number',
        'courier_company',
        'courier_provider',
        'courier_document_ref',
        'courier_awb_number',
        'requires_manual_shipping',
        'manual_shipping_marked_at',
        'subtotal',
        'shipping_cost',
        'tax_amount',
        'total_amount',
        'is_bulk_purchased',
        'shipping_address',
        'billing_address',
        'notes',
        'confirmation_email_sent',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_bulk_purchased' => 'boolean',
        'confirmation_email_sent' => 'boolean',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items for the order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get the payment status badge color.
     */
    public function getPaymentStatusBadgeColorAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']) &&
            $this->payment_status !== 'paid';
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    /**
     * Get total quantity of items in the order.
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->orderItems()->sum('quantity');
    }

    /**
     * Check if order qualifies for bulk purchase.
     */
    public function qualifiesForBulkPurchase(): bool
    {
        $minBulkPurchase = \App\Models\Setting::get('min_bulk_purchase', 10);
        return $this->getTotalQuantityAttribute() >= $minBulkPurchase;
    }

    /**
     * Get bulk purchase badge color.
     */
    /**
     * Get bulk purchase badge color.
     */
    public function getBulkPurchaseBadgeColorAttribute(): string
    {
        return $this->is_bulk_purchased ? 'success' : 'secondary';
    }

    /**
     * Scope for manual shipping orders.
     */
    public function scopeRequiresManualShipping($query)
    {
        return $query->where('requires_manual_shipping', true);
    }

    /**
     * Scope for pending manual shipping orders.
     */
    public function scopePendingManualShipping($query)
    {
        return $query->where('requires_manual_shipping', true)
            ->whereNull('manual_shipping_marked_at');
    }

    /**
     * Check if order is marked as manually shipped.
     */
    public function isManuallyShipped(): bool
    {
        return $this->requires_manual_shipping && !is_null($this->manual_shipping_marked_at);
    }

    /**
     * Mark order as manually shipped.
     */
    public function markAsManuallyShipped(): bool
    {
        return $this->update([
            'manual_shipping_marked_at' => now(),
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);
    }
}
