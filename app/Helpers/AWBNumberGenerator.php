<?php

namespace App\Helpers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class AWBNumberGenerator
{
    /**
     * Generate AWB number for an order (INTERNAL USE ONLY - may increment series)
     */
    protected static function generate(Order $order): string
    {
        // Use Maruti Series if enabled
        if (Setting::get('shree_maruti_enabled', false)) {
            try {
                $marutiService = new \App\Services\ShreeMarutiCourierService();
                $series = $marutiService->getNextSeriesNumber();
                
                if ($series) {
                    return $series;
                }
                
                // If Maruti is enabled but we couldn't get a series (exhausted or not configured)
                throw new \Exception('Shree Maruti courier series is exhausted or not properly configured. Please update series settings in Admin Panels.');
            } catch (\Exception $e) {
                // If it's our series exhausted exception, re-throw it to stop the fallback
                if (strpos($e->getMessage(), 'Shree Maruti courier series') !== false) {
                    throw $e;
                }
                Log::error('AWBGenerator: Failed to generate Maruti series: ' . $e->getMessage());
            }
        }

        // Fallback default format
        $prefix = Setting::get('awb_number_prefix', 'IPDC');
        $timestamp = now()->format('ymd'); // YYMMDD format
        $orderId = str_pad($order->id, 6, '0', STR_PAD_LEFT);

        return strtoupper($prefix) . $timestamp . $orderId;
    }

    /**
     * Generate and assign AWB number to order
     */
    public static function assignToOrder(Order $order, bool $force = false): string
    {
        if ($order->courier_provider === 'shree_maruti' && $order->courier_document_ref) {
            if ($order->awb_number !== $order->courier_document_ref) {
                $order->update(['awb_number' => $order->courier_document_ref]);
            }
            return $order->courier_document_ref;
        }

        if ($order->awb_number && !$force) {
            // Check if it's already a Maruti series (all numeric and long)
            if (is_numeric($order->awb_number) && strlen($order->awb_number) >= 14) {
                return $order->awb_number;
            }

            // Check if we need to transition from IPDC to Maruti
            $marutiEnabled = Setting::get('shree_maruti_enabled', false);
            if ($marutiEnabled && strpos($order->awb_number, 'IPDC') === 0) {
                // Generate a NEW Maruti series
            } else {
                return $order->awb_number;
            }
        }

        // Generate a NEW AWB (this will increment Maruti series if enabled)
        $awbNumber = self::generate($order);
        $order->update(['awb_number' => $awbNumber]);

        return $awbNumber;
    }
}
