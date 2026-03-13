@extends('layouts.admin')

@section('title', 'Create Warranty Claim')
@section('header-title', 'Create Warranty Claim')

@section('content')
<div class="wc-wrap">
    <div id="alertBox"></div>

    {{-- Header with Back Button --}}
    <div class="wc-header">
        <div class="wc-header-left">
            <a href="{{ route('admin.warranty.index') }}" class="wc-btn-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Back to Claims
            </a>
            <div class="wc-header-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3v18M16 5l-4-2-4 2v4l4 2 4-2V5zM8 13l-4 2v4l4 2 4-2v-4l-4-2zM16 13l-4 2v4l4 2 4-2v-4l-4-2z"/>
                </svg>
            </div>
            <div>
                <h1 class="wc-title">New Warranty Claim</h1>
                <p class="wc-sub">Create a warranty claim from an existing invoice</p>
            </div>
        </div>
    </div>

    {{-- Search Invoice Section --}}
    <div class="wc-card">
        <div class="wc-card-header">
            <div class="wc-card-icon" style="background: #3b82f6;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <div class="wc-card-title">
                <h3>Search Invoice</h3>
                <p>Find the original sale invoice by invoice number, party name, phone or product barcode</p>
            </div>
        </div>

        <div class="wc-card-body">
            <div class="wc-search-box">
                <div class="wc-input-group">
                    <svg class="wc-input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text"
                           class="wc-input"
                           id="searchInput"
                           placeholder="Enter invoice number, party name, phone or barcode..."
                           autocomplete="off"
                           value="">
                </div>
            </div>

            {{-- Search Results --}}
            <div class="wc-search-results" id="searchResults" style="display: none;">
                <div class="wc-results-header">
                    <span class="wc-results-count" id="resultsCount">0 invoices found</span>
                </div>
                <div class="wc-results-list" id="resultsList">
                    {{-- Results will be loaded here --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Selected Invoice Details --}}
    <div class="wc-card" id="invoiceDetailsSection" style="display: none;">
        <div class="wc-card-header">
            <div class="wc-card-icon" style="background: #f97316;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
            </div>
            <div class="wc-card-title">
                <h3>Invoice Details</h3>
                <p>Selected invoice information</p>
            </div>
        </div>

        <div class="wc-card-body">
            <div class="wc-invoice-card" id="invoiceDetails">
                {{-- Invoice details will be loaded here --}}
            </div>
        </div>
    </div>

    {{-- Products List --}}
    <div class="wc-card" id="productsSection" style="display: none;">
        <div class="wc-card-header">
            <div class="wc-card-icon" style="background: #8b5cf6;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <div class="wc-card-title">
                <h3>Select Product for Warranty Claim</h3>
                <p>Choose a product from this invoice to create warranty claim</p>
            </div>
        </div>

        <div class="wc-card-body">
            <div class="wc-products-grid" id="productsGrid">
                {{-- Products will be loaded here --}}
            </div>
        </div>
    </div>

    {{-- Claim Form --}}
    <div class="wc-card" id="claimFormSection" style="display: none;">
        <div class="wc-card-header">
            <div class="wc-card-icon" style="background: #10b981;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M12 3v18M16 5l-4-2-4 2v4l4 2 4-2V5zM8 13l-4 2v4l4 2 4-2v-4l-4-2zM16 13l-4 2v4l4 2 4-2v-4l-4-2z"/>
                </svg>
            </div>
            <div class="wc-card-title">
                <h3>Claim Details</h3>
                <p>Enter warranty claim information</p>
            </div>
        </div>

        <div class="wc-card-body">
            <form id="claimForm">
                @csrf
                <input type="hidden" name="sales_invoice_id" id="sales_invoice_id">
                <input type="hidden" name="sales_invoice_item_id" id="sales_invoice_item_id">

                {{-- Selected Product Info --}}
                <div class="wc-selected-product" id="selectedProductInfo" style="display: none;">
                    {{-- Will be populated --}}
                </div>

                <div class="wc-form-row">
                    <div class="wc-form-group">
                        <label class="wc-form-label">
                            Claim Type
                            <span class="wc-required">*</span>
                        </label>
                        <select class="wc-form-select" name="claim_type" id="claim_type" required>
                            <option value="replacement">Replacement</option>
                        </select>
                        <div class="wc-form-hint">Currently only replacement is supported</div>
                    </div>

                    <div class="wc-form-group">
                        <label class="wc-form-label">
                            Notes (Optional)
                        </label>
                        <textarea class="wc-form-textarea"
                                  name="notes"
                                  id="notes"
                                  rows="3"
                                  placeholder="Enter any additional notes about the claim..."></textarea>
                    </div>
                </div>

                {{-- Warranty Info --}}
                <div class="wc-warranty-info" id="warrantyInfo" style="display: none;">
                    {{-- Warranty information will be shown here --}}
                </div>

                <div class="wc-form-actions">
                    <button type="button" class="wc-btn-secondary" onclick="resetForm()">Cancel</button>
                    <button type="submit" class="wc-btn-primary" id="submitBtn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Create Warranty Claim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ─── Variables ───────────────────────────────────────────── */
:root {
    --c-brand:   #f97316;
    --c-brand-d: #ea6c10;
    --c-brand-l: #fff7ed;
    --c-text:    #111827;
    --c-text2:   #374151;
    --c-muted:   #6b7280;
    --c-border:  #e5e7eb;
    --c-bg:      #f9fafb;
    --c-white:   #ffffff;
    --c-shadow:  0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
    --c-shadow2: 0 4px 12px rgba(0,0,0,.08);
    --r:         7px;
    --r-sm:      5px;
}

/* ─── Wrap ────────────────────────────────────────────────── */
.wc-wrap {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 12.5px;
    color: var(--c-text);
    padding: 16px;
    max-width: 100%;
}

/* ─── Header ──────────────────────────────────────────────── */
.wc-header {
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--c-border);
}
.wc-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
}
.wc-btn-back {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    font-size: 11.5px;
    font-weight: 500;
    color: var(--c-text2);
    text-decoration: none;
    transition: all .15s;
    margin-right: 5px;
}
.wc-btn-back:hover {
    background: var(--c-bg);
    border-color: #d1d5db;
}
.wc-header-icon {
    width: 38px; height: 38px;
    background: var(--c-brand-l);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    color: var(--c-brand);
    flex-shrink: 0;
}
.wc-title { font-size: 17px; font-weight: 700; margin: 0 0 2px; letter-spacing: -.3px; }
.wc-sub   { font-size: 11px; color: var(--c-muted); margin: 0; }

/* ─── Cards ───────────────────────────────────────────────── */
.wc-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    box-shadow: var(--c-shadow);
    margin-bottom: 16px;
    overflow: hidden;
}
.wc-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
    border-bottom: 1px solid var(--c-border);
}
.wc-card-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.wc-card-title h3 {
    font-size: 13px;
    font-weight: 600;
    color: var(--c-text);
    margin: 0 0 2px;
}
.wc-card-title p {
    font-size: 10.5px;
    color: var(--c-muted);
    margin: 0;
}
.wc-card-body {
    padding: 16px;
}

/* ─── Search Box ──────────────────────────────────────────── */
.wc-search-box {
    display: flex;
    gap: 8px;
    align-items: center;
}
.wc-input-group {
    flex: 1;
    position: relative;
}
.wc-input-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--c-muted);
    pointer-events: none;
}
.wc-input {
    width: 100%;
    height: 38px;
    padding: 0 10px 0 34px;
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    font-size: 12px;
    color: var(--c-text);
    background: var(--c-bg);
    transition: all .15s;
    outline: none;
}
.wc-input:focus {
    border-color: var(--c-brand);
    background: var(--c-white);
    box-shadow: 0 0 0 3px rgba(249,115,22,.1);
}
.wc-input::placeholder {
    color: #9ca3af;
    font-size: 11.5px;
}

/* ─── Search Results ──────────────────────────────────────── */
.wc-search-results {
    margin-top: 16px;
    border-top: 1px solid var(--c-border);
}
.wc-results-header {
    padding: 10px 0;
}
.wc-results-count {
    font-size: 11px;
    font-weight: 600;
    color: var(--c-muted);
    text-transform: uppercase;
    letter-spacing: .3px;
}
.wc-results-list {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
}
.wc-result-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    border-bottom: 1px solid var(--c-border);
    cursor: pointer;
    transition: all .15s;
}
.wc-result-item:last-child {
    border-bottom: none;
}
.wc-result-item:hover {
    background: #f3f4f6;
}
.wc-result-item.selected {
    background: #fff7ed;
    border-left: 3px solid var(--c-brand);
}
.wc-result-info {
    flex: 1;
}
.wc-result-invoice {
    font-weight: 600;
    color: var(--c-text);
    margin-bottom: 3px;
    font-size: 12px;
}
.wc-result-customer {
    display: flex;
    gap: 15px;
    font-size: 11px;
    color: var(--c-muted);
}
.wc-result-date {
    font-size: 11px;
    color: #9ca3af;
    margin: 0 10px;
}
.wc-result-select {
    color: var(--c-brand);
    font-size: 11px;
    font-weight: 600;
}
.wc-result-warehouse {
    display: inline-flex;
    align-items: center;
    margin-left: 8px;
    padding: 2px 7px;
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid #fed7aa;
    border-radius: 10px;
    font-size: 9.5px;
    font-weight: 600;
    vertical-align: middle;
}
/* ─── Invoice Card ────────────────────────────────────────── */
.wc-invoice-card {
    background: #f9fafb;
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    padding: 16px;
}
.wc-invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--c-border);
}
.wc-invoice-number {
    font-size: 14px;
    font-weight: 700;
    color: var(--c-text);
}
.wc-invoice-badge {
    padding: 4px 10px;
    background: #d1fae5;
    color: #065f46;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}
.wc-invoice-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 15px;
}
.wc-invoice-item {
    display: flex;
    flex-direction: column;
}
.wc-invoice-label {
    font-size: 10px;
    color: var(--c-muted);
    margin-bottom: 2px;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.wc-invoice-value {
    font-size: 13px;
    font-weight: 500;
    color: var(--c-text);
}
.wc-address-box {
    background: #fff;
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    padding: 10px 12px;
    font-size: 11.5px;
    color: var(--c-text2);
    line-height: 1.5;
}

/* ─── Products Grid ───────────────────────────────────────── */
.wc-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 12px;
}
.wc-product-card {
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    padding: 14px;
    cursor: pointer;
    transition: all .15s;
    background: #fff;
}
/* ─── Qty Summary ─────────────────────────────────────────── */
.wc-qty-summary {
    display: flex;
    gap: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    padding: 8px 10px;
    margin: 8px 0;
}
.wc-qty-row {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}
.wc-qty-label {
    font-size: 9px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .3px;
    margin-bottom: 2px;
}
.wc-qty-val {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
}
.wc-product-card:hover {
    border-color: var(--c-brand);
    box-shadow: var(--c-shadow2);
    transform: translateY(-1px);
}
.wc-product-card.selected {
    border-color: var(--c-brand);
    background: #fff7ed;
}
.wc-product-card.warranty-valid {
    border-left: 4px solid #10b981;
}
.wc-product-card.warranty-expired {
    border-left: 4px solid #ef4444;
    opacity: 0.7;
}
.wc-product-card.warranty-no-warranty {
    border-left: 4px solid #9ca3af;
}
.wc-product-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}
.wc-product-name {
    font-weight: 600;
    color: var(--c-text);
    font-size: 13px;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.wc-product-variant {
    font-size: 11px;
    color: var(--c-muted);
    margin-bottom: 6px;
}
.wc-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
}
.wc-badge.valid {
    background: #d1fae5;
    color: #065f46;
}
.wc-badge.expired {
    background: #fee2e2;
    color: #991b1b;
}
.wc-badge.no-warranty {
    background: #f1f5f9;
    color: #64748b;
}
.wc-product-details {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin: 10px 0;
    padding: 8px 0;
    border-top: 1px dashed var(--c-border);
    border-bottom: 1px dashed var(--c-border);
}
.wc-detail-sm {
    display: flex;
    flex-direction: column;
}
.wc-detail-label {
    font-size: 9px;
    color: var(--c-muted);
    margin-bottom: 2px;
}
.wc-detail-value {
    font-size: 11px;
    font-weight: 500;
    color: var(--c-text);
}
.wc-warranty-dates {
    background: #f3f4f6;
    border-radius: var(--r-sm);
    padding: 8px 10px;
    font-size: 11px;
    margin-top: 8px;
}
.wc-date-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 3px;
}
.wc-date-label {
    color: var(--c-muted);
}
.wc-date-value {
    font-weight: 500;
    color: var(--c-text);
}
.wc-disabled-message {
    margin-top: 8px;
    color: #dc2626;
    font-size: 10px;
    font-weight: 500;
    padding: 6px;
    background: #fee2e2;
    border-radius: 4px;
    text-align: center;
}

/* ─── Buttons ─────────────────────────────────────────────── */
.wc-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0 16px;
    height: 38px;
    background: var(--c-brand);
    color: #fff;
    border: none;
    border-radius: var(--r-sm);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.wc-btn-primary:hover {
    background: var(--c-brand-d);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(249,115,22,.25);
}
.wc-btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}
.wc-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0 16px;
    height: 38px;
    background: var(--c-bg);
    color: var(--c-text2);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.wc-btn-secondary:hover {
    background: #f3f4f6;
}

/* ─── Form Elements ───────────────────────────────────────── */
.wc-form-row {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 20px;
    margin-bottom: 20px;
}
.wc-form-group {
    display: flex;
    flex-direction: column;
}
.wc-form-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--c-text2);
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.wc-required {
    color: #ef4444;
    margin-left: 2px;
}
.wc-form-select,
.wc-form-textarea {
    padding: 8px 10px;
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    font-size: 12px;
    color: var(--c-text);
    background: var(--c-bg);
    transition: all .15s;
    outline: none;
}
.wc-form-select:focus,
.wc-form-textarea:focus {
    border-color: var(--c-brand);
    background: var(--c-white);
    box-shadow: 0 0 0 3px rgba(249,115,22,.1);
}
.wc-form-hint {
    font-size: 10px;
    color: var(--c-muted);
    margin-top: 3px;
}

/* ─── Selected Product ────────────────────────────────────── */
.wc-selected-product {
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: var(--r-sm);
    padding: 12px;
    margin-bottom: 20px;
}
.wc-selected-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.wc-selected-name {
    font-weight: 600;
    color: var(--c-text);
    font-size: 13px;
}
.wc-selected-sku {
    font-size: 11px;
    color: #b45309;
    margin-top: 2px;
}

/* ─── Warranty Info ───────────────────────────────────────── */
.wc-warranty-info {
    background: #eff6ff;
    border: 1px solid #dbeafe;
    border-radius: var(--r-sm);
    padding: 15px;
    margin-bottom: 20px;
}
.wc-warranty-title {
    font-weight: 600;
    color: #1e40af;
    margin-bottom: 10px;
    font-size: 12px;
}
.wc-warranty-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}
.wc-warranty-item {
    display: flex;
    flex-direction: column;
}
.wc-warranty-item-label {
    font-size: 10px;
    color: #60a5fa;
    margin-bottom: 2px;
}
.wc-warranty-item-value {
    font-size: 13px;
    font-weight: 600;
    color: #1e3a8a;
}

/* ─── Form Actions ────────────────────────────────────────── */
.wc-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--c-border);
}

/* ─── Loading States ──────────────────────────────────────── */
.wc-loading {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid var(--c-brand);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ─── Empty States ────────────────────────────────────────── */
.wc-empty {
    padding: 40px;
    text-align: center;
    color: var(--c-muted);
}
.wc-empty-icon {
    font-size: 40px;
    margin-bottom: 10px;
    opacity: 0.3;
}

/* ─── Responsive ──────────────────────────────────────────── */
@media (max-width: 768px) {
    .wc-search-box {
        flex-direction: column;
    }
    .wc-btn-primary {
        width: 100%;
        justify-content: center;
    }
    .wc-invoice-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .wc-form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    .wc-warranty-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .wc-products-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
let selectedInvoice = null;
let selectedItem = null;
let searchTimeout = null;

// Real-time search
document.getElementById('searchInput').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    let searchTerm = e.target.value.trim();

    if (searchTerm.length < 3) {
        document.getElementById('searchResults').style.display = 'none';
        return;
    }

    searchTimeout = setTimeout(() => {
        performSearch(searchTerm);
    }, 500);
});

function performSearch(searchTerm) {
    // Show loading
    document.getElementById('resultsList').innerHTML = `
        <div class="wc-empty">
            <div class="wc-loading"></div>
            <p style="margin-top: 10px;">Searching...</p>
        </div>
    `;
    document.getElementById('searchResults').style.display = 'block';

    fetch('{{ route("admin.warranty.search-invoice") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ search_term: searchTerm })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySearchResults(data.invoices);
        } else {
            throw new Error(data.message || 'Search failed');
        }
    })
    .catch(error => {
        document.getElementById('resultsList').innerHTML = `
            <div class="wc-empty">
                <div class="wc-empty-icon">❌</div>
                <p>Error: ${error.message}</p>
            </div>
        `;
    });
}

function displaySearchResults(invoices) {
    let resultsList = document.getElementById('resultsList');
    let resultsCount = document.getElementById('resultsCount');

    if (invoices.length === 0) {
        resultsList.innerHTML = `
            <div class="wc-empty">
                <div class="wc-empty-icon">🔍</div>
                <p>No invoices found matching your search</p>
            </div>
        `;
        resultsCount.textContent = '0 invoices found';
        return;
    }

    resultsCount.textContent = `${invoices.length} invoice${invoices.length > 1 ? 's' : ''} found`;

    let html = '';
    invoices.forEach(invoice => {
        let date = new Date(invoice.invoice_date);
        let formattedDate = date.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
         let warehouseName = invoice.warehouse?.name || '—';

        html += `
            <div class="wc-result-item" onclick="selectInvoice('${invoice.id}')">
                <div class="wc-result-info">
                    <div class="wc-result-invoice">${invoice.invoice_number}
                        <span class="wc-result-warehouse">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:2px;">
                                <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                            ${warehouseName}
                        </span>
                    </div>
                    <div class="wc-result-customer">
                        <span>${invoice.party?.name || 'N/A'}</span>
                        <span>${invoice.party?.phone || ''}</span>
                    </div>
                </div>
                <div class="wc-result-date">${formattedDate}</div>
                <div class="wc-result-select">Select →</div>
            </div>
        `;
    });

    resultsList.innerHTML = html;
}

function selectInvoice(invoiceId) {
    // Show loading
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/admin/warranty/invoice/${invoiceId}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            selectedInvoice = data.invoice;
            displayInvoiceDetails(data.invoice);
            displayProducts(data.invoice.items);

            // Hide search results and show sections
            document.getElementById('searchResults').style.display = 'none';
            document.getElementById('invoiceDetailsSection').style.display = 'block';
            document.getElementById('productsSection').style.display = 'block';

            Swal.close();
        } else {
            throw new Error(data.message || 'Failed to load invoice');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    });
}

function displayInvoiceDetails(invoice) {
    let date = new Date(invoice.invoice_date);
    let formattedDate = date.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
    let warehouseName = invoice.warehouse?.name || 'N/A';
    let html = `
        <div class="wc-invoice-header">
            <span class="wc-invoice-number">${invoice.invoice_number}</span>
            <span class="wc-invoice-badge">${formattedDate}</span>
        </div>
        <div class="wc-invoice-grid">
            <div class="wc-invoice-item">
                <span class="wc-invoice-label">Party</span>
                <span class="wc-invoice-value">${invoice.party?.name || 'N/A'}</span>
            </div>
            <div class="wc-invoice-item">
                <span class="wc-invoice-label">Mobile</span>
                <span class="wc-invoice-value">${invoice.party?.phone || 'N/A'}</span>
            </div>
            <div class="wc-invoice-item">
                <span class="wc-invoice-label">Invoice Type</span>
                <span class="wc-invoice-value">${invoice.invoice_type?.toUpperCase() || 'N/A'}</span>
            </div>
            <div class="wc-invoice-item">
                <span class="wc-invoice-label">Warehouse</span>
                <span class="wc-invoice-value">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" style="vertical-align:middle;margin-right:3px;">
                        <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                    ${warehouseName}
                </span>
            </div>
        </div>
        <div class="wc-address-box">
            <strong>Billing Address:</strong><br>
            ${invoice.billing_address || 'N/A'}
        </div>
    `;

    document.getElementById('invoiceDetails').innerHTML = html;
}

function displayProducts(items) {
    let productsGrid = document.getElementById('productsGrid');
    let html = '';

    items.forEach(item => {
        let warrantyClass = '';
        let warrantyBadge = '';
        let warrantyDates = '';

        if (item.warranty_valid) {
            if (item.warranty_valid.status === 'valid') {
                warrantyClass = 'warranty-valid';
                warrantyBadge = '<span class="wc-badge valid">✓ Valid</span>';
                warrantyDates = `
                    <div class="wc-warranty-dates">
                        <div class="wc-date-row">
                            <span class="wc-date-label">Start:</span>
                            <span class="wc-date-value">${new Date(item.warranty_start).toLocaleDateString()}</span>
                        </div>
                        <div class="wc-date-row">
                            <span class="wc-date-label">End:</span>
                            <span class="wc-date-value">${new Date(item.warranty_end).toLocaleDateString()}</span>
                        </div>
                        <div class="wc-date-row" style="color: #10b981;">
                            <span>Days Left:</span>
                            <span>${item.warranty_valid.days_left} days</span>
                        </div>
                    </div>`;
            } else if (item.warranty_valid.status === 'expired') {
                warrantyClass = 'warranty-expired';
                warrantyBadge = '<span class="wc-badge expired">✗ Expired</span>';
                warrantyDates = `
                    <div class="wc-warranty-dates" style="background: #fee2e2;">
                        <div class="wc-date-row">
                            <span class="wc-date-label">Start:</span>
                            <span class="wc-date-value">${new Date(item.warranty_start).toLocaleDateString()}</span>
                        </div>
                        <div class="wc-date-row">
                            <span class="wc-date-label">End:</span>
                            <span class="wc-date-value">${new Date(item.warranty_end).toLocaleDateString()}</span>
                        </div>
                        <div class="wc-date-row" style="color: #dc2626;">
                            <span>Expired:</span>
                            <span>${item.warranty_valid.days_overdue} days ago</span>
                        </div>
                    </div>`;
            } else {
                warrantyClass = 'warranty-no-warranty';
                warrantyBadge = '<span class="wc-badge no-warranty">No Warranty</span>';
            }
        }

        let canClaim      = item.can_claim && item.can_claim.can_claim;
        let remainingQty  = item.can_claim?.remaining_qty ?? 0;
        let purchasedQty  = item.can_claim?.purchased_qty ?? item.quantity;
        let claimedSoFar  = item.can_claim?.claimed_qty   ?? 0;
        let variantName = '';

        // Qty summary bar shown on every card
        let qtySummary = `
            <div class="wc-qty-summary">
                <div class="wc-qty-row">
                    <span class="wc-qty-label">Purchased</span>
                    <span class="wc-qty-val">${purchasedQty}</span>
                </div>
                <div class="wc-qty-row">
                    <span class="wc-qty-label">Claimed</span>
                    <span class="wc-qty-val" style="color:#f97316">${claimedSoFar}</span>
                </div>
                <div class="wc-qty-row">
                    <span class="wc-qty-label">Remaining</span>
                    <span class="wc-qty-val" style="color:${remainingQty > 0 ? '#10b981' : '#ef4444'};font-weight:700">${remainingQty}</span>
                </div>
            </div>`;

        html += `
            <div class="wc-product-card ${warrantyClass} ${!canClaim ? 'opacity-50' : ''}"
                 onclick="${canClaim ? `selectProduct('${item.id}', '${item.product_name}', '${item.sku || ''}', '${item.variant_name || ''}', ${remainingQty})` : ''}"
                 data-item-id="${item.id}">
                <div class="wc-product-header">
                    <span class="wc-product-name" title="${item.product_name}">${item.product_name}${variantName}</span>
                    ${warrantyBadge}
                </div>
                ${item.variant_name ? `<div class="wc-product-variant">${item.variant_name}</div>` : ''}
                <div class="wc-product-details">
                    <div class="wc-detail-sm">
                        <span class="wc-detail-label">SKU</span>
                        <span class="wc-detail-value">${item.sku || 'N/A'}</span>
                    </div>
                    <div class="wc-detail-sm">
                        <span class="wc-detail-label">Qty Bought</span>
                        <span class="wc-detail-value">${item.quantity}</span>
                    </div>
                    <div class="wc-detail-sm">
                        <span class="wc-detail-label">Price</span>
                        <span class="wc-detail-value">₹${parseFloat(item.price).toFixed(2)}</span>
                    </div>
                </div>
                ${qtySummary}
                ${warrantyDates}
                ${!canClaim ? `<div class="wc-disabled-message">❌ ${item.can_claim?.reason || 'Cannot claim'}</div>` : ''}
            </div>`;
    });

    productsGrid.innerHTML = html;
}
function selectProduct(itemId, productName, sku, variantName, remainingQty) {
    selectedItem = itemId;

    document.querySelectorAll('.wc-product-card').forEach(c => c.classList.remove('selected'));
    document.querySelector(`.wc-product-card[data-item-id="${itemId}"]`)?.classList.add('selected');

    let selectedProduct = selectedInvoice.items.find(item => item.id === itemId);

    document.getElementById('sales_invoice_id').value      = selectedInvoice.id;
    document.getElementById('sales_invoice_item_id').value = itemId;

    let displayName = variantName ? `${productName} (${variantName})` : productName;

    document.getElementById('selectedProductInfo').innerHTML = `
        <div class="wc-selected-row">
            <div>
                <div class="wc-selected-name">${displayName}</div>
                <div class="wc-selected-sku">SKU: ${sku}</div>
            </div>
            <span class="wc-badge ${selectedProduct.warranty_valid?.status || 'no-warranty'}">
                ${selectedProduct.warranty_valid?.status === 'valid' ? '✓ Valid' :
                  selectedProduct.warranty_valid?.status === 'expired' ? '✗ Expired' : 'No Warranty'}
            </span>
        </div>
        <div style="margin-top:10px;">
            <label class="wc-form-label">
                Qty to Claim
                <span class="wc-required">*</span>
                <span style="color:#6b7280;font-weight:400;margin-left:6px;">(max: ${remainingQty})</span>
            </label>
            <input type="number"
                   class="wc-form-select"
                   name="claimed_qty"
                   id="claimed_qty"
                   min="1"
                   max="${remainingQty}"
                   value="1"
                   style="width:120px;margin-top:4px;"
                   required>
            <div class="wc-form-hint">${remainingQty} unit(s) available for warranty claim</div>
        </div>
    `;
    document.getElementById('selectedProductInfo').style.display = 'block';

    if (selectedProduct.warranty_valid?.status === 'valid') {
        document.getElementById('warrantyInfo').innerHTML = `
            <div class="wc-warranty-title">✅ Valid Warranty</div>
            <div class="wc-warranty-grid">
                <div class="wc-warranty-item">
                    <span class="wc-warranty-item-label">Start Date</span>
                    <span class="wc-warranty-item-value">${new Date(selectedProduct.warranty_start).toLocaleDateString()}</span>
                </div>
                <div class="wc-warranty-item">
                    <span class="wc-warranty-item-label">End Date</span>
                    <span class="wc-warranty-item-value">${new Date(selectedProduct.warranty_end).toLocaleDateString()}</span>
                </div>
                <div class="wc-warranty-item">
                    <span class="wc-warranty-item-label">Days Left</span>
                    <span class="wc-warranty-item-value">${selectedProduct.warranty_valid.days_left} days</span>
                </div>
            </div>`;
        document.getElementById('warrantyInfo').style.display = 'block';
    } else {
        document.getElementById('warrantyInfo').style.display = 'none';
    }

    document.getElementById('claimFormSection').style.display = 'block';
    document.getElementById('claimFormSection').scrollIntoView({ behavior: 'smooth', block: 'center' });
}
// Form submission
document.getElementById('claimForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!selectedItem) {
        Swal.fire({
            icon: 'warning',
            title: 'No Product Selected',
            text: 'Please select a product first'
        });
        return;
    }

    let formData = new FormData(this);
    let submitBtn = document.getElementById('submitBtn');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="wc-loading"></span> Creating...';

    fetch('{{ route("admin.warranty.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Warranty claim created successfully',
                showConfirmButton: true
            }).then(() => {
                window.location.href = '{{ route("admin.warranty.index") }}';
            });
        } else {
            throw new Error(data.message || 'Failed to create claim');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Create Warranty Claim';
    });
});

function resetForm() {
    // Clear selections
    selectedInvoice = null;
    selectedItem = null;

    // Hide sections
    document.getElementById('searchResults').style.display = 'none';
    document.getElementById('invoiceDetailsSection').style.display = 'none';
    document.getElementById('productsSection').style.display = 'none';
    document.getElementById('claimFormSection').style.display = 'none';
    document.getElementById('selectedProductInfo').style.display = 'none';
    document.getElementById('warrantyInfo').style.display = 'none';

    // Clear inputs
    document.getElementById('searchInput').value = '';
    document.getElementById('sales_invoice_id').value = '';
    document.getElementById('sales_invoice_item_id').value = '';
    document.getElementById('notes').value = '';
}
</script>
@endpush
@endsection
