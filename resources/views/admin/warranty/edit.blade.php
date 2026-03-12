@extends('layouts.admin')

@section('title', 'Edit Warranty Claim')
@section('header-title', 'Edit Warranty Claim')

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
                <h1 class="wc-title">Edit Warranty Claim</h1>
                <p class="wc-sub">Claim No.{{ $claim->warranty_claim_number }}</p>
            </div>
        </div>


    </div>

    {{-- Warning if claim is already approved --}}
    @if($claim->approved_at)
    <div style="background:#fee2e2; border:1px solid #fecaca; border-radius:7px; padding:15px; margin-bottom:16px; color:#991b1b;">
        <strong>⚠️ This claim has already been approved.</strong> You cannot edit approved claims.
    </div>
    @endif

    {{-- Invoice Details Card (Read-only) --}}
    <div class="wc-card">
        <div class="wc-card-header">
            <div class="wc-card-icon" style="background: #f97316;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
            </div>
            <div class="wc-card-title">
                <h3>Invoice Details</h3>
                <p>Original sale information (read-only)</p>
            </div>
        </div>

        <div class="wc-card-body">
            <div class="wc-invoice-card">
                <div class="wc-invoice-header">
                    <span class="wc-invoice-number">{{ $claim->salesInvoice->invoice_number }}</span>
                    <span class="wc-invoice-badge">{{ \Carbon\Carbon::parse($claim->salesInvoice->invoice_date)->format('d M Y') }}</span>
                </div>
                <div class="wc-invoice-grid">
                    <div class="wc-invoice-item">
                        <span class="wc-invoice-label">Party</span>
                        <span class="wc-invoice-value">{{ $claim->party->name ?? 'N/A' }}</span>
                    </div>
                    <div class="wc-invoice-item">
                        <span class="wc-invoice-label">Mobile</span>
                        <span class="wc-invoice-value">{{ $claim->party->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="wc-invoice-item">
                        <span class="wc-invoice-label">Warehouse</span>
                        <span class="wc-invoice-value">{{ $claim->warehouse->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Product Info Card (Read-only) --}}
    <div class="wc-card">
        <div class="wc-card-header">
            <div class="wc-card-icon" style="background: #8b5cf6;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <div class="wc-card-title">
                <h3>Product Information</h3>
                <p>Product details (read-only)</p>
            </div>
        </div>

        <div class="wc-card-body">
            <div class="wc-product-detail-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <div>
                        <div style="font-size:15px; font-weight:600;">
                            {{ $claim->salesInvoiceItem->product_name ?? 'N/A' }}
                            @if($claim->salesInvoiceItem->variant_name)
                                <span style="color:#6b7280; font-size:12px;">({{ $claim->salesInvoiceItem->variant_name }})</span>
                            @endif
                        </div>
                        <div style="font-size:11px; color:#6b7280; margin-top:3px;">
                            SKU: {{ $claim->salesInvoiceItem->sku ?? 'N/A' }}
                        </div>
                    </div>
                    <span class="wi-badge wi-badge--{{ $claim->warranty_status }}" style="font-size:11px;">
                        {{ ucfirst($claim->warranty_status) }}
                    </span>
                </div>

                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:15px; background:#f9fafb; padding:12px; border-radius:5px; margin-bottom:12px;">
                    <div>
                        <div style="font-size:9px; color:#6b7280; text-transform:uppercase;">Purchased Qty</div>
                        <div style="font-size:14px; font-weight:600;">{{ $claim->salesInvoiceItem->quantity ?? 0 }}</div>
                    </div>
                    <div>
                        <div style="font-size:9px; color:#6b7280; text-transform:uppercase;">Claimed Qty</div>
                        <div style="font-size:14px; font-weight:600; color:#f97316;">{{ $claim->claimed_qty }}</div>
                    </div>
                    <div>
                        <div style="font-size:9px; color:#6b7280; text-transform:uppercase;">Replaced Qty</div>
                        <div style="font-size:14px; font-weight:600;">{{ $claim->replaced_qty }}</div>
                    </div>
                    <div>
                        <div style="font-size:9px; color:#6b7280; text-transform:uppercase;">Remaining</div>
                        <div style="font-size:14px; font-weight:600; color:#10b981;">
                            {{ max(0, $claim->claimed_qty - $claim->replaced_qty) }}
                        </div>
                    </div>
                </div>

                @if($claim->warranty_status === 'valid' && isset($claim->salesInvoiceItem->warranty_end))
                <div style="background:#eff6ff; border:1px solid #dbeafe; border-radius:5px; padding:10px;">
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px;">
                        <div>
                            <div style="font-size:9px; color:#60a5fa;">Warranty Start</div>
                            <div style="font-size:12px; font-weight:500;">{{ \Carbon\Carbon::parse($claim->salesInvoiceItem->warranty_start)->format('d M Y') }}</div>
                        </div>
                        <div>
                            <div style="font-size:9px; color:#60a5fa;">Warranty End</div>
                            <div style="font-size:12px; font-weight:500;">{{ \Carbon\Carbon::parse($claim->salesInvoiceItem->warranty_end)->format('d M Y') }}</div>
                        </div>
                        <div>
                            <div style="font-size:9px; color:#60a5fa;">Days Left</div>
                            <div style="font-size:12px; font-weight:500;">{{ \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($claim->salesInvoiceItem->warranty_end)) }} days</div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Edit Form --}}
    <div class="wc-card">
        <div class="wc-card-header">
            <div class="wc-card-icon" style="background: #10b981;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                </svg>
            </div>
            <div class="wc-card-title">
                <h3>Edit Claim Details</h3>
                <p>You can modify unapproved claims</p>
            </div>
        </div>

        <div class="wc-card-body">
            <form id="editClaimForm" method="POST" action="{{ route('admin.warranty.update', $claim->_id) }}">
                @csrf
                @method('PUT')

                {{-- Read-only fields as hidden --}}
                <input type="hidden" name="sales_invoice_id" value="{{ $claim->sales_invoice_id }}">
                <input type="hidden" name="sales_invoice_item_id" value="{{ $claim->sales_invoice_item_id }}">

                <div class="wc-form-row">
                    {{-- Claim Number (Read-only) --}}
                    <div class="wc-form-group">
                        <label class="wc-form-label">Claim Number</label>
                        <input type="text" class="wc-form-select" value="{{ $claim->warranty_claim_number }}" readonly disabled style="background:#f3f4f6;">
                    </div>

                    {{-- Claim Date (Read-only) --}}
                    <div class="wc-form-group">
                        <label class="wc-form-label">Claim Date</label>
                        <input type="text" class="wc-form-select" value="{{ \Carbon\Carbon::parse($claim->claim_date)->format('d M Y') }}" readonly disabled style="background:#f3f4f6;">
                    </div>
                </div>

                <div class="wc-form-row">
                    {{-- Claim Type (Editable) --}}
                    <div class="wc-form-group">
                        <label class="wc-form-label">
                            Claim Type
                            <span class="wc-required">*</span>
                        </label>
                        <select class="wc-form-select" name="claim_type" id="claim_type" required>
                            <option value="replacement" {{ $claim->claim_type == 'replacement' ? 'selected' : '' }}>Replacement</option>
                        </select>
                        <div class="wc-form-hint">Currently only replacement is supported</div>
                    </div>

                    {{-- Claimed Quantity (Editable) --}}
                    <div class="wc-form-group">
                        <label class="wc-form-label">
                            Claimed Quantity
                            <span class="wc-required">*</span>
                        </label>
                        @php
                            $maxQty = $claim->salesInvoiceItem->quantity ?? 0;
                            $alreadyReplaced = $claim->replaced_qty;
                            // Can't reduce below already replaced quantity
                            $minQty = $alreadyReplaced;
                            // Can't increase beyond original purchased quantity
                            $maxAllowed = $maxQty;
                        @endphp
                        <input type="number"
                               class="wc-form-select"
                               name="claimed_qty"
                               id="claimed_qty"
                               min="{{ $minQty }}"
                               max="{{ $maxAllowed }}"
                               value="{{ $claim->claimed_qty }}"
                               required>
                        <div class="wc-form-hint">
                            Max: {{ $maxAllowed }}
                        </div>
                        @if($alreadyReplaced > 0)
                            <div style="color:#f97316; font-size:10px; margin-top:3px;">
                                ⚠️ {{ $alreadyReplaced }} unit(s) already replaced. You cannot reduce below this.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Notes --}}
                <div class="wc-form-group" style="margin-bottom:20px;">
                    <label class="wc-form-label">Notes</label>
                    <textarea class="wc-form-textarea"
                              name="notes"
                              rows="3"
                              placeholder="Enter any additional notes...">{{ $claim->notes }}</textarea>
                </div>



                {{-- Form Actions --}}
                <div class="wc-form-actions">
                    <a href="{{ route('admin.warranty.index') }}" class="wc-btn-secondary">Cancel</a>
                    <button type="submit" class="wc-btn-primary" id="submitBtn" {{ $claim->approved_at ? 'disabled' : '' }}>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                            <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                        </svg>
                        Update Claim
                    </button>
                </div>

                @if($claim->approved_at)
                    <div style="text-align:center; margin-top:10px; color:#dc2626; font-size:11px;">
                        This claim has been approved and cannot be edited.
                    </div>
                @endif
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
/* Additional styles for edit page */
.wc-product-detail-card {
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    padding: 16px;
    background: white;
}
.wc-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
@media (max-width: 640px) {
    .wc-form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}
</style>

@endpush

@push('scripts')
<script>
document.getElementById('editClaimForm')?.addEventListener('submit', function(e) {
    let claimedQty = parseInt(document.getElementById('claimed_qty').value);
    let minQty = parseInt(this.querySelector('#claimed_qty').getAttribute('min'));
    let maxQty = parseInt(this.querySelector('#claimed_qty').getAttribute('max'));

    if (claimedQty < minQty) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Invalid Quantity',
            text: `Claimed quantity cannot be less than ${minQty} (already replaced quantity)`
        });
        return;
    }

    if (claimedQty > maxQty) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Invalid Quantity',
            text: `Claimed quantity cannot exceed ${maxQty} (purchased quantity)`
        });
        return;
    }

    let submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="wc-loading"></span> Updating...';
});
</script>
@endpush
@endsection
