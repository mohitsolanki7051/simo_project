@extends('layouts.admin')

@section('title', 'Salesmen - Admin Panel')
@section('header-title', 'Salesman Management')

@section('content')
<div class="salesmen-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Salesmen List</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.salesmen.create') }}" class="btn-small btn-primary">
                <span class="btn-icon">+</span> Add Salesman
            </a>
        </div>
    </div>
    <!-- Updated Report Summary Cards -->
    <div class="report-cards">
        {{-- <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">👥</div>
            <div class="card-info">
                <div class="card-title">Total Salesmen</div>
                <div class="card-value" id="totalSalesmen">{{ $salesmen->count() }}</div>
                <div class="card-desc">{{ $salesmen->where('status', 'active')->count() }} active, {{ $salesmen->where('status', 'inactive')->count() }} inactive</div>
            </div>
        </div> --}}
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%);">📊</div>
            <div class="card-info">
                <div class="card-title">Total Parties with Salesman</div>
                <div class="card-value">{{ $totalPartiesWithSalesman }}</div>
                <div class="card-desc">Customers + Dealers + Distributors</div>
            </div>
        </div>
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">👥</div>
            <div class="card-info">
                <div class="card-title">Total Customers</div>
                <div class="card-value">{{ $totalCustomers }}</div>
                <div class="card-desc">Across all salesmen</div>
            </div>
        </div>

        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">👥</div>
            <div class="card-info">
                <div class="card-title">Total Dealers</div>
                <div class="card-value">{{ $totalDealers }}</div>
                <div class="card-desc">Across all salesmen</div>
            </div>
        </div>

        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">👥</div>
            <div class="card-info">
                <div class="card-title">Total Distributors</div>
                <div class="card-value">{{ $totalDistributors }}</div>
                <div class="card-desc">Across all salesmen</div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="table-filters">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" placeholder="Search salesmen..." id="searchInput">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All ({{ $salesmen->count() }})</button>
            <button class="filter-btn" data-filter="active">Active ({{ $salesmen->where('status', 'active')->count() }})</button>
            <button class="filter-btn" data-filter="inactive">Inactive ({{ $salesmen->where('status', 'inactive')->count() }})</button>
        </div>
        <div class="bulk-actions">
            <select class="bulk-select" id="bulkActionSelect" disabled>
                <option value="">Bulk Actions</option>
                <option value="active">Set Active</option>
                <option value="inactive">Set Inactive</option>
            </select>
            <button class="btn-bulk" id="applyBulkAction" disabled>Apply</button>
        </div>
    </div>

    <!-- Salesmen Table - Updated with Customer instead of Retailer -->
    <div class="table-wrapper">
        <table class="compact-table">
            <thead>
                <tr>
                    <th class="th-checkbox"><input type="checkbox" id="selectAll"></th>
                    <th class="th-sno">S.No.</th>
                    <th class="th-name">Salesman Name</th>
                    <th class="th-contact">Contact Info</th>
                    <th class="th-date">Joining Date</th>
                    <th class="th-salary">Salary Type</th>
                    <th class="th-amount">Fixed Salary (Monthly)</th>
                    <th class="th-commission">Commission (%)</th>
                    <th class="th-status">Status</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesmen as $index => $salesman)
                <tr class="table-row"
                    data-status="{{ $salesman->status }}"
                    data-salesman-id="{{ $salesman->id }}">
                    <td class="td-checkbox">
                        <input type="checkbox" class="row-checkbox" value="{{ $salesman->id }}">
                    </td>
                    <td class="td-sno">{{ $index + 1 }}</td>
                    <td class="td-name">
                        <div class="salesman-name">{{ $salesman->name }}</div>
                        @if($salesman->notes)
                            <div class="salesman-notes" title="{{ $salesman->notes }}">📝</div>
                        @endif
                    </td>
                    <td class="td-contact">
                        <div class="contact-info">
                            <div class="contact-phone">📱 {{ $salesman->phone }}</div>
                            @if($salesman->email)
                                <div class="contact-email">✉️ {{ $salesman->email }}</div>
                            @endif
                        </div>
                    </td>
                    <td class="td-date">
                        <span class="date-value">{{ $salesman->formatted_joining_date }}</span>
                    </td>
                    <td class="td-salary">
                        <span class="salary-type">{{ $salesman->salary_type_text }}</span>
                    </td>
                    <td class="td-amount">
                        @if($salesman->salary_type != 'commission')
                            <span class="amount-value">{{ $salesman->formatted_fixed_salary }}</span>
                            <span class="amount-period">/month</span>
                        @else
                            <span class="amount-value text-muted">—</span>
                        @endif
                    </td>
                    <td class="td-commission">
                        @if($salesman->commission_enabled && $salesman->salary_type != 'fixed')
                            <div class="commission-badge enabled">
                                <div class="commission-tooltip">
                                    @if($salesman->customer_commission_percent > 0)
                                        <span class="commission-item">
                                            <span class="commission-party">Customer:</span>
                                            <span class="commission-value">{{ $salesman->customer_commission_percent }}%</span>
                                        </span>
                                    @endif
                                    @if($salesman->dealer_commission_percent > 0)
                                        <span class="commission-item">
                                            <span class="commission-party">Dealer:</span>
                                            <span class="commission-value">{{ $salesman->dealer_commission_percent }}%</span>
                                        </span>
                                    @endif
                                    @if($salesman->distributor_commission_percent > 0)
                                        <span class="commission-item">
                                            <span class="commission-party">Distributor:</span>
                                            <span class="commission-value">{{ $salesman->distributor_commission_percent }}%</span>
                                        </span>
                                    @endif
                                </div>
                                <span class="commission-summary">
                                    @if($salesman->customer_commission_percent > 0) C:{{ $salesman->customer_commission_percent }}% @endif
                                    @if($salesman->dealer_commission_percent > 0) D:{{ $salesman->dealer_commission_percent }}% @endif
                                    @if($salesman->distributor_commission_percent > 0) Di:{{ $salesman->distributor_commission_percent }}% @endif
                                </span>
                            </div>
                        @else
                            <span class="commission-badge disabled">Not Available</span>
                        @endif
                    </td>
                    <td class="td-status">
                        <span class="status-badge status-{{ $salesman->status }}">
                            {{ ucfirst($salesman->status) }}
                        </span>
                    </td>
                    <td class="td-actions">
                        <div class="action-icons">
                            <!-- View -->
                            <a href="{{ route('admin.salesmen.show', $salesman->id) }}"
                               class="icon-btn icon-view" title="View Details">
                                👁️
                            </a>
                            <!-- Edit -->
                            <a href="{{ route('admin.salesmen.edit', $salesman->id) }}"
                               class="icon-btn icon-edit" title="Edit">
                                ✏️
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="empty-state">
                        <div class="empty-content">
                            <div class="empty-icon">👥</div>
                            <h4>No Salesmen Found</h4>
                            <p>Start by adding your first salesman</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($salesmen->count() > 0)
    <div class="table-footer">
        <div class="footer-info">
            Showing {{ $salesmen->count() }} salesman{{ $salesmen->count() > 1 ? 's' : '' }}
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
    .salesmen-container {
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

    .btn-icon {
        font-size: 12px;
    }

    /* Report Cards */
    .report-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 15px;
    }

    .report-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.2s;
        cursor: default;
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

    .card-info {
        flex: 1;
    }

    .card-title {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .card-value {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .card-desc {
        font-size: 11px;
        color: #9ca3af;
    }

    /* Filters */
    .table-filters {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box {
        flex: 1;
        max-width: 200px;
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: #718096;
    }

    .search-input {
        width: 100%;
        padding: 6px 8px 6px 24px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 11px;
        background: #f9fafb;
    }

    .search-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .filter-buttons {
        display: flex;
        gap: 4px;
    }

    .filter-btn {
        padding: 5px 10px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 10px;
        color: #4b5563;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-btn:hover {
        background: #e5e7eb;
    }

    .filter-btn.active {
        background: #667eea;
        border-color: #667eea;
        color: white;
    }

    /* Bulk Actions */
    .bulk-actions {
        display: flex;
        gap: 4px;
        align-items: center;
        margin-left: auto;
    }

    .bulk-select {
        padding: 5px 8px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 11px;
        background: #f9fafb;
        min-width: 120px;
        cursor: pointer;
    }

    .bulk-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-bulk {
        padding: 5px 12px;
        background: #48bb78;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 11px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-bulk:hover:not(:disabled) {
        background: #38a169;
        transform: translateY(-1px);
    }

    .btn-bulk:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Compact Table */
    .table-wrapper {
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        margin-top: 10px;
    }

    .compact-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .compact-table th {
        background: #f8fafc;
        padding: 8px 10px;
        text-align: left;
        font-weight: 600;
        color: #4b5563;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .compact-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .compact-table tr:last-child td {
        border-bottom: none;
    }

    .compact-table tr:hover {
        background: #f9fafb;
    }

    /* Column widths */
    .th-checkbox { width: 30px; }
    .th-sno { width: 50px; }
    .th-name { width: 150px; }
    .th-contact { width: 150px; }
    .th-date { width: 90px; }
    .th-salary { width: 100px; }
    .th-amount { width: 100px; }
    .th-commission { width: 150px; }
    .th-status { width: 70px; }
    .th-actions { width: 80px; }

    /* Checkbox */
    input[type="checkbox"] {
        width: 14px;
        height: 14px;
        accent-color: #667eea;
        cursor: pointer;
    }

    /* S.No. */
    .td-sno {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        text-align: center;
    }

    /* Salesman Name */
    .salesman-name {
        font-weight: 600;
        color: #1f2937;
        line-height: 1.3;
        font-size: 12px;
    }

    .salesman-notes {
        display: inline-block;
        margin-left: 4px;
        cursor: help;
        opacity: 0.6;
    }

    /* Contact Info */
    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .contact-phone, .contact-email {
        font-size: 11px;
        color: #4b5563;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Date */
    .date-value {
        font-size: 11px;
        color: #4b5563;
    }

    /* Salary Type */
    .salary-type {
        font-size: 11px;
        font-weight: 500;
        color: #1f2937;
        padding: 2px 6px;
        background: #f3f4f6;
        border-radius: 10px;
        display: inline-block;
    }

    /* Amount */
    .amount-value {
        font-weight: 600;
        color: #1f2937;
    }

    .amount-period {
        font-size: 9px;
        color: #6b7280;
        margin-left: 2px;
    }

    .text-muted {
        color: #9ca3af;
    }

    /* Commission */
    .commission-badge {
        position: relative;
        display: inline-block;
    }

    .commission-badge.enabled {
        background: #dbeafe;
        color: #1e40af;
        padding: 4px 8px;
        border-radius: 12px;
        cursor: help;
    }

    .commission-badge.disabled {
        background: #f3f4f6;
        color: #6b7280;
        padding: 4px 8px;
        border-radius: 12px;
    }

    .commission-summary {
        font-size: 10px;
        font-weight: 500;
    }

    .commission-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #1f2937;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 10px;
        white-space: nowrap;
        display: none;
        margin-bottom: 8px;
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .commission-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 5px;
        border-style: solid;
        border-color: #1f2937 transparent transparent transparent;
    }

    .commission-badge.enabled:hover .commission-tooltip {
        display: block;
    }

    .commission-item {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        margin: 3px 0;
    }

    .commission-party {
        color: #9ca3af;
    }

    .commission-value {
        font-weight: 600;
        color: #34d399;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 9px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #f3f4f6;
        color: #6b7280;
    }

    /* Action Icons */
    .action-icons {
        display: flex;
        gap: 6px;
    }

    .icon-btn {
        width: 26px;
        height: 26px;
        border-radius: 5px;
        border: none;
        background: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: all 0.2s;
        padding: 0;
        text-decoration: none;
    }

    .icon-view {
        color: #3b82f6;
        background: #dbeafe;
    }

    .icon-view:hover {
        background: #bfdbfe;
        transform: scale(1.1);
    }

    .icon-edit {
        color: #f59e0b;
        background: #fef3c7;
    }

    .icon-edit:hover {
        background: #fde68a;
        transform: scale(1.1);
    }

    /* Empty State */
    .empty-state {
        padding: 40px 20px;
        text-align: center;
    }

    .empty-content {
        display: inline-block;
    }

    .empty-icon {
        font-size: 32px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .empty-content h4 {
        font-size: 14px;
        color: #374151;
        margin-bottom: 5px;
    }

    .empty-content p {
        font-size: 11px;
        color: #6b7280;
    }

    /* Table Footer */
    .table-footer {
        padding: 10px 15px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        font-size: 11px;
        color: #6b7280;
        border-radius: 0 0 6px 6px;
        text-align: center;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(2px);
    }

    .modal-content {
        position: relative;
        background: white;
        border-radius: 12px;
        padding: 0;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        animation: modalFadeIn 0.2s ease;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px 12px 0 0;
    }

    .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #6b7280;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
        margin-left: auto;
    }

    .modal-close:hover {
        background: #f3f4f6;
        color: #1f2937;
    }

    .modal-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .modal-text {
        padding: 24px;
        margin: 0;
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px 24px;
        border-top: 1px solid #e5e7eb;
        background: #fafafa;
        border-radius: 0 0 12px 12px;
    }

    .btn-modal {
        padding: 8px 20px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 80px;
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
    }

    .btn-confirm {
        background: #667eea;
        color: white;
        border: 1px solid #667eea;
    }

    .btn-confirm:hover {
        background: #5a67d8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .report-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .salesmen-container {
            padding: 10px;
        }

        .page-header {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .report-cards {
            grid-template-columns: 1fr;
        }

        .report-card {
            width: 100%;
        }

        .table-filters {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .search-box {
            max-width: 100%;
        }

        .bulk-actions {
            margin-left: 0;
            width: 100%;
            justify-content: flex-end;
        }

        .modal-content {
            margin: 20px;
            width: calc(100% - 40px);
            max-height: calc(100vh - 40px);
        }
    }

    @media (max-width: 480px) {
        .modal-actions {
            flex-direction: column;
            gap: 8px;
        }

        .btn-modal {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // ========== FETCH PARTY COUNTS ==========
    async function fetchPartyCounts() {
        try {
            const response = await fetch('/admin/salesmen/party-counts');
            const data = await response.json();

            document.getElementById('totalCustomers').textContent = data.customers || 0;
            document.getElementById('totalDealers').textContent = data.dealers || 0;
            document.getElementById('totalDistributors').textContent = data.distributors || 0;
        } catch (error) {
            console.error('Error fetching party counts:', error);
        }
    }

    // ========== BULK ACTIONS ==========
    let selectedAction = '';
    let selectedSalesmanIds = [];

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActionButtons();
    });

    // Individual checkboxes
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(document.querySelectorAll('.row-checkbox')).every(cb => cb.checked);
            document.getElementById('selectAll').checked = allChecked;
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

        selectedSalesmanIds = [];

        document.querySelectorAll('.row-checkbox:checked').forEach(checkbox => {
            selectedSalesmanIds.push(checkbox.value);
        });

        if (selectedSalesmanIds.length === 0) {
            alert('Please select at least one salesman');
            return;
        }

        selectedAction = action;
        const modal = document.getElementById('bulkActionModal');
        const modalTitle = document.getElementById('bulkModalTitle');
        const modalText = document.getElementById('bulkModalText');

        const actionText = {
            'active': 'Set as Active',
            'inactive': 'Set as Inactive'
        }[action];

        const actionMessage = {
            'active': `Are you sure you want to set ${selectedSalesmanIds.length} salesman(s) as active?`,
            'inactive': `Are you sure you want to set ${selectedSalesmanIds.length} salesman(s) as inactive?`
        }[action];

        modalTitle.textContent = actionText;
        modalText.textContent = actionMessage;
        modal.style.display = 'flex';
    });

    // Confirm bulk action
    document.getElementById('confirmBulkAction').addEventListener('click', async function() {
        if (selectedSalesmanIds.length === 0 || !selectedAction) return;

        const modal = document.getElementById('bulkActionModal');
        const confirmBtn = this;
        const originalText = confirmBtn.textContent;

        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Processing...';

        try {
            const url = '{{ route("admin.salesmen.bulk-update-status") }}';

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    salesman_ids: selectedSalesmanIds,
                    status: selectedAction
                })
            });

            const data = await response.json();

            if (data.success) {
                alert('Status updated successfully!');
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
        selectedSalesmanIds = [];

        const bulkSelect = document.getElementById('bulkActionSelect');
        bulkSelect.value = '';

        document.getElementById('selectAll').checked = false;
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        updateBulkActionButtons();
    }

    // ========== SEARCH FUNCTIONALITY ==========
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('.table-row');

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const isVisible = text.includes(searchTerm);
            row.style.display = isVisible ? '' : 'none';

            if (isVisible) visibleCount++;
        });

        document.getElementById('totalSalesmen').textContent = visibleCount;
    });

    // ========== FILTER FUNCTIONALITY ==========
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');
            const rows = document.querySelectorAll('.table-row');
            const searchInput = document.getElementById('searchInput');
            const searchTerm = searchInput.value.toLowerCase();

            let visibleCount = 0;

            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const rowText = row.textContent.toLowerCase();

                let shouldShow = true;

                if (filter !== 'all' && status !== filter) {
                    shouldShow = false;
                }

                if (searchTerm && !rowText.includes(searchTerm)) {
                    shouldShow = false;
                }

                row.style.display = shouldShow ? '' : 'none';

                if (shouldShow) visibleCount++;
            });

            document.getElementById('totalSalesmen').textContent = visibleCount;
        });
    });

    // ========== CLOSE MODAL WITH ESCAPE KEY ==========
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeBulkActionModal();
        }
    });

    // ========== CLOSE MODAL WHEN CLICKING OUTSIDE ==========
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });


</script>
@endpush
@endsection
