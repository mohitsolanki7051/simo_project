<!DOCTYPE html>
<html>
<head>
<title>Purchase Invoice - {{$purchase->purchase_no}}</title>
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;padding:40px;background:#f5f5f5}.invoice{background:white;max-width:900px;margin:0 auto;padding:40px;box-shadow:0 0 20px rgba(0,0,0,0.1)}.header{display:flex;justify-content:space-between;margin-bottom:40px;padding-bottom:30px;border-bottom:3px solid #667eea}.company h1{color:#667eea;font-size:32px;margin-bottom:5px}.company p{color:#666;font-size:14px}.invoice-info{text-align:right}.invoice-info h2{color:#333;font-size:24px;margin-bottom:10px}.invoice-info p{color:#666;font-size:14px;margin:5px 0}.badge{display:inline-block;padding:6px 16px;border-radius:20px;font-size:12px;font-weight:700;margin-top:10px}.status-confirmed{background:#c6f6d5;color:#22543d}.status-draft{background:#feebc8;color:#7c2d12}.details{display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-bottom:40px}.section h3{color:#667eea;font-size:16px;margin-bottom:15px;text-transform:uppercase;letter-spacing:1px}.section p{color:#333;font-size:14px;margin:8px 0;line-height:1.6}table{width:100%;border-collapse:collapse;margin-bottom:30px}thead{background:#667eea;color:white}th{padding:15px;text-align:left;font-weight:600;font-size:13px;text-transform:uppercase}td{padding:15px;border-bottom:1px solid #e0e0e0;font-size:14px}.text-right{text-align:right}.summary{margin-left:auto;width:300px;margin-top:20px}.summary-row{display:flex;justify-content:space-between;padding:12px;background:#f9f9f9;margin-bottom:8px;border-radius:6px}.summary-row.total{background:#fef5e7;font-size:18px;font-weight:700;color:#c05621;padding:20px}.notes{margin-top:40px;padding:20px;background:#f9f9f9;border-radius:8px}.notes h3{color:#667eea;margin-bottom:10px}.footer{margin-top:60px;padding-top:30px;border-top:2px solid #e0e0e0;text-align:center;color:#999;font-size:13px}@media print{body{padding:0;background:white}.invoice{box-shadow:none;padding:20px}}</style>
</head>
<body>
<div class="invoice">
<div class="header">
<div class="company">
<h1>{{config('app.name','Your Company')}}</h1>
<p>123 Business Street</p>
<p>City, State 12345</p>
<p>Phone: +91 1234567890</p>
<p>Email: info@company.com</p>
</div>
<div class="invoice-info">
<h2>PURCHASE INVOICE</h2>
<p><strong>Purchase No:</strong> {{$purchase->purchase_no}}</p>
<p><strong>Date:</strong> {{date('d M Y',strtotime($purchase->purchase_date))}}</p>
@if($purchase->invoice_no)
<p><strong>Invoice No:</strong> {{$purchase->invoice_no}}</p>
@endif
<span class="badge status-{{$purchase->status}}">{{ucfirst($purchase->status)}}</span>
</div>
</div>
<div class="details">
<div class="section">
<h3>Supplier Details</h3>
<p><strong>{{$purchase->supplier->name}}</strong></p>
<p>{{$purchase->supplier->supplier_code}}</p>
@if($purchase->supplier->address)
<p>{{$purchase->supplier->address}}</p>
<p>{{$purchase->supplier->city}}, {{$purchase->supplier->state}} {{$purchase->supplier->pincode}}</p>
@endif
@if($purchase->supplier->phone)
<p>Phone: {{$purchase->supplier->phone}}</p>
@endif
@if($purchase->supplier->email)
<p>Email: {{$purchase->supplier->email}}</p>
@endif
@if($purchase->supplier->gstin)
<p>GSTIN: {{$purchase->supplier->gstin}}</p>
@endif
</div>
<div class="section">
<h3>Payment Information</h3>
<p><strong>Payment Terms:</strong> {{$purchase->supplier->payment_terms??'N/A'}}</p>
<p><strong>Payment Status:</strong> <span style="text-transform:uppercase;font-weight:700;color:{{$purchase->payment_status==='paid'?'#22543d':($purchase->payment_status==='partial'?'#ed8936':'#9b2c2c')}}">{{$purchase->payment_status}}</span></p>
<p><strong>Amount Paid:</strong> ₹{{number_format($purchase->paid_amount,2)}}</p>
<p><strong>Amount Due:</strong> ₹{{number_format($purchase->due_amount,2)}}</p>
</div>
</div>
<table>
<thead>
<tr><th>Product</th><th>Variant/SKU</th><th class="text-right">Qty</th><th class="text-right">Rate</th><th class="text-right">Tax</th><th class="text-right">Total</th></tr>
</thead>
<tbody>
@foreach($purchase->items as $item)
<tr>
<td>{{$item->product_name}}</td>
<td>{{$item->variant_name}}<br><small style="color:#999">SKU: {{$item->sku_code}}</small></td>
<td class="text-right">{{$item->qty}} {{$item->unit}}</td>
<td class="text-right">₹{{number_format($item->rate,2)}}</td>
<td class="text-right">{{$item->tax_percent}}%<br><small style="color:#999">₹{{number_format($item->tax_amount,2)}}</small></td>
<td class="text-right"><strong>₹{{number_format($item->total,2)}}</strong></td>
</tr>
@endforeach
</tbody>
</table>
<div class="summary">
<div class="summary-row"><span>Subtotal:</span><span>₹{{number_format($purchase->subtotal,2)}}</span></div>
@if($purchase->discount>0)
<div class="summary-row"><span>Discount:</span><span>-₹{{number_format($purchase->discount,2)}}</span></div>
@endif
<div class="summary-row"><span>Tax:</span><span>₹{{number_format($purchase->tax_amount,2)}}</span></div>
@if($purchase->shipping_charge>0)
<div class="summary-row"><span>Shipping:</span><span>₹{{number_format($purchase->shipping_charge,2)}}</span></div>
@endif
<div class="summary-row total"><span>Grand Total:</span><span>₹{{number_format($purchase->grand_total,2)}}</span></div>
</div>
@if($purchase->notes)
<div class="notes"><h3>Notes</h3><p>{{$purchase->notes}}</p></div>
@endif
<div class="footer">
<p>Thank you for your business!</p>
<p>This is a computer-generated invoice.</p>
</div>
</div>
<script>window.onload=()=>window.print();</script>
</body>
</html>
