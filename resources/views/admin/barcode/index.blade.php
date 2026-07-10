@extends('layouts.admin')

@section('title', 'Print Barcode')
@section('header-title', 'Print Barcode')

@section('content')
<div class="barcode-print-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Main Warehouse Info -->
    <div class="warehouse-info">
        <div class="warehouse-card">
            <div class="warehouse-icon">🏭</div>
            <div class="warehouse-details">
                <h4>Main Warehouse</h4>
                @if(isset($mainWarehouse))
                    <p><strong>{{ $mainWarehouse->name }}</strong> ({{ $mainWarehouse->code }})</p>
                    <p class="warehouse-hint">Searching products only from this warehouse</p>
                @else
                    <p class="error-text">No main warehouse found. Please set a main warehouse first.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="barcode-content">
        <!-- Left Column - Product Selection -->
        <div class="selection-column">
            <!-- Product Search -->
            <div class="search-section">
                <div class="section-header">
                    <h3>Search Product</h3>
                    @if(isset($mainWarehouse))
                        <span class="warehouse-badge">{{ $mainWarehouse->name }}</span>
                    @endif
                </div>
                <div class="search-box">
                    <input type="text" class="search-input" id="productSearch"
                           placeholder="@if(isset($mainWarehouse))Search products... @else No main warehouse found @endif"
                           @if(!isset($mainWarehouse)) disabled @endif>
                    <button class="search-button" id="searchBtn" @if(!isset($mainWarehouse)) disabled @endif>
                        <span class="search-icon">🔍</span>
                    </button>
                </div>
                <p class="search-hint">Search by product name, SKU code, or barcode</p>

                <!-- Search Results -->
                <div class="search-results" id="searchResults"></div>
            </div>

            <!-- Selected Products -->
            <div class="selected-section">
                <div class="section-header">
                    <h3>Selected Products</h3>
                    <span class="count-badge" id="selectedCount">0</span>
                </div>
                <div class="selected-products" id="selectedProductsList">
                    <div class="empty-state">
                        <div class="empty-icon">📦</div>
                        <p>No products selected</p>
                        <p class="empty-hint">Search and add products to print barcodes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Print Settings -->
        <div class="settings-column">
            <!-- Print Settings -->
            <div class="settings-section">
                <div class="section-header">
                    <h3>Print Settings</h3>
                </div>
                <div class="settings-options">
                    <div class="setting-group">
                        <label>Barcode Size</label>
                        <select class="setting-select" id="barcodeSize">
                            <option value="small">Small</option>
                            <option value="medium" selected>Medium</option>
                            <option value="large">Large</option>
                        </select>
                    </div>

                    <div class="setting-group toggle-group">
                        <label>Show Product Name</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="showProductName" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-group toggle-group">
                        <label>Show Price</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="showPrice" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-group toggle-group">
                        <label>Show SKU</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="showSKU" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="preview-section">
                <div class="section-header">
                    <h3>Preview</h3>
                </div>
                <div class="barcode-preview" id="barcodePreview">
                    <div class="preview-placeholder">
                        <div class="preview-icon">🏷️</div>
                        <p>Preview will appear here</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-section">
                <button class="action-button reset-button" id="resetBtn">
                    <span class="button-icon">🔄</span> Reset
                </button>
                <button class="action-button print-button" id="printBtn" disabled>
                    <span class="button-icon">🖨️</span> Print Barcodes
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .barcode-print-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Alert Styles */
    #alertContainer {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 1000;
        max-width: 350px;
    }
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }
    .alert-icon {
        font-size: 18px;
    }

    /* Warehouse Info */
    .warehouse-info {
        margin-bottom: 20px;
    }
    .warehouse-card {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .warehouse-icon {
        font-size: 24px;
        color: #0284c7;
    }
    .warehouse-details h4 {
        margin: 0 0 5px 0;
        color: #0369a1;
        font-size: 16px;
        font-weight: 600;
    }
    .warehouse-details p {
        margin: 3px 0;
        font-size: 14px;
        color: #475569;
    }
    .warehouse-hint {
        font-size: 12px;
        color: #64748b;
    }
    .error-text {
        color: #dc2626;
        font-weight: 500;
    }

    /* Main Content Layout */
    .barcode-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 1024px) {
        .barcode-content {
            grid-template-columns: 1fr;
        }
    }

    /* Sections */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }
    .section-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
    }

    /* Badges */
    .warehouse-badge {
        background: #0284c7;
        color: white;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .count-badge {
        background: #f97316;
        color: white;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    /* Search Section */
    .search-section {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 20px;
    }
    .search-box {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }
    .search-input {
        flex: 1;
        padding: 10px 14px;
        border: 2px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    .search-input:focus {
        outline: none;
        border-color: #3b82f6;
    }
    .search-button {
        padding: 10px 16px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .search-button:hover:not(:disabled) {
        background: #2563eb;
    }
    .search-button:disabled {
        background: #94a3b8;
        cursor: not-allowed;
    }
    .search-icon {
        font-size: 16px;
    }
    .search-hint {
        font-size: 12px;
        color: #64748b;
        margin: 5px 0 0 0;
    }

    /* Search Results */
    .search-results {
        max-height: 300px;
        overflow-y: auto;
        margin-top: 15px;
        display: none;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
    }
    .search-results.show {
        display: block;
    }
    .search-result-item {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .search-result-item:last-child {
        border-bottom: none;
    }
    .search-result-item:hover {
        background: #f8fafc;
    }
    .result-image {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        background: #f1f5f9;
    }
    .result-info {
        flex: 1;
    }
    .result-name {
        font-weight: 500;
        color: #1e293b;
        font-size: 14px;
        margin-bottom: 3px;
    }
    .result-sku {
        font-size: 12px;
        color: #64748b;
    }
    .product-type {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 500;
        margin-left: 8px;
    }
    .simple-type {
        background: #0d9488;
        color: white;
    }
    .variant-type {
        background: #7c3aed;
        color: white;
    }

    /* Selected Section */
    .selected-section {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 20px;
    }
    .selected-products {
        max-height: 350px;
        overflow-y: auto;
    }
    .empty-state {
        text-align: center;
        padding: 30px 20px;
    }
    .empty-icon {
        font-size: 32px;
        margin-bottom: 10px;
        color: #94a3b8;
    }
    .empty-state p {
        color: #64748b;
        margin: 5px 0;
        font-size: 14px;
    }
    .empty-hint {
        font-size: 12px;
        color: #94a3b8;
    }

    /* Product Item */
    .product-item {
        background: #f8fafc;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 10px;
        border: 1px solid #e2e8f0;
    }
    .product-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }
    .product-info {
        display: flex;
        gap: 10px;
        flex: 1;
    }
    .product-image {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        background: #e2e8f0;
    }
    .product-details {
        flex: 1;
    }
    .product-name {
        font-weight: 500;
        color: #1e293b;
        font-size: 14px;
        margin-bottom: 3px;
    }
    .product-sku {
        font-size: 12px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .sku-code {
        background: #f97316;
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
    }
    .btn-remove {
        background: #ef4444;
        color: white;
        border: none;
        padding: 4px 8px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        transition: background 0.2s;
    }
    .btn-remove:hover {
        background: #dc2626;
    }

    .product-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .quantity-label {
        font-size: 13px;
        color: #475569;
        font-weight: 500;
    }
    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .quantity-btn {
        width: 28px;
        height: 28px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: background 0.2s;
    }
    .quantity-btn:hover {
        background: #2563eb;
    }
    .quantity-input {
        width: 60px;
        padding: 6px;
        text-align: center;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        color: #1e293b;
    }

    /* Settings Column */
    .settings-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Settings Section */
    .settings-section {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px;
    }
    .settings-options {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .setting-group {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .setting-group label {
        font-size: 14px;
        color: #475569;
        font-weight: 500;
    }
    .setting-select {
        width: 150px;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 14px;
        background: white;
    }
    .setting-select:focus {
        outline: none;
        border-color: #3b82f6;
    }

    /* Toggle Switch */
    .toggle-group {
        margin-bottom: 5px;
    }
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #cbd5e1;
        border-radius: 24px;
        transition: .3s;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: .3s;
    }
    input:checked + .toggle-slider {
        background: #10b981;
    }
    input:checked + .toggle-slider:before {
        transform: translateX(20px);
    }

    /* Preview Section */
    .preview-section {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 20px;
    }
    .barcode-preview {
        min-height: 200px;
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .preview-placeholder {
        text-align: center;
    }
    .preview-icon {
        font-size: 32px;
        margin-bottom: 10px;
        color: #94a3b8;
    }
    .preview-placeholder p {
        color: #64748b;
        font-size: 14px;
        margin: 0;
    }

    /* Action Buttons */
    .action-section {
        display: flex;
        gap: 12px;
    }
    .action-button {
        flex: 1;
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .reset-button {
        background: #f1f5f9;
        color: #475569;
    }
    .reset-button:hover {
        background: #e2e8f0;
    }
    .print-button {
        background: #10b981;
        color: white;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    }
    .print-button:hover:not(:disabled) {
        background: #0da271;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
    }
    .print-button:disabled {
        background: #94a3b8;
        cursor: not-allowed;
        box-shadow: none;
    }
    .button-icon {
        font-size: 16px;
    }

    @media (max-width: 768px) {
        .barcode-print-container {
            padding: 10px;
        }
        .warehouse-card {
            padding: 12px;
            gap: 12px;
        }
        .warehouse-details h4 {
            font-size: 14px;
        }
        .warehouse-details p {
            font-size: 12px;
        }
        .selection-column, .settings-column {
            gap: 15px;
        }
        .search-section, .selected-section, .settings-section, .preview-section {
            padding: 12px;
        }
        .product-item {
            padding: 10px;
        }
        .product-name {
            font-size: 13px;
        }
        .sku-code {
            font-size: 10px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    let selectedProducts = [];
    let searchTimeout;
    let currentWarehouseId = @if(isset($mainWarehouse)) '{{ $mainWarehouse->id }}' @else null @endif;
    let currentWarehouseName = @if(isset($mainWarehouse)) '{{ $mainWarehouse->name }}' @else '' @endif;

    // Show alert function
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <span class="alert-icon">${type === 'success' ? '✓' : '✕'}</span>
            <span>${message}</span>
        `;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 4000);
    }

    // Search products
    function searchProducts(search) {
        const searchResults = document.getElementById('searchResults');
        searchResults.innerHTML = '<div style="padding: 20px; text-align: center; color: #64748b;">Searching...</div>';
        searchResults.classList.add('show');

        fetch('/admin/barcode/search-products?' + new URLSearchParams({
            search: search
        }), {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchResults(data.products, data.warehouse_name);
            } else {
                showAlert(data.message || 'Search failed', 'error');
                searchResults.classList.remove('show');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            showAlert('Search failed', 'error');
            searchResults.classList.remove('show');
        });
    }

    // Display search results - FIXED JSON STRINGIFY ISSUE
    function displaySearchResults(products, warehouseName) {
        const container = document.getElementById('searchResults');

        if (!products || products.length === 0) {
            container.innerHTML = `
                <div style="padding: 20px; text-align: center; color: #64748b;">
                    No products found in ${warehouseName}
                </div>
            `;
            container.classList.add('show');
            return;
        }

        container.innerHTML = products.map(product => {
            const typeClass = product.model_type === 'simple' ? 'simple-type' : 'variant-type';
            const typeText = product.model_type === 'simple' ? 'Simple' : 'Variant';

            // FIX: Properly escape the product object
            const productJson = JSON.stringify(product)
                .replace(/"/g, '&quot;')
                .replace(/'/g, "&#39;");

            return `
                <div class="search-result-item" onclick="addProduct('${productJson}')">
                    <img src="${product.base_image || '/placeholder.png'}" class="result-image" alt="${product.name}"
                         onerror="this.src='/placeholder.png'">
                    <div class="result-info">
                        <div class="result-name">
                            ${product.name}
                            <span class="product-type ${typeClass}">${typeText}</span>
                        </div>
                        <div class="result-sku">
                            SKU: ${product.sku_code || 'N/A'} | Barcode: ${product.barcode || 'N/A'}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.classList.add('show');
    }

    // Add product to selection - FIXED: Parse JSON string
    function addProduct(productJson) {
        try {
            const product = JSON.parse(productJson.replace(/&quot;/g, '"').replace(/&#39;/g, "'"));

            console.log('Adding product:', product);

            if (selectedProducts.find(p => p.id === product.id)) {
                showAlert('Product already added', 'error');
                return;
            }

            product.quantity = 1;
            selectedProducts.push(product);
            updateSelectedProductsList();
            updatePrintButton();
            generatePreview();

            // Clear search
            document.getElementById('productSearch').value = '';
            document.getElementById('searchResults').classList.remove('show');

            showAlert('Product added', 'success');
        } catch (error) {
            console.error('Error adding product:', error);
            showAlert('Error adding product', 'error');
        }
    }

    // Update selected products list
    function updateSelectedProductsList() {
        const container = document.getElementById('selectedProductsList');
        document.getElementById('selectedCount').textContent = selectedProducts.length;

        if (selectedProducts.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">📦</div>
                    <p>No products selected</p>
                    <p class="empty-hint">Search and add products to print barcodes</p>
                </div>
            `;
            return;
        }

        container.innerHTML = selectedProducts.map((product, index) => {
            return `
                <div class="product-item">
                    <div class="product-header">
                        <div class="product-info">
                            <img src="${product.base_image || '/placeholder.png'}" class="product-image" alt="${product.name}"
                                 onerror="this.src='/placeholder.png'">
                            <div class="product-details">
                                <div class="product-name">${product.name}</div>
                                <div class="product-sku">
                                    <span class="sku-code">${product.sku_code}</span>
                                    <span>Barcode: ${product.barcode}</span>
                                </div>
                            </div>
                        </div>
                        <button class="btn-remove" onclick="removeProduct(${index})">✕</button>
                    </div>
                    <div class="product-quantity">
                        <span class="quantity-label">Quantity:</span>
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">−</button>
                            <input type="number" class="quantity-input" value="${product.quantity}"
                                onchange="setQuantity(${index}, this.value)" min="1" max="1000">
                            <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Remove product
    function removeProduct(index) {
        selectedProducts.splice(index, 1);
        updateSelectedProductsList();
        updatePrintButton();
        generatePreview();
        showAlert('Product removed', 'success');
    }

    // Update quantity
    function updateQuantity(index, change) {
        const product = selectedProducts[index];
        const newQty = product.quantity + change;
        if (newQty < 1) return;
        product.quantity = newQty;
        updateSelectedProductsList();
    }

    function setQuantity(index, value) {
        const qty = parseInt(value) || 1;
        const product = selectedProducts[index];
        product.quantity = qty < 1 ? 1 : qty;
        updateSelectedProductsList();
    }

    // Update print button state
    function updatePrintButton() {
        document.getElementById('printBtn').disabled = selectedProducts.length === 0;
    }

    // Generate barcode preview
    function generatePreview() {
        const previewContainer = document.getElementById('barcodePreview');
        const barcodeSize = document.getElementById('barcodeSize').value;
         const sizes = {
        small:  { width: 1, height: 30 },
        medium: { width: 2, height: 40 },
        large:  { width: 3, height: 55 }
    };

    const size = sizes[barcodeSize] || sizes.medium;

        if (selectedProducts.length === 0) {
            previewContainer.innerHTML = `
                <div class="preview-placeholder">
                    <div class="preview-icon">🏷️</div>
                    <p>Preview will appear here</p>
                </div>
            `;
            return;
        }

        const product = selectedProducts[0];
        const showName = document.getElementById('showProductName').checked;
        const showPrice = document.getElementById('showPrice').checked;
        const showSKU = document.getElementById('showSKU').checked;

        previewContainer.innerHTML = `
            <div style="text-align: center;">
                <svg id="previewBarcode" style="display: block; margin: 0 auto;"></svg>
                ${showName ? `<div style="font-size: 13px; font-weight: 500; margin-top: 10px; color: #1e293b;">${product.name}</div>` : ''}
                ${showSKU ? `<div style="font-size: 11px; color: #64748b; margin-top: 4px;">SKU: ${product.sku_code}</div>` : ''}
                ${showPrice ? `<div style="font-size: 12px; font-weight: 500; margin-top: 4px; color: #1e293b;">₹${product.price || '0'}</div>` : ''}
            </div>
        `;

        // Generate barcode
        const svg = document.getElementById('previewBarcode');
        if (svg && typeof JsBarcode !== 'undefined') {
            try {
                const barcodeValue = product.barcode || product.sku_code;
                JsBarcode(svg, barcodeValue, {
                    format: product.barcode_symbology || 'CODE128',
                    width: size.width,
                    height: size.height,
                    displayValue: true,
                    fontSize: barcodeSize === 'large' ? 14 : 12,
                    margin: 5
                });
            } catch (error) {
                console.error('Barcode error:', error);
            }
        }
    }

    // Event Listeners
    document.getElementById('productSearch').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const search = e.target.value.trim();

        if (search.length < 1) {
            document.getElementById('searchResults').classList.remove('show');
            return;
        }

        if (!currentWarehouseId) {
            showAlert('Main warehouse not found', 'error');
            return;
        }

        searchTimeout = setTimeout(() => searchProducts(search), 300);
    });

    document.getElementById('searchBtn').addEventListener('click', function() {
        const search = document.getElementById('productSearch').value.trim();
        if (search.length >= 1) {
            searchProducts(search);
        }
    });

    document.getElementById('resetBtn').addEventListener('click', function() {
        if (selectedProducts.length > 0) {
            if (confirm('Reset all selections?')) {
                selectedProducts = [];
                updateSelectedProductsList();
                updatePrintButton();
                generatePreview();
                document.getElementById('productSearch').value = '';
                document.getElementById('searchResults').classList.remove('show');
                showAlert('All selections cleared', 'success');
            }
        }
    });

    document.getElementById('printBtn').addEventListener('click', function() {
        if (selectedProducts.length === 0) {
            showAlert('Please select products first', 'error');
            return;
        }

        printBarcodes();
    });

    // Print function
    function printBarcodes() {
        const barcodeSize = document.getElementById('barcodeSize').value;
        const showName = document.getElementById('showProductName').checked;
        const showPrice = document.getElementById('showPrice').checked;
        const showSKU = document.getElementById('showSKU').checked;

        const sizes = {
            small: { width: 1, height: 30 },
            medium: { width: 2, height: 40 },
            large: { width: 3, height: 50 }
        };
        const size = sizes[barcodeSize] || sizes.medium;

        // Create print HTML
        let printHTML = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Barcodes - ${currentWarehouseName}</title>
                <meta charset="UTF-8">
                <style>
                    @media print {
                        @page {
                            margin: 10mm;
                            size: A4 portrait;
                        }

                        body {
                            margin: 0;
                            padding: 10px;
                            font-family: Arial, sans-serif;
                        }

                        .print-header {
                            text-align: center;
                            margin-bottom: 15px;
                            padding-bottom: 10px;
                            border-bottom: 2px solid #333;
                        }

                        .print-container {
                            display: grid;
                            grid-template-columns: repeat(3, 1fr);
                            gap: 10px;
                            width: 100%;
                        }

                        .print-barcode-item {
                            page-break-inside: avoid;
                            padding: 10px;
                            border: 1px solid #000;
                            text-align: center;
                            background: white;
                            border-radius: 5px;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            min-height: 120px;
                        }

                        .barcode-svg {
                            width: 100%;
                            max-width: 150px;
                            height: auto;
                            margin: 0 auto;
                        }

                        .print-barcode-name {
                            font-size: 11px;
                            font-weight: bold;
                            margin: 8px 0 4px;
                            color: #000;
                            word-break: break-word;
                        }

                        .print-barcode-sku {
                            font-size: 9px;
                            color: #666;
                            margin: 2px 0;
                        }

                        .print-barcode-price {
                            font-size: 10px;
                            font-weight: bold;
                            margin-top: 3px;
                            color: #000;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="print-header">
                    <h2 style="margin: 0; color: #1e293b; font-size: 18px;">Barcode Labels</h2>
                    <p style="margin: 5px 0 0; color: #475569; font-size: 12px;">Warehouse: ${currentWarehouseName}</p>
                    <p style="margin: 5px 0 0; color: #666; font-size: 11px;">Printed: ${new Date().toLocaleDateString()}</p>
                </div>
                <div class="print-container">
        `;

        // Generate barcodes for each product
        selectedProducts.forEach((product, productIndex) => {
            for (let i = 0; i < product.quantity; i++) {
                printHTML += `
                    <div class="print-barcode-item">
                        <svg class="barcode-svg" id="barcode-${productIndex}-${i}"></svg>
                        ${showName ? `<div class="print-barcode-name">${product.name}</div>` : ''}
                        ${showSKU ? `<div class="print-barcode-sku">SKU: ${product.sku_code}</div>` : ''}
                        ${showPrice ? `<div class="print-barcode-price">₹${product.price || '0'}</div>` : ''}
                    </div>
                `;
            }
        });

        printHTML += `
                </div>

                <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const productsData = ${JSON.stringify(selectedProducts)};
                        const size = ${JSON.stringify(size)};

                        productsData.forEach((product, productIndex) => {
                            for (let i = 0; i < product.quantity; i++) {
                                const svgId = 'barcode-' + productIndex + '-' + i;
                                const svg = document.getElementById(svgId);

                                if (svg) {
                                    try {
                                        const barcodeValue = product.barcode || product.sku_code;
                                        JsBarcode(svg, barcodeValue, {
                                            format: product.barcode_symbology || 'CODE128',
                                            width: size.width,
                                            height: size.height,
                                            displayValue: true,
                                            fontSize: 10,
                                            margin: 5
                                        });
                                    } catch (error) {
                                        console.error('Barcode error:', error);
                                        svg.innerHTML = '<text x="50%" y="50%" text-anchor="middle">Error</text>';
                                    }
                                }
                            }
                        });

                        setTimeout(() => {
                            window.print();
                            window.onafterprint = function() {
                                setTimeout(() => window.close(), 100);
                            };
                        }, 500);
                    });
                <\/script>
            </body>
            </html>
        `;

        // Open print window
        const printWindow = window.open('', '_blank');
        if (printWindow) {
            printWindow.document.write(printHTML);
            printWindow.document.close();
        } else {
            showAlert('Please allow popups to print', 'error');
        }
    }

    // Settings change listeners
    ['barcodeSize', 'showProductName', 'showPrice', 'showSKU'].forEach(id => {
        document.getElementById(id).addEventListener('change', generatePreview);
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', generatePreview);
</script>
@endpush
@endsection
