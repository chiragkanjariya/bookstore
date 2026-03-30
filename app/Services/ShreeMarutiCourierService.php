<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Services\Contracts\CourierServiceInterface;
use App\Models\Setting;

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
        $this->environment = Setting::get('shree_maruti_environment', 'IPDC');
        $this->clientCode = Setting::get('shree_maruti_client_code', 'IPDC');
        $this->clientName = Setting::get('shree_maruti_client_name', 'IPDC');
        $this->username = Setting::get('shree_maruti_username', 'IPDC');
        $this->password = Setting::get('shree_maruti_password', 'IPDC');

        // Set base URL based on environment
        if ($this->environment === 'production') {
            $this->baseUrl = 'https://customerapi.sevasetu.in/index.php/clientbooking_v5';
        } else {
            $this->baseUrl = 'https://customerapi.sevasetu.in/index.php/clientbookingbeta_v5';
        }
        $this->secretKey = Setting::get('shree_maruti_api_secret_key', 'IPDC');
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
                    
                    if (isset($data['data']['UserID'])) {
                        Cache::put('shree_maruti_user_id', $data['data']['UserID'], $this->tokenExpiry);
                    }

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
                                    'ClientRefID' => (string) $clientRefId,
                                    'IsDP' => "0",
                                    'FromPincode' => (string) $pickupPostcode,
                                    'ToPincode' => (string) $deliveryPostcode,
                                    'DocType' => (string) $docType,
                                    'Weight' => (string) max(50, $weightInGrams) // Minimum 50 grams
                                ]
                            ]
                        ]
                    ]);

            Log::info('Shree Maruti Rate Calculation Response: ' . $response->body());

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] == '1') {
                    Log::info('ShreeMaruti: Rate calculation successful', [
                        'from' => $pickupPostcode,
                        'to' => $deliveryPostcode,
                        'weight_grams' => $weightInGrams,
                        'rates_count' => count($data['data'] ?? [])
                    ]);
                } else {
                    Log::warning('ShreeMaruti: Rate calculation failed with message', [
                        'success' => $data['success'] ?? null,
                        'message' => $data['message'] ?? 'Unknown error'
                    ]);
                }

                return $data;
            }

            Log::error('ShreeMaruti: Rate calculation HTTP request failed', [
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
            if ($order->requires_manual_shipping) {
                Log::info('ShreeMaruti: Skipping order creation for manual shipping', [
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

            Log::info('Shree Maruti Create Order Response: ' . $response->body());
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

                $errorMessage = $data['message'] ?? 'Unknown error from Maruti API';
                Log::error('ShreeMaruti: Order creation failed', [
                    'order_id' => $order->id,
                    'response' => $data
                ]);
                return ['success' => false, 'message' => $errorMessage];
            }

            Log::error('ShreeMaruti: Order creation request failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return ['success' => false, 'message' => 'API request failed: ' . $response->body()];
        } catch (\Exception $e) {
            Log::error('ShreeMaruti: Order creation error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
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

        $data = [
            'ClientRefID' => (string) $this->clientCode,
            'IsDP' => "0",
            // 'DocumentNoRef' => 'BK' . $this->clientCode . $order->id,
            'DocumentNoRef' => (string) $this->getNextSeriesNumber(),
            'OrderNo' => (string) ($order->razorpay_order_id ?? $order->order_number),
            'PickupPincode' => (string) \App\Models\Setting::get('shree_maruti_pickup_pincode', '390012'),
            'ToPincode' => (string) ($order->shipping_address['postal_code'] ?? ''),
            'CodBooking' => "0",
            'TypeID' => "1",
            'ServiceTypeID' => "1",
            'TravelBy' => "1",
            'Weight' => (string) round($weightInGrams),
            'Length' => (string) round($maxLength),
            'Width' => (string) round($maxWidth),
            'Height' => (string) round($maxHeight),
            'ValueRs' => (string) round($order->total_amount),
            'ReceiverName' => (string) ($order->shipping_address['name'] ?? ''),
            'ReceiverAddress' => (string) ($order->shipping_address['address_line_1'] ?? ''),
            'ReceiverCity' => (string) ($order->shipping_address['city'] ?? ''),
            'ReceiverState' => (string) $stateId,
            'Area' => (string) ($order->shipping_address['city'] ?? ''),
            'ReceiverMobile' => (string) ($order->shipping_address['phone'] ?? ''),
            'ReceiverEmail' => (string) ($order->user->email ?? ''),
            'Remarks' => 'Pickup from center',
            'UserID' => (string) Cache::get('shree_maruti_user_id', config('services.shree_maruti.user_id', '12345'))
        ];

        Log::info('Shree Maruti Request Data: ', $data);

        return $data;
    }

    /**
     * Get state ID from state name
     */
    private function getStateId($stateName)
    {
        // State mapping as per Shree Maruti API Master Data
        $stateMap = [
            'GUJARAT' => '1',
            'MAHARASHTRA' => '2',
            'GOA' => '3',
            'RAJASTHAN' => '4',
            'MADHYA PRADESH' => '5',
            'CHHATTISGARH' => '6',
            'UTTAR PRADESH' => '7',
            'JAMMU & KASHMIR' => '8',
            'DELHI' => '9',
            'HARYANA' => '10',
            'PUNJAB' => '11',
            'UTTARAKHAND' => '12',
            'KARNATAKA' => '13',
            'KERALA' => '14',
            'TAMILNADU' => '15',
            'PONDICHERRY' => '16',
            'ANDHRA PRADESH' => '17',
            'ASSAM' => '18',
            'JHARKHAND' => '19',
            'ORISSA' => '20',
            'BIHAR' => '21',
            'WEST BENGAL' => '22',
            'MANIPUR' => '23',
            'HIMACHAL PRADESH' => '24',
            'CHANDIGARH' => '25',
            'SIKKIM' => '26',
            'ARUNACHAL PRADESH' => '27',
            'NAGALAND' => '28',
            'MIZORAM' => '29',
            'TRIPURA' => '30',
            'MEGHALAYA' => '31',
            'DAMAN AND DIU' => '32',
            'DADRA AND NAGAR HAVELI' => '33',
            'LAKSHADWEEP' => '34',
            'ANDAMAN AND NICOBAR' => '35',
            'TELANGANA' => '36',
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
        return \App\Models\Setting::get('shree_maruti_enabled', config('services.shree_maruti.enabled', false));
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

    /**
     * Get and increment the next series number for Shree Maruti.
     * This can be used for auto-generating labels or for tracking series usage.
     */
    public function getNextSeriesNumber()
    {
        $current = Setting::get('shree_maruti_series_current');
        $start = Setting::get('shree_maruti_series_start');
        $end = Setting::get('shree_maruti_series_end');
        $threshold = Setting::get('shree_maruti_notify_threshold');
        $email = Setting::get('shree_maruti_notification_email');

        // If current is empty, initialize with start
        if (empty($current)) {
            $current = $start;
        }

        if (empty($current)) {
            Log::warning('ShreeMaruti: Series tracking active but start/current numbers are empty.');
            return null;
        }

        // Increment for next use (handling potentially large numbers)
        // Note: Using BCMath if available for large strings, otherwise standard increment.
        if (function_exists('bcadd')) {
            $next = bcadd($current, '1');
        } else {
            $next = (string) ($current + 1);
        }
        
        // Update the setting for next call
        Setting::set('shree_maruti_series_current', $next, 'string', 'courier', 'Shree Maruti Series Current');

        Log::info('ShreeMaruti: Series number updated in storage', [
            'previous' => $current,
            'next' => $next
        ]);

        if (!empty($threshold) && !empty($email)) {
            $isNotified = Cache::get('shree_maruti_series_notified_' . $threshold, false);
            
            $shouldNotify = false;
            if (function_exists('bccomp')) {
                $shouldNotify = bccomp($current, $threshold) <= 0;
            } else {
                if (strlen($current) === strlen($threshold)) {
                    $shouldNotify = ($current <= $threshold);
                } else {
                    $shouldNotify = strlen($current) < strlen($threshold) || ($current <= $threshold);
                }
            }

            if ($shouldNotify && !$isNotified) {
                $this->sendSeriesExpirationNotification($current, $threshold, $email);
                Cache::put('shree_maruti_series_notified_' . $threshold, true, now()->addDays(7));
            }
        }

        return $current;
    }

    private function sendSeriesExpirationNotification($current, $threshold, $email)
    {
        try {
            $subject = 'URGENT: Shree Maruti Courier Series Expiration Warning';
            $body = "Hello Admin,\n\n" .
                   "Your Shree Maruti Courier series number is approaching its limit.\n" .
                   "Current Series: $current\n" .
                   "Threshold Level: $threshold\n\n" .
                   "Please obtain a new series range to ensure uninterrupted label generation.\n\n" .
                   "Regards,\nIPDC System";

            $emailService = new \App\Services\EmailService();
            $emailService->sendEmail([$email => 'Admin'], $subject, nl2br($body));

            Log::warning("ShreeMaruti: Expiration email sent to $email", [
                'current' => $current,
                'threshold' => $threshold
            ]);
        } catch (\Exception $e) {
            Log::error('ShreeMaruti: Failed to send expiration email: ' . $e->getMessage());
        }
    }
}
