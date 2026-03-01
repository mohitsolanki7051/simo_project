@extends('layouts.admin')

@section('title', 'New Payment In')
@section('header-title', 'New Payment In')

@section('content')
<div class="new-payment-container">
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Record Payment In #{{ date('Ymd') }}</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.payment-in.index') }}" class="btn-back">← Back to Payments</a>
        </div>
    </div>

    <!-- Main Content - Exactly like your image -->
    <div class="payment-main">
        <!-- Left Side - Party Info & Payment Details -->
        <div class="payment-left">
            <!-- Party Search -->
            <div class="party-search-card">
                <h3>Select Party</h3>
                <div class="search-box">
                    <input type="text" id="partySearch" class="form-control" placeholder="Search party by name or phone...">
                    <div id="partySearchResults" class="search-results" style="display: none;"></div>
                </div>

                <!-- Selected Party Info -->
                <div id="selectedPartyInfo" style="display: none;">
                    <div class="selected-party-header">
                        <h4 id="selectedPartyName"></h4>
                        <span class="party-badge" id="selectedPartyType"></span>
                    </div>
                    <div class="current-balance">
                        <span class="balance-label">Current Balance:</span>
                        <span class="balance-value" id="currentBalance">₹ 0</span>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="payment-details-card">
                <h3>Payment Details</h3>
                <form id="paymentForm">
                    @csrf
                    <input type="hidden" name="party_id" id="partyId">
                    <input type="hidden" name="invoices" id="invoicesInput">

                    <div class="form-group">
                        <label>Amount Received</label>
                        <input type="number" name="amount_received" id="amountReceived" class="form-control large-input" step="0.01" min="0" value="0" readonly>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="form-group">
                            <label>Payment Mode</label>
                            <select name="payment_mode" class="form-control">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Enter Notes"></textarea>
                    </div>

                    <!-- Invoice Search -->
                    <div class="invoice-search-section">
                        <label>Search Invoice Number</label>
                        <div class="search-box">
                            <input type="text" id="invoiceSearch" class="form-control" placeholder="Enter invoice number...">
                            <button type="button" id="searchInvoiceBtn" class="btn-search">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side - Invoices Table -->
        <div class="payment-right">
            <div class="invoices-card">
                <div class="invoices-header">
                    <h3>Outstanding Invoices</h3>
                    <span id="totalOutstanding" class="total-outstanding">₹ 0</span>
                </div>

                <div id="noPartySelected" class="no-party-message">
                    <div class="message-icon">👈</div>
                    <h4>Select a party to view invoices</h4>
                    <p>Search and select a party from the left panel</p>
                </div>

                <div id="invoicesTableContainer" style="display: none;">
                    <table class="invoices-table">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAllInvoices">
                                </th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Invoice #</th>
                                <th>Invoice Amount</th>
                                <th>Discount</th>
                                <th>Amount Received</th>
                            </tr>
                        </thead>
                        <tbody id="invoicesTableBody">
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align: right;"><strong>Total</strong></td>
                                <td><strong>₹ <span id="totalSelectedAmount">0.00</span></strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="form-actions">
                        <button type="button" class="btn-save" id="savePaymentBtn" onclick="submitPayment()">Save</button>
                    </div>
                </div>

                <div id="loadingInvoices" style="display: none; text-align: center; padding: 40px;">
                    <div class="loading-spinner"></div>
                    <p>Loading invoices...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.new-payment-container {
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #f3f4f6;
    min-height: 100vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.page-title {
    font-size: 20px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    color: #374151;
    font-size: 12px;
    text-decoration: none;
}

.payment-main {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 20px;
}

/* Left Panel */
.payment-left {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.party-search-card, .payment-details-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.party-search-card h3, .payment-details-card h3, .invoices-card h3 {
    font-size: 15px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
}

.search-box {
    position: relative;
    margin-bottom: 15px;
}

.search-box .form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    max-height: 250px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.search-result-item {
    padding: 10px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
}

.search-result-item:hover {
    background: #f9fafb;
}

.search-result-item .party-name {
    font-weight: 600;
    color: #111827;
}

.search-result-item .party-detail {
    font-size: 11px;
    color: #6b7280;
    margin-top: 2px;
}

.selected-party-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.selected-party-header h4 {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.current-balance {
    background: #f3f4f6;
    padding: 12px;
    border-radius: 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.balance-label {
    font-size: 13px;
    color: #4b5563;
}

.balance-value {
    font-size: 18px;
    font-weight: 700;
    color: #f97316;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: #4b5563;
    margin-bottom: 5px;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
}

.form-control.large-input {
    font-size: 20px;
    font-weight: 600;
    padding: 12px;
    color: #f97316;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.invoice-search-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.invoice-search-section .search-box {
    display: flex;
    gap: 8px;
}

.btn-search {
    padding: 8px 16px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    white-space: nowrap;
}

/* Right Panel */
.payment-right {
    background: white;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.invoices-card {
    padding: 20px;
}

.invoices-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.total-outstanding {
    font-size: 16px;
    font-weight: 600;
    color: #f97316;
}

.no-party-message {
    text-align: center;
    padding: 60px 20px;
    background: #f9fafb;
    border-radius: 8px;
}

.message-icon {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.no-party-message h4 {
    font-size: 16px;
    color: #374151;
    margin-bottom: 8px;
}

.no-party-message p {
    font-size: 13px;
    color: #6b7280;
}

.invoices-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.invoices-table th {
    background: #f8fafc;
    padding: 12px 8px;
    text-align: left;
    font-weight: 600;
    color: #4b5563;
    border-bottom: 1px solid #e5e7eb;
}

.invoices-table td {
    padding: 10px 8px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.invoices-table tfoot td {
    padding: 15px 8px;
    border-top: 2px solid #e5e7eb;
    font-weight: 600;
}

.amount-input {
    width: 100px;
    padding: 5px 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    text-align: right;
}

.amount-input:focus {
    outline: none;
    border-color: #f97316;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.btn-save {
    padding: 10px 40px;
    background: #f97316;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
}

.btn-save:hover {
    background: #ea580c;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f3f4f6;
    border-top-color: #f97316;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<script>
let selectedParty = null;
let invoices = [];
let selectedInvoices = [];

// Party Search
document.getElementById('partySearch').addEventListener('input', function() {
    const search = this.value;
    if (search.length < 2) {
        document.getElementById('partySearchResults').style.display = 'none';
        return;
    }

    fetch(`{{ route('admin.payment-in.search-parties') }}?q=${encodeURIComponent(search)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.parties.length > 0) {
            const resultsDiv = document.getElementById('partySearchResults');
            resultsDiv.innerHTML = '';
            data.parties.forEach(party => {
                const item = document.createElement('div');
                item.className = 'search-result-item';
                item.innerHTML = `
                    <div class="party-name">${party.name}</div>
                    <div class="party-detail">${party.phone || ''} • ${party.party_type_text}</div>
                `;
                item.onclick = () => selectParty(party);
                resultsDiv.appendChild(item);
            });
            resultsDiv.style.display = 'block';
        } else {
            document.getElementById('partySearchResults').style.display = 'none';
        }
    });
});

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-box')) {
        document.getElementById('partySearchResults').style.display = 'none';
    }
});

function selectParty(party) {
    selectedParty = party;
    document.getElementById('partyId').value = party.id;
    document.getElementById('selectedPartyName').textContent = party.name;
    document.getElementById('selectedPartyType').textContent = party.party_type_text;
    document.getElementById('selectedPartyInfo').style.display = 'block';
    document.getElementById('partySearch').value = party.name;
    document.getElementById('partySearchResults').style.display = 'none';

    // Load invoices
    loadPartyInvoices(party.id);
}

function loadPartyInvoices(partyId) {
    document.getElementById('noPartySelected').style.display = 'none';
    document.getElementById('invoicesTableContainer').style.display = 'none';
    document.getElementById('loadingInvoices').style.display = 'block';

    fetch(`{{ route('admin.payment-in.get-party-details') }}?party_id=${partyId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loadingInvoices').style.display = 'none';

        if (data.success) {
            invoices = data.invoices;
            document.getElementById('currentBalance').textContent = `₹ ${data.total_outstanding.toFixed(2)}`;
            document.getElementById('totalOutstanding').textContent = `₹ ${data.total_outstanding.toFixed(2)}`;

            if (invoices.length > 0) {
                renderInvoicesTable(invoices);
                document.getElementById('invoicesTableContainer').style.display = 'block';
            } else {
                document.getElementById('noPartySelected').style.display = 'block';
                document.getElementById('noPartySelected').querySelector('h4').textContent = 'No Outstanding Invoices';
                document.getElementById('noPartySelected').querySelector('p').textContent = 'This party has no pending invoices';
            }
        }
    });
}

function renderInvoicesTable(invoices) {
    const tbody = document.getElementById('invoicesTableBody');
    tbody.innerHTML = '';

    invoices.forEach((invoice, index) => {
        const row = document.createElement('tr');
        row.dataset.invoiceId = invoice.id;
        row.dataset.balance = invoice.balance;

        row.innerHTML = `
            <td>
                <input type="checkbox" class="invoice-checkbox" data-index="${index}" onchange="toggleInvoice(${index})" checked>
            </td>
            <td>${invoice.invoice_date}</td>
            <td>${invoice.due_date}</td>
            <td><strong>${invoice.invoice_number}</strong></td>
            <td>₹ ${invoice.grand_total.toFixed(2)}</td>
            <td>
                <button type="button" class="btn-apply-discount" onclick="applyDiscount(${index})" style="background: none; border: none; color: #f97316; cursor: pointer; font-size: 11px;">
                    Apply Discount
                </button>
            </td>
            <td>
                <input type="number" class="amount-input" id="paid-${index}"
                       min="0" max="${invoice.balance}" step="0.01"
                       value="${invoice.balance}" data-index="${index}"
                       onchange="updatePaidAmount(${index})">
            </td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('selectAllInvoices').checked = true;
    calculateTotal();
}

function toggleInvoice(index) {
    const checkbox = document.querySelector(`.invoice-checkbox[data-index="${index}"]`);
    const paidInput = document.getElementById(`paid-${index}`);

    if (checkbox.checked) {
        paidInput.disabled = false;
        paidInput.value = invoices[index].balance;
    } else {
        paidInput.disabled = true;
        paidInput.value = 0;
    }

    calculateTotal();
}

function updatePaidAmount(index) {
    const paidInput = document.getElementById(`paid-${index}`);
    const maxAmount = invoices[index].balance;

    let value = parseFloat(paidInput.value) || 0;
    if (value < 0) value = 0;
    if (value > maxAmount) value = maxAmount;

    paidInput.value = value.toFixed(2);
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    invoices.forEach((invoice, index) => {
        const checkbox = document.querySelector(`.invoice-checkbox[data-index="${index}"]`);
        const paidInput = document.getElementById(`paid-${index}`);

        if (checkbox && checkbox.checked) {
            total += parseFloat(paidInput.value) || 0;
        }
    });

    document.getElementById('totalSelectedAmount').textContent = total.toFixed(2);
    document.getElementById('amountReceived').value = total.toFixed(2);
}

document.getElementById('selectAllInvoices').addEventListener('change', function(e) {
    const checkboxes = document.querySelectorAll('.invoice-checkbox');
    checkboxes.forEach((checkbox, index) => {
        checkbox.checked = e.target.checked;
        toggleInvoice(index);
    });
});

// Search Invoice
document.getElementById('searchInvoiceBtn').addEventListener('click', function() {
    const invoiceNumber = document.getElementById('invoiceSearch').value;
    if (!invoiceNumber || !selectedParty) {
        alert('Please select a party and enter invoice number');
        return;
    }

    fetch(`{{ route('admin.payment-in.search-invoice') }}?party_id=${selectedParty.id}&invoice_number=${encodeURIComponent(invoiceNumber)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Find and scroll to invoice
            const rows = document.querySelectorAll('#invoicesTableBody tr');
            for (let row of rows) {
                if (row.querySelector('td:nth-child(4)').textContent.includes(invoiceNumber)) {
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    row.style.backgroundColor = '#fff7ed';
                    setTimeout(() => row.style.backgroundColor = '', 2000);
                    break;
                }
            }
        } else {
            alert(data.message);
        }
    });
});

function applyDiscount(index) {
    const discount = prompt('Enter discount amount (₹):', '0');
    if (discount !== null) {
        const discountAmount = parseFloat(discount) || 0;
        const paidInput = document.getElementById(`paid-${index}`);
        const currentValue = parseFloat(paidInput.value) || 0;
        const newValue = Math.max(0, currentValue - discountAmount);
        paidInput.value = newValue.toFixed(2);
        calculateTotal();
    }
}

function submitPayment() {
    // Collect selected invoices
    const selectedInvoices = [];
    invoices.forEach((invoice, index) => {
        const checkbox = document.querySelector(`.invoice-checkbox[data-index="${index}"]`);
        const paidInput = document.getElementById(`paid-${index}`);

        if (checkbox && checkbox.checked && parseFloat(paidInput.value) > 0) {
            selectedInvoices.push({
                id: invoice.id,
                paid: parseFloat(paidInput.value)
            });
        }
    });

    if (selectedInvoices.length === 0) {
        showAlert('Please select at least one invoice', 'error');
        return;
    }

    // Set form data
    document.getElementById('invoicesInput').value = JSON.stringify(selectedInvoices);

    const formData = new FormData(document.getElementById('paymentForm'));

    const submitBtn = document.getElementById('savePaymentBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    fetch('{{ route("admin.payment-in.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Payment ${data.payment_number} recorded successfully!`, 'success');
            setTimeout(() => {
                window.location.href = '{{ route("admin.payment-in.index") }}';
            }, 1500);
        } else {
            showAlert(data.message, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save';
        }
    })
    .catch(error => {
        showAlert('Failed to record payment', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save';
    });
}

function showAlert(message, type) {
    const container = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = message;
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}
</script>
@endsection
