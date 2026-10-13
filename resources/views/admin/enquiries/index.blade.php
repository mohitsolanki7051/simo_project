@extends('layouts.admin')

@section('title', 'Website Enquiries & Leads - Admin Panel')
@section('header-title', 'Website Enquiries')

@push('styles')
<style>
    .enquiries-container {
        padding-bottom: 3rem;
    }
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1816;
    }
    .page-subtitle {
        font-size: 0.875rem;
        color: #6b6560;
        margin-top: 0.2rem;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }
    .stat-card {
        background: #ffffff;
        border: 1px solid #e8e5df;
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }
    .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        background: #fff4ec;
        color: #f98824;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1816;
        line-height: 1.1;
    }
    .stat-label {
        font-size: 0.8rem;
        color: #6b6560;
        margin-top: 0.2rem;
    }
    .filter-card {
        background: #ffffff;
        border: 1px solid #e8e5df;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .filter-pill {
        padding: 0.45rem 1rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        background: #f5f4f0;
        color: #6b6560;
        transition: all 0.2s;
    }
    .filter-pill.active, .filter-pill:hover {
        background: #f98824;
        color: #ffffff;
    }
    .search-input-box {
        position: relative;
        min-width: 260px;
    }
    .search-input-box input {
        width: 100%;
        padding: 0.5rem 1rem 0.5rem 2.2rem;
        border: 1.5px solid #e8e5df;
        border-radius: 8px;
        font-size: 0.85rem;
        outline: none;
    }
    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #b0aa9f;
        font-size: 0.85rem;
    }
    .data-table-card {
        background: #ffffff;
        border: 1px solid #e8e5df;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .table-custom {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.875rem;
    }
    .table-custom th {
        background: #faf9f6;
        padding: 0.85rem 1rem;
        font-weight: 600;
        color: #6b6560;
        border-bottom: 1px solid #e8e5df;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table-custom td {
        padding: 1rem;
        border-bottom: 1px solid #f5f4f0;
        color: #1a1816;
        vertical-align: middle;
    }
    .table-custom tr:hover {
        background: #fdfcfb;
    }
    .badge-type {
        padding: 0.25rem 0.65rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-block;
    }
    .badge-dealer { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
    .badge-contact { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
    .badge-catalogue { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    
    .status-select {
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid #e8e5df;
        outline: none;
        cursor: pointer;
    }
    .btn-action-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: 1px solid #e8e5df;
        background: #ffffff;
        color: #6b6560;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-action-icon:hover {
        background: #f5f4f0;
        color: #f98824;
    }
    .btn-delete-icon:hover {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }
    /* Modal */
    .admin-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    .admin-modal-card {
        background: #ffffff;
        border-radius: 14px;
        max-width: 550px;
        width: 100%;
        padding: 2rem;
        position: relative;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    .modal-detail-row {
        margin-bottom: 0.85rem;
        font-size: 0.9rem;
    }
    .modal-detail-label {
        font-weight: 600;
        color: #6b6560;
        width: 130px;
        display: inline-block;
    }
</style>
@endpush

@section('content')
<div class="enquiries-container">
    <div class="page-header">
        <div>
            <h2 class="page-title">Website Enquiries & Leads</h2>
            <p class="page-subtitle">View and manage customer submissions, dealer partnership requests, and catalogue downloads</p>
        </div>
    </div>

    <!-- STATS OVERVIEW -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📩</div>
            <div>
                <div class="stat-value">{{ $totalCount }}</div>
                <div class="stat-label">Total Enquiries</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef2f2; color: #dc2626;">🔔</div>
            <div>
                <div class="stat-value" style="color: #dc2626;">{{ $newCount }}</div>
                <div class="stat-label">New Leads</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff7ed; color: #ea580c;">🤝</div>
            <div>
                <div class="stat-value">{{ $dealerCount }}</div>
                <div class="stat-label">Dealer Enquiries</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #f0fdf4; color: #16a34a;">📄</div>
            <div>
                <div class="stat-value">{{ $catalogueCount }}</div>
                <div class="stat-label">Catalogue Requests</div>
            </div>
        </div>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="filter-card">
        <div class="filter-group">
            <span style="font-size: 0.8rem; font-weight: 600; color: #6b6560;">Filter Type:</span>
            <a href="{{ route('admin.enquiries.index') }}" class="filter-pill {{ !request('type') || request('type') == 'all' ? 'active' : '' }}">All Enquiries</a>
            <a href="{{ route('admin.enquiries.index') }}?type=dealer" class="filter-pill {{ request('type') == 'dealer' ? 'active' : '' }}">Dealer Requests</a>
            <a href="{{ route('admin.enquiries.index') }}?type=contact" class="filter-pill {{ request('type') == 'contact' ? 'active' : '' }}">Contact Forms</a>
            <a href="{{ route('admin.enquiries.index') }}?type=catalogue" class="filter-pill {{ request('type') == 'catalogue' ? 'active' : '' }}">Catalogue Downloads</a>
        </div>

        <form action="{{ route('admin.enquiries.index') }}" method="GET" class="search-input-box">
            @if(request('type'))
            <input type="hidden" name="type" value="{{ request('type') }}">
            @endif
            <span class="search-icon">🔍</span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, city...">
        </form>
    </div>

    <!-- ENQUIRIES TABLE -->
    <div class="data-table-card">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Name / Applicant</th>
                    <th>Enquiry Type</th>
                    <th>Phone & Email</th>
                    <th>City / State & Business</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enquiries as $item)
                <tr id="row-{{ $item->_id ?? $item->id }}">
                    <td>
                        <span style="font-weight: 600; font-size: 0.85rem;">{{ $item->created_at ? $item->created_at->format('d M Y') : 'N/A' }}</span><br>
                        <span style="font-size: 0.75rem; color: #b0aa9f;">{{ $item->created_at ? $item->created_at->format('h:i A') : '' }}</span>
                    </td>
                    <td>
                        <strong style="font-size: 0.95rem; color: #1a1816;">{{ $item->name }}</strong>
                    </td>
                    <td>
                        @if($item->type == 'dealer')
                            <span class="badge-type badge-dealer">🤝 Dealer Request</span>
                        @elseif($item->type == 'contact')
                            <span class="badge-type badge-contact">💬 Contact Form</span>
                        @else
                            <span class="badge-type badge-catalogue">📄 Catalogue Request</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size: 0.85rem;">📞 {{ $item->phone ?: 'N/A' }}</div>
                        <div style="font-size: 0.8rem; color: #6b6560;">✉️ {{ $item->email ?: 'N/A' }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 500;">{{ $item->city_state ?: 'N/A' }}</div>
                        @if($item->business_name)
                            <div style="font-size: 0.78rem; color: #f98824;">🏢 {{ $item->business_name }}</div>
                        @endif
                    </td>
                    <td>
                        <select class="status-select" onchange="updateStatus('{{ $item->_id ?? $item->id }}', this.value)">
                            <option value="new" {{ $item->status == 'new' ? 'selected' : '' }}>🔴 New</option>
                            <option value="contacted" {{ $item->status == 'contacted' ? 'selected' : '' }}>🟡 Contacted</option>
                            <option value="resolved" {{ $item->status == 'resolved' ? 'selected' : '' }}>🟢 Resolved</option>
                            <option value="closed" {{ $item->status == 'closed' ? 'selected' : '' }}>⚫ Closed</option>
                        </select>
                    </td>
                    <td style="text-align: right;">
                        <button onclick="viewEnquiry('{{ $item->_id ?? $item->id }}')" class="btn-action-icon" title="View Details">👁️</button>
                        <button onclick="deleteEnquiry('{{ $item->_id ?? $item->id }}')" class="btn-action-icon btn-delete-icon" title="Delete">🗑️</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: #b0aa9f;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📭</div>
                        <p style="font-size: 0.95rem;">No enquiries or leads found yet.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="padding: 1rem 1.5rem;">
            {{ $enquiries->links() }}
        </div>
    </div>
</div>

<!-- VIEW ENQUIRY MODAL -->
<div class="admin-modal-overlay" id="enquiryModal">
    <div class="admin-modal-card">
        <button onclick="closeEnquiryModal()" style="position: absolute; top: 1rem; right: 1rem; border: none; background: #f5f4f0; width: 30px; height: 30px; border-radius: 50%; cursor: pointer;">✕</button>
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem;" id="modalTitle">Enquiry Details</h3>
        <div id="modalBody">
            <p>Loading details...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateStatus(id, newStatus) {
        fetch(`/admin/enquiries/${id}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // optional notification
            } else {
                alert('Failed to update status.');
            }
        });
    }

    function viewEnquiry(id) {
        document.getElementById('enquiryModal').style.display = 'flex';
        const body = document.getElementById('modalBody');
        body.innerHTML = '<p style="color: #6b6560;">Loading details...</p>';

        fetch(`/admin/enquiries/${id}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const item = data.enquiry;
                body.innerHTML = `
                    <div class="modal-detail-row"><span class="modal-detail-label">Enquiry Type:</span> <strong>${item.type ? item.type.toUpperCase() : 'GENERAL'}</strong></div>
                    <div class="modal-detail-row"><span class="modal-detail-label">Full Name:</span> <strong>${item.name || '-'}</strong></div>
                    <div class="modal-detail-row"><span class="modal-detail-label">Phone Number:</span> <a href="tel:${item.phone}">${item.phone || '-'}</a></div>
                    <div class="modal-detail-row"><span class="modal-detail-label">Email Address:</span> <a href="mailto:${item.email}">${item.email || '-'}</a></div>
                    <div class="modal-detail-row"><span class="modal-detail-label">City / State:</span> <span>${item.city_state || '-'}</span></div>
                    <div class="modal-detail-row"><span class="modal-detail-label">Business Name:</span> <span>${item.business_name || '-'}</span></div>
                    <div class="modal-detail-row" style="margin-top: 1rem;"><span class="modal-detail-label">Message / Details:</span></div>
                    <div style="background: #f8f9fa; border: 1px solid #e8e5df; padding: 1rem; border-radius: 8px; font-size: 0.9rem; line-height: 1.5; color: #1a1816;">
                        ${item.message || 'No additional message provided.'}
                    </div>
                `;
            }
        });
    }

    function closeEnquiryModal() {
        document.getElementById('enquiryModal').style.display = 'none';
    }

    function deleteEnquiry(id) {
        if(!confirm('Are you sure you want to delete this enquiry record?')) return;

        fetch(`/admin/enquiries/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const row = document.getElementById(`row-${id}`);
                if(row) row.remove();
            } else {
                alert('Failed to delete enquiry.');
            }
        });
    }
</script>
@endpush
