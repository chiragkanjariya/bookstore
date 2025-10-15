<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookImage;
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
            'isbn' => ['nullable', 'string', 'max:20', 'unique:books,isbn'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'shipping_price' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'depth' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'in:in_stock,limited_stock,out_of_stock'],
            'language' => ['required', 'string', 'max:100'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5000'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5000'],
            'status' => ['required', Rule::in(['active', 'inactive', 'out_of_stock'])],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $data = $request->only([
            'title', 'author', 'isbn', 'description', 'price', 'shipping_price',
            'height', 'width', 'depth', 'weight',
            'stock', 'language', 'status', 'category_id'
        ]);

        // Set default shipping price
        $data['shipping_price'] = $request->input('shipping_price', 0);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('books', 'public');
        }

        $book = Book::create($data);

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('books', 'public');
                
                $book->images()->create([
                    'image_path' => $imagePath,
                    'sort_order' => $index + 1,
                    'is_primary' => $index === 0, // First image is primary
                ]);
            }
        }

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
        $book->load('images');
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
            'isbn' => ['nullable', 'string', 'max:20', 'unique:books,isbn,' . $book->id],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'shipping_price' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'depth' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'in:in_stock,limited_stock,out_of_stock'],
            'language' => ['required', 'string', 'max:100'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5000'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5000'],
            'status' => ['required', Rule::in(['active', 'inactive', 'out_of_stock'])],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $data = $request->only([
            'title', 'author', 'isbn', 'description', 'price', 'shipping_price',
            'height', 'width', 'depth', 'weight',
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

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            $maxSortOrder = $book->images()->max('sort_order') ?? 0;
            
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('books', 'public');
                $maxSortOrder++;
                
                $book->images()->create([
                    'image_path' => $imagePath,
                    'sort_order' => $maxSortOrder,
                    'is_primary' => $book->images()->count() === 0, // First image is primary
                ]);
            }
        }

        return redirect()->route('admin.books.edit', $book)
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

        // Delete all book images
        foreach ($book->images as $image) {
            if ($image->image_path) {
                Storage::disk('public')->delete($image->image_path);
            }
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
            'stock' => ['required', 'in:in_stock,limited_stock,out_of_stock'],
        ]);

        $book->update(['stock' => $request->stock]);

        // Auto-update status based on stock
        if ($book->stock === 'out_of_stock' && $book->status === 'active') {
            $book->update(['status' => 'out_of_stock']);
        } elseif (in_array($book->stock, ['in_stock', 'limited_stock']) && $book->status === 'out_of_stock') {
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

    /**
     * Upload additional images for a book.
     */
    public function uploadImages(Request $request, Book $book)
    {
        $request->validate([
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5000'],
        ]);

        if ($request->hasFile('images')) {
            $maxSortOrder = $book->images()->max('sort_order') ?? 0;
            $uploadedImages = [];
            
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('books', 'public');
                $maxSortOrder++;
                
                $bookImage = $book->images()->create([
                    'image_path' => $imagePath,
                    'sort_order' => $maxSortOrder,
                    'is_primary' => $book->images()->count() === 1, // First image is primary
                ]);

                $uploadedImages[] = [
                    'id' => $bookImage->id,
                    'url' => $bookImage->image_url,
                    'is_primary' => $bookImage->is_primary,
                ];
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Images uploaded successfully!',
                    'images' => $uploadedImages
                ]);
            }
        }

        return redirect()->route('admin.books.edit', $book)
            ->with('success', 'Images uploaded successfully!');
    }

    /**
     * Delete a book image.
     */
    public function deleteImage(Request $request, Book $book, BookImage $image)
    {
        if ($image->book_id !== $book->id) {
            abort(404);
        }

        // Delete the file
        if ($image->image_path) {
            Storage::disk('public')->delete($image->image_path);
        }

        $wasPrimary = $image->is_primary;
        $image->delete();

        // If this was the primary image, make the first remaining image primary
        if ($wasPrimary) {
            $firstImage = $book->images()->first();
            if ($firstImage) {
                $firstImage->update(['is_primary' => true]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully!'
            ]);
        }

        return redirect()->route('admin.books.edit', $book)
            ->with('success', 'Image deleted successfully!');
    }

    /**
     * Set primary image for a book.
     */
    public function setPrimaryImage(Request $request, Book $book, BookImage $image)
    {
        if ($image->book_id !== $book->id) {
            abort(404);
        }

        // Remove primary status from all images
        $book->images()->update(['is_primary' => false]);
        
        // Set this image as primary
        $image->update(['is_primary' => true]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Primary image updated successfully!'
            ]);
        }

        return redirect()->route('admin.books.edit', $book)
            ->with('success', 'Primary image updated successfully!');
    }

    /**
     * Update image sort order.
     */
    public function updateImageOrder(Request $request, Book $book)
    {
        $request->validate([
            'image_ids' => ['required', 'array'],
            'image_ids.*' => ['exists:book_images,id'],
        ]);

        foreach ($request->image_ids as $index => $imageId) {
            BookImage::where('id', $imageId)
                ->where('book_id', $book->id)
                ->update(['sort_order' => $index + 1]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Image order updated successfully!'
            ]);
        }

        return redirect()->route('admin.books.edit', $book)
            ->with('success', 'Image order updated successfully!');
    }
}