@extends('layouts.admin')

@section('title', 'Warranty Claim Details')
@section('header-title', 'Warranty Claim #' . $claim->warranty_claim_number)

@section('content')
<div class="ws-wrap">
    <div id="alertBox"></div>

    {{-- Header --}}
    <div class="ws-header">
        <div class="ws-header-left">
            <a href="{{ route('admin.warranty.index') }}" class="ws-btn-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Back to Claims
            </a>
            <div class="ws-header-icon">
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
            @if(in_array($claim->replacement_status, ['pending', 'partial']) && $claim->warranty_status === 'valid')
                <button class="ws-btn ws-btn-success" onclick="openApproveModal()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Approve Replacement
                    @if($claim->replacement_status === 'partial')
                        <span class="ws-btn-badge">{{ $claim->replacementRemainingQty() }} left</span>
                    @endif
                </button>
            @endif

            @if($claim->canMarkRepairCompleted())
                <button class="ws-btn ws-btn-primary" onclick="openRepairModal()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                    Mark Repair Completed
                    @if($claim->repair_status === 'partial')
                        <span class="ws-btn-badge">{{ $claim->repairRemainingQty() }} left</span>
                    @endif
                </button>
            @endif

            {{-- SCRAP button — same condition as repair --}}
            @if($claim->canMarkScrapped())
                <button class="ws-btn ws-btn-danger" onclick="openScrapModal()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        <path d="M10 11v6M14 11v6"/>
                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                    </svg>
                    Mark Scrapped
                    <span class="ws-btn-badge">{{ $claim->repairRemainingQty() }} left</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Status Cards --}}
    <div class="ws-stats">
        {{-- Warranty Status --}}
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
                @if($claim->warranty_status === 'valid' && $claim->salesInvoiceItem?->warranty_end)
                    <div class="ws-stat-hint">Valid until {{ \Carbon\Carbon::parse($claim->salesInvoiceItem->warranty_end)->format('d M Y') }}</div>
                @endif
            </div>
        </div>

        {{-- Replacement Status --}}
        @php
            $replStatClass = match($claim->replacement_status) { 'done' => 'done', 'partial' => 'partial', default => 'pending' };
        @endphp
        <div class="ws-stat-card ws-stat--{{ $replStatClass }}">
            <div class="ws-stat-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 17h16M4 17l4-4M4 17l4 4M20 7h-16M20 7l-4-4M20 7l-4 4"/>
                </svg>
            </div>
            <div class="ws-stat-content">
                <div class="ws-stat-label">Replacement</div>
                <div class="ws-stat-value">
                    {{ ucfirst($claim->replacement_status) }}
                    <span class="ws-qty-pill">{{ $claim->replaced_qty ?? 0 }} / {{ $claim->claimed_qty ?? 0 }}</span>
                </div>
                @if($claim->approved_at)
                    <div class="ws-stat-hint">Last on {{ $claim->approved_at->format('d M Y') }}@if($claim->approver) by {{ $claim->approver->name }}@endif</div>
                @endif
            </div>
        </div>

        {{-- Repair Status --}}
        @php
            $repairStatClass = match($claim->repair_status) { 'completed' => 'done', 'partial' => 'partial', default => 'pending' };
        @endphp
        <div class="ws-stat-card ws-stat--{{ $repairStatClass }}">
            <div class="ws-stat-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </div>
            <div class="ws-stat-content">
                <div class="ws-stat-label">Repair</div>
                <div class="ws-stat-value">
                    {{ ucfirst($claim->repair_status) }}
                    <span class="ws-qty-pill">{{ $claim->repaired_qty ?? 0 }} / {{ $claim->replaced_qty ?? 0 }}</span>
                </div>
                @if($claim->repaired_at)
                    <div class="ws-stat-hint">Last on {{ $claim->repaired_at->format('d M Y') }}@if($claim->repairer) by {{ $claim->repairer->name }}@endif</div>
                @endif
            </div>
        </div>

        {{-- Defective Stock Status --}}
        @php
            $dss = $claim->defective_stock_status ?? 'pending_repair';
            $dssConfig = [
                'pending_repair' => ['label' => 'Pending Repair', 'cls' => 'pending',  'icon' => 'clock'],
                'repaired'       => ['label' => 'Repaired',       'cls' => 'done',     'icon' => 'check'],
                'scrapped'       => ['label' => 'Scrapped',       'cls' => 'scrapped', 'icon' => 'trash'],
            ];
            $dssInfo = $dssConfig[$dss] ?? $dssConfig['pending_repair'];
        @endphp
        <div class="ws-stat-card ws-stat--{{ $dssInfo['cls'] }}">
            <div class="ws-stat-icon">
                @if($dssInfo['icon'] === 'clock')
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                @elseif($dssInfo['icon'] === 'check')
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                @else
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                @endif
            </div>
            <div class="ws-stat-content">
                <div class="ws-stat-label">Defective Stock</div>
                <div class="ws-stat-value">{{ $dssInfo['label'] }}</div>
                @if($claim->scrapped_qty > 0)
                    <div class="ws-stat-hint">{{ $claim->scrapped_qty }} unit(s) scrapped</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Qty Progress Tracker --}}
    <div class="ws-card ws-qty-tracker">
        <div class="ws-card-header">
            <div class="ws-card-icon" style="background: #6366f1;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
            </div>
            <h3 class="ws-card-title">Quantity Progress</h3>
        </div>
        <div class="ws-card-body">
            <div class="ws-progress-grid">
                @php
                    $claimedQty  = (int)($claim->claimed_qty  ?? 0);
                    $replacedQty = (int)($claim->replaced_qty ?? 0);
                    $repairedQty = (int)($claim->repaired_qty ?? 0);
                    $scrappedQty = (int)($claim->scrapped_qty ?? 0);
                    $pendingQty  = $claim->repairRemainingQty();
                    $replPct     = $claimedQty  > 0 ? round($replacedQty / $claimedQty  * 100) : 0;
                    $repairPct   = $replacedQty > 0 ? round($repairedQty / $replacedQty * 100) : 0;
                    $scrapPct    = $replacedQty > 0 ? round($scrappedQty / $replacedQty * 100) : 0;
                @endphp

                <div class="ws-progress-item">
                    <div class="ws-progress-header">
                        <span class="ws-progress-label">Claimed</span>
                        <span class="ws-progress-count">{{ $claimedQty }}</span>
                    </div>
                    <div class="ws-progress-bar-wrap"><div class="ws-progress-bar" style="width:100%;background:#6366f1"></div></div>
                </div>

                <div class="ws-progress-item">
                    <div class="ws-progress-header">
                        <span class="ws-progress-label">Replaced</span>
                        <span class="ws-progress-count">{{ $replacedQty }} / {{ $claimedQty }}</span>
                    </div>
                    <div class="ws-progress-bar-wrap">
                        <div class="ws-progress-bar" style="width:{{ $replPct }}%;background:#10b981"></div>
                    </div>
                    <div class="ws-progress-hint">
                        @if($replacedQty < $claimedQty)
                            <span style="color:#f97316">{{ $claimedQty - $replacedQty }} unit(s) pending replacement</span>
                        @else
                            <span style="color:#10b981">All replaced ✓</span>
                        @endif
                    </div>
                </div>

                <div class="ws-progress-item">
                    <div class="ws-progress-header">
                        <span class="ws-progress-label">Repaired & Returned</span>
                        <span class="ws-progress-count">{{ $repairedQty }} / {{ $replacedQty }}</span>
                    </div>
                    <div class="ws-progress-bar-wrap">
                        <div class="ws-progress-bar" style="width:{{ $repairPct }}%;background:#3b82f6"></div>
                    </div>
                    <div class="ws-progress-hint">
                        @if($repairedQty > 0)
                            <span style="color:#10b981">{{ $repairedQty }} unit(s) back in stock</span>
                        @else
                            <span style="color:#9ca3af">None repaired yet</span>
                        @endif
                    </div>
                </div>

                {{-- Scrapped column — always show if any replaced --}}
                @if($replacedQty > 0)
                <div class="ws-progress-item">
                    <div class="ws-progress-header">
                        <span class="ws-progress-label">Scrapped</span>
                        <span class="ws-progress-count">{{ $scrappedQty }} / {{ $replacedQty }}</span>
                    </div>
                    <div class="ws-progress-bar-wrap">
                        <div class="ws-progress-bar" style="width:{{ $scrapPct }}%;background:#ef4444"></div>
                    </div>
                    <div class="ws-progress-hint">
                        @if($scrappedQty > 0)
                            <span style="color:#ef4444">{{ $scrappedQty }} unit(s) scrapped (no stock)</span>
                        @else
                            <span style="color:#9ca3af">None scrapped</span>
                        @endif
                    </div>
                </div>

                {{-- Pending pool --}}
                <div class="ws-progress-item">
                    <div class="ws-progress-header">
                        <span class="ws-progress-label">Pending (Repair / Scrap)</span>
                        <span class="ws-progress-count" style="color: {{ $pendingQty > 0 ? '#f97316' : '#10b981' }}">{{ $pendingQty }}</span>
                    </div>
                    <div class="ws-progress-bar-wrap">
                        @php $pendingPct = $replacedQty > 0 ? round($pendingQty / $replacedQty * 100) : 0; @endphp
                        <div class="ws-progress-bar" style="width:{{ $pendingPct }}%;background:#f97316"></div>
                    </div>
                    <div class="ws-progress-hint">
                        @if($pendingQty > 0)
                            <span style="color:#f97316">{{ $pendingQty }} unit(s) awaiting repair or scrap decision</span>
                        @else
                            <span style="color:#10b981">All processed ✓</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="ws-grid">
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
                                @if($claim->party?->phone)
                                    <a href="tel:{{ $claim->party->phone }}">{{ $claim->party->phone }}</a>
                                @else N/A @endif
                            </span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Email</span>
                            <span class="ws-info-value">{{ $claim->party->email ?? 'N/A' }}</span>
                        </div>
                        <div class="ws-info-item ws-info-item-full">
                            <span class="ws-info-label">Address</span>
                            <span class="ws-info-value">{{ $claim->party?->defaultBillingAddress()?->full_address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

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
                            @php
                                $productImage = null;

                                if ($claim->product_type === 'simple') {

                                    if ($claim->simpleProduct && $claim->simpleProduct->base_image) {
                                        $productImage = $claim->simpleProduct->base_image;
                                    }

                                } elseif ($claim->product_type === 'variant') {

                                    $product = $claim->variantProduct;

                                    if ($product && is_array($product->variants)) {

                                        foreach ($product->variants as $variant) {

                                            if ((string)$variant['_id'] === (string)$claim->variant_id) {

                                                if (!empty($variant['base_image'])) {
                                                    $productImage = $variant['base_image'];
                                                }

                                                break;
                                            }

                                        }

                                    }

                                }
                                @endphp

                                @if($productImage)
                                    <img src="{{ asset('storage/'.$productImage) }}" alt="Product">
                                @else
                                    <div class="ws-product-placeholder">No Image</div>
                                @endif
                        </div>
                        <div class="ws-product-info">
                            <h4 class="ws-product-name">{{ $claim->salesInvoiceItem->product_name ?? 'N/A' }}</h4>
                            @if($claim->salesInvoiceItem?->variant_name)
                                <div class="ws-product-variant">{{ $claim->salesInvoiceItem->variant_name }}</div>
                            @endif
                            <div class="ws-product-meta">
                                @if($claim->salesInvoiceItem?->sku)
                                    <div class="ws-meta-item">
                                        <span class="ws-meta-label">SKU:</span>
                                        <span class="ws-meta-value">{{ $claim->salesInvoiceItem->sku }}</span>
                                    </div>
                                @endif
                                @if($claim->salesInvoiceItem?->barcode)
                                    <div class="ws-meta-item">
                                        <span class="ws-meta-label">Barcode:</span>
                                        <span class="ws-meta-value">{{ $claim->salesInvoiceItem->barcode }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($claim->salesInvoiceItem?->warranty_start && $claim->salesInvoiceItem?->warranty_end)
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
        </div>

        <div class="ws-right">
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
                            <span class="ws-info-label">Warehouse</span>
                            <span class="ws-info-value ws-warehouse-tag">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                {{ $claim->warehouse->name ?? 'N/A' }}
                            </span>
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

                    {{-- Scrap info if any --}}
                    @if($claim->scrapped_qty > 0)
                    <div class="ws-scrap-info">
                        <div class="ws-scrap-info-header">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                            Scrap Record
                        </div>
                        <div class="ws-info-grid" style="margin-top:8px;">
                            <div class="ws-info-item">
                                <span class="ws-info-label">Scrapped Qty</span>
                                <span class="ws-info-value" style="color:#ef4444;font-weight:700;">{{ $claim->scrapped_qty }}</span>
                            </div>
                            <div class="ws-info-item">
                                <span class="ws-info-label">Scrapped By</span>
                                <span class="ws-info-value">{{ $claim->scrapper?->name ?? 'N/A' }}</span>
                            </div>
                            @if($claim->scrapped_at)
                            <div class="ws-info-item">
                                <span class="ws-info-label">Scrapped At</span>
                                <span class="ws-info-value">{{ $claim->scrapped_at->format('d M Y') }}</span>
                            </div>
                            @endif
                            @if($claim->scrap_reason)
                            <div class="ws-info-item ws-info-item-full">
                                <span class="ws-info-label">Scrap Reason</span>
                                <span class="ws-info-value">{{ $claim->scrap_reason }}</span>
                            </div>
                            @endif
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
                                @else N/A @endif
                            </span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Invoice Date</span>
                            <span class="ws-info-value">
                                @if($claim->salesInvoice?->invoice_date)
                                    {{ \Carbon\Carbon::parse($claim->salesInvoice->invoice_date)->format('d M Y') }}
                                @else N/A @endif
                            </span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Warehouse</span>
                            <span class="ws-info-value ws-warehouse-tag">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                {{ $claim->warehouse->name ?? 'N/A' }}
                            </span>
                        </div>
                        @if($claim->salesInvoice)
                        <div class="ws-info-item">
                            <span class="ws-info-label">Invoice Total</span>
                            <span class="ws-info-value">₹{{ number_format($claim->salesInvoice->grand_total, 2) }}</span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Qty Purchased</span>
                            <span class="ws-info-value">{{ $claim->salesInvoiceItem->quantity ?? 'N/A' }}</span>
                        </div>
                        <div class="ws-info-item">
                            <span class="ws-info-label">Unit Price</span>
                            <span class="ws-info-value">₹{{ number_format($claim->salesInvoiceItem->price ?? 0, 2) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════ APPROVE REPLACEMENT MODAL ═══════════════ --}}
<div class="wi-modal" id="approveModal">
    <div class="wi-modal-overlay" onclick="closeApproveModal()"></div>
    <div class="wi-modal-box">
        <div class="wi-modal-head">
            <div class="wi-modal-ico" style="background: #10b981;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div class="wi-modal-title">Approve Replacement</div>
                <div class="wi-modal-sub">Enter qty to replace now</div>
            </div>
            <button class="wi-modal-close" onclick="closeApproveModal()">×</button>
        </div>
        <div class="wi-modal-body">
            <div class="wi-info-row"><span class="wi-info-label">Claim</span><span class="wi-info-val">{{ $claim->warranty_claim_number }}</span></div>
            <div class="wi-info-row"><span class="wi-info-label">Warehouse</span><span class="wi-info-val">{{ $claim->warehouse->name ?? 'N/A' }}</span></div>
            <div class="wi-info-row"><span class="wi-info-label">Claimed Qty</span><span class="wi-info-val">{{ $claim->claimed_qty }}</span></div>
            <div class="wi-info-row"><span class="wi-info-label">Already Replaced</span><span class="wi-info-val">{{ $claim->replaced_qty ?? 0 }}</span></div>
            <div class="wi-info-row" style="font-weight:700;"><span class="wi-info-label">Pending Replacement</span><span class="wi-info-val" style="color:#f97316">{{ $claim->replacementRemainingQty() }}</span></div>
            <div class="wi-qty-group">
                <label class="wi-qty-label">Qty to Replace Now <span style="color:#ef4444">*</span></label>
                <input type="number" id="approveQtyInput" class="wi-qty-input" min="1" max="{{ $claim->replacementRemainingQty() }}" value="{{ $claim->replacementRemainingQty() }}">
                <div class="wi-qty-hint">Max: {{ $claim->replacementRemainingQty() }} unit(s)</div>
            </div>
            <div class="wi-alert-warn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                This will deduct stock from <strong>{{ $claim->warehouse->name ?? 'invoice warehouse' }}</strong>.
            </div>
        </div>
        <div class="wi-modal-foot">
            <button class="wi-btn-cancel" onclick="closeApproveModal()">Cancel</button>
            <button class="wi-btn-confirm" id="confirmApproveBtn" style="background:#10b981;">Approve Replacement</button>
        </div>
    </div>
</div>

{{-- ═══════════════ MARK REPAIR COMPLETED MODAL ═══════════════ --}}
<div class="wi-modal" id="repairModal">
    <div class="wi-modal-overlay" onclick="closeRepairModal()"></div>
    <div class="wi-modal-box">
        <div class="wi-modal-head">
            <div class="wi-modal-ico" style="background: #8b5cf6;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            </div>
            <div>
                <div class="wi-modal-title">Mark Repair Completed</div>
                <div class="wi-modal-sub">Repaired units will be added back to stock</div>
            </div>
            <button class="wi-modal-close" onclick="closeRepairModal()">×</button>
        </div>
        <div class="wi-modal-body">
            <div class="wi-info-row"><span class="wi-info-label">Claim</span><span class="wi-info-val">{{ $claim->warranty_claim_number }}</span></div>
            <div class="wi-info-row"><span class="wi-info-label">Warehouse</span><span class="wi-info-val">{{ $claim->warehouse->name ?? 'N/A' }}</span></div>
            <div class="wi-info-row"><span class="wi-info-label">Total Replaced</span><span class="wi-info-val">{{ $claim->replaced_qty ?? 0 }}</span></div>
            <div class="wi-info-row"><span class="wi-info-label">Already Repaired</span><span class="wi-info-val">{{ $claim->repaired_qty ?? 0 }}</span></div>
            <div class="wi-info-row"><span class="wi-info-label">Already Scrapped</span><span class="wi-info-val">{{ $claim->scrapped_qty ?? 0 }}</span></div>
            <div class="wi-info-row" style="font-weight:700;"><span class="wi-info-label">Pending (Repair / Scrap)</span><span class="wi-info-val" style="color:#8b5cf6">{{ $claim->repairRemainingQty() }}</span></div>
            <div class="wi-qty-group">
                <label class="wi-qty-label">Qty Repaired Now <span style="color:#ef4444">*</span></label>
                <input type="number" id="repairQtyInput" class="wi-qty-input" min="1" max="{{ $claim->repairRemainingQty() }}" value="{{ $claim->repairRemainingQty() }}">
                <div class="wi-qty-hint">Max: {{ $claim->repairRemainingQty() }} unit(s)</div>
            </div>
            <div class="wi-alert-info">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Repaired units will be added back to <strong>{{ $claim->warehouse->name ?? 'invoice warehouse' }}</strong>.
            </div>
        </div>
        <div class="wi-modal-foot">
            <button class="wi-btn-cancel" onclick="closeRepairModal()">Cancel</button>
            <button class="wi-btn-confirm" id="confirmRepairBtn" style="background:#8b5cf6;">Mark Completed</button>
        </div>
    </div>
</div>

{{-- ═══════════════ MARK SCRAPPED MODAL ═══════════════ --}}
<div class="wi-modal" id="scrapModal">
    <div class="wi-modal-overlay" onclick="closeScrapModal()"></div>
    <div class="wi-modal-box">
        <div class="wi-modal-head">
            <div class="wi-modal-ico" style="background: #ef4444;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </div>
            <div>
                <div class="wi-modal-title">Mark as Scrapped</div>
                <div class="wi-modal-sub">Scrapped units will NOT be added back to stock</div>
            </div>
            <button class="wi-modal-close" onclick="closeScrapModal()">×</button>
        </div>
        <div class="wi-modal-body">
            <div class="wi-info-row"><span class="wi-info-label">Claim</span><span class="wi-info-val">{{ $claim->warranty_claim_number }}</span></div>
            <div class="wi-info-row"><span class="wi-info-label">Warehouse</span><span class="wi-info-val">{{ $claim->warehouse->name ?? 'N/A' }}</span></div>
            <div class="wi-info-row"><span class="wi-info-label">Total Replaced</span><span class="wi-info-val">{{ $claim->replaced_qty ?? 0 }}</span></div>
            <div class="wi-info-row"><span class="wi-info-label">Already Repaired</span><span class="wi-info-val">{{ $claim->repaired_qty ?? 0 }}</span></div>
            <div class="wi-info-row"><span class="wi-info-label">Already Scrapped</span><span class="wi-info-val" style="color:#ef4444;">{{ $claim->scrapped_qty ?? 0 }}</span></div>
            <div class="wi-info-row" style="font-weight:700;"><span class="wi-info-label">Pending (Repair / Scrap)</span><span class="wi-info-val" style="color:#ef4444">{{ $claim->repairRemainingQty() }}</span></div>
            <div class="wi-qty-group">
                <label class="wi-qty-label">Qty to Scrap <span style="color:#ef4444">*</span></label>
                <input type="number" id="scrapQtyInput" class="wi-qty-input wi-qty-input--danger" min="1" max="{{ $claim->repairRemainingQty() }}" value="{{ $claim->repairRemainingQty() }}">
                <div class="wi-qty-hint">Max: {{ $claim->repairRemainingQty() }} unit(s)</div>
            </div>
            <div class="wi-qty-group">
                <label class="wi-qty-label">Reason <span style="color:#9ca3af;font-weight:400;text-transform:none;">(optional)</span></label>
                <textarea id="scrapReasonInput" class="wi-textarea" rows="2" placeholder="e.g. Beyond repair, physical damage, missing parts..."></textarea>
            </div>
            <div class="wi-alert-danger">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>Scrapped units will <strong>not</strong> be added to stock. This action marks the product as a total loss.</span>
            </div>
        </div>
        <div class="wi-modal-foot">
            <button class="wi-btn-cancel" onclick="closeScrapModal()">Cancel</button>
            <button class="wi-btn-confirm" id="confirmScrapBtn" style="background:#ef4444;">Confirm Scrap</button>
        </div>
    </div>
</div>

@push('styles')
<style>
:root {
    --c-brand:#f97316; --c-brand-d:#ea6c10; --c-brand-l:#fff7ed;
    --c-text:#111827; --c-text2:#374151; --c-muted:#6b7280;
    --c-border:#e5e7eb; --c-bg:#f9fafb; --c-white:#ffffff;
    --c-shadow:0 1px 3px rgba(0,0,0,.08),0 1px 2px rgba(0,0,0,.04);
    --c-shadow2:0 4px 12px rgba(0,0,0,.08);
    --r:7px; --r-sm:5px;
}
.ws-wrap { font-family:'Segoe UI',system-ui,sans-serif; font-size:12.5px; color:var(--c-text); padding:16px; }

/* Header */
.ws-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:14px; border-bottom:1px solid var(--c-border); gap:12px; flex-wrap:wrap; }
.ws-header-left { display:flex; align-items:center; gap:11px; }
.ws-btn-back { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--c-white); border:1px solid var(--c-border); border-radius:var(--r-sm); font-size:11.5px; font-weight:500; color:var(--c-text2); text-decoration:none; transition:all .15s; margin-right:5px; }
.ws-btn-back:hover { background:var(--c-bg); }
.ws-header-icon { width:38px; height:38px; background:var(--c-brand-l); border-radius:9px; display:flex; align-items:center; justify-content:center; color:var(--c-brand); flex-shrink:0; }
.ws-title { font-size:17px; font-weight:700; margin:0 0 2px; letter-spacing:-.3px; }
.ws-sub { font-size:11px; color:var(--c-muted); margin:0; }
.ws-header-actions { display:flex; gap:8px; flex-wrap:wrap; }
.ws-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border:none; border-radius:var(--r-sm); font-size:12px; font-weight:600; cursor:pointer; transition:all .15s; }
.ws-btn-primary { background:#3b82f6; color:white; }
.ws-btn-primary:hover { background:#2563eb; transform:translateY(-1px); }
.ws-btn-success { background:#10b981; color:white; }
.ws-btn-success:hover { background:#059669; transform:translateY(-1px); }
.ws-btn-danger { background:#ef4444; color:white; }
.ws-btn-danger:hover { background:#dc2626; transform:translateY(-1px); }
.ws-btn-badge { background:rgba(255,255,255,.25); padding:1px 7px; border-radius:10px; font-size:10px; }

/* Stats — now 4 cards */
.ws-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px; }
.ws-stat-card { display:flex; align-items:center; gap:14px; padding:14px 16px; background:var(--c-white); border:1px solid var(--c-border); border-radius:var(--r); box-shadow:var(--c-shadow); position:relative; overflow:hidden; }
.ws-stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; }
.ws-stat--valid::before, .ws-stat--done::before { background:#22c55e; }
.ws-stat--expired::before, .ws-stat--pending::before { background:#f97316; }
.ws-stat--partial::before { background:#f59e0b; }
.ws-stat--scrapped::before { background:#ef4444; }
.ws-stat-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ws-stat--valid .ws-stat-icon,.ws-stat--done .ws-stat-icon { background:#dcfce7; color:#16a34a; }
.ws-stat--expired .ws-stat-icon { background:#fee2e2; color:#dc2626; }
.ws-stat--pending .ws-stat-icon { background:#fff7ed; color:#f97316; }
.ws-stat--partial .ws-stat-icon { background:#fef3c7; color:#d97706; }
.ws-stat--scrapped .ws-stat-icon { background:#fee2e2; color:#ef4444; }
.ws-stat-label { font-size:10px; color:var(--c-muted); font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin-bottom:2px; }
.ws-stat-value { font-size:15px; font-weight:700; color:var(--c-text); display:flex; align-items:center; gap:7px; }
.ws-stat-hint { font-size:10px; color:var(--c-muted); margin-top:2px; }
.ws-qty-pill { background:#f3f4f6; color:#374151; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; }

/* Progress */
.ws-qty-tracker { margin-bottom:16px; }
.ws-progress-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; }
.ws-progress-item { display:flex; flex-direction:column; gap:5px; }
.ws-progress-header { display:flex; justify-content:space-between; align-items:baseline; }
.ws-progress-label { font-size:11px; font-weight:600; color:var(--c-text2); }
.ws-progress-count { font-size:13px; font-weight:700; color:var(--c-text); }
.ws-progress-bar-wrap { height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden; }
.ws-progress-bar { height:100%; border-radius:3px; transition:width .4s ease; }
.ws-progress-hint { font-size:10px; }

/* Cards */
.ws-card { background:var(--c-white); border:1px solid var(--c-border); border-radius:var(--r); box-shadow:var(--c-shadow); margin-bottom:16px; overflow:hidden; }
.ws-card-header { display:flex; align-items:center; gap:10px; padding:11px 16px; background:#f8fafc; border-bottom:1px solid var(--c-border); }
.ws-card-icon { width:28px; height:28px; border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ws-card-title { font-size:13px; font-weight:600; color:var(--c-text); margin:0; }
.ws-card-body { padding:16px; }
.ws-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.ws-info-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
.ws-info-item { display:flex; flex-direction:column; }
.ws-info-item-full { grid-column:span 2; }
.ws-info-label { font-size:10px; color:var(--c-muted); margin-bottom:2px; text-transform:uppercase; letter-spacing:.3px; }
.ws-info-value { font-size:13px; color:var(--c-text2); font-weight:500; }
.ws-info-value a { color:#2563eb; text-decoration:none; }
.ws-claim-number { font-family:'Courier New',monospace; font-weight:600; color:var(--c-brand); }
.ws-warehouse-tag { display:inline-flex; align-items:center; gap:4px; color:#c2410c; }
.ws-notes { margin-top:14px; padding-top:14px; border-top:1px dashed var(--c-border); }
.ws-notes-content { background:#f9fafb; padding:10px 12px; border-radius:var(--r-sm); font-size:12px; color:var(--c-text2); line-height:1.5; margin-top:5px; }
.ws-scrap-info { margin-top:14px; padding:12px; border-radius:var(--r-sm); background:#fff5f5; border:1px solid #fecaca; }
.ws-scrap-info-header { font-size:10px; font-weight:700; color:#ef4444; text-transform:uppercase; letter-spacing:.4px; display:flex; align-items:center; gap:5px; }
.ws-product-details { display:flex; gap:14px; margin-bottom:14px; }
.ws-product-image { width:78px; height:78px; border-radius:var(--r-sm); background:#f3f4f6; overflow:hidden; flex-shrink:0; }
.ws-product-image img { width:100%; height:100%; object-fit:cover; }
.ws-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#d1d5db; font-size:10px; }
.ws-product-name { font-size:14px; font-weight:600; color:var(--c-text); margin:0 0 2px; }
.ws-product-variant { font-size:12px; color:var(--c-muted); margin-bottom:7px; }
.ws-meta-item { display:flex; align-items:baseline; gap:6px; font-size:11px; margin-bottom:3px; }
.ws-meta-label { color:var(--c-muted); min-width:50px; }
.ws-meta-value { font-family:'Courier New',monospace; color:var(--c-text2); }
.ws-warranty-period { background:#f8fafc; border:1px solid var(--c-border); border-radius:var(--r-sm); padding:11px 13px; }
.ws-warranty-title { font-size:10px; font-weight:600; color:var(--c-muted); margin-bottom:7px; text-transform:uppercase; letter-spacing:.3px; }
.ws-warranty-dates { display:flex; gap:20px; }
.ws-date-item { display:flex; flex-direction:column; }
.ws-date-label { font-size:10px; color:var(--c-muted); }
.ws-date-value { font-size:12px; font-weight:500; color:var(--c-text2); }
.ws-invoice-link { color:#2563eb; text-decoration:none; font-weight:500; }

/* Modal */
.wi-modal { display:none; position:fixed; inset:0; z-index:1000; align-items:center; justify-content:center; }
.wi-modal-overlay { position:absolute; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(2px); }
.wi-modal-box { position:relative; background:var(--c-white); border-radius:10px; width:420px; max-width:94%; box-shadow:0 20px 50px rgba(0,0,0,.15); animation:wiMIn .2s ease; }
@keyframes wiMIn { from{opacity:0;transform:scale(.95) translateY(10px)} to{opacity:1;transform:none} }
.wi-modal-head { display:flex; align-items:center; gap:11px; padding:14px 16px; border-bottom:1px solid var(--c-border); background:var(--c-bg); border-radius:10px 10px 0 0; }
.wi-modal-ico { width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.wi-modal-title { font-size:13.5px; font-weight:700; }
.wi-modal-sub { font-size:10.5px; color:var(--c-muted); }
.wi-modal-close { margin-left:auto; background:none; border:none; font-size:20px; color:var(--c-muted); cursor:pointer; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:5px; transition:all .15s; }
.wi-modal-close:hover { background:#e5e7eb; color:var(--c-text); }
.wi-modal-body { padding:16px; display:flex; flex-direction:column; gap:8px; }
.wi-modal-foot { display:flex; justify-content:flex-end; gap:8px; padding:12px 16px; border-top:1px solid var(--c-border); background:#fafafa; border-radius:0 0 10px 10px; }
.wi-btn-cancel { padding:7px 16px; border:1px solid var(--c-border); border-radius:var(--r-sm); background:var(--c-bg); color:var(--c-text2); font-size:12px; font-weight:600; cursor:pointer; }
.wi-btn-cancel:hover { background:#e5e7eb; }
.wi-btn-confirm { padding:7px 16px; border:none; border-radius:var(--r-sm); color:#fff; font-size:12px; font-weight:600; cursor:pointer; }
.wi-btn-confirm:hover { filter:brightness(.9); }
.wi-info-row { display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-bottom:1px solid #f3f4f6; font-size:12px; }
.wi-info-label { color:var(--c-muted); }
.wi-info-val { font-weight:600; color:var(--c-text); }
.wi-qty-group { margin-top:4px; }
.wi-qty-label { display:block; font-size:11px; font-weight:600; color:var(--c-text2); text-transform:uppercase; letter-spacing:.3px; margin-bottom:5px; }
.wi-qty-input { width:100%; height:38px; padding:0 10px; border:1.5px solid var(--c-border); border-radius:var(--r-sm); font-size:14px; font-weight:600; color:var(--c-text); outline:none; transition:border-color .15s; box-sizing:border-box; }
.wi-qty-input:focus { border-color:var(--c-brand); box-shadow:0 0 0 3px rgba(249,115,22,.1); }
.wi-qty-input--danger:focus { border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.1); }
.wi-textarea { width:100%; padding:8px 10px; border:1.5px solid var(--c-border); border-radius:var(--r-sm); font-size:12px; color:var(--c-text); outline:none; resize:vertical; transition:border-color .15s; font-family:inherit; box-sizing:border-box; }
.wi-textarea:focus { border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.1); }
.wi-qty-hint { font-size:10px; color:var(--c-muted); margin-top:3px; }
.wi-alert-warn { display:flex; align-items:flex-start; gap:7px; padding:9px 11px; background:#fef3c7; border:1px solid #fde68a; border-radius:6px; font-size:11px; color:#92400e; margin-top:4px; }
.wi-alert-info { display:flex; align-items:flex-start; gap:7px; padding:9px 11px; background:#ede9fe; border:1px solid #c4b5fd; border-radius:6px; font-size:11px; color:#5b21b6; margin-top:4px; }
.wi-alert-danger { display:flex; align-items:flex-start; gap:7px; padding:9px 11px; background:#fee2e2; border:1px solid #fecaca; border-radius:6px; font-size:11px; color:#991b1b; margin-top:4px; }

/* Responsive */
@media(max-width:900px) { .ws-stats { grid-template-columns:repeat(2,1fr); } }
@media(max-width:768px) {
    .ws-stats { grid-template-columns:1fr 1fr; }
    .ws-grid { grid-template-columns:1fr; }
    .ws-info-grid { grid-template-columns:1fr; }
    .ws-info-item-full { grid-column:span 1; }
    .ws-progress-grid { grid-template-columns:1fr 1fr; }
    .ws-product-details { flex-direction:column; }
    .ws-warranty-dates { flex-direction:column; gap:8px; }
    .ws-header-actions { width:100%; }
    .ws-btn { flex:1; justify-content:center; }
}
@media(max-width:480px) {
    .ws-stats { grid-template-columns:1fr; }
    .ws-progress-grid { grid-template-columns:1fr; }
}
</style>
@endpush

@push('scripts')
<script>
let currentClaimId = '{{ $claim->_id }}';

// ── Approve Replacement ──
function openApproveModal()  { document.getElementById('approveModal').style.display = 'flex'; document.getElementById('approveQtyInput').focus(); }
function closeApproveModal() { document.getElementById('approveModal').style.display = 'none'; }

// ── Repair ──
function openRepairModal()  { document.getElementById('repairModal').style.display = 'flex'; document.getElementById('repairQtyInput').focus(); }
function closeRepairModal() { document.getElementById('repairModal').style.display = 'none'; }

// ── Scrap ──
function openScrapModal()  { document.getElementById('scrapModal').style.display = 'flex'; document.getElementById('scrapQtyInput').focus(); }
function closeScrapModal() { document.getElementById('scrapModal').style.display = 'none'; }

// ── Generic AJAX submit ──
function submitAction(url, payload, btnEl, btnText) {
    btnEl.disabled = true;
    btnEl.textContent = 'Processing...';

    return fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .finally(() => { btnEl.disabled = false; btnEl.textContent = btnText; });
}

// ── Approve ──
document.getElementById('confirmApproveBtn')?.addEventListener('click', function() {
    const qty = parseInt(document.getElementById('approveQtyInput').value);
    const max = parseInt(document.getElementById('approveQtyInput').max);
    if (!qty || qty < 1) return Swal.fire({ icon:'warning', title:'Invalid Qty', text:'Please enter a valid quantity.' });
    if (qty > max)       return Swal.fire({ icon:'warning', title:'Exceeds Limit', text:`Max replaceable qty is ${max}.` });

    submitAction(`/admin/warranty/${currentClaimId}/approve-replacement`, { qty }, this, 'Approve Replacement')
    .then(data => {
        closeApproveModal();
        if (data.success) Swal.fire({ icon:'success', title:'Success', text:data.message }).then(() => location.reload());
        else              Swal.fire({ icon:'error',   title:'Error',   text:data.message || 'Failed to approve' });
    })
    .catch(() => Swal.fire({ icon:'error', title:'Error', text:'Something went wrong' }));
});

// ── Repair ──
document.getElementById('confirmRepairBtn')?.addEventListener('click', function() {
    const qty = parseInt(document.getElementById('repairQtyInput').value);
    const max = parseInt(document.getElementById('repairQtyInput').max);
    if (!qty || qty < 1) return Swal.fire({ icon:'warning', title:'Invalid Qty', text:'Please enter a valid quantity.' });
    if (qty > max)       return Swal.fire({ icon:'warning', title:'Exceeds Limit', text:`Max repairable qty is ${max}.` });

    submitAction(`/admin/warranty/${currentClaimId}/mark-repair-completed`, { qty }, this, 'Mark Completed')
    .then(data => {
        closeRepairModal();
        if (data.success) Swal.fire({ icon:'success', title:'Success', text:data.message }).then(() => location.reload());
        else              Swal.fire({ icon:'error',   title:'Error',   text:data.message || 'Failed' });
    })
    .catch(() => Swal.fire({ icon:'error', title:'Error', text:'Something went wrong' }));
});

// ── Scrap ──
document.getElementById('confirmScrapBtn')?.addEventListener('click', function() {
    const qty    = parseInt(document.getElementById('scrapQtyInput').value);
    const max    = parseInt(document.getElementById('scrapQtyInput').max);
    const reason = document.getElementById('scrapReasonInput').value.trim();

    if (!qty || qty < 1) return Swal.fire({ icon:'warning', title:'Invalid Qty', text:'Please enter a valid quantity.' });
    if (qty > max)       return Swal.fire({ icon:'warning', title:'Exceeds Limit', text:`Max scrappable qty is ${max}.` });

    submitAction(`/admin/warranty/${currentClaimId}/mark-scrapped`, { qty, reason }, this, 'Confirm Scrap')
    .then(data => {
        closeScrapModal();
        if (data.success) Swal.fire({ icon:'success', title:'Scrapped', text:data.message }).then(() => location.reload());
        else              Swal.fire({ icon:'error',   title:'Error',   text:data.message || 'Failed' });
    })
    .catch(() => Swal.fire({ icon:'error', title:'Error', text:'Something went wrong' }));
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeApproveModal(); closeRepairModal(); closeScrapModal(); }
});
</script>
@endpush
@endsection
