<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\SupplierController;

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

        // ✅ STOCK UPDATE ROUTE - CORRECT NAME
        Route::post('/products/update-stock', [ProductController::class, 'updateStock'])
            ->name('products.update-stock'); // ये नाम use करें

        // ✅ SKU CODE VALIDATION ROUTE
        Route::post('/products/check-sku', [ProductController::class, 'checkSkuAvailability'])
            ->name('products.check-sku');

        // Attribute Routes
        Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
        Route::get('/attributes/create', [AttributeController::class, 'create'])->name('attributes.create');
        Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
        Route::get('/attributes/{id}', [AttributeController::class, 'show'])->name('attributes.show');
        Route::get('/attributes/{id}/edit', [AttributeController::class, 'edit'])->name('attributes.edit');
        Route::put('/attributes/{id}', [AttributeController::class, 'update'])->name('attributes.update');
        Route::delete('/attributes/{id}', [AttributeController::class, 'destroy'])->name('attributes.destroy');
        Route::post('/attributes/bulk-update-status', [AttributeController::class, 'bulkUpdateStatus'])->name('attributes.bulk-update-status');
        Route::delete('/attributes/bulk-delete', [AttributeController::class, 'bulkDestroy'])->name('attributes.bulk-delete');
        Route::get('/attributes-active', [AttributeController::class, 'getActiveAttributes'])->name('attributes.active');

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

        // Category Routes
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/categories/bulk-update-status', [CategoryController::class, 'bulkUpdateStatus'])->name('categories.bulk-update-status');
        Route::delete('/categories/bulk-delete', [CategoryController::class, 'bulkDestroy'])->name('categories.bulk-delete');

        // Order Routes
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{id}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{id}', [OrderController::class, 'update'])->name('orders.update');
        Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
        Route::get('/orders/{id}/invoice', [OrderController::class, 'generateInvoice'])->name('orders.invoice');
        Route::get('/orders/{id}/download-invoice', [OrderController::class, 'downloadInvoice'])->name('orders.download-invoice');
        Route::post('/orders/bulk-update-status', [OrderController::class, 'bulkUpdateStatus'])->name('orders.bulk-update-status');
        Route::delete('/orders/bulk-delete', [OrderController::class, 'bulkDestroy'])->name('orders.bulk-delete');
        Route::get('/orders-search-products', [OrderController::class, 'searchProducts'])->name('orders.search-products');
        Route::get('/orders-get-product', [OrderController::class, 'getProduct'])->name('orders.get-product');

        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders/store', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/purchase-orders/{id}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::get('/purchase-orders/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
        Route::post('/purchase-orders/{id}/update', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
        Route::post('/purchase-orders/{id}/delete', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');

        // Purchase Order Actions
        Route::post('/purchase-orders/{id}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
        Route::post('/purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::post('/purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

        // Invoice
        Route::get('/purchase-orders/{id}/invoice', [PurchaseOrderController::class, 'printInvoice'])->name('purchase-orders.invoice');
        Route::get('/purchase-orders/{id}/invoice/download', [PurchaseOrderController::class, 'generateInvoice'])->name('purchase-orders.invoice.download');
        // AJAX Routes
        Route::get('/purchase-orders/supplier/{id}', [PurchaseOrderController::class, 'getSupplierDetails'])->name('purchase-orders.supplier.details');
        Route::get('/purchase-orders/product/{id}', [PurchaseOrderController::class, 'getProductVariants'])->name('purchase-orders.product.variants');

        // Bulk Actions
        Route::post('/purchase-orders/bulk-delete', [PurchaseOrderController::class, 'bulkDestroy'])->name('purchase-orders.bulk-delete');

        Route::get('/purchase-orders/warehouse/{id}/products', [PurchaseOrderController::class, 'getProductsByWarehouse'])->name('purchase-orders.warehouse.products');

        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{id}', [SupplierController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [SupplierController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{id}', [SupplierController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-update-status', [SupplierController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
            Route::delete('/bulk-delete', [SupplierController::class, 'bulkDestroy'])->name('bulk-delete');
            Route::get('/active-suppliers', [SupplierController::class, 'getActiveSuppliers'])->name('active');
        });
    });
});
