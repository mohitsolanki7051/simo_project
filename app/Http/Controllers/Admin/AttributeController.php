<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttributeController extends Controller
{
    /**
     * Display a listing of attributes
     */
    public function index()
    {
        $attributes = Attribute::ordered()->get();
        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new attribute
     */
    public function create()
    {
        // Predefined attribute types
        $attributeTypes = [
            'color_temperature' => 'Color Temperature',
            'watt' => 'Watt',
            'shape' => 'Shape'
        ];

        return view('admin.attributes.create', compact('attributeTypes'));
    }

    /**
     * Store a newly created attribute
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:color_temperature,watt,shape',
            'value' => 'required|string|max:100|unique:attributes,value,NULL,_id,type,' . $request->type,
            'status' => 'required|in:active,inactive'
        ]);

        try {
            // Get attribute name based on type
            $attributeNames = [
                'color_temperature' => 'Color Temperature',
                'watt' => 'Watt',
                'shape' => 'Shape'
            ];

            $attribute = Attribute::create([
                'name' => $attributeNames[$validated['type']],
                'type' => $validated['type'],
                'value' => trim($validated['value']),
                'status' => $validated['status']
            ]);

            return redirect()->route('admin.attributes.index')
                ->with('success', 'Attribute value created successfully!');

        } catch (\Exception $e) {
            Log::error('Attribute creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to create attribute value. Please try again.');
        }
    }

    /**
     * Show the form for editing the attribute
     */
    public function edit($id)
    {
        $attribute = Attribute::findOrFail($id);

        $attributeTypes = [
            'color_temperature' => 'Color Temperature',
            'watt' => 'Watt',
            'shape' => 'Shape'
        ];

        return view('admin.attributes.edit', compact('attribute', 'attributeTypes'));
    }

    /**
     * Update the attribute
     */
    public function update(Request $request, $id)
    {
        $attribute = Attribute::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:color_temperature,watt,shape',
            'value' => 'required|string|max:100|unique:attributes,value,' . $id . ',_id,type,' . $request->type,
            'status' => 'required|in:active,inactive'
        ]);

        try {
            // Get attribute name based on type
            $attributeNames = [
                'color_temperature' => 'Color Temperature',
                'watt' => 'Watt',
                'shape' => 'Shape'
            ];

            $attribute->update([
                'name' => $attributeNames[$validated['type']],
                'type' => $validated['type'],
                'value' => trim($validated['value']),
                'status' => $validated['status']
            ]);

            return redirect()->route('admin.attributes.index')
                ->with('success', 'Attribute value updated successfully!');

        } catch (\Exception $e) {
            Log::error('Attribute update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update attribute value. Please try again.');
        }
    }

    /**
     * Remove the attribute
     */
    public function destroy($id)
    {
        try {
            $attribute = Attribute::findOrFail($id);
            $attribute->delete();

            return redirect()->route('admin.attributes.index')
                ->with('success', 'Attribute value deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Attribute deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete attribute value. Please try again.');
        }
    }

    /**
     * Get active attributes for product creation (AJAX)
     */
    public function getActiveAttributes()
    {
        try {
            $groupedAttributes = Attribute::getGroupedAttributes();

            return response()->json([
                'success' => true,
                'attributes' => $groupedAttributes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attributes'
            ], 500);
        }
    }

    /**
     * Bulk delete attributes
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'attribute_ids' => 'required|array'
            ]);

            Attribute::whereIn('_id', $request->attribute_ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attributes deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk attribute deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attributes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update attributes status
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                'attribute_ids' => 'required|array',
                'status' => 'required|in:active,inactive'
            ]);

            Attribute::whereIn('_id', $request->attribute_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Attributes status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attributes status'
            ], 500);
        }
    }
}
