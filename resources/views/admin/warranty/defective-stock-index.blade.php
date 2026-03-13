@extends('layouts.admin')

@section('title', 'Defective Stock')
@section('header-title', 'Defective Stock')

@section('content')
<div class="ds-wrap">

    {{-- Header --}}
    <div class="ds-header">
        <div class="ds-header-left">
            <div class="ds-header-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div>
                <h1 class="ds-title">Defective Stock</h1>
                <p class="ds-sub">Track and manage defective units received from warranty claims</p>
            </div>
        </div>
    </div>

    {{-- Stats — filtered --}}
    <div class="ds-stats">
        <div class="ds-stat ds-stat--blue">
            <div class="ds-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
            </div>
            <div class="ds-stat-content">
                <div class="ds-stat-label">Total</div>
                <div class="ds-stat-value">{{ $stats['total'] }}</div>
                <div class="ds-stat-hint">All defective items</div>
            </div>
        </div>
        <div class="ds-stat ds-stat--orange">
            <div class="ds-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="ds-stat-content">
                <div class="ds-stat-label">Pending Repair</div>
                <div class="ds-stat-value">{{ $stats['pending_repair'] }}</div>
                <div class="ds-stat-hint">In workshop</div>
            </div>
        </div>
        <div class="ds-stat ds-stat--green">
            <div class="ds-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
            </div>
            <div class="ds-stat-content">
                <div class="ds-stat-label">Repaired</div>
                <div class="ds-stat-value">{{ $stats['repaired'] }}</div>
                <div class="ds-stat-hint">Back in stock</div>
            </div>
        </div>
        <div class="ds-stat ds-stat--red">
            <div class="ds-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
            </div>
            <div class="ds-stat-content">
                <div class="ds-stat-label">Scrapped</div>
                <div class="ds-stat-value">{{ $stats['scrapped'] }}</div>
                <div class="ds-stat-hint">No recovery possible</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="ds-filters">
        <div class="ds-filters-header">
            <div class="ds-filters-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filters
            </div>
            @if(request()->anyFilled(['search','warehouse_id','defective_stock_status','from_date','to_date']))
            <a href="{{ route('admin.defective-stock.index') }}" class="ds-clear-filters">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Clear all filters
            </a>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.defective-stock.index') }}">
            <div class="ds-filter-row">
                <div class="ds-filter-group ds-filter-search">
                    <label class="ds-filter-label">Search</label>
                    <div class="ds-input-icon-wrap">
                        <svg class="ds-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="search" class="ds-input ds-input-with-icon"
                               placeholder="Claim no., customer, product..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="ds-filter-group">
                    <label class="ds-filter-label">From</label>
                    <input type="date" name="from_date" class="ds-input" value="{{ request('from_date') }}">
                </div>
                <div class="ds-filter-group">
                    <label class="ds-filter-label">To</label>
                    <input type="date" name="to_date" class="ds-input" value="{{ request('to_date') }}">
                </div>
                <div class="ds-filter-group">
                    <label class="ds-filter-label">Warehouse</label>
                    <select name="warehouse_id" class="ds-select">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ds-filter-group">
                    <label class="ds-filter-label">Status</label>
                    <select name="defective_stock_status" class="ds-select">
                        <option value="">All Status</option>
                        <option value="pending_repair" {{ request('defective_stock_status') === 'pending_repair' ? 'selected' : '' }}>Pending Repair</option>
                        <option value="repaired"       {{ request('defective_stock_status') === 'repaired'       ? 'selected' : '' }}>Repaired</option>
                        <option value="scrapped"       {{ request('defective_stock_status') === 'scrapped'       ? 'selected' : '' }}>Scrapped</option>
                    </select>
                </div>
                <div class="ds-filter-btns">
                    <button type="submit" class="ds-btn-filter">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Apply
                    </button>
                    <a href="{{ route('admin.defective-stock.index') }}" class="ds-btn-reset">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="ds-table-card">
        <div class="ds-table-wrap">
            <table class="ds-table">
                <thead>
                    <tr>
                        <th class="tc-no">#</th>
                        <th class="tc-date">Claim Date</th>
                        <th class="tc-claim">Claim No.</th>
                        <th class="tc-product">Product</th>
                        <th class="tc-variant">Variant</th>
                        <th class="tc-customer">Customer</th>
                        <th class="tc-warehouse">Warehouse</th>
                        <th class="tc-qty tc-replaced">Replaced</th>
                        <th class="tc-qty tc-repaired">Repaired</th>
                        <th class="tc-qty tc-scrapped">Scrapped</th>
                        <th class="tc-qty tc-pending">Pending</th>
                        <th class="tc-status">Status</th>
                        <th class="tc-act">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $i => $claim)
                    @php
                        $pendingQty  = max(0, (int)$claim->replaced_qty - (int)$claim->repaired_qty - (int)$claim->scrapped_qty);
                        $productName = $claim->salesInvoiceItem->product_name ?? 'N/A';
                        $variantName = $claim->salesInvoiceItem->variant_name ?? null;
                        $dss = $claim->defective_stock_status ?? 'pending_repair';
                        $dssMap = [
                            'pending_repair' => ['label' => 'Pending Repair', 'cls' => 'ds-badge--pending'],
                            'repaired'       => ['label' => 'Repaired',       'cls' => 'ds-badge--repaired'],
                            'scrapped'       => ['label' => 'Scrapped',       'cls' => 'ds-badge--scrapped'],
                        ];
                        $dssInfo = $dssMap[$dss] ?? ['label' => ucfirst($dss), 'cls' => 'ds-badge--pending'];
                    @endphp
                    <tr class="ds-tr">
                        <td class="tc-no">
                            <span class="td-serial">{{ ($claims->currentPage() - 1) * $claims->perPage() + $i + 1 }}</span>
                        </td>
                        <td class="tc-date">
                            <div class="td-date-main">{{ \Carbon\Carbon::parse($claim->claim_date)->format('d M Y') }}</div>
                            <div class="td-date-sub">{{ \Carbon\Carbon::parse($claim->claim_date)->format('D') }}</div>
                        </td>
                        <td class="tc-claim">
                            <span class="ds-claim-chip">{{ $claim->warranty_claim_number }}</span>
                        </td>
                        <td class="tc-product">
                            <div class="td-product-name" title="{{ $productName }}">
                                {{ \Illuminate\Support\Str::limit($productName, 28) }}
                            </div>
                            @if($claim->salesInvoiceItem?->sku)
                                <div class="td-product-sku">SKU: {{ $claim->salesInvoiceItem->sku }}</div>
                            @endif
                        </td>
                        <td class="tc-variant">
                            @if($variantName)
                                <span class="ds-variant-badge">{{ \Illuminate\Support\Str::limit($variantName, 18) }}</span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td class="tc-customer">
                            @php $partyName = optional($claim->party)->name ?? 'N/A'; @endphp
                            <div class="td-party-name" title="{{ $partyName }}">{{ \Illuminate\Support\Str::limit($partyName, 20) }}</div>
                            @if(optional($claim->party)->phone)
                                <div class="td-party-phone">{{ $claim->party->phone }}</div>
                            @endif
                        </td>
                        <td class="tc-warehouse">
                            @if($claim->warehouse)
                                <span class="ds-warehouse-badge" title="{{ $claim->warehouse->name }}">
                                    {{ \Illuminate\Support\Str::limit($claim->warehouse->name, 14) }}
                                </span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td class="tc-qty tc-replaced text-center">
                            <span class="ds-qty-chip ds-qty--replaced">{{ $claim->replaced_qty }}</span>
                        </td>
                        <td class="tc-qty tc-repaired text-center">
                            <span class="ds-qty-chip ds-qty--repaired">{{ $claim->repaired_qty }}</span>
                        </td>
                        <td class="tc-qty tc-scrapped text-center">
                            <span class="ds-qty-chip ds-qty--scrapped">{{ $claim->scrapped_qty }}</span>
                        </td>
                        <td class="tc-qty tc-pending text-center">
                            <span class="ds-qty-chip {{ $pendingQty > 0 ? 'ds-qty--pending' : 'ds-qty--zero' }}">{{ $pendingQty }}</span>
                        </td>
                        <td class="tc-status">
                            <span class="ds-badge {{ $dssInfo['cls'] }}">{{ $dssInfo['label'] }}</span>
                        </td>
                        <td class="tc-act">
                            <div class="ds-act-grp">
                                <button type="button"
                                    class="ds-act ds-act--view"
                                    title="View Details"
                                    onclick="openDetailModal('{{ $claim->_id }}')">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="ds-empty-cell">
                            <div class="ds-empty">
                                <div class="ds-empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                        <line x1="12" y1="9" x2="12" y2="13"/>
                                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                                    </svg>
                                </div>
                                <p class="ds-empty-title">No defective stock records found</p>
                                <p class="ds-empty-sub">
                                    @if(request()->anyFilled(['search','warehouse_id','defective_stock_status','from_date','to_date']))
                                        Try adjusting your filters or <a href="{{ route('admin.defective-stock.index') }}">clear all</a>
                                    @else
                                        Defective items from warranty claims will appear here
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($claims->hasPages())
        <div class="ds-pagination">
            <div class="ds-page-info">
                Page <strong>{{ $claims->currentPage() }}</strong> of <strong>{{ $claims->lastPage() }}</strong>
                &nbsp;·&nbsp; {{ $claims->total() }} total records
            </div>
            <div class="ds-pages">
                @if($claims->onFirstPage())
                    <span class="ds-pg ds-pg--dis">«</span>
                    <span class="ds-pg ds-pg--dis">‹</span>
                @else
                    <a href="{{ $claims->url(1) }}" class="ds-pg">«</a>
                    <a href="{{ $claims->previousPageUrl() }}" class="ds-pg">‹</a>
                @endif
                @php
                    $cur   = $claims->currentPage();
                    $last  = $claims->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp
                @if($start > 1)
                    <a href="{{ $claims->url(1) }}" class="ds-pg">1</a>
                    @if($start > 2)<span class="ds-pg-dots">…</span>@endif
                @endif
                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="ds-pg ds-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $claims->url($p) }}" class="ds-pg">{{ $p }}</a>
                    @endif
                @endfor
                @if($end < $last)
                    @if($end < $last - 1)<span class="ds-pg-dots">…</span>@endif
                    <a href="{{ $claims->url($last) }}" class="ds-pg">{{ $last }}</a>
                @endif
                @if($claims->hasMorePages())
                    <a href="{{ $claims->nextPageUrl() }}" class="ds-pg">›</a>
                    <a href="{{ $claims->url($last) }}" class="ds-pg">»</a>
                @else
                    <span class="ds-pg ds-pg--dis">›</span>
                    <span class="ds-pg ds-pg--dis">»</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════ VIEW MODAL ═══════════════════ --}}
<div id="dsModal" class="ds-modal-overlay" style="display:none;" onclick="closeDsModal(event)">
    <div class="ds-modal">
        <div class="ds-modal-header">
            <div class="ds-modal-header-left">
                <div class="ds-modal-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <div>
                    <div class="ds-modal-title">Defective Stock Details</div>
                    <div class="ds-modal-sub" id="dsModalClaimNo">—</div>
                </div>
            </div>
            <button class="ds-modal-close" onclick="closeDsModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="ds-modal-body">
            <div class="ds-modal-loading" id="dsModalLoading">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ds-spin"><circle cx="12" cy="12" r="10" stroke-opacity=".3"/><path d="M12 2a10 10 0 0110 10"/></svg>
                <span>Loading...</span>
            </div>
            <div id="dsModalContent" style="display:none;">

                {{-- Status Banner --}}
                <div id="dsStatusBanner" class="ds-status-banner"></div>

                {{-- Grid --}}
                <div class="ds-modal-grid">
                    <div class="ds-modal-section">
                        <div class="ds-section-title">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Customer
                        </div>
                        <div class="ds-detail-rows">
                            <div class="ds-detail-row"><span class="ds-dl">Name</span><span class="ds-dv" id="md-cust-name">—</span></div>
                            <div class="ds-detail-row"><span class="ds-dl">Phone</span><span class="ds-dv" id="md-cust-phone">—</span></div>
                            <div class="ds-detail-row"><span class="ds-dl">Invoice</span><span class="ds-dv" id="md-invoice">—</span></div>
                        </div>
                    </div>
                    <div class="ds-modal-section">
                        <div class="ds-section-title">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                            Product
                        </div>
                        <div class="ds-detail-rows">
                            <div class="ds-detail-row"><span class="ds-dl">Name</span><span class="ds-dv" id="md-prod-name">—</span></div>
                            <div class="ds-detail-row"><span class="ds-dl">Variant</span><span class="ds-dv" id="md-variant">—</span></div>
                            <div class="ds-detail-row"><span class="ds-dl">SKU</span><span class="ds-dv ds-mono" id="md-sku">—</span></div>
                            <div class="ds-detail-row"><span class="ds-dl">Warehouse</span><span class="ds-dv" id="md-warehouse">—</span></div>
                        </div>
                    </div>
                </div>

                {{-- Qty Row --}}
                <div class="ds-qty-row">
                    <div class="ds-qty-box ds-qty-box--replaced">
                        <div class="ds-qty-num" id="md-replaced">0</div>
                        <div class="ds-qty-lbl">Replaced</div>
                        <div class="ds-qty-sub">Received defective</div>
                    </div>
                    <div class="ds-qty-box ds-qty-box--repaired">
                        <div class="ds-qty-num" id="md-repaired">0</div>
                        <div class="ds-qty-lbl">Repaired</div>
                        <div class="ds-qty-sub">Returned to stock</div>
                    </div>
                    <div class="ds-qty-box ds-qty-box--scrapped">
                        <div class="ds-qty-num" id="md-scrapped">0</div>
                        <div class="ds-qty-lbl">Scrapped</div>
                        <div class="ds-qty-sub">Disposed, no recovery</div>
                    </div>
                    <div class="ds-qty-box" id="md-pending-box">
                        <div class="ds-qty-num" id="md-pending">0</div>
                        <div class="ds-qty-lbl">Pending</div>
                        <div class="ds-qty-sub">Still in workshop</div>
                    </div>
                </div>

                {{-- Dates --}}
                <div class="ds-modal-grid" style="margin-top:12px;">
                    <div class="ds-modal-section">
                        <div class="ds-section-title">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Dates
                        </div>
                        <div class="ds-detail-rows">
                            <div class="ds-detail-row"><span class="ds-dl">Claim Date</span><span class="ds-dv" id="md-claim-date">—</span></div>
                            <div class="ds-detail-row"><span class="ds-dl">Approved At</span><span class="ds-dv" id="md-approved-at">—</span></div>
                            <div class="ds-detail-row"><span class="ds-dl">Repaired At</span><span class="ds-dv" id="md-repaired-at">—</span></div>
                            <div class="ds-detail-row"><span class="ds-dl">Scrapped At</span><span class="ds-dv" id="md-scrapped-at">—</span></div>
                        </div>
                    </div>
                    <div class="ds-modal-section" id="md-scrap-section" style="display:none;">
                        <div class="ds-section-title">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                            Scrap Info
                        </div>
                        <div class="ds-detail-rows">
                            <div class="ds-detail-row"><span class="ds-dl">Reason</span><span class="ds-dv" id="md-scrap-reason">—</span></div>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div id="md-notes-wrap" class="ds-notes-wrap" style="display:none; margin-top:12px;">
                    <div class="ds-section-title" style="margin-bottom:7px;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Notes
                    </div>
                    <div class="ds-notes-body" id="md-notes"></div>
                </div>

                {{-- Footer --}}
                <div class="ds-modal-footer">
                    <a id="md-warranty-link" href="#" target="_blank" class="ds-modal-link">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        View Full Warranty Claim
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
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
.ds-wrap { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-size: 12.5px; color: var(--c-text); padding: 16px; max-width: 100%; }
.ds-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid var(--c-border); gap: 12px; flex-wrap: wrap; }
.ds-header-left { display: flex; align-items: center; gap: 11px; }
.ds-header-icon { width: 38px; height: 38px; background: #fff7ed; border-radius: 9px; display: flex; align-items: center; justify-content: center; color: var(--c-brand); flex-shrink: 0; }
.ds-title { font-size: 17px; font-weight: 700; margin: 0 0 2px; letter-spacing: -.3px; }
.ds-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }
.ds-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 14px; }
.ds-stat { display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: var(--c-white); border: 1px solid var(--c-border); border-radius: var(--r); box-shadow: var(--c-shadow); position: relative; overflow: hidden; transition: box-shadow .2s, transform .15s; }
.ds-stat:hover { box-shadow: var(--c-shadow2); transform: translateY(-1px); }
.ds-stat::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: var(--r) var(--r) 0 0; }
.ds-stat--blue::before   { background: #3b82f6; }
.ds-stat--orange::before { background: #f97316; }
.ds-stat--green::before  { background: #22c55e; }
.ds-stat--red::before    { background: #ef4444; }
.ds-stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ds-stat--blue .ds-stat-icon   { background: #dbeafe; color: #2563eb; }
.ds-stat--orange .ds-stat-icon { background: #fff7ed; color: #f97316; }
.ds-stat--green .ds-stat-icon  { background: #dcfce7; color: #16a34a; }
.ds-stat--red .ds-stat-icon    { background: #fee2e2; color: #dc2626; }
.ds-stat-label { font-size: 10.5px; color: var(--c-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
.ds-stat-value { font-size: 17px; font-weight: 800; color: var(--c-text); letter-spacing: -.4px; margin-bottom: 2px; }
.ds-stat-hint  { font-size: 10px; color: var(--c-muted); }
.ds-filters { background: var(--c-white); border: 1px solid var(--c-border); border-radius: var(--r); padding: 11px 14px 13px; margin-bottom: 12px; box-shadow: var(--c-shadow); }
.ds-filters-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.ds-filters-title { font-size: 11px; font-weight: 700; color: var(--c-text2); display: flex; align-items: center; gap: 5px; text-transform: uppercase; letter-spacing: .5px; }
.ds-clear-filters { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; color: var(--c-brand); text-decoration: none; font-weight: 500; transition: color .15s; }
.ds-clear-filters:hover { color: var(--c-brand-d); text-decoration: underline; }
.ds-filter-row { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; }
.ds-filter-group { display: flex; flex-direction: column; gap: 4px; min-width: 120px; }
.ds-filter-search { min-width: 220px; }
.ds-filter-label { font-size: 9.5px; font-weight: 700; color: var(--c-muted); text-transform: uppercase; letter-spacing: .5px; }
.ds-input, .ds-select { height: 31px; padding: 0 9px; border: 1px solid var(--c-border); border-radius: var(--r-sm); font-size: 11.5px; color: var(--c-text); background: var(--c-bg); transition: border-color .15s, background .15s, box-shadow .15s; outline: none; }
.ds-input:focus, .ds-select:focus { border-color: var(--c-brand); background: var(--c-white); box-shadow: 0 0 0 3px rgba(249,115,22,.1); }
.ds-input-icon-wrap { position: relative; }
.ds-input-icon { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); color: var(--c-muted); pointer-events: none; }
.ds-input-with-icon { padding-left: 28px; }
.ds-filter-btns { display: flex; gap: 6px; align-items: flex-end; margin-left: auto; }
.ds-btn-filter { display: inline-flex; align-items: center; gap: 5px; height: 31px; padding: 0 14px; background: var(--c-text); color: #fff; border: none; border-radius: var(--r-sm); font-size: 11.5px; font-weight: 600; cursor: pointer; transition: background .15s; }
.ds-btn-filter:hover { background: #1f2937; }
.ds-btn-reset { display: inline-flex; align-items: center; height: 31px; padding: 0 12px; background: var(--c-bg); color: var(--c-muted); border: 1px solid var(--c-border); border-radius: var(--r-sm); font-size: 11.5px; text-decoration: none; transition: all .15s; font-weight: 500; }
.ds-btn-reset:hover { background: #f3f4f6; color: var(--c-text); }
.ds-table-card { background: var(--c-white); border: 1px solid var(--c-border); border-radius: var(--r); box-shadow: var(--c-shadow); overflow: hidden; }
.ds-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.ds-table { width: 100%; border-collapse: collapse; font-size: 12px; min-width: 1300px; }
.ds-table th { padding: 9px 8px; background: #f3f4f6; font-size: 9.5px; font-weight: 700; color: var(--c-muted); text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid var(--c-border); white-space: nowrap; text-align: left; position: sticky; top: 0; z-index: 1; }
.ds-table td { padding: 9px 8px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; color: var(--c-text2); }
.ds-table tr:last-child td { border-bottom: none; }
.ds-tr { transition: background .12s; }
.ds-tr:hover td { background: #fafafa; }
.tc-no        { width: 40px; text-align: center; }
.tc-date      { width: 78px; }
.tc-claim     { width: 112px; }
.tc-product   { width: 165px; }
.tc-variant   { width: 100px; }
.tc-customer  { width: 135px; }
.tc-warehouse { width: 95px; }
.tc-qty       { width: 72px; text-align: center; }
.tc-status    { width: 110px; }
.tc-act       { width: 52px; text-align: center; }
.td-serial { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--c-bg); border-radius: 4px; font-size: 10px; color: var(--c-muted); font-weight: 600; }
.td-muted { color: var(--c-muted); font-size: 10.5px; }
.td-date-main { font-size: 11.5px; color: var(--c-text2); white-space: nowrap; font-weight: 500; }
.td-date-sub  { font-size: 9.5px; color: var(--c-muted); }
.td-party-name { font-weight: 500; color: var(--c-text); font-size: 12px; margin-bottom: 2px; max-width: 125px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.td-party-phone { font-size: 10px; color: var(--c-muted); }
.td-product-name { font-weight: 500; color: var(--c-text); font-size: 12px; margin-bottom: 2px; max-width: 155px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.td-product-sku { font-size: 9px; color: var(--c-muted); font-family: 'Courier New', monospace; }
.ds-claim-chip { display: inline-block; padding: 3px 6px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 4px; font-size: 10px; font-weight: 600; color: #0369a1; white-space: nowrap; font-family: 'Courier New', monospace; letter-spacing: .2px; max-width: 105px; overflow: hidden; text-overflow: ellipsis; }
.ds-warehouse-badge { display: inline-block; padding: 3px 8px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 4px; font-size: 10px; font-weight: 600; color: #c2410c; white-space: nowrap; max-width: 90px; overflow: hidden; text-overflow: ellipsis; }
.ds-variant-badge { display: inline-block; padding: 2px 7px; background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 4px; font-size: 10px; font-weight: 600; color: #5b21b6; white-space: nowrap; max-width: 95px; overflow: hidden; text-overflow: ellipsis; }
.ds-qty-chip { display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 22px; padding: 0 6px; border-radius: 4px; font-size: 11px; font-weight: 700; }
.ds-qty--replaced { background: #dbeafe; color: #1d4ed8; }
.ds-qty--repaired { background: #dcfce7; color: #166534; }
.ds-qty--scrapped { background: #fee2e2; color: #991b1b; }
.ds-qty--pending  { background: #fef3c7; color: #92400e; }
.ds-qty--zero     { background: #f3f4f6; color: #9ca3af; }
.ds-badge { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; min-width: 80px; text-align: center; }
.ds-badge--pending  { background: #fef3c7; color: #92400e; }
.ds-badge--repaired { background: #d1fae5; color: #065f46; }
.ds-badge--scrapped { background: #fee2e2; color: #991b1b; }
.ds-act-grp { display: flex; gap: 4px; align-items: center; justify-content: center; }
.ds-act { width: 28px; height: 28px; border: none; border-radius: var(--r-sm); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all .15s; flex-shrink: 0; }
.ds-act--view { background: #dbeafe; color: #2563eb; }
.ds-act--view:hover { background: #bfdbfe; transform: scale(1.05); }
.ds-empty-cell { padding: 52px 20px; text-align: center; }
.ds-empty { display: inline-flex; flex-direction: column; align-items: center; gap: 8px; }
.ds-empty-icon { width: 56px; height: 56px; background: var(--c-bg); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #d1d5db; margin-bottom: 4px; }
.ds-empty-title { font-size: 13px; font-weight: 600; color: var(--c-text2); margin: 0; }
.ds-empty-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }
.ds-empty-sub a { color: var(--c-brand); }
.ds-pagination { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-top: 1px solid var(--c-border); background: var(--c-bg); flex-wrap: wrap; gap: 8px; }
.ds-page-info { font-size: 10.5px; color: var(--c-muted); }
.ds-page-info strong { color: var(--c-text2); }
.ds-pages { display: flex; gap: 3px; align-items: center; flex-wrap: wrap; }
.ds-pg { display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 28px; padding: 0 5px; border: 1px solid var(--c-border); border-radius: var(--r-sm); font-size: 11px; color: var(--c-text2); text-decoration: none; background: var(--c-white); transition: all .12s; font-weight: 500; }
.ds-pg:hover { background: #f3f4f6; border-color: #d1d5db; }
.ds-pg--active { background: var(--c-brand); color: #fff; border-color: var(--c-brand); font-weight: 700; }
.ds-pg--dis { color: #d1d5db; background: var(--c-bg); cursor: default; pointer-events: none; }
.ds-pg-dots { font-size: 11px; color: var(--c-muted); padding: 0 2px; }
.ds-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); backdrop-filter: blur(2px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 16px; animation: dsOverlayIn .15s ease; }
@keyframes dsOverlayIn { from { opacity: 0; } to { opacity: 1; } }
.ds-modal { background: var(--c-white); border-radius: 10px; box-shadow: 0 20px 60px rgba(0,0,0,.18), 0 4px 16px rgba(0,0,0,.08); width: 100%; max-width: 700px; max-height: 90vh; overflow-y: auto; animation: dsModalIn .2s cubic-bezier(.34,1.3,.64,1); }
@keyframes dsModalIn { from { opacity: 0; transform: scale(.95) translateY(8px); } to { opacity: 1; transform: none; } }
.ds-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--c-border); background: #fafafa; border-radius: 10px 10px 0 0; gap: 12px; }
.ds-modal-header-left { display: flex; align-items: center; gap: 10px; }
.ds-modal-icon { width: 34px; height: 34px; border-radius: 8px; background: #fff7ed; color: var(--c-brand); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ds-modal-title { font-size: 13.5px; font-weight: 700; color: var(--c-text); }
.ds-modal-sub   { font-size: 10.5px; color: var(--c-muted); margin-top: 1px; font-family: 'Courier New', monospace; }
.ds-modal-close { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--c-border); background: var(--c-white); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .15s; flex-shrink: 0; }
.ds-modal-close:hover { background: #fee2e2; border-color: #fecaca; color: #dc2626; }
.ds-modal-body { padding: 16px 18px; }
.ds-modal-loading { display: flex; align-items: center; gap: 8px; justify-content: center; padding: 32px 0; color: var(--c-muted); font-size: 12px; }
.ds-spin { animation: dsSpin 1s linear infinite; color: var(--c-brand); }
@keyframes dsSpin { to { transform: rotate(360deg); } }
.ds-status-banner { border-radius: 7px; padding: 10px 14px; margin-bottom: 14px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.ds-status-banner--pending  { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.ds-status-banner--repaired { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.ds-status-banner--scrapped { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.ds-modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.ds-modal-section { border: 1px solid var(--c-border); border-radius: var(--r); padding: 12px; background: #fafafa; }
.ds-section-title { font-size: 9.5px; font-weight: 700; color: var(--c-muted); text-transform: uppercase; letter-spacing: .5px; display: flex; align-items: center; gap: 5px; margin-bottom: 10px; padding-bottom: 7px; border-bottom: 1px solid var(--c-border); }
.ds-detail-rows { display: flex; flex-direction: column; gap: 7px; }
.ds-detail-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
.ds-dl { font-size: 10.5px; color: var(--c-muted); font-weight: 500; white-space: nowrap; flex-shrink: 0; }
.ds-dv { font-size: 11.5px; color: var(--c-text2); font-weight: 500; text-align: right; word-break: break-word; }
.ds-mono { font-family: 'Courier New', monospace; font-size: 10.5px; }
.ds-qty-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 12px; }
.ds-qty-box { border: 1px solid var(--c-border); border-radius: var(--r); padding: 12px 10px; text-align: center; background: #fafafa; }
.ds-qty-box--replaced { border-color: #bfdbfe; background: #eff6ff; }
.ds-qty-box--repaired { border-color: #a7f3d0; background: #f0fdf4; }
.ds-qty-box--scrapped { border-color: #fecaca; background: #fef2f2; }
.ds-qty-box--pending  { border-color: #fde68a; background: #fffbeb; }
.ds-qty-num { font-size: 22px; font-weight: 800; letter-spacing: -.5px; margin-bottom: 2px; }
.ds-qty-box--replaced .ds-qty-num { color: #1d4ed8; }
.ds-qty-box--repaired .ds-qty-num { color: #166534; }
.ds-qty-box--scrapped .ds-qty-num { color: #991b1b; }
.ds-qty-box--pending  .ds-qty-num { color: #92400e; }
.ds-qty-lbl { font-size: 11px; font-weight: 700; color: var(--c-text2); }
.ds-qty-sub { font-size: 9.5px; color: var(--c-muted); margin-top: 2px; }
.ds-notes-wrap { border: 1px solid var(--c-border); border-radius: var(--r); padding: 12px; background: #fafafa; }
.ds-notes-body { font-size: 12px; color: var(--c-text2); line-height: 1.6; }
.ds-modal-footer { display: flex; justify-content: flex-end; padding-top: 12px; margin-top: 4px; border-top: 1px solid var(--c-border); }
.ds-modal-link { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; color: #2563eb; font-weight: 500; text-decoration: none; transition: color .15s; }
.ds-modal-link:hover { color: #1d4ed8; text-decoration: underline; }
@media (max-width: 1100px) { .ds-stats { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) {
    .ds-modal-grid { grid-template-columns: 1fr; }
    .ds-qty-row { grid-template-columns: repeat(2, 1fr); }
    .ds-filter-group { flex: 1 1 calc(50% - 6px); min-width: calc(50% - 6px); }
    .ds-filter-search { flex: 1 1 100%; min-width: 100%; }
    .ds-filter-btns { width: 100%; }
}
@media (max-width: 480px) {
    .ds-wrap { padding: 10px; }
    .ds-stats { grid-template-columns: 1fr 1fr; }
    .ds-filter-group { min-width: 100%; flex: 1 1 100%; }
    .ds-pagination { flex-direction: column; align-items: flex-start; }
}
</style>
@endpush

@push('scripts')
<script>
const warrantyShowBase = "{{ route('admin.warranty.show', ':id') }}";

function openDetailModal(id) {
    const overlay = document.getElementById('dsModal');
    document.getElementById('dsModalClaimNo').textContent = '—';
    document.getElementById('dsModalLoading').style.display = 'flex';
    document.getElementById('dsModalContent').style.display = 'none';
    overlay.style.display = 'flex';

    fetch(`/admin/defective-stock/${id}/detail`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error('Failed');
        populateModal(data.claim);
    })
    .catch(() => {
        document.getElementById('dsModalLoading').innerHTML =
            '<span style="color:#ef4444">Error loading data. Please try again.</span>';
    });
}

function populateModal(c) {
    function fmtDate(val) {
        if (!val) return '—';
        // Handle MongoDB date format
        if (val && typeof val === 'object' && val.$date) {
            try {
                const d = new Date(val.$date);
                return isNaN(d) ? '—' : d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
            } catch (e) {
                return '—';
            }
        }
        // Handle regular date strings
        try {
            const d = new Date(val);
            return isNaN(d) ? '—' : d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        } catch (e) {
            return '—';
        }
    }

    function esc(str) {
        if (!str && str !== 0) return '—';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // Safety check - agar c array hai to pehla element le
    if (Array.isArray(c)) {
        c = c[0] || {};
    }

    const dss = c.defective_stock_status ?? 'pending_repair';

    const dssLabels = {
        pending_repair: 'Pending Repair — awaiting workshop action',
        repaired:       'Repaired — returned to stock',
        scrapped:       'Scrapped — no recovery possible',
    };

    const dssIcons = {
        pending_repair: `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`,
        repaired:       `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>`,
        scrapped:       `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>`,
    };

    document.getElementById('dsModalClaimNo').textContent = c.warranty_claim_number ?? '—';

    const bannerCls = dss === 'pending_repair' ? 'pending' : dss;
    const banner = document.getElementById('dsStatusBanner');
    banner.className = `ds-status-banner ds-status-banner--${bannerCls}`;
    banner.innerHTML = (dssIcons[dss] ?? '') + (dssLabels[dss] ?? dss);

    // Party data
    const party = c.party || {};
    document.getElementById('md-cust-name').textContent = esc(party.name);
    document.getElementById('md-cust-phone').textContent = esc(party.phone);

    // Invoice data
    const inv = c.sales_invoice || {};
    const invId = inv._id?.$oid ?? inv._id ?? inv.id;
    document.getElementById('md-invoice').innerHTML = invId && inv.invoice_number
        ? `<a href="/admin/sales/${invId}" target="_blank" style="color:#2563eb;font-weight:500;text-decoration:none;">${esc(inv.invoice_number)}</a>`
        : '—';

    // Product item data - SIMPLE + VARIANT DONO YAHI SE AA RAHE HAIN
   const item = c.sales_invoice_item || {};

    document.getElementById('md-prod-name').textContent = esc(item.product_name);
    document.getElementById('md-variant').textContent =
        item.variant_name ? esc(item.variant_name) : '—';

    document.getElementById('md-sku').textContent = esc(item.sku);
    document.getElementById('md-warehouse').textContent = esc(c.warehouse?.name);

    // Quantities
    const replaced = parseInt(c.replaced_qty) || 0;
    const repaired = parseInt(c.repaired_qty) || 0;
    const scrapped = parseInt(c.scrapped_qty) || 0;
    const pending  = Math.max(0, replaced - repaired - scrapped);

    document.getElementById('md-replaced').textContent = replaced;
    document.getElementById('md-repaired').textContent = repaired;
    document.getElementById('md-scrapped').textContent = scrapped;
    document.getElementById('md-pending').textContent  = pending;

    const pendingBox = document.getElementById('md-pending-box');
    pendingBox.className = pending > 0 ? 'ds-qty-box ds-qty-box--pending' : 'ds-qty-box';
    document.getElementById('md-pending').style.color = pending > 0 ? '#92400e' : '#9ca3af';

    // Dates
    document.getElementById('md-claim-date').textContent  = fmtDate(c.claim_date);
    document.getElementById('md-approved-at').textContent = fmtDate(c.approved_at);
    document.getElementById('md-repaired-at').textContent = c.repaired_at ? fmtDate(c.repaired_at) : '—';
    document.getElementById('md-scrapped-at').textContent = c.scrapped_at ? fmtDate(c.scrapped_at) : '—';

    // Scrap section
    const scrapSection = document.getElementById('md-scrap-section');
    if (c.scrap_reason) {
        document.getElementById('md-scrap-reason').textContent = esc(c.scrap_reason);
        scrapSection.style.display = 'block';
    } else {
        scrapSection.style.display = 'none';
    }

    // Notes
    const notesWrap = document.getElementById('md-notes-wrap');
    if (c.notes) {
        document.getElementById('md-notes').textContent = esc(c.notes);
        notesWrap.style.display = 'block';
    } else {
        notesWrap.style.display = 'none';
    }

    // Warranty link
    const claimId = c._id?.$oid ?? c._id ?? c.id;
    document.getElementById('md-warranty-link').href = warrantyShowBase.replace(':id', claimId);

    document.getElementById('dsModalLoading').style.display = 'none';
    document.getElementById('dsModalContent').style.display = 'block';
}
function closeDsModal(event) {
    if (event && event.target !== document.getElementById('dsModal')) return;
    document.getElementById('dsModal').style.display = 'none';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('dsModal').style.display = 'none';
});
</script>
@endpush
@endsection
