@extends('layouts.admin')

@section('title', 'Sales Returns')
@section('header-title', 'Sales Returns')

@section('content')
<div class="sr-wrap">
    <div id="alertBox"></div>

    {{-- ── Header ── --}}
    <div class="sr-header">
        <div class="sr-header-left">
            <div class="sr-header-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4v16h16M8 11l4-4 4 4M16 8l-4 4-4-4"/>
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9 16h6"/>
                </svg>
            </div>
            <div>
                <h1 class="sr-title">Sales Returns</h1>
                <p class="sr-sub">Manage and track all returned items</p>
            </div>
        </div>
        <a href="{{ route('admin.sales-returns.create') }}" class="sr-btn-create">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New Return
        </a>
    </div>

    {{-- ── Stats ── --}}
    <div class="sr-stats">
        <div class="sr-stat sr-stat--orange">
            <div class="sr-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <div class="sr-stat-content">
                <div class="sr-stat-label">Total Returns</div>
                <div class="sr-stat-value">{{ $totalReturns }}</div>
                <div class="sr-stat-hint">Completed returns</div>
            </div>
        </div>
        <div class="sr-stat sr-stat--green">
            <div class="sr-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </div>
            <div class="sr-stat-content">
                <div class="sr-stat-label">Return Amount</div>
                <div class="sr-stat-value">₹ {{ number_format($totalReturnAmount, 2) }}</div>
                <div class="sr-stat-hint">Total value returned</div>
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="sr-filters">
        <div class="sr-filters-header">
            <div class="sr-filters-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Filters
            </div>
            @if(request()->anyFilled(['date_from','date_to','return_number','invoice_number','party_id','status']))
            <a href="{{ route('admin.sales-returns.index') }}" class="sr-clear-filters">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Clear all filters
            </a>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.sales-returns.index') }}" id="filterForm">
            <div class="sr-filter-row">

                {{-- Date From --}}
                <div class="sr-filter-group">
                    <label class="sr-filter-label">From Date</label>
                    <input type="date" name="date_from" class="sr-input" value="{{ request('date_from') }}">
                </div>

                {{-- Date To --}}
                <div class="sr-filter-group">
                    <label class="sr-filter-label">To Date</label>
                    <input type="date" name="date_to" class="sr-input" value="{{ request('date_to') }}">
                </div>

                {{-- Return Number --}}
                <div class="sr-filter-group">
                    <label class="sr-filter-label">Return Number</label>
                    <div class="sr-input-icon-wrap">
                        <svg class="sr-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" name="return_number" class="sr-input sr-input-with-icon"
                               placeholder="Search return..." value="{{ request('return_number') }}">
                    </div>
                </div>

                {{-- Invoice Number --}}
                <div class="sr-filter-group">
                    <label class="sr-filter-label">Invoice Number</label>
                    <div class="sr-input-icon-wrap">
                        <svg class="sr-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <input type="text" name="invoice_number" class="sr-input sr-input-with-icon"
                               placeholder="Search invoice..." value="{{ request('invoice_number') }}">
                    </div>
                </div>

                {{-- Party --}}
                <div class="sr-filter-group">
                    <label class="sr-filter-label">Party</label>
                    <select name="party_id" class="sr-select">
                        <option value="">All Parties</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->_id }}" {{ request('party_id') == $party->_id ? 'selected' : '' }}>
                                {{ $party->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="sr-filter-group">
                    <label class="sr-filter-label">Status</label>
                    <select name="status" class="sr-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="sr-filter-btns">
                    <button type="submit" class="sr-btn-filter">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Apply
                    </button>
                    <a href="{{ route('admin.sales-returns.index') }}" class="sr-btn-reset">Reset</a>
                </div>

            </div>
        </form>
    </div>

    {{-- ── Table ── --}}
    <div class="sr-table-card">

        <div class="sr-table-topbar">
            <div class="sr-table-count">
                <strong>{{ $returns->total() }}</strong> return{{ $returns->total() != 1 ? 's' : '' }}
                @if(request()->anyFilled(['date_from','date_to','return_number','invoice_number','party_id','status']))
                    <span class="sr-filtered-pill">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                        </svg>
                        Filtered
                    </span>
                @endif
            </div>
            <div class="sr-table-topbar-right">
                <div class="sr-page-info-top">
                    Showing {{ $returns->firstItem() ?? 0 }}–{{ $returns->lastItem() ?? 0 }} of {{ $returns->total() }}
                </div>
            </div>
        </div>

        <div class="sr-table-wrap">
            <table class="sr-table">
                <thead>
                    <tr>
                        <th class="tc-no">S. No.</th>
                        <th class="tc-date">Return Date</th>
                        <th class="tc-return">Return No.</th>
                        <th class="tc-invoice">Invoice No.</th>
                        <th class="tc-party">Party</th>
                        <th class="tc-wh">Warehouse</th>
                        <th class="tc-qty">Qty</th>
                        <th class="tc-amount">Amount (₹)</th>
                        <th class="tc-credit">Credit Note</th>
                        <th class="tc-status">Status</th>
                        <th class="tc-act">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $i => $return)
                    <tr class="sr-tr">
                        <td class="tc-no">
                            <span class="td-serial">{{ ($returns->currentPage() - 1) * $returns->perPage() + $i + 1 }}</span>
                        </td>
                        <td class="tc-date">
                            <div class="td-date-main">{{ \Carbon\Carbon::parse($return->return_date)->format('d M Y') }}</div>
                            <div class="td-date-sub">{{ \Carbon\Carbon::parse($return->return_date)->format('D') }}</div>
                        </td>
                        <td class="tc-return">
                            <span class="sr-return-chip">{{ $return->return_number }}</span>
                        </td>
                        <td class="tc-invoice">
                            <a href="{{ route('admin.sales.show', $return->sales_invoice_id) }}" class="sr-invoice-link">
                                {{ $return->invoice?->invoice_number ?? 'N/A' }}
                            </a>
                        </td>
                        <td class="tc-party">
                            @php
                                $partyName = optional($return->party)->name ?? 'N/A';
                                $partyType = optional($return->party)->party_type ?? '';
                            @endphp
                            <div class="td-party-name" title="{{ $partyName }}">{{ $partyName }}</div>
                            @if($partyType)
                                <span class="sr-ptype sr-ptype--{{ $partyType }}">{{ ucfirst($partyType) }}</span>
                            @endif
                        </td>
                        <td class="tc-wh">
                            <span class="sr-wh-badge">
                                {{ optional($return->warehouse)->name ?? '—' }}
                            </span>
                        </td>
                        <td class="tc-qty">
                            <span class="sr-qty-badge">{{ $return->total_return_qty }}</span>
                        </td>
                        <td class="tc-amount">
                            <span class="amount-main">₹ {{ number_format($return->total_return_amount, 2) }}</span>
                        </td>
                        <td class="tc-credit">
                            @if($return->creditNote)
                                <a href="{{ route('admin.credit-notes.show', $return->creditNote->_id) }}" class="sr-credit-link">
                                    {{ $return->creditNote->credit_note_number }}
                                </a>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td class="tc-status">
                            @php
                                $statusMap = [
                                    'draft'     => ['cls' => 'sr-status--draft', 'label' => 'Draft'],
                                    'completed' => ['cls' => 'sr-status--completed', 'label' => 'Completed'],
                                    'cancelled' => ['cls' => 'sr-status--cancelled', 'label' => 'Cancelled'],
                                ];
                                $sm = $statusMap[$return->status] ?? $statusMap['draft'];
                            @endphp
                            <span class="sr-status {{ $sm['cls'] }}">{{ $sm['label'] }}</span>
                        </td>
                        <td class="tc-act">
                            <div class="sr-act-grp">
                                <a href="{{ route('admin.sales-returns.show', $return->_id) }}"
                                   class="sr-act sr-act--view" title="View Return">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>

                                @if($return->status === 'draft')
                                    <a href="{{ route('admin.sales-returns.edit', $return->_id) }}"
                                       class="sr-act sr-act--edit" title="Edit Return">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>
                                    <button type="button" onclick="completeReturn('{{ $return->_id }}')"
                                            class="sr-act sr-act--complete" title="Complete Return">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                    </button>
                                    <button type="button" onclick="deleteReturn('{{ $return->_id }}')"
                                            class="sr-act sr-act--del" title="Delete Return">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="sr-empty-cell">
                            <div class="sr-empty">
                                <div class="sr-empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M4 4v16h16M8 11l4-4 4 4M16 8l-4 4-4-4"/>
                                        <circle cx="12" cy="12" r="10"/>
                                    </svg>
                                </div>
                                <p class="sr-empty-title">No returns found</p>
                                <p class="sr-empty-sub">
                                    @if(request()->anyFilled(['date_from','date_to','return_number','invoice_number','party_id','status']))
                                        Try adjusting your filters or <a href="{{ route('admin.sales-returns.index') }}">clear all</a>
                                    @else
                                        Get started by creating your first return
                                    @endif
                                </p>
                                <a href="{{ route('admin.sales-returns.create') }}" class="sr-btn-create" style="margin-top:4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    Create Return
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($returns->hasPages())
        <div class="sr-pagination">
            <div class="sr-page-info">
                Page <strong>{{ $returns->currentPage() }}</strong> of <strong>{{ $returns->lastPage() }}</strong>
                &nbsp;·&nbsp; {{ $returns->total() }} total records
            </div>
            <div class="sr-pages">
                @if($returns->onFirstPage())
                    <span class="sr-pg sr-pg--dis">«</span>
                    <span class="sr-pg sr-pg--dis">‹</span>
                @else
                    <a href="{{ $returns->url(1) }}" class="sr-pg" title="First">«</a>
                    <a href="{{ $returns->previousPageUrl() }}" class="sr-pg" title="Previous">‹</a>
                @endif

                @php
                    $cur   = $returns->currentPage();
                    $last  = $returns->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $returns->url(1) }}" class="sr-pg">1</a>
                    @if($start > 2) <span class="sr-pg-dots">…</span> @endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="sr-pg sr-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $returns->url($p) }}" class="sr-pg">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $last)
                    @if($end < $last - 1) <span class="sr-pg-dots">…</span> @endif
                    <a href="{{ $returns->url($last) }}" class="sr-pg">{{ $last }}</a>
                @endif

                @if($returns->hasMorePages())
                    <a href="{{ $returns->nextPageUrl() }}" class="sr-pg" title="Next">›</a>
                    <a href="{{ $returns->url($last) }}" class="sr-pg" title="Last">»</a>
                @else
                    <span class="sr-pg sr-pg--dis">›</span>
                    <span class="sr-pg sr-pg--dis">»</span>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

@push('styles')
<style>
/* ─── Variables ───────────────────────────────────────────── */
:root {
    --c-brand:   #f97316;
    --c-brand-d: #ea6c10;
    --c-brand-l: #fff7ed;
    --c-text:    #111827;
    --c-text2:   #374151;
    --c-muted:   #6b7280;
    --c-border:  #e5e7eb;
    --c-bg:      #f9fafb;
    --c-white:   #ffffff;
    --c-shadow:  0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
    --c-shadow2: 0 4px 12px rgba(0,0,0,.08);
    --r:         7px;
    --r-sm:      5px;
}

/* ─── Wrap ────────────────────────────────────────────────── */
.sr-wrap {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 12.5px;
    color: var(--c-text);
    padding: 16px;
    max-width: 100%;
}

/* ─── Header ──────────────────────────────────────────────── */
.sr-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--c-border);
    gap: 12px;
    flex-wrap: wrap;
}
.sr-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
}
.sr-header-icon {
    width: 38px; height: 38px;
    background: var(--c-brand-l);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    color: var(--c-brand);
    flex-shrink: 0;
}
.sr-title { font-size: 17px; font-weight: 700; margin: 0 0 2px; letter-spacing: -.3px; }
.sr-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }

.sr-btn-create {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 15px;
    background: var(--c-brand);
    color: #fff;
    border: none;
    border-radius: var(--r-sm);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s, transform .1s, box-shadow .15s;
    box-shadow: 0 2px 8px rgba(249,115,22,.3);
    white-space: nowrap;
}
.sr-btn-create:hover {
    background: var(--c-brand-d);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(249,115,22,.35);
}

/* ─── Stats ───────────────────────────────────────────────── */
.sr-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 14px;
}
.sr-stat {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    box-shadow: var(--c-shadow);
    position: relative;
    overflow: hidden;
    transition: box-shadow .2s, transform .15s;
}
.sr-stat:hover {
    box-shadow: var(--c-shadow2);
    transform: translateY(-1px);
}
.sr-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: var(--r) var(--r) 0 0;
}
.sr-stat--orange::before { background: #f97316; }
.sr-stat--green::before { background: #22c55e; }

.sr-stat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sr-stat--orange .sr-stat-icon { background: #fff7ed; color: #f97316; }
.sr-stat--green .sr-stat-icon { background: #dcfce7; color: #16a34a; }

.sr-stat-label { font-size: 10.5px; color: var(--c-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
.sr-stat-value { font-size: 17px; font-weight: 800; color: var(--c-text); letter-spacing: -.4px; margin-bottom: 2px; }
.sr-stat-hint  { font-size: 10px; color: var(--c-muted); }

/* ─── Filters ─────────────────────────────────────────────── */
.sr-filters {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    padding: 11px 14px 13px;
    margin-bottom: 12px;
    box-shadow: var(--c-shadow);
}
.sr-filters-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.sr-filters-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--c-text2);
    display: flex;
    align-items: center;
    gap: 5px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.sr-clear-filters {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    color: var(--c-brand);
    text-decoration: none;
    font-weight: 500;
    transition: color .15s;
}
.sr-clear-filters:hover { color: var(--c-brand-d); text-decoration: underline; }

.sr-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-end;
}
.sr-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 110px;
}
.sr-filter-label {
    font-size: 9.5px;
    font-weight: 700;
    color: var(--c-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.sr-input, .sr-select {
    height: 31px;
    padding: 0 9px;
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    font-size: 11.5px;
    color: var(--c-text);
    background: var(--c-bg);
    transition: border-color .15s, background .15s, box-shadow .15s;
    outline: none;
}
.sr-input:focus, .sr-select:focus {
    border-color: var(--c-brand);
    background: var(--c-white);
    box-shadow: 0 0 0 3px rgba(249,115,22,.1);
}
.sr-input-icon-wrap { position: relative; }
.sr-input-icon {
    position: absolute;
    left: 9px; top: 50%;
    transform: translateY(-50%);
    color: var(--c-muted);
    pointer-events: none;
}
.sr-input-with-icon { padding-left: 28px; }

.sr-filter-btns {
    display: flex;
    gap: 6px;
    align-items: flex-end;
}
.sr-btn-filter {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 31px;
    padding: 0 14px;
    background: var(--c-text);
    color: #fff;
    border: none;
    border-radius: var(--r-sm);
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.sr-btn-filter:hover { background: #1f2937; }
.sr-btn-reset {
    display: inline-flex;
    align-items: center;
    height: 31px;
    padding: 0 12px;
    background: var(--c-bg);
    color: var(--c-muted);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    font-size: 11.5px;
    text-decoration: none;
    transition: all .15s;
    font-weight: 500;
}
.sr-btn-reset:hover { background: #f3f4f6; color: var(--c-text); }

/* ─── Table Card ──────────────────────────────────────────── */
.sr-table-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    box-shadow: var(--c-shadow);
    overflow: hidden;
}
.sr-table-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-bottom: 1px solid var(--c-border);
    background: var(--c-bg);
    flex-wrap: wrap;
    gap: 6px;
}
.sr-table-count {
    font-size: 11.5px;
    color: var(--c-muted);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}
.sr-table-count strong { color: var(--c-text); font-weight: 700; }
.sr-filtered-pill {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 7px;
    background: #fff7ed;
    color: #92400e;
    border: 1px solid #fed7aa;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.sr-page-info-top { font-size: 10.5px; color: var(--c-muted); }
.sr-table-topbar-right { display: flex; align-items: center; gap: 8px; }

/* ─── Table ───────────────────────────────────────────────── */
.sr-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.sr-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 1200px;
}
.sr-table th {
    padding: 9px 11px;
    background: #f3f4f6;
    font-size: 9.5px;
    font-weight: 700;
    color: var(--c-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
    border-bottom: 1px solid var(--c-border);
    white-space: nowrap;
    text-align: left;
    position: sticky;
    top: 0;
    z-index: 1;
}
.sr-table td {
    padding: 9px 11px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    color: var(--c-text2);
}
.sr-table tr:last-child td { border-bottom: none; }
.sr-tr { transition: background .12s; }
.sr-tr:hover td { background: #fafafa; }

/* Column widths */
.tc-no        { width: 45px;  text-align: center; }
.tc-date      { width: 88px;  }
.tc-return    { width: 160px; }
.tc-invoice   { width: 140px; }
.tc-party     { width: 170px; }
.tc-wh        { width: 100px; }
.tc-qty       { width: 60px;  }
.tc-amount    { width: 100px; }
.tc-credit    { width: 140px; }
.tc-status    { width: 90px;  }
.tc-act       { width: 90px;  }

/* Table cell helpers */
.td-serial   { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--c-bg); border-radius: 4px; font-size: 10px; color: var(--c-muted); font-weight: 600; }
.td-muted    { color: var(--c-muted); font-size: 10.5px; }
.td-date-main{ font-size: 11.5px; color: var(--c-text2); white-space: nowrap; font-weight: 500; }
.td-date-sub { font-size: 9.5px; color: var(--c-muted); }

/* Return chip */
.sr-return-chip {
    display: inline-block;
    padding: 3px 8px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 600;
    color: #9a3412;
    white-space: nowrap;
    font-family: 'Courier New', monospace;
    letter-spacing: .2px;
}

/* Invoice link */
.sr-invoice-link {
    color: var(--c-brand);
    text-decoration: none;
    font-weight: 500;
    font-size: 11.5px;
}
.sr-invoice-link:hover { text-decoration: underline; }

/* Party */
.td-party-name {
    font-weight: 500;
    color: var(--c-text);
    font-size: 12px;
    margin-bottom: 2px;
    max-width: 155px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sr-ptype {
    display: inline-block;
    padding: 1px 5px;
    border-radius: 3px;
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.sr-ptype--customer    { background: #d1fae5; color: #065f46; }
.sr-ptype--dealer      { background: #dbeafe; color: #1e40af; }
.sr-ptype--distributor { background: #fef3c7; color: #92400e; }

/* Warehouse badge */
.sr-wh-badge {
    display: inline-block;
    padding: 2px 7px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    color: #166534;
    white-space: nowrap;
}

/* Qty badge */
.sr-qty-badge {
    display: inline-block;
    padding: 2px 8px;
    background: #f3f4f6;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 600;
    color: var(--c-text2);
    white-space: nowrap;
}

/* Amount */
.amount-main {
    font-weight: 700;
    color: var(--c-text);
    font-size: 12px;
}

/* Credit link */
.sr-credit-link {
    color: #059669;
    text-decoration: none;
    font-weight: 500;
    font-size: 11px;
}
.sr-credit-link:hover { text-decoration: underline; }

/* Status badges */
.sr-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    width: fit-content;
}
.sr-status--draft     { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.sr-status--completed { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.sr-status--cancelled { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

/* Actions */
.sr-act-grp { display: flex; gap: 4px; align-items: center; }
.sr-act {
    width: 28px; height: 28px;
    border: none;
    border-radius: var(--r-sm);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .15s;
    text-decoration: none;
    flex-shrink: 0;
}
.sr-act--view     { background: #dbeafe; color: #2563eb; }
.sr-act--view:hover  { background: #bfdbfe; transform: scale(1.05); }
.sr-act--edit     { background: #fef3c7; color: #92400e; }
.sr-act--edit:hover  { background: #fde68a; transform: scale(1.05); }
.sr-act--complete { background: #dcfce7; color: #16a34a; }
.sr-act--complete:hover { background: #bbf7d0; transform: scale(1.05); }
.sr-act--del      { background: #fee2e2; color: #dc2626; }
.sr-act--del:hover   { background: #fecaca; transform: scale(1.05); }

/* Empty state */
.sr-empty-cell { padding: 52px 20px; text-align: center; }
.sr-empty { display: inline-flex; flex-direction: column; align-items: center; gap: 8px; }
.sr-empty-icon {
    width: 56px; height: 56px;
    background: var(--c-bg);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: #d1d5db;
    margin-bottom: 4px;
}
.sr-empty-title { font-size: 13px; font-weight: 600; color: var(--c-text2); margin: 0; }
.sr-empty-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }
.sr-empty-sub a { color: var(--c-brand); }

/* ─── Pagination ──────────────────────────────────────────── */
.sr-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-top: 1px solid var(--c-border);
    background: var(--c-bg);
    flex-wrap: wrap;
    gap: 8px;
}
.sr-page-info { font-size: 10.5px; color: var(--c-muted); }
.sr-page-info strong { color: var(--c-text2); }
.sr-pages { display: flex; gap: 3px; align-items: center; flex-wrap: wrap; }
.sr-pg {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    padding: 0 5px;
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    font-size: 11px;
    color: var(--c-text2);
    text-decoration: none;
    background: var(--c-white);
    transition: all .12s;
    font-weight: 500;
}
.sr-pg:hover         { background: #f3f4f6; border-color: #d1d5db; }
.sr-pg--active       { background: var(--c-brand); color: #fff; border-color: var(--c-brand); font-weight: 700; }
.sr-pg--active:hover { background: var(--c-brand); }
.sr-pg--dis          { color: #d1d5db; background: var(--c-bg); cursor: default; pointer-events: none; }
.sr-pg-dots          { font-size: 11px; color: var(--c-muted); padding: 0 2px; }

/* ─── Alerts ──────────────────────────────────────────────── */
#alertBox { position: fixed; top: 16px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 7px; }
.sr-alert {
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
    animation: srAIn .25s ease;
    min-width: 220px;
    max-width: 320px;
}
@keyframes srAIn {
    from { transform: translateX(110%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.sr-alert-success { background: #f0fdf4; color: #166534; border-left: 3px solid #22c55e; }
.sr-alert-error   { background: #fef2f2; color: #991b1b; border-left: 3px solid #ef4444; }

/* ─── Responsive ──────────────────────────────────────────── */
@media (max-width: 1024px) {
    .sr-stats { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .sr-stats { grid-template-columns: 1fr; }
    .sr-filter-group { min-width: calc(50% - 4px); flex: 1 1 calc(50% - 4px); }
    .sr-filter-btns { width: 100%; }
}
@media (max-width: 480px) {
    .sr-wrap { padding: 10px; }
    .sr-header { flex-direction: column; align-items: flex-start; }
    .sr-filter-group { min-width: 100%; flex: 1 1 100%; }
    .sr-pagination { flex-direction: column; align-items: flex-start; }
    .sr-pages { justify-content: center; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
function showAlert(message, type = 'success') {
    const box = document.getElementById('alertBox');
    const el = document.createElement('div');
    el.className = 'sr-alert sr-alert-' + type;
    el.textContent = message;
    box.appendChild(el);
    setTimeout(() => el.remove(), 4500);
}

function completeReturn(id) {
    if (!confirm('Complete this return? Stock will be added back and credit note will be generated.')) {
        return;
    }

    fetch('/admin/sales-returns/' + id + '/complete', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showAlert(data.message || 'Failed to complete return', 'error');
        }
    })
    .catch(() => {
        showAlert('Something went wrong', 'error');
    });
}

function deleteReturn(id) {
    if (!confirm('Are you sure you want to delete this draft return?')) {
        return;
    }

    fetch('/admin/sales-returns/' + id, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showAlert(data.message || 'Failed to delete return', 'error');
        }
    })
    .catch(() => {
        showAlert('Something went wrong', 'error');
    });
}
</script>
@endpush
@endsection
