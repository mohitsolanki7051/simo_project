{{-- resources/views/admin/salesmen/pdf-report.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salesman Report - {{ $salesman->name }}</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Inter', Arial, sans-serif;
    font-size: 12px;
    color: #1f2937;
    background: #f3f4f6;
}

.page-wrapper {
    max-width: 900px;
    margin: 20px auto;
    background: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,0.10);
    border-radius: 8px;
    overflow: hidden;
}

/* ── ACTION BAR ── */
.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
}

.action-bar-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    color: #374151;
    font-size: 12px;
    text-decoration: none;
    cursor: pointer;
}

.page-title {
    font-size: 15px;
    font-weight: 600;
    color: #111827;
}

.action-bar-right {
    display: flex;
    gap: 8px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    border: none;
}
.btn-primary { background: #1e3a5f; color: #fff; }
.btn-outline  { background: #fff; border: 1px solid #d1d5db; color: #374151; }

/* ── REPORT HEADER ── */
.rpt-header {
    background: #1e3a5f;
    padding: 16px 24px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.rpt-company {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.3px;
}

.rpt-company-sub {
    font-size: 10px;
    color: #93b4d4;
    margin-top: 2px;
}

.rpt-right {
    text-align: right;
}

.rpt-doc-title {
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

/* ── META BAND ── */
.rpt-meta {
    background: #16304f;
    display: flex;
    border-bottom: 1px solid #2a4a6f;
}

.rpt-meta-item {
    flex: 1;
    padding: 8px 14px;
    border-right: 1px solid #2a4a6f;
}
.rpt-meta-item:last-child { border-right: none; }

.meta-label {
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #7fa8cc;
    margin-bottom: 2px;
}

.meta-value {
    font-size: 11px;
    font-weight: 600;
    color: #fff;
}

.badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
}
.badge-active   { background: #d1fae5; color: #065f46; }
.badge-inactive { background: #fee2e2; color: #991b1b; }

/* ── SECTION HEADING ── */
.sec-heading {
    font-size: 11px;
    font-weight: 700;
    color: #1e3a5f;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    border-bottom: 2px solid #1e3a5f;
    padding: 16px 24px 5px;
    margin-bottom: 0;
}

/* ── OVERVIEW CARDS ── */
.overview-row {
    display: flex;
    padding: 14px 24px;
    gap: 14px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.stat-card {
    flex: 1;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 12px 14px;
}

.stat-card-label {
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #6b7280;
    margin-bottom: 4px;
}

.stat-card-value {
    font-size: 20px;
    font-weight: 700;
    color: #1e3a5f;
    line-height: 1.1;
}

.stat-card-value.green { color: #065f46; }
.stat-card-value.blue  { color: #1d4ed8; }

.stat-card-sub {
    font-size: 10px;
    color: #9ca3af;
    margin-top: 3px;
}

/* ── PROFILE TABLE ── */
.info-section {
    padding: 0 24px 14px;
    border-bottom: 1px solid #e5e7eb;
}

.profile-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 11px;
}

.profile-table td {
    border: 1px solid #e5e7eb;
    padding: 7px 10px;
    width: 25%;
    vertical-align: top;
}

.profile-table tr:nth-child(odd) td  { background: #f9fafb; }
.profile-table tr:nth-child(even) td { background: #fff; }

.p-label {
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #9ca3af;
    margin-bottom: 3px;
}

.p-value {
    font-weight: 600;
    color: #111827;
    font-size: 11px;
}

/* ── COMMISSION RATES ── */
.rates-row {
    display: flex;
    padding: 10px 24px 14px;
    gap: 14px;
    border-bottom: 1px solid #e5e7eb;
}

.rate-box {
    flex: 1;
    background: #f0f4ff;
    border: 1px solid #c7d2fe;
    border-radius: 6px;
    padding: 10px 14px;
    text-align: center;
}

.rate-box-label {
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    margin-bottom: 4px;
}

.rate-box-value {
    font-size: 22px;
    font-weight: 700;
    color: #1e3a5f;
}

/* ── BREAKDOWN TABLE ── */
.table-section {
    padding: 0 24px 16px;
    border-bottom: 1px solid #e5e7eb;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    margin-top: 10px;
}

.data-table thead tr { background: #1e3a5f; }

.data-table thead th {
    padding: 8px 9px;
    font-size: 9.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #fff;
    text-align: left;
    border-right: 1px solid #2a4a6f;
}
.data-table thead th:last-child { border-right: none; }
.data-table thead th.r { text-align: right; }
.data-table thead th.c { text-align: center; }

.data-table tbody tr { border-bottom: 1px solid #e5e7eb; }
.data-table tbody tr:nth-child(even) { background: #f9fafb; }
.data-table tbody tr:nth-child(odd)  { background: #fff; }

.data-table tbody td {
    padding: 7px 9px;
    color: #374151;
    border-right: 1px solid #f0f0f0;
}
.data-table tbody td:last-child { border-right: none; }
.data-table tbody td.r { text-align: right; }
.data-table tbody td.c { text-align: center; }
.data-table tbody td.bold { font-weight: 600; }

.data-table tfoot tr { background: #eef2f9; border-top: 2px solid #1e3a5f; }
.data-table tfoot td {
    padding: 8px 9px;
    font-weight: 700;
    color: #1e3a5f;
    font-size: 11px;
}
.data-table tfoot td.r { text-align: right; }

/* ── TYPE BADGES ── */
.type-customer    { background: #ede9fe; color: #5b21b6; padding: 2px 7px; border-radius: 4px; font-size: 9px; font-weight: 600; }
.type-dealer      { background: #fce7f3; color: #9d174d; padding: 2px 7px; border-radius: 4px; font-size: 9px; font-weight: 600; }
.type-distributor { background: #d1fae5; color: #065f46; padding: 2px 7px; border-radius: 4px; font-size: 9px; font-weight: 600; }
.rate-tag         { background: #e8eefb; color: #1e3a5f; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; }
.comm-green       { color: #065f46; font-weight: 700; }
.inv-no           { font-family: 'Courier New', monospace; font-size: 10px; background: #f0f4ff; padding: 1px 5px; border-radius: 3px; }

/* ── SUMMARY TABLE ── */
.summary-grid {
    display: flex;
    padding: 14px 24px 16px;
    gap: 14px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.summary-box {
    flex: 1;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    overflow: hidden;
}

.summary-box-title {
    background: #1e3a5f;
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 7px 12px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 12px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 11px;
}
.summary-row:last-child { border-bottom: none; }
.s-label { color: #6b7280; }
.s-value { font-weight: 600; color: #111827; }
.s-value.green { color: #065f46; }

/* ── PAGE BREAK ── */
.page-break { page-break-before: always; }

/* ── NO DATA ── */
.no-data {
    text-align: center;
    padding: 20px;
    color: #9ca3af;
    font-size: 11px;
    border: 1px dashed #d1d5db;
    margin: 10px 0;
}

/* ── PAGE 2/3 MINI HEADER ── */
.mini-header {
    background: #1e3a5f;
    padding: 10px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.mini-header-left { font-size: 12px; font-weight: 600; color: #fff; }
.mini-header-right { font-size: 10px; color: #93b4d4; }

/* ── PRINT ── */
@media print {
    @page { margin: 0.4in; size: A4 portrait; }
    html, body { margin: 0 !important; padding: 0 !important; }
    body { background: #fff; font-size: 11px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .action-bar { display: none !important; }
    .page-wrapper { margin: 0; box-shadow: none; border-radius: 0; max-width: 100%; }
    .page-break { page-break-before: always; }
}
</style>
</head>
<body>

{{-- ACTION BAR --}}
<div class="action-bar no-print">
    <div class="action-bar-left">
        <a href="{{ url()->previous() }}" class="back-btn">&#8592; Back</a>
        <span class="page-title">Performance Report &mdash; {{ $salesman->name }}</span>
    </div>
    <div class="action-bar-right">
        <button onclick="printPDF()" class="btn btn-outline">&#128438; Print</button>
        <button onclick="downloadPDF()" class="btn btn-primary">&#8659; Download PDF</button>
    </div>
</div>

<div class="page-wrapper" id="reportToPrint">

{{-- ══════════════ PAGE 1 ══════════════ --}}

{{-- Header --}}
<div class="rpt-header">
    <div>
        <div class="rpt-company">{{ strtoupper(config('app.name', 'YOUR COMPANY')) }}</div>
        <div class="rpt-company-sub">Salesman Performance Report</div>
    </div>
    <div class="rpt-right">
        <div class="rpt-doc-title">Performance Report</div>
        <div style="font-size:10px; color:#93b4d4; margin-top:3px;">{{ now()->format('d M Y') }}</div>
    </div>
</div>

{{-- Meta Band --}}
<div class="rpt-meta">
    <div class="rpt-meta-item" style="flex:1.8;">
        <div class="meta-label">Salesman</div>
        <div class="meta-value">{{ $salesman->name }}</div>
    </div>
    <div class="rpt-meta-item">
        <div class="meta-label">Phone</div>
        <div class="meta-value">{{ $salesman->phone }}</div>
    </div>
    <div class="rpt-meta-item">
        <div class="meta-label">Joining Date</div>
        <div class="meta-value">{{ $salesman->formatted_joining_date }}</div>
    </div>
    <div class="rpt-meta-item">
        <div class="meta-label">Status</div>
        <div class="meta-value">
            <span class="badge badge-{{ $salesman->status }}">{{ ucfirst($salesman->status) }}</span>
        </div>
    </div>
    <div class="rpt-meta-item">
        <div class="meta-label">Generated</div>
        <div class="meta-value">{{ now()->format('d M Y, h:i A') }}</div>
    </div>
</div>

{{-- OVERVIEW --}}
<div class="sec-heading">Overview</div>
<div class="overview-row">
    <div class="stat-card">
        <div class="stat-card-label">Total Sales</div>
        <div class="stat-card-value">&#8377;{{ number_format($totalSales, 2) }}</div>
        <div class="stat-card-sub">{{ $sales->count() }} invoices</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">Commission Earned</div>
        <div class="stat-card-value green">&#8377;{{ number_format($commissionEarned, 2) }}</div>
        <div class="stat-card-sub">
            @php echo $salesman->commission_enabled ? ucfirst($salesman->commission_period ?? 'monthly').' basis' : 'Commission not enabled'; @endphp
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">Parties Assigned</div>
        <div class="stat-card-value blue">{{ $parties->count() }}</div>
        <div class="stat-card-sub">
            {{ $parties->where('party_type','customer')->count() }} Customer /
            {{ $parties->where('party_type','dealer')->count() }} Dealer /
            {{ $parties->where('party_type','distributor')->count() }} Distributor
        </div>
    </div>
</div>

{{-- PROFILE --}}
<div class="sec-heading">Salesman Profile</div>
<div class="info-section">
    <table class="profile-table">
        <tr>
            <td><div class="p-label">Full Name</div><div class="p-value">{{ $salesman->name }}</div></td>
            <td><div class="p-label">Phone</div><div class="p-value">{{ $salesman->phone }}</div></td>
            <td><div class="p-label">Email</div><div class="p-value">{{ $salesman->email ?? '—' }}</div></td>
            <td><div class="p-label">Joining Date</div><div class="p-value">{{ $salesman->formatted_joining_date }}</div></td>
        </tr>
        <tr>
            <td><div class="p-label">Salary Type</div><div class="p-value">{{ $salesman->salary_type_text }}</div></td>
            <td>
                <div class="p-label">Fixed Salary</div>
                <div class="p-value">@php
                    echo ($salesman->salary_type != 'commission' && $salesman->fixed_salary)
                        ? '&#8377;'.number_format($salesman->fixed_salary,2).' / '.($salesman->fixed_salary_period ?? 'month')
                        : '—';
                @endphp</div>
            </td>
            <td><div class="p-label">Commission Period</div><div class="p-value">{{ ucfirst($salesman->commission_period ?? 'Monthly') }}</div></td>
            <td>
                <div class="p-label">Status</div>
                <div class="p-value"><span class="badge badge-{{ $salesman->status }}">{{ ucfirst($salesman->status) }}</span></div>
            </td>
        </tr>
    </table>
</div>

{{-- COMMISSION RATES --}}
@if($salesman->commission_enabled)
<div class="sec-heading">Commission Rates</div>
<div class="rates-row">
    <div class="rate-box">
        <div class="rate-box-label">Customer</div>
        <div class="rate-box-value">{{ $salesman->customer_commission_percent }}%</div>
    </div>
    <div class="rate-box">
        <div class="rate-box-label">Dealer</div>
        <div class="rate-box-value">{{ $salesman->dealer_commission_percent }}%</div>
    </div>
    <div class="rate-box">
        <div class="rate-box-label">Distributor</div>
        <div class="rate-box-value">{{ $salesman->distributor_commission_percent }}%</div>
    </div>
</div>
@endif

{{-- COMMISSION BREAKDOWN --}}
@if($salesman->commission_enabled && isset($salesBreakdown))
<div class="sec-heading">Commission Breakdown</div>
<div class="table-section">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:22%;">Party Type</th>
                <th class="r" style="width:15%;">Invoices</th>
                <th class="r" style="width:25%;">Total Sales (&#8377;)</th>
                <th class="c" style="width:13%;">Rate</th>
                <th class="r" style="width:25%;">Commission (&#8377;)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="type-customer">Customer</span></td>
                <td class="r">{{ $salesBreakdown['customer']['count'] ?? 0 }}</td>
                <td class="r">{{ number_format($salesBreakdown['customer']['total'] ?? 0, 2) }}</td>
                <td class="c"><span class="rate-tag">{{ $salesman->customer_commission_percent }}%</span></td>
                <td class="r comm-green">{{ number_format($salesBreakdown['customer']['commission'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td><span class="type-dealer">Dealer</span></td>
                <td class="r">{{ $salesBreakdown['dealer']['count'] ?? 0 }}</td>
                <td class="r">{{ number_format($salesBreakdown['dealer']['total'] ?? 0, 2) }}</td>
                <td class="c"><span class="rate-tag">{{ $salesman->dealer_commission_percent }}%</span></td>
                <td class="r comm-green">{{ number_format($salesBreakdown['dealer']['commission'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td><span class="type-distributor">Distributor</span></td>
                <td class="r">{{ $salesBreakdown['distributor']['count'] ?? 0 }}</td>
                <td class="r">{{ number_format($salesBreakdown['distributor']['total'] ?? 0, 2) }}</td>
                <td class="c"><span class="rate-tag">{{ $salesman->distributor_commission_percent }}%</span></td>
                <td class="r comm-green">{{ number_format($salesBreakdown['distributor']['commission'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td><strong>TOTAL</strong></td>
                <td class="r">{{ $sales->count() }}</td>
                <td class="r">{{ number_format($totalSales, 2) }}</td>
                <td class="c">—</td>
                <td class="r comm-green">{{ number_format($commissionEarned, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- SUMMARY --}}
<div class="sec-heading">Quick Summary</div>
<div class="summary-grid">
    <div class="summary-box">
        <div class="summary-box-title">Sales Summary</div>
        <div class="summary-row"><span class="s-label">Total Invoices</span><span class="s-value">{{ $invoices->count() }}</span></div>
        <div class="summary-row"><span class="s-label">Total Sales</span><span class="s-value green">&#8377;{{ number_format($totalSales, 2) }}</span></div>
        <div class="summary-row"><span class="s-label">Average Invoice Value</span><span class="s-value">&#8377;{{ number_format($invoices->avg('grand_total') ?? 0, 2) }}</span></div>
        <div class="summary-row"><span class="s-label">Total Parties</span><span class="s-value">{{ $parties->count() }}</span></div>
    </div>
    <div class="summary-box">
        <div class="summary-box-title">Commission Summary</div>
        <div class="summary-row"><span class="s-label">Total Commission</span><span class="s-value green">&#8377;{{ number_format($commissionEarned, 2) }}</span></div>
        <div class="summary-row"><span class="s-label">Avg. Commission / Invoice</span><span class="s-value">&#8377;{{ number_format($commissionEarned / max($invoices->count(), 1), 2) }}</span></div>
        <div class="summary-row"><span class="s-label">Effective Rate</span><span class="s-value">{{ number_format(($commissionEarned / max($totalSales, 1)) * 100, 2) }}%</span></div>
        <div class="summary-row"><span class="s-label">Report Period</span><span class="s-value">@php
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                echo $filters['start_date'].' to '.$filters['end_date'];
            } elseif (!empty($filters['month']) && !empty($filters['year'])) {
                echo date('F', mktime(0,0,0,(int)$filters['month'],1)).' '.$filters['year'];
            } elseif (!empty($filters['year'])) {
                echo 'Year '.$filters['year'];
            } else { echo 'All Time'; }
        @endphp</span></div>
    </div>
</div>


{{-- ══════════════ PAGE 2 — INVOICES ══════════════ --}}
<div class="page-break"></div>
<br>
<div class="mini-header">
    <div class="mini-header-left">{{ strtoupper(config('app.name')) }} &mdash; Sales Invoices</div>
    <div class="mini-header-right">{{ $salesman->name }} &bull; {{ now()->format('d M Y') }}</div>
</div>

<div class="sec-heading">Sales Invoices &mdash; {{ $invoices->count() }} Records</div>

<div class="table-section" style="padding-top:10px;">
@if($invoices->count() > 0)
<table class="data-table">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:15%;">Invoice No.</th>
            <th style="width:11%;">Date</th>
            <th style="width:28%;">Party Name</th>
            <th class="c" style="width:11%;">Type</th>
            <th class="r" style="width:14%;">Amount (&#8377;)</th>
            <th class="c" style="width:7%;">Rate</th>
            <th class="r" style="width:10%;">Commission</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $i => $invoice)
        @php
            $pt     = $invoice->party->party_type ?? 'customer';
            $iRate  = $salesman->getCommissionRateForPartyType($pt);
            $iComm  = $invoice->grand_total * $iRate / 100;
        @endphp
        <tr>
            <td style="color:#9ca3af; font-size:10px;">{{ $i + 1 }}</td>
            <td><span class="inv-no">{{ $invoice->invoice_number }}</span></td>
            <td style="font-size:10px;">{{ $invoice->invoice_date->format('d M Y') }}</td>
            <td class="bold">{{ $invoice->party->name ?? 'N/A' }}</td>
            <td class="c"><span class="type-{{ $pt }}">{{ ucfirst($pt) }}</span></td>
            <td class="r bold">{{ number_format($invoice->grand_total, 2) }}</td>
            <td class="c"><span class="rate-tag">{{ $iRate }}%</span></td>
            <td class="r comm-green">{{ number_format($iComm, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align:right; font-weight:700;">TOTAL &mdash; {{ $invoices->count() }} Invoices</td>
            <td class="r">{{ number_format($invoices->sum('grand_total'), 2) }}</td>
            <td></td>
            <td class="r comm-green">{{ number_format($commissionEarned, 2) }}</td>
        </tr>
    </tfoot>
</table>
@else
<div class="no-data">No invoices found for the selected period.</div>
@endif
</div>


{{-- ══════════════ PAGE 3 — PARTIES ══════════════ --}}
<div class="page-break"></div>
<br>
<div class="mini-header">
    <div class="mini-header-left">{{ strtoupper(config('app.name')) }} &mdash; Assigned Parties</div>
    <div class="mini-header-right">{{ $salesman->name }} &bull; {{ now()->format('d M Y') }}</div>
</div>

<div class="sec-heading">Assigned Parties &mdash; {{ $parties->count() }} Total</div>

<div class="table-section" style="padding-top:10px;">
@if($parties->count() > 0)
<table class="data-table">
    <thead>
        <tr>
            <th style="width:5%;">#</th>
            <th style="width:30%;">Party Name</th>
            <th class="c" style="width:14%;">Type</th>
            <th style="width:16%;">Phone</th>
            <th style="width:25%;">Email</th>
            <th class="c" style="width:10%;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($parties as $i => $party)
        <tr>
            <td style="color:#9ca3af; font-size:10px;">{{ $i + 1 }}</td>
            <td class="bold">{{ $party->name }}</td>
            <td class="c"><span class="type-{{ $party->party_type }}">{{ ucfirst($party->party_type) }}</span></td>
            <td>{{ $party->phone }}</td>
            <td style="color:#6b7280; font-size:10px;">{{ $party->email ?? '—' }}</td>
            <td class="c"><span class="badge badge-{{ $party->status }}">{{ ucfirst($party->status) }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="no-data">No parties assigned to this salesman.</div>
@endif
</div>

{{-- ══════════════ PAGE 4 — PAYMENT RECORDS ══════════════ --}}
<div class="page-break"></div>
<br>
<div class="mini-header">
    <div class="mini-header-left">{{ strtoupper(config('app.name')) }} &mdash; Payment Records</div>
    <div class="mini-header-right">{{ $salesman->name }} &bull; {{ now()->format('d M Y') }}</div>
</div>

{{-- COMMISSION PAYMENTS --}}
@if(isset($commissionPayments) && $commissionPayments->count() > 0)
<div class="sec-heading">Commission Payments</div>
<div class="table-section" style="padding-top:10px;">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:4%;">#</th>
                <th style="width:14%;">Date</th>
                <th style="width:14%;">Time</th>
                <th class="r" style="width:16%;">Sales Total (&#8377;)</th>
                <th class="r" style="width:16%;">Amount Paid (&#8377;)</th>
                <th class="c" style="width:10%;">Type</th>
                <th style="width:14%;">Method</th>
                <th style="width:22%;">Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commissionPayments as $ci => $cp)
            <tr>
                <td style="color:#9ca3af;font-size:10px;">{{ $ci + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($cp->paid_at)->format('d M Y') }}</td>
                <td style="color:#9ca3af;font-size:10px;">{{ \Carbon\Carbon::parse($cp->paid_at)->format('h:i A') }}</td>
                <td class="r">{{ number_format($cp->sales_total ?? 0, 2) }}</td>
                <td class="r comm-green">{{ number_format($cp->amount, 2) }}</td>
                <td class="c">
                    @php echo !empty($cp->is_partial)
                        ? '<span style="background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:4px;font-size:9px;font-weight:600;">Partial</span>'
                        : '<span style="background:#d1fae5;color:#065f46;padding:2px 6px;border-radius:4px;font-size:9px;font-weight:600;">Full</span>';
                    @endphp
                </td>
                <td style="font-size:10px;">{{ ucwords(str_replace('_', ' ', $cp->payment_method ?? '—')) }}</td>
                <td style="color:#6b7280;font-size:10px;">{{ $cp->notes ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;font-weight:700;">Total Commission Paid</td>
                <td class="r comm-green">{{ number_format($commissionPayments->sum('amount'), 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
</div>
@else
<div class="sec-heading">Commission Payments</div>
<div class="table-section" style="padding-top:10px;">
    <div class="no-data">No commission payments found.</div>
</div>
@endif

{{-- FIXED SALARY PAYMENTS --}}
@if(isset($fixedPayments) && $fixedPayments->count() > 0)
<div class="sec-heading" style="margin-top:8px;">Fixed Salary Payments</div>
<div class="table-section" style="padding-top:10px;">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:4%;">#</th>
                <th style="width:13%;">Paid On</th>
                <th style="width:20%;">Period</th>
                <th class="r" style="width:16%;">Amount Paid (&#8377;)</th>
                <th class="r" style="width:16%;">Full Amount (&#8377;)</th>
                <th class="c" style="width:10%;">Type</th>
                <th style="width:13%;">Method</th>
                <th style="width:8%;">Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fixedPayments as $fi => $fp)
            <tr>
                <td style="color:#9ca3af;font-size:10px;">{{ $fi + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($fp->paid_at)->format('d M Y') }}</td>
                <td style="font-size:10px;">
                    {{ \Carbon\Carbon::parse($fp->period_start)->addHours(5)->addMinutes(30)->format('d M Y') }}
                    <span style="color:#9ca3af;"> to </span>
                    {{ \Carbon\Carbon::parse($fp->period_end)->addHours(5)->addMinutes(30)->format('d M Y') }}
                </td>
                <td class="r comm-green">{{ number_format($fp->amount, 2) }}</td>
                <td class="r" style="color:#374151;">
                    @php echo (!empty($fp->full_amount) && (float)$fp->full_amount > 0)
                        ? number_format($fp->full_amount, 2)
                        : number_format($fp->amount, 2);
                    @endphp
                </td>
                <td class="c">
                    @php echo !empty($fp->is_partial)
                        ? '<span style="background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:4px;font-size:9px;font-weight:600;">Partial</span>'
                        : '<span style="background:#d1fae5;color:#065f46;padding:2px 6px;border-radius:4px;font-size:9px;font-weight:600;">Full</span>';
                    @endphp
                </td>
                <td style="font-size:10px;">{{ ucwords(str_replace('_', ' ', $fp->payment_method ?? '—')) }}</td>
                <td style="color:#6b7280;font-size:10px;">{{ $fp->notes ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right;font-weight:700;">Total Fixed Salary Paid</td>
                <td class="r comm-green">{{ number_format($fixedPayments->sum('amount'), 2) }}</td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- PAYMENT SUMMARY BOX --}}
<div style="padding:14px 24px 20px;">
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:50%;padding-right:8px;vertical-align:top;">
                <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:14px 16px;">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#065f46;margin-bottom:10px;">Payment Summary</div>
                    <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #dcfce7;font-size:11px;">
                        <span style="color:#374151;">Commission Paid</span>
                        <span style="font-weight:700;color:#065f46;">&#8377;{{ number_format(isset($commissionPayments) ? $commissionPayments->sum('amount') : 0, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #dcfce7;font-size:11px;">
                        <span style="color:#374151;">Fixed Salary Paid</span>
                        <span style="font-weight:700;color:#065f46;">&#8377;{{ number_format(isset($fixedPayments) ? $fixedPayments->sum('amount') : 0, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:7px 0 0;font-size:12px;">
                        <span style="font-weight:700;color:#111827;">Total Paid (All Time)</span>
                        <span style="font-weight:700;color:#065f46;font-size:14px;">&#8377;{{ number_format((isset($commissionPayments) ? $commissionPayments->sum('amount') : 0) + (isset($fixedPayments) ? $fixedPayments->sum('amount') : 0), 2) }}</span>
                    </div>
                </div>
            </td>
            <td style="width:50%;padding-left:8px;vertical-align:top;">
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px 16px;">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#92400e;margin-bottom:10px;">Commission Status</div>
                    <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #fef3c7;font-size:11px;">
                        <span style="color:#374151;">Total Earned</span>
                        <span style="font-weight:700;color:#111827;">&#8377;{{ number_format($commissionEarned, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #fef3c7;font-size:11px;">
                        <span style="color:#374151;">Total Paid</span>
                        <span style="font-weight:700;color:#065f46;">&#8377;{{ number_format(isset($commissionPayments) ? $commissionPayments->sum('amount') : 0, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:7px 0 0;font-size:12px;">
                        <span style="font-weight:700;color:#111827;">Pending</span>
                        @php
                            $pendingComm = $commissionEarned - (isset($commissionPayments) ? $commissionPayments->sum('amount') : 0);
                        @endphp
                        <span style="font-weight:700;color:{{ $pendingComm > 0 ? '#b45309' : '#065f46' }};font-size:14px;">&#8377;{{ number_format($pendingComm, 2) }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

</div>{{-- end page-wrapper --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function printPDF() {
    const btn = event ? event.target.closest('button') : null;
    if (btn) { btn.disabled = true; btn.innerHTML = 'Preparing...'; }

    const element = document.getElementById('reportToPrint');
    html2pdf().set({
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: 'salesman-report-{{ Str::slug($salesman->name) }}.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, logging: false, removeContainer: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait', putOnlyUsedFonts: true }
    }).from(element).outputPdf('bloburl').then((url) => {
        if (btn) { btn.disabled = false; btn.innerHTML = '&#128438; Print'; }
        const win = window.open(url, '_blank');
        if (win) win.onload = () => win.print();
    });
}

function downloadPDF() {
    const btn = event ? event.target.closest('button') : null;
    if (btn) { btn.disabled = true; btn.innerHTML = 'Generating...'; }

    const element = document.getElementById('reportToPrint');
    html2pdf().set({
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: 'salesman-report-{{ Str::slug($salesman->name) }}.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, logging: false, removeContainer: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait', putOnlyUsedFonts: true }
    }).from(element).save().then(() => {
        if (btn) { btn.disabled = false; btn.innerHTML = '&#8659; Download PDF'; }
    });
}
</script>
</body>
</html>
