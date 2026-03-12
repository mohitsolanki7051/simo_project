@extends('layouts.admin')

@section('title', 'Create Salesman - Admin Panel')
@section('header-title', 'Create Salesman')

@section('content')
<div class="create-salesman-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Add New Salesman</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.salesmen.index') }}" class="back-btn">← Back to Salesmen</a>
        </div>
    </div>

    <form action="{{ route('admin.salesmen.store') }}" method="POST" id="salesmanForm">
        @csrf

        <div class="form-card">
            <!-- Basic Information -->
            <div class="form-section">
                <h3 class="section-title">Basic Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Salesman Name <span class="required">*</span></label>
                        <input type="text" class="form-input" name="name" value="{{ old('name') }}" placeholder="e.g., Rahul Sharma" required>
                        @error('name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mobile Number <span class="required">*</span></label>
                        <input type="text" class="form-input" id="phone" name="phone" value="{{ old('phone') }}" placeholder="e.g., 9876543210" maxlength="10" required oninput="validatePhone(this.value)">
                        <div class="phone-validation-message" id="phone_message"></div>
                        @error('phone')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" id="email" name="email" value="{{ old('email') }}" placeholder="e.g., rahul@sales.com" oninput="validateEmail(this.value)">
                        <div class="email-validation-message" id="email_message"></div>
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Joining Date <span class="required">*</span></label>
                        <input type="date" class="form-input" name="joining_date" value="{{ old('joining_date', date('Y-m-d')) }}" required>
                        @error('joining_date')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Salary Information -->
            <div class="form-section">
                <h3 class="section-title">Salary Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Salary Type <span class="required">*</span></label>
                        <select class="form-select" name="salary_type" id="salaryType" required onchange="toggleSalaryTypeFields()">
                            <option value="fixed" {{ old('salary_type') == 'fixed' ? 'selected' : '' }}>Fixed Only</option>
                            <option value="commission" {{ old('salary_type') == 'commission' ? 'selected' : '' }}>Commission Only</option>
                            <option value="both" {{ old('salary_type') == 'both' ? 'selected' : '' }}>Fixed + Commission</option>
                        </select>
                        @error('salary_type')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Fixed Salary Section - Only visible when salary_type is fixed or both -->
                <div id="fixedSalarySection" class="salary-section" style="display: none;">
                    <div class="section-subheader">
                        <h4 class="subsection-title">Fixed Salary</h4>
                        <span class="section-badge">Monthly Payment</span>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Fixed Salary Amount <span class="required">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-input" name="fixed_salary" id="fixedSalary" value="{{ old('fixed_salary') }}" placeholder="e.g., 15000">
                            <div class="input-hint">Amount in ₹</div>
                            @error('fixed_salary')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Fixed Salary Period <span class="required">*</span></label>
                            <select class="form-select" name="fixed_salary_period" id="fixedSalaryPeriod">
                                <option value="monthly" {{ old('fixed_salary_period') == 'monthly' ? 'selected' : '' }}>Per Month</option>
                                <option value="quarterly" {{ old('fixed_salary_period') == 'quarterly' ? 'selected' : '' }}>Per 3 Months</option>
                                <option value="half_yearly" {{ old('fixed_salary_period') == 'half_yearly' ? 'selected' : '' }}>Per 6 Months</option>
                                <option value="yearly" {{ old('fixed_salary_period') == 'yearly' ? 'selected' : '' }}>Per Year</option>
                            </select>
                            <div class="input-hint">Select payment frequency</div>
                            @error('fixed_salary_period')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Commission Section - Only visible when salary_type is commission or both -->
                <div id="commissionSection" class="salary-section" style="display: none;">
                    <div class="section-subheader">
                        <h4 class="subsection-title">Commission Settings</h4>
                        <label class="toggle-switch">
                            <input type="checkbox" name="commission_enabled" id="commissionEnabled" value="1" {{ old('commission_enabled') ? 'checked' : '' }} onchange="toggleCommissionFields()">
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">Enable Commission</span>
                        </label>
                    </div>

                    <div class="commission-fields" id="commissionFields" style="{{ old('commission_enabled') ? '' : 'display: none;' }}">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Commission Period <span class="required">*</span></label>
                                <select class="form-select" name="commission_period" id="commissionPeriod">
                                    <option value="monthly" {{ old('commission_period') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="quarterly" {{ old('commission_period') == 'quarterly' ? 'selected' : '' }}>Quarterly (3 Months)</option>
                                    <option value="half_yearly" {{ old('commission_period') == 'half_yearly' ? 'selected' : '' }}>Half Yearly (6 Months)</option>
                                    <option value="yearly" {{ old('commission_period') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                <div class="input-hint">Commission calculation period</div>
                                @error('commission_period')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Customer Commission %</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-input" name="customer_commission_percent" value="{{ old('customer_commission_percent', 0) }}" placeholder="e.g., 2">
                                <div class="input-hint">For customers/retailers</div>
                                @error('customer_commission_percent')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Dealer Commission %</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-input" name="dealer_commission_percent" value="{{ old('dealer_commission_percent', 0) }}" placeholder="e.g., 1.5">
                                <div class="input-hint">For dealers</div>
                                @error('dealer_commission_percent')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Distributor Commission %</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-input" name="distributor_commission_percent" value="{{ old('distributor_commission_percent', 0) }}" placeholder="e.g., 1">
                                <div class="input-hint">For distributors</div>
                                @error('distributor_commission_percent')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <p class="field-note">Commission percentages will be applied on sales of respective party types</p>
                    </div>
                </div>
            </div>

            <!-- Status & Notes -->
            <div class="form-section">
                <h3 class="section-title">Status & Notes</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select class="form-select" name="status" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Notes</label>
                        <textarea class="form-textarea" name="notes" rows="3" placeholder="Any additional notes about the salesman">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="window.location.href='{{ route('admin.salesmen.index') }}'">Cancel</button>
                <button type="submit" class="btn-primary" id="submitBtn">✓ Create Salesman</button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .create-salesman-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 13px;
        margin: 0 auto;
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

    /* Header */
    .page-header {
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #dee2e6;
    }

    .page-title {
        font-size: 18px;
        font-weight: 600;
        color: #343a40;
        margin: 0;
    }

    .back-btn {
        color: #fa8128;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        padding: 6px 12px;
        border-radius: 4px;
        transition: all 0.2s;
        border: 1px solid #dee2e6;
        background: white;
    }

    .back-btn:hover {
        background: #fff0e6;
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    .form-section {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .form-section:last-child {
        border-bottom: none;
    }

    .section-title {
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 15px 0;
    }

    .section-subheader {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px dashed #e5e7eb;
    }

    .subsection-title {
        font-size: 14px;
        font-weight: 500;
        color: #4b5563;
        margin: 0;
    }

    .section-badge {
        background: #e5e7eb;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 10px;
        color: #4b5563;
        font-weight: 500;
    }

    .input-hint {
        font-size: 10px;
        color: #6b7280;
        margin-top: 3px;
    }

    /* Salary Sections */
    .salary-section {
        margin-top: 15px;
        padding: 15px;
        background: #f9fafc;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }

    /* Toggle Switch */
    .toggle-switch {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .toggle-switch input {
        display: none;
    }

    .toggle-slider {
        position: relative;
        width: 40px;
        height: 20px;
        background: #e5e7eb;
        border-radius: 20px;
        transition: all 0.3s;
    }

    .toggle-slider:before {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: white;
        top: 2px;
        left: 2px;
        transition: all 0.3s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    .toggle-switch input:checked + .toggle-slider {
        background: #667eea;
    }

    .toggle-switch input:checked + .toggle-slider:before {
        left: 22px;
    }

    .toggle-label {
        font-size: 12px;
        color: #6b7280;
    }

    /* Form Grid */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
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
        font-size: 12px;
    }

    .required {
        color: #dc3545;
    }

    /* Inputs */
    .form-input, .form-textarea, .form-select {
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 13px;
        transition: all 0.2s;
        font-family: inherit;
        background: white;
        height: 38px;
    }

    .form-input:focus, .form-textarea:focus, .form-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-input.error, .form-textarea.error, .form-select.error {
        border-color: #dc3545;
        background: #fff5f5;
    }

    .form-textarea {
        min-height: 80px;
        resize: vertical;
        height: auto;
    }

    .error-message {
        color: #dc3545;
        font-size: 11px;
        font-weight: 500;
        margin-top: 4px;
    }

    /* Field Note */
    .field-note {
        font-size: 11px;
        color: #6c757d;
        margin: 10px 0 0 0;
        font-style: italic;
    }

    /* Phone & Email Validation */
    .phone-validation-message, .email-validation-message {
        font-size: 10px;
        margin-top: 4px;
        padding: 4px 8px;
        border-radius: 3px;
        display: none;
    }


    .phone-validation-message.checking, .email-validation-message.checking {
        display: block;
        background: #cce5ff;
        color: #004085;
        border-left: 3px solid #007bff;
    }

    /* Form Actions */
    .form-actions {
        padding: 20px;
        background: #f9fafc;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid #e5e7eb;
    }

    .btn-primary, .btn-secondary {
        padding: 8px 20px;
        border: none;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 120px;
    }

    .btn-primary {
        background: #28a745;
        color: white;
    }

    .btn-primary:hover {
        background: #218838;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
    }

    .btn-primary:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full-width {
            grid-column: span 1;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .back-btn {
            width: 100%;
            text-align: center;
        }

        .section-subheader {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-primary, .btn-secondary {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Alert Container
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

    // Phone Validation
    let phoneTimeout;
    function validatePhone(phone) {
        const inputField = document.getElementById('phone');
        const messageDiv = document.getElementById('phone_message');

        inputField.classList.remove('error', 'success');
        messageDiv.className = 'phone-validation-message';
        messageDiv.textContent = '';

        if (!phone || phone.trim() === '') {
            return;
        }

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

        // Check availability
        clearTimeout(phoneTimeout);
        phoneTimeout = setTimeout(() => {
            checkPhoneAvailability(phone);
        }, 500);
    }

    function checkPhoneAvailability(phone) {
        fetch(`{{ route("admin.salesmen.check-phone") }}?phone=${phone}`)
            .then(response => response.json())
            .then(data => {
                if (!data.available) {
                    document.getElementById('phone').classList.add('error');
                    document.getElementById('phone').classList.remove('success');
                    document.getElementById('phone_message').className = 'phone-validation-message error';
                    document.getElementById('phone_message').textContent = data.message;
                }
            });
    }

    // Email Validation
    let emailTimeout;
    function validateEmail(email) {
        const inputField = document.getElementById('email');
        const messageDiv = document.getElementById('email_message');

        inputField.classList.remove('error', 'success');
        messageDiv.className = 'email-validation-message';
        messageDiv.textContent = '';

        if (!email || email.trim() === '') {
            return;
        }

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

        // Check availability
        clearTimeout(emailTimeout);
        emailTimeout = setTimeout(() => {
            checkEmailAvailability(email);
        }, 500);
    }

    function checkEmailAvailability(email) {
        if (!email) return;

        fetch(`{{ route("admin.salesmen.check-email") }}?email=${encodeURIComponent(email)}`)
            .then(response => response.json())
            .then(data => {
                if (!data.available) {
                    document.getElementById('email').classList.add('error');
                    document.getElementById('email').classList.remove('success');
                    document.getElementById('email_message').className = 'email-validation-message error';
                    document.getElementById('email_message').textContent = data.message;
                }
            });
    }

    // Toggle Salary Type Fields
    function toggleSalaryTypeFields() {
        const salaryType = document.getElementById('salaryType').value;
        const fixedSalarySection = document.getElementById('fixedSalarySection');
        const commissionSection = document.getElementById('commissionSection');
        const commissionEnabled = document.getElementById('commissionEnabled');
        const fixedSalaryInput = document.getElementById('fixedSalary');
        const fixedSalaryPeriod = document.getElementById('fixedSalaryPeriod');
        const commissionPeriod = document.getElementById('commissionPeriod');

        // Hide both sections first
        fixedSalarySection.style.display = 'none';
        commissionSection.style.display = 'none';

        // Show relevant sections based on salary type
        if (salaryType === 'fixed') {
            fixedSalarySection.style.display = 'block';
            commissionSection.style.display = 'none';
            // Disable commission
            if (commissionEnabled) {
                commissionEnabled.checked = false;
                toggleCommissionFields();
            }
            // Set required attributes
            fixedSalary.setAttribute('required', 'required');
            fixedSalaryPeriod.setAttribute('required', 'required');
            commissionPeriod.removeAttribute('required');
        } else if (salaryType === 'commission') {
            fixedSalarySection.style.display = 'none';
            commissionSection.style.display = 'block';
            // Set fixed salary to 0
            fixedSalary.value = 0;
            fixedSalary.removeAttribute('required');
            fixedSalaryPeriod.removeAttribute('required');
            commissionPeriod.setAttribute('required', 'required');
        } else if (salaryType === 'both') {
            fixedSalarySection.style.display = 'block';
            commissionSection.style.display = 'block';
            fixedSalary.setAttribute('required', 'required');
            fixedSalaryPeriod.setAttribute('required', 'required');
            commissionPeriod.setAttribute('required', 'required');
        }
    }

    // Toggle Commission Fields
    function toggleCommissionFields() {
        const commissionEnabled = document.getElementById('commissionEnabled').checked;
        const commissionFields = document.getElementById('commissionFields');
        const commissionPeriod = document.getElementById('commissionPeriod');

        commissionFields.style.display = commissionEnabled ? 'block' : 'none';

        if (!commissionEnabled) {
            document.querySelector('input[name="customer_commission_percent"]').value = 0;
            document.querySelector('input[name="dealer_commission_percent"]').value = 0;
            document.querySelector('input[name="distributor_commission_percent"]').value = 0;
            commissionPeriod.removeAttribute('required');
        } else {
            commissionPeriod.setAttribute('required', 'required');
        }
    }

    // Form Validation
    function validateForm() {
        const phone = document.getElementById('phone').value;
        const phoneRegex = /^[6-9]\d{9}$/;

        if (!phoneRegex.test(phone)) {
            showAlert('Please enter a valid phone number', 'error');
            document.getElementById('phone').focus();
            return false;
        }

        const email = document.getElementById('email').value;
        if (email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Please enter a valid email address', 'error');
                document.getElementById('email').focus();
                return false;
            }
        }

        const salaryType = document.getElementById('salaryType').value;
        const fixedSalary = document.getElementById('fixedSalary').value;
        const fixedSalaryPeriod = document.getElementById('fixedSalaryPeriod');
        const commissionEnabled = document.getElementById('commissionEnabled').checked;
        const commissionPeriod = document.getElementById('commissionPeriod');

        if (salaryType === 'fixed' || salaryType === 'both') {
            if (!fixedSalary || parseFloat(fixedSalary) <= 0) {
                showAlert('Please enter a valid fixed salary amount', 'error');
                document.getElementById('fixedSalary').focus();
                return false;
            }
            if (!fixedSalaryPeriod.value) {
                showAlert('Please select fixed salary period', 'error');
                fixedSalaryPeriod.focus();
                return false;
            }
        }

        if (salaryType === 'commission' || (salaryType === 'both' && commissionEnabled)) {
            if (!commissionPeriod.value) {
                showAlert('Please select commission period', 'error');
                commissionPeriod.focus();
                return false;
            }

            const customer = parseFloat(document.querySelector('input[name="customer_commission_percent"]').value) || 0;
            const dealer = parseFloat(document.querySelector('input[name="dealer_commission_percent"]').value) || 0;
            const distributor = parseFloat(document.querySelector('input[name="distributor_commission_percent"]').value) || 0;

            if (customer <= 0 && dealer <= 0 && distributor <= 0) {
                showAlert('Please enter at least one commission percentage', 'error');
                return false;
            }
        }

        return true;
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        toggleSalaryTypeFields();

        document.getElementById('salesmanForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!validateForm()) {
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '⏳ Creating...';
            submitBtn.disabled = true;

            this.submit();
        });

        @if($errors->any())
            @foreach($errors->all() as $error)
                showAlert('{{ $error }}', 'error');
            @endforeach
        @endif
    });
</script>
@endpush
@endsection
