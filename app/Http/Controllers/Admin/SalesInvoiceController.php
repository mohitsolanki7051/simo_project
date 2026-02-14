<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesPayment;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\SimpleProduct;
use App\Models\VariantProduct;
use App\Models\InvoiceSetting;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\Regex;
class SalesInvoiceController extends Controller
{
    /**
     * Display a listing of sales invoices.
     */
    public function index(Request $request)
    {
        $query = SalesInvoice::with(['customer', 'warehouse'])
            ->orderBy('invoice_date', 'desc');

        // Apply filters
        if ($request->filled('date')) {
            $query->whereDate('invoice_date', $request->date);
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $invoices = $query->paginate(20);

        // Get summary stats
        $totalSalesRaw = SalesInvoice::sum('grand_total');
        $totalPaidRaw = SalesInvoice::where('payment_status', 'paid')->sum('grand_total');
        $totalUnpaidRaw = SalesInvoice::whereIn('payment_status', ['unpaid', 'partial'])->sum('balance_amount');

        $totalSales = $this->decimalToFloat($totalSalesRaw);
        $totalPaid = $this->decimalToFloat($totalPaidRaw);
        $totalUnpaid = $this->decimalToFloat($totalUnpaidRaw);
                $customers = Customer::active()->get();

        return view('admin.sales.index', compact('invoices', 'totalSales', 'totalPaid', 'totalUnpaid', 'customers'));
    }

    /**
     * Show the form for creating a new sales invoice.
     */
    public function create()
    {
        // Generate invoice number
        $lastInvoice = SalesInvoice::orderBy('invoice_number', 'desc')->first();
        $invoiceNumber = $lastInvoice ? str_pad($lastInvoice->invoice_number + 1, 4, '0', STR_PAD_LEFT) : '0001';

        $customers = Customer::with(['billingAddresses', 'shippingAddresses'])->active()->get();
        $warehouses = Warehouse::active()->get();
        $mainWarehouse = Warehouse::main()->first();
        $invoiceSetting = InvoiceSetting::first();

        return view('admin.sales.create', compact('invoiceNumber', 'customers', 'warehouses', 'mainWarehouse', 'invoiceSetting'));
    }

public function store(Request $request)
{
    // Decode items JSON if needed
    if ($request->has('items') && is_string($request->items)) {
        $request->merge([
            'items' => json_decode($request->items, true)
        ]);
    }

    // Validation
    $request->validate([
        'customer_id' => 'required|exists:customers,_id',
        'warehouse_id' => 'required|exists:warehouses,_id',
        'invoice_date' => 'required|date',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required',
        'items.*.product_type' => 'required|in:simple,variant',
        'items.*.variant_id' => 'nullable',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.mrp_price' => 'required|numeric|min:0',
        'items.*.discount' => 'nullable|numeric|min:0|max:100',
        'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
        'extra_discount' => 'nullable|numeric|min:0',
        'extra_charge' => 'nullable|numeric|min:0',
        'amount_paid' => 'nullable|numeric|min:0',
    ]);

    try {


        /* ================= GET CUSTOMER ADDRESSES ================= */
        $customer = Customer::with('addresses')->findOrFail($request->customer_id);

        $billingAddress = $customer->addresses
            ->where('type', 'billing')
            ->where('is_default', true)
            ->first();

        $shippingAddress = $customer->addresses
            ->where('type', 'shipping')
            ->where('is_default', true)
            ->first();

        /* ================= TOTAL CALCULATION ================= */
        $totalMRP = 0;
        $totalDiscountAmount = 0;
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($request->items as $item) {
            $qty = (float) $item['quantity'];
            $mrpPrice = (float) $item['mrp_price'];
            $salePrice = (float) $item['price'];
            $discountPercent = (float) ($item['discount'] ?? 0);
            $taxPercent = (float) ($item['tax_percent'] ?? 0);

            // MRP Total
            $itemMRPTotal = $qty * $mrpPrice;
            $totalMRP += $itemMRPTotal;

            // Discount amount (on MRP)
            $itemDiscountAmount = ($itemMRPTotal * $discountPercent) / 100;
            $totalDiscountAmount += $itemDiscountAmount;

            // Sale price total
            $itemSaleTotal = $qty * $salePrice;
            $subtotal += $itemSaleTotal;

            // Tax (on sale price)
            $itemTax = ($itemSaleTotal * $taxPercent) / 100;
            $taxTotal += $itemTax;
        }

        // Extra discount
        $extraDiscount = (float) ($request->extra_discount ?? 0);

        // Extra charge
        $extraCharge = (float) ($request->extra_charge ?? 0);

        // Grand total = subtotal + tax - extra discount + extra charge
        $grandTotal = $subtotal + $taxTotal - $extraDiscount + $extraCharge;

        // Round off
        $roundOff = 0;
        if ($request->auto_round_off) {
            $rounded = round($grandTotal);
            $roundOff = $rounded - $grandTotal;
            $grandTotal = $rounded;
        }

        // Payment calculation
        $amountPaid = (float) ($request->amount_paid ?? 0);
        $balance = $grandTotal - $amountPaid;

        if ($amountPaid <= 0) {
            $paymentStatus = 'unpaid';
        } elseif ($balance <= 0.01) {
            $paymentStatus = 'paid';
            $balance = 0;
        } else {
            $paymentStatus = 'partial';
        }
        $subtotalWithTax = $subtotal + $taxTotal;

        /* ================= CREATE INVOICE ================= */
        $invoice = SalesInvoice::create([
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'customer_id' => $request->customer_id,
            'warehouse_id' => $request->warehouse_id,

            // Addresses
            'billing_address' => $billingAddress ? $billingAddress->full_address : $request->billing_address,
            'shipping_address' => $shippingAddress ? $shippingAddress->full_address : $request->shipping_address,

            // Invoice details
            'payment_terms' => $request->payment_terms,
            'due_date' => $request->due_date,
            'po_number' => $request->po_number,
            'vehicle_no' => $request->vehicle_no,
            'colours' => $request->colours,

            // Financial totals
            'total_mrp' => $totalMRP,
            'subtotal' => $subtotalWithTax,
            'discount_total' => $totalDiscountAmount,
            'tax_total' => $taxTotal,
            'extra_discount' => $extraDiscount,
            'extra_charge' => $extraCharge,
            'charge_name' => $request->charge_name,
            'round_off' => $roundOff,
            'grand_total' => $grandTotal,

            // Payment info
            'total_paid' => $amountPaid,
            'balance_amount' => $balance,
            'payment_status' => $paymentStatus,

            'status' => 'confirmed',
            'notes' => $request->notes,
            'created_by' => Auth::guard('admin')->id(),
        ]);

        /* ================= INVOICE ITEMS + STOCK ================= */
        foreach ($request->items as $item) {
            $qty = (float) $item['quantity'];
            $mrpPrice = (float) $item['mrp_price'];
            $salePrice = (float) $item['price'];
            $discountPercent = (float) ($item['discount'] ?? 0);
            $taxPercent = (float) ($item['tax_percent'] ?? 0);

            // Get product details
            if ($item['product_type'] === 'simple') {
                $product = SimpleProduct::find($item['product_id']);
                $productName = $product->name ?? 'Unknown Product';
                $sku = $product->sku_code ?? '';
                $barcode = $product->barcode ?? '';
                $unit = $product->unit ?? 'PCS';
                $hsnSac = $product->hsn_code ?? '';
                $variantName = null;
            } else {
                $product = VariantProduct::find($item['product_id']);
                $productName = $product->name ?? 'Unknown Product';
                $hsnSac = $product->hsn_code ?? '';

                // Get variant details
                $variants = $product->variants ?? [];
                $variant = collect($variants)->first(function($v) use ($item) {
                    $vId = isset($v['_id']) ? (string)$v['_id'] : null;
                    return $vId === $item['variant_id'];
                });

                $variantName = $variant['name'] ?? null;
                $sku = $variant['sku_code'] ?? '';
                $barcode = $variant['barcode'] ?? '';
                $unit = $variant['unit'] ?? 'PCS';
            }

            // Calculate item totals
            $itemMRPTotal = $qty * $mrpPrice;
            $itemDiscountAmount = ($itemMRPTotal * $discountPercent) / 100;
            $itemSaleTotal = $qty * $salePrice;
            $itemTax = ($itemSaleTotal * $taxPercent) / 100;
            $itemFinal = $itemSaleTotal + $itemTax;

            // Warranty calculation
            $warrantyType = $item['warranty_type'] ?? 'none';
            $warrantyPeriod = (int) ($item['warranty_period'] ?? 0);
            $warrantyStart = null;
            $warrantyEnd = null;

            if ($warrantyType !== 'none' && $warrantyPeriod > 0) {
                $warrantyStart = $request->invoice_date;
                $warrantyEnd = $this->calculateWarrantyEnd($warrantyStart, $warrantyType, $warrantyPeriod);
            }

            // Create item
            SalesInvoiceItem::create([
                'sales_invoice_id' => $invoice->_id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'product_name' => $productName,
                'variant_name' => $variantName,
                'sku' => $sku,
                'barcode' => $barcode,
                'hsn_sac' => $hsnSac,
                'quantity' => $qty,
                'unit' => $unit,
                'mrp_price' => $mrpPrice,
                'price' => $salePrice,
                'discount' => $discountPercent,
                'tax_percent' => $taxPercent,
                'tax_amount' => $itemTax,
                'total' => $itemFinal,
                'warranty_type' => $warrantyType,
                'warranty_period' => $warrantyPeriod,
                'warranty_start' => $warrantyStart,
                'warranty_end' => $warrantyEnd,
            ]);

            // Reduce stock
            $stock = WarehouseStock::where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $item['product_id'])
                ->where('product_type', $item['product_type'])
                ->when($item['product_type'] === 'variant', function ($q) use ($item) {
                    return $q->where('variant_id', $item['variant_id']);
                })
                ->first();

            if (!$stock || $stock->quantity < $qty) {
                throw new \Exception("Insufficient stock for {$productName}");
            }

            $stock->update([
                'quantity' => $stock->quantity - $qty
            ]);

            // Create warehouse movement
            WarehouseMovement::create([
                'warehouse_id' => $request->warehouse_id,
                'product_id' => $item['product_id'],
                'product_type' => $item['product_type'],
                'variant_id' => $item['variant_id'] ?? null,
                'type' => WarehouseMovement::TYPE_SALE,
                'quantity' => -$qty,
                'reference_id' => $invoice->_id,
                'remarks' => "Sales Invoice: {$invoice->invoice_number}",
            ]);
        }

        /* ================= PAYMENT ================= */
        if ($amountPaid > 0) {
            SalesPayment::create([
                'sales_invoice_id' => $invoice->_id,
                'amount' => $amountPaid,
                'payment_method' => $request->payment_method ?? 'cash',
                'payment_date' => now(),
                'status' => 'completed',
                'reference_no' => $request->reference_no,
                'notes' => "Payment for invoice {$invoice->invoice_number}"
            ]);
        }



        return response()->json([
            'success' => true,
            'invoice_id' => $invoice->_id,
            'message' => 'Invoice created successfully'
        ]);

    } catch (\Exception $e) {


        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

// Helper function for warranty calculation
private function calculateWarrantyEnd($start, $type, $period)
{
    if ($type === 'none' || $period <= 0) {
        return null;
    }

    $date = \Carbon\Carbon::parse($start);

    return $type === 'year'
        ? $date->addYears($period)
        : $date->addMonths($period);
}


    /**
     * Display the specified sales invoice.
     */
    public function show($id)
    {
        $invoice = SalesInvoice::with(['customer', 'warehouse', 'items', 'payments'])->findOrFail($id);
        return view('admin.sales.show', compact('invoice'));
    }


   public function getMainWarehouseProducts(Request $request)
{
    try {
        $mainWarehouse = Warehouse::main()->first();

        if (!$mainWarehouse) {
            return response()->json(['products' => []]);
        }



        /* ================= SIMPLE PRODUCTS ================= */

        $simpleStocks = WarehouseStock::where('warehouse_id', $mainWarehouse->_id)
            ->where('product_type', 'simple')
            ->where('quantity', '>', 0)
            ->pluck('product_id');

        $simpleProducts = SimpleProduct::whereIn('_id', $simpleStocks)

            ->get()
            ->map(function ($product) use ($mainWarehouse) {

                $stock = WarehouseStock::where('warehouse_id', $mainWarehouse->_id)
                    ->where('product_id', $product->_id)
                    ->where('product_type', 'simple')
                    ->first();

                return [
                    'id' => (string) $product->_id,
                    'name' => $product->name,
                    'type' => 'simple',
                    'sku' => $product->sku_code,
                    'mrp_price' => (float) ($product->mrp_price ?? 0),
                    'sale_price' => (float) ($product->sale_price ?? 0),
                    'current_stock' => $stock?->quantity ?? 0,
                    'unit' => $product->unit ?? 'PCS',
                    'hsn_code' => $product->hsn_code,
                    'warranty_type' => $product->warranty_unit ?? 'none',
                    'warranty_period' => (int) ($product->warranty_duration ?? 0),
                    'tax_percent' => (float) ($product->gst ?? 0),
                ];
            });

            $variantProducts = collect();

            $variantStocks = WarehouseStock::where('warehouse_id', $mainWarehouse->_id)
                ->where('product_type', 'variant')
                ->where('quantity', '>', 0)
                ->get();

            $productIds = $variantStocks
                ->pluck('product_id')
                ->unique();

            foreach ($productIds as $productId) {

                $product = VariantProduct::find($productId);

                if (!$product) continue;

                $rawVariants = $product->getRawOriginal('variants');

            if (is_string($rawVariants)) {
                $variants = json_decode($rawVariants, true);
            } else {
                $variants = $rawVariants;
            }

            if (!is_array($variants)) continue;

                if (!is_array($variants)) continue;

                foreach ($variants as $variant) {
            $variantId = null;

            if (isset($variant['_id'])) {
                if (is_array($variant['_id']) && isset($variant['_id']['$oid'])) {
                    $variantId = $variant['_id']['$oid'];
                } else {
                    $variantId = (string) $variant['_id'];
                }
            }
                $stock = $variantStocks->first(function ($s) use ($variantId, $productId) {
                return (string)$s->product_id === (string)$productId &&
                    (string)$s->variant_id === (string)$variantId;
            });

                if (!$stock) continue;




                $variantProducts->push([
                    'id' => (string) $product->_id,
                    'variant_id' => (string) $variantId,
                    'name' => $product->name . ' - ' . ($variant['name'] ?? ''),
                    'type' => 'variant',
                    'sku' => $variant['sku_code'] ?? '',
                    'mrp_price' => (float) ($variant['mrp_price'] ?? 0),
                    'sale_price' => (float) ($variant['sale_price'] ?? 0),
                    'current_stock' => (float) $stock->quantity,
                    'unit' => $variant['unit'] ?? 'PCS',
                    'hsn_code' => $product->hsn_code,
                    'warranty_type' => $product->warranty_unit ?? 'none',
                    'warranty_period' => (int) ($product->warranty_duration ?? 0),
                    'tax_percent' => (float) ($product->gst ?? 0),
                ]);
            }
        }


        return response()->json([
            'products' => $simpleProducts->merge($variantProducts)->values()
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => $e->getMessage()
        ], 500);
    }
}


    /**
     * Get customer details for invoice.
     */
  public function getCustomerDetails($id)
{
    $customer = Customer::with(['addresses'])->findOrFail($id);

    $billing = $customer->addresses
        ->where('type','billing')
        ->where('is_default', true)
        ->first();

    $shipping = $customer->addresses
        ->where('type','shipping')
        ->where('is_default', true)
        ->first();

    return response()->json([
        'success' => true,
        'customer' => [
            'id' => (string)$customer->_id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'gst_number' => $customer->gst_number,
            'customer_type' => $customer->customer_type,
            'company_name' => $customer->company_name,
            'billing_address' => $billing?->full_address ?? '',
            'shipping_address' => $shipping?->full_address ?? ''
        ]
    ]);
}

    /**
     * Delete a sales invoice.
     */
    public function destroy($id)
    {

        try {
            $invoice = SalesInvoice::with(['items'])->findOrFail($id);

            // Restore stock for each item
            foreach ($invoice->items as $item) {
                $warehouseStock = WarehouseStock::where('warehouse_id', $invoice->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->where('product_type', $item->variant_id ? 'variant' : 'simple')
                    ->when($item->variant_id, function ($query) use ($item) {
                        return $query->where('variant_id', $item->variant_id);
                    })
                    ->first();

                if ($warehouseStock) {
                    $warehouseStock->update([
                        'quantity' => $warehouseStock->quantity + $item->quantity
                    ]);

                    // Create warehouse movement for reversal
                    WarehouseMovement::create([
                        'warehouse_id' => $invoice->warehouse_id,
                        'product_id' => $item->product_id,
                        'product_type' => $item->variant_id ? 'variant' : 'simple',
                        'variant_id' => $item->variant_id ?? null,
                        'type' => 'adjustment',
                        'quantity' => $item->quantity,
                        'reference_id' => $invoice->_id,
                        'remarks' => "Sales Invoice Cancelled: {$invoice->invoice_number}"
                    ]);
                }
            }

            // Delete payments
            SalesPayment::where('sales_invoice_id', $id)->delete();

            // Delete invoice items
            SalesInvoiceItem::where('sales_invoice_id', $id)->delete();

            // Delete invoice
            $invoice->delete();


            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a payment for invoice.
     */
    public function createPayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'reference_no' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);


        try {
            $invoice = SalesInvoice::findOrFail($id);

            // Create payment
            $payment = SalesPayment::create([
                'sales_invoice_id' => $invoice->_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'status' => 'completed',
                'reference_no' => $request->reference_no,
                'notes' => $request->notes
            ]);

            // Update invoice payment status
            $totalPaid = $invoice->total_paid + $request->amount;
            $balanceAmount = $invoice->grand_total - $totalPaid;

            if ($balanceAmount <= 0) {
                $paymentStatus = 'paid';
            } else {
                $paymentStatus = 'partial';
            }

            $invoice->update([
                'total_paid' => $totalPaid,
                'balance_amount' => $balanceAmount,
                'payment_status' => $paymentStatus
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Payment added successfully',
                'payment' => $payment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add payment: ' . $e->getMessage()
            ], 500);
        }
    }


    public function storeCustomerAjax(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|digits:10|unique:customers,phone',
        'email' => 'nullable|email|unique:customers,email',
        'billing_pincode' => 'nullable|digits:6',
        'shipping_pincode' => 'nullable|digits:6',

        'pan_number' => [
            'nullable',
            'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
        ],

        'gst_number' => [
            'nullable',
            'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
        ],
    ]);


    $customer = Customer::create([
        'name' => $request->name,
        'phone' => $request->phone,
        'email' => $request->email,
        'customer_type' => $request->customer_type ?? 'individual',
        'company_name' => $request->company_name,
        'status' => 'active',
        'gst_number' => $request->gst_number,
        'pan_number' => $request->pan_number,
        'notes' => $request->notes
    ]);

    if ($request->billing_address) {
        CustomerAddress::create([
            'customer_id' => $customer->_id,
            'type' => 'billing',
            'address' => $request->billing_address,
            'city' => $request->billing_city,
            'state' => $request->billing_state,
            'pincode' => $request->billing_pincode,
            'country' => $request->billing_country ?? 'India',
            'is_default' => true
        ]);
    }

    if ($request->shipping_address) {
        CustomerAddress::create([
            'customer_id' => $customer->_id,
            'type' => 'shipping',
            'address' => $request->shipping_address,
            'city' => $request->shipping_city,
            'state' => $request->shipping_state,
            'pincode' => $request->shipping_pincode,
            'country' => $request->shipping_country ?? 'India',
            'is_default' => true
        ]);
    }

    return response()->json([
        'success' => true,
        'customer_id' => (string)$customer->_id
    ]);
}

public function getCustomersList(Request $request)
{
    $query = Customer::where('status', 'active');


    $customers = $query->orderBy('name')->limit(50)->get();

    return response()->json([
        'customers' => $customers->map(function ($customer) {
            return [
                'id' => (string)$customer->_id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'company_name' => $customer->company_name,
                'customer_type' => $customer->customer_type,
                'status' => $customer->status
            ];
        })
    ]);
}

private function decimalToFloat($value)
{
    if ($value instanceof Decimal128) {
        return (float) $value->__toString();
    }
    return (float) $value;
}
}
