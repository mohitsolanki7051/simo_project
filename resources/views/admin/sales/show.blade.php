@extends('layouts.admin')

@section('title', 'Invoice #' . $invoice->invoice_number . ' - Admin Panel')
@section('header-title', 'Sales Invoice #' . $invoice->invoice_number)

@section('content')
<div class="invoice-view-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header Actions -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Invoice #{{ $invoice->invoice_number }}</h2>
            <p>Invoice Date: {{ $invoice->invoice_date->format('d/m/Y') }}</p>
        </div>
        <div class="header-right">
            <button onclick="printInvoice()" class="btn-small btn-primary">
                <span class="btn-icon">🖨️</span>
                Print Invoice
            </button>
            <button onclick="downloadInvoice()" class="btn-small btn-secondary">
                <span class="btn-icon">📥</span>
                Download PDF
            </button>
            <a href="{{ route('admin.sales.index') }}" class="btn-small btn-secondary">
                ← Back to Invoices
            </a>
        </div>
    </div>

    <!-- Invoice Display Area -->
    <div class="invoice-display" id="invoiceToPrint">
        <!-- Fetch invoice settings -->
        @php
            $settings = \App\Models\InvoiceSetting::first();
        @endphp

        <!-- Invoice Header with Company Details -->
        <div class="invoice-header">
            @if($settings && $settings->logo_path)
            <div class="company-logo">
                <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Company Logo">
            </div>
            @endif

            <div class="company-details">
                <h1 class="company-name">{{ $settings->company_name ?? 'Your Company Name' }}</h1>
                <p class="company-address">{{ $settings->company_address ?? 'Company Address' }}</p>
                <div class="contact-info">
                    @if($settings && $settings->company_phone)
                    <span>📞 {{ $settings->company_phone }}</span>
                    @endif
                    @if($settings && $settings->company_email)
                    <span>✉️ {{ $settings->company_email }}</span>
                    @endif
                </div>
                <div class="tax-info">
                    @if($settings && $settings->gstin)
                    <span>GSTIN: {{ $settings->gstin }}</span>
                    @endif
                    @if($settings && $settings->pan)
                    <span>PAN: {{ $settings->pan }}</span>
                    @endif
                </div>
            </div>

            <div class="invoice-meta">
                <h2 class="invoice-title">TAX INVOICE</h2>
                <div class="invoice-number">
                    <strong>Invoice #:</strong> {{ $invoice->invoice_number }}
                </div>
                <div class="invoice-date">
                    <strong>Date:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}
                </div>
                @if($invoice->due_date)
                <div class="due-date">
                    <strong>Due Date:</strong> {{ $invoice->due_date->format('d/m/Y') }}
                </div>
                @endif
            </div>
        </div>

        <div class="invoice-divider"></div>

        <!-- Bill To & Ship To -->
        <div class="address-section">
            <div class="address-col">
                <h3 class="address-title">Bill To</h3>
                <div class="address-content">
                    <strong>{{ $invoice->customer->name }}</strong><br>
                    {{ $invoice->billing_address }}<br>
                    @if($invoice->customer->phone)
                    📞 {{ $invoice->customer->phone }}<br>
                    @endif
                    @if($invoice->customer->email)
                    ✉️ {{ $invoice->customer->email }}<br>
                    @endif
                    @if($invoice->customer->gst_number)
                    GST: {{ $invoice->customer->gst_number }}
                    @endif
                </div>
            </div>

            @if($invoice->shipping_address && $invoice->shipping_address != $invoice->billing_address)
            <div class="address-col">
                <h3 class="address-title">Ship To</h3>
                <div class="address-content">
                    {{ $invoice->shipping_address }}
                </div>
            </div>
            @endif

            <div class="address-col">
                <h3 class="address-title">Other Details</h3>
                <div class="address-content">
                    @if($invoice->po_number)
                    <strong>PO Number:</strong> {{ $invoice->po_number }}<br>
                    @endif
                    @if($invoice->vehicle_no)
                    <strong>Vehicle No:</strong> {{ $invoice->vehicle_no }}<br>
                    @endif
                    @if($invoice->colours)
                    <strong>Colours:</strong> {{ $invoice->colours }}<br>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="items-table-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="th-sno">#</th>
                        <th class="th-description">Description</th>
                        <th class="th-hsn">HSN/SAC</th>
                        <th class="th-qty">Qty</th>
                        <th class="th-warranty">Warranty</th>
                        <th class="th-price">Rate</th>
                        <th class="th-discount">Disc %</th>
                        <th class="th-tax">Tax %</th>
                        <th class="th-amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="item-description">
                            <strong>{{ $item->product_name }}</strong>
                            @if($item->variant_name)
                            <br><small>{{ $item->variant_name }}</small>
                            @endif
                            @if($item->sku)
                            <br><small>SKU: {{ $item->sku }}</small>
                            @endif
                        </td>
                        <td>{{ $item->hsn_sac ?: '-' }}</td>
                        <td>{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                        <td>
                            @if($item->warranty_type != 'none')
                            {{ $item->warranty_period }} {{ $item->warranty_type }}
                            @else
                            -
                            @endif
                        </td>
                        <td>₹ {{ number_format($item->price, 2) }}</td>
                        <td>{{ number_format($item->discount, 2) }}%</td>
                        <td>{{ number_format($item->tax_percent, 2) }}%</td>
                        <td>₹ {{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="notes-col">
                @if($settings && $settings->terms_and_conditions)
                <div class="terms-box">
                    <h4>Terms & Conditions:</h4>
                    <p>{{ $settings->terms_and_conditions }}</p>
                </div>
                @endif

                @if($invoice->notes)
                <div class="notes-box">
                    <h4>Notes:</h4>
                    <p>{{ $invoice->notes }}</p>
                </div>
                @endif
            </div>

            <div class="totals-col">
                <div class="totals-table">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>₹ {{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    @if($invoice->discount_total > 0)
                    <div class="total-row">
                        <span>Discount:</span>
                        <span>- ₹ {{ number_format($invoice->discount_total, 2) }}</span>
                    </div>
                    @endif
                    <div class="total-row">
                        <span>Tax:</span>
                        <span>₹ {{ number_format($invoice->tax_total, 2) }}</span>
                    </div>
                    <div class="total-row total-amount">
                        <span>Grand Total:</span>
                        <span>₹ {{ number_format($invoice->grand_total, 2) }}</span>
                    </div>
                    <div class="total-row">
                        <span>Total Paid:</span>
                        <span>₹ {{ number_format($invoice->total_paid, 2) }}</span>
                    </div>
                    <div class="total-row">
                        <span>Balance Due:</span>
                        <span>₹ {{ number_format($invoice->balance_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Details -->
        @if($settings && $settings->bank_name)
        <div class="bank-details">
            <h4>Bank Details:</h4>
            <div class="bank-info">
                <strong>{{ $settings->bank_name }}</strong><br>
                Account Name: {{ $settings->account_name }}<br>
                Account No: {{ $settings->account_number }}<br>
                IFSC: {{ $settings->ifsc_code }}
                @if($settings->branch)
                <br>Branch: {{ $settings->branch }}
                @endif
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="signatures">
                @if($settings && $settings->signature_path)
                <div class="signature-box">
                    <img src="{{ asset('storage/' . $settings->signature_path) }}"
                         alt="Authorized Signature"
                         style="max-height: 60px;">
                    <div class="signature-label">Authorized Signature</div>
                </div>
                @endif

                @if($settings && $settings->stamp_path)
                <div class="stamp-box">
                    <img src="{{ asset('storage/' . $settings->stamp_path) }}"
                         alt="Company Stamp"
                         style="max-height: 60px;">
                </div>
                @endif
            </div>

            <div class="footer-note">
                {{ $settings->footer_note ?? 'This is a computer generated invoice' }}
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .invoice-view-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 11px;
        line-height: 1.3;
        color: #333;
    }

    .invoice-display {
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 20px;
        margin-top: 15px;
        position: relative;
    }

    /* Invoice Header */
    .invoice-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .company-logo img {
        max-height: 60px;
        max-width: 150px;
    }

    .company-details {
        flex: 1;
        text-align: center;
    }

    .company-name {
        font-size: 16px;
        font-weight: bold;
        margin: 0 0 5px 0;
        color: #333;
    }

    .company-address {
        font-size: 10px;
        color: #666;
        margin: 0 0 5px 0;
        line-height: 1.4;
    }

    .contact-info {
        font-size: 9px;
        color: #555;
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 5px;
    }

    .tax-info {
        font-size: 9px;
        color: #555;
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    .invoice-meta {
        text-align: right;
    }

    .invoice-title {
        font-size: 14px;
        font-weight: bold;
        color: #333;
        margin: 0 0 10px 0;
    }

    .invoice-number,
    .invoice-date,
    .due-date {
        font-size: 10px;
        color: #555;
        margin-bottom: 3px;
    }

    .invoice-divider {
        height: 1px;
        background: #ddd;
        margin: 15px 0;
    }

    /* Address Section */
    .address-section {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .address-col {
        flex: 1;
        min-width: 200px;
    }

    .address-title {
        font-size: 11px;
        font-weight: bold;
        color: #333;
        margin: 0 0 8px 0;
        border-bottom: 1px solid #eee;
        padding-bottom: 3px;
    }

    .address-content {
        font-size: 10px;
        color: #555;
        line-height: 1.4;
    }

    /* Items Table */
    .items-table-section {
        margin: 20px 0;
        overflow-x: auto;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }

    .items-table th {
        background: #f8f9fa;
        padding: 8px 10px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 1px solid #ddd;
        border-top: 1px solid #ddd;
    }

    .items-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
        vertical-align: top;
    }

    .items-table tr:last-child td {
        border-bottom: 1px solid #ddd;
    }

    .th-sno { width: 30px; }
    .th-description { width: 250px; }
    .th-hsn { width: 70px; }
    .th-qty { width: 60px; }
    .th-warranty { width: 70px; }
    .th-price { width: 80px; }
    .th-discount { width: 60px; }
    .th-tax { width: 60px; }
    .th-amount { width: 90px; }

    .item-description {
        font-size: 10px;
        color: #333;
    }

    .item-description small {
        font-size: 9px;
        color: #666;
    }

    /* Summary Section */
    .summary-section {
        display: flex;
        gap: 20px;
        margin: 20px 0;
        flex-wrap: wrap;
    }

    .notes-col {
        flex: 2;
        min-width: 300px;
    }

    .totals-col {
        flex: 1;
        min-width: 200px;
    }

    .terms-box,
    .notes-box {
        background: #f9f9f9;
        border: 1px solid #eee;
        border-radius: 3px;
        padding: 10px;
        margin-bottom: 10px;
    }

    .terms-box h4,
    .notes-box h4 {
        font-size: 11px;
        font-weight: bold;
        color: #333;
        margin: 0 0 5px 0;
    }

    .terms-box p,
    .notes-box p {
        font-size: 10px;
        color: #555;
        margin: 0;
        line-height: 1.4;
    }

    .totals-table {
        background: #f9f9f9;
        border: 1px solid #eee;
        border-radius: 3px;
        padding: 15px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 10px;
        color: #555;
    }

    .total-row.total-amount {
        font-size: 12px;
        font-weight: bold;
        color: #333;
        padding-top: 8px;
        border-top: 1px solid #ddd;
        margin-top: 8px;
    }

    /* Bank Details */
    .bank-details {
        background: #f9f9f9;
        border: 1px solid #eee;
        border-radius: 3px;
        padding: 10px;
        margin: 20px 0;
    }

    .bank-details h4 {
        font-size: 11px;
        font-weight: bold;
        color: #333;
        margin: 0 0 8px 0;
    }

    .bank-info {
        font-size: 10px;
        color: #555;
        line-height: 1.4;
    }

    /* Footer */
    .invoice-footer {
        margin-top: 30px;
        padding-top: 15px;
        border-top: 1px solid #ddd;
    }

    .signatures {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 15px;
    }

    .signature-box,
    .stamp-box {
        text-align: center;
    }

    .signature-label {
        font-size: 9px;
        color: #666;
        margin-top: 5px;
    }

    .footer-note {
        text-align: center;
        font-size: 9px;
        color: #666;
        font-style: italic;
        margin-top: 10px;
    }

    /* Print Styles */
    @media print {
        .page-header,
        .btn-small,
        #alertContainer {
            display: none !important;
        }

        .invoice-display {
            border: none;
            padding: 0;
        }

        body {
            background: white !important;
            font-size: 10px !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function printInvoice() {
        const printContent = document.getElementById('invoiceToPrint').innerHTML;
        const originalContent = document.body.innerHTML;

        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        window.location.reload();
    }

    function downloadInvoice() {
        // You can implement PDF download using libraries like jsPDF or make an API call
        showAlert('PDF download feature coming soon!', 'info');
    }

    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }
</script>
@endpush
@endsection
