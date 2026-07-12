@extends('layouts.admin')

@section('title', 'Add Expense')
@section('header-title', 'Add New Expense')

@section('content')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

<style>
    .db-form-container {
        font-family: 'Inter', sans-serif;
        background: #f7f8fc;
        min-height: 100vh;
        padding: 4px 12px 40px;
        color: #111827;
    }

    .db-top-bar {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .db-greeting {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }
    
    .btn-action-back {
        text-decoration: none;
        color: #2563eb;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .form-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        max-width: 600px;
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        font-family: inherit;
        color: #111827;
        background: #fff;
    }
    .form-control:focus { border-color: #2563eb; }

    .btn-submit {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
        transition: opacity 0.15s;
    }
    .btn-submit:hover { opacity: 0.9; }

    .error-text {
        font-size: 11px;
        color: #dc2626;
        margin-top: 4px;
        font-weight: 600;
    }
</style>

<div class="db-form-container">
    <div class="db-top-bar">
        <a href="{{ route('admin.expenses.index') }}" class="btn-action-back">← Back to Expenses</a>
        <h2 class="db-greeting" style="margin-top: 4px;">Add New Expense</h2>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ route('admin.expenses.store') }}">
            @csrf

            <!-- Date -->
            <div class="form-group">
                <label class="form-label">Expense Date</label>
                <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                @error('expense_date')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <!-- Category -->
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category_name" class="form-control" required>
                    <option value="" disabled selected>Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category_name') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category_name')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <!-- Amount -->
            <div class="form-group">
                <label class="form-label">Amount (₹)</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="e.g. 15000" value="{{ old('amount') }}" required>
                @error('amount')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <!-- Payment Method -->
            <div class="form-group">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-control" required>
                    <option value="Cash" {{ old('payment_method', 'Cash') === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Bank" {{ old('payment_method') === 'Bank' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="UPI" {{ old('payment_method') === 'UPI' ? 'selected' : '' }}>UPI / Digital</option>
                </select>
                @error('payment_method')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <!-- Reference Number -->
            <div class="form-group">
                <label class="form-label">Reference / Txn No. <span style="font-weight: normal; color: #9ca3af;">(Optional)</span></label>
                <input type="text" name="reference_no" class="form-control" placeholder="e.g. TXN-982451" value="{{ old('reference_no') }}">
                @error('reference_no')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <!-- Notes -->
            <div class="form-group">
                <label class="form-label">Description / Notes <span style="font-weight: normal; color: #9ca3af;">(Optional)</span></label>
                <textarea name="description" class="form-control" rows="3" placeholder="Add descriptions, invoice details, etc...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-submit">Save Expense</button>
        </form>
    </div>
</div>
@endsection
