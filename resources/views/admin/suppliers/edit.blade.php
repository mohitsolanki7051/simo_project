@extends('layouts.admin')

@section('title', 'Edit Supplier - Admin Panel')
@section('header-title', 'Edit Supplier')

@section('content')
<div class="edit-supplier-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('admin.suppliers.index') }}" class="back-btn">
                <span>←</span> Back to Suppliers
            </a>
            <h2 class="page-title">Edit Supplier: {{ $supplier->name }}</h2>
            <p class="supplier-code">Supplier Code: <strong>{{ $supplier->supplier_code }}</strong></p>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.suppliers.ledger', $supplier->id) }}" class="btn-ledger">
                📊 View Ledger
            </a>
        </div>
    </div>

    <!-- Current Balance Alert -->
    <div class="balance-alert">
        <div class="balance-icon">💰</div>
        <div class="balance-info">
            <div class="balance-label">Current Balance</div>
            <div class="balance-value {{ $supplier->current_balance >= 0 ? 'balance-debit' : 'balance-credit' }}">
                {{ $supplier->formatted_current_balance }}
            </div>
            <div class="balance-description">
                @if($supplier->current_balance > 0)
                    You owe ₹{{ number_format(abs($supplier->current_balance), 2) }} to this supplier
                @elseif($supplier->current_balance < 0)
                    Supplier has ₹{{ number_format(abs($supplier->current_balance), 2) }} advance
                @else
                    All cleared - No pending balance
                @endif
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="form-container">
        <form action="{{ route('admin.suppliers.update', $supplier->id) }}" method="POST" id="supplierForm">
            @csrf
            @method('PUT')

            <!-- Basic Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">📋 Basic Information</h3>
                    <p class="card-description">Update the basic details of the supplier</p>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Supplier Name <span class="required">*</span></label>
                            <input type="text" class="form-input" name="name" value="{{ old('name', $supplier->name) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-input" name="email" value="{{ old('email', $supplier->email) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number <span class="required">*</span></label>
                            <input type="text" class="form-input" name="phone" value="{{ old('phone', $supplier->phone) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alternate Phone</label>
                            <input type="text" class="form-input" name="alternate_phone" value="{{ old('alternate_phone', $supplier->alternate_phone) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-input" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="active" {{ old('status', $supplier->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $supplier->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">📍 Address Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Complete Address</label>
                            <textarea class="form-textarea" name="address" rows="3">{{ old('address', $supplier->address) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" class="form-input" name="city" value="{{ old('city', $supplier->city) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">State</label>
                            <input type="text" class="form-input" name="state" value="{{ old('state', $supplier->state) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-input" name="pincode" value="{{ old('pincode', $supplier->pincode) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <select class="form-select" name="country">
                                <option value="India" {{ old('country', $supplier->country) == 'India' ? 'selected' : '' }}>India</option>
                                <option value="USA" {{ old('country', $supplier->country) == 'USA' ? 'selected' : '' }}>USA</option>
                                <option value="UK" {{ old('country', $supplier->country) == 'UK' ? 'selected' : '' }}>UK</option>
                                <option value="Canada" {{ old('country', $supplier->country) == 'Canada' ? 'selected' : '' }}>Canada</option>
                                <option value="Australia" {{ old('country', $supplier->country) == 'Australia' ? 'selected' : '' }}>Australia</option>
                                <option value="Other" {{ old('country', $supplier->country) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax & Legal Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">📄 Tax & Legal Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">GSTIN</label>
                            <input type="text" class="form-input" name="gstin" value="{{ old('gstin', $supplier->gstin) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">PAN Number</label>
                            <input type="text" class="form-input" name="pan" value="{{ old('pan', $supplier->pan) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank & Payment Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">🏦 Bank & Payment Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Bank Name</label>
                            <input type="text" class="form-input" name="bank_name" value="{{ old('bank_name', $supplier->bank_name) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Account Number</label>
                            <input type="text" class="form-input" name="account_number" value="{{ old('account_number', $supplier->account_number) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">IFSC Code</label>
                            <input type="text" class="form-input" name="ifsc_code" value="{{ old('ifsc_code', $supplier->ifsc_code) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Payment Terms</label>
                            <select class="form-select" name="payment_terms">
                                <option value="Net 15" {{ old('payment_terms', $supplier->payment_terms) == 'Net 15' ? 'selected' : '' }}>Net 15</option>
                                <option value="Net 30" {{ old('payment_terms', $supplier->payment_terms) == 'Net 30' ? 'selected' : '' }}>Net 30</option>
                                <option value="Net 45" {{ old('payment_terms', $supplier->payment_terms) == 'Net 45' ? 'selected' : '' }}>Net 45</option>
                                <option value="Net 60" {{ old('payment_terms', $supplier->payment_terms) == 'Net 60' ? 'selected' : '' }}>Net 60</option>
                                <option value="Cash on Delivery" {{ old('payment_terms', $supplier->payment_terms) == 'Cash on Delivery' ? 'selected' : '' }}>Cash on Delivery</option>
                                <option value="Advance Payment" {{ old('payment_terms', $supplier->payment_terms) == 'Advance Payment' ? 'selected' : '' }}>Advance Payment</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">📝 Additional Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-group full-width">
                        <label class="form-label">Notes</label>
                        <textarea class="form-textarea" name="notes" rows="4">{{ old('notes', $supplier->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.suppliers.index') }}" class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <span>✓</span> Update Supplier
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .edit-supplier-container { padding-bottom: 40px; }
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; margin-bottom: 10px; }
    .back-btn:hover { background: #f7fafc; color: #667eea; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .supplier-code { font-size: 14px; color: #667eea; font-weight: 600; }
    .btn-ledger { padding: 10px 20px; background: linear-gradient(135deg, #38a169 0%, #68d391 100%); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; box-shadow: 0 4px 12px rgba(56,161,105,0.3); }
    .btn-ledger:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(56,161,105,0.4); }

    .balance-alert { background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%); border: 2px solid #4299e1; border-radius: 16px; padding: 25px; margin-bottom: 30px; display: flex; align-items: center; gap: 20px; }
    .balance-icon { font-size: 48px; }
    .balance-info { flex: 1; }
    .balance-label { font-size: 14px; color: #2c5282; font-weight: 600; margin-bottom: 5px; }
    .balance-value { font-size: 32px; font-weight: 700; margin-bottom: 5px; }
    .balance-debit { color: #c05621; }
    .balance-credit { color: #22543d; }
    .balance-description { font-size: 14px; color: #2c5282; }

    .form-container { max-width: 1200px; }
    .form-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; }
    .card-header { padding: 25px 30px; border-bottom: 2px solid #e2e8f0; background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); }
    .card-title { font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .card-description { color: #718096; font-size: 14px; }
    .card-body { padding: 30px; }

    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: span 2; }
    .form-label { margin-bottom: 8px; font-weight: 600; color: #4a5568; font-size: 14px; }
    .required { color: #fc8181; }
    .form-input, .form-textarea, .form-select { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; font-family: inherit; }
    .form-input:focus, .form-textarea:focus, .form-select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
    .form-textarea { min-height: 100px; resize: vertical; }

    .form-actions { margin-top: 30px; display: flex; justify-content: flex-end; gap: 15px; padding-top: 20px; border-top: 2px solid #e2e8f0; }
    .btn-secondary { padding: 12px 24px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block; }
    .btn-secondary:hover { background: #cbd5e0; transform: translateY(-2px); }
    .btn-primary { padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
        .page-header { flex-direction: column; }
    }
</style>
@endpush

@push('scripts')
<script>
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${type === 'success' ? '✓' : '✕'}</span><span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    @if($errors->any())
        @foreach($errors->all() as $error)
            showAlert('{{ $error }}', 'error');
        @endforeach
    @endif

    @if(session('success'))
        showAlert('{{ session('success') }}', 'success');
    @endif
</script>
@endpush
@endsection
