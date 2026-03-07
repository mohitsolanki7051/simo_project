@extends('layouts.admin')

@section('title', 'Payment In - Transactions')
@section('header-title', 'Payment In Transactions')

@section('content')
<div class="payments-container">

    {{-- ── Alert Container ──────────────────────────────────── --}}
    <div id="alertContainer"></div>

    {{-- ── Page Header ──────────────────────────────────────── --}}
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">💰 Payment In Transactions</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.payments.create') }}" class="btn-action btn-primary">
                + New Payment In
            </a>
        </div>
    </div>



    {{-- ── Summary Stats ─────────────────────────────────────── --}}
    @php
        $totalAmount = $payments->sum(function($p) {
            return $p->amount instanceof \MongoDB\BSON\Decimal128
                ? (float) $p->amount->__toString()
                : (float) $p->amount;
        });
    @endphp
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Payments</div>
            <div class="stat-value">{{ $payments->total() }}</div>
        </div>
        <div class="stat-card stat-green">
            <div class="stat-label">Amount (This Page)</div>
            <div class="stat-value">₹{{ number_format($totalAmount, 2) }}</div>
        </div>
        <div class="stat-card stat-blue">
            <div class="stat-label">Showing</div>
            <div class="stat-value">{{ $payments->count() }} records</div>
        </div>
    </div>

      {{-- ── Filters ───────────────────────────────────────────── --}}
    <div class="filters-card">
        <div class="filters-header">Filters</div>
        <div class="filters-body">
            <form method="GET" action="{{ route('admin.payments.index') }}">
                <div class="filters-grid">
                    <div class="filter-field">
                        <label>Search</label>
                        <input type="text" name="search" class="f-input"
                               placeholder="Payment No / Reference…" value="{{ request('search') }}">
                    </div>
                    <div class="filter-field">
                        <label>Party</label>
                        <select name="party_id" class="f-input">
                            <option value="">All Parties</option>
                            @foreach($parties as $party)
                            <option value="{{ $party['id'] }}" {{ request('party_id') == $party['id'] ? 'selected' : '' }}>
                                {{ $party['name'] }} ({{ $party['party_type_text'] }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>Payment Method</label>
                        <select name="payment_method" class="f-input">
                            <option value="">All Methods</option>
                            <option value="cash"          {{ request('payment_method') == 'cash'          ? 'selected' : '' }}>Cash</option>
                            <option value="upi"           {{ request('payment_method') == 'upi'           ? 'selected' : '' }}>UPI</option>
                            <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="cheque"        {{ request('payment_method') == 'cheque'        ? 'selected' : '' }}>Cheque</option>
                            <option value="card"          {{ request('payment_method') == 'card'          ? 'selected' : '' }}>Card</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>From Date</label>
                        <input type="date" name="from_date" class="f-input" value="{{ request('from_date') }}">
                    </div>
                    <div class="filter-field">
                        <label>To Date</label>
                        <input type="date" name="to_date" class="f-input" value="{{ request('to_date') }}">
                    </div>
                    <div class="filter-field filter-btns">
                        <button type="submit" class="btn-filter-apply">Apply</button>
                        <a href="{{ route('admin.payments.index') }}" class="btn-filter-reset">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Payments Table ────────────────────────────────────── --}}
    <div class="table-card">
        <div class="table-wrapper">
            <table class="pi-table">
                <thead>
                    <tr>
                        <th>Payment No</th>
                        <th>Date</th>
                        <th>Party</th>
                        <th>Type</th>
                        <th class="text-right">Amount</th>
                        <th>Method</th>
                        <th>Allocated To</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    @php
                        $amount      = $payment->amount instanceof \MongoDB\BSON\Decimal128
                                        ? (float) $payment->amount->__toString()
                                        : (float) $payment->amount;
                        $allocations = $payment->allocations ?? [];
                        $openingAlloc  = collect($allocations)->where('type', 'opening_balance')->sum('amount');
                        $invoiceAllocs = collect($allocations)->where('type', 'invoice')->count();
                    @endphp
                    <tr>
                        <td>
                            <span class="payment-no-badge">{{ $payment->payment_number ?? 'N/A' }}</span>
                        </td>
                        <td class="date-cell">
                            {{ $payment->payment_date->format('d M Y') }}
                        </td>
                        <td>
                            <div class="party-name">{{ $payment->party->name ?? '—' }}</div>
                            @if($payment->party?->phone)
                            <div class="party-phone">{{ $payment->party->phone }}</div>
                            @endif
                        </td>
                        <td>
                            @if($payment->party?->party_type)
                            <span class="type-badge type-{{ $payment->party->party_type }}">
                                {{ ucfirst($payment->party->party_type) }}
                            </span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <span class="amount-val">₹{{ number_format($amount, 2) }}</span>
                        </td>
                        <td>
                            <span class="method-badge method-{{ $payment->payment_method }}">
                                {{ $payment->payment_method_text }}
                            </span>
                        </td>
                        <td>
                            <div class="alloc-wrap">
                                @if($openingAlloc > 0)
                                <span class="alloc-tag alloc-opening">Opening ₹{{ number_format($openingAlloc, 2) }}</span>
                                @endif
                                @if($invoiceAllocs > 0)
                                <span class="alloc-tag alloc-invoice">{{ $invoiceAllocs }} Invoice{{ $invoiceAllocs > 1 ? 's' : '' }}</span>
                                @endif
                                @if($openingAlloc == 0 && $invoiceAllocs == 0)
                                <span class="text-muted">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn-view-detail"
                                    onclick="viewPayment('{{ $payment->_id }}')">
                                View
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-icon">💰</div>
                                <div class="empty-title">No Payment In transactions found</div>
                                <div class="empty-sub">Try changing the filters or create a new payment</div>
                                <a href="{{ route('admin.payments.create') }}" class="btn-action btn-primary" style="margin-top:12px;">
                                    + New Payment In
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->count() > 0)
        <div class="table-foot">
            <div class="foot-info">
                Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }} payments
            </div>
            <div class="foot-pagination">
                {{ $payments->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     VIEW PAYMENT MODAL
═══════════════════════════════════════════════════════════ --}}
<div class="modal-backdrop" id="viewModal" style="display:none;">
    <div class="modal-box modal-lg">
        <div class="modal-head">
            <div class="modal-head-icon">💰</div>
            <div class="modal-head-info">
                <div class="modal-head-title">Payment Details</div>
                <div class="modal-head-sub" id="modalPaymentNo">—</div>
            </div>
            <button class="modal-close-btn" onclick="closeViewModal()">✕</button>
        </div>

        <div class="modal-body" id="modalBody">
            {{-- Content injected by JS --}}
            <div id="modalLoader" class="modal-loader">
                <div class="spinner"></div>
                <span>Loading…</span>
            </div>
            <div id="modalContent" style="display:none;"></div>
        </div>

        <div class="modal-foot">
            <button class="btn-modal-close" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* ── Root ───────────────────────────────────────────────── */
:root {
    --c-bg:      #f4f6f9;
    --c-white:   #ffffff;
    --c-border:  #e2e8f0;
    --c-text:    #1e293b;
    --c-muted:   #64748b;
    --c-label:   #374151;
    --c-primary: #3b82f6;
    --c-green:   #10b981;
    --c-warn:    #f59e0b;
    --c-danger:  #ef4444;
    --radius:    8px;
    --shadow:    0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.05);
}

/* ── Wrapper ────────────────────────────────────────────── */
.payments-container {
    max-width: 1300px;
    margin: 0 auto;
    font-family: 'Segoe UI', system-ui, sans-serif;
    font-size: 13px;
    color: var(--c-text);
}

/* ── Header ─────────────────────────────────────────────── */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 18px;
}
.page-title   { font-size: 20px; font-weight: 700; margin: 0 0 2px; }
.page-subtitle { font-size: 12px; color: var(--c-muted); }

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all .15s;
}
.btn-primary { background: #fb7f29; color: #fff; }
.btn-primary:hover { background: #fb7f29; color: #fff; transform: translateY(-1px); }

/* ── Filters ────────────────────────────────────────────── */
.filters-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    margin-bottom: 16px;
}
.filters-header {
    padding: 10px 16px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--c-label);
    border-bottom: 1px solid var(--c-border);
    background: #fafbfc;
    border-radius: var(--radius) var(--radius) 0 0;
}
.filters-body { padding: 14px 16px; }
.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    align-items: end;
}
.filter-field { display: flex; flex-direction: column; gap: 4px; }
.filter-field label { font-size: 11px; font-weight: 500; color: var(--c-label); }
.f-input {
    padding: 7px 9px;
    border: 1px solid var(--c-border);
    border-radius: 5px;
    font-size: 12px;
    background: #f9fafb;
    color: var(--c-text);
    height: 32px;
    width: 100%;
    box-sizing: border-box;
}
.f-input:focus { outline: none; border-color: var(--c-primary); background: #fff; }
.filter-btns { display: flex; gap: 8px; align-items: center; }
.btn-filter-apply, .btn-filter-reset {
    padding: 6px 16px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    height: 32px;
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    border: none;
    transition: all .15s;
}
.btn-filter-apply { background: var(--c-primary); color: #fff; }
.btn-filter-apply:hover { background: #2563eb; }
.btn-filter-reset { background: #f3f4f6; color: var(--c-label); border: 1px solid var(--c-border); }
.btn-filter-reset:hover { background: #e5e7eb; }

/* ── Stats ──────────────────────────────────────────────── */
.stats-row {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.stat-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--radius);
    padding: 12px 18px;
    box-shadow: var(--shadow);
    flex: 1;
    min-width: 140px;
}
.stat-card.stat-green { border-left: 3px solid var(--c-green); }
.stat-card.stat-blue  { border-left: 3px solid var(--c-primary); }
.stat-label { font-size: 11px; color: var(--c-muted); margin-bottom: 4px; }
.stat-value { font-size: 18px; font-weight: 700; color: var(--c-text); }

/* ── Table Card ─────────────────────────────────────────── */
.table-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}
.table-wrapper { overflow-x: auto; }
.pi-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}
.pi-table th {
    background: #f8fafc;
    padding: 11px 14px;
    text-align: left;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--c-muted);
    border-bottom: 1px solid var(--c-border);
    white-space: nowrap;
}
.pi-table td {
    padding: 11px 14px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.pi-table tbody tr:last-child td { border-bottom: none; }
.pi-table tbody tr:hover { background: #fafbfc; }
.text-right  { text-align: right; }
.text-center { text-align: center; }
.text-muted  { color: var(--c-muted); }

/* ── Payment No Badge ────────────────────────────────────── */
.payment-no-badge {
    display: inline-block;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1d4ed8;
    padding: 3px 9px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 600;
    font-family: monospace;
    white-space: nowrap;
}

/* ── Party ──────────────────────────────────────────────── */
.date-cell { white-space: nowrap; color: var(--c-muted); font-size: 12px; }
.party-name  { font-weight: 500; font-size: 12.5px; }
.party-phone { font-size: 11px; color: var(--c-muted); margin-top: 2px; }

/* ── Type Badge ─────────────────────────────────────────── */
.type-badge {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 10.5px;
    font-weight: 600;
    text-transform: capitalize;
}
.type-customer    { background: #dbeafe; color: #1e40af; }
.type-dealer      { background: #fef3c7; color: #92400e; }
.type-distributor { background: #d1fae5; color: #065f46; }

/* ── Amount ─────────────────────────────────────────────── */
.amount-val { font-weight: 700; font-size: 13px; color: #065f46; }

/* ── Method Badge ───────────────────────────────────────── */
.method-badge {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 10.5px;
    font-weight: 600;
    white-space: nowrap;
}
.method-cash          { background: #d1fae5; color: #065f46; }
.method-upi           { background: #dbeafe; color: #1e40af; }
.method-bank_transfer { background: #fef3c7; color: #92400e; }
.method-cheque        { background: #fed7aa; color: #9a3412; }
.method-card          { background: #e0e7ff; color: #3730a3; }

/* ── Reference ──────────────────────────────────────────── */
.ref-no { font-size: 11px; color: var(--c-muted); font-family: monospace; }

/* ── Allocation Tags ─────────────────────────────────────── */
.alloc-wrap { display: flex; flex-wrap: wrap; gap: 4px; }
.alloc-tag  {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 500;
    white-space: nowrap;
}
.alloc-opening { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
.alloc-invoice { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }

/* ── View Button ─────────────────────────────────────────── */
.btn-view-detail {
    padding: 5px 14px;
    background: var(--c-primary);
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 11.5px;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s;
}
.btn-view-detail:hover { background: #2563eb; }

/* ── Empty State ─────────────────────────────────────────── */
.empty-state { text-align: center; padding: 50px 20px; }
.empty-icon  { font-size: 48px; opacity: .45; margin-bottom: 12px; }
.empty-title { font-size: 15px; font-weight: 600; color: var(--c-text); margin-bottom: 6px; }
.empty-sub   { font-size: 12px; color: var(--c-muted); }

/* ── Table Footer ────────────────────────────────────────── */
.table-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 11px 16px;
    border-top: 1px solid var(--c-border);
    background: #fafbfc;
    font-size: 11.5px;
    color: var(--c-muted);
}

/* ── Modal ───────────────────────────────────────────────── */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,.45);
    backdrop-filter: blur(2px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.modal-box {
    background: var(--c-white);
    border-radius: 12px;
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
    animation: modalIn .2s ease;
    display: flex;
    flex-direction: column;
}
.modal-lg { max-width: 780px; }
@keyframes modalIn {
    from { opacity:0; transform: translateY(20px) scale(.97); }
    to   { opacity:1; transform: translateY(0)    scale(1);   }
}

/* Modal Head */
.modal-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 22px;
    border-bottom: 1px solid var(--c-border);
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 12px 12px 0 0;
    position: sticky;
    top: 0;
    z-index: 1;
}
.modal-head-icon {
    width: 42px;
    height: 42px;
    background: var(--c-green);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 4px 8px rgba(16,185,129,.25);
}
.modal-head-title { font-size: 15px; font-weight: 700; color: var(--c-text); }
.modal-head-sub   { font-size: 11.5px; color: var(--c-muted); margin-top: 2px; }
.modal-close-btn {
    margin-left: auto;
    background: none;
    border: none;
    font-size: 16px;
    color: var(--c-muted);
    cursor: pointer;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .15s;
}
.modal-close-btn:hover { background: #f3f4f6; color: var(--c-text); }

/* Modal Body */
.modal-body { padding: 22px; flex: 1; }

/* Modal Loader */
.modal-loader {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 40px;
    color: var(--c-muted);
    font-size: 13px;
}
.spinner {
    width: 22px; height: 22px;
    border: 3px solid #e5e7eb;
    border-top-color: var(--c-primary);
    border-radius: 50%;
    animation: spin .8s linear infinite;
    flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Modal Foot */
.modal-foot {
    padding: 14px 22px;
    border-top: 1px solid var(--c-border);
    background: #fafbfc;
    border-radius: 0 0 12px 12px;
    display: flex;
    justify-content: flex-end;
}
.btn-modal-close {
    padding: 8px 22px;
    background: #f3f4f6;
    color: var(--c-label);
    border: 1px solid var(--c-border);
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s;
}
.btn-modal-close:hover { background: #e5e7eb; }

/* ── Modal Detail Styles ─────────────────────────────────── */
.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
}
.detail-item { display: flex; flex-direction: column; gap: 3px; }
.detail-label { font-size: 11px; color: var(--c-muted); font-weight: 500; text-transform: uppercase; letter-spacing: .4px; }
.detail-value { font-size: 13.5px; font-weight: 600; color: var(--c-text); }
.detail-value.green { color: var(--c-green); }
.detail-value.blue  { color: var(--c-primary); }

.section-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--c-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin: 0 0 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--c-border);
}

/* Allocation Table inside modal */
.alloc-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}
.alloc-table th {
    background: #f8fafc;
    padding: 8px 10px;
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    color: var(--c-muted);
    border-bottom: 1px solid var(--c-border);
}
.alloc-table td {
    padding: 9px 10px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.alloc-table tbody tr:last-child td { border-bottom: none; }
.alloc-table .text-right { text-align: right; }

.alloc-type-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 600;
}
.alloc-type-badge.opening { background: #f3e8ff; color: #6b21a8; }
.alloc-type-badge.invoice { background: #dbeafe; color: #1e40af; }

.balance-change {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
}
.balance-from { color: var(--c-danger); text-decoration: line-through; opacity: .7; }
.balance-arrow { color: var(--c-muted); }
.balance-to    { color: var(--c-green); font-weight: 600; }

.no-alloc {
    text-align: center;
    padding: 20px;
    color: var(--c-muted);
    font-size: 12.5px;
}

/* Summary box inside modal */
.summary-box {
    display: flex;
    gap: 0;
    border: 1px solid var(--c-border);
    border-radius: var(--radius);
    overflow: hidden;
    margin-top: 16px;
}
.summary-box-item {
    flex: 1;
    padding: 12px 16px;
    text-align: center;
    border-right: 1px solid var(--c-border);
}
.summary-box-item:last-child { border-right: none; }
.summary-box-label { font-size: 10.5px; color: var(--c-muted); font-weight: 500; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .3px; }
.summary-box-val   { font-size: 14px; font-weight: 700; }
.summary-box-val.green  { color: var(--c-green); }
.summary-box-val.purple { color: #7c3aed; }
.summary-box-val.blue   { color: var(--c-primary); }

/* Alert */
#alertContainer { position: fixed; top: 20px; right: 20px; z-index: 9999; }
.pi-alert {
    padding: 11px 16px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,.1);
    animation: slideIn .25s ease;
}
@keyframes slideIn { from { opacity:0; transform: translateX(40px); } to { opacity:1; transform: translateX(0); } }
.pi-alert.success { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
.pi-alert.error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
</style>
@endpush

@push('scripts')
<script>
function viewPayment(id) {
    document.getElementById('viewModal').style.display = 'flex';
    document.getElementById('modalPaymentNo').textContent = 'Loading…';
    document.getElementById('modalLoader').style.display  = 'flex';
    document.getElementById('modalContent').style.display = 'none';

    $.get('{{ route("admin.payments.show", "__PLACEHOLDER__") }}'.replace('__PLACEHOLDER__', id))
        .done(function(res) {
            document.getElementById('modalLoader').style.display = 'none';
            if (!res.success) { showAlert('Failed to load payment details', 'error'); closeViewModal(); return; }

            const p = res.payment;
            document.getElementById('modalPaymentNo').textContent = p.payment_number || '—';

            /* ── Build modal content ── */
            let html = '';

            /* Info grid */
            html += `<div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Payment Number</span>
                    <span class="detail-value blue">${esc(p.payment_number)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date</span>
                    <span class="detail-value">${esc(p.date)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Party Name</span>
                    <span class="detail-value">${esc(p.party_name)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Party Type</span>
                    <span class="detail-value">${esc(p.party_type)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Amount Received</span>
                    <span class="detail-value green">₹${fmt(p.amount)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value">${esc(p.payment_method)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Reference No</span>
                    <span class="detail-value">${esc(p.reference_no)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Notes</span>
                    <span class="detail-value">${esc(p.notes)}</span>
                </div>
            </div>`;

            /* Allocation breakdown */
            const allocs = p.allocations || [];
            let totalAllocated = 0;
            let openingTotal   = 0;
            let invoiceTotal   = 0;

            html += `<p class="section-title">Allocation Breakdown</p>`;

            if (allocs.length === 0) {
                html += `<div class="no-alloc">No allocation details available</div>`;
            } else {
                html += `<table class="alloc-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Details</th>
                            <th class="text-right">Allocated</th>
                            <th>Balance Change</th>
                        </tr>
                    </thead>
                    <tbody>`;

                allocs.forEach(function(a) {
                    totalAllocated += parseFloat(a.amount) || 0;

                    if (a.type === 'opening_balance') {
                        openingTotal += parseFloat(a.amount) || 0;
                        html += `<tr>
                            <td><span class="alloc-type-badge opening">Opening Bal.</span></td>
                            <td>${esc(a.description || 'Opening Balance Payment')}</td>
                            <td class="text-right"><strong>₹${fmt(a.amount)}</strong></td>
                            <td><span class="text-muted">—</span></td>
                        </tr>`;
                    } else if (a.type === 'invoice') {
                        invoiceTotal += parseFloat(a.amount) || 0;
                        html += `<tr>
                            <td><span class="alloc-type-badge invoice">Invoice</span></td>
                            <td>
                                <strong>${esc(a.invoice_number)}</strong>
                            </td>
                            <td class="text-right"><strong>₹${fmt(a.amount)}</strong></td>
                            <td>
                                <div class="balance-change">
                                    <span class="balance-from">₹${fmt(a.previous_balance)}</span>
                                    <span class="balance-arrow">→</span>
                                    <span class="balance-to">₹${fmt(a.new_balance)}</span>
                                </div>
                            </td>
                        </tr>`;
                    }
                });

                html += `</tbody></table>`;
            }

            /* Summary boxes */
            html += `<div class="summary-box">
                <div class="summary-box-item">
                    <div class="summary-box-label">Total Amount</div>
                    <div class="summary-box-val green">₹${fmt(p.amount)}</div>
                </div>`;

            if (openingTotal > 0) {
                html += `<div class="summary-box-item">
                    <div class="summary-box-label">Opening Balance</div>
                    <div class="summary-box-val purple">₹${fmt(openingTotal)}</div>
                </div>`;
            }
            if (invoiceTotal > 0) {
                html += `<div class="summary-box-item">
                    <div class="summary-box-label">Invoice Payments</div>
                    <div class="summary-box-val blue">₹${fmt(invoiceTotal)}</div>
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
    document.getElementById('viewModal').style.display = 'none';
    document.getElementById('modalContent').innerHTML  = '';
    document.getElementById('modalContent').style.display = 'none';
    document.getElementById('modalLoader').style.display  = 'flex';
}

/* Close on backdrop click */
document.getElementById('viewModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeViewModal();
});

/* Helpers */
function fmt(n)  { return (parseFloat(n) || 0).toFixed(2); }
function esc(s)  {
    return String(s ?? '—')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function showAlert(msg, type) {
    const c = document.getElementById('alertContainer');
    const a = document.createElement('div');
    a.className = 'pi-alert ' + type;
    a.textContent = msg;
    c.appendChild(a);
    setTimeout(() => a.remove(), 4000);
}
</script>
@endpush
