<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

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
            // Get webhook secret from config
            $webhookSecret = config('services.razorpay.webhook_secret');
            
            // Verify webhook signature
            $this->verifyWebhookSignature($request, $webhookSecret);
            
            $payload = $request->all();
            $event = $payload['event'] ?? null;
            
            Log::info('Razorpay Webhook Received', [
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
            
        } catch (SignatureVerificationError $e) {
            Log::error('Razorpay webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
            
        } catch (\Exception $e) {
            Log::error('Razorpay webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature(Request $request, $webhookSecret)
    {
        $actualSignature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();
        
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        
        if (!hash_equals($expectedSignature, $actualSignature)) {
            throw new SignatureVerificationError('Invalid webhook signature');
        }
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

            // Send order confirmation email if not already sent
            $this->sendOrderConfirmationIfNeeded($dbOrder);
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

            // Send order confirmation email
            $emailService = app(\App\Services\EmailService::class);
            $emailSent = $emailService->sendOrderConfirmationEmail($order);

            if ($emailSent) {
                $order->update(['confirmation_email_sent' => true]);
                Log::info('Order confirmation email sent via webhook', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email via webhook', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
