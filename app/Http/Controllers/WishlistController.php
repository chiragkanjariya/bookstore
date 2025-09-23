<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the user's wishlist.
     */
    public function index()
    {
        $wishlistItems = Auth::user()->wishlists()
            ->with(['book.category'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('wishlist.index', compact('wishlistItems'));
    }

    /**
     * Add a book to the wishlist.
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $book = Book::findOrFail($request->book_id);
        $user = Auth::user();

        // Check if item already exists in wishlist
        $existingItem = $user->wishlists()->where('book_id', $book->id)->first();
        
        if ($existingItem) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This book is already in your wishlist.'
                ], 400);
            }
            return redirect()->back()->with('error', 'This book is already in your wishlist.');
        }

        Wishlist::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $message = 'Book added to wishlist successfully!';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'wishlist_count' => $user->fresh()->wishlist_count
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove item from wishlist.
     */
    public function destroy(Wishlist $wishlist)
    {
        // Ensure user can only delete their own wishlist items
        if ($wishlist->user_id !== Auth::id()) {
            abort(403);
        }

        $wishlist->delete();

        if (request()->expectsJson()) {
            $user = Auth::user();
            return response()->json([
                'success' => true,
                'message' => 'Item removed from wishlist successfully!',
                'wishlist_count' => $user->wishlist_count
            ]);
        }

        return redirect()->back()->with('success', 'Item removed from wishlist successfully!');
    }

    /**
     * Move item from wishlist to cart.
     */
    public function moveToCart(Request $request, Wishlist $wishlist)
    {
        // Ensure user can only move their own wishlist items
        if ($wishlist->user_id !== Auth::id()) {
            abort(403);
        }

        $book = $wishlist->book;
        $user = Auth::user();

        // Check if book is available
        if ($book->status !== 'active') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This book is not available for purchase.'
                ], 400);
            }
            return redirect()->back()->with('error', 'This book is not available for purchase.');
        }

        // Check stock availability
        if ($book->stock < 1) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This book is currently out of stock.'
                ], 400);
            }
            return redirect()->back()->with('error', 'This book is currently out of stock.');
        }

        // Check if item already exists in cart
        $existingCartItem = $user->cartItems()->where('book_id', $book->id)->first();
        
        if ($existingCartItem) {
            $newQuantity = $existingCartItem->quantity + 1;
            
            // Check if new quantity exceeds stock
            if ($newQuantity > $book->stock) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "You already have {$existingCartItem->quantity} copies in your cart and there's no more stock available."
                    ], 400);
                }
                return redirect()->back()->with('error', "You already have {$existingCartItem->quantity} copies in your cart and there's no more stock available.");
            }
            
            // Check max quantity limit (10 per item)
            if ($newQuantity > 10) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Maximum 10 copies allowed per item in cart.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Maximum 10 copies allowed per item in cart.');
            }
            
            $existingCartItem->update([
                'quantity' => $newQuantity,
                'price' => $book->price
            ]);
        } else {
            // Add to cart
            $user->cartItems()->create([
                'book_id' => $book->id,
                'quantity' => 1,
                'price' => $book->price
            ]);
        }

        // Remove from wishlist
        $wishlist->delete();

        $message = 'Book moved to cart successfully!';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => $user->fresh()->cart_count,
                'wishlist_count' => $user->fresh()->wishlist_count
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Clear entire wishlist.
     */
    public function clear()
    {
        Auth::user()->wishlists()->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Wishlist cleared successfully!',
                'wishlist_count' => 0
            ]);
        }

        return redirect()->back()->with('success', 'Wishlist cleared successfully!');
    }

    /**
     * Get wishlist count for header display.
     */
    public function count()
    {
        return response()->json([
            'count' => Auth::user()->wishlist_count
        ]);
    }
}