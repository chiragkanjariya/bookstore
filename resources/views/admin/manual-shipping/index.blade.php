@extends('layouts.admin')

@section('title', 'Manual Orders')

@section('content')
    <div class="container mx-auto px-6 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Manual Orders</h1>
                <p class="text-gray-600 mt-2">Manage orders requiring manual shipping (non-serviceable areas)</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.manual-shipping.export', request()->query()) }}"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-200 flex items-center">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-box text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Manual Orders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending Shipment</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['pending']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-truck text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Shipped</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['shipped']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Delivered</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['delivered']) }}</p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                    <select name="payment_status"
                        class="w-full px-3 py-2 border border-claudgray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Payment Status</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Status</label>
                    <select name="shipping_partner_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Shipping Status</option>
                        <option value="pending" {{ request('shipping_partner_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="shipment_created" {{ request('shipping_partner_status') == 'shipment_created' ? 'selected' : '' }}>Shipment Created</option>
                        <option value="ready_to_ship" {{ request('shipping_partner_status') == 'ready_to_ship' ? 'selected' : '' }}>Ready to Ship</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-end">
                    <div class="w-full flex space-x-2">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                            Filter
                        </button>
                        <a href="{{ route('admin.manual-shipping.index') }}"
                            class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition duration-200">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Manual Orders</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Shipping Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Postal Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tracking</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</div>
                                        <div class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $order->orderItems->count() }} item(s)</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $order->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $order->user->email }}</div>
                                        @if(isset($order->shipping_address['phone']))
                                            <div class="text-xs text-gray-500">{{ $order->shipping_address['phone'] }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ $order->shipping_address['address_line_1'] ?? '' }}
                                        @if(isset($order->shipping_address['address_line_2']))
                                            <br>{{ $order->shipping_address['address_line_2'] }}
                                        @endif
                                        <br>{{ $order->shipping_address['city'] ?? '' }},
                                        {{ $order->shipping_address['state'] ?? '' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $order->shipping_address['postal_code'] ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Non-serviceable
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    ₹{{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($order->shipping_partner_status === 'ready_to_ship')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-double mr-1"></i>Ready to Ship
                                        </span>
                                    @elseif($order->shipping_partner_status === 'shipment_created')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-truck mr-1"></i>Shipment Created
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($order->manual_tracking_id)
                                        <div class="text-sm">
                                            <div class="font-medium text-gray-900">{{ $order->manual_courier_name }}</div>
                                            <div class="text-xs text-blue-600">{{ $order->manual_tracking_id }}</div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex flex-col space-y-2">
                                        <a href="{{ route('admin.orders.show', $order) }}"
                                            class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye mr-1"></i>View Details
                                        </a>
                                        <a href="{{ route('admin.manual-shipping.print-label', $order) }}"
                                            class="text-purple-600 hover:text-purple-900 print-label-link"
                                            data-shipped="{{ $order->isManuallyShipped() ? 'true' : 'false' }}" target="_blank">
                                            <i class="fas fa-print mr-1"></i>Print Label & Invoice
                                        </a>
                                        @if(!$order->isManuallyShipped() && $order->status !== 'delivered')
                                            <button type="button"
                                                onclick="openShipModal({{ $order->id }}, '{{ $order->order_number }}')"
                                                class="text-left text-green-600 hover:text-green-900">
                                                <i class="fas fa-check mr-1"></i>Mark Shipped
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-box text-4xl mb-4 opacity-50"></i>
                                    <p class="text-lg">No manual orders found</p>
                                    <p class="text-sm">Orders from non-serviceable areas will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Ship Order Modal -->
    <div id="ship-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Mark as Shipped</h3>
                <button onclick="closeShipModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="text-sm text-gray-600 mb-4">Order: <span id="ship-modal-order-number" class="font-semibold"></span>
            </p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Courier Service <span
                            class="text-red-500">*</span></label>
                    <select id="ship-modal-courier"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0]">
                        <option value="">Select Courier</option>
                        @foreach($manualCouriers as $courier)
                            <option value="{{ $courier->id }}">{{ $courier->name }} ({{ $courier->courier_service }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tracking Number <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="ship-modal-tracking" placeholder="Enter tracking number"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0]">
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closeShipModal()"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition">
                    Cancel
                </button>
                <button onclick="submitShipModal()"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                    <i class="fas fa-truck mr-1"></i> Confirm Ship
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentShipOrderId = null;

        function openShipModal(orderId, orderNumber) {
            currentShipOrderId = orderId;
            document.getElementById('ship-modal-order-number').textContent = '#' + orderNumber;
            document.getElementById('ship-modal-courier').value = '';
            document.getElementById('ship-modal-tracking').value = '';
            document.getElementById('ship-modal').classList.remove('hidden');
        }

        function closeShipModal() {
            currentShipOrderId = null;
            document.getElementById('ship-modal').classList.add('hidden');
        }

        function submitShipModal() {
            const courierId = document.getElementById('ship-modal-courier').value;
            const trackingId = document.getElementById('ship-modal-tracking').value;

            if (!courierId) {
                alert('Please select a courier service.');
                return;
            }
            if (!trackingId) {
                alert('Please enter a tracking number.');
                return;
            }

            fetch(`/admin/manual-shipping/${currentShipOrderId}/mark-shipped`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    manual_courier_id: courierId,
                    manual_tracking_id: trackingId
                })
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
                    alert('An error occurred while marking the order as shipped.');
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Individual print label link validation
            document.querySelectorAll('.print-label-link').forEach(link => {
                link.addEventListener('click', function (e) {
                    if (this.getAttribute('data-shipped') === 'false') {
                        e.preventDefault();
                        alert('This order is not yet marked as shipped. You must mark it as shipped first before printing labels.');
                    }
                });
            });
        });
    </script>
@endsection