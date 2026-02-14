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

            <div class="form-row">
                <div class="form-col-main">
                    <div class="two-col-row">
                        <div class="col-50">
                             <!-- Customer Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-header-left">
                                        <div class="section-icon">👤</div>
                                        <div class="section-title">
                                            <h3>Bill To</h3>
                                            <p>Select customer and address details</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-select-customer" id="selectCustomerBtn" onclick="openSelectCustomerModal()">
                                        <span class="btn-icon">👤</span>
                                        Select Customer
                                    </button>
                                </div>

                                <div class="section-body">
                                    <div id="selectedCustomerDetails" class="selected-customer-details" style="display: none;">
                                        <div class="customer-header">
                                            <h4 id="customerNameDisplay">Customer Name</h4>
                                            <button type="button" class="btn-change-customer" onclick="openSelectCustomerModal()">
                                                Change
                                            </button>
                                        </div>

                                        <div class="customer-info-grid">
                                            <div class="info-column">
                                                <div class="info-row">
                                                    <span class="info-label">Phone:</span>
                                                    <span id="customerPhone" class="info-value">-</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="info-label">Email:</span>
                                                    <span id="customerEmail" class="info-value">-</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="info-label">GST:</span>
                                                    <span id="customerGst" class="info-value">-</span>
                                                </div>
                                            </div>
                                            <div class="info-column">
                                                <div class="info-row">
                                                    <span class="info-label">Type:</span>
                                                    <span id="customerType" class="info-value">-</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="info-label">Company:</span>
                                                    <span id="customerCompany" class="info-value">-</span>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="customer_id" id="customerIdInput">

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
                                                        <p id="billingAddressText">Select a customer to view address</p>
                                                    </div>
                                                    <input type="hidden" name="billing_address" id="billingAddressInput">
                                                </div>

                                                <div class="address-card">
                                                    <div class="address-card-header">
                                                        <span class="address-type">Shipping Address</span>
                                                    </div>
                                                    <div class="address-content">
                                                        <p id="shippingAddressText">Select a customer to view address</p>
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
                                            <label class="form-label">Vehicle No.</label>
                                            <input type="text" name="vehicle_no" class="form-control" placeholder="Optional">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label">Colours</label>
                                            <input type="text" name="colours" class="form-control" placeholder="Optional">
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
                            <button type="button" class="btn-add-item" onclick="openAddItemModal()">
                                + Add Item
                            </button>
                        </div>

                        <div class="section-body">
                            <div class="items-table-container">
                                <table class="items-table" id="mainItemsTable">
                                    <thead>
                                        <tr>
                                            <th class="th-item">Item</th>
                                            <th class="th-hsn">HSN/SAC</th>
                                            <th class="th-unit">Unit</th>
                                            <th class="th-qtys">Qty</th>
                                            <th class="th-warranty">Warranty</th>
                                            <th class="th-mrp">MRP (₹)</th>
                                            <th class="th-discount">Disc %</th>
                                            <th class="th-sale-price">Sale Price (₹)</th>
                                            <th class="th-tax">Tax %</th>
                                            <th class="th-amount">Final Amt (₹)</th>
                                            <th class="th-action">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                    </tbody>
                                    <tfoot id="itemsTableFooter" style="background-color: #f0f0f0; font-weight: 600; border-top: 2px solid #333;">
                                        <tr>
                                            <td colspan="5" style="text-align: right; padding: 10px; font-size: 12px;">
                                                <strong>SUBTOTAL:</strong>
                                            </td>
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
                                            <div class="summary-row">
                                                <span class="summary-label">Total Tax</span>
                                                <span class="summary-value">₹ <span id="totalTax">0.00</span></span>
                                            </div>
                                            <div class="summary-divider"></div>
                                            <div class="summary-row">
                                                <span class="summary-label" style="font-weight: 600;">Subtotal</span>
                                                <span class="summary-value">₹ <span id="subtotal">0.00</span></span>
                                            </div>
                                            <div class="summary-divider"></div>

                                            <div class="summary-row">
                                                <span class="summary-label">
                                                    <a href="javascript:void(0)" onclick="toggleExtraDiscount()">+ Add Discount</a>
                                                </span>
                                            </div>
                                            <div id="extraDiscountRow" style="display:none;" class="summary-row">
                                                <input type="number" id="extraDiscount" placeholder="Enter discount amount" oninput="calculateTotals()" class="summary-input">
                                            </div>

                                            <div class="summary-row">
                                                <span class="summary-label">
                                                    <a href="javascript:void(0)" onclick="toggleExtraDiscountPercent()">+ Add Discount %</a>
                                                </span>
                                            </div>
                                            <div id="extraDiscountPercentRow" style="display:none;" class="summary-row">
                                                <input type="number" id="extraDiscountPercent" placeholder="Enter discount %" oninput="calculateTotals()" class="summary-input">
                                            </div>

                                            <div class="summary-row">
                                                <span class="summary-label">
                                                    <a href="javascript:void(0)" onclick="toggleExtraCharge()">+ Add Another Charge</a>
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
                                            <input type="number" name="amount_paid" id="amountPaid" class="form-control" value="0" step="0.01" min="0" oninput="calculateBalance()">
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

<!-- Select Customer Modal -->
<div class="modal" id="selectCustomerModal">
    <div class="modal-overlay" onclick="closeSelectCustomerModal()"></div>
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <div class="modal-icon">👥</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Select Customer</h4>
                <div class="modal-subtitle">Choose customer or create new one</div>
            </div>
            <button type="button" class="modal-close" onclick="closeSelectCustomerModal()">×</button>
        </div>

        <div class="modal-body">
            <div class="customer-modal-header">
                <div class="search-container">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="searchCustomer" class="search-input" placeholder="Search customers by name, phone or email...">
                    </div>
                </div>
                <button type="button" class="btn-create-new-customer" onclick="openCreateCustomerModal()">
                    <span class="btn-icon">+</span>
                    Create New Customer
                </button>
            </div>

            <div class="customers-table-container">
                <table class="customers-table">
                    <thead>
                        <tr>
                            <th class="th-name">Name</th>
                            <th class="th-phone">Phone</th>
                            <th class="th-email">Email</th>
                            <th class="th-company">Company</th>
                            <th class="th-type">Type</th>
                            <th class="th-status">Status</th>
                            <th class="th-action">Action</th>
                        </tr>
                    </thead>
                    <tbody id="customersTableBody">
                    </tbody>
                </table>
                <div id="customersLoading" class="loading-state">
                    <div class="loading-spinner"></div>
                    <p>Loading customers...</p>
                </div>
                <div id="noCustomers" class="no-data-state" style="display: none;">
                    <div class="no-data-icon">👤</div>
                    <p>No customers found</p>
                    <button type="button" class="btn-create-first-customer" onclick="openCreateCustomerModal()">
                        + Create First Customer
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeSelectCustomerModal()">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Create Customer Modal -->
<div class="modal" id="createCustomerModal">
    <div class="modal-overlay" onclick="closeCreateCustomerModal()"></div>
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <div class="modal-icon">👤</div>
            <div class="modal-title-section">
                <h4 class="modal-title">Create New Customer</h4>
                <div class="modal-subtitle">Enter customer details and address</div>
            </div>
            <button type="button" class="modal-close" onclick="closeCreateCustomerModal()">×</button>
        </div>

        <form id="createCustomerForm">
            @csrf
            <div class="modal-body">
                <div class="form-section-small">
                    <h5>Basic Information</h5>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label required">Customer Name</label>
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
                            <label class="form-label">Customer Type</label>
                            <div class="select-wrapper">
                                <select name="customer_type" class="form-control">
                                    <option value="individual">Individual</option>
                                    <option value="business">Business</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control">
                        </div>
                        <div class="form-group col-6">
                            <label class="form-label">Status</label>
                            <div class="select-wrapper">
                                <select name="status" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
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
                            <input type="text" name="gst_number" class="form-control" maxlength="15">
                        </div>
                        <div class="form-group col-6">
                            <label class="form-label">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control" maxlength="10">
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
                        <div class="form-group col-6">
                            <label class="form-label">City</label>
                            <input type="text" name="billing_city" class="form-control">
                        </div>
                        <div class="form-group col-6">
                            <label class="form-label">State</label>
                            <input type="text" name="billing_state" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="billing_pincode" class="form-control" maxlength="6">
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
                        <input type="text" name="shipping_address" class="form-control" placeholder="House no, street, area">
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

                <div class="form-section-small">
                    <h5>Additional Information</h5>
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closeCreateCustomerModal()">
                    Cancel
                </button>
                <button type="submit" class="btn-modal btn-primary">
                    Create Customer
                </button>
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
            <div class="search-container">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchProduct" class="search-input" placeholder="Search items by name, SKU or barcode...">
                </div>
            </div>

            <div class="products-table-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th class="th-name">Item Name</th>
                            <th class="th-code">Item Code</th>
                            <th class="th-mrp">MRP Price</th>
                            <th class="th-price">Sale Price</th>
                            <th class="th-stock">Current Stock</th>
                            <th class="th-qty">Quantity</th>
                            <th class="th-action">Action</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                    </tbody>
                </table>
                <div id="productsLoading" class="loading-state">
                    <div class="loading-spinner"></div>
                    <p>Loading products...</p>
                </div>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-modal btn-primary" onclick="addSelectedProducts()">
                Done
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
/* [KEEP ALL YOUR EXISTING CSS - Copy from original file] */
/* Main Container */
.products-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 11px;
    line-height: 1.3;
    color: #333;
}
.btn-submit-invoice {
    align-self: flex-start;
}

/* Alert Messages */
#alertContainer {
    position: fixed;
    top: 15px;
    right: 15px;
    z-index: 9999;
}

.alert {
    padding: 10px 14px;
    margin-bottom: 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 500;
    animation: slideInRight 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    border-bottom: 1px solid #ddd;
}

.header-left .page-title {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin: 0 0 3px 0;
}

.btn-small {
    padding: 5px 10px;
    background: #fa8725;
    color: white;
    border: none;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-small:hover {
    background: #444;
}

/* Invoice Form */
.invoice-form-wrapper {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
}

.form-row {
    display: flex;
    flex-direction: row;
    gap: 15px;
}

.form-col-main {
    width: 100%;
}

/* Form Sections */
.form-section {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 15px;
    overflow: hidden;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
}

.section-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-icon {
    width: 28px;
    height: 28px;
    background: #fa8725de;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 13px;
    flex-shrink: 0;
}

.section-title h3 {
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.section-title p {
    font-size: 10px;
    color: #666;
    margin: 1px 0 0 0;
}

.section-body {
    padding: 15px;
}

/* Form Groups */
.form-group {
    margin-bottom: 12px;
}

.form-label {
    display: block;
    font-size: 11px;
    font-weight: 500;
    color: #555;
    margin-bottom: 3px;
}

.form-label.required::after {
    content: ' *';
    color: #dc3545;
}

.form-control {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 10px;
    background: #fff;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #666;
    box-shadow: 0 0 0 2px rgba(102, 102, 102, 0.1);
}

.select-wrapper {
    position: relative;
}

.select-arrow {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    font-size: 9px;
    pointer-events: none;
}

/* Form Layout */
.form-row .form-group {
    margin-bottom: 10px;
}

.form-row .col-6 {
    width: 50%;
}

.form-row .col-4 {
    width: 33.33%;
}

.form-row .col-8 {
    width: 66.67%;
}

/* Customer Section */
.btn-select-customer {
    width: 180px;
    padding: 8px 12px;
    background: #fa8725de;
    color: white;
    border: none;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}


.btn-select-customer .btn-icon {
    font-size: 12px;
}

.selected-customer-details {
    padding: 5px;
    background: #fafafa;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.customer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
    padding-bottom: 4px;
    border-bottom: 1px solid #eee;
}

.customer-header h4 {
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.btn-change-customer {
    padding: 3px 8px;
    background: #666;
    color: white;
    border: none;
    border-radius: 3px;
    font-size: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-change-customer:hover {
    background: #555;
}

.customer-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.info-column .info-row {
    display: flex;
    align-items: center;
    margin-bottom: 6px;
}

.info-column .info-label {
    font-size: 10px;
    font-weight: 500;
    color: #555;
    width: 60px;
    flex-shrink: 0;
}

.info-column .info-value {
    font-size: 10px;
    color: #333;
}
.invoice-bottom-layout {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.invoice-row {
    display: flex;
    gap: 15px;
}

.invoice-col {
    flex: 1;
}

/* Terms box */
.terms-box {
    background: #f1f3f5;
    padding: 12px;
    font-size: 10px;
    border-radius: 4px;
    line-height: 1.5;
}

/* Bank details */
.bank-details-box {
    background: #fafafa;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 12px;
    font-size: 10px;
}

.bank-details-box h4 {
    margin-bottom: 8px;
    font-size: 11px;
}

.change-bank {
    display: inline-block;
    margin-top: 8px;
    font-size: 10px;
    color: #007bff;
    text-decoration: none;
}

.change-bank:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
    .invoice-row {
        flex-direction: column;
    }
}

.summary-input {
    width: 100%;
    padding: 4px;
    font-size: 10px;
}

.summary-input-small {
    width: 70px;
    padding: 4px;
    font-size: 10px;
}

/* Address Section */
.address-section {
    padding-top: 2px;
    border-top: 1px solid #eee;
}

.address-header h5 {
    font-size: 11px;
    font-weight: 600;
    color: #333;
    margin: 0 0 12px 0;
}

.address-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.address-card {
    background: #fafafa;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 12px;
}

.address-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
}

.address-type {
    font-size: 10px;
    font-weight: 600;
    color: #333;
}

/* Address content only - no radio buttons */
.address-content {
    font-size: 10px;
    color: #555;
    line-height: 1.4;
}

/* Invoice Details - Payment Terms */
.payment-terms-days {
    display: flex;
    align-items: center;
    gap: 5px;
}

.payment-terms-days .form-control {
    width: 50%;
    flex-shrink: 0;
}

.payment-terms-days span {
    font-size: 10px;
    color: #666;
    white-space: nowrap;
}

/* Items Section */
.btn-add-item {
    padding: 6px 12px;
    background: #555;
    color: white;
    border: none;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-add-item:hover {
    background: #444;
}

/* Items Table */
.items-table-container {
    overflow-x: auto;
    width: 100%;
    margin-top: 10px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
    min-width: 1000px;
}

.items-table th {
    background: #f8f9fa;
    padding: 10px 8px;
    text-align: center;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #ddd;
    white-space: nowrap;
    font-size: 11px;
}

.items-table td {
    padding: 8px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
    text-align: center;
    font-size: 10px;
}

.items-table td.item-name {
    text-align: left;
    font-weight: 500;
}

.items-table tr:hover {
    background-color: #f9f9f9;
}

.items-table tbody tr {
    transition: background-color 0.2s;
}

/* Column widths */
.th-item { width: 200px; min-width: 150px; text-align: left !important; }
.th-hsn { width: 80px; }
.th-qtys { width: 70px; }
.th-unit { width: 60px; }
.th-warranty { width: 140px; }
.th-sale-price { width: 90px; }
.th-mrp { width: 90px; }
.th-discount { width: 80px; }
.th-tax { width: 75px; }
.th-amount { width: 90px; }
.th-action { width: 65px; }

/* Table Inputs */
.items-table input[type="number"] {
    width: 100%;
    padding: 4px 6px;
    border: 1px solid #ccc;
    border-radius: 2px;
    font-size: 10px;
    text-align: center;
}

.items-table input[type="number"]:focus {
    outline: none;
    border-color: #666;
}

/* Delete Button */
.btn-delete {
    width: 28px;
    height: 28px;
    border: none;
    background: #fee;
    color: #c33;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    margin: 0 auto;
}

.btn-delete:hover {
    background: #fcc;
    color: #a00;
}

.btn-delete svg {
    width: 14px;
    height: 14px;
}

/* Empty Items */
.empty-row td {
    padding: 30px 20px;
    text-align: center;
}

.empty-items {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.empty-icon {
    font-size: 24px;
    opacity: 0.3;
    margin-bottom: 5px;
}

.empty-items p {
    font-size: 10px;
    color: #666;
    margin: 0;
}

.btn-add-first-item {
    padding: 6px 12px;
    background: #555;
    color: white;
    border: none;
    border-radius: 3px;
    font-size: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-add-first-item:hover {
    background: #444;
}

/* Terms and Conditions */
.terms-list {
    margin-top: 12px;
}

.term-item {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    margin-bottom: 6px;
}

.term-item input[type="checkbox"] {
    margin-top: 2px;
    flex-shrink: 0;
}

.term-item label {
    font-size: 10px;
    color: #555;
    line-height: 1.4;
}

/* Summary and Payment Layout */
.form-col-sidebar {
    margin-top: 10px;
}

.summary-and-payment {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

/* Summary Section */
.summary-section {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.summary-header {
    padding: 12px 15px;
    background: #f8f9fa;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid #ddd;
}

.summary-icon {
    font-size: 13px;
}

.summary-header h3 {
    font-size: 13px;
    font-weight: 600;
    margin: 0;
}

.summary-body {
    padding: 15px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.summary-label {
    font-size: 11px;
    color: #555;
}

.summary-label a {
    color: #fa8725;
    text-decoration: none;
    font-weight: 500;
    font-size: 10px;
    padding: 4px 8px;
    background: #fff3e6;
    border-radius: 3px;
    border: 1px dashed #fa8725;
    transition: all 0.2s;
    display: inline-block;
}

.summary-label a:hover {
    background: #fa8725;
    color: white;
    border-style: solid;
}

.summary-value {
    font-size: 11px;
    font-weight: 600;
    color: #333;
}

.summary-divider {
    height: 1px;
    background: #eee;
    margin: 10px 0;
}

.total-row {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 2px solid #333;
}

.total-row .summary-label {
    font-size: 12px;
    font-weight: 700;
    color: #333;
}

.total-row .summary-value {
    font-size: 15px;
    font-weight: 700;
    color: #fa8725;
}
/* ================== 50-50 TOP LAYOUT ================== */

.two-col-row {
    display: flex;
    gap: 15px;
    width: 100%;
}

.col-50 {
    width: 50%;
}

/* Mobile pe stack ho jaye */
@media (max-width: 768px) {
    .two-col-row {
        flex-direction: column;
    }

    .col-50 {
        width: 100%;
    }
}
.btn-mark-paid{
    background: #555555;
    color: white;
    border: none;
    border-radius: 3px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    transition: all 0.2s;
    text-decoration: none;
}

/* Payment Section */
.payment-section-container {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
}

.payment-section-container h4 {
    font-size: 11px;
    font-weight: 600;
    color: #333;
    margin: 0 0 12px 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.balance-amount {
    padding: 6px 10px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    color: #333;
}

/* Submit Button */
.btn-submit-invoice {
    width: 25%;
    padding: 10px 15px;
    background: #f98725;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 15px;
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
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    animation: modalFadeIn 0.2s ease;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.modal-lg {
    max-width: 800px;
}

.modal-xl {
    max-width: 800px;
}

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

.modal-title-section {
    flex: 1;
}

.modal-title {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.modal-subtitle {
    font-size: 10px;
    color: #666;
    margin-top: 2px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 18px;
    color: #666;
    cursor: pointer;
    padding: 4px;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #eee;
    color: #333;
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
    background: #555;
}

.modal-body {
    padding: 20px;
}

/* Customer Modal Specific */
.customer-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.search-container {
    flex: 1;
}

.search-box {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 11px;
    color: #666;
}

.search-input {
    width: 100%;
    padding: 7px 10px 7px 30px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 10px;
    background: #fff;
}

.search-input:focus {
    outline: none;
    border-color: #666;
}

.btn-create-new-customer {
    padding: 7px 14px;
    background: #555;
    color: white;
    border: none;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.btn-create-new-customer:hover {
    background: #444;
}

/* Customers Table */
.customers-table-container {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    position: relative;
}

.customers-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
}

.customers-table th {
    background: #f8f9fa;
    padding: 8px 10px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 1px solid #ddd;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 1;
}

.customers-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.customers-table tr:hover {
    background: #f9f9f9;
}

.customers-table tr.selected {
    background: #e8f4ff;
}

/* Column widths */
.th-name { width: 180px; }
.th-phone { width: 100px; }
.th-email { width: 150px; }
.th-company { width: 120px; }
.th-type { width: 80px; }
.th-status { width: 70px; }
.th-action { width: 80px; }

/* Status badges */
.status-badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 500;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

/* Select button */
.btn-select-customer-row {
    padding: 4px 10px;
    background: #555;
    color: white;
    border: none;
    border-radius: 3px;
    font-size: 9px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-select-customer-row:hover {
    background: #444;
}
.warranty-wrapper {
    display: flex;
    gap: 4px;
    align-items: center;
}

.warranty-input {
    width: 50px;
    padding: 3px;
    font-size: 10px;
}

.warranty-select {
    font-size: 10px;
    padding: 3px;
}

/* No data state */
.no-data-state {
    padding: 30px 20px;
    text-align: center;
}

.no-data-icon {
    font-size: 24px;
    opacity: 0.3;
    margin-bottom: 8px;
}

.no-data-state p {
    font-size: 10px;
    color: #666;
    margin: 0 0 12px 0;
}

.btn-create-first-customer {
    padding: 6px 12px;
    background: #555;
    color: white;
    border: none;
    border-radius: 3px;
    font-size: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-create-first-customer:hover {
    background: #444;
}

/* Create Customer Modal */
.form-section-small {
    margin-bottom: 15px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
}

.form-section-small:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.form-section-small h5 {
    font-size: 11px;
    font-weight: 600;
    color: #333;
    margin: 0 0 10px 0;
}

.section-header-small {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

/* Form Check */
.form-check {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 6px;
}

.form-check-input {
    width: 13px;
    height: 13px;
    border: 1px solid #ccc;
    border-radius: 2px;
    cursor: pointer;
}

.form-check-label {
    font-size: 10px;
    color: #555;
    cursor: pointer;
}

/* Add Item Modal */
.products-table-container {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    position: relative;
}

.products-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
}

.products-table th {
    background: #f8f9fa;
    padding: 8px 10px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 1px solid #ddd;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 1;
}

.products-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.products-table tr:hover {
    background: #f9f9f9;
}

/* Products table column widths */
.th-code { width: 90px; }
.th-stock { width: 90px; }
.th-qty { width: 70px; }

/* Quantity Input */
.products-table input[type="number"] {
    width: 50px;
    padding: 4px 6px;
    border: 1px solid #ccc;
    border-radius: 2px;
    font-size: 10px;
    text-align: center;
}

/* Add Button */
.products-table .btn-add {
    padding: 4px 10px;
    background: #555;
    color: white;
    border: none;
    border-radius: 3px;
    font-size: 9px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.products-table .btn-add:hover {
    background: #444;
}

/* Loading State */
.loading-state {
    padding: 30px 20px;
    text-align: center;
}

.loading-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #eee;
    border-top-color: #555;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 8px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-state p {
    font-size: 10px;
    color: #666;
    margin: 0;
}

/* Modal Buttons */
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
    padding: 7px 16px;
    border: none;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 70px;
}

.btn-cancel {
    background: #f8f9fa;
    color: #333;
    border: 1px solid #ccc;
}

.btn-cancel:hover {
    background: #e9ecef;
}

.btn-primary {
    background: #555;
    color: white;
    border: 1px solid #555;
}

.btn-primary:hover {
    background: #444;
}

/* Error States */
.error-field {
    border-color: #dc3545 !important;
    background: #fff5f5;
}

.field-error {
    font-size: 9px;
    color: #dc3545;
    margin-top: 2px;
}


/* Responsive */
@media (max-width: 768px) {
    .products-container {
        padding: 10px;
    }

    .invoice-form-wrapper {
        padding: 10px;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .btn-add-item {
        align-self: flex-start;
    }

    .modal-content {
        margin: 15px;
        width: calc(100% - 30px);
        max-height: calc(100vh - 30px);
    }

    .customer-modal-header {
        flex-direction: column;
        align-items: stretch;
    }

    .btn-create-new-customer {
        width: 100%;
        justify-content: center;
    }

    .address-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .customer-info-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .summary-and-payment {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .modal-xl {
        margin: 10px;
        width: calc(100% - 20px);
        max-height: calc(100vh - 20px);
    }
}

@media (max-width: 480px) {
    .section-body {
        padding: 12px;
    }

    .modal-body {
        padding: 15px;
    }

    .modal-actions {
        flex-direction: column;
        gap: 6px;
    }

    .btn-modal {
        width: 100%;
    }

    .form-row {
        flex-direction: column;
    }

    .form-row .col-6,
    .form-row .col-4,
    .form-row .col-8 {
        width: 100%;
    }

    .items-table th,
    .items-table td,
    .products-table th,
    .products-table td,
    .customers-table th,
    .customers-table td {
        padding: 6px 8px;
    }

    .selected-customer-details {
        padding: 12px;
    }

    .customers-table-container,
    .products-table-container {
        font-size: 9px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// ===================== 🔥 COMPLETE CORRECTED JAVASCRIPT =====================

function showFieldError(input, message) {
    $(input).addClass('error-field');
    if (!$(input).next('.field-error').length) {
        $(input).after(`<div class="field-error">${message}</div>`);
    }
}

function clearFieldError(input) {
    $(input).removeClass('error-field');
    $(input).next('.field-error').remove();
}

let items = [];

// ===================== CUSTOMER MODAL FUNCTIONS =====================

function openSelectCustomerModal() {
    $('#selectCustomerModal').css('display', 'flex');
    loadCustomers();
}

function closeSelectCustomerModal() {
    $('#selectCustomerModal').hide();
    $('#searchCustomer').val('');
}

function openCreateCustomerModal() {
    closeSelectCustomerModal();
    $('#createCustomerModal').css('display', 'flex');
}

function closeCreateCustomerModal() {
    $('#createCustomerModal').hide();
    $('#createCustomerForm')[0].reset();
}

function toggleExtraDiscount() {
    $('#extraDiscountRow').toggle();
}

function toggleExtraDiscountPercent() {
    $('#extraDiscountPercentRow').toggle();
}

function toggleExtraCharge() {
    $('#extraChargeRow').toggle();
}

function loadCustomers(search = '') {
    const tbody = $('#customersTableBody');
    const loading = $('#customersLoading');
    const noData = $('#noCustomers');

    tbody.empty();
    loading.show();
    noData.hide();

    $.get('{{ route('admin.sales.customers.list') }}', {
        search: search
    }, function(response) {
        loading.hide();

        if (response.customers.length === 0) {
            noData.show();
            return;
        }

        response.customers.forEach(customer => {
            const statusClass = customer.status === 'active' ? 'status-active' : 'status-inactive';
            const row = `
                <tr data-customer-id="${customer.id}">
                    <td class="customer-name">${customer.name}</td>
                    <td class="customer-phone">${customer.phone || '-'}</td>
                    <td class="customer-email">${customer.email || '-'}</td>
                    <td class="customer-company">${customer.company_name || '-'}</td>
                    <td class="customer-type">${customer.customer_type}</td>
                    <td class="customer-status">
                        <span class="status-badge ${statusClass}">${customer.status}</span>
                    </td>
                    <td class="customer-action">
                        <button type="button" class="btn-select-customer-row" onclick="selectCustomer('${customer.id}')">Select</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }).fail(function() {
        loading.hide();
        showAlert('Failed to load customers', 'error');
    });
}

function selectCustomer(customerId) {
    $.get('{{ route('admin.sales.get-customer-details', ':id') }}'.replace(':id', customerId), function(res) {
        if (!res.success) return;

        const c = res.customer;
        $('#selectCustomerBtn').hide();
        $('#selectedCustomerDetails').show();
        $('#customerNameDisplay').text(c.name);
        $('#customerPhone').text(c.phone || '-');
        $('#customerEmail').text(c.email || '-');
        $('#customerGst').text(c.gst_number || '-');
        $('#customerType').text(c.customer_type || '-');
        $('#customerCompany').text(c.company_name || '-');
        $('#customerIdInput').val(c.id);

        window.selectedCustomer = c;

        if (c.billing_address) {
            $('#billingAddressText').text(c.billing_address);
            $('#billingAddressInput').val(c.billing_address);
        }

        if (c.shipping_address) {
            $('#shippingAddressText').text(c.shipping_address);
            $('#shippingAddressInput').val(c.shipping_address);
        } else {
            $('#shippingAddressText').text(c.billing_address || 'No shipping address available');
            $('#shippingAddressInput').val(c.billing_address || '');
        }

        closeSelectCustomerModal();
        showAlert('Customer selected', 'success');
    });
}

// ===================== PAYMENT TERMS =====================

function updateDueDateFromTerms() {
    const invoiceDate = $('#invoiceDate').val();
    const days = parseInt($('#paymentTermsDays').val()) || 0;

    if (!invoiceDate || days <= 0) return;

    const date = new Date(invoiceDate);
    date.setDate(date.getDate() + days);

    const dueDate = date.toISOString().split('T')[0];
    $('#dueDate').val(dueDate);
    $('#paymentTermsInput').val(`Due in ${days} days`);
}

function updatePaymentTermsFromDueDate() {
    const invoiceDate = $('#invoiceDate').val();
    const dueDate = $('#dueDate').val();

    if (!invoiceDate || !dueDate) return;

    const start = new Date(invoiceDate);
    const end = new Date(dueDate);

    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    $('#paymentTermsDays').val(diffDays);
    $('#paymentTermsInput').val(`Due in ${diffDays} days`);
}

// ===================== ITEM MODAL =====================

function openAddItemModal() {
    $('#addItemModal').css('display', 'flex');
    $('#searchProduct').val('');
    loadProducts();
}

function closeAddItemModal() {
    $('#addItemModal').hide();
    $('#searchProduct').val('');
    loadProducts();
}

function markFullyPaid() {
    const grandTotal = parseFloat($('#grandTotal').text()) || 0;
    $('#amountPaid').val(grandTotal.toFixed(2));
    calculateBalance();
}

function addSelectedProducts() {
    $('#productsTableBody tr').each(function() {
        const checkbox = $(this).find('.select-product');
        if (!checkbox.is(':checked')) return;

        const input = $(this).find('.qty-input');
        const qty = parseFloat(input.val()) || 0;
        const maxStock = parseFloat(input.data('stock'));

        if (qty <= 0) return;
        if (qty > maxStock) {
            showAlert(`Only ${maxStock} items available`, 'error');
            return;
        }

        const salePrice = parseFloat(input.data('price')) || 0;
        const mrpPrice = parseFloat(input.data('mrp')) || 0;

        let autoDiscount = 0;
        if (mrpPrice > 0 && mrpPrice > salePrice) {
            autoDiscount = ((mrpPrice - salePrice) / mrpPrice) * 100;
        }

        const item = {
            product_id: input.data('product-id'),
            variant_id: input.data('variant-id'),
            product_type: input.data('type'),
            name: input.data('name'),
            sku: input.data('sku'),
            hsn_sac: input.data('hsn'),
            mrp_price: mrpPrice,
            price: salePrice,
            quantity: qty,
            discount: parseFloat(autoDiscount.toFixed(2)),
            tax_percent: parseFloat(input.data('tax')) || 0,
            unit: input.data('unit') || 'PCS',
            warranty_type: input.data('warranty-type') || 'none',
            warranty_period: parseInt(input.data('warranty-period')) || 0
        };

        addItemToInvoice(item);
    });

    closeAddItemModal();
}

function loadProducts(search = '') {
    const tbody = $('#productsTableBody');
    const loading = $('#productsLoading');

    tbody.empty();
    loading.show();

    $.get('{{ route('admin.sales.get-main-warehouse-products') }}', {
        search: search
    }, function(response) {
        loading.hide();

        if (response.products.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="7" class="text-center" style="padding: 40px 20px;">
                        <div style="font-size: 32px; opacity: 0.3; margin-bottom: 10px;">📦</div>
                        <p style="font-size: 11px; color: #6b7280;">No products found</p>
                    </td>
                </tr>
            `);
            return;
        }

        response.products.forEach(product => {
            const row = `
                <tr>
                    <td class="product-name">${product.name}</td>
                    <td class="product-code">${product.sku || '-'}</td>
                    <td class="product-mrp">₹ ${parseFloat(product.mrp_price || 0).toFixed(2)}</td>
                    <td class="product-price">₹ ${parseFloat(product.sale_price).toFixed(2)}</td>
                    <td class="product-stock">${product.current_stock} ${product.unit}</td>
                    <td class="product-qty">
                        <input type="number"
                               class="qty-input"
                               min="1"
                               max="${product.current_stock}"
                               value="1"
                               data-product-id="${product.id}"
                               data-variant-id="${product.variant_id || ''}"
                               data-type="${product.type}"
                               data-price="${product.sale_price}"
                               data-mrp="${product.mrp_price || 0}"
                               data-stock="${product.current_stock}"
                               data-name="${product.name}"
                               data-sku="${product.sku}"
                               data-hsn="${product.hsn_code || ''}"
                               data-tax="${product.tax_percent || 0}"
                               data-unit="${product.unit}"
                               data-warranty-type="${product.warranty_type}"
                               data-warranty-period="${product.warranty_period}">
                    </td>
                    <td>
                        <input type="checkbox" class="select-product">
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }).fail(function() {
        loading.hide();
        tbody.html(`
            <tr>
                <td colspan="7" class="text-center" style="padding: 40px 20px; color: #ef4444;">
                    Failed to load products
                </td>
            </tr>
        `);
    });
}

// ===================== ITEM MANAGEMENT =====================

function addItemToInvoice(item) {
    const existingIndex = items.findIndex(i =>
        i.product_id === item.product_id &&
        i.variant_id === item.variant_id
    );

    if (existingIndex > -1) {
        items[existingIndex].quantity += item.quantity;
        showAlert(`Updated quantity for ${item.name}`, 'info');
    } else {
        items.push(item);
        showAlert(`Added ${item.name} to invoice`, 'success');
    }

    renderItemsTable();
}

function updateItem(index, field, value) {
    if (items[index]) {
        items[index][field] = parseFloat(value) || 0;

        // 🔥 When discount changes, recalculate sale price from MRP
        if (field === 'discount') {
            const mrp = parseFloat(items[index].mrp_price || 0);
            const discountPercent = parseFloat(value || 0);

            if (mrp > 0 && discountPercent >= 0 && discountPercent <= 100) {
                const discountAmount = (mrp * discountPercent) / 100;
                items[index].price = mrp - discountAmount;
            }
        }

        // 🔥 When sale price changes, recalculate discount from MRP
        if (field === 'price') {
            const mrp = parseFloat(items[index].mrp_price || 0);
            const sale = parseFloat(value || 0);

            if (mrp > 0 && sale <= mrp) {
                items[index].discount = ((mrp - sale) / mrp) * 100;
            } else {
                items[index].discount = 0;
            }
        }

        renderItemsTable();
    }
}

function removeItem(index) {
    items.splice(index, 1);
    renderItemsTable();
}

// ===================== 🔥 CORRECTED RENDER ITEMS TABLE =====================
function renderItemsTable() {
    const tbody = $('#itemsTableBody');
    tbody.empty();

    if (items.length === 0) {
        tbody.html(`
            <tr class="empty-row">
                <td colspan="11">
                    <div class="empty-items">
                        <div class="empty-icon">🛒</div>
                        <p>No items added yet</p>
                        <button type="button" class="btn-add-first-item" onclick="openAddItemModal()">
                            + Add First Item
                        </button>
                    </div>
                </td>
            </tr>
        `);
        $('#itemsTableFooter').hide();

        // Reset summary
        $('#totalMRP').text('0.00');
        $('#totalDiscount').text('0.00');
        $('#totalTax').text('0.00');
        $('#subtotal').text('0.00');
        $('#grandTotal').text('0.00');
        calculateBalance();
        return;
    }

    $('#itemsTableFooter').show();

    // Initialize footer totals
    let footerMRP = 0;
    let footerDiscountAmount = 0;
    let footerSalePrice = 0;
    let footerTaxAmount = 0;
    let footerFinalAmount = 0;

    items.forEach((item, index) => {
        const mrpPrice = parseFloat(item.mrp_price || 0);
        const salePrice = parseFloat(item.price || 0);
        const quantity = parseFloat(item.quantity || 1);
        const discountPercent = parseFloat(item.discount || 0);
        const taxPercent = parseFloat(item.tax_percent || 0);

        // 🔥 Calculations
        const mrpTotal = quantity * mrpPrice;
        const discountAmount = (mrpTotal * discountPercent) / 100;
        const salePriceTotal = quantity * salePrice;
        const taxAmount = (salePriceTotal * taxPercent) / 100;
        const finalTotal = salePriceTotal + taxAmount;

        // Add to footer totals
        footerMRP += mrpTotal;
        footerDiscountAmount += discountAmount;
        footerSalePrice += salePriceTotal;
        footerTaxAmount += taxAmount;
        footerFinalAmount += finalTotal;

        // 🔥 Render row: Warranty → MRP (readonly) → Discount % (EDITABLE) → Sale Price (editable) → Tax → Final
        const row = `
            <tr>
                <td class="item-name">${item.name}</td>
                <td class="item-hsn">${item.hsn_sac || '-'}</td>
                <td class="item-unit">${item.unit || 'PCS'}</td>
                <td class="item-qty">
                    <input type="number" class="qty-edit" min="1" value="${quantity}"
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
                <td class="item-mrp" style="background: #f9f9f9;">₹ ${mrpPrice.toFixed(2)}</td>
                <td class="item-discount">
                    <input type="number" class="discount-edit" min="0" max="100" step="0.01" value="${discountPercent.toFixed(2)}"
                        onchange="updateItem(${index}, 'discount', this.value)">
                </td>
                <td class="item-sale-price">
                    <input type="number" class="sale-price-edit" min="0" step="0.01" value="${salePrice.toFixed(2)}"
                        onchange="updateItem(${index}, 'price', this.value)">
                </td>
                <td class="item-tax">
                    <input type="number" class="tax-edit" min="0" max="100" step="0.01" value="${taxPercent.toFixed(2)}"
                        onchange="updateItem(${index}, 'tax_percent', this.value)">
                </td>
                <td class="item-amount">₹ ${finalTotal.toFixed(2)}</td>
                <td class="item-action">
                    <button type="button" class="btn-delete" onclick="removeItem(${index})" title="Remove item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/>
                        </svg>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    // 🔥 Calculate footer percentages
    const footerDiscountPercent = footerMRP > 0 ? (footerDiscountAmount / footerMRP) * 100 : 0;
    const footerTaxPercent = footerSalePrice > 0 ? (footerTaxAmount / footerSalePrice) * 100 : 0;

    // 🔥 Update footer: MRP → Discount % → Sale Price → Tax % → Final Amount
    $('#footerMRP').text('₹ ' + footerMRP.toFixed(2));
    $('#footerDiscount').text(footerDiscountPercent.toFixed(2) + '%');
    $('#footerSalePrice').text('₹ ' + footerSalePrice.toFixed(2));
    $('#footerTax').text(footerTaxPercent.toFixed(2) + '%');
    $('#footerFinalAmount').text('₹ ' + footerFinalAmount.toFixed(2));

    // 🔥 Pass to invoice summary
    calculateInvoiceSummary(footerMRP, footerDiscountAmount, footerTaxAmount, footerFinalAmount);
}

// ===================== 🔥 CORRECTED INVOICE SUMMARY =====================
function calculateInvoiceSummary(totalMRP, totalDiscount, totalTax, subtotalFinal) {
    // Extra discount (fixed amount)
    let extraDiscount = parseFloat($('#extraDiscount').val()) || 0;

    // Extra discount (percentage)
    const discountPercent = parseFloat($('#extraDiscountPercent').val()) || 0;
    if (discountPercent > 0) {
        extraDiscount = (subtotalFinal * discountPercent) / 100;
    }

    // 🔥 Grand total calculation
    let grandTotal = subtotalFinal;
    grandTotal -= extraDiscount;

    // Extra charge
    const extraCharge = parseFloat($('#extraCharge').val()) || 0;
    grandTotal += extraCharge;

    // Auto round off
    if ($('#autoRoundOff').is(':checked')) {
        grandTotal = Math.round(grandTotal);
    }

    // 🔥 Update invoice summary: Total MRP → Total Discount → Total Tax → Subtotal → Grand Total
    $('#totalMRP').text(totalMRP.toFixed(2));
    $('#totalDiscount').text(totalDiscount.toFixed(2));
    $('#totalTax').text(totalTax.toFixed(2));
    $('#subtotal').text(subtotalFinal.toFixed(2));
    $('#grandTotal').text(grandTotal.toFixed(2));

    // Update hidden inputs
    $('#taxAmountInput').val(totalTax.toFixed(2));
    $('#discountAmountInput').val((totalDiscount + extraDiscount).toFixed(2));

    calculateBalance();
}

function updateWarrantyType(index, value) {
    items[index].warranty_type = value;
}

function updateWarrantyPeriod(index, value) {
    items[index].warranty_period = parseInt(value) || 0;
}

function calculateTotals() {
    renderItemsTable();
}

function calculateBalance() {
    const grandTotal = parseFloat($('#grandTotal').text().replace(/,/g, '')) || 0;
    const amountPaid = parseFloat($('#amountPaid').val()) || 0;
    const balance = grandTotal - amountPaid;
    $('#balanceAmount').text(balance.toFixed(2));
}

function validateForm() {
    if (!$('#customerIdInput').val()) {
        showAlert('Please select a customer', 'error');
        return false;
    }

    if (items.length === 0) {
        showAlert('Please add at least one item', 'error');
        return false;
    }

    return true;
}

function showAlert(message, type = 'success') {
    const container = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `<span>${message}</span>`;
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}

// ===================== DOCUMENT READY =====================

$(document).ready(function() {
    window.selectedCustomer = null;
    loadProducts();

    $('#searchProduct').on('input', function () {
        const value = this.value.toLowerCase();
        $('#productsTableBody tr').each(function () {
            const name = $(this).find('.product-name').text().toLowerCase();
            const code = $(this).find('.product-code').text().toLowerCase();
            if (name.includes(value) || code.includes(value)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('#searchCustomer').on('input', function () {
        const value = this.value.toLowerCase();
        $('#customersTableBody tr').each(function () {
            const name = $(this).find('.customer-name').text().toLowerCase();
            const phone = $(this).find('.customer-phone').text().toLowerCase();
            const email = $(this).find('.customer-email').text().toLowerCase();
            const company = $(this).find('.customer-company').text().toLowerCase();

            if (name.includes(value) || phone.includes(value) || email.includes(value) || company.includes(value)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('#invoiceDate').on('change', updateDueDateFromTerms);
    $('#paymentTermsDays').on('change', updateDueDateFromTerms);
    $('#dueDate').on('change', updatePaymentTermsFromDueDate);
    $('#amountPaid').on('input', calculateBalance);

    // Validation
    $('input[name="phone"]').on('input', function() {
        const value = this.value.replace(/\D/g, '');
        this.value = value;
        if (value.length !== 10) {
            showFieldError(this, 'Phone number must be 10 digits');
        } else {
            clearFieldError(this);
        }
    });

    $('input[name="billing_pincode"], input[name="shipping_pincode"]').on('input', function() {
        const value = this.value.replace(/\D/g, '');
        this.value = value;
        if (value && value.length !== 6) {
            showFieldError(this, 'Pincode must be 6 digits');
        } else {
            clearFieldError(this);
        }
    });

    $('input[name="email"]').on('blur', function() {
        const value = this.value;
        if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            showFieldError(this, 'Invalid email format');
        } else {
            clearFieldError(this);
        }
    });

    $('input[name="pan_number"]').on('input', function() {
        this.value = this.value.toUpperCase();
        if (this.value && !/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(this.value)) {
            showFieldError(this, 'Invalid PAN format (ABCDE1234F)');
        } else {
            clearFieldError(this);
        }
    });

    $('input[name="gst_number"]').on('input', function() {
        this.value = this.value.toUpperCase();
        if (this.value && !/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/.test(this.value)) {
            showFieldError(this, 'Invalid GST number');
        } else {
            clearFieldError(this);
        }
    });

    // Same as billing checkbox
    function copyBillingToShipping() {
        const form = $('#createCustomerForm');
        form.find('input[name="shipping_address"]').val(form.find('input[name="billing_address"]').val());
        form.find('input[name="shipping_city"]').val(form.find('input[name="billing_city"]').val());
        form.find('input[name="shipping_state"]').val(form.find('input[name="billing_state"]').val());
        form.find('input[name="shipping_pincode"]').val(form.find('input[name="billing_pincode"]').val());
        form.find('input[name="shipping_country"]').val(form.find('input[name="billing_country"]').val());
    }

    $('#sameBillingShipping').on('change', function() {
        if (this.checked) {
            copyBillingToShipping();
        }
    });

    $('input[name="billing_address"], input[name="billing_city"], input[name="billing_state"], input[name="billing_pincode"], input[name="billing_country"]')
        .on('input', function() {
            if ($('#sameBillingShipping').is(':checked')) {
                copyBillingToShipping();
            }
        });

    // Create customer form
    $('#createCustomerForm').submit(function(e) {
        if ($('.error-field').length) {
            showAlert('Please fix validation errors', 'error');
            return false;
        }
        e.preventDefault();
        showAlert('Creating customer...', 'info');

        $.ajax({
            url: '/admin/sales/create-customer',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showAlert('Customer created successfully!', 'success');
                    closeCreateCustomerModal();
                    selectCustomer(response.customer_id);
                } else {
                    showAlert('Error: ' + response.message, 'error');
                }
            },
            error: function(xhr) {
                showAlert('Failed to create customer: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
            }
        });
    });

    // Invoice form submission
    $('#salesInvoiceForm').submit(function(e) {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        const formData = new FormData();

        $('#salesInvoiceForm').serializeArray().forEach(item => {
            formData.append(item.name, item.value);
        });

        formData.append('items', JSON.stringify(items));
        formData.append('subtotal', $('#subtotal').text());
        formData.append('grand_total', $('#grandTotal').text());
        formData.append('tax_amount', $('#totalTax').text());
        formData.append('discount_amount', $('#totalDiscount').text());
        formData.append('balance_amount', $('#balanceAmount').text());
        formData.append('extra_discount', $('#extraDiscount').val() || 0);
        formData.append('extra_charge', $('#extraCharge').val() || 0);
        formData.append('charge_name', $('#chargeName').val() || '');
        formData.append('auto_round_off', $('#autoRoundOff').is(':checked') ? 1 : 0);

        showAlert('Creating invoice...', 'info');

        $.ajax({
            url: '{{ route('admin.sales.store') }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('Invoice created successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = '/admin/sales/' + response.invoice_id;
                    }, 1500);
                } else {
                    showAlert('Error: ' + response.message, 'error');
                }
            },
            error: function(xhr) {
                showAlert('Failed to create invoice: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
            }
        });
    });

    // Modal close handlers
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal.id === 'selectCustomerModal') {
                closeSelectCustomerModal();
            } else if (modal.id === 'createCustomerModal') {
                closeCreateCustomerModal();
            } else if (modal.id === 'addItemModal') {
                closeAddItemModal();
            }
        });
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeSelectCustomerModal();
            closeCreateCustomerModal();
            closeAddItemModal();
        }
    });

    setTimeout(function() {
        const termsDays = parseInt($('#paymentTermsDays').val());
        if (termsDays > 0) {
            updateDueDateFromTerms();
        }
    }, 100);
});
</script>
@endpush

@endsection
