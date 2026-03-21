<?php
// app/Http/Controllers/Admin/VendorController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorAddress;
use App\Models\Salesman;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VendorController extends Controller
{
    /**
     * Display a listing of vendors.
     */
    public function index(Request $request)
    {
        $vendors = Vendor::with(['addresses' => function($query) {
                $query->where('is_default', true)
                      ->orWhere(function($q) {
                          $q->where('type', 'billing')->orderBy('is_default', 'desc');
                      });
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalVendors = $vendors->count();
        $totalActiveVendors = $vendors->where('status', 'active')->count();
        $totalInactiveVendors = $vendors->where('status', 'inactive')->count();
        $totalCreditLimit = $vendors->sum('credit_limit');

        // Get purchase executives for filter
        $purchaseExecutives = Salesman::purchaseExecutives()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['_id', 'name']);

        return view('admin.vendors.index', compact(
            'vendors',
            'totalVendors',
            'totalActiveVendors',
            'totalInactiveVendors',
            'totalCreditLimit',
            'purchaseExecutives'
        ));
    }

    /**
     * Show the form for creating a new vendor.
     */
    public function create(Request $request)
    {
        // Get active purchase executives for dropdown
        $purchaseExecutives = Salesman::purchaseExecutives()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['_id', 'name']);

        return view('admin.vendors.create', compact('purchaseExecutives'));
    }

    /**
     * Store a newly created vendor with addresses.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:15|unique:vendors,phone',
            'email' => 'nullable|email|unique:vendors,email',
            'gst_number' => [
                'nullable',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                'unique:vendors,gst_number'
            ],
            'pan_number' => [
                'nullable',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
                'unique:vendors,pan_number'
            ],
            'opening_balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'account_holder_name' => 'nullable|string|max:100',
            'purchase_executive_id' => 'nullable|exists:salesmen,_id',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'billing_addresses' => 'nullable|string',
            'shipping_addresses' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create vendor
            $vendor = Vendor::create([
                'company_name' => $request->company_name,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'gst_number' => strtoupper($request->gst_number),
                'pan_number' => strtoupper($request->pan_number),
                'opening_balance' => $request->opening_balance ?? 0,
                'credit_limit' => $request->credit_limit,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'ifsc_code' => strtoupper($request->ifsc_code),
                'account_holder_name' => $request->account_holder_name,
                'purchase_executive_id' => $request->purchase_executive_id,
                'status' => $request->status,
                'notes' => $request->notes
            ]);

            // Process billing addresses
            if ($request->billing_addresses) {
                $billingAddresses = json_decode($request->billing_addresses, true);

                if (is_array($billingAddresses) && count($billingAddresses) > 0) {
                    $hasDefaultBilling = false;

                    foreach ($billingAddresses as $address) {
                        $isDefault = isset($address['isDefault']) && $address['isDefault'] === true;

                        if ($isDefault && $hasDefaultBilling) {
                            $isDefault = false;
                        } elseif ($isDefault) {
                            $hasDefaultBilling = true;
                        }

                        VendorAddress::create([
                            'vendor_id' => $vendor->id,
                            'type' => 'billing',
                            'address' => $address['address'] ?? '',
                            'city' => $address['city'] ?? '',
                            'state' => $address['state'] ?? '',
                            'pincode' => $address['pincode'] ?? '',
                            'country' => $address['country'] ?? 'India',
                            'landmark' => $address['landmark'] ?? '',
                            'contact_person' => $address['contactPerson'] ?? '',
                            'contact_number' => $address['contactNumber'] ?? '',
                            'is_default' => $isDefault
                        ]);
                    }
                }
            }

            // Process shipping addresses
            if ($request->shipping_addresses) {
                $shippingAddresses = json_decode($request->shipping_addresses, true);

                if (is_array($shippingAddresses) && count($shippingAddresses) > 0) {
                    $hasDefaultShipping = false;

                    foreach ($shippingAddresses as $address) {
                        $isDefault = isset($address['isDefault']) && $address['isDefault'] === true;

                        if ($isDefault && $hasDefaultShipping) {
                            $isDefault = false;
                        } elseif ($isDefault) {
                            $hasDefaultShipping = true;
                        }

                        VendorAddress::create([
                            'vendor_id' => $vendor->id,
                            'type' => 'shipping',
                            'address' => $address['address'] ?? '',
                            'city' => $address['city'] ?? '',
                            'state' => $address['state'] ?? '',
                            'pincode' => $address['pincode'] ?? '',
                            'country' => $address['country'] ?? 'India',
                            'landmark' => $address['landmark'] ?? '',
                            'contact_person' => $address['contactPerson'] ?? '',
                            'contact_number' => $address['contactNumber'] ?? '',
                            'is_default' => $isDefault
                        ]);
                    }
                }
            }

            return redirect()->route('admin.vendors.index')
                ->with('success', 'Vendor created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating vendor: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified vendor.
     */
    public function show($id, Request $request)
    {
        $vendor = Vendor::with(['addresses'])->findOrFail($id);

        // ── Filters ───────────────────────────────────────────────
        $filters = [
            'month'      => $request->get('month', ''),
            'year'       => $request->get('year', date('Y')),
            'start_date' => $request->get('start_date', ''),
            'end_date'   => $request->get('end_date', ''),
        ];

        // ── Date filter closure ───────────────────────────────────
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

        // ── Purchase Invoices ─────────────────────────────────────
        $invoicesQuery = PurchaseInvoice::where('vendor_id', $id)
            ->whereIn('status', ['completed', 'confirmed', 'generated'])
            ->with(['items']);

        $applyDateFilter($invoicesQuery, 'invoice_date');

        $totalPurchases = (float) (clone $invoicesQuery)->sum('grand_total');
        $totalInvoicesCount = $invoicesQuery->count();
        $allInvoices = $invoicesQuery->orderBy('invoice_date', 'desc')
            ->paginate(10, ['*'], 'invoice_page');

        return view('admin.vendors.show', compact(
            'vendor',
            'filters',
            'totalPurchases',
            'totalInvoicesCount',
            'allInvoices'
        ));
    }

    /**
     * Show the form for editing the specified vendor.
     */
    public function edit($id)
    {
        $vendor = Vendor::with(['addresses' => function($query) {
            $query->orderBy('is_default', 'desc')->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        // Get active purchase executives for dropdown
        $purchaseExecutives = Salesman::purchaseExecutives()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['_id', 'name']);

        return view('admin.vendors.edit', compact('vendor', 'purchaseExecutives'));
    }

    /**
     * Update the specified vendor.
     */
    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:15|unique:vendors,phone,' . $id,
            'email' => 'nullable|email|unique:vendors,email,' . $id,
            'gst_number' => [
                'nullable',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                'unique:vendors,gst_number,' . $id
            ],
            'pan_number' => [
                'nullable',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
                'unique:vendors,pan_number,' . $id
            ],
            'opening_balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'account_holder_name' => 'nullable|string|max:100',
            'purchase_executive_id' => 'nullable|exists:salesmen,_id',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $vendor->update([
                'company_name' => $request->company_name,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'gst_number' => strtoupper($request->gst_number),
                'pan_number' => strtoupper($request->pan_number),
                'opening_balance' => $request->opening_balance ?? 0,
                'credit_limit' => $request->credit_limit,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'ifsc_code' => strtoupper($request->ifsc_code),
                'account_holder_name' => $request->account_holder_name,
                'purchase_executive_id' => $request->purchase_executive_id,
                'status' => $request->status,
                'notes' => $request->notes
            ]);

            return redirect()->route('admin.vendors.index')
                ->with('success', 'Vendor updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating vendor: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display vendor ledger.
     */
    public function ledger($id)
    {
        $vendor = Vendor::with(['addresses'])->findOrFail($id);

        // Get all purchase invoices for this vendor
        $purchaseInvoices = PurchaseInvoice::where('vendor_id', $id)
            ->with(['items'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        // ========== LEDGER ENTRIES ==========
        $ledgerEntries = collect();

        // Add opening balance as first entry
        $ledgerEntries->push([
            'date' => null,
            'voucher_type' => 'Opening Balance',
            'voucher_no' => '-',
            'debit' => 0,
            'credit' => 0,
            'balance' => (float) ($vendor->opening_balance ?? 0),
            'is_opening' => true
        ]);

        // Add purchase invoices (Credit - amount owed to vendor)
        foreach ($purchaseInvoices as $invoice) {
            $ledgerEntries->push([
                'date' => $invoice->invoice_date,
                'voucher_type' => 'Purchase Invoice',
                'voucher_no' => $invoice->invoice_number,
                'debit' => 0, // Purchase is credit for vendor (we owe money)
                'credit' => (float) $invoice->grand_total,
                'balance' => 0,
                'reference_id' => $invoice->id,
                'is_opening' => false
            ]);
        }

        // TODO: Add payments when purchase payment module is ready
        // For now, just show purchases

        // Sort by date and calculate running balance
        $ledgerEntries = $ledgerEntries->sortBy(function($entry) {
            return $entry['date'] ?? now()->subYears(100);
        })->values();

        $balance = 0;
        $ledgerEntries = $ledgerEntries->map(function($entry) use (&$balance) {
            if ($entry['is_opening']) {
                $balance = $entry['balance'];
            } else {
                // For vendor: Credit increases balance (we owe more)
                // Debit decreases balance (we paid)
                $balance += $entry['credit'];
                $balance -= $entry['debit'];
                $entry['balance'] = $balance;
            }
            return $entry;
        });

        // Calculate summary
        $totalPurchases = $purchaseInvoices->sum('grand_total');
        $totalPaid = 0; // Will update when payments are added
        $balanceDue = $totalPurchases - $totalPaid + ($vendor->opening_balance ?? 0);

        return view('admin.vendors.ledger', compact(
            'vendor',
            'ledgerEntries',
            'purchaseInvoices',
            'totalPurchases',
            'totalPaid',
            'balanceDue'
        ));
    }

    // ========== ADDRESS MANAGEMENT METHODS ==========

    /**
     * Store a new address for vendor (AJAX).
     */
    public function storeAddress(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:billing,shipping',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'country' => 'required|string|max:100',
            'landmark' => 'nullable|string|max:200',
            'contact_person' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:15',
            'is_default' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            if ($request->is_default) {
                VendorAddress::where('vendor_id', $id)
                    ->where('type', $request->type)
                    ->update(['is_default' => false]);
            }

            $address = $vendor->addresses()->create([
                'type' => $request->type,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'country' => $request->country,
                'landmark' => $request->landmark,
                'contact_person' => $request->contact_person,
                'contact_number' => $request->contact_number,
                'is_default' => $request->is_default ?? false
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($request->type) . ' address added successfully',
                'address' => $address
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an address (AJAX).
     */
    public function updateAddress(Request $request, $addressId)
    {
        $address = VendorAddress::findOrFail($addressId);

        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'country' => 'required|string|max:100',
            'landmark' => 'nullable|string|max:200',
            'contact_person' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:15',
            'is_default' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            if ($request->is_default) {
                VendorAddress::where('vendor_id', $address->vendor_id)
                    ->where('type', $address->type)
                    ->where('id', '!=', $addressId)
                    ->update(['is_default' => false]);
            }

            $address->update([
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'country' => $request->country,
                'landmark' => $request->landmark,
                'contact_person' => $request->contact_person,
                'contact_number' => $request->contact_number,
                'is_default' => $request->is_default ?? $address->is_default
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'address' => $address->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an address (AJAX).
     */
    public function destroyAddress($addressId)
    {
        $address = VendorAddress::findOrFail($addressId);

        try {
            $address->delete();

            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting address'
            ], 500);
        }
    }

    /**
     * Set address as default (AJAX).
     */
    public function setDefaultAddress(Request $request, $addressId)
    {
        $address = VendorAddress::findOrFail($addressId);

        try {
            VendorAddress::where('vendor_id', $address->vendor_id)
                ->where('type', $address->type)
                ->update(['is_default' => false]);

            $address->update(['is_default' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Address set as default'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error setting default address'
            ], 500);
        }
    }

    /**
     * Get addresses by type (AJAX).
     */
    public function getAddresses($id, $type)
    {
        $vendor = Vendor::findOrFail($id);
        $addresses = $vendor->addresses()
            ->where('type', $type)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'addresses' => $addresses
        ]);
    }

    // ========== BULK ACTIONS ==========

    /**
     * Bulk update vendor status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_ids' => 'required|array',
            'vendor_ids.*' => 'required|exists:vendors,id',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            Vendor::whereIn('id', $request->vendor_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== AJAX SEARCH METHODS ==========

    /**
     * Search vendors (AJAX).
     */
    public function searchVendors(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $vendors = Vendor::where(function($query) use ($q) {
                $query->where('company_name', 'like', "%$q%")
                      ->orWhere('name', 'like', "%$q%")
                      ->orWhere('phone', 'like', "%$q%")
                      ->orWhere('email', 'like', "%$q%")
                      ->orWhere('gst_number', 'like', "%$q%");
            })
            ->select(['id', 'company_name', 'name', 'phone', 'email', 'gst_number', 'status'])
            ->orderBy('company_name')
            ->limit(20)
            ->get();

        return response()->json($vendors);
    }

    /**
     * Get vendor details by ID (AJAX)
     */
    public function getVendorDetails($id)
    {
        $vendor = Vendor::with(['addresses' => function($query) {
            $query->orderBy('is_default', 'desc');
        }])->find($id);

        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        return response()->json([
            'id' => $vendor->id,
            'company_name' => $vendor->company_name,
            'name' => $vendor->name,
            'phone' => $vendor->phone,
            'email' => $vendor->email,
            'gst_number' => $vendor->gst_number,
            'credit_limit' => $vendor->credit_limit,
            'addresses' => $vendor->addresses,
            'default_billing' => $vendor->defaultBillingAddress(),
            'default_shipping' => $vendor->defaultShippingAddress(),
        ]);
    }
}
