<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttributeController extends Controller
{
    /**
     * Display a listing of attributes
     */
/**
 * Display a listing of attributes
 */
public function index()
{
    // Get all attributes with ALL items
    $attributes = Attribute::with(['items' => function($query) {
        $query->orderBy('value', 'asc');
    }])->get();

    // Calculate statistics
    $activeAttributesCount = $attributes->where('status', 'active')->count();
    $inactiveAttributesCount = $attributes->where('status', 'inactive')->count();
    $totalAttributeValues = $attributes->sum(function($attribute) {
        return $attribute->items->count();
    });

    return view('admin.attributes.index', compact(
        'attributes',
        'activeAttributesCount',
        'inactiveAttributesCount',
        'totalAttributeValues'
    ));
}

    /**
     * Show the form for creating a new attribute
     */
    public function create()
    {
        // Get unique existing attribute types for reference
        $existingTypes = Attribute::select('type')->distinct()->pluck('type')->toArray();

        return view('admin.attributes.create', compact('existingTypes'));
    }

    /**
     * Store a newly created attribute with multiple values
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:100',
            'values' => 'required|array|min:1',
            'values.*' => 'required|string|max:100',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            // First create the attribute type
            $attribute = Attribute::create([
                'type' => strtolower(trim($validated['type'])),
                'status' => $validated['status']
            ]);

            $createdCount = 0;
            $duplicateCount = 0;

            // Then create attribute items with status
            foreach ($validated['values'] as $value) {
                // Check if this value already exists for this attribute type
                $exists = AttributeItem::where('attribute_type_id', $attribute->_id)
                    ->where('value', trim($value))
                    ->exists();

                if (!$exists) {
                    AttributeItem::create([
                        'attribute_type_id' => $attribute->_id,
                        'value' => trim($value),
                        'status' => $validated['status'] // Item gets same status as attribute
                    ]);
                    $createdCount++;
                } else {
                    $duplicateCount++;
                }
            }

            $message = "Attribute '{$validated['type']}' created with {$createdCount} value(s)!";
            if ($duplicateCount > 0) {
                $message .= " {$duplicateCount} duplicate value(s) were skipped.";
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'attribute' => [
                        'id' => (string) $attribute->id,
                        'type' => $attribute->type
                    ]
                ]);
            }

            return redirect()->route('admin.attributes.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Attribute creation failed: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create attribute. Please try again: ' . $e->getMessage()
                ], 500);
            }
            return back()->withInput()
                ->with('error', 'Failed to create attribute. Please try again.');
        }
    }

    /**
     * Show the form for editing the attribute
     */
/**
 * Show the form for editing the attribute
 */
public function edit($id)
{
    $attribute = Attribute::with(['items' => function($query) {
        $query->orderBy('value', 'asc');
    }])->findOrFail($id);

    return view('admin.attributes.edit', compact('attribute'));
}

/**
 * Update the attribute and its items status
 */
/**
 * Update the attribute and its items
 */
public function update(Request $request, $id)
{
    $attribute = Attribute::findOrFail($id);

    $validated = $request->validate([
        'type' => 'required|string|max:100',
        'status' => 'required|in:active,inactive',
        'existing_items' => 'nullable|array',
        'existing_items.*.id' => 'required|string',
        'existing_items.*.value' => 'required|string|max:100',
        'existing_items.*.status' => 'required|in:active,inactive',
        'new_values' => 'nullable|array',
        'new_values.*' => 'required|string|max:100'
    ]);

    try {
        // Get old status before update
        $oldStatus = $attribute->status;
        $newStatus = $validated['status'];

        // Update attribute type and status
        $attribute->update([
            'type' => strtolower(trim($validated['type'])),
            'status' => $newStatus
        ]);

        $updatedCount = 0;
        $createdCount = 0;
        $autoUpdatedCount = 0;

        // Check if attribute status changed from active to inactive
        $statusChangedToInactive = ($oldStatus == 'active' && $newStatus == 'inactive');

        // Update existing items from form data
        if ($request->has('existing_items')) {
            foreach ($request->existing_items as $itemData) {
                if (isset($itemData['id']) && isset($itemData['value']) && isset($itemData['status'])) {
                    $item = AttributeItem::find($itemData['id']);
                    if ($item && $item->attribute_type_id == $attribute->_id) {
                        // If attribute status changed to inactive, force all items to inactive
                        $newItemStatus = $statusChangedToInactive ? 'inactive' : $itemData['status'];

                        // Update both value and status
                        $item->update([
                            'value' => trim($itemData['value']),
                            'status' => $newItemStatus
                        ]);
                        $updatedCount++;
                    }
                }
            }
        }

        // If attribute status changed to inactive, update ALL items to inactive
        if ($statusChangedToInactive) {
            $autoUpdated = AttributeItem::where('attribute_type_id', $attribute->_id)
                ->where('status', '!=', 'inactive')
                ->update(['status' => 'inactive']);

            $autoUpdatedCount = $autoUpdated;
        }

        // Create new items
        if (isset($validated['new_values'])) {
            foreach ($validated['new_values'] as $value) {
                $value = trim($value);

                // Check if value already exists
                $exists = AttributeItem::where('attribute_type_id', $attribute->_id)
                    ->where('value', $value)
                    ->exists();

                if (!$exists) {
                    // New items get attribute's current status
                    AttributeItem::create([
                        'attribute_type_id' => $attribute->_id,
                        'value' => $value,
                        'status' => $newStatus
                    ]);
                    $createdCount++;
                }
            }
        }

        $message = "Attribute '{$validated['type']}' updated successfully!";

        if ($statusChangedToInactive) {
            $message .= " All values have been automatically set to inactive.";
        }

        if ($updatedCount > 0) {
            $message .= " {$updatedCount} existing value(s) updated.";
        }

        if ($autoUpdatedCount > 0 && !$statusChangedToInactive) {
            $message .= " {$autoUpdatedCount} value(s) automatically updated to match attribute status.";
        }

        if ($createdCount > 0) {
            $message .= " {$createdCount} new value(s) added.";
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', $message);

    } catch (\Exception $e) {
        Log::error('Attribute update failed: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        return back()->withInput()
            ->with('error', 'Failed to update attribute. Please try again.');
    }
}

    /**
     * Bulk update attributes status (attribute and its items)
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            Log::info('Bulk status update request received', ['data' => $request->all()]);

            $validated = $request->validate([
                'attribute_ids' => 'required|array|min:1',
                'attribute_ids.*' => 'required|string',
                'status' => 'required|in:active,inactive'
            ]);

            $updatedCount = 0;

            foreach ($validated['attribute_ids'] as $attributeId) {
                try {
                    $attribute = Attribute::find($attributeId);

                    if ($attribute) {
                        // Update attribute status
                        $attribute->update(['status' => $validated['status']]);

                        // Update all items status for this attribute
                        $itemsUpdated = AttributeItem::where('attribute_type_id', $attributeId)
                            ->update(['status' => $validated['status']]);

                        $updatedCount++;

                        Log::info("Updated attribute {$attributeId} and its items to {$validated['status']}");
                    } else {
                        Log::warning("Attribute not found: {$attributeId}");
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to update attribute {$attributeId}: " . $e->getMessage());
                }
            }

            $message = "{$updatedCount} attribute(s) and their values updated to {$validated['status']}";

            if ($updatedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No attributes were updated. Please try again.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed in bulk status update', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Bulk status update failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attributes status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active attributes for product creation (AJAX)
     */
    public function getActiveAttributes()
    {
        try {
            // Get all active attributes with their active items
            $attributes = Attribute::where('status', 'active')
                ->with(['items' => function($query) {
                    $query->where('status', 'active');
                }])
                ->get();

            $groupedAttributes = [];

            foreach ($attributes as $attribute) {
                $groupedAttributes[$attribute->type] = $attribute->items->pluck('value')->toArray();
            }

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
     * Get attribute values for a specific type
     */
    public function getAttributeValues(Request $request)
    {
        try {
            $request->validate([
                'attribute_type' => 'required|string'
            ]);

            \Log::info('Looking for attribute type:', ['type' => $request->attribute_type]);

            // Find the attribute by type
            $attribute = Attribute::where('type', $request->attribute_type)
                ->where('status', 'active')
                ->first();

            \Log::info('Found attribute:', ['attribute' => $attribute]);

            if (!$attribute) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attribute type not found'
                ], 404);
            }

            // Get active items for this attribute
            $values = AttributeItem::where('attribute_type_id', (string)$attribute->_id)
                ->where('status', 'active')
                ->orderBy('value', 'asc')
                ->get();

            \Log::info('Attribute items found:', [
                'count' => $values->count(),
                'items' => $values->toArray()
            ]);

            return response()->json([
                'success' => true,
                'values' => $values->pluck('value')
            ]);

        } catch (\Exception $e) {
            \Log::error('Get attribute values failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load attribute values: ' . $e->getMessage()
            ], 500);
        }
    }
}
