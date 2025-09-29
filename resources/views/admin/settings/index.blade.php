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
