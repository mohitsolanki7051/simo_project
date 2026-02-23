@extends('layouts.admin')

@section('title', 'Payment In - Admin Panel')
@section('header-title', 'Payment In Management')

@section('content')
<div class="payments-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Payment In</h2>
            @if(isset($selectedParty) && $selectedParty)
            <div class="active-filter">
                <span class="filter-badge">
                    {{ $selectedParty->name }} ({{ ucfirst($selectedParty->party_type) }})
                    <a href="{{ route('admin.payments.index') }}" class="remove-filter">×</a>
                </span>
            </div>
            @endif
        </div>
        <div class="header-right">
            <button type="button" class="btn-small btn-primary" onclick="openSelectPartyModal()">
                <span class="btn-icon">👥</span> Select Party
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-card">
        <div class="card-header">
            <h5 class="card-title">Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payments.index') }}" id="filterForm">
                <div class="filters-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Search</label>
                            <input type="text" name="search" id="searchPayments" class="form-control"
                                   placeholder="Search by invoice or party..." value="{{ request('search') }}">
                        </div>
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" name="from_date" id="filterFromDate" class="form-control"
                                   value="{{ request('from_date') }}">
                        </div>
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" name="to_date" id="filterToDate" class="form-control"
                                   value="{{ request('to_date') }}">
                        </div>
                        <div class="form-group btn-group">
                            <button type="submit" class="btn-apply">Apply Filters</button>
                            <a href="{{ route('admin.payments.index') }}" class="btn-reset">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table - Shows ALL invoices -->
    <div class="table-wrapper">
        <table class="compact-table">
            <thead>
                <tr>
                    <th class="th-date">Date</th>
                    <th class="th-invoice">Invoice No</th>
                    <th class="th-customer">Party</th>
                    <th class="th-type">Type</th>
                    <th class="th-amount">Invoice Total</th>
                    <th class="th-amount">Paid Amount</th>
                    <th class="th-amount">Due Amount</th>
                    <th class="th-status">Status</th>
                    <th class="th-actions">Action</th>
                </tr>
            </thead>
            <tbody id="invoicesTableBody">
                @forelse($invoices as $invoice)
                @php
                    $party = $invoice->party;
                    $grandTotal = $invoice->grand_total instanceof Decimal128 ? (float) $invoice->grand_total->__toString() : (float) $invoice->grand_total;
                    $totalPaid = $invoice->total_paid instanceof Decimal128 ? (float) $invoice->total_paid->__toString() : (float) ($invoice->total_paid ?? 0);
                    $balanceAmount = $invoice->balance_amount instanceof Decimal128 ? (float) $invoice->balance_amount->__toString() : (float) ($invoice->balance_amount ?? 0);

                    $statusClass = $invoice->payment_status == 'paid' ? 'status-paid' : ($invoice->payment_status == 'partial' ? 'status-partial' : 'status-unpaid');
                @endphp
                <tr data-invoice-id="{{ $invoice->_id }}" data-party-id="{{ $party->_id ?? '' }}">
                    <td class="td-date">{{ $invoice->invoice_date->format('d M Y') }}</td>
                    <td class="td-invoice">
                        <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                    </td>
                    <td class="td-customer">
                        <div class="customer-name">{{ $party->name ?? '-' }}</div>
                        @if($party && $party->phone)
                        <div class="customer-phone">{{ $party->phone }}</div>
                        @endif
                    </td>
                    <td class="td-type">
                        @if($party)
                        <span class="party-badge party-{{ $party->party_type }}">
                            {{ ucfirst($party->party_type) }}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td class="td-amount">
                        <div class="amount-value">₹ {{ number_format($grandTotal, 2) }}</div>
                    </td>
                    <td class="td-amount">
                        <div class="amount-value {{ $totalPaid > 0 ? '' : 'text-muted' }}">
                            ₹ {{ number_format($totalPaid, 2) }}
                        </div>
                    </td>
                    <td class="td-amount">
                        <div class="amount-value {{ $balanceAmount > 0 ? 'text-danger' : 'text-success' }}">
                            ₹ {{ number_format($balanceAmount, 2) }}
                        </div>
                    </td>
                    <td class="td-status">
                        <span class="status-badge {{ $statusClass }}">
                            {{ ucfirst($invoice->payment_status) }}
                        </span>
                    </td>
                    <td class="td-actions">
                        @if($invoice->payment_status != 'paid')
                        <button type="button" class="btn-add-payment" onclick="openPaymentModal('{{ $invoice->_id }}')">
                            Add Payment
                        </button>
                        @else
                        <button type="button" class="btn-view-payments" onclick="viewPayments('{{ $invoice->_id }}')">
                            View
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="empty-state">
                        <div class="empty-content">
                            <div class="empty-icon">📄</div>
                            <h4>No Invoices Found</h4>
                            <p>No sales invoices available</p>
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
            Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices
        </div>
        <div class="pagination">
            {{ $invoices->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Select Party Modal - Only for filtering -->
<div class="modal" id="selectPartyModal">
    <div class="modal-overlay" onclick="closeSelectPartyModal()"></div>
    <div class="modal-content modal-md">
        <div class="modal-header">
            <div class="modal-icon">👥</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Select Party</h4>
                <div class="modal-subtitle">Choose customer, dealer, or distributor to filter invoices</div>
            </div>
            <button type="button" class="modal-close" onclick="closeSelectPartyModal()">×</button>
        </div>

        <div class="modal-body">
            <!-- Party Type Tabs -->
            <div class="party-tabs">
                <button type="button" class="tab-btn active" data-type="all">All</button>
                <button type="button" class="tab-btn" data-type="customer">Customers</button>
                <button type="button" class="tab-btn" data-type="dealer">Dealers</button>
                <button type="button" class="tab-btn" data-type="distributor">Distributors</button>
            </div>

            <!-- Search -->
            <div class="search-container">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchParty" class="search-input" placeholder="Search by name or phone...">
                </div>
            </div>

            <!-- Parties List -->
            <div class="parties-list-container">
                <table class="parties-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="partiesTableBody">
                        @foreach($parties as $party)
                        <tr data-party-id="{{ $party['id'] }}" data-party-type="{{ $party['party_type'] }}" data-party-name="{{ $party['name'] }}" data-party-phone="{{ $party['phone'] }}">
                            <td>
                                <div class="party-name">{{ $party['name'] }}</div>
                            </td>
                            <td>{{ $party['phone'] ?? '-' }}</td>
                            <td>
                                <span class="party-badge party-{{ $party['party_type'] }}">
                                    {{ $party['party_type_text'] }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn-select-party" onclick="filterByParty('{{ $party['id'] }}', '{{ $party['name'] }}')">
                                    Select
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeSelectPartyModal()">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal" id="paymentModal">
    <div class="modal-overlay" onclick="closePaymentModal()"></div>
    <div class="modal-content modal-md">
        <div class="modal-header">
            <div class="modal-icon" style="background: #10b981;">💰</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Add Payment</h4>
                <div class="modal-subtitle">Enter payment details</div>
            </div>
            <button type="button" class="modal-close" onclick="closePaymentModal()">×</button>
        </div>

        <form id="paymentForm">
            @csrf
            <div class="modal-body">
                <div class="form-section-small">
                    <input type="hidden" name="sales_invoice_id" id="invoiceId">

                    <div class="invoice-info-box" id="invoiceInfoBox">
                        <!-- Will be populated via JS -->
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Payment Amount</label>
                        <input type="number" step="0.01" name="amount" id="paymentAmount" class="form-control"
                               placeholder="Enter amount" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Payment Method</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference_no" id="referenceNo" class="form-control" placeholder="Transaction ID / Cheque No">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closePaymentModal()">
                    Cancel
                </button>
                <button type="submit" class="btn-modal btn-primary" id="submitPaymentBtn">
                    Save Payment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Payments Modal -->
<div class="modal" id="viewPaymentsModal">
    <div class="modal-overlay" onclick="closeViewPaymentsModal()"></div>
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <div class="modal-icon" style="background: #10b981;">📊</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Payment History</h4>
                <div class="modal-subtitle">Invoice: <span id="viewInvoiceNumber"></span></div>
            </div>
            <button type="button" class="modal-close" onclick="closeViewPaymentsModal()">×</button>
        </div>

        <div class="modal-body">
            <div class="payments-history-table-container">
                <table class="payments-history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Reference</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsHistoryBody">
                    </tbody>
                </table>
                <div id="historyLoading" class="loading-state" style="display: none;">
                    <div class="loading-spinner"></div>
                    <p>Loading payment history...</p>
                </div>
            </div>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeViewPaymentsModal()">
                Close
            </button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Base Styles */
    .payments-container {
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

    .header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .active-filter {
        background: #e0f2fe;
        border-radius: 16px;
        padding: 4px 8px;
        font-size: 11px;
    }

    .filter-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #0369a1;
        font-weight: 500;
    }

    .remove-filter {
        color: #0369a1;
        text-decoration: none;
        font-size: 14px;
        font-weight: bold;
        line-height: 1;
    }

    .remove-filter:hover {
        color: #0284c7;
    }

    .header-right {
        display: flex;
        gap: 8px;
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

    .btn-primary {
        background: #3b82f6;
    }

    .btn-primary:hover {
        background: #2563eb;
    }

    .btn-icon {
        font-size: 12px;
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
    }

    .btn-apply {
        background: #667eea;
        color: white;
        border: none;
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

    /* Table Styles */
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

    /* Column Widths */
    .th-date { width: 90px; }
    .th-invoice { width: 120px; }
    .th-customer { width: 150px; }
    .th-type { width: 80px; }
    .th-amount { width: 100px; }
    .th-status { width: 80px; }
    .th-actions { width: 100px; }

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

    .customer-phone {
        font-size: 10px;
        color: #6b7280;
        margin-top: 2px;
    }

    /* Party Badge */
    .party-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 9px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .party-customer {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    .party-dealer {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .party-distributor {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    /* Amount */
    .amount-value {
        font-weight: 600;
        color: #1f2937;
        font-size: 12px;
    }

    .text-muted {
        color: #9ca3af;
    }

    .text-danger {
        color: #dc2626;
    }

    .text-success {
        color: #059669;
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

    .status-partial {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .status-unpaid {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    /* Action Buttons */
    .btn-add-payment {
        padding: 4px 10px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-add-payment:hover {
        background: #059669;
    }

    .btn-view-payments {
        padding: 4px 10px;
        background: #6b7280;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-view-payments:hover {
        background: #4b5563;
    }

    .btn-delete-payment {
        padding: 2px 6px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 3px;
        font-size: 9px;
        cursor: pointer;
    }

    .btn-delete-payment:hover {
        background: #dc2626;
    }

    /* Empty State */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-content {
        display: inline-block;
        text-align: center;
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-content h4 {
        font-size: 16px;
        color: #374151;
        margin-bottom: 8px;
    }

    .empty-content p {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 20px;
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
        margin-top: 10px;
    }

    .footer-info {
        font-weight: 500;
    }

    .pagination {
        display: flex;
        gap: 5px;
    }

    /* Modal Styles */
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
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        animation: modalFadeIn 0.2s ease;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    .modal-md {
        max-width: 500px;
    }

    .modal-lg {
        max-width: 700px;
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
        background: #555;
    }

    .modal-body {
        padding: 24px;
    }

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
        min-width: 100px;
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
    }

    .btn-modal.btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-modal.btn-primary:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
    }

    /* Party Tabs */
    .party-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 15px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 10px;
    }

    .tab-btn {
        padding: 6px 12px;
        background: none;
        border: none;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s;
    }

    .tab-btn:hover {
        background: #f3f4f6;
        color: #1f2937;
    }

    .tab-btn.active {
        background: #e0f2fe;
        color: #0369a1;
        font-weight: 600;
    }

    /* Search Container */
    .search-container {
        margin-bottom: 15px;
    }

    .search-box {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 11px;
        color: #666;
    }

    .search-input {
        width: 100%;
        padding: 8px 10px 8px 30px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 11px;
        background: #fff;
    }

    .search-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Parties Table */
    .parties-list-container {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    .parties-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .parties-table th {
        background: #f9fafb;
        padding: 10px;
        text-align: left;
        font-weight: 600;
        color: #4b5563;
        border-bottom: 1px solid #e5e7eb;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .parties-table td {
        padding: 10px;
        border-bottom: 1px solid #f3f4f6;
    }

    .parties-table tr:hover {
        background: #f9fafb;
    }

    .btn-select-party {
        padding: 4px 10px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-select-party:hover {
        background: #2563eb;
    }

    /* Invoice Info Box */
    .invoice-info-box {
        background: #f0f9ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 11px;
    }

    .info-label {
        color: #4b5563;
        font-weight: 500;
    }

    .info-value {
        color: #1f2937;
        font-weight: 600;
    }

    .info-total {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px dashed #bfdbfe;
        font-weight: 700;
        color: #1e40af;
    }

    /* Form Styles */
    .form-section-small {
        margin-bottom: 15px;
    }

    .form-label {
        display: block;
        font-size: 11px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 4px;
    }

    .form-label.required::after {
        content: ' *';
        color: #dc2626;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 60px;
    }

    .form-hint {
        display: block;
        font-size: 10px;
        color: #6b7280;
        margin-top: 4px;
    }

    /* Payments History Table */
    .payments-history-table-container {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    .payments-history-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .payments-history-table th {
        background: #f9fafb;
        padding: 10px;
        text-align: left;
        font-weight: 600;
        color: #4b5563;
        border-bottom: 1px solid #e5e7eb;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .payments-history-table td {
        padding: 10px;
        border-bottom: 1px solid #f3f4f6;
    }

    /* Loading State */
    .loading-state {
        padding: 40px 20px;
        text-align: center;
    }

    .loading-spinner {
        width: 30px;
        height: 30px;
        border: 3px solid #f3f4f6;
        border-top-color: #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 10px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .loading-state p {
        font-size: 11px;
        color: #6b7280;
        margin: 0;
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

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
</style>
@endpush

@push('scripts')
<script>
// ===================== GLOBAL VARIABLES =====================
let currentInvoiceId = null;

// ===================== MODAL FUNCTIONS =====================

function openSelectPartyModal() {
    $('#selectPartyModal').css('display', 'flex');
    filterPartiesByType('all');
}

function closeSelectPartyModal() {
    $('#selectPartyModal').hide();
    $('#searchParty').val('');
}

function openPaymentModal(invoiceId) {
    currentInvoiceId = invoiceId;

    // Show loading
    $('#invoiceInfoBox').html('<div class="text-center">Loading...</div>');
    $('#paymentModal').css('display', 'flex');

    // Fetch invoice details
    $.get('/admin/payments/invoice/' + invoiceId, function(response) {
        if (response.success) {
            const invoice = response.invoice;

            const infoHtml = `
                <div class="info-row">
                    <span class="info-label">Party:</span>
                    <span class="info-value">${invoice.party_name}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Invoice:</span>
                    <span class="info-value">${invoice.invoice_number}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Invoice Total:</span>
                    <span class="info-value">₹ ${invoice.grand_total.toFixed(2)}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Paid Amount:</span>
                    <span class="info-value">₹ ${invoice.total_paid.toFixed(2)}</span>
                </div>
                <div class="info-row info-total">
                    <span class="info-label">Due Amount:</span>
                    <span class="info-value">₹ ${invoice.balance_amount.toFixed(2)}</span>
                </div>
            `;
            $('#invoiceInfoBox').html(infoHtml);
            $('#invoiceId').val(invoice.id);
            $('#referenceNo').val(invoice.invoice_number);
            $('#paymentAmount').attr('max', invoice.balance_amount);
            $('#paymentAmount').attr('placeholder', `Max: ₹ ${invoice.balance_amount.toFixed(2)}`);
        } else {
            showAlert('Failed to load invoice details', 'error');
            closePaymentModal();
        }
    }).fail(function() {
        showAlert('Failed to load invoice details', 'error');
        closePaymentModal();
    });
}

function closePaymentModal() {
    $('#paymentModal').hide();
    $('#paymentForm')[0].reset();
    $('#invoiceInfoBox').empty();
    currentInvoiceId = null;
}

function openViewPaymentsModal(invoiceId) {
    $('#viewPaymentsModal').css('display', 'flex');
    $('#historyLoading').show();
    $('#paymentsHistoryBody').empty();

    // This would need an API endpoint to get payments for an invoice
    // For now, just close it
    setTimeout(() => {
        $('#historyLoading').hide();
        $('#viewPaymentsModal').hide();
        showAlert('View payments feature coming soon', 'info');
    }, 500);
}

function closeViewPaymentsModal() {
    $('#viewPaymentsModal').hide();
    $('#paymentsHistoryBody').empty();
}

// ===================== PARTY FILTERING =====================

$('.tab-btn').click(function() {
    $('.tab-btn').removeClass('active');
    $(this).addClass('active');
    filterPartiesByType($(this).data('type'));
});

function filterPartiesByType(type) {
    const search = $('#searchParty').val().toLowerCase();

    $('#partiesTableBody tr').each(function() {
        const partyType = $(this).data('party-type');
        const partyName = $(this).data('party-name').toLowerCase();
        const partyPhone = $(this).data('party-phone') ? $(this).data('party-phone').toLowerCase() : '';

        let showByType = type === 'all' || partyType === type;
        let showBySearch = search === '' ||
                          partyName.includes(search) ||
                          (partyPhone && partyPhone.includes(search));

        $(this).toggle(showByType && showBySearch);
    });
}

$('#searchParty').on('input', function() {
    const activeTab = $('.tab-btn.active').data('type');
    filterPartiesByType(activeTab);
});

// ===================== FILTER BY PARTY =====================

function filterByParty(partyId, partyName) {
    // Redirect with party filter
    window.location.href = '{{ route("admin.payments.index") }}?party_id=' + partyId;
}

// ===================== SUBMIT PAYMENT =====================

$('#paymentForm').submit(function(e) {
    e.preventDefault();

    const formData = $(this).serialize();
    const amount = parseFloat($('#paymentAmount').val());

    if (amount <= 0) {
        showAlert('Please enter a valid amount', 'error');
        return;
    }

    $('#submitPaymentBtn').prop('disabled', true).text('Saving...');

    $.ajax({
        url: '{{ route("admin.payments.store") }}',
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                closePaymentModal();

                // Reload page to show updated data
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showAlert('Error: ' + response.message, 'error');
                $('#submitPaymentBtn').prop('disabled', false).text('Save Payment');
            }
        },
        error: function(xhr) {
            showAlert('Failed to process payment: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
            $('#submitPaymentBtn').prop('disabled', false).text('Save Payment');
        }
    });
});

// ===================== VIEW PAYMENTS (Placeholder) =====================

function viewPayments(invoiceId) {
    showAlert('Payment history feature coming soon', 'info');
}

// ===================== HELPER FUNCTIONS =====================

function showAlert(message, type = 'success') {
    const container = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `<span>${message}</span>`;
    container.appendChild(alert);

    setTimeout(() => alert.remove(), 5000);
}

// Close modals on overlay click
$('.modal-overlay').click(function() {
    const modal = $(this).closest('.modal');
    if (modal.attr('id') === 'selectPartyModal') closeSelectPartyModal();
    else if (modal.attr('id') === 'paymentModal') closePaymentModal();
    else if (modal.attr('id') === 'viewPaymentsModal') closeViewPaymentsModal();
});

// Close modals on Escape key
$(document).keydown(function(event) {
    if (event.key === 'Escape') {
        closeSelectPartyModal();
        closePaymentModal();
        closeViewPaymentsModal();
    }
});
</script>
@endpush
