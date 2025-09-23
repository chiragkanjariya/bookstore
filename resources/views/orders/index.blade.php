@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Orders</h1>
            <nav class="text-sm text-gray-600">
                <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('user.dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Orders</span>
            </nav>
        </div>

        @if($orders->count() > 0)
            <!-- Orders List -->
            <div class="space-y-6">
                @foreach($orders as $order)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Order Header -->
                    <div class="bg-gray-50 px-6 py-4 border-b">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex flex-col md:flex-row md:items-center md:space-x-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Order #{{ $order->order_number }}</h3>
                                    <p class="text-sm text-gray-600">Placed on {{ $order->created_at->format('M d, Y') }}</p>
                                </div>
                                
                                <div class="mt-2 md:mt-0 flex space-x-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $order->status_badge_color }}-100 text-{{ $order->status_badge_color }}-800">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $order->payment_status_badge_color }}-100 text-{{ $order->payment_status_badge_color }}-800">
                                        Payment {{ ucfirst($order->payment_status) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-4 md:mt-0 text-right">
                                <p class="text-lg font-semibold text-gray-900">{{ $order->formatted_total }}</p>
                                <p class="text-sm text-gray-600">{{ $order->orderItems->count() }} item(s)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            @foreach($order->orderItems->take(2) as $item)
                            <div class="flex items-center space-x-4">
                                <img src="{{ $item->book->cover_image_url }}" alt="{{ $item->book->title }}" 
                                     class="w-12 h-16 object-cover rounded">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item->book->title }}</h4>
                                    <p class="text-sm text-gray-500">{{ $item->book->author }}</p>
                                    <p class="text-sm text-gray-600">Qty: {{ $item->quantity }} × {{ $item->formatted_price }}</p>
                                </div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $item->formatted_total }}
                                </div>
                            </div>
                            @endforeach
                            
                            @if($order->orderItems->count() > 2)
                            <p class="text-sm text-gray-600 text-center">
                                and {{ $order->orderItems->count() - 2 }} more item(s)
                            </p>
                            @endif
                        </div>
                    </div>

                    <!-- Order Actions -->
                    <div class="bg-gray-50 px-6 py-4 border-t">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex space-x-4">
                                <a href="{{ route('orders.show', $order) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Details
                                </a>
                                
                                @if($order->payment_status === 'paid')
                                <a href="{{ route('orders.invoice', $order) }}" 
                                   class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    Download Invoice
                                </a>
                                @endif
                            </div>
                            
                            @if($order->canBeCancelled())
                            <form action="{{ route('orders.cancel', $order) }}" method="POST" class="mt-4 md:mt-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                        onclick="return confirm('Are you sure you want to cancel this order?')">
                                    Cancel Order
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-shopping-bag text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No orders yet</h3>
                <p class="text-gray-600 mb-6">You haven't placed any orders yet. Start shopping to see your orders here.</p>
                <a href="{{ route('home') }}" 
                   class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium transition duration-200">
                    Start Shopping
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
