@extends('layouts.app')

@section('content')
<!-- Hero Banner Section -->
<section class="relative bg-gradient-to-r from-[#00BDE0] to-[#0099CC] text-white py-20 overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Left Content -->
            <div class="text-center lg:text-left">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    Discover Your Next
                    <span class="text-yellow-300">Great Read</span>
                </h1>
                <p class="text-lg md:text-xl mb-8 text-gray-100 max-w-2xl">
                    Explore thousands of books across all genres. From bestsellers to hidden gems, 
                    find the perfect book for every moment.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="#books" class="bg-white text-[#00BDE0] px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-colors shadow-lg">
                        Browse Collection
                    </a>
                    <a href="#featured" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:text-[#00BDE0] transition-colors">
                        View Featured
                    </a>
                </div>
            </div>
            
            <!-- Right Content - Featured Books Preview -->
            <div class="flex justify-center lg:justify-end">
                <div class="grid grid-cols-2 gap-4 max-w-sm">
                    @foreach($featuredBooks->take(4) as $book)
                        <div class="bg-white rounded-lg shadow-lg p-4 transform hover:scale-105 transition-transform">
                            <img src="{{ $book->cover_image_url }}" alt="{{ $book->title }}" 
                                 class="w-full h-32 object-cover rounded mb-2">
                            <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $book->title }}</h4>
                            <p class="text-xs text-gray-600">{{ $book->author }}</p>
                            <p class="text-sm font-bold text-[#00BDE0]">₹{{ number_format($book->price, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Books Available -->
            <div class="text-center bg-gray-50 rounded-xl p-8 hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-[#00BDE0] rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ number_format($stats['books_count']) }}+</h3>
                <p class="text-gray-600">Books Available</p>
            </div>
            
            <!-- Categories -->
            <div class="text-center bg-gray-50 rounded-xl p-8 hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ $stats['categories_count'] }}+</h3>
                <p class="text-gray-600">Categories</p>
            </div>
            
            <!-- Average Rating -->
            <div class="text-center bg-gray-50 rounded-xl p-8 hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ $stats['average_rating'] }}</h3>
                <p class="text-gray-600">Average Rating</p>
            </div>
        </div>
    </div>
</section>

<!-- Search and Filter Section -->
<section id="books" class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Search Header -->
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Browse Our Collection</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Search through our vast collection of books using filters to find exactly what you're looking for.
            </p>
        </div>
        
        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <form method="GET" class="space-y-6">
                <!-- Search Bar -->
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ $request->search }}" 
                               placeholder="Search by title, author, or description..."
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00BDE0] focus:border-transparent">
                    </div>
                    <button type="submit" 
                            class="bg-[#00BDE0] text-white px-8 py-3 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Search
                    </button>
                </div>
                
                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0]">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $request->category == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} ({{ $category->books_count }})
                                </option>
                            @endforeach
                    </select>
        </div>
        
                    <!-- Language Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                        <select name="language" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0]">
                            <option value="">All Languages</option>
                            @foreach($languages as $language)
                                <option value="{{ $language }}" {{ $request->language == $language ? 'selected' : '' }}>
                                    {{ $language }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Price</label>
                        <input type="number" name="min_price" value="{{ $request->min_price }}" 
                               min="0" step="0.01" placeholder="₹0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Price</label>
                        <input type="number" name="max_price" value="{{ $request->max_price }}" 
                               min="0" step="0.01" placeholder="₹{{ $priceRange->max_price ?? 10000 }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0]">
                    </div>
                </div>
                
                <!-- Sort and Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center space-x-4">
                        <label class="text-sm font-medium text-gray-700">Sort by:</label>
                        <select name="sort_by" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00BDE0]">
                            <option value="created_at" {{ $request->sort_by == 'created_at' ? 'selected' : '' }}>Latest</option>
                            <option value="price_low" {{ $request->sort_by == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ $request->sort_by == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="title" {{ $request->sort_by == 'title' ? 'selected' : '' }}>Title A-Z</option>
                            <option value="author" {{ $request->sort_by == 'author' ? 'selected' : '' }}>Author A-Z</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-2">
                        <a href="{{ route('home') }}" 
                           class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                            Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Summary -->
        @if($books->total() > 0)
            <div class="flex justify-between items-center mb-6">
                <p class="text-gray-600">
                    Showing {{ $books->firstItem() }}-{{ $books->lastItem() }} of {{ $books->total() }} results
                    @if($request->search)
                        for "<strong>{{ $request->search }}</strong>"
                    @endif
                </p>
            </div>
        @endif

        <!-- Books Grid -->
        @if($books->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-12">
                @foreach($books as $book)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                        <a href="{{ route('book.show', $book->slug) }}" class="block">
                            <div class="aspect-w-3 aspect-h-4">
                                <img src="{{ $book->cover_image_url }}" alt="{{ $book->title }}" 
                                     class="w-full h-64 object-cover">
                            </div>
                        </a>
                        
                        <div class="p-4">
                            <div class="mb-2">
                                <span class="inline-block bg-[#00BDE0] text-white text-xs px-2 py-1 rounded-full">
                                    {{ $book->category->name }}
                                </span>
                                @if($book->language !== 'English')
                                    <span class="inline-block bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full ml-1">
                                        {{ $book->language }}
                                    </span>
                                @endif
                            </div>
                            
                            <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-2">
                                <a href="{{ route('book.show', $book->slug) }}" class="hover:text-[#00BDE0] transition-colors">
                                    {{ $book->title }}
                                </a>
                            </h3>
                            
                            <p class="text-gray-600 text-sm mb-2">by {{ $book->author }}</p>
                            
                            @if($book->description)
                                <p class="text-gray-500 text-sm mb-3 line-clamp-2">{{ $book->description }}</p>
                            @endif
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-2xl font-bold text-[#00BDE0]">₹{{ number_format($book->price, 2) }}</span>
                                    @if($book->shipping_price > 0)
                                        <span class="text-xs text-gray-500 block">+ ₹{{ number_format($book->shipping_price, 2) }} shipping</span>
                                    @else
                                        <span class="text-xs text-green-600 block">Free shipping</span>
                                    @endif
                                </div>
                                
                                <div class="text-right">
                                    @if($book->stock > 0)
                                        <span class="text-sm text-green-600 font-medium">{{ $book->stock }} in stock</span>
                                    @else
                                        <span class="text-sm text-red-600 font-medium">Out of stock</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-4 space-y-2">
                                @auth
                                    @if($book->stock > 0 && $book->status === 'active')
                                        <div class="flex space-x-2">
                                            <!-- Add to Cart -->
                                            <form method="POST" action="{{ route('cart.store') }}" class="flex-1 add-to-cart-form">
                                                @csrf
                                                <input type="hidden" name="book_id" value="{{ $book->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" 
                                                        class="w-full bg-[#00BDE0] text-white py-2 px-4 rounded-md hover:bg-[#00A5C7] transition-colors text-sm font-medium {{ Auth::user()->hasInCart($book) ? 'opacity-75' : '' }}">
                                                    @if(Auth::user()->hasInCart($book))
                                                        In Cart
                                                    @else
                                                        Add to Cart
                                                    @endif
                                                </button>
                                            </form>
                                            
                                            <!-- Add to Wishlist -->
                                            @if(!Auth::user()->hasInWishlist($book))
                                                <form method="POST" action="{{ route('wishlist.store') }}" class="add-to-wishlist-form">
                                                    @csrf
                                                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                                                    <button type="submit" 
                                                            class="bg-white border border-gray-300 text-gray-700 py-2 px-3 rounded-md hover:bg-gray-50 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="bg-red-100 border border-red-300 text-red-600 py-2 px-3 rounded-md cursor-default">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <button disabled class="w-full bg-gray-400 text-white py-2 px-4 rounded-md cursor-not-allowed text-sm font-medium">
                                            Out of Stock
                                        </button>
                                    @endif
                                @else
                                    <button onclick="openAuthModal()" 
                                            class="w-full bg-[#00BDE0] text-white py-2 px-4 rounded-md hover:bg-[#00A5C7] transition-colors text-sm font-medium">
                                        Sign in to Purchase
                                    </button>
                                @endauth
                                
                                <a href="{{ route('book.show', $book->slug) }}" 
                                   class="w-full bg-gray-200 text-gray-900 py-2 px-4 rounded-md hover:bg-gray-300 transition-colors text-center block text-sm font-medium">
                                    View Details
                                </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="flex justify-center">
                {{ $books->links() }}
            </div>
        @else
            <!-- No Results -->
            <div class="text-center py-12">
                <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.467-.881-6.08-2.33M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No books found</h3>
                <p class="mt-2 text-gray-500">Try adjusting your search criteria or browse all books.</p>
                <div class="mt-6">
                    <a href="{{ route('home') }}" 
                       class="bg-[#00BDE0] text-white px-6 py-3 rounded-lg hover:bg-[#00A5C7] transition-colors">
                        Browse All Books
                    </a>
                </div>
        </div>
        @endif
    </div>
</section>

<!-- Featured Books Section -->
@if($featuredBooks->count() > 0)
<section id="featured" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Featured Books</h2>
            <p class="text-lg text-gray-600">Discover our latest additions and popular titles</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredBooks as $book)
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                    <a href="{{ route('book.show', $book->slug) }}" class="block">
                        <div class="aspect-w-3 aspect-h-4">
                            <img src="{{ $book->cover_image_url }}" alt="{{ $book->title }}" 
                                 class="w-full h-48 object-cover">
                        </div>
                    </a>
                    
                    <div class="p-4">
                        <span class="inline-block bg-[#00BDE0] text-white text-xs px-2 py-1 rounded-full mb-2">
                            {{ $book->category->name }}
                        </span>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-2">
                            <a href="{{ route('book.show', $book->slug) }}" class="hover:text-[#00BDE0] transition-colors">
                                {{ $book->title }}
                            </a>
                        </h3>
                        
                        <p class="text-gray-600 text-sm mb-2">by {{ $book->author }}</p>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xl font-bold text-[#00BDE0]">₹{{ number_format($book->price, 2) }}</span>
                            <a href="{{ route('book.show', $book->slug) }}" 
                               class="text-[#00BDE0] hover:text-[#00A5C7] font-medium text-sm">
                                View Details →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<script>
// Auto-submit form when sort option changes
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.querySelector('select[name="sort_by"]');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            this.closest('form').submit();
        });
    }

    // Add to Cart AJAX for book cards
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            button.textContent = 'Adding...';
            button.disabled = true;
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.textContent = 'In Cart';
                    button.classList.add('opacity-75');
                    
                    // Update cart count in header
                    updateCartCount(data.cart_count);
                    
                    // Show success message
                    showMessage(data.message, 'success');
                } else {
                    button.textContent = originalText;
                    button.disabled = false;
                    showMessage(data.message || 'Error adding to cart', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.textContent = originalText;
                button.disabled = false;
                showMessage('Error adding to cart', 'error');
            });
        });
    });

    // Add to Wishlist AJAX for book cards
    document.querySelectorAll('.add-to-wishlist-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = this.querySelector('button[type="submit"]');
            const originalContent = button.innerHTML;
            button.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';
            button.disabled = true;
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Replace the form with a filled heart
                    this.outerHTML = `
                        <button class="bg-red-100 border border-red-300 text-red-600 py-2 px-3 rounded-md cursor-default">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </button>
                    `;
                    
                    // Update wishlist count in header
                    updateWishlistCount(data.wishlist_count);
                    
                    // Show success message
                    showMessage(data.message, 'success');
                } else {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                    showMessage(data.message || 'Error adding to wishlist', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = originalContent;
                button.disabled = false;
                showMessage('Error adding to wishlist', 'error');
            });
        });
    });

    function updateCartCount(count) {
        const cartBadges = document.querySelectorAll('.cart-count');
        cartBadges.forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        });
    }

    function updateWishlistCount(count) {
        const wishlistBadges = document.querySelectorAll('.wishlist-count');
        wishlistBadges.forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        });
    }

    function showMessage(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded relative ${
            type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'
        }`;
        alertDiv.textContent = message;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }
});
</script>
@endsection