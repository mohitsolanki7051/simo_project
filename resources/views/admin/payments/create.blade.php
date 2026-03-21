@extends('layouts.admin')

@section('title', 'New Payment In')
@section('header-title', 'Create Payment In')

@section('content')
<div class="pi-wrapper">

    {{-- ── Top Bar ──────────────────────────────────────────── --}}
    <div class="pi-topbar">
        <div class="pi-topbar-left">
            <h1 class="pi-title">Payment In</h1>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="pi-back-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to List
        </a>
    </div>

    {{-- ── Alert ────────────────────────────────────────────── --}}
    <div id="piAlert" class="pi-alert" style="display:none;"></div>

    <form id="paymentForm" autocomplete="off">
        @csrf

        {{-- ══ ROW 1 : Party  +  Payment Meta ═══════════════════════ --}}
        <div class="pi-row-2col">

            {{-- Left: Party Card --}}
            <div class="pi-card">
                <div class="pi-card-header">Party Name</div>
                <div class="pi-card-body">
                    <div class="pi-party-search-wrap" id="partySearchWrap">
                        <div class="pi-search-field">
                            <svg class="pi-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            <input type="text" id="partySearch" class="pi-input pi-search-input"
                                placeholder="Type party name, phone…" />
                            <button type="button" id="clearPartyBtn" class="pi-clear-btn" style="display:none;" title="Clear">✕</button>
                        </div>
                        <div id="partyDropdown" class="pi-party-dropdown" style="display:none;"></div>
                    </div>

                    {{-- Selected party display --}}
                    <div id="partySelected" style="display:none;">
                        <div class="pi-selected-party">
                            <div class="pi-party-info">
                                <span id="selPartyName" class="pi-party-name-big"></span>
                                <span id="selPartyBadge" class="pi-party-badge"></span>
                                <span id="selPartyPhone" class="pi-party-phone"></span>
                            </div>
                            <button type="button" class="pi-change-btn" onclick="resetParty()">Change</button>
                        </div>
                        <input type="hidden" name="party_id" id="partyId">

                        <div class="pi-balance-row" id="balanceRow">
                            <span class="pi-balance-label">Total Due:</span>
                            <span class="pi-balance-val" id="currentBalanceDisplay">₹0.00</span>
                        </div>

                        <!-- NEW: Credit note adjustment block -->
                        <div id="creditNoteBlock" style="display:none; margin-top:10px; padding:10px 12px;
                            background:#fefce8; border:1px solid #fde68a; border-radius:7px; font-size:13px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <span style="font-weight:600; color:#92400e;">Credit Notes Available</span>
                                <span id="creditTotalDisplay" style="font-weight:700; color:#065f46;">-₹0.00</span>
                            </div>
                            <div id="creditNotesList" style="font-size:12px; color:#92400e; margin-bottom:8px;"></div>
                            <div style="display:flex; justify-content:space-between; padding-top:8px;
                                border-top:1px solid #fde68a; font-weight:600;">
                                <span style="color:#374151;">Net Payable</span>
                                <span id="netPayableDisplay" style="color:#991b1b; font-size:14px;">₹0.00</span>
                            </div>
                        </div>
                    </div>

                    {{-- Amount Received --}}
                    <div id="amountSection" style="display:none; margin-top:14px;">
                        <label class="pi-label">Amount Received <span class="pi-req">*</span></label>
                        <div class="pi-amount-wrap">
                            <span class="pi-currency">₹</span>
                            <input type="number" step="0.01" min="0.01" id="amountReceived"
                                   name="amount" class="pi-input pi-amount-input" placeholder="0.00" onkeydown="limitDecimals(event, this)">
                        </div>
                        <div id="amountValidation" class="pi-val-msg" style="display:none;"></div>
                    </div>
                </div>
            </div>

            {{-- Right: Payment Meta Card --}}
            <div class="pi-card">
                <div class="pi-card-header">Payment Details</div>
                <div class="pi-card-body">
                    <div class="pi-meta-grid">
                        <div class="pi-field">
                            <label class="pi-label">Payment Date <span class="pi-req">*</span></label>
                            <div class="pi-date-wrap">
                                <svg class="pi-date-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <input type="date" name="payment_date" id="paymentDate"
                                       class="pi-input pi-date-input" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="pi-field">
                            <label class="pi-label">Payment Mode <span class="pi-req">*</span></label>
                            <select name="payment_method" id="paymentMethod" class="pi-input pi-select" required>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="card">Card</option>
                            </select>
                        </div>

                        <div class="pi-field">
                            <label class="pi-label">Payment In Prefix</label>
                            <input type="text" class="pi-input pi-prefix-display" value="SIM/PI/" readonly>
                        </div>

                        <div class="pi-field">
                            <label class="pi-label">Payment In Number</label>
                            <input type="text" class="pi-input pi-prefix-display" id="paymentNumberDisplay"
                                   value="{{ $paymentNumber }}" readonly>
                        </div>

                        <div class="pi-field pi-field-full">
                            <label class="pi-label">Reference No.</label>
                            <input type="text" name="reference_no" class="pi-input"
                                   placeholder="Transaction ID / UPI Ref / Cheque No">
                        </div>

                        <div class="pi-field pi-field-full">
                            <label class="pi-label">Notes</label>
                            <textarea name="notes" class="pi-input pi-textarea"
                                      placeholder="Enter Notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ ROW 2 : Invoices Table ════════════════════════════════ --}}
        <div class="pi-card" id="invoicesSection" style="display:none; margin-top:16px;">
            <div class="pi-card-header pi-invoices-header">
                <span>Settle invoices with this payment</span>
                <div class="pi-inv-search-wrap">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" id="invoiceSearch" class="pi-inv-search" placeholder="Search Invoice Number">
                </div>
            </div>
            <div class="pi-card-body pi-table-body">
                <table class="pi-table" id="invoicesTable">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAllCb" class="pi-cb">
                            </th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th>Invoice No.</th>
                            <th>Warehouse</th>
                            <th>Invoice Amount</th>
                            <th>Amount Received</th>
                        </tr>
                    </thead>
                    <tbody id="invoicesTbody">
                        <tr id="noInvoicesRow">
                            <td colspan="6" class="pi-empty">Select a party to view invoices</td>
                        </tr>
                    </tbody>
                    <tfoot id="invoicesTfoot" style="display:none;">
                        <tr class="pi-tfoot-row">
                            <td colspan="5"><strong>Total</strong></td>
                            <td id="tfootInvoiceTotal">₹0.00</td>
                            <td id="tfootAmountReceived">₹0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ══ ROW 3 : Submit ════════════════════════════════════════ --}}
        <div class="pi-actions" id="submitSection" style="display:none;">
            <div class="pi-summary-strip">
                <div class="pi-summary-item">
                    <span>Opening Balance</span>
                    <strong id="sumOpening">₹0.00</strong>
                </div>
                <div class="pi-summary-sep">+</div>
                <div class="pi-summary-item">
                    <span>Invoice Dues</span>
                    <strong id="sumInvoice">₹0.00</strong>
                </div>
                <!-- NEW -->
                <div class="pi-summary-sep">−</div>
                <div class="pi-summary-item">
                    <span>Credit Notes</span>
                    <strong id="sumCredit" style="color:var(--pi-success);">₹0.00</strong>
                </div>
                <!-- END NEW -->
                <div class="pi-summary-sep">=</div>
                <div class="pi-summary-item pi-summary-total">
                    <span>Net Payable</span>
                    <strong id="sumTotal">₹0.00</strong>
                </div>
                <div class="pi-summary-spacer"></div>
                <div class="pi-summary-item pi-summary-paying">
                    <span>You are paying</span>
                    <strong id="sumPaying">₹0.00</strong>
                </div>
            </div>

            <button type="submit" class="pi-submit-btn" id="submitBtn" disabled>
                <span id="btnText">Save Payment</span>
                <span id="btnLoader" style="display:none;">
                    <svg class="pi-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    Processing…
                </span>
            </button>
        </div>

    </form>
</div>
@endsection

@push('styles')
<style>
/* ─── CSS Variables ─────────────────────────────────────── */
:root {
    --pi-bg: #f4f6f9;
    --pi-white: #ffffff;
    --pi-border: #dde1e7;
    --pi-border-focus: #4f7cff;
    --pi-text: #1a2033;
    --pi-muted: #6b7280;
    --pi-label: #374151;
    --pi-primary: #4f7cff;
    --pi-primary-hover: #3a66e8;
    --pi-success: #12b76a;
    --pi-danger: #f04438;
    --pi-warn: #f79009;
    --pi-badge-c-bg: #eff6ff; --pi-badge-c-txt: #1d4ed8;
    --pi-badge-d-bg: #fefce8; --pi-badge-d-txt: #854d0e;
    --pi-badge-dist-bg: #f0fdf4; --pi-badge-dist-txt: #166534;
    --pi-radius: 8px;
    --pi-shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
}

/* ─── Wrapper ────────────────────────────────────────────── */
.pi-wrapper {
    max-width: 1100px;
    margin: 0 auto;
    font-family: 'Segoe UI', system-ui, sans-serif;
    color: var(--pi-text);
}

/* ─── Top Bar ────────────────────────────────────────────── */
.pi-topbar {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 20px;
}
.pi-title  { font-size: 22px; font-weight: 700; margin: 0 0 2px; }
.pi-subtitle { font-size: 13px; color: var(--pi-muted); }
.pi-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: 1px solid var(--pi-border);
    border-radius: var(--pi-radius);
    background: #fa8526;
    color: #fff;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: background .15s;
}
.pi-back-btn:hover { background: #f0f1f3; color: var(--pi-text); }

/* ─── Alert ─────────────────────────────────────────────── */
.pi-alert {
    padding: 12px 16px;
    border-radius: var(--pi-radius);
    font-size: 13.5px;
    font-weight: 500;
    margin-bottom: 16px;
}
.pi-alert.success { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
.pi-alert.error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
.pi-alert.warning { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }

/* ─── Layout ─────────────────────────────────────────────── */
.pi-row-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
@media (max-width: 768px) {
    .pi-row-2col { grid-template-columns: 1fr; }
}

/* ─── Card ───────────────────────────────────────────────── */
.pi-card {
    background: var(--pi-white);
    border: 1px solid var(--pi-border);
    border-radius: var(--pi-radius);
    box-shadow: var(--pi-shadow);
}
.pi-card-header {
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 600;
    color: var(--pi-label);
    border-bottom: 1px solid var(--pi-border);
    background: #fafbfc;
    border-radius: var(--pi-radius) var(--pi-radius) 0 0;
}
.pi-card-body { padding: 16px; }

/* ─── Inputs ─────────────────────────────────────────────── */
.pi-label {
    display: block;
    font-size: 12.5px;
    font-weight: 500;
    color: var(--pi-label);
    margin-bottom: 5px;
}
.pi-req { color: var(--pi-danger); }
.pi-input {
    width: 100%;
    padding: 8px 11px;
    border: 1px solid var(--pi-border);
    border-radius: 6px;
    font-size: 13.5px;
    color: var(--pi-text);
    background: var(--pi-white);
    transition: border .15s, box-shadow .15s;
    box-sizing: border-box;
}
#creditNoteBlock { line-height:1.6; }
.pi-input:focus {
    outline: none;
    border-color: var(--pi-border-focus);
    box-shadow: 0 0 0 3px rgba(79,124,255,.12);
}
.pi-input:read-only { background: #f8f9fa; color: var(--pi-muted); cursor: default; }
.pi-select { cursor: pointer; }
.pi-textarea { resize: vertical; min-height: 72px; }
.pi-prefix-display { font-size: 12.5px; }

/* ─── Party Search ───────────────────────────────────────── */
.pi-party-search-wrap { position: relative; }
.pi-search-field {
    position: relative;
    display: flex;
    align-items: center;
}
.pi-search-icon {
    position: absolute;
    left: 10px;
    color: var(--pi-muted);
    pointer-events: none;
}
.pi-search-input { padding-left: 34px; padding-right: 32px; }
.pi-clear-btn {
    position: absolute;
    right: 8px;
    background: none;
    border: none;
    color: var(--pi-muted);
    font-size: 13px;
    cursor: pointer;
    padding: 2px 5px;
    line-height: 1;
}
.pi-clear-btn:hover { color: var(--pi-danger); }

/* ─── Party Dropdown ─────────────────────────────────────── */
.pi-party-dropdown {
    position: absolute;
    left: 0; right: 0;
    top: calc(100% + 4px);
    background: var(--pi-white);
    border: 1px solid var(--pi-border);
    border-radius: var(--pi-radius);
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
    z-index: 200;
    max-height: 320px;
    overflow-y: auto;
}
.pi-drop-item {
    padding: 11px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background .12s;
}
.pi-drop-item:last-child { border-bottom: none; }
.pi-drop-item:hover { background: #f0f7ff; }
.pi-drop-name { font-size: 14px; font-weight: 600; margin-bottom: 3px; }
.pi-drop-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: var(--pi-muted);
    margin-bottom: 5px;
}
.pi-drop-due {
    display: flex;
    gap: 14px;
    font-size: 12px;
    background: #f8fafc;
    border-radius: 4px;
    padding: 5px 8px;
}
.pi-drop-due span { color: var(--pi-muted); }
.pi-drop-due .due-total { font-weight: 600; color: var(--pi-success); }
.pi-drop-empty {
    padding: 20px;
    text-align: center;
    color: var(--pi-muted);
    font-size: 13px;
}
.pi-drop-loading {
    padding: 16px;
    text-align: center;
    color: var(--pi-muted);
    font-size: 13px;
}

/* ─── Party Badge ────────────────────────────────────────── */
.pi-party-badge {
    display: inline-block;
    padding: 2px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: capitalize;
    margin-left: 6px;
}
.pi-party-badge.customer    { background: var(--pi-badge-c-bg); color: var(--pi-badge-c-txt); }
.pi-party-badge.dealer      { background: var(--pi-badge-d-bg); color: var(--pi-badge-d-txt); }
.pi-party-badge.distributor { background: var(--pi-badge-dist-bg); color: var(--pi-badge-dist-txt); }

/* ─── Selected Party ─────────────────────────────────────── */
.pi-selected-party {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 10px 12px;
    background: #f0f7ff;
    border: 1px solid #bfdbfe;
    border-radius: 7px;
    margin-bottom: 10px;
}
.pi-party-name-big { font-size: 15px; font-weight: 700; }
.pi-party-phone    { display: block; font-size: 12px; color: var(--pi-muted); margin-top: 3px; }
.pi-change-btn {
    padding: 4px 12px;
    border: 1px solid var(--pi-primary);
    border-radius: 5px;
    background: var(--pi-white);
    color: var(--pi-primary);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s;
}
.pi-change-btn:hover { background: #eff6ff; }

.pi-balance-row {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--pi-muted);
    margin-bottom: 4px;
}
.pi-balance-val { font-weight: 600; color: var(--pi-danger); }

/* ─── Amount ─────────────────────────────────────────────── */
.pi-amount-wrap { position: relative; }
.pi-currency {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: var(--pi-muted);
    pointer-events: none;
}
.pi-amount-input { padding-left: 26px; font-size: 15px; font-weight: 600; }
.pi-val-msg { font-size: 11.5px; margin-top: 4px; }
.pi-val-msg.err  { color: var(--pi-danger); }
.pi-val-msg.warn { color: var(--pi-warn); }

/* ─── Meta Grid ──────────────────────────────────────────── */
.pi-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px 16px;
}
.pi-field { display: flex; flex-direction: column; }
.pi-field-full { grid-column: span 2; }
.pi-date-wrap { position: relative; display: flex; align-items: center; }
.pi-date-icon { position: absolute; left: 10px; color: var(--pi-muted); pointer-events: none; }
.pi-date-input { padding-left: 32px; }

/* ─── Invoices Table ─────────────────────────────────────── */
.pi-invoices-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.pi-inv-search-wrap {
    display: flex;
    align-items: center;
    gap: 7px;
    background: var(--pi-white);
    border: 1px solid var(--pi-border);
    border-radius: 6px;
    padding: 5px 10px;
    color: var(--pi-muted);
}
.pi-inv-search {
    border: none;
    outline: none;
    font-size: 12.5px;
    color: var(--pi-text);
    background: transparent;
    width: 160px;
}
.pi-table-body { padding: 0; overflow-x: auto; }
.pi-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.pi-table th {
    background: #f8fafc;
    padding: 11px 14px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: var(--pi-muted);
    border-bottom: 1px solid var(--pi-border);
    white-space: nowrap;
}
.pi-table td {
    padding: 11px 14px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}
.pi-table tr:last-child td { border-bottom: none; }
.pi-table tbody tr:hover { background: #fafbfc; }
.pi-table tbody tr.pi-row-selected { background: #f0f7ff; }

.pi-cb {
    width: 15px; height: 15px;
    cursor: pointer;
    accent-color: var(--pi-primary);
}
.pi-status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: capitalize;
}
.pi-status-badge.unpaid  { background: #fee2e2; color: #991b1b; }
.pi-status-badge.partial { background: #fef3c7; color: #92400e; }
.pi-status-badge.paid    { background: #d1fae5; color: #065f46; }

.pi-recv-input {
    width: 110px;
    padding: 6px 9px;
    border: 1px solid var(--pi-border);
    border-radius: 5px;
    font-size: 13px;
    text-align: right;
    color: var(--pi-text);
    background: var(--pi-white);
    transition: border .15s;
}
.pi-recv-input:focus {
    outline: none;
    border-color: var(--pi-border-focus);
    box-shadow: 0 0 0 3px rgba(79,124,255,.12);
}
.pi-recv-input:disabled { background: #f3f4f6; color: var(--pi-muted); cursor: not-allowed; }
.pi-recv-input.pi-over  { border-color: var(--pi-danger); background: #fff5f5; }

.pi-empty { text-align: center; padding: 30px; color: var(--pi-muted); font-size: 13px; }

.pi-tfoot-row td {
    border-top: 2px solid var(--pi-border);
    padding: 10px 14px;
    font-weight: 600;
    font-size: 13px;
    background: #f8fafc;
}

/* ─── Actions & Summary ──────────────────────────────────── */
.pi-actions { margin-top: 16px; }

.pi-summary-strip {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--pi-white);
    border: 1px solid var(--pi-border);
    border-radius: var(--pi-radius);
    padding: 14px 20px;
    margin-bottom: 14px;
    flex-wrap: wrap;
    box-shadow: var(--pi-shadow);
}
.pi-summary-item { text-align: center; }
.pi-summary-item span { display: block; font-size: 11.5px; color: var(--pi-muted); margin-bottom: 3px; }
.pi-summary-item strong { font-size: 15px; font-weight: 700; color: var(--pi-text); }
.pi-summary-total strong { color: var(--pi-danger); }
.pi-summary-paying strong { color: var(--pi-success); }
.pi-summary-sep { font-size: 18px; color: var(--pi-muted); font-weight: 300; }
.pi-summary-spacer { flex: 1; }

.pi-submit-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 13px;
    background: var(--pi-success);
    color: white;
    border: none;
    border-radius: var(--pi-radius);
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s, transform .1s, box-shadow .15s;
}
.pi-submit-btn:hover:not(:disabled) {
    background: #0ea15f;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(18,183,106,.25);
}
.pi-submit-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }

@keyframes pi-spin { to { transform: rotate(360deg); } }
.pi-spin { animation: pi-spin .8s linear infinite; }
</style>
@endpush

@push('scripts')
<script>
/* ═══════════════════════════════════════════════════════════
   STATE
═══════════════════════════════════════════════════════════ */
let party     = null;   // selected party object
let invoices  = [];     // unpaid/partial invoices
let allocationMap = {}; // { invoiceId: amount }

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
        $.get('{{ route("admin.payments.search-parties") }}', { search: q })
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
    $('#partyDropdown').html('<div class="pi-drop-loading">Searching…</div>').show();
}

function renderDropdown(parties) {
    const $d = $('#partyDropdown').empty();
    if (!parties.length) {
        $d.html('<div class="pi-drop-empty">No parties found with invoices</div>').show();
        return;
    }
    parties.forEach(p => {
        const dueClass = p.total_due > 0 ? 'due-total' : '';
        $d.append(`
            <div class="pi-drop-item" onclick="selectParty('${p.id}')">
                <div class="pi-drop-name">${esc(p.name)}
                    <span class="pi-party-badge ${p.party_type}">${esc(p.party_type_text)}</span>
                </div>
                <div class="pi-drop-meta">
                    <span>📞 ${esc(p.phone)}</span>
                    ${p.email && p.email !== '-' ? `<span>✉️ ${esc(p.email)}</span>` : ''}
                </div>
                <div class="pi-drop-due">
                    <span>Opening: ₹${fmt(p.opening_balance)}</span>
                    <span>Invoice Due: ₹${fmt(p.invoice_due)}</span>
                    <span class="${dueClass}">Total Due: ₹${fmt(p.total_due)}</span>
                </div>
            </div>
        `);
    });
    $d.show();
}

function closeDropdown() {
    $('#partyDropdown').hide();
}

function selectParty(partyId) {
    closeDropdown();
    $('#partySearch').val('').hide();
    $('#partySearchWrap .pi-search-field').hide();
    $('#clearPartyBtn').hide();

    $.get('{{ route("admin.payments.party.details", ":id") }}'.replace(':id', partyId))
        .done(res => {
            if (!res.success) { showAlert('Failed to load party', 'error'); return; }
            party    = res.party;
            invoices = (party.invoices || []).map(inv => ({ ...inv }));
            allocationMap = {};

            // Show selected party
            $('#selPartyName').text(party.name);
            $('#selPartyBadge').text(party.party_type_text)
                .attr('class', 'pi-party-badge ' + party.party_type);
            $('#selPartyPhone').text('📞 ' + party.phone);
            $('#partyId').val(party.id);
            $('#currentBalanceDisplay').text('₹' + fmt(party.total_due));

            // NEW: Show credit note block if credit available
            const creditBalance = party.credit_balance || 0;
            const creditNotes   = party.credit_notes || [];

            if (creditBalance > 0 && creditNotes.length > 0) {
                let listHtml = '';
                creditNotes.forEach(cn => {
                    listHtml += `<div style="display:flex;justify-content:space-between;margin-bottom:2px;">
                        <span>${esc(cn.credit_note_number)} &nbsp;<span style="opacity:.7">${esc(cn.credit_date)}</span></span>
                        <span style="font-weight:600;">₹${fmt(cn.remaining_amount)}</span>
                    </div>`;
                });
                $('#creditNotesList').html(listHtml);
                $('#creditTotalDisplay').text('-₹' + fmt(creditBalance));
                $('#netPayableDisplay').text('₹' + fmt(party.net_payable));
                $('#creditNoteBlock').show();
            } else {
                $('#creditNoteBlock').hide();
            }

            $('#partySelected').show();
            $('#amountSection').show();
            $('#invoicesSection').show();
            $('#submitSection').show();

            // Pre-fill amount with net payable (instead of total due)
            const prefill = party.net_payable > 0 ? party.net_payable : 0;
            $('#amountReceived').val(fmt(prefill));

            renderInvoices();
            updateSummary();

            // Auto-allocate with the pre-filled amount
            if (prefill > 0) {
                autoAllocate(prefill);
            }

            $('#amountReceived').focus();
        })
        .fail(() => showAlert('Failed to load party details', 'error'));
}
function resetParty() {
    party = null; invoices = []; allocationMap = {};
    $('#partySelected').hide();
    $('#amountSection').hide();
    $('#invoicesSection').hide();
    $('#submitSection').hide();
    $('#amountReceived').val('');
    $('#partyId').val('');
    $('#partySearch').val('').show();
    $('#partySearchWrap .pi-search-field').show();
    $('#submitBtn').prop('disabled', true);
    $('#invoicesTfoot').hide();
    renderInvoices(true);
    updateSummary();
}

/* ═══════════════════════════════════════════════════════════
   INVOICES
═══════════════════════════════════════════════════════════ */
function renderInvoices(empty = false) {
    const $tb = $('#invoicesTbody').empty();
    $('#invoicesTfoot').hide();

    if (empty || !invoices.length) {
        $tb.append(`<tr id="noInvoicesRow">
            <td colspan="7" class="pi-empty">
                ${empty ? 'Select a party to view invoices' : 'No unpaid or partial invoices found'}
            </td></tr>`);
        return;
    }

    invoices.forEach((inv, i) => {
        // ALWAYS use server-sent balance (grand_total - total_paid).
        // Never recalculate on the client — the server already guarantees this.
        const pendingBalance = parseFloat(inv.balance);

        const alloc   = allocationMap[inv.id] || 0;
        const checked = alloc > 0 ? 'checked' : '';
        const rowCls  = alloc > 0 ? 'pi-row-selected' : '';

        // Invoice Amount column: always show grand_total,
        // and always show the PENDING amount underneath for both partial and unpaid.
        const isPartial = inv.payment_status === 'partial';
        const isUnpaid  = inv.payment_status === 'unpaid';
        const pendingLabel = isPartial
            ? `<small style="color:var(--pi-warn);display:block;font-size:11px;">₹${fmt(pendingBalance)} pending</small>`
            : (isUnpaid && inv.paid > 0)
                ? `<small style="color:var(--pi-warn);display:block;font-size:11px;">₹${fmt(pendingBalance)} pending</small>`
                : '';

        const statusBadge = isPartial
            ? `<span class="pi-status-badge partial">Partial</span>`
            : '';

       $tb.append(`
        <tr data-idx="${i}" class="${rowCls}" id="inv-row-${i}">
            <td><input type="checkbox" class="pi-cb inv-cb" data-idx="${i}"
                ${checked} onchange="onCbChange(${i}, this.checked)"></td>
            <td>${esc(inv.invoice_date)}</td>
            <td>${esc(inv.due_date)}</td>
            <td>${esc(inv.invoice_number)} ${statusBadge}</td>
            <td>${esc(inv.warehouse_name || '—')}</td>
            <td>₹${fmt(inv.grand_total)}${pendingLabel}</td>
            <td id="recv-${i}" style="font-weight:600; color: var(--pi-text);">
                ${alloc > 0 ? '₹' + fmt(alloc) : '—'}
            </td>
        </tr>
    `);
    });

    updateTableFooter();
    $('#invoicesTfoot').show();
}
function limitDecimals(e, input) {
    const val = input.value;
    const dotPos = val.indexOf('.');
    if (dotPos !== -1 && val.length - dotPos > 2) {
        // Already 2 decimal digits typed, block more digits (not backspace/delete/arrows)
        const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab'];
        if (!allowed.includes(e.key) && !isNaN(e.key)) {
            e.preventDefault();
        }
    }
}
function onCbChange(idx, checked) {
    const inv = invoices[idx];
    const pendingBalance = parseFloat(inv.balance);   // ← always the server-sent pending amount

    if (checked) {
        const remaining = getRemainingBudget();
        const toAlloc   = Math.min(pendingBalance, remaining);
        allocationMap[inv.id] = toAlloc;
       $(`#recv-${idx}`).text(toAlloc > 0 ? '₹' + fmt(toAlloc) : '—');
        $(`#inv-row-${idx}`).addClass('pi-row-selected');
    } else {
        delete allocationMap[inv.id];
        $(`#recv-${idx}`).text('—');
        $(`#inv-row-${idx}`).removeClass('pi-row-selected');
    }
    updateSelectAll();
    updateSummary();
    updateTableFooter();
}

function onRecvInput(idx, val) {
    const inv            = invoices[idx];
    const pendingBalance = parseFloat(inv.balance);   // ← pending amount from server
    let   amount         = parseFloat(val) || 0;
    const $inp           = $(`#recv-${idx}`);

    // Clamp: cannot pay more than the pending balance on this invoice
    if (amount > pendingBalance) { amount = pendingBalance; $inp.val(fmt(amount)); }
    if (amount < 0)              { amount = 0; $inp.val(''); }

    if (amount > 0) {
        allocationMap[inv.id] = amount;
        $(`.inv-cb[data-idx="${idx}"]`).prop('checked', true);
        $(`#inv-row-${idx}`).addClass('pi-row-selected');
    } else {
        delete allocationMap[inv.id];
        $(`.inv-cb[data-idx="${idx}"]`).prop('checked', false);
        $(`#inv-row-${idx}`).removeClass('pi-row-selected');
    }

    updateSelectAll();
    updateSummary();
    updateTableFooter();
}

$('#selectAllCb').on('change', function () {
    const checked   = $(this).is(':checked');
    let   remaining = getRemainingBudget();

    invoices.forEach((inv, i) => {
        if (checked && remaining > 0) {
            const toAlloc = Math.min(inv.balance, remaining);
            allocationMap[inv.id] = toAlloc;
            $(`#recv-${i}`).text(toAlloc > 0 ? '₹' + fmt(toAlloc) : '—');
            $(`#inv-row-${i}`).addClass('pi-row-selected');
            $(`.inv-cb[data-idx="${i}"]`).prop('checked', true);
            remaining -= toAlloc;
        } else {
            delete allocationMap[inv.id];
            $(`#recv-${i}`).text('—');
            $(`#inv-row-${i}`).removeClass('pi-row-selected');
            $(`.inv-cb[data-idx="${i}"]`).prop('checked', false);
        }
    });

    updateSummary();
    updateTableFooter();
});

function updateSelectAll() {
    const total    = invoices.length;
    const selected = Object.keys(allocationMap).filter(id => invoices.find(inv => inv.id === id)).length;
    const $cb = $('#selectAllCb');
    if (selected === 0)     { $cb.prop('checked', false).prop('indeterminate', false); }
    else if (selected === total) { $cb.prop('checked', true).prop('indeterminate', false); }
    else                    { $cb.prop('checked', false).prop('indeterminate', true); }
}

function updateTableFooter() {
    // Show sum of pending balances (what is actually owed), not grand totals
    const totalPending = invoices.reduce((s, inv) => s + parseFloat(inv.balance), 0);
    const totalRecv    = Object.values(allocationMap).reduce((s, v) => s + v, 0);
    $('#tfootInvoiceTotal').text('₹' + fmt(totalPending));
    $('#tfootAmountReceived').text('₹' + fmt(totalRecv));
}

$('#amountReceived').on('input', function () {
    const amount     = parseFloat($(this).val()) || 0;
    const netPayable = party ? (party.net_payable || 0) : 0; // CHANGED

    const $msg = $('#amountValidation');
    if (amount <= 0) {
        $msg.hide();
    } else if (amount > netPayable + 0.005) {
        $msg.text('Amount exceeds net payable of ₹' + fmt(netPayable)).attr('class','pi-val-msg err').show();
        $(this).val(fmt(netPayable));
        return;
    } else {
        $msg.hide();
    }

    if (amount > 0) {
        autoAllocate(amount);
    } else {
        clearAllocations();
    }

    updateSummary();
});
function autoAllocate(amount) {
    allocationMap = {};
     const creditBalance = parseFloat(party.credit_balance) || 0;
    let remaining = amount + creditBalance;

    // Opening balance consumed first (allocation handled server-side on save)
    const openingAlloc = Math.min(parseFloat(party.opening_balance) || 0, remaining);
    remaining -= openingAlloc;

    // Allocate to invoices oldest-first using the SERVER-SENT pending balance
    invoices.forEach((inv, i) => {
        const pendingBalance = parseFloat(inv.balance);  // ← correct pending amount

        if (remaining <= 0.001 || pendingBalance <= 0) {
            $(`#recv-${i}`).text('—');
            $(`.inv-cb[data-idx="${i}"]`).prop('checked', false);
            $(`#inv-row-${i}`).removeClass('pi-row-selected');
            return;
        }

        const toAlloc = Math.min(pendingBalance, remaining);
        allocationMap[inv.id] = toAlloc;
        remaining -= toAlloc;

        $(`#recv-${i}`).text('₹' + fmt(toAlloc));
        $(`.inv-cb[data-idx="${i}"]`).prop('checked', true);
        $(`#inv-row-${i}`).addClass('pi-row-selected');
    });

    // Deselect any rows that received no allocation
    invoices.forEach((inv, i) => {
        if (!allocationMap[inv.id]) {
            $(`.inv-cb[data-idx="${i}"]`).prop('checked', false);
            $(`#inv-row-${i}`).removeClass('pi-row-selected');
            $(`#recv-${i}`).text('—');
        }
    });

    updateSelectAll();
    updateTableFooter();
}

function clearAllocations() {
    allocationMap = {};
    invoices.forEach((inv, i) => {
        $(`.inv-cb[data-idx="${i}"]`).prop('checked', false);
        $(`#inv-row-${i}`).removeClass('pi-row-selected');
        $(`#recv-${i}`).text('—');
    });
    updateSelectAll();
    updateTableFooter();
}

function getRemainingBudget() {
    const amount  = parseFloat($('#amountReceived').val()) || 0;
    const opening = Math.min(party ? (party.opening_balance || 0) : 0, amount);
    const allocated = Object.values(allocationMap).reduce((s, v) => s + v, 0);
    return amount - opening - allocated;
}

function updateSummary() {
    if (!party) {
        $('#sumOpening, #sumInvoice, #sumTotal, #sumPaying').text('₹0.00');
        $('#sumCredit').text('₹0.00');  // NEW
        $('#submitBtn').prop('disabled', true);
        return;
    }
    const amount     = parseFloat($('#amountReceived').val()) || 0;
    const opening    = party.opening_balance || 0;
    const invDue     = party.invoice_due || 0;
    const totalDue   = party.total_due || 0;
    const netPayable = party.net_payable || 0;

    $('#sumOpening').text('₹' + fmt(opening));
    $('#sumInvoice').text('₹' + fmt(invDue));
    $('#sumCredit').text('₹'  + fmt(party.credit_balance || 0));  // NEW
    $('#sumTotal').text('₹'   + fmt(netPayable));                 // CHANGED: was totalDue
    $('#sumPaying').text('₹'  + fmt(amount));

    const valid = amount >= 0 && amount <= (netPayable + 0.005);
    $('#submitBtn').prop('disabled', !valid);
}
/* ═══════════════════════════════════════════════════════════
   INVOICE SEARCH FILTER
═══════════════════════════════════════════════════════════ */
$('#invoiceSearch').on('input', function () {
    const q = $(this).val().toLowerCase();
    $('#invoicesTbody tr').each(function () {
        const text = $(this).text().toLowerCase();
        $(this).toggle(!q || text.includes(q));
    });
});

/* ═══════════════════════════════════════════════════════════
   FORM SUBMIT
═══════════════════════════════════════════════════════════ */
$('#paymentForm').on('submit', function (e) {
    e.preventDefault();

    const amount  = parseFloat($('#amountReceived').val()) || 0;
    const method  = $('#paymentMethod').val();
    const date    = $('#paymentDate').val();
    const partyId = $('#partyId').val();
    const netPayable = party ? (party.net_payable || 0) : 0;
    const creditBalance = party ? (party.credit_balance || 0) : 0;

    if (!partyId)  { showAlert('Please select a party', 'error'); return; }
    if (amount <= 0 && creditBalance <= 0) {
        showAlert('Please enter a valid amount', 'error');
        return;
    }
    if (!method)   { showAlert('Please select a payment mode', 'error'); return; }
    if (!date)     { showAlert('Please select a payment date', 'error'); return; }

    const formData = {
        _token:          '{{ csrf_token() }}',
        party_id:        partyId,
        amount:          amount,
        payment_date:    date,
        payment_method:  method,
        reference_no:    $('input[name="reference_no"]').val(),
        notes:           $('textarea[name="notes"]').val()
    };

    $('#submitBtn').prop('disabled', true);
    $('#btnText').hide();
    $('#btnLoader').show();

    $.ajax({
        url:  '{{ route("admin.payments.store") }}',
        type: 'POST',
        data: formData,
        success: function (res) {
            if (res.success) {
                showAlert('Payment saved! Payment #: ' + res.payment_number, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("admin.payments.index") }}';
                }, 1600);
            } else {
                showAlert(res.message || 'Failed to save payment', 'error');
                resetSubmitBtn();
            }
        },
        error: function (xhr) {
            const msg = xhr.responseJSON?.message || 'Failed to save payment';
            showAlert(msg, 'error');
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
    const $a = $('#piAlert');
    $a.attr('class', 'pi-alert ' + type).text(msg).show();
    clearTimeout(showAlert._t);
    showAlert._t = setTimeout(() => $a.fadeOut(), 5000);
}

// Close dropdown when clicking outside
$(document).on('click', function (e) {
    if (!$(e.target).closest('#partySearchWrap').length) closeDropdown();
});
$(document).on('keydown', function (e) {
    if (e.key === 'Escape') closeDropdown();
});
</script>
@endpush
