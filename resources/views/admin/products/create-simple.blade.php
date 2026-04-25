@extends('layouts.admin')

@section('title', 'Create Simple Product - Admin Panel')
@section('header-title', 'Create Simple Product')

@section('content')
<div class="create-product-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- ========== HSN SEARCH MODAL ========== -->
    <div class="hsn-modal-overlay" id="hsnModal" style="display:none;">
        <div class="hsn-modal">
            <div class="hsn-modal-header">
                <h3 class="hsn-modal-title">🔍 HSN Code Search</h3>
                <button type="button" class="hsn-modal-close" onclick="closeHsnModal()">✕</button>
            </div>
            <div class="hsn-modal-body">
                <div class="hsn-search-bar">
                    <input type="text" id="hsnSearchInput" class="hsn-search-input" placeholder="Search HSN code... e.g., wheat, rice, bulb, LED" oninput="searchHsn(this.value)">
                    <button type="button" class="hsn-search-btn" onclick="searchHsn(document.getElementById('hsnSearchInput').value)">Search</button>
                </div>
                <div id="hsnSearchResults" class="hsn-results-container">
                    <div class="hsn-results-placeholder">
                        <div style="font-size:32px; margin-bottom:8px;">🔍</div>
                        <p>Type to search HSN codes</p>
                        <small>e.g., wheat, rice, LED bulb, plastic</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ========== END HSN MODAL ========== -->

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
                            <input type="number" class="form-input" name="warranty_duration" value="{{ old('warranty_duration') }}" placeholder="e.g., 12" >
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

                        {{-- ===== HSN CODE FIELD ===== --}}
                        <div class="form-group">
                            <label class="form-label">
                                HSN Code <span class="required">*</span>
                                <button type="button" class="hsn-search-trigger" onclick="openHsnModal()">🔍 Search HSN</button>
                            </label>
                            <div class="hsn-input-wrapper">
                                <input type="text"
                                       class="form-input"
                                       id="hsn_code"
                                       name="hsn_code"
                                       value="{{ old('hsn_code') }}"
                                       placeholder="e.g., 85395000"
                                       maxlength="8"
                                       required
                                       oninput="onHsnCodeInput(this.value)">
                                <div class="hsn-loading-spinner" id="hsnLoadingSpinner" style="display:none;">⏳</div>
                            </div>
                            <div class="hsn-status-message" id="hsnStatusMsg"></div>
                            @error('hsn_code')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ===== GST FIELD (auto-filled from HSN) ===== --}}
                        <div class="form-group">
                            <label class="form-label">GST (%) <span class="required">*</span></label>
                            <div class="gst-input-wrapper">
                                <input type="number" step="0.01" class="form-input" id="gst_field" name="gst"
                                    value="{{ old('gst') }}"
                                    placeholder="Auto-filled from HSN" min="0" max="100" required
                                    oninput="validateGST(this, 'gst-error-simple-create')">
                                <span class="gst-auto-badge" id="gstAutoBadge" style="display:none;">✓ Auto</span>
                            </div>
                            <div class="error-message" id="gst-error-simple-create"></div>
                            @error('gst')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Description </label>
                            <textarea class="form-textarea" name="description" rows="4" placeholder="Enter product description with features and specifications">{{ old('description') }}</textarea>
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
                            <label class="form-label">Main Image </label>
                            <div class="image-upload-wrapper">
                                <input type="file" class="form-input-file" id="base_image" name="base_image" accept="image/*" onchange="previewBaseImage(event)" hidden>
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

    .alert-success { background: #d4edda; color: #155724; border-left: 3px solid #28a745; }
    .alert-error { background: #f8d7da; color: #721c24; border-left: 3px solid #dc3545; }

    .page-header {
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #dee2e6;
    }
    .header-left { display: flex; align-items: center; gap: 10px; }
    .header-right { display: flex; align-items: center; }
    .back-btn {
        color: #fa8128; text-decoration: none; font-size: 14px; font-weight: 500;
        padding: 5px 10px; border-radius: 4px; transition: all 0.2s;
        border: 1px solid #dee2e6; background: white;
    }
    .back-btn:hover { background: #fff0e6; }
    .page-title { font-size: 16px; font-weight: 600; color: #343a40; margin: 0; }

    .tab-container {
        background: white; border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden; border: 1px solid #dee2e6;
    }
    .tab-nav { display: flex; background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 0; }
    .tab-btn {
        flex: 1; padding: 10px 12px; background: none; border: none;
        border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.2s;
        font-size: 11px; font-weight: 500; color: #6c757d; text-align: center;
    }
    .tab-btn:hover { background: #e9ecef; color: #495057; }
    .tab-btn.active { background: white; color: #007bff; border-bottom-color: #007bff; font-weight: 600; }
    .tab-content-wrapper { padding: 15px; }
    .tab-content { display: none; }
    .tab-content.active { display: block; animation: fadeIn 0.2s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .tab-header { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #dee2e6; }
    .tab-title { font-size: 14px; font-weight: 600; color: #343a40; margin: 0; }

    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 15px; }
    .form-grid.three-columns { grid-template-columns: repeat(3, 1fr); }
    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: span 2; }
    .form-label { margin-bottom: 5px; font-weight: 500; color: #495057; font-size: 11px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .required { color: #dc3545; }

    .form-input, .form-textarea, .form-select {
        padding: 6px 10px; border: 1px solid #ced4da; border-radius: 4px;
        font-size: 12px; transition: all 0.2s; font-family: inherit;
        background: white; height: 32px;
    }
    .form-input:focus, .form-textarea:focus, .form-select:focus {
        outline: none; border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    .form-input.error, .form-textarea.error, .form-select.error { border-color: #dc3545; background: #fff5f5; }
    .form-textarea { min-height: 70px; resize: vertical; height: auto; }
    .error-message { color: #dc3545; font-size: 10px; font-weight: 500; margin-top: 3px; }

    /* ===== HSN SEARCH TRIGGER BUTTON ===== */
    .hsn-search-trigger {
        padding: 2px 7px; background: #e3f2fd; color: #1565c0;
        border: 1px solid #bbdefb; border-radius: 10px; font-size: 9px;
        font-weight: 600; cursor: pointer; transition: all 0.2s;
        white-space: nowrap;
    }
    .hsn-search-trigger:hover { background: #bbdefb; }

    /* ===== HSN INPUT WRAPPER ===== */
    .hsn-input-wrapper { position: relative; display: flex; align-items: center; }
    .hsn-input-wrapper .form-input { flex: 1; padding-right: 28px; }
    .hsn-loading-spinner { position: absolute; right: 8px; font-size: 12px; }

    .hsn-status-message { font-size: 9px; margin-top: 3px; padding: 3px 6px; border-radius: 3px; display: none; }
    .hsn-status-message.success { display: block; background: #d4edda; color: #155724; border-left: 2px solid #28a745; }
    .hsn-status-message.error { display: block; background: #f8d7da; color: #721c24; border-left: 2px solid #dc3545; }
    .hsn-status-message.info { display: block; background: #cce5ff; color: #004085; border-left: 2px solid #007bff; }

    /* ===== GST AUTO BADGE ===== */
    .gst-input-wrapper { position: relative; display: flex; align-items: center; gap: 6px; }
    .gst-input-wrapper .form-input { flex: 1; }
    .gst-auto-badge {
        font-size: 9px; font-weight: 700; color: #155724; background: #d4edda;
        border: 1px solid #28a745; border-radius: 8px; padding: 2px 6px; white-space: nowrap;
    }

    /* ===== HSN MODAL ===== */
    .hsn-modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.5);
        z-index: 99999; display: flex; align-items: center; justify-content: center;
        padding: 15px;
    }
    .hsn-modal {
        background: white; border-radius: 8px; width: 100%; max-width: 600px;
        max-height: 80vh; display: flex; flex-direction: column;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .hsn-modal-header {
        padding: 14px 16px; border-bottom: 1px solid #dee2e6;
        display: flex; align-items: center; justify-content: space-between;
        background: #f8f9fa; border-radius: 8px 8px 0 0;
    }
    .hsn-modal-title { font-size: 14px; font-weight: 700; color: #343a40; margin: 0; }
    .hsn-modal-close {
        width: 28px; height: 28px; border: none; background: #dc3545; color: white;
        border-radius: 50%; cursor: pointer; font-size: 12px; font-weight: bold;
        display: flex; align-items: center; justify-content: center;
    }
    .hsn-modal-close:hover { background: #c82333; }
    .hsn-modal-body { padding: 14px; flex: 1; overflow: hidden; display: flex; flex-direction: column; }

    .hsn-search-bar { display: flex; gap: 8px; margin-bottom: 12px; }
    .hsn-search-input {
        flex: 1; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px;
        font-size: 12px; font-family: inherit;
    }
    .hsn-search-input:focus { outline: none; border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25); }
    .hsn-search-btn {
        padding: 8px 16px; background: #007bff; color: white; border: none;
        border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer;
    }
    .hsn-search-btn:hover { background: #0069d9; }

    .hsn-results-container { flex: 1; overflow-y: auto; }
    .hsn-results-placeholder { text-align: center; padding: 30px 12px; color: #6c757d; font-size: 12px; }

    /* HSN Result Groups */
    .hsn-group { margin-bottom: 10px; border: 1px solid #e9ecef; border-radius: 6px; overflow: hidden; }
    .hsn-group-header {
        padding: 8px 12px; background: #f8f9fa; border-bottom: 1px solid #e9ecef;
        cursor: pointer; display: flex; align-items: flex-start; gap: 8px;
    }
    .hsn-group-header:hover { background: #e9ecef; }
    .hsn-group-code {
        font-size: 11px; font-weight: 700; color: #495057;
        background: #dee2e6; padding: 2px 6px; border-radius: 4px;
        white-space: nowrap; min-width: 50px; text-align: center;
    }
    .hsn-group-desc { font-size: 11px; color: #343a40; flex: 1; line-height: 1.4; }
    .hsn-group-arrow { font-size: 10px; color: #6c757d; margin-left: auto; padding-top: 1px; }

    .hsn-sub-items { display: block; }
    .hsn-sub-item {
        padding: 7px 12px 7px 24px; border-bottom: 1px solid #f1f3f5;
        cursor: pointer; display: flex; align-items: flex-start; gap: 8px;
        transition: background 0.15s;
    }
    .hsn-sub-item:last-child { border-bottom: none; }
    .hsn-sub-item:hover { background: #e3f2fd; }
    .hsn-sub-code {
        font-size: 11px; font-weight: 700; color: #1565c0;
        background: #e3f2fd; padding: 2px 6px; border-radius: 4px;
        white-space: nowrap; min-width: 60px; text-align: center;
    }
    .hsn-sub-desc { font-size: 11px; color: #495057; flex: 1; line-height: 1.4; }
    .hsn-select-btn {
        font-size: 9px; background: #28a745; color: white; border: none;
        border-radius: 10px; padding: 2px 7px; cursor: pointer; white-space: nowrap;
        font-weight: 600;
    }
    .hsn-select-btn:hover { background: #218838; }

    /* Loading state */
    .hsn-loading { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
    .hsn-no-results { text-align: center; padding: 20px; color: #dc3545; font-size: 12px; }

    /* ===== SKU ===== */
    .sku-validation-message { font-size: 9px; margin-top: 3px; padding: 3px 5px; border-radius: 3px; display: none; }
    .sku-validation-message.error { display: block; background: #f8d7da; color: #721c24; border-left: 2px solid #dc3545; }
    .sku-validation-message.success { display: block; background: #d4edda; color: #155724; border-left: 2px solid #28a745; }
    .sku-validation-message.checking { display: block; background: #cce5ff; color: #004085; border-left: 2px solid #007bff; }
    .form-input.sku-error { border-color: #dc3545 !important; background: #fff5f5 !important; }
    .form-input.sku-success { border-color: #28a745 !important; background: #d4edda !important; }
    .form-input.sku-checking { border-color: #007bff !important; background: #cce5ff !important; }

    /* ===== SECTION TITLES ===== */
    .variant-section-title { font-size: 11px; font-weight: 600; color: #495057; margin: 12px 0 6px; padding-bottom: 4px; border-bottom: 1px solid #dee2e6; }

    /* ===== PRICE MESSAGES ===== */
    .price-error { border-color: #dc3545 !important; background: #f8d7da !important; }
    .price-success { border-color: #28a745 !important; background: #d4edda !important; }
    .price-message { font-size: 9px; margin-top: 3px; padding: 3px 5px; border-radius: 3px; display: none; }
    .price-message.error { display: block; background: #f8d7da; color: #721c24; border-left: 2px solid #dc3545; }
    .price-message.success { display: block; background: #d4edda; color: #155724; border-left: 2px solid #28a745; }

    /* ===== IMAGE UPLOAD ===== */
    .image-upload-wrapper { margin-bottom: 8px; }
    .upload-label {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 15px; border: 1px dashed #adb5bd; border-radius: 4px;
        background: #f8f9fa; cursor: pointer; transition: all 0.2s;
    }
    .upload-label:hover { border-color: #007bff; background: #e7f1ff; }
    .upload-icon { font-size: 18px; margin-bottom: 5px; }
    .upload-text { font-size: 11px; font-weight: 500; color: #495057; margin-bottom: 2px; }
    .upload-hint { font-size: 9px; color: #6c757d; }
    .image-preview-container, .gallery-preview { display: grid; grid-template-columns: repeat(auto-fill, minmax(60px, 1fr)); gap: 6px; margin-top: 8px; }
    .preview-item { position: relative; border-radius: 4px; overflow: hidden; border: 1px solid #dee2e6; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .preview-image { width: 100%; height: 60px; object-fit: cover; display: block; }
    .remove-image {
        position: absolute; top: 3px; right: 3px; width: 16px; height: 16px;
        background: #dc3545; color: white; border: none; border-radius: 50%;
        cursor: pointer; font-size: 10px; font-weight: bold;
        display: flex; align-items: center; justify-content: center; transition: all 0.2s; line-height: 1; padding: 0;
    }
    .remove-image:hover { background: #c82333; transform: scale(1.1); }

    /* ===== FORM ACTIONS ===== */
    .form-actions {
        position: fixed; bottom: 0; left: 0; right: 0; background: white;
        padding: 10px 15px; box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 100; border-top: 1px solid #dee2e6;
    }
    .action-buttons { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
    .right-buttons { display: flex; gap: 6px; margin-left: auto; }
    .btn-back, .btn-next {
        padding: 5px 10px; background: #fa8128; color: white; border: none;
        border-radius: 4px; font-weight: 500; cursor: pointer; transition: all 0.2s;
        display: flex; align-items: center; gap: 4px; font-size: 14px;
    }
    .btn-back:hover, .btn-next:hover { background: #e07020; }
    .btn-primary {
        padding: 5px 12px; background: #28a745; color: white; border: none;
        border-radius: 4px; font-weight: 500; cursor: pointer; transition: all 0.2s;
        display: flex; align-items: center; gap: 4px; font-size: 14px;
    }
    .btn-primary:hover { background: #218838; }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-grid.three-columns { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
        .page-header { flex-direction: column; align-items: flex-start; gap: 8px; }
        .header-right { width: 100%; }
        .back-btn { width: 100%; text-align: center; }
        .hsn-modal { max-height: 90vh; }
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
    let hsnSearchTimer = null;
    let hsnFetchTimer = null;

    const FASTGST_API_KEY = 'FGST_TEST_45AA0EQ9KOMKJ4LQ42TCWH7B';
    const FASTGST_BASE = 'https://api.taxlookup.fastgst.in';

    // ========== ALERT SYSTEM ==========
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 3000);
    }

    // ========== GST VALIDATION ==========
    function validateGST(input, errorId) {
        const value = parseFloat(input.value);
        const errorDiv = document.getElementById(errorId);
        if (!errorDiv) return;

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

    // ========== HSN CODE INPUT HANDLER ==========
    function onHsnCodeInput(value) {
        clearTimeout(hsnFetchTimer);

        const statusMsg = document.getElementById('hsnStatusMsg');
        const spinner = document.getElementById('hsnLoadingSpinner');
        const gstBadge = document.getElementById('gstAutoBadge');

        // Reset
        statusMsg.className = 'hsn-status-message';
        statusMsg.textContent = '';
        gstBadge.style.display = 'none';

        const cleaned = value.replace(/\D/g, '');
        if (cleaned.length < 6) return;

        // Auto-fetch GST when HSN is 8 digits or 6 digits
        if (cleaned.length >= 6) {
            spinner.style.display = 'block';
            statusMsg.className = 'hsn-status-message info';
            statusMsg.textContent = '⏳ Fetching GST rate...';

            hsnFetchTimer = setTimeout(() => {
                fetchGstForHsn(cleaned);
            }, 600);
        }
    }

    // ========== FETCH GST FROM HSN CODE ==========
    function fetchGstForHsn(hsnCode) {
        const spinner = document.getElementById('hsnLoadingSpinner');
        const statusMsg = document.getElementById('hsnStatusMsg');
        const gstField = document.getElementById('gst_field');
        const gstBadge = document.getElementById('gstAutoBadge');

        fetch(`${FASTGST_BASE}/search/hsn/${hsnCode}/taxes`, {
            headers: { 'X-API-Key': FASTGST_API_KEY }
        })
        .then(res => res.json())
        .then(data => {
            spinner.style.display = 'none';

            // Try to extract IGST rate (= total GST)
            let gstRate = null;

            if (data && data.data) {
                const d = data.data;
                // IGST = CGST + SGST combined
                if (d.igst !== undefined && d.igst !== null) {
                    gstRate = parseFloat(d.igst);
                } else if (d.cgst !== undefined && d.sgst !== undefined) {
                    gstRate = (parseFloat(d.cgst) + parseFloat(d.sgst));
                } else if (d.tax_rate !== undefined) {
                    gstRate = parseFloat(d.tax_rate);
                } else if (d.gst_rate !== undefined) {
                    gstRate = parseFloat(d.gst_rate);
                }
            }

            // Fallback: look in nested taxes array
            if (gstRate === null && data && data.taxes && Array.isArray(data.taxes)) {
                data.taxes.forEach(tax => {
                    if (tax.type === 'IGST' || tax.name === 'IGST') {
                        gstRate = parseFloat(tax.rate);
                    }
                });
            }

            if (gstRate !== null && !isNaN(gstRate)) {
                gstField.value = gstRate;
                gstBadge.style.display = 'inline-block';
                statusMsg.className = 'hsn-status-message success';
                statusMsg.textContent = `✓ GST ${gstRate}% auto-filled from HSN`;
                // Clear manual error
                document.getElementById('gst-error-simple-create').textContent = '';
            } else {
                statusMsg.className = 'hsn-status-message error';
                statusMsg.textContent = '⚠️ GST rate not found. Please enter manually.';
            }
        })
        .catch(err => {
            spinner.style.display = 'none';
            statusMsg.className = 'hsn-status-message error';
            statusMsg.textContent = '⚠️ Could not fetch GST. Enter manually.';
            console.error('GST fetch error:', err);
        });
    }

    // ========== HSN MODAL ==========
    function openHsnModal() {
        document.getElementById('hsnModal').style.display = 'flex';
        document.getElementById('hsnSearchInput').focus();
    }

    function closeHsnModal() {
        document.getElementById('hsnModal').style.display = 'none';
    }

    // Close modal on overlay click
    document.getElementById('hsnModal').addEventListener('click', function(e) {
        if (e.target === this) closeHsnModal();
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeHsnModal();
    });

    // ========== HSN SEARCH ==========
    let hsnSearchDebounce = null;
    function searchHsn(query) {
        clearTimeout(hsnSearchDebounce);
        const resultsContainer = document.getElementById('hsnSearchResults');

        if (!query || query.trim().length < 2) {
            resultsContainer.innerHTML = `
                <div class="hsn-results-placeholder">
                    <div style="font-size:32px; margin-bottom:8px;">🔍</div>
                    <p>Type at least 2 characters to search</p>
                </div>`;
            return;
        }

        resultsContainer.innerHTML = '<div class="hsn-loading">⏳ Searching HSN codes...</div>';

        hsnSearchDebounce = setTimeout(() => {
            fetch(`${FASTGST_BASE}/search/hsn?query=${encodeURIComponent(query.trim())}`, {
                headers: { 'X-API-Key': FASTGST_API_KEY }
            })
            .then(res => res.json())
            .then(data => {
                renderHsnResults(data, resultsContainer);
            })
            .catch(err => {
                resultsContainer.innerHTML = '<div class="hsn-no-results">❌ Error fetching results. Check your connection.</div>';
                console.error('HSN search error:', err);
            });
        }, 400);
    }

    function renderHsnResults(data, container) {
        container.innerHTML = '';

        // Handle different API response structures
        let items = [];
        if (data && data.data && Array.isArray(data.data)) {
            items = data.data;
        } else if (data && Array.isArray(data)) {
            items = data;
        } else if (data && data.results && Array.isArray(data.results)) {
            items = data.results;
        }

        if (items.length === 0) {
            container.innerHTML = '<div class="hsn-no-results">❌ No HSN codes found for this search.</div>';
            return;
        }

        items.forEach(group => {
            const groupEl = document.createElement('div');
            groupEl.className = 'hsn-group';

            const code = group.code || group.hsn_code || group.heading || '';
            const desc = group.description || group.desc || group.name || '';
            const children = group.children || group.sub_items || group.items || [];

            // Check if this is a leaf node (no children) - directly selectable
            if (children.length === 0) {
                groupEl.innerHTML = `
                    <div class="hsn-group-header" onclick="selectHsnCode('${code}', '${desc.replace(/'/g, "\\'")}')">
                        <span class="hsn-group-code">${code}</span>
                        <span class="hsn-group-desc">${desc}</span>
                        <button type="button" class="hsn-select-btn">Select</button>
                    </div>`;
            } else {
                // Has children - show as expandable group
                const subItemsHtml = children.map(child => {
                    const childCode = child.code || child.hsn_code || '';
                    const childDesc = child.description || child.desc || child.name || '';
                    return `
                        <div class="hsn-sub-item" onclick="selectHsnCode('${childCode}', '${childDesc.replace(/'/g, "\\'")}')">
                            <span class="hsn-sub-code">${childCode}</span>
                            <span class="hsn-sub-desc">${childDesc}</span>
                            <button type="button" class="hsn-select-btn">Select</button>
                        </div>`;
                }).join('');

                groupEl.innerHTML = `
                    <div class="hsn-group-header" onclick="toggleHsnGroup(this)">
                        <span class="hsn-group-code">${code}</span>
                        <span class="hsn-group-desc">${desc}</span>
                        <span class="hsn-group-arrow">▼</span>
                    </div>
                    <div class="hsn-sub-items">${subItemsHtml}</div>`;
            }

            container.appendChild(groupEl);
        });
    }

    function toggleHsnGroup(headerEl) {
        const subItems = headerEl.nextElementSibling;
        const arrow = headerEl.querySelector('.hsn-group-arrow');
        if (subItems) {
            const isOpen = subItems.style.display !== 'none';
            subItems.style.display = isOpen ? 'none' : 'block';
            if (arrow) arrow.textContent = isOpen ? '▶' : '▼';
        }
    }

    function selectHsnCode(code, description) {
        // Fill HSN field
        const hsnInput = document.getElementById('hsn_code');
        hsnInput.value = code;

        // Close modal
        closeHsnModal();

        // Show info and trigger GST fetch
        const statusMsg = document.getElementById('hsnStatusMsg');
        statusMsg.className = 'hsn-status-message info';
        statusMsg.textContent = `Selected: ${code} - ${description}`;

        showAlert(`HSN ${code} selected`, 'success');

        // Auto-fetch GST
        setTimeout(() => fetchGstForHsn(code), 100);
    }

    // ========== TAB MANAGEMENT ==========
    function showTab(index) {
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(tabs[index]).classList.add('active');
        document.querySelectorAll('.tab-btn')[index].classList.add('active');

        document.getElementById('backBtn').style.display = index === 0 ? 'none' : 'flex';
        document.getElementById('nextBtn').style.display = index === tabs.length - 1 ? 'none' : 'flex';
        document.getElementById('submitBtn').style.display = index === tabs.length - 1 ? 'flex' : 'none';
    }

    // ========== SKU VALIDATION ==========
    function validateSkuCode(skuCode) {
        if (skuValidationTimer) clearTimeout(skuValidationTimer);

        const inputField = document.getElementById('main_sku_code');
        const messageDiv = document.getElementById('sku_message');

        inputField.classList.remove('sku-error', 'sku-success', 'sku-checking');
        messageDiv.className = 'sku-validation-message';
        messageDiv.textContent = '';

        if (!skuCode || skuCode.trim() === '') { skuValidationState = null; return; }

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
                body: JSON.stringify({ sku_code: skuCode.trim(), product_id: null, product_type: 'simple' })
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
            .catch(() => {
                inputField.classList.remove('sku-checking');
                messageDiv.className = 'sku-validation-message';
                skuValidationState = null;
            });
        }, 500);
    }

    // ========== PRICE VALIDATION ==========
    function validatePricing() {
        const costPrice = parseFloat(document.getElementById('cost_price').value) || 0;
        const salePrice = parseFloat(document.getElementById('sale_price').value) || 0;
        const mrpPrice = parseFloat(document.getElementById('mrp_price').value) || 0;

        const saleInput = document.getElementById('sale_price');
        const mrpInput = document.getElementById('mrp_price');
        const salePriceMsg = document.getElementById('sale_price_msg');
        const mrpPriceMsg = document.getElementById('mrp_price_msg');

        saleInput.classList.remove('price-error', 'price-success');
        mrpInput.classList.remove('price-error', 'price-success');
        salePriceMsg.className = 'price-message';
        mrpPriceMsg.className = 'price-message';

        let isValid = true;

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

        if (mrpPrice > 0 && mrpPrice < salePrice) {
            mrpInput.classList.add('price-error');
            mrpPriceMsg.className = 'price-message error';
            mrpPriceMsg.textContent = 'MRP must be greater than sale price';
            isValid = false;
        } else if (mrpPrice >= salePrice) {
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

        if (!isValid) showAlert('Fill all required fields', 'error');
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
                div.innerHTML = `<img src="${e.target.result}" class="preview-image"><button type="button" class="remove-image" onclick="removeBaseImage()">×</button>`;
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
                div.innerHTML = `<img src="${e.target.result}" class="preview-image"><button type="button" class="remove-image" onclick="removeGalleryImage(${index})">×</button>`;
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

        const skuInput = document.getElementById('main_sku_code');
        if (skuInput.value.trim()) validateSkuCode(skuInput.value);

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
            if (!validateCurrentTab()) return;
            if (currentTab === 1 && !validatePricing()) {
                showAlert('Please correct the pricing values', 'error');
                return;
            }
            if (currentTab < tabs.length - 1) {
                currentTab++;
                showTab(currentTab);
            }
        });

        // Back button
        document.getElementById('backBtn').addEventListener('click', () => {
            if (currentTab > 0) { currentTab--; showTab(currentTab); }
        });

        // Form submit
        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (skuValidationState === false) {
                showAlert('Please fix the SKU code error before submitting', 'error');
                document.getElementById('main_sku_code').focus();
                return;
            }
            if (!validatePricing()) {
                currentTab = 1; showTab(currentTab);
                showAlert('Please correct the pricing values', 'error');
                return;
            }

            let allValid = true;
            for (let i = 0; i < tabs.length; i++) {
                const tabElement = document.getElementById(tabs[i]);
                const requiredInputs = tabElement.querySelectorAll('[required]');
                requiredInputs.forEach(input => {
                    input.classList.remove('error');
                    if (!input.value.trim()) { input.classList.add('error'); allValid = false; }
                });
                if (!allValid) {
                    currentTab = i; showTab(i);
                    showAlert('Please fill in all required fields', 'error');
                    return;
                }
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '⏳ Creating...';
            submitBtn.disabled = true;
            if (localStorage.getItem('opened_from_purchase') === 'true') {
                localStorage.setItem('product_created', 'true');
                localStorage.setItem('product_created_close', 'true');
            }
            this.submit();
        });

        // If old HSN value exists, fetch GST
        const existingHsn = document.getElementById('hsn_code').value;
        if (existingHsn && existingHsn.length >= 6) {
            fetchGstForHsn(existingHsn);
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
    });
</script>
@endpush
@endsection
