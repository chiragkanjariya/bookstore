@extends('layouts.admin')

@section('title', 'Books')
@section('breadcrumb', 'Books')

@section('content')
<div class="space-y-6">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Book Management</h1>
                    <p class="mt-2 text-gray-600">Manage your book inventory</p>
                </div>
                <a href="{{ route('admin.books.create') }}" 
                   class="bg-[#00BDE0] text-white px-4 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                    Add New Book
                </a>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <!-- Search -->
                <div class="flex-1 min-w-64">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Books</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Search by title or author..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" id="category_id" 
                            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" 
                            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0]">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="bg-[#00BDE0] text-white px-4 py-2 rounded-md hover:bg-[#00A5C7] transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('admin.books.index') }}" 
                       class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Books Grid -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            @if($books->count() > 0)
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($books as $book)
                            <div class="bg-gray-50 rounded-lg p-4 hover:shadow-lg transition-shadow">
                                <!-- Book Cover -->
                                <div class="aspect-[3/4] bg-gray-200 rounded-lg mb-4 overflow-hidden">
                                    <img src="{{ $book->cover_image_url }}" 
                                         alt="{{ $book->title }}" 
                                         class="w-full h-full object-cover">
                                </div>

                                <!-- Book Info -->
                                <div class="space-y-2">
                                    <h3 class="font-semibold text-gray-900 truncate" title="{{ $book->title }}">
                                        {{ $book->title }}
                                    </h3>
                                    <p class="text-sm text-gray-600 truncate" title="{{ $book->author }}">
                                        by {{ $book->author }}
                                    </p>
                                    <p class="text-xs text-[#00BDE0] font-medium">
                                        {{ $book->category->name }}
                                    </p>
                                    
                                    <!-- Price and Status -->
                                    <div class="flex items-center justify-between">
                                        <span class="text-lg font-semibold text-gray-900">{{ $book->formatted_price }}</span>
                                        {!! $book->status_badge !!}
                                    </div>
                                    
                                    <!-- Stock Info -->
                                    <div class="text-sm">
                                        <span class="text-gray-500">Stock:</span>
                                        <span class="{{ $book->stock_status_color }} font-medium">
                                            {{ $book->stock }} ({{ $book->stock_status }})
                                        </span>
                                    </div>
                                    
                                    <!-- Language and Shipping -->
                                    <div class="text-xs text-gray-500">
                                        <div>Language: {{ $book->language }}</div>
                                        <div>Shipping: {{ $book->formatted_shipping_price }}</div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex space-x-2 pt-2">
                                        <a href="{{ route('admin.books.show', $book) }}" 
                                           class="flex-1 text-center bg-[#00BDE0] text-white px-3 py-2 rounded text-sm hover:bg-[#00A5C7] transition-colors">
                                            View
                                        </a>
                                        <a href="{{ route('admin.books.edit', $book) }}" 
                                           class="flex-1 text-center bg-indigo-600 text-white px-3 py-2 rounded text-sm hover:bg-indigo-700 transition-colors">
                                            Edit
                                        </a>
                                    </div>

                                    <!-- Quick Actions -->
                                    <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                        <!-- Status Toggle -->
                                        <form method="POST" action="{{ route('admin.books.update-status', $book) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" onchange="this.form.submit()" 
                                                    class="text-xs border-0 bg-transparent focus:ring-0 {{ $book->status === 'active' ? 'text-green-600' : ($book->status === 'inactive' ? 'text-red-600' : 'text-yellow-600') }}">
                                                <option value="active" {{ $book->status === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ $book->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                <option value="out_of_stock" {{ $book->status === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                            </select>
                                        </form>

                                        <!-- Delete Button -->
                                        <form method="POST" action="{{ route('admin.books.destroy', $book) }}" 
                                              class="inline" onsubmit="return confirm('Are you sure you want to delete this book?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $books->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No books found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a new book to your inventory.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.books.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#00BDE0] hover:bg-[#00A5C7] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00BDE0]">
                            Add New Book
                        </a>
                    </div>
                </div>
            @endif
        </div>
@endsection
