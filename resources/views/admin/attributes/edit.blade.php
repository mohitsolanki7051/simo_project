@extends('layouts.admin')

@section('title', 'Edit Attribute - Admin Panel')
@section('header-title', 'Edit Attribute')

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
            <h2 class="page-title">Edit Attribute</h2>
        </div>
    </div>

    <form action="{{ route('admin.attributes.update', $attribute->id) }}" method="POST" id="attributeForm">
        @csrf
        @method('PUT')

        <div class="form-container">
            <div class="form-section">
                <h3 class="section-title">Attribute Information</h3>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Attribute Name <span class="required">*</span></label>
                        <input type="text" class="form-input" name="name"
                               value="{{ old('name', $attribute->name) }}"
                               placeholder="e.g., Wattage, Color Temperature" required>
                        <span class="form-hint">Enter a unique name for this attribute</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Attribute Type <span class="required">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="">Select Type</option>
                            <option value="watt" {{ old('type', $attribute->type) == 'watt' ? 'selected' : '' }}>Watt</option>
                            <option value="shape" {{ old('type', $attribute->type) == 'shape' ? 'selected' : '' }}>Shape</option>
                            <option value="color_temperature" {{ old('type', $attribute->type) == 'color_temperature' ? 'selected' : '' }}>Color Temperature</option>
                            <option value="cutting_size" {{ old('type', $attribute->type) == 'cutting_size' ? 'selected' : '' }}>Cutting/Size</option>
                            <option value="other" {{ old('type', $attribute->type) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select class="form-select" name="status" required>
                            <option value="active" {{ old('status', $attribute->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $attribute->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-input" name="sort_order"
                               value="{{ old('sort_order', $attribute->sort_order ?? 0) }}" min="0">
                        <span class="form-hint">Lower numbers appear first</span>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3 class="section-title">Attribute Values</h3>
                    <button type="button" class="btn-add-value" onclick="addValue()">
                        <span>+</span> Add Value
                    </button>
                </div>

                <div id="valuesContainer" class="values-container">
                    @if(old('values'))
                        @foreach(old('values') as $value)
                            <div class="value-item" id="value-{{ $loop->index }}">
                                <input type="text" class="form-input" name="values[]"
                                       value="{{ $value }}"
                                       placeholder="Enter value" required>
                                <button type="button" class="btn-remove-value" onclick="removeValue({{ $loop->index }})">
                                    Remove
                                </button>
                            </div>
                        @endforeach
                    @elseif($attribute->values && is_array($attribute->values))
                        @foreach($attribute->values as $value)
                            <div class="value-item" id="value-{{ $loop->index }}">
                                <input type="text" class="form-input" name="values[]"
                                       value="{{ $value }}"
                                       placeholder="Enter value" required>
                                <button type="button" class="btn-remove-value" onclick="removeValue({{ $loop->index }})">
                                    Remove
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="form-hint">At least one value is required</div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route('admin.attributes.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-primary">
                <span>✓</span> Update Attribute
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
    .create-attribute-container { padding: 30px; max-width: 1000px; margin: 0 auto; }

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
    .form-section { padding: 30px; border-bottom: 2px solid #e2e8f0; }
    .form-section:last-child { border-bottom: none; }

    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .section-title { font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 20px; }

    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: span 2; }
    .form-label { margin-bottom: 8px; font-weight: 600; color: #4a5568; font-size: 14px; }
    .required { color: #fc8181; }
    .form-input, .form-select { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; font-family: inherit; }
    .form-input:focus, .form-select:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
    .form-hint { margin-top: 6px; font-size: 12px; color: #a0aec0; }

    .btn-add-value { padding: 8px 16px; background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 6px; }
    .btn-add-value:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(72,187,120,0.3); }

    .values-container { display: flex; flex-direction: column; gap: 12px; margin-bottom: 15px; }
    .value-item { display: flex; gap: 12px; align-items: center; background: #f7fafc; padding: 12px; border-radius: 10px; border: 2px solid #e2e8f0; }
    .value-item input { flex: 1; }
    .btn-remove-value { padding: 8px 12px; background: #fc8181; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; }
    .btn-remove-value:hover { background: #f56565; transform: scale(1.05); }

    .form-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; }
    .btn-cancel { padding: 12px 24px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 10px; font-weight: 600; text-decoration: none; transition: all 0.3s; }
    .btn-cancel:hover { background: #cbd5e0; }
    .btn-primary { padding: 12px 30px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(255,107,53,0.3); display: flex; align-items: center; gap: 8px; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.4); }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
    }
</style>
@endpush

@push('scripts')
<script>
    let valueCount = {{ $attribute->values && is_array($attribute->values) ? count($attribute->values) : 0 }};

    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    function addValue() {
        valueCount++;
        const container = document.getElementById('valuesContainer');

        const valueHtml = `
            <div class="value-item" id="value-${valueCount}">
                <input type="text" class="form-input" name="values[]"
                       placeholder="Enter value (e.g., 9W, Round, WW)" required>
                <button type="button" class="btn-remove-value" onclick="removeValue(${valueCount})">
                    Remove
                </button>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', valueHtml);
    }

    function removeValue(id) {
        const item = document.getElementById(`value-${id}`);
        if (item) {
            item.remove();
        }
    }

    // Form validation
    document.getElementById('attributeForm').addEventListener('submit', function(e) {
        const values = document.querySelectorAll('input[name="values[]"]');
        let hasValue = false;

        values.forEach(input => {
            if (input.value.trim()) {
                hasValue = true;
            }
        });

        if (!hasValue) {
            e.preventDefault();
            showAlert('Please add at least one value', 'error');
            return false;
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
