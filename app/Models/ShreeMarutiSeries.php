<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShreeMarutiSeries extends Model
{
    protected $fillable = ['series_id', 'awb_number', 'order_id', 'is_used'];

    protected $casts = [
        'is_used' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
