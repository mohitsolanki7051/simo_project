<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use App\Models\SalesPayment;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\DebitNote;
use Illuminate\Http\Request;
use MongoDB\BSON\Decimal128;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesPaymentController extends Controller
{
    /**
     * Generate payment number with format: SIM/SPI/25-26/000001
     */
    private function generatePaymentNumber()
    {
        $financialYear = $this->getFinancialYear();

        $lastPayment = SalesPayment::where('payment_number', 'regex', "/^SIM\/SPI\/{$financialYear}\/\d+$/")
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            preg_match('/(\d+)$/', $lastPayment->payment_number, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "SIM/SPI/{$financialYear}/{$newNumber}";
    }

    /**
     * Get current financial year (e.g., 25-26)
     */
    private function getFinancialYear()
    {
        $currentMonth = (int)date('m');
        $currentYear  = (int)date('y');

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

        $warehouseId = null;
        if ($request->filled('warehouse_id')) {
            $warehouseId = $request->warehouse_id;
        }

        $payments = $query->paginate(50)->withQueryString();

        if ($warehouseId) {
            $invoiceIdsInWarehouse = \App\Models\SalesInvoice::where('warehouse_id', $warehouseId)
                ->pluck('_id')
                ->map(fn($id) => (string)$id)
                ->toArray();

            $payments->setCollection(
                $payments->getCollection()->filter(function ($payment) use ($invoiceIdsInWarehouse) {
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

        $allPayments = $statsQuery->get();
        $totalCount  = $allPayments->count();
        $totalAmount = $allPayments->sum(function ($p) {
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

    // ─────────────────────────────────────────────────────────────────────────
    // PARTY SEARCH
    // Two modes:
    //   mode=sales   → existing logic (customers with invoices)
    //   mode=refund  → vendors + dealers/distributors with active debit notes
    // ─────────────────────────────────────────────────────────────────────────
    public function searchParties(Request $request)
    {
        $mode   = $request->get('mode', 'sales');
        $search = $request->get('search', '');

        try {
            if ($mode === 'refund') {
                return $this->searchRefundParties($search);
            }
            return $this->searchSalesParties($search);
        } catch (\Exception $e) {
            Log::error('Party search error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search parties: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Original: customers who have at least one invoice
     */
    private function searchSalesParties(string $search)
    {
        $partyIdsWithInvoices = SalesInvoice::where('status', '!=', 'draft')
            ->pluck('party_id')
            ->map(fn($id) => (string) $id)
            ->unique()
            ->values()
            ->toArray();

        // Opening balance wale customers bhi include karo
        $partyIdsWithOpeningBalance = \App\Models\Customer::whereNotNull('opening_balance')
    ->where('opening_balance', '!=', 0)
    ->pluck('_id')
    ->map(fn($id) => (string) $id)
    ->toArray();

        $allPartyIds = array_unique(array_merge($partyIdsWithInvoices, $partyIdsWithOpeningBalance));

        if (empty($allPartyIds)) {
            return response()->json(['success' => true, 'parties' => []]);
        }

        $parties = Customer::whereIn('_id', $allPartyIds)
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
    }

    /**
     * NEW: vendors + dealers/distributors who have active debit notes
     */
    private function searchRefundParties(string $search)
    {
        // Get all party_ids that have active debit notes with remaining amount > 0
        $debitPartyIds = DebitNote::where('status', 'active')
            ->where('remaining_amount', '>', 0)
            ->pluck('party_id')
            ->map(fn($id) => (string) $id)
            ->unique()
            ->values()
            ->toArray();

        if (empty($debitPartyIds)) {
            return response()->json(['success' => true, 'parties' => []]);
        }

        $result = [];

        // Search Vendors
        $vendors = Vendor::whereIn('_id', $debitPartyIds)
            ->where(function ($q) use ($search) {
                $q->where('company_name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy('company_name')
            ->limit(10)
            ->get();

        foreach ($vendors as $vendor) {
            $debitDetails = $this->getDebitNoteDetails((string) $vendor->_id);
            $result[] = [
                'id'              => (string) $vendor->_id,
                'name'            => $vendor->company_name,
                'phone'           => $vendor->phone ?? '-',
                'email'           => $vendor->email ?? '-',
                'party_type'      => 'vendor',
                'party_type_text' => 'Vendor',
                'debit_balance'   => $debitDetails['total_remaining'],
            ];
        }

        // Search Dealers / Distributors
        $customers = Customer::whereIn('_id', $debitPartyIds)
            ->whereIn('party_type', ['dealer', 'distributor'])
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        foreach ($customers as $customer) {
            $debitDetails = $this->getDebitNoteDetails((string) $customer->_id);
            $result[] = [
                'id'              => (string) $customer->_id,
                'name'            => $customer->name,
                'phone'           => $customer->phone ?? '-',
                'email'           => $customer->email ?? '-',
                'party_type'      => $customer->party_type,
                'party_type_text' => ucfirst($customer->party_type),
                'debit_balance'   => $debitDetails['total_remaining'],
            ];
        }

        // Sort by name
        usort($result, fn($a, $b) => strcmp($a['name'], $b['name']));

        return response()->json(['success' => true, 'parties' => $result]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARTY DETAILS
    // ─────────────────────────────────────────────────────────────────────────
    public function getPartyDetails($partyId)
    {
        try {
            $party      = Customer::findOrFail($partyId);
            $dueDetails = $this->getPartyDueDetails($party);

            return response()->json([
                'success' => true,
                'party'   => [
                    'id'              => (string) $party->_id,
                    'name'            => $party->name,
                    'phone'           => $party->phone ?? '-',
                    'party_type'      => $party->party_type,
                    'party_type_text' => ucfirst($party->party_type ?? ''),
                    'opening_balance' => $dueDetails['opening_balance'],
                    'invoice_due'     => $dueDetails['invoice_due'],
                    'total_due'       => $dueDetails['total_due'],
                    'credit_balance'  => $dueDetails['credit_balance'],
                    'net_payable'     => $dueDetails['net_payable'],
                    'invoices'        => $dueDetails['invoices'],
                    'credit_notes'    => $dueDetails['credit_notes'],
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

    /**
     * NEW: Get refund party details (vendor or dealer/distributor)
     * Returns active debit notes for that party
     */
    public function getRefundPartyDetails($partyId)
    {
        try {
            // Try vendor first, then customer
            $partyModel = Vendor::find($partyId);
            $partyType  = 'vendor';
            $partyName  = $partyModel?->company_name;

            if (!$partyModel) {
                $partyModel = Customer::find($partyId);
                $partyType  = $partyModel?->party_type ?? 'dealer';
                $partyName  = $partyModel?->name;
            }

            if (!$partyModel) {
                return response()->json(['success' => false, 'message' => 'Party not found'], 404);
            }

            $debitDetails = $this->getDebitNoteDetails($partyId);

            return response()->json([
                'success' => true,
                'party'   => [
                    'id'              => (string) $partyModel->_id,
                    'name'            => $partyName,
                    'phone'           => $partyModel->phone ?? '-',
                    'party_type'      => $partyType,
                    'party_type_text' => ucfirst($partyType),
                    'debit_balance'   => $debitDetails['total_remaining'],
                    'debit_notes'     => $debitDetails['debit_notes'],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get refund party details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load party details'
            ], 500);
        }
    }

    /**
     * Fetch active debit notes for a party
     */
    private function getDebitNoteDetails(string $partyId): array
    {
        $debitNotes = DebitNote::where('party_id', $partyId)
            ->whereIn('status', ['active', 'partial'])
            ->where('remaining_amount', '>', 0)
            ->orderBy('debit_date', 'asc')
            ->get()
            ->map(function ($dn) {
                return [
                    'id'                 => (string) $dn->_id,
                    'debit_note_number'  => $dn->debit_note_number,
                    'debit_date'         => $dn->debit_date instanceof \Carbon\Carbon
                        ? $dn->debit_date->format('d-m-Y')
                        : date('d-m-Y', strtotime($dn->debit_date)),
                    'amount'             => $this->decimalToFloat($dn->amount),
                    'used_amount'        => $this->decimalToFloat($dn->used_amount),
                    'remaining_amount'   => $this->decimalToFloat($dn->remaining_amount),
                    'reason'             => $dn->reason ?? '',
                ];
            });

        return [
            'total_remaining' => round($debitNotes->sum('remaining_amount'), 2),
            'debit_notes'     => $debitNotes,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE
    // Two sub-types:
    //   payment_subtype = sales_payment  → existing flow
    //   payment_subtype = debit_refund   → NEW: debit note cash refund
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        if ($request->input('payment_subtype') === 'debit_refund') {
            return $this->storeDebitRefund($request);
        }
        return $this->storeSalesPayment($request);
    }

    /**
     * Original sales payment logic (unchanged)
     */
    private function storeSalesPayment(Request $request)
    {
        $request->validate([
            'party_id'       => 'required',
            'amount'         => 'required|numeric|min:0',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,upi,bank_transfer,cheque,card'
        ]);

        try {
            $party      = Customer::findOrFail($request->party_id);
            $cashAmount = (float) $request->amount;
            $dueDetails = $this->getPartyDueDetails($party);

            if ($cashAmount > ($dueDetails['net_payable'] + 0.01)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount (₹ ' . number_format($cashAmount, 2) .
                                 ') cannot exceed net payable (₹ ' . number_format($dueDetails['net_payable'], 2) . ')'
                ], 422);
            }

            $paymentNumber = $this->generatePaymentNumber();
            $allocations   = [];

            // PASS 1: Credit note allocation
            $creditAllocations = [];
            $creditUsedTotal   = 0;

            foreach ($dueDetails['credit_notes'] as $cnData) {
                if ($cnData['remaining_amount'] <= 0) continue;

                $creditModel = \App\Models\CreditNote::find($cnData['id']);
                if (!$creditModel) continue;

                $toUse = $cnData['remaining_amount'];

                $creditModel->used_amount      = $this->decimalToFloat($creditModel->used_amount) + $toUse;
                $creditModel->remaining_amount = max(0, $this->decimalToFloat($creditModel->remaining_amount) - $toUse);
                if ($creditModel->remaining_amount <= 0.01) {
                    $creditModel->remaining_amount = 0;
                    $creditModel->status           = 'used';
                }
                $creditModel->save();

                $creditAllocations[] = [
                    'type'               => 'credit_note',
                    'credit_note_id'     => $cnData['id'],
                    'credit_note_number' => $cnData['credit_note_number'],
                    'amount'             => $toUse,
                    'description'        => 'Credit Note Adjustment: ' . $cnData['credit_note_number'],
                ];

                $creditUsedTotal += $toUse;
            }

            $allocations   = array_merge($allocations, $creditAllocations);
            $pool          = $creditUsedTotal + $cashAmount;
            $remainingPool = $pool;

            // PASS 2: Opening balance
            if ($dueDetails['opening_balance'] > 0 && $remainingPool > 0) {
                $payToOpening = min($dueDetails['opening_balance'], $remainingPool);
                if ($payToOpening > 0) {
                    $allocations[] = [
                        'type'        => 'opening_balance',
                        'amount'      => $payToOpening,
                        'description' => 'Opening Balance Payment',
                    ];
                    $remainingPool -= $payToOpening;
                }
            }

            // PASS 3: Invoices
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

            // PASS 4: Save cash payment record
            if ($cashAmount > 0.001) {
                SalesPayment::create([
                    'payment_number'   => $paymentNumber,
                    'party_id'         => $request->party_id,
                    'amount'           => round($cashAmount, 2),
                    'payment_method'   => $request->payment_method,
                    'payment_date'     => $request->payment_date,
                    'status'           => 'completed',
                    'reference_no'     => $request->reference_no,
                    'notes'            => $request->notes,
                    'payment_type'     => 'payment_in',
                    'payment_subtype'  => 'sales_payment',
                    'allocations'      => $allocations,
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

    /**
     * NEW: Store debit note refund
     * Vendor/dealer/distributor pays back cash against their debit notes
     */
    private function storeDebitRefund(Request $request)
    {
        $request->validate([
            'party_id'       => 'required',
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,upi,bank_transfer,cheque,card'
        ]);

        try {
            $partyId    = $request->party_id;
            $cashAmount = (float) $request->amount;

            $debitDetails = $this->getDebitNoteDetails($partyId);

            if ($cashAmount > ($debitDetails['total_remaining'] + 0.01)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund amount (₹' . number_format($cashAmount, 2) .
                                 ') cannot exceed debit note balance (₹' . number_format($debitDetails['total_remaining'], 2) . ')'
                ], 422);
            }

            $paymentNumber = $this->generatePaymentNumber();
            $allocations   = [];
            $remaining     = $cashAmount;

            // Allocate against debit notes (oldest first)
            foreach ($debitDetails['debit_notes'] as $dnData) {
                if ($remaining <= 0.001) break;
                if ($dnData['remaining_amount'] <= 0) continue;

                $debitModel = DebitNote::find($dnData['id']);
                if (!$debitModel) continue;

                $toUse = min($dnData['remaining_amount'], $remaining);

                $debitModel->used_amount      = $this->decimalToFloat($debitModel->used_amount) + $toUse;
                $debitModel->remaining_amount = max(0, $this->decimalToFloat($debitModel->remaining_amount) - $toUse);
                if ($debitModel->remaining_amount <= 0.01) {
                    $debitModel->remaining_amount = 0;
                    $debitModel->status           = 'settled';
                } else {
                    $debitModel->status = 'partial';
                }
                $debitModel->save();

                $allocations[] = [
                    'type'               => 'debit_note',
                    'debit_note_id'      => $dnData['id'],
                    'debit_note_number'  => $dnData['debit_note_number'],
                    'amount'             => round($toUse, 2),
                    'previous_remaining' => round($dnData['remaining_amount'], 2),
                    'new_remaining'      => round($debitModel->remaining_amount, 2),
                    'description'        => 'Debit Note Refund: ' . $dnData['debit_note_number'],
                ];

                $remaining -= $toUse;
            }

            // Save payment record
            SalesPayment::create([
                'payment_number'   => $paymentNumber,
                'party_id'         => $partyId,
                'amount'           => round($cashAmount, 2),
                'payment_method'   => $request->payment_method,
                'payment_date'     => $request->payment_date,
                'status'           => 'completed',
                'reference_no'     => $request->reference_no,
                'notes'            => $request->notes,
                'payment_type'     => 'payment_in',
                'payment_subtype'  => 'debit_refund',
                'allocations'      => $allocations,
            ]);

            return response()->json([
                'success'        => true,
                'payment_number' => $paymentNumber,
                'message'        => 'Refund recorded successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Debit refund store error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to record refund: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $payment = SalesPayment::with('party')->findOrFail($id);

            if ($payment->payment_type !== 'payment_in') {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            $amount      = $this->decimalToFloat($payment->amount);
            $allocations = $payment->allocations ?? [];

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
                if (($alloc['type'] ?? '') === 'debit_note' && !empty($alloc['debit_note_id'])) {
                    $dn = DebitNote::find($alloc['debit_note_id']);
                    if ($dn) {
                        $alloc['debit_date'] = $dn->debit_date instanceof \Carbon\Carbon
                            ? $dn->debit_date->format('d-m-Y')
                            : date('d-m-Y', strtotime($dn->debit_date));
                        $alloc['total_amount'] = $this->decimalToFloat($dn->amount);
                    }
                }
                return $alloc;
            }, $allocations);

            // Resolve party name for vendors too
           $party = $payment->party_details;

$partyName  = $party['name'] ?? '—';
$partyType  = $party['party_type'] ?? '—';
$partyPhone = $party['phone'] ?? '—';

            return response()->json([
                'success' => true,
                'payment' => [
                    'payment_number'   => $payment->payment_number ?? '—',
                    'payment_subtype'  => $payment->payment_subtype ?? 'sales_payment',
                    'date'             => $payment->payment_date instanceof \Carbon\Carbon
                        ? $payment->payment_date->format('d M Y')
                        : date('d M Y', strtotime($payment->payment_date)),
                    'party_name'       => $partyName,
                    'party_type'       => ucfirst($partyType),
                    'party_phone'      => $partyPhone,
                    'amount'           => $amount,
                    'payment_method'   => $payment->payment_method_text,
                    'reference_no'     => $payment->reference_no ?: '—',
                    'notes'            => $payment->notes ?: '—',
                    'allocations'      => $enrichedAllocations,
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

            foreach ($payment->allocations as $allocation) {
                if ($allocation['type'] === 'opening_balance') {
                    // Nothing to reverse in DB for opening balance

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

                } elseif ($allocation['type'] === 'credit_note') {
                    $creditNote = \App\Models\CreditNote::find($allocation['credit_note_id']);
                    if ($creditNote) {
                        $creditNote->used_amount      = max(0, $this->decimalToFloat($creditNote->used_amount) - $allocation['amount']);
                        $creditNote->remaining_amount = $this->decimalToFloat($creditNote->amount) - $this->decimalToFloat($creditNote->used_amount);
                        $creditNote->status           = $creditNote->remaining_amount > 0 ? 'active' : 'used';
                        $creditNote->save();
                    }

                } elseif ($allocation['type'] === 'debit_note') {
                    // NEW: Reverse debit note allocation
                    $debitNote = DebitNote::find($allocation['debit_note_id']);
                    if ($debitNote) {
                        $debitNote->used_amount      = max(0, $this->decimalToFloat($debitNote->used_amount) - $allocation['amount']);
                        $debitNote->remaining_amount = $this->decimalToFloat($debitNote->amount) - $this->decimalToFloat($debitNote->used_amount);
                       if ($debitNote->remaining_amount <= 0) {
                            $debitNote->status = 'settled';
                        } else {
                            $debitNote->status = 'partial';
                        }
                        $debitNote->save();
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

    // ─────────────────────────────────────────────────────────────────────────
    // EXISTING HELPERS (unchanged)
    // ─────────────────────────────────────────────────────────────────────────
    private function getPartyDueDetails($party)
    {
        $originalOpening = (float) ($party->opening_balance ?? 0);
        $partyIdStr      = (string) $party->_id;
        $partyIdObj      = new \MongoDB\BSON\ObjectId($partyIdStr);

        $openingPaid = SalesPayment::where('party_id', $partyIdStr)
            ->where('payment_type', 'payment_in')
            ->get()
            ->sum(function ($p) {
                $allocs = $p->allocations ?? [];
                return collect($allocs)
                    ->where('type', 'opening_balance')
                    ->sum('amount');
            });

        $openingBalance = max(0, $originalOpening - $openingPaid);

        $invoices = SalesInvoice::where('status', '!=', 'draft')
            ->where('status', '!=', 'cancelled')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where(function ($q) use ($partyIdStr, $partyIdObj) {
                $q->where('party_id', $partyIdStr)
                  ->orWhere('party_id', $partyIdObj);
            })
            ->orderBy('invoice_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($invoice) {
                $grandTotal = $this->decimalToFloat($invoice->grand_total);
                $totalPaid  = $this->decimalToFloat($invoice->total_paid);
                $balance    = max(0, $this->decimalToFloat($invoice->balance_amount));
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

        $creditNotes = \App\Models\CreditNote::where('party_id', $partyIdStr)
            ->where('status', 'active')
            ->where('remaining_amount', '>', 0)
            ->orderBy('credit_date', 'asc')
            ->get()
            ->map(function ($cn) {
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
        $netPayable         = max(0, round($openingBalance + $invoiceDue - $totalCreditBalance, 2));

        return [
            'opening_balance' => $openingBalance,
            'invoice_due'     => $invoiceDue,
            'total_due'       => round($openingBalance + $invoiceDue, 2),
            'credit_balance'  => $totalCreditBalance,
            'net_payable'     => $netPayable,
            'invoices'        => $invoices,
            'credit_notes'    => $creditNotes,
        ];
    }

    private function decimalToFloat($value)
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
