@extends('layouts.admin')

@section('title', 'Purchase Executive — ' . $purchaseExecutive->name)
@section('header-title', 'Purchase Executive Details')

@section('content')
@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box;}
body{font-family:'Inter',-apple-system,sans-serif;font-size:13.5px;color:#1a1a1a;background:#f4f5f7;}

.pg{padding:20px 24px;max-width:1360px;}

/* Topbar */
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;}
.pe-identity{display:flex;align-items:center;gap:12px;}
.pe-avatar{width:42px;height:42px;border-radius:10px;background:#059669;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;color:#fff;flex-shrink:0;}
.pe-name{font-size:17px;font-weight:700;line-height:1.2;}
.pe-meta{font-size:12px;color:#6b7280;margin-top:2px;}
.topbar-actions{display:flex;gap:8px;flex-wrap:wrap;}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:7px;font-size:12.5px;font-weight:500;cursor:pointer;border:none;text-decoration:none;transition:background .15s;font-family:inherit;}
.btn-primary{background:#2563eb;color:#fff;}.btn-primary:hover{background:#1d4ed8;color:#fff;}
.btn-gray{background:#fff;color:#374151;border:1px solid #d1d5db;}.btn-gray:hover{background:#f9fafb;}
.btn-ghost{background:transparent;color:#6b7280;border:1px solid #e5e7eb;}.btn-ghost:hover{background:#fff;}
.btn-sm{padding:5px 11px;font-size:12px;}

/* Badges */
.status-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;font-size:11.5px;font-weight:500;}
.status-active{background:#dcfce7;color:#15803d;}
.status-inactive{background:#f3f4f6;color:#6b7280;}
.tag{display:inline-flex;padding:2px 8px;border-radius:5px;font-size:11.5px;font-weight:500;}
.tag-blue{background:#eff6ff;color:#2563eb;}
.tag-green{background:#dcfce7;color:#15803d;}
.tag-orange{background:#fff7ed;color:#ea580c;}
.tag-red{background:#fef2f2;color:#dc2626;}
.tag-gray{background:#f3f4f6;color:#6b7280;}

/* Cards */
.card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;}
.card-head{padding:13px 16px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;gap:10px;}
.card-title{font-size:13px;font-weight:600;}
.card-body{padding:16px;}
.card-body-flush{padding:0;}

/* Stats */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;}
.stat-box{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;}
.stat-label{font-size:11.5px;color:#6b7280;margin-bottom:6px;}
.stat-value{font-size:20px;font-weight:700;line-height:1.1;}
.stat-sub{font-size:11px;color:#9ca3af;margin-top:3px;}

/* Layout */
.layout{display:grid;grid-template-columns:300px 1fr;gap:14px;}
.col{display:flex;flex-direction:column;gap:14px;}

/* Info list */
.info-row{display:flex;align-items:flex-start;padding:9px 0;border-bottom:1px solid #f3f4f6;}
.info-row:last-child{border-bottom:none;}
.info-key{width:100px;flex-shrink:0;font-size:12px;color:#6b7280;padding-top:1px;}
.info-val{flex:1;font-size:13px;font-weight:500;}

/* Filter bar */
.filter-bar{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;margin-bottom:14px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;}
.fg{flex:1;min-width:120px;}
.fl{font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;}
.fs,.fi{width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:7px;font-size:12.5px;font-family:inherit;background:#fff;}
.fs:focus,.fi:focus{outline:none;border-color:#2563eb;}
.fbtn{padding:7px 16px;background:#2563eb;color:#fff;border:none;border-radius:7px;font-size:12.5px;font-weight:500;cursor:pointer;font-family:inherit;}
.fbtn:hover{background:#1d4ed8;}
.fbtn.reset{background:#f3f4f6;color:#374151;}.fbtn.reset:hover{background:#e5e7eb;}

/* Table */
.tbl{width:100%;border-collapse:collapse;}
.tbl thead th{padding:9px 12px;text-align:left;font-size:11.5px;font-weight:600;color:#6b7280;background:#f9fafb;border-bottom:1px solid #e5e7eb;white-space:nowrap;}
.tbl tbody td{padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:12.5px;vertical-align:middle;}
.tbl tbody tr:last-child td{border-bottom:none;}
.tbl tbody tr:hover td{background:#fafafa;}

/* Pagination */
.pgn{padding:10px 14px;background:#f9fafb;border-top:1px solid #e5e7eb;}
.pgn nav{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;}
.pgn nav p{font-size:12px;color:#6b7280;}
.pgn nav .flex{display:flex;gap:3px;}
.pgn nav a,.pgn nav span{display:inline-flex;align-items:center;justify-content:center;padding:4px 9px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;color:#374151;text-decoration:none;min-width:30px;}
.pgn nav a:hover{background:#eff6ff;border-color:#2563eb;color:#2563eb;}
.pgn nav span[aria-current="page"]{background:#2563eb;border-color:#2563eb;color:#fff;}
.pgn nav span.cursor-default{opacity:.4;}

/* Search */
.search-wrap{position:relative;margin-bottom:12px;}
.search-ico{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none;}
.search-inp{width:100%;padding:7px 10px 7px 30px;border:1px solid #d1d5db;border-radius:7px;font-size:12.5px;font-family:inherit;}
.search-inp:focus{outline:none;border-color:#2563eb;}
.search-clr{position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;font-size:14px;line-height:1;display:none;}

/* Empty */
.empty-state{text-align:center;padding:28px 16px;color:#9ca3af;font-size:12.5px;}
.empty-icon{font-size:28px;margin-bottom:6px;}

@media(max-width:1000px){.layout{grid-template-columns:1fr;}}
@media(max-width:580px){.stats-grid{grid-template-columns:1fr;}.pg{padding:14px;}}
</style>
@endpush

@php
    $currYear         = (int)date('Y');
    $currMonth        = (int)date('n');
    $filterYear       = (int)($filters['year'] ?? $currYear);
    $filterMonth      = ($filters['month'] !== '') ? (int)$filters['month'] : 0;
@endphp

<div class="pg">

{{-- TOP BAR --}}
<div class="topbar">
    <div class="pe-identity">
        <div class="pe-avatar">{{ strtoupper(substr($purchaseExecutive->name,0,2)) }}</div>
        <div>
            <div class="pe-name">{{ $purchaseExecutive->name }}</div>
            <div class="pe-meta">
                <span class="status-badge status-{{ $purchaseExecutive->status }}">{{ ucfirst($purchaseExecutive->status) }}</span>
                &nbsp;·&nbsp;Purchase Executive
                &nbsp;·&nbsp;Joined {{ $purchaseExecutive->formatted_joining_date }}
            </div>
        </div>
    </div>
    <div class="topbar-actions">
        <a href="{{ route('admin.purchase-executives.edit', $purchaseExecutive->id) }}" class="btn btn-primary">✏ Edit</a>
        <a href="{{ route('admin.purchase-executives.index') }}" class="btn btn-ghost">← Back</a>
    </div>
</div>

{{-- STATS --}}
<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-label">Purchase Orders</div>
        <div class="stat-value">₹{{ number_format($totalPurchases,2) }}</div>
        <div class="stat-sub">{{ $totalInvoicesCount }} invoices</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Assigned Vendors</div>
        <div class="stat-value">{{ $allVendorsCount }}</div>
        <div class="stat-sub">{{ $vendorCounts['active'] }} active · {{ $vendorCounts['inactive'] }} inactive</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Active Vendors</div>
        <div class="stat-value">{{ $vendorCounts['active'] }}</div>
        <div class="stat-sub">Currently active</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Inactive Vendors</div>
        <div class="stat-value">{{ $vendorCounts['inactive'] }}</div>
        <div class="stat-sub">Currently inactive</div>
    </div>
</div>

{{-- MAIN LAYOUT --}}
<div class="layout">
<div class="col">

    {{-- Personal Info --}}
    <div class="card">
        <div class="card-head"><span class="card-title">Personal Info</span></div>
        <div class="card-body" style="padding-top:6px;padding-bottom:6px;">
            <div class="info-row"><span class="info-key">Name</span><span class="info-val">{{ $purchaseExecutive->name }}</span></div>
            <div class="info-row"><span class="info-key">Phone</span><span class="info-val">{{ $purchaseExecutive->phone }}</span></div>
            <div class="info-row"><span class="info-key">Email</span><span class="info-val" style="{{ $purchaseExecutive->email?'':'color:#9ca3af' }}">{{ $purchaseExecutive->email ?? '—' }}</span></div>
            <div class="info-row"><span class="info-key">Joined</span><span class="info-val">{{ $purchaseExecutive->formatted_joining_date }}</span></div>
            <div class="info-row"><span class="info-key">Status</span><span class="info-val"><span class="status-badge status-{{ $purchaseExecutive->status }}">{{ ucfirst($purchaseExecutive->status) }}</span></span></div>
            <div class="info-row"><span class="info-key">Role</span><span class="info-val"><span class="tag tag-blue">Purchase Executive</span></span></div>
        </div>
    </div>

    @if($purchaseExecutive->notes)
    <div class="card">
        <div class="card-head"><span class="card-title">Notes</span></div>
        <div class="card-body" style="color:#6b7280;line-height:1.65;font-size:12.5px;">{{ $purchaseExecutive->notes }}</div>
    </div>
    @endif

</div>
<div class="col">

    {{-- Filter Bar --}}
    <div class="filter-bar">
        <div class="fg"><div class="fl">Month</div><select class="fs" id="f-month"></select></div>
        <div class="fg"><div class="fl">Year</div>
            <select class="fs" id="f-year" onchange="rebuildMonths()">
                @foreach(range(date('Y'), date('Y')-5) as $y)
                <option value="{{ $y }}" {{ $filterYear===$y?'selected':'' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="fg"><div class="fl">From</div><input type="date" class="fi" id="f-start" value="{{ $filters['start_date']??'' }}"></div>
        <div class="fg"><div class="fl">To</div><input type="date" class="fi" id="f-end" value="{{ $filters['end_date']??'' }}"></div>
        <div style="display:flex;gap:6px;">
            <button class="fbtn" onclick="applyFilters()">Apply</button>
            <button class="fbtn reset" onclick="resetFilters()">Reset</button>
        </div>
    </div>

    {{-- Purchase Invoices --}}
    <div class="card">
        <div class="card-head">
            <span class="card-title">Purchase Invoices</span>
            <span class="tag tag-gray">{{ $totalInvoicesCount }} total</span>
        </div>
        <div class="card-body card-body-flush">
            @if($allInvoices->count())
            <div style="overflow-x:auto;">
                <table class="tbl">
                    <thead><tr><th>Invoice #</th><th>Vendor</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($allInvoices as $inv)
                        <tr>
                            <td><a href="{{ route('admin.purchases.show',$inv->id) }}" style="color:#2563eb;text-decoration:none;font-weight:500">{{ $inv->invoice_number }}</a></td>
                            <td>{{ $inv->supplier->name ?? 'N/A' }}</td>
                            <td style="color:#6b7280;white-space:nowrap">{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') }}</td>
                            <td style="font-weight:600;color:#15803d;white-space:nowrap">₹{{ number_format($inv->grand_total,2) }}</td>
                            <td><span class="tag tag-blue">{{ ucfirst($inv->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($allInvoices->hasPages())
            <div class="pgn">{{ $allInvoices->appends(request()->query())->onEachSide(1)->links() }}</div>
            @endif
            @else
            <div class="empty-state"><div class="empty-icon">🧾</div>No purchase invoices for this period</div>
            @endif
        </div>
    </div>

    {{-- Assigned Vendors --}}
    <div class="card">
        <div class="card-head">
            <span class="card-title">Assigned Vendors</span>
            <span class="tag tag-gray">{{ $allVendorsCount }} total</span>
        </div>
        <div class="card-body">
            <div class="search-wrap">
                <svg class="search-ico" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="search-inp" id="vendor-q" type="text" placeholder="Search name, phone, email…" autocomplete="off">
                <button class="search-clr" id="vendor-clr" onclick="clearSearch()">✕</button>
            </div>
            <div id="vendors-default">
                @if($vendors->count())
                <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                    <table class="tbl">
                        <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>GST</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($vendors as $v)
                            <tr>
                                <td style="font-weight:500">{{ $v->name }}</td>
                                <td style="color:#6b7280">{{ $v->phone ?? '—' }}</td>
                                <td style="color:#6b7280">{{ $v->email ?? '—' }}</td>
                                <td style="color:#6b7280">{{ $v->gst_no ?? '—' }}</td>
                                <td><span class="status-badge status-{{ $v->status }}">{{ ucfirst($v->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($vendors->lastPage() > 1)
                <div class="pgn" style="border:1px solid #e5e7eb;border-top:none;border-radius:0 0 8px 8px;margin-top:-1px;">
                    {{ $vendors->appends(request()->query())->onEachSide(1)->links() }}
                </div>
                @endif
                @else
                <div class="empty-state"><div class="empty-icon">🏢</div>No vendors assigned</div>
                @endif
            </div>
            <div id="vendors-search" style="display:none;">
                <div id="srch-loader" style="text-align:center;padding:18px;display:none;color:#9ca3af;font-size:12px;">Searching…</div>
                <div id="srch-results"></div>
            </div>
        </div>
    </div>

</div>
</div>
</div>

<div class="toast" id="toast"></div>

@push('scripts')
<script>
const EXECUTIVE_ID = '{{ $purchaseExecutive->id }}';
const CSRF         = '{{ csrf_token() }}';
const CURR_YEAR    = {{ $currYear }};
const CURR_MONTH   = {{ $currMonth }};
const FILTER_YEAR  = {{ $filterYear }};
const FILTER_MONTH = {{ $filterMonth }};
const MONTH_NAMES  = ['','January','February','March','April','May','June','July','August','September','October','November','December'];

// ── Month filter (invoice filter) ─────────────────────────────────────────
function rebuildMonths() {
    const yr  = parseInt(document.getElementById('f-year').value, 10);
    const sel = document.getElementById('f-month');
    const prev = parseInt(sel.value, 10) || 0;
    const max  = (yr === CURR_YEAR) ? CURR_MONTH : 12;
    sel.innerHTML = '<option value="">All Months</option>';
    for (let m = 1; m <= max; m++) {
        const o = document.createElement('option');
        o.value = m; o.textContent = MONTH_NAMES[m];
        if (m === prev) o.selected = true;
        sel.appendChild(o);
    }
}

// ── Invoice filters ───────────────────────────────────────────────────────
function applyFilters() {
    const url = new URL(location.href);
    const m = document.getElementById('f-month').value;
    const y = document.getElementById('f-year').value;
    const s = document.getElementById('f-start').value;
    const e = document.getElementById('f-end').value;

    if (s || e) { url.searchParams.delete('month'); url.searchParams.delete('year'); }
    else {
        m ? url.searchParams.set('month', m) : url.searchParams.delete('month');
        y ? url.searchParams.set('year', y)  : url.searchParams.delete('year');
    }
    s ? url.searchParams.set('start_date', s) : url.searchParams.delete('start_date');
    e ? url.searchParams.set('end_date', e)   : url.searchParams.delete('end_date');
    url.searchParams.delete('invoice_page'); url.searchParams.delete('vendor_page');
    location.href = url.toString();
}
function resetFilters() {
    const url = new URL(location.href);
    ['month','year','start_date','end_date','invoice_page','vendor_page'].forEach(k => url.searchParams.delete(k));
    location.href = url.toString();
}

// ── Vendor search ──────────────────────────────────────────────────────────
let searchTimer = null;
const qInput = document.getElementById('vendor-q');
const clrBtn = document.getElementById('vendor-clr');

qInput.addEventListener('input', function () {
    const q = this.value.trim();
    clrBtn.style.display = q ? 'block' : 'none';
    if (!q) { clearSearch(); return; }
    if (q.length < 2) return;
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => doSearch(q), 300);
});

function clearSearch() {
    qInput.value = ''; clrBtn.style.display = 'none';
    document.getElementById('vendors-default').style.display = 'block';
    document.getElementById('vendors-search').style.display  = 'none';
}

function doSearch(q) {
    document.getElementById('vendors-default').style.display = 'none';
    document.getElementById('vendors-search').style.display  = 'block';
    document.getElementById('srch-loader').style.display     = 'block';
    document.getElementById('srch-results').innerHTML        = '';

    fetch(`/admin/purchase-executives/${EXECUTIVE_ID}/search-vendors?q=${encodeURIComponent(q)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('srch-loader').style.display = 'none';
        if (!data || !data.length) {
            document.getElementById('srch-results').innerHTML = '<div class="empty-state"><div class="empty-icon">🔍</div>No vendors found</div>';
            return;
        }
        document.getElementById('srch-results').innerHTML = `
            <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                <table class="tbl"><thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>GST</th><th>Status</th></tr></thead>
                <tbody>${data.map(v=>`<tr>
                    <td style="font-weight:500">${h(v.name)}</td>
                    <td style="color:#6b7280">${h(v.phone||'—')}</td>
                    <td style="color:#6b7280">${h(v.email||'—')}</td>
                    <td style="color:#6b7280">${h(v.gst_no||'—')}</td>
                    <td><span class="status-badge status-${v.status==='active'?'active':'inactive'}">${h(cap(v.status))}</span></td>
                </tr>`).join('')}</tbody></table>
            </div>
            <div style="padding:5px 0;font-size:12px;color:#9ca3af;">${data.length} result${data.length!==1?'s':''}</div>`;
    })
    .catch(() => { document.getElementById('srch-loader').style.display = 'none'; });
}

// ── Helpers ───────────────────────────────────────────────────────────────
function toast(msg, ok = true) {
    const t = document.getElementById('toast');
    t.textContent = (ok ? '✓  ' : '✗  ') + msg;
    t.style.background = ok ? '#15803d' : '#dc2626';
    t.classList.add('show');
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.remove('show'), 3500);
}

function fmt(n) {
    return parseFloat(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function h(s)   { const d = document.createElement('div'); d.textContent = s||''; return d.innerHTML; }
function cap(s) { return s ? s[0].toUpperCase() + s.slice(1) : ''; }

// ── Init ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    rebuildMonths();
    if (FILTER_MONTH > 0) {
        const sel = document.getElementById('f-month');
        if (sel) sel.value = FILTER_MONTH;
    }
});
</script>
@endpush
@endsection
