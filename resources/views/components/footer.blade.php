<!-- Footer -->
<footer class="bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Company Info -->
            <div class="col-span-1 lg:col-span-2">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="flex items-center">
                        <a href="/" class="flex items-center space-x-3">
                            <img src="https://ipdc.org/wp-content/uploads/2022/12/logo.png" alt="IPDC" class="h-10">
                        </a>
                    </div>
                </div>
                
        
                </div>
            </div>
            
            <!-- Important Pages -->
            <div>
                <ul class="space-y-3 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8">
                    <li><a href="{{ route('pages.about-us') }}" class="text-gray-300 hover:text-[#00BDE0] transition-colors">About Us</a></li>
                    <li><a href="{{ route('pages.contact-us') }}" class="text-gray-300 hover:text-[#00BDE0] transition-colors">Contact Us</a></li>
                    <li><a href="{{ route('pages.privacy-policy') }}" class="text-gray-300 hover:text-[#00BDE0] transition-colors">Privacy Policy</a></li>
                    <li><a href="{{ route('pages.terms-of-use') }}" class="text-gray-300 hover:text-[#00BDE0] transition-colors">Terms of Use</a></li>
                    <li><a href="{{ route('pages.payment-policies') }}" class="text-gray-300 hover:text-[#00BDE0] transition-colors">Payment Policies</a></li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-800 mt-12 pt-8 ">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">
                    © {{ date('Y') }} IPDC. All rights reserved.
                </p>
                <div class="flex flex-wrap justify-center md:justify-end space-x-4 mt-4 md:mt-0">
                    <a href="{{ route('pages.privacy-policy') }}" class="text-gray-400 hover:text-[#00BDE0] text-sm transition-colors">Privacy Policy</a>
                    <a href="{{ route('pages.terms-of-use') }}" class="text-gray-400 hover:text-[#00BDE0] text-sm transition-colors">Terms of Use</a>
                    <a href="{{ route('pages.payment-policies') }}" class="text-gray-400 hover:text-[#00BDE0] text-sm transition-colors">Payment Policies</a>
                    <a href="{{ route('pages.about-us') }}" class="text-gray-400 hover:text-[#00BDE0] text-sm transition-colors">About Us</a>
                    <a href="{{ route('pages.contact-us') }}" class="text-gray-400 hover:text-[#00BDE0] text-sm transition-colors">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</footer>
