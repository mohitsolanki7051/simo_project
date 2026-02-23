@extends('layouts.admin')

@section('title', 'Product Details - Admin Panel')
@section('header-title', 'Product Details')

@section('content')
<div class="product-details-container">
    <!-- Header with Back Button -->
    <div class="page-header">
        <h1 class="page-title">Product Details</h1>
        <div class="header-actions">
            <a href="{{ route('admin.products.index') }}" class="btn-back">← Back</a>
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-edit">✏ Edit Product</a>
        </div>
    </div>

    <div class="details-layout">
        <!-- Left Column: Images -->
        <div class="left-column">
            <!-- Base Image -->
            <div class="section-card">
                <div class="section-header">Base Image</div>
                <div class="image-preview">
                    <img src="{{ asset('storage/' . $product->base_image) }}"
                         alt="{{ $product->name }}"
                         id="mainImage"
                         onerror="this.src='https://via.placeholder.com/200x200/e5e7eb/9ca3af?text=No+Image'">
                </div>
            </div>

            <!-- Gallery Images -->
            @if($product->gallery_images && count($product->gallery_images) > 0)
            <div class="section-card">
                <div class="section-header">Gallery Images</div>
                <div class="gallery-grid">
                    @foreach($product->gallery_images as $image)
                    <div class="gallery-thumb" onclick="changeMainImage('{{ asset('storage/' . $image) }}')">
                        <img src="{{ asset('storage/' . $image) }}" alt="Gallery">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Information -->
        <div class="right-column">
            <!-- Product Information -->
            <div class="section-card">
                <div class="section-header">Product Information</div>
                <div class="info-table">
                    <div class="info-row">
                        <span class="info-label">Product Name:</span>
                        <span class="info-value">{{ $product->name }}</span>
                    </div>
                    @if($product instanceof \App\Models\SimpleProduct)
                    <div class="info-row">
                        <span class="info-label">SKU:</span>
                        <span class="info-value">{{ $product->sku_code ?? 'N/A' }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Category:</span>
                        <span class="info-value">{{ $product->category->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Brand:</span>
                        <span class="info-value">{{ $product->brand ?? 'N/A' }}</span>
                    </div>
                    @if($product instanceof \App\Models\SimpleProduct)
                    <div class="info-row">
                        <span class="info-label">Body Type:</span>
                        <span class="info-value">{{ $product->body_type ?? 'N/A' }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value">
                            <span class="status-badge status-{{ $product->status }}">{{ ucfirst($product->status) }}</span>
                        </span>
                    </div>
                </div>

                @if($product->short_description)
                <div class="description-section">
                    <div class="desc-label">Description:</div>
                    <div class="desc-text">{{ $product->short_description }}</div>
                </div>
                @endif
            </div>

            @if($product instanceof \App\Models\SimpleProduct)
            <!-- Pricing & Stock for Simple Product -->
            <div class="section-card">
                <div class="section-header">Pricing & Stock</div>
                <div class="pricing-grid">
                    <div class="price-item">
                        <div class="price-label">MRP Price</div>
                        <div class="price-value">₹{{ number_format($product->mrp_price, 2) }}</div>
                    </div>
                    <div class="price-item">
                        <div class="price-label">Sale Price</div>
                        <div class="price-value">₹{{ number_format($product->sale_price, 2) }}</div>
                    </div>
                    <div class="price-item">
                        <div class="price-label">Discount</div>
                        <div class="price-value">
                            {{ $product->mrp_price > $product->sale_price ? round((($product->mrp_price - $product->sale_price) / $product->mrp_price) * 100) : 0 }}%
                        </div>
                    </div>
                    <div class="price-item">
                        <div class="price-label">Stock</div>
                        <div class="price-value">{{ $product->total_stock }}</div>
                    </div>
                    <div class="price-item">
                        <div class="price-label">Min Stock Alert</div>
                        <div class="price-value">
                            @if($product->main_warehouse_min_stock_alert > 0)
                                {{ $product->main_warehouse_min_stock_alert }}
                            @else
                                Not Set
                            @endif
                        </div>
                    </div>
                    <div class="price-item">
                        <div class="price-label">Unit</div>
                        <div class="price-value">{{ $product->unit ?? 'pcs' }}</div>
                    </div>
                    <div class="price-item">
                        <div class="price-label">Created</div>
                        <div class="price-value-sm">{{ $product->created_at->setTimezone('Asia/Kolkata')->format('M d, Y h:i A') }}</div>
                    </div>
                    <div class="price-item">
                        <div class="price-label">Updated</div>
                        <div class="price-value-sm">{{ $product->updated_at->setTimezone('Asia/Kolkata')->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            </div>

            <!-- Additional Details for Simple Product -->
            <div class="section-card">
                <div class="section-header">Additional Details</div>
                <div class="info-table">
                    <div class="info-row">
                        <span class="info-label">Cost Price:</span>
                        <span class="info-value">₹{{ number_format($product->cost_price, 2) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dealer Price:</span>
                        <span class="info-value">₹{{ number_format($product->dealer_price, 2) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Distributor Price:</span>
                        <span class="info-value">₹{{ number_format($product->distributor_price, 2) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">HSN Code:</span>
                        <span class="info-value">{{ $product->hsn_code ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">GST:</span>
                        <span class="info-value">{{ $product->gst ?? 0 }}%</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Warranty:</span>
                        <span class="info-value">
                            @if($product->warranty_duration && $product->warranty_unit)
                                {{ $product->warranty_duration }} {{ $product->warranty_unit }}(s)
                            @else
                                No warranty
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Barcode Symbology:</span>
                        <span class="info-value">{{ $product->barcode_symbology ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            @else
            <!-- Pricing & Stock for Variant Product - Tabbed -->
            <div class="section-card">
                <div class="section-header">Pricing & Stock - Variants</div>
                <div class="variant-tabs">
                    <div class="variant-tabs-header">
                        @foreach($product->variants as $index => $variant)
                        <button class="variant-tab-btn {{ $index === 0 ? 'active' : '' }}"
                                onclick="switchVariantTab({{ $index }})"
                                data-tab="{{ $index }}">
                            {{ $variant['name'] ?? 'Variant ' . ($index + 1) }}
                        </button>
                        @endforeach
                    </div>
                    <div class="variant-tabs-content">
                        @foreach($product->variants as $index => $variant)
                        <div class="variant-tab-pane {{ $index === 0 ? 'active' : '' }}" id="variant-tab-{{ $index }}">
                            <!-- Variant Attributes -->
                            <div class="variant-attributes-section">
                                <div class="variant-attr-label">Variant Attributes:</div>
                                <div class="variant-attr-values">
                                    @if(isset($variant['attributes']) && is_array($variant['attributes']))
                                        @foreach($variant['attributes'] as $attr)
                                            <span class="attr-item">
                                                <span class="attr-type">{{ ucfirst($attr['type'] ?? 'Attribute') }}:</span>
                                                <span class="attr-value">{{ $attr['value'] ?? 'N/A' }}</span>
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <!-- Pricing Grid -->
                            <div class="pricing-grid variant-pricing">
                                <div class="price-item">
                                    <div class="price-label">SKU</div>
                                    <div class="price-value-sku">{{ $variant['sku_code'] ?? 'N/A' }}</div>
                                </div>
                                <div class="price-item">
                                    <div class="price-label">MRP Price</div>
                                    <div class="price-value">₹{{ number_format($variant['mrp_price'] ?? 0, 2) }}</div>
                                </div>
                                <div class="price-item">
                                    <div class="price-label">Sale Price</div>
                                    <div class="price-value">₹{{ number_format($variant['sale_price'] ?? 0, 2) }}</div>
                                </div>
                                <div class="price-item">
                                    <div class="price-label">Discount</div>
                                    <div class="price-value">
                                        @php
                                            $mrp = $variant['mrp_price'] ?? 0;
                                            $sale = $variant['sale_price'] ?? 0;
                                            $discount = $mrp > $sale ? round((($mrp - $sale) / $mrp) * 100) : 0;
                                        @endphp
                                        {{ $discount }}%
                                    </div>
                                </div>
                                <div class="price-item">
                                    <div class="price-label">Stock</div>
                                    <div class="price-value">
                                        @php
                                            // Get total stock for this variant from all warehouses
                                            $variantStock = \App\Models\WarehouseStock::where('product_id', $product->_id)
                                                ->where('product_type', 'variant')
                                                ->where('variant_id', (string) ($variant['_id'] ?? ''))
                                                ->sum('quantity');
                                        @endphp
                                        {{ $variantStock }}
                                    </div>
                                </div>
                                <div class="price-item">
                                    <div class="price-label">Min Stock Alert</div>
                                    <div class="price-value">
                                        @php
                                            // Get min stock alert from main warehouse
                                            $mainWarehouse = \App\Models\Warehouse::main()->first();
                                            if ($mainWarehouse) {
                                                $warehouseStock = \App\Models\WarehouseStock::where('product_id', $product->_id)
                                                    ->where('product_type', 'variant')
                                                    ->where('variant_id', (string) ($variant['_id'] ?? ''))
                                                    ->where('warehouse_id', $mainWarehouse->id)
                                                    ->first();
                                                $minAlert = $warehouseStock ? $warehouseStock->min_stock_alert : 0;
                                            } else {
                                                $minAlert = 0;
                                            }
                                        @endphp
                                        @if($minAlert > 0)
                                            {{ $minAlert }}
                                        @else
                                            Not Set
                                        @endif
                                    </div>
                                </div>
                                <div class="price-item">
                                    <div class="price-label">Unit</div>
                                    <div class="price-value">{{ $variant['unit'] ?? $product->unit ?? 'pcs' }}</div>
                                </div>
                                <div class="price-item">
                                    <div class="price-label">Cost Price</div>
                                    <div class="price-value">₹{{ number_format($variant['cost_price'] ?? 0, 2) }}</div>
                                </div>
                                <div class="price-item">
                                    <div class="price-label">Dealer Price</div>
                                    <div class="price-value">₹{{ number_format($variant['dealer_price'] ?? 0, 2) }}</div>
                                </div>
                                <div class="price-item">
                                    <div class="price-label">Distributor Price</div>
                                    <div class="price-value">₹{{ number_format($variant['distributor_price'] ?? 0, 2) }}</div>
                                </div>
                                <div class="price-item">
                                    <div class="price-label">Barcode Symbology</div>
                                    <div class="price-value">{{ $variant['barcode_symbology'] ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Additional Details for Variant Product -->
            <div class="section-card">
                <div class="section-header">Product Details</div>
                <div class="info-table">
                    <div class="info-row">
                        <span class="info-label">HSN Code:</span>
                        <span class="info-value">{{ $product->hsn_code ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">GST:</span>
                        <span class="info-value">{{ $product->gst ?? 0 }}%</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Warranty:</span>
                        <span class="info-value">
                            @if($product->warranty_duration && $product->warranty_unit)
                                {{ $product->warranty_duration }} {{ $product->warranty_unit }}(s)
                            @else
                                No warranty
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Created:</span>
                        <span class="info-value">{{ $product->created_at->setTimezone('Asia/Kolkata')->format('M d, Y h:i A') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Updated:</span>
                        <span class="info-value">{{ $product->updated_at->setTimezone('Asia/Kolkata')->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
            </div>
            @endif

            @if($product->long_description)
            <div class="section-card">
                <div class="section-header">Full Description</div>
                <div class="full-description">
                    {{ $product->long_description }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Base Styles */
    .product-details-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 12px;
        line-height: 1.4;
        color: #374151;
        max-width: 1200px;
        margin: 0 auto;
        padding: 12px;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .page-title {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 8px;
    }

    .btn-back, .btn-edit {
        padding: 6px 12px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        background: white;
        color: #374151;
    }

    .btn-back:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .btn-edit {
        background: #f98824;
        color: white;
    }



    /* Layout Grid */
    .details-layout {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 16px;
    }

    .left-column, .right-column {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* Section Card */
    .section-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        overflow: hidden;
    }

    .section-header {
        padding: 10px 14px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        font-size: 12px;
        font-weight: 600;
        color: #1f2937;
    }

    /* Image Preview */
    .image-preview {
        padding: 14px;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #f9fafb;
    }

    .image-preview img {
        max-width: 100%;
        height: 200px;
        object-fit: contain;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
        background: white;
    }

    /* Gallery Grid */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        padding: 12px;
    }

    .gallery-thumb {
        aspect-ratio: 1;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s;
        background: #f9fafb;
    }

    .gallery-thumb:hover {
        border-color: #3b82f6;
        transform: scale(1.05);
    }

    .gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Info Table */
    .info-table {
        padding: 12px 14px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-label {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
    }

    .info-value {
        font-size: 12px;
        color: #1f2937;
        font-weight: 600;
        text-align: right;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #f3f4f6;
        color: #6b7280;
    }

    /* Description Section */
    .description-section {
        padding: 0 14px 12px 14px;
        border-top: 1px solid #f3f4f6;
        margin-top: 8px;
        padding-top: 12px;
    }

    .desc-label {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .desc-text {
        font-size: 12px;
        color: #4b5563;
        line-height: 1.5;
    }

    /* Pricing Grid */
    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        padding: 12px 14px;
    }

    .pricing-grid.variant-pricing {
        grid-template-columns: repeat(4, 1fr);
    }

    .price-item {
        text-align: center;
        padding: 10px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    .price-label {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 6px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .price-value {
        font-size: 13px;
        color: #1f2937;
        font-weight: 700;
    }

    .price-value-sm {
        font-size: 11px;
        color: #1f2937;
        font-weight: 600;
    }

    .price-value-sku {
        font-size: 11px;
        color: #6b21a8;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        background: #f3e8ff;
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-block;
    }

    /* Variant Tabs */
    .variant-tabs {
        display: flex;
        flex-direction: column;
    }

    .variant-tabs-header {
        display: flex;
        gap: 4px;
        padding: 10px 14px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        overflow-x: auto;
    }

    .variant-tab-btn {
        padding: 6px 12px;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .variant-tab-btn:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .variant-tab-btn.active {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .variant-tabs-content {
        padding: 14px;
    }

    .variant-tab-pane {
        display: none;
    }

    .variant-tab-pane.active {
        display: block;
    }

    /* Variant Attributes Section */
    .variant-attributes-section {
        margin-bottom: 16px;
        padding: 12px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    .variant-attr-label {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .variant-attr-values {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .attr-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        font-size: 11px;
    }

    .attr-type {
        color: #6b7280;
        font-weight: 500;
    }

    .attr-value {
        color: #1f2937;
        font-weight: 600;
    }

    /* Full Description */
    .full-description {
        padding: 12px 14px;
        font-size: 12px;
        line-height: 1.6;
        color: #4b5563;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .details-layout {
            grid-template-columns: 1fr;
        }

        .page-header {
            flex-direction: column;
            gap: 12px;
            align-items: stretch;
        }

        .header-actions {
            justify-content: space-between;
        }

        .pricing-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .pricing-grid.variant-pricing {
            grid-template-columns: repeat(2, 1fr);
        }

        .gallery-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .variant-tabs-header {
            overflow-x: auto;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Change main image on gallery click
    function changeMainImage(src) {
        document.getElementById('mainImage').src = src;
    }

    // Switch variant tabs
    function switchVariantTab(tabIndex) {
        // Remove active class from all buttons and panes
        document.querySelectorAll('.variant-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelectorAll('.variant-tab-pane').forEach(pane => {
            pane.classList.remove('active');
        });

        // Add active class to selected button and pane
        const selectedBtn = document.querySelector(`[data-tab="${tabIndex}"]`);
        const selectedPane = document.getElementById(`variant-tab-${tabIndex}`);

        if (selectedBtn) selectedBtn.classList.add('active');
        if (selectedPane) selectedPane.classList.add('active');
    }
</script>
@endpush
@endsection
