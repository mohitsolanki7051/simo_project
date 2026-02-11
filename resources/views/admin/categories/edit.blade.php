@extends('layouts.admin')

@section('title', 'Edit Category - Admin Panel')
@section('header-title', 'Categories Management')

@section('content')
<div class="products-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Edit Category</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.categories.index') }}" class="btn-small btn-cancel">
                <span class="btn-icon">←</span> Back to Categories
            </a>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-wrapper">
        <form action="{{ route('admin.categories.update', $category->_id) }}" method="POST" enctype="multipart/form-data" id="categoryForm">
            @csrf
            @method('PUT')

            <!-- Basic Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h3 class="card-title">Category Information</h3>
                    <div class="card-subtitle">Update category details</div>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <!-- Category Name -->
                        <div class="form-group">
                            <label class="form-label">
                                <span class="label-text">Category Name</span>
                                <span class="required">*</span>
                            </label>
                            <input type="text"
                                   class="form-input @error('name') error @enderror"
                                   name="name"
                                   value="{{ old('name', $category->name) }}"
                                   placeholder="e.g., LED Lights"
                                   required
                                   id="nameInput">
                            @error('name')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div class="form-group">
                            <label class="form-label">
                                <span class="label-text">Slug</span>
                            </label>
                            <input type="text"
                                   class="form-input @error('slug') error @enderror"
                                   name="slug"
                                   value="{{ old('slug', $category->slug) }}"
                                   placeholder="e.g., led-lights"
                                   id="slugInput">
                            @error('slug')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label class="form-label">
                                <span class="label-text">Status</span>
                                <span class="required">*</span>
                            </label>
                            <select class="form-select @error('status') error @enderror" name="status" required>
                                <option value="">Select Status</option>
                                <option value="active" {{ old('status', $category->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $category->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div></div>

                        <!-- Image Upload Section -->
                       <div class="form-group image-desc">
                            <label class="form-label">
                                <span class="label-text">Category Image</span>
                            </label>

                            <!-- Upload Area (UPER) -->
                            <div class="image-upload-section">
                                <input type="file"
                                       class="form-input-file @error('image') error @enderror"
                                       id="category_image"
                                       name="image"
                                       accept="image/*"
                                       onchange="previewImage(event)"
                                       hidden>
                                <label for="category_image" class="upload-label">
                                    <div class="upload-icon">🖼️</div>
                                    <div class="upload-text">{{ $category->image ? 'Change Image' : 'Click to upload image' }}</div>
                                    <div class="upload-hint">PNG, JPG, JPEG, GIF up to 2MB</div>
                                </label>
                                <input type="hidden" name="remove_image" id="removeImageInput" value="0">
                                @error('image')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Current Image Preview (NICHE) - SMALL with X -->
                            @if($category->image)
                            <div class="current-image-preview" id="currentImagePreview">
                                <div class="preview-label">Current Image</div>
                                <div class="image-container">
                                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="current-image">
                                    <button type="button" class="remove-x-btn" onclick="removeCurrentImage()" title="Remove Image">×</button>
                                </div>
                            </div>
                            @endif

                            <!-- New Image Preview (NICHE) - SMALL with X -->
                            <div class="image-preview-container" id="imagePreview"></div>
                        </div>

                        <!-- Description -->
                       <div class="form-group image-desc">
                            <label class="form-label">
                                <span class="label-text">Description</span>
                            </label>
                            <textarea class="form-textarea @error('description') error @enderror"
                                      name="description"
                                      rows="4"
                                      placeholder="Enter category description">{{ old('description', $category->description) }}</textarea>
                            @error('description')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.categories.index') }}" class="btn-action btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-action btn-primary">
                    <span class="submit-icon">✓</span> Update Category
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    /* Main Container */
    .products-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 12px;
        line-height: 1.4;
        min-height: 100vh;
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    }

    /* Image + Description same row balance */
    .form-grid .form-group:nth-last-child(2),
    .form-grid .form-group:nth-last-child(1) {
        align-self: stretch;
    }

    /* Description textarea height match image */
    .form-textarea {
        min-height: 140px;
    }

    /* Force image + description same row */
    .form-group.image-desc {
        align-self: stretch;
    }

    /* Match height */
    .form-group.image-desc .form-textarea {
        min-height: 140px;
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
        gap: 10px;
        margin-bottom: 0;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group.full-width {
        grid-column: span 2;
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

    .form-input, .form-textarea, .form-select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 12px;
        font-family: inherit;
        transition: all 0.2s;
        background: white;
        box-sizing: border-box;
    }

    .form-input:focus, .form-textarea:focus, .form-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: white;
    }

    .form-input.error, .form-textarea.error, .form-select.error {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .form-textarea {
        min-height: 100px;
        resize: vertical;
    }

    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    .form-error {
        color: #ef4444;
        font-size: 11px;
        margin-top: 4px;
        font-weight: 500;
    }

    .form-hint {
        margin-top: 6px;
        font-size: 11px;
        color: #9ca3af;
        line-height: 1.4;
    }

    /* Image Upload Section */
    .image-upload-section {
        margin-bottom: 20px;
    }

    .upload-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 25px;
        border: 2px dashed #d1d5db;
        border-radius: 10px;
        background: #f9fafb;
        cursor: pointer;
        transition: all 0.3s;
    }

    .upload-label:hover {
        border-color: #fa8427;
        background: rgba(250, 132, 39, 0.05);
        transform: translateY(-2px);
    }

    .upload-icon {
        font-size: 28px;
        margin-bottom: 8px;
        opacity: 0.7;
    }

    .upload-text {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }

    .upload-hint {
        font-size: 11px;
        color: #9ca3af;
    }

    /* Current Image Preview - SMALL with ONE X sign */
    .current-image-preview {
        margin-top: 15px;
    }

    .preview-label {
        font-size: 11px;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .image-container {
        position: relative;
        display: inline-block;
    }

    .current-image {
        width: 80px; /* SMALL SIZE */
        height: 80px; /* SMALL SIZE */
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .remove-x-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 24px;
        height: 24px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 10;
        padding: 0;
        line-height: 1;
    }

    .remove-x-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    /* New Image Preview - SMALL with ONE X sign */
    .image-preview-container {
        margin-top: 15px;
    }

    .new-preview-item {
        display: inline-block;
    }

    .new-preview-label {
        font-size: 11px;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .new-image-container {
        position: relative;
        display: inline-block;
    }

    .new-preview-image {
        width: 80px; /* SMALL SIZE - same as current image */
        height: 80px; /* SMALL SIZE - same as current image */
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .new-remove-x-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 24px;
        height: 24px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 10;
        padding: 0;
        line-height: 1;
    }

    .new-remove-x-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding: 10px;
        background: white;
        border-top: 1px solid #e5e7eb;
        border-radius: 0 0 12px 12px;
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

    .submit-icon {
        font-size: 14px;
        font-weight: bold;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .products-container {
            padding: 15px;
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

        .form-group.full-width {
            grid-column: span 1;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
        }

        .current-image,
        .new-preview-image {
            width: 70px;
            height: 70px;
        }
    }

    @media (max-width: 480px) {
        .products-container {
            padding: 12px;
        }

        .card-body {
            padding: 20px;
        }

        .current-image,
        .new-preview-image {
            width: 60px;
            height: 60px;
        }

        .remove-x-btn,
        .new-remove-x-btn {
            width: 20px;
            height: 20px;
            font-size: 14px;
            top: -6px;
            right: -6px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    // Image preview for new image
    function previewImage(event) {
        const input = event.target;
        const container = document.getElementById('imagePreview');
        container.innerHTML = '';

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            const maxSize = 2 * 1024 * 1024; // 2MB

            // Check file type
            if (!validTypes.includes(file.type)) {
                showAlert('Please upload only JPEG, PNG, JPG or GIF images', 'error');
                input.value = '';
                return;
            }

            // Check file size
            if (file.size > maxSize) {
                showAlert('Image size should be less than 2MB', 'error');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'new-preview-item';
                div.innerHTML = `
                    <div class="new-preview-label">New Image Preview</div>
                    <div class="new-image-container">
                        <img src="${e.target.result}" class="new-preview-image">
                        <button type="button" class="new-remove-x-btn" onclick="removeNewImage()" title="Remove Image">×</button>
                    </div>
                `;
                container.appendChild(div);

                // Hide current image preview when new image is uploaded
                const currentImagePreview = document.getElementById('currentImagePreview');
                if (currentImagePreview) {
                    currentImagePreview.style.display = 'none';
                }
            }
            reader.readAsDataURL(file);
        }
    }

    function removeNewImage() {
        document.getElementById('category_image').value = '';
        document.getElementById('imagePreview').innerHTML = '';

        // Show current image preview again
        const currentImagePreview = document.getElementById('currentImagePreview');
        if (currentImagePreview) {
            currentImagePreview.style.display = 'block';
        }
    }

    // Remove current image
    function removeCurrentImage() {
        const removeInput = document.getElementById('removeImageInput');
        removeInput.value = '1';

        const currentImagePreview = document.getElementById('currentImagePreview');
        if (currentImagePreview) {
            currentImagePreview.style.display = 'none';
        }

        showAlert('Current image will be removed on save', 'success');
    }

    // Auto-generate slug from name - IMPROVED VERSION
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('nameInput');
        const slugInput = document.getElementById('slugInput');

        // Auto-focus on name input
        nameInput.focus();

        // Store original values for comparison
        const originalName = nameInput.value;
        const originalSlug = slugInput.value;

        // Track if slug was manually modified by user
        let slugManuallyModified = slugInput.value !== '';

        // Check if slug matches name (auto-generated)
        const nameSlug = originalName.toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/[-\s]+/g, '-')
            .replace(/^-+|-+$/g, '');

        // If slug matches auto-generated slug from name, it's not manually modified
        if (originalSlug === nameSlug) {
            slugManuallyModified = false;
        }

        // Auto-generate slug when name changes
        nameInput.addEventListener('input', function(e) {
            const name = e.target.value.trim();

            // If name is empty, clear slug
            if (!name) {
                slugInput.value = '';
                slugManuallyModified = false;
                return;
            }

            // Only auto-generate if slug hasn't been manually modified
            if (!slugManuallyModified) {
                let slug = name
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/[-\s]+/g, '-')
                    .replace(/^-+|-+$/g, '');

                slugInput.value = slug;
            }
        });

        // Mark slug as manually modified when user types in it
        slugInput.addEventListener('input', function() {
            slugManuallyModified = true;
        });

        // Clear manual flag if user clears the slug field
        slugInput.addEventListener('blur', function() {
            if (!this.value.trim()) {
                slugManuallyModified = false;
            }
        });

        // Reset to auto-generation if name and slug become same
        nameInput.addEventListener('blur', function() {
            const name = this.value.trim();
            const slug = slugInput.value.trim();

            if (name && slug) {
                const nameSlug = name.toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/[-\s]+/g, '-')
                    .replace(/^-+|-+$/g, '');

                if (slug === nameSlug) {
                    slugManuallyModified = false;
                }
            }
        });
    });

    // Form validation
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        let isValid = true;
        const requiredInputs = document.querySelectorAll('[required]');

        // Reset errors
        document.querySelectorAll('.form-input, .form-select, .form-textarea').forEach(input => {
            input.classList.remove('error');
        });

        // Check required fields
        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            showAlert('Please fill in all required fields', 'error');

            // Scroll to first error
            const firstError = document.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Display validation errors from server
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
