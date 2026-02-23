@extends('layouts.admin')

@section('title', ucfirst($partyType) . 's - Admin Panel')
@section('header-title', ucfirst($partyType) . ' Management')

@section('content')
<div class="parties-container">
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

    <!-- Report Summary Cards (unchanged) -->
    <div class="report-cards">
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">👥</div>
            <div class="card-info">
                <div class="card-title">Total {{ ucfirst($partyType) }}s</div>
                <div class="card-value" id="totalParties">{{ $parties->count() }}</div>
                <div class="card-desc">{{ $parties->where('status', 'active')->count() }} active, {{ $parties->where('status', 'inactive')->count() }} inactive</div>
            </div>
        </div>
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">💰</div>
            <div class="card-info">
                <div class="card-title">Total Credit Limit</div>
                <div class="card-value">₹{{ number_format($parties->sum('credit_limit'), 2) }}</div>
                <div class="card-desc">Avg: ₹{{ number_format($parties->avg('credit_limit') ?? 0, 2) }}</div>
            </div>
        </div>
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

    <!-- Filters and Search (unchanged) -->
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
            </select>
            <button class="btn-bulk" id="applyBulkAction" disabled>Apply</button>
        </div>
    </div>

    <!-- Parties Table - UPDATED WITH SALESMAN COLUMN -->
    <div class="table-wrapper">
        <table class="compact-table">
            <thead>
                <tr>
                    <th class="th-checkbox"><input type="checkbox" id="selectAll"></th>
                    <th class="th-sno">S.No.</th>
                    <th class="th-name">{{ ucfirst($partyType) }} Name</th>
                    <th class="th-contact">Contact Info</th>
                    <th class="th-finance">Opening Bal.</th>
                    <th class="th-finance">Credit Limit</th>
                    <!-- NEW: Salesman Column -->
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
                        <span class="finance-value">₹{{ number_format($party->opening_balance ?? 0, 2) }}</span>
                    </td>
                    <td class="td-finance">
                        <span class="finance-value">₹{{ number_format($party->credit_limit ?? 0, 2) }}</span>
                    </td>

                    <!-- NEW: Salesman Column Data -->
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
                            <!-- View Ledger -->
                            <a href="{{ route('admin.parties.ledger', $party->id) }}"
                               class="icon-btn icon-view" title="View Ledger">
                                📒
                            </a>

                            <!-- Edit -->
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

    @if($parties->count() > 0)
    <div class="table-footer">
        <div class="footer-info">
            Showing {{ $parties->count() }} {{ $partyType }}{{ $parties->count() > 1 ? 's' : '' }}
        </div>
    </div>
    @endif
</div>

<!-- Bulk Action Modal (unchanged) -->
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
    /* Main Container (unchanged) */
    .parties-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 12px;
        line-height: 1.4;
    }

    /* Header (unchanged) */
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

    /* Report Cards (unchanged) */
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

    /* Filters (unchanged) */
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

    /* Bulk Actions (unchanged) */
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

    /* Compact Table - UPDATED COLUMN WIDTHS */
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

    /* Updated Column widths */
    .th-checkbox { width: 30px; }
    .th-sno { width: 50px; }
    .th-name { width: 180px; }
    .th-contact { width: 150px; }
    .th-finance { width: 90px; }
    .th-salesman { width: 120px; }  /* NEW: Salesman column width */
    .th-parent { width: 120px; }
    .th-status { width: 70px; }
    .th-actions { width: 90px; }

    /* Checkbox (unchanged) */
    input[type="checkbox"] {
        width: 14px;
        height: 14px;
        accent-color: #667eea;
        cursor: pointer;
    }

    /* S.No. (unchanged) */
    .td-sno {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        text-align: center;
    }

    /* Party Name (unchanged) */
    .party-name {
        font-weight: 600;
        color: #1f2937;
        line-height: 1.3;
        font-size: 12px;
    }

    .party-gst {
        font-size: 10px;
        color: #6b7280;
        margin-top: 2px;
    }

    /* Contact Info (unchanged) */
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

    /* Finance (unchanged) */
    .finance-value {
        font-weight: 600;
        color: #1f2937;
    }

    /* NEW: Salesman Styles */
    .salesman-info {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .salesman-name {
        font-weight: 600;
        color: #2563eb;
        font-size: 11px;
    }

    .salesman-commission {
        font-size: 9px;
        color: #059669;
        background: #d1fae5;
        padding: 2px 4px;
        border-radius: 3px;
        display: inline-block;
        max-width: fit-content;
    }

    .text-muted {
        color: #9ca3af;
        font-style: italic;
        font-size: 10px;
    }

    .parent-name {
        font-size: 11px;
        color: #4b5563;
    }

    /* Status Badge (unchanged) */
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

    /* Action Icons (unchanged) */
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

    /* Empty State (unchanged) */
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

    /* Table Footer (unchanged) */
    .table-footer {
        padding: 10px 15px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        font-size: 11px;
        color: #6b7280;
        border-radius: 0 0 6px 6px;
        text-align: center;
    }

    /* Modal Styles (unchanged) */
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

    /* Responsive (unchanged) */
    @media (max-width: 768px) {
        .parties-container {
            padding: 10px;
        }

        .page-header {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .report-cards {
            flex-direction: column;
        }

        .report-card {
            min-width: auto;
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
    // ========== BULK ACTIONS ==========
    let selectedAction = '';
    let selectedPartyIds = [];

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
            'inactive': 'Set as Inactive'
        }[action];

        const actionMessage = {
            'active': `Are you sure you want to set ${selectedPartyIds.length} {{ $partyType }}(s) as active?`,
            'inactive': `Are you sure you want to set ${selectedPartyIds.length} {{ $partyType }}(s) as inactive?`
        }[action];

        modalTitle.textContent = actionText;
        modalText.textContent = actionMessage;
        modal.style.display = 'flex';
    });

    // Confirm bulk action
    document.getElementById('confirmBulkAction').addEventListener('click', async function() {
        if (selectedPartyIds.length === 0 || !selectedAction) return;

        const modal = document.getElementById('bulkActionModal');
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
        selectedPartyIds = [];

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

        document.getElementById('totalParties').textContent = visibleCount;
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

            document.getElementById('totalParties').textContent = visibleCount;
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
