<?php

namespace App\Services\Contracts;

use App\Models\Order;

interface CourierServiceInterface
{
    /**
     * Authenticate with the courier service and obtain access token
     * 
     * @return string|bool Token on success, false on failure
     */
    public function authenticate();

    /**
     * Create a shipment order with the courier service
     * 
     * @param Order $order The order to create shipment for
     * @return array|bool Response data on success, false on failure
     */
    public function createOrder(Order $order);

    /**
     * Track a shipment using tracking reference
     * 
     * @param string $trackingReference Tracking number or order reference
     * @return array|bool Tracking data on success, false on failure
     */
    public function trackOrder($trackingReference);

    /**
     * Cancel a shipment order
     * 
     * @param string $orderReference Order reference or ID
     * @return array|bool Response data on success, false on failure
     */
    public function cancelOrder($orderReference);

    /**
     * Get available courier companies and rates
     * 
     * @param string $pickupPostcode Pickup pincode
     * @param string $deliveryPostcode Delivery pincode
     * @param float $weight Weight in kg
     * @param int $cod COD flag (0 or 1)
     * @return array|bool Courier companies data on success, false on failure
     */
    public function getCourierCompanies($pickupPostcode, $deliveryPostcode, $weight, $cod = 0);

    /**
     * Check if the courier service is enabled
     * 
     * @return bool
     */
    public function isEnabled();

    /**
     * Get the name of the courier provider
     * 
     * @return string
     */
    public function getProviderName();
}
