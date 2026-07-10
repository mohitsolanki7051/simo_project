<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\SalesInvoice;
use App\Models\SalesPayment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use MongoDB\BSON\ObjectId;

class CustomerController extends Controller
{
    /**
     * Display a listing of parties based on type.
     */
    public function index(Request $request, $type = null)
    {
        // If type is provided in URL, use it, otherwise check query parameter
        $partyType = $type ?? $request->get('type', 'customer');

        // Validate party type
        if (!in_array($partyType, ['customer', 'dealer', 'distributor'])) {
            abort(404);
        }

        $parties = Customer::with(['addresses' => function($query) {
                $query->where('is_default', true)
                      ->orWhere(function($q) {
                          $q->where('type', 'billing')->orderBy('is_default', 'desc');
                      });
            }])
            ->where('party_type', $partyType)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.parties.index', compact('parties', 'partyType'));
    }

public function create(Request $request)
{
    $partyType = $request->get('type', 'customer');

    if (!in_array($partyType, ['customer', 'dealer', 'distributor'])) {
        abort(404);
    }

    // Get distributors for dealer parent selection
    $distributors = [];
    if ($partyType === 'dealer') {
        $distributors = Customer::where('party_type', 'distributor')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['_id', 'name']);
    }

    // NEW: Get active salesmen for dropdown
    $salesmen = \App\Models\Salesman::where('status', 'active')
        ->whereNull('type')
        ->orderBy('name')
        ->get(['_id', 'name']);

    return view('admin.parties.create', compact('partyType', 'distributors', 'salesmen'));
}

    /**
     * Store a newly created party with addresses.
     */
    public function store(Request $request)
    {
        // Validate party data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:customers,phone,NULL,_id',
            'email' => 'nullable|email|unique:customers,email,NULL,_id',
            'salesman_id' => 'nullable|exists:salesmen,_id',
            'party_type' => 'required|in:customer,dealer,distributor',
            'opening_balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'parent_party_id' => 'nullable|exists:customers,_id',
            'gst_number' => [
                'nullable',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
            ],
            'pan_number' => [
                'nullable',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
            ],
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
            // Create party
            $party = Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'party_type' => $request->party_type,
                'opening_balance' => $request->opening_balance ?? 0,
                'credit_limit' => $request->credit_limit,
                'salesman_id' => $request->salesman_id,
                'parent_party_id' => $request->parent_party_id,
                'gst_number' => strtoupper($request->gst_number),
                'pan_number' => strtoupper($request->pan_number),
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

                        CustomerAddress::create([
                            'customer_id' => $party->id,
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

                        CustomerAddress::create([
                            'customer_id' => $party->id,
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

            return redirect()->route('admin.parties.index', ['type' => $request->party_type])
                ->with('success', ucfirst($request->party_type) . ' created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating party: ' . $e->getMessage())
                ->withInput();
        }
    }

public function edit($id)
{
    $party = Customer::with(['addresses' => function($query) {
        $query->orderBy('is_default', 'desc')->orderBy('created_at', 'desc');
    }])->findOrFail($id);

    // Get distributors for dealer parent selection
    $distributors = [];
    if ($party->party_type === 'dealer') {
        $distributors = Customer::where('party_type', 'distributor')
            ->where('status', 'active')
            ->where('_id', '!=', $id)
            ->orderBy('name')
            ->get(['_id', 'name']);
    }

    // NEW: Get active salesmen for dropdown
    $salesmen = \App\Models\Salesman::where('status', 'active')
        ->whereNull('type')
        ->orderBy('name')
        ->get(['_id', 'name']);

    return view('admin.parties.edit', compact('party', 'distributors', 'salesmen'));
}

    /**
     * Update the specified party.
     */
    public function update(Request $request, $id)
    {
        $party = Customer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:customers,phone,' . $id . ',_id',
            'email' => 'nullable|email|unique:customers,email,' . $id . ',_id',
            'salesman_id' => 'nullable|exists:salesmen,_id',
            'opening_balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'parent_party_id' => 'nullable|exists:customers,_id',
            'gst_number' => [
                'nullable',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
            ],
            'pan_number' => [
                'nullable',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
            ],
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $party->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'opening_balance' => $request->opening_balance ?? 0,
                'credit_limit' => $request->credit_limit,
                'salesman_id' => $request->salesman_id,
                'parent_party_id' => $request->parent_party_id,
                'gst_number' => strtoupper($request->gst_number),
                'pan_number' => strtoupper($request->pan_number),
                'status' => $request->status,
                'notes' => $request->notes
            ]);

            return redirect()->route('admin.parties.index', ['type' => $party->party_type])
                ->with('success', ucfirst($party->party_type) . ' updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating party: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Address management methods remain the same...

    /**
     * Store a new address for party (AJAX).
     */
    public function storeAddress(Request $request, $id)
    {
        $party = Customer::findOrFail($id);

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
                CustomerAddress::where('customer_id', $id)
                    ->where('type', $request->type)
                    ->update(['is_default' => false]);
            }

            $address = $party->addresses()->create([
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

    public function updateAddress(Request $request, $addressId)
    {
        $address = CustomerAddress::findOrFail($addressId);

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
                CustomerAddress::where('customer_id', $address->customer_id)
                    ->where('type', $address->type)
                    ->where('_id', '!=', $addressId)
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

    public function destroyAddress($addressId)
    {
        $address = CustomerAddress::findOrFail($addressId);

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

    public function setDefaultAddress(Request $request, $addressId)
    {
        $address = CustomerAddress::findOrFail($addressId);

        try {
            CustomerAddress::where('customer_id', $address->customer_id)
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

    public function getAddresses($id, $type)
    {
        $party = Customer::findOrFail($id);
        $addresses = $party->addresses()
            ->where('type', $type)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'addresses' => $addresses
        ]);
    }

    /**
     * Bulk update party status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'party_ids' => 'required|array',
            'party_ids.*' => 'required|exists:customers,_id',
            'status' => 'required|in:active,inactive,delete'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            if ($request->status === 'delete') {
                $parties = Customer::whereIn('_id', $request->party_ids)->get();
                $deletedCount = 0;
                $skippedCount = 0;

                foreach ($parties as $party) {
                    // Check if there are child parties
                    $hasDealers = Customer::where('parent_party_id', $party->id)->exists();

                    // Check for transaction records
                    $hasTransactions = \App\Models\SalesInvoice::where('party_id', $party->id)->exists()
                        || \App\Models\SalesPayment::where('party_id', $party->id)->exists()
                        || \App\Models\Quotation::where('party_id', $party->id)->exists()
                        || \App\Models\SalesReturn::where('party_id', $party->id)->exists()
                        || \App\Models\CreditNote::where('party_id', $party->id)->exists()
                        || \App\Models\DebitNote::where('party_id', $party->id)->exists()
                        || \App\Models\PurchaseReturn::where('party_id', $party->id)->exists()
                        || \App\Models\PurchaseInvoice::where('party_id', $party->id)->exists()
                        || \App\Models\PurchasePayment::where('party_id', $party->id)->exists()
                        || \App\Models\WarrantyClaim::where('party_id', $party->id)->exists();

                    if ($hasDealers || $hasTransactions) {
                        $skippedCount++;
                    } else {
                        CustomerAddress::where('customer_id', $party->id)->delete();
                        $party->delete();
                        $deletedCount++;
                    }
                }

                if ($deletedCount === 0 && $skippedCount > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to delete selected parties because they all have associated transactions/dealers.'
                    ], 400);
                }

                return response()->json([
                    'success' => true,
                    'message' => "Successfully deleted {$deletedCount} parties." . ($skippedCount > 0 ? " Skipped {$skippedCount} parties with transactions." : "")
                ]);
            }

            Customer::whereIn('_id', $request->party_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

public function ledger($id)
{
    $party = Customer::with(['addresses'])->findOrFail($id);

    // Get all sales invoices for this party
    $salesInvoices = SalesInvoice::where('party_id', $id)
        ->with(['items.product'])
        ->orderBy('invoice_date', 'desc')
        ->get();

    $invoiceIds = $salesInvoices->pluck('_id')->map(function($id) {
        return (string) $id;
    })->toArray();

    // Get all payments (both against invoices and advance)
    $payments = SalesPayment::where(function($query) use ($id, $invoiceIds) {
            $query->whereIn('sales_invoice_id', $invoiceIds)
                  ->orWhere(function($q) use ($id) {
                      $q->whereNull('sales_invoice_id')
                        ->where('customer_id', $id);
                  });
        })
        ->where('status', 'completed')
        ->orderBy('payment_date', 'desc')
        ->get();

    // ========== 1. LEDGER ENTRIES (For Ledger Tab) ==========
    $ledgerEntries = collect();

    // Add opening balance as first entry
    $ledgerEntries->push([
        'date' => null,
        'voucher_type' => 'Opening Balance',
        'voucher_no' => '-',
        'debit' => 0,
        'credit' => 0,
        'balance' => (float) ($party->opening_balance ?? 0),
        'is_opening' => true
    ]);

    // Add sales invoices
    foreach ($salesInvoices as $invoice) {
        $ledgerEntries->push([
            'date' => $invoice->invoice_date,
            'voucher_type' => 'Sales Invoice',
            'voucher_no' => $invoice->invoice_number,
            'debit' => (float) $invoice->grand_total,
            'credit' => 0,
            'balance' => 0, // Will calculate later
            'reference_id' => (string) $invoice->_id,
            'is_opening' => false
        ]);
    }

    // Add payments
    foreach ($payments as $payment) {
        $voucherType = $payment->sales_invoice_id ? 'Payment Received' : 'Advance Payment';
        $voucherNo = $payment->reference_no ?? ($payment->sales_invoice_id ? 'PAY-'.substr((string)$payment->_id, -6) : 'ADV-'.substr((string)$payment->_id, -6));

        $ledgerEntries->push([
            'date' => $payment->payment_date,
            'voucher_type' => $voucherType,
            'voucher_no' => $voucherNo,
            'debit' => 0,
            'credit' => (float) $payment->amount,
            'balance' => 0, // Will calculate later
            'reference_id' => (string) $payment->_id,
            'is_opening' => false
        ]);

        if ((float)($payment->discount ?? 0) > 0) {
            $ledgerEntries->push([
                'date' => $payment->payment_date,
                'voucher_type' => 'Discount Allowed',
                'voucher_no' => $voucherNo . '-D',
                'debit' => 0,
                'credit' => (float) $payment->discount,
                'balance' => 0, // Will calculate later
                'reference_id' => (string) $payment->_id,
                'is_opening' => false
            ]);
        }
    }

    // Add discounts (credit notes)
    foreach ($salesInvoices as $invoice) {
        if ($invoice->extra_discount > 0) {
            $ledgerEntries->push([
                'date' => $invoice->invoice_date,
                'voucher_type' => 'Discount',
                'voucher_no' => $invoice->invoice_number,
                'debit' => 0,
                'credit' => (float) $invoice->extra_discount,
                'balance' => 0,
                'reference_id' => (string) $invoice->_id,
                'is_opening' => false
            ]);
        }
    }

    // Add charges
    foreach ($salesInvoices as $invoice) {
        if ($invoice->extra_charge > 0) {
            $ledgerEntries->push([
                'date' => $invoice->invoice_date,
                'voucher_type' => $invoice->charge_name ?? 'Extra Charge',
                'voucher_no' => $invoice->invoice_number,
                'debit' => (float) $invoice->extra_charge,
                'credit' => 0,
                'balance' => 0,
                'reference_id' => (string) $invoice->_id,
                'is_opening' => false
            ]);
        }
    }

    // Sort by date and calculate running balance
    $ledgerEntries = $ledgerEntries->sortBy(function($entry) {
        return $entry['date'] ?? now()->subYears(100); // Put opening balance first
    })->values();

    $balance = 0;
    $ledgerEntries = $ledgerEntries->map(function($entry) use (&$balance) {
        if ($entry['is_opening']) {
            $balance = $entry['balance'];
        } else {
            $balance += $entry['debit'];
            $balance -= $entry['credit'];
            $entry['balance'] = $balance;
        }
        return $entry;
    });

    // ========== 2. TRANSACTIONS (For Transactions Tab) ==========
    $transactions = collect();

    // Add sales invoices as transactions
    foreach ($salesInvoices as $invoice) {
        $transactions->push([
            'date' => $invoice->invoice_date,
            'type' => 'Sales Invoice',
            'type_badge' => 'sale',
            'reference' => $invoice->invoice_number,
            'amount' => (float) $invoice->grand_total,
            'status' => $invoice->payment_status,
            'status_badge' => $invoice->payment_status,
            'details' => [
                'subtotal' => $invoice->subtotal,
                'discount' => $invoice->discount_total,
                'tax' => $invoice->tax_total,
                'items_count' => $invoice->items->count()
            ],
            'link' => route('admin.sales.show', $invoice->_id)
        ]);
    }

    // Add payments as transactions
    foreach ($payments as $payment) {
        $type = $payment->sales_invoice_id ? 'Payment Received' : 'Advance Payment';
        $reference = $payment->reference_no ?? ($payment->sales_invoice_id ? 'Against Invoice' : 'Advance');

        $transactions->push([
            'date' => $payment->payment_date,
            'type' => $type,
            'type_badge' => 'payment',
            'reference' => $reference,
            'amount' => (float) $payment->amount,
            'status' => $payment->status,
            'status_badge' => $payment->status,
            'details' => [
                'method' => $payment->payment_method,
                'invoice_no' => $payment->sales_invoice_id ?
                    ($salesInvoices->firstWhere('_id', $payment->sales_invoice_id)?->invoice_number ?? '-') : '-'
            ]
        ]);
    }

    // Sort transactions by date (newest first)
    $transactions = $transactions->sortByDesc('date')->values();

    // ========== 3. ITEM WISE REPORT (For Item Wise Tab) ==========
    $itemWiseReport = collect();
    $productSummary = [];

    foreach ($salesInvoices as $invoice) {
        foreach ($invoice->items as $item) {
            $productId = (string) ($item->product_id ?? $item->variant_id ?? 'unknown');
            $productName = $item->product_name ?? $item->variant_name ?? 'Unknown Product';
            $sku = $item->sku ?? '-';
            $hsn = $item->hsn_sac ?? '-';

            if (!isset($productSummary[$productId])) {
                $productSummary[$productId] = [
                    'product_name' => $productName,
                    'sku' => $sku,
                    'hsn' => $hsn,
                    'total_quantity' => 0,
                    'total_amount' => 0,
                    'invoices' => []
                ];
            }

            $productSummary[$productId]['total_quantity'] += (float) $item->quantity;
            $productSummary[$productId]['total_amount'] += (float) $item->total;
            $productSummary[$productId]['invoices'][] = [
                'invoice_no' => $invoice->invoice_number,
                'date' => $invoice->invoice_date,
                'quantity' => (float) $item->quantity,
                'price' => (float) $item->price,
                'total' => (float) $item->total
            ];
        }
    }

    foreach ($productSummary as $productId => $summary) {
        $itemWiseReport->push([
            'product_id' => $productId,
            'product_name' => $summary['product_name'],
            'sku' => $summary['sku'],
            'hsn' => $summary['hsn'],
            'total_quantity' => $summary['total_quantity'],
            'total_amount' => $summary['total_amount'],
            'invoices' => collect($summary['invoices'])->sortByDesc('date')->values()
        ]);
    }

    $itemWiseReport = $itemWiseReport->sortByDesc('total_amount')->values();

    // Calculate summary statistics
    $totalSales = $salesInvoices->sum('grand_total');
    $totalPayments = $payments->sum('amount');
    $totalDiscounts = $salesInvoices->sum('extra_discount');
    $totalItems = $salesInvoices->sum(function($invoice) {
        return $invoice->items->sum('quantity');
    });

    return view('admin.parties.ledger', compact(
        'party',
        'ledgerEntries',
        'transactions',
        'itemWiseReport',
        'salesInvoices',
        'payments',
        'totalSales',
        'totalPayments',
        'totalDiscounts',
        'totalItems'
    ));
}

    /**
     * Remove the specified party from storage.
     */
    public function destroy($id)
    {
        try {
            $party = Customer::findOrFail($id);

            // Check if there are child parties (dealers under a distributor)
            $hasDealers = Customer::where('parent_party_id', $id)->exists();
            if ($hasDealers) {
                return redirect()->back()->with('error', 'Cannot delete this distributor as it has dealers associated with it.');
            }

            // Check for transaction records
            $hasTransactions = \App\Models\SalesInvoice::where('party_id', $id)->exists()
                || \App\Models\SalesPayment::where('party_id', $id)->exists()
                || \App\Models\Quotation::where('party_id', $id)->exists()
                || \App\Models\SalesReturn::where('party_id', $id)->exists()
                || \App\Models\CreditNote::where('party_id', $id)->exists()
                || \App\Models\DebitNote::where('party_id', $id)->exists()
                || \App\Models\PurchaseReturn::where('party_id', $id)->exists()
                || \App\Models\PurchaseInvoice::where('party_id', $id)->exists()
                || \App\Models\PurchasePayment::where('party_id', $id)->exists()
                || \App\Models\WarrantyClaim::where('party_id', $id)->exists();

            if ($hasTransactions) {
                return redirect()->back()->with('error', 'Cannot delete this ' . $party->party_type . ' because they have associated transactions.');
            }

            // Delete addresses
            CustomerAddress::where('customer_id', $id)->delete();

            // Delete party
            $party->delete();

            return redirect()->route('admin.parties.index', ['type' => $party->party_type])
                ->with('success', ucfirst($party->party_type) . ' deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting party: ' . $e->getMessage());
        }
    }
}

