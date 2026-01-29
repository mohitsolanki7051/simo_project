<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Invoice - {{ $purchaseOrder->po_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 14px; line-height: 1.6; color: #333; padding: 40px; }
        .invoice-container { max-width: 900px; margin: 0 auto; background: white; }
        .invoice-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 3px solid #667eea; }
        .company-info h1 { font-size: 32px; color: #667eea; margin-bottom: 5px; }
        .company-info p { font-size: 12px; color: #666; }
        .invoice-meta { text-align: right; }
        .invoice-meta h2 { font-size: 24px; color: #333; margin-bottom: 10px; }
        .invoice-meta p { font-size: 13px; color: #666; margin: 5px 0; }
        .po-number { font-size: 18px; font-weight: bold; color: #667eea; }

        .status-badges { display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px; }
        .badge { padding: 5px 12px; border-radius: 5px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .badge-approved { background: #e6f7ff; color: #1890ff; }
        .badge-pending { background: #fff7e6; color: #fa8c16; }
        .badge-received { background: #f6ffed; color: #52c41a; }
        .badge-draft { background: #f0f0f0; color: #666; }

        .details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; margin-bottom: 40px; }
        .detail-box { background: #f9fafb; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; }
        .detail-box h3 { font-size: 16px; color: #333; margin-bottom: 15px; font-weight: bold; }
        .detail-item { margin: 8px 0; display: flex; }
        .detail-label { font-weight: 600; color: #666; min-width: 140px; }
        .detail-value { color: #333; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table thead { background: #667eea; color: white; }
        .items-table th { padding: 12px; text-align: left; font-weight: 600; font-size: 13px; }
        .items-table td { padding: 12px; border-bottom: 1px solid #e0e0e0; }
        .items-table tbody tr:hover { background: #f9fafb; }
        .items-table tbody tr:last-child td { border-bottom: 2px solid #667eea; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .summary-section { margin-top: 30px; }
        .summary-table { width: 400px; margin-left: auto; }
        .summary-table tr { border-bottom: 1px solid #e0e0e0; }
        .summary-table td { padding: 10px 0; }
        .summary-label { font-weight: 600; color: #666; }
        .summary-value { text-align: right; font-weight: 600; color: #333; }
        .summary-total { font-size: 18px; font-weight: bold; padding-top: 15px !important; border-top: 3px solid #667eea !important; }
        .summary-total td { color: #667eea; }

        .terms-section { margin-top: 40px; padding: 20px; background: #f9fafb; border-radius: 8px; border-left: 4px solid #ffa940; }
        .terms-section h3 { font-size: 16px; margin-bottom: 10px; color: #333; }
        .terms-section p { font-size: 13px; color: #666; white-space: pre-wrap; line-height: 1.8; }

        .footer { margin-top: 50px; padding-top: 20px; border-top: 2px solid #e0e0e0; text-align: center; color: #999; font-size: 12px; }

        @media print {
            body { padding: 20px; }
            .no-print { display: none; }
        }

        .print-buttons { margin-bottom: 20px; display: flex; gap: 10px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: #667eea; color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="print-buttons no-print">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Print Invoice</button>
        <a href="{{ url('/admin/purchase-orders/' . $purchaseOrder->id . '/invoice/download') }}" class="btn btn-primary">📥 Download PDF</a>
        <a href="{{ url('/admin/purchase-orders/' . $purchaseOrder->id) }}" class="btn btn-secondary">← Back to PO</a>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1>YOUR COMPANY</h1>
                <p>123 Business Street, City, State 12345</p>
                <p>Phone: (123) 456-7890 | Email: info@company.com</p>
                <p>GST: 12ABCDE3456F7ZX</p>
            </div>
            <div class="invoice-meta">
                <h2>PURCHASE ORDER</h2>
                <p class="po-number">{{ $purchaseOrder->po_number }}</p>
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($purchaseOrder->po_date)->format('d M Y') }}</p>
                <p><strong>Delivery Date:</strong> {{ \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('d M Y') }}</p>
                @if($purchaseOrder->reference_number)
                <p><strong>Ref:</strong> {{ $purchaseOrder->reference_number }}</p>
                @endif
                <div class="status-badges">
                    @if($purchaseOrder->status === 'approved')
                    <span class="badge badge-approved">Approved</span>
                    @elseif($purchaseOrder->status === 'pending')
                    <span class="badge badge-pending">Pending</span>
                    @elseif($purchaseOrder->status === 'received')
                    <span class="badge badge-received">Received</span>
                    @elseif($purchaseOrder->status === 'draft')
                    <span class="badge badge-draft">Draft</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Supplier & Shipping Details -->
        <div class="details-grid">
            <div class="detail-box">
                <h3>📍 Supplier Details</h3>
                <div class="detail-item">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">{{ $purchaseOrder->supplier_name }}</span>
                </div>
                @if($purchaseOrder->supplier_email)
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $purchaseOrder->supplier_email }}</span>
                </div>
                @endif
                @if($purchaseOrder->supplier_phone)
                <div class="detail-item">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $purchaseOrder->supplier_phone }}</span>
                </div>
                @endif
                @if($purchaseOrder->supplier_address)
                <div class="detail-item">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">{{ $purchaseOrder->supplier_address }}</span>
                </div>
                @endif
            </div>

            <div class="detail-box">
                <h3>🏢 Ship To</h3>
                <div class="detail-item">
                    <span class="detail-label">Warehouse:</span>
                    <span class="detail-value">{{ $purchaseOrder->warehouse->name ?? 'N/A' }}</span>
                </div>
                @if($purchaseOrder->shipping_address)
                <div class="detail-item">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">{{ $purchaseOrder->shipping_address }}</span>
                </div>
                @endif
                <div class="detail-item">
                    <span class="detail-label">Payment Terms:</span>
                    <span class="detail-value">{{ $purchaseOrder->payment_terms }}</span>
                </div>
                @if($purchaseOrder->shipping_method)
                <div class="detail-item">
                    <span class="detail-label">Shipping Method:</span>
                    <span class="detail-value">{{ $purchaseOrder->shipping_method }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>Product Name</th>
                    <th>Variant</th>
                    <th>SKU</th>
                    <th class="text-center">Qty</th>
                    <th>Unit</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-center">GST</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $item['product_name'] }}</strong></td>
                    <td>{{ $item['variant_name'] }}</td>
                    <td>{{ $item['sku_code'] }}</td>
                    <td class="text-center">{{ $item['quantity'] }}</td>
                    <td>{{ $item['unit'] }}</td>
                    <td class="text-right">₹{{ number_format($item['unit_price'], 2) }}</td>
                    <td class="text-center">{{ $item['gst'] }}%</td>
                    <td class="text-right"><strong>₹{{ number_format($item['total'], 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <td class="summary-label">Subtotal:</td>
                    <td class="summary-value">₹{{ number_format($purchaseOrder->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="summary-label">Tax Amount:</td>
                    <td class="summary-value">₹{{ number_format($purchaseOrder->tax_amount, 2) }}</td>
                </tr>
                @if($purchaseOrder->discount_amount > 0)
                <tr>
                    <td class="summary-label">Discount ({{ $purchaseOrder->discount_type }}):</td>
                    <td class="summary-value" style="color: #f56565;">- ₹{{ number_format($purchaseOrder->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($purchaseOrder->shipping_charges > 0)
                <tr>
                    <td class="summary-label">Shipping Charges:</td>
                    <td class="summary-value">₹{{ number_format($purchaseOrder->shipping_charges, 2) }}</td>
                </tr>
                @endif
                @if($purchaseOrder->other_charges > 0)
                <tr>
                    <td class="summary-label">Other Charges:</td>
                    <td class="summary-value">₹{{ number_format($purchaseOrder->other_charges, 2) }}</td>
                </tr>
                @endif
                <tr class="summary-total">
                    <td class="summary-label">GRAND TOTAL:</td>
                    <td class="summary-value">₹{{ number_format($purchaseOrder->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Terms & Conditions -->
        @if($purchaseOrder->terms_conditions)
        <div class="terms-section">
            <h3>📜 Terms & Conditions</h3>
            <p>{{ $purchaseOrder->terms_conditions }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>This is a computer-generated document. No signature is required.</p>
            <p>For any queries, please contact our procurement department.</p>
        </div>
    </div>

    <script>
        // Auto print on page load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
