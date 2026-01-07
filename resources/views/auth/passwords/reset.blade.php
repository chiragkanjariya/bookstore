@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 bg-[#00BDE0] rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11.536 11 11 11.536m0 0l2.298 2.298m0 0l-3.328 3.328a5.83 5.83 0 00-1.743 0l-1.9 2a1.8 1.8 0 01-2.6 0l-.364-.316a1.9 1.9 0 010-2.656l7.85-7.665.207-.557C12.012 5.05 15.542 3.655 18 5.464M18 5.464l1.528 1.528" />
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Reset Password
                </h2>
            </div>

            <div class="mt-8 space-y-6">
                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="rounded-md shadow-sm space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] focus:z-10 sm:text-sm @error('email') border-red-500 @enderror"
                                placeholder="Email address" value="{{ $email ?? old('email') }}">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" required
                                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] focus:z-10 sm:text-sm @error('password') border-red-500 @enderror"
                                placeholder="New Password">
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password-confirm" class="block text-sm font-medium text-gray-700">Confirm
                                Password</label>
                            <input id="password-confirm" name="password_confirmation" type="password"
                                autocomplete="new-password" required
                                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] focus:z-10 sm:text-sm"
                                placeholder="Confirm New Password">
                        </div>
                    </div>

                    <div class="mt-5">
                        <button type="submit"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-[#00BDE0] hover:bg-[#00A5C7] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00BDE0]">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection