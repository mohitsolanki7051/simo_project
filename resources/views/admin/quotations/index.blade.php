{{-- resources/views/admin/quotations/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quotations - Admin Panel')
@section('header-title', 'Quotations / Estimates')

@section('content')
<div class="si-wrap">
    <div id="alertBox"></div>

    {{-- ── Header ── --}}
    <div class="si-header">
        <div class="si-header-left">
            <div class="si-header-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
            <div>
                <h1 class="si-title">Quotations</h1>
                <p class="si-sub">Manage and track all your quotations</p>
            </div>
        </div>
        <a href="{{ route('admin.quotations.create') }}" class="si-btn-create">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Quotation
        </a>
    </div>

    {{-- ── Stats ── --}}
    <div class="si-stats">
        <div class="si-stat si-stat--blue">
            <div class="si-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="si-stat-content">
                <div class="si-stat-label">Total Quotations</div>
                <div class="si-stat-value">{{ $totalQuotations }}</div>
                <div class="si-stat-hint">All quotations</div>
            </div>
        </div>
        <div class="si-stat si-stat--green">
            <div class="si-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="si-stat-content">
                <div class="si-stat-label">Total Amount</div>
                <div class="si-stat-value">₹ {{ number_format($totalAmount, 2) }}</div>
                <div class="si-stat-hint">Sum of all quotations</div>
            </div>
        </div>
        <div class="si-stat si-stat--gray">
            <div class="si-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="12" y2="13"/></svg>
            </div>
            <div class="si-stat-content">
                <div class="si-stat-label">Draft</div>
                <div class="si-stat-value">{{ $draftCount }}</div>
                <div class="si-stat-hint">Not sent yet</div>
            </div>
        </div>
        <div class="si-stat si-stat--blue">
            <div class="si-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            </div>
            <div class="si-stat-content">
                <div class="si-stat-label">Sent</div>
                <div class="si-stat-value">{{ $sentCount }}</div>
                <div class="si-stat-hint">Sent to customers</div>
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="si-filters">
        <div class="si-filters-header">
            <div class="si-filters-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filters
            </div>
            @if(request()->anyFilled(['date_from','date_to','quotation_number','party_id','status','warehouse_id']))
            <a href="{{ route('admin.quotations.index') }}" class="si-clear-filters">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Clear all filters
            </a>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.quotations.index') }}" id="filterForm">
            <div class="si-filter-row">

                {{-- Date From --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Date From</label>
                    <input type="date" name="date_from" class="si-input" value="{{ request('date_from') }}">
                </div>

                {{-- Date To --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Date To</label>
                    <input type="date" name="date_to" class="si-input" value="{{ request('date_to') }}">
                </div>

                {{-- Quotation Number --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Quotation No.</label>
                    <div class="si-input-icon-wrap">
                        <svg class="si-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="quotation_number" class="si-input si-input-with-icon"
                               placeholder="Search quotation..." value="{{ request('quotation_number') }}">
                    </div>
                </div>

                {{-- Party --}}
                <div class="si-filter-group">
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
                {{-- Quotation Type Filter --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Quotation Type</label>
                    <select name="invoice_type" class="si-select">
                        <option value="">All Types</option>
                        <option value="gst" {{ request('invoice_type') == 'gst' ? 'selected' : '' }}>GST Quotation</option>
                        <option value="cash" {{ request('invoice_type') == 'cash' ? 'selected' : '' }}>Cash Memo</option>
                    </select>
                </div>

                {{-- Status --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Status</label>
                    <select name="status" class="si-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                {{-- Warehouse Filter --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Warehouse</label>
                    <select name="warehouse_id" class="si-select">
                        <option value="">All Warehouses</option>
                        @foreach(\App\Models\Warehouse::active()->get() as $wh)
                            <option value="{{ $wh->_id }}" {{ request('warehouse_id') == $wh->_id ? 'selected' : '' }}>
                                {{ $wh->name }}{{ $wh->is_main ? ' (Main)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="si-filter-btns">
                    <button type="submit" class="si-btn-filter">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Apply
                    </button>
                    <a href="{{ route('admin.quotations.index') }}" class="si-btn-reset">Reset</a>
                </div>

            </div>
        </form>
    </div>

    {{-- ── Table ── --}}
    <div class="si-table-card">

        <div class="si-table-topbar">
            <div class="si-table-count">
                <strong>{{ $quotations->total() }}</strong> quotation{{ $quotations->total() != 1 ? 's' : '' }}
                @if(request()->anyFilled(['date_from','date_to','quotation_number','party_id','status','invoice_type','warehouse_id']))
                    <a href="{{ route('admin.quotations.index') }}" class="si-clear-filters">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Clear all filters
                    </a>
                @endif
            </div>
            <div class="si-table-topbar-right">
                <div class="si-page-info-top">
                    Showing {{ $quotations->firstItem() ?? 0 }}–{{ $quotations->lastItem() ?? 0 }} of {{ $quotations->total() }}
                </div>
            </div>
        </div>

        <div class="si-table-wrap">
            <table class="si-table">
                <thead>
                    <tr>
                        <th class="tc-no">S. No.</th>
                        <th class="tc-date">Date</th>
                        <th class="tc-inv">Quotation No.</th>
                        <th class="tc-valid">Valid Till</th>
                        <th class="tc-party">Party</th>
                        <th class="tc-type">Quotation Type</th>
                        <th class="tc-wh">Warehouse</th>
                        <th class="tc-amount">Amount</th>
                        <th class="tc-status">Status</th>
                        <th class="tc-act">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $i => $quotation)
                    <tr class="si-tr">
                        <td class="tc-no">
                            <span class="td-serial">{{ ($quotations->currentPage() - 1) * $quotations->perPage() + $i + 1 }}</span>
                        </td>
                        <td class="tc-date">
                            <div class="td-date-main">{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d M Y') }}</div>
                            <div class="td-date-sub">{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('D') }}</div>
                        </td>
                        <td class="tc-inv">
                            <span class="si-inv-chip">{{ $quotation->quotation_number }}</span>
                        </td>
                        <td class="tc-valid">
                            @if($quotation->valid_till)
                                @php
                                    $valid = \Carbon\Carbon::parse($quotation->valid_till);
                                    $today = \Carbon\Carbon::today();
                                    $diff = (int) $today->diffInDays($valid, false);
                                    if ($quotation->status === 'expired' || $diff < 0) {
                                        $cls = 'si-due--over'; $txt = 'Expired';
                                    } elseif ($diff === 0) {
                                        $cls = 'si-due--today'; $txt = 'Today';
                                    } else {
                                        $cls = 'si-due--ok'; $txt = $valid->format('d/m/Y');
                                    }
                                @endphp
                                <span class="si-due {{ $cls }}">{{ $txt }}</span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td class="tc-party">
                            <div class="td-party-name" title="{{ optional($quotation->party)->name ?? 'N/A' }}">
                                {{ optional($quotation->party)->name ?? 'N/A' }}
                            </div>
                            @php
                                $partyType = optional($quotation->party)->party_type ?? 'customer';
                            @endphp
                            <span class="si-ptype si-ptype--{{ $partyType }}">{{ ucfirst($partyType) }}</span>
                        </td>
                        <td class="tc-type">
                            @if($quotation->invoice_type === 'gst')
                                <span class="si-type-badge si-type--gst">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                    GST
                                </span>
                            @elseif($quotation->invoice_type === 'cash')
                                <span class="si-type-badge si-type--cash">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                    Cash
                                </span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td class="tc-wh">
                            <span class="si-wh-badge">
                                {{ optional($quotation->warehouse)->name ?? '—' }}
                            </span>
                        </td>
                        <td class="tc-amount">
                            <div class="amount-display">
                                <span class="amount-main">₹ {{ number_format((float) $quotation->grand_total, 2) }}</span>
                            </div>
                        </td>
                        <td class="tc-status">
                            @php
                                $statusMap = [
                                    'draft'    => ['cls' => 'status-draft', 'label' => 'Draft'],
                                    'sent'     => ['cls' => 'status-sent', 'label' => 'Sent'],
                                    'accepted' => ['cls' => 'status-accepted', 'label' => 'Accepted'],
                                    'rejected' => ['cls' => 'status-rejected', 'label' => 'Rejected'],
                                    'expired'  => ['cls' => 'status-expired', 'label' => 'Expired'],
                                ];
                                $sm = $statusMap[$quotation->status] ?? $statusMap['draft'];
                            @endphp
                            <span class="si-inv-status {{ $sm['cls'] }}">{{ $sm['label'] }}</span>
                        </td>
                        <td class="tc-act">
                            <div class="si-act-grp">
                                <a href="{{ route('admin.quotations.show', $quotation->id) }}"
                                   class="si-act si-act--view" title="View Quotation">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>

                                @if($quotation->status === 'draft')
                                    <a href="{{ route('admin.quotations.edit', $quotation->id) }}"
                                       class="si-act si-act--edit" title="Edit Quotation">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>
                                    <button type="button" class="si-act si-act--del del-btn" data-id="{{ $quotation->id }}" title="Delete Quotation">
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
                        <td colspan="10" class="si-empty-cell">
                            <div class="si-empty">
                                <div class="si-empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                        <line x1="16" y1="13" x2="8" y2="13"/>
                                        <line x1="16" y1="17" x2="8" y2="17"/>
                                    </svg>
                                </div>
                                <p class="si-empty-title">No quotations found</p>
                                <p class="si-empty-sub">
                                    @if(request()->anyFilled(['date_from','date_to','quotation_number','party_id','status','warehouse_id']))
                                        Try adjusting your filters or <a href="{{ route('admin.quotations.index') }}">clear all</a>
                                    @else
                                        Get started by creating your first quotation
                                    @endif
                                </p>
                                <a href="{{ route('admin.quotations.create') }}" class="si-btn-create" style="margin-top:4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Create Quotation
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($quotations->hasPages())
        <div class="si-pagination">
            <div class="si-page-info">
                Page <strong>{{ $quotations->currentPage() }}</strong> of <strong>{{ $quotations->lastPage() }}</strong>
                &nbsp;·&nbsp; {{ $quotations->total() }} total records
            </div>
            <div class="si-pages">
                @if($quotations->onFirstPage())
                    <span class="si-pg si-pg--dis">«</span>
                    <span class="si-pg si-pg--dis">‹</span>
                @else
                    <a href="{{ $quotations->url(1) }}" class="si-pg" title="First">«</a>
                    <a href="{{ $quotations->previousPageUrl() }}" class="si-pg" title="Previous">‹</a>
                @endif

                @php
                    $cur   = $quotations->currentPage();
                    $last  = $quotations->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $quotations->url(1) }}" class="si-pg">1</a>
                    @if($start > 2) <span class="si-pg-dots">…</span> @endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="si-pg si-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $quotations->url($p) }}" class="si-pg">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $last)
                    @if($end < $last - 1) <span class="si-pg-dots">…</span> @endif
                    <a href="{{ $quotations->url($last) }}" class="si-pg">{{ $last }}</a>
                @endif

                @if($quotations->hasMorePages())
                    <a href="{{ $quotations->nextPageUrl() }}" class="si-pg" title="Next">›</a>
                    <a href="{{ $quotations->url($last) }}" class="si-pg" title="Last">»</a>
                @else
                    <span class="si-pg si-pg--dis">›</span>
                    <span class="si-pg si-pg--dis">»</span>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>{{-- /.si-wrap --}}

{{-- ── Delete Modal ── --}}
<div class="si-modal" id="delModal">
    <div class="si-modal-overlay" onclick="closeDelModal()"></div>
    <div class="si-modal-box">
        <div class="si-modal-head">
            <div class="si-modal-ico" style="background: #ef4444;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
            </div>
            <div>
                <div class="si-modal-title">Delete Quotation</div>
                <div class="si-modal-sub">This action cannot be undone</div>
            </div>
            <button class="si-modal-close" onclick="closeDelModal()">×</button>
        </div>
        <div class="si-modal-body">
            <p>Are you sure you want to permanently delete this quotation?</p>
        </div>
        <div class="si-modal-foot">
            <button class="si-btn-cancel" onclick="closeDelModal()">Cancel</button>
            <button class="si-btn-del-confirm" id="confirmDel">Delete Quotation</button>
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
.si-wrap {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 12.5px;
    color: var(--c-text);
    padding: 16px;
    max-width: 100%;
}

/* ─── Header ──────────────────────────────────────────────── */
.si-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--c-border);
    gap: 12px;
    flex-wrap: wrap;
}
.si-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
}
.si-header-icon {
    width: 38px; height: 38px;
    background: var(--c-brand-l);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    color: var(--c-brand);
    flex-shrink: 0;
}
.si-title { font-size: 17px; font-weight: 700; margin: 0 0 2px; letter-spacing: -.3px; }
.si-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }

.si-btn-create {
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
.si-btn-create:hover {
    background: var(--c-brand-d);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(249,115,22,.35);
}

/* ─── Stats ───────────────────────────────────────────────── */
.si-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 14px;
}
.si-stat {
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
.si-stat:hover {
    box-shadow: var(--c-shadow2);
    transform: translateY(-1px);
}
.si-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: var(--r) var(--r) 0 0;
}
.si-stat--blue::before   { background: #3b82f6; }
.si-stat--green::before  { background: #22c55e; }
.si-stat--gray::before   { background: #6b7280; }

.si-stat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.si-stat--blue .si-stat-icon   { background: #dbeafe; color: #2563eb; }
.si-stat--green .si-stat-icon  { background: #dcfce7; color: #16a34a; }
.si-stat--gray .si-stat-icon   { background: #f3f4f6; color: #4b5563; }

.si-stat-label { font-size: 10.5px; color: var(--c-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
.si-stat-value { font-size: 17px; font-weight: 800; color: var(--c-text); letter-spacing: -.4px; margin-bottom: 2px; }
.si-stat-hint  { font-size: 10px; color: var(--c-muted); }

/* ─── Filters ─────────────────────────────────────────────── */
.si-filters {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    padding: 11px 14px 13px;
    margin-bottom: 12px;
    box-shadow: var(--c-shadow);
}
.si-filters-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.si-filters-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--c-text2);
    display: flex;
    align-items: center;
    gap: 5px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.si-clear-filters {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    color: var(--c-brand);
    text-decoration: none;
    font-weight: 500;
    transition: color .15s;
}
.si-clear-filters:hover { color: var(--c-brand-d); text-decoration: underline; }

.si-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}
.si-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 140px;
}
.si-filter-label {
    font-size: 9.5px;
    font-weight: 700;
    color: var(--c-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.si-input, .si-select {
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
.si-input:focus, .si-select:focus {
    border-color: var(--c-brand);
    background: var(--c-white);
    box-shadow: 0 0 0 3px rgba(249,115,22,.1);
}
.si-input-icon-wrap { position: relative; }
.si-input-icon {
    position: absolute;
    left: 9px; top: 50%;
    transform: translateY(-50%);
    color: var(--c-muted);
    pointer-events: none;
}
.si-input-with-icon { padding-left: 28px; }

.si-filter-btns {
    display: flex;
    gap: 6px;
    align-items: flex-end;
}
.si-btn-filter {
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
.si-btn-filter:hover { background: #1f2937; }
.si-btn-reset {
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
.si-btn-reset:hover { background: #f3f4f6; color: var(--c-text); }

/* ─── Table Card ──────────────────────────────────────────── */
.si-table-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    box-shadow: var(--c-shadow);
}
.si-table-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-bottom: 1px solid var(--c-border);
    background: var(--c-bg);
    flex-wrap: wrap;
    gap: 6px;
}
.si-table-count {
    font-size: 11.5px;
    color: var(--c-muted);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}
.si-table-count strong { color: var(--c-text); font-weight: 700; }
.si-filtered-pill {
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
.si-page-info-top { font-size: 10.5px; color: var(--c-muted); }
.si-table-topbar-right { display: flex; align-items: center; gap: 8px; }

/* ─── Table ───────────────────────────────────────────────── */
.si-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.si-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 1100px;
}
.si-table th {
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
.si-table td {
    padding: 9px 11px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    color: var(--c-text2);
}
.si-table tr:last-child td { border-bottom: none; }
.si-tr { transition: background .12s; }
.si-tr:hover td { background: #fafafa; }

/* Quotation Type Badge */
.si-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    white-space: nowrap;
}
.si-type--gst  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.si-type--cash { background: #fefce8; color: #a16207; border: 1px solid #fde68a; }

/* Column widths adjust karo */
.tc-no        { width: 45px;  text-align: center; }
.tc-date      { width: 88px;  }
.tc-inv       { width: 160px; }
.tc-valid     { width: 88px;  }
.tc-party     { width: 100px; }  /* Party name + type ke liye zyada width */
.tc-type      { width: 90px;  }  /* Quotation Type column */
.tc-wh        { width: 100px; }
.tc-amount    { width: 120px; }
.tc-status    { width: 85px;  }
.tc-act       { width: 110px; }

/* Table cell helpers */
.td-serial {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px; height: 22px;
    background: var(--c-bg);
    border-radius: 4px;
    font-size: 10px;
    color: var(--c-muted);
    font-weight: 600;
}
.td-muted    { color: var(--c-muted); font-size: 10.5px; }
.td-date-main{ font-size: 11.5px; color: var(--c-text2); white-space: nowrap; font-weight: 500; }
.td-date-sub { font-size: 9.5px; color: var(--c-muted); }

/* Quotation chip */
.si-inv-chip {
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

/* Due/Valid badge */
.si-due {
    display: inline-flex;
    align-items: center;
    padding: 3px 7px;
    border-radius: 4px;
    font-size: 9.5px;
    font-weight: 700;
    white-space: nowrap;
}
.si-due--ok    { background: #dcfce7; color: #166534; }
.si-due--today { background: #fef3c7; color: #92400e; }
.si-due--over  { background: #fee2e2; color: #991b1b; }

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

.si-ptype {
    display: inline-block;
    padding: 1px 5px;
    border-radius: 3px;
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.si-ptype--customer    { background: #d1fae5; color: #065f46; }
.si-ptype--dealer      { background: #dbeafe; color: #1e40af; }
.si-ptype--distributor { background: #fef3c7; color: #92400e; }

/* Warehouse badge */
.si-wh-badge {
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

/* Amount column */
.amount-display {
    display: flex;
    flex-direction: column;
    line-height: 1.5;
}
.amount-main {
    font-weight: 700;
    color: var(--c-text);
    font-size: 12px;
}
.amount-type {
    font-size: 9px;
    color: var(--c-muted);
    font-weight: 500;
}

/* Status badges */
.si-inv-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    width: fit-content;
}
.status-draft    { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.status-sent     { background: #dbeafe; color: #1d4ed8; }
.status-accepted { background: #d1fae5; color: #065f46; }
.status-rejected { background: #fee2e2; color: #991b1b; }
.status-expired  { background: #fef3c7; color: #92400e; }

/* Actions */
.si-act-grp { display: flex; gap: 4px; align-items: center; }
.si-act {
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
.si-act--view  { background: #dbeafe; color: #2563eb; }
.si-act--view:hover  { background: #bfdbfe; transform: scale(1.05); }
.si-act--edit  { background: #fef3c7; color: #92400e; }
.si-act--edit:hover  { background: #fde68a; transform: scale(1.05); }
.si-act--del   { background: #fee2e2; color: #dc2626; }
.si-act--del:hover   { background: #fecaca; transform: scale(1.05); }
.si-act--print { background: #e0e7ff; color: #4338ca; }
.si-act--print:hover { background: #c7d2fe; transform: scale(1.05); }

/* Empty state */
.si-empty-cell { padding: 52px 20px; text-align: center; }
.si-empty { display: inline-flex; flex-direction: column; align-items: center; gap: 8px; }
.si-empty-icon {
    width: 56px; height: 56px;
    background: var(--c-bg);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: #d1d5db;
    margin-bottom: 4px;
}
.si-empty-title { font-size: 13px; font-weight: 600; color: var(--c-text2); margin: 0; }
.si-empty-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }
.si-empty-sub a { color: var(--c-brand); }

/* ─── Pagination ──────────────────────────────────────────── */
.si-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-top: 1px solid var(--c-border);
    background: var(--c-bg);
    flex-wrap: wrap;
    gap: 8px;
}
.si-page-info { font-size: 10.5px; color: var(--c-muted); }
.si-page-info strong { color: var(--c-text2); }
.si-pages { display: flex; gap: 3px; align-items: center; flex-wrap: wrap; }
.si-pg {
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
.si-pg:hover         { background: #f3f4f6; border-color: #d1d5db; }
.si-pg--active       { background: var(--c-brand); color: #fff; border-color: var(--c-brand); font-weight: 700; }
.si-pg--active:hover { background: var(--c-brand); }
.si-pg--dis          { color: #d1d5db; background: var(--c-bg); cursor: default; pointer-events: none; }
.si-pg-dots          { font-size: 11px; color: var(--c-muted); padding: 0 2px; }

/* ─── Modal ───────────────────────────────────────────────── */
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
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(2px);
}
.si-modal-box {
    position: relative;
    background: var(--c-white);
    border-radius: 10px;
    width: 380px;
    max-width: 92%;
    box-shadow: 0 20px 50px rgba(0,0,0,.15);
    animation: siMIn .2s ease;
}
@keyframes siMIn {
    from { opacity:0; transform: scale(.95) translateY(10px); }
    to   { opacity:1; transform: scale(1) translateY(0); }
}
.si-modal-head {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--c-border);
    background: var(--c-bg);
    border-radius: 10px 10px 0 0;
}
.si-modal-ico {
    width: 34px; height: 34px;
    background: #ef4444;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.si-modal-title { font-size: 13.5px; font-weight: 700; margin-bottom: 1px; }
.si-modal-sub   { font-size: 10.5px; color: var(--c-muted); }
.si-modal-close {
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
.si-modal-close:hover { background: #e5e7eb; color: var(--c-text); }
.si-modal-body { padding: 16px; }
.si-modal-body p { font-size: 12.5px; color: #4b5563; line-height: 1.6; margin: 0; }
.si-modal-foot {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 12px 16px;
    border-top: 1px solid var(--c-border);
    background: #fafafa;
    border-radius: 0 0 10px 10px;
}
.si-btn-cancel {
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
.si-btn-cancel:hover { background: #e5e7eb; }
.si-btn-del-confirm {
    padding: 7px 16px;
    border: none;
    border-radius: var(--r-sm);
    background: #ef4444;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.si-btn-del-confirm:hover { background: #dc2626; }
.si-btn-del-confirm:disabled { opacity: .55; cursor: not-allowed; }

/* ─── Alerts ──────────────────────────────────────────────── */
#alertBox { position: fixed; top: 16px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 7px; }
.si-alert {
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
    animation: siAIn .25s ease;
    min-width: 220px;
    max-width: 320px;
}
@keyframes siAIn {
    from { transform: translateX(110%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.si-alert-success { background: #f0fdf4; color: #166534; border-left: 3px solid #22c55e; }
.si-alert-error   { background: #fef2f2; color: #991b1b; border-left: 3px solid #ef4444; }

/* ─── Responsive ──────────────────────────────────────────── */
@media (max-width: 1024px) {
    .si-stats { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .si-stats { grid-template-columns: 1fr 1fr; }
    .si-filter-group { min-width: calc(50% - 4px); flex: 1 1 calc(50% - 4px); }
    .si-filter-btns { width: 100%; }
}
@media (max-width: 480px) {
    .si-wrap { padding: 10px; }
    .si-stats { grid-template-columns: 1fr; }
    .si-header { flex-direction: column; align-items: flex-start; }
    .si-filter-group { min-width: 100%; flex: 1 1 100%; }
    .si-pagination { flex-direction: column; align-items: flex-start; }
    .si-pages { justify-content: center; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
// ── Delete Modal
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

document.getElementById('confirmDel')?.addEventListener('click', function () {
    if (!delId) return;
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Deleting…';

    fetch('/admin/quotations/' + delId, {
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
            showAlert('Quotation deleted successfully', 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showAlert(data.message || 'Failed to delete', 'error');
            btn.disabled = false;
            btn.textContent = 'Delete Quotation';
        }
    })
    .catch(() => {
        closeDelModal();
        showAlert('Something went wrong', 'error');
        btn.disabled = false;
        btn.textContent = 'Delete Quotation';
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
@endsection
