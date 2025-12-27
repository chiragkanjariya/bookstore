<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'IPDC') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: #00BDE0;
            --primary-hover: #00A5C7;
            --primary-dark: #008DA8;
            --sidebar-width: 256px;
        }

        /* Ensure proper responsive behavior */
        @media (max-width: 1023px) {
            body {
                overflow-x: hidden;
            }
        }

        /* Fix for sidebar positioning on larger screens */
        @media (min-width: 1024px) {
            #sidebar {
                position: fixed !important;
                transform: translateX(0) !important;
            }
        }

        /* Smooth transitions */
        #sidebar {
            will-change: transform;
        }

        /* Prevent content shift */
        .main-content {
            transition: margin-left 0.3s ease-in-out;
        }
    </style>
</head>

<body class="bg-gray-50 font-inter overflow-x-hidden">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden hidden"></div>

    <!-- Sidebar -->
    <div id="sidebar"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0">
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div
                class="flex items-center justify-between h-16 px-6 border-b border-gray-200 transition-colors bg-[#00BDE0] text-white">
                <div class="flex items-center space-x-3">
                    <div>
                        <img src="https://ipdc.org/wp-content/uploads/2022/12/logo.png" alt="IPDC" class="h-10">
                    </div>
                </div>
                <!-- Mobile Close Button -->
                <button id="close-sidebar" class="lg:hidden p-2 text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#00BDE0] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z" />
                    </svg>
                    Dashboard
                </a>

                <!-- Divider -->
                <div class="border-t border-gray-200 my-4"></div>

                <!-- Inventory Management -->
                <div class="mb-6">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Inventory</h3>

                    <!-- Books -->
                    <a href="{{ route('admin.books.index') }}"
                        class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.books.*') ? 'bg-[#00BDE0] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Books
                        <span
                            class="ml-auto bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">{{ \App\Models\Book::count() }}</span>
                    </a>

                    <!-- Categories -->
                    <a href="{{ route('admin.categories.index') }}"
                        class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-[#00BDE0] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Categories
                        <span
                            class="ml-auto bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">{{ \App\Models\Category::count() }}</span>
                    </a>
                </div>

                <!-- User Management -->
                <div class="mb-6">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Users</h3>

                    <!-- Users -->
                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-[#00BDE0] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                        Manage Users
                        <span
                            class="ml-auto bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">{{ \App\Models\User::count() }}</span>
                    </a>
                </div>

                <!-- Orders Management (Placeholder) -->
                <div class="mb-6">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Sales</h3>

                    <!-- Orders -->
                    <a href="{{ route('admin.orders.index') }}"
                        class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.orders.*') ? 'bg-[#00BDE0] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Orders
                        <span
                            class="ml-auto bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">{{ \App\Models\Order::count() }}</span>
                    </a>
                    <!-- Manual Shipping -->
                    <a href="{{ route('admin.manual-shipping.index') }}"
                        class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.manual-shipping.*') ? 'bg-[#00BDE0] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Manual Shipping
                        <span
                            class="ml-auto bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">{{ \App\Models\Order::where('requires_manual_shipping', '1')->count() }}</span>
                    </a>
                </div>

                <!-- Reports -->
                <div class="mb-6">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Reports</h3>

                    <!-- Account Reports -->
                    <a href="{{ route('admin.reports.accounts.index') }}"
                        class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.reports.accounts.*') ? 'bg-[#00BDE0] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Account Reports
                    </a>
                </div>

                <!-- Settings -->
                <div class="mb-6">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">System</h3>

                    <!-- Settings -->
                    <a href="{{ route('admin.settings.index') }}"
                        class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.settings.*') ? 'bg-[#00BDE0] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Settings
                    </a>
                </div>
            </nav>

            <!-- User Info & Actions -->
            <div class="border-t border-gray-200 p-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-[#00BDE0] rounded-full flex items-center justify-center">
                        <span class="text-white font-medium text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>

                <div class="mt-3 flex space-x-2">
                    <a href="/"
                        class="flex-1 text-center bg-gray-100 text-gray-700 px-3 py-2 rounded-lg text-sm hover:bg-gray-200 transition-colors">
                        View Site
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit"
                            class="w-full bg-red-100 text-red-700 px-3 py-2 rounded-lg text-sm hover:bg-red-200 transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content lg:pl-64 min-h-screen">
        <!-- Top Header -->
        <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-center justify-between px-4 sm:px-6 py-4">
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="lg:hidden p-2 text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Breadcrumb -->
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#00BDE0]">Admin</a>
                    @if(!request()->routeIs('admin.dashboard'))
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="text-gray-900 font-medium">@yield('breadcrumb', 'Page')</span>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="flex items-center space-x-3">
                    <!-- Add New Dropdown -->
                    <div class="relative" id="add-dropdown">
                        <button id="add-button"
                            class="bg-[#00BDE0] text-white px-4 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span>Add New</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="add-menu"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden">
                            <a href="{{ route('admin.books.create') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Add New Book
                            </a>
                            <a href="{{ route('admin.categories.create') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Add New Category
                            </a>
                            <a href="{{ route('admin.users.create') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Add New User
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    <!-- JavaScript for Sidebar -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const closeSidebarButton = document.getElementById('close-sidebar');
            const addButton = document.getElementById('add-button');
            const addMenu = document.getElementById('add-menu');

            // Prevent body scroll when sidebar is open on mobile
            function toggleBodyScroll(shouldDisable) {
                if (shouldDisable) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }

            // Mobile menu toggle function
            function toggleSidebar() {
                const isHidden = sidebar.classList.contains('-translate-x-full');
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');

                // Only prevent scroll on mobile
                if (window.innerWidth < 1024) {
                    toggleBodyScroll(!isHidden);
                }
            }

            // Event listeners for sidebar toggle
            mobileMenuButton.addEventListener('click', toggleSidebar);
            closeSidebarButton.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);

            // Add dropdown toggle
            addButton.addEventListener('click', function (e) {
                e.preventDefault();
                addMenu.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function (e) {
                if (!addButton.contains(e.target) && !addMenu.contains(e.target)) {
                    addMenu.classList.add('hidden');
                }
            });

            // Close sidebar on window resize to desktop
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.add('hidden');
                } else {
                    // Ensure sidebar is hidden on mobile after resize
                    if (!overlay.classList.contains('hidden')) {
                        sidebar.classList.add('-translate-x-full');
                        overlay.classList.add('hidden');
                    }
                }
            });

        });
    </script>
</body>

</html>