<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\DebitNote;
use App\Models\DebitNoteItem;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\WarehouseMovement;
use App\Models\Vendor;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;

class PurchaseReturnController extends Controller
{
    /**
     * Generate return number with format: SIM/PR/24-25/000001
     */
    private function generateReturnNumber()
    {
        $financialYear = $this->getFinancialYear();

        $lastReturn = PurchaseReturn::where('return_number', 'regex', "/^SIM\/PR\/{$financialYear}\/\d+$/")
            ->orderBy('return_number', 'desc')
            ->first();

        if ($lastReturn) {
            preg_match('/(\d+)$/', $lastReturn->return_number, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "SIM/PR/{$financialYear}/{$newNumber}";
    }

    /**
     * Generate debit note number with format: SIM/DN/24-25/000001
     */
    private function generateDebitNoteNumber()
    {
        $financialYear = $this->getFinancialYear();

        $lastNote = DebitNote::where('debit_note_number', 'regex', "/^SIM\/DN\/{$financialYear}\/\d+$/")
            ->orderBy('debit_note_number', 'desc')
            ->first();

        if ($lastNote) {
            preg_match('/(\d+)$/', $lastNote->debit_note_number, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "SIM/DN/{$financialYear}/{$newNumber}";
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
     * Display a listing of purchase returns.
     */
    public function index(Request $request)
{
    $query = PurchaseReturn::with(['vendor', 'invoice', 'warehouse'])
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
        $invoiceIds = PurchaseInvoice::where('invoice_number', 'like', '%' . $request->invoice_number . '%')
            ->pluck('_id');
        $query->whereIn('purchase_invoice_id', $invoiceIds);
    }

    // Party filter (vendor/dealer/distributor)
    if ($request->filled('party_id')) {
        $query->where('party_id', $request->party_id);
    }

    // Status filter
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $returns = $query->paginate(20)->withQueryString();

    /* ================= STATS QUERY - ALL FILTERS APPLY ================= */
    // ✅ Initialize stats query for COMPLETED returns only
    $statsQuery = PurchaseReturn::where('status', 'completed');

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
        $invoiceIds = PurchaseInvoice::where('invoice_number', 'like', '%' . $request->invoice_number . '%')
            ->pluck('_id');
        $statsQuery->whereIn('purchase_invoice_id', $invoiceIds);
    }
    if ($request->filled('party_id')) {  // ✅ Fixed: use $statsQuery, not $query
        $statsQuery->where('party_id', $request->party_id);
    }
    if ($request->filled('status')) {  // ✅ Add status filter if needed for stats
        $statsQuery->where('status', $request->status);
    }

    // Calculate stats with Decimal128 conversion
    $totalReturnAmountRaw = (clone $statsQuery)->sum('total_return_amount');
    $totalReturnAmount = $this->decimalToFloat($totalReturnAmountRaw);
    $totalReturns = (clone $statsQuery)->count();

    // Get active parties for filter dropdown (both vendors and customers)
    $vendors = Vendor::where('status', 'active')
        ->orderBy('company_name')
        ->get();

    // ✅ Fix: Use 'customer_type' instead of 'party_type' for Customer model
    $customers = Customer::whereIn('party_type', ['dealer', 'distributor'])
        ->where('status', 'active')
        ->orderBy('name')
        ->get();

    $allParties = collect();

    foreach ($vendors as $v) {
        $allParties->push((object)[
            '_id' => $v->_id,
            'name' => $v->company_name,
            'type' => 'vendor'
        ]);
    }

    foreach ($customers as $c) {
        $allParties->push((object)[
            '_id' => $c->_id,
            'name' => $c->name,
            'type' => $c->party_type  // ✅ Fix: Use 'customer_type' instead of 'party_type'
        ]);
    }

    $allParties = $allParties->sortBy('name');

    return view('admin.purchase-returns.index', compact(
        'returns',
        'totalReturnAmount',
        'totalReturns',
        'allParties'
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
     * Show the form for creating a new purchase return.
     */
    public function create()
    {
        $returnNumber = $this->generateReturnNumber();
        $warehouses = Warehouse::active()->get();
        $mainWarehouse = Warehouse::main()->first();

        return view('admin.purchase-returns.create', compact('returnNumber', 'warehouses', 'mainWarehouse'));
    }

    /**
     * Search invoices for purchase return
     */
    public function searchInvoices(Request $request)
    {
        $search = $request->get('search', '');

        $partyId = $request->get('party_id');

        $query = PurchaseInvoice::with(['vendor', 'warehouse'])
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

                $q->orWhere('invoice_number', 'regex', new \MongoDB\BSON\Regex($search, 'i'));

                $q->orWhere('party_name', 'regex', new \MongoDB\BSON\Regex($search, 'i'));

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
                    'party_name' => $invoice->party_name,
                    'grand_total' => (float)$invoice->grand_total,
                    'balance_amount' => (float)$invoice->balance_amount,
                    'payment_status' => $invoice->payment_status,
                    'warehouse_name' => $invoice->warehouse?->name ?? 'N/A',
                ];
            })
        ]);
    }

    /**
     * Get invoice details for purchase return
     */
    public function getInvoiceDetails($id)
    {
        $invoice = PurchaseInvoice::with(['vendor', 'warehouse', 'items'])->findOrFail($id);

        // Already returned quantities from completed returns
        $completedReturns = PurchaseReturn::where('purchase_invoice_id', $id)
            ->where('status', 'completed')
            ->with('items')
            ->get();

        $returnedQtys = [];
        foreach ($completedReturns as $return) {
            foreach ($return->items as $item) {
                $key = (string)$item->purchase_invoice_item_id;
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
                'party_type' => $invoice->party_type,
                'party_name' => $invoice->party_name,
                'warehouse_id' => (string)$invoice->warehouse_id,
                'warehouse_name' => $invoice->warehouse?->name ?? 'N/A',
                'grand_total' => (float)$invoice->grand_total,
                'balance_amount' => (float)$invoice->balance_amount,
                'invoice_type' => $invoice->invoice_type,
                'is_gst' => $invoice->isGstInvoice(),

                // Extra discount info
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
                    'price' => (float)$item->purchase_price,
                    'tax_percent' => (float)$item->tax_percent,
                    'tax_amount' => (float)$item->tax_amount,
                    'total' => (float)$item->total,
                ];
            })
        ]);
    }

    /**
     * Store a new purchase return (draft)
     */
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
            'purchase_invoice_id' => 'required|exists:purchase_invoices,_id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.invoice_item_id' => 'required',
            'items.*.return_qty' => 'required|numeric|min:0.01',
        ]);

        try {
            $invoice = PurchaseInvoice::findOrFail($request->purchase_invoice_id);

            // Check if invoice is eligible for return
            if (!in_array($invoice->status, ['confirmed', 'completed', 'partially_returned'])) {
                throw new \Exception('This invoice cannot be returned.');
            }

            // Calculate already returned quantities from COMPLETED returns
            $completedReturns = PurchaseReturn::where('purchase_invoice_id', $invoice->_id)
                ->where('status', 'completed')
                ->with('items')
                ->get();

            $returnedQtys = [];
            foreach ($completedReturns as $return) {
                foreach ($return->items as $ritem) {
                    $key = (string)$ritem->purchase_invoice_item_id;
                    $returnedQtys[$key] = ($returnedQtys[$key] ?? 0) + $ritem->quantity;
                }
            }

            // Check DRAFT returns
            $draftReturn = PurchaseReturn::where('purchase_invoice_id', $invoice->_id)
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
                $invoiceItem = PurchaseInvoiceItem::find($item['invoice_item_id']);
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
                $itemSubtotal = $returnQty * $invoiceItem->purchase_price;
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

            // Calculate discount on subtotal
            $discountAmount = 0;
            if ($extraDiscount > 0 && $itemsSubtotal > 0) {
                if ($extraDiscountType === 'percent') {
                    $discountAmount = ($itemsSubtotal * $extraDiscount) / 100;
                } else {
                    $discountAmount = $extraDiscount;
                }
            }

            // Final return amount = Subtotal + Tax - Discount
            $totalReturnAmount = $itemsSubtotal + $totalTax - $discountAmount;
            $totalReturnQty = array_sum(array_column($itemsData, 'return_qty'));

            // Create Purchase Return (Draft)
            $return = PurchaseReturn::create([
                'return_number' => $this->generateReturnNumber(),
                'purchase_invoice_id' => $invoice->_id,
                'party_id'   => $invoice->party_id,
                'party_type' => $invoice->party_type,
                'party_name' => $invoice->party_name,
                'warehouse_id' => $invoice->warehouse_id,
                'return_date' => $request->return_date,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'total_return_qty' => $totalReturnQty,
                'subtotal' => $itemsSubtotal,
                'total_tax' => $totalTax,
                'discount_amount' => $discountAmount,
                'total_return_amount' => $totalReturnAmount,
                'status' => 'draft',
                'created_by' => Auth::guard('admin')->id(),
            ]);

            // Create Return Items
            foreach ($itemsData as $data) {
                $invoiceItem = $data['invoice_item'];
                $itemSubtotal = $data['item_subtotal'];
                $itemTax = $data['item_tax'];
                $itemTotal = $itemSubtotal + $itemTax;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->_id,
                    'purchase_invoice_item_id' => $invoiceItem->_id,
                    'product_id' => $invoiceItem->product_id,
                    'variant_id' => $invoiceItem->variant_id,
                    'product_name' => $invoiceItem->product_name,
                    'variant_name' => $invoiceItem->variant_name,
                    'quantity' => $data['return_qty'],
                    'price' => $invoiceItem->purchase_price,
                    'tax_percent' => $isGstInvoice ? $invoiceItem->tax_percent : 0,
                    'tax_amount' => $itemTax,
                    'subtotal' => $itemSubtotal,
                    'total' => $itemTotal,
                ]);
            }

            return response()->json([
                'success' => true,
                'return_id' => $return->_id,
                'message' => 'Purchase return draft created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete the return and create debit note
     */
public function complete($id)
{
    try {
        $return = PurchaseReturn::with(['items', 'invoice'])->findOrFail($id);

        if ($return->status !== 'draft') {
            throw new \Exception('Return can only be completed from draft status.');
        }

        $invoice = $return->invoice;

        /* ================= STOCK REDUCTION ================= */
        foreach ($return->items as $item) {
            $stock = WarehouseStock::where('warehouse_id', $return->warehouse_id)
                ->where('product_id', $item->product_id)
                ->where('product_type', $item->variant_id ? 'variant' : 'simple')
                ->when($item->variant_id, function ($q) use ($item) {
                    return $q->where('variant_id', $item->variant_id);
                })
                ->first();

            if (!$stock) {
                throw new \Exception("Stock not found for product {$item->product_name}");
            }

            if ($stock->quantity < $item->quantity) {
                throw new \Exception(
                    "Insufficient stock for {$item->product_name}. " .
                    "Available: {$stock->quantity}, Returning: {$item->quantity}"
                );
            }

            $stock->quantity -= $item->quantity;
            $stock->save();

            WarehouseMovement::create([
                'warehouse_id' => $return->warehouse_id,
                'product_id'   => $item->product_id,
                'product_type' => $item->variant_id ? 'variant' : 'simple',
                'variant_id'   => $item->variant_id ?? null,
                'type'         => WarehouseMovement::TYPE_RETURN,
                'quantity'     => -$item->quantity,
                'reference_id' => $return->_id,
                'remarks'      => "Purchase Return: {$return->return_number}",
            ]);
        }

        /* ================= DEBIT NOTE CREATION ================= */
        $returnAmount = (float) $return->total_return_amount;
        $debitNote    = $this->createDebitNote($return, $returnAmount);

        /* ================= AUTO-ADJUST DEBIT NOTE AGAINST INVOICE ================= */
        $invoice->refresh();
        $currentBalance = (float) $invoice->balance_amount;

        if ($currentBalance > 0) {
            // Kitna adjust ho sakta hai abhi?
            $adjustAmount = min($returnAmount, $currentBalance);

            // Invoice balance kam karo
            $newBalance = round($currentBalance - $adjustAmount, 2);

            // Debit note ka used/remaining update karo
            $debitNote->used_amount      = $adjustAmount;
            $debitNote->remaining_amount = round($returnAmount - $adjustAmount, 2);

            // Debit note status
            if ($debitNote->remaining_amount <= 0) {
                $debitNote->status = 'settled';          // poora use ho gaya
            } else {
                $debitNote->status = 'partial';  // sirf balance tak adjust hua
            }
            $debitNote->save();

            // Invoice payment_status determine karo
            if ($newBalance <= 0) {
                $newBalance          = 0;
                $newPaymentStatus = (float)$invoice->total_paid > 0 ? 'paid' : 'cancelled';    // poora return se settle
            } else {
                $newPaymentStatus    = 'unpaid';        // abhi bhi kuch baaki hai
            }

            $invoice->balance_amount = $newBalance;
            $invoice->payment_status = $newPaymentStatus;

        } else {
            // Invoice already paid tha — debit note future use ke liye active rahega
            // (next invoice mein adjust hoga ya vendor refund dega)
            $debitNote->status = 'active';
            $debitNote->save();
        }

        /* ================= MARK RETURN COMPLETED ================= */
        $return->status = 'completed';
        $return->save();

        /* ================= UPDATE INVOICE STATUS (qty-based) ================= */
        $invoice->load('items');
        $totalPurchasedQty = $invoice->items->sum('quantity');

        $completedReturns = PurchaseReturn::where('purchase_invoice_id', $invoice->_id)
            ->where('status', 'completed')
            ->with('items')
            ->get();

        $totalReturnedQty = 0;
        foreach ($completedReturns as $completedReturn) {
            foreach ($completedReturn->items as $rItem) {
                $totalReturnedQty += $rItem->quantity;
            }
        }

        // Payment cancelled ho chuka hai to invoice status = returned
        if ($invoice->payment_status === 'cancelled') {
            $invoice->status = 'returned';
        } elseif ($totalReturnedQty == 0) {
            $invoice->status = 'confirmed';
        } elseif ($totalReturnedQty < $totalPurchasedQty) {
            $invoice->status = 'partially_returned';
        } else {
            $invoice->status = 'returned';
        }

        $invoice->save();

        return response()->json([
            'success'        => true,
            'return_id'      => (string) $return->_id,
            'debit_note_id'  => (string) $debitNote->_id,
            'new_balance'    => $invoice->balance_amount,
            'payment_status' => $invoice->payment_status,
            'message'        => 'Purchase return completed. Debit note created & auto-adjusted: '
                                    . $debitNote->debit_note_number,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}
    private function createDebitNote($return, $amount)
    {
        $debitNote = DebitNote::create([
            'debit_note_number'   => $this->generateDebitNoteNumber(),
            'party_id'            => $return->party_id,
            'party_type'          => $return->party_type,
            'purchase_invoice_id' => $return->purchase_invoice_id,
            'purchase_return_id'  => $return->_id,
            'debit_date'          => $return->return_date,
            'subtotal'            => $return->subtotal,
            'tax_amount'          => $return->total_tax,
            'discount_amount'     => $return->discount_amount,
            'amount'              => $amount,
            'used_amount'         => 0,
            'remaining_amount'    => $amount,
            'reason'              => $return->reason ?? 'Purchase Return',
            'status'              => 'active',   // always starts active
            'created_by'          => Auth::guard('admin')->id(),
        ]);

        foreach ($return->items as $item) {
            DebitNoteItem::create([
                'debit_note_id'           => $debitNote->_id,
                'purchase_return_item_id' => $item->_id,
                'product_id'              => $item->product_id,
                'variant_id'              => $item->variant_id,
                'product_name'            => $item->product_name,
                'variant_name'            => $item->variant_name,
                'quantity'                => $item->quantity,
                'price'                   => $item->price,
                'tax_percent'             => $item->tax_percent,
                'tax_amount'              => $item->tax_amount,
                'discount_amount'         => 0,
                'total'                   => $item->total,
            ]);
        }

        return $debitNote;
    }



    /**
     * Cancel the return
     */
    public function cancel($id)
    {
        try {
            $return = PurchaseReturn::findOrFail($id);

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
     * Display the specified purchase return.
     */
    public function show($id)
    {
        $return = PurchaseReturn::with(['vendor', 'warehouse', 'invoice', 'items', 'debitNote', 'creator'])
            ->findOrFail($id);

        return view('admin.purchase-returns.show', compact('return'));
    }

    /**
     * Show the form for editing the specified purchase return.
     */
    public function edit($id)
    {
        $return = PurchaseReturn::with(['items', 'invoice', 'vendor', 'warehouse'])
            ->findOrFail($id);

        // Only draft returns can be edited
        if ($return->status !== 'draft') {
            return redirect()->route('admin.purchase-returns.show', $id)
                ->with('error', 'Only draft returns can be edited.');
        }

        $warehouses = Warehouse::active()->get();
        $mainWarehouse = Warehouse::main()->first();

        return view('admin.purchase-returns.edit', compact('return', 'warehouses', 'mainWarehouse'));
    }

    /**
     * Update a draft return
     */
    public function update(Request $request, $id)
    {
        $return = PurchaseReturn::with(['items'])->findOrFail($id);

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
            $invoice = PurchaseInvoice::findOrFail($return->purchase_invoice_id);

            // Calculate already returned quantities from COMPLETED returns (excluding current)
            $completedReturns = PurchaseReturn::where('purchase_invoice_id', $invoice->_id)
                ->where('status', 'completed')
                ->where('_id', '!=', $id)
                ->with('items')
                ->get();

            $returnedQtys = [];
            foreach ($completedReturns as $completedReturn) {
                foreach ($completedReturn->items as $ritem) {
                    $key = (string)$ritem->purchase_invoice_item_id;
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
                $invoiceItem = PurchaseInvoiceItem::find($item['invoice_item_id']);
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
                $itemSubtotal = $returnQty * $invoiceItem->purchase_price;
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

            // Calculate discount on subtotal
            $discountAmount = 0;
            if ($extraDiscount > 0 && $itemsSubtotal > 0) {
                if ($extraDiscountType === 'percent') {
                    $discountAmount = ($itemsSubtotal * $extraDiscount) / 100;
                } else {
                    $discountAmount = $extraDiscount;
                }
            }

            // Final return amount
            $totalReturnAmount = $itemsSubtotal + $totalTax - $discountAmount;
            $totalReturnQty = array_sum(array_column($itemsData, 'return_qty'));

            // Update Purchase Return
            $return->update([
                'return_date' => $request->return_date,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'total_return_qty' => $totalReturnQty,
                'subtotal' => $itemsSubtotal,
                'total_tax' => $totalTax,
                'discount_amount' => $discountAmount,
                'total_return_amount' => $totalReturnAmount,
            ]);

            // Delete old return items
            PurchaseReturnItem::where('purchase_return_id', $return->_id)->delete();

            // Create new return items
            foreach ($itemsData as $data) {
                $invoiceItem = $data['invoice_item'];
                $itemTotal = $data['item_subtotal'] + $data['item_tax'];

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->_id,
                    'purchase_invoice_item_id' => $invoiceItem->_id,
                    'product_id' => $invoiceItem->product_id,
                    'variant_id' => $invoiceItem->variant_id,
                    'product_name' => $invoiceItem->product_name,
                    'variant_name' => $invoiceItem->variant_name,
                    'quantity' => $data['return_qty'],
                    'price' => $invoiceItem->purchase_price,
                    'tax_percent' => $isGstInvoice ? $invoiceItem->tax_percent : 0,
                    'tax_amount' => $data['item_tax'],
                    'subtotal' => $data['item_subtotal'],
                    'total' => $itemTotal,
                ]);
            }

            return response()->json([
                'success' => true,
                'return_id' => $return->_id,
                'message' => 'Purchase return updated successfully'
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
            $return = PurchaseReturn::with(['items'])->findOrFail($id);

            if ($return->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft returns can be deleted.'
                ], 403);
            }

            // Delete items
            PurchaseReturnItem::where('purchase_return_id', $id)->delete();

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
