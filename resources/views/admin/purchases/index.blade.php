@extends('layouts.admin')

@section('title', 'Purchase Invoices')
@section('header-title', 'Purchase Orders')

@section('content')
<div class="pi-wrap">
    <div id="alertBox"></div>

    <!-- HEADER -->
    <div class="pi-header">
        <div class="pi-header-left">
            <div class="pi-header-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </div>
            <div>
                <h1 class="pi-title">Purchase Invoices</h1>
                <p class="pi-sub">Manage and track all purchase transactions</p>
            </div>
        </div>
        <a href="{{ route('admin.purchases.create') }}" class="pi-btn-create">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New Purchase
        </a>
    </div>

    <!-- STATS CARDS -->
    <div class="pi-stats">
        @php
            $totalPurchaseAmount = $invoices->where('status', '!=', 'draft')->sum('grand_total');
            $totalPaidAmount     = $invoices->where('status', '!=', 'draft')->sum('total_paid');
            $totalPendingAmount  = $totalPurchaseAmount - $totalPaidAmount;
            $draftCount          = $invoices->where('status', 'draft')->count();
        @endphp

        <div class="pi-stat pi-stat--purple">
            <div class="pi-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 7v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10"/>
                    <polygon points="14 2 18 6 14 10 10 10 10 6 14 2"/>
                </svg>
            </div>
            <div class="pi-stat-content">
                <div class="pi-stat-label">Total Purchases</div>
                <div class="pi-stat-value">₹ {{ number_format($totalPurchaseAmount, 2) }}</div>
                <div class="pi-stat-hint">All confirmed invoices</div>
            </div>
        </div>

        <div class="pi-stat pi-stat--green">
            <div class="pi-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="pi-stat-content">
                <div class="pi-stat-label">Paid</div>
                <div class="pi-stat-value">₹ {{ number_format($totalPaidAmount, 2) }}</div>
                <div class="pi-stat-hint">Amount paid to vendors</div>
            </div>
        </div>

        <div class="pi-stat pi-stat--orange">
            <div class="pi-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="pi-stat-content">
                <div class="pi-stat-label">Pending</div>
                <div class="pi-stat-value">₹ {{ number_format($totalPendingAmount, 2) }}</div>
                <div class="pi-stat-hint">Balance to be paid</div>
            </div>
        </div>

        <div class="pi-stat pi-stat--blue">
            <div class="pi-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
            <div class="pi-stat-content">
                <div class="pi-stat-label">Drafts</div>
                <div class="pi-stat-value">{{ $draftCount }}</div>
                <div class="pi-stat-hint">Pending confirmation</div>
            </div>
        </div>
    </div>

    <!-- FILTERS -->
    <div class="pi-filters">
        <div class="pi-filters-header">
            <div class="pi-filters-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Filters
            </div>
            @if(request()->anyFilled(['invoice_number','party_id','payment_status','status','period','date_from','date_to','invoice_type','warehouse_id']))
            <a href="{{ route('admin.purchases.index') }}" class="pi-clear-filters">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Clear all filters
            </a>
            @endif
        </div>

        <form method="GET" action="{{ route('admin.purchases.index') }}" id="filterForm">
            <div class="pi-filter-row">
                <!-- Period Filter -->
                <div class="pi-filter-group">
                    <label class="pi-filter-label">Period</label>
                    <select name="period" class="pi-select" id="periodSelect" onchange="handlePeriodChange(this)">
                        <option value="">All Time</option>
                        <option value="today"  {{ request('period') == 'today'  ? 'selected' : '' }}>Today</option>
                        <option value="7"      {{ request('period') == '7'      ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30"     {{ request('period') == '30'     ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90"     {{ request('period') == '90'     ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="365"    {{ request('period') == '365'    ? 'selected' : '' }}>Last 365 Days</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                <!-- Single Date -->
                <div class="pi-filter-group" id="grpSingleDate" style="{{ request()->filled('period') ? 'display:none' : '' }}">
                    <label class="pi-filter-label">Date</label>
                    <input type="date" name="date" class="pi-input" value="{{ request('date') }}">
                </div>

                <!-- Custom Range -->
                <div class="pi-filter-group" id="grpDateFrom" style="{{ request('period') == 'custom' ? '' : 'display:none' }}">
                    <label class="pi-filter-label">From</label>
                    <input type="date" name="date_from" class="pi-input" value="{{ request('date_from') }}">
                </div>
                <div class="pi-filter-group" id="grpDateTo" style="{{ request('period') == 'custom' ? '' : 'display:none' }}">
                    <label class="pi-filter-label">To</label>
                    <input type="date" name="date_to" class="pi-input" value="{{ request('date_to') }}">
                </div>

                <!-- Invoice Number -->
                <div class="pi-filter-group">
                    <label class="pi-filter-label">Invoice No.</label>
                    <div class="pi-input-icon-wrap">
                        <svg class="pi-input-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" name="invoice_number" class="pi-input pi-input-with-icon"
                               placeholder="Search..." value="{{ request('invoice_number') }}">
                    </div>
                </div>

                <!-- Invoice Type -->
                <div class="pi-filter-group">
                    <label class="pi-filter-label">Type</label>
                    <select name="invoice_type" class="pi-select">
                        <option value="">All</option>
                        <option value="gst"  {{ request('invoice_type') == 'gst'  ? 'selected' : '' }}>GST</option>
                        <option value="cash" {{ request('invoice_type') == 'cash' ? 'selected' : '' }}>Cash Memo</option>
                    </select>
                </div>

                <!-- Party -->
                <div class="pi-filter-group">
                    <label class="pi-filter-label">Party</label>
                    <select name="party_id" class="pi-select">
                        <option value="">All Parties</option>
                        @foreach($allParties as $party)
                            <option value="{{ $party['id'] }}" {{ request('party_id') == $party['id'] ? 'selected' : '' }}>
                                {{ $party['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Warehouse -->
                <div class="pi-filter-group">
                    <label class="pi-filter-label">Warehouse</label>
                    <select name="warehouse_id" class="pi-select">
                        <option value="">All</option>
                        @foreach(\App\Models\Warehouse::active()->get() as $wh)
                            <option value="{{ $wh->_id }}" {{ request('warehouse_id') == $wh->_id ? 'selected' : '' }}>
                                {{ $wh->name }}{{ $wh->is_main ? ' (Main)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Status -->
                <div class="pi-filter-group">
                    <label class="pi-filter-label">Payment</label>
                    <select name="payment_status" class="pi-select">
                        <option value="">All</option>
                        <option value="paid"    {{ request('payment_status') == 'paid'    ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid"  {{ request('payment_status') == 'unpaid'  ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>

                <!-- Invoice Status -->
                <div class="pi-filter-group">
                    <label class="pi-filter-label">Status</label>
                    <select name="status" class="pi-select">
                        <option value="">All</option>
                        <option value="draft"              {{ request('status') == 'draft'              ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed"          {{ request('status') == 'confirmed'          ? 'selected' : '' }}>Confirmed</option>
                        <option value="partially_returned" {{ request('status') == 'partially_returned' ? 'selected' : '' }}>Partially Returned</option>
                        <option value="returned"           {{ request('status') == 'returned'           ? 'selected' : '' }}>Returned</option>
                        <option value="cancelled"          {{ request('status') == 'cancelled'          ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="pi-filter-btns">
                    <button type="submit" class="pi-btn-filter">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Apply
                    </button>
                    <a href="{{ route('admin.purchases.index') }}" class="pi-btn-reset">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- TABLE CARD -->
    <div class="pi-table-card">
        <div class="pi-table-topbar">
            <div class="pi-table-count">
                <strong>{{ $invoices->total() }}</strong> invoice{{ $invoices->total() != 1 ? 's' : '' }}
                @if(request()->anyFilled(['invoice_number','party_id','payment_status','status','period','date_from','date_to','invoice_type','warehouse_id']))
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
                    Showing {{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }}
                </div>
            </div>
        </div>

        <div class="pi-table-wrap">
            <table class="pi-table">
                <thead>
                    <tr>
                        <th class="tc-no hide-mob">S No.</th>
                        <th class="tc-date">Date</th>
                        <th class="tc-inv">Invoice No.</th>
                        <th class="tc-type">Type</th>
                        <th class="tc-vendor">Party</th>
                        <th class="tc-pe">Purchase Exec.</th>
                        <th class="tc-wh">Warehouse</th>
                        <th class="tc-amount">Amount</th>
                        <th class="tc-payment">Payment</th>
                        <th class="tc-status">Status</th>
                        <th class="tc-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $index => $invoice)
                    <tr class="pi-tr" data-id="{{ $invoice->id }}">

                        {{-- S.No. --}}
                        <td class="tc-no hide-mob">
                            <span class="td-serial">{{ ($invoices->currentPage() - 1) * $invoices->perPage() + $index + 1 }}</span>
                        </td>

                        {{-- Date --}}
                        <td class="tc-date">
                            <div class="td-date-main">
                                <svg class="mob-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}
                            </div>
                            <div class="td-date-sub hide-mob">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('D') }}</div>
                        </td>

                        {{-- Invoice No. --}}
                        <td class="tc-inv">
                            <span class="pi-inv-chip">{{ $invoice->invoice_number }}</span>
                        </td>

                        {{-- Type --}}
                        <td class="tc-type">
                            @if($invoice->invoice_type === 'gst')
                                <span class="pi-type-badge pi-type--gst">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                                    </svg>
                                    GST
                                </span>
                            @else
                                <span class="pi-type-badge pi-type--cash">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <rect x="2" y="7" width="20" height="14" rx="2"/>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                    </svg>
                                    Cash
                                </span>
                            @endif
                        </td>

                        {{-- Party --}}
                        <td class="tc-vendor">
                            @php
                                $partyName = $invoice->party_name ?? 'N/A';
                                $partyType = ucfirst($invoice->party_type ?? '');
                            @endphp
                            <div class="td-vendor-name" title="{{ $partyName }}">
                                <svg class="mob-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                {{ $partyName }}
                            </div>
                            @if($partyType)
                                <span class="td-vendor-contact hide-mob">{{ $partyType }}</span>
                            @endif
                        </td>

                        {{-- Purchase Executive --}}
                        <td class="tc-pe">
                            @if($invoice->purchaseExecutive)
                                <span class="pe-badge">{{ $invoice->purchaseExecutive->name }}</span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>

                        {{-- Warehouse --}}
                        <td class="tc-wh">
                            <span class="pi-wh-badge">
                                {{ optional($invoice->warehouse)->name ?? '—' }}
                            </span>
                        </td>

                        {{-- Amount Column --}}
                        <td class="tc-amount">
                            @php
                                $grandTotal        = (float) ($invoice->grand_total ?? 0);
                                $totalPaid         = (float) ($invoice->total_paid ?? 0);
                                $balanceAmt        = (float) ($invoice->balance_amount ?? 0);
                                $debitNoteApplied  = (float) ($invoice->debit_note_applied ?? 0);
                                $debitNoteNumbers  = $invoice->debit_note_numbers ?? '';
                                $cashPaid          = max(0, $totalPaid - $debitNoteApplied);
                                $partyIdStr        = (string) $invoice->party_id;
                                $debitAvail        = $debitNoteMap[$partyIdStr] ?? 0;
                            @endphp

                            <div class="amount-display">
                                {{-- Grand Total (always show) --}}
                                <span class="amount-main">₹ {{ number_format($grandTotal, 2) }}</span>

                                @if($invoice->payment_status === 'paid')
                                    {{-- ✅ FULLY PAID --}}
                                    @if($debitNoteApplied > 0 && $cashPaid > 0)
                                        {{-- Both DN + Cash --}}
                                        <span class="amt-row">
                                            <span class="amt-label">DN Adj.</span>
                                            <span class="amt-val amt-yellow">- ₹{{ number_format($debitNoteApplied, 2) }}</span>
                                        </span>
                                        <span class="amt-row">
                                            <span class="amt-label">Cash</span>
                                            <span class="amt-val amt-green">₹{{ number_format($cashPaid, 2) }}</span>
                                        </span>
                                    @elseif($debitNoteApplied > 0)
                                        {{-- Only DN --}}
                                        <span class="amt-row">
                                            <span class="amt-label">DN Adj.</span>
                                            <span class="amt-val amt-yellow">- ₹{{ number_format($debitNoteApplied, 2) }}</span>
                                        </span>
                                    @endif
                                    <span class="amt-paid-tag">✓ Fully Paid</span>

                                @elseif($invoice->payment_status === 'cancelled')
                                    {{-- 🔁 FULLY RETURNED via debit note --}}
                                    @if($debitNoteApplied > 0)
                                        <span class="amt-row">
                                            <span class="amt-label">DN Adj.</span>
                                            <span class="amt-val amt-yellow">- ₹{{ number_format($debitNoteApplied, 2) }}</span>
                                        </span>
                                    @endif
                                    <span class="amt-cancelled-tag">↩ Returned</span>

                                @elseif($invoice->payment_status === 'partial')
                                    {{-- 🔶 PARTIAL --}}
                                    @if($debitNoteApplied > 0)
                                        <span class="amt-row">
                                            <span class="amt-label">DN Adj.</span>
                                            <span class="amt-val amt-yellow">- ₹{{ number_format($debitNoteApplied, 2) }}</span>
                                        </span>
                                    @endif
                                    @if($cashPaid > 0)
                                        <span class="amt-row">
                                            <span class="amt-label">Cash</span>
                                            <span class="amt-val amt-green">₹{{ number_format($cashPaid, 2) }}</span>
                                        </span>
                                    @endif
                                    @if($debitAvail > 0)
                                        <span class="amt-row">
                                            <span class="amt-label">DN Avail.</span>
                                            <span class="amt-val amt-purple">₹{{ number_format($debitAvail, 2) }}</span>
                                        </span>
                                    @endif
                                    <span class="amt-row amt-due-row">
                                        <span class="amt-label">Due</span>
                                        <span class="amt-val amt-red">₹ {{ number_format($balanceAmt, 2) }}</span>
                                    </span>

                                @else
                                    {{-- 🔴 UNPAID --}}
                                    @if($debitNoteApplied > 0)
                                        <span class="amt-row">
                                            <span class="amt-label">DN Adj.</span>
                                            <span class="amt-val amt-yellow">- ₹{{ number_format($debitNoteApplied, 2) }}</span>
                                        </span>
                                    @endif
                                    @if($debitAvail > 0)
                                        <span class="amt-row">
                                            <span class="amt-label">DN Avail.</span>
                                            <span class="amt-val amt-purple">₹{{ number_format($debitAvail, 2) }}</span>
                                        </span>
                                    @endif
                                    <span class="amt-row amt-due-row">
                                        <span class="amt-label">Due</span>
                                        <span class="amt-val amt-red">₹ {{ number_format($balanceAmt, 2) }}</span>
                                    </span>
                                @endif

                                {{-- Debit Note Applied Badge (as fallback) --}}
                                @if($debitNoteApplied > 0 && $invoice->payment_status !== 'paid' && $invoice->payment_status !== 'partial')
                                    <span class="amt-dn-badge" title="{{ $debitNoteNumbers ? 'Applied: ' . $debitNoteNumbers : 'Debit note adjusted' }}">
                                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        DN ₹{{ number_format($debitNoteApplied, 2) }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Payment Status --}}
                        <td class="tc-payment">
                            <span class="pi-badge pi-badge--{{ $invoice->payment_status }}">
                                {{ ucfirst($invoice->payment_status) }}
                            </span>
                        </td>

                        {{-- Invoice Status --}}
                        <td class="tc-status">
                            @php
                                $statusMap = [
                                    'draft'              => ['cls' => 'status-draft',         'label' => 'Draft'],
                                    'confirmed'          => ['cls' => 'status-confirmed',      'label' => 'Confirmed'],
                                    'partially_returned' => ['cls' => 'status-partial-return', 'label' => 'Partial Return'],
                                    'returned'           => ['cls' => 'status-returned',       'label' => 'Returned'],
                                    'cancelled'          => ['cls' => 'status-cancelled',      'label' => 'Cancelled'],
                                ];
                                $sm = $statusMap[$invoice->status] ?? $statusMap['draft'];
                            @endphp
                            <span class="pi-inv-status {{ $sm['cls'] }}">{{ $sm['label'] }}</span>
                        </td>

                        {{-- Actions --}}
                        <td class="tc-actions">
                            <div class="pi-act-grp">
                                <a href="{{ route('admin.purchases.show', $invoice->id) }}"
                                   class="pi-act pi-act--view" title="View Invoice">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>

                                @if($invoice->status === 'draft')
                                    <a href="{{ route('admin.purchases.edit', $invoice->id) }}"
                                       class="pi-act pi-act--edit" title="Edit Invoice">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>

                                    <button type="button" class="pi-act pi-act--generate generate-btn"
                                            data-id="{{ $invoice->id }}" title="Generate Invoice">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14M12 5l7 7-7 7"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="pi-act pi-act--del del-btn"
                                            data-id="{{ $invoice->id }}" title="Delete Invoice">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                    </button>
                                @else
                                    @if(auth()->guard('admin')->check())
                                        <a href="{{ route('admin.purchases.edit', $invoice->id) }}"
                                           class="pi-act pi-act--edit" title="Edit Invoice">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                            </svg>
                                        </a>
                                    @endif
                                @endif

                                @if($invoice->status === 'confirmed' && $invoice->payment_status === 'unpaid')
                                    <button type="button" class="pi-act pi-act--cancel cancel-btn"
                                            data-id="{{ $invoice->id }}" title="Cancel Invoice">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="pi-empty-cell">
                            <div class="pi-empty">
                                <div class="pi-empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                    </svg>
                                </div>
                                <p class="pi-empty-title">No purchase invoices found</p>
                                <p class="pi-empty-sub">
                                    @if(request()->anyFilled(['invoice_number','party_id','payment_status','status','period','date_from','date_to','invoice_type','warehouse_id']))
                                        Try adjusting your filters or <a href="{{ route('admin.purchases.index') }}">clear all</a>
                                    @else
                                        Start by creating your first purchase invoice
                                    @endif
                                </p>
                                <a href="{{ route('admin.purchases.create') }}" class="pi-btn-create" style="margin-top:4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    Create Purchase
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($invoices->hasPages())
        <div class="pi-pagination">
            <div class="pi-page-info">
                Page <strong>{{ $invoices->currentPage() }}</strong> of <strong>{{ $invoices->lastPage() }}</strong>
                &nbsp;·&nbsp; {{ $invoices->total() }} total records
            </div>
            <div class="pi-pages">
                @if($invoices->onFirstPage())
                    <span class="pi-pg pi-pg--dis">«</span>
                    <span class="pi-pg pi-pg--dis">‹</span>
                @else
                    <a href="{{ $invoices->url(1) }}" class="pi-pg" title="First">«</a>
                    <a href="{{ $invoices->previousPageUrl() }}" class="pi-pg" title="Previous">‹</a>
                @endif

                @php
                    $cur   = $invoices->currentPage();
                    $last  = $invoices->lastPage();
                    $start = max(1, $cur - 2);
                    $end   = min($last, $cur + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $invoices->url(1) }}" class="pi-pg">1</a>
                    @if($start > 2) <span class="pi-pg-dots">…</span> @endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $cur)
                        <span class="pi-pg pi-pg--active">{{ $p }}</span>
                    @else
                        <a href="{{ $invoices->url($p) }}" class="pi-pg">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $last)
                    @if($end < $last - 1) <span class="pi-pg-dots">…</span> @endif
                    <a href="{{ $invoices->url($last) }}" class="pi-pg">{{ $last }}</a>
                @endif

                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="pi-pg" title="Next">›</a>
                    <a href="{{ $invoices->url($last) }}" class="pi-pg" title="Last">»</a>
                @else
                    <span class="pi-pg pi-pg--dis">›</span>
                    <span class="pi-pg pi-pg--dis">»</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- GENERATE MODAL -->
<div class="pi-modal" id="generateModal">
    <div class="pi-modal-overlay" onclick="closeGenerateModal()"></div>
    <div class="pi-modal-box">
        <div class="pi-modal-head">
            <div class="pi-modal-ico" style="background:#f97316;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </div>
            <div>
                <div class="pi-modal-title">Generate Purchase Invoice</div>
                <div class="pi-modal-sub">Stock will be added to warehouse</div>
            </div>
            <button class="pi-modal-close" onclick="closeGenerateModal()">×</button>
        </div>
        <div class="pi-modal-body">
            <p>Are you sure you want to generate this invoice? Stock will be added to the warehouse and cannot be undone.</p>
            <div id="generateDnInfo" style="display:none;margin-top:10px;padding:9px 12px;background:#d1fae5;border:1px solid #6ee7b7;border-radius:5px;font-size:11px;color:#065f46;line-height:1.5;"></div>
        </div>
        <div class="pi-modal-foot">
            <button class="pi-btn-cancel" onclick="closeGenerateModal()">Cancel</button>
            <button class="pi-btn-generate-confirm" id="confirmGenerate">Generate Invoice</button>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="pi-modal" id="deleteModal">
    <div class="pi-modal-overlay" onclick="closeDeleteModal()"></div>
    <div class="pi-modal-box">
        <div class="pi-modal-head">
            <div class="pi-modal-ico" style="background:#ef4444;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
            </div>
            <div>
                <div class="pi-modal-title">Delete Invoice</div>
                <div class="pi-modal-sub">This action cannot be undone</div>
            </div>
            <button class="pi-modal-close" onclick="closeDeleteModal()">×</button>
        </div>
        <div class="pi-modal-body">
            <p>Are you sure you want to permanently delete this draft invoice?</p>
        </div>
        <div class="pi-modal-foot">
            <button class="pi-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button class="pi-btn-delete-confirm" id="confirmDelete">Delete Invoice</button>
        </div>
    </div>
</div>

<!-- CANCEL MODAL -->
<div class="pi-modal" id="cancelModal">
    <div class="pi-modal-overlay" onclick="closeCancelModal()"></div>
    <div class="pi-modal-box">
        <div class="pi-modal-head">
            <div class="pi-modal-ico" style="background:#dc2626;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="18" y1="6" x2="6" y2="18"/>
                </svg>
            </div>
            <div>
                <div class="pi-modal-title">Cancel Invoice</div>
                <div class="pi-modal-sub">Stock will be removed from warehouse</div>
            </div>
            <button class="pi-modal-close" onclick="closeCancelModal()">×</button>
        </div>
        <div class="pi-modal-body">
            <p>Are you sure you want to cancel this invoice? Stock will be removed from the warehouse.</p>
        </div>
        <div class="pi-modal-foot">
            <button class="pi-btn-cancel" onclick="closeCancelModal()">Cancel</button>
            <button class="pi-btn-cancel-confirm" id="confirmCancel">Cancel Invoice</button>
        </div>
    </div>
</div>

@push('styles')
<style>
:root {
    --c-primary: #f97316;
    --c-primary-dark: #ea580c;
    --c-primary-light: #fff7ed;
    --c-text: #111827;
    --c-text2: #374151;
    --c-muted: #6b7280;
    --c-border: #e5e7eb;
    --c-bg: #f9fafb;
    --c-white: #ffffff;
    --c-shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
    --c-shadow2: 0 4px 12px rgba(0,0,0,.08);
    --r: 7px;
    --r-sm: 5px;
}
.pi-wrap { font-family:'Segoe UI',system-ui,-apple-system,sans-serif; font-size:12.5px; color:var(--c-text); padding:16px; max-width:100%; }
.pi-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:14px; border-bottom:1px solid var(--c-border); gap:12px; flex-wrap:wrap; }
.pi-header-left { display:flex; align-items:center; gap:11px; }
.pi-header-icon { width:38px; height:38px; background:var(--c-primary-light); border-radius:9px; display:flex; align-items:center; justify-content:center; color:var(--c-primary); flex-shrink:0; }
.pi-title { font-size:17px; font-weight:700; margin:0 0 2px; letter-spacing:-.3px; }
.pi-sub { font-size:11px; color:var(--c-muted); margin:0; }
.pi-btn-create { display:inline-flex; align-items:center; gap:6px; padding:8px 15px; background:var(--c-primary); color:#fff; border:none; border-radius:var(--r-sm); font-size:12px; font-weight:600; cursor:pointer; text-decoration:none; transition:background .15s,transform .1s; box-shadow:0 2px 8px rgba(249,115,22,.3); white-space:nowrap; }
.pi-btn-create:hover { background:var(--c-primary-dark); color:#fff; transform:translateY(-1px); box-shadow:0 4px 12px rgba(249,115,22,.35); }
.pi-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:14px; }
.pi-stat { display:flex; align-items:center; gap:14px; padding:14px 16px; background:var(--c-white); border:1px solid var(--c-border); border-radius:var(--r); box-shadow:var(--c-shadow); position:relative; overflow:hidden; transition:box-shadow .2s,transform .15s; }
.pi-stat:hover { box-shadow:var(--c-shadow2); transform:translateY(-1px); }
.pi-stat::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:var(--r) var(--r) 0 0; }
.pi-stat--purple::before { background:#8b5cf6; }
.pi-stat--green::before  { background:#22c55e; }
.pi-stat--orange::before { background:var(--c-primary); }
.pi-stat--blue::before   { background:#3b82f6; }
.pi-stat-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.pi-stat--purple .pi-stat-icon { background:#f3e8ff; color:#8b5cf6; }
.pi-stat--green .pi-stat-icon  { background:#dcfce7; color:#16a34a; }
.pi-stat--orange .pi-stat-icon { background:var(--c-primary-light); color:var(--c-primary); }
.pi-stat--blue .pi-stat-icon   { background:#dbeafe; color:#2563eb; }
.pi-stat-label { font-size:10.5px; color:var(--c-muted); font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin-bottom:3px; }
.pi-stat-value { font-size:17px; font-weight:800; color:var(--c-text); letter-spacing:-.4px; margin-bottom:2px; }
.pi-stat-hint  { font-size:10px; color:var(--c-muted); }
.pi-filters { background:var(--c-white); border:1px solid var(--c-border); border-radius:var(--r); padding:11px 14px 13px; margin-bottom:12px; box-shadow:var(--c-shadow); }
.pi-filters-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.pi-filters-title { font-size:11px; font-weight:700; color:var(--c-text2); display:flex; align-items:center; gap:5px; text-transform:uppercase; letter-spacing:.5px; }
.pi-clear-filters { display:inline-flex; align-items:center; gap:4px; font-size:10.5px; color:var(--c-primary); text-decoration:none; font-weight:500; }
.pi-clear-filters:hover { color:var(--c-primary-dark); text-decoration:underline; }
.pi-filter-row { display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end; }
.pi-filter-group { display:flex; flex-direction:column; gap:4px; min-width:120px; }
.pi-filter-label { font-size:9.5px; font-weight:700; color:var(--c-muted); text-transform:uppercase; letter-spacing:.5px; }
.pi-input,.pi-select { height:31px; padding:0 9px; border:1px solid var(--c-border); border-radius:var(--r-sm); font-size:11.5px; color:var(--c-text); background:var(--c-bg); transition:border-color .15s,box-shadow .15s; outline:none; }
.pi-input:focus,.pi-select:focus { border-color:var(--c-primary); background:var(--c-white); box-shadow:0 0 0 3px rgba(249,115,22,.1); }
.pi-input-icon-wrap { position:relative; }
.pi-input-icon { position:absolute; left:9px; top:50%; transform:translateY(-50%); color:var(--c-muted); pointer-events:none; }
.pi-input-with-icon { padding-left:28px; }
.pi-filter-btns { display:flex; gap:6px; align-items:flex-end; }
.pi-btn-filter { display:inline-flex; align-items:center; gap:5px; height:31px; padding:0 14px; background:var(--c-text); color:#fff; border:none; border-radius:var(--r-sm); font-size:11.5px; font-weight:600; cursor:pointer; }
.pi-btn-filter:hover { background:#1f2937; }
.pi-btn-reset { display:inline-flex; align-items:center; height:31px; padding:0 12px; background:var(--c-bg); color:var(--c-muted); border:1px solid var(--c-border); border-radius:var(--r-sm); font-size:11.5px; text-decoration:none; font-weight:500; }
.pi-btn-reset:hover { background:#f3f4f6; color:var(--c-text); }
.pi-table-card { background:var(--c-white); border:1px solid var(--c-border); border-radius:var(--r); box-shadow:var(--c-shadow); overflow:hidden; }
.pi-table-topbar { display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:var(--c-bg); border-bottom:1px solid var(--c-border); flex-wrap:wrap; gap:6px; }
.pi-table-count { font-size:11.5px; color:var(--c-muted); font-weight:500; display:flex; align-items:center; gap:6px; }
.pi-table-count strong { color:var(--c-text); font-weight:700; }
.pi-filtered-pill { display:inline-flex; align-items:center; gap:3px; padding:2px 7px; background:var(--c-primary-light); color:#9a3412; border:1px solid #fed7aa; border-radius:10px; font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.3px; }
.pi-page-info-top { font-size:10.5px; color:var(--c-muted); }
.pi-table-topbar-right { display:flex; align-items:center; gap:8px; }
.pi-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
.pi-table { width:100%; border-collapse:collapse; font-size:12px; min-width:1200px; }
.pi-table th { padding:9px 11px; background:#f3f4f6; font-size:9.5px; font-weight:700; color:var(--c-muted); text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid var(--c-border); white-space:nowrap; text-align:left; position:sticky; top:0; z-index:1; }
.pi-table td { padding:9px 11px; border-bottom:1px solid #f3f4f6; vertical-align:middle; color:var(--c-text2); }
.pi-table tr:last-child td { border-bottom:none; }
.pi-tr { transition:background .12s; }
.pi-tr:hover td { background:#fafafa; }
.tc-no { width:45px; text-align:center; }
.tc-date { width:88px; }
.tc-inv  { width:160px; }
.tc-type { width:82px; }
.tc-vendor { width:180px; }
.tc-pe   { width:120px; }
.tc-wh   { width:100px; }
.tc-amount { width:170px; min-width:170px; }
.tc-payment { width:90px; }
.tc-status  { width:90px; }
.tc-actions { width:110px; }
.td-serial { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; background:var(--c-bg); border-radius:4px; font-size:10px; color:var(--c-muted); font-weight:600; }
.td-muted { color:var(--c-muted); font-size:10.5px; }
.td-date-main { font-size:11.5px; color:var(--c-text2); white-space:nowrap; font-weight:500; }
.td-date-sub  { font-size:9.5px; color:var(--c-muted); }
.pi-inv-chip { display:inline-block; padding:3px 8px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:4px; font-size:10.5px; font-weight:600; color:#0369a1; white-space:nowrap; font-family:'Courier New',monospace; }
.pi-type-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:4px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; white-space:nowrap; }
.pi-type--gst  { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.pi-type--cash { background:#fefce8; color:#a16207; border:1px solid #fde68a; }
.td-vendor-name { font-weight:500; color:var(--c-text); font-size:12px; margin-bottom:2px; max-width:165px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.td-vendor-contact { font-size:10px; color:var(--c-muted); display:block; }
.pe-badge { display:inline-block; padding:2px 6px; background:#f3f4f6; color:#374151; border-radius:3px; font-size:10px; font-weight:500; }
.pi-wh-badge { display:inline-block; padding:2px 7px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:4px; font-size:10px; font-weight:600; color:#166534; white-space:nowrap; }
.amount-display { display:flex; flex-direction:column; gap:2px; font-size:11px; line-height:1.4; }
.amount-main { font-weight:700; color:var(--c-text); font-size:12.5px; }
.amt-paid-tag { font-size:10px; font-weight:600; color:#047857; background:#d1fae5; border-radius:3px; padding:1px 5px; width:fit-content; }
.amt-row { display:flex; justify-content:space-between; align-items:center; gap:6px; }
.amt-due-row { border-top:1px dashed #e5e7eb; padding-top:2px; margin-top:1px; }
.amt-label { color:var(--c-muted); font-size:10px; font-weight:500; min-width:45px; }
.amt-val   { font-weight:600; font-size:10.5px; text-align:right; }
.amt-green { color:#047857; }
.amt-red   { color:#dc2626; }

/* Debit Note Applied Badge */
.amt-dn-badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    margin-top: 2px;
    padding: 2px 6px;
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 700;
    cursor: default;
    width: fit-content;
}
.amt-purple       { color: #7c3aed; font-weight: 600; }
.amt-cancelled-tag {
    font-size: 10px; font-weight: 600;
    color: #6b7280; background: #f3f4f6;
    border-radius: 3px; padding: 1px 5px;
    width: fit-content;
    border: 1px solid #e5e7eb;
}
.amt-yellow       { color: #b45309; font-weight: 600; }
.pi-badge { display:inline-block; padding:3px 8px; border-radius:10px; font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.3px; width:fit-content; }
.pi-badge--paid    { background:#d1fae5; color:#065f46; }
.pi-badge--unpaid  { background:#fee2e2; color:#991b1b; }
.pi-badge--partial { background:#fef3c7; color:#92400e; }
.pi-inv-status { display:inline-block; padding:2px 6px; border-radius:3px; font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.3px; width:fit-content; }
.status-draft          { background:#f3f4f6; color:#6b7280; border:1px solid #e5e7eb; }
.status-confirmed      { background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe; }
.status-partial-return { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
.status-returned       { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
.status-cancelled      { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
.pi-act-grp { display:flex; gap:4px; align-items:center; }
.pi-act { width:28px; height:28px; border:none; border-radius:var(--r-sm); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:all .15s; text-decoration:none; flex-shrink:0; }
.pi-act--view    { background:#dbeafe; color:#2563eb; }
.pi-act--view:hover    { background:#bfdbfe; transform:scale(1.05); }
.pi-act--edit    { background:#fef3c7; color:#92400e; }
.pi-act--edit:hover    { background:#fde68a; transform:scale(1.05); }
.pi-act--generate { background:#dcfce7; color:#16a34a; }
.pi-act--generate:hover { background:#bbf7d0; transform:scale(1.05); }
.pi-act--del     { background:#fee2e2; color:#dc2626; }
.pi-act--del:hover     { background:#fecaca; transform:scale(1.05); }
.pi-act--cancel  { background:#fee2e2; color:#b91c1c; }
.pi-act--cancel:hover  { background:#fecaca; transform:scale(1.05); }
.pi-empty-cell { padding:52px 20px; text-align:center; }
.pi-empty { display:inline-flex; flex-direction:column; align-items:center; gap:8px; }
.pi-empty-icon { width:56px; height:56px; background:var(--c-bg); border-radius:14px; display:flex; align-items:center; justify-content:center; color:#d1d5db; margin-bottom:4px; }
.pi-empty-title { font-size:13px; font-weight:600; color:var(--c-text2); margin:0; }
.pi-empty-sub { font-size:11px; color:var(--c-muted); margin:0; }
.pi-empty-sub a { color:var(--c-primary); }
.pi-pagination { display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-top:1px solid var(--c-border); background:var(--c-bg); flex-wrap:wrap; gap:8px; }
.pi-page-info { font-size:10.5px; color:var(--c-muted); }
.pi-page-info strong { color:var(--c-text2); }
.pi-pages { display:flex; gap:3px; align-items:center; flex-wrap:wrap; }
.pi-pg { display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; padding:0 5px; border:1px solid var(--c-border); border-radius:var(--r-sm); font-size:11px; color:var(--c-text2); text-decoration:none; background:var(--c-white); font-weight:500; }
.pi-pg:hover { background:#f3f4f6; border-color:#d1d5db; }
.pi-pg--active { background:var(--c-primary); color:#fff; border-color:var(--c-primary); font-weight:700; }
.pi-pg--active:hover { background:var(--c-primary); }
.pi-pg--dis { color:#d1d5db; background:var(--c-bg); cursor:default; pointer-events:none; }
.pi-pg-dots { font-size:11px; color:var(--c-muted); padding:0 2px; }
.pi-modal { display:none; position:fixed; inset:0; z-index:1000; align-items:center; justify-content:center; }
.pi-modal-overlay { position:absolute; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(2px); }
.pi-modal-box { position:relative; background:var(--c-white); border-radius:10px; width:400px; max-width:92%; box-shadow:0 20px 50px rgba(0,0,0,.15); animation:modalIn .2s ease; }
@keyframes modalIn { from{opacity:0;transform:scale(.95) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
.pi-modal-head { display:flex; align-items:center; gap:11px; padding:14px 16px; border-bottom:1px solid var(--c-border); background:var(--c-bg); border-radius:10px 10px 0 0; }
.pi-modal-ico { width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.pi-modal-title { font-size:13.5px; font-weight:700; margin-bottom:1px; }
.pi-modal-sub { font-size:10.5px; color:var(--c-muted); }
.pi-modal-close { margin-left:auto; background:none; border:none; font-size:20px; color:var(--c-muted); cursor:pointer; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:5px; line-height:1; }
.pi-modal-close:hover { background:#e5e7eb; color:var(--c-text); }
.pi-modal-body { padding:16px; }
.pi-modal-body p { font-size:12.5px; color:#4b5563; line-height:1.6; margin:0; }
.pi-modal-foot { display:flex; justify-content:flex-end; gap:8px; padding:12px 16px; border-top:1px solid var(--c-border); background:#fafafa; border-radius:0 0 10px 10px; }
.pi-btn-cancel { padding:7px 16px; border:1px solid var(--c-border); border-radius:var(--r-sm); background:var(--c-bg); color:var(--c-text2); font-size:12px; font-weight:600; cursor:pointer; }
.pi-btn-cancel:hover { background:#e5e7eb; }
.pi-btn-generate-confirm { padding:7px 16px; border:none; border-radius:var(--r-sm); background:var(--c-primary); color:#fff; font-size:12px; font-weight:600; cursor:pointer; }
.pi-btn-generate-confirm:hover { background:var(--c-primary-dark); }
.pi-btn-delete-confirm { padding:7px 16px; border:none; border-radius:var(--r-sm); background:#ef4444; color:#fff; font-size:12px; font-weight:600; cursor:pointer; }
.pi-btn-delete-confirm:hover { background:#dc2626; }
.pi-btn-cancel-confirm { padding:7px 16px; border:none; border-radius:var(--r-sm); background:#dc2626; color:#fff; font-size:12px; font-weight:600; cursor:pointer; }
.pi-btn-cancel-confirm:hover { background:#b91c1c; }
.pi-btn-generate-confirm:disabled,
.pi-btn-delete-confirm:disabled,
.pi-btn-cancel-confirm:disabled { opacity:.55; cursor:not-allowed; }
#alertBox { position:fixed; top:16px; right:16px; z-index:9999; display:flex; flex-direction:column; gap:7px; }
.pi-alert { padding:10px 14px; border-radius:6px; font-size:12px; font-weight:500; box-shadow:0 4px 14px rgba(0,0,0,.12); animation:alertIn .25s ease; min-width:220px; max-width:340px; }
@keyframes alertIn { from{transform:translateX(110%);opacity:0} to{transform:translateX(0);opacity:1} }
.pi-alert-success { background:#f0fdf4; color:#166534; border-left:3px solid #22c55e; }
.pi-alert-error   { background:#fef2f2; color:#991b1b; border-left:3px solid #ef4444; }
@media (min-width: 769px) {
    .mob-icon { display: none !important; }
}

@media (max-width: 1024px) {
    .pi-stats { grid-template-columns: repeat(2,1fr); }
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
    .pi-table { min-width: 100%; display: block; border: none; }
    .pi-table thead { display: none; }
    .pi-table tbody { display: block; width: 100%; }
    
    .pi-tr {
        display: flex; flex-wrap: wrap; align-content: flex-start; align-items: center;
        background: #fff !important; border-radius: 8px;
        padding: 12px 14px 10px; margin-bottom: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;
        position: relative;
    }
    .pi-tr:hover td { background: transparent; }
    .pi-table td { border: none; padding: 0; display: flex; align-items: center; }

    /* Row 1: Vendor (Left), Amount (Right) */
    .tc-vendor { width: calc(100% - 130px); order: 1; margin-bottom: 6px; }
    .td-vendor-name { font-size: 13.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: flex; align-items: center; }
    
    .tc-amount { width: 130px; order: 2; margin-bottom: 6px; justify-content: flex-end; }
    .amount-display { align-items: flex-end; gap: 1px;}
    .amount-main { font-size: 14px; }
    .amt-row { width: 100%; justify-content: flex-end; gap: 6px; }
    .amt-label { font-size: 9px; } .amt-val { font-size: 9.5px; }
    
    /* Row 2: Invoice (Left), Date (Right) */
    .tc-inv { width: 55%; order: 3; margin-bottom: 10px; }
    .pi-inv-chip { font-size: 10px; padding: 2px 6px; }
    
    .tc-date { width: 45%; order: 4; margin-bottom: 10px; justify-content: flex-end; }
    .td-date-main { display: flex; align-items: center; font-size: 11px; color: #4b5563; font-weight: 600;}
    
    /* Row 3: Badges Collection (Left-flowing) */
    .tc-type, .tc-wh, .tc-pe, .tc-payment, .tc-status {
        width: auto !important; order: 5; margin-right: 6px; margin-bottom: 4px;
    }
    
    .pi-type-badge, .pi-wh-badge, .pe-badge, .pi-badge, .pi-inv-status {
        font-size: 8.5px; padding: 2px 5px; border-radius: 4px;
    }

    /* Row 4: Actions */
    .tc-actions { width: 100%; order: 10; margin-left: auto; margin-top: 4px; border-top: 1px dashed #e5e7eb; padding-top: 10px; justify-content: flex-end; }
    .pi-act-grp { justify-content: flex-end; gap: 5px; }
    .pi-act { width: 26px; height: 26px; border-radius: 5px; }

    .pi-table td.pi-empty-cell { width: 100%; display: block; text-align: center; }
    .pi-pagination { flex-direction: column; align-items: center; background: transparent; border: none; padding: 0; }
    .pi-pages { justify-content: center; width: 100%; margin-top: 10px; }
}
</style>
@endpush

@push('scripts')
<script>
function handlePeriodChange(sel) {
    const v = sel.value;
    document.getElementById('grpSingleDate').style.display = (!v) ? '' : 'none';
    document.getElementById('grpDateFrom').style.display   = (v === 'custom') ? '' : 'none';
    document.getElementById('grpDateTo').style.display     = (v === 'custom') ? '' : 'none';
}

// ==================== GENERATE ====================
let generateId = null;

document.querySelectorAll('.generate-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        generateId = this.dataset.id;
        document.getElementById('generateDnInfo').style.display = 'none';
        document.getElementById('generateDnInfo').textContent   = '';
        document.getElementById('generateModal').style.display  = 'flex';
    });
});

function closeGenerateModal() {
    document.getElementById('generateModal').style.display = 'none';
    generateId = null;
}

document.getElementById('confirmGenerate').addEventListener('click', function () {
    if (!generateId) return;
    const btn = this;
    btn.disabled    = true;
    btn.textContent = 'Generating...';

    fetch('/admin/purchases/' + generateId + '/generate', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        closeGenerateModal();
        if (data.success) {
            // Debit note adjustment info
            let msg = 'Invoice generated successfully!';
            if (data.debit_note_applied && data.debit_note_applied > 0) {
                msg += ' Debit note ₹' + parseFloat(data.debit_note_applied).toFixed(2) + ' adjusted';
                if (data.debit_note_numbers && data.debit_note_numbers.length) {
                    msg += ' (' + data.debit_note_numbers.join(', ') + ')';
                }
                msg += '.';
            }
            showAlert(msg, 'success');
            setTimeout(() => location.reload(), 1800);
        } else {
            showAlert(data.message || 'Failed to generate invoice', 'error');
            btn.disabled    = false;
            btn.textContent = 'Generate Invoice';
        }
    })
    .catch(() => {
        closeGenerateModal();
        showAlert('Something went wrong', 'error');
        btn.disabled    = false;
        btn.textContent = 'Generate Invoice';
    });
});

// ==================== DELETE ====================
let deleteId = null;

document.querySelectorAll('.del-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        deleteId = this.dataset.id;
        document.getElementById('deleteModal').style.display = 'flex';
    });
});

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    deleteId = null;
}

document.getElementById('confirmDelete').addEventListener('click', function () {
    if (!deleteId) return;
    const btn = this;
    btn.disabled    = true;
    btn.textContent = 'Deleting...';

    fetch('/admin/purchases/' + deleteId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        closeDeleteModal();
        if (data.success) {
            showAlert('Invoice deleted successfully', 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showAlert(data.message || 'Failed to delete', 'error');
            btn.disabled    = false;
            btn.textContent = 'Delete Invoice';
        }
    })
    .catch(() => {
        closeDeleteModal();
        showAlert('Something went wrong', 'error');
        btn.disabled    = false;
        btn.textContent = 'Delete Invoice';
    });
});

// ==================== CANCEL ====================
let cancelId = null;

document.querySelectorAll('.cancel-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        cancelId = this.dataset.id;
        document.getElementById('cancelModal').style.display = 'flex';
    });
});

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
    cancelId = null;
}

document.getElementById('confirmCancel').addEventListener('click', function () {
    if (!cancelId) return;
    const btn = this;
    btn.disabled    = true;
    btn.textContent = 'Cancelling...';

    fetch('/admin/purchases/' + cancelId + '/cancel', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        closeCancelModal();
        if (data.success) {
            showAlert('Invoice cancelled. Stock removed from warehouse.', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message || 'Failed to cancel invoice', 'error');
            btn.disabled    = false;
            btn.textContent = 'Cancel Invoice';
        }
    })
    .catch(() => {
        closeCancelModal();
        showAlert('Something went wrong', 'error');
        btn.disabled    = false;
        btn.textContent = 'Cancel Invoice';
    });
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeGenerateModal();
        closeDeleteModal();
        closeCancelModal();
    }
});

function showAlert(msg, type = 'success') {
    const box = document.getElementById('alertBox');
    const el  = document.createElement('div');
    el.className = 'pi-alert pi-alert-' + type;
    el.textContent = msg;
    box.appendChild(el);
    setTimeout(() => el.remove(), 5000);
}
</script>
@endpush
@endsection
