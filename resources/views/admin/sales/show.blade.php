@extends('layouts.admin')

@section('title', 'Invoice #' . $invoice->invoice_number . ' - Admin Panel')
@section('header-title', 'Sales Invoice #' . $invoice->invoice_number)

@section('content')

@php
    $settings = \App\Models\InvoiceSetting::first();
    $party = $invoice->party;
    $partyType = $party->party_type ?? 'customer';

    // Determine price column label based on party type
    $priceLabel = match($partyType) {
        'dealer'       => 'Dealer Price',
        'distributor'  => 'Distributor Price',
        default        => 'Sale Price',
    };

    $statusColors = [
        'paid'    => ['bg' => '#d1fae5', 'text' => '#065f46', 'border' => '#6ee7b7'],
        'unpaid'  => ['bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#fca5a5'],
        'partial' => ['bg' => '#fef3c7', 'text' => '#92400e', 'border' => '#fcd34d'],
    ];
    $ps = $statusColors[$invoice->payment_status] ?? $statusColors['unpaid'];

    $invStatusColors = [
        'draft'     => ['bg' => '#f3f4f6', 'text' => '#374151'],
        'confirmed' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
        'completed' => ['bg' => '#d1fae5', 'text' => '#065f46'],
        'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
    ];
    $is = $invStatusColors[$invoice->status] ?? $invStatusColors['confirmed'];
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap');

    :root {
        --brand:       #1a1a2e;
        --brand-mid:   #16213e;
        --accent:      #f97316;
        --accent-soft: #fff7ed;
        --surface:     #ffffff;
        --border:      #e5e7eb;
        --border-dark: #d1d5db;
        --text-primary: #111827;
        --text-secondary: #6b7280;
        --text-muted:   #9ca3af;
        --bg-page:     #f8fafc;
        --shadow-sm:   0 1px 3px rgba(0,0,0,0.08);
        --shadow-md:   0 4px 16px rgba(0,0,0,0.10);
        --shadow-lg:   0 12px 40px rgba(0,0,0,0.12);
        --radius:      10px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    .iv-wrap {
        font-family: 'Outfit', sans-serif;
        color: var(--text-primary);
        background: var(--bg-page);
        min-height: 100vh;
        padding: 20px;
    }

    /* ── Page Actions Bar ── */
    .iv-actions-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        gap: 12px;
        flex-wrap: wrap;
    }

    .iv-actions-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .iv-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: var(--surface);
        border: 1px solid var(--border-dark);
        border-radius: 7px;
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary);
        text-decoration: none;
        transition: all .2s;
    }
    .iv-back-btn:hover { background: var(--brand); color: #fff; border-color: var(--brand); }

    .iv-page-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .iv-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .4px;
        text-transform: uppercase;
        border: 1px solid;
    }

    .iv-actions-right {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .iv-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        border: none;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
        font-family: 'Outfit', sans-serif;
        text-decoration: none;
    }
    .iv-btn-primary   { background: var(--brand); color: #fff; }
    .iv-btn-primary:hover { background: #0f172a; transform: translateY(-1px); box-shadow: var(--shadow-md); }
    .iv-btn-accent    { background: var(--accent); color: #fff; }
    .iv-btn-accent:hover { background: #ea6c0a; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(249,115,22,.35); }
    .iv-btn-outline   { background: var(--surface); color: var(--text-primary); border: 1px solid var(--border-dark); }
    .iv-btn-outline:hover { background: #f9fafb; }

    /* ── Main Card ── */
    .iv-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    /* ── Invoice Document ── */
    .iv-doc {
        padding: 0;
    }

    /* ── Top color bar ── */
    .iv-topbar {
        height: 6px;
        background: linear-gradient(90deg, var(--brand) 0%, var(--accent) 100%);
    }

    /* ── Header ── */
    .iv-header {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 30px;
        padding: 30px 36px 24px;
        border-bottom: 1px solid var(--border);
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .iv-header::before {
        content: '';
        position: absolute;
        right: -60px; top: -60px;
        width: 240px; height: 240px;
        border-radius: 50%;
        background: rgba(249,115,22,.12);
        pointer-events: none;
    }
    .iv-header::after {
        content: '';
        position: absolute;
        right: 60px; bottom: -80px;
        width: 180px; height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,.04);
        pointer-events: none;
    }

    .iv-company-block {}
    .iv-logo-row {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 12px;
    }
    .iv-logo-img {
        height: 52px;
        width: auto;
        border-radius: 8px;
        background: rgba(255,255,255,.1);
        padding: 4px;
    }
    .iv-logo-placeholder {
        width: 52px; height: 52px;
        border-radius: 8px;
        background: rgba(249,115,22,.25);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 800; color: var(--accent);
        border: 1px solid rgba(249,115,22,.4);
    }
    .iv-company-name {
        font-size: 20px;
        font-weight: 800;
        color: #fff;
        letter-spacing: -.3px;
        line-height: 1.1;
    }
    .iv-company-tagline {
        font-size: 10px;
        color: rgba(255,255,255,.5);
        margin-top: 2px;
        letter-spacing: .8px;
        text-transform: uppercase;
    }
    .iv-company-details {
        font-size: 11px;
        color: rgba(255,255,255,.7);
        line-height: 1.7;
    }
    .iv-company-details span {
        display: inline-block;
        margin-right: 16px;
    }
    .iv-company-tax {
        display: flex;
        gap: 20px;
        margin-top: 8px;
        flex-wrap: wrap;
    }
    .iv-tax-chip {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 5px;
        padding: 3px 10px;
        font-size: 10px;
        font-family: 'JetBrains Mono', monospace;
        color: rgba(255,255,255,.85);
        letter-spacing: .5px;
    }

    /* Right meta block */
    .iv-meta-block {
        text-align: right;
        position: relative;
        z-index: 1;
    }
    .iv-doc-type {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 8px;
    }
    .iv-invoice-num {
        font-family: 'JetBrains Mono', monospace;
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 16px;
        letter-spacing: -.5px;
    }
    .iv-meta-rows {
        display: flex;
        flex-direction: column;
        gap: 7px;
        align-items: flex-end;
    }
    .iv-meta-row {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 11px;
    }
    .iv-meta-label {
        color: rgba(255,255,255,.5);
        font-weight: 500;
        min-width: 80px;
        text-align: right;
    }
    .iv-meta-value {
        color: #fff;
        font-weight: 600;
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        background: rgba(255,255,255,.1);
        padding: 3px 8px;
        border-radius: 4px;
        min-width: 100px;
        text-align: center;
    }
    .iv-meta-value.overdue { background: rgba(239,68,68,.3); color: #fca5a5; }

    /* ── Party & Details Grid ── */
    .iv-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0;
        border-bottom: 1px solid var(--border);
    }
    .iv-info-col {
        padding: 22px 28px;
        border-right: 1px solid var(--border);
    }
    .iv-info-col:last-child { border-right: none; }
    .iv-info-col-title {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .iv-info-col-title::before {
        content: '';
        display: inline-block;
        width: 12px; height: 2px;
        background: var(--accent);
        border-radius: 2px;
    }
    .iv-party-name {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    .iv-party-type-badge {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .badge-customer    { background: #dbeafe; color: #1d4ed8; }
    .badge-dealer      { background: #d1fae5; color: #065f46; }
    .badge-distributor { background: #fef3c7; color: #92400e; }

    .iv-party-detail-row {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        font-size: 11px;
        color: var(--text-secondary);
        margin-bottom: 4px;
        line-height: 1.4;
    }
    .iv-party-detail-icon { font-size: 11px; flex-shrink: 0; margin-top: 1px; }

    .iv-detail-pair {
        margin-bottom: 9px;
    }
    .iv-detail-key {
        font-size: 10px;
        color: var(--text-muted);
        font-weight: 500;
        margin-bottom: 2px;
    }
    .iv-detail-val {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
        font-family: 'JetBrains Mono', monospace;
    }
    .iv-detail-val.normal { font-family: 'Outfit', sans-serif; font-weight: 500; }

    /* ── Items Table ── */
    .iv-items-section {
        padding: 0 0 0 0;
    }
    .iv-items-header {
        padding: 14px 28px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .iv-items-title {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: .3px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .iv-items-count {
        background: var(--brand);
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 20px;
    }
    .iv-tax-type-badge {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 5px;
        letter-spacing: .3px;
    }
    .tax-intra { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .tax-inter { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }

    .iv-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }
    .iv-table thead tr {
        background: var(--brand);
    }
    .iv-table thead th {
        padding: 11px 14px;
        color: rgba(255,255,255,.85);
        font-weight: 600;
        font-size: 10px;
        letter-spacing: .5px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .iv-table thead th:first-child { padding-left: 28px; }
    .iv-table thead th:last-child  { padding-right: 28px; }
    .iv-table thead th.num { text-align: right; }
    .iv-table thead th.center { text-align: center; }

    .iv-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s;
    }
    .iv-table tbody tr:hover { background: #f8fafc; }
    .iv-table tbody tr:last-child { border-bottom: none; }

    .iv-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        color: var(--text-primary);
    }
    .iv-table tbody td:first-child { padding-left: 28px; }
    .iv-table tbody td:last-child  { padding-right: 28px; }
    .iv-table tbody td.num { text-align: right; font-family: 'JetBrains Mono', monospace; font-size: 11px; }
    .iv-table tbody td.center { text-align: center; }

    .sno-cell {
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        color: var(--text-muted);
        font-weight: 600;
        width: 36px;
    }
    .product-name-cell strong {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
    }
    .product-name-cell .variant-name {
        font-size: 10px;
        color: var(--text-secondary);
        margin-top: 2px;
    }
    .product-name-cell .sku-badge {
        display: inline-block;
        background: #f3f4f6;
        color: var(--text-muted);
        font-size: 9px;
        font-family: 'JetBrains Mono', monospace;
        padding: 1px 6px;
        border-radius: 3px;
        margin-top: 3px;
        border: 1px solid #e5e7eb;
    }

    .warranty-badge {
        display: inline-block;
        background: #ecfdf5;
        color: #065f46;
        font-size: 9px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 4px;
        border: 1px solid #a7f3d0;
        white-space: nowrap;
    }
    .warranty-none {
        color: var(--text-muted);
        font-size: 10px;
    }

    .discount-badge {
        display: inline-flex;
        align-items: center;
        background: #fff7ed;
        color: #c2410c;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 4px;
        border: 1px solid #fdba74;
    }

    .mrp-val {
        text-decoration: line-through;
        color: var(--text-muted);
        font-size: 10px;
    }
    .price-val {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .tax-breakdown {
        font-size: 9px;
        color: var(--text-muted);
        line-height: 1.6;
    }
    .total-cell {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-primary);
    }

    /* Table footer (subtotals row) */
    .iv-table tfoot tr {
        background: #f8fafc;
        border-top: 2px solid var(--border-dark);
    }
    .iv-table tfoot td {
        padding: 12px 14px;
        font-weight: 600;
        font-size: 11px;
        font-family: 'JetBrains Mono', monospace;
    }
    .iv-table tfoot td:first-child { padding-left: 28px; }
    .iv-table tfoot td:last-child  { padding-right: 28px; }
    .iv-table tfoot td.num { text-align: right; }
    .iv-table tfoot td.label-cell { font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: .5px; }

    /* ── Bottom Section ── */
    .iv-bottom {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 0;
        border-top: 1px solid var(--border);
    }

    /* Notes & Terms */
    .iv-notes-panel {
        padding: 24px 28px;
        border-right: 1px solid var(--border);
    }
    .iv-panel-title {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .iv-panel-title::before {
        content: '';
        display: inline-block;
        width: 12px; height: 2px;
        background: var(--accent);
        border-radius: 2px;
    }
    .iv-notes-text {
        font-size: 11px;
        color: var(--text-secondary);
        line-height: 1.6;
    }
    .iv-terms-text {
        font-size: 10px;
        color: var(--text-muted);
        line-height: 1.7;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed var(--border);
    }
    .iv-bank-block {
        margin-top: 16px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 7px;
    }
    .iv-bank-row {
        display: flex;
        gap: 6px;
        font-size: 10px;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }
    .iv-bank-key { color: var(--text-muted); min-width: 90px; }
    .iv-bank-val { font-weight: 600; color: var(--text-primary); font-family: 'JetBrains Mono', monospace; }

    /* Totals Panel */
    .iv-totals-panel {
        padding: 24px 28px;
        background: #f8fafc;
    }

    .iv-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 7px 0;
        font-size: 11px;
        border-bottom: 1px solid #f0f0f0;
    }
    .iv-total-row:last-child { border-bottom: none; }
    .iv-total-label { color: var(--text-secondary); font-weight: 500; }
    .iv-total-value {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 12px;
    }
    .iv-total-row.discount .iv-total-value { color: #16a34a; }
    .iv-total-row.tax .iv-total-value { color: #6d28d9; }
    .iv-total-row.grand {
        background: var(--brand);
        margin: 12px -28px -24px;
        padding: 16px 28px;
        border-radius: 0 0 0 0;
        border-bottom: none;
    }
    .iv-total-row.grand .iv-total-label { color: rgba(255,255,255,.75); font-weight: 600; font-size: 12px; }
    .iv-total-row.grand .iv-total-value { color: #fff; font-size: 18px; }

    .iv-total-row.paid .iv-total-value { color: #059669; }
    .iv-total-row.balance .iv-total-label { font-weight: 700; color: var(--text-primary); }
    .iv-total-row.balance .iv-total-value { font-size: 14px; color: #dc2626; }
    .iv-total-row.balance-zero .iv-total-value { color: #16a34a; }

    .iv-divider-line {
        height: 1px;
        background: var(--border-dark);
        margin: 6px 0;
    }

    /* ── Payment History ── */
    .iv-payments-section {
        padding: 20px 28px;
        border-top: 1px solid var(--border);
        background: #fafbfc;
    }
    .iv-pay-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        margin-top: 10px;
    }
    .iv-pay-table th {
        padding: 8px 10px;
        background: #f3f4f6;
        font-size: 10px;
        font-weight: 700;
        color: var(--text-muted);
        letter-spacing: .5px;
        text-transform: uppercase;
        text-align: left;
        border-bottom: 1px solid var(--border-dark);
    }
    .iv-pay-table td {
        padding: 9px 10px;
        border-bottom: 1px solid #f1f5f9;
        color: var(--text-primary);
        vertical-align: middle;
    }
    .iv-pay-table tr:last-child td { border-bottom: none; }
    .pay-method-badge {
        display: inline-block;
        background: #f3f4f6;
        color: var(--text-secondary);
        font-size: 9px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    /* ── Footer bar ── */
    .iv-footer-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 28px;
        background: #f8fafc;
        border-top: 1px solid var(--border);
        flex-wrap: wrap;
        gap: 10px;
    }
    .iv-footer-note {
        font-size: 10px;
        color: var(--text-muted);
        font-style: italic;
    }
    .iv-signature-block {
        display: flex;
        gap: 30px;
        align-items: flex-end;
    }
    .iv-sig-box {
        text-align: center;
    }
    .iv-sig-img { height: 45px; }
    .iv-sig-line {
        border-top: 1px solid var(--border-dark);
        margin-top: 8px;
        padding-top: 4px;
        font-size: 9px;
        color: var(--text-muted);
        font-weight: 600;
        letter-spacing: .5px;
        text-transform: uppercase;
    }



    /* ── Alert ── */
    #iv-alert-container {
        position: fixed;
        top: 20px; right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .iv-alert {
        padding: 12px 18px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 500;
        box-shadow: var(--shadow-md);
        animation: slideIn .3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .iv-alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
    .iv-alert-error   { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
    .iv-alert-info    { background: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; }
    @keyframes slideIn {
        from { transform: translateX(60px); opacity: 0; }
        to   { transform: translateX(0);    opacity: 1; }
    }

    /* ── Print Styles ── */
    @media print {
        .iv-actions-bar, .iv-add-payment-card, #iv-alert-container,
        .no-print { display: none !important; }

        .iv-wrap { background: #fff; padding: 0; }
        .iv-card { border: none; box-shadow: none; border-radius: 0; }
        .iv-topbar { height: 4px; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        .iv-header { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        .iv-table thead { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        .iv-total-row.grand { print-color-adjust: exact; -webkit-print-color-adjust: exact; }

        .iv-bottom { grid-template-columns: 1fr 300px; }

        body { font-size: 10px; }
    }
</style>

<div class="iv-wrap">
    <div id="iv-alert-container"></div>

    <!-- ── Actions Bar ── -->
    <div class="iv-actions-bar no-print">
        <div class="iv-actions-left">
            <a href="{{ route('admin.sales.index') }}" class="iv-back-btn">← Back to Invoices</a>
            <h2 class="iv-page-title">Invoice #{{ $invoice->invoice_number }}</h2>
            <span class="iv-status-pill"
                style="background: {{ $ps['bg'] }}; color: {{ $ps['text'] }}; border-color: {{ $ps['border'] }};">
                @if($invoice->payment_status === 'paid') ✓
                @elseif($invoice->payment_status === 'partial') ◑
                @else ◌
                @endif
                {{ ucfirst($invoice->payment_status) }}
            </span>
            <span class="iv-status-pill"
                style="background: {{ $is['bg'] }}; color: {{ $is['text'] }}; border-color: {{ $is['bg'] }};">
                {{ ucfirst($invoice->status) }}
            </span>
        </div>
        <div class="iv-actions-right">
            <button onclick="printInvoice()" class="iv-btn iv-btn-outline">🖨 Print</button>
            <button onclick="downloadPDF()" class="iv-btn iv-btn-outline">📥 Download PDF</button>

        </div>
    </div>

    <!-- ── Main Invoice Card ── -->
    <div class="iv-card" id="invoiceToPrint">

        <!-- Color accent bar -->
        <div class="iv-topbar"></div>

        <!-- ── HEADER ── -->
        <div class="iv-header">
            <div class="iv-company-block">
                <div class="iv-logo-row">
                    @if($settings && $settings->logo_path)
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="iv-logo-img">
                    @else
                        <div class="iv-logo-placeholder">
                            {{ strtoupper(substr($settings->company_name ?? 'C', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="iv-company-name">{{ $settings->company_name ?? 'Your Company Name' }}</div>
                        <div class="iv-company-tagline">Enterprise Solutions</div>
                    </div>
                </div>
                <div class="iv-company-details">
                    @if($settings && $settings->company_address)
                        <span>📍 {{ $settings->company_address }}</span>
                    @endif
                    @if($settings && $settings->company_phone)
                        <span>📞 {{ $settings->company_phone }}</span>
                    @endif
                    @if($settings && $settings->company_email)
                        <span>✉ {{ $settings->company_email }}</span>
                    @endif
                </div>
                <div class="iv-company-tax">
                    @if($settings && $settings->gstin)
                        <div class="iv-tax-chip">GSTIN: {{ $settings->gstin }}</div>
                    @endif
                    @if($settings && $settings->pan)
                        <div class="iv-tax-chip">PAN: {{ $settings->pan }}</div>
                    @endif
                </div>
            </div>

            <div class="iv-meta-block">
                <div class="iv-doc-type">Tax Invoice</div>
                <div class="iv-invoice-num">#{{ $invoice->invoice_number }}</div>
                <div class="iv-meta-rows">
                    <div class="iv-meta-row">
                        <span class="iv-meta-label">Invoice Date</span>
                        <span class="iv-meta-value">{{ $invoice->invoice_date->format('d M Y') }}</span>
                    </div>
                    @if($invoice->due_date)
                    <div class="iv-meta-row">
                        <span class="iv-meta-label">Due Date</span>
                        <span class="iv-meta-value {{ $invoice->due_date->isPast() && $invoice->payment_status !== 'paid' ? 'overdue' : '' }}">
                            {{ $invoice->due_date->format('d M Y') }}
                        </span>
                    </div>
                    @endif
                    @if($invoice->po_number)
                    <div class="iv-meta-row">
                        <span class="iv-meta-label">PO Number</span>
                        <span class="iv-meta-value">{{ $invoice->po_number }}</span>
                    </div>
                    @endif
                    @if($invoice->payment_terms)
                    <div class="iv-meta-row">
                        <span class="iv-meta-label">Terms</span>
                        <span class="iv-meta-value">{{ $invoice->payment_terms }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ── BILL TO / SHIP TO / OTHER DETAILS ── -->
        <div class="iv-info-grid">
            <!-- Bill To -->
            <div class="iv-info-col">
                <div class="iv-info-col-title">Bill To</div>
                <div class="iv-party-name">{{ $party->name ?? 'N/A' }}</div>
                <div class="iv-party-type-badge badge-{{ $partyType }}">{{ ucfirst($partyType) }}</div>

                @if($invoice->billing_address)
                <div class="iv-party-detail-row">
                    <span class="iv-party-detail-icon">📍</span>
                    <span>{{ $invoice->billing_address }}</span>
                </div>
                @endif
                @if($party && $party->phone)
                <div class="iv-party-detail-row">
                    <span class="iv-party-detail-icon">📞</span>
                    <span>{{ $party->phone }}</span>
                </div>
                @endif
                @if($party && $party->email)
                <div class="iv-party-detail-row">
                    <span class="iv-party-detail-icon">✉</span>
                    <span>{{ $party->email }}</span>
                </div>
                @endif
                @if($party && $party->gst_number)
                <div class="iv-party-detail-row">
                    <span class="iv-party-detail-icon">🏷</span>
                    <span>GSTIN: <strong>{{ $party->gst_number }}</strong></span>
                </div>
                @endif
            </div>

            <!-- Ship To -->
            <div class="iv-info-col">
                <div class="iv-info-col-title">Ship To</div>
                @if($invoice->shipping_address && $invoice->shipping_address !== $invoice->billing_address)
                    <div class="iv-party-detail-row" style="margin-bottom: 6px;">
                        <span class="iv-party-detail-icon">📍</span>
                        <span>{{ $invoice->shipping_address }}</span>
                    </div>
                @else
                    <div class="iv-party-detail-row" style="color: var(--text-muted);">
                        <span>Same as billing address</span>
                    </div>
                    @if($invoice->billing_address)
                    <div class="iv-party-detail-row" style="margin-top: 4px;">
                        <span class="iv-party-detail-icon">📍</span>
                        <span>{{ $invoice->billing_address }}</span>
                    </div>
                    @endif
                @endif

                <!-- Warehouse -->
                @if($invoice->warehouse)
                <div style="margin-top: 12px;">
                    <div class="iv-detail-key">Dispatched From</div>
                    <div class="iv-detail-val normal">
                        🏭 {{ $invoice->warehouse->name ?? 'Main Warehouse' }}
                    </div>
                </div>
                @endif
            </div>

            <!-- Other Details -->
            <div class="iv-info-col">
                <div class="iv-info-col-title">Invoice Details</div>

                <div class="iv-detail-pair">
                    <div class="iv-detail-key">Payment Status</div>
                    <div>
                        <span class="iv-status-pill"
                            style="background: {{ $ps['bg'] }}; color: {{ $ps['text'] }}; border-color: {{ $ps['border'] }}; font-size: 10px;">
                            {{ ucfirst($invoice->payment_status) }}
                        </span>
                    </div>
                </div>

                @if($invoice->total_paid > 0)
                <div class="iv-detail-pair">
                    <div class="iv-detail-key">Amount Paid</div>
                    <div class="iv-detail-val">₹ {{ number_format($invoice->total_paid, 2) }}</div>
                </div>
                @endif

                @if($invoice->balance_amount > 0)
                <div class="iv-detail-pair">
                    <div class="iv-detail-key">Balance Due</div>
                    <div class="iv-detail-val" style="color: #dc2626;">₹ {{ number_format($invoice->balance_amount, 2) }}</div>
                </div>
                @endif

                @if($invoice->due_date)
                <div class="iv-detail-pair">
                    <div class="iv-detail-key">Due In</div>
                    <div class="iv-detail-val normal"
                        style="{{ $invoice->due_date->isPast() && $invoice->payment_status !== 'paid' ? 'color:#dc2626' : 'color:#059669' }}">
                        {{ $invoice->due_in_days }}
                    </div>
                </div>
                @endif

                <div class="iv-detail-pair">
                    <div class="iv-detail-key">Tax Treatment</div>
                    <div style="margin-top: 3px;">
                        @if($invoice->tax_type === 'intra')
                            <span class="iv-tax-type-badge tax-intra">⚡ Intra-State (CGST + SGST)</span>
                        @else
                            <span class="iv-tax-type-badge tax-inter">⚡ Inter-State (IGST)</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- ── ITEMS TABLE ── -->
        <div class="iv-items-section">
            <div class="iv-items-header">
                <div class="iv-items-title">
                    Items
                    <span class="iv-items-count">{{ $invoice->items->count() }}</span>
                </div>
                <div style="font-size:11px; color: var(--text-muted);">
                    Price column: <strong style="color: var(--text-primary);">{{ $priceLabel }}</strong>
                </div>
            </div>

            <table class="iv-table">
                <thead>
                    <tr>
                        <th style="width:36px;">#</th>
                        <th>Product / Description</th>
                        <th class="center">HSN/SAC</th>
                        <th class="center">Qty & Unit</th>
                        <th class="center">Warranty</th>
                        <th class="num">MRP (₹)</th>
                        <th class="num">Disc %</th>
                        <th class="num">{{ $priceLabel }} (₹)</th>
                        <th class="num">Tax</th>
                        <th class="num">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $runningSubtotal = 0; $runningTax = 0; $runningMRP = 0; @endphp
                    @foreach($invoice->items as $idx => $item)
                    @php
                        $lineBase   = $item->quantity * $item->price;
                        $lineTax    = $item->tax_amount ?? 0;
                        $lineMRP    = $item->quantity * $item->mrp_price;
                        $lineTotal  = $item->total;
                        $runningSubtotal += $lineBase;
                        $runningTax      += $lineTax;
                        $runningMRP      += $lineMRP;
                    @endphp
                    <tr>
                        <td class="sno-cell center">{{ str_pad($idx+1, 2, '0', STR_PAD_LEFT) }}</td>

                        <td class="product-name-cell">
                            <strong>{{ $item->product_name }}</strong>
                            @if($item->variant_name)
                                <div class="variant-name">↳ {{ $item->variant_name }}</div>
                            @endif
                            @if($item->sku)
                                <div><span class="sku-badge">SKU: {{ $item->sku }}</span></div>
                            @endif
                        </td>

                        <td class="center" style="font-family:'JetBrains Mono',monospace; font-size:10px; color: var(--text-secondary);">
                            {{ $item->hsn_sac ?: '—' }}
                        </td>

                        <td class="center">
                            <span style="font-family:'JetBrains Mono',monospace; font-size:12px; font-weight:700;">
                                {{ number_format($item->quantity, 2) }}
                            </span>
                            <div style="font-size:9px; color:var(--text-muted); margin-top:2px;">{{ $item->unit }}</div>
                        </td>

                        <td class="center">
                            @if($item->warranty_type && $item->warranty_type !== 'none' && $item->warranty_period > 0)
                                <span class="warranty-badge">
                                    🛡 {{ $item->warranty_period }} {{ ucfirst($item->warranty_type) }}{{ $item->warranty_period > 1 ? 's' : '' }}
                                </span>
                                @if($item->warranty_end)
                                <div style="font-size:9px; color:var(--text-muted); margin-top:3px;">
                                    Until {{ \Carbon\Carbon::parse($item->warranty_end)->format('d M Y') }}
                                </div>
                                @endif
                            @else
                                <span class="warranty-none">—</span>
                            @endif
                        </td>

                        <td class="num">
                            <div class="mrp-val">{{ number_format($item->mrp_price, 2) }}</div>
                        </td>

                        <td class="num">
                            @if($item->discount > 0)
                                <span class="discount-badge">{{ number_format($item->discount, 1) }}%</span>
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>

                        <td class="num">
                            <div class="price-val">{{ number_format($item->price, 2) }}</div>
                            @if($item->quantity > 1)
                            <div style="font-size:9px; color:var(--text-muted);">× {{ number_format($item->quantity, 0) }} = {{ number_format($lineBase, 2) }}</div>
                            @endif
                        </td>

                        <td class="num">
                            <div style="font-size: 11px; font-weight:600;">{{ number_format($item->tax_percent, 0) }}%</div>
                            <div class="tax-breakdown">
                                @if($item->cgst_amount > 0)
                                    C: ₹{{ number_format($item->cgst_amount, 2) }}<br>
                                    S: ₹{{ number_format($item->sgst_amount, 2) }}
                                @elseif($item->igst_amount > 0)
                                    I: ₹{{ number_format($item->igst_amount, 2) }}
                                @endif
                            </div>
                        </td>

                        <td class="num total-cell">
                            ₹ {{ number_format($lineTotal, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="label-cell">Column Totals</td>
                        <td class="num">₹ {{ number_format($runningMRP, 2) }}</td>
                        <td class="num">—</td>
                        <td class="num">₹ {{ number_format($runningSubtotal, 2) }}</td>
                        <td class="num">₹ {{ number_format($runningTax, 2) }}</td>
                        <td class="num">₹ {{ number_format($invoice->grand_total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- ── BOTTOM: NOTES + TOTALS ── -->
        <div class="iv-bottom">

            <!-- Notes & Bank Details -->
            <div class="iv-notes-panel">

                @if($invoice->notes)
                <div class="iv-panel-title">Notes</div>
                <div class="iv-notes-text">{{ $invoice->notes }}</div>
                @endif

                @if($settings && $settings->terms_and_conditions)
                <div class="iv-panel-title" style="{{ $invoice->notes ? 'margin-top:16px;' : '' }}">Terms & Conditions</div>
                <div class="iv-terms-text">{{ $settings->terms_and_conditions }}</div>
                @endif

                @if($settings && ($settings->bank_name || $settings->account_number))
                <div class="iv-panel-title" style="margin-top:16px;">Bank Details</div>
                <div class="iv-bank-block">
                    @if($settings->bank_name)
                    <div class="iv-bank-row"><span class="iv-bank-key">Bank</span><span class="iv-bank-val">{{ $settings->bank_name }}</span></div>
                    @endif
                    @if($settings->account_name)
                    <div class="iv-bank-row"><span class="iv-bank-key">Account Name</span><span class="iv-bank-val">{{ $settings->account_name }}</span></div>
                    @endif
                    @if($settings->account_number)
                    <div class="iv-bank-row"><span class="iv-bank-key">Account No.</span><span class="iv-bank-val">{{ $settings->account_number }}</span></div>
                    @endif
                    @if($settings->ifsc_code)
                    <div class="iv-bank-row"><span class="iv-bank-key">IFSC</span><span class="iv-bank-val">{{ $settings->ifsc_code }}</span></div>
                    @endif
                    @if($settings->branch)
                    <div class="iv-bank-row"><span class="iv-bank-key">Branch</span><span class="iv-bank-val">{{ $settings->branch }}</span></div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Totals -->
            <div class="iv-totals-panel">
                <div class="iv-panel-title">Summary</div>

                <div class="iv-total-row">
                    <span class="iv-total-label">Total MRP</span>
                    <span class="iv-total-value">₹ {{ number_format($invoice->total_mrp ?? 0, 2) }}</span>
                </div>

                @if($invoice->discount_total > 0)
                <div class="iv-total-row discount">
                    <span class="iv-total-label">Item Discounts</span>
                    <span class="iv-total-value">− ₹ {{ number_format($invoice->discount_total, 2) }}</span>
                </div>
                @endif

                <div class="iv-total-row">
                    <span class="iv-total-label" style="font-weight:700; color: var(--text-primary);">Subtotal</span>
                    <span class="iv-total-value">₹ {{ number_format($invoice->subtotal, 2) }}</span>
                </div>

                <div class="iv-divider-line"></div>

                <!-- Tax Breakup -->
                @if($invoice->tax_type === 'intra')
                    @if($invoice->cgst_total > 0)
                    <div class="iv-total-row tax">
                        <span class="iv-total-label">CGST</span>
                        <span class="iv-total-value">+ ₹ {{ number_format($invoice->cgst_total, 2) }}</span>
                    </div>
                    @endif
                    @if($invoice->sgst_total > 0)
                    <div class="iv-total-row tax">
                        <span class="iv-total-label">SGST</span>
                        <span class="iv-total-value">+ ₹ {{ number_format($invoice->sgst_total, 2) }}</span>
                    </div>
                    @endif
                @else
                    @if($invoice->igst_total > 0)
                    <div class="iv-total-row tax">
                        <span class="iv-total-label">IGST</span>
                        <span class="iv-total-value">+ ₹ {{ number_format($invoice->igst_total, 2) }}</span>
                    </div>
                    @endif
                @endif

                <div class="iv-total-row tax">
                    <span class="iv-total-label">Total Tax</span>
                    <span class="iv-total-value">+ ₹ {{ number_format($invoice->tax_total, 2) }}</span>
                </div>

                @if($invoice->extra_discount > 0)
                <div class="iv-divider-line"></div>
                <div class="iv-total-row discount">
                    <span class="iv-total-label">
                        Extra Discount
                        @if($invoice->extra_discount_type === 'percent')
                            ({{ $invoice->extra_discount }}%)
                        @endif
                    </span>
                    <span class="iv-total-value">− ₹ {{ number_format($invoice->extra_discount, 2) }}</span>
                </div>
                @endif

                @if($invoice->extra_charge > 0)
                <div class="iv-total-row">
                    <span class="iv-total-label">{{ $invoice->charge_name ?? 'Extra Charge' }}</span>
                    <span class="iv-total-value">+ ₹ {{ number_format($invoice->extra_charge, 2) }}</span>
                </div>
                @endif

                @if($invoice->round_off != 0)
                <div class="iv-total-row">
                    <span class="iv-total-label">Round Off</span>
                    <span class="iv-total-value">₹ {{ number_format($invoice->round_off, 2) }}</span>
                </div>
                @endif

                <!-- Grand Total -->
                <div class="iv-total-row grand">
                    <span class="iv-total-label">Grand Total</span>
                    <span class="iv-total-value">₹ {{ number_format($invoice->grand_total, 2) }}</span>
                </div>

                @if($invoice->total_paid > 0)
                <div class="iv-total-row paid" style="margin-top: 35px;">
                    <span class="iv-total-label">Amount Paid</span>
                    <span class="iv-total-value">₹ {{ number_format($invoice->total_paid, 2) }}</span>
                </div>
                <div class="iv-total-row {{ $invoice->balance_amount <= 0 ? 'balance-zero' : 'balance' }}">
                    <span class="iv-total-label">Balance Due</span>
                    <span class="iv-total-value">₹ {{ number_format($invoice->balance_amount, 2) }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- ── PAYMENT HISTORY ── -->
        @if($invoice->payments && $invoice->payments->count() > 0)
        <div class="iv-payments-section">
            <div class="iv-panel-title">Payment History</div>
            <table class="iv-pay-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference No.</th>
                        <th>Notes</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $pi => $pay)
                    <tr>
                        <td style="color:var(--text-muted); font-size:10px; font-family:'JetBrains Mono',monospace;">
                            {{ str_pad($pi+1, 2, '0', STR_PAD_LEFT) }}
                        </td>
                        <td style="font-family:'JetBrains Mono',monospace; font-size:11px;">
                            {{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }}
                        </td>
                        <td style="font-family:'JetBrains Mono',monospace; font-size:12px; font-weight:700; color:#059669;">
                            ₹ {{ number_format($pay->amount, 2) }}
                        </td>
                        <td>
                            <span class="pay-method-badge">{{ str_replace('_', ' ', $pay->payment_method) }}</span>
                        </td>
                        <td style="font-family:'JetBrains Mono',monospace; font-size:10px; color:var(--text-secondary);">
                            {{ $pay->reference_no ?: '—' }}
                        </td>
                        <td style="font-size:10px; color:var(--text-secondary);">
                            {{ $pay->notes ?: '—' }}
                        </td>
                        <td>
                            <span class="iv-status-pill" style="background:#d1fae5; color:#065f46; border-color:#6ee7b7; font-size:9px;">
                                ✓ Completed
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- ── FOOTER ── -->
        <div class="iv-footer-bar">
            <div class="iv-footer-note">
                {{ $settings->footer_note ?? 'This is a computer generated invoice. No signature required.' }}
            </div>
            <div class="iv-signature-block">
                @if($settings && $settings->stamp_path)
                <div class="iv-sig-box">
                    <img src="{{ asset('storage/' . $settings->stamp_path) }}" alt="Stamp" class="iv-sig-img">
                    <div class="iv-sig-line">Company Seal</div>
                </div>
                @endif
                @if($settings && $settings->signature_path)
                <div class="iv-sig-box">
                    <img src="{{ asset('storage/' . $settings->signature_path) }}" alt="Signature" class="iv-sig-img">
                    <div class="iv-sig-line">Authorized Signatory</div>
                </div>
                @else
                <div class="iv-sig-box">
                    <div style="height:45px; width:140px; border-bottom:1px solid var(--border-dark);"></div>
                    <div class="iv-sig-line">Authorized Signatory</div>
                </div>
                @endif
            </div>
        </div>

    </div><!-- /.iv-card -->



</div><!-- /.iv-wrap -->

@push('scripts')
<script>
    // ── Alert
    function showAlert(msg, type = 'success') {
        const c = document.getElementById('iv-alert-container');
        const el = document.createElement('div');
        el.className = `iv-alert iv-alert-${type}`;
        const icons = { success: '✓', error: '✕', info: 'ℹ' };
        el.innerHTML = `<span>${icons[type] || '•'}</span> ${msg}`;
        c.appendChild(el);
        setTimeout(() => el.remove(), 5000);
    }

    // ── Print
    function printInvoice() {
        window.print();
    }

    // ── PDF Download (using browser print-to-PDF)
    function downloadPDF() {
        const el = document.getElementById('invoiceToPrint');
        if (window.html2canvas && window.jspdf) {
            // If libraries available
        } else {
            // Fallback: open print dialog, user saves as PDF
            showAlert('To download as PDF, use Print → Save as PDF in the print dialog.', 'info');
            setTimeout(() => window.print(), 1500);
        }
    }




</script>
@endpush
@endsection
