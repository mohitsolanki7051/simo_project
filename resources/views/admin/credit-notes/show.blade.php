@extends('layouts.admin')

@section('title', 'Credit Note Details')
@section('header-title', 'Credit Note Details')

@section('content')
<div class="cn-show-wrap">
    <div id="alertBox"></div>

    {{-- ── Header ── --}}
    <div class="cn-show-header">
        <div class="cn-show-header-left">
            <div class="cn-show-header-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div>
                <h1 class="cn-show-title">Credit Note: {{ $creditNote->credit_note_number }}</h1>
                <p class="cn-show-sub">Created on {{ $creditNote->created_at->format('d M Y, h:i A') }}</p>
            </div>
        </div>
        <div class="cn-show-header-right">
            <a href="{{ route('admin.credit-notes.index') }}" class="cn-show-btn-secondary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Credit Notes
            </a>
            @if($creditNote->status === 'active')
                <button onclick="cancelNote()" class="cn-show-btn-danger">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    Cancel Credit Note
                </button>
            @endif
        </div>
    </div>

    {{-- ── Status Banner ── --}}
    <div class="cn-show-status-banner status-{{ $creditNote->status }}">
        <div class="status-icon">
            @if($creditNote->status === 'active')
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            @elseif($creditNote->status === 'used')
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            @else
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="18" y1="6" x2="6" y2="18"/>
                </svg>
            @endif
        </div>
        <span class="status-text">{{ ucfirst($creditNote->status) }} Credit Note</span>
    </div>

    {{-- ── Main Content ── --}}
    <div class="cn-show-details-grid">
        {{-- Left Column --}}
        <div class="cn-show-left-col">
            {{-- Credit Note Information Card --}}
            <div class="cn-show-info-card">
                <div class="cn-show-card-header">
                    <div class="cn-show-card-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/>
                            <line x1="12" y1="18" x2="12" y2="20"/>
                            <line x1="12" y1="2" x2="12" y2="4"/>
                        </svg>
                    </div>
                    <h3>Credit Note Information</h3>
                </div>
                <div class="cn-show-card-body">
                    <div class="cn-show-info-grid">
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Credit Note No.</span>
                            <span class="cn-show-info-value cn-show-note-number">{{ $creditNote->credit_note_number }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Credit Date</span>
                            <span class="cn-show-info-value">{{ $creditNote->credit_date->format('d M Y') }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Subtotal</span>
                            <span class="cn-show-info-value">₹ {{ number_format($creditNote->subtotal_float, 2) }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Tax Amount</span>
                            <span class="cn-show-info-value cn-show-amount-used">+ ₹ {{ number_format($creditNote->tax_amount_float, 2) }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Discount</span>
                            <span class="cn-show-info-value cn-show-amount-discount">- ₹ {{ number_format($creditNote->discount_amount_float, 2) }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Total Amount</span>
                            <span class="cn-show-info-value cn-show-amount-total">₹ {{ number_format($creditNote->amount_float, 2) }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Used Amount</span>
                            <span class="cn-show-info-value cn-show-amount-used">₹ {{ number_format($creditNote->used_amount_float, 2) }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Remaining</span>
                            <span class="cn-show-info-value cn-show-amount-remaining">₹ {{ number_format($creditNote->remaining_amount_float, 2) }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Status</span>
                            <span class="cn-show-info-value">
                                <span class="cn-show-status-badge status-{{ $creditNote->status }}">
                                    {{ ucfirst($creditNote->status) }}
                                </span>
                            </span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Created By</span>
                            <span class="cn-show-info-value">{{ $creditNote->creator?->name ?? 'System' }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Created At</span>
                            <span class="cn-show-info-value">{{ $creditNote->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Party Information Card --}}
            <div class="cn-show-info-card">
                <div class="cn-show-card-header">
                    <div class="cn-show-card-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <h3>Party Details</h3>
                </div>
                <div class="cn-show-card-body">
                    <div class="cn-show-info-grid">
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Name</span>
                            <span class="cn-show-info-value cn-show-party-name">
                                {{ $creditNote->party?->name ?? 'N/A' }}
                                @if($creditNote->party)
                                    <span class="cn-show-party-type type-{{ $creditNote->party->party_type }}">
                                        {{ ucfirst($creditNote->party->party_type) }}
                                    </span>
                                @endif
                            </span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Phone</span>
                            <span class="cn-show-info-value">{{ $creditNote->party?->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Email</span>
                            <span class="cn-show-info-value">{{ $creditNote->party?->email ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reason Card --}}
            @if($creditNote->reason)
            <div class="cn-show-info-card">
                <div class="cn-show-card-header">
                    <div class="cn-show-card-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <h3>Reason</h3>
                </div>
                <div class="cn-show-card-body">
                    <p class="cn-show-reason-text">{{ $creditNote->reason }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column --}}
        <div class="cn-show-right-col">
            {{-- Credit Note Items Card --}}
            <div class="cn-show-info-card">
                <div class="cn-show-card-header">
                    <div class="cn-show-card-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 7v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2z"/>
                            <line x1="16" y1="11" x2="8" y2="11"/>
                            <line x1="16" y1="15" x2="8" y2="15"/>
                            <line x1="10" y1="7" x2="10" y2="7"/>
                        </svg>
                    </div>
                    <h3>Credit Note Items</h3>
                </div>
                <div class="cn-show-card-body">
                    @if($creditNote->items->count() > 0)
                        <div class="cn-show-table-responsive">
                            <table class="cn-show-items-table">
                                <thead>
                                    <tr>
                                        <th class="items">Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-right">GST %</th>
                                        <th class="text-right">GST Amt</th>
                                        <th class="text-right">Discount</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($creditNote->items as $item)
                                    <tr>
                                        <td>
                                            <div class="item-name">{{ $item->product_name }}</div>
                                            @if($item->variant_name)
                                                <div class="item-variant">→ {{ $item->variant_name }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-right">₹ {{ number_format($item->price, 2) }}</td>
                                        <td class="text-right">{{ $item->tax_percent }}%</td>
                                        <td class="text-right tax-amount">₹ {{ number_format($item->tax_amount, 2) }}</td>
                                        <td class="text-right discount-amount">₹ {{ number_format($item->discount_amount, 2) }}</td>
                                        <td class="text-right total-amount">₹ {{ number_format($item->total, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="summary-row">
                                        <td colspan="6" class="summary-label">Subtotal:</td>
                                        <td class="text-right summary-value">₹ {{ number_format($creditNote->subtotal_float, 2) }}</td>
                                        <td></td>
                                    </tr>
                                    <tr class="summary-row">
                                        <td colspan="6" class="summary-label">Total Tax:</td>
                                        <td class="text-right tax-amount">+ ₹ {{ number_format($creditNote->tax_amount_float, 2) }}</td>
                                        <td></td>
                                    </tr>
                                    <tr class="summary-row">
                                        <td colspan="6" class="summary-label">Total Discount:</td>
                                        <td class="text-right discount-amount">- ₹ {{ number_format($creditNote->discount_amount_float, 2) }}</td>
                                        <td></td>
                                    </tr>
                                    <tr class="summary-row grand-total-row">
                                        <td colspan="6" class="summary-label grand-total-label">Grand Total:</td>
                                        <td class="text-right grand-total-amount">₹ {{ number_format($creditNote->amount_float, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="cn-show-empty-text">No items found for this credit note</p>
                    @endif
                </div>
            </div>

            {{-- Original Invoice Card --}}
            <div class="cn-show-info-card">
                <div class="cn-show-card-header">
                    <div class="cn-show-card-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <h3>Original Invoice</h3>
                </div>
                <div class="cn-show-card-body">
                    @if($creditNote->invoice)
                    <div class="cn-show-info-grid">
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Invoice No.</span>
                            <span class="cn-show-info-value">
                                <a href="{{ route('admin.sales.show', $creditNote->sales_invoice_id) }}" class="cn-show-link">
                                    {{ $creditNote->invoice->invoice_number }}
                                </a>
                            </span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Invoice Date</span>
                            <span class="cn-show-info-value">{{ $creditNote->invoice->invoice_date->format('d M Y') }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Subtotal</span>
                            <span class="cn-show-info-value">₹ {{ number_format($creditNote->invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Tax Total</span>
                            <span class="cn-show-info-value">₹ {{ number_format($creditNote->invoice->tax_total, 2) }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Grand Total</span>
                            <span class="cn-show-info-value cn-show-amount-total">₹ {{ number_format($creditNote->invoice->grand_total, 2) }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Payment Status</span>
                            <span class="cn-show-info-value">
                                <span class="cn-show-payment-badge status-{{ $creditNote->invoice->payment_status }}">
                                    {{ ucfirst($creditNote->invoice->payment_status) }}
                                </span>
                            </span>
                        </div>
                    </div>
                    @else
                    <p class="cn-show-empty-text">Invoice information not available</p>
                    @endif
                </div>
            </div>

            {{-- Sales Return Card --}}
            <div class="cn-show-info-card">
                <div class="cn-show-card-header">
                    <div class="cn-show-card-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4v16h16M8 11l4-4 4 4M16 8l-4 4-4-4"/>
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9 16h6"/>
                        </svg>
                    </div>
                    <h3>Sales Return</h3>
                </div>
                <div class="cn-show-card-body">
                    @if($creditNote->salesReturn)
                    <div class="cn-show-info-grid">
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Return No.</span>
                            <span class="cn-show-info-value">
                                <a href="{{ route('admin.sales-returns.show', $creditNote->sales_return_id) }}" class="cn-show-link">
                                    {{ $creditNote->salesReturn->return_number }}
                                </a>
                            </span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Return Date</span>
                            <span class="cn-show-info-value">{{ $creditNote->salesReturn->return_date->format('d M Y') }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Return Qty</span>
                            <span class="cn-show-info-value cn-show-qty">{{ $creditNote->salesReturn->total_return_qty }}</span>
                        </div>
                        <div class="cn-show-info-item">
                            <span class="cn-show-info-label">Return Amount</span>
                            <span class="cn-show-info-value cn-show-amount">₹ {{ number_format($creditNote->salesReturn->total_return_amount, 2) }}</span>
                        </div>
                    </div>
                    @else
                    <p class="cn-show-empty-text">Return information not available</p>
                    @endif
                </div>
            </div>

            {{-- Usage History Card (for used notes) --}}
            @if($creditNote->status === 'used')
            <div class="cn-show-info-card cn-show-usage-card">
                <div class="cn-show-card-header">
                    <div class="cn-show-card-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <h3>Usage History</h3>
                </div>
                <div class="cn-show-card-body">
                    <p class="cn-show-usage-text">
                        This credit note has been fully utilized for invoice adjustments.
                        <br>
                        <span class="cn-show-usage-note">Total used: ₹ {{ number_format($creditNote->used_amount_float, 2) }}</span>
                    </p>
                </div>
            </div>
            @endif
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
.cn-show-wrap {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 12.5px;
    color: var(--c-text);
    padding: 16px;
    max-width: 100%;
}

/* ─── Header ──────────────────────────────────────────────── */
.cn-show-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--c-border);
    gap: 12px;
    flex-wrap: wrap;
}
.cn-show-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
}
.cn-show-header-icon {
    width: 38px; height: 38px;
    background: var(--c-brand-l);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    color: var(--c-brand);
    flex-shrink: 0;
}
.cn-show-title {
    font-size: 17px;
    font-weight: 700;
    margin: 0 0 2px;
    letter-spacing: -.3px;
    color: var(--c-text);
}
.cn-show-sub {
    font-size: 11px;
    color: var(--c-muted);
    margin: 0;
}

.cn-show-header-right {
    display: flex;
    gap: 8px;
}

.cn-show-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 15px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: var(--r-sm);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s;
    white-space: nowrap;
}
.cn-show-btn-secondary:hover {
    background: #5a6268;
    color: white;
}

.cn-show-btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 15px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: var(--r-sm);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s;
    white-space: nowrap;
}
.cn-show-btn-danger:hover {
    background: #c82333;
    color: white;
}

/* ─── Status Banner ───────────────────────────────────────── */
.cn-show-status-banner {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    margin-bottom: 20px;
    border-radius: var(--r);
    font-size: 13px;
    font-weight: 600;
    box-shadow: var(--c-shadow);
}
.cn-show-status-banner.status-active {
    background: #dcfce7;
    color: #166534;
    border-left: 4px solid #22c55e;
}
.cn-show-status-banner.status-used {
    background: #dbeafe;
    color: #1e40af;
    border-left: 4px solid #3b82f6;
}
.cn-show-status-banner.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}
.status-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ─── Details Grid ────────────────────────────────────────── */
.cn-show-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.cn-show-left-col,
.cn-show-right-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ─── Info Cards ──────────────────────────────────────────── */
.cn-show-info-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--c-shadow);
    transition: box-shadow .15s;
}
.cn-show-info-card:hover {
    box-shadow: var(--c-shadow2);
}
.cn-show-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: #f8fafc;
    border-bottom: 1px solid var(--c-border);
}
.cn-show-card-icon {
    width: 28px;
    height: 28px;
    background: var(--c-brand-l);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--c-brand);
}
.cn-show-card-header h3 {
    font-size: 13px;
    font-weight: 600;
    color: var(--c-text);
    margin: 0;
}
.cn-show-card-body {
    padding: 16px;
}

/* ─── Info Grid ───────────────────────────────────────────── */
.cn-show-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px 16px;
}
.cn-show-info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.cn-show-info-label {
    font-size: 10px;
    font-weight: 600;
    color: var(--c-muted);
    text-transform: uppercase;
    letter-spacing: .3px;
}
.cn-show-info-value {
    font-size: 13px;
    font-weight: 500;
    color: var(--c-text);
    line-height: 1.4;
}

/* Amount styles */
.cn-show-amount-total {
    font-weight: 700;
    color: var(--c-text);
}
.cn-show-amount-used {
    font-weight: 600;
    color: #3b82f6;
}
.cn-show-amount-discount {
    font-weight: 600;
    color: #f59e0b;
}
.cn-show-amount-remaining {
    font-weight: 600;
    color: #10b981;
}
.cn-show-amount {
    font-weight: 600;
    color: var(--c-text);
}
.cn-show-qty {
    font-weight: 600;
    color: var(--c-text2);
}

/* Note number */
.cn-show-note-number {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: var(--c-brand);
}

/* Party name */
.cn-show-party-name {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.cn-show-party-type {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
}
.cn-show-party-type.type-customer {
    background: #d1fae5;
    color: #065f46;
}
.cn-show-party-type.type-dealer {
    background: #dbeafe;
    color: #1e40af;
}
.cn-show-party-type.type-distributor {
    background: #fef3c7;
    color: #92400e;
}

/* Status badges inside card */
.cn-show-status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}
.cn-show-status-badge.status-active {
    background: #dcfce7;
    color: #166534;
}
.cn-show-status-badge.status-used {
    background: #dbeafe;
    color: #1e40af;
}
.cn-show-status-badge.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

/* Payment badge */
.cn-show-payment-badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 600;
}
.cn-show-payment-badge.status-paid {
    background: #d1fae5;
    color: #065f46;
}
.cn-show-payment-badge.status-unpaid {
    background: #fee2e2;
    color: #991b1b;
}
.cn-show-payment-badge.status-partial {
    background: #fef3c7;
    color: #92400e;
}

/* Links */
.cn-show-link {
    color: var(--c-brand);
    text-decoration: none;
    font-weight: 600;
}
.cn-show-link:hover {
    text-decoration: underline;
}

/* Reason text */
.cn-show-reason-text {
    font-size: 12px;
    color: var(--c-text2);
    line-height: 1.6;
    margin: 0;
    padding: 4px 0;
}

/* Items Table */
.cn-show-table-responsive {
    overflow-x: auto;
}
.cn-show-items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    min-width: 700px;
}
.cn-show-items-table th {
    background: #f8fafc;
    padding: 8px 10px;
    font-weight: 600;
    color: var(--c-muted);
    border-bottom: 1px solid var(--c-border);
    white-space: nowrap;
}
.cn-show-items-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}
.cn-show-items-table tbody tr:hover {
    background: #fafafa;
}
.cn-show-items-table tfoot {
    background: #f8fafc;
    border-top: 2px solid var(--c-border);
}
th.items {
    text-align: left;
}
/* Item styles */
.item-name {
    font-weight: 500;
    color: var(--c-text);
}
.item-variant {
    font-size: 9px;
    color: var(--c-brand);
    margin-top: 2px;
}

/* Table text alignment */
.text-center {
    text-align: center;
}
.text-right {
    text-align: right;
}

/* Amount colors in table */
.tax-amount {
    color: #3b82f6;
    font-weight: 600;
}
.discount-amount {
    color: #f59e0b;
    font-weight: 600;
}
.total-amount {
    font-weight: 700;
    color: var(--c-text);
}

/* Summary rows */
.summary-row td {
    padding: 6px 10px;
}
.summary-label {
    text-align: right;
    font-weight: 500;
    color: var(--c-muted);
}
.summary-value {
    font-weight: 600;
    color: var(--c-text);
}

.grand-total-row {
    border-top: 2px solid var(--c-border);
    background: #fff7ed;
}
.grand-total-label {
    font-weight: 700;
    color: var(--c-text);
    font-size: 12px;
}
.grand-total-amount {
    font-weight: 700;
    color: var(--c-brand);
    font-size: 14px;
}

/* Empty state text */
.cn-show-empty-text {
    font-size: 12px;
    color: var(--c-muted);
    font-style: italic;
    margin: 0;
    padding: 8px 0;
}

/* Usage card */
.cn-show-usage-card {
    border-left: 3px solid #3b82f6;
}
.cn-show-usage-text {
    font-size: 12px;
    color: var(--c-text2);
    line-height: 1.6;
    margin: 0;
}
.cn-show-usage-note {
    display: inline-block;
    margin-top: 8px;
    font-weight: 600;
    color: #1e40af;
    background: #dbeafe;
    padding: 4px 8px;
    border-radius: 4px;
}

/* ─── Alerts ──────────────────────────────────────────────── */
#alertBox {
    position: fixed;
    top: 16px;
    right: 16px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 7px;
}
.cn-show-alert {
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
    animation: alertIn .25s ease;
    min-width: 220px;
    max-width: 320px;
}
@keyframes alertIn {
    from { transform: translateX(110%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.cn-show-alert-success {
    background: #f0fdf4;
    color: #166534;
    border-left: 3px solid #22c55e;
}
.cn-show-alert-error {
    background: #fef2f2;
    color: #991b1b;
    border-left: 3px solid #ef4444;
}

/* ─── Responsive ──────────────────────────────────────────── */
@media (max-width: 1024px) {
    .cn-show-details-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 768px) {
    .cn-show-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .cn-show-header-right {
        width: 100%;
        justify-content: flex-start;
    }
    .cn-show-info-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 480px) {
    .cn-show-wrap {
        padding: 10px;
    }
    .cn-show-btn-secondary,
    .cn-show-btn-danger {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
function showAlert(message, type = 'success') {
    const box = document.getElementById('alertBox');
    const el = document.createElement('div');
    el.className = `cn-show-alert cn-show-alert-${type}`;
    el.textContent = message;
    box.appendChild(el);
    setTimeout(() => el.remove(), 4500);
}

function cancelNote() {
    if (!confirm('Are you sure you want to cancel this credit note? This action cannot be undone.')) {
        return;
    }

    fetch('{{ route('admin.credit-notes.cancel', $creditNote->_id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert(data.message || 'Failed to cancel credit note', 'error');
        }
    })
    .catch(error => {
        showAlert('Something went wrong', 'error');
        console.error('Error:', error);
    });
}
</script>
@endpush
@endsection
