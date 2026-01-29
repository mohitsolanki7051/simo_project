@extends('layouts.admin')

@section('title', 'Edit Product - Admin Panel')
@section('header-title', 'Edit Product')

@section('content')
<div class="edit-product-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('admin.products.index') }}" class="back-btn">
                <span>←</span> Back to Products
            </a>
            <h2 class="page-title">Edit Product</h2>
            <p class="page-subtitle">Update product information</p>
        </div>
    </div>

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf
        @method('PUT')

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
                        <p class="tab-description">Update the essential details about your product</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Product Name <span class="required">*</span></label>
                            <input type="text" class="form-input" name="name" value="{{ old('name', $product->name) }}" placeholder="e.g., LED Bulb 9W" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Warehouse <span class="required">*</span></label>
                            <select class="form-select" name="warehouse_id" required>
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $product->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }} ({{ $warehouse->code }})
                                </option>
                                @endforeach
                            </select>
                            <span class="form-hint">Select the warehouse where this product is stored</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Brand <span class="required">*</span></label>
                            <input type="text" class="form-input" name="brand" value="{{ old('brand', $product->brand) }}" placeholder="Enter brand name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Body Type <span class="required">*</span></label>
                            <input type="text" class="form-input" name="body_type" value="{{ old('body_type', $product->body_type) }}" placeholder="e.g., PVC, Metal, Plastic" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Warranty <span class="required">*</span></label>
                            <input type="text" class="form-input" name="warranty" value="{{ old('warranty', $product->warranty) }}" placeholder="e.g., 12 months" maxlength="10" required>
                        </div>

                        <!-- Main Product SKU with Real-Time Validation -->
                        <div class="form-group">
                            <label class="form-label">SKU Code <span class="required">*</span></label>
                            <input
                                type="text"
                                class="form-input"
                                id="main_sku_code"
                                name="sku_code"
                                value="{{ old('sku_code', $product->sku_code) }}"
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
                                <option value="CODE128" {{ old('barcode_symbology', $product->barcode_symbology) == 'CODE128' ? 'selected' : '' }}>Code 128</option>
                                <option value="CODE39" {{ old('barcode_symbology', $product->barcode_symbology) == 'CODE39' ? 'selected' : '' }}>Code 39</option>
                                <option value="EAN13" {{ old('barcode_symbology', $product->barcode_symbology) == 'EAN13' ? 'selected' : '' }}>EAN-13</option>
                                <option value="EAN8" {{ old('barcode_symbology', $product->barcode_symbology) == 'EAN8' ? 'selected' : '' }}>EAN-8</option>
                                <option value="UPC" {{ old('barcode_symbology', $product->barcode_symbology) == 'UPC' ? 'selected' : '' }}>UPC</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">HSN Code <span class="required">*</span></label>
                            <input type="text" class="form-input" name="hsn_code" value="{{ old('hsn_code', $product->hsn_code) }}" placeholder="85395000" maxlength="8" required>
                            <span class="form-hint">Maximum 8 digits</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">GST (%) <span class="required">*</span></label>
                            <input type="number" step="0.01" class="form-input" name="gst" value="{{ old('gst', $product->gst) }}" placeholder="18" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Description <span class="required">*</span></label>
                            <textarea class="form-textarea" name="description" rows="5" placeholder="Enter detailed product description with features and specifications" required>{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Images -->
                <div class="tab-content" id="media">
                    <div class="tab-header">
                        <h3 class="tab-title">Product Images</h3>
                        <p class="tab-description">Update product images (800x800px recommended)</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Main Image</label>

                            @if($product->base_image)
                            <div class="current-image-preview">
                                <div class="current-label">Current Image:</div>
                                <div class="image-preview-container">
                                    <div class="preview-item" id="current-base-image">
                                        <img src="{{ asset('storage/' . $product->base_image) }}" class="preview-image" alt="Current Image">
                                        <input type="checkbox" name="remove_base_image" id="remove_base_image" value="1" style="display: none;">
                                        <button type="button" class="remove-image" onclick="markImageForRemoval('base')">×</button>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="image-upload-wrapper">
                                <input type="file" class="form-input-file" id="base_image" name="base_image" accept="image/*" onchange="previewBaseImage(event)" hidden>
                                <label for="base_image" class="upload-label">
                                    <div class="upload-icon">📸</div>
                                    <div class="upload-text">Click to upload new image</div>
                                    <div class="upload-hint">PNG, JPG up to 2MB</div>
                                </label>
                            </div>
                            <div class="image-preview-container" id="baseImagePreview"></div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Gallery Images</label>

                            @if($product->gallery_images && count($product->gallery_images) > 0)
                            <div class="current-image-preview">
                                <div class="current-label">Current Gallery Images:</div>
                                <div class="gallery-preview" id="currentGalleryPreview">
                                    @foreach($product->gallery_images as $index => $image)
                                    <div class="preview-item" id="current-gallery-{{ $index }}">
                                        <img src="{{ asset('storage/' . $image) }}" class="preview-image" alt="Gallery Image">
                                        <input type="checkbox" name="remove_gallery_images[]" value="{{ $index }}" style="display: none;" id="remove-gallery-{{ $index }}">
                                        <button type="button" class="remove-image" onclick="markGalleryImageForRemoval({{ $index }})">×</button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div class="image-upload-wrapper">
                                <input type="file" class="form-input-file" id="gallery_images" name="gallery_images[]" accept="image/*" multiple onchange="previewGalleryImages(event)" hidden>
                                <label for="gallery_images" class="upload-label">
                                    <div class="upload-icon">🖼️</div>
                                    <div class="upload-text">Upload additional gallery images</div>
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
                        <p class="tab-description">Manage existing variants and add new ones</p>
                    </div>

                    <div class="variants-section">
                        <!-- Existing Variants Section -->
                        <div class="existing-variants-section">
                            <h4 class="section-title">Existing Variants ({{ count($product->variants ?? []) }})</h4>

                            @if($product->variants && count($product->variants) > 0)
                                <div class="variants-info-box">
                                    <div class="info-icon">📦</div>
                                    <div>
                                        <strong>{{ count($product->variants) }} Existing Variant(s)</strong>
                                        <p>You can update variant details but cannot modify opening stock for existing variants.</p>
                                    </div>
                                </div>

                                <div id="existingVariantsContainer">
                                    @foreach($product->variants as $index => $variant)
                                    <div class="variant-card existing-variant" id="existing-variant-{{ $index }}">
                                        <div class="variant-header">
                                            <h4 class="variant-title">
                                                Existing Variant #{{ $index + 1 }}
                                            </h4>
                                        </div>

                                        <input type="hidden" name="variants[{{ $index }}][is_new]" value="0">
                                        <input type="hidden" name="variants[{{ $index }}][attributes]" value='{{ json_encode($variant["attributes"] ?? []) }}'>
                                        <input type="hidden" name="variants[{{ $index }}][color_temperature]" value="{{ $variant['color_temperature'] ?? '' }}">
                                        <input type="hidden" name="variants[{{ $index }}][watt]" value="{{ $variant['watt'] ?? '' }}">
                                        <input type="hidden" name="variants[{{ $index }}][shape]" value="{{ $variant['shape'] ?? '' }}">

                                        <div class="form-grid">
                                            <div class="form-group full-width">
                                                <label class="form-label">Variant Name <span class="required">*</span></label>
                                                <input type="text" class="form-input" name="variants[{{ $index }}][name]" value="{{ $variant['name'] ?? '' }}" placeholder="Enter variant name" required>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Color Temperature</label>
                                                <input type="text" class="form-input" value="{{ $variant['color_temperature'] ?? '' }}" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Watt</label>
                                                <input type="text" class="form-input" value="{{ $variant['watt'] ?? '' }}" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Shape</label>
                                                <input type="text" class="form-input" value="{{ $variant['shape'] ?? '' }}" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Unit <span class="required">*</span></label>
                                                <input type="text" class="form-input" name="variants[{{ $index }}][unit]" value="{{ $variant['unit'] ?? 'Piece' }}" required>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">SKU Code <span class="required">*</span></label>
                                                <input
                                                    type="text"
                                                    class="form-input"
                                                    id="existing_variant_sku_{{ $index }}"
                                                    name="variants[{{ $index }}][sku_code]"
                                                    value="{{ $variant['sku_code'] ?? '' }}"
                                                    maxlength="16"
                                                    required
                                                    onkeyup="validateSkuCode('existing_variant_{{ $index }}', this.value)">
                                                <div class="sku-validation-message" id="existing_variant_{{ $index }}_sku_message"></div>
                                                <span class="form-hint">Max 16 characters</span>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Barcode Symbology <span class="required">*</span></label>
                                                <select class="form-select" name="variants[{{ $index }}][barcode_symbology]" required>
                                                    <option value="CODE128" {{ ($variant['barcode_symbology'] ?? '') == 'CODE128' ? 'selected' : '' }}>Code 128</option>
                                                    <option value="CODE39" {{ ($variant['barcode_symbology'] ?? '') == 'CODE39' ? 'selected' : '' }}>Code 39</option>
                                                    <option value="EAN13" {{ ($variant['barcode_symbology'] ?? '') == 'EAN13' ? 'selected' : '' }}>EAN-13</option>
                                                    <option value="EAN8" {{ ($variant['barcode_symbology'] ?? '') == 'EAN8' ? 'selected' : '' }}>EAN-8</option>
                                                    <option value="UPC" {{ ($variant['barcode_symbology'] ?? '') == 'UPC' ? 'selected' : '' }}>UPC</option>
                                                </select>
                                            </div>

                                            <!-- Opening Stock - Read Only for existing variants -->
                                            <div class="form-group">
                                                <label class="form-label">Opening Stock</label>
                                                <input type="number" class="form-input" value="{{ $variant['opening_stock'] ?? 0 }}" readonly>
                                                <span class="form-hint">Cannot modify for existing variants</span>
                                                <input type="hidden" name="variants[{{ $index }}][opening_stock]" value="{{ $variant['opening_stock'] ?? 0 }}">
                                            </div>
                                        </div>

                                        <div class="variant-section-title">Pricing</div>
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label class="form-label">Cost Price (₹) <span class="required">*</span></label>
                                                <input type="number" step="0.01" class="form-input" id="existing_variant_cost_price_{{ $index }}" name="variants[{{ $index }}][cost_price]" value="{{ $variant['cost_price'] ?? '' }}" required oninput="validateExistingVariantPricing({{ $index }})">
                                                <span class="form-hint">Base cost of the product</span>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">MRP Price (₹) <span class="required">*</span></label>
                                                <input type="number" step="0.01" class="form-input" id="existing_variant_mrp_price_{{ $index }}" name="variants[{{ $index }}][mrp_price]" value="{{ $variant['mrp_price'] ?? '' }}" required oninput="validateExistingVariantPricing({{ $index }})">
                                                <span class="form-hint error-hint" id="existing_mrp_error_{{ $index }}" style="display: none;">Must be greater than cost price</span>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Dealer Price (₹) <span class="required">*</span></label>
                                                <input type="number" step="0.01" class="form-input" id="existing_variant_dealer_price_{{ $index }}" name="variants[{{ $index }}][dealer_price]" value="{{ $variant['dealer_price'] ?? '' }}" required oninput="validateExistingVariantPricing({{ $index }})">
                                                <span class="form-hint error-hint" id="existing_dealer_error_{{ $index }}" style="display: none;">Must be greater than cost price</span>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Distributor Price (₹) <span class="required">*</span></label>
                                                <input type="number" step="0.01" class="form-input" id="existing_variant_distributor_price_{{ $index }}" name="variants[{{ $index }}][distributor_price]" value="{{ $variant['distributor_price'] ?? '' }}" required oninput="validateExistingVariantPricing({{ $index }})">
                                                <span class="form-hint error-hint" id="existing_distributor_error_{{ $index }}" style="display: none;">Must be greater than cost price</span>
                                            </div>
                                        </div>

                                        <div class="variant-section-title">Stock Management</div>
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label class="form-label">Current Stock <span class="required">*</span></label>
                                                <input type="number" class="form-input" id="existing_variant_current_stock_{{ $index }}" name="variants[{{ $index }}][current_stock]" value="{{ $variant['current_stock'] ?? 0 }}" required oninput="validateExistingVariantStock({{ $index }})">
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Min Stock Alert <span class="required">*</span></label>
                                                <input type="number" class="form-input" id="existing_variant_min_stock_{{ $index }}" name="variants[{{ $index }}][min_stock_alert]" value="{{ $variant['min_stock_alert'] ?? 10 }}" required oninput="validateExistingVariantStock({{ $index }})">
                                                <span class="form-hint">Alert when stock reaches this level</span>
                                                <span class="form-hint error-hint" id="existing_current_stock_error_{{ $index }}" style="display: none;">Current stock must be greater than or equal to min stock alert</span>
                                            </div>
                                        </div>

                                        <div class="variant-section-title">Variant Images</div>
                                        <div class="form-grid">
                                            <div class="form-group full-width">
                                                <label class="form-label">Variant Base Image</label>

                                                @if(!empty($variant['base_image']))
                                                <div class="current-image-preview">
                                                    <div class="current-label">Current Variant Image:</div>
                                                    <div class="image-preview-container">
                                                        <div class="preview-item">
                                                            <img src="{{ asset('storage/' . $variant['base_image']) }}" class="preview-image" alt="Variant Image">
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="image-upload-wrapper">
                                                    <input type="file" class="form-input-file" id="existing_variant_base_{{ $index }}" name="variants[{{ $index }}][base_image]" accept="image/*" onchange="previewExistingVariantBaseImage(event, {{ $index }})" hidden>
                                                    <label for="existing_variant_base_{{ $index }}" class="upload-label">
                                                        <div class="upload-icon">📸</div>
                                                        <div class="upload-text">Upload new variant image</div>
                                                        <div class="upload-hint">PNG, JPG up to 2MB</div>
                                                    </label>
                                                </div>
                                                <div class="image-preview-container" id="existingVariantBasePreview-{{ $index }}"></div>
                                            </div>

                                            <div class="form-group full-width">
                                                <label class="form-label">Variant Gallery Images</label>

                                                @if(!empty($variant['gallery_images']) && is_array($variant['gallery_images']) && count($variant['gallery_images']) > 0)
                                                <div class="current-image-preview">
                                                    <div class="current-label">Current Gallery Images:</div>
                                                    <div class="gallery-preview">
                                                        @foreach($variant['gallery_images'] as $galleryImg)
                                                        <div class="preview-item">
                                                            <img src="{{ asset('storage/' . $galleryImg) }}" class="preview-image" alt="Variant Gallery">
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="image-upload-wrapper">
                                                    <input type="file" class="form-input-file" id="existing_variant_gallery_{{ $index }}" name="variants[{{ $index }}][gallery_images][]" accept="image/*" multiple onchange="previewExistingVariantGalleryImages(event, {{ $index }})" hidden>
                                                    <label for="existing_variant_gallery_{{ $index }}" class="upload-label">
                                                        <div class="upload-icon">🖼️</div>
                                                        <div class="upload-text">Upload new gallery images</div>
                                                        <div class="upload-hint">Select multiple files</div>
                                                    </label>
                                                </div>
                                                <div class="gallery-preview" id="existingVariantGalleryPreview-{{ $index }}"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="no-variants-message">
                                    <div class="empty-icon">📦</div>
                                    <p>No existing variants found for this product</p>
                                    <p class="empty-hint">You can add new variants using the section below</p>
                                </div>
                            @endif
                        </div>

                        <!-- Add New Variants Section -->
                        <div class="new-variants-section">
                            <h4 class="section-title">Add New Variants</h4>

                            <div class="attribute-selector-section">
                                <div class="selector-title">
                                    🎨 Select Attribute Values for New Variants
                                </div>

                                <div id="attributesContainer">
                                    <div class="loading-attributes">
                                        <div class="spinner"></div>
                                        <span>Loading attributes...</span>
                                    </div>
                                </div>

                                <button type="button" class="btn-generate-variants" id="generateVariantsBtn" onclick="generateNewVariants()" disabled>
                                    <span>⚡</span> Generate New Variants
                                </button>
                            </div>

                            <!-- Generated New Variants Info -->
                            <div class="generated-variants-info" id="newVariantsInfo">
                                <p class="info-text" id="newVariantsInfoText"></p>
                            </div>

                            <!-- New Variants Container -->
                            <div id="newVariantsContainer">
                                <!-- Newly generated variants will appear here -->
                            </div>
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
                    <a href="{{ route('admin.products.index') }}" class="btn-cancel">
                        <span>✕</span> Cancel
                    </a>
                    <button type="button" class="btn-next" id="nextBtn">
                        Next <span>→</span>
                    </button>
                    <button type="submit" class="btn-primary" id="submitBtn" style="display: none;">
                        <span>✓</span> Update Product
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .edit-product-container { padding-bottom: 100px; }
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }
    .page-header { margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; margin-bottom: 15px; }
    .back-btn:hover { background: #f7fafc; color: #ff6b35; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .page-subtitle { font-size: 14px; color: #718096; }
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
    .section-title { font-size: 18px; font-weight: 700; color: #2d3748; margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0; }
    .existing-variants-section, .new-variants-section { margin-bottom: 40px; }
    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin-bottom: 25px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: span 2; }
    .form-label { margin-bottom: 8px; font-weight: 600; color: #4a5568; font-size: 14px; }
    .required { color: #fc8181; }
    .form-input, .form-textarea, .form-select { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; font-family: inherit; }
    .form-input:focus, .form-textarea:focus, .form-select:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
    .form-input.error, .form-textarea.error, .form-select.error { border-color: #fc8181; background: #fff5f5; }
    .form-input:read-only { background-color: #f7fafc; color: #718096; cursor: not-allowed; }
    .form-textarea { min-height: 120px; resize: vertical; }
    .form-hint { margin-top: 6px; font-size: 12px; color: #a0aec0; }
    .error-hint { color: #fc8181 !important; font-weight: 600; }

    /* SKU VALIDATION STYLES */
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

    /* Existing Variant Styles */
    .existing-variant { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border: 2px solid #cbd5e0; }
    .variants-info-box { background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%); padding: 20px; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid #4299e1; display: flex; gap: 15px; align-items: start; }
    .info-icon { font-size: 28px; }
    .variants-info-box strong { color: #2c5282; font-size: 15px; display: block; margin-bottom: 5px; }
    .variants-info-box p { color: #2d3748; font-size: 13px; margin: 0; }
    .no-variants-message { background: #f7fafc; padding: 40px 20px; border-radius: 12px; text-align: center; border: 2px dashed #cbd5e0; margin-bottom: 20px; }
    .empty-icon { font-size: 48px; margin-bottom: 15px; }
    .no-variants-message p { color: #4a5568; margin-bottom: 5px; }
    .empty-hint { font-size: 12px; color: #a0aec0; }

    /* New Variants Section */
    .new-variants-section { background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%); padding: 25px; border-radius: 16px; border: 2px solid #9ae6b4; }
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

    /* New Variant Card Styles */
    .new-variant-card { background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%); padding: 30px; border-radius: 16px; border: 2px solid #9ae6b4; margin-bottom: 25px; animation: slideIn 0.3s ease; }
    .variant-card { padding: 30px; border-radius: 16px; border: 2px solid #e2e8f0; margin-bottom: 25px; position: relative; }
    .variant-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #cbd5e0; }
    .variant-title { font-size: 18px; font-weight: 700; color: #2d3748; }
    .btn-remove-variant { padding: 8px 16px; background: #fc8181; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .btn-remove-variant:hover { background: #f56565; transform: scale(1.05); }
    .variant-attribute-info { background: #ebf8ff; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4299e1; font-size: 13px; color: #2c5282; }
    .variant-section-title { font-size: 16px; font-weight: 700; color: #2d3748; margin: 20px 0 15px; padding-bottom: 8px; border-bottom: 2px solid #cbd5e0; }
    .variant-images-display { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; margin-top: 15px; }

    /* Current Image Styles */
    .current-image-preview { margin-bottom: 20px; }
    .current-label { font-size: 13px; font-weight: 600; color: #4a5568; margin-bottom: 12px; }
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
    .preview-item.marked-for-removal { opacity: 0.4; }
    .preview-item.marked-for-removal::after { content: 'Will be removed'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(252, 129, 129, 0.9); color: white; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; }

    .form-actions-fixed { position: fixed; bottom: 0; left: 260px; right: 0; background: white; padding: 20px 35px; box-shadow: 0 -4px 20px rgba(0,0,0,0.1); z-index: 100; }
    .form-actions-content { display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto; }
    .action-buttons-right { display: flex; gap: 15px; margin-left: auto; }
    .btn-back, .btn-next, .btn-cancel { padding: 12px 24px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; text-decoration: none; }
    .btn-back:hover, .btn-next:hover, .btn-cancel:hover { background: #cbd5e0; transform: translateY(-2px); }
    .btn-primary { padding: 12px 30px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(255,107,53,0.3); display: flex; align-items: center; gap: 8px; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.4); }
    @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .form-group.full-width { grid-column: span 1; } .form-actions-fixed { left: 0; } .attributes-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@push('scripts')
<script>
    let currentTab = 0;
    const tabs = ['basic-info', 'media', 'variants'];

    // ✅ SKU VALIDATION VARIABLES
    let skuValidationTimers = {};
    let skuValidationStates = {};

    // Variant management
    let newVariantCount = 0;
    let selectedAttributes = {};
    let existingVariants = @json($product->variants ?? []);

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

    // ✅ REAL-TIME SKU VALIDATION FUNCTION - FIXED
    function validateSkuCode(fieldId, skuCode) {
        // Clear existing timer
        if (skuValidationTimers[fieldId]) {
            clearTimeout(skuValidationTimers[fieldId]);
        }

        // Get the correct input field based on fieldId
        let inputField;
        if (fieldId === 'main') {
            inputField = document.getElementById('main_sku_code');
        } else if (fieldId.startsWith('existing_variant_')) {
            const variantIndex = fieldId.replace('existing_variant_', '');
            inputField = document.getElementById(`existing_variant_sku_${variantIndex}`);
        } else if (fieldId.startsWith('new_variant_')) {
            const variantId = fieldId.replace('new_variant_', '');
            inputField = document.getElementById(`new_variant_sku_${variantId}`);
        }

        const messageDiv = document.getElementById(`${fieldId}_sku_message`);

        if (!inputField || !messageDiv) {
            console.error(`Element not found for fieldId: ${fieldId}`);
            return;
        }

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
                    product_id: '{{ $product->id }}' // Include current product ID for edit mode
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
            const mainSkuField = document.getElementById('main_sku_code');
            if (mainSkuField) {
                const mainSku = mainSkuField.value?.trim()?.toUpperCase();
                if (mainSku && mainSku === trimmedSku) {
                    return true;
                }
            }
        }

        // Check all existing variant SKU fields
        const existingVariantCards = document.querySelectorAll('.existing-variant');
        for (let card of existingVariantCards) {
            const variantIndex = card.id.replace('existing-variant-', '');
            const fieldIdToCheck = `existing_variant_${variantIndex}`;

            if (fieldIdToCheck !== currentFieldId) {
                const variantSkuInput = document.getElementById(`existing_variant_sku_${variantIndex}`);
                if (variantSkuInput) {
                    const variantSku = variantSkuInput.value?.trim()?.toUpperCase();
                    if (variantSku && variantSku === trimmedSku) {
                        return true;
                    }
                }
            }
        }

        // Check all new variant SKU fields
        const newVariantCards = document.querySelectorAll('.new-variant-card');
        for (let card of newVariantCards) {
            const variantId = card.id.replace('new-variant-', '');
            const fieldIdToCheck = `new_variant_${variantId}`;

            if (fieldIdToCheck !== currentFieldId) {
                const variantSkuInput = document.getElementById(`new_variant_sku_${variantId}`);
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
        const mainSkuField = document.getElementById('main_sku_code');
        if (mainSkuField) {
            const mainSku = mainSkuField.value?.trim();
            if (mainSku) {
                if (skuValidationStates['main'] === false) {
                    allValid = false;
                    errors.push('Main product SKU code is invalid or already in use');
                } else if (skuValidationStates['main'] === null) {
                    allValid = false;
                    errors.push('Main product SKU code validation is pending');
                }
            }
        }

        // Check existing variant SKUs
        const existingVariantCards = document.querySelectorAll('.existing-variant');
        existingVariantCards.forEach((card) => {
            const variantIndex = card.id.replace('existing-variant-', '');
            const fieldId = `existing_variant_${variantIndex}`;
            const variantSkuField = document.getElementById(`existing_variant_sku_${variantIndex}`);

            if (variantSkuField) {
                const variantSku = variantSkuField.value?.trim();
                if (variantSku) {
                    if (skuValidationStates[fieldId] === false) {
                        allValid = false;
                        errors.push(`Existing Variant #${parseInt(variantIndex) + 1} SKU code is invalid or already in use`);
                    } else if (skuValidationStates[fieldId] === null) {
                        allValid = false;
                        errors.push(`Existing Variant #${parseInt(variantIndex) + 1} SKU code validation is pending`);
                    }
                }
            }
        });

        // Check new variant SKUs
        const newVariantCards = document.querySelectorAll('.new-variant-card');
        newVariantCards.forEach((card) => {
            const variantId = card.id.replace('new-variant-', '');
            const fieldId = `new_variant_${variantId}`;
            const variantSkuField = document.getElementById(`new_variant_sku_${variantId}`);

            if (variantSkuField) {
                const variantSku = variantSkuField.value?.trim();
                if (variantSku) {
                    if (skuValidationStates[fieldId] === false) {
                        allValid = false;
                        errors.push(`New Variant #${variantId} SKU code is invalid or already in use`);
                    } else if (skuValidationStates[fieldId] === null) {
                        allValid = false;
                        errors.push(`New Variant #${variantId} SKU code validation is pending`);
                    }
                }
            }
        });

        if (!allValid && errors.length > 0) {
            showAlert(errors[0], 'error');
        }

        return allValid;
    }

    // Real-time validation for existing variant pricing
    function validateExistingVariantPricing(variantIndex) {
        const costPrice = parseFloat(document.getElementById(`existing_variant_cost_price_${variantIndex}`)?.value) || 0;
        const mrpPrice = parseFloat(document.getElementById(`existing_variant_mrp_price_${variantIndex}`)?.value) || 0;
        const dealerPrice = parseFloat(document.getElementById(`existing_variant_dealer_price_${variantIndex}`)?.value) || 0;
        const distributorPrice = parseFloat(document.getElementById(`existing_variant_distributor_price_${variantIndex}`)?.value) || 0;

        const mrpInput = document.getElementById(`existing_variant_mrp_price_${variantIndex}`);
        const dealerInput = document.getElementById(`existing_variant_dealer_price_${variantIndex}`);
        const distributorInput = document.getElementById(`existing_variant_distributor_price_${variantIndex}`);

        const mrpError = document.getElementById(`existing_mrp_error_${variantIndex}`);
        const dealerError = document.getElementById(`existing_dealer_error_${variantIndex}`);
        const distributorError = document.getElementById(`existing_distributor_error_${variantIndex}`);

        if (!mrpInput || !dealerInput || !distributorInput) return;

        // Validate MRP Price
        if (mrpPrice > 0 && mrpPrice <= costPrice) {
            mrpInput.classList.add('error');
            if (mrpError) mrpError.style.display = 'block';
        } else {
            mrpInput.classList.remove('error');
            if (mrpError) mrpError.style.display = 'none';
        }

        // Validate Dealer Price
        if (dealerPrice > 0 && dealerPrice <= costPrice) {
            dealerInput.classList.add('error');
            if (dealerError) dealerError.style.display = 'block';
        } else {
            dealerInput.classList.remove('error');
            if (dealerError) dealerError.style.display = 'none';
        }

        // Validate Distributor Price
        if (distributorPrice > 0 && distributorPrice <= costPrice) {
            distributorInput.classList.add('error');
            if (distributorError) distributorError.style.display = 'block';
        } else {
            distributorInput.classList.remove('error');
            if (distributorError) distributorError.style.display = 'none';
        }
    }

    // Real-time validation for existing variant stock
    function validateExistingVariantStock(variantIndex) {
        const currentStock = parseInt(document.getElementById(`existing_variant_current_stock_${variantIndex}`)?.value) || 0;
        const minStockAlert = parseInt(document.getElementById(`existing_variant_min_stock_${variantIndex}`)?.value) || 0;

        const currentStockInput = document.getElementById(`existing_variant_current_stock_${variantIndex}`);
        const currentStockError = document.getElementById(`existing_current_stock_error_${variantIndex}`);

        if (!currentStockInput) return;

        // Validate Current Stock >= Min Stock Alert
        if (currentStock < minStockAlert) {
            currentStockInput.classList.add('error');
            if (currentStockError) currentStockError.style.display = 'block';
        } else {
            currentStockInput.classList.remove('error');
            if (currentStockError) currentStockError.style.display = 'none';
        }
    }

    // Real-time validation for new variant pricing
    function validateNewVariantPricing(variantId) {
        const costPrice = parseFloat(document.getElementById(`new_variant_cost_price_${variantId}`)?.value) || 0;
        const mrpPrice = parseFloat(document.getElementById(`new_variant_mrp_price_${variantId}`)?.value) || 0;
        const dealerPrice = parseFloat(document.getElementById(`new_variant_dealer_price_${variantId}`)?.value) || 0;
        const distributorPrice = parseFloat(document.getElementById(`new_variant_distributor_price_${variantId}`)?.value) || 0;

        const mrpInput = document.getElementById(`new_variant_mrp_price_${variantId}`);
        const dealerInput = document.getElementById(`new_variant_dealer_price_${variantId}`);
        const distributorInput = document.getElementById(`new_variant_distributor_price_${variantId}`);

        const mrpError = document.getElementById(`new_mrp_error_${variantId}`);
        const dealerError = document.getElementById(`new_dealer_error_${variantId}`);
        const distributorError = document.getElementById(`new_distributor_error_${variantId}`);

        if (!mrpInput || !dealerInput || !distributorInput) return;

        // Validate MRP Price
        if (mrpPrice > 0 && mrpPrice <= costPrice) {
            mrpInput.classList.add('error');
            if (mrpError) mrpError.style.display = 'block';
        } else {
            mrpInput.classList.remove('error');
            if (mrpError) mrpError.style.display = 'none';
        }

        // Validate Dealer Price
        if (dealerPrice > 0 && dealerPrice <= costPrice) {
            dealerInput.classList.add('error');
            if (dealerError) dealerError.style.display = 'block';
        } else {
            dealerInput.classList.remove('error');
            if (dealerError) dealerError.style.display = 'none';
        }

        // Validate Distributor Price
        if (distributorPrice > 0 && distributorPrice <= costPrice) {
            distributorInput.classList.add('error');
            if (distributorError) distributorError.style.display = 'block';
        } else {
            distributorInput.classList.remove('error');
            if (distributorError) distributorError.style.display = 'none';
        }
    }

    // Real-time validation for new variant stock
    function validateNewVariantStock(variantId) {
        const openingStock = parseInt(document.getElementById(`new_variant_opening_stock_${variantId}`)?.value) || 0;
        const minStockAlert = parseInt(document.getElementById(`new_variant_min_stock_${variantId}`)?.value) || 0;

        const openingStockInput = document.getElementById(`new_variant_opening_stock_${variantId}`);
        const openingStockError = document.getElementById(`new_opening_stock_error_${variantId}`);

        if (!openingStockInput) return;

        // Validate Opening Stock >= Min Stock Alert
        if (openingStock < minStockAlert) {
            openingStockInput.classList.add('error');
            if (openingStockError) openingStockError.style.display = 'block';
        } else {
            openingStockInput.classList.remove('error');
            if (openingStockError) openingStockError.style.display = 'none';
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
            const variantCards = document.querySelectorAll('.existing-variant, .new-variant-card');
            variantCards.forEach((card) => {
                const isExisting = card.classList.contains('existing-variant');
                if (isExisting) {
                    const variantIndex = card.id.replace('existing-variant-', '');
                    const fieldId = `existing_variant_${variantIndex}`;
                    if (skuValidationStates[fieldId] === false) {
                        currentTab = 2; // Go to variants tab
                    }
                } else {
                    const variantId = card.id.replace('new-variant-', '');
                    const fieldId = `new_variant_${variantId}`;
                    if (skuValidationStates[fieldId] === false) {
                        currentTab = 2; // Go to variants tab
                    }
                }
            });
            showTab(currentTab);
            return;
        }

        // Validate all variants have required fields filled and pass validation
        let allVariantsValid = true;
        let validationErrors = [];

        // Validate existing variants
        const existingVariantCards = document.querySelectorAll('.existing-variant');
        existingVariantCards.forEach((card) => {
            const variantIndex = card.id.replace('existing-variant-', '');

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
            const costPrice = parseFloat(document.getElementById(`existing_variant_cost_price_${variantIndex}`)?.value) || 0;
            const mrpPrice = parseFloat(document.getElementById(`existing_variant_mrp_price_${variantIndex}`)?.value) || 0;
            const dealerPrice = parseFloat(document.getElementById(`existing_variant_dealer_price_${variantIndex}`)?.value) || 0;
            const distributorPrice = parseFloat(document.getElementById(`existing_variant_distributor_price_${variantIndex}`)?.value) || 0;

            if (mrpPrice > 0 && mrpPrice <= costPrice) {
                allVariantsValid = false;
                validationErrors.push(`Existing Variant #${parseInt(variantIndex) + 1}: MRP price must be greater than cost price`);
            }
            if (dealerPrice > 0 && dealerPrice <= costPrice) {
                allVariantsValid = false;
                validationErrors.push(`Existing Variant #${parseInt(variantIndex) + 1}: Dealer price must be greater than cost price`);
            }
            if (distributorPrice > 0 && distributorPrice <= costPrice) {
                allVariantsValid = false;
                validationErrors.push(`Existing Variant #${parseInt(variantIndex) + 1}: Distributor price must be greater than cost price`);
            }

            // Validate stock
            const currentStock = parseInt(document.getElementById(`existing_variant_current_stock_${variantIndex}`)?.value) || 0;
            const minStockAlert = parseInt(document.getElementById(`existing_variant_min_stock_${variantIndex}`)?.value) || 0;

            if (currentStock < minStockAlert) {
                allVariantsValid = false;
                validationErrors.push(`Existing Variant #${parseInt(variantIndex) + 1}: Current stock must be greater than or equal to min stock alert`);
            }
        });

        // Validate new variants
        const newVariantCards = document.querySelectorAll('.new-variant-card');
        newVariantCards.forEach((card) => {
            const variantId = card.id.replace('new-variant-', '');

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
            const costPrice = parseFloat(document.getElementById(`new_variant_cost_price_${variantId}`)?.value) || 0;
            const mrpPrice = parseFloat(document.getElementById(`new_variant_mrp_price_${variantId}`)?.value) || 0;
            const dealerPrice = parseFloat(document.getElementById(`new_variant_dealer_price_${variantId}`)?.value) || 0;
            const distributorPrice = parseFloat(document.getElementById(`new_variant_distributor_price_${variantId}`)?.value) || 0;

            if (mrpPrice > 0 && mrpPrice <= costPrice) {
                allVariantsValid = false;
                validationErrors.push(`New Variant #${variantId}: MRP price must be greater than cost price`);
            }
            if (dealerPrice > 0 && dealerPrice <= costPrice) {
                allVariantsValid = false;
                validationErrors.push(`New Variant #${variantId}: Dealer price must be greater than cost price`);
            }
            if (distributorPrice > 0 && distributorPrice <= costPrice) {
                allVariantsValid = false;
                validationErrors.push(`New Variant #${variantId}: Distributor price must be greater than cost price`);
            }

            // Validate stock
            const openingStock = parseInt(document.getElementById(`new_variant_opening_stock_${variantId}`)?.value) || 0;
            const minStockAlert = parseInt(document.getElementById(`new_variant_min_stock_${variantId}`)?.value) || 0;

            if (openingStock < minStockAlert) {
                allVariantsValid = false;
                validationErrors.push(`New Variant #${variantId}: Opening stock must be greater than or equal to min stock alert`);
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

    function markImageForRemoval(type) {
        const checkbox = document.getElementById('remove_base_image');
        const previewItem = document.getElementById('current-base-image');
        if (checkbox && previewItem) {
            if (checkbox.checked) {
                checkbox.checked = false;
                previewItem.classList.remove('marked-for-removal');
            } else {
                checkbox.checked = true;
                previewItem.classList.add('marked-for-removal');
            }
        }
    }

    function markGalleryImageForRemoval(index) {
        const checkbox = document.getElementById('remove-gallery-' + index);
        const previewItem = document.getElementById('current-gallery-' + index);
        if (checkbox && previewItem) {
            if (checkbox.checked) {
                checkbox.checked = false;
                previewItem.classList.remove('marked-for-removal');
            } else {
                checkbox.checked = true;
                previewItem.classList.add('marked-for-removal');
            }
        }
    }

    function previewBaseImage(event) {
        const input = event.target;
        const container = document.getElementById('baseImagePreview');
        if (container) {
            container.innerHTML = '';
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `<img src="${e.target.result}" class="preview-image"><button type="button" class="remove-image" onclick="removeNewBaseImage()">×</button>`;
                    container.appendChild(div);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    }

    function removeNewBaseImage() {
        const baseImageInput = document.getElementById('base_image');
        const container = document.getElementById('baseImagePreview');
        if (baseImageInput) baseImageInput.value = '';
        if (container) container.innerHTML = '';
    }

    let newGalleryFiles = [];
    function previewGalleryImages(event) {
        const input = event.target;
        const container = document.getElementById('galleryPreview');
        if (container) {
            container.innerHTML = '';
            newGalleryFiles = Array.from(input.files);
            newGalleryFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `<img src="${e.target.result}" class="preview-image"><button type="button" class="remove-image" onclick="removeNewGalleryImage(${index})">×</button>`;
                    container.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    }

    function removeNewGalleryImage(index) {
        newGalleryFiles.splice(index, 1);
        const dataTransfer = new DataTransfer();
        newGalleryFiles.forEach(file => dataTransfer.items.add(file));
        const galleryInput = document.getElementById('gallery_images');
        if (galleryInput) {
            galleryInput.files = dataTransfer.files;
            previewGalleryImages({ target: galleryInput });
        }
    }

    function previewExistingVariantBaseImage(event, variantIndex) {
        const input = event.target;
        const container = document.getElementById(`existingVariantBasePreview-${variantIndex}`);
        if (container) {
            container.innerHTML = '';
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `<img src="${e.target.result}" class="preview-image"><button type="button" class="remove-image" onclick="removeExistingVariantBaseImage(${variantIndex})">×</button>`;
                    container.appendChild(div);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    }

    function removeExistingVariantBaseImage(variantIndex) {
        const input = document.getElementById(`existing_variant_base_${variantIndex}`);
        const container = document.getElementById(`existingVariantBasePreview-${variantIndex}`);
        if (input) input.value = '';
        if (container) container.innerHTML = '';
    }

    function previewExistingVariantGalleryImages(event, variantIndex) {
        const input = event.target;
        const container = document.getElementById(`existingVariantGalleryPreview-${variantIndex}`);
        if (container) {
            container.innerHTML = '';
            const files = Array.from(input.files);
            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `<img src="${e.target.result}" class="preview-image"><button type="button" class="remove-image" onclick="removeExistingVariantGalleryImage(${variantIndex}, ${index})">×</button>`;
                    container.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    }

    function removeExistingVariantGalleryImage(variantIndex, imageIndex) {
        const input = document.getElementById(`existing_variant_gallery_${variantIndex}`);
        const files = Array.from(input.files);
        files.splice(imageIndex, 1);
        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        if (input) {
            input.files = dataTransfer.files;
            previewExistingVariantGalleryImages({ target: input }, variantIndex);
        }
    }

    // Load attributes
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
        if (!container) return;

        const hasColorTemp = attributes.color_temperature && attributes.color_temperature.length > 0;
        const hasWatt = attributes.watt && attributes.watt.length > 0;
        const hasShape = attributes.shape && attributes.shape.length > 0;

        if (!hasColorTemp && !hasWatt && !hasShape) {
            container.innerHTML = `
                <div class="no-attributes-message">
                    <p>⚠️ No attribute values found!</p>
                    <p>Please create attribute values first to generate new variants.</p>
                    <a href="{{ route('admin.attributes.create') }}" target="_blank">Create Attribute Values →</a>
                </div>
            `;
            const generateBtn = document.getElementById('generateVariantsBtn');
            if (generateBtn) generateBtn.disabled = true;
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
        const generateBtn = document.getElementById('generateVariantsBtn');
        if (generateBtn) generateBtn.disabled = false;
    }

    function updateSelectedAttributes() {
        selectedAttributes = {};
        const checkboxes = document.querySelectorAll('#attributesContainer input[type="checkbox"]:checked');
        checkboxes.forEach(checkbox => {
            const attrType = checkbox.dataset.attributeType;
            const attrName = checkbox.dataset.attributeName;
            const value = checkbox.value;
            if (!selectedAttributes[attrType]) {
                selectedAttributes[attrType] = { name: attrName, values: [] };
            }
            selectedAttributes[attrType].values.push(value);
        });
        const hasSelections = Object.keys(selectedAttributes).length > 0;
        const generateBtn = document.getElementById('generateVariantsBtn');
        if (generateBtn) generateBtn.disabled = !hasSelections;
    }

    // Check if a variant combination already exists
    function isVariantExists(combination) {
        const existingCombinations = existingVariants.map(variant => ({
            color_temperature: variant.color_temperature || '',
            watt: variant.watt || '',
            shape: variant.shape || ''
        }));

        // Check if any existing variant has the same attributes (checking exact match first)
        return existingCombinations.some(existing => {
            return (
                existing.color_temperature === (combination.color_temperature || '') &&
                existing.watt === (combination.watt || '') &&
                existing.shape === (combination.shape || '')
            );
        });
    }

    function generateNewVariants() {
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

        // Filter out combinations that already exist
        const newCombinations = combinations.filter(combination => !isVariantExists(combination));

        if (newCombinations.length === 0) {
            showAlert('All selected attribute combinations already exist as variants!', 'error');
            return;
        }

        // Clear previous new variants
        const container = document.getElementById('newVariantsContainer');
        if (container) {
            container.innerHTML = '';
        }
        newVariantCount = 0;

        newCombinations.forEach(combination => {
            createNewVariantFromCombination(combination);
        });

        const info = document.getElementById('newVariantsInfo');
        const infoText = document.getElementById('newVariantsInfoText');
        if (info && infoText) {
            infoText.textContent = `✓ Generated ${newCombinations.length} new variant(s) from selected attributes`;
            info.classList.add('show');
        }
        showAlert(`Successfully generated ${newCombinations.length} new variant(s)!`, 'success');
        const newVariantsContainer = document.getElementById('newVariantsContainer');
        if (newVariantsContainer) {
            newVariantsContainer.scrollIntoView({ behavior: 'smooth' });
        }
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

    function createNewVariantFromCombination(combination) {
        newVariantCount++;
        const container = document.getElementById('newVariantsContainer');
        if (!container) return;

        // Get product name - FIXED: Use the correct selector
        const productNameInput = document.querySelector('input[name="name"]');
        const productName = productNameInput ? productNameInput.value || 'Product' : 'Product';

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

        // Calculate index for new variant
        const existingCount = document.querySelectorAll('.existing-variant').length;
        const newVariantIndex = existingCount + newVariantCount - 1;

        const variantHtml = `
            <div class="new-variant-card" id="new-variant-${newVariantCount}">
                <div class="variant-header">
                    <h4 class="variant-title">New Variant #${newVariantCount}: ${attributeString}</h4>
                    <button type="button" class="btn-remove-variant" onclick="removeNewVariant(${newVariantCount})">Remove Variant</button>
                </div>
                <div class="variant-attribute-info"><strong>Attribute Combination:</strong> ${attributeString}</div>
                <input type="hidden" name="variants[${newVariantIndex}][is_new]" value="1">
                <input type="hidden" name="variants[${newVariantIndex}][attributes]" value='${JSON.stringify(combination)}'>
                <input type="hidden" name="variants[${newVariantIndex}][color_temperature]" value="${colorTemp}">
                <input type="hidden" name="variants[${newVariantIndex}][watt]" value="${watt}">
                <input type="hidden" name="variants[${newVariantIndex}][shape]" value="${shape}">

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Variant Name <span class="required">*</span></label>
                        <input type="text" class="form-input" name="variants[${newVariantIndex}][name]" value="${variantName}" required>
                        <span class="form-hint">Auto-generated from product name and attributes</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Color Temperature</label>
                        <input type="text" class="form-input" value="${colorTemp}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Watt</label>
                        <input type="text" class="form-input" value="${watt}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Shape</label>
                        <input type="text" class="form-input" value="${shape}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit <span class="required">*</span></label>
                        <input type="text" class="form-input" name="variants[${newVariantIndex}][unit]" placeholder="Piece, Box, Set" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">SKU Code <span class="required">*</span></label>
                        <input
                            type="text"
                            class="form-input"
                            id="new_variant_sku_${newVariantCount}"
                            name="variants[${newVariantIndex}][sku_code]"
                            placeholder="e.g., VAR-${skuSuffix}"
                            maxlength="16"
                            required
                            onkeyup="validateSkuCode('new_variant_${newVariantCount}', this.value)">
                        <div class="sku-validation-message" id="new_variant_${newVariantCount}_sku_message"></div>
                        <span class="form-hint">Max 16 characters</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Barcode Symbology <span class="required">*</span></label>
                        <select class="form-select" name="variants[${newVariantIndex}][barcode_symbology]" required>
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
                        <input type="number" step="0.01" class="form-input" id="new_variant_cost_price_${newVariantCount}" name="variants[${newVariantIndex}][cost_price]" placeholder="0.00" required oninput="validateNewVariantPricing(${newVariantCount})">
                        <span class="form-hint">Base cost of the product</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">MRP Price (₹) <span class="required">*</span></label>
                        <input type="number" step="0.01" class="form-input" id="new_variant_mrp_price_${newVariantCount}" name="variants[${newVariantIndex}][mrp_price]" placeholder="0.00" required oninput="validateNewVariantPricing(${newVariantCount})">
                        <span class="form-hint error-hint" id="new_mrp_error_${newVariantCount}" style="display: none;">Must be greater than cost price</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dealer Price (₹) <span class="required">*</span></label>
                        <input type="number" step="0.01" class="form-input" id="new_variant_dealer_price_${newVariantCount}" name="variants[${newVariantIndex}][dealer_price]" placeholder="0.00" required oninput="validateNewVariantPricing(${newVariantCount})">
                        <span class="form-hint error-hint" id="new_dealer_error_${newVariantCount}" style="display: none;">Must be greater than cost price</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Distributor Price (₹) <span class="required">*</span></label>
                        <input type="number" step="0.01" class="form-input" id="new_variant_distributor_price_${newVariantCount}" name="variants[${newVariantIndex}][distributor_price]" placeholder="0.00" required oninput="validateNewVariantPricing(${newVariantCount})">
                        <span class="form-hint error-hint" id="new_distributor_error_${newVariantCount}" style="display: none;">Must be greater than cost price</span>
                    </div>
                </div>
                <div class="variant-section-title">Stock Management</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Opening Stock <span class="required">*</span></label>
                        <input type="number" class="form-input" id="new_variant_opening_stock_${newVariantCount}" name="variants[${newVariantIndex}][opening_stock]" value="0" required oninput="validateNewVariantStock(${newVariantCount})">
                        <span class="form-hint">Initial stock for new variant</span>
                        <span class="form-hint error-hint" id="new_opening_stock_error_${newVariantCount}" style="display: none;">Must be greater than or equal to min stock alert</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Min Stock Alert <span class="required">*</span></label>
                        <input type="number" class="form-input" id="new_variant_min_stock_${newVariantCount}" name="variants[${newVariantIndex}][min_stock_alert]" value="10" required oninput="validateNewVariantStock(${newVariantCount})">
                        <span class="form-hint">Alert when stock reaches this level</span>
                    </div>
                </div>
                <div class="variant-section-title">Variant Images</div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Variant Base Image</label>
                        <div class="image-upload-wrapper">
                            <input type="file" class="form-input-file" id="new_variant_base_${newVariantCount}" name="variants[${newVariantIndex}][base_image]" accept="image/*" onchange="previewNewVariantBaseImage(event, ${newVariantCount})" hidden>
                            <label for="new_variant_base_${newVariantCount}" class="upload-label">
                                <div class="upload-icon">📸</div>
                                <div class="upload-text">Click to upload variant image</div>
                                <div class="upload-hint">PNG, JPG up to 2MB</div>
                            </label>
                        </div>
                        <div class="image-preview-container" id="newVariantBasePreview-${newVariantCount}"></div>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Variant Gallery Images</label>
                        <div class="image-upload-wrapper">
                            <input type="file" class="form-input-file" id="new_variant_gallery_${newVariantCount}" name="variants[${newVariantIndex}][gallery_images][]" accept="image/*" multiple onchange="previewNewVariantGalleryImages(event, ${newVariantCount})" hidden>
                            <label for="new_variant_gallery_${newVariantCount}" class="upload-label">
                                <div class="upload-icon">🖼️</div>
                                <div class="upload-text">Upload multiple variant images</div>
                                <div class="upload-hint">Select multiple files</div>
                            </label>
                        </div>
                        <div class="gallery-preview" id="newVariantGalleryPreview-${newVariantCount}"></div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', variantHtml);
    }

    function removeNewVariant(id) {
        const variant = document.getElementById(`new-variant-${id}`);
        if (variant) {
            // Clear SKU validation state for this variant
            delete skuValidationStates[`new_variant_${id}`];
            delete skuValidationTimers[`new_variant_${id}`];

            variant.remove();
            const remaining = document.querySelectorAll('.new-variant-card').length;
            const info = document.getElementById('newVariantsInfo');
            const infoText = document.getElementById('newVariantsInfoText');
            if (info && infoText) {
                if (remaining === 0) {
                    info.classList.remove('show');
                } else {
                    infoText.textContent = `✓ ${remaining} new variant(s) ready`;
                }
            }
        }
    }

    function previewNewVariantBaseImage(event, variantId) {
        const input = event.target;
        const container = document.getElementById(`newVariantBasePreview-${variantId}`);
        if (container) {
            container.innerHTML = '';
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `<img src="${e.target.result}" class="preview-image"><button type="button" class="remove-image" onclick="removeNewVariantBaseImage(${variantId})">×</button>`;
                    container.appendChild(div);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    }

    function removeNewVariantBaseImage(variantId) {
        const input = document.getElementById(`new_variant_base_${variantId}`);
        const container = document.getElementById(`newVariantBasePreview-${variantId}`);
        if (input) input.value = '';
        if (container) container.innerHTML = '';
    }

    function previewNewVariantGalleryImages(event, variantId) {
        const input = event.target;
        const container = document.getElementById(`newVariantGalleryPreview-${variantId}`);
        if (container) {
            container.innerHTML = '';
            const files = Array.from(input.files);
            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `<img src="${e.target.result}" class="preview-image"><button type="button" class="remove-image" onclick="removeNewVariantGalleryImage(${variantId}, ${index})">×</button>`;
                    container.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    }

    function removeNewVariantGalleryImage(variantId, imageIndex) {
        const input = document.getElementById(`new_variant_gallery_${variantId}`);
        const files = Array.from(input.files);
        files.splice(imageIndex, 1);
        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        if (input) {
            input.files = dataTransfer.files;
            previewNewVariantGalleryImages({ target: input }, variantId);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        showTab(0);
        loadAttributes();

        // Initialize SKU validation for existing values
        const mainSkuField = document.getElementById('main_sku_code');
        if (mainSkuField) {
            const mainSku = mainSkuField.value;
            if (mainSku) {
                validateSkuCode('main', mainSku);
            }
        }

        // Initialize existing variant SKU validation
        const existingVariantCards = document.querySelectorAll('.existing-variant');
        existingVariantCards.forEach((card) => {
            const variantIndex = card.id.replace('existing-variant-', '');
            const variantSkuField = document.getElementById(`existing_variant_sku_${variantIndex}`);
            if (variantSkuField) {
                const variantSku = variantSkuField.value;
                if (variantSku) {
                    validateSkuCode(`existing_variant_${variantIndex}`, variantSku);
                }
            }
        });
    });

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
