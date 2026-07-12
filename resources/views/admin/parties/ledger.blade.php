@extends('layouts.admin')

@section('title', ucfirst($party->party_type) . ' Ledger - ' . $party->name)
@section('header-title', ucfirst($party->party_type) . ' Ledger: ' . $party->name)

@section('content')
<div class="ledger-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">{{ ucfirst($party->party_type) }} Ledger: {{ $party->name }}</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.parties.index', ['type' => $party->party_type]) }}" class="back-btn">
                ← Back to {{ ucfirst($party->party_type) }}s
            </a>
        </div>
    </div>

    <!-- Party Summary Cards -->
    <div class="party-summary">
        <div class="summary-card" onclick="switchTab('transactions-tab')" style="cursor: pointer;">
            <div class="summary-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">📊</div>
            <div class="summary-info">
                <div class="summary-label">Total Transactions</div>
                <div class="summary-value">{{ $transactions->count() }}</div>
                <div class="summary-detail">Sales: {{ $salesInvoices->count() }} | Payments: {{ $payments->count() }}</div>
            </div>
        </div>
        <div class="summary-card" onclick="switchTab('profile-tab')" style="cursor: pointer;">
            <div class="summary-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">👤</div>
            <div class="summary-info">
                <div class="summary-label">{{ ucfirst($party->party_type) }} Details</div>
                <div class="summary-value">{{ $party->name }}</div>
                <div class="summary-detail">{{ $party->phone }} | {{ $party->email ?? 'No email' }}</div>
            </div>
        </div>
        <div class="summary-card" onclick="switchTab('ledger-tab')" style="cursor: pointer;">
            <div class="summary-icon" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">💰</div>
            <div class="summary-info">
                <div class="summary-label">Current Balance</div>
                <div class="summary-value {{ $ledgerEntries->last()['balance'] > 0 ? 'text-danger' : ($ledgerEntries->last()['balance'] < 0 ? 'text-success' : '') }}">
                    ₹{{ number_format($ledgerEntries->last()['balance'] ?? 0, 2) }}
                </div>
                <div class="summary-detail">
                    {{ $ledgerEntries->last() && $ledgerEntries->last()['balance'] > 0 ? 'To Pay' : ($ledgerEntries->last() && $ledgerEntries->last()['balance'] < 0 ? 'To Receive' : 'Settled') }}
                </div>
            </div>
        </div>
        <div class="summary-card" onclick="switchTab('items-tab')" style="cursor: pointer;">
            <div class="summary-icon" style="background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);">📦</div>
            <div class="summary-info">
                <div class="summary-label">Items Purchased</div>
                <div class="summary-value">{{ $itemWiseReport->count() }}</div>
                <div class="summary-detail">Total Qty: {{ number_format($totalItems, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <button class="tab-btn active" onclick="switchTab('transactions-tab')" id="tab-transactions-btn">
            <span class="tab-icon">📋</span>
            Transactions
        </button>
        <button class="tab-btn" onclick="switchTab('profile-tab')" id="tab-profile-btn">
            <span class="tab-icon">👤</span>
            Profile
        </button>
        <button class="tab-btn" onclick="switchTab('ledger-tab')" id="tab-ledger-btn">
            <span class="tab-icon">📒</span>
            Ledger
        </button>
        <button class="tab-btn" onclick="switchTab('items-tab')" id="tab-items-btn">
            <span class="tab-icon">📦</span>
            Item-wise Report
        </button>
    </div>

    <!-- ========== TAB 1: TRANSACTIONS ========== -->
<!-- ========== TAB 1: TRANSACTIONS ========== -->
<div class="tab-content active" id="transactions-tab">
    <div class="tab-header">
        <h3>Transaction History</h3>
        <div class="transaction-filters">
            <select class="filter-select" id="transaction-type-filter">
                <option value="all">All Transactions</option>
                <option value="sale">Sales Invoices</option>
                <option value="payment">Payments Only</option>
            </select>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="compact-table" id="transactions-table">
            <thead>
                <tr>
                    <th class="th-date">Date</th>
                    <th class="th-type">Transaction Type</th>
                    <th class="th-ref">Transaction Number</th>
                    <th class="th-amount">Amount</th>
                    <th class="th-status">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr class="transaction-row" data-type="{{ $transaction['type_badge'] }}">
                    <td class="td-date">{{ \Carbon\Carbon::parse($transaction['date'])->format('d-m-Y') }}</td>
                    <td class="td-type">
                        <span class="type-badge type-{{ $transaction['type_badge'] }}">
                            {{ $transaction['type'] }}
                        </span>
                    </td>
                    <td class="td-ref">
                        @if(isset($transaction['link']))
                            <a href="{{ $transaction['link'] }}" class="ref-link">{{ $transaction['reference'] }}</a>
                        @else
                            {{ $transaction['reference'] }}
                        @endif
                    </td>
                    <td class="td-amount">{!! $transaction['amount_display'] !!}</td>
                    <td class="td-status">
                        @if($transaction['status'])
                            <span class="status-badge status-{{ $transaction['status_badge'] }}">
                                {{ $transaction['status'] }}
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        <div class="empty-content">
                            <div class="empty-icon">📋</div>
                            <h4>No Transactions Found</h4>
                            <p>No transactions recorded for this {{ $party->party_type }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

    <!-- ========== TAB 2: PROFILE ========== -->
    <div class="tab-content" id="profile-tab">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    {{ substr($party->name, 0, 1) }}
                </div>
                <div class="profile-title">
                    <h3>{{ $party->name }}</h3>
                    <span class="profile-type-badge type-{{ $party->party_type }}">{{ ucfirst($party->party_type) }}</span>
                </div>
                <div class="profile-status">
                    <span class="status-badge status-{{ $party->status }}">
                        {{ ucfirst($party->status) }}
                    </span>
                </div>
            </div>

            <div class="profile-grid">
                <!-- Contact Information -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <span class="card-icon">📞</span>
                        <h4>Contact Information</h4>
                    </div>
                    <div class="profile-card-body">
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value">{{ $party->phone }}</span>
                        </div>
                        @if($party->email)
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value">{{ $party->email }}</span>
                        </div>
                        @endif
                        @if($party->gst_number)
                        <div class="info-row">
                            <span class="info-label">GST Number:</span>
                            <span class="info-value">{{ $party->gst_number }}</span>
                        </div>
                        @endif
                        @if($party->pan_number)
                        <div class="info-row">
                            <span class="info-label">PAN Number:</span>
                            <span class="info-value">{{ $party->pan_number }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <span class="card-icon">💰</span>
                        <h4>Financial Information</h4>
                    </div>
                    <div class="profile-card-body">
                        <div class="info-row">
                            <span class="info-label">Opening Balance:</span>
                            <span class="info-value">₹{{ number_format($party->opening_balance ?? 0, 2) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Credit Limit:</span>
                            <span class="info-value">₹{{ number_format($party->credit_limit ?? 0, 2) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Current Balance:</span>
                            <span class="info-value {{ $ledgerEntries->last()['balance'] > 0 ? 'text-danger' : ($ledgerEntries->last()['balance'] < 0 ? 'text-success' : '') }}">
                                ₹{{ number_format($ledgerEntries->last()['balance'] ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Total Sales:</span>
                            <span class="info-value">₹{{ number_format($totalSales, 2) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Total Payments:</span>
                            <span class="info-value">₹{{ number_format($totalPayments, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <span class="card-icon">📝</span>
                        <h4>Additional Information</h4>
                    </div>
                    <div class="profile-card-body">
                        @if($party->salesman_id)
                        <div class="info-row">
                            <span class="info-label">Assigned Salesman:</span>
                            <span class="info-value">
                                @php
                                    $salesman = \App\Models\Salesman::find($party->salesman_id);
                                @endphp
                                {{ $salesman->name ?? 'N/A' }}
                            </span>
                        </div>
                        @endif

                        @if($party->parent_party_id && $party->party_type === 'dealer')
                        <div class="info-row">
                            <span class="info-label">Parent Distributor:</span>
                            <span class="info-value">
                                @php
                                    $parent = \App\Models\Customer::find($party->parent_party_id);
                                @endphp
                                {{ $parent->name ?? 'N/A' }}
                            </span>
                        </div>
                        @endif

                        <div class="info-row">
                            <span class="info-label">Created:</span>
                            <span class="info-value">{{ $party->created_at ? $party->created_at->format('d-m-Y') : 'N/A' }}</span>
                        </div>

                        @if($party->notes)
                        <div class="info-row">
                            <span class="info-label">Notes:</span>
                            <span class="info-value">{{ $party->notes }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Addresses -->
                <div class="profile-card full-width">
                    <div class="profile-card-header">
                        <span class="card-icon">📍</span>
                        <h4>Addresses</h4>
                    </div>
                    <div class="profile-card-body addresses-grid">
                        @forelse($party->addresses as $address)
                        <div class="address-card {{ $address->is_default ? 'default-address' : '' }}">
                            <div class="address-type">
                                {{ ucfirst($address->type) }} Address
                                @if($address->is_default)
                                    <span class="default-badge">Default</span>
                                @endif
                            </div>
                            <div class="address-details">
                                <p>{{ $address->address }}</p>
                                <p>{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</p>
                                <p>{{ $address->country }}</p>
                                @if($address->landmark)
                                    <p class="landmark">Landmark: {{ $address->landmark }}</p>
                                @endif
                                @if($address->contact_person)
                                    <p class="contact-person">Contact: {{ $address->contact_person }} ({{ $address->contact_number }})</p>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="no-address">No addresses found</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== TAB 3: LEDGER ========== -->
    <div class="tab-content" id="ledger-tab">
        <div class="table-wrapper">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th class="th-date">Date</th>
                        <th class="th-voucher">Voucher Type</th>
                        <th class="th-ref">Voucher No.</th>
                        <th class="th-debit">Debit (₹)</th>
                        <th class="th-credit">Credit (₹)</th>
                        <th class="th-balance">Balance (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgerEntries as $entry)
                    <tr class="{{ $entry['is_opening'] ? 'opening-balance-row' : '' }}">
                        <td class="td-date">
                            @if($entry['is_opening'])
                                {{ $entry['date'] ? \Carbon\Carbon::parse($entry['date'])->format('d-m-Y') : 'Opening' }}
                            @else
                                {{ \Carbon\Carbon::parse($entry['date'])->format('d-m-Y') }}
                            @endif
                        </td>
                        <td class="td-voucher">
                            <span class="voucher-badge voucher-{{ strtolower(str_replace(' ', '-', $entry['voucher_type'])) }}">
                                {{ $entry['voucher_type'] }}
                            </span>
                        </td>
                        <td class="td-ref">{{ $entry['voucher_no'] }}</td>
                        <td class="td-debit">{{ $entry['debit'] > 0 ? '₹ ' . number_format($entry['debit'], 2) : '-' }}</td>
                        <td class="td-credit">{{ $entry['credit'] > 0 ? '₹ ' . number_format($entry['credit'], 2) : '-' }}</td>
                        <td class="td-balance {{ $entry['balance'] > 0 ? 'text-danger' : ($entry['balance'] < 0 ? 'text-success' : '') }}">
                            ₹ {{ number_format($entry['balance'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <div class="empty-content">
                                <div class="empty-icon">📒</div>
                                <h4>No Ledger Entries Found</h4>
                                <p>No transactions recorded for this {{ $party->party_type }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($ledgerEntries->count() > 0)
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Totals (Excluding Opening):</th>
                        <th class="td-debit">₹ {{ number_format($ledgerEntries->where('is_opening', false)->sum('debit'), 2) }}</th>
                        <th class="td-credit">₹ {{ number_format($ledgerEntries->where('is_opening', false)->sum('credit'), 2) }}</th>
                        <th class="td-balance">₹ {{ number_format($ledgerEntries->last()['balance'] ?? 0, 2) }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- ========== TAB 4: ITEM WISE REPORT ========== -->
    <div class="tab-content" id="items-tab">
        <div class="tab-header">
            <h3>Item-wise Sales Report</h3>
            <div class="summary-stats">
                <span class="stat-badge">Total Items: {{ $itemWiseReport->count() }}</span>
                <span class="stat-badge">Total Qty: {{ number_format($totalItems, 2) }}</span>
                <span class="stat-badge">Total Amount: ₹{{ number_format($itemWiseReport->sum('total_amount'), 2) }}</span>
            </div>
        </div>

        <div class="accordion-container">
            @forelse($itemWiseReport as $index => $item)
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <div class="product-info">
                        <span class="product-sno">{{ $index + 1 }}.</span>
                        <span class="product-name">{{ $item['product_name'] }}</span>
                        <span class="product-sku">{{ $item['sku'] }}</span>
                    </div>
                    <div class="product-summary">
                        <span class="product-qty">Qty: {{ number_format($item['total_quantity'], 2) }}</span>
                        <span class="product-amount">₹{{ number_format($item['total_amount'], 2) }}</span>
                        <span class="accordion-icon">▼</span>
                    </div>
                </div>
                <div class="accordion-content" style="display: none;">
                    <table class="inner-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice No.</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item['invoices'] as $invoice)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($invoice['date'])->format('d-m-Y') }}</td>
                                <td>{{ $invoice['invoice_no'] }}</td>
                                <td>{{ number_format($invoice['quantity'], 2) }}</td>
                                <td>₹{{ number_format($invoice['price'], 2) }}</td>
                                <td>₹{{ number_format($invoice['total'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">Total:</th>
                                <th>{{ number_format($item['total_quantity'], 2) }}</th>
                                <th>-</th>
                                <th>₹{{ number_format($item['total_amount'], 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-content">
                    <div class="empty-icon">📦</div>
                    <h4>No Items Found</h4>
                    <p>No items have been purchased by this {{ $party->party_type }}</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Main Container */
    .ledger-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 12px;
        line-height: 1.4;
    }

    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-title {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }

    .back-btn {
        color: #fa8128;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        padding: 5px 10px;
        border-radius: 4px;
        transition: all 0.2s;
        border: 1px solid #dee2e6;
        background: white;
    }

    .back-btn:hover {
        background: #fff0e6;
    }

    /* Party Summary Cards */
    .party-summary {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }

    .summary-card {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .summary-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        transform: translateY(-2px);
    }

    .summary-icon {
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

    .summary-info {
        flex: 1;
    }

    .summary-label {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .summary-value {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .summary-detail {
        font-size: 11px;
        color: #9ca3af;
    }

    .text-danger {
        color: #dc2626 !important;
    }

    .text-success {
        color: #059669 !important;
    }

    /* Tab Navigation */
    .tab-navigation {
        display: flex;
        gap: 5px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 0;
    }

    .tab-btn {
        padding: 10px 20px;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        font-size: 13px;
        font-weight: 500;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tab-btn:hover {
        color: #374151;
        background: #f9fafb;
    }

    .tab-btn.active {
        color: #667eea;
        border-bottom-color: #667eea;
        background: #f0f4ff;
    }

    .tab-icon {
        font-size: 14px;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Tab Headers */
    .tab-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .tab-header h3 {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin: 0;
    }

    .transaction-filters {
        display: flex;
        gap: 10px;
    }

    .filter-select {
        padding: 5px 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 11px;
        background: white;
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
        padding: 8px 10px;
        text-align: left;
        font-weight: 600;
        color: #4b5563;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .compact-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .compact-table tfoot th {
        background: #f8fafc;
        padding: 8px 10px;
        font-weight: 600;
        color: #1f2937;
        border-top: 2px solid #e2e8f0;
    }

    .compact-table tr:last-child td {
        border-bottom: none;
    }

    .compact-table tr:hover {
        background: #f9fafb;
    }

    .opening-balance-row {
        background: #f0f9ff;
        font-weight: 600;
    }

    /* Column widths */
    .th-date { width: 100px; }
    .th-type { width: 120px; }
    .th-ref { width: 150px; }
    .th-amount { width: 120px; }
    .th-status { width: 80px; }
    .th-details { width: 200px; }
    .th-voucher { width: 120px; }
    .th-debit, .th-credit, .th-balance { width: 120px; }

    .text-right {
        text-align: right;
    }

    /* Type Badges */
    .type-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .type-sale {
        background: #dbeafe;
        color: #1e40af;
    }

    .type-payment {
        background: #d1fae5;
        color: #065f46;
    }

    .type-advance {
        background: #fef3c7;
        color: #92400e;
    }

    .voucher-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 9px;
        font-weight: 600;
    }

    .voucher-sales-invoice {
        background: #dbeafe;
        color: #1e40af;
    }

    .voucher-payment-received {
        background: #d1fae5;
        color: #065f46;
    }

    .voucher-advance-payment {
        background: #fef3c7;
        color: #92400e;
    }

    .voucher-discount {
        background: #f3e8ff;
        color: #6b21a8;
    }

    .voucher-opening-balance {
        background: #f1f5f9;
        color: #334155;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 9px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-paid, .status-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-unpaid {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-partial {
        background: #fef3c7;
        color: #92400e;
    }

    /* Transaction Details */
    .transaction-details {
        display: flex;
        gap: 5px;
        font-size: 10px;
        color: #6b7280;
    }

    .detail-sep {
        color: #d1d5db;
    }

    .ref-link {
        color: #667eea;
        text-decoration: none;
    }

    .ref-link:hover {
        text-decoration: underline;
    }

    /* Profile Styles */
    .profile-container {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 20px;
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 20px;
    }

    .profile-avatar {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 600;
        color: white;
        text-transform: uppercase;
    }

    .profile-title {
        flex: 1;
    }

    .profile-title h3 {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 5px 0;
    }

    .profile-type-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .profile-type-badge.type-customer {
        background: #dbeafe;
        color: #1e40af;
    }

    .profile-type-badge.type-dealer {
        background: #d1fae5;
        color: #065f46;
    }

    .profile-type-badge.type-distributor {
        background: #fef3c7;
        color: #92400e;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .profile-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    .profile-card.full-width {
        grid-column: span 2;
    }

    .profile-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        background: white;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-icon {
        font-size: 16px;
    }

    .profile-card-header h4 {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin: 0;
    }

    .profile-card-body {
        padding: 15px;
    }

    .info-row {
        display: flex;
        padding: 8px 0;
        border-bottom: 1px dashed #e5e7eb;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        width: 120px;
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
    }

    .info-value {
        flex: 1;
        font-size: 11px;
        color: #1f2937;
        font-weight: 500;
    }

    /* Addresses Grid */
    .addresses-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .address-card {
        padding: 15px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        position: relative;
    }

    .address-card.default-address {
        border-color: #667eea;
        background: #f0f4ff;
    }

    .address-type {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .default-badge {
        background: #667eea;
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 8px;
        font-weight: 600;
    }

    .address-details p {
        margin: 3px 0;
        font-size: 11px;
        color: #4b5563;
    }

    .landmark, .contact-person {
        color: #6b7280;
        font-size: 10px;
        margin-top: 5px;
    }

    .no-address {
        grid-column: span 2;
        text-align: center;
        padding: 20px;
        color: #9ca3af;
        font-style: italic;
    }

    /* Summary Stats */
    .summary-stats {
        display: flex;
        gap: 10px;
    }

    .stat-badge {
        padding: 4px 10px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        font-size: 10px;
        color: #4b5563;
    }

    /* Accordion Styles */
    .accordion-container {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        overflow: hidden;
    }

    .accordion-item {
        border-bottom: 1px solid #e5e7eb;
    }

    .accordion-item:last-child {
        border-bottom: none;
    }

    .accordion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        background: #f9fafb;
        cursor: pointer;
        transition: background 0.2s;
    }

    .accordion-header:hover {
        background: #f3f4f6;
    }

    .product-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .product-sno {
        font-weight: 600;
        color: #6b7280;
        min-width: 25px;
    }

    .product-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 12px;
    }

    .product-sku {
        font-size: 10px;
        color: #6b7280;
        background: #e5e7eb;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .product-summary {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .product-qty {
        font-size: 11px;
        color: #4b5563;
    }

    .product-amount {
        font-weight: 600;
        color: #059669;
        min-width: 100px;
        text-align: right;
    }

    .accordion-icon {
        font-size: 10px;
        color: #9ca3af;
        transition: transform 0.2s;
    }

    .accordion-header.active .accordion-icon {
        transform: rotate(180deg);
    }

    .accordion-content {
        padding: 15px;
        background: white;
        border-top: 1px solid #e5e7eb;
    }

    .inner-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .inner-table th {
        background: #f8fafc;
        padding: 6px 10px;
        text-align: left;
        font-weight: 600;
        color: #4b5563;
        border-bottom: 1px solid #e2e8f0;
    }

    .inner-table td {
        padding: 6px 10px;
        border-bottom: 1px solid #f3f4f6;
    }

    .inner-table tfoot th {
        background: #f8fafc;
        padding: 6px 10px;
        border-top: 1px solid #e2e8f0;
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
    }

    /* Table Footer */
    .table-footer {
        padding: 10px 15px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        font-size: 11px;
        color: #6b7280;
        border-radius: 0 0 6px 6px;
        text-align: center;
    }

    /* Debit/Credit/Balance */
    .td-debit, .td-credit, .td-balance, .td-amount {
        font-weight: 600;
    }
.td-amount {
    font-weight: 500;
    color: #1f2937;
}

.td-amount .unpaid-note {
    color: #dc2626;
    font-size: 10px;
    font-weight: normal;
    margin-left: 5px;
}
    .td-debit {
        color: #dc2626;
    }

    .td-credit {
        color: #059669;
    }

    .td-balance {
        color: #1f2937;
    }

    .td-balance.text-danger {
        color: #dc2626;
    }

    .td-balance.text-success {
        color: #059669;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .ledger-container {
            padding: 10px;
        }

        .party-summary {
            flex-direction: column;
            gap: 10px;
        }

        .summary-card {
            width: 100%;
        }

        .tab-navigation {
            flex-wrap: wrap;
        }

        .tab-btn {
            flex: 1;
            justify-content: center;
            padding: 8px 10px;
            font-size: 11px;
        }

        .profile-grid {
            grid-template-columns: 1fr;
        }

        .profile-card.full-width {
            grid-column: span 1;
        }

        .addresses-grid {
            grid-template-columns: 1fr;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .compact-table {
            min-width: 600px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Tab switching function
    function switchTab(tabId) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected tab
        document.getElementById(tabId).classList.add('active');

        // Add active class to corresponding button
        document.getElementById('tab-' + tabId.replace('-tab', '') + '-btn').classList.add('active');

        // Store active tab in localStorage
        localStorage.setItem('activePartyLedgerTab', tabId);
    }

    // Check for saved tab on page load
    document.addEventListener('DOMContentLoaded', function() {
        const savedTab = localStorage.getItem('activePartyLedgerTab');
        if (savedTab && document.getElementById(savedTab)) {
            switchTab(savedTab);
        }

        // Transaction type filter
        const filterSelect = document.getElementById('transaction-type-filter');
        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                const type = this.value;
                const rows = document.querySelectorAll('.transaction-row');

                rows.forEach(row => {
                    if (type === 'all' || row.dataset.type === type) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });

    // Accordion toggle function
    function toggleAccordion(header) {
        header.classList.toggle('active');
        const content = header.nextElementSibling;

        if (content.style.display === 'none' || content.style.display === '') {
            content.style.display = 'block';
        } else {
            content.style.display = 'none';
        }
    }

    // Search functionality for transactions
    document.addEventListener('keyup', function(e) {
        if (e.target.id === 'search-transactions') {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.transaction-row');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    });
</script>
@endpush
@endsection
