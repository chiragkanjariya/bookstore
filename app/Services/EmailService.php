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
     * Note: This method has been disabled as per requirements - no payment pending emails
     */
    public function sendOrderPlacedEmail($order)
    {
        // Payment pending emails are disabled as per requirements
        Log::info('Order placed email skipped for order: ' . $order->id);
        return true; // Return true to avoid breaking the checkout flow
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
                'is_online_user' => 0,
                'notification_name' => config('mail.notification_name', 'Bookstore Admin')
            ];

            Log::info('Sending email via API', [
                'to' => $to,
                'subject' => $subject,
                'form_data' => $formData
            ]);

            // Use cURL directly to match the working curl command exactly
            $curl = curl_init();

            $postFields = [];

            // Add form fields
            foreach ($formData as $key => $value) {
                $postFields[$key] = $value;
            }

            // Add file attachments if provided
            foreach ($attachments as $attachment) {
                if (file_exists($attachment['path'])) {
                    $postFields['file'] = new \CURLFile($attachment['path'], 'application/pdf', $attachment['name']);
                }
            }

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->baseUrl . '/user/send_smtp_mail',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->accessToken
                ],
            ]);

            $response_body = curl_exec($curl);
            $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($curl);
            curl_close($curl);

            // Create a response object similar to Laravel's HTTP response
            $response = new class ($response_body, $response_code, $curl_error) {
                private $body;
                private $status;
                private $error;

                public function __construct($body, $status, $error)
                {
                    $this->body = $body;
                    $this->status = $status;
                    $this->error = $error;
                }

                public function body()
                {
                    return $this->body;
                }

                public function status()
                {
                    return $this->status;
                }

                public function successful()
                {
                    return $this->status >= 200 && $this->status < 300 && empty($this->error);
                }
            };

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
            $orderNumber = '#IPDC' . str_pad($order->id, 5, '0', STR_PAD_LEFT);

            // Generate invoice PDF if not provided
            if (!$pdfPath) {
                $pdfPath = $this->generateInvoicePDF($order);
            }

            // Prepare email content
            $subject = "Order Confirmation - #IPDC" . str_pad($order->id, 5, '0', STR_PAD_LEFT);
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
            // Load order relationships
            $order->load(['orderItems.book.category', 'user.state', 'user.district', 'user.taluka']);

            // Use the new structure with orders collection
            $orders = collect([$order]);

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('admin.reports.accounts.combined-invoice', [
                'orders' => $orders,
                'totalOrders' => 1,
                'totalAmount' => $order->total_amount,
                'totalShipping' => $order->shipping_cost
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
                            <td style="padding: 8px 0; color: #333;">#IPDC' . str_pad($order->id, 5, '0', STR_PAD_LEFT) . '</td>
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
                    <h3 style="color: #333; font-size: 18px; margin-bottom: 15px;">Order Items</h3>';

        foreach ($orderItems as $item) {
            $template .= '
                    <div style="background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 12px;">
                        <div style="margin-bottom: 8px;">
                            <strong style="color: #333; font-size: 16px;">' . $item['name'] . '</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <span style="color: #666; font-size: 14px;">Quantity:</span>
                            <span style="color: #333; font-weight: bold;">' . $item['quantity'] . '</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <span style="color: #666; font-size: 14px;">Unit Price:</span>
                            <span style="color: #333;">' . $item['price'] . '</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #ddd; padding-top: 8px; margin-top: 8px;">
                            <span style="color: #333; font-weight: bold; font-size: 15px;">Total:</span>
                            <span style="color: #00BDE0; font-weight: bold; font-size: 15px;">' . $item['total'] . '</span>
                        </div>
                    </div>';
        }

        $template .= '
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
                    <h1 style="color: #00BDE0; font-size: 28px; margin: 0;">' . (\App\Models\Setting::get('company_name') ?: 'IPDC') . '</h1>
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
                            <td style="padding: 8px 0; color: #333;">#IPDC' . str_pad($order->id, 5, '0', STR_PAD_LEFT) . '</td>
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
                    <h3 style="color: #333; font-size: 18px; margin-bottom: 15px;">Order Items</h3>';

        foreach ($orderItems as $item) {
            $template .= '
                    <div style="background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 12px;">
                        <div style="margin-bottom: 8px;">
                            <strong style="color: #333; font-size: 16px;">' . $item['name'] . '</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <span style="color: #666; font-size: 14px;">Quantity:</span>
                            <span style="color: #333; font-weight: bold;">' . $item['quantity'] . '</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <span style="color: #666; font-size: 14px;">Unit Price:</span>
                            <span style="color: #333;">' . $item['price'] . '</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #ddd; padding-top: 8px; margin-top: 8px;">
                            <span style="color: #333; font-weight: bold; font-size: 15px;">Total:</span>
                            <span style="color: #00BDE0; font-weight: bold; font-size: 15px;">' . $item['total'] . '</span>
                        </div>
                    </div>';
        }

        $template .= '
                </div>

                <!-- Order Summary -->
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #333; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Order Summary</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; color: #666;">Subtotal:</td>
                            <td style="padding: 8px 0; text-align: right; color: #333;">₹' . number_format($order->subtotal, 2) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666;">Shipping:</td>
                            <td style="padding: 8px 0; text-align: right; color: #333;">₹' . number_format($order->shipping_cost, 2) . '</td>
                        </tr>
                        <tr style="border-top: 2px solid #00BDE0;">
                            <td style="padding: 12px 0; color: #333; font-weight: bold; font-size: 16px;">Total Amount:</td>
                            <td style="padding: 12px 0; text-align: right; color: #00BDE0; font-weight: bold; font-size: 16px;">₹' . number_format($order->subtotal + $order->shipping_cost, 2) . '</td>
                        </tr>
                    </table>
                </div>

                <!-- Shipping Address -->
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #333; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Shipping Address</h3>
                    <p style="color: #666; line-height: 1.6; margin: 0;">
                        ' . ($order->shipping_address['name'] ?? $order->user->name) . '<br>
                        ' . ($order->shipping_address['address_line_1'] ?? '') . '<br>
                        ' . ($order->shipping_address['address_line_2'] ? $order->shipping_address['address_line_2'] . '<br>' : '') . '
                        ' . ($order->shipping_address['city'] ?? '') . '<br>
                        ' . ($order->shipping_address['taluka'] ?? '') . ', ' . ($order->shipping_address['district'] ?? '') . '<br>
                        ' . ($order->shipping_address['state'] ?? '') . ' - ' . ($order->shipping_address['postal_code'] ?? '') . '<br>
                        ' . ($order->shipping_address['country'] ?? 'India') . '<br><br>
                        Phone: ' . ($order->shipping_address['phone'] ?? $order->user->phone ?? 'Not provided') . '<br>
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
                'is_online_user' => '0',
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

    /**
     * Send order ready to ship notification email
     */
    public function sendOrderReadyToShipEmail($order)
    {
        try {
            if (!$this->accessToken) {
                $this->getAccessToken();
            }

            if (!$this->accessToken) {
                throw new Exception('Unable to get email access token');
            }

            $customerEmail = $order->user->email;
            $customerName = $order->user->name;
            $orderNumber = '#IPDC' . str_pad($order->id, 5, '0', STR_PAD_LEFT);

            $subject = "Your Order is Ready to Ship - {$orderNumber}";
            $message = $this->getOrderReadyToShipEmailTemplate($order);

            $to = [
                $customerEmail => $customerName
            ];

            $result = $this->sendEmailViaAPI($to, $subject, $message);

            if ($result) {
                Log::info('Order ready to ship email sent successfully', [
                    'order_id' => $order->id,
                    'customer_email' => $customerEmail
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Order ready to ship email error: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null
            ]);
            return false;
        }
    }

    /**
     * Send order shipped notification email
     */
    public function sendOrderShippedEmail($order)
    {
        try {
            if (!$this->accessToken) {
                $this->getAccessToken();
            }

            if (!$this->accessToken) {
                throw new Exception('Unable to get email access token');
            }

            $customerEmail = $order->user->email;
            $customerName = $order->user->name;
            $orderNumber = '#IPDC' . str_pad($order->id, 5, '0', STR_PAD_LEFT);

            $subject = "Your Order Has Been Shipped - {$orderNumber}";
            $message = $this->getOrderShippedEmailTemplate($order);

            $to = [
                $customerEmail => $customerName
            ];

            $result = $this->sendEmailViaAPI($to, $subject, $message);

            if ($result) {
                Log::info('Order shipped email sent successfully', [
                    'order_id' => $order->id,
                    'customer_email' => $customerEmail
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Order shipped email error: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null
            ]);
            return false;
        }
    }

    /**
     * Send order delivered notification email
     */
    public function sendOrderDeliveredEmail($order)
    {
        try {
            if (!$this->accessToken) {
                $this->getAccessToken();
            }

            if (!$this->accessToken) {
                throw new Exception('Unable to get email access token');
            }

            $customerEmail = $order->user->email;
            $customerName = $order->user->name;
            $orderNumber = '#IPDC' . str_pad($order->id, 5, '0', STR_PAD_LEFT);

            $subject = "Your Order Has Been Delivered - {$orderNumber}";
            $message = $this->getOrderDeliveredEmailTemplate($order);

            $to = [
                $customerEmail => $customerName
            ];

            $result = $this->sendEmailViaAPI($to, $subject, $message);

            if ($result) {
                Log::info('Order delivered email sent successfully', [
                    'order_id' => $order->id,
                    'customer_email' => $customerEmail
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Order delivered email error: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null
            ]);
            return false;
        }
    }

    /**
     * Get order ready to ship email template
     */
    private function getOrderReadyToShipEmailTemplate($order)
    {
        $orderNumber = '#IPDC' . str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;">
            <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #00BDE0; padding-bottom: 20px;">
                    <h1 style="color: #00BDE0; font-size: 28px; margin: 0;">' . (\App\Models\Setting::get('company_name') ?: 'IPDC') . '</h1>
                    <p style="color: #666; font-size: 16px; margin: 5px 0 0 0;">Order Ready to Ship</p>
                </div>

                <!-- Greeting -->
                <div style="margin-bottom: 25px;">
                    <h2 style="color: #333; font-size: 22px; margin-bottom: 10px;">Hello ' . $order->user->name . ',</h2>
                    <p style="color: #666; font-size: 16px; line-height: 1.6;">
                        Great news! Your order ' . $orderNumber . ' is ready to ship and will be dispatched soon.
                    </p>
                </div>

                <!-- Order Details -->
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #333; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Order Details</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Order Number:</td>
                            <td style="padding: 8px 0; color: #333;">' . $orderNumber . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Order Date:</td>
                            <td style="padding: 8px 0; color: #333;">' . $order->created_at->format('F d, Y') . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Status:</td>
                            <td style="padding: 8px 0; color: #00BDE0; font-weight: bold;">Ready to Ship</td>
                        </tr>
                    </table>
                </div>

                <!-- Next Steps -->
                <div style="background-color: #e3f2fd; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #1976d2; font-size: 18px; margin-top: 0; margin-bottom: 15px;">What\'s Next?</h3>
                    <ul style="color: #666; line-height: 1.6; margin: 0; padding-left: 20px;">
                        <li>Your order will be shipped within 1-2 business days</li>
                        <li>You\'ll receive a shipping confirmation email with tracking details</li>
                        <li>Your order will be delivered within 3-7 business days</li>
                    </ul>
                </div>

            </div>
        </div>';
    }

    /**
     * Get order shipped email template
     */
    private function getOrderShippedEmailTemplate($order)
    {
        $orderNumber = '#IPDC' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
        $trackingNumber = $order->tracking_number ?? $order->courier_awb_number ?? 'N/A';

        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;">
            <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #00BDE0; padding-bottom: 20px;">
                    <h1 style="color: #00BDE0; font-size: 28px; margin: 0;">' . (\App\Models\Setting::get('company_name') ?: 'IPDC') . '</h1>
                    <p style="color: #666; font-size: 16px; margin: 5px 0 0 0;">Order Shipped</p>
                </div>

                <!-- Greeting -->
                <div style="margin-bottom: 25px;">
                    <h2 style="color: #333; font-size: 22px; margin-bottom: 10px;">Hello ' . $order->user->name . ',</h2>
                    <p style="color: #666; font-size: 16px; line-height: 1.6;">
                        Your order ' . $orderNumber . ' has been shipped and is on its way to you!
                    </p>
                </div>

                <!-- Tracking Information -->
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #333; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Tracking Information</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Order Number:</td>
                            <td style="padding: 8px 0; color: #333;">' . $orderNumber . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Tracking Number:</td>
                            <td style="padding: 8px 0; color: #00BDE0; font-weight: bold;">' . $trackingNumber . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Shipped Date:</td>
                            <td style="padding: 8px 0; color: #333;">' . ($order->shipped_at ? $order->shipped_at->format('F d, Y') : now()->format('F d, Y')) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Courier:</td>
                            <td style="padding: 8px 0; color: #333;">Shree Maruti Courier</td>
                        </tr>
                    </table>
                </div>

                <!-- Delivery Information -->
                <div style="background-color: #e3f2fd; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #1976d2; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Delivery Information</h3>
                    <ul style="color: #666; line-height: 1.6; margin: 0; padding-left: 20px;">
                        <li>Expected delivery: 3-7 business days</li>
                        <li>You\'ll receive a notification when your order is delivered</li>
                        <li>Please keep your tracking number for reference</li>
                    </ul>
                </div>

            </div>
        </div>';
    }

    /**
     * Get order delivered email template
     */
    private function getOrderDeliveredEmailTemplate($order)
    {
        $orderNumber = '#IPDC' . str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;">
            <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #00BDE0; padding-bottom: 20px;">
                    <h1 style="color: #00BDE0; font-size: 28px; margin: 0;">' . (\App\Models\Setting::get('company_name') ?: 'IPDC') . '</h1>
                    <p style="color: #666; font-size: 16px; margin: 5px 0 0 0;">Order Delivered</p>
                </div>

                <!-- Greeting -->
                <div style="margin-bottom: 25px;">
                    <h2 style="color: #333; font-size: 22px; margin-bottom: 10px;">Hello ' . $order->user->name . ',</h2>
                    <p style="color: #666; font-size: 16px; line-height: 1.6;">
                        Great news! Your order ' . $orderNumber . ' has been successfully delivered.
                    </p>
                </div>

                <!-- Order Details -->
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #333; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Order Details</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Order Number:</td>
                            <td style="padding: 8px 0; color: #333;">' . $orderNumber . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Delivered Date:</td>
                            <td style="padding: 8px 0; color: #333;">' . ($order->delivered_at ? $order->delivered_at->format('F d, Y') : now()->format('F d, Y')) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666; font-weight: bold;">Status:</td>
                            <td style="padding: 8px 0; color: #28a745; font-weight: bold;">Delivered</td>
                        </tr>
                    </table>
                </div>

                <!-- Thank You Message -->
                <div style="background-color: #d4edda; padding: 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #c3e6cb;">
                    <h3 style="color: #155724; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Thank You!</h3>
                    <p style="color: #155724; line-height: 1.6; margin: 0;">
                        We hope you enjoy your purchase. If you have any questions or concerns about your order, please don\'t hesitate to contact us.
                    </p>
                </div>

                <!-- Feedback Request -->
                <div style="background-color: #e3f2fd; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="color: #1976d2; font-size: 18px; margin-top: 0; margin-bottom: 15px;">We Value Your Feedback</h3>
                    <p style="color: #666; line-height: 1.6; margin: 0;">
                        Your satisfaction is important to us. If you have a moment, we\'d love to hear about your experience with us.
                    </p>
                </div>

            </div>
        </div>';
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($user, $token)
    {
        try {
            if (!$this->accessToken) {
                $this->getAccessToken();
            }

            if (!$this->accessToken) {
                throw new Exception('Unable to get email access token');
            }

            $resetUrl = route('password.reset', $token) . '?email=' . urlencode($user->email);

            $subject = "Reset Your Password - " . (\App\Models\Setting::get('company_name') ?: 'IPDC');
            $message = $this->getPasswordResetEmailTemplate($user, $resetUrl);

            $to = [
                $user->email => $user->name
            ];

            $result = $this->sendEmailViaAPI($to, $subject, $message);

            if ($result) {
                Log::info('Password reset email sent successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Password reset email error: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null
            ]);
            return false;
        }
    }

    /**
     * Get password reset email template
     */
    private function getPasswordResetEmailTemplate($user, $url)
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;">
            <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #00BDE0; padding-bottom: 20px;">
                    <h1 style="color: #00BDE0; font-size: 28px; margin: 0;">' . (\App\Models\Setting::get('company_name') ?: 'IPDC') . '</h1>
                    <p style="color: #666; font-size: 16px; margin: 5px 0 0 0;">Password Reset Request</p>
                </div>

                <!-- Greeting -->
                <div style="margin-bottom: 25px;">
                    <h2 style="color: #333; font-size: 22px; margin-bottom: 10px;">Hello ' . $user->name . ',</h2>
                    <p style="color: #666; font-size: 16px; line-height: 1.6;">
                        You are receiving this email because we received a password reset request for your account.
                    </p>
                </div>

                <!-- Action Button -->
                <div style="text-align: center; margin-bottom: 30px;">
                    <a href="' . $url . '" style="display: inline-block; background-color: #00BDE0; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 5px; font-weight: bold; font-size: 16px;">Reset Password</a>
                </div>

                <!-- Expiry Notice -->
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <p style="color: #666; font-size: 14px; margin: 0; text-align: center;">
                        This password reset link will expire in 60 minutes.
                    </p>
                </div>

                <!-- Fallback Link -->
                <div style="margin-bottom: 25px;">
                    <p style="color: #666; font-size: 14px; line-height: 1.6; word-break: break-all;">
                        If you\'re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
                        <a href="' . $url . '" style="color: #00BDE0;">' . $url . '</a>
                    </p>
                </div>

                <!-- Security Notice -->
                <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                    <p style="color: #856404; font-size: 14px; margin: 0;">
                        If you did not request a password reset, no further action is required.
                    </p>
                </div>

                <!-- Footer -->
                <div style="text-align: center; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                    <p style="color: #999; font-size: 12px; margin: 0;">
                        &copy; ' . date('Y') . ' ' . (\App\Models\Setting::get('company_name') ?: 'IPDC') . '. All rights reserved.
                    </p>
                </div>
            </div>
        </div>';
    }
}
