@extends('layouts.admin')

@section('breadcrumb', 'Category Details')

@section('content')
<div class="space-y-6">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('admin.categories.index') }}" 
                       class="text-gray-600 hover:text-gray-900 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
                        <p class="mt-2 text-gray-600">Category details and books</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.categories.edit', $category) }}" 
                       class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                        Edit Category
                    </a>
                    <a href="{{ route('admin.books.create', ['category_id' => $category->id]) }}" 
                       class="bg-[#00BDE0] text-white px-4 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                        Add Book
                    </a>
                </div>
            </div>
        </div>

        <!-- Category Info -->
        <div class="bg-white shadow-sm rounded-lg mb-8">
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Category Image -->
                    <div>
                        @if($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}" 
                                 alt="{{ $category->name }}" 
                                 class="w-full h-48 object-cover rounded-lg">
                        @else
                            <div class="w-full h-48 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Category Details -->
                    <div class="lg:col-span-2">
                        <div class="space-y-4">
                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-1">
                                    {!! $category->status_badge !!}
                                </div>
                            </div>

                            <!-- Description -->
                            @if($category->description)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <p class="mt-1 text-gray-900">{{ $category->description }}</p>
                                </div>
                            @endif

                            <!-- Stats -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Total Books</label>
                                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $category->books_count }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Active Books</label>
                                    <p class="mt-1 text-2xl font-semibold text-green-600">{{ $category->active_books_count }}</p>
                                </div>
                            </div>

                            <!-- Meta Info -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                                    <p class="mt-1 text-gray-900">{{ $category->sort_order }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Created</label>
                                    <p class="mt-1 text-gray-900">{{ $category->created_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                                    <p class="mt-1 text-gray-900">{{ $category->updated_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Books in Category -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Books in this Category</h3>
                    <a href="{{ route('admin.books.index', ['category_id' => $category->id]) }}" 
                       class="text-[#00BDE0] hover:text-[#00A5C7] font-medium">
                        View All Books
                    </a>
                </div>
            </div>

            @if($category->books->count() > 0)
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($category->books as $book)
                            <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <!-- Book Cover -->
                                <div class="aspect-[3/4] bg-gray-200 rounded-lg mb-3 overflow-hidden">
                                    <img src="{{ $book->cover_image_url }}" 
                                         alt="{{ $book->title }}" 
                                         class="w-full h-full object-cover">
                                </div>

                                <!-- Book Info -->
                                <div>
                                    <h4 class="font-medium text-gray-900 truncate">{{ $book->title }}</h4>
                                    <p class="text-sm text-gray-600 truncate">by {{ $book->author }}</p>
                                    <div class="mt-2 flex items-center justify-between">
                                        <span class="text-lg font-semibold text-[#00BDE0]">{{ $book->formatted_price }}</span>
                                        {!! $book->status_badge !!}
                                    </div>
                                    <div class="mt-2 text-sm text-gray-500">
                                        Stock: <span class="{{ $book->stock_status_color }}">{{ $book->stock }}</span>
                                    </div>
                                    <div class="mt-3 flex space-x-2">
                                        <a href="{{ route('admin.books.show', $book) }}" 
                                           class="flex-1 text-center bg-[#00BDE0] text-white px-3 py-1 rounded text-sm hover:bg-[#00A5C7] transition-colors">
                                            View
                                        </a>
                                        <a href="{{ route('admin.books.edit', $book) }}" 
                                           class="flex-1 text-center bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700 transition-colors">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No books in this category</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a new book to this category.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.books.create', ['category_id' => $category->id]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#00BDE0] hover:bg-[#00A5C7] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00BDE0]">
                            Add First Book
                        </a>
                    </div>
                </div>
            @endif
        </div>
@endsection
