@extends('layouts.admin')

@section('title', 'Manual Shipping Orders')

@section('content')
    <div class="container mx-auto px-6 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Manual Shipping Orders</h1>
                <p class="text-gray-600 mt-2">Manage orders requiring manual shipping arrangements</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.manual-shipping.export', request()->query()) }}"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-200 flex items-center">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-box text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Manual Shipping</p>
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
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Shipped</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['shipped']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
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
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
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
                    <h2 class="text-lg font-semibold text-gray-900">Manual Shipping Orders</h2>
                    <div class="flex items-center space-x-3">
                        <button type="button" id="bulk-print-labels"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm transition duration-200 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed"
                            disabled>
                            <i class="fas fa-print mr-2"></i>Print Selected Labels
                        </button>
                        <button type="button" id="bulk-mark-shipped"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm transition duration-200 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed"
                            disabled>
                            <i class="fas fa-check mr-2"></i>Mark Selected as Shipped
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Shipping Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Postal Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    @if(!$order->isManuallyShipped())
                                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}"
                                            class="order-checkbox rounded">
                                    @endif
                                </td>
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
                                            <div class="text-xs text-gray-500">{{ $order->shipping_address['phone'] }}</div>
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
                                    @if($order->isManuallyShipped())
                                        <div>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>Shipped
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $order->manual_shipping_marked_at->format('M d, Y') }}
                                            </div>
                                        </div>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex flex-col space-y-2">
                                        <a href="{{ route('admin.orders.show', $order) }}"
                                            class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye mr-1"></i>View Details
                                        </a>
                                        <a href="{{ route('admin.manual-shipping.print-label', $order) }}"
                                            class="text-purple-600 hover:text-purple-900" target="_blank">
                                            <i class="fas fa-print mr-1"></i>Print Label & Invoice
                                        </a>
                                        @if(!$order->isManuallyShipped())
                                            <button type="button" onclick="markAsShipped({{ $order->id }})"
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
                                    <p class="text-lg">No manual shipping orders found</p>
                                    <p class="text-sm">Orders requiring manual shipping will appear here.</p>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('select-all');
            const orderCheckboxes = document.querySelectorAll('.order-checkbox');
            const bulkMarkShipped = document.getElementById('bulk-mark-shipped');
            const bulkPrintLabels = document.getElementById('bulk-print-labels');

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
                const hasSelection = checkedBoxes.length > 0;
                bulkMarkShipped.disabled = !hasSelection;
                bulkPrintLabels.disabled = !hasSelection;
            }

            // Bulk print labels
            bulkPrintLabels.addEventListener('click', function () {
                const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    alert('Please select at least one order.');
                    return;
                }

                const orderIds = Array.from(checkedBoxes).map(cb => cb.value);

                // Create a form and submit it to download PDF
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.manual-shipping.bulk-print-pdf") }}';
                form.style.display = 'none';

                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                // Add order IDs
                orderIds.forEach(orderId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'order_ids[]';
                    input.value = orderId;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            });

            // Bulk mark as shipped
            bulkMarkShipped.addEventListener('click', function () {
                const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    alert('Please select at least one order.');
                    return;
                }

                const orderIds = Array.from(checkedBoxes).map(cb => cb.value);

                if (confirm(`Are you sure you want to mark ${orderIds.length} order(s) as shipped?`)) {
                    fetch('{{ route("admin.manual-shipping.bulk-mark-shipped") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order_ids: orderIds })
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
                            alert('An error occurred while marking orders as shipped.');
                        });
                }
            });
        });

        // Mark single order as shipped
        function markAsShipped(orderId) {
            if (confirm('Are you sure you want to mark this order as shipped?')) {
                fetch(`/admin/manual-shipping/${orderId}/mark-shipped`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                        alert('An error occurred while marking the order as shipped.');
                    });
            }
        }
    </script>
@endsection