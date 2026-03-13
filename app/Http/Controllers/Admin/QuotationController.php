<?php
// app/Http/Controllers/Admin/QuotationController.php - Store method update

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\SimpleProduct;
use App\Models\VariantProduct;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\InvoiceSetting;
use App\Models\Salesman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\Decimal128;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    /**
     * Generate quotation number with format: SIM/QT/24-25/000001
     */
    private function generateQuotationNumber()
    {
        $financialYear = $this->getFinancialYear();

        $lastQuotation = Quotation::where('quotation_number', 'regex', "/^SIM\/QT\/{$financialYear}\/\d+$/")
            ->orderBy('quotation_number', 'desc')
            ->first();

        if ($lastQuotation) {
            preg_match('/(\d+)$/', $lastQuotation->quotation_number, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "SIM/QT/{$financialYear}/{$newNumber}";
    }

    /**
     * Get current financial year (e.g., 24-25)
     */
    private function getFinancialYear()
    {
        $currentMonth = (int)date('m');
        $currentYear = (int)date('y');

        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

    /**
     * Helper function to convert Decimal128 to float
     */
    private function convertDecimalToFloat($value)
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }
        return (float) $value;
    }

    /**
     * Display a listing of quotations.
     */
    public function index(Request $request)
    {
        $query = Quotation::with(['party', 'warehouse'])
            ->orderBy('created_at', 'desc');

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('quotation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('quotation_date', '<=', $request->date_to);
        }

        // Quotation number search
        if ($request->filled('quotation_number')) {
            $query->where('quotation_number', 'like', '%' . $request->quotation_number . '%');
        }

        // Party filter
        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        // Quotation Type filter
        if ($request->filled('invoice_type') && $request->invoice_type != '') {
            $query->where('invoice_type', $request->invoice_type);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Warehouse filter
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // 👇 PAGINATED QUOTATIONS - FILTERS APPLIED
        $quotations = $query->paginate(20)->withQueryString();

        /* ================= STATS QUERY - ALL FILTERS APPLY ================= */
        $statsQuery = Quotation::query(); // 👈 NAYA QUERY FOR STATS

        // Date filter on stats
        if ($request->filled('date_from')) {
            $statsQuery->whereDate('quotation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $statsQuery->whereDate('quotation_date', '<=', $request->date_to);
        }

        // Quotation number filter on stats
        if ($request->filled('quotation_number')) {
            $statsQuery->where('quotation_number', 'like', '%' . $request->quotation_number . '%');
        }

        // Party filter on stats
        if ($request->filled('party_id')) {
            $statsQuery->where('party_id', $request->party_id);
        }

        // Quotation Type filter on stats
        if ($request->filled('invoice_type') && $request->invoice_type != '') {
            $statsQuery->where('invoice_type', $request->invoice_type);
        }

        // Status filter on stats
        if ($request->filled('status')) {
            $statsQuery->where('status', $request->status);
        }

        // Warehouse filter on stats
        if ($request->filled('warehouse_id')) {
            $statsQuery->where('warehouse_id', $request->warehouse_id);
        }

        // 👇 CALCULATE STATS FROM FILTERED QUERY
        $totalQuotations = $statsQuery->count();  // Total quotations after filters
        $totalAmountRaw = $statsQuery->sum('grand_total');
        $totalAmount = $this->convertDecimalToFloat($totalAmountRaw);

        // Draft count after filters
        $draftCount = (clone $statsQuery)->where('status', 'draft')->count();

        // Sent count after filters
        $sentCount = (clone $statsQuery)->where('status', 'sent')->count();

        $customers = Customer::where('status', 'active')->orderBy('name')->get();

        return view('admin.quotations.index', compact(
            'quotations', 'totalQuotations', 'totalAmount', 'draftCount', 'sentCount', 'customers'
        ));
    }

    /**
     * Show the form for creating a new quotation.
     */
    public function create()
    {
        $quotationNumber = $this->generateQuotationNumber();

        $warehouses = Warehouse::active()->get();
        $mainWarehouse = Warehouse::main()->first();
        $invoiceSetting = InvoiceSetting::first();

        return view('admin.quotations.create', compact('quotationNumber', 'warehouses', 'mainWarehouse', 'invoiceSetting'));
    }

    /**
     * Store a newly created quotation.
     */
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
            'invoice_type' => 'required|in:gst,cash',
            'quotation_date' => 'required|date',
            'valid_till' => 'nullable|date|after_or_equal:quotation_date',
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
            $subtotal = 0; // WITHOUT tax
            $taxTotal = 0;
            $cgstTotal = 0;
            $sgstTotal = 0;
            $igstTotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $mrpPrice = (float) $item['mrp_price'];
                $salePrice = (float) $item['price'];
                $taxPercent = $request->invoice_type === 'gst' ? (float) ($item['tax_percent'] ?? 0) : 0;

                // MRP Total
                $itemMRPTotal = $qty * $mrpPrice;
                $totalMRP += $itemMRPTotal;

                // Discount amount
                $itemDiscountAmount = ($mrpPrice - $salePrice) * $qty;
                $totalDiscountAmount += $itemDiscountAmount;

                // Sale price total (WITHOUT TAX)
                $itemSaleTotal = $qty * $salePrice;
                $subtotal += $itemSaleTotal;

                // Tax calculation for GST invoices only
                if ($request->invoice_type === 'gst' && $taxPercent > 0) {
                    $itemTax = ($itemSaleTotal * $taxPercent) / 100;
                    $taxTotal += $itemTax;

                    if ($isIntraState) {
                        $halfTax = $itemTax / 2;
                        $cgstTotal += $halfTax;
                        $sgstTotal += $halfTax;
                        $item['cgst_amount'] = $halfTax;
                        $item['sgst_amount'] = $halfTax;
                        $item['igst_amount'] = 0;
                    } else {
                        $igstTotal += $itemTax;
                        $item['igst_amount'] = $itemTax;
                        $item['cgst_amount'] = 0;
                        $item['sgst_amount'] = 0;
                    }
                } else {
                    $item['cgst_amount'] = 0;
                    $item['sgst_amount'] = 0;
                    $item['igst_amount'] = 0;
                }

                $itemsData[] = $item;
            }

            /* ================= EXTRA DISCOUNT HANDLING ================= */
            $extraDiscountValue = 0;
            $extraDiscountAmount = 0;
            $extraDiscountType = $request->extra_discount_type ?? 'amount';

            if ($request->filled('extra_discount') && (float)$request->extra_discount > 0) {
                $extraDiscountValue = (float)$request->extra_discount;

                if ($extraDiscountType === 'percent') {
                    $extraDiscountAmount = ($subtotal * $extraDiscountValue) / 100;
                } else {
                    $extraDiscountAmount = $extraDiscountValue;
                }
            }

            /* ================= EXTRA CHARGE ================= */
            $extraCharge = (float) ($request->extra_charge ?? 0);

            /* ================= GRAND TOTAL CALCULATION ================= */
            $afterDiscountSubtotal = $subtotal - $extraDiscountAmount;
            $grandTotal = $afterDiscountSubtotal + ($request->invoice_type === 'gst' ? $taxTotal : 0) + $extraCharge;

            // Round off
            $roundOff = 0;
            if ($request->auto_round_off) {
                $rounded = round($grandTotal);
                $roundOff = $rounded - $grandTotal;
                $grandTotal = $rounded;
            }

            /* ================= CREATE QUOTATION ================= */
            $quotation = Quotation::create([
                'quotation_number' => $request->quotation_number ?? $this->generateQuotationNumber(),
                'public_token'   => \Illuminate\Support\Str::random(40),
                'invoice_type' => $request->invoice_type,
                'quotation_date' => $request->quotation_date,
                'valid_till' => $request->valid_till,
                'party_id' => $request->party_id,
                'salesman_id' => $party->salesman_id,
                'warehouse_id' => $request->warehouse_id,

                // Addresses
                'billing_address' => $billingAddress ? $billingAddress->full_address : $request->billing_address,
                'shipping_address' => $shippingAddress ? $shippingAddress->full_address : $request->shipping_address,

                // Financial totals
                'total_mrp' => round($totalMRP, 2),
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($totalDiscountAmount, 2),

                // Tax totals (for GST invoices)
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

                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => Auth::guard('admin')->id(),
            ]);

            /* ================= CREATE QUOTATION ITEMS ================= */
            foreach ($request->items as $index => $item) {
                $qty = (float) $item['quantity'];
                $mrpPrice = (float) $item['mrp_price'];
                $salePrice = (float) $item['price'];
                $discountPercent = (float) ($item['discount'] ?? 0);
                $taxPercent = $request->invoice_type === 'gst' ? (float) ($item['tax_percent'] ?? 0) : 0;

                // Get tax split amounts
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
                    $hsnSac = $request->invoice_type === 'gst' ? ($product->hsn_code ?? '') : '';
                    $variantName = null;
                } else {
                    $product = VariantProduct::find($item['product_id']);
                    $productName = $product->name ?? 'Unknown Product';
                    $hsnSac = $request->invoice_type === 'gst' ? ($product->hsn_code ?? '') : '';

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
                $itemSaleTotal = $qty * $salePrice;
                $itemTax = ($request->invoice_type === 'gst') ? ($itemSaleTotal * $taxPercent) / 100 : 0;
                $itemTotal = $itemSaleTotal + $itemTax;

                // Warranty calculation
                $warrantyType = $item['warranty_type'] ?? 'none';
                $warrantyPeriod = (int) ($item['warranty_period'] ?? 0);
                $warrantyStart = null;
                $warrantyEnd = null;

                if ($warrantyType !== 'none' && $warrantyPeriod > 0) {
                    $warrantyStart = $request->quotation_date;
                    $warrantyEnd = $this->calculateWarrantyEnd($warrantyStart, $warrantyType, $warrantyPeriod);
                }

                // Create item
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'product_type' => $item['product_type'],
                    'product_name' => $productName,
                    'variant_name' => $variantName,
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'hsn_sac' => $hsnSac,
                    'unit' => $unit,
                    'quantity' => $qty,
                    'mrp_price' => round($mrpPrice, 2),
                    'price' => round($salePrice, 2),
                    'discount' => round($discountPercent, 2),
                    'tax_percent' => round($taxPercent, 2),
                    'tax_amount' => round($itemTax, 2),
                    'cgst_amount' => round($cgstAmount, 2),
                    'sgst_amount' => round($sgstAmount, 2),
                    'igst_amount' => round($igstAmount, 2),
                    'total' => round($itemTotal, 2),
                    'warranty_type' => $warrantyType,
                    'warranty_period' => $warrantyPeriod,
                    'warranty_start' => $warrantyStart,
                    'warranty_end' => $warrantyEnd,
                    'party_type' => $request->party_type,
                ]);
            }

            return response()->json([
                'success' => true,
                'quotation_id' => $quotation->id,
                'message' => 'Quotation created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
     * Display the specified quotation.
     */
    public function show($id)
    {
        $quotation = Quotation::with(['party', 'items', 'warehouse'])->findOrFail($id);
        return view('admin.quotations.show', compact('quotation'));
    }

    /**
     * Show the form for editing the specified quotation.
     */
    public function edit($id)
    {
        $quotation = Quotation::with(['items'])->findOrFail($id);

        // Check if quotation is editable (only draft status)
        if ($quotation->status !== 'draft') {
            return redirect()->route('admin.quotations.show', $id)
                ->with('error', 'Only draft quotations can be edited.');
        }

        $warehouses = Warehouse::active()->get();
        $mainWarehouse = Warehouse::main()->first();
        $invoiceSetting = InvoiceSetting::first();

        return view('admin.quotations.edit', compact('quotation', 'warehouses', 'mainWarehouse', 'invoiceSetting'));
    }

    /**
     * Update the specified quotation.
     */
    public function update(Request $request, $id)
    {
        $quotation = Quotation::findOrFail($id);

        // Check if quotation is editable
        if ($quotation->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft quotations can be updated.'
            ], 403);
        }

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
            'invoice_type' => 'required|in:gst,cash',
            'quotation_date' => 'required|date',
            'valid_till' => 'nullable|date|after_or_equal:quotation_date',
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
            $subtotal = 0;
            $taxTotal = 0;
            $cgstTotal = 0;
            $sgstTotal = 0;
            $igstTotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $mrpPrice = (float) $item['mrp_price'];
                $salePrice = (float) $item['price'];
                $taxPercent = $request->invoice_type === 'gst' ? (float) ($item['tax_percent'] ?? 0) : 0;

                $itemMRPTotal = $qty * $mrpPrice;
                $totalMRP += $itemMRPTotal;

                $itemDiscountAmount = ($mrpPrice - $salePrice) * $qty;
                $totalDiscountAmount += $itemDiscountAmount;

                $itemSaleTotal = $qty * $salePrice;
                $subtotal += $itemSaleTotal;

                if ($request->invoice_type === 'gst' && $taxPercent > 0) {
                    $itemTax = ($itemSaleTotal * $taxPercent) / 100;
                    $taxTotal += $itemTax;

                    if ($isIntraState) {
                        $halfTax = $itemTax / 2;
                        $cgstTotal += $halfTax;
                        $sgstTotal += $halfTax;
                        $item['cgst_amount'] = $halfTax;
                        $item['sgst_amount'] = $halfTax;
                        $item['igst_amount'] = 0;
                    } else {
                        $igstTotal += $itemTax;
                        $item['igst_amount'] = $itemTax;
                        $item['cgst_amount'] = 0;
                        $item['sgst_amount'] = 0;
                    }
                } else {
                    $item['cgst_amount'] = 0;
                    $item['sgst_amount'] = 0;
                    $item['igst_amount'] = 0;
                }

                $itemsData[] = $item;
            }

            /* ================= EXTRA DISCOUNT ================= */
            $extraDiscountValue = 0;
            $extraDiscountAmount = 0;
            $extraDiscountType = $request->extra_discount_type ?? 'amount';

            if ($request->filled('extra_discount') && (float)$request->extra_discount > 0) {
                $extraDiscountValue = (float)$request->extra_discount;

                if ($extraDiscountType === 'percent') {
                    $extraDiscountAmount = ($subtotal * $extraDiscountValue) / 100;
                } else {
                    $extraDiscountAmount = $extraDiscountValue;
                }
            }

            $extraCharge = (float) ($request->extra_charge ?? 0);
            $afterDiscountSubtotal = $subtotal - $extraDiscountAmount;
            $grandTotal = $afterDiscountSubtotal + ($request->invoice_type === 'gst' ? $taxTotal : 0) + $extraCharge;

            $roundOff = 0;
            if ($request->auto_round_off) {
                $rounded = round($grandTotal);
                $roundOff = $rounded - $grandTotal;
                $grandTotal = $rounded;
            }

            /* ================= UPDATE QUOTATION ================= */
            $quotation->update([
                'invoice_type' => $request->invoice_type,
                'quotation_date' => $request->quotation_date,
                'valid_till' => $request->valid_till,
                'party_id' => $request->party_id,
                'salesman_id' => $party->salesman_id,
                'warehouse_id' => $request->warehouse_id,
                'billing_address' => $billingAddress ? $billingAddress->full_address : $request->billing_address,
                'shipping_address' => $shippingAddress ? $shippingAddress->full_address : $request->shipping_address,
                'total_mrp' => round($totalMRP, 2),
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($totalDiscountAmount, 2),
                'tax_total' => round($taxTotal, 2),
                'cgst_total' => round($cgstTotal, 2),
                'sgst_total' => round($sgstTotal, 2),
                'igst_total' => round($igstTotal, 2),
                'tax_type' => $taxType,
                'extra_discount' => round($extraDiscountValue, 2),
                'extra_discount_type' => $extraDiscountType,
                'extra_charge' => round($extraCharge, 2),
                'charge_name' => $request->charge_name,
                'round_off' => round($roundOff, 2),
                'grand_total' => round($grandTotal, 2),
                'notes' => $request->notes,
            ]);

            /* ================= DELETE OLD ITEMS AND CREATE NEW ================= */
            QuotationItem::where('quotation_id', $quotation->id)->delete();

            foreach ($request->items as $index => $item) {
                $qty = (float) $item['quantity'];
                $mrpPrice = (float) $item['mrp_price'];
                $salePrice = (float) $item['price'];
                $discountPercent = (float) ($item['discount'] ?? 0);
                $taxPercent = $request->invoice_type === 'gst' ? (float) ($item['tax_percent'] ?? 0) : 0;

                $cgstAmount = $itemsData[$index]['cgst_amount'] ?? 0;
                $sgstAmount = $itemsData[$index]['sgst_amount'] ?? 0;
                $igstAmount = $itemsData[$index]['igst_amount'] ?? 0;

                if ($item['product_type'] === 'simple') {
                    $product = SimpleProduct::find($item['product_id']);
                    $productName = $product->name ?? 'Unknown Product';
                    $sku = $product->sku_code ?? '';
                    $barcode = $product->barcode ?? '';
                    $unit = $product->unit ?? 'PCS';
                    $hsnSac = $request->invoice_type === 'gst' ? ($product->hsn_code ?? '') : '';
                    $variantName = null;
                } else {
                    $product = VariantProduct::find($item['product_id']);
                    $productName = $product->name ?? 'Unknown Product';
                    $hsnSac = $request->invoice_type === 'gst' ? ($product->hsn_code ?? '') : '';

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

                $itemSaleTotal = $qty * $salePrice;
                $itemTax = ($request->invoice_type === 'gst') ? ($itemSaleTotal * $taxPercent) / 100 : 0;
                $itemTotal = $itemSaleTotal + $itemTax;

                $warrantyType = $item['warranty_type'] ?? 'none';
                $warrantyPeriod = (int) ($item['warranty_period'] ?? 0);
                $warrantyStart = null;
                $warrantyEnd = null;

                if ($warrantyType !== 'none' && $warrantyPeriod > 0) {
                    $warrantyStart = $request->quotation_date;
                    $warrantyEnd = $this->calculateWarrantyEnd($warrantyStart, $warrantyType, $warrantyPeriod);
                }

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'product_type' => $item['product_type'],
                    'product_name' => $productName,
                    'variant_name' => $variantName,
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'hsn_sac' => $hsnSac,
                    'unit' => $unit,
                    'quantity' => $qty,
                    'mrp_price' => round($mrpPrice, 2),
                    'price' => round($salePrice, 2),
                    'discount' => round($discountPercent, 2),
                    'tax_percent' => round($taxPercent, 2),
                    'tax_amount' => round($itemTax, 2),
                    'cgst_amount' => round($cgstAmount, 2),
                    'sgst_amount' => round($sgstAmount, 2),
                    'igst_amount' => round($igstAmount, 2),
                    'total' => round($itemTotal, 2),
                    'warranty_type' => $warrantyType,
                    'warranty_period' => $warrantyPeriod,
                    'warranty_start' => $warrantyStart,
                    'warranty_end' => $warrantyEnd,
                    'party_type' => $request->party_type,
                ]);
            }

            return response()->json([
                'success' => true,
                'quotation_id' => $quotation->id,
                'message' => 'Quotation updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified quotation.
     */
    public function destroy($id)
    {
        try {
            $quotation = Quotation::findOrFail($id);

            // Only allow deletion of draft quotations
            if ($quotation->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft quotations can be deleted.'
                ], 403);
            }

            // Delete items
            QuotationItem::where('quotation_id', $id)->delete();

            // Delete quotation
            $quotation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Quotation deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quotation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update quotation status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,sent,accepted,rejected,expired'
        ]);

        try {
            $quotation = Quotation::findOrFail($id);

            // --- VALIDATION RULES ---

            // ✅ Draft can only go to sent
            if ($quotation->status === 'draft' && $request->status !== 'sent') {
                return response()->json([
                    'success' => false,
                    'message' => 'Draft quotations can only be marked as Sent.'
                ], 400);
            }

            // ✅ Sent can go to accepted or rejected
            if ($quotation->status === 'sent') {
                // Check if expired
                if ($quotation->isExpired()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This quotation has expired. It cannot be accepted or rejected.'
                    ], 400);
                }

                if (!in_array($request->status, ['accepted', 'rejected'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sent quotations can only be marked as Accepted or Rejected.'
                    ], 400);
                }
            }

            // ✅ Accepted cannot go to any other status (except maybe keep it as is)
            if ($quotation->status === 'accepted' && $request->status !== 'accepted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accepted quotations cannot change status.'
                ], 400);
            }

            // ✅ Rejected cannot go to any other status
            if ($quotation->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Rejected quotations cannot change status.'
                ], 400);
            }

            // ✅ Expired cannot be changed
            if ($quotation->status === 'expired') {
                return response()->json([
                    'success' => false,
                    'message' => 'Expired quotations cannot change status.'
                ], 400);
            }

            // ✅ If trying to mark as accepted but quotation is already accepted
            if ($request->status === 'accepted' && $quotation->status === 'accepted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation is already accepted'
                ], 400);
            }

            // Update status
            $quotation->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Quotation status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF for quotation
     */
    public function pdf($id)
    {
        $quotation = Quotation::with(['party', 'items', 'warehouse'])->findOrFail($id);
        $settings = InvoiceSetting::first();

        return view('admin.quotations.pdf', compact('quotation', 'settings'));
    }

    /**
     * Send quotation via WhatsApp
     */
    public function sendWhatsApp($id)
    {
        try {
            $quotation = Quotation::with(['party'])->findOrFail($id);

            $party = $quotation->party;
            $phone = $party->phone ?? '';

            if (!$phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Party phone number not found'
                ], 400);
            }

            // Remove any non-numeric characters from phone
            $phone = preg_replace('/[^0-9]/', '', $phone);

            // Ensure 10 digit number
            if (strlen($phone) !== 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number format'
                ], 400);
            }

            // Generate PDF URL
            $pdfUrl = route('admin.quotations.pdf', $quotation->id);

            // Message format
            $message = "Hello {$party->name},\n\n";
            $message .= "Your quotation {$quotation->quotation_number} is ready.\n";
            $message .= "Total Amount: ₹ " . number_format($this->convertDecimalToFloat($quotation->grand_total), 2) . "\n";
            $message .= "Valid Till: " . ($quotation->valid_till ? $quotation->valid_till->format('d/m/Y') : 'Not specified') . "\n\n";
            $message .= "Download here:\n";
            $message .= url($pdfUrl);

            $whatsappUrl = "https://wa.me/91{$phone}?text=" . urlencode($message);

            return response()->json([
                'success' => true,
                'whatsapp_url' => $whatsappUrl
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert quotation to sales invoice with proper GST calculations
     */
 public function convertToInvoice(Request $request, $id)
    {
        try {
            $quotation = Quotation::with('items')->findOrFail($id);


            if ($quotation->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot convert an expired quotation to invoice.'
                ], 422);
            }

            // ✅ Check if rejected
            if ($quotation->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot convert a rejected quotation to invoice.'
                ], 422);
            }

            // ✅ Only accepted quotations can be converted
            if ($quotation->status !== 'accepted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only accepted quotations can be converted to invoice.'
                ], 422);
            }

            if ($quotation->converted_to_invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'This quotation has already been converted to a sales invoice.'
                ], 422);
            }

            // Get main warehouse
            $mainWarehouse = \App\Models\Warehouse::where('is_main', true)->first();
            if (!$mainWarehouse) {
                $mainWarehouse = \App\Models\Warehouse::first();
            }

            // Determine intra/inter state tax
            $taxType = $quotation->tax_type ?? 'intra';
            if ($mainWarehouse && $quotation->billing_address) {
                $party = \App\Models\Customer::find($quotation->party_id);
                if ($party) {
                    $warehouseState = $mainWarehouse->state ?? '';
                    $partyState = $party->billing_state ?? '';
                    if ($warehouseState && $partyState) {
                        $taxType = ($warehouseState === $partyState) ? 'intra' : 'inter';
                    }
                }
            }

            // Generate new invoice number
            $invoiceSetting = \App\Models\InvoiceSetting::first();
            $prefix = $invoiceSetting->prefix ?? 'SIM';
            $now = now();
            $month = (int) $now->format('m');
            $year = (int) $now->format('Y');
            $fyStart = $month >= 4 ? $year : $year - 1;
            $fyEnd = $fyStart + 1;
            $fyLabel = substr($fyStart, -2) . '-' . substr($fyEnd, -2);
            $lastInvoice = \App\Models\SalesInvoice::where('invoice_number', 'like', "{$prefix}/SI/{$fyLabel}/%")
                ->orderBy('invoice_number', 'desc')
                ->first();
            $lastSeq = 0;
            if ($lastInvoice) {
                $parts = explode('/', $lastInvoice->invoice_number);
                $lastSeq = (int) end($parts);
            }
            $newSeq = str_pad($lastSeq + 1, 6, '0', STR_PAD_LEFT);
            $invoiceNumber = "{$prefix}/SI/{$fyLabel}/{$newSeq}";

            // Recalculate tax splits per item
            $cgstTotal = 0;
            $sgstTotal = 0;
            $igstTotal = 0;

            // Create SalesInvoice (draft)
            $invoice = \App\Models\SalesInvoice::create([
                'invoice_number'      => $invoiceNumber,
                'invoice_type'        => $quotation->invoice_type,
                'invoice_date'        => now()->toDateString(),
                'party_id'            => $quotation->party_id,
                'salesman_id'         => $quotation->salesman_id,
                'warehouse_id'        => $mainWarehouse ? $mainWarehouse->_id : $quotation->warehouse_id,
                'billing_address'     => $quotation->billing_address,
                'shipping_address'    => $quotation->shipping_address,
                'total_mrp'           => $quotation->total_mrp,
                'subtotal'            => $quotation->subtotal,
                'discount_total'      => $quotation->discount_amount,
                'tax_total'           => $quotation->tax_total,
                'cgst_total'          => 0, // will be updated below
                'sgst_total'          => 0,
                'igst_total'          => 0,
                'tax_type'            => $taxType,
                'extra_discount'      => $quotation->extra_discount,
                'extra_discount_type' => $quotation->extra_discount_type,
                'extra_charge'        => $quotation->extra_charge,
                'charge_name'         => $quotation->charge_name,
                'round_off'           => $quotation->round_off,
                'grand_total'         => $quotation->grand_total,
                'total_paid'          => 0,
                'balance_amount'      => $quotation->grand_total,
                'payment_status'      => 'unpaid',
                'status'              => 'draft',
                'notes'               => $quotation->notes,
                'created_by'          => auth()->id(),
            ]);

            // Create invoice items with recalculated tax splits
            foreach ($quotation->items as $qItem) {
                $taxPercent = (float) ($qItem->tax_percent ?? 0);
                $salePriceTotal = (float) $qItem->quantity * (float) $qItem->price;
                $itemTaxAmount = ($salePriceTotal * $taxPercent) / 100;

                $cgstAmount = 0;
                $sgstAmount = 0;
                $igstAmount = 0;

                if ($quotation->invoice_type === 'gst') {
                    if ($taxType === 'intra') {
                        $cgstAmount = $itemTaxAmount / 2;
                        $sgstAmount = $itemTaxAmount / 2;
                    } else {
                        $igstAmount = $itemTaxAmount;
                    }
                }

                $cgstTotal += $cgstAmount;
                $sgstTotal += $sgstAmount;
                $igstTotal += $igstAmount;

                \App\Models\SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->_id,
                    'product_id'       => $qItem->product_id,
                    'variant_id'       => $qItem->variant_id,
                    'product_name'     => $qItem->product_name,
                    'variant_name'     => $qItem->variant_name,
                    'sku'              => $qItem->sku,
                    'barcode'          => $qItem->barcode ?? null,
                    'hsn_sac'          => $qItem->hsn_sac,
                    'quantity'         => $qItem->quantity,
                    'unit'             => $qItem->unit,
                    'mrp_price'        => $qItem->mrp_price,
                    'price'            => $qItem->price,
                    'discount'         => $qItem->discount,
                    'tax_percent'      => $taxPercent,
                    'tax_amount'       => $itemTaxAmount,
                    'cgst_amount'      => $cgstAmount,
                    'sgst_amount'      => $sgstAmount,
                    'igst_amount'      => $igstAmount,
                    'total'            => $qItem->total,
                    'warranty_type'    => $qItem->warranty_type ?? 'none',
                    'warranty_period'  => $qItem->warranty_period ?? 0,
                    'warranty_start'   => null,
                    'warranty_end'     => null,
                ]);
            }

            // Update invoice tax totals
            $invoice->cgst_total = $cgstTotal;
            $invoice->sgst_total = $sgstTotal;
            $invoice->igst_total = $igstTotal;
            $invoice->save();

            // Mark quotation as accepted AND converted
            $quotation->status = 'accepted';
            $quotation->converted_to_invoice = true;  // ← KEY FIX
            $quotation->save();

            return response()->json([
                'success'    => true,
                'message'    => 'Quotation converted to sales invoice successfully.',
                'invoice_id' => $invoice->_id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to convert quotation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate total MRP from items
     */
    private function calculateTotalMRP($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item->quantity * $item->mrp_price;
        }
        return round($total, 2);
    }

    /**
     * Get products from main warehouse
     */
    public function getMainWarehouseProducts(Request $request)
    {
        try {
            if ($request->filled('warehouse_id')) {
                $warehouse = Warehouse::find($request->warehouse_id);
            } else {
                $warehouse = Warehouse::main()->first();
            }

            if (!$warehouse) {
                return response()->json(['products' => []]);
            }

            /* ================= SIMPLE PRODUCTS ================= */
            $simpleStocks = WarehouseStock::where('warehouse_id', $warehouse->_id)
                ->where('product_type', 'simple')
                ->where('quantity', '>', 0)
                ->pluck('product_id');

            $simpleProducts = SimpleProduct::whereIn('_id', $simpleStocks)
                ->get()
                ->map(function ($product) use ($warehouse) {
                    $stock = WarehouseStock::where('warehouse_id', $warehouse->_id)
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
                        'dealer_price' => (float) ($product->dealer_price ?? 0),
                        'distributor_price' => (float) ($product->distributor_price ?? 0),
                        'current_stock' => $stock?->quantity ?? 0,
                        'unit' => $product->unit ?? 'PCS',
                        'hsn_code' => $product->hsn_code ?? '',
                        'warranty_type' => $product->warranty_unit ?? 'none',
                        'warranty_period' => (int) ($product->warranty_duration ?? 0),
                        'tax_percent' => (float) ($product->gst ?? 0),
                    ];
                });

            /* ================= VARIANT PRODUCTS ================= */
            $variantProducts = collect();

            $variantStocks = WarehouseStock::where('warehouse_id', $warehouse->_id)
                ->where('product_type', 'variant')
                ->where('quantity', '>', 0)
                ->get();

            $productIds = $variantStocks->pluck('product_id')->unique();

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
                        'name' => $variant['name'] ?? $product->name,
                        'type' => 'variant',
                        'sku' => $variant['sku_code'] ?? '',
                        'mrp_price' => (float) ($variant['mrp_price'] ?? 0),
                        'sale_price' => (float) ($variant['sale_price'] ?? 0),
                        'dealer_price' => (float) ($variant['dealer_price'] ?? 0),
                        'distributor_price' => (float) ($variant['distributor_price'] ?? 0),
                        'current_stock' => (float) $stock->quantity,
                        'unit' => $variant['unit'] ?? 'PCS',
                        'hsn_code' => $product->hsn_code ?? '',
                        'warranty_type' => $product->warranty_unit ?? 'none',
                        'warranty_period' => (int) ($product->warranty_duration ?? 0),
                        'tax_percent' => (float) ($product->gst ?? 0),
                    ]);
                }
            }

             $products = collect()
                    ->merge($simpleProducts)
                    ->merge($variantProducts)
                    ->values();

                return response()->json([
                    'products' => $products
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
        try {
            $query = Customer::where('status', 'active');

            if ($request->filled('party_type') && $request->party_type !== 'all') {
                $query->where('party_type', $request->party_type);
            }

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

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get party details by ID
     */
    public function getPartyDetails($id)
    {
        try {
            $party = Customer::with(['addresses', 'salesman'])->findOrFail($id);

            $billing = $party->addresses
                ->where('type', 'billing')
                ->where('is_default', true)
                ->first();

            $shipping = $party->addresses
                ->where('type', 'shipping')
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
                    'salesman_id' => $party->salesman_id,
                    'salesman_name' => $party->salesman ? $party->salesman->name : null,
                    'billing_address' => $billing ? $billing->full_address : '',
                    'shipping_address' => $shipping ? $shipping->full_address : '',
                    'billing_state' => $billing ? $billing->state : '',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create party via AJAX
     */
    public function storePartyAjax(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|digits:10|unique:customers,phone',
                'email' => 'nullable|email|unique:customers,email',
                'party_type' => 'required|in:customer,dealer,distributor',
                'salesman_id' => 'nullable|exists:salesmen,_id',
                'billing_pincode' => 'nullable|digits:6',
                'shipping_pincode' => 'nullable|digits:6',
            ]);

            $party = Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'party_type' => $request->party_type,
                'salesman_id' => $request->salesman_id,
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

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
