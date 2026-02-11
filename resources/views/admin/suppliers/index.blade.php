@extends('layouts.admin')
@section('title','Suppliers')
@section('content')
<div class="container">
<div id="alertContainer"></div>
<div class="header">
<h2>Suppliers</h2>
<a href="{{route('admin.suppliers.create')}}" class="btn-primary">+ Add Supplier</a>
</div>
<div class="stats">
<div class="stat"><div class="icon">👥</div><div><h3>{{$suppliers->count()}}</h3><p>Total</p></div></div>
<div class="stat"><div class="icon">✅</div><div><h3>{{$suppliers->where('status','active')->count()}}</h3><p>Active</p></div></div>
<div class="stat"><div class="icon">❌</div><div><h3>{{$suppliers->where('status','inactive')->count()}}</h3><p>Inactive</p></div></div>
</div>
<div class="filters">
<input type="text" id="search" placeholder="Search...">
<select id="statusFilter"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
</div>
<div class="table-wrap">
<table class="data-table">
<thead><tr><th><input type="checkbox" id="selectAll"></th><th>Name</th><th>Contact</th><th>Address</th><th>Tax</th><th>Balance</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
@forelse($suppliers as $s)
<tr data-status="{{$s->status}}">
<td><input type="checkbox" class="chk" value="{{$s->id}}"></td>
<td><strong>{{$s->name}}</strong><br><small>{{$s->supplier_code}}</small></td>
<td>{{$s->phone}}<br><small>{{$s->email}}</small></td>
<td>{{$s->city}}, {{$s->state}}</td>
<td>{{$s->gstin}}</td>
<td><span class="bal {{$s->current_balance>=0?'due':'adv'}}">{{$s->formatted_current_balance}}</span></td>
<td><span class="badge status-{{$s->status}}">{{ucfirst($s->status)}}</span></td>
<td class="actions">
<a href="{{route('admin.suppliers.ledger',$s->id)}}" class="btn-icon" title="Ledger">📊</a>
<a href="{{route('admin.suppliers.edit',$s->id)}}" class="btn-icon" title="Edit">✏️</a>
<button class="btn-icon btn-del" data-id="{{$s->id}}" data-name="{{$s->name}}" title="Delete">🗑️</button>
</td>
</tr>
@empty
<tr><td colspan="8" class="empty">No suppliers found</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
<div class="modal" id="deleteModal">
<div class="modal-overlay" onclick="closeModal()"></div>
<div class="modal-box">
<h3>Delete Supplier</h3>
<p id="modalText">Are you sure?</p>
<form id="deleteForm" method="POST">@csrf @method('DELETE')
<div class="modal-actions">
<button type="button" onclick="closeModal()" class="btn-cancel">Cancel</button>
<button type="submit" class="btn-confirm">Delete</button>
</div>
</form>
</div>
</div>
@push('styles')
<style>.container{padding-bottom:40px}.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}.btn-primary{padding:12px 24px;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border-radius:10px;text-decoration:none}.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:30px}.stat{background:white;padding:25px;border-radius:12px;display:flex;gap:20px;box-shadow:0 4px 20px rgba(0,0,0,0.05)}.icon{font-size:42px}.filters{background:white;padding:20px;border-radius:12px;margin-bottom:20px;display:flex;gap:15px}input[type="text"],select{padding:12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px}.table-wrap{background:white;border-radius:12px;overflow:hidden}.data-table{width:100%;border-collapse:collapse}.data-table thead{background:linear-gradient(135deg,#667eea,#764ba2);color:white}.data-table th{padding:16px;text-align:left}.data-table td{padding:16px;border-bottom:1px solid #e2e8f0}.actions{display:flex;gap:8px}.btn-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;text-decoration:none;font-size:16px}.btn-del{background:#fed7d7;color:#9b2c2c}.badge{padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}.status-active{background:#c6f6d5;color:#22543d}.status-inactive{background:#fed7d7;color:#9b2c2c}.bal{font-weight:700}.due{color:#c05621}.adv{color:#22543d}.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:2000;align-items:center;justify-content:center}.modal-overlay{position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5)}.modal-box{position:relative;background:white;border-radius:20px;padding:35px;max-width:450px;width:90%}.modal-actions{display:flex;justify-content:flex-end;gap:12px;margin-top:20px}.btn-cancel{padding:12px 24px;background:#e2e8f0;border:none;border-radius:10px;cursor:pointer}.btn-confirm{padding:12px 24px;background:#f56565;color:white;border:none;border-radius:10px;cursor:pointer}</style>
@endpush
@push('scripts')
<script>
document.querySelectorAll('.btn-del').forEach(b=>b.addEventListener('click',function(){openModal(this.dataset.id,this.dataset.name)}));function openModal(id,name){document.getElementById('modalText').textContent=`Delete ${name}?`;document.getElementById('deleteForm').action=`/admin/suppliers/${id}`;document.getElementById('deleteModal').style.display='flex'}function closeModal(){document.getElementById('deleteModal').style.display='none'}document.getElementById('search').addEventListener('input',e=>{const v=e.target.value.toLowerCase();document.querySelectorAll('tbody tr').forEach(r=>r.style.display=r.textContent.toLowerCase().includes(v)?'':'none')});document.getElementById('statusFilter').addEventListener('change',e=>{const s=e.target.value;document.querySelectorAll('tbody tr').forEach(r=>r.style.display=!s||r.dataset.status===s?'':'none')});@if(session('success'))alert('{{session('success')}}');@endif
</script>
@endpush
@endsection
