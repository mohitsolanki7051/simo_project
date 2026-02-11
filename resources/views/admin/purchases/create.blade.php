@extends('layouts.admin')
@section('title','Create Purchase')
@section('content')
<div class="purchase-container">
<div id="alertContainer"></div>
<div class="header">
<a href="{{route('admin.purchases.index')}}" class="back">← Back</a>
<h2>Create Purchase Order</h2>
</div>
<form action="{{route('admin.purchases.store')}}" method="POST" id="purchaseForm">
@csrf
<div class="row">
<div class="col-8">
<div class="card">
<h3>📋 Purchase Details</h3>
<div class="grid">
<div class="full">
<label>Supplier <span class="req">*</span></label>
<select name="supplier_id" id="supplier_id" required>
<option value="">Select Supplier</option>
@foreach($suppliers as $s)
<option value="{{$s->id}}" data-terms="{{$s->payment_terms}}">{{$s->name}} - {{$s->supplier_code}}</option>
@endforeach
</select>
</div>
<div>
<label>Invoice No</label>
<input name="invoice_no" value="{{old('invoice_no')}}">
</div>
<div>
<label>Invoice Date</label>
<input type="date" name="invoice_date" value="{{old('invoice_date',date('Y-m-d'))}}">
</div>
<div>
<label>Purchase Date <span class="req">*</span></label>
<input type="date" name="purchase_date" value="{{old('purchase_date',date('Y-m-d'))}}" required>
</div>
<div>
<label>Payment Terms</label>
<input name="payment_terms" id="payment_terms" value="{{old('payment_terms','Net 30')}}" readonly>
</div>
</div>
</div>
<div class="card">
<div class="items-header">
<h3>🛒 Purchase Items</h3>
<button type="button" onclick="addItem()" class="btn-add">+ Add Item</button>
</div>
<div class="items-table">
<table id="itemsTable">
<thead>
<tr><th>Product</th><th>Variant</th><th>Qty</th><th>Rate</th><th>Tax%</th><th>Total</th><th></th></tr>
</thead>
<tbody id="itemsBody">
<tr class="item-row">
<td>
<select name="items[0][product_id]" class="product-select" onchange="loadVariants(this,0)" required>
<option value="">Select Product</option>
@foreach($products as $p)
<option value="{{$p->id}}">{{$p->name}}</option>
@endforeach
</select>
</td>
<td><select name="items[0][variant_index]" class="variant-select" required><option value="">Select</option></select></td>
<td><input type="number" name="items[0][qty]" class="qty" value="1" min="1" required onchange="calculateRow(0)"></td>
<td><input type="number" name="items[0][rate]" class="rate" value="0" step="0.01" required onchange="calculateRow(0)"></td>
<td><input type="number" name="items[0][tax_percent]" class="tax" value="0" step="0.01" onchange="calculateRow(0)"></td>
<td><input type="number" name="items[0][total]" class="total" value="0" readonly></td>
<td><button type="button" onclick="removeItem(this)" class="btn-remove">×</button></td>
</tr>
</tbody>
</table>
</div>
</div>
</div>
<div class="col-4">
<div class="card summary-card">
<h3>💰 Summary</h3>
<div class="summary">
<div class="row"><span>Subtotal:</span><span id="subtotal">₹0.00</span></div>
<div class="row"><label>Discount:</label><input type="number" name="discount" id="discount" value="0" step="0.01" onchange="calculateTotal()"></div>
<div class="row">
<label>Type:</label>
<select name="discount_type" id="discount_type" onchange="calculateTotal()">
<option value="fixed">Fixed</option>
<option value="percent">Percent</option>
</select>
</div>
<div class="row"><span>Tax:</span><span id="tax_amount">₹0.00</span></div>
<div class="row"><label>Shipping:</label><input type="number" name="shipping_charge" id="shipping" value="0" step="0.01" onchange="calculateTotal()"></div>
<div class="total-row"><span>Grand Total:</span><span id="grand_total">₹0.00</span></div>
<input type="hidden" name="subtotal" id="subtotal_input">
<input type="hidden" name="tax_amount" id="tax_input">
<input type="hidden" name="grand_total" id="grand_input">
</div>
<div class="status-section">
<label>Status</label>
<select name="status" id="status">
<option value="draft">Draft</option>
<option value="confirmed">Confirmed (Update Stock)</option>
</select>
<small>Select "Confirmed" to update inventory and create ledger entry</small>
</div>
</div>
<div class="card">
<label>Notes</label>
<textarea name="notes" rows="3">{{old('notes')}}</textarea>
</div>
<div class="actions">
<button type="button" onclick="window.history.back()" class="btn-cancel">Cancel</button>
<button type="submit" class="btn-save">Create Purchase</button>
</div>
</div>
</div>
</form>
</div>
@push('styles')
<style>.purchase-container{padding-bottom:40px}.header{margin-bottom:30px}.back{color:#718096;text-decoration:none;padding:8px 16px;border-radius:8px;display:inline-block;margin-bottom:10px}.back:hover{background:#f7fafc}.row{display:grid;grid-template-columns:2fr 1fr;gap:20px}.card{background:white;border-radius:16px;padding:30px;margin-bottom:20px;box-shadow:0 4px 20px rgba(0,0,0,0.05)}.card h3{margin-bottom:20px}.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px}.full{grid-column:span 2}label{display:block;margin-bottom:8px;font-weight:600;color:#4a5568}.req{color:#fc8181}input,select,textarea{width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px}input:focus,select:focus,textarea:focus{outline:none;border-color:#667eea}.items-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}.btn-add{padding:8px 16px;background:#38a169;color:white;border:none;border-radius:8px;cursor:pointer}.items-table{overflow-x:auto}.items-table table{width:100%;border-collapse:collapse}.items-table th{background:#f7fafc;padding:12px;text-align:left;font-weight:600;border-bottom:2px solid #e2e8f0}.items-table td{padding:12px;border-bottom:1px solid #e2e8f0}.item-row input,.item-row select{padding:8px;font-size:13px}.btn-remove{width:32px;height:32px;background:#fed7d7;color:#9b2c2c;border:none;border-radius:6px;cursor:pointer;font-size:20px}.summary-card{position:sticky;top:20px}.summary .row{display:flex;justify-content:space-between;margin-bottom:15px;padding:12px;background:#f7fafc;border-radius:8px}.summary input,.summary select{width:120px;padding:8px}.total-row{display:flex;justify-content:space-between;padding:20px;background:linear-gradient(135deg,#fef5e7,#fdebd0);border-radius:12px;font-size:20px;font-weight:700;color:#c05621;margin-top:20px}.status-section{margin-top:20px;padding:20px;background:#ebf8ff;border-radius:12px}.status-section small{display:block;margin-top:8px;color:#2c5282}.actions{display:flex;justify-content:flex-end;gap:15px;margin-top:20px}.btn-cancel{padding:12px 24px;background:#e2e8f0;border:none;border-radius:10px;cursor:pointer}.btn-save{padding:12px 30px;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:10px;cursor:pointer}@media(max-width:1024px){.row{grid-template-columns:1fr}}</style>
@endpush
@push('scripts')
<script>
let itemCount=0;const products=@json($products);function addItem(){itemCount++;const row=`<tr class="item-row"><td><select name="items[${itemCount}][product_id]" class="product-select" onchange="loadVariants(this,${itemCount})" required><option value="">Select Product</option>@foreach($products as $p)<option value="{{$p->id}}">{{$p->name}}</option>@endforeach</select></td><td><select name="items[${itemCount}][variant_index]" class="variant-select" required><option value="">Select</option></select></td><td><input type="number" name="items[${itemCount}][qty]" class="qty" value="1" min="1" required onchange="calculateRow(${itemCount})"></td><td><input type="number" name="items[${itemCount}][rate]" class="rate" value="0" step="0.01" required onchange="calculateRow(${itemCount})"></td><td><input type="number" name="items[${itemCount}][tax_percent]" class="tax" value="0" step="0.01" onchange="calculateRow(${itemCount})"></td><td><input type="number" name="items[${itemCount}][total]" class="total" value="0" readonly></td><td><button type="button" onclick="removeItem(this)" class="btn-remove">×</button></td></tr>`;document.getElementById('itemsBody').insertAdjacentHTML('beforeend',row)}function removeItem(btn){if(document.querySelectorAll('.item-row').length>1){btn.closest('tr').remove();calculateTotal()}}function loadVariants(select,index){const productId=select.value;const product=products.find(p=>p.id==productId);const variantSelect=select.closest('tr').querySelector('.variant-select');variantSelect.innerHTML='<option value="">Select</option>';if(product&&product.variants){product.variants.forEach((v,i)=>{variantSelect.innerHTML+=`<option value="${i}">${v.variant_name||'Default'} - SKU:${v.sku_code}</option>`})}}function calculateRow(index){const row=document.querySelector(`.item-row:nth-child(${index+1})`);const qty=parseFloat(row.querySelector('.qty').value)||0;const rate=parseFloat(row.querySelector('.rate').value)||0;const tax=parseFloat(row.querySelector('.tax').value)||0;const subtotal=qty*rate;const taxAmt=(subtotal*tax)/100;const total=subtotal+taxAmt;row.querySelector('.total').value=total.toFixed(2);calculateTotal()}function calculateTotal(){let subtotal=0;let totalTax=0;document.querySelectorAll('.item-row').forEach(row=>{const qty=parseFloat(row.querySelector('.qty').value)||0;const rate=parseFloat(row.querySelector('.rate').value)||0;const tax=parseFloat(row.querySelector('.tax').value)||0;const itemSubtotal=qty*rate;const itemTax=(itemSubtotal*tax)/100;subtotal+=itemSubtotal;totalTax+=itemTax});const discount=parseFloat(document.getElementById('discount').value)||0;const discountType=document.getElementById('discount_type').value;const shipping=parseFloat(document.getElementById('shipping').value)||0;let discountAmt=discountType==='percent'?(subtotal*discount)/100:discount;let grandTotal=subtotal-discountAmt+totalTax+shipping;document.getElementById('subtotal').textContent=`₹${subtotal.toFixed(2)}`;document.getElementById('tax_amount').textContent=`₹${totalTax.toFixed(2)}`;document.getElementById('grand_total').textContent=`₹${grandTotal.toFixed(2)}`;document.getElementById('subtotal_input').value=subtotal.toFixed(2);document.getElementById('tax_input').value=totalTax.toFixed(2);document.getElementById('grand_input').value=grandTotal.toFixed(2)}document.getElementById('supplier_id').addEventListener('change',function(){const selected=this.options[this.selectedIndex];document.getElementById('payment_terms').value=selected.dataset.terms||'Net 30'});calculateTotal();
</script>
@endpush
@endsection
