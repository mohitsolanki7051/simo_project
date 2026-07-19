@extends('layouts.admin')

@section('title', 'Purchase Invoice #' . $invoice->invoice_number)
@section('header-title', 'Purchase Invoice Details')

@section('content')

@php
    if ($invoice->invoice_type === 'cash') {
        $settings = \App\Models\CashMemoInvoiceSetting::first();
    } else {
        $settings = \App\Models\InvoiceSetting::first();
    }

    $partyType  = $partyInfo['type'];
    $partyModel = $partyInfo['model'];

    if ($partyType === 'vendor') {
        $partyName  = $partyModel?->company_name ?? 'N/A';
        $partyPhone = $partyModel?->phone;
        $partyEmail = $partyModel?->email;
        $partyGst   = $partyModel?->gst_number;
    } else {
        $partyName  = $partyModel?->name ?? 'N/A';
        $partyPhone = $partyModel?->phone;
        $partyEmail = $partyModel?->email;
        $partyGst   = $partyModel?->gst_number;
    }

    $paymentStatusColors = [
        'paid'    => ['bg' => '#d1fae5', 'text' => '#065f46', 'border' => '#6ee7b7'],
        'unpaid'  => ['bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#fca5a5'],
        'partial' => ['bg' => '#fef3c7', 'text' => '#92400e', 'border' => '#fcd34d'],
    ];
    $ps = $paymentStatusColors[$invoice->payment_status] ?? $paymentStatusColors['unpaid'];

    $invoiceStatusColors = [
        'draft'     => ['bg' => '#f3f4f6', 'color' => '#374151', 'text' => 'Draft'],
        'confirmed' => ['bg' => '#dbeafe', 'color' => '#1e40af', 'text' => 'Confirmed'],
        'completed' => ['bg' => '#d1fae5', 'color' => '#065f46', 'text' => 'Completed'],
        'cancelled' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'text' => 'Cancelled'],
    ];
    $is = $invoiceStatusColors[$invoice->status] ?? $invoiceStatusColors['draft'];

    $showGST = $invoice->invoice_type !== 'cash';
    $totalQuantity = $invoice->items->sum('quantity');

    function piNumberToWords($num) {
        $num = (int)$num;
        $ones = [0=>'Zero',1=>'One',2=>'Two',3=>'Three',4=>'Four',5=>'Five',6=>'Six',7=>'Seven',8=>'Eight',9=>'Nine',10=>'Ten',11=>'Eleven',12=>'Twelve',13=>'Thirteen',14=>'Fourteen',15=>'Fifteen',16=>'Sixteen',17=>'Seventeen',18=>'Eighteen',19=>'Nineteen'];
        $tens = [2=>'Twenty',3=>'Thirty',4=>'Forty',5=>'Fifty',6=>'Sixty',7=>'Seventy',8=>'Eighty',9=>'Ninety'];
        if ($num == 0) return 'Zero';
        $words = [];
        if ($num >= 10000000) { $c = floor($num/10000000); $words[] = piConvertToWords($c,$ones,$tens).' Crore'; $num %= 10000000; }
        if ($num >= 100000)   { $l = floor($num/100000);   $words[] = piConvertToWords($l,$ones,$tens).' Lakh';  $num %= 100000; }
        if ($num >= 1000)     { $t = floor($num/1000);     $words[] = piConvertToWords($t,$ones,$tens).' Thousand'; $num %= 1000; }
        if ($num >= 100)      { $h = floor($num/100);      $words[] = $ones[$h].' Hundred'; $num %= 100; }
        if ($num > 0) {
            if ($num < 20) { $words[] = $ones[$num]; }
            else { $ten=floor($num/10); $one=$num%10; $words[] = $one>0 ? $tens[$ten].' '.$ones[$one] : $tens[$ten]; }
        }
        return implode(' ', $words);
    }
    function piConvertToWords($num, $ones, $tens) {
        if ($num < 20) return $ones[$num];
        $ten=floor($num/10); $one=$num%10;
        return $one>0 ? $tens[$ten].' '.$ones[$one] : $tens[$ten];
    }
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',-apple-system,sans-serif; background:#f3f4f6; font-size:12px; }

    /* ── Action Bar ── */
    .iv-actions-bar {
        display:flex; align-items:center; justify-content:space-between;
        background:white; padding:12px 20px; border-radius:8px;
        margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,.1);
        border:1px solid #e5e7eb; flex-wrap:wrap; gap:10px;
    }
    .iv-actions-left  { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .iv-actions-right { display:flex; gap:8px; flex-wrap:wrap; }

    .iv-back-btn {
        display:inline-flex; align-items:center; gap:6px; padding:6px 12px;
        background:#f3f4f6; border:1px solid #d1d5db; border-radius:6px;
        color:#374151; font-size:12px; text-decoration:none;
    }
    .iv-page-title { font-size:16px; font-weight:600; color:#111827; }

    .iv-status-pill {
        display:inline-flex; align-items:center; padding:4px 10px;
        border-radius:20px; font-size:11px; font-weight:600;
    }

    .iv-btn {
        display:inline-flex; align-items:center; gap:6px; padding:8px 16px;
        border-radius:6px; font-size:12px; font-weight:500; cursor:pointer;
        border:none; text-decoration:none; transition:all .15s;
    }
    .iv-btn:hover { transform:translateY(-1px); }
    .iv-btn-primary  { background:#f97316; color:white; }
    .iv-btn-primary:hover { background:#ea580c; }
    .iv-btn-outline  { background:white; border:1px solid #d1d5db; color:#374151; }
    .iv-btn-outline:hover { background:#f9fafb; }
    .iv-btn-warning  { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
    .iv-btn-warning:hover { background:#fde68a; }
    .iv-btn-danger   { background:#ef4444; color:white; }
    .iv-btn-danger:hover  { background:#dc2626; }

    .iv-draft-warning {
        background:#fffbeb; border-left:4px solid #f59e0b; padding:12px 16px;
        border-radius:6px; margin-bottom:20px; color:#92400e;
        border:1px solid #fde68a;
    }

    /* ── Invoice Card ── */
    .iv-card {
        background:white; border-radius:8px;
        box-shadow:0 4px 12px rgba(0,0,0,.1);
        border:1px solid #e5e7eb; max-width:1100px; margin:0 auto;
    }

    /* ── Header ── */
    .iv-header { padding:20px 25px; border-bottom:2px solid #f97316; }
    .iv-company-block { display:flex; align-items:center; gap:15px; }
    .iv-logo-img { height:60px; width:auto; }
    .iv-logo-placeholder {
        width:60px; height:60px; background:#f97316; color:white;
        border-radius:4px; display:flex; align-items:center;
        justify-content:center; font-size:24px; font-weight:700;
    }
    .iv-company-details h2 { font-size:20px; font-weight:700; color:#111827; margin-bottom:4px; }
    .iv-company-details p  { font-size:11px; color:#4b5563; line-height:1.5; }
    .iv-company-contact    { display:flex; gap:20px; margin-top:6px; font-size:11px; }

    /* ── Cancelled Stamp ── */
    .iv-cancelled-stamp {
        position:absolute; top:50%; left:50%;
        transform:translate(-50%,-50%) rotate(-30deg);
        font-size:80px; font-weight:900;
        color:rgba(220,38,38,.18);
        border:8px solid rgba(220,38,38,.18);
        border-radius:12px; padding:10px 30px;
        pointer-events:none; white-space:nowrap; z-index:10;
        letter-spacing:8px; font-family:'Inter',sans-serif; text-transform:uppercase;
    }

    /* ── Title Row ── */
    .iv-title-row {
        display:flex; justify-content:space-between; align-items:center;
        padding:15px 25px 10px;
    }
    .iv-doc-title      { font-size:20px; font-weight:700; color:#f97316; text-transform:uppercase; }
    .iv-invoice-number { font-size:16px; font-weight:700; color:#111827; text-align:right; }
    .iv-invoice-date   { font-size:12px; color:#6b7280; margin-top:2px; }

    /* ── Party Grid ── */
    .iv-party-grid {
        display:grid; grid-template-columns:1fr 1fr; gap:20px;
        padding:15px 25px; background:#f9fafb;
        border-top:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb;
    }
    .iv-party-block { background:white; padding:15px; border:1px solid #e5e7eb; border-radius:6px; }
    .iv-party-block-title {
        font-size:12px; font-weight:700; color:#f97316; margin-bottom:10px;
        border-bottom:1px solid #e5e7eb; padding-bottom:5px;
    }

    /* ── Items Table ── */
    .iv-items-section { padding:15px 25px; }
    .iv-table { width:100%; border-collapse:collapse; border:1px solid #e5e7eb; font-size:11px; }
    .iv-table thead tr { background:#f97316; color:white; }
    .iv-table thead th {
        padding:8px 6px; font-weight:600; font-size:10px; text-align:center;
        border-right:1px solid #fb923c;
    }
    .iv-table thead th:last-child { border-right:none; }
    .iv-table tbody tr { border-bottom:1px solid #e5e7eb; }
    .iv-table tbody td {
        padding:6px 5px; border-right:1px solid #e5e7eb;
        text-align:center; font-size:9px;
    }
    .iv-table tbody td:last-child { border-right:none; font-weight:600; }
    .iv-table tfoot tr { background:#f3f4f6; font-weight:700; border-top:2px solid #f97316; }
    .iv-table tfoot td { padding:8px 6px; text-align:center; }

    /* ── Bottom Grid ── */
    .iv-bottom-grid {
        display:grid; grid-template-columns:1fr 1fr; gap:20px;
        padding:20px 25px; background:#f9fafb;
        border-top:1px solid #e5e7eb;
    }
    .iv-left-panel, .iv-right-panel {
        background:white; padding:15px;
        border:1px solid #e5e7eb; border-radius:6px;
    }
    .iv-panel-title {
        font-size:12px; font-weight:700; color:#f97316;
        margin-bottom:12px; border-bottom:1px solid #e5e7eb; padding-bottom:5px;
    }

    /* ── Bank Details ── */
    .iv-bank-details { font-size:11px; }
    .iv-bank-row     { display:flex; margin-bottom:5px; }
    .iv-bank-label   { width:90px; color:#6b7280; }
    .iv-bank-value   { font-weight:500; color:#111827; }

    /* ── Summary ── */
    .iv-total-row {
        display:flex; justify-content:space-between;
        padding:5px 0; font-size:11px;
        border-bottom:1px dotted #e5e7eb;
    }
    .iv-total-row.grand {
        border-top:2px solid #f97316; border-bottom:none;
        margin-top:8px; padding-top:8px;
        font-weight:700; font-size:13px;
    }
    .iv-total-row.grand .iv-total-value { color:#f97316; }

    /* ── Amount Words ── */
    .iv-amount-words {
        margin-top:15px; padding-top:15px;
        border-top:1px dashed #e5e7eb;
    }
    .iv-words-label { font-size:11px; color:#6b7280; margin-bottom:4px; }
    .iv-words-value { font-size:12px; font-weight:600; color:#111827; text-transform:uppercase; }

    /* ── Ledger Card ── */
    .iv-ledger-card {
        margin:0 25px 20px; padding:15px;
        background:#fff7ed; border:1px solid #fed7aa; border-radius:6px;
    }
    .iv-ledger-title {
        font-size:12px; font-weight:700; color:#f97316;
        margin-bottom:10px; padding-bottom:5px; border-bottom:1px solid #fde68a;
        display:flex; align-items:center; gap:6px;
    }
    .iv-ledger-row {
        display:flex; justify-content:space-between;
        padding:4px 0; font-size:11px;
    }
    .iv-ledger-row span:first-child { color:#92400e; }
    .iv-ledger-divider { height:1px; background:#fde68a; margin:8px 0; }
    .iv-ledger-total   { font-weight:700; font-size:12px; }
    .iv-ledger-pos     { color:#dc2626; font-weight:700; }
    .iv-ledger-neg     { color:#059669; font-weight:700; }
    .iv-ledger-note {
        margin-top:8px; font-size:9.5px; color:#92400e;
        font-style:italic; line-height:1.5;
    }

    /* ── Footer ── */
    .iv-footer {
        padding:12px 25px; text-align:center; border-top:1px solid #e5e7eb;
        font-size:10px; color:#6b7280; background:#f9fafb;
    }

    /* ── Payment Badge ── */
    .iv-payment-badge {
        display:inline-block; padding:4px 12px; border-radius:4px;
        font-size:11px; font-weight:600;
    }

    /* ── Alerts ── */
    #iv-alert-container {
        position:fixed; top:20px; right:20px; z-index:9999;
    }
    .iv-alert {
        padding:12px 16px; border-radius:6px; font-size:12px;
        margin-bottom:8px; animation:slideIn .2s ease;
        min-width:220px;
    }
    @keyframes slideIn {
        from{transform:translateX(100%);opacity:0}
        to{transform:translateX(0);opacity:1}
    }
    .iv-alert-success { background:#d1fae5; color:#065f46; border-left:4px solid #059669; }
    .iv-alert-error   { background:#fee2e2; color:#991b1b; border-left:4px solid #dc2626; }
    .iv-alert-info    { background:#dbeafe; color:#1e40af; border-left:4px solid #3b82f6; }

    /* ── Print ── */
    @media print {
        body * { visibility:hidden !important; }
        #invoiceToPrint, #invoiceToPrint * { visibility:visible !important; }
        #invoiceToPrint {
            position:absolute !important; left:0 !important; top:0 !important; width:100% !important;
        }
        @page { size:A4; margin:0.3in; }
        .iv-actions-bar, .iv-draft-warning, #iv-alert-container, .no-print { display:none !important; }
        .iv-table thead tr {
            background:#f97316 !important;
            -webkit-print-color-adjust:exact !important;
            print-color-adjust:exact !important;
        }
    }
</style>

<div class="iv-wrap">
    <div id="iv-alert-container"></div>

    {{-- ── Action Bar ── --}}
    <div class="iv-actions-bar no-print">
        <div class="iv-actions-left">
            <a href="{{ route('admin.purchases.index') }}" class="iv-back-btn">← Back</a>
            <h2 class="iv-page-title">
                {{ $invoice->invoice_type === 'cash' ? 'Cash Memo' : 'Purchase Invoice' }}
                {{ $invoice->invoice_number }}
            </h2>
            <span class="iv-status-pill" style="background:{{ $ps['bg'] }};color:{{ $ps['text'] }};">
                {{ ucfirst($invoice->payment_status) }}
            </span>
            <span class="iv-status-pill" style="background:{{ $is['bg'] }};color:{{ $is['color'] }};">
                {{ $is['text'] }}
            </span>
        </div>
        <div class="iv-actions-right">
            @if($invoice->status === 'draft')
                <button onclick="generateInvoice()" class="iv-btn iv-btn-primary">
                    ✓ Generate
                </button>
                <a href="{{ route('admin.purchases.edit', $invoice->id) }}" class="iv-btn iv-btn-warning">
                    ✏️ Edit
                </a>
                <button onclick="deleteInvoice()" class="iv-btn iv-btn-danger">
                    🗑 Delete
                </button>
            @else
                @if(auth()->guard('admin')->check())
                    <a href="{{ route('admin.purchases.edit', $invoice->id) }}" class="iv-btn iv-btn-warning">
                        ✏️ Edit
                    </a>
                @endif
            @endif
            @if($invoice->status === 'confirmed' && $invoice->payment_status === 'unpaid')
                <button onclick="cancelInvoice()" class="iv-btn iv-btn-danger">
                    🚫 Cancel
                </button>
            @endif
            <button onclick="printInvoice()" class="iv-btn iv-btn-outline">🖨 Print</button>
            <button onclick="downloadPDF()" class="iv-btn iv-btn-primary">📄 PDF</button>
        </div>
    </div>

    @if($invoice->status === 'draft')
    <div class="iv-draft-warning no-print">
        <strong>DRAFT:</strong> Stock has NOT been added to warehouse. Click "Generate" to confirm.
    </div>
    @endif

    {{-- ── Main Invoice Card ── --}}
    <div class="iv-card" id="invoiceToPrint">

        {{-- Header --}}
        <div class="iv-header">
            <div class="iv-company-block">
                @if($settings && $settings->logo_path)
                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="iv-logo-img">
                @else
                    <div class="iv-logo-placeholder">
                        {{ strtoupper(substr($settings->company_name ?? 'SIM', 0, 2)) }}
                    </div>
                @endif
                <div class="iv-company-details">
                    <h2>{{ $settings->company_name ?? 'SIMKO ENTERPRISES' }}</h2>
                    <p>{{ $settings->company_address ?? 'Company Address' }}</p>
                    <div class="iv-company-contact">
                        <span>Phone: {{ $settings->company_phone ?? 'N/A' }}</span>
                        <span>Email: {{ $settings->company_email ?? 'N/A' }}</span>
                    </div>
                    @if($showGST && ($settings?->gstin || $settings?->pan))
                    <div style="margin-top:5px;font-size:11px;font-weight:500;">
                        @if($settings->gstin) GSTIN: {{ $settings->gstin }} @endif
                        @if($settings->pan) | PAN: {{ $settings->pan }} @endif
                    </div>
                    @endif
                    @if($invoice->purchaseExecutive)
                    <div style="margin-top:8px;font-size:11px;background:#f3f4f6;padding:4px 8px;border-radius:4px;display:inline-block;">
                        <strong>Purchase Executive:</strong> {{ $invoice->purchaseExecutive->name }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Title Row --}}
        <div class="iv-title-row">
            <div class="iv-doc-title">
                {{ $invoice->invoice_type === 'cash' ? 'PURCHASE MEMO' : 'PURCHASE INVOICE' }}
            </div>
            <div>
                <div class="iv-invoice-number">{{ $invoice->invoice_number }}</div>
                <div class="iv-invoice-date">Date: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</div>
                @if($invoice->due_date)
                <div class="iv-invoice-date">Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</div>
                @endif
                @if($invoice->po_number)
                <div class="iv-invoice-date">PO: {{ $invoice->po_number }}</div>
                @endif
            </div>
        </div>

        {{-- Party Grid --}}
        <div class="iv-party-grid">
            {{-- Bill From --}}
            <div class="iv-party-block">
                <div class="iv-party-block-title">Bill From</div>
                <div style="font-weight:600;margin-bottom:5px;">
                    {{ $partyName }}
                    <span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:9px;font-weight:700;margin-left:6px;
                        background:{{ $partyType==='vendor' ? '#e0f2fe' : ($partyType==='dealer' ? '#fef3c7' : '#d1fae5') }};
                        color:{{ $partyType==='vendor' ? '#0369a1' : ($partyType==='dealer' ? '#92400e' : '#065f46') }};">
                        {{ ucfirst($partyType) }}
                    </span>
                </div>
                <div style="font-size:11px;color:#4b5563;margin-bottom:5px;">
                    {{ $invoice->billing_address ?? 'Address not available' }}
                </div>
                @if($partyPhone)
                <div style="font-size:11px;color:#4b5563;">Phone: {{ $partyPhone }}</div>
                @endif
                @if($partyEmail)
                <div style="font-size:11px;color:#4b5563;">Email: {{ $partyEmail }}</div>
                @endif
                @if($showGST && $partyGst)
                <div style="font-size:11px;font-weight:600;margin-top:5px;">GSTIN: {{ $partyGst }}</div>
                @endif
            </div>

            {{-- Ship To / Warehouse --}}
            <div class="iv-party-block">
                <div class="iv-party-block-title">Delivery Details</div>
                @if($invoice->shipping_address)
                    <div style="font-size:11px;color:#4b5563;">{{ $invoice->shipping_address }}</div>
                @else
                    <div style="font-size:11px;color:#4b5563;">Same as billing address</div>
                @endif
                @if($invoice->warehouse)
                <div style="margin-top:8px;font-size:11px;">
                    <strong>Received at:</strong> {{ $invoice->warehouse->name ?? 'Main Warehouse' }}
                </div>
                @endif
                @if($invoice->payment_terms)
                <div style="margin-top:6px;font-size:11px;">
                    <strong>Payment Terms:</strong> {{ $invoice->payment_terms }}
                </div>
                @endif
                @if($showGST && $invoice->tax_type)
                <div style="margin-top:6px;font-size:11px;">
                    <strong>Tax Type:</strong>
                    {{ $invoice->tax_type === 'intra' ? 'Intra-State (CGST+SGST)' : 'Inter-State (IGST)' }}
                </div>
                @endif
            </div>
        </div>

        {{-- Items Table --}}
        <div class="iv-items-section" style="position:relative;">
            @if($invoice->status === 'cancelled')
            <div class="iv-cancelled-stamp">CANCELLED</div>
            @endif

            <table class="iv-table">
                <thead>
                    <tr>
                        <th width="30">S.No.</th>
                        <th>Product</th>
                        @if($showGST)<th>HSN</th>@endif
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>MRP</th>
                        <th>Purchase Price</th>
                        @if($showGST)<th>Tax%</th><th>Tax Amt</th>@endif
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmount = 0; @endphp
                    @foreach($invoice->items as $idx => $item)
                    @php
                        $taxAmt = $showGST
                            ? round($item->quantity * $item->purchase_price * ($item->tax_percent ?? 0) / 100, 2)
                            : 0;
                        $lineTotal = ($item->quantity * $item->purchase_price) + $taxAmt;
                        $totalAmount += $lineTotal;
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td style="text-align:left;">
                            <strong>{{ $item->product_name }}</strong>
                            @if(!empty($item->variant_name))
                                <div style="font-size:9px;color:#6b7280;">{{ $item->variant_name }}</div>
                            @endif
                            @if($item->sku)
                                <div style="font-size:8px;color:#9ca3af;">SKU: {{ $item->sku }}</div>
                            @endif
                        </td>
                        @if($showGST)
                        <td>{{ $item->hsn_sac ?: '—' }}</td>
                        @endif
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ $item->unit ?? 'PCS' }}</td>
                        <td>₹ {{ number_format($item->mrp_price ?? 0, 2) }}</td>
                        <td>₹ {{ number_format($item->purchase_price, 2) }}</td>
                        @if($showGST)
                        <td>{{ number_format($item->tax_percent ?? 0, 0) }}%</td>
                        <td>₹ {{ number_format($taxAmt, 2) }}</td>
                        @endif
                        <td>₹ {{ number_format($lineTotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="{{ $showGST ? '9' : '6' }}" style="text-align:right;">Total</td>
                        <td><strong>₹ {{ number_format($totalAmount, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>



        {{-- Bottom: Notes/Bank + Summary --}}
        <div class="iv-bottom-grid">
            {{-- Left: Notes & Bank --}}
            <div class="iv-left-panel">
                @if($invoice->notes)
                <div class="iv-panel-title">Notes</div>
                <div style="font-size:11px;color:#4b5563;line-height:1.6;margin-bottom:15px;white-space:pre-line;">{{ $invoice->notes }}</div>
                @endif

                @if($settings && ($settings->bank_name || $settings->account_number))
                <div class="iv-panel-title" style="margin-top:{{ $invoice->notes ? '15px' : '0' }};">Bank Details</div>
                <div class="iv-bank-details">
                    @if($settings->bank_name)
                    <div class="iv-bank-row"><span class="iv-bank-label">Bank:</span><span class="iv-bank-value">{{ $settings->bank_name }}</span></div>
                    @endif
                    @if($settings->account_number)
                    <div class="iv-bank-row"><span class="iv-bank-label">A/c No.:</span><span class="iv-bank-value">{{ $settings->account_number }}</span></div>
                    @endif
                    @if($settings->ifsc_code)
                    <div class="iv-bank-row"><span class="iv-bank-label">IFSC:</span><span class="iv-bank-value">{{ $settings->ifsc_code }}</span></div>
                    @endif
                    @if($settings->account_name)
                    <div class="iv-bank-row"><span class="iv-bank-label">A/c Name:</span><span class="iv-bank-value">{{ $settings->account_name }}</span></div>
                    @endif
                </div>
                @endif

                @if($settings?->terms_and_conditions)
                <div class="iv-panel-title" style="margin-top:15px;">Terms</div>
                <div style="font-size:11px;color:#4b5563;line-height:1.6;">{{ $settings->terms_and_conditions }}</div>
                @endif
            </div>

            {{-- Right: Amount Summary --}}
            <div class="iv-right-panel">
                <div class="iv-panel-title">Amount Summary</div>

                <div class="iv-total-row">
                    <span>Total MRP</span>
                    <span>₹ {{ number_format($invoice->total_mrp ?? 0, 2) }}</span>
                </div>
                <div class="iv-total-row">
                    <span>Subtotal (Purchase)</span>
                    <span>₹ {{ number_format($invoice->subtotal, 2) }}</span>
                </div>

                @if($invoice->extra_discount > 0)
                <div class="iv-total-row">
                    <span>
                        Discount
                        @if($invoice->extra_discount_type === 'percent')
                            ({{ number_format($invoice->extra_discount, 1) }}%)
                        @else
                            (Fixed)
                        @endif
                    </span>
                    <span style="color:#dc2626;">
                        @if($invoice->extra_discount_type === 'percent')
                            − ₹ {{ number_format($invoice->subtotal * $invoice->extra_discount / 100, 2) }}
                        @else
                            − ₹ {{ number_format($invoice->extra_discount, 2) }}
                        @endif
                    </span>
                </div>
                @endif

                @if($showGST)
                    @if($invoice->tax_type === 'intra')
                    <div class="iv-total-row">
                        <span>CGST</span>
                        <span>+ ₹ {{ number_format($invoice->cgst_total ?? 0, 2) }}</span>
                    </div>
                    <div class="iv-total-row">
                        <span>SGST</span>
                        <span>+ ₹ {{ number_format($invoice->sgst_total ?? 0, 2) }}</span>
                    </div>
                    @else
                    <div class="iv-total-row">
                        <span>IGST</span>
                        <span>+ ₹ {{ number_format($invoice->igst_total ?? 0, 2) }}</span>
                    </div>
                    @endif
                    <div class="iv-total-row" style="color:#f97316;font-weight:600;">
                        <span>Total Tax</span>
                        <span>₹ {{ number_format($invoice->tax_total, 2) }}</span>
                    </div>
                @endif

                @if($invoice->extra_charge > 0)
                <div class="iv-total-row">
                    <span>{{ $invoice->charge_name ?: 'Other Charge' }}</span>
                    <span>+ ₹ {{ number_format($invoice->extra_charge, 2) }}</span>
                </div>
                @endif

                @if($invoice->round_off != 0)
                <div class="iv-total-row" style="color:#9ca3af;">
                    <span>Round Off</span>
                    <span>{{ $invoice->round_off > 0 ? '+' : '' }} ₹ {{ number_format($invoice->round_off, 2) }}</span>
                </div>
                @endif

                <div class="iv-total-row grand">
                    <span>Grand Total</span>
                    <span class="iv-total-value">₹ {{ number_format($invoice->grand_total, 2) }}</span>
                </div>


                <div style="margin-top:15px;">
                    <div class="iv-total-row">
                        <span>Total Paid</span>
                        <span style="color:#059669;font-weight:600;">₹ {{ number_format($invoice->total_paid ?? 0, 2) }}</span>
                    </div>
                    <div class="iv-total-row" style="color:#dc2626;">
                        <span>Balance Due</span>
                        <span>₹ {{ number_format($invoice->balance_amount ?? 0, 2) }}</span>
                    </div>
                </div>


                {{-- Amount in Words --}}
                <div class="iv-amount-words">
                    <div class="iv-words-label">Amount In Words</div>
                    <div class="iv-words-value">{{ piNumberToWords($invoice->grand_total) }} Rupees Only</div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="iv-footer">
            {{ $settings->footer_note ?? 'This is a computer generated purchase invoice' }}
        </div>
    </div>
</div>

{{-- ── Modals ── --}}
@foreach([
    ['id'=>'generateModal','title'=>'Generate Invoice','body'=>'Stock will be added to warehouse. This will confirm the purchase.','btnId'=>'confirmGenerate','btnClass'=>'modal-btn-generate','btnTxt'=>'Generate Invoice','color'=>'#f97316'],
    ['id'=>'cancelModal',  'title'=>'Cancel Invoice',  'body'=>'Stock will be removed from warehouse. This cannot be undone.', 'btnId'=>'confirmCancel', 'btnClass'=>'modal-btn-cancel', 'btnTxt'=>'Cancel Invoice', 'color'=>'#dc2626'],
    ['id'=>'deleteModal',  'title'=>'Delete Invoice',  'body'=>'This draft invoice will be permanently deleted.',               'btnId'=>'confirmDelete', 'btnClass'=>'modal-btn-delete', 'btnTxt'=>'Delete Invoice', 'color'=>'#ef4444'],
] as $m)
<div id="{{ $m['id'] }}" style="display:none;position:fixed;inset:0;z-index:1000;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(2px);" onclick="closeAllModals()"></div>
    <div style="position:relative;background:white;border-radius:10px;width:380px;max-width:92%;box-shadow:0 20px 50px rgba(0,0,0,.15);animation:modalIn .2s ease;">
        <div style="display:flex;align-items:center;gap:10px;padding:14px 16px;background:#f8fafc;border-bottom:1px solid #e2e8f0;border-radius:10px 10px 0 0;">
            <div style="width:34px;height:34px;border-radius:8px;background:{{ $m['color'] }};display:flex;align-items:center;justify-content:center;color:white;font-size:16px;flex-shrink:0;">⚡</div>
            <div>
                <div style="font-size:13px;font-weight:700;">{{ $m['title'] }}</div>
                <div style="font-size:10.5px;color:#64748b;">{{ $invoice->invoice_number }}</div>
            </div>
            <button onclick="closeAllModals()" style="margin-left:auto;background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;">×</button>
        </div>
        <div style="padding:16px;font-size:12.5px;color:#374151;line-height:1.6;">
            {{ $m['body'] }}
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;padding:12px 16px;background:#fafafa;border-top:1px solid #e2e8f0;border-radius:0 0 10px 10px;">
            <button onclick="closeAllModals()" style="padding:7px 16px;border:1px solid #e2e8f0;border-radius:5px;background:#f8fafc;color:#475569;font-size:12px;font-weight:600;cursor:pointer;">Cancel</button>
            <button id="{{ $m['btnId'] }}" style="padding:7px 16px;border:none;border-radius:5px;background:{{ $m['color'] }};color:white;font-size:12px;font-weight:600;cursor:pointer;">{{ $m['btnTxt'] }}</button>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
const invoiceId  = '{{ $invoice->id }}';
const csrfToken  = '{{ csrf_token() }}';

// ── Alerts ──
function showAlert(message, type = 'success') {
    const container = document.getElementById('iv-alert-container');
    const alert = document.createElement('div');
    alert.className = `iv-alert iv-alert-${type}`;
    alert.innerHTML = message;
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 4000);
}

// ── Print / PDF ──
function printInvoice() {
    document.title = '{{ $invoice->invoice_number }}';
    window.print();
}

function downloadPDF() {
    const original = document.getElementById('invoiceToPrint');
    showAlert('Generating PDF...', 'info');

    // Create a temporary container to force desktop viewport layout
    const tempContainer = document.createElement('div');
    tempContainer.style.position = 'absolute';
    tempContainer.style.left = '-9999px';
    tempContainer.style.top = '-9999px';
    tempContainer.style.width = '1050px';

    const clone = original.cloneNode(true);
    clone.style.width = '1050px';
    clone.style.minWidth = '1050px';
    clone.style.display = 'block';

    tempContainer.appendChild(clone);
    document.body.appendChild(tempContainer);

    // Add print mode to body (if any print styles need it)
    document.body.classList.add('iv-print-mode');

    html2pdf().set({
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: '{{ $invoice->invoice_number }}.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, width: 1050 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    }).from(clone).save()
    .then(() => {
        showAlert('PDF downloaded!', 'success');
        tempContainer.remove();
        document.body.classList.remove('iv-print-mode');
    })
    .catch((err) => {
        console.error(err);
        showAlert('PDF generation failed!', 'error');
        tempContainer.remove();
        document.body.classList.remove('iv-print-mode');
    });
}

// ── Modals ──
function openModal(id)    { document.getElementById(id).style.display = 'flex'; }
function closeAllModals() { ['generateModal','cancelModal','deleteModal'].forEach(id => document.getElementById(id).style.display = 'none'); }
function generateInvoice(){ openModal('generateModal'); }
function cancelInvoice()  { openModal('cancelModal'); }
function deleteInvoice()  { openModal('deleteModal'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAllModals(); });

// ── Generic action ──
async function doAction(url, btnId, loadingTxt, successCb) {
    const btn = document.getElementById(btnId);
    btn.disabled = true;
    const orig = btn.textContent;
    btn.textContent = loadingTxt;
    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        closeAllModals();
        if (data.success) {
            showAlert(data.message || 'Success!', 'success');
            successCb();
        } else {
            showAlert(data.message || 'Action failed', 'error');
            btn.disabled = false;
            btn.textContent = orig;
        }
    } catch {
        closeAllModals();
        showAlert('Something went wrong', 'error');
        btn.disabled = false;
        btn.textContent = orig;
    }
}

// Generate
document.getElementById('confirmGenerate').addEventListener('click', () =>
    doAction(
        `/admin/purchases/${invoiceId}/generate`,
        'confirmGenerate', 'Generating…',
        () => setTimeout(() => location.reload(), 1500)
    )
);

// Cancel
document.getElementById('confirmCancel').addEventListener('click', () =>
    doAction(
        `/admin/purchases/${invoiceId}/cancel`,
        'confirmCancel', 'Cancelling…',
        () => setTimeout(() => location.reload(), 1500)
    )
);

// Delete
document.getElementById('confirmDelete').addEventListener('click', async () => {
    const btn = document.getElementById('confirmDelete');
    btn.disabled = true; btn.textContent = 'Deleting…';
    try {
        const res  = await fetch(`/admin/purchases/${invoiceId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        closeAllModals();
        if (data.success) {
            showAlert('Invoice deleted', 'success');
            setTimeout(() => window.location.href = '{{ route("admin.purchases.index") }}', 1500);
        } else {
            showAlert(data.message || 'Delete failed', 'error');
            btn.disabled = false; btn.textContent = 'Delete Invoice';
        }
    } catch {
        closeAllModals();
        showAlert('Something went wrong', 'error');
        btn.disabled = false; btn.textContent = 'Delete Invoice';
    }
});

// Modal animation CSS
const style = document.createElement('style');
style.textContent = `@keyframes modalIn { from{opacity:0;transform:scale(.95) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }`;
document.head.appendChild(style);
</script>
@endpush
@endsection
