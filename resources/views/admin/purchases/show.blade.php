@extends('layouts.admin')
@section('title', 'Purchase Details')
@section('content')
    <div class="purchase-detail">
        <div class="header">
            <div><a href="{{ route('admin.purchases.index') }}" class="back">← Back</a>
                <h2>Purchase #{{ $purchase->purchase_no }}</h2>
            </div>
            <div class="actions">
                <a href="{{ route('admin.purchases.invoice', $purchase->id) }}" target="_blank" class="btn">🖨️ Invoice</a>
                @if ($purchase->status === 'draft')
                    <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="btn">✏️ Edit</a>
                    <form action="{{ route('admin.purchases.confirm', $purchase->id) }}" method="POST"
                        style="display:inline"
                        onsubmit="return confirm('Confirm this purchase? Stock will be updated and ledger entry created.')">
                        @csrf<button type="submit" class="btn-confirm">✅ Confirm</button></form>
                @endif
                <a href="{{ route('admin.suppliers.ledger', $purchase->supplier_id) }}" class="btn">📊 Ledger</a>
            </div>
        </div>
        <div class="row">
            <div class="col-8">
                <div class="card">
                    <div class="info-grid">
                        <div><label>Supplier</label>
                            <p><strong>{{ $purchase->supplier->name }}</strong><br><small>{{ $purchase->supplier->supplier_code }}</small>
                            </p>
                        </div>
                        <div><label>Invoice No</label>
                            <p>{{ $purchase->invoice_no ?? 'N/A' }}</p>
                        </div>
                        <div><label>Invoice Date</label>
                            <p>{{ $purchase->invoice_date ? date('d M Y', strtotime($purchase->invoice_date)) : 'N/A' }}</p>
                        </div>
                        <div><label>Purchase Date</label>
                            <p>{{ date('d M Y', strtotime($purchase->purchase_date)) }}</p>
                        </div>
                        <div><label>Status</label>
                            <p><span class="badge status-{{ $purchase->status }}">{{ ucfirst($purchase->status) }}</span>
                            </p>
                        </div>
                        <div><label>Payment</label>
                            <p><span
                                    class="badge payment-{{ $purchase->payment_status }}">{{ ucfirst($purchase->payment_status) }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <h3>📦 Items</h3>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Variant</th>
                                <th>Qty</th>
                                <th>Rate</th>
                                <th>Tax</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchase->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->variant_name }}<br><small>SKU: {{ $item->sku_code }}</small></td>
                                    <td>{{ $item->qty }} {{ $item->unit }}</td>
                                    <td>₹{{ number_format($item->rate, 2) }}</td>
                                    <td>{{ $item->tax_percent }}% (₹{{ number_format($item->tax_amount, 2) }})</td>
                                    <td>₹{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($purchase->notes)
                    <div class="card">
                        <h3>📝 Notes</h3>
                        <p>{{ $purchase->notes }}</p>
                    </div>
                @endif
            </div>
            <div class="col-4">
                <div class="card summary">
                    <h3>💰 Financial Summary</h3>
                    <div class="row"><span>Subtotal:</span><span>₹{{ number_format($purchase->subtotal, 2) }}</span>
                    </div>
                    @if ($purchase->discount > 0)
                        <div class="row"><span>Discount:</span><span>-₹{{ number_format($purchase->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="row"><span>Tax:</span><span>₹{{ number_format($purchase->tax_amount, 2) }}</span></div>
                    @if ($purchase->shipping_charge > 0)
                        <div class="row">
                            <span>Shipping:</span><span>₹{{ number_format($purchase->shipping_charge, 2) }}</span></div>
                    @endif
                    <div class="total"><span>Grand
                            Total:</span><span>₹{{ number_format($purchase->grand_total, 2) }}</span></div>
                    <hr>
                    <div class="row"><span>Paid:</span><span
                            class="paid">₹{{ number_format($purchase->paid_amount, 2) }}</span></div>
                    <div class="row"><span>Due:</span><span
                            class="due">₹{{ number_format($purchase->due_amount, 2) }}</span></div>
                </div>
                @if ($purchase->due_amount > 0)
                    <div class="card payment-card">
                        <h3>💵 Make Payment</h3>
                        <a href="{{ route('admin.supplier-payments.create') }}?purchase_id={{ $purchase->id }}"
                            class="btn-payment">Record Payment</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @push('styles')
        <style>
            .purchase-detail {
                padding-bottom: 40px
            }

            .header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px
            }

            .back {
                color: #718096;
                text-decoration: none;
                padding: 8px 16px;
                border-radius: 8px;
                display: inline-block;
                margin-bottom: 10px
            }

            .back:hover {
                background: #f7fafc
            }

            .actions {
                display: flex;
                gap: 10px
            }

            .btn,
            .btn-confirm {
                padding: 10px 20px;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                border: none;
                border-radius: 10px;
                cursor: pointer;
                text-decoration: none
            }

            .btn-confirm {
                background: linear-gradient(135deg, #38a169, #68d391)
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

            .info-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px
            }

            label {
                font-size: 13px;
                color: #718096;
                font-weight: 600;
                margin-bottom: 5px;
                display: block
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

            .status-confirmed {
                background: #bee3f8;
                color: #2c5282
            }

            .status-received {
                background: #c6f6d5;
                color: #22543d
            }

            .payment-paid {
                background: #c6f6d5;
                color: #22543d
            }

            .payment-partial {
                background: #feebc8;
                color: #7c2d12
            }

            .payment-unpaid {
                background: #fed7d7;
                color: #9b2c2c
            }

            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px
            }

            .items-table thead {
                background: #f7fafc
            }

            .items-table th {
                padding: 12px;
                text-align: left;
                font-weight: 600;
                border-bottom: 2px solid #e2e8f0
            }

            .items-table td {
                padding: 12px;
                border-bottom: 1px solid #e2e8f0
            }

            .summary .row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 15px;
                padding: 12px;
                background: #f7fafc;
                border-radius: 8px
            }

            .total {
                display: flex;
                justify-content: space-between;
                padding: 20px;
                background: linear-gradient(135deg, #fef5e7, #fdebd0);
                border-radius: 12px;
                font-size: 20px;
                font-weight: 700;
                color: #c05621;
                margin: 20px 0
            }

            .paid {
                color: #22543d;
                font-weight: 700
            }

            .due {
                color: #c05621;
                font-weight: 700
            }

            .payment-card {
                background: linear-gradient(135deg, #ebf8ff, #bee3f8);
                border: 2px solid #4299e1
            }

            .btn-payment {
                display: block;
                text-align: center;
                padding: 15px;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                border-radius: 10px;
                text-decoration: none;
                font-weight: 600
            }

            @media(max-width:1024px) {
                .row {
                    grid-template-columns: 1fr
                }
            }
        </style>
    @endpush
@endsection
