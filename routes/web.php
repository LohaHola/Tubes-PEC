
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;

// Public Storefront
Route::get('/', [StorefrontController::class, 'index'])
    ->name('home');

Route::get('/products', [StorefrontController::class, 'products'])
    ->name('storefront.products.index');

Route::get('/products/{product:slug}', [StorefrontController::class, 'show'])
    ->name('storefront.products.show');

// Guest Routes
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.process');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.process');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/dashboard', [AuthController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('/customer/dashboard', [
        AuthController::class,
        'customerDashboard',
    ])->name('customer.dashboard');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])
        ->name('checkout.index');

    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->name('checkout.store');

    // Customer Orders
    Route::get('/orders', [OrderController::class, 'index'])
        ->name('customer.orders.index');

    Route::get('/orders/{order}', [OrderController::class, 'show'])
        ->name('customer.orders.show');

    // Shopping Cart Routes
    Route::get('/cart', [CartController::class, 'index'])
        ->name('cart.index');

    Route::post('/cart', [CartController::class, 'store'])
        ->name('cart.store');

    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])
        ->name('cart.update');

    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])
        ->name('cart.destroy');

    // Admin Routes
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('admin')
        ->group(function () {

            Route::get('/dashboard', [
                AuthController::class,
                'adminDashboard',
            ])->name('dashboard');

            Route::resource('categories', CategoryController::class)
                ->except(['show']);

            Route::resource('products', ProductController::class)
                ->except(['show']);
        });
});
