@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#00BDE0]">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('home', ['category' => $book->category_id]) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#00BDE0] md:ml-2">
                        {{ $book->category->name }}
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 truncate">{{ $book->title }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Book Details -->
    <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 xl:gap-x-16 mb-16">
        <!-- Book Image -->
        <div class="lg:max-w-lg lg:self-start">
            <div class="aspect-w-1 aspect-h-1 rounded-lg overflow-hidden">
                <img src="{{ $book->cover_image_url }}" alt="{{ $book->title }}" 
                     class="w-full h-full object-cover">
            </div>
        </div>

        <!-- Book Info -->
        <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $book->title }}</h1>
            
            <div class="mt-3">
                <h2 class="sr-only">Book information</h2>
                <p class="text-xl text-gray-600">by {{ $book->author }}</p>
            </div>

            <!-- Category and Language -->
            <div class="mt-4 flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-[#00BDE0] text-white">
                    {{ $book->category->name }}
                </span>
                @if($book->language !== 'English')
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-gray-200 text-gray-800">
                        {{ $book->language }}
                    </span>
                @endif
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium {{ $book->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ ucfirst($book->status) }}
                </span>
            </div>

            <!-- Price -->
            <div class="mt-6">
                <div class="flex items-center">
                    <p class="text-4xl font-bold text-[#00BDE0]">₹{{ number_format($book->price, 2) }}</p>
                </div>
                @if($book->shipping_price > 0)
                    <p class="mt-1 text-sm text-gray-500">+ ₹{{ number_format($book->shipping_price, 2) }} shipping</p>
                @else
                    <p class="mt-1 text-sm text-green-600 font-medium">Free shipping</p>
                @endif
            </div>

            <!-- Stock Status -->
            <div class="mt-6">
                @if($book->stock > 0)
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-green-600 font-medium">{{ $book->stock }} copies in stock</span>
                    </div>
                @else
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-red-600 font-medium">Out of stock</span>
                    </div>
                @endif
            </div>

            <!-- Description -->
            @if($book->description)
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900">Description</h3>
                <div class="mt-4 prose prose-sm text-gray-600">
                    <p>{{ $book->description }}</p>
                </div>
            </div>
            @endif

            <!-- Book Details -->
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900">Book Details</h3>
                <div class="mt-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Author:</span>
                        <span class="text-sm text-gray-900">{{ $book->author }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Category:</span>
                        <span class="text-sm text-gray-900">{{ $book->category->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Language:</span>
                        <span class="text-sm text-gray-900">{{ $book->language }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">ISBN:</span>
                        <span class="text-sm text-gray-900 font-mono">{{ $book->slug }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Added:</span>
                        <span class="text-sm text-gray-900">{{ $book->created_at->format('F j, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Purchase Actions -->
            <div class="mt-10 flex flex-col sm:flex-row gap-4">
                @auth
                    @if($book->stock > 0)
                        <!-- Add to Cart Form -->
                        <form method="POST" action="{{ route('cart.store') }}" class="flex-1 add-to-cart-form">
                            @csrf
                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" 
                                    class="w-full bg-[#00BDE0] text-white py-3 px-8 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium text-lg {{ Auth::user()->hasInCart($book) ? 'opacity-75' : '' }}">
                                @if(Auth::user()->hasInCart($book))
                                    Already in Cart
                                @else
                                    Add to Cart
                                @endif
                            </button>
                        </form>

                        <!-- Buy Now Button -->
                        <form method="GET" action="{{ route('checkout.buy-now', $book) }}" class="flex-1">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" 
                                    class="w-full bg-orange-500 text-white py-3 px-8 rounded-lg hover:bg-orange-600 transition-colors font-medium text-lg">
                                Buy Now
                            </button>
                        </form>
                    @else
                        <button type="button" disabled
                                class="flex-1 bg-gray-400 text-white py-3 px-8 rounded-lg cursor-not-allowed font-medium text-lg">
                            Out of Stock
                        </button>
                    @endif
                    
                    <!-- Add to Wishlist Form -->
                    @if(!Auth::user()->hasInWishlist($book))
                        <form method="POST" action="{{ route('wishlist.store') }}" class="add-to-wishlist-form">
                            @csrf
                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                            <button type="submit" 
                                    class="flex-shrink-0 bg-white border-2 border-[#00BDE0] text-[#00BDE0] py-3 px-6 rounded-lg hover:bg-[#00BDE0] hover:text-white transition-colors font-medium">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('wishlist.index') }}" 
                           class="flex-shrink-0 bg-red-500 border-2 border-red-500 text-white py-3 px-6 rounded-lg hover:bg-red-600 transition-colors font-medium">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </a>
                    @endif
                @else
                    <!-- Guest Actions -->
                    <button type="button" onclick="openAuthModal()" 
                            class="flex-1 bg-[#00BDE0] text-white py-3 px-8 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium text-lg">
                        Sign in to Add to Cart
                    </button>
                    <button type="button" onclick="openAuthModal()" 
                            class="flex-1 bg-orange-500 text-white py-3 px-8 rounded-lg hover:bg-orange-600 transition-colors font-medium text-lg">
                        Sign in to Buy Now
                    </button>
                    <button type="button" onclick="openAuthModal()" 
                            class="flex-shrink-0 bg-white border-2 border-[#00BDE0] text-[#00BDE0] py-3 px-6 rounded-lg hover:bg-[#00BDE0] hover:text-white transition-colors font-medium">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                @endauth
            </div>

            <!-- Share -->
            <div class="mt-8 border-t border-gray-200 pt-8">
                <h3 class="text-sm font-medium text-gray-900">Share this book</h3>
                <div class="mt-4 flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Share on Facebook</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M20 10C20 4.477 15.523 0 10 0S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.988C16.343 19.128 20 14.991 20 10z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Share on Twitter</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Share via email</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Books -->
    @if($relatedBooks->count() > 0)
    <section class="border-t border-gray-200 pt-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">More books in {{ $book->category->name }}</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedBooks as $relatedBook)
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                    <a href="{{ route('book.show', $relatedBook->slug) }}" class="block">
                        <div class="aspect-w-3 aspect-h-4">
                            <img src="{{ $relatedBook->cover_image_url }}" alt="{{ $relatedBook->title }}" 
                                 class="w-full h-48 object-cover">
                        </div>
                    </a>
                    
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-2">
                            <a href="{{ route('book.show', $relatedBook->slug) }}" class="hover:text-[#00BDE0] transition-colors">
                                {{ $relatedBook->title }}
                            </a>
                        </h3>
                        
                        <p class="text-gray-600 text-sm mb-2">by {{ $relatedBook->author }}</p>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xl font-bold text-[#00BDE0]">₹{{ number_format($relatedBook->price, 2) }}</span>
                            <a href="{{ route('book.show', $relatedBook->slug) }}" 
                               class="text-[#00BDE0] hover:text-[#00A5C7] font-medium text-sm">
                                View Details →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to Cart AJAX
    const addToCartForm = document.querySelector('.add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
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
                    button.textContent = 'Added to Cart!';
                    button.classList.add('opacity-75');
                    
                    // Update cart count in header
                    updateCartCount(data.cart_count);
                    
                    // Show success message
                    showMessage(data.message, 'success');
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        button.textContent = 'Already in Cart';
                        button.disabled = false;
                    }, 2000);
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
    }

    // Add to Wishlist AJAX
    const addToWishlistForm = document.querySelector('.add-to-wishlist-form');
    if (addToWishlistForm) {
        addToWishlistForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = this.querySelector('button[type="submit"]');
            const originalContent = button.innerHTML;
            button.innerHTML = '<svg class="w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';
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
                    // Replace the form with a "In Wishlist" link
                    const parentDiv = this.parentElement;
                    parentDiv.innerHTML = `
                        <a href="/wishlist" 
                           class="flex-shrink-0 bg-red-500 border-2 border-red-500 text-white py-3 px-6 rounded-lg hover:bg-red-600 transition-colors font-medium">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </a>
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
    }

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
