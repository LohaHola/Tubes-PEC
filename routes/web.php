<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\IndonesiaRegionController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\HomepageFeatureController;
use Illuminate\Support\Facades\Route;

// Public Storefront
Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/products', [StorefrontController::class, 'products'])->name('storefront.products.index');
Route::get('/products/{product:slug}', [StorefrontController::class, 'show'])->name('storefront.products.show');

// Midtrans Webhook
Route::post('/midtrans/notification', [PaymentController::class, 'notification'])
    ->name('midtrans.notification');

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.process');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.process');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/customer/dashboard', [AuthController::class, 'customerDashboard'])->name('customer.dashboard');

    // Authoritative Indonesian region options for profile and customer management
    Route::get('/regions/provinces', [IndonesiaRegionController::class, 'provinces'])->name('regions.provinces');
    Route::get('/regions/provinces/{provinceId}/cities', [IndonesiaRegionController::class, 'cities'])->name('regions.cities');

    // Customer Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('customer.profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('customer.profile.update');

    // Product Reviews (customer-only authorization is enforced in controller)
    Route::post('/products/{product:slug}/reviews', [ProductReviewController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('storefront.products.reviews.store');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Customer Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('customer.orders.show');

    // Midtrans Payment
    Route::post('/orders/{order}/pay', [PaymentController::class, 'pay'])->name('customer.orders.pay');
    Route::get('/payments/finish', [PaymentController::class, 'finish'])->name('payment.finish');

    // Shopping Cart Routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AuthController::class, 'adminDashboard'])->name('dashboard');
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{user}', [CustomerController::class, 'show'])->name('customers.show');
        Route::patch('/customers/{user}', [CustomerController::class, 'update'])->name('customers.update');
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('homepage-features', HomepageFeatureController::class)->except(['show'])->parameters(['homepage-features' => 'homepage_feature']);
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
    });
});
