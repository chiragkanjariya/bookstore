@extends('layouts.admin')

@section('title', 'Orders Management')

@section('content')
    <div class="container mx-auto px-6 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Orders Management</h1>
                <p class="text-gray-600 mt-2">Maruti automatic orders — manual and bulk orders are managed separately</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.orders.export', request()->query()) }}"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-200">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-shopping-cart text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Maruti Orders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_orders']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-box text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Order Placed</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['order_placed']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-truck text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Shipped</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['shipped_orders']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-rupee-sign text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">₹{{ number_format($stats['total_revenue'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Order number, customer name..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="order_placed" {{ request('status') == 'order_placed' ? 'selected' : '' }}>Order Placed</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                    <select name="payment_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Payment Status</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending
                        </option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded
                        </option>
                    </select>
                </div>



                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Status</label>
                    <select name="shipping_partner_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Shipping Status</option>
                        <option value="pending" {{ request('shipping_partner_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('shipping_partner_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('shipping_partner_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-end">
                    <div class="w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <div class="flex space-x-2">
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                                Filter
                            </button>
                            <a href="{{ route('admin.orders.index') }}"
                                class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition duration-200">
                                Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <form id="bulk-form" method="POST" action="{{ route('admin.orders.bulk-status') }}">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Orders List</h2>
                        <div class="flex items-center space-x-3">
                            <button type="button" id="ship-now-btn"
                                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm transition duration-200"
                                disabled>
                                <i class="fas fa-shipping-fast mr-2"></i>Ship Now
                            </button>
                            <button type="button" id="print-labels-btn"
                                class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm transition duration-200"
                                >
                                <i class="fas fa-print mr-2"></i>Print Labels
                            </button>
                            <select id="bulk-status" name="status"
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">Bulk Update Status</option>
                                <option value="pending_to_be_prepared">Mark as Order Placed</option>
                                <option value="shipped">Mark as Shipped</option>
                                <option value="delivered">Mark as Delivered</option>
                            </select>
                            <button type="button" id="bulk-submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm transition duration-200"
                                disabled>
                                Update Selected
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" id="select-all" class="rounded">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($orders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}"
                                            data-status="{{ $order->status }}"
                                            class="order-checkbox rounded">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</div>
                                            <div class="text-sm text-gray-500">{{ $order->orderItems->count() }} item(s)</div>

                                            @if($order->courier_provider == 'shree_maruti' && $order->courier_document_ref)
                                                <div class="mt-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                                        <i class="fas fa-barcode mr-1"></i>Maruti: {{ $order->courier_document_ref }}
                                                    </span>
                                                </div>
                                            @elseif($order->tracking_number || $order->courier_awb_number)
                                                <div class="mt-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                                        <i class="fas fa-barcode mr-1"></i>{{ $order->tracking_number ?? $order->courier_awb_number }}
                                                    </span>
                                                </div>
                                            @elseif($order->status === 'shipped')
                                                <div class="mt-1 text-xs text-yellow-600">
                                                    <i class="fas fa-clock mr-1"></i>Maruti ID pending
                                                </div>
                                            @endif

                                            @if($order->shipping_partner_status)
                                                <div class="mt-1">
                                                    <span class="text-xs font-bold {{ $order->shipping_partner_status == 'approved' ? 'text-green-600' : ($order->shipping_partner_status == 'rejected' ? 'text-red-600' : 'text-gray-500') }}">
                                                        <i class="fas fa-{{ $order->shipping_partner_status == 'approved' ? 'check' : 'times' }} mr-1"></i>{{ ucfirst($order->shipping_partner_status) }}
                                                    </span>
                                                </div>
                                                @if($order->shipping_partner_status == 'rejected' && $order->shipping_partner_error)
                                                    <div class="text-xs text-red-600 mt-0.5 max-w-xs truncate" title="{{ $order->shipping_partner_error }}">
                                                        {{ Str::limit($order->shipping_partner_error, 45) }}
                                                    </div>
                                                @endif
                                            @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $order->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $order->user->email }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $order->status_badge_color }}-100 text-{{ $order->status_badge_color }}-800">
                                        {{ $order->maruti_status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $order->payment_status_badge_color }}-100 text-{{ $order->payment_status_badge_color }}-800">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    ₹{{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $order->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                        class="text-blue-600 hover:text-blue-900">View</a>
                                    
                                    @if(!$order->requires_manual_shipping && (in_array($order->status, ['pending', 'pending_to_be_prepared']) || $order->shipping_partner_status == 'rejected'))
                                        <button type="button" 
                                            onclick="moveToManualShipping({{ $order->id }}, '{{ $order->order_number }}')"
                                            class="text-orange-600 hover:text-orange-900 ml-2"
                                            title="Move to Manual Shipping">
                                            Manual
                                        </button>
                                    @endif
                                </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-shopping-cart text-4xl mb-4 opacity-50"></i>
                                        <p class="text-lg">No orders found</p>
                                        <p class="text-sm">Orders will appear here once customers start placing orders.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('select-all');
            const orderCheckboxes = document.querySelectorAll('.order-checkbox');
            const bulkStatus = document.getElementById('bulk-status');
            const bulkSubmit = document.getElementById('bulk-submit');
            const shipNowBtn = document.getElementById('ship-now-btn');
            const bulkForm = document.getElementById('bulk-form');

            // Select all functionality
            selectAll.addEventListener('change', function () {
                orderCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkButtons();
            });

            // Individual checkbox change
            orderCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkButtons);
            });

            // Update bulk button states
            function updateBulkButtons() {
                const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
                bulkSubmit.disabled = checkedBoxes.length === 0 || !bulkStatus.value;
                shipNowBtn.disabled = checkedBoxes.length === 0;
            }

            // Bulk status change
            bulkStatus.addEventListener('change', updateBulkButtons);

            // Bulk submit
            bulkSubmit.addEventListener('click', function () {
                const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    alert('Please select at least one order.');
                    return;
                }
                if (!bulkStatus.value) {
                    alert('Please select a status to update.');
                    return;
                }
                if (confirm(`Are you sure you want to update ${checkedBoxes.length} order(s) to ${bulkStatus.options[bulkStatus.selectedIndex].text}?`)) {
                    bulkForm.submit();
                }
            });

            // Ship Now button
            shipNowBtn.addEventListener('click', function () {
                const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    alert('Please select at least one order.');
                    return;
                }

                if (confirm(`Are you sure you want to ship ${checkedBoxes.length} order(s) now?\n\nThis will:\n- Submit orders to Maruti API\n- Mark orders as "Ready to Ship"\n- Send notification emails to customers`)) {
                    // Change form action to ship now route
                    const originalAction = bulkForm.action;
                    bulkForm.action = '{{ route("admin.orders.bulk-ship-now") }}';
                    bulkForm.submit();
                    // Restore original action in case of back button
                    bulkForm.action = originalAction;
                }
            });

            // Print Labels button
            const printLabelsBtn = document.getElementById('print-labels-btn');
            printLabelsBtn.addEventListener('click', function () {
                const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    alert('Please select at least one order.');
                    return;
                }

                // Check for non-shipped orders
                const pendingOrders = Array.from(checkedBoxes).filter(cb => 
                    cb.getAttribute('data-status') === 'pending_to_be_prepared'
                );

                if (pendingOrders.length > 0) {
                    alert('Some selected orders are not yet shipped (Pending to be prepared). You must "Ship Now" first before printing labels.');
                    return;
                }

                // Change form action to bulk print route
                const originalAction = bulkForm.action;
                // Use the generic bulk print route for all orders
                bulkForm.action = '{{ route("admin.orders.bulk-print-label") }}';
                bulkForm.submit();
                bulkForm.action = originalAction;
            });
        });

        function moveToManualShipping(orderId, orderNumber) {
            if (confirm(`Are you sure you want to move order #${orderNumber} to manual shipping?`)) {
                fetch(`/admin/orders/${orderId}/move-to-manual-shipping`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while moving the order to manual shipping.');
                });
            }
        }
    </script>
@endsection