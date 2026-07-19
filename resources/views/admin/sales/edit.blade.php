@extends('layouts.admin')

@section('title', 'Edit Sales Invoice - Admin Panel')
@section('header-title', 'Edit Sales Invoice #' . $invoice->invoice_number)

@section('content')
<div class="products-container">
    <div id="alertContainer"></div>

    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Edit Sales Invoice</h2>
            <span style="background: #f3f4f6; color: #374151; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; margin-left: 8px;">DRAFT</span>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.sales.show', $invoice->_id) }}" class="btn-small btn-secondary">← Back to Invoice</a>
        </div>
    </div>

    <div class="invoice-form-wrapper">
        <form id="salesInvoiceForm" class="invoice-form" data-invoice-id="{{ $invoice->_id }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="invoice_number"      value="{{ $invoice->invoice_number }}">
            <input type="hidden" name="warehouse_id"        value="{{ $invoice->warehouse_id ?? $mainWarehouse->_id }}">
            <input type="hidden" name="tax_amount"          id="taxAmountInput"       value="{{ $invoice->tax_total }}">
            <input type="hidden" name="discount_amount"     id="discountAmountInput"  value="{{ $invoice->discount_total }}">
            <input type="hidden" id="extraDiscountTypeInput" name="extra_discount_type" value="{{ $invoice->extra_discount_type ?? 'amount' }}">
            <input type="hidden" name="salesman_id"         id="salesmanIdInput"      value="{{ $invoice->salesman_id }}">
            <input type="hidden" name="invoice_type"        id="invoiceTypeHidden"    value="{{ $invoice->invoice_type }}">
            <input type="hidden" name="gst_mode"            id="gstModeInput"         value="{{ $invoice->gst_mode ?? 'exclusive' }}">

            <div class="form-row">
                <div class="form-col-main">
                    <div class="two-col-row">
                        <!-- LEFT: Party -->
                        <div class="col-50">
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-header-left">
                                        <div class="section-icon">👤</div>
                                        <div class="section-title"><h3>Bill To</h3><p>Party and address details</p></div>
                                    </div>
                                    <button type="button" class="btn-select-customer" id="selectPartyBtn" onclick="openSelectPartyModal()">
                                        <span class="btn-icon">👤</span> Change Party
                                    </button>
                                </div>
                                <div class="section-body">
                                    <div id="selectedPartyDetails" class="selected-customer-details">
                                        <div class="customer-header">
                                            <h4 id="partyNameDisplay">{{ optional($invoice->party)->name ?? 'Party Name' }}</h4>
                                            <span id="partyTypeBadge" class="party-type-badge {{ optional($invoice->party)->party_type ?? '' }}">
                                                {{ ucfirst(optional($invoice->party)->party_type ?? 'customer') }}
                                            </span>
                                            <button type="button" class="btn-change-customer" onclick="openSelectPartyModal()">Change</button>
                                        </div>
                                        <div class="customer-info-grid">
                                            <div class="info-column">
                                                <div class="info-row"><span class="info-label">Phone:</span><span id="partyPhone" class="info-value">{{ optional($invoice->party)->phone ?? '-' }}</span></div>
                                                <div class="info-row"><span class="info-label">Email:</span><span id="partyEmail" class="info-value">{{ optional($invoice->party)->email ?? '-' }}</span></div>
                                                <div class="info-row"><span class="info-label">GST:</span><span id="partyGst" class="info-value">{{ optional($invoice->party)->gst_number ?? '-' }}</span></div>
                                            </div>
                                            <div class="info-column">
                                                <div class="info-row"><span class="info-label">Opening Bal:</span><span id="partyOpeningBalance" class="info-value">₹ 0.00</span></div>
                                                <div class="info-row"><span class="info-label">Credit Limit:</span><span id="partyCreditLimit" class="info-value">{{ optional($invoice->party)->credit_limit ? '₹ '.number_format($invoice->party->credit_limit, 2) : 'No Limit' }}</span></div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="party_id"   id="partyIdInput"   value="{{ $invoice->party_id }}">
                                        <input type="hidden" name="party_type" id="partyTypeInput" value="{{ optional($invoice->party)->party_type ?? 'customer' }}">
                                        <div class="address-section">
                                            <div class="address-header"><h5>Address Details</h5></div>
                                            <div class="address-grid">
                                                <div class="address-card">
                                                    <div class="address-card-header"><span class="address-type">Billing Address</span></div>
                                                    <div class="address-content"><p id="billingAddressText">{{ $invoice->billing_address ?: 'No billing address' }}</p></div>
                                                    <input type="hidden" name="billing_address"  id="billingAddressInput"  value="{{ $invoice->billing_address }}">
                                                </div>
                                                <div class="address-card">
                                                    <div class="address-card-header"><span class="address-type">Shipping Address</span></div>
                                                    <div class="address-content"><p id="shippingAddressText">{{ $invoice->shipping_address ?: 'Same as billing' }}</p></div>
                                                    <input type="hidden" name="shipping_address" id="shippingAddressInput" value="{{ $invoice->shipping_address }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT: Invoice Details -->
                        <div class="col-50">
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-header-left">
                                        <div class="section-icon">📄</div>
                                        <div class="section-title"><h3>Invoice Details</h3><p>Invoice date, terms and other details</p></div>
                                    </div>
                                </div>
                                <div class="section-body">
                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label">Sales Invoice No.</label>
                                            <input type="text" class="form-control" value="{{ $invoice->invoice_number }}" readonly tabindex="-1">
                                        </div>
                                        <div class="form-group col-6">
                                            <label class="form-label required">Invoice Date</label>
                                            <input type="date" name="invoice_date" id="invoiceDate" class="form-control" value="{{ $invoice->invoice_date->format('Y-m-d') }}" required onchange="updateDueDateFromTerms()">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label">Payment Terms</label>
                                            <div class="payment-terms-days">
                                                @php
                                                    $termsDays = 0;
                                                    if ($invoice->due_date && $invoice->invoice_date) {
                                                        $termsDays = \Carbon\Carbon::parse($invoice->invoice_date)->diffInDays(\Carbon\Carbon::parse($invoice->due_date));
                                                    }
                                                @endphp
                                                <input type="number" name="payment_terms_days" id="paymentTermsDays" class="form-control" placeholder="0" min="0" step="1" value="{{ $termsDays }}" onchange="updateDueDateFromTerms()">
                                                <span>days</span>
                                                <input type="hidden" name="payment_terms" id="paymentTermsInput" value="{{ $invoice->payment_terms }}">
                                            </div>
                                        </div>
                                        <div class="form-group col-6">
                                            <label class="form-label">Due Date</label>
                                            <input type="date" name="due_date" id="dueDate" class="form-control" value="{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '' }}" onchange="updatePaymentTermsFromDueDate()">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label">PO Number</label>
                                            <input type="text" name="po_number" class="form-control" placeholder="Optional" value="{{ $invoice->po_number }}">
                                        </div>
                                        <div class="form-group col-6">
                                            <label class="form-label">Assigned Salesman</label>
                                            <div class="salesman-display" id="salesmanNameDisplay">{{ optional($invoice->salesman)->name ?? '—' }}</div>
                                        </div>
                                    </div>
                                    <!-- Invoice Type (read-only) + GST Mode toggle -->
                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label">Invoice Type</label>
                                            <div class="form-control" style="background:#f5f5f5;border:1px solid #ddd;border-radius:3px;">
                                                @if($invoice->invoice_type == 'gst')
                                                    GST Invoice <small style="color:#666;margin-left:8px;">(HSN + tax included)</small>
                                                @else
                                                    Cash Memo <small style="color:#666;margin-left:8px;">(No GST)</small>
                                                @endif
                                            </div>
                                            <small style="font-size:10px;margin-top:3px;color:#dc3545;">⚠️ Invoice type cannot be changed</small>
                                        </div>
                                        <!-- GST Mode toggle — only for GST invoice -->
                                        @if($invoice->invoice_type == 'gst')
                                        <div class="form-group col-6" id="gstModeGroup">
                                            <label class="form-label">GST Mode</label>
                                            <div class="gst-mode-toggle">
                                                <button type="button" class="gst-mode-btn {{ ($invoice->gst_mode ?? 'exclusive') === 'exclusive' ? 'active' : '' }}" data-mode="exclusive" onclick="setGstMode('exclusive')">
                                                    <span class="gst-mode-icon">📤</span>
                                                    <span class="gst-mode-label">Exclusive</span>
                                                    <span class="gst-mode-sub">GST added on top</span>
                                                </button>
                                                <button type="button" class="gst-mode-btn {{ ($invoice->gst_mode ?? 'exclusive') === 'inclusive' ? 'active' : '' }}" data-mode="inclusive" onclick="setGstMode('inclusive')">
                                                    <span class="gst-mode-icon">📥</span>
                                                    <span class="gst-mode-label">Inclusive</span>
                                                    <span class="gst-mode-sub">GST within price</span>
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-header-left">
                                <div class="section-icon">🛒</div>
                                <div class="section-title"><h3>Items</h3><p>Add products to invoice</p></div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span id="selectedWarehouseBadge" style="display:none;font-size:10px;font-weight:600;color:#fa8725;background:#fff3e6;border:1px solid #fa8725;padding:4px 10px;border-radius:12px;">
                                    🏭 <span id="selectedWarehouseBadgeText"></span>
                                </span>
                                <button type="button" class="btn-add-item" id="addItemBtn" onclick="openAddItemModal()">+ Add Item</button>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="items-table-container {{ $invoice->invoice_type == 'gst' ? 'gst-mode' : 'cash-mode' }}" id="itemsTableContainer">
                                <table class="items-table" id="mainItemsTable">
                                    <thead>
                                        <tr id="itemsHeaderRow">
                                            <th class="th-item">Item</th>
                                            <th class="th-hsn" id="hsnHeader" {{ $invoice->invoice_type != 'gst' ? 'style=display:none;' : '' }}>HSN/SAC</th>
                                            <th class="th-unit">Unit</th>
                                            <th class="th-qtys">Qty</th>
                                            <th class="th-warranty">Warranty</th>
                                            <th class="th-mrp">MRP (₹)</th>
                                            <th class="th-discount">Disc %</th>
                                            <th class="th-sale-price" id="priceColumnHeader">Sale Price (₹)</th>
                                            <th class="th-tax" id="taxHeader" {{ $invoice->invoice_type != 'gst' ? 'style=display:none;' : '' }}>Tax %</th>
                                            <th class="th-amount">Final Amt (₹)</th>
                                            <th class="th-action">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody"></tbody>
                                    <tfoot id="itemsTableFooter" style="background-color:#f0f0f0;font-weight:600;border-top:2px solid #333;{{ count($invoice->items) == 0 ? 'display:none;' : '' }}">
                                        <tr>
                                            <td colspan="{{ $invoice->invoice_type == 'gst' ? '5' : '4' }}" style="text-align:right;padding:10px;font-size:12px;"><strong>SUBTOTAL:</strong></td>
                                            <td style="padding:10px;text-align:center;font-size:12px;" id="footerMRP">₹ {{ number_format($invoice->total_mrp, 2) }}</td>
                                            <td style="padding:10px;text-align:center;font-size:12px;" id="footerDiscount">₹ {{ number_format($invoice->discount_total, 2) }}</td>
                                            <td style="padding:10px;text-align:center;font-size:12px;" id="footerSalePrice">₹ {{ number_format($invoice->subtotal, 2) }}</td>
                                            <td style="padding:10px;text-align:center;font-size:12px;{{ $invoice->invoice_type != 'gst' ? 'display:none;' : '' }}" id="footerTax">₹ {{ number_format($invoice->tax_total, 2) }}</td>
                                            <td style="padding:10px;text-align:center;font-size:12px;" id="footerFinalAmount">₹ {{ number_format($invoice->grand_total, 2) }}</td>
                                            <td style="padding:10px;"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="mobile-items-container" id="mobileItemsContainer"></div>
                        </div>
                    </div>

                    <!-- Summary and Payment -->
                    <div class="form-col-sidebar">
                        <div class="invoice-bottom-layout">
                            <div class="invoice-row">
                                <div class="invoice-col">
                                    <div class="summary-section">
                                        <div class="summary-header"><div class="summary-icon">💰</div><h3>Invoice Summary</h3></div>
                                        <div class="summary-body">
                                            <div class="summary-row"><span class="summary-label">Total MRP</span><span class="summary-value">₹ <span id="totalMRP">{{ number_format($invoice->total_mrp, 2) }}</span></span></div>
                                            <div class="summary-row"><span class="summary-label">Total Discount</span><span class="summary-value">- ₹ <span id="totalDiscount">{{ number_format($invoice->discount_total, 2) }}</span></span></div>
                                            <div class="summary-divider"></div>
                                            <div class="summary-row">
                                                <span class="summary-label" style="font-weight:600;">Subtotal <small id="subtotalNote" style="color:#fa8725;font-weight:400;">{{ ($invoice->gst_mode ?? 'exclusive') === 'inclusive' ? '(ex-GST extracted)' : '' }}</small></span>
                                                <span class="summary-value">₹ <span id="subtotal">{{ number_format($invoice->subtotal, 2) }}</span></span>
                                            </div>
                                            <div id="taxBreakupContainer" style="display:{{ $invoice->invoice_type == 'gst' ? 'block' : 'none' }};">
                                                <div id="intraStateTax" style="display:{{ $invoice->tax_type === 'intra' ? 'block' : 'none' }};">
                                                    <div class="summary-row"><span class="summary-label">CGST</span><span class="summary-value">+ ₹ <span id="cgstTotal">{{ number_format($invoice->cgst_total, 2) }}</span></span></div>
                                                    <div class="summary-row"><span class="summary-label">SGST</span><span class="summary-value">+ ₹ <span id="sgstTotal">{{ number_format($invoice->sgst_total, 2) }}</span></span></div>
                                                </div>
                                                <div id="interStateTax" style="display:{{ $invoice->tax_type === 'inter' ? 'block' : 'none' }};">
                                                    <div class="summary-row"><span class="summary-label">IGST</span><span class="summary-value">+ ₹ <span id="igstTotal">{{ number_format($invoice->igst_total, 2) }}</span></span></div>
                                                </div>
                                            </div>
                                            <div class="summary-row" id="totalTaxRow" style="border-top:1px dashed #ddd;padding-top:5px;display:{{ $invoice->invoice_type == 'gst' ? 'flex' : 'none' }};">
                                                <span class="summary-label">Total Tax</span>
                                                <span class="summary-value">+ ₹ <span id="totalTax">{{ number_format($invoice->tax_total, 2) }}</span></span>
                                            </div>
                                            <div class="summary-divider"></div>

                                            <div class="summary-row"><span class="summary-label"><a href="javascript:void(0)" id="addDiscountLink" onclick="toggleExtraDiscount()">+ Add Discount</a></span></div>
                                            <div id="extraDiscountRow" style="display:{{ $invoice->extra_discount > 0 && $invoice->extra_discount_type == 'amount' ? 'flex' : 'none' }};" class="summary-row">
                                                <input type="number" id="extraDiscount" placeholder="Enter discount amount" step="0.01" value="{{ $invoice->extra_discount_type == 'amount' ? $invoice->extra_discount : '' }}" oninput="calculateTotals()" class="summary-input">
                                            </div>
                                            <div class="summary-row"><span class="summary-label"><a href="javascript:void(0)" id="addDiscountPercentLink" onclick="toggleExtraDiscountPercent()">+ Add Discount %</a></span></div>
                                            <div id="extraDiscountPercentRow" style="display:{{ $invoice->extra_discount > 0 && $invoice->extra_discount_type == 'percent' ? 'flex' : 'none' }};" class="summary-row">
                                                <input type="number" id="extraDiscountPercent" placeholder="Enter discount %" step="0.01" value="{{ $invoice->extra_discount_type == 'percent' ? $invoice->extra_discount : '' }}" oninput="calculateTotals()" class="summary-input">
                                            </div>
                                            <div class="summary-row"><span class="summary-label"><a href="javascript:void(0)" id="addChargeLink" onclick="toggleExtraCharge()">+ Add Another Charge</a></span></div>
                                            <div id="extraChargeRow" style="display:{{ $invoice->extra_charge > 0 ? 'flex' : 'none' }};" class="summary-row">
                                                <input type="text" placeholder="Charge Name" id="chargeName" value="{{ $invoice->charge_name }}" class="summary-input">
                                                <input type="number" placeholder="₹" id="extraCharge" value="{{ $invoice->extra_charge }}" oninput="calculateTotals()" class="summary-input-small">
                                            </div>
                                            <div class="summary-row">
                                                <label><input type="checkbox" id="autoRoundOff" onchange="calculateTotals()" {{ $invoice->round_off != 0 ? 'checked' : '' }}> Auto Round Off</label>
                                            </div>
                                            <div class="summary-row total-row">
                                                <span class="summary-label">Grand Total</span>
                                                <span class="summary-value">₹ <span id="grandTotal">{{ number_format($invoice->grand_total, 2) }}</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="invoice-col">
                                    <div class="payment-section-container">
                                        <h4>Payment Details</h4>
                                        <div class="form-group">
                                            <label class="form-label">Payment Method</label>
                                            <select name="payment_method" class="form-control">
                                                <option value="cash"          {{ $invoice->payment_method == 'cash'          ? 'selected' : '' }}>Cash</option>
                                                <option value="bank_transfer" {{ $invoice->payment_method == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                                <option value="cheque"        {{ $invoice->payment_method == 'cheque'        ? 'selected' : '' }}>Cheque</option>
                                                <option value="card"          {{ $invoice->payment_method == 'card'          ? 'selected' : '' }}>Card</option>
                                                <option value="upi"           {{ $invoice->payment_method == 'upi'           ? 'selected' : '' }}>UPI</option>
                                                <option value="credit"        {{ $invoice->payment_method == 'credit'        ? 'selected' : '' }}>Credit</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Amount Paid</label>
                                            <input type="number" name="amount_paid" id="amountPaid" class="form-control" step="0.01" min="0" value="{{ $invoice->total_paid }}" oninput="validateAndCalculateBalance()" onblur="clampAmountPaid()">
                                        </div>
                                        <div class="form-group">
                                            <button type="button" onclick="markFullyPaid()" class="btn-mark-paid" style="width:auto;padding:4px 8px;font-size:10px;">Mark as Fully Paid</button>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Balance Amount</label>
                                            <div class="balance-amount">₹ <span id="balanceAmount">{{ number_format($invoice->balance_amount, 2) }}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row" style="margin-top:10px;">
                        <div class="form-group col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ $invoice->notes }}</textarea>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;">
                        <button type="submit" class="btn-submit-invoice">
                            <span class="btn-icon">💾</span> Update Draft Invoice
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <!-- Mobile Sticky Bottom Bar (MyBillBook style) -->
        <div class="mobile-sticky-bottom-bar">
            <div class="mobile-sticky-total">
                <span class="mobile-sticky-label">Grand Total</span>
                <span class="mobile-sticky-val">₹ <span id="mobileStickyGrandTotal">{{ number_format($invoice->grand_total, 2) }}</span></span>
            </div>
            <button type="button" class="btn-mobile-save" onclick="$('#salesInvoiceForm').submit()">
                💾 Update Invoice
            </button>
        </div>
    </div>
</div>

<!-- Select Party Modal -->
<div class="modal" id="selectPartyModal">
    <div class="modal-overlay"></div>
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <div class="modal-icon">👥</div>
            <div class="modal-title-section"><h4 class="modal-title">Select Party</h4><div class="modal-subtitle">Choose customer, dealer or distributor</div></div>
            <button type="button" class="modal-close" onclick="closeSelectPartyModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="party-type-tabs">
                <button type="button" class="party-type-tab active" data-type="all">All</button>
                <button type="button" class="party-type-tab" data-type="customer">Customers</button>
                <button type="button" class="party-type-tab" data-type="dealer">Dealers</button>
                <button type="button" class="party-type-tab" data-type="distributor">Distributors</button>
            </div>
            <div class="party-modal-header">
                <div class="search-container"><div class="search-box"><span class="search-icon">🔍</span><input type="text" id="searchParty" class="search-input" placeholder="Search by name, phone or email..."></div></div>
                <button type="button" class="btn-create-new-party" onclick="openCreatePartyModal()"><span class="btn-icon">+</span> Create New Party</button>
            </div>
            <div class="parties-table-container">
                <table class="parties-table">
                    <thead><tr><th>Name</th><th>Type</th><th>Phone</th><th>Email</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody id="partiesTableBody"></tbody>
                </table>
                <div id="partiesLoading" class="loading-state"><div class="loading-spinner"></div><p>Loading parties...</p></div>
                <div id="noParties" class="no-data-state" style="display:none;"><div class="no-data-icon">👤</div><p>No parties found</p><button type="button" class="btn-create-first-party" onclick="openCreatePartyModal()">+ Create New Party</button></div>
            </div>
        </div>
        <div class="modal-actions"><button type="button" class="btn-modal btn-cancel" onclick="closeSelectPartyModal()">Cancel</button></div>
    </div>
</div>

<!-- Create Party Modal -->
<div class="modal" id="createPartyModal">
    <div class="modal-overlay"></div>
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <div class="modal-icon">👤</div>
            <div class="modal-title-section"><h4 class="modal-title">Create New Party</h4><div class="modal-subtitle">Enter party details and address</div></div>
            <button type="button" class="modal-close" onclick="closeCreatePartyModal()">×</button>
        </div>
        <form id="createPartyForm">
            @csrf
            <div class="modal-body">
                <div class="form-section-small">
                    <h5>Basic Information</h5>
                    <div class="form-row">
                        <div class="form-group col-6"><label class="form-label required">Party Name</label><input type="text" name="name" class="form-control" required></div>
                        <div class="form-group col-6"><label class="form-label required">Phone Number</label><input type="tel" name="phone" class="form-control" required maxlength="10"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control"></div>
                        <div class="form-group col-6">
                            <label class="form-label required">Party Type</label>
                            <select name="party_type" class="form-control" required>
                                <option value="customer">Customer</option>
                                <option value="dealer">Dealer</option>
                                <option value="distributor">Distributor</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6"><label class="form-label">Opening Balance</label><input type="number" name="opening_balance" class="form-control" step="0.01" min="0" value="0"></div>
                        <div class="form-group col-6"><label class="form-label">Credit Limit</label><input type="number" name="credit_limit" class="form-control" step="0.01" min="0"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label">Assign Salesman</label>
                            <select name="salesman_id" class="form-control">
                                <option value="">None</option>
                                @foreach(\App\Models\Salesman::where('status','active')->orderBy('name')->get() as $s)
                                    <option value="{{ $s->_id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-section-small">
                    <h5>Tax Information</h5>
                    <div class="form-row">
                        <div class="form-group col-6"><label class="form-label">GST Number</label><input type="text" name="gst_number" class="form-control" maxlength="15" oninput="this.value=this.value.toUpperCase()"></div>
                        <div class="form-group col-6"><label class="form-label">PAN Number</label><input type="text" name="pan_number" class="form-control" maxlength="10" oninput="this.value=this.value.toUpperCase()"></div>
                    </div>
                </div>
                <div class="form-section-small">
                    <div class="section-header-small">
                        <h5>Billing Address</h5>
                        <label class="form-check"><input type="checkbox" id="sameBillingShipping" class="form-check-input"><span class="form-check-label">Same as Shipping</span></label>
                    </div>
                    <div class="form-group"><label class="form-label">Address</label><input type="text" name="billing_address" class="form-control"></div>
                    <div class="form-row">
                        <div class="form-group col-6"><label class="form-label">City</label><input type="text" name="billing_city" class="form-control"></div>
                        <div class="form-group col-6"><label class="form-label">State</label><input type="text" name="billing_state" class="form-control"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6"><label class="form-label">Pincode</label><input type="text" name="billing_pincode" class="form-control" maxlength="6"></div>
                        <div class="form-group col-6"><label class="form-label">Country</label><input type="text" name="billing_country" class="form-control" value="India"></div>
                    </div>
                </div>
                <div class="form-section-small">
                    <h5>Shipping Address</h5>
                    <div class="form-group"><label class="form-label">Address</label><input type="text" name="shipping_address" class="form-control"></div>
                    <div class="form-row">
                        <div class="form-group col-6"><label class="form-label">City</label><input type="text" name="shipping_city" class="form-control"></div>
                        <div class="form-group col-6"><label class="form-label">State</label><input type="text" name="shipping_state" class="form-control"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6"><label class="form-label">Pincode</label><input type="text" name="shipping_pincode" class="form-control" maxlength="6"></div>
                        <div class="form-group col-6"><label class="form-label">Country</label><input type="text" name="shipping_country" class="form-control" value="India"></div>
                    </div>
                </div>
                <div class="form-section-small">
                    <h5>Additional Information</h5>
                    <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closeCreatePartyModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-primary">Create Party</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal" id="addItemModal">
    <div class="modal-overlay"></div>
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <div class="modal-icon">🛒</div>
            <div class="modal-title-section"><h4 class="modal-title">Add Items</h4><div class="modal-subtitle">Select products from warehouse</div></div>
            <button type="button" class="modal-close" onclick="closeAddItemModal()">×</button>
        </div>
        <div class="modal-body">
            <div style="text-align:center;padding:20px 0 15px 0;" id="warehouseSelectSection">
                <div style="font-size:13px;font-weight:600;color:#333;margin-bottom:12px;">🏭 Select Warehouse to Load Products</div>
                <div style="display:flex;align-items:center;justify-content:center;gap:10px;">
                    <div class="select-wrapper" style="width:280px;">
                        <select id="itemModalWarehouse" class="form-control" onchange="onWarehouseChange()" style="font-size:12px;padding:8px 12px;">
                            <option value="">-- Select Warehouse --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->_id }}">{{ $wh->name }}{{ $wh->is_main ? ' (Main)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div id="productSearchSection" style="display:none;margin-bottom:12px;">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="search-container" style="flex:1;margin-bottom:0;"><div class="search-box"><span class="search-icon">🔍</span><input type="text" id="searchProduct" class="search-input" placeholder="Search items..."></div></div>
                    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                        <span id="selectedWarehouseName" style="font-size:11px;font-weight:600;color:#fa8725;background:#fff3e6;padding:5px 10px;border-radius:4px;border:1px solid #fa8725;">🏭 —</span>
                        <button type="button" onclick="resetWarehouseSelection()" style="font-size:10px;padding:5px 8px;background:#eee;border:1px solid #ccc;border-radius:3px;cursor:pointer;color:#555;">↩ Change</button>
                    </div>
                </div>
            </div>
            <div class="products-table-container" id="productsTableWrapper" style="display:none;">
                <table class="products-table">
                    <thead><tr>
                        <th>Item Name</th><th>Item Code</th><th>MRP (₹)</th>
                        <th id="modalPriceColumnHeader">Sale Price (₹)</th>
                        <th>Current Stock</th><th>Quantity</th><th>Select</th>
                    </tr></thead>
                    <tbody id="productsTableBody"></tbody>
                </table>
                <div id="productsLoading" class="loading-state" style="display:none;"><div class="loading-spinner"></div><p>Loading products...</p></div>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeAddItemModal()">Cancel</button>
            <button type="button" class="btn-modal btn-primary" onclick="addSelectedProducts()">Done</button>
        </div>
    </div>
</div>

@push('styles')
<style>
/* GST MODE TOGGLE */
.gst-mode-toggle { display:flex; gap:6px; }
.gst-mode-btn { flex:1; display:flex; flex-direction:column; align-items:center; gap:1px; padding:6px 8px; background:#f8f9fa; border:1.5px solid #dee2e6; border-radius:5px; cursor:pointer; transition:all .18s; font-size:10px; line-height:1.3; }
.gst-mode-btn:hover { border-color:#fa8725; background:#fff8f2; }
.gst-mode-btn.active { background:#fff3e6; border-color:#fa8725; }
.gst-mode-btn.active .gst-mode-label { color:#c05e00; font-weight:700; }
.gst-mode-icon { font-size:13px; } .gst-mode-label { font-weight:600; font-size:10px; color:#333; } .gst-mode-sub { font-size:9px; color:#888; }
.items-table tr.row-inclusive { background:#fffbf5 !important; }
.incl-badge { display:inline-block; font-size:8px; background:#fff3e6; color:#c05e00; border:1px solid #fa8725; border-radius:3px; padding:1px 4px; margin-left:4px; vertical-align:middle; font-weight:600; }

/* GENERAL */
.products-container { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; font-size:11px; line-height:1.3; color:#333; }
#alertContainer { position:fixed; top:15px; right:15px; z-index:9999; }
.alert { padding:10px 14px; margin-bottom:8px; border-radius:4px; font-size:10px; font-weight:500; animation:slideInRight .3s ease; box-shadow:0 2px 8px rgba(0,0,0,.1); }
.alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert-error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.alert-info    { background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; }
@keyframes slideInRight { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
.page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; border-bottom:1px solid #ddd; }
.header-left .page-title { font-size:15px; font-weight:600; color:#333; margin:0 0 3px; }
.btn-small { padding:5px 10px; background:white; color:#fa8128; border:1px solid #dee2e6; border-radius:4px; font-size:14px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:3px; text-decoration:none; }
.invoice-form-wrapper { background:white; border:1px solid #ddd; border-radius:4px; padding:15px; }
.form-row { display:flex; flex-direction:row; gap:15px; }
.form-col-main { width:100%; }
.two-col-row { display:flex; gap:15px; width:100%; }
.col-50 { width:50%; }
.form-section { background:white; border:1px solid #ddd; border-radius:4px; margin-bottom:15px; overflow:hidden; }
.section-header { display:flex; justify-content:space-between; align-items:center; padding:12px 15px; background:#f8f9fa; border-bottom:1px solid #ddd; }
.section-header-left { display:flex; align-items:center; gap:10px; }
.section-icon { width:28px; height:28px; background:#fa8725de; border-radius:4px; display:flex; align-items:center; justify-content:center; color:white; font-size:13px; flex-shrink:0; }
.section-title h3 { font-size:13px; font-weight:600; color:#333; margin:0; }
.section-title p { font-size:10px; color:#666; margin:1px 0 0; }
.section-body { padding:15px; }
.form-group { margin-bottom:12px; }
.form-label { display:block; font-size:11px; font-weight:500; color:#555; margin-bottom:3px; }
.form-label.required::after { content:' *'; color:#dc3545; }
.form-control { width:100%; padding:6px 10px; border:1px solid #ccc; border-radius:3px; font-size:10px; background:#fff; transition:all .2s; }
.form-control:focus { outline:none; border-color:#666; box-shadow:0 0 0 2px rgba(102,102,102,.1); }
.form-row .form-group { margin-bottom:10px; }
.form-row .col-6 { width:50%; }
.form-row .col-12 { width:100%; }
.btn-select-customer { width:180px; padding:8px 12px; background:#fa8725de; color:white; border:none; border-radius:3px; font-size:11px; font-weight:500; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:5px; }
.selected-customer-details { padding:16px; background:#fafafa; border-radius:4px; border:1px solid #ddd; }
.customer-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:4px; padding-bottom:4px; border-bottom:1px solid #eee; }
.customer-header h4 { font-size:13px; font-weight:600; color:#333; margin:0; }
.btn-change-customer { padding:3px 8px; background:#666; color:white; border:none; border-radius:3px; font-size:10px; cursor:pointer; }
.customer-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
.info-column .info-row { display:flex; align-items:center; margin-bottom:6px; }
.info-column .info-label { font-size:10px; font-weight:500; color:#555; width:70px; flex-shrink:0; }
.info-column .info-value { font-size:10px; color:#333; }
.salesman-display { padding:6px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; font-size:10px; color:#333; }
.party-type-badge { display:inline-block; padding:2px 8px; border-radius:12px; font-size:9px; font-weight:600; text-transform:uppercase; background:#e7f1ff; color:#0066cc; margin-left:8px; }
.party-type-badge.customer { background:#d4edda; color:#155724; }
.party-type-badge.dealer { background:#cce5ff; color:#004085; }
.party-type-badge.distributor { background:#fff3cd; color:#856404; }
.address-section { padding-top:2px; border-top:1px solid #eee; }
.address-header h5 { font-size:11px; font-weight:600; color:#333; margin:0 0 12px; }
.address-grid { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
.address-card { background:#fafafa; border:1px solid #ddd; border-radius:4px; padding:12px; }
.address-card-header { margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid #eee; }
.address-type { font-size:10px; font-weight:600; color:#333; }
.address-content { font-size:10px; color:#555; line-height:1.4; }
.payment-terms-days { display:flex; align-items:center; gap:5px; }
.payment-terms-days .form-control { width:50%; flex-shrink:0; }
.payment-terms-days span { font-size:10px; color:#666; white-space:nowrap; }
.btn-add-item { padding:6px 12px; background:#555; color:white; border:none; border-radius:3px; font-size:10px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:4px; }
.btn-add-item:hover { background:#444; }
.items-table-container { overflow-x:auto; width:100%; margin-top:10px; border-radius:4px; border:1px solid #ddd; }
.items-table-container.gst-mode .items-table { min-width:980px; }
.items-table-container.cash-mode .items-table { min-width:560px; }
.items-table { width:100%; border-collapse:collapse; font-size:10px; }
.items-table th { background:#f8f9fa; padding:10px 8px; text-align:center; font-weight:600; color:#333; border-bottom:2px solid #ddd; white-space:nowrap; font-size:11px; }
.items-table td { padding:8px; border-bottom:1px solid #eee; vertical-align:middle; text-align:center; font-size:10px; }
.items-table td.item-name { text-align:left; font-weight:500; }
.items-table tr:hover { background:#f9f9f9; }
.items-table input[type="number"] { width:100%; padding:4px 6px; border:1px solid #ccc; border-radius:2px; font-size:10px; text-align:center; }
.th-item { min-width:140px; text-align:left !important; } .th-hsn,.th-unit { width:75px; } .th-qtys,.th-tax { width:65px; } .th-warranty { width:130px; } .th-sale-price { width:95px; } .th-mrp { width:85px; } .th-discount { width:75px; } .th-amount { width:90px; } .th-action { width:60px; }
.btn-delete { width:28px; height:28px; border:none; background:#fee; color:#c33; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; margin:0 auto; }
.btn-delete:hover { background:#fcc; color:#a00; }
.empty-row td { padding:30px 20px; text-align:center; }
.empty-items { display:flex; flex-direction:column; align-items:center; gap:8px; }
.empty-icon { font-size:24px; opacity:.3; margin-bottom:5px; }
.empty-items p { font-size:10px; color:#666; margin:0; }
.btn-add-first-item { padding:6px 12px; background:#555; color:white; border:none; border-radius:3px; font-size:10px; cursor:pointer; }
.warranty-wrapper { display:flex; gap:4px; align-items:center; }
.warranty-input { width:50px; padding:3px; font-size:10px; } .warranty-select { font-size:10px; padding:3px; }
.form-col-sidebar { margin-top:10px; }
.invoice-bottom-layout { display:flex; flex-direction:column; gap:15px; }
.invoice-row { display:flex; gap:15px; }
.invoice-col { flex:1; }
.summary-section { background:white; border:1px solid #ddd; border-radius:4px; overflow:hidden; }
.summary-header { padding:12px 15px; background:#f8f9fa; color:#333; display:flex; align-items:center; gap:8px; border-bottom:1px solid #ddd; }
.summary-icon { font-size:13px; }
.summary-header h3 { font-size:13px; font-weight:600; margin:0; }
.summary-body { padding:15px; }
.summary-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.summary-label { font-size:11px; color:#555; }
.summary-label a { color:#fa8725; text-decoration:none; font-weight:500; font-size:10px; padding:4px 8px; background:#fff3e6; border-radius:3px; border:1px dashed #fa8725; display:inline-block; }
.summary-value { font-size:11px; font-weight:600; color:#333; }
.summary-divider { height:1px; background:#eee; margin:10px 0; }
.total-row { margin-top:10px; padding-top:10px; border-top:2px solid #333; }
.total-row .summary-label { font-size:12px; font-weight:700; color:#333; }
.total-row .summary-value { font-size:15px; font-weight:700; color:#fa8725; }
.summary-input { width:100%; padding:4px; font-size:10px; border:1px solid #ccc; border-radius:3px; }
.summary-input-small { width:70px; padding:4px; font-size:10px; border:1px solid #ccc; border-radius:3px; }
.payment-section-container { background:white; border:1px solid #ddd; border-radius:4px; padding:15px; }
.payment-section-container h4 { font-size:11px; font-weight:600; color:#333; margin:0 0 12px; padding-bottom:10px; border-bottom:1px solid #eee; }
.balance-amount { padding:6px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; font-size:11px; font-weight:600; color:#333; }
.btn-mark-paid { background:#555; color:white; border:none; border-radius:3px; font-weight:500; cursor:pointer; }
.btn-submit-invoice { width:25%; padding:10px 15px; background:#f98725; color:white; border:none; border-radius:4px; font-size:11px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; margin-top:15px; }
.btn-submit-invoice:disabled { opacity:.6; cursor:not-allowed; }
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:1000; align-items:center; justify-content:center; }
.modal-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.4); }
.modal-content { position:relative; background:white; border-radius:6px; width:90%; max-width:800px; max-height:90vh; overflow-y:auto; animation:modalFadeIn .2s ease; box-shadow:0 4px 20px rgba(0,0,0,.15); }
.modal-lg,.modal-xl { max-width:800px; }
@keyframes modalFadeIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.modal-header { display:flex; align-items:center; gap:10px; padding:15px 20px; border-bottom:1px solid #ddd; background:#f8f9fa; border-radius:6px 6px 0 0; }
.modal-title-section { flex:1; } .modal-title { font-size:14px; font-weight:600; color:#333; margin:0; } .modal-subtitle { font-size:10px; color:#666; margin-top:2px; }
.modal-close { background:none; border:none; font-size:18px; color:#666; cursor:pointer; padding:4px; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:4px; }
.modal-close:hover { background:#eee; }
.modal-icon { width:32px; height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:14px; color:white; flex-shrink:0; background:#555; }
.modal-body { padding:20px; }
.modal-actions { display:flex; justify-content:flex-end; gap:8px; padding:15px 20px; border-top:1px solid #ddd; background:#fafafa; border-radius:0 0 6px 6px; }
.btn-modal { padding:7px 16px; border:none; border-radius:3px; font-size:10px; font-weight:500; cursor:pointer; min-width:70px; }
.btn-cancel { background:#f8f9fa; color:#333; border:1px solid #ccc; } .btn-cancel:hover { background:#e9ecef; }
.btn-primary { background:#555; color:white; border:1px solid #555; } .btn-primary:hover { background:#444; }
.party-type-tabs { display:flex; gap:8px; margin-bottom:15px; padding:5px; background:#f8f9fa; border-radius:6px; border:1px solid #dee2e6; }
.party-type-tab { flex:1; padding:8px 12px; background:transparent; border:none; border-radius:4px; font-size:11px; font-weight:500; color:#6c757d; cursor:pointer; text-align:center; }
.party-type-tab.active { background:#007bff; color:white; }
.party-modal-header { display:flex; justify-content:space-between; align-items:center; gap:15px; margin-bottom:15px; }
.search-container { flex:1; } .search-box { position:relative; }
.search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:11px; color:#666; }
.search-input { width:100%; padding:7px 10px 7px 30px; border:1px solid #ccc; border-radius:3px; font-size:10px; }
.btn-create-new-party { padding:7px 14px; background:#28a745; color:white; border:none; border-radius:3px; font-size:10px; font-weight:500; cursor:pointer; display:flex; align-items:center; gap:5px; white-space:nowrap; }
.parties-table-container { max-height:400px; overflow-y:auto; border:1px solid #ddd; border-radius:4px; }
.parties-table { width:100%; border-collapse:collapse; font-size:10px; }
.parties-table th { background:#f8f9fa; padding:8px 10px; text-align:left; font-weight:600; color:#333; border-bottom:1px solid #ddd; position:sticky; top:0; z-index:1; }
.parties-table td { padding:8px 10px; border-bottom:1px solid #eee; vertical-align:middle; }
.parties-table tr:hover { background:#f9f9f9; }
.status-badge { display:inline-block; padding:2px 6px; border-radius:10px; font-size:9px; font-weight:500; }
.status-active { background:#d4edda; color:#155724; } .status-inactive { background:#f8d7da; color:#721c24; }
.btn-create-first-party { padding:6px 12px; background:#28a745; color:white; border:none; border-radius:3px; font-size:10px; cursor:pointer; }
.no-data-state { padding:30px 20px; text-align:center; } .no-data-icon { font-size:24px; opacity:.3; margin-bottom:8px; } .no-data-state p { font-size:10px; color:#666; margin:0 0 12px; }
.loading-state { padding:30px 20px; text-align:center; }
.loading-spinner { width:20px; height:20px; border:2px solid #eee; border-top-color:#555; border-radius:50%; animation:spin 1s linear infinite; margin:0 auto 8px; }
@keyframes spin { to{transform:rotate(360deg)} }
.loading-state p { font-size:10px; color:#666; margin:0; }
.products-table-container { max-height:400px; overflow-y:auto; border:1px solid #ddd; border-radius:4px; }
.products-table { width:100%; border-collapse:collapse; font-size:10px; }
.products-table th { background:#f8f9fa; padding:8px 10px; text-align:left; font-weight:600; color:#333; border-bottom:1px solid #ddd; white-space:nowrap; position:sticky; top:0; z-index:1; }
.products-table td { padding:8px 10px; border-bottom:1px solid #eee; vertical-align:middle; }
.products-table tr:hover { background:#f9f9f9; }
.products-table input[type="number"] { width:50px; padding:4px 6px; border:1px solid #ccc; border-radius:2px; font-size:10px; text-align:center; }
.form-section-small { margin-bottom:15px; padding-bottom:12px; border-bottom:1px solid #eee; }
.form-section-small:last-child { border-bottom:none; margin-bottom:0; padding-bottom:0; }
.form-section-small h5 { font-size:11px; font-weight:600; color:#333; margin:0 0 10px; }
.section-header-small { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.form-check { display:flex; align-items:center; gap:5px; margin-top:6px; }
.form-check-input { width:13px; height:13px; cursor:pointer; }
.form-check-label { font-size:10px; color:#555; cursor:pointer; }
.spinner { display:inline-block; width:12px; height:12px; border:2px solid rgba(255,255,255,.3); border-radius:50%; border-top-color:#fff; animation:spin .8s linear infinite; margin-right:5px; }
.disabled-link { pointer-events:none !important; opacity:.5 !important; cursor:not-allowed !important; background-color:#e0e0e0 !important; color:#999 !important; border-color:#ccc !important; }
.credit-limit-badge { display:inline-block; padding:2px 6px; border-radius:10px; font-size:8px; font-weight:600; margin-left:5px; vertical-align:middle; }
.credit-limit-badge.warning { background:#fff3cd; color:#856404; border:1px solid #ffeeba; }
.credit-limit-badge.danger  { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.available-credit { display:block; font-size:9px; color:#28a745; margin-top:2px; font-weight:normal; }
#warehouseSelectSection { background:#f8f9fa; border:2px dashed #ddd; border-radius:8px; padding:30px 20px; margin-bottom:5px; }

.mobile-items-container {
    display: none;
    flex-direction: column;
    gap: 12px;
}
.mobile-item-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 10px;
}
.mobile-item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 8px;
    margin-bottom: 8px;
}
.mobile-item-title-wrap {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.mobile-item-title {
    font-size: 13px;
    font-weight: 600;
    color: #333;
}
.mobile-item-sub {
    font-size: 10px;
    color: #666;
}
.btn-delete-mobile {
    background: none;
    border: none;
    font-size: 16px;
    cursor: pointer;
    color: #dc3545;
    padding: 2px 6px;
}
.mobile-item-grid-prices {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 12px;
}
.mobile-item-grid-qty {
    display: grid;
    grid-template-columns: 55% 45%;
    gap: 10px;
    margin-bottom: 12px;
    align-items: flex-end;
}
.mobile-item-col-qty {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.mobile-qty-controls {
    display: flex;
    align-items: center;
    border: 1px solid #ccc;
    border-radius: 4px;
    overflow: hidden;
    height: 28px;
    background: #fff;
}
.mobile-qty-btn {
    width: 30px;
    height: 100%;
    background: #f8f9fa;
    border: none;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    color: #fa8725;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
    user-select: none;
    padding: 0;
    margin: 0;
}
.mobile-qty-btn:active {
    background: #e9ecef;
}
.mobile-qty-controls .qty-edit-mobile {
    flex: 1;
    border: none !important;
    border-left: 1px solid #ccc !important;
    border-right: 1px solid #ccc !important;
    height: 100% !important;
    border-radius: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
}
.mobile-item-col-warranty {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.mobile-item-col {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.mobile-item-label {
    font-size: 9px;
    font-weight: 500;
    color: #666;
    text-transform: uppercase;
}
.mobile-item-input {
    width: 100%;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 11px;
    text-align: center;
    background: #fff;
    box-sizing: border-box;
}
.mobile-item-input:focus {
    outline: none;
    border-color: #fa8725;
}
.mobile-item-footer-clean {
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid #f0f0f0;
    padding-top: 8px;
    margin-top: 4px;
}
.mobile-warranty-inputs {
    display: flex;
    gap: 4px;
}
.mobile-warranty-inputs .w-period {
    width: 35%;
}
.mobile-warranty-inputs .w-type {
    width: 65%;
    text-align: left;
}
.mobile-item-amount-wrap {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}
.mobile-item-amount-label {
    font-size: 9px;
    color: #888;
}
.mobile-item-amount-val {
    font-size: 14px;
    font-weight: 700;
    color: #fa8725;
}
.mobile-sticky-bottom-bar {
    display: none;
    position: fixed;
    bottom: 60px;
    left: 0;
    width: 100%;
    background: #fff;
    border-top: 1px solid #e0e0e0;
    box-shadow: 0 -3px 10px rgba(0,0,0,0.08);
    padding: 10px 16px;
    z-index: 9998;
    justify-content: space-between;
    align-items: center;
    box-sizing: border-box;
}
.mobile-sticky-total {
    display: flex;
    flex-direction: column;
}
.mobile-sticky-label {
    font-size: 10px;
    color: #666;
}
.mobile-sticky-val {
    font-size: 18px;
    font-weight: 700;
    color: #fa8725;
}
.btn-mobile-save {
    background: #fa8725;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(250,135,37,0.2);
}
.btn-mobile-save:active {
    background: #e07212;
}
.product-sub-txt {
    display: none;
    font-size: 9px;
    color: #666;
    margin-top: 3px;
}
.product-qty-selector {
    display: flex;
    align-items: center;
    border: 1px solid #ccc;
    border-radius: 4px;
    overflow: hidden;
    height: 24px;
    width: 80px;
    margin: 0 auto;
    background: #fff;
}
.prod-qty-btn {
    width: 22px;
    height: 100%;
    background: #f8f9fa;
    border: none;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    color: #fa8725;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin: 0;
    user-select: none;
}
.prod-qty-btn:active {
    background: #e9ecef;
}
.product-qty-selector .qty-input {
    flex: 1;
    border: none !important;
    border-left: 1px solid #ccc !important;
    border-right: 1px solid #ccc !important;
    height: 100% !important;
    border-radius: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
    text-align: center;
    font-size: 11px;
    width: 36px !important;
}
.row-selected-active {
    background-color: #fff8f2 !important;
}
body.modal-open {
    overflow: hidden !important;
}
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:20000; align-items:center; justify-content:center; }

@media (max-width: 768px) {
    .two-col-row,.invoice-row { flex-direction:column; }
    .col-50,.form-row .col-6 { width:100%; }
    .address-grid,.customer-info-grid { grid-template-columns:1fr; }
    
    /* Toggle table / mobile card views */
    .items-table-container { display: none !important; }
    .mobile-items-container { display: flex !important; }
    
    /* Sticky bottom bar on mobile */
    .mobile-sticky-bottom-bar { display: flex; }
    
    /* Give padding at the bottom of form so it doesn't get covered by sticky bar */
    .invoice-form-wrapper {
        padding: 0 !important;
        background: transparent !important;
        border: none !important;
        padding-bottom: 140px !important;
    }
    .dashboard-content {
        padding: 5px !important;
    }
    .form-section {
        border-radius: 6px !important;
        margin-bottom: 12px !important;
    }
    
    /* Selected customer details layout on mobile */
    .selected-customer-details {
        padding: 10px !important;
    }
    .selected-customer-details .customer-header {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        gap: 6px !important;
        border-bottom: 1px solid #f0f0f0 !important;
        padding-bottom: 6px !important;
        margin-bottom: 8px !important;
    }
    .selected-customer-details .customer-header h4 {
        font-size: 12px !important;
        margin: 0 !important;
    }
    .selected-customer-details .customer-header .party-type-badge {
        margin-left: 0 !important;
        padding: 1px 6px !important;
        font-size: 8px !important;
    }
    .selected-customer-details .btn-change-customer {
        margin-left: auto !important;
        padding: 2px 6px !important;
        font-size: 9px !important;
        background: #fa8725 !important;
    }
    
    /* Keep the info grid double columns on mobile too to save height! */
    .selected-customer-details .customer-info-grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 6px 12px !important;
        margin-bottom: 8px !important;
    }
    .selected-customer-details .info-column .info-row {
        margin-bottom: 2px !important;
    }
    .selected-customer-details .info-column .info-label {
        width: 60px !important;
        font-size: 9px !important;
        color: #777 !important;
    }
    .selected-customer-details .info-column .info-value {
        font-size: 9px !important;
        font-weight: 500 !important;
    }

    /* Address sections - extremely compact */
    .selected-customer-details .address-section {
        border-top: 1px dashed #e0e0e0 !important;
        padding-top: 6px !important;
        margin-top: 4px !important;
    }
    .selected-customer-details .address-header {
        display: none !important;
    }
    .selected-customer-details .address-grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 4px !important;
    }
    .selected-customer-details .address-card {
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }
    .selected-customer-details .address-card-header {
        display: inline !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
        border-bottom: none !important;
    }
    .selected-customer-details .address-type {
        font-size: 9px !important;
        font-weight: 700 !important;
        color: #666 !important;
        text-transform: uppercase !important;
    }
    .selected-customer-details .address-type::after {
        content: ": " !important;
    }
    .selected-customer-details .address-content {
        display: inline !important;
        font-size: 9px !important;
        color: #333 !important;
    }
    .selected-customer-details .address-content p {
        display: inline !important;
        margin: 0 !important;
    }
    
    /* Hide the desktop submit button on mobile */
    .btn-submit-invoice { display: none !important; }

    /* Hide columns in party select modal on mobile */
    .th-email, .th-status, .th-action,
    .party-email, .party-status-col, .party-action-col {
        display: none !important;
    }

    /* Hide columns in product select modal on mobile */
    .th-code, .th-stock, .product-code-col, .product-stock-col {
        display: none !important;
    }
    .product-sub-txt {
        display: block;
    }

    /* Full-screen modals on mobile viewport */
    .modal {
        align-items: flex-start !important;
        justify-content: flex-start !important;
    }
    .modal-content {
        width: 100% !important;
        max-width: 100% !important;
        height: 100% !important;
        max-height: 100vh !important;
        border-radius: 0 !important;
        margin: 0 !important;
        display: flex !important;
        flex-direction: column !important;
    }
    .modal-body {
        flex: 1 !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        padding: 15px !important;
    }
    .modal-content > form {
        display: flex !important;
        flex-direction: column !important;
        flex: 1 !important;
        overflow: hidden !important;
        margin: 0 !important;
    }
    .modal-header, .modal-actions {
        flex-shrink: 0 !important;
    }
    .parties-table-container, .products-table-container {
        max-height: none !important;
        overflow: visible !important;
        border: none !important;
    }
    .parties-table th {
        position: static !important;
    }

    /* Stacking Search and Warehouse buttons on mobile select items modal */
    #productSearchSection > div {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 8px !important;
    }
    #productSearchSection .search-container {
        width: 100% !important;
    }
    #productSearchSection div[style*="flex-shrink"] {
        display: flex !important;
        justify-content: space-between !important;
        width: 100% !important;
        gap: 8px !important;
    }
    #selectedWarehouseName {
        flex: 1 !important;
        text-align: center !important;
        font-size: 10px !important;
        padding: 6px 8px !important;
    }
    #productSearchSection button {
        padding: 6px 12px !important;
        font-size: 11px !important;
    }

    /* Transform select product table to stack of cards on mobile */
    #productsTableWrapper table, 
    #productsTableWrapper thead, 
    #productsTableWrapper tbody, 
    #productsTableWrapper tr, 
    #productsTableWrapper td {
        display: block !important;
    }
    #productsTableWrapper thead {
        display: none !important; /* Hide header completely on mobile */
    }
    #productsTableBody {
        display: flex !important;
        flex-direction: column !important;
        gap: 10px !important;
        padding: 5px 0 !important;
    }
    #productsTableBody tr {
        background: #fff !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 8px !important;
        padding: 12px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
        position: relative !important;
        cursor: pointer !important;
        transition: background-color 0.15s !important;
    }
    #productsTableBody tr.row-selected-active {
        background-color: #fff8f2 !important;
        border-color: #fa8725 !important;
    }
    
    /* Cell layout adjustments */
    #productsTableBody td {
        padding: 0 !important;
        border: none !important;
        background: transparent !important;
        text-align: left !important;
    }
    
    /* 1. Name & details (top left) */
    #productsTableBody .product-name {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #333 !important;
        margin-bottom: 8px !important;
        padding-right: 40px !important; /* leaves space for absolute checkbox */
    }
    #productsTableBody .product-name .product-sub-txt {
        display: block !important;
        font-size: 10px !important;
        color: #666 !important;
        font-weight: 400 !important;
        margin-top: 3px !important;
    }
    
    /* 2. Hide redundant columns */
    #productsTableBody .product-code-col,
    #productsTableBody .product-stock-col {
        display: none !important;
    }
    
    /* 3. MRP and Sale Price side by side in a small flex row */
    #productsTableBody .product-mrp,
    #productsTableBody .product-price {
        display: inline-block !important;
        vertical-align: top !important;
        font-size: 11px !important;
        margin-bottom: 8px !important;
    }
    #productsTableBody .product-mrp {
        color: #999 !important;
        text-decoration: line-through !important;
        margin-right: 12px !important;
    }
    #productsTableBody .product-mrp::before {
        content: "MRP: " !important;
        text-decoration: none !important;
        display: inline-block !important;
    }
    #productsTableBody .product-price {
        color: #fa8725 !important;
        font-weight: 600 !important;
    }
    #productsTableBody .product-price::before {
        content: "Sale: " !important;
        color: #555 !important;
        font-weight: 400 !important;
    }
    
    /* 4. Bottom row: Position Qty Selector absolutely on the bottom-right of the card */
    #productsTableBody .product-qty-col {
        position: absolute !important;
        bottom: 12px !important;
        right: 12px !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        display: block !important;
        width: auto !important;
        height: auto !important;
    }
    #productsTableBody .product-qty-col::before {
        display: none !important; /* Hide "Qty to Add:" label completely */
    }
    #productsTableBody .product-qty-selector {
        display: flex !important;
        align-items: center !important;
        border: 1px solid #ccc !important;
        border-radius: 4px !important;
        overflow: hidden !important;
        height: 26px !important;
        width: 80px !important;
        background: #fff !important;
        margin: 0 !important;
    }
    #productsTableBody .prod-qty-btn {
        width: 22px !important;
        height: 100% !important;
        font-size: 13px !important;
        background: #f8f9fa !important;
        border: none !important;
        color: #fa8725 !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 !important;
        margin: 0 !important;
        user-select: none !important;
    }
    #productsTableBody .prod-qty-btn:active {
        background: #e9ecef !important;
    }
    #productsTableBody .product-qty-selector .qty-input {
        flex: 1 !important;
        border: none !important;
        border-left: 1px solid #ccc !important;
        border-right: 1px solid #ccc !important;
        height: 100% !important;
        border-radius: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        text-align: center !important;
        font-size: 11px !important;
        width: 36px !important;
    }
    
    /* 5. Select Checkbox positioned absolutely in top right corner */
    #productsTableBody .product-select-col {
        position: absolute !important;
        top: 12px !important;
        right: 12px !important;
        margin: 0 !important;
    }
    #productsTableBody .select-product {
        width: 18px !important;
        height: 18px !important;
        cursor: pointer !important;
        margin: 0 !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
// ===================== GLOBALS =====================
window.currentPartyCreditInfo = null;

// ── Load existing items — preserve gst_inclusive flag from DB ──
let items = {!! json_encode($invoice->items->map(function($item) use ($invoice) {
    return [
        'product_id'    => $item->product_id,
        'variant_id'    => $item->variant_id,
        'product_type'  => $item->variant_id ? 'variant' : 'simple',
        'name'          => $item->variant_name ? $item->variant_name : $item->product_name,
        'sku'           => $item->sku,
        'hsn_sac'       => $item->hsn_sac,
        'mrp_price'     => (float) $item->mrp_price,
        // ── KEY: if inclusive, show sale_price_incl in the price field so the
        //    user sees & edits the gross (inclusive) price — same as at create time
        'price'         => $item->gst_inclusive && $item->sale_price_incl
                            ? (float) $item->sale_price_incl
                            : (float) $item->price,
        'quantity'      => (float) $item->quantity,
        'discount'      => (float) $item->discount,
        'tax_percent'   => (float) $item->tax_percent,
        'unit'          => $item->unit,
        'warranty_type' => $item->warranty_type,
        'warranty_period' => (int) $item->warranty_period,
        'warehouse_id'  => $invoice->warehouse_id,
        'max_stock'     => 0,
        'gst_inclusive' => (bool) ($item->gst_inclusive ?? false),
    ];
})->values()) !!};

let currentPartyType = '{{ optional($invoice->party)->party_type ?? "customer" }}';
let cgstTotal        = {{ $invoice->cgst_total ?? 0 }};
let sgstTotal        = {{ $invoice->sgst_total ?? 0 }};
let igstTotal        = {{ $invoice->igst_total ?? 0 }};
let currentTaxType   = '{{ $invoice->tax_type ?? "intra" }}';
let isSubmitting     = false;
let currentInvoiceType = '{{ $invoice->invoice_type ?? "gst" }}';
let currentGstMode   = '{{ $invoice->gst_mode ?? "exclusive" }}';

// ===================== GST MODE =====================

function setGstMode(mode) {
    if (items.length > 0) {
        if (!confirm('Changing GST mode will clear current items. Continue?')) return;
        items = [];
    }
    currentGstMode = mode;
    $('#gstModeInput').val(mode);
    $('.gst-mode-btn').removeClass('active');
    $(`.gst-mode-btn[data-mode="${mode}"]`).addClass('active');
    $('#subtotalNote').text(mode === 'inclusive' ? '(ex-GST extracted)' : '');
    updatePriceColumnHeaders();
    renderItemsTable();
}

// ===================== INVOICE DISPLAY SETUP =====================

function showGSTInvoiceFields() {
    $('.items-table-container').removeClass('cash-mode').addClass('gst-mode');
    $('#hsnHeader, #taxHeader, .th-hsn, .th-tax').show();
    $('#taxBreakupContainer').show();
    $('#totalTaxRow').show();
    $('#footerTax').show();
    $('#itemsTableFooter tr td:first-child').attr('colspan', '5');
}

function showCashMemoFields() {
    $('.items-table-container').removeClass('gst-mode').addClass('cash-mode');
    $('#hsnHeader, #taxHeader, .th-hsn, .th-tax').hide();
    $('#taxBreakupContainer').hide();
    $('#totalTaxRow').hide();
    $('#footerTax').hide();
    $('#itemsTableFooter tr td:first-child').attr('colspan', '4');
    cgstTotal = 0; sgstTotal = 0; igstTotal = 0;
}

// ===================== PARTY MODAL =====================

function openSelectPartyModal()  { $('#selectPartyModal').css('display', 'flex'); loadParties(); }
function closeSelectPartyModal() { $('#selectPartyModal').hide(); $('#searchParty').val(''); }
function openCreatePartyModal()  { closeSelectPartyModal(); $('#createPartyModal').css('display', 'flex'); }
function closeCreatePartyModal() { $('#createPartyModal').hide(); $('#createPartyForm')[0].reset(); $('#sameBillingShipping').prop('checked', false); }

function loadParties(search = '') {
    const tbody = $('#partiesTableBody'), loading = $('#partiesLoading'), noData = $('#noParties');
    tbody.empty(); loading.show(); noData.hide();
    $.get('{{ route('admin.sales.parties.list') }}', {
        search: search,
        party_type: currentPartyType !== 'all' ? currentPartyType : null
    }, function(response) {
        loading.hide();
        if (response.parties.length === 0) { noData.show(); return; }
        response.parties.forEach(party => {
            const sc = party.status === 'active' ? 'status-active' : 'status-inactive';
            tbody.append(`<tr><td class="party-name">${party.name}</td><td>${party.party_type_text}</td><td class="party-phone">${party.phone||'-'}</td><td class="party-email">${party.email||'-'}</td><td><span class="status-badge ${sc}">${party.status}</span></td><td><button type="button" onclick="selectParty('${party.id}')" style="background:none;border:none;cursor:pointer;font-size:16px;color:#28a745;">✓</button></td></tr>`);
        });
    }).fail(function() { loading.hide(); showAlert('Failed to load parties', 'error'); });
}

// ===================== TAX BREAKUP =====================

function showIntraStateTax() { $('#intraStateTax').show(); $('#interStateTax').hide(); currentTaxType = 'intra'; calculateTaxBreakup(); }
function showInterStateTax() { $('#intraStateTax').hide(); $('#interStateTax').show(); currentTaxType = 'inter'; calculateTaxBreakup(); }

function calculateTaxBreakup() {
    if (currentInvoiceType !== 'gst') {
        cgstTotal = sgstTotal = igstTotal = 0;
        $('#cgstTotal, #sgstTotal, #igstTotal, #totalTax').text('0.00');
        return;
    }
    cgstTotal = 0; sgstTotal = 0; igstTotal = 0;
    items.forEach(item => {
        const taxPercent = parseFloat(item.tax_percent) || 0;
        const isIncl     = item.gst_inclusive;
        let itemTax;
        if (isIncl && taxPercent > 0) {
            const gross = item.quantity * item.price;
            itemTax = gross - (gross * 100 / (100 + taxPercent));
        } else {
            itemTax = (item.quantity * item.price * taxPercent) / 100;
        }
        if (currentTaxType === 'intra') { cgstTotal += itemTax / 2; sgstTotal += itemTax / 2; }
        else igstTotal += itemTax;
    });
    $('#cgstTotal').text(cgstTotal.toFixed(2));
    $('#sgstTotal').text(sgstTotal.toFixed(2));
    $('#igstTotal').text(igstTotal.toFixed(2));
    $('#totalTax').text((cgstTotal + sgstTotal + igstTotal).toFixed(2));
}

// ===================== PRICING HELPERS =====================

function getCurrentPartyType() { return $('#partyTypeInput').val() || null; }

function updateAddItemButtonState() {
    const disabled = !getCurrentPartyType();
    $('#addItemBtn').prop('disabled', disabled).css({ opacity: disabled ? '0.5' : '1', cursor: disabled ? 'not-allowed' : 'pointer' });
}

function getPriceColumnHeader() {
    const pt = getCurrentPartyType();
    const modeLabel = (currentInvoiceType === 'gst' && currentGstMode === 'inclusive') ? ' (Incl. GST)' : ' (Excl. GST)';
    if (pt === 'dealer')      return 'Dealer Price ₹' + modeLabel;
    if (pt === 'distributor') return 'Distributor Price ₹' + modeLabel;
    return 'Sale Price ₹' + modeLabel;
}

function updatePriceColumnHeaders() {
    const h = getPriceColumnHeader();
    $('#priceColumnHeader').text(h);
    $('#modalPriceColumnHeader').text(h);
}

function getProductPrice(product, partyType) {
    if (partyType === 'dealer')      return parseFloat(product.dealer_price || 0);
    if (partyType === 'distributor') return parseFloat(product.distributor_price || 0);
    return parseFloat(product.sale_price || 0);
}

// ===================== ADD ITEM MODAL =====================

function openAddItemModal() {
    if (!getCurrentPartyType()) { showAlert('Please select a party first', 'error'); return; }
    resetWarehouseSelection();
    $('#addItemModal').css('display', 'flex');
}
function closeAddItemModal() { $('#addItemModal').hide(); $('#searchProduct').val(''); }

function loadProducts(search = '', warehouseId = null) {
    const tbody = $('#productsTableBody'), loading = $('#productsLoading');
    const partyType = getCurrentPartyType();
    tbody.empty(); loading.show();
    const wh = warehouseId || $('#itemModalWarehouse').val() || '{{ $mainWarehouse->_id }}';
    $.get('{{ route('admin.sales.get-main-warehouse-products') }}', { search, warehouse_id: wh }, function(response) {
        loading.hide();
        if (!response.products.length) { tbody.html('<tr><td colspan="7" style="padding:40px;text-align:center;"><div style="font-size:32px;opacity:.3;">📦</div><p style="font-size:11px;color:#6b7280;">No products found</p></td></tr>'); return; }
        response.products.forEach(product => {
            const price = getProductPrice(product, partyType);
            const mrp   = parseFloat(product.mrp_price || 0);
            const disc  = mrp > 0 && price < mrp ? ((mrp-price)/mrp*100).toFixed(2) : 0;
            
            tbody.append(`<tr onclick="toggleProductRowCheckbox(this, event)" style="cursor: pointer;">
                <td class="product-name">
                    <div class="product-name-txt"><strong>${product.name}</strong></div>
                    <div class="product-sub-txt">${product.sku || '-'} | Stock: ${product.current_stock} ${product.unit}</div>
                </td>
                <td class="product-code-col">${product.sku || '-'}</td>
                <td class="product-mrp">₹ ${mrp.toFixed(2)}</td>
                <td class="product-price">₹ ${price.toFixed(2)}${disc > 0 ? `<br><small style="color:#28a745;">(${disc}% off)</small>` : ''}</td>
                <td class="product-stock-col">${product.current_stock} ${product.unit}</td>
                <td class="product-qty-col" onclick="event.stopPropagation()">
                    <div class="product-qty-selector">
                        <button type="button" class="prod-qty-btn minus" onclick="adjustProductModalQty(this, -1)">−</button>
                        <input type="number" class="qty-input" min="1" max="${product.current_stock}" value="1"
                            data-product-id="${product.id}" data-variant-id="${product.variant_id || ''}"
                            data-type="${product.type}" data-price="${price}" data-mrp="${mrp}"
                            data-stock="${product.current_stock}" data-name="${product.name}"
                            data-sku="${product.sku}" data-hsn="${product.hsn_code || ''}"
                            data-tax="${product.tax_percent || 0}" data-unit="${product.unit}"
                            data-warranty-type="${product.warranty_type}" data-warranty-period="${product.warranty_period}">
                        <button type="button" class="prod-qty-btn plus" onclick="adjustProductModalQty(this, 1)">+</button>
                    </div>
                </td>
                <td class="product-select-col" onclick="event.stopPropagation()">
                    <input type="checkbox" class="select-product" onchange="toggleRowActive(this)">
                </td>
            </tr>`);
        });
    }).fail(function() { loading.hide(); tbody.html('<tr><td colspan="7" style="text-align:center;padding:30px;color:#c33;">Failed to load</td></tr>'); });
}

function toggleProductRowCheckbox(row, event) {
    if (event.target.closest('input, select, button, a')) return;
    const checkbox = $(row).find('.select-product');
    const checked = checkbox.is(':checked');
    checkbox.prop('checked', !checked);
    toggleRowActive(checkbox);
}

function toggleRowActive(checkbox) {
    const row = $(checkbox).closest('tr');
    if ($(checkbox).is(':checked')) {
        row.addClass('row-selected-active');
    } else {
        row.removeClass('row-selected-active');
    }
}

function adjustProductModalQty(btn, amount) {
    const input = $(btn).siblings('.qty-input');
    const currentVal = parseFloat(input.val()) || 1;
    const newVal = Math.max(1, currentVal + amount);
    const maxStock = parseFloat(input.attr('max')) || 9999;
    
    if (newVal > maxStock) {
        showAlert(`❌ Only ${maxStock} units available in stock`, 'error');
        return;
    }
    
    input.val(newVal);
    
    // Auto-check the product row if quantity is changed
    const checkbox = $(btn).closest('tr').find('.select-product');
    if (!checkbox.is(':checked')) {
        checkbox.prop('checked', true);
        toggleRowActive(checkbox);
    }
}

function addSelectedProducts() {
    const selectedWarehouseId = $('#itemModalWarehouse').val();
    if (!selectedWarehouseId) { showAlert('Please select a warehouse', 'error'); return; }
    const hasSelected = $('#productsTableBody tr').toArray().some(row => $(row).find('.select-product').is(':checked'));
    if (!hasSelected) { closeAddItemModal(); return; }
    const existingWH = items.length > 0 ? items[0].warehouse_id : null;
    if (existingWH && existingWH !== selectedWarehouseId) { items = []; showAlert('Warehouse changed — previous items removed', 'info'); }
    $('input[name="warehouse_id"]').val(selectedWarehouseId);

    $('#productsTableBody tr').each(function() {
        const cb = $(this).find('.select-product');
        if (!cb.is(':checked')) return;
        const input = $(this).find('.qty-input');
        const qty = parseFloat(input.val()) || 0;
        const maxStock = parseFloat(input.data('stock'));
        if (qty <= 0 || qty > maxStock) { if (qty > maxStock) showAlert(`Only ${maxStock} items available`, 'error'); return; }
        const price = parseFloat(input.data('price')) || 0;
        const mrp   = parseFloat(input.data('mrp')) || 0;
        const autoDisc = mrp > 0 && mrp > price ? ((mrp-price)/mrp)*100 : 0;
        const isIncl = currentInvoiceType === 'gst' && currentGstMode === 'inclusive';
        addItemToInvoice({
            product_id: input.data('product-id'), variant_id: input.data('variant-id'),
            product_type: input.data('type'), name: input.data('name'), sku: input.data('sku'),
            hsn_sac: currentInvoiceType === 'gst' ? input.data('hsn') : '',
            mrp_price: mrp, price, quantity: qty,
            discount: parseFloat(autoDisc.toFixed(2)),
            tax_percent: currentInvoiceType === 'gst' ? parseFloat(input.data('tax')) || 0 : 0,
            unit: input.data('unit') || 'PCS',
            warranty_type: input.data('warranty-type') || 'none',
            warranty_period: parseInt(input.data('warranty-period')) || 0,
            warehouse_id: selectedWarehouseId, max_stock: maxStock,
            gst_inclusive: isIncl,
        });
    });
    const wName = $('#itemModalWarehouse option:selected').text().trim();
    if (wName && wName !== '-- Select Warehouse --') { $('#selectedWarehouseBadgeText').text(wName); $('#selectedWarehouseBadge').show(); }
    closeAddItemModal();
}

function addItemToInvoice(item) {
    const idx = items.findIndex(i => i.product_id === item.product_id && i.variant_id === item.variant_id);
    if (idx > -1) {
        const newQty = items[idx].quantity + item.quantity;
        const max    = items[idx].max_stock || item.max_stock || 0;
        if (max > 0 && newQty > max) { showAlert(`❌ ${item.name} — only ${max - items[idx].quantity} more available`, 'error'); return; }
        items[idx].quantity = newQty;
        showAlert(`Updated qty for ${item.name}`, 'info');
    } else {
        items.push(item);
        showAlert(`Added ${item.name}`, 'success');
    }
    renderItemsTable();
}

function onWarehouseChange() {
    const wid = $('#itemModalWarehouse').val();
    if (!wid) return;
    $('#warehouseSelectSection').hide();
    $('#productSearchSection').show();
    $('#productsTableWrapper').show();
    $('#selectedWarehouseName').text('🏭 ' + $('#itemModalWarehouse option:selected').text());
    loadProducts('', wid);
}

function resetWarehouseSelection() {
    $('#warehouseSelectSection').show();
    $('#productSearchSection, #productsTableWrapper').hide();
    $('#itemModalWarehouse').val('');
    $('#productsTableBody').empty();
    $('#searchProduct').val('');
}

function updateItem(index, field, value) {
    if (!items[index]) return;
    if (field === 'quantity') {
        const newQty = parseFloat(value) || 0;
        const max    = items[index].max_stock || 0;
        if (newQty <= 0) { showAlert('Quantity must be > 0', 'error'); renderItemsTable(); return; }
        if (max > 0 && newQty > max) { showAlert(`❌ Only ${max} units available for ${items[index].name}`, 'error'); renderItemsTable(); return; }
    }
    items[index][field] = parseFloat(value) || 0;
    if (field === 'discount') {
        const mrp = parseFloat(items[index].mrp_price || 0);
        const pct = parseFloat(value || 0);
        if (mrp > 0 && pct >= 0 && pct <= 100) items[index].price = mrp - (mrp * pct / 100);
    }
    if (field === 'price') {
        const mrp = parseFloat(items[index].mrp_price || 0), sale = parseFloat(value || 0);
        items[index].discount = (mrp > 0 && sale <= mrp) ? ((mrp - sale) / mrp) * 100 : 0;
    }
    renderItemsTable();
}

function removeItem(index)              { items.splice(index, 1); renderItemsTable(); }
function updateWarrantyType(i, v)       { items[i].warranty_type   = v; }
function updateWarrantyPeriod(i, v)     { items[i].warranty_period = parseInt(v) || 0; }

// ===================== RENDER ITEMS TABLE =====================

function adjustMobileQty(index, amount) {
    if (!items[index]) return;
    const currentQty = parseFloat(items[index].quantity) || 1;
    const newQty = Math.max(1, currentQty + amount);
    const maxStock = parseFloat(items[index].max_stock) || 9999;
    
    if (newQty > maxStock) {
        showAlert(`❌ Only ${maxStock} units available for ${items[index].name}`, 'error');
        return;
    }
    
    items[index].quantity = newQty;
    renderItemsTable();
}

function renderItemsTable() {
    const tbody       = $('#itemsTableBody');
    const mobileContainer = $('#mobileItemsContainer');
    const invoiceType = currentInvoiceType;

    tbody.empty();
    mobileContainer.empty();
    enableExtraFields();

    if (items.length === 0) {
        const cols = invoiceType === 'gst' ? 11 : 9;
        tbody.html(`<tr class="empty-row"><td colspan="${cols}"><div class="empty-items"><div class="empty-icon">🛒</div><p>No items added yet</p><button type="button" class="btn-add-first-item" onclick="openAddItemModal()">+ Add First Item</button></div></td></tr>`);
        mobileContainer.html(`<div class="empty-items" style="padding: 20px; text-align: center; border: 1px dashed #ccc; border-radius: 6px; background: #fff;"><div class="empty-icon" style="font-size: 24px; margin-bottom: 8px;">🛒</div><p style="font-size: 11px; color: #666; margin-bottom: 10px;">No items added yet</p><button type="button" class="btn-small" onclick="openAddItemModal()" style="border-color: #fa8725; color: #fa8725; padding: 6px 14px; font-size: 11px;">+ Add First Item</button></div>`);
        $('#itemsTableFooter').hide();
        $('#totalMRP,#totalDiscount,#cgstTotal,#sgstTotal,#igstTotal,#totalTax,#subtotal,#grandTotal,#mobileStickyGrandTotal').text('0.00');
        calculateBalance();
        return;
    }

    $('#itemsTableFooter').show();
    $('#itemsTableFooter tr td:first-child').attr('colspan', invoiceType === 'gst' ? 5 : 4);
    updatePriceColumnHeaders();

    let fMRP = 0, fDisc = 0, fBase = 0, fTax = 0, fFinal = 0;

    items.forEach((item, index) => {
        const mrpPrice   = parseFloat(item.mrp_price || 0);
        const salePrice  = parseFloat(item.price || 0);
        const quantity   = parseFloat(item.quantity || 1);
        const discountPct = parseFloat(item.discount || 0);
        const taxPercent  = invoiceType === 'gst' ? parseFloat(item.tax_percent || 0) : 0;
        const isIncl      = item.gst_inclusive && invoiceType === 'gst';

        const mrpTotal = quantity * mrpPrice;

        let displayBaseTotal, displayTaxAmt, displayFinalTotal, discountAmount;

        if (isIncl && taxPercent > 0) {
            const grossTotal   = quantity * salePrice;
            const baseTotal    = grossTotal * 100 / (100 + taxPercent);
            displayTaxAmt      = grossTotal - baseTotal;
            displayBaseTotal   = baseTotal;
            displayFinalTotal  = grossTotal;
            discountAmount     = (mrpPrice - salePrice) * quantity;
        } else {
            displayBaseTotal   = quantity * salePrice;
            displayTaxAmt      = (displayBaseTotal * taxPercent) / 100;
            displayFinalTotal  = displayBaseTotal + displayTaxAmt;
            discountAmount     = (mrpPrice - salePrice) * quantity;
        }

        fMRP            += mrpTotal;
        fDisc           += discountAmount;
        fBase           += displayBaseTotal;
        fTax            += displayTaxAmt;
        fFinal          += displayFinalTotal;

        const inclBadge = isIncl ? `<span class="incl-badge">INCL</span>` : '';

        // Render Desktop Row
        let row = `<tr class="${isIncl ? 'row-inclusive' : ''}">
            <td class="item-name">${item.name}${inclBadge}</td>`;

        if (invoiceType === 'gst') row += `<td class="item-hsn">${item.hsn_sac || '-'}</td>`;

        row += `
            <td class="item-unit">${item.unit || 'PCS'}</td>
            <td class="item-qty">
                <input type="number" class="qty-edit" min="1" max="${item.max_stock || 9999}" value="${quantity}"
                    onchange="updateItem(${index}, 'quantity', this.value)">
            </td>
            <td class="item-warranty">
                <div class="warranty-wrapper">
                    <input type="number" min="0" value="${item.warranty_period || 0}"
                        onchange="updateWarrantyPeriod(${index}, this.value)" class="warranty-input">
                    <select onchange="updateWarrantyType(${index}, this.value)" class="warranty-select">
                        <option value="none" ${item.warranty_type === 'none' ? 'selected' : ''}>None</option>
                        <option value="month" ${item.warranty_type === 'month' ? 'selected' : ''}>Month(s)</option>
                        <option value="year" ${item.warranty_type === 'year' ? 'selected' : ''}>Year(s)</option>
                    </select>
                </div>
            </td>
            <td class="item-mrp" style="background:#f9f9f9;">₹ ${mrpPrice.toFixed(2)}</td>
            <td class="item-discount">
                <input type="number" class="discount-edit" min="0" max="100" step="0.01" value="${discountPct.toFixed(2)}"
                    onchange="updateItem(${index}, 'discount', this.value)">
            </td>
            <td class="item-sale-price">
                <input type="number" class="sale-price-edit" min="0" step="0.01" value="${salePrice.toFixed(2)}"
                    onchange="updateItem(${index}, 'price', this.value)">
            </td>`;

        if (invoiceType === 'gst') {
            row += `<td class="item-tax">
                <input type="number" class="tax-edit" min="0" max="100" step="0.01" value="${taxPercent.toFixed(2)}"
                    onchange="updateItem(${index}, 'tax_percent', this.value)">
            </td>`;
        }

        row += `
            <td class="item-amount">₹ ${displayFinalTotal.toFixed(2)}</td>
            <td class="item-action">
                <button type="button" class="btn-delete" onclick="removeItem(${index})" title="Remove">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/>
                    </svg>
                </button>
            </td></tr>`;

        tbody.append(row);

        // Render Mobile Card
        let card = `
        <div class="mobile-item-card" data-index="${index}">
            <div class="mobile-item-header">
                <div class="mobile-item-title-wrap">
                    <span class="mobile-item-title">${item.name}</span>
                    ${inclBadge}
                    <span class="mobile-item-sub">${item.sku || '-'} | ${item.unit || 'PCS'}</span>
                </div>
                <button type="button" class="btn-delete-mobile" onclick="removeItem(${index})" title="Remove">🗑️</button>
            </div>
            
            <!-- Row 1: Prices & Taxes -->
            <div class="mobile-item-grid-prices">
                <div class="mobile-item-col">
                    <label class="mobile-item-label">Sale Price (₹)</label>
                    <input type="number" class="mobile-item-input price-edit-mobile" min="0" step="0.01" value="${salePrice.toFixed(2)}"
                        onchange="updateItem(${index}, 'price', this.value)">
                </div>
                <div class="mobile-item-col">
                    <label class="mobile-item-label">Disc %</label>
                    <input type="number" class="mobile-item-input discount-edit-mobile" min="0" max="100" step="0.01" value="${discountPct.toFixed(2)}"
                        onchange="updateItem(${index}, 'discount', this.value)">
                </div>
                <div class="mobile-item-col">
                    <label class="mobile-item-label">Tax %</label>
                    ${invoiceType === 'gst' ? `
                        <input type="number" class="mobile-item-input tax-edit-mobile" min="0" max="100" step="0.01" value="${taxPercent.toFixed(2)}"
                            onchange="updateItem(${index}, 'tax_percent', this.value)">
                    ` : `
                        <input type="text" class="mobile-item-input" value="—" disabled style="background:#f1f1f1;text-align:center;">
                    `}
                </div>
            </div>

            <!-- Row 2: Quantity Adjuster & Warranty -->
            <div class="mobile-item-grid-qty">
                <div class="mobile-item-col-qty">
                    <label class="mobile-item-label">Quantity</label>
                    <div class="mobile-qty-controls">
                        <button type="button" class="mobile-qty-btn minus" onclick="adjustMobileQty(${index}, -1)">−</button>
                        <input type="number" class="mobile-item-input qty-edit-mobile" min="1" max="${item.max_stock || 9999}" value="${quantity}"
                            onchange="updateItem(${index}, 'quantity', this.value)">
                        <button type="button" class="mobile-qty-btn plus" onclick="adjustMobileQty(${index}, 1)">+</button>
                    </div>
                </div>
                <div class="mobile-item-col-warranty">
                    <label class="mobile-item-label">Warranty</label>
                    <div class="mobile-warranty-inputs">
                        <input type="number" min="0" value="${item.warranty_period || 0}"
                            onchange="updateWarrantyPeriod(${index}, this.value)" class="mobile-item-input w-period">
                        <select onchange="updateWarrantyType(${index}, this.value)" class="mobile-item-input w-type">
                            <option value="none" ${item.warranty_type === 'none' ? 'selected' : ''}>None</option>
                            <option value="month" ${item.warranty_type === 'month' ? 'selected' : ''}>Month(s)</option>
                            <option value="year" ${item.warranty_type === 'year' ? 'selected' : ''}>Year(s)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Footer: Final Amount -->
            <div class="mobile-item-footer-clean">
                <div class="mobile-item-amount-wrap">
                    <span class="mobile-item-amount-label">Final Amount</span>
                    <span class="mobile-item-amount-val">₹ ${displayFinalTotal.toFixed(2)}</span>
                </div>
            </div>
        </div>`;
        mobileContainer.append(card);
    });

    $('#footerMRP').text('₹ ' + fMRP.toFixed(2));
    $('#footerDiscount').text('₹ ' + fDisc.toFixed(2));
    $('#footerSalePrice').text('₹ ' + fBase.toFixed(2));
    $('#footerTax').text('₹ ' + fTax.toFixed(2));
    $('#footerFinalAmount').text('₹ ' + fFinal.toFixed(2));
    $('#mobileStickyGrandTotal').text(fFinal.toFixed(2));

    if (invoiceType === 'gst') $('#footerTax').show(); else $('#footerTax').hide();

    calculateTaxBreakup();
    calculateInvoiceSummary(fMRP, fDisc, fTax, fBase);
}

// ===================== SELECT PARTY =====================

function selectParty(partyId) {
    const currentId = $('#partyIdInput').val();
    if (currentId && currentId === partyId) { showAlert('This party is already selected', 'info'); closeSelectPartyModal(); return; }

    $.get('{{ route('admin.sales.get-party-details', ':id') }}'.replace(':id', partyId), function(res) {
        if (!res.success) return;
        const p = res.party;
        $('#partyNameDisplay').text(p.name);
        $('#partyTypeBadge').text(p.party_type_text).attr('class', `party-type-badge ${p.party_type}`);
        $('#partyPhone').text(p.phone || '-');
        $('#partyEmail').text(p.email || '-');
        $('#partyOpeningBalance').text('₹ ' + (p.dynamic_opening_balance || 0).toFixed(2));
        $('#partyCreditLimit').text(p.credit_limit ? '₹ ' + parseFloat(p.credit_limit).toFixed(2) : 'No Limit');
        $('#partyIdInput').val(p.id);
        $('#partyTypeInput').val(p.party_type);
        updatePriceColumnHeaders();
        updateAddItemButtonState();
        items = [];
        const wState = '{{ $mainWarehouse->state ?? "" }}';
        const pState = p.billing_state || '';
        if (wState && pState) { wState === pState ? showIntraStateTax() : showInterStateTax(); }
        else showIntraStateTax();
        renderItemsTable();
        if (p.salesman_id && p.salesman_name) { $('#salesmanIdInput').val(p.salesman_id); $('#salesmanNameDisplay').text('👤 ' + p.salesman_name); }
        else { $('#salesmanIdInput').val(''); $('#salesmanNameDisplay').text('—'); }
        window.selectedParty = p;
        $('#billingAddressText').text(p.billing_address || 'No billing address');
        $('#billingAddressInput').val(p.billing_address || '');
        $('#shippingAddressText').text(p.shipping_address || p.billing_address || 'No shipping address');
        $('#shippingAddressInput').val(p.shipping_address || p.billing_address || '');
        closeSelectPartyModal();
        showAlert('Party changed to ' + p.name, 'success');

        $('#creditNoteRemainingRow').remove();
        const cn = parseFloat(p.credit_notes_remaining || 0);
        if (cn > 0) {
            $('.info-column').first().append(`<div class="info-row" id="creditNoteRemainingRow"><span class="info-label">CN Balance:</span><span class="info-value" style="color:#047857;font-weight:600;">₹ ${cn.toFixed(2)} <small style="background:#d1fae5;color:#065f46;padding:1px 5px;border-radius:3px;font-size:9px;margin-left:4px;">WILL AUTO-ADJUST</small></span></div>`);
        }
        checkCreditLimitStatus(p.id);
        calculateBalance();
    });
}

function checkCreditLimitStatus(partyId) {
    $.get('/admin/sales/party-credit-status/' + partyId, function(res) {
        if (!res.success) return;
        window.currentPartyCreditInfo = res.credit_info;
        updateCreditDisplay(res.credit_info);
        const info = res.credit_info;
        if (info.has_limit) {
            if (info.is_exceeded) showAlert('⚠️ CREDIT LIMIT EXCEEDED! Current Due: ₹' + info.current_due.toFixed(2), 'error');
            else if (info.warning_level === 'warning') showAlert('⚠️ Approaching Credit Limit. Available: ₹' + info.available_credit.toFixed(2), 'error');
        }
    });
}

function validateCreditLimit(newBalance) {
    if (!window.currentPartyCreditInfo) return true;
    const info = window.currentPartyCreditInfo;
    if (!info.has_limit) return true;
    const newTotal = info.current_due + newBalance;
    if (newTotal > info.credit_limit) {
        showAlert('❌ Credit Limit Exceeded!\nCurrent Due: ₹' + info.current_due.toFixed(2) + '\nNew Balance: ₹' + newBalance.toFixed(2) + '\nTotal: ₹' + newTotal.toFixed(2) + '\nLimit: ₹' + info.credit_limit.toFixed(2), 'error');
        return false;
    }
    return true;
}

function updateCreditDisplay(info) {
    $('#partyOpeningBalance').text('₹ ' + info.opening_balance.toFixed(2));
    if (info.has_limit) {
        let cls = 'credit-limit-badge';
        if (info.warning_level === 'danger')   cls += ' danger';
        else if (info.warning_level === 'warning') cls += ' warning';
        $('#partyCreditLimit').html('₹ ' + info.credit_limit.toFixed(2) + ` <span class="${cls}">${info.usage_percent}% used</span>`);
        if (info.available_credit !== null) $('#partyCreditLimit').append(`<br><small class="available-credit">Available: ₹ ${info.available_credit.toFixed(2)}</small>`);
    } else {
        $('#partyCreditLimit').text('No Limit');
    }
}

$('#createPartyForm').submit(function(e) {
    e.preventDefault();
    const phone = $('input[name="phone"]').val();
    if (!/^[6-9]\d{9}$/.test(phone)) { showAlert('Please enter a valid 10-digit phone number', 'error'); return; }
    const formData = $(this).serializeArray();
    formData.push({ name: 'same_billing_shipping', value: $('#sameBillingShipping').is(':checked') ? '1' : '0' });
    $.ajax({
        url: '{{ route('admin.sales.create-party') }}', type: 'POST', data: $.param(formData),
        success: function(r) { if (r.success) { showAlert('Party created!', 'success'); closeCreatePartyModal(); selectParty(r.party_id); } else showAlert('Error: ' + r.message, 'error'); },
        error: function(xhr) { showAlert(xhr.responseJSON?.message || 'Failed', 'error'); }
    });
});

$(document).on('click', '.party-type-tab', function() {
    $('.party-type-tab').removeClass('active'); $(this).addClass('active');
    currentPartyType = $(this).data('type'); loadParties($('#searchParty').val());
});
$('#searchParty').on('input', function() {
    const v = this.value.toLowerCase();
    $('#partiesTableBody tr').each(function() {
        $(this).toggle($(this).find('td:first').text().toLowerCase().includes(v) || $(this).find('td:eq(2)').text().toLowerCase().includes(v));
    });
});
$('#searchProduct').on('input', function() {
    const v = this.value.toLowerCase();
    $('#productsTableBody tr').each(function() {
        $(this).toggle($(this).find('td:first').text().toLowerCase().includes(v) || $(this).find('td:eq(1)').text().toLowerCase().includes(v));
    });
});

function copyBillingToShipping() {
    const f = $('#createPartyForm');
    ['address','city','state','pincode','country'].forEach(k => f.find(`[name="shipping_${k}"]`).val(f.find(`[name="billing_${k}"]`).val()));
}
$('#sameBillingShipping').on('change', function() { if (this.checked) copyBillingToShipping(); });
$('input[name^="billing_"]').on('input', function() { if ($('#sameBillingShipping').is(':checked')) copyBillingToShipping(); });

// ===================== EXTRA DISCOUNT/CHARGE =====================

function toggleExtraDiscount() {
    if (!items.length) { showAlert('Please add at least one item first', 'error'); return; }
    $('#extraDiscountPercentRow').hide(); $('#extraDiscountPercent').val('');
    $('#extraDiscountRow').toggle();
    if ($('#extraDiscountRow').is(':visible')) { $('#extraDiscountTypeInput').val('amount'); $('#extraDiscount').focus(); }
    else $('#extraDiscount').val('');
    calculateTotals();
}
function toggleExtraDiscountPercent() {
    if (!items.length) { showAlert('Please add at least one item first', 'error'); return; }
    $('#extraDiscountRow').hide(); $('#extraDiscount').val('');
    $('#extraDiscountPercentRow').toggle();
    if ($('#extraDiscountPercentRow').is(':visible')) { $('#extraDiscountTypeInput').val('percent'); $('#extraDiscountPercent').focus(); }
    else $('#extraDiscountPercent').val('');
    calculateTotals();
}
function toggleExtraCharge() {
    if (!items.length) { showAlert('Please add at least one item first', 'error'); return; }
    $('#extraChargeRow').toggle();
    if (!$('#extraChargeRow').is(':visible')) { $('#extraCharge').val(''); $('#chargeName').val(''); calculateTotals(); }
}
function enableExtraFields() {
    if (items.length > 0) {
        $('#addDiscountLink, #addDiscountPercentLink, #addChargeLink').removeClass('disabled-link');
    } else {
        $('#addDiscountLink, #addDiscountPercentLink, #addChargeLink').addClass('disabled-link');
        $('#extraDiscountRow, #extraDiscountPercentRow, #extraChargeRow').hide();
        $('#extraDiscount, #extraDiscountPercent, #extraCharge, #chargeName').val('');
        $('#extraDiscountTypeInput').val('amount');
    }
}

// ===================== PAYMENT TERMS =====================

function updateDueDateFromTerms() {
    const d = $('#invoiceDate').val(), days = parseInt($('#paymentTermsDays').val()) || 0;
    if (!d || days <= 0) return;
    const dt = new Date(d); dt.setDate(dt.getDate() + days);
    $('#dueDate').val(dt.toISOString().split('T')[0]);
    $('#paymentTermsInput').val(`Due in ${days} days`);
}
function updatePaymentTermsFromDueDate() {
    const inv = $('#invoiceDate').val(), due = $('#dueDate').val();
    if (!inv || !due) return;
    const diff = Math.ceil(Math.abs(new Date(due) - new Date(inv)) / 86400000);
    $('#paymentTermsDays').val(diff); $('#paymentTermsInput').val(`Due in ${diff} days`);
}
function markFullyPaid()             { $('#amountPaid').val((parseFloat($('#grandTotal').text()) || 0).toFixed(2)); calculateBalance(); }
function validateAndCalculateBalance() {
    const g = parseFloat($('#grandTotal').text()) || 0, p = parseFloat($('#amountPaid').val()) || 0;
    $('#amountPaid').css('border-color', p > g ? '#dc3545' : '#ccc');
    calculateBalance();
}
function clampAmountPaid() {
    const g = parseFloat($('#grandTotal').text()) || 0;
    let p   = parseFloat($('#amountPaid').val()) || 0;
    if (p > g) { p = g; $('#amountPaid').val(p.toFixed(2)); $('#amountPaid').css('border-color','#ccc'); showAlert('Amount paid cannot exceed grand total','error'); }
    calculateBalance();
}

function calculateInvoiceSummary(totalMRP, totalDiscount, totalTax, subtotal) {
    const discType = $('#extraDiscountTypeInput').val();
    let extraDiscount = 0;
    if (discType === 'percent') {
        const pct = parseFloat($('#extraDiscountPercent').val()) || 0;
        if (pct > 0 && subtotal > 0) extraDiscount = subtotal * pct / 100;
    } else {
        extraDiscount = parseFloat($('#extraDiscount').val()) || 0;
    }
    const extraCharge = parseFloat($('#extraCharge').val()) || 0;
    let grand = (subtotal - extraDiscount) + (currentInvoiceType === 'gst' ? totalTax : 0) + extraCharge;
    if ($('#autoRoundOff').is(':checked')) grand = Math.round(grand);
    $('#totalMRP').text(totalMRP.toFixed(2));
    $('#totalDiscount').text(totalDiscount.toFixed(2));
    $('#subtotal').text(subtotal.toFixed(2));
    $('#grandTotal').text(grand.toFixed(2));
    clampAmountPaid();
}

function calculateTotals() { renderItemsTable(); }

function calculateBalance() {
    const grand = parseFloat($('#grandTotal').text().replace(/,/g,'')) || 0;
    const paid  = parseFloat($('#amountPaid').val()) || 0;
    let cn = 0;
    const cnText = $('#creditNoteRemainingRow .info-value').text();
    if (cnText) { const m = cnText.match(/₹\s*([\d,]+\.?\d*)/); if (m) cn = parseFloat(m[1].replace(/,/g,'')) || 0; }
    const cnUse  = Math.min(cn, Math.max(0, grand - paid));
    $('#balanceAmount').text(Math.max(0, grand - paid - cnUse).toFixed(2));
    $('#creditAdjustmentInfo').remove();
    if (cnUse > 0) {
        $('#amountPaid').closest('.payment-section-container').append(`<div id="creditAdjustmentInfo" style="margin-top:8px;padding:8px 10px;background:#d1fae5;border-radius:5px;font-size:11px;color:#065f46;border:1px solid #6ee7b7;">✓ Credit Note ₹${cnUse.toFixed(2)} will be auto-adjusted on generate<br><span style="font-size:10px;color:#047857;">Customer needs to pay only ₹${Math.max(0,grand-cnUse).toFixed(2)}</span></div>`);
    }
}

function validateForm() {
    if (!$('#partyIdInput').val())  { showAlert('Please select a party', 'error'); return false; }
    if (items.length === 0)         { showAlert('Please add at least one item', 'error'); return false; }
    return true;
}

function showAlert(message, type = 'success') {
    const c = document.getElementById('alertContainer'), d = document.createElement('div');
    d.className = `alert alert-${type}`; d.innerHTML = `<span>${message}</span>`;
    c.appendChild(d); setTimeout(() => d.remove(), 5000);
}

// ===================== DOCUMENT READY =====================

$(document).ready(function() {
    currentInvoiceType = $('#invoiceTypeHidden').val();
    currentGstMode     = $('#gstModeInput').val() || 'exclusive';

    updatePriceColumnHeaders();
    updateAddItemButtonState();
    $('#paymentTermsInput').val('{{ $invoice->payment_terms }}');

    if (currentInvoiceType === 'gst') showGSTInvoiceFields(); else showCashMemoFields();
    if (currentTaxType === 'intra')   showIntraStateTax();    else showInterStateTax();

    // Warehouse badge
    const wId   = '{{ $invoice->warehouse_id }}';
    const wName = '{{ optional(App\Models\Warehouse::find($invoice->warehouse_id))->name ?? "" }}';
    if (wId && wName) { $('#selectedWarehouseBadgeText').text(wName); $('#selectedWarehouseBadge').show(); }

    // Load stock for existing items + credit note balance
    function init() {
        const warehouseId = $('input[name="warehouse_id"]').val();
        const partyId     = $('#partyIdInput').val();

        // Credit note balance
        if (partyId) {
            $.get('{{ route('admin.sales.get-party-details', ':id') }}'.replace(':id', partyId), function(res) {
                if (res.success) {
                    window.selectedParty = res.party;
                    const cn = parseFloat(res.party.credit_notes_remaining || 0);
                    $('#creditNoteRemainingRow').remove();
                    if (cn > 0) {
                        $('.info-column').first().append(`<div class="info-row" id="creditNoteRemainingRow"><span class="info-label">CN Balance:</span><span class="info-value" style="color:#047857;font-weight:600;">₹ ${cn.toFixed(2)} <small style="background:#d1fae5;color:#065f46;padding:1px 5px;border-radius:3px;font-size:9px;margin-left:4px;">WILL AUTO-ADJUST</small></span></div>`);
                    }
                    calculateBalance();
                }
            });
            setTimeout(() => checkCreditLimitStatus(partyId), 500);
        }

        if (!warehouseId || !items.length) { renderItemsTable(); return; }

        // Fetch max_stock for existing items
        $.get('{{ route('admin.sales.get-main-warehouse-products') }}', { warehouse_id: warehouseId }, function(response) {
            if (response.products && response.products.length > 0) {
                items.forEach(function(item, index) {
                    const match = response.products.find(p =>
                        String(p.id) === String(item.product_id) &&
                        (item.variant_id ? String(p.variant_id) === String(item.variant_id) : true)
                    );
                    if (match) items[index].max_stock = parseFloat(match.current_stock);
                });
            }
            renderItemsTable();
        }).fail(function() { renderItemsTable(); });
    }

    init();
    loadParties();

    $('#extraDiscount, #extraDiscountPercent, #extraCharge').on('input', calculateTotals);
    $('#invoiceDate').on('change', updateDueDateFromTerms);
    $('#paymentTermsDays').on('change', updateDueDateFromTerms);
    $('#dueDate').on('change', updatePaymentTermsFromDueDate);
    $('#amountPaid').on('input', validateAndCalculateBalance);

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') { closeSelectPartyModal(); closeCreatePartyModal(); closeAddItemModal(); }
    });

    // ── FORM SUBMIT ──────────────────────────────────────────────────
    $('#salesInvoiceForm').submit(function(e) {
        e.preventDefault();
        if (isSubmitting) { showAlert('Please wait...', 'info'); return; }
        if (!validateForm()) return;
        const grand = parseFloat($('#grandTotal').text()) || 0;
        const paid  = parseFloat($('#amountPaid').val()) || 0;
        if (!validateCreditLimit(grand - paid)) return;

        isSubmitting = true;
        const $btn  = $('.btn-submit-invoice'), orig = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner"></span> Updating...');

        const invoiceId = $(this).data('invoice-id');
        const fd = new FormData();
        $(this).serializeArray().forEach(item => fd.append(item.name, item.value));
        fd.append('items',           JSON.stringify(items));
        fd.append('subtotal',        $('#subtotal').text());
        fd.append('grand_total',     $('#grandTotal').text());
        fd.append('tax_amount',      $('#totalTax').text());
        fd.append('cgst_total',      cgstTotal.toFixed(2));
        fd.append('sgst_total',      sgstTotal.toFixed(2));
        fd.append('igst_total',      igstTotal.toFixed(2));
        fd.append('tax_type',        currentTaxType);
        fd.append('discount_amount', $('#totalDiscount').text());
        fd.append('balance_amount',  $('#balanceAmount').text());
        fd.append('gst_mode',        currentGstMode);
        fd.append('auto_round_off',  $('#autoRoundOff').is(':checked') ? 1 : 0);
        const discType = $('#extraDiscountTypeInput').val();
        fd.append('extra_discount_type', discType);
        fd.append('extra_discount',  discType === 'percent' ? ($('#extraDiscountPercent').val() || 0) : ($('#extraDiscount').val() || 0));
        fd.append('extra_charge',    $('#extraCharge').val() || 0);
        fd.append('charge_name',     $('#chargeName').val() || '');

        $.ajax({
            url: '{{ route('admin.sales.update', ':id') }}'.replace(':id', invoiceId),
            type: 'POST', data: fd, processData: false, contentType: false,
            success: function(r) {
                if (r.success) { showAlert('Invoice updated!', 'success'); setTimeout(() => { window.location.href = '/admin/sales/' + r.invoice_id; }, 1500); }
                else { showAlert('Error: ' + r.message, 'error'); isSubmitting = false; $btn.prop('disabled', false).html(orig); }
            },
            error: function(xhr) {
                showAlert(xhr.responseJSON?.message || 'Failed to update', 'error');
                isSubmitting = false; $btn.prop('disabled', false).html(orig);
            }
        });
    });
});
</script>
@endpush
@endsection
