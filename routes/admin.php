<?php

declare(strict_types=1);

use App\Enums\RoleEnum;
use App\Http\Controllers\Admin\AddressController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin (Backoffice) Routes
|--------------------------------------------------------------------------
|
| Reserved for administrators. The "role" middleware is fed from RoleEnum so
| there are no magic strings. Every backoffice CRUD module lives here behind
| auth + role:admin.
|
*/

Route::middleware(['auth', 'role:'.RoleEnum::Admin->value])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Catalogs: CRUD + activation toggle + product assignment.
        Route::patch('catalogs/{catalog}/toggle', [CatalogController::class, 'toggleActive'])->name('catalogs.toggle');
        Route::get('catalogs/{catalog}/products', [CatalogController::class, 'editProducts'])->name('catalogs.products.edit');
        Route::put('catalogs/{catalog}/products', [CatalogController::class, 'updateProducts'])->name('catalogs.products.update');
        Route::resource('catalogs', CatalogController::class)->except('show');

        // Categories: hierarchical CRUD.
        Route::resource('categories', CategoryController::class)->except('show');

        // Products: CRUD + activation toggle.
        Route::patch('products/{product}/toggle', [ProductController::class, 'toggleActive'])->name('products.toggle');
        Route::resource('products', ProductController::class)->except('show');

        // Customers: create/edit + block toggle (no hard delete to preserve integrity).
        Route::patch('customers/{customer}/toggle-block', [CustomerController::class, 'toggleBlock'])->name('customers.toggle-block');
        Route::resource('customers', CustomerController::class)->except(['show', 'destroy']);

        // Addresses: CRUD scoped to customers.
        Route::resource('addresses', AddressController::class)->except('show');

        // Orders: management only (list, detail, status change).
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');

        // Audit logs: read-only visibility into the async-written audit trail.
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

