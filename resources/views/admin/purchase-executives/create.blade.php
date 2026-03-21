@extends('layouts.admin')

@section('title', 'Add Purchase Executive - Admin Panel')
@section('header-title', 'Add Purchase Executive')

@section('content')
<div class="create-executive-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Add New Purchase Executive</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.purchase-executives.index') }}" class="back-btn">← Back to Purchase Executives</a>
        </div>
    </div>

    <form action="{{ route('admin.purchase-executives.store') }}" method="POST" id="executiveForm">
        @csrf

        <div class="form-card">
            <!-- Basic Information -->
            <div class="form-section">
                <h3 class="section-title">Basic Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" class="form-input" name="name" value="{{ old('name') }}" placeholder="e.g., Rajesh Kumar" required>
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
                        <input type="email" class="form-input" id="email" name="email" value="{{ old('email') }}" placeholder="e.g., rajesh@company.com" oninput="validateEmail(this.value)">
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
                        <textarea class="form-textarea" name="notes" rows="3" placeholder="Any additional notes about the purchase executive">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

           

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="window.location.href='{{ route('admin.purchase-executives.index') }}'">Cancel</button>
                <button type="submit" class="btn-primary" id="submitBtn">✓ Add Purchase Executive</button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .create-executive-container {
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

    /* Phone & Email Validation */
    .phone-validation-message, .email-validation-message {
        font-size: 10px;
        margin-top: 4px;
        padding: 4px 8px;
        border-radius: 3px;
        display: none;
    }

    .phone-validation-message.error, .email-validation-message.error {
        display: block;
        background: #f8d7da;
        color: #721c24;
        border-left: 3px solid #dc3545;
    }

    .phone-validation-message.success, .email-validation-message.success {
        display: block;
        background: #d4edda;
        color: #155724;
        border-left: 3px solid #28a745;
    }

    /* Info Note */
    .info-note {
        background: #f0f9ff;
    }

    .info-box {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        background: #dbeafe;
        border-radius: 6px;
        color: #1e40af;
        font-size: 12px;
        border: 1px solid #93c5fd;
    }

    .info-icon {
        font-size: 14px;
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
        fetch(`{{ route("admin.purchase-executives.check-phone") }}?phone=${phone}`)
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

        fetch(`{{ route("admin.purchase-executives.check-email") }}?email=${encodeURIComponent(email)}`)
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

        return true;
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('executiveForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!validateForm()) {
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '⏳ Adding...';
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
