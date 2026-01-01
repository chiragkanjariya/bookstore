<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\ShreeMarutiCourierService;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateShippedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update-shipped';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check with Maruti API and update orders to shipped status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting shipped orders update...');

        // Get orders that are ready to ship but not yet shipped
        $orders = Order::where('status', Order::STATUS_READY_TO_SHIP)
            ->where('courier_provider', 'shree_maruti')
            ->whereNotNull('courier_document_ref')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No orders to update.');
            return 0;
        }

        $this->info("Found {$orders->count()} orders to check.");

        $marutiService = new ShreeMarutiCourierService();
        $emailService = new EmailService();
        $updatedCount = 0;

        foreach ($orders as $order) {
            try {
                $this->info("Checking order #{$order->order_number}...");

                // Get shipment status from Maruti API
                $statusData = $marutiService->getShipmentStatus($order->courier_document_ref);

                if ($statusData && $statusData['success']) {
                    $status = strtolower($statusData['status'] ?? '');

                    // Check if the order has been shipped
                    // Common shipped status keywords: shipped, dispatched, in transit, etc.
                    if (
                        str_contains($status, 'shipped') ||
                        str_contains($status, 'dispatched') ||
                        str_contains($status, 'in transit') ||
                        str_contains($status, 'intransit')
                    ) {
                        // Update order to shipped status
                        $order->update([
                            'status' => Order::STATUS_SHIPPED,
                            'shipped_at' => now(),
                            'tracking_number' => $statusData['awb_number'] ?? $order->tracking_number,
                        ]);

                        // Send notification email to customer
                        $emailService->sendOrderShippedEmail($order);

                        $this->info("✓ Order #{$order->order_number} marked as shipped.");
                        $updatedCount++;

                        Log::info('Order marked as shipped via cron', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'maruti_status' => $statusData['status'],
                            'awb_number' => $statusData['awb_number'] ?? null,
                        ]);
                    } else {
                        $this->info("  Order #{$order->order_number} not yet shipped. Status: {$statusData['status']}");
                    }
                } else {
                    $this->warn("  Failed to get status for order #{$order->order_number}");
                }
            } catch (\Exception $e) {
                $this->error("Error processing order #{$order->order_number}: {$e->getMessage()}");

                Log::error('Error updating shipped order via cron', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Completed. Updated {$updatedCount} orders to shipped status.");

        return 0;
    }
}
