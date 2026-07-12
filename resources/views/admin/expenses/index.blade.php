@extends('layouts.admin')

@section('title', 'Expenses Management')
@section('header-title', 'Expenses')

@section('content')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

<style>
    .db-expenses {
        font-family: 'Inter', sans-serif;
        background: #f7f8fc;
        min-height: 100vh;
        padding: 4px 12px 40px;
        color: #111827;
        font-size: 13px;
    }
    
    .db-top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .db-greeting {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        letter-spacing: -.2px;
        margin: 0;
    }
    .db-greeting span { color: #2563eb; }
    
    .db-date-line {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 2px;
        font-weight: 500;
    }

    .top-actions {
        display: flex;
        gap: 8px;
    }

    .btn-primary-action {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: opacity 0.15s;
    }
    .btn-primary-action:hover { opacity: 0.9; }

    .btn-pdf {
        background: #fff;
        color: #374151;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.15s;
    }
    .btn-pdf:hover { border-color: #9ca3af; }

    /* Stats Grid */
    .db-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }
    
    .db-mc {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px 14px 10px;
        position: relative;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    
    .db-mc::before {
        content: '';
        position: absolute;
        left: 0; top: 10px; bottom: 10px;
        width: 3px;
        border-radius: 0 2px 2px 0;
    }
    
    .mc-red::before    { background: #dc2626; }
    .mc-blue::before   { background: #2563eb; }
    .mc-teal::before   { background: #0d9488; }
    
    .db-mc-icon {
        width: 30px; height: 30px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        margin-bottom: 8px;
    }
    
    .mc-red .db-mc-icon    { background: #fef2f2; color: #dc2626; }
    .mc-blue .db-mc-icon   { background: #eff6ff; color: #2563eb; }
    .mc-teal .db-mc-icon   { background: #f0fdfa; color: #0d9488; }
    
    .db-mc-label {
        font-size: 9.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #9ca3af;
        margin-bottom: 3px;
    }
    
    .db-mc-value {
        font-size: 20px;
        font-weight: 800;
        color: #111827;
        letter-spacing: -.4px;
        line-height: 1;
    }
    
    .db-mc-sub {
        font-size: 10px;
        color: #9ca3af;
        margin-top: 4px;
        font-weight: 500;
    }

    /* Panel Card layout */
    .db-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        margin-bottom: 20px;
    }
    
    .db-ph {
        padding: 12px 14px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fafafa;
        flex-wrap: wrap;
        gap: 12px;
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        width: 100%;
        align-items: center;
    }

    .search-wrapper {
        position: relative;
        flex: 1;
        min-width: 200px;
    }
    
    .search-input {
        width: 100%;
        padding: 6px 10px 6px 28px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 12px;
        outline: none;
        font-family: inherit;
        background: #fff;
        color: #111827;
    }
    .search-input:focus { border-color: #2563eb; }

    .select-filter {
        padding: 6px 10px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 12px;
        background: #fff;
        outline: none;
        font-family: inherit;
        color: #374151;
        cursor: pointer;
    }
    .select-filter:focus { border-color: #2563eb; }

    .date-filter-label {
        font-size: 11px;
        font-weight: 700;
        color: #4b5563;
        margin-left: 4px;
    }

    .date-filter-input {
        padding: 5px 8px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 12px;
        outline: none;
        font-family: inherit;
        color: #374151;
    }

    .btn-filter-submit {
        background: #f3f4f6;
        color: #374151;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }
    .btn-filter-submit:hover { background: #e5e7eb; }

    /* Table styles matching dashboard exactly */
    .db-tbl {
        width: 100%;
        border-collapse: collapse;
    }
    .db-tbl th {
        padding: 9px 14px;
        font-size: 9.5px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #9ca3af;
        font-weight: 700;
        text-align: left;
        background: #fafafa;
        border-bottom: 1px solid #f3f4f6;
    }
    .db-tbl td {
        padding: 10px 14px;
        font-size: 12px;
        color: #374151;
        border-bottom: 1px solid #f9fafb;
    }
    .db-tbl tbody tr {
        transition: background .15s;
    }
    .db-tbl tbody tr:hover td {
        background: #f0f9ff;
    }

    .badge-method {
        font-size: 9px;
        font-weight: 700;
        padding: 2.5px 7px;
        border-radius: 100px;
        text-transform: uppercase;
    }
    .badge-cash { background: #fefce8; color: #ca8a04; }
    .badge-bank { background: #eff6ff; color: #2563eb; }
    .badge-upi  { background: #f0fdfa; color: #0d9488; }

    .badge-category {
        font-size: 10px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 4px;
        background: #f3f4f6;
        color: #4b5563;
        white-space: nowrap;
    }

    .action-link {
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 6px;
        margin-right: 4px;
        display: inline-block;
    }
    .action-edit {
        color: #2563eb;
        background: #eff6ff;
    }
    .action-delete {
        color: #dc2626;
        background: #fef2f2;
        border: none;
        cursor: pointer;
    }

    /* Alert Message */
    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 14px;
    }

    @media (max-width: 768px) {
        .db-expenses {
            padding: 8px 6px 30px;
        }
        .db-top-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .top-actions {
            width: 100%;
        }
        .top-actions a, .top-actions button {
            flex: 1;
            justify-content: center;
        }
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        .search-wrapper, .select-filter, .date-filter-input, .btn-filter-submit {
            width: 100%;
        }
        /* Hide reference and description on mobile */
        .db-tbl th:nth-child(4), .db-tbl td:nth-child(4),
        .db-tbl th:nth-child(5), .db-tbl td:nth-child(5) {
            display: none;
        }
    }
</style>

<div class="db-expenses">
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Top Bar -->
    <div class="db-top-bar">
        <div>
            <h2 class="db-greeting">Expense <span>Management</span></h2>
            <div class="db-date-line">Track office rent, transport, salaries, and other business expenses</div>
        </div>
        <div class="top-actions">
            <a href="{{ route('admin.expenses.pdf', request()->query()) }}" class="btn-pdf">
                <span>📄</span> PDF Statement
            </a>
            <a href="{{ route('admin.expenses.create') }}" class="btn-primary-action">
                <span>+</span> Add Expense
            </a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="db-metrics">
        <div class="db-mc mc-red">
            <div class="db-mc-icon">💸</div>
            <div class="db-mc-label">Total Outflow</div>
            <div class="db-mc-value">₹ {{ number_format($totalExpenses, 2) }}</div>
            <div class="db-mc-sub">Cumulative business expense</div>
        </div>
        <div class="db-mc mc-blue">
            <div class="db-mc-icon">📊</div>
            <div class="db-mc-label">Total Transactions</div>
            <div class="db-mc-value">{{ $expenseCount }}</div>
            <div class="db-mc-sub">Number of voucher entries</div>
        </div>
        <div class="db-mc mc-teal">
            <div class="db-mc-icon">📈</div>
            <div class="db-mc-label">Average Expense</div>
            <div class="db-mc-value">₹ {{ number_format($avgExpense, 2) }}</div>
            <div class="db-mc-sub">Average value per voucher entry</div>
        </div>
    </div>

    <!-- Main Table Panel -->
    <div class="db-panel">
        <div class="db-ph">
            <form method="GET" action="{{ route('admin.expenses.index') }}" class="filter-form">
                <div class="search-wrapper">
                    <span style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 11px; color: #9ca3af;">🔍</span>
                    <input type="text" name="search" class="search-input" value="{{ $search }}" placeholder="Search description, reference...">
                </div>
                
                <select name="category" class="select-filter">
                    <option value="all" {{ $category === 'all' ? 'selected' : '' }}>All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>

                <select name="payment_method" class="select-filter">
                    <option value="all" {{ $paymentMethod === 'all' ? 'selected' : '' }}>All Payment Methods</option>
                    <option value="Cash" {{ $paymentMethod === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Bank" {{ $paymentMethod === 'Bank' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="UPI" {{ $paymentMethod === 'UPI' ? 'selected' : '' }}>UPI / Digital</option>
                </select>

                <span class="date-filter-label">From:</span>
                <input type="date" name="from_date" class="date-filter-input" value="{{ $fromDate }}">

                <span class="date-filter-label">To:</span>
                <input type="date" name="to_date" class="date-filter-input" value="{{ $toDate }}">

                <button type="submit" class="btn-filter-submit">Filter</button>
                <a href="{{ route('admin.expenses.index') }}" style="font-size: 11px; text-decoration: none; color: #6b7280; font-weight: 600; padding: 6px 8px;">Clear</a>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table class="db-tbl">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 110px;">Date</th>
                        <th>Category</th>
                        <th>Reference No.</th>
                        <th>Description / Notes</th>
                        <th style="text-align: center; width: 130px;">Method</th>
                        <th style="text-align: right; width: 140px;">Amount</th>
                        <th style="text-align: center; width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $i => $expense)
                    <tr>
                        <td style="color: #9ca3af; font-weight: 500;">{{ $i + 1 }}</td>
                        <td style="font-weight: 600; color: #4b5563;">{{ $expense->expense_date ? $expense->expense_date->format('d M Y') : 'N/A' }}</td>
                        <td>
                            <span class="badge-category">{{ $expense->category_name }}</span>
                        </td>
                        <td style="font-family: monospace; font-size: 11px; color: #6b7280;">{{ $expense->reference_no ?? '—' }}</td>
                        <td style="color: #64748b; font-size: 11.5px; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $expense->description }}">{{ $expense->description ?? '—' }}</td>
                        <td style="text-align: center;">
                            <span class="badge-method badge-{{ strtolower($expense->payment_method) }}">
                                {{ $expense->payment_method === 'Bank' ? 'Bank Transfer' : $expense->payment_method }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 800; color: #dc2626; font-size: 13px;">₹ {{ number_format($expense->amount, 2) }}</td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="action-link action-edit">EDIT</a>
                            <form method="POST" action="{{ route('admin.expenses.destroy', $expense->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this expense record?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-link action-delete">DELETE</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding: 40px 14px; text-align: center; color: #9ca3af;">
                            <div style="font-size: 28px; margin-bottom: 8px;">💰</div>
                            <div style="font-weight: 700; color: #111827; font-size: 13px;">No Expenses Found</div>
                            <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">No expense records match the specified filters.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
