@extends('layouts.admin')

@section('title', ucfirst($party->party_type) . ' Ledger - Admin Panel')
@section('header-title', ucfirst($party->party_type) . ' Ledger: ' . $party->name)

@section('content')
<div class="ledger-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">{{ ucfirst($party->party_type) }} Ledger: {{ $party->name }}</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.parties.index.type', $party->party_type) }}" class="back-btn">← Back to {{ ucfirst($party->party_type) }}s</a>
        </div>
    </div>

    <!-- Party Summary -->
    <div class="party-summary">
        <div class="summary-card">
            <div class="summary-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">👤</div>
            <div class="summary-info">
                <div class="summary-label">{{ ucfirst($party->party_type) }} Name</div>
                <div class="summary-value">{{ $party->name }}</div>
                <div class="summary-detail">{{ $party->phone }} | {{ $party->email ?? 'No email' }}</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">💰</div>
            <div class="summary-info">
                <div class="summary-label">Opening Balance</div>
                <div class="summary-value">₹{{ number_format($party->opening_balance ?? 0, 2) }}</div>
                <div class="summary-detail">Credit Limit: ₹{{ number_format($party->credit_limit ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">📊</div>
            <div class="summary-info">
                <div class="summary-label">Current Balance</div>
                <div class="summary-value {{ $ledger->last() && $ledger->last()['balance'] > 0 ? 'text-danger' : ($ledger->last() && $ledger->last()['balance'] < 0 ? 'text-success' : '') }}">
                    ₹{{ number_format($ledger->last()['balance'] ?? 0, 2) }}
                </div>
                <div class="summary-detail">
                    {{ $ledger->last() && $ledger->last()['balance'] > 0 ? 'To Pay' : ($ledger->last() && $ledger->last()['balance'] < 0 ? 'To Receive' : 'Settled') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="table-wrapper">
        <table class="compact-table">
            <thead>
                <tr>
                    <th class="th-date">Date</th>
                    <th class="th-type">Type</th>
                    <th class="th-ref">Reference</th>
                    <th class="th-debit">Debit (₹)</th>
                    <th class="th-credit">Credit (₹)</th>
                    <th class="th-balance">Balance (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ledger as $entry)
                <tr>
                    <td class="td-date">{{ \Carbon\Carbon::parse($entry['date'])->format('d-m-Y') }}</td>
                    <td class="td-type">
                        <span class="type-badge type-{{ strtolower($entry['type']) }}">
                            {{ $entry['type'] }}
                        </span>
                    </td>
                    <td class="td-ref">{{ $entry['ref'] }}</td>
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
            @if($ledger->count() > 0)
            <tfoot>
                <tr>
                    <th colspan="3" class="text-right">Totals:</th>
                    <th class="td-debit">₹ {{ number_format($ledger->sum('debit'), 2) }}</th>
                    <th class="td-credit">₹ {{ number_format($ledger->sum('credit'), 2) }}</th>
                    <th class="td-balance">₹ {{ number_format($ledger->last()['balance'] ?? 0, 2) }}</th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    @if($ledger->count() > 0)
    <div class="table-footer">
        <div class="footer-info">
            Showing {{ $ledger->count() }} entries
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
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

    /* Party Summary */
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
        cursor: default;
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

    /* Table */
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

    /* Column widths */
    .th-date { width: 100px; }
    .th-type { width: 100px; }
    .th-ref { width: 150px; }
    .th-debit, .th-credit, .th-balance { width: 120px; }

    .text-right {
        text-align: right;
    }

    /* Type Badge */
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

    .type-discount {
        background: #f3e8ff;
        color: #6b21a8;
    }

    .type-charge {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Debit/Credit/Balance */
    .td-debit, .td-credit, .td-balance {
        font-weight: 600;
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

        .table-wrapper {
            overflow-x: auto;
        }

        .compact-table {
            min-width: 600px;
        }
    }
</style>
@endpush
@endsection
