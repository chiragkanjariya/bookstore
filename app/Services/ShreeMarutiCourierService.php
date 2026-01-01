<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Services\Contracts\CourierServiceInterface;

class ShreeMarutiCourierService implements CourierServiceInterface
{
    private $baseUrl;
    private $clientCode;
    private $clientName;
    private $username;
    private $password;
    private $secretKey;
    private $environment;
    private $token;
    private $tokenExpiry;

    public function __construct()
    {
        $this->environment = config('services.shree_maruti.environment', 'beta');
        $this->clientCode = config('services.shree_maruti.client_code');
        $this->clientName = config('services.shree_maruti.client_name');
        $this->username = config('services.shree_maruti.username');
        $this->password = config('services.shree_maruti.password');

        // Set base URL based on environment
        if ($this->environment === 'production') {
            $this->baseUrl = 'https://customerapi.sevasetu.in/index.php/clientbooking_v5';
            $this->secretKey = config('services.shree_maruti.secret_key_prod');
        } else {
            $this->baseUrl = 'https://customerapi.sevasetu.in/index.php/clientbookingbeta_v5';
            $this->secretKey = config('services.shree_maruti.secret_key_beta');
        }
    }

    /**
     * Authenticate with Shree Maruti Courier and obtain access token
     * Token expires daily at midnight (12:00 AM)
     */
    public function authenticate()
    {
        try {
            // Check if we have a cached valid token
            $cachedToken = Cache::get('shree_maruti_token');
            $cachedExpiry = Cache::get('shree_maruti_token_expiry');

            if ($cachedToken && $cachedExpiry && now()->lt($cachedExpiry)) {
                $this->token = $cachedToken;
                $this->tokenExpiry = $cachedExpiry;

                Log::info('ShreeMaruti: Using cached token', [
                    'expires_at' => $cachedExpiry
                ]);

                return $this->token;
            }

            // Call login API
            $response = Http::withHeaders([
                'clientcode' => $this->clientCode,
                'secretkey' => $this->secretKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/login', [
                        'data' => [
                            'login_username' => $this->username,
                            'login_password' => $this->password
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] == '1') {
                    $this->token = $data['AuthToken'];
                    $this->tokenExpiry = \Carbon\Carbon::parse($data['TokenExpiredOn']);

                    // Cache the token until expiry
                    Cache::put('shree_maruti_token', $this->token, $this->tokenExpiry);
                    Cache::put('shree_maruti_token_expiry', $this->tokenExpiry, $this->tokenExpiry);

                    Log::info('ShreeMaruti: Authentication successful', [
                        'token_expires_at' => $this->tokenExpiry,
                        'user_id' => $data['data']['UserID'] ?? null,
                        'username' => $data['data']['Username'] ?? null
                    ]);

                    return $this->token;
                }

                Log::error('ShreeMaruti: Authentication failed', [
                    'response' => $data
                ]);
                return false;
            }

            Log::error('ShreeMaruti: Authentication request failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('ShreeMaruti: Authentication error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get state master data
     */
    public function getStateMaster($date = null)
    {
        try {
            if (!$this->authenticate()) {
                throw new \Exception('Failed to authenticate with Shree Maruti');
            }

            $date = $date ?? now()->format('Y-m-d');

            $response = Http::withHeaders([
                'token' => $this->token,
                'clientcode' => $this->clientCode,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/getstatedata', [
                        'data' => [
                            'date' => $date
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] == '1') {
                    return $data['state_data'] ?? [];
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('ShreeMaruti: Get state master error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Calculate freight rates
     */
    public function getCourierCompanies($pickupPostcode, $deliveryPostcode, $weight, $cod = 0)
    {
        try {
            if (!$this->authenticate()) {
                throw new \Exception('Failed to authenticate with Shree Maruti');
            }

            // Get ClientRefID and IsDP from login response or config
            $clientRefId = $this->clientCode;
            $isDP = 1; // Default to 1 as per BAPS VISION client

            // Convert weight from kg to grams
            $weightInGrams = $weight * 1000;

            // Determine document type based on weight
            $docType = $weightInGrams <= 500 ? 1 : 2; // 1 = Dox, 2 = Non-Dox

            $response = Http::withHeaders([
                'token' => $this->token,
                'clientcode' => $this->clientCode,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/ratecalculator', [
                        'Data' => [
                            [
                                'data' => [
                                    'ClientRefID' => $clientRefId,
                                    'IsDP' => $isDP,
                                    'FromPincode' => $pickupPostcode,
                                    'ToPincode' => $deliveryPostcode,
                                    'DocType' => $docType,
                                    'Weight' => max(50, $weightInGrams) // Minimum 50 grams
                                ]
                            ]
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] == '1') {
                    Log::info('ShreeMaruti: Rate calculation successful', [
                        'from' => $pickupPostcode,
                        'to' => $deliveryPostcode,
                        'weight_grams' => $weightInGrams,
                        'rates_count' => count($data['data'] ?? [])
                    ]);

                    return $data;
                }
            }

            Log::error('ShreeMaruti: Rate calculation failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('ShreeMaruti: Rate calculation error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Create shipment order
     */
    public function createOrder(Order $order)
    {
        try {
            // Skip for bulk orders
            if ($order->is_bulk_purchased) {
                Log::info('ShreeMaruti: Skipping order creation for bulk purchase', [
                    'order_id' => $order->id
                ]);
                return false;
            }

            if (!$this->authenticate()) {
                throw new \Exception('Failed to authenticate with Shree Maruti');
            }

            // Prepare order data
            $orderData = $this->prepareOrderData($order);

            $response = Http::withHeaders([
                'token' => $this->token,
                'clientcode' => $this->clientCode,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/insertbooking', [
                        'Data' => [
                            [
                                'data' => $orderData
                            ]
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] == '1') {
                    // Update order with Shree Maruti details
                    $order->update([
                        'courier_provider' => 'shree_maruti',
                        'courier_document_ref' => $orderData['DocumentNoRef'],
                        'tracking_number' => $orderData['DocumentNoRef'] // Will be updated with AWB later
                    ]);

                    Log::info('ShreeMaruti: Order created successfully', [
                        'order_id' => $order->id,
                        'document_ref' => $orderData['DocumentNoRef']
                    ]);

                    return [
                        'success' => true,
                        'document_ref' => $orderData['DocumentNoRef'],
                        'message' => $data['message'] ?? 'Order created successfully'
                    ];
                }

                Log::error('ShreeMaruti: Order creation failed', [
                    'order_id' => $order->id,
                    'response' => $data
                ]);
                return false;
            }

            Log::error('ShreeMaruti: Order creation request failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('ShreeMaruti: Order creation error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Prepare order data for Shree Maruti API
     */
    private function prepareOrderData(Order $order)
    {
        $totalWeight = 0;
        $maxLength = 0;
        $maxWidth = 0;
        $maxHeight = 0;

        // Calculate total weight and dimensions
        foreach ($order->orderItems as $item) {
            $bookWeight = $item->book->weight ?? 0.5; // Default 0.5 kg
            $totalWeight += ($bookWeight * $item->quantity);

            $maxLength = max($maxLength, $item->book->width ?? 15);
            $maxWidth = max($maxWidth, $item->book->depth ?? 10);
            $maxHeight = max($maxHeight, $item->book->height ?? 5);
        }

        // Convert weight to grams
        $weightInGrams = max(50, $totalWeight * 1000); // Minimum 50 grams

        // Fixed values as per requirements
        $typeId = 1; // Fixed
        $serviceTypeId = 1; // Fixed
        $travelBy = 1; // Fixed
        $codBooking = 0; // All orders are prepaid via Razorpay

        // Get state ID from state name (you may need to implement state mapping)
        $stateId = $this->getStateId($order->shipping_address['state']);

        return [
            'ClientRefID' => $this->clientCode,
            'IsDP' => 1,
            'DocumentNoRef' => 'IWB2500001', // Fixed value as per requirements
            'OrderNo' => $order->razorpay_order_id ?? $order->order_number, // Use Razorpay order ID
            'PickupPincode' => '390007', // Fixed value as per requirements
            'ToPincode' => $order->shipping_address['postal_code'],
            'CodBooking' => $codBooking,
            'TypeID' => $typeId,
            'ServiceTypeID' => $serviceTypeId,
            'TravelBy' => $travelBy,
            'Weight' => $weightInGrams,
            'Length' => $maxLength,
            'Width' => $maxWidth,
            'Height' => $maxHeight,
            'ValueRs' => $order->total_amount,
            'ReceiverName' => $order->shipping_address['name'],
            'ReceiverAddress' => $order->shipping_address['address_line_1'],
            'ReceiverCity' => $order->shipping_address['city'],
            'ReceiverState' => $stateId,
            'Area' => $order->shipping_address['city'], // Using city as area
            'ReceiverMobile' => $order->shipping_address['phone'],
            'ReceiverEmail' => $order->user->email,
            'Remarks' => 'Pickup from center', // Fixed value as per requirements
            'UserID' => config('services.shree_maruti.user_id', '12345') // Get from login response
        ];
    }

    /**
     * Get state ID from state name
     * This is a basic implementation - you may want to cache state data
     */
    private function getStateId($stateName)
    {
        // Common state mappings - you should fetch this from getStateMaster API
        $stateMap = [
            'GUJARAT' => '1',
            'MAHARASHTRA' => '2',
            'DELHI' => '3',
            // Add more states as needed
        ];

        return $stateMap[strtoupper($stateName)] ?? '1';
    }

    /**
     * Track shipment
     */
    public function trackOrder($trackingReference)
    {
        try {
            if (!$this->authenticate()) {
                throw new \Exception('Failed to authenticate with Shree Maruti');
            }

            $response = Http::withHeaders([
                'token' => $this->token,
                'clientcode' => $this->clientCode,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/client_tracking_all', [
                        'data' => [
                            'reference_no' => $trackingReference
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] == '1') {
                    Log::info('ShreeMaruti: Tracking successful', [
                        'reference' => $trackingReference
                    ]);

                    return $data['data'] ?? [];
                }
            }

            Log::error('ShreeMaruti: Tracking failed', [
                'reference' => $trackingReference,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('ShreeMaruti: Tracking error', [
                'reference' => $trackingReference,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Cancel shipment order
     */
    public function cancelOrder($orderReference)
    {
        try {
            if (!$this->authenticate()) {
                throw new \Exception('Failed to authenticate with Shree Maruti');
            }

            $response = Http::withHeaders([
                'token' => $this->token,
                'clientcode' => $this->clientCode,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/cancelbooking', [
                        'Data' => [
                            [
                                'data' => [
                                    'ClientRefID' => $this->clientCode,
                                    'DocumentNoRef' => $orderReference,
                                    'UserID' => config('services.shree_maruti.user_id', '12345')
                                ]
                            ]
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] == '1') {
                    Log::info('ShreeMaruti: Order cancelled successfully', [
                        'reference' => $orderReference
                    ]);

                    return $data;
                }
            }

            Log::error('ShreeMaruti: Cancellation failed', [
                'reference' => $orderReference,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('ShreeMaruti: Cancellation error', [
                'reference' => $orderReference,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if Shree Maruti service is enabled
     */
    public function isEnabled()
    {
        return config('services.shree_maruti.enabled', false);
    }

    /**
     * Get the provider name
     */
    public function getProviderName()
    {
        return 'shree_maruti';
    }

    /**
     * Get AWB number for a booking
     */
    public function getAWBNumber($bookingDate, $documentRef = null)
    {
        try {
            if (!$this->authenticate()) {
                throw new \Exception('Failed to authenticate with Shree Maruti');
            }

            $response = Http::withHeaders([
                'token' => $this->token,
                'clientcode' => $this->clientCode,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/getshipmentdetails', [
                        'data' => [
                            'IsDP' => 1,
                            'ClientRefID' => $this->clientCode,
                            'bookingdate' => $bookingDate
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] == '1') {
                    $bookings = $data['bookingdata'] ?? [];

                    // If document ref provided, find specific booking
                    if ($documentRef) {
                        foreach ($bookings as $booking) {
                            if ($booking['BookingRefNo'] === $documentRef) {
                                return $booking['TrackingNo'] ?? null;
                            }
                        }
                    }

                    return $bookings;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('ShreeMaruti: Get AWB error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get shipment status details for status updates
     */
    public function getShipmentStatus($documentRef)
    {
        try {
            if (!$this->authenticate()) {
                throw new \Exception('Failed to authenticate with Shree Maruti');
            }

            $response = Http::withHeaders([
                'token' => $this->token,
                'clientcode' => $this->clientCode,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/client_tracking_all', [
                        'data' => [
                            'reference_no' => $documentRef
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] == '1') {
                    $trackingData = $data['data'] ?? [];

                    if (!empty($trackingData)) {
                        // Get the latest status
                        $latestStatus = end($trackingData);

                        Log::info('ShreeMaruti: Shipment status retrieved', [
                            'reference' => $documentRef,
                            'status' => $latestStatus['Status'] ?? 'Unknown'
                        ]);

                        return [
                            'success' => true,
                            'status' => $latestStatus['Status'] ?? null,
                            'status_code' => $latestStatus['StatusCode'] ?? null,
                            'tracking_data' => $trackingData,
                            'awb_number' => $latestStatus['AWBNo'] ?? null,
                        ];
                    }
                }
            }

            Log::error('ShreeMaruti: Failed to get shipment status', [
                'reference' => $documentRef,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('ShreeMaruti: Get shipment status error', [
                'reference' => $documentRef,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
