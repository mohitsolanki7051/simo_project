@extends('layouts.admin')

@section('title', 'Attributes - Admin Panel')
@section('header-title', 'Attributes Management')

@section('content')
<div class="products-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Attribute List</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.attributes.create') }}" class="btn-small btn-primary">
                <span class="btn-icon">+</span> Add Attribute
            </a>
        </div>
    </div>

    <!-- Report Summary Cards -->
    <div class="report-cards">
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">📊</div>
            <div class="card-info">
                <div class="card-title">Total Attributes</div>
                <div class="card-value">{{ $attributes->count() }}</div>
                <div class="card-desc">{{ $activeAttributesCount ?? 0 }} active, {{ $inactiveAttributesCount ?? 0 }} inactive</div>
            </div>
        </div>
        <div class="report-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">🎨</div>
            <div class="card-info">
                <div class="card-title">Attribute Values</div>
                <div class="card-value">{{ $totalAttributeValues ?? 0 }}</div>
                <div class="card-desc">Values across all attributes</div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="table-filters">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" placeholder="Search attributes..." id="searchInput">
        </div>
    </div>

    <!-- Attributes Table -->
    <div class="table-wrapper">
        <table class="compact-table">
            <thead>
                <tr>
                    <th class="th-sno">S.No.</th>
                    <th class="th-type">Attribute Type</th>
                    <th class="th-values">Values</th>
                    <th class="th-status">Status</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attributes as $index => $attribute)
                @php
                    $activeItemsCount = $attribute->items->where('status', 'active')->count();
                    $inactiveItemsCount = $attribute->items->where('status', 'inactive')->count();
                    $totalItems = $attribute->items->count();
                @endphp
                <tr class="table-row"
                    data-status="{{ $attribute->status }}"
                    data-type="{{ $attribute->type }}"
                    data-values="{{ $attribute->items->pluck('value')->implode(',') }}">
                    <td class="td-sno">{{ $index + 1 }}</td>
                    <td class="td-type">
                        <div class="attribute-name">{{ ucwords(str_replace('_', ' ', $attribute->type)) }}</div>
                    </td>
                    <td class="td-values">
                        <div class="values-display">
                            @php
                                $values = $attribute->items->take(3)->pluck('value')->toArray();
                                $remaining = $totalItems - 3;
                            @endphp

                            @foreach($values as $value)
                                <span class="value-tag">{{ $value }}</span>
                            @endforeach

                            @if($remaining > 0)
                                <span class="value-more" onclick="viewAttributeValues('{{ $attribute->type }}', {{ $attribute->items->pluck('value') }}, '{{ $activeItemsCount }}', '{{ $inactiveItemsCount }}')">
                                    +{{ $remaining }} more
                                </span>
                            @endif
                        </div>
                        <div class="values-stats">
                            <span class="stat-item stat-active">{{ $activeItemsCount }} active</span>
                            <span class="stat-item stat-inactive">{{ $inactiveItemsCount }} inactive</span>
                        </div>
                    </td>
                    <td class="td-status">
                        <span class="status-badge status-{{ $attribute->status }}">
                            {{ ucfirst($attribute->status) }}
                        </span>
                    </td>
                    <td class="td-actions">
                        <div class="action-icons">
                            <button type="button"
                                    class="icon-btn icon-view"
                                    onclick="viewAttributeValues('{{ $attribute->type }}', {{ $attribute->items->pluck('value') }}, '{{ $activeItemsCount }}', '{{ $inactiveItemsCount }}')"
                                    title="View All Values">
                                👁️
                            </button>
                            <a href="{{ route('admin.attributes.edit', $attribute->_id) }}"
                               class="icon-btn icon-edit" title="Edit Attribute">
                                ✏️
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        <div class="empty-content">
                            <div class="empty-icon">🎨</div>
                            <h4>No Attributes Found</h4>
                            <p>Start by adding your first attribute</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($attributes->count() > 0)
    <div class="table-footer">
        <div class="footer-info">
            Showing {{ $attributes->count() }} attributes
        </div>
    </div>
    @endif
</div>

<!-- Values View Modal -->
<div class="modal" id="valuesModal">
    <div class="modal-overlay" onclick="closeValuesModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon" style="background: #8b5cf6;">🎨</div>
            <div class="modal-title-section">
                <h4 class="modal-title" id="valuesModalTitle"></h4>
                <div class="modal-subtitle" id="valuesModalSubtitle"></div>
            </div>
            <button type="button" class="modal-close" onclick="closeValuesModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="values-stats-card">
                <div class="stat-item">
                    <div class="stat-value" id="activeCount">0</div>
                    <div class="stat-label">Active Values</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="inactiveCount">0</div>
                    <div class="stat-label">Inactive Values</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="totalCount">0</div>
                    <div class="stat-label">Total Values</div>
                </div>
            </div>
            <div class="values-list-container" id="valuesListContainer">
                <!-- Values will be populated here -->
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeValuesModal()">Close</button>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Main Container - Product Index se same */
    .products-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 12px;
        line-height: 1.4;
    }

    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-title {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
        margin: 0 0 4px 0;
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

    /* Filters */
    .table-filters {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
        align-items: center;
        flex-wrap: wrap;
        justify-content: space-between;
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
    .th-sno { width: 50px; }
    .th-type { width: 150px; }
    .th-values { width: 250px; }
    .th-status { width: 80px; }
    .th-actions { width: 90px; }

    /* S.No. */
    .td-sno {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        text-align: center;
    }

    /* Attribute Name */
    .attribute-name {
        font-weight: 600;
        color: #1f2937;
        line-height: 1.3;
        font-size: 12px;
    }

    /* Values Display */
    .td-values {
        min-width: 250px;
    }

    .values-display {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
        margin-bottom: 4px;
    }

    .value-tag {
        padding: 3px 8px;
        background: #e0e7ff;
        color: #3730a3;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 500;
        white-space: nowrap;
        border: 1px solid #c7d2fe;
    }

    .value-more {
        padding: 3px 8px;
        background: #f3f4f6;
        color: #6b7280;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid #e5e7eb;
    }

    .value-more:hover {
        background: #e5e7eb;
        color: #374151;
        transform: translateY(-1px);
    }

    .values-stats {
        display: flex;
        gap: 8px;
        font-size: 10px;
    }

    .stat-item {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 500;
    }

    .stat-item.stat-active {
        background: #d1fae5;
        color: #065f46;
    }

    .stat-item.stat-inactive {
        background: #fee2e2;
        color: #991b1b;
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
        color: #8b5cf6;
        background: #ede9fe;
    }

    .icon-view:hover {
        background: #ddd6fe;
        transform: scale(1.1);
    }

    .icon-edit {
        color: #3b82f6;
        background: #dbeafe;
    }

    .icon-edit:hover {
        background: #bfdbfe;
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

    .modal-title-section {
        flex: 1;
    }

    .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        line-height: 1.3;
    }

    .modal-subtitle {
        font-size: 11px;
        color: #6b7280;
        margin-top: 2px;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        color: #6b7280;
        cursor: pointer;
        padding: 4px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: #f3f4f6;
        color: #1f2937;
        transform: rotate(90deg);
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

    /* Values Modal Specific */
    .modal-body {
        padding: 24px;
    }

    .values-stats-card {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
    }

    #activeCount { color: #10b981; }
    #inactiveCount { color: #ef4444; }
    #totalCount { color: #8b5cf6; }

    .stat-label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .values-list-container {
        max-height: 300px;
        overflow-y: auto;
        padding: 15px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .values-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .values-list li {
        padding: 10px 12px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 12px;
        color: #374151;
        text-align: center;
        font-weight: 500;
        transition: all 0.2s;
    }

    .values-list li:hover {
        border-color: #8b5cf6;
        background: #f5f3ff;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(139, 92, 246, 0.1);
    }

    /* Modal Buttons */
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
    @media (max-width: 768px) {
        .products-container {
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

        .modal-content {
            margin: 20px;
            width: calc(100% - 40px);
            max-height: calc(100vh - 40px);
        }

        .values-stats-card {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .modal-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
        }
    }

    @media (max-width: 480px) {
        .modal-body {
            padding: 16px;
        }

        .modal-actions {
            flex-direction: column;
            gap: 8px;
        }

        .btn-modal {
            width: 100%;
        }

        .values-list {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('.table-row');
        rows.forEach(row => {
            const type = row.getAttribute('data-type').toLowerCase();
            const values = row.getAttribute('data-values').toLowerCase();
            const text = type + ' ' + values;
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // View Attribute Values
    function viewAttributeValues(type, values, activeCount, inactiveCount) {
        const modal = document.getElementById('valuesModal');
        const title = document.getElementById('valuesModalTitle');
        const subtitle = document.getElementById('valuesModalSubtitle');
        const activeElement = document.getElementById('activeCount');
        const inactiveElement = document.getElementById('inactiveCount');
        const totalElement = document.getElementById('totalCount');
        const container = document.getElementById('valuesListContainer');

        // Set modal content
        title.textContent = `${type.charAt(0).toUpperCase() + type.slice(1).replace(/_/g, ' ')} - All Values`;
        subtitle.textContent = `${values.length} values in total`;

        activeElement.textContent = activeCount;
        inactiveElement.textContent = inactiveCount;
        totalElement.textContent = values.length;

        // Create values list
        const valuesList = values.map(value => `<li>${value}</li>`).join('');
        container.innerHTML = `<ul class="values-list">${valuesList}</ul>`;

        // Show modal
        modal.style.display = 'flex';
    }

    function closeValuesModal() {
        document.getElementById('valuesModal').style.display = 'none';
    }

    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function() {
            this.parentElement.style.display = 'none';
        });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeValuesModal();
        }
    });

    @if (session('success'))
        showAlert('{{ session('success') }}', 'success');
    @endif

    @if (session('error'))
        showAlert('{{ session('error') }}', 'error');
    @endif
</script>
@endpush
@endsection
