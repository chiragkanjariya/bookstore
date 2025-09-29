@extends('layouts.app')

@section('title', 'Terms of Use')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Terms of Use</h1>
                <p class="text-gray-600">Last updated: {{ date('F d, Y') }}</p>
            </div>
            
            <div class="prose prose-lg max-w-none">
                <div class="space-y-8">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-8 border border-blue-100">
                        <div class="text-center mb-6">
                            <svg class="w-16 h-16 text-blue-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Terms of Use</h2>
                            <p class="text-gray-600">Terms and conditions for using B. A. P. S. VISION services</p>
                        </div>
                        
                        <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 space-y-6">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800 mb-4">Terms and Conditions</h3>
                                <p class="text-gray-700 leading-relaxed">
                                    These terms and conditions govern your use of B. A. P. S. VISION (IPDC Store) website and services. By accessing or using our services, you agree to be bound by these terms.
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Use of Services</h4>
                                <p class="text-gray-700 leading-relaxed mb-4">
                                    You may use our services only as permitted by law and these terms. You are responsible for your conduct and content when using our services.
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">User Responsibilities</h4>
                                <p class="text-gray-700 leading-relaxed mb-4">
                                    Users must provide accurate information, maintain account security, and use the platform in accordance with applicable laws and regulations.
                                </p>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-6">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Contact Information</h4>
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <p class="text-gray-700 mb-2"><strong>B. A. P. S. VISION (IPDC Store)</strong></p>
                                    <p class="text-gray-700 mb-2">
                                        IPDC - Integrated Personality development Course<br>
                                        BAPS Pramukh Academy, Shastri Yagnapurush Marg,<br>
                                        near BAPS Shastriji Maharaj Hospital, Atladara,<br>
                                        Vadodara, Gujarat 390007
                                    </p>
                                    <p class="text-gray-700">
                                        Email: <a href="mailto:service.ipdc@in.baps.org" class="text-blue-600 hover:text-blue-700 font-medium">service.ipdc@in.baps.org</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
