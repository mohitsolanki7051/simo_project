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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class WarrantyController extends Controller
{
    /**
     * Display a listing of warranty claims.
     */
    public function index(Request $request)
    {
        $query = WarrantyClaim::with([
            'party',
            'salesInvoice',
            'salesInvoiceItem'
        ]);

        // Apply all filters to the main query
        $this->applyFilters($query, $request);

        // Get filtered claims for pagination
        $claims = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get counts for dashboard (also filtered)
        $counts = $this->getFilteredCounts($request);

        return view('admin.warranty.index', compact('claims', 'counts'));
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request)
    {
        // Filter by warranty status
        if ($request->filled('warranty_status')) {
            $query->where('warranty_status', $request->warranty_status);
        }

        // Filter by replacement status
        if ($request->filled('replacement_status')) {
            $query->where('replacement_status', $request->replacement_status);
        }

        // Filter by repair status
        if ($request->filled('repair_status')) {
            $query->where('repair_status', $request->repair_status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('claim_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('claim_date', '<=', $request->to_date);
        }

        // Search by claim number, invoice number, party name, phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
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
                });
            });
        }
    }

    /**
     * Get filtered counts for dashboard
     */
    private function getFilteredCounts(Request $request)
    {
        $baseQuery = WarrantyClaim::query();
        $this->applyFilters($baseQuery, $request);

        $filteredIds = $baseQuery->pluck('_id');

        return [
            'total' => $filteredIds->count(),
            'valid' => WarrantyClaim::whereIn('_id', $filteredIds)->where('warranty_status', 'valid')->count(),
            'expired' => WarrantyClaim::whereIn('_id', $filteredIds)->where('warranty_status', 'expired')->count(),
            'pending_replacement' => WarrantyClaim::whereIn('_id', $filteredIds)->where('replacement_status', 'pending')->count(),
            'pending_repair' => WarrantyClaim::whereIn('_id', $filteredIds)->where('repair_status', 'pending')->count(),
        ];
    }

    /**
     * Show the form for creating a new warranty claim.
     */
    public function create()
    {
        return view('admin.warranty.create');
    }

    /**
     * Search invoice for warranty verification.
     */
    public function searchInvoice(Request $request)
    {
        $request->validate([
            'search_term' => 'required|string'
        ]);

        $searchTerm = $request->search_term;

        // Search by invoice number, customer mobile, or barcode
        $invoices = SalesInvoice::with(['party', 'items' => function($q) {
                $q->with(['product']);
            }])
            ->where(function($query) use ($searchTerm) {
                $query->where('invoice_number', 'like', "%{$searchTerm}%")
                      ->orWhereHas('party', function($q) use ($searchTerm) {
                          $q->where('phone', 'like', "%{$searchTerm}%")
                            ->orWhere('name', 'like', "%{$searchTerm}%");
                      })
                      ->orWhereHas('items', function($q) use ($searchTerm) {
                          $q->where('barcode', 'like', "%{$searchTerm}%")
                            ->orWhere('sku', 'like', "%{$searchTerm}%");
                      });
            })
            ->orderBy('invoice_date', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'invoices' => $invoices
        ]);
    }

    /**
     * Get invoice details with warranty information.
     */
    public function getInvoiceDetails($id)
    {
        $invoice = SalesInvoice::with([
            'party',
            'items' => function($q) {
                $q->with(['product']);
            }
        ])->findOrFail($id);

        // Add warranty information to each item
        foreach ($invoice->items as $item) {
            $item->warranty_valid = $this->checkWarrantyValidity($item);
            $item->can_claim = $this->canClaimWarranty($item);
        }

        return response()->json([
            'success' => true,
            'invoice' => $invoice
        ]);
    }

    /**
     * Check warranty validity for an item.
     */
    private function checkWarrantyValidity($item)
    {
        if (!$item->warranty_end) {
            return [
                'status' => 'no_warranty',
                'message' => 'No warranty defined'
            ];
        }

        $today = Carbon::today();
        $warrantyEnd = Carbon::parse($item->warranty_end);

        if ($today <= $warrantyEnd) {
            return [
                'status' => 'valid',
                'message' => 'Warranty Valid until ' . $warrantyEnd->format('d-m-Y'),
                'end_date' => $warrantyEnd->format('d-m-Y'),
                'days_left' => $today->diffInDays($warrantyEnd)
            ];
        } else {
            return [
                'status' => 'expired',
                'message' => 'Warranty Expired on ' . $warrantyEnd->format('d-m-Y'),
                'end_date' => $warrantyEnd->format('d-m-Y'),
                'days_overdue' => $warrantyEnd->diffInDays($today)
            ];
        }
    }

    /**
     * Check if item can claim warranty.
     */
    private function canClaimWarranty($item)
    {
        // Check if already claimed
        $existingClaim = WarrantyClaim::where('sales_invoice_item_id', $item->id)
            ->where('replacement_status', 'done')
            ->first();

        if ($existingClaim) {
            return [
                'can_claim' => false,
                'reason' => 'Warranty already claimed for this product'
            ];
        }

        // Check warranty validity
        $warrantyCheck = $this->checkWarrantyValidity($item);

        if ($warrantyCheck['status'] === 'valid') {
            return [
                'can_claim' => true,
                'reason' => 'Eligible for warranty claim'
            ];
        } else {
            return [
                'can_claim' => false,
                'reason' => $warrantyCheck['message']
            ];
        }
    }

    /**
     * Store a newly created warranty claim.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_invoice_id' => 'required|exists:sales_invoices,_id',
            'sales_invoice_item_id' => 'required|exists:sales_invoice_items,_id',
            'claim_type' => 'required|in:replacement',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get invoice and item details
            $invoice = SalesInvoice::findOrFail($request->sales_invoice_id);
            $item = SalesInvoiceItem::findOrFail($request->sales_invoice_item_id);

            // Check if already claimed
            $existingClaim = WarrantyClaim::where('sales_invoice_item_id', $item->id)
                ->whereIn('replacement_status', ['pending', 'done'])
                ->first();

            if ($existingClaim) {
                return response()->json([
                    'success' => false,
                    'message' => 'Warranty claim already exists for this product (Status: ' . $existingClaim->replacement_status . ')'
                ], 400);
            }

            // Check warranty validity
            $warrantyCheck = $this->checkWarrantyValidity($item);

            if ($warrantyCheck['status'] === 'expired') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create claim: ' . $warrantyCheck['message']
                ], 400);
            }

            // Generate claim number
            $financialYear = $this->getFinancialYear();
            $lastClaim = WarrantyClaim::where('warranty_claim_number', 'like', "WC/{$financialYear}/%")
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastClaim) {
                $lastNumber = intval(substr($lastClaim->warranty_claim_number, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }

            $claimNumber = "WC/{$financialYear}/{$newNumber}";

            // Determine product type and IDs
            $productType = $item->variant_id ? 'variant' : 'simple';
            $productId = $item->product_id;
            $variantId = $item->variant_id;

            // Create warranty claim
            $claim = WarrantyClaim::create([
                'warranty_claim_number' => $claimNumber,
                'sales_invoice_id' => $invoice->id,
                'sales_invoice_item_id' => $item->id,
                'party_id' => $invoice->party_id,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'product_type' => $productType,
                'claim_date' => Carbon::today(),
                'warranty_status' => $warrantyCheck['status'],
                'claim_type' => $request->claim_type,
                'replacement_status' => 'pending',
                'repair_status' => 'pending',
                'notes' => $request->notes,
                'created_by' => Auth::guard('admin')->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Warranty claim created successfully',
                'claim' => $claim
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating claim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified warranty claim.
     */
    public function show($id)
    {
        $claim = WarrantyClaim::with([
            'party',
            'salesInvoice',
            'salesInvoiceItem',
            'simpleProduct',
            'variantProduct',
            'creator',
            'approver',
            'repairer',
        ])->findOrFail($id);

        return view('admin.warranty.show', compact('claim'));
    }

    /**
     * Approve replacement and deduct stock.
     */
    public function approveReplacement(Request $request, $id)
    {
        try {
            $claim = WarrantyClaim::with(['salesInvoice', 'salesInvoiceItem'])->findOrFail($id);

            // Validate if can approve
            if (!$claim->canApprove()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This claim cannot be approved. Check warranty status or current claim status.'
                ], 400);
            }

            // Get main warehouse
            $mainWarehouse = Warehouse::where('is_main', true)->first();

            if (!$mainWarehouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Main warehouse not found. Please set a main warehouse first.'
                ], 400);
            }

            // Check stock availability
            $stock = WarehouseStock::where('warehouse_id', $mainWarehouse->id)
                ->where('product_id', $claim->product_id)
                ->where('product_type', $claim->product_type);

            if ($claim->variant_id) {
                $stock->where('variant_id', $claim->variant_id);
            }

            $stockRecord = $stock->first();

            if (!$stockRecord || $stockRecord->quantity < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock in main warehouse for replacement'
                ], 400);
            }

            // Deduct stock
            $stockRecord->decrement('quantity', 1);

            // Create warehouse movement
            WarehouseMovement::create([
                'warehouse_id' => $mainWarehouse->id,
                'product_id' => $claim->product_id,
                'product_type' => $claim->product_type,
                'variant_id' => $claim->variant_id,
                'type' => 'warranty_replacement',
                'quantity' => -1,
                'reference_id' => $claim->id,
                'remarks' => 'Warranty replacement for claim #' . $claim->warranty_claim_number,
            ]);

            // Update claim
            $claim->update([
                'replacement_status' => 'done',
                'approved_by' => Auth::guard('admin')->id(),
                'approved_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Replacement approved and stock deducted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving replacement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark repair as completed and add stock back.
     */
    public function markRepairCompleted(Request $request, $id)
    {
        try {
            $claim = WarrantyClaim::findOrFail($id);

            // Validate if can mark repair completed
            if (!$claim->canMarkRepairCompleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot mark repair completed. Replacement must be done first.'
                ], 400);
            }

            // Get main warehouse
            $mainWarehouse = Warehouse::where('is_main', true)->first();

            if (!$mainWarehouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Main warehouse not found. Please set a main warehouse first.'
                ], 400);
            }

            // Check if stock record exists
            $stockRecord = WarehouseStock::where('warehouse_id', $mainWarehouse->id)
                ->where('product_id', $claim->product_id)
                ->where('product_type', $claim->product_type);

            if ($claim->variant_id) {
                $stockRecord->where('variant_id', $claim->variant_id);
            }

            $stock = $stockRecord->first();

            if ($stock) {
                // Update existing stock
                $stock->increment('quantity', 1);
            } else {
                // Create new stock record
                WarehouseStock::create([
                    'warehouse_id' => $mainWarehouse->id,
                    'product_id' => $claim->product_id,
                    'product_type' => $claim->product_type,
                    'variant_id' => $claim->variant_id,
                    'quantity' => 1,
                    'min_stock_alert' => 0,
                ]);
            }

            // Create warehouse movement
            WarehouseMovement::create([
                'warehouse_id' => $mainWarehouse->id,
                'product_id' => $claim->product_id,
                'product_type' => $claim->product_type,
                'variant_id' => $claim->variant_id,
                'type' => 'warranty_repair_return',
                'quantity' => 1,
                'reference_id' => $claim->id,
                'remarks' => 'Repaired product returned for claim #' . $claim->warranty_claim_number,
            ]);

            // Update claim
            $claim->update([
                'repair_status' => 'completed',
                'repaired_by' => Auth::guard('admin')->id(),
                'repaired_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Repair marked as completed and stock added back'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking repair completed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get financial year string (e.g., "25-26")
     */
    private function getFinancialYear()
    {
        $today = Carbon::today();
        $year = $today->year;
        $month = $today->month;

        if ($month >= 4) {
            // April to March: 2025-26 -> 25-26
            $start = substr($year, -2);
            $end = substr($year + 1, -2);
        } else {
            // January to March: 2024-25 -> 24-25
            $start = substr($year - 1, -2);
            $end = substr($year, -2);
        }

        return $start . '-' . $end;
    }
}
