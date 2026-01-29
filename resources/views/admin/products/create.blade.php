@extends('layouts.admin')

@section('title', 'Create Product - Admin Panel')
@section('header-title', 'Create New Product')

@section('content')
<div class="create-product-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ url('/admin/products') }}" class="back-btn">
                <span>←</span> Back to Products
            </a>
            <h2 class="page-title">Add New Product</h2>
        </div>
    </div>

    <form action="{{ url('/admin/products') }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf

        <!-- Tab Navigation -->
        <div class="tab-container">
            <div class="tab-nav">
                <button type="button" class="tab-btn active" data-tab="basic-info">
                    <span class="tab-icon">📝</span>
                    <span class="tab-text">Basic Info</span>
                </button>
                <button type="button" class="tab-btn" data-tab="media">
                    <span class="tab-icon">🖼️</span>
                    <span class="tab-text">Images</span>
                </button>
                <button type="button" class="tab-btn" data-tab="variants">
                    <span class="tab-icon">🎨</span>
                    <span class="tab-text">Variants</span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content-wrapper">
                <!-- Tab 1: Basic Information -->
                <div class="tab-content active" id="basic-info">
                    <div class="tab-header">
                        <h3 class="tab-title">Basic Product Information</h3>
                        <p class="tab-description">Enter the essential details about your product</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Product Name <span class="required">*</span></label>
                            <input type="text" class="form-input" id="productName" name="name" value="{{ old('name') }}" placeholder="e.g., LED Bulb 9W" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Warehouse <span class="required">*</span></label>
                            <select class="form-select" name="warehouse_id" required>
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }} ({{ $warehouse->code }})
                                </option>
                                @endforeach
                            </select>
                            <span class="form-hint">Select the warehouse where this product will be stored</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Brand <span class="required">*</span></label>
                            <input type="text" class="form-input" name="brand" value="{{ old('brand') }}" placeholder="Enter brand name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Body Type <span class="required">*</span></label>
                            <input type="text" class="form-input" name="body_type" value="{{ old('body_type') }}" placeholder="e.g., PVC, Metal, Plastic" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Warranty <span class="required">*</span></label>
                            <input type="text" class="form-input" name="warranty" value="{{ old('warranty') }}" placeholder="e.g., 12 months" maxlength="10" required>
                        </div>

                        <!-- ✅ UPDATED: Main Product SKU with Real-Time Validation -->
                        <div class="form-group">
                            <label class="form-label">SKU Code <span class="required">*</span></label>
                            <input
                                type="text"
                                class="form-input"
                                id="main_sku_code"
                                name="sku_code"
                                value="{{ old('sku_code') }}"
                                placeholder="e.g., LED-9W-001"
                                maxlength="16"
                                required
                                onkeyup="validateSkuCode('main', this.value)">
                            <div class="sku-validation-message" id="main_sku_message"></div>
                            <span class="form-hint">Unique product identifier (Max 16 characters)</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Barcode Symbology <span class="required">*</span></label>
                            <select class="form-select" name="barcode_symbology" required>
                                <option value="CODE128" {{ old('barcode_symbology') == 'CODE128' ? 'selected' : '' }}>Code 128</option>
                                <option value="CODE39" {{ old('barcode_symbology') == 'CODE39' ? 'selected' : '' }}>Code 39</option>
                                <option value="EAN13" {{ old('barcode_symbology') == 'EAN13' ? 'selected' : '' }}>EAN-13</option>
                                <option value="EAN8" {{ old('barcode_symbology') == 'EAN8' ? 'selected' : '' }}>EAN-8</option>
                                <option value="UPC" {{ old('barcode_symbology') == 'UPC' ? 'selected' : '' }}>UPC</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">HSN Code <span class="required">*</span></label>
                            <input type="text" class="form-input" name="hsn_code" value="{{ old('hsn_code') }}" placeholder="85395000" maxlength="8" required>
                            <span class="form-hint">Maximum 8 digits</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">GST (%) <span class="required">*</span></label>
                            <input type="number" step="0.01" class="form-input" name="gst" value="{{ old('gst') }}" placeholder="18" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Description <span class="required">*</span></label>
                            <textarea class="form-textarea" name="description" rows="5" placeholder="Enter product description with features and specifications" required>{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Images -->
                <div class="tab-content" id="media">
                    <div class="tab-header">
                        <h3 class="tab-title">Product Images</h3>
                        <p class="tab-description">Upload images (800x800px recommended)</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Main Image <span class="required">*</span></label>
                            <div class="image-upload-wrapper">
                                <input type="file" class="form-input-file" id="base_image" name="base_image" accept="image/*" onchange="previewBaseImage(event)" required hidden>
                                <label for="base_image" class="upload-label">
                                    <div class="upload-icon">📸</div>
                                    <div class="upload-text">Click to upload</div>
                                    <div class="upload-hint">PNG, JPG up to 2MB</div>
                                </label>
                            </div>
                            <div class="image-preview-container" id="baseImagePreview"></div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Gallery Images</label>
                            <div class="image-upload-wrapper">
                                <input type="file" class="form-input-file" id="gallery_images" name="gallery_images[]" accept="image/*" multiple onchange="previewGalleryImages(event)" hidden>
                                <label for="gallery_images" class="upload-label">
                                    <div class="upload-icon">🖼️</div>
                                    <div class="upload-text">Upload multiple images</div>
                                    <div class="upload-hint">Select multiple files</div>
                                </label>
                            </div>
                            <div class="gallery-preview" id="galleryPreview"></div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Variants -->
                <div class="tab-content" id="variants">
                    <div class="tab-header">
                        <h3 class="tab-title">Product Variants</h3>
                        <p class="tab-description">Select attribute values to generate variants automatically</p>
                    </div>

                    <div class="variants-section">
                        <!-- Attribute Selector -->
                        <div class="attribute-selector-section">
                            <div class="selector-title">
                                🎨 Select Attribute Values for Variants
                            </div>

                            <div id="attributesContainer">
                                <div class="loading-attributes">
                                    <div class="spinner"></div>
                                    <span>Loading attributes...</span>
                                </div>
                            </div>

                            <button type="button" class="btn-generate-variants" id="generateVariantsBtn" onclick="generateVariants()" disabled>
                                <span>⚡</span> Generate Variants
                            </button>
                        </div>

                        <!-- Generated Variants Info -->
                        <div class="generated-variants-info" id="variantsInfo">
                            <p class="info-text" id="variantsInfoText"></p>
                        </div>

                        <!-- Variants Container -->
                        <div id="variantsContainer">
                            <!-- Generated variants will appear here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions-fixed">
            <div class="form-actions-content">
                <button type="button" class="btn-back" id="backBtn" style="display: none;">
                    <span>←</span> Back
                </button>
                <div class="action-buttons-right">
                    <button type="button" class="btn-next" id="nextBtn">
                        Next <span>→</span>
                    </button>
                    <button type="submit" class="btn-primary" id="submitBtn" style="display: none;">
                        <span>✓</span> Create Product
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .create-product-container { padding-bottom: 100px; }
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }
    .page-header { margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; margin-bottom: 15px; }
    .back-btn:hover { background: #f7fafc; color: #ff6b35; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .tab-container { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
    .tab-nav { display: flex; background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-bottom: 2px solid #e2e8f0; overflow-x: auto; }
    .tab-btn { flex: 1; min-width: 140px; padding: 18px 20px; background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; font-weight: 600; color: #718096; }
    .tab-btn:hover { background: rgba(255,107,53,0.05); color: #ff6b35; }
    .tab-btn.active { background: white; color: #ff6b35; border-bottom-color: #ff6b35; }
    .tab-btn.completed { color: #38a169; }
    .tab-icon { font-size: 18px; }
    .tab-content-wrapper { padding: 40px; }
    .tab-content { display: none; }
    .tab-content.active { display: block; animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .tab-header { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e2e8f0; }
    .tab-title { font-size: 20px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .tab-description { font-size: 14px; color: #718096; }
    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin-bottom: 25px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: span 2; }
    .form-label { margin-bottom: 8px; font-weight: 600; color: #4a5568; font-size: 14px; }
    .required { color: #fc8181; }
    .form-input, .form-textarea, .form-select { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; font-family: inherit; }
    .form-input:focus, .form-textarea:focus, .form-select:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
    .form-input.error, .form-textarea.error, .form-select.error { border-color: #fc8181; background: #fff5f5; }
    .form-textarea { min-height: 120px; resize: vertical; }
    .form-hint { margin-top: 6px; font-size: 12px; color: #a0aec0; }
    .error-hint { color: #fc8181 !important; font-weight: 600; }

    /* ✅ SKU VALIDATION STYLES */
    .sku-validation-message {
        display: none;
        margin-top: 6px;
        font-size: 12px;
        font-weight: 600;
        padding: 8px 12px;
        border-radius: 6px;
        animation: slideDown 0.3s ease;
    }
    .sku-validation-message.error {
        display: block;
        background: #fff5f5;
        color: #c53030;
        border-left: 3px solid #fc8181;
    }
    .sku-validation-message.success {
        display: block;
        background: #f0fff4;
        color: #22543d;
        border-left: 3px solid #68d391;
    }
    .sku-validation-message.checking {
        display: block;
        background: #ebf8ff;
        color: #2c5282;
        border-left: 3px solid #4299e1;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .form-input.sku-error {
        border-color: #fc8181 !important;
        background: #fff5f5 !important;
    }
    .form-input.sku-success {
        border-color: #68d391 !important;
        background: #f0fff4 !important;
    }
    .form-input.sku-checking {
        border-color: #4299e1 !important;
        background: #ebf8ff !important;
    }

    .image-upload-wrapper { margin-bottom: 20px; }
    .upload-label { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; border: 3px dashed #cbd5e0; border-radius: 12px; background: #f7fafc; cursor: pointer; transition: all 0.3s; }
    .upload-label:hover { border-color: #ff6b35; background: rgba(255,107,53,0.05); }
    .upload-icon { font-size: 48px; margin-bottom: 15px; }
    .upload-text { font-size: 16px; font-weight: 600; color: #2d3748; margin-bottom: 5px; }
    .upload-hint { font-size: 13px; color: #a0aec0; }
    .image-preview-container, .gallery-preview { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 20px; }
    .preview-item { position: relative; border-radius: 12px; overflow: hidden; border: 2px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .preview-image { width: 100%; height: 150px; object-fit: cover; display: block; }
    .remove-image { position: absolute; top: 8px; right: 8px; width: 32px; height: 32px; background: #fc8181; color: white; border: none; border-radius: 50%; cursor: pointer; font-size: 18px; font-weight: 700; display: flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
    .remove-image:hover { background: #f56565; transform: scale(1.1); }
    .variants-section { margin-top: 20px; }
    .attribute-selector-section { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); padding: 25px; border-radius: 12px; margin-bottom: 30px; border: 2px solid #e2e8f0; }
    .selector-title { font-size: 16px; font-weight: 700; color: #2d3748; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .loading-attributes { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 20px; color: #718096; }
    .spinner { width: 20px; height: 20px; border: 3px solid #e2e8f0; border-top-color: #ff6b35; border-radius: 50%; animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .attributes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; margin-bottom: 20px; }
    .attribute-group { background: white; padding: 20px; border-radius: 12px; border: 2px solid #e2e8f0; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .attribute-group-title { font-size: 15px; font-weight: 700; color: #2d3748; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0; }
    .attribute-values { display: flex; flex-direction: column; gap: 10px; }
    .attribute-checkbox { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 8px; transition: all 0.2s; cursor: pointer; border: 1px solid transparent; }
    .attribute-checkbox:hover { background: #f7fafc; border-color: #cbd5e0; }
    .attribute-checkbox input[type="checkbox"] { width: 20px; height: 20px; cursor: pointer; accent-color: #ff6b35; }
    .attribute-checkbox label { cursor: pointer; font-size: 14px; color: #2d3748; flex: 1; font-weight: 500; }
    .btn-generate-variants { padding: 14px 28px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(102,126,234,0.3); display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 20px; width: 100%; }
    .btn-generate-variants:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }
    .btn-generate-variants:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
    .generated-variants-info { background: #e6fffa; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: none; border-left: 4px solid #38a169; }
    .generated-variants-info.show { display: block; }
    .info-text { font-size: 14px; color: #234e52; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .no-attributes-message { background: #fff5f5; padding: 30px; border-radius: 12px; text-align: center; border: 2px dashed #feb2b2; margin-bottom: 20px; }
    .no-attributes-message p { color: #9b2c2c; font-weight: 600; margin-bottom: 15px; font-size: 15px; }
    .no-attributes-message a { color: #c53030; text-decoration: underline; font-weight: 600; }
    #variantsContainer { display: flex; flex-direction: column; gap: 25px; margin-top: 20px; }
    .variant-card { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); padding: 30px; border-radius: 16px; border: 2px solid #e2e8f0; position: relative; animation: slideIn 0.3s ease; }
    .variant-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #cbd5e0; }
    .variant-title { font-size: 18px; font-weight: 700; color: #2d3748; }
    .btn-remove-variant { padding: 8px 16px; background: #fc8181; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .btn-remove-variant:hover { background: #f56565; transform: scale(1.05); }
    .variant-attribute-info { background: #ebf8ff; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4299e1; font-size: 13px; }
    .variant-attribute-info strong { color: #2c5282; }
    .variant-section-title { font-size: 16px; font-weight: 700; color: #2d3748; margin: 20px 0 15px; padding-bottom: 8px; border-bottom: 2px solid #cbd5e0; }
    .form-actions-fixed { position: fixed; bottom: 0; left: 260px; right: 0; background: white; padding: 20px 35px; box-shadow: 0 -4px 20px rgba(0,0,0,0.1); z-index: 100; }
    .form-actions-content { display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto; }
    .action-buttons-right { display: flex; gap: 15px; margin-left: auto; }
    .btn-back, .btn-next { padding: 12px 24px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
    .btn-back:hover, .btn-next:hover { background: #cbd5e0; transform: translateY(-2px); }
    .btn-primary { padding: 12px 30px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(255,107,53,0.3); display: flex; align-items: center; gap: 8px; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.4); }
    @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .form-group.full-width { grid-column: span 1; } .form-actions-fixed { left: 0; } .attributes-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@push('scripts')
<script>
    let currentTab = 0;
    const tabs = ['basic-info', 'media', 'variants'];
    let variantCount = 0;
    let selectedAttributes = {};

    // ✅ SKU VALIDATION VARIABLES
    let skuValidationTimers = {};
    let skuValidationStates = {};

    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span class="alert-icon">${type === 'success' ? '✓' : '✕'}</span><span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => { alert.classList.add('removing'); setTimeout(() => alert.remove(), 300); }, 5000);
    }

    function showTab(index) {
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(tabs[index]).classList.add('active');
        document.querySelectorAll('.tab-btn')[index].classList.add('active');
        document.querySelectorAll('.tab-btn').forEach((btn, i) => {
            if (i < index) btn.classList.add('completed');
            else btn.classList.remove('completed');
        });
        const backBtn = document.getElementById('backBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        backBtn.style.display = index === 0 ? 'none' : 'flex';
        nextBtn.style.display = index === tabs.length - 1 ? 'none' : 'flex';
        submitBtn.style.display = index === tabs.length - 1 ? 'flex' : 'none';
    }

    function validateCurrentTab() {
        const currentTabElement = document.getElementById(tabs[currentTab]);
        const requiredInputs = currentTabElement.querySelectorAll('[required]');
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
        if (firstErrorField) firstErrorField.focus();
        return isValid;
    }

    // ✅ REAL-TIME SKU VALIDATION FUNCTION
    function validateSkuCode(fieldId, skuCode) {
        // Clear existing timer
        if (skuValidationTimers[fieldId]) {
            clearTimeout(skuValidationTimers[fieldId]);
        }

        const inputField = document.getElementById(fieldId === 'main' ? 'main_sku_code' : `variant_sku_${fieldId.split('_')[1]}`);
        const messageDiv = document.getElementById(`${fieldId}_sku_message`);

        // Reset validation state
        inputField.classList.remove('sku-error', 'sku-success', 'sku-checking');
        messageDiv.className = 'sku-validation-message';
        messageDiv.textContent = '';

        // If empty, don't validate
        if (!skuCode || skuCode.trim() === '') {
            skuValidationStates[fieldId] = null;
            return;
        }

        // Show checking state
        inputField.classList.add('sku-checking');
        messageDiv.className = 'sku-validation-message checking';
        messageDiv.textContent = '🔍 Checking SKU availability...';

        // Set new timer for debounced validation
        skuValidationTimers[fieldId] = setTimeout(() => {
            // First check for duplicates within the form
            const isDuplicateInForm = checkSkuDuplicateInForm(fieldId, skuCode);

            if (isDuplicateInForm) {
                inputField.classList.remove('sku-checking');
                inputField.classList.add('sku-error');
                messageDiv.className = 'sku-validation-message error';
                messageDiv.textContent = '⚠️ This SKU code is already used in another field on this form';
                skuValidationStates[fieldId] = false;
                return;
            }

            // Then check against database
            fetch('{{ route("admin.products.check-sku") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    sku_code: skuCode.trim(),
                    product_id: null // Set this if editing
                })
            })
            .then(response => response.json())
            .then(data => {
                inputField.classList.remove('sku-checking');

                if (data.available) {
                    inputField.classList.add('sku-success');
                    messageDiv.className = 'sku-validation-message success';
                    messageDiv.textContent = data.message;
                    skuValidationStates[fieldId] = true;
                } else {
                    inputField.classList.add('sku-error');
                    messageDiv.className = 'sku-validation-message error';
                    messageDiv.textContent = data.message;
                    skuValidationStates[fieldId] = false;
                }
            })
            .catch(error => {
                console.error('SKU validation error:', error);
                inputField.classList.remove('sku-checking');
                messageDiv.className = 'sku-validation-message';
                messageDiv.textContent = '';
                skuValidationStates[fieldId] = null;
            });
        }, 500); // 500ms debounce
    }

    // ✅ CHECK SKU DUPLICATE WITHIN FORM
    function checkSkuDuplicateInForm(currentFieldId, skuCode) {
        if (!skuCode || skuCode.trim() === '') {
            return false;
        }

        const trimmedSku = skuCode.trim().toUpperCase();

        // Check main SKU field
        if (currentFieldId !== 'main') {
            const mainSku = document.getElementById('main_sku_code')?.value?.trim()?.toUpperCase();
            if (mainSku && mainSku === trimmedSku) {
                return true;
            }
        }

        // Check all variant SKU fields
        const variantCards = document.querySelectorAll('.variant-card');
        for (let card of variantCards) {
            const variantId = card.id.replace('variant-', '');
            const fieldIdToCheck = `variant_${variantId}`;

            if (fieldIdToCheck !== currentFieldId) {
                const variantSkuInput = document.getElementById(`variant_sku_${variantId}`);
                if (variantSkuInput) {
                    const variantSku = variantSkuInput.value?.trim()?.toUpperCase();
                    if (variantSku && variantSku === trimmedSku) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    // ✅ VALIDATE ALL SKU CODES BEFORE FORM SUBMISSION
    function validateAllSkuCodes() {
        let allValid = true;
        const errors = [];

        // Check main SKU
        const mainSku = document.getElementById('main_sku_code')?.value?.trim();
        if (mainSku) {
            if (skuValidationStates['main'] === false) {
                allValid = false;
                errors.push('Main product SKU code is invalid or already in use');
            } else if (skuValidationStates['main'] === null) {
                allValid = false;
                errors.push('Main product SKU code validation is pending');
            }
        }

        // Check variant SKUs
        const variantCards = document.querySelectorAll('.variant-card');
        variantCards.forEach((card, index) => {
            const variantId = card.id.replace('variant-', '');
            const fieldId = `variant_${variantId}`;
            const variantSku = document.getElementById(`variant_sku_${variantId}`)?.value?.trim();

            if (variantSku) {
                if (skuValidationStates[fieldId] === false) {
                    allValid = false;
                    errors.push(`Variant #${variantId} SKU code is invalid or already in use`);
                } else if (skuValidationStates[fieldId] === null) {
                    allValid = false;
                    errors.push(`Variant #${variantId} SKU code validation is pending`);
                }
            }
        });

        if (!allValid && errors.length > 0) {
            showAlert(errors[0], 'error');
        }

        return allValid;
    }

    // Real-time validation for variant pricing
    function validateVariantPricing(variantId) {
        const costPrice = parseFloat(document.getElementById(`variant_cost_price_${variantId}`).value) || 0;
        const mrpPrice = parseFloat(document.getElementById(`variant_mrp_price_${variantId}`).value) || 0;
        const dealerPrice = parseFloat(document.getElementById(`variant_dealer_price_${variantId}`).value) || 0;
        const distributorPrice = parseFloat(document.getElementById(`variant_distributor_price_${variantId}`).value) || 0;

        const mrpInput = document.getElementById(`variant_mrp_price_${variantId}`);
        const dealerInput = document.getElementById(`variant_dealer_price_${variantId}`);
        const distributorInput = document.getElementById(`variant_distributor_price_${variantId}`);

        const mrpError = document.getElementById(`mrp_error_${variantId}`);
        const dealerError = document.getElementById(`dealer_error_${variantId}`);
        const distributorError = document.getElementById(`distributor_error_${variantId}`);

        // Validate MRP Price
        if (mrpPrice > 0 && mrpPrice <= costPrice) {
            mrpInput.classList.add('error');
            mrpError.style.display = 'block';
        } else {
            mrpInput.classList.remove('error');
            mrpError.style.display = 'none';
        }

        // Validate Dealer Price
        if (dealerPrice > 0 && dealerPrice <= costPrice) {
            dealerInput.classList.add('error');
            dealerError.style.display = 'block';
        } else {
            dealerInput.classList.remove('error');
            dealerError.style.display = 'none';
        }

        // Validate Distributor Price
        if (distributorPrice > 0 && distributorPrice <= costPrice) {
            distributorInput.classList.add('error');
            distributorError.style.display = 'block';
        } else {
            distributorInput.classList.remove('error');
            distributorError.style.display = 'none';
        }
    }

    // Real-time validation for variant stock
    function validateVariantStock(variantId) {
        const openingStock = parseInt(document.getElementById(`variant_opening_stock_${variantId}`).value) || 0;
        const minStockAlert = parseInt(document.getElementById(`variant_min_stock_${variantId}`).value) || 0;

        const openingStockInput = document.getElementById(`variant_opening_stock_${variantId}`);
        const openingStockError = document.getElementById(`opening_stock_error_${variantId}`);

        // Validate Opening Stock >= Min Stock Alert
        if (openingStock < minStockAlert) {
            openingStockInput.classList.add('error');
            openingStockError.style.display = 'block';
        } else {
            openingStockInput.classList.remove('error');
            openingStockError.style.display = 'none';
        }
    }

    document.getElementById('nextBtn').addEventListener('click', () => {
        if (!validateCurrentTab()) {
            showAlert('Please fill in all required fields', 'error');
            return;
        }
        if (currentTab < tabs.length - 1) {
            currentTab++;
            showTab(currentTab);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    document.getElementById('backBtn').addEventListener('click', () => {
        if (currentTab > 0) {
            currentTab--;
            showTab(currentTab);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    document.querySelectorAll('.tab-btn').forEach((btn, index) => {
        btn.addEventListener('click', () => {
            currentTab = index;
            showTab(currentTab);
        });
    });

    // ✅ UPDATED FORM SUBMIT WITH SKU VALIDATION
    document.getElementById('productForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // ✅ VALIDATE ALL SKU CODES FIRST
        if (!validateAllSkuCodes()) {
            currentTab = 0; // Go to basic info tab if main SKU invalid
            // Check if error is in variants tab
            const variantCards = document.querySelectorAll('.variant-card');
            variantCards.forEach((card) => {
                const variantId = card.id.replace('variant-', '');
                const fieldId = `variant_${variantId}`;
                if (skuValidationStates[fieldId] === false) {
                    currentTab = 2; // Go to variants tab
                }
            });
            showTab(currentTab);
            return;
        }

        // Validate all variants have required fields filled and pass validation
        const variantCards = document.querySelectorAll('.variant-card');
        let allVariantsValid = true;
        let validationErrors = [];

        variantCards.forEach((card, index) => {
            const variantId = card.id.replace('variant-', '');

            // Check required fields
            const requiredFields = card.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.classList.remove('error');
                if (!field.value.trim()) {
                    field.classList.add('error');
                    allVariantsValid = false;
                }
            });

            // Validate pricing
            const costPrice = parseFloat(document.getElementById(`variant_cost_price_${variantId}`).value) || 0;
            const mrpPrice = parseFloat(document.getElementById(`variant_mrp_price_${variantId}`).value) || 0;
            const dealerPrice = parseFloat(document.getElementById(`variant_dealer_price_${variantId}`).value) || 0;
            const distributorPrice = parseFloat(document.getElementById(`variant_distributor_price_${variantId}`).value) || 0;

            if (mrpPrice > 0 && mrpPrice <= costPrice) {
                allVariantsValid = false;
                validationErrors.push(`Variant #${variantId}: MRP price must be greater than cost price`);
            }
            if (dealerPrice > 0 && dealerPrice <= costPrice) {
                allVariantsValid = false;
                validationErrors.push(`Variant #${variantId}: Dealer price must be greater than cost price`);
            }
            if (distributorPrice > 0 && distributorPrice <= costPrice) {
                allVariantsValid = false;
                validationErrors.push(`Variant #${variantId}: Distributor price must be greater than cost price`);
            }

            // Validate stock
            const openingStock = parseInt(document.getElementById(`variant_opening_stock_${variantId}`).value) || 0;
            const minStockAlert = parseInt(document.getElementById(`variant_min_stock_${variantId}`).value) || 0;

            if (openingStock < minStockAlert) {
                allVariantsValid = false;
                validationErrors.push(`Variant #${variantId}: Opening stock must be greater than or equal to min stock alert`);
            }
        });

        if (!allVariantsValid) {
            currentTab = 2; // Variants tab
            showTab(currentTab);
            if (validationErrors.length > 0) {
                showAlert(validationErrors[0], 'error');
            } else {
                showAlert('Please fill in all required variant fields', 'error');
            }
            return;
        }

        // Validate all required fields
        let allValid = true;
        const allRequiredInputs = document.querySelectorAll('[required]');
        allRequiredInputs.forEach(input => {
            input.classList.remove('error');
            if (!input.value.trim()) {
                input.classList.add('error');
                allValid = false;
            }
        });
        if (!allValid) {
            for (let i = 0; i < tabs.length; i++) {
                const tabElement = document.getElementById(tabs[i]);
                const errorInput = tabElement.querySelector('.error');
                if (errorInput) {
                    currentTab = i;
                    showTab(i);
                    errorInput.focus();
                    showAlert('Please fill in all required fields', 'error');
                    return;
                }
            }
            showAlert('Please fill in all required fields', 'error');
            return;
        }
        this.submit();
    });

    let baseImageFile = null;
    function previewBaseImage(event) {
        const input = event.target;
        const container = document.getElementById('baseImagePreview');
        container.innerHTML = '';
        if (input.files && input.files[0]) {
            baseImageFile = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `<img src="${e.target.result}" class="preview-image"><button type="button" class="remove-image" onclick="removeBaseImage()">×</button>`;
                container.appendChild(div);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeBaseImage() {
        document.getElementById('base_image').value = '';
        document.getElementById('baseImagePreview').innerHTML = '';
        baseImageFile = null;
    }

    let galleryFiles = [];
    function previewGalleryImages(event) {
        const input = event.target;
        const container = document.getElementById('galleryPreview');
        container.innerHTML = '';
        galleryFiles = Array.from(input.files);
        galleryFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `<img src="${e.target.result}" class="preview-image"><button type="button" class="remove-image" onclick="removeGalleryImage(${index})">×</button>`;
                container.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }

    function removeGalleryImage(index) {
        galleryFiles.splice(index, 1);
        const dataTransfer = new DataTransfer();
        galleryFiles.forEach(file => dataTransfer.items.add(file));
        document.getElementById('gallery_images').files = dataTransfer.files;
        previewGalleryImages({ target: document.getElementById('gallery_images') });
    }

    // Load attributes
    document.addEventListener('DOMContentLoaded', function() {
        loadAttributes();
        showTab(0);
    });

    function loadAttributes() {
        fetch('{{ route("admin.attributes.active") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderAttributeSelector(data.attributes);
                } else {
                    showAlert('Failed to load attributes', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading attributes:', error);
                renderAttributeSelector({});
            });
    }

    function renderAttributeSelector(attributes) {
        const container = document.getElementById('attributesContainer');
        const hasColorTemp = attributes.color_temperature && attributes.color_temperature.length > 0;
        const hasWatt = attributes.watt && attributes.watt.length > 0;
        const hasShape = attributes.shape && attributes.shape.length > 0;

        if (!hasColorTemp && !hasWatt && !hasShape) {
            container.innerHTML = `
                <div class="no-attributes-message">
                    <p>⚠️ No attribute values found!</p>
                    <p>Please create attribute values first to generate variants.</p>
                    <a href="{{ route('admin.attributes.create') }}" target="_blank">Create Attribute Values →</a>
                </div>
            `;
            document.getElementById('generateVariantsBtn').disabled = true;
            return;
        }

        let html = '<div class="attributes-grid">';

        if (hasColorTemp) {
            html += `
                <div class="attribute-group">
                    <div class="attribute-group-title">🌡️ Color Temperature</div>
                    <div class="attribute-values">
            `;
            attributes.color_temperature.forEach(value => {
                const checkboxId = `color_temp_${value.replace(/\s+/g, '_')}`;
                html += `
                    <div class="attribute-checkbox">
                        <input type="checkbox" id="${checkboxId}" value="${value}" data-attribute-type="color_temperature" data-attribute-name="Color Temperature" onchange="updateSelectedAttributes()">
                        <label for="${checkboxId}">${value}</label>
                    </div>
                `;
            });
            html += `</div></div>`;
        }

        if (hasWatt) {
            html += `
                <div class="attribute-group">
                    <div class="attribute-group-title">⚡ Watt</div>
                    <div class="attribute-values">
            `;
            attributes.watt.forEach(value => {
                const checkboxId = `watt_${value.replace(/\s+/g, '_')}`;
                html += `
                    <div class="attribute-checkbox">
                        <input type="checkbox" id="${checkboxId}" value="${value}" data-attribute-type="watt" data-attribute-name="Watt" onchange="updateSelectedAttributes()">
                        <label for="${checkboxId}">${value}</label>
                    </div>
                `;
            });
            html += `</div></div>`;
        }

        if (hasShape) {
            html += `
                <div class="attribute-group">
                    <div class="attribute-group-title">🔶 Shape</div>
                    <div class="attribute-values">
            `;
            attributes.shape.forEach(value => {
                const checkboxId = `shape_${value.replace(/\s+/g, '_')}`;
                html += `
                    <div class="attribute-checkbox">
                        <input type="checkbox" id="${checkboxId}" value="${value}" data-attribute-type="shape" data-attribute-name="Shape" onchange="updateSelectedAttributes()">
                        <label for="${checkboxId}">${value}</label>
                    </div>
                `;
            });
            html += `</div></div>`;
        }

        html += '</div>';
        container.innerHTML = html;
        document.getElementById('generateVariantsBtn').disabled = false;
    }

    function updateSelectedAttributes() {
        selectedAttributes = {};
        document.querySelectorAll('#attributesContainer input[type="checkbox"]:checked').forEach(checkbox => {
            const attrType = checkbox.dataset.attributeType;
            const attrName = checkbox.dataset.attributeName;
            const value = checkbox.value;
            if (!selectedAttributes[attrType]) {
                selectedAttributes[attrType] = { name: attrName, values: [] };
            }
            selectedAttributes[attrType].values.push(value);
        });
        const hasSelections = Object.keys(selectedAttributes).length > 0;
        document.getElementById('generateVariantsBtn').disabled = !hasSelections;
    }

    function generateVariants() {
        const attributeTypes = ['color_temperature', 'watt', 'shape'];
        const activeAttributes = {};
        attributeTypes.forEach(type => {
            if (selectedAttributes[type] && selectedAttributes[type].values.length > 0) {
                activeAttributes[type] = selectedAttributes[type].values;
            }
        });

        if (Object.keys(activeAttributes).length === 0) {
            showAlert('Please select at least one attribute value', 'error');
            return;
        }

        const combinations = generateCombinations(activeAttributes);
        document.getElementById('variantsContainer').innerHTML = '';
        variantCount = 0;

        combinations.forEach(combination => {
            createVariantFromCombination(combination);
        });

        const info = document.getElementById('variantsInfo');
        const infoText = document.getElementById('variantsInfoText');
        infoText.textContent = `✓ Generated ${combinations.length} variant(s) from selected attributes`;
        info.classList.add('show');
        showAlert(`Successfully generated ${combinations.length} variant(s)!`, 'success');
        document.getElementById('variantsContainer').scrollIntoView({ behavior: 'smooth' });
    }

    function generateCombinations(attributes) {
        const attributeTypes = Object.keys(attributes);
        if (attributeTypes.length === 0) return [];
        let combinations = [{}];
        attributeTypes.forEach(type => {
            const values = attributes[type];
            const newCombinations = [];
            combinations.forEach(combo => {
                values.forEach(value => {
                    newCombinations.push({ ...combo, [type]: value });
                });
            });
            combinations = newCombinations;
        });
        return combinations;
    }

    function createVariantFromCombination(combination) {
        variantCount++;
        const container = document.getElementById('variantsContainer');

        // Get product name
        const productName = document.getElementById('productName').value || 'Product';

        // Build attribute string
        const colorTemp = combination.color_temperature || '';
        const watt = combination.watt || '';
        const shape = combination.shape || '';
        const parts = [];
        if (watt) parts.push(watt);
        if (colorTemp) parts.push(colorTemp);
        if (shape) parts.push(shape);
        const attributeString = parts.join(' - ');

        // Generate variant name: ProductName-AttributeString
        const variantName = `${productName}-${attributeString}`;

        const skuParts = [];
        if (watt) skuParts.push(watt.replace(/\s+/g, ''));
        if (colorTemp) skuParts.push(colorTemp.replace(/\s+/g, ''));
        if (shape) skuParts.push(shape.replace(/\s+/g, ''));
        const skuSuffix = skuParts.join('-').toUpperCase();

        const variantHtml = `
            <div class="variant-card" id="variant-${variantCount}">
                <div class="variant-header">
                    <h4 class="variant-title">Variant #${variantCount}: ${attributeString}</h4>
                    <button type="button" class="btn-remove-variant" onclick="removeVariant(${variantCount})">Remove Variant</button>
                </div>
                <div class="variant-attribute-info"><strong>Attribute Combination:</strong> ${attributeString}</div>
                <input type="hidden" name="variants[${variantCount}][attributes]" value='${JSON.stringify(combination)}'>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Variant Name <span class="required">*</span></label>
                        <input type="text" class="form-input" name="variants[${variantCount}][name]" value="${variantName}" required>
                        <span class="form-hint">Auto-generated from product name and attributes</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Color Temperature</label>
                        <input type="text" class="form-input" name="variants[${variantCount}][color_temperature]" value="${colorTemp}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Watt</label>
                        <input type="text" class="form-input" name="variants[${variantCount}][watt]" value="${watt}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Shape</label>
                        <input type="text" class="form-input" name="variants[${variantCount}][shape]" value="${shape}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit <span class="required">*</span></label>
                        <input type="text" class="form-input" name="variants[${variantCount}][unit]" placeholder="Piece, Box, Set" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">SKU Code <span class="required">*</span></label>
                        <input
                            type="text"
                            class="form-input"
                            id="variant_sku_${variantCount}"
                            name="variants[${variantCount}][sku_code]"
                            placeholder="e.g., VAR-${skuSuffix}"
                            maxlength="16"
                            required
                            onkeyup="validateSkuCode('variant_${variantCount}', this.value)">
                        <div class="sku-validation-message" id="variant_${variantCount}_sku_message"></div>
                        <span class="form-hint">Max 16 characters</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Barcode Symbology <span class="required">*</span></label>
                        <select class="form-select" name="variants[${variantCount}][barcode_symbology]" required>
                            <option value="CODE128">Code 128</option>
                            <option value="CODE39">Code 39</option>
                            <option value="EAN13">EAN-13</option>
                            <option value="EAN8">EAN-8</option>
                            <option value="UPC">UPC</option>
                        </select>
                    </div>
                </div>
                <div class="variant-section-title">Pricing</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Cost Price (₹) <span class="required">*</span></label>
                        <input type="number" step="0.01" class="form-input" id="variant_cost_price_${variantCount}" name="variants[${variantCount}][cost_price]" placeholder="0.00" required oninput="validateVariantPricing(${variantCount})">
                        <span class="form-hint">Base cost of the product</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">MRP Price (₹) <span class="required">*</span></label>
                        <input type="number" step="0.01" class="form-input" id="variant_mrp_price_${variantCount}" name="variants[${variantCount}][mrp_price]" placeholder="0.00" required oninput="validateVariantPricing(${variantCount})">
                        <span class="form-hint error-hint" id="mrp_error_${variantCount}" style="display: none;">Must be greater than cost price</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dealer Price (₹) <span class="required">*</span></label>
                        <input type="number" step="0.01" class="form-input" id="variant_dealer_price_${variantCount}" name="variants[${variantCount}][dealer_price]" placeholder="0.00" required oninput="validateVariantPricing(${variantCount})">
                        <span class="form-hint error-hint" id="dealer_error_${variantCount}" style="display: none;">Must be greater than cost price</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Distributor Price (₹) <span class="required">*</span></label>
                        <input type="number" step="0.01" class="form-input" id="variant_distributor_price_${variantCount}" name="variants[${variantCount}][distributor_price]" placeholder="0.00" required oninput="validateVariantPricing(${variantCount})">
                        <span class="form-hint error-hint" id="distributor_error_${variantCount}" style="display: none;">Must be greater than cost price</span>
                    </div>
                </div>
                <div class="variant-section-title">Stock Management</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Opening Stock <span class="required">*</span></label>
                        <input type="number" class="form-input" id="variant_opening_stock_${variantCount}" name="variants[${variantCount}][opening_stock]" value="0" required oninput="validateVariantStock(${variantCount})">
                        <span class="form-hint error-hint" id="opening_stock_error_${variantCount}" style="display: none;">Must be greater than or equal to min stock alert</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Min Stock Alert <span class="required">*</span></label>
                        <input type="number" class="form-input" id="variant_min_stock_${variantCount}" name="variants[${variantCount}][min_stock_alert]" value="10" required oninput="validateVariantStock(${variantCount})">
                        <span class="form-hint">Alert when stock reaches this level</span>
                    </div>
                </div>
                <div class="variant-section-title">Variant Images</div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Variant Base Image</label>
                        <div class="image-upload-wrapper">
                            <input type="file" class="form-input-file" id="variant_base_${variantCount}" name="variants[${variantCount}][base_image]" accept="image/*" onchange="previewVariantBaseImage(event, ${variantCount})" hidden>
                            <label for="variant_base_${variantCount}" class="upload-label">
                                <div class="upload-icon">📸</div>
                                <div class="upload-text">Click to upload variant image</div>
                                <div class="upload-hint">PNG, JPG up to 2MB</div>
                            </label>
                        </div>
                        <div class="image-preview-container" id="variantBasePreview-${variantCount}"></div>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Variant Gallery Images</label>
                        <div class="image-upload-wrapper">
                            <input type="file" class="form-input-file" id="variant_gallery_${variantCount}" name="variants[${variantCount}][gallery_images][]" accept="image/*" multiple onchange="previewVariantGalleryImages(event, ${variantCount})" hidden>
                            <label for="variant_gallery_${variantCount}" class="upload-label">
                                <div class="upload-icon">🖼️</div>
                                <div class="upload-text">Upload multiple variant images</div>
                                <div class="upload-hint">Select multiple files</div>
                            </label>
                        </div>
                        <div class="gallery-preview" id="variantGalleryPreview-${variantCount}"></div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', variantHtml);
    }

    function removeVariant(id) {
        const variant = document.getElementById(`variant-${id}`);
        if (variant) {
            // Clear SKU validation state for this variant
            delete skuValidationStates[`variant_${id}`];
            delete skuValidationTimers[`variant_${id}`];

            variant.remove();
            const remaining = document.querySelectorAll('.variant-card').length;
            if (remaining === 0) {
                document.getElementById('variantsInfo').classList.remove('show');
            } else {
                document.getElementById('variantsInfoText').textContent = `✓ ${remaining} variant(s) ready`;
            }
        }
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
                div.innerHTML = `<img src="${e.target.result}" class="preview-image"><button type="button" class="remove-image" onclick="removeVariantBaseImage(${variantId})">×</button>`;
                container.appendChild(div);
            }
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
        const files = Array.from(input.files);
        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `<img src="${e.target.result}" class="preview-image"><button type="button" class="remove-image" onclick="removeVariantGalleryImage(${variantId}, ${index})">×</button>`;
                container.appendChild(div);
            }
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
