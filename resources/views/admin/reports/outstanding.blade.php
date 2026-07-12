@extends('layouts.admin')

@section('title', 'Outstanding Report')
@section('header-title', 'Outstanding Report')

@section('content')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

<style>
    /* Match Dashboard UI Styles */
    .db-outstanding {
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
    
    .mc-orange::before { background: #ea580c; }
    .mc-blue::before   { background: #2563eb; }
    .mc-teal::before   { background: #0d9488; }
    
    .db-mc-icon {
        width: 30px; height: 30px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        margin-bottom: 8px;
    }
    
    .mc-orange .db-mc-icon { background: #fff7ed; color: #ea580c; }
    .mc-blue   .db-mc-icon { background: #eff6ff; color: #2563eb; }
    .mc-teal   .db-mc-icon { background: #f0fdfa; color: #0d9488; }
    
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

    /* Revenue switch buttons style */
    .rev-btns {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 6px;
        margin-bottom: 14px;
        max-width: 500px;
    }
    .rev-btn {
        padding: 10px;
        border-radius: 7px;
        border: 1.5px solid #e5e7eb;
        background: #f9fafb;
        color: #6b7280;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all .18s;
        text-align: center;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .rev-btn:hover { border-color: #2563eb; color: #2563eb; }
    .rev-btn-active {
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

    .badge-party {
        font-size: 9px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 100px;
        text-transform: uppercase;
    }
    .badge-customer { background: #ede9fe; color: #7c3aed; }
    .badge-dealer { background: #fff7ed; color: #ea580c; }
    .badge-distributor { background: #f0fdfa; color: #0d9488; }
    .badge-vendor { background: #fef2f2; color: #dc2626; }

    @media (max-width: 768px) {
        .db-outstanding {
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
        /* Hide Address column on mobile to fit screen width */
        .db-tbl th:nth-child(5), .db-tbl td:nth-child(5) {
            display: none;
        }
    }
</style>

<div class="db-outstanding">
    <!-- Top Bar -->
    <div class="db-top-bar">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="btn-action-back">← Back to Reports</a>
            <h2 class="db-greeting" style="margin-top: 4px;">Outstanding & Payables</h2>
            <div class="db-date-line">Overview of collections and vendor dues</div>
        </div>
        <div>
            <a href="{{ route('admin.reports.outstanding.pdf') }}?mode=collect" id="btnPdfLink" class="btn-pdf">
                <span>📄</span> Export to PDF
            </a>
        </div>
    </div>

    <!-- Toggle Buttons collect vs pay (Dashboard style) -->
    <div class="rev-btns">
        <button type="button" class="rev-btn rev-btn-active" id="btnModeCollect" onclick="toggleMode('collect')">
            📥 To Collect (Receivable)
        </button>
        <button type="button" class="rev-btn" id="btnModePay" onclick="toggleMode('pay')">
            📤 To Pay (Payable)
        </button>
    </div>

    <!-- Metric Cards (Dashboard style) -->
    <div class="db-metrics">
        <div class="db-mc mc-orange">
            <div class="db-mc-icon">⏳</div>
            <div class="db-mc-label" id="statAmountLabel">Total Outstanding</div>
            <div class="db-mc-value" id="statTotalAmount">₹ {{ number_format($totalToCollect, 2) }}</div>
            <div class="db-mc-sub" id="statAmountSubtext">Pending receivables globally</div>
        </div>
        <div class="db-mc mc-blue">
            <div class="db-mc-icon">👥</div>
            <div class="db-mc-label" id="statAccountsLabel">Debit Accounts</div>
            <div class="db-mc-value" id="statTotalAccounts">{{ count($toCollect) }}</div>
            <div class="db-mc-sub" id="statAccountsSubtext">Parties with non-zero dues</div>
        </div>
        <div class="db-mc mc-teal">
            <div class="db-mc-icon">📈</div>
            <div class="db-mc-label" id="statAvgLabel">Avg Outstanding</div>
            <div class="db-mc-value" id="statAvgAmount">₹ {{ count($toCollect) > 0 ? number_format($totalToCollect / count($toCollect), 2) : '0.00' }}</div>
            <div class="db-mc-sub" id="statAvgSubtext">Average debit balance per head</div>
        </div>
    </div>

    <!-- Main List Panel (Dashboard style) -->
    <div class="db-panel">
        <div class="db-ph">
            <div class="search-wrapper">
                <span style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 11px; color: #9ca3af;">🔍</span>
                <input type="text" id="searchPartyInput" class="search-input" placeholder="Search party name or phone..." onkeyup="filterParties()">
            </div>
            <div class="filter-tabs">
                <button type="button" class="filter-tab-btn active" onclick="filterByType('all')">All</button>
                <button type="button" class="filter-tab-btn" onclick="filterByType('Customer')">Customer</button>
                <button type="button" class="filter-tab-btn" onclick="filterByType('Dealer')">Dealer</button>
                <button type="button" class="filter-tab-btn" onclick="filterByType('Distributor')">Distributor</button>
                <button type="button" class="filter-tab-btn" onclick="filterByType('Vendor')">Vendor</button>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="db-tbl" id="outstandingTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Party Name</th>
                        <th style="text-align: center; width: 140px;">Party Type</th>
                        <th>Phone No</th>
                        <th>Address</th>
                        <th style="text-align: right; width: 180px;" id="thBalanceHeader">Outstanding</th>
                        <th style="text-align: center; width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="outstandingTableBody">
                    <!-- To Collect Rows -->
                    @foreach($toCollect as $i => $party)
                    <tr class="outstanding-row" data-mode="collect" data-type="{{ $party['party_type'] }}">
                        <td class="row-index" style="color: #9ca3af; font-weight: 500;">{{ $i + 1 }}</td>
                        <td style="font-weight: 700; color: #111827;">{{ $party['name'] }}</td>
                        <td style="text-align: center;">
                            <span class="badge-party badge-{{ strtolower($party['party_type']) }}">{{ $party['party_type'] }}</span>
                        </td>
                        <td style="color: #475569; font-weight: 500;">{{ $party['phone'] }}</td>
                        <td style="color: #64748b; font-size: 11px; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $party['address'] }}">{{ $party['address'] }}</td>
                        <td style="text-align: right; font-weight: 800; color: #ea580c; font-size: 13px;">₹ {{ number_format($party['closing_balance'], 2) }}</td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.ledger.show', [strtolower($party['party_type']), $party['id']]) }}" style="text-decoration: none; font-size: 11px; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 4px 8px; border-radius: 6px;" title="View Ledger">
                                LEDGER
                            </a>
                        </td>
                    </tr>
                    @endforeach

                    <!-- To Pay Rows -->
                    @foreach($toPay as $i => $party)
                    <tr class="outstanding-row" data-mode="pay" data-type="{{ $party['party_type'] }}" style="display: none;">
                        <td class="row-index" style="color: #9ca3af; font-weight: 500;">{{ $i + 1 }}</td>
                        <td style="font-weight: 700; color: #111827;">{{ $party['name'] }}</td>
                        <td style="text-align: center;">
                            <span class="badge-party badge-{{ strtolower($party['party_type']) }}">{{ $party['party_type'] }}</span>
                        </td>
                        <td style="color: #475569; font-weight: 500;">{{ $party['phone'] }}</td>
                        <td style="color: #64748b; font-size: 11px; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $party['address'] }}">{{ $party['address'] }}</td>
                        <td style="text-align: right; font-weight: 800; color: #dc2626; font-size: 13px;">₹ {{ number_format($party['closing_balance'], 2) }}</td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.ledger.show', [strtolower($party['party_type']), $party['id']]) }}" style="text-decoration: none; font-size: 11px; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 4px 8px; border-radius: 6px;" title="View Ledger">
                                LEDGER
                            </a>
                        </td>
                    </tr>
                    @endforeach

                    <!-- Empty States -->
                    <tr id="emptyCollectState" style="display: {{ count($toCollect) === 0 ? '' : 'none' }};">
                        <td colspan="7" style="padding: 40px 14px; text-align: center; color: #9ca3af;">
                            <div style="font-size: 28px; margin-bottom: 8px;">🎉</div>
                            <div style="font-weight: 700; color: #111827; font-size: 13px;">No Dues to Collect!</div>
                            <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">All customer accounts are clear.</div>
                        </td>
                    </tr>
                    <tr id="emptyPayState" style="display: none;">
                        <td colspan="7" style="padding: 40px 14px; text-align: center; color: #9ca3af;">
                            <div style="font-size: 28px; margin-bottom: 8px;">🎉</div>
                            <div style="font-weight: 700; color: #111827; font-size: 13px;">No Dues to Pay!</div>
                            <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">We have cleared all liabilities to vendors.</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let activeType = 'all';
    let activeMode = 'collect'; // 'collect' or 'pay'

    const stats = {
        collect: {
            total: '₹ {{ number_format($totalToCollect, 2) }}',
            count: '{{ count($toCollect) }}',
            avg: '₹ {{ count($toCollect) > 0 ? number_format($totalToCollect / count($toCollect), 2) : "0.00" }}',
            label: 'Total Outstanding',
            accountsLabel: 'Debit Accounts',
            avgLabel: 'Avg Outstanding',
            subtext: 'Pending receivables globally',
            accountsSub: 'Parties with non-zero dues',
            avgSub: 'Average debit balance per head',
            header: 'Outstanding'
        },
        pay: {
            total: '₹ {{ number_format($totalToPay, 2) }}',
            count: '{{ count($toPay) }}',
            avg: '₹ {{ count($toPay) > 0 ? number_format($totalToPay / count($toPay), 2) : "0.00" }}',
            label: 'Total Payables',
            accountsLabel: 'Credit Accounts',
            avgLabel: 'Avg Payables',
            subtext: 'Pending dues to be paid',
            accountsSub: 'Parties to whom we owe money',
            avgSub: 'Average credit balance per head',
            header: 'Payable Dues'
        }
    };

    function toggleMode(mode) {
        activeMode = mode;

        // Toggle Button Classes
        if (mode === 'collect') {
            document.getElementById('btnModeCollect').classList.add('rev-btn-active');
            document.getElementById('btnModePay').classList.remove('rev-btn-active');
        } else {
            document.getElementById('btnModePay').classList.add('rev-btn-active');
            document.getElementById('btnModeCollect').classList.remove('rev-btn-active');
        }

        // Update Stats Cards
        document.getElementById('statTotalAmount').innerText = stats[mode].total;
        document.getElementById('statTotalAccounts').innerText = stats[mode].count;
        document.getElementById('statAvgAmount').innerText = stats[mode].avg;

        document.getElementById('statAmountLabel').innerText = stats[mode].label;
        document.getElementById('statAccountsLabel').innerText = stats[mode].accountsLabel;
        document.getElementById('statAvgLabel').innerText = stats[mode].avgLabel;

        document.getElementById('statAmountSubtext').innerText = stats[mode].subtext;
        document.getElementById('statAccountsSubtext').innerText = stats[mode].accountsSub;
        document.getElementById('statAvgSubtext').innerText = stats[mode].avgSub;

        document.getElementById('thBalanceHeader').innerText = stats[mode].header;

        // Update PDF Link
        document.getElementById('btnPdfLink').href = "{{ route('admin.reports.outstanding.pdf') }}?mode=" + mode;

        filterParties();
    }

    function filterParties() {
        const query = document.getElementById('searchPartyInput').value.toLowerCase();
        const tbody = document.getElementById('outstandingTableBody');
        const rows = tbody.getElementsByClassName('outstanding-row');

        let matchCount = 0;

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const rowMode = row.getAttribute('data-mode');
            const rowType = row.getAttribute('data-type');
            
            const name = row.cells[1].textContent.toLowerCase();
            const phone = row.cells[3].textContent.toLowerCase();

            const queryMatches = name.includes(query) || phone.includes(query);
            const typeMatches = activeType === 'all' || rowType === activeType;
            const modeMatches = rowMode === activeMode;

            if (queryMatches && typeMatches && modeMatches) {
                row.style.display = '';
                matchCount++;
                // Recalculate row indices dynamically
                row.getElementsByClassName('row-index')[0].innerText = matchCount;
            } else {
                row.style.display = 'none';
            }
        }

        // Toggle empty states
        if (activeMode === 'collect') {
            document.getElementById('emptyCollectState').style.display = matchCount === 0 ? '' : 'none';
            document.getElementById('emptyPayState').style.display = 'none';
        } else {
            document.getElementById('emptyPayState').style.display = matchCount === 0 ? '' : 'none';
            document.getElementById('emptyCollectState').style.display = 'none';
        }
    }

    function filterByType(type) {
        activeType = type;
        
        // Update active tab styling
        const buttons = document.querySelectorAll('.filter-tab-btn');
        buttons.forEach(btn => {
            if (btn.textContent.trim() === type || (type === 'all' && btn.textContent.trim() === 'All')) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        filterParties();
    }
</script>
@endsection
