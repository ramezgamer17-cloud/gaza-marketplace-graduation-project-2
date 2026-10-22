<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
Route::get('/store/{vendorProfile:slug}', [StoreController::class, 'show'])->name('stores.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::view('/pending', 'auth.pending')->name('pending');

    Route::get('/account', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/account', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/account/complete', [ProfileController::class, 'complete'])->name('profile.complete');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/item/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/item/{item}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/checkout/fees', [CheckoutController::class, 'fees'])->name('checkout.fees');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/orders/{order}/pay', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/orders/{order}/pay', [PaymentController::class, 'sendCode'])->name('payment.send');
    Route::get('/orders/{order}/pay/verify', [PaymentController::class, 'verifyForm'])->name('payment.verify');
    Route::post('/orders/{order}/pay/verify', [PaymentController::class, 'verify'])->name('payment.verify.submit');

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{product}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});
