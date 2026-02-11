@extends('layouts.admin')

@section('title', 'View Category - Admin Panel')
@section('header-title', 'Categories Management')

@section('content')
<div class="products-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Category Details</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.categories.index') }}" class="btn-small btn-cancel">
                <span class="btn-icon">←</span> Back to Categories
            </a>
        </div>
    </div>

    <!-- Category Details Card -->
    <div class="form-wrapper">
        <!-- Basic Information Card -->
        <div class="form-card">
            <div class="card-header">
                <h3 class="card-title">Category Information</h3>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <!-- Category Name -->
                    <div class="form-group">
                        <label class="form-label">Category Name</label>
                        <div class="view-field">{{ $category->name }}</div>
                    </div>

                    <!-- Slug -->
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <div class="view-field view-slug">{{ $category->slug }}</div>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="view-field">
                            <span class="status-badge status-{{ $category->status }}">
                                {{ ucfirst($category->status) }}
                            </span>
                        </div>
                    </div>
                    <div></div>

                    <!-- Image -->
                    <div class="form-group image-desc">
                        <label class="form-label">Category Image</label>
                        <div class="view-image-container">
                            @if($category->image)
                                <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="view-image">
                            @else
                                <div class="view-image-placeholder">📁</div>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group image-desc">
                        <label class="form-label">Description</label>
                        <div class="view-field view-description">
                            {{ $category->description ?? 'No description provided' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Summary Card -->
        <div class="form-card">
            <div class="card-header">
                <h3 class="card-title">Products Summary</h3>
            </div>
            <div class="card-body">
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-icon" style="background: #dbeafe;">📦</div>
                        <div class="summary-content">
                            <div class="summary-count">{{ $category->simple_products_count }}</div>
                            <div class="summary-label">Simple Products</div>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-icon" style="background: #dcfce7;">🔄</div>
                        <div class="summary-content">
                            <div class="summary-count">{{ $category->variant_products_count }}</div>
                            <div class="summary-label">Variant Products</div>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-icon" style="background: #e9d8fd;">📊</div>
                        <div class="summary-content">
                            <div class="summary-count">{{ $category->total_products }}</div>
                            <div class="summary-label">Total Products</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Side by Side -->
        <div class="products-side-by-side">
            <!-- Simple Products Card -->
            <div class="side-card">
                <div class="card-header">
                    <h3 class="card-title">Simple Products</h3>
                    <div class="card-subtitle">{{ $simpleProducts->total() }} products</div>
                </div>
                <div class="card-body">
                    @if($simpleProducts->count() > 0)
                        <div class="products-table">
                            <table class="compact-table">
                                <thead>
                                    <tr>
                                        <th class="th-sno">S.No.</th>
                                        <th class="th-name">Product Name</th>
                                        <th class="th-status">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($simpleProducts as $index => $product)
                                    <tr>
                                        <td class="td-sno">{{ $index + 1 }}</td>
                                        <td class="td-name">{{ $product->name }}</td>
                                        <td class="td-status">
                                            <span class="status-badge status-{{ $product->status }}">
                                                {{ ucfirst($product->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            @if($simpleProducts->hasPages())
                            <div class="pagination-wrapper">
                                {{ $simpleProducts->appends(['variant_page' => $variantProducts->currentPage()])->links('vendor.pagination.simple') }}
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="empty-state-small">
                            <div class="empty-icon">📦</div>
                            <h4>No Simple Products</h4>
                            <p>No simple products in this category</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Variant Products Card -->
            <div class="side-card">
                <div class="card-header">
                    <h3 class="card-title">Variant Products</h3>
                    <div class="card-subtitle">{{ $variantProducts->total() }} products</div>
                </div>
                <div class="card-body">
                    @if($variantProducts->count() > 0)
                        <div class="products-table">
                            <table class="compact-table">
                                <thead>
                                    <tr>
                                        <th class="th-sno">S.No.</th>
                                        <th class="th-name">Product Name</th>
                                        <th class="th-status">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($variantProducts as $index => $product)
                                    <tr>
                                        <td class="td-sno">{{ $index + 1 }}</td>
                                        <td class="td-name">{{ $product->name }}</td>
                                        <td class="td-status">
                                            <span class="status-badge status-{{ $product->status }}">
                                                {{ ucfirst($product->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            @if($variantProducts->hasPages())
                            <div class="pagination-wrapper">
                                {{ $variantProducts->appends(['simple_page' => $simpleProducts->currentPage()])->links('vendor.pagination.simple') }}
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="empty-state-small">
                            <div class="empty-icon">🔄</div>
                            <h4>No Variant Products</h4>
                            <p>No variant products in this category</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>


    </div>
</div>

@push('styles')
<style>
    /* Main Container */
    .products-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 11px;
        line-height: 1.3;
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        padding: 8px;
    }

    /* Alert Messages */
    #alertContainer {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        max-width: 300px;
    }

    .alert {
        padding: 8px 12px;
        border-radius: 6px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease;
        font-weight: 600;
        font-size: 10px;
        border-left: 3px solid transparent;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }

    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-title {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }

    .btn-small {
        padding: 4px 8px;
        background: white;
        color: #fa8128;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-small:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
        transform: translateY(-1px);
    }

    .btn-icon {
        font-size: 10px;
    }

    /* Form Wrapper */
    .form-wrapper {
        max-width: 100%;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    /* Form Card */
    .form-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .card-header {
        padding: 12px 16px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e5e7eb;
    }

    .card-title {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        margin: 0;
    }

    .card-subtitle {
        font-size: 10px;
        color: #6b7280;
        margin-top: 2px;
    }

    .card-body {
        padding: 16px;
    }

    /* Form Grid */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 0;
    }

    .form-group {
        margin-bottom: 0;
    }

    /* Form Elements */
    .form-label {
        display: block;
        font-size: 10px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* View Fields */
    .view-field {
        padding: 6px 10px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 11px;
        background: #f9fafb;
        min-height: 32px;
        display: flex;
        align-items: center;
        color: #374151;
    }

    .view-slug {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        background: #f3f4f6;
        color: #6b7280;
        font-size: 10px;
    }

    .view-description {
        white-space: pre-line;
        line-height: 1.4;
    }

    /* View Image */
    .view-image-container {
        margin-top: 4px;
    }

    .view-image {
        width: 60px;
        height: 60px;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .view-image-placeholder {
        width: 60px;
        height: 60px;
        border-radius: 6px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        border: 1px solid #e5e7eb;
        color: #9ca3af;
    }

    /* Summary Grid */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .summary-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px;
        background: #f9fafb;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }

    .summary-icon {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .summary-content {
        flex: 1;
    }

    .summary-count {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        line-height: 1.2;
    }

    .summary-label {
        font-size: 10px;
        color: #6b7280;
        font-weight: 500;
    }

    /* Products Side by Side Layout */
    .products-side-by-side {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .side-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        height: fit-content;
        max-height: 500px;
        display: flex;
        flex-direction: column;
    }

    .side-card .card-header {
        padding: 10px 12px;
    }

    .side-card .card-body {
        padding: 12px;
        flex: 1;
        overflow-y: auto;
    }

    /* Products Table */
    .products-table {
        margin-top: 8px;
    }

    .compact-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }

    .compact-table th {
        background: #f8fafc;
        padding: 6px 8px;
        text-align: left;
        font-weight: 600;
        color: #4b5563;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .compact-table td {
        padding: 6px 8px;
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
    .th-sno { width: 30px; }
    .th-name { width: 70%; }
    .th-status { width: 30%; }

    /* S.No. */
    .td-sno {
        font-size: 10px;
        color: #6b7280;
        font-weight: 500;
        text-align: center;
    }

    /* Name */
    .td-name {
        font-size: 11px;
        color: #374151;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 9px;
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

    /* Empty State Small */
    .empty-state-small {
        text-align: center;
        padding: 20px 12px;
    }

    .empty-state-small .empty-icon {
        font-size: 24px;
        margin-bottom: 8px;
        opacity: 0.5;
    }

    .empty-state-small h4 {
        font-size: 12px;
        color: #374151;
        margin-bottom: 4px;
    }

    .empty-state-small p {
        font-size: 10px;
        color: #6b7280;
    }

    /* Pagination */
    .pagination-wrapper {
        margin-top: 12px;
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper .pagination {
        margin: 0;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination-wrapper .page-item {
        margin: 1px;
    }

    .pagination-wrapper .page-link {
        padding: 2px 6px;
        font-size: 10px;
        border-radius: 4px;
    }


    .btn-action {
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 120px;
        justify-content: center;
    }

    .btn-secondary {
        background: white;
        color: #4b5563;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #fa8427 0%, #f97316 100%);
        color: white;
        border: 1px solid #fa8427;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #e97317 0%, #ea580c 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(250, 132, 39, 0.3);
    }

    .submit-icon {
        font-size: 12px;
        font-weight: bold;
    }

    /* Scrollbar Styling */
    .side-card .card-body::-webkit-scrollbar {
        width: 4px;
    }

    .side-card .card-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    .side-card .card-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }

    .side-card .card-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .products-side-by-side {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .side-card {
            max-height: 300px;
        }
    }

    @media (max-width: 768px) {
        .products-container {
            padding: 6px;
        }

        .page-header {
            flex-direction: column;
            gap: 8px;
            align-items: flex-start;
        }

        .form-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .summary-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }


        .btn-action {
            width: 100%;
        }

        .compact-table {
            display: block;
        }

        .compact-table thead {
            display: none;
        }

        .compact-table tr {
            display: block;
            margin-bottom: 6px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .compact-table td {
            display: block;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
            padding: 4px 6px;
        }

        .compact-table td:last-child {
            border-bottom: none;
        }

        .td-sno {
            background: #f8fafc;
            font-weight: 600;
            border-bottom: 1px solid #e5e7eb !important;
        }

        .td-name::before {
            content: "Product: ";
            font-weight: 600;
            color: #4b5563;
            font-size: 9px;
        }

        .td-status::before {
            content: "Status: ";
            font-weight: 600;
            color: #4b5563;
            font-size: 9px;
        }
    }

    @media (max-width: 480px) {
        .products-container {
            padding: 4px;
        }

        .card-body {
            padding: 12px;
        }

        .summary-icon {
            width: 30px;
            height: 30px;
            font-size: 14px;
        }

        .summary-count {
            font-size: 14px;
        }

        .view-image {
            width: 50px;
            height: 50px;
        }

        .view-image-placeholder {
            width: 50px;
            height: 50px;
            font-size: 16px;
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

    // Display validation errors from server
    @if($errors->any())
        @foreach($errors->all() as $error)
            showAlert('{{ $error }}', 'error');
        @endforeach
    @endif

    @if(session('success'))
        showAlert('{{ session('success') }}', 'success');
    @endif

    @if(session('error'))
        showAlert('{{ session('error') }}', 'error');
    @endif
</script>
@endpush
@endsection
