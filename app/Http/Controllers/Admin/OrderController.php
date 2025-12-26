<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CourierManager;
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
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
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

        // Filter by bulk purchase
        if ($request->filled('is_bulk_purchased')) {
            $query->where('is_bulk_purchased', $request->is_bulk_purchased === '1');
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
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
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

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Order Number',
                'Customer',
                'Email',
                'Status',
                'Payment Status',
                'Total Amount',
                'Order Date',
                'Items Count'
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
     * Create courier order for an existing order.
     */
    public function createCourierOrder(Order $order)
    {
        try {
            if ($order->courier_provider || $order->shiprocket_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Courier order already exists for this order.'
                ], 400);
            }

            // Skip courier for bulk orders with free shipping
            if ($order->is_bulk_purchased) {
                return response()->json([
                    'success' => false,
                    'message' => 'No courier order created for bulk purchase order (free shipping).'
                ], 400);
            }

            $courierManager = app(CourierManager::class);
            $response = $courierManager->createOrder($order);

            if ($response) {
                return response()->json([
                    'success' => true,
                    'message' => 'Courier order created successfully.',
                    'data' => $response
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create courier order. Please check the logs for more details.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating courier order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track shipment with courier provider.
     */
    public function trackShipment(Order $order)
    {
        try {
            $courierManager = app(CourierManager::class);

            // Use the provider from the order, or fall back to active provider
            $trackingReference = $order->courier_document_ref ?? $order->tracking_number ?? $order->shiprocket_order_id;
            $providerName = $order->courier_provider;

            if (!$trackingReference) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tracking reference found for this order.'
                ], 404);
            }

            $trackingData = $courierManager->trackOrder($trackingReference, $providerName);

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
            // Generate invoice PDF
            $pdfPath = $this->generateInvoicePdf($order);

            $emailService = new EmailService();
            $emailSent = $emailService->sendOrderConfirmationEmail($order, $pdfPath);

            // Clean up temporary PDF file
            if ($pdfPath && file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            if ($emailSent) {
                $order->update(['confirmation_email_sent' => true]);
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

        $filename = 'invoice_IPDC-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
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

            \Log::info('Invoice PDF generated for admin order confirmation email', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'pdf_path' => $tempPath
            ]);

            return $tempPath;
        } catch (\Exception $e) {
            \Log::error('Failed to generate invoice PDF for admin order confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
