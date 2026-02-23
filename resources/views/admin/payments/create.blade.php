@extends('layouts.admin')

@section('title', 'Add Payment')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Add Payment</h5>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.payments.store') }}">
            @csrf

            <div class="form-group">
                <label>Select Invoice</label>
                <select name="sales_invoice_id" class="form-control" required>
                    <option value="">-- Select Invoice --</option>
                    @foreach($invoices as $invoice)
                        <option value="{{ $invoice->_id }}">
                            {{ $invoice->invoice_number }} |
                            {{ $invoice->customer->name }} |
                            Balance: ₹{{ number_format($invoice->balance_amount, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Amount</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Payment Method</label>
                <select name="payment_method" class="form-control" required>
                    <option value="cash">Cash</option>
                    <option value="upi">UPI</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="card">Card</option>
                </select>
            </div>

            <div class="form-group">
                <label>Payment Date</label>
                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label>Reference No</label>
                <input type="text" name="reference_no" class="form-control">
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Save Payment</button>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
