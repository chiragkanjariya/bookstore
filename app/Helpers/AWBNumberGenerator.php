<?php

namespace App\Helpers;

use App\Models\Order;
use App\Models\Setting;

class AWBNumberGenerator
{
    /**
     * Generate AWB number for an order
     */
    public static function generate(Order $order): string
    {
        $prefix = Setting::get('awb_number_prefix', 'IPDC');
        $timestamp = now()->format('ymd'); // YYMMDD format
        $orderId = str_pad($order->id, 6, '0', STR_PAD_LEFT);

        return strtoupper($prefix) . $timestamp . $orderId;
    }

    /**
     * Generate and assign AWB number to order
     */
    public static function assignToOrder(Order $order): string
    {
        if ($order->awb_number) {
            return $order->awb_number;
        }

        $awbNumber = self::generate($order);
        $order->update(['awb_number' => $awbNumber]);

        return $awbNumber;
    }
}
