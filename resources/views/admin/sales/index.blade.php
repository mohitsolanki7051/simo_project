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
            @if(request()->anyFilled(['date','invoice_number','party_id','payment_status','status','period','date_from','date_to','invoice_type','warehouse_id']))
            <a href="{{ route('admin.sales.index') }}" class="si-clear-filters">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Clear all filters
            </a>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.sales.index') }}" id="filterForm">
            <div class="si-filter-row">

                <div class="si-filter-group full-width-mobile">
                    <label class="si-filter-label">Invoice Number</label>
                    <div class="si-input-icon-wrap">
                        <svg class="si-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="invoice_number" class="si-input si-input-with-icon" placeholder="Search invoice..." value="{{ request('invoice_number') }}">
                    </div>
                </div>

                <div class="si-filter-group">
                    <label class="si-filter-label">Period</label>
                    <select name="period" class="si-select" id="periodSelect" onchange="handlePeriodChange(this)">
                        <option value="">All Time</option>
                        <option value="today"  {{ request('period') == 'today'  ? 'selected' : '' }}>Today</option>
                        <option value="7"      {{ request('period') == '7'      ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30"     {{ request('period') == '30'     ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90"     {{ request('period') == '90'     ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="previous_month" {{ request('period') == 'previous_month' ? 'selected' : '' }}>Previous Month</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                <div class="si-filter-group" id="grpSingleDate" style="{{ request()->filled('period') ? 'display:none' : '' }}">
                    <label class="si-filter-label">Date</label>
                    <input type="date" name="date" class="si-input" value="{{ request('date') }}">
                </div>

                <div class="si-filter-group" id="grpDateFrom" style="{{ request('period') == 'custom' ? '' : 'display:none' }}">
                    <label class="si-filter-label">From</label>
                    <input type="date" name="date_from" class="si-input" value="{{ request('date_from') }}">
                </div>
                <div class="si-filter-group" id="grpDateTo" style="{{ request('period') == 'custom' ? '' : 'display:none' }}">
                    <label class="si-filter-label">To</label>
                    <input type="date" name="date_to" class="si-input" value="{{ request('date_to') }}">
                </div>

                <div class="si-filter-group">
                    <label class="si-filter-label">Type</label>
                    <select name="invoice_type" class="si-select">
                        <option value="">All Types</option>
                        <option value="gst"  {{ request('invoice_type') == 'gst'  ? 'selected' : '' }}>GST Invoice</option>
                        <option value="cash" {{ request('invoice_type') == 'cash' ? 'selected' : '' }}>Cash Memo</option>
                    </select>
                </div>

                <div class="si-filter-group">
                    <label class="si-filter-label">Party</label>
                    <select name="party_id" class="si-select">
                        <option value="">All Parties</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->_id }}" {{ request('party_id') == $customer->_id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="si-filter-group">
                    <label class="si-filter-label">Payment Status</label>
                    <select name="payment_status" class="si-select">
                        <option value="">All</option>
                        <option value="paid"    {{ request('payment_status') == 'paid'    ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid"  {{ request('payment_status') == 'unpaid'  ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="cancelled" {{ request('payment_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="si-filter-group">
                    <label class="si-filter-label">Invoice Status</label>
                    <select name="status" class="si-select">
                        <option value="">All</option>
                        <option value="draft"     {{ request('status') == 'draft'     ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="partially_returned" {{ request('status') == 'partially_returned' ? 'selected' : '' }}>Partial Return</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="si-filter-btns full-width-mobile">
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
                @if(request()->anyFilled(['date','invoice_number','party_id','payment_status','status','period','date_from','date_to','invoice_type','warehouse_id']))
                    <span class="si-filtered-pill">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        Filtered
                    </span>
                @endif
            </div>
            <div class="si-table-topbar-right hide-mob">
                <div class="si-page-info-top">
                    Showing {{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }}
                </div>
            </div>
        </div>

        <div class="si-table-wrap">
            <table class="si-table">
                <thead>
                    <tr>
                        <th class="tc-no hide-mob">S. No.</th>
                        <th class="tc-date">Date</th>
                        <th class="tc-inv">Invoice No.</th>
                        <th class="tc-type">Type</th>
                        <th class="tc-party">Party</th>
                        <th class="tc-due">Due</th>
                        <th class="tc-wh">Warehouse</th>
                        <th class="tc-amount">Amount</th>
                        <th class="tc-payment-status">Payment Status</th>
                        <th class="tc-inv-status">Invoice Status</th>
                        <th class="tc-act">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $i => $invoice)
                    <tr class="si-tr">
                        <td class="tc-no hide-mob">
                            <span class="td-serial">{{ ($invoices->currentPage() - 1) * $invoices->perPage() + $i + 1 }}</span>
                        </td>
                        
                        <td class="tc-date">
                            <div class="td-date-main">
                                {{-- Calendar Icon Mobile --}}
                                <svg class="mob-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}
                            </div>
                            <div class="td-date-sub hide-mob">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('D') }}</div>
                        </td>
                        
                        <td class="tc-inv">
                            <span class="si-inv-chip">{{ $invoice->invoice_number }}</span>
                        </td>
                        
                        <td class="tc-type">
                            @if($invoice->invoice_type === 'gst')
                                <span class="si-type-badge si-type--gst"><svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> GST</span>
                            @elseif($invoice->invoice_type === 'cash')
                                <span class="si-type-badge si-type--cash"><svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg> Cash</span>
                            @else
                                <span class="td-muted hide-mob">—</span>
                            @endif
                        </td>

                        <td class="tc-party">
                            @php
                                $partyName = optional($invoice->party)->name ?? 'N/A';
                                $partyType = optional($invoice->party)->party_type ?? '';
                            @endphp
                            <div class="td-party-name" title="{{ $partyName }}">
                                {{-- User Icon Mobile --}}
                                <svg class="mob-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                {{ $partyName }}
                            </div>
                            @if($partyType)
                                <span class="si-ptype si-ptype--{{ $partyType }} hide-mob">{{ ucfirst($partyType) }}</span>
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
                                <span class="td-muted hide-mob">—</span>
                            @endif
                        </td>

                        <td class="tc-wh">
                            @if(optional($invoice->warehouse)->name)
                                <span class="si-wh-badge">{{ $invoice->warehouse->name }}</span>
                            @else
                                <span class="td-muted hide-mob">—</span>
                            @endif
                        </td>

                        <td class="tc-amount">
                            @php
                                $grandTotal  = (float) $invoice->grand_total;
                                $totalPaid   = (float) ($invoice->total_paid ?? 0);
                                $balanceAmt  = (float) ($invoice->balance_amount ?? 0);
                                $cnApplied   = (float) ($invoice->credit_note_applied ?? 0);
                                $cashPaid    = max(0, $totalPaid - $cnApplied);
                                $partyIdStr  = (string) $invoice->party_id;
                                $creditAvail = $creditNoteMap[$partyIdStr] ?? 0;
                            @endphp
                            <div class="amount-display">
                                <span class="amount-main">₹ {{ number_format($grandTotal, 2) }}</span>
                                @if($invoice->payment_status === 'paid')
                                    @if($cnApplied > 0 && $cashPaid > 0)
                                        <span class="amt-row"><span class="amt-label">CN Adj.</span><span class="amt-val amt-yellow">- ₹{{ number_format($cnApplied, 2) }}</span></span>
                                        <span class="amt-row"><span class="amt-label">Cash</span><span class="amt-val amt-green">₹{{ number_format($cashPaid, 2) }}</span></span>
                                    @elseif($cnApplied > 0)
                                        <span class="amt-row"><span class="amt-label">CN Adj.</span><span class="amt-val amt-yellow">- ₹{{ number_format($cnApplied, 2) }}</span></span>
                                    @endif
                                    <span class="amt-paid-tag hide-mob">✓ Fully Paid</span>
                                @elseif($invoice->payment_status === 'cancelled')
                                    @if($cnApplied > 0)
                                        <span class="amt-row"><span class="amt-label">CN Adj.</span><span class="amt-val amt-yellow">- ₹{{ number_format($cnApplied, 2) }}</span></span>
                                    @endif
                                    <span class="amt-cancelled-tag hide-mob">↩ Returned</span>
                                @elseif($invoice->payment_status === 'partial')
                                    @if($cnApplied > 0)
                                        <span class="amt-row"><span class="amt-label">CN Adj.</span><span class="amt-val amt-yellow">- ₹{{ number_format($cnApplied, 2) }}</span></span>
                                    @endif
                                    @if($cashPaid > 0)
                                        <span class="amt-row"><span class="amt-label">Cash</span><span class="amt-val amt-green">₹{{ number_format($cashPaid, 2) }}</span></span>
                                    @endif
                                    @if($creditAvail > 0)
                                        <span class="amt-row"><span class="amt-label">CN Avail.</span><span class="amt-val amt-purple">₹{{ number_format($creditAvail, 2) }}</span></span>
                                    @endif
                                    <span class="amt-row amt-due-row"><span class="amt-label">Due</span><span class="amt-val amt-red">₹ {{ number_format($balanceAmt, 2) }}</span></span>
                                @else
                                    @if($cnApplied > 0)
                                        <span class="amt-row"><span class="amt-label">CN Adj.</span><span class="amt-val amt-yellow">- ₹{{ number_format($cnApplied, 2) }}</span></span>
                                    @endif
                                    @if($creditAvail > 0)
                                        <span class="amt-row"><span class="amt-label">CN Avail.</span><span class="amt-val amt-purple">₹{{ number_format($creditAvail, 2) }}</span></span>
                                    @endif
                                    <span class="amt-row amt-due-row"><span class="amt-label">Due</span><span class="amt-val amt-red">₹ {{ number_format($balanceAmt, 2) }}</span></span>
                                @endif
                            </div>
                        </td>

                        <td class="tc-payment-status">
                            <span class="si-badge si-badge--{{ $invoice->payment_status }}">{{ ucfirst($invoice->payment_status) }}</span>
                        </td>

                        <td class="tc-inv-status">
                            @php
                                $statusMap = [
                                    'draft' => ['cls' => 'inv-draft', 'label' => 'Draft'],
                                    'confirmed' => ['cls' => 'inv-confirmed', 'label' => 'Confirmed'],
                                    'completed' => ['cls' => 'inv-completed', 'label' => 'Completed'],
                                    'cancelled' => ['cls' => 'inv-cancelled', 'label' => 'Cancelled'],
                                    'partially_returned' => ['cls' => 'inv-partial-return', 'label' => 'Partial Return'],
                                    'returned' => ['cls' => 'inv-returned', 'label' => 'Returned']
                                ];
                                $sm = $statusMap[$invoice->status] ?? $statusMap['draft'];
                            @endphp
                            <span class="si-inv-status {{ $sm['cls'] }}">{{ $sm['label'] }}</span>
                        </td>

                        <td class="tc-act">
                            <div class="si-act-grp">
                                <a href="{{ route('admin.sales.show', $invoice->_id) }}" class="si-act si-act--view" title="View Invoice">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                @if($invoice->status === 'draft')
                                    <a href="{{ route('admin.sales.edit', $invoice->_id) }}" class="si-act si-act--edit" title="Edit Invoice">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                    </a>
                                    <button type="button" class="si-act si-act--del del-btn" data-id="{{ $invoice->_id }}" title="Delete Invoice">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                @else
                                    @if(auth()->guard('admin')->check())
                                        <a href="{{ route('admin.sales.edit', $invoice->_id) }}" class="si-act si-act--edit" title="Edit Invoice">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="si-empty-cell">
                            <div class="si-empty">
                                <div class="si-empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                </div>
                                <p class="si-empty-title">No invoices found</p>
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
            <div class="si-page-info hide-mob">
                Page <strong>{{ $invoices->currentPage() }}</strong> of <strong>{{ $invoices->lastPage() }}</strong>
                &nbsp;·&nbsp; {{ $invoices->total() }} total records
            </div>
            <div class="si-pages">
                @if($invoices->onFirstPage())
                    <span class="si-pg si-pg--dis hide-mob">«</span>
                    <span class="si-pg si-pg--dis">‹</span>
                @else
                    <a href="{{ $invoices->url(1) }}" class="si-pg hide-mob" title="First">«</a>
                    <a href="{{ $invoices->previousPageUrl() }}" class="si-pg" title="Previous">‹</a>
                @endif

                @php
                    $cur   = $invoices->currentPage();
                    $last  = $invoices->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $invoices->url(1) }}" class="si-pg hide-mob">1</a>
                    @if($start > 2) <span class="si-pg-dots hide-mob">…</span> @endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="si-pg si-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $invoices->url($p) }}" class="si-pg">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $last)
                    @if($end < $last - 1) <span class="si-pg-dots hide-mob">…</span> @endif
                    <a href="{{ $invoices->url($last) }}" class="si-pg hide-mob">{{ $last }}</a>
                @endif

                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="si-pg" title="Next">›</a>
                    <a href="{{ $invoices->url($last) }}" class="si-pg hide-mob" title="Last">»</a>
                @else
                    <span class="si-pg si-pg--dis">›</span>
                    <span class="si-pg si-pg--dis hide-mob">»</span>
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
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
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
/* ─── Desktop Base CSS ────────────────────── */
:root {
    --c-brand:   #f97316; --c-brand-d: #ea6c10; --c-brand-l: #fff7ed;
    --c-text:    #111827; --c-text2:   #374151; --c-muted:   #6b7280;
    --c-border:  #e5e7eb; --c-bg:      #f9fafb; --c-white:   #ffffff;
    --c-shadow:  0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
    --c-shadow2: 0 4px 12px rgba(0,0,0,.08);
    --r: 7px; --r-sm: 5px;
}
.si-wrap { font-family: 'Segoe UI', system-ui, sans-serif; font-size: 12.5px; color: var(--c-text); padding: 16px; max-width: 100%; }

/* Headers, Stats, Filters (Desktop Exact Match) */
.si-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid var(--c-border); gap: 12px; flex-wrap: wrap; }
.si-header-left { display: flex; align-items: center; gap: 11px; }
.si-header-icon { width: 38px; height: 38px; background: var(--c-brand-l); border-radius: 9px; display: flex; align-items: center; justify-content: center; color: var(--c-brand); flex-shrink: 0; }
.si-title { font-size: 17px; font-weight: 700; margin: 0 0 2px; letter-spacing: -.3px; }
.si-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }
.si-btn-create { display: inline-flex; align-items: center; gap: 6px; padding: 8px 15px; background: var(--c-brand); color: #fff; border: none; border-radius: var(--r-sm); font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; box-shadow: 0 2px 8px rgba(249,115,22,.3); }

.si-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 14px; }
.si-stat { display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: var(--c-white); border: 1px solid var(--c-border); border-radius: var(--r); box-shadow: var(--c-shadow); position: relative; overflow: hidden; }
.si-stat::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: var(--r) var(--r) 0 0; }
.si-stat--green::before { background: #22c55e; } .si-stat--blue::before  { background: #3b82f6; } .si-stat--orange::before{ background: #f97316; }
.si-stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.si-stat--green .si-stat-icon { background: #dcfce7; color: #16a34a; } .si-stat--blue .si-stat-icon { background: #dbeafe; color: #2563eb; } .si-stat--orange .si-stat-icon { background: #fff7ed; color: #f97316; }
.si-stat-label { font-size: 10.5px; color: var(--c-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 3px; }
.si-stat-value { font-size: 17px; font-weight: 800; color: var(--c-text); margin-bottom: 2px; }
.si-stat-hint  { font-size: 10px; color: var(--c-muted); }

.si-filters { background: var(--c-white); border: 1px solid var(--c-border); border-radius: var(--r); padding: 11px 14px 13px; margin-bottom: 12px; box-shadow: var(--c-shadow); }
.si-filters-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.si-filters-title { font-size: 11px; font-weight: 700; color: var(--c-text2); display: flex; align-items: center; gap: 5px; text-transform: uppercase; }
.si-clear-filters { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; color: var(--c-brand); text-decoration: none; font-weight: 500; }
.si-filter-row { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; }
.si-filter-group { display: flex; flex-direction: column; gap: 4px; min-width: 120px; }
.si-filter-label { font-size: 9.5px; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.si-input, .si-select { height: 31px; padding: 0 9px; border: 1px solid var(--c-border); border-radius: var(--r-sm); font-size: 11.5px; color: var(--c-text); background: var(--c-bg); outline: none; width: 100%; }
.si-input-icon-wrap { position: relative; width: 100%; }
.si-input-icon { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); color: var(--c-muted); pointer-events: none; }
.si-input-with-icon { padding-left: 28px; }
.si-filter-btns { display: flex; gap: 6px; align-items: flex-end; }
.si-btn-filter { display: inline-flex; align-items: center; gap: 5px; height: 31px; padding: 0 14px; background: var(--c-text); color: #fff; border: none; border-radius: var(--r-sm); font-size: 11.5px; font-weight: 600; cursor: pointer; }
.si-btn-reset { display: inline-flex; align-items: center; height: 31px; padding: 0 12px; background: var(--c-bg); color: var(--c-muted); border: 1px solid var(--c-border); border-radius: var(--r-sm); font-size: 11.5px; text-decoration: none; font-weight: 500; }

/* Table Elements */
.si-table-card { background: var(--c-white); border: 1px solid var(--c-border); border-radius: var(--r); box-shadow: var(--c-shadow); }
.si-table-topbar { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-bottom: 1px solid var(--c-border); background: var(--c-bg); flex-wrap: wrap; gap: 6px; }
.si-table-count { font-size: 11.5px; color: var(--c-muted); font-weight: 500; display: flex; align-items: center; gap: 6px; }
.si-table-count strong { color: var(--c-text); font-weight: 700; }
.si-filtered-pill { display: inline-flex; align-items: center; gap: 3px; padding: 2px 7px; background: #fff7ed; color: #92400e; border: 1px solid #fed7aa; border-radius: 10px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
.si-page-info-top { font-size: 10.5px; color: var(--c-muted); }

.si-table-wrap { overflow-x: auto; }
.si-table { width: 100%; border-collapse: collapse; font-size: 12px; min-width: 1100px; }
.si-table th { padding: 9px 11px; background: #f3f4f6; font-size: 9.5px; font-weight: 700; color: var(--c-muted); text-transform: uppercase; text-align: left; border-bottom: 1px solid var(--c-border); }
.si-table td { padding: 9px 11px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; color: var(--c-text2); }
.si-tr:hover td { background: #fafafa; }

.tc-no { width: 45px; text-align: center; } .tc-date { width: 88px; } .tc-inv { width: 160px; } .tc-type { width: 82px; } .tc-party { width: 170px; } .tc-due { width: 80px; } .tc-wh { width: 110px; } .tc-amount { width: 150px; } .tc-payment-status { width: 95px; } .tc-inv-status { width: 95px; } .tc-act { width: 90px; }

.td-serial { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--c-bg); border-radius: 4px; font-size: 10px; color: var(--c-muted); font-weight: 600; }
.td-muted { color: var(--c-muted); font-size: 10.5px; }
.td-date-main{ font-size: 11.5px; color: var(--c-text2); font-weight: 500; }
.td-date-sub { font-size: 9.5px; color: var(--c-muted); }
.si-inv-chip { display: inline-block; padding: 3px 8px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 4px; font-size: 10.5px; font-weight: 600; color: #0369a1; font-family: monospace; }
.td-party-name { font-weight: 500; color: var(--c-text); font-size: 12px; margin-bottom: 2px; }
.si-ptype { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8.5px; font-weight: 700; text-transform: uppercase; }
.si-ptype--customer { background: #d1fae5; color: #065f46; } .si-ptype--dealer { background: #dbeafe; color: #1e40af; }

.si-type-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
.si-type--gst  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; } .si-type--cash { background: #fefce8; color: #a16207; border: 1px solid #fde68a; }
.si-wh-badge { display: inline-block; padding: 2px 7px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; font-size: 10px; font-weight: 600; color: #166534; }
.si-due { display: inline-flex; align-items: center; padding: 3px 7px; border-radius: 4px; font-size: 9.5px; font-weight: 700; white-space: nowrap; }
.si-due--ok { background: #dcfce7; color: #166534; } .si-due--over { background: #fee2e2; color: #991b1b; } .si-due--paid { background: #f1f5f9; color: var(--c-muted); }

.amount-display { display:flex; flex-direction:column; gap:2px; font-size:11px; line-height:1.4; }
.amount-main { font-weight:700; color:var(--c-text); font-size:12.5px; }
.amt-paid-tag { font-size:10px; font-weight:600; color:#047857; background:#d1fae5; border-radius:3px; padding:1px 5px; width:fit-content; }
.amt-cancelled-tag{ font-size: 10px; font-weight: 600; color: #6b7280; background: #f3f4f6; border-radius: 3px; padding: 1px 5px; width: fit-content; border: 1px solid #e5e7eb; }
.amt-row { display:flex; justify-content:space-between; align-items:center; gap:6px; }
.amt-due-row { border-top:1px dashed #e5e7eb; padding-top:2px; margin-top:1px; }
.amt-label { color:var(--c-muted); font-size:10px; font-weight:500; min-width:38px; }
.amt-val { font-weight:600; font-size:10.5px; text-align:right; }
.amt-green { color:#047857; } .amt-red { color:#dc2626; } .amt-yellow { color:#b45309; } .amt-purple { color:#7c3aed; font-weight: 600; }

.si-badge { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; text-transform: uppercase; width: fit-content; }
.si-badge--paid { background: #d1fae5; color: #065f46; } .si-badge--unpaid { background: #fee2e2; color: #991b1b; } .si-badge--partial { background: #fef3c7; color: #92400e; }
.si-inv-status { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8.5px; font-weight: 700; text-transform: uppercase; }
.inv-draft { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; } .inv-confirmed { background: #dbeafe; color: #1d4ed8; }

.si-act-grp { display: flex; gap: 4px; align-items: center; }
.si-act { width: 28px; height: 28px; border: none; border-radius: var(--r-sm); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; }
.si-act--view { background: #dbeafe; color: #2563eb; } .si-act--edit { background: #fef3c7; color: #92400e; } .si-act--del { background: #fee2e2; color: #dc2626; }

/* Empty, Pagination & Modals */
.si-empty-cell { padding: 52px 20px; text-align: center; } .si-empty { display: inline-flex; flex-direction: column; align-items: center; gap: 8px; }
.si-empty-icon { width: 56px; height: 56px; background: var(--c-bg); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #d1d5db; margin-bottom: 4px; }
.si-empty-title { font-size: 13px; font-weight: 600; color: var(--c-text2); margin: 0; }
.si-pagination { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-top: 1px solid var(--c-border); background: var(--c-bg); flex-wrap: wrap; gap: 8px; }
.si-page-info { font-size: 10.5px; color: var(--c-muted); } .si-pages { display: flex; gap: 3px; align-items: center; flex-wrap: wrap; }
.si-pg { display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 28px; padding: 0 5px; border: 1px solid var(--c-border); border-radius: var(--r-sm); font-size: 11px; color: var(--c-text2); text-decoration: none; background: var(--c-white); font-weight: 500; }
.si-pg:hover { background: #f3f4f6; } .si-pg--active { background: var(--c-brand); color: #fff; font-weight: 700; } .si-pg--dis { color: #d1d5db; cursor: default; }

.si-modal { display: none; position: fixed; inset: 0; z-index: 1000; align-items: center; justify-content: center; }
.si-modal-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.45); backdrop-filter: blur(2px); }
.si-modal-box { position: relative; background: var(--c-white); border-radius: 10px; width: 380px; max-width: 92%; box-shadow: 0 20px 50px rgba(0,0,0,.15); }
.si-modal-head { display: flex; align-items: center; gap: 11px; padding: 14px 16px; border-bottom: 1px solid var(--c-border); background: var(--c-bg); border-radius: 10px 10px 0 0; }
.si-modal-ico { width: 34px; height: 34px; background: #ef4444; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; }
.si-modal-title { font-size: 13.5px; font-weight: 700; margin-bottom: 1px; } .si-modal-sub { font-size: 10.5px; color: var(--c-muted); }
.si-modal-close { margin-left: auto; background: none; border: none; font-size: 20px; color: var(--c-muted); cursor: pointer; }
.si-modal-body { padding: 16px; font-size: 12.5px; color: #4b5563; }
.si-modal-foot { display: flex; justify-content: flex-end; gap: 8px; padding: 12px 16px; border-top: 1px solid var(--c-border); background: #fafafa; border-radius: 0 0 10px 10px; }
.si-btn-cancel { padding: 7px 16px; border: 1px solid var(--c-border); border-radius: var(--r-sm); background: var(--c-bg); color: var(--c-text2); font-size: 12px; font-weight: 600; cursor: pointer; }
.si-btn-del-confirm { padding: 7px 16px; border: none; border-radius: var(--r-sm); background: #ef4444; color: #fff; font-size: 12px; font-weight: 600; cursor: pointer; }

#alertBox { position: fixed; top: 16px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 7px; }
.si-alert { padding: 10px 14px; border-radius: 6px; font-size: 12px; font-weight: 500; box-shadow: 0 4px 14px rgba(0,0,0,.12); }
.si-alert-success { background: #f0fdf4; color: #166534; border-left: 3px solid #22c55e; } .si-alert-error { background: #fef2f2; color: #991b1b; border-left: 3px solid #ef4444; }


/* ─── DENSE, PREMIUM MOBILE APP UI (Max-width: 768px) ──────────────── */
@media (min-width: 769px) {
    /* Hide mobile-only icons strictly on desktop */
    .mob-icon { display: none !important; }
}

@media (max-width: 1024px) {
    .si-stats { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
    .hide-mob { display: none !important; }
    
    /* Show tiny cute icons specifically added for mobile */
    .mob-icon { display: inline-block; vertical-align: middle; margin-right: 5px; width: 13px; height: 13px; color: var(--c-muted); }

    .si-wrap { padding: 8px; background: #f3f4f6; min-height: 100vh;}
    .si-header { background: #fff; padding: 12px; border-radius: 8px; margin-bottom: 12px; flex-direction: column; align-items: flex-start; }
    
    /* Stats & Filters Compression */
    .si-stats { display: flex; flex-wrap: nowrap; overflow-x: auto; scroll-snap-type: x mandatory; gap: 10px; margin-bottom: 12px; padding-bottom: 4px; -webkit-overflow-scrolling: touch; }
    .si-stat { flex: 0 0 88%; scroll-snap-align: center; padding: 10px 14px; }
    .si-stats::-webkit-scrollbar { display: none; }
    
    .si-filters { padding: 12px; margin-bottom: 12px; }
    .si-filter-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .full-width-mobile { grid-column: span 2; }
    .si-filter-group { min-width: 100%; flex: 1 1 100%; }
    .si-filter-btns { justify-content: space-between; margin-top: 4px; }

    /* Table -> Compact Dense Cards */
    .si-table-card { background: transparent; border: none; box-shadow: none; }
    .si-table-topbar { background: transparent; padding: 0 4px 6px; border: none; }
    .si-table { min-width: 100%; display: block; }
    .si-table thead { display: none; }
    .si-table tbody { display: block; width: 100%; }
    
    /* Magic CSS Flex Layout for Card */
    .si-tr {
        display: flex; flex-wrap: wrap; align-content: flex-start; align-items: center;
        background: #fff !important; border-radius: 8px;
        padding: 12px 14px 10px; margin-bottom: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;
    }
    .si-tr:hover td { background: transparent; }
    
    /* General TD reset */
    .si-table td { border: none; padding: 0; display: flex; align-items: center; }

    /* Custom Mobile Order mapping */
    /* Row 1: Party (Left), Amount (Right) */
    .tc-party { width: calc(100% - 130px); order: 1; margin-bottom: 6px; }
    .td-party-name { font-size: 13.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: flex; align-items: center; }
    
    .tc-amount { width: 130px; order: 2; margin-bottom: 6px; justify-content: flex-end; }
    .amount-display { align-items: flex-end; gap: 1px;}
    .amount-main { font-size: 14px; }
    .amt-row { width: 100%; justify-content: flex-end; gap: 6px; }
    .amt-label { font-size: 9px; } .amt-val { font-size: 9.5px; }
    
    /* Row 2: Invoice (Left), Date (Right) */
    .tc-inv { width: 55%; order: 3; margin-bottom: 10px; }
    .si-inv-chip { font-size: 10px; padding: 2px 6px; }
    
    .tc-date { width: 45%; order: 4; margin-bottom: 10px; justify-content: flex-end; }
    .td-date-main { display: flex; align-items: center; font-size: 11px; color: #4b5563; font-weight: 600;}
    
    /* Row 3: Badges Collection (Left-flowing) */
    .tc-type, .tc-wh, .tc-due, .tc-payment-status, .tc-inv-status {
        width: auto !important; order: 5; margin-right: 6px; margin-bottom: 4px;
    }
    
    /* Make badges tiny and dense */
    .si-type-badge, .si-wh-badge, .si-due, .si-badge, .si-inv-status {
        font-size: 8.5px; padding: 2px 5px; border-radius: 4px;
    }

    /* Row 4 (or inline with Row 3): Actions pushed strictly to the right edge */
    .tc-act { width: 100%; order: 10; margin-left: auto; margin-top: 4px; border-top: 1px dashed #e5e7eb; padding-top: 10px; justify-content: flex-end; }
    .si-act-grp { justify-content: flex-end; gap: 5px; }
    .si-act { width: 26px; height: 26px; border-radius: 5px; }

    /* Fixes for empty tables & pagination */
    .si-table td.si-empty-cell { width: 100%; display: block; text-align: center; }
    .si-pagination { flex-direction: column; align-items: center; background: transparent; border: none; padding: 0; }
    .si-pages { justify-content: center; width: 100%; margin-top: 10px; }
}
</style>
@endpush

@push('scripts')
<script>
function handlePeriodChange(sel) {
    const v = sel.value;
    document.getElementById('grpSingleDate').style.display = (!v)          ? '' : 'none';
    document.getElementById('grpDateFrom').style.display   = (v==='custom') ? '' : 'none';
    document.getElementById('grpDateTo').style.display     = (v==='custom') ? '' : 'none';
}

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
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        closeDelModal();
        if (data.success) { showAlert('Deleted successfully', 'success'); setTimeout(() => location.reload(), 900); } 
        else { showAlert(data.message || 'Failed to delete', 'error'); btn.disabled = false; btn.textContent = 'Delete Invoice'; }
    }).catch(() => {
        closeDelModal(); showAlert('Something went wrong', 'error'); btn.disabled = false; btn.textContent = 'Delete Invoice';
    });
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDelModal(); });

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