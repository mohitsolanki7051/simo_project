<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salesman;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalesmanController extends Controller
{
    /**
     * Display a listing of salesmen.
     */
    public function index()
    {
        $salesmen = Salesman::all();

        // Sirf un parties ko count karo jinke paas salesman assigned hai (salesman_id != null)
        $totalCustomers = Customer::where('party_type', 'customer')
                                ->whereNotNull('salesman_id')  // Sirf jinko salesman assign hai
                                ->count();

        $totalDealers = Customer::where('party_type', 'dealer')
                                ->whereNotNull('salesman_id')    // Sirf jinko salesman assign hai
                                ->count();

        $totalDistributors = Customer::where('party_type', 'distributor')
                                    ->whereNotNull('salesman_id')  // Sirf jinko salesman assign hai
                                    ->count();
         $totalPartiesWithSalesman = $totalCustomers + $totalDealers + $totalDistributors;

        return view('admin.salesmen.index', compact(
            'salesmen',
            'totalCustomers',
            'totalDealers',
            'totalDistributors',
            'totalPartiesWithSalesman'
        ));
    }

    /**
     * Show the form for creating a new salesman.
     */
    public function create()
    {
        return view('admin.salesmen.create');
    }

    /**
     * Store a newly created salesman in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:10|min:10',
            'email' => 'nullable|email|max:255',
            'joining_date' => 'required|date',
            'salary_type' => 'required|in:fixed,commission,both',
            'fixed_salary' => 'nullable|numeric|min:0|required_if:salary_type,fixed,both',
            'commission_enabled' => 'nullable|boolean',
            'customer_commission_percent' => 'nullable|numeric|min:0|max:100|required_if:commission_enabled,1',
            'dealer_commission_percent' => 'nullable|numeric|min:0|max:100|required_if:commission_enabled,1',
            'distributor_commission_percent' => 'nullable|numeric|min:0|max:100|required_if:commission_enabled,1',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        // Handle checkbox
        $data['commission_enabled'] = $request->has('commission_enabled');

        // Set commission percentages to 0 if not enabled
        if (!$data['commission_enabled']) {
            $data['customer_commission_percent'] = 0;
            $data['dealer_commission_percent'] = 0;
            $data['distributor_commission_percent'] = 0;
        }

        Salesman::create($data);

        return redirect()->route('admin.salesmen.index')
            ->with('success', 'Salesman created successfully.');
    }

    /**
     * Display the specified salesman.
     */
    public function show($id)
    {
        $salesman = Salesman::find($id);

        if (!$salesman) {
            return redirect()->route('admin.salesmen.index')
                ->with('error', 'Salesman not found.');
        }

        return view('admin.salesmen.show', compact('salesman'));
    }

    /**
     * Show the form for editing the specified salesman.
     */
    public function edit($id)
    {
        $salesman = Salesman::find($id);

        if (!$salesman) {
            return redirect()->route('admin.salesmen.index')
                ->with('error', 'Salesman not found.');
        }

        return view('admin.salesmen.edit', compact('salesman'));
    }

    /**
     * Update the specified salesman in storage.
     */
    public function update(Request $request, $id)
    {
        $salesman = Salesman::find($id);

        if (!$salesman) {
            return redirect()->route('admin.salesmen.index')
                ->with('error', 'Salesman not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:10|min:10',
            'email' => 'nullable|email|max:255',
            'joining_date' => 'required|date',
            'salary_type' => 'required|in:fixed,commission,both',
            'fixed_salary' => 'nullable|numeric|min:0|required_if:salary_type,fixed,both',
            'commission_enabled' => 'nullable|boolean',
            'customer_commission_percent' => 'nullable|numeric|min:0|max:100|required_if:commission_enabled,1',
            'dealer_commission_percent' => 'nullable|numeric|min:0|max:100|required_if:commission_enabled,1',
            'distributor_commission_percent' => 'nullable|numeric|min:0|max:100|required_if:commission_enabled,1',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        // Handle checkbox
        $data['commission_enabled'] = $request->has('commission_enabled');

        // Set commission percentages to 0 if not enabled
        if (!$data['commission_enabled']) {
            $data['customer_commission_percent'] = 0;
            $data['dealer_commission_percent'] = 0;
            $data['distributor_commission_percent'] = 0;
        }

        $salesman->update($data);

        return redirect()->route('admin.salesmen.index')
            ->with('success', 'Salesman updated successfully.');
    }

    /**
     * Get parties assigned to salesman (for AJAX)
     */
    public function getAssignedParties($id)
    {
        $salesman = Salesman::find($id);

        if (!$salesman) {
            return response()->json(['error' => 'Salesman not found'], 404);
        }

        // Get all parties with this salesman_id
        $parties = Customer::where('salesman_id', $id)->get();

        $customers = $parties->where('party_type', 'customer')->count();
        $dealers = $parties->where('party_type', 'dealer')->count();
        $distributors = $parties->where('party_type', 'distributor')->count();

        $recent = $parties->sortByDesc('created_at')->take(5)->map(function($party) {
            return [
                'name' => $party->name,
                'party_type' => ucfirst($party->party_type),
                'phone' => $party->phone,
                'status' => $party->status
            ];
        })->values();

        return response()->json([
            'customers' => $customers,
            'dealers' => $dealers,
            'distributors' => $distributors,
            'total' => $parties->count(),
            'recent' => $recent
        ]);
    }

    /**
     * Get party counts for dashboard (for AJAX)
     */
    public function getPartyCounts()
    {
        $customers = Customer::where('party_type', 'customer')->count();
        $dealers = Customer::where('party_type', 'dealer')->count();
        $distributors = Customer::where('party_type', 'distributor')->count();

        return response()->json([
            'customers' => $customers,
            'dealers' => $dealers,
            'distributors' => $distributors
        ]);
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'salesman_ids' => 'required|array',
            'salesman_ids.*' => 'required|string',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided.'
            ], 422);
        }

        try {
            Salesman::whereIn('_id', $request->salesman_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.'
            ], 500);
        }
    }

    /**
     * Check if phone number is available
     */
    public function checkPhone(Request $request)
    {
        $phone = $request->get('phone');
        $id = $request->get('id');

        $query = Salesman::where('phone', $phone);

        if ($id) {
            $query->where('_id', '!=', $id);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Phone number already exists' : 'Phone number is available'
        ]);
    }

    /**
     * Check if email is available
     */
    public function checkEmail(Request $request)
    {
        $email = $request->get('email');

        if (!$email) {
            return response()->json(['available' => true]);
        }

        $id = $request->get('id');

        $query = Salesman::where('email', $email);

        if ($id) {
            $query->where('_id', '!=', $id);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Email already exists' : 'Email is available'
        ]);
    }
}
