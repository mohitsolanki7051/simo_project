<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WarrantyClaim;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Customer;
use App\Models\SimpleProduct;
use App\Models\VariantProduct;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use Carbon\Carbon;

class WarrantyController extends Controller
{
    public function index(Request $request)
    {
        $query = WarrantyClaim::with(['party', 'salesInvoice', 'salesInvoiceItem', 'warehouse']);
        $this->applyFilters($query, $request);
        $warehouses = Warehouse::orderBy('name')->get();
        $claims = $query->orderBy('created_at', 'desc')->paginate(15);
        $counts = $this->getFilteredCounts($request);

        return view('admin.warranty.index', compact('claims', 'counts', 'warehouses'));
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('warranty_status')) {
            $query->where('warranty_status', $request->warranty_status);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('replacement_status')) {
            $query->where('replacement_status', $request->replacement_status);
        }
        if ($request->filled('repair_status')) {
            $query->where('repair_status', $request->repair_status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('claim_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('claim_date', '<=', $request->to_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('warranty_claim_number', 'like', "%{$search}%")
                  ->orWhereHas('salesInvoice', function($sq) use ($search) {
                      $sq->where('invoice_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('party', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('salesInvoiceItem', function($sq) use ($search) {
                      $sq->where('product_name', 'like', "%{$search}%")
                         ->orWhere('variant_name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  })
                  ->orWhereHas('warehouse', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }
    }

    private function getFilteredCounts(Request $request)
    {
        if (!$request->anyFilled(['search','warehouse_id', 'warranty_status', 'replacement_status', 'repair_status', 'from_date', 'to_date'])) {
            return [
                'total'               => WarrantyClaim::count(),
                'valid'               => WarrantyClaim::where('warranty_status', 'valid')->count(),
                'expired'             => WarrantyClaim::where('warranty_status', 'expired')->count(),
                'pending_replacement' => WarrantyClaim::where('replacement_status', 'pending')->count(),
                'pending_repair'      => WarrantyClaim::where('repair_status', 'pending')->count(),
            ];
        }

        $query = WarrantyClaim::query();
        $this->applyFilters($query, $request);
        $filteredIds = $query->pluck('id');

        return [
            'total'               => $filteredIds->count(),
            'valid'               => WarrantyClaim::whereIn('id', $filteredIds)->where('warranty_status', 'valid')->count(),
            'expired'             => WarrantyClaim::whereIn('id', $filteredIds)->where('warranty_status', 'expired')->count(),
            'pending_replacement' => WarrantyClaim::whereIn('id', $filteredIds)->where('replacement_status', 'pending')->count(),
            'pending_repair'      => WarrantyClaim::whereIn('id', $filteredIds)->where('repair_status', 'pending')->count(),
        ];
    }

    public function create()
    {
        return view('admin.warranty.create');
    }

    public function searchInvoice(Request $request)
    {
        $request->validate(['search_term' => 'required|string']);
        $searchTerm = $request->search_term;

        $invoices = SalesInvoice::with(['party', 'warehouse', 'items' => fn($q) => $q->with(['product'])])
        ->whereNotIn('status', ['draft', 'cancelled', 'returned'])
        ->where(function ($query) use ($searchTerm) {
            $query->where('invoice_number', 'like', "%{$searchTerm}%")
                ->orWhereHas('party', fn($q) => $q->where('phone', 'like', "%{$searchTerm}%")
                ->orWhere('name', 'like', "%{$searchTerm}%"))
                ->orWhereHas('items', fn($q) => $q->where('barcode', 'like', "%{$searchTerm}%")
                ->orWhere('sku', 'like', "%{$searchTerm}%"));
        })
        ->orderBy('invoice_date', 'desc')
        ->limit(10)
        ->get();

        return response()->json(['success' => true, 'invoices' => $invoices]);
    }

public function getInvoiceDetails($id)
{
    $invoice = SalesInvoice::with(['party','warehouse','items'=>fn($q)=>$q->with(['product'])])->findOrFail($id);

    $returnedItems = SalesReturnItem::whereIn(
        'sales_return_id',
        SalesReturn::where('sales_invoice_id', $id)
            ->where('status','completed')
            ->pluck('_id')
    )->pluck('sales_invoice_item_id')->toArray();

    // remove returned items
    $invoice->items = $invoice->items->filter(function ($item) use ($returnedItems) {
        return !in_array((string)$item->_id, $returnedItems);
    });

    foreach ($invoice->items as $item) {
        $item->warranty_valid = $this->checkWarrantyValidity($item);
        $item->can_claim      = $this->canClaimWarranty($item);
    }

    return response()->json([
        'success'=>true,
        'invoice'=>$invoice
    ]);
}

    private function checkWarrantyValidity($item)
    {
        if (!$item->warranty_end) {
            return ['status' => 'no_warranty', 'message' => 'No warranty defined'];
        }

        $today       = Carbon::today();
        $warrantyEnd = Carbon::parse($item->warranty_end);

        if ($today <= $warrantyEnd) {
            return [
                'status'    => 'valid',
                'message'   => 'Warranty Valid until ' . $warrantyEnd->format('d-m-Y'),
                'end_date'  => $warrantyEnd->format('d-m-Y'),
                'days_left' => $today->diffInDays($warrantyEnd),
            ];
        }

        return [
            'status'       => 'expired',
            'message'      => 'Warranty Expired on ' . $warrantyEnd->format('d-m-Y'),
            'end_date'     => $warrantyEnd->format('d-m-Y'),
            'days_overdue' => $warrantyEnd->diffInDays($today),
        ];
    }

    private function canClaimWarranty($item)
    {
        $totalClaimed = WarrantyClaim::where('sales_invoice_item_id', $item->id)
            ->whereIn('replacement_status', ['pending', 'partial', 'done'])
            ->sum('claimed_qty');

        $purchasedQty = (int) $item->quantity;
        $remainingQty = max(0, $purchasedQty - $totalClaimed);

        if ($remainingQty <= 0) {
            return ['can_claim' => false, 'reason' => 'All units already claimed', 'remaining_qty' => 0, 'purchased_qty' => $purchasedQty, 'claimed_qty' => $totalClaimed];
        }

        $warrantyCheck = $this->checkWarrantyValidity($item);

        if ($warrantyCheck['status'] === 'valid') {
            return ['can_claim' => true, 'reason' => 'Eligible for warranty claim', 'remaining_qty' => $remainingQty, 'purchased_qty' => $purchasedQty, 'claimed_qty' => $totalClaimed];
        }

        return ['can_claim' => false, 'reason' => $warrantyCheck['message'], 'remaining_qty' => $remainingQty, 'purchased_qty' => $purchasedQty, 'claimed_qty' => $totalClaimed];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_invoice_id'      => 'required|exists:sales_invoices,_id',
            'sales_invoice_item_id' => 'required|exists:sales_invoice_items,_id',
            'claim_type'            => 'required|in:replacement',
            'claimed_qty'           => 'required|integer|min:1',
            'notes'                 => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $invoice    = SalesInvoice::findOrFail($request->sales_invoice_id);
            $item       = SalesInvoiceItem::findOrFail($request->sales_invoice_item_id);
            $claimedQty = (int) $request->claimed_qty;

            $totalClaimed = WarrantyClaim::where('sales_invoice_item_id', $item->id)
                ->whereIn('replacement_status', ['pending', 'partial', 'done'])
                ->sum('claimed_qty');

            $purchasedQty = (int) $item->quantity;
            $remainingQty = max(0, $purchasedQty - $totalClaimed);

            if ($claimedQty > $remainingQty) {
                return response()->json(['success' => false, 'message' => "Cannot claim {$claimedQty} units. Only {$remainingQty} unit(s) remaining."], 400);
            }

            $warrantyCheck = $this->checkWarrantyValidity($item);
            if ($warrantyCheck['status'] === 'expired') {
                return response()->json(['success' => false, 'message' => 'Cannot create claim: ' . $warrantyCheck['message']], 400);
            }

            $financialYear = $this->getFinancialYear();
            $lastClaim     = WarrantyClaim::where('warranty_claim_number', 'like', "WC/{$financialYear}/%")
                ->orderBy('created_at', 'desc')->first();

            $newNumber   = $lastClaim ? str_pad((int) substr($lastClaim->warranty_claim_number, -4) + 1, 4, '0', STR_PAD_LEFT) : '0001';
            $claimNumber = "WC/{$financialYear}/{$newNumber}";

            $claim = WarrantyClaim::create([
                'warranty_claim_number'  => $claimNumber,
                'sales_invoice_id'       => $invoice->id,
                'sales_invoice_item_id'  => $item->id,
                'party_id'               => $invoice->party_id,
                'product_id'             => $item->product_id,
                'variant_id'             => $item->variant_id,
                'product_type'           => $item->variant_id ? 'variant' : 'simple',
                'warehouse_id'           => $invoice->warehouse_id,
                'defective_stock_status' => 'pending_repair',
                'claim_date'             => Carbon::today(),
                'warranty_status'        => $warrantyCheck['status'],
                'claim_type'             => $request->claim_type,
                'replacement_status'     => 'pending',
                'repair_status'          => 'pending',
                'claimed_qty'            => $claimedQty,
                'replaced_qty'           => 0,
                'repaired_qty'           => 0,
                'scrapped_qty'           => 0,
                'notes'                  => $request->notes,
                'created_by'             => Auth::guard('admin')->id(),
            ]);

            return response()->json(['success' => true, 'message' => 'Warranty claim created successfully', 'claim' => $claim]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error creating claim: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Show the form for editing the specified warranty claim.
     */
    public function edit($id)
    {
        $claim = WarrantyClaim::with([
            'party',
            'salesInvoice',
            'salesInvoiceItem',
            'warehouse'
        ])->findOrFail($id);

        // If already approved, still show edit page but with warning
        // The form will be disabled/readonly based on approval status

        return view('admin.warranty.edit', compact('claim'));
    }

    /**
     * Update the specified warranty claim in storage.
     */
    public function update(Request $request, $id)
    {
        $claim = WarrantyClaim::findOrFail($id);

        // Check if claim is already approved
        if ($claim->approved_at) {
            return response()->json([
                'success' => false,
                'message' => 'Approved claims cannot be edited.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'sales_invoice_id'      => 'required|exists:sales_invoices,_id',
            'sales_invoice_item_id' => 'required|exists:sales_invoice_items,_id',
            'claim_type'            => 'required|in:replacement',
            'claimed_qty'           => 'required|integer|min:1',
            'notes'                 => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $item = SalesInvoiceItem::findOrFail($request->sales_invoice_item_id);
            $newClaimedQty = (int) $request->claimed_qty;
            $alreadyReplaced = (int) $claim->replaced_qty;

            // Validation: Cannot reduce below already replaced quantity
            if ($newClaimedQty < $alreadyReplaced) {
                $message = "Cannot reduce claimed quantity below {$alreadyReplaced} (already replaced units).";
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return redirect()->back()->with('error', $message);
            }

            // Validation: Cannot exceed purchased quantity
            $purchasedQty = (int) $item->quantity;
            if ($newClaimedQty > $purchasedQty) {
                $message = "Claimed quantity cannot exceed purchased quantity ({$purchasedQty}).";
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return redirect()->back()->with('error', $message);
            }

            // Check warranty validity
            $warrantyCheck = $this->checkWarrantyValidity($item);
            if ($warrantyCheck['status'] === 'expired') {
                $message = 'Cannot update claim: ' . $warrantyCheck['message'];
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return redirect()->back()->with('error', $message);
            }

            // Check if any other claims exist for this item that might conflict
            $totalClaimedByOthers = WarrantyClaim::where('sales_invoice_item_id', $item->id)
                ->where('_id', '!=', $claim->id)
                ->whereIn('replacement_status', ['pending', 'partial', 'done'])
                ->sum('claimed_qty');

            $totalClaimedAfterUpdate = $totalClaimedByOthers + $newClaimedQty;

            if ($totalClaimedAfterUpdate > $purchasedQty) {
                $message = "Total claimed quantity (including other claims) would exceed purchased quantity. Available: " . ($purchasedQty - $totalClaimedByOthers);
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return redirect()->back()->with('error', $message);
            }

            // Update the claim
            $oldClaimedQty = $claim->claimed_qty;
            $claim->claim_type = $request->claim_type;
            $claim->claimed_qty = $newClaimedQty;
            $claim->notes = $request->notes;
            $claim->warranty_status = $warrantyCheck['status']; // Update warranty status too

            // Auto-update replacement status based on new quantity vs replaced
            if ($claim->replaced_qty >= $newClaimedQty) {
                $claim->replacement_status = 'done';
            } elseif ($claim->replaced_qty > 0) {
                $claim->replacement_status = 'partial';
            } else {
                $claim->replacement_status = 'pending';
            }

            $claim->save();

            $message = 'Warranty claim updated successfully.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message, 'claim' => $claim]);
            }

            return redirect()->route('admin.warranty.show', $claim->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            $message = 'Error updating claim: ' . $e->getMessage();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }

            return redirect()->back()->with('error', $message);
        }
    }

    public function show($id)
    {
        $claim = WarrantyClaim::with([
            'party', 'salesInvoice', 'salesInvoiceItem',
            'simpleProduct', 'variantProduct',
            'warehouse',
            'creator', 'approver', 'repairer',
        ])->findOrFail($id);

        return view('admin.warranty.show', compact('claim'));
    }

    public function approveReplacement(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $claim = WarrantyClaim::with(['salesInvoice'])->findOrFail($id);

            if (!$claim->canApprove()) {
                return response()->json(['success' => false, 'message' => 'This claim cannot be approved. Check warranty status or remaining qty.'], 400);
            }

            $qtyToReplace       = (int) $request->qty;
            $remainingToReplace = $claim->replacementRemainingQty();

            if ($qtyToReplace > $remainingToReplace) {
                return response()->json(['success' => false, 'message' => "Cannot replace {$qtyToReplace}. Only {$remainingToReplace} unit(s) pending."], 400);
            }

            $warehouseId = $claim->warehouse_id;
            $warehouse   = Warehouse::find($warehouseId);

            if (!$warehouse) {
                return response()->json(['success' => false, 'message' => 'Invoice warehouse not found.'], 400);
            }

            $stockQuery = WarehouseStock::where('warehouse_id', $warehouseId)
                ->where('product_id', $claim->product_id)
                ->where('product_type', $claim->product_type);

            if ($claim->variant_id) $stockQuery->where('variant_id', $claim->variant_id);

            $stockRecord = $stockQuery->first();

            if (!$stockRecord || $stockRecord->quantity < $qtyToReplace) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock in '{$warehouse->name}'. Need {$qtyToReplace}, available: " . ($stockRecord->quantity ?? 0),
                ], 400);
            }

            $stockRecord->decrement('quantity', $qtyToReplace);

            WarehouseMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id'   => $claim->product_id,
                'product_type' => $claim->product_type,
                'variant_id'   => $claim->variant_id,
                'type'         => 'warranty_replacement',
                'quantity'     => -$qtyToReplace,
                'reference_id' => $claim->id,
                'remarks'      => "Warranty replacement ({$qtyToReplace} unit/s) for claim #" . $claim->warranty_claim_number,
            ]);

            $newReplacedQty = (int) $claim->replaced_qty + $qtyToReplace;
            $newStatus      = $newReplacedQty >= (int) $claim->claimed_qty ? 'done' : 'partial';

            $claim->update([
                'replaced_qty'       => $newReplacedQty,
                'replacement_status' => $newStatus,
                'approved_by'        => Auth::guard('admin')->id(),
                'approved_at'        => Carbon::now(),
                // defective_stock_status stays 'pending_repair' — product abhi workshop mein hai
            ]);

            $claim->refresh();
            $remaining = $claim->replacementRemainingQty();

            $msg = $newStatus === 'done'
                ? "All {$newReplacedQty} unit(s) replaced. Stock deducted from {$warehouse->name}."
                : "{$qtyToReplace} unit(s) replaced. {$remaining} unit(s) still pending replacement.";

            return response()->json(['success' => true, 'message' => $msg]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function markRepairCompleted(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $claim = WarrantyClaim::findOrFail($id);

            if (!$claim->canMarkRepairCompleted()) {
                return response()->json(['success' => false, 'message' => 'Cannot mark repair. No replaced units pending repair.'], 400);
            }

            $qtyToRepair       = (int) $request->qty;
            $remainingToRepair = $claim->repairRemainingQty();

            if ($qtyToRepair > $remainingToRepair) {
                return response()->json(['success' => false, 'message' => "Cannot repair {$qtyToRepair}. Only {$remainingToRepair} unit(s) pending repair."], 400);
            }

            $warehouseId = $claim->warehouse_id;
            $warehouse   = Warehouse::find($warehouseId);

            if (!$warehouse) {
                return response()->json(['success' => false, 'message' => 'Invoice warehouse not found.'], 400);
            }

            $stockQuery = WarehouseStock::where('warehouse_id', $warehouseId)
                ->where('product_id', $claim->product_id)
                ->where('product_type', $claim->product_type);

            if ($claim->variant_id) $stockQuery->where('variant_id', $claim->variant_id);

            $stock = $stockQuery->first();

            if ($stock) {
                $stock->increment('quantity', $qtyToRepair);
            } else {
                WarehouseStock::create([
                    'warehouse_id'    => $warehouseId,
                    'product_id'      => $claim->product_id,
                    'product_type'    => $claim->product_type,
                    'variant_id'      => $claim->variant_id,
                    'quantity'        => $qtyToRepair,
                    'min_stock_alert' => 0,
                ]);
            }

            WarehouseMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id'   => $claim->product_id,
                'product_type' => $claim->product_type,
                'variant_id'   => $claim->variant_id,
                'type'         => 'warranty_repair_return',
                'quantity'     => $qtyToRepair,
                'reference_id' => $claim->id,
                'remarks'      => "Repaired product returned ({$qtyToRepair} unit/s) for claim #" . $claim->warranty_claim_number,
            ]);

            $newRepairedQty = (int) $claim->repaired_qty + $qtyToRepair;

            $claim->refresh();
            $totalReplaced = (int) $claim->replaced_qty;

            // repaired + scrapped = total replaced hona chahiye tab 'completed'
            $totalScraped   = (int) $claim->scrapped_qty;
            $totalProcessed = $newRepairedQty + $totalScraped;
            $newStatus      = $totalProcessed >= $totalReplaced ? 'completed' : 'partial';



            $claim->update([
                'repaired_qty'           => $newRepairedQty,
                'repair_status'          => $newStatus,
                'repaired_by'            => Auth::guard('admin')->id(),
                'repaired_at'            => Carbon::now(),
                'defective_stock_status' => $this->resolveDefectiveStatus(
                                    $totalReplaced,
                                    $newRepairedQty,
                                    (int) $claim->scrapped_qty
                                ),
            ]);

            $claim->refresh();
            $remaining = $claim->repairRemainingQty();

            $msg = $newStatus === 'completed'
                ? "All {$newRepairedQty} unit(s) repaired. Stock added back to {$warehouse->name}."
                : "{$qtyToRepair} unit(s) repaired. {$remaining} unit(s) still pending repair/scrap.";

            return response()->json(['success' => true, 'message' => $msg]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  NEW: Mark Scrapped
    //  Scrapped units: stock wapas nahi aata, sirf qty track hoti hai
    // ─────────────────────────────────────────────────────────────
    public function markScrapped(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'qty'    => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $claim = WarrantyClaim::findOrFail($id);

            // Scrap sirf tab ho sakta hai jab kuch replaced ho aur pending repair ho
            if (!$claim->canMarkScrapped()) {
                return response()->json(['success' => false, 'message' => 'Cannot scrap. No replaced units pending repair/scrap.'], 400);
            }

            $qtyToScrap     = (int) $request->qty;
            $remainingToProcess = $claim->repairRemainingQty(); // same remaining — repair ya scrap dono is pool se

            if ($qtyToScrap > $remainingToProcess) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot scrap {$qtyToScrap}. Only {$remainingToProcess} unit(s) pending.",
                ], 400);
            }

            // No stock movement — scrapped product wapas stock mein nahi jaata
            // Sirf movement log karo for record
            $warehouseId = $claim->warehouse_id;
            WarehouseMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id'   => $claim->product_id,
                'product_type' => $claim->product_type,
                'variant_id'   => $claim->variant_id,
                'type'         => 'warranty_scrap',
                'quantity'     => 0, // no stock impact
                'reference_id' => $claim->id,
                'remarks'      => "Scrapped ({$qtyToScrap} unit/s) for claim #" . $claim->warranty_claim_number . ($request->reason ? ' — ' . $request->reason : ''),
            ]);

            $newScrappedQty = (int) $claim->scrapped_qty + $qtyToScrap;

            $claim->refresh();
            $totalReplaced  = (int) $claim->replaced_qty;
            $totalRepaired  = (int) $claim->repaired_qty;
            $totalProcessed = $totalRepaired + $newScrappedQty;

            // repair_status: agar sab process ho gaya
            $newRepairStatus = $totalProcessed >= $totalReplaced ? 'completed' : 'partial';



            $claim->update([
                'scrapped_qty'           => $newScrappedQty,
                'repair_status'          => $newRepairStatus,
                'defective_stock_status' => $this->resolveDefectiveStatus(
                                    $totalReplaced,
                                    $totalRepaired,
                                    $newScrappedQty
                                ),
                'scrapped_by'            => Auth::guard('admin')->id(),
                'scrapped_at'            => Carbon::now(),
                'scrap_reason'           => $request->reason,
            ]);

            $claim->refresh();
            $remaining = $claim->repairRemainingQty();

            $msg = $newRepairStatus === 'completed'
                ? "All done. {$newScrappedQty} unit(s) scrapped (no stock added)."
                : "{$qtyToScrap} unit(s) scrapped. {$remaining} unit(s) still pending.";

            return response()->json(['success' => true, 'message' => $msg]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    private function getFinancialYear()
    {
        $today = Carbon::today();
        $year  = $today->year;
        $month = $today->month;

        if ($month >= 4) {
            return substr($year, -2) . '-' . substr($year + 1, -2);
        }
        return substr($year - 1, -2) . '-' . substr($year, -2);
    }

    private function resolveDefectiveStatus(int $replaced, int $repaired, int $scrapped): string
    {
        if (($repaired + $scrapped) < $replaced) {
            return 'pending_repair';
        }

        if ($repaired > 0 && $scrapped === 0) {
            return 'repaired';
        }

        if ($scrapped > 0 && $repaired === 0) {
            return 'scrapped';
        }

        // repaired > 0 AND scrapped > 0
        return 'repaired';
    }
}
