@extends('layouts.admin')
@section('title', 'Dashboard')
@section('header-title', 'Dashboard')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════
   DASHBOARD — Clean Professional ERP
   Compact · Subtle · Data-focused
═══════════════════════════════════════ */
.db {
    font-family: 'Inter', sans-serif;
    background: #f7f8fc;
    min-height: 100vh;
    padding: 18px 20px 36px;
    color: #111827;
    font-size: 13px;
}

/* ── TOP WELCOME ROW ─────────────────── */
.db-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.db-top-left {}
.db-greeting {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    letter-spacing: -.2px;
}
.db-greeting span { color: #2563eb; }
.db-date-line {
    font-size: 11.5px;
    color: #9ca3af;
    margin-top: 2px;
    font-weight: 500;
}
.db-live {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 100px;
    padding: 3px 10px;
    font-size: 10px;
    font-weight: 700;
    color: #16a34a;
    letter-spacing: .05em;
    text-transform: uppercase;
}
.db-live-dot {
    width: 5px; height: 5px;
    border-radius: 50%;
    background: #16a34a;
    animation: blink 2s infinite;
}

/* Quick banner stats */
.db-quick-stats {
    display: flex;
    gap: 6px;
}
.db-qs {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 14px;
    text-align: center;
    min-width: 68px;
}
.db-qs-v {
    font-size: 17px;
    font-weight: 800;
    color: #111827;
    line-height: 1;
}
.db-qs-l {
    font-size: 9.5px;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: .06em;
    font-weight: 600;
    margin-top: 3px;
}

/* ── SECTION LABEL ───────────────────── */
.db-sec {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: #9ca3af;
    margin: 14px 0 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.db-sec::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e5e7eb;
}

/* ── METRIC CARDS GRID ───────────────── */
.db-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}

.db-mc {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 16px 12px;
    position: relative;
    transition: box-shadow .2s, transform .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    animation: up .4s ease both;
    overflow: hidden;
}
.db-mc:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}

/* left colored bar */
.db-mc::before {
    content: '';
    position: absolute;
    left: 0; top: 10px; bottom: 10px;
    width: 3px;
    border-radius: 0 2px 2px 0;
}
.mc-blue::before   { background: #2563eb; }
.mc-violet::before { background: #7c3aed; }
.mc-teal::before   { background: #0d9488; }
.mc-orange::before { background: #ea580c; }
.mc-green::before  { background: #16a34a; }
.mc-red::before    { background: #dc2626; }
.mc-amber::before  { background: #d97706; }
.mc-sky::before    { background: #0284c7; }

.db-mc-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.db-mc-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}
.mc-blue   .db-mc-icon { background: #eff6ff; }
.mc-violet .db-mc-icon { background: #f5f3ff; }
.mc-teal   .db-mc-icon { background: #f0fdfa; }
.mc-orange .db-mc-icon { background: #fff7ed; }
.mc-green  .db-mc-icon { background: #f0fdf4; }
.mc-red    .db-mc-icon { background: #fef2f2; }
.mc-amber  .db-mc-icon { background: #fffbeb; }
.mc-sky    .db-mc-icon { background: #f0f9ff; }

.db-mc-badge {
    font-size: 9.5px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 100px;
}
.badge-blue   { background: #eff6ff; color: #2563eb; }
.badge-green  { background: #f0fdf4; color: #16a34a; }
.badge-red    { background: #fef2f2; color: #dc2626; }
.badge-amber  { background: #fffbeb; color: #d97706; }

.db-mc-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #9ca3af;
    margin-bottom: 3px;
}
.db-mc-value {
    font-size: 22px;
    font-weight: 800;
    color: #111827;
    letter-spacing: -.4px;
    line-height: 1;
}
.db-mc-sub {
    font-size: 10.5px;
    color: #9ca3af;
    margin-top: 5px;
    font-weight: 500;
    line-height: 1.4;
}
.db-mc-sub b { color: #6b7280; font-weight: 600; }

/* ── SALES ROW ───────────────────────── */
.db-sales-row {
    display: grid;
    grid-template-columns: 2.2fr 1fr 1fr;
    gap: 10px;
}

/* Revenue panel */
.db-rev {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    animation: up .45s ease both;
    position: relative;
    overflow: hidden;
}

.db-rev-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}
.db-rev-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #9ca3af;
    margin-bottom: 4px;
}
.db-rev-amount {
    font-size: 24px;
    font-weight: 800;
    color: #111827;
    letter-spacing: -.5px;
    line-height: 1;
}
.db-rev-meta {
    font-size: 11px;
    color: #9ca3af;
    margin-top: 4px;
    font-weight: 500;
}
.db-rev-meta strong { color: #374151; font-weight: 700; }

/* sparkline chart */
.db-spark {
    display: flex;
    align-items: flex-end;
    gap: 3px;
    height: 40px;
    align-self: flex-end;
}
.db-sbar {
    flex: 1;
    min-height: 3px;
    border-radius: 2px 2px 0 0;
    background: #e5e7eb;
    position: relative;
    overflow: hidden;
    transition: background .2s;
    cursor: default;
}
.db-sbar:hover { background: #d1d5db; }
.db-sbar-fill {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    background: linear-gradient(to top, #2563eb, #93c5fd);
    border-radius: inherit;
    height: 0%;
    transition: height 1.3s cubic-bezier(.4,0,.2,1);
}
.db-slabels {
    display: flex;
    gap: 3px;
    margin-top: 4px;
}
.db-slabel {
    flex: 1;
    text-align: center;
    font-size: 8.5px;
    color: #d1d5db;
    font-weight: 600;
}

/* ── PARTY CARDS ─────────────────────── */
.db-parties {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.db-pc {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow .2s, transform .2s;
    animation: up .5s ease both;
    position: relative;
    overflow: hidden;
}
.db-pc:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}
.db-pc::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 2px;
}
.pc-cust::after  { background: linear-gradient(90deg,#2563eb,#93c5fd); }
.pc-deal::after  { background: linear-gradient(90deg,#d97706,#fcd34d); }
.pc-dist::after  { background: linear-gradient(90deg,#7c3aed,#c4b5fd); }

.db-pc-av {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.pc-cust .db-pc-av { background: #eff6ff; border: 1px solid #dbeafe; }
.pc-deal .db-pc-av { background: #fffbeb; border: 1px solid #fde68a; }
.pc-dist .db-pc-av { background: #f5f3ff; border: 1px solid #ddd6fe; }

.db-pc-lbl {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #9ca3af;
    font-weight: 700;
    margin-bottom: 2px;
}
.db-pc-num {
    font-size: 20px;
    font-weight: 800;
    color: #111827;
    letter-spacing: -.3px;
    line-height: 1;
}
.db-pc-act {
    font-size: 10.5px;
    color: #9ca3af;
    margin-top: 3px;
    font-weight: 500;
}
.db-pc-act .a { color: #16a34a; font-weight: 700; }

/* ── BOTTOM PANELS ───────────────────── */
.db-bottom {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 10px;
}

.db-panel {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    animation: up .55s ease both;
}

.db-ph {
    padding: 12px 16px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fafafa;
}
.db-ph-title {
    font-size: 12.5px;
    font-weight: 700;
    color: #111827;
}
.db-ph-link {
    font-size: 11px;
    font-weight: 700;
    color: #2563eb;
    text-decoration: none;
    opacity: .8;
}
.db-ph-link:hover { opacity: 1; }

/* Table */
.db-tbl { width: 100%; border-collapse: collapse; }
.db-tbl th {
    padding: 7px 16px;
    font-size: 9.5px;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #9ca3af;
    font-weight: 700;
    text-align: left;
    background: #fafafa;
    border-bottom: 1px solid #f3f4f6;
    white-space: nowrap;
}
.db-tbl td {
    padding: 9px 16px;
    font-size: 12.5px;
    color: #374151;
    border-bottom: 1px solid #f9fafb;
}
.db-tbl tr:last-child td { border-bottom: none; }
.db-tbl tbody tr:hover td { background: #fafbff; }

.t-invno { font-size: 11px; font-weight: 700; color: #2563eb; }
.t-date  { font-size: 10px; color: #9ca3af; margin-top: 1px; }
.t-party { font-size: 12.5px; font-weight: 600; color: #111827; }
.t-ptype { font-size: 9.5px; color: #9ca3af; text-transform: capitalize; margin-top: 1px; }
.t-amt   { font-size: 13px; font-weight: 700; color: #111827; }

.pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 100px;
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.p-paid    { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.p-unpaid  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.p-partial { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }

.db-empty { padding: 32px; text-align: center; color: #9ca3af; font-size: 12px; }

/* Right panel rows */
.db-pr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-bottom: 1px solid #f9fafb;
    transition: background .15s;
}
.db-pr:last-child { border-bottom: none; }
.db-pr:hover { background: #fafbff; }
.db-pr-l { display: flex; align-items: center; gap: 8px; }
.db-pr-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.db-pr-lbl { font-size: 12px; color: #374151; font-weight: 500; }
.db-pr-val { font-size: 13px; font-weight: 700; color: #111827; }

/* Progress */
.db-prg { padding: 11px 16px; border-bottom: 1px solid #f9fafb; }
.db-prg:last-child { border-bottom: none; }
.db-prg-hd { display: flex; justify-content: space-between; margin-bottom: 6px; }
.db-prg-lbl { font-size: 11.5px; color: #374151; font-weight: 600; }
.db-prg-val { font-size: 11.5px; font-weight: 700; color: #111827; }
.db-prg-track { height: 5px; background: #f3f4f6; border-radius: 100px; overflow: hidden; }
.db-prg-fill { height: 100%; border-radius: 100px; width: 0%; transition: width 1.5s cubic-bezier(.4,0,.2,1); }

/* Animations */
@keyframes up    { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

.db-mc:nth-child(1){animation-delay:.03s}
.db-mc:nth-child(2){animation-delay:.06s}
.db-mc:nth-child(3){animation-delay:.09s}
.db-mc:nth-child(4){animation-delay:.12s}
.db-pc:nth-child(1){animation-delay:.15s}
.db-pc:nth-child(2){animation-delay:.18s}
.db-pc:nth-child(3){animation-delay:.21s}

/* Responsive */
@media(max-width:1200px){
    .db-metrics   { grid-template-columns:repeat(2,1fr); }
    .db-sales-row { grid-template-columns:1fr 1fr; }
    .db-rev       { grid-column:span 2; }
}
@media(max-width:900px){
    .db { padding:12px 12px 28px; }
    .db-metrics   { grid-template-columns:repeat(2,1fr); }
    .db-sales-row { grid-template-columns:1fr 1fr; }
    .db-rev       { grid-column:span 2; }
    .db-parties   { grid-template-columns:repeat(3,1fr); }
    .db-bottom    { grid-template-columns:1fr; }
    .db-top       { flex-direction:column;align-items:flex-start;gap:12px; }
    .db-quick-stats { flex-wrap:wrap; }
}
@media(max-width:600px){
    .db-metrics { grid-template-columns:1fr 1fr; }
    .db-parties { grid-template-columns:1fr; }
    .db-sales-row { grid-template-columns:1fr; }
    .db-rev { grid-column:span 1; }
}
</style>
@endpush

@section('content')
<div class="db">

    {{-- TOP WELCOME BAR --}}
    <div class="db-top">
        <div class="db-top-left">
            <div class="db-live"><span class="db-live-dot"></span> Live</div>
            <div class="db-greeting" style="margin-top:6px;">
                Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }},
                <span>{{ Auth::guard('admin')->user()->name }}</span>
            </div>
            <div class="db-date-line">{{ now()->format('l, d F Y') }} · Your business overview</div>
        </div>
        <div class="db-quick-stats">
            <div class="db-qs">
                <div class="db-qs-v">{{ $stats['total_invoices'] }}</div>
                <div class="db-qs-l">Invoices</div>
            </div>
            <div class="db-qs">
                <div class="db-qs-v" style="color:#16a34a">{{ $stats['paid_invoices'] }}</div>
                <div class="db-qs-l">Paid</div>
            </div>
            <div class="db-qs">
                <div class="db-qs-v" style="color:#dc2626">{{ $stats['unpaid_invoices'] }}</div>
                <div class="db-qs-l">Unpaid</div>
            </div>
            <div class="db-qs">
                <div class="db-qs-v" style="color:#d97706">{{ $stats['partial_invoices'] }}</div>
                <div class="db-qs-l">Partial</div>
            </div>
        </div>
    </div>

    {{-- KEY METRICS --}}
    <div class="db-sec">Key Metrics</div>
    <div class="db-metrics">

        <div class="db-mc mc-blue">
            <div class="db-mc-top">
                <div class="db-mc-icon">📦</div>
                <span class="db-mc-badge badge-blue">All</span>
            </div>
            <div class="db-mc-label">Products</div>
            <div class="db-mc-value">{{ number_format($stats['total_products']) }}</div>
            <div class="db-mc-sub"><b>{{ $stats['active_products'] }}</b> active · {{ $stats['inactive_products'] }} inactive</div>
        </div>

        <div class="db-mc mc-violet">
            <div class="db-mc-top">
                <div class="db-mc-icon">🤝</div>
                <span class="db-mc-badge badge-blue" style="background:#f5f3ff;color:#7c3aed;">All</span>
            </div>
            <div class="db-mc-label">Total Parties</div>
            <div class="db-mc-value">{{ number_format($stats['total_parties']) }}</div>
            <div class="db-mc-sub">Customers, Dealers & Distributors</div>
        </div>

        <div class="db-mc mc-teal">
            <div class="db-mc-top">
                <div class="db-mc-icon">🧑‍💼</div>
                <span class="db-mc-badge badge-green" style="background:#f0fdfa;color:#0d9488;">{{ $stats['active_salesmen'] }} Active</span>
            </div>
            <div class="db-mc-label">Salesmen</div>
            <div class="db-mc-value">{{ number_format($stats['total_salesmen']) }}</div>
            <div class="db-mc-sub"><b>{{ $stats['active_salesmen'] }}</b> active · {{ $stats['inactive_salesmen'] }} inactive</div>
        </div>

        <div class="db-mc mc-orange">
            <div class="db-mc-top">
                <div class="db-mc-icon">🏭</div>
                <span class="db-mc-badge badge-amber">{{ $stats['active_warehouses'] }} Active</span>
            </div>
            <div class="db-mc-label">Warehouses</div>
            <div class="db-mc-value">{{ number_format($stats['total_warehouses']) }}</div>
            <div class="db-mc-sub">Main: <b>{{ $stats['main_warehouse'] ?? 'Not Set' }}</b></div>
        </div>

    </div>

    {{-- SALES OVERVIEW --}}
    <div class="db-sec">Sales Overview</div>
    <div class="db-sales-row">

        <div class="db-rev">
            <div class="db-rev-top">
                <div>
                    <div class="db-rev-label">Total Revenue</div>
                    <div class="db-rev-amount">₹{{ number_format($stats['total_revenue'], 2) }}</div>
                    <div class="db-rev-meta">
                        <strong>{{ $stats['total_invoices'] }}</strong> invoices ·
                        <strong>₹{{ number_format($stats['total_collected'], 2) }}</strong> collected
                    </div>
                </div>
                <div>
                    <div class="db-spark">
                        @foreach($stats['monthly_sales'] as $bar)
                            <div class="db-sbar"
                                 data-h="{{ $stats['max_monthly_sale'] > 0 ? round(($bar['total']/$stats['max_monthly_sale'])*100) : 5 }}"
                                 title="{{ $bar['label'] }}: ₹{{ number_format($bar['total']) }}">
                                <div class="db-sbar-fill"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="db-slabels">
                        @foreach($stats['monthly_sales'] as $bar)
                            <div class="db-slabel">{{ substr($bar['label'],0,1) }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="db-mc mc-green" style="border-radius:10px;padding:16px 18px;">
            <div class="db-mc-top">
                <div class="db-mc-icon">✅</div>
                <span class="db-mc-badge badge-green">Cleared</span>
            </div>
            <div class="db-mc-label">Paid Invoices</div>
            <div class="db-mc-value">{{ number_format($stats['paid_invoices']) }}</div>
            <div class="db-mc-sub">{{ $stats['partial_invoices'] }} partially paid</div>
        </div>

        <div class="db-mc mc-red" style="border-radius:10px;padding:16px 18px;">
            <div class="db-mc-top">
                <div class="db-mc-icon">⚠️</div>
                <span class="db-mc-badge badge-red">Pending</span>
            </div>
            <div class="db-mc-label">Unpaid Invoices</div>
            <div class="db-mc-value">{{ number_format($stats['unpaid_invoices']) }}</div>
            <div class="db-mc-sub">₹{{ number_format($stats['unpaid_amount'], 2) }} outstanding</div>
        </div>

    </div>

    {{-- PARTY BREAKDOWN --}}
    <div class="db-sec">Party Breakdown</div>
    <div class="db-parties">

        <div class="db-pc pc-cust">
            <div class="db-pc-av">🛍️</div>
            <div>
                <div class="db-pc-lbl">Customers</div>
                <div class="db-pc-num">{{ number_format($stats['total_customers']) }}</div>
                <div class="db-pc-act">Active: <span class="a">{{ $stats['active_customers'] }}</span></div>
            </div>
        </div>

        <div class="db-pc pc-deal">
            <div class="db-pc-av">🏪</div>
            <div>
                <div class="db-pc-lbl">Dealers</div>
                <div class="db-pc-num">{{ number_format($stats['total_dealers']) }}</div>
                <div class="db-pc-act">Active: <span class="a">{{ $stats['active_dealers'] }}</span></div>
            </div>
        </div>

        <div class="db-pc pc-dist">
            <div class="db-pc-av">🏢</div>
            <div>
                <div class="db-pc-lbl">Distributors</div>
                <div class="db-pc-num">{{ number_format($stats['total_distributors']) }}</div>
                <div class="db-pc-act">Active: <span class="a">{{ $stats['active_distributors'] }}</span></div>
            </div>
        </div>

    </div>

    {{-- BOTTOM: TABLE + SUMMARY --}}
    <div class="db-sec">Activity & Insights</div>
    <div class="db-bottom">

        <div class="db-panel">
            <div class="db-ph">
                <div class="db-ph-title">Recent Sales Invoices</div>
                <a href="{{ route('admin.sales.index') }}" class="db-ph-link">View all →</a>
            </div>
            @if($recentSales->isNotEmpty())
            <table class="db-tbl">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Party</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentSales as $s)
                    <tr>
                        <td>
                            <div class="t-invno">{{ $s->invoice_number }}</div>
                            <div class="t-date">{{ \Carbon\Carbon::parse($s->invoice_date)->format('d M Y') }}</div>
                        </td>
                        <td>
                            <div class="t-party">{{ $s->party->name ?? '—' }}</div>
                            @if($s->party)<div class="t-ptype">{{ $s->party->party_type ?? '' }}</div>@endif
                        </td>
                        <td><div class="t-amt">₹{{ number_format($s->grand_total, 2) }}</div></td>
                        <td><span class="pill p-{{ $s->payment_status }}">{{ ucfirst($s->payment_status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="db-empty">No invoices found</div>
            @endif
        </div>

        <div class="db-panel">
            <div class="db-ph"><div class="db-ph-title">Inventory Split</div></div>

            <div class="db-prg">
                <div class="db-prg-hd">
                    <span class="db-prg-lbl">Simple Products</span>
                    <span class="db-prg-val">{{ $stats['total_simple_products'] }}</span>
                </div>
                <div class="db-prg-track">
                    <div class="db-prg-fill" style="background:#2563eb"
                         data-w="{{ $stats['total_products'] > 0 ? round(($stats['total_simple_products']/$stats['total_products'])*100) : 0 }}"></div>
                </div>
            </div>
            <div class="db-prg">
                <div class="db-prg-hd">
                    <span class="db-prg-lbl">Variant Products</span>
                    <span class="db-prg-val">{{ $stats['total_variant_products'] }}</span>
                </div>
                <div class="db-prg-track">
                    <div class="db-prg-fill" style="background:#7c3aed"
                         data-w="{{ $stats['total_products'] > 0 ? round(($stats['total_variant_products']/$stats['total_products'])*100) : 0 }}"></div>
                </div>
            </div>

            <div class="db-ph" style="border-top:1px solid #f3f4f6;margin-top:2px;"><div class="db-ph-title">Payment Summary</div></div>

            <div class="db-pr">
                <div class="db-pr-l"><div class="db-pr-dot" style="background:#16a34a"></div><span class="db-pr-lbl">Collected</span></div>
                <div class="db-pr-val" style="color:#16a34a">₹{{ number_format($stats['total_collected'], 2) }}</div>
            </div>
            <div class="db-pr">
                <div class="db-pr-l"><div class="db-pr-dot" style="background:#dc2626"></div><span class="db-pr-lbl">Outstanding</span></div>
                <div class="db-pr-val" style="color:#dc2626">₹{{ number_format($stats['unpaid_amount'], 2) }}</div>
            </div>
            <div class="db-pr">
                <div class="db-pr-l"><div class="db-pr-dot" style="background:#d97706"></div><span class="db-pr-lbl">Partial Invoices</span></div>
                <div class="db-pr-val" style="color:#d97706">{{ $stats['partial_invoices'] }}</div>
            </div>
            <div class="db-pr">
                <div class="db-pr-l"><div class="db-pr-dot" style="background:#7c3aed"></div><span class="db-pr-lbl">Avg Invoice</span></div>
                <div class="db-pr-val">₹{{ $stats['total_invoices'] > 0 ? number_format($stats['total_revenue']/$stats['total_invoices'],2) : '0' }}</div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.db-sbar').forEach(function(b, i) {
        var h = parseInt(b.getAttribute('data-h')) || 5;
        setTimeout(function(){ b.querySelector('.db-sbar-fill').style.height = h+'%'; }, 150 + i*70);
    });
    document.querySelectorAll('.db-prg-fill').forEach(function(p) {
        var w = p.getAttribute('data-w') || 0;
        setTimeout(function(){ p.style.width = w+'%'; }, 350);
    });
});
</script>
@endpush
