@extends('layouts.admin')

@section('breadcrumb', 'Edit User')

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
            <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
            <p class="mt-1 text-gray-600">Update {{ $user->name }}'s account details</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-sm rounded-lg">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6">
            @csrf
            @method('PUT')

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
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
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
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
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
                        <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
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
                                  placeholder="Enter address">{{ old('address', $user->address) }}</textarea>
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
                            New Password
                        </label>
                        <input type="password" name="password" id="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('password') border-red-300 @enderror"
                               placeholder="Leave blank to keep current password">
                        <p class="mt-1 text-xs text-gray-500">Leave blank to keep the current password</p>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirm New Password
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]"
                               placeholder="Confirm new password">
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                            User Role <span class="text-red-500">*</span>
                        </label>
                        <select name="role" id="role" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('role') border-red-300 @enderror"
                                {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                            <option value="">Select a role</option>
                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @if($user->id === auth()->id())
                            <input type="hidden" name="role" value="{{ $user->role }}">
                            <p class="mt-1 text-xs text-yellow-600">You cannot change your own role</p>
                        @endif
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
                                   {{ old('is_active', $user->email_verified_at ? true : false) ? 'checked' : '' }}
                                   {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                   class="h-4 w-4 text-[#00BDE0] focus:ring-[#00BDE0] border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active (user can login)
                            </label>
                        </div>
                        @if($user->id === auth()->id())
                            <input type="hidden" name="is_active" value="1">
                            <p class="mt-1 text-xs text-yellow-600">You cannot deactivate your own account</p>
                        @else
                            <p class="mt-1 text-xs text-gray-500">Inactive users cannot login to the system</p>
                        @endif
                    </div>

                    <!-- User Statistics -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">User Statistics</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Current Role:</span>
                                <span class="font-medium text-gray-900">{{ $user->getRoleName() }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Status:</span>
                                <span class="font-medium {{ $user->email_verified_at ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $user->email_verified_at ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500">Joined:</span>
                                <span class="font-medium text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Last Login:</span>
                                <span class="font-medium text-gray-900">
                                    {{ $user->last_login_at ? $user->last_login_at->format('M d, Y') : 'Never' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-between pt-6 border-t border-gray-200">
                <div>
                    <a href="{{ route('admin.users.show', $user) }}" 
                       class="text-[#00BDE0] hover:text-[#00A5C7] font-medium">
                        View User Details
                    </a>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.users.index') }}" 
                       class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-[#00BDE0] text-white px-6 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                        Update User
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Show/hide role permissions based on selection
document.getElementById('role').addEventListener('change', function() {
    // Role change logic can be added here if needed
});
</script>
@endsection
