<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\SupplierPaymentController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SalesInvoiceController;
use App\Http\Controllers\Admin\InvoiceSettingController;
use App\Http\Controllers\Admin\SalesPaymentController;
use App\Http\Controllers\Admin\SalesmanController;
// Redirect root URL based on authentication status
Route::get('/', function () {
    if (Auth::guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
});
Route::post('/admin/send-otp', [AuthController::class, 'sendOtp'])->name('admin.send.otp');

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
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        // Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Product Routes
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::get('/products/create/simple', [ProductController::class, 'createSimple'])->name('products.create-simple');
        Route::post('/products/simple', [ProductController::class, 'storeSimple'])->name('products.store-simple');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
        // REMOVED: Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/bulk-update-status', [ProductController::class, 'bulkUpdateStatus'])->name('products.bulk-update-status');
        // REMOVED: Route::delete('/products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('products.bulk-delete');
        Route::post('/products/update-stock', [ProductController::class, 'updateStock'])->name('products.update-stock');
        Route::post('/products/check-sku', [ProductController::class, 'checkSkuAvailability'])->name('products.check-sku');
        Route::get('/products/{id}/variants', [ProductController::class, 'getVariants'])->name('products.variants');
        Route::post('/products/get-attribute-values', [ProductController::class, 'getAttributeValues'])->name('products.get-attribute-values');
        Route::get('/products/{id}/simple-details', [ProductController::class, 'getSimpleProductDetails'])
            ->name('products.simple-details');

        Route::get('/products/{id}/variant-details', [ProductController::class, 'getVariantProductDetails'])
            ->name('products.variant-details');

        // View Product Details
        Route::get('/products/{id}/show', [ProductController::class, 'show'])->name('products.show');

        Route::get('/products/report-data', [ProductController::class, 'getReportData'])->name('products.report-data');
        Route::get('/products/total-cost-report', [ProductController::class, 'totalCostReport'])->name('products.total-cost-report');
        Route::get('/products/low-stock-report', [ProductController::class, 'lowStockReport'])->name('products.low-stock-report');
        Route::get('/products/{id}/simple-warehouse-stock', [ProductController::class, 'getSimpleProductMainWarehouseStock'])->name('products.simple-warehouse-stock');
        Route::get('/products/{id}/variants-with-stock', [ProductController::class, 'getVariantsWithMainWarehouseStock'])->name('products.variants-with-stock');
        Route::post('/pricing/update', [ProductController::class, 'updateprice'])
            ->name('pricing.update');


        // Attribute Routes
        Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
        Route::get('/attributes/create', [AttributeController::class, 'create'])->name('attributes.create');
        Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
        Route::get('/attributes/{id}/edit', [AttributeController::class, 'edit'])->name('attributes.edit');
        Route::put('/attributes/{id}', [AttributeController::class, 'update'])->name('attributes.update');
        Route::post('/attributes/bulk-update-status', [AttributeController::class, 'bulkUpdateStatus'])->name('attributes.bulk-update-status');
        Route::get('/attributes-active', [AttributeController::class, 'getActiveAttributes'])->name('attributes.active');
        Route::get('/get-attribute-values', [AttributeController::class, 'getAttributeValues'])->name('attributes.get-values');

        // Barcode Routes
        Route::get('/barcode', [BarcodeController::class, 'index'])->name('barcode.index');
        Route::get('/barcode/search-products', [BarcodeController::class, 'searchProducts'])->name('barcode.search');
        Route::get('/barcode/get-product', [BarcodeController::class, 'getProduct'])->name('barcode.get-product');

        // Warehouse Routes
        Route::post('/warehouses/transfer-stock', [WarehouseController::class, 'transferStock'])->name('warehouses.transfer-stock');
        Route::get('/warehouses/active', [WarehouseController::class, 'getActiveWarehouses'])->name('warehouses.active');
        Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::post('/warehouses/create', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/warehouses/{id}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::post('/warehouses/{id}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('/warehouses/{id}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
        Route::post('/warehouses/{id}/set-main', [WarehouseController::class, 'setAsMain'])->name('warehouses.set-main');
        Route::get('/warehouses/{id}/stock', [WarehouseController::class, 'getWarehouseStock'])->name('warehouses.stock');

        // Category Routes
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::post('/categories/bulk-update-status', [CategoryController::class, 'bulkUpdateStatus'])->name('categories.bulk-update-status');
        // web.php या routes/admin.php में
        Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('categories.show');



        // PARTY ROUTES (Customers, Dealers, Distributors)
        Route::prefix('parties')->name('parties.')->group(function () {
            // Main party listing with type parameter
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/{type}', [CustomerController::class, 'index'])->name('index.type');

            // Create - type in query parameter
            Route::get('/create/new', [CustomerController::class, 'create'])->name('create');

            // Store
            Route::post('/', [CustomerController::class, 'store'])->name('store');

            // Edit/Update
            Route::get('/{id}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CustomerController::class, 'update'])->name('update');

            // Address Routes
            Route::post('/{id}/addresses', [CustomerController::class, 'storeAddress'])->name('addresses.store');
            Route::put('/addresses/{addressId}', [CustomerController::class, 'updateAddress'])->name('addresses.update');
            Route::delete('/addresses/{addressId}', [CustomerController::class, 'destroyAddress'])->name('addresses.destroy');
            Route::post('/addresses/{addressId}/default', [CustomerController::class, 'setDefaultAddress'])->name('addresses.default');
            Route::get('/{id}/addresses/{type}', [CustomerController::class, 'getAddresses'])->name('addresses.list');

            // Bulk Actions
            Route::post('/bulk-update-status', [CustomerController::class, 'bulkUpdateStatus'])->name('bulkUpdateStatus');

            // Ledger
            Route::get('/{id}/ledger', [CustomerController::class, 'ledger'])->name('ledger');
        });

        // Keep old customer routes for backward compatibility (redirects to new parties routes)
        Route::get('/customers', function() {
            return redirect()->route('admin.parties.index.type', ['type' => 'customer']);
        })->name('customers.index.old');

        Route::get('/dealers', function() {
            return redirect()->route('admin.parties.index.type', ['type' => 'dealer']);
        })->name('dealers.index');

        Route::get('/distributors', function() {
            return redirect()->route('admin.parties.index.type', ['type' => 'distributor']);
        })->name('distributors.index');



        // Sales
        Route::get('/sales', [SalesInvoiceController::class, 'index'])->name('sales.index');
        Route::get('/sales/create', [SalesInvoiceController::class, 'create'])->name('sales.create');
        Route::post('/sales', [SalesInvoiceController::class, 'store'])->name('sales.store');

        // AJAX / helper routes (ALWAYS ABOVE {id})
        Route::get('/sales/get-main-warehouse-products', [SalesInvoiceController::class, 'getMainWarehouseProducts'])
            ->name('sales.get-main-warehouse-products');

        // Updated routes for Party
        Route::get('/sales/parties-list', [SalesInvoiceController::class, 'getPartiesList'])
            ->name('sales.parties.list');

        Route::get('/sales/party-details/{id}', [SalesInvoiceController::class, 'getPartyDetails'])
            ->name('sales.get-party-details');

        Route::post('/sales/create-party', [SalesInvoiceController::class, 'storePartyAjax'])
            ->name('sales.create-party');

        // Payment route
        Route::post('/sales/{id}/payment', [SalesInvoiceController::class, 'createPayment'])
            ->name('sales.create-payment');

        // Dynamic routes LAST
        Route::get('/sales/{id}', [SalesInvoiceController::class, 'show'])->name('sales.show');
        Route::delete('/sales/{id}', [SalesInvoiceController::class, 'destroy'])->name('sales.destroy');

        // Invoice Settings Routes
        Route::get('/invoice-settings', [InvoiceSettingController::class, 'index'])
            ->name('invoice-settings.index');
        Route::post('/invoice-settings', [InvoiceSettingController::class, 'store'])
            ->name('invoice-settings.store');
        Route::get('/invoice-settings/get', [InvoiceSettingController::class, 'getSettings'])
            ->name('invoice-settings.get');

         // Payment In Routes
        Route::get('/payments', [SalesPaymentController::class, 'index'])
            ->name('payments.index');

        // AJAX routes (specific routes first)
        Route::get('/payments/get-customer-invoices', [SalesPaymentController::class, 'getCustomerInvoices'])
            ->name('payments.get-customer-invoices');
        Route::get('/payments/customer/{customerId}/payments', [SalesPaymentController::class, 'getCustomerPayments'])
            ->name('payments.get-customer-payments');

        // POST/DELETE routes
        Route::post('/payments', [SalesPaymentController::class, 'store'])
            ->name('payments.store');
        Route::delete('/payments/{id}', [SalesPaymentController::class, 'destroy'])
            ->name('payments.destroy');
        Route::post('/payments/bulk-delete', [SalesPaymentController::class, 'bulkDestroy'])
            ->name('payments.bulk-delete');
        Route::get('/payments/get-party-invoices', [SalesPaymentController::class, 'getPartyInvoices'])
            ->name('payments.get-party-invoices');

        // Get party payment history
        Route::get('/payments/party/{partyId}/payments', [SalesPaymentController::class, 'getPartyPayments'])
            ->name('payments.get-party-payments');
        Route::get('/payments/invoice/{id}', [SalesPaymentController::class, 'getInvoiceDetails'])
            ->name('payments.invoice-details');


        // Add these inside your admin middleware group
        Route::prefix('salesmen')->name('salesmen.')->group(function () {
            Route::get('/', [SalesmanController::class, 'index'])->name('index');
            Route::get('/create', [SalesmanController::class, 'create'])->name('create');
            Route::post('/', [SalesmanController::class, 'store'])->name('store');
            Route::get('/{id}', [SalesmanController::class, 'show'])->name('show'); // View route
            Route::get('/{id}/edit', [SalesmanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SalesmanController::class, 'update'])->name('update');
            Route::post('/bulk-update-status', [SalesmanController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
            Route::get('/check-phone', [SalesmanController::class, 'checkPhone'])->name('check-phone');
            Route::get('/check-email', [SalesmanController::class, 'checkEmail'])->name('check-email');

            // AJAX routes
            Route::get('/party-counts', [SalesmanController::class, 'getPartyCounts'])->name('party-counts');
            Route::get('/{id}/parties', [SalesmanController::class, 'getAssignedParties'])->name('assigned-parties');
        });


    });
});

// In routes/web.php - Add this route in the middleware group
Route::get('/api/warehouse-stock', function (Illuminate\Http\Request $request) {
    $warehouseId = $request->get('warehouse_id');
    $productId = $request->get('product_id');
    $productType = $request->get('product_type', 'simple');
    $variantId = $request->get('variant_id');

    $query = \App\Models\WarehouseStock::where('warehouse_id', $warehouseId)
        ->where('product_id', $productId)
        ->where('product_type', $productType);

    if ($variantId) {
        $query->where('variant_id', $variantId);
    }

    $stock = $query->first();

    return response()->json([
        'success' => true,
        'stock' => $stock ? $stock->quantity : 0
    ]);
})->name('api.warehouse-stock');
