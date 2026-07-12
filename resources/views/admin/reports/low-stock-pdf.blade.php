<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Low Stock Statement</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Inter', Arial, sans-serif;
    font-size: 11px;
    color: #1f2937;
    background: #f3f4f6;
    line-height: 1.4;
}

.page-wrapper {
    max-width: 850px;
    margin: 20px auto;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-radius: 4px;
    padding: 30px;
}

/* ── ACTION BAR ── */
.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    padding: 12px 20px;
    border-radius: 6px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
    max-width: 850px;
    margin-left: auto;
    margin-right: auto;
}

.action-bar-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    color: #374151;
    font-size: 11px;
    text-decoration: none;
    cursor: pointer;
    font-weight: 600;
}

.page-title {
    font-size: 13px;
    font-weight: 700;
    color: #111827;
}

.action-bar-right {
    display: flex;
    gap: 8px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    border: none;
}
.btn-primary { background: #2563eb; color: #fff; }
.btn-outline  { background: #fff; border: 1px solid #d1d5db; color: #374151; }

/* ── BUSINESS HEADER ── */
.header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}

.header-table td {
    vertical-align: top;
    padding: 0;
}

.company-logo {
    max-height: 55px;
    margin-bottom: 8px;
    display: block;
}

.company-name {
    font-size: 18px;
    font-weight: 800;
    color: #111827;
    text-transform: uppercase;
}

.company-details {
    font-size: 11px;
    color: #4b5563;
    margin-top: 4px;
    line-height: 1.5;
}

.doc-info {
    text-align: right;
}

.doc-title {
    font-size: 16px;
    font-weight: 800;
    color: #dc2626;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.doc-meta {
    font-size: 11px;
    color: #4b5563;
    margin-top: 6px;
    line-height: 1.5;
}

/* ── SUMMARY BAND ── */
.summary-band {
    display: flex;
    justify-content: space-between;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 12px 18px;
    margin-bottom: 20px;
}

.summary-item {
    font-size: 11px;
}
.summary-label {
    color: #6b7280;
    text-transform: uppercase;
    font-weight: 700;
    font-size: 9px;
    letter-spacing: 0.3px;
}
.summary-value {
    font-size: 14px;
    font-weight: 800;
    color: #111827;
    margin-top: 2px;
}

/* ── DATA TABLE ── */
.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.data-table th {
    background: #f3f4f6;
    border-bottom: 1.5px solid #d1d5db;
    padding: 8px 10px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    color: #374151;
    text-align: left;
}

.data-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #e5e7eb;
    color: #374151;
}

.data-table tbody tr:nth-child(even) {
    background: #f9fafb;
}

.data-table th.r, .data-table td.r { text-align: right; }
.data-table th.c, .data-table td.c { text-align: center; }

.data-table tfoot td {
    padding: 10px 10px;
    font-weight: 700;
    font-size: 11px;
    color: #111827;
    border-top: 2px solid #374151;
    border-bottom: 2px solid #374151;
    background: #f9fafb;
}

.badge-status {
    font-size: 8px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 100px;
    text-transform: uppercase;
}
.badge-out-of-stock { background: #fef2f2; color: #dc2626; }
.badge-critical { background: #fff7ed; color: #ea580c; }
.badge-low-stock { background: #fefce8; color: #ca8a04; }

.badge-type {
    font-size: 9px;
    font-weight: 600;
    padding: 1px 4px;
    border-radius: 4px;
    background: #e5e7eb;
    color: #374151;
}

/* ── PRINT ── */
@media print {
    @page { margin: 0.5in; size: A4 portrait; }
    html, body { margin: 0 !important; padding: 0 !important; }
    body { background: #fff; font-size: 10px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .action-bar { display: none !important; }
    .page-wrapper { margin: 0; box-shadow: none; border-radius: 0; max-width: 100%; padding: 0; }
}
</style>
</head>
<body>

{{-- ACTION BAR --}}
<div class="action-bar no-print">
    <div class="action-bar-left">
        <a href="{{ route('admin.reports.low-stock') }}" class="back-btn">&#8592; Back</a>
        <span class="page-title">Low Stock Statement</span>
    </div>
    <div class="action-bar-right">
        <button onclick="printPDF()" class="btn btn-outline">&#128438; Print</button>
        <button onclick="downloadPDF()" class="btn btn-primary">&#8659; Download PDF</button>
    </div>
</div>

<div class="page-wrapper" id="reportToPrint">

    <!-- UPPER BUSINESS DETAILS HEADER -->
    <table class="header-table">
        <tr>
            <td>
                @if($settings && $settings->logo_path)
                    <img src="{{ $settings->logo_url }}" class="company-logo" alt="Logo">
                @endif
                <div class="company-name">{{ $settings->company_name ?? config('app.name', 'SIMKO') }}</div>
                <div class="company-details">
                    @if($settings)
                        {{ $settings->company_address }}<br>
                        Phone: {{ $settings->company_phone }} &bull; Email: {{ $settings->company_email }}<br>
                        @if($settings->gstin) GSTIN: {{ $settings->gstin }} &bull; @endif
                        @if($settings->pan) PAN: {{ $settings->pan }} @endif
                    @else
                        Default Company Address details
                    @endif
                </div>
            </td>
            <td class="doc-info">
                <div class="doc-title">Low Stock Report</div>
                <div class="doc-meta">
                    <strong>As on Date:</strong> {{ now()->format('d M Y') }}<br>
                    <strong>Generated on:</strong> {{ now()->format('h:i A') }}<br>
                    <strong>Status:</strong> Restock Alert
                </div>
            </td>
        </tr>
    </table>

    @php
        $outOfStock = collect($lowStockItems)->filter(fn($item) => $item['stock'] <= 0)->count();
        $critical = collect($lowStockItems)->filter(fn($item) => $item['stock'] > 0 && $item['stock'] <= 5)->count();
        $totalLow = count($lowStockItems);
    @endphp

    <!-- SUMMARY BAND -->
    <div class="summary-band">
        <div class="summary-item">
            <div class="summary-label">Out of Stock</div>
            <div class="summary-value" style="color: #dc2626;">{{ $outOfStock }} Items</div>
        </div>
        <div class="summary-item" style="text-align: center;">
            <div class="summary-label">Critical Stock</div>
            <div class="summary-value" style="color: #ea580c;">{{ $critical }} Items</div>
        </div>
        <div class="summary-item" style="text-align: right;">
            <div class="summary-label">Total Low Alert Items</div>
            <div class="summary-value" style="color: #2563eb;">{{ $totalLow }} Items</div>
        </div>
    </div>

    <!-- TABLE SECTION -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">Product / Variant Name</th>
                <th style="width: 15%;">SKU Code</th>
                <th class="c" style="width: 10%;">Type</th>
                <th style="width: 15%;">Category</th>
                <th class="r" style="width: 12%;">Alert Level</th>
                <th class="r" style="width: 13%;">Current Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lowStockItems as $i => $item)
            <tr>
                <td style="color:#6b7280; font-size:10px;">{{ $i + 1 }}</td>
                <td style="font-weight: 700; color: #111827;">{{ $item['name'] }}</td>
                <td style="font-family: monospace; font-size: 10px;">{{ $item['sku'] }}</td>
                <td class="c">
                    <span class="badge-type">{{ $item['type'] }}</span>
                </td>
                <td style="color: #4b5563;">{{ $item['category'] }}</td>
                <td class="r" style="font-weight: 600; color:#6b7280;">{{ number_format($item['min_stock_alert']) }}</td>
                <td class="r" style="font-weight: 800; color: {{ $item['stock'] <= 0 ? '#dc2626' : ($item['stock'] <= 5 ? '#ea580c' : '#ca8a04') }};">
                    {{ number_format($item['stock']) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function printPDF() {
    const btn = event ? event.target.closest('button') : null;
    if (btn) { btn.disabled = true; btn.innerHTML = 'Preparing...'; }

    const element = document.getElementById('reportToPrint');
    html2pdf().set({
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: 'low-stock-statement-{{ now()->format("Y-m-d") }}.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, logging: false, removeContainer: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait', putOnlyUsedFonts: true }
    }).from(element).outputPdf('bloburl').then((url) => {
        if (btn) { btn.disabled = false; btn.innerHTML = '&#128438; Print'; }
        const win = window.open(url, '_blank');
        if (win) win.onload = () => win.print();
    });
}

function downloadPDF() {
    const btn = event ? event.target.closest('button') : null;
    if (btn) { btn.disabled = true; btn.innerHTML = 'Generating...'; }

    const element = document.getElementById('reportToPrint');
    html2pdf().set({
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: 'low-stock-statement-{{ now()->format("Y-m-d") }}.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, logging: false, removeContainer: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait', putOnlyUsedFonts: true }
    }).from(element).save().then(() => {
        if (btn) { btn.disabled = false; btn.innerHTML = '&#8659; Download PDF'; }
    });
}
</script>
</body>
</html>
