@extends('layouts.admin')

@section('title', 'Create Supplier - Admin Panel')
@section('header-title', 'Create New Supplier')

@section('content')
<div class="create-supplier-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('admin.suppliers.index') }}" class="back-btn">
                <span>←</span> Back to Suppliers
            </a>
            <h2 class="page-title">Create New Supplier</h2>
        </div>
    </div>

    <!-- Form -->
    <div class="form-container">
        <form action="{{ route('admin.suppliers.store') }}" method="POST" id="supplierForm">
            @csrf

            <!-- Basic Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">Basic Information</h3>
                    <p class="card-description">Enter the basic details of the supplier</p>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Supplier Name <span class="required">*</span></label>
                            <input type="text" class="form-input" name="name" value="{{ old('name') }}" required placeholder="Enter supplier name">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-input" name="email" value="{{ old('email') }}" placeholder="supplier@example.com">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number <span class="required">*</span></label>
                            <input type="text" class="form-input" name="phone" value="{{ old('phone') }}" required placeholder="+91 9876543210">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alternate Phone</label>
                            <input type="text" class="form-input" name="alternate_phone" value="{{ old('alternate_phone') }}" placeholder="Alternate phone number">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-input" name="contact_person" value="{{ old('contact_person') }}" placeholder="Name of contact person">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">Address Information</h3>
                    <p class="card-description">Enter the supplier's address details</p>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Complete Address</label>
                            <textarea class="form-textarea" name="address" rows="3" placeholder="Enter complete address">{{ old('address') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" class="form-input" name="city" value="{{ old('city') }}" placeholder="City">
                        </div>

                        <div class="form-group">
                            <label class="form-label">State</label>
                            <input type="text" class="form-input" name="state" value="{{ old('state') }}" placeholder="State">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-input" name="pincode" value="{{ old('pincode') }}" placeholder="Pincode">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <select class="form-select" name="country">
                                <option value="India" {{ old('country', 'India') == 'India' ? 'selected' : '' }}>India</option>
                                <option value="USA" {{ old('country') == 'USA' ? 'selected' : '' }}>USA</option>
                                <option value="UK" {{ old('country') == 'UK' ? 'selected' : '' }}>UK</option>
                                <option value="Canada" {{ old('country') == 'Canada' ? 'selected' : '' }}>Canada</option>
                                <option value="Australia" {{ old('country') == 'Australia' ? 'selected' : '' }}>Australia</option>
                                <option value="Other" {{ old('country') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax & Legal Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">Tax & Legal Information</h3>
                    <p class="card-description">Enter GSTIN, PAN and other legal details</p>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">GSTIN</label>
                            <input type="text" class="form-input" name="gstin" value="{{ old('gstin') }}" placeholder="22AAAAA0000A1Z5">
                            <span class="form-hint">Format: 22AAAAA0000A1Z5</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">PAN Number</label>
                            <input type="text" class="form-input" name="pan" value="{{ old('pan') }}" placeholder="AAAAA1234A">
                            <span class="form-hint">Format: AAAAA1234A</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank & Payment Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">Bank & Payment Information</h3>
                    <p class="card-description">Enter bank details and payment terms</p>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Bank Name</label>
                            <input type="text" class="form-input" name="bank_name" value="{{ old('bank_name') }}" placeholder="Bank name">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Account Number</label>
                            <input type="text" class="form-input" name="account_number" value="{{ old('account_number') }}" placeholder="Account number">
                        </div>

                        <div class="form-group">
                            <label class="form-label">IFSC Code</label>
                            <input type="text" class="form-input" name="ifsc_code" value="{{ old('ifsc_code') }}" placeholder="IFSC code">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Payment Terms</label>
                            <select class="form-select" name="payment_terms">
                                <option value="Net 15" {{ old('payment_terms') == 'Net 15' ? 'selected' : '' }}>Net 15</option>
                                <option value="Net 30" {{ old('payment_terms', 'Net 30') == 'Net 30' ? 'selected' : '' }}>Net 30</option>
                                <option value="Net 45" {{ old('payment_terms') == 'Net 45' ? 'selected' : '' }}>Net 45</option>
                                <option value="Net 60" {{ old('payment_terms') == 'Net 60' ? 'selected' : '' }}>Net 60</option>
                                <option value="Cash on Delivery" {{ old('payment_terms') == 'Cash on Delivery' ? 'selected' : '' }}>Cash on Delivery</option>
                                <option value="Advance Payment" {{ old('payment_terms') == 'Advance Payment' ? 'selected' : '' }}>Advance Payment</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Credit Limit (₹)</label>
                            <input type="number" step="0.01" class="form-input" name="credit_limit" value="{{ old('credit_limit', 0) }}" placeholder="0.00">
                            <span class="form-hint">Maximum credit allowed</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">Additional Information</h3>
                    <p class="card-description">Any additional notes or information</p>
                </div>
                <div class="card-body">
                    <div class="form-group full-width">
                        <label class="form-label">Notes</label>
                        <textarea class="form-textarea" name="notes" rows="4" placeholder="Enter any additional notes or information">{{ old('notes') }}</textarea>
                        <span class="form-hint">These notes are for internal use only</span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="window.history.back()">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <span>✓</span> Create Supplier
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .create-supplier-container { padding-bottom: 40px; }
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; margin-bottom: 15px; }
    .back-btn:hover { background: #f7fafc; color: #667eea; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }

    .form-container { max-width: 1200px; }
    .form-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; }
    .card-header { padding: 25px 30px; border-bottom: 2px solid #e2e8f0; background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); }
    .card-title { font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .card-description { color: #718096; font-size: 14px; }
    .card-body { padding: 30px; }

    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: span 2; }
    .form-label { margin-bottom: 8px; font-weight: 600; color: #4a5568; font-size: 14px; display: flex; align-items: center; gap: 5px; }
    .required { color: #fc8181; font-size: 18px; }
    .form-input, .form-textarea, .form-select { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; font-family: inherit; }
    .form-input:focus, .form-textarea:focus, .form-select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
    .form-input.error, .form-textarea.error, .form-select.error { border-color: #fc8181; background: #fff5f5; }
    .form-textarea { min-height: 100px; resize: vertical; }
    .form-hint { margin-top: 6px; font-size: 12px; color: #a0aec0; }

    .form-actions { margin-top: 30px; display: flex; justify-content: flex-end; gap: 15px; padding-top: 20px; border-top: 2px solid #e2e8f0; }
    .btn-secondary { padding: 12px 24px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .btn-secondary:hover { background: #cbd5e0; transform: translateY(-2px); }
    .btn-primary { padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
    }
</style>
@endpush

@push('scripts')
<script>
    // Show alert function
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span class="alert-icon">${type === 'success' ? '✓' : '✕'}</span><span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => { alert.classList.add('removing'); setTimeout(() => alert.remove(), 300); }, 5000);
    }

    // Form validation
    document.getElementById('supplierForm').addEventListener('submit', function(e) {
        const requiredInputs = this.querySelectorAll('[required]');
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

        if (!isValid) {
            e.preventDefault();
            firstErrorField?.focus();
            showAlert('Please fill in all required fields', 'error');
            window.scrollTo({ top: firstErrorField?.offsetTop - 100, behavior: 'smooth' });
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
