@extends('layouts.admin')

@section('breadcrumb', 'Add User')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center">
        <a href="{{ route('admin.users.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add New User</h1>
            <p class="mt-1 text-gray-600">Create a new user account</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-sm rounded-lg">
        <form method="POST" action="{{ route('admin.users.store') }}" class="p-6">
            @csrf

            <!-- Error Messages -->
            @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('name') border-red-300 @enderror"
                               placeholder="Enter full name">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('email') border-red-300 @enderror"
                               placeholder="Enter email address">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number
                        </label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('phone') border-red-300 @enderror"
                               placeholder="Enter phone number">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                            Address
                        </label>
                        <textarea name="address" id="address" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('address') border-red-300 @enderror"
                                  placeholder="Enter address">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" id="password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('password') border-red-300 @enderror"
                               placeholder="Enter password">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]"
                               placeholder="Confirm password">
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                            User Role <span class="text-red-500">*</span>
                        </label>
                        <select name="role" id="role" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('role') border-red-300 @enderror">
                            <option value="">Select a role</option>
                            <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Status</label>
                        <div class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-[#00BDE0] focus:ring-[#00BDE0] border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active (user can login)
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Inactive users cannot login to the system</p>
                    </div>

                    <!-- Role Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Role Permissions</h4>
                        <div class="space-y-2 text-sm text-gray-600">
                            <div id="user-permissions" class="space-y-1">
                                <p><strong>User:</strong></p>
                                <ul class="list-disc list-inside ml-2 space-y-1">
                                    <li>Access user dashboard</li>
                                    <li>Browse and purchase books</li>
                                    <li>Manage personal profile</li>
                                    <li>View order history</li>
                                </ul>
                            </div>
                            <div id="admin-permissions" class="space-y-1 hidden">
                                <p><strong>Admin:</strong></p>
                                <ul class="list-disc list-inside ml-2 space-y-1">
                                    <li>Full system access</li>
                                    <li>Manage books and categories</li>
                                    <li>Manage users</li>
                                    <li>View analytics and reports</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.users.index') }}" 
                   class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-[#00BDE0] text-white px-6 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Show/hide role permissions based on selection
document.getElementById('role').addEventListener('change', function() {
    const userPermissions = document.getElementById('user-permissions');
    const adminPermissions = document.getElementById('admin-permissions');
    
    if (this.value === 'admin') {
        userPermissions.classList.add('hidden');
        adminPermissions.classList.remove('hidden');
    } else {
        userPermissions.classList.remove('hidden');
        adminPermissions.classList.add('hidden');
    }
});
</script>
@endsection
