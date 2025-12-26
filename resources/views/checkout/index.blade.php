@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Checkout</h1>
                <nav class="text-sm text-gray-600">
                    <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('cart.index') }}" class="hover:text-blue-600">Cart</a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900">Checkout</span>
                </nav>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Checkout Form -->
                <div class="lg:col-span-2">
                    <form id="checkout-form">
                        @csrf
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
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number
                                        <span class="text-red-500">*</span></label>
                                    <input type="tel" id="phone" name="shipping_address[phone]" required pattern="[0-9]{10}"
                                        maxlength="10" placeholder="Enter 10 digit phone number"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <div class="text-red-500 text-sm mt-1 hidden" id="phone-error">Phone number must be
                                        exactly 10 digits.</div>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="address_line_1" class="block text-sm font-medium text-gray-700 mb-2">Address
                                        Line 1 <span class="text-red-500">*</span></label>
                                    <input type="text" id="address_line_1" name="shipping_address[address_line_1]" required
                                        minlength="10" placeholder="Enter complete address (minimum 10 characters)"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <div class="text-red-500 text-sm mt-1 hidden" id="address-error">Address must be at
                                        least 10 characters long.</div>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="address_line_2" class="block text-sm font-medium text-gray-700 mb-2">Address
                                        Line 2 (Optional)</label>
                                    <input type="text" id="address_line_2" name="shipping_address[address_line_2]"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" id="city" name="shipping_address[city]" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Enter city name">
                                </div>

                                <div>
                                    <label for="state" class="block text-sm font-medium text-gray-700 mb-2">State <span
                                            class="text-red-500">*</span></label>
                                    <select id="state" name="shipping_address[state_id]" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select State</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="district" class="block text-sm font-medium text-gray-700 mb-2">District
                                        <span class="text-red-500">*</span></label>
                                    <select id="district" name="shipping_address[district_id]" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        disabled>
                                        <option value="">Select District</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="taluka" class="block text-sm font-medium text-gray-700 mb-2">Taluka <span
                                            class="text-red-500">*</span></label>
                                    <select id="taluka" name="shipping_address[taluka_id]" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        disabled>
                                        <option value="">Select Taluka</option>
                                    </select>
                                </div>

                                <div class="relative">
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">Postal
                                        Code <span class="text-red-500">*</span></label>
                                    <input type="text" id="postal_code" name="shipping_address[postal_code]" required
                                        pattern="[0-9]{6}" maxlength="6" placeholder="Enter 6 digit postal code"
                                        autocomplete="off"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                                    <!-- Autocomplete Dropdown -->
                                    <div id="zipcode-suggestions"
                                        class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                                        <!-- Suggestions will be populated here -->
                                    </div>

                                    <!-- Non-serviceable Warning -->
                                    <div id="zipcode-warning"
                                        class="hidden mt-2 p-3 bg-orange-50 border border-orange-200 rounded-md">
                                        <div class="flex items-start">
                                            <i class="fas fa-exclamation-triangle text-orange-600 mt-0.5 mr-2"></i>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-orange-800">Manual Shipping Required</p>
                                                <p class="text-xs text-orange-700 mt-1">This postal code is not serviceable
                                                    by our courier partner. Your order will be processed manually and may
                                                    take additional time for delivery.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Serviceable Confirmation -->
                                    <div id="zipcode-success"
                                        class="hidden mt-2 p-3 bg-green-50 border border-green-200 rounded-md">
                                        <div class="flex items-start">
                                            <i class="fas fa-check-circle text-green-600 mt-0.5 mr-2"></i>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-green-800">Serviceable Area</p>
                                                <p class="text-xs text-green-700 mt-1" id="zipcode-details"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="country"
                                        class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                    <select id="country" name="shipping_address[country]" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="India">India</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                    </form>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>

                        <!-- Cart Items -->
                        <div class="space-y-4 mb-6">
                            @foreach($cartItems as $item)
                                <div class="flex items-center space-x-3">
                                    <img src="{{ $item->book->cover_image_url }}" alt="{{ $item->book->title }}"
                                        class="w-12 h-16 object-cover rounded">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-medium text-gray-900 truncate">{{ $item->book->title }}</h3>
                                        <p class="text-sm text-gray-500">{{ $item->book->author }}</p>
                                        <p class="text-sm text-gray-600">Qty: {{ $item->quantity }}</p>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">
                                        ₹{{ number_format($item->total_price, 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border-t pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="text-gray-900">₹{{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Shipping</span>
                                <span class="text-gray-900">
                                    @if($shipping == 0)
                                        <span class="text-green-600 font-medium">FREE</span>
                                    @else
                                        ₹{{ number_format($shipping, 2) }}
                                    @endif
                                </span>
                            </div>
                            @php
                                $totalQuantity = $cartItems->sum('quantity');
                                $minBulkPurchase = \App\Models\Setting::get('min_bulk_purchase', 10);
                                $isBulkPurchase = $totalQuantity >= $minBulkPurchase;
                            @endphp
                            @if($isBulkPurchase)
                                <div class="bg-green-50 border border-green-200 rounded-md p-2">
                                    <p class="text-xs text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        <strong>Bulk Purchase!</strong> You qualify for free shipping ({{ $totalQuantity }}
                                        items ≥ {{ $minBulkPurchase }} items)
                                    </p>
                                </div>
                            @endif
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
                            <span id="btn-text">Place Order</span>
                            <span id="btn-loading" class="hidden">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
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
        document.addEventListener('DOMContentLoaded', function () {
            const placeOrderBtn = document.getElementById('place-order-btn');
            const btnText = document.getElementById('btn-text');
            const btnLoading = document.getElementById('btn-loading');
            const checkoutForm = document.getElementById('checkout-form');

            // Custom validation functions
            function validatePhone(phone) {
                const phoneRegex = /^[0-9]{10}$/;
                return phoneRegex.test(phone);
            }

            function validateAddress(address) {
                return address && address.length >= 10;
            }

            // Real-time validation
            const phoneInput = document.getElementById('phone');
            const addressInput = document.getElementById('address_line_1');
            const phoneError = document.getElementById('phone-error');
            const addressError = document.getElementById('address-error');

            phoneInput.addEventListener('input', function () {
                const phone = this.value.replace(/[^0-9]/g, ''); // Remove non-numeric characters
                this.value = phone; // Set cleaned value

                if (phone.length > 0 && !validatePhone(phone)) {
                    phoneError.classList.remove('hidden');
                    this.classList.add('border-red-500');
                } else {
                    phoneError.classList.add('hidden');
                    this.classList.remove('border-red-500');
                }
            });

            addressInput.addEventListener('input', function () {
                const address = this.value.trim();

                if (address.length > 0 && !validateAddress(address)) {
                    addressError.classList.remove('hidden');
                    this.classList.add('border-red-500');
                } else {
                    addressError.classList.add('hidden');
                    this.classList.remove('border-red-500');
                }
            });

            placeOrderBtn.addEventListener('click', function () {
                // Custom validation before form submission
                const phone = phoneInput.value.trim();
                const address = addressInput.value.trim();
                let isValid = true;

                // Validate phone
                if (!validatePhone(phone)) {
                    phoneError.classList.remove('hidden');
                    phoneInput.classList.add('border-red-500');
                    isValid = false;
                }

                // Validate address
                if (!validateAddress(address)) {
                    addressError.classList.remove('hidden');
                    addressInput.classList.add('border-red-500');
                    isValid = false;
                }

                // Standard form validation
                if (!checkoutForm.checkValidity() || !isValid) {
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
                                name: 'IPDC',
                                description: 'Book Purchase',
                                order_id: data.razorpay_order_id,
                                prefill: {
                                    name: data.user.name,
                                    email: data.user.email,
                                    contact: data.user.phone
                                },
                                theme: {
                                    color: '#3B82F6'
                                },
                                handler: function (response) {
                                    // Payment successful
                                    const successForm = document.createElement('form');
                                    successForm.method = 'POST';
                                    successForm.action = '{{ route("checkout.payment.success") }}';

                                    const fields = {
                                        '_token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'razorpay_order_id': response.razorpay_order_id,
                                        'razorpay_payment_id': response.razorpay_payment_id,
                                        'razorpay_signature': response.razorpay_signature,
                                        'order_id': data.order_id
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
                                    ondismiss: function () {
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

        // Location Dropdown Functionality
        document.addEventListener('DOMContentLoaded', function () {
            const stateSelect = document.getElementById('state');
            const districtSelect = document.getElementById('district');
            const talukaSelect = document.getElementById('taluka');

            // Load states on page load
            loadStates();

            // State change handler
            stateSelect.addEventListener('change', function () {
                const stateId = this.value;
                if (stateId) {
                    loadDistricts(stateId);
                    districtSelect.disabled = false;
                    talukaSelect.disabled = true;
                    talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
                } else {
                    districtSelect.disabled = true;
                    talukaSelect.disabled = true;
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                    talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
                }
            });

            // District change handler
            districtSelect.addEventListener('change', function () {
                const districtId = this.value;
                if (districtId) {
                    loadTalukas(districtId);
                    talukaSelect.disabled = false;
                } else {
                    talukaSelect.disabled = true;
                    talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
                }
            });

            function loadStates() {
                fetch('/api/locations/states')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            stateSelect.innerHTML = '<option value="">Select State</option>';
                            data.data.forEach(state => {
                                const option = document.createElement('option');
                                option.value = state.id;
                                option.textContent = state.name;
                                stateSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error loading states:', error));
            }

            function loadDistricts(stateId) {
                fetch(`/api/locations/districts?state_id=${stateId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            districtSelect.innerHTML = '<option value="">Select District</option>';
                            data.data.forEach(district => {
                                const option = document.createElement('option');
                                option.value = district.id;
                                option.textContent = district.name;
                                districtSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error loading districts:', error));
            }

            function loadTalukas(districtId) {
                fetch(`/api/locations/talukas?district_id=${districtId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
                            data.data.forEach(taluka => {
                                const option = document.createElement('option');
                                option.value = taluka.id;
                                option.textContent = taluka.name;
                                talukaSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error loading talukas:', error));
            }
        });

        // Zipcode Autocomplete and Validation
        document.addEventListener('DOMContentLoaded', function () {
            const postalCodeInput = document.getElementById('postal_code');
            const suggestionsDiv = document.getElementById('zipcode-suggestions');
            const warningDiv = document.getElementById('zipcode-warning');
            const successDiv = document.getElementById('zipcode-success');
            const detailsSpan = document.getElementById('zipcode-details');

            let debounceTimer;
            let selectedZipcode = null;

            // Autocomplete on input
            postalCodeInput.addEventListener('input', function () {
                const query = this.value.trim();

                // Clear previous state
                warningDiv.classList.add('hidden');
                successDiv.classList.add('hidden');
                selectedZipcode = null;

                // Clear debounce timer
                clearTimeout(debounceTimer);

                if (query.length >= 3) {
                    // Debounce API call
                    debounceTimer = setTimeout(() => {
                        fetchAutocompleteSuggestions(query);
                    }, 300);
                } else {
                    suggestionsDiv.classList.add('hidden');
                    suggestionsDiv.innerHTML = '';
                }
            });

            // Validate on blur (when user leaves the field)
            postalCodeInput.addEventListener('blur', function () {
                const zipcode = this.value.trim();

                // Delay to allow click on suggestion
                setTimeout(() => {
                    suggestionsDiv.classList.add('hidden');

                    if (zipcode.length === 6 && !selectedZipcode) {
                        validateZipcode(zipcode);
                    }
                }, 200);
            });

            // Fetch autocomplete suggestions
            function fetchAutocompleteSuggestions(query) {
                fetch(`/api/zipcodes/autocomplete?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            displaySuggestions(data.data);
                        } else {
                            suggestionsDiv.classList.add('hidden');
                            suggestionsDiv.innerHTML = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching autocomplete suggestions:', error);
                        suggestionsDiv.classList.add('hidden');
                    });
            }

            // Display autocomplete suggestions
            function displaySuggestions(suggestions) {
                suggestionsDiv.innerHTML = '';

                suggestions.forEach(suggestion => {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                    div.innerHTML = `
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-medium text-gray-900">${suggestion.pincode}</span>
                                    <span class="text-sm text-gray-600 ml-2">${suggestion.city}, ${suggestion.state_code}</span>
                                </div>
                                <span class="text-xs text-gray-500">${suggestion.hub}</span>
                            </div>
                        `;

                    div.addEventListener('click', function () {
                        postalCodeInput.value = suggestion.pincode;
                        selectedZipcode = suggestion;
                        suggestionsDiv.classList.add('hidden');

                        // Show success message
                        showSuccess(suggestion);
                    });

                    suggestionsDiv.appendChild(div);
                });

                suggestionsDiv.classList.remove('hidden');
            }

            // Validate zipcode
            function validateZipcode(zipcode) {
                fetch('/api/zipcodes/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ pincode: zipcode })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.is_serviceable && data.details) {
                                showSuccess(data.details);
                            } else {
                                showWarning();
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error validating zipcode:', error);
                    });
            }

            // Show success message
            function showSuccess(details) {
                warningDiv.classList.add('hidden');
                successDiv.classList.remove('hidden');
                detailsSpan.textContent = `Delivery available to ${details.city}, ${details.state_code} via ${details.hub} hub`;
            }

            // Show warning message
            function showWarning() {
                successDiv.classList.add('hidden');
                warningDiv.classList.remove('hidden');
            }

            // Close suggestions when clicking outside
            document.addEventListener('click', function (event) {
                if (!postalCodeInput.contains(event.target) && !suggestionsDiv.contains(event.target)) {
                    suggestionsDiv.classList.add('hidden');
                }
            });
        });
    </script>
@endsection