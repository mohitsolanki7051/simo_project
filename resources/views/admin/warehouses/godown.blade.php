@extends('layouts.admin')

@section('title', 'Godown Management - Admin Panel')
@section('header-title', 'Godown Management')

@section('content')
<div class="godown-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header with Warehouse Selector -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Warehouse (Godown) Management</h2>
            <p class="page-subtitle">Manage warehouse stock and transfers</p>
        </div>
        <div class="header-actions">
            <select id="warehouseSelector" class="warehouse-select">
                <option value="">Select Warehouse...</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}"
                        {{ $warehouse->is_main ? 'selected' : '' }}
                        data-is-main="{{ $warehouse->is_main ? 'true' : 'false' }}">
                        {{ $warehouse->name }} {{ $warehouse->is_main ? '(Main)' : '' }}
                    </option>
                @endforeach
            </select>
            <button class="btn-primary" onclick="openCreateWarehouseModal()">
                <span>+</span> Create Warehouse
            </button>
        </div>
    </div>

    <!-- Stock Summary Cards -->
    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);">📦</div>
            <div class="stat-info">
                <div class="stat-label">Total Products</div>
                <div class="stat-value" id="totalProducts">0</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);">📊</div>
            <div class="stat-info">
                <div class="stat-label">Total Stock Value</div>
                <div class="stat-value" id="totalValue">₹0</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);">⚠️</div>
            <div class="stat-info">
                <div class="stat-label">Low Stock Items</div>
                <div class="stat-value" id="lowStockItems">0</div>
            </div>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-header-left">
                <h3 class="table-title">Warehouse Stock</h3>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search products..." class="search-input">
                    <span class="search-icon">🔍</span>
                </div>
            </div>
            <div class="table-header-right">
                <div class="selected-info" id="selectedInfo" style="display: none;">
                    <span id="selectedCount">0</span> items selected
                </div>
                <button class="btn-transfer" id="transferBtn" onclick="openTransferModal()" disabled>
                    <span>↔️</span> Transfer Stock
                </button>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="selectAll" class="checkbox">
                        </th>
                        <th>Product Name</th>
                        <th>Variant</th>
                        <th>SKU Code</th>
                        <th>Stock QTY</th>
                        <th>Unit</th>
                        <th>Sale Price</th>
                        <th>Cost Price</th>
                        <th>Stock Value</th>
                    </tr>
                </thead>
                <tbody id="stockTableBody">
                    <tr>
                        <td colspan="9" class="empty-state">
                            <div class="empty-icon">🏢</div>
                            <p>Select a warehouse to view stock</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Warehouse Modal -->
<div class="modal" id="createWarehouseModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Create New Warehouse</h3>
            <button class="modal-close" onclick="closeCreateWarehouseModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createWarehouseForm">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label>Warehouse Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Warehouse Code <span class="required">*</span></label>
                        <input type="text" name="code" class="form-control" required>
                    </div>

                    <div class="form-group full-width">
                        <label>Address <span class="required">*</span></label>
                        <textarea name="address" class="form-control" rows="2" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>City <span class="required">*</span></label>
                        <input type="text" name="city" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>State <span class="required">*</span></label>
                        <input type="text" name="state" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Pincode <span class="required">*</span></label>
                        <input type="text" name="pincode" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Phone <span class="required">*</span></label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Manager Name</label>
                        <input type="text" name="manager_name" class="form-control">
                    </div>

                    <div class="form-group full-width">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_main" value="1">
                            <span>Set as Main Warehouse</span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeCreateWarehouseModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Create Warehouse</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transfer Stock Modal -->
<div class="modal" id="transferModal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3 class="modal-title">Transfer Stock</h3>
            <button class="modal-close" onclick="closeTransferModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="transferForm">
                @csrf
                <div class="transfer-info-grid">
                    <div class="form-group">
                        <label>From Warehouse</label>
                        <input type="text" id="fromWarehouse" class="form-control" readonly>
                        <input type="hidden" id="fromWarehouseId" name="from_warehouse_id">
                    </div>

                    <div class="form-group">
                        <label>To Warehouse <span class="required">*</span></label>
                        <select name="to_warehouse_id" id="toWarehouse" class="form-control" required>
                            <option value="">Select destination...</option>
                        </select>
                    </div>
                </div>

                <div class="transfer-items-section">
                    <h4>Items to Transfer</h4>
                    <div id="transferItemsList"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeTransferModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Confirm Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .godown-container { padding-bottom: 40px; }

    /* Alert Styles */
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
    .header-left { flex: 1; min-width: 250px; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 8px; }
    .page-subtitle { font-size: 14px; color: #718096; }

    .header-actions { display: flex; gap: 15px; align-items: center; }
    .warehouse-select { padding: 12px 20px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; font-weight: 600; min-width: 250px; transition: all 0.3s; }
    .warehouse-select:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }

    .btn-primary { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(255,107,53,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.4); }

    .btn-secondary { padding: 12px 24px; background: #e2e8f0; color: #2d3748; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .btn-secondary:hover { background: #cbd5e0; }

    .btn-transfer { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(66,153,225,0.3); }
    .btn-transfer:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(66,153,225,0.4); }
    .btn-transfer:disabled { opacity: 0.5; cursor: not-allowed; }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; }
    .stat-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
    .stat-info { flex: 1; }
    .stat-label { font-size: 13px; color: #718096; margin-bottom: 5px; }
    .stat-value { font-size: 28px; font-weight: 700; color: #2d3748; }

    .table-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
    .table-header { padding: 25px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .table-header-left { display: flex; align-items: center; gap: 20px; flex: 1; flex-wrap: wrap; }
    .table-title { font-size: 20px; font-weight: 700; color: #2d3748; }

    .search-box { position: relative; }
    .search-input { padding: 10px 40px 10px 16px; border: 2px solid #e2e8f0; border-radius: 10px; width: 300px; transition: all 0.3s; }
    .search-input:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
    .search-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 18px; }

    .table-header-right { display: flex; align-items: center; gap: 15px; }
    .selected-info { padding: 10px 16px; background: #edf2f7; border-radius: 8px; font-size: 14px; font-weight: 600; color: #2d3748; }
    .selected-info span { color: #ff6b35; }

    .table-wrapper { overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); }
    .data-table th { padding: 16px 20px; text-align: left; font-weight: 700; color: #4a5568; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
    .data-table td { padding: 18px 20px; border-bottom: 1px solid #e2e8f0; }
    .data-table tbody tr { transition: all 0.3s; }
    .data-table tbody tr:hover { background: #f7fafc; }
    .data-table tbody tr.parent-row { background: #fafafa; font-weight: 600; }
    .data-table tbody tr.variant-row { background: #fdfdfd; }
    .data-table tbody tr.variant-row td:nth-child(2) { padding-left: 50px; }

    .checkbox { width: 18px; height: 18px; cursor: pointer; }

    .product-name { font-weight: 600; color: #2d3748; font-size: 15px; }
    .variant-name { color: #4a5568; font-size: 14px; }
    .sku-code { font-size: 12px; color: #718096; background: #edf2f7; padding: 3px 10px; border-radius: 6px; display: inline-block; }
    .stock-qty { font-weight: 600; color: #2d3748; }
    .stock-low { color: #e53e3e; }
    .price-value { font-weight: 500; color: #2d3748; }

    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { font-size: 64px; margin-bottom: 20px; }
    .empty-state p { font-size: 16px; color: #718096; margin-bottom: 20px; }

    /* Modal Styles */
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10000; align-items: center; justify-content: center; padding: 20px; }
    .modal.show { display: flex; }
    .modal-content { background: white; border-radius: 16px; max-width: 600px; width: 100%; max-height: 90vh; overflow: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    .modal-content.modal-lg { max-width: 800px; }
    .modal-header { padding: 25px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 1; }
    .modal-title { font-size: 22px; font-weight: 700; color: #2d3748; }
    .modal-close { background: none; border: none; font-size: 32px; color: #718096; cursor: pointer; line-height: 1; }
    .modal-close:hover { color: #2d3748; }
    .modal-body { padding: 30px; }
    .modal-footer { padding: 20px 30px; border-top: 2px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 15px; position: sticky; bottom: 0; background: white; }

    /* Form Styles */
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group.full-width { grid-column: 1 / -1; }
    .form-group label { font-size: 14px; font-weight: 600; color: #2d3748; }
    .required { color: #e53e3e; }
    .form-control { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; }
    .form-control:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
    textarea.form-control { resize: vertical; font-family: inherit; }

    .checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px; background: #f7fafc; border-radius: 10px; transition: all 0.3s; }
    .checkbox-label:hover { background: #edf2f7; }
    .checkbox-label input[type="checkbox"] { width: 20px; height: 20px; }
    .checkbox-label span { font-size: 14px; font-weight: 600; color: #2d3748; }

    /* Transfer Modal Specific */
    .transfer-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
    .transfer-items-section { margin-top: 20px; }
    .transfer-items-section h4 { font-size: 16px; font-weight: 700; color: #2d3748; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0; }

    .transfer-item { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px; align-items: center; padding: 15px; background: #f7fafc; border-radius: 10px; margin-bottom: 12px; }
    .transfer-item-info { }
    .transfer-item-name { font-weight: 600; color: #2d3748; margin-bottom: 4px; }
    .transfer-item-variant { font-size: 13px; color: #718096; }
    .transfer-item-sku { font-size: 12px; color: #718096; background: #edf2f7; padding: 3px 8px; border-radius: 6px; display: inline-block; margin-top: 4px; }
    .transfer-item-available { text-align: center; }
    .transfer-item-available-label { font-size: 11px; color: #718096; margin-bottom: 4px; }
    .transfer-item-available-qty { font-weight: 700; color: #2d3748; font-size: 16px; }
    .transfer-item-input { }
    .transfer-item-input label { font-size: 11px; color: #718096; margin-bottom: 4px; display: block; }
    .transfer-item-input input { width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-weight: 600; }
    .transfer-item-input input:focus { outline: none; border-color: #ff6b35; }

    @media (max-width: 768px) {
        .page-header { flex-direction: column; gap: 15px; }
        .header-actions { width: 100%; flex-direction: column; }
        .warehouse-select { width: 100%; }
        .table-header { flex-direction: column; align-items: stretch; }
        .table-header-left, .table-header-right { width: 100%; }
        .search-input { width: 100%; }
        .transfer-info-grid { grid-template-columns: 1fr; }
        .transfer-item { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script>
    let selectedItems = [];
    let currentWarehouseId = null;
    let currentWarehouseName = '';

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        const warehouseSelector = document.getElementById('warehouseSelector');

        // Load main warehouse by default
        const mainWarehouse = warehouseSelector.querySelector('[data-is-main="true"]');
        if (mainWarehouse) {
            currentWarehouseId = mainWarehouse.value;
            currentWarehouseName = mainWarehouse.textContent.trim();
            loadWarehouseStock(currentWarehouseId);
        }

        // Warehouse selector change
        warehouseSelector.addEventListener('change', function(e) {
            currentWarehouseId = e.target.value;
            currentWarehouseName = e.target.options[e.target.selectedIndex].text;

            if (currentWarehouseId) {
                loadWarehouseStock(currentWarehouseId);
            } else {
                document.getElementById('stockTableBody').innerHTML = `
                    <tr>
                        <td colspan="9" class="empty-state">
                            <div class="empty-icon">🏢</div>
                            <p>Select a warehouse to view stock</p>
                        </td>
                    </tr>
                `;
            }

            // Clear selections
            selectedItems = [];
            updateSelectionUI();
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#stockTableBody tr');

            rows.forEach(row => {
                if (row.classList.contains('parent-row') || row.classList.contains('variant-row')) {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(search) ? '' : 'none';
                }
            });
        });

        // Select all checkbox
        document.getElementById('selectAll').addEventListener('change', function(e) {
            const checkboxes = document.querySelectorAll('.row-checkbox:not([disabled])');
            checkboxes.forEach(cb => {
                cb.checked = e.target.checked;

                if (e.target.checked) {
                    const itemData = JSON.parse(cb.dataset.item);
                    if (!selectedItems.find(i => i.id === itemData.id && i.type === itemData.type)) {
                        selectedItems.push(itemData);
                    }
                } else {
                    selectedItems = [];
                }
            });
            updateSelectionUI();
        });
    });

    // Load warehouse stock
    function loadWarehouseStock(warehouseId) {
        const tbody = document.getElementById('stockTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="empty-state">
                    <div class="loading">Loading stock...</div>
                </td>
            </tr>
        `;

        fetch(`/admin/godown/warehouse/${warehouseId}/stock`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderStockTable(data.stock);
                updateStats(data.stats);
            } else {
                showAlert('Failed to load warehouse stock', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to load warehouse stock', 'error');
        });
    }

    // Render stock table
    function renderStockTable(stock) {
        const tbody = document.getElementById('stockTableBody');

        if (!stock || stock.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="empty-state">
                        <div class="empty-icon">📦</div>
                        <p>No stock available in this warehouse</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';

        stock.forEach(item => {
            if (item.type === 'simple') {
                // Simple product row
                const stockValue = (item.quantity * item.sale_price).toFixed(2);
                const isLowStock = item.quantity <= item.min_stock_alert;

                const itemData = {
                    id: item.stock_id,
                    type: 'simple',
                    product_id: item.product_id,
                    product_name: item.product_name,
                    sku: item.sku,
                    quantity: item.quantity,
                    sale_price: item.sale_price,
                    cost_price: item.cost_price,
                    variant_id: null,
                    variant_name: null
                };

                html += `
                    <tr class="parent-row" data-product-id="${item.product_id}">
                        <td>
                            <input type="checkbox" class="checkbox row-checkbox"
                                   data-item='${JSON.stringify(itemData)}'
                                   onchange="handleCheckboxChange(this)">
                        </td>
                        <td><span class="product-name">${item.product_name}</span></td>
                        <td>-</td>
                        <td><span class="sku-code">${item.sku}</span></td>
                        <td><span class="stock-qty ${isLowStock ? 'stock-low' : ''}">${item.quantity}</span></td>
                        <td>${item.unit || 'PCS'}</td>
                        <td><span class="price-value">₹${parseFloat(item.sale_price).toFixed(2)}</span></td>
                        <td><span class="price-value">₹${parseFloat(item.cost_price).toFixed(2)}</span></td>
                        <td><span class="price-value">₹${stockValue}</span></td>
                    </tr>
                `;
            } else if (item.type === 'variant') {
                // Parent row (non-selectable)
                html += `
                    <tr class="parent-row" data-product-id="${item.product_id}">
                        <td><input type="checkbox" class="checkbox" disabled></td>
                        <td><span class="product-name">${item.product_name}</span></td>
                        <td colspan="7"></td>
                    </tr>
                `;

                // Variant rows
                if (item.variants && item.variants.length > 0) {
                    item.variants.forEach(variant => {
                        const stockValue = (variant.quantity * variant.sale_price).toFixed(2);
                        const isLowStock = variant.quantity <= variant.min_stock_alert;

                        const itemData = {
                            id: variant.stock_id,
                            type: 'variant',
                            product_id: item.product_id,
                            product_name: item.product_name,
                            variant_id: variant.variant_id,
                            variant_name: variant.variant_name,
                            sku: variant.sku,
                            quantity: variant.quantity,
                            sale_price: variant.sale_price,
                            cost_price: variant.cost_price
                        };

                        html += `
                            <tr class="variant-row" data-product-id="${item.product_id}">
                                <td>
                                    <input type="checkbox" class="checkbox row-checkbox"
                                           data-item='${JSON.stringify(itemData)}'
                                           onchange="handleCheckboxChange(this)">
                                </td>
                                <td><span class="variant-name">${variant.variant_name}</span></td>
                                <td>Variant</td>
                                <td><span class="sku-code">${variant.sku}</span></td>
                                <td><span class="stock-qty ${isLowStock ? 'stock-low' : ''}">${variant.quantity}</span></td>
                                <td>${variant.unit || 'PCS'}</td>
                                <td><span class="price-value">₹${parseFloat(variant.sale_price).toFixed(2)}</span></td>
                                <td><span class="price-value">₹${parseFloat(variant.cost_price).toFixed(2)}</span></td>
                                <td><span class="price-value">₹${stockValue}</span></td>
                            </tr>
                        `;
                    });
                }
            }
        });

        tbody.innerHTML = html;
    }

    // Update statistics
    function updateStats(stats) {
        document.getElementById('totalProducts').textContent = stats.total_products || 0;
        document.getElementById('totalValue').textContent = '₹' + (stats.total_value ? parseFloat(stats.total_value).toFixed(2) : '0.00');
        document.getElementById('lowStockItems').textContent = stats.low_stock_items || 0;
    }

    // Handle checkbox change
    function handleCheckboxChange(checkbox) {
        const itemData = JSON.parse(checkbox.dataset.item);

        if (checkbox.checked) {
            // Add to selection
            if (!selectedItems.find(i => i.id === itemData.id && i.type === itemData.type)) {
                selectedItems.push(itemData);
            }
        } else {
            // Remove from selection
            selectedItems = selectedItems.filter(i => !(i.id === itemData.id && i.type === itemData.type));
        }

        updateSelectionUI();
    }

    // Update selection UI
    function updateSelectionUI() {
        const selectedCount = selectedItems.length;
        const selectedInfo = document.getElementById('selectedInfo');
        const transferBtn = document.getElementById('transferBtn');
        const selectAll = document.getElementById('selectAll');

        if (selectedCount > 0) {
            selectedInfo.style.display = 'block';
            document.getElementById('selectedCount').textContent = selectedCount;
            transferBtn.disabled = false;
        } else {
            selectedInfo.style.display = 'none';
            transferBtn.disabled = true;
        }

        // Update select all checkbox
        const allCheckboxes = document.querySelectorAll('.row-checkbox:not([disabled])');
        const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        selectAll.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
    }

    // Create Warehouse Modal
    function openCreateWarehouseModal() {
        document.getElementById('createWarehouseModal').classList.add('show');
    }

    function closeCreateWarehouseModal() {
        document.getElementById('createWarehouseModal').classList.remove('show');
        document.getElementById('createWarehouseForm').reset();
    }

    // Handle create warehouse form submission
    document.getElementById('createWarehouseForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/admin/warehouses', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message || 'Warehouse created successfully', 'success');
                closeCreateWarehouseModal();

                // Add to dropdown
                const selector = document.getElementById('warehouseSelector');
                const option = document.createElement('option');
                option.value = data.warehouse.id;
                option.textContent = data.warehouse.name + (data.warehouse.is_main ? ' (Main)' : '');
                option.setAttribute('data-is-main', data.warehouse.is_main ? 'true' : 'false');
                selector.appendChild(option);

                // If it's main, select it
                if (data.warehouse.is_main) {
                    selector.value = data.warehouse.id;
                    currentWarehouseId = data.warehouse.id;
                    currentWarehouseName = data.warehouse.name;
                    loadWarehouseStock(currentWarehouseId);
                }
            } else {
                showAlert(data.message || 'Failed to create warehouse', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to create warehouse', 'error');
        });
    });

    // Transfer Modal
    function openTransferModal() {
        if (selectedItems.length === 0) {
            showAlert('Please select items to transfer', 'error');
            return;
        }

        if (!currentWarehouseId) {
            showAlert('Please select a warehouse first', 'error');
            return;
        }

        // Set from warehouse
        document.getElementById('fromWarehouse').value = currentWarehouseName;
        document.getElementById('fromWarehouseId').value = currentWarehouseId;

        // Populate to warehouse dropdown
        const toWarehouse = document.getElementById('toWarehouse');
        toWarehouse.innerHTML = '<option value="">Select destination...</option>';

        const warehouseSelector = document.getElementById('warehouseSelector');
        Array.from(warehouseSelector.options).forEach(option => {
            if (option.value && option.value !== currentWarehouseId) {
                const newOption = document.createElement('option');
                newOption.value = option.value;
                newOption.textContent = option.textContent;
                toWarehouse.appendChild(newOption);
            }
        });

        // Populate transfer items list
        const itemsList = document.getElementById('transferItemsList');
        itemsList.innerHTML = '';

        selectedItems.forEach((item, index) => {
            const itemHtml = `
                <div class="transfer-item">
                    <div class="transfer-item-info">
                        <div class="transfer-item-name">${item.product_name}</div>
                        ${item.variant_name ? `<div class="transfer-item-variant">${item.variant_name}</div>` : ''}
                        <div class="transfer-item-sku">${item.sku}</div>
                    </div>
                    <div class="transfer-item-available">
                        <div class="transfer-item-available-label">Available</div>
                        <div class="transfer-item-available-qty">${item.quantity}</div>
                    </div>
                    <div class="transfer-item-input">
                        <label>Transfer Qty</label>
                        <input type="number"
                               name="items[${index}][quantity]"
                               min="1"
                               max="${item.quantity}"
                               value="${item.quantity}"
                               required>
                        <input type="hidden" name="items[${index}][stock_id]" value="${item.id}">
                        <input type="hidden" name="items[${index}][type]" value="${item.type}">
                        <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        ${item.variant_id ? `<input type="hidden" name="items[${index}][variant_id]" value="${item.variant_id}">` : ''}
                    </div>
                </div>
            `;
            itemsList.insertAdjacentHTML('beforeend', itemHtml);
        });

        document.getElementById('transferModal').classList.add('show');
    }

    function closeTransferModal() {
        document.getElementById('transferModal').classList.remove('show');
        document.getElementById('transferForm').reset();
    }

    // Handle transfer form submission
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        // Validate quantities
        const inputs = this.querySelectorAll('input[type="number"]');
        let valid = true;

        inputs.forEach(input => {
            const value = parseInt(input.value);
            const max = parseInt(input.max);

            if (value > max) {
                valid = false;
                input.style.borderColor = '#e53e3e';
                setTimeout(() => input.style.borderColor = '', 3000);
            }
        });

        if (!valid) {
            showAlert('Transfer quantity cannot exceed available stock', 'error');
            return;
        }

        fetch('/admin/godown/transfer-stock', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message || 'Stock transferred successfully', 'success');
                closeTransferModal();

                // Clear selections and reload
                selectedItems = [];
                document.getElementById('selectAll').checked = false;
                updateSelectionUI();
                loadWarehouseStock(currentWarehouseId);
            } else {
                showAlert(data.message || 'Failed to transfer stock', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to transfer stock', 'error');
        });
    });

    // Alert function
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    // Show Laravel messages
    @if(session('success'))
        showAlert('{{ session('success') }}', 'success');
    @endif

    @if(session('error'))
        showAlert('{{ session('error') }}', 'error');
    @endif
</script>
@endpush
@endsection
