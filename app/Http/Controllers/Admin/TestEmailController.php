<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EmailService;
use App\Models\Order;
use Illuminate\Http\Request;

class TestEmailController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request)
    {
        try {
            $emailService = new EmailService();
            
            // Test getting access token
            $token = $emailService->getAccessToken();
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get access token. Check your email API credentials.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email configuration is working! Access token obtained successfully.',
                'token' => substr($token, 0, 20) . '...' // Show partial token for security
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test sending email with actual order
     */
    public function testOrderEmail(Request $request)
    {
        try {
            // Get the latest order for testing
            $order = Order::with(['user', 'orderItems.book'])->latest()->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'No orders found to test with.'
                ], 404);
            }

            $emailService = new EmailService();
            
            // Test order placed email
            $placedEmailSent = $emailService->sendOrderPlacedEmail($order);
            
            // Test order confirmation email (only if order is paid)
            $confirmationEmailSent = false;
            if ($order->payment_status === 'paid') {
                $confirmationEmailSent = $emailService->sendOrderConfirmationEmail($order);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email test completed',
                'results' => [
                    'order_id' => $order->id,
                    'customer_email' => $order->user->email,
                    'order_placed_email' => $placedEmailSent ? 'Sent' : 'Failed',
                    'confirmation_email' => $order->payment_status === 'paid' 
                        ? ($confirmationEmailSent ? 'Sent' : 'Failed') 
                        : 'Skipped (order not paid)'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email test failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
