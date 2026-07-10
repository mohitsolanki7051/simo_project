@extends('layouts.admin')

@section('title', 'Invoice #' . $invoice->invoice_number . ' - Admin Panel')
@section('header-title', 'Sales Invoice ' )

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
            'draft' => [
                'bg' => '#f3f4f6',
                'color' => '#374151',
                'text' => 'Draft'
            ],

            'confirmed' => [
                'bg' => '#dbeafe',
                'color' => '#1e40af',
                'text' => 'Confirmed'
            ],

            'completed' => [
                'bg' => '#d1fae5',
                'color' => '#065f46',
                'text' => 'Completed'
            ],

            'cancelled' => [
                'bg' => '#fee2e2',
                'color' => '#991b1b',
                'text' => 'Cancelled'
            ],

            'partially_returned' => [
                'bg' => '#fef3c7',
                'color' => '#92400e',
                'text' => 'Partial Return'
            ],

            'returned' => [
                'bg' => '#e5e7eb',
                'color' => '#111827',
                'text' => 'Returned'
            ]
        ];
    $is = $invoiceStatusColors[$invoice->status] ?? $invoiceStatusColors['draft'];

    $showGST = $invoice->invoice_type !== 'cash';
     $openingPaid = \App\Models\SalesPayment::where('party_id', (string)$invoice->party_id)
        ->where('payment_type', 'payment_in')
        ->get()
        ->sum(function($payment) {
            return collect($payment->allocations ?? [])
                ->where('type', 'opening_balance')
                ->sum('amount');
        });
    $dynamicOpeningBalance = max(0, (float)($party->opening_balance ?? 0) - $openingPaid);

    $unpaidInvoices = \App\Models\SalesInvoice::where('party_id', $invoice->party_id)
        ->where('status', '!=', 'draft')
        ->where('status', '!=', 'cancelled')
        ->where('payment_status', '!=', 'paid')
        ->where('_id', '!=', (string)$invoice->_id)
        ->get();

    $previousPendingBalance = 0;
    foreach ($unpaidInvoices as $inv) {
        $bal = $inv->balance_amount;
        if ($bal instanceof \MongoDB\BSON\Decimal128) $bal = (float) $bal->__toString();
        $previousPendingBalance += (float) $bal;
    }

    $totalOutstanding = $dynamicOpeningBalance + $previousPendingBalance + (float)($invoice->balance_amount ?? 0);
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
.iv-cancelled-stamp {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 80px;
    font-weight: 900;
    color: rgba(220, 38, 38, 0.18);
    border: 8px solid rgba(220, 38, 38, 0.18);
    border-radius: 12px;
    padding: 10px 30px;
    pointer-events: none;
    white-space: nowrap;
    z-index: 10;
    letter-spacing: 8px;
    font-family: 'Inter', sans-serif;
    text-transform: uppercase;
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
    /* .iv-items-section {
        padding: 15px 25px;
    } */

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
        text-align: center;
        font-size: 9px;
    }

    .iv-table tbody td:first-child {
        text-align: center;
        font-weight: 600;
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
        text-align: center;
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
        #printWrapper, #printWrapper * { visibility: visible !important; }
        #printWrapper {
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
        .page-break {
            page-break-before: always !important;
        }
        .copy-label {
            display: block !important;
        }
    }

    {{-- ============================================
     MOBILE RESPONSIVE STYLES - paste inside <style> tag ke andar
     ============================================ --}}
@media (max-width: 640px) {

    /* ACTION BAR - mobile stack */
    .iv-actions-bar {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 8px !important;
        padding: 10px !important;
    }
    .iv-actions-left {
        flex-wrap: wrap;
        gap: 6px;
    }
    .iv-page-title { font-size: 13px !important; }
    .iv-actions-right {
        display: grid !important;
        grid-template-columns: 1fr 1fr 1fr !important;
        gap: 6px !important;
    }
    .iv-btn {
        justify-content: center !important;
        padding: 8px 6px !important;
        font-size: 11px !important;
        flex: 1 !important;
    }

    /* INVOICE CARD */
    .iv-card { border-radius: 10px !important; }

    /* COMPANY HEADER */
    .iv-header { padding: 12px !important; }
    .iv-company-block { gap: 10px !important; }
    .iv-logo-img { height: 44px !important; }
    .iv-logo-placeholder { width: 44px !important; height: 44px !important; font-size: 18px !important; }
    .iv-company-details h2 { font-size: 15px !important; }
    .iv-company-details p { font-size: 10px !important; }
    .iv-company-contact { flex-direction: column !important; gap: 2px !important; font-size: 10px !important; }

    /* TITLE ROW */
    .iv-title-row { padding: 10px 12px 8px !important; }
    .iv-doc-title { font-size: 14px !important; }
    .iv-invoice-number { font-size: 12px !important; }
    .iv-invoice-date { font-size: 10px !important; }

    /* PARTY GRID - 2 col on mobile */
    .iv-party-grid {
        grid-template-columns: 1fr 1fr !important;
        gap: 8px !important;
        padding: 8px !important;
    }
    .iv-party-block { padding: 10px !important; }
    .iv-party-block > div { font-size: 11px !important; }

    /* HIDE TABLE, SHOW MOBILE CARDS */
    .iv-items-section .iv-table { display: none !important; }
    .iv-mobile-cards { display: block !important; }

    /* BOTTOM GRID - stack on mobile */
    /* BOTTOM GRID - full stack on mobile */
    .iv-bottom-grid {
        grid-template-columns: 1fr !important;
        gap: 10px !important;
        padding: 10px !important;
    }

    /* Left panel pehle, right panel baad mein */
    .iv-left-panel {
        order: 2 !important;
    }

    .iv-right-panel {
        order: 1 !important;
    }
}
</style>

<div class="iv-wrap">
    <div id="iv-alert-container"></div>

    <!-- Action Bar - Screen Only -->
    <div class="iv-actions-bar no-print">
        <div class="iv-actions-left">
            <a href="{{ route('admin.sales.index') }}" class="iv-back-btn">← Back</a>
            <h2 class="iv-page-title">{{ $invoice->invoice_type === 'cash' ? 'Cash Memo' : 'Tax Invoice' }} </h2>
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
            @else
                @if(auth()->guard('admin')->check())
                    <a href="{{ route('admin.sales.edit', $invoice->_id) }}" class="iv-btn iv-btn-outline">Edit</a>
                @endif
            @endif

            {{-- ✅ FIX: Cancel button ONLY for unpaid invoices --}}
            @if(($invoice->status === 'confirmed' || $invoice->status === 'completed') && $invoice->payment_status === 'unpaid')
                <button onclick="cancelInvoice()" class="iv-btn" style="background: #dc2626; color: white;">
                    🚫 Cancel
                </button>
            @endif

            <button onclick="printInvoice()" class="iv-btn iv-btn-outline">Print</button>
            <button onclick="downloadPDF()" class="iv-btn iv-btn-primary">PDF</button>
            <button onclick="sendWhatsApp()" class="iv-btn" style="background: #25D366; color: white;">
                📱 WhatsApp
            </button>
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
                        <strong>Sales Executive:</strong> {{ $invoice->salesman->name }}
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
        <div class="iv-items-section" style="position: relative;">
            @if($invoice->status === 'cancelled')
            <div class="iv-cancelled-stamp">CANCELLED</div>
            @endif
            <table class="iv-table">
                <thead>
                    <tr>
                        <th width="30">S. No.</th>
                        <th>Product</th>
                        @if($showGST)<th>HSN</th>@endif
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>MRP</th>
                        <th>Disc%</th>
                        <th>Rate</th>
                        @if($showGST)<th>Tax%</th>@endif
                        <th>Warranty</th>
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
                        $warrantyText = 'No Warranty';
                        if ($item->warranty_type && $item->warranty_type !== 'none' && $item->warranty_period > 0) {
                            $period = $item->warranty_period;
                            $type = $item->warranty_type === 'year' ? 'Year' : 'Month';
                            $warrantyText = $period . ' ' . $type . ($period > 1 ? 's' : '');

                            // Add dates if available
                            if ($item->warranty_start && $item->warranty_end) {
                                $start = \Carbon\Carbon::parse($item->warranty_start)->format('d/m/y');
                                $end = \Carbon\Carbon::parse($item->warranty_end)->format('d/m/y');
                                $warrantyText .= '<br><small style="font-size:8px;">' . $start . ' - ' . $end . '</small>';
                            }
                        }
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
                        <td style="font-size: 9px;">{!! $warrantyText !!}</td>
                        <td>₹ {{ number_format($lineTotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="{{ $showGST ? '10' : '8' }}" style="text-align: right;">Total</td>
                        <td><strong>₹ {{ number_format($totalAmount, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
{{-- MOBILE PRODUCT CARDS --}}
<style>
.iv-mobile-cards { display: none; }
.iv-mc { background:#fff; border:0.5px solid #e5e7eb; border-radius:10px; margin-bottom:8px; overflow:hidden; }
.iv-mc-top { display:flex; align-items:center; gap:8px; padding:10px 10px 7px; }
.iv-mc-sno { width:20px; height:20px; border-radius:50%; background:#f97316; color:#fff; font-size:9px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.iv-mc-info { flex:1; min-width:0; }
.iv-mc-name { font-size:12px; font-weight:600; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.iv-mc-variant { font-size:10px; color:#6b7280; margin-top:1px; }
.iv-mc-hsn { font-size:9px; color:#9ca3af; background:#f3f4f6; border-radius:3px; padding:1px 4px; display:inline-block; margin-top:2px; }
.iv-mc-amt { font-size:14px; font-weight:700; color:#f97316; flex-shrink:0; }
.iv-mc-div { height:0.5px; background:#f3f4f6; margin:0 10px; }
.iv-mc-meta { display:grid; grid-template-columns:1fr 1fr 1fr; padding:7px 10px; gap:4px; }
.iv-mc-meta2 { border-top:0.5px solid #f3f4f6; padding-top:6px !important; }
.iv-mc-lbl { font-size:9px; color:#9ca3af; display:block; }
.iv-mc-val { font-size:11px; font-weight:500; color:#111827; display:block; }
.iv-mc-c { text-align:center; }
.iv-mc-r { text-align:right; }
.iv-mc-disc { background:#fff7ed; color:#c2410c; border:0.5px solid #fed7aa; border-radius:3px; font-size:9px; padding:1px 4px; }
.iv-mc-warranty { border-top:0.5px solid #bbf7d0; background:#f0fdf4; padding:5px 10px; font-size:10px; color:#166534; }
.iv-mc-warranty.no { border-color:#e5e7eb; background:#f9fafb; color:#9ca3af; }
.iv-mc-footer { background:#fff; border:0.5px solid #e5e7eb; border-radius:8px; padding:8px 12px; display:flex; justify-content:space-between; align-items:center; margin-top:2px; }
</style>

<div class="iv-mobile-cards" style="background:#f9fafb;padding:10px;border-top:0.5px solid #e5e7eb;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <span style="font-size:11px;font-weight:600;color:#374151;">Items</span>
        <span style="font-size:10px;color:#6b7280;">{{ $invoice->items->count() }} items · Qty: {{ number_format($totalQuantity, 2) }}</span>
    </div>

    @foreach($invoice->items as $idx => $item)
@php
    $lineTotal = (float)$item->quantity * (float)$item->price;
    if ($showGST) {
        $lineTotal += (float)($item->tax_amount ?? 0);
    }
    $wHas = !empty($item->warranty_type)
            && $item->warranty_type !== 'none'
            && (int)($item->warranty_period ?? 0) > 0;
    $wText = 'No warranty';
    if ($wHas) {
        $p = (int)$item->warranty_period;
        $t = $item->warranty_type === 'year' ? 'Year' : 'Month';
        $wText = $p . ' ' . $t . ($p > 1 ? 's' : '');
        if (!empty($item->warranty_start) && !empty($item->warranty_end)) {
            $s = \Carbon\Carbon::parse($item->warranty_start)->format('d/m/y');
            $e = \Carbon\Carbon::parse($item->warranty_end)->format('d/m/y');
            $wText .= ' · ' . $s . ' – ' . $e;
        }
    }
@endphp
<div class="iv-mc">
    <div class="iv-mc-top">
        <div class="iv-mc-sno">{{ $idx + 1 }}</div>
        <div class="iv-mc-info">
            <div class="iv-mc-name">{{ $item->product_name }}</div>
            @if(!empty($item->variant_name))
                <div class="iv-mc-variant">{{ $item->variant_name }}</div>
            @endif
            @if($showGST && !empty($item->hsn_sac))
                <span class="iv-mc-hsn">HSN: {{ $item->hsn_sac }}</span>
            @endif
        </div>
        <div class="iv-mc-amt">₹ {{ number_format($lineTotal, 2) }}</div>
    </div>
    <div class="iv-mc-div"></div>
    <div class="iv-mc-meta">
        <div>
            <span class="iv-mc-lbl">Qty</span>
            <span class="iv-mc-val">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</span>
        </div>
        <div class="iv-mc-c">
            <span class="iv-mc-lbl">MRP</span>
            <span class="iv-mc-val">₹{{ number_format($item->mrp_price, 2) }}</span>
        </div>
        <div class="iv-mc-r">
            <span class="iv-mc-lbl">Disc</span>
            <span class="iv-mc-val">
                @if((float)($item->discount ?? 0) > 0)
                    <span class="iv-mc-disc">{{ number_format($item->discount, 1) }}%</span>
                @else
                    —
                @endif
            </span>
        </div>
    </div>
    <div class="iv-mc-meta iv-mc-meta2">
        <div>
            <span class="iv-mc-lbl">Rate</span>
            <span class="iv-mc-val">₹{{ number_format($item->price, 2) }}</span>
        </div>
        @if($showGST)
        <div class="iv-mc-c">
            <span class="iv-mc-lbl">GST</span>
            <span class="iv-mc-val">{{ number_format($item->tax_percent ?? 0, 0) }}%</span>
        </div>
        <div class="iv-mc-r">
            <span class="iv-mc-lbl">Tax Amt</span>
            <span class="iv-mc-val">₹{{ number_format($item->tax_amount ?? 0, 2) }}</span>
        </div>
        @endif
    </div>
    <div class="iv-mc-warranty {{ $wHas ? '' : 'no' }}">
        @if($wHas) 🛡 {{ $wText }} @else 🚫 No warranty @endif
    </div>
</div>
@endforeach
    <div class="iv-mc-footer">
        <span style="font-size:11px;color:#6b7280;">Total Qty: {{ number_format($totalQuantity,2) }} pcs</span>
        <span style="font-size:14px;font-weight:700;color:#f97316;">₹ {{ number_format($invoice->grand_total, 2) }}</span>
    </div>
</div>
            

        </div>

        <!-- Bottom Sections - Notes, Bank, Summary -->
  <div class="iv-bottom-grid">
            <!-- Left: Notes, Bank Details, Signature & Stamp -->
            <div class="iv-left-panel">

                {{-- NOTES --}}
                @if($invoice->notes)
                <div class="iv-panel-title">Notes</div>
                <div class="iv-notes">{{ $invoice->notes }}</div>
                @endif

                {{-- BANK DETAILS --}}
                @if($settings && ($settings->bank_name || $settings->account_number))
                <div class="iv-panel-title" style="margin-top: {{ $invoice->notes ? '15px' : '0' }};">Bank Details</div>
                <div class="iv-bank-details">
                    @if($settings->bank_name)
                    <div class="iv-bank-row">
                        <span class="iv-bank-label">Bank:</span>
                        <span class="iv-bank-value">{{ $settings->bank_name }}</span>
                    </div>
                    @endif
                    @if($settings->account_name)
                    <div class="iv-bank-row">
                        <span class="iv-bank-label">A/c Name:</span>
                        <span class="iv-bank-value">{{ $settings->account_name }}</span>
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
                    @if($settings->branch)
                    <div class="iv-bank-row">
                        <span class="iv-bank-label">Branch:</span>
                        <span class="iv-bank-value">{{ $settings->branch }}</span>
                    </div>
                    @endif
                </div>
                @endif

                {{-- TERMS --}}
                @if($settings?->terms_and_conditions)
                <div class="iv-panel-title" style="margin-top: 15px;">Terms & Conditions</div>
                <div class="iv-notes">{{ $settings->terms_and_conditions }}</div>
                @endif

                {{-- SIGNATURE & STAMP --}}
                @if($settings?->signature_path || $settings?->stamp_path)
                <div style="margin-top: 20px; display: flex; gap: 20px; align-items: flex-end;">
                    @if($settings->stamp_path)
                    <div style="text-align: center;">
                        <img src="{{ asset('storage/' . $settings->stamp_path) }}"
                             alt="Stamp"
                             style="max-height: 70px; max-width: 100px; object-fit: contain; opacity: 0.85;">
                        <div style="font-size: 9px; color: #6b7280; margin-top: 4px;">Stamp</div>
                    </div>
                    @endif

                    @if($settings->signature_path)
                    <div style="text-align: center;">
                        <img src="{{ asset('storage/' . $settings->signature_path) }}"
                             alt="Authorized Signature"
                             style="max-height: 55px; max-width: 120px; object-fit: contain;">
                        <div style="font-size: 9px; color: #6b7280; margin-top: 4px; border-top: 1px solid #d1d5db; padding-top: 4px;">
                            Authorized Signature
                        </div>
                    </div>
                    @endif
                </div>
                @else
                {{-- No image uploaded — show placeholder signature line --}}
                <div style="margin-top: 30px;">
                    <div style="border-top: 1px solid #374151; width: 160px; padding-top: 5px;">
                        <div style="font-size: 10px; color: #374151; font-weight: 500;">Authorized Signature</div>
                        <div style="font-size: 9px; color: #6b7280; margin-top: 2px;">{{ $settings->company_name ?? '' }}</div>
                    </div>
                </div>
                @endif

            </div>

            <!-- Right: Amount Summary -->
            <div class="iv-right-panel">
                <div class="iv-panel-title">Amount Summary</div>

                <div class="iv-total-row">
                    <span>Subtotal</span>
                    <span>₹ {{ number_format($invoice->subtotal, 2) }}</span>
                </div>

                @if($invoice->extra_discount > 0)
                <div class="iv-total-row">
                    <span>
                        Extra Discount
                        @if($invoice->extra_discount_type === 'percent')
                            ({{ number_format($invoice->extra_discount, 1) }}%)
                        @endif
                    </span>
                    <span>
                        @if($invoice->extra_discount_type === 'percent')
                            - ₹ {{ number_format($invoice->subtotal * $invoice->extra_discount / 100, 2) }}
                        @else
                            - ₹ {{ number_format($invoice->extra_discount, 2) }}
                        @endif
                    </span>
                </div>
                @endif

                @if($showGST)
                <div class="iv-total-row">
                    <span>Tax</span>
                    <span>+ ₹ {{ number_format($invoice->tax_total, 2) }}</span>
                </div>

                {{-- GST Breakup --}}
                @if($invoice->tax_type === 'intra' && $invoice->cgst_total > 0)
                <div class="iv-total-row" style="font-size: 10px; color: #6b7280; padding-left: 10px;">
                    <span>CGST</span>
                    <span>₹ {{ number_format($invoice->cgst_total, 2) }}</span>
                </div>
                <div class="iv-total-row" style="font-size: 10px; color: #6b7280; padding-left: 10px;">
                    <span>SGST</span>
                    <span>₹ {{ number_format($invoice->sgst_total, 2) }}</span>
                </div>
                @elseif($invoice->tax_type === 'inter' && $invoice->igst_total > 0)
                <div class="iv-total-row" style="font-size: 10px; color: #6b7280; padding-left: 10px;">
                    <span>IGST</span>
                    <span>₹ {{ number_format($invoice->igst_total, 2) }}</span>
                </div>
                @endif
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
                    <span class="iv-total-value">₹ {{ number_format($invoice->grand_total, 2) }}</span>
                </div>

                {{-- PREVIOUS OUTSTANDING BALANCE --}}
                @if($dynamicOpeningBalance > 0 || $previousPendingBalance > 0)
                <div style="margin-top:12px;padding:10px 12px;background:#fff8f0;border:1px solid #fed7aa;border-radius:6px;border-left:3px solid #f97316;">
                    <div style="font-size:11px;font-weight:700;color:#c2410c;margin-bottom:8px;">⚠️ Previous Outstanding</div>

                    @if($dynamicOpeningBalance > 0)
                    <div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px dotted #fed7aa;">
                        <span style="color:#78350f;">Opening Balance</span>
                        <span style="font-weight:600;color:#c2410c;">₹ {{ number_format($dynamicOpeningBalance, 2) }}</span>
                    </div>
                    @endif

                    @if($previousPendingBalance > 0)
                    <div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px dotted #fed7aa;">
                        <span style="color:#78350f;">Other Unpaid Invoices ({{ count($unpaidInvoices) }})</span>
                        <span style="font-weight:600;color:#c2410c;">₹ {{ number_format($previousPendingBalance, 2) }}</span>
                    </div>
                    @endif

                    <div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px dotted #fed7aa;">
                        <span style="color:#78350f;">This Invoice Balance</span>
                        <span style="font-weight:600;color:#c2410c;">₹ {{ number_format($invoice->balance_amount ?? 0, 2) }}</span>
                    </div>

                    <div style="display:flex;justify-content:space-between;font-size:12px;padding:6px 0 0 0;margin-top:4px;">
                        <span style="font-weight:700;color:#92400e;">Total Outstanding</span>
                        <span style="font-weight:700;color:#dc2626;font-size:13px;">₹ {{ number_format($totalOutstanding, 2) }}</span>
                    </div>
                </div>
                @endif

                {{-- PAYMENT STATUS --}}
                <div style="margin-top: 15px;">
                    <div class="iv-total-row">
                        <span>Total Paid</span>
                        <span>₹ {{ number_format($invoice->total_paid ?? 0, 2) }}</span>
                    </div>
                    <div class="iv-total-row" style="color: #dc2626;">
                        <span>Balance Due</span>
                        <span>₹ {{ number_format($invoice->balance_amount ?? 0, 2) }}</span>
                    </div>
                </div>

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

    // Cleanup old wrapper
    const old = document.getElementById('printWrapper');
    if (old) old.remove();

    // Original invoice element
    const original = document.getElementById('invoiceToPrint');

    // Create main wrapper
    const wrapper = document.createElement('div');
    wrapper.id = 'printWrapper';

    // --- COPY 1: ORIGINAL ---
    const wrap1 = document.createElement('div');
    wrap1.style.position = 'relative';

    const label1 = document.createElement('div');
    label1.className = 'copy-label';
    label1.style.cssText = 'display:none; position:absolute; top:15px; right:25px; background:#1e40af; color:white; padding:5px 14px; border-radius:4px; font-size:12px; font-weight:700; z-index:99; letter-spacing:0.5px;';
    label1.textContent = 'ORIGINAL FOR RECIPIENT';

    wrap1.appendChild(label1);
    wrap1.appendChild(original.cloneNode(true));

    // --- PAGE BREAK ---
    const pageBreak = document.createElement('div');
    pageBreak.className = 'page-break';

    // --- COPY 2: DUPLICATE ---
    const wrap2 = document.createElement('div');
    wrap2.style.position = 'relative';

    const label2 = document.createElement('div');
    label2.className = 'copy-label';
    label2.style.cssText = 'display:none; position:absolute; top:15px; right:25px; background:#dc2626; color:white; padding:5px 14px; border-radius:4px; font-size:12px; font-weight:700; z-index:99; letter-spacing:0.5px;';
    label2.textContent = 'DUPLICATE FOR TRANSPORTER';

    wrap2.appendChild(label2);
    wrap2.appendChild(original.cloneNode(true));

    // Append all to wrapper
    wrapper.appendChild(wrap1);
    wrapper.appendChild(pageBreak);
    wrapper.appendChild(wrap2);
    document.body.appendChild(wrapper);

    // Print
    window.print();

    // Cleanup after print
    setTimeout(() => {
        const pw = document.getElementById('printWrapper');
        if (pw) pw.remove();
    }, 1500);
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

function sendWhatsApp() {
    const phone = '{{ $party->phone ?? "" }}';

    if (!phone) {
        showAlert('Customer phone number not available!', 'error');
        return;
    }

    let cleanPhone = phone.replace(/\D/g, '');
    if (cleanPhone.length === 10) {
        cleanPhone = '91' + cleanPhone;
    }

    const invoiceNumber = '{{ $invoice->invoice_number }}';
    const partyName     = '{{ $party->name ?? "Customer" }}';
    const grandTotal    = '{{ number_format($invoice->grand_total, 2) }}';
    const invoiceDate   = '{{ $invoice->invoice_date->format("d/m/Y") }}';
    const dueDate       = '{{ $invoice->due_date ? $invoice->due_date->format("d/m/Y") : "N/A" }}';
    const paymentStatus = '{{ ucfirst($invoice->payment_status) }}';

    @if($invoice->public_token)
        const invoiceLink = '{{ url("/invoice/" . $invoice->public_token) }}';
    @else
        const invoiceLink = null;
    @endif

    if (!invoiceLink) {
        showAlert('Public link not available for this invoice. Please regenerate it.', 'error');
        return;
    }

    const message =
`Hello ${partyName},

Your invoice details are below:

Invoice No: ${invoiceNumber}
Date: ${invoiceDate}
Due Date: ${dueDate}
Amount: Rs. ${grandTotal}
Payment Status: ${paymentStatus}

View & Download your invoice here:
${invoiceLink}

Thank you for your business!`;

    const encodedMessage = encodeURIComponent(message);
    const whatsappUrl = 'https://wa.me/' + cleanPhone + '?text=' + encodedMessage;
    window.open(whatsappUrl, '_blank');
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
function cancelInvoice() {
    if (!confirm('⚠️ Cancel this invoice?\n\nThis will:\n• Mark invoice as CANCELLED\n• Return all items to stock\n• This action CANNOT be undone!\n\nAre you sure?')) return;

    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = 'Cancelling...';

    fetch('{{ route("admin.sales.cancel", $invoice->_id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert('✅ Invoice cancelled! Stock returned.', 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert('❌ ' + data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = 'Cancel';
        }
    })
    .catch(() => {
        showAlert('❌ Error cancelling invoice!', 'error');
        btn.disabled = false;
        btn.innerHTML = 'Cancel';
    });
}
</script>
@endpush
@endsection
