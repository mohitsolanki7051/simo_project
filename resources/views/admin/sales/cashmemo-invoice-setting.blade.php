@extends('layouts.admin')

@section('title', 'Invoice Settings - Admin Panel')
@section('header-title', 'Invoice Settings')

@section('content')
<div class="products-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Invoice Settings</h2>
            <p>Configure your company details for invoices</p>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="settings-form-wrapper">
        <form id="invoiceSettingsForm" class="invoice-settings-form" enctype="multipart/form-data">
            @csrf
            @if(isset($setting) && $setting->_id)
            <input type="hidden" name="id" value="{{ $setting->_id }}">
            @endif

            <div class="form-sections">
                <!-- Company Information -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-header-left">
                            <div class="section-icon">🏢</div>
                            <div class="section-title">
                                <h3>Company Information</h3>
                                <p>Basic company details for invoice header</p>
                            </div>
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label class="form-label required">Company Name</label>
                                <input type="text"
                                       name="company_name"
                                       class="form-control"
                                       value="{{ $setting->company_name ?? '' }}"
                                       required>
                            </div>
                            <div class="form-group col-6">
                                <label class="form-label required">Company Email</label>
                                <input type="email"
                                       name="company_email"
                                       class="form-control"
                                       value="{{ $setting->company_email ?? '' }}"
                                       required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-6">
                                <label class="form-label required">Company Phone</label>
                                <input type="text"
                                       name="company_phone"
                                       class="form-control"
                                       value="{{ $setting->company_phone ?? '' }}"
                                       required>
                            </div>
                            <div class="form-group col-6">
                                <label class="form-label required">Company Address</label>
                                <textarea name="company_address"
                                          class="form-control"
                                          rows="2"
                                          required>{{ $setting->company_address ?? '' }}</textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Images Section -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-header-left">
                            <div class="section-icon">🖼️</div>
                            <div class="section-title">
                                <h3>Images & Signatures</h3>
                                <p>Upload logo, signature and stamp for invoices</p>
                            </div>
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="form-row">
                            <!-- Logo -->
                            <div class="form-group col-4">
                                <label class="form-label">Company Logo</label>
                                <div class="image-upload-box" id="logoUploadBox">
                                    @if(isset($setting) && $setting->logo_path)
                                    <div class="current-image">
                                        <img src="{{ asset('storage/' . $setting->logo_path) }}?v={{ time() }}"
                                             alt="Current Logo"
                                             class="preview-image">
                                        <div class="image-actions">
                                            <label class="btn-change-image">
                                                <i class="fas fa-sync-alt"></i> Change Logo
                                                <input type="file" name="logo" accept="image/*" style="display: none;" onchange="previewImage(this, 'logoPreview')">
                                            </label>
                                            <div class="form-check">
                                                <input type="checkbox" name="remove_logo" value="1" id="removeLogo">
                                                <label for="removeLogo">Remove Logo</label>
                                            </div>
                                        </div>
                                        <div class="image-info">
                                            <small>Recommended: 200x60 pixels, PNG or JPG</small>
                                        </div>
                                    </div>
                                    @else
                                    <div class="upload-placeholder">
                                        <div class="upload-icon">
                                            <i class="fas fa-image"></i>
                                        </div>
                                        <p class="upload-text">No logo uploaded</p>
                                        <label class="btn-upload-image">Upload Logo
                                            <input type="file" name="logo" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                        </label>
                                        <div class="image-info">
                                            <small>Recommended: 200x60 pixels, PNG or JPG</small>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Signature -->
                            <div class="form-group col-4">
                                <label class="form-label">Authorized Signature</label>
                                <div class="image-upload-box" id="signatureUploadBox">
                                    @if(isset($setting) && $setting->signature_path)
                                    <div class="current-image">
                                        <img src="{{ asset('storage/' . $setting->signature_path) }}?v={{ time() }}"
                                             alt="Current Signature"
                                             class="preview-image">
                                        <div class="image-actions">
                                            <label class="btn-change-image">
                                                <i class="fas fa-sync-alt"></i> Change Signature
                                                <input type="file" name="signature" accept="image/*" style="display: none;" onchange="previewImage(this, 'signaturePreview')">
                                            </label>
                                            <div class="form-check">
                                                <input type="checkbox" name="remove_signature" value="1" id="removeSignature">
                                                <label for="removeSignature">Remove Signature</label>
                                            </div>
                                        </div>
                                        <div class="image-info">
                                            <small>Recommended: 150x50 pixels, Transparent PNG</small>
                                        </div>
                                    </div>
                                    @else
                                    <div class="upload-placeholder">
                                        <div class="upload-icon">
                                            <i class="fas fa-signature"></i>
                                        </div>
                                        <p class="upload-text">No signature uploaded</p>
                                        <label class="btn-upload-image">Upload Signature
                                            <input type="file" name="signature" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                        </label>
                                        <div class="image-info">
                                            <small>Recommended: 150x50 pixels, Transparent PNG</small>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Stamp -->
                            <div class="form-group col-4">
                                <label class="form-label">Company Stamp (Optional)</label>
                                <div class="image-upload-box" id="stampUploadBox">
                                    @if(isset($setting) && $setting->stamp_path)
                                    <div class="current-image">
                                        <img src="{{ asset('storage/' . $setting->stamp_path) }}?v={{ time() }}"
                                             alt="Current Stamp"
                                             class="preview-image">
                                        <div class="image-actions">
                                            <label class="btn-change-image">
                                                <i class="fas fa-sync-alt"></i> Change Stamp
                                                <input type="file" name="stamp" accept="image/*" style="display: none;" onchange="previewImage(this, 'stampPreview')">
                                            </label>
                                            <div class="form-check">
                                                <input type="checkbox" name="remove_stamp" value="1" id="removeStamp">
                                                <label for="removeStamp">Remove Stamp</label>
                                            </div>
                                        </div>
                                        <div class="image-info">
                                            <small>Recommended: 100x100 pixels, PNG with transparency</small>
                                        </div>
                                    </div>
                                    @else
                                    <div class="upload-placeholder">
                                        <div class="upload-icon">
                                            <i class="fas fa-stamp"></i>
                                        </div>
                                        <p class="upload-text">No stamp uploaded</p>
                                        <label class="btn-upload-image">Upload Stamp
                                            <input type="file" name="stamp" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                        </label>
                                        <div class="image-info">
                                            <small>Recommended: 100x100 pixels, PNG with transparency</small>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Details -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-header-left">
                            <div class="section-icon">🏦</div>
                            <div class="section-title">
                                <h3>Bank Details</h3>
                                <p>Bank information for payment instructions</p>
                            </div>
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label class="form-label">Bank Name</label>
                                <input type="text"
                                       name="bank_name"
                                       class="form-control"
                                       value="{{ $setting->bank_name ?? '' }}"
                                       placeholder="e.g., State Bank of India">
                            </div>
                            <div class="form-group col-6">
                                <label class="form-label">Account Holder Name</label>
                                <input type="text"
                                       name="account_name"
                                       class="form-control"
                                       value="{{ $setting->account_name ?? '' }}"
                                       placeholder="e.g., Your Company Name">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-6">
                                <label class="form-label">Account Number</label>
                                <input type="text"
                                       name="account_number"
                                       class="form-control"
                                       value="{{ $setting->account_number ?? '' }}"
                                       placeholder="e.g., 123456789012">
                                <small class="form-text">Enter bank account number</small>
                            </div>
                            <div class="form-group col-6">
                                <label class="form-label">IFSC Code</label>
                                <input type="text"
                                       name="ifsc_code"
                                       class="form-control"
                                       value="{{ $setting->ifsc_code ?? '' }}"
                                       maxlength="11"
                                       placeholder="e.g., SBIN0001234">
                                <small class="form-text">11 character IFSC code</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-12">
                                <label class="form-label">Branch</label>
                                <input type="text"
                                       name="branch"
                                       class="form-control"
                                       value="{{ $setting->branch ?? '' }}"
                                       placeholder="e.g., Main Branch, Mumbai">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms & Footer -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-header-left">
                            <div class="section-icon">📝</div>
                            <div class="section-title">
                                <h3>Terms & Footer</h3>
                                <p>Default terms and footer notes for invoices</p>
                            </div>
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="form-group">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea name="terms_and_conditions"
                                      class="form-control"
                                      rows="4"
                                      placeholder="Enter default terms and conditions for invoices">{{ $setting->terms_and_conditions ?? '' }}</textarea>
                            <small class="form-text">These will appear at the bottom of every invoice</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Footer Note</label>
                            <input type="text"
                                   name="footer_note"
                                   class="form-control"
                                   value="{{ $setting->footer_note ?? 'This is a computer generated invoice' }}"
                                   placeholder="Footer note displayed at the bottom of invoice">
                            <small class="form-text">Short note that appears in invoice footer</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="form-actions">
                <button type="submit" class="btn-submit-settings">
                    <i class="fas fa-save"></i> Save Settings
                </button>
                <button type="button" class="btn-cancel" onclick="window.location.href='{{ route('admin.dashboard') }}'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .products-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 11px;
        line-height: 1.3;
        color: #333;
    }

    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
    }

    .header-left .page-title {
        font-size: 15px;
        font-weight: 600;
        color: #333;
        margin: 0 0 3px 0;
    }

    .header-left p {
        font-size: 10px;
        color: #666;
        margin: 0;
    }

    /* Settings Form */
    .settings-form-wrapper {
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
    }

    .form-sections {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .form-section {
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow: hidden;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        background: #f8f9fa;
        border-bottom: 1px solid #ddd;
    }

    .section-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-icon {
        width: 28px;
        height: 28px;
        background: #666;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 13px;
        flex-shrink: 0;
    }

    .section-title h3 {
        font-size: 13px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .section-title p {
        font-size: 10px;
        color: #666;
        margin: 1px 0 0 0;
    }

    .section-body {
        padding: 15px;
    }

    /* Form Groups */
    .form-group {
        margin-bottom: 12px;
    }

    .form-label {
        display: block;
        font-size: 10px;
        font-weight: 500;
        color: #555;
        margin-bottom: 3px;
    }

    .form-label.required::after {
        content: ' *';
        color: #dc3545;
    }

    .form-control {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
        font-size: 10px;
        background: #fff;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #666;
        box-shadow: 0 0 0 2px rgba(102, 102, 102, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 60px;
    }

    .form-text {
        display: block;
        font-size: 9px;
        color: #666;
        margin-top: 2px;
    }

    /* Form Layout */
    .form-row {
        display: flex;
        gap: 5px;
        margin-bottom: 10px;
    }

    .form-row:last-child {
        margin-bottom: 0;
    }

    .form-row .col-4,
    .form-row .col-6,
    .form-row .col-12 {
        flex: 1;
    }

    .form-row .col-4 {
        flex: 0 0 33.33%;
        max-width: 33.33%;
    }

    .form-row .col-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }

    .form-row .col-12 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    /* Image Upload Styles */
    .image-upload-box {
        border: 2px dashed #ddd;
        border-radius: 4px;
        padding: 15px;
        text-align: center;
        height: 180px;      /* 👈 fixed height */
        width: 100%;
        overflow: hidden;   /* 👈 extra content hide karega */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: #f9f9f9;
        transition: all 0.3s;
    }


    .image-upload-box:hover {
        border-color: #666;
        background: #f0f0f0;
    }

    .upload-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .upload-icon {
        font-size: 24px;
        color: #666;
        opacity: 0.7;
    }

    .upload-icon i {
        font-size: 24px;
    }

    .upload-text {
        font-size: 10px;
        color: #666;
        margin: 0;
    }

    .btn-upload-image {
        padding: 5px 12px;
        background: #555;
        color: white;
        border: none;
        border-radius: 3px;
        font-size: 10px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .btn-upload-image:hover {
        background: #444;
    }

    .btn-upload-image i {
        font-size: 9px;
    }

    .current-image {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .preview-image {
        max-width: 120px;
        max-height: 80px;
        border: 1px solid #ddd;
        padding: 2px;
        background: white;
        margin-bottom: 10px;
        object-fit: contain;
    }

    .image-actions {
        display: flex;
        flex-direction: column;
        gap: 5px;
        align-items: center;
        width: 100%;
    }

    .btn-change-image {
        padding: 4px 10px;
        background: #555;
        color: white;
        border: none;
        border-radius: 3px;
        font-size: 9px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .btn-change-image:hover {
        background: #444;
    }

    .btn-change-image i {
        font-size: 8px;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 3px;
    }

    .form-check input[type="checkbox"] {
        width: 13px;
        height: 13px;
        cursor: pointer;
    }

    .form-check label {
        font-size: 9px;
        color: #666;
        cursor: pointer;
    }

    .image-info {
        margin-top: 5px;
    }

    .image-info small {
        font-size: 8px;
        color: #888;
        display: block;
        text-align: center;
    }

    /* Form Actions */
    .form-actions {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #ddd;
        text-align: right;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-submit-settings {
        padding: 8px 20px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-submit-settings:hover {
        background: #218838;
    }

    .btn-submit-settings i {
        font-size: 10px;
    }

    .btn-cancel {
        padding: 8px 20px;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-cancel:hover {
        background: #5a6268;
    }

    .btn-cancel i {
        font-size: 10px;
    }

    /* Alert Messages */
    #alertContainer {
        position: fixed;
        top: 15px;
        right: 15px;
        z-index: 9999;
    }

    .alert {
        padding: 10px 14px;
        margin-bottom: 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 500;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
        }

        .form-row .col-4,
        .form-row .col-6,
        .form-row .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .image-upload-box {
            min-height: 150px;
        }
    }

    @media (max-width: 480px) {
        .settings-form-wrapper {
            padding: 10px;
        }

        .section-body {
            padding: 12px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-submit-settings,
        .btn-cancel {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function previewImage(input) {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();

    reader.onload = function (e) {
        // sirf image ka src change karo
        const box = input.closest('.image-upload-box');

        let img = box.querySelector('.preview-image');
        if (!img) {
            img = document.createElement('img');
            img.className = 'preview-image';
            box.prepend(img);
        }

        img.src = e.target.result;
    };

    reader.readAsDataURL(file);
}
</script>


<script>
    $(document).ready(function() {


        // Form submission with validation
        $('#invoiceSettingsForm').submit(function(e) {
            e.preventDefault();

            // Clear previous alerts
            $('#alertContainer').empty();

            // Basic validation
            const companyName = $('input[name="company_name"]').val();
            const companyEmail = $('input[name="company_email"]').val();
            const companyPhone = $('input[name="company_phone"]').val();
            const companyAddress = $('textarea[name="company_address"]').val();

            if (!companyName || !companyEmail || !companyPhone || !companyAddress) {
                showAlert('Please fill all required fields marked with *', 'error');
                return false;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(companyEmail)) {
                showAlert('Please enter a valid email address', 'error');
                return false;
            }

            // Phone validation (basic)
            const phoneRegex = /^[0-9]{10}$/;
            if (!phoneRegex.test(companyPhone.replace(/\D/g, ''))) {
                showAlert('Please enter a valid 10-digit phone number', 'error');
                return false;
            }

            // Show loading
            showAlert('Saving settings...', 'info');

            // Create FormData
            const formData = new FormData(this);

            // Log FormData contents for debugging
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            // AJAX request
            $.ajax({
                url: '{{ route("admin.cashmemo-invoice-settings.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, 'success');

                        // Reload page after 1.5 seconds to show updated images
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to save settings';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += ': ' + xhr.responseJSON.message;
                    } else if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        errorMessage = 'Validation errors: ';
                        for (const field in errors) {
                            errorMessage += errors[field][0] + ' ';
                        }
                    }

                    showAlert(errorMessage, 'error');
                    console.error('AJAX Error:', xhr);
                }
            });
        });

        function showAlert(message, type = 'success') {
            const container = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `<span>${message}</span>`;
            container.appendChild(alert);
            setTimeout(() => alert.remove(), 5000);
        }



        $('input[name="ifsc_code"]').on('input', function() {
            this.value = this.value.toUpperCase().replace(/[^0-9A-Z]/g, '');
        });
    });
</script>
@endpush
@endsection
