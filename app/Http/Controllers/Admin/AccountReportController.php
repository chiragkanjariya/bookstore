<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $query = User::query();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        // Status filtering removed as users table doesn't have status column

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        if ($request->filled('has_orders')) {
            if ($request->get('has_orders') === 'yes') {
                $query->whereHas('orders');
            } elseif ($request->get('has_orders') === 'no') {
                $query->whereDoesntHave('orders');
            }
        }

        // Get users with their order statistics
        $users = $query->withCount(['orders as total_orders'])
                      ->withSum('orders as total_spent', 'total_amount')
                      ->with(['orders' => function ($query) {
                          $query->latest()->take(5);
                      }])
                      ->paginate(20);

        // Get filter options
        $roles = User::distinct()->pluck('role')->filter();

        return view('admin.reports.accounts.index', compact('users', 'roles'));
    }

    /**
     * Export users to CSV.
     */
    public function exportCsv(Request $request)
    {
        $query = User::query();

        // Apply the same filters as index
        $this->applyFilters($query, $request);

        $users = $query->withCount(['orders as total_orders'])
                      ->withSum('orders as total_spent', 'total_amount')
                      ->get();

        $filename = 'users_report_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Role',
                'Status',
                'Total Orders',
                'Total Spent (₹)',
                'Registration Date',
                'Last Order Date',
                'Phone',
                'Address'
            ]);

            foreach ($users as $user) {
                $lastOrder = $user->orders()->latest()->first();
                
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    ucfirst($user->role),
                    'Active', // Status field removed
                    $user->total_orders,
                    number_format($user->total_spent ?? 0, 2),
                    $user->created_at->format('Y-m-d H:i:s'),
                    $lastOrder ? $lastOrder->created_at->format('Y-m-d H:i:s') : 'No orders',
                    $user->phone ?? 'N/A',
                    $user->address ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate combined invoice for selected users.
     */
    public function generateCombinedInvoice(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $userIds = $request->get('user_ids');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Get users with their orders
        $query = User::whereIn('id', $userIds)
                    ->with(['orders' => function ($query) use ($dateFrom, $dateTo) {
                        if ($dateFrom) {
                            $query->whereDate('created_at', '>=', $dateFrom);
                        }
                        if ($dateTo) {
                            $query->whereDate('created_at', '<=', $dateTo);
                        }
                        $query->with('orderItems.book');
                    }]);

        $users = $query->get();

        // Calculate totals
        $totalUsers = $users->count();
        $totalOrders = $users->sum(function ($user) {
            return $user->orders->count();
        });
        $totalAmount = $users->sum(function ($user) {
            return $user->orders->sum('total_amount');
        });

        // Generate PDF
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.reports.accounts.combined-invoice', compact(
            'users', 'totalUsers', 'totalOrders', 'totalAmount', 'dateFrom', 'dateTo'
        ));

        $filename = 'combined_invoice_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Get user details for AJAX requests.
     */
    public function getUserDetails(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::with(['orders.orderItems.book'])
                   ->findOrFail($request->get('user_id'));

        $user->total_orders = $user->orders->count();
        $user->total_spent = $user->orders->sum('total_amount');
        $user->last_order = $user->orders->sortByDesc('created_at')->first();

        return response()->json($user);
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        // Status filtering removed as users table doesn't have status column

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        if ($request->filled('has_orders')) {
            if ($request->get('has_orders') === 'yes') {
                $query->whereHas('orders');
            } elseif ($request->get('has_orders') === 'no') {
                $query->whereDoesntHave('orders');
            }
        }
    }
}
