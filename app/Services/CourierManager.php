<?php

namespace App\Services;

use App\Services\Contracts\CourierServiceInterface;
use Illuminate\Support\Facades\Log;

class CourierManager
{
    /**
     * Get the active courier service based on configuration
     * 
     * @return CourierServiceInterface|null
     */
    public function getActiveProvider()
    {
        $provider = config('services.courier.provider', 'none');

        Log::info('CourierManager: Getting active provider', [
            'configured_provider' => $provider
        ]);

        switch ($provider) {
            case 'shiprocket':
                return $this->getShiprocketService();

            case 'shree_maruti':
                return $this->getShreeMarutiService();

            case 'none':
            default:
                Log::info('CourierManager: No courier provider configured');
                return null;
        }
    }

    /**
     * Get Shiprocket service instance
     * 
     * @return ShiprocketService|null
     */
    private function getShiprocketService()
    {
        if (!config('services.shiprocket.enabled', false)) {
            Log::info('CourierManager: Shiprocket is disabled');
            return null;
        }

        return app(ShiprocketService::class);
    }

    /**
     * Get Shree Maruti Courier service instance
     * 
     * @return ShreeMarutiCourierService|null
     */
    private function getShreeMarutiService()
    {
        if (!config('services.shree_maruti.enabled', false)) {
            Log::info('CourierManager: Shree Maruti is disabled');
            return null;
        }

        return app(ShreeMarutiCourierService::class);
    }

    /**
     * Create order with the active courier provider
     * 
     * @param \App\Models\Order $order
     * @return array|bool
     */
    public function createOrder($order)
    {
        $provider = $this->getActiveProvider();

        if (!$provider) {
            Log::info('CourierManager: No active courier provider for order creation', [
                'order_id' => $order->id
            ]);
            return false;
        }

        if (!$provider->isEnabled()) {
            Log::info('CourierManager: Active provider is disabled', [
                'provider' => $provider->getProviderName(),
                'order_id' => $order->id
            ]);
            return false;
        }

        Log::info('CourierManager: Creating order with provider', [
            'provider' => $provider->getProviderName(),
            'order_id' => $order->id
        ]);

        return $provider->createOrder($order);
    }

    /**
     * Track order with the appropriate courier provider
     * 
     * @param string $trackingReference
     * @param string|null $providerName Optional provider name to use specific provider
     * @return array|bool
     */
    public function trackOrder($trackingReference, $providerName = null)
    {
        if ($providerName) {
            $provider = $this->getProviderByName($providerName);
        } else {
            $provider = $this->getActiveProvider();
        }

        if (!$provider) {
            Log::warning('CourierManager: No courier provider available for tracking', [
                'tracking_reference' => $trackingReference,
                'requested_provider' => $providerName
            ]);
            return false;
        }

        return $provider->trackOrder($trackingReference);
    }

    /**
     * Cancel order with the appropriate courier provider
     * 
     * @param string $orderReference
     * @param string|null $providerName Optional provider name to use specific provider
     * @return array|bool
     */
    public function cancelOrder($orderReference, $providerName = null)
    {
        if ($providerName) {
            $provider = $this->getProviderByName($providerName);
        } else {
            $provider = $this->getActiveProvider();
        }

        if (!$provider) {
            Log::warning('CourierManager: No courier provider available for cancellation', [
                'order_reference' => $orderReference,
                'requested_provider' => $providerName
            ]);
            return false;
        }

        return $provider->cancelOrder($orderReference);
    }

    /**
     * Get courier companies from the active provider
     * 
     * @param string $pickupPostcode
     * @param string $deliveryPostcode
     * @param float $weight
     * @param int $cod
     * @return array|bool
     */
    public function getCourierCompanies($pickupPostcode, $deliveryPostcode, $weight, $cod = 0)
    {
        $provider = $this->getActiveProvider();

        if (!$provider) {
            return false;
        }

        return $provider->getCourierCompanies($pickupPostcode, $deliveryPostcode, $weight, $cod);
    }

    /**
     * Get a specific provider by name
     * 
     * @param string $name
     * @return CourierServiceInterface|null
     */
    private function getProviderByName($name)
    {
        switch (strtolower($name)) {
            case 'shiprocket':
                return $this->getShiprocketService();

            case 'shree_maruti':
            case 'shreemaruti':
                return $this->getShreeMarutiService();

            default:
                return null;
        }
    }

    /**
     * Get the name of the active provider
     * 
     * @return string|null
     */
    public function getActiveProviderName()
    {
        $provider = $this->getActiveProvider();
        return $provider ? $provider->getProviderName() : null;
    }
}
