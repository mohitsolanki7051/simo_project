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
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SalesInvoiceController;
use App\Http\Controllers\Admin\InvoiceSettingController;
use App\Http\Controllers\Admin\CashMemoInvoiceSettingController;
use App\Http\Controllers\Admin\SalesPaymentController;
use App\Http\Controllers\Admin\SalesPaymentOutController;
use App\Http\Controllers\Admin\SalesmanController;
use App\Http\Controllers\Admin\WarrantyController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\DefectiveStockController;
use App\Http\Controllers\Admin\SalesReturnController;
use App\Http\Controllers\Admin\CreditNoteController;
use App\Http\Controllers\Admin\PurchaseExecutiveController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\PurchaseInvoiceController;
use App\Http\Controllers\Admin\PurchaseReturnController;
use App\Http\Controllers\Admin\DebitNoteController;
use App\Http\Controllers\Admin\LedgerController;
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



    Route::prefix('sales')->name('sales.')->group(function () {
        // List all invoices
        Route::get('/', [SalesInvoiceController::class, 'index'])->name('index');

        // Create new invoice (draft)
        Route::get('/create', [SalesInvoiceController::class, 'create'])->name('create');
        Route::post('/', [SalesInvoiceController::class, 'store'])->name('store');

        // AJAX / helper routes (MUST come before {id} routes)
        Route::get('/get-main-warehouse-products', [SalesInvoiceController::class, 'getMainWarehouseProducts'])
            ->name('get-main-warehouse-products');
        Route::get('/parties-list', [SalesInvoiceController::class, 'getPartiesList'])
            ->name('parties.list');
        Route::get('/party-details/{id}', [SalesInvoiceController::class, 'getPartyDetails'])
            ->name('get-party-details');
        Route::post('/create-party', [SalesInvoiceController::class, 'storePartyAjax'])
            ->name('create-party');
        Route::get('/party-credit-status/{partyId}', [SalesInvoiceController::class, 'getPartyCreditStatus'])->name('credit-status');
        Route::get('/next-invoice-number', [SalesInvoiceController::class, 'getNextInvoiceNumber'])
            ->name('get-next-invoice-number');

        // Dynamic routes with {id} parameter (these go LAST)
        Route::get('/{id}', [SalesInvoiceController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [SalesInvoiceController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SalesInvoiceController::class, 'update'])->name('update');
        Route::post('/{id}/generate', [SalesInvoiceController::class, 'generate'])->name('generate');
        Route::post('/{id}/payment', [SalesInvoiceController::class, 'createPayment'])->name('create-payment');
        Route::delete('/{id}', [SalesInvoiceController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/cancel', [SalesInvoiceController::class, 'cancel'])->name('cancel');


    });



        // Invoice Settings Routes
        Route::get('/invoice-settings', [InvoiceSettingController::class, 'index'])
            ->name('invoice-settings.index');
        Route::post('/invoice-settings', [InvoiceSettingController::class, 'store'])
            ->name('invoice-settings.store');
        Route::get('/invoice-settings/get', [InvoiceSettingController::class, 'getSettings'])
            ->name('invoice-settings.get');

         // Cash Memo Invoice Settings Routes
        Route::get('/cashmemo-invoice-settings', [CashMemoInvoiceSettingController::class, 'index'])
            ->name('cashmemo-invoice-settings.index');
        Route::post('/cashmemo-invoice-settings', [CashMemoInvoiceSettingController::class, 'store'])
        ->name('cashmemo-invoice-settings.store');
        Route::get('/cashmemo-invoice-settings/get', [CashMemoInvoiceSettingController::class, 'getSettings'])
            ->name('cashmemo-invoice-settings.get');

        // Payment In Routes
        Route::prefix('payments')->name('payments.')->group(function () {

            Route::get('/', [SalesPaymentController::class,'index'])->name('index');

            Route::get('/create', [SalesPaymentController::class,'create'])->name('create');

            Route::post('/store', [SalesPaymentController::class,'store'])->name('store');

            Route::get('/search-parties', [SalesPaymentController::class,'searchParties'])
                ->name('search-parties');

            Route::get('/party-details/{partyId}', [SalesPaymentController::class,'getPartyDetails'])
                ->name('party.details');
            Route::get('/refund-party/{id}', [SalesPaymentController::class, 'getRefundPartyDetails'])
                ->name('refund-party.details');
            Route::get('/{id}', [SalesPaymentController::class, 'show'])->name('show');

        });
        // Payment Out Routes
        Route::prefix('payments-out')->name('payments-out.')->group(function () {
            Route::get('/', [SalesPaymentOutController::class, 'index'])->name('index');
            Route::get('/create', [SalesPaymentOutController::class, 'create'])->name('create');
            Route::post('/store', [SalesPaymentOutController::class, 'store'])->name('store');
            Route::get('/search-parties', [SalesPaymentOutController::class, 'searchParties'])->name('search-parties');
            Route::get('/party-details/{partyId}', [SalesPaymentOutController::class, 'getPartyDetails'])->name('party.details');
            Route::get('/refund-party/{id}', [SalesPaymentOutController::class, 'getRefundPartyDetails'])->name('refund-party.details');
            Route::get('/{id}', [SalesPaymentOutController::class, 'show'])->name('show');
            Route::delete('/{id}', [SalesPaymentOutController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('salesmen')->name('salesmen.')->group(function () {

            // ── Static routes FIRST ──────────────────────────────────────
            Route::get('/',                        [SalesmanController::class, 'index'])->name('index');
            Route::get('/create',                  [SalesmanController::class, 'create'])->name('create');
            Route::post('/',                       [SalesmanController::class, 'store'])->name('store');
            Route::post('/bulk-update-status',     [SalesmanController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
            Route::get('/check-phone',             [SalesmanController::class, 'checkPhone'])->name('check-phone');
            Route::get('/check-email',             [SalesmanController::class, 'checkEmail'])->name('check-email');
            Route::get('/party-counts',            [SalesmanController::class, 'getPartyCounts'])->name('party-counts');

            // ── {id} routes AFTER static ─────────────────────────────────
            Route::get('/{id}',                    [SalesmanController::class, 'show'])->name('show');
            Route::get('/{id}/edit',               [SalesmanController::class, 'edit'])->name('edit');
            Route::put('/{id}',                    [SalesmanController::class, 'update'])->name('update');
            Route::get('/{id}/parties',            [SalesmanController::class, 'getAssignedParties'])->name('assigned-parties');
            Route::post('/{id}/pay-commission',    [SalesmanController::class, 'payCommission'])->name('pay-commission');
            Route::post('/{id}/pay-fixed',         [SalesmanController::class, 'payFixed'])->name('pay-fixed');
            Route::get('/{id}/check-fixed-paid',   [SalesmanController::class, 'checkFixedPaidStatus'])->name('check-fixed-paid');
            Route::get('/{id}/search-parties',     [SalesmanController::class, 'searchParties'])->name('search-parties');
            Route::get('/{id}/download-report',    [SalesmanController::class, 'downloadReport'])->name('download-report');
        });

        // web.php - Add after salesmen routes

        Route::prefix('purchase-executives')->name('purchase-executives.')->group(function () {
            // ── Static routes FIRST ──────────────────────────────────────
            Route::get('/',                        [PurchaseExecutiveController::class, 'index'])->name('index');
            Route::get('/create',                  [PurchaseExecutiveController::class, 'create'])->name('create');
            Route::post('/',                       [PurchaseExecutiveController::class, 'store'])->name('store');
            Route::post('/bulk-update-status',     [PurchaseExecutiveController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
            Route::get('/check-phone',             [PurchaseExecutiveController::class, 'checkPhone'])->name('check-phone');
            Route::get('/check-email',             [PurchaseExecutiveController::class, 'checkEmail'])->name('check-email');
            Route::get('/vendor-counts',            [PurchaseExecutiveController::class, 'getVendorCounts'])->name('vendor-counts');

            // ── {id} routes AFTER static ─────────────────────────────────
            Route::get('/{id}',                    [PurchaseExecutiveController::class, 'show'])->name('show');
            Route::get('/{id}/edit',               [PurchaseExecutiveController::class, 'edit'])->name('edit');
            Route::put('/{id}',                    [PurchaseExecutiveController::class, 'update'])->name('update');
            Route::get('/{id}/vendors',            [PurchaseExecutiveController::class, 'getAssignedVendors'])->name('assigned-vendors');
            Route::get('/{id}/search-vendors',     [PurchaseExecutiveController::class, 'searchVendors'])->name('search-vendors');
        });

        Route::prefix('vendors')->name('vendors.')->group(function () {
        // ── Static routes FIRST ──────────────────────────────────────
        Route::get('/',                        [VendorController::class, 'index'])->name('index');
        Route::get('/create',                  [VendorController::class, 'create'])->name('create');
        Route::post('/',                       [VendorController::class, 'store'])->name('store');
        Route::post('/bulk-update-status',     [VendorController::class, 'bulkUpdateStatus'])->name('bulk-update-status');

        // AJAX search
        Route::get('/search',                   [VendorController::class, 'searchVendors'])->name('search');
        Route::get('/{id}/details',             [VendorController::class, 'getVendorDetails'])->name('details');

        // ── {id} routes AFTER static ─────────────────────────────────
        Route::get('/{id}',                     [VendorController::class, 'show'])->name('show');
        Route::get('/{id}/edit',                [VendorController::class, 'edit'])->name('edit');
        Route::put('/{id}',                     [VendorController::class, 'update'])->name('update');
        Route::get('/{id}/ledger',               [VendorController::class, 'ledger'])->name('ledger');

        // Address management
        Route::get('/{id}/addresses/{type}',     [VendorController::class, 'getAddresses'])->name('addresses.list');
        Route::post('/{id}/addresses',           [VendorController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{addressId}',     [VendorController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{addressId}',  [VendorController::class, 'destroyAddress'])->name('addresses.destroy');
        Route::post('/addresses/{addressId}/default', [VendorController::class, 'setDefaultAddress'])->name('addresses.default');
    });
        // Warranty Routes
        Route::prefix('warranty')->name('warranty.')->group(function () {
            Route::get('/', [WarrantyController::class, 'index'])->name('index');
            Route::get('/create', [WarrantyController::class, 'create'])->name('create');
            Route::post('/search-invoice', [WarrantyController::class, 'searchInvoice'])->name('search-invoice');
            Route::get('/invoice/{id}', [WarrantyController::class, 'getInvoiceDetails'])->name('invoice-details');
            Route::post('/', [WarrantyController::class, 'store'])->name('store');
            Route::get('/{id}', [WarrantyController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [WarrantyController::class, 'edit'])->name('edit');        // <-- NEW
            Route::put('/{id}', [WarrantyController::class, 'update'])->name('update');
            Route::post('/{id}/approve-replacement', [WarrantyController::class, 'approveReplacement'])->name('approve-replacement');
            Route::post('/{id}/mark-repair-completed', [WarrantyController::class, 'markRepairCompleted'])->name('mark-repair-completed');
            Route::post('/{id}/mark-scrapped', [WarrantyController::class, 'markScrapped'])->name('mark-scrapped');
        });

        // Defective Stock (read-only)
        Route::get('defective-stock', [DefectiveStockController::class, 'index'])->name('defective-stock.index');
        Route::get('defective-stock/{id}/detail', [DefectiveStockController::class, 'detail'])->name('defective-stock.detail');

        Route::prefix('quotations')->name('quotations.')->group(function () {
            // List all quotations
            Route::get('/', [QuotationController::class, 'index'])->name('index');

            // Create new quotation
            Route::get('/create', [QuotationController::class, 'create'])->name('create');
            Route::post('/', [QuotationController::class, 'store'])->name('store');

            // AJAX / helper routes (MUST come before {id} routes)
            Route::get('/get-main-warehouse-products', [QuotationController::class, 'getMainWarehouseProducts'])
                ->name('get-main-warehouse-products');
            Route::get('/parties-list', [QuotationController::class, 'getPartiesList'])
                ->name('parties.list');
            Route::get('/party-details/{id}', [QuotationController::class, 'getPartyDetails'])
                ->name('get-party-details');
            Route::post('/create-party', [QuotationController::class, 'storePartyAjax'])
                ->name('create-party');

            // Dynamic routes with {id} parameter
            Route::get('/{id}', [QuotationController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [QuotationController::class, 'edit'])->name('edit');
            Route::put('/{id}', [QuotationController::class, 'update'])->name('update');
            Route::delete('/{id}', [QuotationController::class, 'destroy'])->name('destroy');

            // Status update
            Route::post('/{id}/status', [QuotationController::class, 'updateStatus'])->name('update-status');

            // PDF and WhatsApp
            Route::get('/{id}/pdf', [QuotationController::class, 'pdf'])->name('pdf');
            Route::post('/{id}/send-whatsapp', [QuotationController::class, 'sendWhatsApp'])->name('send-whatsapp');

            // Convert to invoice
            Route::post('/{id}/convert-to-invoice', [QuotationController::class, 'convertToInvoice'])->name('convert-to-invoice');
        });

        // Sales Returns Routes
        Route::prefix('sales-returns')->name('sales-returns.')->group(function () {
            // List all returns
            Route::get('/', [SalesReturnController::class, 'index'])->name('index');

            // Create new return
            Route::get('/create', [SalesReturnController::class, 'create'])->name('create');
            Route::post('/', [SalesReturnController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [SalesReturnController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SalesReturnController::class, 'update'])->name('update');
            // AJAX / helper routes (MUST come before {id} routes)
            Route::get('/search-invoices', [SalesReturnController::class, 'searchInvoices'])->name('search-invoices');
            Route::get('/invoice-details/{id}', [SalesReturnController::class, 'getInvoiceDetails'])->name('invoice-details');

            // Dynamic routes with {id} parameter (these go LAST)
            Route::get('/{id}', [SalesReturnController::class, 'show'])->name('show');
            Route::post('/{id}/complete', [SalesReturnController::class, 'complete'])->name('complete');
            Route::post('/{id}/cancel', [SalesReturnController::class, 'cancel'])->name('cancel');
            Route::delete('/{id}', [SalesReturnController::class, 'destroy'])->name('destroy');
        });

        // Credit Notes Routes
        Route::prefix('credit-notes')->name('credit-notes.')->group(function () {
            Route::get('/', [CreditNoteController::class, 'index'])->name('index');
            Route::get('/{id}', [CreditNoteController::class, 'show'])->name('show');
            Route::post('/{id}/cancel', [CreditNoteController::class, 'cancel'])->name('cancel');
        });

       // Purchase Invoice Routes
        Route::prefix('purchases')->name('purchases.')->group(function () {
            Route::get('/', [PurchaseInvoiceController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseInvoiceController::class, 'create'])->name('create');
            Route::post('/', [PurchaseInvoiceController::class, 'store'])->name('store');

            // AJAX routes
            Route::get('/parties-list', [PurchaseInvoiceController::class, 'getPartiesList'])->name('parties.list');
            Route::get('/party-details/{id}', [PurchaseInvoiceController::class, 'getPartyDetails'])->name('party-details');
            Route::post('/create-party', [PurchaseInvoiceController::class, 'storePartyAjax'])->name('create-party');
            Route::get('/get-warehouse-products', [PurchaseInvoiceController::class, 'getWarehouseProducts'])->name('get-warehouse-products');

            // *** NEW: Quick Add Product from inside Purchase Invoice modal ***
            Route::post('/quick-add-product', [PurchaseInvoiceController::class, 'quickAddSimpleProduct'])->name('quick-add-product');
            Route::get('/next-invoice-number', [PurchaseInvoiceController::class, 'getNextInvoiceNumber'])->name('get-next-invoice-number');

            // Invoice actions
            Route::get('/{id}', [PurchaseInvoiceController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PurchaseInvoiceController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PurchaseInvoiceController::class, 'update'])->name('update');
            Route::post('/{id}/generate', [PurchaseInvoiceController::class, 'generate'])->name('generate');
            Route::post('/{id}/cancel', [PurchaseInvoiceController::class, 'cancel'])->name('cancel');
            Route::delete('/{id}', [PurchaseInvoiceController::class, 'destroy'])->name('destroy');
        });

        // Purchase Returns Routes
        Route::prefix('purchase-returns')->name('purchase-returns.')->group(function () {
            // List all returns
            Route::get('/', [PurchaseReturnController::class, 'index'])->name('index');

            // Create new return
            Route::get('/create', [PurchaseReturnController::class, 'create'])->name('create');
            Route::post('/', [PurchaseReturnController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [PurchaseReturnController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PurchaseReturnController::class, 'update'])->name('update');

            // AJAX / helper routes (MUST come before {id} routes)
            Route::get('/search-invoices', [PurchaseReturnController::class, 'searchInvoices'])->name('search-invoices');
            Route::get('/invoice-details/{id}', [PurchaseReturnController::class, 'getInvoiceDetails'])->name('invoice-details');

            // Dynamic routes with {id} parameter (these go LAST)
            Route::get('/{id}', [PurchaseReturnController::class, 'show'])->name('show');
            Route::post('/{id}/complete', [PurchaseReturnController::class, 'complete'])->name('complete');
            Route::post('/{id}/cancel', [PurchaseReturnController::class, 'cancel'])->name('cancel');
            Route::delete('/{id}', [PurchaseReturnController::class, 'destroy'])->name('destroy');
        });

        // Debit Notes Routes
        Route::prefix('debit-notes')->name('debit-notes.')->group(function () {
            Route::get('/', [DebitNoteController::class, 'index'])->name('index');
            Route::get('/{id}', [DebitNoteController::class, 'show'])->name('show');
            Route::post('/{id}/cancel', [DebitNoteController::class, 'cancel'])->name('cancel');
        });
        Route::get('ledger/{partyType}/{id}', [LedgerController::class, 'show'])
            ->name('ledger.show')
            ->where('partyType', 'customer|dealer|distributor|vendor');
        Route::get('/ledger/{partyType}/{id}/print', [LedgerController::class, 'print'])->name('ledger.print');


    });
});
//whatsapp invoice route
 Route::get('/invoice/{token}', [App\Http\Controllers\InvoicePublicController::class, 'show'])
            ->name('invoice.public');
Route::get('/quotation/{token}', [App\Http\Controllers\PublicQuotationController::class, 'show'])
    ->name('quotation.public');
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
