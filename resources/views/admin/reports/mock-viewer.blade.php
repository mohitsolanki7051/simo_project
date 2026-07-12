@extends('layouts.admin')

@section('title', $title)
@section('header-title', $title)

@section('content')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

<style>
    /* Match Dashboard UI Styles */
    .db-mock-report {
        font-family: 'Inter', sans-serif;
        background: #f7f8fc;
        min-height: 100vh;
        padding: 4px 12px 40px;
        color: #111827;
        font-size: 13px;
    }
    
    .db-top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .db-greeting {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        letter-spacing: -.2px;
        margin: 0;
    }
    .db-greeting span { color: #2563eb; }
    
    .db-date-line {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 2px;
        font-weight: 500;
    }

    .btn-action-back {
        text-decoration: none;
        color: #2563eb;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .action-buttons {
        display: flex;
        gap: 6px;
    }

    .btn-action {
        padding: 6px 12px;
        border-radius: 7px;
        border: 1.5px solid #e5e7eb;
        background: #f9fafb;
        color: #6b7280;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: all .18s;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-decoration: none;
    }
    .btn-action:hover { border-color: #2563eb; color: #2563eb; }
    .btn-action-primary {
        background: #2563eb !important;
        color: #fff !important;
        border-color: #2563eb !important;
    }

    /* Panel Card layout matching bottom panel of Dashboard */
    .db-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        margin-bottom: 20px;
    }
    
    .db-ph {
        padding: 12px 14px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fafafa;
    }

    .search-wrapper {
        position: relative;
        width: 100%;
        max-width: 320px;
    }
    
    .search-input {
        width: 100%;
        padding: 6px 10px 6px 28px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 12px;
        outline: none;
        font-family: inherit;
        background: #fff;
        color: #111827;
    }
    .search-input:focus { border-color: #2563eb; }

    /* Table styles matching dashboard exactly */
    .db-tbl {
        width: 100%;
        border-collapse: collapse;
    }
    .db-tbl th {
        padding: 9px 14px;
        font-size: 9.5px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #9ca3af;
        font-weight: 700;
        text-align: left;
        background: #fafafa;
        border-bottom: 1px solid #f3f4f6;
    }
    .db-tbl td {
        padding: 10px 14px;
        font-size: 12px;
        color: #374151;
        border-bottom: 1px solid #f9fafb;
    }
    .db-tbl tbody tr {
        transition: background .15s;
    }
    .db-tbl tbody tr:hover td {
        background: #f0f9ff;
    }

    @media print {
        .no-print { display: none !important; }
        .db-top-bar { display: none !important; }
        .db-ph { display: none !important; }
        body { background: #fff; color: #000; }
        .db-mock-report { padding: 0 !important; background: #fff !important; }
    }

    @media (max-width: 768px) {
        .db-mock-report {
            padding: 8px 6px 30px;
        }
        .db-top-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .action-buttons {
            width: 100%;
        }
        .btn-action {
            flex: 1;
            justify-content: center;
        }
        .db-ph {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        .search-wrapper {
            max-width: 100%;
        }
    }
</style>

<div class="db-mock-report">
    <!-- Top Bar -->
    <div class="db-top-bar">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="btn-action-back">← Back to Reports</a>
            <h2 class="db-greeting" style="margin-top: 4px;">{{ $title }} <span>Analysis</span></h2>
            <div class="db-date-line">Detailed reporting table and records log</div>
        </div>
        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn-action">
                <span>🖨️</span> Print
            </button>
            <button onclick="downloadPDF()" class="btn-action btn-action-primary">
                <span>📥</span> PDF Download
            </button>
        </div>
    </div>

    <!-- Main List Panel (Dashboard style) -->
    <div class="db-panel" id="reportToPrint">
        <div class="db-ph no-print">
            <div class="search-wrapper">
                <span style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 11px; color: #9ca3af;">🔍</span>
                <input type="text" id="searchReportInput" class="search-input" placeholder="Quick search report entries..." onkeyup="filterTable()">
            </div>
            <div style="font-size: 11px; color: #9ca3af; font-weight: 600; text-transform: uppercase;">
                {{ count($data) }} Records Found
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="db-tbl" id="reportTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        @foreach($headers as $header)
                        <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="reportTableBody">
                    @forelse($data as $rowIndex => $row)
                    <tr>
                        <td style="color: #9ca3af; font-weight: 500;">{{ $rowIndex + 1 }}</td>
                        @foreach($row as $colIndex => $val)
                        <td style="color: #374151; font-weight: {{ $colIndex === 0 ? '700' : '500' }};">
                            {{ $val }}
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($headers) + 1 }}" style="padding: 40px 14px; text-align: center; color: #9ca3af;">
                            No records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function filterTable() {
        const query = document.getElementById('searchReportInput').value.toLowerCase();
        const tbody = document.getElementById('reportTableBody');
        const rows = tbody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            let rowMatches = false;

            for (let j = 1; j < row.cells.length; j++) {
                const cellText = row.cells[j].textContent.toLowerCase();
                if (cellText.includes(query)) {
                    rowMatches = true;
                    break;
                }
            }

            if (rowMatches) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    function downloadPDF() {
        const element = document.getElementById('reportToPrint');
        const opt = {
            margin: [0.5, 0.5, 0.5, 0.5],
            filename: '{{ Str::slug($title) }}-report.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
@endsection
