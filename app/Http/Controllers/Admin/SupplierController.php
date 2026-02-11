<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('created_at', 'desc')->get();
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:suppliers,email',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'gstin' => 'nullable|string|max:15',
            'pan' => 'nullable|string|max:10',
            'contact_person' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'payment_terms' => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric|min:0',
            'balance_type' => 'required_with:opening_balance|in:due,advance',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        try {
            $validated['supplier_code'] = Supplier::generateSupplierCode();

            $openingBalance = $validated['opening_balance'] ?? 0;
            $balanceType = $validated['balance_type'] ?? 'due';

            if ($balanceType === 'due') {
                $validated['current_balance'] = $openingBalance;
            } else {
                $validated['current_balance'] = -$openingBalance;
            }

            $supplier = Supplier::create($validated);

            if ($openingBalance > 0) {
                SupplierLedger::createOpeningEntry(
                    $supplier->id,
                    $openingBalance,
                    $balanceType,
                    now()
                );
            }

            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier created successfully!');
        } catch (\Exception $e) {
            Log::error('Supplier creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create supplier.');
        }
    }

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        $purchases = $supplier->purchases()->latest()->take(10)->get();
        $payments = $supplier->payments()->latest()->take(10)->get();
        $ledgerEntries = $supplier->ledgerEntries()->latest('date')->take(20)->get();

        $stats = [
            'total_purchases' => $supplier->getTotalPurchases(),
            'total_payments' => $supplier->getTotalPayments(),
            'current_balance' => $supplier->current_balance,
            'total_due' => $supplier->getDueAmount(),
        ];

        return view('admin.suppliers.show', compact('supplier', 'purchases', 'payments', 'ledgerEntries', 'stats'));
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:suppliers,email,' . $id,
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'gstin' => 'nullable|string|max:15',
            'pan' => 'nullable|string|max:10',
            'contact_person' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'payment_terms' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        try {
            $supplier->update($validated);
            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier updated successfully!');
        } catch (\Exception $e) {
            Log::error('Supplier update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update supplier.');
        }
    }

    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            if ($supplier->purchases()->count() > 0) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete supplier with existing purchases.'
                    ], 400);
                }
                return back()->with('error', 'Cannot delete supplier with existing purchases.');
            }

            $supplier->ledgerEntries()->delete();
            $supplier->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Supplier deleted successfully!'
                ]);
            }

            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Supplier deletion failed: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete supplier.'
                ], 500);
            }

            return back()->with('error', 'Failed to delete supplier.');
        }
    }

    public function bulkUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                'supplier_ids' => 'required|array',
                'status' => 'required|in:active,inactive'
            ]);

            Supplier::whereIn('id', $request->supplier_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Supplier status updated successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk status update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update supplier status'
            ], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'supplier_ids' => 'required|array'
            ]);

            $deleted = 0;
            $errors = 0;

            foreach ($request->supplier_ids as $id) {
                $supplier = Supplier::find($id);
                if ($supplier && $supplier->purchases()->count() === 0) {
                    $supplier->ledgerEntries()->delete();
                    $supplier->delete();
                    $deleted++;
                } else {
                    $errors++;
                }
            }

            $message = "Deleted {$deleted} supplier(s)";
            if ($errors > 0) {
                $message .= ". {$errors} supplier(s) could not be deleted.";
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete suppliers'
            ], 500);
        }
    }

    public function getActiveSuppliers()
    {
        $suppliers = Supplier::active()->orderBy('name', 'asc')->get();
        return response()->json($suppliers);
    }

    public function ledger($id, Request $request)
    {
        $supplier = Supplier::findOrFail($id);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $ledgerEntries = SupplierLedger::getSupplierLedgerReport($id, $startDate, $endDate);

        $openingBalance = 0;
        if ($startDate) {
            $openingBalance = SupplierLedger::getSupplierBalance($id, date('Y-m-d', strtotime($startDate . ' -1 day')));
        }

        $stats = [
            'total_purchases' => $supplier->getTotalPurchases(),
            'total_payments' => $supplier->getTotalPayments(),
        ];

        return view('admin.suppliers.ledger', compact('supplier', 'ledgerEntries', 'openingBalance', 'startDate', 'endDate', 'stats'));
    }
}
