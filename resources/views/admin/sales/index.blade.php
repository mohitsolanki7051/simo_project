@extends('layouts.admin')

@section('title', 'Sales Invoices - Admin Panel')
@section('header-title', 'Sales Invoices Management')

@section('content')
<div class="products-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Sales Invoices</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.sales.create') }}" class="btn-small btn-primary">
                <span class="btn-icon">+</span> Create Sales Invoice
            </a>
        </div>
    </div>

    <!-- Report Summary Cards -->
    <div class="report-cards">
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">💰</div>
            <div class="card-info">
                <div class="card-title">Total Sales</div>
                <div class="card-value">₹ {{ number_format($totalSales, 2) }}</div>
                <div class="card-desc">All time sales amount</div>
            </div>
        </div>
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">✅</div>
            <div class="card-info">
                <div class="card-title">Paid</div>
                <div class="card-value">₹ {{ number_format($totalPaid, 2) }}</div>
                <div class="card-desc">Completed payments</div>
            </div>
        </div>
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);">⏰</div>
            <div class="card-info">
                <div class="card-title">Unpaid</div>
                <div class="card-value">₹ {{ number_format($totalUnpaid, 2) }}</div>
                <div class="card-desc">Pending payments</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card filters-card">
        <div class="card-header">
            <h5 class="card-title">Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sales.index') }}" class="filters-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>
                    <div class="form-group">
                        <label>Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control" value="{{ request('invoice_number') }}" placeholder="e.g., 2046">
                    </div>
                    <div class="form-group">
                        <label>Customer</label>
                        <select name="customer_id" class="form-control">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->_id }}" {{ request('customer_id') == $customer->_id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        </select>
                    </div>
                    <div class="form-group btn-group">
                        <button type="submit" class="btn-primary btn-apply">Apply Filters</button>
                        <a href="{{ route('admin.sales.index') }}" class="btn-secondary btn-reset">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="table-wrapper">
        <table class="compact-table">
            <thead>
                <tr>
                    <th class="th-sno">S.No.</th>
                    <th class="th-date">Date</th>
                    <th class="th-invoice">Invoice Number</th>
                    <th class="th-customer">Party Name</th>
                    <th class="th-due">Due In</th>
                    <th class="th-amount">Amount</th>
                    <th class="th-status">Status</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $index => $invoice)
                <tr>
                    <td class="td-sno">{{ ($invoices->currentPage() - 1) * $invoices->perPage() + $index + 1 }}</td>
                    <td class="td-date">{{ $invoice->invoice_date->format('d M Y') }}</td>
                    <td class="td-invoice">
                        <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                    </td>
                    <td class="td-customer">
                        <div class="customer-name">{{ $invoice->customer->name ?? 'N/A' }}</div>
                    </td>
                    <td class="td-due">
                        @if($invoice->due_in_days == '-')
                            <span class="due-text">-</span>
                        @elseif(str_contains($invoice->due_in_days, 'Overdue'))
                            <span class="due-overdue">{{ $invoice->due_in_days }}</span>
                        @elseif($invoice->due_in_days == 'Today')
                            <span class="due-today">{{ $invoice->due_in_days }}</span>
                        @else
                            <span class="due-future">{{ $invoice->due_in_days }}</span>
                        @endif
                    </td>
                    <td class="td-amount">
                        <div class="amount-value">₹ {{ number_format($invoice->grand_total, 2) }}</div>
                    </td>
                    <td class="td-status">
                        <span class="status-badge status-{{ $invoice->payment_status }}">
                            {{ ucfirst($invoice->payment_status) }}
                        </span>
                    </td>
                    <td class="td-actions">
                        <div class="action-icons">
                            <a href="{{ route('admin.sales.show', $invoice->_id) }}"
                               class="icon-btn icon-view" title="View Invoice">
                                👁️
                            </a>
                            <button type="button" class="icon-btn icon-delete delete-invoice"
                                    data-id="{{ $invoice->_id }}" title="Delete Invoice">
                                🗑️
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty-state">
                        <div class="empty-content">
                            <div class="empty-icon">📄</div>
                            <h4>No Invoices Found</h4>
                            <p>Start by creating your first sales invoice</p>
                            <a href="{{ route('admin.sales.create') }}" class="btn-small btn-primary mt-2">
                                <span class="btn-icon">+</span> Create Invoice
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->count() > 0)
    <div class="table-footer">
        <div class="footer-info">
            Showing {{ $invoices->count() }} of {{ $invoices->total() }} invoices
        </div>
       
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-overlay" onclick="closeDeleteModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon" style="background: #ef4444;">⚠️</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Confirm Delete</h4>
                <div class="modal-subtitle">This action cannot be undone</div>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">×</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this invoice? This will permanently remove the invoice and all associated data.</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn-modal btn-danger" id="confirmDelete">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Main Container - Same as attribute index */
    .products-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 12px;
        line-height: 1.4;
        padding: 15px;
    }

    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-title {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
        margin: 0 0 4px 0;
    }

    .btn-small {
        padding: 6px 12px;
        background: #fa8427;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-small:hover {
        background: #e97317;
        transform: translateY(-1px);
    }

    .btn-icon {
        font-size: 12px;
    }

    /* Report Cards */
    .report-cards {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }

    .report-card {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.2s;
        cursor: default;
        min-width: 300px;
    }

    .report-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        transform: translateY(-2px);
    }

    .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
    }

    .card-info {
        flex: 1;
    }

    .card-title {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .card-value {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .card-desc {
        font-size: 11px;
        color: #9ca3af;
    }

    /* Filters Card */
    .filters-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .filters-card .card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 8px 8px 0 0;
    }

    .filters-card .card-title {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin: 0;
    }

    .filters-card .card-body {
        padding: 16px;
    }

    .filters-form .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        align-items: end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .form-group label {
        font-size: 11px;
        font-weight: 500;
        color: #4b5563;
    }

    .form-group .form-control {
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 11px;
        background: #f9fafb;
        height: 32px;
    }

    .form-group .form-control:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-group {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn-apply, .btn-reset {
        padding: 6px 16px;
        border: none;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        height: 32px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-apply {
        background: #667eea;
        color: white;
    }

    .btn-apply:hover {
        background: #5a67d8;
        transform: translateY(-1px);
    }

    .btn-reset {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .btn-reset:hover {
        background: #e5e7eb;
    }

    /* Compact Table */
    .table-wrapper {
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        margin-top: 10px;
    }

    .compact-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .compact-table th {
        background: #f8fafc;
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
        color: #4b5563;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .compact-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .compact-table tr:last-child td {
        border-bottom: none;
    }

    .compact-table tr:hover {
        background: #f9fafb;
    }

    /* Column widths */
    .th-sno { width: 50px; }
    .th-date { width: 100px; }
    .th-invoice { width: 100px; }
    .th-customer { width: 200px; }
    .th-due { width: 100px; }
    .th-amount { width: 120px; }
    .th-status { width: 90px; }
    .th-actions { width: 100px; }

    /* S.No. */
    .td-sno {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        text-align: center;
    }

    /* Date */
    .td-date {
        color: #4b5563;
        font-size: 11px;
        white-space: nowrap;
    }

    /* Invoice Number */
    .invoice-number {
        font-weight: 600;
        color: #1f2937;
        background: #f0f9ff;
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #e0f2fe;
        font-size: 11px;
        text-align: center;
        display: inline-block;
    }

    /* Customer Name */
    .customer-name {
        font-weight: 500;
        color: #374151;
        line-height: 1.3;
        font-size: 12px;
    }

    /* Due In */
    .due-text {
        color: #6b7280;
        font-size: 11px;
    }

    .due-overdue {
        color: #ef4444;
        font-weight: 500;
        font-size: 11px;
        background: #fee2e2;
        padding: 3px 8px;
        border-radius: 4px;
    }

    .due-today {
        color: #d97706;
        font-weight: 500;
        font-size: 11px;
        background: #fef3c7;
        padding: 3px 8px;
        border-radius: 4px;
    }

    .due-future {
        color: #059669;
        font-weight: 500;
        font-size: 11px;
        background: #d1fae5;
        padding: 3px 8px;
        border-radius: 4px;
    }

    /* Amount */
    .amount-value {
        font-weight: 600;
        color: #1f2937;
        font-size: 12px;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-paid {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .status-unpaid {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .status-partial {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    /* Action Icons */
    .action-icons {
        display: flex;
        gap: 6px;
    }

    .icon-btn {
        width: 28px;
        height: 28px;
        border-radius: 5px;
        border: none;
        background: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: all 0.2s;
        padding: 0;
        text-decoration: none;
    }

    .icon-view {
        color: #3b82f6;
        background: #dbeafe;
    }

    .icon-view:hover {
        background: #bfdbfe;
        transform: scale(1.1);
    }

    .icon-delete {
        color: #ef4444;
        background: #fee2e2;
    }

    .icon-delete:hover {
        background: #fecaca;
        transform: scale(1.1);
    }

    /* Empty State */
    .empty-state {
        padding: 40px 20px;
        text-align: center;
    }

    .empty-content {
        display: inline-block;
    }

    .empty-icon {
        font-size: 32px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .empty-content h4 {
        font-size: 14px;
        color: #374151;
        margin-bottom: 5px;
    }

    .empty-content p {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 10px;
    }

    /* Table Footer */
    .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        font-size: 11px;
        color: #6b7280;
        border-radius: 0 0 6px 6px;
    }

    .footer-info {
        font-weight: 500;
    }

    /* Pagination */
    .pagination {
        display: flex;
        gap: 4px;
    }

    .pagination .page-item .page-link {
        padding: 4px 8px;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        font-size: 11px;
        color: #4b5563;
        text-decoration: none;
        transition: all 0.2s;
    }

    .pagination .page-item.active .page-link {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .pagination .page-item.disabled .page-link {
        color: #9ca3af;
        background: #f3f4f6;
    }

    .pagination .page-link:hover {
        background: #f3f4f6;
    }

    /* Modal Styles - Same as attribute index */
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
        backdrop-filter: blur(2px);
    }

    .modal-content {
        position: relative;
        background: white;
        border-radius: 12px;
        padding: 0;
        width: 90%;
        max-width: 400px;
        max-height: 90vh;
        overflow-y: auto;
        animation: modalFadeIn 0.2s ease;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px 12px 0 0;
    }

    .modal-title-section {
        flex: 1;
    }

    .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        line-height: 1.3;
    }

    .modal-subtitle {
        font-size: 11px;
        color: #6b7280;
        margin-top: 2px;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        color: #6b7280;
        cursor: pointer;
        padding: 4px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: #f3f4f6;
        color: #1f2937;
        transform: rotate(90deg);
    }

    .modal-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .modal-body {
        padding: 24px;
    }

    .modal-body p {
        font-size: 12px;
        color: #4b5563;
        line-height: 1.5;
        margin: 0;
    }

    /* Modal Buttons */
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px 24px;
        border-top: 1px solid #e5e7eb;
        background: #fafafa;
        border-radius: 0 0 12px 12px;
    }

    .btn-modal {
        padding: 8px 20px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 80px;
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
    }

    .btn-danger {
        background: #ef4444;
        color: white;
        border: 1px solid #ef4444;
    }

    .btn-danger:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }

    /* Alert Messages */
    #alertContainer {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    .alert {
        padding: 12px 16px;
        margin-bottom: 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .products-container {
            padding: 10px;
        }

        .page-header {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .report-cards {
            flex-direction: column;
        }

        .report-card {
            min-width: auto;
            width: 100%;
        }

        .filters-form .form-row {
            grid-template-columns: 1fr;
        }

        .btn-group {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-apply, .btn-reset {
            width: 100%;
        }

        .modal-content {
            margin: 20px;
            width: calc(100% - 40px);
            max-height: calc(100vh - 40px);
        }

        .modal-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
        }

        .table-footer {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .modal-body {
            padding: 16px;
        }

        .modal-actions {
            flex-direction: column;
            gap: 8px;
        }

        .btn-modal {
            width: 100%;
        }

        .compact-table th,
        .compact-table td {
            padding: 8px;
        }

        .action-icons {
            flex-direction: column;
        }

        .icon-btn {
            width: 100%;
            height: 28px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Delete invoice functionality
    let deleteInvoiceId = null;

    $(document).ready(function() {
        // Delete invoice button click
        $('.delete-invoice').click(function() {
            deleteInvoiceId = $(this).data('id');
            openDeleteModal();
        });
    });

    function openDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.style.display = 'flex';
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.style.display = 'none';
        deleteInvoiceId = null;
    }

    // Confirm delete
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (!deleteInvoiceId) return;

        $.ajax({
            url: '/admin/sales/' + deleteInvoiceId,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Invoice deleted successfully', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert(response.message || 'Failed to delete invoice', 'error');
                }
                closeDeleteModal();
            },
            error: function(xhr) {
                showAlert('Failed to delete invoice', 'error');
                closeDeleteModal();
            }
        });
    });

    // Show alert function
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function() {
            closeDeleteModal();
        });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeDeleteModal();
        }
    });

    // Filter form submission
    document.querySelector('.filters-form').addEventListener('submit', function(e) {
        // Add loading state to button
        const submitBtn = this.querySelector('.btn-apply');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Loading...';
        submitBtn.disabled = true;

        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 1000);
    });
</script>
@endpush
