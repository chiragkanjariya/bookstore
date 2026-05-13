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

class BulkOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display bulk orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.book'])
            ->bulkOrders()
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereNull('manual_shipping_marked_at');
            } elseif ($request->status === 'shipped') {
                $query->whereNotNull('manual_shipping_marked_at');
            } elseif ($request->status === 'delivered') {
                $query->where('status', 'delivered');
            }
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
            'total' => Order::bulkOrders()->count(),
            'pending' => Order::bulkOrders()->whereNull('manual_shipping_marked_at')->where('status', '!=', 'delivered')->count(),
            'shipped' => Order::bulkOrders()->whereNotNull('manual_shipping_marked_at')->where('status', '!=', 'delivered')->count(),
            'delivered' => Order::bulkOrders()->where('status', 'delivered')->count(),
        ];

        $manualCouriers = ManualCourier::active()->orderBy('name')->get();

        return view('admin.bulk-orders.index', compact('orders', 'stats', 'request', 'manualCouriers'));
    }

    /**
     * Mark single bulk order as shipped with tracking data.
     */
    public function markAsShipped(Request $request, Order $order)
    {
        $request->validate([
            'manual_courier_id' => 'required|exists:manual_couriers,id',
            'manual_tracking_id' => 'required|string|max:255',
        ]);

        if (!$order->is_bulk_purchased) {
            return response()->json([
                'success' => false,
                'message' => 'This is not a bulk order'
            ], 400);
        }

        if ($order->status === 'shipped' || $order->status === 'delivered') {
            return response()->json([
                'success' => false,
                'message' => 'Order is already shipped or delivered'
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
            Log::error('Failed to send bulk order shipping email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        Log::info('Bulk order marked as shipped', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'courier_id' => $request->manual_courier_id,
            'tracking_id' => $request->manual_tracking_id,
            'marked_by' => auth()->user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bulk order marked as shipped successfully'
        ]);
    }

    /**
     * Bulk mark orders as shipped.
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
            ->bulkOrders()
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
                    Log::error('Failed to send bulk order shipping email', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        Log::info('Bulk mark bulk orders as shipped', [
            'count' => $count,
            'marked_by' => auth()->user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} bulk orders marked as shipped successfully"
        ]);
    }

    /**
     * Print invoice and shipping label for bulk order.
     */
    public function printLabel(Order $order)
    {
        if (!$order->is_bulk_purchased) {
            abort(404, 'This is not a bulk order');
        }

        try {
            AWBNumberGenerator::assignToOrder($order);
            $order->refresh();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return view('admin.manual-shipping.print-label', compact('order'));
    }

    /**
     * Bulk print labels as PDF.
     */
    public function bulkPrintPdf(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id'
        ]);

        $orders = Order::with(['user', 'orderItems.book'])
            ->whereIn('id', $request->order_ids)
            ->bulkOrders()
            ->get();

        try {
            foreach ($orders as $order) {
                AWBNumberGenerator::assignToOrder($order);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Label generation error: ' . $e->getMessage());
        }

        $orders = Order::with(['user', 'orderItems.book'])
            ->whereIn('id', $request->order_ids)
            ->get();

        $pdf = Pdf::loadView('admin.manual-shipping.bulk-print-pdf', compact('orders'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('bulk_order_labels_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Export bulk orders to CSV.
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'orderItems.book'])
            ->bulkOrders();

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereNull('manual_shipping_marked_at');
            } elseif ($request->status === 'shipped') {
                $query->whereNotNull('manual_shipping_marked_at');
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

        $filename = 'bulk_orders_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

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
                'Items Count',
                'Order Date',
                'Shipping Status',
                'Courier',
                'Tracking ID',
                'Shipped At'
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
                    $order->orderItems->count(),
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
}
