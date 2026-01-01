@extends('layouts.admin')

@section('title', 'Edit Product - Admin Panel')
@section('header-title', 'Edit Product: ' . $product->name)

@section('content')
<div class="form-container">
    <form action="{{ url('/admin/products/' . $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <!-- Product Name -->
            <div class="form-group">
                <label class="form-label" for="name">Product Name *</label>
                <input type="text" class="form-input" id="name" name="name"
                       value="{{ old('name', $product->name) }}" required>
            </div>

            <!-- Price -->
            <div class="form-group">
                <label class="form-label" for="price">Price (₹) *</label>
                <input type="number" step="0.01" class="form-input" id="price" name="price"
                       value="{{ old('price', $product->price) }}" required>
            </div>

            <!-- Category -->
            <div class="form-group">
                <label class="form-label" for="category_id">Category *</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ (old('category_id', $product->category_id) == $category->id) ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Brand -->
            <div class="form-group">
                <label class="form-label" for="brand_id">Brand *</label>
                <select class="form-select" id="brand_id" name="brand_id" required>
                    <option value="">Select Brand</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ (old('brand_id', $product->brand_id) == $brand->id) ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- HSN Code -->
            <div class="form-group">
                <label class="form-label" for="hsn_code">HSN Code *</label>
                <input type="text" class="form-input" id="hsn_code" name="hsn_code"
                       value="{{ old('hsn_code', $product->hsn_code) }}" required>
            </div>

            <!-- GST -->
            <div class="form-group">
                <label class="form-label" for="gst">GST (%) *</label>
                <input type="number" step="0.01" class="form-input" id="gst" name="gst"
                       value="{{ old('gst', $product->gst) }}" required>
            </div>

            <!-- Warranty -->
            <div class="form-group">
                <label class="form-label" for="warranty">Warranty *</label>
                <input type="text" class="form-input" id="warranty" name="warranty"
                       value="{{ old('warranty', $product->warranty) }}" required>
            </div>

            <!-- Body Type -->
            <div class="form-group">
                <label class="form-label" for="body_type">Body Type *</label>
                <input type="text" class="form-input" id="body_type" name="body_type"
                       value="{{ old('body_type', $product->body_type) }}" required>
            </div>

            <!-- Short Description -->
            <div class="form-group full-width">
                <label class="form-label" for="short_description">Short Description *</label>
                <textarea class="form-textarea" id="short_description" name="short_description"
                          rows="3" required>{{ old('short_description', $product->short_description) }}</textarea>
            </div>

            <!-- Full Description -->
            <div class="form-group full-width">
                <label class="form-label" for="description">Full Description *</label>
                <textarea class="form-textarea" id="description" name="description"
                          rows="5" required>{{ old('description', $product->description) }}</textarea>
            </div>

            <!-- Base Image -->
            <div class="form-group full-width">
                <label class="form-label" for="base_image">Base Image</label>
                <input type="file" class="form-input" id="base_image" name="base_image"
                       accept="image/*" onchange="previewBaseImage(event)">
                <div class="image-preview-container" id="baseImagePreview">
                    @if($product->base_image)
                    <img src="{{ asset('storage/' . $product->base_image) }}"
                         alt="{{ $product->name }}"
                         class="image-preview">
                    @endif
                </div>
                <small>Leave empty to keep current image</small>
            </div>

            <!-- Gallery Images -->
            <div class="form-group full-width">
                <label class="form-label" for="gallery_images">Gallery Images</label>
                <input type="file" class="form-input" id="gallery_images" name="gallery_images[]"
                       accept="image/*" multiple onchange="previewGalleryImages(event)">
                <div class="gallery-preview" id="galleryPreview">
                    @if($product->gallery_images)
                        @foreach($product->gallery_images as $image)
                        <div class="gallery-item-container">
                            <img src="{{ asset('storage/' . $image) }}"
                                 alt="Gallery Image"
                                 class="gallery-item">
                        </div>
                        @endforeach
                    @endif
                </div>
                <small>Leave empty to keep current images</small>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label" for="status">Status *</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="active" {{ (old('status', $product->status) == 'active') ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ (old('status', $product->status) == 'inactive') ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ url('/admin/products') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-primary">Update Product</button>
        </div>
    </form>
</div>
@endsection
