@extends('layouts.app')

@section('title', 'Checkout - Buy Now')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Checkout - Buy Now</h1>
            <nav class="text-sm text-gray-600">
                <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('book.show', $buyNowItem->book) }}" class="hover:text-blue-600">{{ $buyNowItem->book->title }}</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Checkout</span>
            </nav>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-2">
                <form id="checkout-form">
                    @csrf
                    <!-- Hidden fields for buy now -->
                    <input type="hidden" name="buy_now_book_id" value="{{ $buyNowItem->book->id }}">
                    <input type="hidden" name="buy_now_quantity" value="{{ $buyNowItem->quantity }}">
                    
                    <!-- Shipping Address -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Shipping Address</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <input type="text" id="name" name="shipping_address[name]" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       value="{{ auth()->user()->name }}">
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" id="phone" name="shipping_address[phone]" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="address_line_1" class="block text-sm font-medium text-gray-700 mb-2">Address Line 1</label>
                                <input type="text" id="address_line_1" name="shipping_address[address_line_1]" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="address_line_2" class="block text-sm font-medium text-gray-700 mb-2">Address Line 2 (Optional)</label>
                                <input type="text" id="address_line_2" name="shipping_address[address_line_2]"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                <input type="text" id="city" name="shipping_address[city]" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="state" class="block text-sm font-medium text-gray-700 mb-2">State</label>
                                <input type="text" id="state" name="shipping_address[state]" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                                <input type="text" id="postal_code" name="shipping_address[postal_code]" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                <select id="country" name="shipping_address[country]" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="India">India</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Notes (Optional)</h2>
                        <textarea name="notes" rows="3" placeholder="Any special instructions for delivery..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>
                    
                    <!-- Book Item -->
                    <div class="mb-6">
                        <div class="flex items-center space-x-4">
                            <img src="{{ $buyNowItem->book->cover_image_url }}" alt="{{ $buyNowItem->book->title }}" 
                                 class="w-16 h-20 object-cover rounded">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-medium text-gray-900">{{ $buyNowItem->book->title }}</h3>
                                <p class="text-sm text-gray-500">{{ $buyNowItem->book->author }}</p>
                                <p class="text-sm text-gray-600">Category: {{ $buyNowItem->book->category->name }}</p>
                                <p class="text-sm text-gray-600">Qty: {{ $buyNowItem->quantity }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="border-t pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Price ({{ $buyNowItem->quantity }} × ₹{{ number_format($buyNowItem->book->price, 2) }})</span>
                            <span class="text-gray-900">₹{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Shipping</span>
                            <span class="text-gray-900">₹{{ number_format($shipping, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax (18% GST)</span>
                            <span class="text-gray-900">₹{{ number_format($tax, 2) }}</span>
                        </div>
                        <div class="border-t pt-2">
                            <div class="flex justify-between text-lg font-semibold">
                                <span class="text-gray-900">Total</span>
                                <span class="text-gray-900">₹{{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Place Order Button -->
                    <button type="button" id="place-order-btn"
                            class="w-full mt-6 bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium transition duration-200">
                        <span id="btn-text">Buy Now</span>
                        <span id="btn-loading" class="hidden">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>

                    <!-- Security Note -->
                    <div class="mt-4 text-xs text-gray-500 text-center">
                        <i class="fas fa-lock mr-1"></i>
                        Your payment information is secure and encrypted
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Razorpay Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const placeOrderBtn = document.getElementById('place-order-btn');
    const btnText = document.getElementById('btn-text');
    const btnLoading = document.getElementById('btn-loading');
    const checkoutForm = document.getElementById('checkout-form');

    placeOrderBtn.addEventListener('click', function() {
        // Validate form
        if (!checkoutForm.checkValidity()) {
            checkoutForm.reportValidity();
            return;
        }

        // Show loading state
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        placeOrderBtn.disabled = true;

        // Submit checkout form
        const formData = new FormData(checkoutForm);
        
        fetch('{{ route("checkout.process") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Initialize Razorpay
                const options = {
                    key: data.key,
                    amount: data.amount,
                    currency: data.currency,
                    name: 'BookStore',
                    description: 'Book Purchase - {{ $buyNowItem->book->title }}',
                    order_id: data.razorpay_order_id,
                    prefill: {
                        name: data.user.name,
                        email: data.user.email,
                        contact: data.user.phone
                    },
                    theme: {
                        color: '#3B82F6'
                    },
                    handler: function(response) {
                        // Payment successful
                        const successForm = document.createElement('form');
                        successForm.method = 'POST';
                        successForm.action = '{{ route("checkout.payment.success") }}';
                        
                        const fields = {
                            '_token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'razorpay_order_id': response.razorpay_order_id,
                            'razorpay_payment_id': response.razorpay_payment_id,
                            'razorpay_signature': response.razorpay_signature,
                            'order_id': data.order_id,
                            'buy_now': '1'
                        };
                        
                        Object.keys(fields).forEach(key => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = fields[key];
                            successForm.appendChild(input);
                        });
                        
                        document.body.appendChild(successForm);
                        successForm.submit();
                    },
                    modal: {
                        ondismiss: function() {
                            // Reset button state
                            btnText.classList.remove('hidden');
                            btnLoading.classList.add('hidden');
                            placeOrderBtn.disabled = false;
                        }
                    }
                };
                
                const rzp = new Razorpay(options);
                rzp.open();
            } else {
                alert(data.message || 'Something went wrong. Please try again.');
                // Reset button state
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
                placeOrderBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong. Please try again.');
            // Reset button state
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
            placeOrderBtn.disabled = false;
        });
    });
});
</script>
@endsection
