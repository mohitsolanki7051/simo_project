@extends('layouts.admin')

@section('title', ucfirst($partyType) . 's - Admin Panel')
@section('header-title', ucfirst($partyType) . ' Management')

@section('content')
@php
    // Batch-fetch ledger closing balances for every party on this page in one
    // go, instead of calling it per-row in the table (avoids N+1-style cost).
    // Positive = Dr (party owes the business), Negative = Cr (business owes
    // the party) — same convention as the full ledger statement.
    $partyIdsForBalance = $parties->pluck('id')->map(fn($id) => (string) $id)->toArray();
    $closingBalances = \App\Http\Controllers\Admin\LedgerController::getClosingBalancesBatch($partyType, $partyIdsForBalance);
@endphp
<div class="parties-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>
    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">{{ ucfirst($partyType) }} List</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.parties.create', ['type' => $partyType]) }}" class="btn-small btn-primary">
                <span class="btn-icon">+</span> Add {{ ucfirst($partyType) }}
            </a>
        </div>
    </div>

    <!-- Report Summary Cards -->
    <div class="report-cards">
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">👥</div>
            <div class="card-info">
                <div class="card-title">Total {{ ucfirst($partyType) }}s</div>
                <div class="card-value" id="totalParties">{{ $parties->count() }}</div>
                <div class="card-desc">{{ $parties->where('status', 'active')->count() }} active, {{ $parties->where('status', 'inactive')->count() }} inactive</div>
            </div>
        </div>
        @if(in_array($partyType, ['dealer', 'customer', 'distributor','vendors']))
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">💰</div>
            <div class="card-info">
                <div class="card-title">Total Closing Balance</div>
                @php
                    $totalClosingBalance = array_sum($closingBalances);
                    $totalDr = array_sum(array_filter($closingBalances, fn($b) => $b > 0));
                    $totalCr = array_sum(array_filter($closingBalances, fn($b) => $b < 0));
                @endphp
                <div class="card-value">
                    ₹{{ number_format(abs($totalClosingBalance), 2) }}
                    @if($totalClosingBalance != 0)
                        <span class="drcr-badge" style="font-size: 12px; font-weight: 700; margin-left: 4px; padding: 2px 6px; border-radius: 4px; background: #f3f4f6; color: {{ $totalClosingBalance > 0 ? '#b91c1c' : '#15803d' }};">{{ $totalClosingBalance > 0 ? 'Dr' : 'Cr' }}</span>
                    @endif
                </div>
                <div class="card-desc">
                    Dr: ₹{{ number_format($totalDr, 2) }} | Cr: ₹{{ number_format(abs($totalCr), 2) }}
                </div>
            </div>
        </div>
        @else
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">💰</div>
            <div class="card-info">
                <div class="card-title">Total Credit Limit</div>
                <div class="card-value">₹{{ number_format($parties->sum('credit_limit'), 2) }}</div>
                <div class="card-desc">Avg: ₹{{ number_format($parties->avg('credit_limit') ?? 0, 2) }}</div>
            </div>
        </div>
        @endif
        @if($partyType === 'dealer')
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">🏢</div>
            <div class="card-info">
                <div class="card-title">Under Distributors</div>
                <div class="card-value">{{ $parties->whereNotNull('parent_party_id')->count() }}</div>
                <div class="card-desc">{{ $parties->whereNull('parent_party_id')->count() }} independent</div>
            </div>
        </div>
        @endif
    </div>

    <!-- Filters and Search -->
    <div class="table-filters">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" placeholder="Search {{ $partyType }}s..." id="searchInput">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All ({{ $parties->count() }})</button>
            <button class="filter-btn" data-filter="active">Active ({{ $parties->where('status', 'active')->count() }})</button>
            <button class="filter-btn" data-filter="inactive">Inactive ({{ $parties->where('status', 'inactive')->count() }})</button>
        </div>
        <div class="bulk-actions">
            <select class="bulk-select" id="bulkActionSelect" disabled>
                <option value="">Bulk Actions</option>
                <option value="active">Set Active</option>
                <option value="inactive">Set Inactive</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button class="btn-bulk" id="applyBulkAction" disabled>Apply</button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         DESKTOP TABLE  (hidden below 768px)
    ══════════════════════════════════════════════ -->
    <div class="table-wrapper ldgp-desktop-only">
        <table class="compact-table">
            <thead>
                <tr>
                    <th class="th-checkbox"><input type="checkbox" id="selectAll"></th>
                    <th class="th-sno">S.No.</th>
                    <th class="th-name">{{ ucfirst($partyType) }} Name</th>
                    <th class="th-contact">Contact Info</th>
                    <th class="th-finance">Total Balance</th>
                    <th class="th-finance">Credit Limit</th>
                    <th class="th-salesman">Salesman</th>
                    @if($partyType === 'dealer')
                    <th class="th-parent">Distributor</th>
                    @endif
                    <th class="th-status">Status</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parties as $index => $party)
                @php
                    $closingBalance = $closingBalances[(string) $party->id] ?? 0.0;
                @endphp
                <tr class="table-row"
                    data-status="{{ $party->status }}"
                    data-party-id="{{ $party->id }}">
                    <td class="td-checkbox">
                        <input type="checkbox" class="row-checkbox" value="{{ $party->id }}">
                    </td>
                    <td class="td-sno">{{ $index + 1 }}</td>
                    <td class="td-name">
                        <div class="party-name">{{ $party->name }}</div>
                        @if($party->gst_number)
                            <div class="party-gst">GST: {{ $party->gst_number }}</div>
                        @endif
                    </td>
                    <td class="td-contact">
                        <div class="contact-info">
                            <div class="contact-phone">📱 {{ $party->phone }}</div>
                            @if($party->email)
                                <div class="contact-email">✉️ {{ $party->email }}</div>
                            @endif
                        </div>
                    </td>
                    <td class="td-finance">
                        <span class="finance-value {{ $closingBalance > 0 ? 'val-dr' : ($closingBalance < 0 ? 'val-cr' : '') }}">
                            ₹{{ number_format(abs($closingBalance), 2) }}
                            @if($closingBalance != 0)
                                <span class="drcr-badge">{{ $closingBalance > 0 ? 'Dr' : 'Cr' }}</span>
                            @endif
                        </span>
                    </td>
                    <td class="td-finance">
                        <span class="finance-value">₹{{ number_format($party->credit_limit ?? 0, 2) }}</span>
                    </td>

                    <td class="td-salesman">
                        @if($party->salesman_id)
                            @php
                                $salesman = \App\Models\Salesman::find($party->salesman_id);
                            @endphp
                            @if($salesman)
                                <div class="salesman-info">
                                    <span class="salesman-name">{{ $salesman->name }}</span>
                                    @if($salesman->commission_enabled)
                                        <span class="salesman-commission">(Commission: Yes)</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        @else
                            <span class="text-muted">Not Assigned</span>
                        @endif
                    </td>

                    @if($partyType === 'dealer')
                    <td class="td-parent">
                        @if($party->parent_party_id)
                            @php
                                $parent = \App\Models\Customer::find($party->parent_party_id);
                            @endphp
                            @if($parent)
                                <span class="parent-name">{{ $parent->name }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    @endif

                    <td class="td-status">
                        <span class="status-badge status-{{ $party->status }}">
                            {{ ucfirst($party->status) }}
                        </span>
                    </td>
                    <td class="td-actions">
                        <div class="action-icons">
                            <a href="{{ route('admin.ledger.show', [$party->party_type, $party->id]) }}"
                                class="icon-btn icon-ledger" title="View Ledger">📒</a>
                            <a href="{{ route('admin.parties.edit', $party->id) }}"
                               class="icon-btn icon-edit" title="Edit">
                                ✏️
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $partyType === 'dealer' ? 10 : 9 }}" class="empty-state">
                        <div class="empty-content">
                            <div class="empty-icon">👥</div>
                            <h4>No {{ ucfirst($partyType) }}s Found</h4>
                            <p>Start by adding your first {{ $partyType }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- ══════════════════════════════════════════════
         MOBILE CARD LAYOUT  (shown below 768px)
    ══════════════════════════════════════════════ -->
    <div class="ldgp-mobile-only mobile-party-cards">
        @forelse($parties as $index => $party)
        @php
            $closingBalance = $closingBalances[(string) $party->id] ?? 0.0;
            $salesman = $party->salesman_id ? \App\Models\Salesman::find($party->salesman_id) : null;
            $parent   = ($partyType === 'dealer' && $party->parent_party_id) ? \App\Models\Customer::find($party->parent_party_id) : null;
        @endphp
        <div class="mp-card" data-status="{{ $party->status }}" data-party-id="{{ $party->id }}">
            <div class="mp-card-top">
                <div class="mp-card-identity">
                    <div class="mp-name">
                        <input type="checkbox" class="row-checkbox mp-checkbox" value="{{ $party->id }}">
                        {{ $party->name }}
                    </div>
                    @if($party->gst_number)
                        <div class="mp-gst">GST: {{ $party->gst_number }}</div>
                    @endif
                </div>
                <span class="status-badge status-{{ $party->status }}">{{ ucfirst($party->status) }}</span>
            </div>

            <div class="mp-card-balance-row">
                <div class="mp-balance-block">
                    <span class="mp-balance-label">Total Balance</span>
                    <span class="mp-balance-val {{ $closingBalance > 0 ? 'val-dr' : ($closingBalance < 0 ? 'val-cr' : '') }}">
                        ₹{{ number_format(abs($closingBalance), 2) }}
                        @if($closingBalance != 0)
                            <span class="drcr-badge">{{ $closingBalance > 0 ? 'Dr' : 'Cr' }}</span>
                        @endif
                    </span>
                </div>
                <div class="mp-balance-block">
                    <span class="mp-balance-label">Credit Limit</span>
                    <span class="mp-balance-val">₹{{ number_format($party->credit_limit ?? 0, 2) }}</span>
                </div>
            </div>

            <div class="mp-card-contact">
                <span class="mp-contact-chip">📱 {{ $party->phone }}</span>
                @if($party->email)
                    <span class="mp-contact-chip">✉️ {{ $party->email }}</span>
                @endif
            </div>

            @if($salesman || $parent)
            <div class="mp-card-meta">
                @if($salesman)
                    <span class="mp-meta-item">👤 {{ $salesman->name }}@if($salesman->commission_enabled) <em>(Comm)</em>@endif</span>
                @endif
                @if($parent)
                    <span class="mp-meta-item">🏢 {{ $parent->name }}</span>
                @endif
            </div>
            @endif

            <div class="mp-card-actions">
                <a href="{{ route('admin.ledger.show', [$party->party_type, $party->id]) }}" class="mp-action-btn ledger-btn">
                    📒 Ledger
                </a>
                <a href="{{ route('admin.parties.edit', $party->id) }}" class="mp-action-btn edit-btn">
                    ✏️ Edit
                </a>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-content">
                <div class="empty-icon">👥</div>
                <h4>No {{ ucfirst($partyType) }}s Found</h4>
                <p>Start by adding your first {{ $partyType }}</p>
            </div>
        </div>
        @endforelse
    </div>

    @if($parties->count() > 0)
    <div class="table-footer">
        <div class="footer-info">
            Showing {{ $parties->count() }} {{ $partyType }}{{ $parties->count() > 1 ? 's' : '' }}
        </div>
    </div>
    @endif
</div>

<!-- Bulk Action Modal -->
<div class="modal" id="bulkActionModal">
    <div class="modal-overlay" onclick="closeBulkActionModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon" style="background: #f59e0b;">⚠️</div>
            <h4 class="modal-title" id="bulkModalTitle"></h4>
            <button class="modal-close" onclick="closeBulkActionModal()">×</button>
        </div>
        <p class="modal-text" id="bulkModalText"></p>
        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeBulkActionModal()">Cancel</button>
            <button type="button" class="btn-modal btn-confirm" id="confirmBulkAction">Confirm</button>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Main Container */
    .parties-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 12px;
        line-height: 1.4;
    }

    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-title {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }

    .btn-small {
        padding: 6px 12px;
        background: #fa8427;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-small:hover {
        background: #e97317;
        transform: translateY(-1px);
    }

    .btn-icon { font-size: 12px; }

    /* Report Cards */
    .report-cards {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }

    .report-card {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.2s;
        cursor: default;
        min-width: 300px;
    }

    .report-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        transform: translateY(-2px);
    }

    .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
    }

    .card-info { flex: 1; }
    .card-title { font-size: 12px; color: #6b7280; font-weight: 500; margin-bottom: 4px; }
    .card-value { font-size: 20px; font-weight: 700; color: #1f2937; margin-bottom: 2px; }
    .card-desc { font-size: 11px; color: #9ca3af; }

    /* Filters */
    .table-filters {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box { flex: 1; max-width: 200px; position: relative; }
    .search-icon { position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #718096; }
    .search-input { width: 100%; padding: 6px 8px 6px 24px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; background: #f9fafb; }
    .search-input:focus { outline: none; border-color: #667eea; background: white; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }

    .filter-buttons { display: flex; gap: 4px; }
    .filter-btn { padding: 5px 10px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 10px; color: #4b5563; cursor: pointer; transition: all 0.2s; }
    .filter-btn:hover { background: #e5e7eb; }
    .filter-btn.active { background: #667eea; border-color: #667eea; color: white; }

    /* Bulk Actions */
    .bulk-actions { display: flex; gap: 4px; align-items: center; margin-left: auto; }
    .bulk-select { padding: 5px 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; background: #f9fafb; min-width: 120px; cursor: pointer; }
    .bulk-select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
    .btn-bulk { padding: 5px 12px; background: #48bb78; color: white; border: none; border-radius: 4px; font-size: 11px; cursor: pointer; transition: all 0.2s; }
    .btn-bulk:hover:not(:disabled) { background: #38a169; transform: translateY(-1px); }
    .btn-bulk:disabled { opacity: 0.5; cursor: not-allowed; }

    /* Desktop / Mobile toggles */
    .ldgp-desktop-only { display: block !important; }
    .ldgp-mobile-only  { display: none !important; }

    /* Compact Table */
    .table-wrapper { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 6px; background: white; margin-top: 10px; }
    .compact-table { width: 100%; border-collapse: collapse; font-size: 11px; }
    .compact-table th { background: #f8fafc; padding: 8px 10px; text-align: left; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
    .compact-table td { padding: 8px 10px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .compact-table tr:last-child td { border-bottom: none; }
    .compact-table tr:hover { background: #f9fafb; }

    .th-checkbox { width: 30px; }
    .th-sno { width: 50px; }
    .th-name { width: 180px; }
    .th-contact { width: 150px; }
    .th-finance { width: 110px; }
    .th-salesman { width: 120px; }
    .th-parent { width: 120px; }
    .th-status { width: 70px; }
    .th-actions { width: 90px; }

    input[type="checkbox"] { width: 14px; height: 14px; accent-color: #667eea; cursor: pointer; }

    .td-sno { font-size: 11px; color: #6b7280; font-weight: 500; text-align: center; }
    .party-name { font-weight: 600; color: #1f2937; line-height: 1.3; font-size: 12px; }
    .party-gst { font-size: 10px; color: #6b7280; margin-top: 2px; }

    .contact-info { display: flex; flex-direction: column; gap: 3px; }
    .contact-phone, .contact-email { font-size: 11px; color: #4b5563; display: flex; align-items: center; gap: 4px; }

    .finance-value { font-weight: 600; color: #1f2937; white-space: nowrap; }
    .val-dr { color: #b91c1c; }
    .val-cr { color: #15803d; }
    .drcr-badge { font-size: 9px; font-weight: 700; margin-left: 3px; opacity: .7; }

    .salesman-info { display: flex; flex-direction: column; gap: 3px; }
    .salesman-name { font-weight: 600; color: #2563eb; font-size: 11px; }
    .salesman-commission { font-size: 9px; color: #059669; background: #d1fae5; padding: 2px 4px; border-radius: 3px; display: inline-block; max-width: fit-content; }
    .text-muted { color: #9ca3af; font-style: italic; font-size: 10px; }
    .parent-name { font-size: 11px; color: #4b5563; }

    .status-badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: 600; text-transform: capitalize; white-space: nowrap; }
    .status-active { background: #d1fae5; color: #065f46; }
    .status-inactive { background: #f3f4f6; color: #6b7280; }

    .action-icons { display: flex; gap: 6px; }
    .icon-btn { width: 26px; height: 26px; border-radius: 5px; border: none; background: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; transition: all 0.2s; padding: 0; text-decoration: none; }
    .icon-view { color: #3b82f6; background: #dbeafe; }
    .icon-view:hover { background: #bfdbfe; transform: scale(1.1); }
    .icon-edit { color: #f59e0b; background: #fef3c7; }
    .icon-edit:hover { background: #fde68a; transform: scale(1.1); }
    .icon-ledger { background: #ede9fe; }
    .icon-ledger:hover { background: #ddd6fe; transform: scale(1.1); }
    .icon-delete { color: #ef4444; background: #fee2e2; }
    .icon-delete:hover { background: #fca5a5; transform: scale(1.1); }

    /* Alerts */
    #alertContainer {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .alert {
        padding: 10px 14px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        min-width: 250px;
        animation: slideInRight 0.3s ease;
    }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .empty-state { padding: 40px 20px; text-align: center; }
    .empty-content { display: inline-block; }
    .empty-icon { font-size: 32px; margin-bottom: 10px; opacity: 0.5; }
    .empty-content h4 { font-size: 14px; color: #374151; margin-bottom: 5px; }
    .empty-content p { font-size: 11px; color: #6b7280; }

    .table-footer { padding: 10px 15px; border-top: 1px solid #e2e8f0; background: #f8fafc; font-size: 11px; color: #6b7280; border-radius: 0 0 6px 6px; text-align: center; }

    /* Modal Styles */
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; align-items: center; justify-content: center; }
    .modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); }
    .modal-content { position: relative; background: white; border-radius: 12px; padding: 0; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; animation: modalFadeIn 0.2s ease; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    @keyframes modalFadeIn { from { opacity: 0; transform: scale(0.95) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    .modal-header { display: flex; align-items: center; gap: 12px; padding: 20px 24px; border-bottom: 1px solid #e5e7eb; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 12px 12px 0 0; }
    .modal-title { font-size: 16px; font-weight: 600; color: #1f2937; margin: 0; }
    .modal-close { background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: all 0.2s; margin-left: auto; }
    .modal-close:hover { background: #f3f4f6; color: #1f2937; }
    .modal-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: white; flex-shrink: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .modal-text { padding: 24px; margin: 0; font-size: 13px; color: #6b7280; line-height: 1.5; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; padding: 20px 24px; border-top: 1px solid #e5e7eb; background: #fafafa; border-radius: 0 0 12px 12px; }
    .btn-modal { padding: 8px 20px; border: none; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s; min-width: 80px; }
    .btn-cancel { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
    .btn-cancel:hover { background: #e5e7eb; }
    .btn-confirm { background: #667eea; color: white; border: 1px solid #667eea; }
    .btn-confirm:hover { background: #5a67d8; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2); }

    /* ══════════════════════════════════════════════
         RESPONSIVE (DENSE, PREMIUM MOBILE APP UI)
    ══════════════════════════════════════════════ */
    @media (max-width: 768px) {
        .parties-container { padding: 8px; background: #f3f4f6; min-height: 100vh; }

        .page-header { flex-direction: column; gap: 10px; align-items: stretch; background: #fff; padding: 12px; border-radius: 8px; margin-bottom: 12px; border: none; }
        .page-title { font-size: 18px; }

        /* Report Cards Horizontal Scroll */
        .report-cards { flex-direction: row; flex-wrap: nowrap; overflow-x: auto; scroll-snap-type: x mandatory; gap: 10px; margin-bottom: 12px; padding-bottom: 4px; -webkit-overflow-scrolling: touch; }
        .report-card { flex: 0 0 88%; scroll-snap-align: center; padding: 12px 14px; min-width: auto; }
        .report-cards::-webkit-scrollbar { display: none; }

        /* Filters */
        .table-filters { background: #fff; padding: 12px; border-radius: 8px; flex-direction: column; align-items: stretch; gap: 10px; margin-bottom: 12px; }
        .search-box { width: 100%; max-width: 100%; }
        .filter-buttons { overflow-x: auto; padding-bottom: 2px; gap: 6px; -webkit-overflow-scrolling: touch; width: 100%; }
        .filter-buttons::-webkit-scrollbar { display: none; }
        .filter-btn { white-space: nowrap; flex-shrink: 0; }
        .bulk-actions { width: 100%; margin-left: 0; justify-content: space-between; }
        .bulk-select { flex: 1; }
        .btn-bulk { width: 80px; }

        /* Hide Desktop Table, Show Mobile Cards */
        .ldgp-desktop-only { display: none !important; }
        .ldgp-mobile-only  { display: block !important; }

        .mobile-party-cards { display: flex; flex-direction: column; gap: 10px; }

        /* Premium Mobile Card Layout */
        .mp-card {
            background: #fff; border-radius: 8px; padding: 12px 14px 10px; margin-bottom: 0px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;
        }
        
        .mp-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
        .mp-card-identity { flex: 1; min-width: 0; }
        .mp-name { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 14px; color: #1f2937; line-height: 1.3; }
        .mp-checkbox { margin-top: 0; flex-shrink: 0; width: 16px; height: 16px; }
        .mp-gst { font-size: 10.5px; color: #6b7280; margin-top: 3px; margin-left: 24px; font-family: monospace; }
        
        /* Fixed to always stay in ONE ROW */
        .mp-card-balance-row { display: flex; flex-direction: row; gap: 8px; margin-bottom: 10px; background: transparent; padding: 0; border-radius: 0; }
        .mp-balance-block { flex: 1; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 6px; padding: 8px 10px; display: flex; flex-direction: column; justify-content: center; }
        .mp-balance-label { font-size: 9px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 3px; }
        .mp-balance-val { font-size: 14px; font-weight: 700; color: #0f172a; white-space: nowrap; }

        /* Inline Tags for Contact and Meta */
        .mp-card-contact, .mp-card-meta { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; padding: 0; border: none; }
        .mp-contact-chip, .mp-meta-item { display: inline-flex; align-items: center; padding: 2px 6px; font-size: 10px; background: #f1f5f9; color: #475569; border-radius: 4px; border: 1px solid #e2e8f0; font-weight: 500; }
        .mp-meta-item em { color: #059669; font-style: normal; margin-left: 2px; }

        /* Highlighted Action Buttons */
        .mp-card-actions { border-top: 1px dashed #e2e8f0; margin-top: 10px; padding-top: 12px; display: flex; justify-content: space-between; gap: 10px; }
        .mp-action-btn { 
            display: inline-flex; align-items: center; justify-content: center; 
            padding: 7px 8px; /* Increased padding for easy clicking */
            border-radius: 8px; 
            font-size: 12px; /* Slightly larger text */
            font-weight: 600; 
            flex: 1; /* Stretch to fill 50% width each */
            box-shadow: 0 2px 4px rgba(0,0,0,0.05); /* Added slight shadow */
        }
        .mp-action-btn.ledger-btn { background: #ede9fe; color: #6d28d9; border: 1px solid #c4b5fd; }
        .mp-action-btn.edit-btn { background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
        .mp-action-btn.delete-btn { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        .modal-content { margin: 20px; width: calc(100% - 40px); max-height: calc(100vh - 40px); }
    }

    @media (max-width: 480px) {
        .modal-actions { flex-direction: column; gap: 8px; }
        .btn-modal { width: 100%; }
        /* Removed flex-direction: column from .mp-card-balance-row so they stay side-by-side */
    }
</style>
@endpush

@push('scripts')
<script>
    // ========== BULK ACTIONS ==========
    let selectedAction = '';
    let selectedPartyIds = [];

    // Select all checkbox (desktop table)
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActionButtons();
    });

    // Individual checkboxes (covers both desktop rows and mobile cards)
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(document.querySelectorAll('.row-checkbox')).every(cb => cb.checked);
            const selectAllEl = document.getElementById('selectAll');
            if (selectAllEl) selectAllEl.checked = allChecked;
            updateBulkActionButtons();
        });
    });

    function updateBulkActionButtons() {
        const anyChecked = Array.from(document.querySelectorAll('.row-checkbox')).some(cb => cb.checked);
        const bulkSelect = document.getElementById('bulkActionSelect');
        const applyBtn = document.getElementById('applyBulkAction');

        bulkSelect.disabled = !anyChecked;
        applyBtn.disabled = !anyChecked;

        if (!anyChecked) {
            bulkSelect.value = '';
        }
    }

    // Apply bulk action
    document.getElementById('applyBulkAction').addEventListener('click', function() {
        const action = document.getElementById('bulkActionSelect').value;
        if (!action) {
            alert('Please select a bulk action');
            return;
        }

        selectedPartyIds = [];

        document.querySelectorAll('.row-checkbox:checked').forEach(checkbox => {
            selectedPartyIds.push(checkbox.value);
        });

        if (selectedPartyIds.length === 0) {
            alert('Please select at least one {{ $partyType }}');
            return;
        }

        selectedAction = action;
        const modal = document.getElementById('bulkActionModal');
        const modalTitle = document.getElementById('bulkModalTitle');
        const modalText = document.getElementById('bulkModalText');

        const actionText = {
            'active': 'Set as Active',
            'inactive': 'Set as Inactive',
            'delete': 'Delete Selected'
        }[action];

        const actionMessage = {
            'active': `Are you sure you want to set ${selectedPartyIds.length} {{ $partyType }}(s) as active?`,
            'inactive': `Are you sure you want to set ${selectedPartyIds.length} {{ $partyType }}(s) as inactive?`,
            'delete': `Are you sure you want to permanently delete ${selectedPartyIds.length} {{ $partyType }}(s)? This action cannot be undone.`
        }[action];

        modalTitle.textContent = actionText;
        modalText.textContent = actionMessage;
        modal.style.display = 'flex';
    });

    // Confirm bulk action
    document.getElementById('confirmBulkAction').addEventListener('click', async function() {
        if (selectedPartyIds.length === 0 || !selectedAction) return;

        const confirmBtn = this;
        const originalText = confirmBtn.textContent;

        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Processing...';

        try {
            const url = '{{ route("admin.parties.bulkUpdateStatus") }}';

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    party_ids: selectedPartyIds,
                    status: selectedAction
                })
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Operation completed successfully!');
                window.location.reload();
            } else {
                alert(data.message || 'Operation failed. Please try again.');
                confirmBtn.disabled = false;
                confirmBtn.textContent = originalText;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating status. Please try again.');
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        }
    });

    function closeBulkActionModal() {
        document.getElementById('bulkActionModal').style.display = 'none';
        selectedAction = '';
        selectedPartyIds = [];

        const bulkSelect = document.getElementById('bulkActionSelect');
        bulkSelect.value = '';

        const selectAllEl = document.getElementById('selectAll');
        if (selectAllEl) selectAllEl.checked = false;
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        updateBulkActionButtons();
    }

    // ========== SEARCH FUNCTIONALITY ==========
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        applyCombinedFilter(searchTerm, getActiveStatusFilter());
    });

    // ========== FILTER FUNCTIONALITY ==========
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            applyCombinedFilter(searchTerm, this.getAttribute('data-filter'));
        });
    });

    function getActiveStatusFilter() {
        const active = document.querySelector('.filter-btn.active');
        return active ? active.getAttribute('data-filter') : 'all';
    }

    // Applies search + status filter together to BOTH the desktop table rows
    // and the mobile cards
    function applyCombinedFilter(searchTerm, statusFilter) {
        let visibleCount = 0;

        document.querySelectorAll('.table-row, .mp-card').forEach(el => {
            const status = el.getAttribute('data-status');
            const text = el.textContent.toLowerCase();

            let shouldShow = true;
            if (statusFilter !== 'all' && status !== statusFilter) shouldShow = false;
            if (searchTerm && !text.includes(searchTerm)) shouldShow = false;

            el.style.display = shouldShow ? '' : 'none';
            if (shouldShow) visibleCount++;
        });

        // Divide by 2 because elements exist in both desktop and mobile DOM
        document.getElementById('totalParties').textContent = Math.ceil(visibleCount / 2);
    }

    // ========== MODAL UTILITIES ==========
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeBulkActionModal();
        }
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    // ========== ALERT SYSTEM ==========
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        if (!container) return;
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);

        setTimeout(() => {
            alert.remove();
        }, 3000);
    }

    // Trigger alerts from PHP sessions
    @if(session('success'))
        showAlert('{{ session('success') }}', 'success');
    @endif
    @if(session('error'))
        showAlert('{{ session('error') }}', 'error');
    @endif
</script>
@endpush
@endsection