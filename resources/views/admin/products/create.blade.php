@extends('layouts.admin')

@section('title', 'Create Variant Product')
@section('header-title', 'Create Variant Product')

@section('content')
<div class="create-product-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Add Variant Product</h2>
        </div>
        <div class="header-right">
            <a href="{{ url('/admin/products') }}" class="back-btn">← Back to Products</a>
        </div>
    </div>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf

        <!-- Tab Navigation -->
        <div class="tab-container">
            <div class="tab-nav">
                <button type="button" class="tab-btn active" data-tab="basic-info">
                    📝 Basic Info
                </button>
                <button type="button" class="tab-btn" data-tab="media">
                    🖼️ Images
                </button>
                <button type="button" class="tab-btn" data-tab="variants">
                    🎨 Variants
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content-wrapper">
                <!-- Tab 1: Basic Information -->
                <div class="tab-content active" id="basic-info">
                    <div class="tab-header">
                        <h3 class="tab-title">Basic Information</h3>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Product Name <span class="required">*</span></label>
                            <input type="text" class="form-input" id="productName" name="name" value="{{ old('name') }}" placeholder="e.g., LED Bulb" required>
                            @error('name')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Warehouse Field को Simple create की तरह बनाएं -->
                        <div class="form-group">
                            <label class="form-label">Warehouse</label>
                            @php
                                $mainWarehouse = \App\Models\Warehouse::main()->first();
                            @endphp
                            @if($mainWarehouse)
                                <input type="text" class="form-input"
                                    value="{{ $mainWarehouse->name }} (Main Warehouse)"
                                    readonly
                                    style="background-color: #f0f8ff; color: #0066cc; font-weight: 500;">
                                <div class="warehouse-note">
                                    📍 Products are always added to Main Warehouse
                                </div>
                                <input type="hidden" name="warehouse_id" value="{{ $mainWarehouse->id }}">
                            @else
                                <div class="alert alert-warning" style="padding: 8px; background: #fff3cd; color: #856404; border-radius: 4px; font-size: 11px;">
                                    ⚠️ Main warehouse not found. Please create a main warehouse first.
                                </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>



                        <div class="form-group">
                            <label class="form-label">Brand</label>
                            <input type="text" class="form-input" value="Simko" readonly style="background-color: #f5f5f5;">
                            <input type="hidden" name="brand" value="Simko">
                        </div>


                        <div class="form-group">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Warranty Duration</label>
                            <input type="number" class="form-input" name="warranty_duration" value="{{ old('warranty_duration') }}" placeholder="e.g., 12" min="1" >
                            @error('warranty_duration')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Warranty Unit</label>
                            <select class="form-select" name="warranty_unit">
                                <option value="">Select Unit</option>
                                <option value="month" {{ old('warranty_unit') == 'month' ? 'selected' : '' }}>Month(s)</option>
                                <option value="year" {{ old('warranty_unit') == 'year' ? 'selected' : '' }}>Year(s)</option>
                            </select>
                            @error('warranty_unit')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>



                        <!-- GST Field in Create Page -->
                        <div class="form-group">
                            <label class="form-label">GST (%) <span class="required">*</span></label>
                            <input type="number" step="0.01" class="form-input" name="gst"
                                value="{{ old('gst') }}"
                                placeholder="18" min="0" max="100" required
                                oninput="validateGST(this, 'gst-error-create')">
                            <div class="error-message" id="gst-error-create"></div>
                            @error('gst')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>



                        <div class="form-group">
                            <label class="form-label">HSN Code <span class="required">*</span></label>
                            <input type="text" class="form-input" name="hsn_code" value="{{ old('hsn_code') }}" placeholder="85395000" maxlength="8" required>
                            @error('hsn_code')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Description </label>
                            <textarea class="form-textarea" name="description" rows="4" placeholder="Enter product description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Images -->
                <div class="tab-content" id="media">
                    <div class="tab-header">
                        <h3 class="tab-title">Product Images</h3>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Main Image <span class="required">*</span></label>
                            <div class="image-upload-wrapper">
                                <input type="file" class="form-input-file" id="base_image" name="base_image" accept="image/*" onchange="previewBaseImage(event)" required hidden>
                                <label for="base_image" class="upload-label">
                                    <div class="upload-icon">📸</div>
                                    <div class="upload-text">Upload Main Image</div>
                                    <div class="upload-hint">JPG, PNG up to 2MB</div>
                                </label>
                            </div>
                            <div class="image-preview-container" id="baseImagePreview"></div>
                            @error('base_image')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Gallery Images</label>
                            <div class="image-upload-wrapper">
                                <input type="file" class="form-input-file" id="gallery_images" name="gallery_images[]" accept="image/*" multiple onchange="previewGalleryImages(event)" hidden>
                                <label for="gallery_images" class="upload-label">
                                    <div class="upload-icon">🖼️</div>
                                    <div class="upload-text">Upload Gallery Images</div>
                                    <div class="upload-hint">Select multiple files</div>
                                </label>
                            </div>
                            <div class="gallery-preview" id="galleryPreview"></div>
                            @error('gallery_images')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Variants -->
                <div class="tab-content" id="variants">
                    <div class="tab-header">
                        <h3 class="tab-title">Product Variants</h3>
                    </div>

                    <div class="variants-section">
                        <!-- Attribute Selector -->
                        <div class="attribute-selector-section">
                            <div class="selector-title">
                                Select Attributes
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Attribute Type <span class="required">*</span></label>
                                    <select class="form-select" id="attribute_type" onchange="loadAttributeValues()">
                                        <option value="">Select Type</option>
                                        @foreach($attributeTypes as $attribute)
                                            <option value="{{ $attribute->type }}">{{ ucfirst($attribute->type) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Attribute Values <span class="required">*</span></label>
                                    <div id="valuesContainer">
                                        <div class="no-values">Select attribute type first</div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn-add-attribute" onclick="addSelectedAttribute()">
                                <span>+</span> Add Attribute
                            </button>
                        </div>

                        <!-- Selected Attributes -->
                        <div class="selected-attributes-section" id="selectedAttributesSection" style="display: none;">
                            <div class="section-header">
                                <h4>Selected Attributes:</h4>
                                <button type="button" class="btn-clear-all" onclick="clearAllAttributes()">Clear All</button>
                            </div>
                            <div class="selected-attributes-list" id="selectedAttributesList"></div>
                        </div>

                        <!-- Generate Button -->
                        <button type="button" class="btn-generate-variants" id="generateVariantsBtn" onclick="generateVariants()" disabled>
                            ⚡ Generate Variants
                        </button>

                        <!-- Variants Info -->
                        <div class="generated-variants-info" id="variantsInfo">
                            <p class="info-text" id="variantsInfoText"></p>
                        </div>

                        <!-- Variants Container -->
                        <div id="variantsContainer">
                            <div class="no-variants-message" id="noVariantsMessage">
                                <div class="no-variants-icon">🎨</div>
                                <h3>No Variants Yet</h3>
                                <p>Select attributes and click "Generate Variants"</p>
                            </div>
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
                        Create Product
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .create-product-container {
        padding: 0 15px 80px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 13px;
    }
.warehouse-note {
    font-size: 9px;
    color: #d97706;
    margin-top: 3px;
    font-style: italic;
}

.opening-stock-note {
    font-size: 9px;
    color: #6c757d;
    font-style: italic;
    margin-top: 2px;
}

.current-stock-note {
    font-size: 9px;
    color: #6c757d;
    font-style: italic;
    margin-top: 2px;
}

.form-input:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
    opacity: 0.7;
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
        font-size: 14px;
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

    /* Attribute Selector Section */
    .attribute-selector-section {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 12px;
        border: 1px solid #dee2e6;
    }

    .selector-title {
        font-size: 12px;
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }

    #valuesContainer {
        min-height: 80px;
        max-height: 120px;
        overflow-y: auto;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 6px;
        background: white;
    }

    .no-values {
        color: #6c757d;
        font-size: 11px;
        text-align: center;
        padding: 20px 0;
    }

    .value-item {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 5px 6px;
        border-radius: 3px;
        margin-bottom: 3px;
        cursor: pointer;
        transition: background 0.2s;
        font-size: 11px;
    }

    .value-item:hover {
        background: #f1f3f4;
    }

    .value-item.selected {
        background: #e3f2fd;
        border-left: 2px solid #2196f3;
    }

    .value-checkbox {
        width: 14px;
        height: 14px;
        border: 1px solid #adb5bd;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }

    .value-checkbox.checked {
        background: #007bff;
        border-color: #007bff;
        color: white;
    }

    .value-label {
        flex: 1;
        color: #495057;
    }

    /* Add Attribute Button */
    .btn-add-attribute {
        padding: 6px 12px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        margin-top: 8px;
    }

    .btn-add-attribute:hover {
        background: #0069d9;
    }

    /* Selected Attributes */
    .selected-attributes-section {
        background: white;
        padding: 10px;
        border-radius: 6px;
        margin-top: 8px;
        border: 1px solid #dee2e6;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .selected-attributes-section h4 {
        font-size: 12px;
        font-weight: 600;
        color: #495057;
        margin: 0;
    }

    .btn-clear-all {
        padding: 3px 8px;
        background: #f8d7da;
        color: #721c24;
        border: none;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-clear-all:hover {
        background: #f5c6cb;
    }

    .selected-attributes-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .selected-attribute-item {
        background: #e3f2fd;
        padding: 6px 8px;
        border-radius: 4px;
        border: 1px solid #bbdefb;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
    }

    .selected-attribute-type {
        font-weight: 600;
        color: #1565c0;
        text-transform: capitalize;
    }

    .selected-attribute-values {
        display: flex;
        gap: 3px;
    }

    .attribute-value-badge {
        background: white;
        padding: 1px 5px;
        border-radius: 8px;
        font-size: 9px;
        border: 1px solid #bbdefb;
        color: #0d47a1;
    }

    /* Generate Button */
    .btn-generate-variants {
        padding: 8px 16px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        margin-top: 8px;
        width: 100%;
        font-size: 11px;
    }

    .btn-generate-variants:hover:not(:disabled) {
        background: #218838;
    }

    .btn-generate-variants:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Variants Info */
    .generated-variants-info {
        background: #d4edda;
        padding: 8px 10px;
        border-radius: 4px;
        margin: 8px 0;
        display: none;
        border-left: 3px solid #28a745;
    }

    .generated-variants-info.show {
        display: block;
    }

    .info-text {
        font-size: 11px;
        color: #155724;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* No Variants Message */
    .no-variants-message {
        text-align: center;
        padding: 20px 12px;
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px dashed #adb5bd;
        margin-top: 8px;
    }

    .no-variants-icon {
        font-size: 28px;
        margin-bottom: 8px;
        color: #adb5bd;
    }

    .no-variants-message h3 {
        font-size: 13px;
        font-weight: 600;
        color: #495057;
        margin-bottom: 4px;
    }

    .no-variants-message p {
        color: #6c757d;
        font-size: 11px;
        margin: 0;
    }

    /* Variants Container */
    #variantsContainer {
        margin-top: 12px;
    }

    /* Variant Card */
    .variant-card {
        background: white;
        padding: 12px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        margin-bottom: 8px;
        position: relative;
    }

    .variant-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #dee2e6;
    }

    .variant-title {
        font-size: 12px;
        font-weight: 600;
        color: #495057;
    }

    .btn-remove-variant {
        padding: 3px 6px;
        background: #f8d7da;
        color: #721c24;
        border: none;
        border-radius: 3px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 10px;
    }

    .btn-remove-variant:hover {
        background: #f5c6cb;
    }

    /* Variant Attribute Info */
    .variant-attribute-info {
        background: #e3f2fd;
        padding: 6px 8px;
        border-radius: 4px;
        margin-bottom: 8px;
        font-size: 10px;
        border-left: 3px solid #2196f3;
    }

    .variant-attribute-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 4px;
    }

    .variant-attribute-tag {
        background: white;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 9px;
        border: 1px solid #bbdefb;
    }

    .attr-type {
        font-weight: 600;
        color: #1565c0;
        margin-right: 2px;
    }

    /* Variant Section Title */
    .variant-section-title {
        font-size: 11px;
        font-weight: 600;
        color: #495057;
        margin: 8px 0 6px;
        padding-bottom: 4px;
        border-bottom: 1px solid #dee2e6;
    }

    /* Variant Form Grid */
    .variant-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    .variant-form-grid.three-columns {
        grid-template-columns: repeat(3, 1fr);
    }


    /* Price Validation */
    .price-error {
        border-color: #dc3545 !important;
        background: #f8d7da !important;
    }

    .price-success {
        border-color: #28a745 !important;
        background: #d4edda !important;
    }

    .price-message {
        font-size: 9px;
        margin-top: 3px;
        padding: 3px 5px;
        border-radius: 3px;
        display: none;
    }

    .price-message.error {
        display: block;
        background: #f8d7da;
        color: #721c24;
        border-left: 2px solid #dc3545;
    }

    .price-message.success {
        display: block;
        background: #d4edda;
        color: #155724;
        border-left: 2px solid #28a745;
    }

    /* SKU Validation */
    .sku-validation-message {
        font-size: 9px;
        margin-top: 3px;
        padding: 3px 5px;
        border-radius: 3px;
        display: none;
    }

    .sku-validation-message.error {
        display: block;
        background: #f8d7da;
        color: #721c24;
        border-left: 2px solid #dc3545;
    }

    .sku-validation-message.success {
        display: block;
        background: #d4edda;
        color: #155724;
        border-left: 2px solid #28a745;
    }

    /* Image Upload */
    .image-upload-wrapper {
        margin-bottom: 8px;
    }

    .upload-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 15px;
        border: 1px dashed #adb5bd;
        border-radius: 4px;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.2s;
    }

    .upload-label:hover {
        border-color: #007bff;
        background: #e7f1ff;
    }

    .upload-icon {
        font-size: 18px;
        margin-bottom: 5px;
    }

    .upload-text {
        font-size: 11px;
        font-weight: 500;
        color: #495057;
        margin-bottom: 2px;
    }

    .upload-hint {
        font-size: 9px;
        color: #6c757d;
    }

    .image-preview-container, .gallery-preview {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
        gap: 6px;
        margin-top: 8px;
    }

    .preview-item {
        position: relative;
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid #dee2e6;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .preview-image {
        width: 100%;
        height: 60px;
        object-fit: cover;
        display: block;
    }

    .remove-image {
        position: absolute;
        top: 3px;
        right: 3px;
        width: 16px;
        height: 16px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 10px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        line-height: 1;
        padding: 0;
    }

    .remove-image:hover {
        background: #c82333;
        transform: scale(1.1);
    }

    /* Form Actions */
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
        font-size: 14px;
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
        font-size: 14px;
    }

    .btn-primary:hover {
        background: #218838;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid, .variant-form-grid {
            grid-template-columns: 1fr;
        }
        .variant-form-grid.three-columns {
            grid-template-columns: 1fr;
        }
        .form-group.full-width {
            grid-column: span 1;
        }
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .header-right {
            width: 100%;
        }
        .back-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // ========== GLOBAL VARIABLES ==========
    let currentTab = 0;
    const tabs = ['basic-info', 'media', 'variants'];
    let variantCount = 0;
    let selectedAttributes = [];
    let generatedVariants = [];

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

    // ========== ATTRIBUTE MANAGEMENT ==========
    function loadAttributeValues() {
        const attributeType = document.getElementById('attribute_type').value;
        const container = document.getElementById('valuesContainer');

        if (!attributeType) {
            container.innerHTML = '<div class="no-values">Select attribute type first</div>';
            return;
        }

        container.innerHTML = '<div class="no-values">Loading values...</div>';

        fetch('{{ route("admin.products.get-attribute-values") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                attribute_type: attributeType
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.values && data.values.length > 0) {
                container.innerHTML = '';
                data.values.forEach(value => {
                    const div = document.createElement('div');
                    div.className = 'value-item';
                    div.innerHTML = `
                        <div class="value-checkbox"></div>
                        <span class="value-label">${value}</span>
                    `;
                    div.onclick = function() {
                        this.classList.toggle('selected');
                        const checkbox = this.querySelector('.value-checkbox');
                        checkbox.classList.toggle('checked');
                        checkbox.innerHTML = checkbox.classList.contains('checked') ? '✓' : '';
                    };
                    container.appendChild(div);
                });
            } else {
                container.innerHTML = '<div class="no-values">No values found</div>';
                showAlert('No values found for this attribute type', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading attribute values:', error);
            container.innerHTML = '<div class="no-values">Error loading values</div>';
            showAlert('Error loading attribute values', 'error');
        });
    }

    function addSelectedAttribute() {
        const typeSelect = document.getElementById('attribute_type');
        const container = document.getElementById('valuesContainer');
        const selectedValues = [];

        // Get selected values
        container.querySelectorAll('.value-item.selected').forEach(item => {
            const label = item.querySelector('.value-label').textContent;
            selectedValues.push(label);
        });

        const attributeType = typeSelect.value;

        if (!attributeType || selectedValues.length === 0) {
            showAlert('Select attribute type and at least one value', 'error');
            return;
        }

        // Check if this attribute type already exists
        const existingIndex = selectedAttributes.findIndex(attr => attr.type === attributeType);

        if (existingIndex !== -1) {
            // Update existing attribute
            selectedAttributes[existingIndex].values = selectedValues;
        } else {
            // Add new attribute
            const displayName = attributeType.split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');

            selectedAttributes.push({
                type: attributeType,
                displayName: displayName,
                values: selectedValues
            });
        }

        updateSelectedAttributesDisplay();

        // Reset
        typeSelect.value = '';
        container.innerHTML = '<div class="no-values">Select attribute type first</div>';

        showAlert('Attribute added', 'success');
    }

    function updateSelectedAttributesDisplay() {
        const section = document.getElementById('selectedAttributesSection');
        const list = document.getElementById('selectedAttributesList');
        const generateBtn = document.getElementById('generateVariantsBtn');

        if (selectedAttributes.length === 0) {
            section.style.display = 'none';
            generateBtn.disabled = true;
            return;
        }

        section.style.display = 'block';
        list.innerHTML = '';

        selectedAttributes.forEach((attr, index) => {
            const attributeDiv = document.createElement('div');
            attributeDiv.className = 'selected-attribute-item';

            let valuesHtml = '';
            attr.values.forEach(value => {
                valuesHtml += `<span class="attribute-value-badge">${value}</span>`;
            });

            attributeDiv.innerHTML = `
                <span class="selected-attribute-type">${attr.displayName}:</span>
                <div class="selected-attribute-values">${valuesHtml}</div>
            `;

            list.appendChild(attributeDiv);
        });

        generateBtn.disabled = false;
    }

    function clearAllAttributes() {
        if (confirm('Clear all selected attributes?')) {
            selectedAttributes = [];
            updateSelectedAttributesDisplay();
            showAlert('All attributes cleared', 'success');
        }
    }

    // ========== VARIANT GENERATION ==========
    function generateVariants() {
        if (selectedAttributes.length === 0) {
            showAlert('Add at least one attribute', 'error');
            return;
        }

        const combinations = generateAttributeCombinations();

        if (combinations.length === 0) {
            showAlert('No combinations found', 'error');
            return;
        }

        const container = document.getElementById('variantsContainer');
        container.innerHTML = '';

        variantCount = 0;
        generatedVariants = [];

        combinations.forEach((combination, index) => {
            createVariantCard(combination, index);
        });

        const infoDiv = document.getElementById('variantsInfo');
        const infoText = document.getElementById('variantsInfoText');
        if (infoDiv && infoText) {
            infoText.textContent = `Generated ${combinations.length} variant(s)`;
            infoDiv.classList.add('show');
        }

        showAlert(`Generated ${combinations.length} variant(s)`, 'success');
    }

    function generateAttributeCombinations() {
        if (selectedAttributes.length === 0) return [];

        let combinations = [[]];

        selectedAttributes.forEach(attribute => {
            const values = attribute.values;
            const newCombinations = [];

            combinations.forEach(combo => {
                values.forEach(value => {
                    newCombinations.push([
                        ...combo,
                        {
                            type: attribute.type,
                            displayName: attribute.displayName,
                            value: value
                        }
                    ]);
                });
            });

            combinations = newCombinations;
        });

        return combinations;
    }

    function generateVariantName(productName, combination) {
        const attributeValues = combination.map(attr => attr.value);
        return `${productName} - ${attributeValues.join(' - ')}`;
    }
    function generateSkuSuffix(combination) {
        const parts = combination.map(attr => {
            return attr.value.replace(/\s+/g, '').toUpperCase().substring(0, 3);
        });
        return parts.join('-');
    }


    function createVariantCard(combination, index) {
        variantCount++;
        const container = document.getElementById('variantsContainer');

        const noVariantsMessage = document.getElementById('noVariantsMessage');
        if (noVariantsMessage) {
            noVariantsMessage.style.display = 'none';
        }

        const productNameElement = document.getElementById('productName');
        const productName = productNameElement ? productNameElement.value : 'Product';
        const variantName = generateVariantName(productName, combination);
        const skuSuffix = generateSkuSuffix(combination);
        const variantId = variantCount;


        const variantHtml = `
            <div class="variant-card" id="variant-${variantId}">
                <div class="variant-header">
                    <h4 class="variant-title">Variant ${variantId}</h4>
                    <button type="button" class="btn-remove-variant" onclick="removeVariant(${variantId})">
                        Remove
                    </button>
                </div>

                <div class="variant-attribute-info">
                    <strong>Attributes:</strong>
                    <div class="variant-attribute-tags">
                        ${combination.map(attr =>
                            `<span class="variant-attribute-tag">
                                <span class="attr-type">${attr.displayName}:</span>
                                <span class="attr-value">${attr.value}</span>
                            </span>`
                        ).join('')}
                    </div>
                </div>

                <input type="hidden"
                       name="variants[${variantId}][attributes]"
                       value='${JSON.stringify(combination)}'>

                <!-- Variant Name and Unit in same row -->
                <div class="variant-form-grid">
                    <div class="form-group">
                        <label class="form-label">Variant Name <span class="required">*</span></label>
                        <input type="text"
                               class="form-input"
                               name="variants[${variantId}][name]"
                               value="${variantName}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unit <span class="required">*</span></label>
                        <select class="form-select" name="variants[${variantId}][unit]" required>
                            <option value="">Select Unit</option>
                            <option value="piece">Piece</option>
                            <option value="set">Set</option>
                            <option value="box">Box</option>
                            <option value="meter">Meter</option>
                            <option value="kg">Kilogram</option>
                            <option value="liter">Liter</option>
                            <option value="pack">Pack</option>
                            <option value="dozen">Dozen</option>
                            <option value="roll">Roll</option>
                            <option value="sheet">Sheet</option>
                        </select>
                    </div>
                </div>

                <!-- SKU and Barcode in same row -->
                <div class="variant-form-grid">
                    <div class="form-group">
                        <label class="form-label">SKU Code <span class="required">*</span></label>
                        <input type="text"
                               class="form-input"
                               id="variant_sku_${variantId}"
                               name="variants[${variantId}][sku_code]"
                               placeholder="e.g., SKU-${skuSuffix}"
                               maxlength="16"
                               required
                               onkeyup="validateVariantSku(${variantId}, this.value)">
                        <div class="sku-validation-message" id="variant_${variantId}_sku_message"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Barcode Symbology <span class="required">*</span></label>
                        <select class="form-select" name="variants[${variantId}][barcode_symbology]" required>
                            <option value="CODE128" selected>CODE128</option>
                        </select>
                    </div>
                </div>

                <div class="variant-section-title">Pricing</div>
                <div class="variant-form-grid three-columns">
                    <div class="form-group">
                        <label class="form-label">Cost Price (₹) <span class="required">*</span></label>
                        <input type="number"
                               step="0.01"
                               class="form-input cost-price"
                               id="variant_cost_price_${variantId}"
                               name="variants[${variantId}][cost_price]"
                               placeholder="0.00"
                               min="0"
                               required
                               onkeyup="validatePrices(${variantId})">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sale Price (₹) <span class="required">*</span></label>
                        <input type="number"
                               step="0.01"
                               class="form-input sale-price"
                               id="variant_sale_price_${variantId}"
                               name="variants[${variantId}][sale_price]"
                               placeholder="0.00"
                               min="0"
                               required
                               onkeyup="validatePrices(${variantId})">
                        <div class="price-message" id="sale_price_msg_${variantId}"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">MRP Price (₹) <span class="required">*</span></label>
                        <input type="number"
                               step="0.01"
                               class="form-input mrp-price"
                               id="variant_mrp_price_${variantId}"
                               name="variants[${variantId}][mrp_price]"
                               placeholder="0.00"
                               min="0"
                               required
                               onkeyup="validatePrices(${variantId})">
                        <div class="price-message" id="mrp_price_msg_${variantId}"></div>
                    </div>
                </div>

                <div class="variant-section-title">Stock Management</div>
                <div class="variant-form-grid">
                    <div class="form-group">
                        <label class="form-label">Opening Stock <span class="required">*</span></label>
                        <input type="number"
                               class="form-input"
                               id="variant_opening_stock_${variantId}"
                               name="variants[${variantId}][opening_stock]"
                               placeholder="0"
                               min="0"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Min Stock Alert <span class="required">*</span></label>
                        <input type="number"
                               class="form-input"
                               id="variant_min_stock_${variantId}"
                               name="variants[${variantId}][min_stock_alert]"
                               placeholder="0"
                               min="0"
                               required>
                    </div>
                </div>



                <div class="variant-section-title">Variant Images (Optional)</div>
                <div class="variant-form-grid">
                    <div class="form-group">
                        <label class="form-label">Variant Base Image</label>
                        <div class="image-upload-wrapper">
                            <input type="file"
                                   class="form-input-file"
                                   id="variant_base_${variantId}"
                                   name="variants[${variantId}][base_image]"
                                   accept="image/*"
                                   onchange="previewVariantBaseImage(event, ${variantId})"
                                   hidden>
                            <label for="variant_base_${variantId}" class="upload-label">
                                <div class="upload-icon">📸</div>
                                <div class="upload-text">Upload</div>
                            </label>
                        </div>
                        <div class="image-preview-container" id="variantBasePreview-${variantId}"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gallery Images</label>
                        <div class="image-upload-wrapper">
                            <input type="file"
                                   class="form-input-file"
                                   id="variant_gallery_${variantId}"
                                   name="variants[${variantId}][gallery_images][]"
                                   accept="image/*"
                                   multiple
                                   onchange="previewVariantGalleryImages(event, ${variantId})"
                                   hidden>
                            <label for="variant_gallery_${variantId}" class="upload-label">
                                <div class="upload-icon">🖼️</div>
                                <div class="upload-text">Upload</div>
                            </label>
                        </div>
                        <div class="gallery-preview" id="variantGalleryPreview-${variantId}"></div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', variantHtml);
        generatedVariants.push(variantId);
    }

    function removeVariant(id) {
        if (confirm('Remove this variant?')) {
            const variant = document.getElementById(`variant-${id}`);
            if (variant) {
                variant.remove();
                generatedVariants = generatedVariants.filter(v => v !== id);

                if (generatedVariants.length === 0) {
                    const noVariantsMessage = document.getElementById('noVariantsMessage');
                    const variantsInfo = document.getElementById('variantsInfo');

                    if (noVariantsMessage) {
                        noVariantsMessage.style.display = 'block';
                    }
                    if (variantsInfo) {
                        variantsInfo.classList.remove('show');
                    }
                }
            }
        }
    }

    // ========== PRICE VALIDATION ==========
    function validatePrices(variantId) {
        const costPrice = parseFloat(document.getElementById(`variant_cost_price_${variantId}`).value) || 0;
        const salePrice = parseFloat(document.getElementById(`variant_sale_price_${variantId}`).value) || 0;
        const mrpPrice = parseFloat(document.getElementById(`variant_mrp_price_${variantId}`).value) || 0;

        const salePriceMsg = document.getElementById(`sale_price_msg_${variantId}`);
        const mrpPriceMsg = document.getElementById(`mrp_price_msg_${variantId}`);

        const saleInput = document.getElementById(`variant_sale_price_${variantId}`);
        const mrpInput = document.getElementById(`variant_mrp_price_${variantId}`);

        // Reset
        saleInput.classList.remove('price-error', 'price-success');
        mrpInput.classList.remove('price-error', 'price-success');
        salePriceMsg.className = 'price-message';
        mrpPriceMsg.className = 'price-message';

        let isValid = true;

        // Sale price must be greater than cost price
        if (salePrice > 0 && salePrice <= costPrice) {
            saleInput.classList.add('price-error');
            salePriceMsg.className = 'price-message error';
            salePriceMsg.textContent = 'Sale price must be greater than cost price';
            isValid = false;
        } else if (salePrice > costPrice) {
            saleInput.classList.add('price-success');
            salePriceMsg.className = 'price-message success';
            salePriceMsg.textContent = '✓ Valid';
        }

        // MRP must be greater than sale price
        if (mrpPrice > 0 && mrpPrice <= salePrice) {
            mrpInput.classList.add('price-error');
            mrpPriceMsg.className = 'price-message error';
            mrpPriceMsg.textContent = 'MRP must be greater than sale price';
            isValid = false;
        } else if (mrpPrice > salePrice) {
            mrpInput.classList.add('price-success');
            mrpPriceMsg.className = 'price-message success';
            mrpPriceMsg.textContent = '✓ Valid';
        }

        return isValid;
    }

    // ========== SKU VALIDATION ==========
    function validateVariantSku(fieldId, skuCode) {
        if (!skuCode || skuCode.trim() === '') {
            return;
        }

        const inputField = document.getElementById(`variant_sku_${fieldId}`);
        const messageDiv = document.getElementById(`variant_${fieldId}_sku_message`);

        inputField.classList.remove('error', 'success');
        messageDiv.className = 'sku-validation-message';

        // Check duplicate in form
        const isDuplicate = checkSkuDuplicateInForm(fieldId, skuCode);
        if (isDuplicate) {
            inputField.classList.add('error');
            messageDiv.className = 'sku-validation-message error';
            messageDiv.textContent = 'SKU already used in another variant';
            return;
        }

        messageDiv.className = 'sku-validation-message checking';
        messageDiv.textContent = 'Checking...';

        fetch('{{ route("admin.products.check-sku") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                sku_code: skuCode.trim(),
                product_type: 'variant'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                inputField.classList.add('success');
                messageDiv.className = 'sku-validation-message success';
                messageDiv.textContent = data.message || '✓ Available';
            } else {
                inputField.classList.add('error');
                messageDiv.className = 'sku-validation-message error';
                messageDiv.textContent = data.message || 'SKU already exists';
            }
        })
        .catch(error => {
            console.error('SKU validation error:', error);
            messageDiv.textContent = 'Error checking SKU';
        });
    }

    function checkSkuDuplicateInForm(currentFieldId, skuCode) {
        const trimmedSku = skuCode.trim().toUpperCase();

        for (let i = 1; i <= variantCount; i++) {
            if (i === currentFieldId) continue;

            const variantSkuInput = document.getElementById(`variant_sku_${i}`);
            if (variantSkuInput) {
                const variantSku = variantSkuInput.value?.trim()?.toUpperCase();
                if (variantSku && variantSku === trimmedSku) {
                    return true;
                }
            }
        }

        return false;
    }

    // ========== IMAGE HANDLING ==========
    function previewBaseImage(event) {
        const input = event.target;
        const container = document.getElementById('baseImagePreview');
        container.innerHTML = '';

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" class="preview-image">
                    <button type="button" class="remove-image" onclick="removeBaseImage()">×</button>
                `;
                container.appendChild(div);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeBaseImage() {
        document.getElementById('base_image').value = '';
        document.getElementById('baseImagePreview').innerHTML = '';
    }

    function previewGalleryImages(event) {
        const input = event.target;
        const container = document.getElementById('galleryPreview');
        container.innerHTML = '';

        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" class="preview-image">
                    <button type="button" class="remove-image" onclick="removeGalleryImage(${index})">×</button>
                `;
                container.appendChild(div);
            };

            reader.readAsDataURL(file);
        });
    }

    function removeGalleryImage(index) {
        const input = document.getElementById('gallery_images');
        const files = Array.from(input.files);
        files.splice(index, 1);
        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
        previewGalleryImages({ target: input });
    }

    function previewVariantBaseImage(event, variantId) {
        const input = event.target;
        const container = document.getElementById(`variantBasePreview-${variantId}`);
        container.innerHTML = '';

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" class="preview-image">
                    <button type="button" class="remove-image" onclick="removeVariantBaseImage(${variantId})">×</button>
                `;
                container.appendChild(div);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeVariantBaseImage(variantId) {
        document.getElementById(`variant_base_${variantId}`).value = '';
        document.getElementById(`variantBasePreview-${variantId}`).innerHTML = '';
    }

    function previewVariantGalleryImages(event, variantId) {
        const input = event.target;
        const container = document.getElementById(`variantGalleryPreview-${variantId}`);
        container.innerHTML = '';

        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" class="preview-image">
                    <button type="button" class="remove-image" onclick="removeVariantGalleryImage(${variantId}, ${index})">×</button>
                `;
                container.appendChild(div);
            };

            reader.readAsDataURL(file);
        });
    }

    function removeVariantGalleryImage(variantId, imageIndex) {
        const input = document.getElementById(`variant_gallery_${variantId}`);
        const files = Array.from(input.files);
        files.splice(imageIndex, 1);
        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
        previewVariantGalleryImages({ target: input }, variantId);
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
            showAlert('Fill all required fields', 'error');
        }

        return isValid;
    }

    function validateForm() {
        let isValid = true;

        // Check at least one variant
        if (generatedVariants.length === 0) {
            showAlert('Generate at least one variant', 'error');
            currentTab = 2;
            showTab(2);
            return false;
        }

        // Validate all prices
        for (let i = 1; i <= variantCount; i++) {
            if (document.getElementById(`variant-${i}`)) {
                if (!validatePrices(i)) {
                    isValid = false;
                }
            }
        }

        if (!isValid) {
            showAlert('Fix price validation errors', 'error');
        }

        return isValid;
    }

    // ========== INITIALIZE ==========
    document.addEventListener('DOMContentLoaded', function() {
        showTab(0);

        // Tab buttons
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
            if (validateCurrentTab() && currentTab < tabs.length - 1) {
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

        // Form submit
        document.getElementById('productForm').addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return;
            }

            const submitBtn = this.querySelector('#submitBtn');
            submitBtn.innerHTML = '⏳ Creating...';
            submitBtn.disabled = true;
            if (localStorage.getItem('opened_from_purchase') === 'true') {
                localStorage.setItem('product_created', 'true');
                localStorage.setItem('product_created_close', 'true');
            }
        });

        // Update variant names when product name changes
        document.getElementById('productName').addEventListener('input', function() {
            const productName = this.value || 'Product';
            generatedVariants.forEach(variantId => {
                const variantCard = document.getElementById(`variant-${variantId}`);
                const nameInput = variantCard.querySelector('input[name*="[name]"]');
                const attributesInput = variantCard.querySelector('input[name*="[attributes]"]');

                if (nameInput && attributesInput) {
                    try {
                        const attributes = JSON.parse(attributesInput.value);
                        const attributeValues = attributes.map(attr => attr.value);
                        nameInput.value = `${productName} - ${attributeValues.join(' - ')}`;
                    } catch (e) {
                        console.error('Error parsing attributes:', e);
                    }
                }
            });
        });

        // Show errors if any
        @if($errors->any())
            @foreach($errors->all() as $error)
                showAlert('{{ $error }}', 'error');
            @endforeach
        @endif
    });
    function validateGST(input, errorId) {
        const value = parseFloat(input.value);
        const errorDiv = document.getElementById(errorId);

        if (!errorDiv) {
            console.error('Error div not found:', errorId);
            return;
        }

        if (isNaN(value)) {
            errorDiv.textContent = 'GST must be a number';
            input.classList.add('error');
        } else if (value < 0) {
            errorDiv.textContent = 'GST cannot be negative';
            input.classList.add('error');
        } else if (value > 100) {
            errorDiv.textContent = 'GST cannot be greater than 100%';
            input.classList.add('error');
        } else {
            errorDiv.textContent = '';
            input.classList.remove('error');
        }
    }
</script>
@endpush
@endsection
