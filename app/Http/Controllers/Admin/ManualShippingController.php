<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
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
     * Display manual shipping orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.book'])
            ->requiresManualShipping()
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereNull('manual_shipping_marked_at');
            } elseif ($request->status === 'shipped') {
                $query->whereNotNull('manual_shipping_marked_at');
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
            'total' => Order::requiresManualShipping()->count(),
            'pending' => Order::pendingManualShipping()->count(),
            'shipped' => Order::requiresManualShipping()->whereNotNull('manual_shipping_marked_at')->count(),
        ];

        return view('admin.manual-shipping.index', compact('orders', 'stats', 'request'));
    }

    /**
     * Mark single order as manually shipped
     */
    public function markAsShipped(Order $order)
    {
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

        $order->markAsManuallyShipped();

        Log::info('Order marked as manually shipped', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
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
            'order_ids.*' => 'exists:orders,id'
        ]);

        $orders = Order::whereIn('id', $request->order_ids)
            ->requiresManualShipping()
            ->whereNull('manual_shipping_marked_at')
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            if ($order->markAsManuallyShipped()) {
                $count++;
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
            ->requiresManualShipping();

        // Apply same filters as index
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

        $filename = 'manual_shipping_orders_' . date('Y-m-d_H-i-s') . '.csv';

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
                    $order->isManuallyShipped() ? 'Shipped' : 'Pending',
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

        // Generate AWB number if not exists
        if (!$order->awb_number) {
            AWBNumberGenerator::assignToOrder($order);
            $order->refresh();
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
            ->requiresManualShipping()
            ->get();

        // Generate AWB numbers for orders that don't have them
        foreach ($orders as $order) {
            if (!$order->awb_number) {
                AWBNumberGenerator::assignToOrder($order);
            }
        }

        // Refresh to get updated AWB numbers
        $orders = $orders->fresh(['user', 'orderItems.book']);

        $pdf = Pdf::loadView('admin.manual-shipping.bulk-print-pdf', compact('orders'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('manual_shipping_labels_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
