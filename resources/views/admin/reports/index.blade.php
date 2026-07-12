@extends('layouts.admin')

@section('title', 'Reports Dashboard')
@section('header-title', 'Reports Dashboard')

@section('content')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

<style>
    /* Match Dashboard UI Styles */
    .db-reports {
        font-family: 'Inter', sans-serif;
        background: #f7f8fc;
        min-height: 100vh;
        padding: 4px 12px 40px;
        color: #111827;
        font-size: 13px;
    }
    
    .db-top-bar {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .db-greeting {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        letter-spacing: -.2px;
    }
    .db-greeting span { color: #2563eb; }
    
    .db-date-line {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 2px;
        font-weight: 500;
    }

    .report-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 12px;
    }
    
    .report-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: box-shadow .2s, transform .2s;
        position: relative;
        overflow: hidden;
    }
    
    .report-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        transform: translateY(-1px);
    }

    /* Colored Left Bars from Dashboard */
    .report-card::before {
        content: '';
        position: absolute;
        left: 0; top: 12px; bottom: 12px;
        width: 3px;
        border-radius: 0 2px 2px 0;
    }
    
    .card-purple::before { background: #7c3aed; }
    .card-blue::before   { background: #2563eb; }
    .card-teal::before   { background: #0d9488; }
    .card-orange::before { background: #ea580c; }
    .card-red::before    { background: #dc2626; }
    
    .card-header-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 1px solid #f3f4f6;
        padding-bottom: 10px;
        margin-bottom: 12px;
    }
    
    .card-icon {
        width: 30px; height: 30px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    
    .card-purple .card-icon { background: #f5f3ff; color: #7c3aed; }
    .card-blue   .card-icon { background: #eff6ff; color: #2563eb; }
    .card-teal   .card-icon { background: #f0fdfa; color: #0d9488; }
    .card-orange .card-icon { background: #fff7ed; color: #ea580c; }
    .card-red    .card-icon { background: #fef2f2; color: #dc2626; }
    
    .card-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #6b7280;
        margin: 0;
    }
    
    .report-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    
    .report-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 10px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        text-decoration: none;
        color: #374151;
        font-size: 12px;
        font-weight: 600;
        transition: all .15s;
    }
    
    .report-item:hover {
        background: #f0f9ff;
        border-color: #2563eb;
        color: #2563eb;
        transform: translateX(2px);
    }
    
    .item-label {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .badge {
        font-size: 9px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 100px;
        text-transform: uppercase;
    }
    
    .badge-blue { background: #eff6ff; color: #2563eb; }
    .badge-green { background: #f0fdf4; color: #16a34a; }
    .badge-violet { background: #f5f3ff; color: #7c3aed; }

    @media (max-width: 768px) {
        .report-grid {
            grid-template-columns: 1fr;
        }
        .db-reports {
            padding: 8px 6px 30px;
        }
    }
</style>

<div class="db-reports">
    <!-- Top Greeting Section (Matches Dashboard) -->
    <div class="db-top-bar">
        <h2 class="db-greeting">Business & Financial <span>Reports</span></h2>
        <div class="db-date-line">Real-time statistics, GST summaries, and inventory valuation statements</div>
    </div>

    <!-- Reports Grid (Matches Metric Cards Layout) -->
    <div class="report-grid">
        <!-- 👥 PARTY REPORTS -->
        <div class="report-card card-purple">
            <div class="card-header-bar">
                <div class="card-icon">👥</div>
                <h3 class="card-title">Party Reports</h3>
            </div>
            <div class="report-list">
                <a href="{{ route('admin.reports.view-mock', 'party-report-by-item') }}" class="report-item">
                    <span class="item-label">📄 Party Report by Item</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.parties.index') }}" class="report-item">
                    <span class="item-label">📘 Party Statement (Ledger)</span>
                    <span class="badge badge-green">Active</span>
                </a>
                <a href="{{ route('admin.reports.outstanding') }}" class="report-item">
                    <span class="item-label">⏳ Party wise Outstanding</span>
                    <span class="badge badge-green">Active</span>
                </a>
            </div>
        </div>

        <!-- 📦 ITEM REPORTS -->
        <div class="report-card card-blue">
            <div class="card-header-bar">
                <div class="card-icon">📦</div>
                <h3 class="card-title">Item Reports</h3>
            </div>
            <div class="report-list">
                <a href="{{ route('admin.reports.stock-valuation') }}" class="report-item">
                    <span class="item-label">📊 Stock Summary</span>
                    <span class="badge badge-green">Active</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'item-report-by-party') }}" class="report-item">
                    <span class="item-label">👥 Item Report by Party</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'rate-list') }}" class="report-item">
                    <span class="item-label">🏷️ Rate List</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'item-sale-summary') }}" class="report-item">
                    <span class="item-label">📉 Item Sale Summary</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'item-timeline') }}" class="report-item">
                    <span class="item-label">⏳ Item Timeline</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.low-stock') }}" class="report-item">
                    <span class="item-label">⚠️ Low Stock Report</span>
                    <span class="badge badge-green">Active</span>
                </a>
            </div>
        </div>

        <!-- 🏛️ GST REPORTS -->
        <div class="report-card card-teal">
            <div class="card-header-bar">
                <div class="card-icon">🏛️</div>
                <h3 class="card-title">GST Reports</h3>
            </div>
            <div class="report-list">
                <a href="{{ route('admin.reports.view-mock', 'gstr-1') }}" class="report-item">
                    <span class="item-label">📈 GSTR-1 (Sales)</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'gstr-2') }}" class="report-item">
                    <span class="item-label">📉 GSTR-2 (Purchase)</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'gstr-3b') }}" class="report-item">
                    <span class="item-label">💼 GSTR-3B Summary</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'gst-sales-hsn') }}" class="report-item">
                    <span class="item-label">🏷️ GST Sales with HSN</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'tds-payable') }}" class="report-item">
                    <span class="item-label">💸 TDS Payable</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'tds-receivable') }}" class="report-item">
                    <span class="item-label">💰 TDS Receivable</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
            </div>
        </div>

        <!-- 💵 CASH REPORTS -->
        <div class="report-card card-orange">
            <div class="card-header-bar">
                <div class="card-icon">💵</div>
                <h3 class="card-title">Cash & Bank</h3>
            </div>
            <div class="report-list">
                <a href="{{ route('admin.reports.view-mock', 'cash-sales') }}" class="report-item">
                    <span class="item-label">📈 Cash Sales</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'cash-purchase') }}" class="report-item">
                    <span class="item-label">📉 Cash Purchase</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'cash-bank-summary') }}" class="report-item">
                    <span class="item-label">🏦 Cash/Bank Summary</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
            </div>
        </div>

        <!-- 📈 FINANCIAL REPORTS -->
        <div class="report-card card-red">
            <div class="card-header-bar">
                <div class="card-icon">📈</div>
                <h3 class="card-title">Financial & Profits</h3>
            </div>
            <div class="report-list">
                <a href="{{ route('admin.reports.view-mock', 'bill-wise-profits') }}" class="report-item">
                    <span class="item-label">💎 Bill-wise Profits</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'sales-summary') }}" class="report-item">
                    <span class="item-label">📊 Sales Summary</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'daybook') }}" class="report-item">
                    <span class="item-label">📔 Daybook</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'profit-and-loss') }}" class="report-item">
                    <span class="item-label">📊 Profit & Loss (P&L)</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'balance-sheet') }}" class="report-item">
                    <span class="item-label">⚖️ Balance Sheet</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
                <a href="{{ route('admin.reports.view-mock', 'cash-and-bank-all') }}" class="report-item">
                    <span class="item-label">💳 Cash & Bank Ledger</span>
                    <span class="badge badge-blue">Interactive</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
