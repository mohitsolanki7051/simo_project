@extends('layouts.admin')

@section('title', 'Sales Invoices')
@section('header-title', 'Sales')

@section('content')
<div class="si-wrap">
    <div id="alertBox"></div>

    {{-- ── Header ── --}}
    <div class="si-header">
        <div>
            <h1 class="si-title">Sales Invoices</h1>
            <p class="si-sub">Manage and track all your sales</p>
        </div>
        <a href="{{ route('admin.sales.create') }}" class="si-btn-create">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Invoice
        </a>
    </div>

    {{-- ── Stats ── --}}
    <div class="si-stats">
        <div class="si-stat si-stat--green">
            <div class="si-stat-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div>
                <div class="si-stat-label">Total Sales</div>
                <div class="si-stat-value">₹ {{ number_format($totalSales, 2) }}</div>
            </div>
        </div>
        <div class="si-stat si-stat--blue">
            <div class="si-stat-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div class="si-stat-label">Collected</div>
                <div class="si-stat-value">₹ {{ number_format($totalPaid, 2) }}</div>
            </div>
        </div>
        <div class="si-stat si-stat--red">
            <div class="si-stat-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div>
                <div class="si-stat-label">Outstanding</div>
                <div class="si-stat-value">₹ {{ number_format($totalUnpaid, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="si-filters">
        <form method="GET" action="{{ route('admin.sales.index') }}" id="filterForm">
            <div class="si-filter-row">

                {{-- Period Dropdown --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Period</label>
                    <select name="period" class="si-select" id="periodSelect" onchange="handlePeriodChange(this)">
                        <option value="">All Time</option>
                        <option value="today"  {{ request('period') == 'today'  ? 'selected' : '' }}>Today</option>
                        <option value="7"      {{ request('period') == '7'      ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30"     {{ request('period') == '30'     ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90"     {{ request('period') == '90'     ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="365"    {{ request('period') == '365'    ? 'selected' : '' }}>Last 365 Days</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                {{-- Single Date (shown when no period selected) --}}
                <div class="si-filter-group" id="grpSingleDate"
                     style="{{ request()->filled('period') ? 'display:none' : '' }}">
                    <label class="si-filter-label">Date</label>
                    <input type="date" name="date" class="si-input" value="{{ request('date') }}">
                </div>

                {{-- Custom Range --}}
                <div class="si-filter-group" id="grpDateFrom"
                     style="{{ request('period') == 'custom' ? '' : 'display:none' }}">
                    <label class="si-filter-label">From</label>
                    <input type="date" name="date_from" class="si-input" value="{{ request('date_from') }}">
                </div>
                <div class="si-filter-group" id="grpDateTo"
                     style="{{ request('period') == 'custom' ? '' : 'display:none' }}">
                    <label class="si-filter-label">To</label>
                    <input type="date" name="date_to" class="si-input" value="{{ request('date_to') }}">
                </div>

                {{-- Invoice Number --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Invoice #</label>
                    <input type="text" name="invoice_number" class="si-input"
                           placeholder="Search..." value="{{ request('invoice_number') }}">
                </div>

                {{-- Party --}}
                <div class="si-filter-group" style="min-width:160px;">
                    <label class="si-filter-label">Party</label>
                    <select name="party_id" class="si-select">
                        <option value="">All Parties</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->_id }}"
                                    {{ request('party_id') == $customer->_id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Status</label>
                    <select name="status" class="si-select">
                        <option value="">All</option>
                        <option value="paid"    {{ request('status') == 'paid'    ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid"  {{ request('status') == 'unpaid'  ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="si-filter-btns">
                    <button type="submit" class="si-btn-filter">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.sales.index') }}" class="si-btn-reset">Reset</a>
                </div>

            </div>
        </form>
    </div>

    {{-- ── Table ── --}}
    <div class="si-table-card">

        <div class="si-table-topbar">
            <div class="si-table-count">
                <strong>{{ $invoices->total() }}</strong> invoice{{ $invoices->total() != 1 ? 's' : '' }}
                @if(request()->anyFilled(['date','invoice_number','party_id','status','period','date_from','date_to']))
                    <span class="si-filtered-pill">Filtered</span>
                @endif
            </div>
            <div class="si-page-info-top">
                Showing {{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }}
                of {{ $invoices->total() }}
            </div>
        </div>

        <div class="si-table-wrap">
            <table class="si-table">
                <thead>
                    <tr>
                        <th class="tc-no">#</th>
                        <th class="tc-date">Date</th>
                        <th class="tc-inv">Invoice No.</th>
                        <th class="tc-party">Party</th>
                        <th class="tc-due">Due</th>
                        <th class="tc-amt">Grand Total</th>
                        <th class="tc-paid">Paid</th>
                        <th class="tc-bal">Balance</th>
                        <th class="tc-status">Status</th>
                        <th class="tc-act">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $i => $invoice)
                    <tr>
                        <td class="tc-no td-muted">
                            {{ ($invoices->currentPage() - 1) * $invoices->perPage() + $i + 1 }}
                        </td>
                        <td class="tc-date">
                            <div class="td-date-main">
                                {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}
                            </div>
                        </td>
                        <td class="tc-inv">
                            <span class="si-inv-chip">{{ $invoice->invoice_number }}</span>
                        </td>
                        <td class="tc-party">
                            @php
                                $partyName = optional($invoice->party)->name ?? 'N/A';
                                $partyType = optional($invoice->party)->party_type ?? '';
                            @endphp
                            <div class="td-party-name">{{ $partyName }}</div>
                            @if($partyType)
                                <span class="si-ptype si-ptype--{{ $partyType }}">{{ ucfirst($partyType) }}</span>
                            @endif
                        </td>
                        <td class="tc-due">
                            @if($invoice->due_date)
                                @php
                                    $due  = \Carbon\Carbon::parse($invoice->due_date);
                                    $today = \Carbon\Carbon::today();
                                    $diff  = (int) $today->diffInDays($due, false);
                                    if ($invoice->payment_status === 'paid') {
                                        $cls = 'si-due--paid'; $txt = 'Paid';
                                    } elseif ($diff < 0) {
                                        $cls = 'si-due--over'; $txt = abs($diff).'d over';
                                    } elseif ($diff === 0) {
                                        $cls = 'si-due--today'; $txt = 'Today';
                                    } else {
                                        $cls = 'si-due--ok'; $txt = $diff.'d left';
                                    }
                                @endphp
                                <span class="si-due {{ $cls }}">{{ $txt }}</span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td class="tc-amt">
                            <span class="td-bold">₹ {{ number_format($invoice->grand_total, 2) }}</span>
                        </td>
                        <td class="tc-paid">
                            <span class="td-green">₹ {{ number_format($invoice->total_paid ?? 0, 2) }}</span>
                        </td>
                        <td class="tc-bal">
                            @php $bal = (float)($invoice->balance_amount ?? 0); @endphp
                            <span class="{{ $bal > 0 ? 'td-red' : 'td-muted' }}">
                                ₹ {{ number_format($bal, 2) }}
                            </span>
                        </td>
                        <td class="tc-status">
                            <span class="si-badge si-badge--{{ $invoice->payment_status }}">
                                {{ ucfirst($invoice->payment_status) }}
                            </span>
                        </td>
                        <td class="tc-act">
                            <div class="si-act-grp">
                                <a href="{{ route('admin.sales.show', $invoice->_id) }}"
                                   class="si-act si-act--view" title="View">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                               
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="si-empty-cell">
                            <div class="si-empty">
                                <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                                <p>No invoices found</p>
                                <a href="{{ route('admin.sales.create') }}" class="si-btn-create" style="font-size:10px;padding:5px 12px;">
                                    + Create Invoice
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── Pagination ── --}}
        @if($invoices->hasPages())
        <div class="si-pagination">
            <div class="si-page-info">
                Page {{ $invoices->currentPage() }} of {{ $invoices->lastPage() }}
                &nbsp;·&nbsp;
                {{ $invoices->total() }} total records
            </div>
            <div class="si-pages">

                {{-- First & Prev --}}
                @if($invoices->onFirstPage())
                    <span class="si-pg si-pg--dis">«</span>
                    <span class="si-pg si-pg--dis">‹</span>
                @else
                    <a href="{{ $invoices->url(1) }}" class="si-pg" title="First">«</a>
                    <a href="{{ $invoices->previousPageUrl() }}" class="si-pg" title="Previous">‹</a>
                @endif

                {{-- Page Numbers --}}
                @php
                    $cur   = $invoices->currentPage();
                    $last  = $invoices->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $invoices->url(1) }}" class="si-pg">1</a>
                    @if($start > 2) <span class="si-pg-dots">…</span> @endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="si-pg si-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $invoices->url($p) }}" class="si-pg">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $last)
                    @if($end < $last - 1) <span class="si-pg-dots">…</span> @endif
                    <a href="{{ $invoices->url($last) }}" class="si-pg">{{ $last }}</a>
                @endif

                {{-- Next & Last --}}
                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="si-pg" title="Next">›</a>
                    <a href="{{ $invoices->url($last) }}" class="si-pg" title="Last">»</a>
                @else
                    <span class="si-pg si-pg--dis">›</span>
                    <span class="si-pg si-pg--dis">»</span>
                @endif

            </div>
        </div>
        @endif

    </div>{{-- /.si-table-card --}}
</div>{{-- /.si-wrap --}}

{{-- ── Delete Modal ── --}}
<div class="si-modal" id="delModal">
    <div class="si-modal-overlay" onclick="closeDelModal()"></div>
    <div class="si-modal-box">
        <div class="si-modal-head">
            <div class="si-modal-ico">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
            </div>
            <div>
                <div class="si-modal-title">Delete Invoice</div>
                <div class="si-modal-sub">Stock will be automatically restored</div>
            </div>
            <button class="si-modal-close" onclick="closeDelModal()">×</button>
        </div>
        <div class="si-modal-body">
            <p>Are you sure you want to permanently delete this invoice? This action cannot be undone.</p>
        </div>
        <div class="si-modal-foot">
            <button class="si-btn-cancel" onclick="closeDelModal()">Cancel</button>
            <button class="si-btn-del-confirm" id="confirmDel">Delete Invoice</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* ── Variables ───────────────────────────────────── */
:root {
    --si-brand:   #f97316;
    --si-brand-d: #ea6c10;
    --si-text:    #1e293b;
    --si-muted:   #64748b;
    --si-border:  #e2e8f0;
    --si-bg:      #f8fafc;
    --si-white:   #ffffff;
    --si-r:       6px;
    --si-shadow:  0 1px 3px rgba(0,0,0,.07);
}

/* ── Layout ──────────────────────────────────────── */
.si-wrap {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 12px;
    color: var(--si-text);
    padding: 14px;
}

/* ── Header ──────────────────────────────────────── */
.si-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--si-border);
}
.si-title { font-size: 17px; font-weight: 700; color: var(--si-text); margin: 0 0 2px; }
.si-sub   { font-size: 11px; color: var(--si-muted); margin: 0; }
.si-btn-create {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 13px;
    background: var(--si-brand);
    color: #fff;
    border: none;
    border-radius: var(--si-r);
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s;
}
.si-btn-create:hover { background: var(--si-brand-d); color: #fff; }

/* ── Stats ───────────────────────────────────────── */
.si-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 14px;
}
.si-stat {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: var(--si-white);
    border: 1px solid var(--si-border);
    border-radius: var(--si-r);
    box-shadow: var(--si-shadow);
}
.si-stat-icon {
    width: 36px; height: 36px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.si-stat--green .si-stat-icon { background: #dcfce7; color: #16a34a; }
.si-stat--blue  .si-stat-icon { background: #dbeafe; color: #2563eb; }
.si-stat--red   .si-stat-icon { background: #fee2e2; color: #dc2626; }
.si-stat-label { font-size: 10px; color: var(--si-muted); font-weight: 500; margin-bottom: 3px; }
.si-stat-value { font-size: 15px; font-weight: 700; color: var(--si-text); }

/* ── Filters ─────────────────────────────────────── */
.si-filters {
    background: var(--si-white);
    border: 1px solid var(--si-border);
    border-radius: var(--si-r);
    padding: 11px 13px;
    margin-bottom: 12px;
    box-shadow: var(--si-shadow);
}
.si-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-end;
}
.si-filter-group {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 120px;
}
.si-filter-label {
    font-size: 9px;
    font-weight: 700;
    color: var(--si-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.si-input, .si-select {
    height: 30px;
    padding: 0 8px;
    border: 1px solid var(--si-border);
    border-radius: 4px;
    font-size: 11px;
    color: var(--si-text);
    background: var(--si-bg);
    transition: border-color .15s;
}
.si-input:focus, .si-select:focus {
    outline: none;
    border-color: var(--si-brand);
    background: var(--si-white);
}
.si-filter-btns {
    display: flex;
    gap: 6px;
    align-items: flex-end;
}
.si-btn-filter {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    height: 30px;
    padding: 0 12px;
    background: var(--si-text);
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.si-btn-filter:hover { background: #0f172a; }
.si-btn-reset {
    display: inline-flex;
    align-items: center;
    height: 30px;
    padding: 0 12px;
    background: var(--si-bg);
    color: var(--si-muted);
    border: 1px solid var(--si-border);
    border-radius: 4px;
    font-size: 11px;
    text-decoration: none;
    transition: background .15s;
}
.si-btn-reset:hover { background: #e2e8f0; color: var(--si-text); }

/* ── Table Card ──────────────────────────────────── */
.si-table-card {
    background: var(--si-white);
    border: 1px solid var(--si-border);
    border-radius: var(--si-r);
    box-shadow: var(--si-shadow);
    overflow: hidden;
}
.si-table-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 13px;
    border-bottom: 1px solid var(--si-border);
    background: var(--si-bg);
}
.si-table-count { font-size: 11px; color: var(--si-muted); font-weight: 500; }
.si-table-count strong { color: var(--si-text); }
.si-filtered-pill {
    display: inline-block;
    margin-left: 6px;
    padding: 1px 7px;
    background: #fef3c7;
    color: #92400e;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 700;
}
.si-page-info-top { font-size: 10px; color: var(--si-muted); }

/* ── Table ───────────────────────────────────────── */
.si-table-wrap { overflow-x: auto; }
.si-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
}
.si-table th {
    padding: 8px 10px;
    background: #f1f5f9;
    font-size: 9px;
    font-weight: 700;
    color: var(--si-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
    border-bottom: 1px solid var(--si-border);
    white-space: nowrap;
    text-align: left;
}
.si-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.si-table tr:last-child td { border-bottom: none; }
.si-table tbody tr:hover td { background: #fafafa; }

/* Column widths */
.tc-no     { width: 40px;  text-align: center; }
.tc-date   { width: 90px;  }
.tc-inv    { width: 160px; }
.tc-party  { width: 175px; }
.tc-due    { width: 82px;  }
.tc-amt    { width: 110px; }
.tc-paid   { width: 95px;  }
.tc-bal    { width: 95px;  }
.tc-status { width: 76px;  }
.tc-act    { width: 70px;  }

/* Cell helpers */
.td-muted      { color: var(--si-muted); font-size: 10px; }
.td-bold       { font-weight: 700; color: var(--si-text); }
.td-green      { color: #16a34a; font-weight: 600; }
.td-red        { color: #dc2626; font-weight: 600; }
.td-date-main  { font-size: 11px; color: var(--si-text); white-space: nowrap; }

/* Invoice chip */
.si-inv-chip {
    display: inline-block;
    padding: 3px 7px;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    color: #0369a1;
    white-space: nowrap;
    font-family: monospace;
}

/* Party */
.td-party-name {
    font-weight: 500;
    color: var(--si-text);
    font-size: 11px;
    margin-bottom: 2px;
    max-width: 160px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.si-ptype {
    display: inline-block;
    padding: 1px 5px;
    border-radius: 3px;
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.si-ptype--customer    { background: #d1fae5; color: #065f46; }
.si-ptype--dealer      { background: #dbeafe; color: #1e40af; }
.si-ptype--distributor { background: #fef3c7; color: #92400e; }

/* Due badge */
.si-due {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 9px;
    font-weight: 600;
    white-space: nowrap;
}
.si-due--ok    { background: #d1fae5; color: #065f46; }
.si-due--today { background: #fef3c7; color: #92400e; }
.si-due--over  { background: #fee2e2; color: #991b1b; }
.si-due--paid  { background: #f1f5f9; color: var(--si-muted); }

/* Status badge */
.si-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.si-badge--paid    { background: #d1fae5; color: #065f46; }
.si-badge--unpaid  { background: #fee2e2; color: #991b1b; }
.si-badge--partial { background: #fef3c7; color: #92400e; }

/* Actions */
.si-act-grp { display: flex; gap: 4px; }
.si-act {
    width: 27px; height: 27px;
    border: none;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .15s;
    text-decoration: none;
}
.si-act--view { background: #dbeafe; color: #2563eb; }
.si-act--view:hover { background: #bfdbfe; }
.si-act--del  { background: #fee2e2; color: #dc2626; }
.si-act--del:hover  { background: #fecaca; }

/* Empty */
.si-empty-cell { padding: 48px 20px; text-align: center; }
.si-empty { display: inline-flex; flex-direction: column; align-items: center; gap: 10px; }
.si-empty p { font-size: 12px; color: var(--si-muted); margin: 0; }

/* ── Pagination ───────────────────────────────────── */
.si-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 13px;
    border-top: 1px solid var(--si-border);
    background: var(--si-bg);
}
.si-page-info { font-size: 10px; color: var(--si-muted); }
.si-pages { display: flex; gap: 3px; align-items: center; }
.si-pg {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 27px;
    height: 27px;
    padding: 0 5px;
    border: 1px solid var(--si-border);
    border-radius: 4px;
    font-size: 11px;
    color: var(--si-text);
    text-decoration: none;
    background: var(--si-white);
    transition: all .12s;
    font-weight: 500;
}
.si-pg:hover          { background: #e2e8f0; }
.si-pg--active        { background: var(--si-brand); color: #fff; border-color: var(--si-brand); font-weight: 700; }
.si-pg--active:hover  { background: var(--si-brand); }
.si-pg--dis           { color: #cbd5e1; background: var(--si-bg); cursor: default; pointer-events: none; }
.si-pg-dots           { font-size: 11px; color: var(--si-muted); padding: 0 1px; line-height: 27px; }

/* ── Modal ───────────────────────────────────────── */
.si-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.si-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.4);
}
.si-modal-box {
    position: relative;
    background: var(--si-white);
    border-radius: 8px;
    width: 370px;
    max-width: 92%;
    box-shadow: 0 20px 40px rgba(0,0,0,.14);
    animation: siMIn .2s ease;
}
@keyframes siMIn {
    from { opacity:0; transform: scale(.95) translateY(8px); }
    to   { opacity:1; transform: scale(1) translateY(0); }
}
.si-modal-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 15px;
    border-bottom: 1px solid var(--si-border);
    background: var(--si-bg);
    border-radius: 8px 8px 0 0;
}
.si-modal-ico {
    width: 32px; height: 32px;
    background: #ef4444;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.si-modal-title { font-size: 13px; font-weight: 700; margin-bottom: 1px; }
.si-modal-sub   { font-size: 10px; color: var(--si-muted); }
.si-modal-close {
    margin-left: auto;
    background: none;
    border: none;
    font-size: 20px;
    color: var(--si-muted);
    cursor: pointer;
    width: 26px; height: 26px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 4px;
    line-height: 1;
}
.si-modal-close:hover { background: #e2e8f0; color: var(--si-text); }
.si-modal-body { padding: 15px; }
.si-modal-body p { font-size: 12px; color: #4b5563; line-height: 1.5; margin: 0; }
.si-modal-foot {
    display: flex;
    justify-content: flex-end;
    gap: 7px;
    padding: 12px 15px;
    border-top: 1px solid var(--si-border);
    background: #fafafa;
    border-radius: 0 0 8px 8px;
}
.si-btn-cancel {
    padding: 6px 14px;
    border: 1px solid var(--si-border);
    border-radius: 4px;
    background: var(--si-bg);
    color: var(--si-text);
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.si-btn-cancel:hover { background: #e2e8f0; }
.si-btn-del-confirm {
    padding: 6px 14px;
    border: none;
    border-radius: 4px;
    background: #ef4444;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.si-btn-del-confirm:hover { background: #dc2626; }
.si-btn-del-confirm:disabled { opacity: .6; cursor: not-allowed; }

/* ── Alerts ──────────────────────────────────────── */
#alertBox { position: fixed; top: 15px; right: 15px; z-index: 9999; }
.si-alert {
    padding: 9px 13px;
    margin-bottom: 7px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 500;
    box-shadow: 0 3px 10px rgba(0,0,0,.1);
    animation: siAIn .25s ease;
}
@keyframes siAIn {
    from { transform: translateX(110%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.si-alert-success { background: #d1fae5; color: #065f46; border-left: 3px solid #16a34a; }
.si-alert-error   { background: #fee2e2; color: #991b1b; border-left: 3px solid #ef4444; }

/* ── Responsive ──────────────────────────────────── */
@media (max-width: 900px) {
    .si-stats { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .si-header { flex-direction: column; align-items: flex-start; gap: 8px; }
    .si-filter-group { min-width: 100%; }
    .si-pagination { flex-direction: column; gap: 8px; }
}
</style>
@endpush

@push('scripts')
<script>
// ── Period filter toggle
function handlePeriodChange(sel) {
    const v = sel.value;
    document.getElementById('grpSingleDate').style.display = (!v) ? '' : 'none';
    document.getElementById('grpDateFrom').style.display   = (v === 'custom') ? '' : 'none';
    document.getElementById('grpDateTo').style.display     = (v === 'custom') ? '' : 'none';
}

// ── Delete
let delId = null;

document.querySelectorAll('.del-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        delId = this.dataset.id;
        document.getElementById('delModal').style.display = 'flex';
    });
});

function closeDelModal() {
    document.getElementById('delModal').style.display = 'none';
    delId = null;
}

document.getElementById('confirmDel').addEventListener('click', function () {
    if (!delId) return;
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Deleting…';

    fetch('/admin/sales/' + delId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        closeDelModal();
        if (data.success) {
            showAlert('Invoice deleted successfully', 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showAlert(data.message || 'Failed to delete', 'error');
            btn.disabled = false;
            btn.textContent = 'Delete Invoice';
        }
    })
    .catch(() => {
        closeDelModal();
        showAlert('Something went wrong', 'error');
        btn.disabled = false;
        btn.textContent = 'Delete Invoice';
    });
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeDelModal();
});

function showAlert(msg, type = 'success') {
    const box = document.getElementById('alertBox');
    const el  = document.createElement('div');
    el.className   = 'si-alert si-alert-' + type;
    el.textContent = msg;
    box.appendChild(el);
    setTimeout(() => el.remove(), 4500);
}
</script>
@endpush
