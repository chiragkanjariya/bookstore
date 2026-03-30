@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 max-w-4xl mx-auto">
    <h3 class="text-2xl font-semibold mb-6 text-gray-800">System Settings</h3>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Company Information -->
        <section>
            <h4 class="text-xl font-semibold text-[#00BDE0] mb-4">Company Information</h4>

            <div class="flex flex-wrap gap-6">
                <div class="flex-1 min-w-[250px]">
                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Company Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="company_name" name="company_name" required
                        value="{{ old('company_name', $settings['company']['company_name']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('company_name') border-red-500 @enderror">
                    @error('company_name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex-1 min-w-[250px]">
                    <label for="company_email" class="block text-sm font-medium text-gray-700 mb-1">Company Email</label>
                    <input type="email" id="company_email" name="company_email"
                        value="{{ old('company_email', $settings['company']['company_email']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('company_email') border-red-500 @enderror">
                    @error('company_email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-wrap gap-6 mt-4">
                <div class="flex-1 min-w-[250px]">
                    <label for="company_phone" class="block text-sm font-medium text-gray-700 mb-1">Company Phone</label>
                    <input type="text" id="company_phone" name="company_phone"
                        value="{{ old('company_phone', $settings['company']['company_phone']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('company_phone') border-red-500 @enderror">
                    @error('company_phone')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex-1 min-w-[250px]">
                    <label for="company_place" class="block text-sm font-medium text-gray-700 mb-1">Company Place</label>
                    <input type="text" id="company_place" name="company_place" placeholder="e.g., Mumbai, India"
                        value="{{ old('company_place', $settings['company']['company_place']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('company_place') border-red-500 @enderror">
                    @error('company_place')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <label for="company_address" class="block text-sm font-medium text-gray-700 mb-1">Company Address</label>
                <textarea id="company_address" name="company_address" rows="3" placeholder="Enter complete company address"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('company_address') border-red-500 @enderror">{{ old('company_address', $settings['company']['company_address']) }}</textarea>
                @error('company_address')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </section>

        <hr class="border-gray-300" />

        <!-- Payment Gateway -->
        <section>
            <h4 class="text-xl font-semibold text-[#00BDE0] mb-4">Payment Gateway (Razorpay)</h4>

            <div class="flex flex-wrap gap-6">
                <div class="flex-1 min-w-[250px]">
                    <label for="razorpay_key_id" class="block text-sm font-medium text-gray-700 mb-1">Razorpay Key ID</label>
                    <input type="text" id="razorpay_key_id" name="razorpay_key_id" placeholder="rzp_test_xxxxxxxxxx"
                        value="{{ old('razorpay_key_id', $settings['payment']['razorpay_key_id']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('razorpay_key_id') border-red-500 @enderror">
                    @error('razorpay_key_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex-1 min-w-[250px]">
                    <label for="razorpay_key_secret" class="block text-sm font-medium text-gray-700 mb-1">Razorpay Key Secret</label>
                    <input type="password" id="razorpay_key_secret" name="razorpay_key_secret" placeholder="Enter Razorpay secret key"
                        value="{{ old('razorpay_key_secret', $settings['payment']['razorpay_key_secret']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('razorpay_key_secret') border-red-500 @enderror">
                    @error('razorpay_key_secret')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <hr class="border-gray-300" />

        <!-- Shipping Settings -->
        <section>
            <h4 class="text-xl font-semibold text-[#00BDE0] mb-4">Shipping (Shiprocket)</h4>

            <div class="flex flex-wrap gap-6">
                <div class="flex-1 min-w-[250px]">
                    <label for="shiprocket_email" class="block text-sm font-medium text-gray-700 mb-1">Shiprocket Email</label>
                    <input type="email" id="shiprocket_email" name="shiprocket_email" placeholder="your@shiprocket.email"
                        value="{{ old('shiprocket_email', $settings['shipping']['shiprocket_email']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('shiprocket_email') border-red-500 @enderror">
                    @error('shiprocket_email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex-1 min-w-[250px]">
                    <label for="shiprocket_password" class="block text-sm font-medium text-gray-700 mb-1">Shiprocket Password</label>
                    <input type="password" id="shiprocket_password" name="shiprocket_password" placeholder="Enter Shiprocket password"
                        value="{{ old('shiprocket_password', $settings['shipping']['shiprocket_password']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('shiprocket_password') border-red-500 @enderror">
                    @error('shiprocket_password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <hr class="border-gray-300" />

        <!-- Courier Provider Settings -->
        <section>
            <h4 class="text-xl font-semibold text-[#00BDE0] mb-4">Courier Provider Settings</h4>

            <div class="mb-6">
                <label for="courier_provider" class="block text-sm font-medium text-gray-700 mb-2">
                    Active Courier Provider <span class="text-red-500">*</span>
                </label>
                <select id="courier_provider" name="courier_provider" required
                    class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                    <option value="shiprocket" {{ old('courier_provider', $settings['courier']['courier_provider']) == 'shiprocket' ? 'selected' : '' }}>Shiprocket</option>
                    <option value="shree_maruti" {{ old('courier_provider', $settings['courier']['courier_provider']) == 'shree_maruti' ? 'selected' : '' }}>Shree Maruti Courier</option>
                    <option value="none" {{ old('courier_provider', $settings['courier']['courier_provider']) == 'none' ? 'selected' : '' }}>None (Disable Courier Integration)</option>
                </select>
                <p class="text-sm text-gray-600 mt-1">Select which courier service to use for order fulfillment</p>
            </div>

            <!-- Shiprocket Settings -->
            <div class="mb-6 p-4 bg-gray-50 rounded-md">
                <div class="flex items-center mb-4">
                    <input type="checkbox" id="shiprocket_enabled" name="shiprocket_enabled" value="1"
                        {{ old('shiprocket_enabled', $settings['courier']['shiprocket_enabled']) ? 'checked' : '' }}
                        class="rounded text-[#00BDE0] focus:ring-[#00BDE0]">
                    <label for="shiprocket_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable Shiprocket</label>
                </div>
                <p class="text-xs text-gray-600 mb-3">Shiprocket credentials are configured in the "Shipping (Shiprocket)" section above</p>
            </div>

            <!-- Shree Maruti Courier Settings -->
            <div class="p-4 bg-gray-50 rounded-md">
                <div class="flex items-center mb-4">
                    <input type="checkbox" id="shree_maruti_enabled" name="shree_maruti_enabled" value="1"
                        {{ old('shree_maruti_enabled', $settings['courier']['shree_maruti_enabled']) ? 'checked' : '' }}
                        class="rounded text-[#00BDE0] focus:ring-[#00BDE0]">
                    <label for="shree_maruti_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable Shree Maruti Courier</label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="shree_maruti_client_name" class="block text-sm font-medium text-gray-700 mb-1">Client Name</label>
                        <input type="text" id="shree_maruti_client_name" name="shree_maruti_client_name"
                            value="{{ old('shree_maruti_client_name', $settings['courier']['shree_maruti_client_name']) }}"
                            placeholder="e.g., BAPS VISION"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                    </div>

                    <div>
                        <label for="shree_maruti_client_code" class="block text-sm font-medium text-gray-700 mb-1">Client Code</label>
                        <input type="text" id="shree_maruti_client_code" name="shree_maruti_client_code"
                            value="{{ old('shree_maruti_client_code', $settings['courier']['shree_maruti_client_code']) }}"
                            placeholder="e.g., 973096"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                    </div>

                    <div>
                        <label for="shree_maruti_username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" id="shree_maruti_username" name="shree_maruti_username"
                            value="{{ old('shree_maruti_username', $settings['courier']['shree_maruti_username']) }}"
                            placeholder="API Username"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                    </div>

                    <div>
                        <label for="shree_maruti_password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="shree_maruti_password" name="shree_maruti_password"
                            value="{{ old('shree_maruti_password', $settings['courier']['shree_maruti_password']) }}"
                            placeholder="API Password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                    </div>

                    <div>
                        <label for="shree_maruti_api_secret_key" class="block text-sm font-medium text-gray-700 mb-1">API Secret Key</label>
                        <input type="text" id="shree_maruti_api_secret_key" name="shree_maruti_api_secret_key"
                            value="{{ old('shree_maruti_api_secret_key', $settings['courier']['shree_maruti_api_secret_key']) }}"
                            placeholder="API Secret Key"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                    </div>

                    <div>
                        <label for="shree_maruti_environment" class="block text-sm font-medium text-gray-700 mb-1">Environment</label>
                        <select id="shree_maruti_environment" name="shree_maruti_environment"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                            <option value="beta" {{ old('shree_maruti_environment', $settings['courier']['shree_maruti_environment']) == 'beta' ? 'selected' : '' }}>Beta (Testing)</option>
                            <option value="production" {{ old('shree_maruti_environment', $settings['courier']['shree_maruti_environment']) == 'production' ? 'selected' : '' }}>Production</option>
                        </select>
                        <p class="text-xs text-gray-600 mt-1">Note: Production requires IP whitelisting</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="awb_number_prefix" class="block text-sm font-medium text-gray-700 mb-1">AWB Number Prefix</label>
                        <input type="text" id="awb_number_prefix" name="awb_number_prefix"
                            value="{{ old('awb_number_prefix', $settings['courier']['awb_number_prefix']) }}"
                            placeholder="e.g., IPDC"
                            maxlength="10"
                            class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        <p class="text-xs text-gray-600 mt-1">Prefix for auto-generated AWB numbers for manual shipping (e.g., IPDC251226000001)</p>
                    </div>
                </div>

                <!-- Maruti Series Tracking Section -->
                <div class="mt-8 border-t pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h5 class="text-lg font-medium text-[#00BDE0]">Maruti Series Tracking</h5>
                        <button type="button" id="test-increment" class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded inline-flex items-center gap-1 transition-colors">
                            <i class="fas fa-plus"></i> Test Increment
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="shree_maruti_series_start" class="block text-sm font-medium text-gray-700 mb-1">Maruti Series Start</label>
                            <input type="text" id="shree_maruti_series_start" name="shree_maruti_series_start"
                                value="{{ old('shree_maruti_series_start', $settings['courier']['shree_maruti_series_start']) }}"
                                placeholder=""
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        </div>

                        <div>
                            <label for="shree_maruti_series_end" class="block text-sm font-medium text-gray-700 mb-1">Maruti Series End</label>
                            <input type="text" id="shree_maruti_series_end" name="shree_maruti_series_end"
                                value="{{ old('shree_maruti_series_end', $settings['courier']['shree_maruti_series_end']) }}"
                                placeholder=""
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        </div>

                        <div>
                            <label for="shree_maruti_series_current" class="block text-sm font-medium text-gray-700 mb-1">Current Series Number</label>
                            <input type="text" id="shree_maruti_series_current" name="shree_maruti_series_current"
                                value="{{ old('shree_maruti_series_current', $settings['courier']['shree_maruti_series_current']) }}"
                                placeholder="Current active number"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                            <p class="text-xs text-gray-500 mt-1">This will automatically increment when a label is generated.</p>
                        </div>

                        <div>
                            <label for="shree_maruti_notify_threshold" class="block text-sm font-medium text-gray-700 mb-1">Notify when goes below</label>
                            <input type="text" id="shree_maruti_notify_threshold" name="shree_maruti_notify_threshold"
                                value="{{ old('shree_maruti_notify_threshold', $settings['courier']['shree_maruti_notify_threshold']) }}"
                                placeholder=""
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        </div>

                        <div class="md:col-span-2">
                            <label for="shree_maruti_notification_email" class="block text-sm font-medium text-gray-700 mb-1">Notification Email</label>
                            <input type="email" id="shree_maruti_notification_email" name="shree_maruti_notification_email"
                                value="{{ old('shree_maruti_notification_email', $settings['courier']['shree_maruti_notification_email']) }}"
                                placeholder=""
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-gray-300" />

        <!-- Bulk Purchase Settings -->
        <section>
            <h4 class="text-xl font-semibold text-[#00BDE0] mb-4">Bulk Purchase Settings</h4>

            <div class="flex flex-wrap gap-6">
                <div class="flex-1 min-w-[250px]">
                    <label for="min_bulk_purchase" class="block text-sm font-medium text-gray-700 mb-1">
                        Minimum Bulk Purchase Quantity <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="min_bulk_purchase" name="min_bulk_purchase" min="1" required
                        value="{{ old('min_bulk_purchase', $settings['bulk']['min_bulk_purchase']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('min_bulk_purchase') border-red-500 @enderror">
                    <p class="text-sm text-gray-600 mt-1">Orders with quantity equal to or above this value will get free shipping</p>
                    @error('min_bulk_purchase')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <!-- Submit Button -->
        <div>
            <button type="submit" class="bg-[#00BDE0] text-white px-6 py-2 rounded-md hover:bg-[#00A5C7] transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('test-increment').addEventListener('click', function() {
        if (!confirm('This will increment the series number in the database and check for notifications. Continue?')) {
            return;
        }

        const currentInput = document.getElementById('shree_maruti_series_current');
        const btn = this;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        fetch('{{ route('admin.settings.test-maruti-series') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentInput.value = data.current;
                btn.innerHTML = '<i class="fas fa-check"></i> Updated & Logged!';
                btn.classList.replace('bg-gray-200', 'bg-green-100');
                
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-plus"></i> Test Increment';
                    btn.classList.replace('bg-green-100', 'bg-gray-200');
                    btn.disabled = false;
                }, 3000);
            } else {
                alert('Error: ' + data.message);
                btn.innerHTML = '<i class="fas fa-plus"></i> Test Increment';
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while communicating with the server.');
            btn.innerHTML = '<i class="fas fa-plus"></i> Test Increment';
            btn.disabled = false;
        });
    });
</script>
@endpush
