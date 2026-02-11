@extends('layouts.admin')
@section('title','Supplier Ledger')
@section('content')
<div class="ledger-container">
<div class="header">
<div><a href="{{route('admin.suppliers.index')}}" class="back">← Back</a><h2>{{$supplier->name}} - Ledger</h2><small>{{$supplier->supplier_code}}</small></div>
<div class="actions"><button onclick="window.print()" class="btn">🖨️ Print</button><a href="{{route('admin.suppliers.edit',$supplier->id)}}" class="btn">✏️ Edit</a></div>
</div>
<div class="summary">
<div class="card {{$supplier->current_balance>0?'due':($supplier->current_balance<0?'adv':'clear')}}">
<div class="icon">💰</div><div><label>Current Balance</label><h3>{{$supplier->formatted_current_balance}}</h3></div>
</div>
<div class="card"><div class="icon">🛒</div><div><label>Total Purchases</label><h3>₹{{number_format($stats['total_purchases']??0,2)}}</h3></div></div>
<div class="card"><div class="icon">💵</div><div><label>Total Payments</label><h3>₹{{number_format($stats['total_payments']??0,2)}}</h3></div></div>
<div class="card"><div class="icon">📊</div><div><label>Terms</label><h3>{{$supplier->payment_terms??'N/A'}}</h3></div></div>
</div>
<div class="filter">
<form method="GET">
<label>From</label><input type="date" name="start_date" value="{{$startDate}}">
<label>To</label><input type="date" name="end_date" value="{{$endDate}}">
<button type="submit" class="btn">🔍 Filter</button>
<a href="{{route('admin.suppliers.ledger',$supplier->id)}}" class="btn">🔄 Reset</a>
</form>
</div>
<div class="table-box">
<h3>📋 Transaction History</h3>
@if($openingBalance!=0&&$startDate)
<div class="opening"><span>Opening Balance</span><span class="{{$openingBalance>=0?'due':'adv'}}">₹{{number_format(abs($openingBalance),2)}} {{$openingBalance>=0?'Dr':'Cr'}}</span></div>
@endif
<table class="ledger-table">
<thead><tr><th>Date</th><th>Type</th><th>Reference</th><th>Description</th><th class="right">Debit</th><th class="right">Credit</th><th class="right">Balance</th></tr></thead>
<tbody>
@forelse($ledgerEntries as $e)
<tr>
<td>{{\Carbon\Carbon::parse($e->date)->format('d M Y')}}</td>
<td><span class="badge {{$e->transaction_type}}">@if($e->transaction_type==='opening')🔵 Opening @elseif($e->transaction_type==='purchase')🛒 Purchase @else💵 Payment @endif</span></td>
<td><strong>{{$e->reference_no}}</strong></td>
<td>{{$e->description}}</td>
<td class="right">@if($e->debit>0)<span class="debit">₹{{number_format($e->debit,2)}}</span>@else-@endif</td>
<td class="right">@if($e->credit>0)<span class="credit">₹{{number_format($e->credit,2)}}</span>@else-@endif</td>
<td class="right"><span class="balance {{$e->balance>=0?'bal-due':'bal-adv'}}">₹{{number_format(abs($e->balance),2)}} {{$e->balance>=0?'Dr':'Cr'}}</span></td>
</tr>
@empty
<tr><td colspan="7" class="empty">No transactions</td></tr>
@endforelse
</tbody>
</table>
@if($ledgerEntries->count()>0)
<div class="closing"><span>Closing Balance</span><span class="{{$supplier->current_balance>=0?'due':'adv'}}">₹{{number_format(abs($supplier->current_balance),2)}} {{$supplier->current_balance>=0?'Dr':'Cr'}}</span></div>
<div class="help">
<h4>📚 Ledger Guide:</h4>
<div class="grid"><div><strong>🔵 Opening:</strong> Starting balance</div><div><strong>🛒 Purchase (Debit):</strong> You buy goods - increases what you owe</div><div><strong>💵 Payment (Credit):</strong> You pay supplier - decreases what you owe</div><div><strong>📊 Balance:</strong> Dr = You owe | Cr = Advance paid</div></div>
</div>
@endif
</div>
<div class="quick">
<h3>⚡ Quick Actions</h3>
<div class="grid">
<a href="{{route('admin.purchases.create')}}?supplier_id={{$supplier->id}}" class="action">🛒 New Purchase</a>
<a href="{{route('admin.supplier-payments.create')}}?supplier_id={{$supplier->id}}" class="action">💵 Make Payment</a>
<a href="{{route('admin.purchases.index')}}?supplier_id={{$supplier->id}}" class="action">📜 View Purchases</a>
<a href="{{route('admin.supplier-payments.index')}}?supplier_id={{$supplier->id}}" class="action">💳 Payments</a>
</div>
</div>
</div>
@push('styles')
<style>.ledger-container{padding-bottom:40px}.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}.back{color:#718096;text-decoration:none;padding:8px 16px;border-radius:8px;display:inline-block;margin-bottom:10px}.back:hover{background:#f7fafc}.actions{display:flex;gap:10px}.btn{padding:10px 20px;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:10px;cursor:pointer;text-decoration:none}.summary{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:30px}.card{background:white;border-radius:16px;padding:25px;display:flex;gap:20px;border-left:4px solid}.due{border-color:#f56565;background:linear-gradient(135deg,#fff5f5,#fed7d7)}.adv{border-color:#48bb78;background:linear-gradient(135deg,#f0fff4,#c6f6d5)}.clear{border-color:#4299e1;background:linear-gradient(135deg,#ebf8ff,#bee3f8)}.icon{font-size:42px}label{font-size:13px;color:#718096;font-weight:600}h3{font-size:28px;font-weight:700;color:#2d3748}.filter{background:white;padding:20px;border-radius:12px;margin-bottom:30px}.filter form{display:flex;gap:15px;align-items:center}input[type="date"]{padding:10px;border:2px solid #e2e8f0;border-radius:8px}.table-box{background:white;border-radius:16px;padding:30px;box-shadow:0 4px 20px rgba(0,0,0,0.05)}.opening,.closing{padding:20px;background:linear-gradient(135deg,#fef5e7,#fdebd0);border:2px solid #f6ad55;border-radius:12px;display:flex;justify-content:space-between;margin-bottom:20px;font-weight:700}.closing{background:linear-gradient(135deg,#e6fffa,#b2f5ea);border-color:#38b2ac}.ledger-table{width:100%;border-collapse:collapse;margin:20px 0}.ledger-table thead{background:linear-gradient(135deg,#667eea,#764ba2);color:white}.ledger-table th{padding:16px;text-align:left}.ledger-table td{padding:16px;border-bottom:1px solid #e2e8f0}.right{text-align:right}.badge{padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600}.opening{background:#e6fffa;color:#234e52}.purchase{background:#fef5e7;color:#7c2d12}.payment{background:#f0fff4;color:#22543d}.debit{color:#c05621;font-weight:700}.credit{color:#22543d;font-weight:700}.balance{font-weight:700;padding:6px 12px;border-radius:6px}.bal-due{color:#c05621;background:#fff5f0}.bal-adv{color:#22543d;background:#f0fff4}.help{background:linear-gradient(135deg,#ebf8ff,#bee3f8);padding:25px;border-radius:12px;margin-top:20px}.help h4{margin-bottom:15px}.help .grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px}.help div{background:white;padding:12px;border-radius:8px;font-size:13px}.quick{background:white;padding:30px;border-radius:16px;margin-top:30px}.quick h3{margin-bottom:20px}.quick .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:15px}.action{background:linear-gradient(135deg,#f7fafc,#edf2f7);border:2px solid #e2e8f0;border-radius:12px;padding:20px;text-align:center;text-decoration:none;color:#2d3748;font-weight:600;transition:0.3s}.action:hover{transform:translateY(-5px);border-color:#667eea}@media print{.back,.actions,.filter,.quick{display:none}}</style>
@endpush
@endsection
