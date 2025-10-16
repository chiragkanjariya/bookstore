<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use App\Services\ShiprocketService;

class WebhookController extends Controller
{
    private $razorpayApi;
    
    public function __construct()
    {
        $this->razorpayApi = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
    }

    /**
     * Handle Razorpay webhook events
     */
    public function razorpayWebhook(Request $request)
    {
        try {
            // Log all incoming webhook data for debugging
            Log::info('Razorpay Webhook Received - Full Request', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $request->headers->all(),
                'body' => $request->getContent(),
                'all_data' => $request->all()
            ]);

            $payload = $request->all();
            $event = $payload['event'] ?? null;
            
            Log::info('Razorpay Webhook Event Processing', [
                'event' => $event,
                'payload' => $payload
            ]);

            // Handle different webhook events
            switch ($event) {
                case 'payment.captured':
                    $this->handlePaymentCaptured($payload);
                    break;
                    
                case 'payment.failed':
                    $this->handlePaymentFailed($payload);
                    break;
                    
                case 'payment.authorized':
                    $this->handlePaymentAuthorized($payload);
                    break;
                    
                case 'order.paid':
                    $this->handleOrderPaid($payload);
                    break;
                    
                default:
                    Log::info('Unhandled Razorpay webhook event: ' . $event);
                    break;
            }

            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            Log::error('Razorpay webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Test endpoint to verify webhook is reachable
     */
    public function testWebhook(Request $request)
    {
        Log::info('Webhook test endpoint hit', [
            'method' => $request->method(),
            'data' => $request->all(),
            'timestamp' => now()
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Webhook endpoint is working',
            'timestamp' => now(),
            'received_data' => $request->all()
        ]);
    }



    /**
     * Handle payment captured event
     */
    private function handlePaymentCaptured($payload)
    {
        $payment = $payload['payload']['payment']['entity'] ?? null;
        
        if (!$payment) {
            Log::error('Payment data not found in webhook payload');
            return;
        }

        $razorpayOrderId = $payment['order_id'] ?? null;
        $paymentId = $payment['id'] ?? null;
        $amount = $payment['amount'] ?? 0;
        $status = $payment['status'] ?? null;

        Log::info('Processing payment captured webhook', [
            'razorpay_order_id' => $razorpayOrderId,
            'payment_id' => $paymentId,
            'amount' => $amount,
            'status' => $status
        ]);

        // Find order by Razorpay order ID
        $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();
        
        if (!$order) {
            Log::error('Order not found for Razorpay order ID: ' . $razorpayOrderId);
            return;
        }

        // Update order status if payment was captured successfully
        if ($status === 'captured') {
            $order->update([
                'payment_status' => 'paid',
                'razorpay_payment_id' => $paymentId,
                'status' => 'processing'
            ]);

            Log::info('Order payment status updated via webhook', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_status' => 'paid'
            ]);

            // Create Shiprocket order automatically
            $this->createShiprocketOrderIfNeeded($order);

            // Send order confirmation email if not already sent
            $this->sendOrderConfirmationIfNeeded($order);
        }
    }

    /**
     * Handle payment failed event
     */
    private function handlePaymentFailed($payload)
    {
        $payment = $payload['payload']['payment']['entity'] ?? null;
        
        if (!$payment) {
            Log::error('Payment data not found in webhook payload');
            return;
        }

        $razorpayOrderId = $payment['order_id'] ?? null;
        $paymentId = $payment['id'] ?? null;
        $errorCode = $payment['error_code'] ?? null;
        $errorDescription = $payment['error_description'] ?? null;

        Log::info('Processing payment failed webhook', [
            'razorpay_order_id' => $razorpayOrderId,
            'payment_id' => $paymentId,
            'error_code' => $errorCode,
            'error_description' => $errorDescription
        ]);

        // Find order by Razorpay order ID
        $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();
        
        if (!$order) {
            Log::error('Order not found for Razorpay order ID: ' . $razorpayOrderId);
            return;
        }

        // Update order status to failed
        $order->update([
            'payment_status' => 'failed',
            'razorpay_payment_id' => $paymentId,
            'status' => 'cancelled',
            'notes' => 'Payment failed: ' . $errorDescription
        ]);

        Log::info('Order payment status updated to failed via webhook', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'error_code' => $errorCode
        ]);
    }

    /**
     * Handle payment authorized event (for manual capture)
     */
    private function handlePaymentAuthorized($payload)
    {
        $payment = $payload['payload']['payment']['entity'] ?? null;
        
        if (!$payment) {
            Log::error('Payment data not found in webhook payload');
            return;
        }

        $razorpayOrderId = $payment['order_id'] ?? null;
        $paymentId = $payment['id'] ?? null;

        Log::info('Processing payment authorized webhook', [
            'razorpay_order_id' => $razorpayOrderId,
            'payment_id' => $paymentId
        ]);

        // Find order by Razorpay order ID
        $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();
        
        if (!$order) {
            Log::error('Order not found for Razorpay order ID: ' . $razorpayOrderId);
            return;
        }

        // Update order with authorized payment details
        $order->update([
            'payment_status' => 'authorized',
            'razorpay_payment_id' => $paymentId,
            'status' => 'pending'
        ]);

        Log::info('Order payment status updated to authorized via webhook', [
            'order_id' => $order->id,
            'order_number' => $order->order_number
        ]);
    }

    /**
     * Handle order paid event
     */
    private function handleOrderPaid($payload)
    {
        $order = $payload['payload']['order']['entity'] ?? null;
        
        if (!$order) {
            Log::error('Order data not found in webhook payload');
            return;
        }

        $razorpayOrderId = $order['id'] ?? null;
        $amount = $order['amount'] ?? 0;
        $amountPaid = $order['amount_paid'] ?? 0;

        Log::info('Processing order paid webhook', [
            'razorpay_order_id' => $razorpayOrderId,
            'amount' => $amount,
            'amount_paid' => $amountPaid
        ]);

        // Find order by Razorpay order ID
        $dbOrder = Order::where('razorpay_order_id', $razorpayOrderId)->first();
        
        if (!$dbOrder) {
            Log::error('Order not found for Razorpay order ID: ' . $razorpayOrderId);
            return;
        }

        // Update order status if fully paid
        if ($amountPaid >= $amount) {
            $dbOrder->update([
                'payment_status' => 'paid',
                'status' => 'processing'
            ]);

            Log::info('Order marked as paid via webhook', [
                'order_id' => $dbOrder->id,
                'order_number' => $dbOrder->order_number
            ]);

            // Create Shiprocket order automatically
            $this->createShiprocketOrderIfNeeded($dbOrder);

            // Send order confirmation email if not already sent
            $this->sendOrderConfirmationIfNeeded($dbOrder);
        }
    }

    /**
     * Create Shiprocket order if needed
     */
    private function createShiprocketOrderIfNeeded($order)
    {
        try {
            // Skip if Shiprocket order already exists
            if ($order->shiprocket_order_id) {
                Log::info('Shiprocket order already exists for order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'shiprocket_order_id' => $order->shiprocket_order_id
                ]);
                return;
            }

            // Skip Shiprocket for bulk orders with free shipping
            if ($order->is_bulk_purchased) {
                Log::info('Skipping Shiprocket order creation for bulk purchase order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ]);
                return;
            }

            // Load order relationships needed for Shiprocket
            $order->load(['orderItems.book', 'user']);

            $shiprocketService = new ShiprocketService();
            $response = $shiprocketService->createOrder($order);

            if ($response) {
                Log::info('Shiprocket order created successfully via webhook', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'shiprocket_order_id' => $response['order_id'] ?? null,
                    'shiprocket_shipment_id' => $response['shipment_id'] ?? null
                ]);
            } else {
                Log::error('Failed to create Shiprocket order via webhook', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating Shiprocket order via webhook', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send order confirmation email if not already sent
     */
    private function sendOrderConfirmationIfNeeded($order)
    {
        try {
            // Check if confirmation email was already sent
            if ($order->confirmation_email_sent) {
                return;
            }

            // Generate invoice PDF
            $pdfPath = $this->generateInvoicePdf($order);

            // Send order confirmation email with invoice attachment
            $emailService = app(\App\Services\EmailService::class);
            $emailSent = $emailService->sendOrderConfirmationEmail($order, $pdfPath);

            if ($emailSent) {
                $order->update(['confirmation_email_sent' => true]);
                Log::info('Order confirmation email sent via webhook', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'invoice_attached' => $pdfPath ? true : false
                ]);
            }

            // Clean up temporary PDF file
            if ($pdfPath && file_exists($pdfPath)) {
                unlink($pdfPath);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email via webhook', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate invoice PDF for the order
     */
    private function generateInvoicePdf($order)
    {
        try {
            // Load order relationships
            $order->load(['orderItems.book.category', 'user']);
            
            // Use the new structure with orders collection
            $orders = collect([$order]);
            
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('admin.reports.accounts.combined-invoice', [
                'orders' => $orders,
                'totalOrders' => 1,
                'totalAmount' => $order->total_amount,
                'totalShipping' => $order->shipping_cost
            ]);

            // Generate filename and path
            $filename = 'invoice_' . $order->order_number . '.pdf';
            $tempPath = storage_path('app/temp/' . $filename);
            
            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            // Save PDF to temporary file
            file_put_contents($tempPath, $pdf->output());

            Log::info('Invoice PDF generated for webhook email', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'pdf_path' => $tempPath
            ]);

            return $tempPath;
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice PDF for webhook email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
