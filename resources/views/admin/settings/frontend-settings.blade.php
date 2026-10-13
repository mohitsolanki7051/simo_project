@extends('layouts.admin')

@section('title', 'Frontend Website Settings - Admin Panel')
@section('header-title', 'Frontend Settings')

@push('styles')
<style>
    .settings-container {
        max-width: 1100px;
        margin: 0 auto;
        padding-bottom: 3rem;
    }
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1816;
    }
    .page-subtitle {
        font-size: 0.875rem;
        color: #6b6560;
        margin-top: 0.2rem;
    }
    .settings-card {
        background: #ffffff;
        border: 1px solid #e8e5df;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        margin-bottom: 1.75rem;
        overflow: hidden;
    }
    .card-header-bar {
        padding: 1.1rem 1.5rem;
        background: #faf9f6;
        border-bottom: 1px solid #e8e5df;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .card-header-icon {
        width: 36px;
        height: 36px;
        background: #fff4ec;
        color: #f98824;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
    .card-header-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1816;
    }
    .card-header-desc {
        font-size: 0.8rem;
        color: #6b6560;
    }
    .card-body-content {
        padding: 1.5rem;
    }
    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }
    .form-grid-1 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    .form-group-item {
        margin-bottom: 1.25rem;
    }
    .form-label-custom {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #1a1816;
        margin-bottom: 0.4rem;
    }
    .form-label-custom.required::after {
        content: " *";
        color: #dc2626;
    }
    .form-input-custom {
        width: 100%;
        padding: 0.65rem 0.9rem;
        border: 1.5px solid #e8e5df;
        border-radius: 8px;
        font-size: 0.9rem;
        font-family: inherit;
        outline: none;
        transition: border-color 0.2s;
    }
    .form-input-custom:focus {
        border-color: #f98824;
        box-shadow: 0 0 0 3px rgba(249, 136, 36, 0.12);
    }
    .file-upload-box {
        border: 2px dashed #e8e5df;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        background: #fdfdfc;
        transition: border-color 0.2s;
        position: relative;
    }
    .file-upload-box:hover {
        border-color: #f98824;
    }
    .file-upload-icon {
        font-size: 2rem;
        color: #f98824;
        margin-bottom: 0.5rem;
    }
    .preview-box {
        margin-top: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        background: #f5f4f0;
        border-radius: 8px;
    }
    .preview-img {
        max-height: 50px;
        max-width: 120px;
        object-fit: contain;

    }
    .btn-save-settings {
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #ffffff;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 14px rgba(247, 147, 30, 0.3);
        transition: all 0.2s;
    }
    .btn-save-settings:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(247, 147, 30, 0.4);
    }
    .alert-banner {
        padding: 0.85rem 1.25rem;
        border-radius: 8px;
        margin-bottom: 1.25rem;
        font-size: 0.9rem;
        display: none;
    }
    .alert-banner.success {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .alert-banner.error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
</style>
@endpush

@section('content')
<div class="settings-container">
    <div class="page-header">
        <div>
            <h2 class="page-title">Frontend Website Settings</h2>
            <p class="page-subtitle">Manage website logo, downloadable catalogue PDF, contact info & homepage content</p>
        </div>
        <div>
            <a href="{{ route('frontend.home') }}" target="_blank" class="btn-save-settings" style="background: #111827; box-shadow: none;">
                🌐 View Live Website
            </a>
        </div>
    </div>

    <div class="alert-banner" id="statusAlert"></div>

    <form id="frontendSettingsForm" enctype="multipart/form-data">
        @csrf

        <!-- 1. BRANDING & FILES UPLOAD -->
        <div class="settings-card">
            <div class="card-header-bar">
                <div class="card-header-icon">🎨</div>
                <div>
                    <div class="card-header-title">Branding & Downloads</div>
                    <div class="card-header-desc">Upload company logo & product catalogue PDF</div>
                </div>
            </div>
            <div class="card-body-content">
                <div class="form-grid-2">
                    <!-- Website Logo Upload -->
                    <div class="form-group-item">
                        <label class="form-label-custom">Website Header Logo</label>
                        <div class="file-upload-box">
                            <div class="file-upload-icon">🖼️</div>
                            <p style="font-size: 0.85rem; font-weight: 600;">Upload Brand Logo Image</p>
                            <p style="font-size: 0.75rem; color: #6b6560; margin-bottom: 0.75rem;">PNG, JPG, SVG, WEBP up to 4MB</p>
                            <input type="file" name="logo" id="logoInput" accept="image/*" class="form-input-custom">
                            @if(isset($setting) && $setting->logo_url)
                            <div class="preview-box" id="logoPreviewBox">
                                <img src="{{ $setting->logo_url }}" alt="Logo" class="preview-img">
                                <div style="flex:1; text-align: left; font-size: 0.8rem;">
                                    <strong>Current Logo</strong>
                                </div>
                                <label style="font-size: 0.75rem; color: #dc2626; cursor: pointer;">
                                    <input type="checkbox" name="remove_logo" value="1"> Remove
                                </label>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Catalogue PDF Upload -->
                    <div class="form-group-item">
                        <label class="form-label-custom">Product Catalogue PDF File</label>
                        <div class="file-upload-box">
                            <div class="file-upload-icon">📄</div>
                            <p style="font-size: 0.85rem; font-weight: 600;">Upload Catalogue Brochure (PDF)</p>
                            <p style="font-size: 0.75rem; color: #6b6560; margin-bottom: 0.75rem;">PDF file up to 25MB for users to download</p>
                            <input type="file" name="catalogue_pdf" id="pdfInput" accept="application/pdf" class="form-input-custom">
                            @if(isset($setting) && $setting->catalogue_pdf_url)
                            <div class="preview-box" id="pdfPreviewBox">
                                <span style="font-size: 1.5rem;">📁</span>
                                <div style="flex:1; text-align: left; font-size: 0.8rem;">
                                    <strong>Current PDF File</strong><br>
                                    <a href="{{ $setting->catalogue_pdf_url }}" target="_blank" style="color: #f98824;">View/Download File</a>
                                </div>
                                <label style="font-size: 0.75rem; color: #dc2626; cursor: pointer;">
                                    <input type="checkbox" name="remove_catalogue_pdf" value="1"> Remove
                                </label>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. COMPANY & CONTACT DETAILS -->
        <div class="settings-card">
            <div class="card-header-bar">
                <div class="card-header-icon">📞</div>
                <div>
                    <div class="card-header-title">Company & Contact Info</div>
                    <div class="card-header-desc">Control company name, phone, email, address & hours displayed on header and footer</div>
                </div>
            </div>
            <div class="card-body-content">
                <div class="form-grid-2">
                    <div class="form-group-item">
                        <label class="form-label-custom required">Company / Brand Name</label>
                        <input type="text" name="company_name" class="form-input-custom" value="{{ $setting->company_name ?? 'Simko' }}" required>
                    </div>
                    <div class="form-group-item">
                        <label class="form-label-custom">Brand Tagline</label>
                        <input type="text" name="brand_tagline" class="form-input-custom" value="{{ $setting->brand_tagline ?? 'Light | Smart | Innovation' }}">
                    </div>
                    <div class="form-group-item">
                        <label class="form-label-custom required">Contact Phone Number</label>
                        <input type="text" name="phone_number" class="form-input-custom" value="{{ $setting->phone_number ?? '+91 98765 43210' }}" required>
                    </div>
                    <div class="form-group-item">
                        <label class="form-label-custom required">Contact Email Address</label>
                        <input type="email" name="email_address" class="form-input-custom" value="{{ $setting->email_address ?? 'info@simkolights.com' }}" required>
                    </div>
                    <div class="form-group-item">
                        <label class="form-label-custom required">City / State / Location Address</label>
                        <input type="text" name="location_address" class="form-input-custom" value="{{ $setting->location_address ?? 'Jaipur, Rajasthan, India' }}" required>
                    </div>
                    <div class="form-group-item">
                        <label class="form-label-custom">Working Hours</label>
                        <input type="text" name="working_hours" class="form-input-custom" value="{{ $setting->working_hours ?? 'Mon - Sat: 9:00 AM - 6:00 PM' }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. HOMEPAGE HERO CONTENT -->
        <div class="settings-card">
            <div class="card-header-bar">
                <div class="card-header-icon">✨</div>
                <div>
                    <div class="card-header-title">Homepage Content Editor</div>
                    <div class="card-header-desc">Customize the main hero banner headline, tag, and description</div>
                </div>
            </div>
            <div class="card-body-content">
                <div class="form-grid-2">
                    <div class="form-group-item">
                        <label class="form-label-custom">Hero Subtitle Tag</label>
                        <input type="text" name="hero_subtitle" class="form-input-custom" value="{{ $setting->hero_subtitle ?? 'Illuminate a Brighter Tomorrow' }}">
                    </div>
                    <div class="form-group-item">
                        <label class="form-label-custom">Hero Main Headline</label>
                        <input type="text" name="hero_headline" class="form-input-custom" value="{{ $setting->hero_headline ?? 'Lighting for a Better Life' }}">
                    </div>
                </div>
                <div class="form-group-item">
                    <label class="form-label-custom">Hero Main Description Text</label>
                    <textarea name="hero_description" class="form-input-custom" rows="3">{{ $setting->hero_description ?? 'High-quality, energy-efficient and beautifully designed lighting solutions for homes, offices and modern spaces.' }}</textarea>
                </div>
            </div>
        </div>

        <!-- 4. SOCIAL MEDIA LINKS -->
        <div class="settings-card">
            <div class="card-header-bar">
                <div class="card-header-icon">🌐</div>
                <div>
                    <div class="card-header-title">Social Media Links</div>
                    <div class="card-header-desc">Footer social profile links</div>
                </div>
            </div>
            <div class="card-body-content">
                <div class="form-grid-2">
                    <div class="form-group-item">
                        <label class="form-label-custom">Facebook URL</label>
                        <input type="text" name="facebook_url" class="form-input-custom" value="{{ $setting->facebook_url ?? '#' }}">
                    </div>
                    <div class="form-group-item">
                        <label class="form-label-custom">Instagram URL</label>
                        <input type="text" name="instagram_url" class="form-input-custom" value="{{ $setting->instagram_url ?? '#' }}">
                    </div>
                    <div class="form-group-item">
                        <label class="form-label-custom">YouTube Channel URL</label>
                        <input type="text" name="youtube_url" class="form-input-custom" value="{{ $setting->youtube_url ?? '#' }}">
                    </div>
                    <div class="form-group-item">
                        <label class="form-label-custom">LinkedIn URL</label>
                        <input type="text" name="linkedin_url" class="form-input-custom" value="{{ $setting->linkedin_url ?? '#' }}">
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn-save-settings" id="saveBtn">
                💾 Save Frontend Settings
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('frontendSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const alertBox = document.getElementById('statusAlert');
        const btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.innerHTML = '⏳ Saving Changes...';

        const formData = new FormData(this);

        fetch("{{ route('admin.frontend-settings.store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '💾 Save Frontend Settings';
            alertBox.style.display = 'block';
            if(data.success) {
                alertBox.className = 'alert-banner success';
                alertBox.innerHTML = '✅ ' + data.message;
                setTimeout(() => { location.reload(); }, 1200);
            } else {
                alertBox.className = 'alert-banner error';
                alertBox.innerHTML = '❌ ' + (data.message || 'Error saving settings');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '💾 Save Frontend Settings';
            alertBox.style.display = 'block';
            alertBox.className = 'alert-banner error';
            alertBox.innerHTML = '❌ Error submitting settings form.';
        });
    });
</script>
@endpush
