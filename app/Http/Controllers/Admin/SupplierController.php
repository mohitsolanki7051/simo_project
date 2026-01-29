<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
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
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        try {
            Supplier::create($validated);
            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier created successfully!');
        } catch (\Exception $e) {
            Log::error('Supplier creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to create supplier. Please try again.');
        }
    }

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        $purchaseOrders = $supplier->purchaseOrders()->latest()->take(10)->get();

        return view('admin.suppliers.show', compact('supplier', 'purchaseOrders'));
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
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        try {
            $supplier->update($validated);
            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier updated successfully!');
        } catch (\Exception $e) {
            Log::error('Supplier update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update supplier. Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            // Check if supplier has purchase orders
            if ($supplier->purchaseOrders()->count() > 0) {
                return back()->with('error', 'Cannot delete supplier with existing purchase orders.');
            }

            $supplier->delete();

            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Supplier deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete supplier. Please try again.');
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
                if ($supplier && $supplier->purchaseOrders()->count() === 0) {
                    $supplier->delete();
                    $deleted++;
                } else {
                    $errors++;
                }
            }

            $message = "Deleted {$deleted} supplier(s)";
            if ($errors > 0) {
                $message .= ". {$errors} supplier(s) could not be deleted (may have purchase orders).";
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
}
