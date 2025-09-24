<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Api\LocationController;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/book/{book:slug}', [HomeController::class, 'show'])->name('book.show');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// User Dashboard Routes
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('user.profile');
    Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('user.profile.update');
});

// Cart Routes (Authenticated Users)
Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::put('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
});

// Wishlist Routes (Authenticated Users)
Route::middleware(['auth'])->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::post('/wishlist/{wishlist}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.move-to-cart');
    Route::delete('/wishlist', [WishlistController::class, 'clear'])->name('wishlist.clear');
    Route::get('/wishlist/count', [WishlistController::class, 'count'])->name('wishlist.count');
});

// Admin Dashboard Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'adminDashboard'])->name('dashboard');
    
    // Category Management Routes
    Route::resource('categories', CategoryController::class);
    Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    
    // Book Management Routes
    Route::resource('books', BookController::class);
    Route::patch('books/{book}/status', [BookController::class, 'updateStatus'])->name('books.update-status');
    Route::patch('books/{book}/stock', [BookController::class, 'updateStock'])->name('books.update-stock');
    Route::patch('books/bulk-status', [BookController::class, 'bulkUpdateStatus'])->name('books.bulk-status');
    
    // User Management Routes
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::patch('users/bulk-status', [UserController::class, 'bulkUpdateStatus'])->name('users.bulk-status');
    
    // Order Management Routes
    Route::get('orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('orders/{order}/payment-status', [\App\Http\Controllers\Admin\OrderController::class, 'updatePaymentStatus'])->name('orders.update-payment-status');
    Route::patch('orders/bulk-status', [\App\Http\Controllers\Admin\OrderController::class, 'bulkUpdateStatus'])->name('orders.bulk-status');
    Route::get('orders/export', [\App\Http\Controllers\Admin\OrderController::class, 'export'])->name('orders.export');
    Route::post('orders/{order}/create-shiprocket', [\App\Http\Controllers\Admin\OrderController::class, 'createShiprocketOrder'])->name('orders.create-shiprocket');
    Route::get('orders/track-shipment/{shiprocketOrderId}', [\App\Http\Controllers\Admin\OrderController::class, 'trackShipment'])->name('orders.track-shipment');
    Route::post('orders/{order}/send-confirmation', [\App\Http\Controllers\Admin\OrderController::class, 'sendOrderConfirmation'])->name('orders.send-confirmation');
    
    // Email Testing Routes (for debugging)
    Route::get('test-email', [\App\Http\Controllers\Admin\TestEmailController::class, 'testEmail'])->name('test-email');
    Route::get('test-order-email', [\App\Http\Controllers\Admin\TestEmailController::class, 'testOrderEmail'])->name('test-order-email');
    
    // Account Report Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('accounts', [\App\Http\Controllers\Admin\AccountReportController::class, 'index'])->name('accounts.index');
        Route::get('accounts/export-csv', [\App\Http\Controllers\Admin\AccountReportController::class, 'exportCsv'])->name('accounts.export-csv');
        Route::post('accounts/combined-invoice', [\App\Http\Controllers\Admin\AccountReportController::class, 'generateCombinedInvoice'])->name('accounts.combined-invoice');
        Route::get('accounts/user-details', [\App\Http\Controllers\Admin\AccountReportController::class, 'getUserDetails'])->name('accounts.user-details');
    });
});

// Checkout Routes (Authenticated Users)
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/checkout/buy-now/{book}', [\App\Http\Controllers\CheckoutController::class, 'buyNow'])->name('checkout.buy-now');
    Route::post('/checkout/process', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/payment/success', [\App\Http\Controllers\CheckoutController::class, 'paymentSuccess'])->name('checkout.payment.success');
    Route::post('/checkout/payment/failed', [\App\Http\Controllers\CheckoutController::class, 'paymentFailed'])->name('checkout.payment.failed');
});

// Order Routes (Authenticated Users)
Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/cancel', [\App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/invoice', [\App\Http\Controllers\OrderController::class, 'invoice'])->name('orders.invoice');
});

// API Routes for Location Data
Route::prefix('api/locations')->group(function () {
    Route::get('/states', [LocationController::class, 'getStates'])->name('api.locations.states');
    Route::get('/districts', [LocationController::class, 'getDistricts'])->name('api.locations.districts');
    Route::get('/talukas', [LocationController::class, 'getTalukas'])->name('api.locations.talukas');
    Route::get('/talukas-by-state', [LocationController::class, 'getTalukasByState'])->name('api.locations.talukas-by-state');
    Route::get('/search', [LocationController::class, 'searchLocations'])->name('api.locations.search');
});
