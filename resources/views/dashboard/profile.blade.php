@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Profile Header -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 bg-[#00BDE0] rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-2xl">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                    </div>
                    <div class="ml-6 flex-1">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                        <p class="text-gray-600">{{ $user->email }}</p>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->isAdmin() ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $user->getRoleName() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">Profile Information</h3>
                
                <!-- Success Message -->
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('user.profile.update') }}" id="profileForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm @error('name') border-red-300 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm @error('email') border-red-300 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm @error('phone') border-red-300 @enderror">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea name="address" id="address" rows="3"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm @error('address') border-red-300 @enderror">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Location Details -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Location Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- State -->
                                <div>
                                    <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                                    <select name="state_id" id="state"
                                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm @error('state_id') border-red-300 @enderror">
                                        <option value="">Select State</option>
                                    </select>
                                    @error('state_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- District -->
                                <div>
                                    <label for="district" class="block text-sm font-medium text-gray-700">District</label>
                                    <select name="district_id" id="district"
                                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm @error('district_id') border-red-300 @enderror" disabled>
                                        <option value="">Select District</option>
                                    </select>
                                    @error('district_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Taluka -->
                                <div>
                                    <label for="taluka" class="block text-sm font-medium text-gray-700">Taluka</label>
                                    <select name="taluka_id" id="taluka"
                                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] sm:text-sm @error('taluka_id') border-red-300 @enderror" disabled>
                                        <option value="">Select Taluka</option>
                                    </select>
                                    @error('taluka_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Account Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Member Since</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('F j, Y') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Last Login</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ $user->last_login_at ? $user->last_login_at->format('F j, Y \a\t g:i A') : 'Never' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Account Status</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email Verified</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        @if($user->email_verified_at)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Verified
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-6 flex items-center justify-between">
                        <div>
                            @if($user->isUser())
                                <a href="{{ route('user.dashboard') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium">
                                    Back to Dashboard
                                </a>
                            @else
                                <a href="{{ route('admin.dashboard') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium">
                                    Back to Dashboard
                                </a>
                            @endif
                        </div>
                        <div class="flex space-x-3">
                            <button type="button" onclick="resetForm()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors font-medium">
                                Reset
                            </button>
                            <button type="submit" class="bg-[#00BDE0] text-white px-4 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                                Update Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('profileForm').reset();
}

// Location Dropdown Functionality
document.addEventListener('DOMContentLoaded', function() {
    const stateSelect = document.getElementById('state');
    const districtSelect = document.getElementById('district');
    const talukaSelect = document.getElementById('taluka');

    // Load states on page load
    loadStates();

    // State change handler
    stateSelect.addEventListener('change', function() {
        const stateId = this.value;
        if (stateId) {
            loadDistricts(stateId);
            districtSelect.disabled = false;
            talukaSelect.disabled = true;
            talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
        } else {
            districtSelect.disabled = true;
            talukaSelect.disabled = true;
            districtSelect.innerHTML = '<option value="">Select District</option>';
            talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
        }
    });

    // District change handler
    districtSelect.addEventListener('change', function() {
        const districtId = this.value;
        if (districtId) {
            loadTalukas(districtId);
            talukaSelect.disabled = false;
        } else {
            talukaSelect.disabled = true;
            talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
        }
    });

    function loadStates() {
        fetch('/api/locations/states')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    stateSelect.innerHTML = '<option value="">Select State</option>';
                    data.data.forEach(state => {
                        const option = document.createElement('option');
                        option.value = state.id;
                        option.textContent = state.name;
                        stateSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading states:', error));
    }

    function loadDistricts(stateId) {
        fetch(`/api/locations/districts?state_id=${stateId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                    data.data.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.name;
                        districtSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading districts:', error));
    }

    function loadTalukas(districtId) {
        fetch(`/api/locations/talukas?district_id=${districtId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
                    data.data.forEach(taluka => {
                        const option = document.createElement('option');
                        option.value = taluka.id;
                        option.textContent = taluka.name;
                        talukaSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading talukas:', error));
    }
});
</script>
@endsection
