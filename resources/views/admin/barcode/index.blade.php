@extends('layouts.admin')

@section('title', 'Print Barcode - Admin Panel')
@section('header-title', 'Print Barcode')

@section('content')
<div class="barcode-print-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <h2 class="page-title">Barcode Printing System</h2>
        <p class="page-subtitle">Select warehouse, products, and print settings</p>
    </div>

    <!-- Main Content -->
    <div class="content-grid">
        <!-- Left Panel - Selection -->
        <div class="selection-panel">
            <!-- Warehouse Selection -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📍 Select Warehouse</h3>
                </div>
                <div class="card-body">
                    <select class="form-select" id="warehouseSelect">
                        <option value="">-- Select Warehouse --</option>
                        @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                        @endforeach
                    </select>
                    <p class="help-text">Select warehouse for stock tracking</p>
                </div>
            </div>

            <!-- Product Search -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🔍 Search Product</h3>
                </div>
                <div class="card-body">
                    <div class="search-box">
                        <input type="text" class="form-input" id="productSearch" placeholder="Search by product name or SKU code...">
                        <button class="search-btn" id="searchBtn">🔍</button>
                    </div>

                    <!-- Search Results Dropdown -->
                    <div class="search-results" id="searchResults"></div>
                </div>
            </div>

            <!-- Selected Products -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📦 Selected Products</h3>
                    <span class="badge" id="selectedCount">0</span>
                </div>
                <div class="card-body">
                    <div class="selected-products-list" id="selectedProductsList">
                        <div class="empty-state">
                            <div class="empty-icon">📦</div>
                            <p>No products selected</p>
                            <p class="empty-hint">Search and add products to print barcodes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Print Settings -->
        <div class="settings-panel">
            <!-- Print Settings -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🖨️ Print Settings</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Paper Size</label>
                        <select class="form-select" id="paperSize">
                            <option value="a4">A4 (210 x 297 mm)</option>
                            <option value="letter">Letter (8.5 x 11 inch)</option>
                            <option value="label-40x30">Label 40x30 mm</option>
                            <option value="label-50x25">Label 50x25 mm</option>
                            <option value="label-100x50">Label 100x50 mm</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Barcode Size</label>
                        <select class="form-select" id="barcodeSize">
                            <option value="small">Small</option>
                            <option value="medium" selected>Medium</option>
                            <option value="large">Large</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Show Product Name</label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="showProductName" checked>
                            <label for="showProductName"></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Show Price</label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="showPrice" checked>
                            <label for="showPrice"></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Show SKU</label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="showSKU" checked>
                            <label for="showSKU"></label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">👁️ Barcode Preview</h3>
                </div>
                <div class="card-body">
                    <div class="barcode-preview" id="barcodePreview">
                        <div class="preview-placeholder">
                            <div class="preview-icon">🏷️</div>
                            <p>Preview will appear here</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn-action btn-reset" id="resetBtn">
                    <span>🔄</span> Reset
                </button>
                <button class="btn-action btn-print" id="printBtn" disabled>
                    <span>🖨️</span> Print Barcodes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Print Template (Hidden) -->
<div id="printTemplate" style="display: none;">
    <div class="print-container">
        <div id="printContent"></div>
    </div>
</div>

@push('styles')
<style>
    .barcode-print-container { padding-bottom: 40px; }

    /* Alert Styles */
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }
    .alert-icon { font-size: 24px; }

    .page-header { margin-bottom: 30px; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 8px; }
    .page-subtitle { font-size: 14px; color: #718096; }

    .content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }

    .card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; }
    .card-header { padding: 20px 25px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .card-title { font-size: 18px; font-weight: 700; color: #2d3748; display: flex; align-items: center; gap: 8px; }
    .card-body { padding: 25px; }
    .badge { background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }

    .form-select, .form-input { width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; }
    .form-select:focus, .form-input:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
    .help-text { font-size: 12px; color: #a0aec0; margin-top: 8px; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: #4a5568; font-size: 14px; }

    .search-box { display: flex; gap: 10px; }
    .search-btn { padding: 12px 20px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 18px; transition: all 0.3s; }
    .search-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.4); }

    .search-results { max-height: 300px; overflow-y: auto; margin-top: 15px; display: none; background: #f7fafc; border-radius: 12px; border: 2px solid #e2e8f0; }
    .search-results.show { display: block; }
    .search-result-item { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 12px; }
    .search-result-item:last-child { border-bottom: none; }
    .search-result-item:hover { background: #edf2f7; }
    .result-image { width: 45px; height: 45px; object-fit: cover; border-radius: 8px; background: #e2e8f0; }
    .result-info { flex: 1; }
    .result-name { font-weight: 600; color: #2d3748; font-size: 14px; margin-bottom: 4px; }
    .result-sku { font-size: 12px; color: #718096; }

    .selected-products-list { max-height: 400px; overflow-y: auto; }
    .empty-state { text-align: center; padding: 40px 20px; }
    .empty-icon { font-size: 48px; margin-bottom: 15px; }
    .empty-state p { color: #718096; margin-bottom: 5px; }
    .empty-hint { font-size: 12px; color: #a0aec0; }

    .product-item { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); padding: 15px; border-radius: 12px; margin-bottom: 12px; border: 2px solid #e2e8f0; }
    .product-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px; }
    .product-info { flex: 1; display: flex; gap: 12px; }
    .product-image { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; background: #e2e8f0; }
    .product-details { flex: 1; }
    .product-name { font-weight: 600; color: #2d3748; font-size: 14px; margin-bottom: 4px; }
    .product-sku { font-size: 12px; color: #718096; display: flex; align-items: center; gap: 6px; }
    .sku-badge { background: #ff6b35; color: white; padding: 2px 8px; border-radius: 4px; font-weight: 600; }
    .btn-remove { background: #fc8181; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s; }
    .btn-remove:hover { background: #f56565; transform: scale(1.05); }

    .product-quantity { display: flex; align-items: center; gap: 12px; }
    .quantity-label { font-size: 13px; color: #4a5568; font-weight: 600; }
    .quantity-controls { display: flex; align-items: center; gap: 8px; }
    .quantity-btn { width: 32px; height: 32px; background: #ff6b35; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 700; transition: all 0.3s; }
    .quantity-btn:hover { background: #f7931e; transform: scale(1.1); }
    .quantity-input { width: 70px; padding: 8px; text-align: center; border: 2px solid #e2e8f0; border-radius: 8px; font-weight: 700; color: #2d3748; }

    /* Toggle Switch */
    .toggle-switch { position: relative; display: inline-block; width: 50px; height: 26px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-switch label { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #cbd5e0; border-radius: 34px; transition: 0.3s; }
    .toggle-switch label:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s; }
    .toggle-switch input:checked + label { background: #48bb78; }
    .toggle-switch input:checked + label:before { transform: translateX(24px); }

    .barcode-preview { min-height: 250px; background: #f7fafc; border: 2px dashed #cbd5e0; border-radius: 12px; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .preview-placeholder { text-align: center; }
    .preview-icon { font-size: 48px; margin-bottom: 15px; }
    .preview-placeholder p { color: #718096; }

    .action-buttons { display: flex; gap: 15px; }
    .btn-action { flex: 1; padding: 15px 24px; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 15px; }
    .btn-reset { background: #e2e8f0; color: #4a5568; }
    .btn-reset:hover { background: #cbd5e0; transform: translateY(-2px); }
    .btn-print { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; box-shadow: 0 4px 12px rgba(72,187,120,0.3); }
    .btn-print:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(72,187,120,0.4); }
    .btn-print:disabled { opacity: 0.5; cursor: not-allowed; }

    /* Print Styles */
    @media print {
        body * { visibility: hidden; }
        #printTemplate, #printTemplate * { visibility: visible; }
        #printTemplate { position: absolute; left: 0; top: 0; width: 100%; }
        .print-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; padding: 20px; }
        .print-barcode-item {
            page-break-inside: avoid;
            padding: 15px;
            border: 2px solid #333;
            text-align: center;
            background: white;
            border-radius: 8px;
        }
        .print-barcode-item svg {
            width: 100% !important;
            height: auto !important;
            max-width: 200px;
            margin: 0 auto;
            display: block;
        }
        .print-barcode-name { font-size: 14px; font-weight: bold; margin: 10px 0 5px; color: #000; }
        .print-barcode-sku { font-size: 11px; color: #666; margin: 5px 0; }
        .print-barcode-price { font-size: 13px; font-weight: bold; margin-top: 5px; color: #000; }
    }

    @media (max-width: 1024px) {
        .content-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    let selectedProducts = [];
    let searchTimeout;

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

        setTimeout(() => alert.remove(), 5000);
    }

    // Disable product search until warehouse is selected
    document.getElementById('productSearch').disabled = true;
    document.getElementById('searchBtn').disabled = true;

    // Enable search when warehouse is selected
    document.getElementById('warehouseSelect').addEventListener('change', function() {
        const warehouseId = this.value;
        const searchInput = document.getElementById('productSearch');
        const searchBtn = document.getElementById('searchBtn');

        if (warehouseId) {
            searchInput.disabled = false;
            searchBtn.disabled = false;
            searchInput.placeholder = 'Search by product name or SKU code...';
        } else {
            searchInput.disabled = true;
            searchBtn.disabled = true;
            searchInput.placeholder = 'Please select warehouse first';
            searchInput.value = '';
            document.getElementById('searchResults').classList.remove('show');
        }
    });

    // Search products
    document.getElementById('productSearch').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const search = e.target.value.trim();

        if (search.length < 2) {
            document.getElementById('searchResults').classList.remove('show');
            return;
        }

        const warehouseId = document.getElementById('warehouseSelect').value;
        if (!warehouseId) {
            showAlert('Please select a warehouse first', 'error');
            return;
        }

        searchTimeout = setTimeout(() => {
            searchProducts(search);
        }, 300);
    });

    document.getElementById('searchBtn').addEventListener('click', function() {
        const search = document.getElementById('productSearch').value.trim();
        const warehouseId = document.getElementById('warehouseSelect').value;

        if (!warehouseId) {
            showAlert('Please select a warehouse first', 'error');
            return;
        }

        if (search.length >= 2) {
            searchProducts(search);
        }
    });

    function searchProducts(search) {
        const warehouseId = document.getElementById('warehouseSelect').value;

        fetch('/admin/barcode/search-products?' + new URLSearchParams({
            search: search,
            warehouse_id: warehouseId
        }), {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchResults(data.products);
            } else {
                showAlert(data.message || 'Search failed', 'error');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            showAlert('Search failed', 'error');
        });
    }

    function displaySearchResults(products) {
        const container = document.getElementById('searchResults');

        if (products.length === 0) {
            container.innerHTML = '<div style="padding: 20px; text-align: center; color: #718096;">No products found</div>';
            container.classList.add('show');
            return;
        }

        container.innerHTML = products.map(product => `
            <div class="search-result-item" onclick='addProduct(${JSON.stringify(product)})'>
                <img src="${product.base_image || '/placeholder.png'}" class="result-image" alt="${product.name}"
                     onerror="this.src='/placeholder.png'">
                <div class="result-info">
                    <div class="result-name">${product.name}</div>
                    <div class="result-sku">SKU: ${product.sku_code} | Barcode: ${product.barcode}</div>
                </div>
            </div>
        `).join('');

        container.classList.add('show');
    }

    function addProduct(product) {
        // Check if already added
        if (selectedProducts.find(p => p.id === product.id)) {
            showAlert('Product already added', 'error');
            return;
        }

        product.quantity = 1;
        selectedProducts.push(product);
        updateSelectedProductsList();
        updatePrintButton();

        // Generate preview immediately
        generatePreview();

        // Clear search
        document.getElementById('productSearch').value = '';
        document.getElementById('searchResults').classList.remove('show');

        showAlert('Product added successfully', 'success');
    }

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

        container.innerHTML = selectedProducts.map((product, index) => `
            <div class="product-item">
                <div class="product-header">
                    <div class="product-info">
                        <img src="${product.base_image || '/placeholder.png'}" class="product-image" alt="${product.name}"
                             onerror="this.src='/placeholder.png'">
                        <div class="product-details">
                            <div class="product-name">${product.name}</div>
                            <div class="product-sku">
                                <span class="sku-badge">${product.sku_code}</span>
                                Barcode: ${product.barcode}
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
        `).join('');
    }

    function removeProduct(index) {
        selectedProducts.splice(index, 1);
        updateSelectedProductsList();
        updatePrintButton();

        // Generate preview after removal
        generatePreview();

        showAlert('Product removed', 'success');
    }

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

        if (qty < 1) product.quantity = 1;
        else product.quantity = qty;

        updateSelectedProductsList();
    }

    function updatePrintButton() {
        const printBtn = document.getElementById('printBtn');
        printBtn.disabled = selectedProducts.length === 0;
    }

    // Reset function
    document.getElementById('resetBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to reset all selections?')) {
            selectedProducts = [];
            updateSelectedProductsList();
            updatePrintButton();
            generatePreview();
            document.getElementById('warehouseSelect').value = '';
            document.getElementById('productSearch').value = '';
            document.getElementById('productSearch').disabled = true;
            document.getElementById('searchBtn').disabled = true;
            document.getElementById('searchResults').classList.remove('show');
            showAlert('All selections cleared', 'success');
        }
    });

    // Update preview when settings change
    document.getElementById('barcodeSize').addEventListener('change', generatePreview);
    document.getElementById('showProductName').addEventListener('change', generatePreview);
    document.getElementById('showPrice').addEventListener('change', generatePreview);
    document.getElementById('showSKU').addEventListener('change', generatePreview);

    // Generate barcode preview
    function generatePreview() {
        const previewContainer = document.getElementById('barcodePreview');

        if (selectedProducts.length === 0) {
            previewContainer.innerHTML = `
                <div class="preview-placeholder">
                    <div class="preview-icon">🏷️</div>
                    <p>Preview will appear here</p>
                </div>
            `;
            return;
        }

        const barcodeSize = document.getElementById('barcodeSize').value;
        const showName = document.getElementById('showProductName').checked;
        const showPrice = document.getElementById('showPrice').checked;
        const showSKU = document.getElementById('showSKU').checked;

        const sizes = {
            small: { width: 1, height: 30 },
            medium: { width: 2, height: 40 },
            large: { width: 3, height: 50 }
        };
        const size = sizes[barcodeSize];

        // Show preview of first product only
        const product = selectedProducts[0];

        previewContainer.innerHTML = `
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; max-width: 300px; margin: 0 auto;">
                <svg id="previewBarcode" style="display: block; margin: 0 auto;"></svg>
                ${showName ? `<div style="font-size: 12px; font-weight: bold; margin-top: 8px; color: #2d3748;">${product.name}</div>` : ''}
                ${showSKU ? `<div style="font-size: 10px; color: #718096; margin-top: 4px;">SKU: ${product.sku_code}</div>` : ''}
                ${showPrice ? `<div style="font-size: 11px; font-weight: bold; color: #2d3748; margin-top: 4px;">₹ ${product.price || 'N/A'}</div>` : ''}
                <div style="font-size: 11px; color: #a0aec0; margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                    Preview (${selectedProducts.length} product${selectedProducts.length > 1 ? 's' : ''} selected)
                </div>
            </div>
        `;

        // Generate barcode IMMEDIATELY
        const svg = document.getElementById('previewBarcode');
        if (svg && typeof JsBarcode !== 'undefined') {
            try {
                // Use barcode value (which is SKU if barcode is empty)
                const barcodeValue = product.barcode || product.sku_code;
                JsBarcode(svg, barcodeValue, {
                    format: product.barcode_symbology || 'CODE128',
                    width: size.width,
                    height: size.height,
                    displayValue: true,
                    fontSize: 12,
                    margin: 5
                });
            } catch (error) {
                console.error('Barcode preview generation error:', error);
                previewContainer.innerHTML = `
                    <div class="preview-placeholder">
                        <div class="preview-icon">⚠️</div>
                        <p style="color: #fc8181;">Barcode Error</p>
                        <p style="font-size: 12px; margin-top: 8px;">SKU: ${product.sku_code}</p>
                        <p style="font-size: 12px;">Barcode: ${product.barcode}</p>
                    </div>
                `;
            }
        }
    }

    // Print function - COMPLETE FIXED VERSION
    document.getElementById('printBtn').addEventListener('click', function() {
        if (selectedProducts.length === 0) {
            showAlert('Please select products to print', 'error');
            return;
        }

        const warehouse = document.getElementById('warehouseSelect').value;
        if (!warehouse) {
            showAlert('Please select a warehouse', 'error');
            return;
        }

        // Start printing process
        printBarcodes();
    });

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
        const size = sizes[barcodeSize];

        // Create print HTML with inline barcodes
        let printHTML = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Barcodes</title>
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
                            min-height: 150px;
                        }

                        .barcode-svg {
                            width: 100%;
                            max-width: 180px;
                            height: auto;
                            margin: 0 auto;
                        }

                        .print-barcode-name {
                            font-size: 12px;
                            font-weight: bold;
                            margin: 8px 0 4px;
                            color: #000;
                            word-break: break-word;
                        }

                        .print-barcode-sku {
                            font-size: 10px;
                            color: #666;
                            margin: 4px 0;
                        }

                        .print-barcode-price {
                            font-size: 12px;
                            font-weight: bold;
                            margin-top: 4px;
                            color: #000;
                        }
                    }

                    /* For screen preview */
                    body {
                        padding: 20px;
                    }
                    .print-container {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 15px;
                    }
                    .print-barcode-item {
                        border: 2px solid #333;
                        padding: 15px;
                        text-align: center;
                        border-radius: 8px;
                        background: white;
                    }
                </style>
            </head>
            <body>
                <div class="print-container">
        `;

        // Generate barcodes for each product
        selectedProducts.forEach((product, productIndex) => {
            for (let i = 0; i < product.quantity; i++) {
                const barcodeValue = product.barcode || product.sku_code;
                const symbology = product.barcode_symbology || 'CODE128';

                printHTML += `
                    <div class="print-barcode-item">
                        <svg class="barcode-svg" id="barcode-${productIndex}-${i}"></svg>
                        ${showName ? `<div class="print-barcode-name">${product.name}</div>` : ''}
                        ${showSKU ? `<div class="print-barcode-sku">SKU: ${product.sku_code}</div>` : ''}
                        ${showPrice ? `<div class="print-barcode-price">₹ ${product.price || 'N/A'}</div>` : ''}
                    </div>
                `;
            }
        });

        printHTML += `
                </div>

                <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
                <script>
                    // Generate all barcodes when page loads
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
                                            fontSize: 12,
                                            margin: 5
                                        });
                                    } catch (error) {
                                        console.error('Barcode generation error:', error);
                                        svg.innerHTML = '<text x="50%" y="50%" text-anchor="middle">Barcode Error</text>';
                                    }
                                }
                            }
                        });

                        // Wait for barcodes to render, then print
                        setTimeout(() => {
                            window.print();

                            // Close window after printing
                            window.onafterprint = function() {
                                setTimeout(() => {
                                    window.close();
                                }, 100);
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
            showAlert('Please allow popups to print barcodes', 'error');
        }
    }

    // Initialize preview on page load
    document.addEventListener('DOMContentLoaded', function() {
        generatePreview();
    });
</script>
@endpush
@endsection
