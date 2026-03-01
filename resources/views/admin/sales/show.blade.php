@extends('layouts.admin')

@section('title', 'Invoice #' . $invoice->invoice_number . ' - Admin Panel')
@section('header-title', 'Sales Invoice #' . $invoice->invoice_number)

@section('content')

@php
    if ($invoice->invoice_type === 'cash') {
        $settings = \App\Models\CashMemoInvoiceSetting::first();
    } else {
        $settings = \App\Models\InvoiceSetting::first();
    }

    $party = $invoice->party;
    $partyType = $party->party_type ?? 'customer';

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

    // Calculate total items quantity
    $totalQuantity = $invoice->items->sum('quantity');

    // Helper function for number to words
   function numberToWords($num) {
        $num = (int)$num; // Convert to integer for simplicity

        $ones = array(
            0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
            14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen'
        );

        $tens = array(
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        );

        if ($num == 0) return 'Zero';

        $words = [];

        // Crore
        if ($num >= 10000000) {
            $crore = floor($num / 10000000);
            $words[] = convertToWords($crore, $ones, $tens) . ' Crore';
            $num %= 10000000;
        }

        // Lakh
        if ($num >= 100000) {
            $lakh = floor($num / 100000);
            $words[] = convertToWords($lakh, $ones, $tens) . ' Lakh';
            $num %= 100000;
        }

        // Thousand
        if ($num >= 1000) {
            $thousand = floor($num / 1000);
            $words[] = convertToWords($thousand, $ones, $tens) . ' Thousand';
            $num %= 1000;
        }

        // Hundred
        if ($num >= 100) {
            $hundred = floor($num / 100);
            $words[] = $ones[$hundred] . ' Hundred';
            $num %= 100;
        }

        // Tens and Ones
        if ($num > 0) {
            if ($num < 20) {
                $words[] = $ones[$num];
            } else {
                $ten = floor($num / 10);
                $one = $num % 10;
                if ($one > 0) {
                    $words[] = $tens[$ten] . ' ' . $ones[$one];
                } else {
                    $words[] = $tens[$ten];
                }
            }
        }

        return implode(' ', $words);
    }

    // Helper function for convertToWords (used above)
    function convertToWords($num, $ones, $tens) {
        if ($num < 20) {
            return $ones[$num];
        } else {
            $ten = floor($num / 10);
            $one = $num % 10;
            if ($one > 0) {
                return $tens[$ten] . ' ' . $ones[$one];
            } else {
                return $tens[$ten];
            }
        }
    }
@endphp

<style>
    /* PROFESSIONAL INVOICE STYLES - CLEAN & MODERN */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, sans-serif;
        background: #f3f4f6;
        font-size: 12px;
    }

    /* Action Bar - Screen Only */
    .iv-actions-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: white;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
    }

    .iv-actions-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .iv-actions-right {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .iv-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        color: #374151;
        font-size: 12px;
        text-decoration: none;
    }

    .iv-page-title {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
    }

    .iv-status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .iv-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }

    .iv-btn-primary { background: #f97316; color: white; }
    .iv-btn-primary:hover { background: #ea580c; }
    .iv-btn-outline { background: white; border: 1px solid #d1d5db; color: #374151; }
    .iv-btn-danger { background: #ef4444; color: white; }

    .iv-draft-warning {
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    /* INVOICE CARD */
    .iv-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        max-width: 1100px;
        margin: 0 auto;
    }

    /* Header - Your Original Company Info */
    .iv-header {
        padding: 20px 25px;
        border-bottom: 2px solid #f97316;
    }

    .iv-company-block {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .iv-logo-img {
        height: 60px;
        width: auto;
    }

    .iv-logo-placeholder {
        width: 60px;
        height: 60px;
        background: #f97316;
        color: white;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
    }

    .iv-company-details h2 {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .iv-company-details p {
        font-size: 11px;
        color: #4b5563;
        line-height: 1.5;
    }

    .iv-company-contact {
        display: flex;
        gap: 20px;
        margin-top: 6px;
        font-size: 11px;
    }

    /* Invoice Title Row */
    .iv-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 25px 10px;
    }

    .iv-doc-title {
        font-size: 20px;
        font-weight: 700;
        color: #f97316;
        text-transform: uppercase;
    }

    .iv-invoice-number {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        text-align: right;
    }

    .iv-invoice-date {
        font-size: 12px;
        color: #6b7280;
        margin-top: 2px;
    }

    /* Party Info Grid - Professional Two Column */
    .iv-party-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        padding: 15px 25px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
    }

    .iv-party-block {
        background: white;
        padding: 15px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    .iv-party-block-title {
        font-size: 12px;
        font-weight: 700;
        color: #f97316;
        margin-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 5px;
    }

    /* Items Table - Clean Professional */
    .iv-items-section {
        padding: 15px 25px;
    }

    .iv-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e5e7eb;
        font-size: 11px;
    }

    .iv-table thead tr {
        background: #f97316;
        color: white;
    }

    .iv-table thead th {
        padding: 8px 6px;
        font-weight: 600;
        font-size: 10px;
        text-align: center;
        border-right: 1px solid #fb923c;
    }

    .iv-table thead th:last-child {
        border-right: none;
    }

    .iv-table tbody tr {
        border-bottom: 1px solid #e5e7eb;
    }

    .iv-table tbody td {
        padding: 6px 5px;
        border-right: 1px solid #e5e7eb;
        text-align: right;
        font-size: 10.5px;
    }

    .iv-table tbody td:first-child {
        text-align: center;
        font-weight: 600;
    }

    .iv-table tbody td:nth-child(2) {
        text-align: left;
    }

    .iv-table tbody td:last-child {
        border-right: none;
        font-weight: 600;
    }

    .product-name-cell strong {
        display: block;
        font-weight: 600;
    }

    .variant-name {
        font-size: 9px;
        color: #6b7280;
    }

    .discount-badge {
        display: inline-block;
        background: #fee2e2;
        color: #b91c1c;
        font-size: 9px;
        padding: 2px 4px;
        border-radius: 3px;
    }

    .iv-table tfoot tr {
        background: #f3f4f6;
        font-weight: 700;
        border-top: 2px solid #f97316;
    }

    .iv-table tfoot td {
        padding: 8px 6px;
        text-align: right;
    }

    /* Bottom Sections */
    .iv-bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        padding: 20px 25px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    .iv-left-panel, .iv-right-panel {
        background: white;
        padding: 15px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    .iv-panel-title {
        font-size: 12px;
        font-weight: 700;
        color: #f97316;
        margin-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 5px;
    }

    .iv-notes {
        font-size: 11px;
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .iv-bank-details {
        font-size: 11px;
    }

    .iv-bank-row {
        display: flex;
        margin-bottom: 5px;
    }

    .iv-bank-label {
        width: 90px;
        color: #6b7280;
    }

    .iv-bank-value {
        font-weight: 500;
        color: #111827;
    }

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

    .iv-total-row.grand .iv-total-value {
        color: #f97316;
    }

    .iv-amount-words {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px dashed #e5e7eb;
    }

    .iv-words-label {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .iv-words-value {
        font-size: 12px;
        font-weight: 600;
        color: #111827;
        text-transform: uppercase;
    }

    .iv-footer {
        padding: 12px 25px;
        text-align: center;
        border-top: 1px solid #e5e7eb;
        font-size: 10px;
        color: #6b7280;
        background: #f9fafb;
    }

    /* Payment Status Badge */
    .iv-payment-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    /* Alerts */
    #iv-alert-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    .iv-alert {
        padding: 12px 16px;
        border-radius: 6px;
        font-size: 12px;
        margin-bottom: 8px;
        animation: slideIn 0.2s ease;
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .iv-alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #059669; }
    .iv-alert-error { background: #fee2e2; color: #991b1b; border-left: 4px solid #dc2626; }

    /* PRINT STYLES */
    @media print {
        body * { visibility: hidden !important; }
        #invoiceToPrint, #invoiceToPrint * { visibility: visible !important; }
        #invoiceToPrint {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
        }
        @page { size: A4; margin: 0.3in; }
        .iv-actions-bar, .iv-draft-warning, #iv-alert-container, .no-print {
            display: none !important;
        }
        .iv-table thead tr {
            background: #f97316 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<div class="iv-wrap">
    <div id="iv-alert-container"></div>

    <!-- Action Bar - Screen Only -->
    <div class="iv-actions-bar no-print">
        <div class="iv-actions-left">
            <a href="{{ route('admin.sales.index') }}" class="iv-back-btn">← Back</a>
            <h2 class="iv-page-title">{{ $invoice->invoice_type === 'cash' ? 'Cash Memo' : 'Tax Invoice' }} #{{ $invoice->invoice_number }}</h2>
            <span class="iv-status-pill" style="background: {{ $ps['bg'] }}; color: {{ $ps['text'] }};">
                {{ ucfirst($invoice->payment_status) }}
            </span>
            <span class="iv-status-pill" style="background: {{ $is['bg'] }}; color: {{ $is['color'] }};">
                {{ $is['text'] }}
            </span>
        </div>
        <div class="iv-actions-right">
            @if($invoice->status === 'draft')
                <button onclick="generateInvoice()" class="iv-btn iv-btn-primary">Generate</button>
                <a href="{{ route('admin.sales.edit', $invoice->_id) }}" class="iv-btn iv-btn-outline">Edit</a>
                <button onclick="deleteInvoice()" class="iv-btn iv-btn-danger">Delete</button>
            @endif
            <button onclick="printInvoice()" class="iv-btn iv-btn-outline">Print</button>
            <button onclick="downloadPDF()" class="iv-btn iv-btn-primary">PDF</button>
        </div>
    </div>

    @if($invoice->status === 'draft')
    <div class="iv-draft-warning no-print">
        <strong>DRAFT:</strong> Stock has NOT been deducted. Click "Generate" to confirm.
    </div>
    @endif

    <!-- MAIN INVOICE - YOUR CONTENT, PROFESSIONAL LAYOUT -->
    <div class="iv-card" id="invoiceToPrint">

        <!-- Header - Your Original Company Info -->
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
                    <div style="margin-top: 5px; font-size: 11px; font-weight: 500;">
                        @if($settings->gstin) GSTIN: {{ $settings->gstin }} @endif
                        @if($settings->pan) | PAN: {{ $settings->pan }} @endif
                    </div>
                    @endif

                    {{-- FIX 1: SALESMAN NAME DISPLAY --}}
                    @if($invoice->salesman && $invoice->salesman->name)
                    <div style="margin-top: 8px; font-size: 11px; background: #f3f4f6; padding: 4px 8px; border-radius: 4px; display: inline-block;">
                        <strong>Sales Person:</strong> {{ $invoice->salesman->name }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice Title & Number -->
        <div class="iv-title-row">
            <div class="iv-doc-title">{{ $invoice->invoice_type === 'cash' ? 'CASH MEMO' : 'TAX INVOICE' }}</div>
            <div>
                <div class="iv-invoice-number">{{ $invoice->invoice_number }}</div>
                <div class="iv-invoice-date">Date: {{ $invoice->invoice_date->format('d/m/Y') }}</div>
                @if($invoice->due_date)
                <div class="iv-invoice-date">Due: {{ $invoice->due_date->format('d/m/Y') }}</div>
                @endif
            </div>
        </div>

        <!-- Party Information - Professional Two Column -->
        <div class="iv-party-grid">
            <!-- Bill To -->
            <div class="iv-party-block">
                <div class="iv-party-block-title">Bill To</div>
                <div style="font-weight: 600; margin-bottom: 5px;">{{ $party->name ?? 'N/A' }}</div>
                <div style="font-size: 11px; color: #4b5563; margin-bottom: 5px;">{{ $invoice->billing_address ?? 'Address not available' }}</div>
                <div style="font-size: 11px; color: #4b5563;">Phone: {{ $party->phone ?? 'N/A' }}</div>
                @if($party?->email)
                <div style="font-size: 11px; color: #4b5563;">Email: {{ $party->email }}</div>
                @endif
                @if($showGST && $party?->gst_number)
                <div style="font-size: 11px; font-weight: 600; margin-top: 5px;">GSTIN: {{ $party->gst_number }}</div>
                @endif
            </div>

            <!-- Ship To -->
            <div class="iv-party-block">
                <div class="iv-party-block-title">Ship To</div>
                @if($invoice->shipping_address && $invoice->shipping_address !== $invoice->billing_address)
                    <div style="font-size: 11px; color: #4b5563;">{{ $invoice->shipping_address }}</div>
                @else
                    <div style="font-size: 11px; color: #4b5563;">Same as billing address</div>
                @endif
                @if($invoice->warehouse)
                <div style="margin-top: 8px; font-size: 11px;">
                    <strong>Dispatched From:</strong> {{ $invoice->warehouse->name ?? 'Main Warehouse' }}
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table - Professional Format -->
        <div class="iv-items-section">
            <table class="iv-table">
                <thead>
                    <tr>
                        <th width="30">#</th>
                        <th>Product</th>
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
                        if ($showGST) {
                            $lineTotal += $item->tax_amount ?? 0;
                        }
                        $totalAmount += $lineTotal;
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="product-name-cell">
                            <strong>{{ $item->product_name }}</strong>
                            @if($item->variant_name)
                                <div class="variant-name">{{ $item->variant_name }}</div>
                            @endif
                        </td>
                        @if($showGST)
                        <td>{{ $item->hsn_sac ?: '—' }}</td>
                        @endif
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>₹ {{ number_format($item->mrp_price, 2) }}</td>
                        <td>
                            @if($item->discount > 0)
                                {{ number_format($item->discount, 1) }}%
                            @else
                                —
                            @endif
                        </td>
                        <td>₹ {{ number_format($item->price, 2) }}</td>
                        @if($showGST)
                        <td>{{ number_format($item->tax_percent, 0) }}%</td>
                        @endif
                        <td>₹ {{ number_format($lineTotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="{{ $showGST ? '9' : '7' }}" style="text-align: right;">Total</td>
                        <td><strong>₹ {{ number_format($totalAmount, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Bottom Sections - Notes, Bank, Summary -->
        <div class="iv-bottom-grid">
            <!-- Left: Notes & Bank Details -->
            <div class="iv-left-panel">
                @if($invoice->notes)
                <div class="iv-panel-title">Notes</div>
                <div class="iv-notes">{{ $invoice->notes }}</div>
                @endif

                @if($settings && ($settings->bank_name || $settings->account_number))
                <div class="iv-panel-title" style="margin-top: {{ $invoice->notes ? '15px' : '0' }};">Bank Details</div>
                <div class="iv-bank-details">
                    @if($settings->bank_name)
                    <div class="iv-bank-row">
                        <span class="iv-bank-label">Bank:</span>
                        <span class="iv-bank-value">{{ $settings->bank_name }}</span>
                    </div>
                    @endif
                    @if($settings->account_number)
                    <div class="iv-bank-row">
                        <span class="iv-bank-label">A/c No.:</span>
                        <span class="iv-bank-value">{{ $settings->account_number }}</span>
                    </div>
                    @endif
                    @if($settings->ifsc_code)
                    <div class="iv-bank-row">
                        <span class="iv-bank-label">IFSC:</span>
                        <span class="iv-bank-value">{{ $settings->ifsc_code }}</span>
                    </div>
                    @endif
                    @if($settings->account_name)
                    <div class="iv-bank-row">
                        <span class="iv-bank-label">A/c Name:</span>
                        <span class="iv-bank-value">{{ $settings->account_name }}</span>
                    </div>
                    @endif
                </div>
                @endif

                @if($settings?->terms_and_conditions)
                <div class="iv-panel-title" style="margin-top: 15px;">Terms</div>
                <div class="iv-notes">{{ $settings->terms_and_conditions }}</div>
                @endif
            </div>

            <!-- Right: Amount Summary -->
            <div class="iv-right-panel">
                <div class="iv-panel-title">Amount Summary</div>

                <div class="iv-total-row">
                    <span>Subtotal</span>
                    <span>₹ {{ number_format($invoice->subtotal, 2) }}</span>
                </div>



                {{-- FIX 2: EXTRA DISCOUNT WITH TYPE CHECK --}}
                @if($invoice->extra_discount > 0)
                <div class="iv-total-row">
                    <span>
                        Extra Discount

                    </span>
                    <span> @if($invoice->extra_discount_type === 'percent')
                             {{ number_format($invoice->extra_discount, 1) }}%
                        @else
                            - ₹ {{ number_format($invoice->extra_discount, 2) }}
                        @endif</span>
                </div>
                @endif
                @if($showGST)
                <div class="iv-total-row">
                    <span>Tax</span>
                    <span>+ ₹ {{ number_format($invoice->tax_total, 2) }}</span>
                </div>
                @endif
                @if($invoice->extra_charge > 0)
                <div class="iv-total-row">
                    <span>{{ $invoice->charge_name ?? 'Extra Charge' }}</span>
                    <span>+ ₹ {{ number_format($invoice->extra_charge, 2) }}</span>
                </div>
                @endif

                @if($invoice->round_off != 0)
                <div class="iv-total-row">
                    <span>Round Off</span>
                    <span>₹ {{ number_format($invoice->round_off, 2) }}</span>
                </div>
                @endif

                <div class="iv-total-row grand">
                    <span>Grand Total</span>
                    <span>₹ {{ number_format($invoice->grand_total, 2) }}</span>
                </div>

                @if($invoice->total_paid > 0)
                <div style="margin-top: 15px;">
                    <div class="iv-total-row">
                        <span>Paid</span>
                        <span>₹ {{ number_format($invoice->total_paid, 2) }}</span>
                    </div>
                    <div class="iv-total-row" style="color: #dc2626;">
                        <span>Balance Due</span>
                        <span>₹ {{ number_format($invoice->balance_amount, 2) }}</span>
                    </div>
                </div>
                @endif

                <!-- Amount in Words -->
                <div class="iv-amount-words">
                    <div class="iv-words-label">Amount In Words</div>
                    <div class="iv-words-value">{{ numberToWords($invoice->grand_total) }} Rupees Only</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="iv-footer">
            {{ $settings->footer_note ?? 'This is a computer generated invoice - no signature required' }}
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function showAlert(message, type = 'success') {
    const container = document.getElementById('iv-alert-container');
    const alert = document.createElement('div');
    alert.className = `iv-alert iv-alert-${type}`;
    alert.innerHTML = message;
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 4000);
}

function printInvoice() {
    document.title = '{{ $invoice->invoice_number }}';
    window.print();
}

function downloadPDF() {
    const element = document.getElementById('invoiceToPrint');
    showAlert('Generating PDF...', 'info');

    html2pdf().set({
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: '{{ $invoice->invoice_number }}.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    }).from(element).save()
    .then(() => showAlert('PDF downloaded!', 'success'))
    .catch(() => showAlert('PDF failed!', 'error'));
}

function generateInvoice() {
    if (!confirm('Generate this invoice?')) return;
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = 'Generating...';

    fetch('{{ route("admin.sales.generate", $invoice->_id) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert('Generated!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = 'Generate';
        }
    })
    .catch(() => {
        showAlert('Error!', 'error');
        btn.disabled = false;
        btn.innerHTML = 'Generate';
    });
}

function deleteInvoice() {
    if (!confirm('Delete this draft?')) return;
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = 'Deleting...';

    fetch('{{ route("admin.sales.destroy", $invoice->_id) }}', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert('Deleted!', 'success');
            setTimeout(() => window.location.href = '{{ route("admin.sales.index") }}', 1500);
        } else {
            showAlert(data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = 'Delete';
        }
    })
    .catch(() => {
        showAlert('Error!', 'error');
        btn.disabled = false;
        btn.innerHTML = 'Delete';
    });
}
</script>
@endpush
@endsection
