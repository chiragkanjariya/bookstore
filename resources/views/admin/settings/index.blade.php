@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">System Settings</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Company Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="text-primary mb-3">Company Information</h4>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                       id="company_name" name="company_name" 
                                       value="{{ old('company_name', $settings['company']['company_name']) }}" required>
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="company_email" class="form-label">Company Email</label>
                                <input type="email" class="form-control @error('company_email') is-invalid @enderror" 
                                       id="company_email" name="company_email" 
                                       value="{{ old('company_email', $settings['company']['company_email']) }}">
                                @error('company_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="company_phone" class="form-label">Company Phone</label>
                                <input type="text" class="form-control @error('company_phone') is-invalid @enderror" 
                                       id="company_phone" name="company_phone" 
                                       value="{{ old('company_phone', $settings['company']['company_phone']) }}">
                                @error('company_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="company_place" class="form-label">Company Place</label>
                                <input type="text" class="form-control @error('company_place') is-invalid @enderror" 
                                       id="company_place" name="company_place" 
                                       value="{{ old('company_place', $settings['company']['company_place']) }}"
                                       placeholder="e.g., Mumbai, India">
                                @error('company_place')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <label for="company_address" class="form-label">Company Address</label>
                                <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                          id="company_address" name="company_address" rows="3"
                                          placeholder="Enter complete company address">{{ old('company_address', $settings['company']['company_address']) }}</textarea>
                                @error('company_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <!-- Payment Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="text-primary mb-3">Payment Gateway (Razorpay)</h4>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="razorpay_key_id" class="form-label">Razorpay Key ID</label>
                                <input type="text" class="form-control @error('razorpay_key_id') is-invalid @enderror" 
                                       id="razorpay_key_id" name="razorpay_key_id" 
                                       value="{{ old('razorpay_key_id', $settings['payment']['razorpay_key_id']) }}"
                                       placeholder="rzp_test_xxxxxxxxxx">
                                @error('razorpay_key_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="razorpay_key_secret" class="form-label">Razorpay Key Secret</label>
                                <input type="password" class="form-control @error('razorpay_key_secret') is-invalid @enderror" 
                                       id="razorpay_key_secret" name="razorpay_key_secret" 
                                       value="{{ old('razorpay_key_secret', $settings['payment']['razorpay_key_secret']) }}"
                                       placeholder="Enter Razorpay secret key">
                                @error('razorpay_key_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <!-- Shipping Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="text-primary mb-3">Shipping (Shiprocket)</h4>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="shiprocket_email" class="form-label">Shiprocket Email</label>
                                <input type="email" class="form-control @error('shiprocket_email') is-invalid @enderror" 
                                       id="shiprocket_email" name="shiprocket_email" 
                                       value="{{ old('shiprocket_email', $settings['shipping']['shiprocket_email']) }}"
                                       placeholder="your@shiprocket.email">
                                @error('shiprocket_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="shiprocket_password" class="form-label">Shiprocket Password</label>
                                <input type="password" class="form-control @error('shiprocket_password') is-invalid @enderror" 
                                       id="shiprocket_password" name="shiprocket_password" 
                                       value="{{ old('shiprocket_password', $settings['shipping']['shiprocket_password']) }}"
                                       placeholder="Enter Shiprocket password">
                                @error('shiprocket_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
