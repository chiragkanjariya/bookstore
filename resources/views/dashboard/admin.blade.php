@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-red-600 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Admin Dashboard</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $user->name }}</dd>
                            <dd class="text-sm text-gray-600">{{ $user->email }}</dd>
                        </dl>
                    </div>
                    <div class="ml-5 flex-shrink-0">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $user->getRoleName() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form method="GET" class="flex flex-wrap items-end space-x-4">
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
                <div>
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200 font-medium">
                        Filter
                    </button>
                    <a href="{{ route('admin.dashboard') }}"
                        class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition duration-200 font-medium inline-block ml-2">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Paid Orders -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Paid Orders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['paid_orders']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Unpaid Orders -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-times-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Unpaid Orders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['unpaid_orders']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Integrated Courrier -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-shopping-cart text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Integrated Courrier</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['integrated_courrier']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['pending']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Shipment Created -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-truck text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Shipment Created</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['shipment_created']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Ready to Ship -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-double text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Ready to Ship</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['ready_to_ship']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Manual Orders -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-hand-paper text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Manual Orders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['manual_orders']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Bulk Orders -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-layer-group text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Bulk Orders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['bulk_orders']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-rupee-sign text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">₹{{ number_format($stats['total_revenue']) }}</p>
                    </div>
                </div>
            </div>
        </div>v>

        <!-- Admin Actions -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Admin Actions</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @menu('books')
                        <a href="{{ route('admin.books.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Add New Book</p>
                                <p class="text-xs text-gray-500">Add books to inventory</p>
                            </div>
                        </a>
                    @endmenu

                    @menu('books')
                        <a href="{{ route('admin.books.index') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Manage Books</p>
                                <p class="text-xs text-gray-500">View and manage all books</p>
                            </div>
                        </a>
                    @endmenu

                    @menu('categories')
                        <a href="{{ route('admin.categories.index') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Manage Categories</p>
                                <p class="text-xs text-gray-500">View and manage categories</p>
                            </div>
                        </a>
                    @endmenu

                    @menu('orders')
                        <a href="{{ route('admin.orders.index') }}" class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Manage Orders</p>
                                <p class="text-xs text-gray-500">View and manage orders</p>
                            </div>
                        </a>
                    @endmenu

                    <a href="{{ route('admin.settings.index') }}" class="flex items-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Settings</p>
                            <p class="text-xs text-gray-500">System configuration</p>
                        </div>
                    </a>

                    <a href="/" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">View Store</p>
                            <p class="text-xs text-gray-500">Visit customer view</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>


</div>
@endsection
