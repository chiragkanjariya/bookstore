@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Order #{{ $order->order_number }}</h1>
            <nav class="text-sm text-gray-600">
                <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('orders.index') }}" class="hover:text-blue-600">Orders</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Order Details</span>
            </nav>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Status -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Order Status</h2>
                        <div class="flex space-x-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $order->status_badge_color }}-100 text-{{ $order->status_badge_color }}-800">
                                {{ ucfirst($order->status) }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $order->payment_status_badge_color }}-100 text-{{ $order->payment_status_badge_color }}-800">
                                Payment {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">Order Date</p>
                            <p class="font-medium">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Payment Method</p>
                            <p class="font-medium">{{ ucfirst($order->payment_method) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Total Amount</p>
                            <p class="font-medium text-lg">{{ $order->formatted_total }}</p>
                        </div>
                    </div>

                    @if($order->canBeCancelled())
                    <div class="mt-6 pt-6 border-t">
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                    onclick="return confirm('Are you sure you want to cancel this order?')">
                                Cancel Order
                            </button>
                        </form>
                    </div>
                    @endif
                </div>

                <!-- Order Items -->
                <div class="bg-white rounded-lg shadow-md p-6">
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
                                    <span class="text-gray-600">Price: {{ $item->formatted_price }}</span>
                                    @if($item->shipping_price > 0)
                                    <span class="text-gray-600">Shipping: ₹{{ number_format($item->shipping_price, 2) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-semibold text-gray-900">{{ $item->formatted_total }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Shipping Address</h2>
                    
                    <div class="text-sm space-y-1">
                        <p class="font-medium">{{ $order->shipping_address['name'] }}</p>
                        <p>{{ $order->shipping_address['phone'] }}</p>
                        <p>{{ $order->shipping_address['address_line_1'] }}</p>
                        @if(!empty($order->shipping_address['address_line_2']))
                        <p>{{ $order->shipping_address['address_line_2'] }}</p>
                        @endif
                        <p>{{ $order->shipping_address['city'] }}, {{ $order->shipping_address['state'] }} {{ $order->shipping_address['postal_code'] }}</p>
                        <p>{{ $order->shipping_address['country'] }}</p>
                    </div>
                </div>

                @if($order->notes)
                <!-- Order Notes -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Notes</h2>
                    <p class="text-gray-700">{{ $order->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Order Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
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
                        <h3 class="font-medium text-gray-900 mb-2">Payment Information</h3>
                        <div class="text-sm space-y-1">
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

                    <!-- Actions -->
                    <div class="mt-6 pt-6 border-t space-y-3">
                        @if($order->payment_status === 'paid')
                        <a href="{{ route('orders.invoice', $order) }}" 
                           class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 font-medium text-center block transition duration-200">
                            Download Invoice
                        </a>
                        @endif
                        
                        <a href="{{ route('orders.index') }}" 
                           class="w-full bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 font-medium text-center block transition duration-200">
                            Back to Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
