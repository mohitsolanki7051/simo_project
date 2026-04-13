@extends('layouts.admin')

@section('title', 'Create Sales Invoice - Admin Panel')
@section('header-title', 'Create Sales Invoice')

@section('content')
<div class="products-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Create Sales Invoice</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.sales.index') }}" class="btn-small btn-secondary">
                ← Back to Sales
            </a>
        </div>
    </div>

    <!-- Invoice Form -->
    <div class="invoice-form-wrapper">
        <form id="salesInvoiceForm" class="invoice-form">
            @csrf
            <input type="hidden" name="invoice_number" value="{{ $invoiceNumber }}">
            <input type="hidden" name="warehouse_id" value="{{ $mainWarehouse->_id }}">
            <input type="hidden" name="tax_amount" id="taxAmountInput" value="0">
            <input type="hidden" name="discount_amount" id="discountAmountInput" value="0">
            <input type="hidden" id="extraDiscountTypeInput" name="extra_discount_type" value="amount">
            <input type="hidden" name="salesman_id" id="salesmanIdInput">
            <input type="hidden" name="gst_mode" id="gstModeInput" value="exclusive">

            <div class="form-row">
                <div class="form-col-main">
                    <div class="two-col-row">
                        <div class="col-50">
                            <!-- Party Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-header-left">
                                        <div class="section-icon">👤</div>
                                        <div class="section-title">
                                            <h3>Bill To</h3>
                                            <p>Select party and address details</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-select-customer" id="selectPartyBtn" onclick="openSelectPartyModal()">
                                        <span class="btn-icon">👤</span>
                                        Select Party
                                    </button>
                                </div>

                                <div class="section-body">
                                    <div id="selectedPartyDetails" class="selected-customer-details" style="display: none;">
                                        <div class="customer-header">
                                            <h4 id="partyNameDisplay">Party Name</h4>
                                            <span id="partyTypeBadge" class="party-type-badge"></span>
                                            <button type="button" class="btn-change-customer" onclick="openSelectPartyModal()">
                                                Change
                                            </button>
                                        </div>

                                        <div class="customer-info-grid">
                                            <div class="info-column">
                                                <div class="info-row">
                                                    <span class="info-label">Phone:</span>
                                                    <span id="partyPhone" class="info-value">-</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="info-label">Email:</span>
                                                    <span id="partyEmail" class="info-value">-</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="info-label">GST:</span>
                                                    <span id="partyGst" class="info-value">-</span>
                                                </div>
                                            </div>
                                            <div class="info-column">
                                                <div class="info-row">
                                                    <span class="info-label">Opening Bal:</span>
                                                    <span id="partyOpeningBalance" class="info-value">-</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="info-label">Credit Limit:</span>
                                                    <span id="partyCreditLimit" class="info-value">-</span>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="party_id" id="partyIdInput">
                                        <input type="hidden" name="party_type" id="partyTypeInput">

                                        <div class="address-section">
                                            <div class="address-header">
                                                <h5>Address Details</h5>
                                            </div>

                                            <div class="address-grid">
                                                <div class="address-card">
                                                    <div class="address-card-header">
                                                        <span class="address-type">Billing Address</span>
                                                    </div>
                                                    <div class="address-content">
                                                        <p id="billingAddressText">Select a party to view address</p>
                                                    </div>
                                                    <input type="hidden" name="billing_address" id="billingAddressInput">
                                                </div>

                                                <div class="address-card">
                                                    <div class="address-card-header">
                                                        <span class="address-type">Shipping Address</span>
                                                    </div>
                                                    <div class="address-content">
                                                        <p id="shippingAddressText">Select a party to view address</p>
                                                    </div>
                                                    <input type="hidden" name="shipping_address" id="shippingAddressInput">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-50">
                            <!-- Invoice Details Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-header-left">
                                        <div class="section-icon">📄</div>
                                        <div class="section-title">
                                            <h3>Invoice Details</h3>
                                            <p>Invoice date, terms and other details</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label">Sales Invoice No.</label>
                                            <input type="text" id="invoiceNumberDisplay" class="form-control" value="{{ $invoiceNumber }}" readonly tabindex="-1">
                                        </div>

                                        <div class="form-group col-6">
                                            <label class="form-label required">Invoice Date</label>
                                            <input type="date" name="invoice_date" id="invoiceDate" class="form-control" value="{{ date('Y-m-d') }}" required onchange="updateDueDateFromTerms()">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label">Payment Terms</label>
                                            <div class="payment-terms-days">
                                                <input type="number" name="payment_terms_days" id="paymentTermsDays" class="form-control" placeholder="0" min="0" step="1" onchange="updateDueDateFromTerms()">
                                                <span>days</span>
                                                <input type="hidden" name="payment_terms" id="paymentTermsInput">
                                            </div>
                                        </div>

                                        <div class="form-group col-6">
                                            <label class="form-label">Due Date</label>
                                            <input type="date" name="due_date" id="dueDate" class="form-control" onchange="updatePaymentTermsFromDueDate()">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label">PO Number</label>
                                            <input type="text" name="po_number" class="form-control" placeholder="Optional">
                                        </div>
                                        <div class="form-group col-6">
                                            <label class="form-label">Assigned Salesman</label>
                                            <div class="salesman-display" id="salesmanNameDisplay">—</div>
                                        </div>
                                    </div>

                                    <!-- Invoice Type -->
                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label required">Invoice Type</label>
                                            <select name="invoice_type" id="invoiceType" class="form-control" required onchange="handleInvoiceTypeChange()">
                                                <option value="">Select Invoice Type</option>
                                                <option value="gst" selected>GST Invoice</option>
                                                <option value="cash">Cash Memo</option>
                                            </select>
                                            <small id="invoiceTypeHelp" class="form-text text-muted" style="font-size:10px;margin-top:3px;">GST Invoice includes HSN code and tax calculations</small>
                                        </div>
                                        <!-- GST Mode toggle (only visible for GST Invoice) -->
                                        <div class="form-group col-6" id="gstModeGroup">
                                            <label class="form-label">GST Mode</label>
                                            <div class="gst-mode-toggle">
                                                <button type="button" class="gst-mode-btn active" data-mode="exclusive" onclick="setGstMode('exclusive')">
                                                    <span class="gst-mode-icon">📤</span>
                                                    <span class="gst-mode-label">Exclusive</span>
                                                    <span class="gst-mode-sub">GST added on top</span>
                                                </button>
                                                <button type="button" class="gst-mode-btn" data-mode="inclusive" onclick="setGstMode('inclusive')">
                                                    <span class="gst-mode-icon">📥</span>
                                                    <span class="gst-mode-label">Inclusive</span>
                                                    <span class="gst-mode-sub">GST within price</span>
                                                </button>
                                            </div>
                                        </div>
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
                                <div class="section-title">
                                    <h3>Items</h3>
                                    <p>Add products to invoice</p>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span id="selectedWarehouseBadge" style="display: none; font-size: 10px; font-weight: 600; color: #fa8725; background: #fff3e6; border: 1px solid #fa8725; padding: 4px 10px; border-radius: 12px;">
                                    🏭 <span id="selectedWarehouseBadgeText"></span>
                                </span>
                                <button type="button" class="btn-add-item" id="addItemBtn" onclick="openAddItemModal()">
                                    + Add Item
                                </button>
                            </div>
                        </div>

                        <div class="section-body">
                            <div class="items-table-container" id="itemsTableContainer">
                                <table class="items-table" id="mainItemsTable">
                                    <thead>
                                        <tr id="itemsHeaderRow">
                                            <th class="th-item">Item</th>
                                            <th class="th-hsn" id="hsnHeader">HSN/SAC</th>
                                            <th class="th-unit">Unit</th>
                                            <th class="th-qtys">Qty</th>
                                            <th class="th-warranty">Warranty</th>
                                            <th class="th-mrp">MRP (₹)</th>
                                            <th class="th-discount">Disc %</th>
                                            <th class="th-sale-price" id="priceColumnHeader">Sale Price (₹)</th>
                                            <th class="th-tax" id="taxHeader">Tax %</th>
                                            <th class="th-amount">Final Amt (₹)</th>
                                            <th class="th-action">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                    </tbody>
                                    <tfoot id="itemsTableFooter" style="display:none; background-color: #f0f0f0; font-weight: 600; border-top: 2px solid #333;">
                                        <tr>
                                            <td colspan="5" style="text-align: right; padding: 10px; font-size: 12px;"><strong>SUBTOTAL:</strong></td>
                                            <td style="padding: 10px; text-align: center; font-size: 12px;" id="footerMRP">₹ 0.00</td>
                                            <td style="padding: 10px; text-align: center; font-size: 12px;" id="footerDiscount">₹ 0.00</td>
                                            <td style="padding: 10px; text-align: center; font-size: 12px;" id="footerSalePrice">₹ 0.00</td>
                                            <td style="padding: 10px; text-align: center; font-size: 12px;" id="footerTax">₹ 0.00</td>
                                            <td style="padding: 10px; text-align: center; font-size: 12px;" id="footerFinalAmount">₹ 0.00</td>
                                            <td style="padding: 10px;"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Summary and Payment Sections -->
                    <div class="form-col-sidebar">
                        <div class="invoice-bottom-layout">
                            <div class="invoice-row">
                                <!-- LEFT: Invoice Summary -->
                                <div class="invoice-col">
                                    <div class="summary-section">
                                        <div class="summary-header">
                                            <div class="summary-icon">💰</div>
                                            <h3>Invoice Summary</h3>
                                        </div>

                                        <div class="summary-body">
                                            <div class="summary-row">
                                                <span class="summary-label">Total MRP</span>
                                                <span class="summary-value">₹ <span id="totalMRP">0.00</span></span>
                                            </div>

                                            <div class="summary-row">
                                                <span class="summary-label">Total Discount</span>
                                                <span class="summary-value">- ₹ <span id="totalDiscount">0.00</span></span>
                                            </div>

                                            <div class="summary-divider"></div>

                                            <div class="summary-row">
                                                <span class="summary-label" style="font-weight: 600;">Subtotal <small id="subtotalNote" style="color:#fa8725;font-weight:400;"></small></span>
                                                <span class="summary-value">₹ <span id="subtotal">0.00</span></span>
                                            </div>

                                            <!-- TAX BREAKUP - Only for GST Invoice -->
                                            <div id="taxBreakupContainer" style="display: none;">
                                                <div id="intraStateTax" style="display: none;">
                                                    <div class="summary-row">
                                                        <span class="summary-label">CGST</span>
                                                        <span class="summary-value">+ ₹ <span id="cgstTotal">0.00</span></span>
                                                    </div>
                                                    <div class="summary-row">
                                                        <span class="summary-label">SGST</span>
                                                        <span class="summary-value">+ ₹ <span id="sgstTotal">0.00</span></span>
                                                    </div>
                                                </div>
                                                <div id="interStateTax" style="display: none;">
                                                    <div class="summary-row">
                                                        <span class="summary-label">IGST</span>
                                                        <span class="summary-value">+ ₹ <span id="igstTotal">0.00</span></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="summary-row" id="totalTaxRow" style="border-top: 1px dashed #ddd; padding-top: 5px; display: none;">
                                                <span class="summary-label">Total Tax</span>
                                                <span class="summary-value">+ ₹ <span id="totalTax">0.00</span></span>
                                            </div>

                                            <div class="summary-divider"></div>

                                            <div class="summary-row">
                                                <span class="summary-label">
                                                    <a href="javascript:void(0)" id="addDiscountLink" onclick="toggleExtraDiscount()">+ Add Discount</a>
                                                </span>
                                            </div>
                                            <div id="extraDiscountRow" style="display:none;" class="summary-row">
                                                <input type="number" id="extraDiscount" placeholder="Enter discount amount" step="0.01" oninput="calculateTotals()" class="summary-input">
                                            </div>

                                            <div class="summary-row">
                                                <span class="summary-label">
                                                    <a href="javascript:void(0)" id="addDiscountPercentLink" onclick="toggleExtraDiscountPercent()">+ Add Discount %</a>
                                                </span>
                                            </div>
                                            <div id="extraDiscountPercentRow" style="display:none;" class="summary-row">
                                                <input type="number" id="extraDiscountPercent" placeholder="Enter discount %" step="0.01" oninput="calculateTotals()" class="summary-input">
                                            </div>

                                            <div class="summary-row">
                                                <span class="summary-label">
                                                    <a href="javascript:void(0)" id="addChargeLink" onclick="toggleExtraCharge()">+ Add Another Charge</a>
                                                </span>
                                            </div>
                                            <div id="extraChargeRow" style="display:none;" class="summary-row">
                                                <input type="text" placeholder="Charge Name" id="chargeName" class="summary-input">
                                                <input type="number" placeholder="₹" id="extraCharge" oninput="calculateTotals()" class="summary-input-small">
                                            </div>

                                            <div class="summary-row">
                                                <label>
                                                    <input type="checkbox" id="autoRoundOff" onchange="calculateTotals()">
                                                    Auto Round Off
                                                </label>
                                            </div>

                                            <div class="summary-row total-row">
                                                <span class="summary-label">Grand Total</span>
                                                <span class="summary-value">₹ <span id="grandTotal">0.00</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- RIGHT: Payment Details -->
                                <div class="invoice-col">
                                    <div class="payment-section-container">
                                        <h4>Payment Details</h4>

                                        <div class="form-group">
                                            <label class="form-label">Payment Method</label>
                                            <div class="select-wrapper">
                                                <select name="payment_method" class="form-control">
                                                    <option value="cash">Cash</option>
                                                    <option value="bank_transfer">Bank Transfer</option>
                                                    <option value="cheque">Cheque</option>
                                                    <option value="card">Card</option>
                                                    <option value="upi">UPI</option>
                                                    <option value="credit">Credit</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Amount Paid</label>
                                            <input type="number" name="amount_paid" id="amountPaid" class="form-control" step="0.01" min="0" oninput="validateAndCalculateBalance()" onblur="clampAmountPaid()">
                                        </div>
                                        <div class="form-group">
                                            <button type="button" onclick="markFullyPaid()" class="btn-mark-paid" style="width:auto; padding:4px 8px; font-size:10px;">
                                                Mark as Fully Paid
                                            </button>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Balance Amount</label>
                                            <div class="balance-amount">
                                                ₹ <span id="balanceAmount">0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-row" style="margin-top: 10px;">
                        <div class="form-group col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes or remarks..."></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div style="display:flex; justify-content:flex-end;">
                        <button type="submit" class="btn-submit-invoice">
                            <span class="btn-icon">💾</span>
                            Save Sales Invoice
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Select Party Modal -->
<div class="modal" id="selectPartyModal">
    <div class="modal-overlay" onclick="closeSelectPartyModal()"></div>
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <div class="modal-icon">👥</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Select Party</h4>
                <div class="modal-subtitle">Choose customer, dealer or distributor</div>
            </div>
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
                <div class="search-container">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="searchParty" class="search-input" placeholder="Search by name, phone or email...">
                    </div>
                </div>
                <button type="button" class="btn-create-new-party" onclick="openCreatePartyModal()">
                    <span class="btn-icon">+</span>
                    Create New Party
                </button>
            </div>

            <div class="parties-table-container">
                <table class="parties-table">
                    <thead>
                        <tr>
                            <th class="th-name">Name</th>
                            <th class="th-type">Type</th>
                            <th class="th-phone">Phone</th>
                            <th class="th-email">Email</th>
                            <th class="th-status">Status</th>
                            <th class="th-action">Action</th>
                        </tr>
                    </thead>
                    <tbody id="partiesTableBody">
                    </tbody>
                </table>
                <div id="partiesLoading" class="loading-state">
                    <div class="loading-spinner"></div>
                    <p>Loading parties...</p>
                </div>
                <div id="noParties" class="no-data-state" style="display: none;">
                    <div class="no-data-icon">👤</div>
                    <p>No parties found</p>
                    <button type="button" class="btn-create-first-party" onclick="openCreatePartyModal()">+ Create New Party</button>
                </div>
            </div>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeSelectPartyModal()">Cancel</button>
        </div>
    </div>
</div>

<!-- Create Party Modal -->
<div class="modal" id="createPartyModal">
    <div class="modal-overlay" onclick="closeCreatePartyModal()"></div>
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <div class="modal-icon">👤</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Create New Party</h4>
                <div class="modal-subtitle">Enter party details and address</div>
            </div>
            <button type="button" class="modal-close" onclick="closeCreatePartyModal()">×</button>
        </div>

        <form id="createPartyForm">
            @csrf
            <div class="modal-body">
                <div class="form-section-small">
                    <h5>Basic Information</h5>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label required">Party Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group col-6">
                            <label class="form-label required">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" required maxlength="10">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="form-group col-6">
                            <label class="form-label required">Party Type</label>
                            <div class="select-wrapper">
                                <select name="party_type" class="form-control" required>
                                    <option value="customer">Customer</option>
                                    <option value="dealer">Dealer</option>
                                    <option value="distributor">Distributor</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label">Opening Balance</label>
                            <input type="number" name="opening_balance" class="form-control" step="0.01" min="0" value="0">
                        </div>
                        <div class="form-group col-6">
                            <label class="form-label">Credit Limit</label>
                            <input type="number" name="credit_limit" class="form-control" step="0.01" min="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label">Assign Salesman</label>
                            <div class="select-wrapper">
                                <select name="salesman_id" class="form-control">
                                    <option value="">None (No Salesman)</option>
                                    @php
                                        $salesmen = \App\Models\Salesman::where('status', 'active')->orderBy('name')->get();
                                    @endphp
                                    @foreach($salesmen as $salesman)
                                        <option value="{{ $salesman->_id }}">{{ $salesman->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section-small">
                    <h5>Tax Information</h5>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label">GST Number</label>
                            <input type="text" name="gst_number" class="form-control" maxlength="15" oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="form-group col-6">
                            <label class="form-label">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control" maxlength="10" oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                </div>

                <div class="form-section-small">
                    <div class="section-header-small">
                        <h5>Billing Address</h5>
                        <label class="form-check">
                            <input type="checkbox" id="sameBillingShipping" class="form-check-input">
                            <span class="form-check-label">Same as Shipping Address</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" name="billing_address" class="form-control" placeholder="House no, street, area">
                    </div>

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
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" name="shipping_address" class="form-control" placeholder="House no, street, area">
                    </div>
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
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
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
    <div class="modal-overlay" onclick="closeAddItemModal()"></div>
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <div class="modal-icon">🛒</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Add Items</h4>
                <div class="modal-subtitle">Select products from warehouse</div>
            </div>
            <button type="button" class="modal-close" onclick="closeAddItemModal()">×</button>
        </div>
        <div class="modal-body">
            <!-- Warehouse Selector -->
            <div style="text-align: center; padding: 20px 0 15px 0;" id="warehouseSelectSection">
                <div style="font-size: 13px; font-weight: 600; color: #333; margin-bottom: 12px;">🏭 Select Warehouse to Load Products</div>
                <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <div class="select-wrapper" style="width: 280px;">
                        <select id="itemModalWarehouse" class="form-control" onchange="onWarehouseChange()" style="font-size: 12px; padding: 8px 12px;">
                            <option value="">-- Select Warehouse --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->_id }}">{{ $wh->name }}{{ $wh->is_main ? ' (Main)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Search + Selected Warehouse Info -->
            <div id="productSearchSection" style="display: none; margin-bottom: 12px;">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div class="search-container" style="flex: 1; margin-bottom: 0;">
                        <div class="search-box">
                            <span class="search-icon">🔍</span>
                            <input type="text" id="searchProduct" class="search-input" placeholder="Search items by name, SKU or barcode...">
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                        <span id="selectedWarehouseName" style="font-size: 11px; font-weight: 600; color: #fa8725; background: #fff3e6; padding: 5px 10px; border-radius: 4px; border: 1px solid #fa8725;">🏭 —</span>
                        <button type="button" onclick="resetWarehouseSelection()" style="font-size: 10px; padding: 5px 8px; background: #eee; border: 1px solid #ccc; border-radius: 3px; cursor: pointer; color: #555;">↩ Change</button>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="products-table-container" id="productsTableWrapper" style="display: none;">
                <table class="products-table">
                    <thead id="productsTableHeader">
                        <tr>
                            <th class="th-name">Item Name</th>
                            <th class="th-code">Item Code</th>
                            <th class="th-mrp">MRP (₹)</th>
                            <th class="th-price" id="modalPriceColumnHeader">Sale Price (₹)</th>
                            <th class="th-stock">Current Stock</th>
                            <th class="th-qty">Quantity</th>
                            <th class="th-action">Select</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody"></tbody>
                </table>
                <div id="productsLoading" class="loading-state" style="display: none;">
                    <div class="loading-spinner"></div>
                    <p>Loading products...</p>
                </div>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-modal btn-primary" onclick="addSelectedProducts()">Done</button>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ── GST MODE TOGGLE ── */
.gst-mode-toggle {
    display: flex;
    gap: 6px;
}

.gst-mode-btn {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1px;
    padding: 6px 8px;
    background: #f8f9fa;
    border: 1.5px solid #dee2e6;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.18s ease;
    font-size: 10px;
    line-height: 1.3;
}

.gst-mode-btn:hover {
    border-color: #fa8725;
    background: #fff8f2;
}

.gst-mode-btn.active {
    background: #fff3e6;
    border-color: #fa8725;
}

.gst-mode-btn.active .gst-mode-label {
    color: #c05e00;
    font-weight: 700;
}

.gst-mode-icon  { font-size: 13px; }
.gst-mode-label { font-weight: 600; font-size: 10px; color: #333; }
.gst-mode-sub   { font-size: 9px; color: #888; }

/* Inclusive row highlight in items table */
.items-table tr.row-inclusive {
    background: #fffbf5 !important;
}

.incl-badge {
    display: inline-block;
    font-size: 8px;
    background: #fff3e6;
    color: #c05e00;
    border: 1px solid #fa8725;
    border-radius: 3px;
    padding: 1px 4px;
    margin-left: 4px;
    vertical-align: middle;
    font-weight: 600;
}

/* ── CREDIT LIMIT BADGES ── */
.credit-limit-badge { display:inline-block; padding:2px 6px; border-radius:10px; font-size:8px; font-weight:600; margin-left:5px; vertical-align:middle; }
.credit-limit-badge.warning { background:#fff3cd; color:#856404; border:1px solid #ffeeba; }
.credit-limit-badge.danger  { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.credit-limit-badge.info    { background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; }
.available-credit { display:block; font-size:9px; color:#28a745; margin-top:2px; font-weight:normal; }

/* ── DISABLED LINKS ── */
.disabled-link { pointer-events:none !important; opacity:.5 !important; cursor:not-allowed !important; background-color:#e0e0e0 !important; color:#999 !important; border-color:#ccc !important; }

/* ── GENERAL ── */
.products-container { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; font-size:11px; line-height:1.3; color:#333; }
#alertContainer { position:fixed; top:15px; right:15px; z-index:9999; }
.alert { padding:10px 14px; margin-bottom:8px; border-radius:4px; font-size:10px; font-weight:500; animation:slideInRight .3s ease; box-shadow:0 2px 8px rgba(0,0,0,.1); }
.alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert-error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.alert-info    { background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; }
@keyframes slideInRight { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }

.page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; border-bottom:1px solid #ddd; }
.header-left .page-title { font-size:15px; font-weight:600; color:#333; margin:0 0 3px; }
.btn-small { padding:5px 10px; background:white; color:#fa8128; border:1px solid #dee2e6; border-radius:4px; font-size:14px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:3px; transition:all .2s; text-decoration:none; }
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
.select-wrapper { position:relative; }
.form-row .form-group { margin-bottom:10px; }
.form-row .col-6 { width:50%; }
.form-row .col-12 { width:100%; }

.btn-select-customer { width:180px; padding:8px 12px; background:#fa8725de; color:white; border:none; border-radius:3px; font-size:11px; font-weight:500; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:center; gap:5px; }
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

/* Items Table */
.items-table-container { overflow-x:auto; width:100%; margin-top:10px; border-radius:4px; border:1px solid #ddd; }
.items-table-container.gst-mode .items-table { min-width:980px; }
.items-table-container.cash-mode .items-table { min-width:560px; }
.items-table { width:100%; border-collapse:collapse; font-size:10px; }
.items-table th { background:#f8f9fa; padding:10px 8px; text-align:center; font-weight:600; color:#333; border-bottom:2px solid #ddd; white-space:nowrap; font-size:11px; }
.items-table td { padding:8px; border-bottom:1px solid #eee; vertical-align:middle; text-align:center; font-size:10px; }
.items-table td.item-name { text-align:left; font-weight:500; }
.items-table tr:hover { background:#f9f9f9; }
.items-table input[type="number"] { width:100%; padding:4px 6px; border:1px solid #ccc; border-radius:2px; font-size:10px; text-align:center; }
.th-item { min-width:140px; text-align:left !important; }
.th-hsn,.th-unit { width:75px; }
.th-qtys,.th-tax { width:65px; }
.th-warranty { width:130px; }
.th-sale-price { width:95px; }
.th-mrp { width:85px; }
.th-discount { width:75px; }
.th-amount { width:90px; }
.th-action { width:60px; }
.btn-delete { width:28px; height:28px; border:none; background:#fee; color:#c33; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; margin:0 auto; }
.btn-delete:hover { background:#fcc; color:#a00; }
.empty-row td { padding:30px 20px; text-align:center; }
.empty-items { display:flex; flex-direction:column; align-items:center; gap:8px; }
.empty-icon { font-size:24px; opacity:.3; margin-bottom:5px; }
.empty-items p { font-size:10px; color:#666; margin:0; }
.btn-add-first-item { padding:6px 12px; background:#555; color:white; border:none; border-radius:3px; font-size:10px; cursor:pointer; }
.warranty-wrapper { display:flex; gap:4px; align-items:center; }
.warranty-input { width:50px; padding:3px; font-size:10px; }
.warranty-select { font-size:10px; padding:3px; }

/* Summary */
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
.summary-label a { color:#fa8725; text-decoration:none; font-weight:500; font-size:10px; padding:4px 8px; background:#fff3e6; border-radius:3px; border:1px dashed #fa8725; transition:all .2s; display:inline-block; }
.summary-label a:hover { background:#fa8725; color:white; }
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

/* Modal */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:1000; align-items:center; justify-content:center; }
.modal-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.4); }
.modal-content { position:relative; background:white; border-radius:6px; width:90%; max-width:800px; max-height:90vh; overflow-y:auto; animation:modalFadeIn .2s ease; box-shadow:0 4px 20px rgba(0,0,0,.15); }
.modal-lg,.modal-xl { max-width:800px; }
@keyframes modalFadeIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.modal-header { display:flex; align-items:center; gap:10px; padding:15px 20px; border-bottom:1px solid #ddd; background:#f8f9fa; border-radius:6px 6px 0 0; }
.modal-title-section { flex:1; }
.modal-title { font-size:14px; font-weight:600; color:#333; margin:0; }
.modal-subtitle { font-size:10px; color:#666; margin-top:2px; }
.modal-close { background:none; border:none; font-size:18px; color:#666; cursor:pointer; padding:4px; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:4px; }
.modal-close:hover { background:#eee; }
.modal-icon { width:32px; height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:14px; color:white; flex-shrink:0; background:#555; }
.modal-body { padding:20px; }
.modal-actions { display:flex; justify-content:flex-end; gap:8px; padding:15px 20px; border-top:1px solid #ddd; background:#fafafa; border-radius:0 0 6px 6px; }
.btn-modal { padding:7px 16px; border:none; border-radius:3px; font-size:10px; font-weight:500; cursor:pointer; min-width:70px; }
.btn-cancel { background:#f8f9fa; color:#333; border:1px solid #ccc; }
.btn-cancel:hover { background:#e9ecef; }
.btn-primary { background:#555; color:white; border:1px solid #555; }
.btn-primary:hover { background:#444; }

/* Party tabs */
.party-type-tabs { display:flex; gap:8px; margin-bottom:15px; padding:5px; background:#f8f9fa; border-radius:6px; border:1px solid #dee2e6; }
.party-type-tab { flex:1; padding:8px 12px; background:transparent; border:none; border-radius:4px; font-size:11px; font-weight:500; color:#6c757d; cursor:pointer; text-align:center; }
.party-type-tab.active { background:#007bff; color:white; }
.party-modal-header { display:flex; justify-content:space-between; align-items:center; gap:15px; margin-bottom:15px; }
.search-container { flex:1; }
.search-box { position:relative; }
.search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:11px; color:#666; }
.search-input { width:100%; padding:7px 10px 7px 30px; border:1px solid #ccc; border-radius:3px; font-size:10px; }
.btn-create-new-party { padding:7px 14px; background:#28a745; color:white; border:none; border-radius:3px; font-size:10px; font-weight:500; cursor:pointer; display:flex; align-items:center; gap:5px; white-space:nowrap; }
.parties-table-container { max-height:400px; overflow-y:auto; border:1px solid #ddd; border-radius:4px; }
.parties-table { width:100%; border-collapse:collapse; font-size:10px; }
.parties-table th { background:#f8f9fa; padding:8px 10px; text-align:left; font-weight:600; color:#333; border-bottom:1px solid #ddd; position:sticky; top:0; z-index:1; }
.parties-table td { padding:8px 10px; border-bottom:1px solid #eee; vertical-align:middle; }
.parties-table tr:hover { background:#f9f9f9; }
.status-badge { display:inline-block; padding:2px 6px; border-radius:10px; font-size:9px; font-weight:500; }
.status-active { background:#d4edda; color:#155724; }
.status-inactive { background:#f8d7da; color:#721c24; }
.btn-create-first-party,.btn-select-party-row { padding:4px 10px; color:white; border:none; border-radius:3px; font-size:9px; cursor:pointer; }
.btn-create-first-party { background:#28a745; font-size:10px; padding:6px 12px; }
.btn-select-party-row { background:none; border:none; font-size:16px; color:#28a745; cursor:pointer; }
.no-data-state { padding:30px 20px; text-align:center; }
.no-data-icon { font-size:24px; opacity:.3; margin-bottom:8px; }
.no-data-state p { font-size:10px; color:#666; margin:0 0 12px; }
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
.th-name { width:180px; }
.th-phone { width:100px; }
.th-email { width:150px; }
.th-type,.th-status { width:80px; }
.th-code { width:90px; }
.th-stock { width:90px; }
.th-qty { width:70px; }
.form-section-small { margin-bottom:15px; padding-bottom:12px; border-bottom:1px solid #eee; }
.form-section-small:last-child { border-bottom:none; margin-bottom:0; padding-bottom:0; }
.form-section-small h5 { font-size:11px; font-weight:600; color:#333; margin:0 0 10px; }
.section-header-small { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.form-check { display:flex; align-items:center; gap:5px; margin-top:6px; }
.form-check-input { width:13px; height:13px; cursor:pointer; }
.form-check-label { font-size:10px; color:#555; cursor:pointer; }
.spinner { display:inline-block; width:12px; height:12px; border:2px solid rgba(255,255,255,.3); border-radius:50%; border-top-color:#fff; animation:spin .8s linear infinite; margin-right:5px; }
#warehouseSelectSection { background:#f8f9fa; border:2px dashed #ddd; border-radius:8px; padding:30px 20px; margin-bottom:5px; }

@media (max-width:768px) {
    .two-col-row,.invoice-row { flex-direction:column; }
    .col-50,.form-row .col-6 { width:100%; }
    .address-grid,.customer-info-grid { grid-template-columns:1fr; }
}
</style>
@endpush

@push('scripts')
<script>
// ===================== GLOBALS =====================
let items            = [];
let currentPartyType = 'all';
let cgstTotal        = 0;
let sgstTotal        = 0;
let igstTotal        = 0;
let currentTaxType   = 'intra';
let isSubmitting     = false;
let currentInvoiceType = 'gst';
let currentGstMode   = 'exclusive'; // 'exclusive' | 'inclusive'

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

    // Update subtotal note
    if (mode === 'inclusive') {
        $('#subtotalNote').text('(ex-GST extracted)');
    } else {
        $('#subtotalNote').text('');
    }

    updatePriceColumnHeaders();
    renderItemsTable();
}

// ===================== INVOICE TYPE =====================

function handleInvoiceTypeChange() {
    const invoiceType = $('#invoiceType').val();
    currentInvoiceType = invoiceType;

    $.get('{{ route('admin.sales.get-next-invoice-number') }}', { invoice_type: invoiceType }, function(res) {
        if (res.invoice_number) {
            $('input[name="invoice_number"]').val(res.invoice_number);
            $('#invoiceNumberDisplay').val(res.invoice_number);
        }
    });

    const helpElement = $('#invoiceTypeHelp');
    if (invoiceType === 'gst') {
        helpElement.text('GST Invoice includes HSN code and tax calculations');
        showGSTInvoiceFields();
        $('#gstModeGroup').show();
    } else if (invoiceType === 'cash') {
        helpElement.text('Cash Memo - No HSN code or GST tax calculations');
        showCashMemoFields();
        $('#gstModeGroup').hide();
    } else {
        helpElement.text('Select invoice type to continue');
    }

    items = [];
    renderItemsTable();
    updateAddItemButtonState();
}

function showGSTInvoiceFields() {
    $('.items-table-container').removeClass('cash-mode').addClass('gst-mode');
    $('#hsnHeader').show();
    $('#taxHeader').show();
    $('.th-hsn, .th-tax').show();
    $('#taxBreakupContainer').show();
    $('#totalTaxRow').show();
}

function showCashMemoFields() {
    $('.items-table-container').removeClass('gst-mode').addClass('cash-mode');
    $('#hsnHeader').hide();
    $('#taxHeader').hide();
    $('.th-hsn, .th-tax').hide();
    $('#taxBreakupContainer').hide();
    $('#totalTaxRow').hide();
    cgstTotal = 0; sgstTotal = 0; igstTotal = 0;
}

// ===================== PARTY MODAL =====================

function openSelectPartyModal() {
    $('#selectPartyModal').css('display', 'flex');
    loadParties();
}

function closeSelectPartyModal() {
    $('#selectPartyModal').hide();
    $('#searchParty').val('');
}

function openCreatePartyModal() {
    closeSelectPartyModal();
    $('#createPartyModal').css('display', 'flex');
}

function closeCreatePartyModal() {
    $('#createPartyModal').hide();
    $('#createPartyForm')[0].reset();
    $('#sameBillingShipping').prop('checked', false);
}

function loadParties(search = '') {
    const tbody = $('#partiesTableBody');
    const loading = $('#partiesLoading');
    const noData = $('#noParties');
    tbody.empty(); loading.show(); noData.hide();

    $.get('{{ route('admin.sales.parties.list') }}', {
        search: search,
        party_type: currentPartyType !== 'all' ? currentPartyType : null
    }, function(response) {
        loading.hide();
        if (response.parties.length === 0) { noData.show(); return; }
        response.parties.forEach(party => {
            const statusClass = party.status === 'active' ? 'status-active' : 'status-inactive';
            tbody.append(`
                <tr>
                    <td class="party-name">${party.name}</td>
                    <td>${party.party_type_text}</td>
                    <td class="party-phone">${party.phone || '-'}</td>
                    <td class="party-email">${party.email || '-'}</td>
                    <td><span class="status-badge ${statusClass}">${party.status}</span></td>
                    <td>
                        <button type="button" class="btn-select-party-row" onclick="selectParty('${party.id}')" title="Select">✓</button>
                    </td>
                </tr>`);
        });
    }).fail(function() { loading.hide(); showAlert('Failed to load parties', 'error'); });
}

// ===================== TAX BREAKUP =====================

function showIntraStateTax() {
    $('#intraStateTax').show(); $('#interStateTax').hide();
    currentTaxType = 'intra';
    calculateTaxBreakup();
}

function showInterStateTax() {
    $('#intraStateTax').hide(); $('#interStateTax').show();
    currentTaxType = 'inter';
    calculateTaxBreakup();
}

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
            // Inclusive: reverse-extract GST
            // gross = qty × price (price is inclusive)
            // base = gross × 100 / (100 + taxPercent)
            // tax  = gross - base
            const grossTotal = item.quantity * item.price;
            const baseTotal  = grossTotal * 100 / (100 + taxPercent);
            itemTax          = grossTotal - baseTotal;
        } else {
            // Exclusive: normal
            const salePriceTotal = item.quantity * item.price;
            itemTax = (salePriceTotal * taxPercent) / 100;
        }

        if (currentTaxType === 'intra') {
            cgstTotal += itemTax / 2;
            sgstTotal += itemTax / 2;
        } else {
            igstTotal += itemTax;
        }
    });

    $('#cgstTotal').text(cgstTotal.toFixed(2));
    $('#sgstTotal').text(sgstTotal.toFixed(2));
    $('#igstTotal').text(igstTotal.toFixed(2));
    $('#totalTax').text((cgstTotal + sgstTotal + igstTotal).toFixed(2));
}

// ===================== PRICE COLUMN HEADERS =====================

function getCurrentPartyType() { return $('#partyTypeInput').val() || null; }

function updateAddItemButtonState() {
    const disabled = !getCurrentPartyType() || !$('#invoiceType').val();
    $('#addItemBtn').prop('disabled', disabled).css({ opacity: disabled ? '0.5' : '1', cursor: disabled ? 'not-allowed' : 'pointer' });
}

function getPriceColumnHeader() {
    const partyType  = getCurrentPartyType();
    const modeLabel  = (currentInvoiceType === 'gst' && currentGstMode === 'inclusive') ? ' (Incl. GST)' : ' (Excl. GST)';
    if (partyType === 'dealer')      return 'Dealer Price ₹' + modeLabel;
    if (partyType === 'distributor') return 'Distributor Price ₹' + modeLabel;
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

function calculateDiscountPercentage(mrp, price) {
    if (mrp <= 0 || price <= 0) return 0;
    return ((mrp - price) / mrp * 100).toFixed(2);
}

// ===================== ADD ITEM MODAL =====================

function openAddItemModal() {
    if (!getCurrentPartyType()) { showAlert('Please select a party first', 'error'); return; }
    if (!$('#invoiceType').val()) { showAlert('Please select invoice type first', 'error'); return; }
    resetWarehouseSelection();
    $('#addItemModal').css('display', 'flex');
}

function closeAddItemModal() {
    $('#addItemModal').hide();
    $('#searchProduct').val('');
}

function loadProducts(search = '', warehouseId = null) {
    const tbody    = $('#productsTableBody');
    const loading  = $('#productsLoading');
    const partyType = getCurrentPartyType();

    tbody.empty(); loading.show();
    const selectedWarehouse = warehouseId || $('#itemModalWarehouse').val() || '{{ $mainWarehouse->_id }}';

    $.get('{{ route('admin.sales.get-main-warehouse-products') }}', {
        search: search,
        warehouse_id: selectedWarehouse
    }, function(response) {
        loading.hide();
        if (response.products.length === 0) {
            tbody.html('<tr><td colspan="7" style="padding:40px;text-align:center;"><div style="font-size:32px;opacity:.3;">📦</div><p style="font-size:11px;color:#6b7280;">No products found</p></td></tr>');
            return;
        }

        response.products.forEach(product => {
            const price   = getProductPrice(product, partyType);
            const mrp     = parseFloat(product.mrp_price || 0);
            const discPct = calculateDiscountPercentage(mrp, price);

            tbody.append(`<tr>
                <td class="product-name">${product.name}</td>
                <td class="product-code">${product.sku || '-'}</td>
                <td class="product-mrp">₹ ${mrp.toFixed(2)}</td>
                <td class="product-price">₹ ${price.toFixed(2)}${discPct > 0 ? `<br><small style="color:#28a745;">(${discPct}% off)</small>` : ''}</td>
                <td class="product-stock">${product.current_stock} ${product.unit}</td>
                <td class="product-qty">
                    <input type="number" class="qty-input" min="1" max="${product.current_stock}" value="1"
                        data-product-id="${product.id}" data-variant-id="${product.variant_id || ''}"
                        data-type="${product.type}" data-price="${price}" data-mrp="${mrp}"
                        data-stock="${product.current_stock}" data-name="${product.name}"
                        data-sku="${product.sku}" data-hsn="${product.hsn_code || ''}"
                        data-tax="${product.tax_percent || 0}" data-unit="${product.unit}"
                        data-warranty-type="${product.warranty_type}" data-warranty-period="${product.warranty_period}">
                </td>
                <td><input type="checkbox" class="select-product"></td>
            </tr>`);
        });
    }).fail(function() {
        loading.hide();
        tbody.html('<tr><td colspan="7" style="padding:40px;text-align:center;color:#c33;">Failed to load products</td></tr>');
    });
}

function addSelectedProducts() {
    const selectedWarehouseId = $('#itemModalWarehouse').val();
    if (!selectedWarehouseId) { showAlert('Please select a warehouse', 'error'); return; }

    const hasSelected = $('#productsTableBody tr').toArray().some(row => $(row).find('.select-product').is(':checked'));
    if (!hasSelected) { closeAddItemModal(); return; }

    const existingWarehouseId = items.length > 0 ? items[0].warehouse_id : null;
    if (existingWarehouseId && existingWarehouseId !== selectedWarehouseId) {
        items = [];
        showAlert('Warehouse changed — previous items removed', 'info');
    }

    $('input[name="warehouse_id"]').val(selectedWarehouseId);

    $('#productsTableBody tr').each(function() {
        const checkbox = $(this).find('.select-product');
        if (!checkbox.is(':checked')) return;

        const input    = $(this).find('.qty-input');
        const qty      = parseFloat(input.val()) || 0;
        const maxStock = parseFloat(input.data('stock'));

        if (qty <= 0) return;
        if (qty > maxStock) { showAlert(`Only ${maxStock} items available in stock`, 'error'); return; }

        const price    = parseFloat(input.data('price')) || 0;
        const mrpPrice = parseFloat(input.data('mrp')) || 0;
        const partyType = getCurrentPartyType();

        let autoDiscount = 0;
        if (mrpPrice > 0 && mrpPrice > price) autoDiscount = ((mrpPrice - price) / mrpPrice) * 100;

        // gst_inclusive is TRUE only when invoice type is GST AND mode is inclusive
        const isIncl = (currentInvoiceType === 'gst') && (currentGstMode === 'inclusive');

        const item = {
            product_id:     input.data('product-id'),
            variant_id:     input.data('variant-id'),
            product_type:   input.data('type'),
            name:           input.data('name'),
            sku:            input.data('sku'),
            hsn_sac:        currentInvoiceType === 'gst' ? input.data('hsn') : '',
            mrp_price:      mrpPrice,
            price:          price,
            quantity:       qty,
            discount:       parseFloat(autoDiscount.toFixed(2)),
            tax_percent:    currentInvoiceType === 'gst' ? parseFloat(input.data('tax')) || 0 : 0,
            unit:           input.data('unit') || 'PCS',
            warranty_type:  input.data('warranty-type') || 'none',
            warranty_period: parseInt(input.data('warranty-period')) || 0,
            party_type:     partyType,
            warehouse_id:   selectedWarehouseId,
            max_stock:      maxStock,
            gst_inclusive:  isIncl,   // ← KEY FLAG sent to backend
        };

        addItemToInvoice(item);
    });

    const warehouseName = $('#itemModalWarehouse option:selected').text().trim();
    if (warehouseName && warehouseName !== '-- Select Warehouse --') {
        $('#selectedWarehouseBadgeText').text(warehouseName);
        $('#selectedWarehouseBadge').show();
    } else {
        $('#selectedWarehouseBadge').hide();
    }
    closeAddItemModal();
}

function addItemToInvoice(item) {
    const existingIndex = items.findIndex(i => i.product_id === item.product_id && i.variant_id === item.variant_id);
    if (existingIndex > -1) {
        const newQty   = items[existingIndex].quantity + item.quantity;
        const maxStock = items[existingIndex].max_stock || item.max_stock || 0;
        if (newQty > maxStock) {
            const remaining = maxStock - items[existingIndex].quantity;
            showAlert(remaining <= 0 ? `❌ ${item.name} — stock full! (${maxStock})` : `❌ ${item.name} — only ${remaining} more available`, 'error');
            return;
        }
        items[existingIndex].quantity = newQty;
        showAlert(`Updated qty for ${item.name} (${newQty}/${maxStock})`, 'info');
    } else {
        items.push(item);
        showAlert(`Added ${item.name}`, 'success');
    }
    renderItemsTable();
}

function updateItem(index, field, value) {
    if (!items[index]) return;

    if (field === 'quantity') {
        const newQty   = parseFloat(value) || 0;
        const maxStock = items[index].max_stock || 0;
        if (newQty <= 0) { showAlert('Quantity must be > 0', 'error'); renderItemsTable(); return; }
        if (maxStock > 0 && newQty > maxStock) { showAlert(`❌ Only ${maxStock} units available for ${items[index].name}`, 'error'); renderItemsTable(); return; }
    }

    items[index][field] = parseFloat(value) || 0;

    // Sync discount ↔ price
    if (field === 'discount') {
        const mrp = parseFloat(items[index].mrp_price || 0);
        const pct = parseFloat(value || 0);
        if (mrp > 0 && pct >= 0 && pct <= 100) items[index].price = mrp - (mrp * pct / 100);
    }
    if (field === 'price') {
        const mrp  = parseFloat(items[index].mrp_price || 0);
        const sale = parseFloat(value || 0);
        items[index].discount = (mrp > 0 && sale <= mrp) ? ((mrp - sale) / mrp) * 100 : 0;
    }

    renderItemsTable();
}

function onWarehouseChange() {
    const warehouseId   = $('#itemModalWarehouse').val();
    if (!warehouseId) return;
    const warehouseName = $('#itemModalWarehouse option:selected').text();
    $('#warehouseSelectSection').hide();
    $('#productSearchSection').show();
    $('#productsTableWrapper').show();
    $('#selectedWarehouseName').text('🏭 ' + warehouseName);
    $('#searchProduct').val('');
    loadProducts('', warehouseId);
}

function resetWarehouseSelection() {
    $('#warehouseSelectSection').show();
    $('#productSearchSection').hide();
    $('#productsTableWrapper').hide();
    $('#itemModalWarehouse').val('');
    $('#productsTableBody').empty();
    $('#searchProduct').val('');
}

function removeItem(index) { items.splice(index, 1); renderItemsTable(); }
function updateWarrantyType(index, value)   { items[index].warranty_type   = value; }
function updateWarrantyPeriod(index, value) { items[index].warranty_period = parseInt(value) || 0; }

// ===================== RENDER ITEMS TABLE =====================

function renderItemsTable() {
    const tbody       = $('#itemsTableBody');
    const invoiceType = $('#invoiceType').val() || 'gst';

    tbody.empty();
    enableExtraFields();

    if (items.length === 0) {
        const emptyColspan = currentInvoiceType === 'gst' ? 11 : 9;
        tbody.html(`<tr class="empty-row"><td colspan="${emptyColspan}"><div class="empty-items"><div class="empty-icon">🛒</div><p>No items added yet</p><button type="button" class="btn-add-first-item" onclick="openAddItemModal()">+ Add First Item</button></div></td></tr>`);
        $('#itemsTableFooter').hide();
        $('#totalMRP, #totalDiscount, #cgstTotal, #sgstTotal, #igstTotal, #totalTax, #subtotal, #grandTotal').text('0.00');
        calculateBalance();
        return;
    }

    $('#itemsTableFooter').show();
    updatePriceColumnHeaders();

    let footerMRP = 0, footerDiscountAmount = 0, footerSalePrice = 0, footerTaxAmount = 0, footerFinalAmount = 0;

    items.forEach((item, index) => {
        const mrpPrice   = parseFloat(item.mrp_price || 0);
        const salePrice  = parseFloat(item.price || 0);   // inclusive: gross price per unit
        const quantity   = parseFloat(item.quantity || 1);
        const discountPct = parseFloat(item.discount || 0);
        const taxPercent  = invoiceType === 'gst' ? parseFloat(item.tax_percent || 0) : 0;
        const isIncl      = item.gst_inclusive && invoiceType === 'gst';

        const mrpTotal = quantity * mrpPrice;

        let displayBaseTotal, displayTaxAmt, displayFinalTotal, discountAmount;

        if (isIncl && taxPercent > 0) {
            // Inclusive: reverse-extract
            const grossTotal   = quantity * salePrice;
            const baseTotal    = grossTotal * 100 / (100 + taxPercent);
            displayTaxAmt      = grossTotal - baseTotal;
            displayBaseTotal   = baseTotal;
            displayFinalTotal  = grossTotal;           // final = gross (tax already inside)
            discountAmount     = (mrpPrice - salePrice) * quantity;
        } else {
            // Exclusive: normal
            displayBaseTotal   = quantity * salePrice;
            displayTaxAmt      = (displayBaseTotal * taxPercent) / 100;
            displayFinalTotal  = displayBaseTotal + displayTaxAmt;
            discountAmount     = (mrpPrice - salePrice) * quantity;
        }

        footerMRP            += mrpTotal;
        footerDiscountAmount += discountAmount;
        footerSalePrice      += displayBaseTotal;   // footer always shows ex-GST subtotal
        footerTaxAmount      += displayTaxAmt;
        footerFinalAmount    += displayFinalTotal;

        const inclBadge = isIncl ? `<span class="incl-badge">INCL</span>` : '';

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
    });

    $('#footerMRP').text('₹ ' + footerMRP.toFixed(2));
    $('#footerDiscount').text('₹ ' + footerDiscountAmount.toFixed(2));
    $('#footerSalePrice').text('₹ ' + footerSalePrice.toFixed(2));
    $('#footerTax').text('₹ ' + footerTaxAmount.toFixed(2));
    $('#footerFinalAmount').text('₹ ' + footerFinalAmount.toFixed(2));

    if (invoiceType === 'gst') $('#footerTax').show(); else $('#footerTax').hide();

    calculateTaxBreakup();
    calculateInvoiceSummary(footerMRP, footerDiscountAmount, footerTaxAmount, footerSalePrice);
}

// ===================== SELECT PARTY =====================

function selectParty(partyId) {
    $.get('{{ route('admin.sales.get-party-details', ':id') }}'.replace(':id', partyId), function(res) {
        if (!res.success) return;
        const p = res.party;

        $('#selectPartyBtn').hide();
        $('#selectedPartyDetails').show();
        $('#partyNameDisplay').text(p.name);
        $('#partyTypeBadge').text(p.party_type_text).attr('class', `party-type-badge ${p.party_type}`);
        $('#partyPhone').text(p.phone || '-');
        $('#partyEmail').text(p.email || '-');

        const openingBalance = p.dynamic_opening_balance || 0;
        $('#partyOpeningBalance').text('₹ ' + openingBalance.toFixed(2));

        const creditLimit = p.credit_limit ? parseFloat(p.credit_limit) : 0;
        if (creditLimit > 0) {
            $('#partyCreditLimit').html('₹ ' + creditLimit.toFixed(2) + ' <span class="credit-limit-badge">Limited</span>');
        } else {
            $('#partyCreditLimit').text('No Limit');
        }

        $('#partyIdInput').val(p.id);
        $('#partyTypeInput').val(p.party_type);

        updatePriceColumnHeaders();
        updateAddItemButtonState();
        items = [];

        const warehouseState = '{{ $mainWarehouse->state ?? "" }}';
        const partyState     = p.billing_state || '';
        if (warehouseState && partyState) {
            warehouseState === partyState ? showIntraStateTax() : showInterStateTax();
        } else {
            showIntraStateTax();
        }

        renderItemsTable();

        if (p.salesman_id && p.salesman_name) {
            $('#salesmanIdInput').val(p.salesman_id);
            $('#salesmanNameDisplay').text('👤 ' + p.salesman_name);
        } else {
            $('#salesmanIdInput').val('');
            $('#salesmanNameDisplay').text('—');
        }

        window.selectedParty = p;

        $('#billingAddressText').text(p.billing_address || 'No billing address');
        $('#billingAddressInput').val(p.billing_address || '');
        $('#shippingAddressText').text(p.shipping_address || p.billing_address || 'No shipping address');
        $('#shippingAddressInput').val(p.shipping_address || p.billing_address || '');

        closeSelectPartyModal();
        showAlert('Party selected successfully', 'success');

        // Credit note balance
        $('#creditNoteRemainingRow').remove();
        const creditRemaining = parseFloat(p.credit_notes_remaining || 0);
        if (creditRemaining > 0) {
            $('.info-column').first().append(`
                <div class="info-row" id="creditNoteRemainingRow">
                    <span class="info-label">CN Balance:</span>
                    <span class="info-value" style="color:#047857;font-weight:600;">
                        ₹ ${creditRemaining.toFixed(2)}
                        <small style="background:#d1fae5;color:#065f46;padding:1px 5px;border-radius:3px;font-size:9px;margin-left:4px;">WILL AUTO-ADJUST</small>
                    </span>
                </div>`);
        }

        $('#advanceBalanceRow').remove();
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
            if (info.is_exceeded) {
                showAlert('⚠️ CREDIT LIMIT EXCEEDED! Current Due: ₹' + info.current_due.toFixed(2), 'error');
            } else if (info.warning_level === 'warning') {
                showAlert('⚠️ Approaching Credit Limit. Available: ₹' + info.available_credit.toFixed(2), 'error');
            }
        }
    });
}

function validateCreditLimit(newInvoiceBalance) {
    if (!window.currentPartyCreditInfo) return true;
    const info = window.currentPartyCreditInfo;
    if (!info.has_limit) return true;
    const newTotalDue = info.current_due + newInvoiceBalance;
    if (newTotalDue > info.credit_limit) {
        const availableCredit = info.credit_limit - info.current_due;
        showAlert('❌ Credit Limit Exceeded!\nCurrent Due: ₹' + info.current_due.toFixed(2) + '\nNew Balance: ₹' + newInvoiceBalance.toFixed(2) + '\nTotal: ₹' + newTotalDue.toFixed(2) + '\nLimit: ₹' + info.credit_limit.toFixed(2) + '\nAvailable: ₹' + availableCredit.toFixed(2), 'error');
        return false;
    }
    return true;
}

function updateCreditDisplay(info) {
    $('#partyOpeningBalance').text('₹ ' + info.opening_balance.toFixed(2));
    if (info.has_limit) {
        let badgeClass = 'credit-limit-badge';
        if (info.warning_level === 'danger')   badgeClass += ' danger';
        else if (info.warning_level === 'warning') badgeClass += ' warning';
        $('#partyCreditLimit').html('₹ ' + info.credit_limit.toFixed(2) + ` <span class="${badgeClass}">${info.usage_percent}% used</span>`);
        if (info.available_credit !== null) {
            $('#partyCreditLimit').append(`<br><small class="available-credit">Available: ₹ ${info.available_credit.toFixed(2)}</small>`);
        }
    } else {
        $('#partyCreditLimit').text('No Limit');
    }
}

$('#createPartyForm').submit(function(e) {
    e.preventDefault();
    const phone = $('input[name="phone"]').val();
    if (!/^[6-9]\d{9}$/.test(phone)) { showAlert('Please enter a valid 10-digit phone number', 'error'); return; }
    const email = $('input[name="email"]').val();
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showAlert('Please enter a valid email address', 'error'); return; }
    const gst = $('input[name="gst_number"]').val();
    if (gst && !/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/.test(gst)) { showAlert('Please enter a valid GST number', 'error'); return; }

    const formData = $(this).serializeArray();
    formData.push({ name: 'same_billing_shipping', value: $('#sameBillingShipping').is(':checked') ? '1' : '0' });
    $.ajax({
        url: '{{ route('admin.sales.create-party') }}', type: 'POST', data: $.param(formData),
        success: function(response) {
            if (response.success) { showAlert('Party created!', 'success'); closeCreatePartyModal(); selectParty(response.party_id); }
            else showAlert('Error: ' + response.message, 'error');
        },
        error: function(xhr) {
            let message = 'Failed to create party';
            if (xhr.responseJSON?.errors) message = Object.values(xhr.responseJSON.errors).flat().join(', ');
            else if (xhr.responseJSON?.message) message = xhr.responseJSON.message;
            showAlert(message, 'error');
        }
    });
});

$(document).on('click', '.party-type-tab', function() {
    $('.party-type-tab').removeClass('active');
    $(this).addClass('active');
    currentPartyType = $(this).data('type');
    loadParties($('#searchParty').val());
});

$('#searchParty').on('input', function() {
    const value = this.value.toLowerCase();
    $('#partiesTableBody tr').each(function() {
        const name  = $(this).find('td:first').text().toLowerCase();
        const phone = $(this).find('td:eq(2)').text().toLowerCase();
        const email = $(this).find('td:eq(3)').text().toLowerCase();
        $(this).toggle(name.includes(value) || phone.includes(value) || email.includes(value));
    });
});

$('#searchProduct').on('input', function() {
    const value = this.value.toLowerCase();
    $('#productsTableBody tr').each(function() {
        const name = $(this).find('.product-name').text().toLowerCase();
        const code = $(this).find('.product-code').text().toLowerCase();
        $(this).toggle(name.includes(value) || code.includes(value));
    });
});

function copyBillingToShipping() {
    const form = $('#createPartyForm');
    ['address','city','state','pincode','country'].forEach(f => {
        form.find(`input[name="shipping_${f}"]`).val(form.find(`input[name="billing_${f}"]`).val());
    });
}
$('#sameBillingShipping').on('change', function() { if (this.checked) copyBillingToShipping(); });
$('input[name^="billing_"]').on('input', function() { if ($('#sameBillingShipping').is(':checked')) copyBillingToShipping(); });

// ===================== EXTRA DISCOUNT/CHARGE =====================

function toggleExtraDiscount() {
    if (items.length === 0) { showAlert('Please add at least one item first', 'error'); return; }
    $('#extraDiscountPercentRow').hide(); $('#extraDiscountPercent').val('');
    $('#extraDiscountRow').toggle();
    if ($('#extraDiscountRow').is(':visible')) { $('#extraDiscountTypeInput').val('amount'); $('#extraDiscount').focus(); }
    else { $('#extraDiscount').val(''); }
    calculateTotals();
}

function toggleExtraDiscountPercent() {
    if (items.length === 0) { showAlert('Please add at least one item first', 'error'); return; }
    $('#extraDiscountRow').hide(); $('#extraDiscount').val('');
    $('#extraDiscountPercentRow').toggle();
    if ($('#extraDiscountPercentRow').is(':visible')) { $('#extraDiscountTypeInput').val('percent'); $('#extraDiscountPercent').focus(); }
    else { $('#extraDiscountPercent').val(''); }
    calculateTotals();
}

function toggleExtraCharge() {
    if (items.length === 0) { showAlert('Please add at least one item first', 'error'); return; }
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
    const invoiceDate = $('#invoiceDate').val();
    const days = parseInt($('#paymentTermsDays').val()) || 0;
    if (!invoiceDate || days <= 0) return;
    const date = new Date(invoiceDate);
    date.setDate(date.getDate() + days);
    $('#dueDate').val(date.toISOString().split('T')[0]);
    $('#paymentTermsInput').val(`Due in ${days} days`);
}

function updatePaymentTermsFromDueDate() {
    const invoiceDate = $('#invoiceDate').val();
    const dueDate     = $('#dueDate').val();
    if (!invoiceDate || !dueDate) return;
    const diffDays = Math.ceil(Math.abs(new Date(dueDate) - new Date(invoiceDate)) / 86400000);
    $('#paymentTermsDays').val(diffDays);
    $('#paymentTermsInput').val(`Due in ${diffDays} days`);
}

function markFullyPaid() {
    $('#amountPaid').val((parseFloat($('#grandTotal').text()) || 0).toFixed(2));
    calculateBalance();
}

function validateAndCalculateBalance() {
    const grand = parseFloat($('#grandTotal').text()) || 0;
    const paid  = parseFloat($('#amountPaid').val())  || 0;
    $('#amountPaid').css('border-color', paid > grand ? '#dc3545' : '#ccc');
    calculateBalance();
}

function clampAmountPaid() {
    const grand = parseFloat($('#grandTotal').text()) || 0;
    let paid    = parseFloat($('#amountPaid').val())  || 0;
    if (paid > grand) {
        paid = grand;
        $('#amountPaid').val(paid.toFixed(2));
        $('#amountPaid').css('border-color', '#ccc');
        showAlert('Amount paid cannot exceed grand total', 'error');
    }
    calculateBalance();
}

// subtotal = ex-GST base, taxTotal = extracted tax
function calculateInvoiceSummary(totalMRP, totalDiscount, totalTax, subtotal) {
    let extraDiscount     = 0;
    const discountType    = $('#extraDiscountTypeInput').val();
    const invoiceType     = $('#invoiceType').val();

    if (discountType === 'percent') {
        const pct = parseFloat($('#extraDiscountPercent').val()) || 0;
        if (pct > 0 && subtotal > 0) extraDiscount = subtotal * pct / 100;
    } else {
        extraDiscount = parseFloat($('#extraDiscount').val()) || 0;
    }

    const afterDiscountSubtotal = subtotal - extraDiscount;
    let grandTotal = afterDiscountSubtotal + (invoiceType === 'gst' ? totalTax : 0);
    const extraCharge = parseFloat($('#extraCharge').val()) || 0;
    grandTotal += extraCharge;

    if ($('#autoRoundOff').is(':checked')) grandTotal = Math.round(grandTotal);

    $('#totalMRP').text(totalMRP.toFixed(2));
    $('#totalDiscount').text(totalDiscount.toFixed(2));
    $('#subtotal').text(subtotal.toFixed(2));
    $('#grandTotal').text(grandTotal.toFixed(2));

    clampAmountPaid();
}

function calculateTotals() { renderItemsTable(); }

function calculateBalance() {
    const grandTotal = parseFloat($('#grandTotal').text().replace(/,/g, '')) || 0;
    const amountPaid = parseFloat($('#amountPaid').val()) || 0;

    let creditRemaining = 0;
    const creditRowText = $('#creditNoteRemainingRow .info-value').text();
    if (creditRowText) {
        const match = creditRowText.match(/₹\s*([\d,]+\.?\d*)/);
        if (match) creditRemaining = parseFloat(match[1].replace(/,/g, '')) || 0;
    }

    const remainingAfterCash = Math.max(0, grandTotal - amountPaid);
    const creditWillUse      = Math.min(creditRemaining, remainingAfterCash);
    const finalBalance        = Math.max(0, grandTotal - (amountPaid + creditWillUse));

    $('#balanceAmount').text(finalBalance.toFixed(2));
    $('#creditAdjustmentInfo').remove();

    if (creditWillUse > 0) {
        const cashRequired = Math.max(0, grandTotal - creditWillUse);
        $('#amountPaid').closest('.payment-section-container').append(`
            <div id="creditAdjustmentInfo" style="margin-top:8px;padding:8px 10px;background:#d1fae5;border-radius:5px;font-size:11px;color:#065f46;border:1px solid #6ee7b7;">
                ✓ Credit Note ₹${creditWillUse.toFixed(2)} will be auto-adjusted on generate
                <br><span style="font-size:10px;color:#047857;">Customer needs to pay only ₹${cashRequired.toFixed(2)}</span>
            </div>`);
    }
}

function validateForm() {
    if (!$('#partyIdInput').val())  { showAlert('Please select a party', 'error'); return false; }
    if (!$('#invoiceType').val())   { showAlert('Please select invoice type', 'error'); return false; }
    if (items.length === 0)         { showAlert('Please add at least one item', 'error'); return false; }
    return true;
}

function showAlert(message, type = 'success') {
    const container = document.getElementById('alertContainer');
    const alert     = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `<span>${message}</span>`;
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}

// ===================== DOCUMENT READY =====================

$(document).ready(function() {
    window.selectedParty = null;

    $('#invoiceType').val('gst');
    currentInvoiceType = 'gst';
    currentGstMode     = 'exclusive';
    showGSTInvoiceFields();
    updateAddItemButtonState();
    $('.items-table-container').addClass('gst-mode');
    showIntraStateTax();

    $('#extraDiscount, #extraDiscountPercent, #extraCharge').on('input', calculateTotals);
    $('#invoiceDate').on('change', updateDueDateFromTerms);
    $('#paymentTermsDays').on('change', updateDueDateFromTerms);
    $('#dueDate').on('change', updatePaymentTermsFromDueDate);
    $('#amountPaid').on('input', validateAndCalculateBalance);

    $(document).on('keydown', function(event) {
        if (event.key === 'Escape') { closeSelectPartyModal(); closeCreatePartyModal(); closeAddItemModal(); }
    });

    setTimeout(function() {
        const termsDays = parseInt($('#paymentTermsDays').val());
        if (termsDays > 0) updateDueDateFromTerms();
    }, 100);

    // ── FORM SUBMIT ──────────────────────────────────────────────────────
    $('#salesInvoiceForm').submit(function(e) {
        e.preventDefault();
        if (isSubmitting) { showAlert('Please wait...', 'info'); return; }
        if (!validateForm()) return;

        const grandTotal        = parseFloat($('#grandTotal').text()) || 0;
        const amountPaid        = parseFloat($('#amountPaid').val()) || 0;
        const newInvoiceBalance = grandTotal - amountPaid;
        if (!validateCreditLimit(newInvoiceBalance)) return;

        isSubmitting = true;
        const $submitBtn  = $('.btn-submit-invoice');
        const originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<span class="spinner"></span> Creating...');

        const formData = new FormData();
        $(this).serializeArray().forEach(item => formData.append(item.name, item.value));
        formData.append('items',           JSON.stringify(items));
        formData.append('subtotal',        $('#subtotal').text());
        formData.append('grand_total',     $('#grandTotal').text());
        formData.append('tax_amount',      $('#totalTax').text());
        formData.append('cgst_total',      cgstTotal.toFixed(2));
        formData.append('sgst_total',      sgstTotal.toFixed(2));
        formData.append('igst_total',      igstTotal.toFixed(2));
        formData.append('tax_type',        currentTaxType);
        formData.append('discount_amount', $('#totalDiscount').text());
        formData.append('balance_amount',  $('#balanceAmount').text());
        formData.append('gst_mode',        currentGstMode);

        const discountType = $('#extraDiscountTypeInput').val();
        if (discountType === 'percent') {
            formData.append('extra_discount',      $('#extraDiscountPercent').val() || 0);
            formData.append('extra_discount_type', 'percent');
        } else {
            formData.append('extra_discount',      $('#extraDiscount').val() || 0);
            formData.append('extra_discount_type', 'amount');
        }
        formData.append('extra_charge',    $('#extraCharge').val() || 0);
        formData.append('charge_name',     $('#chargeName').val() || '');
        formData.append('auto_round_off',  $('#autoRoundOff').is(':checked') ? 1 : 0);

        $.ajax({
            url: '{{ route('admin.sales.store') }}', type: 'POST', data: formData,
            processData: false, contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('Invoice created successfully!', 'success');
                    setTimeout(() => { window.location.href = '/admin/sales/' + response.invoice_id; }, 1500);
                } else {
                    showAlert('Error: ' + response.message, 'error');
                    isSubmitting = false;
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                let message = 'Failed to create invoice';
                if (xhr.responseJSON?.message) message = xhr.responseJSON.message;
                else if (xhr.responseJSON?.errors) message = Object.values(xhr.responseJSON.errors).flat().join(', ');
                showAlert(message, 'error');
                isSubmitting = false;
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush
@endsection
