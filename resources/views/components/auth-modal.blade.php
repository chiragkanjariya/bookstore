<!-- Auth Modal -->
<div id="authModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Sign In</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Login Form -->
            <div id="loginForm" class="auth-form">
                <form id="loginFormElement" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="login_email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="login_email" name="email" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm">
                            <span class="text-red-500 text-sm hidden" id="login_email_error"></span>
                        </div>

                        <div>
                            <label for="login_password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" id="login_password" name="password" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm">
                            <span class="text-red-500 text-sm hidden" id="login_password_error"></span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember_me" name="remember" type="checkbox" 
                                       class="h-4 w-4 text-[#00BDE0] focus:ring-[#00BDE0] border-gray-300 rounded">
                                <label for="remember_me" class="ml-2 block text-sm text-gray-900">Remember me</label>
                            </div>
                            <div class="text-sm">
                                <a href="#" class="font-medium text-[#00BDE0] hover:text-[#00A5C7]">Forgot password?</a>
                            </div>
                        </div>

                        <div>
                            <button type="submit" id="loginSubmitBtn"
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#00BDE0] hover:bg-[#00A5C7] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00BDE0] disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="submit-text">Sign In</span>
                                <span class="loading-spinner hidden">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Signing In...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account? 
                        <button id="showRegisterForm" class="font-medium text-[#00BDE0] hover:text-[#00A5C7]">Sign up</button>
                    </p>
                </div>
            </div>

            <!-- Register Form -->
            <div id="registerForm" class="auth-form hidden">
                <form id="registerFormElement" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="register_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" id="register_name" name="name" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm">
                            <span class="text-red-500 text-sm hidden" id="register_name_error"></span>
                        </div>

                        <div>
                            <label for="register_email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="register_email" name="email" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm">
                            <span class="text-red-500 text-sm hidden" id="register_email_error"></span>
                        </div>

                        <div>
                            <label for="register_phone" class="block text-sm font-medium text-gray-700">Phone (Optional)</label>
                            <input type="tel" id="register_phone" name="phone"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm">
                            <span class="text-red-500 text-sm hidden" id="register_phone_error"></span>
                        </div>

                        <div>
                            <label for="register_password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" id="register_password" name="password" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm">
                            <span class="text-red-500 text-sm hidden" id="register_password_error"></span>
                        </div>

                        <div>
                            <label for="register_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" id="register_password_confirmation" name="password_confirmation" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm">
                        </div>


                        <div>
                            <button type="submit" id="registerSubmitBtn"
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#00BDE0] hover:bg-[#00A5C7] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00BDE0] disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="submit-text">Sign Up</span>
                                <span class="loading-spinner hidden">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Creating Account...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account? 
                        <button id="showLoginForm" class="font-medium text-[#00BDE0] hover:text-[#00A5C7]">Sign in</button>
                    </p>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div id="authMessage" class="hidden mt-4 p-3 rounded-md">
                <p id="authMessageText" class="text-sm"></p>
            </div>
        </div>
    </div>
</div>
