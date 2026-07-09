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
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
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
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filters
            </div>
            @if(request()->anyFilled(['date_from','date_to','return_number','invoice_number','party_id','status']))
            <a href="{{ route('admin.sales-returns.index') }}" class="sr-clear-filters">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Clear all filters
            </a>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.sales-returns.index') }}" id="filterForm">
            <div class="sr-filter-row">

                {{-- Return Number --}}
                <div class="sr-filter-group full-width-mobile">
                    <label class="sr-filter-label">Return Number</label>
                    <div class="sr-input-icon-wrap">
                        <svg class="sr-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="return_number" class="sr-input sr-input-with-icon" placeholder="Search return..." value="{{ request('return_number') }}">
                    </div>
                </div>

                {{-- Invoice Number --}}
                <div class="sr-filter-group full-width-mobile">
                    <label class="sr-filter-label">Invoice Number</label>
                    <div class="sr-input-icon-wrap">
                        <svg class="sr-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <input type="text" name="invoice_number" class="sr-input sr-input-with-icon" placeholder="Search invoice..." value="{{ request('invoice_number') }}">
                    </div>
                </div>

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
                <div class="sr-filter-btns full-width-mobile">
                    <button type="submit" class="sr-btn-filter">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
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
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        Filtered
                    </span>
                @endif
            </div>
            <div class="sr-table-topbar-right hide-mob">
                <div class="sr-page-info-top">
                    Showing {{ $returns->firstItem() ?? 0 }}–{{ $returns->lastItem() ?? 0 }} of {{ $returns->total() }}
                </div>
            </div>
        </div>

        <div class="sr-table-wrap">
            <table class="sr-table">
                <thead>
                    <tr>
                        <th class="tc-no hide-mob">S. No.</th>
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
                        <td class="tc-no hide-mob">
                            <span class="td-serial">{{ ($returns->currentPage() - 1) * $returns->perPage() + $i + 1 }}</span>
                        </td>
                        
                        <td class="tc-date">
                            <div class="td-date-main">
                                <svg class="mob-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ \Carbon\Carbon::parse($return->return_date)->format('d M Y') }}
                            </div>
                            <div class="td-date-sub hide-mob">{{ \Carbon\Carbon::parse($return->return_date)->format('D') }}</div>
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
                            <div class="td-party-name" title="{{ $partyName }}">
                                <svg class="mob-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                {{ $partyName }}
                            </div>
                            @if($partyType)
                                <span class="sr-ptype sr-ptype--{{ $partyType }} hide-mob">{{ ucfirst($partyType) }}</span>
                            @endif
                        </td>
                        
                        <td class="tc-wh">
                            <span class="sr-wh-badge">
                                {{ optional($return->warehouse)->name ?? '—' }}
                            </span>
                        </td>
                        
                        <td class="tc-qty">
                            <span class="sr-qty-badge">{{ $return->total_return_qty }} Pcs</span>
                        </td>
                        
                        <td class="tc-amount">
                            <div class="amount-display">
                                <span class="amount-main">₹ {{ number_format($return->total_return_amount, 2) }}</span>
                            </div>
                        </td>
                        
                        <td class="tc-credit">
                            @if($return->creditNote)
                                <a href="{{ route('admin.credit-notes.show', $return->creditNote->_id) }}" class="sr-credit-link">
                                    {{ $return->creditNote->credit_note_number }}
                                </a>
                            @else
                                <span class="td-muted hide-mob">—</span>
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
                                <a href="{{ route('admin.sales-returns.show', $return->_id) }}" class="sr-act sr-act--view" title="View Return">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>

                                @if($return->status === 'draft')
                                    <a href="{{ route('admin.sales-returns.edit', $return->_id) }}" class="sr-act sr-act--edit" title="Edit Return">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                    </a>
                                    <button type="button" class="sr-act sr-act--complete complete-btn" data-id="{{ $return->_id }}" title="Complete Return">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    </button>
                                    <button type="button" class="sr-act sr-act--del del-btn" data-id="{{ $return->_id }}" title="Delete Return">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
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
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4v16h16M8 11l4-4 4 4M16 8l-4 4-4-4"/><circle cx="12" cy="12" r="10"/></svg>
                                </div>
                                <p class="sr-empty-title">No returns found</p>
                                <a href="{{ route('admin.sales-returns.create') }}" class="sr-btn-create" style="margin-top:4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
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
            <div class="sr-page-info hide-mob">
                Page <strong>{{ $returns->currentPage() }}</strong> of <strong>{{ $returns->lastPage() }}</strong>
                &nbsp;·&nbsp; {{ $returns->total() }} total records
            </div>
            <div class="sr-pages">
                @if($returns->onFirstPage())
                    <span class="sr-pg sr-pg--dis hide-mob">«</span>
                    <span class="sr-pg sr-pg--dis">‹</span>
                @else
                    <a href="{{ $returns->url(1) }}" class="sr-pg hide-mob" title="First">«</a>
                    <a href="{{ $returns->previousPageUrl() }}" class="sr-pg" title="Previous">‹</a>
                @endif

                @php
                    $cur   = $returns->currentPage();
                    $last  = $returns->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $returns->url(1) }}" class="sr-pg hide-mob">1</a>
                    @if($start > 2) <span class="sr-pg-dots hide-mob">…</span> @endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="sr-pg sr-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $returns->url($p) }}" class="sr-pg">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $last)
                    @if($end < $last - 1) <span class="sr-pg-dots hide-mob">…</span> @endif
                    <a href="{{ $returns->url($last) }}" class="sr-pg hide-mob">{{ $last }}</a>
                @endif

                @if($returns->hasMorePages())
                    <a href="{{ $returns->nextPageUrl() }}" class="sr-pg" title="Next">›</a>
                    <a href="{{ $returns->url($last) }}" class="sr-pg hide-mob" title="Last">»</a>
                @else
                    <span class="sr-pg sr-pg--dis">›</span>
                    <span class="sr-pg sr-pg--dis hide-mob">»</span>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ── Complete Modal ── --}}
<div class="sr-modal" id="completeModal">
    <div class="sr-modal-overlay" onclick="closeCompleteModal()"></div>
    <div class="sr-modal-box">
        <div class="sr-modal-head" style="background:#f0fdf4;">
            <div class="sr-modal-ico" style="background:#22c55e;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div class="sr-modal-title">Complete Return</div>
                <div class="sr-modal-sub">Generate credit notes and restock items</div>
            </div>
            <button class="sr-modal-close" onclick="closeCompleteModal()">×</button>
        </div>
        <div class="sr-modal-body">
            <p>Are you sure you want to complete this return transaction? Stock items will be updated and re-allocated instantly.</p>
        </div>
        <div class="sr-modal-foot">
            <button class="sr-btn-cancel" onclick="closeCompleteModal()">Cancel</button>
            <button class="sr-btn-confirm" id="confirmComplete" style="background:#22c55e;">Complete Return</button>
        </div>
    </div>
</div>

{{-- ── Delete Modal ── --}}
<div class="sr-modal" id="delModal">
    <div class="sr-modal-overlay" onclick="closeDelModal()"></div>
    <div class="sr-modal-box">
        <div class="sr-modal-head">
            <div class="sr-modal-ico">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </div>
            <div>
                <div class="sr-modal-title">Delete Return Record</div>
                <div class="sr-modal-sub">Draft configuration will be permanently wiped</div>
            </div>
            <button class="sr-modal-close" onclick="closeDelModal()">×</button>
        </div>
        <div class="sr-modal-body">
            <p>Are you sure you want to permanently delete this return invoice draft? This calculation process can't be undone.</p>
        </div>
        <div class="sr-modal-foot">
            <button class="sr-btn-cancel" onclick="closeDelModal()">Cancel</button>
            <button class="sr-btn-confirm" id="confirmDel">Delete Draft</button>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ─── Desktop Base CSS ────────────────────── */
:root {
    --c-brand:   #f97316; --c-brand-d: #ea6c10; --c-brand-l: #fff7ed;
    --c-text:    #111827; --c-text2:   #374151; --c-muted:   #6b7280;
    --c-border:  #e5e7eb; --c-bg:      #f9fafb; --c-white:   #ffffff;
    --c-shadow:  0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
    --c-shadow2: 0 4px 12px rgba(0,0,0,.08);
    --r: 7px; --r-sm: 5px;
}
.sr-wrap { font-family: 'Segoe UI', system-ui, sans-serif; font-size: 12.5px; color: var(--c-text); padding: 16px; max-width: 100%; }

/* Headers, Stats, Filters (Exact Match Design Configuration) */
.sr-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid var(--c-border); gap: 12px; flex-wrap: wrap; }
.sr-header-left { display: flex; align-items: center; gap: 11px; }
.sr-header-icon { width: 38px; height: 38px; background: var(--c-brand-l); border-radius: 9px; display: flex; align-items: center; justify-content: center; color: var(--c-brand); flex-shrink: 0; }
.sr-title { font-size: 17px; font-weight: 700; margin: 0 0 2px; letter-spacing: -.3px; }
.sr-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }
.sr-btn-create { display: inline-flex; align-items: center; gap: 6px; padding: 8px 15px; background: var(--c-brand); color: #fff; border: none; border-radius: var(--r-sm); font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; box-shadow: 0 2px 8px rgba(249,115,22,.3); }

.sr-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 14px; }
.sr-stat { display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: var(--c-white); border: 1px solid var(--c-border); border-radius: var(--r); box-shadow: var(--c-shadow); position: relative; overflow: hidden; }
.sr-stat::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: var(--r) var(--r) 0 0; }
.sr-stat--orange::before { background: #f97316; } .sr-stat--green::before { background: #22c55e; }
.sr-stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sr-stat--orange .sr-stat-icon { background: #fff7ed; color: #f97316; } .sr-stat--green .sr-stat-icon { background: #dcfce7; color: #16a34a; }
.sr-stat-label { font-size: 10.5px; color: var(--c-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 3px; }
.sr-stat-value { font-size: 17px; font-weight: 800; color: var(--c-text); margin-bottom: 2px; }
.sr-stat-hint  { font-size: 10px; color: var(--c-muted); }

.sr-filters { background: var(--c-white); border: 1px solid var(--c-border); border-radius: var(--r); padding: 11px 14px 13px; margin-bottom: 12px; box-shadow: var(--c-shadow); }
.sr-filters-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.sr-filters-title { font-size: 11px; font-weight: 700; color: var(--c-text2); display: flex; align-items: center; gap: 5px; text-transform: uppercase; }
.sr-clear-filters { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; color: var(--c-brand); text-decoration: none; font-weight: 500; }
.sr-filter-row { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; }
.sr-filter-group { display: flex; flex-direction: column; gap: 4px; min-width: 120px; }
.sr-filter-label { font-size: 9.5px; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.sr-input, .sr-select { height: 31px; padding: 0 9px; border: 1px solid var(--c-border); border-radius: var(--r-sm); font-size: 11.5px; color: var(--c-text); background: var(--c-bg); outline: none; width: 100%; }
.sr-input-icon-wrap { position: relative; width: 100%; }
.sr-input-icon { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); color: var(--c-muted); pointer-events: none; }
.sr-input-with-icon { padding-left: 28px; }
.sr-filter-btns { display: flex; gap: 6px; align-items: flex-end; }
.sr-btn-filter { display: inline-flex; align-items: center; gap: 5px; height: 31px; padding: 0 14px; background: var(--c-text); color: #fff; border: none; border-radius: var(--r-sm); font-size: 11.5px; font-weight: 600; cursor: pointer; }
.sr-btn-reset { display: inline-flex; align-items: center; height: 31px; padding: 0 12px; background: var(--c-bg); color: var(--c-muted); border: 1px solid var(--c-border); border-radius: var(--r-sm); font-size: 11.5px; text-decoration: none; font-weight: 500; }

/* Table Elements */
.sr-table-card { background: var(--c-white); border: 1px solid var(--c-border); border-radius: var(--r); box-shadow: var(--c-shadow); }
.sr-table-topbar { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-bottom: 1px solid var(--c-border); background: var(--c-bg); flex-wrap: wrap; gap: 6px; }
.sr-table-count { font-size: 11.5px; color: var(--c-muted); font-weight: 500; display: flex; align-items: center; gap: 6px; }
.sr-table-count strong { color: var(--c-text); font-weight: 700; }
.sr-filtered-pill { display: inline-flex; align-items: center; gap: 3px; padding: 2px 7px; background: #fff7ed; color: #92400e; border: 1px solid #fed7aa; border-radius: 10px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
.sr-page-info-top { font-size: 10.5px; color: var(--c-muted); }

.sr-table-wrap { overflow-x: auto; }
.sr-table { width: 100%; border-collapse: collapse; font-size: 12px; min-width: 1150px; }
.sr-table th { padding: 9px 11px; background: #f3f4f6; font-size: 9.5px; font-weight: 700; color: var(--c-muted); text-transform: uppercase; text-align: left; border-bottom: 1px solid var(--c-border); }
.sr-table td { padding: 9px 11px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; color: var(--c-text2); }
.sr-tr:hover td { background: #fafafa; }

.tc-no { width: 45px; text-align: center; } .tc-date { width: 88px; } .tc-return { width: 150px; } .tc-invoice { width: 140px; } .tc-party { width: 170px; } .tc-wh { width: 110px; } .tc-qty { width: 65px; } .tc-amount { width: 120px; } .tc-credit { width: 130px; } .tc-status { width: 95px; } .tc-act { width: 95px; }

.td-serial { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--c-bg); border-radius: 4px; font-size: 10px; color: var(--c-muted); font-weight: 600; }
.td-muted { color: var(--c-muted); font-size: 10.5px; }
.td-date-main{ font-size: 11.5px; color: var(--c-text2); font-weight: 500; }
.td-date-sub { font-size: 9.5px; color: var(--c-muted); }
.sr-return-chip { display: inline-block; padding: 3px 8px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 4px; font-size: 10.5px; font-weight: 600; color: #9a3412; font-family: monospace; }
.sr-invoice-link { color: var(--c-brand); text-decoration: none; font-weight: 500; font-size: 11.5px; } .sr-invoice-link:hover { text-decoration: underline; }
.td-party-name { font-weight: 500; color: var(--c-text); font-size: 12px; margin-bottom: 2px; }
.sr-ptype { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8.5px; font-weight: 700; text-transform: uppercase; }
.sr-ptype--customer { background: #d1fae5; color: #065f46; } .sr-ptype--dealer { background: #dbeafe; color: #1e40af; } .sr-ptype--distributor { background: #fef3c7; color: #92400e; }
.sr-wh-badge { display: inline-block; padding: 2px 7px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; font-size: 10px; font-weight: 600; color: #166534; }
.sr-qty-badge { display: inline-block; padding: 2px 8px; background: #f3f4f6; border-radius: 4px; font-size: 10.5px; font-weight: 600; color: var(--c-text2); }
.amount-display { display: flex; flex-direction: column; gap: 2px; font-size: 11px; line-height: 1.4; }
.amount-main { font-weight: 700; color: var(--c-text); font-size: 12.5px; }
.sr-credit-link { color: #059669; text-decoration: none; font-weight: 500; font-size: 11px; } .sr-credit-link:hover { text-decoration: underline; }

.sr-status { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 9.5px; font-weight: 700; text-transform: uppercase; width: fit-content; }
.sr-status--draft { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; } .sr-status--completed { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; } .sr-status--cancelled { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

.sr-act-grp { display: flex; gap: 4px; align-items: center; }
.sr-act { width: 28px; height: 28px; border: none; border-radius: var(--r-sm); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; }
.sr-act--view { background: #dbeafe; color: #2563eb; } .sr-act--edit { background: #fef3c7; color: #92400e; } .sr-act--complete { background: #dcfce7; color: #16a34a; } .sr-act--del { background: #fee2e2; color: #dc2626; }

/* Empty, Pagination & Modals */
.sr-empty-cell { padding: 52px 20px; text-align: center; } .sr-empty { display: inline-flex; flex-direction: column; align-items: center; gap: 8px; }
.sr-empty-icon { width: 56px; height: 56px; background: var(--c-bg); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #d1d5db; margin-bottom: 4px; }
.sr-empty-title { font-size: 13px; font-weight: 600; color: var(--c-text2); margin: 0; }
.sr-pagination { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-top: 1px solid var(--c-border); background: var(--c-bg); flex-wrap: wrap; gap: 8px; }
.sr-page-info { font-size: 10.5px; color: var(--c-muted); } .sr-pages { display: flex; gap: 3px; align-items: center; flex-wrap: wrap; }
.sr-pg { display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 28px; padding: 0 5px; border: 1px solid var(--c-border); border-radius: var(--r-sm); font-size: 11px; color: var(--c-text2); text-decoration: none; background: var(--c-white); font-weight: 500; }
.sr-pg:hover { background: #f3f4f6; } .sr-pg--active { background: var(--c-brand); color: #fff; font-weight: 700; } .sr-pg--dis { color: #d1d5db; cursor: default; }

.sr-modal { display: none; position: fixed; inset: 0; z-index: 1000; align-items: center; justify-content: center; }
.sr-modal-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.45); backdrop-filter: blur(2px); }
.sr-modal-box { position: relative; background: var(--c-white); border-radius: 10px; width: 380px; max-width: 92%; box-shadow: 0 20px 50px rgba(0,0,0,.15); }
.sr-modal-head { display: flex; align-items: center; gap: 11px; padding: 14px 16px; border-bottom: 1px solid var(--c-border); background: var(--c-bg); border-radius: 10px 10px 0 0; }
.sr-modal-ico { width: 34px; height: 34px; background: #ef4444; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; }
.sr-modal-title { font-size: 13.5px; font-weight: 700; margin-bottom: 1px; } .sr-modal-sub { font-size: 10.5px; color: var(--c-muted); }
.sr-modal-close { margin-left: auto; background: none; border: none; font-size: 20px; color: var(--c-muted); cursor: pointer; }
.sr-modal-body { padding: 16px; font-size: 12.5px; color: #4b5563; }
.sr-modal-foot { display: flex; justify-content: flex-end; gap: 8px; padding: 12px 16px; border-top: 1px solid var(--c-border); background: #fafafa; border-radius: 0 0 10px 10px; }
.sr-btn-cancel { padding: 7px 16px; border: 1px solid var(--c-border); border-radius: var(--r-sm); background: var(--c-bg); color: var(--c-text2); font-size: 12px; font-weight: 600; cursor: pointer; }
.sr-btn-confirm { padding: 7px 16px; border: none; border-radius: var(--r-sm); background: #ef4444; color: #fff; font-size: 12px; font-weight: 600; cursor: pointer; }


/* ─── DENSE, PREMIUM MOBILE APP UI (Max-width: 768px) ──────────────── */
@media (min-width: 769px) {
    .mob-icon { display: none !important; }
}

@media (max-width: 1024px) {
    .sr-stats { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .hide-mob { display: none !important; }
    
    /* Mobile Tiny Icons mapping configuration */
    .mob-icon { display: inline-block; vertical-align: middle; margin-right: 5px; width: 13px; height: 13px; color: var(--c-muted); }

    .sr-wrap { padding: 8px; background: #f3f4f6; min-height: 100vh;}
    .sr-header { background: #fff; padding: 12px; border-radius: 8px; margin-bottom: 12px; flex-direction: column; align-items: flex-start; }
    
    /* Horizontal scroll for statistics metrics panel */
    .sr-stats { display: flex; flex-wrap: nowrap; overflow-x: auto; scroll-snap-type: x mandatory; gap: 10px; margin-bottom: 12px; padding-bottom: 4px; -webkit-overflow-scrolling: touch; }
    .sr-stat { flex: 0 0 88%; scroll-snap-align: center; padding: 10px 14px; }
    .sr-stats::-webkit-scrollbar { display: none; }
    
    .sr-filters { padding: 12px; margin-bottom: 12px; }
    .sr-filter-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .full-width-mobile { grid-column: span 2; }
    .sr-filter-group { min-width: 100%; flex: 1 1 100%; }
    .sr-filter-btns { justify-content: space-between; margin-top: 4px; }

    /* Card Layout Injection Configuration */
    .sr-table-card { background: transparent; border: none; box-shadow: none; }
    .sr-table-topbar { background: transparent; padding: 0 4px 6px; border: none; }
    .sr-table { min-width: 100%; display: block; }
    .sr-table thead { display: none; }
    .sr-table tbody { display: block; width: 100%; }
    
    .sr-tr {
        display: flex; flex-wrap: wrap; align-content: flex-start; align-items: center;
        background: #fff !important; border-radius: 8px;
        padding: 12px 14px 10px; margin-bottom: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;
    }
    .sr-tr:hover td { background: transparent; }
    .sr-table td { border: none; padding: 0; display: flex; align-items: center; }

    /* Custom Order layout mapping structure */
    /* Row 1: Party (Left), Amount (Right) */
    .tc-party { width: calc(100% - 130px); order: 1; margin-bottom: 6px; }
    .td-party-name { font-size: 13.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: flex; align-items: center; }
    
    .tc-amount { width: 130px; order: 2; margin-bottom: 6px; justify-content: flex-end; }
    .amount-display { align-items: flex-end; gap: 1px;}
    .amount-main { font-size: 14px; }
    
    /* Row 2: Return Number (Left), Date (Right) */
    .tc-return { width: 55%; order: 3; margin-bottom: 10px; }
    .sr-return-chip { font-size: 10px; padding: 2px 6px; }
    
    .tc-date { width: 45%; order: 4; margin-bottom: 10px; justify-content: flex-end; }
    .td-date-main { display: flex; align-items: center; font-size: 11px; color: #4b5563; font-weight: 600;}
    
    /* Row 3: Badges Collection (Left-flowing inline rows) */
    .tc-invoice, .tc-wh, .tc-qty, .tc-credit, .tc-status {
        width: auto !important; order: 5; margin-right: 6px; margin-bottom: 4px;
    }
    
    /* Make inline table links, text chips and labels scale uniformly */
    .sr-invoice-link, .sr-credit-link { font-size: 9.5px; font-weight: 700; padding: 2px 5px; background: #f1f5f9; border-radius: 4px; border: 1px solid #e2e8f0; }
    .sr-wh-badge, .sr-qty-badge, .sr-status { font-size: 8.5px; padding: 2px 5px; border-radius: 4px; }

    /* Row 4: Border Split Actions row aligned strictly to right edge */
    .tc-act { width: 100%; order: 10; margin-left: auto; margin-top: 4px; border-top: 1px dashed #e5e7eb; padding-top: 10px; justify-content: flex-end; }
    .sr-act-grp { justify-content: flex-end; gap: 5px; }
    .sr-act { width: 26px; height: 26px; border-radius: 5px; }

    /* Empty parameters & pagination structural updates */
    .sr-table td.sr-empty-cell { width: 100%; display: block; text-align: center; }
    .sr-pagination { flex-direction: column; align-items: center; background: transparent; border: none; padding: 0; }
    .sr-pages { justify-content: center; width: 100%; margin-top: 10px; }
}
</style>
@endpush

@push('scripts')
<script>
let actionId = null;

// Complete Return Triggers
document.querySelectorAll('.complete-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        actionId = this.dataset.id;
        document.getElementById('completeModal').style.display = 'flex';
    });
});
function closeCompleteModal() {
    document.getElementById('completeModal').style.display = 'none';
    actionId = null;
}
document.getElementById('confirmComplete').addEventListener('click', function () {
    if (!actionId) return;
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Processing…';

    fetch('/admin/sales-returns/' + actionId + '/complete', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        closeCompleteModal();
        if (data.success) { showAlert(data.message || 'Completed successfully', 'success'); setTimeout(() => location.reload(), 900); } 
        else { showAlert(data.message || 'Failed to complete process', 'error'); btn.disabled = false; btn.textContent = 'Complete Return'; }
    }).catch(() => {
        closeCompleteModal(); showAlert('Something went wrong', 'error'); btn.disabled = false; btn.textContent = 'Complete Return';
    });
});

// Delete Return Triggers
document.querySelectorAll('.del-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        actionId = this.dataset.id;
        document.getElementById('delModal').style.display = 'flex';
    });
});
function closeDelModal() {
    document.getElementById('delModal').style.display = 'none';
    actionId = null;
}
document.getElementById('confirmDel').addEventListener('click', function () {
    if (!actionId) return;
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Deleting…';

    fetch('/admin/sales-returns/' + actionId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        closeDelModal();
        if (data.success) { showAlert(data.message || 'Deleted successfully', 'success'); setTimeout(() => location.reload(), 900); } 
        else { showAlert(data.message || 'Failed to delete draft', 'error'); btn.disabled = false; btn.textContent = 'Delete Draft'; }
    }).catch(() => {
        closeDelModal(); showAlert('Something went wrong', 'error'); btn.disabled = false; btn.textContent = 'Delete Draft';
    });
});

// Global Event Handling Helpers
document.addEventListener('keydown', e => { 
    if (e.key === 'Escape') { closeDelModal(); closeCompleteModal(); } 
});

function showAlert(msg, type = 'success') {
    const box = document.getElementById('alertBox');
    const el  = document.createElement('div');
    el.className   = 'sr-alert sr-alert-' + type;
    el.textContent = msg;
    box.appendChild(el);
    setTimeout(() => el.remove(), 4500);
}
</script>
@endpush
@endsection