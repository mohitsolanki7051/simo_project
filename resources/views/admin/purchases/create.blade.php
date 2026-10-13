@extends('layouts.admin')

@section('title', 'Create Purchase Invoice - Admin Panel')
@section('header-title', 'Create Purchase Invoice')

@section('content')
<div class="products-container">
    <div id="alertContainer"></div>

    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Create Purchase Invoice</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.purchases.index') }}" class="btn-small btn-secondary">← Back to Purchases</a>
        </div>
    </div>

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
            <input type="hidden" name="gst_mode"              id="gstModeInput" value="exclusive">

            <div class="form-row">
                <div class="form-col-main">
                    <div class="two-col-row">

                        <!-- LEFT: Party -->
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
                                        <span class="btn-icon">🏢</span> Select Party
                                    </button>
                                </div>
                                <div class="section-body">
                                    <div id="selectedPartyDetails" class="selected-customer-details" style="display:none;">
                                        <div class="customer-header">
                                            <h4 id="partyNameDisplay">Party Name</h4>
                                            <span id="partyTypeBadge" class="party-type-badge"></span>
                                            <button type="button" class="btn-change-customer" onclick="openSelectPartyModal()">Change</button>
                                        </div>
                                        <div class="customer-info-grid">
                                            <div class="info-column">
                                                <div class="info-row"><span class="info-label">Phone:</span><span id="partyPhone" class="info-value">-</span></div>
                                                <div class="info-row"><span class="info-label">Email:</span><span id="partyEmail" class="info-value">-</span></div>
                                                <div class="info-row"><span class="info-label">GST:</span><span id="partyGst" class="info-value">-</span></div>
                                            </div>
                                            <div class="info-column">
                                                <div class="info-row"><span class="info-label">Opening Bal:</span><span id="partyOpeningBalance" class="info-value">-</span></div>
                                            </div>
                                        </div>
                                        <div class="address-section">
                                            <div class="address-header"><h5>Address Details</h5></div>
                                            <div class="address-grid">
                                                <div class="address-card">
                                                    <div class="address-card-header"><span class="address-type">Billing Address</span></div>
                                                    <div class="address-content"><p id="billingAddressText">Select a party to view address</p></div>
                                                </div>
                                                <div class="address-card">
                                                    <div class="address-card-header"><span class="address-type">Shipping Address</span></div>
                                                    <div class="address-content"><p id="shippingAddressText">Select a party to view address</p></div>
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
                                            <small style="font-size:10px;margin-top:3px;color:#666;">GST Invoice includes HSN code and tax calculations</small>
                                        </div>
                                        <!-- GST MODE TOGGLE -->
                                        <div class="form-group col-6" id="gstModeGroup">
                                            <label class="form-label">GST Mode</label>
                                            <div class="gst-mode-toggle">
                                                <button type="button" class="gst-mode-btn active" data-mode="exclusive" onclick="setGstMode('exclusive')">
                                                    <span>📤</span>
                                                    <span class="gst-mode-label">Exclusive</span>
                                                    <span class="gst-mode-sub">GST added on top</span>
                                                </button>
                                                <button type="button" class="gst-mode-btn" data-mode="inclusive" onclick="setGstMode('inclusive')">
                                                    <span>📥</span>
                                                    <span class="gst-mode-label">Inclusive</span>
                                                    <span class="gst-mode-sub">GST within price</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label">Warehouse</label>
                                            <input type="text" class="form-control" value="{{ ($mainWarehouse->name ?? 'Main Warehouse') . ' (Main)' }}" readonly style="background:#f0f8ff;color:#0066cc;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ITEMS -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-header-left">
                                <div class="section-icon">🛒</div>
                                <div class="section-title"><h3>Items</h3><p>Add products to purchase invoice</p></div>
                            </div>
                            <button type="button" class="btn-add-item" id="addItemBtn" onclick="openAddItemModal()">+ Add Item</button>
                        </div>
                        <div class="section-body">
                            <div class="items-table-container gst-mode" id="itemsTableContainer">
                                <table class="items-table" id="mainItemsTable">
                                    <thead>
                                        <tr>
                                            <th class="th-item">Item</th>
                                            <th class="th-sku">SKU</th>
                                            <th class="th-hsn col-hsn" id="hsnHeader">HSN/SAC</th>
                                            <th class="th-unit">Unit</th>
                                            <th class="th-qtys">Qty</th>
                                            <th class="th-mrp">MRP (₹)</th>
                                            <th class="th-sale-price" id="purchasePriceHeader">Purchase Price (₹)</th>
                                            <th class="th-tax col-tax" id="taxHeader">Tax %</th>
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
                                                    <button type="button" class="btn-add-first-item" onclick="openAddItemModal()">+ Add First Item</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot id="itemsTableFooter" style="display:none;background:#f0f0f0;font-weight:600;border-top:2px solid #333;">
                                        <tr>
                                            <td colspan="5" style="text-align:right;padding:10px;font-size:12px;"><strong>SUBTOTAL:</strong></td>
                                            <td style="padding:10px;text-align:center;font-size:12px;" id="footerMRP">₹0.00</td>
                                            <td style="padding:10px;text-align:center;font-size:12px;" id="footerPurchase">₹0.00</td>
                                            <td class="col-tax" style="padding:10px;text-align:center;font-size:12px;" id="footerTax">₹0.00</td>
                                            <td style="padding:10px;text-align:center;font-size:12px;" id="footerTotal">₹0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="mobile-items-container" id="mobileItemsContainer"></div>
                        </div>
                    </div>

                    <!-- SUMMARY + PAYMENT -->
                    <div class="form-col-sidebar">
                        <div class="invoice-bottom-layout">
                            <div class="invoice-row">
                                <div class="invoice-col">
                                    <div class="summary-section">
                                        <div class="summary-header"><div class="summary-icon">💰</div><h3>Invoice Summary</h3></div>
                                        <div class="summary-body">
                                            <div class="summary-row"><span class="summary-label">Total MRP</span><span class="summary-value">₹ <span id="totalMRP">0.00</span></span></div>
                                            <div class="summary-divider"></div>
                                            <div class="summary-row"><span class="summary-label" style="font-weight:600;">Subtotal</span><span class="summary-value">₹ <span id="subtotal">0.00</span></span></div>

                                            <div id="taxBreakupContainer" style="display:none;">
                                                <div id="intraStateTax" style="display:none;">
                                                    <div class="summary-row"><span class="summary-label">CGST</span><span class="summary-value">+ ₹ <span id="cgstTotal">0.00</span></span></div>
                                                    <div class="summary-row"><span class="summary-label">SGST</span><span class="summary-value">+ ₹ <span id="sgstTotal">0.00</span></span></div>
                                                </div>
                                                <div id="interStateTax" style="display:none;">
                                                    <div class="summary-row"><span class="summary-label">IGST</span><span class="summary-value">+ ₹ <span id="igstTotal">0.00</span></span></div>
                                                </div>
                                            </div>
                                            <div class="summary-row" id="totalTaxRow" style="border-top:1px dashed #ddd;padding-top:5px;display:none;">
                                                <span class="summary-label">Total Tax</span><span class="summary-value">+ ₹ <span id="totalTax">0.00</span></span>
                                            </div>
                                            <div class="summary-divider"></div>

                                            <div class="summary-row"><span class="summary-label"><a href="javascript:void(0)" id="addDiscountLink" onclick="toggleExtraDiscount()">+ Add Discount</a></span></div>
                                            <div id="extraDiscountRow" style="display:none;" class="summary-row">
                                                <input type="number" id="extraDiscount" placeholder="Enter discount amount" step="0.01" oninput="calculateTotals()" class="summary-input">
                                            </div>
                                            <div class="summary-row"><span class="summary-label"><a href="javascript:void(0)" id="addDiscountPercentLink" onclick="toggleExtraDiscountPercent()">+ Add Discount %</a></span></div>
                                            <div id="extraDiscountPercentRow" style="display:none;" class="summary-row">
                                                <input type="number" id="extraDiscountPercent" placeholder="Enter discount %" step="0.01" oninput="calculateTotals()" class="summary-input">
                                            </div>
                                            <div class="summary-row"><span class="summary-label"><a href="javascript:void(0)" id="addChargeLink" onclick="toggleExtraCharge()">+ Add Another Charge</a></span></div>
                                            <div id="extraChargeRow" style="display:none;" class="summary-row">
                                                <input type="text" placeholder="Charge Name" id="chargeName" class="summary-input">
                                                <input type="number" placeholder="₹" id="extraCharge" oninput="calculateTotals()" class="summary-input-small">
                                            </div>
                                            <div class="summary-row">
                                                <label><input type="checkbox" id="autoRoundOff" onchange="calculateTotals()"> Auto Round Off</label>
                                            </div>
                                            <div class="summary-row total-row">
                                                <span class="summary-label">Grand Total</span>
                                                <span class="summary-value">₹ <span id="grandTotal">0.00</span></span>
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
                                                <option value="cash">Cash</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="cheque">Cheque</option>
                                                <option value="upi">UPI</option>
                                                <option value="credit">Credit</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Amount Paid</label>
                                            <input type="number" name="amount_paid" id="amountPaid" class="form-control" step="0.01" min="0" oninput="validateAndCalculateBalance()" onblur="clampAmountPaid()">
                                        </div>
                                        <div class="form-group">
                                            <button type="button" onclick="markFullyPaid()" class="btn-mark-paid" style="width:auto;padding:4px 8px;font-size:10px;">Mark as Fully Paid</button>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Balance Amount</label>
                                            <div class="balance-amount">₹ <span id="balanceAmount">0.00</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row" style="margin-top:10px;">
                        <div class="form-group col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes or remarks..."></textarea>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;">
                        <button type="submit" class="btn-submit-invoice" id="submitBtn">
                            <span class="btn-icon">💾</span> Save Purchase Invoice
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- Mobile Sticky Bottom Bar (MyBillBook style) -->
    <div class="mobile-sticky-bottom-bar">
        <div class="mobile-sticky-total">
            <span class="mobile-sticky-label">Grand Total</span>
            <span class="mobile-sticky-val">₹ <span id="mobileStickyGrandTotal">0.00</span></span>
        </div>
        <button type="button" class="btn-mobile-save" onclick="$('#purchaseInvoiceForm').submit()">
            💾 Save Invoice
        </button>
    </div>
</div>

{{-- MODALS same as original - copy from your existing create.blade.php --}}
{{-- SELECT PARTY MODAL --}}
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
                            <th class="th-email">Email</th><th class="th-status">Opening Bal.</th><th class="th-action">Action</th>
                        </tr>
                    </thead>
                    <tbody id="partiesTableBody"></tbody>
                </table>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeSelectPartyModal()">Cancel</button>
        </div>
    </div>
</div>

{{-- CREATE PARTY MODAL --}}
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
                    <div id="cpVendorPeField" class="form-row">
                        <div class="form-group" style="width:100%">
                            <label class="form-label">Assign Purchase Executive <small style="color:#92400e;">(Vendor only)</small></label>
                            <select name="purchase_executive_id" class="form-control">
                                <option value="">— None —</option>
                                @foreach($purchaseExecutives as $pe)
                                    <option value="{{ $pe->_id }}">{{ $pe->name }}</option>
                                @endforeach
                            </select>
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
                        <label class="form-label required">Address</label>
                        <input type="text" name="billing_address" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6"><label class="form-label required">City</label><input type="text" name="billing_city" class="form-control" required></div>
                        <div class="form-group col-6"><label class="form-label required">State</label><input type="text" name="billing_state" class="form-control" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6"><label class="form-label required">Pincode</label><input type="text" name="billing_pincode" class="form-control" maxlength="6" required></div>
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
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes about this party..."></textarea>
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

{{-- ADD ITEM MODAL --}}
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
                <button type="button" onclick="openProductPageInNewTab()" style="padding:7px 14px;background:#555;color:white;border:none;border-radius:3px;font-size:10px;font-weight:500;cursor:pointer;white-space:nowrap;">
                    ➕ New Product
                </button>
            </div>
            <div class="products-table-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Item Name</th><th>SKU</th><th>MRP (₹)</th>
                            <th id="modalPurchasePriceHeader">Purchase Price (₹)</th><th>Unit</th>
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

{{-- QUICK ADD PRODUCT MODAL --}}
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
                    <div class="form-group col-6"><label class="form-label required">Product Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group col-6"><label class="form-label required">SKU Code</label><input type="text" name="sku_code" class="form-control" maxlength="16" required></div>
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
                    <div class="form-group col-6"><label class="form-label required">Purchase Price (₹)</label><input type="number" name="cost_price" class="form-control" step="0.01" min="0" required></div>
                    <div class="form-group col-6"><label class="form-label required">MRP (₹)</label><input type="number" name="mrp_price" class="form-control" step="0.01" min="0" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group col-6"><label class="form-label required">HSN Code</label><input type="text" name="hsn_code" class="form-control" maxlength="8" required></div>
                    <div class="form-group col-6"><label class="form-label required">GST %</label><input type="number" name="gst" class="form-control" step="0.01" min="0" max="100" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group col-6"><label class="form-label required">Opening Stock</label><input type="number" name="opening_stock" class="form-control" min="0" value="0" required></div>
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

/* ── CREDIT LIMIT & DEBIT BADGES ── */
.credit-limit-badge { display:inline-block; padding:2px 6px; border-radius:10px; font-size:8px; font-weight:600; margin-left:5px; vertical-align:middle; }
.credit-limit-badge.warning { background:#fff3cd; color:#856404; border:1px solid #ffeeba; }
.credit-limit-badge.danger  { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.credit-limit-badge.info    { background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; }
.available-credit { display:block; font-size:9px; color:#28a745; margin-top:2px; font-weight:normal; }

/* ── DISABLED LINKS ── */
.disabled-link { pointer-events:none !important; opacity:.5 !important; cursor:not-allowed !important; background-color:#e0e0e0 !important; color:#999 !important; border-color:#ccc !important; }
.summary-label a.disabled-link { background:#e0e0e0 !important; color:#999 !important; border:1px dashed #ccc !important; }

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
.salesman-display { padding:6px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; font-size:10px; color:#333; display:flex; align-items:center; gap:5px; }
.party-type-badge { display:inline-block; padding:2px 8px; border-radius:12px; font-size:9px; font-weight:600; text-transform:uppercase; background:#e7f1ff; color:#0066cc; margin-left:8px; }
.party-type-badge.customer { background:#d4edda; color:#155724; }
.party-type-badge.vendor { background:#e0f2fe; color:#0369a1; }
.party-type-badge.dealer { background:#fef3c7; color:#856404; }
.party-type-badge.distributor { background:#d1fae5; color:#065f46; }
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
.items-table-container.gst-mode .items-table { min-width:900px; }
.items-table-container.cash-mode .items-table { min-width:unset; width:100%; table-layout:fixed; }
.items-table-container.cash-mode .col-hsn,
.items-table-container.cash-mode .col-tax { display:none; }
.items-table-container.cash-mode .th-item { width:35%; }
.items-table-container.cash-mode .th-sku  { width:15%; }
.items-table-container.cash-mode .th-unit { width:8%; }
.items-table-container.cash-mode .th-qtys { width:10%; }
.items-table-container.cash-mode .th-mrp  { width:12%; }
.items-table-container.cash-mode .th-sale-price { width:12%; }
.items-table-container.cash-mode .th-amount     { width:12%; }
.items-table-container.cash-mode .th-action     { width:8%; }
.items-table { width:100%; border-collapse:collapse; font-size:10px; }
.items-table th { background:#f8f9fa; padding:10px 8px; text-align:center; font-weight:600; color:#333; border-bottom:2px solid #ddd; white-space:nowrap; font-size:11px; }
.items-table td { padding:8px; border-bottom:1px solid #eee; vertical-align:middle; text-align:center; font-size:10px; }
.items-table td.item-name { text-align:left; font-weight:500; }
.items-table tr:hover { background:#f9f9f9; }
.items-table input[type="number"] { width:100%; padding:4px 6px; border:1px solid #ccc; border-radius:2px; font-size:10px; text-align:center; }
.th-item { min-width:140px; text-align:left !important; }
.th-sku,.th-hsn { width:80px; }
.th-qtys,.th-tax { width:65px; }
.th-unit { width:60px; }
.th-sale-price { width:110px; }
.th-mrp { width:85px; }
.th-amount { width:90px; }
.th-action { width:60px; }
.btn-delete { width:28px; height:28px; border:none; background:#fee; color:#c33; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; margin:0 auto; font-size:14px; }
.btn-delete:hover { background:#fcc; color:#a00; }
.empty-row td { padding:30px 20px; text-align:center; }
.empty-items { display:flex; flex-direction:column; align-items:center; gap:8px; }
.empty-icon { font-size:24px; opacity:.3; margin-bottom:5px; }
.empty-items p { font-size:10px; color:#666; margin:0; }
.btn-add-first-item { padding:6px 12px; background:#555; color:white; border:none; border-radius:3px; font-size:10px; cursor:pointer; }

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
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:20000; align-items:center; justify-content:center; }
.modal-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.4); }
.modal-content { position:relative; background:white; border-radius:6px; width:90%; max-width:800px; max-height:90vh; overflow-y:auto; animation:modalFadeIn .2s ease; box-shadow:0 4px 20px rgba(0,0,0,.15); }
.modal-lg,.modal-xl { max-width:800px; }
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
    font-size: 18px;
    cursor: pointer;
    color: #dc3545;
    padding: 6px 10px;
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
    .th-code, .th-stock, .product-code-col, .product-stock-col, .products-table-container td.col-tax {
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
    .products-table-container table, 
    .products-table-container thead, 
    .products-table-container tbody, 
    .products-table-container tr, 
    .products-table-container td {
        display: block !important;
    }
    .products-table-container thead {
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
        content: "Cost: " !important;
        color: #555 !important;
        font-weight: 400 !important;
    }
    
    /* 4. Bottom row: Qty Selector (left/center) and checkbox (top right) */
    .products-table-container td.product-qty-col {
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
        display: none !important;
    }
    #productsTableBody .product-qty-selector {
        display: flex !important;
        align-items: center !important;
        border: 1px solid #ccc !important;
        border-radius: 4px !important;
        overflow: hidden !important;
        height: 28px !important;
        width: 96px !important;
        background: #fff !important;
        margin: 0 !important;
    }
    #productsTableBody .prod-qty-btn {
        width: 30px !important;
        height: 100% !important;
        font-size: 14px !important;
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
</style>
@endpush

@push('scripts')
<script>
// ===================== GLOBALS =====================
let items            = [];
let currentPartyTab  = 'all';
let cgstTotal        = 0;
let sgstTotal        = 0;
let igstTotal        = 0;
let currentTaxType   = 'intra';
let isSubmitting     = false;
let currentInvType   = 'gst';
let currentGstMode   = 'exclusive';   // NEW
let allProducts      = [];
let partiesXhr       = null;
let searchTimer      = null;

// ===================== ALERTS =====================
function showAlert(message, type = 'success') {
    const c = document.getElementById('alertContainer');
    const d = document.createElement('div');
    d.className = `alert alert-${type}`;
    d.innerHTML = `<span>${message}</span>`;
    c.appendChild(d);
    setTimeout(() => d.remove(), 5000);
}

function openProductPageInNewTab() {
    localStorage.removeItem('product_created');
    localStorage.setItem('opened_from_purchase', 'true');
    window.open('{{ route("admin.products.index") }}?open_modal=1', '_blank');
}

// ===================== GST MODE =====================
function setGstMode(mode) {
    if (items.length > 0) {
        if (!confirm('Changing GST mode will clear all current items. Continue?')) return;
        items = [];
    }
    currentGstMode = mode;
    $('#gstModeInput').val(mode);
    $('.gst-mode-btn').removeClass('active');
    $(`.gst-mode-btn[data-mode="${mode}"]`).addClass('active');
    // Update column header
    const label = (currentInvType === 'gst' && mode === 'inclusive')
        ? 'Purchase Price (₹) [Incl. GST]'
        : 'Purchase Price (₹) [Excl. GST]';
    $('#purchasePriceHeader').text(label);
    $('#modalPurchasePriceHeader').text(label);
    renderItemsTable();
}

// ===================== INVOICE TYPE =====================
function handleInvoiceTypeChange() {
    currentInvType = $('#invoiceType').val();
    $.get('{{ route('admin.purchases.get-next-invoice-number') }}', { invoice_type: currentInvType }, function(res) {
        if (res.invoice_number) {
            $('input[name="invoice_number"]').val(res.invoice_number);
            $('#invoiceNumberDisplay').val(res.invoice_number);
        }
    });
    if (currentInvType === 'gst') {
        $('#taxBreakupContainer, #totalTaxRow').show();
        $('#modalTaxHeader').show();
        $('.items-table-container').removeClass('cash-mode').addClass('gst-mode');
        $('#gstModeGroup').show();
    } else {
        $('#taxBreakupContainer, #totalTaxRow').hide();
        $('#modalTaxHeader').hide();
        $('.items-table-container').removeClass('gst-mode').addClass('cash-mode');
        $('#gstModeGroup').hide();
        cgstTotal = sgstTotal = igstTotal = 0;
    }
    items = [];
    renderItemsTable();
}

// ===================== PARTY MODAL =====================
function openSelectPartyModal() {
    $('#selectPartyModal').css('display', 'flex');
    $('#searchParty').val('');
    loadParties();
}
function closeSelectPartyModal() {
    $('#selectPartyModal').hide();
    if (partiesXhr) { partiesXhr.abort(); partiesXhr = null; }
    $('#searchParty').val('');
}
function openCreatePartyModal() { closeSelectPartyModal(); $('#createPartyModal').css('display', 'flex'); }
function closeCreatePartyModal() {
    $('#createPartyModal').hide();
    $('#createPartyForm')[0].reset();
    $('#sameBillingShipping').prop('checked', false);
    onCreatePartyTypeChange('vendor');
}
function onCreatePartyTypeChange(type) { $('#cpVendorPeField').toggle(type === 'vendor'); }

function loadParties() {
    const search = $('#searchParty').val().trim();
    if (partiesXhr) { partiesXhr.abort(); partiesXhr = null; }
    const tbody = $('#partiesTableBody');
    tbody.html(`<tr><td colspan="6" class="loading-state" style="text-align:center;padding:30px;"><div class="loading-spinner"></div><p>Loading...</p></td></tr>`);
    partiesXhr = $.get('{{ route("admin.purchases.parties.list") }}', {
        search: search, party_type: currentPartyTab !== 'all' ? currentPartyTab : null
    }).done(function(response) {
        partiesXhr = null;
        if (!response.parties || response.parties.length === 0) {
            tbody.html(`<tr><td colspan="6" class="no-data-state" style="text-align:center;padding:30px;"><div class="no-data-icon">👤</div><p>${search ? 'No parties found for "' + $('<div>').text(search).html() + '"' : 'No parties found'}</p><button type="button" class="btn-create-first-party" onclick="openCreatePartyModal()">+ Create New Party</button></td></tr>`);
            return;
        }
        tbody.html(response.parties.map(function(p) {
            const ob = p.opening_balance !== undefined ? (p.opening_balance > 0 ? '₹' + parseFloat(p.opening_balance).toFixed(2) : '—') : '—';
            return `<tr onclick="selectParty('${p.id}','${p.party_type}')" style="cursor: pointer;"><td><strong>${p.name}</strong></td><td><span class="party-type-badge ${p.party_type}">${p.party_type_text}</span></td><td>${p.phone || '-'}</td><td class="party-email">${p.email || '-'}</td><td class="party-status-col">${ob}</td><td class="party-action-col"><button type="button" class="btn-select-party-row" onclick="event.stopPropagation(); selectParty('${p.id}','${p.party_type}')">Select</button></td></tr>`;
        }).join(''));
    }).fail(function(xhr) {
        if (xhr.statusText === 'abort') return;
        partiesXhr = null;
        tbody.html(`<tr><td colspan="6" style="text-align:center;padding:20px;color:#c33;">Failed to load parties. Please try again.</td></tr>`);
    });
}

function selectParty(partyId, partyType) {
    $('#vendorIdInput').val(''); $('#dealerIdInput').val(''); $('#distributorIdInput').val('');
    $('#partyTypeInput').val(partyType);
    if      (partyType === 'vendor')      $('#vendorIdInput').val(partyId);
    else if (partyType === 'dealer')      $('#dealerIdInput').val(partyId);
    else                                  $('#distributorIdInput').val(partyId);

    $.get('{{ route("admin.purchases.party-details", ":id") }}'.replace(':id', partyId) + '?type=' + partyType, function(res) {
        if (!res.success) { showAlert('Failed to load party details', 'error'); return; }
        const p = res.party;
        $('#selectPartyBtn').hide();
        $('#selectedPartyDetails').show();
        $('#partyNameDisplay').text(p.name);
        $('#partyTypeBadge').text(p.party_type_text).attr('class', 'party-type-badge ' + p.party_type);
        $('#partyPhone').text(p.phone || '-');
        $('#partyEmail').text(p.email || '-');
        $('#partyGst').text(p.gst_number || '-');

        if (partyType === 'vendor') {
            $('#partyOpeningBalance').text('₹ ' + parseFloat(p.opening_balance || 0).toFixed(2));
            $('#partyOpeningBalance').closest('.info-row').show();
            $('#purchaseExecutiveIdInput').val(p.purchase_executive_id || '');
            $('#peDisplay').text(p.purchase_executive_name ? '👤 ' + p.purchase_executive_name : '—');
        } else {
            $('#partyOpeningBalance').closest('.info-row').hide();
            $('#purchaseExecutiveIdInput').val('');
            $('#peDisplay').text('—');
        }

        $('#debitNoteRemainingRow').remove();
        const debitRemaining = parseFloat(p.debit_notes_remaining || 0);
        if (debitRemaining > 0) {
            $('.info-column').first().append(`<div class="info-row" id="debitNoteRemainingRow"><span class="info-label">DN Balance:</span><span class="info-value" style="color:#047857;font-weight:600;">₹ ${debitRemaining.toFixed(2)} <small style="background:#d1fae5;color:#065f46;padding:1px 5px;border-radius:3px;font-size:9px;margin-left:4px;">WILL AUTO-ADJUST</small></span></div>`);
        }

        $('#billingAddressText').text(p.billing_address || 'No billing address');
        $('#shippingAddressText').text(p.shipping_address || p.billing_address || 'No shipping address');
        $('#billingAddressInput').val(p.billing_address || '');
        $('#shippingAddressInput').val(p.shipping_address || p.billing_address || '');

        const wState = '{{ $mainWarehouse->state ?? "" }}'.trim().toLowerCase();
        const pState = (p.billing_state || '').trim().toLowerCase();
        if (wState && pState && wState === pState) { showIntraStateTax(); } else { showInterStateTax(); }

        items = [];
        renderItemsTable();
        closeSelectPartyModal();
        showAlert('Party selected: ' + p.name, 'success');
        calculateBalance();
    });
}

// ===================== TAX FUNCTIONS =====================
function showIntraStateTax() { $('#intraStateTax').show(); $('#interStateTax').hide(); currentTaxType = 'intra'; calculateTaxBreakup(); }
function showInterStateTax() { $('#intraStateTax').hide(); $('#interStateTax').show(); currentTaxType = 'inter'; calculateTaxBreakup(); }

function calculateTaxBreakup() {
    if (currentInvType !== 'gst') { cgstTotal = sgstTotal = igstTotal = 0; $('#cgstTotal,#sgstTotal,#igstTotal,#totalTax').text('0.00'); return; }
    cgstTotal = sgstTotal = igstTotal = 0;
    items.forEach(function(item) {
        const taxPercent = parseFloat(item.tax_percent) || 0;
        const isIncl     = item.gst_inclusive;
        let tax;
        if (isIncl && taxPercent > 0) {
            const gross = item.quantity * item.purchase_price;
            tax = gross - (gross * 100 / (100 + taxPercent));
        } else {
            tax = (item.quantity * item.purchase_price * taxPercent) / 100;
        }
        if (currentTaxType === 'intra') { cgstTotal += tax / 2; sgstTotal += tax / 2; }
        else igstTotal += tax;
    });
    $('#cgstTotal').text(cgstTotal.toFixed(2));
    $('#sgstTotal').text(sgstTotal.toFixed(2));
    $('#igstTotal').text(igstTotal.toFixed(2));
    $('#totalTax').text((cgstTotal + sgstTotal + igstTotal).toFixed(2));
}

// ===================== ADD ITEM MODAL =====================
function openAddItemModal() {
    if (!$('#partyTypeInput').val()) { showAlert('Please select a party first', 'error'); return; }
    if (!$('#invoiceType').val())    { showAlert('Please select invoice type first', 'error'); return; }
    $('#addItemModal').css('display', 'flex');
    if (allProducts.length === 0) loadProducts();
}
function closeAddItemModal() { $('#addItemModal').hide(); $('#searchProduct').val(''); }

function loadProducts() {
    const tbody = $('#productsTableBody');
    $('#productsLoading').show(); tbody.empty();
    $.get('{{ route("admin.purchases.get-warehouse-products") }}', function(res) {
        $('#productsLoading').hide();
        allProducts = res.products || [];
        renderProductTable(allProducts);
    }).fail(function() {
        $('#productsLoading').hide();
        tbody.html('<tr><td colspan="8" style="text-align:center;padding:30px;color:#c33;">Failed to load products</td></tr>');
    });
}

function renderProductTable(products) {
    const tbody   = $('#productsTableBody');
    const showTax = currentInvType === 'gst';
    if (!products.length) { tbody.html('<tr><td colspan="8" style="text-align:center;padding:30px;color:#999;">No products found</td></tr>'); return; }
    tbody.html(products.map(function(p) {
        return `<tr onclick="toggleProductRowCheckbox(this, event)" style="cursor: pointer;">
            <td class="product-name">
                <div class="product-name-txt"><strong>${p.name}</strong></div>
                <div class="product-sub-txt">${p.sku || '-'} | Unit: ${p.unit}</div>
            </td>
            <td class="product-code-col">${p.sku || '—'}</td>
            <td class="product-mrp">₹${parseFloat(p.mrp_price).toFixed(2)}</td>
            <td class="product-price">₹${parseFloat(p.purchase_price).toFixed(2)}</td>
            <td class="product-stock-col">${p.unit}</td>
            <td class="col-tax" ${showTax ? '' : 'style="display:none"'}>${p.tax_percent}%</td>
            <td class="product-qty-col" onclick="event.stopPropagation()">
                <div class="product-qty-selector">
                    <button type="button" class="prod-qty-btn minus" onclick="adjustProductModalQty(this, -1)">−</button>
                    <input type="number" class="qty-input" min="1" value="1">
                    <button type="button" class="prod-qty-btn plus" onclick="adjustProductModalQty(this, 1)">+</button>
                </div>
            </td>
            <td class="product-select-col" onclick="event.stopPropagation()">
                <input type="checkbox" class="select-product" onchange="toggleRowActive(this)"
                    data-id="${p.id}" data-type="${p.product_type}"
                    data-variant="${p.variant_id || ''}"
                    data-name="${p.name}" data-sku="${p.sku || ''}"
                    data-hsn="${p.hsn_code || ''}" data-unit="${p.unit}"
                    data-mrp="${p.mrp_price}" data-purchase="${p.purchase_price}"
                    data-tax="${p.tax_percent}">
            </td>
        </tr>`;
    }).join(''));
}

function toggleProductRowCheckbox(row, event) {
    if (event.target.closest('input, select, button, a, .product-qty-selector')) return;
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
    input.val(newVal);
    
    const checkbox = $(btn).closest('tr').find('.select-product');
    if (!checkbox.is(':checked')) {
        checkbox.prop('checked', true);
        toggleRowActive(checkbox);
    }
}

function addSelectedProducts() {
    const checked = $('#productsTableBody .select-product:checked');
    if (!checked.length) { closeAddItemModal(); return; }
    checked.each(function() {
        const chk  = $(this);
        const qty  = Math.max(1, Math.round(parseFloat(chk.closest('tr').find('.qty-input').val()) || 0));
        const isIncl = currentInvType === 'gst' && currentGstMode === 'inclusive';
        const item = {
            product_id:     chk.data('id'),
            product_type:   chk.data('type'),
            variant_id:     chk.data('variant') || null,
            product_name:   chk.data('name'),
            sku:            chk.data('sku'),
            hsn_sac:        currentInvType === 'gst' ? chk.data('hsn') : '',
            unit:           chk.data('unit'),
            mrp_price:      parseFloat(chk.data('mrp')),
            purchase_price: parseFloat(chk.data('purchase')),
            quantity:       qty,
            tax_percent:    currentInvType === 'gst' ? parseFloat(chk.data('tax') || 0) : 0,
            gst_inclusive:  isIncl,
        };
        const existIdx = items.findIndex(function(i) { return i.product_id === item.product_id && i.variant_id === item.variant_id; });
        if (existIdx > -1) items[existIdx].quantity += qty;
        else items.push(item);
    });
    closeAddItemModal();
    renderItemsTable();
    showAlert('Items added', 'success');
}

// ===================== QUICK ADD =====================
function openQuickAddModal() { closeAddItemModal(); $('#quickAddModal').css('display', 'flex'); }
function closeQuickAddModal() { $('#quickAddModal').hide(); $('#quickAddForm')[0].reset(); }
$('#quickAddForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $(this).find('[type=submit]').prop('disabled', true).text('Creating...');
    $.ajax({
        url: '{{ route("admin.purchases.quick-add-product") }}',
        type: 'POST', data: new FormData(this), processData: false, contentType: false,
        success: function(res) {
            if (res.success) {
                const p = res.product;
                allProducts.push(p);
                const isIncl = currentInvType === 'gst' && currentGstMode === 'inclusive';
                items.push({
                    product_id: p.id, product_type: 'simple', variant_id: null,
                    product_name: p.name, sku: p.sku,
                    hsn_sac: currentInvType === 'gst' ? p.hsn_code : '',
                    unit: p.unit, mrp_price: p.mrp_price, purchase_price: p.purchase_price,
                    quantity: 1, tax_percent: currentInvType === 'gst' ? p.tax_percent : 0,
                    gst_inclusive: isIncl,
                });
                closeQuickAddModal();
                renderItemsTable();
                showAlert(p.name + ' created and added!', 'success');
            } else {
                showAlert(res.message, 'error');
                btn.prop('disabled', false).text('Create & Add');
            }
        },
        error: function(xhr) {
            showAlert(xhr.responseJSON?.message || 'Failed to create', 'error');
            btn.prop('disabled', false).text('Create & Add');
        }
    });
});

// ===================== RENDER ITEMS =====================
function renderItemsTable() {
    const tbody  = $('#itemsTableBody');
    const mobileContainer = $('#mobileItemsContainer');
    const footer = $('#itemsTableFooter');
    const showTax = currentInvType === 'gst';

    tbody.empty();
    mobileContainer.empty();
    enableExtraFields();

    if (items.length === 0) {
        tbody.html(`<tr class="empty-row"><td colspan="10"><div class="empty-items"><div class="empty-icon">📦</div><p>No items added yet</p><button type="button" class="btn-add-first-item" onclick="openAddItemModal()">+ Add First Item</button></div></td></tr>`);
        mobileContainer.html(`<div class="empty-items" style="padding: 20px; text-align: center; border: 1px dashed #ccc; border-radius: 6px; background: #fff;"><div class="empty-icon" style="font-size: 24px; margin-bottom: 8px;">🛒</div><p style="font-size: 11px; color: #666; margin-bottom: 10px;">No items added yet</p><button type="button" class="btn-small" onclick="openAddItemModal()" style="border-color: #fa8725; color: #fa8725; padding: 6px 14px; font-size: 11px;">+ Add First Item</button></div>`);
        footer.hide();
        resetSummary();
        return;
    }

    footer.show();
    let ftMRP = 0, ftPurchase = 0, ftTax = 0, ftTotal = 0;

    tbody.html(items.map(function(item, idx) {
        const mrpTotal   = item.quantity * (item.mrp_price || 0);
        const isIncl     = item.gst_inclusive && showTax;
        const taxPercent = parseFloat(item.tax_percent) || 0;

        let baseTotal, taxAmt, rowTotal;
        if (isIncl && taxPercent > 0) {
            const gross = item.quantity * item.purchase_price;
            baseTotal   = gross * 100 / (100 + taxPercent);
            taxAmt      = gross - baseTotal;
            rowTotal    = gross;
        } else {
            baseTotal = item.quantity * item.purchase_price;
            taxAmt    = showTax ? baseTotal * taxPercent / 100 : 0;
            rowTotal  = baseTotal + taxAmt;
        }

        ftMRP      += mrpTotal;
        ftPurchase += baseTotal;
        ftTax      += taxAmt;
        ftTotal    += rowTotal;

        const inclBadge = isIncl ? `<span class="incl-badge">INCL</span>` : '';

        // Mobile Card HTML
        let card = `
        <div class="mobile-item-card" data-index="${idx}">
            <div class="mobile-item-header">
                <div class="mobile-item-title-wrap">
                    <span class="mobile-item-title">${item.product_name}</span>
                    ${inclBadge}
                    <span class="mobile-item-sub">${item.sku || '-'} | ${item.unit || 'PCS'}</span>
                </div>
                <button type="button" class="btn-delete-mobile" onclick="removeItem(${idx})" title="Remove">🗑️</button>
            </div>
            
            <!-- Row 1: Prices & Taxes -->
            <div class="mobile-item-grid-prices" style="grid-template-columns: 1fr 1fr;">
                <div class="mobile-item-col">
                    <label class="mobile-item-label">Purchase Price (₹)</label>
                    <input type="number" class="mobile-item-input price-edit-mobile" min="0" step="0.01" value="${item.purchase_price.toFixed(2)}"
                        onchange="updateItem(${idx}, 'purchase_price', this.value)">
                </div>
                <div class="mobile-item-col">
                    <label class="mobile-item-label">Tax %</label>
                    ${showTax ? `
                        <input type="number" class="mobile-item-input tax-edit-mobile" min="0" max="100" step="0.01" value="${taxPercent.toFixed(2)}"
                            onchange="updateItem(${idx}, 'tax_percent', this.value)">
                    ` : `
                        <input type="text" class="mobile-item-input" value="—" disabled style="background:#f1f1f1;text-align:center;">
                    `}
                </div>
            </div>

            <!-- Row 2: Quantity Adjuster -->
            <div class="mobile-item-grid-qty" style="grid-template-columns: 1fr;">
                <div class="mobile-item-col-qty">
                    <label class="mobile-item-label">Quantity</label>
                    <div class="mobile-qty-controls">
                        <button type="button" class="mobile-qty-btn minus" onclick="adjustMobileQty(${idx}, -1)">−</button>
                        <input type="number" class="mobile-item-input qty-edit-mobile" min="1" value="${item.quantity}"
                            onchange="updateItem(${idx}, 'quantity', this.value)">
                        <button type="button" class="mobile-qty-btn plus" onclick="adjustMobileQty(${idx}, 1)">+</button>
                    </div>
                </div>
            </div>
            
            <!-- Final Row: Total Amt -->
            <div class="mobile-item-footer-clean">
                <div class="mobile-item-amount-wrap">
                    <span class="mobile-item-amount-label">Final Amount</span>
                    <span class="mobile-item-amount-val">₹ ${rowTotal.toFixed(2)}</span>
                </div>
            </div>
        </div>`;
        mobileContainer.append(card);

        return `<tr class="${isIncl ? 'row-inclusive' : ''}">
            <td class="item-name">${item.product_name}${inclBadge}</td>
            <td>${item.sku || '—'}</td>
            <td class="col-hsn">${item.hsn_sac || '—'}</td>
            <td>${item.unit || 'PCS'}</td>
            <td><input type="number" min="1" step="1" value="${item.quantity}"
                onchange="updateItem(${idx},'quantity',this.value)"
                style="width:60px;padding:3px;border:1px solid #ccc;border-radius:2px;font-size:10px;text-align:center;"></td>
            <td>₹${(item.mrp_price || 0).toFixed(2)}</td>
            <td><input type="number" min="0" step="0.01" value="${item.purchase_price.toFixed(2)}"
                onchange="updateItem(${idx},'purchase_price',this.value)"
                style="width:80px;padding:3px;border:1px solid #ccc;border-radius:2px;font-size:10px;text-align:right;"></td>
            <td class="col-tax">${taxPercent}%</td>
            <td>₹${rowTotal.toFixed(2)}</td>
            <td><button type="button" class="btn-delete" onclick="removeItem(${idx})">✕</button></td>
        </tr>`;
    }).join(''));

    $('#footerMRP').text('₹' + ftMRP.toFixed(2));
    $('#footerPurchase').text('₹' + ftPurchase.toFixed(2));
    $('#footerTax').text('₹' + ftTax.toFixed(2));
    $('#footerTotal').text('₹' + ftTotal.toFixed(2));

    calculateTaxBreakup();
    calculateInvoiceSummary(ftMRP, ftPurchase, ftTax);
}

function adjustMobileQty(index, amount) {
    if (!items[index]) return;
    const currentQty = parseFloat(items[index].quantity) || 1;
    const newQty = Math.max(1, currentQty + amount);
    items[index].quantity = newQty;
    renderItemsTable();
}

function updateItem(idx, field, val) {
    if (items[idx]) {
        if (field === 'quantity') items[idx][field] = Math.max(1, Math.round(parseFloat(val) || 0));
        else items[idx][field] = parseFloat(val) || 0;
        renderItemsTable();
    }
}
function removeItem(idx) { items.splice(idx, 1); renderItemsTable(); }
function resetSummary() { $('#totalMRP,#subtotal,#cgstTotal,#sgstTotal,#igstTotal,#totalTax,#grandTotal,#mobileStickyGrandTotal').text('0.00'); $('#balanceAmount').text('0.00'); }

// ===================== CALCULATIONS =====================
function calculateInvoiceSummary(totalMRP, subtotal, totalTax) {
    const discType = $('#extraDiscountTypeInput').val();
    let discount = 0;
    if (discType === 'percent') { const pct = parseFloat($('#extraDiscountPercent').val()) || 0; if (pct > 0) discount = subtotal * pct / 100; }
    else discount = parseFloat($('#extraDiscount').val()) || 0;
    const extraCharge = parseFloat($('#extraCharge').val()) || 0;
    let grand = (subtotal - discount) + (currentInvType === 'gst' ? totalTax : 0) + extraCharge;
    if ($('#autoRoundOff').is(':checked')) grand = Math.round(grand);
    $('#totalMRP').text(totalMRP.toFixed(2));
    $('#subtotal').text(subtotal.toFixed(2));
    $('#grandTotal').text(grand.toFixed(2));
    $('#mobileStickyGrandTotal').text(grand.toFixed(2));
    clampAmountPaid();
}
function calculateTotals() { renderItemsTable(); }

function calculateBalance() {
    const grandTotal = parseFloat($('#grandTotal').text()) || 0;
    const amountPaid = parseFloat($('#amountPaid').val()) || 0;
    let debitRemaining = 0;
    const debitRowText = $('#debitNoteRemainingRow .info-value').text();
    if (debitRowText) { const match = debitRowText.match(/₹\s*([\d,]+\.?\d*)/); if (match) debitRemaining = parseFloat(match[1].replace(/,/g, '')) || 0; }
    const remainingAfterCash = Math.max(0, grandTotal - amountPaid);
    const debitWillUse = Math.min(debitRemaining, remainingAfterCash);
    const finalBalance = Math.max(0, grandTotal - (amountPaid + debitWillUse));
    $('#balanceAmount').text(finalBalance.toFixed(2));
    $('#debitAdjustmentInfo').remove();
    if (debitWillUse > 0) {
        const cashRequired = Math.max(0, grandTotal - debitWillUse);
        $('#amountPaid').closest('.payment-section-container').append(`<div id="debitAdjustmentInfo" style="margin-top:8px;padding:8px 10px;background:#d1fae5;border-radius:5px;font-size:11px;color:#065f46;border:1px solid #6ee7b7;">✓ Debit Note ₹${debitWillUse.toFixed(2)} will be auto-adjusted on generate<br><span style="font-size:10px;color:#047857;">You need to pay only ₹${cashRequired.toFixed(2)} (Cash + Debit Note)</span></div>`);
    }
}
function validateAndCalculateBalance() {
    const grand = parseFloat($('#grandTotal').text()) || 0, paid = parseFloat($('#amountPaid').val()) || 0;
    $('#amountPaid').css('border-color', paid > grand ? '#dc3545' : '#ccc');
    calculateBalance();
}
function clampAmountPaid() {
    const grand = parseFloat($('#grandTotal').text()) || 0;
    let paid    = parseFloat($('#amountPaid').val())  || 0;
    if (paid > grand) { paid = grand; $('#amountPaid').val(paid.toFixed(2)); $('#amountPaid').css('border-color','#ccc'); showAlert('Amount paid cannot exceed grand total', 'error'); }
    calculateBalance();
}
function markFullyPaid() { $('#amountPaid').val((parseFloat($('#grandTotal').text()) || 0).toFixed(2)); calculateBalance(); }

// ===================== EXTRA DISCOUNT / CHARGE =====================
function enableExtraFields() {
    const has = items.length > 0;
    if (has) { $('#addDiscountLink,#addDiscountPercentLink,#addChargeLink').removeClass('disabled-link'); }
    else { $('#addDiscountLink,#addDiscountPercentLink,#addChargeLink').addClass('disabled-link'); $('#extraDiscountRow,#extraDiscountPercentRow,#extraChargeRow').hide(); $('#extraDiscount,#extraDiscountPercent,#extraCharge,#chargeName').val(''); $('#extraDiscountTypeInput').val('amount'); }
}
function toggleExtraDiscount() {
    if (!items.length) { showAlert('Add items first', 'error'); return; }
    $('#extraDiscountPercentRow').hide(); $('#extraDiscountPercent').val('');
    $('#extraDiscountRow').toggle();
    if ($('#extraDiscountRow').is(':visible')) { $('#extraDiscountTypeInput').val('amount'); $('#extraDiscount').focus(); } else $('#extraDiscount').val('');
    calculateTotals();
}
function toggleExtraDiscountPercent() {
    if (!items.length) { showAlert('Add items first', 'error'); return; }
    $('#extraDiscountRow').hide(); $('#extraDiscount').val('');
    $('#extraDiscountPercentRow').toggle();
    if ($('#extraDiscountPercentRow').is(':visible')) { $('#extraDiscountTypeInput').val('percent'); $('#extraDiscountPercent').focus(); } else $('#extraDiscountPercent').val('');
    calculateTotals();
}
function toggleExtraCharge() {
    if (!items.length) { showAlert('Add items first', 'error'); return; }
    $('#extraChargeRow').toggle();
    if (!$('#extraChargeRow').is(':visible')) { $('#extraCharge,#chargeName').val(''); calculateTotals(); }
}

// ===================== PAYMENT TERMS =====================
function updateDueDateFromTerms() {
    const invDate = $('#invoiceDate').val(), days = parseInt($('#paymentTermsDays').val()) || 0;
    if (!invDate || days <= 0) return;
    const d = new Date(invDate); d.setDate(d.getDate() + days);
    $('#dueDate').val(d.toISOString().split('T')[0]);
    $('#paymentTermsInput').val('Due in ' + days + ' days');
}
function updatePaymentTermsFromDueDate() {
    const inv = $('#invoiceDate').val(), due = $('#dueDate').val();
    if (!inv || !due) return;
    const diff = Math.ceil((new Date(due) - new Date(inv)) / 86400000);
    $('#paymentTermsDays').val(diff); $('#paymentTermsInput').val('Due in ' + diff + ' days');
}

// ===================== DOCUMENT READY =====================
$(document).ready(function() {
    currentInvType = 'gst';
    currentGstMode = 'exclusive';
    handleInvoiceTypeChange();
    showIntraStateTax();
    enableExtraFields();
    onCreatePartyTypeChange('vendor');

    $('.party-type-tab').on('click', function() {
        $('.party-type-tab').removeClass('active'); $(this).addClass('active');
        currentPartyTab = $(this).data('type'); loadParties();
    });
    $('#searchParty').on('input', function() { clearTimeout(searchTimer); searchTimer = setTimeout(function() { loadParties(); }, 300); });
    $('#searchProduct').on('input', function() {
        const s = $(this).val().toLowerCase();
        const filtered = allProducts.filter(function(p) { return p.name.toLowerCase().includes(s) || (p.sku || '').toLowerCase().includes(s); });
        renderProductTable(filtered);
    });

    $('#sameBillingShipping').on('change', function() {
        if (!this.checked) return;
        const f = document.getElementById('createPartyForm');
        ['address','city','state','pincode','country'].forEach(function(field) {
            const src = f.querySelector(`[name="billing_${field}"]`);
            const dst = f.querySelector(`[name="shipping_${field}"]`);
            if (src && dst) dst.value = src.value;
        });
    });

    $('#createPartyForm').on('submit', function(e) {
        e.preventDefault();
        const phone = $('input[name="phone"]').val();
        if (!/^[6-9]\d{9}$/.test(phone)) { showAlert('Please enter a valid 10-digit phone number', 'error'); return; }
        const formData = $(this).serializeArray();
        formData.push({ name: 'same_billing_shipping', value: $('#sameBillingShipping').is(':checked') ? '1' : '0' });
        $.ajax({
            url: '{{ route("admin.purchases.create-party") }}', type: 'POST', data: $.param(formData),
            success: function(res) {
                if (res.success) { showAlert('Party created!', 'success'); closeCreatePartyModal(); selectParty(res.party_id, res.party_type); }
                else showAlert('Error: ' + res.message, 'error');
            },
            error: function(xhr) { showAlert(xhr.responseJSON?.message || 'Failed to create party', 'error'); }
        });
    });

    $('.modal-overlay').on('click', function() { $(this).closest('.modal').hide(); });
    $(document).on('keydown', function(e) { if (e.key === 'Escape') $('.modal').hide(); });
    $('#extraDiscount,#extraDiscountPercent,#extraCharge').on('input', calculateTotals);
    $('#amountPaid').on('input', calculateBalance);
    $('#autoRoundOff').on('change', calculateTotals);

    $('#purchaseInvoiceForm').on('submit', function(e) {
        e.preventDefault();
        if (isSubmitting) { showAlert('Please wait...', 'info'); return; }
        const partyType = $('#partyTypeInput').val();
        if (!partyType)    { showAlert('Please select a party', 'error'); return; }
        if (!$('#invoiceType').val()) { showAlert('Please select invoice type', 'error'); return; }
        if (!items.length) { showAlert('Please add at least one item', 'error'); return; }

        isSubmitting = true;
        const $btn = $('#submitBtn'), orig = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner"></span> Creating...');

        const fd = new FormData();
        $(this).serializeArray().forEach(function(f) { fd.append(f.name, f.value); });
        fd.append('items',          JSON.stringify(items));
        fd.append('grand_total',    $('#grandTotal').text());
        fd.append('auto_round_off', $('#autoRoundOff').is(':checked') ? 1 : 0);
        fd.append('gst_mode',       currentGstMode);

        const discType = $('#extraDiscountTypeInput').val();
        fd.append('extra_discount_type', discType);
        fd.append('extra_discount', discType === 'percent' ? ($('#extraDiscountPercent').val() || 0) : ($('#extraDiscount').val() || 0));
        fd.append('extra_charge', $('#extraCharge').val() || 0);
        fd.append('charge_name',  $('#chargeName').val()  || '');

        $.ajax({
            url: '{{ route("admin.purchases.store") }}', type: 'POST', data: fd, processData: false, contentType: false,
            success: function(res) {
                if (res.success) { showAlert('Invoice created!', 'success'); setTimeout(function() { window.location.href = '/admin/purchases/' + res.invoice_id; }, 1500); }
                else { showAlert('Error: ' + res.message, 'error'); isSubmitting = false; $btn.prop('disabled', false).html(orig); }
            },
            error: function(xhr) { showAlert(xhr.responseJSON?.message || 'Failed', 'error'); isSubmitting = false; $btn.prop('disabled', false).html(orig); }
        });
    });

    setInterval(function() {
        if (localStorage.getItem('product_created') === 'true') {
            localStorage.removeItem('product_created');
            const modalOpen = $('#addItemModal').is(':visible');
            allProducts = []; $('#productsLoading').show(); $('#productsTableBody').empty();
            $.get('{{ route("admin.purchases.get-warehouse-products") }}', function(res) {
                $('#productsLoading').hide(); allProducts = res.products || [];
                if (modalOpen) { renderProductTable(allProducts); showAlert('New product added to list! ✓', 'success'); }
            }).fail(function() { $('#productsLoading').hide(); });
        }
    }, 1000);
});
</script>
@endpush
@endsection
