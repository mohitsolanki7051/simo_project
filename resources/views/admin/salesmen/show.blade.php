@extends('layouts.admin')

@section('title', 'Salesman — ' . $salesman->name)
@section('header-title', 'Salesman Details')

@section('content')
@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box;}
body{font-family:'Inter',-apple-system,sans-serif;font-size:13.5px;color:#1a1a1a;background:#f4f5f7;}

.pg{padding:20px 24px;max-width:1360px;}

/* Topbar */
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;}
.sm-identity{display:flex;align-items:center;gap:12px;}
.sm-avatar{width:42px;height:42px;border-radius:10px;background:#2563eb;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;color:#fff;flex-shrink:0;}
.sm-name{font-size:17px;font-weight:700;line-height:1.2;}
.sm-meta{font-size:12px;color:#6b7280;margin-top:2px;}
.topbar-actions{display:flex;gap:8px;flex-wrap:wrap;}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:7px;font-size:12.5px;font-weight:500;cursor:pointer;border:none;text-decoration:none;transition:background .15s;font-family:inherit;}
.btn-primary{background:#2563eb;color:#fff;}.btn-primary:hover{background:#1d4ed8;color:#fff;}
.btn-gray{background:#fff;color:#374151;border:1px solid #d1d5db;}.btn-gray:hover{background:#f9fafb;}
.btn-ghost{background:transparent;color:#6b7280;border:1px solid #e5e7eb;}.btn-ghost:hover{background:#fff;}
.btn-green{background:#16a34a;color:#fff;}.btn-green:hover{background:#15803d;color:#fff;}
.btn-sm{padding:5px 11px;font-size:12px;}
.btn:disabled{opacity:.5;cursor:not-allowed;pointer-events:none;}

/* Badges */
.status-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;font-size:11.5px;font-weight:500;}
.status-active{background:#dcfce7;color:#15803d;}
.status-inactive{background:#f3f4f6;color:#6b7280;}
.tag{display:inline-flex;padding:2px 8px;border-radius:5px;font-size:11.5px;font-weight:500;}
.tag-blue{background:#eff6ff;color:#2563eb;}
.tag-green{background:#dcfce7;color:#15803d;}
.tag-orange{background:#fff7ed;color:#ea580c;}
.tag-red{background:#fef2f2;color:#dc2626;}
.tag-gray{background:#f3f4f6;color:#6b7280;}

/* Cards */
.card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;}
.card-head{padding:13px 16px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;gap:10px;}
.card-title{font-size:13px;font-weight:600;}
.card-body{padding:16px;}
.card-body-flush{padding:0;}

/* Stats */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;}
.stat-box{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;}
.stat-label{font-size:11.5px;color:#6b7280;margin-bottom:6px;}
.stat-value{font-size:20px;font-weight:700;line-height:1.1;}
.stat-sub{font-size:11px;color:#9ca3af;margin-top:3px;}

/* Layout */
.layout{display:grid;grid-template-columns:300px 1fr;gap:14px;}
.col{display:flex;flex-direction:column;gap:14px;}

/* Info list */
.info-row{display:flex;align-items:flex-start;padding:9px 0;border-bottom:1px solid #f3f4f6;}
.info-row:last-child{border-bottom:none;}
.info-key{width:110px;flex-shrink:0;font-size:12px;color:#6b7280;padding-top:1px;}
.info-val{flex:1;font-size:13px;font-weight:500;}

/* Pending boxes */
.pending-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;}
.pending-box{border:1px solid #e5e7eb;border-radius:10px;padding:16px;background:#fff;}
.pending-box.has-pending{border-color:#fbbf24;background:#fffbeb;}
.pending-box.no-pending{border-color:#86efac;background:#f0fdf4;}
.pb-label{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:8px;}
.pb-amount{font-size:24px;font-weight:700;line-height:1;}
.pb-sub{font-size:11.5px;color:#6b7280;margin-top:5px;line-height:1.6;}
.pb-actions{margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;}

/* Rate rows */
.rate-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f3f4f6;}
.rate-row:last-child{border-bottom:none;}
.rate-name{font-size:13px;font-weight:500;}
.rate-pct{font-size:16px;font-weight:700;color:#2563eb;}

/* Schedule */
.schedule-list{max-height:280px;overflow-y:auto;padding:0 14px;}
.sch-row{display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f3f4f6;font-size:12.5px;}
.sch-row:last-child{border-bottom:none;}
.sch-month{font-weight:500;color:#374151;}
.sch-detail{font-size:11.5px;color:#9ca3af;margin-top:2px;}

/* Filter bar */
.filter-bar{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;margin-bottom:14px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;}
.fg{flex:1;min-width:120px;}
.fl{font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;}
.fs,.fi{width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:7px;font-size:12.5px;font-family:inherit;background:#fff;}
.fs:focus,.fi:focus{outline:none;border-color:#2563eb;}
.fbtn{padding:7px 16px;background:#2563eb;color:#fff;border:none;border-radius:7px;font-size:12.5px;font-weight:500;cursor:pointer;font-family:inherit;}
.fbtn:hover{background:#1d4ed8;}
.fbtn.reset{background:#f3f4f6;color:#374151;}.fbtn.reset:hover{background:#e5e7eb;}

/* Table */
.tbl{width:100%;border-collapse:collapse;}
.tbl thead th{padding:9px 12px;text-align:left;font-size:11.5px;font-weight:600;color:#6b7280;background:#f9fafb;border-bottom:1px solid #e5e7eb;white-space:nowrap;}
.tbl tbody td{padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:12.5px;vertical-align:middle;}
.tbl tbody tr:last-child td{border-bottom:none;}
.tbl tbody tr:hover td{background:#fafafa;}

/* Pagination */
.pgn{padding:10px 14px;background:#f9fafb;border-top:1px solid #e5e7eb;}
.pgn nav{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;}
.pgn nav p{font-size:12px;color:#6b7280;}
.pgn nav .flex{display:flex;gap:3px;}
.pgn nav a,.pgn nav span{display:inline-flex;align-items:center;justify-content:center;padding:4px 9px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;color:#374151;text-decoration:none;min-width:30px;}
.pgn nav a:hover{background:#eff6ff;border-color:#2563eb;color:#2563eb;}
.pgn nav span[aria-current="page"]{background:#2563eb;border-color:#2563eb;color:#fff;}
.pgn nav span.cursor-default{opacity:.4;}

/* Tabs */
.tabs{display:flex;gap:2px;background:#f3f4f6;border-radius:8px;padding:3px;margin-bottom:14px;}
.tab-btn{flex:1;padding:6px 10px;border:none;border-radius:6px;font-size:12.5px;font-weight:500;cursor:pointer;background:transparent;color:#6b7280;font-family:inherit;transition:all .15s;}
.tab-btn.active{background:#fff;color:#111827;box-shadow:0 1px 3px rgba(0,0,0,.08);}

/* Search */
.search-wrap{position:relative;margin-bottom:12px;}
.search-ico{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none;}
.search-inp{width:100%;padding:7px 10px 7px 30px;border:1px solid #d1d5db;border-radius:7px;font-size:12.5px;font-family:inherit;}
.search-inp:focus{outline:none;border-color:#2563eb;}
.search-clr{position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;font-size:14px;line-height:1;display:none;}

/* Empty */
.empty-state{text-align:center;padding:28px 16px;color:#9ca3af;font-size:12.5px;}
.empty-icon{font-size:28px;margin-bottom:6px;}

/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;padding:20px;}
.modal-overlay.open{display:flex;}
.modal-box{background:#fff;border-radius:12px;width:100%;max-width:440px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:mIn .18s ease;}
@keyframes mIn{from{opacity:0;transform:scale(.97) translateY(6px);}to{opacity:1;transform:none;}}
.modal-hdr{padding:16px 18px 13px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;}
.modal-title{font-size:14px;font-weight:700;}
.modal-close{background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;line-height:1;}
.modal-body{padding:16px 18px;}
.modal-ftr{padding:12px 18px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:8px;}

/* Modal form */
.mf-grp{margin-bottom:14px;}
.mf-grp:last-child{margin-bottom:0;}
.mf-label{display:block;font-size:11.5px;font-weight:600;color:#374151;margin-bottom:5px;}
.mf-inp,.mf-sel{width:100%;padding:8px 11px;border:1px solid #d1d5db;border-radius:7px;font-size:13px;font-family:inherit;background:#fff;}
.mf-inp:focus,.mf-sel:focus{outline:none;border-color:#2563eb;}
.mf-inp:disabled{background:#f9fafb;color:#9ca3af;cursor:not-allowed;}
.mf-hint{font-size:11.5px;color:#6b7280;margin-top:4px;}

/* Summary box */
.sum-box{background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:12px 14px;margin-bottom:14px;}
.sum-row{display:flex;justify-content:space-between;padding:3px 0;font-size:12.5px;}
.sum-row:not(:last-child){border-bottom:1px solid #f3f4f6;}
.sum-lbl{color:#6b7280;}
.sum-val{font-weight:600;}
.sum-val.green{color:#15803d;}
.sum-val.orange{color:#ea580c;}
.sum-val.red{color:#dc2626;}

/* Partial toggle */
.partial-toggle{display:flex;align-items:center;gap:8px;padding:10px 12px;background:#fefce8;border:1px solid #fde68a;border-radius:7px;cursor:pointer;margin-bottom:12px;user-select:none;}
.partial-toggle input{width:15px;height:15px;accent-color:#d97706;cursor:pointer;flex-shrink:0;}
.partial-toggle span{font-size:12.5px;font-weight:500;color:#92400e;}
.partial-amt-box{display:none;}
.partial-amt-box.show{display:block;}

/* Paid notice */
.paid-notice{background:#f0fdf4;border:1px solid #86efac;border-radius:7px;padding:10px 13px;display:flex;align-items:center;gap:8px;font-size:12.5px;color:#15803d;font-weight:500;margin-bottom:14px;}

/* Toast */
.toast{position:fixed;bottom:22px;right:22px;color:#fff;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:500;z-index:99999;opacity:0;transform:translateY(10px);transition:all .25s;pointer-events:none;max-width:320px;box-shadow:0 6px 20px rgba(0,0,0,.25);}
.toast.show{opacity:1;transform:none;}

@keyframes spin{to{transform:rotate(360deg);}}
@media(max-width:1000px){.layout{grid-template-columns:1fr;}.stats-grid{grid-template-columns:repeat(2,1fr);}.pending-grid{grid-template-columns:1fr;}}
@media(max-width:580px){.stats-grid{grid-template-columns:1fr;}.pg{padding:14px;}}
</style>
@endpush

@php
    $paidFixedJson    = json_encode($paidFixedMonths);
    $scheduleJson     = json_encode(array_values($fixedSalarySchedule));
    $fixedSalary      = (float)($salesman->fixed_salary ?? 0);
    $periodMonths     = match($salesman->fixed_salary_period ?? 'monthly') {
        'quarterly'   => 3,
        'half_yearly' => 6,
        'yearly'      => 12,
        default       => 1,
    };
    $duePerPeriod     = $fixedSalary * $periodMonths;
    $currYear         = (int)date('Y');
    $currMonth        = (int)date('n');
    $filterYear       = (int)($filters['year'] ?? $currYear);
    $filterMonth      = ($filters['month'] !== '') ? (int)$filters['month'] : 0;
    $fixedPeriodLabel = match($salesman->fixed_salary_period ?? 'monthly') {
        'quarterly'   => 'Quarterly',
        'half_yearly' => 'Half-Yearly',
        'yearly'      => 'Yearly',
        default       => 'Monthly',
    };
    $commPeriodLabel = match($salesman->commission_period ?? 'monthly') {
        'quarterly'   => 'Quarterly',
        'half_yearly' => 'Half-Yearly',
        'yearly'      => 'Yearly',
        default       => 'Monthly',
    };
@endphp

<div class="pg">

{{-- TOP BAR --}}
<div class="topbar">
    <div class="sm-identity">
        <div class="sm-avatar">{{ strtoupper(substr($salesman->name,0,2)) }}</div>
        <div>
            <div class="sm-name">{{ $salesman->name }}</div>
            <div class="sm-meta">
                <span class="status-badge status-{{ $salesman->status }}">{{ ucfirst($salesman->status) }}</span>
                &nbsp;·&nbsp;{{ $salesman->salary_type_text }}
                &nbsp;·&nbsp;Joined {{ $salesman->formatted_joining_date }}
            </div>
        </div>
    </div>
    <div class="topbar-actions">
        <a href="{{ route('admin.salesmen.download-report',$salesman->id) }}?{{ http_build_query(request()->query()) }}" class="btn btn-gray" target="_blank">↓ PDF</a>
        <a href="{{ route('admin.salesmen.edit',$salesman->id) }}" class="btn btn-primary">✏ Edit</a>
        <a href="{{ route('admin.salesmen.index') }}" class="btn btn-ghost">← Back</a>
    </div>
</div>

{{-- STATS --}}
<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-label">Sales</div>
        <div class="stat-value">₹{{ number_format($totalSales,2) }}</div>
        <div class="stat-sub">{{ $totalInvoicesCount }} invoices</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Commission</div>
        <div class="stat-value" style="color:#15803d">₹{{ number_format($commissionEarned,2) }}</div>
        <div class="stat-sub">Earned this period</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Assigned Parties</div>
        <div class="stat-value">{{ $allPartiesCount }}</div>
        <div class="stat-sub">{{ $partyCounts['customers'] }}C · {{ $partyCounts['dealers'] }}D · {{ $partyCounts['distributors'] }}Di</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Total Paid (All Time)</div>
        <div class="stat-value">₹{{ number_format($totalCommissionPaid + $totalFixedPaid,2) }}</div>
        <div class="stat-sub">Comm: ₹{{ number_format($totalCommissionPaid,2) }} · Fixed: ₹{{ number_format($totalFixedPaid,2) }}</div>
    </div>
</div>

{{-- PENDING BOXES --}}
<div class="pending-grid">

    @if($salesman->commission_enabled && $salesman->salary_type !== 'fixed')
    <div class="pending-box {{ $pendingCommission > 0 ? 'has-pending' : 'no-pending' }}">
        <div class="pb-label">Commission Pending</div>
        <div class="pb-amount" style="color:{{ $pendingCommission > 0 ? '#b45309' : '#15803d' }}">
            ₹{{ number_format($pendingCommission,2) }}
        </div>
        <div class="pb-sub">
            Earned (all time): ₹{{ number_format($totalEarnedCommission,2) }}<br>
            Paid (all time): ₹{{ number_format($totalCommissionPaid,2) }}<br>
            Period: <strong>{{ $commPeriodLabel }}</strong>
        </div>
        @if($pendingCommission > 0)
        <div class="pb-actions">
            <button class="btn btn-green btn-sm" onclick="openCommModal(false)">Pay ₹{{ number_format($pendingCommission,2) }}</button>
            <button class="btn btn-gray btn-sm" onclick="openCommModal(true)">Partial Pay</button>
        </div>
        @else
        <div style="margin-top:10px;font-size:12px;color:#15803d;font-weight:500">✓ All commission paid</div>
        @endif
    </div>
    @endif

    @if($salesman->salary_type !== 'commission' && $salesman->fixed_salary > 0)
    <div class="pending-box {{ $pendingFixedSalary > 0 ? 'has-pending' : 'no-pending' }}">
        <div class="pb-label">Fixed Salary Pending</div>
        <div class="pb-amount" style="color:{{ $pendingFixedSalary > 0 ? '#b45309' : '#15803d' }}">
            ₹{{ number_format($pendingFixedSalary,2) }}
        </div>
        <div class="pb-sub">
            ₹{{ number_format($fixedSalary,2) }}/month × {{ $periodMonths }} = ₹{{ number_format($duePerPeriod,2) }} per {{ strtolower($fixedPeriodLabel) }}<br>
            Total paid: ₹{{ number_format($totalFixedPaid,2) }}
        </div>
        @if($pendingFixedSalary > 0)
        <div class="pb-actions">
            <button class="btn btn-green btn-sm" onclick="openFixedModal(false)">Pay Salary</button>
            <button class="btn btn-gray btn-sm" onclick="openFixedModal(true)">Partial Pay</button>
        </div>
        @else
        <div style="margin-top:10px;font-size:12px;color:#15803d;font-weight:500">✓ All salary paid</div>
        @endif
    </div>
    @endif

</div>

{{-- MAIN LAYOUT --}}
<div class="layout">
<div class="col">

    {{-- Personal Info --}}
    <div class="card">
        <div class="card-head"><span class="card-title">Personal Info</span></div>
        <div class="card-body" style="padding-top:6px;padding-bottom:6px;">
            <div class="info-row"><span class="info-key">Name</span><span class="info-val">{{ $salesman->name }}</span></div>
            <div class="info-row"><span class="info-key">Phone</span><span class="info-val">{{ $salesman->phone }}</span></div>
            <div class="info-row"><span class="info-key">Email</span><span class="info-val" style="{{ $salesman->email?'':'color:#9ca3af' }}">{{ $salesman->email ?? '—' }}</span></div>
            <div class="info-row"><span class="info-key">Joined</span><span class="info-val">{{ $salesman->formatted_joining_date }}</span></div>
            <div class="info-row"><span class="info-key">Status</span><span class="info-val"><span class="status-badge status-{{ $salesman->status }}">{{ ucfirst($salesman->status) }}</span></span></div>
            <div class="info-row"><span class="info-key">Salary Type</span><span class="info-val">{{ $salesman->salary_type_text }}</span></div>
        </div>
    </div>

    {{-- Salary Config --}}
    <div class="card">
        <div class="card-head"><span class="card-title">Salary Configuration</span></div>
        <div class="card-body" style="padding-top:6px;padding-bottom:6px;">
            @if($salesman->salary_type !== 'commission')
            <div class="info-row"><span class="info-key">Fixed/Month</span><span class="info-val">₹{{ number_format($salesman->fixed_salary,2) }}</span></div>
            <div class="info-row"><span class="info-key">Due/Period</span><span class="info-val" style="color:#ea580c;font-weight:700">₹{{ number_format($duePerPeriod,2) }}</span></div>
            <div class="info-row"><span class="info-key">Pay Period</span><span class="info-val"><span class="tag tag-blue">{{ $fixedPeriodLabel }}</span></span></div>
            @endif
            @if($salesman->salary_type !== 'fixed')
            <div class="info-row">
                <span class="info-key">Commission</span>
                <span class="info-val">
                    @if($salesman->commission_enabled)
                        <span class="tag tag-green">Enabled</span>
                    @else
                        <span class="tag tag-gray">Disabled</span>
                    @endif
                </span>
            </div>
            @if($salesman->commission_enabled)
            <div class="info-row"><span class="info-key">Comm. Period</span><span class="info-val"><span class="tag tag-blue">{{ $commPeriodLabel }}</span></span></div>
            @endif
            @endif
        </div>
    </div>

    {{-- Commission Rates --}}
    @if($salesman->commission_enabled && $salesman->salary_type !== 'fixed')
    <div class="card">
        <div class="card-head"><span class="card-title">Commission Rates</span></div>
        <div class="card-body" style="padding-top:4px;padding-bottom:4px;">
            @foreach([
                ['label'=>'Customer',    'pct'=>$salesman->customer_commission_percent],
                ['label'=>'Dealer',      'pct'=>$salesman->dealer_commission_percent],
                ['label'=>'Distributor', 'pct'=>$salesman->distributor_commission_percent],
            ] as $r)
            <div class="rate-row">
                <span class="rate-name">{{ $r['label'] }}</span>
                <span class="rate-pct">{{ $r['pct'] ?? 0 }}%</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Fixed Salary Schedule --}}
    @if($salesman->salary_type !== 'commission' && count($fixedSalarySchedule))
    <div class="card">
        <div class="card-head">
            <span class="card-title">Salary Schedule</span>
            @if($pendingFixedSalary > 0)
                <span class="tag tag-orange">₹{{ number_format($pendingFixedSalary,2) }} pending</span>
            @else
                <span class="tag tag-green">All paid</span>
            @endif
        </div>
        <div class="card-body card-body-flush">
            <div class="schedule-list">
                @foreach(array_reverse($fixedSalarySchedule) as $key => $entry)
                <div class="sch-row">
                    <div>
                        <div class="sch-month">{{ $entry['label'] }}</div>
                        <div class="sch-detail">
                            Due: ₹{{ number_format($entry['due'],2) }}
                            @if($entry['paid'] > 0) · Paid: ₹{{ number_format($entry['paid'],2) }} @endif
                        </div>
                    </div>
                    <div>
                        @if($entry['is_paid'])
                            <span class="tag tag-green">✓ Paid</span>
                        @elseif($entry['paid'] > 0)
                            <span class="tag tag-orange">₹{{ number_format($entry['remaining'],2) }} left</span>
                        @else
                            <span class="tag tag-red">₹{{ number_format($entry['remaining'],2) }} due</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($salesman->notes)
    <div class="card">
        <div class="card-head"><span class="card-title">Notes</span></div>
        <div class="card-body" style="color:#6b7280;line-height:1.65;font-size:12.5px;">{{ $salesman->notes }}</div>
    </div>
    @endif

</div>
<div class="col">

    {{-- Filter Bar --}}
    <div class="filter-bar">
        <div class="fg"><div class="fl">Month</div><select class="fs" id="f-month"></select></div>
        <div class="fg"><div class="fl">Year</div>
            <select class="fs" id="f-year" onchange="rebuildMonths()">
                @foreach(range(date('Y'), date('Y')-5) as $y)
                <option value="{{ $y }}" {{ $filterYear===$y?'selected':'' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="fg"><div class="fl">Party Type</div>
            <select class="fs" id="f-type">
                <option value="">All Types</option>
                <option value="customer"    {{ ($filters['party_type']??'')==='customer'?'selected':'' }}>Customer</option>
                <option value="dealer"      {{ ($filters['party_type']??'')==='dealer'?'selected':'' }}>Dealer</option>
                <option value="distributor" {{ ($filters['party_type']??'')==='distributor'?'selected':'' }}>Distributor</option>
            </select>
        </div>
        <div class="fg"><div class="fl">From</div><input type="date" class="fi" id="f-start" value="{{ $filters['start_date']??'' }}"></div>
        <div class="fg"><div class="fl">To</div><input type="date" class="fi" id="f-end" value="{{ $filters['end_date']??'' }}"></div>
        <div style="display:flex;gap:6px;">
            <button class="fbtn" onclick="applyFilters()">Apply</button>
            <button class="fbtn reset" onclick="resetFilters()">Reset</button>
        </div>
    </div>

    {{-- Sales Breakdown --}}
    <div class="card">
        <div class="card-head"><span class="card-title">Sales Breakdown</span></div>
        <div class="card-body card-body-flush">
            <div style="overflow-x:auto;">
                <table class="tbl">
                    <thead><tr><th>Party Type</th><th>Invoices</th><th>Total Sales</th><th>Commission</th></tr></thead>
                    <tbody>
                        @foreach([['key'=>'customer','label'=>'Customer'],['key'=>'dealer','label'=>'Dealer'],['key'=>'distributor','label'=>'Distributor']] as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td>{{ $salesBreakdown[$row['key']]['count']??0 }}</td>
                            <td>₹{{ number_format($salesBreakdown[$row['key']]['total']??0,2) }}</td>
                            <td style="color:#15803d;font-weight:600">₹{{ number_format($salesBreakdown[$row['key']]['commission']??0,2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Invoices --}}
    <div class="card">
        <div class="card-head">
            <span class="card-title">Invoices</span>
            <span class="tag tag-gray">{{ $totalInvoicesCount }} total</span>
        </div>
        <div class="card-body card-body-flush">
            @if($allInvoices->count())
            <div style="overflow-x:auto;">
                <table class="tbl">
                    <thead><tr><th>Invoice #</th><th>Party</th><th>Type</th><th>Date</th><th>Amount</th></tr></thead>
                    <tbody>
                        @foreach($allInvoices as $inv)
                        <tr>
                            <td><a href="{{ route('admin.sales.show',$inv->id) }}" style="color:#2563eb;text-decoration:none;font-weight:500">{{ $inv->invoice_number }}</a></td>
                            <td>{{ $inv->party->name??'N/A' }}</td>
                            <td><span class="tag tag-gray">{{ ucfirst($inv->party->party_type??'—') }}</span></td>
                            <td style="color:#6b7280;white-space:nowrap">{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') }}</td>
                            <td style="font-weight:600;color:#15803d;white-space:nowrap">₹{{ number_format($inv->grand_total,2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($allInvoices->hasPages())
            <div class="pgn">{{ $allInvoices->appends(request()->query())->onEachSide(1)->links() }}</div>
            @endif
            @else
            <div class="empty-state"><div class="empty-icon">🧾</div>No invoices for this period</div>
            @endif
        </div>
    </div>

    {{-- Assigned Parties --}}
    <div class="card">
        <div class="card-head">
            <span class="card-title">Assigned Parties</span>
            <span class="tag tag-gray">{{ $allPartiesCount }} total</span>
        </div>
        <div class="card-body">
            <div class="search-wrap">
                <svg class="search-ico" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="search-inp" id="party-q" type="text" placeholder="Search name, phone, email…" autocomplete="off">
                <button class="search-clr" id="party-clr" onclick="clearSearch()">✕</button>
            </div>
            <div id="parties-default">
                @if($parties->count())
                <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                    <table class="tbl">
                        <thead><tr><th>Name</th><th>Type</th><th>Phone</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($parties as $p)
                            <tr>
                                <td style="font-weight:500">{{ $p->name }}</td>
                                <td><span class="tag tag-gray">{{ ucfirst($p->party_type) }}</span></td>
                                <td style="color:#6b7280">{{ $p->phone??'—' }}</td>
                                <td><span class="status-badge status-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($parties->lastPage() > 1)
                <div class="pgn" style="border:1px solid #e5e7eb;border-top:none;border-radius:0 0 8px 8px;margin-top:-1px;">
                    {{ $parties->appends(request()->query())->onEachSide(1)->links() }}
                </div>
                @endif
                @else
                <div class="empty-state"><div class="empty-icon">👥</div>No parties assigned</div>
                @endif
            </div>
            <div id="parties-search" style="display:none;">
                <div id="srch-loader" style="text-align:center;padding:18px;display:none;color:#9ca3af;font-size:12px;">Searching…</div>
                <div id="srch-results"></div>
            </div>
        </div>
    </div>

    {{-- Payment History --}}
    <div class="card">
        <div class="card-head"><span class="card-title">Payment History</span></div>
        <div class="card-body">
            <div class="tabs">
                <button class="tab-btn active" id="tab-comm"  onclick="switchTab('comm')">Commission</button>
                @if($salesman->salary_type !== 'commission')
                <button class="tab-btn"        id="tab-fixed" onclick="switchTab('fixed')">Fixed Salary</button>
                @endif
            </div>

            <div id="panel-comm">
                @if($commissionPayments->count())
                <div style="overflow-x:auto;border:1px solid #e5e7eb;border-radius:8px;">
                    <table class="tbl">
                        <thead><tr><th>Date</th><th>Sales Total</th><th>Paid</th><th>Type</th><th>Method</th><th>Note</th></tr></thead>
                        <tbody>
                            @foreach($commissionPayments as $cp)
                            <tr>
                                <td style="white-space:nowrap">
                                    {{ \Carbon\Carbon::parse($cp->paid_at)->format('d M Y') }}<br>
                                    <small style="color:#9ca3af">{{ \Carbon\Carbon::parse($cp->paid_at)->format('h:i A') }}</small>
                                </td>
                                <td>₹{{ number_format($cp->sales_total??0,2) }}</td>
                                <td style="white-space:nowrap">
                                    <span style="font-weight:600;color:#15803d">₹{{ number_format($cp->amount,2) }}</span>
                                    @if(!empty($cp->full_amount) && (float)$cp->full_amount > (float)$cp->amount)
                                    <br><small style="color:#9ca3af">of ₹{{ number_format($cp->full_amount,2) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($cp->is_partial))
                                        <span class="tag tag-orange">Partial</span>
                                    @else
                                        <span class="tag tag-green">Full</span>
                                    @endif
                                </td>
                                <td><span class="tag tag-gray">{{ ucwords(str_replace('_',' ',$cp->payment_method??'—')) }}</span></td>
                                <td style="color:#6b7280;font-size:12px;">{{ $cp->notes??'—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:8px;display:flex;justify-content:space-between;font-size:12px;color:#6b7280;">
                    <span>{{ $commissionPayments->count() }} payment(s)</span>
                    <span style="font-weight:600;color:#15803d">Total: ₹{{ number_format($totalCommissionPaid,2) }}</span>
                </div>
                @else
                <div class="empty-state"><div class="empty-icon">💳</div>No commission payments yet</div>
                @endif
            </div>

            @if($salesman->salary_type !== 'commission')
            <div id="panel-fixed" style="display:none;">
                @if($fixedPayments->count())
                <div style="overflow-x:auto;border:1px solid #e5e7eb;border-radius:8px;">
                    <table class="tbl">
                        <thead><tr><th>Date</th><th>Period</th><th>Paid</th><th>Type</th><th>Method</th><th>Note</th></tr></thead>
                        <tbody>
                            @foreach($fixedPayments as $fp)
                            <tr>
                                <td style="white-space:nowrap">
                                    {{ \Carbon\Carbon::parse($fp->paid_at)->format('d M Y') }}<br>
                                    <small style="color:#9ca3af">{{ \Carbon\Carbon::parse($fp->paid_at)->format('h:i A') }}</small>
                                </td>
                                <td style="white-space:nowrap;font-size:12px;">
                                    {{ \Carbon\Carbon::parse($fp->period_start)->addHours(5)->addMinutes(30)->format('d M Y') }}
                                    <br><span style="color:#9ca3af">to {{ \Carbon\Carbon::parse($fp->period_end)->addHours(5)->addMinutes(30)->format('d M Y') }}</span>
                                </td>
                                <td style="white-space:nowrap;">
                                    <span style="font-weight:600;color:#15803d">₹{{ number_format($fp->amount,2) }}</span>
                                    @if(!empty($fp->full_amount) && (float)$fp->full_amount > (float)$fp->amount)
                                    <br><small style="color:#9ca3af">of ₹{{ number_format($fp->full_amount,2) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($fp->is_partial))
                                        <span class="tag tag-orange">Partial</span>
                                    @else
                                        <span class="tag tag-green">Full</span>
                                    @endif
                                </td>
                                <td><span class="tag tag-gray">{{ ucwords(str_replace('_',' ',$fp->payment_method??'—')) }}</span></td>
                                <td style="color:#6b7280;font-size:12px;">{{ $fp->notes??'—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:8px;display:flex;justify-content:space-between;font-size:12px;color:#6b7280;">
                    <span>{{ $fixedPayments->count() }} payment(s)</span>
                    <span style="font-weight:600;color:#15803d">Total: ₹{{ number_format($totalFixedPaid,2) }}</span>
                </div>
                @else
                <div class="empty-state"><div class="empty-icon">💳</div>No fixed salary payments yet</div>
                @endif
            </div>
            @endif
        </div>
    </div>

</div>
</div>
</div>

{{-- COMMISSION MODAL --}}
<div class="modal-overlay" id="modal-comm">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-title" id="comm-modal-title">Pay Commission</div>
            <button class="modal-close" onclick="closeModal('modal-comm')">×</button>
        </div>
        <div class="modal-body">
            <div id="comm-sum-box" class="sum-box"></div>
            <div id="comm-partial-section" style="display:none;">
                <div class="mf-grp">
                    <label class="mf-label">Partial Amount (₹) <span style="color:#dc2626">*</span></label>
                    <input type="number" class="mf-inp" id="comm-partial-input" placeholder="Enter amount" min="0.01" step="0.01">
                    <div class="mf-hint">Maximum: <strong id="comm-max-label"></strong></div>
                </div>
            </div>
            <div class="mf-grp">
                <label class="mf-label">Payment Method</label>
                <select class="mf-sel" id="comm-method">
                    <option value="cash">Cash</option>
                    <option value="upi">UPI</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cheque">Cheque</option>
                </select>
            </div>
            <div class="mf-grp">
                <label class="mf-label">Note (optional)</label>
                <input type="text" class="mf-inp" id="comm-note" placeholder="Optional note…">
            </div>
        </div>
        <div class="modal-ftr">
            <button class="btn btn-ghost" onclick="closeModal('modal-comm')">Cancel</button>
            <button class="btn btn-green" id="comm-pay-btn" onclick="submitCommPay()">Confirm Payment</button>
        </div>
    </div>
</div>

{{-- FIXED SALARY MODAL --}}
<div class="modal-overlay" id="modal-fixed">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-title" id="fixed-modal-title">Pay Fixed Salary</div>
            <button class="modal-close" onclick="closeModal('modal-fixed')">×</button>
        </div>
        <div class="modal-body">
            <div class="mf-grp">
                <label class="mf-label">Select Period</label>
                <select class="mf-sel" id="fixed-period-sel" onchange="onFixedPeriodChange()"></select>
            </div>
            <div id="fixed-status-area"></div>
            <div id="fixed-pay-fields">
                <div class="mf-grp">
                    <label class="mf-label">Payment Method</label>
                    <select class="mf-sel" id="fixed-method">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <label class="partial-toggle" for="fixed-partial-chk">
                    <input type="checkbox" id="fixed-partial-chk" onchange="onFixedPartialToggle()">
                    <span>Pay partial amount instead of full remaining</span>
                </label>
                <div class="partial-amt-box" id="fixed-partial-box">
                    <div class="mf-grp">
                        <label class="mf-label">Partial Amount (₹) <span style="color:#dc2626">*</span></label>
                        <input type="number" class="mf-inp" id="fixed-partial-input" placeholder="Enter amount" min="0.01" step="0.01">
                        <div class="mf-hint">Remaining: <strong id="fixed-remaining-label" style="color:#ea580c"></strong></div>
                    </div>
                </div>
                <div class="mf-grp">
                    <label class="mf-label">Note (optional)</label>
                    <input type="text" class="mf-inp" id="fixed-note" placeholder="Optional note…">
                </div>
            </div>
        </div>
        <div class="modal-ftr">
            <button class="btn btn-ghost" onclick="closeModal('modal-fixed')">Cancel</button>
            <button class="btn btn-green" id="fixed-pay-btn" onclick="submitFixedPay()">Confirm Payment</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

@push('scripts')
<script>
const SALESMAN_ID  = '{{ $salesman->id }}';
const CSRF         = '{{ csrf_token() }}';
const PENDING_COMM = {{ round($pendingCommission, 2) }};
const DUE_PER_PERIOD = {{ $duePerPeriod }};
const CURR_YEAR    = {{ $currYear }};
const CURR_MONTH   = {{ $currMonth }};
const FILTER_YEAR  = {{ $filterYear }};
const FILTER_MONTH = {{ $filterMonth }};
const PAID_FIXED   = {!! $paidFixedJson !!};
// Full schedule from PHP — each entry has label, period_start, period_end, due, paid, remaining, is_paid
const SCHEDULE     = {!! $scheduleJson !!};
const MONTH_NAMES  = ['','January','February','March','April','May','June','July','August','September','October','November','December'];

let commIsPartial = false;

// ── Month filter (invoice filter) ─────────────────────────────────────────
function rebuildMonths() {
    const yr  = parseInt(document.getElementById('f-year').value, 10);
    const sel = document.getElementById('f-month');
    const prev = parseInt(sel.value, 10) || 0;
    const max  = (yr === CURR_YEAR) ? CURR_MONTH : 12;
    sel.innerHTML = '<option value="">All Months</option>';
    for (let m = 1; m <= max; m++) {
        const o = document.createElement('option');
        o.value = m; o.textContent = MONTH_NAMES[m];
        if (m === prev) o.selected = true;
        sel.appendChild(o);
    }
}

// ── Invoice filters ───────────────────────────────────────────────────────
function applyFilters() {
    const url = new URL(location.href);
    const m = document.getElementById('f-month').value;
    const y = document.getElementById('f-year').value;
    const t = document.getElementById('f-type').value;
    const s = document.getElementById('f-start').value;
    const e = document.getElementById('f-end').value;

    if (s || e) { url.searchParams.delete('month'); url.searchParams.delete('year'); }
    else {
        m ? url.searchParams.set('month', m) : url.searchParams.delete('month');
        y ? url.searchParams.set('year', y)  : url.searchParams.delete('year');
    }
    t ? url.searchParams.set('party_type', t) : url.searchParams.delete('party_type');
    s ? url.searchParams.set('start_date', s) : url.searchParams.delete('start_date');
    e ? url.searchParams.set('end_date', e)   : url.searchParams.delete('end_date');
    url.searchParams.delete('invoice_page'); url.searchParams.delete('party_page');
    location.href = url.toString();
}
function resetFilters() {
    const url = new URL(location.href);
    ['month','year','party_type','start_date','end_date','invoice_page','party_page'].forEach(k => url.searchParams.delete(k));
    location.href = url.toString();
}

// ── Commission Modal ──────────────────────────────────────────────────────
function openCommModal(isPartial) {
    commIsPartial = isPartial;
    document.getElementById('comm-note').value = '';
    document.getElementById('comm-modal-title').textContent = isPartial ? 'Pay Partial Commission' : 'Pay Commission';

    const totalPaid = parseFloat('{{ $totalCommissionPaid }}');
    const totalEarned = parseFloat('{{ $totalEarnedCommission }}');

    document.getElementById('comm-sum-box').innerHTML = sumRows([
        { l: 'Total Earned (All Time)', v: '₹' + fmt(totalEarned),   c: '' },
        { l: 'Total Paid (All Time)',   v: '₹' + fmt(totalPaid),     c: 'green' },
        { l: 'Pending Commission',      v: '₹' + fmt(PENDING_COMM),  c: PENDING_COMM > 0 ? 'orange' : 'green' },
    ]);

    const ps = document.getElementById('comm-partial-section');
    ps.style.display = isPartial ? 'block' : 'none';
    if (isPartial) {
        document.getElementById('comm-max-label').textContent = '₹' + fmt(PENDING_COMM);
        document.getElementById('comm-partial-input').value = '';
    }

    const btn = document.getElementById('comm-pay-btn');
    btn.disabled = false;
    btn.textContent = isPartial ? 'Confirm Partial Payment' : 'Pay ₹' + fmt(PENDING_COMM);
    openModal('modal-comm');
}

function submitCommPay() {
    const btn = document.getElementById('comm-pay-btn');
    let partial = null;
    if (commIsPartial) {
        partial = parseFloat(document.getElementById('comm-partial-input').value);
        if (isNaN(partial) || partial <= 0) { toast('Enter a valid amount', false); return; }
        if (partial > PENDING_COMM) { toast('Amount cannot exceed ₹' + fmt(PENDING_COMM), false); return; }
    }
    btn.disabled = true; btn.textContent = 'Processing…';

    const payload = {
        payment_method: document.getElementById('comm-method').value,
        notes: document.getElementById('comm-note').value,
        is_partial: commIsPartial,
    };
    if (commIsPartial) payload.partial_amount = partial;

    post(`/admin/salesmen/${SALESMAN_ID}/pay-commission`, payload)
        .then(d => {
            btn.disabled = false; btn.textContent = 'Confirm Payment';
            if (d.success) { closeModal('modal-comm'); toast(d.message, true); setTimeout(() => location.reload(), 1500); }
            else toast(d.error || 'Payment failed', false);
        })
        .catch(() => { btn.disabled = false; btn.textContent = 'Confirm Payment'; toast('Network error', false); });
}

// ── Fixed Salary Modal ────────────────────────────────────────────────────
function buildFixedPeriods() {
    const sel = document.getElementById('fixed-period-sel');
    sel.innerHTML = '';

    if (!SCHEDULE || SCHEDULE.length === 0) return;

    let firstUnpaidIdx = -1;

    SCHEDULE.forEach(function(period, idx) {
        const opt = document.createElement('option');
        // Store period_start and period_end in value separated by pipe
        opt.value = period.period_start + '|' + period.period_end;

        let label = period.label;
        if (period.is_paid) {
            label += '  ✓ Paid';
        } else if (period.paid > 0) {
            label += '  (₹' + fmt(period.paid) + ' paid, ₹' + fmt(period.remaining) + ' left)';
        } else {
            label += '  — ₹' + fmt(period.due) + ' due';
        }

        opt.textContent   = label;
        opt.dataset.due       = period.due;
        opt.dataset.paid      = period.paid;
        opt.dataset.remaining = period.remaining;
        opt.dataset.isPaid    = period.is_paid ? '1' : '0';
        opt.dataset.label     = period.label;

        sel.appendChild(opt);

        // Track first unpaid period
        if (!period.is_paid && firstUnpaidIdx === -1) {
            firstUnpaidIdx = idx;
        }
    });

    // Auto-select first unpaid period
    if (firstUnpaidIdx >= 0) {
        sel.selectedIndex = firstUnpaidIdx;
    }
}

function onFixedPeriodChange() {
    const sel      = document.getElementById('fixed-period-sel');
    const selected = sel.options[sel.selectedIndex];
    if (!selected) return;

    const due       = parseFloat(selected.dataset.due       || 0);
    const paid      = parseFloat(selected.dataset.paid      || 0);
    const remaining = parseFloat(selected.dataset.remaining || 0);
    const isPaid    = selected.dataset.isPaid === '1';

    document.getElementById('fixed-remaining-label').textContent = '₹' + fmt(remaining);

    const statusArea = document.getElementById('fixed-status-area');
    const payFields  = document.getElementById('fixed-pay-fields');
    const btn        = document.getElementById('fixed-pay-btn');

    if (isPaid) {
        statusArea.innerHTML    = `<div class="paid-notice">✓ Fully paid for this period</div>`;
        payFields.style.display = 'none';
        btn.disabled = true; btn.textContent = 'Fully Paid';
    } else {
        const rows = [{ l: 'Period Due', v: '₹' + fmt(due), c: '' }];
        if (paid > 0) {
            rows.push({ l: 'Already Paid', v: '₹' + fmt(paid),      c: 'green'  });
            rows.push({ l: 'Remaining',    v: '₹' + fmt(remaining),  c: 'orange' });
        } else {
            rows.push({ l: 'Amount Due',   v: '₹' + fmt(remaining),  c: 'orange' });
        }
        statusArea.innerHTML    = sumRows(rows);
        payFields.style.display = 'block';
        btn.disabled            = false;
        btn.textContent         = 'Confirm Payment';
    }
}

function onFixedPartialToggle() {
    const checked = document.getElementById('fixed-partial-chk').checked;
    document.getElementById('fixed-partial-box').classList.toggle('show', checked);
}

function openFixedModal(forcePartial) {
    buildFixedPeriods();
    document.getElementById('fixed-partial-chk').checked = forcePartial;
    document.getElementById('fixed-partial-box').classList.toggle('show', forcePartial);
    document.getElementById('fixed-partial-input').value = '';
    document.getElementById('fixed-note').value = '';
    document.getElementById('fixed-modal-title').textContent = forcePartial ? 'Pay Partial Fixed Salary' : 'Pay Fixed Salary';
    onFixedPeriodChange();
    openModal('modal-fixed');
}

function submitFixedPay() {
    const sel      = document.getElementById('fixed-period-sel');
    const selected = sel.options[sel.selectedIndex];
    if (!selected) { toast('No period selected', false); return; }

    const parts      = sel.value.split('|');
    const periodStart = parts[0]; // e.g. "2026-01-01"
    const periodEnd   = parts[1]; // e.g. "2026-03-31"

    const method    = document.getElementById('fixed-method').value;
    const note      = document.getElementById('fixed-note').value;
    const isPartial = document.getElementById('fixed-partial-chk').checked;
    const btn       = document.getElementById('fixed-pay-btn');

    const remaining = parseFloat(selected.dataset.remaining || 0);

    let partial = null;
    if (isPartial) {
        partial = parseFloat(document.getElementById('fixed-partial-input').value);
        if (isNaN(partial) || partial <= 0) { toast('Enter a valid amount', false); return; }
        if (partial > remaining) { toast('Amount cannot exceed remaining ₹' + fmt(remaining), false); return; }
    }

    btn.disabled = true; btn.textContent = 'Processing…';

    const payload = {
        period_start:   periodStart,
        period_end:     periodEnd,
        payment_method: method,
        notes:          note,
        is_partial:     isPartial,
    };
    if (isPartial) payload.partial_amount = partial;

    post(`/admin/salesmen/${SALESMAN_ID}/pay-fixed`, payload)
        .then(d => {
            btn.disabled = false; btn.textContent = 'Confirm Payment';
            if (d.success) {
                closeModal('modal-fixed');
                toast(d.message, true);
                setTimeout(() => location.reload(), 1500);
            } else toast(d.error || 'Payment failed', false);
        })
        .catch(() => { btn.disabled = false; btn.textContent = 'Confirm Payment'; toast('Network error', false); });
}

// ── Party search ──────────────────────────────────────────────────────────
let searchTimer = null;
const qInput = document.getElementById('party-q');
const clrBtn = document.getElementById('party-clr');

qInput.addEventListener('input', function () {
    const q = this.value.trim();
    clrBtn.style.display = q ? 'block' : 'none';
    if (!q) { clearSearch(); return; }
    if (q.length < 2) return;
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => doSearch(q), 300);
});

function clearSearch() {
    qInput.value = ''; clrBtn.style.display = 'none';
    document.getElementById('parties-default').style.display = 'block';
    document.getElementById('parties-search').style.display  = 'none';
}

function doSearch(q) {
    document.getElementById('parties-default').style.display = 'none';
    document.getElementById('parties-search').style.display  = 'block';
    document.getElementById('srch-loader').style.display     = 'block';
    document.getElementById('srch-results').innerHTML        = '';

    fetch(`/admin/salesmen/${SALESMAN_ID}/search-parties?q=${encodeURIComponent(q)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('srch-loader').style.display = 'none';
        if (!data || !data.length) {
            document.getElementById('srch-results').innerHTML = '<div class="empty-state"><div class="empty-icon">🔍</div>No parties found</div>';
            return;
        }
        document.getElementById('srch-results').innerHTML = `
            <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                <table class="tbl"><thead><tr><th>Name</th><th>Type</th><th>Phone</th><th>Status</th></tr></thead>
                <tbody>${data.map(p=>`<tr>
                    <td style="font-weight:500">${h(p.name)}</td>
                    <td><span class="tag tag-gray">${h(cap(p.party_type))}</span></td>
                    <td style="color:#6b7280">${h(p.phone||'—')}</td>
                    <td><span class="status-badge status-${p.status==='active'?'active':'inactive'}">${h(cap(p.status))}</span></td>
                </tr>`).join('')}</tbody></table>
            </div>
            <div style="padding:5px 0;font-size:12px;color:#9ca3af;">${data.length} result${data.length!==1?'s':''}</div>`;
    })
    .catch(() => { document.getElementById('srch-loader').style.display = 'none'; });
}

// ── Tabs ──────────────────────────────────────────────────────────────────
function switchTab(t) {
    ['comm','fixed'].forEach(x => {
        const p = document.getElementById('panel-'+x);
        const b = document.getElementById('tab-'+x);
        if (p) p.style.display = x===t ? 'block' : 'none';
        if (b) b.classList.toggle('active', x===t);
    });
}

// ── Helpers ───────────────────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function sumRows(rows) {
    return '<div class="sum-box">' + rows.map(r =>
        `<div class="sum-row"><span class="sum-lbl">${r.l}</span><span class="sum-val ${r.c||''}">${r.v}</span></div>`
    ).join('') + '</div>';
}

function post(url, data) {
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(data),
    }).then(r => r.json());
}

function toast(msg, ok = true) {
    const t = document.getElementById('toast');
    t.textContent = (ok ? '✓  ' : '✗  ') + msg;
    t.style.background = ok ? '#15803d' : '#dc2626';
    t.classList.add('show');
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.remove('show'), 3500);
}

function fmt(n) {
    return parseFloat(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function h(s)   { const d = document.createElement('div'); d.textContent = s||''; return d.innerHTML; }
function cap(s) { return s ? s[0].toUpperCase() + s.slice(1) : ''; }

// ── Init ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    rebuildMonths();
    if (FILTER_MONTH > 0) {
        const sel = document.getElementById('f-month');
        if (sel) sel.value = FILTER_MONTH;
    }
    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', e => { if (e.target === el) closeModal(el.id); });
    });
});
</script>
@endpush
@endsection
