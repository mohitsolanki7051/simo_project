@extends('layouts.admin')

@section('title', 'Create Quotation - Admin Panel')
@section('header-title', 'Create Quotation')

@section('content')
<div class="quotation-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Create Quotation / Estimate</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.quotations.index') }}" class="btn-small btn-secondary">
                ← Back to Quotations
            </a>
        </div>
    </div>

    <!-- Quotation Form -->
    <div class="quotation-form-wrapper">
        <form id="quotationForm" class="quotation-form">
            @csrf
            <input type="hidden" name="quotation_number" value="{{ $quotationNumber }}">
            <input type="hidden" name="warehouse_id" value="{{ $mainWarehouse->_id }}">
            <input type="hidden" id="extraDiscountTypeInput" name="extra_discount_type" value="amount">
            <input type="hidden" name="salesman_id" id="salesmanIdInput">

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
                                            <h3>Party Details</h3>
                                            <p>Select customer, dealer or distributor</p>
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
                                            </div>
                                            <div class="info-column">
                                                <div class="info-row">
                                                    <span class="info-label">Party Type:</span>
                                                    <span id="partyTypeValue" class="info-value">-</span>
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
                        <div class="col-50">
                            <!-- Quotation Details Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-header-left">
                                        <div class="section-icon">📄</div>
                                        <div class="section-title">
                                            <h3>Quotation Details</h3>
                                            <p>Quotation date and validity</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label">Quotation No.</label>
                                            <input type="text" class="form-control" value="{{ $quotationNumber }}" readonly tabindex="-1">
                                        </div>

                                        <div class="form-group col-6">
                                            <label class="form-label required">Quotation Date</label>
                                            <input type="date" name="quotation_date" id="quotationDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-6">
                                            <label class="form-label">Valid Till</label>
                                            <input type="date" name="valid_till" id="validTill" class="form-control">
                                            <small style="font-size: 9px; color: #666;">Offer validity date</small>
                                        </div>

                                        <div class="form-group col-6">
                                            <label class="form-label">Assigned Salesman</label>
                                            <div class="salesman-display" id="salesmanNameDisplay">
                                                —
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
                                    <p>Add products to quotation</p>
                                </div>
                            </div>
                            <button type="button" class="btn-add-item" id="addItemBtn" onclick="openAddItemModal()" disabled style="opacity: 0.5; cursor: not-allowed;">
                                + Add Item
                            </button>
                        </div>

                        <div class="section-body">
                            <div class="items-table-container quotation-mode">
                                <table class="items-table" id="mainItemsTable">
                                    <thead>
                                        <tr>
                                            <th class="th-sno">#</th>
                                            <th class="th-item">Item Name</th>
                                            <th class="th-unit">Unit</th>
                                            <th class="th-qtys">Qty</th>
                                            <th class="th-warranty">Warranty</th>
                                            <th class="th-mrp">MRP (₹)</th>
                                            <th class="th-discount">Disc %</th>
                                            <th class="th-price">Price (₹)</th>
                                            <th class="th-amount">Amount (₹)</th>
                                            <th class="th-action">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                    </tbody>
                                    <tfoot id="itemsTableFooter" style="display:none; background-color: #f0f0f0; font-weight: 600; border-top: 2px solid #ddd;">
                                        <tr>
                                            <td colspan="5" style="text-align: right; padding: 10px 8px; font-size: 11px;">
                                                <strong>TOTAL:</strong>
                                            </td>
                                            <td style="padding: 10px 8px; text-align: center; font-size: 11px;" id="footerMRP">₹ 0.00</td>
                                            <td style="padding: 10px 8px; text-align: center; font-size: 11px;" id="footerDiscount">₹ 0.00</td>
                                            <td style="padding: 10px 8px; text-align: center; font-size: 11px;" id="footerAmount">₹ 0.00</td>
                                            <td colspan="2" style="padding: 10px 8px;"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Notes + Summary Row -->
                    <div class="bottom-row">

                        <!-- LEFT: Notes -->
                        <div class="bottom-col-left">
                            <div class="form-section" style="height:100%;">
                                <div class="section-header">
                                    <div class="section-header-left">
                                        <div class="section-icon">📝</div>
                                        <div class="section-title">
                                            <h3>Notes</h3>
                                            <p>Remarks for the customer</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="section-body">
                                    <textarea name="notes" class="form-control" rows="6"
                                        placeholder="Add any notes or remarks for the customer..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT: Quotation Summary -->
                        <div class="bottom-col-right">
                            <div class="summary-section">
                                <div class="summary-header">
                                    <div class="summary-icon">💰</div>
                                    <h3>Quotation Summary</h3>
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
                                        <span class="summary-label" style="font-weight:600;">Subtotal</span>
                                        <span class="summary-value">₹ <span id="subtotal">0.00</span></span>
                                    </div>
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

                    </div>

                    <!-- Submit Button -->
                    <div style="display:flex; justify-content:flex-end; margin-top:15px;">
                        <button type="submit" class="btn-submit-quotation">
                            <span class="btn-icon">💾</span>
                            Save Quotation
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
                            <th>Name</th>
                            <th>Type</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="partiesTableBody"></tbody>
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
                            <label class="form-label">Assign Salesman</label>
                            <div class="select-wrapper">
                                <select name="salesman_id" class="form-control">
                                    <option value="">None</option>
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
                    <div class="section-header-small">
                        <h5>Billing Address</h5>
                        <label class="form-check">
                            <input type="checkbox" id="sameBillingShipping" class="form-check-input">
                            <span class="form-check-label">Same as Shipping</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" name="billing_address" class="form-control">
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
                        <input type="text" name="shipping_address" class="form-control">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="form-group col-6">
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
            <div class="search-container" style="margin-bottom:12px;">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchProduct" class="search-input" placeholder="Search items by name, SKU or barcode...">
                </div>
            </div>
            <div class="products-table-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th style="width:35%;">Item Name</th>
                            <th style="width:12%;">Item Code</th>
                            <th style="width:10%; text-align:right;">MRP (₹)</th>
                            <th style="width:12%; text-align:right;">Price (₹)</th>
                            <th style="width:10%; text-align:center;">Stock</th>
                            <th style="width:10%; text-align:center;">Qty</th>
                            <th style="width:11%; text-align:center;">Select</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody"></tbody>
                </table>
                <div id="productsLoading" class="loading-state">
                    <div class="loading-spinner"></div>
                    <p>Loading products...</p>
                </div>
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
/* ── Base ──────────────────────────────────────────── */
.quotation-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 11px;
    line-height: 1.3;
    color: #333;
}
#alertContainer { position:fixed; top:15px; right:15px; z-index:9999; }
.alert { padding:10px 14px; margin-bottom:8px; border-radius:4px; font-size:10px; font-weight:500; animation:slideInRight .3s ease; box-shadow:0 2px 8px rgba(0,0,0,.1); }
.alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert-error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.alert-info    { background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; }
@keyframes slideInRight { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }

/* ── Page Header ───────────────────────────────────── */
.page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; border-bottom:1px solid #ddd; padding-bottom:10px; }
.header-left .page-title { font-size:15px; font-weight:600; color:#333; margin:0 0 3px 0; }
.btn-small { padding:5px 10px; background:white; color:#28a745; border:1px solid #dee2e6; border-radius:4px; font-size:14px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:3px; text-decoration:none; }

/* ── Form Wrapper ──────────────────────────────────── */
.quotation-form-wrapper { background:white; border:1px solid #ddd; border-radius:4px; padding:15px; }
.form-row { display:flex; flex-direction:row; gap:15px; }
.form-col-main { width:100%; }
.two-col-row { display:flex; gap:15px; width:100%; }
.col-50 { width:50%; }
@media(max-width:768px){ .two-col-row{flex-direction:column} .col-50{width:100%} }

/* ── Form Section ──────────────────────────────────── */
.form-section { background:white; border:1px solid #ddd; border-radius:4px; margin-bottom:15px; overflow:hidden; }
.section-header { display:flex; justify-content:space-between; align-items:center; padding:12px 15px; background:#f8f9fa; border-bottom:1px solid #ddd; }
.section-header-left { display:flex; align-items:center; gap:10px; }
.section-icon { width:28px; height:28px; background:#28a745; border-radius:4px; display:flex; align-items:center; justify-content:center; color:white; font-size:13px; flex-shrink:0; }
.section-title h3 { font-size:13px; font-weight:600; color:#333; margin:0; }
.section-title p { font-size:10px; color:#666; margin:1px 0 0 0; }
.section-body { padding:15px; }
.form-group { margin-bottom:12px; }
.form-label { display:block; font-size:11px; font-weight:500; color:#555; margin-bottom:3px; }
.form-label.required::after { content:' *'; color:#dc3545; }
.form-control { width:100%; padding:6px 10px; border:1px solid #ccc; border-radius:3px; font-size:10px; background:#fff; box-sizing:border-box; }
.form-control:focus { outline:none; border-color:#666; }
.form-row .col-6 { width:50%; }
.form-row .col-12 { width:100%; }

/* ── Party Section ─────────────────────────────────── */
.btn-select-customer { width:180px; padding:8px 12px; background:#28a745; color:white; border:none; border-radius:3px; font-size:11px; font-weight:500; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:5px; }
.selected-customer-details { padding:16px; background:#fafafa; border-radius:4px; border:1px solid #ddd; }
.customer-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:4px; padding-bottom:4px; border-bottom:1px solid #eee; }
.customer-header h4 { font-size:13px; font-weight:600; color:#333; margin:0; }
.btn-change-customer { padding:3px 8px; background:#666; color:white; border:none; border-radius:3px; font-size:10px; cursor:pointer; }
.customer-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:10px; }
.info-row { display:flex; align-items:center; margin-bottom:6px; }
.info-label { font-size:10px; font-weight:500; color:#555; width:60px; flex-shrink:0; }
.info-value { font-size:10px; color:#333; }
.address-section { padding-top:10px; border-top:1px solid #eee; }
.address-header h5 { font-size:11px; font-weight:600; color:#333; margin:0 0 10px 0; }
.address-grid { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
.address-card { background:#fafafa; border:1px solid #ddd; border-radius:4px; padding:12px; }
.address-card-header { margin-bottom:8px; padding-bottom:6px; border-bottom:1px solid #eee; }
.address-type { font-size:10px; font-weight:600; color:#333; }
.address-content { font-size:10px; color:#555; line-height:1.4; }
.salesman-display { padding:6px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; font-size:10px; color:#333; }

/* ── Items Table ───────────────────────────────────── */
.btn-add-item { padding:6px 14px; background:#28a745; color:white; border:none; border-radius:3px; font-size:11px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:4px; }
.btn-add-item:hover { background:#218838; }

.items-table-container {
    overflow-x: auto;
    width: 100%;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    table-layout: fixed;
}

/* Column widths */
.items-table .th-sno      { width: 36px; }
.items-table .th-item     { width: 30%; }
.items-table .th-unit     { width: 52px; }
.items-table .th-qtys     { width: 60px; }
.items-table .th-warranty { width: 130px; }
.items-table .th-mrp      { width: 80px; }
.items-table .th-discount { width: 74px; }
.items-table .th-price    { width: 100px; }
.items-table .th-amount   { width: 84px; }
.items-table .th-action   { width: 42px; }

.items-table thead tr {
    background: #f8f9fa;
}

.items-table th {
    padding: 9px 8px;
    text-align: center;
    font-weight: 600;
    color: #444;
    border-bottom: 2px solid #ddd;
    border-right: 1px solid #e5e7eb;
    white-space: nowrap;
    font-size: 11px;
    background: #f3f4f6;
}
.items-table th:last-child { border-right: none; }
.items-table th.th-item   { text-align: left; padding-left: 10px; }

.items-table td {
    padding: 7px 8px;
    border-bottom: 1px solid #eee;
    border-right: 1px solid #f0f0f0;
    vertical-align: middle;
    text-align: center;
    font-size: 11px;
    color: #333;
}
.items-table td:last-child { border-right: none; }
.items-table td.item-name  { text-align: left; font-weight: 500; padding-left: 10px; }
.items-table td.item-sno   { color: #888; font-size: 10px; }
.items-table td.item-unit  { color: #555; font-size: 10px; }
.items-table td.item-amount { font-weight: 600; color: #166534; background: #f0fdf4; }

.items-table tbody tr:hover { background: #fafafa; }
.items-table tbody tr:last-child td { border-bottom: none; }

/* Inputs inside table */
.items-table input[type="number"] {
    width: 100%;
    padding: 4px 5px;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    font-size: 11px;
    text-align: center;
    background: #fff;
    box-sizing: border-box;
    transition: border-color .15s;
}
.items-table input[type="number"]:focus { outline:none; border-color:#28a745; background:#f0fdf4; }

/* Warranty cell */
.warranty-wrapper { display:flex; gap:3px; align-items:center; justify-content:center; }
.warranty-input   { width:38px !important; padding:4px 3px !important; font-size:10px !important; }
.warranty-select  { font-size:10px; padding:4px 3px; border:1px solid #d1d5db; border-radius:3px; background:#fff; flex:1; }

/* Delete btn */
.btn-delete { width:26px; height:26px; border:none; background:#fee2e2; color:#dc2626; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; margin:0 auto; transition:all .15s; }
.btn-delete:hover { background:#fecaca; color:#b91c1c; }

/* Empty row */
.empty-items { text-align:center; padding:35px 20px; }
.empty-items .empty-icon { font-size:36px; opacity:.3; margin-bottom:10px; }
.empty-items p { font-size:12px; color:#6b7280; margin-bottom:10px; }
.btn-add-first-item { padding:6px 14px; background:#28a745; color:white; border:none; border-radius:3px; font-size:10px; cursor:pointer; }

/* ── Summary ───────────────────────────────────────── */
.invoice-bottom-layout { display:flex; flex-direction:column; gap:15px; }
.invoice-row { display:flex; gap:15px; }
.invoice-col { flex:1; }
.summary-section { background:white; border:1px solid #ddd; border-radius:4px; overflow:hidden; }
.summary-header { padding:12px 15px; background:#f8f9fa; color:#333; display:flex; align-items:center; gap:8px; border-bottom:1px solid #ddd; }
.summary-header h3 { font-size:13px; font-weight:600; margin:0; }
.summary-body { padding:15px; }
.summary-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.summary-label { font-size:11px; color:#555; }
.summary-label a { color:#28a745; text-decoration:none; font-weight:500; font-size:10px; padding:4px 8px; background:#e8f5e9; border-radius:3px; border:1px dashed #28a745; display:inline-block; }
.summary-label a:hover { background:#28a745; color:white; }
.summary-label a.disabled-link { pointer-events:none; opacity:.5; background:#e0e0e0; color:#999; border-color:#ccc; }
.summary-value { font-size:11px; font-weight:600; color:#333; }
.summary-divider { height:1px; background:#eee; margin:10px 0; }
.summary-input { width:100%; padding:4px; font-size:10px; border:1px solid #ddd; border-radius:3px; }
.summary-input-small { width:70px; padding:4px; font-size:10px; border:1px solid #ddd; border-radius:3px; }
.total-row { margin-top:10px; padding-top:10px; border-top:2px solid #333; }
.total-row .summary-label { font-size:12px; font-weight:700; color:#333; }
.total-row .summary-value { font-size:15px; font-weight:700; color:#28a745; }

/* ── Bottom Row (Notes + Summary) ──────────────────── */
.bottom-row {
    display: flex;
    gap: 15px;
    align-items: flex-start;
    margin-bottom: 0;
}
.bottom-col-left  { width: 50%; }
.bottom-col-right { width: 50%; }
@media(max-width:768px) {
    .bottom-row { flex-direction: column; }
    .bottom-col-left, .bottom-col-right { width: 100%; }
}

/* ── Submit ─────────────────────────────────────────── */
.btn-submit-quotation { width:25%; padding:10px 15px; background:#28a745; color:white; border:none; border-radius:4px; font-size:11px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; margin-top:15px; }
.btn-submit-quotation:hover { background:#218838; }
.btn-submit-quotation:disabled { opacity:.6; cursor:not-allowed; }
.spinner { display:inline-block; width:12px; height:12px; border:2px solid rgba(255,255,255,.3); border-radius:50%; border-top-color:#fff; animation:spin .8s linear infinite; }
@keyframes spin { to{transform:rotate(360deg)} }

/* ── Modals ─────────────────────────────────────────── */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:1000; align-items:center; justify-content:center; }
.modal-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.4); }
.modal-content { position:relative; background:white; border-radius:6px; width:90%; max-width:820px; max-height:90vh; overflow-y:auto; animation:modalFadeIn .2s ease; box-shadow:0 4px 20px rgba(0,0,0,.15); }
.modal-xl { max-width:820px; }
@keyframes modalFadeIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.modal-header { display:flex; align-items:center; gap:10px; padding:15px 20px; border-bottom:1px solid #ddd; background:#f8f9fa; }
.modal-title-section { flex:1; }
.modal-title { font-size:14px; font-weight:600; color:#333; margin:0; }
.modal-subtitle { font-size:10px; color:#666; margin-top:2px; }
.modal-close { background:none; border:none; font-size:18px; color:#666; cursor:pointer; padding:4px; }
.modal-icon { width:32px; height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:14px; color:white; flex-shrink:0; background:#28a745; }
.modal-body { padding:20px; }
.modal-actions { display:flex; justify-content:flex-end; gap:8px; padding:15px 20px; border-top:1px solid #ddd; background:#fafafa; }
.btn-modal { padding:7px 16px; border:none; border-radius:3px; font-size:11px; font-weight:500; cursor:pointer; min-width:70px; }
.btn-cancel { background:#f8f9fa; color:#333; border:1px solid #ccc; }
.btn-primary { background:#28a745; color:white; }

/* ── Party Modal ─────────────────────────────────────── */
.party-type-tabs { display:flex; gap:8px; margin-bottom:15px; padding:5px; background:#f8f9fa; border-radius:6px; }
.party-type-tab { flex:1; padding:8px 12px; background:transparent; border:none; border-radius:4px; font-size:11px; font-weight:500; color:#6c757d; cursor:pointer; text-align:center; transition:all .2s; }
.party-type-tab.active { background:#28a745; color:white; }
.party-modal-header { display:flex; justify-content:space-between; align-items:center; gap:15px; margin-bottom:15px; }
.search-container { flex:1; }
.search-box { position:relative; }
.search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:11px; color:#666; }
.search-input { width:100%; padding:7px 10px 7px 30px; border:1px solid #ccc; border-radius:3px; font-size:11px; box-sizing:border-box; }
.btn-create-new-party { padding:7px 14px; background:#28a745; color:white; border:none; border-radius:3px; font-size:11px; font-weight:500; cursor:pointer; display:flex; align-items:center; gap:5px; white-space:nowrap; }
.parties-table-container { max-height:380px; overflow-y:auto; border:1px solid #ddd; border-radius:4px; }
.parties-table { width:100%; border-collapse:collapse; font-size:11px; }
.parties-table th { background:#f3f4f6; padding:9px 10px; text-align:left; font-weight:600; border-bottom:2px solid #ddd; position:sticky; top:0; z-index:1; color:#444; }
.parties-table td { padding:9px 10px; border-bottom:1px solid #eee; }
.parties-table tr:hover { background:#f9f9f9; }
.btn-select-party-row { background:none; border:none; cursor:pointer; font-size:16px; color:#28a745; }
.status-badge { display:inline-block; padding:2px 6px; border-radius:10px; font-size:9px; font-weight:500; }
.status-active { background:#d4edda; color:#155724; }
.status-inactive { background:#f8d7da; color:#721c24; }
.loading-state { padding:30px 20px; text-align:center; }
.loading-spinner { width:20px; height:20px; border:2px solid #eee; border-top-color:#555; border-radius:50%; animation:spin 1s linear infinite; margin:0 auto 8px; }
.no-data-state { padding:30px 20px; text-align:center; }
.no-data-icon { font-size:24px; opacity:.3; margin-bottom:8px; }
.btn-create-first-party { padding:6px 12px; background:#28a745; color:white; border:none; border-radius:3px; font-size:10px; cursor:pointer; }

/* ── Products Modal Table ────────────────────────────── */
.products-table-container {
    max-height: 420px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.products-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    table-layout: fixed;
}

.products-table th {
    background: #f3f4f6;
    padding: 9px 10px;
    font-weight: 600;
    color: #444;
    border-bottom: 2px solid #ddd;
    border-right: 1px solid #e5e7eb;
    position: sticky;
    top: 0;
    z-index: 1;
    white-space: nowrap;
    font-size: 11px;
}
.products-table th:last-child { border-right: none; }

.products-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #eee;
    border-right: 1px solid #f0f0f0;
    vertical-align: middle;
    font-size: 11px;
    color: #333;
}
.products-table td:last-child { border-right: none; }
.products-table tbody tr:hover { background: #f9fafb; }
.products-table tbody tr:last-child td { border-bottom: none; }

.products-table td.product-name { font-weight: 500; }
.products-table td.product-code { color: #6b7280; font-size: 10px; font-family: monospace; }
.products-table td.product-mrp  { text-align: right; }
.products-table td.product-price { text-align: right; }
.products-table td.product-stock { text-align: center; }
.products-table td.product-qty  { text-align: center; }

.products-table input[type="number"] {
    width: 56px;
    padding: 4px 6px;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    font-size: 11px;
    text-align: center;
    box-sizing: border-box;
}
.products-table input[type="number"]:focus { outline:none; border-color:#28a745; }

.products-table input[type="checkbox"] {
    width: 15px;
    height: 15px;
    cursor: pointer;
    accent-color: #28a745;
}

/* ── Misc ─────────────────────────────────────────────── */
.party-type-badge { display:inline-block; padding:2px 8px; border-radius:12px; font-size:9px; font-weight:600; text-transform:uppercase; background:#e7f1ff; color:#0066cc; margin-left:8px; }
.party-type-badge.customer { background:#d4edda; color:#155724; }
.party-type-badge.dealer { background:#cce5ff; color:#004085; }
.party-type-badge.distributor { background:#fff3cd; color:#856404; }
.form-section-small { margin-bottom:15px; padding-bottom:12px; border-bottom:1px solid #eee; }
.form-section-small h5 { font-size:11px; font-weight:600; color:#333; margin:0 0 10px 0; }
.section-header-small { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.form-check { display:flex; align-items:center; gap:5px; }
.form-check-input { width:13px; height:13px; }
.form-check-label { font-size:10px; color:#555; }
@media(max-width:768px){ .invoice-row{flex-direction:column} .party-modal-header{flex-direction:column} }
</style>
@endpush

@push('scripts')
<script>
let items = [];
let isSubmitting = false;
let currentPartyType = 'all';

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
    $.get('{{ route('admin.quotations.parties.list') }}', {
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
                    <td>${party.phone || '-'}</td>
                    <td>${party.email || '-'}</td>
                    <td><span class="status-badge ${statusClass}">${party.status}</span></td>
                    <td><button type="button" class="btn-select-party-row" onclick="selectParty('${party.id}')">✓</button></td>
                </tr>
            `);
        });
    }).fail(function() { loading.hide(); showAlert('Failed to load parties', 'error'); });
}
function selectParty(partyId) {
    $.get('{{ route('admin.quotations.get-party-details', ':id') }}'.replace(':id', partyId), function(res) {
        if (!res.success) return;
        const p = res.party;
        $('#selectPartyBtn').hide();
        $('#selectedPartyDetails').show();
        $('#partyNameDisplay').text(p.name);
        $('#partyTypeBadge').text(p.party_type_text).attr('class', `party-type-badge ${p.party_type}`);
        $('#partyPhone').text(p.phone || '-');
        $('#partyEmail').text(p.email || '-');
        $('#partyTypeValue').text(p.party_type_text);
        $('#partyIdInput').val(p.id);
        $('#partyTypeInput').val(p.party_type);
        updatePriceColumnHeaders();
        updateAddItemButtonState();
        items = []; renderItemsTable();
        if (p.salesman_id && p.salesman_name) {
            $('#salesmanIdInput').val(p.salesman_id);
            $('#salesmanNameDisplay').text('👤 ' + p.salesman_name);
        } else {
            $('#salesmanIdInput').val('');
            $('#salesmanNameDisplay').text('—');
        }
        $('#billingAddressText').text(p.billing_address || 'No billing address');
        $('#shippingAddressText').text(p.shipping_address || p.billing_address || 'No shipping address');
        closeSelectPartyModal();
        showAlert('Party selected successfully', 'success');
    });
}
$(document).on('click', '.party-type-tab', function() {
    $('.party-type-tab').removeClass('active');
    $(this).addClass('active');
    currentPartyType = $(this).data('type');
    loadParties($('#searchParty').val());
});

// ===================== PARTY TYPE HELPERS =====================
function getCurrentPartyType() { return $('#partyTypeInput').val() || null; }
function updateAddItemButtonState() {
    const pt = getCurrentPartyType();
    const btn = $('#addItemBtn');
    if (pt) { btn.prop('disabled', false).css({opacity:'1',cursor:'pointer'}); }
    else     { btn.prop('disabled', true).css({opacity:'0.5',cursor:'not-allowed'}); }
}
function getPriceColumnHeader() {
    switch(getCurrentPartyType()) {
        case 'dealer':      return 'Dealer Price (₹)';
        case 'distributor': return 'Distributor Price (₹)';
        default:            return 'Sale Price (₹)';
    }
}
function updatePriceColumnHeaders() { $('.th-price').text(getPriceColumnHeader()); }
function getProductPrice(product, partyType) {
    switch(partyType) {
        case 'dealer':      return parseFloat(product.dealer_price || 0);
        case 'distributor': return parseFloat(product.distributor_price || 0);
        default:            return parseFloat(product.sale_price || 0);
    }
}
function calculateDiscountPercentage(mrp, price) {
    if (mrp <= 0 || price <= 0) return 0;
    return ((mrp - price) / mrp * 100).toFixed(2);
}

// ===================== ADD ITEM MODAL =====================
function openAddItemModal() {
    if (!getCurrentPartyType()) { showAlert('Please select a party first', 'error'); return; }
    $('#addItemModal').css('display', 'flex');
    $('#searchProduct').val('');
    loadProducts();
}
function closeAddItemModal() { $('#addItemModal').hide(); $('#searchProduct').val(''); }

function loadProducts(search = '') {
    const tbody = $('#productsTableBody');
    const loading = $('#productsLoading');
    const partyType = getCurrentPartyType();
    tbody.empty(); loading.show();
    $.get('{{ route('admin.quotations.get-main-warehouse-products') }}', { search }, function(response) {
        loading.hide();
        if (!response.products || response.products.length === 0) {
            tbody.html(`<tr><td colspan="7" style="text-align:center;padding:40px 20px;color:#6b7280;"><div style="font-size:32px;opacity:.3;margin-bottom:8px;">📦</div><p style="font-size:11px;">No products found</p></td></tr>`);
            return;
        }
        response.products.forEach(product => {
            const price = getProductPrice(product, partyType);
            const mrp   = parseFloat(product.mrp_price || 0);
            const discPct = calculateDiscountPercentage(mrp, price);
            const stockLabel = `${product.current_stock} ${product.unit}`;
            tbody.append(`
                <tr>
                    <td class="product-name">${product.name}</td>
                    <td class="product-code">${product.sku || '-'}</td>
                    <td class="product-mrp">₹ ${mrp.toFixed(2)}</td>
                    <td class="product-price">
                        ₹ ${price.toFixed(2)}
                        ${discPct > 0 ? `<br><small style="color:#16a34a;font-size:9px;">(${discPct}% off)</small>` : ''}
                    </td>
                    <td class="product-stock">${stockLabel}</td>
                    <td class="product-qty">
                        <input type="number" class="qty-input" min="1" max="${product.current_stock}" value="1"
                            data-product-id="${product.id}"
                            data-variant-id="${product.variant_id || ''}"
                            data-type="${product.type}"
                            data-price="${price}"
                            data-mrp="${mrp}"
                            data-stock="${product.current_stock}"
                            data-name="${product.name}"
                            data-sku="${product.sku}"
                            data-unit="${product.unit}"
                            data-warranty-type="${product.warranty_type}"
                            data-warranty-period="${product.warranty_period}">
                    </td>
                    <td style="text-align:center;">
                        <input type="checkbox" class="select-product">
                    </td>
                </tr>
            `);
        });
    }).fail(function() {
        loading.hide();
        tbody.html(`<tr><td colspan="7" style="text-align:center;padding:30px;color:#ef4444;">Failed to load products</td></tr>`);
    });
}

function addSelectedProducts() {
    $('#productsTableBody tr').each(function() {
        const checkbox = $(this).find('.select-product');
        if (!checkbox.is(':checked')) return;
        const input = $(this).find('.qty-input');
        const qty = parseFloat(input.val()) || 0;
        const maxStock = parseFloat(input.data('stock'));
        if (qty <= 0) return;
        if (qty > maxStock) { showAlert(`Only ${maxStock} items available`, 'error'); return; }
        const price    = parseFloat(input.data('price')) || 0;
        const mrpPrice = parseFloat(input.data('mrp')) || 0;
        let autoDiscount = 0;
        if (mrpPrice > 0 && mrpPrice > price) {
            autoDiscount = ((mrpPrice - price) / mrpPrice) * 100;
        }
        addItemToQuotation({
            product_id: input.data('product-id'),
            variant_id: input.data('variant-id'),
            product_type: input.data('type'),
            name: input.data('name'),
            sku: input.data('sku'),
            mrp_price: mrpPrice,
            price: price,
            quantity: qty,
            discount: parseFloat(autoDiscount.toFixed(2)),
            unit: input.data('unit') || 'PCS',
            warranty_type: input.data('warranty-type') || 'none',
            warranty_period: parseInt(input.data('warranty-period')) || 0,
            party_type: getCurrentPartyType()
        });
    });
    closeAddItemModal();
}

function addItemToQuotation(item) {
    const existingIndex = items.findIndex(i => i.product_id === item.product_id && i.variant_id === item.variant_id);
    if (existingIndex > -1) {
        items[existingIndex].quantity += item.quantity;
        showAlert(`Updated quantity for ${item.name}`, 'info');
    } else {
        items.push(item);
        showAlert(`Added ${item.name} to quotation`, 'success');
    }
    renderItemsTable();
}

// ===================== ITEMS TABLE RENDER =====================
function updateItem(index, field, value) {
    if (!items[index]) return;
    items[index][field] = parseFloat(value) || 0;
    if (field === 'discount') {
        const mrp = parseFloat(items[index].mrp_price || 0);
        const pct = parseFloat(value || 0);
        if (mrp > 0 && pct >= 0 && pct <= 100) {
            items[index].price = mrp - (mrp * pct / 100);
        }
    }
    if (field === 'price') {
        const mrp  = parseFloat(items[index].mrp_price || 0);
        const sale = parseFloat(value || 0);
        items[index].discount = (mrp > 0 && sale <= mrp) ? ((mrp - sale) / mrp) * 100 : 0;
    }
    renderItemsTable();
}
function removeItem(index) { items.splice(index, 1); renderItemsTable(); }
function updateWarrantyType(index, value)   { items[index].warranty_type   = value; }
function updateWarrantyPeriod(index, value) { items[index].warranty_period = parseInt(value) || 0; }

function renderItemsTable() {
    const tbody = $('#itemsTableBody');
    tbody.empty();
    enableExtraFields();

    if (items.length === 0) {
        tbody.html(`
            <tr class="empty-row">
                <td colspan="10">
                    <div class="empty-items">
                        <div class="empty-icon">📄</div>
                        <p>No items added yet</p>
                        <button type="button" class="btn-add-first-item" onclick="openAddItemModal()">+ Add First Item</button>
                    </div>
                </td>
            </tr>
        `);
        $('#itemsTableFooter').hide();
        $('#totalMRP, #totalDiscount, #subtotal, #grandTotal').text('0.00');
        return;
    }

    $('#itemsTableFooter').show();
    let footerMRP = 0, footerDiscountAmt = 0, footerAmount = 0;

    items.forEach((item, index) => {
        const mrp      = parseFloat(item.mrp_price || 0);
        const sale     = parseFloat(item.price || 0);
        const qty      = parseFloat(item.quantity || 1);
        const discPct  = parseFloat(item.discount || 0);
        const mrpTotal = qty * mrp;
        const discAmt  = (mrp - sale) * qty;
        const lineAmt  = qty * sale;

        footerMRP        += mrpTotal;
        footerDiscountAmt += discAmt;
        footerAmount      += lineAmt;

        tbody.append(`
            <tr>
                <td class="item-sno">${index + 1}</td>
                <td class="item-name">${item.name}</td>
                <td class="item-unit">${item.unit || 'PCS'}</td>
                <td class="item-qty">
                    <input type="number" class="qty-edit" min="1" value="${qty}"
                        onchange="updateItem(${index}, 'quantity', this.value)">
                </td>
                <td class="item-warranty">
                    <div class="warranty-wrapper">
                        <input type="number" min="0" value="${item.warranty_period || 0}"
                            onchange="updateWarrantyPeriod(${index}, this.value)" class="warranty-input">
                        <select onchange="updateWarrantyType(${index}, this.value)" class="warranty-select">
                            <option value="none"  ${item.warranty_type === 'none'  ? 'selected' : ''}>None</option>
                            <option value="month" ${item.warranty_type === 'month' ? 'selected' : ''}>Month(s)</option>
                            <option value="year"  ${item.warranty_type === 'year'  ? 'selected' : ''}>Year(s)</option>
                        </select>
                    </div>
                </td>
                <td class="item-mrp" style="background:#f9fafb;">₹ ${mrp.toFixed(2)}</td>
                <td class="item-discount">
                    <input type="number" class="discount-edit" min="0" max="100" step="0.01" value="${discPct.toFixed(2)}"
                        onchange="updateItem(${index}, 'discount', this.value)">
                </td>
                <td class="item-price">
                    <input type="number" class="sale-price-edit" min="0" step="0.01" value="${sale.toFixed(2)}"
                        onchange="updateItem(${index}, 'price', this.value)">
                </td>
                <td class="item-amount">₹ ${lineAmt.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn-delete" onclick="removeItem(${index})" title="Remove">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/>
                        </svg>
                    </button>
                </td>
            </tr>
        `);
    });

    $('#footerMRP').text('₹ ' + footerMRP.toFixed(2));
    $('#footerDiscount').text('₹ ' + footerDiscountAmt.toFixed(2));
    $('#footerAmount').text('₹ ' + footerAmount.toFixed(2));
    calculateQuotationSummary(footerMRP, footerDiscountAmt, footerAmount);
}

function calculateQuotationSummary(totalMRP, totalDiscount, subtotal) {
    let extraDiscount = 0;
    const discountType = $('#extraDiscountTypeInput').val();
    if (discountType === 'percent') {
        const pct = parseFloat($('#extraDiscountPercent').val()) || 0;
        if (pct > 0 && subtotal > 0) extraDiscount = (subtotal * pct) / 100;
    } else {
        extraDiscount = parseFloat($('#extraDiscount').val()) || 0;
    }
    let grandTotal = subtotal - extraDiscount + (parseFloat($('#extraCharge').val()) || 0);
    if ($('#autoRoundOff').is(':checked')) grandTotal = Math.round(grandTotal);

    $('#totalMRP').text(totalMRP.toFixed(2));
    $('#totalDiscount').text(totalDiscount.toFixed(2));
    $('#subtotal').text(subtotal.toFixed(2));
    $('#grandTotal').text(grandTotal.toFixed(2));
}

function calculateTotals() { renderItemsTable(); }

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

// ===================== SEARCH =====================
$('#searchParty').on('input', function() {
    const v = this.value.toLowerCase();
    $('#partiesTableBody tr').each(function() {
        const name  = $(this).find('td:eq(0)').text().toLowerCase();
        const phone = $(this).find('td:eq(2)').text().toLowerCase();
        const email = $(this).find('td:eq(3)').text().toLowerCase();
        $(this).toggle(name.includes(v) || phone.includes(v) || email.includes(v));
    });
});
$('#searchProduct').on('input', function() {
    const v = this.value.toLowerCase();
    $('#productsTableBody tr').each(function() {
        const name = $(this).find('.product-name').text().toLowerCase();
        const code = $(this).find('.product-code').text().toLowerCase();
        $(this).toggle(name.includes(v) || code.includes(v));
    });
});

// ===================== CREATE PARTY FORM =====================
function copyBillingToShipping() {
    const f = $('#createPartyForm');
    f.find('[name="shipping_address"]').val(f.find('[name="billing_address"]').val());
    f.find('[name="shipping_city"]').val(f.find('[name="billing_city"]').val());
    f.find('[name="shipping_state"]').val(f.find('[name="billing_state"]').val());
    f.find('[name="shipping_pincode"]').val(f.find('[name="billing_pincode"]').val());
    f.find('[name="shipping_country"]').val(f.find('[name="billing_country"]').val());
}
$('#sameBillingShipping').on('change', function() { if (this.checked) copyBillingToShipping(); });
$('[name="billing_address"],[name="billing_city"],[name="billing_state"],[name="billing_pincode"],[name="billing_country"]').on('input', function() {
    if ($('#sameBillingShipping').is(':checked')) copyBillingToShipping();
});
$('#createPartyForm').submit(function(e) {
    e.preventDefault();
    const phone = $('[name="phone"]').val();
    if (!/^[6-9]\d{9}$/.test(phone)) { showAlert('Please enter a valid 10-digit phone number', 'error'); return; }
    const email = $('[name="email"]').val();
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showAlert('Please enter a valid email address', 'error'); return; }
    const formData = $(this).serializeArray();
    formData.push({ name: 'same_billing_shipping', value: $('#sameBillingShipping').is(':checked') ? '1' : '0' });
    $.ajax({
        url: '{{ route('admin.quotations.create-party') }}',
        type: 'POST',
        data: $.param(formData),
        success: function(response) {
            if (response.success) { showAlert('Party created successfully!', 'success'); closeCreatePartyModal(); selectParty(response.party_id); }
            else { showAlert('Error: ' + response.message, 'error'); }
        },
        error: function(xhr) {
            let msg = 'Failed to create party';
            if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(', ');
            else if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
            showAlert(msg, 'error');
        }
    });
});

// ===================== FORM SUBMIT =====================
function validateForm() {
    if (!$('#partyIdInput').val()) { showAlert('Please select a party', 'error'); return false; }
    if (items.length === 0) { showAlert('Please add at least one item', 'error'); return false; }
    return true;
}
function showAlert(message, type = 'success') {
    const c = document.getElementById('alertContainer');
    const a = document.createElement('div');
    a.className = `alert alert-${type}`;
    a.innerHTML = `<span>${message}</span>`;
    c.appendChild(a);
    setTimeout(() => a.remove(), 5000);
}

$(document).ready(function() {
    updateAddItemButtonState();
    $('#extraDiscount, #extraDiscountPercent, #extraCharge').on('input', calculateTotals);
    const validTill = new Date(); validTill.setDate(validTill.getDate() + 30);
    $('#validTill').val(validTill.toISOString().split('T')[0]);
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') { closeSelectPartyModal(); closeCreatePartyModal(); closeAddItemModal(); }
    });
    $('#quotationForm').submit(function(e) {
        e.preventDefault();
        if (isSubmitting) { showAlert('Please wait...', 'info'); return; }
        if (!validateForm()) return;
        isSubmitting = true;
        const $btn = $('.btn-submit-quotation');
        const orig = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner"></span> Creating...');
        const fd = new FormData();
        $(this).serializeArray().forEach(i => fd.append(i.name, i.value));
        fd.append('items', JSON.stringify(items));
        fd.append('subtotal', $('#subtotal').text());
        fd.append('grand_total', $('#grandTotal').text());
        fd.append('discount_amount', $('#totalDiscount').text());
        const dt = $('#extraDiscountTypeInput').val();
        fd.append('extra_discount', dt === 'percent' ? ($('#extraDiscountPercent').val() || 0) : ($('#extraDiscount').val() || 0));
        fd.append('extra_discount_type', dt);
        fd.append('extra_charge', $('#extraCharge').val() || 0);
        fd.append('charge_name', $('#chargeName').val() || '');
        fd.append('auto_round_off', $('#autoRoundOff').is(':checked') ? 1 : 0);
        $.ajax({
            url: '{{ route('admin.quotations.store') }}',
            type: 'POST', data: fd, processData: false, contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('Quotation created successfully!', 'success');
                    setTimeout(() => { window.location.href = '/admin/quotations/' + response.quotation_id; }, 1500);
                } else {
                    showAlert('Error: ' + response.message, 'error');
                    isSubmitting = false; $btn.prop('disabled', false).html(orig);
                }
            },
            error: function(xhr) {
                let msg = 'Failed to create quotation';
                if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                else if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                showAlert(msg, 'error');
                isSubmitting = false; $btn.prop('disabled', false).html(orig);
            }
        });
    });
});
</script>
@endpush
@endsection
