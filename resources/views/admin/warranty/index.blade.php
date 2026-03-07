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
                <div class="wi-filter-group" style="flex:2;">
                    <label class="wi-filter-label">Search</label>
                    <div class="wi-input-icon-wrap">
                        <svg class="wi-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="search" class="wi-input wi-input-with-icon"
                               placeholder="Claim no., invoice, party, phone, product..." value="{{ request('search') }}">
                    </div>
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

                {{-- Replacement Status --}}
                <div class="wi-filter-group">
                    <label class="wi-filter-label">Replacement</label>
                    <select name="replacement_status" class="wi-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('replacement_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="done" {{ request('replacement_status') == 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>

                {{-- Repair Status --}}
                <div class="wi-filter-group">
                    <label class="wi-filter-label">Repair</label>
                    <select name="repair_status" class="wi-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('repair_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('repair_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
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
                        <th class="tc-no">S. No.</th>
                        <th class="tc-date">Date</th>
                        <th class="tc-claim">Claim No.</th>
                        <th class="tc-party">Party</th>
                        <th class="tc-product">Product</th>
                        <th class="tc-invoice">Invoice</th>
                        <th class="tc-warranty">Warranty</th>
                        <th class="tc-replace">Replace</th>
                        <th class="tc-repair">Repair</th>
                        <th class="tc-act">Actions</th>
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
                            <div class="td-party-name" title="{{ $partyName }}">{{ $partyName }}</div>
                            @if($partyPhone)
                                <div class="td-party-phone">{{ $partyPhone }}</div>
                            @endif
                        </td>
                        <td class="tc-product">
                            @if($claim->salesInvoiceItem)

                                <div class="td-product-name"
                                    title="{{ $claim->salesInvoiceItem->product_name }}">
                                    {{ \Illuminate\Support\Str::limit($claim->salesInvoiceItem->product_name ?? 'N/A', 30) }}
                                </div>

                                @if($claim->salesInvoiceItem->variant_name)
                                    <div class="td-variant-name">
                                        {{ $claim->salesInvoiceItem->variant_name ?? 'N/A' }}
                                    </div>
                                @endif

                                @if($claim->salesInvoiceItem->sku)
                                    <div class="td-product-sku">
                                        SKU: {{ $claim->salesInvoiceItem->sku ?? 'N/A' }}
                                    </div>
                                @endif

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
                        <td class="tc-warranty">
                            <span class="wi-badge wi-badge--{{ $claim->warranty_status }}">
                                {{ ucfirst($claim->warranty_status) }}
                            </span>
                        </td>
                        <td class="tc-replace">
                            <span class="wi-badge wi-badge--{{ $claim->replacement_status == 'done' ? 'done' : 'pending' }}">
                                {{ ucfirst($claim->replacement_status) }}
                            </span>
                        </td>
                        <td class="tc-repair">
                            <span class="wi-badge wi-badge--{{ $claim->repair_status == 'completed' ? 'done' : 'pending' }}">
                                {{ ucfirst($claim->repair_status) }}
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

                                @if($claim->canApprove())
                                    <button type="button"
                                            class="wi-act wi-act--approve"
                                            onclick="approveReplacement('{{ $claim->_id }}', '{{ $claim->warranty_claim_number }}')"
                                            title="Approve Replacement">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                    </button>
                                @endif

                                @if($claim->canMarkRepairCompleted())
                                    <button type="button"
                                            class="wi-act wi-act--repair"
                                            onclick="markRepairCompleted('{{ $claim->_id }}', '{{ $claim->warranty_claim_number }}')"
                                            title="Mark Repair Completed">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M14 7L9 12L14 17M20 7L15 12L20 17M4 7L9 12L4 17"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="wi-empty-cell">
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

{{-- Approve Replacement Modal --}}
<div class="wi-modal" id="approveModal">
    <div class="wi-modal-overlay" onclick="closeApproveModal()"></div>
    <div class="wi-modal-box">
        <div class="wi-modal-head">
            <div class="wi-modal-ico" style="background: #10b981;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div>
                <div class="wi-modal-title">Approve Replacement</div>
                <div class="wi-modal-sub">Confirm replacement approval</div>
            </div>
            <button class="wi-modal-close" onclick="closeApproveModal()">×</button>
        </div>
        <div class="wi-modal-body">
            <p style="margin-bottom: 12px;">This will deduct 1 item from main warehouse stock.</p>
            <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 6px; padding: 10px 12px;">
                <strong>Claim Number:</strong> <span id="approveClaimNumber"></span>
            </div>
        </div>
        <div class="wi-modal-foot">
            <button class="wi-btn-cancel" onclick="closeApproveModal()">Cancel</button>
            <button class="wi-btn-confirm" id="confirmApproveBtn" style="background: #10b981;">Approve Replacement</button>
        </div>
    </div>
</div>

{{-- Mark Repair Completed Modal --}}
<div class="wi-modal" id="repairModal">
    <div class="wi-modal-overlay" onclick="closeRepairModal()"></div>
    <div class="wi-modal-box">
        <div class="wi-modal-head">
            <div class="wi-modal-ico" style="background: #8b5cf6;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M14 7L9 12L14 17M20 7L15 12L20 17M4 7L9 12L4 17"/>
                </svg>
            </div>
            <div>
                <div class="wi-modal-title">Mark Repair Completed</div>
                <div class="wi-modal-sub">Confirm repair completion</div>
            </div>
            <button class="wi-modal-close" onclick="closeRepairModal()">×</button>
        </div>
        <div class="wi-modal-body">
            <p style="margin-bottom: 12px;">This will add the repaired product back to stock.</p>
            <div style="background: #ede9fe; border: 1px solid #c4b5fd; border-radius: 6px; padding: 10px 12px;">
                <strong>Claim Number:</strong> <span id="repairClaimNumber"></span>
            </div>
        </div>
        <div class="wi-modal-foot">
            <button class="wi-btn-cancel" onclick="closeRepairModal()">Cancel</button>
            <button class="wi-btn-confirm" id="confirmRepairBtn" style="background: #8b5cf6;">Mark Completed</button>
        </div>
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
    min-width: 1400px;
}
.wi-table th {
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
.wi-table td {
    padding: 9px 11px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    color: var(--c-text2);
}
.wi-table tr:last-child td { border-bottom: none; }
.wi-tr { transition: background .12s; }
.wi-tr:hover td { background: #fafafa; }

/* Column widths */
.tc-no        { width: 50px;  text-align: center; }
.tc-date      { width: 90px;  }
.tc-claim     { width: 130px; }
.tc-party     { width: 150px; }
.tc-product   { width: 200px; }
.tc-invoice   { width: 140px; }
.tc-warranty  { width: 80px;  }
.tc-replace   { width: 80px;  }
.tc-repair    { width: 80px;  }
.tc-act       { width: 100px; }

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
    max-width: 130px;
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
    max-width: 160px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.td-variant-name {
    font-size: 10px;
    color: var(--c-muted);
    margin-bottom: 2px;
}
.td-product-sku {
    font-size: 9px;
    color: var(--c-muted);
    font-family: 'Courier New', monospace;
}

/* Claim chip */
.wi-claim-chip {
    display: inline-block;
    padding: 3px 8px;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 600;
    color: #0369a1;
    white-space: nowrap;
    font-family: 'Courier New', monospace;
    letter-spacing: .2px;
}

/* Invoice link */
.wi-invoice-link {
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
    font-size: 11px;
}
.wi-invoice-link:hover {
    text-decoration: underline;
}

/* Status badges */
.wi-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    width: fit-content;
    min-width: 60px;
    text-align: center;
}
.wi-badge--valid      { background: #d1fae5; color: #065f46; }
.wi-badge--expired    { background: #fee2e2; color: #991b1b; }
.wi-badge--pending    { background: #fef3c7; color: #92400e; }
.wi-badge--done       { background: #d1fae5; color: #065f46; }
.wi-badge--completed  { background: #d1fae5; color: #065f46; }

/* Actions */
.wi-act-grp { display: flex; gap: 4px; align-items: center; }
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
.wi-act--approve { background: #d1fae5; color: #059669; }
.wi-act--approve:hover { background: #a7f3d0; transform: scale(1.05); }
.wi-act--repair  { background: #ede9fe; color: #7c3aed; }
.wi-act--repair:hover  { background: #ddd6fe; transform: scale(1.05); }

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

/* ─── Modal ───────────────────────────────────────────────── */
.wi-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.wi-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(2px);
}
.wi-modal-box {
    position: relative;
    background: var(--c-white);
    border-radius: 10px;
    width: 380px;
    max-width: 92%;
    box-shadow: 0 20px 50px rgba(0,0,0,.15);
    animation: wiMIn .2s ease;
}
@keyframes wiMIn {
    from { opacity:0; transform: scale(.95) translateY(10px); }
    to   { opacity:1; transform: scale(1) translateY(0); }
}
.wi-modal-head {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--c-border);
    background: var(--c-bg);
    border-radius: 10px 10px 0 0;
}
.wi-modal-ico {
    width: 34px; height: 34px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.wi-modal-title { font-size: 13.5px; font-weight: 700; margin-bottom: 1px; }
.wi-modal-sub   { font-size: 10.5px; color: var(--c-muted); }
.wi-modal-close {
    margin-left: auto;
    background: none;
    border: none;
    font-size: 20px;
    color: var(--c-muted);
    cursor: pointer;
    width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 5px;
    line-height: 1;
    transition: all .15s;
}
.wi-modal-close:hover { background: #e5e7eb; color: var(--c-text); }
.wi-modal-body { padding: 16px; }
.wi-modal-body p { font-size: 12.5px; color: #4b5563; line-height: 1.6; margin: 0; }
.wi-modal-foot {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 12px 16px;
    border-top: 1px solid var(--c-border);
    background: #fafafa;
    border-radius: 0 0 10px 10px;
}
.wi-btn-cancel {
    padding: 7px 16px;
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    background: var(--c-bg);
    color: var(--c-text2);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.wi-btn-cancel:hover { background: #e5e7eb; }
.wi-btn-confirm {
    padding: 7px 16px;
    border: none;
    border-radius: var(--r-sm);
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.wi-btn-confirm:hover { filter: brightness(0.9); }

/* ─── Responsive ──────────────────────────────────────────── */
@media (max-width: 1200px) {
    .wi-stats { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
    .wi-stats { grid-template-columns: 1fr 1fr; }
    .wi-stats .wi-stat:last-child { grid-column: span 2; }
    .wi-filter-group { min-width: calc(50% - 4px); flex: 1 1 calc(50% - 4px); }
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
let currentClaimId = null;

// Approve Replacement Modal
function approveReplacement(claimId, claimNumber) {
    currentClaimId = claimId;
    document.getElementById('approveClaimNumber').textContent = claimNumber;
    document.getElementById('approveModal').style.display = 'flex';
}

function closeApproveModal() {
    document.getElementById('approveModal').style.display = 'none';
    currentClaimId = null;
}

document.getElementById('confirmApproveBtn')?.addEventListener('click', function() {
    if (!currentClaimId) return;

    fetch(`/admin/warranty/${currentClaimId}/approve-replacement`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        closeApproveModal();
        if (data.success) {
            showAlert('Replacement approved successfully', 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showAlert(data.message || 'Failed to approve', 'error');
        }
    })
    .catch(() => {
        closeApproveModal();
        showAlert('Something went wrong', 'error');
    });
});

// Mark Repair Completed Modal
function markRepairCompleted(claimId, claimNumber) {
    currentClaimId = claimId;
    document.getElementById('repairClaimNumber').textContent = claimNumber;
    document.getElementById('repairModal').style.display = 'flex';
}

function closeRepairModal() {
    document.getElementById('repairModal').style.display = 'none';
    currentClaimId = null;
}

document.getElementById('confirmRepairBtn')?.addEventListener('click', function() {
    if (!currentClaimId) return;

    fetch(`/admin/warranty/${currentClaimId}/mark-repair-completed`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        closeRepairModal();
        if (data.success) {
            showAlert('Repair marked as completed', 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showAlert(data.message || 'Failed to mark completed', 'error');
        }
    })
    .catch(() => {
        closeRepairModal();
        showAlert('Something went wrong', 'error');
    });
});

// Close modals when clicking overlay
document.querySelectorAll('.wi-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function() {
        closeApproveModal();
        closeRepairModal();
    });
});

// Keyboard shortcut to close modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeApproveModal();
        closeRepairModal();
    }
});

// Alert function
function showAlert(msg, type = 'success') {
    const box = document.getElementById('alertBox');
    if (!box) return;

    const el = document.createElement('div');
    el.className = 'wi-alert wi-alert-' + type;
    el.textContent = msg;
    box.appendChild(el);
    setTimeout(() => el.remove(), 4500);
}
</script>
@endpush
@endsection
