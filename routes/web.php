<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\WarehouseController;

// Redirect root URL based on authentication status
Route::get('/', function () {
    if (Auth::guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
});

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest Routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    });

    // Protected Routes
    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Product Routes
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/bulk-update-status', [ProductController::class, 'bulkUpdateStatus'])->name('products.bulk-update-status');
        Route::delete('/products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('products.bulk-delete');

        // Barcode Routes
        Route::get('/barcode', [BarcodeController::class, 'index'])->name('barcode.index');
        Route::get('/barcode/search-products', [BarcodeController::class, 'searchProducts'])->name('barcode.search');
        Route::get('/barcode/get-product', [BarcodeController::class, 'getProduct'])->name('barcode.get-product');

        // Warehouse Routes
        Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('/warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/warehouses/{id}', [WarehouseController::class, 'show'])->name('warehouses.show');
        Route::get('/warehouses/{id}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('/warehouses/{id}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('/warehouses/{id}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
        Route::post('/warehouses/bulk-update-status', [WarehouseController::class, 'bulkUpdateStatus'])->name('warehouses.bulk-update-status');
        Route::delete('/warehouses/bulk-delete', [WarehouseController::class, 'bulkDestroy'])->name('warehouses.bulk-delete');
    });
});
