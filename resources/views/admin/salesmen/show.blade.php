@extends('layouts.admin')

@section('title', 'Salesman Details - Admin Panel')
@section('header-title', 'Salesman Details')

@section('content')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg: #f5f4f0;
    --surface: #ffffff;
    --border: #e8e5df;
    --text-primary: #1a1816;
    --text-secondary: #7a756e;
    --text-muted: #b0aa9f;
    --accent: #fa8128;
    --accent-light: #fff4ec;
    --accent-dark: #e06a12;
    --green: #2d7a5f;
    --green-light: #eaf5f0;
    --radius: 12px;
    --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
    --shadow-md: 0 4px 24px rgba(0,0,0,0.08);
}

.sm-wrap {
    font-family: 'DM Sans', sans-serif;
    font-size: 12.5px;
    background: var(--bg);
    padding: 24px;
    min-height: 100vh;
}

/* ─── TOP BAR ─── */
.sm-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}

.sm-identity {
    display: flex;
    align-items: center;
    gap: 14px;
}

.sm-avatar {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--accent), #ff6b35);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Syne', sans-serif;
    font-size: 18px; font-weight: 700;
    color: white;
    letter-spacing: -0.5px;
    flex-shrink: 0;
}

.sm-name-block {}
.sm-name {
    font-family: 'Syne', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.1;
    letter-spacing: -0.4px;
}
.sm-sub {
    font-size: 11.5px;
    color: var(--text-muted);
    margin-top: 3px;
}

.pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.3px;
}
.pill-active { background: var(--green-light); color: var(--green); }
.pill-inactive { background: #f0f0ee; color: var(--text-secondary); }
.pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; display: inline-block; }

.sm-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 500;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all 0.15s ease;
}
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { background: var(--accent-dark); color: white; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(250,129,40,0.3); }
.btn-outline { background: white; color: var(--text-primary); border: 1.5px solid var(--border); }
.btn-outline:hover { border-color: #ccc8c0; background: #fafaf8; }
.btn-ghost { background: transparent; color: var(--text-secondary); border: 1.5px solid var(--border); }
.btn-ghost:hover { background: white; color: var(--text-primary); }

/* ─── LAYOUT ─── */
.sm-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 16px;
}

.sm-col-left { display: flex; flex-direction: column; gap: 16px; }
.sm-col-right { display: flex; flex-direction: column; gap: 16px; }

/* ─── CARD ─── */
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
}

.card-head {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 8px;
}
.card-head-icon {
    width: 28px; height: 28px;
    background: var(--accent-light);
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
}
.card-head-title {
    font-family: 'Syne', sans-serif;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text-primary);
    letter-spacing: 0.2px;
    flex: 1;
}
.card-body { padding: 18px; }

/* ─── INFO ROWS ─── */
.info-list { display: flex; flex-direction: column; gap: 0; }
.info-item {
    display: flex;
    align-items: flex-start;
    padding: 9px 0;
    border-bottom: 1px solid #f5f3ef;
}
.info-item:last-child { border-bottom: none; }
.info-key {
    width: 110px;
    flex-shrink: 0;
    font-size: 11.5px;
    color: var(--text-muted);
    font-weight: 400;
    padding-top: 1px;
}
.info-val {
    flex: 1;
    font-size: 12.5px;
    font-weight: 500;
    color: var(--text-primary);
}
.info-val.money { color: var(--green); font-weight: 600; font-size: 13.5px; }
.mono {
    font-family: 'Courier New', monospace;
    font-size: 11px;
    background: #f5f3ef;
    padding: 2px 7px;
    border-radius: 5px;
    color: var(--text-secondary);
}

/* ─── BADGE ─── */
.badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
}
.badge-neutral { background: #f0f0ee; color: var(--text-secondary); }
.badge-green { background: var(--green-light); color: var(--green); }
.badge-orange { background: var(--accent-light); color: var(--accent-dark); }

/* ─── SALARY HIGHLIGHT ─── */
.salary-hero {
    background: linear-gradient(135deg, #1a1816 0%, #2d2926 100%);
    border-radius: 10px;
    padding: 20px;
    color: white;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}
.salary-hero::after {
    content: '₹';
    position: absolute;
    right: -10px; top: -10px;
    font-size: 80px;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    opacity: 0.06;
    line-height: 1;
}
.salary-label { font-size: 10.5px; color: rgba(255,255,255,0.5); font-weight: 400; margin-bottom: 4px; letter-spacing: 0.8px; text-transform: uppercase; }
.salary-amount { font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 700; letter-spacing: -0.5px; }
.salary-sub { font-size: 11px; color: rgba(255,255,255,0.4); margin-top: 3px; }

/* ─── COMMISSION RATES ─── */
.rate-items { display: flex; flex-direction: column; gap: 10px; }
.rate-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    background: var(--bg);
    border-radius: 8px;
    border: 1px solid var(--border);
}
.rate-left { display: flex; align-items: center; gap: 10px; }
.rate-icon-wrap {
    width: 32px; height: 32px;
    background: white;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    border: 1px solid var(--border);
}
.rate-name { font-size: 12px; font-weight: 500; color: var(--text-primary); }
.rate-desc { font-size: 10.5px; color: var(--text-muted); }
.rate-percent { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; color: var(--accent); }

/* ─── STATS ─── */
.stats-trio {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.stat-cell {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px 12px;
    text-align: center;
}
.stat-n {
    font-family: 'Syne', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1;
}
.stat-l {
    font-size: 10.5px;
    color: var(--text-muted);
    margin-top: 4px;
    font-weight: 400;
}

/* ─── TABLE ─── */
.sm-table { width: 100%; border-collapse: collapse; }
.sm-table thead th {
    padding: 9px 14px;
    text-align: left;
    font-size: 10.5px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    background: var(--bg);
    border-bottom: 1px solid var(--border);
}
.sm-table thead th:first-child { border-radius: 8px 0 0 0; }
.sm-table thead th:last-child { border-radius: 0 8px 0 0; }
.sm-table tbody td {
    padding: 11px 14px;
    border-bottom: 1px solid #f5f3ef;
    font-size: 12.5px;
    color: var(--text-primary);
    vertical-align: middle;
}
.sm-table tbody tr:last-child td { border-bottom: none; }
.sm-table tbody tr:hover td { background: #faf9f7; }

/* ─── NOTES ─── */
.notes-box {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px;
    font-size: 12.5px;
    color: var(--text-secondary);
    line-height: 1.65;
}

/* ─── SYSTEM GRID ─── */
.sys-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
.sys-item {
    padding: 10px 0;
    border-bottom: 1px solid #f5f3ef;
}
.sys-grid > .sys-item:nth-last-child(-n+2) { border-bottom: none; }
.sys-key { font-size: 11px; color: var(--text-muted); margin-bottom: 3px; }
.sys-val { font-size: 12px; font-weight: 500; color: var(--text-primary); }

/* ─── SECTION LABEL ─── */
.section-label {
    font-size: 10.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: var(--text-muted);
    margin-bottom: 12px;
}

.empty-state {
    text-align: center;
    padding: 28px;
    color: var(--text-muted);
    font-size: 12px;
}
.empty-state-icon { font-size: 28px; margin-bottom: 8px; }

@media (max-width: 900px) {
    .sm-grid { grid-template-columns: 1fr; }
    .stats-trio { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 600px) {
    .sm-wrap { padding: 14px; }
    .sm-topbar { flex-direction: column; align-items: flex-start; }
    .sm-actions { width: 100%; }
    .btn { flex: 1; justify-content: center; }
    .stats-trio { grid-template-columns: 1fr; }
    .sys-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

<div class="sm-wrap">

    <!-- TOP BAR -->
    <div class="sm-topbar">
        <div class="sm-identity">
            <div class="sm-avatar">{{ strtoupper(substr($salesman->name, 0, 2)) }}</div>
            <div class="sm-name-block">
                <div class="sm-name">{{ $salesman->name }}</div>
                <div class="sm-sub">
                    <span class="pill pill-{{ $salesman->status }}">{{ ucfirst($salesman->status) }}</span>
                    &nbsp;·&nbsp; ID: <span class="mono">{{ $salesman->id }}</span>
                </div>
            </div>
        </div>
        <div class="sm-actions">
            <button class="btn btn-outline" onclick="downloadReport()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Report
            </button>
            <a href="{{ route('admin.salesmen.edit', $salesman->id) }}" class="btn btn-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </a>
            <a href="{{ route('admin.salesmen.index') }}" class="btn btn-ghost">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                Back
            </a>
        </div>
    </div>

    <!-- GRID -->
    <div class="sm-grid">

        <!-- LEFT COLUMN -->
        <div class="sm-col-left">

            <!-- Personal Info -->
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon">👤</div>
                    <span class="card-head-title">Personal Info</span>
                </div>
                <div class="card-body" style="padding-top:8px; padding-bottom:8px;">
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-key">Full Name</span>
                            <span class="info-val">{{ $salesman->name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-key">Phone</span>
                            <span class="info-val">{{ $salesman->phone }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-key">Email</span>
                            <span class="info-val" style="color:{{ $salesman->email ? 'inherit' : 'var(--text-muted)' }}">{{ $salesman->email ?? '—' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-key">Joined</span>
                            <span class="info-val">{{ $salesman->formatted_joining_date }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Salary Info -->
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon">💰</div>
                    <span class="card-head-title">Salary</span>
                    <span class="badge badge-neutral">{{ $salesman->salary_type_text }}</span>
                </div>
                <div class="card-body">
                    @if($salesman->salary_type != 'commission')
                    <div class="salary-hero">
                        <div class="salary-label">Fixed Monthly</div>
                        <div class="salary-amount">{{ $salesman->formatted_fixed_salary }}</div>
                        <div class="salary-sub">per month</div>
                    </div>
                    @endif
                    @if($salesman->salary_type != 'fixed')
                    <div class="info-list">
                        <div class="info-item" style="border:none; padding-top:0;">
                            <span class="info-key">Commission</span>
                            <span class="info-val">
                                @if($salesman->commission_enabled)
                                    <span class="badge badge-green">✓ Enabled</span>
                                @else
                                    <span class="badge badge-neutral">Disabled</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- System Info -->
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon">⚙️</div>
                    <span class="card-head-title">System Info</span>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-key">Record ID</span>
                            <span class="info-val"><span class="mono">{{ $salesman->id }}</span></span>
                        </div>
                        <div class="info-item">
                            <span class="info-key">Created</span>
                            <span class="info-val">{{ $salesman->created_at?->format('d M Y') ?? 'N/A' }}<br><span style="font-size:11px;color:var(--text-muted)">{{ $salesman->created_at?->format('h:i A') }}</span></span>
                        </div>
                        <div class="info-item">
                            <span class="info-key">Updated</span>
                            <span class="info-val">{{ $salesman->updated_at?->format('d M Y') ?? 'N/A' }}<br><span style="font-size:11px;color:var(--text-muted)">{{ $salesman->updated_at?->format('h:i A') }}</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($salesman->notes)
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon">📝</div>
                    <span class="card-head-title">Notes</span>
                </div>
                <div class="card-body">
                    <div class="notes-box">{{ $salesman->notes }}</div>
                </div>
            </div>
            @endif
        </div>

        <!-- RIGHT COLUMN -->
        <div class="sm-col-right">

            <!-- Commission Rates -->
            @if($salesman->commission_enabled && $salesman->salary_type != 'fixed')
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon">📊</div>
                    <span class="card-head-title">Commission Rates</span>
                </div>
                <div class="card-body">
                    <div class="rate-items">
                        @if($salesman->customer_commission_percent > 0)
                        <div class="rate-item">
                            <div class="rate-left">
                                <div class="rate-icon-wrap">👥</div>
                                <div>
                                    <div class="rate-name">Customers</div>
                                    <div class="rate-desc">On customer sales</div>
                                </div>
                            </div>
                            <div class="rate-percent">{{ $salesman->customer_commission_percent }}%</div>
                        </div>
                        @endif
                        @if($salesman->dealer_commission_percent > 0)
                        <div class="rate-item">
                            <div class="rate-left">
                                <div class="rate-icon-wrap">🏢</div>
                                <div>
                                    <div class="rate-name">Dealers</div>
                                    <div class="rate-desc">On dealer sales</div>
                                </div>
                            </div>
                            <div class="rate-percent">{{ $salesman->dealer_commission_percent }}%</div>
                        </div>
                        @endif
                        @if($salesman->distributor_commission_percent > 0)
                        <div class="rate-item">
                            <div class="rate-left">
                                <div class="rate-icon-wrap">📦</div>
                                <div>
                                    <div class="rate-name">Distributors</div>
                                    <div class="rate-desc">On distributor sales</div>
                                </div>
                            </div>
                            <div class="rate-percent">{{ $salesman->distributor_commission_percent }}%</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Assigned Parties -->
            <div class="card" style="flex:1;">
                <div class="card-head">
                    <div class="card-head-icon">👥</div>
                    <span class="card-head-title">Assigned Parties</span>
                    <span class="badge badge-neutral" id="totalParties">—</span>
                </div>
                <div class="card-body">
                    <div class="stats-trio">
                        <div class="stat-cell">
                            <div class="stat-n" id="customerCount">—</div>
                            <div class="stat-l">Customers</div>
                        </div>
                        <div class="stat-cell">
                            <div class="stat-n" id="dealerCount">—</div>
                            <div class="stat-l">Dealers</div>
                        </div>
                        <div class="stat-cell">
                            <div class="stat-n" id="distributorCount">—</div>
                            <div class="stat-l">Distributors</div>
                        </div>
                    </div>

                    <div class="section-label">Recent Assignments</div>
                    <div style="border:1px solid var(--border); border-radius:8px; overflow:hidden;">
                        <table class="sm-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="recentPartiesBody">
                                <tr><td colspan="4" class="empty-state"><div class="empty-state-icon">⏳</div>Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PRINT REPORT STYLES -->
<style id="print-styles">
@media print {
    body * { visibility: hidden; }
    #printReport, #printReport * { visibility: visible; }
    #printReport { position: fixed; inset: 0; z-index: 9999; }
}
</style>

<!-- HIDDEN PRINT REPORT -->
<div id="printReport" style="display:none; font-family:'DM Sans',sans-serif; padding:40px; max-width:800px; margin:auto; color:#1a1816;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:32px; padding-bottom:20px; border-bottom:2px solid #1a1816;">
        <div>
            <div style="font-family:'Syne',sans-serif; font-size:26px; font-weight:800; letter-spacing:-0.5px;">{{ $salesman->name }}</div>
            <div style="font-size:12px; color:#7a756e; margin-top:4px;">Salesman Profile Report</div>
        </div>
        <div style="text-align:right;">
            <div style="background:#1a1816; color:white; padding:6px 14px; border-radius:6px; font-size:11px; font-weight:600; display:inline-block; text-transform:uppercase; letter-spacing:0.5px;">{{ ucfirst($salesman->status) }}</div>
            <div style="font-size:11px; color:#b0aa9f; margin-top:6px;" id="reportDate"></div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:28px;">
        <div>
            <div style="font-size:10px; text-transform:uppercase; letter-spacing:0.8px; color:#b0aa9f; font-weight:600; margin-bottom:10px;">Personal Information</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; justify-content:space-between; font-size:12.5px;"><span style="color:#7a756e;">Phone</span><span style="font-weight:500;">{{ $salesman->phone }}</span></div>
                <div style="display:flex; justify-content:space-between; font-size:12.5px;"><span style="color:#7a756e;">Email</span><span style="font-weight:500;">{{ $salesman->email ?? '—' }}</span></div>
                <div style="display:flex; justify-content:space-between; font-size:12.5px;"><span style="color:#7a756e;">Joined</span><span style="font-weight:500;">{{ $salesman->formatted_joining_date }}</span></div>
            </div>
        </div>
        <div>
            <div style="font-size:10px; text-transform:uppercase; letter-spacing:0.8px; color:#b0aa9f; font-weight:600; margin-bottom:10px;">Salary Details</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; justify-content:space-between; font-size:12.5px;"><span style="color:#7a756e;">Type</span><span style="font-weight:500;">{{ $salesman->salary_type_text }}</span></div>
                @if($salesman->salary_type != 'commission')
                <div style="display:flex; justify-content:space-between; font-size:12.5px;"><span style="color:#7a756e;">Fixed Salary</span><span style="font-weight:600; color:#2d7a5f;">{{ $salesman->formatted_fixed_salary }}/mo</span></div>
                @endif
                <div style="display:flex; justify-content:space-between; font-size:12.5px;"><span style="color:#7a756e;">Commission</span><span style="font-weight:500;">{{ $salesman->commission_enabled ? 'Enabled' : 'Disabled' }}</span></div>
            </div>
        </div>
    </div>

    @if($salesman->commission_enabled && $salesman->salary_type != 'fixed')
    <div style="margin-bottom:28px;">
        <div style="font-size:10px; text-transform:uppercase; letter-spacing:0.8px; color:#b0aa9f; font-weight:600; margin-bottom:12px;">Commission Rates</div>
        <div style="display:flex; gap:12px;">
            @if($salesman->customer_commission_percent > 0)
            <div style="flex:1; background:#f5f4f0; border-radius:8px; padding:14px; text-align:center;">
                <div style="font-size:22px; font-weight:700; color:#fa8128;">{{ $salesman->customer_commission_percent }}%</div>
                <div style="font-size:11px; color:#7a756e; margin-top:3px;">Customers</div>
            </div>
            @endif
            @if($salesman->dealer_commission_percent > 0)
            <div style="flex:1; background:#f5f4f0; border-radius:8px; padding:14px; text-align:center;">
                <div style="font-size:22px; font-weight:700; color:#fa8128;">{{ $salesman->dealer_commission_percent }}%</div>
                <div style="font-size:11px; color:#7a756e; margin-top:3px;">Dealers</div>
            </div>
            @endif
            @if($salesman->distributor_commission_percent > 0)
            <div style="flex:1; background:#f5f4f0; border-radius:8px; padding:14px; text-align:center;">
                <div style="font-size:22px; font-weight:700; color:#fa8128;">{{ $salesman->distributor_commission_percent }}%</div>
                <div style="font-size:11px; color:#7a756e; margin-top:3px;">Distributors</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div id="reportPartiesSection" style="margin-bottom:28px; display:none;">
        <div style="font-size:10px; text-transform:uppercase; letter-spacing:0.8px; color:#b0aa9f; font-weight:600; margin-bottom:12px;">Assigned Parties Summary</div>
        <div style="display:flex; gap:12px; margin-bottom:16px;" id="reportStatsRow"></div>
        <table style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr style="background:#f5f4f0;">
                    <th style="padding:9px 12px; text-align:left; font-size:10.5px; color:#7a756e; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Name</th>
                    <th style="padding:9px 12px; text-align:left; font-size:10.5px; color:#7a756e; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Type</th>
                    <th style="padding:9px 12px; text-align:left; font-size:10.5px; color:#7a756e; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Phone</th>
                    <th style="padding:9px 12px; text-align:left; font-size:10.5px; color:#7a756e; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Status</th>
                </tr>
            </thead>
            <tbody id="reportPartiesBody"></tbody>
        </table>
    </div>

    @if($salesman->notes)
    <div style="margin-bottom:28px;">
        <div style="font-size:10px; text-transform:uppercase; letter-spacing:0.8px; color:#b0aa9f; font-weight:600; margin-bottom:10px;">Notes</div>
        <div style="background:#f5f4f0; border-radius:8px; padding:14px; font-size:12.5px; color:#4b5563; line-height:1.65;">{{ $salesman->notes }}</div>
    </div>
    @endif

    <div style="margin-top:40px; padding-top:16px; border-top:1px solid #e8e5df; display:flex; justify-content:space-between; font-size:10.5px; color:#b0aa9f;">
        <span>Generated on <span id="reportFooterDate"></span></span>
        <span>Salesman ID: {{ $salesman->id }}</span>
    </div>
</div>

@push('scripts')
<script>
let partiesData = null;

async function loadAssignedParties() {
    try {
        const res = await fetch('/admin/salesmen/{{ $salesman->id }}/parties');
        const data = await res.json();
        partiesData = data;

        document.getElementById('customerCount').textContent = data.customers ?? 0;
        document.getElementById('dealerCount').textContent = data.dealers ?? 0;
        document.getElementById('distributorCount').textContent = data.distributors ?? 0;
        document.getElementById('totalParties').textContent = `Total: ${data.total ?? 0}`;

        const tbody = document.getElementById('recentPartiesBody');
        if (data.recent?.length) {
            tbody.innerHTML = data.recent.map(p => `
                <tr>
                    <td style="font-weight:500;">${p.name}</td>
                    <td><span class="badge badge-neutral">${p.party_type}</span></td>
                    <td style="color:var(--text-secondary);">${p.phone}</td>
                    <td><span class="pill pill-${p.status}" style="font-size:10.5px;">${ucFirst(p.status)}</span></td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = `<tr><td colspan="4" class="empty-state"><div class="empty-state-icon">🔍</div>No parties assigned yet</td></tr>`;
        }
    } catch (e) {
        console.error(e);
        document.getElementById('recentPartiesBody').innerHTML = `<tr><td colspan="4" class="empty-state">Failed to load</td></tr>`;
    }
}

function ucFirst(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : str; }

function downloadReport() {
    const now = new Date().toLocaleString('en-IN', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
    document.getElementById('reportDate').textContent = now;
    document.getElementById('reportFooterDate').textContent = now;

    const section = document.getElementById('reportPartiesSection');
    const statsRow = document.getElementById('reportStatsRow');
    const partiesBody = document.getElementById('reportPartiesBody');

    if (partiesData) {
        section.style.display = 'block';
        const stats = [
            { label: 'Customers', val: partiesData.customers ?? 0 },
            { label: 'Dealers', val: partiesData.dealers ?? 0 },
            { label: 'Distributors', val: partiesData.distributors ?? 0 },
        ];
        statsRow.innerHTML = stats.map(s => `
            <div style="flex:1;background:#f5f4f0;border-radius:8px;padding:12px;text-align:center;">
                <div style="font-size:20px;font-weight:700;color:#1a1816;">${s.val}</div>
                <div style="font-size:11px;color:#7a756e;margin-top:2px;">${s.label}</div>
            </div>
        `).join('');
        if (partiesData.recent?.length) {
            partiesBody.innerHTML = partiesData.recent.map((p, i) => `
                <tr style="background:${i%2===0?'white':'#f9f9f7'};">
                    <td style="padding:9px 12px;font-size:12px;font-weight:500;border-bottom:1px solid #f0ede8;">${p.name}</td>
                    <td style="padding:9px 12px;font-size:12px;color:#7a756e;border-bottom:1px solid #f0ede8;">${ucFirst(p.party_type)}</td>
                    <td style="padding:9px 12px;font-size:12px;color:#7a756e;border-bottom:1px solid #f0ede8;">${p.phone}</td>
                    <td style="padding:9px 12px;font-size:12px;border-bottom:1px solid #f0ede8;">${ucFirst(p.status)}</td>
                </tr>
            `).join('');
        }
    }

    const report = document.getElementById('printReport');
    report.style.display = 'block';
    window.print();
    setTimeout(() => { report.style.display = 'none'; }, 1000);
}

document.addEventListener('DOMContentLoaded', loadAssignedParties);
</script>
@endpush
@endsection
