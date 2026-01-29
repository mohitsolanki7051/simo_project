{{--
    EDIT VIEW: The edit view is very similar to create view.
    For simplicity, you can reuse the create.blade.php with minor modifications.
    Here's what you need to change:
--}}

@extends('layouts.admin')

@section('title', 'Edit Purchase Order - Admin Panel')
@section('header-title', 'Edit Purchase Order #' . $purchaseOrder->po_number)

@section('content')
{{--
    IMPLEMENTATION NOTE:
    The edit view is 95% similar to create view.

    Copy create.blade.php and make these changes:

    1. Change form action:
       FROM: action="{{ url('/admin/purchase-orders') }}"
       TO:   action="{{ url('/admin/purchase-orders/' . $purchaseOrder->id . '/update') }}"

    2. Pre-fill all form fields with $purchaseOrder data:
       - Replace old('field') with old('field', $purchaseOrder->field)
       - Example: value="{{ old('po_date', $purchaseOrder->po_date) }}"

    3. Pre-load items on page load:
       Add this in the JavaScript section:

       document.addEventListener('DOMContentLoaded', function() {
           // Load existing items
           @foreach($purchaseOrder->items as $index => $item)
               addExistingItem({
                   product_id: '{{ $item['product_id'] }}',
                   variant_index: {{ $item['variant_index'] }},
                   quantity: {{ $item['quantity'] }},
                   unit_price: {{ $item['unit_price'] }},
                   gst: {{ $item['gst'] }}
               });
           @endforeach

           showTab(0);
       });

    4. Change submit button text:
       FROM: Create Purchase Order
       TO:   Update Purchase Order

    5. Add this at top of page:
       @if(!$purchaseOrder->canEdit())
           <div class="alert alert-warning">
               This purchase order cannot be edited.
               <a href="{{ url('/admin/purchase-orders') }}">Back to List</a>
           </div>
       @endif
--}}

<div class="create-po-container">
    <div class="alert-container" id="alertContainer"></div>

    <div class="page-header">
        <div class="header-left">
            <a href="{{ url('/admin/purchase-orders') }}" class="back-btn">
                <span>←</span> Back to Purchase Orders
            </a>
            <h2 class="page-title">Edit Purchase Order #{{ $purchaseOrder->po_number }}</h2>
        </div>
    </div>

    @if(!$purchaseOrder->canEdit())
        <div style="background: #fff3cd; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
            <strong>⚠️ Warning:</strong> This purchase order cannot be edited because its status is "{{ $purchaseOrder->status }}".
            <br>
            <a href="{{ url('/admin/purchase-orders/' . $purchaseOrder->id) }}" style="color: #0066cc; text-decoration: underline;">View Purchase Order</a>
        </div>
    @else
        {{--
            Copy the entire form from create.blade.php here
            and apply the 5 changes mentioned above
        --}}

        <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <p style="color: #718096; margin-bottom: 20px;">
                📝 <strong>Implementation Note:</strong> Copy the entire form structure from <code>create.blade.php</code>
                and make the following changes:
            </p>

            <ol style="color: #4a5568; line-height: 2;">
                <li>Change form action to: <code>{{ url('/admin/purchase-orders/' . $purchaseOrder->id . '/update') }}</code></li>
                <li>Pre-fill all fields with <code>$purchaseOrder</code> data</li>
                <li>Load existing items in JavaScript on page load</li>
                <li>Change button text to "Update Purchase Order"</li>
                <li>Keep the same tab structure and styling</li>
            </ol>

            <div style="margin-top: 30px; padding: 20px; background: #f7fafc; border-radius: 8px;">
                <strong>Quick Reference:</strong>
                <ul style="margin-top: 10px; color: #4a5568;">
                    <li>PO Number: <strong>{{ $purchaseOrder->po_number }}</strong></li>
                    <li>Status: <strong>{{ $purchaseOrder->status }}</strong></li>
                    <li>Total Items: <strong>{{ count($purchaseOrder->items ?? []) }}</strong></li>
                    <li>Total Amount: <strong>₹{{ number_format($purchaseOrder->total_amount, 2) }}</strong></li>
                </ul>
            </div>
        </div>
    @endif
</div>

@endsection
