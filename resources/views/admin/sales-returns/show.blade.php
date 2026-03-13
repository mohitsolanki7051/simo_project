@extends('layouts.admin')

@section('title', 'Sales Return Details - Admin Panel')
@section('header-title', 'Sales Return Details')

@section('content')
<div class="sr-container">

    <div id="alertContainer"></div>

    {{-- Top Bar --}}
    <div class="sr-topbar">
        <div class="sr-topbar-left">
            <a href="{{ route('admin.sales-returns.index') }}" class="btn-back">← Back to Returns</a>
            <h2 class="sr-page-title">Sales Return: <span>{{ $return->return_number }}</span></h2>
        </div>
        <div class="sr-btn-group">
            @if($return->status === 'draft')
                <a href="{{ route('admin.sales-returns.edit', $return->_id) }}" class="btn btn-edit">✎ Edit Return</a>
                <button onclick="completeReturn()" class="btn btn-complete">✓ Complete Return</button>
                <button onclick="cancelReturn()" class="btn btn-cancel-action">✕ Cancel Return</button>
            @endif
            @if($return->status === 'completed' && $return->creditNote)
                <a href="{{ route('admin.credit-notes.show', $return->creditNote->_id) }}" class="btn btn-credit">View Credit Note</a>
            @endif
        </div>
    </div>

    {{-- Status Strip --}}
    <div class="status-strip status-strip-{{ $return->status }}">
        <div class="status-dot"></div>
        <span class="status-label">{{ ucfirst($return->status) }}</span>
       
    </div>

    {{-- Main Grid --}}
    <div class="sr-grid">

        {{-- Left Sidebar --}}
        <div class="sr-sidebar">

            {{-- Return Info --}}
            <div class="sr-card">
                <div class="sr-card-head">
                    <div class="sr-card-icon">📦</div>
                    <h4>Return Information</h4>
                </div>
                <div class="sr-card-body">
                    <div class="kv"><span class="kv-k">Return No.</span><span class="kv-v">{{ $return->return_number }}</span></div>
                    <div class="kv"><span class="kv-k">Return Date</span><span class="kv-v">{{ $return->return_date->format('d-m-Y') }}</span></div>
                    <div class="kv"><span class="kv-k">Created By</span><span class="kv-v">{{ $return->creator?->name ?? 'System' }}</span></div>
                    <div class="kv"><span class="kv-k">Created At</span><span class="kv-v">{{ $return->created_at->format('d-m-Y H:i') }}</span></div>
                </div>
            </div>

            {{-- Original Invoice --}}
            <div class="sr-card">
                <div class="sr-card-head">
                    <div class="sr-card-icon">📄</div>
                    <h4>Original Invoice</h4>
                </div>
                <div class="sr-card-body">
                    <div class="kv">
                        <span class="kv-k">Invoice No.</span>
                        <span class="kv-v">
                            <a href="{{ route('admin.sales.show', $return->sales_invoice_id) }}" class="kv-link">
                                {{ $return->invoice?->invoice_number ?? 'N/A' }}
                            </a>
                        </span>
                    </div>
                    <div class="kv"><span class="kv-k">Invoice Date</span><span class="kv-v">{{ $return->invoice?->invoice_date?->format('d-m-Y') ?? 'N/A' }}</span></div>
                    <div class="kv"><span class="kv-k">Warehouse</span><span class="kv-v">{{ $return->warehouse?->name ?? 'N/A' }}</span></div>
                </div>
            </div>

            {{-- Party Details --}}
            <div class="sr-card">
                <div class="sr-card-head">
                    <div class="sr-card-icon">👤</div>
                    <h4>Party Details</h4>
                </div>
                <div class="sr-card-body">
                    <div class="kv"><span class="kv-k">Name</span><span class="kv-v">{{ $return->party?->name ?? 'N/A' }}</span></div>
                    <div class="kv"><span class="kv-k">Phone</span><span class="kv-v">{{ $return->party?->phone ?? 'N/A' }}</span></div>
                    <div class="kv"><span class="kv-k">Email</span><span class="kv-v">{{ $return->party?->email ?? 'N/A' }}</span></div>
                </div>
            </div>

            {{-- Return Details --}}
            <div class="sr-card">
                <div class="sr-card-head">
                    <div class="sr-card-icon">📝</div>
                    <h4>Return Details</h4>
                </div>
                <div class="sr-card-body">
                    <div class="kv"><span class="kv-k">Reason</span><span class="kv-v">{{ $return->reason ?? 'Not specified' }}</span></div>
                    <div class="kv"><span class="kv-k">Notes</span><span class="kv-v">{{ $return->notes ?? 'No notes' }}</span></div>
                </div>
            </div>

            {{-- Credit Note --}}
            @if($return->creditNote)
            <div class="sr-card">
                <div class="sr-card-head">
                    <div class="sr-card-icon">💰</div>
                    <h4>Credit Note</h4>
                </div>
                <div class="sr-card-body">
                    <div class="kv">
                        <span class="kv-k">Credit Note No.</span>
                        <span class="kv-v">
                            <a href="{{ route('admin.credit-notes.show', $return->creditNote->_id) }}" class="kv-link">
                                {{ $return->creditNote->credit_note_number }}
                            </a>
                        </span>
                    </div>
                    <div class="kv"><span class="kv-k">Amount</span><span class="kv-v kv-orange">₹ {{ number_format($return->creditNote->amount, 2) }}</span></div>
                    <div class="kv">
                        <span class="kv-k">Status</span>
                        <span class="kv-v">
                            <span class="badge {{ $return->creditNote->status_badge }}">{{ ucfirst($return->creditNote->status) }}</span>
                        </span>
                    </div>
                    <div class="kv"><span class="kv-k">Date</span><span class="kv-v">{{ $return->creditNote->credit_date->format('d-m-Y') }}</span></div>
                </div>
            </div>
            @endif

        </div>

        {{-- Right Column --}}
        <div class="sr-right">

            {{-- Items Table --}}
            <div class="sr-card">
                <div class="sr-card-head">
                    <div class="sr-card-icon">🛒</div>
                    <h4>Returned Items</h4>
                </div>
                <div class="tbl-wrap">
                    @php
                        $itemsSubtotal = 0;
                        $totalTax = 0;
                        $invoice = $return->invoice;
                        $extraDiscount = $invoice ? (float)($invoice->extra_discount ?? 0) : 0;
                        $extraDiscountType = $invoice ? ($invoice->extra_discount_type ?? 'amount') : 'amount';
                        $isGstInvoice = $invoice ? $invoice->isGstInvoice() : false;
                    @endphp

                    <table class="sr-table">
                        <thead>
                            <tr>
                                <th class="th-item">Item</th>
                                <th class="th-qty text-c">Qty</th>
                                <th class="th-price text-r">Price (₹)</th>
                                <th class="th-gst text-r">GST %</th>
                                <th class="th-gstamt text-r">GST Amt (₹)</th>
                                <th class="th-total text-r">Total (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($return->items as $item)
                                @php
                                    $itemSubtotal = $item->quantity * $item->price;
                                    $itemsSubtotal += $itemSubtotal;
                                    $itemTax = $item->tax_amount ?? 0;
                                    $totalTax += $itemTax;
                                @endphp
                                <tr>
                                    <td class="td-item">
                                        <div class="item-name">{{ $item->product_name }}</div>
                                        @if($item->variant_name)
                                            <div class="item-variant">→ {{ $item->variant_name }}</div>
                                        @endif
                                    </td>
                                    <td class="text-c">{{ $item->quantity }}</td>
                                    <td class="text-r">₹ {{ number_format($item->price, 2) }}</td>
                                    <td class="text-r">{{ $item->tax_percent }}%</td>
                                    <td class="text-r td-tax">₹ {{ number_format($itemTax, 2) }}</td>
                                    <td class="text-r td-total">₹ {{ number_format($itemSubtotal + $itemTax, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @php
                                $discountAmount = 0;
                                if ($extraDiscount > 0 && $itemsSubtotal > 0) {
                                    if ($extraDiscountType === 'percent') {
                                        $discountAmount = ($itemsSubtotal * $extraDiscount) / 100;
                                    } else {
                                        $discountAmount = $extraDiscount;
                                    }
                                }
                                $finalTotal = $itemsSubtotal + $totalTax - $discountAmount;
                            @endphp

                            <tr class="tf-row">
                                <td colspan="4" class="tf-label">Items Subtotal</td>
                                <td class="tf-val" colspan="2">₹ {{ number_format($itemsSubtotal, 2) }}</td>
                            </tr>

                            @if($isGstInvoice && $totalTax > 0)
                            <tr class="tf-row">
                                <td colspan="4" class="tf-label">Total Tax (GST)</td>
                                <td class="tf-val td-tax" colspan="2">+ ₹ {{ number_format($totalTax, 2) }}</td>
                            </tr>
                            @endif

                            @if($discountAmount > 0)
                            <tr class="tf-row">
                                <td colspan="4" class="tf-label">
                                    Extra Discount {{ $extraDiscountType === 'percent' ? '('.$extraDiscount.'%)' : '' }}
                                </td>
                                <td class="tf-val td-disc" colspan="2">− ₹ {{ number_format($discountAmount, 2) }}</td>
                            </tr>
                            @endif

                            <tr class="tf-row tf-grand">
                                <td colspan="4" class="tf-label tf-grand-label">Grand Total</td>
                                <td class="tf-val tf-grand-val" colspan="2">₹ {{ number_format($finalTotal, 2) }}</td>
                            </tr>

                            <tr class="tf-row tf-ref">
                                <td colspan="4" class="tf-label">Return Amount (Saved)</td>
                                <td class="tf-val" colspan="2">₹ {{ number_format($return->total_return_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Status Banner --}}
            @if($return->status === 'completed')
            <div class="sr-info-banner banner-success">
                <div class="banner-icon bi-success">✓</div>
                <div class="banner-body">
                    <p class="banner-title">Stock successfully returned to warehouse</p>
                    <span class="banner-sub">Items have been restocked. Credit note has been generated for the party.</span>
                </div>
            </div>
            @endif

            @if($return->status === 'cancelled')
            <div class="sr-info-banner banner-cancelled">
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
/* ── Reset & Base ─────────────────────────────────── */
.sr-container {
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
.alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.alert-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.alert-info    { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}

/* ── Top Bar ────────────────────────────────────────── */
.sr-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    gap: 12px;
    flex-wrap: wrap;
}
.sr-topbar-left {
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
.sr-page-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}
.sr-page-title span { color: #f97316; }

/* ── Buttons ────────────────────────────────────────── */
.sr-btn-group { display: flex; gap: 7px; flex-wrap: wrap; }
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
.btn-edit    { background: #f97316; color: #fff; }
.btn-edit:hover { background: #ea6c0a; }
.btn-complete { background: #f97316; color: #fff; }
.btn-complete:hover { background: #ea6c0a; }
.btn-cancel-action { background: #fff; color: #6b7280; border: 1px solid #d1d5db; }
.btn-cancel-action:hover { background: #f9fafb; color: #374151; }
.btn-credit { background: #f97316; color: #fff; }
.btn-credit:hover { background: #ea6c0a; }

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
.status-strip-draft    { background: #fff7ed; border-color: #f97316; }
.status-strip-completed { background: #fff7ed; border-color: #f97316; }
.status-strip-cancelled { background: #fef2f2; border-color: #ef4444; }

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}
.status-strip-draft .status-dot,
.status-strip-completed .status-dot { background: #f97316; }
.status-strip-cancelled .status-dot { background: #ef4444; }

.status-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
}
.status-strip-draft .status-label,
.status-strip-completed .status-label { color: #c2410c; }
.status-strip-cancelled .status-label { color: #b91c1c; }

.status-meta {
    margin-left: auto;
    font-size: 10px;
    color: #6b7280;
}
.cancelled-note { color: #b91c1c; font-style: italic; }

/* ── Main Grid ───────────────────────────────────────── */
.sr-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 14px;
    align-items: start;
}

/* ── Sidebar ─────────────────────────────────────────── */
.sr-sidebar {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* ── Cards ───────────────────────────────────────────── */
.sr-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    overflow: hidden;
}
.sr-card-head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 13px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}
.sr-card-icon {
    width: 22px;
    height: 22px;
    background: #f97316;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    color: #fff;
    flex-shrink: 0;
}
.sr-card-head h4 {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    margin: 0;
}
.sr-card-body { padding: 0 13px; }

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
    min-width: 95px;
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
.kv-v.kv-orange { color: #f97316; font-weight: 600; }
.kv-link { color: #f97316; text-decoration: none; }
.kv-link:hover { text-decoration: underline; }

/* ── Badges ──────────────────────────────────────────── */
.badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 500;
}
.badge-success { background: #d1fae5; color: #065f46; }
.badge-info    { background: #dbeafe; color: #1e40af; }
.badge-danger  { background: #fee2e2; color: #991b1b; }

/* ── Right Column ────────────────────────────────────── */
.sr-right {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* ── Items Table ─────────────────────────────────────── */
.tbl-wrap { overflow-x: auto; }
.sr-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
}
.sr-table thead tr {
    background: #f9fafb;
}
.sr-table thead th {
    padding: 9px 12px;
    font-size: 10px;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}
.sr-table thead th:first-child { text-align: left; }
.sr-table tbody tr { transition: background .1s; }
.sr-table tbody tr:hover { background: #fafafa; }
.sr-table tbody td {
    padding: 9px 12px;
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
    font-size: 10px;
    vertical-align: top;
}
.sr-table tbody tr:last-child td { border-bottom: none; }
.th-item  { text-align: left; min-width: 180px; }
.th-qty   { width: 50px; }
.th-price { width: 90px; }
.th-gst   { width: 60px; }
.th-gstamt{ width: 95px; }
.th-total { width: 105px; }
.td-item  { min-width: 180px; }
.item-name    { font-weight: 500; color: #111827; }
.item-variant { font-size: 9px; color: #f97316; margin-top: 2px; }
.td-tax   { color: #10b981; font-weight: 500; }
.td-total { font-weight: 600; color: #111827; }
.text-c { text-align: center; }
.text-r { text-align: right; }

/* ── Table Footer ────────────────────────────────────── */
.sr-table tfoot .tf-row td {
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
    border-top: 2px solid #fed7aa !important;
    background: #fff7ed !important;
}
.tf-grand-label {
    font-weight: 700 !important;
    color: #111827 !important;
    font-size: 11px !important;
}
.tf-grand-val {
    font-size: 14px !important;
    font-weight: 700 !important;
    color: #f97316 !important;
}

.tf-ref td {
    border-top: 1px solid #e5e7eb !important;
    background: #f9fafb !important;
}
.tf-ref .tf-label, .tf-ref .tf-val {
    color: #9ca3af !important;
    font-size: 9px !important;
    font-weight: 400 !important;
}

/* ── Info Banners ────────────────────────────────────── */
.sr-info-banner {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 13px 15px;
    border-radius: 7px;
    border: 1px solid;
}
.banner-success   { background: #fff7ed; border-color: #fed7aa; }
.banner-cancelled { background: #fef2f2; border-color: #fecaca; }
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
.bi-success   { background: #f97316; }
.bi-cancelled { background: #ef4444; }
.banner-body { flex: 1; }
.banner-title {
    font-size: 11px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 3px;
}
.banner-sub {
    font-size: 10px;
    color: #6b7280;
}

/* ── Responsive ─────────────────────────────────────── */
@media (max-width: 900px) {
    .sr-grid { grid-template-columns: 1fr; }
    .status-meta { display: none; }
}
@media (max-width: 600px) {
    .sr-topbar { flex-direction: column; align-items: flex-start; }
    .sr-btn-group { width: 100%; }
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
    if (!confirm('Complete this return? Stock will be added back and credit note will be generated.')) return;
    $.ajax({
        url: '{{ route('admin.sales-returns.complete', $return->_id) }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            }
        },
        error: function(xhr) {
            let message = 'Failed to complete return';
            if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
            showAlert(message, 'error');
        }
    });
}

function cancelReturn() {
    if (!confirm('Are you sure you want to cancel this draft return? This action cannot be undone.')) return;
    $.ajax({
        url: '{{ route('admin.sales-returns.cancel', $return->_id) }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            }
        },
        error: function(xhr) {
            let message = 'Failed to cancel return';
            if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
            showAlert(message, 'error');
        }
    });
}
</script>
@endpush
@endsection
