<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceableZipcode extends Model
{
    protected $fillable = [
        'pincode',
        'hub',
        'city',
        'state_code',
        'is_serviceable',
    ];

    /**
     * Check if a pincode is serviceable
     */
    public static function isServiceable(string $pincode): bool
    {
        return self::where('pincode', $pincode)
            ->where('is_serviceable', 'YES')
            ->exists();
    }

    /**
     * Get autocomplete suggestions for pincode
     */
    public static function autocomplete(string $query, int $limit = 5): array
    {
        return self::where('pincode', 'LIKE', $query . '%')
            ->where('is_serviceable', 'YES')
            ->select('pincode', 'city', 'state_code', 'hub')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get zipcode details
     */
    public static function getDetails(string $pincode): ?self
    {
        return self::where('pincode', $pincode)->first();
    }

    /**
     * Scope for serviceable zipcodes only
     */
    public function scopeServiceable($query)
    {
        return $query->where('is_serviceable', 'YES');
    }
}
