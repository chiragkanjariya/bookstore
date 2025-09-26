@extends('layouts.admin')

@section('title', 'Account Reports')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Order Reports</h1>
            <p class="text-gray-600">View and export paid orders data with advanced filtering</p>
        </div>
        <div class="flex space-x-3">
            <button id="export-csv-btn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </button>
            <button id="generate-invoice-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors" disabled>
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Generate Invoice (<span id="selected-count">0</span>)
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm rounded-lg p-6">
        <form id="filter-form" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                           placeholder="Name, email or phone..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select id="role" name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Order Number -->
                <div>
                    <label for="order_number" class="block text-sm font-medium text-gray-700 mb-1">Order Number</label>
                    <input type="text" id="order_number" name="order_number" value="{{ request('order_number') }}"
                           placeholder="Order number..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                </div>

                <!-- Payment ID -->
                <div>
                    <label for="payment_id" class="block text-sm font-medium text-gray-700 mb-1">Payment ID</label>
                    <input type="text" id="payment_id" name="payment_id" value="{{ request('payment_id') }}"
                           placeholder="Razorpay payment ID..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Order Date From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Order Date To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                </div>
            </div>

            <div class="flex space-x-3">
                <button type="submit" class="bg-[#00BDE0] text-white px-6 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors">
                    Apply Filters
                </button>
                <a href="{{ route('admin.reports.accounts.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                    Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="bg-white shadow-sm rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-[#00BDE0]">{{ $orders->total() }}</div>
                <div class="text-sm text-gray-600">Total Orders</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ $totalOrders }}</div>
                <div class="text-sm text-gray-600">Paid Orders</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">₹{{ number_format($totalRevenue, 2) }}</div>
                <div class="text-sm text-gray-600">Total Revenue</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">₹{{ number_format($totalShipping, 2) }}</div>
                <div class="text-sm text-gray-600">Total Shipping</div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Paid Orders</h3>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="select-all" class="rounded border-gray-300 text-[#00BDE0] focus:ring-[#00BDE0]">
                    <label for="select-all" class="text-sm text-gray-600">Select All</label>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="select-all-header" class="rounded border-gray-300 text-[#00BDE0] focus:ring-[#00BDE0]">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                        @php
                            $user = $order->user;
                            $shippingAddress = $order->shipping_address;
                            $totalExcludingShipping = $order->total_amount - $order->shipping_cost;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox rounded border-gray-300 text-[#00BDE0] focus:ring-[#00BDE0]">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-[#00BDE0] flex items-center justify-center">
                                            <span class="text-white font-medium text-sm">
                                                {{ strtoupper(substr($user->name ?? 'N', 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $user->phone ?? ($shippingAddress['phone'] ?? 'N/A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="space-y-1">
                                    <div><strong>Country:</strong> {{ $shippingAddress['country'] ?? 'India' }}</div>
                                    <div><strong>State:</strong> {{ $user->state->name ?? ($shippingAddress['state'] ?? 'N/A') }}</div>
                                    <div><strong>District:</strong> {{ $user->district->name ?? ($shippingAddress['district'] ?? 'N/A') }}</div>
                                    <div><strong>Taluka:</strong> {{ $user->taluka->name ?? ($shippingAddress['taluka'] ?? 'N/A') }}</div>
                                    <div><strong>City:</strong> {{ $shippingAddress['city'] ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="space-y-1">
                                    <div><strong>Order #:</strong> {{ $order->order_number }}</div>
                                    <div><strong>Invoice #:</strong> IPDC-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    <div><strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="space-y-1">
                                    <div><strong>Total:</strong> ₹{{ number_format($order->total_amount, 2) }}</div>
                                    <div><strong>Shipping:</strong> ₹{{ number_format($order->shipping_cost, 2) }}</div>
                                    <div><strong>Excl. Shipping:</strong> ₹{{ number_format($totalExcludingShipping, 2) }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="space-y-1">
                                    <div><strong>Payment ID:</strong> {{ $order->razorpay_payment_id ?? 'N/A' }}</div>
                                    <div><strong>Order ID:</strong> {{ $order->razorpay_order_id ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="viewOrderDetails({{ $order->id }})" class="text-[#00BDE0] hover:text-[#00A5C7] mr-3">
                                    View Details
                                </button>
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-gray-600 hover:text-gray-900">
                                    View Order
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                No paid orders found matching the criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Order Details Modal -->
<div id="order-details-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Order Details</h3>
                <button onclick="closeOrderDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="order-details-content">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
</div>


<script>
// Select all functionality
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

document.getElementById('select-all-header').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    document.getElementById('select-all').checked = this.checked;
    updateSelectedCount();
});

// Individual checkbox change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('order-checkbox')) {
        updateSelectedCount();
    }
});

function updateSelectedCount() {
    const selectedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
    const count = selectedCheckboxes.length;
    
    document.getElementById('selected-count').textContent = count;
    document.getElementById('generate-invoice-btn').disabled = count === 0;
    
    // Update select all checkbox state
    const totalCheckboxes = document.querySelectorAll('.order-checkbox');
    const selectAllCheckbox = document.getElementById('select-all');
    const selectAllHeaderCheckbox = document.getElementById('select-all-header');
    
    if (count === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
        selectAllHeaderCheckbox.indeterminate = false;
        selectAllHeaderCheckbox.checked = false;
    } else if (count === totalCheckboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
        selectAllHeaderCheckbox.indeterminate = false;
        selectAllHeaderCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
        selectAllCheckbox.checked = false;
        selectAllHeaderCheckbox.indeterminate = true;
        selectAllHeaderCheckbox.checked = false;
    }
}

// Export CSV
document.getElementById('export-csv-btn').addEventListener('click', function() {
    const form = document.getElementById('filter-form');
    form.action = '{{ route("admin.reports.accounts.export-csv") }}';
    form.submit();
});

// Generate Invoice
document.getElementById('generate-invoice-btn').addEventListener('click', function() {
    const selectedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
    if (selectedCheckboxes.length === 0) return;
    
    // Create a form and submit it directly
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.reports.accounts.combined-invoice") }}';
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add selected order IDs
    selectedCheckboxes.forEach(checkbox => {
        const orderIdInput = document.createElement('input');
        orderIdInput.type = 'hidden';
        orderIdInput.name = 'order_ids[]';
        orderIdInput.value = checkbox.value;
        form.appendChild(orderIdInput);
    });
    
    // Add form to document and submit
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
});


function viewOrderDetails(orderId) {
    fetch(`{{ route('admin.reports.accounts.order-details') }}?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('order-details-content');
            const user = data.user;
            const shippingAddress = data.shipping_address;
            const totalExcludingShipping = data.total_amount - data.shipping_cost;
            
            content.innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-[#00BDE0] flex items-center justify-center">
                            <span class="text-white font-medium text-xl">
                                ${(user.name || 'N').substring(0, 2).toUpperCase()}
                            </span>
                        </div>
                        <div>
                            <h4 class="text-lg font-medium text-gray-900">${user.name || 'N/A'}</h4>
                            <p class="text-gray-600">${user.email || 'N/A'}</p>
                            <p class="text-sm text-gray-500">Order #${data.order_number} • ${new Date(data.created_at).toLocaleDateString()}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded">
                            <div class="text-sm text-gray-600">Total Amount</div>
                            <div class="text-lg font-semibold">₹${parseFloat(data.total_amount).toFixed(2)}</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded">
                            <div class="text-sm text-gray-600">Shipping Cost</div>
                            <div class="text-lg font-semibold">₹${parseFloat(data.shipping_cost).toFixed(2)}</div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-3 rounded">
                        <div class="text-sm text-gray-600">Payment Details</div>
                        <div class="text-sm">Payment ID: ${data.razorpay_payment_id || 'N/A'}</div>
                        <div class="text-sm">Order ID: ${data.razorpay_order_id || 'N/A'}</div>
                    </div>
                    
                    <div class="bg-gray-50 p-3 rounded">
                        <div class="text-sm text-gray-600">Shipping Address</div>
                        <div class="text-sm">
                            ${shippingAddress.city || 'N/A'}, ${shippingAddress.district || 'N/A'}<br>
                            ${shippingAddress.state || 'N/A'}, ${shippingAddress.country || 'India'}
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('order-details-modal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading order details');
        });
}

function closeOrderDetailsModal() {
    document.getElementById('order-details-modal').classList.add('hidden');
}

// Initialize
updateSelectedCount();
</script>
@endsection

