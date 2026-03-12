@extends('layouts.admin')

@section('title', 'Sales Invoices')
@section('header-title', 'Sales')

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
                <h1 class="si-title">Sales Invoices</h1>
                <p class="si-sub">Manage and track all your sales transactions</p>
            </div>
        </div>
        <a href="{{ route('admin.sales.create') }}" class="si-btn-create">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Invoice
        </a>
    </div>

    {{-- ── Stats ── --}}
    <div class="si-stats">
        <div class="si-stat si-stat--green">
            <div class="si-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="si-stat-content">
                <div class="si-stat-label">Total Sales</div>
                <div class="si-stat-value">₹ {{ number_format($totalSales, 2) }}</div>
                <div class="si-stat-hint">All confirmed invoices</div>
            </div>
        </div>
        <div class="si-stat si-stat--blue">
            <div class="si-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="si-stat-content">
                <div class="si-stat-label">Collected</div>
                <div class="si-stat-value">₹ {{ number_format($totalPaid, 2) }}</div>
                <div class="si-stat-hint">Amount received</div>
            </div>
        </div>
        <div class="si-stat si-stat--orange">
            <div class="si-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="si-stat-content">
                <div class="si-stat-label">Outstanding</div>
                <div class="si-stat-value">₹ {{ number_format($totalUnpaid, 2) }}</div>
                <div class="si-stat-hint">Pending collection</div>
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
            @if(request()->anyFilled(['date','invoice_number','party_id','payment_status','status','period','date_from','date_to','invoice_type']))
            <a href="{{ route('admin.sales.index') }}" class="si-clear-filters">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Clear all filters
            </a>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.sales.index') }}" id="filterForm">
            <div class="si-filter-row">

                {{-- Period --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Period</label>
                    <select name="period" class="si-select" id="periodSelect" onchange="handlePeriodChange(this)">
                        <option value="">All Time</option>
                        <option value="today"  {{ request('period') == 'today'  ? 'selected' : '' }}>Today</option>
                        <option value="7"      {{ request('period') == '7'      ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30"     {{ request('period') == '30'     ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90"     {{ request('period') == '90'     ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="365"    {{ request('period') == '365'    ? 'selected' : '' }}>Last 365 Days</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                {{-- Single Date --}}
                <div class="si-filter-group" id="grpSingleDate"
                     style="{{ request()->filled('period') ? 'display:none' : '' }}">
                    <label class="si-filter-label">Date</label>
                    <input type="date" name="date" class="si-input" value="{{ request('date') }}">
                </div>

                {{-- Custom Range --}}
                <div class="si-filter-group" id="grpDateFrom"
                     style="{{ request('period') == 'custom' ? '' : 'display:none' }}">
                    <label class="si-filter-label">From</label>
                    <input type="date" name="date_from" class="si-input" value="{{ request('date_from') }}">
                </div>
                <div class="si-filter-group" id="grpDateTo"
                     style="{{ request('period') == 'custom' ? '' : 'display:none' }}">
                    <label class="si-filter-label">To</label>
                    <input type="date" name="date_to" class="si-input" value="{{ request('date_to') }}">
                </div>

                {{-- Invoice Number (FIXED: label changed) --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Invoice Number</label>
                    <div class="si-input-icon-wrap">
                        <svg class="si-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="invoice_number" class="si-input si-input-with-icon"
                               placeholder="Search invoice..." value="{{ request('invoice_number') }}">
                    </div>
                </div>

                {{-- Invoice Type (FIXED: now works) --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Type</label>
                    <select name="invoice_type" class="si-select">
                        <option value="">All Types</option>
                        <option value="gst"  {{ request('invoice_type') == 'gst'  ? 'selected' : '' }}>GST Invoice</option>
                        <option value="cash" {{ request('invoice_type') == 'cash' ? 'selected' : '' }}>Cash Memo</option>
                    </select>
                </div>

                {{-- Party --}}
                <div class="si-filter-group" style="min-width:155px;">
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

                {{-- Payment Status (NEW) --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Payment Status</label>
                    <select name="payment_status" class="si-select">
                        <option value="">All</option>
                        <option value="paid"    {{ request('payment_status') == 'paid'    ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid"  {{ request('payment_status') == 'unpaid'  ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>

                {{-- Invoice Status (FIXED: only draft & confirmed) --}}
                <div class="si-filter-group">
                    <label class="si-filter-label">Invoice Status</label>
                    <select name="status" class="si-select">
                        <option value="">All</option>
                        <option value="draft"     {{ request('status') == 'draft'     ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>

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
                    <a href="{{ route('admin.sales.index') }}" class="si-btn-reset">Reset</a>
                </div>

            </div>
        </form>
    </div>

    {{-- ── Table ── --}}
    <div class="si-table-card">

        <div class="si-table-topbar">
            <div class="si-table-count">
                <strong>{{ $invoices->total() }}</strong> invoice{{ $invoices->total() != 1 ? 's' : '' }}
                @if(request()->anyFilled(['date','invoice_number','party_id','payment_status','status','period','date_from','date_to','invoice_type']))
                    <span class="si-filtered-pill">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        Filtered
                    </span>
                @endif
            </div>
            <div class="si-table-topbar-right">
                <div class="si-page-info-top">
                    Showing {{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }}
                </div>
            </div>
        </div>

        <div class="si-table-wrap">
            <table class="si-table">
                <thead>
                    <tr>
                        <th class="tc-no">S. No.</th>
                        <th class="tc-date">Date</th>
                        <th class="tc-inv">Invoice No.</th>
                        <th class="tc-type">Type</th>
                        <th class="tc-party">Party</th>
                        <th class="tc-due">Due</th>
                        <th class="tc-wh">Warehouse</th>
                        <th class="tc-amount">Amount</th> {{-- Combined Amount Column --}}
                        <th class="tc-payment-status">Payment Status</th>
                        <th class="tc-inv-status">Invoice Status</th>
                        <th class="tc-act">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $i => $invoice)
                    <tr class="si-tr">
                        <td class="tc-no">
                            <span class="td-serial">{{ ($invoices->currentPage() - 1) * $invoices->perPage() + $i + 1 }}</span>
                        </td>
                        <td class="tc-date">
                            <div class="td-date-main">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</div>
                            <div class="td-date-sub">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('D') }}</div>
                        </td>
                        <td class="tc-inv">
                            <span class="si-inv-chip">{{ $invoice->invoice_number }}</span>
                        </td>

                        {{-- Invoice Type --}}
                        <td class="tc-type">
                            @if($invoice->invoice_type === 'gst')
                                <span class="si-type-badge si-type--gst">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                    GST
                                </span>
                            @elseif($invoice->invoice_type === 'cash')
                                <span class="si-type-badge si-type--cash">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                    Cash
                                </span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>

                        <td class="tc-party">
                            @php
                                $partyName = optional($invoice->party)->name ?? 'N/A';
                                $partyType = optional($invoice->party)->party_type ?? '';
                            @endphp
                            <div class="td-party-name" title="{{ $partyName }}">{{ $partyName }}</div>
                            @if($partyType)
                                <span class="si-ptype si-ptype--{{ $partyType }}">{{ ucfirst($partyType) }}</span>
                            @endif
                        </td>

                        <td class="tc-due">
                            @if($invoice->due_date)
                                @php
                                    $due   = \Carbon\Carbon::parse($invoice->due_date);
                                    $today = \Carbon\Carbon::today();
                                    $diff  = (int) $today->diffInDays($due, false);
                                    if ($invoice->payment_status === 'paid') {
                                        $cls = 'si-due--paid'; $txt = 'Paid';
                                    } elseif ($diff < 0) {
                                        $cls = 'si-due--over'; $txt = abs($diff).'d over';
                                    } elseif ($diff === 0) {
                                        $cls = 'si-due--today'; $txt = 'Today';
                                    } else {
                                        $cls = 'si-due--ok'; $txt = $diff.'d left';
                                    }
                                @endphp
                                <span class="si-due {{ $cls }}">{{ $txt }}</span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td class="tc-wh">
                            <span class="si-wh-badge">
                                {{ optional($invoice->warehouse)->name ?? '—' }}
                            </span>
                        </td>
                        {{-- Combined Amount Column with Payment Status --}}
                        <td class="tc-amount">
                            <div class="amount-display">
                                <span class="amount-main">₹ {{ number_format($invoice->grand_total, 2) }}</span>

                                @if($invoice->payment_status == 'partial')
                                    <span class="amount-partial">
                                        (₹ {{ number_format($invoice->total_paid ?? 0, 2) }} paid)
                                    </span>
                                    <span class="amount-balance">
                                        Bal: ₹ {{ number_format($invoice->balance_amount ?? 0, 2) }}
                                    </span>
                                @elseif($invoice->payment_status == 'unpaid')
                                    <span class="amount-unpaid">(Full amount due)</span>
                                @elseif($invoice->payment_status == 'paid')
                                    <span class="amount-paid">(Fully paid)</span>
                                @endif
                            </div>
                        </td>

                        {{-- Payment Status Column --}}
                        <td class="tc-payment-status">
                            <span class="si-badge si-badge--{{ $invoice->payment_status }}">
                                {{ ucfirst($invoice->payment_status) }}
                            </span>
                        </td>

                        {{-- Invoice Status Column --}}
                        <td class="tc-inv-status">
                            @php
                                $statusMap = [
                                    'draft'     => ['cls' => 'inv-draft',     'label' => 'Draft'],
                                    'confirmed' => ['cls' => 'inv-confirmed', 'label' => 'Confirmed'],
                                    'completed' => ['cls' => 'inv-completed', 'label' => 'Completed'],
                                    'cancelled' => ['cls' => 'inv-cancelled', 'label' => 'Cancelled'],
                                ];
                                $sm = $statusMap[$invoice->status] ?? $statusMap['draft'];
                            @endphp
                            <span class="si-inv-status {{ $sm['cls'] }}">{{ $sm['label'] }}</span>
                        </td>

                        <td class="tc-act">
                            <div class="si-act-grp">
                                <a href="{{ route('admin.sales.show', $invoice->_id) }}"
                                class="si-act si-act--view" title="View Invoice">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>

                                @if($invoice->status === 'draft')
                                    <a href="{{ route('admin.sales.edit', $invoice->_id) }}"
                                    class="si-act si-act--edit" title="Edit Invoice">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>
                                    <button type="button" class="si-act si-act--del del-btn" data-id="{{ $invoice->_id }}" title="Delete Invoice">
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
                        <td colspan="11" class="si-empty-cell">
                            <div class="si-empty">
                                <div class="si-empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                        <line x1="16" y1="13" x2="8" y2="13"/>
                                        <line x1="16" y1="17" x2="8" y2="17"/>
                                    </svg>
                                </div>
                                <p class="si-empty-title">No invoices found</p>
                                <p class="si-empty-sub">
                                    @if(request()->anyFilled(['date','invoice_number','party_id','payment_status','status','period','date_from','date_to','invoice_type']))
                                        Try adjusting your filters or <a href="{{ route('admin.sales.index') }}">clear all</a>
                                    @else
                                        Get started by creating your first invoice
                                    @endif
                                </p>
                                <a href="{{ route('admin.sales.create') }}" class="si-btn-create" style="margin-top:4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Create Invoice
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($invoices->hasPages())
        <div class="si-pagination">
            <div class="si-page-info">
                Page <strong>{{ $invoices->currentPage() }}</strong> of <strong>{{ $invoices->lastPage() }}</strong>
                &nbsp;·&nbsp; {{ $invoices->total() }} total records
            </div>
            <div class="si-pages">
                @if($invoices->onFirstPage())
                    <span class="si-pg si-pg--dis">«</span>
                    <span class="si-pg si-pg--dis">‹</span>
                @else
                    <a href="{{ $invoices->url(1) }}" class="si-pg" title="First">«</a>
                    <a href="{{ $invoices->previousPageUrl() }}" class="si-pg" title="Previous">‹</a>
                @endif

                @php
                    $cur   = $invoices->currentPage();
                    $last  = $invoices->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $invoices->url(1) }}" class="si-pg">1</a>
                    @if($start > 2) <span class="si-pg-dots">…</span> @endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="si-pg si-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $invoices->url($p) }}" class="si-pg">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $last)
                    @if($end < $last - 1) <span class="si-pg-dots">…</span> @endif
                    <a href="{{ $invoices->url($last) }}" class="si-pg">{{ $last }}</a>
                @endif

                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="si-pg" title="Next">›</a>
                    <a href="{{ $invoices->url($last) }}" class="si-pg" title="Last">»</a>
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
            <div class="si-modal-ico">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
            </div>
            <div>
                <div class="si-modal-title">Delete Invoice</div>
                <div class="si-modal-sub">Stock will be automatically restored</div>
            </div>
            <button class="si-modal-close" onclick="closeDelModal()">×</button>
        </div>
        <div class="si-modal-body">
            <p>Are you sure you want to permanently delete this invoice? This action cannot be undone.</p>
        </div>
        <div class="si-modal-foot">
            <button class="si-btn-cancel" onclick="closeDelModal()">Cancel</button>
            <button class="si-btn-del-confirm" id="confirmDel">Delete Invoice</button>
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
    grid-template-columns: repeat(3, 1fr);
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
.si-stat--green::before { background: #22c55e; }
.si-stat--blue::before  { background: #3b82f6; }
.si-stat--orange::before{ background: #f97316; }

.si-stat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.si-stat--green .si-stat-icon { background: #dcfce7; color: #16a34a; }
.si-stat--blue  .si-stat-icon { background: #dbeafe; color: #2563eb; }
.si-stat--orange .si-stat-icon { background: #fff7ed; color: #f97316; }

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
/* Amount column styling */
.tc-amount {
    width: 150px;
    min-width: 150px;
}
.tc-wh { width: 110px; }
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
.amount-display {
    display: flex;
    flex-direction: column;
    line-height: 1.5;
    font-size: 11px;
}

.amount-main {
    font-weight: 700;
    color: var(--c-text);
    font-size: 12px;
}

.amount-partial {
    color: #b45309;
    font-size: 10px;
    font-weight: 600;
}

.amount-balance {
    color: #dc2626;
    font-size: 10px;
    font-weight: 600;
}

.amount-unpaid {
    color: #b91c1c;
    font-size: 10px;
    font-weight: 600;
    font-style: italic;
}

.amount-paid {
    color: #047857;
    font-size: 10px;
    font-weight: 600;
    font-style: italic;
}
.si-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-end;
}
.si-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 120px;
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
.si-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 950px; /* Reduced from 1100px */
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
    min-width: 1100px; /* Increased for new column */
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
/* Column widths (updated - removed grand total, paid, balance) */
.tc-no        { width: 45px;  text-align: center; }
.tc-date      { width: 88px;  }
.tc-inv       { width: 160px; }
.tc-type      { width: 82px;  }
.tc-party     { width: 170px; }
.tc-due       { width: 80px;  }
.tc-amount    { width: 150px; }
.tc-payment-status { width: 95px; }
.tc-inv-status { width: 95px; }
.tc-act       { width: 90px;  }

/* Table cell helpers */
.td-serial   { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--c-bg); border-radius: 4px; font-size: 10px; color: var(--c-muted); font-weight: 600; }
.td-muted    { color: var(--c-muted); font-size: 10.5px; }
.td-bold     { font-weight: 700; color: var(--c-text); font-size: 12px; }
.td-green    { color: #16a34a; font-weight: 600; }
.td-red      { color: #dc2626; font-weight: 600; }
.td-date-main{ font-size: 11.5px; color: var(--c-text2); white-space: nowrap; font-weight: 500; }
.td-date-sub { font-size: 9.5px; color: var(--c-muted); }

/* Invoice chip */
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

/* Invoice type badge */
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

/* Due badge */
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
.si-due--paid  { background: #f1f5f9; color: var(--c-muted); }

/* Status badges */
.si-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    width: fit-content;
}
.si-badge--paid    { background: #d1fae5; color: #065f46; }
.si-badge--unpaid  { background: #fee2e2; color: #991b1b; }
.si-badge--partial { background: #fef3c7; color: #92400e; }

.si-inv-status {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    width: fit-content;
}
.inv-draft     { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.inv-confirmed { background: #dbeafe; color: #1d4ed8; }

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
    .si-stats { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
    .si-stats { grid-template-columns: 1fr 1fr; }
    .si-stats .si-stat:last-child { grid-column: span 2; }
    .si-filter-group { min-width: calc(50% - 4px); flex: 1 1 calc(50% - 4px); }
    .si-filter-btns { width: 100%; }
}
@media (max-width: 480px) {
    .si-wrap { padding: 10px; }
    .si-stats { grid-template-columns: 1fr; }
    .si-stats .si-stat:last-child { grid-column: span 1; }
    .si-header { flex-direction: column; align-items: flex-start; }
    .si-filter-group { min-width: 100%; flex: 1 1 100%; }
    .si-pagination { flex-direction: column; align-items: flex-start; }
    .si-pages { justify-content: center; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
// ── Period filter toggle
function handlePeriodChange(sel) {
    const v = sel.value;
    document.getElementById('grpSingleDate').style.display = (!v)          ? '' : 'none';
    document.getElementById('grpDateFrom').style.display   = (v==='custom') ? '' : 'none';
    document.getElementById('grpDateTo').style.display     = (v==='custom') ? '' : 'none';
}

// ── Delete
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

document.getElementById('confirmDel').addEventListener('click', function () {
    if (!delId) return;
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Deleting…';

    fetch('/admin/sales/' + delId, {
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
            showAlert('Invoice deleted successfully', 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showAlert(data.message || 'Failed to delete', 'error');
            btn.disabled = false;
            btn.textContent = 'Delete Invoice';
        }
    })
    .catch(() => {
        closeDelModal();
        showAlert('Something went wrong', 'error');
        btn.disabled = false;
        btn.textContent = 'Delete Invoice';
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
