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
                // Generate invoice PDF for test
                $pdfPath = $this->generateInvoicePdf($order);
                $confirmationEmailSent = $emailService->sendOrderConfirmationEmail($order, $pdfPath);
                
                // Clean up temporary PDF file
                if ($pdfPath && file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
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

    /**
     * Generate invoice PDF for the order
     */
    private function generateInvoicePdf($order)
    {
        try {
            // Load order relationships
            $order->load(['orderItems.book.category', 'user']);
            
            // Structure the data the same way as the OrderController does
            $user = $order->user;
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

            \Log::info('Invoice PDF generated for test email', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'pdf_path' => $tempPath
            ]);

            return $tempPath;
        } catch (\Exception $e) {
            \Log::error('Failed to generate invoice PDF for test email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
