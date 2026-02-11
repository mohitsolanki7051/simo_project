@extends('layouts.admin')

@section('title', 'Create Attribute - Admin Panel')
@section('header-title', 'Attributes Management')

@section('content')
<div class="products-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Add New Attribute</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.attributes.index') }}" class="btn-small btn-cancel">
                <span class="btn-icon">←</span> Back to attribute
            </a>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-wrapper">
        <form action="{{ route('admin.attributes.store') }}" method="POST" id="attributeForm">
            @csrf

            <!-- Basic Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">Basic Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <span class="label-text">Attribute Type</span>
                                <span class="required">*</span>
                            </label>
                            <input type="text" class="form-input" name="type" id="typeInput"
                                   value="{{ old('type') }}"
                                   placeholder="e.g., color, size, material"
                                   required>
                            <div class="form-hint">
                                Use lowercase letters. Spaces will be converted to underscores.
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <span class="label-text">Status</span>
                                <span class="required">*</span>
                            </label>
                            <select class="form-select" name="status" id="statusSelect" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : 'selected' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Values Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">Attribute Values</h3>
                    <div class="card-subtitle">Add values for this attribute type</div>
                </div>
                <div class="card-body">
                    <!-- Value Input -->
                    <div class="value-input-section">
                        <div class="input-with-button">
                            <input type="text"
                                   class="form-input"
                                   id="valueInput"
                                   placeholder="Type value and press Enter (e.g., Red, Blue, Green)">
                            <button type="button" class="btn-add-value" id="addValueBtn">
                                <span class="btn-icon">+</span> Add Value
                            </button>
                        </div>
                        <div class="form-hint">Press Enter or click Add Value to add multiple values</div>
                    </div>

                    <!-- Selected Values -->
                    <div class="selected-values-section">
                        <div class="section-header">
                            <div class="section-title">
                                <span class="title-text">Selected Values</span>
                                <span class="badge-count" id="valuesCount">0</span>
                            </div>
                            <button type="button" class="btn-secondary btn-clear-all" id="clearAllBtn">
                                Clear All
                            </button>
                        </div>

                        <div class="values-container">
                            <div class="values-tags" id="valuesTags">
                                <div class="empty-state">
                                    <div class="empty-icon">🎨</div>
                                    <p>No values added yet</p>
                                </div>
                            </div>

                            <!-- Hidden Inputs -->
                            <div id="hiddenValuesInputs"></div>
                        </div>
                    </div>

                  
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.attributes.index') }}" class="btn-action btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-action btn-primary" id="submitBtn">
                    Create Attribute with <span id="submitCount">0</span> Values
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    /* Main Container */
    .products-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 12px;
        line-height: 1.4;
        min-height: 100vh;
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    }

    /* Alert Messages */
    #alertContainer {
        position: fixed;
        top: 90px;
        right: 35px;
        z-index: 9999;
        max-width: 400px;
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease;
        font-weight: 600;
        font-size: 11px;
        border-left: 4px solid transparent;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left-color: #10b981;
    }

    .alert-error {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-left-color: #ef4444;
    }

    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-title {
        font-size: 20px;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }

    .btn-small {
        padding: 5px 10px;
        background: white;
        color: #fa8128;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-small:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
        transform: translateY(-1px);
    }

    .btn-icon {
        font-size: 12px;
    }

    /* Form Wrapper */
    .form-wrapper {
        max-width: 100%;
    }

    /* Form Card */
    .form-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 10px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

    .card-header {
        padding: 18px 24px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e5e7eb;
    }

    .card-title {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin: 0;
    }

    .card-subtitle {
        font-size: 11px;
        color: #6b7280;
        margin-top: 4px;
    }

    .card-body {
        padding: 24px;
    }

    /* Form Grid */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 0;
    }

    .form-group {
        margin-bottom: 0;
    }

    /* Form Elements */
    .form-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .required {
        color: #ef4444;
        margin-left: 2px;
    }

    .form-input, .form-select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 12px;
        font-family: inherit;
        transition: all 0.2s;
        background: white;
    }

    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: white;
    }

    .form-hint {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 6px;
        line-height: 1.4;
    }

    /* Value Input Section */
    .value-input-section {
        margin-bottom: 25px;
    }

    .input-with-button {
        display: flex;
        gap: 12px;
        margin-bottom: 8px;
    }

    .input-with-button .form-input {
        flex: 1;
    }

    .btn-add-value {
        padding: 10px 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .btn-add-value:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.2);
    }

    /* Selected Values Section */
    .selected-values-section {
        margin-bottom: 25px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .title-text {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }

    .badge-count {
        background: #667eea;
        color: white;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 12px;
        min-width: 30px;
        text-align: center;
    }

    .btn-secondary {
        padding: 8px 16px;
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
        border-color: #9ca3af;
    }

    .btn-clear-all {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }

    .btn-clear-all:hover {
        background: #fecaca;
        color: #7f1d1d;
    }

    .values-container {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        min-height: 120px;
    }

    /* Values Tags */
    .values-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        min-height: 40px;
    }

    .value-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        color: #374151;
        animation: fadeIn 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: all 0.2s;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    .value-tag:hover {
        border-color: #667eea;
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.1);
        transform: translateY(-1px);
    }

    .value-tag .tag-text {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .value-tag .tag-remove {
        color: #ef4444;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
        transition: all 0.2s;
        padding: 2px;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .value-tag .tag-remove:hover {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Empty State */
    .empty-state {
        color: #9ca3af;
        font-style: italic;
        padding: 30px;
        width: 100%;
        text-align: center;
    }

    .empty-icon {
        font-size: 32px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .empty-state p {
        margin: 0;
        font-size: 12px;
    }

    /* Info Card */
    .info-card {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 1px solid #bae6fd;
        border-radius: 10px;
        padding: 20px;
    }

    .info-header {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 15px;
    }

    .info-icon {
        font-size: 24px;
        color: #0369a1;
        margin-top: 2px;
    }

    .info-title-section {
        flex: 1;
    }

    .info-title {
        font-size: 13px;
        font-weight: 700;
        color: #0369a1;
        margin: 0 0 4px 0;
    }

    .info-subtitle {
        font-size: 11px;
        color: #0c4a6e;
        margin: 0;
    }

    .info-list {
        margin: 0;
        padding-left: 20px;
    }

    .info-list li {
        font-size: 12px;
        color: #0c4a6e;
        margin: 8px 0;
        line-height: 1.5;
    }

    .info-list li strong {
        color: #0369a1;
        font-weight: 600;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        border-top: 1px solid #e5e7eb;
    }

    .btn-action {
        padding: 10px 15px;
        border: none;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-width: 160px;
        justify-content: center;
    }

    .btn-secondary {
        background: white;
        color: #4b5563;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #fa8427 0%, #f97316 100%);
        color: white;
        border: 1px solid #fa8427;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #e97317 0%, #ea580c 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(250, 132, 39, 0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .products-container {
            padding: 20px;
        }

        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .form-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .input-with-button {
            flex-direction: column;
        }

        .btn-add-value {
            width: 100%;
            justify-content: center;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .products-container {
            padding: 15px;
        }

        .card-body {
            padding: 20px;
        }

        .info-card {
            padding: 16px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let attributeValues = [];

    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    function addValue(value) {
        value = value.trim();
        if (!value) return;

        // Check if value already exists
        if (attributeValues.includes(value)) {
            showAlert(`"${value}" is already added`, 'error');
            return;
        }

        // Add to array
        attributeValues.push(value);

        // Update UI
        updateValuesDisplay();

        // Clear input
        document.getElementById('valueInput').value = '';

        // Focus back on input
        document.getElementById('valueInput').focus();
    }

    function removeValue(value) {
        const index = attributeValues.indexOf(value);
        if (index > -1) {
            attributeValues.splice(index, 1);
            updateValuesDisplay();
        }
    }

    function updateValuesDisplay() {
        const container = document.getElementById('valuesTags');
        const countElement = document.getElementById('valuesCount');
        const submitCountElement = document.getElementById('submitCount');
        const hiddenInputsContainer = document.getElementById('hiddenValuesInputs');

        // Update count
        countElement.textContent = attributeValues.length;
        submitCountElement.textContent = attributeValues.length;

        // Clear containers
        container.innerHTML = '';
        hiddenInputsContainer.innerHTML = '';

        if (attributeValues.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">🎨</div>
                    <p>No values added yet</p>
                </div>
            `;
            return;
        }

        // Create tags and hidden inputs
        attributeValues.forEach((value, index) => {
            // Create tag
            const tag = document.createElement('div');
            tag.className = 'value-tag';
            tag.innerHTML = `
                <span class="tag-text">${value}</span>
                <span class="tag-remove" onclick="removeValue('${value.replace(/'/g, "\\'")}')">×</span>
            `;
            container.appendChild(tag);

            // Create hidden input
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `values[${index}]`;
            hiddenInput.value = value;
            hiddenInputsContainer.appendChild(hiddenInput);
        });
    }

    function clearAllValues() {
        if (attributeValues.length === 0) return;

        if (confirm(`Are you sure you want to remove all ${attributeValues.length} values?`)) {
            attributeValues = [];
            updateValuesDisplay();
        }
    }

    // Format attribute type as user types
    document.getElementById('typeInput').addEventListener('input', function(e) {
        let value = e.target.value.toLowerCase().trim();
        value = value.replace(/\s+/g, '_');
        e.target.value = value;
    });

    // Handle Enter key in value input
    document.getElementById('valueInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addValue(this.value);
        }
    });

    // Handle Add button click
    document.getElementById('addValueBtn').addEventListener('click', function() {
        const input = document.getElementById('valueInput');
        addValue(input.value);
    });

    // Handle Clear All button
    document.getElementById('clearAllBtn').addEventListener('click', clearAllValues);

    // Form validation before submission
    document.getElementById('attributeForm').addEventListener('submit', function(e) {
        if (attributeValues.length === 0) {
            e.preventDefault();
            showAlert('Please add at least one attribute value', 'error');
            document.getElementById('valueInput').focus();
            return;
        }

        if (!document.getElementById('typeInput').value.trim()) {
            e.preventDefault();
            showAlert('Please enter an attribute type', 'error');
            document.getElementById('typeInput').focus();
            return;
        }
    });

    @if($errors->any())
        @foreach($errors->all() as $error)
            showAlert('{{ $error }}', 'error');
        @endforeach
    @endif

    @if(session('success'))
        showAlert('{{ session("success") }}', 'success');
    @endif

    @if(session('error'))
        showAlert('{{ session("error") }}', 'error');
    @endif

    // Auto-focus on type input
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('typeInput').focus();
    });
</script>
@endpush
@endsection
