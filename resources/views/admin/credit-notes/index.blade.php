@extends('layouts.admin')

@section('title', 'Credit Notes')
@section('header-title', 'Credit Notes')

@section('content')
<div class="cn-wrap">
    <div id="alertBox"></div>

    {{-- ── Header ── --}}
    <div class="cn-header">
        <div class="cn-header-left">
            <div class="cn-header-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div>
                <h1 class="cn-title">Credit Notes</h1>
                <p class="cn-sub">Manage customer credits and adjustments</p>
            </div>
        </div>
        <div class="cn-header-right">
            <a href="{{ route('admin.sales-returns.index') }}" class="cn-btn-secondary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Returns
            </a>
        </div>
    </div>

    {{-- ── Stats ── --}}
    <div class="cn-stats">
        <div class="cn-stat cn-stat--orange">
            <div class="cn-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/>
                    <line x1="12" y1="18" x2="12" y2="20"/>
                    <line x1="12" y1="2" x2="12" y2="4"/>
                </svg>
            </div>
            <div class="cn-stat-content">
                <div class="cn-stat-label">Total Credit</div>
                <div class="cn-stat-value">₹ {{ number_format($totalAmount, 2) }}</div>
                <div class="cn-stat-hint">All credit notes</div>
            </div>
        </div>
        <div class="cn-stat cn-stat--green">
            <div class="cn-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="cn-stat-content">
                <div class="cn-stat-label">Active Notes</div>
                <div class="cn-stat-value">{{ $activeCount }}</div>
                <div class="cn-stat-hint">Available for use</div>
            </div>
        </div>
        <div class="cn-stat cn-stat--blue">
            <div class="cn-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </div>
            <div class="cn-stat-content">
                <div class="cn-stat-label">Used Amount</div>
                <div class="cn-stat-value">₹ {{ number_format($totalUsed, 2) }}</div>
                <div class="cn-stat-hint">Already utilized</div>
            </div>
        </div>
        <div class="cn-stat cn-stat--purple">
            <div class="cn-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="6" x2="12" y2="18"/>
                    <line x1="6" y1="12" x2="18" y2="12"/>
                </svg>
            </div>
            <div class="cn-stat-content">
                <div class="cn-stat-label">Remaining</div>
                <div class="cn-stat-value">₹ {{ number_format($totalRemaining, 2) }}</div>
                <div class="cn-stat-hint">Still available</div>
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="cn-filters">
        <div class="cn-filters-header">
            <div class="cn-filters-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Filters
            </div>
            @if(request()->anyFilled(['date_from','date_to','credit_note_number','invoice_number','party_id','status']))
            <a href="{{ route('admin.credit-notes.index') }}" class="cn-clear-filters">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Clear all filters
            </a>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.credit-notes.index') }}" id="filterForm">
            <div class="cn-filter-row">

                {{-- Date From --}}
                <div class="cn-filter-group">
                    <label class="cn-filter-label">From Date</label>
                    <input type="date" name="date_from" class="cn-input" value="{{ request('date_from') }}">
                </div>

                {{-- Date To --}}
                <div class="cn-filter-group">
                    <label class="cn-filter-label">To Date</label>
                    <input type="date" name="date_to" class="cn-input" value="{{ request('date_to') }}">
                </div>

                {{-- Credit Note Number --}}
                <div class="cn-filter-group">
                    <label class="cn-filter-label">Credit Note No.</label>
                    <div class="cn-input-icon-wrap">
                        <svg class="cn-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" name="credit_note_number" class="cn-input cn-input-with-icon"
                               placeholder="Search note..." value="{{ request('credit_note_number') }}">
                    </div>
                </div>

                {{-- Invoice Number --}}
                <div class="cn-filter-group">
                    <label class="cn-filter-label">Invoice Number</label>
                    <div class="cn-input-icon-wrap">
                        <svg class="cn-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <input type="text" name="invoice_number" class="cn-input cn-input-with-icon"
                               placeholder="Search invoice..." value="{{ request('invoice_number') }}">
                    </div>
                </div>

                {{-- Party --}}
                <div class="cn-filter-group">
                    <label class="cn-filter-label">Party</label>
                    <select name="party_id" class="cn-select">
                        <option value="">All Parties</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->_id }}" {{ request('party_id') == $party->_id ? 'selected' : '' }}>
                                {{ $party->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="cn-filter-group">
                    <label class="cn-filter-label">Status</label>
                    <select name="status" class="cn-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Used</option>
                        <option value="advance_transferred" {{ request('status') == 'advance_transferred' ? 'selected' : '' }}>Advance Transferred</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="cn-filter-btns">
                    <button type="submit" class="cn-btn-filter">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Apply
                    </button>
                    <a href="{{ route('admin.credit-notes.index') }}" class="cn-btn-reset">Reset</a>
                </div>

            </div>
        </form>
    </div>

    {{-- ── Table ── --}}
    <div class="cn-table-card">

        <div class="cn-table-topbar">
            <div class="cn-table-count">
                <strong>{{ $creditNotes->total() }}</strong> credit note{{ $creditNotes->total() != 1 ? 's' : '' }}
                @if(request()->anyFilled(['date_from','date_to','credit_note_number','invoice_number','party_id','status']))
                    <span class="cn-filtered-pill">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                        </svg>
                        Filtered
                    </span>
                @endif
            </div>
            <div class="cn-table-topbar-right">
                <div class="cn-page-info-top">
                    Showing {{ $creditNotes->firstItem() ?? 0 }}–{{ $creditNotes->lastItem() ?? 0 }} of {{ $creditNotes->total() }}
                </div>
            </div>
        </div>

        <div class="cn-table-wrap">
            <table class="cn-table">
                <thead>
                    <tr>
                        <th class="tc-no">S. No.</th>
                        <th class="tc-date">Date</th>
                        <th class="tc-note">Credit Note No.</th>
                        <th class="tc-party">Party</th>
                        <th class="tc-invoice">Invoice No.</th>
                        <th class="tc-subtotal">Subtotal (₹)</th>
                        <th class="tc-tax">Tax (₹)</th>
                        <th class="tc-discount">Discount (₹)</th>
                        <th class="tc-amount">Total (₹)</th>
                        <th class="tc-used">Used (₹)</th>
                        <th class="tc-remaining">Remaining (₹)</th>
                        <th class="tc-status">Status</th>
                        <th class="tc-act">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($creditNotes as $i => $note)
                    <tr class="cn-tr">
                        <td class="tc-no">
                            <span class="td-serial">{{ ($creditNotes->currentPage() - 1) * $creditNotes->perPage() + $i + 1 }}</span>
                        </td>
                        <td class="tc-date">
                            <div class="td-date-main">{{ \Carbon\Carbon::parse($note->credit_date)->format('d M Y') }}</div>
                            <div class="td-date-sub">{{ \Carbon\Carbon::parse($note->credit_date)->format('D') }}</div>
                        </td>
                        <td class="tc-note">
                            <span class="cn-note-chip">{{ $note->credit_note_number }}</span>
                        </td>
                        <td class="tc-party">
                            @php
                                $partyName = optional($note->party)->name ?? 'N/A';
                                $partyType = optional($note->party)->party_type ?? '';
                            @endphp
                            <div class="td-party-name" title="{{ $partyName }}">{{ $partyName }}</div>
                            @if($partyType)
                                <span class="cn-ptype cn-ptype--{{ $partyType }}">{{ ucfirst($partyType) }}</span>
                            @endif
                        </td>
                        <td class="tc-invoice">
                            @if($note->invoice)
                                <a href="{{ route('admin.sales.show', $note->sales_invoice_id) }}" class="cn-invoice-link">
                                    {{ $note->invoice->invoice_number }}
                                </a>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td class="tc-subtotal">
                            <span class="amount-subtotal">₹ {{ number_format($note->subtotal_float, 2) }}</span>
                        </td>
                        <td class="tc-tax">
                            <span class="amount-tax">₹ {{ number_format($note->tax_amount_float, 2) }}</span>
                        </td>
                        <td class="tc-discount">
                            <span class="amount-discount">₹ {{ number_format($note->discount_amount_float, 2) }}</span>
                        </td>
                        <td class="tc-amount">
                            <span class="amount-main">₹ {{ number_format($note->amount_float, 2) }}</span>
                        </td>
                        <td class="tc-used">
                            @php
                                $totalAmount   = (float) $note->amount_float;
                                $usedAmount    = (float) $note->used_amount_float;
                                $invoiceUsed   = $totalAmount - $usedAmount; // jo invoice balance clear karne mein gaya
                                $advanceUsed   = $usedAmount;                // jo advance mein gaya
                                $isAdvance     = $note->status === 'advance_transferred';
                            @endphp

                            @if($isAdvance && $invoiceUsed > 0.001 && $advanceUsed > 0.001)
                                {{-- Case 3: Kuch invoice mein, kuch advance mein --}}
                                <div style="display:flex;flex-direction:column;gap:2px;">
                                    <span style="font-size:10.5px;font-weight:600;color:#3b82f6;">
                                        ₹ {{ number_format($invoiceUsed, 2) }}
                                        <span style="font-size:9px;color:#6b7280;font-weight:400;">invoice</span>
                                    </span>
                                    <span style="font-size:10.5px;font-weight:600;color:#f97316;">
                                        ₹ {{ number_format($advanceUsed, 2) }}
                                        <span style="font-size:9px;color:#6b7280;font-weight:400;">advance</span>
                                    </span>
                                </div>
                            @elseif($isAdvance && $advanceUsed > 0.001)
                                {{-- Poora advance mein gaya --}}
                                <div style="display:flex;flex-direction:column;gap:2px;">
                                    <span style="font-size:10.5px;font-weight:600;color:#f97316;">
                                        ₹ {{ number_format($advanceUsed, 2) }}
                                        <span style="font-size:9px;color:#6b7280;font-weight:400;">advance</span>
                                    </span>
                                </div>
                            @else
                                <span class="amount-used">₹ {{ number_format($usedAmount, 2) }}</span>
                            @endif
                        </td>
                        <td class="tc-remaining">
                            <span class="amount-remaining">₹ {{ number_format($note->remaining_amount_float, 2) }}</span>
                        </td>
                        <td class="tc-status">
                            @php
                                $statusMap = [
                                    'active'               => ['cls' => 'cn-status--active',    'label' => 'Active'],
                                    'used'                 => ['cls' => 'cn-status--used',      'label' => 'Used'],
                                    'cancelled'            => ['cls' => 'cn-status--cancelled', 'label' => 'Cancelled'],
                                    'advance_transferred'  => ['cls' => 'cn-status--advance',   'label' => 'Advance'],
                                ];
                                $sm = $statusMap[$note->status] ?? $statusMap['active'];
                            @endphp
                            <span class="cn-status {{ $sm['cls'] }}">{{ $sm['label'] }}</span>
                        </td>
                        <td class="tc-act">
                            <div class="cn-act-grp">
                                <a href="{{ route('admin.credit-notes.show', $note->_id) }}"
                                   class="cn-act cn-act--view" title="View Credit Note">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>

                                @if($note->status === 'active')
                                    <button type="button" onclick="cancelNote('{{ $note->_id }}')"
                                            class="cn-act cn-act--cancel" title="Cancel Credit Note">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="15" y1="9" x2="9" y2="15"/>
                                            <line x1="9" y1="9" x2="15" y2="15"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="cn-empty-cell">
                            <div class="cn-empty">
                                <div class="cn-empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                </div>
                                <p class="cn-empty-title">No credit notes found</p>
                                <p class="cn-empty-sub">
                                    @if(request()->anyFilled(['date_from','date_to','credit_note_number','invoice_number','party_id','status']))
                                        Try adjusting your filters or <a href="{{ route('admin.credit-notes.index') }}">clear all</a>
                                    @else
                                        Credit notes are generated automatically from sales returns
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
        @if($creditNotes->hasPages())
        <div class="cn-pagination">
            <div class="cn-page-info">
                Page <strong>{{ $creditNotes->currentPage() }}</strong> of <strong>{{ $creditNotes->lastPage() }}</strong>
                &nbsp;·&nbsp; {{ $creditNotes->total() }} total records
            </div>
            <div class="cn-pages">
                @if($creditNotes->onFirstPage())
                    <span class="cn-pg cn-pg--dis">«</span>
                    <span class="cn-pg cn-pg--dis">‹</span>
                @else
                    <a href="{{ $creditNotes->url(1) }}" class="cn-pg" title="First">«</a>
                    <a href="{{ $creditNotes->previousPageUrl() }}" class="cn-pg" title="Previous">‹</a>
                @endif

                @php
                    $cur   = $creditNotes->currentPage();
                    $last  = $creditNotes->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $creditNotes->url(1) }}" class="cn-pg">1</a>
                    @if($start > 2) <span class="cn-pg-dots">…</span> @endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="cn-pg cn-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $creditNotes->url($p) }}" class="cn-pg">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $last)
                    @if($end < $last - 1) <span class="cn-pg-dots">…</span> @endif
                    <a href="{{ $creditNotes->url($last) }}" class="cn-pg">{{ $last }}</a>
                @endif

                @if($creditNotes->hasMorePages())
                    <a href="{{ $creditNotes->nextPageUrl() }}" class="cn-pg" title="Next">›</a>
                    <a href="{{ $creditNotes->url($last) }}" class="cn-pg" title="Last">»</a>
                @else
                    <span class="cn-pg cn-pg--dis">›</span>
                    <span class="cn-pg cn-pg--dis">»</span>
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
.cn-wrap {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 12.5px;
    color: var(--c-text);
    padding: 16px;
    max-width: 100%;
}

/* ─── Header ──────────────────────────────────────────────── */
.cn-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--c-border);
    gap: 12px;
    flex-wrap: wrap;
}
.cn-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
}
.cn-header-icon {
    width: 38px; height: 38px;
    background: var(--c-brand-l);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    color: var(--c-brand);
    flex-shrink: 0;
}
.cn-title { font-size: 17px; font-weight: 700; margin: 0 0 2px; letter-spacing: -.3px; }
.cn-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }

.cn-header-right { display: flex; gap: 8px; }

.cn-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 15px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: var(--r-sm);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s;
    white-space: nowrap;
}
.cn-btn-secondary:hover {
    background: #5a6268;
    color: white;
}

/* ─── Stats ───────────────────────────────────────────────── */
.cn-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 14px;
}
.cn-stat {
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
.cn-stat:hover {
    box-shadow: var(--c-shadow2);
    transform: translateY(-1px);
}
.cn-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: var(--r) var(--r) 0 0;
}
.cn-stat--orange::before { background: #f97316; }
.cn-stat--green::before  { background: #22c55e; }
.cn-stat--blue::before   { background: #3b82f6; }
.cn-stat--purple::before { background: #8b5cf6; }

.cn-stat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.cn-stat--orange .cn-stat-icon { background: #fff7ed; color: #f97316; }
.cn-stat--green .cn-stat-icon  { background: #dcfce7; color: #16a34a; }
.cn-stat--blue .cn-stat-icon   { background: #dbeafe; color: #2563eb; }
.cn-stat--purple .cn-stat-icon { background: #f3e8ff; color: #7c3aed; }

.cn-stat-label { font-size: 10.5px; color: var(--c-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
.cn-stat-value { font-size: 17px; font-weight: 800; color: var(--c-text); letter-spacing: -.4px; margin-bottom: 2px; }
.cn-stat-hint  { font-size: 10px; color: var(--c-muted); }

/* ─── Filters ─────────────────────────────────────────────── */
.cn-filters {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    padding: 11px 14px 13px;
    margin-bottom: 12px;
    box-shadow: var(--c-shadow);
}
.cn-filters-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.cn-filters-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--c-text2);
    display: flex;
    align-items: center;
    gap: 5px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.cn-clear-filters {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    color: var(--c-brand);
    text-decoration: none;
    font-weight: 500;
    transition: color .15s;
}
.cn-clear-filters:hover { color: var(--c-brand-d); text-decoration: underline; }

.cn-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-end;
}
.cn-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 110px;
}
.cn-filter-label {
    font-size: 9.5px;
    font-weight: 700;
    color: var(--c-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.cn-input, .cn-select {
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
.cn-input:focus, .cn-select:focus {
    border-color: var(--c-brand);
    background: var(--c-white);
    box-shadow: 0 0 0 3px rgba(249,115,22,.1);
}
.cn-input-icon-wrap { position: relative; }
.cn-input-icon {
    position: absolute;
    left: 9px; top: 50%;
    transform: translateY(-50%);
    color: var(--c-muted);
    pointer-events: none;
}
.cn-input-with-icon { padding-left: 28px; }

.cn-filter-btns {
    display: flex;
    gap: 6px;
    align-items: flex-end;
}
.cn-btn-filter {
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
.cn-btn-filter:hover { background: #1f2937; }
.cn-btn-reset {
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
.cn-btn-reset:hover { background: #f3f4f6; color: var(--c-text); }

/* ─── Table Card ──────────────────────────────────────────── */
.cn-table-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    box-shadow: var(--c-shadow);
    overflow: hidden;
}
.cn-table-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-bottom: 1px solid var(--c-border);
    background: var(--c-bg);
    flex-wrap: wrap;
    gap: 6px;
}
.cn-table-count {
    font-size: 11.5px;
    color: var(--c-muted);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}
.cn-table-count strong { color: var(--c-text); font-weight: 700; }
.cn-filtered-pill {
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
.cn-page-info-top { font-size: 10.5px; color: var(--c-muted); }
.cn-table-topbar-right { display: flex; align-items: center; gap: 8px; }

/* ─── Table ───────────────────────────────────────────────── */
.cn-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.cn-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 1400px; /* Increased for new columns */
}
.cn-table th {
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
.cn-table td {
    padding: 9px 11px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    color: var(--c-text2);
}
.cn-table tr:last-child td { border-bottom: none; }
.cn-tr { transition: background .12s; }
.cn-tr:hover td { background: #fafafa; }

/* Column widths - Updated */
.tc-no        { width: 45px;  text-align: center; }
.tc-date      { width: 88px;  }
.tc-note      { width: 160px; }
.tc-party     { width: 170px; }
.tc-invoice   { width: 140px; }
.tc-subtotal  { width: 90px;  }
.tc-tax       { width: 80px;  }
.tc-discount  { width: 90px;  }
.tc-amount    { width: 100px; }
.tc-used      { width: 100px; }
.tc-remaining { width: 100px; }
.tc-status    { width: 80px;  }
.tc-act       { width: 70px;  }

/* Table cell helpers */
.td-serial   { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--c-bg); border-radius: 4px; font-size: 10px; color: var(--c-muted); font-weight: 600; }
.td-muted    { color: var(--c-muted); font-size: 10.5px; }
.td-date-main{ font-size: 11.5px; color: var(--c-text2); white-space: nowrap; font-weight: 500; }
.td-date-sub { font-size: 9.5px; color: var(--c-muted); }

/* Note chip */
.cn-note-chip {
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
.cn-ptype {
    display: inline-block;
    padding: 1px 5px;
    border-radius: 3px;
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.cn-ptype--customer    { background: #d1fae5; color: #065f46; }
.cn-ptype--dealer      { background: #dbeafe; color: #1e40af; }
.cn-ptype--distributor { background: #fef3c7; color: #92400e; }

/* Invoice link */
.cn-invoice-link {
    color: var(--c-brand);
    text-decoration: none;
    font-weight: 500;
    font-size: 11.5px;
}
.cn-invoice-link:hover { text-decoration: underline; }

/* Amount styles - New */
.amount-subtotal {
    font-weight: 600;
    color: var(--c-text);
    font-size: 11.5px;
}
.amount-tax {
    font-weight: 600;
    color: #3b82f6;
    font-size: 11.5px;
}
.amount-discount {
    font-weight: 600;
    color: #f59e0b;
    font-size: 11.5px;
}
.amount-main {
    font-weight: 700;
    color: var(--c-text);
    font-size: 12px;
}
.amount-used {
    font-weight: 600;
    color: #3b82f6;
    font-size: 11.5px;
}
.amount-remaining {
    font-weight: 600;
    color: #10b981;
    font-size: 11.5px;
}

/* Status badges */
.cn-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    width: fit-content;
}
.cn-status--active    { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.cn-status--used      { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
.cn-status--cancelled { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.cn-status--advance   { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }

/* Actions */
.cn-act-grp { display: flex; gap: 4px; align-items: center; }
.cn-act {
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
.cn-act--view    { background: #dbeafe; color: #2563eb; }
.cn-act--view:hover  { background: #bfdbfe; transform: scale(1.05); }
.cn-act--cancel  { background: #fee2e2; color: #dc2626; }
.cn-act--cancel:hover   { background: #fecaca; transform: scale(1.05); }

/* Empty state */
.cn-empty-cell { padding: 52px 20px; text-align: center; }
.cn-empty { display: inline-flex; flex-direction: column; align-items: center; gap: 8px; }
.cn-empty-icon {
    width: 56px; height: 56px;
    background: var(--c-bg);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: #d1d5db;
    margin-bottom: 4px;
}
.cn-empty-title { font-size: 13px; font-weight: 600; color: var(--c-text2); margin: 0; }
.cn-empty-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }
.cn-empty-sub a { color: var(--c-brand); }

/* ─── Pagination ──────────────────────────────────────────── */
.cn-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-top: 1px solid var(--c-border);
    background: var(--c-bg);
    flex-wrap: wrap;
    gap: 8px;
}
.cn-page-info { font-size: 10.5px; color: var(--c-muted); }
.cn-page-info strong { color: var(--c-text2); }
.cn-pages { display: flex; gap: 3px; align-items: center; flex-wrap: wrap; }
.cn-pg {
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
.cn-pg:hover         { background: #f3f4f6; border-color: #d1d5db; }
.cn-pg--active       { background: var(--c-brand); color: #fff; border-color: var(--c-brand); font-weight: 700; }
.cn-pg--active:hover { background: var(--c-brand); }
.cn-pg--dis          { color: #d1d5db; background: var(--c-bg); cursor: default; pointer-events: none; }
.cn-pg-dots          { font-size: 11px; color: var(--c-muted); padding: 0 2px; }

/* ─── Alerts ──────────────────────────────────────────────── */
#alertBox { position: fixed; top: 16px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 7px; }
.cn-alert {
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
    animation: cnAIn .25s ease;
    min-width: 220px;
    max-width: 320px;
}
@keyframes cnAIn {
    from { transform: translateX(110%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.cn-alert-success { background: #f0fdf4; color: #166534; border-left: 3px solid #22c55e; }
.cn-alert-error   { background: #fef2f2; color: #991b1b; border-left: 3px solid #ef4444; }

/* ─── Responsive ──────────────────────────────────────────── */
@media (max-width: 1024px) {
    .cn-stats { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .cn-stats { grid-template-columns: 1fr; }
    .cn-filter-group { min-width: calc(50% - 4px); flex: 1 1 calc(50% - 4px); }
    .cn-filter-btns { width: 100%; }
}
@media (max-width: 480px) {
    .cn-wrap { padding: 10px; }
    .cn-header { flex-direction: column; align-items: flex-start; }
    .cn-filter-group { min-width: 100%; flex: 1 1 100%; }
    .cn-pagination { flex-direction: column; align-items: flex-start; }
    .cn-pages { justify-content: center; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
function showAlert(message, type = 'success') {
    const box = document.getElementById('alertBox');
    const el = document.createElement('div');
    el.className = 'cn-alert cn-alert-' + type;
    el.textContent = message;
    box.appendChild(el);
    setTimeout(() => el.remove(), 4500);
}

function cancelNote(id) {
    if (!confirm('Are you sure you want to cancel this credit note?')) {
        return;
    }

    fetch('/admin/credit-notes/' + id + '/cancel', {
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
            setTimeout(() => window.location.reload(), 900);
        } else {
            showAlert(data.message || 'Failed to cancel credit note', 'error');
        }
    })
    .catch(() => {
        showAlert('Something went wrong', 'error');
    });
}
</script>
@endpush
@endsection
