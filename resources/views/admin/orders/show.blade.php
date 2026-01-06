@extends('layouts.admin')

@section('title', 'Order #' . $order->id)

@section('content')
    <div class="container mx-auto px-6 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">#IPDC{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h1>
                <nav class="text-sm text-gray-600 mt-2">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('admin.orders.index') }}" class="hover:text-blue-600">Orders</a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900">Order Details</span>
                </nav>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.orders.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Status -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Order Information</h2>
                        <div class="flex space-x-2">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $order->status_badge_color }}-100 text-{{ $order->status_badge_color }}-800">
                                {{ ucfirst($order->status) }}
                            </span>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $order->payment_status_badge_color }}-100 text-{{ $order->payment_status_badge_color }}-800">
                                Payment {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                        <div>
                            <p class="text-gray-600 font-medium">Order Date</p>
                            <p class="text-gray-900">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 font-medium">Payment Method</p>
                            <p class="text-gray-900">{{ ucfirst($order->payment_method) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 font-medium">Total Amount</p>
                            <p class="text-gray-900 text-lg font-semibold">₹{{ number_format($order->total_amount, 2) }}</p>
                        </div>
                        @if($order->courier_provider == 'shree_maruti' && $order->courier_document_ref)
                            <div>
                                <p class="text-gray-600 font-medium">Maruti Document Ref</p>
                                <p class="text-gray-900">{{ $order->courier_document_ref }}</p>
                            </div>
                        @endif
                        @if($order->tracking_number || $order->courier_awb_number)
                            <div>
                                <p class="text-gray-600 font-medium">Tracking Number</p>
                                <p class="text-gray-900">{{ $order->tracking_number ?? $order->courier_awb_number }}</p>
                            </div>
                        @endif
                        @if($order->courier_company)
                            <div>
                                <p class="text-gray-600 font-medium">Courier Company</p>
                                <p class="text-gray-900">{{ $order->courier_company }}</p>
                            </div>
                        @elseif($order->courier_provider == 'shree_maruti')
                            <div>
                                <p class="text-gray-600 font-medium">Courier Provider</p>
                                <p class="text-gray-900">Shree Maruti Courier</p>
                            </div>
                        @endif

                        <div>
                            <p class="text-gray-600 font-medium">Payment Status</p>
                            <p class="text-gray-900">{{ $order->payment_status }}</p>
                        </div>
                    </div>

                    <!-- Status Update Forms -->
                    {{-- <div class="mt-6 pt-6 border-t grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}"
                                class="flex items-end space-x-2">
                                @csrf
                                @method('PATCH')
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Update Order Status</label>
                                    <select name="status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending
                                        </option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : ''
                                            }}>Processing</option>
                                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped
                                        </option>
                                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : ''
                                            }}>Delivered</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : ''
                                            }}>Cancelled</option>
                                    </select>
                                </div>
                                <button type="submit"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                                    Update
                                </button>
                            </form>
                        </div>

                        <div>
                            <form method="POST" action="{{ route('admin.orders.update-payment-status', $order) }}"
                                class="flex items-end space-x-2">
                                @csrf
                                @method('PATCH')
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Update Payment
                                        Status</label>
                                    <select name="payment_status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : ''
                                            }}>Pending</option>
                                        <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Paid
                                        </option>
                                        <option value="failed" {{ $order->payment_status == 'failed' ? 'selected' : ''
                                            }}>Failed</option>
                                        <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : ''
                                            }}>Refunded</option>
                                    </select>
                                </div>
                                <button type="submit"
                                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-200">
                                    Update
                                </button>
                            </form>
                        </div>
                    </div> --}}


                    <!-- Maruti Courier Section -->
                    @if($order->courier_provider == 'shree_maruti')
                        <div class="mt-6 pt-6 border-t">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Shree Maruti Courier Details</h3>
                            <div class="bg-blue-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    @if($order->courier_document_ref)
                                        <div>
                                            <p class="text-gray-600 font-medium">Document Reference</p>
                                            <p class="text-gray-900 font-mono">{{ $order->courier_document_ref }}</p>
                                        </div>
                                    @endif
                                    @if($order->tracking_number || $order->courier_awb_number)
                                        <div>
                                            <p class="text-gray-600 font-medium">AWB / Tracking Number</p>
                                            <p class="text-gray-900 font-mono">
                                                {{ $order->tracking_number ?? $order->courier_awb_number }}</p>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-gray-600 font-medium">Courier Provider</p>
                                        <p class="text-gray-900">Shree Maruti Courier</p>
                                    </div>
                                    @if($order->shipped_at)
                                        <div>
                                            <p class="text-gray-600 font-medium">Shipped Date</p>
                                            <p class="text-gray-900">{{ $order->shipped_at->format('M d, Y h:i A') }}</p>
                                        </div>
                                    @endif
                                    @if($order->delivered_at)
                                        <div>
                                            <p class="text-gray-600 font-medium">Delivered Date</p>
                                            <p class="text-gray-900">{{ $order->delivered_at->format('M d, Y h:i A') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 pt-6 border-t">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Shipping Information</h3>
                            @if($order->is_bulk_purchased)
                                <div class="bg-green-50 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-green-600 mr-3"></i>
                                        <div>
                                            <p class="text-green-800 font-medium">Bulk Purchase Order - Free Shipping</p>
                                            <p class="text-green-700 text-sm">This order qualifies for bulk purchase with free
                                                shipping.</p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($order->requires_manual_shipping)
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                                        <div>
                                            <p class="text-blue-800 font-medium">Manual Shipping Required</p>
                                            <p class="text-blue-700 text-sm">This order requires manual shipping arrangement.</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="bg-yellow-50 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                                        <div>
                                            <p class="text-yellow-800 font-medium">Pending Shipment</p>
                                            <p class="text-yellow-700 text-sm">This order is
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }} and will be shipped via bulk
                                                action from the orders list.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Customer Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Customer Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-medium text-gray-900 mb-2">Customer Details</h3>
                            <div class="text-sm space-y-1">
                                <p><span class="font-medium">Name:</span> {{ $order->user->name }}</p>
                                <p><span class="font-medium">Email:</span> {{ $order->user->email }}</p>
                                <p><span class="font-medium">Member Since:</span>
                                    {{ $order->user->created_at->format('M Y') }}</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-medium text-gray-900 mb-2">Shipping Address</h3>
                            <div class="text-sm space-y-1">
                                <p class="font-medium">{{ $order->shipping_address['name'] }}</p>
                                <p>{{ $order->shipping_address['phone'] }}</p>
                                <p>{{ $order->shipping_address['address_line_1'] }}</p>
                                @if(!empty($order->shipping_address['address_line_2']))
                                    <p>{{ $order->shipping_address['address_line_2'] }}</p>
                                @endif
                                <p>{{ $order->shipping_address['city'] }}, {{ $order->shipping_address['state'] }}
                                    {{ $order->shipping_address['postal_code'] }}
                                </p>
                                <p>{{ $order->shipping_address['country'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Items</h2>

                    <div class="space-y-4">
                        @foreach($order->orderItems as $item)
                            <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
                                <img src="{{ $item->book->cover_image_url }}" alt="{{ $item->book->title }}"
                                    class="w-16 h-20 object-cover rounded">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-medium text-gray-900">{{ $item->book->title }}</h3>
                                    <p class="text-sm text-gray-600">by {{ $item->book->author }}</p>
                                    <p class="text-sm text-gray-600">Category: {{ $item->book->category->name }}</p>
                                    <div class="mt-2 flex items-center space-x-4 text-sm">
                                        <span class="text-gray-600">Qty: {{ $item->quantity }}</span>
                                        <span class="text-gray-600">Price: ₹{{ number_format($item->price, 2) }}</span>
                                        @if($item->shipping_price > 0)
                                            <span class="text-gray-600">Shipping:
                                                ₹{{ number_format($item->shipping_price, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-gray-900">₹{{ number_format($item->total_price, 2) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($order->notes)
                    <!-- Order Notes -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Notes</h2>
                        <p class="text-gray-700">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Order Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>

                    <!-- Price Breakdown -->
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="text-gray-900">₹{{ number_format($order->subtotal, 2) }}</span>
                        </div>

                        @if($order->shipping_cost > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="text-gray-900">₹{{ number_format($order->shipping_cost, 2) }}</span>
                            </div>
                        @endif

                        @if($order->tax_amount > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax (18% GST)</span>
                                <span class="text-gray-900">₹{{ number_format($order->tax_amount, 2) }}</span>
                            </div>
                        @endif

                        <div class="border-t pt-2 mt-2">
                            <div class="flex justify-between text-lg font-semibold">
                                <span class="text-gray-900">Total</span>
                                <span class="text-gray-900">₹{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="mt-6 pt-6 border-t">
                        <h3 class="font-medium text-gray-900 mb-3">Payment Information</h3>
                        <div class="text-sm space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Method</span>
                                <span class="text-gray-900">{{ ucfirst($order->payment_method) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status</span>
                                <span class="text-{{ $order->payment_status_badge_color }}-600 font-medium">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                            @if($order->razorpay_payment_id)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Payment ID</span>
                                    <span class="text-gray-900 text-xs">{{ $order->razorpay_payment_id }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6 pt-6 border-t space-y-3">
                        @if($order->payment_status === 'paid')
                            <a href="{{ route('admin.orders.invoice', $order) }}"
                                class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 font-medium text-center block transition duration-200">
                                Download Invoice
                            </a>
                        @endif

                        <button onclick="window.print()"
                            class="w-full bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 font-medium text-center block transition duration-200">
                            Print Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function trackShipment(shiprocketOrderId) {
            // Show loading state
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Tracking...';
            btn.disabled = true;

            // Make AJAX request to track shipment
            fetch(`/admin/orders/track-shipment/${shiprocketOrderId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
                .then(response => response.json())
                .then(data => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;

                    if (data.success) {
                        // Show tracking information in a modal or alert
                        alert('Tracking Status: ' + JSON.stringify(data.tracking_data, null, 2));
                    } else {
                        alert('Error tracking shipment: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert('Error tracking shipment: ' + error.message);
                });
        }

        function createShiprocketOrder(orderId) {
            if (!confirm('Are you sure you want to create a Shiprocket order for this order?')) {
                return;
            }

            // Show loading state
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
            btn.disabled = true;

            // Make AJAX request to create Shiprocket order
            fetch(`/admin/orders/${orderId}/create-shiprocket`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
                .then(response => response.json())
                .then(data => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;

                    if (data.success) {
                        alert('Shiprocket order created successfully!');
                        // Reload the page to show updated information
                        window.location.reload();
                    } else {
                        alert('Error creating Shiprocket order: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert('Error creating Shiprocket order: ' + error.message);
                });
        }

        function sendOrderConfirmation() {
            const btn = event.target;
            const originalText = btn.innerHTML;

            // Show loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';

            fetch('{{ route("admin.orders.send-confirmation", $order) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    if (data.success) {
                        alert('Order confirmation email sent successfully!');
                    } else {
                        alert('Failed to send email: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    alert('Error sending email: ' + error.message);
                });
        }
    </script>
@endsection