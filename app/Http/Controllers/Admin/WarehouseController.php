<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses
     */
    public function index()
    {
        $warehouses = Warehouse::orderBy('created_at', 'desc')->get();
        return view('admin.warehouses.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new warehouse
     */
    public function create()
    {
        return view('admin.warehouses.create');
    }

    /**
     * Store a newly created warehouse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'phone' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            Warehouse::create($validated);

            return redirect()->route('admin.warehouses.index')
                ->with('success', 'Warehouse created successfully!');

        } catch (\Exception $e) {
            Log::error('Warehouse creation failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to create warehouse. Please try again.');
        }
    }

    /**
     * Show the form for editing the warehouse
     */
    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        return view('admin.warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the warehouse
     */
    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $id,
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'phone' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $warehouse->update($validated);

            return redirect()->route('admin.warehouses.index')
                ->with('success', 'Warehouse updated successfully!');

        } catch (\Exception $e) {
            Log::error('Warehouse update failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to update warehouse. Please try again.');
        }
    }

    /**
     * Remove the warehouse
     */
    public function destroy($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            $warehouse->delete();

            return redirect()->route('admin.warehouses.index')
                ->with('success', 'Warehouse deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Warehouse deletion failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to delete warehouse. Please try again.');
        }
    }

    /**
     * Get warehouse details (AJAX)
     */
    public function show($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            return response()->json([
                'success' => true,
                'warehouse' => $warehouse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Warehouse not found'
            ], 404);
        }
    }

    /**
     * Bulk update warehouses status (AJAX)
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                'warehouse_ids' => 'required|array',
                'status' => 'required|in:active,inactive'
            ]);

            Warehouse::whereIn('id', $request->warehouse_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Warehouses status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update warehouses status'
            ], 500);
        }
    }

    /**
     * Bulk delete warehouses (AJAX)
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'warehouse_ids' => 'required|array'
            ]);

            Warehouse::whereIn('id', $request->warehouse_ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Warehouses deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk warehouse deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete warehouses: ' . $e->getMessage()
            ], 500);
        }
    }
}
