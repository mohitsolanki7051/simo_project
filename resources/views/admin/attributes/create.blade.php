@extends('layouts.admin')

@section('title', 'Create Attribute - Admin Panel')
@section('header-title', 'Create New Attribute Value')

@section('content')
<div class="create-attribute-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('admin.attributes.index') }}" class="back-btn">
                <span>←</span> Back to Attributes
            </a>
            <h2 class="page-title">Add New Attribute Value</h2>
        </div>
    </div>

    <form action="{{ route('admin.attributes.store') }}" method="POST" id="attributeForm">
        @csrf

        <div class="form-container">
            <div class="form-section">
                <h3 class="section-title">Attribute Value Information</h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Attribute Type <span class="required">*</span></label>
                        <select class="form-select" name="type" id="typeSelect" required>
                            <option value="">Select Attribute Type</option>
                            @foreach($attributeTypes as $key => $name)
                            <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Attribute Value <span class="required">*</span></label>
                        <input type="text" class="form-input" name="value" id="valueInput"
                               value="{{ old('value') }}" placeholder="e.g., 9W, Round, WW" required>
                        <span class="form-hint" id="valueHint">
                            Enter value for selected attribute type
                        </span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select class="form-select" name="status" required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="attribute-examples" id="attributeExamples">
                    <div class="examples-title">Examples:</div>
                    <div class="examples-content">
                        <div class="example-item">
                            <strong>Color Temperature:</strong> WW (Warm White), NW (Natural White), CW (Cool White)
                        </div>
                        <div class="example-item">
                            <strong>Watt:</strong> 9W, 12W, 18W, 24W
                        </div>
                        <div class="example-item">
                            <strong>Shape:</strong> Round, Square, Oval, Rectangular
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route('admin.attributes.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-primary">
                <span>✓</span> Create Attribute Value
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
    .create-attribute-container { padding: 30px; max-width: 800px; margin: 0 auto; }

    /* Alert Styles */
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; margin-bottom: 15px; }
    .back-btn:hover { background: #f7fafc; color: #ff6b35; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; }

    .form-container { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
    .form-section { padding: 30px; }

    .section-title { font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 20px; }

    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 25px; }
    .form-group { display: flex; flex-direction: column; }
    .form-label { margin-bottom: 8px; font-weight: 600; color: #4a5568; font-size: 14px; }
    .required { color: #fc8181; }
    .form-input, .form-select { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; font-family: inherit; }
    .form-input:focus, .form-select:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
    .form-hint { margin-top: 6px; font-size: 12px; color: #a0aec0; }

    .attribute-examples {
        background: #f7fafc;
        padding: 20px;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        margin-top: 20px;
    }

    .examples-title {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 10px;
        font-size: 15px;
    }

    .examples-content {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .example-item {
        font-size: 13px;
        color: #4a5568;
        padding: 5px 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .example-item:last-child {
        border-bottom: none;
    }

    .example-item strong {
        color: #2d3748;
    }

    .form-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; }
    .btn-cancel { padding: 12px 24px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 10px; font-weight: 600; text-decoration: none; transition: all 0.3s; }
    .btn-cancel:hover { background: #cbd5e0; }
    .btn-primary { padding: 12px 30px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(255,107,53,0.3); display: flex; align-items: center; gap: 8px; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.4); }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script>
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    // Update placeholder and hint based on selected type
    document.getElementById('typeSelect').addEventListener('change', function() {
        const valueInput = document.getElementById('valueInput');
        const valueHint = document.getElementById('valueHint');

        const placeholders = {
            'color_temperature': 'e.g., WW, NW, CW',
            'watt': 'e.g., 9W, 12W, 18W',
            'shape': 'e.g., Round, Square, Oval'
        };

        const hints = {
            'color_temperature': 'Enter color temperature value',
            'watt': 'Enter wattage value with unit (W)',
            'shape': 'Enter shape name'
        };

        if (this.value in placeholders) {
            valueInput.placeholder = placeholders[this.value];
            valueHint.textContent = hints[this.value];
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
</script>
@endpush
@endsection
