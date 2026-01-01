<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;  // Add this import
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProductController;
// Redirect root URL based on authentication status
Route::get('/', function () {
    if (Auth::guard('admin')->check()) {
        // User is already logged in - go to dashboard
        return redirect()->route('admin.dashboard');
    }
    // User is not logged in - go to login page
    return redirect()->route('admin.login');
});

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest Routes (Only accessible when not logged in)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    });

    // Protected Routes (Only accessible when logged in)
    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::prefix('admin')->group(function () {
    // Product Routes
    Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
     Route::post('/products/bulk-update-status', [ProductController::class, 'bulkUpdateStatus'])->name('admin.products.bulk-update-status');
    Route::delete('/products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('admin.products.bulk-delete');

});
