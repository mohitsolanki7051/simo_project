@extends('layouts.admin')

@section('title', 'Products - Admin Panel')
@section('header-title', 'Products Management')

@section('content')
<div class="products-header">
    <div class="header-content">
        <div class="header-left">
            <h2 class="page-title">All Products</h2>
            <p class="page-subtitle">Manage your product inventory</p>
        </div>
        <a href="{{ url('/admin/products/create') }}" class="btn-primary">
            <span class="btn-icon">➕</span> Add New Product
        </a>
    </div>
</div>

<div class="table-container">
    <div class="table-filters">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" placeholder="Search products..." id="searchInput">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All ({{ $products->count() }})</button>
            <button class="filter-btn" data-filter="active">Active</button>
            <button class="filter-btn" data-filter="inactive">Inactive</button>
        </div>

        <!-- Bulk Actions moved here -->
        <div class="bulk-actions-container">
            <select class="bulk-action-select" id="bulkActionSelect" disabled>
                <option value="">Bulk Actions</option>
                <option value="active">Set as Active</option>
                <option value="inactive">Set as Inactive</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button class="btn-apply-bulk" id="applyBulkAction" disabled>Apply</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th class="th-checkbox">
                        <input type="checkbox" id="selectAll">
                    </th>
                    <th class="th-sno">S.No.</th> <!-- New S.No. column -->
                    <th class="th-image">Image</th>
                    <th class="th-name">Product Details</th>
                    <th class="th-category">Category</th>
                    <th class="th-price">Price</th>
                    <th class="th-stock">Stock</th>
                    <th class="th-status">Status</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $product)
                <tr class="table-row" data-status="{{ $product->status }}" data-product-id="{{ $product->id }}">
                    <td>
                        <input type="checkbox" class="row-checkbox" value="{{ $product->id }}">
                    </td>
                    <td class="td-sno">{{ $index + 1 }}</td> <!-- S.No. cell -->
                    <td class="td-image">
                        <div class="product-image-wrapper">
                            <img src="{{ asset('storage/' . $product->base_image) }}"
                                 alt="{{ $product->name }}"
                                 class="product-image"
                                 onerror="this.src='https://via.placeholder.com/80x80/667eea/ffffff?text=No+Image'">
                        </div>
                    </td>
                    <td class="td-details">
                        <div class="product-details">
                            <div class="product-name">{{ $product->name }}</div>
                            <div class="product-sku">{{ $product->sku_code ?? 'N/A' }}</div>
                            <div class="product-brand">{{ $product->brand->name ?? 'No Brand' }}</div>
                        </div>
                    </td>
                    <td class="td-category">
                        <span class="category-badge">
                            {{ $product->category->name ?? 'Uncategorized' }}
                        </span>
                    </td>
                    <td class="td-price">
                        <div class="price-wrapper">
                            <span class="price-main">₹{{ number_format($product->price, 0) }}</span>
                            @if(isset($product->mrp_price) && $product->mrp_price > $product->price)
                            <span class="price-original">₹{{ number_format($product->mrp_price, 0) }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="td-stock">
                        <span class="stock-badge {{ ($product->current_stock ?? 0) > 10 ? 'stock-high' : (($product->current_stock ?? 0) > 0 ? 'stock-low' : 'stock-out') }}">
                            {{ $product->current_stock ?? 0 }} units
                        </span>
                    </td>
                    <td class="td-status">
                        <span class="status-badge status-{{ $product->status }}">
                            <span class="status-dot"></span>
                            {{ ucfirst($product->status) }}
                        </span>
                    </td>
                    <td class="td-actions">
                        <div class="action-buttons">
                            <a href="{{ route('admin.products.edit', $product->id) }}"
                               class="btn-action btn-edit"
                               title="Edit Product">
                                <span>✏️</span>
                            </a>
                            <button type="button"
                                    class="btn-action btn-delete"
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ htmlspecialchars($product->name, ENT_QUOTES) }}"
                                    title="Delete Product">
                                <span>🗑️</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="empty-state"> <!-- Update colspan to 9 -->
                        <div class="empty-content">
                            <div class="empty-icon">📦</div>
                            <h3 class="empty-title">No Products Found</h3>
                            <p class="empty-text">Get started by adding your first product</p>
                            <a href="{{ route('admin.products.create') }}" class="btn-primary">Add Product</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->count() > 0)
    <div class="table-footer">
        <div class="footer-info">
            Showing <strong>{{ $products->count() }}</strong> products
        </div>
        <!-- Bulk Actions removed from footer -->
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-overlay" onclick="closeDeleteModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon modal-icon-danger">🗑️</div>
            <h3 class="modal-title">Delete Product</h3>
        </div>
        <p class="modal-text" id="deleteModalText">Are you sure you want to delete this product? This action cannot be undone.</p>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-confirm">Delete Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Action Confirmation Modal -->
<div class="modal" id="bulkActionModal">
    <div class="modal-overlay" onclick="closeBulkActionModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon modal-icon-warning">⚠️</div>
            <h3 class="modal-title" id="bulkModalTitle"></h3>
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
    /* Products Header */
    .products-header {
        margin-bottom: 30px;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-left {
        flex: 1;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 5px;
    }

    .page-subtitle {
        font-size: 14px;
        color: #718096;
    }

    .btn-primary {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
    }

    .btn-icon {
        font-size: 16px;
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    /* Table Filters */
    .table-filters {
        padding: 25px 30px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        flex: 1;
        max-width: 300px;
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        color: #a0aec0;
    }

    .search-input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #ff6b35;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }

    .filter-buttons {
        display: flex;
        gap: 10px;
    }

    .filter-btn {
        padding: 8px 16px;
        background: #f7fafc;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #4a5568;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-btn:hover {
        background: #edf2f7;
        border-color: #cbd5e0;
    }

    .filter-btn.active {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        border-color: #ff6b35;
        color: white;
    }

    /* Bulk Actions */
    .bulk-actions-container {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .bulk-action-select {
        padding: 8px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: #f7fafc;
        font-size: 14px;
        font-weight: 600;
        color: #4a5568;
        cursor: pointer;
        min-width: 150px;
        transition: all 0.3s ease;
    }

    .bulk-action-select:not(:disabled):hover {
        border-color: #cbd5e0;
        background: #edf2f7;
    }

    .bulk-action-select:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .bulk-action-select:focus {
        outline: none;
        border-color: #ff6b35;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }

    .btn-apply-bulk {
        padding: 8px 20px;
        background: #48bb78;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-apply-bulk:hover:not(:disabled) {
        background: #38a169;
        transform: translateY(-1px);
    }

    .btn-apply-bulk:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Table Wrapper */
    .table-wrapper {
        overflow-x: auto;
    }

    /* Table Styles */
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead {
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table th {
        padding: 18px 20px;
        text-align: left;
        font-weight: 700;
        font-size: 13px;
        color: #4a5568;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
    }

    .th-checkbox {
        width: 40px;
    }

    .th-sno {
        width: 60px;
    }

    .th-image {
        width: 100px;
    }

    .th-name {
        min-width: 200px;
    }

    .th-category {
        width: 150px;
    }

    .th-price {
        width: 130px;
    }

    .th-stock {
        width: 120px;
    }

    .th-status {
        width: 120px;
    }

    .th-actions {
        width: 100px;
        text-align: center;
    }

    .table tbody tr {
        border-bottom: 1px solid #edf2f7;
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: #f7fafc;
        transform: scale(1.001);
    }

    .table tbody tr:last-child {
        border-bottom: none;
    }

    .table td {
        padding: 20px;
        vertical-align: middle;
    }

    /* S.No. Styles */
    .td-sno {
        color: #718096;
        font-weight: 600;
        font-size: 14px;
    }

    /* Checkbox Styles */
    input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #ff6b35;
    }

    /* Product Image */
    .product-image-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .product-image-wrapper:hover .product-image {
        transform: scale(1.1);
    }

    /* Product Details */
    .product-details {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .product-name {
        font-weight: 600;
        color: #2d3748;
        font-size: 15px;
        line-height: 1.4;
    }

    .product-sku {
        font-size: 12px;
        color: #718096;
        background: #f7fafc;
        padding: 2px 8px;
        border-radius: 4px;
        display: inline-block;
        width: fit-content;
    }

    .product-brand {
        font-size: 13px;
        color: #a0aec0;
    }

    /* Category Badge */
    .category-badge {
        display: inline-block;
        padding: 6px 14px;
        background: #ebf4ff;
        color: #3182ce;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    /* Price Styles */
    .price-wrapper {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .price-main {
        font-size: 16px;
        font-weight: 700;
        color: #2d3748;
    }

    .price-original {
        font-size: 13px;
        color: #a0aec0;
        text-decoration: line-through;
    }

    /* Stock Badge */
    .stock-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .stock-high {
        background: #c6f6d5;
        color: #22543d;
    }

    .stock-low {
        background: #feebc8;
        color: #7c2d12;
    }

    .stock-out {
        background: #fed7d7;
        color: #9b2c2c;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .status-active {
        background: #c6f6d5;
        color: #22543d;
    }

    .status-active .status-dot {
        background: #22543d;
        animation: pulse 2s infinite;
    }

    .status-inactive {
        background: #fed7d7;
        color: #9b2c2c;
    }

    .status-inactive .status-dot {
        background: #9b2c2c;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 16px;
        text-decoration: none;
    }

    .btn-edit {
        background: #bee3f8;
        color: #2c5282;
    }

    .btn-edit:hover {
        background: #90cdf4;
        transform: scale(1.1);
    }

    .btn-delete {
        background: #fed7d7;
        color: #9b2c2c;
    }

    .btn-delete:hover {
        background: #fc8181;
        transform: scale(1.1);
    }

    /* Empty State */
    .empty-state {
        padding: 80px 20px;
    }

    .empty-content {
        text-align: center;
    }

    .empty-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }

    .empty-title {
        font-size: 22px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 10px;
    }

    .empty-text {
        font-size: 14px;
        color: #718096;
        margin-bottom: 25px;
    }

    /* Table Footer */
    .table-footer {
        padding: 20px 30px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .footer-info {
        font-size: 14px;
        color: #718096;
    }

    .footer-info strong {
        color: #2d3748;
        font-weight: 700;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }

    .modal-content {
        position: relative;
        background: white;
        border-radius: 20px;
        padding: 35px;
        max-width: 450px;
        width: 90%;
        animation: modalSlideIn 0.3s ease;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .modal-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .modal-icon-danger {
        background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%);
    }

    .modal-icon-warning {
        background: linear-gradient(135deg, #feebc8 0%, #f6ad55 100%);
    }

    .modal-title {
        font-size: 22px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }

    .modal-text {
        color: #4a5568;
        margin-bottom: 30px;
        line-height: 1.6;
        font-size: 15px;
    }

    .modal-text strong {
        color: #2d3748;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .btn-modal {
        padding: 12px 24px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-cancel {
        background: #e2e8f0;
        color: #4a5568;
    }

    .btn-cancel:hover {
        background: #cbd5e0;
    }

    .btn-confirm {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    .btn-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
    }

    .modal-icon-danger + .btn-confirm {
        background: linear-gradient(135deg, #fc8181 0%, #f56565 100%);
        box-shadow: 0 4px 12px rgba(252, 129, 129, 0.3);
    }

    .modal-icon-danger + .btn-confirm:hover {
        box-shadow: 0 6px 20px rgba(252, 129, 129, 0.4);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .table-wrapper {
            overflow-x: auto;
        }

        .table {
            min-width: 850px;
        }
    }

    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            gap: 20px;
            align-items: flex-start;
        }

        .table-filters {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
        }

        .search-box {
            max-width: 100%;
        }

        .filter-buttons {
            justify-content: flex-start;
            overflow-x: auto;
        }

        .bulk-actions-container {
            width: 100%;
            justify-content: flex-end;
        }

        .table-footer {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
        }
    }

    @media (max-width: 480px) {
        .bulk-actions-container {
            flex-direction: column;
            align-items: stretch;
        }

        .bulk-action-select {
            min-width: unset;
            width: 100%;
        }

        .btn-apply-bulk {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('.table-row');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Filter functionality
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');
            const rows = document.querySelectorAll('.table-row');

            rows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else {
                    const status = row.getAttribute('data-status');
                    row.style.display = status === filter ? '' : 'none';
                }
            });
        });
    });

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const bulkSelect = document.getElementById('bulkActionSelect');
        const applyBtn = document.getElementById('applyBulkAction');

        checkboxes.forEach(cb => cb.checked = this.checked);

        const anyChecked = this.checked;
        bulkSelect.disabled = !anyChecked;
        applyBtn.disabled = !anyChecked;
    });

    // Individual checkboxes
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const anyChecked = Array.from(document.querySelectorAll('.row-checkbox')).some(cb => cb.checked);
            const allChecked = Array.from(document.querySelectorAll('.row-checkbox')).every(cb => cb.checked);

            document.getElementById('selectAll').checked = allChecked;
            document.getElementById('bulkActionSelect').disabled = !anyChecked;
            document.getElementById('applyBulkAction').disabled = !anyChecked;
        });
    });

    // Bulk Action functionality - FIXED
    let selectedAction = '';
    let selectedProductIds = [];

    document.getElementById('applyBulkAction').addEventListener('click', function() {
        const action = document.getElementById('bulkActionSelect').value;

        if (!action) {
            alert('Please select a bulk action first');
            return;
        }

        // Get selected product IDs
        selectedProductIds = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(checkbox => {
            selectedProductIds.push(checkbox.value);
        });

        if (selectedProductIds.length === 0) {
            alert('Please select at least one product');
            return;
        }

        selectedAction = action;

        // Show confirmation modal
        const modal = document.getElementById('bulkActionModal');
        const modalTitle = document.getElementById('bulkModalTitle');
        const modalText = document.getElementById('bulkModalText');

        const actionText = {
            'active': 'Set as Active',
            'inactive': 'Set as Inactive',
            'delete': 'Delete Products'
        }[action];

        const actionMessage = {
            'active': 'Are you sure you want to set the selected products as active?',
            'inactive': 'Are you sure you want to set the selected products as inactive?',
            'delete': 'Are you sure you want to delete the selected products? This action cannot be undone.'
        }[action];

        modalTitle.textContent = actionText;
        modalText.textContent = `${actionMessage} (${selectedProductIds.length} products selected)`;
        modal.style.display = 'flex';
    });

    // Confirm bulk action - SIMPLIFIED VERSION
    // Alternative SIMPLE solution
document.getElementById('confirmBulkAction').addEventListener('click', function() {
    if (selectedProductIds.length === 0 || !selectedAction) return;

    // Create a hidden form
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = "{{ csrf_token() }}";
    form.appendChild(csrfInput);

    // Add product IDs
    selectedProductIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'product_ids[]';
        input.value = id;
        form.appendChild(input);
    });

    if (selectedAction === 'delete') {
        form.action = '/admin/products/bulk-delete';
        // Add _method for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
    } else {
        form.action = '/admin/products/bulk-update-status';
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = selectedAction;
        form.appendChild(statusInput);
    }

    // Submit the form (this will cause page reload)
    document.body.appendChild(form);
    form.submit();
});
    // Delete Modal functions
    function openDeleteModal(productId, productName) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const modalText = document.getElementById('deleteModalText');

        const safeProductName = productName.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        modalText.innerHTML = `Are you sure you want to delete <strong>"${safeProductName}"</strong>? This action cannot be undone.`;
        form.action = `/admin/products/${productId}`;
        modal.style.display = 'flex';
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.style.display = 'none';
    }

    function closeBulkActionModal() {
        const modal = document.getElementById('bulkActionModal');
        modal.style.display = 'none';
        selectedAction = '';
        selectedProductIds = [];

        // Reset the select dropdown
        document.getElementById('bulkActionSelect').selectedIndex = 0;
    }

    // Add event listeners to all delete buttons
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.btn-delete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                openDeleteModal(productId, productName);
            });
        });
    });

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeDeleteModal();
            closeBulkActionModal();
        }
    });
</script>
@endpush
@endsection
