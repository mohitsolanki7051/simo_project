@extends('layouts.admin')

@section('title', 'Supplier Details - Admin Panel')
@section('header-title', 'Supplier Details')

@section('content')
<div class="po-details-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('admin.suppliers.index') }}" class="back-btn">
                <span>←</span> Back to Suppliers
            </a>
            <h2 class="page-title">{{ $supplier->name }}</h2>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn-secondary">
                <span>✏️</span> Edit Supplier
            </a>
            @if($supplier->status == 'active')
            <a href="{{ route('admin.purchase-orders.create') }}?supplier_id={{ $supplier->id }}" class="btn-primary">
                <span>📋</span> Create Purchase Order
            </a>
            @endif
        </div>
    </div>

    <!-- Status Card -->
    <div class="status-card">
        <div class="status-info">
            <div class="status-item">
                <span class="label">Status:</span>
                <span class="status-badge status-{{ $supplier->status }}">
                    {{ ucfirst($supplier->status) }}
                </span>
            </div>
            <div class="status-item">
                <span class="label">Supplier ID:</span>
                <strong>#{{ str_pad($supplier->id, 6, '0', STR_PAD_LEFT) }}</strong>
            </div>
            <div class="status-item">
                <span class="label">Created On:</span>
                <strong>{{ $supplier->created_at->format('d M Y') }}</strong>
            </div>
            <div class="status-item">
                <span class="label">Last Updated:</span>
                <strong>{{ $supplier->updated_at->format('d M Y') }}</strong>
            </div>
        </div>
    </div>

    <div class="details-grid">
        <!-- Basic Information -->
        <div class="detail-card">
            <h3 class="card-title">Basic Information</h3>
            <div class="detail-row">
                <span class="detail-label">Supplier Name</span>
                <span class="detail-value">{{ $supplier->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact Person</span>
                <span class="detail-value">{{ $supplier->contact_person ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email Address</span>
                <span class="detail-value">{{ $supplier->email ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone Number</span>
                <span class="detail-value">{{ $supplier->phone }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Alternate Phone</span>
                <span class="detail-value">{{ $supplier->alternate_phone ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Address Information -->
        <div class="detail-card">
            <h3 class="card-title">Address Information</h3>
            <div class="detail-row">
                <span class="detail-label">Complete Address</span>
                <span class="detail-value">{{ $supplier->address ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">City</span>
                <span class="detail-value">{{ $supplier->city ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">State</span>
                <span class="detail-value">{{ $supplier->state ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Pincode</span>
                <span class="detail-value">{{ $supplier->pincode ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Country</span>
                <span class="detail-value">{{ $supplier->country ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <div class="details-grid">
        <!-- Tax & Legal Information -->
        <div class="detail-card">
            <h3 class="card-title">Tax & Legal Information</h3>
            <div class="detail-row">
                <span class="detail-label">GSTIN</span>
                <span class="detail-value">{{ $supplier->gstin ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">PAN Number</span>
                <span class="detail-value">{{ $supplier->pan ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Bank & Payment Information -->
        <div class="detail-card">
            <h3 class="card-title">Bank & Payment Information</h3>
            <div class="detail-row">
                <span class="detail-label">Bank Name</span>
                <span class="detail-value">{{ $supplier->bank_name ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Account Number</span>
                <span class="detail-value">{{ $supplier->account_number ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">IFSC Code</span>
                <span class="detail-value">{{ $supplier->ifsc_code ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Terms</span>
                <span class="detail-value">{{ $supplier->payment_terms ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Credit Limit</span>
                <span class="detail-value">₹{{ number_format($supplier->credit_limit, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Recent Purchase Orders -->
    <div class="items-card">
        <div class="card-header">
            <h3 class="card-title">Recent Purchase Orders</h3>
            <a href="{{ route('admin.purchase-orders.index') }}?supplier_id={{ $supplier->id }}" class="card-action">
                View All
            </a>
        </div>
        @if($purchaseOrders->count() > 0)
        <div class="table-responsive">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrders as $po)
                    <tr>
                        <td><strong>{{ $po->po_number }}</strong></td>
                        <td>{{ $po->po_date->format('d M Y') }}</td>
                        <td>
                            <span class="status-badge status-{{ $po->status }}">
                                {{ ucfirst($po->status) }}
                            </span>
                        </td>
                        <td><strong>₹{{ number_format($po->total_amount, 2) }}</strong></td>
                        <td>
                            <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="action-link">
                                View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="no-data">
            <p>No purchase orders found for this supplier.</p>
            @if($supplier->status == 'active')
            <a href="{{ route('admin.purchase-orders.create') }}?supplier_id={{ $supplier->id }}" class="btn-secondary">
                Create First Purchase Order
            </a>
            @endif
        </div>
        @endif
    </div>

    <!-- Additional Notes -->
    @if($supplier->notes)
    <div class="notes-card">
        <h3 class="card-title">Additional Notes</h3>
        <div class="notes-content">
            <p>{{ $supplier->notes }}</p>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .po-details-container { padding: 20px; max-width: 1400px; margin: 0 auto; }

    /* Header */
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; }
    .back-btn:hover { background: #f7fafc; color: #667eea; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; margin: 0; margin-bottom: 10px; }
    .header-right { display: flex; gap: 15px; flex-wrap: wrap; }

    /* Buttons */
    .btn-primary, .btn-secondary { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 10px; font-weight: 600; text-decoration: none; transition: all 0.3s; border: none; cursor: pointer; font-size: 14px; }
    .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
    .btn-secondary { background: #e2e8f0; color: #4a5568; }
    .btn-primary:hover, .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }

    /* Status Card */
    .status-card { background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .status-info { display: flex; gap: 30px; flex-wrap: wrap; }
    .status-item { display: flex; flex-direction: column; gap: 5px; }
    .status-item .label { font-size: 12px; color: #718096; font-weight: 600; text-transform: uppercase; }
    .status-badge { padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; display: inline-block; }
    .status-active { background: #c6f6d5; color: #22543d; }
    .status-inactive { background: #fed7d7; color: #9b2c2c; }
    .status-pending { background: #fefcbf; color: #744210; }
    .status-approved { background: #bee3f8; color: #2c5282; }
    .status-completed { background: #c6f6d5; color: #22543d; }
    .status-cancelled { background: #fed7d7; color: #9b2c2c; }

    /* Details Grid */
    .details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin-bottom: 25px; }
    @media (max-width: 768px) { .details-grid { grid-template-columns: 1fr; } }

    /* Detail Cards */
    .detail-card, .items-card, .notes-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
    .card-title { font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e2e8f0; }
    .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .card-action { font-size: 14px; color: #667eea; text-decoration: none; font-weight: 600; }

    /* Detail Rows */
    .detail-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f7fafc; }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { font-size: 14px; color: #718096; font-weight: 600; }
    .detail-value { font-size: 14px; color: #2d3748; font-weight: 500; text-align: right; max-width: 60%; }

    /* Table */
    .table-responsive { overflow-x: auto; }
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table thead { background: #f7fafc; }
    .items-table th { padding: 12px; text-align: left; font-size: 13px; font-weight: 700; color: #4a5568; border-bottom: 2px solid #e2e8f0; }
    .items-table td { padding: 12px; border-bottom: 1px solid #f7fafc; font-size: 14px; color: #2d3748; }
    .items-table tr:hover { background: #f7fafc; }
    .action-link { color: #667eea; text-decoration: none; font-weight: 600; }
    .action-link:hover { text-decoration: underline; }

    /* No Data */
    .no-data { text-align: center; padding: 40px 20px; }
    .no-data p { color: #a0aec0; margin-bottom: 20px; font-size: 16px; }

    /* Notes */
    .notes-content { font-size: 14px; color: #4a5568; line-height: 1.6; white-space: pre-wrap; }
</style>
@endpush
@endsection
