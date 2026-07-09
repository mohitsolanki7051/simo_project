@extends('layouts.admin')

@section('title', 'Warehouse Management - Admin Panel')
@section('header-title', 'Warehouse Management')

@section('content')
<div class="godown-container">
    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Warehouse Selector & Actions Bar -->
    <div class="control-bar">
        <div class="control-grid">
            <!-- Left Side: Warehouse List -->
            <div class="control-left">
                <div class="warehouse-selector">
                    <label>Warehouse List:</label>
                    <select id="warehouseSelect" class="select-warehouse" onchange="loadWarehouseStock()">
                        @if($warehouses->isEmpty())
                            <option value="">No warehouses available</option>
                        @else
                            @foreach($warehouses as $warehouse)
                                <option value="{{ (string)$warehouse->_id }}" {{ $mainWarehouse && (string)$warehouse->_id == (string)$mainWarehouse->_id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                    @if($warehouse->is_main)
                                        (Main)
                                    @endif
                                    @if($warehouse->status === 'inactive')
                                        (Inactive)
                                    @endif
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Center: Stats -->
            <div class="control-center">
                <div class="stats-display">
                    <div class="stat-item">
                        <span class="stat-label">Total Products:</span>
                        <span class="stat-value" id="totalProducts">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Value:</span>
                        <span class="stat-value" id="totalValue">₹0.00</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Low Stock:</span>
                        <span class="stat-value" id="lowStockItems">0</span>
                    </div>
                </div>
            </div>

            <!-- Right Side: Buttons -->
            <div class="control-right">
                <div class="action-buttons">
                    <button class="btn-transfer" id="transferBtn" onclick="openTransferModal()" disabled>
                        <span>🔄</span> Transfer Stock
                    </button>
                    <button class="btn-primary" onclick="openCreateWarehouseModal()">
                        + Create Warehouse
                    </button>
                </div>
            </div>
        </div>

        <!-- Compact Warehouse Details -->
        <div class="warehouse-info-compact" id="warehouseInfoCompact">
            <div class="warehouse-info-row">
                <span class="info-label">Name:</span>
                <span class="info-value" id="infoName">-</span>
            </div>
            <div class="warehouse-info-row">
                <span class="info-label">Code:</span>
                <span class="info-value" id="infoCode">-</span>
            </div>
            <div class="warehouse-info-row">
                <span class="info-label">Status:</span>
                <span class="info-value status-badge" id="infoStatus">-</span>
            </div>
            <div class="warehouse-info-row">
                <span class="info-label">Address:</span>
                <span class="info-value" id="infoAddress">-</span>
            </div>
            <div class="warehouse-info-row">
                <span class="info-label">City/State:</span>
                <span class="info-value" id="infoCityState">-</span>
            </div>
            <div class="warehouse-info-row">
                <span class="info-label">Pincode:</span>
                <span class="info-value" id="infoPincode">-</span>
            </div>
            <div class="warehouse-info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value" id="infoPhone">-</span>
            </div>
            <div class="warehouse-actions-compact">
                <button class="btn-icon btn-edit" onclick="openEditWarehouseModal()" title="Edit" {{ $warehouses->isEmpty() ? 'disabled' : '' }}>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </button>
                <button class="btn-icon btn-delete" onclick="deleteWarehouse()" title="Delete" {{ $warehouses->isEmpty() ? 'disabled' : '' }}>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 6h18"></path>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-header-left">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search products..." class="search-input" {{ $warehouses->isEmpty() ? 'disabled' : '' }}>
                    <span class="search-icon">🔍</span>
                </div>
                <div class="status-filter">
                    <select id="statusFilter" class="form-input" onchange="filterByStatus()" {{ $warehouses->isEmpty() ? 'disabled' : '' }}>
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAll" class="checkbox" onchange="toggleSelectAll()" {{ $warehouses->isEmpty() ? 'disabled' : '' }}>
                        </th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Min Stock</th>
                        <th class="text-right">Stock Value</th>
                        <th class="text-right">Sale Price</th>
                        <th class="text-right">Cost Price</th>
                    </tr>
                </thead>
                <tbody id="stockTableBody">
                    @if($warehouses->isEmpty())
                        <tr>
                            <td colspan="10" class="empty-state">
                                <div class="empty-state-content">
                                    <span class="empty-icon">🏢</span>
                                    <h3>No Warehouses Found</h3>
                                    <p>Create your first warehouse to start managing inventory</p>
                                    <button class="btn-primary" onclick="openCreateWarehouseModal()">
                                        + Create Warehouse
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="10" class="loading-state">
                                <div class="spinner"></div>
                                <p>Loading stock data...</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Warehouse Modal -->
<div class="modal" id="createWarehouseModal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title">Create New Warehouse</h3>
            <button class="modal-close" onclick="closeCreateWarehouseModal()">&times;</button>
        </div>
        <form id="createWarehouseForm" onsubmit="handleCreateWarehouse(event)">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Warehouse Name *</label>
                        <input type="text" name="name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Warehouse Code *</label>
                        <input type="text" name="code" class="form-input" pattern="\d{4}" maxlength="4" required placeholder="0001" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        <small class="form-help">Must be exactly 4 numeric digits (0-9)</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-input" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Address *</label>
                        <textarea name="address" class="form-input" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">City *</label>
                        <input type="text" name="city" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">State *</label>
                        <input type="text" name="state" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pincode *</label>
                        <input type="text" name="pincode" class="form-input" pattern="\d{6}" maxlength="6" required oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        <small class="form-help">Must be exactly 6 numeric digits</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone *</label>
                        <input type="text" name="phone" class="form-input" pattern="\d{10}" maxlength="10" required oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        <small class="form-help">Must be exactly 10 numeric digits</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Manager Name</label>
                        <input type="text" name="manager_name" class="form-input">
                    </div>
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_main" value="1">
                            <span>Set as Main Warehouse</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCreateWarehouseModal()">Cancel</button>
                <button type="submit" class="btn-primary">Create Warehouse</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Warehouse Modal -->
<div class="modal" id="editWarehouseModal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title">Edit Warehouse</h3>
            <button class="modal-close" onclick="closeEditWarehouseModal()">&times;</button>
        </div>
        <form id="editWarehouseForm" onsubmit="handleEditWarehouse(event)">
            <input type="hidden" name="id" id="editWarehouseId">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Warehouse Name *</label>
                        <input type="text" name="name" id="editName" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Warehouse Code *</label>
                        <input type="text" name="code" id="editCode" class="form-input" pattern="\d{4}" maxlength="4" required oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        <small class="form-help">Must be exactly 4 numeric digits (0-9)</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status *</label>
                        <select name="status" id="editStatus" class="form-input" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Address *</label>
                        <textarea name="address" id="editAddress" class="form-input" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">City *</label>
                        <input type="text" name="city" id="editCity" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">State *</label>
                        <input type="text" name="state" id="editState" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pincode *</label>
                        <input type="text" name="pincode" id="editPincode" class="form-input" pattern="\d{6}" maxlength="6" required oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        <small class="form-help">Must be exactly 6 numeric digits</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone *</label>
                        <input type="text" name="phone" id="editPhone" class="form-input" pattern="\d{10}" maxlength="10" required oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        <small class="form-help">Must be exactly 10 numeric digits</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="editEmail" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Manager Name</label>
                        <input type="text" name="manager_name" id="editManagerName" class="form-input">
                    </div>
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_main" id="editIsMain" value="1">
                            <span>Set as Main Warehouse</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditWarehouseModal()">Cancel</button>
                <button type="submit" class="btn-primary">Update Warehouse</button>
            </div>
        </form>
    </div>
</div>

<!-- Transfer Stock Modal -->
<div class="modal" id="transferModal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title">Transfer Stock</h3>
            <button class="modal-close" onclick="closeTransferModal()">&times;</button>
        </div>
        <form id="transferForm" onsubmit="handleTransfer(event)">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">From Warehouse</label>
                        <input type="text" id="fromWarehouse" class="form-input" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To Warehouse *</label>
                        <select name="to_warehouse_id" id="toWarehouse" class="form-input" required>
                            <option value="">Select Warehouse</option>
                        </select>
                    </div>
                </div>

                <div class="transfer-items-section">
                    <h4 class="section-title">Items to Transfer (<span id="selectedItemCount">0</span> selected)</h4>
                    <div id="transferItemsList"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeTransferModal()">Cancel</button>
                <button type="submit" class="btn-primary">Transfer Stock</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .godown-container {
        background: #f5f5f5;
        min-height: 100vh;
        font-size: 14px;
    }

    #alertContainer {
        position: fixed;
        top: 70px;
        right: 20px;
        z-index: 9999;
        max-width: 350px;
    }

    .alert {
        padding: 10px 15px;
        border-radius: 6px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease;
        font-size: 13px;
        font-weight: 500;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(50px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .alert-success { background: #d4edda; color: #155724; border-left: 3px solid #28a745; }
    .alert-error { background: #f8d7da; color: #721c24; border-left: 3px solid #dc3545; }

    /* Control Bar Layout */
    .control-bar {
        background: white;
        padding: 12px 15px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }

    .control-grid {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: nowrap;
        padding-bottom: 10px;
        border-bottom: 1px dashed #e0e0e0;
    }

    .control-left { flex: 0 0 auto; min-width: 250px; }
    .control-center { flex: 1; display: flex; justify-content: center; }
    .control-right { flex: 0 0 auto; min-width: 200px; }

    .warehouse-selector {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .warehouse-selector label {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        white-space: nowrap;
    }

    .select-warehouse {
        padding: 6px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        min-width: 200px;
        background: white;
        cursor: pointer;
    }

    .select-warehouse:focus {
        outline: none;
        border-color: #007bff;
    }

    /* Stats Display */
    .stats-display {
        display: flex;
        gap: 30px;
        align-items: center;
        justify-content: center;
        padding: 5px 0;
    }

    .stat-item {
        display: flex;
        flex-direction: column;
        gap: 3px;
        align-items: center;
        min-width: 100px;
    }

    .stat-label {
        font-size: 11px;
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }

    #lowStockItems { color: #dc3545; font-weight: 700; }
    #totalValue { color: #28a745; font-weight: 700; }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .btn-primary, .btn-transfer {
        padding: 6px 15px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-primary {
        background: #f98825;
        color: white;
    }

    .btn-transfer {
        background: #7c3aed;
        color: white;
    }


    .btn-transfer:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #6c757d;
    }

    /* Warehouse Info Compact */
    .warehouse-info-compact {
        display: flex;
        flex-wrap: wrap;
        gap: 15px 20px;
        align-items: center;
        padding: 8px 0 0 0;
        margin-top: 8px;
    }

    .warehouse-info-row {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
    }

    .info-label {
        font-weight: 600;
        color: #495057;
        white-space: nowrap;
    }

    .info-value {
        color: #333;
        font-weight: 500;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .warehouse-actions-compact {
        display: flex;
        gap: 5px;
        margin-left: auto;
    }

    /* Status Badges */
    .status-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #f1f5f9;
        color: #64748b;
    }

    /* Icon Buttons */
    .btn-icon {
        width: 28px;
        height: 28px;
        border: none;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-icon:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-edit {
        background: #e3f2fd;
        color: #1976d2;
    }

    .btn-edit:hover:not(:disabled) {
        background: #bbdefb;
    }

    .btn-delete {
        background: #ffebee;
        color: #d32f2f;
    }

    .btn-delete:hover:not(:disabled) {
        background: #ffcdd2;
    }

    /* Table Styles */
    .table-card {
        background: white;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-top: 15px;
    }

    .table-header {
        padding: 10px 15px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-header-left {
        display: flex;
        align-items: center;
        gap: 15px;
        flex: 1;
    }

    .search-box {
        position: relative;
    }

    .search-input {
        padding: 6px 30px 6px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        width: 250px;
        font-size: 14px;
    }

    .search-input:focus {
        outline: none;
        border-color: #007bff;
    }

    .search-input:disabled {
        background: #f5f5f5;
        cursor: not-allowed;
    }

    .search-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        color: #999;
    }

    .status-filter select {
        padding: 6px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        min-width: 150px;
    }

    .status-filter select:disabled {
        background: #f5f5f5;
        cursor: not-allowed;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .data-table thead {
        background: #f8f9fa;
    }

    .data-table th {
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
        color: #495057;
        font-size: 12px;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }

    .data-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e9ecef;
        color: #333;
        font-size: 13px;
        white-space: nowrap;
    }

    .data-table tbody tr {
        transition: background 0.2s;
    }

    .data-table tbody tr:hover {
        background: #f8f9fa;
    }



    /* Simple product rows */
    .simple-row {
        background: white;
    }

    /* Variant group header */
    .variant-group-header {
        background: #f0f8ff !important;
        border-top: 1px solid #cce7ff;
        border-bottom: 1px solid #cce7ff;
    }

    .variant-group-header td {
        padding: 10px 12px !important;
        font-weight: 600 !important;
        color: #0066cc !important;
        font-size: 13px !important;
        background: #f0f8ff;
    }

    .variant-group-header:hover {
        background: #f0f8ff !important;
    }

    /* Variant product rows */
    .variant-row {
        background: white;
    }

    .variant-row td:nth-child(2) {
        padding-left: 25px;
    }

    .checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .checkbox:disabled {
        cursor: not-allowed;
        opacity: 0.4;
    }

    /* Empty and Loading States */
    .loading-state,
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .empty-state-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .empty-state h3 {
        font-size: 18px;
        color: #333;
        margin: 0;
    }

    .empty-state p {
        font-size: 14px;
        color: #666;
        margin: 5px 0 15px;
    }

    .spinner {
        border: 3px solid #f0f0f0;
        border-top: 3px solid #007bff;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 0.8s linear infinite;
        margin: 0 auto 15px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 6px;
        max-width: 700px;
        width: 90%;
        max-height: 90vh;
        overflow: auto;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }

    .modal-large {
        max-width: 800px;
    }

    .modal-header {
        padding: 12px 15px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        background: white;
        z-index: 1;
    }

    .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #999;
        cursor: pointer;
        line-height: 1;
        padding: 0;
        width: 30px;
        height: 30px;
    }

    .modal-close:hover {
        color: #333;
    }

    .modal-body {
        padding: 15px;
    }

    .modal-footer {
        padding: 12px 15px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        position: sticky;
        bottom: 0;
        background: white;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        font-size: 13px;
        color: #495057;
        font-weight: 600;
    }

    .form-input {
        padding: 6px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .form-input:focus {
        outline: none;
        border-color: #007bff;
    }

    .form-input[readonly] {
        background: #f5f5f5;
        cursor: not-allowed;
    }

    .form-help {
        font-size: 12px;
        color: #6c757d;
        margin-top: 3px;
    }

    .checkbox-group {
        align-items: flex-start;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 14px;
    }

    .btn-secondary {
        padding: 6px 15px;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    .transfer-items-section {
        margin-top: 20px;
    }

    .section-title {
        font-size: 15px;
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e0e0e0;
    }

    .transfer-item {
        padding: 12px;
        background: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 12px;
        border: 1px solid #e9ecef;
    }

    .transfer-item-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .transfer-item-body {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    @media (max-width: 1024px) {
        .control-grid {
            flex-wrap: wrap;
            gap: 15px;
        }
        .control-left, .control-center, .control-right {
            width: 100%;
            min-width: auto;
        }
        .stats-display {
            justify-content: flex-start;
        }
        .action-buttons {
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .form-grid, .transfer-item-body {
            grid-template-columns: 1fr;
        }
        .control-grid {
            grid-template-columns: 1fr !important;
            gap: 12px;
        }
        .stats-display {
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 8px;
            width: 100%;
        }
        .stat-item {
            padding: 8px 10px !important;
            text-align: center;
        }
        .stat-label {
            font-size: 10px !important;
        }
        .stat-value {
            font-size: 13px !important;
        }
        .warehouse-selector {
            flex-direction: column;
            align-items: flex-start;
        }
        .select-warehouse {
            width: 100%;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            width: 100%;
        }
        .btn-primary, .btn-transfer {
            flex: 1;
            width: 100%;
            justify-content: center;
        }
        .warehouse-info-row {
            width: 100%;
            justify-content: space-between;
        }
        .warehouse-actions-compact {
            margin-left: 0;
            margin-top: 10px;
        }

        /* Stock Table -> Card View */
        .table-wrapper {
            background: transparent;
            border: none;
            box-shadow: none;
            overflow-x: visible;
        }
        .data-table {
            min-width: 100%;
            display: block;
            border: none;
        }
        .data-table thead {
            display: none;
        }
        .data-table tbody {
            display: block;
            width: 100%;
        }
        .data-table tr {
            position: relative;
        }
        .data-table tr.simple-row, .data-table tr.variant-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            background: #fff;
            border-radius: 8px;
            padding: 12px 14px 10px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        .data-table tr.variant-group-header {
            display: block;
            background: #f1f5f9;
            padding: 8px 12px;
            margin-top: 12px;
            margin-bottom: 6px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-weight: 700;
            color: #334155;
            font-size: 12px;
        }
        .data-table tr.variant-group-header td {
            display: block;
            width: 100%;
            border: none;
            padding: 0;
        }
        .data-table tr.simple-row:hover td, .data-table tr.variant-row:hover td {
            background: transparent;
        }
        .data-table td {
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
        }

        /* td 1: Checkbox */
        .data-table td:nth-child(1) {
            width: 30px;
            order: 1;
        }
        /* td 2: Product Name */
        .data-table td:nth-child(2) {
            flex: 1;
            min-width: 0;
            order: 2;
        }
        .product-info .product-name {
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
        }
        .product-info .variant-name {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        /* td 5: Status Badge -> absolute top right */
        .data-table td:nth-child(5) {
            width: auto;
            order: 3;
            position: absolute;
            top: 12px;
            right: 14px;
        }
        .status-badge {
            font-size: 9px;
            padding: 2px 7px;
        }

        /* td 3: SKU, td 4: Unit */
        .data-table td:nth-child(3) {
            width: auto;
            order: 4;
            margin-top: 4px;
            margin-right: 10px;
            font-size: 11px;
            color: #64748b;
        }
        .data-table td:nth-child(3)::before {
            content: "SKU: ";
            font-weight: 600;
        }
        .data-table td:nth-child(4) {
            width: auto;
            order: 5;
            margin-top: 4px;
            font-size: 11px;
            color: #64748b;
        }
        .data-table td:nth-child(4)::before {
            content: "Unit: ";
            font-weight: 600;
        }

        /* td 6: Stock, td 7: Min Stock, td 8: Stock Value */
        .data-table td:nth-child(6) {
            width: 33.33%;
            order: 6;
            flex-direction: column;
            align-items: flex-start;
            margin-top: 8px;
            border-top: 1px dashed #f1f5f9;
            padding-top: 8px;
        }
        .data-table td:nth-child(6)::before {
            content: "Stock";
            font-size: 8px;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .data-table td:nth-child(7) {
            width: 33.33%;
            order: 7;
            flex-direction: column;
            align-items: flex-start;
            margin-top: 8px;
            border-top: 1px dashed #f1f5f9;
            padding-top: 8px;
        }
        .data-table td:nth-child(7)::before {
            content: "Min Stock";
            font-size: 8px;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .data-table td:nth-child(8) {
            width: 33.33%;
            order: 8;
            flex-direction: column;
            align-items: flex-start;
            margin-top: 8px;
            border-top: 1px dashed #f1f5f9;
            padding-top: 8px;
        }
        .data-table td:nth-child(8)::before {
            content: "Stock Value";
            font-size: 8px;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        /* td 9: Sale Price, td 10: Cost Price */
        .data-table td:nth-child(9) {
            width: 50%;
            order: 9;
            flex-direction: column;
            align-items: flex-start;
            margin-top: 8px;
            border-top: 1px dashed #f1f5f9;
            padding-top: 8px;
        }
        .data-table td:nth-child(9)::before {
            content: "Sale Price";
            font-size: 8px;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .data-table td:nth-child(10) {
            width: 50%;
            order: 10;
            flex-direction: column;
            align-items: flex-start;
            margin-top: 8px;
            border-top: 1px dashed #f1f5f9;
            padding-top: 8px;
        }
        .data-table td:nth-child(10)::before {
            content: "Cost Price";
            font-size: 8px;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let selectedItems = [];
    let currentWarehouseId = '{{ $mainWarehouse ? (string)$mainWarehouse->_id : "" }}';
    let warehouses = [];
    let currentWarehouseData = null;
    let warehouseDataMap = {};
    let currentStockData = [];

    document.addEventListener('DOMContentLoaded', function() {
        initializeWarehouseData();
    });

    function initializeWarehouseData() {
        const selectElement = document.getElementById('warehouseSelect');

        if (selectElement) {
            // Clear existing data
            warehouses = [];
            warehouseDataMap = {};

            @foreach($warehouses as $warehouse)
                warehouseDataMap['{{ (string)$warehouse->_id }}'] = {
                    _id: '{{ (string)$warehouse->_id }}',
                    id: '{{ (string)$warehouse->_id }}',
                    name: '{{ addslashes($warehouse->name) }}',
                    code: '{{ addslashes($warehouse->code) }}',
                    address: '{{ addslashes($warehouse->address) }}',
                    city: '{{ addslashes($warehouse->city) }}',
                    state: '{{ addslashes($warehouse->state) }}',
                    pincode: '{{ $warehouse->pincode }}',
                    phone: '{{ $warehouse->phone }}',
                    email: '{{ $warehouse->email ?? "" }}',
                    manager_name: '{{ addslashes($warehouse->manager_name ?? "") }}',
                    status: '{{ $warehouse->status }}',
                    is_main: {{ $warehouse->is_main ? 'true' : 'false' }},
                };
            @endforeach

            // Build warehouses array from options
            Array.from(selectElement.options).forEach(option => {
                if (option.value) {
                    const text = option.textContent;
                    warehouses.push({
                        _id: option.value,
                        name: text.replace(' (Main)', '').replace(' (Inactive)', ''),
                        is_main: text.includes('(Main)'),
                        status: text.includes('(Inactive)') ? 'inactive' : 'active'
                    });
                }
            });

            if (currentWarehouseId && warehouseDataMap[currentWarehouseId]) {
                loadWarehouseStock();
                loadWarehouseDetails();
            } else if (warehouses.length > 0) {
                // Select first warehouse if current not found
                currentWarehouseId = warehouses[0]._id;
                selectElement.value = currentWarehouseId;
                loadWarehouseStock();
                loadWarehouseDetails();
            }
        }
    }

    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    function loadWarehouseStock() {
        const warehouseId = document.getElementById('warehouseSelect').value;

        if (!warehouseId) {
            return;
        }

        currentWarehouseId = warehouseId;

        // Clear selected items when warehouse changes
        selectedItems = [];
        document.getElementById('selectAll').checked = false;
        updateTransferButton();

        const tbody = document.getElementById('stockTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="loading-state">
                    <div class="spinner"></div>
                    <p>Loading stock data...</p>
                </td>
            </tr>
        `;

        fetch(`/admin/warehouses/${warehouseId}/stock`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                currentStockData = data.stock;
                renderStockTable(data.stock);
                updateStats(data.stats);
            } else {
                tbody.innerHTML = `<tr><td colspan="10" class="empty-state">${data.message}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = `<tr><td colspan="10" class="empty-state">Failed to load stock data</td></tr>`;
        });

        // Also load warehouse details
        loadWarehouseDetails();
    }

    function loadWarehouseDetails() {
        const warehouse = warehouseDataMap[currentWarehouseId];

        if (warehouse) {
            currentWarehouseData = warehouse;
            updateWarehouseDetails(warehouse);
        } else {
            resetWarehouseDetails();
        }
    }

    function updateWarehouseDetails(warehouse) {
        document.getElementById('infoName').textContent = warehouse.name || '-';
        document.getElementById('infoCode').textContent = warehouse.code || '-';
        document.getElementById('infoAddress').textContent = warehouse.address || '-';
        document.getElementById('infoCityState').textContent = (warehouse.city || '') + (warehouse.city && warehouse.state ? ', ' : '') + (warehouse.state || '');
        document.getElementById('infoPincode').textContent = warehouse.pincode || '-';
        document.getElementById('infoPhone').textContent = warehouse.phone || '-';

        const statusElement = document.getElementById('infoStatus');
        statusElement.textContent = warehouse.status === 'active' ? 'Active' : 'Inactive';
        statusElement.className = `info-value status-badge ${warehouse.status === 'active' ? 'status-active' : 'status-inactive'}`;
    }

    function resetWarehouseDetails() {
        document.getElementById('infoName').textContent = '-';
        document.getElementById('infoCode').textContent = '-';
        document.getElementById('infoAddress').textContent = '-';
        document.getElementById('infoCityState').textContent = '-';
        document.getElementById('infoPincode').textContent = '-';
        document.getElementById('infoPhone').textContent = '-';
        document.getElementById('infoStatus').textContent = '-';
        document.getElementById('infoStatus').className = 'info-value';
    }

    function renderStockTable(stockData) {
        const tbody = document.getElementById('stockTableBody');

        if (!stockData || stockData.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="empty-state">
                        <div class="empty-state-content">
                            <span class="empty-icon">📦</span>
                            <h3>No Stock Available</h3>
                            <p>This warehouse currently has no products in stock</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';

        const simpleProducts = stockData.filter(item => item.type === 'simple');
        const variantProducts = stockData.filter(item => item.type === 'variant_parent');

        // RENDER SIMPLE PRODUCTS
        simpleProducts.forEach(item => {
            const productStatus = item.product_status || 'active';

            html += `
                <tr class="simple-row" data-stock-id="${item.stock_id}" data-status="${productStatus}">
                    <td style="text-align: center;">
                        <input type="checkbox" class="checkbox row-checkbox"
                            data-stock-id="${item.stock_id}"
                            data-product-id="${item.product_id}"
                            data-product-type="simple"
                            data-quantity="${item.quantity}"
                            data-product-name="${item.product_name}"
                            data-sku="${item.sku}"
                            ${item.quantity === 0 ? 'disabled' : ''}
                            onchange="handleCheckboxChange(this)">
                    </td>
                    <td>
                        <div class="product-info">
                            <span class="product-name">${item.product_name}</span>
                        </div>
                    </td>
                    <td><span class="sku-code">${item.sku}</span></td>
                    <td>${item.unit}</td>
                    <td>
                        <span class="status-badge ${productStatus === 'active' ? 'status-active' : 'status-inactive'}">
                            ${productStatus === 'active' ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="text-right">
                        <span class="stock-quantity ${item.quantity <= item.min_stock_alert ? 'low-stock' : ''}">
                            ${item.quantity.toLocaleString('en-IN')}
                        </span>
                    </td>
                    <td class="text-right">${item.min_stock_alert}</td>
                    <td class="text-right stock-value">₹${item.stock_value.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-right price">₹${item.sale_price.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-right price">₹${item.cost_price.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                </tr>
            `;
        });

        // RENDER VARIANT PRODUCTS
        variantProducts.forEach(item => {
            if (!item.variants || item.variants.length === 0) return;

            const productStatus = item.product_status || 'active';

            // Variant group header
            html += `
                <tr class="variant-group-header" data-status="${productStatus}">
                    <td colspan="10">
                        <span class="variant-group-icon">▶</span>
                        ${item.product_name} <span class="variant-badge">Variant Product</span>
                    </td>
                </tr>
            `;

            // Variant rows
            item.variants.forEach(variant => {
                const displayName = variant.variant_name || `Variant`;

                html += `
                    <tr class="variant-row" data-stock-id="${variant.stock_id || ''}" data-status="${productStatus}">
                        <td style="text-align: center;">
                            ${variant.stock_id ? `
                            <input type="checkbox" class="checkbox row-checkbox"
                                data-stock-id="${variant.stock_id}"
                                data-product-id="${item.product_id}"
                                data-product-type="variant"
                                data-variant-id="${variant.variant_id}"
                                data-quantity="${variant.quantity}"
                                data-product-name="${item.product_name}"
                                data-variant-name="${variant.variant_name}"
                                data-sku="${variant.sku}"
                                ${variant.quantity === 0 ? 'disabled' : ''}
                                onchange="handleCheckboxChange(this)">
                            ` : ''}
                        </td>
                        <td>
                            <div class="product-info">
                                <span class="variant-name">↳ ${displayName}</span>
                            </div>
                        </td>
                        <td><span class="sku-code">${variant.sku}</span></td>
                        <td>${variant.unit}</td>
                        <td>
                            <span class="status-badge ${productStatus === 'active' ? 'status-active' : 'status-inactive'}">
                                ${productStatus === 'active' ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td class="text-right">
                            <span class="stock-quantity ${variant.quantity <= variant.min_stock_alert ? 'low-stock' : ''}">
                                ${variant.quantity.toLocaleString('en-IN')}
                            </span>
                        </td>
                        <td class="text-right">${variant.min_stock_alert}</td>
                        <td class="text-right stock-value">₹${variant.stock_value.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-right price">₹${variant.sale_price.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-right price">₹${variant.cost_price.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    </tr>
                `;
            });
        });

        tbody.innerHTML = html;
    }

    function filterByStatus() {
        const statusFilter = document.getElementById('statusFilter').value;
        const allRows = document.querySelectorAll('#stockTableBody tr');

        allRows.forEach(row => {
            if (row.classList.contains('variant-group-header')) {
                let hasMatchingChild = false;
                let nextRow = row.nextElementSibling;

                while (nextRow && nextRow.classList.contains('variant-row')) {
                    const rowStatus = nextRow.dataset.status;
                    if (statusFilter === 'all' || rowStatus === statusFilter) {
                        hasMatchingChild = true;
                    }
                    nextRow = nextRow.nextElementSibling;
                }

                const headerStatus = row.dataset.status;
                const headerMatches = statusFilter === 'all' || headerStatus === statusFilter;
                row.style.display = (hasMatchingChild || headerMatches) ? '' : 'none';
            } else {
                const rowStatus = row.dataset.status;
                const matches = statusFilter === 'all' || rowStatus === statusFilter;
                row.style.display = matches ? '' : 'none';

                if (matches && row.classList.contains('variant-row')) {
                    let prevRow = row.previousElementSibling;
                    while (prevRow && !prevRow.classList.contains('variant-group-header')) {
                        prevRow = prevRow.previousElementSibling;
                    }
                    if (prevRow && prevRow.classList.contains('variant-group-header')) {
                        prevRow.style.display = '';
                    }
                }
            }
        });
    }

    function updateStats(stats) {
        document.getElementById('totalProducts').textContent = stats.total_products;
        document.getElementById('totalValue').textContent = '₹' + parseFloat(stats.total_value).toLocaleString('en-IN', { minimumFractionDigits: 2 });
        document.getElementById('lowStockItems').textContent = stats.low_stock_items;
    }

    function handleCheckboxChange(checkbox) {
        const data = {
            stock_id: checkbox.dataset.stockId,
            product_id: checkbox.dataset.productId,
            product_type: checkbox.dataset.productType,
            variant_id: checkbox.dataset.variantId || null,
            quantity: parseInt(checkbox.dataset.quantity),
            product_name: checkbox.dataset.productName,
            variant_name: checkbox.dataset.variantName || null,
            sku: checkbox.dataset.sku,
        };

        if (checkbox.checked) {
            selectedItems.push(data);
        } else {
            selectedItems = selectedItems.filter(item => item.stock_id !== data.stock_id);
        }

        updateTransferButton();
    }

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const allCheckboxes = document.querySelectorAll('.row-checkbox:not(:disabled)');

        selectedItems = [];

        allCheckboxes.forEach(cb => {
            cb.checked = selectAll.checked;
            if (selectAll.checked) {
                const data = {
                    stock_id: cb.dataset.stockId,
                    product_id: cb.dataset.productId,
                    product_type: cb.dataset.productType,
                    variant_id: cb.dataset.variantId || null,
                    quantity: parseInt(cb.dataset.quantity),
                    product_name: cb.dataset.productName,
                    variant_name: cb.dataset.variantName || null,
                    sku: cb.dataset.sku,
                };
                selectedItems.push(data);
            }
        });

        updateTransferButton();
    }

    function updateTransferButton() {
        const transferBtn = document.getElementById('transferBtn');
        transferBtn.disabled = selectedItems.length === 0;
    }

    function openTransferModal() {
        if (selectedItems.length === 0) {
            showAlert('Please select items to transfer', 'error');
            return;
        }

        const currentWarehouse = warehouses.find(w => w._id === currentWarehouseId);
        if (!currentWarehouse) {
            showAlert('Current warehouse not found', 'error');
            return;
        }

        document.getElementById('fromWarehouse').value = currentWarehouse.name + (currentWarehouse.is_main ? ' (Main)' : '');

        const toWarehouseSelect = document.getElementById('toWarehouse');
        toWarehouseSelect.innerHTML = '<option value="">Select Warehouse</option>';

        warehouses.forEach(warehouse => {
            if (warehouse._id !== currentWarehouseId && warehouse.status === 'active') {
                const option = document.createElement('option');
                option.value = warehouse._id;
                option.textContent = warehouse.name + (warehouse.is_main ? ' (Main)' : '');
                toWarehouseSelect.appendChild(option);
            }
        });

        renderTransferItems();
        document.getElementById('transferModal').classList.add('show');
    }

    function closeTransferModal() {
        document.getElementById('transferModal').classList.remove('show');
        document.getElementById('transferForm').reset();
    }

    function renderTransferItems() {
        const container = document.getElementById('transferItemsList');
        document.getElementById('selectedItemCount').textContent = selectedItems.length;

        let html = '';

        selectedItems.forEach(item => {
            const displayName = item.variant_name
                ? `${item.product_name} - ${item.variant_name}`
                : item.product_name;

            html += `
                <div class="transfer-item">
                    <div class="transfer-item-header">
                        <span>${displayName}</span>
                        <span>Available: ${item.quantity}</span>
                    </div>
                    <div class="transfer-item-body">
                        <div class="form-group">
                            <label class="form-label">SKU</label>
                            <input type="text" class="form-input" value="${item.sku}" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Transfer Quantity *</label>
                            <input type="number" class="form-input transfer-quantity"
                                data-stock-id="${item.stock_id}"
                                min="1" max="${item.quantity}" value="1" required>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function handleTransfer(event) {
        event.preventDefault();

        const toWarehouseId = document.getElementById('toWarehouse').value;
        const fromWarehouseId = currentWarehouseId;

        if (!toWarehouseId) {
            showAlert('Please select destination warehouse', 'error');
            return;
        }

        const items = [];
        const quantityInputs = document.querySelectorAll('.transfer-quantity');

        quantityInputs.forEach(input => {
            const stockId = input.dataset.stockId;
            const quantity = parseInt(input.value);
            const item = selectedItems.find(i => i.stock_id === stockId);

            if (item && quantity > 0) {
                items.push({
                    stock_id: stockId,
                    product_id: item.product_id,
                    product_type: item.product_type,
                    variant_id: item.variant_id,
                    quantity: quantity
                });
            }
        });

        if (items.length === 0) {
            showAlert('Please enter transfer quantities', 'error');
            return;
        }

        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Processing...';
        submitBtn.disabled = true;

        fetch('/admin/warehouses/transfer-stock', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                from_warehouse_id: fromWarehouseId,
                to_warehouse_id: toWarehouseId,
                items: items
            })
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;

            if (data.success) {
                showAlert(data.message, 'success');
                closeTransferModal();
                loadWarehouseStock();
                document.getElementById('selectAll').checked = false;
            } else {
                showAlert(data.message || 'Transfer failed', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            showAlert('Transfer failed', 'error');
        });
    }

    function openCreateWarehouseModal() {
        document.getElementById('createWarehouseModal').classList.add('show');
    }

    function closeCreateWarehouseModal() {
        document.getElementById('createWarehouseModal').classList.remove('show');
        document.getElementById('createWarehouseForm').reset();
    }

    function handleCreateWarehouse(event) {
        event.preventDefault();
        const formData = new FormData(event.target);

        // Validate inputs
        const code = formData.get('code');
        const pincode = formData.get('pincode');
        const phone = formData.get('phone');

        if (!/^\d{4}$/.test(code)) {
            showAlert('Warehouse code must be exactly 4 digits', 'error');
            return;
        }

        if (!/^\d{6}$/.test(pincode)) {
            showAlert('Pincode must be exactly 6 digits', 'error');
            return;
        }

        if (!/^\d{10}$/.test(phone)) {
            showAlert('Phone number must be exactly 10 digits', 'error');
            return;
        }

        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Creating...';
        submitBtn.disabled = true;

        fetch('/admin/warehouses/create', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;

            if (data.success) {
                showAlert(data.message, 'success');
                closeCreateWarehouseModal();

                // Add to warehouseDataMap
                if (data.warehouse) {
                    warehouseDataMap[data.warehouse._id] = data.warehouse;

                    // Remove "No warehouses available" option if it exists
                    const select = document.getElementById('warehouseSelect');
                    if (select.options.length === 1 && select.options[0].value === '') {
                        select.innerHTML = '';
                    }

                    // Add to dropdown
                    const option = document.createElement('option');
                    option.value = data.warehouse._id;
                    let optionText = data.warehouse.name;
                    if (data.warehouse.is_main) optionText += ' (Main)';
                    if (data.warehouse.status === 'inactive') optionText += ' (Inactive)';
                    option.textContent = optionText;
                    select.appendChild(option);

                    // Update warehouses array
                    warehouses.push({
                        _id: data.warehouse._id,
                        name: data.warehouse.name,
                        is_main: data.warehouse.is_main,
                        status: data.warehouse.status
                    });

                    // Select the new warehouse
                    select.value = data.warehouse._id;
                    currentWarehouseId = data.warehouse._id;

                    // Enable buttons and inputs
                    document.querySelectorAll('.btn-edit, .btn-delete, #searchInput, #statusFilter, #selectAll').forEach(el => {
                        el.disabled = false;
                    });

                    loadWarehouseStock();
                }
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            showAlert('Failed to create warehouse', 'error');
        });
    }

    function openEditWarehouseModal() {
        if (!currentWarehouseData) {
            showAlert('Warehouse data not loaded', 'error');
            return;
        }

        document.getElementById('editWarehouseId').value = currentWarehouseData._id;
        document.getElementById('editName').value = currentWarehouseData.name;
        document.getElementById('editCode').value = currentWarehouseData.code;
        document.getElementById('editAddress').value = currentWarehouseData.address;
        document.getElementById('editCity').value = currentWarehouseData.city;
        document.getElementById('editState').value = currentWarehouseData.state;
        document.getElementById('editPincode').value = currentWarehouseData.pincode;
        document.getElementById('editPhone').value = currentWarehouseData.phone;
        document.getElementById('editEmail').value = currentWarehouseData.email || '';
        document.getElementById('editManagerName').value = currentWarehouseData.manager_name || '';
        document.getElementById('editStatus').value = currentWarehouseData.status;
        document.getElementById('editIsMain').checked = currentWarehouseData.is_main;

        document.getElementById('editWarehouseModal').classList.add('show');
    }

    function closeEditWarehouseModal() {
        document.getElementById('editWarehouseModal').classList.remove('show');
    }

    function handleEditWarehouse(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const warehouseId = formData.get('id');

        // Validate inputs
        const code = formData.get('code');
        const pincode = formData.get('pincode');
        const phone = formData.get('phone');

        if (!/^\d{4}$/.test(code)) {
            showAlert('Warehouse code must be exactly 4 digits', 'error');
            return;
        }

        if (!/^\d{6}$/.test(pincode)) {
            showAlert('Pincode must be exactly 6 digits', 'error');
            return;
        }

        if (!/^\d{10}$/.test(phone)) {
            showAlert('Phone number must be exactly 10 digits', 'error');
            return;
        }

        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Updating...';
        submitBtn.disabled = true;

        fetch(`/admin/warehouses/${warehouseId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;

            if (data.success) {
                showAlert(data.message, 'success');
                closeEditWarehouseModal();

                // Update local data
                if (data.warehouse) {
                    // Update warehouseDataMap
                    warehouseDataMap[warehouseId] = data.warehouse;

                    // Update dropdown option
                    const warehouseSelect = document.getElementById('warehouseSelect');
                    const warehouseOption = warehouseSelect.querySelector(`option[value="${warehouseId}"]`);

                    if (warehouseOption) {
                        let newText = data.warehouse.name;
                        if (data.warehouse.is_main) newText += ' (Main)';
                        if (data.warehouse.status === 'inactive') newText += ' (Inactive)';
                        warehouseOption.textContent = newText;
                    }

                    // Update warehouses array
                    const warehouseIndex = warehouses.findIndex(w => w._id === warehouseId);
                    if (warehouseIndex !== -1) {
                        warehouses[warehouseIndex] = {
                            _id: warehouseId,
                            name: data.warehouse.name,
                            is_main: data.warehouse.is_main,
                            status: data.warehouse.status
                        };
                    }

                    // Reload current warehouse details
                    if (currentWarehouseId === warehouseId) {
                        currentWarehouseData = data.warehouse;
                        updateWarehouseDetails(data.warehouse);
                    }
                }
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            showAlert('Failed to update warehouse', 'error');
        });
    }

    function deleteWarehouse() {
        if (!currentWarehouseId) {
            showAlert('No warehouse selected', 'error');
            return;
        }

        if (!confirm('Are you sure you want to delete this warehouse?')) {
            return;
        }

        fetch(`/admin/warehouses/${currentWarehouseId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');

                const select = document.getElementById('warehouseSelect');

                // Remove from dropdown
                const option = select.querySelector(`option[value="${currentWarehouseId}"]`);
                if (option) {
                    option.remove();
                }

                // Remove from warehouses array and map
                warehouses = warehouses.filter(w => w._id !== currentWarehouseId);
                delete warehouseDataMap[currentWarehouseId];

                if (warehouses.length > 0) {
                    // If new main warehouse was set
                    if (data.newMainWarehouseId) {
                        // Update main status in dropdown
                        warehouses.forEach(w => {
                            const opt = select.querySelector(`option[value="${w._id}"]`);
                            if (opt) {
                                let text = w.name;
                                if (w._id === data.newMainWarehouseId) {
                                    w.is_main = true;
                                    text += ' (Main)';
                                }
                                if (w.status === 'inactive') text += ' (Inactive)';
                                opt.textContent = text;
                            }
                        });

                        // Select the new main warehouse
                        select.value = data.newMainWarehouseId;
                        currentWarehouseId = data.newMainWarehouseId;
                    } else {
                        // Select first available warehouse
                        select.value = warehouses[0]._id;
                        currentWarehouseId = warehouses[0]._id;
                    }

                    loadWarehouseStock();
                } else {
                    // No warehouses left - show empty state
                    currentWarehouseId = null;
                    currentWarehouseData = null;
                    resetWarehouseDetails();

                    // Add "No warehouses available" option
                    select.innerHTML = '<option value="">No warehouses available</option>';

                    // Disable buttons and inputs
                    document.querySelectorAll('.btn-edit, .btn-delete, #searchInput, #statusFilter, #selectAll').forEach(el => {
                        el.disabled = true;
                    });

                    const tbody = document.getElementById('stockTableBody');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="10" class="empty-state">
                                <div class="empty-state-content">
                                    <span class="empty-icon">🏢</span>
                                    <h3>No Warehouses Found</h3>
                                    <p>Create your first warehouse to start managing inventory</p>
                                    <button class="btn-primary" onclick="openCreateWarehouseModal()">
                                        + Create Warehouse
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to delete warehouse', 'error');
        });
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const search = e.target.value.toLowerCase();
        const allRows = document.querySelectorAll('#stockTableBody tr');

        allRows.forEach(row => {
            if (row.classList.contains('variant-group-header')) {
                let hasMatchingChild = false;
                let nextRow = row.nextElementSibling;

                while (nextRow && nextRow.classList.contains('variant-row')) {
                    const text = nextRow.textContent.toLowerCase();
                    if (text.includes(search)) {
                        hasMatchingChild = true;
                    }
                    nextRow = nextRow.nextElementSibling;
                }

                const headerMatches = row.textContent.toLowerCase().includes(search);
                row.style.display = (hasMatchingChild || headerMatches || search === '') ? '' : 'none';
            } else {
                const text = row.textContent.toLowerCase();
                const matches = text.includes(search);
                row.style.display = matches || search === '' ? '' : 'none';

                if (matches && row.classList.contains('variant-row')) {
                    let prevRow = row.previousElementSibling;
                    while (prevRow && !prevRow.classList.contains('variant-group-header')) {
                        prevRow = prevRow.previousElementSibling;
                    }
                    if (prevRow && prevRow.classList.contains('variant-group-header')) {
                        prevRow.style.display = '';
                    }
                }
            }
        });
    });

    document.getElementById('warehouseSelect').addEventListener('change', function() {
        const warehouseId = this.value;
        if (warehouseId) {
            currentWarehouseId = warehouseId;
            loadWarehouseStock();
        }
    });

    @if(session('success'))
        showAlert('{{ session('success') }}', 'success');
    @endif
    @if(session('error'))
        showAlert('{{ session('error') }}', 'error');
    @endif
</script>
@endpush
@endsection
