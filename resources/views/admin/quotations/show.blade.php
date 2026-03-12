{{-- resources/views/admin/quotations/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quotation #' . $quotation->quotation_number . ' - Admin Panel')
@section('header-title', 'Quotation #' . $quotation->quotation_number)

@section('content')

@php
    $extraDiscountAmount = 0;
    if ($quotation->extra_discount_type === 'percent' && $quotation->extra_discount > 0) {
        $extraDiscountAmount = ($quotation->subtotal * $quotation->extra_discount) / 100;
    } else {
        $extraDiscountAmount = $quotation->extra_discount;
    }
    if ($quotation->invoice_type === 'cash') {
        $settings = \App\Models\CashMemoInvoiceSetting::first();
    } else {
        $settings = \App\Models\InvoiceSetting::first();
    }
    $party = $quotation->party;
    $partyType = $party->party_type ?? 'customer';

    $quotationStatusColors = [
        'draft'    => ['bg' => '#f3f4f6', 'color' => '#374151', 'text' => 'Draft'],
        'sent'     => ['bg' => '#dbeafe', 'color' => '#1e40af', 'text' => 'Sent'],
        'accepted' => ['bg' => '#d1fae5', 'color' => '#065f46', 'text' => 'Accepted'],
        'rejected' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'text' => 'Rejected'],
        'expired'  => ['bg' => '#fff3cd', 'color' => '#856404', 'text' => 'Expired'],
    ];
    $qs = $quotationStatusColors[$quotation->status] ?? $quotationStatusColors['draft'];

    $showGST = $quotation->invoice_type !== 'cash';

    // Calculate total items quantity
    $totalQuantity = $quotation->items->sum('quantity');

    // Helper function for number to words
    function numberToWords($num) {
        $num = (int)$num;

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

        if ($num >= 10000000) {
            $crore = floor($num / 10000000);
            $words[] = convertToWords($crore, $ones, $tens) . ' Crore';
            $num %= 10000000;
        }

        if ($num >= 100000) {
            $lakh = floor($num / 100000);
            $words[] = convertToWords($lakh, $ones, $tens) . ' Lakh';
            $num %= 100000;
        }

        if ($num >= 1000) {
            $thousand = floor($num / 1000);
            $words[] = convertToWords($thousand, $ones, $tens) . ' Thousand';
            $num %= 1000;
        }

        if ($num >= 100) {
            $hundred = floor($num / 100);
            $words[] = $ones[$hundred] . ' Hundred';
            $num %= 100;
        }

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
    /* PROFESSIONAL QUOTATION STYLES - EXACT COPY OF SALES INVOICE */
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
    .iv-btn-success { background: #28a745; color: white; }
    .iv-btn-success:hover { background: #218838; }

    .iv-draft-warning {
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
        color: #92400e;
        border: 1px solid #fde68a;
    }
/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
}

.modal-content {
    position: relative;
    background: white;
    border-radius: 6px;
    padding: 0;
    width: 90%;
    max-width: 480px;
    max-height: 90vh;
    overflow-y: auto;
    animation: modalFadeIn 0.2s ease;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.modal-sm { max-width: 400px; }

@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    background: #f8f9fa;
    border-radius: 6px 6px 0 0;
}

.modal-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: white;
    flex-shrink: 0;
    background: #f97316;
}

.modal-title-section { flex: 1; }
.modal-title { font-size: 14px; font-weight: 600; color: #333; margin: 0; }
.modal-subtitle { font-size: 10px; color: #666; margin-top: 2px; }

.modal-close {
    background: none; border: none; font-size: 18px; color: #666; cursor: pointer;
    width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
    border-radius: 4px; transition: all 0.2s;
}
.modal-close:hover { background: #eee; color: #333; }

.modal-body { padding: 20px; }

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 15px 20px;
    border-top: 1px solid #ddd;
    background: #fafafa;
    border-radius: 0 0 6px 6px;
}

.btn-modal {
    padding: 7px 16px; border: none; border-radius: 3px;
    font-size: 10px; font-weight: 500; cursor: pointer;
    transition: all 0.2s; min-width: 70px;
}
.btn-cancel  { background: #f8f9fa; color: #333; border: 1px solid #ccc; }
.btn-cancel:hover { background: #e9ecef; }
.btn-primary { background: #f97316; color: white; border: 1px solid #f97316; }
.btn-primary:hover { background: #ea580c; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
    /* QUOTATION CARD */
    .iv-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        max-width: 1100px;
        margin: 0 auto;
    }

    /* Header - Company Info */
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

    /* Title Row */
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

    .iv-quotation-number {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        text-align: right;
    }

    .iv-quotation-date {
        font-size: 12px;
        color: #6b7280;
        margin-top: 2px;
    }

    /* Party Info Grid */
    .iv-party-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        padding: 15px 25px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
    }
/* Bank Details Styles */
.iv-bank-details {
    font-size: 11px;
    margin-top: 5px;
}

.iv-bank-row {
    display: flex;
    margin-bottom: 5px;
    font-size: 11px;
}

.iv-bank-label {
    width: 90px;
    color: #6b7280;
    flex-shrink: 0;
}

.iv-bank-value {
    font-weight: 500;
    color: #111827;
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

    /* Items Table */
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

    /* Status Badge */
    .iv-status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    /* Validity Info */
    .iv-validity {
        margin-top: 8px;
        padding: 8px;
        background: #fff3e6;
        border-radius: 4px;
        font-size: 10px;
        color: #a05000;
        border: 1px solid #fa8725;
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
        #quotationToPrint, #quotationToPrint * { visibility: visible !important; }
        #quotationToPrint {
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
            <a href="{{ route('admin.quotations.index') }}" class="iv-back-btn">← Back</a>
            <h2 class="iv-page-title">{{ $quotation->invoice_type === 'cash' ? 'Cash Memo Quotation' : 'GST Quotation' }} #{{ $quotation->quotation_number }}</h2>
            <span class="iv-status-pill" style="background: {{ $qs['bg'] }}; color: {{ $qs['color'] }};">
                {{ $qs['text'] }}
            </span>
        </div>
        <div class="iv-actions-right">
            @if($quotation->status === 'draft')
                <a href="{{ route('admin.quotations.edit', $quotation->_id) }}" class="iv-btn iv-btn-outline">✏ Edit</a>
                <button onclick="deleteQuotation()" class="iv-btn" style="background: #dc2626; color: white;">🗑 Delete</button>
                <button onclick="updateStatus('sent')" class="iv-btn iv-btn-outline">📤 Mark Sent</button>
            @endif

            {{-- Sent ke baad Accept ya Reject --}}
            @if($quotation->status === 'sent' && !$quotation->isExpired())
                <button onclick="updateStatus('accepted')" class="iv-btn iv-btn-success">✅ Accept</button>
                <button onclick="updateStatus('rejected')" class="iv-btn" style="background: #dc2626; color: white;">❌ Reject</button>
            @endif

            {{-- Convert to Invoice - sirf accepted par --}}
            @if($quotation->status === 'accepted' && !$quotation->converted_to_invoice)
                <button onclick="confirmConvert()" class="iv-btn iv-btn-success">🔄 Convert to Invoice</button>
            @endif

            {{-- Expired warning - but no action --}}
            @if($quotation->isExpired() && $quotation->status !== 'expired')
                <span class="iv-btn" style="background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; cursor: default;">⚠ Expired</span>
            @endif

            <button onclick="printQuotation()" class="iv-btn iv-btn-outline">🖨 Print</button>
            <button onclick="downloadPDF()" class="iv-btn iv-btn-primary">📄 PDF</button>
            <button onclick="sendWhatsApp()" class="iv-btn" style="background: #25D366; color: white;">
                📱 WhatsApp
            </button>
        </div>
    </div>

    @if($quotation->status === 'draft')
    <div class="iv-draft-warning no-print">
        <strong>DRAFT:</strong> This quotation has not been sent to the customer yet.
    </div>
    @endif

    @if($quotation->isExpired() && $quotation->status !== 'expired')
    <div class="iv-draft-warning no-print" style="background: #fff3cd; border-left-color: #856404; color: #856404;">
        <strong>EXPIRED:</strong> This quotation has expired. Please create a new one.
    </div>
    @endif

    <!-- MAIN QUOTATION - EXACT COPY OF SALES INVOICE LAYOUT -->
    <div class="iv-card" id="quotationToPrint">

        <!-- Header - Company Info -->
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

                    @if($quotation->salesman && $quotation->salesman->name)
                    <div style="margin-top: 8px; font-size: 11px; background: #f3f4f6; padding: 4px 8px; border-radius: 4px; display: inline-block;">
                        <strong>Sales Executive:</strong> {{ $quotation->salesman->name }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quotation Title & Number -->
        <div class="iv-title-row">
            <div class="iv-doc-title">{{ $quotation->invoice_type === 'cash' ? 'CASH MEMO QUOTATION' : 'GST QUOTATION' }}</div>
            <div>
                <div class="iv-quotation-number">{{ $quotation->quotation_number }}</div>
                <div class="iv-quotation-date">Date: {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d/m/Y') }}</div>
                @if($quotation->valid_till)
                <div class="iv-quotation-date">Valid Till: {{ \Carbon\Carbon::parse($quotation->valid_till)->format('d/m/Y') }}</div>
                @endif
            </div>
        </div>

        <!-- Party Information -->
        <div class="iv-party-grid">
            <!-- Bill To -->
            <div class="iv-party-block">
                <div class="iv-party-block-title">Bill To</div>
                <div style="font-weight: 600; margin-bottom: 5px;">{{ $party->name ?? 'N/A' }}</div>
                <div style="font-size: 11px; color: #4b5563; margin-bottom: 5px;">{{ $quotation->billing_address ?? 'Address not available' }}</div>
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
                @if($quotation->shipping_address && $quotation->shipping_address !== $quotation->billing_address)
                    <div style="font-size: 11px; color: #4b5563;">{{ $quotation->shipping_address }}</div>
                @else
                    <div style="font-size: 11px; color: #4b5563;">Same as billing address</div>
                @endif
                @if($quotation->warehouse)
                <div style="margin-top: 8px; font-size: 11px;">
                    <strong>Warehouse:</strong> {{ $quotation->warehouse->name ?? 'Main Warehouse' }}
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="iv-items-section">
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
                    @foreach($quotation->items as $idx => $item)
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
                            <div style="display: flex; align-items: center; gap: 8px;">
                                @php
                                    $productImage = null;

                                    if ($item->product_type === 'simple') {
                                        $product = \App\Models\SimpleProduct::find($item->product_id);

                                        if ($product && $product->base_image) {
                                            $productImage = $product->base_image;
                                        }

                                    } else {
                                        $product = \App\Models\VariantProduct::find($item->product_id);

                                        if ($product && is_array($product->variants)) {

                                            foreach ($product->variants as $variant) {

                                                if ((string)$variant['_id'] === (string)$item->variant_id) {

                                                    if (!empty($variant['base_image'])) {
                                                        $productImage = $variant['base_image'];
                                                    }

                                                    break;
                                                }

                                            }

                                        }
                                    }
                                @endphp
                                @if($productImage)
                                    <img src="{{ asset('storage/' . $productImage) }}" alt="{{ $item->product_name }}"
                                        style="width: 35px; height: 35px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;">
                                @else
                                    <div style="width: 35px; height: 35px; background: #f3f4f6; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 10px; border: 1px solid #e5e7eb;">
                                        📷
                                    </div>
                                @endif
                                <div>
                                    <strong>{{ $item->product_name }}</strong>
                                    @if($item->variant_name)
                                        <div class="variant-name">{{ $item->variant_name }}</div>
                                    @endif
                                </div>
                            </div>
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
        </div>

        <!-- Bottom Sections - Notes & Summary -->
        <div class="iv-bottom-grid">
            <!-- Left: Notes & Validity -->
            <div class="iv-left-panel">
                @if($quotation->notes)
                <div class="iv-panel-title">Notes</div>
                <div class="iv-notes">{{ $quotation->notes }}</div>
                @endif

               @if($settings && ($settings->bank_name || $settings->account_number))
                <div class="iv-panel-title" style="margin-top: {{ $quotation->notes ? '15px' : '0' }};">Bank Details</div>
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
                <div class="iv-panel-title">Quotation Summary</div>

                <div class="iv-total-row">
                    <span>Total MRP</span>
                    <span>₹ {{ number_format($quotation->total_mrp, 2) }}</span>
                </div>

                <div class="iv-total-row">
                    <span>Total Discount</span>
                    <span>- ₹ {{ number_format($quotation->discount_amount, 2) }}</span>
                </div>

                <div class="iv-total-row">
                    <span class="fw-semibold">Subtotal</span>
                    <span class="fw-semibold">₹ {{ number_format($quotation->subtotal, 2) }}</span>
                </div>

                @if($showGST && $quotation->tax_total > 0)
                    <div class="iv-total-row">
                        <span>Tax</span>
                        <span>+ ₹ {{ number_format($quotation->tax_total, 2) }}</span>
                    </div>
                @endif

                @if($quotation->extra_discount > 0)
                <div class="iv-total-row">
                    <span>
                        Extra Discount
                        @if($quotation->extra_discount_type === 'percent')
                            ({{ number_format($quotation->extra_discount, 1) }}%)
                        @endif
                    </span>
                    <span>- ₹ {{ number_format($extraDiscountAmount, 2) }}</span>
                </div>
                @endif

                @if($quotation->extra_charge > 0)
                <div class="iv-total-row">
                    <span>{{ $quotation->charge_name ?? 'Extra Charge' }}</span>
                    <span>+ ₹ {{ number_format($quotation->extra_charge, 2) }}</span>
                </div>
                @endif

                @if($quotation->round_off != 0)
                <div class="iv-total-row">
                    <span>Round Off</span>
                    <span>{{ $quotation->round_off >= 0 ? '+' : '' }} ₹ {{ number_format($quotation->round_off, 2) }}</span>
                </div>
                @endif

                <div class="iv-total-row grand">
                    <span>Grand Total</span>
                    <span>₹ {{ number_format($quotation->grand_total, 2) }}</span>
                </div>

                <!-- Amount in Words -->
                <div class="iv-amount-words">
                    <div class="iv-words-label">Amount In Words</div>
                    <div class="iv-words-value">{{ numberToWords($quotation->grand_total) }} Rupees Only</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="iv-footer">
            {{ $settings->footer_note ?? 'This is a computer generated quotation - no signature required' }}
        </div>
    </div>
</div>

<!-- Confirm Convert Modal -->
<div class="modal" id="convertModal" style="display:none;">
    <div class="modal-overlay" onclick="closeConvertModal()"></div>
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <div class="modal-icon">🔄</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Convert to Invoice</h4>
                <div class="modal-subtitle">Create sales invoice from quotation</div>
            </div>
            <button type="button" class="modal-close" onclick="closeConvertModal()">×</button>
        </div>
        <div class="modal-body">
            <p style="font-size:11px;color:#555;line-height:1.6;margin:0;">
                Convert <strong>{{ $quotation->quotation_number }}</strong> to a Sales Invoice?
            </p>
            <div style="background:#fff3e6;border:1px solid #fa8725;border-radius:4px;padding:10px;margin-top:12px;font-size:10px;color:#a05000;">
                ⚠ A <strong>draft</strong> sales invoice will be created. Stock will remain unchanged until the invoice is confirmed.
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeConvertModal()">Cancel</button>
            <button type="button" class="btn-modal btn-primary" id="confirmConvertBtn" onclick="doConvert()">
                Yes, Convert
            </button>
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

function printQuotation() {
    document.title = '{{ $quotation->quotation_number }}';
    window.print();
}

function downloadPDF() {
    const element = document.getElementById('quotationToPrint');
    showAlert('Generating PDF...', 'info');

    html2pdf().set({
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: '{{ $quotation->quotation_number }}.pdf',
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

    const quotationNumber = '{{ $quotation->quotation_number }}';
    const partyName = '{{ $party->name ?? "Customer" }}';
    const grandTotal = '{{ number_format($quotation->grand_total, 2) }}';
    const quotationDate = '{{ \Carbon\Carbon::parse($quotation->quotation_date)->format("d/m/Y") }}';
    const validTill = '{{ $quotation->valid_till ? \Carbon\Carbon::parse($quotation->valid_till)->format("d/m/Y") : "N/A" }}';

    @if($quotation->public_token)
        const quotationLink = '{{ url("/quotation/" . $quotation->public_token) }}';
    @else
        const quotationLink = null;
        showAlert('Public link not available for this quotation.', 'error');
        return;
    @endif

    const message =
`Hello ${partyName},

Your quotation details are below:

Quotation No: ${quotationNumber}
Date: ${quotationDate}
Valid Till: ${validTill}
Total Amount: Rs. ${grandTotal}

View your quotation here:
${quotationLink}

Please review and let us know if you have any questions.

Thank you for your business!`;

    const encodedMessage = encodeURIComponent(message);
    const whatsappUrl = 'https://wa.me/' + cleanPhone + '?text=' + encodedMessage;
    window.open(whatsappUrl, '_blank');
}

function updateStatus(newStatus) {
    if (!confirm(`Mark this quotation as ${newStatus}?`)) return;

    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = 'Updating...';

    fetch('{{ route("admin.quotations.update-status", $quotation->_id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert(`Status updated to ${newStatus}!`, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = newStatus === 'sent' ? '📤 Mark Sent' : 'Mark ' + newStatus;
        }
    })
    .catch(() => {
        showAlert('Error updating status!', 'error');
        btn.disabled = false;
        btn.innerHTML = newStatus === 'sent' ? '📤 Mark Sent' : 'Mark ' + newStatus;
    });
}

function deleteQuotation() {
    if (!confirm('Delete this draft quotation? This action cannot be undone.')) return;

    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = 'Deleting...';

    fetch('{{ route("admin.quotations.destroy", $quotation->_id) }}', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert('Quotation deleted!', 'success');
            setTimeout(() => window.location.href = '{{ route("admin.quotations.index") }}', 1500);
        } else {
            showAlert(data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '🗑 Delete';
        }
    })
    .catch(() => {
        showAlert('Error deleting quotation!', 'error');
        btn.disabled = false;
        btn.innerHTML = '🗑 Delete';
    });
}

// ===================== CONVERT TO INVOICE =====================

function confirmConvert() {
    document.getElementById('convertModal').style.display = 'flex';
}

function closeConvertModal() {
    document.getElementById('convertModal').style.display = 'none';
}

function doConvert() {
    const $btn = document.getElementById('confirmConvertBtn');
    $btn.disabled = true;
    $btn.innerHTML = 'Converting...';

    fetch('{{ route("admin.quotations.convert-to-invoice", $quotation->_id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        closeConvertModal();
        if (data.success) {
            showAlert('Quotation converted to invoice successfully!', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert(data.message || 'Conversion failed.', 'error');
            $btn.disabled = false;
            $btn.innerHTML = 'Yes, Convert';
        }
    })
    .catch(() => {
        closeConvertModal();
        showAlert('Failed to convert quotation.', 'error');
        $btn.disabled = false;
        $btn.innerHTML = 'Yes, Convert';
    });
}

// ===================== DOCUMENT READY =====================

$(document).ready(function() {
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') closeConvertModal();
    });
});
</script>
@endpush
@endsection
