@extends('layouts.admin')

@section('title', 'Create Simple Product - Admin Panel')
@section('header-title', 'Create Simple Product')

@section('content')
<div class="create-product-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Add Simple Product</h2>
        </div>
        <div class="header-right">
            <a href="{{ url('/admin/products') }}" class="back-btn">← Back to Products</a>
        </div>
    </div>

    <form action="{{ route('admin.products.store-simple') }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf

        <!-- Tab Navigation -->
        <div class="tab-container">
            <div class="tab-nav">
                <button type="button" class="tab-btn active" data-tab="basic-info">
                    📝 Basic Info
                </button>
                <button type="button" class="tab-btn" data-tab="pricing">
                    💰 Pricing
                </button>
                <button type="button" class="tab-btn" data-tab="media">
                    🖼️ Images
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content-wrapper">
                <!-- Tab 1: Basic Information -->
                <div class="tab-content active" id="basic-info">
                    <div class="tab-header">
                        <h3 class="tab-title">Basic Product Information</h3>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Product Name <span class="required">*</span></label>
                            <input type="text" class="form-input" id="productName" name="name" value="{{ old('name') }}" placeholder="e.g., LED Bulb 9W" required>
                            @error('name')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Warehouse <span class="required">*</span></label>
                            @php
                                $mainWarehouse = \App\Models\Warehouse::main()->first();
                            @endphp
                            @if($mainWarehouse)
                                <input type="text" class="form-input" value="{{ $mainWarehouse->name }} (Main Warehouse)" readonly style="background-color: #f0f8ff; color: #0066cc; font-weight: 500;">
                                <div class="warehouse-note">
                                    📍 Products are always added to Main Warehouse
                                </div>
                            <input type="hidden" name="warehouse_id" value="{{ $mainWarehouse->id }}">
                            @else
                            <div class="alert alert-warning" style="padding: 8px; background: #fff3cd; color: #856404; border-radius: 4px; font-size: 11px;">
                                ⚠️ Main warehouse not found. Please create a main warehouse first.
                            </div>
                            @endif
                            @error('warehouse_id')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
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
                            @error('brand')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Body Type <span class="required">*</span></label>
                            <input type="text" class="form-input" name="body_type" value="{{ old('body_type') }}" placeholder="e.g., PVC, Metal, Plastic" required>
                            @error('body_type')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
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

                        <div class="form-group">
                            <label class="form-label">SKU Code <span class="required">*</span></label>
                            <input type="text" class="form-input" id="main_sku_code" name="sku_code" value="{{ old('sku_code') }}" placeholder="e.g., LED-9W-001" maxlength="16" required onkeyup="validateSkuCode(this.value)">
                            <div class="sku-validation-message" id="sku_message"></div>
                            @error('sku_code')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Barcode Symbology <span class="required">*</span></label>
                            <select class="form-select" name="barcode_symbology" required>
                                <option value="CODE128" {{ old('barcode_symbology') == 'CODE128' ? 'selected' : '' }}>Code 128</option>
                            </select>
                            @error('barcode_symbology')
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

                        <!-- GST Field (Variant page jaisa) -->
                        <div class="form-group">
                            <label class="form-label">GST (%) <span class="required">*</span></label>
                            <input type="number" step="0.01" class="form-input" name="gst"
                                value="{{ old('gst') }}"
                                placeholder="18" min="0" max="100" required
                                oninput="validateGST(this, 'gst-error-simple-create')">
                            <div class="error-message" id="gst-error-simple-create"></div>
                            @error('gst')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>



                        <div class="form-group full-width">
                            <label class="form-label">Description <span class="required">*</span></label>
                            <textarea class="form-textarea" name="description" rows="4" placeholder="Enter product description with features and specifications" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Pricing -->
                <div class="tab-content" id="pricing">
                    <div class="tab-header">
                        <h3 class="tab-title">Pricing & Stock Information</h3>
                    </div>

                    <div class="variant-section-title">Pricing</div>
                    <div class="form-grid three-columns">
                        <div class="form-group">
                            <label class="form-label">Cost Price (₹) <span class="required">*</span></label>
                            <input type="number" step="0.01" class="form-input" id="cost_price" name="cost_price" value="{{ old('cost_price') }}" placeholder="0.00" min="0" required oninput="validatePricing()">
                            @error('cost_price')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Sale Price (₹) <span class="required">*</span></label>
                            <input type="number" step="0.01" class="form-input sale-price" id="sale_price" name="sale_price" value="{{ old('sale_price') }}" placeholder="0.00" min="0" required oninput="validatePricing()">
                            <div class="price-message" id="sale_price_msg"></div>
                            @error('sale_price')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">MRP Price (₹) <span class="required">*</span></label>
                            <input type="number" step="0.01" class="form-input mrp-price" id="mrp_price" name="mrp_price" value="{{ old('mrp_price') }}" placeholder="0.00" min="0" required oninput="validatePricing()">
                            <div class="price-message" id="mrp_price_msg"></div>
                            @error('mrp_price')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Dealer Price (₹) <span class="required">*</span></label>
                            <input type="number" step="0.01" class="form-input" id="dealer_price" name="dealer_price" value="{{ old('dealer_price') }}" placeholder="0.00" min="0" required>
                            @error('dealer_price')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Distributor Price (₹) <span class="required">*</span></label>
                            <input type="number" step="0.01" class="form-input" id="distributor_price" name="distributor_price" value="{{ old('distributor_price') }}" placeholder="0.00" min="0" required>
                            @error('distributor_price')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit <span class="required">*</span></label>
                            <select class="form-select" name="unit" required>
                                <option value="" {{ old('unit') == '' ? 'selected' : '' }}>Select Unit</option>
                                <option value="piece" {{ old('unit') == 'piece' ? 'selected' : '' }}>Piece</option>
                                <option value="set" {{ old('unit') == 'set' ? 'selected' : '' }}>Set</option>
                                <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box</option>
                                <option value="meter" {{ old('unit') == 'meter' ? 'selected' : '' }}>Meter</option>
                                <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogram</option>
                                <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>Liter</option>
                                <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>Pack</option>
                                <option value="dozen" {{ old('unit') == 'dozen' ? 'selected' : '' }}>Dozen</option>
                                <option value="roll" {{ old('unit') == 'roll' ? 'selected' : '' }}>Roll</option>
                                <option value="sheet" {{ old('unit') == 'sheet' ? 'selected' : '' }}>Sheet</option>
                            </select>
                            @error('unit')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="variant-section-title">Stock Management</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Opening Stock <span class="required">*</span></label>
                            <input type="number" class="form-input" id="opening_stock" name="opening_stock" value="{{ old('opening_stock') }}" min="0" required>
                            @error('opening_stock')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Min Stock Alert <span class="required">*</span></label>
                            <input type="number" class="form-input" id="min_stock_alert" name="min_stock_alert" value="{{ old('min_stock_alert') }}" min="0" required>
                            @error('min_stock_alert')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                </div>

                <!-- Tab 3: Images -->
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
                                    <div class="upload-hint">PNG, JPG up to 2MB</div>
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
                        ✓ Create Product
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

    /* Header - matched to variant create */
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
        font-size: 12px;
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

    /* Tab Container - matched to variant create */
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

    /* Form Grid - matched to variant create */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 15px;
    }

    .form-grid.three-columns {
        grid-template-columns: repeat(3, 1fr);
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

    /* Inputs - matched to variant create */
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

    /* SKU Validation - matched to variant create */
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

    .sku-validation-message.checking {
        display: block;
        background: #cce5ff;
        color: #004085;
        border-left: 2px solid #007bff;
    }

    .form-input.sku-error {
        border-color: #dc3545 !important;
        background: #fff5f5 !important;
    }

    .form-input.sku-success {
        border-color: #28a745 !important;
        background: #d4edda !important;
    }

    .form-input.sku-checking {
        border-color: #007bff !important;
        background: #cce5ff !important;
    }

    /* Section Titles - same as variant cards */
    .variant-section-title {
        font-size: 11px;
        font-weight: 600;
        color: #495057;
        margin: 12px 0 6px;
        padding-bottom: 4px;
        border-bottom: 1px solid #dee2e6;
    }

    /* Price Validation Messages - matched exactly to variant create */
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


    /* Image Upload - matched to variant create */
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

    /* Form Actions - matched exactly to variant create */
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
        font-size: 11px;
    }

    .btn-back:hover, .btn-next:hover {
        background: #e07020;
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
        font-size: 11px;
    }

    .btn-primary:hover {
        background: #218838;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-grid.three-columns { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .header-right { width: 100%; }
        .back-btn { width: 100%; text-align: center; }
    }
</style>
@endpush

@push('scripts')
<script>
    // ========== GLOBAL VARIABLES ==========
    let currentTab = 0;
    const tabs = ['basic-info', 'pricing', 'media'];
    let skuValidationTimer = null;
    let skuValidationState = null;

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

    // ========== GST VALIDATION (Variant page jaisa) ==========
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

    // ========== SKU VALIDATION ==========
    function validateSkuCode(skuCode) {
        if (skuValidationTimer) {
            clearTimeout(skuValidationTimer);
        }

        const inputField = document.getElementById('main_sku_code');
        const messageDiv = document.getElementById('sku_message');

        // Reset
        inputField.classList.remove('sku-error', 'sku-success', 'sku-checking');
        messageDiv.className = 'sku-validation-message';
        messageDiv.textContent = '';

        if (!skuCode || skuCode.trim() === '') {
            skuValidationState = null;
            return;
        }

        // Show checking state
        inputField.classList.add('sku-checking');
        messageDiv.className = 'sku-validation-message checking';
        messageDiv.textContent = '🔍 Checking SKU availability...';

        skuValidationTimer = setTimeout(() => {
            fetch('{{ route("admin.products.check-sku") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    sku_code: skuCode.trim(),
                    product_id: null,
                    product_type: 'simple'
                })
            })
            .then(response => response.json())
            .then(data => {
                inputField.classList.remove('sku-checking');

                if (data.available) {
                    inputField.classList.add('sku-success');
                    messageDiv.className = 'sku-validation-message success';
                    messageDiv.textContent = data.message;
                    skuValidationState = true;
                } else {
                    inputField.classList.add('sku-error');
                    messageDiv.className = 'sku-validation-message error';
                    messageDiv.textContent = data.message;
                    skuValidationState = false;
                }
            })
            .catch(error => {
                console.error('SKU validation error:', error);
                inputField.classList.remove('sku-checking');
                messageDiv.className = 'sku-validation-message';
                messageDiv.textContent = '';
                skuValidationState = null;
            });
        }, 500);
    }

    // ========== PRICE VALIDATION (matched to variant create logic) ==========
    function validatePricing() {
        const costPrice = parseFloat(document.getElementById('cost_price').value) || 0;
        const salePrice = parseFloat(document.getElementById('sale_price').value) || 0;
        const mrpPrice = parseFloat(document.getElementById('mrp_price').value) || 0;

        const saleInput = document.getElementById('sale_price');
        const mrpInput = document.getElementById('mrp_price');
        const salePriceMsg = document.getElementById('sale_price_msg');
        const mrpPriceMsg = document.getElementById('mrp_price_msg');

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

    // ========== TAB VALIDATION ==========
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


    // ========== INITIALIZE ==========
    document.addEventListener('DOMContentLoaded', function() {
        showTab(0);




        // Auto-validate SKU if already filled (old value)
        const skuInput = document.getElementById('main_sku_code');
        if (skuInput.value.trim()) {
            validateSkuCode(skuInput.value);
        }

        // Tab buttons click
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
            if (!validateCurrentTab()) return;

            // Price validation on pricing tab
            if (currentTab === 1) {
                if (!validatePricing()) {
                    showAlert('Please correct the pricing values', 'error');
                    return;
                }
            }

            if (currentTab < tabs.length - 1) {
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
            e.preventDefault();

            // SKU validation check
            if (skuValidationState === false) {
                showAlert('Please fix the SKU code error before submitting', 'error');
                document.getElementById('main_sku_code').focus();
                return;
            }

            if (skuValidationState === null) {
                const skuCode = document.getElementById('main_sku_code').value;
                if (skuCode && skuCode.trim()) {
                    showAlert('Please wait for SKU validation to complete', 'error');
                    return;
                }
            }

            // Price validation
            if (!validatePricing()) {
                currentTab = 1;
                showTab(currentTab);
                showAlert('Please correct the pricing values', 'error');
                return;
            }

            // Check all required
            let allValid = true;
            for (let i = 0; i < tabs.length; i++) {
                const tabElement = document.getElementById(tabs[i]);
                const requiredInputs = tabElement.querySelectorAll('[required]');
                requiredInputs.forEach(input => {
                    input.classList.remove('error');
                    if (!input.value.trim()) {
                        input.classList.add('error');
                        allValid = false;
                    }
                });
                if (!allValid) {
                    currentTab = i;
                    showTab(i);
                    showAlert('Please fill in all required fields', 'error');
                    return;
                }
            }


            // Disable button and submit
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '⏳ Creating...';
            submitBtn.disabled = true;

            this.submit();
        });

        // Show Laravel errors / success
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
    });
</script>
@endpush
@endsection
