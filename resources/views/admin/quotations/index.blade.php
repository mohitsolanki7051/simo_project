{{-- resources/views/admin/quotations/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quotations - Admin Panel')
@section('header-title', 'Quotations / Estimates')

@section('content')
<div class="quotations-container">
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📄</div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalQuotations }}</div>
                <div class="stat-label">Total Quotations</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <div class="stat-value">₹ {{ number_format($totalAmount, 2) }}</div>
                <div class="stat-label">Total Amount</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <div class="stat-value">{{ $draftCount }}</div>
                <div class="stat-label">Draft</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✉️</div>
            <div class="stat-content">
                <div class="stat-value">{{ $sentCount }}</div>
                <div class="stat-label">Sent</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" action="{{ route('admin.quotations.index') }}" class="filters-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-input">
                </div>
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="filter-input">
                </div>
                <div class="filter-group">
                    <label>Quotation No.</label>
                    <input type="text" name="quotation_number" value="{{ request('quotation_number') }}" placeholder="Search..." class="filter-input">
                </div>
                <div class="filter-group">
                    <label>Party</label>
                    <select name="party_id" class="filter-select">
                        <option value="">All Parties</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->_id }}" {{ request('party_id') == $customer->_id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="filter-group filter-actions">
                    <button type="submit" class="btn-filter">Apply</button>
                    <a href="{{ route('admin.quotations.index') }}" class="btn-reset">Reset</a>
                </div>
            </div>
        </form>
        <div class="filter-actions-right">
            <a href="{{ route('admin.quotations.create') }}" class="btn-create">
                + New Quotation
            </a>
        </div>
    </div>

    <!-- Quotations Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Quotation No.</th>
                    <th>Date</th>
                    <th>Valid Till</th>
                    <th>Party</th>
                    <th>Party Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotations as $quotation)
                <tr>
                    <td><strong>{{ $quotation->quotation_number }}</strong></td>
                    <td>{{ $quotation->quotation_date->format('d/m/Y') }}</td>
                    <td>
                        {{ $quotation->valid_till ? $quotation->valid_till->format('d/m/Y') : '-' }}
                        @if($quotation->isExpired())
                            <span class="status-badge expired">Expired</span>
                        @endif
                    </td>
                    <td>{{ $quotation->party->name ?? 'N/A' }}</td>
                    <td>
                        <span class="party-type {{ $quotation->party->party_type ?? 'customer' }}">
                            {{ ucfirst($quotation->party->party_type ?? 'customer') }}
                        </span>
                    </td>
                    <td class="text-right">₹ {{ number_format((float) $quotation->grand_total, 2) }}</td>
                    <td>
                        <span class="status-badge {{ $quotation->status }}">
                            {{ $quotation->status_text }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('admin.quotations.show', $quotation->id) }}" class="btn-icon" title="View">
                                👁️
                            </a>
                            @if($quotation->status === 'draft')
                                <a href="{{ route('admin.quotations.edit', $quotation->id) }}" class="btn-icon" title="Edit">
                                    ✏️
                                </a>
                                <a href="{{ route('admin.quotations.pdf', $quotation->id) }}" target="_blank" class="btn-icon" title="PDF">
                                    📄
                                </a>
                                <button onclick="deleteQuotation('{{ $quotation->id }}')" class="btn-icon delete-btn" title="Delete">
                                    🗑️
                                </button>
                            @else
                                <a href="{{ route('admin.quotations.pdf', $quotation->id) }}" target="_blank" class="btn-icon" title="PDF">
                                    📄
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No quotations found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container">
        {{ $quotations->links() }}
    </div>
</div>

@push('styles')
<style>
.quotations-container {
    padding: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 40px;
    height: 40px;
    background: #28a745;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}

.stat-value {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

.stat-label {
    font-size: 12px;
    color: #6b7280;
}

.filters-section {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 15px;
}

.filters-form {
    flex: 1;
}

.filter-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    min-width: 140px;
}

.filter-group label {
    display: block;
    font-size: 11px;
    font-weight: 500;
    color: #555;
    margin-bottom: 3px;
}

.filter-input, .filter-select {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 11px;
}

.filter-actions {
    display: flex;
    gap: 5px;
    align-items: flex-end;
}

.btn-filter, .btn-reset {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-filter {
    background: #28a745;
    color: white;
}

.btn-reset {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-create {
    display: inline-block;
    padding: 8px 16px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
}

.table-container {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.data-table th {
    background: #f9fafb;
    padding: 12px 10px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
}

.data-table td {
    padding: 10px;
    border-bottom: 1px solid #f3f4f6;
}

.data-table tr:hover {
    background: #f9fafb;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
}

.status-badge.draft { background: #f3f4f6; color: #374151; }
.status-badge.sent { background: #dbeafe; color: #1e40af; }
.status-badge.accepted { background: #d1fae5; color: #065f46; }
.status-badge.rejected { background: #fee2e2; color: #991b1b; }
.status-badge.expired { background: #fef3c7; color: #92400e; }

.party-type {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 500;
}

.party-type.customer { background: #d4edda; color: #155724; }
.party-type.dealer { background: #cce5ff; color: #004085; }
.party-type.distributor { background: #fff3cd; color: #856404; }

.text-right {
    text-align: right;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-icon {
    text-decoration: none;
    font-size: 16px;
    padding: 4px;
    border-radius: 4px;
    transition: background 0.2s;
}

.btn-icon:hover {
    background: #f3f4f6;
}

.delete-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    padding: 4px;
}

.pagination-container {
    margin-top: 20px;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .filter-row {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-group {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
function deleteQuotation(id) {
    if (!confirm('Delete this quotation?')) return;

    fetch(`/admin/quotations/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}
</script>
@endpush
@endsection
