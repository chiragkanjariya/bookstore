<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the user's cart.
     */
    public function index()
    {
        $cartItems = Auth::user()->cartItems()
            ->with(['book.category'])
            ->orderBy('created_at', 'desc')
            ->get();

        $subtotal = $cartItems->sum('total_price');
        $shipping = $cartItems->sum(function ($item) {
            return $item->book->shipping_price * $item->quantity;
        });
        $total = $subtotal + $shipping;

        return view('cart.index', compact('cartItems', 'subtotal', 'shipping', 'total'));
    }

    /**
     * Add a book to the cart.
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'integer|min:1|max:10'
        ]);

        $book = Book::findOrFail($request->book_id);
        $quantity = $request->get('quantity', 1);

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
        if ($book->stock < $quantity) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock available. Only ' . $book->stock . ' copies left.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Not enough stock available. Only ' . $book->stock . ' copies left.');
        }

        $user = Auth::user();
        
        // Check if item already exists in cart
        $existingItem = $user->cartItems()->where('book_id', $book->id)->first();
        
        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            
            // Check if new quantity exceeds stock
            if ($newQuantity > $book->stock) {
                $maxAdditional = $book->stock - $existingItem->quantity;
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "You already have {$existingItem->quantity} copies in your cart. You can only add {$maxAdditional} more."
                    ], 400);
                }
                return redirect()->back()->with('error', "You already have {$existingItem->quantity} copies in your cart. You can only add {$maxAdditional} more.");
            }
            
            // Check max quantity limit (10 per item)
            if ($newQuantity > 10) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Maximum 10 copies allowed per item.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Maximum 10 copies allowed per item.');
            }
            
            $existingItem->update([
                'quantity' => $newQuantity,
                'price' => $book->price // Update price to current price
            ]);
            
            $message = 'Cart updated successfully!';
        } else {
            CartItem::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'quantity' => $quantity,
                'price' => $book->price
            ]);
            
            $message = 'Book added to cart successfully!';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => $user->fresh()->cart_count
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, CartItem $cartItem)
    {
        // Ensure user can only update their own cart items
        if ($cartItem->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $quantity = $request->quantity;
        
        // Check stock availability
        if ($cartItem->book->stock < $quantity) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock available. Only ' . $cartItem->book->stock . ' copies left.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Not enough stock available. Only ' . $cartItem->book->stock . ' copies left.');
        }

        $cartItem->update([
            'quantity' => $quantity,
            'price' => $cartItem->book->price // Update to current price
        ]);

        if ($request->expectsJson()) {
            $user = Auth::user();
            $cartItems = $user->cartItems()->with(['book.category'])->get();
            $subtotal = $cartItems->sum('total_price');
            $shipping = $cartItems->sum(function ($item) {
                return $item->book->shipping_price * $item->quantity;
            });
            $total = $subtotal + $shipping;

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully!',
                'cart_count' => $user->cart_count,
                'item_total' => $cartItem->total_price,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total
            ]);
        }

        return redirect()->back()->with('success', 'Cart updated successfully!');
    }

    /**
     * Remove item from cart.
     */
    public function destroy(CartItem $cartItem)
    {
        // Ensure user can only delete their own cart items
        if ($cartItem->user_id !== Auth::id()) {
            abort(403);
        }

        $cartItem->delete();

        if (request()->expectsJson()) {
            $user = Auth::user();
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully!',
                'cart_count' => $user->cart_count
            ]);
        }

        return redirect()->back()->with('success', 'Item removed from cart successfully!');
    }

    /**
     * Clear entire cart.
     */
    public function clear()
    {
        Auth::user()->cartItems()->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully!',
                'cart_count' => 0
            ]);
        }

        return redirect()->back()->with('success', 'Cart cleared successfully!');
    }

    /**
     * Get cart count for header display.
     */
    public function count()
    {
        return response()->json([
            'count' => Auth::user()->cart_count
        ]);
    }
}