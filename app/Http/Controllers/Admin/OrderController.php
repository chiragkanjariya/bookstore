<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ShiprocketService;
use App\Services\EmailService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of all orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.book'])
                     ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by order number or user name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', '%' . $search . '%')
                               ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(20)->withQueryString();

        // Get statistics
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
        ];

        return view('admin.orders.index', compact('orders', 'stats', 'request'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.book.category']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $oldStatus = $order->status;
        $order->update([
            'status' => $request->status,
            'shipped_at' => $request->status === 'shipped' ? now() : $order->shipped_at,
            'delivered_at' => $request->status === 'delivered' ? now() : $order->delivered_at,
        ]);

        // If order is cancelled, restore stock
        if ($request->status === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->orderItems as $orderItem) {
                $orderItem->book->increment('stock', $orderItem->quantity);
            }
        }

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Update the payment status.
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded'
        ]);

        $order->update(['payment_status' => $request->payment_status]);

        return redirect()->back()->with('success', 'Payment status updated successfully.');
    }

    /**
     * Bulk update order statuses.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $orders = Order::whereIn('id', $request->order_ids)->get();
        
        foreach ($orders as $order) {
            $oldStatus = $order->status;
            $order->update([
                'status' => $request->status,
                'shipped_at' => $request->status === 'shipped' ? now() : $order->shipped_at,
                'delivered_at' => $request->status === 'delivered' ? now() : $order->delivered_at,
            ]);

            // If order is cancelled, restore stock
            if ($request->status === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($order->orderItems as $orderItem) {
                    $orderItem->book->increment('stock', $orderItem->quantity);
                }
            }
        }

        return redirect()->back()->with('success', count($request->order_ids) . ' orders updated successfully.');
    }

    /**
     * Export orders to CSV.
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'orderItems.book']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', '%' . $search . '%')
                               ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        $orders = $query->get();

        $filename = 'orders_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Order Number', 'Customer', 'Email', 'Status', 'Payment Status',
                'Total Amount', 'Order Date', 'Items Count'
            ]);
            
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->user->name,
                    $order->user->email,
                    ucfirst($order->status),
                    ucfirst($order->payment_status),
                    '₹' . number_format($order->total_amount, 2),
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->orderItems->count()
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Create Shiprocket order for an existing order.
     */
    public function createShiprocketOrder(Order $order)
    {
        try {
            if ($order->shiprocket_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shiprocket order already exists for this order.'
                ], 400);
            }

            $shiprocketService = new ShiprocketService();
            $response = $shiprocketService->createOrder($order);

            if ($response) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shiprocket order created successfully.',
                    'data' => $response
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create Shiprocket order. Please check the logs for more details.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating Shiprocket order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track shipment in Shiprocket.
     */
    public function trackShipment($shiprocketOrderId)
    {
        try {
            $shiprocketService = new ShiprocketService();
            $trackingData = $shiprocketService->trackOrder($shiprocketOrderId);

            if ($trackingData) {
                return response()->json([
                    'success' => true,
                    'tracking_data' => $trackingData
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch tracking information.'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error tracking shipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send order confirmation email to customer.
     */
    public function sendOrderConfirmation(Request $request, Order $order)
    {
        try {
            $emailService = new EmailService();
            $emailSent = $emailService->sendOrderConfirmationEmail($order);
            
            if ($emailSent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order confirmation email sent successfully!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send order confirmation email.'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate and download invoice PDF for the order (same as email format).
     */
    public function invoice(Order $order)
    {
        // Only allow invoice download for paid orders
        if ($order->payment_status !== 'paid') {
            return redirect()->back()->with('error', 'Invoice is only available for paid orders.');
        }

        $order->load(['orderItems.book.category', 'user']);
        
        // Structure the data the same way as the email service does
        $user = $order->user;
        $user->orders = collect([$order]);
        $users = collect([$user]);
        
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.reports.accounts.combined-invoice', [
            'users' => $users,
            'totalUsers' => 1,
            'totalOrders' => 1,
            'totalAmount' => $order->total_amount,
            'dateFrom' => null,
            'dateTo' => null
        ]);

        $filename = 'invoice_' . $order->order_number . '.pdf';
        
        return $pdf->download($filename);
    }
}
