<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ServiceableZipcode;
use App\Services\ShreeMarutiCourierService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReviewMarutiOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:review-maruti-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Review and validate zipcodes for 10 pending orders against Maruti API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Maruti zipcode review for pending orders...');

        // Get 10 orders that are pending and haven't had their shipping partner status approved/rejected yet
        $orders = Order::whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PENDING_TO_BE_PREPARED, Order::STATUS_PROCESSING])
            ->where('shipping_partner_status', Order::SHIPPING_PARTNER_PENDING)
            ->where('requires_manual_shipping', false)
            ->limit(10)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No pending orders found for review.');
            return 0;
        }

        $this->info("Found {$orders->count()} orders to review.");

        $marutiService = new ShreeMarutiCourierService();
        $validatedCount = 0;
        $reviewData = [];

        foreach ($orders as $order) {
            try {
                $pincode = $order->shipping_address['postal_code'] ?? null;
                $city = $order->shipping_address['city'] ?? 'N/A';

                if (!$pincode) {
                    $this->warn("Order #{$order->order_number} has no pincode. Skipping.");
                    $reviewData[] = [
                        'order_number' => $order->order_number,
                        'date' => $order->created_at->format('Y-m-d H:i'),
                        'pincode' => 'MISSING',
                        'city' => $city,
                        'status' => 'PENDING',
                        'message' => 'No pincode provided'
                    ];
                    continue;
                }

                $this->info("Reviewing pincode {$pincode} for order #{$order->order_number}...");

                // Get pickup pincode from settings or use default
                $pickupPincode = \App\Models\Setting::get('shree_maruti_pickup_pincode', '390012'); 
                $weight = 0.5; // Default small weight for validation

                // Call Maruti API to check if it's serviceable
                $rates = $marutiService->getCourierCompanies($pickupPincode, $pincode, $weight);

                if ($rates && isset($rates['success'])) {
                    if ($rates['success'] == '1') {
                        $this->info("✓ Pincode {$pincode} is serviceable.");
                        $order->update([
                            'shipping_partner_status' => Order::SHIPPING_PARTNER_APPROVED,
                            'shipping_partner_error' => null
                        ]);

                        // Also update the ServiceableZipcode table if entry exists
                        ServiceableZipcode::where('pincode', $pincode)->update(['is_serviceable' => 'YES']);
                        
                        // Approved orders are NOT shown in the final review as per request
                    } else {
                        $errorMessage = $rates['message'] ?? 'Zipcode not serviceable';
                        
                        // If error is about the origin (from pincode), don't mark destination as unserviceable
                        if (str_contains(strtolower($errorMessage), 'from pincode') || str_contains(strtolower($errorMessage), 'pickup pincode')) {
                            $this->warn("⚠️ API Error (Origin): {$errorMessage}. Skipping validation for this order.");
                            $reviewData[] = [
                                'order_number' => $order->order_number,
                                'date' => $order->created_at->format('Y-m-d H:i'),
                                'pincode' => $pincode,
                                'city' => $city,
                                'status' => 'STILL PENDING',
                                'message' => 'Origin Pincode Error: ' . $errorMessage
                            ];
                        } else {
                            $this->error("✗ Pincode {$pincode} is NOT serviceable. Error: {$errorMessage}");

                            $order->update([
                                'shipping_partner_status' => Order::SHIPPING_PARTNER_REJECTED,
                                'shipping_partner_error' => $errorMessage,
                                'requires_manual_shipping' => true  
                            ]);

                            // Update the ServiceableZipcode table to NO
                            ServiceableZipcode::where('pincode', $pincode)->update(['is_serviceable' => 'NO']);

                            $reviewData[] = [
                                'order_number' => $order->order_number,
                                'date' => $order->created_at->format('Y-m-d H:i'),
                                'pincode' => $pincode,
                                'city' => $city,
                                'status' => 'REJECTED',
                                'message' => $errorMessage
                            ];
                        }
                    }
                } else {
                    $this->error("Maruti API service returned an error or unsuccessful response for order #{$order->order_number}.");
                    $reviewData[] = [
                        'order_number' => $order->order_number,
                        'date' => $order->created_at->format('Y-m-d H:i'),
                        'pincode' => $pincode,
                        'city' => $city,
                        'status' => 'STILL PENDING',
                        'message' => 'API Unresponsive/Error'
                    ];
                }

                $validatedCount++;

            } catch (\Exception $e) {
                $this->error("Error reviewing order #{$order->order_number}: {$e->getMessage()}");
                Log::error('Maruti review error', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
                $reviewData[] = [
                    'order_number' => $order->order_number,
                    'date' => $order->created_at->format('Y-m-d H:i'),
                    'pincode' => $pincode ?? 'N/A',
                    'city' => $city ?? 'N/A',
                    'status' => 'ERROR',
                    'message' => $e->getMessage()
                ];
            }
        }

        $this->newLine();
        $this->info("Review summary (Batched 10): Processed {$validatedCount} orders.");

        if (!empty($reviewData)) {
            $this->warn("The following orders are REJECTED or STILL PENDING and require review:");
            $this->table(['Order #', 'Date', 'Pincode', 'City', 'Status', 'Details'], $reviewData);
        } else {
            $this->info("All orders in this batch were successfully APPROVED and validated.");
        }

        return 0;
    }
}
