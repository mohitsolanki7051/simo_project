<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentIn;
use App\Models\PaymentInItem;
use App\Models\SalesInvoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\Decimal128;

class PaymentInController extends Controller
{
    /**
     * Display payment in list (ALL PAYMENTS - paid, unpaid, partial)
     */
    public function index(Request $request)
    {
        $query = PaymentIn::with(['party', 'items'])
            ->orderBy('created_at', 'desc');

        // Filter by party
        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        // Date filters
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        // Search by payment number
        if ($request->filled('search')) {
            $query->where('payment_number', 'like', '%' . $request->search . '%');
        }

        $payments = $query->paginate(20);

        // Get all parties for filter dropdown
        $parties = Customer::orderBy('name')->get();

        return view('admin.payment_in.index', compact('payments', 'parties'));
    }

    /**
     * Show create payment form - EXACTLY LIKE YOUR IMAGE
     */
    public function create()
    {
        return view('admin.payment_in.create');
    }

    /**
     * Get party details with unpaid/partial invoices (AJAX)
     */
    public function getPartyDetails(Request $request)
    {
        $request->validate([
            'party_id' => 'required|exists:customers,_id'
        ]);

        $party = Customer::findOrFail($request->party_id);

        // Get all unpaid or partial invoices for this party
        $invoices = SalesInvoice::with('party')
            ->where('party_id', $request->party_id)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('status', 'confirmed')
            ->orderBy('invoice_date', 'desc')
            ->get()
            ->map(function($invoice) {
                $grandTotal = $invoice->grand_total instanceof Decimal128
                    ? (float) $invoice->grand_total->__toString()
                    : (float) $invoice->grand_total;

                $totalPaid = $invoice->total_paid instanceof Decimal128
                    ? (float) $invoice->total_paid->__toString()
                    : (float) ($invoice->total_paid ?? 0);

                $balance = $invoice->balance_amount instanceof Decimal128
                    ? (float) $invoice->balance_amount->__toString()
                    : (float) ($invoice->balance_amount ?? 0);

                return [
                    'id' => (string) $invoice->_id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date->format('d M Y'),
                    'due_date' => $invoice->due_date ? $invoice->due_date->format('d M Y') : '-',
                    'grand_total' => $grandTotal,
                    'total_paid' => $totalPaid,
                    'balance' => $balance,
                    'payment_status' => $invoice->payment_status,
                ];
            });

        // Calculate total outstanding
        $totalOutstanding = $invoices->sum('balance');

        return response()->json([
            'success' => true,
            'party' => [
                'id' => (string) $party->_id,
                'name' => $party->name,
                'phone' => $party->phone,
                'email' => $party->email,
                'party_type' => $party->party_type,
                'current_balance' => $totalOutstanding,
            ],
            'invoices' => $invoices,
            'total_outstanding' => $totalOutstanding,
        ]);
    }

    /**
     * Search parties (AJAX for search)
     */
    public function searchParties(Request $request)
    {
        $search = $request->get('q', '');

        $parties = Customer::where('status', 'active')
            ->where(function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->limit(20)
            ->get()
            ->map(function($party) {
                return [
                    'id' => (string) $party->_id,
                    'name' => $party->name,
                    'phone' => $party->phone,
                    'email' => $party->email,
                    'party_type' => $party->party_type,
                    'party_type_text' => ucfirst($party->party_type),
                ];
            });

        return response()->json([
            'success' => true,
            'parties' => $parties
        ]);
    }

    /**
     * Search invoice by number (AJAX)
     */
    public function searchInvoice(Request $request)
    {
        $request->validate([
            'party_id' => 'required|exists:customers,_id',
            'invoice_number' => 'required|string'
        ]);

        $invoice = SalesInvoice::where('party_id', $request->party_id)
            ->where('invoice_number', 'like', '%' . $request->invoice_number . '%')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('status', 'confirmed')
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found or already paid'
            ]);
        }

        $grandTotal = $invoice->grand_total instanceof Decimal128
            ? (float) $invoice->grand_total->__toString()
            : (float) $invoice->grand_total;

        $totalPaid = $invoice->total_paid instanceof Decimal128
            ? (float) $invoice->total_paid->__toString()
            : (float) ($invoice->total_paid ?? 0);

        $balance = $invoice->balance_amount instanceof Decimal128
            ? (float) $invoice->balance_amount->__toString()
            : (float) ($invoice->balance_amount ?? 0);

        return response()->json([
            'success' => true,
            'invoice' => [
                'id' => (string) $invoice->_id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date->format('d M Y'),
                'due_date' => $invoice->due_date ? $invoice->due_date->format('d M Y') : '-',
                'grand_total' => $grandTotal,
                'balance' => $balance,
            ]
        ]);
    }

    /**
     * Store payment for multiple invoices
     */
    public function store(Request $request)
    {
        $request->validate([
            'party_id' => 'required|exists:customers,_id',
            'amount_received' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|in:cash,upi,bank_transfer,cheque,card',
            'notes' => 'nullable|string',
            'invoices' => 'required|array|min:1',
            'invoices.*.id' => 'required|exists:sales_invoices,_id',
            'invoices.*.paid' => 'required|numeric|min:0',
        ]);

        try {

            // Generate payment number
            $paymentNumber = PaymentIn::generatePaymentNumber();

            // Calculate total amount
            $totalAmount = array_sum(array_column($request->invoices, 'paid'));

            // Create Payment In
            $payment = PaymentIn::create([
                'payment_number' => $paymentNumber,
                'payment_date' => $request->payment_date,
                'party_id' => $request->party_id,
                'party_type' => Customer::find($request->party_id)->party_type,
                'total_amount' => round($totalAmount, 2),
                'payment_method' => $request->payment_mode,
                'reference_no' => $request->reference_no,
                'notes' => $request->notes,
                'status' => 'completed',
                'created_by' => Auth::guard('admin')->id(),
            ]);

            // Process each invoice
            foreach ($request->invoices as $invoiceData) {
                if ($invoiceData['paid'] <= 0) continue;

                $invoice = SalesInvoice::findOrFail($invoiceData['id']);

                // Convert values to float
                $currentTotalPaid = $invoice->total_paid instanceof Decimal128
                    ? (float) $invoice->total_paid->__toString()
                    : (float) ($invoice->total_paid ?? 0);

                $currentBalance = $invoice->balance_amount instanceof Decimal128
                    ? (float) $invoice->balance_amount->__toString()
                    : (float) ($invoice->balance_amount ?? 0);

                $grandTotal = $invoice->grand_total instanceof Decimal128
                    ? (float) $invoice->grand_total->__toString()
                    : (float) $invoice->grand_total;

                $paidAmount = (float) $invoiceData['paid'];

                // Calculate new values
                $newTotalPaid = $currentTotalPaid + $paidAmount;
                $newBalance = $currentBalance - $paidAmount;

                if ($newBalance < 0) {
                    throw new \Exception("Payment amount exceeds balance for invoice {$invoice->invoice_number}");
                }

                // Determine new payment status
                if ($newBalance <= 0.01) {
                    $newStatus = 'paid';
                    $newBalance = 0;
                } else {
                    $newStatus = 'partial';
                }

                // Update invoice
                $invoice->update([
                    'total_paid' => round($newTotalPaid, 2),
                    'balance_amount' => round($newBalance, 2),
                    'payment_status' => $newStatus,
                ]);

                // Create Payment In Item
                PaymentInItem::create([
                    'payment_in_id' => $payment->_id,
                    'sales_invoice_id' => $invoice->_id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_amount' => round($grandTotal, 2),
                    'paid_amount' => round($paidAmount, 2),
                    'balance_before' => round($currentBalance, 2),
                    'balance_after' => round($newBalance, 2),
                ]);
            }


            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'payment_id' => (string) $payment->_id,
                'payment_number' => $payment->payment_number,
            ]);

        } catch (\Exception $e) {
          
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show payment details
     */
    public function show($id)
    {
        $payment = PaymentIn::with(['party', 'items', 'items.invoice'])
            ->findOrFail($id);

        return view('admin.payment_in.show', compact('payment'));
    }
}
