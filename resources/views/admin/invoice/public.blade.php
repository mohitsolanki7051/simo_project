<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            font-size: 13px;
            color: #111827;
        }

        /* ─── TOP BAR ─────────────────────────────────── */
        .pub-topbar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            gap: 10px;
        }

        .pub-topbar-title {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pub-topbar-sub {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pub-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .pub-btn-primary { background: #f97316; color: white; }
        .pub-btn-outline { background: white; border: 1px solid #d1d5db; color: #374151; }

        .pub-btn-group {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }

        /* Payment Badge */
        .pub-status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }
        .badge-paid    { background: #d1fae5; color: #065f46; }
        .badge-unpaid  { background: #fee2e2; color: #991b1b; }
        .badge-partial { background: #fef3c7; color: #92400e; }

        /* ─── WRAPPER ─────────────────────────────────── */
        .pub-wrap {
            max-width: 900px;
            margin: 16px auto;
            padding: 0 12px 40px;
        }

        .pub-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        /* ─── HEADER ──────────────────────────────────── */
        .iv-header {
            padding: 16px 20px;
            border-bottom: 3px solid #f97316;
        }

        .iv-company-block {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .iv-logo-img { height: 52px; width: auto; flex-shrink: 0; }

        .iv-logo-placeholder {
            width: 52px; height: 52px;
            background: #f97316;
            color: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .iv-company-details { flex: 1; min-width: 0; }

        .iv-company-details h2 {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 3px;
        }

        .iv-company-details p {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.5;
        }

        .iv-company-contact {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            margin-top: 5px;
            font-size: 11px;
            color: #4b5563;
        }

        /* ─── TITLE ROW ───────────────────────────────── */
        .iv-title-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px 8px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .iv-doc-title {
            font-size: 18px;
            font-weight: 700;
            color: #f97316;
            text-transform: uppercase;
        }

        .iv-invoice-meta { text-align: right; }

        .iv-invoice-number {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        .iv-invoice-date {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* ─── PARTY GRID ──────────────────────────────── */
        .iv-party-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 12px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .iv-party-block {
            background: white;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }

        .iv-party-block-title {
            font-size: 11px;
            font-weight: 700;
            color: #f97316;
            margin-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* ─── ITEMS TABLE ─────────────────────────────── */
        .iv-items-section { padding: 12px 20px; }

        /* Desktop table */
        .iv-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }

        .iv-table thead tr { background: #f97316; color: white; }

        .iv-table thead th {
            padding: 8px 6px;
            font-weight: 600;
            font-size: 10px;
            text-align: center;
            border-right: 1px solid #fb923c;
        }

        .iv-table thead th:last-child { border-right: none; }

        .iv-table tbody tr { border-bottom: 1px solid #e5e7eb; }

        .iv-table tbody td {
            padding: 6px 5px;
            border-right: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
        }

        .iv-table tbody td:last-child { border-right: none; font-weight: 600; }

        .iv-table tfoot tr {
            background: #f3f4f6;
            font-weight: 700;
            border-top: 2px solid #f97316;
        }

        .iv-table tfoot td { padding: 8px 6px; text-align: center; }

        /* Mobile card layout for items */
        .iv-items-mobile { display: none; }

        .iv-item-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .iv-item-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
            gap: 8px;
        }

        .iv-item-name {
            font-weight: 700;
            font-size: 13px;
            color: #111827;
        }

        .iv-item-variant {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }

        .iv-item-total {
            font-size: 14px;
            font-weight: 700;
            color: #f97316;
            white-space: nowrap;
        }

        .iv-item-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 12px;
            margin-top: 6px;
        }

        .iv-item-pill {
            background: #f3f4f6;
            border-radius: 4px;
            padding: 3px 8px;
            font-size: 10px;
            color: #374151;
        }

        .iv-item-pill strong { color: #111827; }

        .iv-items-total-bar {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 6px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
            font-size: 13px;
            font-weight: 700;
        }

        /* ─── BOTTOM GRID ─────────────────────────────── */
        .iv-bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 16px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        .iv-left-panel, .iv-right-panel {
            background: white;
            padding: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }

        .iv-panel-title {
            font-size: 11px;
            font-weight: 700;
            color: #f97316;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .iv-bank-row {
            display: flex;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .iv-bank-label { min-width: 85px; color: #6b7280; }
        .iv-bank-value { font-weight: 500; color: #111827; }

        .iv-total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 11px;
            border-bottom: 1px dotted #e5e7eb;
        }

        .iv-total-row.grand {
            border-top: 2px solid #f97316;
            border-bottom: none;
            margin-top: 8px;
            padding-top: 8px;
            font-weight: 700;
            font-size: 13px;
        }

        .iv-total-row.grand .iv-total-value { color: #f97316; }

        .iv-amount-words {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px dashed #e5e7eb;
        }

        .iv-words-label { font-size: 10px; color: #6b7280; margin-bottom: 4px; }

        .iv-words-value {
            font-size: 11px;
            font-weight: 600;
            color: #111827;
            text-transform: uppercase;
            line-height: 1.5;
        }

        .iv-footer {
            padding: 12px 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
            background: #f9fafb;
        }

        .iv-notes {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.7;
            margin-bottom: 12px;
        }

        /* ─── RESPONSIVE ──────────────────────────────── */
        @media (max-width: 640px) {
            /* Topbar */
            body:not(.iv-print-mode) .pub-topbar {
                padding: 10px 12px;
                flex-wrap: wrap;
            }
            body:not(.iv-print-mode) .pub-topbar-left { flex: 1; min-width: 0; }
            body:not(.iv-print-mode) .pub-btn-group { flex-shrink: 0; }
            body:not(.iv-print-mode) .pub-btn { padding: 7px 10px; font-size: 11px; }
            body:not(.iv-print-mode) .pub-btn span.btn-label { display: none; }

            /* Wrap */
            body:not(.iv-print-mode) .pub-wrap { margin: 10px auto; padding: 0 8px 30px; }

            /* Header */
            body:not(.iv-print-mode) .iv-header { padding: 14px; }
            body:not(.iv-print-mode) .iv-company-details h2 { font-size: 14px; }

            /* Title row */
            body:not(.iv-print-mode) .iv-title-row {
                padding: 10px 14px 6px;
                flex-direction: column;
                align-items: flex-start;
            }
            body:not(.iv-print-mode) .iv-invoice-meta { text-align: left; }

            /* Party grid: stack */
            body:not(.iv-print-mode) .iv-party-grid {
                grid-template-columns: 1fr;
                padding: 10px 14px;
                gap: 8px;
            }

            /* Items: hide table, show cards */
            body:not(.iv-print-mode) .iv-items-section { padding: 10px 14px; }
            body:not(.iv-print-mode) .iv-table-wrap { display: none; }
            body:not(.iv-print-mode) .iv-items-mobile { display: block; }

            /* Bottom grid: stack */
            body:not(.iv-print-mode) .iv-bottom-grid {
                grid-template-columns: 1fr;
                padding: 12px 14px;
                gap: 10px;
            }

            /* Totals panel — make it prominent on mobile */
            body:not(.iv-print-mode) .iv-right-panel { order: -1; }

            body:not(.iv-print-mode) .iv-total-row.grand { font-size: 15px; }
            body:not(.iv-print-mode) .iv-footer { padding: 10px 14px; }
        }

        @media (max-width: 360px) {
            body:not(.iv-print-mode) .pub-btn-group { gap: 4px; }
            body:not(.iv-print-mode) .pub-btn { padding: 6px 8px; }
        }

        /* ─── PRINT ───────────────────────────────────── */
        @media print {
            .pub-topbar { display: none !important; }
            body { background: white; }
            .pub-wrap { margin: 0; padding: 0; }
            .pub-card { box-shadow: none; border: none; }
            .iv-items-mobile { display: none !important; }
            .iv-table-wrap { display: block !important; }
            @page { size: A4; margin: 0.3in; }
            .iv-table thead tr {
                background: #f97316 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

@php
    $showGST = $invoice->invoice_type !== 'cash';
    $party   = $invoice->party;

    function pubNumberToWords($num) {
        $num  = (int)$num;
        $ones = [0=>'Zero',1=>'One',2=>'Two',3=>'Three',4=>'Four',5=>'Five',
                 6=>'Six',7=>'Seven',8=>'Eight',9=>'Nine',10=>'Ten',
                 11=>'Eleven',12=>'Twelve',13=>'Thirteen',14=>'Fourteen',
                 15=>'Fifteen',16=>'Sixteen',17=>'Seventeen',18=>'Eighteen',19=>'Nineteen'];
        $tens = [2=>'Twenty',3=>'Thirty',4=>'Forty',5=>'Fifty',
                 6=>'Sixty',7=>'Seventy',8=>'Eighty',9=>'Ninety'];

        if ($num == 0) return 'Zero';
        $words = [];
        if ($num >= 10000000) { $words[] = pubConvert((int)($num/10000000),$ones,$tens).' Crore'; $num %= 10000000; }
        if ($num >= 100000)   { $words[] = pubConvert((int)($num/100000),$ones,$tens).' Lakh';   $num %= 100000; }
        if ($num >= 1000)     { $words[] = pubConvert((int)($num/1000),$ones,$tens).' Thousand'; $num %= 1000; }
        if ($num >= 100)      { $words[] = $ones[(int)($num/100)].' Hundred'; $num %= 100; }
        if ($num > 0) {
            if ($num < 20) { $words[] = $ones[$num]; }
            else { $t=(int)($num/10); $o=$num%10; $words[] = $o>0 ? $tens[$t].' '.$ones[$o] : $tens[$t]; }
        }
        return implode(' ', $words);
    }

    function pubConvert($num, $ones, $tens) {
        if ($num < 20) return $ones[$num];
        $t=(int)($num/10); $o=$num%10;
        return $o>0 ? $tens[$t].' '.$ones[$o] : $tens[$t];
    }
@endphp

<!-- ─── TOP ACTION BAR ─────────────────────────────── -->
<div class="pub-topbar">
    <div class="pub-topbar-left">
        <div class="pub-topbar-title">
            {{ $invoice->invoice_type === 'cash' ? 'Cash Memo' : 'Tax Invoice' }}
            #{{ $invoice->invoice_number }}
        </div>
        <div class="pub-topbar-sub">
            {{ $party->name ?? '' }} &nbsp;·&nbsp;
            {{ $invoice->invoice_date->format('d M Y') }} &nbsp;·&nbsp;
            <span class="pub-status-badge badge-{{ $invoice->payment_status }}">
                {{ ucfirst($invoice->payment_status) }}
            </span>
        </div>
    </div>
    <div class="pub-btn-group">
        <button onclick="window.print()" class="pub-btn pub-btn-outline">
            🖨️ <span class="btn-label">Print</span>
        </button>
        <button onclick="downloadPDF()" class="pub-btn pub-btn-primary">
            ⬇️ <span class="btn-label">Download PDF</span>
        </button>
    </div>
</div>

<!-- ─── INVOICE ────────────────────────────────────── -->
<div class="pub-wrap">
    <div class="pub-card" id="invoiceToPrint">

        <!-- Company Header -->
        <div class="iv-header">
            <div class="iv-company-block">
                @if($settings && $settings->logo_path)
                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="iv-logo-img">
                @else
                    <div class="iv-logo-placeholder">
                        {{ strtoupper(substr($settings->company_name ?? 'SI', 0, 2)) }}
                    </div>
                @endif
                <div class="iv-company-details">
                    <h2>{{ $settings?->company_name ?? 'Company Name' }}</h2>
                    <p>{{ $settings?->company_address ?? '' }}</p>
                    <div class="iv-company-contact">
                        @if($settings?->company_phone) <span>Phone: {{ $settings?->company_phone }}</span> @endif
                        @if($settings?->company_email) <span>Enail:  {{ $settings?->company_email }}</span> @endif
                    </div>
                    @if($showGST && ($settings?->gstin || $settings?->pan))
                    <div style="margin-top:5px;font-size:11px;font-weight:500;">
                        @if($settings?->gstin) GSTIN: {{ $settings?->gstin }} @endif
                        @if($settings?->pan) &nbsp;|&nbsp; PAN: {{ $settings?->pan }} @endif
                    </div>
                    @endif
                    @if($invoice->salesman && $invoice->salesman->name)
                    <div style="margin-top:8px;font-size:11px;background:#fff7ed;padding:4px 8px;border-radius:4px;display:inline-block;border:1px solid #fed7aa;">
                        <strong>Sales Executive:</strong> {{ $invoice->salesman->name }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice Title Row -->
        <div class="iv-title-row">
            <div class="iv-doc-title">
                {{ $invoice->invoice_type === 'cash' ? 'CASH MEMO' : 'TAX INVOICE' }}
            </div>
            <div class="iv-invoice-meta">
                <div class="iv-invoice-number">{{ $invoice->invoice_number }}</div>
                <div class="iv-invoice-date">Date: {{ $invoice->invoice_date->format('d/m/Y') }}</div>
                @if($invoice->due_date)
                <div class="iv-invoice-date">Due: {{ $invoice->due_date->format('d/m/Y') }}</div>
                @endif
            </div>
        </div>

        <!-- Party Info -->
        <div class="iv-party-grid">
            <div class="iv-party-block">
                <div class="iv-party-block-title">Bill To</div>
                <div style="font-weight:600;margin-bottom:4px;font-size:13px;">{{ $party->name ?? 'N/A' }}</div>
                <div style="font-size:11px;color:#4b5563;margin-bottom:4px;line-height:1.5;">{{ $invoice->billing_address ?? '' }}</div>
                <div style="font-size:11px;color:#4b5563;">Phone: {{ $party->phone ?? 'N/A' }}</div>
                @if($party?->email)
                <div style="font-size:11px;color:#4b5563;"> Email: {{ $party->email }}</div>
                @endif
                @if($showGST && $party?->gst_number)
                <div style="font-size:11px;font-weight:600;margin-top:6px;background:#f0fdf4;padding:3px 6px;border-radius:4px;display:inline-block;">
                    GSTIN: {{ $party->gst_number }}
                </div>
                @endif
            </div>
            <div class="iv-party-block">
                <div class="iv-party-block-title">Ship To</div>
                @if($invoice->shipping_address && $invoice->shipping_address !== $invoice->billing_address)
                    <div style="font-size:11px;color:#4b5563;line-height:1.5;">{{ $invoice->shipping_address }}</div>
                @else
                    <div style="font-size:11px;color:#6b7280;font-style:italic;">Same as billing address</div>
                @endif
                @if($invoice->warehouse)
                <div style="margin-top:8px;font-size:11px;background:#f9fafb;padding:5px 8px;border-radius:4px;border:1px solid #e5e7eb;">
                    <strong>Dispatched From:</strong> {{ $invoice->warehouse->name ?? 'Main Warehouse' }}
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="iv-items-section">

            {{-- Desktop Table --}}
            <div class="iv-table-wrap">
                <table class="iv-table">
                    <thead>
                        <tr>
                            <th width="30">S.No</th>
                            <th style="text-align:left;">Product</th>
                            @if($showGST)<th>HSN</th>@endif
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>MRP</th>
                            <th>Disc%</th>
                            <th>Rate</th>
                            @if($showGST)<th>Tax%</th>@endif
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalAmount = 0; @endphp
                        @foreach($invoice->items as $idx => $item)
                        @php
                            $lineTotal = $item->quantity * $item->price;
                            if ($showGST) $lineTotal += $item->tax_amount ?? 0;
                            $totalAmount += $lineTotal;
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td style="text-align:left;">
                                <strong>{{ $item->product_name }}</strong>
                                @if($item->variant_name)
                                    <div style="font-size:9px;color:#6b7280;">{{ $item->variant_name }}</div>
                                @endif
                            </td>
                            @if($showGST)<td>{{ $item->hsn_sac ?: '—' }}</td>@endif
                            <td>{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>₹{{ number_format($item->mrp_price, 2) }}</td>
                            <td>{{ $item->discount > 0 ? number_format($item->discount, 1).'%' : '—' }}</td>
                            <td>₹{{ number_format($item->price, 2) }}</td>
                            @if($showGST)<td>{{ number_format($item->tax_percent, 0) }}%</td>@endif
                            <td>₹{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="{{ $showGST ? '9' : '7' }}" style="text-align:right;font-size:11px;">Total</td>
                            <td><strong>₹{{ number_format($totalAmount, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="iv-items-mobile">
                @php $totalAmountMobile = 0; @endphp
                @foreach($invoice->items as $idx => $item)
                @php
                    $lineTotal = $item->quantity * $item->price;
                    if ($showGST) $lineTotal += $item->tax_amount ?? 0;
                    $totalAmountMobile += $lineTotal;
                @endphp
                <div class="iv-item-card">
                    <div class="iv-item-card-header">
                        <div>
                            <div class="iv-item-name">{{ $idx + 1 }}. {{ $item->product_name }}</div>
                            @if($item->variant_name)
                                <div class="iv-item-variant">{{ $item->variant_name }}</div>
                            @endif
                        </div>
                        <div class="iv-item-total">₹{{ number_format($lineTotal, 2) }}</div>
                    </div>
                    <div class="iv-item-row">
                        <div class="iv-item-pill">Qty: <strong>{{ number_format($item->quantity, 2) }} {{ $item->unit }}</strong></div>
                        <div class="iv-item-pill">MRP: <strong>₹{{ number_format($item->mrp_price, 2) }}</strong></div>
                        <div class="iv-item-pill">Rate: <strong>₹{{ number_format($item->price, 2) }}</strong></div>
                        @if($item->discount > 0)
                        <div class="iv-item-pill" style="background:#fef3c7;">Disc: <strong>{{ number_format($item->discount, 1) }}%</strong></div>
                        @endif
                        @if($showGST)
                        <div class="iv-item-pill">Tax: <strong>{{ number_format($item->tax_percent, 0) }}%</strong></div>
                        @if($item->hsn_sac)
                        <div class="iv-item-pill">HSN: <strong>{{ $item->hsn_sac }}</strong></div>
                        @endif
                        @endif
                    </div>
                </div>
                @endforeach

                <div class="iv-items-total-bar">
                    <span>Items Total</span>
                    <span>₹{{ number_format($totalAmountMobile, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Bottom: Notes + Summary -->
        <div class="iv-bottom-grid">

            {{-- Left: Notes / Bank / Terms --}}
            <div class="iv-left-panel">
                @if($invoice->notes)
                <div class="iv-panel-title">Notes</div>
                <div class="iv-notes">{{ $invoice->notes }}</div>
                @endif

                @if($settings && ($settings?->bank_name || $settings?->account_number))
                <div class="iv-panel-title" @if($invoice->notes) style="margin-top:14px;" @endif>Bank Details</div>
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
                @endif

                @if($settings?->terms_and_conditions)
                <div class="iv-panel-title" style="margin-top:14px;">Terms</div>
                <div class="iv-notes">{{ $settings->terms_and_conditions }}</div>
                @endif

                @if(!$invoice->notes && !($settings && ($settings?->bank_name || $settings?->account_number)) && !$settings?->terms_and_conditions)
                <div style="font-size:11px;color:#9ca3af;font-style:italic;">No additional details.</div>
                @endif
            </div>

            {{-- Right: Amount Summary --}}
            <div class="iv-right-panel">
                <div class="iv-panel-title">Amount Summary</div>

                <div class="iv-total-row">
                    <span>Subtotal</span>
                    <span>₹{{ number_format($invoice->subtotal, 2) }}</span>
                </div>

                @if($invoice->extra_discount > 0)
                <div class="iv-total-row">
                    <span>Extra Discount</span>
                    <span>
                        @if($invoice->extra_discount_type === 'percent')
                            {{ number_format($invoice->extra_discount, 1) }}%
                        @else
                            - ₹{{ number_format($invoice->extra_discount, 2) }}
                        @endif
                    </span>
                </div>
                @endif

                @if($showGST)
                <div class="iv-total-row">
                    <span>Tax</span>
                    <span>+ ₹{{ number_format($invoice->tax_total, 2) }}</span>
                </div>
                @endif

                @if($invoice->extra_charge > 0)
                <div class="iv-total-row">
                    <span>{{ $invoice->charge_name ?? 'Extra Charge' }}</span>
                    <span>+ ₹{{ number_format($invoice->extra_charge, 2) }}</span>
                </div>
                @endif

                @if($invoice->round_off != 0)
                <div class="iv-total-row">
                    <span>Round Off</span>
                    <span>₹{{ number_format($invoice->round_off, 2) }}</span>
                </div>
                @endif

                <div class="iv-total-row grand">
                    <span>Grand Total</span>
                    <span class="iv-total-value">₹{{ number_format($invoice->grand_total, 2) }}</span>
                </div>
                @if($invoice->total_paid > 0)
                <div style="margin-top:12px;">
                    <div class="iv-total-row">
                        <span>Paid</span>
                        <span style="color:#16a34a;font-weight:600;">₹{{ number_format($invoice->total_paid, 2) }}</span>
                    </div>
                    <div class="iv-total-row" style="color:#dc2626;">
                        <span>Balance Due</span>
                        <span style="font-weight:700;">₹{{ number_format($invoice->balance_amount, 2) }}</span>
                    </div>
                </div>
                @endif

                <div class="iv-amount-words">
                    <div class="iv-words-label">Amount In Words</div>
                    <div class="iv-words-value">{{ pubNumberToWords($invoice->grand_total) }} Rupees Only</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="iv-footer">
            {{ $settings?->footer_note ?? 'This is a computer generated invoice.' }}
        </div>

    </div>
</div>

<script>
function downloadPDF() {
    const btn = document.getElementById('dl-pdf-btn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Generating...';

    const original = document.getElementById('invoiceToPrint');

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

    // In clone, make sure table is visible and mobile card items are hidden
    const mobileItems = clone.querySelector('.iv-items-mobile');
    const tableWrap   = clone.querySelector('.iv-table-wrap');
    if (mobileItems) mobileItems.style.display = 'none';
    if (tableWrap)   tableWrap.style.display = 'block';

    tempContainer.appendChild(clone);
    document.body.appendChild(tempContainer);

    // Add print mode to body to disable media queries
    document.body.classList.add('iv-print-mode');

    html2pdf().set({
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: 'Invoice_{{ $invoice->invoice_number }}.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, width: 1050 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    })
    .from(clone)
    .save()
    .then(() => {
        btn.disabled = false;
        btn.innerHTML = '⬇️ <span class="btn-label">Download PDF</span>';
        tempContainer.remove();
        document.body.classList.remove('iv-print-mode');
    })
    .catch((err) => {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = '⬇️ <span class="btn-label">Download PDF</span>';
        tempContainer.remove();
        document.body.classList.remove('iv-print-mode');
    });
}
</script>
</body>
</html> 