<?php

declare(strict_types=1);

use App\Enums\RoleEnum;
use App\Http\Controllers\API\AddressController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CatalogController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Stateless REST API for external integrations, authenticated with Laravel
| Sanctum personal access tokens. Public auth endpoints issue/inspect tokens;
| everything else lives behind the "auth:sanctum" guard.
|
| Authorization is layered: model Policies are the source of truth (checked in
| the controllers and admin bypass via Gate::before), while admin-only writes
| are additionally gated with the existing "role:admin" middleware so a
| forbidden caller is rejected before any payload validation runs (clean 403
| instead of a misleading 422). Reads stay open to any authenticated user where
| the domain allows browsing.
|
*/

$admin = 'role:'.RoleEnum::Admin->value;

// Public: obtain a token. Throttled to blunt credential-stuffing attempts.
Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1')
    ->name('api.login');

Route::middleware('auth:sanctum')->group(function () use ($admin): void {
    // Session / identity.
    Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('me', [AuthController::class, 'me'])->name('api.me');

    // Catalogs: administrative concern end to end.
    Route::middleware($admin)->group(function (): void {
        Route::apiResource('catalogs', CatalogController::class);
        Route::apiResource('customers', CustomerController::class);
    });

    // Categories: browsable hierarchy (read), admin-only writes. Bound by id to
    // keep a uniform contract despite the model's slug route-key.
    Route::get('categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('categories/{category:id}', [CategoryController::class, 'show'])->name('api.categories.show');
    Route::middleware($admin)->group(function (): void {
        Route::post('categories', [CategoryController::class, 'store'])->name('api.categories.store');
        Route::match(['put', 'patch'], 'categories/{category:id}', [CategoryController::class, 'update'])->name('api.categories.update');
        Route::delete('categories/{category:id}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');
    });

    // Products: browsable catalog (read), admin-only writes.
    Route::get('products', [ProductController::class, 'index'])->name('api.products.index');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('api.products.show');
    Route::middleware($admin)->group(function (): void {
        Route::post('products', [ProductController::class, 'store'])->name('api.products.store');
        Route::match(['put', 'patch'], 'products/{product}', [ProductController::class, 'update'])->name('api.products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('api.products.destroy');
    });

    // Addresses: ownership based (customer manages own, admin manages all),
    // enforced by the AddressPolicy in the controller.
    Route::apiResource('addresses', AddressController::class);
});
