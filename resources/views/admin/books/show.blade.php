@extends('layouts.admin')

@section('breadcrumb', 'Book Details')

@section('content')
<div class="space-y-6">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('admin.books.index') }}" 
                       class="text-gray-600 hover:text-gray-900 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $book->title }}</h1>
                        <p class="mt-2 text-gray-600">Book details and management</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.books.edit', $book) }}" 
                       class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                        Edit Book
                    </a>
                    <form method="POST" action="{{ route('admin.books.destroy', $book) }}" 
                          class="inline" onsubmit="return confirm('Are you sure you want to delete this book?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors font-medium">
                            Delete Book
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Book Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Book Cover and Quick Actions -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <!-- Book Cover -->
                    <div class="aspect-[3/4] bg-gray-200 rounded-lg mb-6 overflow-hidden">
                        <img src="{{ $book->cover_image_url }}" 
                             alt="{{ $book->title }}" 
                             class="w-full h-full object-cover">
                    </div>

                    <!-- Quick Status Update -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quick Status Update</label>
                            <form method="POST" action="{{ route('admin.books.update-status', $book) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                                    <option value="active" {{ $book->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ $book->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="out_of_stock" {{ $book->status === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                </select>
                            </form>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Update Stock</label>
                            <form method="POST" action="{{ route('admin.books.update-stock', $book) }}" class="flex space-x-2">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="stock" value="{{ $book->stock }}" min="0" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                                <button type="submit" class="bg-[#00BDE0] text-white px-4 py-2 rounded-md hover:bg-[#00A5C7] transition-colors">
                                    Update
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Book Information -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $book->title }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Author</label>
                                <p class="mt-1 text-gray-900">{{ $book->author }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Category</label>
                                <p class="mt-1">
                                    <a href="{{ route('admin.categories.show', $book->category) }}" 
                                       class="text-[#00BDE0] hover:text-[#00A5C7] font-medium">
                                        {{ $book->category->name }}
                                    </a>
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Language</label>
                                <p class="mt-1 text-gray-900">{{ $book->language }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-1">
                                    {!! $book->status_badge !!}
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Price</label>
                                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $book->formatted_price }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Shipping Price</label>
                                <p class="mt-1 text-lg text-gray-900">{{ $book->formatted_shipping_price }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Price</label>
                                <p class="mt-1 text-lg font-semibold text-[#00BDE0]">{{ $book->formatted_total_price }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Stock</label>
                                <p class="mt-1">
                                    <span class="text-2xl font-bold {{ $book->stock_status_color }}">{{ $book->stock }}</span>
                                    <span class="text-sm text-gray-500 ml-2">({{ $book->stock_status }})</span>
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Availability</label>
                                <p class="mt-1">
                                    @if($book->is_available)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Available for Purchase
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Not Available
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($book->description)
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <div class="prose max-w-none">
                                <p class="text-gray-900 leading-relaxed">{{ $book->description }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Metadata -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">Book Metadata</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Slug</label>
                                <p class="mt-1 text-sm text-gray-600 font-mono">{{ $book->slug }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Created</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $book->created_at->format('F j, Y \a\t g:i A') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $book->updated_at->format('F j, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Messages -->
        @if(session('success'))
            <div class="mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="mt-8 flex items-center justify-between">
            <div>
                <a href="{{ route('admin.categories.show', $book->category) }}" 
                   class="text-[#00BDE0] hover:text-[#00A5C7] font-medium">
                    ← View Category: {{ $book->category->name }}
                </a>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.books.index') }}" 
                   class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium">
                    Back to Books
                </a>
                <a href="{{ route('admin.books.create', ['category_id' => $book->category_id]) }}" 
                   class="bg-[#00BDE0] text-white px-4 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                    Add Another Book
                </a>
            </div>
        </div>
@endsection
