@extends('layouts.print')

@section('title', 'Ledger Statement - ' . ($partyType === 'vendor' ? $party->company_name : $party->name))

@section('content')
@php
    $settings = \App\Models\InvoiceSetting::first();
@endphp

<style>
    /* PROFESSIONAL LEDGER PRINT STYLES - myBillBook Style */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 11px;
        background: white;
        padding: 20px;
        color: #1f2937;
    }

    /* Print Container */
    .ledger-print {
        max-width: 1100px;
        margin: 0 auto;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    /* Header Section */
    .ledger-header {
        padding: 20px 25px 15px;
        border-bottom: 2px solid #f97316;
        background: white;
    }

    .company-block {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .logo-img {
        height: 60px;
        width: auto;
    }

    .logo-placeholder {
        width: 60px;
        height: 60px;
        background: #f97316;
        color: white;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
    }

    .company-details h2 {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .company-details p {
        font-size: 11px;
        color: #4b5563;
        line-height: 1.4;
    }

    .company-contact {
        display: flex;
        gap: 20px;
        margin-top: 5px;
        font-size: 10px;
        color: #4b5563;
    }

    /* Title Row */
    .title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 25px;
        background: white;
    }

    .doc-title {
        font-size: 18px;
        font-weight: 700;
        color: #f97316;
        text-transform: uppercase;
    }

    /* Party Info - Simple Table Style like myBillBook */
    .party-info {
        padding: 0 25px 15px;
        background: white;
    }

    .party-info-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .party-info-table td {
        padding: 6px 0;
        vertical-align: top;
    }

    .party-info-table td:first-child {
        width: 100px;
        font-weight: 500;
        color: #6b7280;
    }

    .party-info-table td:last-child {
        font-weight: 500;
        color: #111827;
    }

    /* Summary Section - Simple Horizontal */
    .summary-section {
        background: #f9fafb;
        padding: 12px 25px;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .summary-item {
        text-align: center;
        flex: 1;
        min-width: 110px;
    }

    .summary-label {
        font-size: 9px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }

    .summary-value {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
    }

    .summary-value.dr { color: #b91c1c; }
    .summary-value.cr { color: #15803d; }

    /* Ledger Table - Clean like myBillBook */
    .ledger-table-wrapper {
        padding: 20px 25px;
        overflow-x: auto;
    }

    .ledger-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
        border: 1px solid #e5e7eb;
    }

    .ledger-table thead {
        background: #f3f4f6;
    }

    .ledger-table thead th {
        padding: 8px 8px;
        font-weight: 600;
        text-align: center;
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        font-size: 9px;
        white-space: nowrap;
        background: #f9fafb;
        color: #374151;
    }

    .ledger-table thead th:last-child {
        border-right: none;
    }

    .ledger-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
    }

    .ledger-table tbody td {
        padding: 7px 8px;
        vertical-align: top;
        border-right: 1px solid #f3f4f6;
        text-align: center;
    }

    .ledger-table tbody td:last-child {
        border-right: none;
    }

    .ledger-table tbody tr.opening-row {
        background: #fffdf5;
        font-style: italic;
    }

    .ledger-table tbody tr.closing-row {
        background: #f9fafb;
        font-weight: 600;
        border-top: 2px solid #e5e7eb;
    }

    /* Balance Colors */
    .balance-dr { color: #b91c1c; font-weight: 600; }
    .balance-cr { color: #15803d; font-weight: 600; }
    .amount-dr { color: #b91c1c; font-weight: 600; }
    .amount-cr { color: #15803d; font-weight: 600; }
    .text-muted { color: #9ca3af; }

    .due-status {
        display: inline-block;
        font-size: 8px;
        font-weight: 600;
        padding: 1px 5px;
        border-radius: 3px;
        margin-top: 2px;
        white-space: nowrap;
    }
    .due-status.paid     { background: #f0fdf4; color: #15803d; }
    .due-status.unpaid   { background: #fef2f2; color: #b91c1c; }
    .due-status.partial  { background: #fffbeb; color: #92400e; }

    /* Footer */
    .ledger-footer {
        padding: 12px 25px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        font-size: 9px;
        color: #6b7280;
    }

    /* Utility */
    .ta-r { text-align: center; }
    .ta-c { text-align: center; }
    .fw6 { font-weight: 600; }
    .mono { font-family: 'SF Mono', monospace; font-size: 9px; }

    /* Print Styles */
    @media print {
        body {
            padding: 0;
            margin: 0;
        }

        .ledger-print {
            border: none;
            margin: 0;
            max-width: 100%;
        }

        .ledger-table thead th {
            background: #f3f4f6 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4;
            margin: 0.5cm;
        }
    }
</style>

<div class="ledger-print">
    <!-- Header -->
    <div class="ledger-header">
        <div class="company-block">
            @if($settings && $settings->logo_path)
                <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="logo-img">
            @else
                <div class="logo-placeholder">
                    {{ strtoupper(substr($settings->company_name ?? 'SIM', 0, 2)) }}
                </div>
            @endif
            @php
                $companyName = $settings->company_name ?? 'SIMKO ENTERPRISES';
                $companyAddress = $settings->company_address ?? 'Company Address';
                $companyPhone = $settings->company_phone ?? 'N/A';
                $companyEmail = $settings->company_email ?? 'N/A';
                $companyGstin = $settings->gstin ?? null;
            @endphp
            <div class="company-details">
                <h2>{{ $companyName }}</h2>
                <p>{{ $companyAddress }}</p>
                <div class="company-contact">
                    <span>Phone: {{ $companyPhone }}</span>
                    <span>Email: {{ $companyEmail }}</span>
                    @if($companyGstin)
                    <span>GSTIN: {{ $companyGstin }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Title -->
    <div class="title-row">
        <div class="doc-title">
            LEDGER STATEMENT
            @if(($invoiceType ?? null) === 'gst')
                <span style="font-size:11px;font-weight:600;color:#1d4ed8;background:#eff6ff;padding:2px 8px;border-radius:4px;margin-left:8px;vertical-align:middle;">GST INVOICE ONLY</span>
            @elseif(($invoiceType ?? null) === 'cash')
                <span style="font-size:11px;font-weight:600;color:#c2410c;background:#fff7ed;padding:2px 8px;border-radius:4px;margin-left:8px;vertical-align:middle;">CASH MEMO ONLY</span>
            @endif
        </div>
        <div class="date-range">
            <div>{{ $fromDate ?? 'Opening' }} to {{ $toDate ?? now()->format('d-m-Y') }}</div>
        </div>
    </div>

    <!-- Party Info - Simple Table like myBillBook -->
    <div class="party-info">
        <table class="party-info-table">
            <tr>
                <td>To</td>
                <td><strong>{{ $partyType === 'vendor' ? $party->company_name : $party->name }}</strong></td>
            </tr>
            @if($party->phone)
            <tr>
                <td>Phone</td>
                <td>{{ $party->phone }}</td>
            </tr>
            @endif
            @if($party->gst_number)
            <tr>
                <td>GST No.</td>
                <td>{{ $party->gst_number }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Summary - Simple Horizontal -->
    <div class="summary-section">
        <div class="summary-item">
            <div class="summary-label">Opening Balance</div>
            <div class="summary-value {{ $summary['opening_balance'] >= 0 ? 'dr' : 'cr' }}">
                ₹ {{ number_format(abs($summary['opening_balance']), 2) }}
                <span style="font-size: 9px;">{{ $summary['opening_balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Sales</div>
            <div class="summary-value">₹ {{ number_format($summary['total_sales'], 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Received</div>
            <div class="summary-value">₹ {{ number_format($summary['total_received'], 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Overdue Amount</div>
            <div class="summary-value {{ $summary['overdue_amount'] > 0 ? 'dr' : '' }}">₹ {{ number_format($summary['overdue_amount'], 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Closing Balance</div>
            <div class="summary-value {{ $summary['closing_balance'] >= 0 ? 'dr' : 'cr' }}">
                ₹ {{ number_format(abs($summary['closing_balance']), 2) }}
                <span style="font-size: 9px;">{{ $summary['closing_balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="ledger-table-wrapper">
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width: 75px">Date</th>
                    <th style="width: 110px">Voucher Type</th>
                    <th style="width: 100px">Sr No.</th>
                    <th style="width: 110px">Payment Mode</th>
                    <th style="width: 90px" class="ta-r">Debit (₹)</th>
                    <th style="width: 90px" class="ta-r">Credit (₹)</th>
                    <th style="width: 100px" class="ta-r">Balance (₹)</th>
                    <th style="width: 110px">Due Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ledger as $entry)
                <tr class="@if($entry['is_opening'] ?? false) opening-row @elseif($entry['is_closing'] ?? false) closing-row @endif">
                    <td class="{{ $entry['is_opening'] ? 'text-muted' : '' }}">
                        {{ $entry['is_opening'] ? ($entry['date'] ?? 'Opening') : ($entry['is_closing'] ? '' : $entry['date']) }}
                    </td>
                    <td>
                        <strong>{{ $entry['voucher_type'] }}</strong>
                    </td>
                    <td class="mono">{{ $entry['sr_no'] }}</td>
                    <td>
                        @if(($entry['payment_mode'] ?? '—') !== '—')
                            {{ $entry['payment_mode'] }}
                            @if(!empty($entry['reference_number']))
                                <br><span class="text-muted">({{ $entry['reference_number'] }})</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="ta-r">
                        @if($entry['debit'] > 0)
                            <span class="amount-dr">₹ {{ number_format($entry['debit'], 2) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="ta-r">
                        @if($entry['credit'] > 0)
                            <span class="amount-cr">₹ {{ number_format($entry['credit'], 2) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="ta-r fw6">
                        @if($entry['balance'] > 0)
                            <span class="balance-dr">₹ {{ number_format($entry['balance'], 2) }} Dr</span>
                        @elseif($entry['balance'] < 0)
                            <span class="balance-cr">₹ {{ number_format(abs($entry['balance']), 2) }} Cr</span>
                        @else
                            <span class="text-muted">₹ 0.00</span>
                        @endif
                    </td>
                    <td>
                        @if($entry['due_date'])
                            {{ $entry['due_date'] }}
                            @if($entry['due_status'])
                                <br>
                                <span class="due-status {{ Str::startsWith($entry['due_status'], 'Paid') ? 'paid' : (Str::startsWith($entry['due_status'], 'Partially') ? 'partial' : 'unpaid') }}">
                                    {{ $entry['due_status'] }}
                                </span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot style="background: #f9fafb; font-weight: 600; border-top: 1px solid #e5e7eb;">
                <tr>
                    <td colspan="4" class="fw6">Total</td>
                    <td class="ta-r amount-dr">₹ {{ number_format($ledger->where('is_opening', false)->where('is_closing', false)->sum('debit'), 2) }}</td>
                    <td class="ta-r amount-cr">₹ {{ number_format($ledger->where('is_opening', false)->where('is_closing', false)->sum('credit'), 2) }}</td>
                    <td class="ta-r fw6">
                        @if($summary['closing_balance'] > 0)
                            <span class="balance-dr">₹ {{ number_format($summary['closing_balance'], 2) }} Dr</span>
                        @elseif($summary['closing_balance'] < 0)
                            <span class="balance-cr">₹ {{ number_format(abs($summary['closing_balance']), 2) }} Cr</span>
                        @else
                            <span>₹ 0.00</span>
                        @endif
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Footer -->
    <div class="ledger-footer">
        <div>{{ $settings->footer_note ?? 'This is a computer generated statement - no signature required' }}</div>
        <div>Generated on: {{ now()->format('d/m/Y h:i A') }}</div>
    </div>
</div>
@endsection