@extends('layouts.admin')

@section('title', 'Manual Courier')
@section('breadcrumb', 'Manual Courier')

@section('content')
    <div class="bg-white rounded-lg shadow-sm p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-2xl font-semibold text-gray-800">Manual Courier</h3>
                <p class="mt-1 text-sm text-gray-600">Manage courier services for manual and bulk order shipping.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-1 mb-8">
            <!-- Add/Edit Courier Form -->
            <section>
                <h4 class="text-xl font-semibold text-[#00BDE0] mb-4">
                    {{ $editCourier ? 'Edit Courier' : 'Add Courier' }}
                </h4>
                <p class="mb-4 text-sm text-gray-600">
                    {{ $editCourier ? 'Update the courier details below.' : 'Add a new courier service for manual shipping.' }}
                </p>

                <form action="{{ $editCourier ? route('admin.manual-couriers.update', $editCourier) : route('admin.manual-couriers.store') }}"
                    method="POST" class="space-y-4">
                    @csrf
                    @if($editCourier)
                        @method('PUT')
                    @endif

                    <div class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label for="courier_service" class="block text-sm font-medium text-gray-700 mb-1">Courier
                                Service <span class="text-red-500">*</span></label>
                            <input type="text" name="courier_service" id="courier_service" required
                                value="{{ old('courier_service', $editCourier->courier_service ?? '') }}"
                                placeholder="e.g., DTDC, India Post, BlueDart"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                            @error('courier_service')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" required
                                value="{{ old('name', $editCourier->name ?? '') }}"
                                placeholder="e.g., DTDC Express"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                            @error('name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label for="tracking_url" class="block text-sm font-medium text-gray-700 mb-1">Tracking
                                URL</label>
                            <input type="text" name="tracking_url" id="tracking_url"
                                value="{{ old('tracking_url', $editCourier->tracking_url ?? '') }}"
                                placeholder="e.g., https://www.dtdc.in/tracking.asp"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                            <p class="text-xs text-gray-600 mt-1">Tracking page URL of the courier service</p>
                            @error('tracking_url')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-4">
                        <button type="submit"
                            class="bg-[#00BDE0] text-white px-6 py-2 rounded-md hover:bg-[#00A5C7] transition-colors inline-flex items-center gap-2">
                            <i class="fas fa-{{ $editCourier ? 'save' : 'plus' }}"></i>
                            {{ $editCourier ? 'Update Courier' : 'Add Courier' }}
                        </button>

                        @if($editCourier)
                            <a href="{{ route('admin.manual-couriers.index') }}"
                                class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                                Cancel
                            </a>
                        @endif
                    </div>
                </form>
            </section>
        </div>

        <hr class="border-gray-200 mb-8" />

        <!-- Data Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-700 uppercase">
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold tracking-wider">ID
                        </th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold tracking-wider">
                            Courier Service</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold tracking-wider">Name
                        </th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold tracking-wider">
                            Tracking URL</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold tracking-wider">
                            Created</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($couriers as $courier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $courier->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                {{ $courier->courier_service }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $courier->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                @if($courier->tracking_url)
                                    <a href="{{ $courier->tracking_url }}" target="_blank"
                                        class="text-[#00BDE0] hover:underline">{{ $courier->tracking_url }}</a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($courier->is_active)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1 text-xs"></i> Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1 text-xs"></i> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $courier->created_at->format('M d, Y') }}
                                <div class="text-xs text-gray-400">{{ $courier->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('admin.manual-couriers.index', ['edit' => $courier->id]) }}"
                                        class="text-[#00BDE0] hover:text-[#00A5C7]" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.manual-couriers.toggle-status', $courier) }}"
                                        method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="{{ $courier->is_active ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800' }}"
                                            title="{{ $courier->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $courier->is_active ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.manual-couriers.destroy', $courier) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this courier?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 border-t border-gray-200">
                                No courier services found. Add your first courier service above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $couriers->links() }}
        </div>
    </div>
@endsection
