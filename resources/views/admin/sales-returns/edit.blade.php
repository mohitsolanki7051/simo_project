@extends('layouts.admin')

@section('title', 'Edit Sales Return - Admin Panel')
@section('header-title', 'Edit Sales Return')

@section('content')
<div class="return-create-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Edit Sales Return: <span class="return-num">{{ $return->return_number }}</span></h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.sales-returns.show', $return->_id) }}" class="btn-small btn-secondary">
                ← Back to Return
            </a>
        </div>
    </div>

    <!-- Return Form -->
    <div class="return-form-wrapper">
        <form id="salesReturnForm" class="return-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_number" value="{{ $return->return_number }}">
            <input type="hidden" name="warehouse_id" id="warehouseIdInput" value="{{ $return->warehouse_id }}">
            <input type="hidden" name="sales_invoice_id" id="salesInvoiceIdInput" value="{{ $return->sales_invoice_id }}">

            <!-- Invoice Selection Section (Read-only in edit) -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-header-left">
                        <div class="section-icon">📄</div>
                        <div class="section-title">
                            <h3>Invoice Details</h3>
                            <p>Original invoice for this return</p>
                        </div>
                    </div>
                    <span class="invoice-badge">Invoice No.: {{ $return->invoice->invoice_number }}</span>
                </div>

                <div class="section-body">
                    <!-- Selected Invoice Details -->
                    <div id="selectedInvoiceDetails" class="selected-invoice-details">
                        <div class="invoice-header">
                            <div class="invoice-info">
                                <h4 id="invoiceNumberDisplay">{{ $return->invoice->invoice_number }}</h4>
                                <span class="invoice-date" id="invoiceDateDisplay">{{ $return->invoice->invoice_date->format('d-m-Y') }}</span>
                            </div>
                            <div class="party-info">
                                <span class="party-name" id="partyNameDisplay">{{ $return->party->name }}</span>
                                <span class="party-phone" id="partyPhoneDisplay">{{ $return->party->phone ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="warehouse-badge">
                            <span>🏭 <span id="warehouseNameDisplay">{{ $return->warehouse->name }}</span></span>
                        </div>

                        <!-- Return Items Table -->
                        <div class="items-section">
                            <h5>Edit Return Items</h5>
                            <div class="items-table-container">
                                <table class="items-table">
                                    <thead>
                                        <tr>
                                            <th class="th-checkbox">
                                                <input type="checkbox" id="selectAllItems" checked onchange="toggleSelectAll()">
                                            </th>
                                            <th class="th-item">Item</th>
                                            <th class="th-sold-qty text-c">Sold Qty</th>
                                            <th class="th-returned-qty text-c">Already Returned</th>
                                            <th class="th-remaining-qty text-c">Remaining</th>
                                            <th class="th-price text-r">Price (₹)</th>
                                            <th class="th-return-qty text-c">Return Qty</th>
                                            <th class="th-amount text-r">Return Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                    </tbody>
                                    <tfoot>
                                        <tr class="tfoot-main">
                                            <td colspan="6" class="tfoot-total-label">Total:</td>
                                            <td id="totalReturnQty" class="text-c tfoot-qty">{{ $return->total_return_qty }}</td>
                                            <td id="totalReturnAmount" class="text-r tfoot-amount">₹ {{ number_format($return->total_return_amount, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Return Details -->
                        <div class="return-details-section">
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label class="form-label required">Return Date</label>
                                    <input type="date" name="return_date" id="returnDate" class="form-control"
                                           value="{{ $return->return_date->format('Y-m-d') }}" required>
                                </div>
                                <div class="form-group col-6">
                                    <label class="form-label">Reason for Return</label>
                                    <select name="reason" class="form-control">
                                        <option value="">Select Reason</option>
                                        <option value="Damaged Product" {{ $return->reason == 'Damaged Product' ? 'selected' : '' }}>Damaged Product</option>
                                        <option value="Wrong Product" {{ $return->reason == 'Wrong Product' ? 'selected' : '' }}>Wrong Product</option>
                                        <option value="Customer Not Satisfied" {{ $return->reason == 'Customer Not Satisfied' ? 'selected' : '' }}>Customer Not Satisfied</option>
                                        <option value="Defective Product" {{ $return->reason == 'Defective Product' ? 'selected' : '' }}>Defective Product</option>
                                        <option value="Other" {{ $return->reason == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"
                                          placeholder="Add any additional notes...">{{ $return->notes }}</textarea>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-actions">
                            <a href="{{ route('admin.sales-returns.show', $return->_id) }}" class="btn-cancel">
                                Cancel
                            </a>
                            <button type="submit" class="btn-submit" id="submitBtn">
                                Update Return
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* ── Base ─────────────────────────────── */
.return-create-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 11px;
    padding: 16px;
    background: #f4f6f8;
    min-height: 100vh;
}

/* ── Alert ────────────────────────────── */
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
.alert-warning { background: #fffbeb; color: #92400e; border-left: 3px solid #f59e0b; }
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}

/* ── Page Header ──────────────────────── */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    flex-wrap: wrap;
    gap: 10px;
}
.page-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}
.page-title .return-num { color: #f97316; }

.btn-small {
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all .15s;
}
.btn-secondary {
    background: #fff;
    color: #6b7280;
    border: 1px solid #d1d5db;
}
.btn-secondary:hover { background: #f3f4f6; color: #374151; }

/* ── Form Wrapper ─────────────────────── */
.return-form-wrapper {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 14px;
}

/* ── Section Card ─────────────────────── */
.form-section {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    overflow: hidden;
}
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    gap: 10px;
    flex-wrap: wrap;
}
.section-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.section-icon {
    width: 26px;
    height: 26px;
    background: #f97316;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 12px;
    flex-shrink: 0;
}
.section-title h3 {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin: 0;
}
.section-title p {
    font-size: 10px;
    color: #9ca3af;
    margin: 1px 0 0;
}
.section-body { padding: 14px; }

/* Invoice badge in header */
.invoice-badge {
    padding: 4px 11px;
    background: #f97316;
    color: #fff;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 500;
    white-space: nowrap;
}

/* ── Selected Invoice Card ────────────── */
.selected-invoice-details {
    padding: 14px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
}
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
    gap: 10px;
    flex-wrap: wrap;
}
.invoice-info h4 {
    font-size: 13px;
    font-weight: 600;
    color: #f97316;
    margin: 0 0 2px;
}
.invoice-info .invoice-date { font-size: 10px; color: #9ca3af; }
.invoice-header .party-info { text-align: center; }
.invoice-header .party-name { display: block; font-size: 11px; font-weight: 500; color: #374151; }
.invoice-header .party-phone { font-size: 9px; color: #9ca3af; }

.warehouse-badge {
    margin-bottom: 14px;
    padding: 5px 12px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 20px;
    color: #c2410c;
    font-size: 10px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* ── Items Section ────────────────────── */
.items-section h5 {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 10px;
}
.items-table-container {
    overflow-x: auto;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    margin-bottom: 14px;
}
.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
    min-width: 700px;
}
.items-table thead tr { background: #f9fafb; }
.items-table thead th {
    padding: 9px 11px;
    font-size: 10px;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}
.items-table thead th.th-item { text-align: left; }
.items-table thead th.th-checkbox { text-align: center; }

.items-table tbody tr { transition: background .1s; }
.items-table tbody tr:hover { background: #fafafa; }
.items-table tbody td {
    padding: 9px 11px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    color: #374151;
}
.items-table tbody tr:last-child td { border-bottom: none; }

/* Column widths */
.th-checkbox      { width: 32px; }
.th-item          { min-width: 180px; }
.th-sold-qty      { width: 65px; }
.th-returned-qty  { width: 100px; }
.th-remaining-qty { width: 75px; }
.th-price         { width: 80px; }
.th-return-qty    { width: 120px; }
.th-amount        { width: 100px; }

/* Item cell */
.item-name    { font-weight: 500; color: #111827; margin-bottom: 2px; }
.item-variant {
    font-size: 9px;
    color: #f97316;
    background: #fff7ed;
    display: inline-block;
    padding: 1px 6px;
    border-radius: 3px;
    margin-top: 2px;
}

/* Value cells */
.remaining-qty      { font-weight: 600; color: #10b981; }
.returned-highlight { color: #f97316; font-weight: 600; }
.price-col          { font-weight: 500; color: #374151; }
.amount-col         { font-weight: 600; color: #111827; }
.item-total         { font-weight: 600; color: #111827; }

/* Qty input */
.qty-control { display: flex; flex-direction: column; align-items: center; gap: 2px; }
.return-qty-input {
    width: 70px;
    padding: 5px 7px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    text-align: center;
    font-size: 11px;
    font-weight: 500;
    color: #111827;
}
.return-qty-input:focus {
    border-color: #f97316;
    outline: none;
    box-shadow: 0 0 0 2px rgba(249,115,22,0.1);
}
.max-hint { font-size: 8px; color: #9ca3af; }

/* Tax / invoice type badges */
.tax-info { display: flex; justify-content: center; gap: 4px; margin-top: 4px; flex-wrap: wrap; }
.tax-badge {
    background: #f97316;
    color: #fff;
    font-size: 8px;
    font-weight: 600;
    padding: 2px 5px;
    border-radius: 9px;
}
.cash-badge { background: #6b7280; }
.tax-amount { color: #10b981; font-size: 9px; font-weight: 600; }

/* ── Table Footer ─────────────────────── */
.items-table tfoot td {
    padding: 8px 11px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}
.tfoot-main { border-top: 2px solid #e5e7eb !important; }
.tfoot-total-label {
    text-align: right;
    font-weight: 600;
    color: #374151;
    font-size: 11px;
}
.tfoot-qty    { font-weight: 700; color: #374151; font-size: 11px; }
.tfoot-amount { font-weight: 700; color: #f97316; font-size: 12px; }

/* ── Summary Footer (JS-generated) ───── */
.summary-footer { }
.summary-row td {
    padding: 7px 11px;
    border-top: 1px dashed #e5e7eb;
    background: #f9fafb;
}
.summary-label {
    text-align: right;
    font-weight: 500;
    color: #6b7280;
    font-size: 10px;
}
.summary-qty    { text-align: center; font-weight: 600; color: #374151; width: 80px; }
.summary-amount { text-align: right; font-weight: 600; width: 100px; font-size: 10px; }
.summary-amount.tax-amount      { color: #10b981; }
.summary-amount.discount-amount { color: #ef4444; }

.grand-total-row td { border-top: 2px solid #fed7aa !important; background: #fff7ed !important; }
.grand-total-label  { font-weight: 700 !important; color: #111827 !important; font-size: 11px !important; }
.grand-total-amount { font-weight: 700 !important; color: #f97316 !important; font-size: 13px !important; }

/* Discount banner */
.discount-info-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 9px 13px;
    margin: 8px 0 13px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 6px;
    font-size: 11px;
    color: #92400e;
}
.discount-icon { font-size: 16px; }
.discount-details strong { color: #b45309; font-weight: 700; }
.discount-details small   { color: #92400e; opacity: .9; font-size: 10px; }

/* ── Return Details Form ──────────────── */
.return-details-section { margin-top: 14px; }
.form-row { display: flex; gap: 14px; margin-bottom: 12px; }
.form-group { flex: 1; }
.form-label {
    display: block;
    font-size: 10px;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 3px;
}
.form-label.required::after { content: ' *'; color: #ef4444; }
.form-control {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 10px;
    color: #111827;
    background: #fff;
    transition: border-color .15s, box-shadow .15s;
}
.form-control:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 2px rgba(249,115,22,0.1);
}

/* ── Form Actions ─────────────────────── */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #e5e7eb;
}
.btn-cancel {
    padding: 7px 16px;
    background: #fff;
    color: #6b7280;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all .15s;
}
.btn-cancel:hover { background: #f3f4f6; color: #374151; }

.btn-submit {
    padding: 7px 16px;
    background: #f97316;
    color: #fff;
    border: 1px solid #f97316;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 500;
    cursor: pointer;
    transition: all .15s;
}
.btn-submit:hover { background: #ea6c0a; }
.btn-submit:disabled { opacity: .6; cursor: not-allowed; }

/* ── Spinner ──────────────────────────── */
.spinner {
    display: inline-block;
    width: 11px;
    height: 11px;
    border: 2px solid rgba(255,255,255,.35);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 0.7s linear infinite;
    margin-right: 5px;
    vertical-align: middle;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Utility ──────────────────────────── */
.text-c       { text-align: center; }
.text-r       { text-align: right; }
.text-success { color: #10b981; font-weight: 500; }
.text-warning { color: #f97316; font-weight: 500; }
.text-muted   { color: #9ca3af; font-size: 8px; }

/* ── Responsive ───────────────────────── */
@media (max-width: 768px) {
    .form-row { flex-direction: column; gap: 10px; }
    .items-table { font-size: 9px; }
    .items-table th, .items-table td { padding: 6px 8px; }
}
</style>
@endpush

@push('scripts')
<script>
let invoiceItems = [];
let isSubmitting = false;
let selectedInvoice = null;

$(document).ready(function() {
    // Load invoice details
    loadInvoiceDetails();
});

function loadInvoiceDetails() {
    const invoiceId = $('#salesInvoiceIdInput').val();

    $('#invoicesLoading').show();

    $.get(`/admin/sales-returns/invoice-details/${invoiceId}`, function(response) {
        $('#invoicesLoading').hide();

        if (response.success) {
            // Store invoice info including type and discount
            selectedInvoice = response.invoice;
            invoiceItems = response.items;

            renderItemsTable();
        }
    }).fail(function() {
        $('#invoicesLoading').hide();
        showAlert('Failed to load invoice details', 'error');
    });
}

function renderItemsTable() {
    const tbody = $('#itemsTableBody');
    tbody.empty();

    let totalQty = 0;
    let totalTax = 0;
    let itemsSubtotal = 0;

    // Check if invoice is GST type
    const isGstInvoice = selectedInvoice?.is_gst || false;

    // Get extra discount info
    const extraDiscount = selectedInvoice?.extra_discount || 0;
    const extraDiscountType = selectedInvoice?.extra_discount_type || 'amount';

    // Get current return quantities
    const currentReturnQtys = {};
    @foreach($return->items as $item)
        currentReturnQtys['{{ $item->sales_invoice_item_id }}'] = {{ $item->quantity }};
    @endforeach

    invoiceItems.forEach((item, index) => {
        const soldQty = item.quantity;
        const alreadyReturned = item.already_returned || 0;
        const remaining = item.max_return_qty;

        // Use current return quantity if available, otherwise default to remaining
        const currentQty = currentReturnQtys[item.id] || remaining;
        const returnQty = currentQty;
        const price = item.price;
        const itemSubtotal = returnQty * price;

        itemsSubtotal += itemSubtotal;
        totalQty += returnQty;

        // Calculate tax for this item
        const taxPercent = item.tax_percent || 0;
        const itemTax = isGstInvoice ? (itemSubtotal * taxPercent / 100) : 0;
        totalTax += itemTax;

        // Tax display HTML
        let taxHtml = '';
        if (isGstInvoice) {
            taxHtml = `
                <div class="tax-info">
                    <span class="tax-badge">GST ${taxPercent}%</span>
                </div>
            `;
        } else {
            taxHtml = `<div class="tax-info"><span class="tax-badge cash-badge">Cash Memo</span></div>`;
        }

        const row = `
            <tr data-index="${index}">
                <td class="text-c">
                    <input type="checkbox" class="item-checkbox" checked onchange="updateItemSelection(${index})">
                </td>
                <td>
                    <div class="item-name">${item.product_name}</div>
                    ${item.variant_name ? `<div class="item-variant">${item.variant_name}</div>` : ''}
                </td>
                <td class="text-c">${soldQty}</td>
                <td class="text-c ${alreadyReturned > 0 ? 'returned-highlight' : ''}">
                    ${alreadyReturned > 0 ? alreadyReturned : '—'}
                </td>
                <td class="text-c remaining-qty">${remaining}</td>
                <td class="text-r price-col">₹ ${price.toFixed(2)}</td>
                <td>
                    <div class="qty-control">
                        <input type="number" class="return-qty-input"
                               value="${returnQty.toFixed(2)}"
                               min="1"
                               max="${remaining}"
                               step="0"
                               onchange="updateItemQty(${index}, this.value)">
                    </div>
                    ${taxHtml}
                </td>
                <td class="text-r amount-col">
                    <span class="item-total">₹ ${itemSubtotal.toFixed(2)}</span>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    // Calculate discount on subtotal
    let discountAmount = 0;
    if (extraDiscount > 0 && itemsSubtotal > 0) {
        if (extraDiscountType === 'percent') {
            discountAmount = (itemsSubtotal * extraDiscount) / 100;
        } else {
            discountAmount = extraDiscount;
        }
    }

    // Final total
    const finalTotal = itemsSubtotal + totalTax - discountAmount;

    // Update totals in footer
    $('#totalReturnQty').text(totalQty.toFixed(2));
    $('#totalReturnAmount').text('₹ ' + finalTotal.toFixed(2));

    // Add calculation summary
    updateCalculationSummary(itemsSubtotal, totalTax, discountAmount, extraDiscount, extraDiscountType, finalTotal, isGstInvoice);
}

function updateCalculationSummary(subtotal, tax, discount, discountVal, discountType, grandTotal, isGstInvoice) {
    // Remove existing summary if any
    $('#calculationSummary').remove();

    let summaryHtml = `
        <tfoot id="calculationSummary" class="summary-footer">
            <tr class="summary-row">
                <td colspan="6" class="summary-label">Items Subtotal:</td>
                <td class="summary-qty">${$('#totalReturnQty').text()}</td>
                <td class="summary-amount">₹ ${subtotal.toFixed(2)}</td>
            </tr>
    `;

    if (isGstInvoice && tax > 0) {
        summaryHtml += `
            <tr class="summary-row">
                <td colspan="7" class="summary-label">Total Tax (GST):</td>
                <td class="summary-amount tax-amount">+ ₹ ${tax.toFixed(2)}</td>
            </tr>
        `;
    }

    if (discount > 0) {
        const discountText = discountType === 'percent' ? `${discountVal}%` : 'Fixed';
        summaryHtml += `
            <tr class="summary-row">
                <td colspan="7" class="summary-label">Extra Discount (${discountText}):</td>
                <td class="summary-amount discount-amount">- ₹ ${discount.toFixed(2)}</td>
            </tr>
        `;
    }

    summaryHtml += `
            <tr class="summary-row grand-total-row">
                <td colspan="7" class="summary-label grand-total-label">Grand Total:</td>
                <td class="summary-amount grand-total-amount">₹ ${grandTotal.toFixed(2)}</td>
            </tr>
        </tfoot>
    `;

    $('.items-table').append(summaryHtml);
}
function updateItemSelection(index, checkbox) {
    // Just recalculate totals based on current checkbox states
    let totalQty = 0;
    let itemsSubtotal = 0;
    let totalTax = 0;

    const isGstInvoice = selectedInvoice?.is_gst || false;
    const extraDiscount = selectedInvoice?.extra_discount || 0;
    const extraDiscountType = selectedInvoice?.extra_discount_type || 'amount';

    // Loop through all rows to calculate totals
    $('.item-checkbox').each(function(idx) {
        if ($(this).prop('checked')) {
            const row = $(this).closest('tr');
            const rowQty = parseFloat(row.find('.return-qty-input').val()) || 0;
            const price = invoiceItems[idx].price;
            const rowSubtotal = rowQty * price;

            itemsSubtotal += rowSubtotal;
            totalQty += rowQty;

            // Calculate tax
            const taxPercent = invoiceItems[idx].tax_percent || 0;
            if (isGstInvoice) {
                totalTax += (rowSubtotal * taxPercent / 100);
            }
        }
    });

    // Calculate discount
    let discountAmount = 0;
    if (extraDiscount > 0 && itemsSubtotal > 0) {
        if (extraDiscountType === 'percent') {
            discountAmount = (itemsSubtotal * extraDiscount) / 100;
        } else {
            discountAmount = extraDiscount;
        }
    }

    // Final total
    const finalTotal = itemsSubtotal + totalTax - discountAmount;

    // Update footer totals
    $('#totalReturnQty').text(totalQty.toFixed(2));
    $('#totalReturnAmount').text('₹ ' + finalTotal.toFixed(2));

    // Update summary
    updateCalculationSummary(itemsSubtotal, totalTax, discountAmount, extraDiscount, extraDiscountType, finalTotal, isGstInvoice);
}
function updateItemQty(index, qty) {
    const item = invoiceItems[index];
    const maxQty = item.max_return_qty;

    // Validate quantity
    qty = parseFloat(qty) || 0;
    if (qty < 0.01) qty = 0.01;
    if (qty > maxQty) qty = maxQty;

    // Update input value
    $(`tr[data-index="${index}"] .return-qty-input`).val(qty.toFixed(2));

    // Update this row's amount
    const price = item.price;
    const rowSubtotal = qty * price;
    $(`tr[data-index="${index}"] .item-total`).text('₹ ' + rowSubtotal.toFixed(2));

    // Recalculate totals
    let totalQty = 0;
    let itemsSubtotal = 0;
    let totalTax = 0;

    const isGstInvoice = selectedInvoice?.is_gst || false;
    const extraDiscount = selectedInvoice?.extra_discount || 0;
    const extraDiscountType = selectedInvoice?.extra_discount_type || 'amount';

    $('.item-checkbox').each(function(idx) {
        if ($(this).prop('checked')) {
            const row = $(this).closest('tr');
            const rowQty = parseFloat(row.find('.return-qty-input').val()) || 0;
            const rowPrice = invoiceItems[idx].price;
            const rowSubtotal = rowQty * rowPrice;

            itemsSubtotal += rowSubtotal;
            totalQty += rowQty;

            // Calculate tax
            const taxPercent = invoiceItems[idx].tax_percent || 0;
            if (isGstInvoice) {
                totalTax += (rowSubtotal * taxPercent / 100);
            }
        }
    });

    // Calculate discount
    let discountAmount = 0;
    if (extraDiscount > 0 && itemsSubtotal > 0) {
        if (extraDiscountType === 'percent') {
            discountAmount = (itemsSubtotal * extraDiscount) / 100;
        } else {
            discountAmount = extraDiscount;
        }
    }

    // Final total
    const finalTotal = itemsSubtotal + totalTax - discountAmount;

    // Update footer totals
    $('#totalReturnQty').text(totalQty.toFixed(2));
    $('#totalReturnAmount').text('₹ ' + finalTotal.toFixed(2));

    // Update summary
    updateCalculationSummary(itemsSubtotal, totalTax, discountAmount, extraDiscount, extraDiscountType, finalTotal, isGstInvoice);
}

// Fixed: Toggle select all
function toggleSelectAll() {
    const checked = $('#selectAllItems').prop('checked');
    $('.item-checkbox').prop('checked', checked);

    // Recalculate totals using the same logic as updateItemSelection
    let totalQty = 0;
    let itemsSubtotal = 0;
    let totalTax = 0;

    const isGstInvoice = selectedInvoice?.is_gst || false;
    const extraDiscount = selectedInvoice?.extra_discount || 0;
    const extraDiscountType = selectedInvoice?.extra_discount_type || 'amount';

    $('.item-checkbox').each(function(idx) {
        if ($(this).prop('checked')) {
            const row = $(this).closest('tr');
            const rowQty = parseFloat(row.find('.return-qty-input').val()) || 0;
            const price = invoiceItems[idx].price;
            const rowSubtotal = rowQty * price;

            itemsSubtotal += rowSubtotal;
            totalQty += rowQty;

            // Calculate tax
            const taxPercent = invoiceItems[idx].tax_percent || 0;
            if (isGstInvoice) {
                totalTax += (rowSubtotal * taxPercent / 100);
            }
        }
    });

    // Calculate discount
    let discountAmount = 0;
    if (extraDiscount > 0 && itemsSubtotal > 0) {
        if (extraDiscountType === 'percent') {
            discountAmount = (itemsSubtotal * extraDiscount) / 100;
        } else {
            discountAmount = extraDiscount;
        }
    }

    // Final total
    const finalTotal = itemsSubtotal + totalTax - discountAmount;

    // Update footer totals
    $('#totalReturnQty').text(totalQty.toFixed(2));
    $('#totalReturnAmount').text('₹ ' + finalTotal.toFixed(2));

    // Update summary
    updateCalculationSummary(itemsSubtotal, totalTax, discountAmount, extraDiscount, extraDiscountType, finalTotal, isGstInvoice);
}


function showAlert(message, type = 'success') {
    const container = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `<span>${message}</span>`;
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}

// Form Submission
$('#salesReturnForm').submit(function(e) {
    e.preventDefault();

    if (isSubmitting) {
        showAlert('Please wait, return is being updated...', 'info');
        return;
    }

    // Validate return items
    const items = [];
    let hasValidItems = false;

    $('.item-checkbox').each(function(index) {
        if ($(this).prop('checked')) {
            const qty = parseFloat($(this).closest('tr').find('.return-qty-input').val()) || 0;
            if (qty > 0) {
                hasValidItems = true;
                items.push({
                    invoice_item_id: invoiceItems[index].id,
                    return_qty: qty
                });
            }
        }
    });

    if (!hasValidItems) {
        showAlert('Please select at least one item to return', 'error');
        return;
    }

    if (!$('#returnDate').val()) {
        showAlert('Please select return date', 'error');
        return;
    }

    isSubmitting = true;
    const $submitBtn = $('#submitBtn');
    const originalText = $submitBtn.html();

    $submitBtn.prop('disabled', true);
    $submitBtn.html('<span class="spinner"></span> Updating...');

    const formData = new FormData(this);
    formData.append('items', JSON.stringify(items));

    $.ajax({
        url: '{{ route('admin.sales-returns.update', $return->_id) }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-HTTP-Method-Override': 'PUT'
        },
        success: function(response) {
            if (response.success) {
                showAlert('Return updated successfully!', 'success');
                setTimeout(() => {
                    window.location.href = '/admin/sales-returns/' + response.return_id;
                }, 1500);
            }
        },
        error: function(xhr) {
            let message = 'Failed to update return';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert(message, 'error');

            isSubmitting = false;
            $submitBtn.prop('disabled', false);
            $submitBtn.html(originalText);
        }
    });
});
</script>
@endpush
@endsection
