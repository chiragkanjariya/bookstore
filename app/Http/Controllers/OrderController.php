<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the user's orders.
     */
    public function index()
    {
        $orders = Auth::user()->orders()
            ->with(['orderItems.book.category'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Ensure user can only view their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['orderItems.book.category']);

        return view('orders.show', compact('order'));
    }

    /**
     * Cancel the specified order.
     */
    public function cancel(Order $order)
    {
        // Ensure user can only cancel their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$order->canBeCancelled()) {
            return redirect()->back()->with('error', 'This order cannot be cancelled.');
        }

        $order->update([
            'status' => 'cancelled',
            'payment_status' => $order->payment_status === 'paid' ? 'refunded' : 'cancelled'
        ]);

        // Restore stock
        foreach ($order->orderItems as $orderItem) {
            $orderItem->book->increment('stock', $orderItem->quantity);
        }

        return redirect()->back()->with('success', 'Order has been cancelled successfully.');
    }

    /**
     * Download invoice for the order (same as email format).
     */
    public function invoice(Order $order)
    {
        // Ensure user can only download their own invoices
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

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
}
