<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ManualCourier;
use App\Services\EmailService;
use App\Helpers\AWBNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ManualShippingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display manual orders (non-serviceable, non-bulk)
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.book'])
            ->manualOrders()
            ->orderBy('created_at', 'desc');

        // Filter by status (default to not_shipped / not shipped)
        $status = $request->input('status', 'not_shipped');
        if (!empty($status)) {
            if ($status === 'not_shipped') {
                $query->whereNull('manual_shipping_marked_at')->where('status', '!=', 'delivered');
            } elseif ($status === 'pending') {
                $query->whereNull('manual_shipping_marked_at')->whereIn('status', ['pending', 'pending_to_be_prepared']);
            } elseif ($status === 'shipped') {
                $query->whereNotNull('manual_shipping_marked_at')->where('status', '!=', 'delivered');
            } elseif ($status === 'delivered') {
                $query->where('status', 'delivered');
            }
            $request->merge(['status' => $status]);
        }

        // Filter by payment status
        $paymentStatus = $request->input('payment_status', 'paid');
        if (!empty($paymentStatus)) {
            $query->where('payment_status', $paymentStatus);
            $request->merge(['payment_status' => $paymentStatus]);
        }

        // Search by order number or customer name
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

        $orders = $query->paginate(20)->withQueryString();

        // Get statistics
        $stats = [
            'total' => Order::manualOrders()->count(),
            'pending' => Order::manualOrders()->whereNull('manual_shipping_marked_at')->where('status', '!=', 'delivered')->count(),
            'shipped' => Order::manualOrders()->whereNotNull('manual_shipping_marked_at')->where('status', '!=', 'delivered')->count(),
            'delivered' => Order::manualOrders()->where('status', 'delivered')->count(),
        ];

        $manualCouriers = ManualCourier::active()->orderBy('name')->get();

        return view('admin.manual-shipping.index', compact('orders', 'stats', 'request', 'manualCouriers'));
    }

    /**
     * Mark single order as manually shipped with tracking data
     */
    public function markAsShipped(Request $request, Order $order)
    {
        $request->validate([
            'manual_courier_id' => 'required|exists:manual_couriers,id',
            'manual_tracking_id' => 'required|string|max:255',
        ]);

        if (!$order->requires_manual_shipping) {
            return response()->json([
                'success' => false,
                'message' => 'This order does not require manual shipping'
            ], 400);
        }

        if ($order->isManuallyShipped()) {
            return response()->json([
                'success' => false,
                'message' => 'Order already marked as shipped'
            ], 400);
        }

        $order->markAsManuallyShipped([
            'manual_courier_id' => $request->manual_courier_id,
            'manual_tracking_id' => $request->manual_tracking_id,
        ]);

        // Send email notification to customer
        try {
            $order->load(['user', 'orderItems.book']);
            $emailService = new EmailService();
            $emailService->sendManualShippingEmail($order);
        } catch (\Exception $e) {
            Log::error('Failed to send manual shipping email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        Log::info('Order marked as manually shipped', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'courier_id' => $request->manual_courier_id,
            'tracking_id' => $request->manual_tracking_id,
            'marked_by' => auth()->user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order marked as shipped successfully'
        ]);
    }

    /**
     * Bulk mark orders as manually shipped
     */
    public function bulkMarkAsShipped(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'manual_courier_id' => 'required|exists:manual_couriers,id',
            'manual_tracking_ids' => 'required|array',
        ]);

        $orders = Order::whereIn('id', $request->order_ids)
            ->manualOrders()
            ->whereNull('manual_shipping_marked_at')
            ->get();

        $count = 0;
        $emailService = new EmailService();

        foreach ($orders as $order) {
            $trackingId = $request->manual_tracking_ids[$order->id] ?? null;
            if (!$trackingId) continue;

            if ($order->markAsManuallyShipped([
                'manual_courier_id' => $request->manual_courier_id,
                'manual_tracking_id' => $trackingId,
            ])) {
                $count++;

                // Send email notification
                try {
                    $order->load(['user', 'orderItems.book']);
                    $emailService->sendManualShippingEmail($order);
                } catch (\Exception $e) {
                    Log::error('Failed to send manual shipping email', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        Log::info('Bulk mark as manually shipped', [
            'count' => $count,
            'marked_by' => auth()->user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} orders marked as shipped successfully"
        ]);
    }

    /**
     * Export manual shipping orders to CSV
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'orderItems.book'])
            ->manualOrders();

        // Apply same filters as index
        $status = $request->input('status', 'not_shipped');
        if (!empty($status)) {
            if ($status === 'not_shipped') {
                $query->whereNull('manual_shipping_marked_at')->where('status', '!=', 'delivered');
            } elseif ($status === 'pending') {
                $query->whereNull('manual_shipping_marked_at')->whereIn('status', ['pending', 'pending_to_be_prepared']);
            } elseif ($status === 'shipped') {
                $query->whereNotNull('manual_shipping_marked_at')->where('status', '!=', 'delivered');
            } elseif ($status === 'delivered') {
                $query->where('status', 'delivered');
            }
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

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->get();

        $filename = 'manual_orders_' . date('Y-m-d_H-i-s') . '.csv';

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
                'Phone',
                'Shipping Address',
                'Postal Code',
                'City',
                'State',
                'Total Amount',
                'Order Date',
                'Shipping Status',
                'Courier',
                'Tracking ID',
                'Marked Shipped At'
            ]);

            foreach ($orders as $order) {
                $shippingAddress = $order->shipping_address;

                fputcsv($file, [
                    $order->order_number,
                    $order->user->name,
                    $order->user->email,
                    $shippingAddress['phone'] ?? '',
                    $shippingAddress['address_line_1'] ?? '',
                    $shippingAddress['postal_code'] ?? '',
                    $shippingAddress['city'] ?? '',
                    $shippingAddress['state'] ?? '',
                    '₹' . number_format($order->total_amount, 2),
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->status === 'delivered' ? 'Delivered' : ($order->isManuallyShipped() ? 'Shipped' : 'Pending'),
                    $order->manual_courier_name ?? '',
                    $order->manual_tracking_id ?? '',
                    $order->manual_shipping_marked_at ? $order->manual_shipping_marked_at->format('Y-m-d H:i:s') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print invoice and shipping label for manual shipping order
     */
    public function printLabel(Order $order)
    {
        if (!$order->requires_manual_shipping) {
            abort(404, 'This order does not require manual shipping');
        }

        // Ensure AWB number exists and is in the correct format
        try {
            AWBNumberGenerator::assignToOrder($order);
            $order->refresh();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return view('admin.manual-shipping.print-label', compact('order'));
    }

    /**
     * Bulk print labels and invoices as PDF
     */
    public function bulkPrintPdf(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id'
        ]);

        $orders = Order::with(['user', 'orderItems.book'])
            ->whereIn('id', $request->order_ids)
            ->manualOrders()
            ->get();

        // Ensure AWB numbers are generated and in the correct format
        try {
            foreach ($orders as $order) {
                AWBNumberGenerator::assignToOrder($order);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'One or more labels could not be generated: ' . $e->getMessage());
        }

        // Re-load to get updated AWB numbers
        $orders = Order::with(['user', 'orderItems.book'])
            ->whereIn('id', $request->order_ids)
            ->get();

        $pdf = Pdf::loadView('admin.manual-shipping.bulk-print-pdf', compact('orders'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('manual_shipping_labels_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
