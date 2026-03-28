@extends('layouts.admin')

@section('title', 'Payment Out - Transactions')
@section('header-title', 'Payment Out')

@section('content')
<div class="po-wrap">
    <div id="alertBox"></div>

    {{-- ── Header ── --}}
    <div class="po-header">
        <div class="po-header-left">
            <div class="po-header-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div>
                <h1 class="po-title">Payment Out Transactions</h1>
                <p class="po-sub">Track and manage all outgoing payments to vendors, dealers, and distributors</p>
            </div>
        </div>
        <a href="{{ route('admin.payments-out.create') }}" class="po-btn-create">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New Payment Out
        </a>
    </div>

    {{-- ── Stats ── --}}
    <div class="po-stats">
        <div class="po-stat po-stat--orange">
            <div class="po-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <div class="po-stat-content">
                <div class="po-stat-label">Total Payments</div>
                <div class="po-stat-value">{{ $totalCount }}</div>
            </div>
        </div>
        <div class="po-stat po-stat--purple">
            <div class="po-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="po-stat-content">
                <div class="po-stat-label">Total Amount Paid</div>
                <div class="po-stat-value">₹ {{ number_format($totalAmount, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="po-filters">
        <div class="po-filters-header">
            <div class="po-filters-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Filters
            </div>
            @if(request()->anyFilled(['search','party_id','payment_method','from_date','to_date','warehouse_id']))
            <a href="{{ route('admin.payments-out.index') }}" class="po-clear-filters">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Clear all filters
            </a>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.payments-out.index') }}">
            <div class="po-filter-row">

                {{-- Search --}}
                <div class="po-filter-group">
                    <label class="po-filter-label">Search</label>
                    <div class="po-input-icon-wrap">
                        <svg class="po-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" name="search" class="po-input po-input-with-icon"
                               placeholder="Payment No / Ref…" value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Party --}}
                <div class="po-filter-group" style="min-width:155px;">
                    <label class="po-filter-label">Party</label>
                    <select name="party_id" class="po-select">
                        <option value="">All Parties</option>
                        @foreach($parties as $party)
                        <option value="{{ $party['id'] }}" {{ request('party_id') == $party['id'] ? 'selected' : '' }}>
                            {{ $party['name'] }} ({{ $party['party_type_text'] }})
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment Method --}}
                <div class="po-filter-group">
                    <label class="po-filter-label">Method</label>
                    <select name="payment_method" class="po-select">
                        <option value="">All Methods</option>
                        <option value="cash"          {{ request('payment_method') == 'cash'          ? 'selected' : '' }}>Cash</option>
                        <option value="upi"           {{ request('payment_method') == 'upi'           ? 'selected' : '' }}>UPI</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cheque"        {{ request('payment_method') == 'cheque'        ? 'selected' : '' }}>Cheque</option>
                        <option value="card"          {{ request('payment_method') == 'card'          ? 'selected' : '' }}>Card</option>
                    </select>
                </div>

                {{-- From Date --}}
                <div class="po-filter-group">
                    <label class="po-filter-label">From Date</label>
                    <input type="date" name="from_date" class="po-input" value="{{ request('from_date') }}">
                </div>

                {{-- To Date --}}
                <div class="po-filter-group">
                    <label class="po-filter-label">To Date</label>
                    <input type="date" name="to_date" class="po-input" value="{{ request('to_date') }}">
                </div>

                {{-- Buttons --}}
                <div class="po-filter-btns">
                    <button type="submit" class="po-btn-filter">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Apply
                    </button>
                    <a href="{{ route('admin.payments-out.index') }}" class="po-btn-reset">Reset</a>
                </div>

            </div>
        </form>
    </div>

    {{-- ── Table Card ── --}}
    <div class="po-table-card">

        <div class="po-table-topbar">
            <div class="po-table-count">
                <strong>{{ $payments->total() }}</strong> payment{{ $payments->total() != 1 ? 's' : '' }}
                @if(request()->anyFilled(['search','party_id','payment_method','from_date','to_date','warehouse_id']))
                <span class="po-filtered-pill">
                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Filtered
                </span>
                @endif
            </div>
            <div class="po-table-topbar-right">
                <div class="po-page-info-top">
                    Showing {{ $payments->firstItem() ?? 0 }}–{{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }}
                </div>
            </div>
        </div>

        <div class="po-table-wrap">
            <table class="po-tbl">
                <thead>
                    <tr>
                        <th class="tc-no">S. No.</th>
                        <th class="tc-date">Date</th>
                        <th class="tc-pno">Payment No</th>
                        <th class="tc-party">Party</th>
                        <th class="tc-type">Type</th>
                        <th class="tc-wh">Warehouse</th>
                        <th class="tc-amount">Amount</th>
                        <th class="tc-method">Method</th>
                        <th class="tc-alloc">Allocated To</th>
                        <th class="tc-act">Action</th>
                    </thead>
                <tbody>
                    @forelse($payments as $i => $payment)
                    @php
                        $amount      = $payment->amount instanceof \MongoDB\BSON\Decimal128
                                        ? (float) $payment->amount->__toString()
                                        : (float) $payment->amount;
                        $allocations = $payment->allocations ?? [];
                        $openingAlloc  = collect($allocations)->where('type', 'opening_balance')->sum('amount');
                        $invoiceAllocs = collect($allocations)->where('type', 'invoice')->count();
                        $creditAllocs  = collect($allocations)->where('type', 'credit_note')->count();

                        $allocInvoiceIds = collect($allocations)
                            ->where('type', 'invoice')
                            ->pluck('invoice_id')
                            ->unique()
                            ->toArray();
                        $warehouseNames = \App\Models\PurchaseInvoice::whereIn('_id', $allocInvoiceIds)
                            ->with('warehouse')
                            ->get()
                            ->pluck('warehouse.name')
                            ->unique()
                            ->filter()
                            ->implode(', ');

                        // Determine payment subtype display
                        $subtype = $payment->payment_subtype ?? 'purchase_payment';
                        $subtypeLabel = $subtype === 'credit_refund' ? 'Credit Refund' : 'Purchase Payment';
                        $subtypeClass = $subtype === 'credit_refund' ? 'po-subtype-refund' : 'po-subtype-purchase';
                    @endphp
                    <tr class="po-tr">
                        <td class="tc-no">
                            <span class="td-serial">{{ ($payments->currentPage() - 1) * $payments->perPage() + $i + 1 }}</span>
                        </td>
                        <td class="tc-date">
                            <div class="td-date-main">{{ $payment->payment_date->format('d M Y') }}</div>
                            <div class="td-date-sub">{{ $payment->payment_date->format('D') }}</div>
                        </td>
                        <td class="tc-pno">
                            <span class="po-pno-chip">{{ $payment->payment_number ?? 'N/A' }}</span>
                        </td>
                        <td class="tc-party">
                            <div class="td-party-name" title="{{ $payment->party->name ?? $payment->party->company_name ?? '—' }}">
                                {{ $payment->party->name ?? $payment->party->company_name ?? '—' }}
                            </div>
                            @if(($payment->party->phone ?? null))
                            <div class="td-party-phone">{{ $payment->party->phone }}</div>
                            @endif
                        </td>
                        <td class="tc-type">
                            <span class="po-subtype-badge {{ $subtypeClass }}">{{ $subtypeLabel }}</span>
                        </td>
                        <td class="tc-wh">
                            <span class="po-wh-badge">{{ $warehouseNames ?: '—' }}</span>
                        </td>
                        <td class="tc-amount">
                            <span class="td-amount">₹ {{ number_format($amount, 2) }}</span>
                        </td>
                        <td class="tc-method">
                            <span class="po-method-badge po-method--{{ $payment->payment_method }}">
                                {{ $payment->payment_method_text }}
                            </span>
                        </td>
                        <td class="tc-alloc">
                            <div class="po-alloc-wrap">
                                @if($openingAlloc > 0)
                                <span class="po-alloc-tag po-alloc--opening">
                                    Opening ₹{{ number_format($openingAlloc, 2) }}
                                </span>
                                @endif
                                @if($invoiceAllocs > 0)
                                <span class="po-alloc-tag po-alloc--invoice">
                                    {{ $invoiceAllocs }} Invoice{{ $invoiceAllocs > 1 ? 's' : '' }}
                                </span>
                                @endif
                                @if($creditAllocs > 0)
                                <span class="po-alloc-tag po-alloc--credit">
                                    {{ $creditAllocs }} Credit Note{{ $creditAllocs > 1 ? 's' : '' }}
                                </span>
                                @endif
                                @if($openingAlloc == 0 && $invoiceAllocs == 0 && $creditAllocs == 0)
                                <span class="td-muted">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="tc-act">
                            <div class="po-act-grp">
                                <button type="button" class="po-act po-act--view"
                                        onclick="viewPayment('{{ $payment->_id }}')" title="View Payment">
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
                        <td colspan="10" class="po-empty-cell">
                            <div class="po-empty">
                                <div class="po-empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <line x1="12" y1="1" x2="12" y2="23"/>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                    </svg>
                                </div>
                                <p class="po-empty-title">No payments found</p>
                                <p class="po-empty-sub">
                                    @if(request()->anyFilled(['search','party_id','payment_method','from_date','to_date','warehouse_id']))
                                        Try adjusting your filters or <a href="{{ route('admin.payments-out.index') }}">clear all</a>
                                    @else
                                        Get started by recording your first payment out
                                    @endif
                                </p>
                                <a href="{{ route('admin.payments-out.create') }}" class="po-btn-create" style="margin-top:4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    New Payment Out
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($payments->hasPages())
        <div class="po-pagination">
            <div class="po-page-info">
                Page <strong>{{ $payments->currentPage() }}</strong> of <strong>{{ $payments->lastPage() }}</strong>
                &nbsp;·&nbsp; {{ $payments->total() }} total records
            </div>
            <div class="po-pages">
                @if($payments->onFirstPage())
                    <span class="po-pg po-pg--dis">«</span>
                    <span class="po-pg po-pg--dis">‹</span>
                @else
                    <a href="{{ $payments->url(1) }}" class="po-pg" title="First">«</a>
                    <a href="{{ $payments->previousPageUrl() }}" class="po-pg" title="Previous">‹</a>
                @endif

                @php
                    $cur   = $payments->currentPage();
                    $last  = $payments->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $payments->url(1) }}" class="po-pg">1</a>
                    @if($start > 2)<span class="po-pg-dots">…</span>@endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="po-pg po-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $payments->url($p) }}" class="po-pg">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $last)
                    @if($end < $last - 1)<span class="po-pg-dots">…</span>@endif
                    <a href="{{ $payments->url($last) }}" class="po-pg">{{ $last }}</a>
                @endif

                @if($payments->hasMorePages())
                    <a href="{{ $payments->nextPageUrl() }}" class="po-pg" title="Next">›</a>
                    <a href="{{ $payments->url($last) }}" class="po-pg" title="Last">»</a>
                @else
                    <span class="po-pg po-pg--dis">›</span>
                    <span class="po-pg po-pg--dis">»</span>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ═══════════════════════════════════════════
     VIEW PAYMENT MODAL
════════════════════════════════════════════════ --}}
<div class="po-modal" id="viewModal" style="display:none;">
    <div class="po-modal-overlay" onclick="closeViewModal()"></div>
    <div class="po-modal-box">
        <div class="po-modal-head">
            <div class="po-modal-ico">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div>
                <div class="po-modal-title">Payment Out Details</div>
                <div class="po-modal-sub" id="modalPaymentNo">—</div>
            </div>
            <button class="po-modal-close" onclick="closeViewModal()">×</button>
        </div>

        <div class="po-modal-body">
            <div id="modalLoader" class="po-modal-loader">
                <div class="po-spinner"></div>
                <span>Loading…</span>
            </div>
            <div id="modalContent" style="display:none;"></div>
        </div>

        <div class="po-modal-foot">
            <button class="po-btn-modal-close" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>



@push('styles')
<style>
/* ─── Variables ─────────────────────────────────────────── */
:root {
    --po-brand:   #f97316;
    --po-brand-d: #ea580c;
    --po-brand-l: #fff7ed;
    --po-purple:  #7c3aed;
    --po-purple-d:#6d28d9;
    --po-purple-l:#f5f3ff;
    --po-text:    #111827;
    --po-text2:   #374151;
    --po-muted:   #6b7280;
    --po-border:  #e5e7eb;
    --po-bg:      #f9fafb;
    --po-white:   #ffffff;
    --po-shadow:  0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
    --po-shadow2: 0 4px 12px rgba(0,0,0,.08);
    --r:          7px;
    --r-sm:       5px;
}

/* ─── Wrap ──────────────────────────────────────────────── */
.po-wrap {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 12.5px;
    color: var(--po-text);
    padding: 16px;
    max-width: 100%;
}

/* ─── Header ────────────────────────────────────────────── */
.po-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--po-border);
    gap: 12px;
    flex-wrap: wrap;
}
.po-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
}
.po-header-icon {
    width: 38px; height: 38px;
    background: var(--po-brand-l);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    color: var(--po-brand);
    flex-shrink: 0;
}
.po-title { font-size: 17px; font-weight: 700; margin: 0 0 2px; letter-spacing: -.3px; }
.po-sub   { font-size: 11px; color: var(--po-muted); margin: 0; }

.po-btn-create {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 15px;
    background: #fc7d2b;
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
.po-btn-create:hover {
    background: #ea580c;
    transform: translateY(-1px);
}

/* ─── Stats ─────────────────────────────────────────────── */
.po-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 14px;
}
.po-stat {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    background: var(--po-white);
    border: 1px solid var(--po-border);
    border-radius: var(--r);
    box-shadow: var(--po-shadow);
    position: relative;
    overflow: hidden;
    transition: box-shadow .2s, transform .15s;
}
.po-stat:hover {
    box-shadow: var(--po-shadow2);
    transform: translateY(-1px);
}
.po-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: var(--r) var(--r) 0 0;
}
.po-stat--orange::before { background: #f97316; }
.po-stat--purple::before  { background: #7c3aed; }

.po-stat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.po-stat--orange .po-stat-icon { background: #fff7ed; color: #f97316; }
.po-stat--purple  .po-stat-icon { background: #f5f3ff; color: #7c3aed; }

.po-stat-label { font-size: 10.5px; color: var(--po-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
.po-stat-value { font-size: 17px; font-weight: 800; color: var(--po-text); letter-spacing: -.4px; margin-bottom: 2px; }

/* ─── Filters ───────────────────────────────────────────── */
.po-filters {
    background: var(--po-white);
    border: 1px solid var(--po-border);
    border-radius: var(--r);
    padding: 11px 14px 13px;
    margin-bottom: 12px;
    box-shadow: var(--po-shadow);
}
.po-filters-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.po-filters-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--po-text2);
    display: flex;
    align-items: center;
    gap: 5px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.po-clear-filters {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    color: #fc7d2b;
    text-decoration: none;
    font-weight: 500;
    transition: color .15s;
}
.po-clear-filters:hover { color: #ea580c; text-decoration: underline; }
.po-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}
.po-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 120px;
}
.po-filter-label {
    font-size: 9.5px;
    font-weight: 700;
    color: var(--po-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.po-input, .po-select {
    height: 31px;
    padding: 0 9px;
    border: 1px solid var(--po-border);
    border-radius: var(--r-sm);
    font-size: 11.5px;
    color: var(--po-text);
    background: var(--po-bg);
    transition: border-color .15s, background .15s, box-shadow .15s;
    outline: none;
}
.po-input:focus, .po-select:focus {
    border-color: var(--po-brand);
    background: var(--po-white);
    box-shadow: 0 0 0 3px rgba(249,115,22,.1);
}
.po-input-icon-wrap { position: relative; }
.po-input-icon {
    position: absolute;
    left: 9px; top: 50%;
    transform: translateY(-50%);
    color: var(--po-muted);
    pointer-events: none;
}
.po-input-with-icon { padding-left: 28px; }

.po-filter-btns {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}
.po-btn-filter {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 31px;
    padding: 0 14px;
    background: var(--po-text);
    color: #fff;
    border: none;
    border-radius: var(--r-sm);
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.po-btn-filter:hover { background: #1f2937; }
.po-btn-reset {
    display: inline-flex;
    align-items: center;
    height: 31px;
    padding: 0 12px;
    background: var(--po-bg);
    color: var(--po-muted);
    border: 1px solid var(--po-border);
    border-radius: var(--r-sm);
    font-size: 11.5px;
    text-decoration: none;
    transition: all .15s;
    font-weight: 500;
}
.po-btn-reset:hover { background: #f3f4f6; color: var(--po-text); }

/* ─── Table Card ────────────────────────────────────────── */
.po-table-card {
    background: var(--po-white);
    border: 1px solid var(--po-border);
    border-radius: var(--r);
    box-shadow: var(--po-shadow);
    overflow: hidden;
}
.po-table-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-bottom: 1px solid var(--po-border);
    background: var(--po-bg);
    flex-wrap: wrap;
    gap: 6px;
}
.po-table-count {
    font-size: 11.5px;
    color: var(--po-muted);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}
.po-table-count strong { color: var(--po-text); font-weight: 700; }
.po-filtered-pill {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 7px;
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid #fed7aa;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.po-page-info-top { font-size: 10.5px; color: var(--po-muted); }

/* ─── Table ─────────────────────────────────────────────── */
.po-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.po-tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 1050px;
}
.po-tbl th {
    padding: 9px 11px;
    background: #f3f4f6;
    font-size: 9.5px;
    font-weight: 700;
    color: var(--po-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
    border-bottom: 1px solid var(--po-border);
    white-space: nowrap;
    text-align: left;
}
.po-tbl td {
    padding: 9px 11px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    color: var(--po-text2);
}
.po-tbl tr:last-child td { border-bottom: none; }
.po-tr { transition: background .12s; }
.po-tr:hover td { background: #fafafa; }

/* Column widths */
.tc-no     { width: 45px;  text-align: center; }
.tc-date   { width: 90px;  }
.tc-pno    { width: 165px; }
.tc-party  { width: 170px; }
.tc-type   { width: 110px; }
.tc-wh     { width: 120px; }
.tc-amount { width: 110px; }
.tc-method { width: 110px; }
.tc-alloc  { width: 160px; }
.tc-act    { width: 60px;  }

/* Cell helpers */
.td-serial {
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px;
    background: var(--po-bg);
    border-radius: 4px;
    font-size: 10px; color: var(--po-muted); font-weight: 600;
}
.td-muted      { color: var(--po-muted); font-size: 10.5px; }
.td-date-main  { font-size: 11.5px; color: var(--po-text2); white-space: nowrap; font-weight: 500; }
.td-date-sub   { font-size: 9.5px; color: var(--po-muted); }
.td-party-name {
    font-weight: 500; color: var(--po-text); font-size: 12px; margin-bottom: 2px;
    max-width: 155px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.td-party-phone { font-size: 10.5px; color: var(--po-muted); }
.td-amount { font-weight: 700; font-size: 12.5px; color: #c2410c; }

/* Payment No chip */
.po-pno-chip {
    display: inline-block;
    padding: 3px 8px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 600;
    color: #c2410c;
    white-space: nowrap;
    font-family: 'Courier New', monospace;
    letter-spacing: .2px;
}

/* Subtype badges */
.po-subtype-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
}
.po-subtype-purchase {
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid #fed7aa;
}
.po-subtype-refund {
    background: #f5f3ff;
    color: #6d28d9;
    border: 1px solid #ddd6fe;
}

/* Warehouse badge */
.po-wh-badge {
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

/* Method badges */
.po-method-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
}
.po-method--cash          { background: #d1fae5; color: #065f46; }
.po-method--upi           { background: #dbeafe; color: #1e40af; }
.po-method--bank_transfer { background: #fef3c7; color: #92400e; }
.po-method--cheque        { background: #fed7aa; color: #9a3412; }
.po-method--card          { background: #e0e7ff; color: #3730a3; }

/* Allocation tags */
.po-alloc-wrap { display: flex; flex-wrap: wrap; gap: 4px; }
.po-alloc-tag {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 500;
    white-space: nowrap;
}
.po-alloc--opening { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
.po-alloc--invoice { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
.po-alloc--credit  { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

/* Actions */
.po-act-grp { display: flex; gap: 4px; align-items: center; }
.po-act {
    width: 28px; height: 28px;
    border: none;
    border-radius: var(--r-sm);
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .15s;
    text-decoration: none; flex-shrink: 0;
}
.po-act--view       { background: #dbeafe; color: #2563eb; }
.po-act--view:hover { background: #bfdbfe; transform: scale(1.05); }

/* Empty state */
.po-empty-cell { padding: 52px 20px; text-align: center; }
.po-empty { display: inline-flex; flex-direction: column; align-items: center; gap: 8px; }
.po-empty-icon {
    width: 56px; height: 56px;
    background: var(--po-bg);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: #d1d5db; margin-bottom: 4px;
}
.po-empty-title { font-size: 13px; font-weight: 600; color: var(--po-text2); margin: 0; }
.po-empty-sub   { font-size: 11px; color: var(--po-muted); margin: 0; }
.po-empty-sub a { color: #fc7d2b; text-decoration: none; }
.po-empty-sub a:hover { text-decoration: underline; }

/* ─── Pagination ────────────────────────────────────────── */
.po-pagination {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 14px;
    border-top: 1px solid var(--po-border);
    background: var(--po-bg);
    flex-wrap: wrap; gap: 8px;
}
.po-page-info { font-size: 10.5px; color: var(--po-muted); }
.po-pages { display: flex; gap: 3px; align-items: center; flex-wrap: wrap; }
.po-pg {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 28px; height: 28px; padding: 0 5px;
    border: 1px solid var(--po-border); border-radius: var(--r-sm);
    font-size: 11px; color: var(--po-text2);
    text-decoration: none; background: var(--po-white);
    transition: all .12s; font-weight: 500;
}
.po-pg:hover         { background: #f3f4f6; border-color: #d1d5db; }
.po-pg--active       { background: var(--po-brand); color: #fff; border-color: var(--po-brand); font-weight: 700; }
.po-pg--active:hover { background: var(--po-brand); }
.po-pg--dis          { color: #d1d5db; background: var(--po-bg); cursor: default; pointer-events: none; }
.po-pg-dots          { font-size: 11px; color: var(--po-muted); padding: 0 2px; }

/* ─── Modal ─────────────────────────────────────────────── */
.po-modal {
    position: fixed; inset: 0; z-index: 1000;
    display: flex; align-items: center; justify-content: center;
}
.po-modal-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(2px);
}
.po-modal-box {
    position: relative;
    background: var(--po-white);
    border-radius: 10px;
    width: 720px; max-width: 94%; max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 50px rgba(0,0,0,.15);
    animation: poMIn .2s ease;
    display: flex; flex-direction: column;
}
@keyframes poMIn {
    from { opacity:0; transform: scale(.95) translateY(10px); }
    to   { opacity:1; transform: scale(1) translateY(0); }
}
.po-modal-head {
    display: flex; align-items: center; gap: 11px;
    padding: 14px 18px;
    border-bottom: 1px solid var(--po-border);
    background: var(--po-bg);
    border-radius: 10px 10px 0 0;
    position: sticky; top: 0; z-index: 1;
}
.po-modal-ico {
    width: 34px; height: 34px;
    background: var(--po-brand);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.po-modal-title { font-size: 13.5px; font-weight: 700; margin-bottom: 1px; }
.po-modal-sub   { font-size: 10.5px; color: var(--po-muted); }
.po-modal-close {
    margin-left: auto;
    background: none; border: none;
    font-size: 20px; color: var(--po-muted);
    cursor: pointer; width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 5px; line-height: 1; transition: all .15s;
}
.po-modal-close:hover { background: #e5e7eb; color: var(--po-text); }
.po-modal-body { padding: 18px; flex: 1; }
.po-modal-foot {
    display: flex; justify-content: flex-end;
    padding: 12px 18px;
    border-top: 1px solid var(--po-border);
    background: #fafafa;
    border-radius: 0 0 10px 10px;
}
.po-btn-modal-close {
    padding: 7px 20px;
    border: 1px solid var(--po-border); border-radius: var(--r-sm);
    background: var(--po-bg); color: var(--po-text2);
    font-size: 12px; font-weight: 600; cursor: pointer; transition: background .15s;
}
.po-btn-modal-close:hover { background: #e5e7eb; }

.po-modal-loader {
    display: flex; align-items: center; justify-content: center;
    gap: 10px; padding: 40px; color: var(--po-muted); font-size: 13px;
}
.po-spinner {
    width: 22px; height: 22px;
    border: 3px solid #e5e7eb;
    border-top-color: var(--po-brand);
    border-radius: 50%;
    animation: poSpin .8s linear infinite;
    flex-shrink: 0;
}
@keyframes poSpin { to { transform: rotate(360deg); } }

/* Modal detail content */
.md-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
}
.md-item { display: flex; flex-direction: column; gap: 3px; }
.md-label {
    font-size: 10.5px; color: var(--po-muted); font-weight: 600;
    text-transform: uppercase; letter-spacing: .4px;
}
.md-value { font-size: 13px; font-weight: 600; color: var(--po-text); }
.md-value.green { color: #059669; }
.md-value.purple { color: #7c3aed; }
.md-value.orange { color: #f97316; }

.md-section-title {
    font-size: 11.5px; font-weight: 700; color: var(--po-muted);
    text-transform: uppercase; letter-spacing: .5px;
    margin: 0 0 10px; padding-bottom: 6px;
    border-bottom: 1px solid var(--po-border);
}
.md-alloc-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.md-alloc-table th {
    background: #f8fafc; padding: 8px 10px;
    text-align: left; font-size: 11px; font-weight: 600;
    color: var(--po-muted); border-bottom: 1px solid var(--po-border);
}
.md-alloc-table td {
    padding: 9px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle;
}
.md-alloc-table tbody tr:last-child td { border-bottom: none; }
.md-type-badge {
    display: inline-block; padding: 2px 8px; border-radius: 4px;
    font-size: 10.5px; font-weight: 600;
}
.md-type-badge.opening { background: #f3e8ff; color: #6b21a8; }
.md-type-badge.invoice { background: #dbeafe; color: #1e40af; }
.md-type-badge.credit  { background: #fef3c7; color: #92400e; }
.md-bal-change { display: flex; align-items: center; gap: 5px; font-size: 11px; }
.md-bal-from   { color: #dc2626; text-decoration: line-through; opacity: .7; }
.md-bal-arrow  { color: var(--po-muted); }
.md-bal-to     { color: #059669; font-weight: 600; }
.md-no-alloc   { text-align: center; padding: 20px; color: var(--po-muted); font-size: 12.5px; }

.md-summary {
    display: flex; gap: 0;
    border: 1px solid var(--po-border); border-radius: var(--r);
    overflow: hidden; margin-top: 16px;
}
.md-summary-item {
    flex: 1; padding: 12px 16px; text-align: center;
    border-right: 1px solid var(--po-border);
}
.md-summary-item:last-child { border-right: none; }
.md-summary-label { font-size: 10px; color: var(--po-muted); font-weight: 600; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .3px; }
.md-summary-val { font-size: 14px; font-weight: 700; }
.md-summary-val.green  { color: #059669; }
.md-summary-val.purple { color: #7c3aed; }
.md-summary-val.orange { color: #f97316; }

/* ─── Alert ─────────────────────────────────────────────── */
#alertBox { position: fixed; top: 16px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 7px; }
.po-alert {
    padding: 10px 14px; border-radius: 6px;
    font-size: 12px; font-weight: 500;
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
    animation: poAIn .25s ease;
    min-width: 220px; max-width: 320px;
}
@keyframes poAIn {
    from { transform: translateX(110%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.po-alert-success { background: #f0fdf4; color: #166534; border-left: 3px solid #22c55e; }
.po-alert-error   { background: #fef2f2; color: #991b1b; border-left: 3px solid #ef4444; }

/* ─── Responsive ────────────────────────────────────────── */
@media (max-width: 768px) {
    .po-stats { grid-template-columns: 1fr 1fr; }
    .po-filter-group { min-width: calc(50% - 4px); flex: 1 1 calc(50% - 4px); }
    .po-filter-btns { width: 100%; }
    .md-grid { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
    .po-wrap { padding: 10px; }
    .po-stats { grid-template-columns: 1fr; }
    .po-header { flex-direction: column; align-items: flex-start; }
    .po-filter-group { min-width: 100%; flex: 1 1 100%; }
    .po-pagination { flex-direction: column; align-items: flex-start; }
    .po-pages { justify-content: center; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
function viewPayment(id) {
    const modal = document.getElementById('viewModal');
    modal.style.display = 'flex';
    document.getElementById('modalPaymentNo').textContent  = 'Loading…';
    document.getElementById('modalLoader').style.display   = 'flex';
    document.getElementById('modalContent').style.display  = 'none';

    $.get('{{ route("admin.payments-out.show", "__ID__") }}'.replace('__ID__', id))
        .done(function(res) {
            document.getElementById('modalLoader').style.display = 'none';
            if (!res.success) { showAlert('Failed to load payment details', 'error'); closeViewModal(); return; }

            const p = res.payment;
            document.getElementById('modalPaymentNo').textContent = p.payment_number || '—';

            let html = '';

            html += `<div class="md-grid">
                <div class="md-item">
                    <span class="md-label">Payment Number</span>
                    <span class="md-value purple">${esc(p.payment_number)}</span>
                </div>
                <div class="md-item">
                    <span class="md-label">Date</span>
                    <span class="md-value">${esc(p.date)}</span>
                </div>
                <div class="md-item">
                    <span class="md-label">Party Name</span>
                    <span class="md-value">${esc(p.party_name)}</span>
                </div>
                <div class="md-item">
                    <span class="md-label">Party Type</span>
                    <span class="md-value">${esc(p.party_type)}</span>
                </div>
                <div class="md-item">
                    <span class="md-label">Amount Paid</span>
                    <span class="md-value orange">₹${fmt(p.amount)}</span>
                </div>
                <div class="md-item">
                    <span class="md-label">Payment Method</span>
                    <span class="md-value">${esc(p.payment_method)}</span>
                </div>
                <div class="md-item">
                    <span class="md-label">Reference No</span>
                    <span class="md-value">${esc(p.reference_no)}</span>
                </div>
                <div class="md-item">
                    <span class="md-label">Notes</span>
                    <span class="md-value">${esc(p.notes)}</span>
                </div>
            </div>`;

            const allocs = p.allocations || [];
            let openingTotal = 0, invoiceTotal = 0, creditTotal = 0, debitTotal = 0;

            html += `<p class="md-section-title">Allocation Breakdown</p>`;

            if (allocs.length === 0) {
                html += `<div class="md-no-alloc">No allocation details available</div>`;
            } else {
                html += `<table class="md-alloc-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Details</th>
                            <th style="text-align:right">Amount</th>
                            <th>Balance Change / Status</th>
                        </thead>
                    <tbody>`;

                allocs.forEach(function(a) {
                    if (a.type === 'opening_balance') {
                        openingTotal += parseFloat(a.amount) || 0;
                        html += `——
                            <td><span class="md-type-badge opening">Opening Balance</span>——
                            <td>${esc(a.description || 'Opening Balance Payment')}——
                            <td style="text-align:right"><strong>₹${fmt(a.amount)}</strong>——
                            <td><span style="color:var(--po-muted)">—</span>——
                        \)`;
                    }
                    else if (a.type === 'invoice') {
                        invoiceTotal += parseFloat(a.amount) || 0;
                        html += `——
                            <td><span class="md-type-badge invoice">Purchase Invoice</span>——
                            <td><strong>${esc(a.invoice_number)}</strong>
                                ${a.invoice_date ? `<br><small style="color:var(--po-muted);font-size:10px;">Date: ${a.invoice_date}</small>` : ''}
                                ${a.grand_total ? `<br><small style="color:var(--po-muted);font-size:10px;">Total: ₹${fmt(a.grand_total)}</small>` : ''}
                            ——
                            <td style="text-align:right"><strong>₹${fmt(a.amount)}</strong>——
                            <td>
                                <div class="md-bal-change">
                                    <span class="md-bal-from">₹${fmt(a.previous_balance)}</span>
                                    <span class="md-bal-arrow">→</span>
                                    <span class="md-bal-to">₹${fmt(a.new_balance)}</span>
                                </div>
                            ——
                        \)`;
                    }
                    else if (a.type === 'credit_note') {
                        creditTotal += parseFloat(a.amount) || 0;
                        const remainingAfter = a.new_remaining || 0;
                        const previousRemaining = a.previous_remaining || 0;

                        html += `——
                            <td><span class="md-type-badge credit">Credit Note Refund</span>——
                            <td><strong>${esc(a.credit_note_number)}</strong>
                                ${a.credit_date ? `<br><small style="color:var(--po-muted);font-size:10px;">Date: ${a.credit_date}</small>` : ''}
                                ${a.total_amount ? `<br><small style="color:var(--po-muted);font-size:10px;">Total: ₹${fmt(a.total_amount)}</small>` : ''}
                                ${a.description ? `<br><small style="color:var(--po-muted);font-size:10px;">${esc(a.description)}</small>` : ''}
                            ——
                            <td style="text-align:right"><strong style="color:#7c3aed;">₹${fmt(a.amount)}</strong>——
                            <td>
                                <div class="md-bal-change">
                                    <span class="md-bal-from">₹${fmt(previousRemaining)}</span>
                                    <span class="md-bal-arrow">→</span>
                                    <span class="md-bal-to">₹${fmt(remainingAfter)}</span>
                                </div>
                            ——
                        \)`;
                    }
                    else if (a.type === 'debit_note') {
                        debitTotal += parseFloat(a.amount) || 0;
                        const remainingAfter = a.new_remaining || 0;
                        const previousRemaining = a.previous_remaining || 0;

                        html += `——
                            <td><span class="md-type-badge" style="background:#f5f3ff; color:#6d28d9;">Debit Note</span>——
                            <td><strong>${esc(a.debit_note_number)}</strong>
                                ${a.debit_date ? `<br><small style="color:var(--po-muted);font-size:10px;">Date: ${a.debit_date}</small>` : ''}
                                ${a.total_amount ? `<br><small style="color:var(--po-muted);font-size:10px;">Total: ₹${fmt(a.total_amount)}</small>` : ''}
                                ${a.description ? `<br><small style="color:var(--po-muted);font-size:10px;">${esc(a.description)}</small>` : ''}
                            ——
                            <td style="text-align:right"><strong style="color:#059669;">₹${fmt(a.amount)}</strong>——
                            <td>
                                <div class="md-bal-change">
                                    <span class="md-bal-from">₹${fmt(previousRemaining)}</span>
                                    <span class="md-bal-arrow">→</span>
                                    <span class="md-bal-to">₹${fmt(remainingAfter)}</span>
                                </div>
                            ——
                        \)`;
                    }
                });
                html += `</tbody>`;
                html += `</table>`;
            }

            // Summary section with all allocation types
            html += `<div class="md-summary">
                <div class="md-summary-item">
                    <div class="md-summary-label">Cash Paid</div>
                    <div class="md-summary-val orange">₹${fmt(p.amount)}</div>
                </div>`;

            if (debitTotal > 0) {
                html += `<div class="md-summary-item">
                    <div class="md-summary-label">Debit Adjusted</div>
                    <div class="md-summary-val" style="color:#059669;">₹${fmt(debitTotal)}</div>
                </div>`;
            }

            if (creditTotal > 0) {
                html += `<div class="md-summary-item">
                    <div class="md-summary-label">Credit Refund</div>
                    <div class="md-summary-val" style="color:#7c3aed;">₹${fmt(creditTotal)}</div>
                </div>`;
            }

            if (openingTotal > 0) {
                html += `<div class="md-summary-item">
                    <div class="md-summary-label">Opening Settled</div>
                    <div class="md-summary-val purple">₹${fmt(openingTotal)}</div>
                </div>`;
            }

            if (invoiceTotal > 0) {
                html += `<div class="md-summary-item">
                    <div class="md-summary-label">Invoice Settled</div>
                    <div class="md-summary-val" style="color:#2563eb;">₹${fmt(invoiceTotal)}</div>
                </div>`;
            }

            html += `</div>`;

            document.getElementById('modalContent').innerHTML = html;
            document.getElementById('modalContent').style.display = 'block';
        })
        .fail(function() {
            document.getElementById('modalLoader').style.display = 'none';
            showAlert('Failed to load payment details', 'error');
            closeViewModal();
        });
}
function closeViewModal() {
    document.getElementById('viewModal').style.display    = 'none';
    document.getElementById('modalContent').innerHTML     = '';
    document.getElementById('modalContent').style.display = 'none';
    document.getElementById('modalLoader').style.display  = 'flex';
}

document.getElementById('viewModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeViewModal();
});

function fmt(n) { return (parseFloat(n) || 0).toFixed(2); }
function esc(s) {
    return String(s ?? '—')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function showAlert(msg, type = 'success') {
    const box = document.getElementById('alertBox');
    const el  = document.createElement('div');
    el.className   = 'po-alert po-alert-' + type;
    el.textContent = msg;
    box.appendChild(el);
    setTimeout(() => el.remove(), 4500);
}
</script>
@endpush

@endsection
