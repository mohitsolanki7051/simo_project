@extends('layouts.admin')

@section('title', 'Low Stock Report')
@section('header-title', 'Low Stock Report')

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

    .btn-pdf {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: opacity 0.15s;
    }
    .btn-pdf:hover { opacity: 0.9; }

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
    
    .mc-red::before    { background: #dc2626; }
    .mc-orange::before { background: #ea580c; }
    .mc-blue::before   { background: #2563eb; }
    
    .db-mc-icon {
        width: 30px; height: 30px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        margin-bottom: 8px;
    }
    
    .mc-red .db-mc-icon    { background: #fef2f2; color: #dc2626; }
    .mc-orange .db-mc-icon { background: #fff7ed; color: #ea580c; }
    .mc-blue .db-mc-icon   { background: #eff6ff; color: #2563eb; }
    
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
        flex-wrap: wrap;
        gap: 12px;
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

    .filter-tabs {
        display: flex;
        gap: 4px;
    }
    
    .filter-tab-btn {
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
    }
    .filter-tab-btn:hover { border-color: #2563eb; color: #2563eb; }
    .filter-tab-btn.active {
        background: #2563eb !important;
        color: #fff !important;
        border-color: #2563eb !important;
    }

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

    .badge-status {
        font-size: 9px;
        font-weight: 700;
        padding: 2.5px 7px;
        border-radius: 100px;
        text-transform: uppercase;
    }
    .badge-out-of-stock { background: #fef2f2; color: #dc2626; }
    .badge-critical { background: #fff7ed; color: #ea580c; }
    .badge-low-stock { background: #fefce8; color: #ca8a04; }

    .badge-type {
        font-size: 9.5px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 4px;
        background: #f3f4f6;
        color: #4b5563;
    }

    @media (max-width: 768px) {
        .db-valuation {
            padding: 8px 6px 30px;
        }
        .db-top-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .db-top-bar div:last-child {
            width: 100%;
        }
        .btn-pdf {
            width: 100%;
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
        .filter-tabs {
            justify-content: space-between;
        }
        .filter-tab-btn {
            flex: 1;
            text-align: center;
            padding: 6px 4px;
            font-size: 10px;
        }
        /* Hide SKU, Type and Category on mobile */
        .db-tbl th:nth-child(3), .db-tbl td:nth-child(3),
        .db-tbl th:nth-child(4), .db-tbl td:nth-child(4),
        .db-tbl th:nth-child(5), .db-tbl td:nth-child(5) {
            display: none;
        }
    }
</style>

@php
    $outOfStock = collect($lowStockItems)->filter(fn($item) => $item['stock'] <= 0)->count();
    $critical = collect($lowStockItems)->filter(fn($item) => $item['stock'] > 0 && $item['stock'] <= 5)->count();
    $low = collect($lowStockItems)->filter(fn($item) => $item['stock'] > 5)->count();
@endphp

<div class="db-valuation">
    <!-- Top Bar -->
    <div class="db-top-bar">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="btn-action-back">← Back to Reports</a>
            <h2 class="db-greeting" style="margin-top: 4px;">Low Stock <span>Report</span></h2>
            <div class="db-date-line">Products and variants running below alert limits</div>
        </div>
        <div>
            <a href="{{ route('admin.reports.low-stock.pdf') }}" class="btn-pdf">
                <span>📄</span> Export to PDF
            </a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="db-metrics">
        <div class="db-mc mc-red">
            <div class="db-mc-icon">🚨</div>
            <div class="db-mc-label">Out of Stock</div>
            <div class="db-mc-value">{{ $outOfStock }}</div>
            <div class="db-mc-sub">Items with zero quantity</div>
        </div>
        <div class="db-mc mc-orange">
            <div class="db-mc-icon">⚠️</div>
            <div class="db-mc-label">Critical Stock</div>
            <div class="db-mc-value">{{ $critical }}</div>
            <div class="db-mc-sub">Items below 5 units left</div>
        </div>
        <div class="db-mc mc-blue">
            <div class="db-mc-icon">📦</div>
            <div class="db-mc-label">Low Stock (Alert)</div>
            <div class="db-mc-value">{{ $low }}</div>
            <div class="db-mc-sub">Below customized alerts</div>
        </div>
    </div>

    <!-- Main List Panel -->
    <div class="db-panel">
        <div class="db-ph">
            <div class="search-wrapper">
                <span style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 11px; color: #9ca3af;">🔍</span>
                <input type="text" id="searchValuationInput" class="search-input" placeholder="Search product name or SKU..." onkeyup="filterItems()">
            </div>
            <div class="filter-tabs">
                <button type="button" class="filter-tab-btn active" onclick="filterByStatus('all')">All</button>
                <button type="button" class="filter-tab-btn" onclick="filterByStatus('Out of Stock')">Out of Stock</button>
                <button type="button" class="filter-tab-btn" onclick="filterByStatus('Critical')">Critical</button>
                <button type="button" class="filter-tab-btn" onclick="filterByStatus('Low Stock')">Low Alert</button>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="db-tbl" id="valuationTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Product / Variant Name</th>
                        <th>SKU Code</th>
                        <th style="text-align: center; width: 100px;">Type</th>
                        <th>Category</th>
                        <th style="text-align: right; width: 120px;">Current Stock</th>
                        <th style="text-align: right; width: 120px;">Alert Level</th>
                        <th style="text-align: center; width: 140px;">Status</th>
                    </tr>
                </thead>
                <tbody id="valuationTableBody">
                    @forelse($lowStockItems as $i => $item)
                    <tr class="lowstock-row" data-status="{{ $item['status'] }}">
                        <td class="row-index" style="color: #9ca3af; font-weight: 500;">{{ $i + 1 }}</td>
                        <td style="font-weight: 700; color: #111827;">{{ $item['name'] }}</td>
                        <td style="color: #6b7280; font-family: monospace; font-size: 11px;">{{ $item['sku'] }}</td>
                        <td style="text-align: center;">
                            <span class="badge-type">{{ $item['type'] }}</span>
                        </td>
                        <td style="color: #4b5563;">{{ $item['category'] }}</td>
                        <td style="text-align: right; font-weight: 800; color: {{ $item['stock'] <= 0 ? '#dc2626' : ($item['stock'] <= 5 ? '#ea580c' : '#ca8a04') }};">
                            {{ number_format($item['stock']) }}
                        </td>
                        <td style="text-align: right; color: #6b7280; font-weight: 600;">
                            {{ number_format($item['min_stock_alert']) }}
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-status badge-{{ strtolower(str_replace(' ', '-', $item['status'])) }}">
                                {{ $item['status'] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding: 40px 14px; text-align: center; color: #9ca3af;">
                            <div style="font-size: 28px; margin-bottom: 8px;">🎉</div>
                            <div style="font-weight: 700; color: #111827; font-size: 13px;">Stock Healthy!</div>
                            <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">No products are currently running below alerts.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let activeStatus = 'all';

    function filterItems() {
        const query = document.getElementById('searchValuationInput').value.toLowerCase();
        const tbody = document.getElementById('valuationTableBody');
        const rows = tbody.getElementsByClassName('lowstock-row');

        let visibleCount = 0;

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const name = row.cells[1].textContent.toLowerCase();
            const sku = row.cells[2].textContent.toLowerCase();
            const status = row.getAttribute('data-status');

            const queryMatches = name.includes(query) || sku.includes(query);
            const statusMatches = activeStatus === 'all' || status === activeStatus;

            if (queryMatches && statusMatches) {
                row.style.display = '';
                visibleCount++;
                row.getElementsByClassName('row-index')[0].innerText = visibleCount;
            } else {
                row.style.display = 'none';
            }
        }
    }

    function filterByStatus(status) {
        activeStatus = status;

        // Update active tab buttons
        const buttons = document.querySelectorAll('.filter-tab-btn');
        buttons.forEach(btn => {
            if (btn.textContent.trim() === status || (status === 'all' && btn.textContent.trim() === 'All')) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        filterItems();
    }
</script>
@endsection
