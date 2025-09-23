@extends('layouts.admin')

@section('title', 'Account Reports')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Account Reports</h1>
            <p class="text-gray-600">Manage and export user account data with advanced filtering</p>
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
                           placeholder="Name or email..."
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

                <!-- Role (Duplicate removed - keeping original) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                    <div class="text-sm text-gray-500 py-2 px-3 bg-gray-50 rounded-md">
                        All users are active
                    </div>
                </div>

                <!-- Has Orders -->
                <div>
                    <label for="has_orders" class="block text-sm font-medium text-gray-700 mb-1">Has Orders</label>
                    <select id="has_orders" name="has_orders" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        <option value="">All Users</option>
                        <option value="yes" {{ request('has_orders') == 'yes' ? 'selected' : '' }}>With Orders</option>
                        <option value="no" {{ request('has_orders') == 'no' ? 'selected' : '' }}>Without Orders</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Registration From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Registration To</label>
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
                <div class="text-2xl font-bold text-[#00BDE0]">{{ $users->total() }}</div>
                <div class="text-sm text-gray-600">Total Users</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ $users->where('total_orders', '>', 0)->count() }}</div>
                <div class="text-sm text-gray-600">Users with Orders</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $users->sum('total_orders') }}</div>
                <div class="text-sm text-gray-600">Total Orders</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">₹{{ number_format($users->sum('total_spent'), 2) }}</div>
                <div class="text-sm text-gray-600">Total Revenue</div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Users</h3>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox rounded border-gray-300 text-[#00BDE0] focus:ring-[#00BDE0]">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-[#00BDE0] flex items-center justify-center">
                                            <span class="text-white font-medium text-sm">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $user->total_orders }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹{{ number_format($user->total_spent ?? 0, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="viewUserDetails({{ $user->id }})" class="text-[#00BDE0] hover:text-[#00A5C7] mr-3">
                                    View Details
                                </button>
                                <a href="{{ route('admin.users.show', $user) }}" class="text-gray-600 hover:text-gray-900">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No users found matching the criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- User Details Modal -->
<div id="user-details-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">User Details</h3>
                <button onclick="closeUserDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="user-details-content">
                <!-- User details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Combined Invoice Modal -->
<div id="invoice-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Generate Combined Invoice</h3>
                <button onclick="closeInvoiceModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="invoice-form" method="POST" action="{{ route('admin.reports.accounts.combined-invoice') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Selected Users</label>
                        <div id="selected-users-list" class="text-sm text-gray-600">
                            <!-- Selected users will be listed here -->
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="invoice_date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From (Optional)</label>
                            <input type="date" id="invoice_date_from" name="date_from" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        </div>
                        <div>
                            <label for="invoice_date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To (Optional)</label>
                            <input type="date" id="invoice_date_to" name="date_to" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeInvoiceModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Generate Invoice
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Select all functionality
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

document.getElementById('select-all-header').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    document.getElementById('select-all').checked = this.checked;
    updateSelectedCount();
});

// Individual checkbox change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('user-checkbox')) {
        updateSelectedCount();
    }
});

function updateSelectedCount() {
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    const count = selectedCheckboxes.length;
    
    document.getElementById('selected-count').textContent = count;
    document.getElementById('generate-invoice-btn').disabled = count === 0;
    
    // Update select all checkbox state
    const totalCheckboxes = document.querySelectorAll('.user-checkbox');
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
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    if (selectedCheckboxes.length === 0) return;
    
    // Update selected users list
    const selectedUsersList = document.getElementById('selected-users-list');
    selectedUsersList.innerHTML = '';
    
    selectedCheckboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const name = row.querySelector('.text-sm.font-medium').textContent;
        const email = row.querySelector('.text-sm.text-gray-500').textContent;
        
        const userDiv = document.createElement('div');
        userDiv.className = 'flex justify-between items-center py-1';
        userDiv.innerHTML = `
            <span>${name} (${email})</span>
            <input type="hidden" name="user_ids[]" value="${checkbox.value}">
        `;
        selectedUsersList.appendChild(userDiv);
    });
    
    document.getElementById('invoice-modal').classList.remove('hidden');
});

function closeInvoiceModal() {
    document.getElementById('invoice-modal').classList.add('hidden');
}

function viewUserDetails(userId) {
    fetch(`{{ route('admin.reports.accounts.user-details') }}?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('user-details-content');
            content.innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-[#00BDE0] flex items-center justify-center">
                            <span class="text-white font-medium text-xl">
                                ${data.name.substring(0, 2).toUpperCase()}
                            </span>
                        </div>
                        <div>
                            <h4 class="text-lg font-medium text-gray-900">${data.name}</h4>
                            <p class="text-gray-600">${data.email}</p>
                            <p class="text-sm text-gray-500">${data.role} • Registered ${new Date(data.created_at).toLocaleDateString()}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded">
                            <div class="text-sm text-gray-600">Total Orders</div>
                            <div class="text-lg font-semibold">${data.total_orders}</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded">
                            <div class="text-sm text-gray-600">Total Spent</div>
                            <div class="text-lg font-semibold">₹${parseFloat(data.total_spent || 0).toFixed(2)}</div>
                        </div>
                    </div>
                    
                    ${data.last_order ? `
                        <div class="bg-gray-50 p-3 rounded">
                            <div class="text-sm text-gray-600">Last Order</div>
                            <div class="text-sm">Order #${data.last_order.id} - ₹${parseFloat(data.last_order.total_amount).toFixed(2)}</div>
                            <div class="text-xs text-gray-500">${new Date(data.last_order.created_at).toLocaleDateString()}</div>
                        </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('user-details-modal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading user details');
        });
}

function closeUserDetailsModal() {
    document.getElementById('user-details-modal').classList.add('hidden');
}

// Initialize
updateSelectedCount();
</script>
@endsection
