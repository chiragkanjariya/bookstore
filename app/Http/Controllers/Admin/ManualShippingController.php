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

        // Filter by shipping partner status (the only status shown in the listing)
        $shippingPartnerStatus = $request->input('shipping_partner_status', Order::SHIPPING_PARTNER_PENDING);
        if (in_array($shippingPartnerStatus, Order::SHIPPING_PARTNER_STATUSES, true)) {
            $query->shippingPartnerStatus($shippingPartnerStatus);
            $request->merge(['shipping_partner_status' => $shippingPartnerStatus]);
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
        $query->createdBetweenDisplayDates(
            $request->input('date_from'),
            $request->input('date_to')
        );

        $orders = $query->paginate(20)->withQueryString();

        // Get statistics
        $stats = [
            'total' => Order::manualOrders()->count(),
            'pending' => Order::manualOrders()->shippingPartnerStatus(Order::SHIPPING_PARTNER_PENDING)->count(),
            'shipment_created' => Order::manualOrders()->shippingPartnerStatus(Order::SHIPPING_PARTNER_SHIPMENT_CREATED)->count(),
            'ready_to_ship' => Order::manualOrders()->shippingPartnerStatus(Order::SHIPPING_PARTNER_READY_TO_SHIP)->count(),
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

        $shippingPartnerStatus = $request->input('shipping_partner_status', Order::SHIPPING_PARTNER_PENDING);
        if (in_array($shippingPartnerStatus, Order::SHIPPING_PARTNER_STATUSES, true)) {
            $query->shippingPartnerStatus($shippingPartnerStatus);
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

        $query->createdBetweenDisplayDates(
            $request->input('date_from'),
            $request->input('date_to')
        );

        $orders = $query->get();

        $filename = 'manual_orders_' . now()->ist()->format('Y-m-d_H-i-s') . '.csv';

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
                'Shipment Created At',
                'Label Printed At'
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
                    $order->created_at->ist()->format('Y-m-d H:i:s'),
                    $order->shipping_partner_status_label,
                    $order->manual_courier_name ?? '',
                    $order->manual_tracking_id ?? '',
                    $order->shipment_created_at ? $order->shipment_created_at->ist()->format('Y-m-d H:i:s') : '',
                    $order->label_printed_at ? $order->label_printed_at->ist()->format('Y-m-d H:i:s') : ''
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

        if (!$order->hasShipment()) {
            return back()->with('error', 'Label cannot be printed until shipment is created.');
        }

        // Printing the label moves the order to Ready to Ship
        $order->markLabelPrinted();

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

        $invalidOrders = $orders->filter(fn($order) => !$order->hasShipment());

        if ($invalidOrders->count() > 0) {
            return back()->with('error', 'Labels can only be printed for orders with "Shipment Created" or "Ready to Ship" status.');
        }

        // Portrait to match the @page rule in the view (dompdf's @page size wins
        // over setPaper anyway, so keep the two in sync)
        $pdf = Pdf::loadView('admin.manual-shipping.bulk-print-pdf', compact('orders'))
            ->setPaper('a4', 'portrait');

        Order::markLabelsPrinted($request->order_ids);

        return $pdf->download('manual_shipping_labels_' . now()->ist()->format('Y-m-d_H-i-s') . '.pdf');
    }
}
