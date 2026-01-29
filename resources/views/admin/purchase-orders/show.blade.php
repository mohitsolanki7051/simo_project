@extends('layouts.admin')

@section('title', 'Purchase Order Details - Admin Panel')
@section('header-title', 'Purchase Order #' . $purchaseOrder->po_number)

@section('content')
<div class="po-details-container">
    <div class="page-header">
        <div class="header-left">
            <a href="{{ url('/admin/purchase-orders') }}" class="back-btn">
                <span>←</span> Back to Purchase Orders
            </a>
            <h2 class="page-title">Purchase Order #{{ $purchaseOrder->po_number }}</h2>
        </div>
        <div class="header-right">
            @if($purchaseOrder->canEdit())
            <a href="{{ url('/admin/purchase-orders/' . $purchaseOrder->id . '/edit') }}" class="btn-secondary">
                <span>✏️</span> Edit
            </a>
            @endif
            <a href="{{ url('/admin/purchase-orders/' . $purchaseOrder->id . '/invoice') }}" class="btn-primary" target="_blank">
                <span>📄</span> View Invoice
            </a>
        </div>
    </div>

    <!-- Status & Actions Card -->
    <div class="status-card">
        <div class="status-info">
            <div class="status-item">
                <span class="label">Status:</span>
                {!! $purchaseOrder->status_badge !!}
            </div>
            <div class="status-item">
                <span class="label">Payment:</span>
                {!! $purchaseOrder->payment_status_badge !!}
            </div>
            <div class="status-item">
                <span class="label">PO Date:</span>
                <strong>{{ \Carbon\Carbon::parse($purchaseOrder->po_date)->format('d M Y') }}</strong>
            </div>
            <div class="status-item">
                <span class="label">Expected Delivery:</span>
                <strong>{{ \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('d M Y') }}</strong>
            </div>
        </div>
        <div class="action-buttons">
            @if($purchaseOrder->canApprove())
            <form action="{{ url('/admin/purchase-orders/' . $purchaseOrder->id . '/approve') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-approve" onclick="return confirm('Approve this purchase order?')">
                    <span>✓</span> Approve
                </button>
            </form>
            @endif
            @if($purchaseOrder->canReceive())
            <form action="{{ url('/admin/purchase-orders/' . $purchaseOrder->id . '/receive') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-receive" onclick="return confirm('Mark as received and update stock?')">
                    <span>📦</span> Mark as Received
                </button>
            </form>
            @endif
            @if($purchaseOrder->canCancel())
            <button class="btn-cancel" onclick="showCancelModal()">
                <span>✕</span> Cancel PO
            </button>
            @endif
        </div>
    </div>

    <div class="details-grid">
        <!-- Supplier Details -->
        <div class="detail-card">
            <h3 class="card-title">👤 Supplier Details</h3>
            <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span class="detail-value">{{ $purchaseOrder->supplier_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value">{{ $purchaseOrder->supplier_email ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span class="detail-value">{{ $purchaseOrder->supplier_phone ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Address:</span>
                <span class="detail-value">{{ $purchaseOrder->supplier_address ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Purchase Details -->
        <div class="detail-card">
            <h3 class="card-title">📋 Purchase Details</h3>
            <div class="detail-row">
                <span class="detail-label">Warehouse:</span>
                <span class="detail-value">{{ $purchaseOrder->warehouse->name ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Reference:</span>
                <span class="detail-value">{{ $purchaseOrder->reference_number ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Terms:</span>
                <span class="detail-value">{{ $purchaseOrder->payment_terms }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Shipping Method:</span>
                <span class="detail-value">{{ $purchaseOrder->shipping_method ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="items-card">
        <h3 class="card-title">📦 Purchase Order Items</h3>
        <div class="table-responsive">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>SKU</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>GST</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $item['product_name'] }}</strong></td>
                        <td>{{ $item['variant_name'] }}</td>
                        <td>{{ $item['sku_code'] }}</td>
                        <td>{{ $item['unit'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>₹{{ number_format($item['unit_price'], 2) }}</td>
                        <td>{{ $item['gst'] }}%</td>
                        <td><strong>₹{{ number_format($item['total'], 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="summary-card">
        <h3 class="card-title">💰 Order Summary</h3>
        <div class="summary-details">
            <div class="summary-row">
                <span>Subtotal:</span>
                <strong>{{ $purchaseOrder->formatted_subtotal }}</strong>
            </div>
            <div class="summary-row">
                <span>Tax Amount:</span>
                <strong>{{ $purchaseOrder->formatted_tax_amount }}</strong>
            </div>
            @if($purchaseOrder->discount_amount > 0)
            <div class="summary-row">
                <span>Discount ({{ $purchaseOrder->discount_type }}):</span>
                <strong class="text-danger">- {{ $purchaseOrder->formatted_discount_amount }}</strong>
            </div>
            @endif
            @if($purchaseOrder->shipping_charges > 0)
            <div class="summary-row">
                <span>Shipping Charges:</span>
                <strong>₹{{ number_format($purchaseOrder->shipping_charges, 2) }}</strong>
            </div>
            @endif
            @if($purchaseOrder->other_charges > 0)
            <div class="summary-row">
                <span>Other Charges:</span>
                <strong>₹{{ number_format($purchaseOrder->other_charges, 2) }}</strong>
            </div>
            @endif
            <div class="summary-row total-row">
                <span>Grand Total:</span>
                <strong>{{ $purchaseOrder->formatted_total_amount }}</strong>
            </div>
        </div>
    </div>

    <!-- Terms & Notes -->
    @if($purchaseOrder->terms_conditions || $purchaseOrder->notes)
    <div class="notes-card">
        @if($purchaseOrder->terms_conditions)
        <div class="notes-section">
            <h4>Terms & Conditions</h4>
            <p>{{ $purchaseOrder->terms_conditions }}</p>
        </div>
        @endif
        @if($purchaseOrder->notes)
        <div class="notes-section">
            <h4>Internal Notes</h4>
            <p>{{ $purchaseOrder->notes }}</p>
        </div>
        @endif
    </div>
    @endif
</div>

<!-- Cancel Modal -->
<div id="cancelModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Cancel Purchase Order</h3>
            <button onclick="closeCancelModal()" class="modal-close">×</button>
        </div>
        <form action="{{ url('/admin/purchase-orders/' . $purchaseOrder->id . '/cancel') }}" method="POST">
            @csrf
            <div class="modal-body">
                <label class="form-label">Reason for Cancellation <span class="required">*</span></label>
                <textarea name="cancellation_reason" class="form-textarea" rows="4" required placeholder="Enter reason..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCancelModal()">Close</button>
                <button type="submit" class="btn-danger">Cancel Purchase Order</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .po-details-container { padding: 20px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; margin-bottom: 15px; }
    .back-btn:hover { background: #f7fafc; color: #667eea; }
    .page-title { font-size: 28px; font-weight: 700; color: #2d3748; }
    .header-right { display: flex; gap: 15px; }
    .btn-primary, .btn-secondary { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 10px; font-weight: 600; text-decoration: none; transition: all 0.3s; border: none; cursor: pointer; }
    .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
    .btn-secondary { background: #e2e8f0; color: #4a5568; }
    .btn-primary:hover { transform: translateY(-2px); }

    .status-card { background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
    .status-info { display: flex; gap: 30px; flex-wrap: wrap; }
    .status-item { display: flex; flex-direction: column; gap: 5px; }
    .status-item .label { font-size: 12px; color: #718096; font-weight: 600; text-transform: uppercase; }
    .action-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
    .btn-approve, .btn-receive, .btn-cancel { padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
    .btn-approve { background: #48bb78; color: white; }
    .btn-receive { background: #4299e1; color: white; }
    .btn-cancel { background: #fc8181; color: white; }
    .btn-approve:hover, .btn-receive:hover, .btn-cancel:hover { transform: translateY(-2px); }

    .details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin-bottom: 25px; }
    .detail-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .card-title { font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e2e8f0; }
    .detail-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f7fafc; }
    .detail-label { font-size: 14px; color: #718096; font-weight: 600; }
    .detail-value { font-size: 14px; color: #2d3748; font-weight: 500; text-align: right; max-width: 60%; }

    .items-card, .summary-card, .notes-card { background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table thead { background: #f7fafc; }
    .items-table th { padding: 12px; text-align: left; font-size: 13px; font-weight: 700; color: #4a5568; border-bottom: 2px solid #e2e8f0; }
    .items-table td { padding: 12px; border-bottom: 1px solid #f7fafc; font-size: 14px; color: #2d3748; }

    .summary-details { max-width: 400px; margin-left: auto; }
    .summary-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f7fafc; }
    .summary-row.total-row { font-size: 18px; font-weight: 700; padding-top: 15px; margin-top: 10px; border-top: 2px solid #e2e8f0; border-bottom: none; color: #2d3748; }
    .text-danger { color: #fc8181; }

    .notes-section { margin-bottom: 20px; }
    .notes-section h4 { font-size: 16px; font-weight: 700; color: #2d3748; margin-bottom: 10px; }
    .notes-section p { font-size: 14px; color: #4a5568; line-height: 1.6; white-space: pre-wrap; }

    .modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; }
    .modal-content { background: white; border-radius: 12px; width: 90%; max-width: 500px; }
    .modal-header { padding: 20px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { font-size: 20px; font-weight: 700; color: #2d3748; }
    .modal-close { background: none; border: none; font-size: 32px; color: #718096; cursor: pointer; }
    .modal-body { padding: 20px; }
    .modal-footer { padding: 20px; border-top: 2px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; }
    .form-textarea { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; font-family: inherit; }
    .btn-danger { padding: 10px 20px; background: #fc8181; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }

    @media (max-width: 768px) { .details-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@push('scripts')
<script>
    function showCancelModal() {
        document.getElementById('cancelModal').style.display = 'flex';
    }
    function closeCancelModal() {
        document.getElementById('cancelModal').style.display = 'none';
    }
</script>
@endpush
@endsection
