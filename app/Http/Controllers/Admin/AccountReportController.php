<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class AccountReportController extends Controller
{
    /**
     * Display the account report page with filters.
     */
    public function index(Request $request)
    {
        $query = Order::query()
            ->where('payment_status', 'paid')
            ->with(['user.state', 'user.district', 'user.taluka']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('role', $request->get('role'));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', "%{$request->get('order_number')}%");
        }

        if ($request->filled('payment_id')) {
            $query->where('razorpay_payment_id', 'like', "%{$request->get('payment_id')}%");
        }

        // Get orders with pagination
        $orders = $query->latest()->paginate(20);

        // Get filter options
        $roles = User::distinct()->pluck('role')->filter();

        // Calculate summary statistics
        $totalOrders = $query->count();
        $totalRevenue = $query->sum('total_amount');
        $totalShipping = $query->sum('shipping_cost');

        return view('admin.reports.accounts.index', compact('orders', 'roles', 'totalOrders', 'totalRevenue', 'totalShipping'));
    }

    /**
     * Export orders to CSV.
     */
    public function exportCsv(Request $request)
    {
        $query = Order::query()
            ->where('payment_status', 'paid')
            ->with(['user.state', 'user.district', 'user.taluka']);

        // Apply the same filters as index
        $this->applyFilters($query, $request);

        $orders = $query->latest()->get();

        $orderIds = $orders->pluck('id');

        // One column per book actually purchased across the filtered orders.
        $bookIds = OrderItem::whereIn('order_id', $orderIds)
            ->distinct()
            ->pluck('book_id')
            ->filter();

        $bookColumns = Book::whereIn('id', $bookIds)
            ->orderBy('title')
            ->orderBy('id')
            ->pluck('title', 'id')
            ->all();

        // order_items keeps no title snapshot, so books removed from inventory
        // still need a column rather than having their quantities disappear.
        foreach ($bookIds->diff(array_keys($bookColumns)) as $missingBookId) {
            $bookColumns[$missingBookId] = 'Book #' . $missingBookId;
        }

        // Quantity per (order, book) in a single aggregate query. Summed so an
        // order listing the same book on two line items reports the combined qty.
        $quantities = OrderItem::whereIn('order_id', $orderIds)
            ->selectRaw('order_id, book_id, SUM(quantity) as qty')
            ->groupBy('order_id', 'book_id')
            ->get()
            ->groupBy('order_id')
            ->map(fn ($rows) => $rows->pluck('qty', 'book_id'));

        $filename = 'orders_report_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($orders, $bookColumns, $quantities) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'Name',
                'Email',
                'Mobile',
                'Country',
                'State',
                'District',
                'Taluka',
                'City',
                ...array_values($bookColumns),
                'Total Amount (₹)',
                'Shipping Cost (₹)',
                'Maruti Shipping Rate (₹)',
                'Total Amount Excluding Shipping (₹)',
                'Invoice Number',
                'Payment Date',
                'Razorpay Payment ID',
                'Razorpay Order ID'
            ]);

            foreach ($orders as $order) {
                $user = $order->user;
                $shippingAddress = $order->shipping_address;
                $totalExcludingShipping = $order->total_amount - $order->shipping_cost - ($order->maruti_shipping_rate ?? 0);
                $orderQuantities = $quantities->get($order->id, collect());

                fputcsv($file, [
                    $user->name ?? 'N/A',
                    $user->email ?? 'N/A',
                    $user->phone ?? ($shippingAddress['phone'] ?? 'N/A'),
                    $shippingAddress['country'] ?? 'India',
                    $user->state->name ?? ($shippingAddress['state'] ?? 'N/A'),
                    $user->district->name ?? ($shippingAddress['district'] ?? 'N/A'),
                    $user->taluka->name ?? ($shippingAddress['taluka'] ?? 'N/A'),
                    $shippingAddress['city'] ?? 'N/A',
                    // Must stay in the same order as the book headers above.
                    ...array_map(
                        fn ($bookId) => (int) ($orderQuantities[$bookId] ?? 0),
                        array_keys($bookColumns)
                    ),
                    number_format($order->total_amount, 2),
                    number_format($order->shipping_cost, 2),
                    number_format($order->maruti_shipping_rate ?? 0, 2),
                    number_format($totalExcludingShipping, 2),
                    'IPDC-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->razorpay_payment_id ?? 'N/A',
                    $order->razorpay_order_id ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate combined invoice for selected orders.
     */
    public function generateCombinedInvoice(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
        ]);

        $orderIds = $request->get('order_ids');

        // Get orders with their details
        $orders = Order::whereIn('id', $orderIds)
            ->where('payment_status', 'paid')
            ->with(['user.state', 'user.district', 'user.taluka', 'orderItems.book'])
            ->get();

        // Calculate totals
        $totalOrders = $orders->count();
        $totalAmount = $orders->sum('total_amount');
        $totalShipping = $orders->sum('shipping_cost');

        // Generate PDF
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.reports.accounts.combined-invoice', compact(
            'orders',
            'totalOrders',
            'totalAmount',
            'totalShipping'
        ));

        $filename = 'combined_invoice_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Get order details for AJAX requests.
     */
    public function getOrderDetails(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = Order::with(['user.state', 'user.district', 'user.taluka', 'orderItems.book'])
            ->findOrFail($request->get('order_id'));

        return response()->json($order);
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('role', $request->get('role'));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', "%{$request->get('order_number')}%");
        }

        if ($request->filled('payment_id')) {
            $query->where('razorpay_payment_id', 'like', "%{$request->get('payment_id')}%");
        }
    }
}
