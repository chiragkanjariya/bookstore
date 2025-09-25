<!-- Header -->
<header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50 " style="background-color: #00BDE0; color:#fff">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="flex items-center justify-between h-16 ">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="/" class="flex items-center space-x-3">
                    <img src="https://ipdc.org/wp-content/uploads/2022/12/logo.png" alt="IPDC" class="h-10">
                </a>
            </div>
            
            <!-- Search Bar - Center -->
            <div class="hidden sm:block flex-1 max-w-2xl mx-8">
                <div class="relative">
                    <input type="text" 
                           placeholder="Search books, authors, genres..." 
                           class="w-full pl-10 pr-4 py-2 border border-white-300 rounded-lg focus:ring-2 focus:ring-[#ffffff] focus:border-transparent">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                        <svg class="h-5 w-5 text-white-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Right side buttons -->
            <div class="flex items-center space-x-4">
                <!-- Authentication Section -->
                @auth
                    <!-- Cart and Wishlist Icons -->
                    <div class="flex items-center space-x-4 mr-4">
                        <!-- Wishlist -->
                        <a href="{{ route('wishlist.index') }}" class="relative text-white hover:text-gray-700 transition-colors p-2">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" xml:space="preserve" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:2">
                                <path d="M36.5 51.325 32 56.234 11.725 34.116c-4.579-4.996-4.241-12.769.754-17.348 4.996-4.579 12.769-4.241 17.348.754L32 19.893l2.173-2.371c4.579-4.995 12.352-5.333 17.348-.754 4.995 4.579 5.333 12.352.754 17.348L42.5 44.839" style="fill:none;stroke:currentColor;stroke-width:2px"/>
                                <path d="M14.868 31.187c-3.217-3.509-2.98-8.97.53-12.187M42 39v12M48 45H36" style="fill:none;stroke:currentColor;stroke-width:2px"/>
                            </svg>
                            @if(Auth::user()->wishlist_count > 0)
                                <span class="wishlist-count absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 min-w-5 px-1.5 flex items-center justify-center font-medium" style="    padding: 3px;border-radius: 100px;">
                                    {{ Auth::user()->wishlist_count }}
                                </span>
                            @else
                                <span class="wishlist-count absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 min-w-5 px-1.5 items-center justify-center font-medium hidden">
                                    0
                                </span>
                            @endif
                        </a>

                        <!-- Cart -->
                        <a href="{{ route('cart.index') }}" class="relative text-white hover:text-gray-700 transition-colors p-2">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                                <defs><style>.cls-1{fill:currentColor}</style></defs>
                                <g id="cart">
                                    <path class="cls-1" d="M29.46 10.14A2.94 2.94 0 0 0 27.1 9H10.22L8.76 6.35A2.67 2.67 0 0 0 6.41 5H3a1 1 0 0 0 0 2h3.41a.68.68 0 0 1 .6.31l1.65 3 .86 9.32a3.84 3.84 0 0 0 4 3.38h10.37a3.92 3.92 0 0 0 3.85-2.78l2.17-7.82a2.58 2.58 0 0 0-.45-2.27zM28 11.86l-2.17 7.83A1.93 1.93 0 0 1 23.89 21H13.48a1.89 1.89 0 0 1-2-1.56L10.73 11H27.1a1 1 0 0 1 .77.35.59.59 0 0 1 .13.51z"/>
                                    <circle class="cls-1" cx="14" cy="26" r="2"/>
                                    <circle class="cls-1" cx="24" cy="26" r="2"/>
                                </g>
                            </svg>
                            @if(Auth::user()->cart_count > 0)
                                <span class="cart-count absolute -top-1 -right-1 bg-[#00BDE0] text-white text-xs rounded-full h-5 min-w-5 px-1.5 flex items-center justify-center font-medium" style="background: #fff;padding: 3px;color: #01bde0;">
                                    {{ Auth::user()->cart_count }}
                                </span>
                            @else
                                <span class="cart-count absolute -top-1 -right-1 bg-[#00BDE0] text-white text-xs rounded-full h-5 min-w-5 px-1.5 items-center justify-center font-medium hidden" style="background: #fff;padding: 3px;color: #01bde0;">
                                    0
                                </span>
                            @endif
                        </a>
                    </div>

                    <!-- User Dropdown -->
                    <div class="relative" id="userDropdown">
                        <button class="flex items-center space-x-2 bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg transition-colors" id="userMenuButton">
                            <div class="w-8 h-8 bg-[#00BDE0] rounded-full flex items-center justify-center">
                                <span class="text-white font-medium text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                            <span class="hidden sm:block text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-60 bg-white rounded-md shadow-lg py-1 z-50 hidden" id="userMenu">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm text-gray-500">Signed in as</p>
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->email }}</p>
                                <p class="text-xs text-[#00BDE0]">{{ Auth::user()->getRoleName() }}</p>
                            </div>
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Admin Dashboard
                                </a>
                            @else
                                <a href="{{ route('user.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Dashboard
                                </a>
                            @endif
                            <a href="{{ route('user.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Profile
                            </a>
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                My Orders
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Cart and Wishlist Icons for Guests -->
                    <div class="flex items-center space-x-4 mr-4">
                        <!-- Wishlist -->
                        <button onclick="openAuthModal()" class="relative text-white hover:text-gray-700 transition-colors p-2" title="Sign in to view wishlist">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" xml:space="preserve" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:2">
                                <path d="M36.5 51.325 32 56.234 11.725 34.116c-4.579-4.996-4.241-12.769.754-17.348 4.996-4.579 12.769-4.241 17.348.754L32 19.893l2.173-2.371c4.579-4.995 12.352-5.333 17.348-.754 4.995 4.579 5.333 12.352.754 17.348L42.5 44.839" style="fill:none;stroke:currentColor;stroke-width:2px"/>
                                <path d="M14.868 31.187c-3.217-3.509-2.98-8.97.53-12.187M42 39v12M48 45H36" style="fill:none;stroke:currentColor;stroke-width:2px"/>
                            </svg>
                        </button>

                        <!-- Cart -->
                        <button onclick="openAuthModal()" class="relative text-white hover:text-gray-700 transition-colors p-2" title="Sign in to view cart">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                                <defs><style>.cls-1{fill:currentColor}</style></defs>
                                <g id="cart">
                                    <path class="cls-1" d="M29.46 10.14A2.94 2.94 0 0 0 27.1 9H10.22L8.76 6.35A2.67 2.67 0 0 0 6.41 5H3a1 1 0 0 0 0 2h3.41a.68.68 0 0 1 .6.31l1.65 3 .86 9.32a3.84 3.84 0 0 0 4 3.38h10.37a3.92 3.92 0 0 0 3.85-2.78l2.17-7.82a2.58 2.58 0 0 0-.45-2.27zM28 11.86l-2.17 7.83A1.93 1.93 0 0 1 23.89 21H13.48a1.89 1.89 0 0 1-2-1.56L10.73 11H27.1a1 1 0 0 1 .77.35.59.59 0 0 1 .13.51z"/>
                                    <circle class="cls-1" cx="14" cy="26" r="2"/>
                                    <circle class="cls-1" cx="24" cy="26" r="2"/>
                                </g>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Sign In Button -->
                    <button id="openAuthModal" class="bg-[#00BDE0] text-white px-4 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                        Sign In
                    </button>
                @endauth
            </div>
        </div>
        
        <!-- Mobile Search -->
        <div class="md:hidden pb-4">
            <div class="relative">
                <input type="text" 
                       placeholder="Search books, authors, genres..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00BDE0] focus:border-transparent">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</header>
