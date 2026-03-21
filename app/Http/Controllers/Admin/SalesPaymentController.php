<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use App\Models\SalesPayment;
use App\Models\Customer;
use Illuminate\Http\Request;
use MongoDB\BSON\Decimal128;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesPaymentController extends Controller
{
    /**
     * Generate payment number with format: SIM/PI/25-26/000001
     */
    private function generatePaymentNumber()
    {
        $financialYear = $this->getFinancialYear();

        $lastPayment = SalesPayment::where('payment_number', 'regex', "/^SIM\/PI\/{$financialYear}\/\d+$/")
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            preg_match('/(\d+)$/', $lastPayment->payment_number, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "SIM/PI/{$financialYear}/{$newNumber}";
    }

    /**
     * Get current financial year (e.g., 25-26)
     */
    private function getFinancialYear()
    {
        $currentMonth = (int)date('m');
        $currentYear = (int)date('y');

        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

    public function index(Request $request)
    {
        $query = SalesPayment::with(['party'])
            ->where('payment_type', 'payment_in')
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('party_id'))       $query->where('party_id',       $request->party_id);
        if ($request->filled('from_date'))      $query->whereDate('payment_date', '>=', $request->from_date);
        if ($request->filled('to_date'))        $query->whereDate('payment_date', '<=', $request->to_date);
        if ($request->filled('payment_method')) $query->where('payment_method',  $request->payment_method);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('payment_number', 'like', "%{$s}%")
                ->orWhere('reference_no',  'like', "%{$s}%");
            });
        }

        // Warehouse filter — match invoices allocated to this warehouse
        $warehouseId = null;
        if ($request->filled('warehouse_id')) {
            $warehouseId = $request->warehouse_id;
        }

        $payments = $query->paginate(50)->withQueryString();

        // Filter by warehouse in PHP (allocations mein invoice_id se match)
        if ($warehouseId) {
            $invoiceIdsInWarehouse = \App\Models\SalesInvoice::where('warehouse_id', $warehouseId)
                ->pluck('_id')
                ->map(fn($id) => (string)$id)
                ->toArray();

            $payments->setCollection(
                $payments->getCollection()->filter(function($payment) use ($invoiceIdsInWarehouse) {
                    $allocs = $payment->allocations ?? [];
                    foreach ($allocs as $alloc) {
                        if (($alloc['type'] ?? '') === 'invoice' &&
                            in_array($alloc['invoice_id'] ?? '', $invoiceIdsInWarehouse)) {
                            return true;
                        }
                    }
                    return false;
                })
            );
        }

        // Stats — same filters apply
        $statsQuery = SalesPayment::where('payment_type', 'payment_in');
        if ($request->filled('party_id'))       $statsQuery->where('party_id',       $request->party_id);
        if ($request->filled('from_date'))      $statsQuery->whereDate('payment_date', '>=', $request->from_date);
        if ($request->filled('to_date'))        $statsQuery->whereDate('payment_date', '<=', $request->to_date);
        if ($request->filled('payment_method')) $statsQuery->where('payment_method',  $request->payment_method);
        if ($request->filled('search')) {
            $s = $request->search;
            $statsQuery->where(function ($q) use ($s) {
                $q->where('payment_number', 'like', "%{$s}%")
                ->orWhere('reference_no',  'like', "%{$s}%");
            });
        }

        $allPayments   = $statsQuery->get();
        $totalCount    = $allPayments->count();
        $totalAmount   = $allPayments->sum(function($p) {
            return $p->amount instanceof \MongoDB\BSON\Decimal128
                ? (float) $p->amount->__toString()
                : (float) $p->amount;
        });

        $warehouses = \App\Models\Warehouse::active()->get();
        $parties    = \App\Models\Customer::orderBy('name')->get()->map(fn ($p) => [
            'id'              => (string) $p->_id,
            'name'            => $p->name,
            'phone'           => $p->phone,
            'party_type'      => $p->party_type,
            'party_type_text' => ucfirst($p->party_type),
        ]);

        return view('admin.payments.index', compact(
            'payments', 'parties', 'warehouses', 'totalCount', 'totalAmount'
        ));
    }

    public function create()
    {
        $paymentNumber = $this->generatePaymentNumber();
        return view('admin.payments.create', compact('paymentNumber'));
    }

    /**
     * Search parties — only those who have at least one invoice (any status)
     */
    public function searchParties(Request $request)
    {
        try {
            $search = $request->get('search', '');

            // MongoDB ODM: distinct() chained before pluck() is unreliable.
            // Fetch all party_ids then deduplicate in PHP.
            $partyIdsWithInvoices = SalesInvoice::where('status', '!=', 'draft')
                ->pluck('party_id')
                ->map(fn($id) => (string) $id)
                ->unique()
                ->values()
                ->toArray();

            if (empty($partyIdsWithInvoices)) {
                return response()->json(['success' => true, 'parties' => []]);
            }

            $parties = Customer::whereIn('_id', $partyIdsWithInvoices)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                          ->orWhere('phone', 'like', '%' . $search . '%')
                          ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orderBy('name')
                ->limit(20)
                ->get();

            $result = [];
            foreach ($parties as $party) {
                $dueDetails = $this->getPartyDueDetails($party);

                $result[] = [
                    'id'              => (string) $party->_id,
                    'name'            => $party->name,
                    'phone'           => $party->phone ?? '-',
                    'email'           => $party->email ?? '-',
                    'party_type'      => $party->party_type,
                    'party_type_text' => ucfirst($party->party_type ?? ''),
                    'opening_balance' => $dueDetails['opening_balance'],
                    'invoice_due'     => $dueDetails['invoice_due'],
                    'total_due'       => $dueDetails['total_due'],
                ];
            }

            return response()->json(['success' => true, 'parties' => $result]);

        } catch (\Exception $e) {
            Log::error('Party search error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search parties: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPartyDetails($partyId)
    {
        try {
            $party = Customer::findOrFail($partyId);
            $dueDetails = $this->getPartyDueDetails($party);

            return response()->json([
                'success' => true,
                'party' => [
                    'id'              => (string) $party->_id,
                    'name'            => $party->name,
                    'phone'           => $party->phone ?? '-',
                    'party_type'      => $party->party_type,
                    'party_type_text' => ucfirst($party->party_type ?? ''),
                    'opening_balance' => $dueDetails['opening_balance'],
                    'invoice_due'     => $dueDetails['invoice_due'],
                    'total_due'       => $dueDetails['total_due'],
                    'credit_balance'  => $dueDetails['credit_balance'],   // NEW
                    'net_payable'     => $dueDetails['net_payable'],       // NEW
                    'invoices'        => $dueDetails['invoices'],
                    'credit_notes'    => $dueDetails['credit_notes'],      // NEW
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get party details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load party details'
            ], 500);
        }
    }

    private function getPartyDueDetails($party)
    {
        $openingBalance = (float) ($party->opening_balance ?? 0);

        $partyIdStr = (string) $party->_id;
        $partyIdObj = new \MongoDB\BSON\ObjectId($partyIdStr);

        $invoices = SalesInvoice::where('status', '!=', 'draft')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where(function($q) use ($partyIdStr, $partyIdObj) {
                $q->where('party_id', $partyIdStr)
                ->orWhere('party_id', $partyIdObj);
            })
            ->orderBy('invoice_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($invoice) {
                $grandTotal = $this->decimalToFloat($invoice->grand_total);
                $totalPaid  = $this->decimalToFloat($invoice->total_paid);
                $balance    = max(0, round($grandTotal - $totalPaid, 2));
                $invoice->load('warehouse');
                return [
                    'id'             => (string) $invoice->_id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date'   => $invoice->invoice_date instanceof \Carbon\Carbon
                        ? $invoice->invoice_date->format('d-m-Y')
                        : date('d-m-Y', strtotime($invoice->invoice_date)),
                    'due_date'       => $invoice->due_date
                        ? (($invoice->due_date instanceof \Carbon\Carbon)
                            ? $invoice->due_date->format('d-m-Y')
                            : date('d-m-Y', strtotime($invoice->due_date)))
                        : '-',
                    'warehouse_name' => $invoice->warehouse->name ?? '—',
                    'grand_total'    => $grandTotal,
                    'paid'           => $totalPaid,
                    'balance'        => $balance,
                    'payment_status' => $invoice->payment_status,
                ];
            })
            ->filter(fn($inv) => $inv['balance'] > 0)
            ->values();

        $invoiceDue = round($invoices->sum('balance'), 2);

        // ── NEW: fetch active credit notes for this party ──────────────
        $creditNotes = \App\Models\CreditNote::where('party_id', $partyIdStr)
            ->where('status', 'active')
            ->where('remaining_amount', '>', 0)
            ->orderBy('credit_date', 'asc')
            ->get()
            ->map(function($cn) {
                return [
                    'id'                 => (string) $cn->_id,
                    'credit_note_number' => $cn->credit_note_number,
                    'credit_date'        => $cn->credit_date instanceof \Carbon\Carbon
                        ? $cn->credit_date->format('d-m-Y')
                        : date('d-m-Y', strtotime($cn->credit_date)),
                    'amount'             => $this->decimalToFloat($cn->amount),
                    'used_amount'        => $this->decimalToFloat($cn->used_amount),
                    'remaining_amount'   => $this->decimalToFloat($cn->remaining_amount),
                    'reason'             => $cn->reason ?? '',
                ];
            });

        $totalCreditBalance = round($creditNotes->sum('remaining_amount'), 2);

        // Net payable = opening + invoice dues − credit notes
        $netPayable = round($openingBalance + $invoiceDue - $totalCreditBalance, 2);
        $netPayable = max(0, $netPayable);
        // ── END NEW ────────────────────────────────────────────────────

        return [
            'opening_balance'     => $openingBalance,
            'invoice_due'         => $invoiceDue,
            'total_due'           => round($openingBalance + $invoiceDue, 2), // raw total (for display)
            'credit_balance'      => $totalCreditBalance,                     // NEW
            'net_payable'         => $netPayable,                             // NEW
            'invoices'            => $invoices,
            'credit_notes'        => $creditNotes,                            // NEW
        ];
    }
    public function store(Request $request)
    {
        $request->validate([
            'party_id'       => 'required',
            'amount'         => 'required|numeric|min:0',   // 0 allowed if fully covered by credit
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,upi,bank_transfer,cheque,card'
        ]);

        try {
            $party = Customer::findOrFail($request->party_id);
            $cashAmount = (float) $request->amount;

            $dueDetails = $this->getPartyDueDetails($party);

            // Validate: cash cannot exceed net payable
            if ($cashAmount > ($dueDetails['net_payable'] + 0.01)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount (₹ ' . number_format($cashAmount, 2) .
                                ') cannot exceed net payable (₹ ' . number_format($dueDetails['net_payable'], 2) . ')'
                ], 422);
            }

            $paymentNumber = $this->generatePaymentNumber();
            $allocations   = [];

            // ── PASS 1: Credit note allocation (applied first, oldest first) ──
            $creditAllocations = [];
            $creditUsedTotal   = 0;

            foreach ($dueDetails['credit_notes'] as $cnData) {
                if ($cnData['remaining_amount'] <= 0) continue;

                $creditModel = \App\Models\CreditNote::find($cnData['id']);
                if (!$creditModel) continue;

                $toUse = $cnData['remaining_amount']; // use full remaining credit

                $creditModel->used_amount      = $this->decimalToFloat($creditModel->used_amount) + $toUse;
                $creditModel->remaining_amount = max(0, $this->decimalToFloat($creditModel->remaining_amount) - $toUse);
                if ($creditModel->remaining_amount <= 0.01) {
                    $creditModel->remaining_amount = 0;
                    $creditModel->status           = 'used';
                }
                $creditModel->save();

                $creditAllocations[] = [
                    'type'                => 'credit_note',
                    'credit_note_id'      => $cnData['id'],
                    'credit_note_number'  => $cnData['credit_note_number'],
                    'amount'              => $toUse,
                    'description'         => 'Credit Note Adjustment: ' . $cnData['credit_note_number'],
                ];

                $creditUsedTotal += $toUse;
            }

            // Merge credit allocations at the start
            $allocations = array_merge($allocations, $creditAllocations);

            // ── Build a "virtual payment pool" = credit + cash ────────────────
            // This pool is used to settle opening balance then invoices.
            $pool = $creditUsedTotal + $cashAmount;
            $remainingPool = $pool;

            // ── PASS 2: Opening balance ───────────────────────────────────────
            if ($dueDetails['opening_balance'] > 0 && $remainingPool > 0) {
                $payToOpening = min($dueDetails['opening_balance'], $remainingPool);

                if ($payToOpening > 0) {
                    $party->opening_balance = max(0, $dueDetails['opening_balance'] - $payToOpening);
                    $party->save();

                    $allocations[] = [
                        'type'        => 'opening_balance',
                        'amount'      => $payToOpening,
                        'description' => 'Opening Balance Payment',
                    ];

                    $remainingPool -= $payToOpening;
                }
            }

            // ── PASS 3: Invoices (oldest first) ───────────────────────────────
            foreach ($dueDetails['invoices'] as $invoiceData) {
                if ($remainingPool <= 0.001) break;

                $invoice = SalesInvoice::find($invoiceData['id']);
                if (!$invoice || $invoiceData['balance'] <= 0) continue;

                $payToInvoice = min($invoiceData['balance'], $remainingPool);

                if ($payToInvoice > 0.001) {
                    $newPaid    = $invoiceData['paid'] + $payToInvoice;
                    $newBalance = $invoiceData['balance'] - $payToInvoice;

                    $paymentStatus = ($newBalance <= 0.01) ? 'paid' : 'partial';
                    if ($paymentStatus === 'paid') $newBalance = 0;

                    $invoice->update([
                        'total_paid'     => round($newPaid, 2),
                        'balance_amount' => round($newBalance, 2),
                        'payment_status' => $paymentStatus,
                    ]);

                    $allocations[] = [
                        'type'             => 'invoice',
                        'invoice_id'       => (string) $invoice->_id,
                        'invoice_number'   => $invoice->invoice_number,
                        'amount'           => round($payToInvoice, 2),
                        'previous_balance' => round($invoiceData['balance'], 2),
                        'new_balance'      => round($newBalance, 2),
                    ];

                    $remainingPool -= $payToInvoice;
                }
            }

            // ── PASS 4: Save the cash payment record ─────────────────────────
            // Only create a payment record if the customer actually paid cash.
            if ($cashAmount > 0.001) {
                SalesPayment::create([
                    'payment_number' => $paymentNumber,
                    'party_id'       => $request->party_id,
                    'amount'         => round($cashAmount, 2),
                    'payment_method' => $request->payment_method,
                    'payment_date'   => $request->payment_date,
                    'status'         => 'completed',
                    'reference_no'   => $request->reference_no,
                    'notes'          => $request->notes,
                    'payment_type'   => 'payment_in',
                    'allocations'    => $allocations,
                ]);
            }

            return response()->json([
                'success'        => true,
                'payment_number' => $paymentNumber,
                'credit_used'    => round($creditUsedTotal, 2),
                'cash_paid'      => round($cashAmount, 2),
                'message'        => 'Payment recorded successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Payment store error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }
     public function show($id)
    {
        try {
            $payment = SalesPayment::with('party')->findOrFail($id);

            // Only show payment_in records via this endpoint
            if ($payment->payment_type !== 'payment_in') {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            $amount      = $this->decimalToFloat($payment->amount);
            $allocations = $payment->allocations ?? [];

            // Enrich each allocation with invoice details if available
            $enrichedAllocations = array_map(function ($alloc) {
                if (($alloc['type'] ?? '') === 'invoice' && !empty($alloc['invoice_id'])) {
                    $inv = SalesInvoice::find($alloc['invoice_id']);
                    if ($inv) {
                        $alloc['invoice_date']   = $inv->invoice_date instanceof \Carbon\Carbon
                            ? $inv->invoice_date->format('d-m-Y')
                            : date('d-m-Y', strtotime($inv->invoice_date));
                        $alloc['grand_total']    = $this->decimalToFloat($inv->grand_total);
                        $alloc['payment_status'] = $inv->payment_status;
                    }
                }
                return $alloc;
            }, $allocations);

            return response()->json([
                'success' => true,
                'payment' => [
                    'payment_number' => $payment->payment_number ?? '—',
                    'date'           => $payment->payment_date instanceof \Carbon\Carbon
                        ? $payment->payment_date->format('d M Y')
                        : date('d M Y', strtotime($payment->payment_date)),
                    'party_name'     => $payment->party->name ?? 'N/A',
                    'party_type'     => ucfirst($payment->party->party_type ?? ''),
                    'party_phone'    => $payment->party->phone ?? '—',
                    'amount'         => $amount,
                    'payment_method' => $payment->payment_method_text,
                    'reference_no'   => $payment->reference_no ?: '—',
                    'notes'          => $payment->notes ?: '—',
                    'allocations'    => $enrichedAllocations,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('show payment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }
    }

    /**
     * Delete a payment and reverse all allocations
     */
    public function destroy($id)
    {
        try {
            $payment = SalesPayment::findOrFail($id);

            // SAHI — credit_note case add karo
            foreach ($payment->allocations as $allocation) {
                if ($allocation['type'] === 'opening_balance') {
                    $party = Customer::find($payment->party_id);
                    if ($party) {
                        $party->opening_balance = ($party->opening_balance ?? 0) + $allocation['amount'];
                        $party->save();
                    }
                } elseif ($allocation['type'] === 'invoice') {
                    $invoice = SalesInvoice::find($allocation['invoice_id']);
                    if ($invoice) {
                        $currentPaid = $this->decimalToFloat($invoice->total_paid);
                        $newPaid     = max(0, $currentPaid - $allocation['amount']);
                        $grandTotal  = $this->decimalToFloat($invoice->grand_total);
                        $newBalance  = $grandTotal - $newPaid;

                        $paymentStatus = 'unpaid';
                        if ($newPaid > 0 && $newBalance > 0.01) $paymentStatus = 'partial';
                        elseif ($newBalance <= 0.01) { $paymentStatus = 'paid'; $newBalance = 0; }

                        $invoice->update([
                            'total_paid'     => round($newPaid, 2),
                            'balance_amount' => round($newBalance, 2),
                            'payment_status' => $paymentStatus
                        ]);
                    }
                } elseif ($allocation['type'] === 'credit_note') {  // ← NEW
                    $creditNote = \App\Models\CreditNote::find($allocation['credit_note_id']);
                    if ($creditNote) {
                        $creditNote->used_amount      = max(0, $this->decimalToFloat($creditNote->used_amount) - $allocation['amount']);
                        $creditNote->remaining_amount = $this->decimalToFloat($creditNote->amount) - $this->decimalToFloat($creditNote->used_amount);
                        $creditNote->status           = $creditNote->remaining_amount > 0 ? 'active' : 'used';
                        $creditNote->save();
                    }
                }
            }

            $payment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Payment delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to convert Decimal128 to float
     */
    private function decimalToFloat($value)
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
