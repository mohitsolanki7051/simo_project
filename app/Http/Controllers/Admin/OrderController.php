<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index()
    {
        $orders = Order::orderBy('created_at', 'desc')->get();
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order
     */
    public function create()
    {
        return view('admin.orders.create');
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            // Customer Information
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_age' => 'nullable|integer|min:1|max:150',

            // Billing Address
            'billing_address_line1' => 'required|string|max:255',
            'billing_address_line2' => 'nullable|string|max:255',
            'billing_city' => 'required|string|max:100',
            'billing_state' => 'required|string|max:100',
            'billing_pincode' => 'required|string|max:10',
            'billing_country' => 'required|string|max:100',

            // Shipping Address
            'same_as_billing' => 'nullable|boolean',
            'shipping_address_line1' => 'required_if:same_as_billing,0|nullable|string|max:255',
            'shipping_address_line2' => 'nullable|string|max:255',
            'shipping_city' => 'required_if:same_as_billing,0|nullable|string|max:100',
            'shipping_state' => 'required_if:same_as_billing,0|nullable|string|max:100',
            'shipping_pincode' => 'required_if:same_as_billing,0|nullable|string|max:10',
            'shipping_country' => 'required_if:same_as_billing,0|nullable|string|max:100',

            // Order Information
            'order_date' => 'required|date',
            'order_status' => 'required|in:pending,processing,completed,cancelled,on_hold',
            'payment_status' => 'required|in:paid,unpaid,partially_paid,refunded',
            'payment_method' => 'required|string|max:100',

            // Items
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string',
            'items.*.product_name' => 'required|string',
            'items.*.sku' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.gst' => 'required|numeric|min:0|max:100',

            // Pricing
            'shipping_charges' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',

            // Notes
            'notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ]);

        try {
            // Generate order number
            $orderNumber = Order::generateOrderNumber();

            // Create order
            $order = Order::create([
                // Order Information
                'order_number' => $orderNumber,
                'order_date' => $request->order_date,
                'order_status' => $request->order_status,
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method,

                // Customer Information
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_age' => $request->customer_age,

                // Billing Address
                'billing_address_line1' => $request->billing_address_line1,
                'billing_address_line2' => $request->billing_address_line2,
                'billing_city' => $request->billing_city,
                'billing_state' => $request->billing_state,
                'billing_pincode' => $request->billing_pincode,
                'billing_country' => $request->billing_country,

                // Shipping Address
                'same_as_billing' => $request->has('same_as_billing') ? true : false,
                'shipping_address_line1' => $request->same_as_billing ? $request->billing_address_line1 : $request->shipping_address_line1,
                'shipping_address_line2' => $request->same_as_billing ? $request->billing_address_line2 : $request->shipping_address_line2,
                'shipping_city' => $request->same_as_billing ? $request->billing_city : $request->shipping_city,
                'shipping_state' => $request->same_as_billing ? $request->billing_state : $request->shipping_state,
                'shipping_pincode' => $request->same_as_billing ? $request->billing_pincode : $request->shipping_pincode,
                'shipping_country' => $request->same_as_billing ? $request->billing_country : $request->shipping_country,

                // Items
                'items' => $request->items,

                // Pricing
                'shipping_charges' => $request->shipping_charges ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,

                // Notes
                'notes' => $request->notes,
                'internal_notes' => $request->internal_notes,
            ]);

            // Calculate totals
            $order->calculateTotals();
            $order->save();

            // Update product stock
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->updateStock($item['quantity'], 'subtract');
                }
            }

            return redirect()->route('admin.orders.index')
                ->with('success', 'Order created successfully! Order Number: ' . $orderNumber);

        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to create order. Please try again.');
        }
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        $order = Order::findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the order
     */
    public function edit($id)
    {
        $order = Order::findOrFail($id);
        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Validation
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_age' => 'nullable|integer|min:1|max:150',
            'billing_address_line1' => 'required|string|max:255',
            'billing_address_line2' => 'nullable|string|max:255',
            'billing_city' => 'required|string|max:100',
            'billing_state' => 'required|string|max:100',
            'billing_pincode' => 'required|string|max:10',
            'billing_country' => 'required|string|max:100',
            'same_as_billing' => 'nullable|boolean',
            'shipping_address_line1' => 'required_if:same_as_billing,0|nullable|string|max:255',
            'shipping_address_line2' => 'nullable|string|max:255',
            'shipping_city' => 'required_if:same_as_billing,0|nullable|string|max:100',
            'shipping_state' => 'required_if:same_as_billing,0|nullable|string|max:100',
            'shipping_pincode' => 'required_if:same_as_billing,0|nullable|string|max:10',
            'shipping_country' => 'required_if:same_as_billing,0|nullable|string|max:100',
            'order_date' => 'required|date',
            'order_status' => 'required|in:pending,processing,completed,cancelled,on_hold',
            'payment_status' => 'required|in:paid,unpaid,partially_paid,refunded',
            'payment_method' => 'required|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string',
            'items.*.product_name' => 'required|string',
            'items.*.sku' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.gst' => 'required|numeric|min:0|max:100',
            'shipping_charges' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ]);

        try {
            // Restore old stock
            foreach ($order->items as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->updateStock($item['quantity'], 'add');
                }
            }

            // Update order
            $order->update([
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_age' => $request->customer_age,
                'billing_address_line1' => $request->billing_address_line1,
                'billing_address_line2' => $request->billing_address_line2,
                'billing_city' => $request->billing_city,
                'billing_state' => $request->billing_state,
                'billing_pincode' => $request->billing_pincode,
                'billing_country' => $request->billing_country,
                'same_as_billing' => $request->has('same_as_billing') ? true : false,
                'shipping_address_line1' => $request->same_as_billing ? $request->billing_address_line1 : $request->shipping_address_line1,
                'shipping_address_line2' => $request->same_as_billing ? $request->billing_address_line2 : $request->shipping_address_line2,
                'shipping_city' => $request->same_as_billing ? $request->billing_city : $request->shipping_city,
                'shipping_state' => $request->same_as_billing ? $request->billing_state : $request->shipping_state,
                'shipping_pincode' => $request->same_as_billing ? $request->billing_pincode : $request->shipping_pincode,
                'shipping_country' => $request->same_as_billing ? $request->billing_country : $request->shipping_country,
                'order_date' => $request->order_date,
                'order_status' => $request->order_status,
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method,
                'items' => $request->items,
                'shipping_charges' => $request->shipping_charges ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'notes' => $request->notes,
                'internal_notes' => $request->internal_notes,
            ]);

            // Recalculate totals
            $order->calculateTotals();
            $order->save();

            // Update new stock
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->updateStock($item['quantity'], 'subtract');
                }
            }

            return redirect()->route('admin.orders.index')
                ->with('success', 'Order updated successfully!');

        } catch (\Exception $e) {
            Log::error('Order update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update order. Please try again.');
        }
    }

    /**
     * Remove the specified order
     */
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);

            // Restore stock
            if ($order->order_status !== 'cancelled') {
                foreach ($order->items as $item) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->updateStock($item['quantity'], 'add');
                    }
                }
            }

            $order->delete();

            return redirect()->route('admin.orders.index')
                ->with('success', 'Order deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Order deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete order. Please try again.');
        }
    }

    /**
     * Bulk delete orders
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'order_ids' => 'required|array'
            ]);

            $orders = Order::whereIn('id', $request->order_ids)->get();

            foreach ($orders as $order) {
                // Restore stock
                if ($order->order_status !== 'cancelled') {
                    foreach ($order->items as $item) {
                        $product = Product::find($item['product_id']);
                        if ($product) {
                            $product->updateStock($item['quantity'], 'add');
                        }
                    }
                }
                $order->delete();
            }

            return response()->json([
                'success' => true,
                'message' => count($request->order_ids) . ' order(s) deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk order deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update order status
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                'order_ids' => 'required|array',
                'status' => 'required|in:pending,processing,completed,cancelled,on_hold'
            ]);

            Order::whereIn('id', $request->order_ids)
                ->update(['order_status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => count($request->order_ids) . ' order(s) status updated to ' . $request->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update orders status'
            ], 500);
        }
    }

    /**
     * Search products for order (AJAX)
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('query', '');

        $products = Product::where('status', 'active')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku_code', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    /**
     * Get product details (AJAX)
     */
    public function getProduct(Request $request)
    {
        try {
            $product = Product::findOrFail($request->product_id);
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * Generate and display invoice
     */
    public function generateInvoice($id)
    {
        $order = Order::findOrFail($id);
        return view('admin.orders.invoice', compact('order'));
    }

    /**
     * Download invoice as PDF
     */
    public function downloadInvoice($id)
    {
        $order = Order::findOrFail($id);

        $pdf = Pdf::loadView('admin.orders.invoice-pdf', compact('order'));

        return $pdf->download('invoice-' . $order->order_number . '.pdf');
    }
}
