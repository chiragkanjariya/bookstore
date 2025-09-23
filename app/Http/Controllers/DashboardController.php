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
    public function adminDashboard()
    {
        $user = Auth::user();
        
        // Dynamic admin stats
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'total_orders' => Order::count(),
            'total_books' => Book::count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'monthly_revenue' => Order::where('payment_status', 'paid')
                                     ->whereMonth('created_at', now()->month)
                                     ->whereYear('created_at', now()->year)
                                     ->sum('total_amount'),
            'monthly_orders' => Order::whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)
                                    ->count(),
        ];
        
        $recentUsers = User::where('role', 'user')
            ->latest()
            ->take(5)
            ->get();
            
        $recentOrders = Order::with(['user', 'orderItems'])
            ->latest()
            ->take(10)
            ->get();
        
        return view('dashboard.admin', compact('user', 'stats', 'recentUsers', 'recentOrders'));
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
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
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