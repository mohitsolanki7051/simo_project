@extends('layouts.admin')

@section('title', 'Warranty Claim Details')
@section('header-title', 'Warranty Claim #' . $claim->warranty_claim_number)

@section('content')
<div class="ws-wrap">
    <div id="alertBox"></div>

    {{-- Header with Actions --}}
    <div class="ws-header">
        <div class="ws-header-left">
            <a href="{{ route('admin.warranty.index') }}" class="ws-btn-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Back to Claims
            </a>
            <div class="ws-header-icon" style="background: #f97316;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3v18M16 5l-4-2-4 2v4l4 2 4-2V5zM8 13l-4 2v4l4 2 4-2v-4l-4-2zM16 13l-4 2v4l4 2 4-2v-4l-4-2z"/>
                </svg>
            </div>
            <div>
                <h1 class="ws-title">Warranty Claim Details</h1>
                <p class="ws-sub">Claim #{{ $claim->warranty_claim_number }}</p>
            </div>
        </div>
        <div class="ws-header-actions">
            @if($claim->canApprove())
                <button class="ws-btn ws-btn-success" onclick="approveReplacement()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Approve Replacement
                </button>
            @endif

            @if($claim->canMarkRepairCompleted())
                <button class="ws-btn ws-btn-primary" onclick="markRepairCompleted()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 7L9 12L14 17M20 7L15 12L20 17M4 7L9 12L4 17"/>
                    </svg>
                    Mark Repair Completed
                </button>
            @endif
        </div>
    </div>

    {{-- Status Cards --}}
    <div class="ws-stats">
        <div class="ws-stat-card ws-stat--{{ $claim->warranty_status }}">
            <div class="ws-stat-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <div class="ws-stat-content">
                <div class="ws-stat-label">Warranty Status</div>
                <div class="ws-stat-value">{{ ucfirst($claim->warranty_status) }}</div>
                @if($claim->warranty_status === 'valid' && $claim->salesInvoiceItem && $claim->salesInvoiceItem->warranty_end)
                    <div class="ws-stat-hint">
                        Valid until {{ \Carbon\Carbon::parse($claim->salesInvoiceItem->warranty_end)->format('d M Y') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="ws-stat-card ws-stat--{{ $claim->replacement_status }}">
            <div class="ws-stat-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 17h16M4 17l4-4M4 17l4 4M20 7h-16M20 7l-4-4M20 7l-4 4"/>
                </svg>
            </div>
            <div class="ws-stat-content">
                <div class="ws-stat-label">Replacement Status</div>
                <div class="ws-stat-value">{{ ucfirst($claim->replacement_status) }}</div>
                @if($claim->approved_at)
                    <div class="ws-stat-hint">
                        Approved on {{ $claim->approved_at->format('d M Y, h:i A') }}
                        @if($claim->approver)
                            by {{ $claim->approver->name }}
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="ws-stat-card ws-stat--{{ $claim->repair_status === 'completed' ? 'done' : 'pending' }}">
            <div class="ws-stat-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 7L9 12L14 17M20 7L15 12L20 17M4 7L9 12L4 17"/>
                </svg>
            </div>
            <div class="ws-stat-content">
                <div class="ws-stat-label">Repair Status</div>
                <div class="ws-stat-value">{{ ucfirst($claim->repair_status) }}</div>
                @if($claim->repaired_at)
                    <div class="ws-stat-hint">
                        Repaired on {{ $claim->repaired_at->format('d M Y, h:i A') }}
                        @if($claim->repairer)
                            by {{ $claim->repairer->name }}
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="ws-grid">
        {{-- Left Column --}}
        <div class="ws-left">
            {{-- Party Information --}}
            <div class="ws-card">
                <div class="ws-card-header">
                    <div class="ws-card-icon" style="background: #3b82f6;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <h3 class="ws-card-title">Party Information</h3>
                </div>
                <div class="ws-card-body">
                    <div class="ws-info-grid">
                        <div class="ws-info-item">
                            <span class="ws-info-label">Name</span>
                            <span class="ws-info-value">{{ $claim->party->name ?? 'N/A' }}</span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Phone</span>
                            <span class="ws-info-value">
                                @if($claim->party && $claim->party->phone)
                                    <a href="tel:{{ $claim->party->phone }}">{{ $claim->party->phone }}</a>
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Email</span>
                            <span class="ws-info-value">{{ $claim->party->email ?? 'N/A' }}</span>
                        </div>
                        <div class="ws-info-item ws-info-item-full">
                            <span class="ws-info-label">Address</span>
                            <span class="ws-info-value">{{ $claim->party->defaultBillingAddress()?->full_address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Claim Details --}}
            <div class="ws-card">
                <div class="ws-card-header">
                    <div class="ws-card-icon" style="background: #f97316;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                    </div>
                    <h3 class="ws-card-title">Claim Details</h3>
                </div>
                <div class="ws-card-body">
                    <div class="ws-info-grid">
                        <div class="ws-info-item">
                            <span class="ws-info-label">Claim Number</span>
                            <span class="ws-info-value ws-claim-number">{{ $claim->warranty_claim_number }}</span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Claim Date</span>
                            <span class="ws-info-value">{{ \Carbon\Carbon::parse($claim->claim_date)->format('d M Y') }}</span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Claim Type</span>
                            <span class="ws-info-value">{{ ucfirst($claim->claim_type) }}</span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Created By</span>
                            <span class="ws-info-value">{{ $claim->creator->name ?? 'N/A' }}</span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Created At</span>
                            <span class="ws-info-value">{{ $claim->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>

                    @if($claim->notes)
                    <div class="ws-notes">
                        <div class="ws-info-label">Notes</div>
                        <div class="ws-notes-content">{{ $claim->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="ws-right">
            {{-- Product Information --}}
            <div class="ws-card">
                <div class="ws-card-header">
                    <div class="ws-card-icon" style="background: #8b5cf6;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        </svg>
                    </div>
                    <h3 class="ws-card-title">Product Information</h3>
                </div>
                <div class="ws-card-body">
                    <div class="ws-product-details">
                        <div class="ws-product-image">
                           @if($claim->product_type == 'simple' && $claim->simpleProduct && $claim->simpleProduct->base_image)
                            <img src="{{ asset('storage/'.$claim->simpleProduct->base_image) }}" alt="Product">
                        @elseif($claim->product_type == 'variant' && $claim->variantProduct && $claim->variantProduct->base_image)
                            <img src="{{ asset('storage/'.$claim->variantProduct->base_image) }}" alt="Product">
                        @else
                            <div class="ws-product-placeholder">
                                No Image
                            </div>
                        @endif
                        </div>
                        <div class="ws-product-info">
                            <h4 class="ws-product-name">{{ $claim->salesInvoiceItem->product_name ?? 'N/A' }}</h4>
                            @if($claim->salesInvoiceItem->variant_name)
                            <div class="ws-product-variant">
                                {{ $claim->salesInvoiceItem->variant_name ?? 'N/A' }}
                            </div>
                            @endif
                            <div class="ws-product-meta">
                                @if($claim->salesInvoiceItem && $claim->salesInvoiceItem->sku)
                                <div class="ws-meta-item">
                                    <span class="ws-meta-label">SKU:</span>
                                    <span class="ws-meta-value">{{ $claim->salesInvoiceItem->sku ?? 'N/A' }}</span>
                                </div>
                                @endif

                                @if($claim->salesInvoiceItem && $claim->salesInvoiceItem->barcode)
                                <div class="ws-meta-item">
                                    <span class="ws-meta-label">Barcode:</span>
                                    <span class="ws-meta-value">{{ $claim->salesInvoiceItem->barcode ?? 'N/A' }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($claim->salesInvoiceItem && $claim->salesInvoiceItem->warranty_start && $claim->salesInvoiceItem->warranty_end)
                    <div class="ws-warranty-period">
                        <div class="ws-warranty-title">Warranty Period</div>
                        <div class="ws-warranty-dates">
                            <div class="ws-date-item">
                                <span class="ws-date-label">Start Date:</span>
                                <span class="ws-date-value">{{ \Carbon\Carbon::parse($claim->salesInvoiceItem->warranty_start)->format('d M Y') }}</span>
                            </div>
                            <div class="ws-date-item">
                                <span class="ws-date-label">End Date:</span>
                                <span class="ws-date-value">{{ \Carbon\Carbon::parse($claim->salesInvoiceItem->warranty_end)->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Invoice Information --}}
            <div class="ws-card">
                <div class="ws-card-header">
                    <div class="ws-card-icon" style="background: #10b981;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                    </div>
                    <h3 class="ws-card-title">Invoice Information</h3>
                </div>
                <div class="ws-card-body">
                    <div class="ws-info-grid">
                        <div class="ws-info-item">
                            <span class="ws-info-label">Invoice Number</span>
                            <span class="ws-info-value">
                                @if($claim->salesInvoice)
                                    <a href="{{ route('admin.sales.show', $claim->sales_invoice_id) }}" class="ws-invoice-link" target="_blank">
                                        {{ $claim->salesInvoice->invoice_number }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Invoice Date</span>
                            <span class="ws-info-value">
                                @if($claim->salesInvoice && $claim->salesInvoice->invoice_date)
                                    {{ \Carbon\Carbon::parse($claim->salesInvoice->invoice_date)->format('d M Y') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        @if($claim->salesInvoice)
                        <div class="ws-info-item">
                            <span class="ws-info-label">Invoice Total</span>
                            <span class="ws-info-value">₹{{ number_format($claim->salesInvoice->grand_total, 2) }}</span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Quantity</span>
                            <span class="ws-info-value">{{ $claim->salesInvoiceItem->quantity ?? 'N/A' }}</span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Price</span>
                            <span class="ws-info-value">₹{{ number_format($claim->salesInvoiceItem->price ?? 0, 2) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Approve Replacement Modal --}}
<div class="wi-modal" id="approveModal">
    <div class="wi-modal-overlay" onclick="closeApproveModal()"></div>
    <div class="wi-modal-box">
        <div class="wi-modal-head">
            <div class="wi-modal-ico" style="background: #10b981;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div>
                <div class="wi-modal-title">Approve Replacement</div>
                <div class="wi-modal-sub">Confirm replacement approval</div>
            </div>
            <button class="wi-modal-close" onclick="closeApproveModal()">×</button>
        </div>
        <div class="wi-modal-body">
            <p style="margin-bottom: 12px;">This will deduct 1 item from main warehouse stock.</p>
            <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 6px; padding: 10px 12px;">
                <strong>Claim Number:</strong> <span id="approveClaimNumber"></span>
            </div>
        </div>
        <div class="wi-modal-foot">
            <button class="wi-btn-cancel" onclick="closeApproveModal()">Cancel</button>
            <button class="wi-btn-confirm" id="confirmApproveBtn" style="background: #10b981;">Approve Replacement</button>
        </div>
    </div>
</div>

{{-- Mark Repair Completed Modal --}}
<div class="wi-modal" id="repairModal">
    <div class="wi-modal-overlay" onclick="closeRepairModal()"></div>
    <div class="wi-modal-box">
        <div class="wi-modal-head">
            <div class="wi-modal-ico" style="background: #8b5cf6;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M14 7L9 12L14 17M20 7L15 12L20 17M4 7L9 12L4 17"/>
                </svg>
            </div>
            <div>
                <div class="wi-modal-title">Mark Repair Completed</div>
                <div class="wi-modal-sub">Confirm repair completion</div>
            </div>
            <button class="wi-modal-close" onclick="closeRepairModal()">×</button>
        </div>
        <div class="wi-modal-body">
            <p style="margin-bottom: 12px;">This will add the repaired product back to stock.</p>
            <div style="background: #ede9fe; border: 1px solid #c4b5fd; border-radius: 6px; padding: 10px 12px;">
                <strong>Claim Number:</strong> <span id="repairClaimNumber"></span>
            </div>
        </div>
        <div class="wi-modal-foot">
            <button class="wi-btn-cancel" onclick="closeRepairModal()">Cancel</button>
            <button class="wi-btn-confirm" id="confirmRepairBtn" style="background: #8b5cf6;">Mark Completed</button>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Warranty Show Page Styles */
:root {
    --c-brand: #f97316;
    --c-brand-d: #ea6c10;
    --c-brand-l: #fff7ed;
    --c-text: #111827;
    --c-text2: #374151;
    --c-muted: #6b7280;
    --c-border: #e5e7eb;
    --c-bg: #f9fafb;
    --c-white: #ffffff;
    --c-shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
    --c-shadow2: 0 4px 12px rgba(0,0,0,.08);
    --r: 7px;
    --r-sm: 5px;
}

.ws-wrap {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 12.5px;
    color: var(--c-text);
    padding: 16px;
    max-width: 100%;
}

/* Header */
.ws-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--c-border);
    gap: 12px;
    flex-wrap: wrap;
}

.ws-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
}

.ws-btn-back {
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

.ws-btn-back:hover {
    background: var(--c-bg);
    border-color: #d1d5db;
}

.ws-header-icon {
    width: 38px;
    height: 38px;
    background: var(--c-brand-l);
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--c-brand);
    flex-shrink: 0;
}

.ws-title {
    font-size: 17px;
    font-weight: 700;
    margin: 0 0 2px;
    letter-spacing: -.3px;
}

.ws-sub {
    font-size: 11px;
    color: var(--c-muted);
    margin: 0;
}

.ws-header-actions {
    display: flex;
    gap: 8px;
}

.ws-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: none;
    border-radius: var(--r-sm);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    text-decoration: none;
}

.ws-btn-primary {
    background: #3b82f6;
    color: white;
}

.ws-btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(59,130,246,.25);
}

.ws-btn-success {
    background: #10b981;
    color: white;
}

.ws-btn-success:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(16,185,129,.25);
}

/* Stats Cards */
.ws-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.ws-stat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    box-shadow: var(--c-shadow);
    position: relative;
    overflow: hidden;
    transition: box-shadow .2s;
}

.ws-stat-card:hover {
    box-shadow: var(--c-shadow2);
}

.ws-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
}

.ws-stat--valid::before {
    background: #22c55e;
}

.ws-stat--expired::before {
    background: #ef4444;
}

.ws-stat--pending::before {
    background: #f97316;
}

.ws-stat--done::before {
    background: #22c55e;
}

.ws-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: #f3f4f6;
}

.ws-stat--valid .ws-stat-icon {
    background: #dcfce7;
    color: #16a34a;
}

.ws-stat--expired .ws-stat-icon {
    background: #fee2e2;
    color: #dc2626;
}

.ws-stat--pending .ws-stat-icon {
    background: #fff7ed;
    color: #f97316;
}

.ws-stat--done .ws-stat-icon {
    background: #dcfce7;
    color: #16a34a;
}

.ws-stat-label {
    font-size: 10.5px;
    color: var(--c-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 2px;
}

.ws-stat-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--c-text);
    margin-bottom: 2px;
}

.ws-stat-hint {
    font-size: 10px;
    color: var(--c-muted);
}

/* Grid Layout */
.ws-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* Cards */
.ws-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    box-shadow: var(--c-shadow);
    margin-bottom: 16px;
    overflow: hidden;
}

.ws-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: #f8fafc;
    border-bottom: 1px solid var(--c-border);
}

.ws-card-icon {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ws-card-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--c-text);
    margin: 0;
}

.ws-card-body {
    padding: 16px;
}

/* Info Grid */
.ws-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.ws-info-item {
    display: flex;
    flex-direction: column;
}

.ws-info-item-full {
    grid-column: span 2;
}

.ws-info-label {
    font-size: 10px;
    color: var(--c-muted);
    margin-bottom: 2px;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.ws-info-value {
    font-size: 13px;
    color: var(--c-text2);
    font-weight: 500;
}

.ws-info-value a {
    color: #2563eb;
    text-decoration: none;
}

.ws-info-value a:hover {
    text-decoration: underline;
}

.ws-claim-number {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: var(--c-brand);
}

/* Notes */
.ws-notes {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px dashed var(--c-border);
}

.ws-notes-content {
    background: #f9fafb;
    padding: 10px 12px;
    border-radius: var(--r-sm);
    font-size: 12px;
    color: var(--c-text2);
    line-height: 1.5;
    margin-top: 6px;
}

/* Product Details */
.ws-product-details {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.ws-product-image {
    width: 80px;
    height: 80px;
    border-radius: var(--r-sm);
    background: #f3f4f6;
    overflow: hidden;
    flex-shrink: 0;
}

.ws-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ws-product-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d1d5db;
}

.ws-product-info {
    flex: 1;
}

.ws-product-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--c-text);
    margin: 0 0 2px;
}

.ws-product-variant {
    font-size: 12px;
    color: var(--c-muted);
    margin-bottom: 8px;
}

.ws-product-meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ws-meta-item {
    display: flex;
    align-items: baseline;
    gap: 6px;
    font-size: 11px;
}

.ws-meta-label {
    color: var(--c-muted);
    min-width: 50px;
}

.ws-meta-value {
    color: var(--c-text2);
    font-family: 'Courier New', monospace;
}

/* Warranty Period */
.ws-warranty-period {
    background: #f8fafc;
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    padding: 12px;
}

.ws-warranty-title {
    font-size: 11px;
    font-weight: 600;
    color: var(--c-muted);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.ws-warranty-dates {
    display: flex;
    gap: 20px;
}

.ws-date-item {
    display: flex;
    flex-direction: column;
}

.ws-date-label {
    font-size: 10px;
    color: var(--c-muted);
}

.ws-date-value {
    font-size: 12px;
    font-weight: 500;
    color: var(--c-text2);
}

/* Invoice Link */
.ws-invoice-link {
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
}

.ws-invoice-link:hover {
    text-decoration: underline;
}

/* Modal Styles (reuse from index) */
.wi-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.wi-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(2px);
}

.wi-modal-box {
    position: relative;
    background: var(--c-white);
    border-radius: 10px;
    width: 380px;
    max-width: 92%;
    box-shadow: 0 20px 50px rgba(0,0,0,.15);
    animation: wiMIn .2s ease;
}

@keyframes wiMIn {
    from { opacity:0; transform: scale(.95) translateY(10px); }
    to   { opacity:1; transform: scale(1) translateY(0); }
}

.wi-modal-head {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--c-border);
    background: var(--c-bg);
    border-radius: 10px 10px 0 0;
}

.wi-modal-ico {
    width: 34px; height: 34px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.wi-modal-title { font-size: 13.5px; font-weight: 700; margin-bottom: 1px; }
.wi-modal-sub   { font-size: 10.5px; color: var(--c-muted); }
.wi-modal-close {
    margin-left: auto;
    background: none;
    border: none;
    font-size: 20px;
    color: var(--c-muted);
    cursor: pointer;
    width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 5px;
    line-height: 1;
    transition: all .15s;
}
.wi-modal-close:hover { background: #e5e7eb; color: var(--c-text); }
.wi-modal-body { padding: 16px; }
.wi-modal-body p { font-size: 12.5px; color: #4b5563; line-height: 1.6; margin: 0; }
.wi-modal-foot {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 12px 16px;
    border-top: 1px solid var(--c-border);
    background: #fafafa;
    border-radius: 0 0 10px 10px;
}
.wi-btn-cancel {
    padding: 7px 16px;
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm);
    background: var(--c-bg);
    color: var(--c-text2);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.wi-btn-cancel:hover { background: #e5e7eb; }
.wi-btn-confirm {
    padding: 7px 16px;
    border: none;
    border-radius: var(--r-sm);
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.wi-btn-confirm:hover { filter: brightness(0.9); }

/* Responsive */
@media (max-width: 768px) {
    .ws-stats {
        grid-template-columns: 1fr;
    }

    .ws-grid {
        grid-template-columns: 1fr;
    }

    .ws-info-grid {
        grid-template-columns: 1fr;
    }

    .ws-info-item-full {
        grid-column: span 1;
    }

    .ws-product-details {
        flex-direction: column;
    }

    .ws-warranty-dates {
        flex-direction: column;
        gap: 10px;
    }

    .ws-header-actions {
        width: 100%;
    }

    .ws-btn {
        flex: 1;
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
let currentClaimId = '{{ $claim->_id }}';

function approveReplacement() {
    document.getElementById('approveClaimNumber').textContent = '{{ $claim->warranty_claim_number }}';
    document.getElementById('approveModal').style.display = 'flex';
}

function closeApproveModal() {
    document.getElementById('approveModal').style.display = 'none';
}

document.getElementById('confirmApproveBtn')?.addEventListener('click', function() {
    fetch(`/admin/warranty/${currentClaimId}/approve-replacement`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        closeApproveModal();
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message,
                showConfirmButton: true
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to approve'
            });
        }
    })
    .catch(error => {
        closeApproveModal();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong'
        });
    });
});

function markRepairCompleted() {
    document.getElementById('repairClaimNumber').textContent = '{{ $claim->warranty_claim_number }}';
    document.getElementById('repairModal').style.display = 'flex';
}

function closeRepairModal() {
    document.getElementById('repairModal').style.display = 'none';
}

document.getElementById('confirmRepairBtn')?.addEventListener('click', function() {
    fetch(`/admin/warranty/${currentClaimId}/mark-repair-completed`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        closeRepairModal();
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message,
                showConfirmButton: true
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to mark completed'
            });
        }
    })
    .catch(error => {
        closeRepairModal();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong'
        });
    });
});

// Close modals when clicking overlay
document.querySelectorAll('.wi-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function() {
        closeApproveModal();
        closeRepairModal();
    });
});

// Keyboard shortcut to close modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeApproveModal();
        closeRepairModal();
    }
});
</script>
@endpush
@endsection
