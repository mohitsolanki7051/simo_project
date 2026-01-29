@extends('layouts.admin')

@section('title', 'Orders - Admin Panel')
@section('header-title', 'Order Management')

@section('content')
<div class="orders-container">
    <div id="alertContainer"></div>

    <!-- Header Section -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">📦 All Orders</h2>
            <p class="page-subtitle">Manage and track all customer orders</p>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.orders.create') }}" class="btn-create">
                <span class="icon">+</span> Create New Order
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">📊</div>
            <div class="stat-content">
                <div class="stat-value">{{ $orders->count() }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">⏳</div>
            <div class="stat-content">
                <div class="stat-value">{{ $orders->where('order_status', 'pending')->count() }}</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">🔄</div>
            <div class="stat-content">
                <div class="stat-value">{{ $orders->where('order_status', 'processing')->count() }}</div>
                <div class="stat-label">Processing</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">✅</div>
            <div class="stat-content">
                <div class="stat-value">{{ $orders->where('order_status', 'completed')->count() }}</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
    </div>

    <!-- Filters & Bulk Actions -->
    <div class="table-controls">
        <div class="controls-left">
            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <button type="button" class="btn-bulk" onclick="bulkUpdateStatus('pending')">
                    <span>⏳</span> Mark Pending
                </button>
                <button type="button" class="btn-bulk" onclick="bulkUpdateStatus('processing')">
                    <span>🔄</span> Mark Processing
                </button>
                <button type="button" class="btn-bulk" onclick="bulkUpdateStatus('completed')">
                    <span>✅</span> Mark Completed
                </button>
                <button type="button" class="btn-bulk btn-bulk-delete" onclick="bulkDelete()">
                    <span>🗑️</span> Delete Selected
                </button>
            </div>
        </div>
        <div class="controls-right">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search orders..." class="search-input">
                <span class="search-icon">🔍</span>
            </div>
            <select class="filter-select" id="statusFilter" onchange="filterOrders()">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="on_hold">On Hold</option>
            </select>
            <select class="filter-select" id="paymentFilter" onchange="filterOrders()">
                <option value="">All Payments</option>
                <option value="paid">Paid</option>
                <option value="unpaid">Unpaid</option>
                <option value="partially_paid">Partially Paid</option>
                <option value="refunded">Refunded</option>
            </select>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th width="50">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                    </th>
                    <th>Order Number</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody id="ordersTableBody">
                @forelse($orders as $order)
                <tr>
                    <td>
                        <input type="checkbox" class="order-checkbox" value="{{ $order->id }}" onchange="updateBulkActions()">
                    </td>
                    <td>
                        <div class="order-number">#{{ $order->order_number }}</div>
                    </td>
                    <td>
                        <div class="customer-info">
                            <div class="customer-name">{{ $order->customer_name }}</div>
                            <div class="customer-email">{{ $order->customer_email }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="date-info">{{ $order->order_date->format('d M Y') }}</div>
                        <div class="time-info">{{ $order->order_date->format('h:i A') }}</div>
                    </td>
                    <td>
                        <span class="badge badge-light">{{ $order->total_items }} item(s)</span>
                    </td>
                    <td>
                        <div class="amount-value">{{ $order->formatted_total_amount }}</div>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $order->order_status }}">
                            {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                        </span>
                    </td>
                    <td>
                        <span class="payment-badge payment-{{ $order->payment_status }}">
                            {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-icon" title="View">
                                <span>👁️</span>
                            </a>
                            <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn-icon" title="Invoice" target="_blank">
                                <span>📄</span>
                            </a>
                            <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn-icon" title="Edit">
                                <span>✏️</span>
                            </a>
                            <button type="button" class="btn-icon btn-delete" onclick="deleteOrder('{{ $order->id }}')" title="Delete">
                                <span>🗑️</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="no-data">
                        <div class="no-data-content">
                            <div class="no-data-icon">📦</div>
                            <div class="no-data-text">No orders found</div>
                            <a href="{{ route('admin.orders.create') }}" class="btn-create-inline">Create Your First Order</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('styles')
<style>
    .orders-container { padding: 30px; }
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .header-left { flex: 1; }
    .page-title { font-size: 32px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .page-subtitle { font-size: 14px; color: #718096; }
    .btn-create { padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
    .btn-create:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }
    .btn-create .icon { font-size: 20px; }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; gap: 15px; align-items: center; transition: all 0.3s; border: 2px solid #f7fafc; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .stat-content { flex: 1; }
    .stat-value { font-size: 28px; font-weight: 700; color: #2d3748; }
    .stat-label { font-size: 13px; color: #718096; margin-top: 2px; }

    .table-controls { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; gap: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .controls-left, .controls-right { display: flex; gap: 10px; align-items: center; }
    .bulk-actions { display: flex; gap: 10px; }
    .btn-bulk { padding: 10px 18px; background: #f7fafc; color: #4a5568; border: 2px solid #e2e8f0; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 6px; font-size: 13px; }
    .btn-bulk:hover { background: #edf2f7; border-color: #cbd5e0; transform: translateY(-2px); }
    .btn-bulk-delete { background: #fff5f5; color: #c53030; border-color: #feb2b2; }
    .btn-bulk-delete:hover { background: #fed7d7; border-color: #fc8181; }

    .search-box { position: relative; }
    .search-input { padding: 10px 40px 10px 16px; border: 2px solid #e2e8f0; border-radius: 10px; width: 280px; font-size: 14px; transition: all 0.3s; }
    .search-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
    .search-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 16px; }
    .filter-select { padding: 10px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; cursor: pointer; transition: all 0.3s; background: white; font-weight: 500; }
    .filter-select:focus { outline: none; border-color: #667eea; }

    .table-wrapper { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); }
    .data-table th { padding: 16px; text-align: left; font-weight: 700; color: #2d3748; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .data-table td { padding: 16px; border-bottom: 1px solid #f7fafc; color: #4a5568; font-size: 14px; }
    .data-table tbody tr { transition: all 0.2s; }
    .data-table tbody tr:hover { background: #f7fafc; }

    .order-number { font-weight: 700; color: #667eea; font-size: 15px; }
    .customer-info { }
    .customer-name { font-weight: 600; color: #2d3748; margin-bottom: 2px; }
    .customer-email { font-size: 12px; color: #a0aec0; }
    .date-info { font-weight: 600; color: #2d3748; }
    .time-info { font-size: 12px; color: #a0aec0; margin-top: 2px; }
    .amount-value { font-weight: 700; color: #2d3748; font-size: 16px; }

    .badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-block; }
    .badge-light { background: #edf2f7; color: #4a5568; }

    .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-block; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-processing { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .status-on_hold { background: #e5e7eb; color: #374151; }

    .payment-badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-block; }
    .payment-paid { background: #d1fae5; color: #065f46; }
    .payment-unpaid { background: #fee2e2; color: #991b1b; }
    .payment-partially_paid { background: #fef3c7; color: #92400e; }
    .payment-refunded { background: #dbeafe; color: #1e40af; }

    .action-buttons { display: flex; gap: 8px; }
    .btn-icon { width: 36px; height: 36px; border: none; background: #f7fafc; border-radius: 8px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; font-size: 16px; text-decoration: none; border: 2px solid #e2e8f0; }
    .btn-icon:hover { background: #edf2f7; transform: translateY(-2px); }
    .btn-delete { background: #fff5f5; border-color: #feb2b2; }
    .btn-delete:hover { background: #fed7d7; }

    .no-data { text-align: center; padding: 60px 20px; }
    .no-data-content { }
    .no-data-icon { font-size: 64px; margin-bottom: 20px; }
    .no-data-text { font-size: 18px; color: #718096; font-weight: 600; margin-bottom: 20px; }
    .btn-create-inline { padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; display: inline-block; transition: all 0.3s; }
    .btn-create-inline:hover { transform: translateY(-2px); }

    @media (max-width: 768px) {
        .page-header { flex-direction: column; align-items: flex-start; gap: 15px; }
        .table-controls { flex-direction: column; align-items: stretch; }
        .controls-right { flex-direction: column; width: 100%; }
        .search-input { width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${type === 'success' ? '✓' : '✕'}</span><span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    function toggleSelectAll(checkbox) {
        document.querySelectorAll('.order-checkbox').forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateBulkActions();
    }

    function updateBulkActions() {
        const checked = document.querySelectorAll('.order-checkbox:checked').length;
        document.getElementById('bulkActions').style.display = checked > 0 ? 'flex' : 'none';
    }

    function bulkUpdateStatus(status) {
        const selectedIds = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) return;

        if (!confirm(`Update ${selectedIds.length} order(s) to ${status}?`)) return;

        fetch('{{ route("admin.orders.bulk-update-status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ order_ids: selectedIds, status: status })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        });
    }

    function bulkDelete() {
        const selectedIds = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) return;

        if (!confirm(`Delete ${selectedIds.length} order(s)? This action cannot be undone!`)) return;

        fetch('{{ route("admin.orders.bulk-delete") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ order_ids: selectedIds })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        });
    }

    function deleteOrder(id) {
        if (!confirm('Delete this order? This action cannot be undone!')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/orders/${id}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', filterOrders);

    function filterOrders() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
        const paymentFilter = document.getElementById('paymentFilter').value.toLowerCase();

        document.querySelectorAll('#ordersTableBody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            const status = row.querySelector('.status-badge')?.textContent.toLowerCase().trim() || '';
            const payment = row.querySelector('.payment-badge')?.textContent.toLowerCase().trim() || '';

            const matchSearch = text.includes(search);
            const matchStatus = !statusFilter || status.includes(statusFilter);
            const matchPayment = !paymentFilter || payment.includes(paymentFilter);

            row.style.display = matchSearch && matchStatus && matchPayment ? '' : 'none';
        });
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
