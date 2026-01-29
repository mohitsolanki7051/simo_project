@extends('layouts.admin')

@section('title', 'Supplier Details - Admin Panel')
@section('header-title', 'Supplier Details')

@section('content')
<div class="supplier-details-container">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('admin.suppliers.index') }}" class="back-btn">
                <span>←</span> Back to Suppliers
            </a>
            <h2 class="page-title">{{ $supplier->name }}</h2>
            <div class="header-info">
                <span class="supplier-id">ID: {{ $supplier->id }}</span>
                <span class="supplier-status status-{{ $supplier->status }}">
                    {{ ucfirst($supplier->status) }}
                </span>
                <span class="supplier-date">Created: {{ $supplier->created_at->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="header-right">
            <div class="header-actions">
                <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Edit Supplier
                </a>
                <a href="{{ route('admin.purchase-orders.create') }}?supplier_id={{ $supplier->id }}" class="btn-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Create PO
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Left Column: Supplier Info -->
        <div class="left-column">
            <!-- Basic Info Card -->
            <div class="info-card">
                <div class="card-header">
                    <h3 class="card-title">Basic Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Supplier Name</label>
                            <span>{{ $supplier->name }}</span>
                        </div>
                        <div class="info-item">
                            <label>Email Address</label>
                            <span>{{ $supplier->email ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Phone Number</label>
                            <span>{{ $supplier->phone }}</span>
                        </div>
                        <div class="info-item">
                            <label>Alternate Phone</label>
                            <span>{{ $supplier->alternate_phone ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Contact Person</label>
                            <span>{{ $supplier->contact_person ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Status</label>
                            <span class="status-badge status-{{ $supplier->status }}">
                                {{ ucfirst($supplier->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Card -->
            <div class="info-card">
                <div class="card-header">
                    <h3 class="card-title">Address Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item full-width">
                            <label>Complete Address</label>
                            <span>{{ $supplier->address ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>City</label>
                            <span>{{ $supplier->city ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>State</label>
                            <span>{{ $supplier->state ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Pincode</label>
                            <span>{{ $supplier->pincode ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Country</label>
                            <span>{{ $supplier->country ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax & Legal Card -->
            <div class="info-card">
                <div class="card-header">
                    <h3 class="card-title">Tax & Legal Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>GSTIN</label>
                            <span>{{ $supplier->gstin ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>PAN Number</label>
                            <span>{{ $supplier->pan ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Additional Info & Purchase Orders -->
        <div class="right-column">
            <!-- Bank & Payment Card -->
            <div class="info-card">
                <div class="card-header">
                    <h3 class="card-title">Bank & Payment Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Bank Name</label>
                            <span>{{ $supplier->bank_name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Account Number</label>
                            <span>{{ $supplier->account_number ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>IFSC Code</label>
                            <span>{{ $supplier->ifsc_code ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Payment Terms</label>
                            <span>{{ $supplier->payment_terms ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Credit Limit</label>
                            <span>₹{{ number_format($supplier->credit_limit, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            <div class="info-card">
                <div class="card-header">
                    <h3 class="card-title">Additional Notes</h3>
                </div>
                <div class="card-body">
                    <div class="notes-content">
                        @if($supplier->notes)
                            {{ $supplier->notes }}
                        @else
                            <p class="no-notes">No notes added for this supplier.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Purchase Orders Card -->
            <div class="info-card">
                <div class="card-header">
                    <h3 class="card-title">Recent Purchase Orders</h3>
                    <a href="{{ route('admin.purchase-orders.index') }}?supplier_id={{ $supplier->id }}" class="card-action">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if($purchaseOrders->count() > 0)
                        <div class="purchase-orders-list">
                            @foreach($purchaseOrders as $po)
                            <div class="po-item">
                                <div class="po-header">
                                    <span class="po-number">{{ $po->po_number }}</span>
                                    <span class="po-status status-{{ $po->status }}">
                                        {{ ucfirst($po->status) }}
                                    </span>
                                </div>
                                <div class="po-details">
                                    <span class="po-date">{{ $po->po_date->format('d M Y') }}</span>
                                    <span class="po-total">₹{{ number_format($po->total_amount, 2) }}</span>
                                </div>
                                <div class="po-actions">
                                    <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="po-action-btn">
                                        View Details
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="no-orders">
                            <p>No purchase orders found for this supplier.</p>
                            <a href="{{ route('admin.purchase-orders.create') }}?supplier_id={{ $supplier->id }}" class="btn-secondary">
                                Create First Purchase Order
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .supplier-details-container { padding-bottom: 40px; }
    #alertContainer { position: fixed; top: 90px; right: 35px; z-index: 9999; max-width: 400px; }
    .alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .alert-success { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); color: #22543d; border-left: 4px solid #38a169; }
    .alert-error { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; border-left: 4px solid #f56565; }

    .page-header { margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; margin-bottom: 15px; }
    .back-btn:hover { background: #f7fafc; color: #667eea; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 10px; }
    .header-info { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
    .supplier-id { font-size: 14px; color: #718096; background: #f7fafc; padding: 4px 12px; border-radius: 20px; }
    .supplier-status { font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; }
    .status-active { background: #c6f6d5; color: #22543d; }
    .status-inactive { background: #fed7d7; color: #9b2c2c; }
    .supplier-date { font-size: 14px; color: #a0aec0; }
    .header-right { margin-top: 15px; }
    .header-actions { display: flex; gap: 12px; }
    .btn-primary, .btn-success { padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }
    .btn-success { background: #38a169; color: white; }
    .btn-success:hover { background: #2f855a; transform: translateY(-2px); }

    .main-content { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
    .left-column, .right-column { display: flex; flex-direction: column; gap: 25px; }

    .info-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
    .card-header { padding: 20px 25px; border-bottom: 2px solid #e2e8f0; background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); display: flex; justify-content: space-between; align-items: center; }
    .card-title { font-size: 16px; font-weight: 700; color: #2d3748; margin: 0; }
    .card-action { font-size: 14px; color: #667eea; text-decoration: none; font-weight: 600; }
    .card-body { padding: 25px; }

    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .info-item { display: flex; flex-direction: column; gap: 6px; }
    .info-item.full-width { grid-column: span 2; }
    .info-item label { font-size: 12px; font-weight: 600; color: #718096; text-transform: uppercase; }
    .info-item span { font-size: 14px; color: #2d3748; font-weight: 500; }
    .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; display: inline-block; }

    .notes-content { line-height: 1.6; color: #4a5568; }
    .no-notes { color: #a0aec0; font-style: italic; text-align: center; padding: 20px; }

    .purchase-orders-list { display: flex; flex-direction: column; gap: 15px; }
    .po-item { background: #f7fafc; padding: 15px; border-radius: 10px; border: 2px solid #e2e8f0; }
    .po-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .po-number { font-weight: 600; color: #2d3748; }
    .po-status { padding: 3px 10px; border-radius: 15px; font-size: 11px; font-weight: 600; }
    .po-details { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .po-date { color: #718096; font-size: 13px; }
    .po-total { font-weight: 600; color: #38a169; }
    .po-actions { text-align: right; }
    .po-action-btn { font-size: 13px; color: #667eea; text-decoration: none; font-weight: 600; }
    .no-orders { text-align: center; padding: 30px 20px; }
    .no-orders p { color: #a0aec0; margin-bottom: 15px; }
    .btn-secondary { padding: 8px 16px; background: #edf2f7; color: #4a5568; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block; }
    .btn-secondary:hover { background: #e2e8f0; }

    @media (max-width: 1024px) {
        .main-content { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .info-grid { grid-template-columns: 1fr; }
        .info-item.full-width { grid-column: span 1; }
        .header-actions { flex-direction: column; }
    }
</style>
@endpush

@push('scripts')
<script>
    // Show alert function
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<span class="alert-icon">${type === 'success' ? '✓' : '✕'}</span><span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => { alert.classList.add('removing'); setTimeout(() => alert.remove(), 300); }, 5000);
    }

    @if(session('success'))
        showAlert('{{ session('success') }}', 'success');
    @endif
    @if(session('error'))
        showAlert('{{ session('error') }}', 'error');
    @endif
</script>
@endpush
@endsection
