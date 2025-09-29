@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#00BDE0]">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Home
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Shopping Cart</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>
        <p class="mt-2 text-gray-600">Review your items and proceed to checkout</p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    @endif

    @if($cartItems->count() > 0)
        <div class="lg:grid lg:grid-cols-3 lg:gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900">Cart Items ({{ $cartItems->count() }})</h2>
                        <form method="POST" action="{{ route('cart.clear') }}" class="inline" onsubmit="return confirm('Are you sure you want to clear your cart?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                Clear Cart
                            </button>
                        </form>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @foreach($cartItems as $item)
                            <div class="p-6" data-item-id="{{ $item->id }}">
                                <div class="flex items-start space-x-4">
                                    <!-- Book Image -->
                                    <div class="flex-shrink-0">
                                        <a href="{{ route('book.show', $item->book->slug) }}">
                                            <img src="{{ $item->book->cover_image_url }}" alt="{{ $item->book->title }}" 
                                                 class="w-20 h-28 object-cover rounded-lg">
                                        </a>
                                    </div>

                                    <!-- Book Details -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    <a href="{{ route('book.show', $item->book->slug) }}" class="hover:text-[#00BDE0]">
                                                        {{ $item->book->title }}
                                                    </a>
                                                </h3>
                                                <p class="text-sm text-gray-600 mt-1">by {{ $item->book->author }}</p>
                                                <div class="flex items-center space-x-2 mt-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-[#00BDE0] text-white">
                                                        {{ $item->book->category->name }}
                                                    </span>
                                                    @if($item->book->language !== 'English')
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-800">
                                                            {{ $item->book->language }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Remove Button -->
                                            <form method="POST" action="{{ route('cart.destroy', $item) }}" class="remove-item-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 p-2">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Price and Quantity -->
                                        <div class="flex items-center justify-between mt-4">
                                            <div class="flex items-center space-x-4">
                                                <!-- Quantity Controls -->
                                                <div class="flex items-center border border-gray-300 rounded-lg">
                                                    <button type="button" class="quantity-btn p-2 hover:bg-gray-100" data-action="decrease" data-item-id="{{ $item->id }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                        </svg>
                                                    </button>
                                                    <input type="number" 
                                                           class="quantity-input w-16 text-center border-0 focus:ring-0" 
                                                           value="{{ $item->quantity }}" 
                                                           min="1" 
                                                           max="{{ $item->max_quantity }}"
                                                           data-item-id="{{ $item->id }}">
                                                    <button type="button" class="quantity-btn p-2 hover:bg-gray-100" data-action="increase" data-item-id="{{ $item->id }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <!-- Stock Status -->
                                                @if($item->book->stock < $item->quantity)
                                                    <span class="text-red-600 text-sm font-medium">Limited Stock!</span>
                                                @elseif($item->book->stock <= 5)
                                                    <span class="text-yellow-600 text-sm">Only {{ $item->book->stock }} left</span>
                                                @endif
                                            </div>

                                            <!-- Price -->
                                            <div class="text-right">
                                                <p class="text-lg font-semibold text-gray-900 item-total">₹{{ number_format($item->total_price, 2) }}</p>
                                                <p class="text-sm text-gray-500">₹{{ number_format($item->price, 2) }} each</p>
                                                @if($item->book->shipping_price > 0)
                                                    <p class="text-xs text-gray-500">+ ₹{{ number_format($item->book->shipping_price, 2) }} shipping</p>
                                                @else
                                                    <p class="text-xs text-green-600">Free shipping</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="mt-8 lg:mt-0">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-6">Order Summary</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal ({{ $cartItems->sum('quantity') }} items)</span>
                            <span class="font-medium" id="subtotal">₹{{ number_format($subtotal, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium" id="shipping">
                                @if($shipping > 0)
                                    ₹{{ number_format($shipping, 2) }}
                                @else
                                    Free
                                @endif
                            </span>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold text-gray-900">Total</span>
                                <span class="text-lg font-semibold text-gray-900" id="total">₹{{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <a href="{{ route('checkout.index') }}" class="w-full bg-[#00BDE0] text-white py-3 px-4 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium text-lg text-center block">
                            Proceed to Checkout
                        </a>
                        <a href="{{ route('home') }}" class="w-full bg-gray-200 text-gray-900 py-3 px-4 rounded-lg hover:bg-gray-300 transition-colors font-medium text-center block">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty Cart -->
        <div class="text-center py-16">
            <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Your cart is empty</h3>
            <p class="mt-2 text-gray-500">Start shopping to add items to your cart.</p>
            <div class="mt-6">
                <a href="{{ route('home') }}" 
                   class="bg-[#00BDE0] text-white px-6 py-3 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                    Start Shopping
                </a>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            const itemId = this.dataset.itemId;
            const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
            let currentValue = parseInt(input.value);
            
            if (action === 'increase' && currentValue < parseInt(input.max)) {
                input.value = currentValue + 1;
            } else if (action === 'decrease' && currentValue > 1) {
                input.value = currentValue - 1;
            }
            
            updateQuantity(itemId, input.value);
        });
    });

    // Direct quantity input
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const itemId = this.dataset.itemId;
            let value = parseInt(this.value);
            
            if (value < 1) value = 1;
            if (value > parseInt(this.max)) value = parseInt(this.max);
            
            this.value = value;
            updateQuantity(itemId, value);
        });
    });

    // Remove item forms
    document.querySelectorAll('.remove-item-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!confirm('Remove this item from your cart?')) return;
            
            fetch(this.action, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error removing item from cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error removing item from cart');
            });
        });
    });

    function updateQuantity(itemId, quantity) {
        fetch(`/cart/${itemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ quantity: quantity })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update item total
                document.querySelector(`[data-item-id="${itemId}"] .item-total`).textContent = 
                    `₹${parseFloat(data.item_total).toFixed(2)}`;
                
                // Update order summary
                document.getElementById('subtotal').textContent = `₹${parseFloat(data.subtotal).toFixed(2)}`;
                document.getElementById('shipping').textContent = 
                    data.shipping > 0 ? `₹${parseFloat(data.shipping).toFixed(2)}` : 'Free';
                document.getElementById('total').textContent = `₹${parseFloat(data.total).toFixed(2)}`;
                
                // Update cart count in header
                updateCartCount(data.cart_count);
            } else {
                alert(data.message || 'Error updating cart');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating cart');
            location.reload();
        });
    }

    function updateCartCount(count) {
        const cartBadges = document.querySelectorAll('.cart-count');
        cartBadges.forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        });
    }
});
</script>
@endsection
