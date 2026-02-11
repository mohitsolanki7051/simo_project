@extends('layouts.admin')

@section('title', 'Purchases - Admin Panel')
@section('header-title', 'Purchase Management')

@section('content')
<div class="purchases-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Purchases</h2>
            <p class="page-description">Manage your purchase orders and inventory</p>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.purchases.create') }}" class="btn-primary">
                <span>+</span> New Purchase
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-section">
        <div class="stat-card card-total">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $purchases->count() }}</h3>
                <p class="stat-label">Total Purchases</p>
            </div>
        </div>
        <div class="stat-card card-confirmed">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $purchases->where('status', 'confirmed')->count() }}</h3>
                <p class="stat-label">Confirmed</p>
            </div>
        </div>
        <div class="stat-card card-draft">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $purchases->where('status', 'draft')->count() }}</h3>
                <p class="stat-label">Draft</p>
            </div>
        </div>
        <div class="stat-card card-amount">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <h3 class="stat-value">₹{{ number_format($purchases->sum('grand_total'), 2) }}</h3>
                <p class="stat-label">Total Amount</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search purchases..." class="search-input">
            <button class="search-btn">🔍</button>
        </div>

        <div class="filter-actions">
            <select id="statusFilter" class="filter-select">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="confirmed">Confirmed</option>
                <option value="received">Received</option>
            </select>

            <select id="paymentFilter" class="filter-select">
                <option value="">Payment Status</option>
                <option value="unpaid">Unpaid</option>
                <option value="partial">Partial</option>
                <option value="paid">Paid</option>
            </select>

            <select id="supplierFilter" class="filter-select">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Purchases Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Purchase No</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Invoice No</th>
                    <th>Items</th>
                    <th>Grand Total</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="purchasesTableBody">
                @forelse($purchases as $purchase)
                <tr data-status="{{ $purchase->status }}" data-payment="{{ $purchase->payment_status }}" data-supplier="{{ $purchase->supplier_id }}">
                    <td>
                        <div class="purchase-no">
                            <strong>{{ $purchase->purchase_no }}</strong>
                        </div>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}</td>
                    <td>
                        <div class="supplier-info">
                            <div class="supplier-name">{{ $purchase->supplier->name ?? 'N/A' }}</div>
                            <div class="supplier-code">{{ $purchase->supplier->supplier_code ?? '' }}</div>
                        </div>
                    </td>
                    <td>{{ $purchase->invoice_no ?? 'N/A' }}</td>
                    <td>
                        <span class="items-badge">{{ $purchase->items->count() }} items</span>
                    </td>
                    <td><strong>{{ $purchase->formatted_grand_total }}</strong></td>
                    <td><span class="amount-paid">{{ $purchase->formatted_paid_amount }}</span></td>
                    <td>
                        @if($purchase->due_amount > 0)
                        <span class="amount-due">{{ $purchase->formatted_due_amount }}</span>
                        @else
                        <span class="amount-clear">₹0.00</span>
                        @endif
                    </td>
                    <td>
                        <span class="payment-badge payment-{{ $purchase->payment_status }}">
                            {{ ucfirst($purchase->payment_status) }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $purchase->status }}">
                            <span class="status-dot"></span>
                            {{ ucfirst($purchase->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn-action btn-view" title="View">
                                <span>👁️</span>
                            </a>
                            @if($purchase->status === 'draft')
                            <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="btn-action btn-edit" title="Edit">
                                <span>✏️</span>
                            </a>
                            @endif
                            <a href="{{ route('admin.purchases.invoice', $purchase->id) }}" class="btn-action btn-invoice" title="Invoice" target="_blank">
                                <span>🧾</span>
                            </a>
                            @if($purchase->status === 'draft')
                            <form action="{{ route('admin.purchases.confirm', $purchase->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Confirm this purchase? Stock will be updated.')">
                                @csrf
                                <button type="submit" class="btn-action btn-confirm" title="Confirm">
                                    <span>✅</span>
                                </button>
                            </form>
                            @endif
                            @if($purchase->canDelete())
                            <button type="button"
                                    class="btn-action btn-delete"
                                    data-purchase-id="{{ $purchase->id }}"
                                    data-purchase-no="{{ htmlspecialchars($purchase->purchase_no, ENT_QUOTES) }}"
                                    title="Delete">
                                <span>🗑️</span>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="no-data">
                        <div class="no-data-content">
                            <div class="no-data-icon">📦</div>
                            <h3>No Purchases Found</h3>
                            <p>Get started by creating your first purchase</p>
                            <a href="{{ route('admin.purchases.create') }}" class="btn-primary">New Purchase</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-overlay" onclick="closeDeleteModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon modal-icon-danger">🗑️</div>
            <h3 class="modal-title">Delete Purchase</h3>
        </div>
        <p class="modal-text" id="deleteModalText">Are you sure you want to delete this purchase?</p>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-confirm">Delete</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .purchases-container { padding-bottom: 40px; }
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }

    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .page-description { color: #718096; font-size: 14px; }
    .btn-primary { padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }

    .stats-section { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 4px solid; }
    .card-total { border-color: #667eea; }
    .card-confirmed { border-color: #48bb78; }
    .card-draft { border-color: #ed8936; }
    .card-amount { border-color: #f6ad55; }
    .stat-icon { font-size: 42px; }
    .stat-value { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .stat-label { color: #718096; font-size: 14px; }

    .filters-section { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; gap: 20px; }
    .search-box { flex: 1; max-width: 400px; position: relative; }
    .search-input { width: 100%; padding: 12px 16px 12px 45px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; }
    .search-btn { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; }
    .filter-actions { display: flex; gap: 12px; }
    .filter-select { padding: 10px 16px; border: 2px solid #e2e8f0; border-radius: 8px; background: white; font-size: 14px; }

    .table-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .data-table th { padding: 16px 20px; text-align: left; color: white; font-weight: 600; font-size: 13px; text-transform: uppercase; }
    .data-table tbody tr { border-bottom: 1px solid #e2e8f0; transition: all 0.3s; }
    .data-table tbody tr:hover { background: #f7fafc; }
    .data-table td { padding: 16px 20px; font-size: 14px; }

    .purchase-no { font-family: monospace; color: #667eea; font-weight: 600; }
    .supplier-name { font-weight: 600; color: #2d3748; }
    .supplier-code { font-size: 12px; color: #a0aec0; }
    .items-badge { background: #ebf8ff; color: #2c5282; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    .amount-paid { color: #22543d; font-weight: 600; }
    .amount-due { color: #c05621; font-weight: 600; }
    .amount-clear { color: #a0aec0; }

    .payment-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .payment-paid { background: #c6f6d5; color: #22543d; }
    .payment-partial { background: #feebc8; color: #7c2d12; }
    .payment-unpaid { background: #fed7d7; color: #9b2c2c; }

    .status-badge { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; }
    .status-draft { background: #feebc8; color: #7c2d12; }
    .status-draft .status-dot { background: #ed8936; }
    .status-confirmed { background: #bee3f8; color: #2c5282; }
    .status-confirmed .status-dot { background: #3182ce; }
    .status-received { background: #c6f6d5; color: #22543d; }
    .status-received .status-dot { background: #38a169; animation: pulse 2s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

    .action-buttons { display: flex; gap: 8px; justify-content: center; }
    .btn-action { width: 36px; height: 36px; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; font-size: 16px; text-decoration: none; }
    .btn-view { background: #ebf8ff; color: #2c5282; }
    .btn-edit { background: #fef5e7; color: #7c2d12; }
    .btn-invoice { background: #e9d8fd; color: #553c9a; }
    .btn-confirm { background: #c6f6d5; color: #22543d; }
    .btn-delete { background: #fed7d7; color: #9b2c2c; }
    .btn-action:hover { transform: scale(1.1); }

    .no-data { text-align: center; padding: 60px 20px; }
    .no-data-icon { font-size: 64px; margin-bottom: 20px; }

    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center; }
    .modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
    .modal-content { position: relative; background: white; border-radius: 20px; padding: 35px; max-width: 450px; width: 90%; animation: modalSlideIn 0.3s ease; }
    @keyframes modalSlideIn { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
    .modal-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
    .modal-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .modal-icon-danger { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); }
    .modal-title { font-size: 22px; font-weight: 700; color: #2d3748; }
    .modal-text { color: #4a5568; margin-bottom: 25px; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 12px; }
    .btn-modal { padding: 12px 24px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; }
    .btn-cancel { background: #e2e8f0; color: #4a5568; }
    .btn-confirm { background: #f56565; color: white; }
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

    function openDeleteModal(purchaseId, purchaseNo) {
        document.getElementById('deleteModalText').innerHTML = `Are you sure you want to delete <strong>${purchaseNo}</strong>?`;
        document.getElementById('deleteForm').action = `/admin/purchases/${purchaseId}`;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                openDeleteModal(this.dataset.purchaseId, this.dataset.purchaseNo);
            });
        });
    });

    // Search
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const search = e.target.value.toLowerCase();
        document.querySelectorAll('#purchasesTableBody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        });
    });

    // Filters
    document.getElementById('statusFilter').addEventListener('change', function() {
        filterTable();
    });
    document.getElementById('paymentFilter').addEventListener('change', function() {
        filterTable();
    });
    document.getElementById('supplierFilter').addEventListener('change', function() {
        filterTable();
    });

    function filterTable() {
        const status = document.getElementById('statusFilter').value;
        const payment = document.getElementById('paymentFilter').value;
        const supplier = document.getElementById('supplierFilter').value;

        document.querySelectorAll('#purchasesTableBody tr').forEach(row => {
            const rowStatus = row.dataset.status;
            const rowPayment = row.dataset.payment;
            const rowSupplier = row.dataset.supplier;

            const matchStatus = !status || rowStatus === status;
            const matchPayment = !payment || rowPayment === payment;
            const matchSupplier = !supplier || rowSupplier === supplier;

            row.style.display = (matchStatus && matchPayment && matchSupplier) ? '' : 'none';
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
