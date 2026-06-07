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
            <span class="ldg-sum-val">₹{{ number_format($summary['opening_balance'], 2) }}</span>
        </div>
        <div class="ldg-sum-sep"></div>
        <div class="ldg-sum-item">
            <span class="ldg-sum-label">Total Debit</span>
            <span class="ldg-sum-val ldg-dr">₹{{ number_format($summary['total_debit'], 2) }}</span>
        </div>
        <div class="ldg-sum-sep"></div>
        <div class="ldg-sum-item">
            <span class="ldg-sum-label">Total Credit</span>
            <span class="ldg-sum-val ldg-cr">₹{{ number_format($summary['total_credit'], 2) }}</span>
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
        <button class="ldg-tab active" data-tab="transactions">Transactions</button>
        <button class="ldg-tab" data-tab="ledger">Ledger Statement</button>
        <button class="ldg-tab" data-tab="items">Item Wise</button>
        <button class="ldg-tab" data-tab="profile">Profile</button>
    </div>

    {{-- ══════════════════════════════════════════════
         TAB 1 — TRANSACTIONS (flat table, grouped by invoice)
    ══════════════════════════════════════════════ --}}
    <div class="ldg-panel active" id="tab-transactions">
        <div class="ldg-toolbar">
            <div class="ldg-search">
                <input type="text" id="txnSearch" placeholder="Search..." class="ldg-input">
            </div>
            <div class="ldg-pills" id="txnFilters">
                <button class="ldg-pill active" data-type="all">All</button>
                <button class="ldg-pill" data-type="sale">Sale Invoice</button>
                <button class="ldg-pill" data-type="purchase">Purchase Invoice</button>
                <button class="ldg-pill" data-type="payment_in">Payment In</button>
                <button class="ldg-pill" data-type="payment_out">Payment Out</button>
                <button class="ldg-pill" data-type="credit_note">Credit Note</button>
                <button class="ldg-pill" data-type="debit_note">Debit Note</button>
            </div>
        </div>

        <div class="ldg-table-wrap">
            <table class="ldg-table" id="txnTable">
                <thead>
                    <tr>
                        <th style="width:100px">Date</th>
                        <th style="width:160px">Type</th>
                        <th>Number</th>
                        <th class="ta-r" style="width:130px">Amount (₹)</th>
                        <th style="width:70px">Flow</th>
                        <th style="width:90px">Status</th>
                        @if(in_array($partyType, ['customer','dealer','distributor']))
                        <th class="ta-r" style="width:110px">Balance (₹)</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                    <tr data-type="{{ $txn['type_badge'] }}"
                        class="{{ $txn['is_parent'] ?? false ? 'ldg-parent-row' : '' }}
                               {{ $txn['is_child']  ?? false ? 'ldg-child-row-tr' : '' }}">

                        <td class="ldg-date">{{ $txn['date'] }}</td>

                        <td>
                            <span class="ldg-chip chip-{{ $txn['type_badge'] }}">{{ $txn['type'] }}</span>
                        </td>

                        <td class="ldg-mono">{{ $txn['number'] }}</td>

                        <td class="ta-r fw6 {{ $txn['amount_type'] === 'debit' ? 'ldg-dr' : 'ldg-cr' }}">
                            ₹{{ number_format($txn['amount'], 2) }}
                        </td>

                        <td>
                            <span class="ldg-flow flow-{{ $txn['amount_type'] }}">
                                {{ strtoupper($txn['amount_type']) }}
                            </span>
                        </td>

                        <td>
                            <span class="ldg-st st-{{ $txn['status'] }}">{{ ucfirst($txn['status'] ?? '') }}</span>
                        </td>

                        @if(in_array($partyType, ['customer','dealer','distributor']))
                        <td class="ta-r">
                            @if(($txn['is_parent'] ?? false) && isset($txn['balance']))
                                <span class="fw6 {{ $txn['balance'] > 0 ? 'ldg-dr' : ($txn['balance'] < 0 ? 'ldg-cr' : 'ldg-muted') }}">
                                    ₹{{ number_format(abs($txn['balance']), 2) }}
                                    @if($txn['balance'] != 0)
                                        <span class="ldg-drcr">{{ $txn['balance'] > 0 ? 'Dr' : 'Cr' }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="ldg-muted">—</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="7" class="ldg-empty">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
        TAB 2 — LEDGER STATEMENT
    ══════════════════════════════════════════════ --}}
    <div class="ldg-panel" id="tab-ledger">
        <div class="ldg-toolbar" style="justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
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
                <div style="display:flex;align-items:center;gap:6px;">
                    <input type="date" id="ledgerFromDate" class="ldg-input" style="width:130px;" placeholder="From">
                    <span style="color:#9ca3af;font-size:11px;">to</span>
                    <input type="date" id="ledgerToDate" class="ldg-input" style="width:130px;" placeholder="To">
                    <button onclick="applyCustomDateFilter()" style="padding:4px 10px;background:#1f2937;color:#fff;border:none;border-radius:4px;font-size:11px;cursor:pointer;">Apply</button>
                </div>
            </div>

            <button class="ldg-print-btn" onclick="window.open('{{ route('admin.ledger.print', ['partyType' => $partyType, 'id' => $partyType === 'vendor' ? $party->id : $party->_id]) }}', '_blank')">
                🖨️ Print Ledger
            </button>
        </div>

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
                        <th style="width:180px">Voucher Type</th>
                        <th>Voucher No.</th>
                        <th class="ta-r" style="width:110px">Debit (₹)</th>
                        <th class="ta-r" style="width:110px">Credit (₹)</th>
                        <th class="ta-r" style="width:100px">TDS Party</th>
                        <th class="ta-r" style="width:100px">TDS Self</th>
                        <th class="ta-r" style="width:130px">Balance (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ledger as $entry)
                    <tr class="{{ $entry['is_opening'] ? 'ldg-opening-row' : '' }}
                            {{ ($entry['is_parent'] ?? false) ? 'ldg-parent-row' : '' }}
                            {{ ($entry['is_child'] ?? false) ? 'ldg-child-row-tr' : '' }}"
                        data-raw-date="{{ $entry['raw_date'] ? \Carbon\Carbon::parse($entry['raw_date'])->format('Y-m-d') : '' }}"
                        data-is-opening="{{ $entry['is_opening'] ? '1' : '0' }}"
                        data-debit="{{ $entry['debit'] }}"
                        data-credit="{{ $entry['credit'] }}"
                        data-balance="{{ $entry['balance'] }}">
                        <td class="ldg-date">{{ $entry['is_opening'] ? 'Opening' : $entry['date'] }}</td>
                        <td>
                            <span class="ldg-vtype vt-{{ Str::slug($entry['voucher_type']) }}">
                                {{ $entry['voucher_type'] }}
                            </span>
                        </td>
                        <td class="ldg-mono">{{ $entry['voucher_no'] }}</td>
                        <td class="ta-r {{ $entry['debit'] > 0 ? 'ldg-dr fw6' : 'ldg-muted' }}">
                            {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '—' }}
                        </td>
                        <td class="ta-r {{ $entry['credit'] > 0 ? 'ldg-cr fw6' : 'ldg-muted' }}">
                            {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '—' }}
                        </td>
                        <td class="ta-r ldg-muted">
                            {{ ($entry['tds_by_party'] ?? 0) > 0 ? number_format($entry['tds_by_party'], 2) : '—' }}
                        </td>
                        <td class="ta-r ldg-muted">
                            {{ ($entry['tds_by_self'] ?? 0) > 0 ? number_format($entry['tds_by_self'], 2) : '—' }}
                        </td>
                        <td class="ta-r fw6 ldg-bal-cell {{ $entry['balance'] > 0 ? 'ldg-dr' : ($entry['balance'] < 0 ? 'ldg-cr' : 'ldg-muted') }}">
                            {{ number_format(abs($entry['balance']), 2) }}
                            @if(!$entry['is_opening'])
                                <span class="ldg-drcr">{{ $entry['balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="ldg-tfoot" id="ledgerTfoot">
                        <td colspan="3" class="fw6">Total</td>
                        <td class="ta-r fw6 ldg-dr" id="tfoot_debit">
                            {{ number_format($ledger->where('is_opening', false)->sum('debit'), 2) }}
                        </td>
                        <td class="ta-r fw6 ldg-cr" id="tfoot_credit">
                            {{ number_format($ledger->where('is_opening', false)->sum('credit'), 2) }}
                        </td>
                        <td colspan="2"></td>
                        <td class="ta-r fw6 {{ $summary['closing_balance'] >= 0 ? 'ldg-dr' : 'ldg-cr' }}" id="tfoot_balance">
                            {{ number_format(abs($summary['closing_balance']), 2) }}
                            <span class="ldg-drcr">{{ $summary['closing_balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         TAB 3 — ITEM WISE
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
         TAB 4 — PROFILE
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
                    <tr><td class="ldg-pk">Total Debit</td><td class="fw6 ldg-dr">₹{{ number_format($summary['total_debit'], 2) }}</td></tr>
                    <tr><td class="ldg-pk">Total Credit</td><td class="fw6 ldg-cr">₹{{ number_format($summary['total_credit'], 2) }}</td></tr>
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
.ldg-summary { display:flex; align-items:center; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; margin-bottom:16px; overflow:hidden; }
.ldg-sum-item { flex:1; padding:12px 18px; display:flex; flex-direction:column; gap:3px; }
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
.ldg-print-btn { margin-left:auto; padding:5px 12px; border:1px solid #e5e7eb; border-radius:4px; background:#fff; font-size:11px; font-weight:500; color:#374151; cursor:pointer; }
.ldg-print-btn:hover { background:#f3f4f6; }

/* TABLE */
.ldg-table-wrap { border:1px solid #e5e7eb; border-radius:6px; overflow-x:auto; background:#fff; }
.ldg-table { width:100%; border-collapse:collapse; font-size:12px; }
.ldg-table thead th { background:#f9fafb; padding:8px 12px; text-align:left; font-weight:600; font-size:10px; color:#6b7280; border-bottom:1px solid #e5e7eb; text-transform:uppercase; letter-spacing:.4px; white-space:nowrap; }
.ldg-table tbody td { padding:8px 12px; border-bottom:1px solid #f3f4f6; vertical-align:middle; white-space:nowrap; }
.ldg-table tbody tr:last-child td { border-bottom:none; }
.ldg-table tbody tr:hover { background:#fafafa; }

/* Parent invoice row — slightly highlighted */
.ldg-parent-row { background:#fafbff !important; }
.ldg-parent-row:hover { background:#f0f4ff !important; }
.ldg-parent-row td { font-weight:500; }
.ldg-parent-row td:first-child { border-left:3px solid #6366f1; }

/* Child rows — slightly indented feel */
.ldg-child-row-tr td { background:#fff; color:#374151; }
.ldg-child-row-tr td:first-child {border-left:3px solid #e5e7eb; }
.ldg-child-row-tr:hover td { background:#f9fafb !important; }

.ldg-child-prefix { color:#d1d5db; margin-right:4px; font-size:11px; }

/* Ledger rows */
.ldg-opening-row { background:#fffdf5 !important; }
.ldg-opening-row td { font-style:italic; color:#78716c; }
.ldg-tfoot td { padding:8px 12px; background:#f9fafb; border-top:1px solid #e5e7eb; font-size:11px; }

/* TYPE CHIPS */
.ldg-chip { display:inline-block; padding:2px 7px; border-radius:3px; font-size:10px; font-weight:500; white-space:nowrap; }
.chip-sale        { background:#eff6ff; color:#1e40af; }
.chip-purchase    { background:#f5f3ff; color:#5b21b6; }
.chip-payment_in  { background:#f0fdf4; color:#166534; }
.chip-payment_out { background:#fef2f2; color:#991b1b; }
.chip-credit_note { background:#fffbeb; color:#92400e; }
.chip-debit_note  { background:#eef2ff; color:#3730a3; }

/* VOUCHER TYPE */
.ldg-vtype { display:inline-block; font-size:10px; font-weight:500; padding:1px 6px; border-radius:3px; }
.vt-opening-balance   { background:#fffbeb; color:#92400e; }
.vt-sale-invoice      { background:#eff6ff; color:#1e40af; }
.vt-purchase-invoice  { background:#f5f3ff; color:#5b21b6; }
.vt-payment-in        { background:#f0fdf4; color:#166534; }
.vt-payment-out       { background:#fef2f2; color:#991b1b; }
.vt-credit-note       { background:#fffbeb; color:#92400e; }
.vt-debit-note        { background:#eef2ff; color:#3730a3; }
.vt-debit-refund-recv { background:#f0fdf4; color:#166534; }
.vt-credit-refund-out { background:#fef2f2; color:#991b1b; }
.vt-debit-note-refund { background:#f0fdf4; color:#166534; }

/* FLOW BADGE */
.ldg-flow { display:inline-block; font-size:9px; font-weight:700; padding:2px 6px; border-radius:2px; letter-spacing:.4px; }
.flow-debit  { background:#fef2f2; color:#b91c1c; }
.flow-credit { background:#f0fdf4; color:#15803d; }

/* STATUS */
.ldg-st { display:inline-block; font-size:10px; padding:2px 7px; border-radius:3px; font-weight:500; text-transform:capitalize; }
.st-paid      { background:#f0fdf4; color:#15803d; }
.st-unpaid    { background:#fef2f2; color:#b91c1c; }
.st-partial   { background:#fffbeb; color:#92400e; }
.st-active    { background:#eff6ff; color:#1d4ed8; }
.st-settled   { background:#f3f4f6; color:#374151; }
.st-cancelled { background:#f9fafb; color:#9ca3af; }
.st-completed { background:#f0fdf4; color:#15803d; }
.st-confirmed { background:#f0fdf4; color:#15803d; }
.st-draft     { background:#fffbeb; color:#92400e; }

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
@media (max-width:768px)  { .ldg-profile-grid { grid-template-columns:1fr; } .ldg-topbar { flex-direction:column; align-items:flex-start; gap:8px; } .ldg-tabs { overflow-x:auto; } .ldg-summary { flex-wrap:wrap; } }
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

// ========== LEDGER DATE FILTER ==========
function applyLedgerFilter(fromDate, toDate, labelText) {
    var rows = document.querySelectorAll('#ledgerTable tbody tr');
    var totalDebit = 0, totalCredit = 0, lastBalance = 0;
    var visibleCount = 0;

    rows.forEach(function(row) {
        var isOpening = row.dataset.isOpening === '1';
        var rawDate = row.dataset.rawDate;

        if (isOpening) {
            row.style.display = '';
            return;
        }

        if (!fromDate && !toDate) {
            row.style.display = '';
            totalDebit  += parseFloat(row.dataset.debit  || 0);
            totalCredit += parseFloat(row.dataset.credit || 0);
            lastBalance  = parseFloat(row.dataset.balance || 0);
            visibleCount++;
            return;
        }

        var rowDate = rawDate ? new Date(rawDate) : null;
        var show = true;

        if (rowDate) {
            if (fromDate && rowDate < fromDate) show = false;
            if (toDate   && rowDate > toDate)   show = false;
        }

        row.style.display = show ? '' : 'none';

        if (show) {
            totalDebit  += parseFloat(row.dataset.debit  || 0);
            totalCredit += parseFloat(row.dataset.credit || 0);
            lastBalance  = parseFloat(row.dataset.balance || 0);
            visibleCount++;
        }
    });

    // Update tfoot
    document.getElementById('tfoot_debit').textContent  = totalDebit.toFixed(2);
    document.getElementById('tfoot_credit').textContent = totalCredit.toFixed(2);
    var balEl = document.getElementById('tfoot_balance');
    balEl.textContent = Math.abs(lastBalance).toFixed(2) + (lastBalance >= 0 ? ' Dr' : ' Cr');
    balEl.className = 'ta-r fw6 ' + (lastBalance >= 0 ? 'ldg-dr' : 'ldg-cr');

    // Show filtered summary
    var summaryEl = document.getElementById('ledgerFilteredSummary');
    if (fromDate || toDate) {
        summaryEl.style.display = 'flex';
        document.getElementById('flt_period').textContent  = labelText;
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

// Ledger search (existing) — update to respect filter
var ledgerSearchEl = document.getElementById('ledgerSearch');
if (ledgerSearchEl) {
    ledgerSearchEl.addEventListener('input', function() {
        var t = this.value.toLowerCase();
        document.querySelectorAll('#ledgerTable tbody tr').forEach(function(row) {
            if (row.dataset.isOpening === '1') return;
            if (row.style.display === 'none') return;
            var matches = row.textContent.toLowerCase().indexOf(t) > -1;
            row.style.display = matches ? '' : 'none';
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

// Transaction search
var txnSearchEl = document.getElementById('txnSearch');
if (txnSearchEl) {
    txnSearchEl.addEventListener('input', function() { filterTable('txnTable', this.value); });
}

// Item search
var itemSearchEl = document.getElementById('itemSearch');
if (itemSearchEl) {
    itemSearchEl.addEventListener('input', function() { filterTable('itemTable', this.value); });
}

// Type filter pills
document.querySelectorAll('#txnFilters .ldg-pill').forEach(function(pill) {
    pill.addEventListener('click', function() {
        document.querySelectorAll('#txnFilters .ldg-pill').forEach(function(p) { p.classList.remove('active'); });
        this.classList.add('active');

        var type   = this.dataset.type;
        var search = (document.getElementById('txnSearch').value || '').toLowerCase();
        var activeParentVisible = false;
        var lastParentRow = null;

        document.querySelectorAll('#txnTable tbody tr').forEach(function(row) {
            var rowType  = row.dataset.type || '';
            var rowText  = row.textContent.toLowerCase();
            var isChild  = row.classList.contains('ldg-child-row-tr');
            var isParent = row.classList.contains('ldg-parent-row');

            if (isParent) {
                // Show parent if type=all or type matches parent
                var matchType = type === 'all' || rowType === type;
                var matchText = !search || rowText.indexOf(search) > -1;
                row.style.display = (matchType && matchText) ? '' : 'none';
                lastParentRow = row;
                activeParentVisible = row.style.display !== 'none';
            } else if (isChild) {
                if (type === 'all') {
                    var matchText = !search || rowText.indexOf(search) > -1;
                    row.style.display = matchText ? '' : 'none';
                } else if (rowType === type) {
                    // Show this child + ensure its parent is visible
                    var matchText = !search || rowText.indexOf(search) > -1;
                    row.style.display = matchText ? '' : 'none';
                    if (matchText && lastParentRow) {
                        lastParentRow.style.display = '';
                    }
                } else {
                    row.style.display = 'none';
                }
            } else {
                // Standalone rows
                var matchType = type === 'all' || rowType === type;
                var matchText = !search || rowText.indexOf(search) > -1;
                row.style.display = (matchType && matchText) ? '' : 'none';
            }
        });
    });
});
</script>
@endpush
@endsection
