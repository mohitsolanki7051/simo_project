<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\PurchasePayment;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\DebitNote;
use App\Models\CreditNote;
use Illuminate\Http\Request;
use MongoDB\BSON\Decimal128;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesPaymentOutController extends Controller
{
    /**
     * Generate payment number with format: SIM/SPO/25-26/000001
     * SPO = Sales Payment Out
     */
    private function generatePaymentNumber()
    {
        $financialYear = $this->getFinancialYear();

        $lastPayment = PurchasePayment::where('payment_number', 'regex', "/^SIM\/SPO\/{$financialYear}\/\d+$/")
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            preg_match('/(\d+)$/', $lastPayment->payment_number, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "SIM/SPO/{$financialYear}/{$newNumber}";
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
        $query = PurchasePayment::with(['invoice'])
            ->where('payment_type', 'payment_out')
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('party_id'))       $query->where('party_id', $request->party_id);
        if ($request->filled('from_date'))      $query->whereDate('payment_date', '>=', $request->from_date);
        if ($request->filled('to_date'))        $query->whereDate('payment_date', '<=', $request->to_date);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('payment_number', 'like', "%{$s}%")
                  ->orWhere('reference_no', 'like', "%{$s}%");
            });
        }

        $warehouseId = null;
        if ($request->filled('warehouse_id')) {
            $warehouseId = $request->warehouse_id;
        }

        $payments = $query->paginate(50)->withQueryString();

        if ($warehouseId) {
            $invoiceIdsInWarehouse = PurchaseInvoice::where('warehouse_id', $warehouseId)
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

        $statsQuery = PurchasePayment::where('payment_type', 'payment_out');
        if ($request->filled('party_id'))       $statsQuery->where('party_id', $request->party_id);
        if ($request->filled('from_date'))      $statsQuery->whereDate('payment_date', '>=', $request->from_date);
        if ($request->filled('to_date'))        $statsQuery->whereDate('payment_date', '<=', $request->to_date);
        if ($request->filled('payment_method')) $statsQuery->where('payment_method', $request->payment_method);
        if ($request->filled('search')) {
            $s = $request->search;
            $statsQuery->where(function ($q) use ($s) {
                $q->where('payment_number', 'like', "%{$s}%")
                  ->orWhere('reference_no', 'like', "%{$s}%");
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

        // Get all parties that can receive payments (vendors, dealers, distributors)
        $parties = collect();

        // Add vendors
        $vendors = Vendor::orderBy('company_name')->get()->map(fn($v) => [
            'id'              => (string) $v->id,
            'name'            => $v->company_name,
            'phone'           => $v->phone,
            'party_type'      => 'vendor',
            'party_type_text' => 'Vendor',
        ]);
        $parties = $parties->merge($vendors);

        // Add dealers and distributors
        $customers = Customer::whereIn('party_type', ['dealer', 'distributor'])
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id'              => (string) $c->_id,
                'name'            => $c->name,
                'phone'           => $c->phone,
                'party_type'      => $c->party_type,
                'party_type_text' => ucfirst($c->party_type),
            ]);
        $parties = $parties->merge($customers);

        return view('admin.payments-out.index', compact(
            'payments', 'parties', 'warehouses', 'totalCount', 'totalAmount'
        ));
    }

    public function create()
    {
        $paymentNumber = $this->generatePaymentNumber();
        return view('admin.payments-out.create', compact('paymentNumber'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARTY SEARCH
    // Two modes:
    //   mode=purchase → vendors, dealers, distributors with outstanding purchase invoices (we owe them)
    //   mode=credit_refund → customers (dealer/distributor/customer) with active credit notes (we owe them refund)
    // ─────────────────────────────────────────────────────────────────────────
    public function searchParties(Request $request)
    {
        $mode   = $request->get('mode', 'purchase');
        $search = $request->get('search', '');

        try {
            if ($mode === 'credit_refund') {
                return $this->searchCreditRefundParties($search);
            }
            return $this->searchPurchaseParties($search);
        } catch (\Exception $e) {
            Log::error('Party search error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search parties: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Purchase mode: parties who have outstanding purchase invoices (we owe them money)
     * These can be vendors, dealers, or distributors
     */
    private function searchPurchaseParties(string $search)
    {
        // Get party IDs with outstanding purchase invoices (unpaid/partial)
        $partyIdsWithInvoices = PurchaseInvoice::where('status', '!=', 'draft')
            ->pluck('party_id')
            ->map(fn($id) => (string) $id)
            ->unique()
            ->values()
            ->toArray();

        // Get vendor IDs with opening balance > 0
        $vendorIdsWithOpening = Vendor::whereNotNull('opening_balance')
            ->whereNotIn('opening_balance', ['', '0', '0.00', 0, 0.0])
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        // Get customer IDs (dealers/distributors) with opening balance > 0
        $customerIdsWithOpening = Customer::whereIn('party_type', ['dealer', 'distributor'])
            ->whereNotNull('opening_balance')
            ->whereNotIn('opening_balance', ['', '0', '0.00', 0, 0.0])
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        $allowedPartyIds = array_values(array_unique(array_merge(
            $partyIdsWithInvoices,
            $vendorIdsWithOpening,
            $customerIdsWithOpening
        )));

        if (empty($allowedPartyIds)) {
            return response()->json(['success' => true, 'parties' => []]);
        }

        $parties = collect();

        // Search vendors
        $vendors = Vendor::whereIn('id', $allowedPartyIds)
            ->where(function ($query) use ($search) {
                $query->where('company_name', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy('company_name')
            ->limit(20)
            ->get();

        foreach ($vendors as $vendor) {
            $dueDetails = $this->getPartyDueDetailsForPurchase($vendor, 'vendor');
            $parties->push([
                'id'              => (string) $vendor->id,
                'name'            => $vendor->company_name,
                'phone'           => $vendor->phone ?? '-',
                'email'           => $vendor->email ?? '-',
                'party_type'      => 'vendor',
                'party_type_text' => 'Vendor',
                'opening_balance' => $dueDetails['opening_balance'],
                'invoice_due'     => $dueDetails['invoice_due'],
                'total_due'       => $dueDetails['total_due'],
            ]);
        }

        // Search dealers and distributors
        $customers = Customer::whereIn('_id', $allowedPartyIds)
            ->whereIn('party_type', ['dealer', 'distributor'])
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        foreach ($customers as $customer) {
            $dueDetails = $this->getPartyDueDetailsForPurchase($customer, $customer->party_type);
            $parties->push([
                'id'              => (string) $customer->_id,
                'name'            => $customer->name,
                'phone'           => $customer->phone ?? '-',
                'email'           => $customer->email ?? '-',
                'party_type'      => $customer->party_type,
                'party_type_text' => ucfirst($customer->party_type),
                'opening_balance' => $dueDetails['opening_balance'],
                'invoice_due'     => $dueDetails['invoice_due'],
                'total_due'       => $dueDetails['total_due'],
            ]);
        }

        // Sort by name
        $parties = $parties->sortBy('name')->values();

        return response()->json(['success' => true, 'parties' => $parties]);
    }

    /**
     * Credit Refund mode: customers who have active credit notes (we owe them money back)
     * This is for refunds when customers return goods (Credit Notes are created)
     */
    private function searchCreditRefundParties(string $search)
    {
        // Get all party_ids that have active credit notes with remaining amount > 0
        $creditPartyIds = CreditNote::whereIn('status', ['active', 'partial'])
            ->where('remaining_amount', '>', 0)
            ->pluck('party_id')
            ->map(fn($id) => (string) $id)
            ->unique()
            ->values()
            ->toArray();

        if (empty($creditPartyIds)) {
            return response()->json(['success' => true, 'parties' => []]);
        }

        $parties = collect();

        // Search customers with credit notes (dealers, distributors, regular customers)
        $customers = Customer::whereIn('_id', $creditPartyIds)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        foreach ($customers as $customer) {
            $creditDetails = $this->getCreditNoteDetails((string) $customer->_id);
            $parties->push([
                'id'              => (string) $customer->_id,
                'name'            => $customer->name,
                'phone'           => $customer->phone ?? '-',
                'email'           => $customer->email ?? '-',
                'party_type'      => $customer->party_type,
                'party_type_text' => ucfirst($customer->party_type),
                'credit_balance'  => $creditDetails['total_remaining'],
            ]);
        }

        // Sort by name
        $parties = $parties->sortBy('name')->values();

        return response()->json(['success' => true, 'parties' => $parties]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARTY DETAILS
    // ─────────────────────────────────────────────────────────────────────────
    public function getPartyDetails($partyId, Request $request)
    {
        try {
            $partyType = $request->get('type', 'vendor');

            if ($partyType === 'vendor') {
                $party = Vendor::findOrFail($partyId);
                $dueDetails = $this->getPartyDueDetailsForPurchase($party, 'vendor');

                return response()->json([
                    'success' => true,
                    'party'   => [
                        'id'              => (string) $party->id,
                        'name'            => $party->company_name,
                        'phone'           => $party->phone ?? '-',
                        'party_type'      => 'vendor',
                        'party_type_text' => 'Vendor',
                        'opening_balance' => $dueDetails['opening_balance'],
                        'invoice_due'     => $dueDetails['invoice_due'],
                        'total_due'       => $dueDetails['total_due'],
                        'debit_balance'   => $dueDetails['debit_balance'],
                        'net_payable'     => $dueDetails['net_payable'],
                        'invoices'        => $dueDetails['invoices'],
                        'debit_notes'     => $dueDetails['debit_notes'],
                    ]
                ]);
            } else {
                // Dealer or Distributor
                $party = Customer::findOrFail($partyId);
                $dueDetails = $this->getPartyDueDetailsForPurchase($party, $party->party_type);

                return response()->json([
                    'success' => true,
                    'party'   => [
                        'id'              => (string) $party->_id,
                        'name'            => $party->name,
                        'phone'           => $party->phone ?? '-',
                        'party_type'      => $party->party_type,
                        'party_type_text' => ucfirst($party->party_type),
                        'opening_balance' => $dueDetails['opening_balance'],
                        'invoice_due'     => $dueDetails['invoice_due'],
                        'total_due'       => $dueDetails['total_due'],
                        'debit_balance'   => $dueDetails['debit_balance'],
                        'net_payable'     => $dueDetails['net_payable'],
                        'invoices'        => $dueDetails['invoices'],
                        'debit_notes'     => $dueDetails['debit_notes'],
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Get party details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load party details'
            ], 500);
        }
    }

    /**
     * Get refund party details (for credit note refunds)
     */
    public function getRefundPartyDetails($partyId)
    {
        try {
            $party = Customer::findOrFail($partyId);
            $creditDetails = $this->getCreditNoteDetails($partyId);

            return response()->json([
                'success' => true,
                'party'   => [
                    'id'              => (string) $party->_id,
                    'name'            => $party->name,
                    'phone'           => $party->phone ?? '-',
                    'party_type'      => $party->party_type,
                    'party_type_text' => ucfirst($party->party_type),
                    'credit_balance'  => $creditDetails['total_remaining'],
                    'credit_notes'    => $creditDetails['credit_notes'],
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
     * Get party due details for PURCHASE invoices (money we owe to party)
     *
     * For Vendors/Dealers/Distributors:
     * - Opening balance: If positive, we owe them money
     * - Invoice due: Total of unpaid purchase invoices
     * - Debit balance: Debit notes we have (they owe us money) - reduces what we owe
     * - Net payable = Opening + Invoice - Debit
     */
    private function getPartyDueDetailsForPurchase($party, string $partyType)
    {
        $partyIdStr = $partyType === 'vendor' ? (string) $party->id : (string) $party->_id;
        $originalOpening = (float) ($party->opening_balance ?? 0);

        // For purchase invoices, positive opening balance means we owe them money
        $openingPaid = PurchasePayment::where('party_id', $partyIdStr)
            ->where('payment_type', 'payment_out')
            ->get()
            ->sum(function ($p) {
                $allocs = $p->allocations ?? [];
                return collect($allocs)
                    ->where('type', 'opening_balance')
                    ->sum('amount');
            });

        $openingBalance = max(0, $originalOpening - $openingPaid);

        // Get outstanding purchase invoices (we owe them money)
        $invoices = PurchaseInvoice::where('status', '!=', 'draft')
            ->where('status', '!=', 'cancelled')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('party_id', $partyIdStr)
            ->where('balance_amount', '>', 0)
            ->orderBy('invoice_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($invoice) {
                $grandTotal = $this->decimalToFloat($invoice->grand_total);
                $totalPaid  = $this->decimalToFloat($invoice->total_paid);
                $balance = $this->decimalToFloat($invoice->balance_amount);
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

        // DEBIT NOTES: These are FROM vendors (they owe us money for returns)
        // When we pay out, debit notes REDUCE what we owe them
        $debitNotes = DebitNote::where('party_id', $partyIdStr)
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

        $totalDebitBalance = round($debitNotes->sum('remaining_amount'), 2);

        // Net payable = Opening Balance + Invoice Due - Debit Notes (debit notes reduce what we owe)
        $netPayable = max(0, round($openingBalance + $invoiceDue - $totalDebitBalance, 2));

        return [
            'opening_balance' => $openingBalance,
            'invoice_due'     => $invoiceDue,
            'total_due'       => round($openingBalance + $invoiceDue, 2),
            'debit_balance'   => $totalDebitBalance,
            'net_payable'     => $netPayable,
            'invoices'        => $invoices,
            'debit_notes'     => $debitNotes,
        ];
    }

    /**
     * Fetch active credit notes for a party (for refunds)
     * Credit notes are created when customers return goods - we owe them money
     */
    private function getCreditNoteDetails(string $partyId): array
    {
        $creditNotes = CreditNote::where('party_id', $partyId)
            ->whereIn('status', ['active', 'partial'])
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

        return [
            'total_remaining' => round($creditNotes->sum('remaining_amount'), 2),
            'credit_notes'    => $creditNotes,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE
    // Two sub-types:
    //   payment_subtype = purchase_payment → pay for purchase invoices (money going OUT)
    //   payment_subtype = credit_refund   → refund credit notes (money going OUT to customer)
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        if ($request->input('payment_subtype') === 'credit_refund') {
            return $this->storeCreditRefund($request);
        }
        return $this->storePurchasePayment($request);
    }

    /**
     * Purchase payment logic: Pay money TO vendors, dealers, distributors for purchases
     * Debit Notes reduce what we owe (since they owe us money from returns)
     */
    private function storePurchasePayment(Request $request)
    {
        $request->validate([
            'party_id'       => 'required',
            'party_type'     => 'required|in:vendor,dealer,distributor',
            'amount'         => 'required|numeric|min:0',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,upi,bank_transfer,cheque,card'
        ]);

        try {
            // Get party model based on type
            if ($request->party_type === 'vendor') {
                $party = Vendor::findOrFail($request->party_id);
            } else {
                $party = Customer::findOrFail($request->party_id);
            }

            $cashAmount = (float) $request->amount;
            $dueDetails = $this->getPartyDueDetailsForPurchase($party, $request->party_type);

            if ($cashAmount > ($dueDetails['net_payable'] + 0.01)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount (₹ ' . number_format($cashAmount, 2) .
                                 ') cannot exceed net payable (₹ ' . number_format($dueDetails['net_payable'], 2) . ')'
                ], 422);
            }

            $paymentNumber = $this->generatePaymentNumber();
            $allocations   = [];

            // PASS 1: Debit note allocation (debit notes reduce what we owe)
            $debitAllocations = [];
            $debitUsedTotal   = 0;

            foreach ($dueDetails['debit_notes'] as $dnData) {
                if ($dnData['remaining_amount'] <= 0) continue;

                $debitModel = DebitNote::find($dnData['id']);
                if (!$debitModel) continue;

                $toUse = min($dnData['remaining_amount'], $cashAmount - $debitUsedTotal);
                if ($toUse <= 0) break;

                $debitModel->used_amount      = $this->decimalToFloat($debitModel->used_amount) + $toUse;
                $debitModel->remaining_amount = max(0, $this->decimalToFloat($debitModel->remaining_amount) - $toUse);
                if ($debitModel->remaining_amount <= 0.01) {
                    $debitModel->remaining_amount = 0;
                    $debitModel->status           = 'settled';
                } else {
                    $debitModel->status = 'partial';
                }
                $debitModel->save();

                $debitAllocations[] = [
                    'type'               => 'debit_note',
                    'debit_note_id'      => $dnData['id'],
                    'debit_note_number'  => $dnData['debit_note_number'],
                    'amount'             => $toUse,
                    'description'        => 'Debit Note Adjustment: ' . $dnData['debit_note_number'],
                ];

                $debitUsedTotal += $toUse;
            }

            $allocations   = array_merge($allocations, $debitAllocations);
            $pool          = $debitUsedTotal + $cashAmount;
            $remainingPool = $pool;

            // PASS 2: Opening balance (if positive, meaning we owe them opening balance)
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

            // PASS 3: Invoices (purchase invoices)
            foreach ($dueDetails['invoices'] as $invoiceData) {
                if ($remainingPool <= 0.001) break;

                $invoice = PurchaseInvoice::find($invoiceData['id']);
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
                PurchasePayment::create([
                    'payment_number'   => $paymentNumber,
                    'party_id'         => $request->party_id,
                    'amount'           => round($cashAmount, 2),
                    'payment_method'   => $request->payment_method,
                    'payment_date'     => $request->payment_date,
                    'status'           => 'completed',
                    'reference_no'     => $request->reference_no,
                    'notes'            => $request->notes,
                    'payment_type'     => 'payment_out',
                    'payment_subtype'  => 'purchase_payment',
                    'allocations'      => $allocations,
                    'created_by'       => auth()->id(),
                ]);
            }

            return response()->json([
                'success'        => true,
                'payment_number' => $paymentNumber,
                'debit_used'     => round($debitUsedTotal, 2),
                'cash_paid'      => round($cashAmount, 2),
                'message'        => 'Payment recorded successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Purchase payment store error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Credit refund: Pay money back to customers who have credit notes (from returns)
     */
    private function storeCreditRefund(Request $request)
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

            $creditDetails = $this->getCreditNoteDetails($partyId);

            if ($cashAmount > ($creditDetails['total_remaining'] + 0.01)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund amount (₹' . number_format($cashAmount, 2) .
                                 ') cannot exceed credit note balance (₹' . number_format($creditDetails['total_remaining'], 2) . ')'
                ], 422);
            }

            $paymentNumber = $this->generatePaymentNumber();
            $allocations   = [];
            $remaining     = $cashAmount;

            // Allocate against credit notes (oldest first)
            foreach ($creditDetails['credit_notes'] as $cnData) {
                if ($remaining <= 0.001) break;
                if ($cnData['remaining_amount'] <= 0) continue;

                $creditModel = CreditNote::find($cnData['id']);
                if (!$creditModel) continue;

                $toUse = min($cnData['remaining_amount'], $remaining);

                $creditModel->used_amount      = $this->decimalToFloat($creditModel->used_amount) + $toUse;
                $creditModel->remaining_amount = max(0, $this->decimalToFloat($creditModel->remaining_amount) - $toUse);
                if ($creditModel->remaining_amount <= 0.01) {
                    $creditModel->remaining_amount = 0;
                    $creditModel->status           = 'settled';
                } else {
                    $creditModel->status = 'partial';
                }
                $creditModel->save();

                $allocations[] = [
                    'type'               => 'credit_note',
                    'credit_note_id'     => $cnData['id'],
                    'credit_note_number' => $cnData['credit_note_number'],
                    'amount'             => round($toUse, 2),
                    'previous_remaining' => round($cnData['remaining_amount'], 2),
                    'new_remaining'      => round($creditModel->remaining_amount, 2),
                    'description'        => 'Credit Note Refund: ' . $cnData['credit_note_number'],
                ];

                $remaining -= $toUse;
            }

            // Save payment record
            PurchasePayment::create([
                'payment_number'   => $paymentNumber,
                'party_id'         => $partyId,
                'amount'           => round($cashAmount, 2),
                'payment_method'   => $request->payment_method,
                'payment_date'     => $request->payment_date,
                'status'           => 'completed',
                'reference_no'     => $request->reference_no,
                'notes'            => $request->notes,
                'payment_type'     => 'payment_out',
                'payment_subtype'  => 'credit_refund',
                'allocations'      => $allocations,
                'created_by'       => auth()->id(),
            ]);

            return response()->json([
                'success'        => true,
                'payment_number' => $paymentNumber,
                'message'        => 'Refund recorded successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Credit refund store error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to record refund: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $payment = PurchasePayment::findOrFail($id);

            if ($payment->payment_type !== 'payment_out') {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            $amount      = $this->decimalToFloat($payment->amount);
            $allocations = $payment->allocations ?? [];

            $enrichedAllocations = array_map(function ($alloc) {
                if (($alloc['type'] ?? '') === 'invoice' && !empty($alloc['invoice_id'])) {
                    $inv = PurchaseInvoice::find($alloc['invoice_id']);
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
                if (($alloc['type'] ?? '') === 'credit_note' && !empty($alloc['credit_note_id'])) {
                    $cn = CreditNote::find($alloc['credit_note_id']);
                    if ($cn) {
                        $alloc['credit_date'] = $cn->credit_date instanceof \Carbon\Carbon
                            ? $cn->credit_date->format('d-m-Y')
                            : date('d-m-Y', strtotime($cn->credit_date));
                        $alloc['total_amount'] = $this->decimalToFloat($cn->amount);
                    }
                }
                return $alloc;
            }, $allocations);

            // Resolve party name
            $partyName = '';
            $partyType = '';
            $partyPhone = '—';

            // Try vendor first
            $vendor = Vendor::find($payment->party_id);
            if ($vendor) {
                $partyName = $vendor->company_name;
                $partyType = 'vendor';
                $partyPhone = $vendor->phone ?? '—';
            } else {
                $customer = Customer::find($payment->party_id);
                if ($customer) {
                    $partyName = $customer->name;
                    $partyType = $customer->party_type ?? '';
                    $partyPhone = $customer->phone ?? '—';
                }
            }

            $responseData = [
                'success' => true,
                'payment' => [
                    'payment_number'   => $payment->payment_number ?? '—',
                    'payment_subtype'  => $payment->payment_subtype ?? 'purchase_payment',
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
            ];

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($responseData);
            }

            return view('admin.payments-out.show', ['payment' => $responseData['payment']]);

        } catch (\Exception $e) {
            Log::error('show payment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }
    }

    private function decimalToFloat($value)
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
