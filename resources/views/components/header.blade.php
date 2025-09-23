<!-- Header -->
<header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="/" class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-[#00BDE0] rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">BookStore</span>
                </a>
            </div>
            
            <!-- Search Bar - Center -->
            <div class="flex-1 max-w-2xl mx-8">
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
            
            <!-- Right side buttons -->
            <div class="flex items-center space-x-4">
                <!-- Authentication Section -->
                @auth
                    <!-- Cart and Wishlist Icons -->
                    <div class="flex items-center space-x-4 mr-4">
                        <!-- Wishlist -->
                        <a href="{{ route('wishlist.index') }}" class="relative text-gray-700 hover:text-[#00BDE0] transition-colors p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            @if(Auth::user()->wishlist_count > 0)
                                <span class="wishlist-count absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    {{ Auth::user()->wishlist_count }}
                                </span>
                            @else
                                <span class="wishlist-count absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 items-center justify-center hidden">
                                    0
                                </span>
                            @endif
                        </a>

                        <!-- Cart -->
                        <a href="{{ route('cart.index') }}" class="relative text-gray-700 hover:text-[#00BDE0] transition-colors p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 2.5M7 13l2.5 2.5m6.5 0a1.5 1.5 0 103 0 1.5 1.5 0 00-3 0zm-6 0a1.5 1.5 0 103 0 1.5 1.5 0 00-3 0z"/>
                            </svg>
                            @if(Auth::user()->cart_count > 0)
                                <span class="cart-count absolute -top-1 -right-1 bg-[#00BDE0] text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    {{ Auth::user()->cart_count }}
                                </span>
                            @else
                                <span class="cart-count absolute -top-1 -right-1 bg-[#00BDE0] text-white text-xs rounded-full h-5 w-5 items-center justify-center hidden">
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
                            <span class="text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden" id="userMenu">
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
                        <button onclick="openAuthModal()" class="relative text-gray-700 hover:text-[#00BDE0] transition-colors p-2" title="Sign in to view wishlist">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>

                        <!-- Cart -->
                        <button onclick="openAuthModal()" class="relative text-gray-700 hover:text-[#00BDE0] transition-colors p-2" title="Sign in to view cart">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 2.5M7 13l2.5 2.5m6.5 0a1.5 1.5 0 103 0 1.5 1.5 0 00-3 0zm-6 0a1.5 1.5 0 103 0 1.5 1.5 0 00-3 0z"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Sign In Button -->
                    <button id="openAuthModal" class="bg-[#00BDE0] text-white px-4 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                        Sign In
                    </button>
                @endauth
            </div>
            
            <!-- Mobile menu button -->
            <button class="md:hidden p-2 text-gray-600 hover:text-[#00BDE0] ml-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
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
