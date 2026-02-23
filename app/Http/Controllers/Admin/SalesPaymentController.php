<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use App\Models\SalesPayment;
use App\Models\Customer;
use Illuminate\Http\Request;
use MongoDB\BSON\Decimal128;
use Illuminate\Support\Facades\DB;

class SalesPaymentController extends Controller
{
    /**
     * Show all sales invoices with payment summary
     */
    public function index(Request $request)
    {
        $query = SalesInvoice::with('party')
            ->orderBy('invoice_date', 'desc');

        // Initialize selectedParty as null
        $selectedParty = null;

        // Filter by party (customer/dealer/distributor)
        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
            $selectedParty = Customer::find($request->party_id);
        }

        // Date filters
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%');

                // Search by party name
                $partyIds = Customer::where('name', 'like', '%' . $search . '%')
                    ->pluck('_id');
                if ($partyIds->count() > 0) {
                    $q->orWhereIn('party_id', $partyIds);
                }
            });
        }

        $invoices = $query->paginate(50);

        // Get all parties for dropdown
        $parties = Customer::orderBy('name')
            ->get()
            ->map(function($party) {
                return [
                    'id' => (string) $party->_id,
                    'name' => $party->name,
                    'phone' => $party->phone,
                    'party_type' => $party->party_type,
                    'party_type_text' => ucfirst($party->party_type),
                ];
            });

        return view('admin.payments.index', compact('invoices', 'parties', 'selectedParty'));
    }

    /**
     * Get invoice details for payment modal
     */
    public function getInvoiceDetails($id)
    {
        try {
            $invoice = SalesInvoice::with('party')->findOrFail($id);

            $grandTotal = $invoice->grand_total instanceof Decimal128
                ? (float) $invoice->grand_total->__toString()
                : (float) $invoice->grand_total;

            $totalPaid = $invoice->total_paid instanceof Decimal128
                ? (float) $invoice->total_paid->__toString()
                : (float) ($invoice->total_paid ?? 0);

            $balanceAmount = $invoice->balance_amount instanceof Decimal128
                ? (float) $invoice->balance_amount->__toString()
                : (float) ($invoice->balance_amount ?? 0);

            return response()->json([
                'success' => true,
                'invoice' => [
                    'id' => (string) $invoice->_id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date->format('d M Y'),
                    'party_id' => (string) $invoice->party_id,
                    'party_name' => $invoice->party->name ?? 'N/A',
                    'party_type' => $invoice->party->party_type ?? 'customer',
                    'grand_total' => $grandTotal,
                    'total_paid' => $totalPaid,
                    'balance_amount' => $balanceAmount,
                    'payment_status' => $invoice->payment_status,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }
    }

    /**
     * Store payment
     */
    public function store(Request $request)
    {
        $request->validate([
            'sales_invoice_id' => 'required|exists:sales_invoices,_id',
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'reference_no' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {

            $invoice = SalesInvoice::findOrFail($request->sales_invoice_id);
            $amount = (float) $request->amount;

            // Convert existing total_paid to float
            $currentTotalPaid = $invoice->total_paid instanceof Decimal128
                ? (float) $invoice->total_paid->__toString()
                : (float) ($invoice->total_paid ?? 0);

            // Check if amount is negative (reduce payment)
            if ($amount < 0) {
                if (abs($amount) > $currentTotalPaid) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot reduce more than total paid amount'
                    ], 400);
                }
            }

            // Create payment
            $payment = SalesPayment::create([
                'sales_invoice_id' => $invoice->_id,
                'amount' => round($amount, 2),
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'status' => 'completed',
                'reference_no' => $request->reference_no ?: $invoice->invoice_number,
                'notes' => $request->notes,
            ]);

            // Recalculate invoice totals
            $this->recalculateInvoiceTotals($invoice);



            // Get updated invoice data
            $invoice->refresh();

            $grandTotal = $invoice->grand_total instanceof Decimal128
                ? (float) $invoice->grand_total->__toString()
                : (float) $invoice->grand_total;

            $totalPaid = $invoice->total_paid instanceof Decimal128
                ? (float) $invoice->total_paid->__toString()
                : (float) ($invoice->total_paid ?? 0);

            $balanceAmount = $invoice->balance_amount instanceof Decimal128
                ? (float) $invoice->balance_amount->__toString()
                : (float) ($invoice->balance_amount ?? 0);

            return response()->json([
                'success' => true,
                'message' => $amount < 0 ? 'Payment reduced successfully' : 'Payment added successfully',
                'invoice' => [
                    'id' => (string) $invoice->_id,
                    'invoice_number' => $invoice->invoice_number,
                    'grand_total' => $grandTotal,
                    'total_paid' => $totalPaid,
                    'balance_amount' => $balanceAmount,
                    'payment_status' => $invoice->payment_status,
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete payment
     */
    public function destroy($id)
    {
        try {

            $payment = SalesPayment::findOrFail($id);
            $invoice = SalesInvoice::findOrFail($payment->sales_invoice_id);

            // Delete payment
            $payment->delete();

            // Recalculate invoice totals
            $this->recalculateInvoiceTotals($invoice);



            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete payments
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'required|string',
        ]);

        try {

            foreach ($request->payment_ids as $paymentId) {
                $payment = SalesPayment::find($paymentId);
                if ($payment) {
                    $invoice = SalesInvoice::find($payment->sales_invoice_id);
                    $payment->delete();

                    // Recalculate invoice
                    if ($invoice) {
                        $this->recalculateInvoiceTotals($invoice);
                    }
                }
            }


            return response()->json([
                'success' => true,
                'message' => 'Selected payments deleted successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate invoice totals based on payments
     */
    private function recalculateInvoiceTotals($invoice)
    {
        $payments = $invoice->payments()->get();
        $totalPaid = 0;

        foreach ($payments as $payment) {
            $paymentAmount = $payment->amount instanceof Decimal128
                ? (float) $payment->amount->__toString()
                : (float) $payment->amount;
            $totalPaid += $paymentAmount;
        }

        $grandTotal = $invoice->grand_total instanceof Decimal128
            ? (float) $invoice->grand_total->__toString()
            : (float) $invoice->grand_total;

        $balance = round($grandTotal - $totalPaid, 2);

        if ($totalPaid <= 0) {
            $status = 'unpaid';
        } elseif ($balance <= 0.01) {
            $status = 'paid';
            $balance = 0;
        } else {
            $status = 'partial';
        }

        $invoice->update([
            'total_paid' => round($totalPaid, 2),
            'balance_amount' => $balance,
            'payment_status' => $status,
        ]);

        return $invoice;
    }
}
