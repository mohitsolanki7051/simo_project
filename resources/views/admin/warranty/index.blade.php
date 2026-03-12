@extends('layouts.admin')

@section('title', 'Warranty Claims')
@section('header-title', 'Warranty')

@section('content')
<div class="wi-wrap">
    <div id="alertBox"></div>

    {{-- Header --}}
    <div class="wi-header">
        <div class="wi-header-left">
            <div class="wi-header-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3v18M16 5l-4-2-4 2v4l4 2 4-2V5zM8 13l-4 2v4l4 2 4-2v-4l-4-2zM16 13l-4 2v4l4 2 4-2v-4l-4-2z"/>
                </svg>
            </div>
            <div>
                <h1 class="wi-title">Warranty Claims</h1>
                <p class="wi-sub">Manage product warranty claims and replacements</p>
            </div>
        </div>
        <a href="{{ route('admin.warranty.create') }}" class="wi-btn-create">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Claim
        </a>
    </div>

    {{-- Stats --}}
    <div class="wi-stats">
        <div class="wi-stat wi-stat--blue">
            <div class="wi-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M16 5l-4-2-4 2v4l4 2 4-2V5zM8 13l-4 2v4l4 2 4-2v-4l-4-2zM16 13l-4 2v4l4 2 4-2v-4l-4-2z"/></svg>
            </div>
            <div class="wi-stat-content">
                <div class="wi-stat-label">Total Claims</div>
                <div class="wi-stat-value">{{ $counts['total'] }}</div>
                <div class="wi-stat-hint">All warranty claims</div>
            </div>
        </div>
        <div class="wi-stat wi-stat--green">
            <div class="wi-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="wi-stat-content">
                <div class="wi-stat-label">Valid Warranty</div>
                <div class="wi-stat-value">{{ $counts['valid'] }}</div>
                <div class="wi-stat-hint">Eligible for replacement</div>
            </div>
        </div>
        <div class="wi-stat wi-stat--red">
            <div class="wi-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
            </div>
            <div class="wi-stat-content">
                <div class="wi-stat-label">Expired</div>
                <div class="wi-stat-value">{{ $counts['expired'] }}</div>
                <div class="wi-stat-hint">Warranty expired</div>
            </div>
        </div>
        <div class="wi-stat wi-stat--orange">
            <div class="wi-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="wi-stat-content">
                <div class="wi-stat-label">Pending Replacement</div>
                <div class="wi-stat-value">{{ $counts['pending_replacement'] }}</div>
                <div class="wi-stat-hint">Awaiting approval</div>
            </div>
        </div>
        <div class="wi-stat wi-stat--purple">
            <div class="wi-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 7L9 12L14 17M20 7L15 12L20 17M4 7L9 12L4 17"/></svg>
            </div>
            <div class="wi-stat-content">
                <div class="wi-stat-label">Under Repair</div>
                <div class="wi-stat-value">{{ $counts['pending_repair'] }}</div>
                <div class="wi-stat-hint">In repair process</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="wi-filters">
        <div class="wi-filters-header">
            <div class="wi-filters-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filters
            </div>
            @if(request()->anyFilled(['search','warranty_status','replacement_status','repair_status','from_date','to_date']))
            <a href="{{ route('admin.warranty.index') }}" class="wi-clear-filters">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Clear all filters
            </a>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.warranty.index') }}" id="filterForm">
            <div class="wi-filter-row">
                {{-- Search --}}
                <div class="wi-filter-group wi-filter-search">
                    <label class="wi-filter-label">Search</label>
                    <div class="wi-input-icon-wrap">
                        <svg class="wi-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="search" class="wi-input wi-input-with-icon"
                               placeholder="Claim no., invoice..." value="{{ request('search') }}">
                    </div>
                </div>
                {{-- From Date --}}
                <div class="wi-filter-group">
                    <label class="wi-filter-label">From</label>
                    <input type="date" name="from_date" class="wi-input" value="{{ request('from_date') }}">
                </div>

                {{-- To Date --}}
                <div class="wi-filter-group">
                    <label class="wi-filter-label">To</label>
                    <input type="date" name="to_date" class="wi-input" value="{{ request('to_date') }}">
                </div>
                {{-- Warranty Status --}}
                <div class="wi-filter-group">
                    <label class="wi-filter-label">Warranty</label>
                    <select name="warranty_status" class="wi-select">
                        <option value="">All Warranty</option>
                        <option value="valid" {{ request('warranty_status') == 'valid' ? 'selected' : '' }}>Valid</option>
                        <option value="expired" {{ request('warranty_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>

                <div class="wi-filter-group">
                    <label class="wi-filter-label">Warehouse</label>
                    <select name="warehouse_id" class="wi-select">
                        <option value="">All</option>

                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Replacement Status --}}
                <div class="wi-filter-group">
                    <label class="wi-filter-label">Replacement</label>
                    <select name="replacement_status" class="wi-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('replacement_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ request('replacement_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="done" {{ request('replacement_status') == 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>

                {{-- Repair Status --}}
                <div class="wi-filter-group">
                    <label class="wi-filter-label">Repair</label>
                    <select name="repair_status" class="wi-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('repair_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ request('repair_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="completed" {{ request('repair_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>



                {{-- Buttons --}}
                <div class="wi-filter-btns">
                    <button type="submit" class="wi-btn-filter">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Apply
                    </button>
                    <a href="{{ route('admin.warranty.index') }}" class="wi-btn-reset">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="wi-table-card">
        <div class="wi-table-wrap">
            <table class="wi-table">
                <thead>
                    <tr>
                        <th class="tc-no">#</th>
                        <th class="tc-date">Date</th>
                        <th class="tc-claim">Claim No.</th>
                        <th class="tc-party">Party</th>
                        <th class="tc-product">Product</th>
                        <th class="tc-invoice">Invoice</th>
                        <th class="tc-warehouse">Warehouse</th>
                        <th class="tc-warranty">Warranty</th>
                        <th class="tc-replace">Replace</th>
                        <th class="tc-repair">Repair</th>
                        <th class="tc-act">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $i => $claim)
                    <tr class="wi-tr">
                        <td class="tc-no">
                            <span class="td-serial">{{ ($claims->currentPage() - 1) * $claims->perPage() + $i + 1 }}</span>
                        </td>
                        <td class="tc-date">
                            <div class="td-date-main">{{ \Carbon\Carbon::parse($claim->claim_date)->format('d M Y') }}</div>
                            <div class="td-date-sub">{{ \Carbon\Carbon::parse($claim->claim_date)->format('D') }}</div>
                        </td>
                        <td class="tc-claim">
                            <span class="wi-claim-chip">{{ $claim->warranty_claim_number }}</span>
                        </td>
                        <td class="tc-party">
                            @php
                                $partyName = optional($claim->party)->name ?? 'N/A';
                                $partyPhone = optional($claim->party)->phone ?? '';
                            @endphp
                            <div class="td-party-name" title="{{ $partyName }}">{{ \Illuminate\Support\Str::limit($partyName, 20) }}</div>
                            @if($partyPhone)
                                <div class="td-party-phone">{{ $partyPhone }}</div>
                            @endif
                        </td>
                        <td class="tc-product">
                            @if($claim->salesInvoiceItem)
                                <div class="td-product-name" title="{{ $claim->salesInvoiceItem->product_name ?? 'N/A' }}">
                                    {{ \Illuminate\Support\Str::limit($claim->salesInvoiceItem->product_name ?? 'N/A', 25) }}
                                </div>
                                @if($claim->salesInvoiceItem->variant_name)
                                    <div class="td-variant-name">
                                        {{ \Illuminate\Support\Str::limit($claim->salesInvoiceItem->variant_name ?? 'N/A', 20) }}
                                    </div>
                                @endif
                                <div class="td-product-sku">
                                    SKU: {{ $claim->salesInvoiceItem->sku ?? 'N/A' }}
                                </div>
                            @else
                                <span class="td-muted">Product not found</span>
                            @endif
                        </td>
                        <td class="tc-invoice">
                            @if($claim->salesInvoice)
                                <a href="{{ route('admin.sales.show', $claim->sales_invoice_id) }}" class="wi-invoice-link" target="_blank">
                                    {{ $claim->salesInvoice->invoice_number }}
                                </a>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td class="tc-warehouse">
                            @if($claim->warehouse)
                                <span class="wi-warehouse-badge" title="{{ $claim->warehouse->name }}">
                                    {{ \Illuminate\Support\Str::limit($claim->warehouse->name, 15) }}
                                </span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td class="tc-warranty">
                            <span class="wi-badge wi-badge--{{ $claim->warranty_status }}">
                                {{ ucfirst($claim->warranty_status) }}
                            </span>
                        </td>
                        <td class="tc-replace">
                            <span class="wi-badge wi-badge--{{ $claim->replacement_status == 'done' ? 'done' : ($claim->replacement_status == 'partial' ? 'pending' : 'pending') }}">
                                {{ ucfirst($claim->replacement_status) }}
                                @if($claim->claimed_qty > 1)
                                    <span style="font-size:8px; display:block;">({{ $claim->replaced_qty }}/{{ $claim->claimed_qty }})</span>
                                @endif
                            </span>
                        </td>
                        <td class="tc-repair">
                            <span class="wi-badge wi-badge--{{ $claim->repair_status == 'completed' ? 'done' : ($claim->repair_status == 'partial' ? 'pending' : 'pending') }}">
                                {{ ucfirst($claim->repair_status) }}
                                @if($claim->replaced_qty > 0)
                                    <span style="font-size:8px; display:block;">({{ $claim->repaired_qty }}/{{ $claim->replaced_qty }})</span>
                                @endif
                            </span>
                        </td>
                        <td class="tc-act">
                            <div class="wi-act-grp">
                                <a href="{{ route('admin.warranty.show', $claim->_id) }}"
                                   class="wi-act wi-act--view" title="View Details">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                                {{-- Edit button - only show if claim is not approved --}}
                                @if(!$claim->approved_at)
                                <a href="{{ route('admin.warranty.edit', $claim->_id) }}"
                                class="wi-act wi-act--edit" title="Edit Claim">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="wi-empty-cell">
                            <div class="wi-empty">
                                <div class="wi-empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M12 3v18M16 5l-4-2-4 2v4l4 2 4-2V5zM8 13l-4 2v4l4 2 4-2v-4l-4-2zM16 13l-4 2v4l4 2 4-2v-4l-4-2z"/>
                                    </svg>
                                </div>
                                <p class="wi-empty-title">No warranty claims found</p>
                                <p class="wi-empty-sub">
                                    @if(request()->anyFilled(['search','warranty_status','replacement_status','repair_status','from_date','to_date']))
                                        Try adjusting your filters or <a href="{{ route('admin.warranty.index') }}">clear all</a>
                                    @else
                                        Get started by creating your first warranty claim
                                    @endif
                                </p>
                                <a href="{{ route('admin.warranty.create') }}" class="wi-btn-create" style="margin-top:4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    New Claim
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($claims->hasPages())
        <div class="wi-pagination">
            <div class="wi-page-info">
                Page <strong>{{ $claims->currentPage() }}</strong> of <strong>{{ $claims->lastPage() }}</strong>
                &nbsp;·&nbsp; {{ $claims->total() }} total records
            </div>
            <div class="wi-pages">
                @if($claims->onFirstPage())
                    <span class="wi-pg wi-pg--dis">«</span>
                    <span class="wi-pg wi-pg--dis">‹</span>
                @else
                    <a href="{{ $claims->url(1) }}" class="wi-pg" title="First">«</a>
                    <a href="{{ $claims->previousPageUrl() }}" class="wi-pg" title="Previous">‹</a>
                @endif

                @php
                    $cur   = $claims->currentPage();
                    $last  = $claims->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $claims->url(1) }}" class="wi-pg">1</a>
                    @if($start > 2) <span class="wi-pg-dots">…</span> @endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="wi-pg wi-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $claims->url($p) }}" class="wi-pg">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $last)
                    @if($end < $last - 1) <span class="wi-pg-dots">…</span> @endif
                    <a href="{{ $claims->url($last) }}" class="wi-pg">{{ $last }}</a>
                @endif

                @if($claims->hasMorePages())
                    <a href="{{ $claims->nextPageUrl() }}" class="wi-pg" title="Next">›</a>
                    <a href="{{ $claims->url($last) }}" class="wi-pg" title="Last">»</a>
                @else
                    <span class="wi-pg wi-pg--dis">›</span>
                    <span class="wi-pg wi-pg--dis">»</span>
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
.wi-wrap {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 12.5px;
    color: var(--c-text);
    padding: 16px;
    max-width: 100%;
}

/* ─── Header ──────────────────────────────────────────────── */
.wi-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--c-border);
    gap: 12px;
    flex-wrap: wrap;
}
.wi-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
}
.wi-header-icon {
    width: 38px; height: 38px;
    background: var(--c-brand-l);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    color: var(--c-brand);
    flex-shrink: 0;
}
.wi-title { font-size: 17px; font-weight: 700; margin: 0 0 2px; letter-spacing: -.3px; }
.wi-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }

.wi-btn-create {
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
.wi-btn-create:hover {
    background: var(--c-brand-d);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(249,115,22,.35);
}

/* ─── Stats ───────────────────────────────────────────────── */
.wi-stats {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-bottom: 14px;
}
.wi-stat {
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
.wi-stat:hover {
    box-shadow: var(--c-shadow2);
    transform: translateY(-1px);
}
.wi-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: var(--r) var(--r) 0 0;
}
.wi-stat--blue::before   { background: #3b82f6; }
.wi-stat--green::before  { background: #22c55e; }
.wi-stat--red::before    { background: #ef4444; }
.wi-stat--orange::before { background: #f97316; }
.wi-stat--purple::before { background: #8b5cf6; }

.wi-stat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.wi-stat--blue .wi-stat-icon   { background: #dbeafe; color: #2563eb; }
.wi-stat--green .wi-stat-icon  { background: #dcfce7; color: #16a34a; }
.wi-stat--red .wi-stat-icon    { background: #fee2e2; color: #dc2626; }
.wi-stat--orange .wi-stat-icon { background: #fff7ed; color: #f97316; }
.wi-stat--purple .wi-stat-icon { background: #ede9fe; color: #7c3aed; }

.wi-stat-label { font-size: 10.5px; color: var(--c-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
.wi-stat-value { font-size: 17px; font-weight: 800; color: var(--c-text); letter-spacing: -.4px; margin-bottom: 2px; }
.wi-stat-hint  { font-size: 10px; color: var(--c-muted); }

/* ─── Filters ─────────────────────────────────────────────── */
.wi-filters {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    padding: 11px 14px 13px;
    margin-bottom: 12px;
    box-shadow: var(--c-shadow);
}
.wi-filters-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.wi-filters-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--c-text2);
    display: flex;
    align-items: center;
    gap: 5px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.wi-clear-filters {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    color: var(--c-brand);
    text-decoration: none;
    font-weight: 500;
    transition: color .15s;
}
.wi-clear-filters:hover { color: var(--c-brand-d); text-decoration: underline; }

.wi-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-end;
}
.wi-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 120px;
}

.wi-filter-search{
    min-width: 220px;
}
.wi-filter-label {
    font-size: 9.5px;
    font-weight: 700;
    color: var(--c-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.wi-input, .wi-select {
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
.wi-input:focus, .wi-select:focus {
    border-color: var(--c-brand);
    background: var(--c-white);
    box-shadow: 0 0 0 3px rgba(249,115,22,.1);
}
.wi-input-icon-wrap { position: relative; }
.wi-input-icon {
    position: absolute;
    left: 9px; top: 50%;
    transform: translateY(-50%);
    color: var(--c-muted);
    pointer-events: none;
}
.wi-input-with-icon { padding-left: 28px; }

.wi-filter-btns {
    display: flex;
    gap: 6px;
    align-items: flex-end;
    margin-left: auto;
}
.wi-btn-filter {
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
.wi-btn-filter:hover { background: #1f2937; }
.wi-btn-reset {
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
.wi-btn-reset:hover { background: #f3f4f6; color: var(--c-text); }

/* ─── Table Card ──────────────────────────────────────────── */
.wi-table-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    box-shadow: var(--c-shadow);
    overflow: hidden;
}
.wi-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.wi-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 1300px;
}
.wi-table th {
    padding: 9px 8px;
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
.wi-table td {
    padding: 9px 8px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    color: var(--c-text2);
}
.wi-table tr:last-child td { border-bottom: none; }
.wi-tr { transition: background .12s; }
.wi-tr:hover td { background: #fafafa; }

/* Column widths - compact */
.tc-no        { width: 40px; text-align: center; }
.tc-date      { width: 70px; }
.tc-claim     { width: 110px; }
.tc-party     { width: 130px; }
.tc-product   { width: 160px; }
.tc-invoice   { width: 100px; }
.tc-warehouse { width: 90px; }
.tc-warranty  { width: 70px; }
.tc-replace   { width: 70px; }
.tc-repair    { width: 70px; }
.tc-act       { width: 50px; }

/* Table cell helpers */
.td-serial {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    background: var(--c-bg);
    border-radius: 4px;
    font-size: 10px;
    color: var(--c-muted);
    font-weight: 600;
}
.td-muted { color: var(--c-muted); font-size: 10.5px; }
.td-date-main { font-size: 11.5px; color: var(--c-text2); white-space: nowrap; font-weight: 500; }
.td-date-sub { font-size: 9.5px; color: var(--c-muted); }
.td-party-name {
    font-weight: 500;
    color: var(--c-text);
    font-size: 12px;
    margin-bottom: 2px;
    max-width: 120px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.td-party-phone {
    font-size: 10px;
    color: var(--c-muted);
}
.td-product-name {
    font-weight: 500;
    color: var(--c-text);
    font-size: 12px;
    margin-bottom: 2px;
    max-width: 140px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.td-variant-name {
    font-size: 10px;
    color: var(--c-muted);
    margin-bottom: 2px;
    max-width: 140px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.td-product-sku {
    font-size: 9px;
    color: var(--c-muted);
    font-family: 'Courier New', monospace;
}

/* Warehouse badge - orange color */
.wi-warehouse-badge {
    display: inline-block;
    padding: 3px 8px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    color: #c2410c;
    white-space: nowrap;
    max-width: 85px;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Claim chip */
.wi-claim-chip {
    display: inline-block;
    padding: 3px 6px;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    color: #0369a1;
    white-space: nowrap;
    font-family: 'Courier New', monospace;
    letter-spacing: .2px;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
}
.wi-act--edit {
    background: #fff7ed;
    color: #f97316;
}
.wi-act--edit:hover {
    background: #fed7aa;
    transform: scale(1.05);
}
/* Invoice link */
.wi-invoice-link {
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
    font-size: 11px;
    display: inline-block;
    max-width: 90px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.wi-invoice-link:hover {
    text-decoration: underline;
}

/* Status badges */
.wi-badge {
    display: inline-block;
    padding: 3px 6px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    width: fit-content;
    min-width: 55px;
    text-align: center;
}
.wi-badge--valid      { background: #d1fae5; color: #065f46; }
.wi-badge--expired    { background: #fee2e2; color: #991b1b; }
.wi-badge--pending    { background: #fef3c7; color: #92400e; }
.wi-badge--done       { background: #d1fae5; color: #065f46; }
.wi-badge--completed  { background: #d1fae5; color: #065f46; }

/* Actions */
.wi-act-grp { display: flex; gap: 4px; align-items: center; justify-content: center; }
.wi-act {
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
.wi-act--view    { background: #dbeafe; color: #2563eb; }
.wi-act--view:hover    { background: #bfdbfe; transform: scale(1.05); }

/* Empty state */
.wi-empty-cell { padding: 52px 20px; text-align: center; }
.wi-empty { display: inline-flex; flex-direction: column; align-items: center; gap: 8px; }
.wi-empty-icon {
    width: 56px; height: 56px;
    background: var(--c-bg);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: #d1d5db;
    margin-bottom: 4px;
}
.wi-empty-title { font-size: 13px; font-weight: 600; color: var(--c-text2); margin: 0; }
.wi-empty-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }
.wi-empty-sub a { color: var(--c-brand); }

/* ─── Pagination ──────────────────────────────────────────── */
.wi-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-top: 1px solid var(--c-border);
    background: var(--c-bg);
    flex-wrap: wrap;
    gap: 8px;
}
.wi-page-info { font-size: 10.5px; color: var(--c-muted); }
.wi-page-info strong { color: var(--c-text2); }
.wi-pages { display: flex; gap: 3px; align-items: center; flex-wrap: wrap; }
.wi-pg {
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
.wi-pg:hover         { background: #f3f4f6; border-color: #d1d5db; }
.wi-pg--active       { background: var(--c-brand); color: #fff; border-color: var(--c-brand); font-weight: 700; }
.wi-pg--active:hover { background: var(--c-brand); }
.wi-pg--dis          { color: #d1d5db; background: var(--c-bg); cursor: default; pointer-events: none; }
.wi-pg-dots          { font-size: 11px; color: var(--c-muted); padding: 0 2px; }

/* ─── Responsive ──────────────────────────────────────────── */
@media (max-width: 1200px) {
    .wi-stats { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
    .wi-stats { grid-template-columns: 1fr 1fr; }
    .wi-stats .wi-stat:last-child { grid-column: span 2; }
     .wi-filter-group {
        flex: 1 1 calc(50% - 6px);
        min-width: calc(50% - 6px);
    }

    .wi-filter-search{
        flex: 1 1 100%;
        min-width:100%;
    }
    .wi-filter-btns { width: 100%; }
    .wi-header { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 480px) {
    .wi-wrap { padding: 10px; }
    .wi-stats { grid-template-columns: 1fr; }
    .wi-stats .wi-stat:last-child { grid-column: span 1; }
    .wi-filter-group { min-width: 100%; flex: 1 1 100%; }
    .wi-pagination { flex-direction: column; align-items: flex-start; }
    .wi-pages { justify-content: center; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
// Simple alert function
function showAlert(msg, type = 'success') {
    const box = document.getElementById('alertBox');
    if (!box) return;

    const el = document.createElement('div');
    el.style.cssText = `
        padding: 10px 14px;
        margin-bottom: 12px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 500;
        background: ${type === 'success' ? '#d1fae5' : '#fee2e2'};
        color: ${type === 'success' ? '#065f46' : '#991b1b'};
        border: 1px solid ${type === 'success' ? '#a7f3d0' : '#fecaca'};
    `;
    el.textContent = msg;
    box.appendChild(el);
    setTimeout(() => el.remove(), 4500);
}
</script>
@endpush
@endsection
