<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\Book;

class DashboardController extends Controller
{
    /**
     * Show user dashboard
     */
    public function userDashboard()
    {
        $user = Auth::user();
        
        // Sample user stats
        $stats = [
            'orders_count' => 0,
            'wishlist_count' => 0,
            'reviews_count' => 0,
        ];
        
        return view('dashboard.user', compact('user', 'stats'));
    }

    /**
     * Show admin dashboard
     */
    public function adminDashboard(Request $request)
    {
        $user = Auth::user();
        
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $year = $request->input('year');
        $months = $request->input('months', []);

        $ordersQuery = Order::query();
        
        if ($year) {
            $ordersQuery->whereYear('created_at', $year);
            if (!empty($months)) {
                $ordersQuery->where(function ($q) use ($months) {
                    foreach ($months as $month) {
                        $q->orWhereMonth('created_at', $month);
                    }
                });
            }
        } elseif ($dateFrom || $dateTo) {
            $ordersQuery->createdBetweenDisplayDates($dateFrom, $dateTo);
        }

        $stats = [
            'paid_orders' => (clone $ordersQuery)->where('payment_status', 'paid')->count(),
            'unpaid_orders' => (clone $ordersQuery)->whereIn('payment_status', ['pending', 'failed'])->count(),
            'integrated_courrier' => (clone $ordersQuery)->marutiOrders()->count(),
            'pending' => (clone $ordersQuery)->shippingPartnerStatus(Order::SHIPPING_PARTNER_PENDING)->count(),
            'shipment_created' => (clone $ordersQuery)->shippingPartnerStatus(Order::SHIPPING_PARTNER_SHIPMENT_CREATED)->count(),
            'ready_to_ship' => (clone $ordersQuery)->shippingPartnerStatus(Order::SHIPPING_PARTNER_READY_TO_SHIP)->count(),
            'total_revenue' => (clone $ordersQuery)->where('payment_status', 'paid')->sum('total_amount'),
            'manual_orders' => (clone $ordersQuery)->where('requires_manual_shipping', true)->where('is_bulk_purchased', false)->count(),
            'bulk_orders' => (clone $ordersQuery)->where('is_bulk_purchased', true)->count(),
        ];
        
        $recentUsers = User::where('role', 'user')
            ->latest()
            ->take(5)
            ->get();
            
        $recentOrders = Order::with(['user', 'orderItems'])
            ->latest()
            ->take(10)
            ->get();
        
        return view('dashboard.admin', compact('user', 'stats', 'recentUsers', 'recentOrders', 'request'));
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        $user = Auth::user();
        return view('dashboard.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'taluka_id' => ['nullable', 'integer', 'exists:talukas,id'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'state_id' => $request->state_id,
            'district_id' => $request->district_id,
            'taluka_id' => $request->taluka_id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!'
            ]);
        }

        return back()->with('success', 'Profile updated successfully!');
    }
}