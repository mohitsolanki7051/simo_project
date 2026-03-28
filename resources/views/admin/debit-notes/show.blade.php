{{-- resources/views/admin/debit-notes/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Debit Note ' . $debitNote->debit_note_number)
@section('header-title', 'Debit Note Details')

@section('content')
@php
    $settings = \App\Models\InvoiceSetting::first();

    $statusMap = [
        'active'   => ['bg' => '#dcfce7', 'color' => '#166534', 'text' => 'Active'],
        'settled'  => ['bg' => '#ede9fe', 'color' => '#5b21b6', 'text' => 'Settled'],
        'partial'  => ['bg' => '#fef3c7', 'color' => '#92400e', 'text' => 'Partial']
    ];

    $ss = $statusMap[$debitNote->status] ?? $statusMap['active'];
@endphp

<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'Inter', -apple-system, sans-serif; background:#f3f4f6; font-size:12px; }

.iv-actions-bar { display:flex; align-items:center; justify-content:space-between; background:white; padding:12px 20px; border-radius:8px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,.1); border:1px solid #e5e7eb; }
.iv-actions-left  { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.iv-actions-right { display:flex; gap:8px; flex-wrap:wrap; }
.iv-back-btn { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; background:#f3f4f6; border:1px solid #d1d5db; border-radius:6px; color:#374151; font-size:12px; text-decoration:none; }
.iv-page-title { font-size:16px; font-weight:600; color:#111827; }
.iv-status-pill { display:inline-flex; align-items:center; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
.iv-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:6px; font-size:12px; font-weight:500; cursor:pointer; border:none; text-decoration:none; }
.iv-btn-primary { background:#ef4444; color:white; }
.iv-btn-primary:hover { background:#dc2626; }
.iv-btn-outline { background:white; border:1px solid #d1d5db; color:#374151; }

.iv-card { background:white; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,.1); border:1px solid #e5e7eb; max-width:1100px; margin:0 auto; }
.iv-header { padding:20px 25px; border-bottom:2px solid #ef4444; }
.iv-company-block { display:flex; align-items:center; gap:15px; }
.iv-logo-placeholder { width:60px; height:60px; background:#ef4444; color:white; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:700; }
.iv-logo-img { height:60px; width:auto; }
.iv-company-details h2 { font-size:20px; font-weight:700; color:#111827; margin-bottom:4px; }
.iv-company-details p { font-size:11px; color:#4b5563; line-height:1.5; }
.iv-company-contact { display:flex; gap:20px; margin-top:6px; font-size:11px; }

.iv-title-row { display:flex; justify-content:space-between; align-items:center; padding:15px 25px 10px; }
.iv-doc-title { font-size:20px; font-weight:700; color:#ef4444; text-transform:uppercase; }
.iv-invoice-number { font-size:16px; font-weight:700; color:#111827; text-align:right; }
.iv-invoice-date { font-size:12px; color:#6b7280; margin-top:2px; }

.iv-party-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; padding:15px 25px; background:#f9fafb; border-top:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb; }
.iv-party-block { background:white; padding:15px; border:1px solid #e5e7eb; border-radius:6px; }
.iv-party-block-title { font-size:12px; font-weight:700; color:#ef4444; margin-bottom:10px; border-bottom:1px solid #e5e7eb; padding-bottom:5px; }

.iv-items-section { padding:15px 25px; }
.iv-table { width:100%; border-collapse:collapse; border:1px solid #e5e7eb; font-size:11px; }
.iv-table thead tr { background:#ef4444; color:white; }
.iv-table thead th { padding:8px 6px; font-weight:600; font-size:10px; text-align:center; border-right:1px solid #f87171; }
.iv-table thead th:last-child { border-right:none; }
.iv-table tbody tr { border-bottom:1px solid #e5e7eb; }
.iv-table tbody td { padding:6px 5px; border-right:1px solid #e5e7eb; text-align:center; font-size:11px; }
.iv-table tbody td:last-child { border-right:none; font-weight:600; }
.iv-table tfoot tr { background:#f3f4f6; font-weight:700; border-top:2px solid #ef4444; }
.iv-table tfoot td { padding:8px 6px; text-align:center; }

.iv-bottom-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; padding:20px 25px; background:#f9fafb; border-top:1px solid #e5e7eb; }
.iv-left-panel, .iv-right-panel { background:white; padding:15px; border:1px solid #e5e7eb; border-radius:6px; }
.iv-panel-title { font-size:12px; font-weight:700; color:#ef4444; margin-bottom:12px; border-bottom:1px solid #e5e7eb; padding-bottom:5px; }
.iv-total-row { display:flex; justify-content:space-between; padding:5px 0; font-size:11px; border-bottom:1px dotted #e5e7eb; }
.iv-total-row.grand { border-top:2px solid #ef4444; border-bottom:none; margin-top:8px; padding-top:8px; font-weight:700; font-size:13px; }
.iv-total-row.grand .iv-total-value { color:#ef4444; }
.iv-footer { padding:12px 25px; text-align:center; border-top:1px solid #e5e7eb; font-size:10px; color:#6b7280; background:#f9fafb; }

#iv-alert-container { position:fixed; top:20px; right:20px; z-index:9999; }
.iv-alert { padding:12px 16px; border-radius:6px; font-size:12px; margin-bottom:8px; }
.iv-alert-success { background:#d1fae5; color:#065f46; border-left:4px solid #059669; }
.iv-alert-error   { background:#fee2e2; color:#991b1b; border-left:4px solid #dc2626; }

@media print {
    body * { visibility:hidden !important; }
    #dnToPrint, #dnToPrint * { visibility:visible !important; }
    #dnToPrint { position:absolute !important; left:0 !important; top:0 !important; width:100% !important; }
    @page { size:A4; margin:0.3in; }
    .no-print { display:none !important; }
    .iv-table thead tr { background:#ef4444 !important; -webkit-print-color-adjust:exact !important; print-color-adjust:exact !important; }
}
</style>

<div id="iv-alert-container"></div>

{{-- Action Bar --}}
<div class="iv-actions-bar no-print">
    <div class="iv-actions-left">
        <a href="{{ route('admin.debit-notes.index') }}" class="iv-back-btn">← Back</a>
        <h2 class="iv-page-title">Debit Note {{ $debitNote->debit_note_number }}</h2>
        <span class="iv-status-pill" style="background:{{ $ss['bg'] }};color:{{ $ss['color'] }};">
            {{ $ss['text'] }}
        </span>
    </div>
    <div class="iv-actions-right">
        <button onclick="printDN()" class="iv-btn iv-btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print
        </button>
        <button onclick="downloadPDF()" class="iv-btn iv-btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download PDF
        </button>
     
    </div>
</div>

{{-- Debit Note Card --}}
<div class="iv-card" id="dnToPrint">

    {{-- Header --}}
    <div class="iv-header">
        <div class="iv-company-block">
            @if($settings && $settings->logo_path)
                <img src="{{ asset('storage/' . $settings->logo_path) }}" class="iv-logo-img" alt="Logo">
            @else
                <div class="iv-logo-placeholder">{{ strtoupper(substr($settings->company_name ?? 'SIM', 0, 2)) }}</div>
            @endif
            <div class="iv-company-details">
                <h2>{{ $settings->company_name ?? 'SIMKO ENTERPRISES' }}</h2>
                <p>{{ $settings->company_address ?? '' }}</p>
                <div class="iv-company-contact">
                    <span>Phone: {{ $settings->company_phone ?? 'N/A' }}</span>
                    <span>Email: {{ $settings->company_email ?? 'N/A' }}</span>
                </div>
                @if($settings?->gstin)
                    <div style="margin-top:5px;font-size:11px;font-weight:500;">GSTIN: {{ $settings->gstin }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Title Row --}}
    <div class="iv-title-row">
        <div class="iv-doc-title">Debit Note</div>
        <div>
            <div class="iv-invoice-number">{{ $debitNote->debit_note_number }}</div>
            <div class="iv-invoice-date">Date: {{ $debitNote->debit_date->format('d/m/Y') }}</div>
            <div class="iv-invoice-date">
                Status: <span style="font-weight:600;color:{{ $ss['color'] }};">{{ $ss['text'] }}</span>
            </div>
        </div>
    </div>

    {{-- Party + Invoice Info --}}
    <div class="iv-party-grid">
        <div class="iv-party-block">
            <div class="iv-party-block-title">Party Details</div>
            @php
                $party = $debitNote->party;
                $partyName = $debitNote->party_type === 'vendor'
                    ? (optional($party)->company_name ?? 'N/A')
                    : (optional($party)->name ?? 'N/A');
            @endphp
            <div style="font-weight:600;margin-bottom:5px;">{{ $partyName }}</div>
            <div style="font-size:11px;color:#4b5563;">Phone: {{ optional($party)->phone ?? 'N/A' }}</div>
            <div style="font-size:11px;color:#4b5563;">Email: {{ optional($party)->email ?? 'N/A' }}</div>
            <div style="font-size:11px;margin-top:5px;">
                <span class="iv-status-pill" style="background:#fef2f2;color:#991b1b;padding:2px 8px;">
                    {{ ucfirst($debitNote->party_type) }}
                </span>
            </div>
            @if(optional($party)->gst_number)
                <div style="font-size:11px;font-weight:600;margin-top:5px;">GSTIN: {{ $party->gst_number }}</div>
            @endif
        </div>
        <div class="iv-party-block">
            <div class="iv-party-block-title">Against Purchase Invoice</div>
            @if($debitNote->purchaseInvoice)
                <div style="font-weight:600;margin-bottom:5px;">{{ $debitNote->purchaseInvoice->invoice_number }}</div>
                <div style="font-size:11px;color:#4b5563;">Date: {{ $debitNote->purchaseInvoice->invoice_date->format('d/m/Y') }}</div>
                <div style="font-size:11px;color:#4b5563;">Grand Total: ₹ {{ number_format($debitNote->purchaseInvoice->grand_total, 2) }}</div>
            @else
                <div style="font-size:11px;color:#6b7280;">Invoice info not available</div>
            @endif
            @if($debitNote->reason)
                <div style="margin-top:8px;font-size:11px;"><strong>Reason:</strong> {{ $debitNote->reason }}</div>
            @endif
        </div>
    </div>

    {{-- Items Table --}}
    <div class="iv-items-section">
        <table class="iv-table">
            <thead>
                <tr>
                    <th width="40">S. No.</th>
                    <th>Product</th>
                    <th width="60">Qty</th>
                    <th width="90">Rate (₹)</th>
                    <th width="60">Tax %</th>
                    <th width="90">Tax Amt (₹)</th>
                    <th width="90">Discount (₹)</th>
                    <th width="100">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($debitNote->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td style="text-align:left;">
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->variant_name)
                            <div style="font-size:9px;color:#6b7280;">{{ $item->variant_name }}</div>
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>₹ {{ number_format($item->price, 2) }}</td>
                    <td>{{ $item->tax_percent }}%</td>
                    <td style="color:#3b82f6;">₹ {{ number_format($item->tax_amount, 2) }}</td>
                    <td style="color:#f59e0b;">₹ {{ number_format($item->discount_amount ?? 0, 2) }}</td>
                    <td>₹ {{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align:right;">Total</td>
                    <td>₹ {{ number_format($debitNote->amount_float, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Bottom --}}
    <div class="iv-bottom-grid">
        <div class="iv-left-panel">
            <div class="iv-panel-title">Purchase Return Details</div>
            @if($debitNote->purchaseReturn)
                <div style="font-size:11px;line-height:1.8;">
                    <div><strong>Return No:</strong> {{ $debitNote->purchaseReturn->return_number }}</div>
                    <div><strong>Return Date:</strong> {{ $debitNote->purchaseReturn->return_date->format('d M Y') }}</div>
                    <div><strong>Return Qty:</strong> {{ $debitNote->purchaseReturn->total_return_qty }}</div>
                    <div><strong>Return Amount:</strong> ₹ {{ number_format($debitNote->purchaseReturn->total_return_amount, 2) }}</div>
                </div>
            @else
                <div style="font-size:11px;color:#6b7280;">Return info not available</div>
            @endif

            @if($settings?->terms_and_conditions)
                <div class="iv-panel-title" style="margin-top:15px;">Terms</div>
                <div style="font-size:11px;color:#4b5563;line-height:1.6;">{{ $settings->terms_and_conditions }}</div>
            @endif
        </div>

        <div class="iv-right-panel">
            <div class="iv-panel-title">Amount Summary</div>
            <div class="iv-total-row">
                <span>Subtotal</span>
                <span>₹ {{ number_format($debitNote->subtotal_float, 2) }}</span>
            </div>
            <div class="iv-total-row">
                <span>Tax Amount</span>
                <span style="color:#3b82f6;">+ ₹ {{ number_format($debitNote->tax_amount_float, 2) }}</span>
            </div>
            @if($debitNote->discount_amount_float > 0)
            <div class="iv-total-row">
                <span>Discount</span>
                <span style="color:#f59e0b;">- ₹ {{ number_format($debitNote->discount_amount_float, 2) }}</span>
            </div>
            @endif
            <div class="iv-total-row grand">
                <span>Debit Note Total</span>
                <span class="iv-total-value">₹ {{ number_format($debitNote->amount_float, 2) }}</span>
            </div>
            <div class="iv-total-row" style="margin-top:8px;">
                <span>Used Amount</span>
                <span style="color:#3b82f6;">₹ {{ number_format($debitNote->used_amount_float, 2) }}</span>
            </div>
            <div class="iv-total-row">
                <span>Remaining</span>
                <span style="color:#10b981;font-weight:700;">₹ {{ number_format($debitNote->remaining_amount_float, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="iv-footer">
        {{ $settings->footer_note ?? 'This is a computer generated debit note' }}
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function showAlert(msg, type = 'success') {
    const c = document.getElementById('iv-alert-container');
    const el = document.createElement('div');
    el.className = `iv-alert iv-alert-${type}`;
    el.textContent = msg;
    c.appendChild(el);
    setTimeout(() => el.remove(), 4000);
}

function printDN() {
    document.title = '{{ $debitNote->debit_note_number }}';
    window.print();
}

function downloadPDF() {
    showAlert('Generating PDF...', 'success');
    html2pdf().set({
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: '{{ $debitNote->debit_note_number }}.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    }).from(document.getElementById('dnToPrint')).save()
    .then(() => showAlert('PDF downloaded!', 'success'))
    .catch(() => showAlert('PDF failed!', 'error'));
}


</script>
@endpush
@endsection
