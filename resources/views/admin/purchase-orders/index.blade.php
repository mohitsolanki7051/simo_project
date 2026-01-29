@extends('layouts.admin')

@section('title', 'Purchase Orders - Admin Panel')
@section('header-title', 'Purchase Orders Management')

@section('content')
<div class="purchase-orders-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header with Actions -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Purchase Orders</h2>
            <p class="page-subtitle">Manage all your purchase orders</p>
        </div>
        <div class="header-right">
            <a href="{{ url('/admin/purchase-orders/create') }}" class="btn-primary">
                <span>+</span> Create Purchase Order
            </a>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-card">
        <div class="filters-row">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by PO Number, Supplier..." onkeyup="filterTable()">
            </div>

            <select class="filter-select" id="statusFilter" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="received">Received</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <select class="filter-select" id="warehouseFilter" onchange="filterTable()">
                <option value="">All Warehouses</option>
                @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->name }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>

            <select class="filter-select" id="supplierFilter" onchange="filterTable()">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->name }}">{{ $supplier->name }}</option>
                @endforeach
            </select>

            <button class="btn-bulk-action" id="bulkDeleteBtn" onclick="bulkDelete()" style="display: none;">
                <span>🗑️</span> Delete Selected
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-draft">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <div class="stat-label">Draft</div>
                <div class="stat-value">{{ $purchaseOrders->where('status', 'draft')->count() }}</div>
            </div>
        </div>
        <div class="stat-card stat-pending">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ $purchaseOrders->where('status', 'pending')->count() }}</div>
            </div>
        </div>
        <div class="stat-card stat-approved">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <div class="stat-label">Approved</div>
                <div class="stat-value">{{ $purchaseOrders->where('status', 'approved')->count() }}</div>
            </div>
        </div>
        <div class="stat-card stat-received">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <div class="stat-label">Received</div>
                <div class="stat-value">{{ $purchaseOrders->where('status', 'received')->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">All Purchase Orders</h3>
            <div class="table-actions">
                <button class="btn-export" onclick="exportToExcel()">
                    <span>📊</span> Export
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="purchaseOrdersTable">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>PO Number</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Warehouse</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                    <tr data-status="{{ $po->status }}" data-warehouse="{{ $po->warehouse->name ?? '' }}" data-supplier="{{ $po->supplier->name ?? '' }}">
                        <td>
                            <input type="checkbox" class="row-checkbox" value="{{ $po->id }}">
                        </td>
                        <td>
                            <strong class="po-number">{{ $po->po_number }}</strong>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($po->po_date)->format('d M Y') }}</td>
                        <td>{{ $po->supplier->name ?? $po->supplier_name }}</td>
                        <td>{{ $po->warehouse->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-info">{{ $po->total_items }} items</span>
                        </td>
                        <td>
                            <strong class="amount">{{ $po->formatted_total_amount }}</strong>
                        </td>
                        <td>
                            @if($po->status === 'draft')
                                <span class="badge badge-secondary">Draft</span>
                            @elseif($po->status === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($po->status === 'approved')
                                <span class="badge badge-info">Approved</span>
                            @elseif($po->status === 'received')
                                <span class="badge badge-success">Received</span>
                            @elseif($po->status === 'cancelled')
                                <span class="badge badge-danger">Cancelled</span>
                            @endif
                        </td>
                        <td>
                            @if($po->payment_status === 'unpaid')
                                <span class="badge badge-danger">Unpaid</span>
                            @elseif($po->payment_status === 'partial')
                                <span class="badge badge-warning">Partial</span>
                            @elseif($po->payment_status === 'paid')
                                <span class="badge badge-success">Paid</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ url('/admin/purchase-orders/' . $po->id) }}" class="btn-action btn-view" title="View">
                                    <span>👁️</span>
                                </a>
                                @if($po->canEdit())
                                <a href="{{ url('/admin/purchase-orders/' . $po->id . '/edit') }}" class="btn-action btn-edit" title="Edit">
                                    <span>✏️</span>
                                </a>
                                @endif
                                <a href="{{ url('/admin/purchase-orders/' . $po->id . '/invoice') }}" class="btn-action btn-invoice" title="Invoice" target="_blank">
                                    <span>📄</span>
                                </a>
                                @if($po->status === 'draft' || $po->status === 'cancelled')
                                <button class="btn-action btn-delete" onclick="deletePO('{{ $po->id }}', '{{ $po->po_number }}')" title="Delete">
                                    <span>🗑️</span>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="empty-state">
                                <div class="empty-icon">📋</div>
                                <p>No purchase orders found</p>
                                <a href="{{ url('/admin/purchase-orders/create') }}" class="btn-primary">Create Your First Purchase Order</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<style>
    .purchase-orders-container { padding: 20px; }
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .page-subtitle { font-size: 14px; color: #718096; }
    .btn-primary { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }

    .filters-card { background: white; padding: 20px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .filters-row { display: flex; gap: 15px; flex-wrap: wrap; }
    .search-box { flex: 1; min-width: 250px; }
    .search-box input { width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; }
    .search-box input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
    .filter-select { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; cursor: pointer; transition: all 0.3s; background: white; }
    .filter-select:focus { outline: none; border-color: #667eea; }
    .btn-bulk-action { padding: 12px 20px; background: #fc8181; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
    .btn-bulk-action:hover { background: #f56565; transform: translateY(-2px); }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
    .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px; transition: all 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
    .stat-icon { font-size: 32px; }
    .stat-label { font-size: 12px; color: #718096; font-weight: 600; text-transform: uppercase; }
    .stat-value { font-size: 28px; font-weight: 700; color: #2d3748; }
    .stat-draft { border-left: 4px solid #a0aec0; }
    .stat-pending { border-left: 4px solid #f6ad55; }
    .stat-approved { border-left: 4px solid #4299e1; }
    .stat-received { border-left: 4px solid #48bb78; }

    .table-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden; }
    .table-header { padding: 20px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .table-title { font-size: 18px; font-weight: 700; color: #2d3748; }
    .btn-export { padding: 10px 20px; background: #48bb78; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
    .btn-export:hover { background: #38a169; }

    .table-responsive { overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); }
    .data-table th { padding: 15px; text-align: left; font-weight: 700; color: #4a5568; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
    .data-table td { padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #2d3748; }
    .data-table tbody tr { transition: all 0.2s; }
    .data-table tbody tr:hover { background: #f7fafc; }
    .po-number { color: #667eea; font-weight: 600; }
    .amount { color: #2d3748; font-size: 15px; }

    .badge { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-block; }
    .badge-secondary { background: #e2e8f0; color: #4a5568; }
    .badge-warning { background: #fef5e7; color: #b7791f; }
    .badge-info { background: #ebf8ff; color: #2c5282; }
    .badge-success { background: #f0fff4; color: #22543d; }
    .badge-danger { background: #fff5f5; color: #9b2c2c; }

    .action-buttons { display: flex; gap: 8px; }
    .btn-action { padding: 8px 12px; border: 2px solid #e2e8f0; background: white; border-radius: 8px; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
    .btn-view:hover { border-color: #4299e1; background: #ebf8ff; }
    .btn-edit:hover { border-color: #f6ad55; background: #fef5e7; }
    .btn-invoice:hover { border-color: #9f7aea; background: #faf5ff; }
    .btn-delete:hover { border-color: #fc8181; background: #fff5f5; }

    .empty-state { padding: 60px 20px; text-align: center; }
    .empty-icon { font-size: 64px; margin-bottom: 20px; }
    .empty-state p { font-size: 16px; color: #718096; margin-bottom: 20px; }

    .text-center { text-align: center; }
</style>
@endpush

@push('scripts')
<script>
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    function filterTable() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
        const warehouseFilter = document.getElementById('warehouseFilter').value.toLowerCase();
        const supplierFilter = document.getElementById('supplierFilter').value.toLowerCase();

        const rows = document.querySelectorAll('#purchaseOrdersTable tbody tr');

        rows.forEach(row => {
            if (row.querySelector('.empty-state')) return;

            const text = row.textContent.toLowerCase();
            const status = row.dataset.status?.toLowerCase() || '';
            const warehouse = row.dataset.warehouse?.toLowerCase() || '';
            const supplier = row.dataset.supplier?.toLowerCase() || '';

            const matchesSearch = text.includes(searchValue);
            const matchesStatus = !statusFilter || status === statusFilter;
            const matchesWarehouse = !warehouseFilter || warehouse === warehouseFilter;
            const matchesSupplier = !supplierFilter || supplier === supplierFilter;

            if (matchesSearch && matchesStatus && matchesWarehouse && matchesSupplier) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkActions();
    }

    function updateBulkActions() {
        const selected = document.querySelectorAll('.row-checkbox:checked').length;
        document.getElementById('bulkDeleteBtn').style.display = selected > 0 ? 'flex' : 'none';
    }

    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });

    function deletePO(id, poNumber) {
        if (confirm(`Are you sure you want to delete Purchase Order ${poNumber}?`)) {
            fetch(`/admin/purchase-orders/${id}/delete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Purchase Order deleted successfully', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('Failed to delete purchase order', 'error');
                }
            })
            .catch(error => {
                showAlert('Error deleting purchase order', 'error');
            });
        }
    }

    function bulkDelete() {
        const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) return;

        if (confirm(`Delete ${selected.length} selected purchase order(s)?`)) {
            fetch('/admin/purchase-orders/bulk-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ po_ids: selected })
            })
            .then(response => response.json())
            .then(data => {
                showAlert(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => {
                showAlert('Error performing bulk delete', 'error');
            });
        }
    }

    function exportToExcel() {
        showAlert('Export feature coming soon!', 'success');
    }

    @if(session('success'))
        showAlert('{{ session('success') }}', 'success');
    @endif
    @if(session('error'))
        showAlert('{{ session('error') }}', 'error');
    @endif
</script>
@endpush
@endsection
