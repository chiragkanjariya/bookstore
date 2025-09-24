<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ShiprocketService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Razorpay\Api\Api;

class CheckoutController extends Controller
{
    private $razorpayApi;

    public function __construct()
    {
        $this->middleware('auth');
        $this->razorpayApi = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
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
        $shipping = $cartItems->sum(function ($item) {
            return $item->book->shipping_price * $item->quantity;
        });
        $tax = $subtotal * 0.18; // 18% GST
        $total = $subtotal + $shipping + $tax;

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
        $shipping = $book->shipping_price * $quantity;
        $tax = $subtotal * 0.18; // 18% GST
        $total = $subtotal + $shipping + $tax;

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
            if ($request->has('buy_now_book_id')) {
                // Handle buy now checkout
                $book = Book::findOrFail($request->buy_now_book_id);
                $quantity = $request->buy_now_quantity;

                // Verify stock
                if ($book->stock < $quantity) {
                    throw new \Exception('Not enough stock available.');
                }

                $subtotal = $book->price * $quantity;
                $shipping = $book->shipping_price * $quantity;
                $tax = $subtotal * 0.18;
                $total = $subtotal + $shipping + $tax;

                $orderItems = [(object) [
                    'book_id' => $book->id,
                    'quantity' => $quantity,
                    'price' => $book->price,
                    'shipping_price' => $book->shipping_price,
                    'total_price' => $subtotal
                ]];
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
                $shipping = $cartItems->sum(function ($item) {
                    return $item->book->shipping_price * $item->quantity;
                });
                $tax = $subtotal * 0.18;
                $total = $subtotal + $shipping + $tax;

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
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'razorpay',
                'razorpay_order_id' => $razorpayOrder['id'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'tax_amount' => $tax,
                'total_amount' => $total,
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
                'key' => config('services.razorpay.key'),
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
                'status' => 'processing',
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);

            // Update book stock and clear cart if needed
            foreach ($order->orderItems as $orderItem) {
                $book = $orderItem->book;
                $book->decrement('stock', $orderItem->quantity);
            }

            // Clear cart if this was a cart checkout (not buy now)
            if (!$request->has('buy_now')) {
                Auth::user()->cartItems()->delete();
            }

            // Create Shiprocket order for delivery
            try {
                $shiprocketService = new ShiprocketService();
                $shiprocketResponse = $shiprocketService->createOrder($order);
                
                if ($shiprocketResponse) {
                    \Log::info('Shiprocket order created successfully for order: ' . $order->id);
                } else {
                    \Log::warning('Failed to create Shiprocket order for order: ' . $order->id);
                }
            } catch (\Exception $e) {
                \Log::error('Shiprocket order creation failed for order: ' . $order->id . '. Error: ' . $e->getMessage());
                // Don't fail the main order process if Shiprocket fails
            }

            // Send order confirmation email with invoice
            try {
                $emailService = new EmailService();
                $emailSent = $emailService->sendOrderConfirmationEmail($order);
                
                if ($emailSent) {
                    \Log::info('Order confirmation email sent successfully for order: ' . $order->id);
                } else {
                    \Log::warning('Failed to send order confirmation email for order: ' . $order->id);
                }
            } catch (\Exception $e) {
                \Log::error('Order confirmation email error for order ' . $order->id . ': ' . $e->getMessage());
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
}
