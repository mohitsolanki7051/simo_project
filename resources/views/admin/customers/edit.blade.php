@extends('layouts.admin')

@section('title', 'Edit Customer - Admin Panel')
@section('header-title', 'Edit Customer')

@section('content')
<div class="edit-customer-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Edit Customer: {{ $customer->name }}</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.customers.index') }}" class="back-btn">← Back to Customers</a>
        </div>
    </div>

    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST" id="customerForm">
        @csrf
        @method('PUT')

        <!-- Tab Navigation -->
        <div class="tab-container">
            <div class="tab-nav">
                <button type="button" class="tab-btn active" data-tab="customer-info">
                    👤 Customer Info
                </button>
                <button type="button" class="tab-btn" data-tab="addresses">
                    📍 Billing & Shipping
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content-wrapper">
                <!-- Tab 1: Customer Information -->
                <div class="tab-content active" id="customer-info">
                    <div class="tab-header">
                        <h3 class="tab-title">Customer Information</h3>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" class="form-input" name="name" value="{{ old('name', $customer->name) }}" placeholder="e.g., John Doe" required>
                            @error('name')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number <span class="required">*</span></label>
                            <input type="text" class="form-input" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" placeholder="e.g., 9876543210" maxlength="10" required oninput="validatePhone(this.value)">
                            <div class="phone-validation-message" id="phone_message"></div>
                            @error('phone')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-input" id="email" name="email" value="{{ old('email', $customer->email) }}" placeholder="e.g., john@example.com" oninput="validateEmail(this.value)">
                            <div class="email-validation-message" id="email_message"></div>
                            @error('email')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Customer Type <span class="required">*</span></label>
                            <select class="form-select" name="customer_type" id="customerType" required onchange="toggleCompanyField()">
                                <option value="">Select Type</option>
                                <option value="individual" {{ old('customer_type', $customer->customer_type) == 'individual' ? 'selected' : '' }}>Individual</option>
                                <option value="business" {{ old('customer_type', $customer->customer_type) == 'business' ? 'selected' : '' }}>Business</option>
                            </select>
                            @error('customer_type')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group" id="companyField" style="display: {{ old('customer_type', $customer->customer_type) == 'business' ? 'block' : 'none' }};">
                            <label class="form-label">Company Name <span class="required">*</span></label>
                            <input type="text" class="form-input" name="company_name" value="{{ old('company_name', $customer->company_name) }}" placeholder="e.g., ABC Enterprises" {{ $customer->customer_type == 'business' ? 'required' : '' }}>
                            @error('company_name')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">GST Number</label>
                            <input type="text" class="form-input" name="gst_number" value="{{ old('gst_number', $customer->gst_number) }}" placeholder="e.g., 27ABCDE1234F1Z5" maxlength="15" oninput="this.value = this.value.toUpperCase()">
                            @error('gst_number')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">PAN Number</label>
                            <input type="text" class="form-input" name="pan_number" value="{{ old('pan_number', $customer->pan_number) }}" placeholder="e.g., ABCDE1234F" maxlength="10" oninput="this.value = this.value.toUpperCase()">
                            @error('pan_number')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="active" {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $customer->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Notes</label>
                            <textarea class="form-textarea" name="notes" rows="3" placeholder="Any additional notes about the customer">{{ old('notes', $customer->notes) }}</textarea>
                            @error('notes')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Addresses -->
                <div class="tab-content" id="addresses">
                    <div class="tab-header">
                        <h3 class="tab-title">Billing & Shipping Addresses</h3>
                        <p class="tab-subtitle">Manage billing and shipping addresses for this customer</p>
                    </div>

                    <!-- Address Type Tabs -->
                    <div class="address-type-tabs">
                        <div class="address-type-nav">
                            <button type="button" class="address-type-btn active" data-address-type="billing">
                                🏢 Billing Address
                            </button>
                            <button type="button" class="address-type-btn" data-address-type="shipping">
                                📦 Shipping Address
                            </button>
                        </div>
                    </div>

                    <!-- Billing Address Section -->
                    <div class="address-section" id="billingSection">
                        <div class="section-header">
                            <h4 class="section-title">Billing Addresses</h4>
                            <button type="button" class="btn-small btn-secondary" onclick="openAddressModal('billing')">
                                <span class="btn-icon">+</span> Add Billing Address
                            </button>
                        </div>

                        <div class="addresses-list" id="billingAddressesList">
                            @foreach($customer->addresses->where('type', 'billing') as $address)
                            <div class="address-card {{ $address->is_default ? 'default' : '' }}" data-id="{{ $address->id }}" data-type="billing">
                                <div class="address-header">
                                    <h5 class="address-title">{{ $address->city }}, {{ $address->state }}</h5>
                                    <span class="address-badge {{ $address->is_default ? 'badge-default' : 'badge-regular' }}">
                                        {{ $address->is_default ? 'DEFAULT' : '' }}
                                    </span>
                                </div>
                                <div class="address-details">
                                    {{ $address->address }}<br>
                                    @if($address->landmark){{ $address->landmark }}<br>@endif
                                    {{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}<br>
                                    {{ $address->country }}
                                </div>
                                @if($address->contact_person || $address->contact_number)
                                <div class="address-contact">
                                    @if($address->contact_person)<span>👤 {{ $address->contact_person }}</span>@endif
                                    @if($address->contact_number)<span>📱 {{ $address->contact_number }}</span>@endif
                                </div>
                                @endif
                                <div class="address-actions">
                                    <button type="button" class="action-btn btn-edit" onclick="editAddress('{{ $address->id }}')">
                                        ✏️ Edit
                                    </button>
                                    @if(!$address->is_default)
                                    <button type="button" class="action-btn btn-set-default" onclick="setDefaultAddress('{{ $address->id }}')">
                                        ⭐ Set Default
                                    </button>
                                    @endif
                                    <button type="button" class="action-btn btn-delete" onclick="deleteAddress('{{ $address->id }}')">
                                        🗑️ Delete
                                    </button>
                                </div>
                            </div>
                            @endforeach
                            @if($customer->addresses->where('type', 'billing')->isEmpty())
                            <div class="no-addresses">
                                <div class="empty-icon">🏢</div>
                                <p>No billing addresses added yet</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Shipping Address Section -->
                    <div class="address-section" id="shippingSection" style="display: none;">
                        <div class="section-header">
                            <h4 class="section-title">Shipping Addresses</h4>
                            <button type="button" class="btn-small btn-secondary" onclick="openAddressModal('shipping')">
                                <span class="btn-icon">+</span> Add Shipping Address
                            </button>
                        </div>

                        <div class="addresses-list" id="shippingAddressesList">
                            @foreach($customer->addresses->where('type', 'shipping') as $address)
                            <div class="address-card {{ $address->is_default ? 'default' : '' }}" data-id="{{ $address->id }}" data-type="shipping">
                                <div class="address-header">
                                    <h5 class="address-title">{{ $address->city }}, {{ $address->state }}</h5>
                                    <span class="address-badge {{ $address->is_default ? 'badge-default' : 'badge-regular' }}">
                                        {{ $address->is_default ? 'DEFAULT' : '' }}
                                    </span>
                                </div>
                                <div class="address-details">
                                    {{ $address->address }}<br>
                                    @if($address->landmark){{ $address->landmark }}<br>@endif
                                    {{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}<br>
                                    {{ $address->country }}
                                </div>
                                @if($address->contact_person || $address->contact_number)
                                <div class="address-contact">
                                    @if($address->contact_person)<span>👤 {{ $address->contact_person }}</span>@endif
                                    @if($address->contact_number)<span>📱 {{ $address->contact_number }}</span>@endif
                                </div>
                                @endif
                                <div class="address-actions">
                                    <button type="button" class="action-btn btn-edit" onclick="editAddress('{{ $address->id }}')">
                                        ✏️ Edit
                                    </button>
                                    @if(!$address->is_default)
                                    <button type="button" class="action-btn btn-set-default" onclick="setDefaultAddress('{{ $address->id }}')">
                                        ⭐ Set Default
                                    </button>
                                    @endif
                                    <button type="button" class="action-btn btn-delete" onclick="deleteAddress('{{ $address->id }}')">
                                        🗑️ Delete
                                    </button>
                                </div>
                            </div>
                            @endforeach
                            @if($customer->addresses->where('type', 'shipping')->isEmpty())
                            <div class="no-addresses">
                                <div class="empty-icon">📦</div>
                                <p>No shipping addresses added yet</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="action-buttons">
                <button type="button" class="btn-back" id="backBtn" style="display: none;">
                    ← Back
                </button>
                <div class="right-buttons">
                    <button type="button" class="btn-next" id="nextBtn">
                        Next →
                    </button>
                    <button type="submit" class="btn-primary" id="submitBtn" style="display: none;">
                        ✓ Update Customer
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Address Modal -->
<div class="modal" id="addressModal">
    <div class="modal-overlay" onclick="closeAddressModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon" style="background: #667eea;">📍</div>
            <h4 class="modal-title" id="modalTitle">Add Address</h4>
            <button type="button" class="modal-close" onclick="closeAddressModal()">×</button>
        </div>
        <form id="addressForm">
            <div class="modal-body">
                <input type="hidden" id="addressId">
                <input type="hidden" id="addressType">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Contact Person</label>
                        <input type="text" class="form-input" id="contactPerson" placeholder="e.g., John Doe">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-input" id="contactNumber" placeholder="e.g., 9876543210" maxlength="10">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Address <span class="required">*</span></label>
                        <textarea class="form-textarea" id="address" rows="3" placeholder="Full address" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Landmark</label>
                        <input type="text" class="form-input" id="landmark" placeholder="e.g., Near Central Mall">
                    </div>
                    <div class="form-group">
                        <label class="form-label">City <span class="required">*</span></label>
                        <input type="text" class="form-input" id="city" placeholder="e.g., Mumbai" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">State <span class="required">*</span></label>
                        <input type="text" class="form-input" id="state" placeholder="e.g., Maharashtra" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pincode <span class="required">*</span></label>
                        <input type="text" class="form-input" id="pincode" placeholder="e.g., 400001" required maxlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country <span class="required">*</span></label>
                        <input type="text" class="form-input" id="country" placeholder="e.g., India" required value="India">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-checkbox">
                            <input type="checkbox" id="isDefault">
                            <span class="checkmark"></span>
                            Set as default address
                        </label>
                        <div class="checkbox-note">Only one address can be default for each type</div>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closeAddressModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-confirm" id="saveAddressBtn">Save Address</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    /* Same styles as create.blade.php - Don't change */
    .edit-customer-container {
        padding: 0 15px 80px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 13px;
    }

    #alertContainer {
        position: fixed;
        top: 70px;
        right: 20px;
        z-index: 9999;
        max-width: 300px;
    }

    .alert {
        padding: 10px 12px;
        border-radius: 6px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease;
        font-size: 12px;
        font-weight: 500;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 3px solid #28a745;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border-left: 3px solid #dc3545;
    }

    .page-header {
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #dee2e6;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .header-right {
        display: flex;
        align-items: center;
    }

    .back-btn {
        color: #fa8128;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        padding: 5px 10px;
        border-radius: 4px;
        transition: all 0.2s;
        border: 1px solid #dee2e6;
        background: white;
    }

    .back-btn:hover {
        background: #fff0e6;
    }

    .page-title {
        font-size: 16px;
        font-weight: 600;
        color: #343a40;
        margin: 0;
    }

    .tab-container {
        background: white;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 1px solid #dee2e6;
    }

    .tab-nav {
        display: flex;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 0;
    }

    .tab-btn {
        flex: 1;
        padding: 10px 12px;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 11px;
        font-weight: 500;
        color: #6c757d;
        text-align: center;
    }

    .tab-btn:hover {
        background: #e9ecef;
        color: #495057;
    }

    .tab-btn.active {
        background: white;
        color: #007bff;
        border-bottom-color: #007bff;
        font-weight: 600;
    }

    .tab-content-wrapper {
        padding: 15px;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.2s;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .tab-header {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }

    .tab-title {
        font-size: 14px;
        font-weight: 600;
        color: #343a40;
        margin: 0;
    }

    .tab-subtitle {
        font-size: 11px;
        color: #6c757d;
        margin: 5px 0 0 0;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 15px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: span 2;
    }

    .form-label {
        margin-bottom: 5px;
        font-weight: 500;
        color: #495057;
        font-size: 11px;
    }

    .required {
        color: #dc3545;
    }

    .form-input, .form-textarea, .form-select {
        padding: 6px 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 12px;
        transition: all 0.2s;
        font-family: inherit;
        background: white;
        height: 32px;
    }

    .form-input:focus, .form-textarea:focus, .form-select:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }

    .form-input.error, .form-textarea.error, .form-select.error {
        border-color: #dc3545;
        background: #fff5f5;
    }

    .form-textarea {
        min-height: 70px;
        resize: vertical;
        height: auto;
    }

    .error-message {
        color: #dc3545;
        font-size: 10px;
        font-weight: 500;
        margin-top: 3px;
    }

    .phone-validation-message, .email-validation-message {
        font-size: 9px;
        margin-top: 3px;
        padding: 3px 5px;
        border-radius: 3px;
        display: none;
    }

    .phone-validation-message.error, .email-validation-message.error {
        display: block;
        background: #f8d7da;
        color: #721c24;
        border-left: 2px solid #dc3545;
    }

    .phone-validation-message.success, .email-validation-message.success {
        display: block;
        background: #d4edda;
        color: #155724;
        border-left: 2px solid #28a745;
    }

    .phone-validation-message.checking, .email-validation-message.checking {
        display: block;
        background: #cce5ff;
        color: #004085;
        border-left: 2px solid #007bff;
    }

    .address-type-tabs {
        margin-bottom: 15px;
    }

    .address-type-nav {
        display: flex;
        background: #f8f9fa;
        border-radius: 4px;
        padding: 2px;
        border: 1px solid #dee2e6;
    }

    .address-type-btn {
        flex: 1;
        padding: 8px 10px;
        background: none;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 11px;
        font-weight: 500;
        color: #6c757d;
        text-align: center;
    }

    .address-type-btn:hover {
        background: #e9ecef;
    }

    .address-type-btn.active {
        background: #007bff;
        color: white;
    }

    .address-section {
        margin-top: 15px;
        padding: 15px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background: #f8fafc;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .section-title {
        font-size: 13px;
        font-weight: 600;
        color: #343a40;
        margin: 0;
    }

    .btn-small {
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 500;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }

    .btn-tertiary {
        background: #17a2b8;
        color: white;
    }

    .btn-tertiary:hover {
        background: #138496;
        transform: translateY(-1px);
    }

    .btn-icon {
        font-size: 12px;
    }

    .addresses-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .address-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 12px;
        transition: all 0.2s;
        position: relative;
    }

    .address-card:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.1);
    }

    .address-card.default {
        border-color: #28a745;
        background: #f8fff9;
    }

    .address-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .address-title {
        font-size: 12px;
        font-weight: 600;
        color: #343a40;
        margin: 0;
    }

    .address-badge {
        font-size: 9px;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-default {
        background: #28a745;
        color: white;
    }

    .badge-regular {
        background: #6c757d;
        color: white;
    }

    .address-details {
        font-size: 11px;
        color: #6c757d;
        line-height: 1.5;
        margin-bottom: 8px;
    }

    .address-contact {
        font-size: 10px;
        color: #495057;
        margin-top: 5px;
        display: flex;
        gap: 10px;
    }

    .address-actions {
        display: flex;
        gap: 6px;
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px solid #f1f3f4;
    }

    .action-btn {
        padding: 4px 8px;
        font-size: 10px;
        border-radius: 3px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .btn-edit {
        background: #e7f1ff;
        color: #0066cc;
    }

    .btn-edit:hover {
        background: #d0e3ff;
    }

    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-delete:hover {
        background: #fecaca;
    }

    .btn-set-default {
        background: #d1fae5;
        color: #065f46;
    }

    .btn-set-default:hover {
        background: #a7f3d0;
    }

    .no-addresses {
        text-align: center;
        padding: 30px;
        color: #6c757d;
    }

    .empty-icon {
        font-size: 32px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .no-addresses p {
        font-size: 12px;
        margin: 0;
    }

    .form-actions {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 10px 15px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 100;
        border-top: 1px solid #dee2e6;
    }

    .action-buttons {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
    }

    .right-buttons {
        display: flex;
        gap: 6px;
        margin-left: auto;
    }

    .btn-back, .btn-next {
        padding: 5px 10px;
        background: #fa8128;
        color: white;
        border: none;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
    }

    .btn-back:hover, .btn-next:hover {
        background: #e07020;
    }

    .btn-primary {
        padding: 5px 12px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
    }

    .btn-primary:hover {
        background: #218838;
    }

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
        backdrop-filter: blur(2px);
    }

    .modal-content {
        position: relative;
        background: white;
        border-radius: 12px;
        padding: 0;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        animation: modalFadeIn 0.2s ease;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px 12px 0 0;
    }

    .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        color: #6b7280;
        cursor: pointer;
        padding: 4px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
        margin-left: auto;
    }

    .modal-close:hover {
        background: #f3f4f6;
        color: #1f2937;
    }

    .modal-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: white;
        flex-shrink: 0;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px 24px;
        border-top: 1px solid #e5e7eb;
        background: #fafafa;
        border-radius: 0 0 12px 12px;
    }

    .btn-modal {
        padding: 8px 20px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 80px;
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
    }

    .btn-confirm {
        background: #667eea;
        color: white;
        border: 1px solid #667eea;
    }

    .btn-confirm:hover {
        background: #5a67d8;
        transform: translateY(-1px);
    }

    .form-checkbox {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 12px;
        color: #495057;
    }

    .form-checkbox input {
        display: none;
    }

    .checkmark {
        width: 16px;
        height: 16px;
        border: 2px solid #ced4da;
        border-radius: 3px;
        position: relative;
        transition: all 0.2s;
    }

    .form-checkbox input:checked ~ .checkmark {
        background: #007bff;
        border-color: #007bff;
    }

    .checkmark:after {
        content: '';
        position: absolute;
        display: none;
        left: 5px;
        top: 2px;
        width: 4px;
        height: 8px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .form-checkbox input:checked ~ .checkmark:after {
        display: block;
    }

    .checkbox-note {
        font-size: 9px;
        color: #6c757d;
        margin-top: 3px;
        font-style: italic;
    }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .header-right { width: 100%; }
        .back-btn { width: 100%; text-align: center; }
        .section-header { flex-direction: column; align-items: flex-start; }
        .address-actions { flex-wrap: wrap; }
        .modal-content { margin: 20px; width: calc(100% - 40px); }
    }
</style>
@endpush

@push('scripts')
<script>
    // ========== GLOBAL VARIABLES ==========
    let currentTab = 0;
    const tabs = ['customer-info', 'addresses'];
    let editingAddressId = null;
    const customerId = @json($customer->id);


    // ========== ALERT SYSTEM ==========
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);

        setTimeout(() => {
            alert.remove();
        }, 3000);
    }

    // ========== TAB MANAGEMENT ==========
    function showTab(index) {
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

        document.getElementById(tabs[index]).classList.add('active');
        document.querySelectorAll('.tab-btn')[index].classList.add('active');

        const backBtn = document.getElementById('backBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');

        backBtn.style.display = index === 0 ? 'none' : 'flex';
        nextBtn.style.display = index === tabs.length - 1 ? 'none' : 'flex';
        submitBtn.style.display = index === tabs.length - 1 ? 'flex' : 'none';
    }

    // ========== PHONE VALIDATION ==========
    function validatePhone(phone) {
        const inputField = document.getElementById('phone');
        const messageDiv = document.getElementById('phone_message');

        // Reset
        inputField.classList.remove('error', 'success');
        messageDiv.className = 'phone-validation-message';
        messageDiv.textContent = '';

        if (!phone || phone.trim() === '') {
            return;
        }

        // Basic validation
        const phoneRegex = /^[6-9]\d{9}$/;
        if (!phoneRegex.test(phone)) {
            inputField.classList.add('error');
            messageDiv.className = 'phone-validation-message error';
            messageDiv.textContent = 'Please enter a valid 10-digit phone number starting with 6-9';
            return;
        }

        inputField.classList.add('success');
        messageDiv.className = 'phone-validation-message success';
        messageDiv.textContent = '✓ Valid phone number';
    }

    // ========== EMAIL VALIDATION ==========
    function validateEmail(email) {
        const inputField = document.getElementById('email');
        const messageDiv = document.getElementById('email_message');

        // Reset
        inputField.classList.remove('error', 'success');
        messageDiv.className = 'email-validation-message';
        messageDiv.textContent = '';

        if (!email || email.trim() === '') {
            return;
        }

        // Basic validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            inputField.classList.add('error');
            messageDiv.className = 'email-validation-message error';
            messageDiv.textContent = 'Please enter a valid email address';
            return;
        }

        inputField.classList.add('success');
        messageDiv.className = 'email-validation-message success';
        messageDiv.textContent = '✓ Valid email';
    }

    // ========== COMPANY FIELD TOGGLE ==========
    function toggleCompanyField() {
        const customerType = document.getElementById('customerType').value;
        const companyField = document.getElementById('companyField');

        if (customerType === 'business') {
            companyField.style.display = 'block';
            document.querySelector('[name="company_name"]').required = true;
        } else {
            companyField.style.display = 'none';
            document.querySelector('[name="company_name"]').required = false;
        }
    }

    // ========== ADDRESS MANAGEMENT ==========
    function openAddressModal(type) {
        const modal = document.getElementById('addressModal');
        const title = document.getElementById('modalTitle');

        document.getElementById('addressType').value = type;
        title.textContent = type === 'billing' ? 'Add Billing Address' : 'Add Shipping Address';

        // Reset form
        document.getElementById('addressForm').reset();
        document.getElementById('addressId').value = '';
        document.getElementById('country').value = 'India';
        editingAddressId = null;

        modal.style.display = 'flex';
    }

    function closeAddressModal() {
        document.getElementById('addressModal').style.display = 'none';
        document.getElementById('addressForm').reset();
        editingAddressId = null;
    }

    function editAddress(addressId) {
        // Get address data via AJAX from server
        fetch(`/admin/customers/${customerId}/addresses?type=${getAddressType(addressId)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.addresses) {
                    const address = data.addresses.find(addr => addr.id === addressId);
                    if (!address) return;

                    const modal = document.getElementById('addressModal');
                    const title = document.getElementById('modalTitle');
                    const addressType = address.type;

                    document.getElementById('addressType').value = addressType;
                    title.textContent = `Edit ${addressType === 'billing' ? 'Billing' : 'Shipping'} Address`;

                    document.getElementById('addressId').value = address.id;
                    document.getElementById('contactPerson').value = address.contact_person || '';
                    document.getElementById('contactNumber').value = address.contact_number || '';
                    document.getElementById('address').value = address.address || '';
                    document.getElementById('landmark').value = address.landmark || '';
                    document.getElementById('city').value = address.city || '';
                    document.getElementById('state').value = address.state || '';
                    document.getElementById('pincode').value = address.pincode || '';
                    document.getElementById('country').value = address.country || 'India';
                    document.getElementById('isDefault').checked = address.is_default || false;

                    editingAddressId = addressId;
                    modal.style.display = 'flex';
                }
            })
            .catch(error => {
                console.error('Error fetching address:', error);
                showAlert('Error loading address details', 'error');
            });
    }

    function getAddressType(addressId) {
        const addressCard = document.querySelector(`[data-id="${addressId}"]`);
        return addressCard ? addressCard.getAttribute('data-type') : 'billing';
    }

    function deleteAddress(addressId) {
        if (!confirm('Are you sure you want to delete this address?')) {
            return;
        }

        // Get address type
        const addressCard = document.querySelector(`[data-id="${addressId}"]`);
        if (!addressCard) return;

        const addressType = addressCard.getAttribute('data-type');
        const isDefault = addressCard.classList.contains('default');

        // Send AJAX request to delete - FIXED ROUTE URL
        const deleteUrl = `/admin/customers/addresses/${addressId}`;
        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove address from UI
                addressCard.remove();

                // Check if this was a default address
                if (isDefault) {
                    // Set the first remaining address as default
                    const remainingAddresses = document.querySelectorAll(`[data-type="${addressType}"]`);
                    if (remainingAddresses.length > 0) {
                        const nextAddress = remainingAddresses[0];
                        const nextAddressId = nextAddress.getAttribute('data-id');

                        // Set as default via AJAX
                        const defaultUrl = `/admin/customers/addresses/${nextAddressId}/default`;
                        fetch(defaultUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update UI for the new default
                                nextAddress.classList.add('default');
                                const badge = nextAddress.querySelector('.address-badge');
                                badge.classList.remove('badge-regular');
                                badge.classList.add('badge-default');
                                badge.textContent = 'DEFAULT';

                                // Disable "Set Default" button for new default
                                const setDefaultBtn = nextAddress.querySelector('.btn-set-default');
                                if (setDefaultBtn) {
                                    setDefaultBtn.style.display = 'none';
                                }
                            }
                        });
                    }
                }

                // Check if no addresses left
                const addressList = document.getElementById(`${addressType}AddressesList`);
                const addresses = addressList.querySelectorAll('.address-card');
                if (addresses.length === 0) {
                    addressList.innerHTML = `
                        <div class="no-addresses">
                            <div class="empty-icon">${addressType === 'billing' ? '🏢' : '📦'}</div>
                            <p>No ${addressType} addresses added yet</p>
                        </div>
                    `;
                }

                showAlert('Address deleted successfully');
            } else {
                showAlert(data.message || 'Error deleting address', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error deleting address', 'error');
        });
    }

    // ========== SET DEFAULT ADDRESS ==========
    function setDefaultAddress(addressId) {
        const defaultUrl = `/admin/customers/addresses/${addressId}/default`;
        fetch(defaultUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Get address type
                const addressCard = document.querySelector(`[data-id="${addressId}"]`);
                if (!addressCard) return;

                const addressType = addressCard.getAttribute('data-type');

                // Remove default from all addresses of this type
                document.querySelectorAll(`[data-type="${addressType}"]`).forEach(card => {
                    card.classList.remove('default');
                    const badge = card.querySelector('.address-badge');
                    if (badge) {
                        badge.classList.remove('badge-default');
                        badge.classList.add('badge-regular');
                        badge.textContent = '';
                    }

                    // Show "Set Default" button
                    const setDefaultBtn = card.querySelector('.btn-set-default');
                    if (setDefaultBtn) {
                        setDefaultBtn.style.display = 'flex';
                    }
                });

                // Set new default
                addressCard.classList.add('default');
                const badge = addressCard.querySelector('.address-badge');
                if (badge) {
                    badge.classList.remove('badge-regular');
                    badge.classList.add('badge-default');
                    badge.textContent = 'DEFAULT';
                }

                // Hide "Set Default" button for new default
                const setDefaultBtn = addressCard.querySelector('.btn-set-default');
                if (setDefaultBtn) {
                    setDefaultBtn.style.display = 'none';
                }

                showAlert('Address set as default');
            } else {
                showAlert(data.message || 'Error setting default address', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error setting default address', 'error');
        });
    }

    // ========== ADDRESS FORM SUBMISSION ==========
    document.getElementById('addressForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const addressId = document.getElementById('addressId').value;
        const addressType = document.getElementById('addressType').value;

        // FIXED: Use direct URLs instead of route helpers
        const url = addressId
            ? `/admin/customers/addresses/${addressId}`
            : `/admin/customers/${customerId}/addresses`;

        const method = addressId ? 'PUT' : 'POST';

        const formData = {
            type: addressType,
            address: document.getElementById('address').value.trim(),
            city: document.getElementById('city').value.trim(),
            state: document.getElementById('state').value.trim(),
            pincode: document.getElementById('pincode').value.trim(),
            country: document.getElementById('country').value.trim(),
            landmark: document.getElementById('landmark').value.trim(),
            contact_person: document.getElementById('contactPerson').value.trim(),
            contact_number: document.getElementById('contactNumber').value.trim(),
            is_default: document.getElementById('isDefault').checked
        };

        // Validate required fields
        if (!formData.address || !formData.city || !formData.state || !formData.pincode || !formData.country) {
            showAlert('Please fill all required fields', 'error');
            return;
        }

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeAddressModal();

                if (addressId) {
                    // Update existing address in UI
                    updateAddressInUI(addressType, addressId, data.address);
                    showAlert('Address updated successfully');
                } else {
                    // Add new address to UI
                    addAddressToUI(addressType, data.address);
                    showAlert('Address added successfully');
                }
            } else {
                showAlert(data.message || 'Error saving address', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error saving address', 'error');
        });
    });

    function updateAddressInUI(type, addressId, addressData) {
        const addressCard = document.querySelector(`[data-id="${addressId}"]`);
        if (!addressCard) return;

        // Update card content
        addressCard.querySelector('.address-title').textContent = `${addressData.city}, ${addressData.state}`;

        const detailsHTML = `
            ${addressData.address}<br>
            ${addressData.landmark ? addressData.landmark + '<br>' : ''}
            ${addressData.city}, ${addressData.state} - ${addressData.pincode}<br>
            ${addressData.country}
        `;
        addressCard.querySelector('.address-details').innerHTML = detailsHTML;

        // Update contact info
        let contactHTML = '';
        if (addressData.contact_person || addressData.contact_number) {
            contactHTML = '<div class="address-contact">';
            if (addressData.contact_person) {
                contactHTML += `<span>👤 ${addressData.contact_person}</span>`;
            }
            if (addressData.contact_number) {
                contactHTML += `<span>📱 ${addressData.contact_number}</span>`;
            }
            contactHTML += '</div>';
        }

        const contactDiv = addressCard.querySelector('.address-contact');
        if (contactDiv) {
            if (addressData.contact_person || addressData.contact_number) {
                contactDiv.innerHTML = contactHTML;
            } else {
                contactDiv.remove();
            }
        } else if (addressData.contact_person || addressData.contact_number) {
            const detailsDiv = addressCard.querySelector('.address-details');
            detailsDiv.insertAdjacentHTML('afterend', contactHTML);
        }

        // Update default status
        if (addressData.is_default) {
            // Remove default from all addresses of this type
            document.querySelectorAll(`[data-type="${type}"]`).forEach(card => {
                card.classList.remove('default');
                const badge = card.querySelector('.address-badge');
                if (badge) {
                    badge.classList.remove('badge-default');
                    badge.classList.add('badge-regular');
                    badge.textContent = '';
                }

                const setDefaultBtn = card.querySelector('.btn-set-default');
                if (setDefaultBtn) {
                    setDefaultBtn.style.display = 'flex';
                }
            });

            // Set this address as default
            addressCard.classList.add('default');
            const badge = addressCard.querySelector('.address-badge');
            if (badge) {
                badge.classList.remove('badge-regular');
                badge.classList.add('badge-default');
                badge.textContent = 'DEFAULT';
            }

            const setDefaultBtn = addressCard.querySelector('.btn-set-default');
            if (setDefaultBtn) {
                setDefaultBtn.style.display = 'none';
            }
        }
    }

    function addAddressToUI(type, addressData) {
        const addressList = document.getElementById(`${type}AddressesList`);

        // Remove "no addresses" message if it exists
        const noAddresses = addressList.querySelector('.no-addresses');
        if (noAddresses) {
            noAddresses.remove();
        }

        // Create new address card HTML
        const addressCard = document.createElement('div');
        addressCard.className = `address-card ${addressData.is_default ? 'default' : ''}`;
        addressCard.setAttribute('data-id', addressData._id || addressData.id);
        addressCard.setAttribute('data-type', type);

        let contactHTML = '';
        if (addressData.contact_person || addressData.contact_number) {
            contactHTML = '<div class="address-contact">';
            if (addressData.contact_person) {
                contactHTML += `<span>👤 ${addressData.contact_person}</span>`;
            }
            if (addressData.contact_number) {
                contactHTML += `<span>📱 ${addressData.contact_number}</span>`;
            }
            contactHTML += '</div>';
        }

        addressCard.innerHTML = `
            <div class="address-header">
                <h5 class="address-title">${addressData.city}, ${addressData.state}</h5>
                <span class="address-badge ${addressData.is_default ? 'badge-default' : 'badge-regular'}">
                    ${addressData.is_default ? 'DEFAULT' : ''}
                </span>
            </div>
            <div class="address-details">
                ${addressData.address}<br>
                ${addressData.landmark ? addressData.landmark + '<br>' : ''}
                ${addressData.city}, ${addressData.state} - ${addressData.pincode}<br>
                ${addressData.country}
            </div>
            ${contactHTML}
            <div class="address-actions">
                <button type="button" class="action-btn btn-edit" onclick="editAddress('${addressData._id || addressData.id}')">
                    ✏️ Edit
                </button>
                ${!addressData.is_default ? `
                <button type="button" class="action-btn btn-set-default" onclick="setDefaultAddress('${addressData._id || addressData.id}')">
                    ⭐ Set Default
                </button>
                ` : ''}
                <button type="button" class="action-btn btn-delete" onclick="deleteAddress('${addressData._id || addressData.id}')">
                    🗑️ Delete
                </button>
            </div>
        `;

        // Add to list
        addressList.appendChild(addressCard);

        // If this is default, update other addresses
        if (addressData.is_default) {
            document.querySelectorAll(`[data-type="${type}"]`).forEach(card => {
                const cardId = card.getAttribute('data-id');
                if (cardId !== (addressData._id || addressData.id)) {
                    card.classList.remove('default');
                    const badge = card.querySelector('.address-badge');
                    if (badge) {
                        badge.classList.remove('badge-default');
                        badge.classList.add('badge-regular');
                        badge.textContent = '';
                    }

                    const setDefaultBtn = card.querySelector('.btn-set-default');
                    if (setDefaultBtn) {
                        setDefaultBtn.style.display = 'flex';
                    }
                }
            });
        }
    }

    // ========== FORM VALIDATION ==========
    function validateCurrentTab() {
        const currentTabElement = document.getElementById(tabs[currentTab]);
        const requiredInputs = currentTabElement.querySelectorAll('[required]');
        let isValid = true;

        requiredInputs.forEach(input => {
            input.classList.remove('error');
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            }
        });

        if (!isValid) {
            showAlert('Please fill all required fields', 'error');
        }

        return isValid;
    }

    // ========== FORM SUBMISSION ==========
    document.getElementById('customerForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate phone
        const phone = document.getElementById('phone').value;
        const phoneRegex = /^[6-9]\d{9}$/;
        if (!phoneRegex.test(phone)) {
            showAlert('Please enter a valid phone number', 'error');
            document.getElementById('phone').focus();
            return false;
        }

        // Validate email if provided
        const email = document.getElementById('email').value;
        if (email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Please enter a valid email address', 'error');
                document.getElementById('email').focus();
                return false;
            }
        }

        // Validate customer type
        const customerType = document.getElementById('customerType').value;
        if (customerType === 'business') {
            const companyName = document.querySelector('[name="company_name"]').value;
            if (!companyName.trim()) {
                showAlert('Company name is required for business customers', 'error');
                document.querySelector('[name="company_name"]').focus();
                return false;
            }
        }

        // Submit form
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.innerHTML = '⏳ Updating...';
        submitBtn.disabled = true;

        this.submit();
        return true;
    });

    // ========== INITIALIZE ==========
    document.addEventListener('DOMContentLoaded', function() {
        showTab(0);

        // Initialize company field
        toggleCompanyField();

        // Tab buttons click
        document.querySelectorAll('.tab-btn').forEach((btn, index) => {
            btn.addEventListener('click', () => {
                if (validateCurrentTab() || currentTab === index) {
                    currentTab = index;
                    showTab(index);
                }
            });
        });

        // Next button
        document.getElementById('nextBtn').addEventListener('click', () => {
            if (!validateCurrentTab()) return;

            if (currentTab < tabs.length - 1) {
                currentTab++;
                showTab(currentTab);
            }
        });

        // Back button
        document.getElementById('backBtn').addEventListener('click', () => {
            if (currentTab > 0) {
                currentTab--;
                showTab(currentTab);
            }
        });

        // Address type tab switching
        document.querySelectorAll('.address-type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.getAttribute('data-address-type');

                // Update active button
                document.querySelectorAll('.address-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Show/hide sections
                document.getElementById('billingSection').style.display = type === 'billing' ? 'block' : 'none';
                document.getElementById('shippingSection').style.display = type === 'shipping' ? 'block' : 'none';
            });
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAddressModal();
            }
        });

        // Show Laravel errors
        @if($errors->any())
            @foreach($errors->all() as $error)
                showAlert('{{ $error }}', 'error');
            @endforeach
        @endif
    });
</script>
@endpush
@endsection
