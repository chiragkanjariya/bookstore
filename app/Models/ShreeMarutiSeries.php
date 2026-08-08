<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShreeMarutiSeries extends Model
{
    protected $fillable = ['series_id', 'year', 'awb_number', 'order_id', 'is_used'];

    protected $casts = [
        'is_used' => 'boolean',
        'year' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Numbers that belong to the given year.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }
}
