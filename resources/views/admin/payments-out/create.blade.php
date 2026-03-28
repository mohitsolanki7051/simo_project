@extends('layouts.admin')

@section('title', 'New Payment Out')
@section('header-title', 'Create Payment Out')

@section('content')
<div class="po-wrapper">

    {{-- ── Top Bar ──────────────────────────────────────────── --}}
    <div class="po-topbar">
        <div class="po-topbar-left">
            <h1 class="po-title">Payment Out</h1>
        </div>
        <a href="{{ route('admin.payments-out.index') }}" class="po-back-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to List
        </a>
    </div>

    {{-- ── Alert ────────────────────────────────────────────── --}}
    <div id="poAlert" class="po-alert" style="display:none;"></div>

    {{-- ── Mode Tabs ─────────────────────────────────────────── --}}
    <div class="po-tabs">
        <button type="button" class="po-tab active" id="tabPurchase" onclick="switchMode('purchase')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18l-2 13H5L3 6z"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            Purchase Payment
            <span class="po-tab-hint">Pay to vendor / dealer / distributor</span>
        </button>
        <button type="button" class="po-tab" id="tabCreditRefund" onclick="switchMode('credit_refund')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.5"/></svg>
            Credit Note Refund
            <span class="po-tab-hint">Refund to customer for returned goods</span>
        </button>
    </div>

    <form id="paymentForm" autocomplete="off">
        @csrf
        <input type="hidden" name="payment_subtype" id="paymentSubtype" value="purchase_payment">

        {{-- ══ ROW 1 : Party  +  Payment Meta ═══════════════════════ --}}
        <div class="po-row-2col">

            {{-- Left: Party Card --}}
            <div class="po-card">
                <div class="po-card-header" id="partyCardHeader">Party Name</div>
                <div class="po-card-body">
                    <div class="po-party-search-wrap" id="partySearchWrap">
                        <div class="po-search-field">
                            <svg class="po-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            <input type="text" id="partySearch" class="po-input po-search-input"
                                placeholder="Type party name, phone…" />
                            <button type="button" id="clearPartyBtn" class="po-clear-btn" style="display:none;" title="Clear">✕</button>
                        </div>
                        <div id="partyDropdown" class="po-party-dropdown" style="display:none;"></div>
                    </div>

                    {{-- Selected party display --}}
                    <div id="partySelected" style="display:none;">
                        <div class="po-selected-party">
                            <div class="po-party-info">
                                <span id="selPartyName" class="po-party-name-big"></span>
                                <span id="selPartyBadge" class="po-party-badge"></span>
                                <span id="selPartyPhone" class="po-party-phone"></span>
                            </div>
                            <button type="button" class="po-change-btn" onclick="resetParty()">Change</button>
                        </div>
                        <input type="hidden" name="party_id" id="partyId">
                        <input type="hidden" name="party_type" id="partyType">

                        {{-- PURCHASE: balance row (with Debit Notes) --}}
                        <div id="purchaseBalanceSection">
                            <div class="po-balance-row" id="balanceRow">
                                <span class="po-balance-label">Total Payable:</span>
                                <span class="po-balance-val" id="currentBalanceDisplay">₹0.00</span>
                            </div>
                            <div id="debitNoteBlock" style="display:none; margin-top:10px; padding:10px 12px;
                                background:#fefce8; border:1px solid #fde68a; border-radius:7px; font-size:13px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                    <span style="font-weight:600; color:#92400e;">Debit Notes Available (Reduces Payable)</span>
                                    <span id="debitTotalDisplay" style="font-weight:700; color:#065f46;">-₹0.00</span>
                                </div>
                                <div id="debitNotesList" style="font-size:12px; color:#92400e; margin-bottom:8px;"></div>
                                <div style="display:flex; justify-content:space-between; padding-top:8px;
                                    border-top:1px solid #fde68a; font-weight:600;">
                                    <span style="color:#374151;">Net Payable</span>
                                    <span id="netPayableDisplay" style="color:#991b1b; font-size:14px;">₹0.00</span>
                                </div>
                            </div>
                        </div>

                        {{-- CREDIT REFUND: credit note block (for customer refunds) --}}
                        <div id="creditRefundSection" style="display:none; margin-top:10px;">
                            <div class="po-credit-block">
                                <div class="po-credit-block-header">
                                    <span>Credit Notes Available for Refund</span>
                                    <span id="creditRefundTotalDisplay" class="po-credit-total">₹0.00</span>
                                </div>
                                <div id="creditNotesRefundList" class="po-credit-list"></div>
                            </div>
                        </div>

                        {{-- Amount to Pay --}}
                        <div id="amountSection" style="display:none; margin-top:14px;">
                            <label class="po-label" id="amountLabel">Amount to Pay <span class="po-req">*</span></label>
                            <div class="po-amount-wrap">
                                <span class="po-currency">₹</span>
                                <input type="number" step="0.01" min="0.01" id="amountToPay"
                                       name="amount" class="po-input po-amount-input" placeholder="0.00"
                                       onkeydown="limitDecimals(event, this)">
                            </div>
                            <div id="amountValidation" class="po-val-msg" style="display:none;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Payment Meta Card --}}
            <div class="po-card">
                <div class="po-card-header">Payment Details</div>
                <div class="po-card-body">
                    <div class="po-meta-grid">
                        <div class="po-field">
                            <label class="po-label">Payment Date <span class="po-req">*</span></label>
                            <div class="po-date-wrap">
                                <svg class="po-date-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <input type="date" name="payment_date" id="paymentDate"
                                       class="po-input po-date-input" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="po-field">
                            <label class="po-label">Payment Mode <span class="po-req">*</span></label>
                            <select name="payment_method" id="paymentMethod" class="po-input po-select" required>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="card">Card</option>
                            </select>
                        </div>

                        <div class="po-field">
                            <label class="po-label">Payment Out Prefix</label>
                            <input type="text" class="po-input po-prefix-display" value="SIM/SPO/" readonly>
                        </div>

                        <div class="po-field">
                            <label class="po-label">Payment Out Number</label>
                            <input type="text" class="po-input po-prefix-display" id="paymentNumberDisplay"
                                   value="{{ $paymentNumber }}" readonly>
                        </div>

                        <div class="po-field po-field-full">
                            <label class="po-label">Reference No.</label>
                            <input type="text" name="reference_no" class="po-input"
                                   placeholder="Transaction ID / UPI Ref / Cheque No">
                        </div>

                        <div class="po-field po-field-full">
                            <label class="po-label">Notes</label>
                            <textarea name="notes" class="po-input po-textarea"
                                      placeholder="Enter Notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ ROW 2 : Purchase Invoices Table (only in purchase mode) ══════════ --}}
        <div class="po-card" id="invoicesSection" style="display:none; margin-top:16px;">
            <div class="po-card-header po-invoices-header">
                <span>Settle purchase invoices with this payment</span>
                <div class="po-inv-search-wrap">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" id="invoiceSearch" class="po-inv-search" placeholder="Search Invoice Number">
                </div>
            </div>
            <div class="po-card-body po-table-body">
                <table class="po-table" id="invoicesTable">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="selectAllCb" class="po-cb"></th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th>Invoice No.</th>
                            <th>Warehouse</th>
                            <th>Invoice Amount</th>
                            <th>Amount to Pay</th>
                        </thead>
                    <tbody id="invoicesTbody">
                        <tr id="noInvoicesRow">
                            <td colspan="7" class="po-empty">Select a party to view invoices</td>
                        </tr>
                    </tbody>
                    <tfoot id="invoicesTfoot" style="display:none;">
                        <tr class="po-tfoot-row">
                            <td colspan="5"><strong>Total</strong></td>
                            <td id="tfootInvoiceTotal">₹0.00</td>
                            <td id="tfootAmountToPay">₹0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ══ ROW 2B: Debit Notes Table (only in purchase mode for adjustment) ══════ --}}
        <div class="po-card" id="debitNotesAdjustSection" style="display:none; margin-top:16px;">
            <div class="po-card-header">
                <span>Debit Notes being adjusted with this payment</span>
            </div>
            <div class="po-card-body po-table-body">
                <table class="po-table">
                    <thead>
                        <tr>
                            <th>Debit Note No.</th>
                            <th>Date</th>
                            <th>Original Amount</th>
                            <th>Used</th>
                            <th>Remaining</th>
                            <th>Adjusted Amount</th>
                        </tr>
                    </thead>
                    <tbody id="debitNotesAdjustTbody">
                        <tr><td colspan="6" class="po-empty">Select a party to view debit notes</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ══ ROW 2C: Credit Notes Table (only in credit refund mode) ══════ --}}
        <div class="po-card" id="creditNotesSection" style="display:none; margin-top:16px;">
            <div class="po-card-header">
                <span>Credit Notes being refunded to customer</span>
            </div>
            <div class="po-card-body po-table-body">
                <table class="po-table">
                    <thead>
                        <tr>
                            <th>Credit Note No.</th>
                            <th>Date</th>
                            <th>Original Amount</th>
                            <th>Used</th>
                            <th>Remaining</th>
                            <th>Refund Amount</th>
                        </tr>
                    </thead>
                    <tbody id="creditNotesTbody">
                        <tr><td colspan="6" class="po-empty">Select a party to view credit notes</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ══ ROW 3 : Submit ════════════════════════════════════════ --}}
        <div class="po-actions" id="submitSection" style="display:none;">

            {{-- Purchase summary --}}
            <div class="po-summary-strip" id="purchaseSummaryStrip">
                <div class="po-summary-item">
                    <span>Opening Balance</span>
                    <strong id="sumOpening">₹0.00</strong>
                </div>
                <div class="po-summary-sep">+</div>
                <div class="po-summary-item">
                    <span>Invoice Dues</span>
                    <strong id="sumInvoice">₹0.00</strong>
                </div>
                <div class="po-summary-sep">−</div>
                <div class="po-summary-item">
                    <span>Debit Notes</span>
                    <strong id="sumDebit" style="color:var(--po-success);">₹0.00</strong>
                </div>
                <div class="po-summary-sep">=</div>
                <div class="po-summary-item po-summary-total">
                    <span>Net Payable</span>
                    <strong id="sumTotal">₹0.00</strong>
                </div>
                <div class="po-summary-spacer"></div>
                <div class="po-summary-item po-summary-paying">
                    <span>You are paying</span>
                    <strong id="sumPaying">₹0.00</strong>
                </div>
            </div>

            {{-- Credit Refund summary --}}
            <div class="po-summary-strip" id="creditRefundSummaryStrip" style="display:none;">
                <div class="po-summary-item">
                    <span>Credit Note Balance</span>
                    <strong id="sumCreditBalance">₹0.00</strong>
                </div>
                <div class="po-summary-sep">=</div>
                <div class="po-summary-item po-summary-total">
                    <span>Max Refundable</span>
                    <strong id="sumMaxRefund">₹0.00</strong>
                </div>
                <div class="po-summary-spacer"></div>
                <div class="po-summary-item po-summary-paying">
                    <span>Refund Amount</span>
                    <strong id="sumRefundPaying">₹0.00</strong>
                </div>
            </div>

            <button type="submit" class="po-submit-btn" id="submitBtn" disabled>
                <span id="btnText">Save Payment</span>
                <span id="btnLoader" style="display:none;">
                    <svg class="po-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    Processing…
                </span>
            </button>
        </div>

    </form>
</div>

@push('styles')
<style>
/* ─── CSS Variables ─────────────────────────────────────── */
:root {
    --po-bg: #f4f6f9;
    --po-white: #ffffff;
    --po-border: #dde1e7;
    --po-border-focus: #7c3aed;
    --po-text: #1a2033;
    --po-muted: #6b7280;
    --po-label: #374151;
    --po-primary: #7c3aed;
    --po-primary-hover: #6d28d9;
    --po-success: #12b76a;
    --po-danger: #f04438;
    --po-warn: #f79009;
    --po-badge-v-bg: #f5f3ff; --po-badge-v-txt: #6d28d9;
    --po-badge-d-bg: #fefce8; --po-badge-d-txt: #854d0e;
    --po-badge-dist-bg: #f0fdf4; --po-badge-dist-txt: #166534;
    --po-radius: 8px;
    --po-shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
}

/* ─── Wrapper ────────────────────────────────────────────── */
.po-wrapper { max-width: 1100px; margin: 0 auto; font-family: 'Segoe UI', system-ui, sans-serif; color: var(--po-text); }

/* ─── Top Bar ────────────────────────────────────────────── */
.po-topbar { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 20px; }
.po-title  { font-size: 22px; font-weight: 700; margin: 0 0 2px; }
.po-back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px; border: 1px solid var(--po-border); border-radius: var(--po-radius);
    background: #fa8526; color: #fff; font-size: 13px; font-weight: 500; text-decoration: none;
    transition: background .15s;
}
.po-back-btn:hover { background: #f0f1f3; color: var(--po-text); }

/* ─── Tabs ───────────────────────────────────────────────── */
.po-tabs {
    display: flex; gap: 0; margin-bottom: 16px;
    background: var(--po-white); border: 1px solid var(--po-border);
    border-radius: var(--po-radius); overflow: hidden;
    box-shadow: var(--po-shadow);
}
.po-tab {
    flex: 1; display: flex; flex-direction: column; align-items: center;
    gap: 3px; padding: 12px 16px;
    background: transparent; border: none; cursor: pointer;
    font-size: 14px; font-weight: 600; color: var(--po-muted);
    transition: background .15s, color .15s;
    border-right: 1px solid var(--po-border);
}
.po-tab:last-child { border-right: none; }
.po-tab svg { flex-shrink: 0; }
.po-tab-hint { font-size: 11px; font-weight: 400; color: var(--po-muted); }
.po-tab:hover { background: #f8f9fa; color: var(--po-text); }
.po-tab.active {
    background: #f5f3ff; color: var(--po-primary);
    border-bottom: 2px solid var(--po-primary);
}
.po-tab.active .po-tab-hint { color: var(--po-primary); opacity: .7; }

/* ─── Alert ─────────────────────────────────────────────── */
.po-alert { padding: 12px 16px; border-radius: var(--po-radius); font-size: 13.5px; font-weight: 500; margin-bottom: 16px; }
.po-alert.success { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
.po-alert.error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
.po-alert.warning { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }

/* ─── Layout ─────────────────────────────────────────────── */
.po-row-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 768px) { .po-row-2col { grid-template-columns: 1fr; } }

/* ─── Card ───────────────────────────────────────────────── */
.po-card { background: var(--po-white); border: 1px solid var(--po-border); border-radius: var(--po-radius); box-shadow: var(--po-shadow); }
.po-card-header { padding: 12px 16px; font-size: 13px; font-weight: 600; color: var(--po-label); border-bottom: 1px solid var(--po-border); background: #fafbfc; border-radius: var(--po-radius) var(--po-radius) 0 0; }
.po-card-body { padding: 16px; }

/* ─── Inputs ─────────────────────────────────────────────── */
.po-label { display: block; font-size: 12.5px; font-weight: 500; color: var(--po-label); margin-bottom: 5px; }
.po-req { color: var(--po-danger); }
.po-input {
    width: 100%; padding: 8px 11px; border: 1px solid var(--po-border); border-radius: 6px;
    font-size: 13.5px; color: var(--po-text); background: var(--po-white);
    transition: border .15s, box-shadow .15s; box-sizing: border-box;
}
.po-input:focus { outline: none; border-color: var(--po-border-focus); box-shadow: 0 0 0 3px rgba(124,58,237,.12); }
.po-input:read-only { background: #f8f9fa; color: var(--po-muted); cursor: default; }
.po-select { cursor: pointer; }
.po-textarea { resize: vertical; min-height: 72px; }
.po-prefix-display { font-size: 12.5px; }

/* ─── Party Search ───────────────────────────────────────── */
.po-party-search-wrap { position: relative; }
.po-search-field { position: relative; display: flex; align-items: center; }
.po-search-icon { position: absolute; left: 10px; color: var(--po-muted); pointer-events: none; }
.po-search-input { padding-left: 34px; padding-right: 32px; }
.po-clear-btn { position: absolute; right: 8px; background: none; border: none; color: var(--po-muted); font-size: 13px; cursor: pointer; padding: 2px 5px; line-height: 1; }
.po-clear-btn:hover { color: var(--po-danger); }

/* ─── Party Dropdown ─────────────────────────────────────── */
.po-party-dropdown { position: absolute; left: 0; right: 0; top: calc(100% + 4px); background: var(--po-white); border: 1px solid var(--po-border); border-radius: var(--po-radius); box-shadow: 0 8px 24px rgba(0,0,0,.12); z-index: 200; max-height: 320px; overflow-y: auto; }
.po-drop-item { padding: 11px 14px; cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: background .12s; }
.po-drop-item:last-child { border-bottom: none; }
.po-drop-item:hover { background: #f5f3ff; }
.po-drop-name { font-size: 14px; font-weight: 600; margin-bottom: 3px; }
.po-drop-meta { display: flex; align-items: center; gap: 10px; font-size: 12px; color: var(--po-muted); margin-bottom: 5px; }
.po-drop-due { display: flex; gap: 14px; font-size: 12px; background: #f8fafc; border-radius: 4px; padding: 5px 8px; }
.po-drop-due .due-total { font-weight: 600; color: var(--po-success); }
.po-drop-empty { padding: 20px; text-align: center; color: var(--po-muted); font-size: 13px; }
.po-drop-loading { padding: 16px; text-align: center; color: var(--po-muted); font-size: 13px; }

/* ─── Debit Note block ───────────────────────────────────── */
.po-debit-block {
    background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 7px;
    padding: 10px 12px; font-size: 13px;
}
.po-debit-block-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 8px; font-weight: 600; color: #5b21b6;
}
.po-debit-total { font-weight: 700; color: #065f46; font-size: 14px; }
.po-debit-list { font-size: 12px; color: #4c1d95; }
.po-debit-list-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 4px 0; border-bottom: 1px solid #ede9fe;
}
.po-debit-list-item:last-child { border-bottom: none; }

/* ─── Credit Note block ───────────────────────────────────── */
.po-credit-block {
    background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 7px;
    padding: 10px 12px; font-size: 13px;
}
.po-credit-block-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 8px; font-weight: 600; color: #5b21b6;
}
.po-credit-total { font-weight: 700; color: #065f46; font-size: 14px; }
.po-credit-list { font-size: 12px; color: #4c1d95; }
.po-credit-list-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 4px 0; border-bottom: 1px solid #ede9fe;
}
.po-credit-list-item:last-child { border-bottom: none; }

/* ─── Party Badge ────────────────────────────────────────── */
.po-party-badge { display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: capitalize; margin-left: 6px; }
.po-party-badge.vendor      { background: var(--po-badge-v-bg); color: var(--po-badge-v-txt); }
.po-party-badge.dealer      { background: var(--po-badge-d-bg); color: var(--po-badge-d-txt); }
.po-party-badge.distributor { background: var(--po-badge-dist-bg); color: var(--po-badge-dist-txt); }

/* ─── Selected Party ─────────────────────────────────────── */
.po-selected-party { display: flex; align-items: flex-start; justify-content: space-between; padding: 10px 12px; background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 7px; margin-bottom: 10px; }
.po-party-name-big { font-size: 15px; font-weight: 700; }
.po-party-phone    { display: block; font-size: 12px; color: var(--po-muted); margin-top: 3px; }
.po-change-btn { padding: 4px 12px; border: 1px solid var(--po-primary); border-radius: 5px; background: var(--po-white); color: var(--po-primary); font-size: 12px; font-weight: 500; cursor: pointer; white-space: nowrap; transition: background .15s; }
.po-change-btn:hover { background: #f5f3ff; }
.po-balance-row { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--po-muted); margin-bottom: 4px; }
.po-balance-val { font-weight: 600; color: var(--po-danger); }

/* ─── Amount ─────────────────────────────────────────────── */
.po-amount-wrap { position: relative; }
.po-currency { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); font-size: 14px; color: var(--po-muted); pointer-events: none; }
.po-amount-input { padding-left: 26px; font-size: 15px; font-weight: 600; }
.po-val-msg { font-size: 11.5px; margin-top: 4px; }
.po-val-msg.err  { color: var(--po-danger); }
.po-val-msg.warn { color: var(--po-warn); }

/* ─── Meta Grid ──────────────────────────────────────────── */
.po-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; }
.po-field { display: flex; flex-direction: column; }
.po-field-full { grid-column: span 2; }
.po-date-wrap { position: relative; display: flex; align-items: center; }
.po-date-icon { position: absolute; left: 10px; color: var(--po-muted); pointer-events: none; }
.po-date-input { padding-left: 32px; }

/* ─── Tables ─────────────────────────────────────────────── */
.po-invoices-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.po-inv-search-wrap { display: flex; align-items: center; gap: 7px; background: var(--po-white); border: 1px solid var(--po-border); border-radius: 6px; padding: 5px 10px; color: var(--po-muted); }
.po-inv-search { border: none; outline: none; font-size: 12.5px; color: var(--po-text); background: transparent; width: 160px; }
.po-table-body { padding: 0; overflow-x: auto; }
.po-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.po-table th { background: #f8fafc; padding: 11px 14px; text-align: left; font-size: 12px; font-weight: 600; color: var(--po-muted); border-bottom: 1px solid var(--po-border); white-space: nowrap; }
.po-table td { padding: 11px 14px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.po-table tr:last-child td { border-bottom: none; }
.po-table tbody tr:hover { background: #fafbfc; }
.po-table tbody tr.po-row-selected { background: #f5f3ff; }
.po-cb { width: 15px; height: 15px; cursor: pointer; accent-color: var(--po-primary); }
.po-status-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: capitalize; }
.po-status-badge.unpaid  { background: #fee2e2; color: #991b1b; }
.po-status-badge.partial { background: #fef3c7; color: #92400e; }
.po-status-badge.paid    { background: #d1fae5; color: #065f46; }
.po-status-badge.active  { background: #f5f3ff; color: #6d28d9; }
.po-empty { text-align: center; padding: 30px; color: var(--po-muted); font-size: 13px; }
.po-tfoot-row td { border-top: 2px solid var(--po-border); padding: 10px 14px; font-weight: 600; font-size: 13px; background: #f8fafc; }

/* ─── Actions & Summary ──────────────────────────────────── */
.po-actions { margin-top: 16px; }
.po-summary-strip { display: flex; align-items: center; gap: 12px; background: var(--po-white); border: 1px solid var(--po-border); border-radius: var(--po-radius); padding: 14px 20px; margin-bottom: 14px; flex-wrap: wrap; box-shadow: var(--po-shadow); }
.po-summary-item { text-align: center; }
.po-summary-item span  { display: block; font-size: 11.5px; color: var(--po-muted); margin-bottom: 3px; }
.po-summary-item strong { font-size: 15px; font-weight: 700; color: var(--po-text); }
.po-summary-total strong { color: var(--po-danger); }
.po-summary-paying strong { color: var(--po-success); }
.po-summary-sep { font-size: 18px; color: var(--po-muted); font-weight: 300; }
.po-summary-spacer { flex: 1; }
.po-submit-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 13px; background: var(--po-success); color: white; border: none; border-radius: var(--po-radius); font-size: 15px; font-weight: 600; cursor: pointer; transition: background .15s, transform .1s, box-shadow .15s; }
.po-submit-btn:hover:not(:disabled) { background: #0ea15f; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(18,183,106,.25); }
.po-submit-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }
.po-submit-btn.refund-mode { background: #7c3aed; }
.po-submit-btn.refund-mode:hover:not(:disabled) { background: #6d28d9; box-shadow: 0 4px 14px rgba(124,58,237,.25); }
@keyframes po-spin { to { transform: rotate(360deg); } }
.po-spin { animation: po-spin .8s linear infinite; }
</style>
@endpush

@push('scripts')
<script>
/* ═══════════════════════════════════════════════════════════
   STATE
═══════════════════════════════════════════════════════════ */
let currentMode  = 'purchase';   // 'purchase' | 'credit_refund'
let party        = null;
let invoices     = [];
let allocationMap = {};

/* ═══════════════════════════════════════════════════════════
   MODE SWITCH
═══════════════════════════════════════════════════════════ */
function switchMode(mode) {
    currentMode = mode;
    $('#paymentSubtype').val(mode === 'credit_refund' ? 'credit_refund' : 'purchase_payment');

    // Tab active states
    $('#tabPurchase').removeClass('active');
    $('#tabCreditRefund').removeClass('active');
    if (mode === 'purchase') {
        $('#tabPurchase').addClass('active');
        $('#btnText').text('Save Payment');
        $('#submitBtn').removeClass('refund-mode');
        $('#amountLabel').html('Amount to Pay <span class="po-req">*</span>');
        $('#partyCardHeader').text('Party Name (Vendor / Dealer / Distributor)');
    } else {
        $('#tabCreditRefund').addClass('active');
        $('#btnText').text('Save Refund');
        $('#submitBtn').addClass('refund-mode');
        $('#amountLabel').html('Refund Amount <span class="po-req">*</span>');
        $('#partyCardHeader').text('Party Name (Customer / Dealer / Distributor)');
    }

    // Reset everything on mode change
    resetParty();
}

/* ═══════════════════════════════════════════════════════════
   PARTY SEARCH
═══════════════════════════════════════════════════════════ */
let _searchTimer;

$('#partySearch').on('input', function () {
    const q = $(this).val().trim();
    clearTimeout(_searchTimer);
    $('#clearPartyBtn').toggle(q.length > 0);
    if (q.length < 2) { closeDropdown(); return; }

    showDropdownLoading();
    _searchTimer = setTimeout(() => {
        $.get('{{ route("admin.payments-out.search-parties") }}', { search: q, mode: currentMode })
            .done(res => renderDropdown(res.parties || []))
            .fail(() => closeDropdown());
    }, 280);
});

$('#clearPartyBtn').on('click', () => {
    $('#partySearch').val('');
    $('#clearPartyBtn').hide();
    closeDropdown();
});

function showDropdownLoading() {
    $('#partyDropdown').html('<div class="po-drop-loading">Searching…</div>').show();
}

function renderDropdown(parties) {
    const $d = $('#partyDropdown').empty();
    if (!parties.length) {
        const emptyMsg = currentMode === 'credit_refund'
            ? 'No parties found with active credit notes'
            : 'No parties found with outstanding purchase invoices';
        $d.html(`<div class="po-drop-empty">${emptyMsg}</div>`).show();
        return;
    }

    parties.forEach(p => {
        if (currentMode === 'credit_refund') {
            $d.append(`
                <div class="po-drop-item" onclick="selectParty('${p.id}', '${p.party_type}')">
                    <div class="po-drop-name">${esc(p.name)}
                        <span class="po-party-badge ${p.party_type}">${esc(p.party_type_text)}</span>
                    </div>
                    <div class="po-drop-meta">
                        <span>📞 ${esc(p.phone)}</span>
                    </div>
                    <div class="po-drop-due">
                        <span class="due-total">Credit Balance: ₹${fmt(p.credit_balance)}</span>
                    </div>
                </div>
            `);
        } else {
            $d.append(`
                <div class="po-drop-item" onclick="selectParty('${p.id}', '${p.party_type}')">
                    <div class="po-drop-name">${esc(p.name)}
                        <span class="po-party-badge ${p.party_type}">${esc(p.party_type_text)}</span>
                    </div>
                    <div class="po-drop-meta">
                        <span>📞 ${esc(p.phone)}</span>
                        ${p.email && p.email !== '-' ? `<span>✉️ ${esc(p.email)}</span>` : ''}
                    </div>
                    <div class="po-drop-due">
                        <span>Opening: ₹${fmt(p.opening_balance)}</span>
                        <span>Invoice Due: ₹${fmt(p.invoice_due)}</span>
                        <span class="due-total">Total Due: ₹${fmt(p.total_due)}</span>
                    </div>
                </div>
            `);
        }
    });
    $d.show();
}

function closeDropdown() { $('#partyDropdown').hide(); }

function selectParty(partyId, partyType) {
    closeDropdown();
    $('#partySearch').val('').hide();
    $('#partySearchWrap .po-search-field').hide();
    $('#clearPartyBtn').hide();

    if (currentMode === 'credit_refund') {
        selectCreditRefundParty(partyId, partyType);
    } else {
        selectPurchaseParty(partyId, partyType);
    }
}

/* ─── Purchase party selection (vendors/dealers/distributors) ─────────────── */
function selectPurchaseParty(partyId, partyType) {
    $.get('{{ route("admin.payments-out.party.details", ":id") }}'.replace(':id', partyId), { type: partyType })
        .done(res => {
            if (!res.success) { showAlert('Failed to load party', 'error'); return; }
            party = res.party;
            invoices = (party.invoices || []).map(inv => ({ ...inv }));
            allocationMap = {};

            showSelectedPartyHeader(party.name, party.party_type_text, party.party_type, party.phone);
            $('#partyId').val(party.id);
            $('#partyType').val(party.party_type);

            // Purchase-mode UI
            $('#purchaseBalanceSection').show();
            $('#creditRefundSection').hide();
            $('#currentBalanceDisplay').text('₹' + fmt(party.total_due));

            const debitBalance = party.debit_balance || 0;
            const debitNotes   = party.debit_notes || [];
            if (debitBalance > 0 && debitNotes.length > 0) {
                let listHtml = '';
                debitNotes.forEach(dn => {
                    listHtml += `<div style="display:flex;justify-content:space-between;margin-bottom:2px;">
                        <span>${esc(dn.debit_note_number)} &nbsp;<span style="opacity:.7">${esc(dn.debit_date)}</span></span>
                        <span style="font-weight:600;">₹${fmt(dn.remaining_amount)}</span>
                    </div>`;
                });
                $('#debitNotesList').html(listHtml);
                $('#debitTotalDisplay').text('-₹' + fmt(debitBalance));
                $('#netPayableDisplay').text('₹' + fmt(party.net_payable));
                $('#debitNoteBlock').show();
            } else {
                $('#debitNoteBlock').hide();
            }

            $('#partySelected').show();
            $('#amountSection').show();
            $('#invoicesSection').show();
            $('#debitNotesAdjustSection').show();
            $('#creditNotesSection').hide();
            $('#submitSection').show();

            if (party.net_payable <= 0) {
                $('#amountSection').hide();
                $('#submitSection').hide();
                showAlert('Party has debit balance. No payment needed.', 'warning');
            }

            const prefill = party.net_payable > 0 ? party.net_payable : 0;
            $('#amountToPay').val(fmt(prefill));

            renderInvoices();
            renderDebitNotesAdjustTable(debitNotes);
            updateSummary();
            if (prefill > 0) autoAllocate(prefill);
            $('#amountToPay').focus();
        })
        .fail(() => showAlert('Failed to load party details', 'error'));
}

/* ─── Credit Refund party selection (customers with credit notes) ─────────── */
function selectCreditRefundParty(partyId, partyType) {
    $.get('{{ route("admin.payments-out.refund-party.details", ":id") }}'.replace(':id', partyId))
        .done(res => {
            if (!res.success) { showAlert('Failed to load party', 'error'); return; }
            party = res.party;

            showSelectedPartyHeader(party.name, party.party_type_text, party.party_type, party.phone);
            $('#partyId').val(party.id);
            $('#partyType').val(party.party_type);

            // Refund-mode UI
            $('#purchaseBalanceSection').hide();
            $('#creditRefundSection').show();
            $('#debitNotesAdjustSection').hide();

            const creditNotes = party.credit_notes || [];
            $('#creditRefundTotalDisplay').text('₹' + fmt(party.credit_balance));

            let listHtml = '';
            creditNotes.forEach(cn => {
                listHtml += `<div class="po-credit-list-item">
                    <span>${esc(cn.credit_note_number)} &nbsp;<span style="opacity:.6">${esc(cn.credit_date)}</span></span>
                    <span style="font-weight:600;">₹${fmt(cn.remaining_amount)}</span>
                </div>`;
            });
            $('#creditNotesRefundList').html(listHtml || '<div style="color:var(--po-muted);text-align:center;padding:6px 0;">No active credit notes</div>');

            $('#partySelected').show();
            $('#amountSection').show();
            $('#invoicesSection').hide();
            $('#creditNotesSection').show();
            $('#submitSection').show();

            // Pre-fill with full credit balance
            $('#amountToPay').val(fmt(party.credit_balance));

            renderCreditNotesTable(creditNotes, party.credit_balance);
            updateCreditRefundSummary();
            $('#amountToPay').focus();
        })
        .fail(() => showAlert('Failed to load party details', 'error'));
}

function showSelectedPartyHeader(name, typeText, typeClass, phone) {
    $('#selPartyName').text(name);
    $('#selPartyBadge').text(typeText).attr('class', 'po-party-badge ' + typeClass);
    $('#selPartyPhone').text('📞 ' + phone);
}

function resetParty() {
    party = null; invoices = []; allocationMap = {};
    $('#partySelected').hide();
    $('#amountSection').hide();
    $('#invoicesSection').hide();
    $('#debitNotesAdjustSection').hide();
    $('#creditNotesSection').hide();
    $('#submitSection').hide();
    $('#amountToPay').val('');
    $('#partyId').val('');
    $('#partySearch').val('').show();
    $('#partySearchWrap .po-search-field').show();
    $('#submitBtn').prop('disabled', true);
    $('#invoicesTfoot').hide();
    renderInvoices(true);
    updateSummary();
}

/* ═══════════════════════════════════════════════════════════
   AMOUNT INPUT — routes to correct handler
═══════════════════════════════════════════════════════════ */
$('#amountToPay').on('input', function () {
    if (currentMode === 'credit_refund') {
        onCreditRefundAmountInput($(this).val());
    } else {
        onPurchaseAmountInput($(this).val());
    }
});

function onPurchaseAmountInput(val) {
    const amount     = parseFloat(val) || 0;
    const netPayable = party ? (party.net_payable || 0) : 0;
    const $msg = $('#amountValidation');

    if (amount <= 0) {
        $msg.hide();
    } else if (amount > netPayable + 0.005) {
        $msg.text('Amount exceeds net payable of ₹' + fmt(netPayable)).attr('class','po-val-msg err').show();
        $('#amountToPay').val(fmt(netPayable));
        return;
    } else {
        $msg.hide();
    }

    if (amount > 0) autoAllocate(amount);
    else clearAllocations();
    updateSummary();
}

function onCreditRefundAmountInput(val) {
    const amount        = parseFloat(val) || 0;
    const creditBalance = party ? (party.credit_balance || 0) : 0;
    const $msg = $('#amountValidation');

    if (amount > creditBalance + 0.005) {
        $msg.text('Amount exceeds credit note balance of ₹' + fmt(creditBalance)).attr('class','po-val-msg err').show();
        $('#amountToPay').val(fmt(creditBalance));
        return;
    } else {
        $msg.hide();
    }

    renderCreditNotesTable(party.credit_notes || [], amount);
    updateCreditRefundSummary();
}

/* ═══════════════════════════════════════════════════════════
   DEBIT NOTES ADJUSTMENT TABLE (purchase mode)
═══════════════════════════════════════════════════════════ */
function renderDebitNotesAdjustTable(debitNotes) {
    const $tb = $('#debitNotesAdjustTbody').empty();
    if (!debitNotes || !debitNotes.length) {
        $tb.html('苦<td colspan="6" class="po-empty">No active debit notes available for adjustment</td> </tr>');
        return;
    }

    // Show all debit notes that will be automatically adjusted
    debitNotes.forEach(dn => {
        $tb.append(`
            <tr>
                <td><strong>${esc(dn.debit_note_number)}</strong></td>
                <td>${esc(dn.debit_date)}</td>
                <td>₹${fmt(dn.amount)}</td>
                <td>₹${fmt(dn.used_amount)}</td>
                <td><span class="po-status-badge active">₹${fmt(dn.remaining_amount)}</span></td>
                <td style="font-weight:600; color:var(--po-success);">Will be auto-adjusted</td>
            </tr>
        `);
    });
}

/* ═══════════════════════════════════════════════════════════
   CREDIT NOTES TABLE (credit refund mode)
═══════════════════════════════════════════════════════════ */
function renderCreditNotesTable(creditNotes, cashAmount) {
    const $tb = $('#creditNotesTbody').empty();
    if (!creditNotes.length) {
        $tb.html('苦<td colspan="6" class="po-empty">No active credit notes</td> </tr>');
        return;
    }

    let remaining = parseFloat(cashAmount) || 0;
    creditNotes.forEach(cn => {
        const toUse   = Math.min(cn.remaining_amount, remaining);
        remaining     = Math.max(0, remaining - toUse);
        const rowCls  = toUse > 0 ? 'po-row-selected' : '';

        $tb.append(`
            <tr class="${rowCls}">
                <td><strong>${esc(cn.credit_note_number)}</strong></td>
                <td>${esc(cn.credit_date)}</td>
                <td>₹${fmt(cn.amount)}</td>
                <td>₹${fmt(cn.used_amount)}</td>
                <td><span class="po-status-badge active">₹${fmt(cn.remaining_amount)}</span></td>
                <td style="font-weight:600; color:${toUse > 0 ? 'var(--po-success)' : 'var(--po-muted)'};">
                    ${toUse > 0 ? '₹' + fmt(toUse) : '—'}
                </td>
            </tr>
        `);
    });
}

function updateCreditRefundSummary() {
    if (!party) return;
    const amount        = parseFloat($('#amountToPay').val()) || 0;
    const creditBalance = party.credit_balance || 0;

    $('#sumCreditBalance').text('₹' + fmt(creditBalance));
    $('#sumMaxRefund').text('₹' + fmt(creditBalance));
    $('#sumRefundPaying').text('₹' + fmt(amount));

    $('#purchaseSummaryStrip').hide();
    $('#creditRefundSummaryStrip').show();

    const valid = amount > 0 && amount <= (creditBalance + 0.005);
    $('#submitBtn').prop('disabled', !valid);
}

/* ═══════════════════════════════════════════════════════════
   INVOICES (purchase mode)
═══════════════════════════════════════════════════════════ */
function renderInvoices(empty = false) {
    const $tb = $('#invoicesTbody').empty();
    $('#invoicesTfoot').hide();

    if (empty || !invoices.length) {
        $tb.append(`<tr id="noInvoicesRow">
            <td colspan="7" class="po-empty">
                ${empty ? 'Select a party to view invoices' : 'No unpaid or partial purchase invoices found'}
            </td>
        </tr>`);
        return;
    }

    invoices.forEach((inv, i) => {
        const pendingBalance = parseFloat(inv.balance);
        const alloc   = allocationMap[inv.id] || 0;
        const checked = alloc > 0 ? 'checked' : '';
        const rowCls  = alloc > 0 ? 'po-row-selected' : '';
        const isPartial = inv.payment_status === 'partial';
        const pendingLabel = isPartial
            ? `<small style="color:var(--po-warn);display:block;font-size:11px;">₹${fmt(pendingBalance)} pending</small>`
            : '';
        const statusBadge = isPartial
            ? `<span class="po-status-badge partial">Partial</span>`
            : '';

        $tb.append(`
            <tr data-idx="${i}" class="${rowCls}" id="inv-row-${i}">
                <td><input type="checkbox" class="po-cb inv-cb" data-idx="${i}"
                    ${checked} onchange="onCbChange(${i}, this.checked)"></td>
                <td>${esc(inv.invoice_date)}</td>
                <td>${esc(inv.due_date)}</td>
                <td>${esc(inv.invoice_number)} ${statusBadge}</td>
                <td>${esc(inv.warehouse_name || '—')}</td>
                <td>₹${fmt(inv.grand_total)}${pendingLabel}</td>
                <td id="pay-${i}" style="font-weight:600; color: var(--po-text);">
                    ${alloc > 0 ? '₹' + fmt(alloc) : '—'}
                </td>
            </tr>
        `);
    });

    updateTableFooter();
    $('#invoicesTfoot').show();
}

function limitDecimals(e, input) {
    const val    = input.value;
    const dotPos = val.indexOf('.');
    if (dotPos !== -1 && val.length - dotPos > 2) {
        const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab'];
        if (!allowed.includes(e.key) && !isNaN(e.key)) e.preventDefault();
    }
}

function onCbChange(idx, checked) {
    const inv            = invoices[idx];
    const pendingBalance = parseFloat(inv.balance);
    if (checked) {
        const remaining = getRemainingBudget();
        const toAlloc   = Math.min(pendingBalance, remaining);
        allocationMap[inv.id] = toAlloc;
        $(`#pay-${idx}`).text(toAlloc > 0 ? '₹' + fmt(toAlloc) : '—');
        $(`#inv-row-${idx}`).addClass('po-row-selected');
    } else {
        delete allocationMap[inv.id];
        $(`#pay-${idx}`).text('—');
        $(`#inv-row-${idx}`).removeClass('po-row-selected');
    }
    updateSelectAll(); updateSummary(); updateTableFooter();
}

$('#selectAllCb').on('change', function () {
    const checked   = $(this).is(':checked');
    let   remaining = getRemainingBudget();
    invoices.forEach((inv, i) => {
        if (checked && remaining > 0) {
            const toAlloc = Math.min(inv.balance, remaining);
            allocationMap[inv.id] = toAlloc;
            $(`#pay-${i}`).text(toAlloc > 0 ? '₹' + fmt(toAlloc) : '—');
            $(`#inv-row-${i}`).addClass('po-row-selected');
            $(`.inv-cb[data-idx="${i}"]`).prop('checked', true);
            remaining -= toAlloc;
        } else {
            delete allocationMap[inv.id];
            $(`#pay-${i}`).text('—');
            $(`#inv-row-${i}`).removeClass('po-row-selected');
            $(`.inv-cb[data-idx="${i}"]`).prop('checked', false);
        }
    });
    updateSummary(); updateTableFooter();
});

function updateSelectAll() {
    const total    = invoices.length;
    const selected = Object.keys(allocationMap).filter(id => invoices.find(inv => inv.id === id)).length;
    const $cb = $('#selectAllCb');
    if (selected === 0)          { $cb.prop('checked', false).prop('indeterminate', false); }
    else if (selected === total) { $cb.prop('checked', true).prop('indeterminate', false); }
    else                         { $cb.prop('checked', false).prop('indeterminate', true); }
}

function updateTableFooter() {
    const totalPending = invoices.reduce((s, inv) => s + parseFloat(inv.balance), 0);
    const totalRecv    = Object.values(allocationMap).reduce((s, v) => s + v, 0);
    $('#tfootInvoiceTotal').text('₹' + fmt(totalPending));
    $('#tfootAmountToPay').text('₹' + fmt(totalRecv));
}

function autoAllocate(amount) {
    allocationMap = {};
    const debitBalance = parseFloat(party.debit_balance) || 0;
    let remaining = amount + debitBalance;
    const openingAlloc = Math.min(parseFloat(party.opening_balance) || 0, remaining);
    remaining -= openingAlloc;

    invoices.forEach((inv, i) => {
        const pendingBalance = parseFloat(inv.balance);
        if (remaining <= 0.001 || pendingBalance <= 0) {
            $(`#pay-${i}`).text('—');
            $(`.inv-cb[data-idx="${i}"]`).prop('checked', false);
            $(`#inv-row-${i}`).removeClass('po-row-selected');
            return;
        }
        const toAlloc = Math.min(pendingBalance, remaining);
        allocationMap[inv.id] = toAlloc;
        remaining -= toAlloc;
        $(`#pay-${i}`).text('₹' + fmt(toAlloc));
        $(`.inv-cb[data-idx="${i}"]`).prop('checked', true);
        $(`#inv-row-${i}`).addClass('po-row-selected');
    });

    invoices.forEach((inv, i) => {
        if (!allocationMap[inv.id]) {
            $(`.inv-cb[data-idx="${i}"]`).prop('checked', false);
            $(`#inv-row-${i}`).removeClass('po-row-selected');
            $(`#pay-${i}`).text('—');
        }
    });

    updateSelectAll(); updateTableFooter();
}

function clearAllocations() {
    allocationMap = {};
    invoices.forEach((inv, i) => {
        $(`.inv-cb[data-idx="${i}"]`).prop('checked', false);
        $(`#inv-row-${i}`).removeClass('po-row-selected');
        $(`#pay-${i}`).text('—');
    });
    updateSelectAll(); updateTableFooter();
}

function getRemainingBudget() {
    const amount    = parseFloat($('#amountToPay').val()) || 0;
    const allocated = Object.values(allocationMap).reduce((s, v) => s + v, 0);
    return amount - allocated;
}

function updateSummary() {
    if (!party || currentMode === 'credit_refund') {
        if (!party) {
            $('#sumOpening, #sumInvoice, #sumTotal, #sumPaying').text('₹0.00');
            $('#sumDebit').text('₹0.00');
            $('#submitBtn').prop('disabled', true);
        }
        return;
    }
    const amount     = parseFloat($('#amountToPay').val()) || 0;
    const opening    = party.opening_balance || 0;
    const invDue     = party.invoice_due || 0;
    const netPayable = party.net_payable || 0;

    $('#sumOpening').text('₹' + fmt(opening));
    $('#sumInvoice').text('₹' + fmt(invDue));
    $('#sumDebit').text('₹' + fmt(party.debit_balance || 0));
    $('#sumTotal').text('₹' + fmt(netPayable));
    $('#sumPaying').text('₹' + fmt(amount));

    $('#purchaseSummaryStrip').show();
    $('#creditRefundSummaryStrip').hide();

    const valid = amount >= 0 && amount <= (netPayable + 0.005);
    $('#submitBtn').prop('disabled', !valid);
}

/* ─── Invoice search filter ──────────────────────────────── */
$('#invoiceSearch').on('input', function () {
    const q = $(this).val().toLowerCase();
    $('#invoicesTbody tr').each(function () {
        $(this).toggle(!q || $(this).text().toLowerCase().includes(q));
    });
});

/* ═══════════════════════════════════════════════════════════
   FORM SUBMIT
═══════════════════════════════════════════════════════════ */
$('#paymentForm').on('submit', function (e) {
    e.preventDefault();

    const amount        = parseFloat($('#amountToPay').val()) || 0;
    const method        = $('#paymentMethod').val();
    const date          = $('#paymentDate').val();
    const partyId       = $('#partyId').val();
    const partyType     = $('#partyType').val();

    if (!partyId)  { showAlert('Please select a party', 'error'); return; }
    if (amount <= 0) { showAlert('Please enter a valid amount', 'error'); return; }
    if (!method)   { showAlert('Please select a payment mode', 'error'); return; }
    if (!date)     { showAlert('Please select a payment date', 'error'); return; }

    const formData = {
        _token:           '{{ csrf_token() }}',
        party_id:         partyId,
        party_type:       partyType,
        amount:           amount,
        payment_date:     date,
        payment_method:   method,
        payment_subtype:  currentMode === 'credit_refund' ? 'credit_refund' : 'purchase_payment',
        reference_no:     $('input[name="reference_no"]').val(),
        notes:            $('textarea[name="notes"]').val()
    };

    $('#submitBtn').prop('disabled', true);
    $('#btnText').hide();
    $('#btnLoader').show();

    $.ajax({
        url:  '{{ route("admin.payments-out.store") }}',
        type: 'POST',
        data: formData,
        success: function (res) {
            if (res.success) {
                const label = currentMode === 'credit_refund' ? 'Refund saved!' : 'Payment saved!';
                showAlert(label + ' #: ' + res.payment_number, 'success');
                setTimeout(() => { window.location.href = '{{ route("admin.payments-out.index") }}'; }, 1600);
            } else {
                showAlert(res.message || 'Failed to save', 'error');
                resetSubmitBtn();
            }
        },
        error: function (xhr) {
            showAlert(xhr.responseJSON?.message || 'Failed to save', 'error');
            resetSubmitBtn();
        }
    });
});

function resetSubmitBtn() {
    $('#submitBtn').prop('disabled', false);
    $('#btnText').show();
    $('#btnLoader').hide();
}

/* ═══════════════════════════════════════════════════════════
   HELPERS
═══════════════════════════════════════════════════════════ */
function fmt(n) { return (parseFloat(n) || 0).toFixed(2); }
function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function showAlert(msg, type = 'success') {
    const $a = $('#poAlert');
    $a.attr('class', 'po-alert ' + type).text(msg).show();
    clearTimeout(showAlert._t);
    showAlert._t = setTimeout(() => $a.fadeOut(), 5000);
}
$(document).on('click', function (e) {
    if (!$(e.target).closest('#partySearchWrap').length) closeDropdown();
});
$(document).on('keydown', function (e) {
    if (e.key === 'Escape') closeDropdown();
});
</script>
@endpush
@endsection
