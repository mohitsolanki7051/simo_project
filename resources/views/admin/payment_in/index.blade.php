@extends('layouts.admin')

@section('title', 'Payment In')
@section('header-title', 'Payment In')

@section('content')
<div class="payment-container">
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Payment In</h2>
            <span class="total-payments">Total: {{ $payments->total() }}</span>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.payment-in.create') }}" class="btn-new-payment">
                <span class="btn-icon">+</span> New Payment
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" action="{{ route('admin.payment-in.index') }}">
            <div class="filters-grid">
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Payment #...">
                </div>
                <div class="filter-group">
                    <label>Party</label>
                    <select name="party_id" class="form-control">
                        <option value="">All Parties</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->_id }}" {{ request('party_id') == $party->_id ? 'selected' : '' }}>
                                {{ $party->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="filter-group">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="filter-group filter-buttons">
                    <button type="submit" class="btn-apply">Apply</button>
                    <a href="{{ route('admin.payment-in.index') }}" class="btn-reset">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="table-card">
        <table class="payments-table">
            <thead>
                <tr>
                    <th>Payment #</th>
                    <th>Date</th>
                    <th>Party</th>
                    <th>Type</th>
                    <th>Total Amount</th>
                    <th>Method</th>
                    <th>Invoices</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>
                        <span class="payment-number">{{ $payment->payment_number }}</span>
                    </td>
                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                    <td>
                        <div class="party-name">{{ $payment->party->name ?? 'N/A' }}</div>
                        <div class="party-phone">{{ $payment->party->phone ?? '' }}</div>
                    </td>
                    <td>
                        <span class="party-badge party-{{ $payment->party_type }}">
                            {{ ucfirst($payment->party_type) }}
                        </span>
                    </td>
                    <td class="amount">₹ {{ number_format($payment->total_amount, 2) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                    <td>
                        <span class="invoice-count">{{ $payment->items->count() }} invoices</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.payment-in.show', $payment->_id) }}" class="btn-view">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty-state">
                        <div class="empty-content">
                            <div class="empty-icon">💰</div>
                            <h4>No Payments Found</h4>
                            <p>Create your first payment</p>
                            <a href="{{ route('admin.payment-in.create') }}" class="btn-new-payment">New Payment</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
    <div class="pagination-footer">
        {{ $payments->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<style>
.payment-container {
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e2e8f0;
}

.page-title {
    font-size: 18px;
    font-weight: 600;
    color: #2d3748;
    margin: 0 10px 0 0;
    display: inline-block;
}

.total-payments {
    background: #f3f4f6;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    color: #4b5563;
}

.btn-new-payment {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #f97316;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-new-payment:hover {
    background: #ea580c;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(249, 115, 22, 0.2);
}

.filters-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 20px;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.filter-group label {
    font-size: 11px;
    font-weight: 600;
    color: #4b5563;
    text-transform: uppercase;
}

.filter-group .form-control {
    padding: 6px 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 12px;
    height: 34px;
}

.filter-buttons {
    display: flex;
    gap: 8px;
}

.btn-apply, .btn-reset {
    padding: 6px 16px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    height: 34px;
    display: inline-flex;
    align-items: center;
}

.btn-apply {
    background: #3b82f6;
    color: white;
}

.btn-reset {
    background: #f3f4f6;
    color: #4b5563;
    border: 1px solid #e5e7eb;
}

.table-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow-x: auto;
}

.payments-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.payments-table th {
    background: #f8fafc;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #4b5563;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}

.payments-table td {
    padding: 12px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.payment-number {
    font-weight: 600;
    color: #1f2937;
    background: #f0f9ff;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    display: inline-block;
}

.party-name {
    font-weight: 500;
    color: #374151;
}

.party-phone {
    font-size: 10px;
    color: #6b7280;
    margin-top: 2px;
}

.party-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}

.party-customer { background: #dbeafe; color: #1e40af; }
.party-dealer { background: #fef3c7; color: #92400e; }
.party-distributor { background: #d1fae5; color: #065f46; }

.amount {
    font-weight: 600;
    color: #1f2937;
}

.invoice-count {
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    color: #4b5563;
}

.btn-view {
    padding: 4px 12px;
    background: #6b7280;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 10px;
    text-decoration: none;
    display: inline-block;
}

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

.pagination-footer {
    margin-top: 20px;
    text-align: center;
}
</style>
@endsection
