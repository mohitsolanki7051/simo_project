<?php
// app/Http/Controllers/Admin/PurchaseExecutiveController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salesman; // Same table
use App\Models\Vendor;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PurchaseExecutiveController extends Controller
{
    /**
     * Display a listing of purchase executives.
     */
    public function index()
    {
        $purchaseExecutives = Salesman::purchaseExecutives()->get();

        $totalVendorsWithExecutive = Vendor::whereNotNull('purchase_executive_id')->count();
        $totalVendors = Vendor::count();
        $totalActiveVendors = Vendor::where('status', 'active')->count();
        $totalInactiveVendors = Vendor::where('status', 'inactive')->count();

        return view('admin.purchase-executives.index', compact(
            'purchaseExecutives',
            'totalVendorsWithExecutive',
            'totalVendors',
            'totalActiveVendors',
            'totalInactiveVendors'
        ));
    }

    /**
     * Show the form for creating a new purchase executive.
     */
    public function create()
    {
        return view('admin.purchase-executives.create');
    }

    /**
     * Store a newly created purchase executive in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:10|min:10',
            'email'         => 'nullable|email|max:255',
            'joining_date'  => 'required|date',
            'status'        => 'required|in:active,inactive',
            'notes'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['type'] = 'purchase_executive';

        // Set default values for salary/commission fields (not used)
        $data['salary_type'] = 'fixed';
        $data['fixed_salary'] = 0;
        $data['fixed_salary_period'] = 'monthly';
        $data['commission_enabled'] = false;
        $data['commission_period'] = null;
        $data['customer_commission_percent'] = 0;
        $data['dealer_commission_percent'] = 0;
        $data['distributor_commission_percent'] = 0;

        Salesman::create($data);

        return redirect()->route('admin.purchase-executives.index')
            ->with('success', 'Purchase Executive created successfully');
    }

    /**
     * Display the specified purchase executive.
     */
    public function show($id, Request $request)
    {
        $purchaseExecutive = Salesman::purchaseExecutives()->find($id);

        if (!$purchaseExecutive) {
            return redirect()->route('admin.purchase-executives.index')
                ->with('error', 'Purchase Executive not found');
        }

        // ── Filters ───────────────────────────────────────────────────────────
        $filters = [
            'month'      => $request->get('month', ''),
            'year'       => $request->get('year', date('Y')),
            'start_date' => $request->get('start_date', ''),
            'end_date'   => $request->get('end_date', ''),
        ];

        // ── Reusable date filter closure ──────────────────────────────────────
        $applyDateFilter = function ($query, $dateColumn) use ($filters) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $query->whereBetween($dateColumn, [
                    Carbon::parse($filters['start_date'])->startOfDay(),
                    Carbon::parse($filters['end_date'])->endOfDay(),
                ]);
            } elseif (!empty($filters['start_date'])) {
                $query->whereDate($dateColumn, '>=', $filters['start_date']);
            } elseif (!empty($filters['end_date'])) {
                $query->whereDate($dateColumn, '<=', $filters['end_date']);
            } else {
                if (!empty($filters['month'])) {
                    $query->whereMonth($dateColumn, (int) $filters['month']);
                }
                if (!empty($filters['year'])) {
                    $query->whereYear($dateColumn, (int) $filters['year']);
                }
            }
            return $query;
        };

        // ── Purchase Invoice filter closure ────────────────────────────────────
        $applyInvoiceFilters = function ($query) use ($filters, $id, $applyDateFilter) {
            $query->where('purchase_executive_id', $id)
                  ->whereIn('status', ['completed', 'confirmed', 'generated']);

            $applyDateFilter($query, 'invoice_date');

            return $query;
        };

        // ── Filtered purchases ────────────────────────────────────────────────────
        $purchases = $applyInvoiceFilters(PurchaseInvoice::with('supplier'))
            ->orderBy('invoice_date', 'desc')
            ->get();

        $totalPurchases = (float) $purchases->sum('grand_total');

        // ── Paginated invoices ────────────────────────────────────────────────
        $invoicesQuery = $applyInvoiceFilters(PurchaseInvoice::with('supplier'))
            ->orderBy('invoice_date', 'desc');

        $totalInvoicesCount = $invoicesQuery->count();
        $allInvoices = $invoicesQuery->paginate(10, ['*'], 'invoice_page');

        // ── Filtered vendors ──────────────────────────────────────────────────
        $vendorsQuery = Vendor::where('purchase_executive_id', $id);
        $applyDateFilter($vendorsQuery, 'created_at');

        $vendorsQuery->orderBy('created_at', 'desc');

        $allVendorsCount = $vendorsQuery->count();
        $vendors = $vendorsQuery->paginate(10, ['*'], 'vendor_page');

        // Vendor counts by status (filtered)
        $vendorCounts = [
            'active'   => Vendor::where('purchase_executive_id', $id)
                ->tap(fn($q) => $applyDateFilter($q, 'created_at'))
                ->where('status', 'active')
                ->count(),
            'inactive' => Vendor::where('purchase_executive_id', $id)
                ->tap(fn($q) => $applyDateFilter($q, 'created_at'))
                ->where('status', 'inactive')
                ->count(),
        ];

        return view('admin.purchase-executives.show', compact(
            'purchaseExecutive',
            'filters',
            'totalPurchases',
            'allInvoices',
            'totalInvoicesCount',
            'vendors',
            'allVendorsCount',
            'vendorCounts'
        ));
    }

    /**
     * Show the form for editing the specified purchase executive.
     */
    public function edit($id)
    {
        $purchaseExecutive = Salesman::purchaseExecutives()->find($id);

        if (!$purchaseExecutive) {
            return redirect()->route('admin.purchase-executives.index')
                ->with('error', 'Purchase Executive not found');
        }

        return view('admin.purchase-executives.edit', compact('purchaseExecutive'));
    }

    /**
     * Update the specified purchase executive in storage.
     */
    public function update(Request $request, $id)
    {
        $purchaseExecutive = Salesman::purchaseExecutives()->find($id);

        if (!$purchaseExecutive) {
            return redirect()->route('admin.purchase-executives.index')
                ->with('error', 'Purchase Executive not found');
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:10|min:10',
            'email'         => 'nullable|email|max:255',
            'joining_date'  => 'required|date',
            'status'        => 'required|in:active,inactive',
            'notes'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();

        // Keep type as purchase_executive
        $data['type'] = 'purchase_executive';

        $purchaseExecutive->update($data);

        return redirect()->route('admin.purchase-executives.index')
            ->with('success', 'Purchase Executive updated successfully');
    }

    /**
     * Search vendors assigned to this purchase executive (AJAX).
     */
    public function searchVendors($id, Request $request)
    {
        $purchaseExecutive = Salesman::purchaseExecutives()->find($id);

        if (!$purchaseExecutive) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        return response()->json(
            Vendor::where('purchase_executive_id', $id)
                ->select(['id', 'name', 'phone', 'email', 'status', 'gst_no'])
                ->where(fn($w) =>
                    $w->where('name',    'like', "%$q%")
                      ->orWhere('phone', 'like', "%$q%")
                      ->orWhere('email', 'like', "%$q%")
                      ->orWhere('gst_no', 'like', "%$q%")
                )
                ->orderBy('name')
                ->limit(20)
                ->get()
        );
    }

    /**
     * Get vendor counts (AJAX).
     */
    public function getVendorCounts(Request $request)
    {
        $id = $request->get('purchase_executive_id');

        return response()->json([
            'active'   => Vendor::where('purchase_executive_id', $id)
                ->where('status', 'active')
                ->count(),
            'inactive' => Vendor::where('purchase_executive_id', $id)
                ->where('status', 'inactive')
                ->count(),
        ]);
    }

    /**
     * Get all vendors assigned to this purchase executive (AJAX).
     */
    public function getAssignedVendors($id, Request $request)
    {
        $purchaseExecutive = Salesman::purchaseExecutives()->find($id);

        if (!$purchaseExecutive) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json(
            Vendor::where('purchase_executive_id', $id)
                ->select(['id', 'name', 'phone', 'email', 'status'])
                ->orderBy('name')
                ->get()
        );
    }

    /**
     * Check if phone number already exists for another purchase executive.
     */
    public function checkPhone(Request $request)
    {
        $q = Salesman::purchaseExecutives()->where('phone', $request->get('phone'));

        if ($eid = $request->get('exclude_id')) {
            $q->where('_id', '!=', $eid);
        }

        return response()->json([
            'exists' => $q->exists(),
            'available' => !$q->exists(),
            'message' => $q->exists() ? 'This phone number is already in use' : ''
        ]);
    }

    /**
     * Check if email already exists for another purchase executive.
     */
    public function checkEmail(Request $request)
    {
        $q = Salesman::purchaseExecutives()->where('email', $request->get('email'));

        if ($eid = $request->get('exclude_id')) {
            $q->where('_id', '!=', $eid);
        }

        return response()->json([
            'exists' => $q->exists(),
            'available' => !$q->exists(),
            'message' => $q->exists() ? 'This email is already in use' : ''
        ]);
    }

    /**
     * Bulk update status for purchase executives.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $ids = $request->get('ids', []);
        $status = $request->get('status');

        if (!in_array($status, ['active', 'inactive']) || empty($ids)) {
            return response()->json(['error' => 'Invalid request'], 422);
        }

        Salesman::purchaseExecutives()->whereIn('_id', $ids)->update(['status' => $status]);

        return response()->json(['success' => true]);
    }
}
