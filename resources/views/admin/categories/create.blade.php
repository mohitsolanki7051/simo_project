@extends('layouts.admin')

@section('title', 'Create Category - Admin Panel')
@section('header-title', 'Create New Category')

@section('content')
<div class="create-category-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('admin.categories.index') }}" class="back-btn">
                <span>←</span> Back to Categories
            </a>
            <h2 class="page-title">Add New Category</h2>
        </div>
    </div>

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" id="categoryForm">
        @csrf

        <div class="form-container">
            <div class="form-section">
                <h3 class="section-title">Category Information</h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Category Name <span class="required">*</span></label>
                        <input type="text" class="form-input" name="name" value="{{ old('name') }}" placeholder="e.g., LED Lights" required>
                        <span class="form-hint">Enter a unique name for this category</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Parent Category</label>
                        <select class="form-select" name="parent_id">
                            <option value="">Select Parent Category</option>
                            @foreach($categories as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                        <span class="form-hint">Leave empty for main category</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select class="form-select" name="status" required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-input" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                        <span class="form-hint">Lower numbers appear first</span>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Category Image</label>
                        <div class="image-upload-wrapper">
                            <input type="file" class="form-input-file" id="category_image" name="image" accept="image/*" onchange="previewImage(event)" hidden>
                            <label for="category_image" class="upload-label">
                                <div class="upload-icon">🖼️</div>
                                <div class="upload-text">Click to upload image</div>
                                <div class="upload-hint">PNG, JPG up to 2MB</div>
                            </label>
                        </div>
                        <div class="image-preview-container" id="imagePreview"></div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Description</label>
                        <textarea class="form-textarea" name="description" rows="4" placeholder="Enter category description">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">SEO Information (Optional)</h3>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Meta Title</label>
                        <input type="text" class="form-input" name="meta_title" value="{{ old('meta_title') }}" placeholder="SEO title for search engines">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Meta Description</label>
                        <textarea class="form-textarea" name="meta_description" rows="3" placeholder="SEO description for search engines">{{ old('meta_description') }}</textarea>
                        <span class="form-hint">Recommended: 150-160 characters</span>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" class="form-input" name="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="comma, separated, keywords">
                        <span class="form-hint">Separate keywords with commas</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route('admin.categories.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-primary">
                <span>✓</span> Create Category
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
    .create-category-container { padding: 30px; max-width: 1000px; margin: 0 auto; }

    /* Alert Styles */
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; margin-bottom: 15px; }
    .back-btn:hover { background: #f7fafc; color: #ff6b35; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; }

    .form-container { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
    .form-section { padding: 30px; border-bottom: 2px solid #e2e8f0; }
    .form-section:last-child { border-bottom: none; }

    .section-title { font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 20px; }

    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: span 2; }
    .form-label { margin-bottom: 8px; font-weight: 600; color: #4a5568; font-size: 14px; }
    .required { color: #fc8181; }
    .form-input, .form-textarea, .form-select { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; font-family: inherit; }
    .form-input:focus, .form-textarea:focus, .form-select:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
    .form-textarea { min-height: 100px; resize: vertical; }
    .form-hint { margin-top: 6px; font-size: 12px; color: #a0aec0; }

    .image-upload-wrapper { margin-bottom: 15px; }
    .upload-label { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px; border: 3px dashed #cbd5e0; border-radius: 12px; background: #f7fafc; cursor: pointer; transition: all 0.3s; }
    .upload-label:hover { border-color: #ff6b35; background: rgba(255,107,53,0.05); }
    .upload-icon { font-size: 36px; margin-bottom: 10px; }
    .upload-text { font-size: 14px; font-weight: 600; color: #2d3748; margin-bottom: 5px; }
    .upload-hint { font-size: 12px; color: #a0aec0; }

    .image-preview-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 15px; }
    .preview-item { position: relative; border-radius: 12px; overflow: hidden; border: 2px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .preview-image { width: 100%; height: 150px; object-fit: cover; display: block; }
    .remove-image { position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; background: #fc8181; color: white; border: none; border-radius: 50%; cursor: pointer; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
    .remove-image:hover { background: #f56565; transform: scale(1.1); }

    .form-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; }
    .btn-cancel { padding: 12px 24px; background: #e2e8f0; color: #4a5568; border: none; border-radius: 10px; font-weight: 600; text-decoration: none; transition: all 0.3s; }
    .btn-cancel:hover { background: #cbd5e0; }
    .btn-primary { padding: 12px 30px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(255,107,53,0.3); display: flex; align-items: center; gap: 8px; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.4); }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
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

    // Image preview
    function previewImage(event) {
        const input = event.target;
        const container = document.getElementById('imagePreview');
        container.innerHTML = '';

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" class="preview-image">
                    <button type="button" class="remove-image" onclick="removeImage()">×</button>
                `;
                container.appendChild(div);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeImage() {
        document.getElementById('category_image').value = '';
        document.getElementById('imagePreview').innerHTML = '';
    }

    // Form validation
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        const requiredInputs = document.querySelectorAll('[required]');
        let isValid = true;

        requiredInputs.forEach(input => {
            input.classList.remove('error');
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            showAlert('Please fill in all required fields', 'error');
        }
    });

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
</script>
@endpush
@endsection
