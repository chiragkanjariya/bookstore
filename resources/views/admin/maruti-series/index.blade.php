@extends('layouts.admin')

@section('content')
    <div class="bg-white rounded-lg shadow-sm p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-2xl font-semibold text-gray-800">Maruti Shipping Series</h3>
                <p class="mt-1 text-sm text-gray-600">Manage available tracking numbers, thresholds, and view usage.</p>
            </div>
        </div>


        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 mb-8">
            <!-- Add Series Form -->
            <section>
                <h4 class="text-xl font-semibold text-[#00BDE0] mb-4">Add Series</h4>
                <p class="mb-4 text-sm text-gray-600">Generate a new batch of tracking numbers.</p>

                <form action="{{ route('admin.maruti-series.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label for="start_number" class="block text-sm font-medium text-gray-700 mb-1">Start Number
                                <span class="text-red-500">*</span></label>
                            <input type="text" name="start_number" id="start_number" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label for="end_number" class="block text-sm font-medium text-gray-700 mb-1">End Number <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="end_number" id="end_number" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        </div>
                    </div>

                    <div class="text-right mt-4">
                        <button type="submit"
                            class="bg-[#00BDE0] text-white px-6 py-2 rounded-md hover:bg-[#00A5C7] transition-colors inline-flex items-center gap-2">
                            <i class="fas fa-plus"></i> Generate Batch
                        </button>
                    </div>
                </form>
            </section>

            <!-- Notification Settings Form -->
            <section>
                <h4 class="text-xl font-semibold text-[#00BDE0] mb-4">Low Series Alerts</h4>
                <p class="mb-4 text-sm text-gray-600">Configure threshold alerts.</p>

                <form action="{{ route('admin.maruti-series.settings') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="flex flex-wrap gap-4">
                        <div class="flex-[2] min-w-[200px]">
                            <label for="shree_maruti_notification_email"
                                class="block text-sm font-medium text-gray-700 mb-1">Notification Email <span
                                    class="text-red-500">*</span></label>
                            <input type="email" name="shree_maruti_notification_email" id="shree_maruti_notification_email"
                                value="{{ old('shree_maruti_notification_email', $notificationEmail) }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        </div>

                        <div class="flex-1 min-w-[120px]">
                            <label for="shree_maruti_notify_threshold"
                                class="block text-sm font-medium text-gray-700 mb-1">Threshold <span
                                    class="text-red-500">*</span></label>
                            <input type="number" min="1" name="shree_maruti_notify_threshold"
                                id="shree_maruti_notify_threshold"
                                value="{{ old('shree_maruti_notify_threshold', $notifyThreshold) }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        </div>
                    </div>

                    <div class="text-right mt-4">
                        <button type="submit"
                            class="bg-[#00BDE0] text-white px-6 py-2 rounded-md hover:bg-[#00A5C7] transition-colors inline-flex items-center gap-2">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <hr class="border-gray-200 mb-8" />

        <!-- Filter and Delete actions -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
            <form action="{{ route('admin.maruti-series.index') }}" method="GET"
                class="flex flex-col sm:flex-row items-end gap-3 w-full md:w-auto">
                <div>
                    <label for="series_id" class="block text-sm font-medium text-gray-700 mb-1">Filter by Series ID</label>
                    <select id="series_id" name="series_id"
                        class="w-full min-w-[200px] px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        <option value="">All Series</option>
                        @foreach($availableSeriesIds as $id)
                            <option value="{{ $id }}" {{ request('series_id') == $id ? 'selected' : '' }}>Series ID: {{ $id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="bg-gray-100 text-gray-700 border border-gray-300 px-4 py-2 rounded-md hover:bg-gray-200 transition-colors">
                    Filter
                </button>

                @if(request('series_id'))
                    <a href="{{ route('admin.maruti-series.index') }}"
                        class="text-sm text-[#00BDE0] hover:text-[#00A5C7] mb-2 px-2">Clear Filter</a>
                @endif
            </form>

            @if(request('series_id'))
                <form action="{{ route('admin.maruti-series.destroy') }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this series? It cannot be reverted.');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="series_id" value="{{ request('series_id') }}">
                    <button type="submit"
                        class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors flex items-center gap-2">
                        <i class="fas fa-trash"></i> Delete Series {{ request('series_id') }}
                    </button>
                </form>
            @endif
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 border-b border-gray-200 text-sm font-medium">ID</th>
                        <th class="px-4 py-3 border-b border-gray-200 text-sm font-medium">Series ID</th>
                        <th class="px-4 py-3 border-b border-gray-200 text-sm font-medium">AWB Number</th>
                        <th class="px-4 py-3 border-b border-gray-200 text-sm font-medium">Status</th>
                        <th class="px-4 py-3 border-b border-gray-200 text-sm font-medium">Order ID</th>
                        <th class="px-4 py-3 border-b border-gray-200 text-sm font-medium">Generated At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($seriesRecords as $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $record->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $record->series_id }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $record->awb_number }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($record->is_used)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Used</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-[#00BDE0] bg-opacity-10 text-[#00A5C7]">Available</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($record->order_id)
                                    <a href="{{ route('admin.orders.show', $record->order_id) }}"
                                        class="text-[#00BDE0] hover:text-[#00A5C7] hover:underline">#{{ $record->order_id }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $record->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 border-t border-gray-200">
                                No tracking numbers found. Generate a series batch to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $seriesRecords->links() }}
        </div>
    </div>
@endsection