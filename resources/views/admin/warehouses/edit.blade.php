@extends('layouts.admin')

@section('title', 'Edit Warehouse - Admin Panel')
@section('header-title', 'Edit Warehouse')

@section('content')
<div class="edit-warehouse-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('admin.warehouses.index') }}" class="back-btn">
                <span>←</span> Back to Warehouses
            </a>
            <h2 class="page-title">Edit Warehouse</h2>
            <p class="page-subtitle">Update warehouse information</p>
        </div>
    </div>

    <form action="{{ route('admin.warehouses.update', $warehouse->id) }}" method="POST" id="warehouseForm">
        @csrf
        @method('PUT')

        <div class="form-card">
            <!-- Basic Information -->
            <div class="form-section">
                <div class="section-header">
                    <h3 class="section-title">🏢 Basic Information</h3>
                    <p class="section-description">Update warehouse identification details</p>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Warehouse Name <span class="required">*</span></label>
                        <input type="text" class="form-input" name="name" value="{{ old('name', $warehouse->name) }}"
                            placeholder="e.g., Main Warehouse Delhi" required>
                        @error('name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Warehouse Code <span class="required">*</span></label>
                        <input type="text" class="form-input" name="code" value="{{ old('code', $warehouse->code) }}"
                            placeholder="e.g., WH-DLH-001" required>
                        <span class="form-hint">Unique identifier for the warehouse</span>
                        @error('code')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="status" value="active"
                                    {{ old('status', $warehouse->status) == 'active' ? 'checked' : '' }} required>
                                <span class="radio-custom"></span>
                                <span class="radio-text">Active</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="status" value="inactive"
                                    {{ old('status', $warehouse->status) == 'inactive' ? 'checked' : '' }}>
                                <span class="radio-custom"></span>
                                <span class="radio-text">Inactive</span>
                            </label>
                        </div>
                        @error('status')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Location Details -->
            <div class="form-section">
                <div class="section-header">
                    <h3 class="section-title">📍 Location Details</h3>
                    <p class="section-description">Update warehouse address</p>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Full Address <span class="required">*</span></label>
                        <textarea class="form-textarea" name="address" rows="3"
                            placeholder="Enter complete street address" required>{{ old('address', $warehouse->address) }}</textarea>
                        @error('address')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">City <span class="required">*</span></label>
                        <input type="text" class="form-input" name="city" value="{{ old('city', $warehouse->city) }}"
                            placeholder="e.g., Delhi" required>
                        @error('city')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">State <span class="required">*</span></label>
                        <input type="text" class="form-input" name="state" value="{{ old('state', $warehouse->state) }}"
                            placeholder="e.g., Delhi" required>
                        @error('state')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">PIN Code <span class="required">*</span></label>
                        <input type="text" class="form-input" name="pincode" value="{{ old('pincode', $warehouse->pincode) }}"
                            placeholder="e.g., 110001" maxlength="10" required>
                        @error('pincode')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="form-section">
                <div class="section-header">
                    <h3 class="section-title">📞 Contact Information</h3>
                    <p class="section-description">Update warehouse contact details</p>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Phone Number <span class="required">*</span></label>
                        <input type="text" class="form-input" name="phone" value="{{ old('phone', $warehouse->phone) }}"
                            placeholder="e.g., +91 98765 43210" maxlength="15" required>
                        @error('phone')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input" name="email" value="{{ old('email', $warehouse->email) }}"
                            placeholder="e.g., warehouse@example.com">
                        @error('email')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Manager Name</label>
                        <input type="text" class="form-input" name="manager_name" value="{{ old('manager_name', $warehouse->manager_name) }}"
                            placeholder="e.g., John Doe">
                        @error('manager_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.warehouses.index') }}" class="btn-cancel">
                    <span>✕</span> Cancel
                </a>
                <button type="button" class="btn-delete" onclick="deleteWarehouse()">
                    <span>🗑️</span> Delete
                </button>
                <button type="submit" class="btn-submit">
                    <span>✓</span> Update Warehouse
                </button>
            </div>
        </div>
    </form>

    <!-- Hidden Delete Form -->
    <form id="deleteForm" action="{{ route('admin.warehouses.destroy', $warehouse->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('styles')
<style>
    .edit-warehouse-container { padding-bottom: 40px; }

    /* Alert Styles */
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; margin-bottom: 15px; }
    .back-btn:hover { background: #f7fafc; color: #ff6b35; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 8px; }
    .page-subtitle { font-size: 14px; color: #718096; }

    .form-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 40px; }

    .form-section { margin-bottom: 40px; padding-bottom: 40px; border-bottom: 2px solid #e2e8f0; }
    .form-section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

    .section-header { margin-bottom: 25px; }
    .section-title { font-size: 20px; font-weight: 700; color: #2d3748; margin-bottom: 8px; }
    .section-description { font-size: 14px; color: #718096; }

    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: span 2; }

    .form-label { margin-bottom: 8px; font-weight: 600; color: #4a5568; font-size: 14px; }
    .required { color: #fc8181; }

    .form-input, .form-textarea { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; font-family: inherit; }
    .form-input:focus, .form-textarea:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
    .form-input.error, .form-textarea.error { border-color: #fc8181; }
    .form-textarea { min-height: 100px; resize: vertical; }

    .form-hint { margin-top: 6px; font-size: 12px; color: #a0aec0; }
    .error-text { margin-top: 6px; font-size: 12px; color: #fc8181; font-weight: 600; }

    .radio-group { display: flex; gap: 20px; }
    .radio-label { display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px 20px; border: 2px solid #e2e8f0; border-radius: 10px; transition: all 0.3s; }
    .radio-label:hover { border-color: #cbd5e0; background: #f7fafc; }
    .radio-label input[type="radio"] { display: none; }
    .radio-custom { width: 20px; height: 20px; border: 2px solid #cbd5e0; border-radius: 50%; position: relative; transition: all 0.3s; }
    .radio-label input[type="radio"]:checked ~ .radio-custom { border-color: #ff6b35; background: #ff6b35; }
    .radio-label input[type="radio"]:checked ~ .radio-custom::after { content: ''; position: absolute; top: 50%; left: 50%; width: 8px; height: 8px; background: white; border-radius: 50%; transform: translate(-50%, -50%); }
    .radio-text { font-weight: 600; color: #4a5568; }

    .form-actions { display: flex; gap: 15px; justify-content: flex-end; margin-top: 40px; padding-top: 30px; border-top: 2px solid #e2e8f0; }
    .btn-cancel { padding: 12px 24px; background: #e2e8f0; color: #4a5568; text-decoration: none; border-radius: 10px; font-weight: 600; transition: all 0.3s; display: flex; align-items: center; gap: 8px; border: none; cursor: pointer; }
    .btn-cancel:hover { background: #cbd5e0; transform: translateY(-2px); }
    .btn-delete { padding: 12px 24px; background: linear-gradient(135deg, #fc8181 0%, #f56565 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(252,129,129,0.3); display: flex; align-items: center; gap: 8px; }
    .btn-delete:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(252,129,129,0.4); }
    .btn-submit { padding: 12px 30px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(255,107,53,0.3); display: flex; align-items: center; gap: 8px; }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.4); }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
        .form-card { padding: 25px; }
        .form-actions { flex-direction: column; }
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
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    // Form validation
    document.getElementById('warehouseForm').addEventListener('submit', function(e) {
        let isValid = true;
        const requiredInputs = document.querySelectorAll('[required]');

        requiredInputs.forEach(input => {
            input.classList.remove('error');
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            showAlert('Please fill in all required fields', 'error');
            const firstError = document.querySelector('.error');
            if (firstError) {
                firstError.focus();
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Remove error class on input
    document.querySelectorAll('.form-input, .form-textarea').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('error');
        });
    });

    // Delete warehouse function
    function deleteWarehouse() {
        if (confirm('Are you sure you want to delete this warehouse? This action cannot be undone.')) {
            document.getElementById('deleteForm').submit();
        }
    }

    // Show Laravel errors
    @if($errors->any())
        @foreach($errors->all() as $error)
            showAlert('{{ $error }}', 'error');
        @endforeach
    @endif

    // Show success message
    @if(session('success'))
        showAlert('{{ session('success') }}', 'success');
    @endif

    // Show error message
    @if(session('error'))
        showAlert('{{ session('error') }}', 'error');
    @endif
</script>
@endpush
@endsection
