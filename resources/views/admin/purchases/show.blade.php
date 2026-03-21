@extends('layouts.admin')

@section('title', 'Purchase Invoice #' . $invoice->invoice_number)
@section('header-title', 'Purchase Invoice Details')

@section('content')
<div class="pi-show-container">
    <div id="alertBox"></div>

    {{-- ==================== HEADER ==================== --}}
    <div class="pi-show-header">
        <div class="pi-show-header-left">
            <a href="{{ route('admin.purchases.index') }}" class="pi-show-back-btn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back
            </a>
            <div class="pi-show-title-group">
                <h1>Purchase Invoice</h1>
                <span class="pi-show-inv-num">{{ $invoice->invoice_number }}</span>
            </div>
        </div>

        <div class="pi-show-actions">
            @if($invoice->status === 'draft')
                <button class="pi-act-btn pi-act-generate" onclick="openGenerateModal()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Generate
                </button>
                <a href="{{ route('admin.purchases.edit', $invoice->id) }}" class="pi-act-btn pi-act-edit">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                    Edit
                </a>
                <button class="pi-act-btn pi-act-delete" onclick="openDeleteModal()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    Delete
                </button>
            @endif
            @if($invoice->status === 'confirmed')
                <button class="pi-act-btn pi-act-cancel" onclick="openCancelModal()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Cancel
                </button>
            @endif
            <button class="pi-act-btn pi-act-print" onclick="window.print()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3h12v6"/><rect x="6" y="15" width="12" height="6" rx="2"/></svg>
                Print
            </button>
        </div>
    </div>

    {{-- ==================== STATUS BAR ==================== --}}
    <div class="pi-status-bar">
        @php
            $statusCfg = [
                'draft'     => ['cls'=>'pi-s-draft',     'lbl'=>'Draft',     'dot'=>'#94a3b8'],
                'confirmed' => ['cls'=>'pi-s-confirmed', 'lbl'=>'Confirmed', 'dot'=>'#2563eb'],
                'cancelled' => ['cls'=>'pi-s-cancelled', 'lbl'=>'Cancelled', 'dot'=>'#dc2626'],
            ][$invoice->status] ?? ['cls'=>'pi-s-draft','lbl'=>'Draft','dot'=>'#94a3b8'];

            $payCfg = [
                'paid'    => ['cls'=>'pi-p-paid',    'lbl'=>'Paid'],
                'partial' => ['cls'=>'pi-p-partial', 'lbl'=>'Partial'],
                'unpaid'  => ['cls'=>'pi-p-unpaid',  'lbl'=>'Unpaid'],
            ][$invoice->payment_status] ?? ['cls'=>'pi-p-unpaid','lbl'=>'Unpaid'];

            // Resolve party info (no stored party_type — derived dynamically)
            $partyType  = $partyInfo['type'];
            $partyModel = $partyInfo['model'];

            if ($partyType === 'vendor') {
                $partyName  = $partyModel?->company_name ?? 'N/A';
                $partyPhone = $partyModel?->phone;
                $partyEmail = $partyModel?->email;
                $partyGst   = $partyModel?->gst_number;
                $partyPan   = $partyModel?->pan_number;
            } else {
                $partyName  = $partyModel?->name ?? 'N/A';
                $partyPhone = $partyModel?->phone;
                $partyEmail = $partyModel?->email;
                $partyGst   = $partyModel?->gst_number;
                $partyPan   = $partyModel?->pan_number ?? null;
            }
        @endphp

        <div class="pi-status-chip {{ $statusCfg['cls'] }}">
            <span class="pi-status-dot" style="background:{{ $statusCfg['dot'] }}"></span>
            {{ $statusCfg['lbl'] }}
        </div>
        <div class="pi-status-chip {{ $payCfg['cls'] }}">{{ $payCfg['lbl'] }}</div>
        <div class="pi-status-chip pi-type-chip">
            {{ strtoupper($invoice->invoice_type) }}
        </div>
        <div class="pi-status-chip pi-party-type-chip pi-pt-{{ $partyType }}">
            {{ ucfirst($partyType) }}
        </div>
        @if($invoice->tax_type)
        <div class="pi-status-chip pi-tax-chip">
            {{ $invoice->tax_type === 'intra' ? 'CGST + SGST' : 'IGST' }}
        </div>
        @endif
    </div>

    {{-- ==================== MAIN GRID ==================== --}}
    <div class="pi-show-grid">

        {{-- ========== LEFT COLUMN ========== --}}
        <div class="pi-show-left">

            {{-- Party Card --}}
            <div class="pi-card">
                <div class="pi-card-hd">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    {{ ucfirst($partyType) }} Details
                </div>
                <div class="pi-card-bd">
                    <div class="pi-party-name-row">
                        <span class="pi-party-name">{{ $partyName }}</span>
                        <span class="pi-badge pi-badge-{{ $partyType }}">{{ ucfirst($partyType) }}</span>
                    </div>

                    <div class="pi-info-table">
                        @if($partyPhone)
                        <div class="pi-info-row"><span class="pi-info-l">Phone</span><span class="pi-info-v">{{ $partyPhone }}</span></div>
                        @endif
                        @if($partyEmail)
                        <div class="pi-info-row"><span class="pi-info-l">Email</span><span class="pi-info-v">{{ $partyEmail }}</span></div>
                        @endif
                        @if($partyGst)
                        <div class="pi-info-row"><span class="pi-info-l">GST</span><span class="pi-info-v pi-mono">{{ $partyGst }}</span></div>
                        @endif
                        @if($partyPan)
                        <div class="pi-info-row"><span class="pi-info-l">PAN</span><span class="pi-info-v pi-mono">{{ $partyPan }}</span></div>
                        @endif
                    </div>

                    @if($invoice->billing_address)
                    <div class="pi-addr-block">
                        <div class="pi-addr-lbl">📍 Billing Address</div>
                        <div class="pi-addr-val">{{ $invoice->billing_address }}</div>
                    </div>
                    @endif
                    @if($invoice->shipping_address && $invoice->shipping_address !== $invoice->billing_address)
                    <div class="pi-addr-block" style="margin-top:8px;">
                        <div class="pi-addr-lbl">🚚 Shipping Address</div>
                        <div class="pi-addr-val">{{ $invoice->shipping_address }}</div>
                    </div>
                    @endif

                    {{-- Opening balance — DISPLAY ONLY, never modified --}}
                    @if(($partyModel?->opening_balance ?? 0) > 0)
                    <div class="pi-opening-bal-note">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Opening Balance: <strong>₹ {{ number_format($partyModel->opening_balance, 2) }}</strong>
                        <span class="pi-ob-note-tag">Reference only — never modified by transactions</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Purchase Executive --}}
            @if($invoice->purchaseExecutive)
            <div class="pi-card">
                <div class="pi-card-hd">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Purchase Executive
                </div>
                <div class="pi-card-bd">
                    <div class="pi-pe-name">{{ $invoice->purchaseExecutive->name }}</div>
                    <div class="pi-pe-meta">
                        @if($invoice->purchaseExecutive->phone)
                            <span>📱 {{ $invoice->purchaseExecutive->phone }}</span>
                        @endif
                        @if($invoice->purchaseExecutive->email)
                            <span>✉️ {{ $invoice->purchaseExecutive->email }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Invoice Details --}}
            <div class="pi-card">
                <div class="pi-card-hd">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="8" y1="10" x2="16" y2="10"/></svg>
                    Invoice Details
                </div>
                <div class="pi-card-bd">
                    <div class="pi-info-table">
                        <div class="pi-info-row"><span class="pi-info-l">Invoice No.</span><span class="pi-info-v pi-mono">{{ $invoice->invoice_number }}</span></div>
                        <div class="pi-info-row"><span class="pi-info-l">Invoice Date</span><span class="pi-info-v">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M, Y') }}</span></div>
                        @if($invoice->due_date)
                        <div class="pi-info-row"><span class="pi-info-l">Due Date</span><span class="pi-info-v">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M, Y') }}</span></div>
                        @endif
                        @if($invoice->po_number)
                        <div class="pi-info-row"><span class="pi-info-l">PO Number</span><span class="pi-info-v pi-mono">{{ $invoice->po_number }}</span></div>
                        @endif
                        @if($invoice->payment_terms)
                        <div class="pi-info-row"><span class="pi-info-l">Terms</span><span class="pi-info-v">{{ $invoice->payment_terms }}</span></div>
                        @endif
                        <div class="pi-info-row">
                            <span class="pi-info-l">Warehouse</span>
                            <span class="pi-info-v">{{ $invoice->warehouse?->name ?? '—' }}</span>
                        </div>
                        <div class="pi-info-row">
                            <span class="pi-info-l">Tax Type</span>
                            <span class="pi-info-v">{{ $invoice->tax_type === 'intra' ? 'Intra-State (CGST+SGST)' : 'Inter-State (IGST)' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ledger / Balance Card --}}
            {{-- Opening balance shown for reference; final balance = opening + transactions --}}
            @if(!empty($ledgerBalance))
            <div class="pi-card pi-ledger-card">
                <div class="pi-card-hd">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    {{ $ledgerBalance['label'] }}
                </div>
                <div class="pi-card-bd">
                    <div class="pi-ledger-row pi-ledger-row-ob">
                        <span>Opening Balance</span>
                        <span>₹ {{ number_format($ledgerBalance['opening_balance'], 2) }}</span>
                    </div>
                    @if($partyType === 'vendor')
                    <div class="pi-ledger-row">
                        <span>Total Purchases (Confirmed)</span>
                        <span class="pi-ledger-plus">+ ₹ {{ number_format($ledgerBalance['total_purchased'], 2) }}</span>
                    </div>
                    <div class="pi-ledger-row">
                        <span>Total Payments Made</span>
                        <span class="pi-ledger-minus">− ₹ {{ number_format($ledgerBalance['total_paid'], 2) }}</span>
                    </div>
                    @else
                    <div class="pi-ledger-row">
                        <span>Goods Purchased from {{ ucfirst($partyType) }}</span>
                        <span>₹ {{ number_format($ledgerBalance['total_purchased'], 2) }}</span>
                    </div>
                    <div class="pi-ledger-row">
                        <span>Total Paid to {{ ucfirst($partyType) }}</span>
                        <span class="pi-ledger-minus">− ₹ {{ number_format($ledgerBalance['total_paid'], 2) }}</span>
                    </div>
                    @endif
                    <div class="pi-ledger-divider"></div>
                    <div class="pi-ledger-row pi-ledger-total">
                        <span>{{ $ledgerBalance['balance_label'] }}</span>
                        <span class="{{ $ledgerBalance['balance'] > 0 ? 'pi-ledger-bal-pos' : 'pi-ledger-bal-neg' }}">
                            ₹ {{ number_format(abs($ledgerBalance['balance']), 2) }}
                        </span>
                    </div>
                    <p class="pi-ledger-note">
                        * Opening balance is a reference figure and is <strong>never modified</strong> by transactions. Balance is calculated dynamically.
                    </p>
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($invoice->notes)
            <div class="pi-card">
                <div class="pi-card-hd">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Notes
                </div>
                <div class="pi-card-bd">
                    <p class="pi-notes-txt">{{ $invoice->notes }}</p>
                </div>
            </div>
            @endif

        </div>{{-- /left --}}

        {{-- ========== RIGHT COLUMN ========== --}}
        <div class="pi-show-right">

            {{-- Items Table --}}
            <div class="pi-card">
                <div class="pi-card-hd">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    Items <span class="pi-item-count">({{ $invoice->items->count() }})</span>
                </div>
                <div style="padding:0;overflow-x:auto;">
                    <table class="pi-items-tbl">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>SKU</th>
                                @if($invoice->invoice_type === 'gst')<th>HSN</th>@endif
                                <th>Unit</th>
                                <th class="ta-r">Qty</th>
                                <th class="ta-r">Purchase Price</th>
                                <th class="ta-r">MRP</th>
                                @if($invoice->invoice_type === 'gst')
                                <th class="ta-r">Tax %</th>
                                <th class="ta-r">Tax Amt</th>
                                @endif
                                <th class="ta-r">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $i => $item)
                            @php
                                $taxAmt = $invoice->invoice_type === 'gst'
                                    ? round($item->quantity * $item->purchase_price * ($item->tax_percent ?? 0) / 100, 2)
                                    : 0;
                            @endphp
                            <tr>
                                <td class="pi-idx">{{ $i + 1 }}</td>
                                <td>
                                    <div class="pi-item-name">{{ $item->product_name }}</div>
                                    @if($item->variant_name)
                                        <div class="pi-item-var">{{ $item->variant_name }}</div>
                                    @endif
                                </td>
                                <td class="pi-mono">{{ $item->sku ?: '—' }}</td>
                                @if($invoice->invoice_type === 'gst')
                                    <td class="pi-mono">{{ $item->hsn_sac ?: '—' }}</td>
                                @endif
                                <td>{{ $item->unit ?? 'PCS' }}</td>
                                <td class="ta-r">{{ number_format($item->quantity, 2) }}</td>
                                <td class="ta-r">₹ {{ number_format($item->purchase_price, 2) }}</td>
                                <td class="ta-r">₹ {{ number_format($item->mrp_price ?? 0, 2) }}</td>
                                @if($invoice->invoice_type === 'gst')
                                    <td class="ta-r">{{ $item->tax_percent ?? 0 }}%</td>
                                    <td class="ta-r">₹ {{ number_format($taxAmt, 2) }}</td>
                                @endif
                                <td class="ta-r pi-item-total">₹ {{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="{{ $invoice->invoice_type === 'gst' ? 10 : 7 }}" class="ta-r" style="font-weight:700;padding:10px;">SUBTOTAL</td>
                                <td class="ta-r" style="font-weight:700;padding:10px;">₹ {{ number_format($invoice->subtotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Summary Card --}}
            <div class="pi-card">
                <div class="pi-card-hd">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Invoice Summary
                </div>
                <div class="pi-card-bd">
                    <div class="pi-summary">
                        <div class="pi-sum-row">
                            <span>Total MRP</span>
                            <span>₹ {{ number_format($invoice->total_mrp ?? 0, 2) }}</span>
                        </div>
                        <div class="pi-sum-row">
                            <span>Subtotal (Purchase)</span>
                            <span>₹ {{ number_format($invoice->subtotal, 2) }}</span>
                        </div>

                        @if($invoice->extra_discount > 0)
                        <div class="pi-sum-row pi-sum-disc">
                            <span>
                                Discount
                                @if($invoice->extra_discount_type === 'percent')
                                    ({{ $invoice->extra_discount }}%)
                                @else
                                    (Fixed)
                                @endif
                            </span>
                            <span>
                                @php
                                    $discAmt = $invoice->extra_discount_type === 'percent'
                                        ? $invoice->subtotal * $invoice->extra_discount / 100
                                        : $invoice->extra_discount;
                                @endphp
                                − ₹ {{ number_format($discAmt, 2) }}
                            </span>
                        </div>
                        @endif

                        @if($invoice->invoice_type === 'gst')
                            @if($invoice->tax_type === 'intra')
                                <div class="pi-sum-row pi-sum-tax">
                                    <span>CGST</span><span>+ ₹ {{ number_format($invoice->cgst_total ?? 0, 2) }}</span>
                                </div>
                                <div class="pi-sum-row pi-sum-tax">
                                    <span>SGST</span><span>+ ₹ {{ number_format($invoice->sgst_total ?? 0, 2) }}</span>
                                </div>
                            @else
                                <div class="pi-sum-row pi-sum-tax">
                                    <span>IGST</span><span>+ ₹ {{ number_format($invoice->igst_total ?? 0, 2) }}</span>
                                </div>
                            @endif
                            <div class="pi-sum-row pi-sum-tax-total">
                                <span>Total Tax</span>
                                <span>₹ {{ number_format($invoice->tax_total, 2) }}</span>
                            </div>
                        @endif

                        @if($invoice->extra_charge > 0)
                        <div class="pi-sum-row">
                            <span>{{ $invoice->charge_name ?: 'Other Charge' }}</span>
                            <span>+ ₹ {{ number_format($invoice->extra_charge, 2) }}</span>
                        </div>
                        @endif

                        @if($invoice->round_off != 0)
                        <div class="pi-sum-row pi-sum-muted">
                            <span>Round Off</span>
                            <span>{{ $invoice->round_off > 0 ? '+' : '' }} ₹ {{ number_format($invoice->round_off, 2) }}</span>
                        </div>
                        @endif

                        <div class="pi-sum-divider"></div>

                        <div class="pi-sum-row pi-sum-grand">
                            <span>Grand Total</span>
                            <span>₹ {{ number_format($invoice->grand_total, 2) }}</span>
                        </div>

                        <div class="pi-sum-divider"></div>

                        <div class="pi-sum-row">
                            <span>Total Paid</span>
                            <span class="pi-sum-paid">₹ {{ number_format($invoice->total_paid, 2) }}</span>
                        </div>
                        <div class="pi-sum-row pi-sum-balance">
                            <span>Balance Due</span>
                            <span class="pi-sum-bal-amt">₹ {{ number_format($invoice->balance_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer metadata --}}
            <div class="pi-footer-meta">
                <span>Created by <strong>{{ $invoice->creator?->name ?? 'System' }}</strong> on {{ $invoice->created_at->format('d M Y, h:i A') }}</span>
                @if($invoice->updated_at != $invoice->created_at)
                    <span>Updated: {{ $invoice->updated_at->format('d M Y, h:i A') }}</span>
                @endif
            </div>

        </div>{{-- /right --}}
    </div>{{-- /grid --}}
</div>

{{-- ==================== MODALS ==================== --}}
@foreach([
    ['id'=>'generateModal','title'=>'Generate Invoice','sub'=>'Stock will be added to warehouse','btnId'=>'confirmGenerate','btnClass'=>'pi-mconf-generate','btnTxt'=>'Generate Invoice','ico'=>'#f97316','icoSvg'=>'<path d="M5 12h14M12 5l7 7-7 7"/>'],
    ['id'=>'cancelModal',  'title'=>'Cancel Invoice',  'sub'=>'Stock will be removed from warehouse','btnId'=>'confirmCancel', 'btnClass'=>'pi-mconf-cancel', 'btnTxt'=>'Cancel Invoice', 'ico'=>'#dc2626','icoSvg'=>'<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'],
    ['id'=>'deleteModal',  'title'=>'Delete Invoice',  'sub'=>'This cannot be undone',              'btnId'=>'confirmDelete', 'btnClass'=>'pi-mconf-delete', 'btnTxt'=>'Delete Invoice', 'ico'=>'#ef4444','icoSvg'=>'<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>'],
] as $m)
<div class="pi-modal" id="{{ $m['id'] }}">
    <div class="pi-modal-overlay" onclick="closeAllModals()"></div>
    <div class="pi-modal-box">
        <div class="pi-modal-hd">
            <span class="pi-modal-ico" style="background:{{ $m['ico'] }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">{!! $m['icoSvg'] !!}</svg>
            </span>
            <div>
                <div class="pi-modal-title">{{ $m['title'] }}</div>
                <div class="pi-modal-sub">{{ $m['sub'] }}</div>
            </div>
            <button class="pi-modal-x" onclick="closeAllModals()">×</button>
        </div>
        <div class="pi-modal-bd">
            <p>Are you sure you want to proceed with "<strong>{{ $m['title'] }}</strong>" for invoice <strong>{{ $invoice->invoice_number }}</strong>?</p>
        </div>
        <div class="pi-modal-ft">
            <button class="pi-mconf-cancel-btn" onclick="closeAllModals()">Cancel</button>
            <button id="{{ $m['btnId'] }}" class="{{ $m['btnClass'] }}">{{ $m['btnTxt'] }}</button>
        </div>
    </div>
</div>
@endforeach

@push('styles')
<style>
/* ===========================
   PURCHASE INVOICE SHOW
=========================== */
.pi-show-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-size: 12.5px;
    color: #1e293b;
    padding: 18px 20px 60px;
    max-width: 1450px;
}

/* Alerts */
#alertBox {
    position: fixed; top: 16px; right: 16px; z-index: 9999;
    display: flex; flex-direction: column; gap: 7px;
}
.pi-alert {
    padding: 10px 14px; border-radius: 6px; font-size: 12px; font-weight: 500;
    box-shadow: 0 4px 14px rgba(0,0,0,.12); animation: alertIn .25s ease;
    min-width: 220px; max-width: 320px;
}
@keyframes alertIn { from{transform:translateX(110%);opacity:0} to{transform:translateX(0);opacity:1} }
.pi-alert-success { background:#f0fdf4; color:#166534; border-left:3px solid #22c55e; }
.pi-alert-error   { background:#fef2f2; color:#991b1b; border-left:3px solid #ef4444; }

/* Header */
.pi-show-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid #e2e8f0;
    flex-wrap: wrap; gap: 12px;
}
.pi-show-header-left { display: flex; align-items: center; gap: 14px; }
.pi-show-back-btn {
    display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;
    color: #475569; font-size: 11.5px; text-decoration: none; transition: all .15s;
}
.pi-show-back-btn:hover { background: #f1f5f9; color: #1e293b; }
.pi-show-title-group { display: flex; align-items: center; gap: 10px; }
.pi-show-title-group h1 { font-size: 18px; font-weight: 700; margin: 0; }
.pi-show-inv-num {
    padding: 4px 10px; background: #f1f5f9; border: 1px solid #e2e8f0;
    border-radius: 20px; font-size: 11.5px; font-weight: 600;
    color: #475569; font-family: 'Courier New', monospace;
}
.pi-show-actions { display: flex; gap: 8px; flex-wrap: wrap; }

/* Action buttons */
.pi-act-btn {
    display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px;
    border: none; border-radius: 6px; font-size: 11.5px; font-weight: 600;
    cursor: pointer; text-decoration: none; transition: all .15s; font-family: inherit;
}
.pi-act-btn:hover { transform: translateY(-1px); }
.pi-act-generate { background: #f97316; color: white; }
.pi-act-generate:hover { background: #ea580c; box-shadow: 0 4px 12px rgba(249,115,22,.3); }
.pi-act-edit     { background: #fef3c7; color: #92400e; }
.pi-act-edit:hover { background: #fde68a; }
.pi-act-delete   { background: #fee2e2; color: #b91c1c; }
.pi-act-delete:hover { background: #fecaca; }
.pi-act-cancel   { background: #fee2e2; color: #b91c1c; }
.pi-act-cancel:hover { background: #fecaca; }
.pi-act-print    { background: #f1f5f9; color: #475569; }
.pi-act-print:hover { background: #e2e8f0; }

/* Status bar */
.pi-status-bar {
    display: flex; flex-wrap: wrap; gap: 8px;
    margin-bottom: 22px; align-items: center;
}
.pi-status-chip {
    display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
    border: 1px solid transparent;
}
.pi-status-dot { width: 7px; height: 7px; border-radius: 50%; }
.pi-s-draft     { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
.pi-s-confirmed { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
.pi-s-cancelled { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.pi-p-paid      { background: #d1fae5; color: #065f46; border-color: #a7f3d0; }
.pi-p-partial   { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.pi-p-unpaid    { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.pi-type-chip   { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
.pi-pt-vendor       { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
.pi-pt-dealer       { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.pi-pt-distributor  { background: #d1fae5; color: #065f46; border-color: #a7f3d0; }
.pi-tax-chip    { background: #f3e8ff; color: #6b21a8; border-color: #e9d5ff; }

/* Layout */
.pi-show-grid       { display: grid; grid-template-columns: 340px 1fr; gap: 20px; }
.pi-show-left       { display: flex; flex-direction: column; gap: 0; }
.pi-show-right      { display: flex; flex-direction: column; gap: 0; }

/* Cards */
.pi-card {
    background: white; border: 1px solid #e2e8f0; border-radius: 8px;
    margin-bottom: 16px; overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.pi-card-hd {
    display: flex; align-items: center; gap: 8px; padding: 11px 16px;
    background: #f8fafc; border-bottom: 1px solid #e2e8f0;
    font-size: 12px; font-weight: 700; color: #1e293b;
}
.pi-card-bd { padding: 14px 16px; }

/* Party card */
.pi-party-name-row { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.pi-party-name     { font-size: 15px; font-weight: 700; }
.pi-badge {
    display: inline-block; padding: 2px 8px; border-radius: 10px;
    font-size: 9.5px; font-weight: 700;
}
.pi-badge-vendor      { background: #e0f2fe; color: #0369a1; }
.pi-badge-dealer      { background: #fef3c7; color: #92400e; }
.pi-badge-distributor { background: #d1fae5; color: #065f46; }

.pi-info-table { display: flex; flex-direction: column; gap: 6px; margin-bottom: 12px; }
.pi-info-row   { display: flex; align-items: flex-start; font-size: 12px; }
.pi-info-l     { width: 80px; color: #64748b; font-weight: 500; flex-shrink: 0; }
.pi-info-v     { color: #1e293b; font-weight: 500; }
.pi-mono       { font-family: 'Courier New', monospace; font-size: 11px; }

.pi-addr-block { }
.pi-addr-lbl   { font-size: 10px; font-weight: 700; color: #f97316; text-transform: uppercase;
                 letter-spacing: .4px; margin-bottom: 4px; }
.pi-addr-val   { font-size: 11.5px; color: #475569; line-height: 1.6;
                 padding: 8px; background: #f8fafc; border-radius: 4px;
                 border: 1px solid #e2e8f0; }

/* Opening balance note */
.pi-opening-bal-note {
    margin-top: 12px; padding: 8px 10px;
    background: #fff7ed; border: 1px solid #fed7aa; border-radius: 6px;
    font-size: 11px; color: #92400e; display: flex; align-items: flex-start;
    gap: 6px; flex-wrap: wrap;
}
.pi-opening-bal-note strong { color: #c2410c; }
.pi-ob-note-tag {
    display: inline-block; margin-left: 6px; font-size: 9px;
    background: #fef3c7; color: #92400e; padding: 1px 6px;
    border-radius: 10px; font-weight: 600;
}

/* PE card */
.pi-pe-name { font-size: 14px; font-weight: 700; color: #f97316; margin-bottom: 5px; }
.pi-pe-meta { display: flex; flex-direction: column; gap: 3px; font-size: 11px; color: #64748b; }

/* Item count */
.pi-item-count { font-size: 11px; font-weight: 400; color: #64748b; margin-left: 4px; }

/* Items table */
.pi-items-tbl { width: 100%; border-collapse: collapse; font-size: 11.5px; min-width: 700px; }
.pi-items-tbl th {
    padding: 10px 8px; background: #f8fafc; font-size: 10.5px; font-weight: 700;
    color: #475569; text-transform: uppercase; letter-spacing: .3px;
    border-bottom: 2px solid #e2e8f0; white-space: nowrap; text-align: left;
}
.pi-items-tbl td  { padding: 9px 8px; border-bottom: 1px solid #f1f5f9; }
.pi-items-tbl tfoot td { padding: 9px 8px; background: #f8fafc; border-top: 2px solid #e2e8f0; }
.pi-items-tbl tr:hover td { background: #f8fafc; }
.pi-items-tbl tfoot tr:hover td { background: #f8fafc; }
.ta-r { text-align: right; }
.pi-idx  { color: #94a3b8; font-size: 11px; }
.pi-item-name  { font-weight: 600; color: #1e293b; }
.pi-item-var   { font-size: 10px; color: #64748b; margin-top: 2px; }
.pi-item-total { font-weight: 700; }

/* Summary */
.pi-summary    { max-width: 440px; margin-left: auto; }
.pi-sum-row    { display: flex; justify-content: space-between; align-items: center;
                 padding: 5px 0; font-size: 12px; }
.pi-sum-row span:first-child  { color: #64748b; }
.pi-sum-row span:last-child   { font-weight: 600; }
.pi-sum-disc span:last-child  { color: #dc2626; }
.pi-sum-tax span:last-child   { color: #475569; }
.pi-sum-tax-total span:last-child { color: #f97316; font-weight: 700; }
.pi-sum-muted span            { color: #94a3b8 !important; }
.pi-sum-divider { height: 1px; background: #e2e8f0; margin: 8px 0; }
.pi-sum-grand  { padding: 8px 0; }
.pi-sum-grand span:first-child { font-size: 13px; font-weight: 800; color: #1e293b; }
.pi-sum-grand span:last-child  { font-size: 16px; font-weight: 800; color: #f97316; }
.pi-sum-paid   { color: #059669 !important; }
.pi-sum-balance span:first-child { font-weight: 600; }
.pi-sum-bal-amt { color: #dc2626 !important; font-weight: 800 !important; font-size: 13px; }

/* Ledger card */
.pi-ledger-card { border-color: #fed7aa; }
.pi-ledger-card .pi-card-hd { background: #fff7ed; border-color: #fed7aa; }
.pi-ledger-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 5px 0; font-size: 12px;
}
.pi-ledger-row span:first-child { color: #64748b; }
.pi-ledger-row-ob span { color: #475569; font-weight: 500; }
.pi-ledger-plus  { color: #dc2626; font-weight: 600; }
.pi-ledger-minus { color: #059669; font-weight: 600; }
.pi-ledger-divider { height: 1px; background: #fed7aa; margin: 8px 0; }
.pi-ledger-total span:first-child { font-size: 13px; font-weight: 700; color: #1e293b; }
.pi-ledger-bal-pos { font-size: 15px; font-weight: 800; color: #dc2626; }
.pi-ledger-bal-neg { font-size: 15px; font-weight: 800; color: #059669; }
.pi-ledger-note {
    margin: 10px 0 0; font-size: 10px; color: #92400e;
    background: #fff7ed; padding: 6px 8px; border-radius: 4px;
    border-left: 2px solid #f97316; line-height: 1.5;
}

/* Notes */
.pi-notes-txt {
    font-size: 12px; color: #475569; line-height: 1.7; margin: 0;
    padding: 8px; background: #f8fafc; border-radius: 4px;
    border-left: 3px solid #f97316; white-space: pre-line;
}

/* Footer */
.pi-footer-meta {
    font-size: 11px; color: #94a3b8; text-align: right;
    padding: 10px 4px; border-top: 1px dashed #e2e8f0;
    display: flex; flex-direction: column; gap: 2px;
}

/* Modals */
.pi-modal {
    display: none; position: fixed; inset: 0; z-index: 1000;
    align-items: center; justify-content: center;
}
.pi-modal-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.42); backdrop-filter: blur(2px); }
.pi-modal-box {
    position: relative; background: white; border-radius: 10px;
    width: 370px; max-width: 92%; box-shadow: 0 20px 50px rgba(0,0,0,.15);
    animation: piMod .2s ease;
}
@keyframes piMod { from{opacity:0;transform:scale(.95) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
.pi-modal-hd {
    display: flex; align-items: center; gap: 10px; padding: 14px 16px;
    background: #f8fafc; border-bottom: 1px solid #e2e8f0;
    border-radius: 10px 10px 0 0;
}
.pi-modal-ico {
    width: 34px; height: 34px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.pi-modal-title { font-size: 13px; font-weight: 700; }
.pi-modal-sub   { font-size: 10.5px; color: #64748b; }
.pi-modal-x {
    margin-left: auto; background: none; border: none; font-size: 20px;
    color: #94a3b8; cursor: pointer; width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center; border-radius: 5px;
}
.pi-modal-x:hover { background: #e2e8f0; color: #1e293b; }
.pi-modal-bd    { padding: 16px; }
.pi-modal-bd p  { font-size: 12.5px; color: #374151; line-height: 1.6; margin: 0; }
.pi-modal-ft {
    display: flex; justify-content: flex-end; gap: 8px; padding: 12px 16px;
    background: #fafafa; border-top: 1px solid #e2e8f0; border-radius: 0 0 10px 10px;
}
.pi-mconf-cancel-btn {
    padding: 7px 16px; border: 1px solid #e2e8f0; border-radius: 5px;
    background: #f8fafc; color: #475569; font-size: 12px; font-weight: 600; cursor: pointer;
}
.pi-mconf-cancel-btn:hover { background: #f1f5f9; }
.pi-mconf-generate, .pi-mconf-cancel, .pi-mconf-delete {
    padding: 7px 16px; border: none; border-radius: 5px; font-size: 12px;
    font-weight: 600; cursor: pointer; color: white; font-family: inherit;
}
.pi-mconf-generate { background: #f97316; }
.pi-mconf-generate:hover { background: #ea580c; }
.pi-mconf-cancel   { background: #dc2626; }
.pi-mconf-cancel:hover { background: #b91c1c; }
.pi-mconf-delete   { background: #ef4444; }
.pi-mconf-delete:hover { background: #dc2626; }
.pi-mconf-generate:disabled,
.pi-mconf-cancel:disabled,
.pi-mconf-delete:disabled { opacity: .55; cursor: not-allowed; }

/* Print */
@media print {
    .pi-show-header, .pi-show-actions, .pi-show-back-btn,
    .pi-status-bar, .pi-modal, .pi-ledger-card,
    .pi-footer-meta, #alertBox { display: none !important; }
    .pi-show-container { padding: 0; }
    .pi-show-grid { grid-template-columns: 1fr; }
    .pi-card { break-inside: avoid; }
}

/* Responsive */
@media (max-width: 1100px) {
    .pi-show-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .pi-show-container { padding: 10px; }
    .pi-show-header { flex-direction: column; align-items: flex-start; }
    .pi-show-actions { width: 100%; }
    .pi-act-btn { flex: 1; justify-content: center; }
    .pi-status-bar { gap: 6px; }
}
</style>
@endpush

@push('scripts')
<script>
const invoiceId = '{{ $invoice->id }}';
const csrfToken = '{{ csrf_token() }}';

function showAlert(msg, type = 'success') {
    const box = document.getElementById('alertBox');
    const el  = document.createElement('div');
    el.className = 'pi-alert pi-alert-' + type;
    el.textContent = msg;
    box.appendChild(el);
    setTimeout(() => el.remove(), 4500);
}

// Open / close
function openGenerateModal() { document.getElementById('generateModal').style.display = 'flex'; }
function openCancelModal()   { document.getElementById('cancelModal').style.display   = 'flex'; }
function openDeleteModal()   { document.getElementById('deleteModal').style.display   = 'flex'; }
function closeAllModals() {
    ['generateModal','cancelModal','deleteModal'].forEach(id => {
        document.getElementById(id).style.display = 'none';
    });
}

// Generic POST action
async function doAction(url, btnId, loadingTxt, successCb) {
    const btn = document.getElementById(btnId);
    btn.disabled = true;
    const orig = btn.textContent;
    btn.textContent = loadingTxt;
    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        closeAllModals();
        if (data.success) {
            showAlert(data.message, 'success');
            successCb();
        } else {
            showAlert(data.message || 'Action failed', 'error');
            btn.disabled = false; btn.textContent = orig;
        }
    } catch {
        closeAllModals();
        showAlert('Something went wrong', 'error');
        btn.disabled = false; btn.textContent = orig;
    }
}

document.getElementById('confirmGenerate').addEventListener('click', () =>
    doAction(`/admin/purchases/${invoiceId}/generate`, 'confirmGenerate', 'Generating…', () =>
        setTimeout(() => location.reload(), 1500)
    )
);

document.getElementById('confirmCancel').addEventListener('click', () =>
    doAction(`/admin/purchases/${invoiceId}/cancel`, 'confirmCancel', 'Cancelling…', () =>
        setTimeout(() => location.reload(), 1500)
    )
);

document.getElementById('confirmDelete').addEventListener('click', async () => {
    const btn = document.getElementById('confirmDelete');
    btn.disabled = true; btn.textContent = 'Deleting…';
    try {
        const res  = await fetch(`/admin/purchases/${invoiceId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        closeAllModals();
        if (data.success) {
            showAlert('Invoice deleted', 'success');
            setTimeout(() => window.location.href = '{{ route("admin.purchases.index") }}', 1500);
        } else {
            showAlert(data.message || 'Delete failed', 'error');
            btn.disabled = false; btn.textContent = 'Delete Invoice';
        }
    } catch {
        closeAllModals();
        showAlert('Something went wrong', 'error');
        btn.disabled = false; btn.textContent = 'Delete Invoice';
    }
});

// ESC key
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAllModals(); });
</script>
@endpush
@endsection
