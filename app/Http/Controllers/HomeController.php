<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Category;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('category')->where('status', 'active');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('author', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Language filter
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        switch($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'author':
                $query->orderBy('author', 'asc');
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        // Get books with pagination
        $books = $query->paginate(12)->withQueryString();
        
        // Get featured books (latest 8 books for hero section)
        $featuredBooks = Book::with('category')
            ->where('status', 'active')
            ->latest()
            ->limit(8)
            ->get();

        // Get all categories for filter
        $categories = Category::where('is_active', true)
            ->withCount('books')
            ->orderBy('name')
            ->get();

        // Get available languages
        $languages = Book::where('status', 'active')
            ->distinct()
            ->pluck('language')
            ->sort()
            ->values();

        // Get price range
        $priceRange = Book::where('status', 'active')
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        // Stats
        $stats = [
            'books_count' => Book::where('status', 'active')->count(),
            'categories_count' => Category::where('is_active', true)->count(),
            'average_rating' => '4.8★' // You can calculate this based on reviews when implemented
        ];

        return view('home', compact(
            'books', 
            'featuredBooks', 
            'categories', 
            'languages', 
            'priceRange', 
            'stats',
            'request'
        ));
    }

    public function show(Book $book)
    {
        // Load the book with its category
        $book->load('category');
        
        // Get related books from the same category
        $relatedBooks = Book::with('category')
            ->where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->where('status', 'active')
            ->limit(4)
            ->get();

        return view('book-detail', compact('book', 'relatedBooks'));
    }
}