<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupplierPayment;
use App\Models\Supplier;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    public function index()
    {
        $payments = SupplierPayment::with(['supplier', 'purchase'])
            ->orderBy('created_at', 'desc')
            ->get();

        $suppliers = Supplier::active()->get();

        return view('admin.supplier-payments.index', compact('payments', 'suppliers'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::active()->orderBy('name', 'asc')->get();

        $purchaseId = $request->input('purchase_id');
        $supplierId = $request->input('supplier_id');

        $purchase = null;
        $supplier = null;

        if ($purchaseId) {
            $purchase = Purchase::findOrFail($purchaseId);
            $supplier = $purchase->supplier;
        } elseif ($supplierId) {
            $supplier = Supplier::findOrFail($supplierId);
        }

        return view('admin.supplier-payments.create', compact('suppliers', 'purchase', 'supplier'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'nullable|exists:purchases,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|in:cash,upi,bank,cheque',
            'payment_date' => 'required|date',
            'reference_no' => 'nullable|string|max:100',
            'cheque_no' => 'required_if:payment_mode,cheque|nullable|string|max:50',
            'cheque_date' => 'required_if:payment_mode,cheque|nullable|date',
            'bank_name' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $paymentNo = SupplierPayment::generatePaymentNo();

            $payment = SupplierPayment::create([
                'payment_no' => $paymentNo,
                'purchase_id' => $validated['purchase_id'],
                'supplier_id' => $validated['supplier_id'],
                'amount' => $validated['amount'],
                'payment_mode' => $validated['payment_mode'],
                'payment_date' => $validated['payment_date'],
                'reference_no' => $validated['reference_no'],
                'cheque_no' => $validated['cheque_no'] ?? null,
                'cheque_date' => $validated['cheque_date'] ?? null,
                'bank_name' => $validated['bank_name'],
                'note' => $validated['note'],
                'created_by' => Auth::guard('admin')->id(),
            ]);

            if ($payment->purchase_id) {
                $payment->updatePurchasePayment();
            }

            $payment->createLedgerEntry();

            DB::commit();

            return redirect()->route('admin.supplier-payments.index')
                ->with('success', 'Payment recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to record payment.');
        }
    }

    public function show($id)
    {
        $payment = SupplierPayment::with(['supplier', 'purchase'])->findOrFail($id);
        return view('admin.supplier-payments.show', compact('payment'));
    }

    public function destroy($id)
    {
        try {
            $payment = SupplierPayment::findOrFail($id);

            DB::beginTransaction();

            if ($payment->purchase) {
                $purchase = $payment->purchase;
                $purchase->paid_amount -= $payment->amount;
                $purchase->due_amount += $payment->amount;
                $purchase->updatePaymentStatus();
            }

            $payment->ledgerEntry()->delete();

            $supplier = $payment->supplier;
            $supplier->updateBalance($payment->amount, 'debit');

            \App\Models\SupplierLedger::recalculateBalance($payment->supplier_id);

            $payment->delete();

            DB::commit();

            return redirect()->route('admin.supplier-payments.index')
                ->with('success', 'Payment deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete payment.');
        }
    }

    public function getPendingPurchases($supplierId)
    {
        try {
            $purchases = Purchase::where('supplier_id', $supplierId)
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->where('status', 'confirmed')
                ->orderBy('purchase_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'purchases' => $purchases
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load purchases'
            ], 500);
        }
    }
}
