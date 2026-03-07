{{-- resources/views/admin/quotations/pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
    <style>
        /* Same styles as show view but optimized for PDF */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .qt-pdf-container {
            max-width: 1000px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .qt-header {
            border-bottom: 2px solid #28a745;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .qt-company {
            font-size: 18px;
            font-weight: 700;
            color: #28a745;
        }
        .qt-title-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .qt-doc-title {
            font-size: 16px;
            font-weight: 700;
            color: #28a745;
        }
        .qt-party-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .qt-party-block {
            border: 1px solid #ddd;
            padding: 10px;
        }
        .qt-party-title {
            font-weight: 700;
            color: #28a745;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .qt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .qt-table th {
            background: #28a745;
            color: white;
            padding: 8px;
            text-align: center;
            font-size: 10px;
        }
        .qt-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
            font-size: 10px;
        }
        .qt-table tfoot {
            font-weight: 700;
            background: #f5f5f5;
        }
        .qt-bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .qt-total-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px dotted #ddd;
        }
        .qt-grand-total {
            border-top: 2px solid #28a745;
            padding-top: 8px;
            margin-top: 8px;
            font-weight: 700;
            font-size: 13px;
        }
        .qt-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="qt-pdf-container">
        <div class="qt-header">
            <div class="qt-company">{{ $settings->company_name ?? 'SIMKO ENTERPRISES' }}</div>
            <div>{{ $settings->company_address ?? 'Company Address' }}</div>
            <div>Phone: {{ $settings->company_phone ?? 'N/A' }} | Email: {{ $settings->company_email ?? 'N/A' }}</div>
        </div>

        <div class="qt-title-row">
            <div class="qt-doc-title">QUOTATION / ESTIMATE</div>
            <div class="text-right">
                <div><strong>{{ $quotation->quotation_number }}</strong></div>
                <div>Date: {{ $quotation->quotation_date->format('d/m/Y') }}</div>
                @if($quotation->valid_till)
                <div>Valid Till: {{ $quotation->valid_till->format('d/m/Y') }}</div>
                @endif
            </div>
        </div>

        <div class="qt-party-grid">
            <div class="qt-party-block">
                <div class="qt-party-title">Bill To</div>
                <div><strong>{{ $quotation->party->name ?? 'N/A' }}</strong></div>
                <div>{{ $quotation->party->billing_address ?? 'Address not available' }}</div>
                <div>Phone: {{ $quotation->party->phone ?? 'N/A' }}</div>
                @if($quotation->party->email)
                <div>Email: {{ $quotation->party->email }}</div>
                @endif
            </div>

            <div class="qt-party-block">
                <div class="qt-party-title">Ship To</div>
                @if($quotation->party->shipping_address && $quotation->party->shipping_address !== $quotation->party->billing_address)
                    <div>{{ $quotation->party->shipping_address }}</div>
                @else
                    <div>Same as billing address</div>
                @endif
                <div style="margin-top: 8px;">Party Type: {{ ucfirst($quotation->party->party_type ?? 'customer') }}</div>
            </div>
        </div>

        <table class="qt-table">
            <thead>
                <tr>
                    <th width="30">S.No.</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>MRP</th>
                    <th>Disc%</th>
                    <th>Price</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $totalAmount = 0; @endphp
                @foreach($quotation->items as $idx => $item)
                @php
                    $lineTotal = $item->quantity * $item->price;
                    $totalAmount += $lineTotal;
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="text-left">
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->variant_name)
                            <div style="font-size: 9px;">{{ $item->variant_name }}</div>
                        @endif
                    </td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>₹ {{ number_format($item->mrp_price, 2) }}</td>
                    <td>{{ $item->discount > 0 ? number_format($item->discount, 1) . '%' : '—' }}</td>
                    <td>₹ {{ number_format($item->price, 2) }}</td>
                    <td>₹ {{ number_format($lineTotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" class="text-right">Subtotal</td>
                    <td><strong>₹ {{ number_format($totalAmount, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="qt-bottom-grid">
            <div>
                @if($quotation->notes)
                <div style="margin-bottom: 15px;">
                    <strong>Notes:</strong>
                    <p>{{ $quotation->notes }}</p>
                </div>
                @endif

                @if($settings && $settings->terms_and_conditions)
                <div>
                    <strong>Terms:</strong>
                    <p>{{ $settings->terms_and_conditions }}</p>
                </div>
                @endif
            </div>

            <div>
                <div class="qt-total-row">
                    <span>Subtotal</span>
                    <span>₹ {{ number_format($quotation->subtotal, 2) }}</span>
                </div>

                <div class="qt-total-row">
                    <span>Discount</span>
                    <span>- ₹ {{ number_format($quotation->discount_amount, 2) }}</span>
                </div>

                @if($quotation->extra_discount > 0)
                <div class="qt-total-row">
                    <span>Extra Discount
                        @if($quotation->extra_discount_type === 'percent')
                            ({{ number_format($quotation->extra_discount, 1) }}%)
                        @endif
                    </span>
                    <span>- ₹ {{ number_format($quotation->extra_discount, 2) }}</span>
                </div>
                @endif

                @if($quotation->extra_charge > 0)
                <div class="qt-total-row">
                    <span>{{ $quotation->charge_name ?? 'Extra Charge' }}</span>
                    <span>+ ₹ {{ number_format($quotation->extra_charge, 2) }}</span>
                </div>
                @endif

                @if($quotation->round_off != 0)
                <div class="qt-total-row">
                    <span>Round Off</span>
                    <span>₹ {{ number_format($quotation->round_off, 2) }}</span>
                </div>
                @endif

                <div class="qt-total-row qt-grand-total">
                    <span>Grand Total</span>
                    <span>₹ {{ number_format($quotation->grand_total, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="qt-footer">
            This is a computer generated quotation - valid until {{ $quotation->valid_till ? $quotation->valid_till->format('d/m/Y') : 'mentioned date' }}
        </div>
    </div>
</body>
</html>
