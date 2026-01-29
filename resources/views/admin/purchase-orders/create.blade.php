@extends('layouts.admin')

@section('title', 'Create Purchase Order - Admin Panel')
@section('header-title', 'Create New Purchase Order')

@section('content')
<div class="create-po-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ url('/admin/purchase-orders') }}" class="back-btn">
                <span>←</span> Back to Purchase Orders
            </a>
            <h2 class="page-title">Create New Purchase Order</h2>
        </div>
    </div>

    <form action="{{ url('/admin/purchase-orders/store') }}" method="POST" id="purchaseOrderForm">
        @csrf

        <!-- Tab Navigation -->
        <div class="tab-container">
            <div class="tab-nav">
                <button type="button" class="tab-btn active" data-tab="basic-info">
                    <span class="tab-icon">📋</span>
                    <span class="tab-text">Basic Info</span>
                </button>
                <button type="button" class="tab-btn" data-tab="items">
                    <span class="tab-icon">📦</span>
                    <span class="tab-text">Items</span>
                </button>
                <button type="button" class="tab-btn" data-tab="charges">
                    <span class="tab-icon">💰</span>
                    <span class="tab-text">Charges & Summary</span>
                </button>
                <button type="button" class="tab-btn" data-tab="notes">
                    <span class="tab-icon">📝</span>
                    <span class="tab-text">Terms & Notes</span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content-wrapper">
                <!-- Tab 1: Basic Information -->
                <div class="tab-content active" id="basic-info">
                    <div class="tab-header">
                        <h3 class="tab-title">Purchase Order Information</h3>
                        <p class="tab-description">Enter the basic details for the purchase order</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">PO Date <span class="required">*</span></label>
                            <input type="date" class="form-input" name="po_date" value="{{ old('po_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Reference Number</label>
                            <input type="text" class="form-input" name="reference_number" value="{{ old('reference_number') }}" placeholder="Optional reference">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Supplier <span class="required">*</span></label>
                            <select class="form-select" id="supplier_id" name="supplier_id" required onchange="loadSupplierDetails()">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Warehouse <span class="required">*</span></label>
                            <select class="form-select" name="warehouse_id" required>
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }} ({{ $warehouse->code }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <div class="supplier-details-card" id="supplierDetailsCard" style="display: none;">
                                <div class="supplier-detail-header">📋 Supplier Details</div>
                                <div class="supplier-detail-grid">
                                    <div class="supplier-detail-item">
                                        <label>Name:</label>
                                        <span id="supplier_name_display"></span>
                                        <input type="hidden" name="supplier_name" id="supplier_name">
                                    </div>
                                    <div class="supplier-detail-item">
                                        <label>Email:</label>
                                        <span id="supplier_email_display"></span>
                                        <input type="hidden" name="supplier_email" id="supplier_email">
                                    </div>
                                    <div class="supplier-detail-item">
                                        <label>Phone:</label>
                                        <span id="supplier_phone_display"></span>
                                        <input type="hidden" name="supplier_phone" id="supplier_phone">
                                    </div>
                                    <div class="supplier-detail-item full-width">
                                        <label>Address:</label>
                                        <span id="supplier_address_display"></span>
                                        <input type="hidden" name="supplier_address" id="supplier_address">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Payment Terms <span class="required">*</span></label>
                            <select class="form-select" id="payment_terms" name="payment_terms" required>
                                <option value="">Select Terms</option>
                                <option value="Net 15" {{ old('payment_terms') == 'Net 15' ? 'selected' : '' }}>Net 15</option>
                                <option value="Net 30" {{ old('payment_terms') == 'Net 30' ? 'selected' : '' }}>Net 30</option>
                                <option value="Net 45" {{ old('payment_terms') == 'Net 45' ? 'selected' : '' }}>Net 45</option>
                                <option value="Net 60" {{ old('payment_terms') == 'Net 60' ? 'selected' : '' }}>Net 60</option>
                                <option value="Cash on Delivery" {{ old('payment_terms') == 'Cash on Delivery' ? 'selected' : '' }}>Cash on Delivery</option>
                                <option value="Advance Payment" {{ old('payment_terms') == 'Advance Payment' ? 'selected' : '' }}>Advance Payment</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Expected Delivery Date <span class="required">*</span></label>
                            <input type="date" class="form-input" name="expected_delivery_date" value="{{ old('expected_delivery_date') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Shipping Method</label>
                            <input type="text" class="form-input" name="shipping_method" value="{{ old('shipping_method') }}" placeholder="e.g., Air, Road, Courier">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Shipping Address</label>
                            <textarea class="form-textarea" name="shipping_address" rows="3" placeholder="Enter shipping address">{{ old('shipping_address') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Items -->
                <div class="tab-content" id="items">
                    <div class="tab-header">
                        <h3 class="tab-title">Purchase Order Items</h3>
                        <p class="tab-description">Add products with variants to this purchase order</p>
                    </div>

                    <div class="items-section">
                        <button type="button" class="btn-add-item" onclick="addNewItem()">
                            <span>+</span> Add Item
                        </button>

                        <div id="itemsContainer">
                            <!-- Items will be added here dynamically -->
                        </div>

                        <div class="items-summary-card" id="itemsSummary" style="display: none;">
                            <div class="summary-row">
                                <span>Total Items:</span>
                                <strong id="totalItemsCount">0</strong>
                            </div>
                            <div class="summary-row">
                                <span>Total Quantity:</span>
                                <strong id="totalQuantity">0</strong>
                            </div>
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <strong id="subtotalDisplay">₹0.00</strong>
                            </div>
                            <div class="summary-row">
                                <span>Total Tax:</span>
                                <strong id="totalTaxDisplay">₹0.00</strong>
                            </div>
                            <div class="summary-row total-row">
                                <span>Grand Total:</span>
                                <strong id="grandTotalDisplay">₹0.00</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Charges & Summary -->
                <div class="tab-content" id="charges">
                    <div class="tab-header">
                        <h3 class="tab-title">Additional Charges & Summary</h3>
                        <p class="tab-description">Add discounts, shipping and other charges</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Discount Type</label>
                            <select class="form-select" name="discount_type" id="discount_type" onchange="calculateFinalTotals()">
                                <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Discount Amount/Percentage</label>
                            <input type="number" step="0.01" class="form-input" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', 0) }}" placeholder="0.00" oninput="calculateFinalTotals()">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Shipping Charges</label>
                            <input type="number" step="0.01" class="form-input" name="shipping_charges" id="shipping_charges" value="{{ old('shipping_charges', 0) }}" placeholder="0.00" oninput="calculateFinalTotals()">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Other Charges</label>
                            <input type="number" step="0.01" class="form-input" name="other_charges" id="other_charges" value="{{ old('other_charges', 0) }}" placeholder="0.00" oninput="calculateFinalTotals()">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Payment Status <span class="required">*</span></label>
                            <select class="form-select" name="payment_status" required>
                                <option value="unpaid" {{ old('payment_status', 'unpaid') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="partial" {{ old('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>
                    </div>

                    <div class="final-summary-card">
                        <h4 class="summary-title">💰 Purchase Order Summary</h4>
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <strong id="finalSubtotal">₹0.00</strong>
                            </div>
                            <div class="summary-row">
                                <span>Tax Amount:</span>
                                <strong id="finalTaxAmount">₹0.00</strong>
                            </div>
                            <div class="summary-row">
                                <span>Discount:</span>
                                <strong id="finalDiscount">₹0.00</strong>
                            </div>
                            <div class="summary-row">
                                <span>Shipping Charges:</span>
                                <strong id="finalShipping">₹0.00</strong>
                            </div>
                            <div class="summary-row">
                                <span>Other Charges:</span>
                                <strong id="finalOther">₹0.00</strong>
                            </div>
                            <div class="summary-row total-row">
                                <span>Grand Total:</span>
                                <strong id="finalGrandTotal">₹0.00</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden fields for totals -->
                    <input type="hidden" name="subtotal" id="subtotal_hidden" value="0">
                    <input type="hidden" name="tax_amount" id="tax_amount_hidden" value="0">
                    <input type="hidden" name="total_amount" id="total_amount_hidden" value="0">
                </div>

                <!-- Tab 4: Terms & Notes -->
                <div class="tab-content" id="notes">
                    <div class="tab-header">
                        <h3 class="tab-title">Terms & Conditions and Notes</h3>
                        <p class="tab-description">Add any additional terms, conditions or notes</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea class="form-textarea" name="terms_conditions" rows="6" placeholder="Enter terms and conditions for this purchase order">{{ old('terms_conditions', '1. Goods once sold cannot be returned.
2. Payment must be made within the agreed terms.
3. Late payments may incur additional charges.
4. Supplier must deliver goods by the expected delivery date.') }}</textarea>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Internal Notes</label>
                            <textarea class="form-textarea" name="notes" rows="4" placeholder="Add any internal notes or remarks">{{ old('notes') }}</textarea>
                            <span class="form-hint">These notes are for internal use only and won't appear on the invoice</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions-fixed">
            <div class="form-actions-content">
                <button type="button" class="btn-back" id="backBtn" style="display: none;">
                    <span>←</span> Back
                </button>
                <div class="action-buttons-right">
                    <button type="button" class="btn-next" id="nextBtn">
                        Next <span>→</span>
                    </button>
                    <button type="submit" class="btn-primary" id="submitBtn" style="display: none;">
                        <span>✓</span> Create Purchase Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .create-po-container { padding-bottom: 100px; }
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; margin-bottom: 15px; }
    .back-btn:hover { background: #f7fafc; color: #667eea; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }

    .tab-container { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
    .tab-nav { display: flex; background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-bottom: 2px solid #e2e8f0; overflow-x: auto; }
    .tab-btn { flex: 1; min-width: 140px; padding: 18px 20px; background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; font-weight: 600; color: #718096; }
    .tab-btn:hover { background: rgba(102,126,234,0.05); color: #667eea; }
    .tab-btn.active { background: white; color: #667eea; border-bottom-color: #667eea; }
    .tab-btn.completed { color: #38a169; }
    .tab-icon { font-size: 18px; }

    .tab-content-wrapper { padding: 40px; }
    .tab-content { display: none; }
    .tab-content.active { display: block; animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .tab-header { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e2e8f0; }
    .tab-title { font-size: 20px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .tab-description { font-size: 14px; color: #718096; }

    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin-bottom: 25px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: span 2; }
    .form-label { margin-bottom: 8px; font-weight: 600; color: #4a5568; font-size: 14px; }
    .required { color: #fc8181; }
    .form-input, .form-textarea, .form-select { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; font-family: inherit; }
    .form-input:focus, .form-textarea:focus, .form-select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
    .form-input.error, .form-textarea.error, .form-select.error { border-color: #fc8181; background: #fff5f5; }
    .form-textarea { min-height: 100px; resize: vertical; }
    .form-hint { margin-top: 6px; font-size: 12px; color: #a0aec0; }

    .supplier-details-card { background: linear-gradient(135deg, #ebf8ff 0%, #e6fffa 100%); padding: 20px; border-radius: 12px; border: 2px solid #bee3f8; margin-top: 10px; }
    .supplier-detail-header { font-size: 16px; font-weight: 700; color: #2c5282; margin-bottom: 15px; }
    .supplier-detail-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
    .supplier-detail-item { display: flex; flex-direction: column; gap: 5px; }
    .supplier-detail-item.full-width { grid-column: span 3; }
    .supplier-detail-item label { font-size: 12px; font-weight: 600; color: #4a5568; text-transform: uppercase; }
    .supplier-detail-item span { font-size: 14px; color: #2d3748; font-weight: 500; }

    .items-section { margin-top: 20px; }
    .btn-add-item { width: 100%; padding: 14px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(102,126,234,0.3); display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 20px; }
    .btn-add-item:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }

    #itemsContainer { display: flex; flex-direction: column; gap: 20px; margin-bottom: 20px; }
    .item-card { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); padding: 25px; border-radius: 12px; border: 2px solid #e2e8f0; position: relative; }
    .item-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #cbd5e0; }
    .item-title { font-size: 16px; font-weight: 700; color: #2d3748; }
    .btn-remove-item { padding: 8px 16px; background: #fc8181; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .btn-remove-item:hover { background: #f56565; transform: scale(1.05); }

    .items-summary-card { background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%); padding: 20px; border-radius: 12px; border: 2px solid #81e6d9; margin-top: 20px; }
    .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #4fd1c5; }
    .summary-row:last-child { border-bottom: none; }
    .summary-row.total-row { font-size: 18px; color: #234e52; padding-top: 15px; margin-top: 10px; border-top: 2px solid #38b2ac; }

    .final-summary-card { background: linear-gradient(135deg, #fef5e7 0%, #fdebd0 100%); padding: 25px; border-radius: 12px; border: 2px solid #f6ad55; margin-top: 20px; }
    .summary-title { font-size: 18px; font-weight: 700; color: #7c2d12; margin-bottom: 15px; }
    .summary-details .summary-row { padding: 12px 0; }

    .form-actions-fixed { position: fixed; bottom: 0; left: 260px; right: 0; background: white; padding: 20px 35px; box-shadow: 0 -4px 20px rgba(0,0,0,0.1); z-index: 100; }
    .form-actions-content { display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto; }
    .action-buttons-right { display: flex; gap: 15px; margin-left: auto; }
    .btn-back, .btn-next { padding: 12px 24px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
    .btn-back:hover, .btn-next:hover { background: #cbd5e0; transform: translateY(-2px); }
    .btn-primary { padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(102,126,234,0.3); display: flex; align-items: center; gap: 8px; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
        .form-actions-fixed { left: 0; }
        .supplier-detail-grid { grid-template-columns: 1fr; }
        .supplier-detail-item.full-width { grid-column: span 1; }
    }
</style>
@endpush

@push('scripts')
<script>
    let currentTab = 0;
    const tabs = ['basic-info', 'items', 'charges', 'notes'];
    let itemCount = 0;
    let productsData = [];

    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span class="alert-icon">${type === 'success' ? '✓' : '✕'}</span><span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => { alert.classList.add('removing'); setTimeout(() => alert.remove(), 300); }, 5000);
    }

    function showTab(index) {
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(tabs[index]).classList.add('active');
        document.querySelectorAll('.tab-btn')[index].classList.add('active');
        document.querySelectorAll('.tab-btn').forEach((btn, i) => {
            if (i < index) btn.classList.add('completed');
            else btn.classList.remove('completed');
        });

        const backBtn = document.getElementById('backBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');

        backBtn.style.display = index === 0 ? 'none' : 'flex';
        nextBtn.style.display = index === tabs.length - 1 ? 'none' : 'flex';
        submitBtn.style.display = index === tabs.length - 1 ? 'flex' : 'none';
    }

    function validateCurrentTab() {
        const currentTabElement = document.getElementById(tabs[currentTab]);
        const requiredInputs = currentTabElement.querySelectorAll('[required]');
        let isValid = true;
        let firstErrorField = null;

        requiredInputs.forEach(input => {
            input.classList.remove('error');
            if (!input.value.trim()) {
                input.classList.add('error');
                if (!firstErrorField) firstErrorField = input;
                isValid = false;
            }
        });

        if (currentTab === 1) {
            const itemCards = document.querySelectorAll('.item-card');
            if (itemCards.length === 0) {
                showAlert('Please add at least one item to the purchase order', 'error');
                isValid = false;
            }
        }

        if (firstErrorField) firstErrorField.focus();
        return isValid;
    }

    function loadSupplierDetails() {
        const supplierId = document.getElementById('supplier_id').value;

        if (!supplierId) {
            document.getElementById('supplierDetailsCard').style.display = 'none';
            return;
        }

        fetch(`/admin/purchase-orders/supplier/${supplierId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('supplier_name').value = data.supplier.name;
                    document.getElementById('supplier_email').value = data.supplier.email || '';
                    document.getElementById('supplier_phone').value = data.supplier.phone || '';
                    document.getElementById('supplier_address').value = data.supplier.address || '';
                    document.getElementById('payment_terms').value = data.supplier.payment_terms || '';

                    document.getElementById('supplier_name_display').textContent = data.supplier.name;
                    document.getElementById('supplier_email_display').textContent = data.supplier.email || 'N/A';
                    document.getElementById('supplier_phone_display').textContent = data.supplier.phone || 'N/A';
                    document.getElementById('supplier_address_display').textContent = data.supplier.address || 'N/A';

                    document.getElementById('supplierDetailsCard').style.display = 'block';
                } else {
                    showAlert('Failed to load supplier details', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading supplier:', error);
                showAlert('Error loading supplier details', 'error');
            });
    }

    function loadProductsByWarehouse() {
        const warehouseId = document.querySelector('select[name="warehouse_id"]').value;

        if (!warehouseId) {
            productsData = [];
            showAlert('Please select a warehouse first', 'error');
            return;
        }

        fetch(`/admin/purchase-orders/warehouse/${warehouseId}/products`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    productsData = data.products;
                    showAlert('Products loaded successfully', 'success');

                    const itemCards = document.querySelectorAll('.item-card');
                    itemCards.forEach(card => {
                        const productSelect = card.querySelector('select[id^="product_"]');
                        if (productSelect) {
                            updateProductDropdown(productSelect);
                        }
                    });
                } else {
                    productsData = [];
                    showAlert(data.message || 'No products found', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error loading products', 'error');
            });
    }

    function updateProductDropdown(selectElement) {
        const currentValue = selectElement.value;
        selectElement.innerHTML = '<option value="">Select Product</option>';

        productsData.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = product.name;
            option.dataset.sku = product.sku_code;
            option.dataset.gst = product.gst;
            selectElement.appendChild(option);
        });

        if (currentValue) {
            const productExists = productsData.find(p => p.id === currentValue);
            if (productExists) {
                selectElement.value = currentValue;
            }
        }
    }

    function addNewItem() {
        itemCount++;

        const itemHtml = `
            <div class="item-card" id="item-${itemCount}">
                <div class="item-header">
                    <h4 class="item-title">Item #${itemCount}</h4>
                    <button type="button" class="btn-remove-item" onclick="removeItem(${itemCount})">Remove</button>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Product <span class="required">*</span></label>
                        <select class="form-select" name="items[${itemCount}][product_id]" id="product_${itemCount}" required onchange="loadProductVariants(${itemCount})">
                            <option value="">Select Product</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Variant <span class="required">*</span></label>
                        <select class="form-select" name="items[${itemCount}][variant_index]" id="variant_${itemCount}" required onchange="selectVariant(${itemCount})">
                            <option value="">Select Variant</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">SKU Code</label>
                        <input type="text" class="form-input" id="sku_${itemCount}" readonly>
                        <input type="hidden" name="items[${itemCount}][sku_code]" id="sku_hidden_${itemCount}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-input" id="unit_${itemCount}" readonly>
                        <input type="hidden" name="items[${itemCount}][unit]" id="unit_hidden_${itemCount}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Quantity <span class="required">*</span></label>
                        <input type="number" class="form-input" name="items[${itemCount}][quantity]" id="quantity_${itemCount}" min="1" value="1" required oninput="calculateItemTotal(${itemCount})">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unit Price (₹) <span class="required">*</span></label>
                        <input type="number" step="0.01" class="form-input" name="items[${itemCount}][unit_price]" id="unit_price_${itemCount}" min="0" value="0" required oninput="calculateItemTotal(${itemCount})">
                    </div>

                    <div class="form-group">
                        <label class="form-label">GST (%) <span class="required">*</span></label>
                        <input type="number" step="0.01" class="form-input" name="items[${itemCount}][gst]" id="gst_${itemCount}" min="0" value="0" required oninput="calculateItemTotal(${itemCount})">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tax Amount (₹)</label>
                        <input type="number" step="0.01" class="form-input" id="tax_amount_${itemCount}" readonly>
                        <input type="hidden" name="items[${itemCount}][tax_amount]" id="tax_amount_hidden_${itemCount}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Total (₹)</label>
                        <input type="number" step="0.01" class="form-input" id="total_${itemCount}" readonly>
                        <input type="hidden" name="items[${itemCount}][total]" id="total_hidden_${itemCount}">
                    </div>
                </div>

                <input type="hidden" name="items[${itemCount}][product_name]" id="product_name_${itemCount}">
                <input type="hidden" name="items[${itemCount}][variant_name]" id="variant_name_${itemCount}">
            </div>
        `;

        document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', itemHtml);

        const productSelect = document.getElementById(`product_${itemCount}`);
        if (productSelect && productsData.length > 0) {
            updateProductDropdown(productSelect);
        } else {
            showAlert('Please select warehouse first to load products', 'error');
        }

        updateItemsSummary();
    }

    function loadProductVariants(itemId) {
        const productId = document.getElementById(`product_${itemId}`).value;
        const variantSelect = document.getElementById(`variant_${itemId}`);

        variantSelect.innerHTML = '<option value="">Select Variant</option>';

        if (!productId) return;

        fetch(`/admin/purchase-orders/product/${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`product_name_${itemId}`).value = data.product.name;
                    document.getElementById(`gst_${itemId}`).value = data.product.gst;

                    if (data.product.variants && Array.isArray(data.product.variants) && data.product.variants.length > 0) {
                        data.product.variants.forEach((variant, index) => {
                            const option = document.createElement('option');
                            option.value = index;
                            option.textContent = variant.name || `Variant ${index + 1}`;
                            variantSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = 0;
                        option.textContent = 'Main Product';
                        variantSelect.appendChild(option);
                    }
                } else {
                    showAlert('Failed to load product variants', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading variants:', error);
                showAlert('Error loading product variants', 'error');
            });
    }

    function selectVariant(itemId) {
        const productId = document.getElementById(`product_${itemId}`).value;
        const variantIndex = document.getElementById(`variant_${itemId}`).value;

        if (!productId || variantIndex === '') return;

        fetch(`/admin/purchase-orders/product/${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const product = data.product;

                    if (product.variants && product.variants[variantIndex]) {
                        const variant = product.variants[variantIndex];
                        document.getElementById(`variant_name_${itemId}`).value = variant.name || `Variant ${parseInt(variantIndex) + 1}`;
                        document.getElementById(`sku_${itemId}`).value = variant.sku_code || product.sku_code;
                        document.getElementById(`sku_hidden_${itemId}`).value = variant.sku_code || product.sku_code;
                        document.getElementById(`unit_${itemId}`).value = variant.unit || 'PCS';
                        document.getElementById(`unit_hidden_${itemId}`).value = variant.unit || 'PCS';
                        document.getElementById(`unit_price_${itemId}`).value = variant.cost_price || 0;
                    } else {
                        document.getElementById(`variant_name_${itemId}`).value = product.name;
                        document.getElementById(`sku_${itemId}`).value = product.sku_code;
                        document.getElementById(`sku_hidden_${itemId}`).value = product.sku_code;
                        document.getElementById(`unit_${itemId}`).value = 'PCS';
                        document.getElementById(`unit_hidden_${itemId}`).value = 'PCS';
                        document.getElementById(`unit_price_${itemId}`).value = 0;
                    }

                    calculateItemTotal(itemId);
                }
            })
            .catch(error => {
                console.error('Error selecting variant:', error);
            });
    }

    function calculateItemTotal(itemId) {
        const quantity = parseFloat(document.getElementById(`quantity_${itemId}`).value) || 0;
        const unitPrice = parseFloat(document.getElementById(`unit_price_${itemId}`).value) || 0;
        const gst = parseFloat(document.getElementById(`gst_${itemId}`).value) || 0;

        const subtotal = quantity * unitPrice;
        const taxAmount = (subtotal * gst) / 100;
        const total = subtotal + taxAmount;

        document.getElementById(`tax_amount_${itemId}`).value = taxAmount.toFixed(2);
        document.getElementById(`tax_amount_hidden_${itemId}`).value = taxAmount.toFixed(2);
        document.getElementById(`total_${itemId}`).value = total.toFixed(2);
        document.getElementById(`total_hidden_${itemId}`).value = total.toFixed(2);

        updateItemsSummary();
        calculateFinalTotals();
    }

    function removeItem(itemId) {
        const item = document.getElementById(`item-${itemId}`);
        if (item) {
            item.remove();
            updateItemsSummary();
            calculateFinalTotals();
        }
    }

    function updateItemsSummary() {
        const itemCards = document.querySelectorAll('.item-card');
        const summaryCard = document.getElementById('itemsSummary');

        if (itemCards.length === 0) {
            summaryCard.style.display = 'none';
            return;
        }

        summaryCard.style.display = 'block';

        let totalQuantity = 0;
        let subtotal = 0;
        let totalTax = 0;

        itemCards.forEach(card => {
            const itemId = card.id.replace('item-', '');
            const quantity = parseFloat(document.getElementById(`quantity_${itemId}`)?.value) || 0;
            const unitPrice = parseFloat(document.getElementById(`unit_price_${itemId}`)?.value) || 0;
            const gst = parseFloat(document.getElementById(`gst_${itemId}`)?.value) || 0;

            totalQuantity += quantity;
            const itemSubtotal = quantity * unitPrice;
            subtotal += itemSubtotal;
            totalTax += (itemSubtotal * gst) / 100;
        });

        const grandTotal = subtotal + totalTax;

        document.getElementById('totalItemsCount').textContent = itemCards.length;
        document.getElementById('totalQuantity').textContent = totalQuantity;
        document.getElementById('subtotalDisplay').textContent = `₹${subtotal.toFixed(2)}`;
        document.getElementById('totalTaxDisplay').textContent = `₹${totalTax.toFixed(2)}`;
        document.getElementById('grandTotalDisplay').textContent = `₹${grandTotal.toFixed(2)}`;
    }

    function calculateFinalTotals() {
        const itemCards = document.querySelectorAll('.item-card');

        let subtotal = 0;
        let taxAmount = 0;

        itemCards.forEach(card => {
            const itemId = card.id.replace('item-', '');
            const quantity = parseFloat(document.getElementById(`quantity_${itemId}`)?.value) || 0;
            const unitPrice = parseFloat(document.getElementById(`unit_price_${itemId}`)?.value) || 0;
            const gst = parseFloat(document.getElementById(`gst_${itemId}`)?.value) || 0;

            const itemSubtotal = quantity * unitPrice;
            subtotal += itemSubtotal;
            taxAmount += (itemSubtotal * gst) / 100;
        });

        const discountType = document.getElementById('discount_type').value;
        const discountValue = parseFloat(document.getElementById('discount_amount').value) || 0;
        const shippingCharges = parseFloat(document.getElementById('shipping_charges').value) || 0;
        const otherCharges = parseFloat(document.getElementById('other_charges').value) || 0;

        let discountAmount = 0;
        if (discountType === 'percentage') {
            discountAmount = (subtotal * discountValue) / 100;
        } else {
            discountAmount = discountValue;
        }

        const totalAmount = subtotal + taxAmount - discountAmount + shippingCharges + otherCharges;

        document.getElementById('finalSubtotal').textContent = `₹${subtotal.toFixed(2)}`;
        document.getElementById('finalTaxAmount').textContent = `₹${taxAmount.toFixed(2)}`;
        document.getElementById('finalDiscount').textContent = `₹${discountAmount.toFixed(2)}`;
        document.getElementById('finalShipping').textContent = `₹${shippingCharges.toFixed(2)}`;
        document.getElementById('finalOther').textContent = `₹${otherCharges.toFixed(2)}`;
        document.getElementById('finalGrandTotal').textContent = `₹${totalAmount.toFixed(2)}`;

        document.getElementById('subtotal_hidden').value = subtotal.toFixed(2);
        document.getElementById('tax_amount_hidden').value = taxAmount.toFixed(2);
        document.getElementById('total_amount_hidden').value = totalAmount.toFixed(2);
    }

    document.getElementById('nextBtn').addEventListener('click', () => {
        if (!validateCurrentTab()) {
            showAlert('Please fill in all required fields', 'error');
            return;
        }
        if (currentTab < tabs.length - 1) {
            currentTab++;
            showTab(currentTab);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    document.getElementById('backBtn').addEventListener('click', () => {
        if (currentTab > 0) {
            currentTab--;
            showTab(currentTab);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    document.querySelectorAll('.tab-btn').forEach((btn, index) => {
        btn.addEventListener('click', () => {
            currentTab = index;
            showTab(currentTab);
        });
    });

    document.getElementById('purchaseOrderForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const itemCards = document.querySelectorAll('.item-card');
        if (itemCards.length === 0) {
            currentTab = 1;
            showTab(currentTab);
            showAlert('Please add at least one item to the purchase order', 'error');
            return;
        }

        let allValid = true;
        const allRequiredInputs = document.querySelectorAll('[required]');
        allRequiredInputs.forEach(input => {
            input.classList.remove('error');
            if (!input.value.trim()) {
                input.classList.add('error');
                allValid = false;
            }
        });

        if (!allValid) {
            for (let i = 0; i < tabs.length; i++) {
                const tabElement = document.getElementById(tabs[i]);
                const errorInput = tabElement.querySelector('.error');
                if (errorInput) {
                    currentTab = i;
                    showTab(i);
                    errorInput.focus();
                    showAlert('Please fill in all required fields', 'error');
                    return;
                }
            }
            showAlert('Please fill in all required fields', 'error');
            return;
        }

        this.submit();
    });

    document.addEventListener('DOMContentLoaded', function() {
        showTab(0);

        const warehouseSelect = document.querySelector('select[name="warehouse_id"]');
        if (warehouseSelect) {
            warehouseSelect.addEventListener('change', function() {
                loadProductsByWarehouse();
            });
        }
    });

    @if($errors->any())
        @foreach($errors->all() as $error)
            showAlert('{{ $error }}', 'error');
        @endforeach
    @endif
    @if(session('success'))
        showAlert('{{ session('success') }}', 'success');
    @endif
    @if(session('error'))
        showAlert('{{ session('error') }}', 'error');
    @endif
</script>
@endpush
@endsection
