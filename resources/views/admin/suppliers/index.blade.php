@extends('layouts.admin')

@section('title', 'Suppliers - Admin Panel')
@section('header-title', 'Supplier Management')

@section('content')
<div class="suppliers-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Suppliers</h2>
            <p class="page-description">Manage your suppliers and view their details</p>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.suppliers.create') }}" class="btn-primary">
                <span>+</span> Add New Supplier
            </a>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-section">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search suppliers..." class="search-input">
            <button class="search-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </div>

        <div class="filter-actions">
            <select id="statusFilter" class="filter-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <button class="btn-secondary" onclick="showBulkActions()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3" y2="6"></line>
                    <line x1="3" y1="12" x2="3" y2="12"></line>
                    <line x1="3" y1="18" x2="3" y2="18"></line>
                </svg>
                Bulk Actions
            </button>
        </div>
    </div>

    <!-- Bulk Actions Panel -->
    <div class="bulk-actions-panel" id="bulkActionsPanel" style="display: none;">
        <div class="bulk-header">
            <span id="selectedCount">0 suppliers selected</span>
            <div class="bulk-buttons">
                <button class="btn-success" onclick="bulkUpdateStatus('active')">
                    Mark as Active
                </button>
                <button class="btn-warning" onclick="bulkUpdateStatus('inactive')">
                    Mark as Inactive
                </button>
                <button class="btn-danger" onclick="confirmBulkDelete()">
                    Delete Selected
                </button>
                <button class="btn-secondary" onclick="clearSelection()">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th width="30">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                    </th>
                    <th>Supplier Name</th>
                    <th>Contact Info</th>
                    <th>Address</th>
                    <th>GSTIN/PAN</th>
                    <th>Payment Terms</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="suppliersTableBody">
                @forelse($suppliers as $supplier)
                <tr data-status="{{ $supplier->status }}">
                    <td>
                        <input type="checkbox" class="supplier-checkbox" value="{{ $supplier->id }}">
                    </td>
                    <td>
                        <div class="supplier-info">
                            <div class="supplier-name">{{ $supplier->name }}</div>
                            <div class="supplier-id">ID: {{ $supplier->id }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="contact-info">
                            <div class="contact-email">{{ $supplier->email ?? 'N/A' }}</div>
                            <div class="contact-phone">{{ $supplier->phone }}</div>
                            @if($supplier->alternate_phone)
                            <div class="contact-alt-phone">{{ $supplier->alternate_phone }}</div>
                            @endif
                            @if($supplier->contact_person)
                            <div class="contact-person">Contact: {{ $supplier->contact_person }}</div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="address-info">
                            <div>{{ $supplier->address ?? 'N/A' }}</div>
                            <div class="address-city">{{ $supplier->city ?? '' }} {{ $supplier->state ?? '' }} {{ $supplier->pincode ?? '' }}</div>
                            <div class="address-country">{{ $supplier->country ?? '' }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="tax-info">
                            @if($supplier->gstin)
                            <div class="gstin">GSTIN: {{ $supplier->gstin }}</div>
                            @endif
                            @if($supplier->pan)
                            <div class="pan">PAN: {{ $supplier->pan }}</div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="payment-info">
                            <div class="payment-terms">{{ $supplier->payment_terms }}</div>
                            @if($supplier->credit_limit > 0)
                            <div class="credit-limit">Credit: ₹{{ number_format($supplier->credit_limit, 2) }}</div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $supplier->status }}">
                            {{ ucfirst($supplier->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('admin.suppliers.show', $supplier->id) }}" class="btn-action btn-view" title="View">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                            <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn-action btn-edit" title="Edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <button type="button" class="btn-action btn-delete" title="Delete" onclick="confirmDelete({{ $supplier->id }})">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="no-data">
                        <div class="no-data-content">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                            </svg>
                            <h3>No Suppliers Found</h3>
                            <p>Get started by creating your first supplier</p>
                            <a href="{{ route('admin.suppliers.create') }}" class="btn-primary">Add New Supplier</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Stats Cards -->
    <div class="stats-section">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $suppliers->count() }}</h3>
                <p class="stat-label">Total Suppliers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #38a169 0%, #68d391 100%);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 6v6l4 2"></path>
                </svg>
            </div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $suppliers->where('status', 'active')->count() }}</h3>
                <p class="stat-label">Active Suppliers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ed8936 0%, #f6ad55 100%);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                    <path d="M3 9h18"></path>
                    <path d="M9 21V9"></path>
                </svg>
            </div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $suppliers->where('status', 'inactive')->count() }}</h3>
                <p class="stat-label">Inactive Suppliers</p>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this supplier? This action cannot be undone.</p>
            <div class="modal-warning">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>If this supplier has purchase orders, it cannot be deleted.</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
            <button class="btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

@push('styles')
<style>
    .suppliers-container { padding-bottom: 40px; }
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .page-description { color: #718096; font-size: 14px; }
    .btn-primary { padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }

    .filters-section { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; gap: 20px; }
    .search-box { flex: 1; max-width: 400px; position: relative; }
    .search-input { width: 100%; padding: 12px 16px 12px 45px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; }
    .search-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
    .search-btn { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #a0aec0; cursor: pointer; }
    .filter-actions { display: flex; gap: 12px; align-items: center; }
    .filter-select { padding: 10px 16px; border: 2px solid #e2e8f0; border-radius: 8px; background: white; font-size: 14px; }
    .btn-secondary { padding: 10px 16px; background: #edf2f7; color: #4a5568; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-secondary:hover { background: #e2e8f0; }

    .bulk-actions-panel { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; border: 2px solid #cbd5e0; }
    .bulk-header { display: flex; justify-content: space-between; align-items: center; }
    .bulk-buttons { display: flex; gap: 10px; }
    .btn-success { padding: 8px 16px; background: #38a169; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
    .btn-warning { padding: 8px 16px; background: #ed8936; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
    .btn-danger { padding: 8px 16px; background: #f56565; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }

    .table-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .data-table th { padding: 16px 20px; text-align: left; color: white; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    .data-table tbody tr { border-bottom: 1px solid #e2e8f0; transition: all 0.3s; }
    .data-table tbody tr:hover { background: #f7fafc; }
    .data-table td { padding: 16px 20px; font-size: 14px; }

    .supplier-info .supplier-name { font-weight: 600; color: #2d3748; }
    .supplier-info .supplier-id { font-size: 12px; color: #a0aec0; }
    .contact-info div, .address-info div { margin-bottom: 4px; }
    .contact-email { color: #4299e1; }
    .contact-phone { font-weight: 500; }
    .contact-alt-phone { color: #718096; font-size: 12px; }
    .contact-person { font-size: 12px; color: #4a5568; }
    .address-city { color: #718096; font-size: 13px; }
    .address-country { color: #a0aec0; font-size: 12px; }
    .tax-info div { font-family: monospace; font-size: 13px; }
    .payment-info .payment-terms { font-weight: 500; }
    .payment-info .credit-limit { color: #38a169; font-size: 13px; }

    .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .status-active { background: #c6f6d5; color: #22543d; }
    .status-inactive { background: #fed7d7; color: #9b2c2c; }

    .action-buttons { display: flex; gap: 8px; }
    .btn-action { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s; }
    .btn-view { background: #ebf8ff; color: #4299e1; }
    .btn-edit { background: #fef5e7; color: #ed8936; }
    .btn-delete { background: #fff5f5; color: #f56565; border: none; cursor: pointer; }
    .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

    .no-data { text-align: center; padding: 60px 20px; }
    .no-data-content { max-width: 300px; margin: 0 auto; }
    .no-data-content svg { color: #cbd5e0; margin-bottom: 20px; }
    .no-data-content h3 { font-size: 18px; color: #4a5568; margin-bottom: 8px; }
    .no-data-content p { color: #a0aec0; margin-bottom: 20px; }

    .stats-section { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px; }
    .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; }
    .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .stat-value { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .stat-label { color: #718096; font-size: 14px; }

    .modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
    .modal-content { background: white; border-radius: 16px; width: 90%; max-width: 500px; animation: modalSlideIn 0.3s; }
    @keyframes modalSlideIn { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
    .modal-header { padding: 20px 25px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { font-size: 18px; color: #2d3748; }
    .modal-close { background: none; border: none; font-size: 24px; color: #a0aec0; cursor: pointer; }
    .modal-body { padding: 25px; }
    .modal-warning { background: #fff5f5; border-left: 4px solid #f56565; padding: 12px 16px; border-radius: 8px; margin-top: 15px; display: flex; align-items: center; gap: 10px; color: #9b2c2c; }
    .modal-footer { padding: 20px 25px; border-top: 2px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px; }
</style>
@endpush

@push('scripts')
<script>
    let selectedSuppliers = new Set();
    let currentDeleteId = null;

    // Show alert function
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span class="alert-icon">${type === 'success' ? '✓' : '✕'}</span><span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => { alert.classList.add('removing'); setTimeout(() => alert.remove(), 300); }, 5000);
    }

    // Toggle select all
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll').checked;
        const checkboxes = document.querySelectorAll('.supplier-checkbox');

        checkboxes.forEach(cb => {
            cb.checked = selectAll;
            if (selectAll) {
                selectedSuppliers.add(cb.value);
            } else {
                selectedSuppliers.delete(cb.value);
            }
        });

        updateSelectedCount();
    }

    // Update checkbox selection
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('supplier-checkbox')) {
            if (e.target.checked) {
                selectedSuppliers.add(e.target.value);
            } else {
                selectedSuppliers.delete(e.target.value);
                document.getElementById('selectAll').checked = false;
            }
            updateSelectedCount();
        }
    });

    // Update selected count
    function updateSelectedCount() {
        const count = selectedSuppliers.size;
        document.getElementById('selectedCount').textContent = `${count} supplier(s) selected`;

        if (count > 0) {
            document.getElementById('bulkActionsPanel').style.display = 'block';
        } else {
            document.getElementById('bulkActionsPanel').style.display = 'none';
        }
    }

    // Show bulk actions
    function showBulkActions() {
        const panel = document.getElementById('bulkActionsPanel');
        if (selectedSuppliers.size === 0) {
            showAlert('Please select suppliers first', 'error');
            return;
        }
        panel.style.display = 'block';
    }

    // Clear selection
    function clearSelection() {
        selectedSuppliers.clear();
        document.querySelectorAll('.supplier-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        updateSelectedCount();
    }

    // Bulk update status
    function bulkUpdateStatus(status) {
        if (selectedSuppliers.size === 0) return;

        const supplierIds = Array.from(selectedSuppliers);

        fetch('{{ route("admin.suppliers.bulk-update-status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                supplier_ids: supplierIds,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update status badges
                supplierIds.forEach(id => {
                    const row = document.querySelector(`.supplier-checkbox[value="${id}"]`).closest('tr');
                    const badge = row.querySelector('.status-badge');
                    badge.className = `status-badge status-${status}`;
                    badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    row.dataset.status = status;
                });

                showAlert(data.message, 'success');
                clearSelection();
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to update status', 'error');
        });
    }

    // Confirm bulk delete
    function confirmBulkDelete() {
        if (selectedSuppliers.size === 0) return;

        if (confirm(`Are you sure you want to delete ${selectedSuppliers.size} supplier(s)? This action cannot be undone.`)) {
            performBulkDelete();
        }
    }

    // Perform bulk delete
    function performBulkDelete() {
        const supplierIds = Array.from(selectedSuppliers);

        fetch('{{ route("admin.suppliers.bulk-delete") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                supplier_ids: supplierIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove rows from table
                supplierIds.forEach(id => {
                    const row = document.querySelector(`.supplier-checkbox[value="${id}"]`)?.closest('tr');
                    if (row) row.remove();
                });

                showAlert(data.message, 'success');
                clearSelection();

                // Reload if no rows left
                if (document.querySelectorAll('.data-table tbody tr:not(.no-data)').length === 0) {
                    location.reload();
                }
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to delete suppliers', 'error');
        });
    }

    // Confirm delete single supplier
    function confirmDelete(id) {
        currentDeleteId = id;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    // Close modal
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        currentDeleteId = null;
    }

    // Delete supplier
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (!currentDeleteId) return;

        fetch(`/admin/suppliers/${currentDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove row from table
                const row = document.querySelector(`.supplier-checkbox[value="${currentDeleteId}"]`)?.closest('tr');
                if (row) row.remove();

                showAlert('Supplier deleted successfully!', 'success');
                closeModal('deleteModal');

                // Reload if no rows left
                if (document.querySelectorAll('.data-table tbody tr:not(.no-data)').length === 0) {
                    location.reload();
                }
            } else {
                showAlert(data.message || 'Failed to delete supplier', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to delete supplier', 'error');
        });
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#suppliersTableBody tr:not(.no-data)');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function(e) {
        const status = e.target.value;
        const rows = document.querySelectorAll('#suppliersTableBody tr:not(.no-data)');

        rows.forEach(row => {
            if (!status || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
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
