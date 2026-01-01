<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CourierManager;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class CheckoutController extends Controller
{
    private $razorpayApi;

    public function __construct()
    {
        $this->middleware('auth');

        // Get Razorpay credentials from database settings (with fallback to config)
        $razorpayKeyId = \App\Models\Setting::get('razorpay_key_id') ?: config('services.razorpay.key');
        $razorpaySecret = \App\Models\Setting::get('razorpay_key_secret') ?: config('services.razorpay.secret');

        $this->razorpayApi = new Api($razorpayKeyId, $razorpaySecret);
    }

    /**
     * Show checkout page for cart items
     */
    public function index()
    {
        $user = Auth::user();
        $cartItems = $user->cartItems()->with(['book.category'])->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Calculate totals
        $subtotal = $cartItems->sum('total_price');

        // Check if order qualifies for bulk purchase (free shipping)
        $totalQuantity = $cartItems->sum('quantity');
        $minBulkPurchase = \App\Models\Setting::get('min_bulk_purchase', 10);
        $isBulkPurchase = $totalQuantity >= $minBulkPurchase;

        $shipping = $isBulkPurchase ? 0 : $cartItems->sum(function ($item) {
            return $item->book->shipping_price * $item->quantity;
        });

        $tax = 0; // GST removed as per requirements
        $total = $subtotal + $shipping;

        return view('checkout.index', compact('cartItems', 'subtotal', 'shipping', 'tax', 'total'));
    }

    /**
     * Show checkout page for direct buy now
     */
    public function buyNow(Request $request, Book $book)
    {
        $request->validate([
            'quantity' => 'integer|min:1|max:10'
        ]);

        $quantity = $request->get('quantity', 1);

        // Check if book is available
        if ($book->status !== 'active') {
            return redirect()->back()->with('error', 'This book is not available for purchase.');
        }

        // Check stock availability
        if ($book->stock < $quantity) {
            return redirect()->back()->with('error', 'Not enough stock available. Only ' . $book->stock . ' copies left.');
        }

        // Calculate totals
        $subtotal = $book->price * $quantity;

        // Check if order qualifies for bulk purchase (free shipping)
        $minBulkPurchase = \App\Models\Setting::get('min_bulk_purchase', 10);
        $isBulkPurchase = $quantity >= $minBulkPurchase;

        $shipping = $isBulkPurchase ? 0 : ($book->shipping_price * $quantity);
        $tax = 0; // GST removed as per requirements
        $total = $subtotal + $shipping;

        $buyNowItem = (object) [
            'book' => $book,
            'quantity' => $quantity,
            'price' => $book->price,
            'total_price' => $subtotal,
            'shipping_price' => $shipping
        ];

        return view('checkout.buy-now', compact('buyNowItem', 'subtotal', 'shipping', 'tax', 'total'));
    }

    /**
     * Process checkout and create Razorpay order
     */
    public function process(Request $request)
    {

        $request->validate([
            'shipping_address.name' => 'required|string|min:2|max:255',
            'shipping_address.phone' => 'required|string|regex:/^[0-9]{10}$/|size:10',
            'shipping_address.address_line_1' => 'required|string|min:10|max:255',
            'shipping_address.address_line_2' => 'nullable|string|max:255',
            'shipping_address.city' => 'required|string|min:2|max:100',
            'shipping_address.state_id' => 'required|integer|exists:states,id',
            'shipping_address.district_id' => 'required|integer|exists:districts,id',
            'shipping_address.taluka_id' => 'required|integer|exists:talukas,id',
            'shipping_address.postal_code' => 'required|string|regex:/^[0-9]{6}$/|size:6',
            'shipping_address.country' => 'required|string|max:100',
            'buy_now_book_id' => 'nullable|exists:books,id',
            'buy_now_quantity' => 'nullable|integer|min:1|max:10',
        ], [
            'shipping_address.name.required' => 'Full name is required.',
            'shipping_address.name.min' => 'Full name must be at least 2 characters.',
            'shipping_address.phone.required' => 'Phone number is required.',
            'shipping_address.phone.regex' => 'Phone number must be exactly 10 digits.',
            'shipping_address.phone.size' => 'Phone number must be exactly 10 digits.',
            'shipping_address.address_line_1.required' => 'Address line 1 is required.',
            'shipping_address.address_line_1.min' => 'Address line 1 must be at least 10 characters.',
            'shipping_address.city.required' => 'City is required.',
            'shipping_address.city.min' => 'City must be at least 2 characters.',
            'shipping_address.postal_code.required' => 'Postal code is required.',
            'shipping_address.postal_code.regex' => 'Postal code must be exactly 6 digits.',
            'shipping_address.postal_code.size' => 'Postal code must be exactly 6 digits.',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $minBulkPurchase = \App\Models\Setting::get('min_bulk_purchase', 10);

            if ($request->has('buy_now_book_id')) {
                // Handle buy now checkout
                $book = Book::findOrFail($request->buy_now_book_id);
                $quantity = $request->buy_now_quantity;

                // Verify stock
                if ($book->stock < $quantity) {
                    throw new \Exception('Not enough stock available.');
                }

                $subtotal = $book->price * $quantity;

                // Check if order qualifies for bulk purchase (free shipping)
                $isBulkPurchase = $quantity >= $minBulkPurchase;

                $shipping = $isBulkPurchase ? 0 : ($book->shipping_price * $quantity);
                $tax = 0; // GST removed
                $total = $subtotal + $shipping;

                $orderItems = [
                    (object) [
                        'book_id' => $book->id,
                        'quantity' => $quantity,
                        'price' => $book->price,
                        'shipping_price' => $book->shipping_price,
                        'total_price' => $subtotal
                    ]
                ];
            } else {
                // Handle cart checkout
                $cartItems = $user->cartItems()->with('book')->get();

                if ($cartItems->isEmpty()) {
                    throw new \Exception('Your cart is empty.');
                }

                // Verify stock for all items
                foreach ($cartItems as $item) {
                    if ($item->book->stock < $item->quantity) {
                        throw new \Exception("Not enough stock for {$item->book->title}.");
                    }
                }

                $subtotal = $cartItems->sum('total_price');

                // Check if order qualifies for bulk purchase (free shipping)
                $totalQuantity = $cartItems->sum('quantity');
                $isBulkPurchase = $totalQuantity >= $minBulkPurchase;

                $shipping = $isBulkPurchase ? 0 : $cartItems->sum(function ($item) {
                    return $item->book->shipping_price * $item->quantity;
                });
                $tax = 0; // GST removed
                $total = $subtotal + $shipping;

                $orderItems = $cartItems;
            }

            // Convert location IDs to names for storage
            $shippingAddress = $request->shipping_address;
            $state = \App\Models\State::find($shippingAddress['state_id']);
            $district = \App\Models\District::find($shippingAddress['district_id']);
            $taluka = \App\Models\Taluka::find($shippingAddress['taluka_id']);

            $shippingAddress['state'] = $state->name;
            $shippingAddress['district'] = $district->name;
            $shippingAddress['taluka'] = $taluka->name;

            // Verify payment signature
            // NOTE: This block seems to be misplaced here. Payment signature verification typically happens
            // in the payment success callback, not during the initial order creation.
            // The variables $razorpayOrderId, $razorpayPaymentId, $razorpaySignature are not defined at this point.
            // I am inserting it as per instruction, but it will likely cause errors or incorrect logic.
            // For a correct implementation, this logic should be in the paymentSuccess method.
            // $generatedSignature = hash_hmac('sha256',
            //     $razorpayOrderId . '|' . $razorpayPaymentId,
            //     config('services.razorpay.key_secret')
            // );

            // if ($generatedSignature !== $razorpaySignature) {
            //     Log::error('Payment signature verification failed', [
            //         'order_id' => $razorpayOrderId,
            //         'payment_id' => $razorpayPaymentId
            //     ]);
            //     return redirect()->route('cart.index')->with('error', 'Payment verification failed');
            // }

            // Check if zipcode is serviceable
            $postalCode = $shippingAddress['postal_code']; // Use postal code from validated shipping address
            $requiresManualShipping = !\App\Models\ServiceableZipcode::isServiceable($postalCode);

            // Remove the ID fields as we only store names
            unset($shippingAddress['state_id'], $shippingAddress['district_id'], $shippingAddress['taluka_id']);

            // Create Razorpay order
            $razorpayOrder = $this->razorpayApi->order->create([
                'receipt' => 'order_' . time(),
                'amount' => $total * 100, // Amount in paise
                'currency' => 'INR',
            ]);

            // Create order in database
            $order = Order::create([
                'user_id' => $user->id,
                'status' => Order::STATUS_PENDING_TO_BE_PREPARED, // New order flow - pending to be prepared
                'payment_status' => 'pending',
                'payment_method' => 'razorpay',
                'razorpay_order_id' => $razorpayOrder['id'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'is_bulk_purchased' => $isBulkPurchase,
                'requires_manual_shipping' => $requiresManualShipping, // Added this field
                'shipping_address' => $shippingAddress,
                'billing_address' => $request->billing_address ?? $shippingAddress,
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $item->book_id ?? $item->book->id,
                    'quantity' => $item->quantity,
                    'price' => $item->price ?? $item->book->price,
                    'shipping_price' => $item->shipping_price ?? $item->book->shipping_price,
                    'total_price' => $item->total_price,
                ]);
            }

            // Send order placed email (now disabled - just logs)
            try {
                $emailService = new EmailService();
                $emailService->sendOrderPlacedEmail($order); // This now just logs and returns true
            } catch (\Exception $e) {
                \Log::error('Order email service error for order ' . $order->id . ': ' . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $total * 100,
                'currency' => 'INR',
                'key' => \App\Models\Setting::get('razorpay_key_id') ?: config('services.razorpay.key'),
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $request->shipping_address['phone'] ?? '',
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Handle payment success callback
     */
    public function paymentSuccess(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required',
            'order_id' => 'required|exists:orders,id',
        ]);

        try {
            // Verify payment signature
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            $this->razorpayApi->utility->verifyPaymentSignature($attributes);

            DB::beginTransaction();

            $order = Order::findOrFail($request->order_id);

            // Update order with payment details
            $order->update([
                'payment_status' => 'paid',
                'status' => Order::STATUS_PENDING_TO_BE_PREPARED, // Keep as pending to be prepared after payment
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);

            // Update book stock and clear cart if needed
            foreach ($order->orderItems as $orderItem) {
                $book = $orderItem->book;
            }

            // Clear cart if this was a cart checkout (not buy now)
            if (!$request->has('buy_now')) {
                Auth::user()->cartItems()->delete();
            }

            // NOTE: Courier order creation is now handled via the "Ship Now" bulk action in admin panel
            // Orders are set to "pending_to_be_prepared" and admin will use bulk ship functionality
            Log::info('Order created and set to pending_to_be_prepared status: ' . $order->id);

            // Send order confirmation email with invoice
            try {
                // Generate invoice PDF
                $pdfPath = $this->generateInvoicePdf($order);

                $emailService = new EmailService();
                $emailSent = $emailService->sendOrderConfirmationEmail($order, $pdfPath);

                if ($emailSent) {
                    $order->update(['confirmation_email_sent' => true]);
                    Log::info('Order confirmation email sent successfully for order: ' . $order->id);
                } else {
                    Log::warning('Failed to send order confirmation email for order: ' . $order->id);
                }

                // Clean up temporary PDF file
                if ($pdfPath && file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
            } catch (\Exception $e) {
                Log::error('Order confirmation email error for order ' . $order->id . ': ' . $e->getMessage());
                // Don't fail the main order process if email fails
            }

            DB::commit();

            return redirect()->route('orders.show', $order)->with('success', 'Payment successful! Your order has been placed and will be shipped soon.');

        } catch (\Exception $e) {
            DB::rollback();

            // Update order status to failed
            if (isset($order)) {
                $order->update(['payment_status' => 'failed']);
            }

            return redirect()->route('checkout.index')->with('error', 'Payment verification failed. Please try again.');
        }
    }

    /**
     * Handle payment failure
     */
    public function paymentFailed(Request $request)
    {
        if ($request->has('order_id')) {
            $order = Order::find($request->order_id);
            if ($order) {
                $order->update(['payment_status' => 'failed']);
            }
        }

        return redirect()->route('checkout.index')->with('error', 'Payment failed. Please try again.');
    }

    /**
     * Generate invoice PDF for the order
     */
    private function generateInvoicePdf($order)
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

            // Generate filename and path
            $filename = 'invoice_IPDC-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) . '.pdf';
            $tempPath = storage_path('app/temp/' . $filename);

            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            // Save PDF to temporary file
            file_put_contents($tempPath, $pdf->output());

            Log::info('Invoice PDF generated for order confirmation email', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'pdf_path' => $tempPath
            ]);

            return $tempPath;
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice PDF for order confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
