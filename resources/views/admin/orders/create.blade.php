@extends('layouts.admin')

@section('title', 'Create New Order - Admin Panel')
@section('header-title', 'Create New Order')

@section('content')
<div class="create-order-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('admin.orders.index') }}" class="back-btn">
                <span>←</span> Back to Orders
            </a>
            <h2 class="page-title">Create New Order</h2>
            <p class="page-subtitle">Fill in customer details and add products</p>
        </div>
    </div>

    <form action="{{ route('admin.orders.store') }}" method="POST" id="orderForm">
        @csrf

        <!-- Tab Navigation -->
        <div class="tab-container">
            <div class="tab-nav">
                <button type="button" class="tab-btn active" data-tab="customer-info">
                    <span class="tab-icon">👤</span>
                    <span class="tab-text">Customer Info</span>
                </button>
                <button type="button" class="tab-btn" data-tab="address">
                    <span class="tab-icon">🏠</span>
                    <span class="tab-text">Address</span>
                </button>
                <button type="button" class="tab-btn" data-tab="products">
                    <span class="tab-icon">🛒</span>
                    <span class="tab-text">Products</span>
                </button>
                <button type="button" class="tab-btn" data-tab="summary">
                    <span class="tab-icon">📋</span>
                    <span class="tab-text">Summary</span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content-wrapper">
                <!-- Tab 1: Customer Information -->
                <div class="tab-content active" id="customer-info">
                    <div class="tab-header">
                        <h3 class="tab-title">Customer Information</h3>
                        <p class="tab-description">Enter customer details and payment information</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Customer Name <span class="required">*</span></label>
                            <input type="text" class="form-input" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" placeholder="Enter customer name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address <span class="required">*</span></label>
                            <input type="email" class="form-input" name="customer_email" id="customer_email" value="{{ old('customer_email') }}" placeholder="customer@example.com" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number <span class="required">*</span></label>
                            <input type="text" class="form-input" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}" placeholder="Enter phone number" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Age</label>
                            <input type="number" class="form-input" name="customer_age" id="customer_age" value="{{ old('customer_age') }}" min="1" max="150" placeholder="Optional">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Order Date <span class="required">*</span></label>
                            <input type="datetime-local" class="form-input" name="order_date" id="order_date" value="{{ old('order_date', date('Y-m-d\TH:i')) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Payment Method <span class="required">*</span></label>
                            <select class="form-select" name="payment_method" id="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="debit_card" {{ old('payment_method') == 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI</option>
                                <option value="net_banking" {{ old('payment_method') == 'net_banking' ? 'selected' : '' }}>Net Banking</option>
                                <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Order Status <span class="required">*</span></label>
                            <select class="form-select" name="order_status" id="order_status" required>
                                <option value="pending" {{ old('order_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ old('order_status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="completed" {{ old('order_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('order_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="on_hold" {{ old('order_status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Payment Status <span class="required">*</span></label>
                            <select class="form-select" name="payment_status" id="payment_status" required>
                                <option value="unpaid" {{ old('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="partially_paid" {{ old('payment_status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                                <option value="refunded" {{ old('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Address Details -->
                <div class="tab-content" id="address">
                    <div class="tab-header">
                        <h3 class="tab-title">Address Details</h3>
                        <p class="tab-description">Enter billing and shipping addresses</p>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><span class="card-icon">🏢</span> Billing Address</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label class="form-label">Address Line 1 <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="billing_address_line1" id="billing_address_line1" value="{{ old('billing_address_line1') }}" placeholder="Street address" required>
                                </div>

                                <div class="form-group full-width">
                                    <label class="form-label">Address Line 2</label>
                                    <input type="text" class="form-input" name="billing_address_line2" id="billing_address_line2" value="{{ old('billing_address_line2') }}" placeholder="Apartment, suite, unit, etc.">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">City <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="billing_city" id="billing_city" value="{{ old('billing_city') }}" placeholder="City" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">State <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="billing_state" id="billing_state" value="{{ old('billing_state') }}" placeholder="State" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Pincode <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="billing_pincode" id="billing_pincode" value="{{ old('billing_pincode') }}" placeholder="Postal code" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Country <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="billing_country" id="billing_country" value="{{ old('billing_country', 'India') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4><span class="card-icon">🚚</span> Shipping Address</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-4">
                                <input type="checkbox" class="form-check-input" id="same_as_billing" name="same_as_billing">
                                <label class="form-check-label" for="same_as_billing">
                                    <strong>Same as Billing Address</strong>
                                </label>
                            </div>

                            <div id="shipping_address_fields">
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label class="form-label">Address Line 1 <span class="required">*</span></label>
                                        <input type="text" class="form-input shipping-field" name="shipping_address_line1" id="shipping_address_line1" value="{{ old('shipping_address_line1') }}" placeholder="Street address">
                                    </div>

                                    <div class="form-group full-width">
                                        <label class="form-label">Address Line 2</label>
                                        <input type="text" class="form-input shipping-field" name="shipping_address_line2" id="shipping_address_line2" value="{{ old('shipping_address_line2') }}" placeholder="Apartment, suite, unit, etc.">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">City <span class="required">*</span></label>
                                        <input type="text" class="form-input shipping-field" name="shipping_city" id="shipping_city" value="{{ old('shipping_city') }}" placeholder="City">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">State <span class="required">*</span></label>
                                        <input type="text" class="form-input shipping-field" name="shipping_state" id="shipping_state" value="{{ old('shipping_state') }}" placeholder="State">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Pincode <span class="required">*</span></label>
                                        <input type="text" class="form-input shipping-field" name="shipping_pincode" id="shipping_pincode" value="{{ old('shipping_pincode') }}" placeholder="Postal code">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Country <span class="required">*</span></label>
                                        <input type="text" class="form-input shipping-field" name="shipping_country" id="shipping_country" value="{{ old('shipping_country', 'India') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Products -->
                <div class="tab-content" id="products">
                    <div class="tab-header">
                        <h3 class="tab-title">Add Products</h3>
                        <p class="tab-description">Search and add products to the order</p>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><span class="card-icon">🔍</span> Product Search</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Search Products <span class="required">*</span></label>
                                <div class="product-search-container">
                                    <div class="search-input-wrapper">
                                        <input type="text" class="form-input" id="product_search"
                                               placeholder="Search by product name, SKU or barcode...">
                                        <span class="search-icon">🔍</span>
                                    </div>
                                    <div class="product-search-results" id="product_search_results"></div>
                                </div>
                                <span class="form-hint">Type at least 2 characters to search</span>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><span class="card-icon">📦</span> Order Items</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="order-items-table" id="order_items_table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th width="100">SKU</th>
                                            <th width="100">Price (₹)</th>
                                            <th width="100">GST (%)</th>
                                            <th width="120">Quantity</th>
                                            <th width="120">Total (₹)</th>
                                            <th width="50">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="order_items_body">
                                        <!-- Items will be added here dynamically -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="order-summary mt-4">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Shipping Charges (₹)</label>
                                        <input type="number" class="form-input" id="shipping_charges"
                                               name="shipping_charges" value="0" min="0" step="0.01">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Discount (₹)</label>
                                        <input type="number" class="form-input" id="discount_amount"
                                               name="discount_amount" value="0" min="0" step="0.01">
                                    </div>

                                    <div class="form-group full-width">
                                        <div class="totals-box">
                                            <div class="total-item">
                                                <span class="total-label">Subtotal:</span>
                                                <span class="total-value" id="subtotal_display">₹0.00</span>
                                            </div>
                                            <div class="total-item">
                                                <span class="total-label">Tax Amount:</span>
                                                <span class="total-value" id="tax_display">₹0.00</span>
                                            </div>
                                            <div class="total-item">
                                                <span class="total-label">Shipping:</span>
                                                <span class="total-value" id="shipping_display">₹0.00</span>
                                            </div>
                                            <div class="total-item">
                                                <span class="total-label">Discount:</span>
                                                <span class="total-value" id="discount_display">₹0.00</span>
                                            </div>
                                            <div class="total-item grand-total">
                                                <span class="total-label">Grand Total:</span>
                                                <span class="total-value" id="grand_total_display">₹0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes Section -->
                            <div class="form-grid mt-4">
                                <div class="form-group">
                                    <label class="form-label">Customer Notes</label>
                                    <textarea class="form-textarea" name="notes" id="notes" rows="3"
                                              placeholder="Notes visible to customer...">{{ old('notes') }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Internal Notes</label>
                                    <textarea class="form-textarea" name="internal_notes" id="internal_notes" rows="3"
                                              placeholder="Private notes for internal use...">{{ old('internal_notes') }}</textarea>
                                </div>
                            </div>

                            <!-- Hidden fields for items -->
                            <div id="items_hidden_container"></div>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Summary -->
                <div class="tab-content" id="summary">
                    <div class="tab-header">
                        <h3 class="tab-title">Order Summary</h3>
                        <p class="tab-description">Review order details before creating</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h4><span class="card-icon">📋</span> Order Details</h4>
                                </div>
                                <div class="card-body">
                                    <table class="summary-table">
                                        <tr>
                                            <th>Customer Name:</th>
                                            <td id="summary_customer_name">-</td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td id="summary_customer_email">-</td>
                                        </tr>
                                        <tr>
                                            <th>Phone:</th>
                                            <td id="summary_customer_phone">-</td>
                                        </tr>
                                        <tr>
                                            <th>Order Date:</th>
                                            <td id="summary_order_date">-</td>
                                        </tr>
                                        <tr>
                                            <th>Payment Method:</th>
                                            <td id="summary_payment_method">-</td>
                                        </tr>
                                        <tr>
                                            <th>Order Status:</th>
                                            <td id="summary_order_status">-</td>
                                        </tr>
                                        <tr>
                                            <th>Payment Status:</th>
                                            <td id="summary_payment_status">-</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h4><span class="card-icon">📍</span> Address Summary</h4>
                                </div>
                                <div class="card-body">
                                    <div class="address-section">
                                        <h6>Billing Address:</h6>
                                        <p id="summary_billing_address">-</p>
                                    </div>
                                    <div class="address-section">
                                        <h6>Shipping Address:</h6>
                                        <p id="summary_shipping_address">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h4><span class="card-icon">🧾</span> Invoice Preview</h4>
                                </div>
                                <div class="card-body">
                                    <div class="invoice-preview">
                                        <div class="invoice-header">
                                            <h3>TAX INVOICE</h3>
                                            <p class="invoice-meta">
                                                Invoice #: <span id="invoice_number">ORD{{ date('Ymd') }}0001</span><br>
                                                Date: <span id="invoice_date">{{ date('d/m/Y') }}</span>
                                            </p>
                                        </div>

                                        <div class="invoice-addresses">
                                            <div class="address-col">
                                                <h6>Bill To:</h6>
                                                <p id="invoice_bill_to" class="address-text">-</p>
                                            </div>
                                            <div class="address-col">
                                                <h6>Ship To:</h6>
                                                <p id="invoice_ship_to" class="address-text">-</p>
                                            </div>
                                        </div>

                                        <div class="invoice-items">
                                            <table class="invoice-table">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Product</th>
                                                        <th>Qty</th>
                                                        <th>Price</th>
                                                        <th>GST</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="invoice_items">
                                                    <tr><td colspan="6" class="text-center">No items added</td></tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="4" class="text-right">Subtotal:</td>
                                                        <td colspan="2">₹<span id="invoice_subtotal">0.00</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4" class="text-right">Tax:</td>
                                                        <td colspan="2">₹<span id="invoice_tax">0.00</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4" class="text-right">Shipping:</td>
                                                        <td colspan="2">₹<span id="invoice_shipping">0.00</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4" class="text-right">Discount:</td>
                                                        <td colspan="2">₹<span id="invoice_discount">0.00</span></td>
                                                    </tr>
                                                    <tr class="total-row">
                                                        <td colspan="4" class="text-right"><strong>Grand Total:</strong></td>
                                                        <td colspan="2"><strong>₹<span id="invoice_grand_total">0.00</span></strong></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="invoice-actions mt-3">
                                        <button type="button" class="btn btn-print" id="print_invoice">
                                            <span class="action-icon">🖨️</span> Print Invoice
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h4><span class="card-icon">📝</span> Notes</h4>
                                </div>
                                <div class="card-body">
                                    <div id="summary_notes" class="notes-content">
                                        <p><strong>Customer Notes:</strong> <span id="summary_notes_text">-</span></p>
                                        <p><strong>Internal Notes:</strong> <span id="summary_internal_notes_text">-</span></p>
                                    </div>
                                </div>
                            </div>
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
                        <span>✓</span> Create Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.create-order-container { padding-bottom: 100px; }
#alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
.alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
@keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
.alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
.alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }
.page-header { margin-bottom: 30px; }
.back-btn { display: inline-flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; margin-bottom: 15px; }
.back-btn:hover { background: #f7fafc; color: #ff6b35; }
.page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
.page-subtitle { font-size: 14px; color: #718096; }
.tab-container { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
.tab-nav { display: flex; background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-bottom: 2px solid #e2e8f0; overflow-x: auto; }
.tab-btn { flex: 1; min-width: 140px; padding: 18px 20px; background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; font-weight: 600; color: #718096; }
.tab-btn:hover { background: rgba(255,107,53,0.05); color: #ff6b35; }
.tab-btn.active { background: white; color: #ff6b35; border-bottom-color: #ff6b35; }
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
.form-input:focus, .form-textarea:focus, .form-select:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
.form-textarea { min-height: 120px; resize: vertical; }
.form-hint { margin-top: 6px; font-size: 12px; color: #a0aec0; }
.card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; overflow: hidden; border: 2px solid #f7fafc; }
.card-header { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); padding: 20px; border-bottom: 2px solid #e2e8f0; }
.card-header h4 { margin: 0; color: #2d3748; display: flex; align-items: center; }
.card-icon { font-size: 20px; margin-right: 10px; }
.card-body { padding: 30px; }
.product-search-container { position: relative; }
.search-input-wrapper { position: relative; }
.search-input-wrapper .search-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 16px; color: #a0aec0; }
.product-search-results { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 2px solid #e2e8f0; border-top: none; border-radius: 0 0 10px 10px; z-index: 1000; max-height: 300px; overflow-y: auto; display: none; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
.product-item { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f7fafc; transition: all 0.2s; }
.product-item:hover { background: #f7fafc; }
.product-item:last-child { border-bottom: none; }
.product-item strong { color: #2d3748; display: block; margin-bottom: 4px; }
.product-item small { color: #718096; font-size: 12px; }
.order-items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.order-items-table th { background: #f7fafc; padding: 12px 16px; text-align: left; font-weight: 600; color: #2d3748; border-bottom: 2px solid #e2e8f0; }
.order-items-table td { padding: 12px 16px; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
.order-items-table tbody tr { background: #f7fafc; transition: background 0.2s; }
.order-items-table tbody tr:hover { background: #edf2f7; }
.item-total { font-weight: 700; color: #2d3748; }
.remove-item { background: #fed7d7; border: 2px solid #feb2b2; color: #c53030; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s; }
.remove-item:hover { background: #feb2b2; transform: scale(1.1); }
.order-summary { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); padding: 25px; border-radius: 12px; border: 2px solid #e2e8f0; }
.totals-box { background: white; padding: 20px; border-radius: 10px; }
.total-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0; }
.total-item:last-child { border-bottom: none; }
.total-item.grand-total { padding-top: 15px; margin-top: 10px; border-top: 2px solid #cbd5e0; font-size: 18px; }
.total-label { font-size: 14px; color: #718096; font-weight: 600; }
.total-value { font-size: 16px; color: #2d3748; font-weight: 700; }
.total-item.grand-total .total-value { color: #ff6b35; font-size: 20px; }
.summary-table { width: 100%; border-collapse: collapse; }
.summary-table tr { border-bottom: 1px solid #e2e8f0; }
.summary-table th { padding: 10px; text-align: left; font-weight: 600; color: #4a5568; width: 40%; }
.summary-table td { padding: 10px; color: #2d3748; }
.address-section { margin-bottom: 20px; }
.address-section h6 { color: #4a5568; margin-bottom: 8px; font-weight: 600; }
.invoice-preview { background: white; padding: 25px; border-radius: 12px; border: 2px solid #e2e8f0; }
.invoice-header { text-align: center; border-bottom: 2px solid #2d3748; padding-bottom: 15px; margin-bottom: 20px; }
.invoice-header h3 { color: #2d3748; margin-bottom: 8px; font-size: 22px; }
.invoice-meta { color: #718096; font-size: 13px; }
.invoice-addresses { display: flex; gap: 20px; margin-bottom: 20px; }
.address-col { flex: 1; }
.address-col h6 { color: #4a5568; margin-bottom: 8px; font-weight: 600; font-size: 13px; }
.address-text { color: #2d3748; line-height: 1.5; font-size: 13px; }
.invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
.invoice-table th { background: #f7fafc; padding: 10px; text-align: left; font-weight: 600; color: #2d3748; border-bottom: 2px solid #e2e8f0; font-size: 12px; }
.invoice-table td { padding: 10px; border-bottom: 1px solid #edf2f7; font-size: 12px; }
.invoice-table tfoot tr:last-child { border-bottom: none; }
.invoice-table .total-row { background: #f7fafc; }
.invoice-actions { text-align: center; }
.btn-print { padding: 10px 20px; background: #4299e1; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
.btn-print:hover { background: #3182ce; transform: translateY(-2px); }
.action-icon { font-size: 16px; }
.notes-content p { margin-bottom: 10px; color: #4a5568; }
.form-actions-fixed { position: fixed; bottom: 0; left: 260px; right: 0; background: white; padding: 20px 35px; box-shadow: 0 -4px 20px rgba(0,0,0,0.1); z-index: 100; }
.form-actions-content { display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto; }
.action-buttons-right { display: flex; gap: 15px; margin-left: auto; }
.btn-back, .btn-next { padding: 12px 24px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
.btn-back:hover, .btn-next:hover { background: #cbd5e0; transform: translateY(-2px); }
.btn-primary { padding: 12px 30px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(255,107,53,0.3); display: flex; align-items: center; gap: 8px; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.4); }
@media (max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; }
    .form-group.full-width { grid-column: span 1; }
    .form-actions-fixed { left: 0; }
    .invoice-addresses { flex-direction: column; }
}
</style>
@endpush

@push('scripts')
<script>
let currentTab = 0;
const tabs = ['customer-info', 'address', 'products', 'summary'];
let orderItems = [];
let itemCounter = 0;

function showAlert(message, type = 'success') {
    const container = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `<span class="alert-icon">${type === 'success' ? '✓' : '✕'}</span><span>${message}</span>`;
    container.appendChild(alert);
    setTimeout(() => {
        alert.classList.add('removing');
        setTimeout(() => alert.remove(), 300);
    }, 5000);
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

    // Special validation for products tab
    if (currentTab === 2 && orderItems.length === 0) {
        showAlert('Please add at least one product', 'error');
        isValid = false;
    }

    if (firstErrorField) firstErrorField.focus();
    return isValid;
}

// Tab Navigation
document.getElementById('nextBtn').addEventListener('click', () => {
    if (!validateCurrentTab()) {
        showAlert('Please fill in all required fields', 'error');
        return;
    }

    if (currentTab < tabs.length - 1) {
        currentTab++;
        showTab(currentTab);
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Update summary tab when going to it
        if (currentTab === 3) {
            updateOrderSummary();
            updateInvoicePreview();
        }
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
        // Validate only if going back
        if (index < currentTab) {
            currentTab = index;
            showTab(currentTab);
        } else {
            // Going forward - validate current tab
            if (validateCurrentTab()) {
                currentTab = index;
                showTab(currentTab);
            } else {
                showAlert('Please fill in all required fields', 'error');
            }
        }
    });
});

// Same as billing checkbox
document.getElementById('same_as_billing').addEventListener('change', function() {
    const isChecked = this.checked;
    const shippingFields = document.querySelectorAll('.shipping-field');

    shippingFields.forEach(field => {
        field.disabled = isChecked;
        field.required = !isChecked;
    });

    if (isChecked) {
        document.getElementById('shipping_address_fields').style.display = 'none';
    } else {
        document.getElementById('shipping_address_fields').style.display = 'block';
    }
});

// Product search functionality
let searchTimeout;
document.getElementById('product_search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();

    if (query.length < 2) {
        document.getElementById('product_search_results').innerHTML = '';
        document.getElementById('product_search_results').style.display = 'none';
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch(`{{ route('admin.orders.search-products') }}?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySearchResults(data.products);
                } else {
                    showAlert('Failed to search products', 'error');
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                showAlert('Error searching products', 'error');
            });
    }, 300);
});

function displaySearchResults(products) {
    const resultsContainer = document.getElementById('product_search_results');

    if (products.length === 0) {
        resultsContainer.innerHTML = '<div class="product-item text-center">No products found</div>';
    } else {
        let html = '';
        products.forEach(product => {
            // MongoDB ObjectId को string में convert करें
            const productId = product._id ? product._id.$oid || product._id : product.id;
            const price = parseFloat(product.price || product.selling_price || 0);
            const gst = parseFloat(product.gst || 0);
            const stock = product.current_stock || product.stock_quantity || 0;

            html += `
                <div class="product-item" data-product-id="${productId}">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${product.name}</strong><br>
                            <small>SKU: ${product.sku_code} | Stock: ${stock}</small>
                        </div>
                        <div class="text-right">
                            <strong>₹${price.toFixed(2)}</strong><br>
                            <small>GST: ${gst.toFixed(2)}%</small>
                        </div>
                    </div>
                </div>
            `;
        });
        resultsContainer.innerHTML = html;

        // Add click event listeners
        resultsContainer.querySelectorAll('.product-item').forEach(item => {
            item.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                if (productId && productId !== 'undefined') {
                    getProductDetails(productId);
                    document.getElementById('product_search').value = '';
                    resultsContainer.style.display = 'none';
                } else {
                    showAlert('Product ID is invalid', 'error');
                }
            });
        });
    }

    resultsContainer.style.display = 'block';
}

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.product-search-container')) {
        document.getElementById('product_search_results').style.display = 'none';
    }
});

// Get product details
async function getProductDetails(productId) {
    try {
        // Check if productId is valid
        if (!productId || productId === 'undefined') {
            showAlert('Invalid product ID', 'error');
            return;
        }

        const response = await fetch(`{{ route('admin.orders.get-product') }}?product_id=${encodeURIComponent(productId)}`);
        const data = await response.json();

        if (data.success) {
            addProductToOrder(data.product);
        } else {
            showAlert(data.message || 'Product not found', 'error');
        }
    } catch (error) {
        console.error('Error fetching product:', error);
        showAlert('Error loading product details', 'error');
    }
}

// Add product to order
function addProductToOrder(product) {
    // MongoDB ObjectId handle करें
    const productId = product._id ? product._id.$oid || product._id : product.id;

    if (!productId) {
        showAlert('Product ID is missing', 'error');
        return;
    }

    // Check if product already exists in order
    const existingIndex = orderItems.findIndex(item => item.product_id === productId);

    if (existingIndex > -1) {
        // Increase quantity
        orderItems[existingIndex].quantity++;
        updateOrderItemRow(existingIndex);
        showAlert(`Increased quantity of ${product.name}`, 'success');
    } else {
        // Add new item
        const newItem = {
            index: itemCounter++,
            product_id: productId,
            product_name: product.name,
            sku: product.sku_code,
            price: parseFloat(product.price || product.selling_price || 0),
            gst: parseFloat(product.gst || 0),
            quantity: 1
        };

        orderItems.push(newItem);
        addOrderItemRow(newItem);
        showAlert(`Added ${product.name} to order`, 'success');
    }

    calculateTotals();
}

function addOrderItemRow(item) {
    const tbody = document.getElementById('order_items_body');

    const row = document.createElement('tr');
    row.id = `item_row_${item.index}`;
    row.innerHTML = `
        <td>
            <input type="hidden" name="items[${item.index}][product_id]" value="${item.product_id}">
            <input type="hidden" name="items[${item.index}][product_name]" value="${item.product_name}">
            <input type="hidden" name="items[${item.index}][sku]" value="${item.sku}">
            <strong>${item.product_name}</strong>
        </td>
        <td>${item.sku}</td>
        <td>
            <input type="number" class="form-input item-price"
                   data-index="${item.index}" value="${item.price.toFixed(2)}"
                   min="0" step="0.01" onchange="updateItemPrice(${item.index}, this.value)">
        </td>
        <td>
            <input type="number" class="form-input item-gst"
                   data-index="${item.index}" value="${item.gst.toFixed(2)}"
                   min="0" max="100" step="0.01" onchange="updateItemGst(${item.index}, this.value)">
        </td>
        <td>
            <input type="number" class="form-input item-quantity"
                   data-index="${item.index}" value="${item.quantity}"
                   min="1" onchange="updateItemQuantity(${item.index}, this.value)">
        </td>
        <td class="item-total" id="item_total_${item.index}">₹${(item.price * item.quantity).toFixed(2)}</td>
        <td>
            <button type="button" class="remove-item" onclick="removeItem(${item.index})">
                ×
            </button>
        </td>
    `;

    tbody.appendChild(row);
}

function updateOrderItemRow(index) {
    const item = orderItems.find(item => item.index === index);
    if (!item) return;

    const row = document.getElementById(`item_row_${index}`);
    if (!row) return;

    row.querySelector('.item-price').value = item.price.toFixed(2);
    row.querySelector('.item-gst').value = item.gst.toFixed(2);
    row.querySelector('.item-quantity').value = item.quantity;
    row.querySelector(`#item_total_${index}`).textContent = `₹${(item.price * item.quantity).toFixed(2)}`;
}

function updateItemPrice(index, price) {
    const item = orderItems.find(item => item.index === index);
    if (item) {
        item.price = parseFloat(price) || 0;
        updateOrderItemRow(index);
        calculateTotals();
    }
}

function updateItemGst(index, gst) {
    const item = orderItems.find(item => item.index === index);
    if (item) {
        item.gst = parseFloat(gst) || 0;
        updateOrderItemRow(index);
        calculateTotals();
    }
}

function updateItemQuantity(index, quantity) {
    const item = orderItems.find(item => item.index === index);
    if (item) {
        const newQuantity = parseInt(quantity) || 1;
        if (newQuantity < 1) {
            showAlert('Quantity must be at least 1', 'error');
            item.quantity = 1;
        } else {
            item.quantity = newQuantity;
        }
        updateOrderItemRow(index);
        calculateTotals();
    }
}

function removeItem(index) {
    const item = orderItems.find(item => item.index === index);
    if (item) {
        showAlert(`Removed ${item.product_name} from order`, 'success');
    }

    orderItems = orderItems.filter(item => item.index !== index);
    const row = document.getElementById(`item_row_${index}`);
    if (row) row.remove();
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let taxAmount = 0;

    orderItems.forEach(item => {
        const itemSubtotal = item.price * item.quantity;
        const itemTax = (itemSubtotal * item.gst) / 100;

        subtotal += itemSubtotal;
        taxAmount += itemTax;
    });

    const shipping = parseFloat(document.getElementById('shipping_charges').value) || 0;
    const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
    const grandTotal = subtotal + taxAmount + shipping - discount;

    // Update display
    document.getElementById('subtotal_display').textContent = `₹${subtotal.toFixed(2)}`;
    document.getElementById('tax_display').textContent = `₹${taxAmount.toFixed(2)}`;
    document.getElementById('shipping_display').textContent = `₹${shipping.toFixed(2)}`;
    document.getElementById('discount_display').textContent = `₹${discount.toFixed(2)}`;
    document.getElementById('grand_total_display').textContent = `₹${grandTotal.toFixed(2)}`;
}

// Add event listeners for shipping and discount
document.getElementById('shipping_charges').addEventListener('input', calculateTotals);
document.getElementById('discount_amount').addEventListener('input', calculateTotals);

// Update order summary
function updateOrderSummary() {
    // Customer info
    document.getElementById('summary_customer_name').textContent = document.getElementById('customer_name').value || '-';
    document.getElementById('summary_customer_email').textContent = document.getElementById('customer_email').value || '-';
    document.getElementById('summary_customer_phone').textContent = document.getElementById('customer_phone').value || '-';
    document.getElementById('summary_order_date').textContent = document.getElementById('order_date').value || '-';
    document.getElementById('summary_payment_method').textContent = document.getElementById('payment_method').value || '-';
    document.getElementById('summary_order_status').textContent = document.getElementById('order_status').value || '-';
    document.getElementById('summary_payment_status').textContent = document.getElementById('payment_status').value || '-';

    // Billing address
    const billingAddress = [
        document.getElementById('billing_address_line1').value,
        document.getElementById('billing_address_line2').value,
        `${document.getElementById('billing_city').value}, ${document.getElementById('billing_state').value} - ${document.getElementById('billing_pincode').value}`,
        document.getElementById('billing_country').value
    ].filter(Boolean).join('<br>');
    document.getElementById('summary_billing_address').innerHTML = billingAddress || '-';

    // Shipping address
    if (document.getElementById('same_as_billing').checked) {
        document.getElementById('summary_shipping_address').innerHTML = '<em>Same as billing address</em>';
    } else {
        const shippingAddress = [
            document.getElementById('shipping_address_line1').value,
            document.getElementById('shipping_address_line2').value,
            `${document.getElementById('shipping_city').value}, ${document.getElementById('shipping_state').value} - ${document.getElementById('shipping_pincode').value}`,
            document.getElementById('shipping_country').value
        ].filter(Boolean).join('<br>');
        document.getElementById('summary_shipping_address').innerHTML = shippingAddress || '-';
    }

    // Notes
    document.getElementById('summary_notes_text').textContent = document.getElementById('notes').value || 'None';
    document.getElementById('summary_internal_notes_text').textContent = document.getElementById('internal_notes').value || 'None';
}

// Update invoice preview
function updateInvoicePreview() {
    // Bill To
    const billTo = [
        document.getElementById('customer_name').value,
        document.getElementById('billing_address_line1').value,
        document.getElementById('billing_address_line2').value,
        `${document.getElementById('billing_city').value}, ${document.getElementById('billing_state').value} - ${document.getElementById('billing_pincode').value}`,
        document.getElementById('billing_country').value,
        `Phone: ${document.getElementById('customer_phone').value}`,
        `Email: ${document.getElementById('customer_email').value}`
    ].filter(Boolean).join('<br>');
    document.getElementById('invoice_bill_to').innerHTML = billTo || '-';

    // Ship To
    if (document.getElementById('same_as_billing').checked) {
        document.getElementById('invoice_ship_to').innerHTML = '<em>Same as billing address</em>';
    } else {
        const shipTo = [
            document.getElementById('customer_name').value,
            document.getElementById('shipping_address_line1').value,
            document.getElementById('shipping_address_line2').value,
            `${document.getElementById('shipping_city').value}, ${document.getElementById('shipping_state').value} - ${document.getElementById('shipping_pincode').value}`,
            document.getElementById('shipping_country').value,
            `Phone: ${document.getElementById('customer_phone').value}`
        ].filter(Boolean).join('<br>');
        document.getElementById('invoice_ship_to').innerHTML = shipTo || '-';
    }

    // Invoice items
    const invoiceItems = document.getElementById('invoice_items');
    invoiceItems.innerHTML = '';

    if (orderItems.length === 0) {
        invoiceItems.innerHTML = '<tr><td colspan="6" class="text-center">No items added</td></tr>';
    } else {
        orderItems.forEach((item, index) => {
            const itemTotal = (item.price * item.quantity);
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${item.product_name}</td>
                <td>${item.quantity}</td>
                <td>₹${item.price.toFixed(2)}</td>
                <td>${item.gst}%</td>
                <td>₹${itemTotal.toFixed(2)}</td>
            `;
            invoiceItems.appendChild(row);
        });
    }

    // Update totals
    const subtotal = parseFloat(document.getElementById('subtotal_display').textContent.replace('₹', '')) || 0;
    const tax = parseFloat(document.getElementById('tax_display').textContent.replace('₹', '')) || 0;
    const shipping = parseFloat(document.getElementById('shipping_display').textContent.replace('₹', '')) || 0;
    const discount = parseFloat(document.getElementById('discount_display').textContent.replace('₹', '')) || 0;
    const grandTotal = parseFloat(document.getElementById('grand_total_display').textContent.replace('₹', '')) || 0;

    document.getElementById('invoice_subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('invoice_tax').textContent = tax.toFixed(2);
    document.getElementById('invoice_shipping').textContent = shipping.toFixed(2);
    document.getElementById('invoice_discount').textContent = discount.toFixed(2);
    document.getElementById('invoice_grand_total').textContent = grandTotal.toFixed(2);
}

// Print invoice
document.getElementById('print_invoice').addEventListener('click', function() {
    const printContents = document.querySelector('.invoice-preview').innerHTML;
    const originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    showTab(3); // Return to summary tab
});

// Form submission
document.getElementById('orderForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate all tabs
    for (let i = 0; i < tabs.length; i++) {
        const tabElement = document.getElementById(tabs[i]);
        const requiredInputs = tabElement.querySelectorAll('[required]');

        requiredInputs.forEach(input => {
            input.classList.remove('error');
            if (!input.value.trim()) {
                input.classList.add('error');
                currentTab = i;
                showTab(i);
                input.focus();
                showAlert('Please fill in all required fields', 'error');
                e.preventDefault();
                return false;
            }
        });
    }

    // Validate at least one product
    if (orderItems.length === 0) {
        currentTab = 2; // Products tab
        showTab(currentTab);
        showAlert('Please add at least one product', 'error');
        e.preventDefault();
        return false;
    }

    // Add hidden fields for items
    const itemsContainer = document.getElementById('items_hidden_container');
    itemsContainer.innerHTML = '';

    orderItems.forEach((item, index) => {
        itemsContainer.innerHTML += `
            <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
            <input type="hidden" name="items[${index}][product_name]" value="${item.product_name}">
            <input type="hidden" name="items[${index}][sku]" value="${item.sku}">
            <input type="hidden" name="items[${index}][price]" value="${item.price}">
            <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
            <input type="hidden" name="items[${index}][gst]" value="${item.gst}">
        `;
    });

    // Submit the form
    this.submit();
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    showTab(0);
    calculateTotals();

    // Set today's date in proper format
    const now = new Date();
    const timezoneOffset = now.getTimezoneOffset() * 60000;
    const localISOTime = new Date(now - timezoneOffset).toISOString().slice(0, 16);
    document.getElementById('order_date').value = localISOTime;
});

// Alert messages for errors/success
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
