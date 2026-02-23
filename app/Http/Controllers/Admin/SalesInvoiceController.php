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
use App\Models\Salesman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\Regex;

class SalesInvoiceController extends Controller
{
    /**
     * Generate invoice number with format: SIM/SI/24-25/000001
     */
    private function generateInvoiceNumber()
    {
        // Get current financial year
        $financialYear = $this->getFinancialYear();

        // Get the last invoice for this financial year
        $lastInvoice = SalesInvoice::where('invoice_number', 'regex', "/^SIM\/SI\/{$financialYear}\/\d+$/")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extract the last number from invoice number
            preg_match('/(\d+)$/', $lastInvoice->invoice_number, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "SIM/SI/{$financialYear}/{$newNumber}";
    }

    /**
     * Get current financial year (e.g., 24-25)
     */
    private function getFinancialYear()
    {
        $currentMonth = (int)date('m');
        $currentYear = (int)date('y');

        // Financial year in India starts from April
        if ($currentMonth >= 4) {
            // Apr to Dec: 24-25
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            // Jan to Mar: 23-24
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

public function index(Request $request)
{
    $query = SalesInvoice::with(['party', 'warehouse', 'salesman'])
        ->orderBy('invoice_date', 'desc');

    // ── Period filter
    if ($request->filled('period')) {
        $period = $request->period;
        if ($period === 'today') {
            $query->whereDate('invoice_date', today());
        } elseif ($period === 'custom') {
            if ($request->filled('date_from'))
                $query->whereDate('invoice_date', '>=', $request->date_from);
            if ($request->filled('date_to'))
                $query->whereDate('invoice_date', '<=', $request->date_to);
        } elseif (is_numeric($period)) {
            $query->whereDate('invoice_date', '>=', now()->subDays((int)$period)->toDateString());
        }
    }

    // ── Single date filter
    if ($request->filled('date')) {
        $query->whereDate('invoice_date', $request->date);
    }

    if ($request->filled('invoice_number')) {
        $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
    }

    if ($request->filled('party_id')) {
        $query->where('party_id', $request->party_id);
    }

    if ($request->filled('status')) {
        $query->where('payment_status', $request->status);
    }

    $invoices = $query->paginate(20)->withQueryString();

    // ── Summary stats (global, not filtered)
    $totalSalesRaw  = SalesInvoice::sum('grand_total');
    $totalPaidRaw   = SalesInvoice::where('payment_status', 'paid')->sum('grand_total');
    $totalUnpaidRaw = SalesInvoice::whereIn('payment_status', ['unpaid', 'partial'])->sum('balance_amount');

    $totalSales  = $this->decimalToFloat($totalSalesRaw);
    $totalPaid   = $this->decimalToFloat($totalPaidRaw);
    $totalUnpaid = $this->decimalToFloat($totalUnpaidRaw);

    $customers = Customer::where('status', 'active')->orderBy('name')->get();

    return view('admin.sales.index', compact(
        'invoices', 'totalSales', 'totalPaid', 'totalUnpaid', 'customers'
    ));
}

    /**
     * Show the form for creating a new sales invoice.
     */
    public function create()
    {
        // Generate invoice number with prefix
        $invoiceNumber = $this->generateInvoiceNumber();

        // Get all active parties (customers, dealers, distributors)
        $parties = Customer::with(['addresses' => function($query) {
                $query->where('is_default', true);
            }])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get active salesmen for dropdown
        $salesmen = Salesman::where('status', 'active')
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::active()->get();
        $mainWarehouse = Warehouse::main()->first();
        $invoiceSetting = InvoiceSetting::first();

        return view('admin.sales.create', compact('invoiceNumber', 'parties', 'salesmen', 'warehouses', 'mainWarehouse', 'invoiceSetting'));
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
        'party_id' => 'required|exists:customers,_id',
        'party_type' => 'required|in:customer,dealer,distributor',
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
        'extra_discount_type' => 'nullable|in:amount,percent',
        'extra_charge' => 'nullable|numeric|min:0',
        'amount_paid' => 'nullable|numeric|min:0',
    ]);

    try {
        /* ================= GET PARTY ADDRESSES ================= */
        $party = Customer::with(['addresses', 'salesman'])->findOrFail($request->party_id);

        $billingAddress = $party->addresses
            ->where('type', 'billing')
            ->where('is_default', true)
            ->first();

        $shippingAddress = $party->addresses
            ->where('type', 'shipping')
            ->where('is_default', true)
            ->first();

        /* ================= GET WAREHOUSE STATE ================= */
        $warehouse = Warehouse::find($request->warehouse_id);
        $warehouseState = $warehouse->state ?? '';

        /* ================= GET PARTY STATE ================= */
        $partyState = '';
        if ($billingAddress) {
            $partyState = $billingAddress->state ?? '';
        }

        /* ================= DETERMINE TAX TYPE ================= */
        $isIntraState = (!empty($warehouseState) && !empty($partyState) && $warehouseState === $partyState);
        $taxType = $isIntraState ? 'intra' : 'inter';

        /* ================= TOTAL CALCULATION WITH GST SPLIT ================= */
        $totalMRP = 0;
        $totalDiscountAmount = 0;
        $subtotal = 0; // This is WITHOUT tax
        $taxTotal = 0;
        $cgstTotal = 0;
        $sgstTotal = 0;
        $igstTotal = 0;
        $itemsData = [];

        foreach ($request->items as $item) {
            $qty = (float) $item['quantity'];
            $mrpPrice = (float) $item['mrp_price'];
            $salePrice = (float) $item['price'];
            $discountPercent = (float) ($item['discount'] ?? 0);
            $taxPercent = (float) ($item['tax_percent'] ?? 0);

            // MRP Total
            $itemMRPTotal = $qty * $mrpPrice;
            $totalMRP += $itemMRPTotal;

            // Discount amount - difference between MRP and Sale Price
            $itemDiscountAmount = ($mrpPrice - $salePrice) * $qty;
            $totalDiscountAmount += $itemDiscountAmount;

            // Sale price total (WITHOUT TAX) - This is subtotal
            $itemSaleTotal = $qty * $salePrice;
            $subtotal += $itemSaleTotal;

            // Tax calculation based on tax type
            $itemTax = ($itemSaleTotal * $taxPercent) / 100;
            $taxTotal += $itemTax;

            if ($isIntraState) {
                // Split tax equally into CGST and SGST
                $halfTax = $itemTax / 2;
                $cgstTotal += $halfTax;
                $sgstTotal += $halfTax;
                $item['cgst_amount'] = $halfTax;
                $item['sgst_amount'] = $halfTax;
                $item['igst_amount'] = 0;
            } else {
                // Full tax as IGST
                $igstTotal += $itemTax;
                $item['igst_amount'] = $itemTax;
                $item['cgst_amount'] = 0;
                $item['sgst_amount'] = 0;
            }

            $itemsData[] = $item;
        }

        /* ================= EXTRA DISCOUNT HANDLING - APPLIED ON SUBTOTAL ONLY ================= */
        $extraDiscountValue = 0;
        $extraDiscountAmount = 0;
        $extraDiscountType = $request->extra_discount_type ?? 'amount';

        if ($request->filled('extra_discount') && (float)$request->extra_discount > 0) {
            $extraDiscountValue = (float)$request->extra_discount;

            if ($extraDiscountType === 'percent') {
                // Calculate discount on SUBTOTAL only, NOT on tax
                $extraDiscountAmount = ($subtotal * $extraDiscountValue) / 100;
            } else {
                // Fixed amount discount
                $extraDiscountAmount = $extraDiscountValue;
            }
        }

        /* ================= EXTRA CHARGE ================= */
        $extraCharge = (float) ($request->extra_charge ?? 0);

        /* ================= GRAND TOTAL CALCULATION ================= */
        // Formula: (Subtotal - Extra Discount) + Tax + Extra Charge
        $afterDiscountSubtotal = $subtotal - $extraDiscountAmount;
        $grandTotal = $afterDiscountSubtotal + $taxTotal + $extraCharge;

        // Round off
        $roundOff = 0;
        if ($request->auto_round_off) {
            $rounded = round($grandTotal);
            $roundOff = $rounded - $grandTotal;
            $grandTotal = $rounded;
        }

        /* ================= PAYMENT CALCULATION ================= */
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

        // Generate invoice number if not provided
        $invoiceNumber = $request->invoice_number ?? $this->generateInvoiceNumber();

        /* ================= CREATE INVOICE ================= */
        $invoice = SalesInvoice::create([
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $request->invoice_date,
            'party_id' => $request->party_id,
            'salesman_id' => $party->salesman_id,
            'warehouse_id' => $request->warehouse_id,

            // Addresses
            'billing_address' => $billingAddress ? $billingAddress->full_address : $request->billing_address,
            'shipping_address' => $shippingAddress ? $shippingAddress->full_address : $request->shipping_address,

            // Invoice details
            'payment_terms' => $request->payment_terms,
            'due_date' => $request->due_date,
            'po_number' => $request->po_number,

            // Financial totals
            'total_mrp' => round($totalMRP, 2),
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($totalDiscountAmount, 2),
            'tax_total' => round($taxTotal, 2),
            'cgst_total' => round($cgstTotal, 2),
            'sgst_total' => round($sgstTotal, 2),
            'igst_total' => round($igstTotal, 2),
            'tax_type' => $taxType,

            // Extra fields
            'extra_discount' => round($extraDiscountValue, 2),
            'extra_discount_type' => $extraDiscountType,
            'extra_charge' => round($extraCharge, 2),
            'charge_name' => $request->charge_name,
            'round_off' => round($roundOff, 2),
            'grand_total' => round($grandTotal, 2),

            // Payment info
            'total_paid' => round($amountPaid, 2),
            'balance_amount' => round($balance, 2),
            'payment_status' => $paymentStatus,

            'status' => 'confirmed',
            'notes' => $request->notes,
            'created_by' => Auth::guard('admin')->id(),
        ]);

        /* ================= INVOICE ITEMS + STOCK ================= */
        foreach ($request->items as $index => $item) {
            $qty = (float) $item['quantity'];
            $mrpPrice = (float) $item['mrp_price'];
            $salePrice = (float) $item['price'];
            $discountPercent = (float) ($item['discount'] ?? 0);
            $taxPercent = (float) ($item['tax_percent'] ?? 0);

            // Get tax split amounts (calculated earlier)
            $cgstAmount = $itemsData[$index]['cgst_amount'] ?? 0;
            $sgstAmount = $itemsData[$index]['sgst_amount'] ?? 0;
            $igstAmount = $itemsData[$index]['igst_amount'] ?? 0;

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
            $itemDiscountAmount = ($mrpPrice - $salePrice) * $qty;
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

            // Create item with GST split
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
                'mrp_price' => round($mrpPrice, 2),
                'price' => round($salePrice, 2),
                'discount' => round($discountPercent, 2),
                'tax_percent' => round($taxPercent, 2),
                'tax_amount' => round($itemTax, 2),
                'cgst_amount' => round($cgstAmount, 2),
                'sgst_amount' => round($sgstAmount, 2),
                'igst_amount' => round($igstAmount, 2),
                'total' => round($itemFinal, 2),
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
                'amount' => round($amountPaid, 2),
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

    /**
     * Display the specified sales invoice.
     */
    public function show($id)
    {
        $invoice = SalesInvoice::with(['party', 'warehouse', 'salesman', 'items', 'payments'])->findOrFail($id);
        return view('admin.sales.show', compact('invoice'));
    }

    /**
     * Get products from main warehouse
     */
    /**
 * Get products from main warehouse
 */
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
                    'dealer_price' => (float) ($product->dealer_price ?? 0), // Added
                    'distributor_price' => (float) ($product->distributor_price ?? 0), // Added
                    'current_stock' => $stock?->quantity ?? 0,
                    'unit' => $product->unit ?? 'PCS',
                    'hsn_code' => $product->hsn_code,
                    'warranty_type' => $product->warranty_unit ?? 'none',
                    'warranty_period' => (int) ($product->warranty_duration ?? 0),
                    'tax_percent' => (float) ($product->gst ?? 0),
                ];
            });

        /* ================= VARIANT PRODUCTS ================= */
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
                    'dealer_price' => (float) ($variant['dealer_price'] ?? 0), // Added
                    'distributor_price' => (float) ($variant['distributor_price'] ?? 0), // Added
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
     * Get parties list for dropdown
     */
    public function getPartiesList(Request $request)
    {
        $query = Customer::where('status', 'active');

        // Filter by party type if provided
        if ($request->filled('party_type') && $request->party_type !== 'all') {
            $query->where('party_type', $request->party_type);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $parties = $query->orderBy('name')->limit(50)->get();

        return response()->json([
            'parties' => $parties->map(function ($party) {
                return [
                    'id' => (string)$party->_id,
                    'name' => $party->name,
                    'phone' => $party->phone,
                    'email' => $party->email,
                    'party_type' => $party->party_type,
                    'party_type_text' => ucfirst($party->party_type),
                    'status' => $party->status
                ];
            })
        ]);
    }

/**
 * Get party details by ID
 */
public function getPartyDetails($id)
{
    $party = Customer::with(['addresses', 'salesman'])->findOrFail($id);

    $billing = $party->addresses
        ->where('type','billing')
        ->where('is_default', true)
        ->first();

    $shipping = $party->addresses
        ->where('type','shipping')
        ->where('is_default', true)
        ->first();

    return response()->json([
        'success' => true,
        'party' => [
            'id' => (string)$party->_id,
            'name' => $party->name,
            'phone' => $party->phone,
            'email' => $party->email,
            'party_type' => $party->party_type,
            'party_type_text' => ucfirst($party->party_type),
            'opening_balance' => $party->opening_balance,
            'credit_limit' => $party->credit_limit,
            'salesman_id' => $party->salesman_id,
            'salesman_name' => $party->salesman ? $party->salesman->name : null,
            'billing_address' => $billing?->full_address ?? '',
            'shipping_address' => $shipping?->full_address ?? '',
            'billing_state' => $billing?->state ?? '', // ADD THIS LINE
        ]
    ]);
}

    /**
     * Create party via AJAX
     */
    public function storePartyAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|digits:10|unique:customers,phone',
            'email' => 'nullable|email|unique:customers,email',
            'party_type' => 'required|in:customer,dealer,distributor',
            'salesman_id' => 'nullable|exists:salesmen,_id',
            'opening_balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
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

        $party = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'party_type' => $request->party_type,
            'salesman_id' => $request->salesman_id,
            'opening_balance' => $request->opening_balance ?? 0,
            'credit_limit' => $request->credit_limit,
            'gst_number' => strtoupper($request->gst_number),
            'pan_number' => strtoupper($request->pan_number),
            'status' => 'active',
            'notes' => $request->notes
        ]);

        if ($request->billing_address) {
            CustomerAddress::create([
                'customer_id' => $party->_id,
                'type' => 'billing',
                'address' => $request->billing_address,
                'city' => $request->billing_city,
                'state' => $request->billing_state,
                'pincode' => $request->billing_pincode,
                'country' => $request->billing_country ?? 'India',
                'is_default' => true
            ]);
        }

        if ($request->shipping_address && !$request->same_billing_shipping) {
            CustomerAddress::create([
                'customer_id' => $party->_id,
                'type' => 'shipping',
                'address' => $request->shipping_address,
                'city' => $request->shipping_city,
                'state' => $request->shipping_state,
                'pincode' => $request->shipping_pincode,
                'country' => $request->shipping_country ?? 'India',
                'is_default' => true
            ]);
        } elseif ($request->same_billing_shipping && $request->billing_address) {
            // Copy billing to shipping
            CustomerAddress::create([
                'customer_id' => $party->_id,
                'type' => 'shipping',
                'address' => $request->billing_address,
                'city' => $request->billing_city,
                'state' => $request->billing_state,
                'pincode' => $request->billing_pincode,
                'country' => $request->billing_country ?? 'India',
                'is_default' => true
            ]);
        }

        return response()->json([
            'success' => true,
            'party_id' => (string)$party->_id,
            'party_type' => $party->party_type
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

    /**
     * Helper function for warranty calculation
     */
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
     * Helper function to convert Decimal128 to float
     */
    private function decimalToFloat($value)
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
