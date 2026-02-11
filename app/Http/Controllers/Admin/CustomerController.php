<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index()
    {
        $customers = Customer::with(['addresses' => function($query) {
            $query->where('is_default', true)
                  ->orWhere(function($q) {
                      $q->where('type', 'billing')->orderBy('is_default', 'desc');
                  });
        }])->orderBy('created_at', 'desc')->get();

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer with addresses.
     */
    public function store(Request $request)
    {
        // Validate customer data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:customers,phone,NULL,_id',
            'email' => 'nullable|email|unique:customers,email,NULL,_id',
            'status' => 'required|in:active,inactive',
            'customer_type' => 'required|in:individual,business',
            'company_name' => 'required_if:customer_type,business',
            'gst_number' => [
                'nullable',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
            ],

            'pan_number' => [
                'nullable',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
            ],

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
            // Create customer
            $customer = Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'status' => $request->status,
                'customer_type' => $request->customer_type,
                'company_name' => $request->customer_type == 'business' ? $request->company_name : null,
                'gst_number' => strtoupper($request->gst_number),
                'pan_number' => strtoupper($request->pan_number),
                'notes' => $request->notes
            ]);

            // Process billing addresses
            if ($request->billing_addresses) {
                $billingAddresses = json_decode($request->billing_addresses, true);

                // Debug
                // \Log::info('Billing Addresses:', $billingAddresses);

                if (is_array($billingAddresses) && count($billingAddresses) > 0) {
                    $hasDefaultBilling = false;

                    foreach ($billingAddresses as $address) {
                        // Check keys - यहाँ ध्यान दें कि JavaScript में contactPerson है, controller में contact_person check कर रहा है
                        $isDefault = isset($address['isDefault']) && $address['isDefault'] === true;

                        // Ensure only one default billing address
                        if ($isDefault && $hasDefaultBilling) {
                            $isDefault = false;
                        } elseif ($isDefault) {
                            $hasDefaultBilling = true;
                        }

                        CustomerAddress::create([
                            'customer_id' => $customer->id,
                            'type' => 'billing',
                            'address' => $address['address'] ?? '',
                            'city' => $address['city'] ?? '',
                            'state' => $address['state'] ?? '',
                            'pincode' => $address['pincode'] ?? '',
                            'country' => $address['country'] ?? 'India',
                            'landmark' => $address['landmark'] ?? '',
                            'contact_person' => $address['contactPerson'] ?? '', // यहाँ ध्यान दें
                            'contact_number' => $address['contactNumber'] ?? '', // यहाँ ध्यान दें
                            'is_default' => $isDefault
                        ]);
                    }
                }
            }

            // Process shipping addresses
            if ($request->shipping_addresses) {
                $shippingAddresses = json_decode($request->shipping_addresses, true);

                // Debug
                // \Log::info('Shipping Addresses:', $shippingAddresses);

                if (is_array($shippingAddresses) && count($shippingAddresses) > 0) {
                    $hasDefaultShipping = false;

                    foreach ($shippingAddresses as $address) {
                        $isDefault = isset($address['isDefault']) && $address['isDefault'] === true;

                        // Ensure only one default shipping address
                        if ($isDefault && $hasDefaultShipping) {
                            $isDefault = false;
                        } elseif ($isDefault) {
                            $hasDefaultShipping = true;
                        }

                        CustomerAddress::create([
                            'customer_id' => $customer->id,
                            'type' => 'shipping',
                            'address' => $address['address'] ?? '',
                            'city' => $address['city'] ?? '',
                            'state' => $address['state'] ?? '',
                            'pincode' => $address['pincode'] ?? '',
                            'country' => $address['country'] ?? 'India',
                            'landmark' => $address['landmark'] ?? '',
                            'contact_person' => $address['contactPerson'] ?? '', // यहाँ ध्यान दें
                            'contact_number' => $address['contactNumber'] ?? '', // यहाँ ध्यान दें
                            'is_default' => $isDefault
                        ]);
                    }
                }
            }

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating customer: ' . $e->getMessage())
                ->withInput();
        }
    }



    /**
     * Show the form for editing the specified customer.
     */
    public function edit($id)
    {
        $customer = Customer::with(['addresses' => function($query) {
            $query->orderBy('is_default', 'desc')->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:customers,phone,' . $id . ',_id',
            'email' => 'nullable|email|unique:customers,email,' . $id . ',_id',
            'status' => 'required|in:active,inactive',
            'customer_type' => 'required|in:individual,business',
            'company_name' => 'required_if:customer_type,business',
            'gst_number' => [
                'nullable',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
            ],

            'pan_number' => [
                'nullable',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
            ],

            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $customer->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'status' => $request->status,
                'customer_type' => $request->customer_type,
                'company_name' => $request->customer_type == 'business' ? $request->company_name : null,
                'gst_number' => strtoupper($request->gst_number),
                'pan_number' => strtoupper($request->pan_number),
                'notes' => $request->notes
            ]);

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating customer: ' . $e->getMessage())
                ->withInput();
        }
    }



    /**
     * Store a new address for customer (AJAX).
     */
    public function storeAddress(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

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
            // If this address is set as default, remove default from other addresses of same type
            if ($request->is_default) {
                CustomerAddress::where('customer_id', $id)
                    ->where('type', $request->type)
                    ->update(['is_default' => false]);
            }

            $address = $customer->addresses()->create([
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
     * Update an address.
     */
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
            // If this address is set as default, remove default from other addresses of same type
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

    /**
     * Delete an address.
     */
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

    /**
     * Set address as default.
     */
    public function setDefaultAddress(Request $request, $addressId)
    {
        $address = CustomerAddress::findOrFail($addressId);

        try {
            // Remove default from all addresses of same type for this customer
            CustomerAddress::where('customer_id', $address->customer_id)
                ->where('type', $address->type)
                ->update(['is_default' => false]);

            // Set this address as default
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
     * Get addresses by type for a customer.
     */
    public function getAddresses($id, $type)
    {
        $customer = Customer::findOrFail($id);
        $addresses = $customer->addresses()
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
     * Bulk update customer status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'required|exists:customers,_id',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            // Convert string IDs to MongoDB ObjectIds if using MongoDB
            $customerIds = $request->customer_ids;

            Customer::whereIn('_id', $customerIds)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Customer status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating customer status: ' . $e->getMessage()
            ], 500);
        }
    }
}
