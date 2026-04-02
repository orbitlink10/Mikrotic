<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/products/{product:slug}', [StorefrontController::class, 'show'])->name('product.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product:slug}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/items/{cartItem}/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/items/{cartItem}/remove', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/checkout', [CartController::class, 'checkoutForm'])->name('checkout.form');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout.submit');
    Route::get('/checkout/success/{orderNumber}', [CartController::class, 'success'])->name('checkout.success');

    Route::get('/become-vendor', [VendorController::class, 'applyForm'])->name('vendor.apply.form');
    Route::post('/become-vendor', [VendorController::class, 'apply'])->name('vendor.apply.submit');
});

Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function (): void {
    Route::get('/dashboard', [VendorController::class, 'dashboard'])->name('dashboard');
    Route::get('/products/create', [VendorController::class, 'createProductForm'])->name('products.create');
    Route::post('/products', [VendorController::class, 'storeProduct'])->name('products.store');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::get('/vendors', [AdminController::class, 'pendingVendors'])->name('vendors.pending');
    Route::post('/vendors/{vendor}/approve', [AdminController::class, 'approveVendor'])->name('vendors.approve');
});
