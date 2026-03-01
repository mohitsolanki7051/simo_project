@extends('layouts.admin')

@section('title', 'Payment #' . $payment->payment_number)
@section('header-title', 'Payment #' . $payment->payment_number)

@section('content')
<div class="payment-detail-container">
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Payment #{{ $payment->payment_number }}</h2>
            <span class="status-badge status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.payment-in.index') }}" class="btn-back">← Back to List</a>
            <button onclick="window.print()" class="btn-print">Print</button>
        </div>
    </div>

    <!-- Payment Details -->
    <div class="details-grid">
        <div class="detail-card">
            <h3>Payment Information</h3>
            <table class="detail-table">
                <tr>
                    <td>Payment Number:</td>
                    <td><strong>{{ $payment->payment_number }}</strong></td>
                </tr>
                <tr>
                    <td>Payment Date:</td>
                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td>Payment Method:</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                </tr>
                <tr>
                    <td>Reference No:</td>
                    <td>{{ $payment->reference_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Total Amount:</td>
                    <td><strong class="amount">₹ {{ number_format($payment->total_amount, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <div class="detail-card">
            <h3>Party Information</h3>
            <table class="detail-table">
                <tr>
                    <td>Name:</td>
                    <td><strong>{{ $payment->party->name ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <td>Party Type:</td>
                    <td>
                        <span class="party-badge party-{{ $payment->party_type }}">
                            {{ ucfirst($payment->party_type) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Phone:</td>
                    <td>{{ $payment->party->phone ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td>{{ $payment->party->email ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Notes -->
    @if($payment->notes)
    <div class="notes-card">
        <h3>Notes</h3>
        <p>{{ $payment->notes }}</p>
    </div>
    @endif

    <!-- Invoices Paid -->
    <div class="invoices-card">
        <h3>Invoices Paid</h3>
        <table class="invoices-table">
            <thead>
                <tr>
                    <th>Invoice No.</th>
                    <th>Invoice Amount</th>
                    <th>Balance Before</th>
                    <th>Paid Amount</th>
                    <th>Balance After</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment->items as $item)
                <tr>
                    <td>
                        <a href="{{ route('admin.sales.show', $item->sales_invoice_id) }}" class="invoice-link">
                            {{ $item->invoice_number }}
                        </a>
                    </td>
                    <td class="amount">₹ {{ number_format($item->invoice_amount, 2) }}</td>
                    <td class="amount">₹ {{ number_format($item->balance_before, 2) }}</td>
                    <td class="amount paid">₹ {{ number_format($item->paid_amount, 2) }}</td>
                    <td class="amount">₹ {{ number_format($item->balance_after, 2) }}</td>
                    <td>
                        @if($item->balance_after <= 0)
                            <span class="status-badge status-paid">Paid</span>
                        @else
                            <span class="status-badge status-partial">Partial</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                    <td class="amount"><strong>₹ {{ number_format($payment->total_amount, 2) }}</strong></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<style>
.payment-detail-container {
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

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.status-paid {
    background: #d1fae5;
    color: #065f46;
    font-size: 10px;
    padding: 2px 8px;
}

.status-partial {
    background: #fef3c7;
    color: #92400e;
    font-size: 10px;
    padding: 2px 8px;
}

.btn-back, .btn-print {
    padding: 6px 16px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin-left: 8px;
}

.btn-back {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
}

.btn-print {
    background: #3b82f6;
    color: white;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.detail-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
}

.detail-card h3 {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e2e8f0;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.detail-table td {
    padding: 8px 0;
}

.detail-table td:first-child {
    color: #6b7280;
    width: 120px;
}

.amount {
    font-weight: 600;
    color: #1f2937;
}

.paid {
    color: #059669;
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

.notes-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.notes-card h3 {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 10px;
}

.notes-card p {
    font-size: 12px;
    color: #4b5563;
    line-height: 1.6;
}

.invoices-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
}

.invoices-card h3 {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 15px;
}

.invoices-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.invoices-table th {
    background: #f8fafc;
    padding: 10px;
    text-align: left;
    font-weight: 600;
    color: #4b5563;
    border-bottom: 1px solid #e2e8f0;
}

.invoices-table td {
    padding: 10px;
    border-bottom: 1px solid #f3f4f6;
}

.invoices-table tfoot td {
    padding: 15px 10px;
    border-top: 2px solid #e2e8f0;
}

.invoice-link {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
}

.invoice-link:hover {
    text-decoration: underline;
}

@media print {
    .btn-back, .btn-print, .page-header .btn-print {
        display: none;
    }
}
</style>
@endsection
