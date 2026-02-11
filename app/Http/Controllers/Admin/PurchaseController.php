<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['supplier'])->orderBy('created_at', 'desc')->get();
        $suppliers = Supplier::active()->get();

        return view('admin.purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name', 'asc')->get();
        $products = Product::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('admin.purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_no' => 'nullable|string|max:50',
            'invoice_date' => 'nullable|date',
            'purchase_date' => 'required|date',
            'payment_terms' => 'nullable|string|max:100',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:fixed,percent',
            'shipping_charge' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,confirmed',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $purchaseNo = Purchase::generatePurchaseNo();

            $purchase = Purchase::create([
                'purchase_no' => $purchaseNo,
                'supplier_id' => $validated['supplier_id'],
                'invoice_no' => $validated['invoice_no'],
                'invoice_date' => $validated['invoice_date'],
                'purchase_date' => $validated['purchase_date'],
                'payment_terms' => $validated['payment_terms'],
                'discount' => $validated['discount'] ?? 0,
                'discount_type' => $validated['discount_type'],
                'shipping_charge' => $validated['shipping_charge'] ?? 0,
                'subtotal' => $validated['subtotal'],
                'tax_amount' => $validated['tax_amount'],
                'grand_total' => $validated['grand_total'],
                'notes' => $validated['notes'],
                'status' => $validated['status'],
                'payment_status' => 'unpaid',
                'paid_amount' => 0,
                'due_amount' => $validated['grand_total'],
                'created_by' => Auth::guard('admin')->id(),
            ]);

            foreach ($request->input('items') as $itemData) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $itemData['product_id'],
                    'product_name' => $itemData['product_name'] ?? 'Product',
                    'variant_index' => $itemData['variant_index'],
                    'variant_name' => $itemData['variant_name'] ?? 'Default',
                    'sku_code' => $itemData['sku_code'] ?? 'SKU',
                    'unit' => $itemData['unit'] ?? 'pcs',
                    'qty' => $itemData['qty'],
                    'rate' => $itemData['rate'],
                    'tax_percent' => $itemData['tax_percent'] ?? 0,
                    'tax_amount' => $itemData['tax_amount'] ?? 0,
                    'total' => $itemData['total'],
                ]);
            }

            if ($validated['status'] === 'confirmed') {
                foreach ($purchase->items as $item) {
                    $item->updateProductStock();
                }
                $purchase->createLedgerEntry();
                $purchase->confirmed_by = Auth::guard('admin')->id();
                $purchase->confirmed_at = now();
                $purchase->save();
            }

            DB::commit();

            return redirect()->route('admin.purchases.index')
                ->with('success', 'Purchase created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create purchase.');
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'items', 'payments'])->findOrFail($id);
        return view('admin.purchases.show', compact('purchase'));
    }

    public function edit($id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        if (!$purchase->canEdit()) {
            return redirect()->route('admin.purchases.index')
                ->with('error', 'This purchase cannot be edited.');
        }

        $suppliers = Supplier::active()->orderBy('name', 'asc')->get();
        $products = Product::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('admin.purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);

        if (!$purchase->canEdit()) {
            return back()->with('error', 'This purchase cannot be edited.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_no' => 'nullable|string|max:50',
            'invoice_date' => 'nullable|date',
            'purchase_date' => 'required|date',
            'discount' => 'nullable|numeric|min:0',
            'shipping_charge' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $purchase->update($validated);
            $purchase->items()->delete();

            foreach ($request->input('items') as $itemData) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $itemData['product_id'],
                    'product_name' => $itemData['product_name'] ?? 'Product',
                    'variant_index' => $itemData['variant_index'],
                    'variant_name' => $itemData['variant_name'] ?? 'Default',
                    'sku_code' => $itemData['sku_code'] ?? 'SKU',
                    'unit' => $itemData['unit'] ?? 'pcs',
                    'qty' => $itemData['qty'],
                    'rate' => $itemData['rate'],
                    'tax_percent' => $itemData['tax_percent'] ?? 0,
                    'tax_amount' => $itemData['tax_amount'] ?? 0,
                    'total' => $itemData['total'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.purchases.show', $purchase->id)
                ->with('success', 'Purchase updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update purchase.');
        }
    }

    public function destroy($id)
    {
        try {
            $purchase = Purchase::findOrFail($id);

            if (!$purchase->canDelete()) {
                return back()->with('error', 'This purchase cannot be deleted.');
            }

            DB::beginTransaction();
            $purchase->items()->delete();
            $purchase->delete();
            DB::commit();

            return redirect()->route('admin.purchases.index')
                ->with('success', 'Purchase deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete purchase.');
        }
    }

    public function confirm($id)
    {
        try {
            $purchase = Purchase::with('items')->findOrFail($id);

            if ($purchase->status !== 'draft') {
                return back()->with('error', 'Only draft purchases can be confirmed.');
            }

            DB::beginTransaction();

            $purchase->status = 'confirmed';
            $purchase->confirmed_by = Auth::guard('admin')->id();
            $purchase->confirmed_at = now();
            $purchase->save();

            foreach ($purchase->items as $item) {
                $item->updateProductStock();
            }

            $purchase->createLedgerEntry();

            DB::commit();

            return redirect()->route('admin.purchases.show', $purchase->id)
                ->with('success', 'Purchase confirmed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase confirmation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to confirm purchase.');
        }
    }

    public function invoice($id)
    {
        $purchase = Purchase::with(['supplier', 'items'])->findOrFail($id);
        return view('admin.purchases.invoice', compact('purchase'));
    }
}
