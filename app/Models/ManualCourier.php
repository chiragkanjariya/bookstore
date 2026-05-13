<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualCourier extends Model
{
    protected $fillable = [
        'courier_service',
        'name',
        'tracking_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active couriers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
