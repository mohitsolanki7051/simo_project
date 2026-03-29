<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\WarehouseMovement;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Decimal128;

class SalesReturnController extends Controller
{
    /**
     * Generate return number with format: SIM/SR/24-25/000001
     */
    private function generateReturnNumber()
    {
        $financialYear = $this->getFinancialYear();

        $lastReturn = SalesReturn::where('return_number', 'regex', "/^SIM\/SR\/{$financialYear}\/\d+$/")
            ->orderBy('return_number', 'desc')
            ->first();

        if ($lastReturn) {
            preg_match('/(\d+)$/', $lastReturn->return_number, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "SIM/SR/{$financialYear}/{$newNumber}";
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
     * Display a listing of sales returns.
     */
/**
 * Display a listing of sales returns.
 */
public function index(Request $request)
{
    $query = SalesReturn::with(['party', 'invoice', 'warehouse'])
        ->orderBy('created_at', 'desc');

    // Date filter
    if ($request->filled('date_from')) {
        $query->whereDate('return_date', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('return_date', '<=', $request->date_to);
    }

    // Return number search
    if ($request->filled('return_number')) {
        $query->where('return_number', 'like', '%' . $request->return_number . '%');
    }

    // Invoice number search
    if ($request->filled('invoice_number')) {
        $invoiceIds = SalesInvoice::where('invoice_number', 'like', '%' . $request->invoice_number . '%')
            ->pluck('_id');
        $query->whereIn('sales_invoice_id', $invoiceIds);
    }

    // Party filter
    if ($request->filled('party_id')) {
        $query->where('party_id', $request->party_id);
    }

    // Status filter
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $returns = $query->paginate(20)->withQueryString();

    /* ================= STATS QUERY - ALL FILTERS APPLY ================= */
    $statsQuery = SalesReturn::where('status', 'completed');

    // Apply same filters to stats query
    if ($request->filled('date_from')) {
        $statsQuery->whereDate('return_date', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $statsQuery->whereDate('return_date', '<=', $request->date_to);
    }
    if ($request->filled('return_number')) {
        $statsQuery->where('return_number', 'like', '%' . $request->return_number . '%');
    }
    if ($request->filled('invoice_number')) {
        $invoiceIds = SalesInvoice::where('invoice_number', 'like', '%' . $request->invoice_number . '%')
            ->pluck('_id');
        $statsQuery->whereIn('sales_invoice_id', $invoiceIds);
    }
    if ($request->filled('party_id')) {
        $statsQuery->where('party_id', $request->party_id);
    }

    // Calculate stats with Decimal128 conversion
    $totalReturnAmountRaw = (clone $statsQuery)->sum('total_return_amount');
    $totalReturnAmount = $this->decimalToFloat($totalReturnAmountRaw);
    $totalReturns = (clone $statsQuery)->count();

    // Get active parties for filter dropdown
    $parties = Customer::where('status', 'active')->orderBy('name')->get();

    return view('admin.sales-returns.index', compact(
        'returns',
        'totalReturnAmount',
        'totalReturns',
        'parties'
    ));
}

/**
 * Helper function to convert Decimal128 to float
 */
private function decimalToFloat($value)
{
    if ($value instanceof \MongoDB\BSON\Decimal128) {
        return (float) $value->__toString();
    }
    return (float) $value;
}

    /**
     * Show the form for creating a new sales return.
     */
    public function create()
    {
        $returnNumber = $this->generateReturnNumber();
        $warehouses = Warehouse::active()->get();
        $mainWarehouse = Warehouse::main()->first();

        return view('admin.sales-returns.create', compact('returnNumber', 'warehouses', 'mainWarehouse'));
    }


public function searchInvoices(Request $request)
{
    $search = $request->get('search', '');
    $partyId = $request->get('party_id');

    $query = SalesInvoice::with(['party', 'warehouse'])
          ->whereIn('status', [
        'confirmed',
        'completed',
        'partially_returned'
    ]);

    if ($partyId) {
        $query->where('party_id', $partyId);
    }

    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('invoice_number', 'like', '%' . $search . '%')
              ->orWhereHas('party', function($pq) use ($search) {
                  $pq->where('name', 'like', '%' . $search . '%')
                     ->orWhere('phone', 'like', '%' . $search . '%');
              });
        });
    }

    $invoices = $query->limit(20)->get();

    return response()->json([
        'success' => true,
        'invoices' => $invoices->map(function($invoice) {
            return [
                'id' => (string)$invoice->_id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date->format('d-m-Y'),
                'party_name' => $invoice->party?->name ?? 'N/A',
                'party_phone' => $invoice->party?->phone ?? '-',
                'grand_total' => (float)$invoice->grand_total,
                'balance_amount' => (float)$invoice->balance_amount,
                'payment_status' => $invoice->payment_status, // ✅ Status bhi bhejo UI ke liye
                'warehouse_name' => $invoice->warehouse?->name ?? 'N/A',
            ];
        })
    ]);
}

public function getInvoiceDetails($id)
{
    $invoice = SalesInvoice::with(['party', 'warehouse', 'items'])->findOrFail($id);

    // ✅ Already returned quantities
    $completedReturns = SalesReturn::where('sales_invoice_id', $id)
        ->where('status', 'completed')
        ->with('items')
        ->get();

    $returnedQtys = [];
    foreach ($completedReturns as $return) {
        foreach ($return->items as $item) {
            $key = (string)$item->sales_invoice_item_id;
            $returnedQtys[$key] = ($returnedQtys[$key] ?? 0) + $item->quantity;
        }
    }

    return response()->json([
        'success' => true,
        'invoice' => [
            'id' => (string)$invoice->_id,
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date->format('d-m-Y'),
            'party_id' => (string)$invoice->party_id,
            'party_name' => $invoice->party?->name ?? 'N/A',
            'party_phone' => $invoice->party?->phone ?? '-',
            'warehouse_id' => (string)$invoice->warehouse_id,
            'warehouse_name' => $invoice->warehouse?->name ?? 'N/A',
            'grand_total' => (float)$invoice->grand_total,
            'balance_amount' => (float)$invoice->balance_amount,
            'invoice_type' => $invoice->invoice_type,
            'is_gst' => $invoice->isGstInvoice(),

            // ✅ Extra discount info
            'extra_discount' => (float)($invoice->extra_discount ?? 0),
            'extra_discount_type' => $invoice->extra_discount_type ?? 'amount',
        ],
        'items' => $invoice->items->map(function($item) use ($returnedQtys) {
            $itemId = (string)$item->_id;
            $alreadyReturned = $returnedQtys[$itemId] ?? 0;
            $maxReturnable = $item->quantity - $alreadyReturned;

            return [
                'id' => $itemId,
                'product_id' => (string)$item->product_id,
                'variant_id' => $item->variant_id ? (string)$item->variant_id : null,
                'product_name' => $item->variant_name ? $item->variant_name : $item->product_name,
                'quantity' => (float)$item->quantity,
                'already_returned' => $alreadyReturned,
                'max_return_qty' => max(0, $maxReturnable),
                'price' => (float)$item->price,
                'tax_percent' => (float)$item->tax_percent,
                'tax_amount' => (float)$item->tax_amount,
                'total' => (float)$item->total,
            ];
        })
    ]);
}
public function store(Request $request)
{
    // Decode items JSON if string
    if ($request->has('items') && is_string($request->items)) {
        $request->merge([
            'items' => json_decode($request->items, true)
        ]);
    }

    // Validation
    $request->validate([
        'sales_invoice_id' => 'required|exists:sales_invoices,_id',
        'return_date' => 'required|date',
        'reason' => 'nullable|string',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.invoice_item_id' => 'required',
        'items.*.return_qty' => 'required|numeric|min:0.01',
    ]);

    try {

        $invoice = SalesInvoice::findOrFail($request->sales_invoice_id);

        // Check if invoice is eligible for return
        if (!in_array($invoice->status, ['confirmed', 'completed', 'partially_returned'])) {
    throw new \Exception('This invoice cannot be returned.');
}

        // Calculate already returned quantities from COMPLETED returns
        $completedReturns = SalesReturn::where('sales_invoice_id', $invoice->_id)
            ->where('status', 'completed')
            ->with('items')
            ->get();

        $returnedQtys = [];
        foreach ($completedReturns as $return) {
            foreach ($return->items as $ritem) {
                $key = (string)$ritem->sales_invoice_item_id;
                $returnedQtys[$key] = ($returnedQtys[$key] ?? 0) + $ritem->quantity;
            }
        }

        // Check DRAFT returns
        $draftReturn = SalesReturn::where('sales_invoice_id', $invoice->_id)
            ->where('status', 'draft')
            ->first();

        if ($draftReturn) {
            throw new \Exception('A draft return already exists for this invoice. Please complete or delete it first.');
        }

        // Get extra discount info from invoice
        $extraDiscount = (float)($invoice->extra_discount ?? 0);
        $extraDiscountType = $invoice->extra_discount_type ?? 'amount';
        $isGstInvoice = $invoice->isGstInvoice();

        // Calculate items
        $totalReturnQty = 0;
        $itemsSubtotal = 0;
        $totalTax = 0;
        $itemsData = [];

        foreach ($request->items as $item) {
            $invoiceItem = SalesInvoiceItem::find($item['invoice_item_id']);
            if (!$invoiceItem) {
                throw new \Exception('Invalid invoice item.');
            }

            $returnQty = (float)$item['return_qty'];

            if ($returnQty <= 0) {
                throw new \Exception('Return quantity must be greater than 0.');
            }

            // Check remaining quantity
            $alreadyReturned = $returnedQtys[(string)$invoiceItem->_id] ?? 0;
            $maxReturnable = $invoiceItem->quantity - $alreadyReturned;

            if ($returnQty > $maxReturnable) {
                throw new \Exception(
                    "Cannot return more than remaining quantity for {$invoiceItem->product_name}. " .
                    "Already returned: {$alreadyReturned}, Remaining: {$maxReturnable}"
                );
            }

            // Calculate item subtotal (without tax)
            $itemSubtotal = $returnQty * $invoiceItem->price;
            $itemsSubtotal += $itemSubtotal;

            // Calculate tax
            $itemTax = 0;
            if ($isGstInvoice) {
                $itemTax = ($itemSubtotal * $invoiceItem->tax_percent) / 100;
                $totalTax += $itemTax;
            }

            $itemsData[] = [
                'invoice_item' => $invoiceItem,
                'return_qty' => $returnQty,
                'item_subtotal' => $itemSubtotal,
                'item_tax' => $itemTax,
            ];
        }

        // ✅ Calculate discount on subtotal
        $discountAmount = 0;
        if ($extraDiscount > 0 && $itemsSubtotal > 0) {
            if ($extraDiscountType === 'percent') {
                $discountAmount = ($itemsSubtotal * $extraDiscount) / 100;
            } else {
                $discountAmount = $extraDiscount;
            }
        }

        // ✅ Final return amount = Subtotal + Tax - Discount
        $totalReturnAmount = $itemsSubtotal + $totalTax - $discountAmount;
        $totalReturnQty = array_sum(array_column($itemsData, 'return_qty'));

        // Create Sales Return (Draft) - WITH ALL FIELDS
        $return = SalesReturn::create([
            'return_number' => $this->generateReturnNumber(),
            'sales_invoice_id' => $invoice->_id,
            'party_id' => $invoice->party_id,
            'warehouse_id' => $invoice->warehouse_id,
            'return_date' => $request->return_date,
            'reason' => $request->reason,
            'notes' => $request->notes,
            'total_return_qty' => $totalReturnQty,
            'subtotal' => $itemsSubtotal,                 // ✅ New field
            'total_tax' => $totalTax,                     // ✅ New field
            'discount_amount' => $discountAmount,         // ✅ New field
            'total_return_amount' => $totalReturnAmount,
            'status' => 'draft',
            'created_by' => Auth::guard('admin')->id(),
        ]);

        // Create Return Items - WITH ALL FIELDS
        foreach ($itemsData as $data) {
            $invoiceItem = $data['invoice_item'];
            $itemSubtotal = $data['item_subtotal'];
            $itemTax = $data['item_tax'];
            $itemTotal = $itemSubtotal + $itemTax;

            SalesReturnItem::create([
                'sales_return_id' => $return->_id,
                'sales_invoice_item_id' => $invoiceItem->_id,
                'product_id' => $invoiceItem->product_id,
                'variant_id' => $invoiceItem->variant_id,
                'product_name' => $invoiceItem->product_name,
                'variant_name' => $invoiceItem->variant_name,
                'quantity' => $data['return_qty'],
                'price' => $invoiceItem->price,
                'tax_percent' => $isGstInvoice ? $invoiceItem->tax_percent : 0,
                'tax_amount' => $itemTax,
                'subtotal' => $itemSubtotal,              // ✅ New field
                'total' => $itemTotal,
            ]);
        }



        return response()->json([
            'success' => true,
            'return_id' => $return->_id,
            'message' => 'Sales return draft created successfully'
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function complete($id)
{
    try {
        $return = SalesReturn::with(['items', 'invoice', 'party'])->findOrFail($id);

        if ($return->status !== 'draft') {
            throw new \Exception('Return can only be completed from draft status.');
        }

        $invoice = $return->invoice;

        /* ================= STOCK RETURN ================= */
        foreach ($return->items as $item) {
            $stock = WarehouseStock::where('warehouse_id', $return->warehouse_id)
                ->where('product_id', $item->product_id)
                ->where('product_type', $item->variant_id ? 'variant' : 'simple')
                ->when($item->variant_id, function ($q) use ($item) {
                    return $q->where('variant_id', $item->variant_id);
                })
                ->first();

            if ($stock) {
                $stock->quantity += $item->quantity;
                $stock->save();
            } else {
                WarehouseStock::create([
                    'warehouse_id'    => $return->warehouse_id,
                    'product_id'      => $item->product_id,
                    'product_type'    => $item->variant_id ? 'variant' : 'simple',
                    'variant_id'      => $item->variant_id ?? null,
                    'quantity'        => $item->quantity,
                    'min_stock_alert' => 0,
                ]);
            }

            WarehouseMovement::create([
                'warehouse_id' => $return->warehouse_id,
                'product_id'   => $item->product_id,
                'product_type' => $item->variant_id ? 'variant' : 'simple',
                'variant_id'   => $item->variant_id ?? null,
                'type'         => WarehouseMovement::TYPE_RETURN,
                'quantity'     => $item->quantity,
                'reference_id' => $return->_id,
                'remarks'      => "Sales Return: {$return->return_number}",
            ]);
        }

        /* ================= CREDIT NOTE CREATION ================= */
        $returnAmount = (float) $return->total_return_amount;
        $creditNote   = $this->createCreditNote($return, $returnAmount);

        /* ================= AUTO-ADJUST CREDIT NOTE AGAINST INVOICE ================= */
        $invoice->refresh();
        $currentBalance = (float) $invoice->balance_amount;

        if ($currentBalance > 0) {
            // How much of the credit note can be applied right now?
            $adjustAmount = min($returnAmount, $currentBalance);

            // Deduct from invoice balance
            $newBalance = round($currentBalance - $adjustAmount, 2);

            // Update the credit note's used / remaining amounts
            $creditNote->used_amount      = $adjustAmount;
            $creditNote->remaining_amount = round($returnAmount - $adjustAmount, 2);

            // Credit note status
            if ($creditNote->remaining_amount <= 0) {
                $creditNote->status = 'settled';        // fully used up
            } else {
                $creditNote->status = 'partial'; // only part was enough to clear balance
            }
            $creditNote->save();

            // Determine invoice payment_status
            if ($newBalance <= 0) {
                $newBalance       = 0;
                $newPaymentStatus = (float)$invoice->total_paid > 0 ? 'paid' : 'cancelled';      // fully returned / settled via credit
            } else {
                $newPaymentStatus = 'unpaid';          // still some due remaining
            }

            $invoice->balance_amount  = $newBalance;
            $invoice->payment_status  = $newPaymentStatus;

        } else {
            // Invoice already paid — credit note stays active for future use / refund
            // No balance to deduct; credit note remains fully available
            $creditNote->status = 'active';
            $creditNote->save();
        }

        /* ================= MARK RETURN COMPLETED ================= */
        $return->status = 'completed';
        $return->save();

        /* ================= UPDATE INVOICE STATUS (qty-based) ================= */
        $invoice->load('items');
        $totalSoldQty = $invoice->items->sum('quantity');

        $completedReturns = SalesReturn::where('sales_invoice_id', $invoice->_id)
            ->where('status', 'completed')
            ->with('items')
            ->get();

        $totalReturnedQty = 0;
        foreach ($completedReturns as $completedReturn) {
            foreach ($completedReturn->items as $rItem) {
                $totalReturnedQty += $rItem->quantity;
            }
        }

        // Only change status if not already 'cancelled' (payment-based above takes priority)
        if ($invoice->payment_status !== 'cancelled') {
            if ($totalReturnedQty == 0) {
                $invoice->status = 'confirmed';
            } elseif ($totalReturnedQty < $totalSoldQty) {
                $invoice->status = 'partially_returned';
            } else {
                $invoice->status = 'returned';
            }
        } else {
            // Fully settled via returns
            $invoice->status = 'returned';
        }

        $invoice->save();

        return response()->json([
            'success'          => true,
            'return_id'        => (string) $return->_id,
            'credit_note_id'   => (string) $creditNote->_id,
            'new_balance'      => $invoice->balance_amount,
            'payment_status'   => $invoice->payment_status,
            'message'          => 'Sales return completed. Credit note created & auto-adjusted: '
                                    . $creditNote->credit_note_number,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
private function createCreditNote($return, $amount)
{
    $creditNote = CreditNote::create([
        'credit_note_number' => $this->generateCreditNoteNumber(),
        'party_id'           => $return->party_id,
        'sales_invoice_id'   => $return->sales_invoice_id,
        'sales_return_id'    => $return->_id,
        'credit_date'        => $return->return_date,
        'subtotal'           => $return->subtotal,
        'tax_amount'         => $return->total_tax,
        'discount_amount'    => $return->discount_amount,
        'amount'             => $amount,
        'used_amount'        => 0,
        'remaining_amount'   => $amount,
        'reason'             => $return->reason ?? 'Sales Return',
        'status'             => 'active',   // always starts active
        'created_by'         => Auth::guard('admin')->id(),
    ]);

    foreach ($return->items as $item) {
        CreditNoteItem::create([
            'credit_note_id'       => $creditNote->_id,
            'sales_return_item_id' => $item->_id,
            'product_id'           => $item->product_id,
            'variant_id'           => $item->variant_id,
            'product_name'         => $item->product_name,
            'variant_name'         => $item->variant_name,
            'quantity'             => $item->quantity,
            'price'                => $item->price,
            'tax_percent'          => $item->tax_percent,
            'tax_amount'           => $item->tax_amount,
            'discount_amount'      => 0,
            'total'                => $item->total,
        ]);
    }

    return $creditNote;
}

    /**
     * Cancel the return
     */
    public function cancel($id)
    {
        try {
            $return = SalesReturn::findOrFail($id);

            if ($return->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft returns can be cancelled.'
                ], 400);
            }

            $return->update([
                'status' => 'cancelled'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Return cancelled successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified sales return.
     */
    public function show($id)
    {
        $return = SalesReturn::with(['party', 'warehouse', 'invoice', 'items', 'creditNote', 'creator'])
            ->findOrFail($id);

        return view('admin.sales-returns.show', compact('return'));
    }
    /**
 * Show the form for editing the specified sales return.
 */
public function edit($id)
{
    $return = SalesReturn::with(['items', 'invoice', 'party', 'warehouse'])
        ->findOrFail($id);

    // Only draft returns can be edited
    if ($return->status !== 'draft') {
        return redirect()->route('admin.sales-returns.show', $id)
            ->with('error', 'Only draft returns can be edited.');
    }

    $warehouses = Warehouse::active()->get();
    $mainWarehouse = Warehouse::main()->first();

    return view('admin.sales-returns.edit', compact('return', 'warehouses', 'mainWarehouse'));
}

public function update(Request $request, $id)
{
    $return = SalesReturn::with(['items'])->findOrFail($id);

    if ($return->status !== 'draft') {
        return response()->json([
            'success' => false,
            'message' => 'Only draft returns can be updated.'
        ], 403);
    }

    // Decode items JSON if string
    if ($request->has('items') && is_string($request->items)) {
        $request->merge([
            'items' => json_decode($request->items, true)
        ]);
    }

    // Validation
    $request->validate([
        'return_date' => 'required|date',
        'reason' => 'nullable|string',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.invoice_item_id' => 'required',
        'items.*.return_qty' => 'required|numeric|min:0.01',
    ]);

    try {

        $invoice = SalesInvoice::findOrFail($return->sales_invoice_id);

        // Calculate already returned quantities from COMPLETED returns (excluding current)
        $completedReturns = SalesReturn::where('sales_invoice_id', $invoice->_id)
            ->where('status', 'completed')
            ->where('_id', '!=', $id)
            ->with('items')
            ->get();

        $returnedQtys = [];
        foreach ($completedReturns as $completedReturn) {
            foreach ($completedReturn->items as $ritem) {
                $key = (string)$ritem->sales_invoice_item_id;
                $returnedQtys[$key] = ($returnedQtys[$key] ?? 0) + $ritem->quantity;
            }
        }

        // Get extra discount info
        $extraDiscount = (float)($invoice->extra_discount ?? 0);
        $extraDiscountType = $invoice->extra_discount_type ?? 'amount';
        $isGstInvoice = $invoice->isGstInvoice();

        // Calculate items
        $totalReturnQty = 0;
        $itemsSubtotal = 0;
        $totalTax = 0;
        $itemsData = [];

        foreach ($request->items as $item) {
            $invoiceItem = SalesInvoiceItem::find($item['invoice_item_id']);
            if (!$invoiceItem) {
                throw new \Exception('Invalid invoice item.');
            }

            $returnQty = (float)$item['return_qty'];

            if ($returnQty <= 0) {
                throw new \Exception('Return quantity must be greater than 0.');
            }

            // Check remaining quantity
            $alreadyReturned = $returnedQtys[(string)$invoiceItem->_id] ?? 0;
            $maxReturnable = $invoiceItem->quantity - $alreadyReturned;

            if ($returnQty > $maxReturnable) {
                throw new \Exception(
                    "Cannot return more than remaining quantity for {$invoiceItem->product_name}. " .
                    "Already returned: {$alreadyReturned}, Remaining: {$maxReturnable}"
                );
            }

            // Calculate item subtotal
            $itemSubtotal = $returnQty * $invoiceItem->price;
            $itemsSubtotal += $itemSubtotal;

            // Calculate tax
            $itemTax = 0;
            if ($isGstInvoice) {
                $itemTax = ($itemSubtotal * $invoiceItem->tax_percent) / 100;
                $totalTax += $itemTax;
            }

            $itemsData[] = [
                'invoice_item' => $invoiceItem,
                'return_qty' => $returnQty,
                'item_subtotal' => $itemSubtotal,
                'item_tax' => $itemTax,
            ];
        }

        // ✅ Calculate discount on subtotal
        $discountAmount = 0;
        if ($extraDiscount > 0 && $itemsSubtotal > 0) {
            if ($extraDiscountType === 'percent') {
                $discountAmount = ($itemsSubtotal * $extraDiscount) / 100;
            } else {
                $discountAmount = $extraDiscount;
            }
        }

        // ✅ Final return amount = Subtotal + Tax - Discount
        $totalReturnAmount = $itemsSubtotal + $totalTax - $discountAmount;
        $totalReturnQty = array_sum(array_column($itemsData, 'return_qty'));

        // Update Sales Return
        $return->update([
            'return_date' => $request->return_date,
            'reason' => $request->reason,
            'notes' => $request->notes,
            'total_return_qty' => $totalReturnQty,
            'subtotal' => $itemsSubtotal,                 // ✅ New field
            'total_tax' => $totalTax,                     // ✅ New field
            'discount_amount' => $discountAmount,         // ✅ New field
            'total_return_amount' => $totalReturnAmount,
        ]);

        // Delete old return items
        SalesReturnItem::where('sales_return_id', $return->_id)->delete();

        // Create new return items
        foreach ($itemsData as $data) {
            $invoiceItem = $data['invoice_item'];
            $itemTotal = $data['item_subtotal'] + $data['item_tax'];

            SalesReturnItem::create([
                'sales_return_id' => $return->_id,
                'sales_invoice_item_id' => $invoiceItem->_id,
                'product_id' => $invoiceItem->product_id,
                'variant_id' => $invoiceItem->variant_id,
                'product_name' => $invoiceItem->product_name,
                'variant_name' => $invoiceItem->variant_name,
                'quantity' => $data['return_qty'],
                'price' => $invoiceItem->price,
                'tax_percent' => $isGstInvoice ? $invoiceItem->tax_percent : 0,
                'tax_amount' => $data['item_tax'],
                 'subtotal' => $itemSubtotal,                  // ✅ New field
                'total' => $itemTotal,
            ]);
        }


        return response()->json([
            'success' => true,
            'return_id' => $return->_id,
            'message' => 'Sales return updated successfully'
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Delete a draft return.
     */
    public function destroy($id)
    {
        try {
            $return = SalesReturn::with(['items'])->findOrFail($id);

            if ($return->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft returns can be deleted.'
                ], 403);
            }

            // Delete items
            SalesReturnItem::where('sales_return_id', $id)->delete();

            // Delete return
            $return->delete();

            return response()->json([
                'success' => true,
                'message' => 'Return deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete return: ' . $e->getMessage()
            ], 500);
        }
    }
}
