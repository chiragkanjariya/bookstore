@extends('layouts.admin')

@section('title', 'Edit Book')
@section('breadcrumb', 'Edit Book')

@section('content')
<div class="space-y-6">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('admin.books.index') }}" 
                   class="text-gray-600 hover:text-gray-900 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Book</h1>
                    <p class="mt-2 text-gray-600">Update {{ $book->title }} details</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white shadow-sm rounded-lg">
            <form method="POST" action="{{ route('admin.books.update', $book) }}" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                Book Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title', $book->title) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('title') border-red-300 @enderror"
                                   placeholder="Enter book title">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Author -->
                        <div>
                            <label for="author" class="block text-sm font-medium text-gray-700 mb-1">
                                Author <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="author" id="author" value="{{ old('author', $book->author) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('author') border-red-300 @enderror"
                                   placeholder="Enter author name">
                            @error('author')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- ISBN -->
                        <div>
                            <label for="isbn" class="block text-sm font-medium text-gray-700 mb-1">
                                ISBN
                            </label>
                            <input type="text" name="isbn" id="isbn" value="{{ old('isbn', $book->isbn) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('isbn') border-red-300 @enderror"
                                   placeholder="Enter ISBN (e.g., 978-0123456789)"
                                   maxlength="20">
                            @error('isbn')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">International Standard Book Number (optional)</p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" id="category_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('category_id') border-red-300 @enderror">
                                <option value="">Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Language -->
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-1">
                                Language <span class="text-red-500">*</span>
                            </label>
                            <select name="language" id="language" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('language') border-red-300 @enderror">
                                <option value="English" {{ old('language', $book->language) === 'English' ? 'selected' : '' }}>English</option>
                                <option value="Hindi" {{ old('language', $book->language) === 'Hindi' ? 'selected' : '' }}>Hindi</option>
                                <option value="Spanish" {{ old('language', $book->language) === 'Spanish' ? 'selected' : '' }}>Spanish</option>
                                <option value="French" {{ old('language', $book->language) === 'French' ? 'selected' : '' }}>French</option>
                                <option value="German" {{ old('language', $book->language) === 'German' ? 'selected' : '' }}>German</option>
                                <option value="Other" {{ old('language', $book->language) === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('language')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea name="description" id="description" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('description') border-red-300 @enderror"
                                      placeholder="Enter book description">{{ old('description', $book->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Current Cover Image -->
                        @if($book->cover_image)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Cover Image</label>
                                <div class="mt-1">
                                    <img src="{{ $book->cover_image_url }}" 
                                         alt="{{ $book->title }}" 
                                         class="h-48 w-32 object-cover rounded-lg">
                                </div>
                            </div>
                        @endif

                        <!-- Cover Image Upload -->
                        <div>
                            <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $book->cover_image ? 'Replace Cover Image' : 'Cover Image' }}
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="cover_image" class="relative cursor-pointer bg-white rounded-md font-medium text-[#00BDE0] hover:text-[#00A5C7] focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-[#00BDE0]">
                                            <span>Upload a file</span>
                                            <input id="cover_image" name="cover_image" type="file" accept="image/*" class="sr-only">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 6MB</p>
                                </div>
                            </div>
                            @error('cover_image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Multiple Images Management -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-medium text-gray-900">Product Images</h4>
                                <button type="button" id="add-images-btn" 
                                        class="bg-[#00BDE0] text-white px-3 py-1 rounded text-sm hover:bg-[#00A5C7] transition-colors">
                                    Add Images
                                </button>
                            </div>

                            <!-- Current Images -->
                            <div id="current-images" class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                                @forelse($book->images as $image)
                                    <div class="relative group image-item" data-image-id="{{ $image->id }}">
                                        <img src="{{ $image->image_url }}" 
                                             alt="Book Image" 
                                             class="w-full h-32 object-cover rounded-lg">
                                        
                                        <!-- Image Controls -->
                                        <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                            <div class="flex space-x-2">
                                                @if(!$image->is_primary)
                                                    <button type="button" 
                                                            onclick="setPrimaryImage({{ $image->id }})"
                                                            class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700"
                                                            title="Set as Primary">
                                                        Primary
                                                    </button>
                                                @endif
                                                <button type="button" 
                                                        onclick="deleteImage({{ $image->id }})"
                                                        class="bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700"
                                                        title="Delete Image">
                                                    Delete
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Primary Badge -->
                                        @if($image->is_primary)
                                            <div class="absolute top-2 left-2 bg-green-600 text-white px-2 py-1 rounded text-xs">
                                                Primary
                                            </div>
                                        @endif

                                        <!-- Drag Handle -->
                                        <div class="absolute top-2 right-2 bg-gray-800 text-white p-1 rounded cursor-move drag-handle opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full text-center py-8 text-gray-500">
                                        No additional images uploaded yet.
                                    </div>
                                @endforelse
                            </div>

                            <!-- Upload New Images -->
                            <div id="image-upload-area" class="hidden">
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                                    <input type="file" id="new-images" name="images[]" multiple accept="image/*" class="hidden">
                                    <div class="text-center">
                                        <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-600">
                                            <button type="button" onclick="document.getElementById('new-images').click()" 
                                                    class="font-medium text-[#00BDE0] hover:text-[#00A5C7]">
                                                Click to upload
                                            </button>
                                            or drag and drop
                                        </p>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB each</p>
                                    </div>
                                </div>
                                
                                <!-- Selected Files Preview -->
                                <div id="selected-files" class="mt-4 hidden">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Selected Files:</h5>
                                    <div id="file-list" class="space-y-2"></div>
                                    <div class="mt-4 flex space-x-2">
                                        <button type="button" onclick="uploadNewImages()" 
                                                class="bg-[#00BDE0] text-white px-4 py-2 rounded text-sm hover:bg-[#00A5C7]">
                                            Upload Images
                                        </button>
                                        <button type="button" onclick="cancelImageUpload()" 
                                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-400">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Price -->
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                    Price (₹) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="price" id="price" value="{{ old('price', $book->price) }}" required min="0" step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('price') border-red-300 @enderror"
                                       placeholder="0.00">
                                @error('price')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Shipping Price -->
                            <div>
                                <label for="shipping_price" class="block text-sm font-medium text-gray-700 mb-1">
                                    Shipping Price (₹)
                                </label>
                                <input type="number" name="shipping_price" id="shipping_price" value="{{ old('shipping_price', $book->shipping_price) }}" min="0" step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('shipping_price') border-red-300 @enderror"
                                       placeholder="0.00">
                                @error('shipping_price')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Dimensions and Weight -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-4">Physical Properties</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Height -->
                                <div>
                                    <label for="height" class="block text-sm font-medium text-gray-700 mb-1">
                                        Height (cm)
                                    </label>
                                    <input type="number" name="height" id="height" value="{{ old('height', $book->height) }}" min="0" step="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('height') border-red-300 @enderror"
                                           placeholder="0.00">
                                    @error('height')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Width -->
                                <div>
                                    <label for="width" class="block text-sm font-medium text-gray-700 mb-1">
                                        Width (cm)
                                    </label>
                                    <input type="number" name="width" id="width" value="{{ old('width', $book->width) }}" min="0" step="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('width') border-red-300 @enderror"
                                           placeholder="0.00">
                                    @error('width')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Depth -->
                                <div>
                                    <label for="depth" class="block text-sm font-medium text-gray-700 mb-1">
                                        Depth (cm)
                                    </label>
                                    <input type="number" name="depth" id="depth" value="{{ old('depth', $book->depth) }}" min="0" step="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('depth') border-red-300 @enderror"
                                           placeholder="0.00">
                                    @error('depth')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Weight -->
                                <div>
                                    <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">
                                        Weight (kg)
                                    </label>
                                    <input type="number" name="weight" id="weight" value="{{ old('weight', $book->weight) }}" min="0" step="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('weight') border-red-300 @enderror"
                                           placeholder="0.00">
                                    @error('weight')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Stock and Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Stock -->
                            <div>
                                <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">
                                    Stock Status <span class="text-red-500">*</span>
                                </label>
                                <select name="stock" id="stock" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('stock') border-red-300 @enderror">
                                    <option value="">Select stock status</option>
                                    <option value="in_stock" {{ old('stock', $book->stock) === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                    <option value="limited_stock" {{ old('stock', $book->stock) === 'limited_stock' ? 'selected' : '' }}>Limited Stock</option>
                                    <option value="out_of_stock" {{ old('stock', $book->stock) === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                </select>
                                @error('stock')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select name="status" id="status" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('status') border-red-300 @enderror">
                                    <option value="active" {{ old('status', $book->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $book->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="out_of_stock" {{ old('status', $book->status) === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Book Statistics -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Book Statistics</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Category:</span>
                                    <span class="font-medium text-gray-900">{{ $book->category->name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Current Status:</span>
                                    <span class="font-medium text-gray-900">{{ ucfirst($book->status) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Created:</span>
                                    <span class="font-medium text-gray-900">{{ $book->created_at->format('M d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Last Updated:</span>
                                    <span class="font-medium text-gray-900">{{ $book->updated_at->format('M d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Stock Status:</span>
                                    <span class="font-medium {{ $book->stock_status_color }}">{{ $book->stock_status }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Total Price:</span>
                                    <span class="font-medium text-gray-900">{{ $book->formatted_total_price }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex items-center justify-between pt-6 border-t border-gray-200">
                    <div>
                        <a href="{{ route('admin.books.show', $book) }}" 
                           class="text-[#00BDE0] hover:text-[#00A5C7] font-medium">
                            View Book Details
                        </a>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('admin.books.index') }}" 
                           class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-[#00BDE0] text-white px-6 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
                            Update Book
                        </button>
                    </div>
                </div>
            </form>
        </div>

<script>
// Image upload preview
document.getElementById('cover_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Create preview image if doesn't exist
            let preview = document.getElementById('image-preview');
            if (!preview) {
                preview = document.createElement('img');
                preview.id = 'image-preview';
                preview.className = 'mt-4 h-48 w-32 object-cover rounded-lg mx-auto';
                e.target.closest('.space-y-1').appendChild(preview);
            }
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Auto-update status based on stock
document.getElementById('stock').addEventListener('change', function(e) {
    const stock = e.target.value;
    const statusSelect = document.getElementById('status');
    
    if (stock === 'out_of_stock' && statusSelect.value === 'active') {
        statusSelect.value = 'out_of_stock';
    } else if ((stock === 'in_stock' || stock === 'limited_stock') && statusSelect.value === 'out_of_stock') {
        statusSelect.value = 'active';
    }
});

// Multiple Images Management
document.getElementById('add-images-btn').addEventListener('click', function() {
    document.getElementById('image-upload-area').classList.remove('hidden');
    this.style.display = 'none';
});

document.getElementById('new-images').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const fileList = document.getElementById('file-list');
    const selectedFiles = document.getElementById('selected-files');
    
    if (files.length > 0) {
        fileList.innerHTML = '';
        files.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'flex items-center justify-between bg-white p-2 rounded border';
            fileItem.innerHTML = `
                <span class="text-sm text-gray-700">${file.name}</span>
                <span class="text-xs text-gray-500">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
            `;
            fileList.appendChild(fileItem);
        });
        selectedFiles.classList.remove('hidden');
    }
});

function uploadNewImages() {
    const formData = new FormData();
    const files = document.getElementById('new-images').files;
    
    for (let i = 0; i < files.length; i++) {
        formData.append('images[]', files[i]);
    }
    
    // Add CSRF token
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`{{ route('admin.books.upload-images', $book) }}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to show new images
        } else {
            alert('Error uploading images: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error uploading images');
    });
}

function cancelImageUpload() {
    document.getElementById('image-upload-area').classList.add('hidden');
    document.getElementById('add-images-btn').style.display = 'inline-block';
    document.getElementById('new-images').value = '';
    document.getElementById('selected-files').classList.add('hidden');
}

function deleteImage(imageId) {
    if (confirm('Are you sure you want to delete this image?')) {
        fetch(`{{ route('admin.books.delete-image', ['book' => $book, 'image' => '__IMAGE_ID__']) }}`.replace('__IMAGE_ID__', imageId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting image: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting image');
        });
    }
}

function setPrimaryImage(imageId) {
    fetch(`{{ route('admin.books.set-primary-image', ['book' => $book, 'image' => '__IMAGE_ID__']) }}`.replace('__IMAGE_ID__', imageId), {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error setting primary image: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error setting primary image');
    });
}

// Make images sortable (drag and drop)
let draggedElement = null;

document.addEventListener('DOMContentLoaded', function() {
    const imageItems = document.querySelectorAll('.image-item');
    
    imageItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            draggedElement = this;
            e.dataTransfer.effectAllowed = 'move';
        });
        
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        });
        
        item.addEventListener('drop', function(e) {
            e.preventDefault();
            if (draggedElement !== this) {
                const container = this.parentNode;
                const draggedIndex = Array.from(container.children).indexOf(draggedElement);
                const targetIndex = Array.from(container.children).indexOf(this);
                
                if (draggedIndex < targetIndex) {
                    container.insertBefore(draggedElement, this.nextSibling);
                } else {
                    container.insertBefore(draggedElement, this);
                }
                
                updateImageOrder();
            }
        });
        
        item.draggable = true;
    });
});

function updateImageOrder() {
    const imageItems = document.querySelectorAll('.image-item');
    const imageIds = Array.from(imageItems).map(item => item.dataset.imageId);
    
    fetch(`{{ route('admin.books.update-image-order', $book) }}`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            image_ids: imageIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Error updating image order:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endsection
