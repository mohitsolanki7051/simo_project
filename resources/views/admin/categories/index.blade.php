@extends('layouts.admin')

@section('title', 'Categories - Admin Panel')
@section('header-title', 'Categories Management')

@section('content')
<div class="categories-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Categories</h2>
            <p class="page-subtitle">Manage product categories</p>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.categories.create') }}" class="btn-primary">
                <span>+</span> Add Category
            </a>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions" id="bulkActions" style="display: none;">
        <div class="bulk-info">
            <span id="selectedCount">0</span> items selected
        </div>
        <div class="bulk-buttons">
            <button class="btn-bulk btn-activate" onclick="bulkUpdateStatus('active')">
                ✓ Activate
            </button>
            <button class="btn-bulk btn-deactivate" onclick="bulkUpdateStatus('inactive')">
                ✕ Deactivate
            </button>
            <button class="btn-bulk btn-delete" onclick="bulkDelete()">
                🗑️ Delete
            </button>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th width="50">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                    </th>
                    <th>Category</th>
                    <th>Parent</th>
                    <th>Products</th>
                    <th>Sub-Categories</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    function displayCategories($categories, $parentId = null, $level = 0) {
                        $filtered = $categories->where('parent_id', $parentId);
                        foreach($filtered as $category) {
                @endphp
                            <tr data-level="{{ $level }}" class="category-level-{{ $level }}">
                                <td>
                                    <input type="checkbox" class="row-checkbox" value="{{ $category->id }}" onchange="updateBulkActions()">
                                </td>
                                <td>
                                    <div class="category-info">
                                        @if($level > 0)
                                            <span class="level-indicator">
                                                @for($i = 0; $i < $level; $i++)
                                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                                @endfor
                                                ↳
                                            </span>
                                        @endif
                                        @if($category->image)
                                            <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="category-image">
                                        @endif
                                        <div class="category-details">
                                            <div class="category-name">{{ $category->name }}</div>
                                            @if($category->description)
                                                <div class="category-description">{{ Str::limit($category->description, 50) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($category->parent)
                                        <span class="parent-category">{{ $category->parent->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-count">{{ $category->products_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-count">{{ $category->children_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="sort-order">{{ $category->sort_order }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $category->status }}">
                                        {{ ucfirst($category->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.categories.show', $category->id) }}" class="btn-action btn-view" title="View">
                                            👁️
                                        </a>
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn-action btn-edit" title="Edit">
                                            ✏️
                                        </a>
                                        <button onclick="deleteCategory('{{ $category->id }}')" class="btn-action btn-delete" title="Delete">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                @php
                            // Recursively display children
                            displayCategories($categories, $category->id, $level + 1);
                        }
                    }
                @endphp

                @php displayCategories($categories); @endphp

                @if($categories->count() == 0)
                <tr>
                    <td colspan="8" class="text-center">
                        <div class="empty-state">
                            <div class="empty-icon">📂</div>
                            <h3>No Categories Found</h3>
                            <p>Start by creating your first category</p>
                            <a href="{{ route('admin.categories.create') }}" class="btn-primary">
                                <span>+</span> Add Category
                            </a>
                        </div>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@push('styles')
<style>
    .categories-container { padding: 30px; }

    /* Alert Styles */
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 5px; }
    .page-subtitle { font-size: 14px; color: #718096; }

    .btn-primary { padding: 12px 24px; background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; border: none; border-radius: 10px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; box-shadow: 0 4px 12px rgba(255,107,53,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.4); }

    /* Bulk Actions */
    .bulk-actions { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; border: 2px solid #e2e8f0; }
    .bulk-info { font-weight: 600; color: #2d3748; }
    .bulk-buttons { display: flex; gap: 10px; }
    .btn-bulk { padding: 8px 16px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .btn-activate { background: #48bb78; color: white; }
    .btn-deactivate { background: #ecc94b; color: white; }
    .btn-delete { background: #fc8181; color: white; }

    /* Table */
    .table-container { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); }
    .data-table th { padding: 16px; text-align: left; font-weight: 700; color: #2d3748; font-size: 14px; border-bottom: 2px solid #e2e8f0; }
    .data-table td { padding: 16px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #4a5568; }
    .data-table tr:hover { background: #f7fafc; }

    /* Category Levels */
    .category-level-1 { background-color: #f7fafc; }
    .category-level-2 { background-color: #edf2f7; }
    .category-level-3 { background-color: #e2e8f0; }

    .category-info { display: flex; align-items: center; gap: 12px; }
    .level-indicator { color: #a0aec0; font-size: 12px; }
    .category-image { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; border: 2px solid #e2e8f0; }
    .category-details { flex: 1; }
    .category-name { font-weight: 600; color: #2d3748; }
    .category-description { font-size: 12px; color: #718096; margin-top: 2px; }

    .parent-category { padding: 4px 10px; background: #e6fffa; color: #234e52; border-radius: 6px; font-size: 12px; font-weight: 600; }

    .badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .badge-active { background: #c6f6d5; color: #22543d; }
    .badge-inactive { background: #fed7d7; color: #9b2c2c; }
    .badge-count { background: #bee3f8; color: #2c5282; }

    .sort-order { font-weight: 600; color: #2d3748; }

    .action-buttons { display: flex; gap: 8px; }
    .btn-action { padding: 8px 12px; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s; font-size: 16px; background: #f7fafc; }
    .btn-action:hover { transform: scale(1.1); }
    .btn-view:hover { background: #bee3f8; }
    .btn-edit:hover { background: #c6f6d5; }
    .btn-delete:hover { background: #fed7d7; }

    .empty-state { padding: 60px 20px; text-align: center; }
    .empty-icon { font-size: 64px; margin-bottom: 20px; }
    .empty-state h3 { font-size: 20px; color: #2d3748; margin-bottom: 10px; }
    .empty-state p { color: #718096; margin-bottom: 20px; }
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

    function toggleSelectAll() {
        const checked = document.getElementById('selectAll').checked;
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checked);
        updateBulkActions();
    }

    function updateBulkActions() {
        const checked = document.querySelectorAll('.row-checkbox:checked').length;
        document.getElementById('bulkActions').style.display = checked > 0 ? 'flex' : 'none';
        document.getElementById('selectedCount').textContent = checked;
    }

    function bulkUpdateStatus(status) {
        const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (!confirm(`Are you sure you want to ${status} ${ids.length} category(s)?`)) return;

        fetch('{{ route("admin.categories.bulk-update-status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ category_ids: ids, status: status })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(data.message, 'error');
            }
        });
    }

    function bulkDelete() {
        const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (!confirm(`Are you sure you want to delete ${ids.length} category(s)? This action cannot be undone!`)) return;

        fetch('{{ route("admin.categories.bulk-delete") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ category_ids: ids })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                if (data.errors && data.errors.length > 0) {
                    data.errors.forEach(error => showAlert(error, 'error'));
                }
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        });
    }

    function deleteCategory(id) {
        if (!confirm('Are you sure you want to delete this category?')) return;

        fetch(`/admin/categories/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message || 'Category deleted successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(data.message || 'Failed to delete category', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to delete category', 'error');
        });
    }

    @if(session('success'))
        showAlert('{{ session("success") }}', 'success');
    @endif

    @if(session('error'))
        showAlert('{{ session("error") }}', 'error');
    @endif
</script>
@endpush
@endsection
