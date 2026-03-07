<?php
// app/Http/Controllers/Admin/QuotationController.php

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
        $query = Quotation::with(['party'])
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

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $quotations = $query->paginate(20)->withQueryString();

        // Summary stats with proper decimal handling
        $totalQuotations = Quotation::count();

        $totalAmountRaw = Quotation::sum('grand_total');
        $totalAmount = $this->convertDecimalToFloat($totalAmountRaw);

        $draftCount = Quotation::where('status', 'draft')->count();
        $sentCount = Quotation::where('status', 'sent')->count();

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

        // Get all active parties
        $parties = Customer::with(['addresses' => function($query) {
                $query->where('is_default', true);
            }])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $mainWarehouse = Warehouse::main()->first();
        $invoiceSetting = InvoiceSetting::first();

        return view('admin.quotations.create', compact('quotationNumber', 'parties', 'mainWarehouse', 'invoiceSetting'));
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
            'extra_discount' => 'nullable|numeric|min:0',
            'extra_discount_type' => 'nullable|in:amount,percent',
            'extra_charge' => 'nullable|numeric|min:0',
        ]);

        try {

            /* ================= GET PARTY DETAILS ================= */
            $party = Customer::with(['addresses'])->findOrFail($request->party_id);

            /* ================= TOTAL CALCULATION ================= */
            $totalMRP = 0;
            $totalDiscountAmount = 0;
            $subtotal = 0;

            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $mrpPrice = (float) $item['mrp_price'];
                $salePrice = (float) $item['price'];

                // MRP Total
                $itemMRPTotal = $qty * $mrpPrice;
                $totalMRP += $itemMRPTotal;

                // Discount amount
                $itemDiscountAmount = ($mrpPrice - $salePrice) * $qty;
                $totalDiscountAmount += $itemDiscountAmount;

                // Sale price total
                $itemSaleTotal = $qty * $salePrice;
                $subtotal += $itemSaleTotal;
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
            $grandTotal = $afterDiscountSubtotal + $extraCharge;

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
                'party_id' => $request->party_id,
                'quotation_date' => $request->quotation_date,
                'valid_till' => $request->valid_till,

                // Financial totals
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($totalDiscountAmount, 2),

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
            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $mrpPrice = (float) $item['mrp_price'];
                $salePrice = (float) $item['price'];
                $discountPercent = (float) ($item['discount'] ?? 0);

                // Get product details
                if ($item['product_type'] === 'simple') {
                    $product = SimpleProduct::find($item['product_id']);
                    $productName = $product->name ?? 'Unknown Product';
                    $sku = $product->sku_code ?? '';
                    $unit = $product->unit ?? 'PCS';
                    $variantName = null;
                    $taxPercent = (float) ($product->gst ?? 0);
                } else {
                    $product = VariantProduct::find($item['product_id']);
                    $productName = $product->name ?? 'Unknown Product';

                    $variants = $product->variants ?? [];
                    $variant = collect($variants)->first(function($v) use ($item) {
                        $vId = isset($v['_id']) ? (string)$v['_id'] : null;
                        return $vId === $item['variant_id'];
                    });

                    $variantName = $variant['name'] ?? null;
                    $sku = $variant['sku_code'] ?? '';
                    $unit = $variant['unit'] ?? 'PCS';
                    $taxPercent = (float) ($product->gst ?? 0);
                }

                // Calculate item total
                $itemTotal = $qty * $salePrice;

                // Create item with tax percent stored for later use
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'product_type' => $item['product_type'],
                    'product_name' => $productName,
                    'variant_name' => $variantName,
                    'sku' => $sku,
                    'unit' => $unit,
                    'quantity' => $qty,
                    'mrp_price' => round($mrpPrice, 2),
                    'price' => round($salePrice, 2),
                    'discount' => round($discountPercent, 2),
                    'total' => round($itemTotal, 2),
                    'tax_percent' => $taxPercent, // Store tax percent for invoice conversion
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
     * Display the specified quotation.
     */
    public function show($id)
    {
        $quotation = Quotation::with(['party', 'items'])->findOrFail($id);

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

        // Get all active parties
        $parties = Customer::with(['addresses' => function($query) {
                $query->where('is_default', true);
            }])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $mainWarehouse = Warehouse::main()->first();
        $invoiceSetting = InvoiceSetting::first();

        return view('admin.quotations.edit', compact('quotation', 'parties', 'mainWarehouse', 'invoiceSetting'));
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
            'extra_discount' => 'nullable|numeric|min:0',
            'extra_discount_type' => 'nullable|in:amount,percent',
            'extra_charge' => 'nullable|numeric|min:0',
        ]);

        try {

            $party = Customer::with(['addresses'])->findOrFail($request->party_id);

            /* ================= TOTAL CALCULATION ================= */
            $totalMRP = 0;
            $totalDiscountAmount = 0;
            $subtotal = 0;

            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $mrpPrice = (float) $item['mrp_price'];
                $salePrice = (float) $item['price'];

                $itemMRPTotal = $qty * $mrpPrice;
                $totalMRP += $itemMRPTotal;

                $itemDiscountAmount = ($mrpPrice - $salePrice) * $qty;
                $totalDiscountAmount += $itemDiscountAmount;

                $itemSaleTotal = $qty * $salePrice;
                $subtotal += $itemSaleTotal;
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
            $grandTotal = $afterDiscountSubtotal + $extraCharge;

            $roundOff = 0;
            if ($request->auto_round_off) {
                $rounded = round($grandTotal);
                $roundOff = $rounded - $grandTotal;
                $grandTotal = $rounded;
            }

            /* ================= UPDATE QUOTATION ================= */
            $quotation->update([
                'quotation_date' => $request->quotation_date,
                'valid_till' => $request->valid_till,
                'party_id' => $request->party_id,
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($totalDiscountAmount, 2),
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

            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $mrpPrice = (float) $item['mrp_price'];
                $salePrice = (float) $item['price'];
                $discountPercent = (float) ($item['discount'] ?? 0);

                if ($item['product_type'] === 'simple') {
                    $product = SimpleProduct::find($item['product_id']);
                    $productName = $product->name ?? 'Unknown Product';
                    $sku = $product->sku_code ?? '';
                    $unit = $product->unit ?? 'PCS';
                    $variantName = null;
                    $taxPercent = (float) ($product->gst ?? 0);
                } else {
                    $product = VariantProduct::find($item['product_id']);
                    $productName = $product->name ?? 'Unknown Product';

                    $variants = $product->variants ?? [];
                    $variant = collect($variants)->first(function($v) use ($item) {
                        $vId = isset($v['_id']) ? (string)$v['_id'] : null;
                        return $vId === $item['variant_id'];
                    });

                    $variantName = $variant['name'] ?? null;
                    $sku = $variant['sku_code'] ?? '';
                    $unit = $variant['unit'] ?? 'PCS';
                    $taxPercent = (float) ($product->gst ?? 0);
                }

                $itemTotal = $qty * $salePrice;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'product_type' => $item['product_type'],
                    'product_name' => $productName,
                    'variant_name' => $variantName,
                    'sku' => $sku,
                    'unit' => $unit,
                    'quantity' => $qty,
                    'mrp_price' => round($mrpPrice, 2),
                    'price' => round($salePrice, 2),
                    'discount' => round($discountPercent, 2),
                    'total' => round($itemTotal, 2),
                    'tax_percent' => $taxPercent,
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

            // If trying to mark as accepted but quotation is already accepted
            if ($request->status === 'accepted' && $quotation->status === 'accepted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation is already accepted'
                ], 400);
            }

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
        $quotation = Quotation::with(['party', 'items'])->findOrFail($id);
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
    public function convertToInvoice($id)
    {

        try {
            // Load quotation with all necessary relationships
            $quotation = Quotation::with(['items', 'party.addresses'])->findOrFail($id);

            // Check if quotation can be converted
            if ($quotation->status === 'expired') {
                throw new \Exception('Cannot convert expired quotation to invoice');
            }

            if ($quotation->status === 'accepted') {
                throw new \Exception('Quotation has already been converted to invoice');
            }

            // Get party and addresses
            $party = $quotation->party;

            // Get billing address
            $billingAddress = $party->addresses
                ->where('type', 'billing')
                ->where('is_default', true)
                ->first();

            if (!$billingAddress) {
                $billingAddress = $party->addresses->where('type', 'billing')->first();
            }

            // Get shipping address
            $shippingAddress = $party->addresses
                ->where('type', 'shipping')
                ->where('is_default', true)
                ->first();

            if (!$shippingAddress) {
                $shippingAddress = $party->addresses->where('type', 'shipping')->first();
            }

            // Get main warehouse
            $mainWarehouse = Warehouse::main()->first();
            if (!$mainWarehouse) {
                throw new \Exception('No main warehouse found. Please set a main warehouse first.');
            }

            // Get warehouse state
            $warehouseState = $mainWarehouse->state ?? '';

            // Get party state from billing address
            $partyState = '';
            if ($billingAddress) {
                $partyState = $billingAddress->state ?? '';
            }

            // Determine tax type (intra-state or inter-state)
            $isIntraState = (!empty($warehouseState) && !empty($partyState) && $warehouseState === $partyState);
            $taxType = $isIntraState ? 'intra' : 'inter';

            // Initialize tax totals
            $taxTotal = 0;
            $cgstTotal = 0;
            $sgstTotal = 0;
            $igstTotal = 0;

            // Calculate taxes for each item
            foreach ($quotation->items as $item) {
                $itemSaleTotal = $item->quantity * $item->price;
                $taxPercent = (float) ($item->tax_percent ?? 0);

                // If tax percent is 0, try to get from product
                if ($taxPercent == 0) {
                    if ($item->product_type === 'simple') {
                        $product = SimpleProduct::find($item->product_id);
                        $taxPercent = (float) ($product->gst ?? 0);
                    } else {
                        $product = VariantProduct::find($item->product_id);
                        $taxPercent = (float) ($product->gst ?? 0);
                    }
                }

                $itemTax = ($itemSaleTotal * $taxPercent) / 100;
                $taxTotal += $itemTax;

                if ($isIntraState) {
                    // Split tax equally into CGST and SGST
                    $halfTax = $itemTax / 2;
                    $cgstTotal += $halfTax;
                    $sgstTotal += $halfTax;
                } else {
                    // Full tax as IGST
                    $igstTotal += $itemTax;
                }
            }

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Create sales invoice with all tax details
            $invoice = SalesInvoice::create([
                'invoice_number' => $invoiceNumber,
                'invoice_type' => 'gst',
                'invoice_date' => now(),
                'party_id' => $quotation->party_id,
                'salesman_id' => $party->salesman_id,
                'warehouse_id' => $mainWarehouse->_id,

                // Addresses
                'billing_address' => $billingAddress ? $billingAddress->full_address : null,
                'shipping_address' => $shippingAddress ? $shippingAddress->full_address : ($billingAddress ? $billingAddress->full_address : null),

                // Invoice details
                'payment_terms' => null,
                'due_date' => null,
                'po_number' => null,

                // Financial totals - mapping from quotation
                'total_mrp' => $this->calculateTotalMRP($quotation->items),
                'subtotal' => $quotation->subtotal,
                'discount_total' => $quotation->discount_amount,

                // Tax totals
                'tax_total' => round($taxTotal, 2),
                'cgst_total' => round($cgstTotal, 2),
                'sgst_total' => round($sgstTotal, 2),
                'igst_total' => round($igstTotal, 2),
                'tax_type' => $taxType,

                // Extra fields
                'extra_discount' => $quotation->extra_discount,
                'extra_discount_type' => $quotation->extra_discount_type,
                'extra_charge' => $quotation->extra_charge,
                'charge_name' => $quotation->charge_name,
                'round_off' => $quotation->round_off,

                // Grand total with tax
                'grand_total' => round($quotation->grand_total + $taxTotal, 2),

                // Payment info (unpaid by default)
                'total_paid' => 0,
                'balance_amount' => round($quotation->grand_total + $taxTotal, 2),
                'payment_status' => 'unpaid',

                'status' => 'draft',
                'notes' => "Created from Quotation: {$quotation->quotation_number}\n" . ($quotation->notes ?? ''),
                'created_by' => Auth::guard('admin')->id(),
            ]);

            // Create invoice items with proper tax splits
            foreach ($quotation->items as $item) {
                $itemSaleTotal = $item->quantity * $item->price;
                $taxPercent = (float) ($item->tax_percent ?? 0);

                // If tax percent is 0, try to get from product
                if ($taxPercent == 0) {
                    if ($item->product_type === 'simple') {
                        $product = SimpleProduct::find($item->product_id);
                        $taxPercent = (float) ($product->gst ?? 0);
                    } else {
                        $product = VariantProduct::find($item->product_id);
                        $taxPercent = (float) ($product->gst ?? 0);
                    }
                }

                $itemTax = ($itemSaleTotal * $taxPercent) / 100;

                // Get HSN code
                $hsnCode = '';
                if ($item->product_type === 'simple') {
                    $product = SimpleProduct::find($item->product_id);
                    $hsnCode = $product->hsn_code ?? '';
                } else {
                    $product = VariantProduct::find($item->product_id);
                    $hsnCode = $product->hsn_code ?? '';
                }

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->_id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'sku' => $item->sku,
                    'barcode' => '',
                    'hsn_sac' => $hsnCode,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'mrp_price' => $item->mrp_price,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => round($itemTax, 2),
                    'cgst_amount' => $isIntraState ? round($itemTax / 2, 2) : 0,
                    'sgst_amount' => $isIntraState ? round($itemTax / 2, 2) : 0,
                    'igst_amount' => $isIntraState ? 0 : round($itemTax, 2),
                    'total' => round($itemSaleTotal + $itemTax, 2),
                    'warranty_type' => 'none',
                    'warranty_period' => 0,
                ]);
            }

            // Update quotation status to accepted
            $quotation->update(['status' => 'accepted']);


            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->_id,
                'message' => 'Quotation converted to invoice successfully with GST calculations'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
