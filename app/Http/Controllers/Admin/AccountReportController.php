<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AccountReportExport;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

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

        $query->createdBetweenDisplayDates(
            $request->input('date_from'),
            $request->input('date_to')
        );

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
     * Export the filtered account report to XLSX.
     */
    public function export(Request $request)
    {
        $query = Order::query()
            ->where('payment_status', 'paid')
            ->with(['user.state', 'user.district', 'user.taluka']);

        // Same filters as the on-screen report.
        $this->applyFilters($query, $request);

        return Excel::download(
            new AccountReportExport($query),
            'orders_report_' . now()->ist()->format('Y-m-d_H-i-s') . '.xlsx'
        );
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

        $filename = 'combined_invoice_' . now()->ist()->format('Y-m-d_H-i-s') . '.pdf';

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

        $query->createdBetweenDisplayDates(
            $request->input('date_from'),
            $request->input('date_to')
        );

        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', "%{$request->get('order_number')}%");
        }

        if ($request->filled('payment_id')) {
            $query->where('razorpay_payment_id', 'like', "%{$request->get('payment_id')}%");
        }
    }
}
