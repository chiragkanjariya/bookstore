<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\ShreeMarutiCourierService;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateDeliveredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update-delivered';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check with Maruti API and update orders to delivered status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting delivered orders update...');

        // Get orders that are shipped but not yet delivered
        $orders = Order::where('status', Order::STATUS_SHIPPED)
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

                    // Check if the order has been delivered
                    // Common delivered status keywords: delivered, completed, etc.
                    if (
                        str_contains($status, 'delivered') ||
                        str_contains($status, 'completed') ||
                        str_contains($status, 'delivery')
                    ) {
                        // Update order to delivered status
                        $order->update([
                            'status' => Order::STATUS_DELIVERED,
                            'delivered_at' => now(),
                        ]);

                        // Send notification email to customer
                        $emailService->sendOrderDeliveredEmail($order);

                        $this->info("✓ Order #{$order->order_number} marked as delivered.");
                        $updatedCount++;

                        Log::info('Order marked as delivered via cron', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'maruti_status' => $statusData['status'],
                        ]);
                    } else {
                        $this->info("  Order #{$order->order_number} not yet delivered. Status: {$statusData['status']}");
                    }
                } else {
                    $this->warn("  Failed to get status for order #{$order->order_number}");
                }
            } catch (\Exception $e) {
                $this->error("Error processing order #{$order->order_number}: {$e->getMessage()}");

                Log::error('Error updating delivered order via cron', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Completed. Updated {$updatedCount} orders to delivered status.");

        return 0;
    }
}
