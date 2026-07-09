@extends('layouts.admin')

@section('title', 'Payment In - Transactions')
@section('header-title', 'Payment In')

@section('content')
<div class="pi-wrap">
    <div id="alertBox"></div>

    {{-- ── Header ── --}}
    <div class="pi-header">
        <div class="pi-header-left">
            <div class="pi-header-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div>
                <h1 class="pi-title">Payment In Transactions</h1>
                <p class="pi-sub">Track and manage all incoming payments</p>
            </div>
        </div>
        <a href="{{ route('admin.payments.create') }}" class="pi-btn-create">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New Payment In
        </a>
    </div>

    {{-- ── Stats ── --}}
    <div class="pi-stats">
        <div class="pi-stat pi-stat--green">
            <div class="pi-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <div class="pi-stat-content">
                <div class="pi-stat-label">Total Payments</div>
                <div class="pi-stat-value">{{ $totalCount }}</div>

            </div>
        </div>
        <div class="pi-stat pi-stat--blue">
            <div class="pi-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="pi-stat-content">
                <div class="pi-stat-label">Total Amount</div>
                <div class="pi-stat-value">₹ {{ number_format($totalAmount, 2) }}</div>

            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="pi-filters">
        <div class="pi-filters-header">
            <div class="pi-filters-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Filters
            </div>
            @if(request()->anyFilled(['search','party_id','payment_method','from_date','to_date','warehouse_id']))
            <a href="{{ route('admin.payments.index') }}" class="pi-clear-filters">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Clear all filters
            </a>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.payments.index') }}">
            <div class="pi-filter-row">

                {{-- Search --}}
                <div class="pi-filter-group">
                    <label class="pi-filter-label">Search</label>
                    <div class="pi-input-icon-wrap">
                        <svg class="pi-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" name="search" class="pi-input pi-input-with-icon"
                               placeholder="Payment No / Ref…" value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Party --}}
                <div class="pi-filter-group" style="min-width:155px;">
                    <label class="pi-filter-label">Party</label>
                    <select name="party_id" class="pi-select">
                        <option value="">All Parties</option>
                        @foreach($parties as $party)
                        <option value="{{ $party['id'] }}" {{ request('party_id') == $party['id'] ? 'selected' : '' }}>
                            {{ $party['name'] }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment Method --}}
                <div class="pi-filter-group">
                    <label class="pi-filter-label">Method</label>
                    <select name="payment_method" class="pi-select">
                        <option value="">All Methods</option>
                        <option value="cash"          {{ request('payment_method') == 'cash'          ? 'selected' : '' }}>Cash</option>
                        <option value="upi"           {{ request('payment_method') == 'upi'           ? 'selected' : '' }}>UPI</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cheque"        {{ request('payment_method') == 'cheque'        ? 'selected' : '' }}>Cheque</option>
                        <option value="card"          {{ request('payment_method') == 'card'          ? 'selected' : '' }}>Card</option>
                    </select>
                </div>


                {{-- From Date --}}
                <div class="pi-filter-group">
                    <label class="pi-filter-label">From Date</label>
                    <input type="date" name="from_date" class="pi-input" value="{{ request('from_date') }}">
                </div>

                {{-- To Date --}}
                <div class="pi-filter-group">
                    <label class="pi-filter-label">To Date</label>
                    <input type="date" name="to_date" class="pi-input" value="{{ request('to_date') }}">
                </div>

                {{-- Buttons --}}
                <div class="pi-filter-btns">
                    <button type="submit" class="pi-btn-filter">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Apply
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="pi-btn-reset">Reset</a>
                </div>

            </div>
        </form>
    </div>

    {{-- ── Table Card ── --}}
    <div class="pi-table-card">

        <div class="pi-table-topbar">
            <div class="pi-table-count">
                <strong>{{ $payments->total() }}</strong> payment{{ $payments->total() != 1 ? 's' : '' }}
                @if(request()->anyFilled(['search','party_id','payment_method','from_date','to_date','warehouse_id']))
                <span class="pi-filtered-pill">
                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Filtered
                </span>
                @endif
            </div>
            <div class="pi-table-topbar-right">
                <div class="pi-page-info-top">
                    Showing {{ $payments->firstItem() ?? 0 }}–{{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }}
                </div>
            </div>
        </div>

        <div class="pi-table-wrap">
            <table class="pi-tbl">
                <thead>
                    <tr>
                        <th class="tc-no hide-mob">S. No.</th>
                        <th class="tc-date">Date</th>
                        <th class="tc-pno">Payment No</th>
                        <th class="tc-party">Party</th>
                        <th class="tc-type">Type</th>
                        <th class="tc-wh">Warehouse</th>
                        <th class="tc-amount">Amount</th>
                        <th class="tc-method">Method</th>
                        <th class="tc-alloc">Allocated To</th>
                        <th class="tc-act">Action</th>
                    </tr>
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
                        $debitAllocs   = collect($allocations)->where('type', 'debit_note')->count(); // ← ADD THIS
                        $debitTotal    = collect($allocations)->where('type', 'debit_note')->sum('amount'); // ← ADD THIS

                        $allocInvoiceIds = collect($allocations)
                            ->where('type', 'invoice')
                            ->pluck('invoice_id')
                            ->unique()
                            ->toArray();
                        $warehouseNames = \App\Models\SalesInvoice::whereIn('_id', $allocInvoiceIds)
                            ->with('warehouse')
                            ->get()
                            ->pluck('warehouse.name')
                            ->unique()
                            ->filter()
                            ->implode(', ');
                    @endphp
                    <tr class="pi-tr">
                        <td class="tc-no hide-mob">
                            <span class="td-serial">{{ ($payments->currentPage() - 1) * $payments->perPage() + $i + 1 }}</span>
                        </td>
                        <td class="tc-date">
                            <div class="td-date-main">
                                <svg class="mob-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $payment->payment_date->format('d M Y') }}
                            </div>
                            <div class="td-date-sub hide-mob">{{ $payment->payment_date->format('D') }}</div>
                        </td>
                        <td class="tc-pno">
                            <span class="pi-pno-chip">{{ $payment->payment_number ?? 'N/A' }}</span>
                        </td>
                        <td class="tc-party">
                            @php $party = $payment->party_details; @endphp

                            <div class="td-party-name" title="{{ $party['name'] ?? '—' }}">
                                <svg class="mob-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                {{ $party['name'] ?? '—' }}
                            </div>

                            @if(!empty($party['phone']))
                            <div class="td-party-phone hide-mob">{{ $party['phone'] }}</div>
                            @endif
                        </td>
                        <td class="tc-type">
                            @php
                                $pt = $party['party_type'] ?? null;
                            @endphp
                            @if($pt)
                            <span class="pi-ptype pi-ptype--{{ $pt }}">{{ ucfirst($pt) }}</span>
                            @else
                            <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td class="tc-wh">
                            <span class="pi-wh-badge">{{ $warehouseNames ?: '—' }}</span>
                        </td>
                        <td class="tc-amount">
                            <span class="td-amount">₹ {{ number_format($amount, 2) }}</span>
                            @if(!empty($payment->discount) && (float) $payment->discount > 0)
                            <div style="font-size: 11px; color: #10b981; margin-top: 2px;" title="Discount given">
                                + ₹{{ number_format((float)$payment->discount, 2) }} Disc.
                            </div>
                            @endif
                        </td>
                        <td class="tc-method">
                            <span class="pi-method-badge pi-method--{{ $payment->payment_method }}">
                                {{ $payment->payment_method_text }}
                            </span>
                        </td>
                        <td class="tc-alloc">
                            <div class="pi-alloc-wrap">
                                @if($openingAlloc > 0)
                                <span class="pi-alloc-tag pi-alloc--opening">
                                    Opening ₹{{ number_format($openingAlloc, 2) }}
                                </span>
                                @endif
                                @if($invoiceAllocs > 0)
                                <span class="pi-alloc-tag pi-alloc--invoice">
                                    {{ $invoiceAllocs }} Invoice{{ $invoiceAllocs > 1 ? 's' : '' }}
                                </span>
                                @endif
                                @if($debitAllocs > 0)  {{-- ← ADD THIS BLOCK --}}
                                <span class="pi-alloc-tag pi-alloc--debit">
                                    Debit Refund ₹{{ number_format($debitTotal, 2) }}
                                </span>
                                @endif
                                @if($openingAlloc == 0 && $invoiceAllocs == 0 && $debitAllocs == 0)  {{-- ← UPDATE CONDITION --}}
                                <span class="td-muted">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="tc-act">
                            <div class="pi-act-grp">
                                <button type="button" class="pi-act pi-act--view"
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
                        <td colspan="10" class="pi-empty-cell">
                            <div class="pi-empty">
                                <div class="pi-empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <line x1="12" y1="1" x2="12" y2="23"/>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                    </svg>
                                </div>
                                <p class="pi-empty-title">No payments found</p>
                                <p class="pi-empty-sub">
                                    @if(request()->anyFilled(['search','party_id','payment_method','from_date','to_date','warehouse_id']))
                                        Try adjusting your filters or <a href="{{ route('admin.payments.index') }}">clear all</a>
                                    @else
                                        Get started by recording your first payment
                                    @endif
                                </p>
                                <a href="{{ route('admin.payments.create') }}" class="pi-btn-create" style="margin-top:4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    New Payment In
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
        <div class="pi-pagination">
            <div class="pi-page-info">
                Page <strong>{{ $payments->currentPage() }}</strong> of <strong>{{ $payments->lastPage() }}</strong>
                &nbsp;·&nbsp; {{ $payments->total() }} total records
            </div>
            <div class="pi-pages">
                @if($payments->onFirstPage())
                    <span class="pi-pg pi-pg--dis">«</span>
                    <span class="pi-pg pi-pg--dis">‹</span>
                @else
                    <a href="{{ $payments->url(1) }}" class="pi-pg" title="First">«</a>
                    <a href="{{ $payments->previousPageUrl() }}" class="pi-pg" title="Previous">‹</a>
                @endif

                @php
                    $cur   = $payments->currentPage();
                    $last  = $payments->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $payments->url(1) }}" class="pi-pg">1</a>
                    @if($start > 2)<span class="pi-pg-dots">…</span>@endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="pi-pg pi-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $payments->url($p) }}" class="pi-pg">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $last)
                    @if($end < $last - 1)<span class="pi-pg-dots">…</span>@endif
                    <a href="{{ $payments->url($last) }}" class="pi-pg">{{ $last }}</a>
                @endif

                @if($payments->hasMorePages())
                    <a href="{{ $payments->nextPageUrl() }}" class="pi-pg" title="Next">›</a>
                    <a href="{{ $payments->url($last) }}" class="pi-pg" title="Last">»</a>
                @else
                    <span class="pi-pg pi-pg--dis">›</span>
                    <span class="pi-pg pi-pg--dis">»</span>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ═══════════════════════════════════════════
     VIEW PAYMENT MODAL
════════════════════════════════════════════════ --}}
<div class="pi-modal" id="viewModal" style="display:none;">
    <div class="pi-modal-overlay" onclick="closeViewModal()"></div>
    <div class="pi-modal-box">
        <div class="pi-modal-head">
            <div class="pi-modal-ico">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div>
                <div class="pi-modal-title">Payment Details</div>
                <div class="pi-modal-sub" id="modalPaymentNo">—</div>
            </div>
            <button class="pi-modal-close" onclick="closeViewModal()">×</button>
        </div>

        <div class="pi-modal-body">
            <div id="modalLoader" class="pi-modal-loader">
                <div class="pi-spinner"></div>
                <span>Loading…</span>
            </div>
            <div id="modalContent" style="display:none;"></div>
        </div>

        <div class="pi-modal-foot">
            <button class="pi-btn-modal-close" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* ─── Variables ─────────────────────────────────────────── */
:root {
    --pi-brand:   #10b981;
    --pi-brand-d: #059669;
    --pi-brand-l: #ecfdf5;
    --pi-text:    #111827;
    --pi-text2:   #374151;
    --pi-muted:   #6b7280;
    --pi-border:  #e5e7eb;
    --pi-bg:      #f9fafb;
    --pi-white:   #ffffff;
    --pi-shadow:  0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
    --pi-shadow2: 0 4px 12px rgba(0,0,0,.08);
    --r:          7px;
    --r-sm:       5px;
}

/* ─── Wrap ──────────────────────────────────────────────── */
.pi-wrap {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 12.5px;
    color: var(--pi-text);
    padding: 16px;
    max-width: 100%;
}

/* ─── Header ────────────────────────────────────────────── */
.pi-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--pi-border);
    gap: 12px;
    flex-wrap: wrap;
}
.pi-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
}
.pi-header-icon {
    width: 38px; height: 38px;
    background: var(--pi-brand-l);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    color: var(--pi-brand);
    flex-shrink: 0;
}
.pi-title { font-size: 17px; font-weight: 700; margin: 0 0 2px; letter-spacing: -.3px; }
.pi-sub   { font-size: 11px; color: var(--pi-muted); margin: 0; }

.pi-btn-create {
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
    box-shadow: 0 2px 8px rgba(16,185,129,.3);
    white-space: nowrap;
}


/* ─── Stats ─────────────────────────────────────────────── */
.pi-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 14px;
}
.pi-stat {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    background: var(--pi-white);
    border: 1px solid var(--pi-border);
    border-radius: var(--r);
    box-shadow: var(--pi-shadow);
    position: relative;
    overflow: hidden;
    transition: box-shadow .2s, transform .15s;
}
.pi-stat:hover {
    box-shadow: var(--pi-shadow2);
    transform: translateY(-1px);
}
.pi-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: var(--r) var(--r) 0 0;
}
.pi-stat--green::before { background: #22c55e; }
.pi-stat--blue::before  { background: #3b82f6; }

.pi-stat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.pi-stat--green .pi-stat-icon { background: #dcfce7; color: #16a34a; }
.pi-stat--blue  .pi-stat-icon { background: #dbeafe; color: #2563eb; }

.pi-stat-label { font-size: 10.5px; color: var(--pi-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
.pi-stat-value { font-size: 17px; font-weight: 800; color: var(--pi-text); letter-spacing: -.4px; margin-bottom: 2px; }
.pi-stat-hint  { font-size: 10px; color: var(--pi-muted); }

/* ─── Filters ───────────────────────────────────────────── */
.pi-filters {
    background: var(--pi-white);
    border: 1px solid var(--pi-border);
    border-radius: var(--r);
    padding: 11px 14px 13px;
    margin-bottom: 12px;
    box-shadow: var(--pi-shadow);
}
.pi-filters-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.pi-filters-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--pi-text2);
    display: flex;
    align-items: center;
    gap: 5px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.pi-clear-filters {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    color: #fc7d2b;
    text-decoration: none;
    font-weight: 500;
    transition: color .15s;
}
.pi-clear-filters:hover { color: var(--pi-brand-d); text-decoration: underline; }
.md-type-badge.credit {
    background: #fef9c3;
    color: #854d0e;
    border: 1px solid #fde68a;
}
.pi-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}
.pi-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 120px;
}
.pi-filter-label {
    font-size: 9.5px;
    font-weight: 700;
    color: var(--pi-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.pi-input, .pi-select {
    height: 31px;
    padding: 0 9px;
    border: 1px solid var(--pi-border);
    border-radius: var(--r-sm);
    font-size: 11.5px;
    color: var(--pi-text);
    background: var(--pi-bg);
    transition: border-color .15s, background .15s, box-shadow .15s;
    outline: none;
}
.pi-input:focus, .pi-select:focus {
    border-color: var(--pi-brand);
    background: var(--pi-white);
    box-shadow: 0 0 0 3px rgba(16,185,129,.1);
}
.pi-input-icon-wrap { position: relative; }
.pi-input-icon {
    position: absolute;
    left: 9px; top: 50%;
    transform: translateY(-50%);
    color: var(--pi-muted);
    pointer-events: none;
}
.pi-input-with-icon { padding-left: 28px; }

.pi-filter-btns {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}
.pi-btn-filter {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 31px;
    padding: 0 14px;
    background: var(--pi-text);
    color: #fff;
    border: none;
    border-radius: var(--r-sm);
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.pi-btn-filter:hover { background: #1f2937; }
.pi-btn-reset {
    display: inline-flex;
    align-items: center;
    height: 31px;
    padding: 0 12px;
    background: var(--pi-bg);
    color: var(--pi-muted);
    border: 1px solid var(--pi-border);
    border-radius: var(--r-sm);
    font-size: 11.5px;
    text-decoration: none;
    transition: all .15s;
    font-weight: 500;
}
.pi-btn-reset:hover { background: #f3f4f6; color: var(--pi-text); }

/* ─── Table Card ────────────────────────────────────────── */
.pi-table-card {
    background: var(--pi-white);
    border: 1px solid var(--pi-border);
    border-radius: var(--r);
    box-shadow: var(--pi-shadow);
    overflow: hidden;
}
.pi-table-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-bottom: 1px solid var(--pi-border);
    background: var(--pi-bg);
    flex-wrap: wrap;
    gap: 6px;
}
.pi-table-count {
    font-size: 11.5px;
    color: var(--pi-muted);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}
.pi-ptype--vendor {
    background: #fee2e2;
    color: #991b1b;
}
.pi-table-count strong { color: var(--pi-text); font-weight: 700; }
.pi-filtered-pill {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 7px;
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.pi-page-info-top { font-size: 10.5px; color: var(--pi-muted); }
.pi-table-topbar-right { display: flex; align-items: center; gap: 8px; }

/* ─── Table ─────────────────────────────────────────────── */
.pi-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.pi-tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 1050px;
}
.pi-tbl th {
    padding: 9px 11px;
    background: #f3f4f6;
    font-size: 9.5px;
    font-weight: 700;
    color: var(--pi-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
    border-bottom: 1px solid var(--pi-border);
    white-space: nowrap;
    text-align: left;
    position: sticky;
    top: 0;
    z-index: 1;
}
.pi-tbl td {
    padding: 9px 11px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    color: var(--pi-text2);
}
.pi-tbl tr:last-child td { border-bottom: none; }
.pi-tr { transition: background .12s; }
.pi-tr:hover td { background: #fafafa; }

/* Column widths */
.tc-no     { width: 45px;  text-align: center; }
.tc-date   { width: 90px;  }
.tc-pno    { width: 165px; }
.tc-party  { width: 170px; }
.tc-type   { width: 90px;  }
.tc-wh     { width: 120px; }
.tc-amount { width: 110px; }
.tc-method { width: 110px; }
.tc-alloc  { width: 160px; }
.tc-act    { width: 60px;  }

/* Cell helpers */
.td-serial {
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px;
    background: var(--pi-bg);
    border-radius: 4px;
    font-size: 10px; color: var(--pi-muted); font-weight: 600;
}
.td-muted      { color: var(--pi-muted); font-size: 10.5px; }
.td-date-main  { font-size: 11.5px; color: var(--pi-text2); white-space: nowrap; font-weight: 500; }
.td-date-sub   { font-size: 9.5px; color: var(--pi-muted); }
.td-party-name {
    font-weight: 500; color: var(--pi-text); font-size: 12px; margin-bottom: 2px;
    max-width: 155px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.td-party-phone { font-size: 10.5px; color: var(--pi-muted); }
.td-amount { font-weight: 700; font-size: 12.5px; color: #065f46; }

/* Payment No chip */
.pi-pno-chip {
    display: inline-block;
    padding: 3px 8px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 600;
    color: #065f46;
    white-space: nowrap;
    font-family: 'Courier New', monospace;
    letter-spacing: .2px;
}

/* Party type badges */
.pi-ptype {
    display: inline-block;
    padding: 1px 5px;
    border-radius: 3px;
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.pi-ptype--customer    { background: #d1fae5; color: #065f46; }
.pi-ptype--dealer      { background: #dbeafe; color: #1e40af; }
.pi-ptype--distributor { background: #fef3c7; color: #92400e; }

/* Warehouse badge */
.pi-wh-badge {
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
.pi-method-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
}
.pi-method--cash          { background: #d1fae5; color: #065f46; }
.pi-method--upi           { background: #dbeafe; color: #1e40af; }
.pi-method--bank_transfer { background: #fef3c7; color: #92400e; }
.pi-method--cheque        { background: #fed7aa; color: #9a3412; }
.pi-method--card          { background: #e0e7ff; color: #3730a3; }

/* Allocation tags */
.pi-alloc-wrap { display: flex; flex-wrap: wrap; gap: 4px; }
.pi-alloc-tag {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 500;
    white-space: nowrap;
}
.pi-alloc--opening { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
.pi-alloc--invoice { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }

/* Actions */
.pi-act-grp { display: flex; gap: 4px; align-items: center; }
.pi-act {
    width: 28px; height: 28px;
    border: none;
    border-radius: var(--r-sm);
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .15s;
    text-decoration: none; flex-shrink: 0;
}
.pi-act--view       { background: #dbeafe; color: #2563eb; }
.pi-act--view:hover { background: #bfdbfe; transform: scale(1.05); }

/* Empty state */
.pi-empty-cell { padding: 52px 20px; text-align: center; }
.pi-empty { display: inline-flex; flex-direction: column; align-items: center; gap: 8px; }
.pi-empty-icon {
    width: 56px; height: 56px;
    background: var(--pi-bg);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: #d1d5db; margin-bottom: 4px;
}
.pi-empty-title { font-size: 13px; font-weight: 600; color: var(--pi-text2); margin: 0; }
.pi-empty-sub   { font-size: 11px; color: var(--pi-muted); margin: 0; }
.pi-empty-sub a { color: #fc7d2b}

/* ─── Pagination ────────────────────────────────────────── */
.pi-pagination {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 14px;
    border-top: 1px solid var(--pi-border);
    background: var(--pi-bg);
    flex-wrap: wrap; gap: 8px;
}
.pi-page-info { font-size: 10.5px; color: var(--pi-muted); }
.pi-page-info strong { color: var(--pi-text2); }
.pi-pages { display: flex; gap: 3px; align-items: center; flex-wrap: wrap; }
.pi-pg {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 28px; height: 28px; padding: 0 5px;
    border: 1px solid var(--pi-border); border-radius: var(--r-sm);
    font-size: 11px; color: var(--pi-text2);
    text-decoration: none; background: var(--pi-white);
    transition: all .12s; font-weight: 500;
}
.pi-pg:hover         { background: #f3f4f6; border-color: #d1d5db; }
.pi-pg--active       { background: var(--pi-brand); color: #fff; border-color: var(--pi-brand); font-weight: 700; }
.pi-pg--active:hover { background: var(--pi-brand); }
.pi-pg--dis          { color: #d1d5db; background: var(--pi-bg); cursor: default; pointer-events: none; }
.pi-pg-dots          { font-size: 11px; color: var(--pi-muted); padding: 0 2px; }

/* ─── Modal ─────────────────────────────────────────────── */
.pi-modal {
    position: fixed; inset: 0; z-index: 1000;
    display: flex; align-items: center; justify-content: center;
}
.pi-modal-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(2px);
}
.pi-modal-box {
    position: relative;
    background: var(--pi-white);
    border-radius: 10px;
    width: 720px; max-width: 94%; max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 50px rgba(0,0,0,.15);
    animation: piMIn .2s ease;
    display: flex; flex-direction: column;
}
@keyframes piMIn {
    from { opacity:0; transform: scale(.95) translateY(10px); }
    to   { opacity:1; transform: scale(1) translateY(0); }
}
.pi-modal-head {
    display: flex; align-items: center; gap: 11px;
    padding: 14px 18px;
    border-bottom: 1px solid var(--pi-border);
    background: var(--pi-bg);
    border-radius: 10px 10px 0 0;
    position: sticky; top: 0; z-index: 1;
}
.pi-modal-ico {
    width: 34px; height: 34px;
    background: var(--pi-brand);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.pi-modal-title { font-size: 13.5px; font-weight: 700; margin-bottom: 1px; }
.pi-modal-sub   { font-size: 10.5px; color: var(--pi-muted); }
.pi-modal-close {
    margin-left: auto;
    background: none; border: none;
    font-size: 20px; color: var(--pi-muted);
    cursor: pointer; width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 5px; line-height: 1; transition: all .15s;
}
.pi-modal-close:hover { background: #e5e7eb; color: var(--pi-text); }
.pi-modal-body { padding: 18px; flex: 1; }
.pi-modal-foot {
    display: flex; justify-content: flex-end;
    padding: 12px 18px;
    border-top: 1px solid var(--pi-border);
    background: #fafafa;
    border-radius: 0 0 10px 10px;
}
.pi-btn-modal-close {
    padding: 7px 20px;
    border: 1px solid var(--pi-border); border-radius: var(--r-sm);
    background: var(--pi-bg); color: var(--pi-text2);
    font-size: 12px; font-weight: 600; cursor: pointer; transition: background .15s;
}
.pi-btn-modal-close:hover { background: #e5e7eb; }

.pi-modal-loader {
    display: flex; align-items: center; justify-content: center;
    gap: 10px; padding: 40px; color: var(--pi-muted); font-size: 13px;
}
.pi-alloc--debit { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
.pi-spinner {
    width: 22px; height: 22px;
    border: 3px solid #e5e7eb;
    border-top-color: var(--pi-brand);
    border-radius: 50%;
    animation: piSpin .8s linear infinite;
    flex-shrink: 0;
}
@keyframes piSpin { to { transform: rotate(360deg); } }

/* Modal detail content */
.md-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
}
.md-item { display: flex; flex-direction: column; gap: 3px; }
.md-label {
    font-size: 10.5px; color: var(--pi-muted); font-weight: 600;
    text-transform: uppercase; letter-spacing: .4px;
}
.md-value { font-size: 13px; font-weight: 600; color: var(--pi-text); }
.md-value.green { color: #059669; }
.md-value.blue  { color: #2563eb; }

.md-section-title {
    font-size: 11.5px; font-weight: 700; color: var(--pi-muted);
    text-transform: uppercase; letter-spacing: .5px;
    margin: 0 0 10px; padding-bottom: 6px;
    border-bottom: 1px solid var(--pi-border);
}
.md-alloc-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.md-alloc-table th {
    background: #f8fafc; padding: 8px 10px;
    text-align: left; font-size: 11px; font-weight: 600;
    color: var(--pi-muted); border-bottom: 1px solid var(--pi-border);
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
.md-bal-change { display: flex; align-items: center; gap: 5px; font-size: 11px; }
.md-bal-from   { color: #dc2626; text-decoration: line-through; opacity: .7; }
.md-bal-arrow  { color: var(--pi-muted); }
.md-bal-to     { color: #059669; font-weight: 600; }
.md-no-alloc   { text-align: center; padding: 20px; color: var(--pi-muted); font-size: 12.5px; }

.md-summary {
    display: flex; gap: 0;
    border: 1px solid var(--pi-border); border-radius: var(--r);
    overflow: hidden; margin-top: 16px;
}
.md-summary-item {
    flex: 1; padding: 12px 16px; text-align: center;
    border-right: 1px solid var(--pi-border);
}
.md-summary-item:last-child { border-right: none; }
.md-summary-label { font-size: 10px; color: var(--pi-muted); font-weight: 600; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .3px; }
.md-summary-val { font-size: 14px; font-weight: 700; }
.md-summary-val.green  { color: #059669; }
.md-summary-val.purple { color: #7c3aed; }
.md-summary-val.blue   { color: #2563eb; }

/* ─── Alert ─────────────────────────────────────────────── */
#alertBox { position: fixed; top: 16px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 7px; }
.pi-alert {
    padding: 10px 14px; border-radius: 6px;
    font-size: 12px; font-weight: 500;
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
    animation: piAIn .25s ease;
    min-width: 220px; max-width: 320px;
}
@keyframes piAIn {
    from { transform: translateX(110%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.pi-alert-success { background: #f0fdf4; color: #166534; border-left: 3px solid #22c55e; }
.pi-alert-error   { background: #fef2f2; color: #991b1b; border-left: 3px solid #ef4444; }

/* ─── Responsive ────────────────────────────────────────── */
@media (min-width: 769px) {
    .mob-icon { display: none !important; }
}

@media (max-width: 768px) {
    .hide-mob { display: none !important; }
    
    .mob-icon { display: inline-block; vertical-align: middle; margin-right: 5px; width: 13px; height: 13px; color: var(--c-muted); }

    .pi-wrap { padding: 8px; background: #f3f4f6; min-height: 100vh;}
    .pi-header { background: #fff; padding: 12px; border-radius: 8px; margin-bottom: 12px; flex-direction: column; align-items: flex-start; }
    
    .pi-stats { display: flex; flex-wrap: nowrap; overflow-x: auto; scroll-snap-type: x mandatory; gap: 10px; margin-bottom: 12px; padding-bottom: 4px; -webkit-overflow-scrolling: touch; }
    .pi-stat { flex: 0 0 88%; scroll-snap-align: center; padding: 10px 14px; }
    .pi-stats::-webkit-scrollbar { display: none; }
    
    .pi-filters { padding: 12px; margin-bottom: 12px; }
    .pi-filter-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .full-width-mobile { grid-column: span 2; }
    .pi-filter-group { min-width: 100%; flex: 1 1 100%; }
    .pi-filter-btns { justify-content: space-between; margin-top: 4px; width: 100%; grid-column: span 2; display: flex; gap: 6px; }
    .pi-btn-filter, .pi-btn-reset { flex: 1; justify-content: center; }

    /* Table -> Compact Dense Cards */
    .pi-table-card { background: transparent; border: none; box-shadow: none; }
    .pi-table-topbar { background: transparent; padding: 0 4px 6px; border: none; }
    .pi-tbl { min-width: 100%; display: block; border: none; }
    .pi-tbl thead { display: none; }
    .pi-tbl tbody { display: block; width: 100%; }
    
    .pi-tr {
        display: flex; flex-wrap: wrap; align-content: flex-start; align-items: center;
        background: #fff !important; border-radius: 8px;
        padding: 12px 14px 10px; margin-bottom: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;
        position: relative;
    }
    .pi-tr:hover td { background: transparent; }
    .pi-tbl td { border: none; padding: 0; display: flex; align-items: center; }

    /* Row 1: Party (Left), Amount (Right) */
    .tc-party { width: calc(100% - 130px); order: 1; margin-bottom: 6px; }
    .td-party-name { font-size: 13.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: flex; align-items: center; }
    
    .tc-amount { width: 130px; order: 2; margin-bottom: 6px; justify-content: flex-end; }
    .td-amount { font-size: 14px; font-weight: 700; color: var(--c-text); }
    
    /* Row 2: Payment No (Left), Date (Right) */
    .tc-pno { width: 55%; order: 3; margin-bottom: 10px; }
    .pi-pno-chip { font-size: 10px; padding: 2px 6px; }
    
    .tc-date { width: 45%; order: 4; margin-bottom: 10px; justify-content: flex-end; }
    .td-date-main { display: flex; align-items: center; font-size: 11px; color: #4b5563; font-weight: 600;}
    
    /* Row 3: Badges Collection (Left-flowing) */
    .tc-type, .tc-wh, .tc-method {
        width: auto !important; order: 5; margin-right: 6px; margin-bottom: 4px;
    }
    
    .pi-ptype, .pi-wh-badge, .pi-method-badge {
        font-size: 8.5px; padding: 2px 5px; border-radius: 4px;
    }
    
    /* Row 4: Allocated to (Label + tags horizontal) */
    .tc-alloc {
        width: 100%; order: 6; margin-top: 4px; margin-bottom: 6px;
        flex-direction: column; align-items: flex-start;
        border-top: 1px dashed #f1f5f9; padding-top: 8px;
    }
    .tc-alloc::before {
        content: "Allocated To";
        font-size: 8px;
        font-weight: 700;
        color: #94a3b8;
        margin-bottom: 4px;
        text-transform: uppercase;
    }
    .pi-alloc-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }

    /* Row 5: Actions */
    .tc-act { width: 100%; order: 10; margin-left: auto; margin-top: 4px; border-top: 1px solid #e5e7eb; padding-top: 10px; justify-content: flex-end; }
    .pi-act-grp { justify-content: flex-end; gap: 5px; }
    .pi-act { width: 26px; height: 26px; border-radius: 5px; }

    .pi-tbl td.pi-empty-cell { width: 100%; display: block; text-align: center; }
    .pi-pagination { flex-direction: column; align-items: center; background: transparent; border: none; padding: 0; }
    .pi-pages { justify-content: center; width: 100%; margin-top: 10px; }
    .md-grid { grid-template-columns: 1fr; }
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

    $.get('{{ route("admin.payments.show", "__ID__") }}'.replace('__ID__', id))
        .done(function(res) {
            document.getElementById('modalLoader').style.display = 'none';
            if (!res.success) { showAlert('Failed to load payment details', 'error'); closeViewModal(); return; }

            const p = res.payment;
            document.getElementById('modalPaymentNo').textContent = p.payment_number || '—';

            let html = '';

            html += `<div class="md-grid">
                <div class="md-item">
                    <span class="md-label">Payment Number</span>
                    <span class="md-value blue">${esc(p.payment_number)}</span>
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
                    <span class="md-label">Amount Received</span>
                    <span class="md-value green">₹${fmt(p.amount)}</span>
                </div>
                ${p.discount > 0 ? `
                <div class="md-item">
                    <span class="md-label">Discount Given</span>
                    <span class="md-value" style="color: #10b981; font-weight: 600;">₹${fmt(p.discount)}</span>
                </div>
                ` : ''}
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
                        </tr>
                    </thead><tbody>`;

                allocs.forEach(function(a) {
                    if (a.type === 'opening_balance') {
                        openingTotal += parseFloat(a.amount) || 0;
                        html += `<tr>
                            <td><span class="md-type-badge opening">Opening Balance</span></td>
                            <td>${esc(a.description || 'Opening Balance Payment')}</td>
                            <td style="text-align:right"><strong>₹${fmt(a.amount)}</strong></td>
                            <td><span style="color:var(--pi-muted)">—</span></td>
                        </tr>`;
                    }
                    else if (a.type === 'invoice') {
                        invoiceTotal += parseFloat(a.amount) || 0;
                        html += `<tr>
                            <td><span class="md-type-badge invoice">Invoice</span></td>
                            <td><strong>${esc(a.invoice_number)}</strong>
                                ${a.invoice_date ? `<br><small style="color:var(--pi-muted);font-size:10px;">Date: ${a.invoice_date}</small>` : ''}
                                ${a.grand_total ? `<br><small style="color:var(--pi-muted);font-size:10px;">Total: ₹${fmt(a.grand_total)}</small>` : ''}
                            </td>
                            <td style="text-align:right"><strong>₹${fmt(a.amount)}</strong></td>
                            <td>
                                <div class="md-bal-change">
                                    <span class="md-bal-from">₹${fmt(a.previous_balance)}</span>
                                    <span class="md-bal-arrow">→</span>
                                    <span class="md-bal-to">₹${fmt(a.new_balance)}</span>
                                </div>
                            </td>
                        </tr>`;
                    }
                    else if (a.type === 'credit_note') {
                        creditTotal += parseFloat(a.amount) || 0;
                        html += `<tr>
                            <td><span class="md-type-badge credit">Credit Note</span></td>
                            <td><strong>${esc(a.credit_note_number)}</strong><br>
                                <small style="color:var(--pi-muted);font-size:10px;">${esc(a.description || '')}</small>
                            </td>
                            <td style="text-align:right"><strong style="color:#059669;">₹${fmt(a.amount)}</strong></td>
                            <td><span style="color:#059669;font-size:11px;">Adjusted</span></td>
                        </tr>`;
                    }
                    else if (a.type === 'debit_note') {
                        debitTotal += parseFloat(a.amount) || 0;
                        const remainingAfter = a.new_remaining || 0;
                        const previousRemaining = a.previous_remaining || 0;

                        html += `<tr>
                            <td><span class="md-type-badge" style="background:#f5f3ff; color:#6d28d9;">Debit Note</span></td>
                            <td><strong>${esc(a.debit_note_number)}</strong>
                                ${a.debit_date ? `<br><small style="color:var(--pi-muted);font-size:10px;">Date: ${a.debit_date}</small>` : ''}
                                ${a.total_amount ? `<br><small style="color:var(--pi-muted);font-size:10px;">Total: ₹${fmt(a.total_amount)}</small>` : ''}
                                ${a.description ? `<br><small style="color:var(--pi-muted);font-size:10px;">${esc(a.description)}</small>` : ''}
                            </td>
                            <td style="text-align:right"><strong style="color:#7c3aed;">₹${fmt(a.amount)}</strong></td>
                            <td>
                                <div class="md-bal-change">
                                    <span class="md-bal-from">₹${fmt(previousRemaining)}</span>
                                    <span class="md-bal-arrow">→</span>
                                    <span class="md-bal-to">₹${fmt(remainingAfter)}</span>
                                </div>
                            </td>
                        </tr>`;
                    }
                });
                html += `</tbody></table>`;
            }

            // Summary section with debit notes included
            html += `<div class="md-summary">
                <div class="md-summary-item">
                    <div class="md-summary-label">Cash Received</div>
                    <div class="md-summary-val green">₹${fmt(p.amount)}</div>
                </div>`;

            if (creditTotal > 0) {
                html += `<div class="md-summary-item">
                    <div class="md-summary-label">Credit Adjusted</div>
                    <div class="md-summary-val" style="color:#059669;">₹${fmt(creditTotal)}</div>
                </div>`;
            }

            if (debitTotal > 0) {
                html += `<div class="md-summary-item">
                    <div class="md-summary-label">Debit Refund</div>
                    <div class="md-summary-val" style="color:#7c3aed;">₹${fmt(debitTotal)}</div>
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
                    <div class="md-summary-val blue">₹${fmt(invoiceTotal)}</div>
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
    el.className   = 'pi-alert pi-alert-' + type;
    el.textContent = msg;
    box.appendChild(el);
    setTimeout(() => el.remove(), 4500);
}
</script>
@endpush
