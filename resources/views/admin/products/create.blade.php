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
                <button type="button" class="tab-btn" data-tab="pricing">
                    <span class="tab-icon">💰</span>
                    <span class="tab-text">Pricing</span>
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
                            <input type="text" class="form-input" name="name" value="{{ old('name') }}" placeholder="e.g., LED Bulb 9W" required>
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
                            <label class="form-label">Brand <span class="required">*</span></label>
                            <input type="text" class="form-input" name="brand" value="{{ old('brand') }}" placeholder="Enter brand name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Body Type <span class="required">*</span></label>
                            <input type="text" class="form-input" name="body_type" value="{{ old('body_type') }}" placeholder="e.g., PVC, Metal, Plastic" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Warranty <span class="required">*</span></label>
                            <input type="text" class="form-input" name="warranty" value="{{ old('warranty') }}" placeholder="e.g., 1 Year" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">SKU Code <span class="required">*</span></label>
                            <input type="text" class="form-input" name="sku_code" value="{{ old('sku_code') }}" placeholder="e.g., LED-9W-001" required>
                            <span class="form-hint">Unique product identifier</span>
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

                <!-- Tab 2: Pricing -->
                <div class="tab-content" id="pricing">
                    <div class="tab-header">
                        <h3 class="tab-title">Pricing & Tax</h3>
                        <p class="tab-description">Set pricing and tax details</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">MRP Price (₹) <span class="required">*</span></label>
                            <input type="number" step="0.01" class="form-input" name="mrp_price" value="{{ old('mrp_price') }}" placeholder="0.00" required>
                            <span class="form-hint">Maximum Retail Price</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Selling Price (₹) <span class="required">*</span></label>
                            <input type="number" step="0.01" class="form-input" name="price" id="sellingPrice" value="{{ old('price') }}" placeholder="0.00" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">HSN Code <span class="required">*</span></label>
                            <input type="text" class="form-input" name="hsn_code" value="{{ old('hsn_code') }}" placeholder="85395000" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">GST (%) <span class="required">*</span></label>
                            <input type="number" step="0.01" class="form-input" name="gst" id="gstRate" value="{{ old('gst') }}" placeholder="18" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Opening Stock</label>
                            <input type="number" class="form-input" name="opening_stock" value="{{ old('opening_stock', 0) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Min Stock Alert</label>
                            <input type="number" class="form-input" name="min_stock_alert" value="{{ old('min_stock_alert', 10) }}">
                            <span class="form-hint">Alert when stock is low</span>
                        </div>

                        <div class="form-group full-width">
                            <div class="price-summary">
                                <div class="summary-item">
                                    <span class="summary-label">Base Price:</span>
                                    <span class="summary-value" id="basePriceDisplay">₹0.00</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">GST Amount:</span>
                                    <span class="summary-value" id="gstAmountDisplay">₹0.00</span>
                                </div>
                                <div class="summary-item highlight">
                                    <span class="summary-label">Final Price:</span>
                                    <span class="summary-value" id="finalPriceDisplay">₹0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Images -->
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

                <!-- Tab 4: Variants -->
                <div class="tab-content" id="variants">
                    <div class="tab-header">
                        <h3 class="tab-title">Product Variants</h3>
                        <p class="tab-description">Add multiple variants of this product</p>
                    </div>

                    <div class="variants-section">
                        <button type="button" class="btn-add-variant" onclick="addVariant()">
                            <span>+</span> Add Variant
                        </button>

                        <div id="variantsContainer">
                            <!-- Variants will be added here dynamically -->
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

    /* Alert Styles */
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes slideOut { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(100px); } }
    .alert.removing { animation: slideOut 0.3s ease; }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }
    .alert-icon { font-size: 24px; }

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
    .form-input.error, .form-textarea.error, .form-select.error { border-color: #fc8181; }
    .form-textarea { min-height: 120px; resize: vertical; }
    .form-hint { margin-top: 6px; font-size: 12px; color: #a0aec0; }

    .price-summary { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); padding: 20px; border-radius: 12px; border: 2px solid #e2e8f0; }
    .summary-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0; }
    .summary-item:last-child { border-bottom: none; }
    .summary-item.highlight { padding-top: 15px; margin-top: 10px; border-top: 2px solid #cbd5e0; }
    .summary-label { font-size: 14px; color: #718096; font-weight: 600; }
    .summary-value { font-size: 16px; color: #2d3748; font-weight: 700; }
    .summary-item.highlight .summary-value { color: #ff6b35; font-size: 18px; }

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

    /* Variants Section */
    .variants-section { margin-top: 20px; }
    .btn-add-variant { padding: 12px 24px; background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(72,187,120,0.3); display: flex; align-items: center; gap: 8px; margin-bottom: 25px; }
    .btn-add-variant:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(72,187,120,0.4); }

    #variantsContainer { display: flex; flex-direction: column; gap: 25px; }
    .variant-card { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); padding: 30px; border-radius: 16px; border: 2px solid #e2e8f0; position: relative; }
    .variant-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #cbd5e0; }
    .variant-title { font-size: 18px; font-weight: 700; color: #2d3748; }
    .btn-remove-variant { padding: 8px 16px; background: #fc8181; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .btn-remove-variant:hover { background: #f56565; transform: scale(1.05); }

    .variant-section-title { font-size: 15px; font-weight: 700; color: #2d3748; margin: 20px 0 15px; padding-bottom: 8px; border-bottom: 2px solid #cbd5e0; }

    .form-actions-fixed { position: fixed; bottom: 0; left: 260px; right: 0; background: white; padding: 20px 35px; box-shadow: 0 -4px 20px rgba(0,0,0,0.1); z-index: 100; }
    .form-actions-content { display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto; }
    .action-buttons-right { display: flex; gap: 15px; margin-left: auto; }
    .btn-back, .btn-next { padding: 12px 24px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
    .btn-back:hover, .btn-next:hover { background: #cbd5e0; transform: translateY(-2px); }
    .btn-primary { padding: 12px 30px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(255,107,53,0.3); display: flex; align-items: center; gap: 8px; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.4); }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
        .form-actions-fixed { left: 0; }
        #alertContainer { right: 20px; left: 20px; max-width: none; }
    }
</style>
@endpush

@push('scripts')
<script>
    let currentTab = 0;
    const tabs = ['basic-info', 'pricing', 'media', 'variants'];
    let variantCount = 0;

    // Show alert function
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <span class="alert-icon">${type === 'success' ? '✓' : '✕'}</span>
            <span>${message}</span>
        `;
        container.appendChild(alert);

        setTimeout(() => {
            alert.classList.add('removing');
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    }

    // Tab navigation
    function showTab(index) {
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

        document.getElementById(tabs[index]).classList.add('active');
        document.querySelectorAll('.tab-btn')[index].classList.add('active');

        // Mark previous tabs as completed
        document.querySelectorAll('.tab-btn').forEach((btn, i) => {
            if (i < index) btn.classList.add('completed');
            else btn.classList.remove('completed');
        });

        // Button visibility
        const backBtn = document.getElementById('backBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');

        backBtn.style.display = index === 0 ? 'none' : 'flex';
        nextBtn.style.display = index === tabs.length - 1 ? 'none' : 'flex';
        submitBtn.style.display = index === tabs.length - 1 ? 'flex' : 'none';
    }

    // Validate current tab
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

        if (firstErrorField) {
            firstErrorField.focus();
        }

        return isValid;
    }

    // Next button
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

    // Back button
    document.getElementById('backBtn').addEventListener('click', () => {
        if (currentTab > 0) {
            currentTab--;
            showTab(currentTab);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // Tab click navigation
    document.querySelectorAll('.tab-btn').forEach((btn, index) => {
        btn.addEventListener('click', () => {
            currentTab = index;
            showTab(currentTab);
        });
    });

    // Form submission
    document.getElementById('productForm').addEventListener('submit', function(e) {
        e.preventDefault();

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
            // Find first tab with error
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

        // If all valid, submit form
        this.submit();
    });

    // Price calculation
    function calculatePrice() {
        const selling = parseFloat(document.getElementById('sellingPrice')?.value) || 0;
        const gst = parseFloat(document.getElementById('gstRate')?.value) || 0;

        const base = selling / (1 + gst/100);
        const gstAmount = selling - base;

        document.getElementById('basePriceDisplay').textContent = '₹' + base.toFixed(2);
        document.getElementById('gstAmountDisplay').textContent = '₹' + gstAmount.toFixed(2);
        document.getElementById('finalPriceDisplay').textContent = '₹' + selling.toFixed(2);
    }

    document.getElementById('sellingPrice')?.addEventListener('input', calculatePrice);
    document.getElementById('gstRate')?.addEventListener('input', calculatePrice);

    // Base image preview
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
                div.innerHTML = `
                    <img src="${e.target.result}" class="preview-image">
                    <button type="button" class="remove-image" onclick="removeBaseImage()">×</button>
                `;
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

    // Gallery images preview
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
                div.innerHTML = `
                    <img src="${e.target.result}" class="preview-image">
                    <button type="button" class="remove-image" onclick="removeGalleryImage(${index})">×</button>
                `;
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

    // Add Variant Function
    function addVariant() {
        variantCount++;
        const container = document.getElementById('variantsContainer');

        const variantHtml = `
            <div class="variant-card" id="variant-${variantCount}">
                <div class="variant-header">
                    <h4 class="variant-title">Variant #${variantCount}</h4>
                    <button type="button" class="btn-remove-variant" onclick="removeVariant(${variantCount})">
                        Remove Variant
                    </button>
                </div>

                <div class="variant-section-title">Variant Details</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Watt</label>
                        <input type="text" class="form-input" name="variants[${variantCount}][watt]" placeholder="e.g., 9W">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Shape</label>
                        <input type="text" class="form-input" name="variants[${variantCount}][shape]" placeholder="e.g., Round">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Color Temperature</label>
                        <select class="form-select" name="variants[${variantCount}][color_temperature]">
                            <option value="">Select</option>
                            <option value="WW">WW (Warm White)</option>
                            <option value="NW">NW (Natural White)</option>
                            <option value="WH">WH (White)</option>
                            <option value="3IN1">3IN1</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cutting/Size</label>
                        <input type="text" class="form-input" name="variants[${variantCount}][cutting_size]" placeholder="e.g., 75mm">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-input" name="variants[${variantCount}][unit]" placeholder="Piece, Box, Set">
                    </div>

                    <div class="form-group">
                        <label class="form-label">SKU Code <span class="required">*</span></label>
                        <input type="text" class="form-input" name="variants[${variantCount}][sku_code]" placeholder="e.g., LED-9W-WW-001" required>
                        <span class="form-hint">Unique variant identifier</span>
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
                        <label class="form-label">Cost Price (₹)</label>
                        <input type="number" step="0.01" class="form-input" name="variants[${variantCount}][cost_price]" placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Dealer Price (₹)</label>
                        <input type="number" step="0.01" class="form-input" name="variants[${variantCount}][dealer_price]" placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Distributor Price (₹)</label>
                        <input type="number" step="0.01" class="form-input" name="variants[${variantCount}][distributor_price]" placeholder="0.00">
                    </div>
                </div>

                <div class="variant-section-title">Stock Management</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Opening Stock</label>
                        <input type="number" class="form-input" name="variants[${variantCount}][opening_stock]" value="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Min Stock Alert</label>
                        <input type="number" class="form-input" name="variants[${variantCount}][min_stock_alert]" value="10">
                    </div>
                </div>

                <div class="variant-section-title">Variant Images</div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Variant Base Image</label>
                        <div class="image-upload-wrapper">
                            <input type="file" class="form-input-file" id="variant_base_${variantCount}"
                                name="variants[${variantCount}][base_image]" accept="image/*"
                                onchange="previewVariantBaseImage(event, ${variantCount})" hidden>
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
                            <input type="file" class="form-input-file" id="variant_gallery_${variantCount}"
                                name="variants[${variantCount}][gallery_images][]" accept="image/*" multiple
                                onchange="previewVariantGalleryImages(event, ${variantCount})" hidden>
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

    // Remove Variant Function
    function removeVariant(id) {
        const variant = document.getElementById(`variant-${id}`);
        if (variant) {
            variant.remove();
        }
    }

    // Preview Variant Base Image
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
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Remove Variant Base Image
    function removeVariantBaseImage(variantId) {
        document.getElementById(`variant_base_${variantId}`).value = '';
        document.getElementById(`variantBasePreview-${variantId}`).innerHTML = '';
    }

    // Preview Variant Gallery Images
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
                div.innerHTML = `
                    <img src="${e.target.result}" class="preview-image">
                    <button type="button" class="remove-image" onclick="removeVariantGalleryImage(${variantId}, ${index})">×</button>
                `;
                container.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }

    // Remove Variant Gallery Image
    function removeVariantGalleryImage(variantId, imageIndex) {
        const input = document.getElementById(`variant_gallery_${variantId}`);
        const files = Array.from(input.files);
        files.splice(imageIndex, 1);

        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;

        previewVariantGalleryImages({ target: input }, variantId);
    }

    // Show Laravel validation errors
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

    // Initialize
    showTab(0);
</script>
@endpush
@endsection
