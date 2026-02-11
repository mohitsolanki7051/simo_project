<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .invoice-header h1 {
            margin: 0;
            color: #333;
        }
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .col-6 {
            width: 50%;
            padding: 0 10px;
            box-sizing: border-box;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }
        .details-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f2f2f2;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .items-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px;
            border: none;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .signature {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }
        .signature-space {
            height: 50px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Company Header -->
        <div class="company-info">
            <h2>Your Company Name</h2>
            <p>123 Business Street, City, State - 123456</p>
            <p>Phone: (123) 456-7890 | Email: info@company.com</p>
            <p>GSTIN: 22AAAAA0000A1Z5</p>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <h1>TAX INVOICE</h1>
            <p><strong>Invoice #:</strong> {{ $order->order_number }}</p>
            <p><strong>Invoice Date:</strong> {{ $order->formatted_order_date }}</p>
        </div>

        <!-- Customer and Order Details -->
        <div class="row">
            <div class="col-6">
                <h4>Bill To:</h4>
                <p><strong>{{ $order->customer_name }}</strong></p>
                <p>{{ $order->full_billing_address }}</p>
                <p>Phone: {{ $order->customer_phone }}</p>
                <p>Email: {{ $order->customer_email }}</p>
            </div>
            <div class="col-6">
                <h4>Ship To:</h4>
                <p><strong>{{ $order->customer_name }}</strong></p>
                <p>{{ $order->full_shipping_address }}</p>
                <p>Phone: {{ $order->customer_phone }}</p>
            </div>
        </div>

        <!-- Order Details Table -->
        <table class="details-table">
            <tr>
                <th>Order Status:</th>
                <td>{!! $order->order_status_badge !!}</td>
                <th>Payment Status:</th>
                <td>{!! $order->payment_status_badge !!}</td>
            </tr>
            <tr>
                <th>Payment Method:</th>
                <td>{{ ucfirst($order->payment_method) }}</td>
                <th>Total Items:</th>
                <td>{{ $order->total_items }}</td>
            </tr>
        </table>

        <!-- Order Items Table -->
        <h4>Order Items:</h4>
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>SKU</th>
                    <th>Price (₹)</th>
                    <th>GST (%)</th>
                    <th>Quantity</th>
                    <th>Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['product_name'] }}</td>
                    <td>{{ $item['sku'] }}</td>
                    <td>{{ number_format($item['price'], 2) }}</td>
                    <td>{{ $item['gst'] }}%</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <table class="totals-table" style="width: 300px; float: right;">
            <tr>
                <td class="text-right">Subtotal:</td>
                <td class="text-right">₹{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right">Tax Amount:</td>
                <td class="text-right">₹{{ number_format($order->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right">Shipping Charges:</td>
                <td class="text-right">₹{{ number_format($order->shipping_charges, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right">Discount:</td>
                <td class="text-right">₹{{ number_format($order->discount_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td class="text-right"><strong>Grand Total:</strong></td>
                <td class="text-right"><strong>₹{{ number_format($order->total_amount, 2) }}</strong></td>
            </tr>
        </div>
        <div style="clear: both;"></div>

        <!-- Notes -->
        @if($order->notes)
        <div style="margin-top: 30px;">
            <h4>Notes:</h4>
            <p>{{ $order->notes }}</p>
        </div>
        @endif

        <!-- Terms and Conditions -->
        <div style="margin-top: 30px; font-size: 10px;">
            <h4>Terms & Conditions:</h4>
            <p>1. Goods once sold will not be taken back.</p>
            <p>2. All disputes are subject to jurisdiction of courts.</p>
            <p>3. E. & O.E.</p>
        </div>

        <!-- Signature -->
        <div class="signature">
            <div class="row">
                <div class="col-6">
                    <div class="signature-space"></div>
                    <p>Customer Signature</p>
                </div>
                <div class="col-6">
                    <div class="signature-space"></div>
                    <p>For Your Company Name</p>
                    <p>Authorized Signatory</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is a computer-generated invoice. No signature required.</p>
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>
