@extends('layouts.admin')

@section('title', 'Ledger — ' . ($partyType === 'vendor' ? $party->company_name : $party->name))
@section('header-title', 'Party Ledger')

@section('content')
<div class="ldg">

    {{-- TOP BAR --}}
    <div class="ldg-topbar">
        <div class="ldg-topbar-left">
            <a href="javascript:history.back()" class="ldg-back">&#8592; Back</a>
            <span class="ldg-divider">|</span>
            <span class="ldg-party-type">{{ ucfirst($partyType) }}</span>
            <span class="ldg-party-name">{{ $partyType === 'vendor' ? $party->company_name : $party->name }}</span>
        </div>
        <div class="ldg-topbar-right">
            <span class="ldg-status ldg-status-{{ $party->status }}">{{ ucfirst($party->status) }}</span>
        </div>
    </div>

    {{-- SUMMARY STRIP --}}
    <div class="ldg-summary">
        <div class="ldg-sum-item">
            <span class="ldg-sum-label">Opening Balance</span>
            <span class="ldg-sum-val">₹{{ number_format(abs($summary['opening_balance']), 2) }}
                <span class="ldg-drcr">{{ $summary['opening_balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
            </span>
        </div>
        <div class="ldg-sum-sep"></div>
        <div class="ldg-sum-item">
            <span class="ldg-sum-label">Total Sales</span>
            <span class="ldg-sum-val">₹{{ number_format($summary['total_sales'], 2) }}</span>
        </div>
        <div class="ldg-sum-sep"></div>
        <div class="ldg-sum-item">
            <span class="ldg-sum-label">Total Received</span>
            <span class="ldg-sum-val">₹{{ number_format($summary['total_received'], 2) }}</span>
        </div>
        <div class="ldg-sum-sep"></div>
        <div class="ldg-sum-item">
            <span class="ldg-sum-label">Overdue Amount</span>
            <span class="ldg-sum-val {{ $summary['overdue_amount'] > 0 ? 'ldg-dr' : '' }}">₹{{ number_format($summary['overdue_amount'], 2) }}</span>
        </div>
        <div class="ldg-sum-sep"></div>
        <div class="ldg-sum-item">
            <span class="ldg-sum-label">Closing Balance</span>
            <span class="ldg-sum-val ldg-bold">
                ₹{{ number_format(abs($summary['closing_balance']), 2) }}
                <span class="ldg-drcr">{{ $summary['closing_balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
            </span>
        </div>
    </div>

    {{-- TABS --}}
    <div class="ldg-tabs">
        <button class="ldg-tab active" data-tab="ledger">Ledger Statement</button>
        <button class="ldg-tab" data-tab="items">Item Wise</button>
        <button class="ldg-tab" data-tab="profile">Profile</button>
    </div>

    {{-- ══════════════════════════════════════════════
        LEDGER STATEMENT  (Tally / MyBillBook style)
    ══════════════════════════════════════════════ --}}
    <div class="ldg-panel active" id="tab-ledger">
        <div class="ldg-toolbar" style="justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;" class="ldg-filter-row">
                <div class="ldg-search">
                    <input type="text" id="ledgerSearch" placeholder="Search voucher..." class="ldg-input">
                </div>

                {{-- Date filter pills --}}
                <div class="ldg-pills" id="ledgerDateFilters">
                    <button class="ldg-pill active" data-days="all">All</button>
                    <button class="ldg-pill" data-days="7">7 Days</button>
                    <button class="ldg-pill" data-days="30">30 Days</button>
                    <button class="ldg-pill" data-days="60">60 Days</button>
                    <button class="ldg-pill" data-days="90">90 Days</button>
                    <button class="ldg-pill" data-days="180">6 Months</button>
                    <button class="ldg-pill" data-days="365">1 Year</button>
                </div>

                {{-- Custom date range --}}
                <div style="display:flex;align-items:center;gap:6px;" class="ldg-daterange-row">
                    <input type="date" id="ledgerFromDate" class="ldg-input" style="width:130px;" placeholder="From">
                    <span style="color:#9ca3af;font-size:11px;">to</span>
                    <input type="date" id="ledgerToDate" class="ldg-input" style="width:130px;" placeholder="To">
                    <button onclick="applyCustomDateFilter()" class="ldg-apply-btn">Apply</button>
                </div>
            </div>

            <div class="ldg-action-btns">
                <button class="ldg-action-btn" id="downloadPdfBtn" title="Download as PDF (uses selected date range)">
                    <span class="ldg-action-icon">⬇</span><span class="ldg-action-label">Download PDF</span>
                </button>
                <button class="ldg-action-btn" id="shareWhatsappBtn" title="Share via WhatsApp">
                    <span class="ldg-action-icon">↗</span><span class="ldg-action-label">Share</span>
                </button>
            </div>
        </div>

        {{-- Invoice Type filter — GST Invoice vs Cash Memo --}}
        @if(in_array($partyType, ['customer','dealer','distributor']))
        <div class="ldg-pills ldg-invoicetype-pills" id="ledgerInvoiceTypeFilters" style="margin-bottom:10px;">
            <span class="ldg-pill-group-label">Bill Type:</span>
            <button class="ldg-pill active" data-invoice-type="all">All</button>
            <button class="ldg-pill" data-invoice-type="gst">GST Invoice</button>
            <button class="ldg-pill" data-invoice-type="cash">Cash Memo</button>
        </div>
        @endif

        {{-- Filtered summary --}}
        <div id="ledgerFilteredSummary" style="display:none;margin-bottom:10px;padding:10px 14px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;display:flex;gap:24px;flex-wrap:wrap;">
            <span>Period: <strong id="flt_period">—</strong></span>
            <span style="color:#b91c1c;">Debit: <strong id="flt_debit">₹0.00</strong></span>
            <span style="color:#15803d;">Credit: <strong id="flt_credit">₹0.00</strong></span>
            <span>Closing Balance: <strong id="flt_balance">₹0.00</strong></span>
        </div>

        <div class="ldg-table-wrap" id="ledger-print-area">
            <div class="ldg-print-header">
                <strong>Ledger Statement</strong> &nbsp;|&nbsp;
                {{ $partyType === 'vendor' ? $party->company_name : $party->name }}
                &nbsp;({{ ucfirst($partyType) }})
            </div>

            <table class="ldg-table" id="ledgerTable">
                <thead>
                    <tr>
                        <th style="width:90px">Date</th>
                        <th style="width:160px">Voucher Type</th>
                        <th style="width:120px">Sr No.</th>
                        <th style="width:130px">Payment Mode</th>
                        <th class="ta-r" style="width:110px">Debit (₹)</th>
                        <th class="ta-r" style="width:110px">Credit (₹)</th>
                        <th class="ta-r" style="width:130px">Balance (₹)</th>
                        <th style="width:150px">Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ledger as $entry)
                    <tr class="{{ $entry['is_opening'] ? 'ldg-opening-row' : '' }} {{ $entry['is_closing'] ? 'ldg-closing-row' : '' }} {{ (!empty($entry['url']) && !$entry['is_opening'] && !$entry['is_closing']) ? 'ldg-clickable-row' : '' }}"
                        data-raw-date="{{ $entry['raw_date'] ? \Carbon\Carbon::parse($entry['raw_date'])->format('Y-m-d') : '' }}"
                        data-is-opening="{{ $entry['is_opening'] ? '1' : '0' }}"
                        data-is-closing="{{ $entry['is_closing'] ? '1' : '0' }}"
                        data-debit="{{ $entry['debit'] }}"
                        data-credit="{{ $entry['credit'] }}"
                        data-balance="{{ $entry['balance'] }}"
                        data-invoice-type="{{ $entry['invoice_type'] ?? 'all' }}"
                        @if(!empty($entry['url'])) data-url="{{ $entry['url'] }}" @endif>

                        <td class="ldg-date">
                            {{ $entry['is_opening'] ? ($entry['date'] ?? 'Opening') : ($entry['is_closing'] ? '' : $entry['date']) }}
                        </td>

                        <td>
                            <span class="ldg-vtype vt-{{ Str::slug($entry['voucher_type']) }}">
                                {{ $entry['voucher_type'] }}
                            </span>
                            <!-- @if(($entry['invoice_type'] ?? 'all') === 'gst')
                                <span class="ldg-billtype-badge bt-gst">GST</span>
                            @elseif(($entry['invoice_type'] ?? 'all') === 'cash')
                                <span class="ldg-billtype-badge bt-cash">Cash</span>
                            @endif -->
                        </td>

                        <td class="ldg-mono">{{ $entry['sr_no'] }}</td>

                        <td>
                            @if(($entry['payment_mode'] ?? '—') !== '—')
                                {{ $entry['payment_mode'] }}
                                @if(!empty($entry['reference_number']))
                                    <span class="ldg-muted">({{ $entry['reference_number'] }})</span>
                                @endif
                            @else
                                <span class="ldg-muted">—</span>
                            @endif
                        </td>

                        <td class="ta-r {{ $entry['debit'] > 0 ? 'ldg-dr fw6' : 'ldg-muted' }}">
                            {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '—' }}
                        </td>

                        <td class="ta-r {{ $entry['credit'] > 0 ? 'ldg-cr fw6' : 'ldg-muted' }}">
                            {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '—' }}
                        </td>

                        <td class="ta-r fw6 ldg-bal-cell {{ $entry['balance'] > 0 ? 'ldg-dr' : ($entry['balance'] < 0 ? 'ldg-cr' : 'ldg-muted') }}">
                            {{ number_format(abs($entry['balance']), 2) }}
                            @if(!$entry['is_opening'])
                                <span class="ldg-drcr">{{ $entry['balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
                            @endif
                        </td>

                        <td>
                            @if($entry['due_date'])
                                <div class="ldg-date">{{ $entry['due_date'] }}</div>
                                @if($entry['due_status'])
                                    <span class="ldg-st {{ Str::startsWith($entry['due_status'], 'Paid') ? 'st-paid' : (Str::startsWith($entry['due_status'], 'Partially') ? 'st-partial' : 'st-unpaid') }}">
                                        {{ $entry['due_status'] }}
                                    </span>
                                @endif
                            @else
                                <span class="ldg-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="ldg-tfoot" id="ledgerTfoot">
                        <td colspan="4" class="fw6">Total</td>
                        <td class="ta-r fw6 ldg-dr" id="tfoot_debit">
                            {{ number_format($ledger->where('is_opening', false)->where('is_closing', false)->sum('debit'), 2) }}
                        </td>
                        <td class="ta-r fw6 ldg-cr" id="tfoot_credit">
                            {{ number_format($ledger->where('is_opening', false)->where('is_closing', false)->sum('credit'), 2) }}
                        </td>
                        <td class="ta-r fw6 {{ $summary['closing_balance'] >= 0 ? 'ldg-dr' : 'ldg-cr' }}" id="tfoot_balance">
                            {{ number_format(abs($summary['closing_balance']), 2) }}
                            <span class="ldg-drcr">{{ $summary['closing_balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            {{-- MOBILE CARD LAYOUT (shown instead of table below 768px) --}}
            <div class="ldg-mobile-cards" id="ledgerMobileCards">
                @foreach($ledger as $entry)
                <div class="ldg-mcard {{ $entry['is_opening'] ? 'is-opening' : '' }} {{ $entry['is_closing'] ? 'is-closing' : '' }} {{ (!empty($entry['url']) && !$entry['is_opening'] && !$entry['is_closing']) ? 'ldg-clickable-card' : '' }}"
                     data-raw-date="{{ $entry['raw_date'] ? \Carbon\Carbon::parse($entry['raw_date'])->format('Y-m-d') : '' }}"
                     data-is-opening="{{ $entry['is_opening'] ? '1' : '0' }}"
                     data-is-closing="{{ $entry['is_closing'] ? '1' : '0' }}"
                     data-invoice-type="{{ $entry['invoice_type'] ?? 'all' }}"
                     @if(!empty($entry['url'])) data-url="{{ $entry['url'] }}" @endif>

                    <div class="ldg-mcard-top">
                        <div class="ldg-mcard-voucher">
                            <span class="ldg-vtype vt-{{ Str::slug($entry['voucher_type']) }}">{{ $entry['voucher_type'] }}</span>
                            @if(($entry['invoice_type'] ?? 'all') === 'gst')
                                <span class="ldg-billtype-badge bt-gst">GST</span>
                            @elseif(($entry['invoice_type'] ?? 'all') === 'cash')
                                <span class="ldg-billtype-badge bt-cash">Cash</span>
                            @endif
                            <span class="ldg-mcard-date">{{ $entry['is_opening'] ? ($entry['date'] ?? 'Opening') : ($entry['is_closing'] ? '' : $entry['date']) }}</span>
                            @if($entry['sr_no'] && $entry['sr_no'] !== '—')
                                <span class="ldg-mcard-sr">{{ $entry['sr_no'] }}</span>
                            @endif
                        </div>
                        <div class="ldg-mcard-balance">
                            <div class="ldg-mcard-balance-val {{ $entry['balance'] > 0 ? 'ldg-dr' : ($entry['balance'] < 0 ? 'ldg-cr' : 'ldg-muted') }}">
                                ₹{{ number_format(abs($entry['balance']), 2) }}
                                @if(!$entry['is_opening'])
                                    <span class="ldg-drcr">{{ $entry['balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($entry['debit'] > 0 || $entry['credit'] > 0 || (($entry['payment_mode'] ?? '—') !== '—'))
                    <div class="ldg-mcard-grid">
                        @if($entry['debit'] > 0)
                            <div class="ldg-mcard-row"><span class="ldg-mcard-row-label">Debit</span><span class="ldg-dr fw6">₹{{ number_format($entry['debit'], 2) }}</span></div>
                        @endif
                        @if($entry['credit'] > 0)
                            <div class="ldg-mcard-row"><span class="ldg-mcard-row-label">Credit</span><span class="ldg-cr fw6">₹{{ number_format($entry['credit'], 2) }}</span></div>
                        @endif
                        @if(($entry['payment_mode'] ?? '—') !== '—')
                            <div class="ldg-mcard-row">
                                <span class="ldg-mcard-row-label">Mode</span>
                                <span>{{ $entry['payment_mode'] }}{{ !empty($entry['reference_number']) ? ' ('.$entry['reference_number'].')' : '' }}</span>
                            </div>
                        @endif
                    </div>
                    @endif

                    @if($entry['due_date'])
                    <div class="ldg-mcard-due">
                        <span class="ldg-mcard-row-label">Due {{ $entry['due_date'] }}</span>
                        @if($entry['due_status'])
                            <span class="ldg-st {{ Str::startsWith($entry['due_status'], 'Paid') ? 'st-paid' : (Str::startsWith($entry['due_status'], 'Partially') ? 'st-partial' : 'st-unpaid') }}">
                                {{ $entry['due_status'] }}
                            </span>
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         ITEM WISE
    ══════════════════════════════════════════════ --}}
    <div class="ldg-panel" id="tab-items">
        <div class="ldg-toolbar">
            <div class="ldg-search">
                <input type="text" id="itemSearch" placeholder="Search item..." class="ldg-input">
            </div>
        </div>
        <div class="ldg-table-wrap">
            <table class="ldg-table" id="itemTable">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Item Name</th>
                        <th style="width:120px">SKU</th>
                        <th style="width:90px">HSN / SAC</th>
                        @if(in_array($partyType, ['customer','dealer','distributor']))
                        <th class="ta-r" style="width:90px">Sale Qty</th>
                        <th class="ta-r" style="width:130px">Sale Amount (₹)</th>
                        @endif
                        @if(in_array($partyType, ['vendor','dealer','distributor']))
                        <th class="ta-r" style="width:90px">Purch. Qty</th>
                        <th class="ta-r" style="width:140px">Purch. Amount (₹)</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemReport as $i => $item)
                    <tr>
                        <td class="ldg-muted ta-c">{{ $i + 1 }}</td>
                        <td class="fw6">{{ $item['product_name'] }}</td>
                        <td class="ldg-mono ldg-muted">{{ $item['sku'] }}</td>
                        <td class="ldg-mono ldg-muted">{{ $item['hsn'] }}</td>
                        @if(in_array($partyType, ['customer','dealer','distributor']))
                        <td class="ta-r">{{ number_format($item['sale_qty'], 2) }}</td>
                        <td class="ta-r fw6 ldg-dr">{{ number_format($item['sale_amount'], 2) }}</td>
                        @endif
                        @if(in_array($partyType, ['vendor','dealer','distributor']))
                        <td class="ta-r">{{ number_format($item['purchase_qty'], 2) }}</td>
                        <td class="ta-r fw6 ldg-cr">{{ number_format($item['purchase_amount'], 2) }}</td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="8" class="ldg-empty">No item data found.</td></tr>
                    @endforelse
                </tbody>
                @if($itemReport->count() > 0)
                <tfoot>
                    <tr class="ldg-tfoot">
                        <td colspan="4" class="fw6">Total</td>
                        @if(in_array($partyType, ['customer','dealer','distributor']))
                        <td class="ta-r fw6">{{ number_format($itemReport->sum('sale_qty'), 2) }}</td>
                        <td class="ta-r fw6 ldg-dr">{{ number_format($itemReport->sum('sale_amount'), 2) }}</td>
                        @endif
                        @if(in_array($partyType, ['vendor','dealer','distributor']))
                        <td class="ta-r fw6">{{ number_format($itemReport->sum('purchase_qty'), 2) }}</td>
                        <td class="ta-r fw6 ldg-cr">{{ number_format($itemReport->sum('purchase_amount'), 2) }}</td>
                        @endif
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         PROFILE
    ══════════════════════════════════════════════ --}}
    <div class="ldg-panel" id="tab-profile">
        <div class="ldg-profile-grid">

            <div class="ldg-pcard">
                <div class="ldg-pcard-title">Basic Information</div>
                <table class="ldg-pinfo">
                    <tr><td class="ldg-pk">Name</td><td class="fw6">{{ $profile['name'] }}</td></tr>
                    @if($profile['contact_person'])
                    <tr><td class="ldg-pk">Contact Person</td><td>{{ $profile['contact_person'] }}</td></tr>
                    @endif
                    <tr>
                        <td class="ldg-pk">Type</td>
                        <td><span class="ldg-pt-badge pt-{{ $partyType }}">{{ $profile['party_type'] }}</span></td>
                    </tr>
                    <tr>
                        <td class="ldg-pk">Status</td>
                        <td><span class="ldg-status ldg-status-{{ $profile['status'] }}">{{ ucfirst($profile['status']) }}</span></td>
                    </tr>
                    <tr><td class="ldg-pk">Phone</td><td>{{ $profile['phone'] ?? '—' }}</td></tr>
                    <tr><td class="ldg-pk">Email</td><td>{{ $profile['email'] ?? '—' }}</td></tr>
                    <tr><td class="ldg-pk">GST Number</td><td class="ldg-mono">{{ $profile['gst_number'] ?? '—' }}</td></tr>
                    <tr><td class="ldg-pk">PAN Number</td><td class="ldg-mono">{{ $profile['pan_number'] ?? '—' }}</td></tr>
                    @if($profile['notes'])
                    <tr><td class="ldg-pk">Notes</td><td class="ldg-muted">{{ $profile['notes'] }}</td></tr>
                    @endif
                </table>
            </div>

            <div class="ldg-pcard">
                <div class="ldg-pcard-title">Financial Details</div>
                <table class="ldg-pinfo">
                    <tr><td class="ldg-pk">Opening Balance</td><td class="fw6">₹{{ number_format($profile['opening_balance'], 2) }}</td></tr>
                    <tr>
                        <td class="ldg-pk">Credit Limit</td>
                        <td class="fw6">{{ $profile['credit_limit'] > 0 ? '₹'.number_format($profile['credit_limit'],2) : '—' }}</td>
                    </tr>
                    <tr><td class="ldg-pk">Total Sales</td><td class="fw6 ldg-dr">₹{{ number_format($summary['total_sales'], 2) }}</td></tr>
                    <tr><td class="ldg-pk">Total Received</td><td class="fw6 ldg-cr">₹{{ number_format($summary['total_received'], 2) }}</td></tr>
                    <tr><td class="ldg-pk">Overdue Amount</td><td class="fw6 {{ $summary['overdue_amount'] > 0 ? 'ldg-dr' : '' }}">₹{{ number_format($summary['overdue_amount'], 2) }}</td></tr>
                    <tr>
                        <td class="ldg-pk">Closing Balance</td>
                        <td class="fw6 {{ $summary['closing_balance'] > 0 ? 'ldg-dr' : 'ldg-cr' }}">
                            ₹{{ number_format(abs($summary['closing_balance']), 2) }}
                            <span class="ldg-drcr">{{ $summary['closing_balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
                        </td>
                    </tr>
                    @if($profile['bank_name'])
                    <tr><td colspan="2" class="ldg-pk" style="padding-top:14px;border-top:1px solid #f0f0f0">Bank Details</td></tr>
                    <tr><td class="ldg-pk">Bank Name</td><td>{{ $profile['bank_name'] }}</td></tr>
                    <tr><td class="ldg-pk">Account No.</td><td class="ldg-mono">{{ $profile['account_number'] ?? '—' }}</td></tr>
                    <tr><td class="ldg-pk">IFSC Code</td><td class="ldg-mono">{{ $profile['ifsc_code'] ?? '—' }}</td></tr>
                    @endif
                </table>
            </div>

            <div class="ldg-pcard">
                <div class="ldg-pcard-title">Addresses</div>
                <table class="ldg-pinfo">
                    <tr>
                        <td class="ldg-pk" style="vertical-align:top;padding-top:10px">Billing</td>
                        <td style="padding-top:10px">{{ $profile['billing_address'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="ldg-pk" style="vertical-align:top;padding-top:10px">Shipping</td>
                        <td style="padding-top:10px">{{ $profile['shipping_address'] ?? '—' }}</td>
                    </tr>
                </table>
            </div>

        </div>
    </div>

    {{-- WHATSAPP SHARE MODAL --}}
    <div class="ldg-modal-overlay" id="whatsappModal">
        <div class="ldg-modal">
            <div class="ldg-modal-header">
                <span>Share Ledger via WhatsApp</span>
                <button class="ldg-modal-close" id="closeWhatsappModal">&times;</button>
            </div>
            <div class="ldg-modal-body">
                <label class="ldg-modal-label">Phone Number (optional)</label>
                <input type="tel" id="whatsappNumber" class="ldg-input" style="width:100%;" placeholder="e.g. 9876543210 (leave blank to choose contact)">
                <p class="ldg-modal-hint">With a number, it opens that chat directly. Leave blank to pick any contact from WhatsApp.</p>

                <label class="ldg-modal-label" style="margin-top:12px;">Message Preview</label>
                <textarea id="whatsappPreview" class="ldg-input ldg-modal-textarea" rows="6" readonly></textarea>
            </div>
            <div class="ldg-modal-footer">
                <button class="ldg-modal-cancel" id="cancelWhatsappShare">Cancel</button>
                <button class="ldg-modal-send" id="sendWhatsappShare">Open WhatsApp</button>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
.ldg * { box-sizing: border-box; }
.ldg {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-size: 12px; color: #1f2937; line-height: 1.5;
}

/* TOP BAR */
.ldg-topbar { display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; margin-bottom:14px; border-bottom:1px solid #e5e7eb; }
.ldg-topbar-left { display:flex; align-items:center; gap:10px; }
.ldg-back { font-size:11px; color:#6b7280; text-decoration:none; font-weight:500; }
.ldg-back:hover { color:#374151; }
.ldg-divider { color:#d1d5db; }
.ldg-party-type { font-size:10px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; background:#f3f4f6; padding:2px 8px; border-radius:3px; }
.ldg-party-name { font-size:15px; font-weight:700; color:#111827; }
.ldg-status { font-size:10px; font-weight:600; padding:3px 10px; border-radius:3px; text-transform:capitalize; }
.ldg-status-active   { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.ldg-status-inactive { background:#f9fafb; color:#6b7280; border:1px solid #e5e7eb; }

/* SUMMARY */
.ldg-summary { display:flex; align-items:center; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; margin-bottom:16px; overflow:hidden; flex-wrap:wrap; }
.ldg-sum-item { flex:1; min-width:140px; padding:12px 18px; display:flex; flex-direction:column; gap:3px; }
.ldg-sum-sep  { width:1px; height:36px; background:#e5e7eb; flex-shrink:0; }
.ldg-sum-label { font-size:10px; color:#9ca3af; font-weight:500; text-transform:uppercase; letter-spacing:.4px; }
.ldg-sum-val   { font-size:13px; font-weight:700; color:#111827; }

/* TABS */
.ldg-tabs { display:flex; border-bottom:1px solid #e5e7eb; margin-bottom:14px; }
.ldg-tab { padding:8px 18px; background:none; border:none; border-bottom:2px solid transparent; margin-bottom:-1px; font-size:12px; font-weight:500; color:#6b7280; cursor:pointer; transition:color .15s,border-color .15s; }
.ldg-tab:hover { color:#374151; }
.ldg-tab.active { color:#fa8427; border-bottom-color:#fa8427; }

/* PANELS */
.ldg-panel { display:none; }
.ldg-panel.active { display:block; }

/* TOOLBAR */
.ldg-toolbar { display:flex; align-items:center; gap:8px; margin-bottom:10px; flex-wrap:wrap; }
.ldg-search { position:relative; }
.ldg-input { width:220px; padding:5px 10px; border:1px solid #d1d5db; border-radius:4px; font-size:11px; background:#fff; color:#374151; outline:none; transition:border-color .15s; }
.ldg-input:focus { border-color:#9ca3af; }
.ldg-pills { display:flex; gap:4px; flex-wrap:wrap; }
.ldg-pill { padding:4px 10px; border:1px solid #e5e7eb; border-radius:3px; background:#fff; font-size:10px; font-weight:500; color:#6b7280; cursor:pointer; transition:all .12s; }
.ldg-pill:hover { border-color:#9ca3af; color:#374151; }
.ldg-pill.active { background:#1f2937; border-color:#1f2937; color:#fff; }
.ldg-pill-group-label { font-size:10px; font-weight:600; color:#9ca3af; margin-right:4px; align-self:center; }
.ldg-invoicetype-pills { align-items:center; }

/* BILL TYPE BADGE (GST / Cash) */
.ldg-billtype-badge { display:inline-block; font-size:8px; font-weight:700; padding:1px 5px; border-radius:3px; margin-left:4px; vertical-align:middle; letter-spacing:.3px; }
.bt-gst  { background:#eff6ff; color:#1d4ed8; }
.bt-cash { background:#fff7ed; color:#c2410c; }
.ldg-print-btn { margin-left:auto; padding:5px 12px; border:1px solid #e5e7eb; border-radius:4px; background:#fff; font-size:11px; font-weight:500; color:#374151; cursor:pointer; }
.ldg-print-btn:hover { background:#f3f4f6; }

/* ACTION BUTTONS (Download PDF / Share) */
.ldg-action-btns { display:flex; gap:8px; margin-left:auto; flex-shrink:0; }
.ldg-action-btn { display:flex; align-items:center; gap:6px; padding:6px 14px; border:1px solid #e5e7eb; border-radius:5px; background:#fff; font-size:11px; font-weight:600; color:#374151; cursor:pointer; transition:all .12s; white-space:nowrap; }
.ldg-action-btn:hover { background:#f3f4f6; border-color:#d1d5db; }
.ldg-action-icon { font-size:13px; line-height:1; }
#shareWhatsappBtn { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
#shareWhatsappBtn:hover { background:#dcfce7; }
.ldg-apply-btn { padding:4px 10px; background:#1f2937; color:#fff; border:none; border-radius:4px; font-size:11px; cursor:pointer; }
.ldg-apply-btn:hover { background:#111827; }

/* WHATSAPP MODAL */
.ldg-modal-overlay { display:none; position:fixed; inset:0; background:rgba(17,24,39,.5); z-index:1000; align-items:center; justify-content:center; padding:16px; }
.ldg-modal-overlay.open { display:flex; }
.ldg-modal { background:#fff; border-radius:8px; width:100%; max-width:420px; box-shadow:0 10px 40px rgba(0,0,0,.2); }
.ldg-modal-header { display:flex; justify-content:space-between; align-items:center; padding:14px 18px; border-bottom:1px solid #e5e7eb; font-size:13px; font-weight:700; color:#111827; }
.ldg-modal-close { background:none; border:none; font-size:20px; color:#9ca3af; cursor:pointer; line-height:1; }
.ldg-modal-close:hover { color:#374151; }
.ldg-modal-body { padding:16px 18px; }
.ldg-modal-label { display:block; font-size:11px; font-weight:600; color:#6b7280; margin-bottom:5px; }
.ldg-modal-hint { font-size:10px; color:#9ca3af; margin-top:5px; }
.ldg-modal-textarea { width:100%; margin-top:5px; font-family:inherit; font-size:11px; resize:vertical; background:#f9fafb; }
.ldg-modal-footer { display:flex; justify-content:flex-end; gap:8px; padding:12px 18px; border-top:1px solid #e5e7eb; }
.ldg-modal-cancel { padding:7px 14px; background:#fff; border:1px solid #e5e7eb; border-radius:5px; font-size:12px; color:#374151; cursor:pointer; }
.ldg-modal-cancel:hover { background:#f3f4f6; }
.ldg-modal-send { padding:7px 16px; background:#15803d; border:none; border-radius:5px; font-size:12px; font-weight:600; color:#fff; cursor:pointer; }
.ldg-modal-send:hover { background:#166534; }

/* MOBILE CARD LAYOUT for ledger table */
.ldg-mobile-cards { display:none; }
.ldg-mcard { border:1px solid #e5e7eb; border-radius:8px; padding:12px 14px; margin-bottom:8px; background:#fff; }
.ldg-mcard.is-opening { background:#fffdf5; border-color:#fde68a; }
.ldg-mcard.is-closing { background:#f9fafb; border-color:#d1d5db; }
.ldg-mcard-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; gap:8px; }
.ldg-mcard-voucher { display:flex; flex-direction:column; gap:3px; }
.ldg-mcard-date { font-size:10px; color:#9ca3af; }
.ldg-mcard-balance { text-align:right; flex-shrink:0; }
.ldg-mcard-balance-val { font-size:14px; font-weight:700; white-space:nowrap; }
.ldg-mcard-grid { display:grid; grid-template-columns:1fr 1fr; gap:6px 14px; font-size:11px; }
.ldg-mcard-row { display:flex; justify-content:space-between; }
.ldg-mcard-row-label { color:#9ca3af; }
.ldg-mcard-sr { font-family:'SFMono-Regular','Consolas',monospace; font-size:10px; color:#6b7280; margin-top:2px; }
.ldg-mcard-due { margin-top:8px; padding-top:8px; border-top:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; font-size:11px; }

/* TABLE */
.ldg-table-wrap { border:1px solid #e5e7eb; border-radius:6px; overflow-x:auto; background:#fff; }
.ldg-table { width:100%; border-collapse:collapse; font-size:12px; }
.ldg-table thead th { background:#f9fafb; padding:8px 12px; text-align:left; font-weight:600; font-size:10px; color:#6b7280; border-bottom:1px solid #e5e7eb; text-transform:uppercase; letter-spacing:.4px; white-space:nowrap; }
.ldg-table tbody td { padding:8px 12px; border-bottom:1px solid #f3f4f6; vertical-align:middle; white-space:nowrap; }
.ldg-table tbody tr:last-child td { border-bottom:none; }
.ldg-table tbody tr:hover { background:#fafafa; }

/* Ledger rows */
.ldg-opening-row { background:#fffdf5 !important; }
.ldg-opening-row td { font-style:italic; color:#78716c; }
.ldg-closing-row { background:#f9fafb !important; border-top:2px solid #e5e7eb; }
.ldg-closing-row td { font-weight:700; }
.ldg-tfoot td { padding:8px 12px; background:#f9fafb; border-top:1px solid #e5e7eb; font-size:11px; }

/* VOUCHER TYPE */
.ldg-vtype { display:inline-block; font-size:10px; font-weight:500; padding:1px 6px; border-radius:3px; white-space:nowrap; }
.vt-opening-balance    { background:#fffbeb; color:#92400e; }
.vt-closing-balance     { background:#f3f4f6; color:#111827; }
.vt-sale-invoice        { background:#eff6ff; color:#1e40af; }
.vt-purchase-invoice    { background:#f5f3ff; color:#5b21b6; }
.vt-payment-in          { background:#f0fdf4; color:#166534; }
.vt-payment-out         { background:#fef2f2; color:#991b1b; }
.vt-credit-note         { background:#fffbeb; color:#92400e; }
.vt-debit-note          { background:#eef2ff; color:#3730a3; }
.vt-refund-payment      { background:#fef2f2; color:#991b1b; }
.vt-refund-received     { background:#f0fdf4; color:#166534; }

/* STATUS */
.ldg-st { display:inline-block; font-size:10px; padding:2px 7px; border-radius:3px; font-weight:500; white-space:nowrap; margin-top:2px; }
.st-paid     { background:#f0fdf4; color:#15803d; }
.st-unpaid   { background:#fef2f2; color:#b91c1c; }
.st-partial  { background:#fffbeb; color:#92400e; }

/* BALANCE */
.ldg-bal-cell { white-space:nowrap; }
.ldg-drcr { font-size:9px; font-weight:700; margin-left:3px; opacity:.65; }

/* UTILITY */
.ldg-dr    { color:#b91c1c; }
.ldg-cr    { color:#15803d; }
.ldg-muted { color:#9ca3af; }
.ldg-bold  { font-weight:700; }
.ldg-mono  { font-family:'SFMono-Regular','Consolas',monospace; font-size:11px; }
.ldg-date  { color:#6b7280; font-size:11px; }

.ta-c      { text-align:center; }
.fw6       { font-weight:600; }
.ldg-empty { padding:32px; text-align:center; color:#9ca3af; font-style:italic; }

/* PROFILE */
.ldg-profile-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
.ldg-pcard { background:#fff; border:1px solid #e5e7eb; border-radius:6px; overflow:hidden; }
.ldg-pcard-title { padding:9px 14px; background:#f9fafb; border-bottom:1px solid #e5e7eb; font-size:11px; font-weight:600; color:#374151; }
.ldg-pinfo { width:100%; border-collapse:collapse; font-size:12px; }
.ldg-pinfo td { padding:7px 14px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
.ldg-pinfo tr:last-child td { border-bottom:none; }
.ldg-pk { width:110px; font-size:10px; font-weight:500; color:#9ca3af; text-transform:uppercase; letter-spacing:.3px; white-space:nowrap; }
.ldg-pt-badge { font-size:10px; font-weight:600; padding:2px 8px; border-radius:3px; }
.pt-customer    { background:#eff6ff; color:#1e40af; }
.pt-dealer      { background:#fffbeb; color:#92400e; }
.pt-distributor { background:#f5f3ff; color:#5b21b6; }
.pt-vendor      { background:#f0fdf4; color:#15803d; }

/* PRINT */
.ldg-print-header { display:none; padding:10px 12px; font-size:12px; border-bottom:1px solid #e5e7eb; background:#f9fafb; color:#374151; }

@media (max-width:1100px) { .ldg-profile-grid { grid-template-columns:1fr 1fr; } }
@media (max-width:768px)  {
    .ldg-profile-grid { grid-template-columns:1fr; }
    .ldg-topbar { flex-direction:column; align-items:flex-start; gap:8px; }
    .ldg-tabs { overflow-x:auto; }
    .ldg-summary { flex-wrap:wrap; }
    .ldg-toolbar { flex-direction:column; align-items:stretch; }
    .ldg-filter-row { flex-direction:column; align-items:stretch; width:100%; }
    .ldg-search, .ldg-input { width:100% !important; }
    .ldg-pills { overflow-x:auto; flex-wrap:nowrap; padding-bottom:2px; }
    .ldg-daterange-row { width: 100%; }
    .ldg-daterange-row .ldg-input { width:calc(50% - 14px) !important; }
    .ldg-action-btns { width:100%; margin-left:0; }
    .ldg-action-btn { flex:1; justify-content:center; }

    /* Switch ledger table to card layout on mobile */
    .ldg-table-wrap #ledgerTable { display:none; }
    .ldg-mobile-cards { display:block; }
}
.ldg-clickable-row { cursor: pointer; transition: background 0.15s ease; }
.ldg-clickable-row:hover { background-color: #f9fafb !important; }
.ldg-clickable-card { cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease; }
.ldg-clickable-card:hover { transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
@media print {
    .ldg-tabs,.ldg-toolbar,.ldg-topbar-right,.ldg-back,.ldg-print-btn { display:none !important; }
    .ldg-panel { display:block !important; }
    .ldg-print-header { display:block !important; }
    .ldg-table-wrap { border:none; }
    .ldg-summary { background:none; border:none; }
}
</style>
@endpush

@push('scripts')
<script>
// Clickable rows and cards details navigation
document.addEventListener('click', function(e) {
    var clickableElement = e.target.closest('.ldg-clickable-row, .ldg-clickable-card');
    if (!clickableElement) return;
    
    // If the click is on an interactive element, do not trigger the row navigation
    if (e.target.closest('a, button, input, select, textarea')) {
        return;
    }
    
    var url = clickableElement.getAttribute('data-url');
    if (url) {
        window.location.href = url;
    }
});

// Tab switching
document.querySelectorAll('.ldg-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var target = this.dataset.tab;
        document.querySelectorAll('.ldg-tab').forEach(function(b)  { b.classList.remove('active'); });
        document.querySelectorAll('.ldg-panel').forEach(function(p) { p.classList.remove('active'); });
        this.classList.add('active');
        document.getElementById('tab-' + target).classList.add('active');
    });
});

// ========== LEDGER DATE + INVOICE-TYPE FILTER ==========
// Tracks the currently-applied range/type so Download PDF / Share can reuse it.
var ldgActiveFromDate   = null;
var ldgActiveToDate     = null;
var ldgActiveInvoiceType = 'all'; // 'all' | 'gst' | 'cash'

function ldgRowPassesInvoiceType(rowInvoiceType) {
    if (ldgActiveInvoiceType === 'all') return true;
    // Rows tagged 'all' (opening/closing anchors, standalone payments, mixed
    // allocations) always stay visible so the running balance never looks broken.
    return rowInvoiceType === 'all' || rowInvoiceType === ldgActiveInvoiceType;
}

function applyLedgerFilter(fromDate, toDate, labelText) {
    ldgActiveFromDate = fromDate;
    ldgActiveToDate   = toDate;
    ldgRecomputeLedgerView(labelText);
}

function applyInvoiceTypeFilter(type) {
    ldgActiveInvoiceType = type;
    var labelText = (ldgActiveFromDate || ldgActiveToDate)
        ? (document.getElementById('flt_period').textContent || null)
        : null;
    ldgRecomputeLedgerView(labelText);
}

// Single pass that applies BOTH the active date range and the active
// invoice-type filter together (AND logic), to desktop rows + mobile cards.
function ldgRecomputeLedgerView(labelText) {
    var fromDate = ldgActiveFromDate;
    var toDate   = ldgActiveToDate;
    var totalDebit = 0, totalCredit = 0, lastBalance = 0;
    var hasDateFilter = !!(fromDate || toDate);

    function rowShouldShow(isOpening, isClosing, rawDate, invoiceType) {
        if (isOpening || isClosing) return true;
        if (!ldgRowPassesInvoiceType(invoiceType)) return false;
        if (hasDateFilter) {
            var rowDate = rawDate ? new Date(rawDate) : null;
            if (rowDate) {
                if (fromDate && rowDate < fromDate) return false;
                if (toDate   && rowDate > toDate)   return false;
            }
        }
        return true;
    }

    // Desktop table rows
    document.querySelectorAll('#ledgerTable tbody tr').forEach(function(row) {
        var isOpening = row.dataset.isOpening === '1';
        var isClosing = row.dataset.isClosing === '1';
        var show = rowShouldShow(isOpening, isClosing, row.dataset.rawDate, row.dataset.invoiceType || 'all');
        row.style.display = show ? '' : 'none';

        if (show && !isOpening && !isClosing) {
            totalDebit  += parseFloat(row.dataset.debit  || 0);
            totalCredit += parseFloat(row.dataset.credit || 0);
            lastBalance  = parseFloat(row.dataset.balance || 0);
        }
    });

    // Mobile cards — mirror the same visibility (totals already computed above)
    document.querySelectorAll('#ledgerMobileCards .ldg-mcard').forEach(function(card) {
        var isOpening = card.dataset.isOpening === '1';
        var isClosing = card.dataset.isClosing === '1';
        var show = rowShouldShow(isOpening, isClosing, card.dataset.rawDate, card.dataset.invoiceType || 'all');
        card.style.display = show ? '' : 'none';
    });

    // Update tfoot
    document.getElementById('tfoot_debit').textContent  = totalDebit.toFixed(2);
    document.getElementById('tfoot_credit').textContent = totalCredit.toFixed(2);
    var balEl = document.getElementById('tfoot_balance');
    balEl.textContent = Math.abs(lastBalance).toFixed(2) + (lastBalance >= 0 ? ' Dr' : ' Cr');
    balEl.className = 'ta-r fw6 ' + (lastBalance >= 0 ? 'ldg-dr' : 'ldg-cr');

    // Show filtered summary strip whenever ANY filter (date or bill-type) is active
    var summaryEl = document.getElementById('ledgerFilteredSummary');
    var anyFilterActive = hasDateFilter || ldgActiveInvoiceType !== 'all';
    if (anyFilterActive) {
        summaryEl.style.display = 'flex';
        var periodLabel = labelText || (hasDateFilter ? 'Selected period' : 'All time');
        if (ldgActiveInvoiceType !== 'all') {
            periodLabel += ' · ' + (ldgActiveInvoiceType === 'gst' ? 'GST Invoice' : 'Cash Memo') + ' only';
        }
        document.getElementById('flt_period').textContent  = periodLabel;
        document.getElementById('flt_debit').textContent   = '₹' + totalDebit.toFixed(2);
        document.getElementById('flt_credit').textContent  = '₹' + totalCredit.toFixed(2);
        document.getElementById('flt_balance').textContent = '₹' + Math.abs(lastBalance).toFixed(2) + (lastBalance >= 0 ? ' Dr' : ' Cr');
    } else {
        summaryEl.style.display = 'none';
    }
}

// Pill click
document.querySelectorAll('#ledgerDateFilters .ldg-pill').forEach(function(pill) {
    pill.addEventListener('click', function() {
        document.querySelectorAll('#ledgerDateFilters .ldg-pill').forEach(function(p) { p.classList.remove('active'); });
        this.classList.add('active');

        var days = this.dataset.days;
        document.getElementById('ledgerFromDate').value = '';
        document.getElementById('ledgerToDate').value   = '';

        if (days === 'all') {
            applyLedgerFilter(null, null, 'All');
            return;
        }

        var toDate   = new Date();
        var fromDate = new Date();
        fromDate.setDate(fromDate.getDate() - parseInt(days));
        applyLedgerFilter(fromDate, toDate, 'Last ' + days + ' days');
    });
});

// Custom date apply
function applyCustomDateFilter() {
    document.querySelectorAll('#ledgerDateFilters .ldg-pill').forEach(function(p) { p.classList.remove('active'); });

    var fromVal = document.getElementById('ledgerFromDate').value;
    var toVal   = document.getElementById('ledgerToDate').value;

    var fromDate = fromVal ? new Date(fromVal) : null;
    var toDate   = toVal   ? new Date(toVal)   : null;

    if (toDate) toDate.setHours(23, 59, 59);

    var label = (fromVal || 'Start') + ' → ' + (toVal || 'Today');
    applyLedgerFilter(fromDate, toDate, label);
}

// Invoice type pill click (All / GST Invoice / Cash Memo)
var ledgerInvoiceTypeFiltersEl = document.getElementById('ledgerInvoiceTypeFilters');
if (ledgerInvoiceTypeFiltersEl) {
    ledgerInvoiceTypeFiltersEl.querySelectorAll('.ldg-pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            ledgerInvoiceTypeFiltersEl.querySelectorAll('.ldg-pill').forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');
            applyInvoiceTypeFilter(this.dataset.invoiceType);
        });
    });
}

// Ledger search — respects active date + invoice-type filters (won't un-hide filtered-out rows)
var ledgerSearchEl = document.getElementById('ledgerSearch');
if (ledgerSearchEl) {
    ledgerSearchEl.addEventListener('input', function() {
        var t = this.value.toLowerCase();
        document.querySelectorAll('#ledgerTable tbody tr').forEach(function(row) {
            if (row.dataset.isOpening === '1' || row.dataset.isClosing === '1') return;

            var passesType = ldgRowPassesInvoiceType(row.dataset.invoiceType || 'all');

            var passesDate = true;
            if (ldgActiveFromDate || ldgActiveToDate) {
                var rowDate = row.dataset.rawDate ? new Date(row.dataset.rawDate) : null;
                if (rowDate) {
                    if (ldgActiveFromDate && rowDate < ldgActiveFromDate) passesDate = false;
                    if (ldgActiveToDate   && rowDate > ldgActiveToDate)   passesDate = false;
                }
            }

            var matchesText = row.textContent.toLowerCase().indexOf(t) > -1;
            row.style.display = (passesType && passesDate && matchesText) ? '' : 'none';
        });
    });
}

// Generic table search
function filterTable(tableId, term) {
    var t = term.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().indexOf(t) > -1 ? '' : 'none';
    });
}

// Item search
var itemSearchEl = document.getElementById('itemSearch');
if (itemSearchEl) {
    itemSearchEl.addEventListener('input', function() { filterTable('itemTable', this.value); });
}

// ========== HELPERS: party id + print URL with active date range ==========
var ldgPartyType = @json($partyType);
var ldgPartyId   = @json($partyType === 'vendor' ? $party->id : $party->_id);
var ldgPrintBaseUrl = @json(route('admin.ledger.print', ['partyType' => $partyType, 'id' => $partyType === 'vendor' ? $party->id : $party->_id]));
var ldgPartyName = @json($partyType === 'vendor' ? $party->company_name : $party->name);

function ldgToInputDate(d) {
    // yyyy-mm-dd for query string, regardless of locale
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + day;
}

function ldgBuildPrintUrl() {
    var url = ldgPrintBaseUrl;
    var params = [];
    if (ldgActiveFromDate) params.push('from_date=' + ldgToInputDate(ldgActiveFromDate));
    if (ldgActiveToDate)   params.push('to_date=' + ldgToInputDate(ldgActiveToDate));
    if (ldgActiveInvoiceType && ldgActiveInvoiceType !== 'all') params.push('invoice_type=' + ldgActiveInvoiceType);
    if (params.length) url += (url.indexOf('?') > -1 ? '&' : '?') + params.join('&');
    return url;
}

// ========== DOWNLOAD PDF ==========
// Opens the print view (with the currently active date range, if any) in a
// new tab. From there the user picks "Save as PDF" in the browser's print
// dialog — same approach as the existing Print Ledger flow.
document.getElementById('downloadPdfBtn').addEventListener('click', function() {
    var printUrl = ldgBuildPrintUrl();
    var win = window.open(printUrl, '_blank');
    // Give the print view a moment to render before triggering the dialog —
    // many browsers need the document to finish loading first.
    if (win) {
        win.addEventListener('load', function() {
            setTimeout(function() { win.print(); }, 400);
        });
    }
});

// ========== WHATSAPP SHARE ==========
var whatsappModal = document.getElementById('whatsappModal');

function ldgOpenWhatsappModal() {
    var printUrl  = ldgBuildPrintUrl();
    var closingBalEl = document.getElementById('tfoot_balance');
    var closingText  = closingBalEl ? closingBalEl.textContent.trim() : '';

    var periodText = (ldgActiveFromDate || ldgActiveToDate)
        ? (document.getElementById('flt_period').textContent || 'Selected period')
        : 'All time';

    var message = 'Ledger Statement — ' + ldgPartyName + '\n' +
                   'Period: ' + periodText + '\n' +
                   'Closing Balance: ₹' + closingText + '\n\n' +
                   'View / download: ' + printUrl;

    document.getElementById('whatsappPreview').value = message;
    document.getElementById('whatsappNumber').value = '';
    whatsappModal.classList.add('open');
}

function ldgCloseWhatsappModal() {
    whatsappModal.classList.remove('open');
}

document.getElementById('shareWhatsappBtn').addEventListener('click', ldgOpenWhatsappModal);
document.getElementById('closeWhatsappModal').addEventListener('click', ldgCloseWhatsappModal);
document.getElementById('cancelWhatsappShare').addEventListener('click', ldgCloseWhatsappModal);
whatsappModal.addEventListener('click', function(e) {
    if (e.target === whatsappModal) ldgCloseWhatsappModal();
});

document.getElementById('sendWhatsappShare').addEventListener('click', function() {
    var number  = document.getElementById('whatsappNumber').value.replace(/[^0-9]/g, '');
    // Bare 10-digit numbers are assumed to be Indian mobile numbers and need
    // the country code for wa.me links to resolve correctly.
    if (number.length === 10) {
        number = '91' + number;
    }
    var message = document.getElementById('whatsappPreview').value;
    var encoded = encodeURIComponent(message);

    var waUrl = number
        ? 'https://wa.me/' + number + '?text=' + encoded   // direct to a specific number
        : 'https://wa.me/?text=' + encoded;                 // let user pick any contact

    window.open(waUrl, '_blank');
    ldgCloseWhatsappModal();
});
</script>
@endpush
@endsection