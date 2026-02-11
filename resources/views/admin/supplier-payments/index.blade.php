
@extends('layouts.admin')
@section('title','Supplier Payments')
@section('content')
<div class="payments-container">
<div class="header">
<h2>Supplier Payments</h2>
<a href="{{route('admin.supplier-payments.create')}}" class="btn-primary">+ Record Payment</a>
</div>
<div class="stats">
<div class="stat"><div class="icon">💵</div><div><h3>{{$payments->count()}}</h3><p>Total Payments</p></div></div>
<div class="stat"><div class="icon">💰</div><div><h3>₹{{number_format($payments->sum('amount'),2)}}</h3><p>Total Amount</p></div></div>
<div class="stat"><div class="icon">📅</div><div><h3>{{$payments->where('created_at','>=',now()->startOfMonth())->count()}}</h3><p>This Month</p></div></div>
</div>
<div class="filters">
<input type="text" id="search" placeholder="Search payments...">
<select id="modeFilter">
<option value="">All Modes</option>
<option value="cash">Cash</option>
<option value="upi">UPI</option>
<option value="bank">Bank Transfer</option>
<option value="cheque">Cheque</option>
</select>
<select id="supplierFilter">
<option value="">All Suppliers</option>
@foreach($suppliers as $s)
<option value="{{$s->id}}">{{$s->name}}</option>
@endforeach
</select>
</div>
<div class="table-wrap">
<table class="data-table">
<thead><tr><th>Payment No</th><th>Date</th><th>Supplier</th><th>Purchase Ref</th><th>Amount</th><th>Mode</th><th>Reference</th><th>Actions</th></tr></thead>
<tbody>
@forelse($payments as $p)
<tr data-mode="{{$p->payment_mode}}" data-supplier="{{$p->supplier_id}}">
<td><strong>{{$p->payment_no}}</strong></td>
<td>{{date('d M Y',strtotime($p->payment_date))}}</td>
<td>{{$p->supplier->name}}<br><small>{{$p->supplier->supplier_code}}</small></td>
<td>
@if($p->purchase)
<a href="{{route('admin.purchases.show',$p->purchase_id)}}" class="link">{{$p->purchase->purchase_no}}</a>
@else
<span class="badge">General Payment</span>
@endif
</td>
<td><strong class="amount">₹{{number_format($p->amount,2)}}</strong></td>
<td><span class="badge mode-{{$p->payment_mode}}">{{ucfirst($p->payment_mode)}}</span></td>
<td>{{$p->reference_no??'N/A'}}</td>
<td class="actions">
<a href="{{route('admin.suppliers.ledger',$p->supplier_id)}}" class="btn-icon" title="Ledger">📊</a>
<form action="{{route('admin.supplier-payments.destroy',$p->id)}}" method="POST" style="display:inline" onsubmit="return confirm('Delete this payment? Ledger will be reversed.')">
@csrf @method('DELETE')
<button type="submit" class="btn-icon btn-del" title="Delete">🗑️</button>
</form>
</td>
</tr>
@empty
<tr><td colspan="8" class="empty">No payments found</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@push('styles')
<style>.payments-container{padding-bottom:40px}.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}.btn-primary{padding:12px 24px;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border-radius:10px;text-decoration:none}.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:30px}.stat{background:white;padding:25px;border-radius:12px;display:flex;gap:20px;box-shadow:0 4px 20px rgba(0,0,0,0.05)}.icon{font-size:42px}.filters{background:white;padding:20px;border-radius:12px;margin-bottom:20px;display:flex;gap:15px}input,select{padding:12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px}.table-wrap{background:white;border-radius:12px;overflow:hidden}.data-table{width:100%;border-collapse:collapse}.data-table thead{background:linear-gradient(135deg,#667eea,#764ba2);color:white}.data-table th{padding:16px;text-align:left}.data-table td{padding:16px;border-bottom:1px solid #e2e8f0}.link{color:#4299e1;text-decoration:none}.link:hover{text-decoration:underline}.badge{padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:#e2e8f0;color:#4a5568}.mode-cash{background:#c6f6d5;color:#22543d}.mode-upi{background:#bee3f8;color:#2c5282}.mode-bank{background:#e9d8fd;color:#553c9a}.mode-cheque{background:#feebc8;color:#7c2d12}.amount{color:#38a169;font-size:16px}.actions{display:flex;gap:8px}.btn-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;text-decoration:none;font-size:16px;background:#ebf8ff;color:#2c5282}.btn-del{background:#fed7d7;color:#9b2c2c}</style>
@endpush
@push('scripts')
<script>
document.getElementById('search').addEventListener('input',e=>{const v=e.target.value.toLowerCase();document.querySelectorAll('tbody tr').forEach(r=>r.style.display=r.textContent.toLowerCase().includes(v)?'':'none')});document.getElementById('modeFilter').addEventListener('change',e=>{const m=e.target.value;document.querySelectorAll('tbody tr').forEach(r=>r.style.display=!m||r.dataset.mode===m?'':'none')});document.getElementById('supplierFilter').addEventListener('change',e=>{const s=e.target.value;document.querySelectorAll('tbody tr').forEach(r=>r.style.display=!s||r.dataset.supplier===s?'':'none')});
</script>
@endpush
@endsection
