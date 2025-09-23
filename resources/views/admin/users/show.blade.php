@extends('layouts.admin')

@section('breadcrumb', 'User Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <a href="{{ route('admin.users.index') }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                <p class="mt-1 text-gray-600">User account details and management</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.users.edit', $user) }}" 
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                Edit User
            </a>
            @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" 
                      class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors font-medium">
                        Delete User
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Success Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif

    <!-- User Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Avatar and Quick Actions -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <!-- User Avatar -->
                <div class="text-center mb-6">
                    <div class="mx-auto h-24 w-24 {{ $user->isAdmin() ? 'bg-red-500' : 'bg-[#00BDE0]' }} rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-2xl">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                    <div class="mt-2 flex justify-center">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $user->isAdmin() ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $user->getRoleName() }}
                        </span>
                    </div>
                </div>

                <!-- Quick Status Update -->
                @if($user->id !== auth()->id())
                    <div class="space-y-4 border-t pt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quick Status Toggle</label>
                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="w-full {{ $user->email_verified_at ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} px-4 py-2 rounded-md transition-colors font-medium">
                                    {{ $user->email_verified_at ? 'Deactivate User' : 'Activate User' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- User Information -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $user->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <p class="mt-1 text-gray-900">{{ $user->email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <p class="mt-1 text-gray-900">{{ $user->phone ?: 'Not provided' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <div class="mt-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $user->isAdmin() ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $user->getRoleName() }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Account Status</label>
                            <div class="mt-1">
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Member Since</label>
                            <p class="mt-1 text-gray-900">{{ $user->created_at->format('F j, Y') }}</p>
                            <p class="text-sm text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Login</label>
                            <p class="mt-1 text-gray-900">
                                {{ $user->last_login_at ? $user->last_login_at->format('F j, Y \a\t g:i A') : 'Never logged in' }}
                            </p>
                            @if($user->last_login_at)
                                <p class="text-sm text-gray-500">{{ $user->last_login_at->diffForHumans() }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                            <p class="mt-1 text-gray-900">{{ $user->updated_at->format('F j, Y \a\t g:i A') }}</p>
                            <p class="text-sm text-gray-500">{{ $user->updated_at->diffForHumans() }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Account ID</label>
                            <p class="mt-1 text-sm text-gray-600 font-mono">{{ $user->id }}</p>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                @if($user->address)
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 leading-relaxed">{{ $user->address }}</p>
                        </div>
                    </div>
                @endif

                <!-- Role Permissions -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Role Permissions</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        @if($user->isAdmin())
                            <div class="space-y-2">
                                <p class="font-medium text-red-800">Administrator Access:</p>
                                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1 ml-2">
                                    <li>Full system access and control</li>
                                    <li>Manage books, categories, and inventory</li>
                                    <li>Manage user accounts and permissions</li>
                                    <li>View analytics and system reports</li>
                                    <li>System configuration and settings</li>
                                </ul>
                            </div>
                        @else
                            <div class="space-y-2">
                                <p class="font-medium text-green-800">Standard User Access:</p>
                                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1 ml-2">
                                    <li>Browse and search book catalog</li>
                                    <li>Purchase books and manage orders</li>
                                    <li>Manage personal profile and preferences</li>
                                    <li>View order history and tracking</li>
                                    <li>Add books to wishlist and favorites</li>
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-between">
        <div>
            @if($user->id === auth()->id())
                <span class="text-sm text-[#00BDE0] font-medium">This is your account</span>
            @endif
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.users.index') }}" 
               class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium">
                Back to Users
            </a>
            <a href="{{ route('admin.users.create') }}" 
               class="bg-[#00BDE0] text-white px-4 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                Add Another User
            </a>
        </div>
    </div>
</div>
@endsection
