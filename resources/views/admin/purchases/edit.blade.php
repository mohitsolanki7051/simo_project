@extends('layouts.admin')
@section('title', 'Edit Purchase')
@section('content')
    <div class="purchase-container">
        <div class="header">
            <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="back">← Back</a>
            <h2>Edit Purchase #{{ $purchase->purchase_no }}</h2>
            <span class="badge status-{{ $purchase->status }}">{{ ucfirst($purchase->status) }}</span>
        </div>
        @if ($purchase->status !== 'draft')
            <div class="alert-warning">⚠️ Only draft purchases can be edited. This purchase is {{ $purchase->status }}.</div>
        @else
            <form action="{{ route('admin.purchases.update', $purchase->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-8">
                        <div class="card">
                            <h3>📋 Details</h3>
                            <div class="grid">
                                <div class="full"><label>Supplier</label><select name="supplier_id" required>
                                        @foreach ($suppliers as $s)
                                            <option value="{{ $s->id }}"
                                                {{ $purchase->supplier_id == $s->id ? 'selected' : '' }}>{{ $s->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div><label>Invoice No</label><input name="invoice_no" value="{{ $purchase->invoice_no }}">
                                </div>
                                <div><label>Invoice Date</label><input type="date" name="invoice_date"
                                        value="{{ $purchase->invoice_date }}"></div>
                                <div><label>Purchase Date</label><input type="date" name="purchase_date"
                                        value="{{ $purchase->purchase_date }}" required></div>
                            </div>
                        </div>
                        <div class="card">
                            <h3>🛒 Items</h3>
                            <table id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Variant</th>
                                        <th>Qty</th>
                                        <th>Rate</th>
                                        <th>Tax%</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @foreach ($purchase->items as $idx => $item)
                                        <tr class="item-row">
                                            <td><select name="items[{{ $idx }}][product_id]" required>
                                                    @foreach ($products as $p)
                                                        <option value="{{ $p->id }}"
                                                            {{ $item->product_id == $p->id ? 'selected' : '' }}>
                                                            {{ $p->name }}</option>
                                                    @endforeach
                                                </select></td>
                                            <td><input type="text" name="items[{{ $idx }}][variant_name]"
                                                    value="{{ $item->variant_name }}" required></td>
                                            <td><input type="number" name="items[{{ $idx }}][qty]" class="qty"
                                                    value="{{ $item->qty }}" min="1" required
                                                    onchange="calculateRow({{ $idx }})"></td>
                                            <td><input type="number" name="items[{{ $idx }}][rate]"
                                                    class="rate" value="{{ $item->rate }}" step="0.01" required
                                                    onchange="calculateRow({{ $idx }})"></td>
                                            <td><input type="number" name="items[{{ $idx }}][tax_percent]"
                                                    class="tax" value="{{ $item->tax_percent }}" step="0.01"
                                                    onchange="calculateRow({{ $idx }})"></td>
                                            <td><input type="number" name="items[{{ $idx }}][total]"
                                                    class="total" value="{{ $item->total }}" readonly></td>
                                            <td><button type="button" onclick="removeItem(this)"
                                                    class="btn-remove">×</button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card summary-card">
                            <h3>💰 Summary</h3>
                            <div class="summary">
                                <div class="row"><span>Subtotal:</span><span
                                        id="subtotal">₹{{ number_format($purchase->subtotal, 2) }}</span></div>
                                <div class="row"><label>Discount:</label><input type="number" name="discount"
                                        id="discount" value="{{ $purchase->discount }}" step="0.01"
                                        onchange="calculateTotal()"></div>
                                <div class="row"><span>Tax:</span><span
                                        id="tax_amount">₹{{ number_format($purchase->tax_amount, 2) }}</span></div>
                                <div class="row"><label>Shipping:</label><input type="number" name="shipping_charge"
                                        id="shipping" value="{{ $purchase->shipping_charge }}" step="0.01"
                                        onchange="calculateTotal()"></div>
                                <div class="total-row"><span>Grand Total:</span><span
                                        id="grand_total">₹{{ number_format($purchase->grand_total, 2) }}</span></div>
                                <input type="hidden" name="subtotal" id="subtotal_input"
                                    value="{{ $purchase->subtotal }}">
                                <input type="hidden" name="tax_amount" id="tax_input" value="{{ $purchase->tax_amount }}">
                                <input type="hidden" name="grand_total" id="grand_input"
                                    value="{{ $purchase->grand_total }}">
                            </div>
                        </div>
                        <div class="card"><label>Notes</label>
                            <textarea name="notes" rows="3">{{ $purchase->notes }}</textarea>
                        </div>
                        <div class="actions">
                            <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn-cancel">Cancel</a>
                            <button type="submit" class="btn-save">Update Purchase</button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
    @push('styles')
        <style>
            .purchase-container {
                padding-bottom: 40px
            }

            .header {
                margin-bottom: 30px;
                display: flex;
                align-items: center;
                gap: 15px
            }

            .back {
                color: #718096;
                text-decoration: none;
                padding: 8px 16px;
                border-radius: 8px
            }

            .alert-warning {
                background: #fef5e7;
                border-left: 4px solid #ed8936;
                padding: 20px;
                border-radius: 12px;
                color: #7c2d12;
                margin-bottom: 30px
            }

            .row {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 20px
            }

            .card {
                background: white;
                border-radius: 16px;
                padding: 30px;
                margin-bottom: 20px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05)
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px
            }

            .full {
                grid-column: span 2
            }

            label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #4a5568
            }

            input,
            select,
            textarea {
                width: 100%;
                padding: 12px;
                border: 2px solid #e2e8f0;
                border-radius: 10px
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px
            }

            th {
                background: #f7fafc;
                padding: 12px;
                text-align: left
            }

            td {
                padding: 12px;
                border-bottom: 1px solid #e2e8f0
            }

            .item-row input,
            .item-row select {
                padding: 8px
            }

            .btn-remove {
                width: 32px;
                height: 32px;
                background: #fed7d7;
                border: none;
                border-radius: 6px;
                cursor: pointer
            }

            .summary .row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 15px;
                padding: 12px;
                background: #f7fafc;
                border-radius: 8px
            }

            .summary input {
                width: 120px;
                padding: 8px
            }

            .total-row {
                display: flex;
                justify-content: space-between;
                padding: 20px;
                background: linear-gradient(135deg, #fef5e7, #fdebd0);
                border-radius: 12px;
                font-size: 20px;
                font-weight: 700;
                color: #c05621;
                margin-top: 20px
            }

            .actions {
                display: flex;
                justify-content: flex-end;
                gap: 15px;
                margin-top: 20px
            }

            .btn-cancel {
                padding: 12px 24px;
                background: #e2e8f0;
                border: none;
                border-radius: 10px;
                text-decoration: none;
                color: #4a5568
            }

            .btn-save {
                padding: 12px 30px;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                border: none;
                border-radius: 10px;
                cursor: pointer
            }

            .badge {
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600
            }

            .status-draft {
                background: #feebc8;
                color: #7c2d12
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            function calculateRow(i) {
                const row = document.querySelector(`.item-row:nth-child(${i+1})`);
                const q = parseFloat(row.querySelector('.qty').value) || 0;
                const r = parseFloat(row.querySelector('.rate').value) || 0;
                const t = parseFloat(row.querySelector('.tax').value) || 0;
                const sub = q * r;
                const tax = (sub * t) / 100;
                row.querySelector('.total').value = (sub + tax).toFixed(2);
                calculateTotal()
            }

            function calculateTotal() {
                let sub = 0,
                    tax = 0;
                document.querySelectorAll('.item-row').forEach(r => {
                    const q = parseFloat(r.querySelector('.qty').value) || 0;
                    const rt = parseFloat(r.querySelector('.rate').value) || 0;
                    const tx = parseFloat(r.querySelector('.tax').value) || 0;
                    const s = q * rt;
                    sub += s;
                    tax += (s * tx) / 100
                });
                const disc = parseFloat(document.getElementById('discount').value) || 0;
                const ship = parseFloat(document.getElementById('shipping').value) || 0;
                const grand = sub - disc + tax + ship;
                document.getElementById('subtotal').textContent = `₹${sub.toFixed(2)}`;
                document.getElementById('tax_amount').textContent = `₹${tax.toFixed(2)}`;
                document.getElementById('grand_total').textContent = `₹${grand.toFixed(2)}`;
                document.getElementById('subtotal_input').value = sub.toFixed(2);
                document.getElementById('tax_input').value = tax.toFixed(2);
                document.getElementById('grand_input').value = grand.toFixed(2)
            }

            function removeItem(btn) {
                if (document.querySelectorAll('.item-row').length > 1) {
                    btn.closest('tr').remove();
                    calculateTotal()
                }
            }
            calculateTotal();
        </script>
    @endpush
@endsection
