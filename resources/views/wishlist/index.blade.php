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
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Wishlist</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">My Wishlist</h1>
            <p class="mt-2 text-gray-600">Save your favorite books for later</p>
        </div>
        @if($wishlistItems->count() > 0)
            <form method="POST" action="{{ route('wishlist.clear') }}" class="inline" onsubmit="return confirm('Are you sure you want to clear your entire wishlist?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Clear Wishlist
                </button>
            </form>
        @endif
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    @endif

    @if($wishlistItems->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($wishlistItems as $wishlistItem)
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden" data-wishlist-id="{{ $wishlistItem->id }}">
                    <!-- Book Image -->
                    <div class="relative">
                        <a href="{{ route('book.show', $wishlistItem->book->slug) }}" class="block">
                            <img src="{{ $wishlistItem->book->cover_image_url }}" alt="{{ $wishlistItem->book->title }}" 
                                 class="w-full h-64 object-cover">
                        </a>
                        
                        <!-- Remove from Wishlist Button -->
                        <form method="POST" action="{{ route('wishlist.destroy', $wishlistItem) }}" class="absolute top-2 right-2 remove-wishlist-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-white bg-opacity-90 hover:bg-opacity-100 p-2 rounded-full shadow-md transition-all">
                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Book Details -->
                    <div class="p-4">
                        <div class="mb-2">
                            <span class="inline-block bg-[#00BDE0] text-white text-xs px-2 py-1 rounded-full">
                                {{ $wishlistItem->book->category->name }}
                            </span>
                            @if($wishlistItem->book->language !== 'English')
                                <span class="inline-block bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full ml-1">
                                    {{ $wishlistItem->book->language }}
                                </span>
                            @endif
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-2">
                            <a href="{{ route('book.show', $wishlistItem->book->slug) }}" class="hover:text-[#00BDE0] transition-colors">
                                {{ $wishlistItem->book->title }}
                            </a>
                        </h3>
                        
                        <p class="text-gray-600 text-sm mb-3">by {{ $wishlistItem->book->author }}</p>
                        
                        <!-- Price and Stock Status -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-bold text-[#00BDE0]">₹{{ number_format($wishlistItem->book->price, 2) }}</span>
                                <div class="text-right text-sm">
                                    @if($wishlistItem->book->status === 'active' && $wishlistItem->book->stock > 0)
                                        <span class="text-green-600 font-medium">In Stock ({{ $wishlistItem->book->stock }})</span>
                                    @elseif($wishlistItem->book->stock == 0)
                                        <span class="text-red-600 font-medium">Out of Stock</span>
                                    @else
                                        <span class="text-gray-500 font-medium">Unavailable</span>
                                    @endif
                                </div>
                            </div>
                            @if($wishlistItem->book->shipping_price > 0)
                                <p class="text-xs text-gray-500 mt-1">+ ₹{{ number_format($wishlistItem->book->shipping_price, 2) }} shipping</p>
                            @else
                                <p class="text-xs text-green-600 mt-1">Free shipping</p>
                            @endif
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-2">
                            @if($wishlistItem->book->status === 'active' && $wishlistItem->book->stock > 0)
                                <!-- Move to Cart Button -->
                                <form method="POST" action="{{ route('wishlist.move-to-cart', $wishlistItem) }}" class="move-to-cart-form">
                                    @csrf
                                    <button type="submit" class="w-full bg-[#00BDE0] text-white py-2 px-4 rounded-md hover:bg-[#00A5C7] transition-colors font-medium">
                                        Move to Cart
                                    </button>
                                </form>
                            @else
                                <button type="button" disabled class="w-full bg-gray-400 text-white py-2 px-4 rounded-md cursor-not-allowed font-medium">
                                    Unavailable
                                </button>
                            @endif
                            
                            <a href="{{ route('book.show', $wishlistItem->book->slug) }}" 
                               class="w-full bg-gray-200 text-gray-900 py-2 px-4 rounded-md hover:bg-gray-300 transition-colors font-medium text-center block">
                                View Details
                            </a>
                        </div>
                        
                        <!-- Added Date -->
                        <p class="text-xs text-gray-500 mt-3">
                            Added {{ $wishlistItem->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty Wishlist -->
        <div class="text-center py-16">
            <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Your wishlist is empty</h3>
            <p class="mt-2 text-gray-500">Start browsing and save your favorite books to your wishlist.</p>
            <div class="mt-6">
                <a href="{{ route('home') }}" 
                   class="bg-[#00BDE0] text-white px-6 py-3 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                    Start Shopping
                </a>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove from wishlist
    document.querySelectorAll('.remove-wishlist-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!confirm('Remove this item from your wishlist?')) return;
            
            fetch(this.action, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the card from the page
                    this.closest('[data-wishlist-id]').remove();
                    
                    // Update wishlist count in header
                    updateWishlistCount(data.wishlist_count);
                    
                    // If no more items, reload page to show empty state
                    if (data.wishlist_count === 0) {
                        location.reload();
                    }
                } else {
                    alert(data.message || 'Error removing item from wishlist');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error removing item from wishlist');
            });
        });
    });

    // Move to cart
    document.querySelectorAll('.move-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            button.textContent = 'Moving...';
            button.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the card from the page
                    this.closest('[data-wishlist-id]').remove();
                    
                    // Update counts in header
                    updateCartCount(data.cart_count);
                    updateWishlistCount(data.wishlist_count);
                    
                    // Show success message
                    showMessage(data.message, 'success');
                    
                    // If no more items, reload page to show empty state
                    if (data.wishlist_count === 0) {
                        setTimeout(() => location.reload(), 1500);
                    }
                } else {
                    button.textContent = originalText;
                    button.disabled = false;
                    alert(data.message || 'Error moving item to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.textContent = originalText;
                button.disabled = false;
                alert('Error moving item to cart');
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
