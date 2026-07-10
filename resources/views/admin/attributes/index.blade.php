@extends('layouts.admin')

@section('title', 'Attributes - Admin Panel')
@section('header-title', 'Attributes Management')

@section('content')
<div class="products-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header with Action Buttons - Exactly like product index -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Attribute List</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.attributes.create') }}" class="btn-add">
                <span>Add Attribute</span>
            </a>
        </div>
    </div>

    <!-- Report Summary Cards - Exactly like product index -->
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

    <!-- Filters and Search - Exactly like product index -->
    <div class="table-filters">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text"
                   class="search-input"
                   placeholder="Search attributes..."
                   id="searchInput"
                   autocomplete="off">
        </div>
    </div>

    <!-- Attributes Table - Exactly like product index styling -->
    <div class="table-wrapper">
        <table class="products-table">
            <thead>
                <tr>
                    <th width="120">S.No.</th>
                    <th>Attribute Type</th>
                    <th width="400">Values</th>
                    <th width="200">Status</th>
                    <th width="120">Actions</th>
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
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div class="product-name">{{ ucwords(str_replace('_', ' ', $attribute->type)) }}</div>
                    </td>
                    <td>
                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-bottom: 4px;">
                            @php
                                $values = $attribute->items->take(3)->pluck('value')->toArray();
                                $remaining = $totalItems - 3;
                            @endphp

                            @foreach($values as $value)
                                <span style="padding: 3px 10px; background: #e0e7ff; color: #3730a3; border-radius: 12px; font-size: 12px; font-weight: 500; border: 1px solid #c7d2fe;">{{ $value }}</span>
                            @endforeach

                            @if($remaining > 0)
                                <span onclick="viewAttributeValues('{{ $attribute->type }}', {{ $attribute->items->pluck('value') }}, '{{ $activeItemsCount }}', '{{ $inactiveItemsCount }}')"
                                      style="padding: 3px 10px; background: #f3f4f6; color: #6b7280; border-radius: 12px; font-size: 12px; font-weight: 500; cursor: pointer; border: 1px solid #e5e7eb;">
                                    +{{ $remaining }} more
                                </span>
                            @endif
                        </div>
                        <div style="display: flex; gap: 8px; font-size: 12px;">
                            <span style="background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 12px;">{{ $activeItemsCount }} active</span>
                            <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 12px;">{{ $inactiveItemsCount }} inactive</span>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $attribute->status }}">
                            {{ ucfirst($attribute->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-icons">
                            <button type="button"
                                    class="action-btn view"
                                    onclick="viewAttributeValues('{{ $attribute->type }}', {{ $attribute->items->pluck('value') }}, '{{ $activeItemsCount }}', '{{ $inactiveItemsCount }}')"
                                    title="View All Values">
                                <span>👁️</span>
                            </button>
                            <a href="{{ route('admin.attributes.edit', $attribute->_id) }}"
                               class="action-btn edit"
                               title="Edit Attribute">
                                <span>✏️</span>
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
                            <a href="{{ route('admin.attributes.create') }}" class="btn-add-product">
                                + Add Attribute
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($attributes->count() > 0)
    <div class="table-footer">
        <div class="pagination-info">
            Showing {{ $attributes->count() }} attributes
        </div>
    </div>
    @endif
</div>

<!-- Values View Modal - Keep as is, it's fine -->
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
    /* Use EXACT same styles as product index - only keeping what's needed */
    .products-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        background: #f8fafc;
        min-height: 100vh;
    }

    /* Header - exactly like product index */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding: 0px 5px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .page-title {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn-add {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        color: white;
        background: linear-gradient(135deg, #fa8427 0%, #e97317 100%);
        text-decoration: none;
    }

    .btn-add:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(250, 132, 39, 0.3);
    }

    /* Report Cards - exactly like product index */
    .report-cards {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 15px;
    }

    .report-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.2s;
    }

    .report-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.05);
    }

    .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        flex-shrink: 0;
    }

    .card-info {
        flex: 1;
    }

    .card-title {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
    }

    .card-value {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
        line-height: 1.2;
    }

    .card-desc {
        font-size: 11px;
        color: #94a3b8;
    }

    /* Filters - exactly like product index */
    .table-filters {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 5px;
        background: white;
        padding: 10px 15px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .search-box {
        flex: 1;
        max-width: 300px;
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
    }

    .search-input {
        width: 100%;
        padding: 8px 12px 8px 36px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        transition: all 0.2s;
        background: #f8fafc;
    }

    .search-input:focus {
        outline: none;
        border-color: #8b5cf6;
        background: white;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    /* Table - exactly like product index */
    .table-wrapper {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        overflow-x: auto;
        margin-bottom: 15px;
    }

    .products-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .products-table th {
        background: #f8fafc;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        color: #475569;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .products-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .products-table tr:hover td {
        background: #f8fafc;
    }

    /* Product name style - reused for attribute name */
    .product-name {
        font-weight: 500;
        color: #1e293b;
        font-size: 13px;
    }

    /* Status badge - exactly like product index */
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #f1f5f9;
        color: #64748b;
    }

    /* Action icons - exactly like product index */
    .action-icons {
        display: flex;
        gap: 6px;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .action-btn.view {
        background: #ede9fe;
        color: #7c3aed;
    }

    .action-btn.edit {
        background: #dbeafe;
        color: #2563eb;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    /* Footer - exactly like product index */
    .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 12px 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .pagination-info {
        font-size: 13px;
        color: #64748b;
    }

    /* Empty state - exactly like product index */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-content {
        max-width: 300px;
        margin: 0 auto;
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.3;
    }

    .empty-content h4 {
        font-size: 16px;
        color: #334155;
        margin-bottom: 8px;
    }

    .empty-content p {
        font-size: 13px;
        color: #94a3b8;
        margin-bottom: 20px;
    }

    .btn-add-product {
        padding: 10px 20px;
        background: #8b5cf6;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-add-product:hover {
        background: #7c3aed;
    }

    /* Keep modal styles as they were - they're fine */
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

    .modal-body {
        padding: 24px;
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

    .values-stats-card .stat-item {
        text-align: center;
    }

    .values-stats-card .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .values-stats-card .stat-label {
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

    @media (max-width: 768px) {
        .report-cards {
            grid-template-columns: 1fr;
        }

        .table-filters {
            flex-direction: column;
            align-items: stretch;
            padding: 12px;
        }

        .search-box {
            max-width: 100%;
        }

        /* Table -> Compact Dense Cards */
        .table-wrapper {
            background: transparent;
            border: none;
            box-shadow: none;
            overflow-x: visible;
        }
        .products-table {
            min-width: 100%;
            display: block;
            border: none;
        }
        .products-table thead {
            display: none;
        }
        .products-table tbody {
            display: block;
            width: 100%;
        }
        
        .products-table .table-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            background: #fff;
            border-radius: 8px;
            padding: 12px 14px 10px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            position: relative;
        }
        .products-table .table-row:hover td {
            background: transparent;
        }

        .products-table td {
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
        }

        /* Col 1: S.No (td 1) -> Hide */
        .products-table td:nth-child(1) {
            display: none;
        }

        /* Col 2: Attribute Name/Type (td 2) */
        .products-table td:nth-child(2) {
            flex: 1;
            min-width: 0;
            order: 1;
        }
        .product-name {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        /* Col 4: Status (td 4) - Pushed absolute to top-right */
        .products-table td:nth-child(4) {
            width: auto;
            order: 2;
            position: absolute;
            top: 12px;
            right: 14px;
        }
        .status-badge {
            font-size: 9px;
            padding: 2px 7px;
        }

        /* Col 3: Values tags and stats (td 3) */
        .products-table td:nth-child(3) {
            width: 100%;
            order: 3;
            flex-direction: column;
            align-items: flex-start;
            margin-top: 8px;
            border-top: 1px dashed #f1f5f9;
            padding-top: 8px;
            gap: 6px;
        }

        /* Col 5: Actions (td 5) */
        .products-table td:nth-child(5) {
            width: 100%;
            order: 4;
            margin-top: 8px;
            border-top: 1px solid #f1f5f9;
            padding-top: 8px;
            justify-content: flex-end;
        }
        .action-icons {
            gap: 8px;
        }
        .action-btn {
            width: 28px;
            height: 28px;
            font-size: 12px;
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
