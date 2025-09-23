<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ShiprocketService;
use App\Models\Order;

class TestShiprocketOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shiprocket:test-order {order_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Shiprocket order creation with a specific order';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');
        
        if (!$orderId) {
            // Get the latest order
            $order = Order::with(['user', 'orderItems.book'])->latest()->first();
            if (!$order) {
                $this->error('No orders found in the database');
                return;
            }
        } else {
            $order = Order::with(['user', 'orderItems.book'])->find($orderId);
            if (!$order) {
                $this->error('Order not found');
                return;
            }
        }

        $this->info('Testing Shiprocket order creation...');
        $this->info('Order: #' . $order->order_number);
        $this->info('Customer: ' . $order->user->name);
        $this->info('Items: ' . $order->orderItems->count());
        
        try {
            $shiprocketService = new ShiprocketService();
            $response = $shiprocketService->createOrder($order);
            
            if ($response) {
                $this->info('✅ Shiprocket order created successfully!');
                $this->info('Response: ' . json_encode($response, JSON_PRETTY_PRINT));
                
                // Reload order to see updated fields
                $order->refresh();
                $this->info('Updated Order:');
                $this->info('- Shiprocket Order ID: ' . ($order->shiprocket_order_id ?? 'Not set'));
                $this->info('- Shiprocket Shipment ID: ' . ($order->shiprocket_shipment_id ?? 'Not set'));
                
            } else {
                $this->error('❌ Shiprocket order creation failed!');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }
    }
}
