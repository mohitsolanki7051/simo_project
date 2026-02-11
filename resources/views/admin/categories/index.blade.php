@extends('layouts.admin')

@section('title', 'Categories - Admin Panel')
@section('header-title', 'Categories Management')

@section('content')
<div class="products-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Category List</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.categories.create') }}" class="btn-small btn-primary">
                <span class="btn-icon">+</span> Add Category
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="table-filters">
        <div class="filter-group">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" class="search-input" placeholder="Search categories..." id="searchInput">
            </div>
            <div class="status-filter">
                <select class="filter-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="table-wrapper">
        <table class="compact-table">
            <thead>
                <tr>
                    <th class="th-sno">S.No.</th>
                    <th class="th-category">Category</th>
                    <th class="th-simple">Simple Products</th>
                    <th class="th-variant">Variant Products</th>
                    <th class="th-total">Total Products</th>
                    <th class="th-status">Status</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $index => $category)
                <tr class="table-row"
                    data-status="{{ $category->status }}"
                    data-name="{{ strtolower($category->name) }}"
                    data-slug="{{ strtolower($category->slug) }}"
                    data-description="{{ strtolower($category->description) }}">
                    <td class="td-sno">{{ $index + 1 }}</td>
                    <td class="td-category">
                        <div class="category-display">
                            @if($category->image)
                                <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="category-image">
                            @else
                                <div class="category-image-placeholder">
                                    📁
                                </div>
                            @endif
                            <div class="category-info">
                                <div class="category-name">{{ $category->name }}</div>
                                <div class="category-slug">{{ $category->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="td-simple">
                        <div class="count-badge count-simple">{{ $category->simple_products_count }}</div>
                    </td>
                    <td class="td-variant">
                        <div class="count-badge count-variant">{{ $category->variant_products_count }}</div>
                    </td>
                    <td class="td-total">
                        <div class="count-badge count-total">{{ $category->total_products }}</div>
                    </td>
                    <td class="td-status">
                        <span class="status-badge status-{{ $category->status }}">
                            {{ ucfirst($category->status) }}
                        </span>
                    </td>
                    <td class="td-actions">
                        <div class="action-icons">
                            <a href="{{ route('admin.categories.show', $category->_id) }}"
                                class="icon-btn icon-view" title="View Category">
                                    👁️
                                </a>
                            <a href="{{ route('admin.categories.edit', $category->_id) }}"
                               class="icon-btn icon-edit" title="Edit Category">
                                ✏️
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        <div class="empty-content">
                            <div class="empty-icon">📂</div>
                            <h4>No Categories Found</h4>
                            <p>Start by adding your first category</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->count() > 0)
    <div class="table-footer">
        <div class="footer-info">
            Showing {{ $categories->count() }} categories
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    /* Main Container */
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

    /* Filters */
    .table-filters {
        display: flex;
        gap: 8px;
        margin-bottom: 15px;
        align-items: center;
    }

    .filter-group {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        width: 100%;
    }

    .search-box {
        flex: 1;
        min-width: 200px;
        max-width: 300px;
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
        padding: 8px 8px 8px 28px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 12px;
        background: #f9fafb;
        transition: all 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .status-filter {
        min-width: 150px;
    }

    .filter-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 12px;
        background: #f9fafb;
        color: #4b5563;
        cursor: pointer;
        transition: all 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    .filter-select:focus {
        outline: none;
        border-color: #667eea;
        background-color: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Compact Table */
    .table-wrapper {
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        margin-top: 5px;
    }

    .compact-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .compact-table th {
        background: #f8fafc;
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
        color: #4b5563;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .compact-table td {
        padding: 10px 12px;
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
    .th-category { width: 300px; }
    .th-simple { width: 120px; }
    .th-variant { width: 120px; }
    .th-total { width: 120px; }
    .th-status { width: 100px; }
    .th-actions { width: 80px; }

    /* S.No. */
    .td-sno {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        text-align: center;
    }

    /* Category Display */
    .category-display {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .category-image,
    .category-image-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        flex-shrink: 0;
        object-fit: cover;
        border: 1px solid #e5e7eb;
    }

    .category-image-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        font-size: 16px;
    }

    .category-info {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .category-name {
        font-size: 13px;
        font-weight: 600;
        color: #111827;
        line-height: 1.2;
    }

    .category-slug {
        font-size: 11px;
        color: #6b7280;
        background: transparent;
        padding: 0;
        border: none;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    }

    /* Count Badges */
    .count-badge {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        text-align: center;
        min-width: 60px;
    }

    .count-simple {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    .count-variant {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .count-total {
        background: #e9d8fd;
        color: #5b21b6;
        border: 1px solid #d8b4fe;
        font-weight: 800;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: capitalize;
        letter-spacing: 0.3px;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .status-inactive {
        background: #f3f4f6;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }

    /* Action Icons */
    .action-icons {
        display: flex;
        gap: 8px;
    }
    .icon-view {
        color: #10b981;
        background: #d1fae5;
        border: 1px solid #a7f3d0;
    }

    .icon-view:hover {
        background: #a7f3d0;
        transform: scale(1.1);
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    }

    .icon-btn {
        width: 28px;
        height: 28px;
        border-radius: 5px;
        border: none;
        background: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        transition: all 0.2s;
        padding: 0;
        text-decoration: none;
    }

    .icon-edit {
        color: #3b82f6;
        background: #dbeafe;
        border: 1px solid #bfdbfe;
    }

    .icon-edit:hover {
        background: #bfdbfe;
        transform: scale(1.1);
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
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
        padding: 12px 15px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        font-size: 11px;
        color: #6b7280;
        border-radius: 0 0 6px 6px;
        text-align: center;
    }

    /* Alert Messages */
    #alertContainer {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 1000;
        max-width: 300px;
    }

    .alert {
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 10px;
        font-size: 12px;
        font-weight: 500;
        display: flex;
        align-items: center;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
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

        .table-filters {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .filter-group {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .search-box {
            max-width: 100%;
        }

        .status-filter {
            width: 100%;
        }

        .category-display {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }

        .category-info {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .compact-table {
            display: block;
        }

        .compact-table thead {
            display: none;
        }

        .compact-table tr {
            display: block;
            margin-bottom: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }

        .compact-table td {
            display: block;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
        }

        .compact-table td:last-child {
            border-bottom: none;
        }

        .td-sno {
            background: #f8fafc;
            font-weight: 600;
            border-bottom: 1px solid #e5e7eb !important;
        }

        .td-category::before {
            content: "Category: ";
            font-weight: 600;
            color: #4b5563;
        }

        .td-simple::before {
            content: "Simple Products: ";
            font-weight: 600;
            color: #4b5563;
        }

        .td-variant::before {
            content: "Variant Products: ";
            font-weight: 600;
            color: #4b5563;
        }

        .td-total::before {
            content: "Total Products: ";
            font-weight: 600;
            color: #4b5563;
        }

        .td-status::before {
            content: "Status: ";
            font-weight: 600;
            color: #4b5563;
        }

        .td-actions::before {
            content: "Actions: ";
            font-weight: 600;
            color: #4b5563;
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
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');

    function filterCategories() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const rows = document.querySelectorAll('.table-row');

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const slug = row.getAttribute('data-slug');
            const description = row.getAttribute('data-description') || '';
            const status = row.getAttribute('data-status');
            const text = name + ' ' + slug + ' ' + description;

            const matchesSearch = text.includes(searchTerm);
            const matchesStatus = statusValue === 'all' || status === statusValue;

            row.style.display = matchesSearch && matchesStatus ? '' : 'none';
        });
    }

    // Add event listeners
    searchInput.addEventListener('input', filterCategories);
    statusFilter.addEventListener('change', filterCategories);

    // Show alerts from session
    @if (session('success'))
        showAlert('{{ session('success') }}', 'success');
    @endif

    @if (session('error'))
        showAlert('{{ session('error') }}', 'error');
    @endif
</script>
@endpush
@endsection
