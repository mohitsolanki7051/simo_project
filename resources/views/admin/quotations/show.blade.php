@extends('layouts.admin')

@section('title', 'Quotation #' . $quotation->quotation_number . ' - Admin Panel')
@section('header-title', 'Quotation #' . $quotation->quotation_number)

@section('content')

@php
    $party = $quotation->party;
    $billingAddress  = $party->addresses->where('type', 'billing')->where('is_default', true)->first();
    $shippingAddress = $party->addresses->where('type', 'shipping')->where('is_default', true)->first();
    $salesman = $party->salesman ?? null;

    $statusColors = [
        'draft'    => ['bg' => '#f3f4f6', 'color' => '#374151', 'text' => 'Draft'],
        'sent'     => ['bg' => '#dbeafe', 'color' => '#1e40af', 'text' => 'Sent'],
        'accepted' => ['bg' => '#d1fae5', 'color' => '#065f46', 'text' => 'Accepted'],
        'rejected' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'text' => 'Rejected'],
        'expired'  => ['bg' => '#fef3c7', 'color' => '#92400e', 'text' => 'Expired'],
    ];
    $sc = $statusColors[$quotation->status] ?? $statusColors['draft'];
    $settings = \App\Models\InvoiceSetting::first();
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
*{ margin:0; padding:0; box-sizing:border-box; }
body{ font-family:'Inter',-apple-system,sans-serif; background:#f3f4f6; font-size:12px; }

/* ── Action Bar ─────────────────────────────────────── */
.qt-actions-bar{
    display:flex; align-items:center; justify-content:space-between;
    background:white; padding:12px 20px; border-radius:8px;
    margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,.1); border:1px solid #e5e7eb;
}
.qt-actions-left{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.qt-actions-right{ display:flex; gap:8px; flex-wrap:wrap; }
.qt-back-btn{
    display:inline-flex; align-items:center; gap:6px; padding:6px 12px;
    background:#f3f4f6; border:1px solid #d1d5db; border-radius:6px;
    color:#374151; font-size:12px; text-decoration:none;
}
.qt-back-btn:hover{ background:#e5e7eb; }
.qt-page-title{ font-size:16px; font-weight:600; color:#111827; }
.qt-status-pill{
    display:inline-flex; align-items:center; padding:4px 10px;
    border-radius:20px; font-size:11px; font-weight:600;
    background:{{ $sc['bg'] }}; color:{{ $sc['color'] }};
}
.qt-btn{
    display:inline-flex; align-items:center; gap:6px; padding:8px 16px;
    border-radius:6px; font-size:12px; font-weight:500; cursor:pointer; border:none; text-decoration:none;
}
.qt-btn-primary { background:#28a745; color:white; }
.qt-btn-primary:hover { background:#218838; }
.qt-btn-outline { background:white; border:1px solid #d1d5db; color:#374151; }
.qt-btn-outline:hover { background:#f3f4f6; }
.qt-btn-danger  { background:#ef4444; color:white; }
.qt-btn-warning { background:#f59e0b; color:white; }

/* ── Card ───────────────────────────────────────────── */
.qt-card{
    background:white; border-radius:8px;
    box-shadow:0 4px 12px rgba(0,0,0,.1); border:1px solid #e5e7eb;
    max-width:1100px; margin:0 auto;
}

/* ── Header ─────────────────────────────────────────── */
.qt-header{
    padding:20px 25px; border-bottom:2px solid #28a745;
    display:flex; justify-content:space-between; align-items:center;
}
.qt-company-block{ display:flex; align-items:center; gap:15px; }
.qt-logo-img{ height:60px; width:auto; }
.qt-logo-placeholder{
    width:60px; height:60px; background:#28a745; color:white;
    border-radius:4px; display:flex; align-items:center; justify-content:center;
    font-size:24px; font-weight:700;
}
.qt-company-details h2{ font-size:20px; font-weight:700; color:#111827; margin-bottom:4px; }
.qt-company-details p{ font-size:11px; color:#4b5563; line-height:1.5; }
.qt-company-contact{ display:flex; gap:15px; margin-top:5px; font-size:11px; color:#6b7280; }

/* ── Title Row ──────────────────────────────────────── */
.qt-title-row{
    display:flex; justify-content:space-between; align-items:center;
    padding:15px 25px; background:#f9fafb; border-bottom:1px solid #e5e7eb;
}
.qt-doc-title{ font-size:20px; font-weight:700; color:#28a745; text-transform:uppercase; }
.qt-number-section{ text-align:right; }
.qt-quotation-number{ font-size:18px; font-weight:700; color:#111827; }
.qt-date-info{ display:flex; gap:15px; margin-top:5px; font-size:12px; color:#6b7280; }
.qt-valid-till{ color:#f59e0b; font-weight:600; }

/* ── Party Grid ─────────────────────────────────────── */
.qt-party-grid{
    display:grid; grid-template-columns:1fr 1fr; gap:20px;
    padding:20px 25px; border-bottom:1px solid #e5e7eb;
}
.qt-party-block{ background:#f9fafb; padding:16px; border-radius:6px; border:1px solid #e5e7eb; }
.qt-party-title{ font-size:12px; font-weight:700; color:#28a745; margin-bottom:10px; border-bottom:1px solid #e5e7eb; padding-bottom:5px; }
.qt-party-name{ font-size:14px; font-weight:600; color:#111827; margin-bottom:8px; }
.qt-party-address{ font-size:11px; color:#4b5563; line-height:1.5; margin-bottom:8px; }
.qt-party-contact{ font-size:11px; color:#6b7280; margin-top:8px; padding-top:8px; border-top:1px dashed #e5e7eb; }
.qt-contact-row{ display:flex; align-items:center; gap:8px; margin-bottom:4px; }
.qt-contact-label{ font-weight:500; color:#4b5563; min-width:45px; }
.qt-party-type{
    display:inline-block; padding:3px 8px; border-radius:12px;
    font-size:10px; font-weight:600; text-transform:uppercase; margin-left:8px;
}
.qt-party-type.customer    { background:#d4edda; color:#155724; }
.qt-party-type.dealer      { background:#cce5ff; color:#004085; }
.qt-party-type.distributor { background:#fff3cd; color:#856404; }
.qt-salesman-info{
    margin-top:12px; padding:8px 12px; background:#e8f5e9; border-radius:4px;
    display:flex; align-items:center; gap:8px; font-size:11px;
}
.qt-salesman-icon{ color:#28a745; font-size:14px; }
.qt-salesman-name{ font-weight:600; color:#2c3e50; }

/* ── Items Table ────────────────────────────────────── */
.qt-items-section{ padding:20px 25px; }
.qt-table{ width:100%; border-collapse:collapse; border:1px solid #e5e7eb; font-size:11px; }
.qt-table thead tr{ background:#28a745; color:white; }
.qt-table thead th{
    padding:10px 8px; font-weight:600; font-size:11px; text-align:center;
    border-right:1px solid #34ce57;
}
.qt-table thead th:first-child { text-align:center; }
.qt-table thead th.th-desc    { text-align:left; padding-left:12px; }
.qt-table thead th:last-child  { border-right:none; }
.qt-table tbody tr{ border-bottom:1px solid #e5e7eb; }
.qt-table tbody tr:hover{ background:#f9fafb; }
.qt-table tbody td{
    padding:10px 8px; border-right:1px solid #e5e7eb;
    text-align:center; font-size:11px; vertical-align:middle;
}
.qt-table tbody td:first-child{ font-weight:600; background:#f9fafb; color:#6b7280; }
.qt-table tbody td:last-child{ border-right:none; font-weight:600; color:#166534; background:#f0fdf4; }
.qt-product-name{ text-align:left !important; padding-left:12px !important; }
.qt-variant-name{ font-size:10px; color:#6b7280; margin-top:2px; }
.qt-table tfoot tr{ background:#f3f4f6; }
.qt-table tfoot td{
    padding:10px 8px; text-align:center;
    border-top:2px solid #28a745; font-weight:700;
}

/* ── Bottom Grid: Notes LEFT, Summary RIGHT ─────────── */
.qt-bottom-grid{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap:20px;
    padding:20px 25px;
    background:#f9fafb;
    border-top:1px solid #e5e7eb;
}

.qt-notes-panel, .qt-summary-panel{
    background:white; padding:16px;
    border:1px solid #e5e7eb; border-radius:6px;
}

.qt-panel-title{
    font-size:13px; font-weight:700; color:#28a745;
    margin-bottom:15px; border-bottom:1px solid #e5e7eb;
    padding-bottom:8px; display:flex; align-items:center; gap:6px;
}
.qt-panel-icon{ font-size:14px; }

/* Notes */
.qt-notes-content{
    font-size:12px; color:#4b5563; line-height:1.6;
    min-height:80px; padding:8px; background:#f9fafb;
    border-radius:4px; border:1px solid #e5e7eb;
}
.qt-terms-content{
    margin-top:15px; padding:10px; background:#fff3cd;
    border-radius:4px; border:1px solid #ffeeba;
    color:#856404; font-size:11px; line-height:1.5;
}

/* Summary */
.qt-total-row{
    display:flex; justify-content:space-between;
    padding:7px 0; font-size:12px;
    border-bottom:1px dotted #e5e7eb;
}
.qt-total-row:last-child{ border-bottom:none; }
.qt-total-label{ color:#4b5563; }
.qt-total-value{ font-weight:600; color:#111827; }
.qt-grand-total{
    margin-top:12px; padding-top:12px;
    border-top:2px solid #28a745; font-size:14px;
}
.qt-grand-total .qt-total-label{ font-weight:700; color:#111827; }
.qt-grand-total .qt-total-value{ font-size:16px; font-weight:700; color:#28a745; }

/* ── Footer ─────────────────────────────────────────── */
.qt-footer{
    padding:12px 25px; text-align:center; border-top:1px solid #e5e7eb;
    font-size:10px; color:#6b7280; background:#f9fafb;
}

/* ── Misc ───────────────────────────────────────────── */
.qt-expired-warning{
    background:#fef3c7; border-left:4px solid #f59e0b; padding:12px 16px;
    border-radius:6px; margin-bottom:20px; color:#92400e; border:1px solid #fde68a;
}
#qt-alert-container{ position:fixed; top:20px; right:20px; z-index:9999; }
.qt-alert{ padding:12px 16px; border-radius:6px; font-size:12px; margin-bottom:8px; animation:slideIn .2s ease; box-shadow:0 4px 12px rgba(0,0,0,.15); }
@keyframes slideIn{ from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
.qt-alert-success { background:#d1fae5; color:#065f46; border-left:4px solid #059669; }
.qt-alert-error   { background:#fee2e2; color:#991b1b; border-left:4px solid #dc2626; }
.qt-alert-warning { background:#fef3c7; color:#92400e; border-left:4px solid #f59e0b; }

@media(max-width:768px){
    .qt-party-grid, .qt-bottom-grid{ grid-template-columns:1fr; }
    .qt-actions-bar{ flex-direction:column; gap:10px; align-items:flex-start; }
}

@media print{
    body * { visibility:hidden; }
    #quotationToPrint, #quotationToPrint * { visibility:visible; }
    #quotationToPrint { position:absolute; left:0; top:0; width:100%; }
    .qt-actions-bar, .no-print, #qt-alert-container { display:none !important; }
    .qt-table thead tr{
        background:#28a745 !important;
        -webkit-print-color-adjust:exact !important;
        print-color-adjust:exact !important;
    }
}
</style>

<div class="qt-wrap">
    <div id="qt-alert-container"></div>

    <!-- Action Bar - Screen Only -->
    <div class="qt-actions-bar no-print">
        <div class="qt-actions-left">
            <a href="{{ route('admin.quotations.index') }}" class="qt-back-btn">← Back to Quotations</a>
            <h2 class="qt-page-title">Quotation #{{ $quotation->quotation_number }}</h2>
            <span class="qt-status-pill">{{ $sc['text'] }}</span>
        </div>
        <div class="qt-actions-right">
            @if($quotation->status === 'draft')
                <a href="{{ route('admin.quotations.edit', $quotation->id) }}" class="qt-btn qt-btn-outline">Edit</a>
                <button onclick="updateStatus('sent')" class="qt-btn qt-btn-primary">Mark as Sent</button>
            @endif

            {{-- Show Convert button only if quotation is not already accepted/expired/rejected --}}
            @if($quotation->status !== 'accepted' && $quotation->status !== 'expired' && $quotation->status !== 'rejected')
                <button onclick="convertToInvoice()" class="qt-btn qt-btn-primary">Convert to Invoice</button>
            @endif

            <button onclick="sendWhatsApp()" class="qt-btn qt-btn-warning">Send WhatsApp</button>
            <button onclick="printQuotation()" class="qt-btn qt-btn-outline">Print</button>
            <a href="{{ route('admin.quotations.pdf', $quotation->id) }}" target="_blank" class="qt-btn qt-btn-primary">PDF</a>

            @if($quotation->status === 'draft')
                <button onclick="deleteQuotation()" class="qt-btn qt-btn-danger">Delete</button>
            @endif
        </div>
    </div>

    @if($quotation->isExpired())
    <div class="qt-expired-warning no-print">
        <strong>⚠️ EXPIRED:</strong> This quotation expired on {{ $quotation->valid_till->format('d/m/Y') }}.
    </div>
    @endif

    {{-- ── Main Quotation Card ── --}}
    <div class="qt-card" id="quotationToPrint">

        {{-- Company Header --}}
        <div class="qt-header">
            <div class="qt-company-block">
                @if($settings && $settings->logo_path)
                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="qt-logo-img">
                @else
                    <div class="qt-logo-placeholder">
                        {{ strtoupper(substr($settings->company_name ?? 'SIM', 0, 2)) }}
                    </div>
                @endif
                <div class="qt-company-details">
                    <h2>{{ $settings->company_name ?? 'SIMKO ENTERPRISES' }}</h2>
                    <p>{{ $settings->company_address ?? 'Company Address' }}</p>
                    <div class="qt-company-contact">
                        <span>📞 {{ $settings->company_phone ?? 'N/A' }}</span>
                        <span>✉️ {{ $settings->company_email ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quotation Title & Number --}}
        <div class="qt-title-row">
            <div class="qt-doc-title">QUOTATION / ESTIMATE</div>
            <div class="qt-number-section">
                <div class="qt-quotation-number">{{ $quotation->quotation_number }}</div>
                <div class="qt-date-info">
                    <span>Date: {{ $quotation->quotation_date->format('d/m/Y') }}</span>
                    @if($quotation->valid_till)
                    <span class="qt-valid-till">Valid Till: {{ $quotation->valid_till->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Party Info --}}
        <div class="qt-party-grid">
            <div class="qt-party-block">
                <div class="qt-party-title">📋 BILL TO</div>
                <div class="qt-party-name">
                    {{ $party->name ?? 'N/A' }}
                    <span class="qt-party-type {{ $party->party_type ?? 'customer' }}">
                        {{ strtoupper($party->party_type ?? 'CUSTOMER') }}
                    </span>
                </div>
                @if($billingAddress)
                <div class="qt-party-address">
                    {{ $billingAddress->address }}<br>
                    @if($billingAddress->city || $billingAddress->state)
                        {{ $billingAddress->city ?? '' }} {{ $billingAddress->state ?? '' }} - {{ $billingAddress->pincode ?? '' }}<br>
                    @endif
                    {{ $billingAddress->country ?? 'India' }}
                </div>
                @else
                <div class="qt-party-address">Address not available</div>
                @endif
                <div class="qt-party-contact">
                    @if($party->phone)
                    <div class="qt-contact-row"><span class="qt-contact-label">📞</span><span>{{ $party->phone }}</span></div>
                    @endif
                    @if($party->email)
                    <div class="qt-contact-row"><span class="qt-contact-label">✉️</span><span>{{ $party->email }}</span></div>
                    @endif
                </div>
                @if($salesman)
                <div class="qt-salesman-info">
                    <span class="qt-salesman-icon">👤</span>
                    <span>Sales Executive: <span class="qt-salesman-name">{{ $salesman->name }}</span></span>
                </div>
                @endif
            </div>

            <div class="qt-party-block">
                <div class="qt-party-title">🚚 SHIP TO</div>
                @if($shippingAddress)
                    <div class="qt-party-name">{{ $party->name ?? 'N/A' }}</div>
                    <div class="qt-party-address">
                        {{ $shippingAddress->address }}<br>
                        @if($shippingAddress->city || $shippingAddress->state)
                            {{ $shippingAddress->city ?? '' }} {{ $shippingAddress->state ?? '' }} - {{ $shippingAddress->pincode ?? '' }}<br>
                        @endif
                        {{ $shippingAddress->country ?? 'India' }}
                    </div>
                @elseif($billingAddress)
                    <div class="qt-party-address"><em>Same as billing address</em></div>
                @else
                    <div class="qt-party-address">Shipping address not available</div>
                @endif
            </div>
        </div>

        {{-- Items Table --}}
        <div class="qt-items-section">
            <table class="qt-table">
                <thead>
                    <tr>
                        <th width="30">#</th>
                        <th class="th-desc">Product Description</th>
                        <th width="50">Qty</th>
                        <th width="50">Unit</th>
                        <th width="80">MRP (₹)</th>
                        <th width="60">Disc %</th>
                        <th width="80">Price (₹)</th>
                        <th width="90">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmount = 0; @endphp
                    @foreach($quotation->items as $idx => $item)
                    @php
                        $lineTotal = $item->quantity * $item->price;
                        $totalAmount += $lineTotal;
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="qt-product-name">
                            <strong>{{ $item->product_name }}</strong>
                            @if($item->variant_name)
                                <div class="qt-variant-name">({{ $item->variant_name }})</div>
                            @endif
                            @if($item->sku)
                                <div style="font-size:9px;color:#6b7280;">SKU: {{ $item->sku }}</div>
                            @endif
                        </td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ $item->unit ?? 'PCS' }}</td>
                        <td>₹ {{ number_format($item->mrp_price, 2) }}</td>
                        <td>{{ $item->discount > 0 ? number_format($item->discount, 1).'%' : '—' }}</td>
                        <td>₹ {{ number_format($item->price, 2) }}</td>
                        <td>₹ {{ number_format($lineTotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" style="text-align:right;padding-right:20px;">Subtotal</td>
                        <td>₹ {{ number_format($totalAmount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ── Bottom: Notes LEFT | Summary RIGHT ── --}}
        <div class="qt-bottom-grid">

            {{-- LEFT: Notes & Terms --}}
            <div class="qt-notes-panel">
                <div class="qt-panel-title">
                    <span class="qt-panel-icon">📝</span>
                    Notes & Terms
                </div>
                @if($quotation->notes)
                <div class="qt-notes-content">{{ $quotation->notes }}</div>
                @else
                <div class="qt-notes-content" style="color:#9ca3af;font-style:italic;">
                    No notes added for this quotation.
                </div>
                @endif

                @if($settings && $settings->terms_and_conditions)
                <div class="qt-terms-content">
                    <strong>Terms & Conditions:</strong><br>
                    {{ $settings->terms_and_conditions }}
                </div>
                @else
                <div class="qt-terms-content">
                    <strong>Terms & Conditions:</strong><br>
                    1. This quotation is valid for 30 days from the date mentioned.<br>
                    2. Prices are subject to change without prior notice.<br>
                    3. Taxes and duties will be applicable as per prevailing rates.
                </div>
                @endif
            </div>

            {{-- RIGHT: Amount Summary --}}
            <div class="qt-summary-panel">
                <div class="qt-panel-title">
                    <span class="qt-panel-icon">💰</span>
                    Amount Summary
                </div>

                <div class="qt-total-row">
                    <span class="qt-total-label">Subtotal</span>
                    <span class="qt-total-value">₹ {{ number_format($quotation->subtotal, 2) }}</span>
                </div>
                <div class="qt-total-row">
                    <span class="qt-total-label">Discount</span>
                    <span class="qt-total-value">- ₹ {{ number_format($quotation->discount_amount, 2) }}</span>
                </div>
                @if($quotation->extra_discount > 0)
                <div class="qt-total-row">
                    <span class="qt-total-label">
                        Extra Discount
                        @if($quotation->extra_discount_type === 'percent')
                            ({{ number_format($quotation->extra_discount, 1) }}%)
                        @endif
                    </span>
                    <span class="qt-total-value">- ₹ {{ number_format($quotation->extra_discount, 2) }}</span>
                </div>
                @endif
                @if($quotation->extra_charge > 0)
                <div class="qt-total-row">
                    <span class="qt-total-label">{{ $quotation->charge_name ?? 'Other Charges' }}</span>
                    <span class="qt-total-value">+ ₹ {{ number_format($quotation->extra_charge, 2) }}</span>
                </div>
                @endif
                @if(isset($quotation->round_off) && $quotation->round_off != 0)
                <div class="qt-total-row">
                    <span class="qt-total-label">Round Off</span>
                    <span class="qt-total-value">{{ $quotation->round_off > 0 ? '+' : '' }} ₹ {{ number_format($quotation->round_off, 2) }}</span>
                </div>
                @endif

                <div class="qt-total-row qt-grand-total">
                    <span class="qt-total-label">Grand Total</span>
                    <span class="qt-total-value">₹ {{ number_format($quotation->grand_total, 2) }}</span>
                </div>

                <div style="margin-top:15px;font-size:10px;color:#6b7280;text-align:center;padding:8px;background:#f9fafb;border-radius:4px;border:1px dashed #e5e7eb;">
                    * This is a quotation only, not an invoice *
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="qt-footer">
            This is a computer generated quotation — valid until
            {{ $quotation->valid_till ? $quotation->valid_till->format('d/m/Y') : 'mentioned date' }}
        </div>

    </div>{{-- end qt-card --}}
</div>

@push('scripts')
<script>
function showAlert(message, type = 'success') {
    const c = document.getElementById('qt-alert-container');
    const a = document.createElement('div');
    a.className = `qt-alert qt-alert-${type}`;
    a.innerHTML = message;
    c.appendChild(a);
    setTimeout(() => a.remove(), 4000);
}

function printQuotation() {
    document.title = '{{ $quotation->quotation_number }}';
    window.print();
}

function sendWhatsApp() {
    const btn = event.target.closest('button');
    btn.disabled = true; btn.innerHTML = 'Sending...';
    fetch('{{ route("admin.quotations.send-whatsapp", $quotation->id) }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false; btn.innerHTML = 'Send WhatsApp';
        if (data.success) window.open(data.whatsapp_url, '_blank');
        else showAlert(data.message, 'error');
    })
    .catch(() => { btn.disabled = false; btn.innerHTML = 'Send WhatsApp'; showAlert('Failed to send WhatsApp', 'error'); });
}

function updateStatus(status) {
    if (!confirm(`Mark quotation as ${status}?`)) return;
    const btn = event.target.closest('button');
    const orig = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = 'Updating...';
    fetch('{{ route("admin.quotations.update-status", $quotation->id) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        body: JSON.stringify({ status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { showAlert(`Quotation marked as ${status}!`, 'success'); setTimeout(() => location.reload(), 1500); }
        else { showAlert(data.message, 'error'); btn.disabled = false; btn.innerHTML = orig; }
    })
    .catch(() => { showAlert('Failed to update status', 'error'); btn.disabled = false; btn.innerHTML = orig; });
}

function convertToInvoice() {
    if (!confirm('Convert this quotation to sales invoice? This will create a draft invoice with GST calculations based on product tax rates and party state.')) return;

    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Converting...';

    fetch('{{ route("admin.quotations.convert-to-invoice", $quotation->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert('Quotation converted to invoice successfully!', 'success');
            setTimeout(() => window.location.href = '/admin/sales/' + data.invoice_id, 2000);
        } else {
            showAlert(data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(() => {
        showAlert('Failed to convert quotation', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function deleteQuotation() {
    if (!confirm('Delete this quotation? This cannot be undone.')) return;
    const btn = event.target.closest('button');
    const orig = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = 'Deleting...';
    fetch('{{ route("admin.quotations.destroy", $quotation->id) }}', {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { showAlert('Deleted!', 'success'); setTimeout(() => window.location.href = '{{ route("admin.quotations.index") }}', 1500); }
        else { showAlert(data.message, 'error'); btn.disabled = false; btn.innerHTML = orig; }
    })
    .catch(() => { showAlert('Failed to delete', 'error'); btn.disabled = false; btn.innerHTML = orig; });
}
</script>
@endpush
@endsection
