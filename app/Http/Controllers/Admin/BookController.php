<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Book::with('category');

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $books = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::active()->ordered()->get();

        return view('admin.books.index', compact('books', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::active()->ordered()->get();
        return view('admin.books.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'shipping_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'language' => ['required', 'string', 'max:100'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive', 'out_of_stock'])],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $data = $request->only([
            'title', 'author', 'description', 'price', 'shipping_price',
            'stock', 'language', 'status', 'category_id'
        ]);

        // Set default shipping price
        $data['shipping_price'] = $request->input('shipping_price', 0);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('books', 'public');
        }

        Book::create($data);

        return redirect()->route('admin.books.index')
            ->with('success', 'Book created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->load('category');
        return view('admin.books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        $categories = Category::active()->ordered()->get();
        return view('admin.books.edit', compact('book', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'shipping_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'language' => ['required', 'string', 'max:100'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive', 'out_of_stock'])],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $data = $request->only([
            'title', 'author', 'description', 'price', 'shipping_price',
            'stock', 'language', 'status', 'category_id'
        ]);

        // Set default shipping price
        $data['shipping_price'] = $request->input('shipping_price', 0);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old image if exists
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('books', 'public');
        }

        $book->update($data);

        return redirect()->route('admin.books.index')
            ->with('success', 'Book updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        // Delete cover image if exists
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();

        return redirect()->route('admin.books.index')
            ->with('success', 'Book deleted successfully!');
    }

    /**
     * Update book status.
     */
    public function updateStatus(Request $request, Book $book)
    {
        $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive', 'out_of_stock'])],
        ]);

        $book->update(['status' => $request->status]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Book status updated successfully!',
                'status' => $book->status
            ]);
        }

        return redirect()->route('admin.books.index')
            ->with('success', 'Book status updated successfully!');
    }

    /**
     * Update book stock.
     */
    public function updateStock(Request $request, Book $book)
    {
        $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $book->update(['stock' => $request->stock]);

        // Auto-update status based on stock
        if ($book->stock <= 0 && $book->status === 'active') {
            $book->update(['status' => 'out_of_stock']);
        } elseif ($book->stock > 0 && $book->status === 'out_of_stock') {
            $book->update(['status' => 'active']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Book stock updated successfully!',
                'stock' => $book->stock,
                'status' => $book->status
            ]);
        }

        return redirect()->route('admin.books.index')
            ->with('success', 'Book stock updated successfully!');
    }

    /**
     * Bulk update books status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'book_ids' => ['required', 'array'],
            'book_ids.*' => ['exists:books,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'out_of_stock'])],
        ]);

        Book::whereIn('id', $request->book_ids)
            ->update(['status' => $request->status]);

        $count = count($request->book_ids);
        
        return redirect()->route('admin.books.index')
            ->with('success', "{$count} books status updated successfully!");
    }
}