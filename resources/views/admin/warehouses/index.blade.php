@extends('layouts.admin')

@section('title', 'Warehouses - Admin Panel')
@section('header-title', 'Warehouse Management')

@section('content')
<div class="warehouses-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Warehouse Management</h2>
            <p class="page-subtitle">Manage all your warehouses and storage locations</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.warehouses.create') }}" class="btn-primary">
                <span>+</span> Add New Warehouse
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);">📦</div>
            <div class="stat-info">
                <div class="stat-label">Total Warehouses</div>
                <div class="stat-value">{{ $warehouses->count() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);">✅</div>
            <div class="stat-info">
                <div class="stat-label">Active Warehouses</div>
                <div class="stat-value">{{ $warehouses->where('status', 'active')->count() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);">⏸️</div>
            <div class="stat-info">
                <div class="stat-label">Inactive Warehouses</div>
                <div class="stat-value">{{ $warehouses->where('status', 'inactive')->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Warehouses Table -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-header-left">
                <h3 class="table-title">All Warehouses</h3>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search warehouses..." class="search-input">
                    <span class="search-icon">🔍</span>
                </div>
            </div>
            <div class="table-header-right">
                <div class="bulk-actions" id="bulkActions" style="display: none;">
                    <button class="btn-bulk btn-bulk-activate" onclick="bulkUpdateStatus('active')">
                        <span>✓</span> Activate
                    </button>
                    <button class="btn-bulk btn-bulk-deactivate" onclick="bulkUpdateStatus('inactive')">
                        <span>⏸</span> Deactivate
                    </button>
                    <button class="btn-bulk btn-bulk-delete" onclick="bulkDelete()">
                        <span>🗑️</span> Delete
                    </button>
                </div>
                <select id="filterStatus" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="selectAll" class="checkbox">
                        </th>
                        <th>Warehouse Details</th>
                        <th>Location</th>
                        <th>Contact Info</th>
                        <th>Manager</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody id="warehouseTableBody">
                    @forelse($warehouses as $warehouse)
                    <tr data-status="{{ $warehouse->status }}">
                        <td>
                            <input type="checkbox" class="checkbox row-checkbox" value="{{ $warehouse->id }}">
                        </td>
                        <td>
                            <div class="warehouse-info">
                                <div class="warehouse-name">{{ $warehouse->name }}</div>
                                <div class="warehouse-code">Code: {{ $warehouse->code }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="location-info">
                                <div class="location-text">{{ $warehouse->city }}, {{ $warehouse->state }}</div>
                                <div class="pincode-text">PIN: {{ $warehouse->pincode }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <div class="contact-item">📞 {{ $warehouse->phone }}</div>
                                @if($warehouse->email)
                                <div class="contact-item">✉️ {{ $warehouse->email }}</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="manager-name">{{ $warehouse->manager_name ?: 'Not Assigned' }}</div>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $warehouse->status }}">
                                {{ ucfirst($warehouse->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-view" onclick="viewWarehouse('{{ $warehouse->id }}')" title="View Details">
                                    👁️
                                </button>
                                <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn-action btn-edit" title="Edit">
                                    ✏️
                                </a>
                                <button class="btn-action btn-delete" onclick="deleteWarehouse('{{ $warehouse->id }}')" title="Delete">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <div class="empty-icon">🏢</div>
                            <p>No warehouses found</p>
                            <a href="{{ route('admin.warehouses.create') }}" class="btn-primary-small">Add First Warehouse</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Warehouse Modal -->
<div class="modal" id="viewModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Warehouse Details</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <div class="loading">Loading...</div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .warehouses-container { padding-bottom: 40px; }

    /* Alert Styles */
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 30px; }
    .header-left { flex: 1; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 8px; }
    .page-subtitle { font-size: 14px; color: #718096; }
    .btn-primary { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; text-decoration: none; border-radius: 12px; font-weight: 600; transition: all 0.3s; box-shadow: 0 4px 12px rgba(255,107,53,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.4); }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; }
    .stat-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
    .stat-info { flex: 1; }
    .stat-label { font-size: 13px; color: #718096; margin-bottom: 5px; }
    .stat-value { font-size: 28px; font-weight: 700; color: #2d3748; }

    .table-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
    .table-header { padding: 25px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .table-header-left { display: flex; align-items: center; gap: 20px; flex: 1; }
    .table-title { font-size: 20px; font-weight: 700; color: #2d3748; }
    .search-box { position: relative; }
    .search-input { padding: 10px 40px 10px 16px; border: 2px solid #e2e8f0; border-radius: 10px; width: 300px; transition: all 0.3s; }
    .search-input:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
    .search-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 18px; }

    .table-header-right { display: flex; align-items: center; gap: 15px; }
    .filter-select { padding: 10px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; }
    .filter-select:focus { outline: none; border-color: #ff6b35; }

    .bulk-actions { display: flex; gap: 10px; }
    .btn-bulk { padding: 10px 16px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 6px; font-size: 13px; }
    .btn-bulk-activate { background: #c6f6d5; color: #22543d; }
    .btn-bulk-activate:hover { background: #9ae6b4; }
    .btn-bulk-deactivate { background: #fed7d7; color: #9b2c2c; }
    .btn-bulk-deactivate:hover { background: #fc8181; }
    .btn-bulk-delete { background: #feb2b2; color: #742a2a; }
    .btn-bulk-delete:hover { background: #fc8181; }

    .table-wrapper { overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); }
    .data-table th { padding: 16px 20px; text-align: left; font-weight: 700; color: #4a5568; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    .data-table td { padding: 18px 20px; border-bottom: 1px solid #e2e8f0; }
    .data-table tbody tr { transition: all 0.3s; }
    .data-table tbody tr:hover { background: #f7fafc; }

    .checkbox { width: 18px; height: 18px; cursor: pointer; }

    .warehouse-info { display: flex; flex-direction: column; gap: 5px; }
    .warehouse-name { font-weight: 600; color: #2d3748; font-size: 15px; }
    .warehouse-code { font-size: 12px; color: #718096; background: #edf2f7; padding: 3px 10px; border-radius: 6px; display: inline-block; }

    .location-info { display: flex; flex-direction: column; gap: 4px; }
    .location-text { font-size: 14px; color: #4a5568; }
    .pincode-text { font-size: 12px; color: #718096; }

    .contact-info { display: flex; flex-direction: column; gap: 6px; }
    .contact-item { font-size: 13px; color: #4a5568; }

    .manager-name { font-size: 14px; color: #4a5568; font-weight: 500; }

    .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; }
    .status-active { background: #c6f6d5; color: #22543d; }
    .status-inactive { background: #fed7d7; color: #9b2c2c; }

    .action-buttons { display: flex; gap: 8px; }
    .btn-action { width: 36px; height: 36px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; transition: all 0.3s; display: flex; align-items: center; justify-content: center; text-decoration: none; }
    .btn-view { background: #bee3f8; color: #2c5282; }
    .btn-view:hover { background: #90cdf4; transform: scale(1.1); }
    .btn-edit { background: #feebc8; color: #7c2d12; }
    .btn-edit:hover { background: #fbd38d; transform: scale(1.1); }
    .btn-delete { background: #fed7d7; color: #9b2c2c; }
    .btn-delete:hover { background: #fc8181; transform: scale(1.1); }

    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { font-size: 64px; margin-bottom: 20px; }
    .empty-state p { font-size: 16px; color: #718096; margin-bottom: 20px; }
    .btn-primary-small { display: inline-flex; padding: 10px 20px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; }

    /* Modal Styles */
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10000; align-items: center; justify-content: center; }
    .modal.show { display: flex; }
    .modal-content { background: white; border-radius: 16px; max-width: 600px; width: 90%; max-height: 80vh; overflow: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    .modal-header { padding: 25px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .modal-title { font-size: 22px; font-weight: 700; color: #2d3748; }
    .modal-close { background: none; border: none; font-size: 32px; color: #718096; cursor: pointer; line-height: 1; }
    .modal-close:hover { color: #2d3748; }
    .modal-body { padding: 30px; }

    .detail-group { margin-bottom: 20px; }
    .detail-label { font-size: 12px; color: #718096; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; font-weight: 700; }
    .detail-value { font-size: 15px; color: #2d3748; font-weight: 500; }

    @media (max-width: 768px) {
        .page-header { flex-direction: column; gap: 15px; }
        .table-header { flex-direction: column; align-items: stretch; }
        .table-header-left, .table-header-right { width: 100%; }
        .search-input { width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
    // Show alert function
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const search = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#warehouseTableBody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        });
    });

    // Filter by status
    document.getElementById('filterStatus').addEventListener('change', function(e) {
        const status = e.target.value;
        const rows = document.querySelectorAll('#warehouseTableBody tr');

        rows.forEach(row => {
            if (!status || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function(e) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = e.target.checked);
        toggleBulkActions();
    });

    // Individual checkbox
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', toggleBulkActions);
    });

    function toggleBulkActions() {
        const checked = document.querySelectorAll('.row-checkbox:checked').length;
        document.getElementById('bulkActions').style.display = checked > 0 ? 'flex' : 'none';
    }

    // View warehouse
    function viewWarehouse(id) {
        const modal = document.getElementById('viewModal');
        const modalBody = document.getElementById('modalBody');

        modal.classList.add('show');
        modalBody.innerHTML = '<div class="loading">Loading...</div>';

        fetch(`/admin/warehouses/${id}`, {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const w = data.warehouse;
                modalBody.innerHTML = `
                    <div class="detail-group">
                        <div class="detail-label">Warehouse Name</div>
                        <div class="detail-value">${w.name}</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Warehouse Code</div>
                        <div class="detail-value">${w.code}</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Full Address</div>
                        <div class="detail-value">${w.address}, ${w.city}, ${w.state} - ${w.pincode}</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Phone Number</div>
                        <div class="detail-value">${w.phone}</div>
                    </div>
                    ${w.email ? `
                    <div class="detail-group">
                        <div class="detail-label">Email Address</div>
                        <div class="detail-value">${w.email}</div>
                    </div>` : ''}
                    ${w.manager_name ? `
                    <div class="detail-group">
                        <div class="detail-label">Manager Name</div>
                        <div class="detail-value">${w.manager_name}</div>
                    </div>` : ''}
                    <div class="detail-group">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="status-badge status-${w.status}">${w.status.toUpperCase()}</span>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            modalBody.innerHTML = '<div class="error">Failed to load warehouse details</div>';
        });
    }

    function closeModal() {
        document.getElementById('viewModal').classList.remove('show');
    }

    // Delete warehouse
    function deleteWarehouse(id) {
        if (!confirm('Are you sure you want to delete this warehouse?')) return;

        fetch(`/admin/warehouses/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Warehouse deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Failed to delete warehouse', 'error');
            }
        })
        .catch(error => {
            showAlert('Failed to delete warehouse', 'error');
        });
    }

    // Bulk update status
    function bulkUpdateStatus(status) {
        const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);

        if (ids.length === 0) return;

        fetch('/admin/warehouses/bulk-update-status', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ warehouse_ids: ids, status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(error => showAlert('Failed to update status', 'error'));
    }

    // Bulk delete
    function bulkDelete() {
        const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);

        if (ids.length === 0) return;
        if (!confirm(`Delete ${ids.length} warehouse(s)?`)) return;

        fetch('/admin/warehouses/bulk-delete', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ warehouse_ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(error => showAlert('Failed to delete warehouses', 'error'));
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
