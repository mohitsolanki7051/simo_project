@extends('layouts.admin')

@section('title', 'Edit Attribute - Admin Panel')
@section('header-title', 'Attributes Management')

@section('content')
<div class="products-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Edit Attribute</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.attributes.index') }}" class="btn-small btn-cancel">
                <span class="btn-icon">←</span> Back to Attributes
            </a>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-wrapper">
        <form action="{{ route('admin.attributes.update', $attribute->_id) }}" method="POST" id="attributeForm">
            @csrf
            @method('PUT')

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
                                   value="{{ old('type', $attribute->type) }}"
                                   placeholder="e.g., color, size, material">
                            <div class="form-hint">
                                Enter a unique attribute type name
                            </div>
                            @error('type')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <span class="label-text">Status</span>
                                <span class="required">*</span>
                            </label>
                            <select class="form-select" name="status" id="statusSelect" required onchange="handleAttributeStatusChange(this)">
                                <option value="active" {{ old('status', $attribute->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $attribute->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <div class="form-hint" id="statusHint">
                                @if($attribute->status == 'inactive')
                                    <span class="warning-text">⚠️ When attribute is inactive, all values remain inactive</span>
                                @else
                                    New values will be created with <span id="statusText">{{ old('status', $attribute->status) }}</span> status.
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Existing Values Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">Edit Existing Values</h3>
                    <div class="card-subtitle">Edit or update existing attribute values ({{ $attribute->items->count() }} total)</div>
                </div>
                <div class="card-body">
                    @php
                        $allItems = $attribute->items->toArray();
                        $activeCount = $attribute->items->where('status', 'active')->count();
                        $inactiveCount = $attribute->items->where('status', 'inactive')->count();
                    @endphp



                    <!-- Values Grid -->
                    <div class="existing-values-grid">
                        @if(count($allItems) > 0)
                            @foreach($attribute->items as $item)
                                <div class="value-grid-item" data-id="{{ $item->_id }}">
                                    <div class="value-header">
                                        <div class="value-input-wrapper">
                                            <input type="text"
                                                   class="form-input value-input"
                                                   name="existing_items[{{ $loop->index }}][value]"
                                                   value="{{ old('existing_items.' . $loop->index . '.value', $item->value) }}"
                                                   placeholder="Enter value"
                                                   data-original-value="{{ $item->value }}"
                                                   onfocus="showSaveButton(this)"
                                                   onblur="hideSaveButton(this)"
                                                   {{ $attribute->status == 'inactive' ? 'readonly' : '' }}>
                                            <button type="button" class="btn-save-value" style="display: none;" onclick="saveValue(this)">
                                                <span class="btn-icon">✓</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="value-footer">
                                        <input type="hidden" name="existing_items[{{ $loop->index }}][id]" value="{{ $item->_id }}">
                                        <input type="hidden" name="existing_items[{{ $loop->index }}][status]" value="{{ $attribute->status == 'inactive' ? 'inactive' : $item->status }}">
                                        <div class="status-toggle">
                                            <label class="toggle-switch">
                                                <input type="checkbox"
                                                       name="existing_items[{{ $loop->index }}][status_checkbox]"
                                                       value="active"
                                                       {{ $item->status == 'active' ? 'checked' : '' }}
                                                       onchange="updateToggleLabel(this)"
                                                       {{ $attribute->status == 'inactive' ? 'disabled' : '' }}
                                                       data-original-status="{{ $item->status }}">
                                                <span class="toggle-slider {{ $attribute->status == 'inactive' ? 'disabled-slider' : '' }}"></span>
                                            </label>
                                            <span class="toggle-label {{ $item->status == 'active' ? 'label-active' : 'label-inactive' }}">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <div class="empty-icon">📋</div>
                                <p>No existing values found</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Add New Values Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">Add New Values</h3>
                    <div class="card-subtitle">Add more values to this attribute</div>
                </div>
                <div class="card-body">
                    <!-- Value Input -->
                    <div class="value-input-section">
                        <div class="input-with-button">
                            <input type="text"
                                   class="form-input"
                                   id="newValueInput"
                                   placeholder="Type value and press Enter (e.g., Purple, Orange)"
                                   {{ $attribute->status == 'inactive' ? 'readonly' : '' }}>
                            <button type="button" class="btn-add-value" id="addNewValueBtn" {{ $attribute->status == 'inactive' ? 'disabled' : '' }}>
                                <span class="btn-icon">+</span> Add Value
                            </button>
                        </div>
                        <div class="form-hint">
                            @if($attribute->status == 'inactive')
                                <span class="warning-text">⚠️ Cannot add new values while attribute is inactive</span>
                            @else
                                Press Enter or click Add Value to add multiple values
                            @endif
                        </div>
                    </div>

                    <!-- New Values Display -->
                    <div class="selected-values-section">
                        <div class="section-header">
                            <div class="section-title">
                                <span class="title-text">New Values to Add</span>
                                <span class="badge-count" id="newValuesCount">0</span>
                            </div>
                            <button type="button" class="btn-secondary btn-clear-all" id="clearNewValuesBtn" {{ $attribute->status == 'inactive' ? 'disabled' : '' }}>
                                Clear All
                            </button>
                        </div>

                        <div class="values-container">
                            <div class="values-tags" id="newValuesTags">
                                <div class="empty-state">
                                    <div class="empty-icon">🎨</div>
                                    <p>No new values added yet</p>
                                </div>
                            </div>

                            <!-- Hidden Inputs for New Values -->
                            <div id="hiddenNewValuesInputs"></div>
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
                    Update Attribute with <span id="totalValuesCount">{{ count($allItems) }}</span> Values
                </button>
            </div>
        </form>
    </div>
</div>


@push('styles')
<style>
    /* Main Container */
     .warning-text {
        color: #dc2626;
        font-weight: 500;
    }

    .status-warning {
        display: flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border: 1px solid #fca5a5;
        border-radius: 6px;
        padding: 10px 12px;
        margin-top: 12px;
    }

    .warning-icon {
        color: #dc2626;
        font-size: 14px;
        flex-shrink: 0;
    }

    .status-warning .warning-text {
        font-size: 11px;
        color: #7f1d1d;
    }

    /* Disabled Slider */
    .disabled-slider {
        background-color: #d1d5db !important;
        cursor: not-allowed;
    }

    .disabled-slider:before {
        background-color: #f3f4f6;
    }

    /* Disabled Button Styles */
    .btn-add-value:disabled,
    .btn-clear-all:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    .btn-add-value:disabled:hover,
    .btn-clear-all:disabled:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: none !important;
        box-shadow: none !important;
    }

    /* Info Card Warning */
    .info-list .warning-text {
        color: #dc2626;
    }
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

    /* Error Message */
    .form-error {
        color: #ef4444;
        font-size: 11px;
        margin-top: 6px;
        font-weight: 500;
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

    /* Status Summary */
    .status-summary {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .summary-stats {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .stat-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        min-width: 80px;
    }

    .stat-badge {
        font-size: 18px;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 20px;
        min-width: 45px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .badge-total {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
    }

    .badge-active {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        color: white;
    }

    .badge-inactive {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        color: white;
    }

    .stat-label {
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Existing Values Grid */
    .existing-values-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-top: 15px;
    }

    .value-grid-item {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 16px;
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 120px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        position: relative;
    }

    .value-grid-item:hover {
        border-color: #d1d5db;
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }

    .value-header {
        margin-bottom: 12px;
        flex: 1;
    }

    .value-input-wrapper {
        position: relative;
    }

    .value-input {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 8px 36px 8px 12px;
        font-size: 12px;
        width: 100%;
        font-weight: 500;
        color: #374151;
        transition: all 0.2s;
    }

    .value-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .value-footer {
        border-top: 1px solid #f3f4f6;
        padding-top: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Save Button */
    .btn-save-value {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: #10b981;
        color: white;
        border: none;
        border-radius: 4px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        padding: 0;
        z-index: 2;
    }

    .btn-save-value:hover {
        background: #059669;
        transform: translateY(-50%) scale(1.1);
    }

    .btn-save-value .btn-icon {
        font-size: 14px;
        line-height: 1;
    }

    /* Status Toggle Switch */
    .status-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 36px;
        height: 18px;
        flex-shrink: 0;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #d1d5db;
        border-radius: 20px;
        transition: .3s;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 14px;
        width: 14px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        border-radius: 50%;
        transition: .3s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    input:checked + .toggle-slider {
        background-color: #10b981;
    }

    input:checked + .toggle-slider:before {
        transform: translateX(18px);
    }

    .toggle-label {
        font-size: 10px;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
        min-width: 50px;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        flex: 1;
    }

    .label-active {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }

    .label-inactive {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
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

    /* Responsive - Grid adjustments */
    @media (max-width: 1024px) {
        .existing-values-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

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

        .existing-values-grid {
            grid-template-columns: 1fr;
        }

        .summary-stats {
            gap: 15px;
        }

        .stat-item {
            min-width: 70px;
        }

        .value-grid-item {
            min-height: 110px;
            padding: 14px;
        }

        .value-input {
            padding: 6px 32px 6px 10px;
            font-size: 11px;
        }

        .btn-save-value {
            width: 22px;
            height: 22px;
            right: 6px;
        }

        .toggle-switch {
            width: 32px;
            height: 16px;
        }

        .toggle-slider:before {
            height: 12px;
            width: 12px;
        }

        input:checked + .toggle-slider:before {
            transform: translateX(16px);
        }

        .toggle-label {
            font-size: 9px;
            min-width: 45px;
            padding: 3px 6px;
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

        .summary-stats {
            gap: 10px;
        }

        .stat-badge {
            font-size: 16px;
            padding: 4px 10px;
            min-width: 40px;
        }

        .value-grid-item {
            padding: 12px;
            min-height: 100px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let newAttributeValues = [];
    let existingValuesCount = {{ $attribute->items->count() }};
    let isAttributeInactive = {{ $attribute->status == 'inactive' ? 'true' : 'false' }};

    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    function handleAttributeStatusChange(select) {
        const isInactive = select.value === 'inactive';
        isAttributeInactive = isInactive;

        // Update status hint
        const statusHint = document.getElementById('statusHint');
        if (isInactive) {
            statusHint.innerHTML = '<span class="warning-text">⚠️ When attribute is inactive, all values remain inactive</span>';

            // Disable all toggle switches and set to inactive
            const toggleSwitches = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
            const hiddenStatusInputs = document.querySelectorAll('input[name^="existing_items"][name$="[status]"]');

            toggleSwitches.forEach(checkbox => {
                checkbox.disabled = true;
                checkbox.checked = false;

                // Update UI
                const gridItem = checkbox.closest('.value-grid-item');
                const label = gridItem.querySelector('.toggle-label');
                label.textContent = 'Inactive';
                label.className = 'toggle-label label-inactive';

                const slider = gridItem.querySelector('.toggle-slider');
                slider.classList.add('disabled-slider');
            });

            // Update hidden status inputs to inactive
            hiddenStatusInputs.forEach(input => {
                input.value = 'inactive';
            });

            // Disable value inputs
            const valueInputs = document.querySelectorAll('.value-input');
            valueInputs.forEach(input => {
                input.readOnly = true;
            });

            // Disable new value input and buttons
            document.getElementById('newValueInput').readOnly = true;
            document.getElementById('addNewValueBtn').disabled = true;
            document.getElementById('clearNewValuesBtn').disabled = true;

            // Clear any new values that were added
            if (newAttributeValues.length > 0) {
                if (confirm('Attribute status changed to inactive. All new values will be removed.')) {
                    newAttributeValues = [];
                    updateNewValuesDisplay();
                } else {
                    // If user cancels, revert selection
                    select.value = 'active';
                    handleAttributeStatusChange(select);
                    return;
                }
            }

            // Show warning
            showAlert('Attribute status changed to inactive. All values will be set to inactive.', 'error');

        } else {
            statusHint.innerHTML = 'New values will be created with <span id="statusText">active</span> status.';

            // Enable all toggle switches and restore original status
            const toggleSwitches = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
            const hiddenStatusInputs = document.querySelectorAll('input[name^="existing_items"][name$="[status]"]');

            toggleSwitches.forEach(checkbox => {
                checkbox.disabled = false;

                // Restore original status
                const originalStatus = checkbox.getAttribute('data-original-status');
                const isActive = originalStatus === 'active';
                checkbox.checked = isActive;

                // Update UI
                const gridItem = checkbox.closest('.value-grid-item');
                const label = gridItem.querySelector('.toggle-label');
                label.textContent = isActive ? 'Active' : 'Inactive';
                label.className = `toggle-label ${isActive ? 'label-active' : 'label-inactive'}`;

                const slider = gridItem.querySelector('.toggle-slider');
                slider.classList.remove('disabled-slider');

                // Update corresponding hidden status input
                const itemId = gridItem.dataset.id;
                const hiddenInput = gridItem.querySelector('input[name^="existing_items"][name$="[status]"]');
                if (hiddenInput) {
                    hiddenInput.value = isActive ? 'active' : 'inactive';
                }
            });

            // Enable value inputs
            const valueInputs = document.querySelectorAll('.value-input');
            valueInputs.forEach(input => {
                input.readOnly = false;
            });

            // Enable new value input and buttons
            document.getElementById('newValueInput').readOnly = false;
            document.getElementById('addNewValueBtn').disabled = false;
            document.getElementById('clearNewValuesBtn').disabled = false;
        }

        // Update status text
        const statusText = document.getElementById('statusText');
        if (statusText) {
            statusText.textContent = select.value;
        }

        updateTotalCounts();
    }

    function showSaveButton(input) {
        if (isAttributeInactive) return;
        const saveBtn = input.parentNode.querySelector('.btn-save-value');
        if (saveBtn) {
            saveBtn.style.display = 'flex';
        }
    }

    function hideSaveButton(input) {
        const saveBtn = input.parentNode.querySelector('.btn-save-value');
        if (saveBtn) {
            // Hide after a short delay to allow click on save button
            setTimeout(() => {
                const originalValue = input.getAttribute('data-original-value');
                const currentValue = input.value.trim();

                // If input is empty or same as original, hide the save button
                if (!currentValue || currentValue === originalValue) {
                    saveBtn.style.display = 'none';
                }
            }, 300);
        }
    }

    function saveValue(button) {
        if (isAttributeInactive) return;
        const input = button.parentNode.querySelector('.value-input');
        const originalValue = input.getAttribute('data-original-value');
        const newValue = input.value.trim();

        if (!newValue) {
            showAlert('Value cannot be empty', 'error');
            input.value = originalValue;
            button.style.display = 'none';
            return;
        }

        if (newValue === originalValue) {
            button.style.display = 'none';
            return;
        }

        // Check for duplicates among existing values
        const existingInputs = document.querySelectorAll('.value-input');
        let hasDuplicate = false;

        existingInputs.forEach(existingInput => {
            if (existingInput !== input && existingInput.value.trim() === newValue) {
                hasDuplicate = true;
            }
        });

        if (hasDuplicate) {
            showAlert(`"${newValue}" already exists in this attribute`, 'error');
            input.value = originalValue;
            button.style.display = 'none';
            return;
        }

        // Check in new values
        if (newAttributeValues.includes(newValue)) {
            showAlert(`"${newValue}" is already added as new value`, 'error');
            input.value = originalValue;
            button.style.display = 'none';
            return;
        }

        // Update the original value attribute
        input.setAttribute('data-original-value', newValue);
        button.style.display = 'none';
        showAlert(`Value updated to "${newValue}"`, 'success');
    }

    function updateToggleLabel(checkbox) {
        if (isAttributeInactive) {
            checkbox.checked = false;
            return;
        }

        const gridItem = checkbox.closest('.value-grid-item');
        const label = gridItem.querySelector('.toggle-label');
        const isActive = checkbox.checked;

        label.textContent = isActive ? 'Active' : 'Inactive';
        label.className = `toggle-label ${isActive ? 'label-active' : 'label-inactive'}`;

        // Update hidden status input
        const hiddenInput = gridItem.querySelector('input[name^="existing_items"][name$="[status]"]');
        if (hiddenInput) {
            hiddenInput.value = isActive ? 'active' : 'inactive';
        }

        // Update total counts
        updateTotalCounts();
    }

    function updateTotalCounts() {
        const activeCheckboxes = document.querySelectorAll('.value-grid-item input[type="checkbox"]:checked').length;
        const inactiveCheckboxes = existingValuesCount - activeCheckboxes;

        // Update summary badges
        const activeBadge = document.querySelector('.badge-active');
        const inactiveBadge = document.querySelector('.badge-inactive');

        if (activeBadge) activeBadge.textContent = activeCheckboxes;
        if (inactiveBadge) inactiveBadge.textContent = inactiveCheckboxes;

        // Update total values count in submit button
        const totalValuesCount = existingValuesCount + newAttributeValues.length;
        document.getElementById('totalValuesCount').textContent = totalValuesCount;
    }

    function addNewValue(value) {
        if (isAttributeInactive) {
            showAlert('Cannot add new values while attribute is inactive', 'error');
            return;
        }

        value = value.trim();
        if (!value) return;

        // Check if value already exists in existing values
        const existingValues = Array.from(document.querySelectorAll('.value-input')).map(el => el.value.trim());
        if (existingValues.includes(value)) {
            showAlert(`"${value}" already exists in this attribute`, 'error');
            return;
        }

        // Check if value already in new values
        if (newAttributeValues.includes(value)) {
            showAlert(`"${value}" is already added as new value`, 'error');
            return;
        }

        // Add to array
        newAttributeValues.push(value);

        // Update UI
        updateNewValuesDisplay();

        // Clear input
        document.getElementById('newValueInput').value = '';

        // Focus back on input
        document.getElementById('newValueInput').focus();
    }

    function removeNewValue(value) {
        if (isAttributeInactive) return;
        const index = newAttributeValues.indexOf(value);
        if (index > -1) {
            newAttributeValues.splice(index, 1);
            updateNewValuesDisplay();
        }
    }

    function updateNewValuesDisplay() {
        const container = document.getElementById('newValuesTags');
        const countElement = document.getElementById('newValuesCount');
        const hiddenInputsContainer = document.getElementById('hiddenNewValuesInputs');

        // Clear existing hidden inputs for new values
        const newValueInputs = hiddenInputsContainer.querySelectorAll('input[name^="new_values"]');
        newValueInputs.forEach(input => input.remove());

        // Update count
        countElement.textContent = newAttributeValues.length;

        if (newAttributeValues.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">🎨</div>
                    <p>No new values added yet</p>
                </div>
            `;
            // Update total values count
            document.getElementById('totalValuesCount').textContent = existingValuesCount;
            return;
        }

        // Clear new values tags container
        container.innerHTML = '';

        // Create tags and hidden inputs for new values
        newAttributeValues.forEach((value, index) => {
            // Create tag
            const tag = document.createElement('div');
            tag.className = 'value-tag';
            tag.innerHTML = `
                <span class="tag-text">${value}</span>
                <span class="tag-remove" onclick="removeNewValue('${value.replace(/'/g, "\\'")}')">×</span>
            `;
            container.appendChild(tag);

            // Create hidden input for new value
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `new_values[${index}]`;
            hiddenInput.value = value;
            hiddenInputsContainer.appendChild(hiddenInput);
        });

        // Update total values count
        document.getElementById('totalValuesCount').textContent = existingValuesCount + newAttributeValues.length;
    }

    function clearAllNewValues() {
        if (isAttributeInactive) return;
        if (newAttributeValues.length === 0) return;

        if (confirm(`Are you sure you want to remove all ${newAttributeValues.length} new values?`)) {
            newAttributeValues = [];
            updateNewValuesDisplay();
        }
    }

    function validateForm() {
        const attributeType = document.getElementById('typeInput').value.trim();
        const existingInputs = document.querySelectorAll('.value-input');
        const existingValues = Array.from(existingInputs).map(input => input.value.trim());
        const allValues = [...existingValues, ...newAttributeValues];

        // Check attribute type
        if (!attributeType) {
            showAlert('Attribute type is required', 'error');
            return false;
        }

        // Check for duplicate values
        const seen = {};
        const duplicates = [];

        allValues.forEach(value => {
            if (value && seen[value]) {
                duplicates.push(value);
            }
            seen[value] = true;
        });

        if (duplicates.length > 0) {
            showAlert(`Duplicate values found: ${duplicates.join(', ')}. Please make all values unique.`, 'error');
            return false;
        }

        // Check for empty values in existing items
        const emptyValues = existingValues.filter(value => !value);
        if (emptyValues.length > 0) {
            showAlert('All attribute values must have a name', 'error');
            return false;
        }

        return true;
    }

    // Handle Enter key in new value input
    document.getElementById('newValueInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addNewValue(this.value);
        }
    });

    // Handle Add New Value button click
    document.getElementById('addNewValueBtn').addEventListener('click', function() {
        const input = document.getElementById('newValueInput');
        addNewValue(input.value);
    });

    // Handle Clear All button for new values
    document.getElementById('clearNewValuesBtn').addEventListener('click', clearAllNewValues);

    // Update status hint when status select changes
    document.getElementById('statusSelect').addEventListener('change', function() {
        const statusText = document.getElementById('statusText');
        statusText.textContent = this.value;
    });

    // Handle form submission
    document.getElementById('attributeForm').addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial status hint
        const statusSelect = document.getElementById('statusSelect');
        const statusText = document.getElementById('statusText');
        statusText.textContent = statusSelect.value;

        // If attribute is already inactive, disable everything
        if (isAttributeInactive) {
            handleAttributeStatusChange(statusSelect);
        }

        // Update total counts
        updateTotalCounts();

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
    });
</script>
@endpush
@endsection
