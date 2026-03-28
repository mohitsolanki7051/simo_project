{{-- resources/views/admin/purchase-returns/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Purchase Return Details - Admin Panel')
@section('header-title', 'Purchase Return Details')

@section('content')
<div class="pr-show-container">

    <div id="alertContainer"></div>

    {{-- Top Bar --}}
    <div class="pr-topbar">
        <div class="pr-topbar-left">
            <a href="{{ route('admin.purchase-returns.index') }}" class="btn-back">← Back to Returns</a>
            <h2 class="pr-page-title">Purchase Return: <span>{{ $return->return_number }}</span></h2>
        </div>
        <div class="pr-btn-group">
            @if($return->status === 'draft')
                <a href="{{ route('admin.purchase-returns.edit', $return->_id) }}" class="btn btn-edit">✎ Edit Return</a>
                <button onclick="completeReturn()" class="btn btn-complete">✓ Complete Return</button>
                <button onclick="cancelReturn()" class="btn btn-cancel-action">✕ Cancel Return</button>
            @endif
            @if($return->status === 'completed' && $return->debitNote)
                <a href="{{ route('admin.debit-notes.show', $return->debitNote->_id) }}" class="btn btn-debit">View Debit Note</a>
            @endif
        </div>
    </div>

    {{-- Status Strip --}}
    <div class="status-strip status-strip-{{ $return->status }}">
        <div class="status-dot"></div>
        <span class="status-label">{{ ucfirst($return->status) }}</span>
    </div>

    {{-- Main Grid --}}
    <div class="pr-grid">

        {{-- Left Sidebar --}}
        <div class="pr-sidebar">

            {{-- Return Info --}}
            <div class="pr-card">
                <div class="pr-card-head">
                    <div class="pr-card-icon">📦</div>
                    <h4>Return Information</h4>
                </div>
                <div class="pr-card-body">
                    <div class="kv"><span class="kv-k">Return No.</span><span class="kv-v">{{ $return->return_number }}</span></div>
                    <div class="kv"><span class="kv-k">Return Date</span><span class="kv-v">{{ $return->return_date->format('d-m-Y') }}</span></div>
                    <div class="kv"><span class="kv-k">Created By</span><span class="kv-v">{{ $return->creator?->name ?? 'System' }}</span></div>
                    <div class="kv"><span class="kv-k">Created At</span><span class="kv-v">{{ $return->created_at->format('d-m-Y H:i') }}</span></div>
                </div>
            </div>

            {{-- Original Invoice --}}
            <div class="pr-card">
                <div class="pr-card-head">
                    <div class="pr-card-icon">📄</div>
                    <h4>Original Invoice</h4>
                </div>
                <div class="pr-card-body">
                    <div class="kv">
                        <span class="kv-k">Invoice No.</span>
                        <span class="kv-v">
                            <a href="{{ route('admin.purchases.show', $return->purchase_invoice_id) }}" class="kv-link">
                                {{ $return->invoice?->invoice_number ?? 'N/A' }}
                            </a>
                        </span>
                    </div>
                    <div class="kv">
                        <span class="kv-k">Invoice Date</span>
                        <span class="kv-v">{{ $return->invoice?->invoice_date?->format('d-m-Y') ?? 'N/A' }}</span>
                    </div>
                    <div class="kv">
                        <span class="kv-k">Warehouse</span>
                        <span class="kv-v">{{ $return->warehouse?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="kv">
                        <span class="kv-k">Invoice Type</span>
                        <span class="kv-v">
                            @if($return->invoice?->invoice_type === 'gst')
                                <span class="badge badge-info">GST</span>
                            @else
                                <span class="badge badge-secondary">Cash</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Party Details --}}
            @php
                // Resolve party — vendor or dealer/distributor
                $invoice  = $return->invoice;
                $partyId  = $invoice?->party_id ?? $return->vendor_id;
                $party    = null;
                $partyTypeLabel = 'N/A';

                if ($partyId) {
                    $vendor = \App\Models\Vendor::find($partyId);
                    if ($vendor) {
                        $party          = $vendor;
                        $partyTypeLabel = 'Vendor';
                        $partyName      = $vendor->company_name;
                    } else {
                        $customer = \App\Models\Customer::find($partyId);
                        if ($customer) {
                            $party          = $customer;
                            $partyTypeLabel = ucfirst($customer->party_type ?? 'Customer');
                            $partyName      = $customer->name;
                        }
                    }
                }
            @endphp
            <div class="pr-card">
                <div class="pr-card-head">
                    <div class="pr-card-icon">🏢</div>
                    <h4>Party Details</h4>
                </div>
                <div class="pr-card-body">
                    <div class="kv">
                        <span class="kv-k">Type</span>
                        <span class="kv-v">
                            <span class="badge badge-info">{{ $partyTypeLabel }}</span>
                        </span>
                    </div>
                    <div class="kv"><span class="kv-k">Name</span><span class="kv-v">{{ $partyName ?? 'N/A' }}</span></div>
                    <div class="kv"><span class="kv-k">Phone</span><span class="kv-v">{{ $party?->phone ?? 'N/A' }}</span></div>
                    <div class="kv"><span class="kv-k">Email</span><span class="kv-v">{{ $party?->email ?? 'N/A' }}</span></div>
                    @if($party?->gst_number)
                    <div class="kv"><span class="kv-k">GST No.</span><span class="kv-v">{{ $party->gst_number }}</span></div>
                    @endif
                </div>
            </div>

            {{-- Return Details --}}
            <div class="pr-card">
                <div class="pr-card-head">
                    <div class="pr-card-icon">📝</div>
                    <h4>Return Details</h4>
                </div>
                <div class="pr-card-body">
                    <div class="kv"><span class="kv-k">Reason</span><span class="kv-v">{{ $return->reason ?? 'Not specified' }}</span></div>
                    <div class="kv"><span class="kv-k">Notes</span><span class="kv-v">{{ $return->notes ?? 'No notes' }}</span></div>
                </div>
            </div>

            {{-- Debit Note --}}
            @if($return->debitNote)
            <div class="pr-card">
                <div class="pr-card-head">
                    <div class="pr-card-icon">💰</div>
                    <h4>Debit Note</h4>
                </div>
                <div class="pr-card-body">
                    <div class="kv">
                        <span class="kv-k">Debit Note No.</span>
                        <span class="kv-v">
                            <a href="{{ route('admin.debit-notes.show', $return->debitNote->_id) }}" class="kv-link">
                                {{ $return->debitNote->debit_note_number }}
                            </a>
                        </span>
                    </div>
                    <div class="kv"><span class="kv-k">Amount</span><span class="kv-v kv-red">₹ {{ number_format((float)$return->debitNote->amount, 2) }}</span></div>
                    <div class="kv">
                        <span class="kv-k">Status</span>
                        <span class="kv-v">
                            @php
                                $dnStatus = $return->debitNote->status ?? 'active';
                                $dnBadge  = match($dnStatus) {
                                    'active'             => 'badge-info',
                                    'used'               => 'badge-success',
                                    'advance_transferred'=> 'badge-warning',
                                    default              => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $dnBadge }}">{{ ucfirst(str_replace('_', ' ', $dnStatus)) }}</span>
                        </span>
                    </div>
                    <div class="kv">
                        <span class="kv-k">Date</span>
                        <span class="kv-v">{{ $return->debitNote->debit_date?->format('d-m-Y') ?? 'N/A' }}</span>
                    </div>
                    @if((float)($return->debitNote->remaining_amount ?? 0) > 0)
                    <div class="kv">
                        <span class="kv-k">Remaining</span>
                        <span class="kv-v kv-red">₹ {{ number_format((float)$return->debitNote->remaining_amount, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>

        {{-- Right Column --}}
        <div class="pr-right">

            {{-- Items Table --}}
            <div class="pr-card">
                <div class="pr-card-head">
                    <div class="pr-card-icon">🛒</div>
                    <h4>Returned Items</h4>
                </div>
                <div class="tbl-wrap">
                    @php
                        $itemsSubtotal = 0;
                        $totalTax      = 0;
                        $isGstInvoice  = $return->invoice?->isGstInvoice() ?? false;
                        $extraDiscount     = (float)($return->invoice?->extra_discount     ?? 0);
                        $extraDiscountType =         $return->invoice?->extra_discount_type ?? 'amount';
                    @endphp

                    <table class="pr-table">
                        <thead>
                            <tr>
                                <th class="th-item">Item</th>
                                <th class="th-qty text-c">Qty</th>
                                <th class="th-price text-r">Price (₹)</th>
                                @if($isGstInvoice)
                                <th class="th-gst text-r">GST %</th>
                                <th class="th-gstamt text-r">GST Amt (₹)</th>
                                @endif
                                <th class="th-total text-r">Total (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($return->items as $item)
                                @php
                                    $itemSubtotal  = (float)$item->quantity * (float)$item->price;
                                    $itemsSubtotal += $itemSubtotal;
                                    $itemTax        = $isGstInvoice ? (float)($item->tax_amount ?? 0) : 0;
                                    $totalTax      += $itemTax;
                                @endphp
                                <tr>
                                    <td class="td-item">
                                        <div class="item-name">{{ $item->product_name }}</div>
                                        @if($item->variant_name)
                                            <div class="item-variant">→ {{ $item->variant_name }}</div>
                                        @endif
                                    </td>
                                    <td class="text-c">{{ $item->quantity }}</td>
                                    <td class="text-r">₹ {{ number_format((float)$item->price, 2) }}</td>
                                    @if($isGstInvoice)
                                    <td class="text-r">{{ $item->tax_percent }}%</td>
                                    <td class="text-r td-tax">₹ {{ number_format($itemTax, 2) }}</td>
                                    @endif
                                    <td class="text-r td-total">₹ {{ number_format($itemSubtotal + $itemTax, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @php
                                $discountAmount = 0;
                                if ($extraDiscount > 0 && $itemsSubtotal > 0) {
                                    $discountAmount = $extraDiscountType === 'percent'
                                        ? ($itemsSubtotal * $extraDiscount) / 100
                                        : $extraDiscount;
                                }
                                $finalTotal = $itemsSubtotal + $totalTax - $discountAmount;
                                $colspan    = $isGstInvoice ? 4 : 2;
                            @endphp

                            <tr class="tf-row">
                                <td colspan="{{ $colspan }}" class="tf-label">Items Subtotal</td>
                                <td class="tf-val" colspan="2">₹ {{ number_format($itemsSubtotal, 2) }}</td>
                            </tr>

                            @if($isGstInvoice && $totalTax > 0)
                            <tr class="tf-row">
                                <td colspan="{{ $colspan }}" class="tf-label">Total Tax (GST)</td>
                                <td class="tf-val td-tax" colspan="2">+ ₹ {{ number_format($totalTax, 2) }}</td>
                            </tr>
                            @endif

                            @if($discountAmount > 0)
                            <tr class="tf-row">
                                <td colspan="{{ $colspan }}" class="tf-label">
                                    Extra Discount {{ $extraDiscountType === 'percent' ? '('.$extraDiscount.'%)' : '' }}
                                </td>
                                <td class="tf-val td-disc" colspan="2">− ₹ {{ number_format($discountAmount, 2) }}</td>
                            </tr>
                            @endif

                            <tr class="tf-row tf-grand">
                                <td colspan="{{ $colspan }}" class="tf-label tf-grand-label">Grand Total</td>
                                <td class="tf-val tf-grand-val" colspan="2">₹ {{ number_format($finalTotal, 2) }}</td>
                            </tr>

                            <tr class="tf-row tf-ref">
                                <td colspan="{{ $colspan }}" class="tf-label">Return Amount (Saved)</td>
                                <td class="tf-val" colspan="2">₹ {{ number_format((float)$return->total_return_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Status Banner --}}
            @if($return->status === 'completed')
            <div class="pr-info-banner banner-success">
                <div class="banner-icon bi-success">✓</div>
                <div class="banner-body">
                    <p class="banner-title">Stock successfully removed from warehouse</p>
                    <span class="banner-sub">Items have been deducted from stock. Debit note has been generated for the party.</span>
                </div>
            </div>
            @endif

            @if($return->status === 'cancelled')
            <div class="pr-info-banner banner-cancelled">
                <div class="banner-icon bi-cancelled">✕</div>
                <div class="banner-body">
                    <p class="banner-title">Return Cancelled</p>
                    <span class="banner-sub">This return has been cancelled. No stock movement occurred.</span>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

@push('styles')
<style>
/* ── Base ──────────────────────────────────────────── */
.pr-show-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 11px;
    padding: 16px;
    background: #f4f6f8;
    min-height: 100vh;
}

/* ── Alert ─────────────────────────────────────────── */
#alertContainer {
    position: fixed;
    top: 15px;
    right: 15px;
    z-index: 9999;
}
.alert {
    padding: 10px 14px;
    margin-bottom: 8px;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 500;
    animation: slideInRight 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.alert-success { background: #f0fdf4; color: #166534; border-left: 3px solid #22c55e; }
.alert-error   { background: #fef2f2; color: #991b1b; border-left: 3px solid #ef4444; }
.alert-info    { background: #eff6ff; color: #1e40af; border-left: 3px solid #3b82f6; }
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}

/* ── Top Bar ────────────────────────────────────────── */
.pr-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    gap: 12px;
    flex-wrap: wrap;
}
.pr-topbar-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    padding: 5px 10px;
    border-radius: 4px;
    border: 1px solid #d1d5db;
    color: #6b7280;
    background: #fff;
    cursor: pointer;
    text-decoration: none;
    transition: all .15s;
}
.btn-back:hover { background: #f3f4f6; color: #374151; }
.pr-page-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}
.pr-page-title span { color: #ef4444; }

/* ── Buttons ────────────────────────────────────────── */
.pr-btn-group { display: flex; gap: 7px; flex-wrap: wrap; }
.btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 10px;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all .15s;
}
.btn-edit            { background: #ef4444; color: #fff; }
.btn-edit:hover      { background: #dc2626; }
.btn-complete        { background: #10b981; color: #fff; }
.btn-complete:hover  { background: #059669; }
.btn-cancel-action   { background: #fff; color: #6b7280; border: 1px solid #d1d5db; }
.btn-cancel-action:hover { background: #f9fafb; color: #374151; }
.btn-debit           { background: #ef4444; color: #fff; }
.btn-debit:hover     { background: #dc2626; }

/* ── Status Strip ────────────────────────────────────── */
.status-strip {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 14px;
    border-radius: 6px;
    margin-bottom: 16px;
    border-left: 3px solid;
}
.status-strip-draft     { background: #fffbeb; border-color: #f59e0b; }
.status-strip-completed { background: #fef2f2; border-color: #ef4444; }
.status-strip-cancelled { background: #f9fafb; border-color: #9ca3af; }

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}
.status-strip-draft .status-dot     { background: #f59e0b; }
.status-strip-completed .status-dot { background: #ef4444; }
.status-strip-cancelled .status-dot { background: #9ca3af; }

.status-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
}
.status-strip-draft .status-label     { color: #92400e; }
.status-strip-completed .status-label { color: #991b1b; }
.status-strip-cancelled .status-label { color: #6b7280; }

/* ── Main Grid ───────────────────────────────────────── */
.pr-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 14px;
    align-items: start;
}

/* ── Sidebar ─────────────────────────────────────────── */
.pr-sidebar {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.pr-right {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* ── Cards ───────────────────────────────────────────── */
.pr-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    overflow: hidden;
}
.pr-card-head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 13px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}
.pr-card-icon {
    width: 22px;
    height: 22px;
    background: #ef4444;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    color: #fff;
    flex-shrink: 0;
}
.pr-card-head h4 {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    margin: 0;
}
.pr-card-body { padding: 0 13px; }

/* ── Key-Value Rows ──────────────────────────────────── */
.kv {
    display: flex;
    align-items: flex-start;
    padding: 7px 0;
    border-bottom: 1px solid #f3f4f6;
    gap: 8px;
}
.kv:last-child { border-bottom: none; }
.kv-k {
    min-width: 90px;
    font-size: 10px;
    color: #9ca3af;
    flex-shrink: 0;
}
.kv-v {
    font-size: 10px;
    color: #111827;
    font-weight: 500;
    flex: 1;
    word-break: break-word;
}
.kv-v.kv-red { color: #ef4444; font-weight: 600; }
.kv-link { color: #ef4444; text-decoration: none; }
.kv-link:hover { text-decoration: underline; }

/* ── Badges ──────────────────────────────────────────── */
.badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 500;
}
.badge-success   { background: #d1fae5; color: #065f46; }
.badge-info      { background: #dbeafe; color: #1e40af; }
.badge-danger    { background: #fee2e2; color: #991b1b; }
.badge-warning   { background: #fef3c7; color: #92400e; }
.badge-secondary { background: #f3f4f6; color: #6b7280; }

/* ── Items Table ─────────────────────────────────────── */
.tbl-wrap { overflow-x: auto; }
.pr-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
}
.pr-table thead tr { background: #f9fafb; }
.pr-table thead th {
    padding: 9px 12px;
    font-size: 10px;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}
.pr-table thead th:first-child { text-align: left; }
.pr-table tbody tr { transition: background .1s; }
.pr-table tbody tr:hover { background: #fafafa; }
.pr-table tbody td {
    padding: 9px 12px;
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
    font-size: 10px;
    vertical-align: top;
}
.pr-table tbody tr:last-child td { border-bottom: none; }
.th-item   { text-align: left; min-width: 180px; }
.th-qty    { width: 50px; }
.th-price  { width: 90px; }
.th-gst    { width: 60px; }
.th-gstamt { width: 95px; }
.th-total  { width: 110px; }
.td-item   { min-width: 180px; }
.item-name    { font-weight: 500; color: #111827; }
.item-variant { font-size: 9px; color: #ef4444; margin-top: 2px; }
.td-tax   { color: #10b981; font-weight: 500; }
.td-total { font-weight: 600; color: #111827; }
.text-c { text-align: center; }
.text-r { text-align: right; }

/* ── Table Footer ────────────────────────────────────── */
.pr-table tfoot .tf-row td {
    padding: 7px 12px;
    border-top: 1px dashed #e5e7eb;
    background: #f9fafb;
}
.tf-label {
    text-align: right;
    font-weight: 500;
    color: #6b7280;
    font-size: 10px;
}
.tf-val {
    text-align: right;
    font-weight: 600;
    color: #374151;
    font-size: 10px;
}
.tf-val.td-tax  { color: #10b981; }
.tf-val.td-disc { color: #ef4444; }

.tf-grand td {
    border-top: 2px solid #fecaca !important;
    background: #fef2f2 !important;
}
.tf-grand-label {
    font-weight: 700 !important;
    color: #111827 !important;
    font-size: 11px !important;
}
.tf-grand-val {
    font-size: 14px !important;
    font-weight: 700 !important;
    color: #ef4444 !important;
}
.tf-ref td {
    border-top: 1px solid #e5e7eb !important;
    background: #f9fafb !important;
}
.tf-ref .tf-label,
.tf-ref .tf-val {
    color: #9ca3af !important;
    font-size: 9px !important;
    font-weight: 400 !important;
}

/* ── Info Banners ────────────────────────────────────── */
.pr-info-banner {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 13px 15px;
    border-radius: 7px;
    border: 1px solid;
}
.banner-success   { background: #fef2f2; border-color: #fecaca; }
.banner-cancelled { background: #f9fafb; border-color: #e5e7eb; }
.banner-icon {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
    color: #fff;
}
.bi-success   { background: #10b981; }
.bi-cancelled { background: #9ca3af; }
.banner-body { flex: 1; }
.banner-title {
    font-size: 11px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 3px;
}
.banner-sub { font-size: 10px; color: #6b7280; }

/* ── Responsive ─────────────────────────────────────── */
@media (max-width: 900px) {
    .pr-grid { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .pr-topbar { flex-direction: column; align-items: flex-start; }
    .pr-btn-group { width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
function showAlert(message, type = 'success') {
    const container = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `<span>${message}</span>`;
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}

function completeReturn() {
    if (!confirm('Complete this return? Stock will be removed from warehouse and a debit note will be generated.')) return;

    $.ajax({
        url: '{{ route('admin.purchase-returns.complete', $return->_id) }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert(response.message || 'Failed to complete return', 'error');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Failed to complete return';
            showAlert(message, 'error');
        }
    });
}

function cancelReturn() {
    if (!confirm('Are you sure you want to cancel this draft return? This action cannot be undone.')) return;

    $.ajax({
        url: '{{ route('admin.purchase-returns.cancel', $return->_id) }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert(response.message || 'Failed to cancel return', 'error');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Failed to cancel return';
            showAlert(message, 'error');
        }
    });
}
</script>
@endpush
@endsection
