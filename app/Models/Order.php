<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    // Order status constants
    const STATUS_PENDING_TO_BE_PREPARED = 'pending_to_be_prepared';
    const STATUS_READY_TO_SHIP = 'ready_to_ship';
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    // Shipping Partner Status
    const SHIPPING_PARTNER_PENDING = 'pending';
    const SHIPPING_PARTNER_APPROVED = 'approved';
    const SHIPPING_PARTNER_REJECTED = 'rejected';

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
        'awb_number',
        'requires_manual_shipping',
        'manual_courier_id',
        'manual_tracking_id',
        'manual_courier_name',
        'shipping_partner_status',
        'shipping_partner_error',
        'manual_shipping_marked_at',
        'subtotal',
        'shipping_cost',
        'maruti_shipping_rate',
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
        'maruti_shipping_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_bulk_purchased' => 'boolean',
        'confirmation_email_sent' => 'boolean',
        'manual_shipping_marked_at' => 'datetime',
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

            if (empty($order->shipping_partner_status)) {
                $order->shipping_partner_status = self::SHIPPING_PARTNER_PENDING;
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
     * Get the manual courier for the order.
     */
    public function manualCourier(): BelongsTo
    {
        return $this->belongsTo(ManualCourier::class);
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'pending_to_be_prepared' => 'warning',
            'ready_to_ship' => 'info',
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get display-friendly status label for Maruti orders.
     */
    public function getMarutiStatusLabelAttribute(): string
    {
        if (in_array($this->status, ['pending_to_be_prepared', 'ready_to_ship', 'pending', 'processing'])) {
            return 'Order Placed';
        }
        return ucfirst($this->status);
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
     * Check if order is marked as manually shipped (works for both manual and bulk orders).
     */
    public function isManuallyShipped(): bool
    {
        return !is_null($this->manual_shipping_marked_at);
    }

    /**
     * Mark order as manually shipped with tracking data.
     */
    public function markAsManuallyShipped($courierData = []): bool
    {
        $updateData = [
            'manual_shipping_marked_at' => now(),
            'status' => 'shipped',
            'shipped_at' => now(),
        ];

        if (!empty($courierData['manual_courier_id'])) {
            $updateData['manual_courier_id'] = $courierData['manual_courier_id'];
            $courier = ManualCourier::find($courierData['manual_courier_id']);
            if ($courier) {
                $updateData['manual_courier_name'] = $courier->name;
            }
        }

        if (!empty($courierData['manual_tracking_id'])) {
            $updateData['manual_tracking_id'] = $courierData['manual_tracking_id'];
        }

        return $this->update($updateData);
    }

    /**
     * Scope for pending to be prepared orders.
     */
    public function scopePendingToBePrepared($query)
    {
        return $query->where('status', self::STATUS_PENDING_TO_BE_PREPARED);
    }

    /**
     * Scope for ready to ship orders.
     */
    public function scopeReadyToShip($query)
    {
        return $query->where('status', self::STATUS_READY_TO_SHIP);
    }

    /**
     * Scope for Maruti (automatic) orders — non-manual, non-bulk.
     */
    public function scopeMarutiOrders($query)
    {
        return $query->where('requires_manual_shipping', false)
            ->where('is_bulk_purchased', false);
    }

    /**
     * Scope for bulk orders.
     */
    public function scopeBulkOrders($query)
    {
        return $query->where('is_bulk_purchased', true);
    }

    /**
     * Scope for manual orders (non-serviceable, non-bulk).
     */
    public function scopeManualOrders($query)
    {
        return $query->where('requires_manual_shipping', true)
            ->where('is_bulk_purchased', false);
    }

    /**
     * Get tracking URL for manual/bulk shipped order.
     */
    public function getManualTrackingUrlAttribute(): ?string
    {
        if (!$this->manual_tracking_id || !$this->manual_courier_id) {
            return null;
        }

        $courier = $this->manualCourier;
        if ($courier && $courier->tracking_url) {
            return $courier->tracking_url;
        }

        return null;
    }

    /**
     * Mark order as ready to ship.
     */
    public function markAsReadyToShip(): bool
    {
        return $this->update([
            'status' => self::STATUS_READY_TO_SHIP,
        ]);
    }
}
