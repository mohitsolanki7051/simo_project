<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            font-size: 12px;
            color: #111827;
        }

        /* Top Bar */
        .pub-topbar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }

        .pub-topbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pub-topbar-title {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        .pub-topbar-sub {
            font-size: 11px;
            color: #6b7280;
        }

        .pub-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .pub-btn-primary {
            background: #f97316;
            color: white;
        }

        .pub-btn-outline {
            background: white;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .pub-btn-group {
            display: flex;
            gap: 8px;
        }

        /* Quotation Card */
        .pub-wrap {
            max-width: 900px;
            margin: 24px auto;
            padding: 0 16px 40px;
        }

        .pub-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        /* Header */
        .iv-header {
            padding: 20px 25px;
            border-bottom: 2px solid #f97316;
        }

        .iv-company-block {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .iv-logo-img { height: 60px; width: auto; }

        .iv-logo-placeholder {
            width: 60px; height: 60px;
            background: #f97316;
            color: white;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
        }

        .iv-company-details h2 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .iv-company-details p {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.5;
        }

        .iv-company-contact {
            display: flex;
            gap: 20px;
            margin-top: 6px;
            font-size: 11px;
            color: #4b5563;
        }

        /* Title Row */
        .iv-title-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 25px 10px;
        }

        .iv-doc-title {
            font-size: 20px;
            font-weight: 700;
            color: #f97316;
            text-transform: uppercase;
        }

        .iv-quotation-number {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            text-align: right;
        }

        .iv-quotation-date {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Party Grid */
        .iv-party-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 15px 25px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .iv-party-block {
            background: white;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }

        .iv-party-block-title {
            font-size: 12px;
            font-weight: 700;
            color: #f97316;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }

        /* Items Table */
        .iv-items-section { padding: 15px 25px; }

        .iv-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }

        .iv-table thead tr { background: #f97316; color: white; }

        .iv-table thead th {
            padding: 8px 6px;
            font-weight: 600;
            font-size: 10px;
            text-align: center;
            border-right: 1px solid #fb923c;
        }

        .iv-table thead th:last-child { border-right: none; }

        .iv-table tbody tr { border-bottom: 1px solid #e5e7eb; }

        .iv-table tbody td {
            padding: 6px 5px;
            border-right: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
        }

        .iv-table tbody td:last-child { border-right: none; font-weight: 600; }

        .iv-table tfoot tr {
            background: #f3f4f6;
            font-weight: 700;
            border-top: 2px solid #f97316;
        }

        .iv-table tfoot td { padding: 8px 6px; text-align: center; }

        /* Bottom Grid */
        .iv-bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px 25px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        .iv-left-panel, .iv-right-panel {
            background: white;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }

        .iv-panel-title {
            font-size: 12px;
            font-weight: 700;
            color: #f97316;
            margin-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }

        .iv-bank-row {
            display: flex;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .iv-bank-label { width: 90px; color: #6b7280; }
        .iv-bank-value { font-weight: 500; color: #111827; }

        .iv-total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 11px;
            border-bottom: 1px dotted #e5e7eb;
        }

        .iv-total-row.grand {
            border-top: 2px solid #f97316;
            border-bottom: none;
            margin-top: 8px;
            padding-top: 8px;
            font-weight: 700;
            font-size: 13px;
        }

        .iv-total-row.grand .iv-total-value { color: #f97316; }

        .iv-amount-words {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #e5e7eb;
        }

        .iv-words-label { font-size: 11px; color: #6b7280; margin-bottom: 4px; }

        .iv-words-value {
            font-size: 12px;
            font-weight: 600;
            color: #111827;
            text-transform: uppercase;
        }

        .iv-footer {
            padding: 12px 25px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
            background: #f9fafb;
        }

        .iv-notes {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .iv-validity {
            margin-top: 8px;
            padding: 8px;
            background: #fff3e6;
            border-radius: 4px;
            font-size: 10px;
            color: #a05000;
            border: 1px solid #fa8725;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .iv-party-grid { grid-template-columns: 1fr; }
            .iv-bottom-grid { grid-template-columns: 1fr; }
            .pub-topbar { flex-direction: column; gap: 10px; align-items: flex-start; }
            .pub-btn-group { width: 100%; }
            .pub-btn { flex: 1; justify-content: center; }
        }

        /* Print */
        @media print {
            .pub-topbar { display: none !important; }
            body { background: white; }
            .pub-wrap { margin: 0; padding: 0; }
            .pub-card { box-shadow: none; border: none; }
            @page { size: A4; margin: 0.3in; }
            .iv-table thead tr {
                background: #f97316 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

@php
    $party = $quotation->party;
    $showGST = $quotation->invoice_type !== 'cash';

    function pubNumberToWords($num) {
        $num  = (int)$num;
        $ones = [0=>'Zero',1=>'One',2=>'Two',3=>'Three',4=>'Four',5=>'Five',
                 6=>'Six',7=>'Seven',8=>'Eight',9=>'Nine',10=>'Ten',
                 11=>'Eleven',12=>'Twelve',13=>'Thirteen',14=>'Fourteen',
                 15=>'Fifteen',16=>'Sixteen',17=>'Seventeen',18=>'Eighteen',19=>'Nineteen'];
        $tens = [2=>'Twenty',3=>'Thirty',4=>'Forty',5=>'Fifty',
                 6=>'Sixty',7=>'Seventy',8=>'Eighty',9=>'Ninety'];

        if ($num == 0) return 'Zero';
        $words = [];
        if ($num >= 10000000) { $words[] = pubConvert((int)($num/10000000),$ones,$tens).' Crore'; $num %= 10000000; }
        if ($num >= 100000)   { $words[] = pubConvert((int)($num/100000),$ones,$tens).' Lakh';   $num %= 100000; }
        if ($num >= 1000)     { $words[] = pubConvert((int)($num/1000),$ones,$tens).' Thousand'; $num %= 1000; }
        if ($num >= 100)      { $words[] = $ones[(int)($num/100)].' Hundred'; $num %= 100; }
        if ($num > 0) {
            if ($num < 20) { $words[] = $ones[$num]; }
            else { $t=(int)($num/10); $o=$num%10; $words[] = $o>0 ? $tens[$t].' '.$ones[$o] : $tens[$t]; }
        }
        return implode(' ', $words);
    }

    function pubConvert($num, $ones, $tens) {
        if ($num < 20) return $ones[$num];
        $t=(int)($num/10); $o=$num%10;
        return $o>0 ? $tens[$t].' '.$ones[$o] : $tens[$t];
    }
@endphp

<!-- Top Action Bar -->
<div class="pub-topbar">
    <div class="pub-topbar-left">
        <div>
            <div class="pub-topbar-title">
                {{ $quotation->invoice_type === 'cash' ? 'Cash Memo Quotation' : 'GST Quotation' }}
                #{{ $quotation->quotation_number }}
            </div>
            <div class="pub-topbar-sub">
                {{ $party->name ?? '' }} &nbsp;·&nbsp;
                {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d M Y') }}
                @if($quotation->valid_till)
                    &nbsp;·&nbsp; Valid till {{ \Carbon\Carbon::parse($quotation->valid_till)->format('d M Y') }}
                @endif
            </div>
        </div>
    </div>
    <div class="pub-btn-group">
        <button onclick="window.print()" class="pub-btn pub-btn-outline">
            🖨️ Print
        </button>
        <button onclick="downloadPDF()" class="pub-btn pub-btn-primary">
            ⬇️ Download PDF
        </button>
    </div>
</div>

<!-- Quotation -->
<div class="pub-wrap">
    <div class="pub-card" id="quotationToPrint">

        <!-- Company Header -->
        <div class="iv-header">
            <div class="iv-company-block">
                @if($settings && $settings->logo_path)
                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="iv-logo-img">
                @else
                    <div class="iv-logo-placeholder">
                        {{ strtoupper(substr($settings->company_name ?? 'SIM', 0, 2)) }}
                    </div>
                @endif
                <div class="iv-company-details">
                    <h2>{{ $settings?->company_name ?? 'SIMKO ENTERPRISES' }}</h2>
                    <p>{{ $settings?->company_address ?? 'Company Address' }}</p>
                    <div class="iv-company-contact">
                        @if($settings?->company_phone) <span>Phone: {{ $settings?->company_phone }}</span> @endif
                        @if($settings?->company_email) <span>Email: {{ $settings?->company_email }}</span> @endif
                    </div>
                    @if($showGST && ($settings?->gstin || $settings?->pan))
                    <div style="margin-top:5px;font-size:11px;font-weight:500;">
                        @if($settings?->gstin) GSTIN: {{ $settings?->gstin }} @endif
                        @if($settings?->pan) | PAN: {{ $settings?->pan }} @endif
                    </div>
                    @endif
                    @if($quotation->salesman && $quotation->salesman->name)
                    <div style="margin-top:8px;font-size:11px;background:#f3f4f6;padding:4px 8px;border-radius:4px;display:inline-block;">
                        <strong>Sales Executive:</strong> {{ $quotation->salesman->name }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quotation Title -->
        <div class="iv-title-row">
            <div class="iv-doc-title">
                {{ $quotation->invoice_type === 'cash' ? 'CASH MEMO QUOTATION' : 'GST QUOTATION' }}
            </div>
            <div>
                <div class="iv-quotation-number">{{ $quotation->quotation_number }}</div>
                <div class="iv-quotation-date">Date: {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d/m/Y') }}</div>
                @if($quotation->valid_till)
                <div class="iv-quotation-date">Valid Till: {{ \Carbon\Carbon::parse($quotation->valid_till)->format('d/m/Y') }}</div>
                @endif
            </div>
        </div>

        <!-- Party Info -->
        <div class="iv-party-grid">
            <div class="iv-party-block">
                <div class="iv-party-block-title">Bill To</div>
                <div style="font-weight:600;margin-bottom:5px;">{{ $party->name ?? 'N/A' }}</div>
                <div style="font-size:11px;color:#4b5563;margin-bottom:5px;">{{ $quotation->billing_address ?? '' }}</div>
                <div style="font-size:11px;color:#4b5563;">Phone: {{ $party->phone ?? 'N/A' }}</div>
                @if($party?->email)
                <div style="font-size:11px;color:#4b5563;">Email: {{ $party->email }}</div>
                @endif
                @if($showGST && $party?->gst_number)
                <div style="font-size:11px;font-weight:600;margin-top:5px;">GSTIN: {{ $party->gst_number }}</div>
                @endif
            </div>
            <div class="iv-party-block">
                <div class="iv-party-block-title">Ship To</div>
                @if($quotation->shipping_address && $quotation->shipping_address !== $quotation->billing_address)
                    <div style="font-size:11px;color:#4b5563;">{{ $quotation->shipping_address }}</div>
                @else
                    <div style="font-size:11px;color:#4b5563;">Same as billing address</div>
                @endif
                @if($quotation->warehouse)
                <div style="margin-top:8px;font-size:11px;">
                    <strong>Warehouse:</strong> {{ $quotation->warehouse->name ?? 'Main Warehouse' }}
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="iv-items-section">
            <table class="iv-table">
                <thead>
                    <tr>
                        <th width="30">S.No</th>
                        <th>Product</th>
                        @if($showGST)<th>HSN</th>@endif
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>MRP</th>
                        <th>Disc%</th>
                        <th>Rate</th>
                        @if($showGST)<th>Tax%</th>@endif
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmount = 0; @endphp
                    @foreach($quotation->items as $idx => $item)
                    @php
                        $lineTotal = $item->quantity * $item->price;
                        if ($showGST) {
                            $lineTotal += $item->tax_amount ?? 0;
                        }
                        $totalAmount += $lineTotal;

                        // Get product image
                        $productImage = null;
                        if ($item->product_type === 'simple') {
                            $product = \App\Models\SimpleProduct::find($item->product_id);
                            if ($product && $product->base_image) {
                                $productImage = $product->base_image;
                            }
                        } else {
                            $product = \App\Models\VariantProduct::find($item->product_id);
                            if ($product && is_array($product->variants)) {
                                foreach ($product->variants as $variant) {
                                    if ((string)$variant['_id'] === (string)$item->variant_id) {
                                        if (!empty($variant['base_image'])) {
                                            $productImage = $variant['base_image'];
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td style="text-align:left;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                @if($productImage)
                                    <img src="{{ asset('storage/' . $productImage) }}" alt="{{ $item->product_name }}"
                                        style="width: 35px; height: 35px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;">
                                @else
                                    <div style="width: 35px; height: 35px; background: #f3f4f6; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 10px; border: 1px solid #e5e7eb;">
                                        📷
                                    </div>
                                @endif
                                <div>
                                    <strong>{{ $item->product_name }}</strong>
                                    @if($item->variant_name)
                                        <div style="font-size:9px;color:#6b7280;">{{ $item->variant_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @if($showGST)<td>{{ $item->hsn_sac ?: '—' }}</td>@endif
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>₹ {{ number_format($item->mrp_price, 2) }}</td>
                        <td>{{ $item->discount > 0 ? number_format($item->discount, 1).'%' : '—' }}</td>
                        <td>₹ {{ number_format($item->price, 2) }}</td>
                        @if($showGST)<td>{{ number_format($item->tax_percent, 0) }}%</td>@endif
                        <td>₹ {{ number_format($lineTotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="{{ $showGST ? '9' : '7' }}" style="text-align:right;">Total</td>
                        <td><strong>₹ {{ number_format($totalAmount, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Bottom: Notes + Summary -->
        <div class="iv-bottom-grid">
            <div class="iv-left-panel">
                @if($quotation->notes)
                <div class="iv-panel-title">Notes</div>
                <div class="iv-notes">{{ $quotation->notes }}</div>
                @endif

                @if($settings && ($settings->bank_name || $settings->account_number))
                <div class="iv-panel-title" style="margin-top:{{ $quotation->notes ? '15px' : '0' }};">Bank Details</div>
                <div class="iv-bank-details">
                    @if($settings->bank_name)
                    <div class="iv-bank-row"><span class="iv-bank-label">Bank:</span><span class="iv-bank-value">{{ $settings->bank_name }}</span></div>
                    @endif
                    @if($settings->account_number)
                    <div class="iv-bank-row"><span class="iv-bank-label">A/c No.:</span><span class="iv-bank-value">{{ $settings->account_number }}</span></div>
                    @endif
                    @if($settings->ifsc_code)
                    <div class="iv-bank-row"><span class="iv-bank-label">IFSC:</span><span class="iv-bank-value">{{ $settings->ifsc_code }}</span></div>
                    @endif
                    @if($settings->account_name)
                    <div class="iv-bank-row"><span class="iv-bank-label">A/c Name:</span><span class="iv-bank-value">{{ $settings->account_name }}</span></div>
                    @endif
                </div>
                @endif

                @if($quotation->valid_till)
                <div class="iv-panel-title" style="margin-top:15px;">Validity</div>
                <div class="iv-validity">
                    This quotation is valid until <strong>{{ \Carbon\Carbon::parse($quotation->valid_till)->format('d M Y') }}</strong>
                </div>
                @endif

                @if($settings?->terms_and_conditions)
                <div class="iv-panel-title" style="margin-top:15px;">Terms</div>
                <div class="iv-notes">{{ $settings->terms_and_conditions }}</div>
                @endif
            </div>

            <div class="iv-right-panel">
                <div class="iv-panel-title">Quotation Summary</div>

                <div class="iv-total-row">
                    <span>Total MRP</span>
                    <span>₹ {{ number_format($quotation->total_mrp, 2) }}</span>
                </div>

                <div class="iv-total-row">
                    <span>Total Discount</span>
                    <span>- ₹ {{ number_format($quotation->discount_amount, 2) }}</span>
                </div>

                <div class="iv-total-row">
                    <span class="fw-semibold">Subtotal</span>
                    <span class="fw-semibold">₹ {{ number_format($quotation->subtotal, 2) }}</span>
                </div>

                @if($showGST && $quotation->tax_total > 0)
                    <div class="iv-total-row">
                        <span>Tax</span>
                        <span>+ ₹ {{ number_format($quotation->tax_total, 2) }}</span>
                    </div>
                @endif

                @if($quotation->extra_discount > 0)
                <div class="iv-total-row">
                    <span>
                        Extra Discount
                        @if($quotation->extra_discount_type === 'percent')
                            ({{ number_format($quotation->extra_discount, 1) }}%)
                        @endif
                    </span>
                    <span>- ₹ {{ number_format($extraDiscountAmount, 2) }}</span>
                </div>
                @endif

                @if($quotation->extra_charge > 0)
                <div class="iv-total-row">
                    <span>{{ $quotation->charge_name ?? 'Extra Charge' }}</span>
                    <span>+ ₹ {{ number_format($quotation->extra_charge, 2) }}</span>
                </div>
                @endif

                @if($quotation->round_off != 0)
                <div class="iv-total-row">
                    <span>Round Off</span>
                    <span>{{ $quotation->round_off >= 0 ? '+' : '' }} ₹ {{ number_format($quotation->round_off, 2) }}</span>
                </div>
                @endif

                <div class="iv-total-row grand">
                    <span>Grand Total</span>
                    <span>₹ {{ number_format($quotation->grand_total, 2) }}</span>
                </div>

                <!-- Amount in Words -->
                <div class="iv-amount-words">
                    <div class="iv-words-label">Amount In Words</div>
                    <div class="iv-words-value">{{ pubNumberToWords($quotation->grand_total) }} Rupees Only</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="iv-footer">
            {{ $settings?->footer_note ?? 'This is a computer generated quotation.' }}
        </div>

    </div>
</div>

<script>
function downloadPDF() {
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '⏳ Generating...';

    html2pdf().set({
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: 'Quotation_{{ $quotation->quotation_number }}.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    })
    .from(document.getElementById('quotationToPrint'))
    .save()
    .then(() => {
        btn.disabled = false;
        btn.innerHTML = '⬇️ Download PDF';
    });
}
</script>
</body>
</html>
