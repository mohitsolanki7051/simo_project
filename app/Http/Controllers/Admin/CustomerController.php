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
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            Customer::whereIn('_id', $request->party_ids)
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

    public function ledger($id)
    {
        $party = Customer::findOrFail($id);

        $sales = SalesInvoice::where('customer_id', $id)
            ->get()
            ->map(function ($invoice) {
                return [
                    'date' => $invoice->invoice_date,
                    'type' => 'Sale',
                    'ref'  => $invoice->invoice_number,
                    'invoice_id' => $invoice->_id,
                    'debit' => (float) $invoice->grand_total,
                    'credit' => 0,
                ];
            });

        $invoiceIds = SalesInvoice::where('customer_id', $id)
            ->get()
            ->map(function ($invoice) {
                return (string) $invoice->_id;
            })
            ->toArray();

        $payments = SalesPayment::whereIn('sales_invoice_id', $invoiceIds)
            ->where('status', 'completed')
            ->get()
            ->map(function ($payment) {
                return [
                    'date' => $payment->payment_date,
                    'type' => 'Payment',
                    'ref'  => $payment->reference_no ?? '-',
                    'debit' => 0,
                    'credit' => (float) $payment->amount,
                ];
            });

        $advance = SalesPayment::whereNull('sales_invoice_id')
            ->where('status', 'completed')
            ->where('customer_id', $id)
            ->get()
            ->map(function ($payment) {
                return [
                    'date' => $payment->payment_date,
                    'type' => 'Advance',
                    'ref'  => $payment->reference_no ?? 'ADV',
                    'debit' => 0,
                    'credit' => (float) $payment->amount,
                ];
            });

        $discounts = SalesInvoice::where('customer_id', $id)
            ->where('extra_discount', '>', 0)
            ->get()
            ->map(function ($invoice) {
                return [
                    'date' => $invoice->invoice_date,
                    'type' => 'Discount',
                    'ref'  => $invoice->invoice_number,
                    'debit' => 0,
                    'credit' => (float) $invoice->extra_discount,
                ];
            });

        $charges = SalesInvoice::where('customer_id', $id)
            ->where('extra_charge', '>', 0)
            ->get()
            ->map(function ($invoice) {
                return [
                    'date' => $invoice->invoice_date,
                    'type' => $invoice->charge_name ?? 'Charge',
                    'ref'  => $invoice->invoice_number,
                    'debit' => (float) $invoice->extra_charge,
                    'credit' => 0,
                ];
            });

        $ledger = collect()
            ->merge($sales)
            ->merge($payments)
            ->merge($advance)
            ->merge($discounts)
            ->merge($charges)
            ->sortBy('date')
            ->values();

        $balance = 0;
        $ledger = $ledger->map(function ($row) use (&$balance) {
            $balance += $row['debit'];
            $balance -= $row['credit'];
            $row['balance'] = $balance;
            return $row;
        });

        return view('admin.parties.ledger', compact('party', 'ledger'));
    }
}
