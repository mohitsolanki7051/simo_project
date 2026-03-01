@extends('layouts.admin')

@section('title', 'Products - Admin Panel')
@section('header-title', 'Products Management')

@section('content')
<div class="products-container">
    <!-- Header with Action Buttons -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Product List</h2>
        </div>
        <div class="header-right">
            <button type="button" class="btn-pricing" onclick="openPricingModal()">
                <span>Set Dealer %</span>
            </button>
            <button type="button" class="btn-add" onclick="openProductTypeModal()">
                <span>Add Product</span>
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
                <div class="card-desc" id="totalProductsCount">
                    {{ $totalProducts }} products ({{ $totalSimple }} simple, {{ $totalVariant }} variant)
                </div>
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
            <input type="text"
                   class="search-input"
                   placeholder="Search products by name or SKU..."
                   id="searchInput"
                   value="{{ $search }}"
                   autocomplete="off">
        </div>

        <!-- Product Type Filter Dropdown -->
        <div class="filter-dropdown">
            <select class="type-filter" id="productTypeFilter" onchange="filterByProductType(this.value)">
                <option value="all" {{ request('product_type') == 'all' ? 'selected' : '' }}>All Types</option>
                <option value="simple" {{ request('product_type') == 'simple' ? 'selected' : '' }}>Simple Products</option>
                <option value="variant" {{ request('product_type') == 'variant' ? 'selected' : '' }}>Variant Products</option>
            </select>
        </div>

        <div class="filter-tabs">
            <a href="{{ route('admin.products.index', array_merge(request()->query(), ['status' => 'all', 'product_type' => request('product_type', 'all')])) }}"
               class="filter-tab {{ $status === 'all' ? 'active' : '' }}">
                All ({{ $totalProducts }})
            </a>
            <a href="{{ route('admin.products.index', array_merge(request()->query(), ['status' => 'active', 'product_type' => request('product_type', 'all')])) }}"
               class="filter-tab {{ $status === 'active' ? 'active' : '' }}">
                Active
            </a>
            <a href="{{ route('admin.products.index', array_merge(request()->query(), ['status' => 'inactive', 'product_type' => request('product_type', 'all')])) }}"
               class="filter-tab {{ $status === 'inactive' ? 'active' : '' }}">
                Inactive
            </a>
        </div>

        <div class="bulk-actions">
            <select class="bulk-select" id="bulkActionSelect" disabled>
                <option value="">Bulk Actions</option>
                <option value="active">Set Active</option>
                <option value="inactive">Set Inactive</option>
            </select>
            <button class="btn-bulk" id="applyBulkAction" disabled>Apply</button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="table-wrapper">
        <table class="products-table">
            <thead>
                <tr>
                    <th width="30"><input type="checkbox" id="selectAll"></th>
                    <th width="50">S.No.</th>
                    <th width="60">Image</th>
                    <th>Product Name</th>
                    <th width="80">Type</th>
                    <th width="100">MRP</th>
                    <th width="100">Sale Price</th>
                    <th width="100">Stock</th>
                    <th width="80">Status</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $product)
                <tr class="table-row"
                    data-status="{{ $product->status }}"
                    data-product-id="{{ $product->id }}"
                    data-product-type="{{ $product->product_type }}">
                    <td>
                        <input type="checkbox" class="row-checkbox" value="{{ $product->id }}">
                    </td>
                    <td>{{ $products->firstItem() + $index }}</td>
                    <td>
                        <div class="product-img">
                            <img src="{{ $product->base_image ? asset('storage/' . $product->base_image) : 'https://via.placeholder.com/40x40/667eea/ffffff?text=No+Image' }}"
                                 alt="{{ $product->name }}"
                                 onerror="this.src='https://via.placeholder.com/40x40/667eea/ffffff?text=IMG'">
                        </div>
                    </td>
                    <td>
                        <div class="product-name">{{ $product->name }}</div>
                    </td>
                    <td>
                        @if($product->product_type === 'simple')
                            <span class="type-badge simple">Simple</span>
                        @else
                            <span class="type-badge variant">Variant</span>
                        @endif
                    </td>
                    <td class="price-cell">
                        @if($product->product_type === 'simple')
                            ₹{{ number_format($product->mrp_price, 2) }}
                        @else
                            {{ $product->formatted_mrp_price_range }}
                        @endif
                    </td>
                    <td class="price-cell">
                        @if($product->product_type === 'simple')
                            ₹{{ number_format($product->sale_price, 2) }}
                        @else
                            {{ $product->formatted_price_range }}
                        @endif
                    </td>
                    <td>
                        <div class="stock-info">
                            <span class="stock-value">{{ $product->total_stock }}</span>
                            <span class="stock-unit">{{ $product->unit ?? 'pcs' }}</span>
                        </div>
                        @php
                            $minAlert = 0;
                            if ($product->product_type === 'simple') {
                                $warehouseStock = \App\Models\WarehouseStock::where('product_id', $product->_id)
                                    ->where('product_type', 'simple')
                                    ->first();
                                $minAlert = $warehouseStock ? $warehouseStock->min_stock_alert : 0;
                            } else {
                                $minAlert = $product->variants ? collect($product->variants)->min('min_stock_alert') : 0;
                            }
                        @endphp
                    </td>
                    <td>
                        <span class="status-badge status-{{ $product->status }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-icons">
                            <a href="{{ route('admin.products.show', $product->id) }}"
                               class="action-btn view" title="View">
                                <span>👁️</span>
                            </a>
                            <a href="{{ route('admin.products.edit', $product->id) }}"
                               class="action-btn edit" title="Edit">
                                <span>✏️</span>
                            </a>
                            <button type="button"
                                    class="action-btn stock"
                                    onclick="openStockManagement('{{ $product->id }}', '{{ addslashes($product->name) }}')"
                                    title="Manage Stock">
                                <span>📦</span>
                            </button>
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
                            <button class="btn-add-product" onclick="openProductTypeModal()">
                                + Add Product
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination and Per Page Selector -->
    @if($products->total() > 0)
    <div class="table-footer">
        <div class="pagination-info">
            Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} entries
        </div>

        <div class="pagination-controls">
            <div class="per-page-selector">
                <label for="perPage">Show</label>
                <select id="perPage" onchange="changePerPage(this.value)">
                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $perPage == 200 ? 'selected' : '' }}>200</option>
                    <option value="500" {{ $perPage == 500 ? 'selected' : '' }}>500</option>
                </select>
                <span>entries</span>
            </div>

            <div class="pagination">
                @if($products->onFirstPage())
                    <span class="page-item disabled">‹</span>
                @else
                    <a href="{{ $products->previousPageUrl() }}" class="page-item">‹</a>
                @endif

                @foreach($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}" class="page-item {{ $page == $products->currentPage() ? 'active' : '' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if($products->hasMorePages())
                    <a href="{{ $products->nextPageUrl() }}" class="page-item">›</a>
                @else
                    <span class="page-item disabled">›</span>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Pricing Modal -->
<div class="modal" id="pricingModal">
    <div class="modal-overlay" onclick="closePricingModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon pricing">⚙️</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Price Settings</h4>
                <p class="modal-subtitle">Set dealer and distributor percentages</p>
            </div>
            <button type="button" class="modal-close" onclick="closePricingModal()">×</button>
        </div>

        <form method="POST" action="{{ route('admin.pricing.update') }}" id="pricingForm">
            @csrf
            <div class="modal-body">
                <div class="info-note">
                    <span class="note-icon">ℹ️</span>
                    <span>Prices will be automatically calculated from MRP</span>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Dealer Percentage
                        <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number"
                               step="0.01"
                               name="dealer_percentage"
                               class="form-input"
                               value="{{ old('dealer_percentage', $pricing->dealer_percentage ?? '') }}"
                               min="0"
                               max="100"
                               required>
                        <span class="input-suffix">%</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Distributor Percentage
                        <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number"
                               step="0.01"
                               name="distributor_percentage"
                               class="form-input"
                               value="{{ old('distributor_percentage', $pricing->distributor_percentage ?? '') }}"
                               min="0"
                               max="100"
                               required>
                        <span class="input-suffix">%</span>
                    </div>
                </div>

                <div class="preview-box">
                    <div class="preview-title">Preview Calculation</div>
                    <div class="preview-row">
                        <span class="preview-label">Dealer Price =</span>
                        <span class="preview-value">MRP × <span id="dealerPreview">{{ $pricing->dealer_percentage ?? '—' }}</span>%</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">Distributor Price =</span>
                        <span class="preview-value">MRP × <span id="distributorPreview">{{ $pricing->distributor_percentage ?? '—' }}</span>%</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closePricingModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Settings</button>
            </div>
        </form>
    </div>
</div>

<!-- Product Type Modal -->
<div class="modal" id="productTypeModal">
    <div class="modal-overlay" onclick="closeProductTypeModal()"></div>
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <div class="modal-icon add">📦</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Add Product</h4>
                <p class="modal-subtitle">Choose product type</p>
            </div>
            <button type="button" class="modal-close" onclick="closeProductTypeModal()">×</button>
        </div>

        <div class="modal-body">
            <div class="type-options">
                <button type="button" class="type-option" onclick="selectProductType('simple')">
                    <div class="option-icon simple">📦</div>
                    <div class="option-content">
                        <h5>Simple Product</h5>
                        <p>Single product without variations</p>
                    </div>
                </button>

                <button type="button" class="type-option" onclick="selectProductType('variant')">
                    <div class="option-icon variant">🎨</div>
                    <div class="option-content">
                        <h5>Variant Product</h5>
                        <p>Product with multiple variations</p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal" id="bulkActionModal">
    <div class="modal-overlay" onclick="closeBulkActionModal()"></div>
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <div class="modal-icon warning" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">⚠️</div>
            <div class="modal-title-section">
                <h4 class="modal-title" id="bulkModalTitle">Confirm Bulk Action</h4>
                <p class="modal-subtitle" id="bulkModalText">Update selected products</p>
            </div>
            <button type="button" class="modal-close" onclick="closeBulkActionModal()">×</button>
        </div>

        <div class="modal-body">
            <div class="bulk-info">
                <div class="bulk-icon">📋</div>
                <div class="bulk-details" id="bulkDetails"></div>
            </div>
            <div class="bulk-summary" id="bulkSummary"></div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeBulkActionModal()">Cancel</button>
            <button type="button" class="btn-confirm" id="confirmBulkAction" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">Confirm Update</button>
        </div>
    </div>
</div>

<!-- Stock Management Modal - Clean like Dealer Modal -->
<div class="modal" id="stockManagementModal">
    <div class="modal-overlay" onclick="closeStockManagementModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon stock">📦</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Manage Stock</h4>
                <p class="modal-subtitle">Adjust inventory in Main Warehouse</p>
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

                <!-- Product Info - Simple note like dealer modal -->
                <div class="info-note">
                    <span class="note-icon">📦</span>
                    <span><strong id="stockProductName">Loading...</strong> - {{ $defaultWarehouse->name ?? 'Main Warehouse' }}</span>
                </div>

                <!-- Current Stock Display - Clean card -->
                <div class="stock-display-simple">
                    <div class="stock-label">Current Stock</div>
                    <div class="stock-value-simple" id="currentStockValue">0</div>
                </div>

                <!-- Variant Selector -->
                <div class="variant-selector" id="variantSelector" style="display: none;">
                    <label class="form-label">Select Variant <span class="required">*</span></label>
                    <div class="variant-options" id="variantOptions">
                        <div class="loading-message">Loading variants...</div>
                    </div>
                </div>

                <!-- Adjustment Form -->
                <div class="form-group">
                    <label class="form-label">
                        Adjustment Type
                        <span class="required">*</span>
                    </label>
                    <select class="form-select" name="adjustment_type" id="stockAdjustmentType" onchange="updateStockCalculation()">
                        <option value="add">➕ Add Stock</option>
                        <option value="reduce">➖ Reduce Stock</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Quantity
                        <span class="required">*</span>
                    </label>
                    <input type="number"
                           class="form-input"
                           name="quantity"
                           id="stockQuantity"
                           value="1"
                           min="1"
                           step="1"
                           oninput="updateStockCalculation()">
                </div>

                <div class="form-group">
                    <label class="form-label">Remarks (Optional)</label>
                    <textarea class="form-textarea"
                              name="remarks"
                              id="remarks"
                              rows="2"
                              placeholder="Enter reason for adjustment..."></textarea>
                </div>

                <!-- Calculation Preview -->
                <div class="preview-box">
                    <div class="preview-title">Stock Calculation</div>
                    <div class="preview-row">
                        <span class="preview-label">Current Stock:</span>
                        <span class="preview-value" id="calcCurrent">0</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">Adjustment:</span>
                        <span class="preview-value adjustment" id="calcAdjustment">+0</span>
                    </div>
                    <div class="preview-divider"></div>
                    <div class="preview-row total">
                        <span class="preview-label">New Stock:</span>
                        <span class="preview-value new-stock" id="calcNew">0</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeStockManagementModal()">Cancel</button>
                <button type="submit" class="btn-save" id="stockSubmitBtn">Update Stock</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    /* Modern ERP Style CSS */
    .products-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        background: #f8fafc;
        min-height: 100vh;
    }

    /* Header Styles */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding: 0px 5px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .page-title {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn-pricing, .btn-add {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        color: white;
    }

    .btn-pricing {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .btn-pricing:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
    }

    .btn-add {
        background: linear-gradient(135deg, #fa8427 0%, #e97317 100%);
    }

    .btn-add:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(250, 132, 39, 0.3);
    }

    /* Report Cards */
    .report-cards {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 15px;
    }

    .report-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.2s;
    }

    .report-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.05);
    }

    .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        flex-shrink: 0;
    }

    .card-info {
        flex: 1;
    }

    .card-title {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
    }

    .card-value {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
        line-height: 1.2;
    }

    .card-desc {
        font-size: 11px;
        color: #94a3b8;
    }

    /* Filters Section */
    .table-filters {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 5px;
        background: white;
        padding: 10px 15px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .search-box {
        flex: 1;
        max-width: 300px;
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
    }

    .search-input {
        width: 100%;
        padding: 8px 12px 8px 36px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        transition: all 0.2s;
        background: #f8fafc;
    }

    .search-input:focus {
        outline: none;
        border-color: #8b5cf6;
        background: white;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    .filter-dropdown {
        min-width: 140px;
    }

    .type-filter {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        color: #1e293b;
        background: white;
        cursor: pointer;
    }

    .type-filter:focus {
        outline: none;
        border-color: #8b5cf6;
    }

    .filter-tabs {
        display: flex;
        gap: 4px;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 8px;
    }

    .filter-tab {
        padding: 6px 16px;
        font-size: 13px;
        color: #64748b;
        text-decoration: none;
        border-radius: 6px;
        transition: all 0.2s;
        font-weight: 500;
    }

    .filter-tab:hover {
        color: #334155;
    }

    .filter-tab.active {
        background: white;
        color: #8b5cf6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .bulk-actions {
        display: flex;
        gap: 8px;
        margin-left: auto;
    }

    .bulk-select {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        color: #1e293b;
        background: white;
        min-width: 130px;
        cursor: pointer;
    }

    .bulk-select:focus {
        outline: none;
        border-color: #8b5cf6;
    }

    .btn-bulk {
        padding: 8px 16px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 500;
    }

    .btn-bulk:hover:not(:disabled) {
        background: #e2e8f0;
    }

    .btn-bulk:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Table Styles */
    .table-wrapper {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        overflow-x: auto;
        margin-bottom: 15px;
    }

    .products-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .products-table th {
        background: #f8fafc;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        color: #475569;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .products-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .products-table tr:hover td {
        background: #f8fafc;
    }

    .product-img {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .product-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-name {
        font-weight: 500;
        color: #1e293b;
        font-size: 13px;
    }

    .type-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .type-badge.simple {
        background: #dbeafe;
        color: #1e40af;
    }

    .type-badge.variant {
        background: #f3e8ff;
        color: #6b21a8;
    }

    .price-cell {
        font-weight: 500;
        color: #1e293b;
        font-size: 13px;
    }

    .stock-info {
        display: flex;
        align-items: baseline;
        gap: 4px;
    }

    .stock-value {
        font-weight: 600;
        color: #1e293b;
        font-size: 13px;
    }

    .stock-unit {
        font-size: 11px;
        color: #94a3b8;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #f1f5f9;
        color: #64748b;
    }

    .action-icons {
        display: flex;
        gap: 6px;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .action-btn.view {
        background: #f1f5f9;
        color: #475569;
    }

    .action-btn.edit {
        background: #dbeafe;
        color: #2563eb;
    }

    .action-btn.stock {
        background: #ede9fe;
        color: #7c3aed;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    /* Pagination Footer */
    .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 12px 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .pagination-info {
        font-size: 13px;
        color: #64748b;
    }

    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .per-page-selector {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #475569;
    }

    .per-page-selector select {
        padding: 6px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        color: #1e293b;
        background: white;
        cursor: pointer;
    }

    .pagination {
        display: flex;
        gap: 4px;
    }

    .page-item {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 6px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s;
        font-weight: 500;
    }

    .page-item.active {
        background: #8b5cf6;
        border-color: #8b5cf6;
        color: white;
    }

    .page-item.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-content {
        max-width: 300px;
        margin: 0 auto;
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.3;
    }

    .empty-content h4 {
        font-size: 16px;
        color: #334155;
        margin-bottom: 8px;
    }

    .empty-content p {
        font-size: 13px;
        color: #94a3b8;
        margin-bottom: 20px;
    }

    .btn-add-product {
        padding: 10px 20px;
        background: #8b5cf6;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-add-product:hover {
        background: #7c3aed;
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
        backdrop-filter: blur(4px);
    }

    .modal-content {
        position: relative;
        background: white;
        width: 90%;
        max-width: 450px;
        max-height: 90vh;
        overflow-y: auto;
        animation: modalFadeIn 0.2s ease;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        border-radius: 12px;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    .modal-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px 12px 0 0;
    }

    .modal-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
    }

    .modal-icon.pricing {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .modal-icon.add {
        background: linear-gradient(135deg, #fa8427 0%, #e97317 100%);
    }

    .modal-icon.warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .modal-icon.stock {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .modal-title-section {
        flex: 1;
    }

    .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 2px 0;
    }

    .modal-subtitle {
        font-size: 12px;
        color: #64748b;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        color: #94a3b8;
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: #f1f5f9;
        color: #334155;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 20px;
        border-top: 1px solid #e2e8f0;
        background: #fafafa;
        border-radius: 0 0 12px 12px;
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #334155;
        margin-bottom: 6px;
    }

    .required {
        color: #ef4444;
        margin-left: 2px;
    }

    .input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        transition: all 0.2s;
        background: white;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #8b5cf6;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    .input-suffix {
        position: absolute;
        right: 12px;
        color: #64748b;
        font-weight: 500;
        font-size: 13px;
    }

    .info-note {
        background: #eff6ff;
        border: 1px solid #dbeafe;
        border-radius: 6px;
        padding: 10px 12px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #1e40af;
    }

    .note-icon {
        font-size: 14px;
    }

    .preview-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 12px;
        margin-top: 16px;
    }

    .preview-title {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .preview-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
        font-size: 13px;
    }

    .preview-row.total {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid #e2e8f0;
        font-weight: 600;
    }

    .preview-label {
        color: #64748b;
    }

    .preview-value {
        font-weight: 600;
        color: #1e293b;
    }

    .preview-value.adjustment {
        color: #10b981;
    }

    .preview-value.new-stock {
        color: #8b5cf6;
        font-size: 16px;
    }

    .preview-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 8px 0;
    }

    /* Stock Management Specific */
    .stock-display-simple {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 15px;
        text-align: center;
        margin-bottom: 20px;
    }

    .stock-display-simple .stock-label {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
    }

    .stock-display-simple .stock-value-simple {
        font-size: 28px;
        font-weight: 700;
        color: #8b5cf6;
        line-height: 1.2;
    }

    .variant-selector {
        margin-bottom: 16px;
    }

    .variant-options {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        max-height: 180px;
        overflow-y: auto;
        background: white;
    }

    .variant-card {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 12px;
    }

    .variant-card:last-child {
        border-bottom: none;
    }

    .variant-card:hover {
        background: #f8fafc;
    }

    .variant-card.selected {
        background: #f3e8ff;
        border-left: 3px solid #8b5cf6;
    }

    .loading-message {
        padding: 15px;
        text-align: center;
        color: #94a3b8;
        font-size: 12px;
    }

    /* Buttons */
    .btn-cancel, .btn-save, .btn-confirm {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-cancel {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
    }

    .btn-save {
        background: #f98824;
        color: white;
    }


    .btn-confirm {
        background: #f59e0b;
        color: white;
    }

    .btn-confirm:hover {
        background: #d97706;
    }

    /* Bulk Action Styles */
    .bulk-info {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #f8fafc;
        border-radius: 6px;
        margin-bottom: 12px;
    }

    .bulk-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
    }

    .bulk-details h4 {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 2px 0;
    }

    .bulk-details p {
        font-size: 12px;
        color: #64748b;
        margin: 0;
    }

    .bulk-summary {
        background: #f1f5f9;
        border-radius: 6px;
        padding: 12px;
        font-size: 13px;
        color: #334155;
        border-left: 3px solid #f59e0b;
    }

    /* Type Options */
    .type-options {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .type-option {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
        text-align: left;
    }

    .type-option:hover {
        border-color: #8b5cf6;
        background: #f8fafc;
    }

    .option-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
    }

    .option-icon.simple {
        background: linear-gradient(135deg, #fa8427 0%, #e97317 100%);
    }

    .option-icon.variant {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .option-content h5 {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 2px 0;
    }

    .option-content p {
        font-size: 12px;
        color: #64748b;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 10px;
        }

        .header-right {
            width: 100%;
            flex-direction: column;
        }

        .btn-pricing, .btn-add {
            width: 100%;
            justify-content: center;
        }

        .report-cards {
            grid-template-columns: 1fr;
        }

        .table-filters {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            max-width: 100%;
        }

        .filter-tabs {
            justify-content: center;
        }

        .bulk-actions {
            margin-left: 0;
            width: 100%;
        }

        .table-footer {
            flex-direction: column;
            gap: 12px;
        }

        .pagination-controls {
            flex-direction: column;
            gap: 12px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Load report data
    document.addEventListener('DOMContentLoaded', function() {
        loadReportData();
        setInterval(loadReportData, 30000);
    });

    function loadReportData() {
        fetch('{{ route("admin.products.report-data") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('totalCostValue').textContent = '₹' + data.total_cost;
                    document.getElementById('lowStockCount').textContent = data.total_low_stock;
                    document.getElementById('lowStockDetails').textContent =
                        data.simple_low_stock_count + ' simple, ' + data.variant_low_stock_count + ' variant';
                }
            })
            .catch(error => console.error('Error loading report data:', error));
    }

    // Product Type Filter
    function filterByProductType(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('product_type', value);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    // Pagination and Per Page
    function changePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    // Search with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const url = new URL(window.location.href);
            if (e.target.value) {
                url.searchParams.set('search', e.target.value);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }, 500);
    });

    // Modal Functions
    function openPricingModal() {
        document.getElementById('pricingModal').style.display = 'flex';

        const dealerInput = document.querySelector('input[name="dealer_percentage"]');
        const distributorInput = document.querySelector('input[name="distributor_percentage"]');

        if (!dealerInput.value) {
            document.getElementById('dealerPreview').textContent = '—';
        }
        if (!distributorInput.value) {
            document.getElementById('distributorPreview').textContent = '—';
        }
    }

    function closePricingModal() {
        document.getElementById('pricingModal').style.display = 'none';
    }

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
        } else {
            window.location.href = '{{ route("admin.products.create") }}';
        }
    }

    // Bulk Actions
    let selectedAction = '';
    let selectedProductIds = [];
    let selectedProductTypes = [];

    document.getElementById('applyBulkAction').addEventListener('click', function() {
        const action = document.getElementById('bulkActionSelect').value;
        if (!action) return;

        selectedProductIds = [];
        selectedProductTypes = [];

        document.querySelectorAll('.row-checkbox:checked').forEach(checkbox => {
            const row = checkbox.closest('tr');
            selectedProductIds.push(checkbox.value);
            selectedProductTypes.push(row.dataset.productType);
        });

        if (selectedProductIds.length === 0) return;

        selectedAction = action;

        const actionText = action === 'active' ? 'Activate' : 'Deactivate';
        document.getElementById('bulkModalTitle').textContent = `Bulk ${actionText} Products`;
        document.getElementById('bulkModalText').textContent = `You are about to ${actionText.toLowerCase()} ${selectedProductIds.length} product(s)`;

        const simpleCount = selectedProductTypes.filter(t => t === 'simple').length;
        const variantCount = selectedProductTypes.filter(t => t === 'variant').length;

        document.getElementById('bulkDetails').innerHTML = `
            <h4>${selectedProductIds.length} Products Selected</h4>
            <p>${simpleCount} Simple, ${variantCount} Variant</p>
        `;

        document.getElementById('bulkSummary').innerHTML = `
            <strong>Action:</strong> Set as ${action === 'active' ? 'Active' : 'Inactive'}<br>
            <strong>Products:</strong> ${selectedProductIds.length} items
        `;

        document.getElementById('bulkActionModal').style.display = 'flex';
    });

    document.getElementById('confirmBulkAction').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Processing...';

        try {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('status', selectedAction);

            selectedProductIds.forEach(id => formData.append('product_ids[]', id));
            selectedProductTypes.forEach(type => formData.append('product_types[]', type));

            const response = await fetch('{{ route("admin.products.bulk-update-status") }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Operation failed');
                closeBulkActionModal();
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
            closeBulkActionModal();
        } finally {
            btn.disabled = false;
            btn.textContent = 'Confirm Update';
        }
    });

    function closeBulkActionModal() {
        document.getElementById('bulkActionModal').style.display = 'none';
        selectedAction = '';
        selectedProductIds = [];
        selectedProductTypes = [];
    }

    // Select All Checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
        updateBulkActionButtons();
    });

    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkActionButtons);
    });

    function updateBulkActionButtons() {
        const anyChecked = document.querySelectorAll('.row-checkbox:checked').length > 0;
        document.getElementById('bulkActionSelect').disabled = !anyChecked;
        document.getElementById('applyBulkAction').disabled = !anyChecked;
    }

    // Stock Management
    let currentProductStock = 0;
    let currentProductVariants = [];
    let selectedVariantIndex = -1;
    let selectedVariantData = null;

    async function openStockManagement(productId, productName) {
        document.getElementById('stockProductId').value = productId;
        document.getElementById('stockProductName').textContent = productName;

        document.getElementById('stockManagementForm').reset();
        document.getElementById('stockQuantity').value = 1;

        const warehouseId = document.getElementById('warehouseId').value;
        if (!warehouseId) {
            alert('Main warehouse not found');
            return;
        }

        const row = document.querySelector(`tr[data-product-id="${productId}"]`);
        const isVariant = row.querySelector('.type-badge.variant') !== null;
        document.getElementById('productType').value = isVariant ? 'variant' : 'simple';

        if (isVariant) {
            document.getElementById('variantSelector').style.display = 'block';
            await loadProductVariants(productId);
        } else {
            document.getElementById('variantSelector').style.display = 'none';
            await fetchSimpleProductStock(productId);
        }

        document.getElementById('stockManagementModal').style.display = 'flex';
    }

    async function fetchSimpleProductStock(productId) {
        try {
            const response = await fetch(`/admin/products/${productId}/simple-warehouse-stock`);
            const data = await response.json();

            currentProductStock = data.success ? parseInt(data.main_warehouse_stock) || 0 : 0;
            document.getElementById('currentStockValue').textContent = currentProductStock;
            updateStockCalculation();
        } catch (error) {
            console.error('Error fetching stock:', error);
            currentProductStock = 0;
            document.getElementById('currentStockValue').textContent = '0';
        }
    }

    async function loadProductVariants(productId) {
        try {
            const options = document.getElementById('variantOptions');
            options.innerHTML = '<div class="loading-message">Loading variants...</div>';

            const response = await fetch(`/admin/products/${productId}/variants-with-stock`);
            const data = await response.json();

            if (data.success) {
                currentProductVariants = data.variants || [];
                options.innerHTML = '';

                if (currentProductVariants.length === 0) {
                    options.innerHTML = '<div class="loading-message">No variants found</div>';
                    return;
                }

                currentProductVariants.forEach((variant, index) => {
                    const card = document.createElement('div');
                    card.className = 'variant-card';
                    card.dataset.index = index;
                    card.onclick = () => selectVariant(index);

                    const attrs = variant.attributes || [];
                    const attrText = Array.isArray(attrs)
                        ? attrs.map(a => `${a.type || ''}: ${a.value || ''}`).join(', ')
                        : '';

                    card.innerHTML = `
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <strong>${variant.name || 'Unnamed'}</strong>
                            <span style="background: #e2e8f0; padding: 2px 6px; border-radius: 10px;">${variant.current_stock || 0}</span>
                        </div>
                        <div style="font-size: 11px; color: #64748b;">${attrText}</div>
                        <div style="font-size: 10px; color: #94a3b8;">SKU: ${variant.sku_code || 'N/A'}</div>
                    `;

                    options.appendChild(card);
                });

                if (currentProductVariants.length > 0) {
                    setTimeout(() => selectVariant(0), 100);
                }
            }
        } catch (error) {
            console.error('Error loading variants:', error);
            document.getElementById('variantOptions').innerHTML = '<div class="loading-message">Error loading variants</div>';
        }
    }

    function selectVariant(index) {
        selectedVariantIndex = index;
        selectedVariantData = currentProductVariants[index];

        if (!selectedVariantData) return;

        document.querySelectorAll('.variant-card').forEach(c => c.classList.remove('selected'));
        const card = document.querySelector(`.variant-card[data-index="${index}"]`);
        if (card) card.classList.add('selected');

        currentProductStock = parseInt(selectedVariantData.current_stock) || 0;
        document.getElementById('currentStockValue').textContent = currentProductStock;
        document.getElementById('selectedVariantId').value = selectedVariantData._id || index;

        updateStockCalculation();
    }

    function updateStockCalculation() {
        const current = parseInt(currentProductStock) || 0;
        const type = document.getElementById('stockAdjustmentType').value;
        const qty = parseInt(document.getElementById('stockQuantity').value) || 0;

        let newStock = current;
        let adjText = '';

        if (type === 'add') {
            newStock = current + qty;
            adjText = `+${qty}`;
            document.getElementById('calcAdjustment').style.color = '#10b981';
        } else {
            newStock = Math.max(0, current - qty);
            adjText = `-${qty}`;
            document.getElementById('calcAdjustment').style.color = '#ef4444';
        }

        document.getElementById('calcCurrent').textContent = current;
        document.getElementById('calcAdjustment').textContent = adjText;
        document.getElementById('calcNew').textContent = newStock;
    }

    function closeStockManagementModal() {
        document.getElementById('stockManagementModal').style.display = 'none';
        currentProductVariants = [];
        selectedVariantIndex = -1;
        selectedVariantData = null;
        currentProductStock = 0;
    }

    // Form submission
    document.getElementById('stockManagementForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const type = document.getElementById('productType').value;
        if (type === 'variant' && selectedVariantIndex === -1) {
            alert('Please select a variant');
            return;
        }

        const btn = document.querySelector('#stockManagementForm .btn-save');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Updating...';

        const formData = new FormData(this);
        if (type === 'variant' && selectedVariantData) {
            formData.append('variant_id', selectedVariantData._id || selectedVariantIndex);
        }

        try {
            const response = await fetch('{{ route("admin.products.update-stock") }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();
            if (data.success) {
                alert('Stock updated successfully');
                closeStockManagementModal();
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert(data.message || 'Failed to update stock');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    // Close modals with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closePricingModal();
            closeProductTypeModal();
            closeBulkActionModal();
            closeStockManagementModal();
        }
    });
</script>
@endpush
@endsection
