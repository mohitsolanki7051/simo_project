@extends('layouts.admin')

@section('title', 'Customer Ledger')
@section('header-title', 'Customer Ledger')

@section('content')
<div class="ledger-container">

    <!-- 🔝 Customer Header -->
    <div class="ledger-header">
        <div class="customer-info">
            <h2>{{ $customer->name }}</h2>
            <p>📱 {{ $customer->phone }}</p>
        </div>

        <div class="balance-box">
            <span>Current Balance</span>
            @php
                $lastBalance = count($ledger) ? $ledger->last()['balance'] : 0;
            @endphp
            <h2 class="{{ $lastBalance > 0 ? 'due' : 'advance' }}">
                ₹{{ number_format($lastBalance,2) }}
            </h2>
        </div>
    </div>

    <!-- 📊 Summary Cards -->
    @php
        $totalDebit = $ledger->sum('debit');
        $totalCredit = $ledger->sum('credit');
    @endphp

    <div class="summary-cards">
        <div class="card sale">
            <h4>Total Sale</h4>
            <h2>₹{{ number_format($totalDebit,2) }}</h2>
        </div>

        <div class="card payment">
            <h4>Total Payment</h4>
            <h2>₹{{ number_format($totalCredit,2) }}</h2>
        </div>

        <div class="card balance">
            <h4>Balance</h4>
            <h2>₹{{ number_format($totalDebit - $totalCredit,2) }}</h2>
        </div>
    </div>

    <!-- 📅 Filter -->
    <div class="ledger-filters">
        <input type="date" id="fromDate">
        <input type="date" id="toDate">
        <input type="text" id="searchInvoice" placeholder="Search Invoice">
        <button onclick="filterLedger()">Filter</button>
        <button onclick="window.print()">🖨️ Print</button>
    </div>

    <!-- 📒 Ledger Table -->
    <div class="table-wrapper">
        <table class="ledger-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Reference</th>
                    <th class="debit">Debit</th>
                    <th class="credit">Credit</th>
                    <th>Balance</th>
                </tr>
            </thead>

            <tbody id="ledgerBody">
                @foreach($ledger as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>

                    <!-- TYPE BADGE -->
                    <td>
                        @if($row['type'] == 'Sale')
                            <span class="badge sale">Sale</span>
                        @elseif($row['type'] == 'Payment')
                            <span class="badge payment">Payment</span>
                        @elseif($row['type'] == 'Advance')
                            <span class="badge advance">Advance</span>
                        @elseif($row['type'] == 'Discount')
                            <span class="badge discount">Discount</span>
                        @else
                            <span class="badge charge">{{ $row['type'] }}</span>
                        @endif
                    </td>

                    <!-- REFERENCE -->
                    <td>
                        @if($row['type'] == 'Sale')
                            <a href="{{ route('admin.sales.show', $row['invoice_id'] ?? '') }}" target="_blank" class="invoice-link">
                                {{ $row['ref'] }}
                            </a>
                        @else
                            {{ $row['ref'] }}
                        @endif
                    </td>

                    <td class="debit">₹{{ number_format($row['debit'],2) }}</td>
                    <td class="credit">₹{{ number_format($row['credit'],2) }}</td>
                    <td class="balance">₹{{ number_format($row['balance'],2) }}</td>
                </tr>
                @endforeach
            </tbody>

            <!-- FOOTER TOTAL -->
            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th class="debit">₹{{ number_format($totalDebit,2) }}</th>
                    <th class="credit">₹{{ number_format($totalCredit,2) }}</th>
                    <th>₹{{ number_format($totalDebit - $totalCredit,2) }}</th>
                </tr>
            </tfoot>

        </table>
    </div>

</div>

<style>

.ledger-header{
    display:flex;justify-content:space-between;align-items:center;
    background:#fff;padding:15px;border-radius:8px;margin-bottom:15px;
    border:1px solid #eee;
}

.balance-box h2{margin:0;font-size:22px}
.balance-box .due{color:#dc2626}
.balance-box .advance{color:#16a34a}

.summary-cards{
    display:flex;gap:15px;margin-bottom:15px;
}
.card{
    flex:1;padding:15px;border-radius:8px;color:#fff;
}
.card.sale{background:#3b82f6}
.card.payment{background:#16a34a}
.card.balance{background:#f97316}

.ledger-filters{
    display:flex;gap:10px;margin-bottom:10px;
}
.ledger-filters input{
    padding:6px;border:1px solid #ccc;border-radius:4px;
}
.ledger-filters button{
    background:#fa8427;color:#fff;border:none;padding:6px 12px;border-radius:4px;
    cursor:pointer;
}

.ledger-table{
    width:100%;border-collapse:collapse;background:#fff;
}
.ledger-table th, .ledger-table td{
    padding:10px;border-bottom:1px solid #eee;
}
.ledger-table th{background:#f8fafc;text-align:left}

.debit{color:#dc2626}
.credit{color:#16a34a}
.balance{font-weight:bold}

.badge{
    padding:3px 8px;border-radius:10px;font-size:11px;color:#fff;
}

.sale{background:#3b82f6}
.payment{background:#16a34a}
.advance{background:#9333ea}
.discount{background:#f59e0b}
.charge{background:#ef4444}

.invoice-link{
    color:#2563eb;
    text-decoration:none;
    font-weight:600;
}
.invoice-link:hover{
    text-decoration:underline;
}

/* PRINT STYLE */
@media print{
    .ledger-filters, .summary-cards{
        display:none;
    }
    body{
        background:white;
    }
}

</style>

<script>
function filterLedger(){
    let search = document.getElementById('searchInvoice').value.toLowerCase();
    let rows = document.querySelectorAll("#ledgerBody tr");

    rows.forEach(row=>{
        let ref = row.children[2].innerText.toLowerCase();
        let show = ref.includes(search);
        row.style.display = show ? "" : "none";
    });
}
</script>

@endsection
