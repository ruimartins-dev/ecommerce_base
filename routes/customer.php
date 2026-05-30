<?php

declare(strict_types=1);

use App\Enums\RoleEnum;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CategoryController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer (Frontoffice) Routes
|--------------------------------------------------------------------------
|
| Reserved for authenticated customers. The "role" middleware is fed from
| RoleEnum to avoid magic strings. Products and categories are bound by slug;
| orders are protected by the OrderPolicy in the controller.
|
*/

Route::middleware(['auth', 'role:'.RoleEnum::Customer->value])
    ->prefix('customer')
    ->name('customer.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Catalogue.
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

        // Category browsing.
        Route::get('categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

        // Session-based shopping cart.
        Route::get('cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('cart', [CartController::class, 'store'])->name('cart.add');
        Route::delete('cart', [CartController::class, 'clear'])->name('cart.clear');
        Route::patch('cart/{product:slug}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('cart/{product:slug}', [CartController::class, 'destroy'])->name('cart.remove');

        // Checkout (cart review → address selection → order creation).
        Route::get('checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        // Order history (own orders only, enforced by OrderPolicy).
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });

