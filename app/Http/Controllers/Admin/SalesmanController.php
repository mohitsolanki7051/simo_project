<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salesman;
use App\Models\SalesmanPayment;
use App\Models\Customer;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SalesmanController extends Controller
{
    // ── INDEX ─────────────────────────────────────────────────────
    public function index()
    {
         $salesmen = Salesman::whereNull('type')->get();

        $totalCustomers           = Customer::where('party_type', 'customer')->whereNotNull('salesman_id')->count();
        $totalDealers             = Customer::where('party_type', 'dealer')->whereNotNull('salesman_id')->count();
        $totalDistributors        = Customer::where('party_type', 'distributor')->whereNotNull('salesman_id')->count();
        $totalPartiesWithSalesman = $totalCustomers + $totalDealers + $totalDistributors;

        return view('admin.salesmen.index', compact(
            'salesmen', 'totalCustomers', 'totalDealers', 'totalDistributors', 'totalPartiesWithSalesman'
        ));
    }

    // ── CREATE / STORE ────────────────────────────────────────────
    public function create()
    {
        return view('admin.salesmen.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                           => 'required|string|max:255',
            'phone'                          => 'required|string|max:10|min:10',
            'email'                          => 'nullable|email|max:255',
            'joining_date'                   => 'required|date',
            'salary_type'                    => 'required|in:fixed,commission,both',
            'fixed_salary'                   => 'nullable|numeric|min:0|required_if:salary_type,fixed,both',
            'fixed_salary_period'            => 'nullable|in:monthly,quarterly,half_yearly,yearly|required_if:salary_type,fixed,both',
            'commission_enabled'             => 'nullable|boolean',
            'commission_period'              => 'nullable|in:monthly,quarterly,half_yearly,yearly|required_if:commission_enabled,1',
            'customer_commission_percent'    => 'nullable|numeric|min:0|max:100',
            'dealer_commission_percent'      => 'nullable|numeric|min:0|max:100',
            'distributor_commission_percent' => 'nullable|numeric|min:0|max:100',
            'status'                         => 'required|in:active,inactive',
            'notes'                          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data                       = $request->all();
        $data['commission_enabled'] = $request->has('commission_enabled');

        if (!$data['commission_enabled']) {
            $data['customer_commission_percent']    = 0;
            $data['dealer_commission_percent']      = 0;
            $data['distributor_commission_percent'] = 0;
            $data['commission_period']              = null;
        }

        if ($request->salary_type === 'commission') {
            $data['fixed_salary']        = null;
            $data['fixed_salary_period'] = null;
        }

        Salesman::create($data);
        return redirect()->route('admin.salesmen.index')->with('success', 'Salesman created successfully');
    }

    // ── SHOW ──────────────────────────────────────────────────────
public function show($id, Request $request)
{
    $salesman = Salesman::whereNull('type')->find($id);
    if (!$salesman) {
        return redirect()->route('admin.salesmen.index')->with('error', 'Salesman not found');
    }

    // ── Filters ───────────────────────────────────────────────────────────
    $filters = [
        'month'      => $request->get('month', ''),
        'year'       => $request->get('year', date('Y')),
        'party_type' => $request->get('party_type', ''),
        'start_date' => $request->get('start_date', ''),
        'end_date'   => $request->get('end_date', ''),
    ];

    // ── Reusable date filter closure ──────────────────────────────────────
    $applyDateFilter = function ($query, $dateColumn) use ($filters) {
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween($dateColumn, [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay(),
            ]);
        } elseif (!empty($filters['start_date'])) {
            $query->whereDate($dateColumn, '>=', $filters['start_date']);
        } elseif (!empty($filters['end_date'])) {
            $query->whereDate($dateColumn, '<=', $filters['end_date']);
        } else {
            if (!empty($filters['month'])) {
                $query->whereMonth($dateColumn, (int) $filters['month']);
            }
            if (!empty($filters['year'])) {
                $query->whereYear($dateColumn, (int) $filters['year']);
            }
        }
        return $query;
    };

    // ── Invoice filter closure ────────────────────────────────────────────
    $applyInvoiceFilters = function ($query) use ($filters, $id, $applyDateFilter) {
        $query->where('salesman_id', $id)
              ->whereIn('status', ['completed', 'confirmed', 'generated']);

        $applyDateFilter($query, 'invoice_date');

        if (!empty($filters['party_type'])) {
            $query->whereHas('party', fn($q) => $q->where('party_type', $filters['party_type']));
        }

        return $query;
    };

    // ── Filtered sales ────────────────────────────────────────────────────
    $sales      = $applyInvoiceFilters(SalesInvoice::with('party'))->orderBy('invoice_date', 'desc')->get();
    $totalSales = (float) $sales->sum('grand_total');

    $commissionEarned = 0;
    $salesBreakdown   = [
        'customer'    => ['count' => 0, 'total' => 0, 'commission' => 0],
        'dealer'      => ['count' => 0, 'total' => 0, 'commission' => 0],
        'distributor' => ['count' => 0, 'total' => 0, 'commission' => 0],
    ];

    foreach ($sales as $invoice) {
        $pt   = $invoice->party->party_type ?? 'customer';
        $rate = $salesman->getCommissionRateForPartyType($pt);
        $amt  = (float) $invoice->grand_total;
        $comm = $amt * $rate / 100;

        $commissionEarned                  += $comm;
        $salesBreakdown[$pt]['count']++;
        $salesBreakdown[$pt]['total']      += $amt;
        $salesBreakdown[$pt]['commission'] += $comm;
    }

    // ── Paginated invoices ────────────────────────────────────────────────
    $invoicesQuery      = $applyInvoiceFilters(SalesInvoice::with('party'))->orderBy('invoice_date', 'desc');
    $totalInvoicesCount = $invoicesQuery->count();
    $allInvoices        = $invoicesQuery->paginate(10, ['*'], 'invoice_page');

    // ── Filtered parties ──────────────────────────────────────────────────
    $partiesQuery = Customer::where('salesman_id', $id);
    $applyDateFilter($partiesQuery, 'created_at');

    if (!empty($filters['party_type'])) {
        $partiesQuery->where('party_type', $filters['party_type']);
    }

    $partiesQuery->orderBy('created_at', 'desc');

    $allPartiesCount = $partiesQuery->count();
    $parties         = $partiesQuery->paginate(10, ['*'], 'party_page');

    // partyCounts — filtered by same date filters
    $partyCounts = [
        'customers'    => Customer::where('salesman_id', $id)->tap(fn($q) => $applyDateFilter($q, 'created_at'))->where('party_type', 'customer')->count(),
        'dealers'      => Customer::where('salesman_id', $id)->tap(fn($q) => $applyDateFilter($q, 'created_at'))->where('party_type', 'dealer')->count(),
        'distributors' => Customer::where('salesman_id', $id)->tap(fn($q) => $applyDateFilter($q, 'created_at'))->where('party_type', 'distributor')->count(),
    ];

    // ── All-time commission earned (for pending calculation) ──────────────
    $allSalesInvoices = SalesInvoice::where('salesman_id', $id)
        ->whereIn('status', ['completed', 'confirmed', 'generated'])
        ->with('party')
        ->get();

    $totalEarnedCommission = 0;
    foreach ($allSalesInvoices as $inv) {
        $pt                     = $inv->party->party_type ?? 'customer';
        $totalEarnedCommission += (float) $inv->grand_total
            * $salesman->getCommissionRateForPartyType($pt) / 100;
    }

    // ── Filtered total paid ───────────────────────────────────────────────
    $paidBaseQuery = SalesmanPayment::where('salesman_id', $id);
    $applyDateFilter($paidBaseQuery, 'paid_at');

    $totalCommissionPaid = (float) (clone $paidBaseQuery)->where('payment_type', 'commission')->sum('amount');
    $totalFixedPaid      = (float) (clone $paidBaseQuery)->where('payment_type', 'fixed')->sum('amount');

    // Pending commission is always all-time (not filtered)
    $allTimeTotalCommPaid = (float) SalesmanPayment::where('salesman_id', $id)
        ->where('payment_type', 'commission')
        ->sum('amount');
    $pendingCommission = max(0, round($totalEarnedCommission - $allTimeTotalCommPaid, 2));

    // ── Fixed salary schedule ─────────────────────────────────────────────
    $pendingFixedSalary  = 0;
    $fixedSalarySchedule = [];

    if ($salesman->salary_type !== 'commission' && $salesman->fixed_salary > 0) {
        $joiningDate  = Carbon::parse($salesman->joining_date);
        $today        = Carbon::now();
        $periodMonths = $this->getPeriodMonths($salesman->fixed_salary_period ?? 'monthly');
        $duePerPeriod = (float) $salesman->fixed_salary * $periodMonths;

        $cursor             = $this->getAlignedPeriodStart($joiningDate, $periodMonths);
        $currentPeriodStart = $this->getAlignedPeriodStart($today, $periodMonths);

        while ($cursor->lte($currentPeriodStart)) {
            $periodEndDate = $cursor->copy()->addMonths($periodMonths)->subDay();
            $key           = $cursor->format('Y-m');

            $paidAmount = (float) SalesmanPayment::where('salesman_id', $id)
                ->where('payment_type', 'fixed')
                ->whereBetween('period_start', [
                    $cursor->copy()->setTime(0, 0, 0),
                    $periodEndDate->copy()->setTime(23, 59, 59),
                ])
                ->sum('amount');

            $remaining = max(0, round($duePerPeriod - $paidAmount, 2));

            $fixedSalarySchedule[$key] = [
                'label'        => $this->getPeriodLabel($cursor, $periodMonths),
                'period_start' => $cursor->format('Y-m-d'),
                'period_end'   => $periodEndDate->format('Y-m-d'),
                'due'          => $duePerPeriod,
                'paid'         => round($paidAmount, 2),
                'remaining'    => $remaining,
                'is_paid'      => $remaining <= 0,
            ];

            $pendingFixedSalary += $remaining;
            $cursor->addMonths($periodMonths);
        }
    }

    $pendingFixedSalary = round($pendingFixedSalary, 2);

    // ── Payment histories ─────────────────────────────────────────────────
    $commissionPayments = SalesmanPayment::where('salesman_id', $id)
        ->where('payment_type', 'commission')
        ->orderBy('paid_at', 'desc')
        ->get();

    $fixedPayments = SalesmanPayment::where('salesman_id', $id)
        ->where('payment_type', 'fixed')
        ->orderBy('paid_at', 'desc')
        ->get();

    // Build paid-periods map for JS { 'Y-m': total_paid }
    $paidFixedMonths = [];
    foreach ($fixedPayments as $fp) {
        $key = Carbon::parse($fp->period_start)
            ->addHours(5)->addMinutes(30)
            ->format('Y-m');
        $paidFixedMonths[$key] = ($paidFixedMonths[$key] ?? 0) + (float) $fp->amount;
    }

    return view('admin.salesmen.show', compact(
        'salesman', 'filters',
        'totalSales', 'commissionEarned', 'salesBreakdown',
        'allInvoices', 'totalInvoicesCount',
        'parties', 'allPartiesCount', 'partyCounts',
        'pendingCommission', 'totalEarnedCommission', 'totalCommissionPaid',
        'pendingFixedSalary', 'fixedSalarySchedule',
        'commissionPayments', 'fixedPayments',
        'totalCommissionPaid', 'totalFixedPaid',
        'paidFixedMonths'
    ));
}

    // ── PAY COMMISSION ────────────────────────────────────────────
    public function payCommission(Request $request, $id)
    {
        $salesman = Salesman::whereNull('type')->find($id);
        if (!$salesman) return response()->json(['error' => 'Salesman not found'], 404);

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,upi,bank_transfer,cheque',
            'notes'          => 'nullable|string|max:500',
            'is_partial'     => 'nullable|boolean',
            'partial_amount' => 'nullable|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $allInvoices = SalesInvoice::where('salesman_id', $id)
            ->whereIn('status', ['completed', 'confirmed', 'generated'])
            ->with('party')
            ->get();
        $totalEarned = 0;
        $salesTotal  = 0;
        foreach ($allInvoices as $inv) {
            $pt          = $inv->party->party_type ?? 'customer';
            $amount      = (float) $inv->grand_total;
            $salesTotal += $amount;
            $totalEarned += $amount * $salesman->getCommissionRateForPartyType($pt) / 100;
        }

        $totalPaid     = (float) SalesmanPayment::where('salesman_id', $id)
            ->where('payment_type', 'commission')->sum('amount');
        $pendingAmount = max(0, round($totalEarned - $totalPaid, 2));

        if ($pendingAmount <= 0) {
            return response()->json(['error' => 'No pending commission to pay.'], 422);
        }

        $isPartial = filter_var($request->get('is_partial', false), FILTER_VALIDATE_BOOLEAN);

        if ($isPartial) {
            $partialAmount = round((float) $request->partial_amount, 2);
            if ($partialAmount <= 0 || $partialAmount > $pendingAmount) {
                return response()->json([
                    'error' => 'Partial amount must be between ₹0.01 and ₹' . number_format($pendingAmount, 2)
                ], 422);
            }
            $payableAmount = $partialAmount;
        } else {
            $payableAmount = $pendingAmount;
        }

        $now = Carbon::now();

        SalesmanPayment::create([
            'salesman_id'    => $id,
            'payment_type'   => 'commission',
            'amount'         => $payableAmount,
            'full_amount'    => $pendingAmount,
            'is_partial'     => $isPartial || ($payableAmount < $pendingAmount),
            'period_type'    => $salesman->commission_period ?? 'monthly',
            'period_start'   => $now->copy()->startOfMonth()->setTime(12, 0, 0),
            'period_end'     => $now->copy()->setTime(12, 0, 0),
            'sales_total'    => round($salesTotal, 2),
            'payment_method' => $request->payment_method,
            'notes'          => $request->notes,
            'paid_at'        => $now,
            'paid_by'        => auth()->user()->name ?? 'Admin',
        ]);

        return response()->json([
            'success'     => true,
            'message'     => ($isPartial ? 'Partial commission' : 'Commission')
                . ' ₹' . number_format($payableAmount, 2) . ' paid successfully.',
            'new_pending' => max(0, round($pendingAmount - $payableAmount, 2)),
        ]);
    }

    // ── PAY FIXED ─────────────────────────────────────────────────
    public function payFixed(Request $request, $id)
    {
        $salesman = Salesman::whereNull('type')->find($id);
        if (!$salesman) return response()->json(['error' => 'Salesman not found'], 404);

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,upi,bank_transfer,cheque',
            'period_start'   => 'required|date_format:Y-m-d',
            'period_end'     => 'required|date_format:Y-m-d',
            'notes'          => 'nullable|string|max:500',
            'is_partial'     => 'nullable|boolean',
            'partial_amount' => 'nullable|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $periodMonths = $this->getPeriodMonths($salesman->fixed_salary_period ?? 'monthly');
        $fullAmount   = (float) $salesman->fixed_salary * $periodMonths;

        if ($fullAmount <= 0) {
            return response()->json(['error' => 'No fixed salary configured.'], 422);
        }

        $periodStart = Carbon::parse($request->period_start);
        $periodEnd   = Carbon::parse($request->period_end);
        $isPartial   = filter_var($request->get('is_partial', false), FILTER_VALIDATE_BOOLEAN);

        $alreadyPaid = (float) SalesmanPayment::where('salesman_id', $id)
            ->where('payment_type', 'fixed')
            ->whereBetween('period_start', [
                $periodStart->copy()->setTime(0, 0, 0),
                $periodEnd->copy()->setTime(23, 59, 59),
            ])
            ->sum('amount');

        $remaining = round($fullAmount - $alreadyPaid, 2);

        if ($remaining <= 0) {
            return response()->json(['error' => 'Already fully paid for this period.'], 422);
        }

        if ($isPartial) {
            $partialAmount = round((float) $request->partial_amount, 2);
            if ($partialAmount <= 0 || $partialAmount > $remaining) {
                return response()->json([
                    'error' => 'Partial amount must be between ₹0.01 and ₹' . number_format($remaining, 2)
                ], 422);
            }
            $payableAmount = $partialAmount;
        } else {
            $payableAmount = $remaining;
        }

        SalesmanPayment::create([
            'salesman_id'    => $id,
            'payment_type'   => 'fixed',
            'amount'         => $payableAmount,
            'full_amount'    => $fullAmount,
            'is_partial'     => ($payableAmount < $fullAmount),
            'period_type'    => $salesman->fixed_salary_period ?? 'monthly',
            'period_start'   => $periodStart->copy()->setTime(12, 0, 0),
            'period_end'     => $periodEnd->copy()->setTime(12, 0, 0),
            'sales_total'    => 0,
            'payment_method' => $request->payment_method,
            'notes'          => $request->notes,
            'paid_at'        => Carbon::now(),
            'paid_by'        => auth()->user()->name ?? 'Admin',
        ]);

        $newAlreadyPaid = $alreadyPaid + $payableAmount;
        $newRemaining   = round($fullAmount - $newAlreadyPaid, 2);

        return response()->json([
            'success'      => true,
            'message'      => ($payableAmount < $fullAmount ? 'Partial salary' : 'Salary')
                . ' ₹' . number_format($payableAmount, 2) . ' paid successfully.',
            'amount'       => $payableAmount,
            'full_amount'  => $fullAmount,
            'already_paid' => $newAlreadyPaid,
            'remaining'    => $newRemaining,
            'is_partial'   => ($payableAmount < $fullAmount),
            'period_key'   => $periodStart->format('Y-m'),
        ]);
    }

    // ── CHECK FIXED PAID STATUS ───────────────────────────────────
    public function checkFixedPaidStatus(Request $request, $id)
    {
        $salesman = Salesman::find($id);
        if (!$salesman) return response()->json(['error' => 'Not found'], 404);

        $periodStart = $request->get('period_start');
        $periodEnd   = $request->get('period_end');
        if (!$periodStart || !$periodEnd) return response()->json(['error' => 'Period required'], 422);

        $periodMonths = $this->getPeriodMonths($salesman->fixed_salary_period ?? 'monthly');
        $fullAmount   = (float) $salesman->fixed_salary * $periodMonths;

        $ps = Carbon::parse($periodStart);
        $pe = Carbon::parse($periodEnd);

        $alreadyPaid = (float) SalesmanPayment::where('salesman_id', $id)
            ->where('payment_type', 'fixed')
            ->whereBetween('period_start', [
                $ps->copy()->setTime(0, 0, 0),
                $pe->copy()->setTime(23, 59, 59),
            ])
            ->sum('amount');

        $remaining = max(0, round($fullAmount - $alreadyPaid, 2));

        return response()->json([
            'full_amount'  => $fullAmount,
            'already_paid' => round($alreadyPaid, 2),
            'remaining'    => $remaining,
            'fully_paid'   => $remaining <= 0,
        ]);
    }

    // ── HELPERS ───────────────────────────────────────────────────

    private function getPeriodMonths(string $period): int
    {
        return match ($period) {
            'quarterly'   => 3,
            'half_yearly' => 6,
            'yearly'      => 12,
            default       => 1,
        };
    }

    /**
     * Period-aligned start date from a given date.
     * e.g. Joined Feb, Quarterly → Jan 1 (Q1 start)
     * e.g. Joined Aug, Half-yearly → Jul 1 (H2 start)
     */
    private function getAlignedPeriodStart(Carbon $date, int $periodMonths): Carbon
    {
        if ($periodMonths === 1) {
            return $date->copy()->startOfMonth();
        }

        $month = $date->month;
        $year  = $date->year;

        if ($periodMonths === 3) {
            // Q1=Jan(1), Q2=Apr(4), Q3=Jul(7), Q4=Oct(10)
            $startMonth = $month - (($month - 1) % 3);
        } elseif ($periodMonths === 6) {
            // H1=Jan(1), H2=Jul(7)
            $startMonth = $month <= 6 ? 1 : 7;
        } else {
            // Yearly = Jan
            $startMonth = 1;
        }

        return Carbon::create($year, $startMonth, 1)->startOfDay();
    }

    /**
     * Human-readable period label
     */
    private function getPeriodLabel(Carbon $start, int $periodMonths): string
    {
        if ($periodMonths === 1) {
            return $start->format('M Y');
        }

        $end = $start->copy()->addMonths($periodMonths)->subMonth();

        if ($periodMonths === 3) {
            $quarter = ceil($start->month / 3);
            return 'Q' . $quarter . ' ' . $start->year
                . ' (' . $start->format('M') . ' – ' . $end->format('M Y') . ')';
        }

        if ($periodMonths === 6) {
            $half = $start->month <= 6 ? 'H1' : 'H2';
            return $half . ' ' . $start->year
                . ' (' . $start->format('M') . ' – ' . $end->format('M Y') . ')';
        }

        return 'Year ' . $start->year
            . ' (' . $start->format('M') . ' – ' . $end->format('M Y') . ')';
    }

    // ── SEARCH PARTIES ────────────────────────────────────────────
    public function searchParties($id, Request $request)
    {
        if (!Salesman::find($id)) return response()->json(['error' => 'Not found'], 404);

        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) return response()->json([]);

        return response()->json(
            Customer::where('salesman_id', $id)
                ->select(['id', 'name', 'party_type', 'phone', 'email', 'status', 'gst_no'])
                ->where(fn($w) =>
                    $w->where('name',    'like', "%$q%")
                      ->orWhere('phone', 'like', "%$q%")
                      ->orWhere('email', 'like', "%$q%")
                      ->orWhere('gst_no','like', "%$q%")
                )
                ->orderBy('name')->limit(20)->get()
        );
    }

    public function getPartyCounts(Request $request)
    {
        $id = $request->get('salesman_id');
        return response()->json([
            'customers'    => Customer::where('salesman_id', $id)->where('party_type', 'customer')->count(),
            'dealers'      => Customer::where('salesman_id', $id)->where('party_type', 'dealer')->count(),
            'distributors' => Customer::where('salesman_id', $id)->where('party_type', 'distributor')->count(),
        ]);
    }

    public function getAssignedParties($id, Request $request)
    {
        if (!Salesman::find($id)) return response()->json(['error' => 'Not found'], 404);
        return response()->json(
            Customer::where('salesman_id', $id)
                ->select(['id', 'name', 'party_type', 'phone', 'email', 'status'])
                ->orderBy('name')->get()
        );
    }

    // ── EDIT / UPDATE ─────────────────────────────────────────────
    public function edit($id)
    {
        $salesman = Salesman::whereNull('type')->find($id);
        if (!$salesman) return redirect()->route('admin.salesmen.index')->with('error', 'Not found');
        return view('admin.salesmen.edit', compact('salesman'));
    }

    public function update(Request $request, $id)
    {
        $salesman = Salesman::whereNull('type')->find($id);
        if (!$salesman) return redirect()->route('admin.salesmen.index')->with('error', 'Not found');

        $validator = Validator::make($request->all(), [
            'name'                           => 'required|string|max:255',
            'phone'                          => 'required|string|max:10|min:10',
            'email'                          => 'nullable|email|max:255',
            'joining_date'                   => 'required|date',
            'salary_type'                    => 'required|in:fixed,commission,both',
            'fixed_salary'                   => 'nullable|numeric|min:0|required_if:salary_type,fixed,both',
            'fixed_salary_period'            => 'nullable|in:monthly,quarterly,half_yearly,yearly|required_if:salary_type,fixed,both',
            'commission_enabled'             => 'nullable|boolean',
            'commission_period'              => 'nullable|in:monthly,quarterly,half_yearly,yearly|required_if:commission_enabled,1',
            'customer_commission_percent'    => 'nullable|numeric|min:0|max:100',
            'dealer_commission_percent'      => 'nullable|numeric|min:0|max:100',
            'distributor_commission_percent' => 'nullable|numeric|min:0|max:100',
            'status'                         => 'required|in:active,inactive',
            'notes'                          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data                       = $request->all();
        $data['commission_enabled'] = $request->has('commission_enabled');

        if (!$data['commission_enabled']) {
            $data['customer_commission_percent']    = 0;
            $data['dealer_commission_percent']      = 0;
            $data['distributor_commission_percent'] = 0;
            $data['commission_period']              = null;
        }

        if ($request->salary_type === 'commission') {
            $data['fixed_salary']        = null;
            $data['fixed_salary_period'] = null;
        }

        $salesman->update($data);
        return redirect()->route('admin.salesmen.index')->with('success', 'Updated successfully');
    }

    // ── CHECK PHONE / EMAIL ───────────────────────────────────────
    public function checkPhone(Request $request)
    {
        $q = Salesman::where('phone', $request->get('phone'));
        if ($eid = $request->get('exclude_id')) $q->where('_id', '!=', $eid);
        return response()->json(['exists' => $q->exists()]);
    }

    public function checkEmail(Request $request)
    {
        $q = Salesman::where('email', $request->get('email'));
        if ($eid = $request->get('exclude_id')) $q->where('_id', '!=', $eid);
        return response()->json(['exists' => $q->exists()]);
    }

    // ── BULK STATUS ───────────────────────────────────────────────
    public function bulkUpdateStatus(Request $request)
    {
        $ids    = $request->get('ids', []);
        $status = $request->get('status');
        if (!in_array($status, ['active', 'inactive']) || empty($ids)) {
            return response()->json(['error' => 'Invalid request'], 422);
        }
        Salesman::whereIn('_id', $ids)->update(['status' => $status]);
        return response()->json(['success' => true]);
    }

// ── DOWNLOAD REPORT (replace existing downloadReport method) ─────────────────
public function downloadReport($id, Request $request)
{
    $salesman = Salesman::find($id);
    if (!$salesman) return redirect()->route('admin.salesmen.index')->with('error', 'Not found');

    $filters = [
        'month'      => $request->get('month', ''),
        'year'       => $request->get('year', date('Y')),
        'party_type' => $request->get('party_type', ''),
        'start_date' => $request->get('start_date', ''),
        'end_date'   => $request->get('end_date', ''),
    ];

    $q = SalesInvoice::where('salesman_id', $id)
        ->whereIn('status', ['completed', 'confirmed', 'generated'])
        ->with('party')
        ->orderBy('invoice_date', 'desc');

    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
        $q->whereBetween('invoice_date', [
            Carbon::parse($filters['start_date'])->startOfDay(),
            Carbon::parse($filters['end_date'])->endOfDay(),
        ]);
    } else {
        if (!empty($filters['month'])) $q->whereMonth('invoice_date', (int) $filters['month']);
        if (!empty($filters['year']))  $q->whereYear('invoice_date', (int) $filters['year']);
    }

    if (!empty($filters['party_type'])) {
        $q->whereHas('party', fn($w) => $w->where('party_type', $filters['party_type']));
    }

    $sales      = $q->get();
    $invoices   = $sales;
    $totalSales = (float) $sales->sum('grand_total');

    $commissionEarned = 0;
    $salesBreakdown   = [
        'customer'    => ['count' => 0, 'total' => 0, 'commission' => 0],
        'dealer'      => ['count' => 0, 'total' => 0, 'commission' => 0],
        'distributor' => ['count' => 0, 'total' => 0, 'commission' => 0],
    ];

    foreach ($sales as $inv) {
        $pt   = $inv->party->party_type ?? 'customer';
        $rate = $salesman->getCommissionRateForPartyType($pt);
        $amt  = (float) $inv->grand_total;
        $comm = $amt * $rate / 100;

        $commissionEarned              += $comm;
        $salesBreakdown[$pt]['count']++;
        $salesBreakdown[$pt]['total']      += $amt;
        $salesBreakdown[$pt]['commission'] += $comm;
    }

    $parties = Customer::where('salesman_id', $id)->orderBy('name')->get();

    // Payment records
    $commissionPayments = \App\Models\SalesmanPayment::where('salesman_id', $id)
        ->where('payment_type', 'commission')
        ->orderBy('paid_at', 'desc')
        ->get();

    $fixedPayments = \App\Models\SalesmanPayment::where('salesman_id', $id)
        ->where('payment_type', 'fixed')
        ->orderBy('paid_at', 'desc')
        ->get();

    // Returns the view directly — html2pdf.js handles PDF generation in browser
    return view('admin.salesmen.pdf-report', [
        'salesman'            => $salesman,
        'sales'               => $sales,
        'invoices'            => $invoices,
        'parties'             => $parties,
        'totalSales'          => $totalSales,
        'commissionEarned'    => $commissionEarned,
        'salesBreakdown'      => $salesBreakdown,
        'generated_date'      => now()->format('d M Y h:i A'),
        'filters'             => $filters,
        'commissionPayments'  => $commissionPayments,
        'fixedPayments'       => $fixedPayments,
    ]);
}
}
