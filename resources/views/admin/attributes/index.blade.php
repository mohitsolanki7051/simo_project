@extends('layouts.admin')

@section('title', 'Attributes - Admin Panel')
@section('header-title', 'Attributes Management')

@section('content')
<div class="attributes-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h2 class="page-title">Attributes</h2>
            <p class="page-subtitle">Manage product attribute values</p>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.attributes.create') }}" class="btn-primary">
                <span>+</span> Add Attribute Value
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

    <!-- Attributes Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th width="50">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                    </th>
                    <th>Attribute Type</th>
                    <th>Name</th>
                    <th>Value</th>
                    <th>Status</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attributes as $attribute)
                <tr>
                    <td>
                        <input type="checkbox" class="row-checkbox" value="{{ $attribute->id }}" onchange="updateBulkActions()">
                    </td>
                    <td>
                        <span class="badge badge-type">{{ ucwords(str_replace('_', ' ', $attribute->type)) }}</span>
                    </td>
                    <td>
                        <div class="attribute-name">
                            {{ $attribute->name }}
                        </div>
                    </td>
                    <td>
                        <div class="value-display">
                            <span class="value-tag">{{ $attribute->value }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-{{ $attribute->status }}">
                            {{ ucfirst($attribute->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('admin.attributes.edit', $attribute->id) }}" class="btn-action btn-edit" title="Edit">
                                ✏️
                            </a>
                            <button onclick="deleteAttribute('{{ $attribute->id }}')" class="btn-action btn-delete" title="Delete">
                                🗑️
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">
                        <div class="empty-state">
                            <div class="empty-icon">📋</div>
                            <h3>No Attribute Values Found</h3>
                            <p>Start by creating your first attribute value</p>
                            <a href="{{ route('admin.attributes.create') }}" class="btn-primary">
                                <span>+</span> Add Attribute Value
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('styles')
<style>
    .attributes-container { padding: 30px; }

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

    .attribute-name { font-weight: 600; color: #2d3748; }

    .value-display { display: flex; }
    .value-tag { padding: 8px 16px; background: #e6fffa; color: #234e52; border-radius: 8px; font-size: 14px; font-weight: 600; border: 2px solid #b2f5ea; }

    .badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .badge-active { background: #c6f6d5; color: #22543d; }
    .badge-inactive { background: #fed7d7; color: #9b2c2c; }
    .badge-type { background: #e6fffa; color: #234e52; }

    .action-buttons { display: flex; gap: 8px; }
    .btn-action { padding: 8px 12px; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s; font-size: 16px; background: #f7fafc; }
    .btn-action:hover { transform: scale(1.1); }
    .btn-edit:hover { background: #bee3f8; }
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
        if (!confirm(`Are you sure you want to ${status} ${ids.length} attribute value(s)?`)) return;

        fetch('{{ route("admin.attributes.bulk-update-status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ attribute_ids: ids, status: status })
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
        if (!confirm(`Are you sure you want to delete ${ids.length} attribute value(s)? This action cannot be undone!`)) return;

        fetch('{{ route("admin.attributes.bulk-delete") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ attribute_ids: ids })
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

    function deleteAttribute(id) {
        if (!confirm('Are you sure you want to delete this attribute value?')) return;

        fetch(`/admin/attributes/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message || 'Attribute value deleted successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(data.message || 'Failed to delete attribute value', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to delete attribute value', 'error');
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
