{{-- resources/views/admin/debit-notes/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Debit Notes')
@section('header-title', 'Debit Notes')

@section('content')
<div class="dn-wrap">
    <div id="alertBox"></div>

    {{-- ── Header ── --}}
    <div class="dn-header">
        <div class="dn-header-left">
            <div class="dn-header-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div>
                <h1 class="dn-title">Debit Notes</h1>
                <p class="dn-sub">Manage vendor debits and adjustments</p>
            </div>
        </div>
        <div class="dn-header-right">
            <a href="{{ route('admin.purchase-returns.index') }}" class="dn-btn-secondary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Returns
            </a>
        </div>
    </div>

    {{-- Stats Section - Updated to match credit note --}}
    <div class="dn-stats" style="grid-template-columns: repeat(3, 1fr);">
        <div class="dn-stat dn-stat--green">
            <div class="dn-stat-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="dn-stat-content">
                <div class="dn-stat-label">Active</div>
                <div class="dn-stat-value">{{ $activeCount }}</div>
                <div class="dn-stat-hint">Available to use</div>
            </div>
        </div>
        <div class="dn-stat dn-stat--purple">
            <div class="dn-stat-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="dn-stat-content">
                <div class="dn-stat-label">Settled</div>
                <div class="dn-stat-value">{{ $settledCount }}</div>
                <div class="dn-stat-hint">Fully invoice set.</div>
            </div>
        </div>
        <div class="dn-stat dn-stat--orange">
            <div class="dn-stat-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="1 4 1 10 7 10"/>
                    <path d="M3.51 15a9 9 0 1 0 .49-4.5"/>
                </svg>
            </div>
            <div class="dn-stat-content">
                <div class="dn-stat-label">Partial</div>
                <div class="dn-stat-value">{{ $partialCount }}</div>
                <div class="dn-stat-hint">Partial cash</div>
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="dn-filters">
        <div class="dn-filters-header">
            <div class="dn-filters-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Filters
            </div>
            @if(request()->anyFilled(['date_from','date_to','debit_note_number','invoice_number','party_id','status']))
            <a href="{{ route('admin.debit-notes.index') }}" class="dn-clear-filters">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Clear all filters
            </a>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.debit-notes.index') }}" id="filterForm">
            <div class="dn-filter-row">

                {{-- Date From --}}
                <div class="dn-filter-group">
                    <label class="dn-filter-label">From Date</label>
                    <input type="date" name="date_from" class="dn-input" value="{{ request('date_from') }}">
                </div>

                {{-- Date To --}}
                <div class="dn-filter-group">
                    <label class="dn-filter-label">To Date</label>
                    <input type="date" name="date_to" class="dn-input" value="{{ request('date_to') }}">
                </div>

                {{-- Debit Note Number --}}
                <div class="dn-filter-group">
                    <label class="dn-filter-label">Debit Note No.</label>
                    <div class="dn-input-icon-wrap">
                        <svg class="dn-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" name="debit_note_number" class="dn-input dn-input-with-icon"
                               placeholder="Search note..." value="{{ request('debit_note_number') }}">
                    </div>
                </div>

                {{-- Invoice Number --}}
                <div class="dn-filter-group">
                    <label class="dn-filter-label">Invoice Number</label>
                    <div class="dn-input-icon-wrap">
                        <svg class="dn-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <input type="text" name="invoice_number" class="dn-input dn-input-with-icon"
                               placeholder="Search invoice..." value="{{ request('invoice_number') }}">
                    </div>
                </div>

                {{-- Vendor --}}
                {{-- Party Filter --}}
                <div class="dn-filter-group">
                    <label class="dn-filter-label">Party</label>
                    <select name="party_id" class="dn-select">
                        <option value="">All Parties</option>
                        @foreach($allParties as $party)
                            <option value="{{ $party->_id }}" {{ request('party_id') == $party->_id ? 'selected' : '' }}>
                                {{ $party->name }}
                                <span style="font-size: 9px; color: #6b7280;">({{ ucfirst($party->type) }})</span>
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                {{-- Status Filter --}}
                <div class="dn-filter-group">
                    <label class="dn-filter-label">Status</label>
                    <select name="status" class="dn-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="settled" {{ request('status') == 'settled' ? 'selected' : '' }}>Settled</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="dn-filter-btns">
                    <button type="submit" class="dn-btn-filter">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Apply
                    </button>
                    <a href="{{ route('admin.debit-notes.index') }}" class="dn-btn-reset">Reset</a>
                </div>

            </div>
        </form>
    </div>

    {{-- ── Table ── --}}
    <div class="dn-table-card">

        <div class="dn-table-topbar">
            <div class="dn-table-count">
                <strong>{{ $debitNotes->total() }}</strong> debit note{{ $debitNotes->total() != 1 ? 's' : '' }}
                @if(request()->anyFilled(['date_from','date_to','debit_note_number','invoice_number','party_id','status']))
                    <span class="dn-filtered-pill">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                        </svg>
                        Filtered
                    </span>
                @endif
            </div>
            <div class="dn-table-topbar-right">
                <div class="dn-page-info-top">
                    Showing {{ $debitNotes->firstItem() ?? 0 }}–{{ $debitNotes->lastItem() ?? 0 }} of {{ $debitNotes->total() }}
                </div>
            </div>
        </div>

        <div class="dn-table-wrap">
            <table class="dn-table">
                <thead>
                    <tr>
                        <th class="tc-no">S. No.</th>
                        <th class="tc-date">Date</th>
                        <th class="tc-note">Debit Note No.</th>
                        <th class="tc-vendor">Party</th>
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
                    @forelse($debitNotes as $i => $note)
                    <tr class="dn-tr">
                        <td class="tc-no">
                            <span class="td-serial">{{ ($debitNotes->currentPage() - 1) * $debitNotes->perPage() + $i + 1 }}</span>
                        </td>
                        <td class="tc-date">
                            <div class="td-date-main">{{ \Carbon\Carbon::parse($note->debit_date)->format('d M Y') }}</div>
                            <div class="td-date-sub">{{ \Carbon\Carbon::parse($note->debit_date)->format('D') }}</div>
                        </td>
                        <td class="tc-note">
                            <span class="dn-note-chip">{{ $note->debit_note_number }}</span>
                        </td>
                        <td class="tc-vendor">
                            @php
                                // ✅ Get party name directly from accessor
                                $partyName = $note->party_name;

                                $badge = match($note->party_type) {
                                    'vendor'      => ['Vendor',      'pt-vendor'],
                                    'dealer'      => ['Dealer',      'pt-dealer'],
                                    'distributor' => ['Distributor', 'pt-dist'],
                                    default       => [ucfirst($note->party_type ?? '-'), 'pt-vendor'],
                                };
                            @endphp
                            <span class="dn-party-badge {{ $badge[1] }}">{{ $badge[0] }}</span>
                            <div class="td-vendor-name" title="{{ $partyName }}">
                                {{ $partyName }}
                            </div>
                        </td>
                        <td class="tc-invoice">
                            @if($note->purchaseInvoice)
                                <a href="{{ route('admin.purchases.show', $note->purchase_invoice_id) }}" class="dn-invoice-link">
                                    {{ $note->purchaseInvoice->invoice_number }}
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
                                $invoiceUsed   = $totalAmount - $usedAmount;
                                $advanceUsed   = $usedAmount;
                                $isAdvance     = $note->status === 'advance_transferred';
                            @endphp

                            @if($isAdvance && $invoiceUsed > 0.001 && $advanceUsed > 0.001)
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
                        {{-- Status Badge in Table --}}
                        <td class="tc-status">
                            @php
                                $statusMap = [
                                    'active'  => ['cls' => 'dn-status--active', 'label' => 'Active'],
                                    'settled' => ['cls' => 'dn-status--settled', 'label' => 'Settled'],
                                    'partial' => ['cls' => 'dn-status--partial', 'label' => 'Partial'],
                                ];
                                $sm = $statusMap[$note->status] ?? $statusMap['active'];
                            @endphp
                            <span class="dn-status {{ $sm['cls'] }}">{{ $sm['label'] }}</span>
                        </td>
                        <td class="tc-act">
                            <div class="dn-act-grp">
                                <a href="{{ route('admin.debit-notes.show', $note->_id) }}"
                                   class="dn-act dn-act--view" title="View Debit Note">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="dn-empty-cell">
                            <div class="dn-empty">
                                <div class="dn-empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                </div>
                                <p class="dn-empty-title">No debit notes found</p>
                                <p class="dn-empty-sub">
                                    @if(request()->anyFilled(['date_from','date_to','debit_note_number','invoice_number','party_id','status']))
                                        Try adjusting your filters or <a href="{{ route('admin.debit-notes.index') }}">clear all</a>
                                    @else
                                        Debit notes are generated automatically from purchase returns
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
        @if($debitNotes->hasPages())
        <div class="dn-pagination">
            <div class="dn-page-info">
                Page <strong>{{ $debitNotes->currentPage() }}</strong> of <strong>{{ $debitNotes->lastPage() }}</strong>
                &nbsp;·&nbsp; {{ $debitNotes->total() }} total records
            </div>
            <div class="dn-pages">
                @if($debitNotes->onFirstPage())
                    <span class="dn-pg dn-pg--dis">«</span>
                    <span class="dn-pg dn-pg--dis">‹</span>
                @else
                    <a href="{{ $debitNotes->url(1) }}" class="dn-pg" title="First">«</a>
                    <a href="{{ $debitNotes->previousPageUrl() }}" class="dn-pg" title="Previous">‹</a>
                @endif

                @php
                    $cur   = $debitNotes->currentPage();
                    $last  = $debitNotes->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $debitNotes->url(1) }}" class="dn-pg">1</a>
                    @if($start > 2) <span class="dn-pg-dots">…</span> @endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="dn-pg dn-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $debitNotes->url($p) }}" class="dn-pg">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $last)
                    @if($end < $last - 1) <span class="dn-pg-dots">…</span> @endif
                    <a href="{{ $debitNotes->url($last) }}" class="dn-pg">{{ $last }}</a>
                @endif

                @if($debitNotes->hasMorePages())
                    <a href="{{ $debitNotes->nextPageUrl() }}" class="dn-pg" title="Next">›</a>
                    <a href="{{ $debitNotes->url($last) }}" class="dn-pg" title="Last">»</a>
                @else
                    <span class="dn-pg dn-pg--dis">›</span>
                    <span class="dn-pg dn-pg--dis">»</span>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

@push('styles')
<style>
/* Variables */
:root {
    --c-brand:   #ef4444;
    --c-brand-d: #dc2626;
    --c-brand-l: #fef2f2;
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

.dn-wrap {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 12.5px;
    color: var(--c-text);
    padding: 16px;
    max-width: 100%;
}

.dn-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--c-border);
    gap: 12px;
    flex-wrap: wrap;
}
.dn-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
}
.dn-header-icon {
    width: 38px; height: 38px;
    background: var(--c-brand-l);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    color: var(--c-brand);
    flex-shrink: 0;
}
.dn-title { font-size: 17px; font-weight: 700; margin: 0 0 2px; letter-spacing: -.3px; }
.dn-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }

.dn-header-right { display: flex; gap: 8px; }

.dn-btn-secondary {
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
.dn-btn-secondary:hover {
    background: #5a6268;
    color: white;
}

.dn-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 14px;
}
.dn-stat {
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
.dn-stat:hover {
    box-shadow: var(--c-shadow2);
    transform: translateY(-1px);
}
.dn-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: var(--r) var(--r) 0 0;
}
.dn-stat--red::before    { background: #ef4444; }
.dn-stat--green::before  { background: #22c55e; }
.dn-stat--blue::before   { background: #3b82f6; }
.dn-stat--orange::before { background: #f97316; }
.dn-status--partial {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}
.dn-status--closed {
    background: #e0e7ff;
    color: #3730a3;
    border: 1px solid #c7d2fe;
}
.dn-stat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.dn-stat--red .dn-stat-icon    { background: #fef2f2; color: #ef4444; }
.dn-stat--green .dn-stat-icon  { background: #dcfce7; color: #16a34a; }
.dn-stat--blue .dn-stat-icon   { background: #dbeafe; color: #2563eb; }
.dn-stat--orange .dn-stat-icon { background: #fff7ed; color: #f97316; }

.dn-stat-label { font-size: 10.5px; color: var(--c-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
.dn-stat-value { font-size: 17px; font-weight: 800; color: var(--c-text); letter-spacing: -.4px; margin-bottom: 2px; }
.dn-stat-hint  { font-size: 10px; color: var(--c-muted); }

.dn-filters {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    padding: 11px 14px 13px;
    margin-bottom: 12px;
    box-shadow: var(--c-shadow);
}
.dn-filters-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.dn-filters-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--c-text2);
    display: flex;
    align-items: center;
    gap: 5px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.dn-clear-filters {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    color: var(--c-brand);
    text-decoration: none;
    font-weight: 500;
    transition: color .15s;
}
.dn-clear-filters:hover { color: var(--c-brand-d); text-decoration: underline; }

.dn-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-end;
}
.dn-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 110px;
}
.dn-filter-label {
    font-size: 9.5px;
    font-weight: 700;
    color: var(--c-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.dn-input, .dn-select {
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
.dn-party-badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 3px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    margin-bottom: 3px;
}
.pt-vendor { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.pt-dealer { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
.pt-dist   { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
.dn-input:focus, .dn-select:focus {
    border-color: var(--c-brand);
    background: var(--c-white);
    box-shadow: 0 0 0 3px rgba(239,68,68,.1);
}
.dn-input-icon-wrap { position: relative; }
.dn-input-icon {
    position: absolute;
    left: 9px; top: 50%;
    transform: translateY(-50%);
    color: var(--c-muted);
    pointer-events: none;
}
.dn-input-with-icon { padding-left: 28px; }

.dn-filter-btns {
    display: flex;
    gap: 6px;
    align-items: flex-end;
}
.dn-btn-filter {
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
.dn-btn-filter:hover { background: #1f2937; }
.dn-btn-reset {
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
.dn-btn-reset:hover { background: #f3f4f6; color: var(--c-text); }

.dn-table-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    box-shadow: var(--c-shadow);
    overflow: hidden;
}
.dn-table-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-bottom: 1px solid var(--c-border);
    background: var(--c-bg);
    flex-wrap: wrap;
    gap: 6px;
}
.dn-table-count {
    font-size: 11.5px;
    color: var(--c-muted);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}
.dn-table-count strong { color: var(--c-text); font-weight: 700; }
.dn-filtered-pill {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 7px;
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.dn-page-info-top { font-size: 10.5px; color: var(--c-muted); }
.dn-table-topbar-right { display: flex; align-items: center; gap: 8px; }

.dn-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.dn-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 1400px;
}
.dn-table th {
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
.dn-table td {
    padding: 9px 11px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    color: var(--c-text2);
}
.dn-table tr:last-child td { border-bottom: none; }
.dn-tr { transition: background .12s; }
.dn-tr:hover td { background: #fafafa; }

.tc-no        { width: 45px;  text-align: center; }
.tc-date      { width: 88px;  }
.tc-note      { width: 160px; }
.tc-vendor    { width: 180px; }
.tc-invoice   { width: 140px; }
.tc-subtotal  { width: 90px;  }
.tc-tax       { width: 80px;  }
.tc-discount  { width: 90px;  }
.tc-amount    { width: 100px; }
.tc-used      { width: 100px; }
.tc-remaining { width: 100px; }
.tc-status    { width: 80px;  }
.tc-act       { width: 70px;  }

.td-serial   { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--c-bg); border-radius: 4px; font-size: 10px; color: var(--c-muted); font-weight: 600; }
.td-muted    { color: var(--c-muted); font-size: 10.5px; }
.td-date-main{ font-size: 11.5px; color: var(--c-text2); white-space: nowrap; font-weight: 500; }
.td-date-sub { font-size: 9.5px; color: var(--c-muted); }

.dn-note-chip {
    display: inline-block;
    padding: 3px 8px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 600;
    color: #991b1b;
    white-space: nowrap;
    font-family: 'Courier New', monospace;
    letter-spacing: .2px;
}

.td-vendor-name {
    font-weight: 500;
    color: var(--c-text);
    font-size: 12px;
    margin-bottom: 2px;
    max-width: 155px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dn-vendor-phone {
    font-size: 9.5px;
    color: var(--c-muted);
}

.dn-invoice-link {
    color: var(--c-brand);
    text-decoration: none;
    font-weight: 500;
    font-size: 11.5px;
}
.dn-invoice-link:hover { text-decoration: underline; }

.amount-subtotal { font-weight: 600; color: var(--c-text); font-size: 11.5px; }
.amount-tax { font-weight: 600; color: #3b82f6; font-size: 11.5px; }
.amount-discount { font-weight: 600; color: #f59e0b; font-size: 11.5px; }
.amount-main { font-weight: 700; color: var(--c-text); font-size: 12px; }
.amount-used { font-weight: 600; color: #3b82f6; font-size: 11.5px; }
.amount-remaining { font-weight: 600; color: #10b981; font-size: 11.5px; }

.dn-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    width: fit-content;
}
.dn-status--active    { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.dn-status--settled   { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }
.dn-status--partial   { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.dn-stat--green::before  { background: #22c55e; }
.dn-stat--purple::before { background: #8b5cf6; }
.dn-stat--orange::before { background: #f97316; }
.dn-stat--green .dn-stat-icon  { background: #dcfce7; color: #16a34a; }
.dn-stat--purple .dn-stat-icon { background: #f3e8ff; color: #7c3aed; }
.dn-stat--orange .dn-stat-icon { background: #fff7ed; color: #f97316; }

.dn-act-grp { display: flex; gap: 4px; align-items: center; }
.dn-act {
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
.dn-act--view    { background: #dbeafe; color: #2563eb; }
.dn-act--view:hover  { background: #bfdbfe; transform: scale(1.05); }
.dn-act--cancel  { background: #fee2e2; color: #dc2626; }
.dn-act--cancel:hover   { background: #fecaca; transform: scale(1.05); }

.dn-empty-cell { padding: 52px 20px; text-align: center; }
.dn-empty { display: inline-flex; flex-direction: column; align-items: center; gap: 8px; }
.dn-empty-icon {
    width: 56px; height: 56px;
    background: var(--c-bg);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: #d1d5db;
    margin-bottom: 4px;
}
.dn-empty-title { font-size: 13px; font-weight: 600; color: var(--c-text2); margin: 0; }
.dn-empty-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }
.dn-empty-sub a { color: var(--c-brand); }

.dn-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-top: 1px solid var(--c-border);
    background: var(--c-bg);
    flex-wrap: wrap;
    gap: 8px;
}
.dn-page-info { font-size: 10.5px; color: var(--c-muted); }
.dn-page-info strong { color: var(--c-text2); }
.dn-pages { display: flex; gap: 3px; align-items: center; flex-wrap: wrap; }
.dn-pg {
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
.dn-pg:hover         { background: #f3f4f6; border-color: #d1d5db; }
.dn-pg--active       { background: var(--c-brand); color: #fff; border-color: var(--c-brand); font-weight: 700; }
.dn-pg--active:hover { background: var(--c-brand); }
.dn-pg--dis          { color: #d1d5db; background: var(--c-bg); cursor: default; pointer-events: none; }
.dn-pg-dots          { font-size: 11px; color: var(--c-muted); padding: 0 2px; }

#alertBox { position: fixed; top: 16px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 7px; }
.dn-alert {
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
    animation: dnAIn .25s ease;
    min-width: 220px;
    max-width: 320px;
}
@keyframes dnAIn {
    from { transform: translateX(110%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.dn-alert-success { background: #f0fdf4; color: #166534; border-left: 3px solid #22c55e; }
.dn-alert-error   { background: #fef2f2; color: #991b1b; border-left: 3px solid #ef4444; }

@media (max-width: 1024px) {
    .dn-stats { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .dn-stats { grid-template-columns: 1fr; }
    .dn-filter-group { min-width: calc(50% - 4px); flex: 1 1 calc(50% - 4px); }
    .dn-filter-btns { width: 100%; }
}
@media (max-width: 480px) {
    .dn-wrap { padding: 10px; }
    .dn-header { flex-direction: column; align-items: flex-start; }
    .dn-filter-group { min-width: 100%; flex: 1 1 100%; }
    .dn-pagination { flex-direction: column; align-items: flex-start; }
    .dn-pages { justify-content: center; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
function showAlert(message, type = 'success') {
    const box = document.getElementById('alertBox');
    const el = document.createElement('div');
    el.className = 'dn-alert dn-alert-' + type;
    el.textContent = message;
    box.appendChild(el);
    setTimeout(() => el.remove(), 4500);
}

function cancelNote(id) {
    if (!confirm('Are you sure you want to cancel this debit note?')) {
        return;
    }

    fetch('/admin/debit-notes/' + id + '/cancel', {
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
            showAlert(data.message || 'Failed to cancel debit note', 'error');
        }
    })
    .catch(() => {
        showAlert('Something went wrong', 'error');
    });
}
</script>
@endpush
@endsection
