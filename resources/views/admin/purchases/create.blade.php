@extends('layouts.admin')

@section('title', 'Create Purchase Invoice - Admin Panel')
@section('header-title', 'Create Purchase Invoice')

@section('content')
<div class="products-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Create Purchase Invoice</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.purchases.index') }}" class="btn-small btn-secondary">
                ← Back to Purchases
            </a>
        </div>
    </div>

    <!-- Invoice Form -->
    <div class="invoice-form-wrapper">
        <form id="purchaseInvoiceForm" class="invoice-form">
            @csrf
            <input type="hidden" name="invoice_number"        value="{{ $invoiceNumber }}">
            <input type="hidden" name="warehouse_id"          id="warehouseIdInput" value="{{ $mainWarehouse->_id ?? '' }}">
            <input type="hidden" name="party_type"            id="partyTypeInput">
            <input type="hidden" name="vendor_id"             id="vendorIdInput">
            <input type="hidden" name="dealer_id"             id="dealerIdInput">
            <input type="hidden" name="distributor_id"        id="distributorIdInput">
            <input type="hidden" name="purchase_executive_id" id="purchaseExecutiveIdInput">
            <input type="hidden" name="billing_address"       id="billingAddressInput">
            <input type="hidden" name="shipping_address"      id="shippingAddressInput">
            <input type="hidden" name="extra_discount_type"   id="extraDiscountTypeInput" value="amount">

            <div class="form-row">
                <div class="form-col-main">
                    <div class="two-col-row">

                        <!-- ====== LEFT: Party Section ====== -->
                        <div class="col-50">
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-header-left">
                                        <div class="section-icon">🏢</div>
                                        <div class="section-title">
                                            <h3>Bill From</h3>
                                            <p>Select vendor, dealer or distributor</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-select-customer" id="selectPartyBtn" onclick="openSelectPartyModal()">
                                        <span class="btn-icon">🏢</span>
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
                                            </div>
                                        </div>

                                        <!-- Auto PE (vendor) -->
                                        <div id="autoPeSection" style="display:none; margin: 10px 0 4px; padding: 8px 10px; background:#fffbf5; border:1px solid #fed7aa; border-radius:5px;">
                                            <div style="font-size:10px;font-weight:700;color:#92400e;margin-bottom:3px;">📌 Purchase Executive</div>
                                            <div id="autoPeName" style="font-size:12px;font-weight:700;color:#f97316;"></div>
                                            <span style="font-size:9px;background:#d1fae5;color:#065f46;padding:1px 6px;border-radius:8px;font-weight:600;">Auto-assigned</span>
                                        </div>

                                        <!-- Manual PE (dealer/distributor) -->
                                        <div id="manualPeSection" style="display:none; margin: 10px 0 4px; padding: 8px 10px; background:#fffbf5; border:1px solid #fed7aa; border-radius:5px;">
                                            <div style="font-size:10px;font-weight:700;color:#92400e;margin-bottom:5px;">
                                                👤 Select Purchase Executive <span style="color:#ef4444">*</span>
                                            </div>
                                            <select id="manualPeSelect" class="form-control" onchange="onPeChange()">
                                                <option value="">— Select Purchase Executive —</option>
                                                @foreach($purchaseExecutives as $pe)
                                                    <option value="{{ $pe->_id }}">{{ $pe->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

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
                                                </div>
                                                <div class="address-card">
                                                    <div class="address-card-header">
                                                        <span class="address-type">Shipping Address</span>
                                                    </div>
                                                    <div class="address-content">
                                                        <p id="shippingAddressText">Select a party to view address</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ====== RIGHT: Invoice Details ====== -->
                        <div class="col-50">
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
                                            <label class="form-label">Purchase Invoice No.</label>
                                            <input type="text" class="form-control" value="{{ $invoiceNumber }}" readonly tabindex="-1">
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
                                            <label class="form-label">Purchase Executive</label>
                                            <div class="salesman-display" id="peDisplay">—</div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label required">Invoice Type</label>
                                            <select name="invoice_type" id="invoiceType" class="form-control" required onchange="handleInvoiceTypeChange()">
                                                <option value="gst" selected>GST Invoice</option>
                                                <option value="cash">Cash Memo</option>
                                            </select>
                                            <small class="form-text text-muted" style="font-size:10px;margin-top:3px;">GST Invoice includes HSN code and tax calculations</small>
                                        </div>
                                        <div class="form-group col-6">
                                            <label class="form-label">Warehouse</label>
                                            <input type="text" class="form-control" value="{{ ($mainWarehouse->name ?? 'Main Warehouse') . ' (Main)' }}" readonly style="background:#f0f8ff;color:#0066cc;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ====== ITEMS SECTION ====== -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-header-left">
                                <div class="section-icon">🛒</div>
                                <div class="section-title">
                                    <h3>Items</h3>
                                    <p>Add products to purchase invoice</p>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <button type="button" class="btn-add-item" id="addItemBtn" onclick="openAddItemModal()">
                                    + Add Item
                                </button>
                            </div>
                        </div>

                        <div class="section-body">
                            <div class="items-table-container" id="itemsTableContainer">
                                <table class="items-table" id="mainItemsTable">
                                    <thead>
                                        <tr>
                                            <th class="th-item">Item</th>
                                            <th class="th-sku">SKU</th>
                                            <th class="th-hsn" id="hsnHeader">HSN/SAC</th>
                                            <th class="th-unit">Unit</th>
                                            <th class="th-qtys">Qty</th>
                                            <th class="th-mrp">MRP (₹)</th>
                                            <th class="th-sale-price">Purchase Price (₹)</th>
                                            <th class="th-tax" id="taxHeader">Tax %</th>
                                            <th class="th-amount">Total (₹)</th>
                                            <th class="th-action">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                        <tr class="empty-row">
                                            <td colspan="10">
                                                <div class="empty-items">
                                                    <div class="empty-icon">📦</div>
                                                    <p>No items added yet</p>
                                                    <button type="button" class="btn-add-first-item" onclick="openAddItemModal()">
                                                        + Add First Item
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot id="itemsTableFooter" style="display:none;background:#f0f0f0;font-weight:600;border-top:2px solid #333;">
                                        <tr>
                                            <td colspan="5" style="text-align:right;padding:10px;font-size:12px;"><strong>SUBTOTAL:</strong></td>
                                            <td style="padding:10px;text-align:center;font-size:12px;" id="footerMRP">₹0.00</td>
                                            <td style="padding:10px;text-align:center;font-size:12px;" id="footerPurchase">₹0.00</td>
                                            <td style="padding:10px;text-align:center;font-size:12px;" id="footerTax">₹0.00</td>
                                            <td style="padding:10px;text-align:center;font-size:12px;" id="footerTotal">₹0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ====== SUMMARY + PAYMENT ====== -->
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
                                            <div class="summary-divider"></div>
                                            <div class="summary-row">
                                                <span class="summary-label" style="font-weight:600;">Subtotal</span>
                                                <span class="summary-value">₹ <span id="subtotal">0.00</span></span>
                                            </div>

                                            <!-- TAX BREAKUP -->
                                            <div id="taxBreakupContainer" style="display:none;">
                                                <div id="intraStateTax" style="display:none;">
                                                    <div class="summary-row">
                                                        <span class="summary-label">CGST</span>
                                                        <span class="summary-value">+ ₹ <span id="cgstTotal">0.00</span></span>
                                                    </div>
                                                    <div class="summary-row">
                                                        <span class="summary-label">SGST</span>
                                                        <span class="summary-value">+ ₹ <span id="sgstTotal">0.00</span></span>
                                                    </div>
                                                </div>
                                                <div id="interStateTax" style="display:none;">
                                                    <div class="summary-row">
                                                        <span class="summary-label">IGST</span>
                                                        <span class="summary-value">+ ₹ <span id="igstTotal">0.00</span></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="summary-row" id="totalTaxRow" style="border-top:1px dashed #ddd;padding-top:5px;display:none;">
                                                <span class="summary-label">Total Tax</span>
                                                <span class="summary-value">+ ₹ <span id="totalTax">0.00</span></span>
                                            </div>

                                            <div class="summary-divider"></div>

                                            <!-- Extra Discount -->
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
                                                    <input type="checkbox" id="autoRoundOff" onchange="calculateTotals()"> Auto Round Off
                                                </label>
                                            </div>

                                            <div class="summary-row total-row">
                                                <span class="summary-label">Grand Total</span>
                                                <span class="summary-value">₹ <span id="grandTotal">0.00</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- RIGHT: Payment -->
                                <div class="invoice-col">
                                    <div class="payment-section-container">
                                        <h4>Payment Details</h4>
                                        <div class="form-group">
                                            <label class="form-label">Payment Method</label>
                                            <select name="payment_method" class="form-control">
                                                <option value="cash">Cash</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="cheque">Cheque</option>
                                                <option value="upi">UPI</option>
                                                <option value="credit">Credit</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Amount Paid</label>
                                            <input type="number" name="amount_paid" id="amountPaid" class="form-control" step="0.01" min="0" oninput="calculateBalance()">
                                        </div>
                                        <div class="form-group">
                                            <button type="button" onclick="markFullyPaid()" class="btn-mark-paid" style="width:auto;padding:4px 8px;font-size:10px;">
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
                    <div class="form-row" style="margin-top:10px;">
                        <div class="form-group col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes or remarks..."></textarea>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div style="display:flex;justify-content:flex-end;">
                        <button type="submit" class="btn-submit-invoice" id="submitBtn">
                            <span class="btn-icon">💾</span>
                            Save Purchase Invoice
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ==================== SELECT PARTY MODAL ==================== --}}
<div class="modal" id="selectPartyModal">
    <div class="modal-overlay" onclick="closeSelectPartyModal()"></div>
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <div class="modal-icon">👥</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Select Party</h4>
                <div class="modal-subtitle">Choose vendor, dealer or distributor</div>
            </div>
            <button type="button" class="modal-close" onclick="closeSelectPartyModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="party-type-tabs">
                <button type="button" class="party-type-tab active" data-type="all">All</button>
                <button type="button" class="party-type-tab" data-type="vendor">Vendors</button>
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
                    <span class="btn-icon">+</span> Create New Party
                </button>
            </div>
            <div class="parties-table-container">
                <table class="parties-table">
                    <thead>
                        <tr>
                            <th>Name</th><th>Type</th><th>Phone</th>
                            <th>Email</th><th>Opening Bal.</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="partiesTableBody"></tbody>
                </table>
                <div id="partiesLoading" class="loading-state">
                    <div class="loading-spinner"></div><p>Loading parties...</p>
                </div>
                <div id="noParties" class="no-data-state" style="display:none;">
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

{{-- ==================== CREATE PARTY MODAL ==================== --}}
{{-- Vendor → show Purchase Executive | Dealer/Distributor → NO PE, NO Salesman at creation --}}
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
                            <select name="party_type" id="createPartyType" class="form-control" required onchange="onCreatePartyTypeChange(this.value)">
                                <option value="vendor">Vendor</option>
                                <option value="dealer">Dealer</option>
                                <option value="distributor">Distributor</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label">GST Number</label>
                            <input type="text" name="gst_number" class="form-control" maxlength="15" oninput="this.value=this.value.toUpperCase()">
                        </div>
                        <div class="form-group col-6">
                            <label class="form-label">Opening Balance</label>
                            <input type="number" name="opening_balance" class="form-control" step="0.01" min="0" value="0">
                        </div>
                    </div>

                    {{-- Purchase Executive — only for Vendor --}}
                    <div id="cpVendorPeField" class="form-row">
                        <div class="form-group col-6" style="width:100%">
                            <label class="form-label">Assign Purchase Executive <small style="color:#92400e;">(Vendor only)</small></label>
                            <select name="purchase_executive_id" class="form-control">
                                <option value="">— None —</option>
                                @foreach($purchaseExecutives as $pe)
                                    <option value="{{ $pe->_id }}">{{ $pe->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- NOTE: Dealer/Distributor → PE selected per-invoice, NOT at party creation --}}
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
                        <label class="form-label required">Address</label>
                        <input type="text" name="billing_address" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label required">City</label>
                            <input type="text" name="billing_city" class="form-control" required>
                        </div>
                        <div class="form-group col-6">
                            <label class="form-label required">State</label>
                            <input type="text" name="billing_state" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label required">Pincode</label>
                            <input type="text" name="billing_pincode" class="form-control" maxlength="6" required>
                        </div>
                        <div class="form-group col-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="billing_country" class="form-control" value="India">
                        </div>
                    </div>
                </div>

                <div class="form-section-small">
                    <h5>Shipping Address</h5>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" name="shipping_address" class="form-control">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label">City</label>
                            <input type="text" name="shipping_city" class="form-control">
                        </div>
                        <div class="form-group col-6">
                            <label class="form-label">State</label>
                            <input type="text" name="shipping_state" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="shipping_pincode" class="form-control" maxlength="6">
                        </div>
                        <div class="form-group col-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="shipping_country" class="form-control" value="India">
                        </div>
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

{{-- ==================== ADD ITEM MODAL ==================== --}}
<div class="modal" id="addItemModal">
    <div class="modal-overlay" onclick="closeAddItemModal()"></div>
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <div class="modal-icon">🛒</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Add Items</h4>
                <div class="modal-subtitle">Select products to purchase</div>
            </div>
            <button type="button" class="modal-close" onclick="closeAddItemModal()">×</button>
        </div>
        <div class="modal-body">
            <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;">
                <div class="search-container" style="flex:1;">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="searchProduct" class="search-input" placeholder="Search products by name or SKU...">
                    </div>
                </div>
                <button type="button" onclick="openQuickAddModal()" style="padding:7px 14px;background:#555;color:white;border:none;border-radius:3px;font-size:10px;font-weight:500;cursor:pointer;white-space:nowrap;">
                    ➕ New Product
                </button>
            </div>
            <div class="products-table-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Item Name</th><th>SKU</th><th>MRP (₹)</th>
                            <th>Purchase Price (₹)</th><th>Unit</th>
                            <th id="modalTaxHeader">Tax %</th>
                            <th>Quantity</th><th>Select</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody"></tbody>
                </table>
                <div id="productsLoading" class="loading-state" style="display:none;">
                    <div class="loading-spinner"></div><p>Loading products...</p>
                </div>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeAddItemModal()">Cancel</button>
            <button type="button" class="btn-modal btn-primary" onclick="addSelectedProducts()">Add Selected</button>
        </div>
    </div>
</div>

{{-- ==================== QUICK ADD PRODUCT MODAL ==================== --}}
<div class="modal" id="quickAddModal">
    <div class="modal-overlay" onclick="closeQuickAddModal()"></div>
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <div class="modal-icon">⚡</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Quick Add Product</h4>
                <div class="modal-subtitle">Create a simple product and add to invoice</div>
            </div>
            <button type="button" class="modal-close" onclick="closeQuickAddModal()">×</button>
        </div>
        <form id="quickAddForm">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col-6">
                        <label class="form-label required">Product Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group col-6">
                        <label class="form-label required">SKU Code</label>
                        <input type="text" name="sku_code" class="form-control" maxlength="16" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">— None —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-6">
                        <label class="form-label required">Unit</label>
                        <select name="unit" class="form-control" required>
                            <option value="">Select</option>
                            <option value="piece">Piece</option><option value="set">Set</option>
                            <option value="box">Box</option><option value="meter">Meter</option>
                            <option value="kg">KG</option><option value="liter">Liter</option>
                            <option value="pack">Pack</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-6">
                        <label class="form-label required">Purchase Price (₹)</label>
                        <input type="number" name="cost_price" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="form-group col-6">
                        <label class="form-label required">MRP (₹)</label>
                        <input type="number" name="mrp_price" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-6">
                        <label class="form-label required">HSN Code</label>
                        <input type="text" name="hsn_code" class="form-control" maxlength="8" required>
                    </div>
                    <div class="form-group col-6">
                        <label class="form-label required">GST %</label>
                        <input type="number" name="gst" class="form-control" step="0.01" min="0" max="100" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-6">
                        <label class="form-label required">Opening Stock</label>
                        <input type="number" name="opening_stock" class="form-control" min="0" value="0" required>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closeQuickAddModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-primary">Create & Add</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* ============ REUSE EXACT SAME STYLES AS SALES CREATE ============ */
.products-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 11px; line-height: 1.3; color: #333;
}
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

/* Party section */
.btn-select-customer { width:180px; padding:8px 12px; background:#fa8725de; color:white; border:none; border-radius:3px; font-size:11px; font-weight:500; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:5px; }
.selected-customer-details { padding:16px; background:#fafafa; border-radius:4px; border:1px solid #ddd; }
.customer-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:4px; padding-bottom:4px; border-bottom:1px solid #eee; }
.customer-header h4 { font-size:13px; font-weight:600; color:#333; margin:0; }
.btn-change-customer { padding:3px 8px; background:#666; color:white; border:none; border-radius:3px; font-size:10px; cursor:pointer; }
.customer-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
.info-column .info-row { display:flex; align-items:center; margin-bottom:6px; }
.info-column .info-label { font-size:10px; font-weight:500; color:#555; width:70px; flex-shrink:0; }
.info-column .info-value { font-size:10px; color:#333; }

.salesman-display { padding:6px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; font-size:10px; color:#333; display:flex; align-items:center; gap:5px; }

.party-type-badge { display:inline-block; padding:2px 8px; border-radius:12px; font-size:9px; font-weight:600; text-transform:uppercase; background:#e7f1ff; color:#0066cc; margin-left:8px; }
.party-type-badge.vendor { background:#e0f2fe; color:#0369a1; }
.party-type-badge.dealer { background:#fef3c7; color:#856404; }
.party-type-badge.distributor { background:#d1fae5; color:#065f46; }

/* Address */
.address-section { padding-top:2px; border-top:1px solid #eee; }
.address-header h5 { font-size:11px; font-weight:600; color:#333; margin:0 0 12px; }
.address-grid { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
.address-card { background:#fafafa; border:1px solid #ddd; border-radius:4px; padding:12px; }
.address-card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid #eee; }
.address-type { font-size:10px; font-weight:600; color:#333; }
.address-content { font-size:10px; color:#555; line-height:1.4; }

/* Payment terms */
.payment-terms-days { display:flex; align-items:center; gap:5px; }
.payment-terms-days .form-control { width:50%; flex-shrink:0; }
.payment-terms-days span { font-size:10px; color:#666; white-space:nowrap; }

/* Items table */
.btn-add-item { padding:6px 12px; background:#555; color:white; border:none; border-radius:3px; font-size:10px; font-weight:500; cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:4px; }
.btn-add-item:hover { background:#444; }
.items-table-container { overflow-x:auto; width:100%; margin-top:10px; border-radius:4px; border:1px solid #ddd; }
.items-table { width:100%; border-collapse:collapse; font-size:10px; min-width:900px; }
.items-table th { background:#f8f9fa; padding:10px 8px; text-align:center; font-weight:600; color:#333; border-bottom:2px solid #ddd; white-space:nowrap; font-size:11px; }
.items-table td { padding:8px; border-bottom:1px solid #eee; vertical-align:middle; text-align:center; font-size:10px; }
.items-table td.item-name { text-align:left; font-weight:500; }
.items-table tr:hover { background:#f9f9f9; }
.items-table input[type="number"] { width:100%; padding:4px 6px; border:1px solid #ccc; border-radius:2px; font-size:10px; text-align:center; }
.th-item { min-width:140px; text-align:left !important; }
.th-sku { width:80px; }
.th-hsn { width:75px; }
.th-qtys { width:65px; }
.th-unit { width:60px; }
.th-sale-price { width:110px; }
.th-mrp { width:85px; }
.th-tax { width:70px; }
.th-amount { width:90px; }
.th-action { width:60px; }
.btn-delete { width:28px; height:28px; border:none; background:#fee; color:#c33; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .2s; margin:0 auto; font-size:14px; }
.btn-delete:hover { background:#fcc; color:#a00; }
.empty-row td { padding:30px 20px; text-align:center; }
.empty-items { display:flex; flex-direction:column; align-items:center; gap:8px; }
.empty-icon { font-size:24px; opacity:.3; margin-bottom:5px; }
.empty-items p { font-size:10px; color:#666; margin:0; }
.btn-add-first-item { padding:6px 12px; background:#555; color:white; border:none; border-radius:3px; font-size:10px; cursor:pointer; }

/* Summary + Payment */
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
.summary-label a:hover { background:#fa8725; color:white; border-style:solid; }
.summary-value { font-size:11px; font-weight:600; color:#333; }
.summary-divider { height:1px; background:#eee; margin:10px 0; }
.total-row { margin-top:10px; padding-top:10px; border-top:2px solid #333; }
.total-row .summary-label { font-size:12px; font-weight:700; color:#333; }
.total-row .summary-value { font-size:15px; font-weight:700; color:#fa8725; }
.summary-input { width:100%; padding:4px; font-size:10px; }
.summary-input-small { width:70px; padding:4px; font-size:10px; }
.payment-section-container { background:white; border:1px solid #ddd; border-radius:4px; padding:15px; }
.payment-section-container h4 { font-size:11px; font-weight:600; color:#333; margin:0 0 12px; padding-bottom:10px; border-bottom:1px solid #eee; }
.balance-amount { padding:6px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; font-size:11px; font-weight:600; color:#333; }
.btn-mark-paid { background:#555555; color:white; border:none; border-radius:3px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:3px; transition:all .2s; }
.btn-submit-invoice { width:25%; padding:10px 15px; background:#f98725; color:white; border:none; border-radius:4px; font-size:11px; font-weight:600; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:center; gap:6px; margin-top:15px; }
.btn-submit-invoice:disabled { opacity:.6; cursor:not-allowed; }

/* Modal */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:1000; align-items:center; justify-content:center; }
.modal-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.4); }
.modal-content { position:relative; background:white; border-radius:6px; width:90%; max-width:800px; max-height:90vh; overflow-y:auto; animation:modalFadeIn .2s ease; box-shadow:0 4px 20px rgba(0,0,0,.15); }
.modal-lg { max-width:800px; }
.modal-xl { max-width:800px; }
@keyframes modalFadeIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.modal-header { display:flex; align-items:center; gap:10px; padding:15px 20px; border-bottom:1px solid #ddd; background:#f8f9fa; border-radius:6px 6px 0 0; }
.modal-title-section { flex:1; }
.modal-title { font-size:14px; font-weight:600; color:#333; margin:0; }
.modal-subtitle { font-size:10px; color:#666; margin-top:2px; }
.modal-close { background:none; border:none; font-size:18px; color:#666; cursor:pointer; padding:4px; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:4px; }
.modal-close:hover { background:#eee; color:#333; }
.modal-icon { width:32px; height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:14px; color:white; flex-shrink:0; background:#555; }
.modal-body { padding:20px; }
.modal-actions { display:flex; justify-content:flex-end; gap:8px; padding:15px 20px; border-top:1px solid #ddd; background:#fafafa; border-radius:0 0 6px 6px; }
.btn-modal { padding:7px 16px; border:none; border-radius:3px; font-size:10px; font-weight:500; cursor:pointer; transition:all .2s; min-width:70px; }
.btn-cancel { background:#f8f9fa; color:#333; border:1px solid #ccc; }
.btn-cancel:hover { background:#e9ecef; }
.btn-primary { background:#555; color:white; border:1px solid #555; }
.btn-primary:hover { background:#444; }

/* Party modal tabs */
.party-type-tabs { display:flex; gap:8px; margin-bottom:15px; padding:5px; background:#f8f9fa; border-radius:6px; border:1px solid #dee2e6; }
.party-type-tab { flex:1; padding:8px 12px; background:transparent; border:none; border-radius:4px; font-size:11px; font-weight:500; color:#6c757d; cursor:pointer; transition:all .2s; text-align:center; }
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
.btn-create-first-party { padding:6px 12px; background:#28a745; color:white; border:none; border-radius:3px; font-size:10px; cursor:pointer; }
.btn-select-party-row { padding:4px 10px; background:#555; color:white; border:none; border-radius:3px; font-size:9px; font-weight:500; cursor:pointer; }

/* Products table */
.products-table-container { max-height:400px; overflow-y:auto; border:1px solid #ddd; border-radius:4px; }
.products-table { width:100%; border-collapse:collapse; font-size:10px; }
.products-table th { background:#f8f9fa; padding:8px 10px; text-align:left; font-weight:600; color:#333; border-bottom:1px solid #ddd; position:sticky; top:0; z-index:1; }
.products-table td { padding:8px 10px; border-bottom:1px solid #eee; vertical-align:middle; }
.products-table tr:hover { background:#f9f9f9; }

/* Loading / no-data */
.loading-state { padding:30px 20px; text-align:center; }
.loading-spinner { width:20px; height:20px; border:2px solid #eee; border-top-color:#555; border-radius:50%; animation:spin 1s linear infinite; margin:0 auto 8px; }
@keyframes spin { to{transform:rotate(360deg)} }
.loading-state p { font-size:10px; color:#666; margin:0; }
.no-data-state { padding:30px 20px; text-align:center; }
.no-data-icon { font-size:24px; opacity:.3; margin-bottom:8px; }
.no-data-state p { font-size:10px; color:#666; margin:0 0 12px; }

/* Form check */
.form-section-small { margin-bottom:15px; padding-bottom:12px; border-bottom:1px solid #eee; }
.form-section-small:last-child { border-bottom:none; margin-bottom:0; padding-bottom:0; }
.form-section-small h5 { font-size:11px; font-weight:600; color:#333; margin:0 0 10px; }
.section-header-small { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.form-check { display:flex; align-items:center; gap:5px; margin-top:6px; }
.form-check-input { width:13px; height:13px; border:1px solid #ccc; border-radius:2px; cursor:pointer; }
.form-check-label { font-size:10px; color:#555; cursor:pointer; }

/* Spinner */
.spinner { display:inline-block; width:12px; height:12px; border:2px solid rgba(255,255,255,.3); border-radius:50%; border-top-color:#fff; animation:spin .8s linear infinite; margin-right:5px; }

@media (max-width:768px) {
    .two-col-row, .invoice-row { flex-direction:column; }
    .col-50 { width:100%; }
    .form-row { flex-direction:column; gap:8px; }
    .form-row .col-6 { width:100%; }
    .address-grid, .customer-info-grid { grid-template-columns:1fr; }
}
</style>
@endpush

@push('scripts')
<script>
// ===================== PURCHASE INVOICE CREATE =====================

let items = [];
let currentPartyType = 'all';
let cgstTotal = 0;
let sgstTotal = 0;
let igstTotal = 0;
let currentTaxType = 'intra';
let isSubmitting = false;
let currentInvoiceType = 'gst';
let allProducts = [];

// ===================== ALERT FUNCTION =====================
function showAlert(message, type = 'success') {
    const container = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `<span>${message}</span>`;
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}

// ===================== INVOICE TYPE FUNCTIONS =====================
function handleInvoiceTypeChange() {
    const invoiceType = $('#invoiceType').val();
    currentInvoiceType = invoiceType;

    if (invoiceType === 'gst') {
        $('#hsnHeader, #taxHeader, #modalTaxHeader').show();
        $('#taxBreakupContainer, #totalTaxRow').show();
        $('.items-table-container').removeClass('cash-mode').addClass('gst-mode');
    } else {
        $('#hsnHeader, #taxHeader, #modalTaxHeader').hide();
        $('#taxBreakupContainer, #totalTaxRow').hide();
        cgstTotal = sgstTotal = igstTotal = 0;
        $('.items-table-container').removeClass('gst-mode').addClass('cash-mode');
    }

    items = [];
    renderItemsTable();
}

// ===================== PARTY MODAL FUNCTIONS =====================
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
    onCreatePartyTypeChange('vendor');
}

function onCreatePartyTypeChange(type) {
    if (type === 'vendor') {
        $('#cpVendorPeField').show();
    } else {
        $('#cpVendorPeField').hide();
    }
}

function loadParties(search = '') {
    const tbody = $('#partiesTableBody');
    const loading = $('#partiesLoading');
    const noData = $('#noParties');

    tbody.empty();
    loading.show();
    noData.hide();

    $.get('{{ route("admin.purchases.parties.list") }}', {
        search: search,
        party_type: currentPartyType !== 'all' ? currentPartyType : null
    }, function(response) {
        loading.hide();

        if (response.parties.length === 0) {
            noData.show();
            return;
        }

        response.parties.forEach(party => {
            const ob = party.opening_balance > 0 ? '₹' + parseFloat(party.opening_balance).toFixed(2) : '—';
            const row = `
                <tr>
                    <td><strong>${party.name}</strong></td>
                    <td><span class="party-type-badge ${party.party_type}">${party.party_type_text}</span></td>
                    <td>${party.phone || '-'}</td>
                    <td>${party.email || '-'}</td>
                    <td>${ob}</td>
                    <td>
                        <button type="button" class="btn-select-party-row" onclick="selectParty('${party.id}', '${party.party_type}')">
                            Select
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }).fail(function() {
        loading.hide();
        showAlert('Failed to load parties', 'error');
    });
}

function selectParty(partyId, partyType) {
    $('#vendorIdInput').val('');
    $('#dealerIdInput').val('');
    $('#distributorIdInput').val('');
    $('#partyTypeInput').val(partyType);

    if (partyType === 'vendor') $('#vendorIdInput').val(partyId);
    else if (partyType === 'dealer') $('#dealerIdInput').val(partyId);
    else $('#distributorIdInput').val(partyId);

    $.get('{{ route("admin.purchases.party-details", ":id") }}'.replace(':id', partyId) + '?type=' + partyType, function(res) {
        if (!res.success) return;

        const p = res.party;
        $('#selectPartyBtn').hide();
        $('#selectedPartyDetails').show();

        $('#partyNameDisplay').text(p.name);
        $('#partyTypeBadge').text(p.party_type_text).attr('class', `party-type-badge ${p.party_type}`);
        $('#partyPhone').text(p.phone || '-');
        $('#partyEmail').text(p.email || '-');
        $('#partyGst').text(p.gst_number || '-');
        $('#partyOpeningBalance').text('₹ ' + parseFloat(p.opening_balance || 0).toFixed(2));

        if (partyType === 'vendor') {
            $('#autoPeSection').show();
            $('#manualPeSection').hide();
            $('#autoPeName').text(p.purchase_executive_name || 'Not Assigned');
            $('#purchaseExecutiveIdInput').val(p.purchase_executive_id || '');
            $('#peDisplay').text(p.purchase_executive_name ? ('👤 ' + p.purchase_executive_name) : '—');
        } else {
            $('#autoPeSection').hide();
            $('#manualPeSection').show();
            $('#manualPeSelect').val('');
            $('#purchaseExecutiveIdInput').val('');
            $('#peDisplay').text('— Select PE —');
        }

        $('#billingAddressText').text(p.billing_address || 'No billing address');
        $('#shippingAddressText').text(p.shipping_address || p.billing_address || 'No shipping address');
        $('#billingAddressInput').val(p.billing_address || '');
        $('#shippingAddressInput').val(p.shipping_address || p.billing_address || '');

        const warehouseState = '{{ $mainWarehouse->state ?? "" }}';
        const partyState = p.billing_state || '';

        if (warehouseState && partyState && warehouseState === partyState) {
            showIntraStateTax();
        } else {
            showInterStateTax();
        }

        items = [];
        renderItemsTable();

        closeSelectPartyModal();
        showAlert('Party selected successfully', 'success');
    });
}

function onPeChange() {
    const val = $('#manualPeSelect').val();
    const name = $('#manualPeSelect option:selected').text();
    $('#purchaseExecutiveIdInput').val(val);
    $('#peDisplay').text(val ? ('👤 ' + name) : '— Select PE —');
}

// ===================== TAX FUNCTIONS =====================
function showIntraStateTax() {
    $('#intraStateTax').show();
    $('#interStateTax').hide();
    currentTaxType = 'intra';
    calculateTaxBreakup();
}

function showInterStateTax() {
    $('#intraStateTax').hide();
    $('#interStateTax').show();
    currentTaxType = 'inter';
    calculateTaxBreakup();
}

function calculateTaxBreakup() {
    if (currentInvoiceType !== 'gst') {
        cgstTotal = sgstTotal = igstTotal = 0;
        $('#cgstTotal, #sgstTotal, #igstTotal, #totalTax').text('0.00');
        return;
    }

    cgstTotal = 0;
    sgstTotal = 0;
    igstTotal = 0;

    items.forEach(item => {
        const purchaseTotal = item.quantity * item.purchase_price;
        const taxPercent = parseFloat(item.tax_percent) || 0;
        const itemTax = (purchaseTotal * taxPercent) / 100;

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

// ===================== ADD ITEM FUNCTIONS =====================
function openAddItemModal() {
    const partyType = $('#partyTypeInput').val();
    if (!partyType) {
        showAlert('Please select a party first', 'error');
        return;
    }
    if (!$('#invoiceType').val()) {
        showAlert('Please select invoice type first', 'error');
        return;
    }
    $('#addItemModal').css('display', 'flex');
    if (allProducts.length === 0) loadProducts();
}

function closeAddItemModal() {
    $('#addItemModal').hide();
    $('#searchProduct').val('');
}

function loadProducts() {
    const tbody = $('#productsTableBody');
    const loading = $('#productsLoading');
    tbody.empty();
    loading.show();

    $.get('{{ route("admin.purchases.get-warehouse-products") }}', function(response) {
        loading.hide();
        allProducts = response.products || [];
        renderProductTable(allProducts);
    }).fail(function() {
        loading.hide();
        showAlert('Failed to load products', 'error');
    });
}

function renderProductTable(products) {
    const tbody = $('#productsTableBody');
    const showTax = currentInvoiceType === 'gst';

    if (products.length === 0) {
        tbody.html('<tr><td colspan="8" style="text-align:center;padding:30px;">No products found</td></tr>');
        return;
    }

    tbody.html(products.map(p => `
        <tr>
            <td>${p.name}</td>
            <td>${p.sku || '—'}</td>
            <td>₹${parseFloat(p.mrp_price).toFixed(2)}</td>
            <td>₹${parseFloat(p.purchase_price).toFixed(2)}</td>
            <td>${p.unit}</td>
            <td ${showTax ? '' : 'style="display:none"'}>${p.tax_percent}%</td>
            <td><input type="number" class="qty-input" min="0.01" step="0.01" value="1" style="width:60px;"></td>
            <td>
                <input type="checkbox" class="select-product"
                    data-id="${p.id}"
                    data-type="${p.product_type}"
                    data-variant="${p.variant_id || ''}"
                    data-name="${p.name}"
                    data-sku="${p.sku || ''}"
                    data-hsn="${p.hsn_code || ''}"
                    data-unit="${p.unit}"
                    data-mrp="${p.mrp_price}"
                    data-purchase="${p.purchase_price}"
                    data-tax="${p.tax_percent}">
            </td>
        </tr>
    `).join(''));
}

function addSelectedProducts() {
    const hasSelected = $('#productsTableBody .select-product:checked').length > 0;
    if (!hasSelected) {
        closeAddItemModal();
        return;
    }

    $('#productsTableBody tr').each(function() {
        const chk = $(this).find('.select-product');
        if (!chk.is(':checked')) return;

        const qty = parseFloat($(this).find('.qty-input').val()) || 1;
        const taxPercent = currentInvoiceType === 'gst' ? parseFloat(chk.data('tax') || 0) : 0;

        const item = {
            product_id: chk.data('id'),
            product_type: chk.data('type'),
            variant_id: chk.data('variant') || null,
            product_name: chk.data('name'),
            sku: chk.data('sku'),
            hsn_sac: currentInvoiceType === 'gst' ? chk.data('hsn') : '',
            unit: chk.data('unit'),
            mrp_price: parseFloat(chk.data('mrp')),
            purchase_price: parseFloat(chk.data('purchase')),
            quantity: qty,
            tax_percent: taxPercent,
        };

        const existingIndex = items.findIndex(i => i.product_id === item.product_id && i.variant_id === item.variant_id);
        if (existingIndex > -1) {
            items[existingIndex].quantity += qty;
        } else {
            items.push(item);
        }
    });

    closeAddItemModal();
    renderItemsTable();
    showAlert('Items added', 'success');
}

function openQuickAddModal() {
    closeAddItemModal();
    $('#quickAddModal').css('display', 'flex');
}

function closeQuickAddModal() {
    $('#quickAddModal').hide();
    $('#quickAddForm')[0].reset();
}

// ===================== ITEMS TABLE FUNCTIONS =====================
function renderItemsTable() {
    const tbody = $('#itemsTableBody');
    const footer = $('#itemsTableFooter');
    const showTax = currentInvoiceType === 'gst';
    const colspan = showTax ? 10 : 8;

    enableExtraFields();

    if (items.length === 0) {
        tbody.html(`
            <tr class="empty-row">
                <td colspan="${colspan}">
                    <div class="empty-items">
                        <div class="empty-icon">📦</div>
                        <p>No items added yet</p>
                        <button type="button" class="btn-add-first-item" onclick="openAddItemModal()">+ Add First Item</button>
                    </div>
                 </td>
             </tr>
        `);
        footer.hide();
        resetSummary();
        return;
    }

    footer.show();
    let ftMRP = 0, ftPurchase = 0, ftTax = 0, ftTotal = 0;

    tbody.html(items.map((item, idx) => {
        const mrpTotal = item.quantity * (item.mrp_price || 0);
        const purchaseTotal = item.quantity * item.purchase_price;
        const taxAmt = showTax ? (purchaseTotal * (item.tax_percent || 0) / 100) : 0;
        const rowTotal = purchaseTotal + taxAmt;

        ftMRP += mrpTotal;
        ftPurchase += purchaseTotal;
        ftTax += taxAmt;
        ftTotal += rowTotal;

        let row = `
            <tr>
                <td class="item-name">${item.product_name}</td>
                <td>${item.sku || '—'}</td>
        `;
        if (showTax) row += `<td>${item.hsn_sac || '—'}</td>`;
        row += `
                <td>${item.unit || 'PCS'}</td>
                <td><input type="number" min="0.01" step="0.01" value="${item.quantity}" onchange="updateItem(${idx}, 'quantity', this.value)" style="width:60px;"></td>
                <td>₹${(item.mrp_price || 0).toFixed(2)}</td>
                <td><input type="number" min="0" step="0.01" value="${item.purchase_price.toFixed(2)}" onchange="updateItem(${idx}, 'purchase_price', this.value)" style="width:80px;"></td>
        `;
        if (showTax) row += `<td>${item.tax_percent || 0}%</td>`;
        row += `
                <td>₹${rowTotal.toFixed(2)}</td>
                <td><button type="button" class="btn-delete" onclick="removeItem(${idx})">✕</button></td>
            </tr>
        `;
        return row;
    }).join(''));

    $('#footerMRP').text('₹' + ftMRP.toFixed(2));
    $('#footerPurchase').text('₹' + ftPurchase.toFixed(2));
    $('#footerTax').text(showTax ? '₹' + ftTax.toFixed(2) : '—');
    $('#footerTotal').text('₹' + ftTotal.toFixed(2));

    calculateTaxBreakup();
    calculateInvoiceSummary(ftMRP, ftPurchase, ftTax);
}

function updateItem(index, field, value) {
    if (items[index]) {
        items[index][field] = parseFloat(value) || 0;
        renderItemsTable();
    }
}

function removeItem(index) {
    items.splice(index, 1);
    renderItemsTable();
}

function resetSummary() {
    $('#totalMRP, #subtotal, #cgstTotal, #sgstTotal, #igstTotal, #totalTax, #grandTotal').text('0.00');
    $('#balanceAmount').text('0.00');
}

function calculateInvoiceSummary(totalMRP, subtotal, totalTax) {
    const discType = $('#extraDiscountTypeInput').val();
    let extraDiscount = 0;

    if (discType === 'percent') {
        const pct = parseFloat($('#extraDiscountPercent').val()) || 0;
        if (pct > 0 && subtotal > 0) extraDiscount = (subtotal * pct) / 100;
    } else {
        extraDiscount = parseFloat($('#extraDiscount').val()) || 0;
    }

    const afterDiscount = subtotal - extraDiscount;
    let grandTotal = afterDiscount + (currentInvoiceType === 'gst' ? totalTax : 0);

    const extraCharge = parseFloat($('#extraCharge').val()) || 0;
    grandTotal += extraCharge;

    if ($('#autoRoundOff').is(':checked')) grandTotal = Math.round(grandTotal);

    $('#totalMRP').text(totalMRP.toFixed(2));
    $('#subtotal').text(subtotal.toFixed(2));
    $('#grandTotal').text(grandTotal.toFixed(2));

    calculateBalance();
}

function calculateTotals() {
    renderItemsTable();
}

function calculateBalance() {
    const grand = parseFloat($('#grandTotal').text()) || 0;
    const paid = parseFloat($('#amountPaid').val()) || 0;
    $('#balanceAmount').text(Math.max(0, grand - paid).toFixed(2));
}

function markFullyPaid() {
    $('#amountPaid').val((parseFloat($('#grandTotal').text()) || 0).toFixed(2));
    calculateBalance();
}

function enableExtraFields() {
    const hasItems = items.length > 0;
    if (hasItems) {
        $('#addDiscountLink, #addDiscountPercentLink, #addChargeLink').removeClass('disabled-link');
    } else {
        $('#addDiscountLink, #addDiscountPercentLink, #addChargeLink').addClass('disabled-link');
        $('#extraDiscountRow, #extraDiscountPercentRow, #extraChargeRow').hide();
        $('#extraDiscount, #extraDiscountPercent, #extraCharge, #chargeName').val('');
        $('#extraDiscountTypeInput').val('amount');
    }
}

function toggleExtraDiscount() {
    if (items.length === 0) { showAlert('Add items first', 'error'); return; }
    $('#extraDiscountPercentRow').hide();
    $('#extraDiscountPercent').val('');
    $('#extraDiscountRow').toggle();
    if ($('#extraDiscountRow').is(':visible')) {
        $('#extraDiscountTypeInput').val('amount');
        $('#extraDiscount').focus();
    } else {
        $('#extraDiscount').val('');
    }
    calculateTotals();
}

function toggleExtraDiscountPercent() {
    if (items.length === 0) { showAlert('Add items first', 'error'); return; }
    $('#extraDiscountRow').hide();
    $('#extraDiscount').val('');
    $('#extraDiscountPercentRow').toggle();
    if ($('#extraDiscountPercentRow').is(':visible')) {
        $('#extraDiscountTypeInput').val('percent');
        $('#extraDiscountPercent').focus();
    } else {
        $('#extraDiscountPercent').val('');
    }
    calculateTotals();
}

function toggleExtraCharge() {
    if (items.length === 0) { showAlert('Add items first', 'error'); return; }
    $('#extraChargeRow').toggle();
    if (!$('#extraChargeRow').is(':visible')) {
        $('#extraCharge').val('');
        $('#chargeName').val('');
        calculateTotals();
    }
}

function updateDueDateFromTerms() {
    const invDate = $('#invoiceDate').val();
    const days = parseInt($('#paymentTermsDays').val()) || 0;
    if (!invDate || days <= 0) return;
    const d = new Date(invDate);
    d.setDate(d.getDate() + days);
    $('#dueDate').val(d.toISOString().split('T')[0]);
    $('#paymentTermsInput').val('Due in ' + days + ' days');
}

function updatePaymentTermsFromDueDate() {
    const invDate = $('#invoiceDate').val();
    const dueDate = $('#dueDate').val();
    if (!invDate || !dueDate) return;
    const diff = Math.ceil((new Date(dueDate) - new Date(invDate)) / 86400000);
    $('#paymentTermsDays').val(diff);
    $('#paymentTermsInput').val('Due in ' + diff + ' days');
}

function validateForm() {
    if (!$('#partyTypeInput').val()) {
        showAlert('Please select a party', 'error');
        return false;
    }
    if (!$('#invoiceType').val()) {
        showAlert('Please select invoice type', 'error');
        return false;
    }
    if (items.length === 0) {
        showAlert('Please add at least one item', 'error');
        return false;
    }
    return true;
}

// ===================== DOCUMENT READY =====================
$(document).ready(function() {
    // Initialize with GST Invoice selected
    $('#invoiceType').val('gst');
    currentInvoiceType = 'gst';
    handleInvoiceTypeChange();
    showIntraStateTax();
    enableExtraFields();
    onCreatePartyTypeChange('vendor');

    // Load initial parties
    loadParties();

    // Event handlers
    $('#extraDiscount').on('input', calculateTotals);
    $('#extraDiscountPercent').on('input', calculateTotals);
    $('#extraCharge').on('input', calculateTotals);
    $('#amountPaid').on('input', calculateBalance);
    $('#autoRoundOff').on('change', calculateTotals);
    $('#invoiceDate').on('change', updateDueDateFromTerms);
    $('#paymentTermsDays').on('change', updateDueDateFromTerms);
    $('#dueDate').on('change', updatePaymentTermsFromDueDate);

    // Modal close handlers
    $('.modal-overlay').on('click', function() {
        $(this).closest('.modal').hide();
    });

    // Escape key handler
    $(document).on('keydown', function(event) {
        if (event.key === 'Escape') {
            $('.modal').hide();
        }
    });

    // Party type tabs
    $('.party-type-tab').on('click', function() {
        $('.party-type-tab').removeClass('active');
        $(this).addClass('active');
        currentPartyType = $(this).data('type');
        loadParties();
    });

    // Search party
    $('#searchParty').on('input', function() {
        loadParties($(this).val());
    });

    // Search product
    $('#searchProduct').on('input', function() {
        const s = $(this).val().toLowerCase();
        const filtered = allProducts.filter(p => p.name.toLowerCase().includes(s) || (p.sku || '').toLowerCase().includes(s));
        renderProductTable(filtered);
    });

    // Same billing/shipping
    $('#sameBillingShipping').on('change', function() {
        if (this.checked) {
            const f = document.getElementById('createPartyForm');
            ['address', 'city', 'state', 'pincode', 'country'].forEach(function(field) {
                const src = f.querySelector(`[name="billing_${field}"]`);
                const dst = f.querySelector(`[name="shipping_${field}"]`);
                if (src && dst) dst.value = src.value;
            });
        }
    });

    // Create party form submit
    $('#createPartyForm').on('submit', function(e) {
        e.preventDefault();

        const phone = $('input[name="phone"]').val();
        if (!/^[6-9]\d{9}$/.test(phone)) {
            showAlert('Please enter a valid 10-digit phone number', 'error');
            return;
        }

        const formData = $(this).serializeArray();
        formData.push({ name: 'same_billing_shipping', value: $('#sameBillingShipping').is(':checked') ? '1' : '0' });

        $.ajax({
            url: '{{ route("admin.purchases.create-party") }}',
            type: 'POST',
            data: $.param(formData),
            success: function(response) {
                if (response.success) {
                    showAlert('Party created successfully!', 'success');
                    closeCreatePartyModal();
                    selectParty(response.party_id, response.party_type);
                } else {
                    showAlert('Error: ' + response.message, 'error');
                }
            },
            error: function(xhr) {
                showAlert(xhr.responseJSON?.message || 'Failed to create party', 'error');
            }
        });
    });

    // Quick add form submit
    $('#quickAddForm').on('submit', function(e) {
        e.preventDefault();
        const submitBtn = $(this).find('[type=submit]');
        submitBtn.prop('disabled', true).text('Creating...');
        const fd = new FormData(this);

        $.ajax({
            url: '{{ route("admin.purchases.quick-add-product") }}',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    const p = res.product;
                    allProducts.push(p);
                    items.push({
                        product_id: p.id,
                        product_type: 'simple',
                        variant_id: null,
                        product_name: p.name,
                        sku: p.sku,
                        hsn_sac: currentInvoiceType === 'gst' ? p.hsn_code : '',
                        unit: p.unit,
                        mrp_price: p.mrp_price,
                        purchase_price: p.purchase_price,
                        quantity: 1,
                        tax_percent: currentInvoiceType === 'gst' ? p.tax_percent : 0,
                    });
                    closeQuickAddModal();
                    renderItemsTable();
                    showAlert(p.name + ' created and added!', 'success');
                } else {
                    showAlert(res.message, 'error');
                    submitBtn.prop('disabled', false).text('Create & Add');
                }
            },
            error: function(xhr) {
                showAlert(xhr.responseJSON?.message || 'Failed to create', 'error');
                submitBtn.prop('disabled', false).text('Create & Add');
            }
        });
    });

    // Purchase invoice form submit
    $('#purchaseInvoiceForm').on('submit', function(e) {
        e.preventDefault();

        if (isSubmitting) {
            showAlert('Please wait, invoice is being created...', 'info');
            return;
        }

        if (!validateForm()) return;

        const partyType = $('#partyTypeInput').val();
        if ((partyType === 'dealer' || partyType === 'distributor') && !$('#purchaseExecutiveIdInput').val()) {
            showAlert('Please select a Purchase Executive for this ' + partyType, 'error');
            return;
        }

        isSubmitting = true;
        const $submitBtn = $('.btn-submit-invoice');
        const originalText = $submitBtn.html();

        $submitBtn.prop('disabled', true);
        $submitBtn.html('<span class="spinner"></span> Creating...');

        const formData = new FormData();

        $(this).serializeArray().forEach(item => {
            formData.append(item.name, item.value);
        });

        formData.append('items', JSON.stringify(items));
        formData.append('grand_total', $('#grandTotal').text());
        formData.append('auto_round_off', $('#autoRoundOff').is(':checked') ? 1 : 0);

        const discType = $('#extraDiscountTypeInput').val();
        formData.append('extra_discount_type', discType);
        formData.append('extra_discount', discType === 'percent' ? ($('#extraDiscountPercent').val() || 0) : ($('#extraDiscount').val() || 0));
        formData.append('extra_charge', $('#extraCharge').val() || 0);
        formData.append('charge_name', $('#chargeName').val() || '');

        showAlert('Creating invoice...', 'info');

        $.ajax({
            url: '{{ route("admin.purchases.store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('Invoice created successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = '/admin/purchases/' + response.invoice_id;
                    }, 1500);
                } else {
                    showAlert('Error: ' + response.message, 'error');
                    isSubmitting = false;
                    $submitBtn.prop('disabled', false);
                    $submitBtn.html(originalText);
                }
            },
            error: function(xhr) {
                let message = 'Failed to create invoice';
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
});
</script>
@endpush
@endsection
