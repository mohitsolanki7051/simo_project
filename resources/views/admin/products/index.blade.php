@extends('layouts.admin')

@section('title', 'Products - Admin Panel')
@section('header-title', 'Products Management')

@section('content')
<div class="products-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Product List</h2>
        </div>
        <div class="header-right">
            <button type="button" class="btn-small btn-primary" onclick="openProductTypeModal()">
                <span class="btn-icon">+</span> Add Product
            </button>
        </div>
    </div>

    <!-- Report Summary Cards -->
    <div class="report-cards">
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">💰</div>
            <div class="card-info">
                <div class="card-title">Product Total Cost</div>
                <div class="card-value" id="totalCostValue">₹0.00</div>
                <div class="card-desc" id="totalProductsCount">0 products (0 simple, 0 variant)</div>
            </div>
        </div>
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">⚠️</div>
            <div class="card-info">
                <div class="card-title">Low Stock Products</div>
                <div class="card-value" id="lowStockCount">0</div>
                <div class="card-desc" id="lowStockDetails">0 simple, 0 variant</div>
            </div>
        </div>
    </div>

    <!-- Filters and Bulk Actions -->
    <div class="table-filters">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" placeholder="Search products..." id="searchInput">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All ({{ $products->count() }})</button>
            <button class="filter-btn" data-filter="active">Active</button>
            <button class="filter-btn" data-filter="inactive">Inactive</button>
        </div>
        <div class="bulk-actions">
            <select class="bulk-select" id="bulkActionSelect" disabled>
                <option value="">Bulk Actions</option>
                <option value="active">Set Active</option>
                <option value="inactive">Set Inactive</option>
                <!-- REMOVED: Delete option -->
            </select>
            <button class="btn-bulk" id="applyBulkAction" disabled>Apply</button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="table-wrapper">
        <table class="compact-table">
            <thead>
                <tr>
                    <th class="th-checkbox"><input type="checkbox" id="selectAll"></th>
                    <th class="th-sno">S.No.</th>
                    <th class="th-image">Image</th>
                    <th class="th-name">Product Name</th>
                    <th class="th-type">Type</th>
                    <th class="th-mrp">MRP</th>
                    <th class="th-price">Sale Price</th>
                    <th class="th-stock">Stock</th>
                    <th class="th-status">Status</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $product)
                <tr class="table-row"
                    data-status="{{ $product->status }}"
                    data-product-id="{{ $product->id }}"
                    data-product-type="{{ $product instanceof \App\Models\SimpleProduct ? 'simple' : 'variant' }}">
                    <td class="td-checkbox">
                        <input type="checkbox" class="row-checkbox" value="{{ $product->id }}">
                    </td>
                    <td class="td-sno">{{ $index + 1 }}</td>
                    <td class="td-image">
                        <div class="product-img">
                            <img src="{{ asset('storage/' . $product->base_image) }}"
                                 alt="{{ $product->name }}"
                                 onerror="this.src='https://via.placeholder.com/40x40/667eea/ffffff?text=IMG'">
                        </div>
                    </td>
                    <td class="td-name">
                        <div class="product-name">{{ $product->name }}</div>
                    </td>
                    <td class="td-type">
                        @if($product instanceof \App\Models\SimpleProduct)
                            <span class="type-badge type-simple">Simple</span>
                        @else
                            <span class="type-badge type-variant">Variant</span>
                        @endif
                    </td>
                    <td class="td-mrp">
                        @if($product instanceof \App\Models\SimpleProduct)
                            ₹{{ number_format($product->mrp_price, 2) }}
                        @else
                            {{ $product->formatted_mrp_price_range }}
                        @endif
                    </td>
                    <td class="td-price">
                        @if($product instanceof \App\Models\SimpleProduct)
                            <div class="price-main">₹{{ number_format($product->sale_price, 2) }}</div>
                        @else
                            <div class="price-main">{{ $product->formatted_price_range }}</div>
                        @endif
                    </td>
                    <td class="td-stock">
                        <div class="stock-info">
                            <span class="stock-value">{{ $product->total_stock }}</span>
                            <span class="stock-unit">{{ $product->unit ?? 'pcs' }}</span>
                        </div>
                        @if($product->min_stock_alert && $product->total_stock <= $product->min_stock_alert)
                            <div class="stock-alert">⚠️ Alert: {{ $product->min_stock_alert }}</div>
                        @endif
                    </td>
                    <td class="td-status">
                        <span class="status-badge status-{{ $product->status }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </td>
                    <td class="td-actions">
                        <div class="action-icons">
                            <a href="{{ route('admin.products.show', $product->id) }}"
                               class="icon-btn icon-view" title="View">
                                👁️
                            </a>
                            <a href="{{ route('admin.products.edit', $product->id) }}"
                               class="icon-btn icon-edit" title="Edit">
                                ✏️
                            </a>

                            <button type="button"
                                    class="icon-btn icon-stock"
                                    onclick="openStockManagement('{{ $product->id }}', '{{ addslashes($product->name) }}')"
                                    title="Manage Stock">
                                📦
                            </button>
                            <!-- REMOVED: Delete button -->
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="empty-state">
                        <div class="empty-content">
                            <div class="empty-icon">📦</div>
                            <h4>No Products Found</h4>
                            <p>Start by adding your first product</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->count() > 0)
    <div class="table-footer">
        <div class="footer-info">
            Showing {{ $products->count() }} products
        </div>
    </div>
    @endif
</div>

<!-- Product Type Modal -->
<div class="modal" id="productTypeModal">
    <div class="modal-overlay" onclick="closeProductTypeModal()"></div>
    <div class="modal-content modal-compact">
        <div class="modal-header">
            <h4 class="modal-title">Add Product</h4>
            <button type="button" class="modal-close" onclick="closeProductTypeModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="type-options">
                <button type="button" class="type-option" onclick="selectProductType('simple')">
                    <div class="option-icon">📦</div>
                    <div class="option-details">
                        <h5>Simple Product</h5>
                        <p>Single product without variations</p>
                    </div>
                </button>
                <button type="button" class="type-option" onclick="selectProductType('variant')">
                    <div class="option-icon">🎨</div>
                    <div class="option-details">
                        <h5>Variant Product</h5>
                        <p>Product with multiple variations</p>
                    </div>
                </button>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal btn-cancel" onclick="closeProductTypeModal()">Cancel</button>
        </div>
    </div>
</div>

<!-- REMOVED: Delete Confirmation Modal -->

<!-- Bulk Action Modal -->
<div class="modal" id="bulkActionModal">
    <div class="modal-overlay" onclick="closeBulkActionModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon" style="background: #f59e0b;">⚠️</div>
            <h4 class="modal-title" id="bulkModalTitle"></h4>
        </div>
        <p class="modal-text" id="bulkModalText"></p>
        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeBulkActionModal()">Cancel</button>
            <button type="button" class="btn-modal btn-confirm" id="confirmBulkAction">Confirm</button>
        </div>
    </div>
</div>

<!-- Stock Management Modal -->
<div class="modal" id="stockManagementModal">
    <div class="modal-overlay" onclick="closeStockManagementModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon" style="background: #8b5cf6;">📦</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Manage Stock</h4>
                <div class="modal-subtitle">Adjust inventory levels in Main Warehouse</div>
            </div>
            <button type="button" class="modal-close" onclick="closeStockManagementModal()">×</button>
        </div>
        <form id="stockManagementForm" method="POST">
            @csrf
            <div class="modal-body">
                <input type="hidden" name="product_id" id="stockProductId">
                <input type="hidden" name="product_type" id="productType">
                <input type="hidden" name="variant_id" id="selectedVariantId">
                <input type="hidden" name="warehouse_id" id="warehouseId" value="{{ $defaultWarehouse->id ?? '' }}">

                <!-- Product Info Card -->
                <div class="info-card">
                    <div class="info-item">
                        <label>Product</label>
                        <div class="info-value" id="stockProductName"></div>
                    </div>
                    <div class="info-item">
                        <label>Current Stock (Main Warehouse)</label>
                        <div class="stock-display">
                            <span class="stock-value" id="currentStockValue">0</span>
                            <span class="stock-unit">units</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Warehouse</label>
                        <div class="info-value" style="color: #667eea; font-weight: 600;">
                            {{ $defaultWarehouse->name ?? 'Main Warehouse' }}
                        </div>
                    </div>
                </div>

                <!-- Variant Selector -->
                <div class="variant-selector" id="variantSelector">
                    <label class="section-label">Select Variant</label>
                    <div class="variant-options" id="variantOptions">
                        <div class="no-variants">Loading variants...</div>
                    </div>
                </div>

                <!-- Adjustment Form -->
                <div class="adjustment-form">
                    <label class="section-label">Stock Adjustment</label>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="stockAdjustmentType">
                                <span class="label-text">Type</span>
                                <span class="required">*</span>
                            </label>
                            <select class="form-select" name="adjustment_type" id="stockAdjustmentType" onchange="updateStockCalculation()">
                                <option value="add">Add Stock</option>
                                <option value="reduce">Reduce Stock</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="stockQuantity">
                                <span class="label-text">Quantity</span>
                                <span class="required">*</span>
                            </label>
                            <input type="number" class="form-input" name="quantity" id="stockQuantity"
                                   value="1" min="1" step="1" oninput="updateStockCalculation()">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea class="form-textarea" name="remarks" id="remarks"
                                  rows="3" placeholder="Enter reason for stock adjustment..."></textarea>
                    </div>
                </div>

                <!-- Calculation Preview -->
                <div class="calculation-card">
                    <div class="calc-header">Stock Calculation</div>
                    <div class="calc-body">
                        <div class="calc-row">
                            <span class="calc-label">Current Stock:</span>
                            <span class="calc-value" id="calcCurrent">0</span>
                        </div>
                        <div class="calc-row">
                            <span class="calc-label">Adjustment:</span>
                            <span class="calc-value" id="calcAdjustment">+0</span>
                        </div>
                        <div class="calc-divider"></div>
                        <div class="calc-row total">
                            <span class="calc-label">New Stock:</span>
                            <span class="calc-value" id="calcNew">0</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closeStockManagementModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-confirm" id="stockSubmitBtn">Update Stock</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    /* Main Container */
    .products-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 12px;
        line-height: 1.4;
    }

    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-title {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
        margin: 0 0 4px 0;
    }

    .page-subtitle {
        font-size: 11px;
        color: #6b7280;
        margin: 0;
    }

    .btn-small {
        padding: 6px 12px;
        background: #fa8427;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
    }

    .btn-small:hover {
        background: #e97317;
        transform: translateY(-1px);
    }

    .btn-icon {
        font-size: 12px;
    }

    /* Report Cards */
    .report-cards {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }

    .report-card {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.2s;
        cursor: default;
        min-width: 300px;
    }

    .report-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        transform: translateY(-2px);
    }

    .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
    }

    .card-info {
        flex: 1;
    }

    .card-title {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .card-value {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .card-desc {
        font-size: 11px;
        color: #9ca3af;
    }

    /* Filters */
    .table-filters {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box {
        flex: 1;
        max-width: 200px;
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: #718096;
    }

    .search-input {
        width: 100%;
        padding: 6px 8px 6px 24px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 11px;
        background: #f9fafb;
    }

    .search-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .filter-buttons {
        display: flex;
        gap: 4px;
    }

    .filter-btn {
        padding: 5px 10px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 10px;
        color: #4b5563;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-btn:hover {
        background: #e5e7eb;
    }

    .filter-btn.active {
        background: #667eea;
        border-color: #667eea;
        color: white;
    }

    /* Bulk Actions */
    .bulk-actions {
        display: flex;
        gap: 4px;
        align-items: center;
        margin-left: auto;
    }

    .bulk-select {
        padding: 5px 8px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 11px;
        background: #f9fafb;
        min-width: 120px;
        cursor: pointer;
    }

    .bulk-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-bulk {
        padding: 5px 12px;
        background: #48bb78;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 11px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-bulk:hover:not(:disabled) {
        background: #38a169;
        transform: translateY(-1px);
    }

    .btn-bulk:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Compact Table */
    .table-wrapper {
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        margin-top: 10px;
    }

    .compact-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .compact-table th {
        background: #f8fafc;
        padding: 8px 10px;
        text-align: left;
        font-weight: 600;
        color: #4b5563;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .compact-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .compact-table tr:last-child td {
        border-bottom: none;
    }

    .compact-table tr:hover {
        background: #f9fafb;
    }

    /* Column widths */
    .th-checkbox { width: 30px; }
    .th-sno { width: 50px; }
    .th-image { width: 50px; }
    .th-name { width: 180px; }
    .th-type { width: 70px; }
    .th-mrp { width: 80px; }
    .th-price { width: 90px; }
    .th-stock { width: 80px; }
    .th-status { width: 70px; }
    .th-actions { width: 90px; }

    /* Checkbox */
    input[type="checkbox"] {
        width: 14px;
        height: 14px;
        accent-color: #667eea;
        cursor: pointer;
    }

    /* S.No. */
    .td-sno {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        text-align: center;
    }

    /* Image */
    .product-img {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .product-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Product Name */
    .product-name {
        font-weight: 600;
        color: #1f2937;
        line-height: 1.3;
        font-size: 12px;
    }

    /* Type Badge */
    .type-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .type-simple {
        background: #dbeafe;
        color: #1e40af;
    }

    .type-variant {
        background: #f3e8ff;
        color: #6b21a8;
    }

    /* Prices */
    .td-mrp {
        font-weight: 600;
        color: #1f2937;
        white-space: nowrap;
        font-size: 11px;
    }

    .td-price {
        white-space: nowrap;
    }

    .price-main {
        font-weight: 600;
        color: #1f2937;
        font-size: 11px;
    }

    /* Stock */
    .stock-info {
        display: flex;
        align-items: baseline;
        gap: 3px;
        margin-bottom: 3px;
    }

    .stock-value {
        font-weight: 600;
        color: #1f2937;
        font-size: 12px;
    }

    .stock-unit {
        font-size: 9px;
        color: #6b7280;
    }

    .stock-alert {
        font-size: 9px;
        color: #dc2626;
        background: #fee2e2;
        padding: 2px 6px;
        border-radius: 3px;
        display: inline-block;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 9px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #f3f4f6;
        color: #6b7280;
    }

    /* Action Icons */
    .action-icons {
        display: flex;
        gap: 6px;
    }

    .icon-btn {
        width: 26px;
        height: 26px;
        border-radius: 5px;
        border: none;
        background: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: all 0.2s;
        padding: 0;
    }

    .icon-edit {
        color: #3b82f6;
        background: #dbeafe;
    }

    .icon-edit:hover {
        background: #bfdbfe;
        transform: scale(1.1);
    }

    .icon-stock {
        color: #8b5cf6;
        background: #ede9fe;
    }

    .icon-stock:hover {
        background: #ddd6fe;
        transform: scale(1.1);
    }

    .icon-delete {
        color: #ef4444;
        background: #fee2e2;
    }

    .icon-delete:hover {
        background: #fecaca;
        transform: scale(1.1);
    }

    /* Empty State */
    .empty-state {
        padding: 40px 20px;
        text-align: center;
    }

    .empty-content {
        display: inline-block;
    }

    .empty-icon {
        font-size: 32px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .empty-content h4 {
        font-size: 14px;
        color: #374151;
        margin-bottom: 5px;
    }

    .empty-content p {
        font-size: 11px;
        color: #6b7280;
    }

    /* Table Footer */
    .table-footer {
        padding: 10px 15px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        font-size: 11px;
        color: #6b7280;
        border-radius: 0 0 6px 6px;
        text-align: center;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(2px);
    }

    .modal-content {
        position: relative;
        background: white;
        border-radius: 12px;
        padding: 0;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        animation: modalFadeIn 0.2s ease;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    .modal-compact {
        max-width: 400px;
        padding: 20px;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px 12px 0 0;
    }

    .modal-title-section {
        flex: 1;
    }

    .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        line-height: 1.3;
    }

    .modal-subtitle {
        font-size: 11px;
        color: #6b7280;
        margin-top: 2px;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        color: #6b7280;
        cursor: pointer;
        padding: 4px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: #f3f4f6;
        color: #1f2937;
        transform: rotate(90deg);
    }

    .modal-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    /* Product Type Modal */
    .type-options {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .type-option {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
        width: 100%;
        border: none;
    }

    .type-option:hover {
        background: #f3f4f6;
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
    }

    .option-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #667eea;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .option-details h5 {
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 4px 0;
    }

    .option-details p {
        font-size: 12px;
        color: #6b7280;
        margin: 0;
    }

    /* Modal Buttons */
    .modal-actions, .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px 24px;
        border-top: 1px solid #e5e7eb;
        background: #fafafa;
        border-radius: 0 0 12px 12px;
    }

    .btn-modal {
        padding: 8px 20px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 80px;
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
    }

    .btn-confirm {
        background: #667eea;
        color: white;
        border: 1px solid #667eea;
    }

    .btn-confirm:hover {
        background: #5a67d8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }

    /* Stock Management Modal Specific Styles */
    .modal-body {
        padding: 24px;
    }

    .info-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .info-item:last-child {
        margin-bottom: 0;
    }

    .info-item label {
        font-size: 11px;
        font-weight: 500;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
        text-align: right;
        max-width: 60%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stock-display {
        display: flex;
        align-items: baseline;
        gap: 4px;
    }

    .stock-display .stock-value {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
    }

    .stock-display .stock-unit {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 16px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .label-text {
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .required {
        color: #ef4444;
        margin-left: 2px;
    }

    .info-badge {
        display: inline-block;
        padding: 2px 8px;
        background: #e0e7ff;
        color: #4f46e5;
        border-radius: 10px;
        font-size: 9px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-left: 6px;
    }

    .section-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }

    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        font-family: inherit;
        transition: all 0.2s;
        background: white;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: white;
    }

    .form-input:disabled, .form-input:read-only {
        background: #f3f4f6;
        color: #6b7280;
        cursor: not-allowed;
        border-color: #e5e7eb;
    }

    .form-input::placeholder, .form-textarea::placeholder {
        color: #9ca3af;
    }

    .form-textarea {
        resize: vertical;
        min-height: 80px;
    }

    /* Variant Selector */
    .variant-selector {
        margin-bottom: 20px;
        display: none;
    }

    .variant-options {
        display: grid;
        gap: 8px;
        max-height: 200px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .variant-options::-webkit-scrollbar {
        width: 6px;
    }

    .variant-options::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .variant-options::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .variant-options::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .variant-card {
        padding: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #f9fafb;
        cursor: pointer;
        transition: all 0.2s;
    }

    .variant-card:hover {
        border-color: #8b5cf6;
        background: #f5f3ff;
        transform: translateX(2px);
    }

    .variant-card.selected {
        border-color: #8b5cf6;
        background: #ede9fe;
        box-shadow: 0 2px 8px rgba(139, 92, 246, 0.1);
    }

    .variant-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 6px;
    }

    .variant-name {
        font-size: 12px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .variant-stock {
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        background: #f3f4f6;
        padding: 2px 8px;
        border-radius: 10px;
    }

    .variant-attributes {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 4px;
        line-height: 1.4;
    }

    .variant-sku {
        font-size: 10px;
        color: #9ca3af;
        font-family: 'Courier New', monospace;
    }

    .no-variants {
        text-align: center;
        padding: 20px;
        color: #9ca3af;
        font-size: 12px;
        background: #f9fafb;
        border-radius: 6px;
        border: 1px dashed #d1d5db;
    }

    /* Calculation Card */
    .calculation-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-top: 20px;
    }

    .calc-header {
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }

    .calc-body {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .calc-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .calc-label {
        font-size: 12px;
        color: #6b7280;
    }

    .calc-value {
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
    }

    .calc-row.total {
        margin-top: 8px;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
    }

    .calc-row.total .calc-label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }

    .calc-row.total .calc-value {
        font-size: 16px;
        font-weight: 700;
        color: #10b981;
    }

    .calc-divider {
        height: 1px;
        background: #e5e7eb;
        margin: 8px 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .products-container {
            padding: 10px;
        }

        .page-header {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .report-cards {
            flex-direction: column;
        }

        .report-card {
            min-width: auto;
            width: 100%;
        }

        .table-filters {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .search-box {
            max-width: 100%;
        }

        .bulk-actions {
            margin-left: 0;
            width: 100%;
            justify-content: flex-end;
        }

        .modal-content {
            margin: 20px;
            width: calc(100% - 40px);
            max-height: calc(100vh - 40px);
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .modal-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
        }
    }

    @media (max-width: 480px) {
        .modal-body {
            padding: 16px;
        }

        .info-card {
            padding: 12px;
        }

        .modal-actions {
            flex-direction: column;
            gap: 8px;
        }

        .btn-modal {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Load product data and calculate summaries
    document.addEventListener('DOMContentLoaded', function() {
        loadReportData();
        setInterval(loadReportData, 30000);
    });

    function loadReportData() {
        // Fetch fresh data from server
        fetch('{{ route("admin.products.report-data") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Format total cost with ₹ symbol
                    const totalCost = '₹' + data.total_cost;

                    // Update first card (Product Total Cost)
                    document.getElementById('totalCostValue').textContent = totalCost;
                    document.getElementById('totalProductsCount').textContent =
                        data.total_products + ' products (' + data.simple_products_count + ' simple, ' + data.variant_items_count + ' variant)';

                    // Update second card (Low Stock Products)
                    document.getElementById('lowStockCount').textContent = data.total_low_stock;
                    document.getElementById('lowStockDetails').textContent =
                        data.simple_low_stock_count + ' simple, ' + data.variant_low_stock_count + ' variant';
                }
            })
            .catch(error => console.error('Error loading report data:', error));
    }

    // Product Type Selection
    function openProductTypeModal() {
        document.getElementById('productTypeModal').style.display = 'flex';
    }

    function closeProductTypeModal() {
        document.getElementById('productTypeModal').style.display = 'none';
    }

    function selectProductType(type) {
        closeProductTypeModal();
        if (type === 'simple') {
            window.location.href = '{{ route("admin.products.create-simple") }}';
        } else if (type === 'variant') {
            window.location.href = '{{ route("admin.products.create") }}';
        }
    }

    // Stock Management
    let currentProductStock = 0;
    let currentProductVariants = [];
    let selectedVariantIndex = -1;
    let selectedVariantData = null;

    async function openStockManagement(productId, productName) {
        const modal = document.getElementById('stockManagementModal');
        const productNameElement = document.getElementById('stockProductName');

        // Set basic info
        document.getElementById('stockProductId').value = productId;
        productNameElement.textContent = productName;

        // Reset form
        const form = document.getElementById('stockManagementForm');
        form.reset();
        document.getElementById('stockQuantity').value = 1;
        document.getElementById('remarks').value = '';

        // ✅ Always set to main warehouse
        const warehouseId = document.getElementById('warehouseId').value;
        if (!warehouseId) {
            alert('Main warehouse not found. Please set a main warehouse first.');
            return;
        }

        // Check if product is simple or variant
        const productRow = document.querySelector(`tr[data-product-id="${productId}"]`);
        const isVariant = productRow.querySelector('.type-variant') !== null;

        document.getElementById('productType').value = isVariant ? 'variant' : 'simple';

        if (isVariant) {
            // Show variant selector and load variants
            document.getElementById('variantSelector').style.display = 'block';
            await loadProductVariants(productId);
        } else {
            // Simple product - hide variant selector
            document.getElementById('variantSelector').style.display = 'none';

            // ✅ Fetch main warehouse stock for simple product
            await fetchSimpleProductStock(productId);

            selectedVariantData = null;
            updateStockCalculation();
        }

        modal.style.display = 'flex';
    }

    // ✅ NEW FUNCTION: Fetch simple product stock from main warehouse
    async function fetchSimpleProductStock(productId) {
        try {
            const response = await fetch(`/admin/products/${productId}/simple-warehouse-stock`);
            const data = await response.json();

            if (data.success) {
                currentProductStock = parseInt(data.main_warehouse_stock) || 0;
                document.getElementById('currentStockValue').textContent = currentProductStock;

                // Set calculation display
                document.getElementById('calcCurrent').textContent = currentProductStock;
                document.getElementById('calcAdjustment').textContent = '+0';
                document.getElementById('calcNew').textContent = currentProductStock;
            } else {
                currentProductStock = 0;
                document.getElementById('currentStockValue').textContent = '0';
                alert('Could not load stock data');
            }
        } catch (error) {
            console.error('Error fetching simple product stock:', error);
            currentProductStock = 0;
            document.getElementById('currentStockValue').textContent = '0';
        }
    }

    async function loadProductVariants(productId) {
        try {
            const variantOptions = document.getElementById('variantOptions');
            variantOptions.innerHTML = '<div class="no-variants">Loading variants...</div>';

            // ✅ NEW: Load variants with main warehouse stock
            const response = await fetch(`/admin/products/${productId}/variants-with-stock`);
            const data = await response.json();

            if (data.success) {
                currentProductVariants = data.variants || [];
                variantOptions.innerHTML = '';

                if (currentProductVariants.length === 0) {
                    variantOptions.innerHTML = '<div class="no-variants">No variants found</div>';
                    return;
                }

                currentProductVariants.forEach((variant, index) => {
                    const variantCard = document.createElement('div');
                    variantCard.className = 'variant-card';
                    variantCard.dataset.index = index;
                    variantCard.onclick = function() {
                        selectVariant(index);
                    };

                    const variantName = variant.name || 'Unnamed Variant';
                    const attributes = variant.attributes || [];
                    const stock = parseInt(variant.current_stock) || 0;
                    const sku = variant.sku_code || 'N/A';

                    let attributesText = '';
                    if (Array.isArray(attributes)) {
                        attributesText = attributes.map(attr =>
                            `${attr.type || ''}: ${attr.value || ''}`
                        ).join(', ');
                    }

                    variantCard.innerHTML = `
                        <div class="variant-header">
                            <div class="variant-name">${variantName}</div>
                            <div class="variant-stock">${stock} units</div>
                        </div>
                        <div class="variant-attributes">${attributesText}</div>
                        <div class="variant-sku">SKU: ${sku}</div>
                    `;

                    variantOptions.appendChild(variantCard);
                });

                // Select first variant by default
                if (currentProductVariants.length > 0) {
                    setTimeout(() => selectVariant(0), 100);
                }
            }
        } catch (error) {
            console.error('Error loading variants:', error);
            document.getElementById('variantOptions').innerHTML =
                '<div class="no-variants">Error loading variants</div>';
        }
    }

    function selectVariant(index) {
        selectedVariantIndex = index;
        selectedVariantData = currentProductVariants[index];

        if (!selectedVariantData) return;

        // Update UI
        document.querySelectorAll('.variant-card').forEach(card => {
            card.classList.remove('selected');
        });

        const selectedCard = document.querySelector(`.variant-card[data-index="${index}"]`);
        if (selectedCard) {
            selectedCard.classList.add('selected');
        }

        // Get stock from variant data
        const currentStock = parseInt(selectedVariantData.current_stock) || 0;
        document.getElementById('currentStockValue').textContent = currentStock;
        document.getElementById('selectedVariantId').value = selectedVariantData._id || index;

        currentProductStock = currentStock;
        updateStockCalculation();
    }

    function updateStockCalculation() {
        // Ensure currentProductStock is always an integer
        const currentStock = parseInt(currentProductStock) || 0;
        const adjustmentType = document.getElementById('stockAdjustmentType').value;
        // Parse quantity as integer
        const quantity = parseInt(document.getElementById('stockQuantity').value) || 0;

        let newStock = currentStock;
        let adjustmentText = '';

        switch(adjustmentType) {
            case 'add':
                // Ensure numeric addition
                newStock = currentStock + quantity;
                adjustmentText = `+${quantity}`;
                document.getElementById('calcAdjustment').style.color = '#10b981';
                break;
            case 'reduce':
                newStock = Math.max(0, currentStock - quantity);
                adjustmentText = `-${quantity}`;
                document.getElementById('calcAdjustment').style.color = '#ef4444';
                break;
        }

        // Update calculation display
        document.getElementById('calcCurrent').textContent = currentStock;
        document.getElementById('calcAdjustment').textContent = adjustmentText;
        document.getElementById('calcNew').textContent = newStock;

        // Update current stock display in info card
        document.getElementById('currentStockValue').textContent = currentStock;
    }

    function closeStockManagementModal() {
        document.getElementById('stockManagementModal').style.display = 'none';
        document.getElementById('stockManagementForm').reset();
        currentProductVariants = [];
        selectedVariantIndex = -1;
        selectedVariantData = null;
        currentProductStock = 0;
        document.getElementById('variantOptions').innerHTML = '';
    }

    // Update form submission to handle both simple and variant products
    document.getElementById('stockManagementForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const productType = document.getElementById('productType').value;

        if (productType === 'variant' && selectedVariantIndex === -1) {
            alert('Please select a variant');
            return;
        }

        // Add variant-specific data if it's a variant product
        if (productType === 'variant' && selectedVariantData) {
            formData.append('variant_id', selectedVariantData._id || selectedVariantIndex);
        }

        const submitBtn = document.getElementById('stockSubmitBtn');
        const originalBtnText = submitBtn.textContent;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Updating...';

        try {
            const response = await fetch('{{ route("admin.products.update-stock") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert('✅ Stock updated successfully in Main Warehouse');
                closeStockManagementModal();
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert('❌ ' + (data.message || 'Failed to update stock'));
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('❌ An error occurred');
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('.table-row');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Filter functionality
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.getAttribute('data-filter');
            const rows = document.querySelectorAll('.table-row');
            rows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else {
                    const status = row.getAttribute('data-status');
                    row.style.display = status === filter ? '' : 'none';
                }
            });
        });
    });

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActionButtons();
    });

    // Individual checkboxes
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(document.querySelectorAll('.row-checkbox')).every(cb => cb.checked);
            document.getElementById('selectAll').checked = allChecked;
            updateBulkActionButtons();
        });
    });

    function updateBulkActionButtons() {
        const anyChecked = Array.from(document.querySelectorAll('.row-checkbox')).some(cb => cb.checked);
        document.getElementById('bulkActionSelect').disabled = !anyChecked;
        document.getElementById('applyBulkAction').disabled = !anyChecked;
    }

    // ✅ FIXED: Bulk Action with product_types array (REMOVED DELETE OPTION)
    let selectedAction = '';
    let selectedProductIds = [];
    let selectedProductTypes = [];

    document.getElementById('applyBulkAction').addEventListener('click', function() {
        const action = document.getElementById('bulkActionSelect').value;
        if (!action) {
            alert('Please select a bulk action');
            return;
        }

        selectedProductIds = [];
        selectedProductTypes = [];

        // ✅ Collect product IDs and types
        document.querySelectorAll('.row-checkbox:checked').forEach(checkbox => {
            const productId = checkbox.value;
            const row = checkbox.closest('tr');
            const productType = row.getAttribute('data-product-type');

            selectedProductIds.push(productId);
            selectedProductTypes.push(productType);
        });

        if (selectedProductIds.length === 0) {
            alert('Please select at least one product');
            return;
        }

        selectedAction = action;
        const modal = document.getElementById('bulkActionModal');
        const modalTitle = document.getElementById('bulkModalTitle');
        const modalText = document.getElementById('bulkModalText');

        const actionText = {
            'active': 'Set as Active',
            'inactive': 'Set as Inactive'
        }[action];

        const actionMessage = {
            'active': `Set ${selectedProductIds.length} product(s) as active?`,
            'inactive': `Set ${selectedProductIds.length} product(s) as inactive?`
        }[action];

        modalTitle.textContent = actionText;
        modalText.textContent = actionMessage;
        modal.style.display = 'flex';
    });

    // ✅ FIXED: Confirm bulk action with proper data (REMOVED DELETE LOGIC)
    document.getElementById('confirmBulkAction').addEventListener('click', async function() {
        if (selectedProductIds.length === 0 || !selectedAction) return;

        const modal = document.getElementById('bulkActionModal');
        const confirmBtn = this;
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Processing...';

        try {
            const url = '/admin/products/bulk-update-status';
            const method = 'POST';

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');

            // ✅ Send both product_ids and product_types arrays
            selectedProductIds.forEach(id => {
                formData.append('product_ids[]', id);
            });

            selectedProductTypes.forEach(type => {
                formData.append('product_types[]', type);
            });

            formData.append('status', selectedAction);

            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Operation failed');
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Confirm';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm';
        }
    });

    // REMOVED: Delete Modal functions

    function closeBulkActionModal() {
        document.getElementById('bulkActionModal').style.display = 'none';
        selectedAction = '';
        selectedProductIds = [];
        selectedProductTypes = [];
        document.getElementById('bulkActionSelect').selectedIndex = 0;
    }

    // Close modals with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeBulkActionModal();
            closeStockManagementModal();
            closeProductTypeModal();
        }
    });
</script>
@endpush
@endsection
