<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use PDF; // Use barryvdh/laravel-dompdf

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'warehouse'])
            ->orderBy('created_at', 'desc')
            ->get();

        $warehouses = Warehouse::where('status', 'active')->orderBy('name', 'asc')->get();
        $suppliers = Supplier::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('admin.purchase-orders.index', compact('purchaseOrders', 'warehouses', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name', 'asc')->get();
        $warehouses = Warehouse::where('status', 'active')->orderBy('name', 'asc')->get();

        $products = collect();

        return view('admin.purchase-orders.create', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Basic Information
            'po_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,_id',
            'warehouse_id' => 'required|exists:warehouses,_id',

            // Supplier Details (auto-filled but validated)
            'supplier_name' => 'required|string|max:255',
            'supplier_email' => 'nullable|email|max:255',
            'supplier_phone' => 'nullable|string|max:20',
            'supplier_address' => 'nullable|string',

            // Purchase Details
            'reference_number' => 'nullable|string|max:100',
            'payment_terms' => 'required|string|max:100',
            'expected_delivery_date' => 'required|date|after_or_equal:po_date',
            'shipping_method' => 'nullable|string|max:100',
            'shipping_address' => 'nullable|string',

            // Items
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,_id',
            'items.*.product_name' => 'required|string',
            'items.*.variant_index' => 'required|integer|min:0',
            'items.*.variant_name' => 'required|string',
            'items.*.sku_code' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.gst' => 'required|numeric|min:0|max:100',
            'items.*.tax_amount' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',

            // Financial
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'shipping_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',

            // Status
            'status' => 'required|in:draft,pending',
            'payment_status' => 'required|in:unpaid,partial,paid',

            // Notes
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $purchaseOrder = new PurchaseOrder();

            // Generate PO Number
            $poNumber = $purchaseOrder->generatePONumber();

            // Create Purchase Order
            $purchaseOrder->fill([
                'po_number' => $poNumber,
                'po_date' => $request->po_date,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'supplier_name' => $request->supplier_name,
                'supplier_email' => $request->supplier_email,
                'supplier_phone' => $request->supplier_phone,
                'supplier_address' => $request->supplier_address,
                'reference_number' => $request->reference_number,
                'payment_terms' => $request->payment_terms,
                'expected_delivery_date' => $request->expected_delivery_date,
                'shipping_method' => $request->shipping_method,
                'shipping_address' => $request->shipping_address,
                'items' => $request->items,
                'subtotal' => $request->subtotal,
                'tax_amount' => $request->tax_amount,
                'discount_amount' => $request->discount_amount ?? 0,
                'discount_type' => $request->discount_type ?? 'fixed',
                'shipping_charges' => $request->shipping_charges ?? 0,
                'other_charges' => $request->other_charges ?? 0,
                'total_amount' => $request->total_amount,
                'status' => $request->status,
                'payment_status' => $request->payment_status,
                'received_status' => 'not_received',
                'terms_conditions' => $request->terms_conditions,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            $purchaseOrder->save();

            return redirect()->route('admin.purchase-orders.index')
                ->with('success', 'Purchase Order created successfully! PO Number: ' . $poNumber);

        } catch (\Exception $e) {
            Log::error('Purchase Order creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to create purchase order. Please try again.');
        }
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'warehouse', 'creator', 'approver', 'receiver'])
            ->findOrFail($id);

        return view('admin.purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if (!$purchaseOrder->canEdit()) {
            return redirect()->route('admin.purchase-orders.index')
                ->with('error', 'This purchase order cannot be edited.');
        }

        $suppliers = Supplier::where('status', 'active')->orderBy('name', 'asc')->get();
        $warehouses = Warehouse::where('status', 'active')->orderBy('name', 'asc')->get();
        $products = Product::where('status', 'active')
            ->with(['category'])
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'warehouses', 'products'));
    }

    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if (!$purchaseOrder->canEdit()) {
            return redirect()->route('admin.purchase-orders.index')
                ->with('error', 'This purchase order cannot be edited.');
        }

        $validated = $request->validate([
            'po_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,_id',
            'warehouse_id' => 'required|exists:warehouses,_id',
            'supplier_name' => 'required|string|max:255',
            'supplier_email' => 'nullable|email|max:255',
            'supplier_phone' => 'nullable|string|max:20',
            'supplier_address' => 'nullable|string',
            'reference_number' => 'nullable|string|max:100',
            'payment_terms' => 'required|string|max:100',
            'expected_delivery_date' => 'required|date|after_or_equal:po_date',
            'shipping_method' => 'nullable|string|max:100',
            'shipping_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,_id',
            'items.*.product_name' => 'required|string',
            'items.*.variant_index' => 'required|integer|min:0',
            'items.*.variant_name' => 'required|string',
            'items.*.sku_code' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.gst' => 'required|numeric|min:0|max:100',
            'items.*.tax_amount' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'shipping_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:draft,pending',
            'payment_status' => 'required|in:unpaid,partial,paid',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $purchaseOrder->update([
                'po_date' => $request->po_date,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'supplier_name' => $request->supplier_name,
                'supplier_email' => $request->supplier_email,
                'supplier_phone' => $request->supplier_phone,
                'supplier_address' => $request->supplier_address,
                'reference_number' => $request->reference_number,
                'payment_terms' => $request->payment_terms,
                'expected_delivery_date' => $request->expected_delivery_date,
                'shipping_method' => $request->shipping_method,
                'shipping_address' => $request->shipping_address,
                'items' => $request->items,
                'subtotal' => $request->subtotal,
                'tax_amount' => $request->tax_amount,
                'discount_amount' => $request->discount_amount ?? 0,
                'discount_type' => $request->discount_type ?? 'fixed',
                'shipping_charges' => $request->shipping_charges ?? 0,
                'other_charges' => $request->other_charges ?? 0,
                'total_amount' => $request->total_amount,
                'status' => $request->status,
                'payment_status' => $request->payment_status,
                'terms_conditions' => $request->terms_conditions,
                'notes' => $request->notes,
            ]);

            return redirect()->route('admin.purchase-orders.index')
                ->with('success', 'Purchase Order updated successfully!');

        } catch (\Exception $e) {
            Log::error('Purchase Order update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update purchase order. Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if (!in_array($purchaseOrder->status, ['draft', 'cancelled'])) {
                return back()->with('error', 'Only draft or cancelled purchase orders can be deleted.');
            }

            $purchaseOrder->delete();

            return redirect()->route('admin.purchase-orders.index')
                ->with('success', 'Purchase Order deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Purchase Order deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete purchase order. Please try again.');
        }
    }

    public function approve($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if (!$purchaseOrder->canApprove()) {
                return back()->with('error', 'This purchase order cannot be approved.');
            }

            $purchaseOrder->approve(Auth::id());

            return redirect()->route('admin.purchase-orders.show', $id)
                ->with('success', 'Purchase Order approved successfully!');

        } catch (\Exception $e) {
            Log::error('Purchase Order approval failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve purchase order. Please try again.');
        }
    }

    public function receive($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if (!$purchaseOrder->canReceive()) {
                return back()->with('error', 'This purchase order cannot be marked as received.');
            }

            // Update stock for all items
            foreach ($purchaseOrder->items as $item) {
                $product = Product::find($item['product_id']);
                if ($product && isset($item['variant_index'])) {
                    $product->updateVariantStock(
                        $item['variant_index'],
                        $item['quantity'],
                        'add'
                    );
                }
            }

            $purchaseOrder->markAsReceived(Auth::id());

            return redirect()->route('admin.purchase-orders.show', $id)
                ->with('success', 'Purchase Order marked as received and stock updated!');

        } catch (\Exception $e) {
            Log::error('Purchase Order receive failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to receive purchase order. Please try again.');
        }
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ]);

        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if (!$purchaseOrder->canCancel()) {
                return back()->with('error', 'This purchase order cannot be cancelled.');
            }

            $purchaseOrder->cancel(Auth::id(), $request->cancellation_reason);

            return redirect()->route('admin.purchase-orders.index')
                ->with('success', 'Purchase Order cancelled successfully!');

        } catch (\Exception $e) {
            Log::error('Purchase Order cancellation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to cancel purchase order. Please try again.');
        }
    }

    public function generateInvoice($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::with(['supplier', 'warehouse'])
                ->findOrFail($id);

            $pdf = PDF::loadView('admin.purchase-orders.invoice', compact('purchaseOrder'));

            return $pdf->download('PO-Invoice-' . $purchaseOrder->po_number . '.pdf');

        } catch (\Exception $e) {
            Log::error('Invoice generation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate invoice. Please try again.');
        }
    }

    public function printInvoice($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::with(['supplier', 'warehouse'])
                ->findOrFail($id);

            return view('admin.purchase-orders.invoice', compact('purchaseOrder'));

        } catch (\Exception $e) {
            Log::error('Invoice print failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to print invoice. Please try again.');
        }
    }

    // Get supplier details for auto-fill
    public function getSupplierDetails($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            return response()->json([
                'success' => true,
                'supplier' => [
                    'name' => $supplier->name,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone,
                    'address' => $supplier->full_address,
                    'payment_terms' => $supplier->payment_terms,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier not found'
            ], 404);
        }
    }

    public function getProductsByWarehouse($warehouseId)
    {
        try {
            $products = Product::where('warehouse_id', $warehouseId)
                ->where('status', 'active')
                ->with(['category'])
                ->orderBy('name', 'asc')
                ->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No products in this warehouse'
                ]);
            }

            $formattedProducts = [];
            foreach ($products as $product) {
                $formattedProducts[] = [
                    'id' => (string)$product->_id,
                    'name' => $product->name,
                    'sku_code' => $product->sku_code,
                    'gst' => $product->gst ?? 0,
                    'variants' => $product->variants ?? []
                ];
            }

            return response()->json([
                'success' => true,
                'products' => $formattedProducts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading products'
            ], 500);
        }
    }

    public function getProductVariants($id)
    {
        try {
            $productId = trim($id);

            $allProducts = Product::all();

            $product = null;

            foreach ($allProducts as $prod) {
                $dbId = (string)$prod->_id;
                $inputId = (string)$productId;

                if ($dbId === $inputId) {
                    $product = $prod;
                    break;
                }
            }

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $variants = [];

            if ($product->variants && is_array($product->variants) && count($product->variants) > 0) {
                foreach ($product->variants as $index => $variant) {
                    $variants[] = [
                        'name' => $variant['name'] ?? 'Variant ' . ($index + 1),
                        'sku_code' => $variant['sku_code'] ?? '',
                        'unit' => $variant['unit'] ?? 'PCS',
                        'cost_price' => $variant['cost_price'] ?? 0
                    ];
                }
            } else {
                $variants[] = [
                    'name' => $product->name,
                    'sku_code' => $product->sku_code,
                    'unit' => 'PCS',
                    'cost_price' => 0
                ];
            }

            return response()->json([
                'success' => true,
                'product' => [
                    'name' => $product->name,
                    'sku_code' => $product->sku_code,
                    'gst' => $product->gst ?? 0,
                    'variants' => $variants
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading product'
            ], 500);
        }
    }

    // Bulk operations
    public function bulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'po_ids' => 'required|array'
            ]);

            $deleted = 0;
            $errors = 0;

            foreach ($request->po_ids as $id) {
                $purchaseOrder = PurchaseOrder::find($id);

                if ($purchaseOrder && in_array($purchaseOrder->status, ['draft', 'cancelled'])) {
                    $purchaseOrder->delete();
                    $deleted++;
                } else {
                    $errors++;
                }
            }

            $message = "Deleted {$deleted} purchase order(s)";
            if ($errors > 0) {
                $message .= ". {$errors} purchase order(s) could not be deleted (must be draft or cancelled).";
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk PO deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete purchase orders'
            ], 500);
        }
    }
}
