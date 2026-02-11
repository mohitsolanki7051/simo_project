@extends('layouts.admin')
@section('title','Record Payment')
@section('content')
<div class="payment-container">
<div class="header">
<a href="{{route('admin.supplier-payments.index')}}" class="back">← Back</a>
<h2>Record Supplier Payment</h2>
</div>
<form action="{{route('admin.supplier-payments.store')}}" method="POST" id="paymentForm">
@csrf
<div class="row">
<div class="col-8">
<div class="card">
<h3>💵 Payment Details</h3>
<div class="grid">
<div class="full">
<label>Supplier <span class="req">*</span></label>
<select name="supplier_id" id="supplier_id" required onchange="loadPendingPurchases()">
<option value="">Select Supplier</option>
@foreach($suppliers as $s)
<option value="{{$s->id}}" {{request('supplier_id')==$s->id?'selected':''}}>{{$s->name}} - {{$s->supplier_code}}</option>
@endforeach
</select>
</div>
<div>
<label>Amount (₹) <span class="req">*</span></label>
<input type="number" step="0.01" name="amount" id="amount" value="{{old('amount')}}" required min="0.01">
</div>
<div>
<label>Payment Date <span class="req">*</span></label>
<input type="date" name="payment_date" value="{{old('payment_date',date('Y-m-d'))}}" required>
</div>
<div>
<label>Payment Mode <span class="req">*</span></label>
<select name="payment_mode" id="payment_mode" required onchange="toggleModeFields()">
<option value="cash">Cash</option>
<option value="upi">UPI</option>
<option value="bank">Bank Transfer</option>
<option value="cheque">Cheque</option>
</select>
</div>
<div>
<label>Reference No</label>
<input name="reference_no" id="reference_no" value="{{old('reference_no')}}" placeholder="Transaction ID / UTR">
</div>
<div id="chequeFields" style="display:none" class="full">
<div class="grid">
<div><label>Cheque No</label><input name="cheque_no" id="cheque_no"></div>
<div><label>Cheque Date</label><input type="date" name="cheque_date" id="cheque_date"></div>
<div><label>Bank Name</label><input name="bank_name" id="bank_name"></div>
</div>
</div>
<div class="full">
<label>Link to Purchase (Optional)</label>
<select name="purchase_id" id="purchase_id">
<option value="">General Payment (Not linked to purchase)</option>
</select>
</div>
<div class="full">
<label>Note</label>
<textarea name="note" rows="3">{{old('note')}}</textarea>
</div>
</div>
</div>
</div>
<div class="col-4">
<div class="card supplier-info" id="supplierInfo" style="display:none">
<h3>📊 Supplier Balance</h3>
<div class="balance-card">
<div class="label">Current Balance</div>
<div class="value" id="currentBalance">₹0.00</div>
</div>
<div class="pending-section" id="pendingSection" style="display:none">
<h4>Pending Purchases</h4>
<div id="pendingList"></div>
</div>
</div>
<div class="card">
<h3>💰 Payment Summary</h3>
<div class="summary">
<div class="row"><span>Payment Amount:</span><span id="paymentAmount">₹0.00</span></div>
<div class="row"><span>Mode:</span><span id="paymentMode">Cash</span></div>
<div class="row"><span>Date:</span><span id="paymentDateDisplay">{{date('d M Y')}}</span></div>
</div>
</div>
<div class="actions">
<button type="button" onclick="window.history.back()" class="btn-cancel">Cancel</button>
<button type="submit" class="btn-save">Record Payment</button>
</div>
</div>
</div>
</form>
</div>
@push('styles')
<style>.payment-container{padding-bottom:40px}.header{margin-bottom:30px}.back{color:#718096;text-decoration:none;padding:8px 16px;border-radius:8px;display:inline-block;margin-bottom:10px}.back:hover{background:#f7fafc}.row{display:grid;grid-template-columns:2fr 1fr;gap:20px}.card{background:white;border-radius:16px;padding:30px;margin-bottom:20px;box-shadow:0 4px 20px rgba(0,0,0,0.05)}.card h3{margin-bottom:20px}.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px}.full{grid-column:span 2}label{display:block;margin-bottom:8px;font-weight:600;color:#4a5568}.req{color:#fc8181}input,select,textarea{width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px}input:focus,select:focus,textarea:focus{outline:none;border-color:#667eea}.balance-card{background:linear-gradient(135deg,#ebf8ff,#bee3f8);border:2px solid #4299e1;border-radius:12px;padding:20px;text-align:center}.label{font-size:13px;color:#2c5282;font-weight:600;margin-bottom:5px}.value{font-size:32px;font-weight:700;color:#2d3748}.pending-section{margin-top:20px;padding:20px;background:#f7fafc;border-radius:12px}.pending-section h4{font-size:14px;margin-bottom:15px}.pending-item{padding:12px;background:white;border-radius:8px;margin-bottom:10px;border-left:3px solid #ed8936}.pending-item label{display:flex;justify-content:space-between;cursor:pointer}.summary .row{display:flex;justify-content:space-between;margin-bottom:15px;padding:12px;background:#f7fafc;border-radius:8px}.actions{display:flex;justify-content:flex-end;gap:15px;margin-top:20px}.btn-cancel{padding:12px 24px;background:#e2e8f0;border:none;border-radius:10px;cursor:pointer}.btn-save{padding:12px 30px;background:linear-gradient(135deg,#38a169,#68d391);color:white;border:none;border-radius:10px;cursor:pointer}@media(max-width:1024px){.row{grid-template-columns:1fr}}</style>
@endpush
@push('scripts')
<script>
const suppliers=@json($suppliers->keyBy('id'));function toggleModeFields(){const mode=document.getElementById('payment_mode').value;document.getElementById('chequeFields').style.display=mode==='cheque'?'block':'none';document.getElementById('paymentMode').textContent=mode.toUpperCase()}function loadPendingPurchases(){const supplierId=document.getElementById('supplier_id').value;if(!supplierId){document.getElementById('supplierInfo').style.display='none';return}const supplier=suppliers[supplierId];document.getElementById('supplierInfo').style.display='block';document.getElementById('currentBalance').textContent=supplier.formatted_current_balance||'₹0.00';fetch(`/admin/supplier-payments/pending-purchases/${supplierId}`).then(r=>r.json()).then(data=>{const select=document.getElementById('purchase_id');select.innerHTML='<option value="">General Payment</option>';const pendingList=document.getElementById('pendingList');pendingList.innerHTML='';if(data.purchases&&data.purchases.length>0){document.getElementById('pendingSection').style.display='block';data.purchases.forEach(p=>{select.innerHTML+=`<option value="${p.id}">${p.purchase_no} - Due: ₹${parseFloat(p.due_amount).toFixed(2)}</option>`;pendingList.innerHTML+=`<div class="pending-item"><label><input type="radio" name="select_purchase" value="${p.id}" onchange="selectPurchase(${p.id},${p.due_amount})"> ${p.purchase_no}<span>₹${parseFloat(p.due_amount).toFixed(2)}</span></label></div>`})}else{document.getElementById('pendingSection').style.display='none'}})}function selectPurchase(id,amount){document.getElementById('purchase_id').value=id;document.getElementById('amount').value=amount.toFixed(2);updateSummary()}function updateSummary(){const amt=parseFloat(document.getElementById('amount').value)||0;document.getElementById('paymentAmount').textContent=`₹${amt.toFixed(2)}`}document.getElementById('amount').addEventListener('input',updateSummary);document.getElementById('payment_mode').addEventListener('change',toggleModeFields);@if(request('supplier_id'))loadPendingPurchases();@endif;@if(request('purchase_id'))setTimeout(()=>{document.getElementById('purchase_id').value='{{request('purchase_id')}}'},500);@endif;updateSummary();
</script>
@endpush
@endsection
