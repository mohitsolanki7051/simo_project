@extends('layouts.admin')

@section('title', 'Stock Valuation Report')
@section('header-title', 'Stock Valuation Report')

@section('content')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

<style>
    /* Match Dashboard UI Styles */
    .db-valuation {
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

    .date-picker-input {
        padding: 6px 12px;
        border-radius: 7px;
        border: 1.5px solid #e5e7eb;
        background: #f9fafb;
        color: #374151;
        font-size: 11px;
        font-weight: 700;
        outline: none;
        cursor: pointer;
    }
    .date-picker-input:focus { border-color: #2563eb; }

    /* Stats Grid from Dashboard */
    .db-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }
    
    .db-mc {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px 14px 10px;
        position: relative;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    
    .db-mc::before {
        content: '';
        position: absolute;
        left: 0; top: 10px; bottom: 10px;
        width: 3px;
        border-radius: 0 2px 2px 0;
    }
    
    .mc-teal::before   { background: #0d9488; }
    .mc-blue::before   { background: #2563eb; }
    .mc-violet::before { background: #7c3aed; }
    
    .db-mc-icon {
        width: 30px; height: 30px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        margin-bottom: 8px;
    }
    
    .mc-teal   .db-mc-icon { background: #f0fdfa; color: #0d9488; }
    .mc-blue   .db-mc-icon { background: #eff6ff; color: #2563eb; }
    .mc-violet .db-mc-icon { background: #f5f3ff; color: #7c3aed; }
    
    .db-mc-label {
        font-size: 9.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #9ca3af;
        margin-bottom: 3px;
    }
    
    .db-mc-value {
        font-size: 20px;
        font-weight: 800;
        color: #111827;
        letter-spacing: -.4px;
        line-height: 1;
    }
    
    .db-mc-sub {
        font-size: 10px;
        color: #9ca3af;
        margin-top: 4px;
        font-weight: 500;
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
    .db-tbl tfoot tr td {
        background: #fdfdfd;
        border-top: 1.5px solid #e5e7eb;
        font-size: 13px;
        font-weight: 800;
        color: #111827;
    }

    .badge-type {
        font-size: 9px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 100px;
        text-transform: uppercase;
    }
    .badge-simple { background: #eff6ff; color: #2563eb; }
    .badge-variant { background: #f5f3ff; color: #7c3aed; }

    @media (max-width: 768px) {
        .db-valuation {
            padding: 8px 6px 30px;
        }
        .db-top-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .db-top-bar form {
            width: 100%;
            justify-content: space-between;
        }
        .date-picker-input {
            flex: 1;
            max-width: 200px;
        }
        .db-ph {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        .search-wrapper {
            max-width: 100%;
        }
        /* Hide SKU and Type on mobile */
        .db-tbl th:nth-child(3), .db-tbl td:nth-child(3),
        .db-tbl th:nth-child(4), .db-tbl td:nth-child(4) {
            display: none;
        }
    }
</style>

<div class="db-valuation">
    <!-- Top Bar -->
    <div class="db-top-bar">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="btn-action-back">← Back to Reports</a>
            <h2 class="db-greeting" style="margin-top: 4px;">Stock <span>Valuation</span></h2>
            <div class="db-date-line">Retroactive inventory cost calculation and breakdown</div>
        </div>
        <div>
            <form action="{{ route('admin.reports.stock-valuation') }}" method="GET" id="dateFilterForm" style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Target Date:</span>
                <input type="date" name="date" class="date-picker-input" value="{{ $targetDate ?? date('Y-m-d') }}" max="{{ date('Y-m-d') }}" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    <!-- Metric Cards (Dashboard style) -->
    <div class="db-metrics">
        <div class="db-mc mc-teal">
            <div class="db-mc-icon">📊</div>
            <div class="db-mc-label">Total Valuation (Cost)</div>
            <div class="db-mc-value">₹ {{ number_format($totalCost, 2) }}</div>
            <div class="db-mc-sub">Asset stock value on target date</div>
        </div>
        <div class="db-mc mc-blue">
            <div class="db-mc-icon">📦</div>
            <div class="db-mc-label">Total Stock Qty</div>
            <div class="db-mc-value">{{ number_format(collect($products)->sum('stock')) }}</div>
            <div class="db-mc-sub">Physical quantity in warehouse</div>
        </div>
        <div class="db-mc mc-violet">
            <div class="db-mc-icon">🏷️</div>
            <div class="db-mc-label">Avg Item Cost</div>
            <div class="db-mc-value">₹ {{ count($products) > 0 ? number_format(collect($products)->avg('cost_price'), 2) : '0.00' }}</div>
            <div class="db-mc-sub">Average unit purchase cost</div>
        </div>
    </div>

    <!-- Main List Panel (Dashboard style) -->
    <div class="db-panel">
        <div class="db-ph">
            <div class="search-wrapper">
                <span style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 11px; color: #9ca3af;">🔍</span>
                <input type="text" id="searchProductInput" class="search-input" placeholder="Search product name or SKU..." onkeyup="filterProducts()">
            </div>
            <div style="font-size: 11px; color: #9ca3af; font-weight: 600; text-transform: uppercase;">
                {{ count($products) }} Items Listed
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="db-tbl" id="valuationTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Product / Variant Name</th>
                        <th style="width: 180px;">SKU Code</th>
                        <th style="text-align: center; width: 100px;">Type</th>
                        <th style="text-align: right; width: 140px;">Stock Qty</th>
                        <th style="text-align: right; width: 160px;">Cost Price</th>
                        <th style="text-align: right; width: 180px;">Total Value</th>
                    </tr>
                </thead>
                <tbody id="valuationTableBody">
                    @forelse($products as $i => $item)
                    <tr>
                        <td style="color: #9ca3af; font-weight: 500;">{{ $i + 1 }}</td>
                        <td style="font-weight: 700; color: #111827;">{{ $item['name'] }}</td>
                        <td style="color: #475569; font-family: monospace; font-size: 11px;">{{ $item['sku'] }}</td>
                        <td style="text-align: center;">
                            <span class="badge-type badge-{{ strtolower($item['type']) }}">{{ $item['type'] }}</span>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #374151;">
                            {{ $item['stock'] }} <span style="font-size: 10px; color: #9ca3af; font-weight: 500;">{{ $item['unit'] }}</span>
                        </td>
                        <td style="text-align: right; color: #475569; font-weight: 500;">₹ {{ number_format($item['cost_price'], 2) }}</td>
                        <td style="text-align: right; font-weight: 800; color: #0d9488;">₹ {{ number_format($item['total_cost'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 40px 14px; text-align: center; color: #9ca3af;">
                            <div style="font-size: 28px; margin-bottom: 8px;">📦</div>
                            <div style="font-weight: 700; color: #111827; font-size: 13px;">No Inventory Found</div>
                            <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">Ensure warehouse movements exist before the target date.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($products) > 0)
                <tfoot>
                    <tr>
                        <td colspan="4" style="padding: 12px 14px;">TOTAL SUMMARY</td>
                        <td style="text-align: right; padding: 12px 14px;">{{ number_format(collect($products)->sum('stock')) }}</td>
                        <td></td>
                        <td style="text-align: right; color: #0d9488; padding: 12px 14px;">₹ {{ number_format($totalCost, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<script>
    function filterProducts() {
        const query = document.getElementById('searchProductInput').value.toLowerCase();
        const tbody = document.getElementById('valuationTableBody');
        const rows = tbody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            if (row.cells.length < 2) continue; // Skip header / footer

            const name = row.cells[1].textContent.toLowerCase();
            const sku = row.cells[2].textContent.toLowerCase();

            if (name.includes(query) || sku.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }
</script>
@endsection
