<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Services\Contracts\CourierServiceInterface;

class ShiprocketService implements CourierServiceInterface
{
    private $baseUrl;
    private $email;
    private $password;
    private $token;

    public function __construct()
    {
        $this->baseUrl = 'https://apiv2.shiprocket.in/v1/external';
        $this->email = config('services.shiprocket.email');
        $this->password = config('services.shiprocket.password');
    }

    /**
     * Get authentication token from Shiprocket
     */
    public function authenticate()
    {
        try {
            $response = Http::post($this->baseUrl . '/auth/login', [
                'email' => $this->email,
                'password' => $this->password
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->token = $data['token'];
                return $this->token;
            }

            Log::error('Shiprocket authentication failed', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Shiprocket authentication error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Create order in Shiprocket
     */
    public function createOrder(Order $order)
    {
        try {
            // Skip Shiprocket for bulk orders with free shipping
            if ($order->is_bulk_purchased) {
                Log::info('Skipping Shiprocket order creation for bulk purchase order: ' . $order->id);
                return false;
            }

            // Authenticate first
            if (!$this->authenticate()) {
                throw new \Exception('Failed to authenticate with Shiprocket');
            }

            // Prepare order data
            $orderData = $this->prepareOrderData($order);

            // Create order in Shiprocket
            $response = Http::withToken($this->token)
                ->post($this->baseUrl . '/orders/create/adhoc', $orderData);

            if ($response->successful()) {
                $responseData = $response->json();

                // Log the full response for debugging
                Log::info('Shiprocket API Response', [
                    'order_id' => $order->id,
                    'response_data' => $responseData
                ]);

                // Check if the response contains an error message
                if (isset($responseData['message']) && !isset($responseData['order_id'])) {
                    Log::error('Shiprocket API Error', [
                        'order_id' => $order->id,
                        'message' => $responseData['message'],
                        'response' => $responseData
                    ]);
                    return false;
                }

                // Update order with Shiprocket details
                $order->update([
                    'shiprocket_order_id' => $responseData['order_id'] ?? null,
                    'shiprocket_shipment_id' => $responseData['shipment_id'] ?? null,
                ]);

                Log::info('Shiprocket order created successfully', [
                    'order_id' => $order->id,
                    'shiprocket_order_id' => $responseData['order_id'] ?? null,
                    'shiprocket_shipment_id' => $responseData['shipment_id'] ?? null
                ]);

                return $responseData;
            }

            Log::error('Shiprocket order creation failed', [
                'order_id' => $order->id,
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Shiprocket order creation error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Track order in Shiprocket
     */
    public function trackOrder($shiprocketOrderId)
    {
        try {
            if (!$this->authenticate()) {
                throw new \Exception('Failed to authenticate with Shiprocket');
            }

            $response = Http::withToken($this->token)
                ->get($this->baseUrl . '/courier/track/shipment/' . $shiprocketOrderId);

            if ($response->successful()) {
                return $response->json();
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Shiprocket tracking error', [
                'shiprocket_order_id' => $shiprocketOrderId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Cancel order in Shiprocket
     */
    public function cancelOrder($shiprocketOrderId)
    {
        try {
            if (!$this->authenticate()) {
                throw new \Exception('Failed to authenticate with Shiprocket');
            }

            $response = Http::withToken($this->token)
                ->post($this->baseUrl . '/orders/cancel', [
                    'ids' => [$shiprocketOrderId]
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Shiprocket order cancellation error', [
                'shiprocket_order_id' => $shiprocketOrderId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Prepare order data for Shiprocket API
     */
    private function prepareOrderData(Order $order)
    {
        $orderItems = [];
        $totalWeight = 0;

        foreach ($order->orderItems as $item) {
            $orderItems[] = [
                'name' => $item->book->title,
                'sku' => 'BOOK-' . $item->book->id,
                'units' => $item->quantity,
                'selling_price' => $item->price,
                'discount' => 0,
                'tax' => 0,
                'hsn' => '49019900' // HSN code for books
            ];

            // Use actual book weight or default to 0.5 kg if not set
            $bookWeight = $item->book->weight ?? 0.5;
            $totalWeight += ($bookWeight * $item->quantity);
        }

        return [
            'order_id' => $order->order_number,
            'order_date' => $order->created_at->format('Y-m-d H:i'),
            'pickup_location' => 'warehouse', // Use the actual pickup location name
            'channel_id' => '',
            'comment' => $order->notes ?? 'Order from IPDC',
            'billing_customer_name' => $order->shipping_address['name'],
            'billing_last_name' => '',
            'billing_address' => $order->shipping_address['address_line_1'],
            'billing_address_2' => $order->shipping_address['address_line_2'] ?? '',
            'billing_city' => $order->shipping_address['city'],
            'billing_pincode' => $order->shipping_address['postal_code'],
            'billing_state' => $order->shipping_address['state'],
            'billing_country' => $order->shipping_address['country'],
            'billing_email' => $order->user->email,
            'billing_phone' => $order->shipping_address['phone'],
            'shipping_is_billing' => true,
            'shipping_customer_name' => $order->shipping_address['name'],
            'shipping_last_name' => '',
            'shipping_address' => $order->shipping_address['address_line_1'],
            'shipping_address_2' => $order->shipping_address['address_line_2'] ?? '',
            'shipping_city' => $order->shipping_address['city'],
            'shipping_pincode' => $order->shipping_address['postal_code'],
            'shipping_state' => $order->shipping_address['state'],
            'shipping_country' => $order->shipping_address['country'],
            'shipping_email' => $order->user->email,
            'shipping_phone' => $order->shipping_address['phone'],
            'order_items' => $orderItems,
            'payment_method' => 'Prepaid',
            'shipping_charges' => $order->shipping_cost,
            'giftwrap_charges' => 0,
            'transaction_charges' => 0,
            'total_discount' => 0,
            'sub_total' => $order->subtotal,
            'length' => $this->getOrderDimensions($order, 'length'),
            'breadth' => $this->getOrderDimensions($order, 'breadth'),
            'height' => $this->getOrderDimensions($order, 'height'),
            'weight' => max($totalWeight, 0.5), // Minimum 0.5 kg
        ];
    }

    /**
     * Get order dimensions based on books in the order
     */
    private function getOrderDimensions(Order $order, $dimension)
    {
        $maxDimensions = [
            'length' => 0,
            'breadth' => 0,
            'height' => 0
        ];

        foreach ($order->orderItems as $item) {
            $book = $item->book;

            // Use actual book dimensions or defaults
            $bookLength = $book->width ?? 15; // width becomes length for shipping
            $bookBreadth = $book->depth ?? 10; // depth becomes breadth for shipping
            $bookHeight = $book->height ?? 5; // height stays height

            $maxDimensions['length'] = max($maxDimensions['length'], $bookLength);
            $maxDimensions['breadth'] = max($maxDimensions['breadth'], $bookBreadth);
            $maxDimensions['height'] = max($maxDimensions['height'], $bookHeight);
        }

        // Return default values if no books have dimensions set
        if ($maxDimensions[$dimension] == 0) {
            $defaults = [
                'length' => 20,
                'breadth' => 15,
                'height' => 5
            ];
            return $defaults[$dimension];
        }

        return $maxDimensions[$dimension];
    }

    /**
     * Get available courier companies
     */
    public function getCourierCompanies($pickupPostcode, $deliveryPostcode, $weight, $cod = 0)
    {
        try {
            if (!$this->authenticate()) {
                throw new \Exception('Failed to authenticate with Shiprocket');
            }

            $response = Http::withToken($this->token)
                ->get($this->baseUrl . '/courier/serviceability', [
                    'pickup_postcode' => $pickupPostcode,
                    'delivery_postcode' => $deliveryPostcode,
                    'weight' => $weight,
                    'cod' => $cod
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Shiprocket courier companies error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if Shiprocket service is enabled
     */
    public function isEnabled()
    {
        return config('services.shiprocket.enabled', false);
    }

    /**
     * Get the provider name
     */
    public function getProviderName()
    {
        return 'shiprocket';
    }
}
