<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailService
{
    private $baseUrl;
    private $authToken;
    private $accessToken;

    public function __construct()
    {
        $this->baseUrl = config('mail.api_base_url', 'https://mail.ipdc.org/api/v1');
        $this->authToken = config('mail.auth_token');
    }

    /**
     * Get mail access token
     */
    public function getAccessToken($email = null, $password = null)
    {
        try {
            $email = 'helloatbhavesh@gmail.com';
            $password = 'Hello@bhavesh123';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->authToken
            ])->asForm()->post('https://mail.ipdc.org/api/v1/user/mail_access_token', [
                'email' => $email,
                'user_type' => '4',
                'password' => $password
            ]);
            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['token'] ?? null;
                return $this->accessToken;
            }

            Log::error('Failed to get email access token', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Email access token error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send order placed notification email (before payment)
     */
    public function sendOrderPlacedEmail($order)
    {
        try {
            // Get access token if not already set
            if (!$this->accessToken) {
                $this->getAccessToken();
            }

            if (!$this->accessToken) {
                throw new Exception('Unable to get email access token');
            }

            // Prepare email data
            $customerEmail = $order->user->email;
            $customerName = $order->user->name;
            $orderNumber = 'ORD-' . $order->id;
            
            // Prepare email content
            $subject = "Order Placed - {$orderNumber} (Payment Pending)";
            $message = $this->getOrderPlacedEmailTemplate($order);
            
            // Prepare recipients
            $to = [
                $customerEmail => $customerName
            ];

            // Send email without attachment
            return $this->sendEmailViaAPI($to, $subject, $message);

        } catch (Exception $e) {
            Log::error('Order placed email error: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null
            ]);
            return false;
        }
    }

    /**
     * Send email via API (common method)
     */
    private function sendEmailViaAPI($to, $subject, $message, $attachments = [])
    {
        try {
            // Notification details
            $notificationEmail = [
                config('mail.notification_email', 'admin@bookstore.com') => config('mail.notification_name', 'Bookstore Admin')
            ];

            // Prepare form data - format to match your API example
            $formData = [
                'notification_email' => json_encode($notificationEmail),
                'subject' => $subject,
                'message' => $message,
                'to' => json_encode([$to]), // This should be an array of objects
                'is_online_user' => '2',
                'notification_name' => config('mail.notification_name', 'Bookstore Admin')
            ];

            Log::info('Sending email via API', [
                'to' => $to,
                'subject' => $subject,
                'form_data' => $formData
            ]);

            // Prepare HTTP request
            $request = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken
            ])->asMultipart();

            // Add form data
            foreach ($formData as $key => $value) {
                $request = $request->attach($key, $value);
            }

            // Add attachments if provided
            foreach ($attachments as $attachment) {
                if (file_exists($attachment['path'])) {
                    $request = $request->attach('file', fopen($attachment['path'], 'r'), $attachment['name']);
                }
            }

            // Send email
            $response = $request->post($this->baseUrl . '/user/send_smtp_mail');

            Log::info('Email API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Failed to send email via API', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return false;

        } catch (Exception $e) {
            Log::error('Email API error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order confirmation email with invoice PDF (after payment)
     */
    public function sendOrderConfirmationEmail($order, $pdfPath = null)
    {
        try {
            // Get access token if not already set
            if (!$this->accessToken) {
                $this->getAccessToken();
            }

            if (!$this->accessToken) {
                throw new Exception('Unable to get email access token');
            }

            // Prepare email data
            $customerEmail = $order->user->email;
            $customerName = $order->user->name;
            $orderNumber = 'ORD-' . $order->id;
            
            // Generate invoice PDF if not provided
            if (!$pdfPath) {
                $pdfPath = $this->generateInvoicePDF($order);
            }

            // Prepare email content
            $subject = "Order Confirmation - {$orderNumber}";
            $message = $this->getOrderConfirmationEmailTemplate($order);
            
            // Prepare recipients
            $to = [
                $customerEmail => $customerName
            ];

            // Prepare attachments
            $attachments = [];
            if ($pdfPath && file_exists($pdfPath)) {
                $attachments[] = [
                    'path' => $pdfPath,
                    'name' => 'invoice.pdf'
                ];
            }

            // Send email with attachment
            $result = $this->sendEmailViaAPI($to, $subject, $message, $attachments);

            if ($result) {
                Log::info('Order confirmation email sent successfully', [
                    'order_id' => $order->id,
                    'customer_email' => $customerEmail
                ]);

                // Clean up temporary PDF file
                if ($pdfPath && file_exists($pdfPath) && strpos($pdfPath, 'temp') !== false) {
                    unlink($pdfPath);
                }
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Order confirmation email error: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null
            ]);
            return false;
        }
    }

    /**
     * Generate invoice PDF for order
     */
    private function generateInvoicePDF($order)
    {
        try {
            $users = collect([$order->user]);
            
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('admin.reports.accounts.combined-invoice', [
                'users' => $users,
                'totalUsers' => 1,
                'totalOrders' => 1,
                'totalAmount' => $order->total_amount,
                'dateFrom' => null,
                'dateTo' => null
            ]);

            // Create temporary file
            $tempPath = storage_path('app/temp/invoice_' . $order->id . '_' . time() . '.pdf');
            
            // Ensure temp directory exists
            $tempDir = dirname($tempPath);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $pdf->save($tempPath);

            return $tempPath;

        } catch (Exception $e) {
            Log::error('PDF generation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get order placed email template (before payment)
     */
    private function getOrderPlacedEmailTemplate($order)
    {
        $orderItems = $order->orderItems->map(function ($item) {
            return [
                'name' => $item->book->title,
                'quantity' => $item->quantity,
                'price' => '₹' . number_format($item->price, 2),
                'total' => '₹' . number_format($item->price * $item->quantity, 2)
            ];
        });

        $template = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;">
            <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #00BDE0; padding-bottom: 20px;">
                    <h1 style="color: #00BDE0; font-size: 28px; margin: 0;">BOOKSTORE</h1>
                    <p style="color: #666; font-size: 16px; margin: 5px 0 0 0;">Order Placed Successfully</p>
                </div>

                <!-- Greeting -->
                <div style="margin-bottom: 25px;">
                    <h2 style="color: #333; font-size: 22px; margin-bottom: 10px;">Hello ' . $order->user->name . ',</h2>
                    <p style="color: #666; font-size: 16px; line-height: 1.6;">
                        Thank you for placing your order! We have received your order details and are waiting for payment confirmation.
                    </p>
                </div>

                <!-- Payment Pending Notice -->
                <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #856404; font-size: 16px; margin-top: 0; margin-bottom: 10px;">⏳ Payment Pending</h3>
                    <p style="color: #856404; font-size: 14px; margin: 0;">
                        Please complete your payment to confirm this order. Once payment is received, we\'ll send you a detailed confirmation with invoice.
                    </p>
                </div>

                <!-- Order Details -->
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #333; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Order Details</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Order Number:</td>
                            <td style="padding: 8px 0; color: #333;">ORD-' . $order->id . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Order Date:</td>
                            <td style="padding: 8px 0; color: #333;">' . $order->created_at->format('F d, Y') . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Status:</td>
                            <td style="padding: 8px 0; color: #f39c12; font-weight: bold;">Payment Pending</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Total Amount:</td>
                            <td style="padding: 8px 0; color: #333; font-weight: bold; font-size: 18px;">₹' . number_format($order->total_amount, 2) . '</td>
                        </tr>
                    </table>
                </div>

                <!-- Order Items -->
                <div style="margin-bottom: 25px;">
                    <h3 style="color: #333; font-size: 18px; margin-bottom: 15px;">Order Items</h3>
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                        <thead>
                            <tr style="background-color: #f8f9fa;">
                                <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd; color: #666;">Book</th>
                                <th style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd; color: #666;">Qty</th>
                                <th style="padding: 12px; text-align: right; border-bottom: 1px solid #ddd; color: #666;">Price</th>
                                <th style="padding: 12px; text-align: right; border-bottom: 1px solid #ddd; color: #666;">Total</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($orderItems as $item) {
            $template .= '
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #eee; color: #333;">' . $item['name'] . '</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee; color: #333;">' . $item['quantity'] . '</td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #eee; color: #333;">' . $item['price'] . '</td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #eee; color: #333; font-weight: bold;">' . $item['total'] . '</td>
                            </tr>';
        }

        $template .= '
                        </tbody>
                    </table>
                </div>

                <!-- Next Steps -->
                <div style="background-color: #e3f2fd; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #1976d2; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Next Steps</h3>
                    <ol style="color: #666; line-height: 1.6; margin: 0; padding-left: 20px;">
                        <li>Complete your payment using the payment link provided</li>
                        <li>Once payment is confirmed, we\'ll process your order</li>
                        <li>You\'ll receive a detailed confirmation email with invoice</li>
                        <li>Your order will be shipped within 1-2 business days</li>
                    </ol>
                </div>

                <!-- Contact Info -->
                <div style="text-align: center; color: #666; font-size: 14px; border-top: 1px solid #eee; padding-top: 20px;">
                    <p>Need help? Contact us at <a href="mailto:support@store.ipdc.org" style="color: #00BDE0;">support@bookstore.com</a></p>
                    <p style="margin-top: 15px;">Thank you for choosing Bookstore!</p>
                </div>

            </div>
        </div>';

        return $template;
    }

    /**
     * Get order confirmation email template (after payment)
     */
    private function getOrderConfirmationEmailTemplate($order)
    {
        $orderItems = $order->orderItems->map(function ($item) {
            return [
                'name' => $item->book->title,
                'quantity' => $item->quantity,
                'price' => '₹' . number_format($item->price, 2),
                'total' => '₹' . number_format($item->price * $item->quantity, 2)
            ];
        });

        $template = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;">
            <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #00BDE0; padding-bottom: 20px;">
                    <h1 style="color: #00BDE0; font-size: 28px; margin: 0;">BOOKSTORE</h1>
                    <p style="color: #666; font-size: 16px; margin: 5px 0 0 0;">Order Confirmation</p>
                </div>

                <!-- Greeting -->
                <div style="margin-bottom: 25px;">
                    <h2 style="color: #333; font-size: 22px; margin-bottom: 10px;">Hello ' . $order->user->name . ',</h2>
                    <p style="color: #666; font-size: 16px; line-height: 1.6;">
                        Thank you for your order! We\'re excited to confirm that we\'ve received your order and it\'s being processed.
                    </p>
                </div>

                <!-- Order Details -->
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #333; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Order Details</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Order Number:</td>
                            <td style="padding: 8px 0; color: #333;">ORD-' . $order->id . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Order Date:</td>
                            <td style="padding: 8px 0; color: #333;">' . $order->created_at->format('F d, Y') . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Status:</td>
                            <td style="padding: 8px 0; color: #00BDE0; font-weight: bold;">' . ucfirst($order->status) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Total Amount:</td>
                            <td style="padding: 8px 0; color: #333; font-weight: bold; font-size: 18px;">₹' . number_format($order->total_amount, 2) . '</td>
                        </tr>
                    </table>
                </div>

                <!-- Order Items -->
                <div style="margin-bottom: 25px;">
                    <h3 style="color: #333; font-size: 18px; margin-bottom: 15px;">Order Items</h3>
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                        <thead>
                            <tr style="background-color: #f8f9fa;">
                                <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd; color: #666;">Book</th>
                                <th style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd; color: #666;">Qty</th>
                                <th style="padding: 12px; text-align: right; border-bottom: 1px solid #ddd; color: #666;">Price</th>
                                <th style="padding: 12px; text-align: right; border-bottom: 1px solid #ddd; color: #666;">Total</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($orderItems as $item) {
            $template .= '
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #eee; color: #333;">' . $item['name'] . '</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee; color: #333;">' . $item['quantity'] . '</td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #eee; color: #333;">' . $item['price'] . '</td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #eee; color: #333; font-weight: bold;">' . $item['total'] . '</td>
                            </tr>';
        }

        $template .= '
                        </tbody>
                    </table>
                </div>

                <!-- Shipping Address -->
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #333; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Shipping Address</h3>
                    <p style="color: #666; line-height: 1.6; margin: 0;">
                        ' . $order->user->name . '<br>
                        ' . ($order->user->address ?? 'Address not provided') . '<br>
                        Phone: ' . ($order->user->phone ?? 'Not provided') . '<br>
                        Email: ' . $order->user->email . '
                    </p>
                </div>

                <!-- Next Steps -->
                <div style="background-color: #e3f2fd; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #1976d2; font-size: 18px; margin-top: 0; margin-bottom: 15px;">What\'s Next?</h3>
                    <ul style="color: #666; line-height: 1.6; margin: 0; padding-left: 20px;">
                        <li>We\'ll process your order within 1-2 business days</li>
                        <li>You\'ll receive a shipping confirmation email with tracking details</li>
                        <li>Your order will be delivered within 3-7 business days</li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div style="text-align: center; color: #666; font-size: 14px; border-top: 1px solid #eee; padding-top: 20px;">
                    <p>Need help? Contact us at <a href="mailto:support@bookstore.com" style="color: #00BDE0;">support@bookstore.com</a></p>
                    <p style="margin-top: 15px;">Thank you for choosing Bookstore!</p>
                </div>

            </div>
        </div>';

        return $template;
    }

    /**
     * Send general email
     */
    public function sendEmail($to, $subject, $message, $attachments = [])
    {
        try {
            if (!$this->accessToken) {
                $this->getAccessToken();
            }

            if (!$this->accessToken) {
                throw new Exception('Unable to get email access token');
            }

            $notificationEmail = [
                config('mail.notification_email', 'admin@bookstore.com') => config('mail.notification_name', 'Bookstore Admin')
            ];

            $formData = [
                'notification_email' => json_encode($notificationEmail),
                'subject' => $subject,
                'message' => $message,
                'to' => json_encode([$to]),
                'is_online_user' => '2',
                'notification_name' => config('mail.notification_name', 'Bookstore Admin')
            ];

            $request = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken
            ])->asMultipart();

            foreach ($formData as $key => $value) {
                $request = $request->attach($key, $value);
            }

            // Add attachments
            foreach ($attachments as $attachment) {
                if (file_exists($attachment['path'])) {
                    $request = $request->attach('file', fopen($attachment['path'], 'r'), $attachment['name']);
                }
            }

            $response = $request->post($this->baseUrl . '/user/send_smtp_mail');

            return $response->successful();

        } catch (Exception $e) {
            Log::error('Email sending error: ' . $e->getMessage());
            return false;
        }
    }
}
