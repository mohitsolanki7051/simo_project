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
use App\Models\CreditNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\Regex;

class SalesInvoiceController extends Controller
{
// In SalesInvoiceController.php

// ① Replace generateInvoiceNumber() — accept $invoiceType param
private function generateInvoiceNumber(string $invoiceType = 'gst'): string
{
    $financialYear = $this->getFinancialYear();

    // GST Invoice → SIM/SI/26-27/000001
    // Cash Memo   → SIM/SICM/26-27/000001
    $prefix = $invoiceType === 'cash' ? 'SIM/SICM' : 'SIM/SI';

    $lastInvoice = SalesInvoice::where('invoice_number', 'regex', "/^\Q{$prefix}\E\/{$financialYear}\/\d+$/")
        ->orderBy('invoice_number', 'desc')
        ->first();

    if ($lastInvoice) {
        preg_match('/(\d+)$/', $lastInvoice->invoice_number, $matches);
        $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
        $newNumber  = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '000001';
    }

    return "{$prefix}/{$financialYear}/{$newNumber}";
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
            ->orderBy('created_at', 'desc');

        if ($request->filled('period')) {
            $period = $request->period;
            if ($period === 'today') {
                $query->whereDate('invoice_date', today());
            } elseif ($period === 'this_month') {
                $query->whereDate('invoice_date', '>=', now()->startOfMonth()->toDateString())
                      ->whereDate('invoice_date', '<=', now()->endOfMonth()->toDateString());
            } elseif ($period === 'previous_month') {
                $query->whereDate('invoice_date', '>=', now()->subMonth()->startOfMonth()->toDateString())
                      ->whereDate('invoice_date', '<=', now()->subMonth()->endOfMonth()->toDateString());
            } elseif ($period === 'custom') {
                if ($request->filled('date_from')) $query->whereDate('invoice_date', '>=', $request->date_from);
                if ($request->filled('date_to'))   $query->whereDate('invoice_date', '<=', $request->date_to);
            } elseif (is_numeric($period)) {
                $query->whereDate('invoice_date', '>=', now()->subDays((int)$period)->toDateString());
            }
        } else {
            if ($request->filled('date')) {
                $query->whereDate('invoice_date', $request->date);
            }
        }
        if ($request->filled('invoice_number')) $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        if ($request->filled('invoice_type') && $request->invoice_type != '') $query->where('invoice_type', $request->invoice_type);
        if ($request->filled('party_id') && $request->party_id != '')         $query->where('party_id', $request->party_id);
        if ($request->filled('payment_status') && $request->payment_status != '') $query->where('payment_status', $request->payment_status);
        if ($request->filled('status') && $request->status != '')             $query->where('status', $request->status);
        if ($request->filled('warehouse_id') && $request->warehouse_id != '') $query->where('warehouse_id', $request->warehouse_id);

        $invoices = $query->paginate(20)->withQueryString();

        /* ── Credit Note Map + Advance Map ── */
        $partyIds = $invoices->pluck('party_id')
            ->map(fn($id) => (string)$id)
            ->unique()->values()->toArray();

        $creditNoteMap = [];


        if (!empty($partyIds)) {
            \App\Models\CreditNote::whereIn('party_id', $partyIds)
                ->where('status',  ['active', 'partial'])
                ->where('remaining_amount', '>', 0)
                ->get()
                ->each(function ($cn) use (&$creditNoteMap) {
                    $pid = (string) $cn->party_id;
                    $creditNoteMap[$pid] = ($creditNoteMap[$pid] ?? 0) + $this->decimalToFloat($cn->remaining_amount);
                });


        }

        /* ── Stats Query ── */
        $statsQuery = SalesInvoice::where('status', '!=', 'draft');

        if ($request->filled('period')) {
            $period = $request->period;
            if ($period === 'today') {
                $statsQuery->whereDate('invoice_date', today());
            } elseif ($period === 'this_month') {
                $statsQuery->whereDate('invoice_date', '>=', now()->startOfMonth()->toDateString())
                           ->whereDate('invoice_date', '<=', now()->endOfMonth()->toDateString());
            } elseif ($period === 'previous_month') {
                $statsQuery->whereDate('invoice_date', '>=', now()->subMonth()->startOfMonth()->toDateString())
                           ->whereDate('invoice_date', '<=', now()->subMonth()->endOfMonth()->toDateString());
            } elseif ($period === 'custom') {
                if ($request->filled('date_from')) $statsQuery->whereDate('invoice_date', '>=', $request->date_from);
                if ($request->filled('date_to'))   $statsQuery->whereDate('invoice_date', '<=', $request->date_to);
            } elseif (is_numeric($period)) {
                $statsQuery->whereDate('invoice_date', '>=', now()->subDays((int)$period)->toDateString());
            }
        } else {
            if ($request->filled('date')) {
                $statsQuery->whereDate('invoice_date', $request->date);
            }
        }
        if ($request->filled('invoice_number')) $statsQuery->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        if ($request->filled('invoice_type') && $request->invoice_type != '')  $statsQuery->where('invoice_type', $request->invoice_type);
        if ($request->filled('party_id') && $request->party_id != '')          $statsQuery->where('party_id', $request->party_id);
        if ($request->filled('payment_status') && $request->payment_status != '') $statsQuery->where('payment_status', $request->payment_status);
        if ($request->filled('status') && $request->status != '')              $statsQuery->where('status', $request->status);
        if ($request->filled('warehouse_id') && $request->warehouse_id != '')  $statsQuery->where('warehouse_id', $request->warehouse_id);

        $totalSalesRaw  = (clone $statsQuery)->sum('grand_total');
        $totalPaidRaw   = (clone $statsQuery)->whereIn('payment_status', ['paid', 'partial'])->sum('total_paid');
        $totalUnpaidRaw = (clone $statsQuery)->whereIn('payment_status', ['unpaid', 'partial'])->sum('balance_amount');

        $totalSales  = $this->decimalToFloat($totalSalesRaw);
        $totalPaid   = $this->decimalToFloat($totalPaidRaw);
        $totalUnpaid = $this->decimalToFloat($totalUnpaidRaw);

        $customers = Customer::where('status', 'active')->orderBy('name')->get();

        return view('admin.sales.index', compact(
            'invoices', 'totalSales', 'totalPaid', 'totalUnpaid',
            'customers', 'creditNoteMap'
        ));
    }

    /**
     * Show the form for creating a new sales invoice.
     */
    public function create()
    {
        // Generate invoice number with prefix
        $invoiceNumber = $this->generateInvoiceNumber('gst');

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
            ->get()
            ->map(function($party) {
                // Add dynamic opening balance to the party object
                $party->dynamic_opening_balance = $this->getDynamicOpeningBalance($party->_id);
                return $party;
            });

        $warehouses = Warehouse::active()->get();
        $mainWarehouse = Warehouse::main()->first();
        $invoiceSetting = InvoiceSetting::first();

        return view('admin.sales.create', compact('invoiceNumber', 'parties', 'salesmen', 'warehouses', 'mainWarehouse', 'invoiceSetting'));
    }

    /**
     * Generate credit note number with format: SIM/CN/24-25/000001
     */
    private function generateCreditNoteNumber()
    {
        $financialYear = $this->getFinancialYear();

        $lastNote = CreditNote::where('credit_note_number', 'regex', "/^SIM\/CN\/{$financialYear}\/\d+$/")
            ->orderBy('credit_note_number', 'desc')
            ->first();

        if ($lastNote) {
            preg_match('/(\d+)$/', $lastNote->credit_note_number, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "SIM/CN/{$financialYear}/{$newNumber}";
    }

/**
 * Cancel invoice (revert stock and mark as cancelled)
 */
public function cancel($id)
{
    try {
        $invoice = SalesInvoice::with(['items', 'party'])->findOrFail($id);

        // Check if invoice can be cancelled (only confirmed/completed)
        if (!in_array($invoice->status, ['confirmed', 'completed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only confirmed or completed invoices can be cancelled.'
            ], 400);
        }

        // Check if already cancelled
        if ($invoice->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already cancelled.'
            ], 400);
        }

        // REVERT STOCK - Add back to warehouse
        foreach ($invoice->items as $item) {
            $stock = WarehouseStock::where('warehouse_id', $invoice->warehouse_id)
                ->where('product_id', $item->product_id)
                ->where('product_type', $item->variant_id ? 'variant' : 'simple')
                ->when($item->variant_id, function ($q) use ($item) {
                    return $q->where('variant_id', $item->variant_id);
                })
                ->first();

            if ($stock) {
                // Increase stock (revert the deduction)
                $stock->update([
                    'quantity' => $stock->quantity + $item->quantity
                ]);

                // Create warehouse movement for cancellation
                WarehouseMovement::create([
                    'warehouse_id' => $invoice->warehouse_id,
                    'product_id' => $item->product_id,
                    'product_type' => $item->variant_id ? 'variant' : 'simple',
                    'variant_id' => $item->variant_id ?? null,
                    'type' => 'cancellation',
                    'quantity' => +$item->quantity,
                    'reference_id' => $invoice->_id,
                    'remarks' => "Invoice Cancelled: {$invoice->invoice_number} - Stock returned",
                ]);
            }
        }

        // Create a Credit Note for any payments received on this invoice
        $creditNoteMsg = '';
        $totalPaid = $this->decimalToFloat($invoice->total_paid);
        if ($totalPaid > 0) {
            $creditNoteNumber = $this->generateCreditNoteNumber();
            CreditNote::create([
                'credit_note_number' => $creditNoteNumber,
                'party_id'           => $invoice->party_id,
                'sales_invoice_id'   => $invoice->_id,
                'credit_date'        => now(),
                'subtotal'           => $totalPaid,
                'tax_amount'         => 0.0,
                'discount_amount'    => 0.0,
                'amount'             => $totalPaid,
                'used_amount'        => 0.0,
                'remaining_amount'   => $totalPaid,
                'reason'             => "Invoice Cancelled: " . $invoice->invoice_number,
                'status'             => 'active',
                'created_by'         => Auth::guard('admin')->id() ?? Auth::id(),
            ]);
            $creditNoteMsg = " A Credit Note ({$creditNoteNumber}) of ₹" . number_format($totalPaid, 2) . " has been created for the customer.";
        }

        // Update invoice status to cancelled
        $invoice->update([
            'status' => 'cancelled',
            'notes' => ($invoice->notes ? $invoice->notes . "\n" : '') .
                      "[Cancelled on " . now()->format('d/m/Y H:i') . "]"
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice cancelled successfully. Stock has been returned.' . $creditNoteMsg
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to cancel invoice: ' . $e->getMessage()
        ], 500);
    }
}
public function store(Request $request)
    {
        if ($request->has('items') && is_string($request->items)) {
            $request->merge(['items' => json_decode($request->items, true)]);
        }

        $request->validate([
            'party_id'              => 'required|exists:customers,_id',
            'party_type'            => 'required|in:customer,dealer,distributor',
            'warehouse_id'          => 'required|exists:warehouses,_id',
            'invoice_date'          => 'required|date',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required',
            'items.*.product_type'  => 'required|in:simple,variant',
            'items.*.variant_id'    => 'nullable',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.price'         => 'required|numeric|min:0',
            'items.*.mrp_price'     => 'required|numeric|min:0',
            'items.*.discount'      => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent'   => 'nullable|numeric|min:0|max:100',
            'extra_discount'        => 'nullable|numeric|min:0',
            'extra_discount_type'   => 'nullable|in:amount,percent',
            'extra_charge'          => 'nullable|numeric|min:0',
            'amount_paid'           => 'nullable|numeric|min:0',
        ]);

        try {
            $party          = Customer::with(['addresses', 'salesman'])->findOrFail($request->party_id);
            $openingBalance = $this->getDynamicOpeningBalance($request->party_id);

            $unpaidInvoicesBalance = SalesInvoice::where('party_id', $request->party_id)
                ->where('status', '!=', 'draft')
                ->where('payment_status', '!=', 'paid')
                ->sum('balance_amount');
            $unpaidInvoicesBalance = $this->decimalToFloat($unpaidInvoicesBalance);

            $currentDue          = $openingBalance + $unpaidInvoicesBalance;
            $grandTotalFromReq   = (float) $request->grand_total;
            $amountPaidFromReq   = (float) ($request->amount_paid ?? 0);
            $newInvoiceBalance   = $grandTotalFromReq - $amountPaidFromReq;
            $totalDueAfterInvoice= $currentDue + $newInvoiceBalance;
            $creditLimit         = (float) ($party->credit_limit ?? 0);

            if ($creditLimit > 0 && $totalDueAfterInvoice > $creditLimit) {
                $availableCredit         = max(0, $creditLimit - $currentDue);
                $formattedOpeningBalance = number_format($openingBalance, 2);
                $formattedUnpaidInvoices = number_format($unpaidInvoicesBalance, 2);
                $formattedCurrentDue     = number_format($currentDue, 2);
                $formattedNewBalance     = number_format($newInvoiceBalance, 2);
                $formattedTotalDue       = number_format($totalDueAfterInvoice, 2);
                $formattedCreditLimit    = number_format($creditLimit, 2);
                $formattedAvailable      = number_format($availableCredit, 2);

                throw new \Exception(
                    "❌ Credit Limit Exceeded!\n\n" .
                    "Credit Limit: ₹{$formattedCreditLimit}\n" .
                    "─────────────────────\n" .
                    "Opening Balance: ₹{$formattedOpeningBalance}\n" .
                    "Unpaid Invoices: ₹{$formattedUnpaidInvoices}\n" .
                    "Current Due: ₹{$formattedCurrentDue}\n" .
                    "─────────────────────\n" .
                    "New Invoice Balance: ₹{$formattedNewBalance}\n" .
                    "Total After Invoice: ₹{$formattedTotalDue}\n" .
                    "─────────────────────\n" .
                    "Available Credit: ₹{$formattedAvailable}\n\n" .
                    "💡 Customer can only take items worth ₹{$formattedAvailable} on credit.\n" .
                    "Please collect payment or increase credit limit."
                );
            }

            $billingAddress  = $party->addresses->where('type', 'billing')->where('is_default', true)->first();
            $shippingAddress = $party->addresses->where('type', 'shipping')->where('is_default', true)->first();

            $warehouse      = Warehouse::find($request->warehouse_id);
            $warehouseState = $warehouse->state ?? '';
            $partyState     = $billingAddress ? ($billingAddress->state ?? '') : '';
            $isIntraState   = (!empty($warehouseState) && !empty($partyState) && $warehouseState === $partyState);
            $taxType        = $isIntraState ? 'intra' : 'inter';

            $totalMRP            = 0;
            $totalDiscountAmount = 0;
            $subtotal            = 0;   // always ex-GST subtotal
            $taxTotal            = 0;
            $cgstTotal           = 0;
            $sgstTotal           = 0;
            $igstTotal           = 0;
            $itemsData           = [];

            // ── ITEM TOTALS LOOP ──────────────────────────────────────────────
            foreach ($request->items as $item) {
                $qty        = (float) $item['quantity'];
                $mrpPrice   = (float) $item['mrp_price'];
                $salePrice  = (float) $item['price'];       // inclusive: gross price per unit; exclusive: ex-GST price
                $taxPercent = (float) ($item['tax_percent'] ?? 0);
                $isIncl     = !empty($item['gst_inclusive']); // true = inclusive mode

                $totalMRP += $qty * $mrpPrice;

                if ($isIncl && $taxPercent > 0) {
                    // ── INCLUSIVE: reverse-extract base price and GST ─────────
                    // salePrice is the GST-inclusive price per unit
                    // base = price × 100 / (100 + gst%)
                    $basePricePerUnit = $salePrice * 100 / (100 + $taxPercent);
                    $itemBaseTotal    = $qty * $basePricePerUnit;
                    $grossTotal       = $qty * $salePrice;
                    $itemTax          = $grossTotal - $itemBaseTotal;
                    $itemDiscountAmt  = ($mrpPrice - $salePrice) * $qty; // discount vs MRP (using inclusive price)
                } else {
                    // ── EXCLUSIVE: current logic, unchanged ───────────────────
                    $basePricePerUnit = $salePrice;
                    $itemBaseTotal    = $qty * $salePrice;
                    $itemTax          = ($itemBaseTotal * $taxPercent) / 100;
                    $itemDiscountAmt  = ($mrpPrice - $salePrice) * $qty;
                }

                $totalDiscountAmount += $itemDiscountAmt;
                $subtotal            += $itemBaseTotal;    // always ex-GST
                $taxTotal            += $itemTax;

                if ($isIntraState) {
                    $halfTax             = $itemTax / 2;
                    $cgstTotal          += $halfTax;
                    $sgstTotal          += $halfTax;
                    $item['cgst_amount'] = $halfTax;
                    $item['sgst_amount'] = $halfTax;
                    $item['igst_amount'] = 0;
                } else {
                    $igstTotal           += $itemTax;
                    $item['igst_amount']  = $itemTax;
                    $item['cgst_amount']  = 0;
                    $item['sgst_amount']  = 0;
                }

                // carry calculated values to the save loop below
                $item['_base_price'] = $basePricePerUnit;
                $item['_is_incl']    = $isIncl;
                $itemsData[]         = $item;
            }
            // ── END ITEM TOTALS LOOP ──────────────────────────────────────────

            $extraDiscountValue  = 0;
            $extraDiscountAmount = 0;
            $extraDiscountType   = $request->extra_discount_type ?? 'amount';

            if ($request->filled('extra_discount') && (float)$request->extra_discount > 0) {
                $extraDiscountValue  = (float)$request->extra_discount;
                $extraDiscountAmount = $extraDiscountType === 'percent'
                    ? ($subtotal * $extraDiscountValue) / 100
                    : $extraDiscountValue;
            }

            $extraCharge           = (float) ($request->extra_charge ?? 0);
            $afterDiscountSubtotal = $subtotal - $extraDiscountAmount;
            $grandTotal            = $afterDiscountSubtotal + $taxTotal + $extraCharge;

            $roundOff = 0;
            if ($request->auto_round_off) {
                $rounded  = round($grandTotal);
                $roundOff = $rounded - $grandTotal;
                $grandTotal = $rounded;
            }

            $amountPaid  = (float) ($request->amount_paid ?? 0);
            $advanceUsed = 0;
            $balance     = $grandTotal - $amountPaid;

            if ($amountPaid <= 0) {
                $paymentStatus = 'unpaid';
            } elseif ($balance <= 0.01) {
                $paymentStatus = 'paid';
                $balance       = 0;
            } else {
                $paymentStatus = 'partial';
            }

            $invoiceNumber = $this->generateInvoiceNumber($request->invoice_type ?? 'gst');

            $invoice = SalesInvoice::create([
                'invoice_number'      => $invoiceNumber,
                'public_token'        => \Illuminate\Support\Str::random(40),
                'invoice_type'        => $request->invoice_type,
                 'gst_mode'            => $request->gst_mode ?? 'exclusive',
                'invoice_date'        => $request->invoice_date,
                'party_id'            => $request->party_id,
                'salesman_id'         => $party->salesman_id,
                'warehouse_id'        => $request->warehouse_id,
                'billing_address'     => $billingAddress ? $billingAddress->full_address : $request->billing_address,
                'shipping_address'    => $shippingAddress ? $shippingAddress->full_address : $request->shipping_address,
                'payment_terms'       => $request->payment_terms,
                'due_date'            => $request->due_date,
                'po_number'           => $request->po_number,
                'total_mrp'           => round($totalMRP, 2),
                'subtotal'            => round($subtotal, 2),
                'discount_total'      => round($totalDiscountAmount, 2),
                'tax_total'           => round($taxTotal, 2),
                'cgst_total'          => round($cgstTotal, 2),
                'sgst_total'          => round($sgstTotal, 2),
                'igst_total'          => round($igstTotal, 2),
                'tax_type'            => $taxType,
                'extra_discount'      => round($extraDiscountValue, 2),
                'extra_discount_type' => $extraDiscountType,
                'extra_charge'        => round($extraCharge, 2),
                'charge_name'         => $request->charge_name,
                'round_off'           => round($roundOff, 2),
                'grand_total'         => round($grandTotal, 2),
                'total_paid'          => round($amountPaid, 2),
                'balance_amount'      => round($balance, 2),
                'payment_status'      => $paymentStatus,
                'advance_used'        => round($advanceUsed, 2),
                'status'              => 'draft',
                'notes'               => $request->notes,
                'created_by'          => Auth::guard('admin')->id(),
            ]);

            // ── ITEM SAVE LOOP ────────────────────────────────────────────────
            foreach ($request->items as $index => $item) {
                $qty              = (float) $item['quantity'];
                $mrpPrice         = (float) $item['mrp_price'];
                $salePrice        = (float) $item['price'];         // gross if inclusive
                $discountPercent  = (float) ($item['discount'] ?? 0);
                $taxPercent       = (float) ($item['tax_percent'] ?? 0);
                $cgstAmount       = $itemsData[$index]['cgst_amount'] ?? 0;
                $sgstAmount       = $itemsData[$index]['sgst_amount'] ?? 0;
                $igstAmount       = $itemsData[$index]['igst_amount'] ?? 0;
                $isIncl           = !empty($itemsData[$index]['_is_incl']);
                $basePricePerUnit = (float) ($itemsData[$index]['_base_price'] ?? $salePrice);

                if ($item['product_type'] === 'simple') {
                    $product     = SimpleProduct::find($item['product_id']);
                    $productName = $product->name ?? 'Unknown Product';
                    $sku         = $product->sku_code ?? '';
                    $barcode     = $product->barcode ?? '';
                    $unit        = $product->unit ?? 'PCS';
                    $hsnSac      = $product->hsn_code ?? '';
                    $variantName = null;
                } else {
                    $product     = VariantProduct::find($item['product_id']);
                    $productName = $product->name ?? 'Unknown Product';
                    $hsnSac      = $product->hsn_code ?? '';
                    $variants    = $product->variants ?? [];
                    $variant     = collect($variants)->first(function($v) use ($item) {
                        return (string)(isset($v['_id']) ? $v['_id'] : null) === $item['variant_id'];
                    });
                    $variantName = $variant['name'] ?? null;
                    $sku         = $variant['sku_code'] ?? '';
                    $barcode     = $variant['barcode'] ?? '';
                    $unit        = $variant['unit'] ?? 'PCS';
                }

                $itemBaseTotal = $qty * $basePricePerUnit;
                $itemTax       = $cgstAmount + $sgstAmount + $igstAmount;

                // inclusive: final total = gross (price already includes tax)
                // exclusive: final total = base + tax
                $itemFinal = $isIncl ? ($qty * $salePrice) : ($itemBaseTotal + $itemTax);

                $warrantyType   = $item['warranty_type'] ?? 'none';
                $warrantyPeriod = (int) ($item['warranty_period'] ?? 0);
                $warrantyStart  = null;
                $warrantyEnd    = null;

                if ($warrantyType !== 'none' && $warrantyPeriod > 0) {
                    $warrantyStart = $request->invoice_date;
                    $warrantyEnd   = $this->calculateWarrantyEnd($warrantyStart, $warrantyType, $warrantyPeriod);
                }

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->_id,
                    'product_id'       => $item['product_id'],
                    'variant_id'       => $item['variant_id'] ?? null,
                    'product_name'     => $productName,
                    'variant_name'     => $variantName,
                    'sku'              => $sku,
                    'barcode'          => $barcode,
                    'hsn_sac'          => $hsnSac,
                    'quantity'         => $qty,
                    'unit'             => $unit,
                    'mrp_price'        => round($mrpPrice, 2),
                    'price'            => round($basePricePerUnit, 2),          // ALWAYS ex-GST base price
                    'sale_price_incl'  => $isIncl ? round($salePrice, 2) : null, // original inclusive price
                    'gst_inclusive'    => $isIncl,
                    'discount'         => round($discountPercent, 2),
                    'tax_percent'      => round($taxPercent, 2),
                    'tax_amount'       => round($itemTax, 2),
                    'cgst_amount'      => round($cgstAmount, 2),
                    'sgst_amount'      => round($sgstAmount, 2),
                    'igst_amount'      => round($igstAmount, 2),
                    'total'            => round($itemFinal, 2),
                    'warranty_type'    => $warrantyType,
                    'warranty_period'  => $warrantyPeriod,
                    'warranty_start'   => $warrantyStart,
                    'warranty_end'     => $warrantyEnd,
                ]);
            }
            // ── END ITEM SAVE LOOP ────────────────────────────────────────────

            return response()->json([
                'success'    => true,
                'invoice_id' => $invoice->_id,
                'message'    => 'Invoice draft created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
     * Show the form for editing the specified invoice.
     */
    public function edit($id)
    {
        $invoice = SalesInvoice::with(['items', 'party'])->findOrFail($id);

        // Check if invoice is editable (only draft status, except for Admin)
        if ($invoice->status !== 'draft') {
            if (!auth()->guard('admin')->check()) {
                return redirect()->route('admin.sales.show', $id)
                    ->with('error', 'Only draft invoices can be edited.');
            }
        }

        // Get all active parties
        $parties = Customer::with(['addresses' => function($query) {
                $query->where('is_default', true);
            }])
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function($party) {
                // Add dynamic opening balance to the party object
                $party->dynamic_opening_balance = $this->getDynamicOpeningBalance($party->_id);
                return $party;
            });
        // Get active salesmen
        $salesmen = Salesman::where('status', 'active')
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::active()->get();
        $mainWarehouse = Warehouse::main()->first();
        $invoiceSetting = InvoiceSetting::first();

        return view('admin.sales.edit', compact('invoice', 'parties', 'salesmen', 'warehouses', 'mainWarehouse', 'invoiceSetting'));
    }

    public function update(Request $request, $id)
    {
        $invoice = SalesInvoice::findOrFail($id);

        if ($invoice->status !== 'draft') {
            if (!auth()->guard('admin')->check()) {
                return response()->json(['success' => false, 'message' => 'Only draft invoices can be updated.'], 403);
            }
        }

        if ($request->has('items') && is_string($request->items)) {
            $request->merge(['items' => json_decode($request->items, true)]);
        }

        $request->validate([
            'party_id'              => 'required|exists:customers,_id',
            'party_type'            => 'required|in:customer,dealer,distributor',
            'warehouse_id'          => 'required|exists:warehouses,_id',
            'invoice_date'          => 'required|date',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required',
            'items.*.product_type'  => 'required|in:simple,variant',
            'items.*.variant_id'    => 'nullable',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.price'         => 'required|numeric|min:0',
            'items.*.mrp_price'     => 'required|numeric|min:0',
            'items.*.discount'      => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent'   => 'nullable|numeric|min:0|max:100',
            'extra_discount'        => 'nullable|numeric|min:0',
            'extra_discount_type'   => 'nullable|in:amount,percent',
            'extra_charge'          => 'nullable|numeric|min:0',
            'amount_paid'           => 'nullable|numeric|min:0',
        ]);

        try {
            $party          = Customer::with(['addresses', 'salesman'])->findOrFail($request->party_id);
            $openingBalance = $this->getDynamicOpeningBalance($request->party_id);

            $unpaidInvoicesBalance = SalesInvoice::where('party_id', $request->party_id)
                ->where('status', '!=', 'draft')
                ->where('payment_status', '!=', 'paid')
                ->where('_id', '!=', $id)
                ->sum('balance_amount');
            $unpaidInvoicesBalance = $this->decimalToFloat($unpaidInvoicesBalance);

            $currentDue           = $openingBalance + $unpaidInvoicesBalance;
            $grandTotalFromReq    = (float) $request->grand_total;
            $amountPaidFromReq    = (float) ($request->amount_paid ?? 0);
            $newInvoiceBalance    = $grandTotalFromReq - $amountPaidFromReq;
            $totalDueAfterInvoice = $currentDue + $newInvoiceBalance;
            $creditLimit          = (float) ($party->credit_limit ?? 0);

            if ($creditLimit > 0 && $totalDueAfterInvoice > $creditLimit) {
                $availableCredit         = max(0, $creditLimit - $currentDue);
                $formattedOpeningBalance = number_format($openingBalance, 2);
                $formattedUnpaidInvoices = number_format($unpaidInvoicesBalance, 2);
                $formattedCurrentDue     = number_format($currentDue, 2);
                $formattedNewBalance     = number_format($newInvoiceBalance, 2);
                $formattedTotalDue       = number_format($totalDueAfterInvoice, 2);
                $formattedCreditLimit    = number_format($creditLimit, 2);
                $formattedAvailable      = number_format($availableCredit, 2);

                throw new \Exception(
                    "❌ Credit Limit Exceeded!\n\n" .
                    "Credit Limit: ₹{$formattedCreditLimit}\n" .
                    "─────────────────────\n" .
                    "Opening Balance: ₹{$formattedOpeningBalance}\n" .
                    "Unpaid Invoices: ₹{$formattedUnpaidInvoices}\n" .
                    "Current Due: ₹{$formattedCurrentDue}\n" .
                    "─────────────────────\n" .
                    "New Invoice Balance: ₹{$formattedNewBalance}\n" .
                    "Total After Invoice: ₹{$formattedTotalDue}\n" .
                    "─────────────────────\n" .
                    "Available Credit: ₹{$formattedAvailable}\n\n" .
                    "💡 Customer can only take items worth ₹{$formattedAvailable} on credit.\n" .
                    "Please collect payment or increase credit limit."
                );
            }

            $billingAddress  = $party->addresses->where('type', 'billing')->where('is_default', true)->first();
            $shippingAddress = $party->addresses->where('type', 'shipping')->where('is_default', true)->first();

            $warehouse      = Warehouse::find($request->warehouse_id);
            $warehouseState = $warehouse->state ?? '';
            $partyState     = $billingAddress ? ($billingAddress->state ?? '') : '';
            $isIntraState   = (!empty($warehouseState) && !empty($partyState) && $warehouseState === $partyState);
            $taxType        = $isIntraState ? 'intra' : 'inter';

            $totalMRP            = 0;
            $totalDiscountAmount = 0;
            $subtotal            = 0;
            $taxTotal            = 0;
            $cgstTotal           = 0;
            $sgstTotal           = 0;
            $igstTotal           = 0;
            $itemsData           = [];

            // ── ITEM TOTALS LOOP ──────────────────────────────────────────────
            foreach ($request->items as $item) {
                $qty        = (float) $item['quantity'];
                $mrpPrice   = (float) $item['mrp_price'];
                $salePrice  = (float) $item['price'];
                $taxPercent = (float) ($item['tax_percent'] ?? 0);
                $isIncl     = !empty($item['gst_inclusive']);

                $totalMRP += $qty * $mrpPrice;

                if ($isIncl && $taxPercent > 0) {
                    $basePricePerUnit = $salePrice * 100 / (100 + $taxPercent);
                    $itemBaseTotal    = $qty * $basePricePerUnit;
                    $grossTotal       = $qty * $salePrice;
                    $itemTax          = $grossTotal - $itemBaseTotal;
                    $itemDiscountAmt  = ($mrpPrice - $salePrice) * $qty;
                } else {
                    $basePricePerUnit = $salePrice;
                    $itemBaseTotal    = $qty * $salePrice;
                    $itemTax          = ($itemBaseTotal * $taxPercent) / 100;
                    $itemDiscountAmt  = ($mrpPrice - $salePrice) * $qty;
                }

                $totalDiscountAmount += $itemDiscountAmt;
                $subtotal            += $itemBaseTotal;
                $taxTotal            += $itemTax;

                if ($isIntraState) {
                    $halfTax             = $itemTax / 2;
                    $cgstTotal          += $halfTax;
                    $sgstTotal          += $halfTax;
                    $item['cgst_amount'] = $halfTax;
                    $item['sgst_amount'] = $halfTax;
                    $item['igst_amount'] = 0;
                } else {
                    $igstTotal           += $itemTax;
                    $item['igst_amount']  = $itemTax;
                    $item['cgst_amount']  = 0;
                    $item['sgst_amount']  = 0;
                }

                $item['_base_price'] = $basePricePerUnit;
                $item['_is_incl']    = $isIncl;
                $itemsData[]         = $item;
            }
            // ── END ITEM TOTALS LOOP ──────────────────────────────────────────

            $extraDiscountValue  = 0;
            $extraDiscountAmount = 0;
            $extraDiscountType   = $request->extra_discount_type ?? 'amount';

            if ($request->filled('extra_discount') && (float)$request->extra_discount > 0) {
                $extraDiscountValue  = (float)$request->extra_discount;
                $extraDiscountAmount = $extraDiscountType === 'percent'
                    ? ($subtotal * $extraDiscountValue) / 100
                    : $extraDiscountValue;
            }

            $extraCharge           = (float) ($request->extra_charge ?? 0);
            $afterDiscountSubtotal = $subtotal - $extraDiscountAmount;
            $grandTotal            = $afterDiscountSubtotal + $taxTotal + $extraCharge;

            $roundOff = 0;
            if ($request->auto_round_off) {
                $rounded    = round($grandTotal);
                $roundOff   = $rounded - $grandTotal;
                $grandTotal = $rounded;
            }

            // Agar is invoice mein pehle se advance_used tha to wapas karo
            $previousAdvanceUsed = (float) ($invoice->advance_used ?? 0);
            if ($previousAdvanceUsed > 0) {
                $party->advance_balance = (float)($party->advance_balance ?? 0) + $previousAdvanceUsed;
                $party->save();
            }

            $amountPaid  = (float) ($request->amount_paid ?? 0);
            $advanceUsed = 0;
            $balance     = $grandTotal - $amountPaid;

            if ($amountPaid <= 0) {
                $paymentStatus = 'unpaid';
            } elseif ($balance <= 0.01) {
                $paymentStatus = 'paid';
                $balance       = 0;
            } else {
                $paymentStatus = 'partial';
            }

            $isConfirmed = ($invoice->status !== 'draft');

            if ($isConfirmed) {
                // 1. Group old items by key
                $oldItems = SalesInvoiceItem::where('sales_invoice_id', $invoice->_id)->get();
                $oldMap = [];
                foreach ($oldItems as $oldItem) {
                    $key = $oldItem->product_id . '_' . ($oldItem->variant_id ?? '');
                    $oldMap[$key] = (float) $oldItem->quantity;
                }

                // 2. Group new items by key
                $newMap = [];
                foreach ($request->items as $item) {
                    $key = $item['product_id'] . '_' . ($item['variant_id'] ?? '');
                    $newMap[$key] = ($newMap[$key] ?? 0.0) + (float) $item['quantity'];
                }

                // 3. Check stock availability for net additions
                $warehouseChanged = ($invoice->warehouse_id !== $request->warehouse_id);

                if ($warehouseChanged) {
                    // Check new warehouse for full new quantities
                    foreach ($request->items as $item) {
                        $stock = WarehouseStock::where('warehouse_id', $request->warehouse_id)
                            ->where('product_id', $item['product_id'])
                            ->where('product_type', $item['variant_id'] ? 'variant' : 'simple')
                            ->when($item['variant_id'], function ($q) use ($item) {
                                return $q->where('variant_id', $item['variant_id']);
                            })
                            ->first();
                        if (!$stock || $stock->quantity < $item['quantity']) {
                            throw new \Exception("Insufficient stock in new warehouse for item " . ($item['product_name'] ?? ''));
                        }
                    }
                } else {
                    // Check same warehouse for net difference
                    $allKeys = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));
                    foreach ($allKeys as $key) {
                        $oldQty = $oldMap[$key] ?? 0.0;
                        $newQty = $newMap[$key] ?? 0.0;
                        $diff = $newQty - $oldQty;

                        if ($diff > 0.001) {
                            list($productId, $variantId) = explode('_', $key);
                            $stock = WarehouseStock::where('warehouse_id', $invoice->warehouse_id)
                                ->where('product_id', $productId)
                                ->where('product_type', $variantId ? 'variant' : 'simple')
                                ->when($variantId, function ($q) use ($variantId) {
                                    return $q->where('variant_id', $variantId);
                                })
                                ->first();
                            if (!$stock || $stock->quantity < $diff) {
                                throw new \Exception("Insufficient stock for item (needs additional " . $diff . " units)");
                            }
                        }
                    }
                }

                // 4. Apply stock updates and movements
                if ($warehouseChanged) {
                    // Revert old items on old warehouse
                    foreach ($oldItems as $oldItem) {
                        $stock = WarehouseStock::where('warehouse_id', $invoice->warehouse_id)
                            ->where('product_id', $oldItem->product_id)
                            ->where('product_type', $oldItem->variant_id ? 'variant' : 'simple')
                            ->when($oldItem->variant_id, function ($q) use ($oldItem) {
                                return $q->where('variant_id', $oldItem->variant_id);
                            })
                            ->first();
                        if ($stock) {
                            $stock->update(['quantity' => $stock->quantity + $oldItem->quantity]);
                        }
                    }
                    // Delete old warehouse movement
                    WarehouseMovement::where('reference_id', $invoice->_id)->delete();

                    // Deduct new items on new warehouse
                    foreach ($request->items as $item) {
                        $stock = WarehouseStock::where('warehouse_id', $request->warehouse_id)
                            ->where('product_id', $item['product_id'])
                            ->where('product_type', $item['variant_id'] ? 'variant' : 'simple')
                            ->when($item['variant_id'], function ($q) use ($item) {
                                return $q->where('variant_id', $item['variant_id']);
                            })
                            ->first();
                        if ($stock) {
                            $stock->update(['quantity' => $stock->quantity - $item['quantity']]);
                        }
                        WarehouseMovement::create([
                            'warehouse_id' => $request->warehouse_id,
                            'product_id'   => $item['product_id'],
                            'product_type' => $item['variant_id'] ? 'variant' : 'simple',
                            'variant_id'   => $item['variant_id'] ?? null,
                            'type'         => WarehouseMovement::TYPE_SALE,
                            'quantity'     => -$item['quantity'],
                            'reference_id' => $invoice->_id,
                            'remarks'      => "Sales Invoice Updated (Warehouse Changed): {$invoice->invoice_number}",
                        ]);
                    }
                } else {
                    // Apply net difference updates
                    $allKeys = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));
                    WarehouseMovement::where('reference_id', $invoice->_id)->delete();

                    foreach ($allKeys as $key) {
                        $oldQty = $oldMap[$key] ?? 0.0;
                        $newQty = $newMap[$key] ?? 0.0;
                        $diff = $newQty - $oldQty;

                        list($productId, $variantId) = explode('_', $key);
                        $stock = WarehouseStock::where('warehouse_id', $invoice->warehouse_id)
                            ->where('product_id', $productId)
                            ->where('product_type', $variantId ? 'variant' : 'simple')
                            ->when($variantId, function ($q) use ($variantId) {
                                return $q->where('variant_id', $variantId);
                            })
                            ->first();
                        if ($stock && abs($diff) > 0.001) {
                            $stock->update(['quantity' => $stock->quantity - $diff]);
                        }

                        if ($newQty > 0.001) {
                            WarehouseMovement::create([
                                'warehouse_id' => $invoice->warehouse_id,
                                'product_id'   => $productId,
                                'product_type' => $variantId ? 'variant' : 'simple',
                                'variant_id'   => $variantId ?: null,
                                'type'         => WarehouseMovement::TYPE_SALE,
                                'quantity'     => -$newQty,
                                'reference_id' => $invoice->_id,
                                'remarks'      => "Sales Invoice Updated: {$invoice->invoice_number}",
                            ]);
                        }
                    }
                }
            }

            $invoice->update([
                'invoice_type'        => $request->invoice_type,
                'gst_mode'            => $request->gst_mode ?? 'exclusive',
                'invoice_date'        => $request->invoice_date,
                'party_id'            => $request->party_id,
                'salesman_id'         => $party->salesman_id,
                'warehouse_id'        => $request->warehouse_id,
                'billing_address'     => $billingAddress ? $billingAddress->full_address : $request->billing_address,
                'shipping_address'    => $shippingAddress ? $shippingAddress->full_address : $request->shipping_address,
                'payment_terms'       => $request->payment_terms,
                'due_date'            => $request->due_date,
                'po_number'           => $request->po_number,
                'total_mrp'           => round($totalMRP, 2),
                'subtotal'            => round($subtotal, 2),
                'discount_total'      => round($totalDiscountAmount, 2),
                'tax_total'           => round($taxTotal, 2),
                'cgst_total'          => round($cgstTotal, 2),
                'sgst_total'          => round($sgstTotal, 2),
                'igst_total'          => round($igstTotal, 2),
                'tax_type'            => $taxType,
                'extra_discount'      => round($extraDiscountValue, 2),
                'extra_discount_type' => $extraDiscountType,
                'extra_charge'        => round($extraCharge, 2),
                'charge_name'         => $request->charge_name,
                'round_off'           => round($roundOff, 2),
                'grand_total'         => round($grandTotal, 2),
                'total_paid'          => round($amountPaid, 2),
                'balance_amount'      => round($balance, 2),
                'payment_status'      => $paymentStatus,
                'advance_used'        => round($advanceUsed, 2),
                'notes'               => $request->notes,
            ]);

            // Sync SalesPayment records if the invoice is already confirmed/completed
            if ($isConfirmed) {
                $existingPayments = SalesPayment::where('sales_invoice_id', $invoice->_id)->get();
                if ($amountPaid <= 0) {
                    foreach ($existingPayments as $payment) {
                        $payment->delete();
                    }
                } else {
                    $allocations = [
                        [
                            'type'           => 'invoice',
                            'invoice_id'     => (string) $invoice->_id,
                            'invoice_number' => $invoice->invoice_number,
                            'amount'         => round($amountPaid, 2),
                            'description'    => 'Invoice payment at time of generation',
                        ]
                    ];

                    if ($existingPayments->count() > 0) {
                        $firstPayment = $existingPayments->first();
                        $firstPayment->update([
                            'party_id'        => $invoice->party_id,
                            'amount'          => round($amountPaid, 2),
                            'payment_method'  => $request->payment_method ?? $firstPayment->payment_method ?? 'cash',
                            'payment_date'    => $invoice->invoice_date,
                            'notes'           => "Cash paid: ₹" . number_format($amountPaid, 2) . " (updated during invoice edit)",
                            'allocations'     => $allocations,
                            'payment_type'    => 'payment_in',
                            'payment_subtype' => 'sales_payment',
                        ]);

                        for ($i = 1; $i < $existingPayments->count(); $i++) {
                            $existingPayments[$i]->delete();
                        }
                    } else {
                        SalesPayment::create([
                            'payment_number'   => null,
                            'sales_invoice_id' => $invoice->_id,
                            'party_id'         => $invoice->party_id,
                            'amount'           => round($amountPaid, 2),
                            'payment_method'   => $request->payment_method ?? 'cash',
                            'payment_date'     => $invoice->invoice_date,
                            'status'           => 'completed',
                            'notes'            => "Cash paid: ₹" . number_format($amountPaid, 2) . " (created during invoice edit)",
                            'payment_type'     => 'payment_in',
                            'payment_subtype'  => 'sales_payment',
                            'allocations'      => $allocations,
                            'created_by'       => Auth::guard('admin')->id(),
                        ]);
                    }
                }
            }

            SalesInvoiceItem::where('sales_invoice_id', $invoice->_id)->delete();

            // ── ITEM SAVE LOOP ────────────────────────────────────────────────
            foreach ($request->items as $index => $item) {
                $qty              = (float) $item['quantity'];
                $mrpPrice         = (float) $item['mrp_price'];
                $salePrice        = (float) $item['price'];
                $discountPercent  = (float) ($item['discount'] ?? 0);
                $taxPercent       = (float) ($item['tax_percent'] ?? 0);
                $cgstAmount       = $itemsData[$index]['cgst_amount'] ?? 0;
                $sgstAmount       = $itemsData[$index]['sgst_amount'] ?? 0;
                $igstAmount       = $itemsData[$index]['igst_amount'] ?? 0;
                $isIncl           = !empty($itemsData[$index]['_is_incl']);
                $basePricePerUnit = (float) ($itemsData[$index]['_base_price'] ?? $salePrice);

                if ($item['product_type'] === 'simple') {
                    $product     = SimpleProduct::find($item['product_id']);
                    $productName = $product->name ?? 'Unknown Product';
                    $sku         = $product->sku_code ?? '';
                    $barcode     = $product->barcode ?? '';
                    $unit        = $product->unit ?? 'PCS';
                    $hsnSac      = $product->hsn_code ?? '';
                    $variantName = null;
                } else {
                    $product     = VariantProduct::find($item['product_id']);
                    $productName = $product->name ?? 'Unknown Product';
                    $hsnSac      = $product->hsn_code ?? '';
                    $variants    = $product->variants ?? [];
                    $variant     = collect($variants)->first(function($v) use ($item) {
                        return (string)(isset($v['_id']) ? $v['_id'] : null) === $item['variant_id'];
                    });
                    $variantName = $variant['name'] ?? null;
                    $sku         = $variant['sku_code'] ?? '';
                    $barcode     = $variant['barcode'] ?? '';
                    $unit        = $variant['unit'] ?? 'PCS';
                }

                $itemBaseTotal = $qty * $basePricePerUnit;
                $itemTax       = $cgstAmount + $sgstAmount + $igstAmount;
                $itemFinal     = $isIncl ? ($qty * $salePrice) : ($itemBaseTotal + $itemTax);

                $warrantyType   = $item['warranty_type'] ?? 'none';
                $warrantyPeriod = (int) ($item['warranty_period'] ?? 0);
                $warrantyStart  = null;
                $warrantyEnd    = null;

                if ($warrantyType !== 'none' && $warrantyPeriod > 0) {
                    $warrantyStart = $request->invoice_date;
                    $warrantyEnd   = $this->calculateWarrantyEnd($warrantyStart, $warrantyType, $warrantyPeriod);
                }

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->_id,
                    'product_id'       => $item['product_id'],
                    'variant_id'       => $item['variant_id'] ?? null,
                    'product_name'     => $productName,
                    'variant_name'     => $variantName,
                    'sku'              => $sku,
                    'barcode'          => $barcode,
                    'hsn_sac'          => $hsnSac,
                    'quantity'         => $qty,
                    'unit'             => $unit,
                    'mrp_price'        => round($mrpPrice, 2),
                    'price'            => round($basePricePerUnit, 2),           // ALWAYS ex-GST base price
                    'sale_price_incl'  => $isIncl ? round($salePrice, 2) : null, // original inclusive price
                    'gst_inclusive'    => $isIncl,
                    'discount'         => round($discountPercent, 2),
                    'tax_percent'      => round($taxPercent, 2),
                    'tax_amount'       => round($itemTax, 2),
                    'cgst_amount'      => round($cgstAmount, 2),
                    'sgst_amount'      => round($sgstAmount, 2),
                    'igst_amount'      => round($igstAmount, 2),
                    'total'            => round($itemFinal, 2),
                    'warranty_type'    => $warrantyType,
                    'warranty_period'  => $warrantyPeriod,
                    'warranty_start'   => $warrantyStart,
                    'warranty_end'     => $warrantyEnd,
                ]);
            }
            // ── END ITEM SAVE LOOP ────────────────────────────────────────────

            return response()->json([
                'success'    => true,
                'invoice_id' => $invoice->_id,
                'message'    => 'Invoice draft updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Generate invoice (confirm and deduct stock)
     */
/**
     * Generate invoice (confirm and deduct stock)
     * PASTE THIS generate() method inside SalesInvoiceController
     * replacing the existing generate() method
     */
    public function generate($id)
    {
        try {
            $invoice = SalesInvoice::with(['items', 'party'])->findOrFail($id);

            if ($invoice->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice is already generated or completed.'
                ], 400);
            }

            // Check stock availability
            foreach ($invoice->items as $item) {
                $stock = WarehouseStock::where('warehouse_id', $invoice->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->where('product_type', $item->variant_id ? 'variant' : 'simple')
                    ->when($item->variant_id, function ($q) use ($item) {
                        return $q->where('variant_id', $item->variant_id);
                    })
                    ->first();

                if (!$stock || $stock->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock for {$item->product_name}" . ($item->variant_name ? " - {$item->variant_name}" : ""));
                }
            }

            // Deduct stock
            foreach ($invoice->items as $item) {
                $stock = WarehouseStock::where('warehouse_id', $invoice->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->where('product_type', $item->variant_id ? 'variant' : 'simple')
                    ->when($item->variant_id, function ($q) use ($item) {
                        return $q->where('variant_id', $item->variant_id);
                    })
                    ->first();

                if ($stock) {
                    $stock->update(['quantity' => $stock->quantity - $item->quantity]);

                    WarehouseMovement::create([
                        'warehouse_id' => $invoice->warehouse_id,
                        'product_id'   => $item->product_id,
                        'product_type' => $item->variant_id ? 'variant' : 'simple',
                        'variant_id'   => $item->variant_id ?? null,
                        'type'         => WarehouseMovement::TYPE_SALE,
                        'quantity'     => -$item->quantity,
                        'reference_id' => $invoice->_id,
                        'remarks'      => "Sales Invoice Generated: {$invoice->invoice_number}",
                    ]);
                }
            }

            $cashPaid   = (float) ($invoice->total_paid ?? 0);
            $grandTotal = (float) $invoice->grand_total;

            /* ================= CREDIT NOTE AUTO-ADJUSTMENT ================= */
            $activeCreditNotes = \App\Models\CreditNote::where('party_id', $invoice->party_id)
                ->whereIn('status', ['active', 'partial'])
                ->where('remaining_amount', '>', 0)
                ->orderBy('credit_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            $totalCreditApplied = 0;
            $appliedCNNumbers   = [];
            $remainingToAdjust  = max(0, $grandTotal - $cashPaid);

            foreach ($activeCreditNotes as $cn) {
                if ($remainingToAdjust <= 0) break;

                $cnRemaining = $cn->remaining_amount;
                if ($cnRemaining instanceof \MongoDB\BSON\Decimal128) {
                    $cnRemaining = (float) $cnRemaining->__toString();
                } else {
                    $cnRemaining = (float) $cnRemaining;
                }

                $applyAmount = min($cnRemaining, $remainingToAdjust);

                $cn->used_amount      = (float) $cn->used_amount + $applyAmount;
                $cn->remaining_amount = $cnRemaining - $applyAmount;

                if ($cn->remaining_amount <= 0.001) {
                    $cn->remaining_amount = 0;
                    $cn->status           = 'settled';
                } else {
                    $cn->status = 'partial';
                }
                $cn->save();

                $totalCreditApplied  += $applyAmount;
                $remainingToAdjust   -= $applyAmount;
                $appliedCNNumbers[]   = $cn->credit_note_number;
            }

            /* ================= PAYMENT CALCULATION ================= */
            $amountPaid = $cashPaid + $totalCreditApplied;
            if ($amountPaid > $grandTotal) $amountPaid = $grandTotal;

            $balance = max(0, $grandTotal - $amountPaid);

            if ($amountPaid <= 0) {
                $paymentStatus = 'unpaid';
            } elseif ($balance <= 0.01) {
                $paymentStatus = 'paid';
                $balance       = 0;
            } else {
                $paymentStatus = 'partial';
            }

            // Update invoice payment fields
            $invoice->total_paid          = round($amountPaid, 2);
            $invoice->balance_amount      = round($balance, 2);
            $invoice->payment_status      = $paymentStatus;
            $invoice->credit_note_applied = round($totalCreditApplied, 2);

            if (!empty($appliedCNNumbers)) {
                $invoice->credit_note_numbers = implode(', ', $appliedCNNumbers);
            }
            $invoice->save();

            /* ================= CREATE PAYMENT RECORD ================= */
            // ✅ FIX: payment_type, payment_subtype, allocations — sabhi fields set karo
            // Taaki LedgerController sahi se match kar sake
            if ($amountPaid > 0) {
                $notes       = [];
                $allocations = [];

                // Cash paid allocation (invoice ke against)
                if ($cashPaid > 0) {
                    $notes[] = "Cash paid: ₹" . number_format($cashPaid, 2);
                    $allocations[] = [
                        'type'           => 'invoice',
                        'invoice_id'     => (string) $invoice->_id,
                        'invoice_number' => $invoice->invoice_number,
                        'amount'         => round($cashPaid, 2),
                        'description'    => 'Invoice payment at time of generation',
                    ];
                }

                // Credit note allocation
                if ($totalCreditApplied > 0) {
                    $notes[] = "Credit note adjusted: ₹" . number_format($totalCreditApplied, 2)
                             . " (" . implode(', ', $appliedCNNumbers) . ")";
                    foreach ($appliedCNNumbers as $cnNum) {
                        $allocations[] = [
                            'type'               => 'credit_note',
                            'credit_note_number' => $cnNum,
                            'amount'             => round($totalCreditApplied / count($appliedCNNumbers), 2),
                            'description'        => 'Credit Note Adjustment: ' . $cnNum,
                        ];
                    }
                }

                SalesPayment::create([
                    'payment_number'   => null,              // invoice-time payment has no payment_number
                    'sales_invoice_id' => $invoice->_id,
                    'party_id'         => $invoice->party_id,
                    'amount'           => round($amountPaid, 2),
                    'payment_method'   => request()->payment_method ?? 'cash',
                    'payment_date'     => now(),
                    'status'           => 'completed',
                    'notes'            => implode("\n", $notes),
                    'payment_type'     => 'payment_in',      // ✅ FIXED
                    'payment_subtype'  => 'sales_payment',   // ✅ FIXED
                    'allocations'      => $allocations,      // ✅ FIXED
                    'created_by'       => Auth::guard('admin')->id(),
                ]);
            }

            $invoice->update(['status' => 'confirmed']);

            $msg = 'Invoice generated successfully. Stock deducted from warehouse.';
            if ($totalCreditApplied > 0) {
                $msg .= ' Credit note adjusted: ₹' . number_format($totalCreditApplied, 2)
                      . ' (' . implode(', ', $appliedCNNumbers) . ')';
            }

            return response()->json([
                'success'              => true,
                'invoice_id'           => $invoice->_id,
                'message'              => $msg,
                'credit_note_applied'  => $totalCreditApplied,
                'credit_note_numbers'  => $appliedCNNumbers,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Calculate dynamic opening balance (original opening minus payments allocated to opening balance)
     */
    private function getDynamicOpeningBalance($partyId)
    {
        $party = Customer::find($partyId);
        if (!$party) return 0;

        $originalOpening = (float) ($party->opening_balance ?? 0);

        // Get total payments allocated to opening balance for this party
        $openingPaid = SalesPayment::where('party_id', (string)$partyId)
            ->where('payment_type', 'payment_in')
            ->get()
            ->sum(function($payment) {
                $allocations = $payment->allocations ?? [];
                return collect($allocations)
                    ->where('type', 'opening_balance')
                    ->sum('amount');
            });

        // Dynamic opening balance = original - paid
        return max(0, $originalOpening - $openingPaid);
    }
    /**
     * Delete a sales invoice.
     */
    public function destroy($id)
    {
        try {
            $invoice = SalesInvoice::with(['items'])->findOrFail($id);

            // Only allow deletion of draft invoices
            if ($invoice->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft invoices can be deleted.'
                ], 403);
            }

            // Delete items
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
     * Get products from main warehouse
     */
    public function getMainWarehouseProducts(Request $request)
    {
        try {
            // Use warehouse_id from request, fallback to main warehouse
            if ($request->filled('warehouse_id')) {
                $warehouse = Warehouse::find($request->warehouse_id);
            } else {
                $warehouse = Warehouse::main()->first();
            }

            if (!$warehouse) {
                return response()->json(['products' => []]);
            }

            $partyId = $request->party_id;
            $partyInvoiceIds = [];
            if ($partyId) {
                $partyInvoiceIds = SalesInvoice::where('party_id', $partyId)
                    ->where('status', '!=', 'cancelled')
                    ->pluck('id')
                    ->toArray();
            }

            /* ================= SIMPLE PRODUCTS ================= */
            $simpleStocks = WarehouseStock::where('warehouse_id', $warehouse->_id)
                ->where('product_type', 'simple')
                ->where('quantity', '>', 0)
                ->pluck('product_id');

            $simpleProducts = SimpleProduct::whereIn('_id', $simpleStocks)
                ->where('status', 'active')
                ->get()
                ->map(function ($product) use ($warehouse, $partyInvoiceIds) {
                    $stock = WarehouseStock::where('warehouse_id', $warehouse->_id)
                        ->where('product_id', $product->_id)
                        ->where('product_type', 'simple')
                        ->first();

                    $lastSalePrice = null;
                    if (!empty($partyInvoiceIds)) {
                        $lastSoldItem = SalesInvoiceItem::whereIn('sales_invoice_id', $partyInvoiceIds)
                            ->where('product_id', $product->_id)
                            ->whereNull('variant_id')
                            ->orderBy('created_at', 'desc')
                            ->first();
                        if ($lastSoldItem) {
                            $lastSalePrice = $lastSoldItem->gst_inclusive 
                                ? (float)$lastSoldItem->sale_price_incl 
                                : (float)$lastSoldItem->price;
                        }
                    }

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
                        'hsn_code' => $product->hsn_code,
                        'warranty_type' => $product->warranty_unit ?? 'none',
                        'warranty_period' => (int) ($product->warranty_duration ?? 0),
                        'tax_percent' => (float) ($product->gst ?? 0),
                        'last_sale_price' => $lastSalePrice,
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
                $product = VariantProduct::where('_id', $productId)
                    ->where('status', 'active')
                    ->first();
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

                    $lastSalePrice = null;
                    if (!empty($partyInvoiceIds)) {
                        $lastSoldItem = SalesInvoiceItem::whereIn('sales_invoice_id', $partyInvoiceIds)
                            ->where('product_id', $product->_id)
                            ->where('variant_id', (string)$variantId)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        if ($lastSoldItem) {
                            $lastSalePrice = $lastSoldItem->gst_inclusive 
                                ? (float)$lastSoldItem->sale_price_incl 
                                : (float)$lastSoldItem->price;
                        }
                    }

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
                        'hsn_code' => $product->hsn_code,
                        'warranty_type' => $product->warranty_unit ?? 'none',
                        'warranty_period' => (int) ($product->warranty_duration ?? 0),
                        'tax_percent' => (float) ($product->gst ?? 0),
                        'last_sale_price' => $lastSalePrice,
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
    }

 public function getPartyDetails($id)
{
    $party = Customer::with(['addresses', 'salesman'])->findOrFail($id);

    $billing = $party->addresses
        ->where('type', 'billing')
        ->where('is_default', true)
        ->first();

    $shipping = $party->addresses
        ->where('type', 'shipping')
        ->where('is_default', true)
        ->first();

    $dynamicOpeningBalance = $this->getDynamicOpeningBalance($id);

    // ✅ Calculate total remaining amount from active credit notes
    $creditNotes = \App\Models\CreditNote::where('party_id', (string)$party->_id)
        ->whereIn('status', ['active', 'partial'])
        ->where('remaining_amount', '>', 0)
        ->get();

    $totalCreditRemaining = 0;
    foreach ($creditNotes as $cn) {
        $remaining = $cn->remaining_amount;
        if ($remaining instanceof \MongoDB\BSON\Decimal128) {
            $totalCreditRemaining += (float) $remaining->__toString();
        } else {
            $totalCreditRemaining += (float) $remaining;
        }
    }

    return response()->json([
        'success' => true,
        'party' => [
            'id'               => (string)$party->_id,
            'name'             => $party->name,
            'phone'            => $party->phone,
            'email'            => $party->email,
            'party_type'       => $party->party_type,
            'party_type_text'  => ucfirst($party->party_type),
            'opening_balance'  => (float) ($party->opening_balance ?? 0),
            'dynamic_opening_balance' => $dynamicOpeningBalance,
            'advance_balance'  => (float) ($party->advance_balance ?? 0),
            'credit_limit'     => (float) ($party->credit_limit ?? 0),
            'credit_notes_remaining' => $totalCreditRemaining, // ✅ Add this
            'salesman_id'      => $party->salesman_id,
            'salesman_name'    => $party->salesman ? $party->salesman->name : null,
            'billing_address'  => $billing?->full_address ?? '',
            'shipping_address' => $shipping?->full_address ?? '',
            'billing_state'    => $billing?->state ?? '',
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

            // Only allow payments for confirmed/completed invoices
            if (!in_array($invoice->status, ['confirmed', 'completed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payments can only be added to confirmed invoices.'
                ], 403);
            }

            // Create payment
            $payment = SalesPayment::create([
                'sales_invoice_id' => $invoice->_id,
                'party_id' => $invoice->party_id,
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
    public function getPartyCreditStatus($partyId)
    {
        try {
            $party = Customer::findOrFail($partyId);

           $openingBalance = $this->getDynamicOpeningBalance($partyId);
            $advanceBalance         = (float) ($party->advance_balance ?? 0); // NEW

            $unpaidInvoicesBalance = SalesInvoice::where('party_id', $partyId)
                ->where('status', '!=', 'draft')
                ->where('payment_status', '!=', 'paid')
                ->sum('balance_amount');

            $unpaidInvoicesBalance = $this->decimalToFloat($unpaidInvoicesBalance);
            $currentDue            = $openingBalance + $unpaidInvoicesBalance;

            $creditLimit     = (float) ($party->credit_limit ?? 0);
            $availableCredit = $creditLimit > 0 ? max(0, $creditLimit - $currentDue) : null;

            $usagePercent = 0;
            if ($creditLimit > 0 && $currentDue > 0) {
                $usagePercent = min(100, ($currentDue / $creditLimit) * 100);
            }

            return response()->json([
                'success' => true,
                'credit_info' => [
                    'party_name'      => $party->name,
                    'opening_balance' => $openingBalance,
                    'advance_balance' => $advanceBalance,  // NEW
                    'unpaid_invoices' => $unpaidInvoicesBalance,
                    'current_due'     => $currentDue,
                    'credit_limit'    => $creditLimit,
                    'available_credit'=> $availableCredit,
                    'has_limit'       => $creditLimit > 0,
                    'is_exceeded'     => $creditLimit > 0 && $currentDue >= $creditLimit,
                    'usage_percent'   => round($usagePercent, 2),
                    'warning_level'   => $this->getWarningLevel($usagePercent)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

/**
 * Helper to determine warning level based on usage percentage
 */
    private function getWarningLevel($percent)
    {
        if ($percent >= 100) return 'danger';
        if ($percent >= 80) return 'warning';
        if ($percent > 0) return 'info';
        return 'safe';
    }
    public function getNextInvoiceNumber(Request $request)
    {
        $type = $request->invoice_type ?? 'gst';
        return response()->json([
            'invoice_number' => $this->generateInvoiceNumber($type)
        ]);
    }
}
